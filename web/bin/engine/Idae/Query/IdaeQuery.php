<?php

	namespace Idae\Query;

	use Idae\Connect\IdaeConnect;
	use Idae\Data\Scheme\IdaeDataScheme;
	use Idae\Db\IdaeDB;
	use Idae\Scheme\SchemeInstance;
	use function array_diff_assoc;
	use function array_filter;
	use function array_map;
	use function array_merge;
	use function is_array;
	use function is_null;
	use function sizeof;
	use function ucfirst;

	/**
	 * Class IdaeQuery
	 * override and set query methods on DB
	 *
	 * @deprecated see Idae\Query instead // should host getSchemeList
	 *
	 * IS THE ONE TO QUERY AGAINST, THE ONLY ONE !!!!
	 *
	 * @property  \MongoCollection $collection
	 */
	class IdaeQuery {

		/** @var IdaeDataScheme @deprecated */
		private $AppDataScheme;
		private $appscheme_name;
		private $appscheme_code;
		private $appscheme_nameid;
		/**  @var \MongoCollection
		 *   the collection on which we want to query
		 */
		private $appscheme_instance;
		private $appscheme_model_data;

		private $cursor_results;

		private $sort = [];

		private $page = 0;
		private $nbRows = 25;
		/**
		 * @var \MongoCollection
		 */
		private $appscheme_model_instance;

		/**
		 * @var \MongoCollection $appscheme_instance
		 */

		/**
		 * @param string $appscheme_code
		 *
		 * @throws \Exception
		 */
		public function __construct($appscheme_code = null) {

			$this->appscheme_model_instance = IdaeConnect::getInstance()->appscheme_model_instance;

			$this->AppDataScheme = null;//new AppDataScheme($argument);
			$this->collection    = $this->get_collection_from_code($appscheme_code);

			$this->appscheme_code     = $appscheme_code;
			$this->appscheme_nameid   = "id$appscheme_code";

			//return $this;
		}

		/**
		 * @param array $query_vars
		 * @param array $options
		 *
		 * @return \MongoCursor
		 */
		public function find(array $query_vars = [], array $options = []) {
			$options = array_merge(['sort'  => $this->get_sort(),
			                        'skip'  => $this->nbRows * $this->page,
			                        'limit' => $this->nbRows],
				$options);

			$rs                   = $this->collection->find($query_vars, $options);
			$this->cursor_results = $rs;

			return $rs;
		}

		/**
		 * @param array $query_vars
		 * @param array $projection
		 *
		 * @return array|null
		 */
		public function findOne($query_vars = [], $projection = []) {
			$arr                  = $this->collection->findOne($query_vars, $projection);
			$this->cursor_results = $arr;

			return $arr;
		}

		/**
		 * @param       $table_value
		 * @param array $fields
		 * @param bool  $upsert
		 *
		 * @return array|bool|void
		 */
		function updateId($table_value, $fields = [], $upsert = true) {
			if (empty($table_value)) return false;

			return $this->update([$this->appscheme_nameid => $table_value], $fields, $upsert);
		}

		function update($vars, $fields = [], $upsert = true) {
			$table       = $this->appscheme_code;
			$table_value = (int)$vars[$this->appscheme_nameid];
			if (empty($table_value)) {
				echo "probleme de mise à jour";

				return;
			}
			$arr_one_before = $this->findOne([$this->appscheme_nameid => $table_value]);
			// differences avec anciennes valeurs
			$arr_inter = array_diff_assoc($fields, (array)$arr_one_before);
			if (sizeof($arr_inter) == 0) {
				return;
			}
			// on garde la différence
			$fields = $arr_inter;
			// UPDATE !!!
			$this->collection->update($vars, ['$set' => $fields], ['upsert' => $upsert]);

			// \idae\event for pre and post
			// $this->consolidate_scheme($table_value);
			//
			$arr_one_after = $this->findOne([$this->appscheme_nameid => $table_value]);

			$updated_fields_real = array_diff_assoc((array)$arr_one_after, (array)$arr_one_before);
			$this->collection->update($vars, ['$set' => ['updated_fields' => $updated_fields_real]], ['upsert' => $upsert]);

			$update_diff_cast = [];
			$App              = new App($table);

			foreach ($updated_fields_real as $k => $v):
				$exp['field_name']  = $k;
				$exp['field_value'] = $v;
				//if($v==$vars[$k] || empty($vars[$k])) continue;
				$update_diff_cast[$k] = $App->cast_field_all($exp, true);
			endforeach;

			return $update_diff_cast;
		}

		public function insert($vars = []) {
			if (empty($vars[$this->appscheme_nameid])):
				$vars[$this->appscheme_nameid] = (int)$this->getNext($this->appscheme_nameid);
			endif;

			$this->collection->insert($vars);

			return (int)$vars[$this->appscheme_nameid];
		}

		/**
		 * @param       $distinctField
		 * @param array $query_vars
		 *
		 * @return array|bool
		 */
		public function distinct($distinctField, $query_vars = []) {
			$arr                  = $this->collection->distinct($distinctField, $query_vars);
			$this->cursor_results = $arr;

			return $arr;
		}

		/**
		 * @param string $group_by       date|dateCreation|dateDebut
		 * @param array  $query_vars
		 * @param string $grouptype_date day|month|year
		 *
		 * @return Iterator
		 */
		public function group_date($group_by, $query_vars = [], $grouptype_date = 'month') {

			//$isoname = 'iso' . ucfirst($group_by);
			$date_str_iso = "new UTDate($group_by)";
			//$date = "function (){ return   new  Date($isoname)}";
			//$date = new MongoCode($date,[]);

			return $this->group("id$group_by", $query_vars, ['month' => ['$' . $grouptype_date => ['date' => $date_str_iso]]]);
		}

		/**
		 *  on 32bits x86 windows machines ini_set('mongo.long_as_object', true); before calling, set to false after use
		 *
		 * @param       $group_by 'date'|'dateCreation'|'dateDebut'|'dateFin'
		 * @param array $query_vars
		 * @param array $groupkey []
		 *
		 * @return Iterator
		 */
		public function group_fk($group_by, $query_vars = [], $groupkey) {
			$groupkey = ['$' . $groupkey];

			return $this->group("id$group_by", $query_vars);
		}

		/**
		 * on 32bits x86 windows machines ini_set('mongo.long_as_object', true); before calling, set to false after use
		 *
		 * @param string | array $group_index
		 * @param array          $query_vars
		 * @param array          $groupkey
		 *
		 * @return \Iterator
		 */
		public function group($group_index, $query_vars = [], $groupkey = []) {

			if (is_array($group_index)) {
				$group_index = array_map(function ($n) {
					return [$n => '$' . $n];
				}, $group_index);
			} else {
				$group_index = [$group_index => '$' . $group_index];
			}
			$pipe_init = ['$group' => ['_id'   => $group_index,
			                           'count' => ['$sum' => 1],
			                           'group' => ['$push' => '$$ROOT']]];
			if ($groupkey) {
				$pipe_init['$group']['_id'] = $groupkey;
			}
			$pipeline = [$pipe_init,
				['$match' => (object)$query_vars],
				['$skip' => ($this->nbRows * $this->page)],
				['$limit' => $this->nbRows],
				['$sort' => $this->get_sort()]];

			return $this->collection->aggregateCursor($pipeline, ["cursor" => ["batchSize" => 50]]);
		}

		/**
		 * @param array $sort
		 */
		public function setSort($sort = []) {
			$this->sort = $sort;
		}

		/**
		 * @param $nbRows
		 */
		public function setLimit($nbRows) {
			$this->nbRows = (int)$nbRows;
		}

		/**
		 * @param $page
		 */
		public function setPage($page) {
			$this->page = $page;
		}

		/**
		 * @return array
		 */
		private function get_sort() {
			//return !empty($this->sort) ? $this->sort : $this->AppDataScheme->scheme_sort_fields;
			/**
			 * todo , need that in others class, use trait or something
			 * set default sorting orders for scheme
			 */
			$nom           = "nom" . ucfirst($this->appscheme_code);
			$sortBy        = empty($this->appscheme_model_data['sortFieldName']) ? $nom : $this->appscheme_model_data['sortFieldName'];
			$sortDir       = empty($this->appscheme_model_data['sortFieldOrder']) ? 1 : (int)$this->appscheme_model_data['sortFieldOrder'];
			$sortBySecond  = empty($this->appscheme_model_data['sortFieldSecondName']) ? null : $this->appscheme_model_data['sortFieldSecondName'];
			$sortDirSecond = empty($this->appscheme_model_data['sortFieldSecondOrder']) ? null : (int)$this->appscheme_model_data['sortFieldSecondOrder'];
			$sortByThird   = empty($this->appscheme_model_data['sortFieldThirdName']) ? null : $this->appscheme_model_data['sortFieldThirdName'];
			$sortDirThird  = empty($this->appscheme_model_data['sortFieldThirdOrder']) ? null : (int)$this->appscheme_model_data['sortFieldThirdOrder'];

			$sort_fields = [$sortBy => $sortDir, $sortBySecond => $sortDirSecond];
			$sort_fields = array_filter($sort_fields);

			return $sort_fields;
			//	$this->scheme_sort_fields = $sort_fields;
		}

		private function get_collection_from_code($codeAppscheme) {
			$arr                        = $this->appscheme_model_instance->findOne(['codeAppscheme' => $codeAppscheme]);
			$this->appscheme_model_data = $arr;
			$instance                   = $this->plug($arr['codeAppscheme_base'], $codeAppscheme);

			return $instance;
		}

		private function getNext($id, $min = 1) {

			if (!empty($min)) {
				$test = $this->plug('sitebase_increment', 'auto_increment')->findOne(['_id' => $id]);
				if (!empty($test['value'])) {
					if ($test['value'] < $min) {
						$this->plug('sitebase_increment', 'auto_increment')->update(['_id' => $id], ['value' => (int)$min], ["upsert" => true]);
					}
				}
			}

			$this->plug('sitebase_increment', 'auto_increment')->update(['_id' => $id], ['$inc' => ['value' => 1]], ["upsert" => true]);
			$ret = $this->plug('sitebase_increment', 'auto_increment')->findOne(['_id' => $id]);

			return (int)$ret['value'];
		}

	}

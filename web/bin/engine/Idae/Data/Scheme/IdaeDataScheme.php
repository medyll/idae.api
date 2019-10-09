<?php

	/**
	 * Class AppDataScheme
	 * Build scheme for given collection
	 *
	 * @property @deprecated $schemeFields
	 * @property @deprecated $schemeFieldsNative
	 * @property @deprecated $schemeFieldsShort
	 * @property @deprecated $schemeFieldsMini
	 * @property @deprecated $schemeFieldsTable
	 */

	namespace Idae\Data\Scheme;
	/**
	 * @todo remove IdaeConnect from here
	 */

	use Idae\Connect\IdaeConnect;
	use Idae\Data\Db\IdaeDataDb;

	class IdaeDataScheme extends IdaeDataDB {

		private $scheme_data;
		private $scheme_fields;

		public $appscheme;
		public $appscheme_base;
		public $appscheme_field;
		public $appscheme_field_group;
		public $appscheme_field_type;
		public $appscheme_has_field;
		public $appscheme_has_table_field;

		public $schemeFields;
		public $schemeFieldsNative;
		public $schemeFieldsShort;
		public $schemeFieldsMini;
		public $schemeFieldsTable;

		public $table_id;

		public $appscheme_model_instance;
		public $appscheme_model_name;
		/** @var  \MongoCollection */
		public $appscheme_instance;
		public $appscheme_name;
		public $appscheme_code;

		private $schemeFieldGroupBy;
		private $schemeFieldGrouped;

		private $AppDroitsFields;
		public $AppDataSchemeModel;

		public $grille_fk;
		public $grille_fk_grouped;
		public $grille_fk_nongrouped;
		public $grille_rfk;
		public $grille_count;

		public $scheme_sort_fields = [];

		private $IdaeDataDB;

		public function __construct($table = '', $param_draoit_fields = []) {

			try {
				if (empty($table)) {
					throw new Exception('appscheme non defini', 'EMPTY_PARAMETER_SCHEME', true);
				}
			} catch (Exception $e) {
				echo 'Exception reçue : ', $e->getMessage(), "\n";

				return false;
			};

			parent::__construct($table);

			$this->IdaeDataDB = new IdaeDataDB($table);

			//$rs = $this->IdaeDataDB->appscheme_has_field_model_instance->find()->count();

			$this->table = $table;

			if ($param_draoit_fields) {
				//$this->make_appDroitsFields($type_session, $CRUD_CODE, \IdaeDroitsFields $arrDroitFields);
				/**
				 * todo remove that
				 */
				$this->AppDroitsFields = $param_draoit_fields;//IdaeDroitsFields::getInstance()->droit_session_table_crud($arrDroitFields[0], $this->table, $arrDroitFields[1]);
			}
			$this->init();

		}

		public function init() {
			/**
			 * sur le model pour le moment
			 * DROITS  filtrer sur $this->AppDroitsFields
			 * on enleve les droits grille FK, pour le moment
			 */
			/*$vars_appDroitsFields = [];
			if (!empty($this->AppDroitsFields)) {
				$arr_allowed   = $this->AppDroitsFields['allowed'];
				$arr_forbidden = $this->AppDroitsFields['forbidden'];
				unset($arr_allowed['grilleFK']);
				unset($arr_forbidden['grilleFK']);
				$allowed   = array_map(function ($value) {
					return "$value" . ucfirst($this->table);
				}, array_filter(array_values($arr_allowed)));
				$forbidden = array_map(function ($value) {
					return "$value" . ucfirst($this->table);
				}, array_filter(array_values($arr_forbidden)));
				if ($allowed) $vars_appDroitsFields['$in'] = $allowed;
				if ($forbidden) $vars_appDroitsFields['$nin'] = $forbidden;
			}*/

			/**
			 * todo apply droits to AppDataSchemeModel, on call
			 */
			$this->set_scheme_data();
			$this->table_id = (int)$this->getSchemeData()['idappscheme'];
			$this->set_scheme_fields();
			$this->set_appscheme_instance();
			$this->set_make_grille_fk();
			$this->set_make_grille_rfk(); // and count

			//$this->AppDataSchemeModel = new IdaeDataSchemeModel($this->table, $this->AppDroitsFields);

			$this->appscheme_model_name = IdaeConnect::appscheme_model_name;

			$this->make_scheme_sort_fields();

		}

		public function getSchemeData() {
			return $this->scheme_data;
		}

		public function getSchemeFields() {
			return $this->scheme_fields;
		}

		/**
		 * @return array return all grille_fk schemes
		 */
		public function getGrilleFK() {
			return $this->grille_fk;
		}

		/**
		 * @return array return grille_fk schemes like statut / type / group / categorie
		 */
		public function get_grille_fk_grouped() {
			return $this->grille_fk_grouped;
		}

		/**
		 * @return array return grille_fk schemes not like statut / type / group / categorie
		 */
		public function get_grille_fk_nongrouped() {
			return $this->grille_fk_nongrouped;
		}

		public function get_grille_rfk() {
			return $this->grille_rfk;
		}

		/**
		 * todo , need that in others class, use trait or something
		 * set default sorting orders for scheme
		 */
		private function make_scheme_sort_fields() {
			$nom           = 'nom' . ucfirst($this->appscheme_name);
			$sortBy        = empty($this->scheme_data['sortFieldName']) ? $nom : $this->scheme_data['sortFieldName'];
			$sortDir       = empty($this->scheme_data['sortFieldOrder']) ? 1 : (int)$this->scheme_data['sortFieldOrder'];
			$sortBySecond  = empty($this->scheme_data['sortFieldSecondName']) ? null : $this->scheme_data['sortFieldSecondName'];
			$sortDirSecond = empty($this->scheme_data['sortFieldSecondOrder']) ? 1 : (int)$this->scheme_data['sortFieldSecondOrder'];
			$sortByThird   = empty($this->scheme_data['sortFieldThirdName']) ? null : $this->scheme_data['sortFieldThirdName'];
			$sortDirThird  = empty($this->scheme_data['sortFieldThirdOrder']) ? 1 : (int)$this->scheme_data['sortFieldThirdOrder'];

			$sort_fields = [$sortBy => $sortDir];
			if ($sortBySecond) {
				$sort_fields = array_merge($this->scheme_sort_fields, [$sortBySecond => $sortDirSecond], [$sortByThird => $sortDirThird]);
			}
			$this->scheme_sort_fields = $sort_fields;
		}

		/**
		 * sets parameter $AppDroitsFields
		 *
		 * @param                  $type_session
		 * @param                  $CRUD_CODE
		 * @param IdaeDroitsFields $arrDroitFields
		 */
		public function make_appDroitsFields($type_session, $CRUD_CODE, \IdaeDroitsFields $arrDroitFields) {
			$this->AppDroitsFields = $arrDroitFields->droit_session_table_crud($type_session, $this->table, $CRUD_CODE);
		}

		public function get_AppDroitsFields() {
			return $this->AppDroitsFields;
		}

		public function get_model_to_drawer() { // Mini Table ...
			$out_groups = [];
			$model      = $this->AppDataSchemeModel->model_mini;
			foreach ($model as $key_group => $arr_out_groups) {
				$out_groups[$key_group] = new IdaeDataSchemeFieldDrawer($arr_out_groups, $this, true);
			}

			return new IdaeDataSchemeFieldDrawer($out_groups, $this, true);
		}

		/**
		 * @param string $schemeFieldsType Native|Table|Mini|Short
		 *
		 * @return \IdaeDataSchemeFieldDrawer
		 * @deprecated
		 *
		 */
		public function get_schemeFields($schemeFieldsType = 'Native') {
			$name = "schemeFields$schemeFieldsType";

			return $this->$name;
		}

		/**
		 * todo move to Model
		 *
		 * @param array $scheme_field_types
		 *
		 * @return \IdaeDataSchemeFieldDrawer[]
		 * @deprecated
		 *
		 */
		public function get_schemeFieldsAll($scheme_field_types = []) {

		}

		public function set_schemeFieldGroupByMode($fieldGroupBy = null) { // group , type , Grp , Type
			if ($fieldGroupBy === null) {
				$this->schemeFieldGrouped = false;
				$this->schemeFieldGroupBy = null;

			} else {
				$this->schemeFieldGrouped = true;
				$this->schemeFieldGroupBy = $fieldGroupBy;
			}
		}

		/*		function get_table_rfk() {

					return $this->AppDataSchemeModel->grille_rfk;
				}*/

		/*	function get_grille_count() {
				return $this->AppDataSchemeModel->grille_count;
			}*/

		private function set_scheme_data() {
			$this->scheme_data = $this->appscheme_model_instance->findOne(['codeAppscheme' => $this->appscheme_code]);
		}

		/**
		 * @throws \MongoCursorException
		 * @todo credential , ripe _instance
		 */
		private function set_scheme_fields() {

			/*$vars_droits*/
			$options             = ['sort' => ['ordreAppscheme_has_table_field' => 1,
			                                   'ordreAppscheme_has_field'       => 1,
			                                   'ordreAppscheme_field'           => 1]];
			$this->scheme_fields = $this->IdaeDataDB->appscheme_has_field_model_instance->find(['idappscheme' => (int)$this->table_id], $options);

			// $this->scheme_fields = $this->sitebase_app_instance->appscheme_has_table_field->find(['idappscheme' => (int)$this->table_id]);
		}

		private function set_appscheme_instance() {
			$db_name                  = $this->scheme_data['codeAppscheme_base'];
			$this->appscheme_instance = $this->plug($db_name, $this->appscheme_code);
		}

		private function set_make_grille_rfk() {
			$table         = $this->appscheme_code;

			$rs_grille_rfk = $this->appscheme_model_instance->find(['grilleFK.table' => $table])->sort(['ordreAppscheme' => 1]);
			$out           = [];

			while ($arr_grille_rfk = $rs_grille_rfk->getNext()) {
				$codeAppscheme       = $arr_grille_rfk['codeAppscheme'];
				$out[$codeAppscheme] = $arr_grille_rfk;
			}

			$this->grille_rfk   = $out;
			$this->grille_count = empty($this->scheme_data['grilleCount']) ? [] : $this->scheme_data['grilleCount'];
		}

		private function set_make_grille_fk($vars = []) {

			$this->grille_fk            = $this->make_grille_fk($vars);
			$this->grille_fk_grouped    = $this->make_grille_fk(['grouped_scheme' => 1]);
			$this->grille_fk_nongrouped = $this->make_grille_fk(['grouped_scheme' => ['$ne' => 1]]);
		}

		private function make_grille_fk($vars = []) {
			$out       = [];
			$grille_fk = $this->scheme_data['grilleFK'];

			if (!$grille_fk) return [];
			usort($grille_fk, function ($a, $b) {
				return $a['ordreTable'] > $b['ordreTable'];
			});

			foreach ($grille_fk as $arr_fk):
				$table_fk = $arr_fk['table'];
				$det_fk   = $this->appscheme_model_instance->findOne($vars + ['codeAppscheme' => $table_fk]);

				if (empty($det_fk)) continue;

				$vars_fields = ['codeAppscheme_has_field' => ['$in' => ['nom' . ucfirst($table_fk)]]];
				$arr_rs      = $this->sitebase_app_instance->appscheme_has_field->findOne(array_merge($vars_fields, ['codeAppscheme' => $table_fk]));

				$out[$arr_rs['codeAppscheme_has_field']] = $arr_rs;

			endforeach;

			return $out;
		}

		/**
		 * @return mixed
		 */
		public function getTableId() {
			return $this->table_id;
		}

		/**
		 * @param mixed $table_id
		 *
		 * @return IdaeDataScheme
		 */
		public function setTableId($table_id) {
			$this->table_id = $table_id;

			return $this;
		}

	}









<?php

	namespace Idae\Data;

	use Idae\IdaeConstants;
	use Idae\Db\IdaeDB;
	use function array_merge;

	/**
	 * Class IdaeData
	 *
	 * Here to retrieve info on all app_schemes ( sitebase_base.sitebase_app )
	 *
	 * @package Idae\Data
	 */
	class IdaeData {

		private $conn;

		/**
		 * IdaeData constructor.
		 */
		public function __construct() {
			$this->conn = new IdaeDB(IdaeConstants::appscheme_model_name);
		}

		/**
		 * @param array (string)$args[]
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getBaseList($args = []) {
			$conn = new IdaeDB(IdaeConstants::appscheme_base_model_name);

			return $conn->find($args)->sort(['ordreAppscheme_base' => 1]);

		}

		/**
		 * @param array $args
		 * @param array $sort
		 * @param int   $limit
		 *
		 * @return \MongoCursor
		 * @throws \Exception
		 */
		public function getSchemeList($args = [], $sort = ['codeAppscheme_base' => 1], $limit = 50) {
			$conn = new IdaeDB(IdaeConstants::appscheme_model_name);
			$conn->setLimit($limit);
			$conn->setSort($sort);

			/*$options = array_merge(['sort'  => $conn->sort,
			                        'skip'  => $this->nbRows * $this->page,
			                        'limit' => $this->nbRows],
				$options);*/

			return $conn->find($args);
		}

		/**
		 * @param string $codeAppscheme
		 * @param array  $sort
		 * @param array  $options
		 *
		 * @return \MongoCursor
		 * @throws \Exception
		 */
		public function getSchemeFieldList(string $codeAppscheme, array $sort = [], array $options = []) {

			if (empty($sort)) {
				$options['sort'] = ['ordreAppscheme_has_field' => 1,
				                    'ordreAppscheme_field'     => 1];
			}

			$conn = new IdaeDB(IdaeConstants::appscheme_has_field_model_name);

			return $conn->find(['codeAppscheme' => $codeAppscheme], $options);
		}

		/**
		 * @param array $args
		 * @param array $sort
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getFieldList($args = [], $sort = ['ordreAppscheme_field' => 1]) {
			$conn = new IdaeDB(IdaeConstants::appscheme_field_model_name);

			return $conn->find($args)->sort($sort);
		}

		/**
		 * @param array (string)$args[]
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getFieldTypelist($args = []) {
			$conn = new IdaeDB(IdaeConstants::appscheme_field_type_model_name);

			return $conn->find($args);
		}

		/**
		 * @param array (string)$args[]
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getFieldGroupList($args = []) {
			$conn = new IdaeDB(IdaeConstants::appscheme_field_group_model_name);

			return $conn->find($args);
		}

	}

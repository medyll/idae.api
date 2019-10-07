<?php

	namespace Idae\Data;

	use Idae\IdaeConstants;
	use Idae\Connect\IdaeConnect;
	use Idae\Data\Db\IdaeDataDB;

	/**
	 * Class IdaeData
	 *
	 * Here to retrieve info on all app_schemes ( sitebase_base.sitebase_app )
	 *
	 * @package Idae\Data
	 */
	class IdaeData extends IdaeConnect {

		/**
		 * IdaeData constructor.
		 */
		public function __construct() {
		}

		/**
		 * @param array (string)$args[]
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getBaseList($args = []) {
			$conn = new IdaeDataDB(IdaeConstants::appscheme_base_model_name);

			return $conn->find($args)->sort(['ordreAppscheme_base' => 1]);

		}

		/**
		 * @param array $args
		 * @param array $sort
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getSchemeList($args = [], $sort = ['ordreAppscheme' => 1], $limit = 50) {
			$conn = new IdaeDataDB(IdaeConstants::appscheme_model_name);
			$conn->setLimit($limit) ;
			return $conn->find($args)->sort($sort);
		}

		/**
		 * @param string $codeAppscheme
		 * @param array  $sort
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getSchemeFieldList($codeAppscheme, $sort = []) {
			if (empty($sort)) {
				$sort = ['ordreAppscheme_has_field' => 1,
				         'ordreAppscheme_field'     => 1];
			}
			$conn = new IdaeDataDB(IdaeConstants::appscheme_has_field_model_name);

			return $conn->find(['codeAppscheme' => $codeAppscheme])->sort($sort);
		}

		/**
		 * @param array $args
		 * @param array $sort
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getFieldList($args = [], $sort = ['ordreAppscheme_field' => 1]) {
			$conn = new IdaeDataDB(IdaeConstants::appscheme_field_model_name);

			return $conn->find($args)->sort($sort);
		}

		/**
		 * @param array (string)$args[]
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getFieldTypelist($args = []) {
			$conn = new IdaeDataDB(IdaeConstants::appscheme_field_type_model_name);

			return $conn->find($args);
		}

		/**
		 * @param array (string)$args[]
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getFieldGroupList($args = []) {
			$conn = new IdaeDataDB(IdaeConstants::appscheme_field_group_model_name);

			return $conn->find($args);
		}

	}

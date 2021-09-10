<?php

	namespace Idae\App;

	use Idae\Data\Cache\IdaeDataCache;
	use Idae\IdaeConstants;
	use Idae\Query\IdaeQuery;

	/**
	 * Class IdaeAppBase
	 *
	 * Here to retrieve info on all app_schemes data schemas
	 *
	 * @package Idae\Data
	 */
	class IdaeAppBase {

		private $qy;
		private $cache;

		/**
		 * IdaeAppBase constructor.
		 */
		public function __construct() {

			$this->qy    = new IdaeQuery();
			$this->cache = new IdaeDataCache;
		}

		/**
		 * @param array $args
		 * @param array $sort
		 *
		 * @return \MongoCursor
		 */
		public function getSchemeBaseList($args = [], $sort = ['ordreAppscheme_base' => 1]) {

			return $this->qy->collection(IdaeConstants::appscheme_base_model_name)
			                ->setSort($sort)
			                ->find($args);
		}

		/**
		 * @param array $args
		 * @param array $sort
		 * @param int   $limit
		 *
		 * @return \MongoCursor
		 * @throws \Exception
		 */
		public function getSchemeList($args = [], $sort = ['ordreAppscheme' => 1], $limit = 50) {

			$this->qy->collection(IdaeConstants::appscheme_model_name)
			         ->setSort($sort)
			         ->setLimit($limit);

			return $this->qy->find($args);
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

			$sort = $sort ?? ['ordreAppscheme_has_field' => 1,
			                  'ordreAppscheme_field'     => 1];

			return $this->qy->collection(IdaeConstants::appscheme_has_field_model_name)
			                ->setSort($sort)
			                ->find(['codeAppscheme' => $codeAppscheme], $options);

		}

		/**
		 * @param array $args
		 * @param array $sort
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		public function getFieldList($args = [], $sort = ['ordreAppscheme_field' => 1]) {
			return $this->qy->collection(IdaeConstants::appscheme_field_model_name)
			                ->setSort($sort)
			                ->find($args);
		}

		/**
		 * @param array $args
		 * @param array $sort
		 *
		 * @return \MongoCursor
		 */
		public function getFieldTypelist($args = [], $sort = ['ordreAppscheme_field_type' => 1]) {

			return $this->qy->collection(IdaeConstants::appscheme_field_type_model_name)
			                ->setSort($sort)
			                ->find($args);
		}

		/**
		 * @param array $args
		 * @param array $sort [ord]
		 *
		 * @return \MongoCursor
		 * @throws \Exception
		 */
		public function getFieldGroupList($args = [], $sort = ['ordreAppscheme_field_group' => 1]) {

			return $this->qy->collection(IdaeConstants::appscheme_field_group_model_name)
			                ->setSort($sort)
			                ->find($args);
		}

	}

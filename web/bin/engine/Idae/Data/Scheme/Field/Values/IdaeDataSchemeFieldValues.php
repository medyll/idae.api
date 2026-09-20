<?php

	/**
	 *
	 * Make query on a AppDataScheme
	 *
	 */
	namespace Idae\Data\Scheme\Field\Values;

	use Idae\Data\Scheme\IdaeDataScheme;

	/**
	 * Runs a query against a scheme's collection and casts each row's values through
	 * the scheme's field definitions.
	 */
	class IdaeDataSchemeFieldValues {
		private $AppDataScheme;
		private $appscheme_name;

		private $appscheme_instance;
		private $arr_fetchedFields = [];

		/**
		 * @param array           $query_vars
		 * @param \IdaeDataScheme $AppDataScheme
		 */
		public function __construct($query_vars = [], IdaeDataScheme $AppDataScheme) {

			$this->AppDataScheme      = $AppDataScheme;
			$this->appscheme_instance = $this->AppDataScheme->appscheme_instance;
			$this->appscheme_name     = $this->AppDataScheme->appscheme_name;

		}

		/**
		 * Finds several documents.
		 *
		 * @param array $query_vars
		 * @param int   $limit Defaults to 10
		 * @return array Cast values, one entry per row
		 */
		public function query($query_vars = [], $limit = 10) { // default sort order !!!
			$rs                  = $this->appscheme_instance->find($query_vars)->limit($limit);
			$this->looped_values = $this->loop_values($rs, 'find');

			return $this->looped_values;
		}

		/**
		 * Finds a single document.
		 *
		 * @param array $query_vars
		 * @return array Cast values
		 */
		public function query_one($query_vars = []) {
			$arr                 = $this->appscheme_instance->findOne($query_vars);
			$this->looped_values = $this->loop_values($arr, 'findOne');

			return $this->looped_values;
		}

		/**
		 * Distinct values of one field, cast through that field's definition.
		 *
		 * @param string $distinctField
		 * @param array  $query_vars
		 * @return array
		 */
		public function query_distinct($distinctField, $query_vars = []) {
			$arr = $this->appscheme_instance->distinct($distinctField, $query_vars);

			$this->looped_values = $this->loop_values($arr, 'distinct');

			return $this->looped_values;
		}

	}

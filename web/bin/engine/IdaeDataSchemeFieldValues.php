<?php

	/**
	 *
	 * Make query on a AppDataScheme
	 *
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
		public function __construct($query_vars = [], \IdaeDataScheme $AppDataScheme) {

			$this->AppDataScheme      = $AppDataScheme;
			$this->appscheme_instance = $this->AppDataScheme->appscheme_instance;
			$this->appscheme_name     = $this->AppDataScheme->appscheme_name;

		}

		public function query($query_vars = [], $limit = 10) { // default sort order !!!
			$rs                  = $this->appscheme_instance->find($query_vars)->limit($limit);
			$this->looped_values = $this->loop_values($rs, 'find');

			return $this->looped_values;
		}

		public function query_one($query_vars = []) {
			$arr                 = $this->appscheme_instance->findOne($query_vars);
			$this->looped_values = $this->loop_values($arr, 'findOne');

			return $this->looped_values;
		}

		public function query_distinct($distinctField, $query_vars = []) {
			$arr = $this->appscheme_instance->distinct($distinctField, $query_vars);

			$this->looped_values = $this->loop_values($arr, 'distinct');

			return $this->looped_values;
		}

	}
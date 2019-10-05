<?php

	/**
	 * Class IdaeDataSchemeParts
	 */
	namespace Idae\Data\Scheme\Parts;

	class IdaeDataSchemeParts implements \ArrayAccess, \Iterator {

		CONST  SCHEME_MAIN          = 'scheme_field_main';
		CONST  SCHEME_FK_ALL        = 'scheme_field_fk_all';
		CONST  SCHEME_FK_GROUPED    = 'scheme_field_fk_grouped';
		CONST  SCHEME_FK_NONGROUPED = 'scheme_field_fk_nongrouped';
		CONST  SCHEME_RFK           = 'scheme_field_rfk';
		CONST  SCHEME_COUNT         = 'scheme_field_count';
		CONST  SCHEME_IMAGE         = 'scheme_field_image';

		private $position            = 0;
		public  $scheme_part_content = [];
		public  $scheme_part_type    = null;

		public function __construct($scheme_part, $scheme_part_content = []) {

			try {
				if (empty($scheme_part)) throw new Exception('Scheme_part argument null', 'EMPTY_SCHEMEPART_ARGUMENT', true);
			}
			catch (\Exception $e) {
				echo 'Exception reçue : ', $e->getMessage(), "\n";

				return false;
			}
			$this->scheme_part_type    = $scheme_part;
			$this->scheme_part_content = $scheme_part_content;

			return $this;
		}

		public static function setSchemePart($scheme_part = '', $scheme_part_content = []) {

			$part = new  self($scheme_part, $scheme_part_content);

			return $part;
		}

		public function offsetSet($offset, $value) {
			if (is_null($offset)) {
				$this->scheme_part_content[] = $value;
			} else {
				$this->scheme_part_content[$offset] = $value;
			}
		}

		public function offsetExists($offset) {
			return isset($this->scheme_part_content[$offset]);
		}

		public function offsetUnset($offset) {
			unset($this->scheme_part_content[$offset]);
		}

		public function offsetGet($offset) {
			return isset($this->scheme_part_content[$offset]) ? $this->scheme_part_content[$offset] : null;
		}

		public function rewind() {
			$this->position = 0;
		}

		public function current() {

			return $this->scheme_part_content[$this->position];
		}

		public function key() {

			return $this->position;
		}

		public function next() {
			++$this->position;
		}

		public function valid() {

			return isset($this->scheme_part_content[$this->position]);
		}
	}

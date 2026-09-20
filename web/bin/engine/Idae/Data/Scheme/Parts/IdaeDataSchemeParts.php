<?php

	/**
	 * Class IdaeDataSchemeParts
	 */
	namespace Idae\Data\Scheme\Parts;

	/**
	 * One named slice of a scheme's fields: the main fields, the foreign keys, the
	 * reverse keys, and so on. The `SCHEME_*` constants name the slices.
	 *
	 * Wraps a plain array, readable as an array and iterable. Iteration walks integer
	 * positions 0..n, so a slice stored under string keys reads correctly through
	 * ArrayAccess but yields nothing when iterated.
	 */
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

		/**
		 * Builds a slice of the given type.
		 *
		 * An empty `$scheme_part` is reported by echoing the exception, not by throwing:
		 * the object is still constructed, with a null type.
		 *
		 * @param string $scheme_part One of the SCHEME_* constants
		 * @param array  $scheme_part_content
		 */
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

		/**
		 * Constructor shorthand.
		 *
		 * @param string $scheme_part One of the SCHEME_* constants
		 * @param array  $scheme_part_content
		 * @return self
		 */
		public static function setSchemePart($scheme_part = '', $scheme_part_content = []) {

			$part = new  self($scheme_part, $scheme_part_content);

			return $part;
		}

		/** @inheritDoc */
		public function offsetSet($offset, $value) {
			if (is_null($offset)) {
				$this->scheme_part_content[] = $value;
			} else {
				$this->scheme_part_content[$offset] = $value;
			}
		}

		/** @inheritDoc */
		public function offsetExists($offset) {
			return isset($this->scheme_part_content[$offset]);
		}

		/** @inheritDoc */
		public function offsetUnset($offset) {
			unset($this->scheme_part_content[$offset]);
		}

		/** @inheritDoc */
		public function offsetGet($offset) {
			return isset($this->scheme_part_content[$offset]) ? $this->scheme_part_content[$offset] : null;
		}

		/** @inheritDoc */
		public function rewind() {
			$this->position = 0;
		}

		/** @inheritDoc */
		public function current() {

			return $this->scheme_part_content[$this->position];
		}

		/** @inheritDoc */
		public function key() {

			return $this->position;
		}

		/** @inheritDoc */
		public function next() {
			++$this->position;
		}

		/** @inheritDoc */
		public function valid() {

			return isset($this->scheme_part_content[$this->position]);
		}
	}

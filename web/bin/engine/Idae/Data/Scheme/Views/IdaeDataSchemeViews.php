<?php
	namespace Idae\Data\Scheme\Views;

	/**
	 * The field list for one rendering of a scheme: native, mini, table or short.
	 * The `SCHEME_VIEW_*` constants name the renderings.
	 *
	 * Wraps a plain array, readable as an array and iterable. Iteration reads `current()` for validity, so a stored `false`, `0` or `''`
	 * ends the loop early.
	 */
	class IdaeDataSchemeViews implements \ArrayAccess, \Iterator {

		CONST  SCHEME_VIEW_NATIVE = 'scheme_field_native';
		CONST  SCHEME_VIEW_MINI   = 'scheme_field_mini';
		CONST  SCHEME_VIEW_TABLE  = 'scheme_field_table';
		CONST  SCHEME_VIEW_SHORT  = 'scheme_field_short';

		private $position            = 0;
		public  $scheme_view_content = [];
		public  $scheme_view_type    = null;

		/**
		 * Builds a view of the given type.
		 *
		 * An empty `$scheme_part` is reported by echoing the exception, not by throwing:
		 * the object is still constructed, with a null type.
		 *
		 * @param string $scheme_part One of the SCHEME_VIEW_* constants
		 * @param array  $scheme_view_content
		 */
		public function __construct($scheme_part, $scheme_view_content = []) {

			try {
				if (empty($scheme_part)) throw new Exception('Scheme_part argument null', 'EMPTY_SCHEMEPART_ARGUMENT', true);
			}
			catch (\Exception $e) {
				echo 'Exception reçue : ', $e->getMessage(), "\n";

				return false;
			}
			$this->scheme_view_type    = $scheme_part;
			$this->scheme_view_content = $scheme_view_content;

		}

		/**
		 * Returns the underlying field list.
		 *
		 * @return array
		 */
		public function __invoke() {
			return $this->scheme_view_content;
		}

		/**
		 * Constructor shorthand.
		 *
		 * @param string $scheme_view_type One of the SCHEME_VIEW_* constants
		 * @param array  $scheme_view_content
		 * @return self
		 */
		public static function setSchemeView($scheme_view_type = '', $scheme_view_content = []) {

			$part = new  self($scheme_view_type, $scheme_view_content);

			return $part;
		}

		/** @inheritDoc */
		public function offsetSet($offset, $value) {
			if (is_null($offset)) {
				$this->scheme_view_content[] = $value;
			} else {
				$this->scheme_view_content[$offset] = $value;
			}
		}

		/** @inheritDoc */
		public function offsetExists($offset) {
			return isset($this->scheme_view_content[$offset]);
		}

		/** @inheritDoc */
		public function offsetUnset($offset) {
			unset($this->scheme_view_content[$offset]);
		}

		/** @inheritDoc */
		public function offsetGet($offset) {
			return isset($this->scheme_view_content[$offset]) ? $this->scheme_view_content[$offset] : null;
		}

		/** @inheritDoc */
		public function key() {
			return key($this->scheme_view_content);
		}

		/** @inheritDoc */
		public function current() {
			return current($this->scheme_view_content);
		}

		/** @inheritDoc */
		public function next() {
			next($this->scheme_view_content);
		}

		/** @inheritDoc */
		public function rewind() {
			reset($this->scheme_view_content);
		}

		/** @inheritDoc */
		public function valid() {
			return current($this->scheme_view_content);
		}
	}

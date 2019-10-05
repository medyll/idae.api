<?php
	namespace Idae\Data\Scheme;

	class IdaeDataSchemeViews implements \ArrayAccess, \Iterator {

		CONST  SCHEME_VIEW_NATIVE = 'scheme_field_native';
		CONST  SCHEME_VIEW_MINI   = 'scheme_field_mini';
		CONST  SCHEME_VIEW_TABLE  = 'scheme_field_table';
		CONST  SCHEME_VIEW_SHORT  = 'scheme_field_short';

		private $position            = 0;
		public  $scheme_view_content = [];
		public  $scheme_view_type    = null;

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

		public function __invoke() {
			return $this->scheme_view_content;
		}

		public static function setSchemeView($scheme_view_type = '', $scheme_view_content = []) {

			$part = new  self($scheme_view_type, $scheme_view_content);

			return $part;
		}

		public function offsetSet($offset, $value) {
			if (is_null($offset)) {
				$this->scheme_view_content[] = $value;
			} else {
				$this->scheme_view_content[$offset] = $value;
			}
		}

		public function offsetExists($offset) {
			return isset($this->scheme_view_content[$offset]);
		}

		public function offsetUnset($offset) {
			unset($this->scheme_view_content[$offset]);
		}

		public function offsetGet($offset) {
			return isset($this->scheme_view_content[$offset]) ? $this->scheme_view_content[$offset] : null;
		}

		public function key() {
			return key($this->scheme_view_content);
		}

		public function current() {
			return current($this->scheme_view_content);
		}

		public function next() {
			next($this->scheme_view_content);
		}

		public function rewind() {
			reset($this->scheme_view_content);
		}

		public function valid() {
			return current($this->scheme_view_content);
		}
	}

<?php

	namespace Idae\Api;

	use function array_map;
	use function array_merge;
	use function convert_datetime;
	use function explode;
	use function is_array;
	use function is_numeric;
	use function is_string;
	use function str_replace;
	use function strlen;
	use function strpos;
	use function substr;
	use function substr_replace;
	use function var_dump;

	class IdaeApiOperatorMongoDbPhp extends IdaeApiOperators {

		/**
		 * IdaeApiOperatorMongoDbPhp constructor.
		 */
		public function __construct() {

		}

		/**
		 * return mongodb query operators array
		 * @param array $operators
		 *
		 * @return array
		 */
		public static function set_operators(array $operators = []) {
			$out = [];
			foreach ($operators as $key => $value_key) {
				if (!empty($operators[$key])) {
					$out = array_merge($out, self::set_operator($key, $value_key));
				}
			}

			return $out;
		}

		private static function set_operator(string $operator, array $keys_eq, string $prefix = null) {
			$method = "set_key_$operator";

			return self::$method($keys_eq, $prefix);
		}

		private static function cast($value, $type = null) {
			if ($type !== 'string') {
				// 99
				if (is_numeric($value)) $value = (int)$value;
			}
			// [item1,item2,item3]
			if (is_string($value) && substr($value, 0, 1) === '[' && substr($value, -1, 1) === ']') {
				$value = str_replace('[', '', $value);
				$value = str_replace(']', '', $value);
				$value = explode(',', $value);
				$value = array_map(function ($out) use ($type) {
					return self::cast($out, $type);
				}, $value);
			}
			// [cmd:do]
			if (is_string($value) && sizeof(explode(':', $value)) == 2) {
				$value = explode(':', $value);
				$value = [self::cast_cmd($value[0]) => self::cast($value[1], $type)];
			}

			// not dots no : no [
			// breaks $expr
			if (is_string($value) && strpos($value, ',') === false && strpos($value, ':') === false){

				if (strpos($value, '$') === 0) {
					$value = substr_replace($value, '', 0, 1);
				}else{
					$value = substr_replace($value, '^', 0, 1);
				}
				if (strpos($value, '$') === strlen($value) - 1) {
					$value = substr_replace($value, '', -1, 1);
				}else{
					$value = substr_replace($value, '$', 0, -1);
				}

				$value = ['$regex' => "$value", '$options' => "i"];
			}
				return $value;
		}

		private static function cast_cmd(string $cmd) {

			if (self::is_operator($cmd)) return '$' . $cmd;

			return $cmd;
		}

		private static function set_key_or(array $key_or, string $prefix = null) {

			$prefix = $prefix ?? '$or';
			$out    = [];
			$test   = [];

			// $or: [ { quantity: { $lt: 20 } }, { price: 10 } ] }
			foreach ($key_or as $key_field => $field_value):
				if (empty($field_value)) continue;

				$test[] = [$key_field => self::cast($field_value)];

			endforeach;

			$out[$prefix] = $test;

			return $out;
		}

		private static function set_key_nor(array $keys_eq, string $prefix = null) {

			return self::set_key_or($keys_eq, '$nor');
		}

		private static function set_key_in(array $keys_eq, string $prefix = null) {

			$prefix = $prefix ?? '$in';
			$out    = [];

			foreach ($keys_eq as $key_lk => $value_lk) {
				if (is_array($value_lk)) {
					$out[$key_lk][$prefix] = $value_lk;
				} else {
					$arr_value_in = self::cast($value_lk);

					$out[$key_lk][$prefix] = array_map(function ($out) {
						return self::cast($out);
					}, $arr_value_in);
				}
			}

			return $out;
		}

		private static function set_key_nin(array $keys_nin, string $prefix = null) {
			return self::set_key_in($keys_nin, '$nin');
		}

		private static function set_key_all(array $keys_all, string $prefix = null) {
			return self::set_key_in($keys_all, '$all');
		}

		private static function set_key_eq(array $keys_eq) {
			$out = [];
			foreach ($keys_eq as $key_lk => $value_lk) {
				$out[$key_lk] = self::cast($value_lk);
			}

			return $out;
		}

		private static function set_key_ne(array $keys_eq) {
			$out = [];
			foreach ($keys_eq as $key_lk => $value_lk) {
				$out[$key_lk]['$ne'] = self::cast($value_lk);
			}

			return $out;
		}

		/**
		 * @param array $keys_lk
		 *
		 * @return array
		 */
		private static function set_key_lk(array $keys_lk) {
			$out = [];
			foreach ($keys_lk as $key_lk => $value_lk) {
				if (strpos($value_lk, '$') !== false) {
					// {"field":{"$regex":"\d+","$options":"i"}}
					if (strpos($value_lk, '$') === 0) {
						$value_lk = substr_replace($value_lk, '', 0, 1);
					}
					if (strpos($value_lk, '$') === strlen($value_lk) - 1) {
						$value_lk = substr_replace($value_lk, '', -1, 1);
					}

					$regexp = ['$regex' => "$value_lk", '$options' => "i"];
				} else {
					$value_lk = str_replace('$', '', $value_lk);
					$regexp   = ['$regex' => "^$value_lk$", '$options' => "i"];
				}
				$out[$key_lk] = $regexp;
			}

			return $out;
		}
	}

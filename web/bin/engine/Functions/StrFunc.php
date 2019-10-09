<?php

	namespace Functions;

	use function lcfirst;
	use function str_replace;
	use function strlen;
	use function strtolower;
	use function ucwords;

	class StrFunc {
		/**
		 * Converts string to camel case.
		 *
		 * @param string $str
		 * @return string
		 */
		public static function toCamelCase($str)
		{
			return $str = str_replace(
				' ',
				'',
				ucwords(str_replace(array('-', '_'), ' ', $str))
			);

			/*$str[0] = strtolower($str[0]);

			return $str;*/
		}

		/**
		 * Converts string to snake case.
		 *
		 *
		 * @param string $str
		 * @param string $delimiter
		 * @return string
		 */
		public static function toSnakeCase($str, $delimiter = '_')
		{
			$str = lcfirst($str);
			$lowerCase = strtolower($str);
			$result = '';
			$length = strlen($str);
			for ($i = 0; $i < $length; $i++) {
				$result .= ($str[$i] === $lowerCase[$i] ? '' : $delimiter) . $lowerCase[$i];
			}
			return $result;
		}
	}

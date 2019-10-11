<?php

namespace Functions;

use function lcfirst;
use function str_replace;
use function strlen;
use function strtolower;
use function ucwords;

class StrFunc
{
		
		static function br2nl($string)
		{
				return preg_replace('#<br\s*/?-->#i', "\n", $string);
		}
		
		static function cleanStr(&$value, $key = '')
		{
				if (is_array($value) || is_object($value)) {
						array_walk_recursive($value, 'CleanStr', $value);
						
						return;
				}
				$value = trim($value);
				if (stristr($value, '/')) {
						$arrTest = explode('/', $value);
						if (is_numeric($arrTest[0]) && is_numeric($arrTest[1])) {
								$value = date_mysql($value);
						}
				}
		}
		
		static function strFind($haystack, $needle, $ignoreCase = false)
		{
				if ($ignoreCase) {
						$haystack = strtolower($haystack);
						$needle   = strtolower($needle);
				}
				$needlePos = strpos($haystack, $needle);
				
				return ($needlePos === false ? false : ($needlePos + 1));
		}
		
		/**
		 * Converts string to camel case.
		 *
		 * @param string $str
		 *
		 * @return string
		 */
		static function toCamelCase($str)
		{
				return $str = str_replace(
					' ',
					'',
					ucwords(str_replace(['-', '_'], ' ', $str))
				);
				
		}
		
		/**
		 * Converts string to snake case.
		 *
		 *
		 * @param string $str
		 * @param string $delimiter
		 *
		 * @return string
		 */
		static function toSnakeCase($str, $delimiter = '_')
		{
				$str       = lcfirst($str);
				$lowerCase = strtolower($str);
				$result    = '';
				$length    = strlen($str);
				for ($i = 0; $i < $length; $i++) {
						$result .= ($str[$i] === $lowerCase[$i] ? '' : $delimiter) . $lowerCase[$i];
				}
				return $result;
		}
}

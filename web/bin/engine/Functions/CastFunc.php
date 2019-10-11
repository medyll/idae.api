<?php
/**
 * Created by PhpStorm.
 * User: Meddy
 * Date: 11/10/2019
 * Time: 13:35
 */

namespace Functions;


class CastFunc
{
		static function cleanPostMongo($arr, $keepnumerickey = false)
		{
				
				unset($arr['F_action']);
				unset($arr['mdl']);
				unset($arr['module']);
				unset($arr['reloadModule']);
				unset($arr['afterAction']);
				unset($arr['_id']);
				if (empty($arr)) return $arr;
				foreach ($arr as $key => $column) {
						$pos = strpos($key, 'fake_');
						if ($pos === false) {
						} else {
								unset($arr[$key]);
						}
				}
				$arrClean = [];
				foreach ($arr as $key => $column) {
						if (str_find($key, 'code') || str_find($key, 'phone')) {
								$arrClean[$key] = $column;
								continue;
						};
						if ((!is_int($key) || $keepnumerickey == true)) {
								$arrClean[$key] = $arr[$key];
								if ($arr[$key] == 'true') $arrClean[$key] = (bool)true;
								if ($arr[$key] == 'false') $arrClean[$key] = (bool)false;
								
								if (function_prod::isTrueFloat($arrClean[$key])) {
										$arrClean[$key] = (float)$arrClean[$key];
								} elseif (is_numeric($arr[$key])) {
										$arrClean[$key] = (int)$arrClean[$key];
								} elseif (is_numeric(str_replace(' ', '', $arr[$key]))) {
										$arrClean[$key] = (int)str_replace(' ', '', $arr[$key]);
								}
								if (is_array($arr[$key])) {
										$arrClean[$key] = function_prod::cleanPostMongo($arrClean[$key], $keepnumerickey);
								}
						}
				}
				
				return $arrClean;
		}
		
		static function cleanPostDesc($arr)
		{
				if (empty($arr)) return $arr;
				foreach ($arr as $key => $column) {
						$pos = strpos($key, 'description');
						if (is_array($arr[$key])) {
								$arr[$key] = function_prod::cleanPostMongo($arr[$key]);
						}
						if ($pos === false) {
						} else {
								unset($arr[$key]);
						}
				}
				
				return $arr;
		}
		
		static function cleanAdodb($arr, $keepnumerickey = false)
		{
				unset($arr['F_action']);
				unset($arr['mdl']);
				unset($arr['module']);
				unset($arr['reloadModule']);
				unset($arr['afterAction']);
				
				$arrClean = [];
				foreach ($arr as $key => $column) {
						if ((!is_int($key) || $keepnumerickey == true)) {
								$arrClean[$key] = $arr[$key];
								if (is_array($arr[$key])) {
										$arrClean[$key] = function_prod::cleanAdodb($arrClean[$key], $keepnumerickey);
								}
						}
				}
				
				return $arrClean;
		}
		
		static function mysqlToMongo($arr, $keepnumerickey = false)
		{
				unset($arr['F_action']);
				unset($arr['mdl']);
				unset($arr['module']);
				unset($arr['reloadModule']);
				unset($arr['afterAction']);
				$arr = function_prod::cleanPostMongo($arr, $keepnumerickey);
				foreach ($arr as $key => $column) {
						$pos = strpos($key, 'fake_');
						if ($pos === false) {
						} else {
								unset($arr[$key]);
						}
				}
				$arrClean = [];
				foreach ($arr as $key => $column) {
						if ((!is_int($key) || $keepnumerickey == true)) {
								$arrClean[$key] = $arr[$key];
								if (is_array($arr[$key])) {
										$arrClean[$key] = function_prod::mysqlToMongo($arrClean[$key], $keepnumerickey);
								}
								if (!is_array($arrClean[$key])) {
										$arrID = explode("_id", $key);
										if (sizeof($arrID) == 2) {
												$arrClean['id' . $arrID[1]] = $arrClean[$key];
												unset($arrClean[$key]);
										}
								}
						}
				}
				
				return $arrClean;
		}
}
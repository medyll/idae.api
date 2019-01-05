<?php

	/**
	 * Class function_prod
	 * @deprecated
	 */
	class function_prod {

		function __construct() {

		}




		static function mois_fr($num) {
			$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

			return $tabmonth[(int)$num];
		}

		static function date_fr($date) {
			$arrDate  = explode('-', $date);
			$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

			return $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}

		static function moisDate_fr($date) {
			$arrDate  = explode('-', $date);
			$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

			return $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}

		static function mois_short_Date_fr($date) {
			$arrDate  = explode('-', $date);
			$tabmonth = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

			return substr($tabmonth[(int)$arrDate[1]], 0, 4) . ' ' . $arrDate[0];
		}

		static function jourMoisDate_fr($date) {
			$arrDate   = explode('-', $date);
			$tabmonth  = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
			$tabjour   = [1 => "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
			$indexjour = date("w", strtotime($date));

			return $tabjour[$indexjour] . ' ' . $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]] . ' ' . $arrDate[0];
		}

		static function jourMoisDate_fr_short($date) {
			$arrDate   = explode('-', $date);
			$tabmonth  = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
			$tabjour   = [1 => "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
			$indexjour = date("w", strtotime($date));

			return $tabjour[$indexjour] . ' ' . $arrDate[2] . ' ' . $tabmonth[(int)$arrDate[1]];
		}

		static function cleanPostMongo($arr, $keepnumerickey = false) {

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

		function cleanPostDesc($arr) {
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

		function isTrueFloat($val) {
			/*if(is_array($val)) return false;
			$pattern = '/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/';
			return (!is_bool($val) && (is_float($val) || preg_match($pattern, trim($val))));*/
			//
			if (is_string($val)) $val = trim($val);
			if (is_numeric($val) && (is_float($val) || ((float)$val > (int)$val
						|| strlen($val) != strlen((int)$val)) && (ceil($val)) != 0)
			) {
				return true;
			} else return false;
		}

		function cleanAdodb($arr, $keepnumerickey = false) {
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

		function mysqlToMongo($arr, $keepnumerickey = false) {
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

		function andLast($tmparray, $sep = ',', $word = 'et') {
			if (sizeof($tmparray) > 1):
				$last  = array_pop($tmparray);
				$toadd = implode($sep . ' ', $tmparray) . " $word $last";
			else:
				$toadd = array_pop($tmparray);
			endif;

			return $toadd;
		}


		function cleanPostSearch($txt) {
			$arr      = explode('-', $txt);
			$arrClean = [];
			$i        = 0;
			foreach ($arr as $key => $value) {
				$i++;
				if (!is_numeric($value)) {
					$arrClean[$key] = $value;
				}
			}
			$out = implode(' ', $arrClean);

			return $out;
		}

		function buildCode($texte, $len = 8, $num = '') {
			$texte = strtolower($texte);
			$texte = str_replace(" ", "", $texte);
			$texte = preg_replace('{(.)\1+}', '$1', $texte);
			$texte = str_replace(
				[
					'à', 'â', 'ä', 'á', 'ã', 'å',
					'î', 'ï', 'ì', 'í',
					'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
					'ù', 'û', 'ü', 'ú',
					'é', 'è', 'ê', 'ë', 'ê', '&', strtoupper('&'),
					'ç', 'ÿ', 'ñ', '\'', '"', '_', '!', '?', '\\',
					'(', ')', '/'
				],
				[
					'a', 'a', 'a', 'a', 'a', 'a',
					'i', 'i', 'i', 'i',
					'o', 'o', 'o', 'o', 'o', 'o',
					'u', 'u', 'u', 'u',
					'e', 'e', 'e', 'e', 'e', 'e', 'e',
					'c', 'y', 'n', '-', '-', '-', '-', '-', '-',
					'', '', ''
				], $texte
			);
			if (!empty($num)) {
				$texte = str_replace(
					[
						'a', 'e', 'i', 'o', 'u', 'y'
					],
					[
						'', '', '', '', '', ''
					], $texte
				);
			}
			$testlen = strlen(str_replace(" ", "", $texte));
			$arrt    = explode(' ', $texte);
			$dsp     = '';//echo "-> ";
			$i       = 0;
			$strdone = 0;
			if (strlen($texte) >= $len) {
				//echo sizeof($arrt);//echo "-> ";
				$div      = ceil((int)$testlen / sizeof($arrt));//echo "-> ";
				$maxpiece = ceil($len / sizeof($arrt));
				//echo "<br>";
				foreach ($arrt as $value) {
					$i++;
					$strdone += $maxpiece;
					if ($i == sizeof($arrt)) {
						//echo $strdone;echo "-> ";
						$maxpiece = $testlen - $strdone;
						//echo " , ";
					}
					//if(strlen($value)> $div && $testlen-$len>$len) {
					$value = substr($value, 0, $maxpiece);
					//}
					$dsp .= $value;
				}
			} else {
				$dsp = $texte;
			}
			//echo "'".$dsp."'  ";
			// $texte = array_unique($texte);
			$texte = str_replace("-", "", $dsp);
			$texte = str_replace(" ", "", $texte);
			if (strlen($texte) > $len) {
				$texte = substr($texte, 0, $len);
			}
			$texte = stripslashes(strtoupper($texte));

			//echo $texte.'/';
			return $texte;
		}

	}
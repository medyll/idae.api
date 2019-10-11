<?php


/**
 * Class function_prod
 *
 * @deprecated
 */
class function_prod
{
		
		function __construct()
		{
		
		}
		
		
		function isTrueFloat($val)
		{
				//
				if (is_string($val)) $val = trim($val);
				if (is_numeric($val) && (is_float($val) || ((float)$val > (int)$val
				                                            || strlen($val) != strlen((int)$val)) && (ceil($val)) != 0)
				) {
						return true;
				} else return false;
		}
		
		
		function andLast($tmparray, $sep = ',', $word = 'et')
		{
				if (sizeof($tmparray) > 1):
						$last  = array_pop($tmparray);
						$toadd = implode($sep . ' ', $tmparray) . " $word $last";
				else:
						$toadd = array_pop($tmparray);
				endif;
				
				return $toadd;
		}
		
		
		function cleanPostSearch($txt)
		{
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
		
		function buildCode($texte, $len = 8, $num = '')
		{
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

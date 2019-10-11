<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 11/12/14
	 * Time: 00:32
	 */

	/**
	 * @param DateTime $dt
	 * @param int      $precision
	 *
	 * @return DateTime
	 */
	function roundToNextXMin(\DateTime $dt, $precision = 10) {
		$s = $precision * 60;
		$dt->setTimestamp($s * ceil($dt->getTimestamp() / $s));

		return $dt;
	}

	function calcul_marge() {
		$part_coursier = 3;
		$part_shop     = 26;
		$totalcommande = 29;
		$part_stripe   = 1.4;

	}



	function delay_minute_random($min = 1, $max = 5) {

		$min = 1000 * 60 * $min;
		$max = 1000 * 60 * $max;

		return rand($min, $max);
	}

	;
	function array_random($arr, $num = 1) {
		shuffle($arr);
		$num = (sizeof($arr) < $num) ? sizeof($arr) : $num;
		$r   = [];
		for ($i = 0; $i < $num; $i++) {
			$r[] = $arr[$i];
		}

		return $num == 1 ? $r : $r;
	}



	function chkSch($name, $value = '', $name_vars = 'vars') {
		$id_no  = uniqid('no_') . '_' . random_int();
		$id_yes = uniqid('yes_') . '_' . random_int();
		$ch_no  = checked(empty($value));
		$ch_yes = checked(!empty($value));
		$name   = $name_vars . '[' . $name . ']';

		$str = <<<EOD
		 <div class="switch_toggle">
			<input $ch_no type="radio"   name="$name" id="$id_no" value="0"   />
			<label for="$id_no" class="is_off">&nbsp;</label>
			<input $ch_yes type="radio"   name="$name" id="$id_yes" value="1" />
			<label for="$id_yes" class="is_on">&nbsp;</label>
			<span class="slider_toggle"></span>
		</div>
EOD;
		$ret = '<div class="switch flex_h flex_padding flex_align_middle">
				<input' . checked(!empty($value)) . ' name="' . $name_vars . '[' . $name . ']" type="radio" value="1" class="">';
		$ret .= '<label class="flex_h flex_align_middle flex_padding">
				<span class="switch-label switch-label-off">Oui</span></label>';
		$ret .= '   ';
		$ret .= '<label class="flex_h flex_align_middle flex_padding"><input ' . checked(empty($value)) . '  name="' . $name_vars . '[' . $name . ']" type="radio" value="0" class=""> <span>Non</span></label>';
		$ret .= '<span class="switch-selection"></span>';
		$ret .= '</div>';

		return $str;
	}

	function my_array_filter_fn($val) {
		if (is_array($val)) return array_filter($val, "my_array_filter_fn");
		$val          = trim($val);
		$allowed_vals = ["0"]; // Add here your valid values

		return in_array($val, $allowed_vals, true) ? true : ($val ? true : false);
	}

	function my_array_filter_to_time($val) {
		return strtotime($val);;
	}

	function convert_datetime($str) {

		list($date, $time) = explode(' ', $str);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);

		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);

		return $timestamp;
	}

	function calculateMoyenne($Values) {
		$type = '';
		if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $Values[0])) {
			$type   = 'heure';
			$Values = array_map(function ($index) {
				return strtotime(date('Y-m-d') . ' ' . $index) . ' => ';
			}, $Values);

			return date('H:i:s', array_sum($Values) / sizeof($Values));
		}

		return array_sum($Values) / sizeof($Values);
	}

	function calculateMedian($Values) {
		$type = '';
		//Remove array items less than 1
		$Values = array_filter($Values, "my_array_filter_fn");

		if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $Values[0])) {
			$type   = 'heure';
			$Values = array_map(function ($index) {
				return strtotime(date('Y-m-d') . ' ' . $index) . ' => ';
			}, $Values);
		}
		//Sort the array into descending order 1 - ?
		sort($Values, SORT_NATURAL);

		//Find out the total amount of elements in the array
		$Count = count($Values);

		if ($type == '') {
			if ($Count % 2 == 0) {
				return $Values[$Count / 2];
			}

			return (($Values[($Count / 2)] + $Values[($Count / 2) - 1]) / 2);
		}
		if ($type == 'heure') {
			if ($Count % 2 == 0) {
				return date('H:i:s', $Values[$Count / 2]);
			}

			return date('H:i:s', (($Values[($Count / 2)] + $Values[($Count / 2) - 1]) / 2));
		}
	}



	function array_key_diff($aArray1, $aArray2) {
		$aReturn = [];

		foreach ($aArray1 as $mKey => $mValue) {
			if (array_key_exists($mKey, $aArray2)) {
				if (is_array($mValue)) {
					$aRecursiveDiff = array_key_diff($mValue, $aArray2[$mKey]);
					if (count($aRecursiveDiff)) {
						$aReturn[$mKey] = $aRecursiveDiff;
					}
				} else {
					if ($mValue != $aArray2[$mKey]) {
						$aReturn[$mKey] = $mValue;
					}
				}
			} else {
				$aReturn[$mKey] = $mValue;
			}
		}

		return $aReturn;
	}

	function xml2php($xml) {
		$fils  = 0;
		$tab   = false;
		$array = [];
		foreach ($xml->children() as $key => $value) {

			$child = xml2php($value);

			//  To deal with the attributes
			foreach ($value->attributes() as $ak => $av) {
				$array[$ak] = (string)$av;
			}

			//Let see if the new child is not in the array
			if ($tab == false && in_array($key, array_keys($array))) {
				//If this element is already in the array we will create an indexed array
				$tmp           = $array[$key];
				$array[$key]   = null;
				$array[$key][] = $tmp;
				$array[$key][] = $child;
				$tab           = true;
			} elseif ($tab == true) {
				//Add an element in an existing array
				$array[$key][] = $child;
			} else {
				//Add a simple element
				$array[$key] = $child;
			}

			$fils++;
		}

		if ($fils == 0) {
			return (string)$xml;
		}

		return (array)$array;
	}

	function xml2array($xmlObject, $out = []) {
		foreach ((array)$xmlObject as $index => $node) $out[$index] = (is_object($node) || is_array($node)) ? xml2array($node) : $node;

		return $out;
	}

	function soapDebug($client) {

		$requestHeaders  = $client->__getLastRequestHeaders();
		$request         = $client->__getLastRequest();
		$responseHeaders = $client->__getLastResponseHeaders();
		$response        = $client->__getLastResponse(); // prettyXml();

		return ['requestHeaders'  => html_entity_decode($requestHeaders),
		        'request'         => html_entity_decode($request),
		        'responseHeaders' => html_entity_decode($responseHeaders),
		        'response'        => html_entity_decode($response)];
	}

	function scan_dir($directory) {
		$i          = 0;
		$rootDir    = [];
		$tmprootDir = scandir(trim($directory));
		if (!empty($tmprootDir)) {
			foreach ($tmprootDir as $index => $dir) {
				if (is_writable($directory . '/' . $dir) && is_dir($directory . '/' . $dir) && $dir != '.' && $dir != '..' && $dir != '_notes') {
					$rootDir[$i]['name'] = $dir;
					$i++;
					//$rootDir[]['size'] = 30;//disk_total_space($directory.'/'.$dir);
				}
			}
		}

		return (array)$rootDir;
	}

	function scan_files($directory) {
		$i          = 0;
		$rootDir    = [];
		$tmprootDir = scandir($directory);
		foreach ($tmprootDir as $index => $dir) {
			if (is_writable($directory . '/' . $dir) && !is_dir($directory . '/' . $dir) && $dir != '.' && $dir != '..' && $dir != '_notes') {
				$rootDir[$i]['name'] = $dir;
				$i++;
			}
		}

		return (array)$rootDir;
	}

	function idioma($text) {
		return $text;
		if (trim($text) == '') {
			return '';
		}

		return ($final->fields['fr'] != '') ? $final->fields['fr'] : $text;
	}

	function date_mysql($date_origine) {

		$tmp_final_date = "";
		$tmpdate        = explode("/", $date_origine);
		for ($i = (count($tmpdate) - 1); $i >= 0; $i--) {
			if (strlen($tmpdate[$i]) < 2) {
				$tmpdate[$i] = "0" . $tmpdate[$i];
			}
			$tmp_final_date .= $tmpdate[$i];
			if ($i > 0) {
				$tmp_final_date .= "-";
			}
		}

		return $tmp_final_date;

	}

	function date_fr($date_origine) {
		if ($date_origine != "") {
			$tmp_final_date = "";
			$tmpdate        = explode("-", $date_origine);
			for ($i = (count($tmpdate) - 1); $i >= 0; $i--) {
				$tmp_final_date .= $tmpdate[$i];
				if ($i > 0) {
					$tmp_final_date .= "/";
				}
			}
			if ($tmp_final_date == '00/00/0000') {
				return '';
			}

			return $tmp_final_date;
		}
	}

	function sendPost($params = '', $arr1 = '') {
		// on pase le post si instabilité du code
		if ($arr1 == '') {
			$arr1 = array_unique($_POST);
		}
		$arr2 = [];
		parse_str($params, $arr2);
		$tempArray = array_merge((array)$arr1, (array)$arr2);

		return http_build_query($tempArray);
	}


	function maskNbre($number, $idx = 4) {
		if (is_string($number)) {
			$number = (float)$number;
		}

		return number_format($number, $idx, '.', ' ');
	}

	function cleanTel($tel) {
		$tel = str_replace(' ', '', $tel);
		$tel = str_replace('.', '', $tel);
		$tel = str_replace('-', '', $tel);
		$tel = str_replace('/', '', $tel);
		$tel = str_replace(':', '', $tel);

		return $tel;
	}



	function cleanPostMongo($arr, $keepnumerickey = false) {
		unset($arr['F_action']);
		unset($arr['mdl']);
		unset($arr['module']);
		unset($arr['reloadModule']);
		unset($arr['afterAction']);
		unset($arr['_id']);
		if (empty($arr)) {
			return $arr;
		}
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
				if ($arr[$key] == 'true') {
					$arrClean[$key] = (bool)true;
				}
				if ($arr[$key] == 'false') {
					$arrClean[$key] = (bool)false;
				}
				//if(!is_array($arrClean[$key])){if(fonctionsProduction::isTrueFloat($arrClean[$key])) {$arrClean[$key]=(float)$arrClean[$key];} }
				if (isTrueFloat($arrClean[$key])) {
					$arrClean[$key] = (float)$arrClean[$key];
				} elseif (is_numeric($arr[$key])) {
					$arrClean[$key] = (int)$arrClean[$key];
				} elseif (is_numeric(str_replace(' ', '', $arr[$key]))) {
					$arrClean[$key] = (int)str_replace(' ', '', $arr[$key]);
				}
				if (is_array($arr[$key])) {
					$arrClean[$key] = cleanPostMongo($arrClean[$key], $keepnumerickey);
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
		// $arr = cleanPostMongo($arr , $keepnumerickey);
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
					$arrClean[$key] = mysqlToMongo($arrClean[$key], $keepnumerickey);
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

	function isTrueFloat($val) {
		/*if(is_array($val)) return false;
		$pattern = '/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/';
		return (!is_bool($val) && (is_float($val) || preg_match($pattern, trim($val))));*/
		//
		if (is_string($val)) {
			$val = trim($val);
		}
		if (is_numeric($val) && (is_float($val) || ((float)$val > (int)$val || strlen($val) != strlen((int)$val)) && (ceil($val)) != 0)) {
			return true;
		} else {
			return false;
		}
	}


	function ShortUrl($matches) {

		$link_displayed = (strlen($matches[0]) > 35) ? substr($matches[0], 0, 30) . '...' . substr($matches[0], -30) : $matches[0];

		return '<a href="' . $matches[0] . '" title="Se rendre à « ' . $matches[0] . ' »" target="_blank">' . $link_displayed . '</a>';

	}

	function maskTime($secondes) {
		$lHeure     = floor($secondes / 60);
		$lesMinutes = $secondes % 60;

		return ($lHeure . " : " . $lesMinutes);
	}



	function custom_sort_newsblock($a, $b) {
		return (int)$a['sort'] > (int)$b['sort'];
	}


	function sys_log($text) {
		define_syslog_variables();
		openlog(basename(__FILE__), LOG_PID | LOG_PERROR, LOG_LOCAL0);
		syslog(LOG_DEBUG, $text);
		closelog();
	}

	function vardump($value, $return = false) {
		if (!empty($return)) return '<pre class="margin borderb blanc">' . json_encode($value, JSON_PRETTY_PRINT) . '</pre>';
		?>
        <pre class="margin borderb blanc">
    ___<br/> <?php
				echo json_encode($value, JSON_PRETTY_PRINT);
			?>
    </pre>        <?php
	}

	function vardump_async($value, $sticky = false) {
		$debug = ['msg' => vardump($value, 1)];
		if ($sticky) $debug['options'] = ['sticky' => true,
		                                  'id'     => 'cardump'];
		AppSocket::send_cmd('act_notify', $debug, $_COOKIE['PHPSESSID']);
	}

	function printr($value) {

		echo json_encode($value, JSON_PRETTY_PRINT);

	}

	function buffer_flush() {
		echo str_pad(" ", 1024);
		echo '<!-- -->';

		if (ob_get_length()) {
			@ob_flush();
			@flush();
			@ob_end_flush();
		}
		@ob_start();

	}

	function UrlToShortLink($text) {

		//Pattern to retrieve the url in the comment

		$pattern = '`((?:https?|ftp)://\S+?)(?=[[:punct:]]?(?:\s|\Z)|\Z)`';

		//Replacement of the pattern

		$text = preg_replace_callback($pattern, 'ShortUrl', $text);

		return $text;

	}

	function padIt($input, $offset, $padChar, $padConstant = STR_PAD_RIGHT) {
		define('PAD_CONSTANT', $padConstant);
		if ((int)$offset === 0 || strlen($input) == 0 || !isset($padChar) || strlen($padChar) < 1) {
			return $input;
		}            // NOTHING TO PAD
		switch (PAD_CONSTANT) {
			case STR_PAD_LEFT:
				for ($i = 1; $i <= $offset; $i++) $input = "$padChar$input";
				break;
			case STR_PAD_RIGHT:
				for ($i = 1; $i <= $offset; $i++) $input = "$input$padChar";
				break;
			case STR_PAD_BOTH:
				for ($i = 1; $i <= $offset; $i++) $input = "$padChar$input$padChar";
				break;
			default: // DO NOTHING
				break;
		}

		return $input;
	}

	function pourcentage($Nombre, $Total) {
		return round(($Total * 100) / $Nombre, 2);
	}

	function pourcent($kill, $tot) {
		return $kill * $tot / 100;
	}

	function pourcent_add($kill, $tot) {
		return maskNbre(round($tot + pourcent($kill, $tot), 2), 2);
	}

	function calcul_joursferies($month, $day, $year) {
		$resultat = false;

		$jf1 = $year - 1900;
		$jf2 = $jf1 % 19;
		$jf3 = intval((7 * $jf2 + 1) / 19);
		$jf4 = (11 * $jf2 + 4 - $jf3) % 29;
		$jf5 = intval($jf1 / 4);
		$jf6 = ($jf1 + $jf5 + 31 - $jf4) % 7;
		$jfj = 25 - $jf4 - $jf6;
		$jfm = 4;
		if ($jfj <= 0) {
			$jfm = 3;
			$jfj = $jfj + 31;
		}
		$paques    = (($jfm < 10) ? "0" . $jfm : $jfm) . "/" . (($jfj < 10) ? "0" . $jfj : $jfj);
		$lunpaq    = date("m/d", mktime(12, 0, 0, $jfm, $jfj + 1, $year));
		$ascension = date("m/d", mktime(12, 0, 0, $jfm, $jfj + 39, $year));
		$lunpent   = date("m/d", mktime(12, 0, 0, $jfm, $jfj + 50, $year));

		$JourFerie = ["01/01",
		              "05/01",
		              "05/08",
		              "07/14",
		              "08/15",
		              "11/01",
		              "11/11",
		              "12/25",
		              "$paques",
		              "$lunpaq",
		              "$ascension",
		              "$lunpent"];

		$nbj = 0;
		$val = date("m/d", mktime(0, 0, 0, $month, $day, $year));
		while ($nbj < count($JourFerie)) {
			if ($JourFerie[$nbj] == $val) {
				$resultat = true;
				$nbj      = 15;
			}
			$nbj++;
		}

		return ($resultat);
	}


	function auto_code($string) {
		$red     = format_uri($string);
		$red_arr = explode('-', $red);
		$red_arr = array_map(function ($node) {
			return strtoupper(trim(substr($node, 0, 3)));
		}, $red_arr);

		return implode('', $red_arr);
	}

	function format_uri($string, $separator = '-') { // from tac-tac
		$charmap       = ['À' => 'A',
		                  'Á' => 'A',
		                  'Â' => 'A',
		                  'Ã' => 'A',
		                  'Ä' => 'A',
		                  'Å' => 'A',
		                  'Æ' => 'AE',
		                  'Ç' => 'C',
		                  'È' => 'E',
		                  'É' => 'E',
		                  'Ê' => 'E',
		                  'Ë' => 'E',
		                  'Ì' => 'I',
		                  'Í' => 'I',
		                  'Î' => 'I',
		                  'Ï' => 'I',
		                  'Ð' => 'D',
		                  'Ñ' => 'N',
		                  'Ò' => 'O',
		                  'Ó' => 'O',
		                  'Ô' => 'O',
		                  'Õ' => 'O',
		                  'Ö' => 'O',
		                  'Ő' => 'O',
		                  'Ø' => 'O',
		                  'Ù' => 'U',
		                  'Ú' => 'U',
		                  'Û' => 'U',
		                  'Ü' => 'U',
		                  'Ű' => 'U',
		                  'Ý' => 'Y',
		                  'Þ' => 'TH',
		                  'ß' => 'ss',
		                  'à' => 'a',
		                  'á' => 'a',
		                  'â' => 'a',
		                  'ã' => 'a',
		                  'ä' => 'a',
		                  'å' => 'a',
		                  'æ' => 'ae',
		                  'ç' => 'c',
		                  'è' => 'e',
		                  'é' => 'e',
		                  'ê' => 'e',
		                  'ë' => 'e',
		                  'ì' => 'i',
		                  'í' => 'i',
		                  'î' => 'i',
		                  'ï' => 'i',
		                  'ð' => 'd',
		                  'ñ' => 'n',
		                  'ò' => 'o',
		                  'ó' => 'o',
		                  'ô' => 'o',
		                  'õ' => 'o',
		                  'ö' => 'o',
		                  'ő' => 'o',
		                  'ø' => 'o',
		                  'ù' => 'u',
		                  'ú' => 'u',
		                  'û' => 'u',
		                  'ü' => 'u',
		                  'ű' => 'u',
		                  'ý' => 'y',
		                  'þ' => 'th',
		                  'ÿ' => 'y',
		                  '©' => '(c)'];
		$accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
		$special_cases = ['&' => 'et',
		                  "'" => ''];
		$string        = mb_strtolower(trim($string), 'UTF-8');
		$string        = str_replace(array_keys($charmap), $charmap, $string);
		$string        = str_replace(array_keys($special_cases), array_values($special_cases), $string);
		$string        = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
		$string        = preg_replace("/[^a-z0-9\\/]/u", "$separator", $string);
		$string        = preg_replace("/[$separator]+/u", "$separator", $string);

		return $string;
	}

	function niceUrl($text = '') {
		$text = str_replace('--', "-", $text);
		$text = str_replace('- -', "-", $text);
		$text = str_replace('*', "-", $text);
		$text = str_replace('(', "-", $text);
		$text = str_replace(')', "-", $text);
		$text = str_replace(' ', '-', trim(removeaccents($text)));
		$text = str_replace('\\', '-', trim($text));
		$text = str_replace('--', "-", $text);
		$text = str_replace('---', "-", $text);
		$text = str_replace("'", "-", $text);
		$text = str_replace(",", "-", $text);
		$text = str_replace("'", "-", $text);

		return addslashes(strtolower(str_replace('/', '-', str_replace("'", '', trim($text)))));
	}


	function noSpace($text = '') {
		$text = niceUrl($text);

		return str_replace("-", "", $text);
	}


	/**
	 *
	 */
	function nomAgent($id, $type = 'full') {
		$APP = new App('agent'); // verification des droits utilisateur
		$ARR = $APP->findOne(['idagent' => (int)$id]);
		switch ($type) :
			case'full':
				return $ARR['prenomAgent'] . ' ' . $ARR['nomAgent'];
				break;
			case'nom':
				return $ARR['nomAgent'];
				break;
			case'prenom':
				return $ARR['prenomAgent'];
				break;
			case'code':
				return $ARR['codeAgent'];
				break;
			case'login':
				return $ARR['loginAgent'];
				break;
			default:
				return $ARR['nomAgent'];
				break;
		endswitch;

	}

	/**
     * todo move migrate
	 * todo move to class => done
	 *
	 * @param $type_session
	 * @param $code
	 * @param $table
	 *
	 * @return bool
     *
     * @deprecated  droit_table
	 */
	function droit_table($type_session, $code, $table = null) // code = CRUD // rapport au groupe_agent
	{
		$code = strtoupper($code);
		if ($type_session == 'agent') return droit_table_multi($_SESSION["id$type_session"], $code, $table);
		$arr_tbl['livreur']['C'] = ['livreur_affectation'];
		$arr_tbl['livreur']['R'] = ['commande',
		                            'commande_facture',
		                            'livreur_affectation',
		                            'livreur'];
		$arr_tbl['livreur']['U'] = ['livreur_affectation',
		                            'livreur'];
		$arr_tbl['livreur']['L'] = ['commande',
		                            'commande_facture',
		                            'shop',
		                            'livreur_affectation',
		                            'livreur',
		                            'secteur'];
		$arr_tbl['livreur']['D'] = ['livreur_affectation'];
		$arr_tbl['shop']['C']    = ['produit'];
		$arr_tbl['shop']['R']    = ['commande',
		                            'commande_facture',
		                            'produit',
		                            'shop_jours',
		                            'shop_jours_shift',
		                            'shop_jours_shift_run'];// 'produit_categorie',
		$arr_tbl['shop']['U']    = ['commande',
		                            'produit',
		                            'shop',
		                            'shop_jours_shift'];
		$arr_tbl['shop']['L']    = ['commande',
		                            'commande_facture',
		                            'produit',
		                            'shop_jours',
		                            'shop_jours_shift',
		                            'shop_jours_shift_run'];
		$arr_tbl['shop']['D']    = ['produit'];
		//
		if (empty($arr_tbl[$type_session])) return false;
		//
		if (!empty($table)) {
			if (!in_array($table, $arr_tbl[$type_session][$code])) {
				//	echo "$type_session, $code, $table<br>";
				return false;
			}

			return $table;
		} else {
			if (empty($arr_tbl[$type_session][$code])) {
				return false;
			}

			return $arr_tbl[$type_session][$code];
		}
	}
    /** @deprecated  */
	function droit_table_multi($idagent, $code, $table = null) // code = CRUD // rapport au groupe_agent
	{
		$APP    = new App('agent'); // verification des droits utilisateur // code =  $code.'_'.$table
		$APP_GD = new App('agent_groupe_droit'); // verification des droits utilisateur // code =  $code.'_'.$table
		$arr_ag = $APP->findOne(['idagent' => (int)$idagent]);

		if (!empty($table)) {
			$count = $APP_GD->find(['idagent_groupe' => (int)$arr_ag['idagent_groupe'],
			                        'codeAppscheme'  => $table,
			                        $code            => true])->count();
			if ($count == 0) {
				return false;
			}

			return $table;
		} else {
			$dist = $APP_GD->distinct_all('codeAppscheme', ['idagent_groupe' => (int)$arr_ag['idagent_groupe'],
			                                                $code            => true]);

			if (sizeof($dist) == 0) {
				return false;
			}

			return $dist;
		}
	}

	function droit($code) {
		if ($code == 'ADMIN') return true;
		$APP = new App('agent'); // verification des droits utilisateur
		$arr = $APP->findOne(['idagent'            => (int)$_SESSION['idagent'],
		                      'droit_app.' . $code => 1]);
		if (empty($arr['idagent'])) {
			return false;
		}

		return true;
	}

	function removeaccents($texte) {
		//$texte = utf8_decode($texte);
		$texte = str_replace(['à',
		                      'â',
		                      'ä',
		                      'á',
		                      'ã',
		                      'å',
		                      'î',
		                      'ï',
		                      'ì',
		                      'í',
		                      'ô',
		                      'ö',
		                      'ò',
		                      'ó',
		                      'õ',
		                      'ø',
		                      'ù',
		                      'û',
		                      'ü',
		                      'ú',
		                      'é',
		                      'è',
		                      'ê',
		                      'ë',
		                      'ê',
		                      '&',
		                      strtoupper('&'),
		                      'ç',
		                      'ÿ',
		                      'ñ',
		                      '\'',
		                      '"',
		                      '_',
		                      '!',
		                      '?',
		                      '\\'], ['a',
		                              'a',
		                              'a',
		                              'a',
		                              'a',
		                              'a',
		                              'i',
		                              'i',
		                              'i',
		                              'i',
		                              'o',
		                              'o',
		                              'o',
		                              'o',
		                              'o',
		                              'o',
		                              'u',
		                              'u',
		                              'u',
		                              'u',
		                              'e',
		                              'e',
		                              'e',
		                              'e',
		                              'e',
		                              'e',
		                              'e',
		                              'c',
		                              'y',
		                              'n',
		                              '-',
		                              '-',
		                              '-',
		                              '-',
		                              '-',
		                              '-'], $texte);
		$texte = str_replace("--", "-", $texte);
		$texte = str_replace("\\", "-", $texte);

		//$texte = utf8_encode($texte);
		return stripslashes($texte);
	}








	function mois_fr($date_origine) {
		$tabmonth = [1 => "Janvier",
		             "Février",
		             "Mars",
		             "Avril",
		             "Mai",
		             "Juin",
		             "Juillet",
		             "Août",
		             "Septembre",
		             "Octobre",
		             "Novembre",
		             "Décembre"];
		if ($date_origine != "") {
			$tmp_final_date = "";
			$tmpdate        = explode("-", $date_origine);
			for ($i = (count($tmpdate) - 1); $i >= 0; $i--) {
				$tmp_final_date .= $tmpdate[$i];
				if ($i > 0) {
					$tmp_final_date .= "/";
				}
			}
			if ($tmp_final_date == '00/00/0000') {
				return '';
			}

			return $tabmonth[(int)date('m', strtotime($date_origine))];
		}
	}

	function maskTel($tel) {
		if (empty($tel)) {
			return '';
		}
		if (strlen($tel) <= 9) {
			$tel = '0' . $tel;
		}
		$tel = str_replace(' ', '', $tel);
		$tel = str_replace('.', '', $tel);
		$tel = str_replace('-', '', $tel);
		$tel = str_replace('/', '', $tel);
		$tel = strrev(chunk_split(strrev($tel), 2, ' '));
// $tel = str_replace('0 03','003',$tel);
// $tel = str_replace('003 77','00377',$tel);
		return $tel;
	}

	function maskHeure_sweet($tel) {
		if (empty($tel)) {
			return $tel;
		}

		$arrtel = explode(':', $tel);
		$min    = $arrtel[1];
		$ret    = (int)$arrtel[0] . "H " . (($min == '00') ? '' : $min);

		return $ret;
	}

	function maskHeure($tel) {
		if (empty($tel)) {
			return $tel;
		}
		if (is_int($tel)) return $tel;
		$arrtel = explode(':', $tel);
		$ret    = (int)$arrtel[0] . " h " . $arrtel[1];

		return $ret;
	}

	function cf_output($str, $replace = '') {
		if ($replace == '') {
			$replace = idioma('...');
		}
		if (trim($str) == '') {
			return "<span class='textgris'>" . $replace . "</span>";
		}

		return $str;
	}

	function ouiNon($val = '') {
		if ($val == '' || $val == '0') {
			return 'non';
		}

		return 'oui';
	}

	function checked($val = '') {
		if ($val == '' || $val == '0' || empty($val) || $val == false || $val === 'false') { //
			return ' ';
		}

		return " checked='checked' ";
	}

	function selected($val = '') {
		if ($val == '' || $val == '0') {
			return ' ';
		}

		return " selected='selected' ";
	}



	function recursiveDirectoryIterator($directory = null, $files = []) {
		$iterator = new \DirectoryIterator ($directory);

		foreach ($iterator as $info) {
			if ($info->isFile()) {
				$files [$info->__toString()] = $info;
			} elseif (!$info->isDot()) {
				$list = [$info->__toString() => recursiveDirectoryIterator($directory . DIRECTORY_SEPARATOR . $info->__toString())];
				if (!empty($files)) $files = $files;// array_merge_recursive($files, $filest);
				else {
					$files = $list;
				}
			}
		}

		return $files;
	}

	function mostRecentModifiedFileTime($dirName, $doRecursive, $exclude = []) {
		$d            = dir($dirName);
		$lastModified = 0;
		while ($entry = $d->read()) {
			if ($entry != "." && $entry != "..") {
				if (in_array($entry, $exclude)) continue;
				if (!is_dir($dirName . "/" . $entry)) {
					$currentModified = filemtime($dirName . "/" . $entry);
				} else if ($doRecursive && is_dir($dirName . "/" . $entry)) {
					$currentModified = mostRecentModifiedFileTime($dirName . "/" . $entry, true);
				}
				if ($currentModified > $lastModified) {
					$lastModified = $currentModified;
				}
			}
		}
		$d->close();

		return $lastModified;
	}





	if (!function_exists('random_int')) {
		function random_int($min = 1, $max = 99999999) {
			if (!function_exists('mcrypt_create_iv')) {
				trigger_error('mcrypt must be loaded for random_int to work', E_USER_WARNING);

				return null;
			}

			if (!is_int($min) || !is_int($max)) {
				trigger_error('$min and $max must be integer values', E_USER_NOTICE);
				$min = (int)$min;
				$max = (int)$max;
			}

			if ($min > $max) {
				trigger_error('$max can\'t be lesser than $min', E_USER_WARNING);

				return null;
			}

			$range = $counter = $max - $min;
			$bits  = 1;

			while ($counter >>= 1) {
				++$bits;
			}

			$bytes   = (int)max(ceil($bits / 8), 1);
			$bitmask = pow(2, $bits) - 1;

			if ($bitmask >= PHP_INT_MAX) {
				$bitmask = PHP_INT_MAX;
			}

			do {
				$result = hexdec(bin2hex(mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM))) & $bitmask;
			} while ($result > $range);

			return $result + $min;
		}
	}
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

	/**
	 * Prints the margin breakdown of a delivery order. Every figure is hardcoded;
	 * the function takes no input and returns nothing.
	 *
	 * @return void
	 */
	function calcul_marge() {
		$part_coursier = 3;
		$part_shop     = 26;
		$totalcommande = 29;
		$part_stripe   = 1.4;

	}



	/**
	 * A random delay in milliseconds, between `$min` and `$max` minutes.
	 *
	 * @param int $min Minutes
	 * @param int $max Minutes
	 * @return int Milliseconds
	 */
	function delay_minute_random($min = 1, $max = 5) {

		$min = 1000 * 60 * $min;
		$max = 1000 * 60 * $max;

		return rand($min, $max);
	}

	;
	/**
	 * Picks up to `$num` random entries of an array.
	 *
	 * Shuffles its copy, so the order of the result is random too, and returns the
	 * whole array when it is shorter than `$num`.
	 *
	 * @param array $arr
	 * @param int   $num
	 * @return array
	 */
	function array_random($arr, $num = 1) {
		shuffle($arr);
		$num = (sizeof($arr) < $num) ? sizeof($arr) : $num;
		$r   = [];
		for ($i = 0; $i < $num; $i++) {
			$r[] = $arr[$i];
		}

		return $num == 1 ? $r : $r;
	}



	/**
	 * Renders a yes/no radio pair for a scheme field.
	 *
	 * @param string $name      Field name
	 * @param mixed  $value     Current value; empty selects `no`
	 * @param string $name_vars Array the inputs are posted under
	 * @return string HTML
	 */
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

	/**
	 * array_filter() callback that keeps the string `'0'` as well as truthy values.
	 *
	 * Recurses into nested arrays.
	 *
	 * @param mixed $val
	 * @return bool
	 */
	function my_array_filter_fn($val) {
		if (is_array($val)) return array_filter($val, "my_array_filter_fn");
		$val          = trim($val);
		$allowed_vals = ["0"]; // Add here your valid values

		return in_array($val, $allowed_vals, true) ? true : ($val ? true : false);
	}

	/**
	 * array_map() callback turning a date string into a timestamp.
	 *
	 * @param string $val
	 * @return int|false
	 */
	function my_array_filter_to_time($val) {
		return strtotime($val);;
	}

	/**
	 * Parses a `Y-m-d H:i:s` string into a timestamp.
	 *
	 * @param string $str
	 * @return int
	 */
	function convert_datetime($str) {

		list($date, $time) = explode(' ', $str);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);

		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);

		return $timestamp;
	}

	/**
	 * The mean of a set of values.
	 *
	 * Values matching `HH:MM:SS` are averaged as times and the result is formatted
	 * back as a time.
	 *
	 * @param array $Values
	 * @return string|float
	 */
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

	/**
	 * The median of a set of values.
	 *
	 * Empty values are dropped first. Values matching `HH:MM:SS` are handled as
	 * times and the result is formatted back as one.
	 *
	 * @param array $Values
	 * @return string|float
	 */
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



	/**
	 * Recursive diff of two arrays, comparing by key and value.
	 *
	 * @param array $aArray1
	 * @param array $aArray2
	 * @return array Entries of the first array that differ
	 */
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

	/**
	 * Converts a SimpleXML tree into nested arrays, collapsing repeated child names
	 * into lists.
	 *
	 * @param \SimpleXMLElement $xml
	 * @return array
	 */
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

	/**
	 * Converts a SimpleXML tree into nested arrays by casting each node.
	 *
	 * @param \SimpleXMLElement $xmlObject
	 * @param array             $out Accumulator, for the recursion
	 * @return array
	 */
	function xml2array($xmlObject, $out = []) {
		foreach ((array)$xmlObject as $index => $node) $out[$index] = (is_object($node) || is_array($node)) ? xml2array($node) : $node;

		return $out;
	}

	/**
	 * Dumps the last SOAP request and response, headers included.
	 *
	 * The client must have been built with `trace` on.
	 *
	 * @param \SoapClient $client
	 * @return mixed
	 */
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

	/**
	 * Lists the subdirectories of a directory.
	 *
	 * @param string $directory
	 * @return array
	 */
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

	/**
	 * Lists the writable files of a directory, skipping `.`, `..` and `_notes`.
	 *
	 * @param string $directory
	 * @return array
	 */
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

	/**
	 * Translation hook. Currently a pass-through: it returns its argument and the
	 * lookup below it never runs.
	 *
	 * @param string $text
	 * @return string
	 */
	function idioma($text) {
		return $text;
		if (trim($text) == '') {
			return '';
		}

		return ($final->fields['fr'] != '') ? $final->fields['fr'] : $text;
	}

	/**
	 * Converts a `d/m/Y` date into `Y-m-d`.
	 *
	 * @param string $date_origine
	 * @return string
	 */
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

	/**
	 * Converts a `Y-m-d` date into `d/m/Y`. Empty input gives an empty string.
	 *
	 * @param string $date_origine
	 * @return string
	 */
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

	/**
	 * Re-posts the current request's fields to another endpoint.
	 *
	 * @param string $params Target
	 * @param array|string $arr1 Fields to send; defaults to the unique values of `$_POST`
	 * @return mixed
	 */
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


	/**
	 * Formats a number for display: a dot for decimals, a space for thousands.
	 *
	 * @param float|string $number
	 * @param int          $idx Decimal places
	 * @return string
	 */
	function maskNbre($number, $idx = 4) {
		if (is_string($number)) {
			$number = (float)$number;
		}

		return number_format($number, $idx, '.', ' ');
	}

	/**
	 * Strips the separators out of a phone number, leaving digits.
	 *
	 * @param string $tel
	 * @return string
	 */
	function cleanTel($tel) {
		$tel = str_replace(' ', '', $tel);
		$tel = str_replace('.', '', $tel);
		$tel = str_replace('-', '', $tel);
		$tel = str_replace('/', '', $tel);
		$tel = str_replace(':', '', $tel);

		return $tel;
	}



	/**
	 * Prepares a posted array for MongoDB. See Functions\CastFunc::cleanPostMongo(),
	 * which is the same routine in class form.
	 *
	 * @param array $arr
	 * @param bool  $keepnumerickey
	 * @return array
	 */
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

	/**
	 * Converts a MySQL-shaped row into its MongoDB equivalent, rewriting `x_id` keys
	 * to `idx`.
	 *
	 * @param array $arr
	 * @param bool  $keepnumerickey
	 * @return array
	 */
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

	/**
	 * Whether a value is a decimal number rather than an integer.
	 *
	 * Used to decide between an int and a float cast when coercing posted values.
	 *
	 * @param mixed $val
	 * @return bool
	 */
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


	/**
	 * preg_replace_callback() callback that renders a URL as a link, shortening the
	 * text past 35 characters.
	 *
	 * @param array $matches
	 * @return string HTML
	 */
	function ShortUrl($matches) {

		$link_displayed = (strlen($matches[0]) > 35) ? substr($matches[0], 0, 30) . '...' . substr($matches[0], -30) : $matches[0];

		return '<a href="' . $matches[0] . '" title="Se rendre à « ' . $matches[0] . ' »" target="_blank">' . $link_displayed . '</a>';

	}

	/**
	 * Formats a duration as `H : M`.
	 *
	 * Note that it divides by 60 once, so it reads minutes as hours and seconds as
	 * minutes: pass it minutes, despite the parameter name.
	 *
	 * @param int $secondes
	 * @return string
	 */
	function maskTime($secondes) {
		$lHeure     = floor($secondes / 60);
		$lesMinutes = $secondes % 60;

		return ($lHeure . " : " . $lesMinutes);
	}



	/**
	 * usort() comparator ordering news blocks by their `sort` field.
	 *
	 * Returns a bool rather than -1/0/1, which usort() reads as 1 or 0: equal and
	 * lesser compare the same, so the sort is not stable across them.
	 *
	 * @param array $a
	 * @param array $b
	 * @return bool
	 */
	function custom_sort_newsblock($a, $b) {
		return (int)$a['sort'] > (int)$b['sort'];
	}


	/**
	 * Writes a debug line to syslog under LOG_LOCAL0.
	 *
	 * @param string $text
	 * @return void
	 */
	function sys_log($text) {
		define_syslog_variables();
		openlog(basename(__FILE__), LOG_PID | LOG_PERROR, LOG_LOCAL0);
		syslog(LOG_DEBUG, $text);
		closelog();
	}

	/**
	 * Dumps a value as pretty JSON in a styled block.
	 *
	 * @param mixed $value
	 * @param bool  $return True returns the HTML instead of echoing it
	 * @return string|void
	 */
	function vardump($value, $return = false) {
		if (!empty($return)) return '<pre class="margin borderb blanc">' . json_encode($value, JSON_PRETTY_PRINT) . '</pre>';
		?>
        <pre class="margin borderb blanc">
    ___<br/> <?php
				echo json_encode($value, JSON_PRETTY_PRINT);
			?>
    </pre>        <?php
	}

	/**
	 * Pushes a dump to the current session's browser over the socket.
	 *
	 * @param mixed $value
	 * @param bool  $sticky Keep the notification until dismissed
	 * @return void
	 */
	function vardump_async($value, $sticky = false) {
		$debug = ['msg' => vardump($value, 1)];
		if ($sticky) $debug['options'] = ['sticky' => true,
		                                  'id'     => 'cardump'];
		AppSocket::send_cmd('act_notify', $debug, $_COOKIE['PHPSESSID']);
	}

	/**
	 * Echoes a value as pretty JSON, unstyled.
	 *
	 * @param mixed $value
	 * @return void
	 */
	function printr($value) {

		echo json_encode($value, JSON_PRETTY_PRINT);

	}

	/**
	 * Forces buffered output out to the browser, padding to get past the buffer
	 * sizes proxies and browsers hold onto.
	 *
	 * @return void
	 */
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

	/**
	 * Turns the bare URLs in a text into shortened links.
	 *
	 * @param string $text
	 * @return string HTML
	 */
	function UrlToShortLink($text) {

		//Pattern to retrieve the url in the comment

		$pattern = '`((?:https?|ftp)://\S+?)(?=[[:punct:]]?(?:\s|\Z)|\Z)`';

		//Replacement of the pattern

		$text = preg_replace_callback($pattern, 'ShortUrl', $text);

		return $text;

	}

	/**
	 * Pads a string to a length.
	 *
	 * Defines the global constant `PAD_CONSTANT` on first use, so the direction of
	 * the very first call is the one every later call uses.
	 *
	 * @param string $input
	 * @param int    $offset      Target length
	 * @param string $padChar
	 * @param int    $padConstant STR_PAD_RIGHT, STR_PAD_LEFT or STR_PAD_BOTH
	 * @return string
	 */
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

	/**
	 * What percentage `$Total` is of `$Nombre`. Note the argument order: the base
	 * comes second.
	 *
	 * @param float $Nombre Base
	 * @param float $Total  Part
	 * @return float
	 */
	function pourcentage($Nombre, $Total) {
		return round(($Total * 100) / $Nombre, 2);
	}

	/**
	 * `$kill` percent of `$tot`.
	 *
	 * @param float $kill Percentage
	 * @param float $tot  Base
	 * @return float
	 */
	function pourcent($kill, $tot) {
		return $kill * $tot / 100;
	}

	/**
	 * `$tot` with `$kill` percent added, formatted to two decimals.
	 *
	 * @param float $kill Percentage
	 * @param float $tot  Base
	 * @return string
	 */
	function pourcent_add($kill, $tot) {
		return maskNbre(round($tot + pourcent($kill, $tot), 2), 2);
	}

	/**
	 * Whether a date is a French public holiday, fixed or Easter-derived.
	 *
	 * @param int $month
	 * @param int $day
	 * @param int $year
	 * @return bool
	 */
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


	/**
	 * Builds a short uppercase code from a label, three letters per word.
	 *
	 * @param string $string
	 * @return string
	 */
	function auto_code($string) {
		$red     = format_uri($string);
		$red_arr = explode('-', $red);
		$red_arr = array_map(function ($node) {
			return strtoupper(trim(substr($node, 0, 3)));
		}, $red_arr);

		return implode('', $red_arr);
	}

	/**
	 * Slugifies a string: accents folded, non-word characters replaced by the
	 * separator.
	 *
	 * @param string $string
	 * @param string $separator
	 * @return string
	 */
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

	/**
	 * Tidies an already-slugified string, collapsing repeated and stray separators.
	 *
	 * @param string $text
	 * @return string
	 */
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


	/**
	 * niceUrl() with the separators removed as well.
	 *
	 * @param string $text
	 * @return string
	 */
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

	/**
	 * Whether the current session holds a permission.
	 *
	 * `ADMIN` is always granted, without a lookup.
	 *
	 * @param string $code Permission code
	 * @return bool
	 */
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

	/**
	 * Folds accented characters to their unaccented equivalents.
	 *
	 * @param string $texte
	 * @return string
	 */
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








	/**
	 * The French month name for a date.
	 *
	 * @param string $date_origine `Y-m-d`
	 * @return string
	 */
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

	/**
	 * Formats a French phone number in pairs, restoring a leading zero when the
	 * number is nine digits or shorter.
	 *
	 * @param string $tel
	 * @return string
	 */
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

	/**
	 * Formats a time for display, dropping the parts that are zero.
	 *
	 * @param string $tel `H:i:s`
	 * @return string
	 */
	function maskHeure_sweet($tel) {
		if (empty($tel)) {
			return $tel;
		}

		$arrtel = explode(':', $tel);
		$min    = $arrtel[1];
		$ret    = (int)$arrtel[0] . "H " . (($min == '00') ? '' : $min);

		return $ret;
	}

	/**
	 * Formats a time as `H:i`. An integer is returned unchanged.
	 *
	 * @param string|int $tel
	 * @return string|int
	 */
	function maskHeure($tel) {
		if (empty($tel)) {
			return $tel;
		}
		if (is_int($tel)) return $tel;
		$arrtel = explode(':', $tel);
		$ret    = (int)$arrtel[0] . " h " . $arrtel[1];

		return $ret;
	}

	/**
	 * Echoes a value, falling back to greyed placeholder text when it is empty.
	 *
	 * @param string $str
	 * @param string $replace Placeholder; defaults to an ellipsis
	 * @return string HTML
	 */
	function cf_output($str, $replace = '') {
		if ($replace == '') {
			$replace = idioma('...');
		}
		if (trim($str) == '') {
			return "<span class='textgris'>" . $replace . "</span>";
		}

		return $str;
	}

	/**
	 * Renders a boolean as `oui` or `non`. The string `'0'` counts as false.
	 *
	 * @param mixed $val
	 * @return string
	 */
	function ouiNon($val = '') {
		if ($val == '' || $val == '0') {
			return 'non';
		}

		return 'oui';
	}

	/**
	 * The `checked` attribute when a value is truthy, otherwise a space.
	 *
	 * The strings `'0'` and `'false'` both count as false.
	 *
	 * @param mixed $val
	 * @return string
	 */
	function checked($val = '') {
		if ($val == '' || $val == '0' || empty($val) || $val == false || $val === 'false') { //
			return ' ';
		}

		return " checked='checked' ";
	}

	/**
	 * The `selected` attribute when a value is truthy, otherwise a space.
	 *
	 * @param mixed $val
	 * @return string
	 */
	function selected($val = '') {
		if ($val == '' || $val == '0') {
			return ' ';
		}

		return " selected='selected' ";
	}



	/**
	 * Lists every file under a directory, recursively.
	 *
	 * @param string $directory
	 * @param array  $files Accumulator, for the recursion
	 * @return \SplFileInfo[] Keyed by filename
	 */
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

	/**
	 * The modification time of the most recently changed file in a directory.
	 *
	 * @param string $dirName
	 * @param bool   $doRecursive Descend into subdirectories
	 * @param array  $exclude     Entry names to skip
	 * @return int Unix timestamp; 0 when nothing matched
	 */
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
		/**
		 * Polyfill of the PHP 7 function, for older runtimes.
		 *
		 * Needs the mcrypt extension; warns and returns null without it, and for an
		 * inverted range.
		 *
		 * @param int $min
		 * @param int $max
		 * @return int|null
		 */
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
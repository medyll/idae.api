<?

	class AppSocket {
		static function cf_module($module, $array = [], $value = '', $attributes = '') {
			require($_SERVER['CONF_INC']);

			if (trim($module == ''))  return '';
			if (empty($array)) $array = [];
			//
			//
			$moduleId  = 'id' . uniqid();
			$moduleTag = (empty($array['moduleTag'])) ? 'div' : $array['moduleTag'];
			$value     = (empty($value)) ? empty($array['table_value']) ? 'mdl_' . md5($module) : $array['table_value'] : $value;

			//
			ksort($array);
			$arrQuery = $array;
			unset($arrQuery['module'],
				$arrQuery['mdl'],
				$arrQuery['PHPSESSID'],
				$arrQuery['moduleTag'],
				$arrQuery['className'],
				$arrQuery['defer'],
				$arrQuery['cacheOn']);
			$theQuery = ($array != '') ? http_build_query($arrQuery) : '';

			$className = empty($array['className']) ? '' : $array['className'];
			$act_defer = empty($array['defer']) ? 'no_defer' : 'act_defer';

			$data_scope  = (empty($arrQuery['scope'])) ? '' : 'scope="' . $arrQuery['scope'] . '"';
			$data_string = (empty($arrQuery['table'])) ? '' : 'data-table="' . $arrQuery['table'] . '"';
			$data_string .= (empty($arrQuery['table_value'])) ? '' : ' data-table_value="' . $arrQuery['table_value'] . '"';

			$start = ($moduleTag != 'none') ? "<$moduleTag $data_string $act_defer $attributes $data_scope class='cf_module $className' mdl='$module' vars='$theQuery' value='$value' id='$moduleId' >" : ""; //  title='$module=$value'
			$end   = ($moduleTag != 'none') ? "</$moduleTag>" : "";

			ob_start();
			$final = '';
			$final .= $start;
			if (file_exists(APPMDL . '/' . $module . '.php')) {

				if (empty($array['defer'])) {
					if (empty($array['emptyModule'])) {
						$tempPost        = $_POST;
						$_POST           = $arrQuery;
						// $_POST['MODULE'] = $module;
						include(APPMDL . '/' . $module . '.php');
						$_POST = $tempPost;
						//$final .= AppSocket::doCurl(HTTPMDL . $module . '.php' , $array);
					}
				} else {
					// echo('scripo');

				}
			} else {
				// $final .= "missing" . APPMDL . '/' . $module . '.php';
			}
			$final .= $end;

			$final = ob_get_contents();
			$final = $start . $final . $end;
			ob_end_clean();

			return trim($final);

		}

		static function reloadModule($module, $value='*', $vars = [],$room = null) {
			$arrjson = ['timeStamp' => (int)time(), 'module' => $module, 'value' => $value,'room'=>$room];
			if (sizeof($vars) != 0) {
				$arrjson['vars'] = $vars;
			}

			AppSocket::send_cmd('act_reload_module',$arrjson,$room);
			//return $dozat;
		}
		static function reloadMdlTest($module, $value='*', $vars = [],$room = null) {

			$arrjson = ['timeStamp' => (int)time(), 'mdl_test' => $module, 'value' => $value,'room'=>$room];
			if (sizeof($vars) != 0) {
				$arrjson['vars'] = $vars;
			}

			AppSocket::send_cmd('act_reload_module',$arrjson,$room);
			//return $dozat;
		}

		function doCurl($module, $array = []) {
			/*$ckfile = COOKIE_PATH . "cookie.txt";
			$fp     = fopen($ckfile, "w");
			fclose($fp);*/
			$array['iscurlmdl'] = '1';
			$strcookie          = session_name() . "=" . session_id() . "; path=" . session_save_path();
			// session_write_close();
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($array));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_COOKIESESSION, TRUE);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			// curl_setopt($curl, CURLOPT_HTTPHEADER,array("Expect:"));
			/*curl_setopt($curl , CURLOPT_COOKIEFILE , $ckfile);
			curl_setopt($curl , CURLOPT_COOKIEJAR , $ckfile);*/
			curl_setopt($curl, CURLOPT_COOKIE, $strcookie);
			curl_setopt($curl, CURLOPT_URL, $module); //
			curl_setopt($curl, CURLOPT_POST, 1);
			$page = curl_exec($curl);
			if ($page === false) {
				// trigger_error('Erreur curl : ' . curl_error($curl) . ' ' . $module, E_USER_WARNING);
			}
			// curl_close($curl);

			//unlink($ckfile);
			return ($page);
		}

		static function run($mdl, $vars = [], $room = '') {

			$arrjson = ['mdl' => $mdl];
			if (sizeof($vars) != 0) {
				$arrjson['vars'] = $vars;
			}
			if (!empty($room)) {
				$arrjson['OWN'] = $room;
			}

			AppSocket::doPost(HTTPHOSTNOPORT . ':' . SOCKETIO_PORT . '/run', $arrjson);
		}

		static function doPost($url, $vars = []) {

			$crlf       = "\r\n";
			$parts      = parse_url($url);
			$cookie_str = session_name() . "=" . session_id(). "; path=" . session_save_path();

     		//
			// $fp = fsockopen('ssl://'.$parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
			$fp = fsockopen($_SERVER['HTTP_HOST'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

			$vars['DOCUMENTDOMAIN'] = DOCUMENTDOMAIN;
			$vars['PHPSESSID'] = session_id();
			$query                  = http_build_query($vars);


			if (!$fp) {

				// AppSocket::send_cmd('act_notify', ['msg' => 'ERREUR ' . $errstr . '  $errno ' . $errno, session_id()]);

				return false;
			} else {
				$out = "POST " . $parts['path'] . " HTTP/1.1" . $crlf;
				$out .= "Host: ".  $parts['scheme'].'://'.  $parts['host'].':'.$parts['port'] . $crlf;
				$out .= "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0" . $crlf;
				$out .= "Origin: https://tac-tac.shop.mydde.fr" . $crlf;
				$out .= "Content-Type: application/x-www-form-urlencoded" . $crlf;
				$out .= "Content-Length: " . strlen($query) . $crlf;
				$out .= "Connection: Close" . $crlf;
				$out .= $crlf;

				if (isset($query)) {
					$out .= $query;
				}
				if (!empty($cookie_str)) {
					$out .= 'Cookie: ' . substr($cookie_str, 0, -2) . $crlf;
				}

				fwrite($fp, $out);
				fclose($fp);

				return true;
			}
		}

		static function send_cmd($cmd, $vars = [], $room = '') {
			$arrjson = ['timeStamp' => (int)time(), 'cmd' => $cmd];
			//
			if (sizeof($vars) != 0) {
				$arrjson['vars'] = $vars;
			}
			//
			if (!empty($room)) {
				$arrjson['OWN'] = $room;
			}
			//

			AppSocket::doPost(SOCKETIO_HOST . ':' . SOCKETIO_PORT . '/postReload', $arrjson);

		}
		static function send_grantIn( $vars = [], $room = '') {
			$arrjson = ['timeStamp' => (int)time(), 'vars' => $vars];
			//

			//
			if (!empty($room)) {
				$arrjson['OWN'] = $room;
			}
			//

			AppSocket::doPost(SOCKETIO_HOST . ':' . SOCKETIO_PORT . '/postGrantIn', $arrjson);

		}


		static function runSocketModule($mdl, $vars = [], $room = '') {
			$arrjson = ['timeStamp' => (int)time(), 'mdl' => $mdl, 'PHPSESSID' => session_id()];
			if (sizeof($vars) != 0) {
				$arrjson['vars'] = $vars;
			}
			if (!empty($room)) {
				$arrjson['OWN'] = $room;
			}

			AppSocket::doPost(HTTPMDL . $mdl . '.php', $vars);
		}

		static function runModule($mdl, $vars = [], $room = '') {
			$arrjson = ['timeStamp' => (int)time(), 'mdl' => $mdl, 'PHPSESSID' => session_id()];
			if (sizeof($vars) != 0) {
				$arrjson['vars'] = $vars;
			}
			if (!empty($room)) {
				$arrjson['OWN'] = $room;
			}

			$arrjson['str_cookie'] = session_name() . "=" . session_id() . "; path=" . session_save_path();
			AppSocket::doPost(HTTPHOSTNOPORT . ':' . SOCKETIO_PORT . '/runModule', $arrjson);
		}

		function _construct() {

		}

		function wrapIt($content) {
			$start = ($moduleTag != 'none') ? "<$moduleTag $attributes class='cf_module $className' mdl='$module' vars='$theQuery' value='$value'>" : "";
			$end   = ($moduleTag != 'none') ? "</$moduleTag>" : "";

			return $start . $content . $end;
		}

		function doInclude($module, $MDLPOST = []) {
			$MDLPOST;
			//
			$moduleid = AppSocket::getModuleId($module, $MDLPOST);
			//
			$needCache = AppSocket::testNeedCache($module, $MDLPOST);
			//
			if ($needCache === true):
				$dsp = AppSocket::getCache($module, $MDLPOST);
			else:
				ob_start();
				include(APPMDL . '/' . $module . '.php');
				$dsp = ob_get_contents();
				ob_end_clean();
				AppSocket::writeCache($module, $MDLPOST, $dsp);
			endif;

			return $dsp;
		}

		function getModuleId($module, $theQuery) {
			unset($theQuery['cacheTime']);
			unset($theQuery['cacheOn']);
			unset($theQuery['mdl']);
			unset($theQuery['scope']);
			unset($theQuery['moduleTag']);
			unset($theQuery['className']);
			unset($theQuery['HTTP_REFERER']);
			unset($theQuery['REMOTE_ADDR']);
			unset($theQuery['load_time']);
			unset($theQuery['session_id']);
			unset($theQuery['time']);
			ksort($theQuery);

			$add      = http_build_query($theQuery);
			$moduleid = md5($module . $add);

			return $moduleid;
		}

		function testNeedCache($module, $theQuery) {
			//
			$moduleid = AppSocket::getModuleId($module, $theQuery);
			//
			//
			$timecache = (defined(CACHETIME)) ? 43200 : CACHETIME;
			$timecache = (!empty($theQuery['cacheTime'])) ? $theQuery['cacheTime'] : $timecache;
			$timecache = (defined(CACHETIME)) ? CACHETIME : $timecache;
			//
			$maxAge = time() - $timecache;
			//
			$APP      = new App();
			$conBase  = $APP->plug_base('sitebase_cache');
			$gridBase = $conBase->getGridFs();
			//
			$testBase = $gridBase->find(['filename' => $moduleid . '.html'])->count();
			if ($testBase == 0) {
				return false;
			}
			//
			$testBase = $gridBase->find(['time' => ['$lte' => (int)$maxAge], 'filename' => $moduleid . '.html'])->count();
			if ($testBase > 1) {
				return true;
			}

			return false;
		}

		function getCache($module, $theQuery) {
			$APP      = new App();
			$conBase  = $APP->plug_base('sitebase_cache');
			$gridBase = $conBase->getGridFs();
			//
			$moduleid = AppSocket::getModuleId($module, $theQuery);
			//
			$testBase = $gridBase->findOne(['filename' => $moduleid . '.html']);

			return $testBase->getBytes();
		}

		function writeCache($module, $array, $final) {
			//
			$moduleid = AppSocket::getModuleId($module, $array);
			//
			$APP     = new App();
			$conBase = $APP->plug_base('sitebase_cache');
			$Fs      = $conBase->getGridFS();
			//
			$Fs->remove(['filename' => $moduleid . '.html']);
			//
			$pattern  = '/(?:(?<=\>)|(?<=\/\>))(\s+)(?=\<\/?)/';
			$newfinal = preg_replace($pattern, "", $final);

			$obj             = ['filename' => $moduleid . '.html', 'time' => (int)time(), "module" => $module, 'date' => new MongoDate(), 'uploadDate' => new MongoDate()];
			$obj['metadata'] = $array;
			//
			$Fs->storeBytes($newfinal, $obj);
		}

		function urlCache($dsp, $url) {
			if (!AppSocket::testCacheUrl($url)):
				// AppSocket::setCacheUrl($dsp, $url);

				return $dsp;
			endif;
		}

		function testCacheUrl($url) {
			//
			$timecache = (defined(CACHETIME)) ? 43200 : CACHETIME;
			$timecache = (!empty($theQuery['cacheTime'])) ? $theQuery['cacheTime'] : $timecache;
			$timecache = (defined(CACHETIME)) ? CACHETIME : $timecache;
			//
			$maxAge = time() - $timecache;
			//
			$APP      = new App();
			$conBase  = $APP->plug_base('sitebase_cache');
			$gridBase = $conBase->getGridFs();
			//
			$testBase = $gridBase->findOne(['filename' => $url]);
			//
			$obj = $testBase->file;
			if (empty($obj['filename']) || $obj['time'] < $maxAge) {
				return false;
			}
			$oldDate = $obj['time'];
			$curDate = time();
			$diff    = $curDate - $oldDate;

			if ($timecache > $diff) {
				return true;
			}

			return false;
		}

		function dropCache() {
			$APP     = new App();
			$conBase = $APP->plug_base('sitebase_cache');
			$conBase->drop();
		}

		function getCacheUrl($url) {
			$APP      = new App();
			$conBase  = $APP->plug_base('sitebase_cache');
			$gridBase = $conBase->getGridFs();
			//
			$testBase = $gridBase->findOne(['filename' => $url]);

			return $testBase->getBytes();
		}

		function setCacheUrl($dsp, $url) {
			$APP     = new App();
			$conBase = $APP->plug_base('sitebase_cache');

			$Fs = $conBase->getGridFS();
			//
			$Fs->remove(['filename' => $url]);

			$obj = ['filename' => $url, 'time' => time(), 'date' => date('Y-m-d')];
			//
			$Fs->storeBytes($dsp, $obj);
		}

		function cleanModuleVars($module, $theQuery) {
			unset($theQuery['cacheTime']);
			unset($theQuery['cacheOn']);
			unset($theQuery['mdl']);
			unset($theQuery['scope']);
			unset($theQuery['moduleTag']);
			unset($theQuery['className']);
			unset($theQuery['HTTP_REFERER']);
			unset($theQuery['REMOTE_ADDR']);
			unset($theQuery['load_time']);
			unset($theQuery['session_id']);
			unset($theQuery['time']);
			ksort($theQuery);

			$add      = http_build_query($theQuery);
			$moduleid = md5($module . $add);

			return $moduleid;
		}
	}

?>
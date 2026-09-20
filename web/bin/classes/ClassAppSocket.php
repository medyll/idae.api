<?

	/**
	 * Renders modules server-side and pushes updates to the browser through the
	 * socket.io bridge.
	 *
	 * Two halves: the `cf_module`/`doInclude`/`*Cache` methods render a module and
	 * cache its output in GridFS, and the `send_*`/`reload*`/`run*` methods POST a
	 * JSON command to the socket server, which relays it to the connected clients.
	 * `$room` narrows a command to one session; empty means everyone.
	 */
	class AppSocket {
		/**
		 * Renders a module and returns its HTML, wrapped in its module tag.
		 *
		 * The wrapper carries the module name and its query, which is what lets the
		 * client reload just this module later.
		 *
		 * @param string $module     Module path
		 * @param array  $array      Module variables; `moduleTag`, `className` and
		 *                           `cacheOn` steer the wrapper
		 * @param string $value      Value the module is bound to
		 * @param string $attributes Extra attributes on the wrapper
		 * @return string HTML
		 */
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

		/**
		 * Tells the connected clients to reload a module.
		 *
		 * @param string      $module
		 * @param string      $value Value to reload; `*` means every instance
		 * @param array       $vars
		 * @param string|null $room  Session to target; null means everyone
		 * @return void
		 */
		static function reloadModule($module, $value='*', $vars = [],$room = null) {
			$arrjson = ['timeStamp' => (int)time(), 'module' => $module, 'value' => $value,'room'=>$room];
			if (sizeof($vars) != 0) {
				$arrjson['vars'] = $vars;
			}

			AppSocket::send_cmd('act_reload_module',$arrjson,$room);
			//return $dozat;
		}
		/**
		 * reloadModule() against the test module channel.
		 *
		 * @param string      $module
		 * @param string      $value
		 * @param array       $vars
		 * @param string|null $room
		 * @return void
		 */
		static function reloadMdlTest($module, $value='*', $vars = [],$room = null) {

			$arrjson = ['timeStamp' => (int)time(), 'mdl_test' => $module, 'value' => $value,'room'=>$room];
			if (sizeof($vars) != 0) {
				$arrjson['vars'] = $vars;
			}

			AppSocket::send_cmd('act_reload_module',$arrjson,$room);
			//return $dozat;
		}

		/**
		 * Fetches a module over HTTP, forwarding the current session cookie.
		 *
		 * @param string $module
		 * @param array  $array
		 * @return string
		 */
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

		/**
		 * Runs a module on the server without returning its output.
		 *
		 * @param string $mdl
		 * @param array  $vars
		 * @param string $room
		 * @return void
		 */
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

		/**
		 * POSTs a payload, forwarding the current session cookie.
		 *
		 * This is what every send_* and run* method goes through.
		 *
		 * @param string $url
		 * @param array  $vars
		 * @return string
		 */
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
				$out .= "Origin: ".HTTPHOST."" . $crlf;
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

		/**
		 * Sends a command to the connected clients through the socket server.
		 *
		 * @param string $cmd  Command name, e.g. `act_notify` or `act_reload_module`
		 * @param array  $vars Command payload
		 * @param string $room Session to target; empty means everyone
		 * @return void
		 */
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
		/**
		 * Pushes a permission grant to the socket server.
		 *
		 * @param array  $vars
		 * @param string $room Session to target; empty means everyone
		 * @return void
		 */
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


		/**
		 * Runs a module by POSTing straight to its PHP file.
		 *
		 * Note that it builds a payload carrying the session and room but then posts
		 * `$vars`, so neither reaches the module.
		 *
		 * @param string $mdl
		 * @param array  $vars
		 * @param string $room
		 * @return void
		 */
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

		/**
		 * Asks the socket server to run a module, passing the session cookie along so
		 * it runs as the current user.
		 *
		 * @param string $mdl
		 * @param array  $vars
		 * @param string $room
		 * @return void
		 */
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

		/**
		 * Not a constructor: the name is missing a leading underscore, so PHP never
		 * calls it. Every method here is static anyway.
		 */
		function _construct() {

		}

		/**
		 * Wraps rendered content in its module tag.
		 *
		 * Broken as it stands: it reads `$moduleTag`, `$attributes` and the rest as
		 * local variables that are never set, so the wrapper always comes out empty.
		 *
		 * @param string $content
		 * @return string
		 */
		function wrapIt($content) {
			$start = ($moduleTag != 'none') ? "<$moduleTag $attributes class='cf_module $className' mdl='$module' vars='$theQuery' value='$value'>" : "";
			$end   = ($moduleTag != 'none') ? "</$moduleTag>" : "";

			return $start . $content . $end;
		}

		/**
		 * Includes a module's PHP file and returns its output, serving the cached copy
		 * when there is a fresh one.
		 *
		 * @param string $module
		 * @param array  $MDLPOST Module variables
		 * @return string
		 */
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

		/**
		 * The cache key for a module render: a hash of its name and its variables, with
		 * the ones that do not affect the output stripped out first.
		 *
		 * @param string $module
		 * @param array  $theQuery
		 * @return string
		 */
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

		/**
		 * Whether a module's cached copy is missing or stale.
		 *
		 * @param string $module
		 * @param array  $theQuery
		 * @return bool
		 */
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

		/**
		 * Reads a module's cached output from GridFS.
		 *
		 * @param string $module
		 * @param array  $theQuery
		 * @return string|null
		 */
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

		/**
		 * Stores a module's rendered output in GridFS.
		 *
		 * @param string $module
		 * @param array  $array Module variables, for the cache key
		 * @param string $final  Rendered output
		 * @return void
		 */
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

		/**
		 * Returns the cached copy of a URL, or the content it was given when there is
		 * no fresh copy.
		 *
		 * @param string $dsp Content to fall back on
		 * @param string $url
		 * @return string
		 */
		function urlCache($dsp, $url) {
			if (!AppSocket::testCacheUrl($url)):
				// AppSocket::setCacheUrl($dsp, $url);

				return $dsp;
			endif;
		}

		/**
		 * Whether a URL's cached copy is still within its lifetime.
		 *
		 * @param string $url
		 * @return bool
		 */
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

		/**
		 * Drops the whole cache database. Destructive: every cached module and URL goes.
		 *
		 * @return void
		 */
		function dropCache() {
			$APP     = new App();
			$conBase = $APP->plug_base('sitebase_cache');
			$conBase->drop();
		}

		/**
		 * Reads a URL's cached content from GridFS.
		 *
		 * @param string $url
		 * @return string|null
		 */
		function getCacheUrl($url) {
			$APP      = new App();
			$conBase  = $APP->plug_base('sitebase_cache');
			$gridBase = $conBase->getGridFs();
			//
			$testBase = $gridBase->findOne(['filename' => $url]);

			return $testBase->getBytes();
		}

		/**
		 * Stores content against a URL, replacing any copy already there.
		 *
		 * @param string $dsp Content
		 * @param string $url
		 * @return void
		 */
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

		/**
		 * Strips the request-specific variables out of a module's query and returns the
		 * resulting cache key.
		 *
		 * @param string $module
		 * @param array  $theQuery
		 * @return string
		 */
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

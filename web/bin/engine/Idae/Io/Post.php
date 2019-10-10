<?php

	namespace Idae\Io;

	use function fclose;
	use function fsockopen;
	use function fwrite;
	use function http_build_query;
	use function parse_url;
	use function session_id;
	use function session_name;
	use function session_save_path;
	use function strlen;
	use function substr;
	use const CURLOPT_CRLF;
	use const DOCUMENTDOMAIN;
	use const HTTPHOST;

	class Post {

		/**
		 * Post constructor.
		 */
		public function __construct() {


		}

		/**
		 * @param       $url
		 * @param array $vars
		 *
		 * @return bool
		 * @throws \Exception
		 */
		static function doPost($url, $vars = []) {

			$crlf       = CURLOPT_CRLF;
			$parts      = parse_url($url);

			$cookie_str = session_name() . "=" . session_id(). "; path=" . session_save_path();

			$fp = fsockopen($_SERVER['HTTP_HOST'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

			$vars['DOCUMENTDOMAIN'] = DOCUMENTDOMAIN;
			$vars['PHPSESSID'] = session_id();
			$query                  = http_build_query($vars);


			if (!$fp) {
				// AppSocket::send_cmd('act_notify', ['msg' => 'ERREUR ' . $errstr . '  $errno ' . $errno, session_id()]);
				throw new \Exception("url $url is unreachable", 'UNREACHABLE_HOST', true);
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
	}

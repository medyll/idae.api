<?php

	namespace Idae\Io;

	use function array_merge;
	use function curl_close;
	use function curl_errno;
	use function curl_error;
	use function curl_exec;
	use function curl_init;
	use function curl_setopt;
	use function curl_setopt_array;
	use function fclose;
	use function fsockopen;
	use function fwrite;
	use function http_build_query;
	use function parse_url;
	use function session_id;
	use function session_name;
	use function session_save_path;
	use function strlen;
	use function strpos;
	use function substr;
	use function trigger_error;
	use function var_dump;
	use const CURLOPT_COOKIE;
	use const CURLOPT_COOKIESESSION;
	use const CURLOPT_CRLF;
	use const CURLOPT_FAILONERROR;
	use const CURLOPT_HEADER;
	use const CURLOPT_POST;
	use const CURLOPT_POSTFIELDS;
	use const CURLOPT_RETURNTRANSFER;
	use const CURLOPT_SSL_VERIFYHOST;
	use const CURLOPT_SSL_VERIFYPEER;
	use const CURLOPT_URL;
	use const DOCUMENTDOMAIN;
	use const E_USER_WARNING;
	use const HTTPHOST;

	class Send {

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
		public static function Post($url, $vars = []) {

			$str_cookie = session_name() . "=" . session_id() . "; path=" . session_save_path();

			$options = [
				CURLOPT_URL        => $url,
				CURLOPT_POSTFIELDS => http_build_query($vars),
				CURLOPT_COOKIE     => $str_cookie,
				CURLOPT_POST       => true,
			];
			$options = self::get_curl_options($options);

			return self::do_curl($options);
		}

		public static function Put($url, $vars = []) {

		}

		public static function Patch($url, $vars = []) {

		}

		public static function Get($url, $vars = []) {

			if (!empty($vars)) {
				$send_vars = http_build_query($vars);
				$url       .= (strpos('?', $url) === false) ? '?' . $send_vars : '&' . $send_vars;
			}

			$str_cookie = session_name() . "=" . session_id() . "; path=" . session_save_path();

			$options = [
				CURLOPT_URL    => $url,
				CURLOPT_COOKIE => $str_cookie,
			];
			$options = self::get_curl_options($options);

			return self::do_curl($options);
		}

		static private function get_curl_options(array $curl_options = []) {

			return [
				       CURLOPT_RETURNTRANSFER => true,
				       CURLOPT_HEADER         => false,
				       CURLOPT_FAILONERROR    => true,
				       CURLOPT_COOKIESESSION  => true,
				       CURLOPT_SSL_VERIFYHOST => false,
				       CURLOPT_SSL_VERIFYPEER => false,
			       ] + $curl_options;
		}

		static private function do_curl($options) {

			$CURL = curl_init();

			if (empty($CURL)) {
				die("Error curl_init : curl not found.");
			}

			curl_setopt_array($CURL, $options);
			$content = curl_exec($CURL);

			if (curl_errno($CURL)) echo "Error curl_exec : " . curl_error($CURL);

			if ($content === false) {
				trigger_error('Erreur curl : ' . curl_error($CURL) . ' ' . $options[CURLOPT_URL], E_USER_WARNING);
			}
			if(!curl_errno($CURL)) {
				 $info = curl_getinfo($CURL);
			}
			curl_close($CURL);

			return $content;
		}
	}

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
	use function json_encode;
	use function parse_url;
	use function session_id;
	use function session_name;
	use function session_save_path;
	use function strlen;
	use function strpos;
	use function strtoupper;
	use function substr;
	use function trigger_error;
	use function var_dump;
	use const CURLOPT_COOKIE;
	use const CURLOPT_COOKIESESSION;
	use const CURLOPT_CRLF;
	use const CURLOPT_CUSTOMREQUEST;
	use const CURLOPT_FAILONERROR;
	use const CURLOPT_HEADER;
	use const CURLOPT_HEADEROPT;
	use const CURLOPT_HTTPHEADER;
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

		const headers
			= ["Cache-Control"   => "max-age=0",
			   "Accept-Charset:" => "ISO-8859-1,utf-8;q=0.7,*;q=0.7",
			   "Accept-Language" => "en-us,en;fr-fr,fr,q=0.5",
			];

		public static function Get(string $url, array $vars = []) {

			if (!empty($vars)) {
				$send_vars = http_build_query($vars);
				$url       .= (strpos('?', $url) === false) ? '?' . $send_vars : '&' . $send_vars;
			}

			$curl_options = [];
			$curl_headers = [];

			$curl_options                     = self::setCurlOptions($curl_options);
			$curl_options[CURLOPT_HTTPHEADER] = self::createHeaders($curl_headers, $curl_options);

			return self::doCurl($url, $curl_options);
		}

		/**
		 * @param string $url
		 * @param array  $vars
		 *
		 * @return bool|string
		 */
		public static function Post(string $url, array $vars = []) {

			$curl_options = [
				CURLOPT_POSTFIELDS => json_encode($vars),
				CURLOPT_POST       => true,
			];
			$curl_headers = ["Content-Type" => "application/json"];
			// $curl_headers = ["Content-Type" => "application/x-www-form-urlencoded"];

			$curl_options                     = self::setCurlOptions($curl_options);
			$curl_options[CURLOPT_HTTPHEADER] = self::createHeaders($curl_headers, $curl_options);

			return self::doCurl($url, $curl_options);
		}

		public static function Put(string $url, array $vars = []) {

			return self::PutPatch('PATCH', $url, $vars);
		}

		public static function Patch(string $url, array $vars = []) {

			return self::PutPatch('PUT', $url, $vars);
		}

		Private static function PutPatch(string $method, string $url, array $vars = []) {

			$curl_options = [
				CURLOPT_POSTFIELDS    => $vars,
				CURLOPT_CUSTOMREQUEST => strtoupper($method),
			];
			$curl_headers = ["Content-Type" => "application/json"];

			$curl_options                     = self::setCurlOptions($curl_options);
			$curl_options[CURLOPT_HTTPHEADER] = self::createHeaders($curl_headers, $curl_options);

			return self::doCurl($url, $curl_options);
		}

		static private function setCurlOptions(array $curl_options = []) {

			return [
				       CURLOPT_COOKIE         => session_name() . "=" . session_id() . "; path=" . session_save_path(),
				       CURLOPT_RETURNTRANSFER => true,
				       CURLOPT_AUTOREFERER    => true,
				       CURLOPT_HEADER         => false,
				       CURLOPT_FAILONERROR    => true,
				       CURLOPT_COOKIESESSION  => true,
				       CURLOPT_SSL_VERIFYHOST => false,
				       CURLOPT_SSL_VERIFYPEER => false,
			       ] + $curl_options;
		}

		static private function doCurl(string $url, array $curl_options) {

			$CURL = curl_init();

			if (empty($CURL)) {
				die("Error curl_init : curl not found.");
			}

			curl_setopt_array($CURL, $curl_options);
			curl_setopt($CURL, CURLOPT_URL, $url);

			$content = curl_exec($CURL);

			switch (curl_errno($CURL)) {
				case CURLE_OK:
					break;
				case CURLE_COULDNT_RESOLVE_PROXY:
				case CURLE_COULDNT_RESOLVE_HOST:
				case CURLE_COULDNT_CONNECT:
				case CURLE_OPERATION_TIMEOUTED:
				case CURLE_SSL_CONNECT_ERROR:
					echo "Error curl_exec : " . curl_error($CURL);
					break;
				default:
					echo "Error curl_exec : " . curl_error($CURL);
			}

			if ($content === false) {
				trigger_error('Error curl : ' . curl_error($CURL) . ' ' . $curl_options[CURLOPT_URL], E_USER_WARNING);
			}
			if (!curl_errno($CURL)) {
				$info = curl_getinfo($CURL);
			}
			curl_close($CURL);

			return $content;
		}

		/**
		 * @param array $headers
		 * @param array $curlOptions
		 *
		 * @return string[]
		 */
		private static function createHeaders(array $headers, array $curlOptions): array {

			$outHeaders  = [];
			$curlHeaders = array_merge($headers, self::headers);

			foreach ($curlHeaders as $header_name => $values) {

				$header = strtolower($header_name);

				if ('expect' === $header) {
					continue;
				}

				if ('content-Length' === $header) {
					if (array_key_exists(CURLOPT_POSTFIELDS, $curlOptions)) {
						$values = strlen($curlOptions[CURLOPT_POSTFIELDS]);
					} elseif (!array_key_exists(CURLOPT_READFUNCTION, $curlOptions)) {
						$values = 0;
					}
				}

				$outHeaders[] = $header_name . ': ' . $values;
			}
			$outHeaders[] = 'Expect:';

			return $outHeaders;
		}
	}

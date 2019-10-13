<?php

	namespace Idae\Api;

	// curl or direct api call
	use Idae\Io\Send;
	use function json_encode;
	use function print_r;

	class IdaeApiQuery {

		const api_uri = 'https://idae.api.lan/api/';
		private $send_method;

		public static function get($query = null) {

			return self::query(self::api_uri . '/' . $query);
		}

		public static function query($query = null) {

			return Send::Get(self::api_uri . '/' . $query);
		}

		public static function post($query = null, array $vars = []) {

			return self::update($query, $vars);
		}

		public static function update($query = null, array $vars = []) {
			return Send::Post(self::api_uri . '/' . $query);
		}

		public static function idql(array $idql = [], string $method = 'get') {

			return Send::Post(self::api_uri . "idql/$method", $idql);

		}
	}

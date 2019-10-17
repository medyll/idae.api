<?php

	namespace Idae\Api;

	// curl or direct api call
	use Idae\Io\Send;
	use function json_encode;
	use function print_r;

	class IdaeApiQuery {

		const api_uri = 'https://idae.api.lan/api/';
		private $query_method;

		// _GET
		public static function query($query = null) {

			return Send::Get(self::api_uri . '/' . $query);
		}

		// _GET
		public static function queryOne($query = null) {

			return Send::Get(self::api_uri . '/' . $query);
		}

		// _PATCH
		public static function update($query = null, array $json = []) {
			return Send::Patch(self::api_uri . '/' . $query,$json);
		}

		// _POST
		public static function insert($query = null, array $json = []) {
			return Send::Post(self::api_uri . '/' . $query, $json);
		}

		// _POST
		public static function idql(array $idql = []) {

			return Send::Post(self::api_uri . "idql/" . $idql['scheme'], $idql);
		}
	}

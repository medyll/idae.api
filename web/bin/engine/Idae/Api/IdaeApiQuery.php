<?php

	namespace Idae\Api;

	// curl or direct api call
	use Idae\Io\Send;
	use function json_encode;
	use function print_r;

	/**
	 * Client for calling this API over HTTP, from PHP.
	 *
	 * Every method is a thin wrapper over Send and targets the host in `api_uri`,
	 * which is hardcoded to the LAN instance.
	 */
	class IdaeApiQuery {

		const api_uri = 'http://idae.api.lan/api/';
		private $query_method;

		// _GET
		/**
		 * GETs a collection.
		 *
		 * @param string $query Path under the API root
		 * @return mixed
		 */
		public static function query($query = null) {

			return Send::Get(self::api_uri . '/' . $query);
		}

		// _GET
		/**
		 * GETs a single document. Identical to query(); kept for call-site clarity.
		 *
		 * @param string $query Path under the API root
		 * @return mixed
		 */
		public static function queryOne($query = null) {

			return Send::Get(self::api_uri . '/' . $query);
		}

		// _PATCH
		/**
		 * PATCHes a document.
		 *
		 * @param string $query Path under the API root
		 * @param array  $json  Fields to change
		 * @return mixed
		 */
		public static function update($query = null, array $json = []) {
			return Send::Patch(self::api_uri . '/' . $query,$json);
		}

		// _POST
		/**
		 * POSTs a new document.
		 *
		 * @param string $query Path under the API root
		 * @param array  $json  Document to insert
		 * @return mixed
		 */
		public static function insert($query = null, array $json = []) {
			return Send::Post(self::api_uri . '/' . $query, $json);
		}

		// _POST
		/**
		 * POSTs an IDQL query. The scheme is taken from `$idql['scheme']`.
		 *
		 * @param array $idql
		 * @return mixed
		 */
		public static function idql(array $idql = []) {

			return Send::Post(self::api_uri . "idql/" . $idql['scheme'], $idql);
		}
	}

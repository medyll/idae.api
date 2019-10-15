<?php

	namespace Idae\Api;

	use Idae\Connect\IdaeConnect;
	use Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use Idae\Query\IdaeQuery;
	use function explode;
	use function file_get_contents;
	use function is_array;
	use function json_decode;
	use function json_encode;
	use function str_replace;
	use function strcasecmp;
	use function trim;
	use function var_dump;
	use const JSON_PRESERVE_ZERO_FRACTION;
	use const JSON_PRETTY_PRINT;

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 07/06/2018
	 * Time: 22:12
	 *
	 * point d'entrée du listener
	 */
	class IdaeApiRest {

		private $api_root = '/api/';
		private $http_method;
		private $query_method;
		private $http_vars;
		private $parser;
		private $routes;

		/**
		 * IdaeApiRest constructor.
		 */
		public function __construct() {

			$this->parser = new IdaeApiParser();

			$this->parser->setApiRoot($this->api_root)
			             ->setRequestUri(str_replace(trim($this->api_root), '', $_SERVER['REQUEST_URI']))
			             ->setQyCodeType('php');

			$this->setHttpMethod($_SERVER['REQUEST_METHOD']);
			$this->setHttpVars();

		}

		public function doIdql() {

			$idql = $this->http_vars;

			$query = $this->parser->parse($idql);

			$this->doQuery($query);
		}

		public function doRest() {
			$query = $this->parser->parse();
			$this->doQuery($query);
		}

		private function doQuery(array $query) {

			$qy = new IdaeQuery();
			$qy->collection($query['scheme']);

			if (!empty($query['limit'])) $qy->setLimit($query['limit']);
			if (!empty($query['page'])) $qy->setPage($query['page']);
			if (!empty($query['sort'])) $qy->setSort((int)$query['sort']);

			$find = $query['where'] ?? [];
			$query_method = $query['query_method'] ?? 'find';
			// find findOne update insert ?
			$rs = $qy->$query_method($find);


			echo json_encode($rs,JSON_PRETTY_PRINT,JSON_PRESERVE_ZERO_FRACTION);

			return $rs;
		}

		public function addRoute($path, $action, \Closure $callback) {
			$path                  = $path ?? '/';
			$action                = trim($action, $path);
			$this->routes[$action] = $callback;
		}

		public function dispatch($action) {

			$action   = trim($action, '/');
			$callback = $this->routes[$action];

			echo call_user_func($callback);
		}

		private function setHttpVars() {

			switch ($this->http_method) {
				case 'POST':
				case 'PATCH':
				case 'PUT':
					$this->http_vars = $this->getJson();
					break;

				case 'GET':
					$this->http_vars = $_GET; // $this->http_vars = $_REQUEST;
			}
		}

		private function setHttpMethod(string $http_method) {
			$this->http_method = $http_method;

			return $this;
		}

		private function getJson() {

			$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

			switch ($contentType) {
				case 'application/json':
					$content = trim(file_get_contents("php://input"));
					$decoded = json_decode($content, true);
					//If json_decode failed, the JSON is invalid.
					if (!is_array($decoded)) {
						// throw new Exception('Invalid JSON!');
					}

					return $this->http_vars = $decoded;

					break;
				case 'application/x-www-form-urlencoded':
					return $this->http_vars = $_POST;
					break;
			}
		}
	}

<?php

	namespace Idae\Api;

	use Idae\Connect\IdaeConnect;
	use Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use function explode;
	use function file_get_contents;
	use function is_array;
	use function json_decode;
	use function str_replace;
	use function strcasecmp;
	use function trim;
	use function var_dump;

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
		private $method;
		private $route;
		private $vars;

		private $routes;

		/**
		 * IdaeApiRest constructor.
		 */
		public function __construct() {

			$this->getMethod();
			$this->getRoute();
			$this->getVars();
		}

		public function fetch_idql($method) {

			$parser = new IdaeApiParser();
			$parser->setApiRoot($this->api_root);
			$parser->setRequestUri(str_replace(trim($this->api_root), '', $_SERVER['REQUEST_URI']));
			$parser->set_query_scheme($this->getVars());
			$transpiler = new IdaeApiTransPiler();
			$transpiler->dunno($this->getVars());
		}

		public function fetch($uri_vars) {

			$parser = new IdaeApiParser();
			$parser->setApiRoot($this->api_root);
			$parser->setRequestUri(str_replace(trim($this->api_root), '', $_SERVER['REQUEST_URI']));
			$parse = $parser->parse();
			var_dump($parse);
			$trans = $parser->transcript();
			var_dump($trans);

			$transpiler = new IdaeApiTransPiler();
			$transpiler->dunno($trans);

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

		private function getMethod() {


			$this->method = $_SERVER['REQUEST_METHOD'];

		}

		private function getRoute() {
			// REQUEST_URI ?
			$this->route = explode('/', str_replace(trim($this->api_root), '', $_SERVER['REQUEST_URI']));
		}

		private function getVars() {

			switch ($this->method) {
				case 'POST':
				case 'PATCH':
				case 'PUT':
					$this->vars = $this->getJson();
					break;

				case 'GET':
					$this->vars = $_GET;
			}
var_dump($this->vars);
			return $this->vars;
			//Make sure that the content type of the POST request has been set to application/json

			// $this->vars = $_REQUEST;
		}

		private function getJson() {

			$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

			switch ($contentType) {
				case 'application/json':
					// Takes raw data from the request
					$json = file_get_contents('php://input');
					//Receive the RAW post data.
					$content = trim(file_get_contents("php://input"));
					//Attempt to decode the incoming RAW post data from JSON.
					$decoded = json_decode($content, true);
					//If json_decode failed, the JSON is invalid.
					if (!is_array($decoded)) {
						// throw new Exception('Received content contained invalid JSON!');
					}

					return $this->vars = $decoded;

					break;
				case 'application/x-www-form-urlencoded':
					return $this->vars = $_POST;
					break;
			}
		}
	}

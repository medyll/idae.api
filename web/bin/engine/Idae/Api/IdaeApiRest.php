<?php

	namespace Idae\Api;

	use Idae\Connect\IdaeConnect;
	use Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use function explode;
	use function str_replace;
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
		}

		public function fetch_idql($method) {

			$parser = new IdaeApiParser();
			$parser->setApiRoot($this->api_root);
			$parser->setRequestUri(str_replace(trim($this->api_root), '', $_SERVER['REQUEST_URI']));
			$parser->set_query_scheme($_POST);
			$transpiler = new IdaeApiTransPiler();
			$transpiler->dunno($_POST);
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

			$this->getMethod();
			$this->getRoute();
			$this->getVars();

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
			$this->vars = $_REQUEST;
		}
	}

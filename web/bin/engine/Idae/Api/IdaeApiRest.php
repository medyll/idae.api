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
			$parser = new IdaeApiParser();
			$parser->setApiRoot($this->api_root);
			$parser->setRequestUri(str_replace(trim($this->api_root), '', $_SERVER['REQUEST_URI']));
			var_dump($parser->parse());
			$parser->transcript();

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
			echo $this->method = $_SERVER['REQUEST_METHOD'];

		}

		private function getRoute() {
			// REQUEST_URI ?
			$this->route = explode('/', str_replace(trim($this->api_root), '', $_SERVER['REQUEST_URI']));

			var_dump($this->route);
		}

		private function getVars() {
			$this->vars = $_REQUEST;
		}
	}

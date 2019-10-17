<?php

	namespace Idae;

	use function call_user_func;
	use function trim;

	class IdaeRouter {

		private $routes;

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
	}

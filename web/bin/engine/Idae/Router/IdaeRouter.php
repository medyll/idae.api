<?php

	namespace Idae;

	use function call_user_func;
	use function trim;

	/**
	 * Minimal action-to-callback router.
	 *
	 * Not the router the API itself uses; see ClassRouter, which extends AltoRouter.
	 */
	class IdaeRouter {

		private $routes;

		/**
		 * Registers a callback for an action.
		 *
		 * @param string   $path     Prefix trimmed off the action
		 * @param string   $action
		 * @param \Closure $callback
		 * @return void
		 */
		public function addRoute($path, $action, \Closure $callback) {
			$path                  = $path ?? '/';
			$action                = trim($action, $path);
			$this->routes[$action] = $callback;
		}

		/**
		 * Runs the callback registered for an action and echoes what it returns.
		 *
		 * @param string $action
		 * @return void
		 */
		public function dispatch($action) {

			$action   = trim($action, '/');
			$callback = $this->routes[$action];

			echo call_user_func($callback);
		}
	}

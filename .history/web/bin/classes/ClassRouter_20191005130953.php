<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 10/08/2017
	 * Time: 20:43
	 */
	class Router extends AltoRouter {

		function __construct() {
			parent::__construct();

			$this->do_match();
		}

		function do_match() {

			$this->addRoutes($this->routes());
			//
			$match = $this->match();


			if ($match) {
				if (is_string($match['target']) && strpos($match['target'], '#') !== false) {
					$is_cl = explode('#', $match['target']);

					if (sizeof($is_cl) == 2) {
						$cl   = new $is_cl[0]();
						$meth = $is_cl[1];
						$cl->$meth($match['params']);
					}
				} else if (is_callable($match['target'])) {

					call_user_func_array($match['target'], $match['params']);
				} else {
					// no route was matched
					echo "404";
					// header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
				}
			}
		}

		public function routes() {
			return [
				['POST', '/api_login', 'Action#do_action', 'action_exec'],
				['POST', '/api_heart', 'Action#do_other_action', 'action_other_exec'],
				['GET', '/api/[*:file]', function ($file) {
						echo "grep";
					include_once('bin/services/' . $file . '.php');
				}],
			];
		}



	}

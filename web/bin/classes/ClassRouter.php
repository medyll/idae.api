<?php

	use Idae\Api\IdaeApiQuery;
	use Idae\Api\IdaeApiRest;

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
				// var_dump($match);
				// var_dump(json_decode(file_get_contents('php://input'), JSON_OBJECT_AS_ARRAY | JSON_PRETTY_PRINT));

				// echo json_decode(file_get_contents('php://input'),JSON_OBJECT_AS_ARRAY | JSON_PRETTY_PRINT);
				if (is_string($match['target']) && strpos($match['target'], '#') !== false) {
					$is_cl = explode('#', $match['target']);

					if (sizeof($is_cl) == 2) {
						$cl   = new $is_cl[0]();
						$meth = $is_cl[1];
						$cl->$meth($match['params']);
					}
				} else if (is_callable($match['target'])) {
					// var_dump($match);
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
				['POST', '/api/idql/[*:scheme]', function (string $scheme) {
					$_POST = json_decode(file_get_contents('php://input'), JSON_OBJECT_AS_ARRAY | JSON_PRETTY_PRINT);

					$defaulIdql = [
						'method' => 'find',
						'scheme' => $scheme,
						'limit'  => 10,
						'page'   => 0,
					];

					$idql = array_merge($defaulIdql,$_POST );

					//IdaeApiQuery::idql($idql);
					$api = new IdaeApiRest();
					$api->doIdql($idql);

				}],
				['GET|POST|PATCH|PUT', '/api/[*:uri_vars]', function ($uri_vars) {
					$api = new IdaeApiRest();
					$api->doRest();
				}],
			];
		}

	}


<?php

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
			$match = $this->match();

			if (!$match) {
				$this->jsonResponse(404, 'Route not found');
				return;
			}

			try {
				if (is_string($match['target']) && strpos($match['target'], '#') !== false) {
					$is_cl = explode('#', $match['target']);

					if (sizeof($is_cl) === 2) {
						$cl   = new $is_cl[0]();
						$meth = $is_cl[1];
						$cl->$meth($match['params']);
						return;
					}
				} elseif (is_callable($match['target'])) {
					call_user_func_array($match['target'], $match['params']);
					return;
				}
			} catch (\Throwable $e) {
				error_log('Router failure: ' . $e->getMessage());
				$this->jsonResponse(500, 'Internal server error');
				return;
			}

			$this->jsonResponse(500, 'Invalid route target');
		}

		public function routes() {
			return [
				['POST', '/api/idql/[*:scheme]', function (string $scheme) {
					$api = new IdaeApiRest();
					$api->doIdql(null, $scheme);
				}],
				['OPTIONS', '/api/idql/[*:scheme]', function () {
					$this->emptyResponse(204, 'POST, OPTIONS');
				}],
				['GET|PUT|PATCH|DELETE|HEAD', '/api/idql/[*:scheme]', function () {
					$this->jsonResponse(405, 'Method not allowed', 'POST, OPTIONS');
				}],
				['GET|HEAD|POST|PATCH|PUT', '/api/[*:uri_vars]', function ($uri_vars) {
					if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
						header('Deprecation: true');
						header('Warning: 299 - "Non-GET methods on /api/* are deprecated; use POST /api/idql/{scheme}"');
					}

					$api = new IdaeApiRest();
					$api->doRest();
				}],
				['OPTIONS', '/api/[*:uri_vars]', function () {
					$this->emptyResponse(204, 'GET, HEAD, POST, PATCH, PUT, OPTIONS');
				}],
				['DELETE', '/api/[*:uri_vars]', function () {
					$this->jsonResponse(405, 'Method not allowed', 'GET, HEAD, POST, PATCH, PUT, OPTIONS');
				}],
			];
		}

		private function emptyResponse($code, $allow = null) {
			http_response_code($code);
			if ($allow !== null) {
				header('Allow: ' . $allow);
			}
		}

		private function jsonResponse($code, $message, $allow = null) {
			http_response_code($code);
			header('Content-Type: application/json');
			if ($allow !== null) {
				header('Allow: ' . $allow);
			}

			if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
				echo json_encode([
					'status'  => false,
					'message' => $message,
				]);
			}
		}

	}


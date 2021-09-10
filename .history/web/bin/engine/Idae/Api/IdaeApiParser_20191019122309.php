<?php

	namespace Idae\Api;

	use function array_filter;
	use function array_key_first;
	use function array_merge;
	use function array_values;
	use function explode;
	use function implode;
	use function is_array;
	use function is_numeric;
	use function is_string;
	use function parse_str;
	use function preg_match;
	use function sizeof;
	use function str_replace;
	use function strlen;
	use function strpos;
	use function var_dump;

	class IdaeApiParser {

		private $api_root;
		private $request_uri;
		/**
		 * @var string $qy_code_type php|..todo
		 */
		private $qy_code_type;

		private $uri_keys_methods = ['find', 'group', 'update', 'create', 'delete'];
		private $uri_keys_where   = ['where', 'scheme', 'query_method'];
		private $uri_keys_sizes   = ['sort', 'page', 'limit'];
		private $uri_keys_format  = ['proj'];
		private $uri_key_output   = ['output'];

		public function __construct() {

		}

		/**
		 * @param string $request_uri
		 *
		 * @return $this
		 */
		public function setRequestUri(string $request_uri) {
			$this->request_uri = $request_uri;

			return $this;
		}

		/**
		 * @param string $api_root
		 *
		 * @return $this
		 */
		public function setApiRoot(string $api_root) {
			$this->api_root = $api_root;

			return $this;
		}

		/**
		 * @param mixed $qy_code_type
		 *
		 * @return IdaeApiParser
		 */
		public function setQyCodeType($qy_code_type) {
			$this->qy_code_type = $qy_code_type;

			return $this;
		}

		/**
		 * build idql from uri
		 *
		 * @param array|null $idql
		 *
		 * @return array
		 */
		public function parse(array $idql = null) {

			if (empty($idql)) {
				$idql = $this->uriToIdql();
			}

			$idql = $this->filterUriKeys($idql);

			$idql['where'] = $idql['where'] ?? [];

			switch ($this->qy_code_type) {
				case 'php':
				default:
					$idql['where'] = IdaeApiOperatorMongoDbPhp::set_operators($idql['where']);
			}

			return $idql;
		}

		private function uriToIdql() {

			$routes = $dql ?? array_filter(explode('/', $this->request_uri));
			// agent/find/limit:12/sort:id:desc/sort:code:asc/page:1/output:json/groupby:[code:d]/lk:id:254/in:key1:[item1:item2:item3:item4]/lk:code:test/in:key2:[val1:val2]/lk:code:test
			// scheme:agent/find/limit:12/sort:id:desc/sort:code:asc/page:1/output:json/groupby:[code:d]/lk:id:254/in:key1:[item1:item2:item3:item4]/lk:code:test/in:key2:[val1:val2]/lk:code:test

			// key:value  ^[a-z0-9\-_]+:[a-z0-9\-_]+$
			$reg_key_value = '/^[a-z0-9\-_]+:[a-z0-9\-_]+$/';
			// cmd:key:value  cmd:kay:[value1:value2]  ^([a-z0-9]+):([a-z0-9]+):(.*)$
			$reg_cmd_key_value = '/^([a-z0-9]+):([a-z0-9]+):(.*)$/';
			// item1,item2,item3... [val1:val2:val3:val4] ^(\[)([^\[]+[^\]]+)(\])$
			$new_routes  = [];
			$new_collect = [];

			foreach ($routes as $index => $route) {
				$new_value = null;
				$command = $this->extract_commands($route);

				// useless !!!
				preg_match($reg_cmd_key_value, $route, $matches_cmd_key_value);
				if(sizeof($matches_cmd_key_value)===4){
					$new_value   = [$matches_cmd_key_value[1]=>[$matches_cmd_key_value[2]=>$matches_cmd_key_value[3]]];
				}


				$new_collect[$command] = $new_value;

				if (is_string($route) && strpos($route, ':') !== false) {

					preg_match($reg_key_value, $route, $matches);
					var_dump($matches);

					$arr_route = explode(':', $route);
					$cmd       = $arr_route[0];
					unset($arr_route[0]);
					$left_over   = implode(':', $arr_route);
					$final_route = (strpos($left_over, ':') === false) ? $left_over : $this->parseValues($arr_route);

				} else if (is_numeric($route)) {

					continue;
				} else {
					$cmd         = $route;
					$final_route = $route;
				}

				if ($cmd === 'where') {
					if (is_string($route)) {
						$new_routes[$cmd] = str_replace('where:', '', $route);
						parse_str($new_routes[$cmd], $new_routes[$cmd]);
						continue;
					}
					if (is_array($route)) {
						$new_routes[$cmd] = $route;
						continue;
					}
				}

				if (is_array($final_route) && is_string($command) && !empty($new_routes[$command])) {
					$new_routes[$command] = array_merge($new_routes[$cmd], $final_route);
				} else if (is_string($cmd)) {
					$new_routes[$command] = $final_route;
				}
			}

			var_dump($new_collect);

			$new_routes['scheme'] = $new_routes['scheme'] ?? array_key_first($new_routes); // deduction scheme

			// where : is not yet decoded to real query
			if (!empty($new_routes['where']) && is_string($new_routes['where'])) parse_str($new_routes['where'], $new_routes['where']);
			// is there an id to retain ?
			if (!empty($routes[1]) && is_numeric($routes[1])) {
				$new_routes['where'] = $new_routes['where'] ?? [];
				if (is_array($new_routes['where'])) {
					$new_routes['where']['eq']['id' . $new_routes['scheme']] = $routes[1];
				}
			};

			// var_dump($new_routes);

			return $new_routes;
		}

		private function extract_commands(string $route) {
			$arr_route = explode(':', $route);

			return is_numeric($arr_route[0]) ? $route : $arr_route[0];
		}

		private function parseValues($uri_values) {

			$uri_values = array_values($uri_values);

			// if brackets then array with ',' as delimiter ?
			$subject = implode(':', $uri_values);
			// is an array
			if (strpos($subject, '[') === 0 && strpos($subject, ']') === strlen($subject) - 1) {
				$pattern = '/^\[(.*.)\]$/';
				preg_match($pattern, $subject, $matches);

				return $matches[0];
			} else if (strpos($subject, '[') !== false && strpos($subject, '[') !== 0) {
				// don't start with bracket but contains brackets
				// item:[item1:item2:item3:item4:[item1:item2]]
				$pattern     = '/^([a-z 0-9]+):\[(.*.)\]$/';
				$key_match   = preg_replace($pattern, '$1', $subject);
				$value_match = preg_replace($pattern, '$2', $subject);
				if (!empty($key_match) && !empty($value_match)) {
					if (strpos($value_match, '[') === false) {
						$value_match = explode(':', $value_match);
					}

					return [$key_match => $value_match];
				}
				// treat item1:item2:item3:item4:[item1:item2]
			}

			//echo sizeof($uri_values);
			if (sizeof($uri_values) === 2) {

				return [$uri_values[0] => $uri_values[1]];
			}

			return [$uri_values];
		}

		/**
		 * check we only have existing keys
		 * uri_2_array filter ( where | sort | page ...)
		 *
		 * @param $new_routes
		 *
		 * @return array
		 */
		public function filterUriKeys($new_routes) {

			$out          = [];
			$all_uri_keys = [$this->uri_keys_sizes, $this->uri_keys_where, $this->uri_key_output, $this->uri_keys_methods, $this->uri_keys_format];
			// check we only have existing keys
			foreach ($all_uri_keys as $index_key => $uri_key) {
				foreach ($uri_key as $index => $uri_keys_size) {
					if (!empty($new_routes[$uri_keys_size])) {
						$out[$uri_keys_size] = $new_routes[$uri_keys_size];
					}
				}
			}

			return $out;
		}

	}

<?php

	namespace Idae\Api;

	use http\Env\Request;
	use Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use Idae\Query\IdaeQuery;
	use Idae\Api\IdaeApiTransPiler;
	use function array_filter;
	use function array_key_first;
	use function array_map;
	use function array_values;
	use function cleanTel;
	use function droit;
	use function explode;
	use function file_get_contents;
	use function implode;
	use function is_array;
	use function is_int;
	use function iterator_to_array;
	use function json_decode;
	use function json_encode;
	use function parse_url;
	use function preg_match;
	use function rtrim;
	use function session_id;
	use function sizeof;
	use function str_replace;
	use function strlen;
	use function strpos;
	use function substr;
	use function trim;
	use function ucfirst;
	use function var_dump;
	use const JSON_PRETTY_PRINT;

	class IdaeApiParser {

		private $request_uri;

		private $appscheme_code  = null; // there can be more than one
		private $appscheme_model = null;
		private $query_vars      = [];

		private $query_schema;

		private $uri_keys_methods = ['find', 'group', 'update', 'create', 'delete'];
		private $uri_keys_where   = ['where', 'scheme'];
		private $uri_keys_sizes   = ['sort', 'page', 'limit'];
		private $uri_key_output   = ['output'];

		private $api_root;
		private $query_where;

		public function __construct() {

		}

		public function setRequestUri($REQUEST_URI) {
			$this->request_uri = $REQUEST_URI;
		}

		public function setApiRoot($api_root) {
			$this->api_root = $api_root;
		}

		public function transcript() {

			$out          = [];
			$all_uri_keys = [$this->uri_keys_sizes, $this->uri_keys_where, $this->uri_key_output, $this->uri_keys_methods];
			// check we only have existing keys
			foreach ($all_uri_keys as $index_key => $uri_key) {
				foreach ($uri_key as $index => $uri_keys_size) {
					if (!empty($this->query_schema[$uri_keys_size])) {
						$out[$uri_keys_size] = $this->query_schema[$uri_keys_size];
					}
				}
			}

			if (!empty($out['where'])) parse_str($out['where'], $out['where']);

			var_dump($out);

			$transpiler = new IdaeApiTransPiler();
			$transpiler->dunno($out);
		}

		/**
		 * @param array|null $request_uri
		 *
		 * @return array
		 */
		public function parse(array $request_uri = null) {

			$new_routes = [];
			$routes     = $request_uri ?? array_filter(explode('/', $this->request_uri));
			// scheme:appscheme/find/limit:12/sort:id:desc/sort:code:asc/page:1/output:json/groupby:[code:d]/lk:id:254/in:key1:[item1:item2:item3:item4]/lk:code:test/in:key2:[val1:val2]/lk:code:test

			// key:value  ^[a-z0-9]+:[a-z0-9]+$

			// cmd:key:value  cmd:kay:[value1:value2]  ^([a-z0-9]+):([a-z0-9]+):(.*)$

			// item1,item2,item3... [val1:val2:val3:val4] ^(\[)([^\[]+[^\]]+)(\])$

			foreach ($routes as $index => $route) {

				if (strpos($route, ':') !== false) {
					$arr_route = explode(':', $route);
					$cmd       = $arr_route[0];
					unset($arr_route[0]);
					$left_over   = implode(':', $arr_route);
					$final_route = (strpos($left_over, ':') === false) ? $left_over : $this->parse_values($arr_route);
				} else {
					$cmd         = $route;
					$final_route = $route;
				}

				if ($cmd === 'where') {
					$this->query_where = str_replace('where:', '', $route);
					continue;
				}

				if (is_array($final_route) && !empty($new_routes[$cmd])) {
					$new_routes[$cmd] = array_merge($new_routes[$cmd], $final_route);
				} else {
					$new_routes[$cmd] = $final_route;
				}
			}

			$this->query_schema           = $new_routes;
			$this->query_schema['scheme'] = $new_routes['scheme'] ?? array_key_first($this->query_schema);
			$this->query_schema['where']  = $this->query_where ?? null;

			$new_routes['where'] = $this->query_where ?? null;

			return $new_routes;
		}

		private function parse_values($uri_values) {

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

	}

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

			//   Helper::dump($match);

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
					header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
				}
			}
		}

		public function routes() {
			return [
				['POST', '/login_check', 'Action#do_action', 'action_exec'],
				['GET', '/api/[*:file]', function ($file) {
					include_once('bin/services/' . $file . '.php');
				}],

				['GET', '*', 'Page#index', 'index']
			];
		}

		static function build_route($type = 'index', $params = [], $query_vars = []) {

			$link = '';


			return HTTPCUSTOMERSITE.Router::format_uri($link);
		}

		static function format_uri($string, $separator = '-') {
			$charmap       = ['À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
			                  'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			                  'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
			                  'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
			                  'ß' => 'ss',
			                  'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
			                  'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			                  'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
			                  'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
			                  'ÿ' => 'y', '©' => '(c)'];
			$accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
			$special_cases = ['&' => 'et', "'" => ''];
			$string        = mb_strtolower(trim($string), 'UTF-8');
			$string        = str_replace(array_keys($charmap), $charmap, $string);
			$string        = str_replace(array_keys($special_cases), array_values($special_cases), $string);
			$string        = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
			$string        = preg_replace("/[^a-z0-9\\/]/u", "$separator", $string);
			$string        = preg_replace("/[$separator]+/u", "$separator", $string);

			return $string;
		}
	}

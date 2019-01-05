<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 10/08/2017
	 * Time: 14:22
	 */
	// namespace idaeWeb;
	global $LATTE;
	$LATTE->setAutoRefresh(true);

	class Page extends AppSite {

		function __construct() {
			parent::__construct();
			$this->Fragment = new Fragment();
		}

		public function index() {
			# active page
			$this->set_page('index');

			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			#
			#  Shop liste
			$APP_SH                            = new App('shop');
			$rs_sh                             = $APP_SH->find([], ['_id' => 0]);
			$parameters_index['var_shop_list'] = iterator_to_array($rs_sh, false);
			foreach ($parameters_index['var_shop_list'] as $key => $val) {
				$url                                              = Router::build_route('restaurant', ['idshop' => $val['idshop']]);
				$parameters_index['var_shop_list'][$key]['route'] = $url;
				//
			}
			$LATTE->setAutoRefresh(false);
			$html = $LATTE->renderToString(APPTPL . 'pages/index.html', $parameters_index);

			$this->render($html);

		}

		function do_action($params = ['action', 'value']) {
			if (strpos($params['value'], '/') === false && strpos($params['action'], '/') === false) {
				$this->$params['action']($params['value']);
			} else if (strpos($params['action'], '/') === false) {
				$this->$params['action'](explode('/', $params['value']));
			} else {
				$actions = explode('/', $params['action']);
				$this->$params['action'](explode('/', $params['value']));
			}
			// Helper::dump($params);
		}



		function login_register($inner = false) {
			# active page
			$this->set_page('login_register');

			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$parameters['HTTP_REFERER']     = $_SERVER['HTTP_REFERER'];
			$parameters['type']             = 'client';

			if (empty($_SESSION['client'])) {
				$html = $LATTE->renderToString(APPTPL . 'pages/login_register.html', $parameters);
			} else {
				$html = $LATTE->renderToString(APPTPL . 'fragments/login_multi_done.html', $parameters);

			}

			$this->render($html);

		}

		function login_init($params = []) {
			# active page
			$this->set_page('login_mail');

			global $LATTE;
			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$parameters['HTTP_REFERER']     = $_SERVER['HTTP_REFERER'];
			$parameters['type']             = $type = $params[0];
			$parameters['Type']             = $Type = ucfirst($params[0]);
			$parameters['private_key']      = $Type = $params[1];

			$parameters['link'] = http_build_query($parameters);

			$APP_CLI = new App($type);

			$test_cli = $APP_CLI->findOne(["private_key" => $params[1]]);
			if (empty($test_cli['private_key'])) {
				$this->render("opération impossible");

				return;
			}
			$parameters['vars'] = $test_cli;
			$html               = $LATTE->renderToString(APPTPL . 'pages/login_init.html', $parameters);

			$this->render($html);

		}

		function login_multi($params = []) {
			# active page
			$this->set_page('login_multi_' . $params['type']);

			global $LATTE;
			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$parameters['HTTP_REFERER']     = $_SERVER['HTTP_REFERER'];
			$parameters['type']             = $type = $params['type'];
			$parameters['Type']             = $Type = ucfirst($type);

			$parameters['link'] = http_build_query($parameters);

			$html = $LATTE->renderToString(APPTPL . 'pages/login_multi.html', $parameters);

			$this->render($html);

		}
	}
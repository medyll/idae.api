<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 10/08/2017
	 * Time: 14:22
	 */
	class Fragment extends AppSite {

		function __construct() {
			parent::__construct();

		}

		function menu_bar($inner = false) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$Cart                    = new Cart();
			$parameters['var_cart']  = $Cart->get_cart();
			$parameters['site_page'] = $this->get_page();

			$html = $LATTE->renderToString(APPTPL . 'fragments/menu_bar.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function shop_search($inner = false) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$Cart                      = new Cart();
			$parameters['var_cart']    = $Cart->get_cart();
			$parameters['site_page']   = $this->get_page();
			$parameters['map_adresse'] = $parameters['var_cart']['cart_adresse']['formatted_address'] ?: null;
			$LATTE->setAutoRefresh(true);
			$html = $LATTE->renderToString(APPTPL . 'fragments/shop_search.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function do_action($params = ['action',
		                              'value']) {
			//

			if (strpos($params['value'], '/') === false) {
				$this->$params['action']($params['value']);
			} else {
				$this->$params['action'](explode('/', $params['value']));
			}
			// Helper::dump($params);
		}

		function cart_small($inner = false) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$Cart                   = new Cart();
			$parameters['var_cart'] = $Cart->get_cart();

			$html = $LATTE->renderToString(APPTPL . 'fragments/cart_small.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function cart_edit_line($inner = false) {
			global $LATTE;
			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$Cart                        = new Cart();
			$arr_cart                    = $Cart->get_cart();
			$parameters['cart_line']     = $arr_cart['cart_lines'][$_REQUEST['cart_line_key']];
			$parameters['cart_line_key'] = $_REQUEST['cart_line_key'];
			$html                        = $LATTE->renderToString(APPTPL . 'fragments/cart_big_item.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		public function cart_big($inner = false) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$Cart                   = new Cart();
			$parameters['var_cart'] = $Cart->get_cart();

			$html = $LATTE->renderToString(APPTPL . 'fragments/cart_big.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function cart_sum($inner = false) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$Cart                   = new Cart();
			$parameters['var_cart'] = $Cart->get_cart();
			//Helper::dump($parameters['var_cart']);
			$html = $LATTE->renderToString(APPTPL . 'fragments/cart_small.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function livraison_estimation($idshop, $inner = false) {
			global $LATTE;

			$Bin      = new Bin();
			$APP_SHOP = new App('shop');

			$ARR_SHOP          = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$idshop            = (int)$ARR_SHOP['idshop'];
			$idsecteur         = (int)$ARR_SHOP['idsecteur'];

			$configs           = CommandeQueueConsole::consoleShopSite($idshop);

			$times_shop        = $configs->console_shop->get_templateObjHTML();
			$times_shop_obj    = $configs->console_shop->get_templateObj();

			$times_secteur     = $configs->console_secteur_livreur->get_templateObjHTML();
			$times_secteur_obj = $configs->console_secteur_livreur->get_templateObj();

			$COMMAND_SHOP_FINAL_DELIVERY_DELAY_RANGE = $times_shop->COMMAND_SHOP_FINAL_DELIVERY_DELAY_RANGE;
			$COMMAND_SHOP_FINAL_DELIVERY_DATETIME    = $times_shop->COMMAND_SHOP_FINAL_DELIVERY_DATETIME;
			$COMMAND_SHOP_FINAL_DELIVERY_THUMB_STATE = $times_shop->COMMAND_SHOP_FINAL_DELIVERY_THUMB_STATE;
			$COMMAND_SHOP_MSG                        = $times_shop->COMMAND_SHOP_MSG;
			$COMMAND_SHOP_SHIFT                      = $times_shop->COMMAND_SHOP_SHIFT_NB;

			$COMMAND_SHOP_FINAL_DELIVERY_DELAY_RANGE_OBJ = $times_shop_obj->COMMAND_SHOP_FINAL_DELIVERY_DELAY_RANGE;
			$COMMAND_SHOP_FINAL_DELIVERY_DATETIME_OBJ    = $times_shop_obj->COMMAND_SHOP_FINAL_DELIVERY_DATETIME;
			$COMMAND_SHOP_FINAL_DELIVERY_THUMB_STATE_OBJ = $times_shop_obj->COMMAND_SHOP_FINAL_DELIVERY_THUMB_STATE;
			$COMMAND_SHOP_SHIFT_OBJ                      = $times_shop_obj->COMMAND_SHOP_SHIFT_NB;

			$LIVREUR_WORKING_NB          = $times_secteur->LIVREUR_WORKING_NB;
			$LIVREUR_MSG                 = $times_secteur->LIVREUR_MSG;
			$LIVREUR_DISPONIBLE_NB       = $times_secteur->LIVREUR_DISPONIBLE_NB;
			$LIVREUR_WAITING_COMMANDE_NB = $times_secteur->LIVREUR_WAITING_COMMANDE_NB;

			$LIVREUR_WORKING_NB_OBJ          = $times_secteur_obj->LIVREUR_WORKING_NB;
			$LIVREUR_DISPONIBLE_NB_OBJ       = $times_secteur_obj->LIVREUR_DISPONIBLE_NB;
			$LIVREUR_WAITING_COMMANDE_NB_OBJ = $times_secteur_obj->LIVREUR_WAITING_COMMANDE_NB;

			$parameters['LIVREUR_WORKING_NB']                      = $times_secteur->LIVREUR_WORKING_NB;
			$parameters['LIVREUR_MSG']                             = $times_secteur->LIVREUR_MSG;
			$parameters['LIVREUR_DISPONIBLE_NB']                   = $times_secteur->LIVREUR_DISPONIBLE_NB;
			$parameters['LIVREUR_WAITING_COMMANDE_NB']             = $times_secteur->LIVREUR_WAITING_COMMANDE_NB;
			$parameters['COMMAND_SHOP_FINAL_DELIVERY_THUMB_STATE'] = $times_shop->COMMAND_SHOP_FINAL_DELIVERY_THUMB_STATE;
			$parameters['COMMAND_SHOP_MSG']                        = $times_shop->COMMAND_SHOP_MSG;

			$var_liv_affect = $parameters['var_liv_affect'] = $Bin->test_livreur_affect_free($idsecteur);

			$parameters['idsecteur']      = $idsecteur;
			$parameters['var_liv_affect'] = $var_liv_affect;

			$parameters['idshop']    = $idshop;
			$parameters['idsecteur'] = $idsecteur;

			$parameters['tempsLivraison'] = $COMMAND_SHOP_FINAL_DELIVERY_DELAY_RANGE;
			$parameters['heureLivraison'] = $COMMAND_SHOP_FINAL_DELIVERY_DATETIME;


			$html = $LATTE->renderToString(APPTPL . 'fragments/livraison_estimation.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}
		}

		function commande_confirm_info() {
			Helper::dump($_POST);
		}

		function livraison_ligne($inner = false) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE']    = HTTPCUSTOMERSITE;
			$parameters['num_livraison_ligne'] = 121;
			$html                              = $LATTE->renderToString(APPTPL . 'fragments/livraison_ligne.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}
		}

		function login($inner = false) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			if (empty($_SESSION['client'])) {
				$html = $LATTE->renderToString(APPTPL . 'fragments/login.html', $parameters);

			} else {
				$html = $LATTE->renderToString(APPTPL . 'fragments/login_done.html', $parameters);

			}

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function login_multi($inner = false) {
			global $LATTE;
			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$type                           = $parameters['type'] = $_GET['type'];
			$parameters['Type']             = ucfirst($type);

			switch ($type):
				case 'livreur':
				case 'client':
				case 'delivery':
				case 'shop':
					$tpl = 'fragments/login_multi.html';
					break;
				case 'agent':
					$tpl = 'idae/fragments/login_multi.html';
					break;
			endswitch;

			$LATTE->setAutoRefresh(true);
			if (empty($_SESSION[$type])) {
				$html = $LATTE->renderToString(APPTPL . $tpl, $parameters);
			} else {
				$html = $LATTE->renderToString(APPTPL . 'fragments/login_multi_done.html', $parameters);
			}

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function login_multi_register($inner = false) {
			global $LATTE;
			$parameters = array_merge($_GET, $_POST);

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$type                           = $parameters['type'] = $_GET['type'];
			$parameters['Type']             = ucfirst($type);

			$LATTE->setAutoRefresh(true);
			$html = $LATTE->renderToString(APPTPL . 'fragments/login_multi_register.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function idae_login_multi($inner = false) {
			global $LATTE;
			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$type                           = $parameters['type'] = $_GET['type'];
			$parameters['Type']             = ucfirst($type);

			if (empty($_SESSION[$type])) {
				$html = $LATTE->renderToString(APPTPL . 'idae/fragments/login_multi.html', $parameters);

			} else {
				$html = $LATTE->renderToString(APPTPL . 'idae/fragments/login_multi_done.html', $parameters);
			}

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function login_multi_done($inner = false) {
			global $LATTE;
			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$type                           = $parameters['type'] = $_GET['type'];
			$parameters['Type']             = ucfirst($type);
			$html                           = '';
			if (!empty($_SESSION[$type])) {
				$html = $LATTE->renderToString(APPTPL . 'fragments/login_multi_done.html', $parameters);
			}

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function login_retrieve($inner = false) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$Cart                   = new Cart();
			$parameters['var_cart'] = $Cart->get_cart();

			$html = $LATTE->renderToString(APPTPL . 'fragments/login_retrieve.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function login_multi_retrieve($inner = true) {

			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$parameters['type'] = $_GET['type'];

			$html = $LATTE->renderToString(APPTPL . 'fragments/login_multi_retrieve.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}

		}

		function login_multi_init($inner = true) {
			global $LATTE;

			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;

			$parameters['type'] = $_GET['type'];
			$html               = $LATTE->renderToString(APPTPL . 'fragments/login_multi_init.html', $_GET);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}
		}

		function footer($inner = true) {
			global $LATTE;

			$APP_SECTEUR    = new App('secteur');
			$APP_SH_J       = new App('shop_jours');
			$APP_SH_J_SHIFT = new App('shop_jours_shift');
			# index jour
			$index_jour = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;

			//
			$arr_sh_j       = $APP_SH_J->distinct_all('idshop', ['actifShop_jours' => 1,
			                                                     'ordreJours'      => $index_jour]);
			$arr_sh_j_shift = $APP_SH_J_SHIFT->distinct_all('idshop', ['idshop' => ['$in' => $arr_sh_j]]); // $APP_SH_J_SHIFT
			//
			$parameters['nomJours'] = sizeof($arr_sh_j);

			#  Shop liste
			$APP_SH = new App('shop');
			$arr_sh = $APP_SH->distinct_all('idshop', ["actifShop" => 1,
			                                           'idshop'    => ['$in' => $arr_sh_j_shift]], ['_id' => 0]);

			$arr_sect   = $APP_SH->distinct_all('idsecteur', ['idshop' => ['$in' => $arr_sh_j_shift]], ['_id' => 0]);
			$rs_secteur = $APP_SECTEUR->find(['idsecteur' => ['$in' => $arr_sect]])->limit(6)->sort(['nomSecteur' => 1]);
			$rs_shop    = $APP_SH->find(['idshop' => ['$in' => $arr_sh]])->limit(6)->sort(['nomShop' => 1]);
			while ($arr_secteur = $rs_secteur->getNext()) {
				$idsecteur                                           = (int)$arr_secteur['idsecteur'];
				$parameters['var_secteur_liste'][$idsecteur]         = $arr_secteur;
				$parameters['var_secteur_liste'][$idsecteur]['link'] = Router::build_route('secteur', ['idsecteur' => $idsecteur]);
			}
			while ($arr_shop = $rs_shop->getNext()) {
				$idshop                                        = (int)$arr_shop['idshop'];
				$parameters['var_shop_liste'][$idshop]         = $arr_shop;
				$parameters['var_shop_liste'][$idshop]['link'] = Router::build_route('restaurant', ['idshop' => $idshop]);
			}
			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$html                           = $LATTE->renderToString(APPTPL . 'fragments/footer.html', $parameters);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}
		}

		function notify($inner = true) {
			global $LATTE;

			$LATTE->setAutoRefresh();
			$html = $LATTE->renderToString(APPTPL . 'fragments/notify.html', $_GET + ['warm' => 'up.' . time()]);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}
		}

		function commande_end($inner = true) {
			global $LATTE;

			$LATTE->setAutoRefresh();
			$html = $LATTE->renderToString(APPTPL . 'fragments/commande_end.html', $_GET + ['warm' => 'up.' . time()]);

			if ($inner) {
				return $html;
			} else {
				echo $html;
			}
		}
	}
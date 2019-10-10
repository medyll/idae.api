<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 18/09/2017
	 * Time: 13:32
	 *
	 * @deprecated
	 */
	class Admin extends AppSite {

		function __construct() {
			parent::__construct();

		}

		function do_action($params = ['action', 'value']) {
			//

			if (strpos($params['action'], '/') === true) {

			}
			if (strpos($params['value'], '/') === false) {
				$this->$params['action']($params['value']);
			} else {
				$this->$params['action'](explode('/', $params['value']));
			}
		}

		function entrance($type = 'shop') {

			$type          = ($type == 'livreur') ? 'delivery' : $type;
			$session_swift = ($type == 'delivery') ? 'livreur' : $type;
			$Type          = ucfirst($type);
			$template_main = "admin_$type";
			# active page
			if (empty($_SESSION[$session_swift])) {
				// login fragment
				$_GET['type']   = ($type == 'delivery') ? 'livreur' : $type;
				$this->Fragment = new Fragment();
				$this->Page     = new Page();
				if (in_array($session_swift, ['livreur', 'shop','agent'])) {
					$this->Page->set_page("application");
					$this->Page->login_multi(['type' => $session_swift]);
				} else {
					$template_main = "application";
					$this->render($this->Fragment->idae_login_multi(true));
				}

				/*AppSocket::send_cmd('act_script', ['script'    => 'post_login_multi',
				                                   'arguments' => $type ,
				                                   'options'   => []],session_id());*/

				return;

			}

			switch ($type) {
				case "delivery":
				case "livreur":
					$livreur_private_key = $_SESSION['livreur'];

					$APP_LIVREUR = new IdaeDB('livreur');
					$APP_AFF     = new IdaeDB('livreur_affectation');

					$parameters['var_livreur'] = $arr_livreur = $APP_LIVREUR->findOne(['private_key' => $livreur_private_key], ['_id' => 0]);
					$idlivreur                 = (int)$arr_livreur['idlivreur'];

					$test_aff = $APP_AFF->findOne(["idlivreur" => (int)$idlivreur, 'dateDebutLivreur_affectation' => date('Y-m-d')]);

					$Idae                        = new Idae('commande');
					$parameters['arr_statut']    = $Idae->liste_statut(['codeCommande_statut' => ['$nin' => ['RESERV']]]);// ['codeCommande_statut'=>['$in'=>['','','']]]
					$parameters['html_menu_top'] = $Idae->module('app_gui/app_menu_top');
					AppSocket::send_cmd('act_notify', ['msg' => $type], session_id());

					AppSocket::send_grantIn(['room' => "livreur_" . $idlivreur]); // room personnelle de type shop_7
					/*if (empty($test_aff['idsecteur'])) die("Aucun secteur");*/
					$idsecteur = (int)$test_aff['idsecteur'];
					AppSocket::send_grantIn(['room' => "secteur_" . $idsecteur]); // room personnelle de type shop_7

					break;
				case "shop":
					$shop_private_key = $_SESSION['shop'];

					$APP_SHOP = new IdaeDB('shop');
					$BIN      = new Bin();

					$parameters['var_shop']               = $arr_shop = $APP_SHOP->findOne(['private_key' => $shop_private_key], ['_id' => 0]);
					$idshop                               = (int)$arr_shop['idshop'];
					$parameters['idshop ']                = $idshop;
					$parameters['livreur_affect']         = sizeof($BIN->test_livreur_affect($arr_shop['idsecteur']));
					$parameters['livreur_affect_free']    = sizeof($BIN->test_livreur_affect_free($arr_shop['idsecteur']));
					$parameters['commande_shop']          = sizeof($BIN->test_commande_shop($idshop));
					$parameters['commande_shop_wait']     = sizeof($BIN->test_commande_shop_wait($idshop));
					$parameters['commande_shop_livencou'] = sizeof($BIN->test_commande_shop_livencou($idshop));

					$Idae                        = new Idae('commande');
					$parameters['html_menu_top'] = $Idae->module('app_gui/app_menu_top');
					$parameters['arr_statut']    = $Idae->liste_statut(['codeCommande_statut' => ['$nin' => ['RESERV']]]);// ['codeCommande_statut'=>['$in'=>['','','']]]

					AppSocket::send_cmd('act_notify', ['msg' => $type], session_id());
					AppSocket::send_grantIn(['room' => "shop_" . $idshop]); // room personnelle de type shop_7
					break;
				default:

					$session_key                                      = $_SESSION[$type];
					$name_id_session_key                              = "id$type";
					$ARR_SESSION                                      = new App($type);
					$parameters['var_session']                        = $arr_session = $ARR_SESSION->findOne(['private_key' => $session_key], ['_id' => 0]);
					$idsession                                        = (int)$arr_session[$name_id_session_key];
					$parameters['var_session']['type']                = $type;
					$parameters['var_session']['session_key']         = $session_key;
					$parameters['var_session']['Session_key']         = ucfirst($session_key);
					$parameters['var_session']['name_id_session_key'] = $name_id_session_key;
					$parameters['var_session']['idsession']           = $idsession;
					$parameters['var_session']['nom']                 = $parameters['var_session']["nom" . $Type];
					$parameters['var_session']['prenom']              = $parameters['var_session']["prenom" . $Type];

					$Idae                        = new Idae('commande');
					$parameters['html_menu_top'] = $Idae->module('app_gui/app_menu_top');

					AppSocket::send_cmd('act_notify', ['msg' => $type], session_id());
					AppSocket::send_grantIn(['room' => $type . "_" . $idsession]);

					$template_main = "application";
					break;
			}

			$parameters['html_menu_debug']      = $Idae->module('app_dev/debug_times');

			# active page
			$this->set_page($template_main);

			global $LATTE;
			$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
			$tpl_url                        = ($type == 'agent') ? "idae" : 'pages';
			$LATTE->setAutoRefresh(true);

			$url = APPTPL ."/idae/application";
			$html = $LATTE->renderToString( "$url.html", $parameters);

			$this->render($html);

		}

	}

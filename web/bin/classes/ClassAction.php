<?php

	use PHPMailer\PHPMailer\PHPMailer;

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 29/08/2017
	 * Time: 16:56
	 */
	class Action extends App {

		private $currentCommande;

		function __construct() {
			parent::__construct();
		}

		function do_action($params = ['action',
		                              'value']) {
			//  recevoir $params[value]  /idclient:122/745/array_values:125:457:485:475
			$values_params = [];
			$values        = explode('/', $params['value']);
			foreach ($values as $key_values => $value_node) {
				if (strpos($value_node, ':') === false) {
					$values_params[$key_values] = $value_node;
					continue;
				}
				$tmp_node = explode(':', $value_node);
				if (sizeof($tmp_node) == 2) {
					$values_params[$tmp_node[0]] = $tmp_node[1];
				} elseif (sizeof($tmp_node) == 1) {
					$values_params[] = $tmp_node[0];
				} else {
					$node_key = $tmp_node[0];
					unset($tmp_node[0]);
					$values_params[$node_key] = array_values($tmp_node);
				}
			}
			//
			$this->$params['action']($values_params);
		}

		function login() {
			$table        = 'client';
			$Table        = ucfirst($table);
			$param        = $_POST;
			$json_message = ['err' => '',
			                 'msg' => ''];

			foreach ($param as $key => $post) {

				$post = filter_var($post, FILTER_SANITIZE_STRING);
				if (strpos($key, 'email') !== false) {
					$post = filter_var($post, FILTER_VALIDATE_EMAIL);
				}

				$param[$key] = $post;
			}

			if (!empty($param['emailClient']) && !empty($param['passwordClient'])) {

				$APP_CLI  = new App('client');
				$test_cli = $APP_CLI->findOne(['passwordClient' => md5($param['passwordClient']),
				                               'emailClient'    => $param['emailClient']]);

				if (!empty($test_cli['idclient'])) {
					$_SESSION['client']          = $test_cli['private_key'];
					$_SESSION['client_identity'] = $test_cli['prenomClient'] . ' ' . $test_cli['nomClient'];
					$json_message                = ['err' => 0,
					                                'msg' => 'good'];
				} else {
					$json_message = ['err' => 1,
					                 'msg' => 'bad'];
				}

			}

			// cart !!
			$Cart     = new Cart();
			$var_cart = $Cart->get_cart();

			if (!empty($var_cart['cart_lines']) && !empty($var_cart['cart_adresse'])) {

			}

			echo json_encode($json_message, JSON_FORCE_OBJECT);
		}

		function login_multi() {
			global $LATTE;

			$param        = $_POST;
			$type         = $parameters['type'] = $_POST['type'];
			$Type         = ucfirst($type);
			$json_message = ['err' => '',
			                 'msg' => ''];

			foreach ($param as $key => $post) {

				$post = filter_var($post, FILTER_SANITIZE_STRING);
				if (strpos($key, 'email') !== false) {
					$post = filter_var($post, FILTER_VALIDATE_EMAIL);
				}

				$param[$key] = $post;
			}

			$login_field = ($type == 'agent') ? "login" : "email";

			if (!empty($param["$login_field$Type"]) && !empty($param["password$Type"])) {

				$APP_CLI = new App($type);

				$test_cli = $APP_CLI->findOne(["password$Type"     => md5($param["password$Type"]),
				                               "$login_field$Type" => $param["$login_field$Type"]]);
				if (empty($test_cli["id$type"])) {
					$test_cli = $APP_CLI->findOne(["password$Type"     => trim($param["password$Type"]),
					                               "$login_field$Type" => trim($param["$login_field$Type"])]);
					if (!empty($test_cli["id$type"])) {
						$test_cli['private_key'] = md5($test_cli['password' . $Type] . $test_cli['dateCreation' . ucfirst($type)]);
						$APP_CLI->update(["id$type" => (int)$test_cli["id$type"]], ['private_key' => $test_cli['private_key']]);
					}
				}
				if (!empty($test_cli["id$type"])) {
					unset($_SESSION["client"], $_SESSION["livreur"], $_SESSION["shop"]);

					$IdaeSession = IdaeSession::getInstance();
					$IdaeSession->setSession($type, $test_cli);

					$session = $IdaeSession->get_session();

					/*$_SESSION["type_session"]      = $type;
					$_SESSION["idtype_session"]    = (int)$test_cli["id$type"];
					$_SESSION["id$type"]           = (int)$test_cli["id$type"];
					$_SESSION[$type]               = $test_cli['private_key'];
					$_SESSION[$type . "_identity"] = $test_cli["prenom$Type"] . ' ' . $test_cli["nom$Type"];*/

					$json_message = ['err'  => 0,
					                 'msg'  => 'good',
					                 'json' => json_encode($_SESSION)];

					# join socket pour publication
					AppSocket::send_grantIn(['room' => $type . "_" . $test_cli["id$type"]]); // room personnelle de type shop_7
					if ($type == 'livreur') {
						$APP_AFF  = new App('livreur_affectation');
						$test_aff = $APP_AFF->findOne(["idlivreur"                    => (int)$test_cli["id$type"],
						                               'dateDebutLivreur_affectation' => date('Y-M-D')]);
						if (!empty($test_aff["idsecteur"])) {
							AppSocket::send_grantIn(['room' => "secteur_" . $test_aff["idsecteur"]]); // room secteur

						}
					}
					$json_message['data_html'] = html_entity_decode($LATTE->renderToString(APPTPL . "fragments/login_multi_success.html", ['type' => $type,
					                                                                                                                       'data' => $test_cli]));
					$json_message['type']      = $type;
				} else {

					$json_message              = ['err' => 1,
					                              'msg' => "L'adresse mail ou le mot de passe ne sont pas reconnus"];
					$json_message['data_html'] = html_entity_decode($LATTE->renderToString(APPTPL . "fragments/login_multi_fail.html", ['type' => $type,
					                                                                                                                    'data' => $test_cli,
					                                                                                                                    'msg'  => "L'adresse mail ou le mot de passe ne sont pas reconnus"]));

				}

			}

			echo json_encode($json_message, JSON_FORCE_OBJECT);
		}

		function room_reconnect() {
			if (!empty($_SESSION["type_session"])) {
				$type        = $_SESSION["type_session"];
				$Type        = ucfirst($type);
				$name_idtype = "id$type";
				$idtype      = (int)$_SESSION["idtype_session"];
				$APP_SESSION = new App($type);
				$test        = $APP_SESSION->findOne([$name_idtype => $idtype]);
				if (empty($test[$name_idtype])) return;
				AppSocket::send_grantIn(['room' => 'room_' . $type], session_id()); // room total de type shop agent livreur
				AppSocket::send_grantIn(['room' => $type . "_" . $test["id$type"]], session_id()); // room personnelle de type shop_7 agent_1 livreur_3
				if ($type == 'livreur') {
					$APP_AFF  = new App('livreur_affectation');
					$test_aff = $APP_AFF->findOne(["idlivreur"                    => (int)$test["id$type"],
					                               'dateDebutLivreur_affectation' => date('Y-M-D')]);
					if (!empty($test["idsecteur"])) {
						AppSocket::send_grantIn(['room' => "secteur_" . $test["idsecteur"]], session_id());
					}
					//NotifySite::notify_idae('send_grantIn livreur');
				}
				//NotifySite::notify_idae('Vous êtes connecté !!');
			}
		}

		function logout() {
			unset($_SESSION['client'], $_SESSION['client_identity']);
		}

		function login_multi_retrieve() {
			include_once(APPCLASSES . 'ClassSMTP.php');
			global $LATTE;
			$param    = $_POST;
			$type     = $_POST['type'];
			$Type     = ucfirst($type);
			$tmp_ssid = session_id();
			$APP_CLI  = new App($type);

			$failed       = 0;
			$json_message = ['msg'     => null,
			                 'err'     => 1,
			                 'success' => null];
			AppSocket::send_cmd('act_notify', ['msg' => 'Recherche du mail utilisateur'], session_id());
			$test_cli = $APP_CLI->findOne(["email$Type" => $param["email$Type"]]);
			if (empty($test_cli["id$type"])) {
				$json_message['err']     = 1;
				$json_message['success'] = null;
				$json_message['msg']     = 'Erreur enregistrement non trouvé ';

				AppSocket::send_cmd('act_notify', ['msg' => 'Erreur enregistrement non trouvé '], session_id());

				echo json_encode($json_message, JSON_FORCE_OBJECT);

			} else {
				AppSocket::send_cmd('act_notify', ['msg' => 'Envoi du mail'], session_id());

				$private_key = $test_cli['private_key'];
				$link        = HTTPCUSTOMERSITE . "page/login_init/$type/{$private_key}";
				$body        = html_entity_decode($LATTE->renderToString(APPTPL . "fragments/login_init_mail.html", ['type' => $type,
				                                                                                                     'Type' => $Type,
				                                                                                                     'link' => $link,
				                                                                                                     'data' => $test_cli,
				                                                                                                     'msg'  => "Votre mot de passe"]));

				$mail = new PHPMailer();

				$mail->IsSMTP();
				$mail->IsHTML();
				$mail->WordWrap    = 50;
				$mail->SMTPDebug   = 0;
				$mail->SMTPAuth    = true;
				$mail->SMTPOptions = [ // PREPROD ?
				                       'ssl' => ['verify_peer'       => false,
				                                 'verify_peer_name'  => false,
				                                 'allow_self_signed' => true]];
				$mail->CharSet     = 'UTF-8';
				$mail->Hostname    = SMTPDOMAIN;
				$mail->Helo        = SMTPDOMAIN;
				$mail->Host        = SMTPHOST;
				$mail->Username    = SMTPUSER;
				$mail->Password    = SMTPPASS;
				$mail->SetFrom(SMTPUSER, 'postmaster tac-tac');
				// $mail->AddReplyTo($_POST['emailFrom'] , $_POST['emailFromName']);
				$mail->Subject = "Récupération de votre mot de passe";
				$mail->AltBody = strip_tags($body);
				$mail->AddAddress($test_cli["email$Type"], 'destinataire');

				$mail->MsgHTML($body);

				if (!$mail->Send()) {
					AppSocket::send_cmd('act_notify', ['msg' => 'Erreur envoi'], session_id());
					echo "Mailer Error: " . $mail->ErrorInfo;
				} else {
					$json_message['data_html'] = html_entity_decode($LATTE->renderToString(APPTPL . "fragments/login_multi_retrieve_send.html", ['type' => $type,
					                                                                                                                             'Type' => $Type,
					                                                                                                                             'link' => $link,
					                                                                                                                             'data' => $test_cli,
					                                                                                                                             'msg'  => "Votre mot de passe"]));
					$json_message['err']       = 0;
					$json_message['success']   = 1;
					$json_message['type']      = $type;
					$json_message['msg']       = 'Enregistrement  trouvé ! ';
					$json_message['msg_key']   = $test_cli['private_key'];

					AppSocket::send_cmd('act_notify', ['msg' => 'Email envoyé !'], session_id());

					echo json_encode($json_message, JSON_FORCE_OBJECT);
				}
			}
		}

		function logout_multi() {
			global $LATTE;
			$type                      = $_POST['type'];
			$tmp_ssid                  = session_id();
			$json_message['data_html'] = html_entity_decode($LATTE->renderToString(APPTPL . "fragments/login_multi_dead.html", ['type' => $type]));
			echo json_encode($json_message, JSON_FORCE_OBJECT);

			unset($_SESSION[$type], $_SESSION[$type . '_identity']);
			// Détruit toutes les variables de session
			$_SESSION = [];

			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
			}

			session_destroy();
			AppSocket::send_cmd('act_script', ['script'    => 'post_logout_shop',
			                                   'arguments' => [],
			                                   'options'   => []], $tmp_ssid);
		}

		function register_again($post_vars = []) {
			global $LATTE;

			$post_vars      = array_filter($post_vars);
			$param          = (sizeof($post_vars) == 0) ? $_POST : $post_vars;
			$failed         = 0;
			$empty_fields   = [];
			$insert_field   = [];
			$json_message   = ['msg'     => null,
			                   'err'     => 1,
			                   'success' => null];
			$allowed_fields = ['nom',
			                   'prenom',
			                   'adresse',
			                   'codePostal',
			                   'ville',
			                   'email',
			                   'password'];
			$type           = empty($post_vars['type']) ? 'client' : $post_vars['type'];
			$Type           = ucfirst($type);
			$APP_CLI        = new App($type);

			foreach ($param as $key => $post) {
				$post = filter_var($post, FILTER_SANITIZE_STRING);
				if (strpos($key, 'email') !== false) {
					$post = filter_var($post, FILTER_VALIDATE_EMAIL);
				}

				if (empty($post)) {
					$empty_fields[$key] = $post;
					$failed             = 1;
				} else {
					$param[$key] = $post;
				}
			}

			if ($param["password$Type"] != $param["password$Type" . "_verif"]) {
				$json_message['msg'] = ['mauvais mot de passe' => $param["password$Type"]];
				$failed              = 1;
			}
			$test_cli = $APP_CLI->findOne(['private_key' => $param['private_key']], ['_id' => 0]);
			if (empty($test_cli["id$type"])) {
				$json_message['msg'] = ["$Type absent pour clef " => $param['private_key']];
				$failed              = 1;
			}
			if ($failed == 0) {
				$json_message['err']           = 0;
				$insert_field["password$Type"] = md5($param["password$Type"]);
				$insert_field['private_key']   = md5($test_cli["prenom$Type"] . time() . $test_cli["ville$Type"] . $test_cli["email$Type"] . $test_cli["codePostal$Type"]);
				$APP_CLI->update_native(['private_key' => $param['private_key']], $insert_field);

				AppSocket::send_cmd('act_notify', ['msg' => "Modification terminée, nouveau mot de passe $Type "], session_id());
				$json_message['data_html'] = html_entity_decode($LATTE->renderToString(APPTPL . "fragments/login_multi_success.html", ['type' => $type,
				                                                                                                                       'data' => $insert_field]));

				$_SESSION["type_session"]      = $type;
				$_SESSION["idtype_session"]    = (int)$test_cli["id$type"];
				$_SESSION["id$type"]           = (int)$test_cli["id$type"];
				$_SESSION[$type]               = $insert_field['private_key'];
				$_SESSION[$type . "_identity"] = $test_cli["prenom$Type"] . ' ' . $test_cli["nom$Type"];
				//
				$idclient    = (int)$test_cli["id$type"];
				$room_client = 'client_' . $idclient;
				AppSocket::send_grantIn(['room' => $room_client]);
			}
			echo json_encode($json_message, JSON_FORCE_OBJECT);

		}

		function register($post_vars = []) {
			global $LATTE;

			$type            = 'client';
			$Type            = ucfirst($type);
			$post_vars       = array_filter($post_vars);
			$param           = (sizeof($post_vars) == 0) ? $_POST : $post_vars;
			$failed          = 0;
			$empty_fields    = [];
			$insert_field    = [];
			$error_msg       = [];
			$json_message    = ['msg'     => null,
			                    'err'     => 1,
			                    'success' => null];
			$allowed_fields  = ['nom',
			                    'prenom',
			                    'adresse',
			                    'codePostal',
			                    'ville',
			                    'email',
			                    'password',
			                    'telephone'];
			$required_fields = ['nom',
			                    'prenom',
			                    'password',
			                    'email'];
			$type            = 'client';
			$APP_CLI         = new App($type);
			// Helper::dump($param);

			foreach ($param as $key => $post) {

				$post = filter_var($post, FILTER_SANITIZE_STRING);
				if (strpos($key, 'email') !== false) {
					$post = filter_var($post, FILTER_VALIDATE_EMAIL);
				}

				if (empty($post) && in_array($post, $required_fields)) {
					$empty_fields[$key] = $post;
					$failed             = 1;
				} else {
					$param[$key] = $post;
				}
			}
			// password idem ?
			if (!empty($param['passwordClient']) && $param['passwordClient'] != $param['passwordClient_verif']) {
				$json_message['msg'] = ['mauvais mot de passe' => $param['passwordClient'] . ' ' . $param['passwordClient_verif']];
				$error_msg[]         = 'Mauvaise vérification du mot de passe';
				$failed              = 1;
			}
			// EMAIL still ?
			$test_email = $APP_CLI->findOne(['emailClient' => $param['emailClient']], ['_id' => 0]);
			if (!empty($test_email['idclient'])) {
				$failed = 1;
			}

			// all ok dude ...
			if ($failed == 1) {
				$json_message['err']     = 1;
				$json_message['success'] = null;

				if (!empty($test_email['idclient'])) {
					$json_message['msg'] = 'Client existant : ' . $test_email['nomClient'];
					$error_msg[]         = 'Client déja existant pour cet email ' . $test_email['emailClient'];
				}
				if (sizeof($empty_fields) != 0) {
					$json_message['msg'] = ['Erreur inconnue ' => $empty_fields];
					$error_msg[]         = 'Saisie manquante ';
				}
				//$json_message['msg'] = ['mauvais mot de passe' => $param['passwordClient'] . ' ' . $param['passwordClient_verif']];
				//AppSocket::send_cmd('act_notify', ['msg' => 'Erreur enregistrment '.$json_message['msg']], session_id());
				NotifySite::notify_modal('Erreur enregistrement', 'error', ['mdl_vars' => ['msg_array' => $error_msg]], session_id());

				echo json_encode($json_message, JSON_FORCE_OBJECT);

			} else {
				# ok pour enregistrement
				foreach ($allowed_fields as $field) {
					$insert_field[$field . 'Client'] = $param[$field . 'Client'];
				}
				# md5 pour mot de passe
				$insert_field['passwordClient'] = md5($insert_field['passwordClient']);

				$insert_field['private_key'] = md5($insert_field['prenomClient'] . time() . $insert_field['villeClient'] . $insert_field['emailClient'] . $insert_field['codePostalClient']);

				// Helper::dump($insert_field);
				$json_message['msg']     = ['Client enregistré' => $insert_field];
				$json_message['success'] = 1;
				$json_message['err']     = 0;

				$LATTE->setAutoRefresh(true);
				if (ENVIRONEMENT != 'PREPROD_LAN') {
					$json_message['data_html'] = html_entity_decode($LATTE->renderToString(APPTPL . "fragments/login_multi_success.html", ['type' => $type,
					                                                                                                                       'data' => $insert_field]));

				}

				$idclient = $APP_CLI->insert($insert_field);

				$_SESSION['client']          = $insert_field['private_key'];
				$_SESSION['client_identity'] = $insert_field['prenomClient'] . ' ' . $insert_field['nomClient'];

				// notify ok
				$room_client = 'client_' . $idclient;
				AppSocket::send_grantIn(['room' => $room_client]);
				//AppSocket::send_cmd('act_notify', ['msg' => 'Enregistrement ok '], $room_client);
				NotifySite::notify_modal('Enregistrement validé ' . $insert_field['nomClient'], 'success', ['mdl_vars' => ['msg_array' => $error_msg]], $room_client);

				echo json_encode($json_message, JSON_FORCE_OBJECT);

				// NotifySite::notify_modal('Vérification de votre saisie', 'error', ['mdl_vars' => ['msg_array' => $json_message]],$room_client);

				return (int)$idclient;
			}

		}

		function create_commande() {

		}

		function delivery_reserv($array_vars = []) {
			global $LATTE;

			$idcommande = empty($array_vars['idcommande']) ? (int)$_POST['idcommande'] : (int)$array_vars['idcommande'];

			$APP_COMMANDE        = new App("commande");
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_LIVREUR         = new App('livreur');

			if (empty($array_vars['idlivreur'])) {
				$_livreur_session = $_SESSION['livreur'];
				$arr_livreur      = $APP_LIVREUR->findOne(['private_key' => $_livreur_session]);
				$idlivreur        = (int)$arr_livreur['idlivreur'];
			} else {
				$arr_livreur = $APP_LIVREUR->findOne(['idlivreur' => (int)$array_vars['idlivreur']]);
				$idlivreur   = (int)$arr_livreur['idlivreur'];

			}

			$arr_commande = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);
			$idshop       = (int)$arr_commande['idshop'];
			$idsecteur    = (int)$arr_commande['idsecteur'];
			$room_livreur = "livreur_$idlivreur";

			if (empty($arr_commande['idlivreur'])) {
				// nombre de commandes en cours !!
				$max_commande             = 1;
				$test_max_commande        = $APP_COMMANDE->find(['dateCommande'        => date('Y-m-d'),
				                                                 'idlivreur'           => $idlivreur,
				                                                 'codeCommande_statut' => ['$nin' => ['END']]]);
				$test_max_commande_before = $APP_COMMANDE->find(['dateCommande'        => date('Y-m-d'),
				                                                 'idsecteur'           => $idsecteur,
				                                                 'idlivreur'           => ['$in' => ['',
				                                                                                     null,
				                                                                                     0]],
				                                                 'codeCommande_statut' => ['$nin' => ['END']]])->sort(['timeFinPreparationCommande' => 1]);
				$test_max_commande_prefin = $APP_COMMANDE->find(['dateCommande'        => date('Y-m-d'),
				                                                 'idsecteur'           => $idsecteur,
				                                                 'idlivreur'           => $idlivreur,
				                                                 'codeCommande_statut' => ['$in' => ['PREFIN']]])->sort(['timeFinPreparationCommande' => 1]);
				//
				$arr_test_first = $test_max_commande_before->getNext();

				if ($arr_test_first['idcommande'] != $idcommande) {
					//NotifySite::notify_modal('Merci de prendre la premiere commande disponible', 'error', null, $room_livreur);

					return false;
				}
				//Helper::dump(iterator_to_array($test_max_commande));
				if ($test_max_commande->count() >= $max_commande) {
					NotifySite::notify_idae('trop de commandes ou commande en cours', 'alert', null);

					return false;
				}
				$insert_field = [];
				// statut + livreur
				$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'RESERV']);
				if ((int)$arr_commande['ordreCommande_statut'] < (int)$arr_commande_statut['ordreCommande_statut']) {
					// statut "reserv" seulement si pas deja avancée
					$insert_field['idcommande_statut']    = (int)$arr_commande_statut['idcommande_statut'];
					$insert_field['ordreCommande_statut'] = (int)$arr_commande_statut['ordreCommande_statut'];
					$insert_field['codeCommande_statut']  = 'RESERV';

				}
				$insert_field['idlivreur'] = $idlivreur;
				$insert_field['timeFinPreparationCommande'];

				return $idcommande;

			} else {
				$msg  = "Un livreur est déja affecté à cette commande";
				$icon = "ban";
				echo $data_html = html_entity_decode($LATTE->renderToString(APPTPL . "fragments/admin/admin_delivery_command_reserv.html", ['msg'  => $msg,
				                                                                                                                            'icon' => $icon]));

				return false;
			}

			return false;
		}

		//
		function commande_charge($arr_vars = []) {

			$arr_vars = array_filter($arr_vars);
			$param    = (sizeof($arr_vars) == 0) ? $_POST : $arr_vars;
			$param    = array_merge($param, json_decode($_SESSION['commande_data'] ?: "{}", JSON_OBJECT_AS_ARRAY));

			$Cart     = new Cart(session_id());
			$arr_cart = $Cart->get_cart();

			$param    = $this->commande_filter_vars($param);
			$pre_test = $this->commande_test_info($param);

			if (!empty($pre_test['err'])) {

				NotifySite::notify_modal('Erreur', 'alert', ['mdl_vars' => ['msg_array' => $pre_test['msg']]], session_id());

				return false;
			}

			if ($this->commande_set_info($param) != false) {
				$APP_COMMANDE = new App('commande');
				$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => (int)$this->currentCommande]);

				$parameters['api_key']     = "pk_test_uTCUev6Hzay5EyoYM5fmeRjw";
				$parameters['scr_api_key'] = "sk_test_V46Tk6DpdrbX2nkk4EH7hOLn";
				$cart_centimes             = $arr_cart['cart_total'] * 100;
				\Stripe\Stripe::setApiKey($parameters['scr_api_key']);
				$token = $param['stripeToken'];

				$charge = \Stripe\Charge::create(['amount'        => $cart_centimes,
				                                  'currency'      => 'eur',
				                                  'source'        => $token,
				                                  'metadata'      => ['orderId' => $ARR_COMMANDE['codeCommande']],
				                                  'receipt_email' => $param['emailCommande']]);

				if (empty($ARR_COMMANDE['demo_mode'])) {
					$table       = 'commande';
					$table_value = (int)$this->currentCommande;
					$Table       = ucfirst($table);

					$Idae    = new Idae($table);
					$AppMail = new AppMail();
					$instyle = new InStyle();
					$Idae->consolidate_scheme($table_value);
					$ARR_COMMANDE = $Idae->findOne(["id$table" => (int)$table_value]);
					$Body         = $instyle->convert($Idae->fiche_mail($table_value), true);
					$AppMail->set_body($Body);
					$AppMail->set_destinataire_email($ARR_COMMANDE["email$Table"]);
					$AppMail->set_destinataire_name($ARR_COMMANDE["prenom$Table"] . ' ' . $ARR_COMMANDE["nom$Table"]);
					$AppMail->set_subject('Votre commande ' . $ARR_COMMANDE["code$Table"] . ' avec TAC-TAC');
					$AppMail->sendMail();

				}
				$return = ['success' => HTTPCUSTOMERSITE . 'commande/commande_end'];
				$this->send_data($return);
			} else {
				// on fail
				$return = ['error' => "Une erreur a eu lieu"];
				$this->send_data($return);
			}

		}

		function commande_filter_vars($param = []) {

			foreach ($param as $key => $post) {

				$post = filter_var(trim($post), FILTER_SANITIZE_STRING);
				if (strpos($key, 'telephone') !== false) {
					$post = filter_var($post, FILTER_SANITIZE_NUMBER_INT);
				}
				if (strpos($key, 'email') !== false) {
					$post = filter_var($post, FILTER_VALIDATE_EMAIL);
				}

				$param[$key] = $post;
			}

			return $param;
		}

		function commande_test_info($arr_vars = []) {
			$APP_SHOP = new App('shop');

			$arr_vars = array_filter($arr_vars);

			if (empty($_SESSION['commande_data'])) {
				$json_message['err']   = 1;
				$json_message['msg'][] = 'pas de commande en cours';
			}
			if (sizeof($arr_vars) == 0) {
				$json_message['err']   = 1;
				$json_message['msg'][] = 'Auncun parametre';
			}
			if (sizeof($arr_vars) == 0) {
				$json_message['err']   = 1;
				$json_message['msg'][] = 'Le panier est vide';
			}

			if (sizeof(json_decode($_SESSION['commande_data'], JSON_OBJECT_AS_ARRAY)) == 0) {
				$json_message['err']   = 1;
				$json_message['msg'][] = 'La commande est vide';
			}

			if ($arr_vars['demo_mode']) {
				$Cart     = new Cart('client_demo_' . $arr_vars['idclient']);
				$arr_cart = $Cart->get_cart();
			} else {
				$Cart     = new Cart();
				$arr_cart = $Cart->get_cart();
			}

			$idshop    = (int)$arr_cart['idshop'];
			$ARR_SHOP  = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$idsecteur = (int)$ARR_SHOP['idsecteur'];

			$param        = $arr_vars;
			$json_message = [];
			$failed       = 0;
			$empty_fields = [];
			$insert_field = [];

			$required_fields = ['nom',
			                    'prenom',
			                    'telephone',
			                    'adresse',
			                    'codePostal',
			                    'ville'];

			foreach ($param as $key => $post) {

				$post = filter_var(trim($post), FILTER_SANITIZE_STRING);
				if (strpos($key, 'telephone') !== false) {
					$post = filter_var($post, FILTER_SANITIZE_NUMBER_INT);
				}
				if (strpos($key, 'email') !== false) {
					$post = filter_var($post, FILTER_VALIDATE_EMAIL);
				}

				if (empty($post) && in_array($key, $required_fields)) {
					$empty_fields[$key]    = $post;
					$json_message['err']   = 1;
					$json_message['msg'][] = 'Un champ est manquant : ' . $key;
				} else {
					$param[$key] = $post;
				}
			}

			NB_MAX_COMMANDE_SECTEUR_LIVREUR;

			$nb_liv_affect    = CommandeQueue::secteur_has_livreur_count($idsecteur);
			$nb_commande_wait = CommandeQueue::shop_commande_queue_count($idshop);

			if (($nb_liv_affect * NB_MAX_COMMANDE_SECTEUR_LIVREUR) <= $nb_commande_wait) {
				$json_message['err']   = 1;
				$json_message['msg'][] = 'Commande impossible actuellement, trop de commandes actuellement en cours pour nos coursiers';
			}

			if ($nb_commande_wait > NB_MAX_COMMANDE_SHOP) {
				$json_message['err']   = 1;
				$json_message['msg'][] = 'Commande impossible, trop de commandes actuellement en cours';
			}

			$index_jour     = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;
			$NOW            = date('H:i:s');
			$APP_SH_J       = new App('shop_jours');
			$APP_SH_J_SHIFT = new App('shop_jours_shift');
			$arr_sh_j       = $APP_SH_J->findOne(['idshop'     => $idshop,
			                                      'ordreJours' => $index_jour]);
			$arr_sh_shift   = iterator_to_array($APP_SH_J_SHIFT->find(['idshop'                     => $idshop,
			                                                           'idshop_jours'               => (int)$arr_sh_j['idshop_jours'],
			                                                           'actifShop_jours_shift'      => 1,
			                                                           'heureDebutShop_jours_shift' => ['$lte' => $NOW],
			                                                           'heureFinShop_jours_shift'   => ['$gte' => $NOW]], ['_id' => 0])->sort(['heureDebutShop_jours_shift' => 1]));
			if (sizeof($arr_sh_shift) != 0) {
				$insert_field['idshop_jours_shift'] = (int)$arr_sh_shift[0]['idshop_jours_shift'];
			} else {
				$json_message['err']   = 1;
				$json_message['msg'][] = 'Aune disponibilité restaurant actuellement';

			}

			return $json_message;

		}

		function commande_set_info($arr_vars = []) {

			$arr_vars = array_filter($arr_vars);

			$param           = (sizeof($arr_vars) == 0) ? $_POST : $arr_vars;
			$json_message    = ['err' => 0,
			                    'msg' => 'good'];
			$failed          = 0;
			$empty_fields    = [];
			$insert_field    = [];
			$allowed_fields  = ['nom',
			                    'prenom',
			                    'telephone',
			                    'email',
			                    'adresse',
			                    'adresse2',
			                    'codePostal',
			                    'ville'];
			$required_fields = ['nom',
			                    'prenom',
			                    'telephone',
			                    'adresse',
			                    'codePostal',
			                    'ville'];

			$BIN                 = new Bin();
			$APP_COMMANDE        = new App('commande');
			$APP_COMMANDE_LIGNE  = new App('commande_ligne');
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_CLI             = new App('client');

			foreach ($param as $key => $post) {

				$post = filter_var(trim($post), FILTER_SANITIZE_STRING);
				if (strpos($key, 'telephone') !== false) {
					$post = filter_var($post, FILTER_SANITIZE_NUMBER_INT);
				}
				if (strpos($key, 'email') !== false) {
					$post = filter_var($post, FILTER_VALIDATE_EMAIL);
				}

				if (empty($post) && in_array($key, $required_fields)) {
					$empty_fields[$key] = $post;
					$failed             = 1;
				} else {
					$param[$key] = $post;
				}
			}

			// all ok dude ...
			if ($failed == 1) {
				if (sizeof($empty_fields) != 0) {
					// Helper::dump($empty_fields);
					$json_message = ['err' => 1,
					                 'msg' => $empty_fields];
				}
				$json_message = ['err' => 1,
				                 'msg' => 'bad champ manquand'];
				Helper::dump($json_message);

				return false;
			} else {
				#
				global $LATTE;
				# ok pour enregistrement
				foreach ($allowed_fields as $field) {
					$insert_field[$field . 'Commande'] = $param[$field . 'Commande'];
				}

				if ($arr_vars['idclient']) {
					$test_cli = $APP_CLI->findOne(['idclient' => (int)$arr_vars['idclient']]);
				} else {
					$test_cli = $APP_CLI->findOne(['private_key' => $_SESSION['client']]);
				}

				// commande_ligne, shop, secteur, produit => from cart
				if ($arr_vars['demo_mode']) {
					$Cart     = new Cart('client_demo_' . $arr_vars['idclient']);
					$arr_cart = $Cart->get_cart();
				} else {
					$Cart     = new Cart();
					$arr_cart = $Cart->get_cart();
				}

				$duree_realisationCommande = DUREE_REALISATION_COMMANDE;// de la prise de commande à la livraison, en minutes
				$time_preparation_commande = TIME_PREPARATION_COMMANDE;// rand(4, 7);
				$temps_livraison           = TEMPS_LIVRAISON_COMMANDE;// rand(4, 7);

				$idshop    = (int)$arr_cart['idshop'];
				$idsecteur = (int)$arr_cart['idsecteur'];
				$idclient  = (int)$test_cli['idclient'];

				$nb_commande_wait                  = CommandeQueue::shop_commande_queue_count($idshop);//$BIN->shop_commande_queue($idshop);
				$json_message['infoenplus']        = $nb_commande_wait;
				$json_message['infoenplus_encore'] = NB_MAX_COMMANDE_SHOP;
				// AppSocket::send_cmd('act_notify', ['msg' => 'test_livreur_affect_free '.sizeof($arr_liv_free).' pour '.$idshop],"secteur_$idsecteur");
				if ($nb_commande_wait > NB_MAX_COMMANDE_SHOP) {
					$json_message['err'] = 1;
					$json_message['msg'] = 'trop de commandes actuellement';
					if ($arr_vars['demo_mode']) {
					} else {
						AppSocket::send_cmd('act_notify', ['msg' => 'Commande impossible, trop de commandes actuellement en cours '], session_id());
					}

					return false;
				}
				++$nb_commande_wait;
				$ordreCommande = $nb_commande_wait;

				$insert_field['idclient']                  = $idclient;
				$insert_field['dateCommande']              = date('Y-m-d');
				$insert_field['dateCreationCommande']      = date('Y-m-d');
				$insert_field['heureCommande']             = date('H:i:00');
				$insert_field['timeCommande']              = time();
				$insert_field['timeCreationCommande']      = time();
				$insert_field['heureCreationCommande']     = date('H:i:s');
				$insert_field['idshop']                    = (int)$arr_cart['idshop'];
				$insert_field['idsecteur']                 = (int)$arr_cart['idsecteur'];
				$insert_field['volumeCommande']            = $arr_cart['cart_total_volume'];
				$insert_field['duree_realisationCommande'] = $duree_realisationCommande;//(int)$arr_cart['cart_total_time'];
				$insert_field['prixCommande']              = (float)$arr_cart['cart_total'];
				$insert_field['prixServiceCommande']       = (float)$arr_cart['cart_sous_total'];
				$insert_field['rangCommande']              = "S" . $ordreCommande;
				$insert_field['ordreCommande']             = $ordreCommande;
				if ($arr_vars['demo_mode']) {
					$insert_field['demo_mode']              = $arr_vars['demo_mode'];
					$insert_field['dureeLivraisonCommande'] = rand(180, 600);
					$insert_field['distanceCommande']       = rand(600, 3000);
				}
				//
				if (empty($idshop)) {
					return false;
				}
				//
				// shift ?
				$index_jour     = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;
				$NOW            = date('H:i:s');
				$APP_SHOP       = new App('shop');
				$APP_SH_J       = new App('shop_jours');
				$APP_SH_J_SHIFT = new App('shop_jours_shift');
				$APP_SHIFT_RUN  = new App('shop_jours_shift_run');
				$arr_sh_j       = $APP_SH_J->findOne(['idshop'     => $idshop,
				                                      'ordreJours' => $index_jour]);
				$arr_sh_shift   = iterator_to_array($APP_SH_J_SHIFT->find(['idshop'                     => $idshop,
				                                                           'idshop_jours'               => (int)$arr_sh_j['idshop_jours'],
				                                                           'actifShop_jours_shift'      => 1,
				                                                           'heureDebutShop_jours_shift' => ['$lte' => $NOW],
				                                                           'heureFinShop_jours_shift'   => ['$gte' => $NOW]], ['_id' => 0])->sort(['heureDebutShop_jours_shift' => 1]));
				if (sizeof($arr_sh_shift) != 0) {
					$insert_field['idshop_jours_shift'] = (int)$arr_sh_shift[0]['idshop_jours_shift'];
				} else {
					// Aucun shifts !!!
					Helper::dump($json_message);

					return false;
				}

				// statut
				$arr_commande_statut                  = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'START']);
				$insert_field['idcommande_statut']    = (int)$arr_commande_statut['idcommande_statut'];
				$insert_field['codeCommande_statut']  = $arr_commande_statut['codeCommande_statut'];
				$insert_field['nomCommande_statut']   = $arr_commande_statut['nomCommande_statut'];
				$insert_field['ordreCommande_statut'] = (int)$arr_commande_statut['ordreCommande_statut'];

				# creation ou recuperation shift_run
				$arr_run = ['idshop'                         => $idshop,
				            'idshop_jours_shift'             => $insert_field['idshop_jours_shift'],
				            'dateDebutShop_jours_shift_run'  => date('Y-m-d'),
				            'nomShop_jours_shift_run'        => date('d-m-Y'),
				            'heureDebutShop_jours_shift_run' => $arr_sh_shift[0]['heureDebutShop_jours_shift']];
				//
				$idshift_run                            = $APP_SHIFT_RUN->create_update($arr_run);
				$insert_field['idshop_jours_shift_run'] = $idshift_run;

				// fin de préparation
				// $attentePreparationCommande = $nb_commande_wait + 1;
				// $time_prep                  = ($time_preparation_commande * 60) * $attentePreparationCommande;

				$insert_field = array_merge($insert_field);
				// suivant le nombre de livreur disponibles, voire en attente ( RESERV )
				$ARR_SHOP      = $APP_SHOP->findOne(['idshop' => $idshop]);
				$cou           = $APP_COMMANDE->find(['idshop'               => (int)$arr_cart['idshop'],
				                                      'dateCreationCommande' => date('Y-m-d')])->count();
				$count_secteur = $APP_COMMANDE->find(['idsecteur'            => (int)$ARR_SHOP['idsecteur'],
				                                      'dateCreationCommande' => date('Y-m-d')])->count();
				++$cou;
				++$count_secteur;
				$num_padded                        = sprintf("%02d", $cou);
				$referenceCommande                 = sprintf("%03d", $count_secteur);
				$codeCommande_facture              = date('my') . '-' . $referenceCommande;
				$insert_field['codeCommande']      = date('dmy') . '-' . $num_padded . '-' . substr($ARR_SHOP['codeShop'], 0, 3);
				$insert_field['referenceCommande'] = "#$referenceCommande";

				$insert_field['tempsAnnonceCommande'] = CommandeQueue::shop_next_slot($idshop);
				/**
				 * INSERTION COMMANDE
				 */
				$idcommande        = $APP_COMMANDE->insert($insert_field);
				$insert_field_more = $BIN->getCommande_queue_periods($idcommande);
				$APP_COMMANDE->update(['idcommande' => $idcommande], $insert_field_more);
				ClassRangCommande::updateRangShopCommandes($idshop);
				$CommandeSlot = new CommandeSlot($idsecteur);
				$CommandeSlot->distribute($idsecteur);

				$time_prep = $insert_field_more['timeFinPreparationCommande'] - time();

				// $APP_COMMANDE->consolidate_scheme($idcommande);
				$arr_commande = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);

				# lignes de commandes
				foreach ($arr_cart['cart_lines'] as $cart_line) {
					$ins_ligne['idcommande']                = $idcommande;
					$ins_ligne['idshop']                    = (int)$arr_cart['idshop'];
					$ins_ligne['idclient']                  = (int)$test_cli['idclient'];
					$ins_ligne['idproduit']                 = (int)$cart_line['id']['idproduit'];
					$ins_ligne['nomProduit']                = $cart_line['id']['nomProduit'];
					$ins_ligne['prixCommande_ligne']        = $cart_line['id']['prix_siteProduit'];
					$ins_ligne['quantiteCommande_ligne']    = $cart_line['qte'];
					$ins_ligne['descriptionCommande_ligne'] = $cart_line['description'];
					$ins_ligne['totalCommande_ligne']       = $cart_line['qte'] * $cart_line['id']['prix_siteProduit'];
					$idligne_commande                       = $APP_COMMANDE_LIGNE->insert($ins_ligne);
					$APP_COMMANDE_LIGNE->consolidate_scheme($idligne_commande);
				}
				$_SESSION['commande']  = $idcommande;
				$this->currentCommande = $idcommande;
				// register commande charges repartitions
				$APP_CMD_FACT = new App('commande_facture');
				$charges      = AppCharges::get_commandeParts($insert_field['prixCommande'], $insert_field['prixServiceCommande']);

				$vars_charge = array_merge($charges, ['idshop'                => $idshop,
				                                      'idcommande'            => $idcommande,
				                                      'codeCommande_facture'  => $codeCommande_facture,
				                                      'dateCommande_facture'  => date('Y-m-d'),
				                                      'heureCommande_facture' => date('h-i-00'),]);

				$APP_CMD_FACT->insert($vars_charge);
				$Dispatch = new Dispatch();
				$Dispatch->propose_commande($idcommande);
				$Dispatch->propose_commande_shop($idcommande);

				//$Demo = new Demo();
				//$Demo->animate_step(['idcommande'=>$idcommande]);

				$room_sect = 'secteur_' . $idsecteur;
				$room_shop = 'shop_' . $idshop;
				//
				$Notify = new Notify();
				$Notify->notify_commande_change($idcommande);

				SendCmd::play_sound($room_sect);
				SendCmd::play_sound($room_shop);

				if (ENVIRONEMENT != 'PREPROD_LAN') {
					$Cart->empty_cart("all");
					unset($_SESSION['commande_data']);
				}
				// demo_mode
				if ($arr_vars['demo_mode']) {
					// AppSocket::send_cmd('act_notify', ['msg' => 'Step livreur dans   '.$time_prep, 'options' => ['sticky' => 1]]);

					AppSocket::run('act_run', ['route'  => 'demo/commande_step/idcommande:' . $idcommande,
					                           'method' => 'POST',
					                           'vars'   => ['mode' => 'set_ready_shop'] + $arr_commande,
					                           'delay'  => $time_prep * 1000]);// $time_prep*100

				}
				if (ENVIRONEMENT == 'PREPROD_LAN') {
					// AppSocket::send_cmd('act_notify', ['msg' => 'Nouvelle commande dans '.$time_prep, 'options' => ['sticky' => 1]]);

				}
				// $_SESSION['client'] = $insert_field['private_key'];
				// $_SESSION['client_identity'] = $insert_field['prenomClient'] . ' ' . $insert_field['nomClient'];
			}

			return json_encode($json_message, JSON_FORCE_OBJECT);
		}

		function commande_preset_info($arr_vars = []) {
			$failed    = 0;
			$error_msg = [];
			$arr_vars  = array_filter($arr_vars);
			$param     = (sizeof($arr_vars) == 0) ? $_POST : $arr_vars;
			//
			$allowed_fields  = ['nom',
			                    'prenom',
			                    'telephone',
			                    'adresse',
			                    'adresse2',
			                    'codePostal',
			                    'ville'];
			$required_fields = ['nom',
			                    'prenom',
			                    'telephone',
			                    'email',
			                    'codePostal',
			                    'ville',
			                    'antieme'];
			// verify param[email]
			// $param['emailCommande'] = $param['emailClient'];
			foreach ($param as $key => $post) {
				$post            = filter_var(trim($post), FILTER_SANITIZE_STRING);
				$data_field_name = str_replace('Commande', '', $key);
				if (strpos($key, 'telephone') !== false) {
					$post_tmp = filter_var($post, FILTER_SANITIZE_NUMBER_INT);
					if ($post_tmp != $post) {
						$failed      = 1;
						$error_msg[] = 'Votre numéro de téléphone semble erroné';
					} else {
						$post = $post_tmp;
					}
				}
				if (strpos($key, 'email') !== false) {
					$post_tmp = filter_var($post, FILTER_VALIDATE_EMAIL);
					if ($post_tmp != $post) {
						$failed      = 1;
						$error_msg[] = 'Votre email semble erroné';
					} else {
						$post = $post_tmp;
					}
				}

				if (empty($post) && in_array($data_field_name, $required_fields)) {
					$error_fields[$key] = $post;
					$failed             = 1;
					$error_msg[]        = "Le $data_field_name doit être saisie";
				} else {
					$param[$key] = $post;
				}
			}
			// email ? client
			//$param['emailCommande'] = $param['emailClient'];
			$APP_CLIENT = new App('client');
			$ARR_CLIENT = $APP_CLIENT->findOne(['emailClient' => $param['emailClient']]);
			/*if (!empty($ARR_CLIENT['emailClient'])) {
				$failed             = 1;
				$error_msg[]        = "Client déja enregistré";
			}*/

			if ($failed == 1) {
				$json_message = ['err'     => 1,
				                 'msg_arr' => $error_msg];
				NotifySite::notify_modal('Vérification de votre saisie', 'error', ['mdl_vars' => ['msg_array' => $error_msg]], session_id());
				$this->send_data($json_message);

				return false;
			}
			if (empty($_SESSION['client'])) {
				AppSocket::send_cmd('act_notify_reveal', array_merge(['msg'      => '$msg',
				                                                      'mdl'      => '/fragment/login_multi_register',
				                                                      'type'     => 'info',
				                                                      'mdl_vars' => ['type' => 'client'] + $param]), session_id());
				$_SESSION['commande_data'] = json_encode($param, JSON_FORCE_OBJECT);
				// AppSocket::send_cmd('act_notify',['msg' => '',  'options'=>['sticky'=>1,'vars' => ['type'=>'client'],'type' => 'info','mdl' => '/fragment/login_multi_register']]);
				$json_message = ['err'     => 0,
				                 'msg_arr' => []];
				$this->send_data($json_message);

			} else {
				$json_message              = ['err'     => 0,
				                              'msg_arr' => []];
				$_SESSION['commande_data'] = json_encode($param, JSON_FORCE_OBJECT);
				if (!empty($_SESSION['client'])) {
					$json_message['err'] = 2;
				}
				NotifySite::notify_modal('Saisie correcte', 'success', [], session_id());
				$this->send_data($json_message);
			}
		}

		function verify_vicinity() {

			$json_message         = ['err' => 1,
			                         'msg' => 'bad'];
			$arr_adresse          = $_POST['arr_adresse'];
			$APP_SHOP             = new App('shop');
			$APP_SECTEUR          = new App('secteur');
			$rs_sh                = $APP_SHOP->find(['codePostalShop' => (string)$arr_adresse['postal_code']], ['_id' => 0]);
			$arr_sh               = iterator_to_array($rs_sh);
			$covered              = $rs_sh->count();
			$find_vars            = ['gps_indexSecteur' => ['$geoIntersects' => ['$geometry' => ['type'        => 'Point',
			                                                                                     'coordinates' => [(float)$arr_adresse['lng'],
			                                                                                                       (float)$arr_adresse['lat']]]]]];
			$rs_sec               = $APP_SECTEUR->find($find_vars, ['_id' => 0]);
			$find_vars['count']   = sizeof($rs_sec);
			$arr_sect             = $rs_sec->getNext();
			$find_vars['secteur'] = $arr_sect;

			if (!preg_match("/^([0-9]+)/", $arr_adresse['name'])) {
				$json_message = ['err' => 1,
				                 'msg' => 'Cette adresse semble non valide'];
				// NotifySite::notify_modal('Cette adresse semble non valide', ' ... ');
				$this->send_data($json_message);

				return false;
			}
			if (!empty($arr_sect['idsecteur'])) {
				$_SESSION['vicinity'] = $arr_adresse['vicinity'];
				$json_message         = ['err'     => 0,
				                         'msg'     => 'Adresse validée secteur ' . $arr_sect['nomSecteur'],
				                         'secteur' => $arr_sect];
				$this->send_data($json_message);

				return false;
			} else {
				$json_message = ['err' => 1,
				                 'msg' => 'Votre quartier de livraison nest pas encore couvert par TAC-TAC-CITY'];
				$this->send_data($json_message);

				return false;
			}

			$this->send_data($json_message);
		}

		function send_data($json_message) {
			echo json_encode($json_message, JSON_FORCE_OBJECT);
		}

		function update($vars = []) {
			$table       = $vars[0];
			$table_value = (int)$vars[1];
			$post_vars   = function_prod::cleanPostMongo($_POST['vars'], true);
			if (!empty($table) && !empty($table_value)) {

				$APP_TMP = new App($table);
				$APP_TMP->update(["id$table" => $table_value], $post_vars);
			}
		}

		function propose_commande_secteur_pool($vars = []) {
			if (empty($vars['idsecteur'])) return false;
			$Dispatch = new Dispatch();
			$Dispatch->propose_commande_secteur_pool($vars['idsecteur']);

		}

		private function propose_commande_coursier($vars = []) {

			if (empty($vars['idlivreur'])) return false;
			$Dispatch = new Dispatch();
			$Dispatch->propose_commande_coursier($vars['idlivreur']);

		}

		function dump() {
			Helper::dump(func_get_args());
			Helper::dump($_POST);
		}
	}
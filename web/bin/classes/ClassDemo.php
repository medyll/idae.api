<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 24/09/2017
	 * Time: 01:18
	 */
	class Demo extends App {

		function __construct() {
			parent::__construct();
		}

		function launch_demo() {
			$this->create_affectation();
			$this->create_client();
			$this->create_client();
			$this->create_client();
			$idclient = $this->get_client();
			$this->create_cart_client(['idclient' => (int)$idclient]);
		}

		function create_client() {
			// mode demo =>
			$Action = new Action();

			$arr_prenom = ['Lucas',
			               'Enzo',
			               'Nathan',
			               'Mathis',
			               'Louis',
			               'Raphaël',
			               'Gabriel',
			               'Yanis',
			               'Hugo',
			               'Emma',
			               'Léa',
			               'Clara',
			               'Chloé',
			               'Inès',
			               'Joao',
			               'Jade',
			               'Manon',
			               'Sarah',
			               'Lola',
			               'Camille'];
			$arr_nom    = ['MARTIN',
			               'BERNARD',
			               'ROUX',
			               'THOMAS',
			               'PETIT',
			               'DURAND',
			               'MICHEL',
			               'ROBERT',
			               'RICHARD',
			               'SIMON',
			               'MOREAU',
			               'DUBOIS',
			               'BLANC',
			               'LAURENT',
			               'GIRARD',
			               'BERTRAND',
			               'GARNIER',
			               'DAVID',
			               'MOREL',
			               'BRANC',
			               'GUERIN',
			               'FOURNIER',
			               'ROY',
			               'ROUSSEAU',
			               'ANDRE',
			               'GAUTIER',
			               'BONNET',
			               'LAMBERT'];
			$arr_rue    = ['quai solidor',
			               'rue Jeanne Jugan',
			               'bvld republique',
			               'rue du chapitre',
			               'rue de la montre',
			               'avenue Monjtjoie',
			               'rue de la mare',
			               'route Riancourt',
			               'bvld tregor',
			               'bvld de Lambety',
			               'rue miriel',
			               'rue de la marne',
			               'rue petite anguille',
			               'avenue de la nation'];

			$prenomClient      = array_random($arr_prenom)[0];
			$nomClient         = array_random($arr_nom)[0];
			$nomClient_compose = array_random($arr_nom)[0];

			$is_compose = rand(0, 4);

			$vars['prenomClient'] = $prenomClient = ucfirst($prenomClient);
			$vars['nomClient']    = ucfirst(strtolower($nomClient));
			$vars['nomClient']    = $nomClient = ($is_compose == 4) ? $vars['nomClient'] : $vars['nomClient'] . '-' . strtolower($nomClient_compose);
			$vars['emailClient']  = $emailClient = strtolower($nomClient) . '-' . strtolower($prenomClient) . '@yohaa.crom';

			$vars['passwordClient']       = ucfirst($nomClient);
			$vars['passwordClient_verif'] = ucfirst($nomClient);

			$vars['telephoneClient'] = "8456258475";
			$vars['mobileClient']    = "0685421352";

			$vars['villeClient']      = 'St Malo';
			$vars['adresseClient']    = rand(1, 32) . ' ' . array_random($arr_rue)[0];
			$vars['adresse2Client']   = rand(1, 7) . ' eme étage';
			$vars['codePostalClient'] = '35400';

			$idclient = $Action->register($vars);

			return $idclient;

		}

		function get_client() {
			$APP_CLIENT  = new App('client');
			$DIST_CLIENT = $APP_CLIENT->distinct_all('idclient', ['emailClient' => new MongoRegex('/.yohaa.crom/')]);
			if (empty($DIST_CLIENT)) {
				$this->create_client();

				return $this->get_client();
			}
			$arrand_idclient = array_random($DIST_CLIENT, 1);

			return $arrand_idclient[0];
		}

		/**
		 * @param array $arr_vars
		 * cré entre 2 et 12 panier client, puis enchaine sur la reservation livreur
		 */
		function create_cart_client($arr_vars = []) {
			//
			$BIN                  = new Bin();
			$APP_CLIENT           = new App('client');
			$APP_SHOP             = new App('shop');
			$APP_SHOP_JOURS       = new App('shop_jours');
			$APP_SHOP_JOURS_SHIFT = new App('shop_jours_shift');
			$APP_PRODUIT          = new App('produit');

			// selectionner quelques clients au hasard, ou pas
			if (empty($arr_vars['idclient'])) {
				$DIST_CLIENT     = $APP_CLIENT->distinct_all('idclient', ['emailClient' => new MongoRegex('/.yohaa.crom/')]);
				$arrand_idclient = array_random($DIST_CLIENT, rand(2, 6));

			} else {
				$arrand_idclient[] = (int)$arr_vars['idclient'];
			}

			//
			# index du jour
			$index_jour = (int)date('w');
			//
			$arr_sh_j       = $APP_SHOP_JOURS->distinct_all('idshop', ['actifShop_jours' => 1, 'ordreJours' => $index_jour]);
			$arr_sh_j_shift = $APP_SHOP_JOURS_SHIFT->distinct_all('idshop', ['idshop' => ['$in' => $arr_sh_j]]); // $APP_SH_J_SHIFT
			$arr_sh         = $APP_SHOP->distinct_all('idshop', ['idshop' => ['$in' => $arr_sh_j_shift], 'actifShop' => 1]);

			if (!empty($arr_vars['idshop'])) {
				$arr_sh = [(int)$arr_vars['idshop']];
			}
			// $arr_sh_j_shift = [ 1 ];
			// trouver shop actif, avec produits actifs
			$DIST_SHOP = $APP_PRODUIT->distinct_all('idshop', ['actifProduit' => 1, 'idshop' => ['$in' => $arr_sh]]);

			//$DIST_SHOP = array_filter($DIST_SHOP);
			if (sizeof($DIST_SHOP) == 0) {
				return false;
			}
			$arrand_idshop = array_random($DIST_SHOP, rand(2, 12));

			// chaque client create_cart dans random shop
			foreach ($arrand_idclient as $key => $idclient) {
				$Cart = new Cart("client_demo_$idclient");

				$idclient   = (int)$idclient;
				$idshop     = array_random($arrand_idshop)[0];
				$arr_shop   = $APP_SHOP->findOne(['idshop' => $idshop]);
				$arr_client = $APP_CLIENT->findOne(['idclient' => $idclient]);
				$DIST_PROD  = array_random($APP_PRODUIT->distinct_all('idproduit', ['actifProduit' => 1, 'idshop' => $idshop]), rand(1, 4));
				$adr        = $arr_client['adresseClient'] . ' ' . $arr_client['codePostalClient'] . ' ' . $arr_client['villeClient'];
				// mise à jour adresse client cart
				$Cart->set_shop($idshop);
				$Cart->set_secteur($arr_shop['idsecteur']);
				$Cart->set_adresse(['formatted_address' => $adr,
				                    'locality'          => $arr_client['villeClient'],
				                    'postal_code'       => $arr_client['codePostalClient'],
				                    'name'              => $arr_client['adresseClient']]);

				foreach ($DIST_PROD as $key_prod => $idproduit) {
					$Cart->add_item($idproduit);
				}
				if (!empty($idclient)) {
					# communication
					$delay = delay_minute_random(60, 240);
					/*AppSocket::send_cmd('act_notify', ['options' => ['info' => 'panier ok, test commande dans ' . ceil($delay / 60000) . ' min'],
					                                   'msg' => "demo, panier client " . $arr_client['nomClient'].' shop '.$arr_shop['nomShop']]);*/

					$this->create_commande_client(['idclient' => $idclient]);

					# lancer commande pour ce client
					/*AppSocket::run('act_run', ['route'  => 'demo/create_commande_client/idclient:' . $idclient . '/demo_mode:true',
					                           'method' => 'POST',
					                           'delay'  => $delay * 1000]);*/
				}
			}

		}

		function create_commande_shop($arr_vars = []) {
			// find client
			$idclient = $this->get_client();
			$idshop   = (int)$arr_vars['idshop'];
			$this->create_cart_client(['idshop' => $idshop, 'idclient' => $idclient]);
		}

		function create_commande_client($arr_vars = []) {
			//
			$APP_CLIENT           = new App('client');
			$APP_SHOP             = new App('shop');
			$APP_SHOP_JOURS       = new App('shop_jours');
			$APP_SHOP_JOURS_SHIFT = new App('shop_jours_shift');
			$APP_PRODUIT          = new App('produit');

			// selectionner quelques clients au hasard, ou pas
			if (empty($arr_vars['idclient'])) {
				$DIST_CLIENT     = $APP_CLIENT->distinct_all('idclient', ['emailClient' => new MongoRegex('/.yohaa.crom/')]);
				$arrand_idclient = array_random($DIST_CLIENT, rand(2, 12));

			} else {
				$arrand_idclient[] = (int)$arr_vars['idclient'];
			}

			$fields = ['nom', 'prenom', 'email', 'telephone', 'adresse', 'adresse2', 'codePostal', 'ville'];
			foreach ($arrand_idclient as $key_rand => $idclient) {
				$ARR_CLIENT   = $APP_CLIENT->findOne(['idclient' => $idclient]);
				$nomClient    = $ARR_CLIENT['nomClient'];
				$Cart         = new Cart('client_demo_' . $idclient);
				$arr_cart     = $Cart->get_cart();
				$arr_commande = [];
				$arr_client   = $APP_CLIENT->findOne(['idclient' => (int)$idclient]);
				foreach ($fields as $key => $field) {
					$arr_commande[$field . 'Commande'] = $arr_client[$field . 'Client'];
				}

				if (sizeof($arr_cart['cart_adresse']) != 0 || sizeof($arr_cart['cart_lines']) != 0) {
					$arr_commande['adresseCommande']    = $arr_cart['cart_adresse']['name'];
					$arr_commande['codePostalCommande'] = $arr_cart['cart_adresse']['postal_code'];
					$arr_commande['villeCommande']      = $arr_cart['cart_adresse']['locality'];
				}

				// création commande si shopn open
				$arr_commande['demo_mode'] = 1;
				$arr_commande['idclient']  = $idclient;
				$arr_commande['idshop']    = $arr_cart['idshop'];
				$Action                    = new Action();

				$param    = $Action->commande_filter_vars($arr_commande);
				$pre_test = $Action->commande_test_info($param);

				if (!empty($pre_test['err'])) {
					NotifySite::notify_modal('Erreur', 'alert', ['mdl_vars' => ['msg_array' => $pre_test['msg']]], session_id());

					return false;
				}

				//$this->notify(['msg' => 'tentative création commande : ' . $nomClient]);
				$test = $Action->commande_set_info($arr_commande);
				// $this->notify(['msg' => 'resultat tentative création commande : ' . $nomClient . ' => ' . $test]);
				//// 5 min avant reserv livreur

			}

		}

		function create_secteur_shift() {
			// shop jour
			$APP_SECTEUR             = new App('secteur');
			$APP_J                   = new App('jours');
			$APP_SH_J                = new App('shop_jours');
			$APP_SECTEUR_JOURS_SHIFT = new App('secteur_jours_shift');

			$RS_SECTEUR = $APP_SECTEUR->find();
			//
			//
			while ($ARR_SECTEUR = $RS_SECTEUR->getNext()) {
				$idsecteur = (int)$ARR_SECTEUR['idsecteur'];

				$i = 0;
				while ($i <= 6) {
					$ARR_JOURS = $APP_J->findOne(['ordreJours' => $i]);
					$idjours   = (int)$ARR_JOURS['idjours'];

					$codeJours_AM = 'AM-' . $ARR_JOURS['nomJours'] . ' ' . $ARR_SECTEUR['codeSecteur'];
					$codeJours_PM = 'PM-' . $ARR_JOURS['nomJours'] . ' ' . $ARR_SECTEUR['codeSecteur'];

					$heureDebut_AM = "00:01:00";
					$heureFin_AM   = "15:00:00";

					$heureDebut_PM = "15:01:00";
					$heureFin_PM   = "23:59:00";

					$TEST_JOURS_AM = $APP_SECTEUR_JOURS_SHIFT->find(['idsecteur' => $idsecteur, 'idjours' => $idjours, 'code_auto' => 'AM']);
					$TEST_JOURS_PM = $APP_SECTEUR_JOURS_SHIFT->find(['idsecteur' => $idsecteur, 'idjours' => $idjours, 'code_auto' => 'PM']);

					if ($TEST_JOURS_AM->count() == 0) {
						$arr_AM = ['code_auto' => 'AM', 'heureDebutSecteur_jours_shift' => $heureDebut_AM, 'heureFinSecteur_jours_shift' => $heureFin_AM];
						$APP_SECTEUR_JOURS_SHIFT->insert($arr_AM + ['idsecteur' => $idsecteur, 'idjours' => $idjours, 'codeSecteur_jours_shift' => $codeJours_AM]);
					}
					if ($TEST_JOURS_PM->count() == 0) {
						$arr_PM = ['code_auto' => 'PM', 'heureDebutSecteur_jours_shift' => $heureDebut_PM, 'heureFinSecteur_jours_shift' => $heureFin_PM];
						$APP_SECTEUR_JOURS_SHIFT->insert($arr_PM + ['idsecteur' => $idsecteur, 'idjours' => $idjours, 'codeSecteur_jours_shift' => $codeJours_PM]);
					}
					$i++;
				}

			}
		}

		static public function create_shop_shift($table_value) {
			// shop jour
			$APP_SHOP       = new App('shop');
			$APP_J          = new App('jours');
			$APP_SH_J       = new App('shop_jours');
			$APP_SH_J_SHIFT = new App('shop_jours_shift');

			$ARR_SHOP = $APP_SHOP->findOne(['idshop' => $table_value]);
			$test_j   = $APP_SH_J->find(['idshop' => $table_value]);
			$test_j_s = $APP_SH_J_SHIFT->find(['idshop' => $table_value]);
			$nomShop  = $ARR_SHOP['nomShop'];
			//
			$cpount = $test_j_s->count();
			if ($test_j->count() < 7 || $test_j_s->count() == 0) {
				$rs_j = $APP_J->find();
				while ($ARR_JOURS = $rs_j->getNext()) {
					$idjours = (int)$ARR_JOURS['idjours'];
					if ($APP_SH_J->find(['idjours' => $idjours, 'idshop' => $table_value])->count() == 0) {
						$idshop_jours = $APP_SH_J->insert(['idjours'         => $idjours,
						                                   'idshop'          => $table_value,
						                                   'nomShop_jours'   => $ARR_JOURS['nomJours'] . ' ' . $nomShop,
						                                   'ordreShop_jours' => (int)$ARR_JOURS['ordreJours'],
						                                   'actifShop_jours' => 1]);
					} else {
						$test         = $APP_SH_J->findOne(['idjours' => $idjours, 'idshop' => $table_value]);
						$idshop_jours = (int)$test['idshop_jours'];
					}
					$APP_SH_J->consolidate_scheme($idshop_jours);
					// vardump_async([$idshop_jours,'Création shift shop  auto'], true);
					$test_j_s = $APP_SH_J_SHIFT->find(['idshop' => $table_value, 'idshop_jours' => $idshop_jours]);
					if ($test_j_s->count() < 1) {
						$nomShop_jours_shift = $ARR_JOURS['nomJours'];
						$h_d                 = "00:01:00";
						$h_f                 = "14:00:00";
						$idshop_jours_shift  = $APP_SH_J_SHIFT->create_update(['nomShop_jours_shift' => $nomShop_jours_shift . ' AM ' . $nomShop,
						                                                       'idshop_jours'        => $idshop_jours,
						                                                       'idshop'              => $table_value,], ['heureDebutShop_jours_shift' => $h_d,
						                                                                                                 'heureFinShop_jours_shift'   => $h_f,
						                                                                                                 'actifShop_jours_shift'      => 1,
						                                                                                                 'ordreShop_jours_shift'      => (int)$ARR_JOURS['ordreJours']]);
						$APP_SH_J_SHIFT->consolidate_scheme($idshop_jours_shift);
						$h_d                = "14:01:01";
						$h_f                = "23:59:59";
						$idshop_jours_shift = $APP_SH_J_SHIFT->create_update(['nomShop_jours_shift' => $nomShop_jours_shift . ' PM ' . $nomShop,
						                                                      'idshop_jours'        => $idshop_jours,
						                                                      'idshop'              => $table_value,], ['heureDebutShop_jours_shift' => $h_d,
						                                                                                                'heureFinShop_jours_shift'   => $h_f,
						                                                                                                'actifShop_jours_shift'      => 1,
						                                                                                                'ordreShop_jours_shift'      => (int)$ARR_JOURS['ordreJours']]);
						$APP_SH_J_SHIFT->consolidate_scheme($idshop_jours_shift);
					}
				}
			}
		}

		/**
		 * crreate affectation for 2 weeks
		 */
		function create_affectation_period() {
			$datestart = 'now';
			// End date
			$end_date = 'sunday next week + 1 week';

			while (strtotime($datestart) <= strtotime($end_date)) {
				$datestart = date("Y-m-d", strtotime("+1 day", strtotime($datestart)));
				$date_time = strtotime($datestart);
				$this->create_affectation($date_time);

			}
		}

		function create_affectation($date_time = null) {
			// mode demo => livreur affectation au même secteur que le shop
			$APP_JOURS               = new App('jours');
			$APP_LIVREUR             = new App('livreur');
			$APP_LIV_AFFECT          = new App('livreur_affectation');
			$APP_SHOP                = new App('shop');
			$APP_SECTEUR_JOURS_SHIFT = new App('secteur_jours_shift');

			$time                        = ($date_time) ? $date_time : time();
			$date_affectation            = date('Y-m-d', $time);
			$date_affectation_fr         = date('d-m-Y', $time);
			$date_affectation_code       = date('dmy', $time);
			$date_affectation_jour       = date('w', $time);
			$date_affectation_ordre_jour = date('l', $time);

			// les secteurs des livreurs actifs
			$arr_idsecteur = $APP_LIVREUR->distinct_all('idsecteur', []);
			$tabjour       = [1 => "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];

			foreach ($arr_idsecteur as $idsecteur) {
				$idsecteur = (int)$idsecteur;

				$index_jour = ((int)$date_affectation_jour - 1 < 0) ? 6 : (int)$date_affectation_jour - 1;

				$ARR_JOURS = $APP_JOURS->findOne(['ordreJours' => $index_jour]);
				$idjours   = $ARR_JOURS['idjours'];

				$TEST_JOURS_AM = $APP_SECTEUR_JOURS_SHIFT->findOne(['idsecteur' => $idsecteur, 'idjours' => $idjours, 'code_auto' => 'AM']);
				$TEST_JOURS_PM = $APP_SECTEUR_JOURS_SHIFT->findOne(['idsecteur' => $idsecteur, 'idjours' => $idjours, 'code_auto' => 'PM']);

				$heureDebut_AM = $TEST_JOURS_AM['heureDebutSecteur_jours_shift'];
				$heureFin_AM   = $TEST_JOURS_AM['heureFinSecteur_jours_shift'];

				$heureDebut_PM = $TEST_JOURS_PM['heureDebutSecteur_jours_shift'];
				$heureFin_PM   = $TEST_JOURS_PM['heureFinSecteur_jours_shift'];

				//if ($rs_test_affect->count() == 0) {

				$arr_idliv = $APP_LIVREUR->distinct_all('idlivreur', ['actifLivreur' => 1, 'idsecteur' => $idsecteur]);
				foreach ($arr_idliv as $key => $idlivreur) {
					$idlivreur = (int)$idlivreur;
					// verif déja crée
					$VERIF_AM = ['code_auto' => 'AM', 'idlivreur' => $idlivreur, 'idsecteur' => $idsecteur, 'dateDebutLivreur_affectation' => $date_affectation];
					$VERIF_PM = ['code_auto' => 'PM', 'idlivreur' => $idlivreur, 'idsecteur' => $idsecteur, 'dateDebutLivreur_affectation' => $date_affectation];

					$rs_test_AM = $APP_LIV_AFFECT->find($VERIF_AM);
					$rs_test_PM = $APP_LIV_AFFECT->find($VERIF_PM);
					$ARR_LIV    = $APP_LIVREUR->findOne(['idlivreur' => $idlivreur]);

					if ($rs_test_AM->count() == 0) {
						$arr_insert = ['dateDebutLivreur_affectation'  => $date_affectation,
						               'dateFinLivreur_affectation'    => $date_affectation,
						               'actifLivreur_affectation'      => 1,
						               'nomLivreur_affectation'        => $ARR_LIV['nomLivreur'] . ' ' . $tabjour[$date_affectation_jour] . ' AM ' . $date_affectation_fr,
						               'heureDebutLivreur_affectation' => $heureDebut_AM,
						               'heureFinLivreur_affectation'   => $heureFin_AM,
						               'codeLivreur_affectation'       => 'AM-' . $date_affectation_code,
						               'code_auto'                     => 'AM',
						               'idlivreur'                     => $idlivreur,
						               'idsecteur'                     => $idsecteur];
						$APP_LIV_AFFECT->insert($arr_insert);

					}
					if ($rs_test_PM->count() == 0) {
						$arr_insert = ['dateDebutLivreur_affectation'  => $date_affectation,
						               'dateFinLivreur_affectation'    => $date_affectation,
						               'actifLivreur_affectation'      => 1,
						               'nomLivreur_affectation'        => $ARR_LIV['nomLivreur'] . ' ' . $tabjour[$date_affectation_jour] . ' PM ' . $date_affectation_fr,
						               'heureDebutLivreur_affectation' => $heureDebut_PM,
						               'heureFinLivreur_affectation'   => $heureFin_PM,
						               'codeLivreur_affectation'       => 'PM-' . $date_affectation_code,
						               'code_auto'                     => 'PM',
						               'idlivreur'                     => $idlivreur,
						               'idsecteur'                     => $idsecteur];
						$APP_LIV_AFFECT->insert($arr_insert);

						// AppSocket::send_cmd('act_notify' , [ 'msg' => "demo, affectation livreur $idlivreur secteur $idsecteur " ]);
					}
				}
				//}
			}

		}

		function animate_step($array_vars = []) {
			return;
			$table               = 'commande';
			$idcommande          = (int)$array_vars['idcommande'];
			$APP_COMMANDE        = new App("commande");
			$APP_COMMANDE_STATUT = new App('commande_statut');

			$arr_commande = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);

			if (empty($arr_commande['idcommande'])) return false;
			if ($arr_commande['codeCommande_statut'] == 'END') return false;

			$array_vars = array_merge($array_vars, $_POST);

			$PROGRESS_RATIO            = 4;
			$duree_realisationCommande = DUREE_REALISATION_COMMANDE;
			$time_preparation_commande = TIME_PREPARATION_COMMANDE;
			$temps_livraison           = TEMPS_LIVRAISON_COMMANDE;

			$timeFinPreparationCommande = $arr_commande['timeFinPreparationCommande'];
			$timeLivraisonCommande      = $arr_commande['timeLivraisonCommande'];

			$Bin         = new Bin();
			$arr_minutes = $Bin->get_elapsed_minutes_arr_for_commande($idcommande);

			$start_time     = $arr_minutes['start_time'];
			$to_time        = $arr_minutes['to_time'];
			$max            = $arr_minutes['max'];
			$value_progress = $arr_minutes['value_progress'];

			$step_time_size = $max / $PROGRESS_RATIO;

			$turn               = (isset($array_vars['turn'])) ? $array_vars['turn'] : 0;
			$array_vars['turn'] = ++$turn;

			$progress_vars['progress_name']  = "animate_step_commande_$idcommande";
			$progress_vars['progress_value'] = $value_progress;
			$progress_vars['progress_max']   = $max;
			/*$progress_vars['progress_animate_to_value']    = $max;
			$progress_vars['progress_animate_to_secondes'] = $max;*/

			// $progress_vars['progress_text'] = $to_time;
			//	$progress_vars['progress_message'] = 'progress_message progress_message progress_message';

			$act_vars = ['route'  => 'demo/animate_step/idcommande:' . $idcommande,
			             'method' => 'POST',
			             'vars'   => $array_vars];
			if ($turn != 0) {
				$act_vars['delay_name'] = "delay_animate_step_$idcommande";
				$act_vars['delay']      = $step_time_size * 1000;
			}

			// $progress_vars['progress_text'] =  "$step_time_size s. <span>value ($elapsed_value/60) max ($max/60) </span>".$arr_commande['codeCommande_statut'];

			if ($value_progress >= $max) {
				//	$progress_vars['progress_text']    = $elapsed_value . ' / ' . $max.' soit '.($step_time_size*60);
				$progress_vars['progress_text'] .= "<i class='fa fa-exclamation-triangle fa-2x padding margin textrouge absolute' style='left:0'></i>";
			} else {
				$progress_vars['progress_text'] = '<span> </span>';
			}

			$room_commande = "commande_$idcommande";

			AppSocket::send_cmd('act_progress', $progress_vars);
			// AppSocket::run('act_run', $act_vars);

		}

		function commande_step($array_vars = []) {
			$table               = 'commande';
			$idcommande          = (int)$array_vars['idcommande'];
			$APP_COMMANDE        = new App("commande");
			$APP_COMMANDE_STATUT = new App('commande_statut');

			$arr_commande = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);

			if (empty($_POST['mode'])) return false;
			if (empty($arr_commande['idcommande'])) return false;

			$_msg = date('H:i') . ' ' . $arr_commande['codeCommande'] . ' ' . $arr_commande['nomLivreur'] . ' ' . $arr_commande['nomLivreur'];

			$idshop    = (int)$arr_commande['idshop'];
			$idlivreur = (int)$arr_commande['idlivreur'];
			$idsecteur = (int)$arr_commande['idsecteur'];

			switch ($_POST['mode']):
				case 'set_ready_shop':
					$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'PREFIN']);
					$IdaeAction          = new IdaeAction($table);
					$IdaeAction->app_update($idcommande, ['idcommande_statut'    => (int)$arr_commande_statut['idcommande_statut'],
					                                      'ordreCommande_statut' => $arr_commande_statut['ordreCommande_statut'],
					                                      'nomCommande_statut'   => $arr_commande_statut['nomCommande_statut'],
					                                      'codeCommande_statut'  => $arr_commande_statut['codeCommande_statut']]);
					break;
				case 'set_livreur_start':
					$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'PREFIN']);
					if ($arr_commande_statut['codeCommande_statut'] != 'PREFIN') {
						AppSocket::run('act_run', ['route'  => 'demo/commande_step/idcommande:' . $idcommande,
						                           'method' => 'POST',
						                           'vars'   => ['mode' => 'set_livreur_start'],
						                           'delay'  => 10000]);

						return false;
					}
					// test disponibilité shop PREFIN
					// $arr_commande_statut = $APP_COMMANDE_STATUT->distinct_all('idcommande_statut',['codeCommande_statut' => ['$in'=>[ 'RUN' , 'PREFIN' ]]]);

					$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'LIVENCOU']);
					$IdaeAction          = new IdaeAction($table);
					$IdaeAction->app_update($idcommande, ['idcommande_statut'    => (int)$arr_commande_statut['idcommande_statut'],
					                                      'ordreCommande_statut' => $arr_commande_statut['ordreCommande_statut'],
					                                      'nomCommande_statut'   => $arr_commande_statut['nomCommande_statut'],
					                                      'codeCommande_statut'  => $arr_commande_statut['codeCommande_statut']]);
					$arr_commande['idcommande'];
					$delay_secondes = (TEMPS_LIVRAISON_COMMANDE * 60); //$arr_commande['timeLivraisonCommande'] - time();
					$delay_secondes = ($delay_secondes < 0) ? 0 : $delay_secondes;

					AppSocket::run('act_run', ['route'  => 'demo/commande_step/idcommande:' . $arr_commande['idcommande'],
					                           'method' => 'POST',
					                           'vars'   => ['mode' => 'set_commande_end'],
					                           'delay'  => $delay_secondes * 1000]);//
					break;
				case 'set_commande_end':
					$arr_commande_statut  = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'END']);
					$IdaeAction           = new IdaeAction($table);
					$timeCreationCommande = strtotime($arr_commande['dateCommande'] . ' ' . $arr_commande['heureCommande']);
					$IdaeAction->app_update($idcommande, ['idcommande_statut'    => (int)$arr_commande_statut['idcommande_statut'],
					                                      'ordreCommande_statut' => $arr_commande_statut['ordreCommande_statut'],
					                                      'nomCommande_statut'   => $arr_commande_statut['nomCommande_statut'],
					                                      'heureFinCommande'     => date('H:i:00'),
					                                      'tempsTotalCommande'   => ceil((time() - $timeCreationCommande) / 60),
					                                      'codeCommande_statut'  => $arr_commande_statut['codeCommande_statut']]);
					break;
			endswitch;
			//
			$Notify = new Notify();
			$Notify->notify_count_statut($table, (int)$idcommande);
			if (!empty($idlivreur)) $Notify->notify_commande_livreur($idlivreur);
			if (!empty($idsecteur)) $Notify->notify_livreur_affect($idsecteur);
			if (!empty($idsecteur)) $Notify->notify_commande_secteur($idsecteur);
			if (!empty($idshop)) $Notify->notify_commande_shop($idshop);

			$this->log($_POST['mode'] . '  idcommande : ' . $idcommande);

			/*AppSocket::send_cmd('act_notify', ['options' => ['sticky' => 1, 'info' => $_msg.' '.$delay_secondes.' secondes '],
					                                   'msg'     => $_POST['mode'] . '  idcommande : ' . $idcommande]);*/
		}

		function commande_step_statut($array_vars = []) {

			$table               = 'commande';
			$idcommande          = (int)$array_vars['idcommande'];
			$APP_COMMANDE        = new App("commande");
			$APP_COMMANDE_STATUT = new App('commande_statut');

			$arr_commande = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);

			if (empty($arr_commande['idcommande'])) return false;
			/*AppSocket::send_cmd('act_notify' , [ 'options' => ['sticky'=>1, 'info' =>  date('H:i:s').' STATUT demo '.$arr_commande['nomCommande'] ] ,
			                                     'msg' => date('H:i:s'). " $idcommande   commande_step_statut, livraison auto" ]);*/
			// statut
			$arr_commande_statut            = $APP_COMMANDE_STATUT->findOne(['idcommande_statut' => (int)$arr_commande['idcommande_statut']]);
			$arr_commande_statut_livencours = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'LIVENCOU']);
			$rs_commande_statut_next        = $APP_COMMANDE_STATUT->find(['ordreCommande_statut' => ['$gt' => (int)$arr_commande_statut['ordreCommande_statut']]])->sort(['ordreCommande_statut' => 1]);

			if ($rs_commande_statut_next->count() != 0 && !empty($arr_commande['idcommande'])) {
				$arr_next = $rs_commande_statut_next->getNext();
				if (!empty($arr_next['idcommande_statut'])) {

					if ($arr_commande['codeCommande_statut'] == 'PREFIN' && empty($arr_commande['idlivreur'])) {
						// si pas de livreur et commande prete, on attend deux minutes //  flag urgent !!
						AppSocket::run('act_run', ['route'  => 'demo/commande_step_statut/idcommande:' . $idcommande,
						                           'method' => 'POST',
						                           'delay'  => 120000]);

						return;
					}
					if ($arr_next['codeCommande_statut'] == 'RESERV' && empty($arr_commande['idlivreur'])) {
						$arr_next = $rs_commande_statut_next->getNext();
					}

					$IdaeAction = new IdaeAction($table);
					$IdaeAction->app_update($idcommande, ['idcommande_statut' => (int)$arr_next['idcommande_statut'], 'codeCommande_statut' => $arr_next['codeCommande_statut']]);
					// $APP_COMMANDE->update([ 'idcommande' => $idcommande ] , [ 'idcommande_statut' => (int)$arr_next['idcommande_statut'] ]);
					//
					AppSocket::reloadModule('idae/fiche_next_statut/commande/' . $idcommande, $idcommande);

					if ($_POST['data-remove']) {
						AppSocket::reloadModule($_POST['data-remove'], $idcommande);
					}
					//
					$delay_theo = ceil($arr_commande['duree_realisationCommande'] / 4) * 60 * 1000;
					$delay      = 60000; // 1 min  de delay
					//
					if ($delay != 0) {
						// $this->notify(['options' => ['sticky'=>1,'info' => $arr_next['codeCommande_statut']], 'msg' => "demo<br> $delay / $delay_theo  " . $arr_commande['codeCommande']]);

						AppSocket::run('act_run', ['route'  => 'demo/commande_step_statut/idcommande:' . $idcommande,
						                           'method' => 'POST',
						                           'delay'  => $delay]);

						$Notify = new Notify();
						$Notify->notify_livreur_affect($arr_commande['idsecteur']);
						$Notify->notify_commande_shop($arr_commande['idshop']);
					}

				}
			} else {
				//// 3 min avant reserv livreur
			}

		}

		function notify($obj, $room = '') {

			AppSocket::send_cmd('act_notify', $obj, $room);

		}

		function log($obj, $room = '') {
			//	AppSocket::send_cmd('act_notify', ['options' => ['info' => $obj],   'msg'     => 'log'],$room);
		}

		function commande_pool_livreur_start($arr_vars = []) {
			$BIN                      = new Bin();
			$IdaeAction               = new IdaeAction('commande');
			$APP_COMMANDE             = new App('commande');
			$APP_COMMANDE_STATUT      = new App('commande_statut');
			$DB_SECTEUR               = new App('secteur');
			$RS_SECTEUR = $DB_SECTEUR->find();
			while ($ARR_SECTEUR = $RS_SECTEUR->getNext()) {
				$idsecteur         = $ARR_SECTEUR['idsecteur'];
				// $RS_COMMANDE_QUEUE = CommandeQueue::sectew($idsecteur);

			}

		}
		function commande_pool($arr_vars = []) {

			$BIN                      = new Bin();
			$IdaeAction               = new IdaeAction('commande');
			$APP_COMMANDE             = new App('commande');
			$APP_COMMANDE_STATUT      = new App('commande_statut');
			$DB_SECTEUR               = new App('secteur');

			$arr_commande_statut_reserv = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'RESERV']);
			$update_fields              = ['idcommande_statut' => (int)$arr_commande_statut_reserv['idcommande_statut'], 'codeCommande_statut' => $arr_commande_statut_reserv['codeCommande_statut']];

			$RS_SECTEUR = $DB_SECTEUR->find();
			while ($ARR_SECTEUR = $RS_SECTEUR->getNext()) {
				$idsecteur         = $ARR_SECTEUR['idsecteur'];
				$RS_COMMANDE_QUEUE = CommandeQueue::secteur_commande_queue_list($idsecteur);
				while ($ARR_COMMANDE_QUEUE = $RS_COMMANDE_QUEUE->getNext()) {
					$idcommande      = (int)$ARR_COMMANDE_QUEUE['idcommande'];
					$arr_commande    = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);
					$RS_LIVREUR_FREE = CommandeQueue::secteur_has_livreur_free_list($idsecteur);
					while ($ARR_LIVREUR = $RS_LIVREUR_FREE->getNext()) {
						$idlivreur = (int)$ARR_LIVREUR['idlivreur'];
						ob_start();
						$test = $BIN->test_delivery_reserv(['idcommande' => $idcommande, 'idlivreur' => $idlivreur]);
						ob_end_clean();
						if ($test !== false && $test['err'] == 0) {
							$IdaeAction->app_update($idcommande, $update_fields + ['idlivreur' => $idlivreur]);

							$delay_secondes = $arr_commande['timeFinPreparationCommande'] - time();
							$delay_secondes = ($delay_secondes < 0) ? 0 : $delay_secondes;

							AppSocket::run('act_run', ['route'  => 'demo/commande_step/idcommande:' . $idcommande,
							                           'method' => 'POST',
							                           'vars'   => ['mode' => 'set_livreur_start'],
							                           'delay'  => $delay_secondes * 1000]); // $delay_secondes * 1000

						}
					}
				}
			}

			return;

		}

		function dump($vars_received) {

			AppSocket::send_cmd('act_notify', ['msg' => "demo, dump " . json_encode($vars_received, JSON_PRETTY_PRINT)]);
		}

		function do_action($params = ['action', 'value']) {
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
	}
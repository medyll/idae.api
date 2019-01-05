<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 03/10/2017
	 * Time: 01:02
	 */
	class Notify extends App {

		function __construct($table = null) {
			parent::__construct($table);
		}

		function notify_commande_change($idcommande) {

			$commandeQueueConsole = new CommandeQueueConsole();
			$configs              = $commandeQueueConsole->get_times_config();

			$configs->get_times_secteur->update();
			$configs->get_times_secteur_livreur->update();

			$DB = new IdaeDataDB('shop');

			$session_data = IdaeSession::getInstance()->get_session();
			$rs           = $DB->find(['actifShop' => 1]);

			while ($arr = $rs->getNext()) {
				$configs = CommandeQueueConsole::consoleShop($arr['idshop']);
				$configs->console_shop->update();
				$configs->console_secteur->update();
				$configs->console_secteur_livreur->update();
				$configs = CommandeQueueConsole::consoleShopSite($arr['idshop']);
				$configs->console_shop->update();
				$configs->console_secteur_livreur->update();
				$configs = CommandeQueueConsole::consoleLivreur($arr['idlivreur']);
				$configs->console_secteur->update();
				$configs->console_secteur_livreur->update();

			}


			$APP_COMMANDE = new App('commande');
			$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);

			$this->notify_count_statut('commande', (int)$idcommande);
			$this->notify_commande_shop($ARR_COMMANDE['idshop']);

			$this->notify_commande_secteur($ARR_COMMANDE['idsecteur']);
			$this->notify_commande_free_secteur($ARR_COMMANDE['idsecteur']);
			$this->notify_livreur_affect($ARR_COMMANDE['idsecteur']);

			$this->notify_wait_time_secteur($ARR_COMMANDE['idsecteur']);

			if (!empty($ARR_COMMANDE['idlivreur'])) $this->notify_commande_livreur((int)$ARR_COMMANDE['idlivreur']);

		}

		function notify_livreur($idsecteur) {
			if (empty($idsecteur)) {
				return;
			}
			$BIN        = new Bin();
			$affect_val = sizeof($BIN->test_livreur_affect($idsecteur));
			$free_val   = sizeof($BIN->test_livreur_affect_free($idsecteur));

			$affect = "[data-count-livreur_affect=$idsecteur]";
			$free   = "[data-count-livreur_affect_free=$idsecteur]";

			AppSocket::send_cmd('act_insert_selector', [[$affect,
			                                             $affect_val],
			                                            [$free,
			                                             $free_val]]);
		}

		function notify_livreur_affect($idsecteur) {
			if (empty($idsecteur)) {
				return;
			}

			$BIN        = new Bin();
			$affect_val = sizeof($BIN->test_livreur_affect($idsecteur));
			$free_val   = sizeof($BIN->test_livreur_affect_free($idsecteur));

			$affect = "[data-count-livreur_affect=$idsecteur]";
			$free   = "[data-count-livreur_affect_free=$idsecteur]";

			AppSocket::send_cmd('act_insert_selector', [[$affect,
			                                             $affect_val],
			                                            [$free,
			                                             $free_val]]);
		}

		function notify_commande_forshop($idshop) {
			if (empty($idshop)) {
				return;
			}
			$BIN                        = new Bin();
			$commande_shop_val          = sizeof($BIN->test_commande_shop($idshop));
			$commande_shop_wait_val     = sizeof($BIN->test_commande_shop_wait($idshop));
			$commande_shop_livencou_val = sizeof($BIN->test_commande_shop_livencou($idshop));

			$commande_shop          = "[data-count-commande_shop=$idshop]";
			$commande_shop_wait     = "[data-count-commande_shop_wait=$idshop]";
			$commande_shop_livencou = "[data-count-commande_shop_livencou=$idshop]";

			$_table = 'commande_statut';
			$_id    = "id$_table";

			$index_jour         = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;
			$NOW                = date('H:i:s');
			$APP_SHOP           = new App('shop');
			$APP_SH_J           = new App('shop_jours');
			$APP_SH_J_SHIFT     = new App('shop_jours_shift');
			$APP_SHIFT_RUN      = new App('shop_jours_shift_run');
			$arr_sh             = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$arr_sh_j           = $APP_SH_J->findOne(['idshop'     => $idshop,
			                                          'ordreJours' => $index_jour]);
			$arr_sh_shift       = iterator_to_array($APP_SH_J_SHIFT->find(['idshop'                     => $idshop,
			                                                               'idshop_jours'               => (int)$arr_sh_j['idshop_jours'],
			                                                               'actifShop_jours_shift'      => 1,
			                                                               'heureDebutShop_jours_shift' => ['$lte' => $NOW],
			                                                               'heureFinShop_jours_shift'   => ['$gte' => $NOW]], ['_id' => 0])->sort(['heureDebutShop_jours_shift' => 1]));
			$idshop_jours_shift = (int)$arr_sh_shift[0]['idshop_jours_shift'];
			# creation ou recuperation shift_run
			$arr_run = ['idshop'                         => $idshop,
			            'idshop_jours_shift'             => $idshop_jours_shift,
			            'dateDebutShop_jours_shift_run'  => date('Y-m-d'),
			            'nomShop_jours_shift_run'        => date('d-m-Y') . ' ' . $arr_sh_shift[0]['heureDebutShop_jours_shift'],
			            'heureDebutShop_jours_shift_run' => $arr_sh_shift[0]['heureDebutShop_jours_shift']];

			$APP_COMMANDE = new App('commande');
			$APPTMP       = new App($_table);

			$RS_STATUT           = $APPTMP->find();
			$arr_commande_statut = [];

			while ($ARR_STATUT = $RS_STATUT->getNext()) {
				$id = (int)$ARR_STATUT[$_id];
				// only same shift run
				//$CT_NO_LIV = $APP_COMMANDE->find([ 'idlivreur' => null , 'dateCreationCommande' => date('Y-m-d') , 'idcommande_statut' => $id , 'idsecteur' => $idsecteur ]);
				$CT = $APP_COMMANDE->find(['idshop'             => $idshop,
				                           'idcommande_statut'  => $id,
				                           'idshop_jours_shift' => $idshop_jours_shift]);
				array_push($arr_commande_statut, ["[data-idshop][data-count=commande_statut][data-table_value=$id][data-table=commande]",
				                                  $CT->count()]);
				//echo  $CT_NO_LIV->count();
				/*if ( $CT_NO_LIV->count() != 0 ) {
					array_push($arr_commande_statut , [ "[data-idsecteur=$idsecteur][data-count=commande_statut][data-table_value=$id][data-table=commande]" , $CT_NO_LIV->count() ]);
				}*/
			}

			AppSocket::send_cmd('act_insert_selector', $arr_commande_statut);
			AppSocket::send_cmd('act_insert_selector', [[$commande_shop,
			                                             $commande_shop_val],
			                                            [$commande_shop_wait,
			                                             $commande_shop_wait_val],
			                                            [$commande_shop_livencou,
			                                             $commande_shop_livencou_val]]);
		}

		function notify_count_statut($table, $idcommande) {
			if (empty($idcommande)) {
				return;
			}

			$_table = "commande_statut";
			$_id    = "id$_table";

			$APP_COMMANDE   = new App($table);
			$APPTMP         = new App($_table);
			$APP_SH_J       = new App('shop_jours');
			$APP_LIV_AFFECT = new App('livreur_affectation');
			$APP_LIVREUR    = new App('livreur');

			$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);

			$idshop            = (int)$ARR_COMMANDE['idshop'];
			$idlivreur         = (int)$ARR_COMMANDE['idlivreur'];
			$idsecteur         = (int)$ARR_COMMANDE['idsecteur'];
			$idcommande_statut = (int)$ARR_COMMANDE['idcommande_statut'];

			if (empty($ARR_COMMANDE['idlivreur'])) {
				$CT = $APP_COMMANDE->find(['idsecteur'            => $idsecteur,
				                           'idlivreur'            => ['$exists' => false],
				                           'dateCreationCommande' => date('Y-m-d')]);

				$arr_commande_statut = [];
				array_push($arr_commande_statut, ["[data-idsecteur=$idsecteur][data-count=$_table][data-table_value=$idcommande_statut][data-table=commande]",
				                                  $CT->count()]);
				AppSocket::send_cmd('act_insert_selector', $arr_commande_statut);

			}

		}

		/**
		 * Nombre de commandes en cours pour un livreur
		 *
		 * @param $idlivreur
		 */
		function notify_commande_livreur($idlivreur) {
			if (empty($idlivreur)) {
				return;
			}

			$APP_COMMANDE        = new App('commande');
			$arr_commande_statut = [];

			$CT = $APP_COMMANDE->find(['idlivreur'           => (int)$idlivreur,
			                           'codeCommande_statut' => ['$nin' => ['END']]]);
			array_push($arr_commande_statut, ["[data-idlivreur=$idlivreur][data-count_livreur=commande]",
			                                  $CT->count()]);

			AppSocket::send_cmd('act_insert_selector', $arr_commande_statut);
		}

		/**
		 * Nombre de commandes en cours pour un secteur
		 *
		 * @param $idsecteur
		 */
		function notify_commande_secteur($idsecteur) {
			if (empty($idsecteur)) {
				return;
			}
			$room_secteur = "secteur_$idsecteur";
			$CT           = CommandeQueue::secteur_commande_queue_count($idsecteur);

			SendCmd::insert_selector("[data-idsecteur=$idsecteur][data-count_secteur=commande]", $CT, $room_secteur);
		}

		/**
		 * Nombre de commandes sans livreur free pour un secteur
		 *
		 * @param $idsecteur
		 */
		function notify_commande_free_secteur($idsecteur) {
			if (empty($idsecteur)) {
				return;
			}

			$room_secteur                = "secteur_$idsecteur";
			$secteur_commande_free_count = CommandeQueue::secteur_commande_free_count($idsecteur);

			SendCmd::insert_selector("[data-idsecteur=$idsecteur][data-count_secteur_commande_free=commande]", $secteur_commande_free_count, $room_secteur);
		}

		/**
		 * Nombre de commandes en cours pour un shop
		 *
		 * @param $idshop
		 */
		function notify_commande_shop($idshop) {
			if (empty($idshop)) {
				return;
			}

			$APP_COMMANDE        = new App('commande');
			$arr_commande_statut = [];

			$room_shop = "shop_$idshop";

			$CT = $APP_COMMANDE->find(['idshop'              => (int)$idshop,
			                           'codeCommande_statut' => ['$nin' => ['END']]]);
			array_push($arr_commande_statut, ["[data-idshop=$idshop][data-count_shop=commande]",
			                                  $CT->count()]);

			AppSocket::send_cmd('act_insert_selector', $arr_commande_statut, $room_shop);
		}

		/**
		 * @param $idsecteur
		 *
		 * @throws \MongoConnectionException
		 * @throws \MongoCursorTimeoutException
		 */
		function notify_wait_time_secteur($idsecteur) {
			$APP_SHOP = new App('shop');
			$RS_SHOP  = $APP_SHOP->find(['idsecteur' => (int)$idsecteur]);
			while ($ARR_SHOP = $RS_SHOP->getNext()) {
				$this->notify_wait_time_shop($ARR_SHOP['idshop']);
			}
		}

		/**
		 * @deprecated
		 * Temps attente client  pour un shop
		 *
		 * @param $idshop
		 */
		function notify_wait_time_shop($idshop) {
			return;
			$arr_cmd  = [];
			$Bin      = new Bin();
			$APP_SHOP = new App('shop');

			$ARR_SHOP = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			// temps    d'une livraison
			$time_livraison = $Bin->get_wait_time_shop_secteur($idshop) ?: time();
			$test_shop_open = $Bin::test_shop_open($idshop);
			$idsecteur      = (int)$ARR_SHOP['idsecteur'];

			$heure_livraison  = date('H:i', $time_livraison);
			$secondeLivraison = $time_livraison - time(); // en minutes
			$tempsLivraison   = ceil($secondeLivraison / 60); // en minutes

			$plus = CommandeQueue::secteur_shop_other_commande_non_prefin_count($idshop);

			$etat = 5;
			if ($ARR_SHOP['actifShop'] && $test_shop_open) {
				if ($tempsLivraison <= 35) {
					$etat = '1';
				} elseif ($tempsLivraison >= 150) {
					$etat = 5;
				} elseif ($tempsLivraison >= 120) {
					$etat = 4;
				} elseif ($tempsLivraison >= 90) {
					$etat = 3;
				} elseif ($tempsLivraison >= 60) {
					$etat = 2;
				}

				//$tempsLivraison = $Bin->get_value_wait_time_shop_secteur($idshop) . ' donc ' . $plus;

			} else {
				$tempsLivraison  = '--';
				$heure_livraison = 'fermé';
			}
			$NB_LIVREUR_DISP               = CommandeQueue::secteur_has_livreur_free_count($idsecteur);
			$NB_COMMAND_SHOP_WAITING       = CommandeQueue::shop_commande_queue_count($idshop);
			$NB_COMMAND_OTHER_SHOP_WAITING = CommandeQueue::secteur_shop_other_commande_non_prefin_count($idshop);
			$NB_LIVREUR_WORK               = CommandeQueue::secteur_has_livreur_count($idsecteur);
			$ARR_COMMAND_SECTEUR_LAST      = CommandeQueue::secteur_commande_queue_list_last($idsecteur);

			$NB_COMMAND_SECTEUR_WAITING       = CommandeQueue::secteur_commande_queue_count($idsecteur);

			$ARR_COMMANDES_SECTEUR_LAST      = CommandeQueue::secteur_commande_nonfree_list($idsecteur);

			$timeLivraisonCommande   = $ARR_COMMAND_SECTEUR_LAST['timeLivraisonCommande'] ?: time();
			$minuteLivraisonCommande = ceil(($timeLivraisonCommande - time()) / 60);
			$heureLivraisonCommande  = date('H:i', $timeLivraisonCommande);

			$facteur = ($NB_LIVREUR_DISP <= $NB_COMMAND_SHOP_WAITING)? TIME_PREPARATION_COMMANDE + TEMPS_LIVRAISON_COMMANDE : TEMPS_LIVRAISON_COMMANDE ;

			$DEBUG = $heureLivraisonCommande.' + '.$facteur;

			$temps_total = TIME_PREPARATION_COMMANDE + (($NB_LIVREUR_DISP <= $NB_COMMAND_SHOP_WAITING) ? TEMPS_LIVRAISON_COMMANDE : 0) + ($NB_COMMAND_OTHER_SHOP_WAITING * TEMPS_LIVRAISON_COMMANDE) + (($NB_LIVREUR_WORK <= $NB_COMMAND_OTHER_SHOP_WAITING) ? TIME_PREPARATION_COMMANDE : 0);
			$temps_total = $temps_total + (($NB_LIVREUR_WORK <= $NB_COMMAND_SHOP_WAITING) ? TEMPS_LIVRAISON_COMMANDE : 0);

			$tes = $tempsLivraison + ($plus * $temps_total);

			array_push($arr_cmd, ["[data-wait_time][data-idshop=$idshop]",
			                      $DEBUG.'<br/>'.$tes . ' / ' . $heure_livraison . ' : ' . $tempsLivraison . '-' . $plus . '-' . $temps_total . '<br>' . "$minuteLivraisonCommande +" . (TEMPS_LIVRAISON_COMMANDE + TIME_PREPARATION_COMMANDE)]);
			array_push($arr_cmd, ["[data-wait_hour][data-idshop=$idshop]",
			                      $heure_livraison]);
			array_push($arr_cmd, ["[data-wait_thumb][data-idshop=$idshop]",
			                      "<div class='etat_$etat'></div>"]);

			SendCmd::insert_selectors($arr_cmd);
			//AppSocket::send_cmd('act_insert_selector', $arr_cmd);
		}
	}
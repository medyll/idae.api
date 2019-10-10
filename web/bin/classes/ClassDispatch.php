<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 23/05/2018
	 * Time: 11:26
	 */
	class Dispatch {

		/**
		 * dispatch commands to all secteurs
		 */
		public function propose_commande_secteur_all_pool() {
			$BIN         = new Bin();
			$APP_SECTEUR = new App('secteur');

			$RS_SECTEUR = $APP_SECTEUR->find();

			$Not = new Notify();
			while ($ARR_SECTEUR = $RS_SECTEUR->getNext()) {
				/**
				 * debug
				 */
				$Not->notify_commande_secteur($ARR_SECTEUR['idsecteur']);
				$ARR_COMMANDE_SECTEUR = $BIN->secteur_commande_free_queue($ARR_SECTEUR['idsecteur']);

				$Notify = new Notify();
				$Notify->notify_commande_secteur($ARR_SECTEUR['idsecteur']);
				foreach ($ARR_COMMANDE_SECTEUR as $idkey_liv => $ARR_COMMANDE) {
					$this->propose_commande($ARR_COMMANDE['idcommande']);
				}

			}
		}

		/**
		 * dispatch commands to available coursiers in secteur
		 *
		 * @param $idsecteur
		 */
		public function propose_commande_secteur_pool($idsecteur) {
			$BIN = new Bin();

			$ARR_COMMANDE_SECTEUR = $BIN->secteur_commande_free_queue($idsecteur);

			foreach ($ARR_COMMANDE_SECTEUR as $idkey_liv => $ARR_COMMANDE) {
				$this->propose_commande($ARR_COMMANDE['idcommande']);
			}
		}

		/**
		 * remove command shown to available coursiers
		 *
		 * @param $idcommande
		 */
		public function remove_propose_commande($table_value) {
			$table                    = 'commande';
			$APP_COMMANDE             = new App($table);
			$APP_COMMANDE_PROPOSITION = new App('commande_proposition');
			$ARR_COMMANDE             = $APP_COMMANDE->findOne(['idcommande' => (int)$table_value]);

			$idlivreur = $ARR_COMMANDE['idlivreur'];

			// deactivate commande_proposition for all coursiers
			$RS_REM = $APP_COMMANDE_PROPOSITION->find(['idcommande' => (int)$table_value]);
			while ($ARR_REM = $RS_REM->getNext()) {
				$APP_COMMANDE_PROPOSITION->update(['idcommande_proposition' => (int)$ARR_REM['idcommande_proposition']], ['actifCommande_proposition' => 0]);
				if ($ARR_REM['idlivreur'] == $ARR_COMMANDE['idlivreur']) {
					//  activate commande_proposition for   coursier if one
					$APP_COMMANDE_PROPOSITION->update(['idcommande_proposition' => (int)$ARR_REM['idcommande_proposition']], ['livreur_take' => $idlivreur, 'acceptCommande_proposition' => 1]);
				}
			}

			// enlever annonce commande dans autre console livreur
			$secteur_selector = "[data-type_session=livreur][data-see_less_commande][data-idlivreur=$idlivreur][data-table_value=$table_value]";
			AppSocket::send_cmd('act_remove_selector', [$secteur_selector]);

			// enlever commande dans console livreur
			$secteur_selector = "[data-type_session=livreur][data-console_liste]:not([data-idlivreur=$idlivreur]) [data-table_value=$table_value][data-table=$table]";
			AppSocket::send_cmd('act_remove_selector', [$secteur_selector]);
		}

		/**
		 * remove command shown to  one  coursiers
		 *
		 * @param $table_value
		 * @param $idlivreur
		 */
		public function remove_propose_commande_coursier($table_value, $idlivreur) {
			$table = 'commande';

			// enlever commande dans console livreur
			$secteur_selector = "[data-type_session=livreur][data-console_liste][data-idlivreur=$idlivreur] [data-table_value=$table_value][data-table=$table]";
			AppSocket::send_cmd('act_remove_selector', [$secteur_selector]);
		}

		public function create_commande_proposition($idcommande, $idlivreur) {

			$APP_COMMANDE             = new IdaeDB('commande');
			$APP_COMMANDE_PROPOSITION = new IdaeDB('commande_proposition');

			$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);

			if (empty($ARR_COMMANDE['idcommande'])) return false;
			$idsecteur = (int)$ARR_COMMANDE['idsecteur'];
			$idshop    = (int)$ARR_COMMANDE['idshop'];

			$time       = time();
			$arr_insert = ['idcommande'                              => (int)$idcommande,
			               'idsecteur'                               => $idsecteur,
			               'idshop'                                  => $idshop,
			               'idlivreur'                               => (int)$idlivreur,
			               'actifCommande_proposition'               => 1,
			               'dateCommande_proposition'                => date('Y-m-d', $time),
			               'timeCommande_proposition'                => $time,
			               'heureCommande_proposition'               => date('H:i:s', $time),
			               'referenceCommande_proposition'           => $ARR_COMMANDE['referenceCommande'],
			               'heureFinPreparationCommande_proposition' => $ARR_COMMANDE['heureFinPreparationCommande'],
			               'timeFinPreparationCommande_proposition'  => $ARR_COMMANDE['timeFinPreparationCommande']];

			return $APP_COMMANDE_PROPOSITION->insert($arr_insert);
		}

		/**
		 * @param $idcommande
		 *
		 * @return bool
		 * @throws \MongoCursorException
		 */
		public function propose_commande($idcommande) {

			$APP_COMMANDE             = new App('commande');
			$APP_COMMANDE_PROPOSITION = new App('commande_proposition');
			$BIN                      = new Bin();

			$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);

			if (empty($ARR_COMMANDE['idcommande'])) return false;
			$idsecteur    = (int)$ARR_COMMANDE['idsecteur'];
			$idshop       = (int)$ARR_COMMANDE['idshop'];
			$room_shop    = 'shop_' . $idshop;
			$room_livreur = [];
			$time         = time();
			//AppSocket::send_cmd('act_notify', ['msg' => "propose commande $idcommande " . $ARR_COMMANDE['codeCommande'].' '.  $ARR_COMMANDE['referenceCommande']]);

			// NE PAS PROPOSER SI SHOP   COMMANDE NON PREFIN > NBLIV DISP
			$shop_has_other_commande_non_prefin = CommandeQueue::shop_has_other_commande_non_prefin($idshop, $idcommande); // shop_has_other_commandefree_non_prefin_list

			if ($shop_has_other_commande_non_prefin) {
				return false;
			}

			$arr_livreur_free = $BIN->test_livreur_affect_free($idsecteur);

			// proposition
			foreach ($arr_livreur_free as $idkey_liv => $arr_livreur) {
				$idlivreur = (int)$arr_livreur['idlivreur'];
				// proposal deja actif ?
				$TEST_COMMANDE_PROPOSITION = $APP_COMMANDE_PROPOSITION->find(['idlivreur'                              => $idlivreur,
				                                                              'idsecteur'                              => $idsecteur,
				                                                              'livreur_take'                           => ['$ne' => $idlivreur],
				                                                              'dateCommande_proposition'               => date('Y-m-d'),
				                                                              'endedCommande_proposition'              => ['$ne' => 1],
				                                                              'timeFinPreparationCommande_proposition' => ['$lt' => $ARR_COMMANDE['timeFinPreparationCommande']],
				                                                              'actifCommande_proposition'              => 1])->sort(['timeFinPreparationCommande_proposition' => 1]);
				if ($TEST_COMMANDE_PROPOSITION->count() != 0) {
					//  AppSocket::send_cmd('act_notify', ['msg' => "NOT propose commande $idcommande ".$arr_livreur['nomLivreur'].$TEST_COMMANDE_PROPOSITION->count()  ]);

					continue;
				}

				$ARR_COMMANDE_PROPOSITION = $APP_COMMANDE_PROPOSITION->findOne(['idcommande' => (int)$idcommande, 'idlivreur' => (int)$arr_livreur['idlivreur']]);

				if (empty($ARR_COMMANDE_PROPOSITION['idcommande'])) {
					$idcommande_proposition = $this->create_commande_proposition($idcommande, $idlivreur);
				} else {
					$idcommande_proposition = (int)$ARR_COMMANDE_PROPOSITION['idcommande_proposition'];
				}

				$room_livreur[] = 'livreur_' . $idlivreur;
			}

			// shopww
			SendCmd::insert_mdl('app_console_thumb', 'commande', $idcommande, ['data-table' => 'commande', 'data-type_session' => 'shop', 'data-idsecteur' => $idsecteur, 'data-idshop' => $idshop], $room_shop);
			// livreur si
			if (!empty($room_livreur)) {
				SendCmd::insert_mdl('app_fiche/app_fiche_reserv', 'commande', $idcommande, ['data-table' => 'commande', 'data-idsecteur' => $idsecteur, 'data-type_session' => 'livreur'], $room_livreur);
			} else {// sinon

			}
			// agent
			SendCmd::insert_mdl('app_console/agent/app_console_fiche', 'commande', $idcommande, ['data-type_session' => 'agent', 'data-type_liste' => 'pool_statut_START'], 'room_agent');
		}

		/**
		 * show command shop
		 *
		 * @param $idcommande
		 */
		public function propose_commande_shop($idcommande) {
			$APP_COMMANDE = new App('commande');

			$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);
			$idsecteur    = (int)$ARR_COMMANDE['idsecteur'];
			$idshop       = (int)$ARR_COMMANDE['idshop'];

			$room_shop = 'shop_' . $idshop;
			// shop
			SendCmd::insert_mdl('app_console_thumb', 'commande', $idcommande, ['data-table' => 'commande', 'data-type_session' => 'shop', 'data-idsecteur' => $idsecteur, 'data-idshop' => $idshop], $room_shop);

		}

		public function propose_commande_coursier($idlivreur) {
			ini_set('display_errors', 'On');
			$BIN                      = new Bin();
			$APP_COMMANDE             = new App('commande');
			$APP_LIVREUR              = new App('livreur');
			$APP_COMMANDE_PROPOSITION = new App('commande_proposition');
			$ARR_LIVREUR              = $APP_LIVREUR->findOne(['idlivreur' => (int)$idlivreur]);

			if (empty($ARR_LIVREUR['actifLivreur'])) return false;

			$room_livreur         = 'livreur_' . $idlivreur;
			$idsecteur            = $ARR_LIVREUR['idsecteur'];
			$ARR_COMMANDE_SECTEUR = CommandeQueue::secteur_commande_free_list($idsecteur);

			if (empty($ARR_COMMANDE_SECTEUR)) return false;
			//
			$QY   = ['idsecteur' => $idsecteur, 'codeCommande_statut' => ['$ne' => 'END'], 'idlivreur' => ['$in' => ['', 0, null, (int)$idlivreur]]];
			$SORT = ['idlivreur' => -1, 'slotCommande' => 1, 'rangCommande' => 1];
			$RS   = $APP_COMMANDE->find($QY)->sort($SORT);
			//
			$ARR_COMMANDE = $RS->getNext();
			$idcommande   = (int)$ARR_COMMANDE['idcommande'];
			$idshop       = (int)$ARR_COMMANDE['idshop'];

			$shop_has_other_commande_non_prefin = CommandeQueue::shop_has_other_commande_non_prefin($idshop, $idcommande);

			if ($shop_has_other_commande_non_prefin) {
				return false;
			}
			echo "GO ON";
			// test si livreur est engagé
			$HAS = CommandeProposition::livreur_has_proposition_before_commande($idlivreur, $idcommande);
			//var_dump($HAS);
			//if (!empty($HAS)) return false;
			// test si livreur a déja une proposition sur cette commande
			$ARR_COMMANDE_PROPOSITION = $APP_COMMANDE_PROPOSITION->findOne(['idcommande' => (int)$idcommande, 'idlivreur' => (int)$idlivreur]);
			if (empty($ARR_COMMANDE_PROPOSITION['idcommande'])) {

				$this->create_commande_proposition($idcommande, $idlivreur);// $APP_COMMANDE_PROPOSITION->insert($arr_insert);
			} else {
				$ARR_COMMANDE_PROPOSITION['idcommande_proposition'];
			}

			$html_tags = ['data-table' => 'commande', 'data-idlivreur' => $idlivreur, 'data-idsecteur' => $idsecteur, 'data-type_session' => 'livreur'];

			SendCmd::insert_mdl('app_fiche/app_fiche_reserv', 'commande', $idcommande, $html_tags, $room_livreur);

			/*$Notify = new Notify();
			$Notify->notify_commande_change($idcommande);*/
		}

		public function propose_commande_coursier_old($idlivreur) {
			$BIN                      = new Bin();
			$APP_COMMANDE             = new App('commande');
			$APP_LIVREUR              = new App('livreur');
			$APP_COMMANDE_PROPOSITION = new App('commande_proposition');
			$ARR_LIVREUR              = $APP_LIVREUR->findOne(['idlivreur' => (int)$idlivreur]);

			if (empty($ARR_LIVREUR['actifLivreur'])) return false;
			$table        = 'commande';
			$room         = "livreur_" . $idlivreur;
			$room_livreur = 'livreur_' . $idlivreur;

			$idsecteur = $ARR_LIVREUR['idsecteur'];

			$ARR_COMMANDE_SECTEUR = CommandeQueue::secteur_commande_free_list($idsecteur);
			if (empty($ARR_COMMANDE_SECTEUR)) {
				return false;
			}
			//
			$QY     = ['idsecteur' => $idsecteur, 'codeCommande_statut' => ['$ne' => 'end'], 'idlivreur' => ['$in' => ['', 0, null, (int)$idlivreur]]];
			$SORT   = ['slotCommande' => 1, 'rangCommande' => 1];
			$RS     = $APP_COMMANDE->find($QY)->sort($SORT);
			$ARR_RS = iterator_to_array($RS);
			//
			$ARR_COMMANDE = array_values($ARR_COMMANDE_SECTEUR)[0];
			$idcommande   = (int)$ARR_COMMANDE['idcommande'];
			$idshop       = (int)$ARR_COMMANDE['idshop'];

			$IS_FREE = $BIN->test_livreur_is_free($idlivreur);
			if (!empty($IS_FREE)) return false;
			//  test si livreur deja vu une commande, non traitée par lui ni personne
			$LAST_SEEN = CommandeProposition::livreur_last_commande_seen_still_active($idlivreur);
			if (!empty($LAST_SEEN)) {
				$LAST_SEEN_BEFORE = CommandeQueue::shop_has_other_commande_non_prefin_count($idshop, $LAST_SEEN['idcommande']);
				if (empty($LAST_SEEN_BEFORE)) {
					$html_tags = ['data-table' => 'commande', 'data-idlivreur' => $idlivreur, 'data-idsecteur' => $idsecteur, 'data-type_session' => 'livreur'];

					SendCmd::insert_mdl('app_fiche/app_fiche_reserv', 'commande', $LAST_SEEN['idcommande'], $html_tags, $room_livreur);

					$Notify = new Notify();
					$Notify->notify_commande_change($LAST_SEEN['idcommande']);

					return true;

				}
			}
			// test si livreur est engagé
			$HAS = CommandeProposition::livreur_has_proposition_before_commande($idlivreur, $idcommande);

			if (!empty($HAS)) return false;
			// test si livreur a déja une proposition sur cette commande
			$ARR_COMMANDE_PROPOSITION = $APP_COMMANDE_PROPOSITION->findOne(['idcommande' => (int)$idcommande, 'idlivreur' => (int)$idlivreur]);
			if (empty($ARR_COMMANDE_PROPOSITION['idcommande'])) {
				$arr_insert = ['idcommande'                              => (int)$idcommande,
				               'idsecteur'                               => $idsecteur,
				               'idshop'                                  => $idshop,
				               'idlivreur'                               => (int)$idlivreur,
				               'actifCommande_proposition'               => 1,
				               'dateCommande_proposition'                => date('Y-m-d'),
				               'timeCommande_proposition'                => time(),
				               'heureCommande_proposition'               => date('H:i:s'),
				               'referenceCommande_proposition'           => $ARR_COMMANDE['referenceCommande'],
				               'heureFinPreparationCommande_proposition' => $ARR_COMMANDE['heureFinPreparationCommande'],
				               'timeFinPreparationCommande_proposition'  => $ARR_COMMANDE['timeFinPreparationCommande']];

				$idcommande_proposition = $APP_COMMANDE_PROPOSITION->insert($arr_insert);
			} else {
				$idcommande_proposition = (int)$ARR_COMMANDE_PROPOSITION['idcommande_proposition'];
			}

			$html_tags = ['data-table' => 'commande', 'data-idlivreur' => $idlivreur, 'data-idsecteur' => $idsecteur, 'data-type_session' => 'livreur'];

			SendCmd::insert_mdl('app_fiche/app_fiche_reserv', 'commande', $idcommande, $html_tags, $room_livreur);

			$Notify = new Notify();
			$Notify->notify_commande_change($idcommande);
		}

		/**
		 * statut console for agent only
		 *
		 * @param $idcommande
		 */
		public function propose_commande_statut($table_value) {
			$table        = 'commande';
			$APP_COMMANDE = new App($table);
			$APP_COMMANDE->consolidate_scheme((int)$table_value);
			$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => (int)$table_value]);
			if (empty($ARR_COMMANDE['idcommande'])) return false;

			$remove_selector = "[data-type_session=agent][data-type_liste]:not([data-type_liste=pool_statut_" . $ARR_COMMANDE['codeCommande_statut'] . "]) [data-table_value=$table_value][data-table=$table]";

			// pour console agent
			SendCmd::remove_selector([$remove_selector], 'room_agent');
			SendCmd::insert_mdl('app_console/agent/app_console_fiche', $table, $table_value, '[data-type_session=agent][data-type_liste=pool_statut_' . $ARR_COMMANDE['codeCommande_statut'] . ']', 'room_agent');

		}

		public function propose_commande_sound($table_value) {
			$APP_COMMANDE        = new IdaeDB('commande');
			$APP_COMMANDE_STATUT = new IdaeDB('commande_statut');

			$ARR_COMMANDE        = $APP_COMMANDE->findOne(['idcommande' => $table_value]);
			$ARR_COMMANDE_STATUT = $APP_COMMANDE_STATUT->findOne(['idcommande_statut' => (int)$ARR_COMMANDE['idcommande_statut']]);

			$idshop    = $ARR_COMMANDE['idshop'];
			$idlivreur = $ARR_COMMANDE['idlivreur'];

			switch ($ARR_COMMANDE_STATUT['codeCommande_statut']):
				case 'RESERV';
				case 'LIVENCOU';
					SendCmd::play_sound("shop_$idshop");
					break;
				case 'PREFIN';
					SendCmd::play_sound("livreur_$idlivreur");
					break;
			endswitch;

		}

		/**
		 * remove commande from console sop / livreur when status END
		 *
		 * @param $table_value
		 *
		 * @return bool
		 */
		public function remove_commande($table_value) {
			$table                    = 'commande';
			$APP_COMMANDE             = new App($table);
			$APP_COMMANDE_PROPOSITION = new App('commande_proposition');

			$ARR_COMMANDE             = $APP_COMMANDE->findOne(['idcommande' => (int)$table_value]);
			$ARR_COMMANDE_PROPOSITION = $APP_COMMANDE_PROPOSITION->findOne(['idcommande' => (int)$table_value, 'idlivreur' => (int)$ARR_COMMANDE['idlivreur']]);
			if (empty($ARR_COMMANDE['idcommande'])) return false;
			if (empty($ARR_COMMANDE_PROPOSITION['idcommande'])) return false;

			$room_shop    = 'shop_' . $ARR_COMMANDE['idshop'];
			$room_livreur = 'livreur_' . $ARR_COMMANDE['idlivreur'];

			$upd = $APP_COMMANDE_PROPOSITION->update(['idcommande_proposition' => (int)$ARR_COMMANDE_PROPOSITION['idcommande_proposition']], ['actifCommande_proposition' => 0, 'endedCommande_proposition' => 1]);

			$oith                     = [];
			$commande_gutter_selector = "[data-console_liste] module[data-table_value=$table_value][data-table=$table]";
			$commande_shop_selector   = "[data-console_liste_detail] module[data-table_value=$table_value][data-table=$table]";

			array_push($oith, $commande_shop_selector);
			array_push($oith, $commande_gutter_selector);

			SendCmd::remove_selector([$commande_gutter_selector, $commande_shop_selector], $room_shop);
			SendCmd::remove_selector([$commande_gutter_selector], $room_livreur);
		}
	}

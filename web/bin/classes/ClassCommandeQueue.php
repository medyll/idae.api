<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 19/06/2018
	 * Time: 21:51
	 */
	class CommandeQueue {

		const  sort_from_first_slot = ['rangCommande' => 1, 'heureCommande' => 1, 'slotCommande' => 1];
		const  sort_from_last_slot  = ['rangCommande' => -1, 'heureCommande' => -1, 'slotCommande' => -1];

		const  sort_from_first_commande = ['rangCommande' => 1, 'heureCommande' => 1];
		const  sort_from_last_commande  = ['rangCommande' => -1, 'heureCommande' => -1];

		static function secteur_commande_queue_ended_count($idsecteur) {
			return self::secteur_commande_queue_ended_list($idsecteur)->count();
		}

		static function secteur_commande_queue_ended_list($idsecteur) {
			$APP_COMMANDE = new IdaeDB('commande');
			$day          = date('Y-m-d');

			$rs_test_commande_secteur = $APP_COMMANDE->find(['codeCommande_statut' => ['$in' => ['END']], 'idsecteur' => (int)$idsecteur, 'dateCommande' => $day]);
			$rs_test_commande_secteur->sort(self::sort_from_first_slot);

			return $rs_test_commande_secteur;
		}

		static function secteur_commande_queue_count($idsecteur) {
			return self::secteur_commande_queue_list($idsecteur)->count();
		}

		static function secteur_commande_queue_list($idsecteur) {
			$APP_COMMANDE = new IdaeDB('commande');
			$day          = date('Y-m-d');

			$rs_test_commande_secteur = $APP_COMMANDE->find(['codeCommande_statut' => ['$nin' => ['END']], 'idsecteur' => (int)$idsecteur, 'dateCommande' => $day])->sort(['rangCommande' => 1, 'heureCommande' => 1]);

			return $rs_test_commande_secteur;
		}

		static function secteur_commande_queue_list_last($idsecteur) {
			return self::secteur_commande_queue_list($idsecteur)->sort(['slotCommande' => -1, 'rangCommande' => -1])->getNext();
		}

		static function secteur_commande_free_count($idsecteur, $day = '', $now = '') {
			return sizeof(self::secteur_commande_free_list($idsecteur));
		}

		static function secteur_commande_free_list($idsecteur, $day = '', $now = '') {
			$APP_COMMANDE = new IdaeDB('commande');
			$day          = date('Y-m-d');

			$rs_test_commande_secteur = $APP_COMMANDE->find(['idlivreur' => ['$in' => ['', 0, null]], 'idsecteur' => (int)$idsecteur, 'dateCommande' => $day])->sort(['slotCommande' => 1, 'rangCommande' => 1, 'heureCommande' => 1]);

			return iterator_to_array($rs_test_commande_secteur);
		}

		static function secteur_commande_nonfree_count($idsecteur) {
			return sizeof((self::secteur_commande_nonfree_list($idsecteur)));
		}

		static function secteur_commande_nonfree_list($idsecteur, $day = '', $now = '') {
			$APP_COMMANDE = new App('secteur');
			$day          = date('Y-m-d');

			$rs_test_commande_secteur = $APP_COMMANDE->find(['codeCommande_statut' => ['$nin' => ['PREFIN', 'LIVENCOU', 'END']], 'idsecteur' => (int)$idsecteur, 'dateCommande' => $day])->sort(['slotCommande' => 1, 'rangCommande' => 1, 'heureCommande' => 1]);

			return iterator_to_array($rs_test_commande_secteur);
		}

		/**
		 * @param      $idsecteur
		 * @param null $idlivreur
		 *
		 * @return int
		 */
		static function secteur_has_livreur_count($idsecteur, $idlivreur = null) {
			return sizeof(self::secteur_has_livreur_list($idsecteur, $idlivreur));
		}

		/**
		 * @param      $idsecteur
		 * @param null $idlivreur
		 *
		 * @return array
		 */
		static function secteur_has_livreur_list($idsecteur, $idlivreur = null) {

			$APP_LIV        = new IdaeDB('livreur');
			$APP_LIV_AFFECT = new IdaeDB('livreur_affectation');
			$idsecteur      = (int)$idsecteur;

			$time_test_against = time() - (TIME_PREPARATION_COMMANDE + TEMPS_LIVRAISON_COMMANDE) * 60;
			$day               = date('Y-m-d');
			$now               = date('H:i:s');

			$vars_qy_liv['heureDebutLivreur_affectation'] = ['$lte' => $now];
			$vars_qy_liv['heureFinLivreur_affectation']   = ['$gte' => $now];
			$vars_qy_liv['idsecteur']                     = $idsecteur;
			$vars_qy_liv['dateDebutLivreur_affectation']  = $day;
			$vars_qy_liv['actifLivreur_affectation']      = 1;

			$vars_liv = []; // livreurs actifs !
			if ($idlivreur) {
				$vars_liv['idlivreur'] = $idlivreur;
			}
			$vars_liv['idsecteur']    = $idsecteur;
			$vars_liv['actifLivreur'] = 1;
			$LIV_IN                   = $APP_LIV->distinct('idlivreur', $vars_liv);
			$vars_qy_liv['idlivreur'] = ['$in' => $LIV_IN];

			$rs_test_affect = $APP_LIV_AFFECT->find($vars_qy_liv);

			return iterator_to_array($rs_test_affect);
		}

		static function secteur_has_livreur_free($idsecteur) {
			return (self::secteur_has_livreur_free_list($idsecteur)->count() == 0);
		}

		static function secteur_has_livreur_free_count($idsecteur) {
			return self::secteur_has_livreur_free_list($idsecteur)->count();
		}

		/**
		 * Livreurs actifs, n'ayant aucune commande en cours
		 *
		 * @param        $idsecteur
		 * @param string $day
		 * @param string $now
		 *
		 * @return MongoCursor
		 */
		static function secteur_has_livreur_free_list($idsecteur, $day = '', $now = '') {
			$APP_COMMANDE        = new IdaeDB('commande');
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_LIVREUR         = new App('livreur');
			$BIN                 = new Bin();

			$idsecteur = (int)$idsecteur;

			$day = date('Y-m-d');

			$ARR_STATUT                           = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'END']);
			$livreur_secteur['idsecteur']         = $idsecteur;
			$livreur_secteur['dateCommande']      = $day;
			$livreur_secteur['idcommande_statut'] = ['$nin' => [(int)$ARR_STATUT['idcommande_statut']]];
			// livreur FREE
			$arr_test_affect_free = $APP_COMMANDE->distinct('idlivreur', $livreur_secteur);

			$arr_secteur_liv = CommandeQueue::secteur_has_livreur_list($idsecteur);
			//$arr_test_affect = $BIN->test_livreur_affect($idsecteur);
			$arr_test_affect = array_column(array_values($arr_secteur_liv), 'idlivreur');

			//
			$arr_idlivreur = array_values(array_diff($arr_test_affect, $arr_test_affect_free));

			$rs_test_affect = $APP_LIVREUR->find(['idlivreur' => ['$in' => $arr_idlivreur], 'actifLivreur' => 1]);

			return $rs_test_affect;

		}

		static function secteur_has_livreur_waiting($idsecteur, $day = '') {
			return (self::secteur_has_livreur_waiting_list($idsecteur)->count() == 0);
		}

		static function secteur_has_livreur_waiting_count($idsecteur, $day = '') {
			return self::secteur_has_livreur_waiting_list($idsecteur)->count();
		}

		static function secteur_has_livreur_waiting_list($idsecteur, $day = '') {
			$APP_COMMANDE        = new App('commande');
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_LIVREUR         = new App('livreur');

			$day = date('Y-m-d');

			$arr_test_affect = CommandeQueue::secteur_has_livreur_list((int)$idsecteur);
			$arr_idlivreur   = array_column(array_values($arr_test_affect), 'idlivreur');

			$ARR_STATUT                       = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => ['$in' => ['START', 'RESERV', 'RUN']]]);
			$vars_qy_liv['idsecteur']         = (int)$idsecteur;
			$vars_qy_liv['dateCommande']      = $day;
			$vars_qy_liv['idcommande_statut'] = ['$in' => [(int)$ARR_STATUT['idcommande_statut']]];
			$vars_qy_liv['idlivreur']['$nin'] = ['', 0, null];
			$vars_qy_liv['idlivreur']['$in']  = $arr_idlivreur;

			$arr_test_affect_free = $APP_COMMANDE->distinct_all('idlivreur', $vars_qy_liv);
			$rs_test_affect       = $APP_LIVREUR->find(['idlivreur' => ['$in' => $arr_test_affect_free]]);

			return $rs_test_affect;

		}

		/**
		 * get other commands ( same secteur ) other shops
		 *
		 * @param $idshop
		 *
		 * @return bool
		 */
		static public function secteur_shop_other_commande_non_prefin($idshop) {
			return (self::secteur_shop_other_commande_non_prefin_list($idshop)->count() == 0);
		}

		/**
		 * get other commands ( same secteur ) other shops
		 *
		 * @param $idshop
		 *
		 * @return int
		 */
		static public function secteur_shop_other_commande_non_prefin_count($idshop) {
			return self::secteur_shop_other_commande_non_prefin_list($idshop)->count();
		}

		/**
		 * get other commands ( same secteur ) other shops
		 *
		 * @param $idshop
		 *
		 * @return MongoCursor
		 */
		static public function secteur_shop_other_commande_non_prefin_list($idshop) {
			$APP_COMMANDE        = new  App("commande");
			$APP_COMMANDE_STATUT = new  App('commande_statut');
			$day                 = date('Y-m-d');
			$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'PREFIN']);

			$rs_test_commande_shop = $APP_COMMANDE->find(['ordreCommande_statut' => ['$lt' => $arr_commande_statut['ordreCommande_statut']],
			                                              'idshop'               => ['$ne' => (int)$idshop],
			                                              'dateCommande'         => $day])->sort(['rangCommande' => 1, 'heureCommande' => 1]);

			return $rs_test_commande_shop;
		}

		static public function shop_has_other_commande_non_prefin($idshop, $idcommande) {
			return (self::shop_has_other_commande_non_prefin_list($idshop, $idcommande)->count() != 0);
		}

		static public function shop_has_other_commande_non_prefin_count($idshop, $idcommande) {
			return self::shop_has_other_commande_non_prefin_list($idshop, $idcommande)->count();
		}

		static public function shop_has_other_commande_non_prefin_list($idshop, $idcommande) {
			$APP_COMMANDE        = new App("commande");
			$APP_COMMANDE_STATUT = new App('commande_statut');

			$day = date('Y-m-d');

			$arr_commande        = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);
			$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'PREFIN']);

			$rs_test_commande_shop = $APP_COMMANDE->find(['ordreCommande_statut' => ['$lt' => $arr_commande_statut['ordreCommande_statut']],
			                                              'idcommande'           => ['$ne' => (int)$idcommande],
			                                              'idshop'               => (int)$idshop,
			                                              'dateCommande'         => $day])->sort(['rangCommande' => 1, 'heureCommande' => 1]);

			return $rs_test_commande_shop;

		}

		static public function shop_has_other_commandefree_non_prefin_list($idshop, $idcommande) {
			$APP_COMMANDE        = new App("commande");
			$APP_COMMANDE_STATUT = new App('commande_statut');

			$day = date('Y-m-d');

			$arr_commande = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);

			$arr_commande_statut = $APP_COMMANDE_STATUT->distinct_all('idcommande_statut', ['codeCommande_statut' => ['$in' => ['START', 'RESERV', 'RUN']]]);

			$rs_test_commande_shop = $APP_COMMANDE->find(['idcommande_statut' => ['$in' => $arr_commande_statut],
			                                              'idcommande'        => ['$ne' => (int)$idcommande],
			                                              'idlivreur'         => ['$in' => ['', 0, null]],
			                                              'idshop'            => (int)$idshop,
			                                              'heureCommande'     => ['$lt' => $arr_commande['heureCommande']],
			                                              'dateCommande'      => $day])->sort(['rangCommande' => 1, 'heureCommande' => 1]);

			return $rs_test_commande_shop;
		}

		static public function shop_has_other_commandefree_non_prefin($idshop, $idcommande) {
			return (self::shop_has_other_commandefree_non_prefin_list($idshop, $idcommande)->count() == 0);
		}

		static public function shop_has_other_commandefree_non_prefin_count($idshop, $idcommande) {
			return self::shop_has_other_commandefree_non_prefin_list($idshop, $idcommande)->count();
		}

		/**
		 * @param $idshop
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		static public function shop_commande_queue_list($idshop) {
			$APP_COMMANDE        = new IdaeDB('commande');
			$APP_COMMANDE_STATUT = new IdaeDB('commande_statut');
			$day                 = date('Y-m-d');

			$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'PREFIN']);

			$rs_test_commande_shop = $APP_COMMANDE->find(['ordreCommande_statut' => ['$lt' => $arr_commande_statut['ordreCommande_statut']], 'idshop' => (int)$idshop, 'dateCommande' => $day])->sort(['rangCommande' => 1, 'heureCommande' => 1]);

			return $rs_test_commande_shop;
		}

		static public function shop_commande_queue_count($idshop) {
			return self::shop_commande_queue_list($idshop)->count();
		}

		static public function shop_commande_queue_undelivered_list($idshop) {
			$APP_COMMANDE        = new IdaeDB('commande');
			$APP_COMMANDE_STATUT = new IdaeDB('commande_statut');
			$day                 = date('Y-m-d');

			$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'END']);

			$rs_test_commande_shop = $APP_COMMANDE->find(['ordreCommande_statut' => ['$lt' => $arr_commande_statut['ordreCommande_statut']], 'idshop' => (int)$idshop, 'dateCommande' => $day])->sort(['rangCommande' => 1, 'heureCommande' => 1, 'slotCommande' => 1]);

			return $rs_test_commande_shop;
		}

		static public function shop_commande_queue_undelivered_count($idshop) {
			return self::shop_commande_queue_undelivered_list($idshop)->count();
		}

		static public function shop_commande_queue_undelivered_last_list($idshop) {
			$APP_COMMANDE        = new IdaeDB('commande');
			$APP_COMMANDE_STATUT = new IdaeDB('commande_statut');
			$day                 = date('Y-m-d');

			$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'END']);

			$rs_test_commande_shop = $APP_COMMANDE->find(['ordreCommande_statut' => ['$lt' => $arr_commande_statut['ordreCommande_statut']], 'idshop' => (int)$idshop, 'dateCommande' => $day])->sort(['rangCommande' => -1, 'heureCommande' => -1, 'slotCommande' => -1]);

			return $rs_test_commande_shop;
		}

		static public function shop_commande_queue_undelivered_last_elemnt($idshop) {
			return self::shop_commande_queue_undelivered_last_list($idshop)->getNext();
		}

		/**
		 * @param $idshop
		 *
		 * @return \MongoCursor
		 * @throws \MongoCursorException
		 */
		static public function shop_commande_queue_shift_list($idshop) {
			$APP_COMMANDE        = new IdaeDB('commande');
			$APP_COMMANDE_STATUT = new IdaeDB('commande_statut');
			$day                 = date('Y-m-d');
			$arr_shift           = Bin::get_shop_shift_current($idshop);
			$arr_shift_vars      = ['heureCommande' => ['$gte' => $arr_shift['heureDebutShop_jours_shift'], '$lte' => $arr_shift['heureFinShop_jours_shift']]];

			$rs_test_commande_shop = $APP_COMMANDE->find($arr_shift_vars + ['idshop' => (int)$idshop, 'dateCommande' => $day])->sort(['rangCommande' => 1, 'heureCommande' => 1]);

			return $rs_test_commande_shop;
		}

		/**
		 * @param $idshop
		 *
		 * @return bool
		 * @throws \MongoCursorException
		 */
		static public function shop_commande_queue_shift($idshop) {
			return (self::shop_commande_queue_shift_count($idshop) == 0);
		}

		/**
		 * @param $idshop
		 *
		 * @return int
		 * @throws \MongoCursorException
		 */
		static public function shop_commande_queue_shift_count($idshop) {
			return self::shop_commande_queue_shift_list($idshop)->count();
		}

		static public function shop_commande_queue_last($idshop) {
			$APP_COMMANDE        = new App('commande');
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$day                 = date('Y-m-d');

			$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'PREFIN']);

			$rs_test_commande_shop = $APP_COMMANDE->find(['ordreCommande_statut' => ['$lt' => $arr_commande_statut['ordreCommande_statut']], 'idshop' => (int)$idshop, 'dateCommande' => $day])->sort(['heureCommande' => -1, 'rangCommande' => -1]);

			return $rs_test_commande_shop->getNext();
		}

		/**
		 * shop actual slot is rang commande !!! shop_actual_slot
		 *
		 * @param $idcommande
		 *
		 * @return int
		 */
		static public function shop_actual_slot($idcommande) {
			$APP_COMMANDE = new App('commande');
			$APP_SHOP     = new App('shop');
			$arr_commande = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);

			$idshop              = $arr_commande['idshop'];
			$arr_shop            = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$list_secteur        = CommandeQueue::secteur_commande_queue_list($arr_shop['idsecteur']);
			$list_shop           = CommandeQueue::shop_commande_queue_last($idshop);
			$secteur_queue_count = CommandeQueue::secteur_shop_other_commande_non_prefin($idshop);
			$nb_livreur_disp     = CommandeQueue::secteur_has_livreur_count($arr_shop['idsecteur']);

			$arr_list = iterator_to_array($list_secteur);
			$adadou   = array_column($arr_list, 'rangCommande');
			$ADADA    = array_count_values($adadou);

			ksort($ADADA);

			$rangCommande = $arr_commande['rangCommande'];
			$taille       = $ADADA[$rangCommande];
			$taille_int   = (int)str_replace('S', '', $rangCommande);

			return $taille_int;

			$i = 0;
			if (empty($rangCommande)) {
				$i = 0;
				foreach ($ADADA as $index => $nb) {
					++$i;
					if ($nb >= $nb_livreur_disp && $i != sizeof($ADADA)) {
						continue;
					}
					$rang = (int)str_replace('S', '', $index);

					$rangCommande = $rang;
				}

			} else {
				$taille     = $ADADA[$rangCommande];
				$taille_int = (int)str_replace('S', '', $rangCommande);

				if (empty($taille)) {
					$i = 0;

					foreach ($ADADA as $index => $nb) {
						++$i;
						if ($nb >= $nb_livreur_disp && $i != sizeof($ADADA)) {
							continue;
						}
						$rang         = (int)str_replace('S', '', $index);
						$rangCommande = $rang;
					}
				} else {
					$rangCommande = $taille_int;
				}
			}

			return $rangCommande;

		}

		static public function shop_next_slot($idshop) {

			$APP_SHOP        = new IdaeDB('shop');
			$arr_shop        = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$a_nomShop       = $arr_shop['nomShop'];
			$list_shop       = CommandeQueue::shop_commande_queue_last($idshop);
			$list_secteur    = CommandeQueue::secteur_commande_queue_list($arr_shop['idsecteur']); // last before shop
			$nb_livreur_disp = CommandeQueue::secteur_has_livreur_count($arr_shop['idsecteur']);

			$arr_list = iterator_to_array($list_secteur);
			$adadou   = array_column($arr_list, 'slotCommande');
			$ADADA    = array_count_values($adadou);

			ksort($ADADA, SORT_STRING);

			$rangCommande_str = $list_shop['slotCommande'] ?: 'R0';
			$rangCommande     = (int)str_replace('R', '', $rangCommande_str);

			$taille = $ADADA[$rangCommande_str];

			$i = 0;

			$calculated = $rangCommande + 1 ;

			foreach ($ADADA as $index => $nb) {
				++$i;

				$rangSecteur    = (int)str_replace('R', '', $index); // rang actuel secteur ADADA
				$rangSecteur_nb = $ADADA[$index];

				if ($rangSecteur <= $rangCommande) {
					continue;
				}

				$calculated     = ++$rangSecteur_nb;
			}

			return "$calculated";

		}
	}

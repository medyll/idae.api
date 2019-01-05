<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 03/10/2017
	 * Time: 00:57
	 *
	 * all moved to CommandeQueue
	 */
	class Bin extends App {

		function __construct() {
			parent::__construct();
		}

		/**
		 * @deprecated CommandeQueue::secteur_has_livreur_list
		 *
		 * @param      $idsecteur
		 * @param null $idlivreur
		 *
		 * @return array
		 */
		function test_livreur_affect($idsecteur, $idlivreur = null) {
			return CommandeQueue::secteur_has_livreur_list($idsecteur, $idlivreur);
		}

		function test_livreur_affect_wait($idsecteur, $day = '', $now = '') { // livreur avec affectation, mais livraison pas encore
			$APP_COMMANDE        = new App('commande');
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_LIVREUR         = new App('livreur');
			$APP_LIV_AFFECT      = new App('livreur_affectation');

			$idsecteur = (int)$idsecteur;

			$day = date('Y-m-d');

			$ARR_STATUT                       = $APP_COMMANDE_STATUT->distinct_all('idcommande_statut', ['codeCommande_statut' => ['$in' => ['RESERV',
			                                                                                                                                 'RUN']]]);
			$vars_qy_liv['idsecteur']         = (int)$idsecteur;
			$vars_qy_liv['dateCommande']      = $day;
			$vars_qy_liv['idcommande_statut'] = ['$in' => $ARR_STATUT];
			//
			$arr_test_affect_free = $APP_COMMANDE->distinct_all('idlivreur', $vars_qy_liv);
			//
			$arr_test_affect      = $this->test_livreur_affect($idsecteur);
			$arr_idlivreur_affect = array_column(array_values($arr_test_affect), 'idlivreur');
			$arr_idlivreur        = array_values(array_intersect($arr_idlivreur_affect, $arr_test_affect_free));

			$rs_test_affect = $APP_LIVREUR->find(['idlivreur'    => ['$in' => $arr_idlivreur],
			                                      'actifLivreur' => 1]);

			//
			return iterator_to_array($rs_test_affect);

		}

		function test_livreur_proposal_free($idsecteur, $day = '', $now = '') {
			$APP_COMMANDE        = new App('commande');
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_LIVREUR         = new App('livreur');

			$idsecteur = (int)$idsecteur;

			$day = date('Y-m-d');

			$ARR_STATUT                       = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'END']);
			$vars_qy_liv['idsecteur']         = $idsecteur;
			$vars_qy_liv['dateCommande']      = $day;
			$vars_qy_liv['idcommande_statut'] = ['$nin' => [(int)$ARR_STATUT['idcommande_statut']]];
			//
			$arr_test_affect = $this->test_livreur_affect($idsecteur);
			$arr_idlivreur   = array_column(array_values($arr_test_affect), 'idlivreur');
			// les livreurs en commandes  ou en proposal sont à exclure
			$arr_test_affect_free = $APP_COMMANDE->distinct_all('idlivreur', $vars_qy_liv);
			//
			$arr_idlivreur = array_values(array_diff($arr_idlivreur, $arr_test_affect_free));

			$rs_test_affect = $APP_LIVREUR->find(['idlivreur'    => ['$in' => $arr_idlivreur],
			                                      'actifLivreur' => 1]);

			//
			return iterator_to_array($rs_test_affect);

		}

		function test_livreur_is_affected($idlivreur, $date_time = null) {
			$APP_LIV        = new App('livreur');
			$APP_LIV_AFFECT = new App('livreur_affectation');

			$day = date('Y-m-d', $date_time ?: time());

			$now = date('H:i:s', $date_time ?: time());

			$vars_qy_liv['heureDebutLivreur_affectation'] = ['$lte' => $now];
			$vars_qy_liv['heureFinLivreur_affectation']   = ['$gte' => $now];
			$vars_qy_liv['idlivreur']                     = $idlivreur;
			$vars_qy_liv['dateDebutLivreur_affectation']  = $day;
			$vars_qy_liv['actifLivreur_affectation']      = 1;

			$vars_liv                 = []; // livreurs actifs !
			$vars_liv['idlivreur']    = (int)$idlivreur;
			$vars_liv['actifLivreur'] = 1;
			$LIV_IN                   = $APP_LIV->distinct_all('idlivreur', $vars_liv);

			$vars_qy_liv['idlivreur'] = ['$in' => $LIV_IN];

			$rs_test_affect = $APP_LIV_AFFECT->find($vars_qy_liv, ['_id' => 0]);

			return iterator_to_array($rs_test_affect);

		}

		function test_livreur_is_free($idlivreur, $day = '', $now = '') {
			$APP_COMMANDE        = new App('commande');
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_LIVREUR         = new App('livreur');

			$day = date('Y-m-d');

			$ARR_STATUT                       = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'END']);
			$vars_qy_liv['idlivreur']         = $idlivreur;
			$vars_qy_liv['dateCommande']      = $day;
			$vars_qy_liv['idcommande_statut'] = ['$nin' => [(int)$ARR_STATUT['idcommande_statut']]];

			$arr_test_affect_free = $APP_COMMANDE->distinct_all('idlivreur', $vars_qy_liv);
			$rs_test_affect       = $APP_LIVREUR->find(['idlivreur'    => ['$in' => $arr_test_affect_free],
			                                            'actifLivreur' => 1]);

			return iterator_to_array($rs_test_affect);

		}

		function test_livreur_affect_free($idsecteur, $day = '', $now = '') {
			$APP_COMMANDE        = new App('commande');
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_LIVREUR         = new App('livreur');

			$idsecteur = (int)$idsecteur;

			$day = date('Y-m-d');

			$ARR_STATUT                       = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'END']);
			$vars_qy_liv['idsecteur']         = $idsecteur;
			$vars_qy_liv['dateCommande']      = $day;
			$vars_qy_liv['idcommande_statut'] = ['$nin' => [(int)$ARR_STATUT['idcommande_statut']]];
			//
			$arr_test_affect = $this->test_livreur_affect($idsecteur);
			$arr_idlivreur   = array_column(array_values($arr_test_affect), 'idlivreur');
			// les livreurs en commandes sont à exclure
			$arr_test_affect_free = $APP_COMMANDE->distinct_all('idlivreur', $vars_qy_liv);
			//
			$arr_idlivreur = array_values(array_diff($arr_idlivreur, $arr_test_affect_free));

			$rs_test_affect = $APP_LIVREUR->find(['idlivreur'    => ['$in' => $arr_idlivreur],
			                                      'actifLivreur' => 1]);

			//
			return iterator_to_array($rs_test_affect);

		}

		function test_commande_shop_time($idshop, $day = '', $now = '') {
			$APP_COMMANDE = new App('commande');
			$day          = date('Y-m-d');
			$now          = date('H:i:s');

			$rs_test_commande_shop = $APP_COMMANDE->find(['idshop'       => $idshop,
			                                              'dateCommande' => $day], ['_id' => 0]);

			return iterator_to_array($rs_test_commande_shop);

		}

		function test_commande_shop($idshop, $day = '', $now = '') {
			$APP_COMMANDE = new App('commande');
			$day          = date('Y-m-d');
			$now          = date('H:i:s');

			$rs_test_commande_shop = $APP_COMMANDE->find(['idshop'       => $idshop,
			                                              'dateCommande' => $day], ['_id' => 0]);

			return iterator_to_array($rs_test_commande_shop);

		}

		function test_commande_shop_wait($idshop, $day = '', $now = '') {
			$APP_COMMANDE = new App('commande');
			$day          = date('Y-m-d');
			$now          = date('H:i:s');

			$rs_test_commande_shop = $APP_COMMANDE->find(['idlivreur'           => ['$in' => [null,
			                                                                                  0,
			                                                                                  '']],
			                                              'codeCommande_statut' => ['$ne' => 'END'],
			                                              'idshop'              => (int)$idshop,
			                                              'dateCommande'        => $day], ['_id' => 0]);

			return iterator_to_array($rs_test_commande_shop);

		}

		function get_elapsed_minutes_arr_for_commande($idcommande) {
			$table        = 'commande';
			$idcommande   = (int)$idcommande;
			$APP_COMMANDE = new App($table);

			$ARR = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);
			if (empty($ARR['idcommande'])) return false;

			$NOW = time();
			//
			switch ($ARR['codeCommande_statut']) {
				case 'START':
				case 'RESERV':
				case 'RUN':
					$start_time = ($ARR['timeDebutPreparationCommande'] > $NOW) ? $NOW : $ARR['timeDebutPreparationCommande'];
					$to_time    = ($ARR['timeDebutPreparationCommande'] > $NOW) ? $ARR['timeDebutPreparationCommande'] : $ARR['timeFinPreparationCommande'];

					$max            = $to_time - $start_time;
					$value_progress = $NOW - $start_time;
					break;
				case 'PREFIN':
				case 'LIVENCOU':
				case 'END':
					$start_time = $ARR['timeFinPreparationCommande'];
					$to_time    = $ARR['timeLivraisonCommande'];

					$max            = $to_time - $start_time;
					$value_progress = $NOW - $start_time;
					break;
				default:
					$start_time = ($ARR['timeDebutPreparationCommande'] > $NOW) ? $NOW : $ARR['timeDebutPreparationCommande'];
					$to_time    = ($ARR['timeDebutPreparationCommande'] > $NOW) ? $ARR['timeDebutPreparationCommande'] : $ARR['timeFinPreparationCommande'];

					$max            = $to_time - $start_time;
					$value_progress = $NOW - $start_time;
					break;
			}

			$arr_dates = ['start_time'     => $start_time,
			              'to_time'        => $to_time,
			              'max'            => $max,
			              'value_progress' => $value_progress];

			return $arr_dates;
		}

		function get_elapsed_secondes_arr_for_commande($idcommande) {
			if (!$idcommande) return false;

			$table        = 'commande';
			$idcommande   = (int)$idcommande;
			$APP_COMMANDE = new App($table);

			$arr_commande = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);
			if (empty($arr_commande['idcommande'])) return false;

			$NOW_TIME = time();
			$NOW      = date('H:i:s', $NOW_TIME);
			// $attentePreparationCommande
			$datetime1                    = new DateTime($NOW);
			$datetime2                    = new DateTime($arr_commande['heureFinPreparationCommande']);
			$interval                     = $datetime1->diff($datetime2);
			$elapsed_secondes_preparation = $interval->format('%R%s');
			$elapsed_minutes_preparation  = $interval->format('%R%i');
			$elapsed_secondes_preparation = $elapsed_secondes_preparation + ($elapsed_minutes_preparation * 60);

			$datetime1                  = new DateTime($NOW);
			$datetime2                  = new DateTime($arr_commande['heureLivraisonCommande']);
			$interval                   = $datetime1->diff($datetime2);
			$elapsed_minutes_livraison  = $interval->format('%R%i');
			$elapsed_secondes_livraison = $interval->format('%R%s');
			$elapsed_secondes_livraison = $elapsed_secondes_livraison + ($elapsed_minutes_livraison * 60);

			$datetime1                 = new DateTime($NOW);
			$datetime2                 = new DateTime($arr_commande['heureLivraisonCommande']);
			$interval                  = $datetime1->diff($datetime2);
			$elapsed_secondes_commande = $interval->format('%R%s');
			$elapsed_minutes_commande  = $interval->format('%R%i');
			$elapsed_secondes_commande = $elapsed_secondes_commande + ($elapsed_minutes_commande * 60);

			return ['elapsed_minutes_preparation'    => $elapsed_minutes_preparation,
			        'elapsed_secondes_preparation'   => $elapsed_secondes_preparation,
			        'elapsed_minutes_livraison'      => $elapsed_minutes_livraison,
			        'elapsed_secondes_livraison'     => $elapsed_secondes_livraison,
			        'elapsed_minutes_commande'       => $elapsed_minutes_commande,
			        'elapsed_secondes_commande'      => $elapsed_secondes_commande,
			        'remaining_minutes_preparation'  => TIME_PREPARATION_COMMANDE - $elapsed_minutes_preparation,
			        'remaining_secondes_preparation' => (TIME_PREPARATION_COMMANDE * 60) - $elapsed_secondes_preparation,
			        'remaining_minutes_livraison'    => TEMPS_LIVRAISON_COMMANDE - $elapsed_minutes_livraison,
			        'remaining_secondes_livraison'   => (TEMPS_LIVRAISON_COMMANDE * 60) - $elapsed_secondes_livraison,
			        'remaining_minutes_commande'     => DUREE_REALISATION_COMMANDE - $elapsed_minutes_commande,
			        'remaining_secondes_commande'    => (DUREE_REALISATION_COMMANDE * 60) - $elapsed_secondes_commande];
		}

		function get_next_commande_secteur($idsecteur) {
			$APP_COMMANDE = new App('commande');

			$rs_commande  = $APP_COMMANDE->find(['idsecteur' => (int)$idsecteur,
			                                     'idlivreur' => ['$in' => [0,
			                                                               null,
			                                                               '']]])->sort(['timeFinPreparationCommande' => 1]);
			$arr_commande = $rs_commande->getNext();

			return (int)$arr_commande['idcommande'];
		}

		function fetch_estimation_wait_time_fields($idshop) {

			$LAST_COMMANDE = CommandeQueue::shop_commande_queue_last($idshop);

			$return_field['attentePreparationCommande']    = $LAST_COMMANDE['attentePreparationCommande'];
			$return_field['heureDebutPreparationCommande'] = $LAST_COMMANDE['heureDebutPreparationCommande'];
			$return_field['timeDebutPreparationCommande']  = $LAST_COMMANDE['timeDebutPreparationCommande'];
			$return_field['heureFinPreparationCommande']   = $LAST_COMMANDE['heureFinPreparationCommande'];
			$return_field['timeFinPreparationCommande']    = $LAST_COMMANDE['timeFinPreparationCommande'];
			$return_field['heureLivraisonCommande']        = $LAST_COMMANDE['heureLivraisonCommande'];
			$return_field['timeLivraisonCommande']         = $LAST_COMMANDE['timeLivraisonCommande'];

			return $return_field;
		}

		static function get_shop_shift_current($idshop) {

			$APP_SH_J       = new IdaeDataDB('shop_jours');
			$APP_SH_J_SHIFT = new IdaeDataDB('shop_jours_shift');

			$index_jour = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;
			$NOW        = date('H:i:00');

			$arr_sh_j = $APP_SH_J->findOne(['idshop'     => (int)$idshop,
			                                'ordreJours' => $index_jour]);

			$return = $APP_SH_J_SHIFT->findOne(['idshop'                     => $idshop,
			                                    'idshop_jours'               => (int)$arr_sh_j['idshop_jours'],
			                                    'actifShop_jours_shift'      => 1,
			                                    'heureDebutShop_jours_shift' => ['$lte' => $NOW],
			                                    'heureFinShop_jours_shift'   => ['$gte' => $NOW]]);

			return $return;
		}

		static function get_shop_secteur_shift_current($idsecteur) {

			$APP_SH         = new IdaeDataDB('shop');
			$APP_SH_J       = new IdaeDataDB('shop_jours');
			$APP_SH_J_SHIFT = new IdaeDataDB('shop_jours_shift');

			$index_jour = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;
			$NOW        = date('H:i:00');

			$arr_sh_j       = $APP_SH_J->distinct('idshop', ['actifShop_jours' => 1,  'ordreJours' => $index_jour]);
			$arr_sh_j_shift = $APP_SH_J_SHIFT->distinct('idshop', ['idshop'                     => ['$in' => $arr_sh_j],
			                                                           'actifShop_jours_shift'      => 1,
			                                                           'heureDebutShop_jours_shift' => ['$lte' => $NOW],
			                                                           'heureFinShop_jours_shift'   => ['$gte' => $NOW]]);

			$rs_sh = $APP_SH->find(['idsecteur' => $idsecteur, 'idshop' => ['$in' => $arr_sh_j_shift]], ['_id' => 0]);

			return $rs_sh;
		}

		function getCommande_queue_periods($idcommande) {
			$DB_COMMANDE   = new IdaeDataDB('commande');
			$ARR_COMMANDE  = $DB_COMMANDE->findOne(['idcommande' => (int)$idcommande]);
			$idshop        = (int)$ARR_COMMANDE['idshop'];
			$ordreCommande = (int)$ARR_COMMANDE['ordreCommande'];

			$timeCommande = time();

			$seconde_preparation_commande = TIME_PREPARATION_COMMANDE * 60;
			$seconde_livraison_commande   = TEMPS_LIVRAISON_COMMANDE * 60;
			$multi_before_secondes        = ($ordreCommande - 1) * $seconde_preparation_commande;
			$timeDebutPreparationCommande = $timeCommande + $multi_before_secondes;
			$timeFinPreparationCommande   = $timeDebutPreparationCommande + $seconde_preparation_commande;
			$timeFinLivraisonCommande     = $timeFinPreparationCommande + $seconde_livraison_commande;

			$return_field['timeDebutPreparationCommande']  = $timeDebutPreparationCommande;
			$return_field['heureDebutPreparationCommande'] = date('H:i:00', $timeDebutPreparationCommande);
			$return_field['timeFinPreparationCommande']    = $timeFinPreparationCommande;
			$return_field['heureFinPreparationCommande']   = date('H:i:00', $timeFinPreparationCommande);
			$return_field['timeFinLivraisonCommande']      = $timeFinLivraisonCommande;
			$return_field['heureFinLivraisonCommande']     = date('H:i:00', $timeFinLivraisonCommande);

			return $return_field;
		}

		function get_update_estimation_wait_time_fields($idshop) {
			$BIN            = new Bin();
			$APP_SHOP       = new IdaeDataDB('shop');
			$APP_SH_J       = new IdaeDataDB('shop_jours');
			$APP_SH_J_SHIFT = new IdaeDataDB('shop_jours_shift');

			$ARR_SHOP  = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$idsecteur = (int)$ARR_SHOP['idsecteur'];

			$arr_commande_wait    = $BIN->shop_commande_queue($idshop);
			$arr_secteur_wait     = $BIN->secteur_commande_queue($idsecteur);
			$arr_secteur_wait_all = $BIN->secteur_commande_queue_all($idsecteur);
			$arr_liv_free         = $BIN->test_livreur_affect_free($idsecteur);
			//
			$nb_liv_free         = sizeof($arr_liv_free);
			$nb_commande_wait    = sizeof($arr_commande_wait);
			$nb_secteur_wait_all = sizeof($arr_secteur_wait_all);

			$timeCommande          = time();
			$multi_before          = ($nb_liv_free > sizeof($arr_secteur_wait)) ? 0 : ceil($nb_secteur_wait_all - $nb_liv_free);
			$multi_before_secondes = $multi_before * TIME_PREPARATION_COMMANDE;

			$attentePreparationCommande = $nb_commande_wait + 1;
			// fin de préparation
			$time_prep = ((TIME_PREPARATION_COMMANDE * 60) * $attentePreparationCommande) + ((int)$ARR_SHOP['tempsAttenteShop'] * 60);

			// début de préparation
			$timeDebutPreparationCommande  = $timeCommande + $time_prep - (TIME_PREPARATION_COMMANDE * 60);
			$heureDebutPreparationCommande = date('H:i:00', $timeDebutPreparationCommande);

			$heureFinPreparationCommande = date('H:i:00', $timeCommande + $time_prep);
			$timeFinPreparationCommande  = $timeCommande + $time_prep;

			// si nombre de livreurs free < nombre commandes START,RESERV,RUN du secteur ! alors normal, sinon date de fin derniere commande
			// on prend la date théorique de la derniere commande de la queue active ? => $arr_secteur_wait_all
			$multi = ceil($nb_secteur_wait_all - $nb_liv_free);
			// si une commande 30 mn sinon 20 min ? a voir
			$last_commande_to_liv = $arr_secteur_wait_all[$multi]['timeLivraisonCommande'];
			$auto_commande_to_liv = $timeFinPreparationCommande + ((TEMPS_LIVRAISON_COMMANDE * 60));
			if ($auto_commande_to_liv < $last_commande_to_liv - (TEMPS_LIVRAISON_COMMANDE * 60 * 2)) {
				$timeLivraisonCommande = $last_commande_to_liv + (TEMPS_LIVRAISON_COMMANDE * 60 * 2);
			} else {
				$timeLivraisonCommande = $auto_commande_to_liv;
			}
			$heureLivraisonCommande = date('H:i:00', $timeLivraisonCommande);

			/*$arr_upd['slotCommande']                       = 'R' . CommandeQueue::shop_actual_slot($idcommande);*/
			$return_field['rangCommande']                  = $attentePreparationCommande ?: '';
			$return_field['attentePreparationCommande']    = $attentePreparationCommande ?: '';
			$return_field['heureDebutPreparationCommande'] = $heureDebutPreparationCommande ?: '';
			$return_field['timeDebutPreparationCommande']  = $timeDebutPreparationCommande ?: '';
			$return_field['heureFinPreparationCommande']   = $heureFinPreparationCommande ?: '';
			$return_field['timeFinPreparationCommande']    = $timeFinPreparationCommande ?: '';
			$return_field['heureLivraisonCommande']        = $heureLivraisonCommande ?: '';
			$return_field['timeLivraisonCommande']         = $timeLivraisonCommande ?: '';

			return $return_field;
		}

		function get_estimation_wait_time_fields($idshop) {
			$BIN            = new Bin();
			$APP_SHOP       = new App('shop');
			$APP_SH_J       = new App('shop_jours');
			$APP_SH_J_SHIFT = new App('shop_jours_shift');

			$ARR_SHOP  = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$idsecteur = (int)$ARR_SHOP['idsecteur'];

			$duree_realisationCommande = DUREE_REALISATION_COMMANDE;// de la prise de commande à la livraison, en minutes
			$time_preparation_commande = TIME_PREPARATION_COMMANDE;
			$temps_livraison           = TEMPS_LIVRAISON_COMMANDE;

			$arr_commande_wait    = $BIN->shop_commande_queue($idshop);
			$arr_secteur_wait     = $BIN->secteur_commande_queue($idsecteur);
			$arr_secteur_wait_all = $BIN->secteur_commande_queue_all($idsecteur);
			$arr_liv_free         = $BIN->test_livreur_affect_free($idsecteur);

			if (sizeof($arr_commande_wait) > 4) {

			}

			$index_jour = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;
			$NOW        = date('H:i:s');

			$arr_sh_j     = $APP_SH_J->findOne(['idshop'     => $idshop,
			                                    'ordreJours' => $index_jour]);
			$arr_sh_shift = iterator_to_array($APP_SH_J_SHIFT->find(['idshop'                     => $idshop,
			                                                         'idshop_jours'               => (int)$arr_sh_j['idshop_jours'],
			                                                         'actifShop_jours_shift'      => 1,
			                                                         'heureDebutShop_jours_shift' => ['$lte' => $NOW],
			                                                         'heureFinShop_jours_shift'   => ['$gte' => $NOW]], ['_id' => 0])->sort(['heureDebutShop_jours_shift' => 1]));
			if (sizeof($arr_sh_shift) != 0) {
				$return_field['idshop_jours_shift'] = (int)$arr_sh_shift[0]['idshop_jours_shift'];
			} else {

			}

			$nb_liv_free         = sizeof($arr_liv_free);
			$nb_commande_wait    = sizeof($arr_commande_wait);
			$nb_secteur_wait_all = sizeof($arr_secteur_wait_all);

			$timeCommande          = time();
			$multi_before          = ($nb_liv_free > sizeof($arr_secteur_wait)) ? 0 : ceil($nb_secteur_wait_all - $nb_liv_free);
			$multi_before_secondes = $multi_before * TIME_PREPARATION_COMMANDE;

			$attentePreparationCommande = $nb_commande_wait + 1;
			// fin de préparation
			$time_prep = ((TIME_PREPARATION_COMMANDE * 60) * $attentePreparationCommande) + ((int)$ARR_SHOP['tempsAttenteShop'] * 60);

			// début de préparation
			$timeDebutPreparationCommande  = $timeCommande + $time_prep - (TIME_PREPARATION_COMMANDE * 60);
			$heureDebutPreparationCommande = date('H:i:00', $timeDebutPreparationCommande);

			$heureFinPreparationCommande = date('H:i:00', $timeCommande + $time_prep);
			$timeFinPreparationCommande  = $timeCommande + $time_prep;

			// si nombre de livreurs free < nombre commandes START,RESERV,RUN du secteur ! alors normal, sinon date de fin derniere commande
			if ($nb_liv_free >= sizeof($arr_secteur_wait)) {
				$timeLivraisonCommande  = $timeFinPreparationCommande + (($temps_livraison * 60));// * $attentePreparationCommande
				$heureLivraisonCommande = date('H:i:00', $timeLivraisonCommande);

			} else {
				// on prend la date théorique de la derniere commande de la queue active ? => $arr_secteur_wait_all
				$multi = ceil($nb_secteur_wait_all - $nb_liv_free);
				// si une commande 30 mn sinon 20 min ? a voir
				$last_commande_to_liv = $arr_secteur_wait_all[$multi]['timeLivraisonCommande'];
				$auto_commande_to_liv = $timeFinPreparationCommande + (($temps_livraison * 60));
				if ($auto_commande_to_liv < $last_commande_to_liv - ($temps_livraison * 60 * 2)) {
					$timeLivraisonCommande = $last_commande_to_liv + ($temps_livraison * 60 * 2);
				} else {
					$timeLivraisonCommande = $auto_commande_to_liv;
				}
				$heureLivraisonCommande = date('H:i:00', $timeLivraisonCommande);
			}

			$return_field['attentePreparationCommande']    = $attentePreparationCommande ?: '';
			$return_field['heureDebutPreparationCommande'] = $heureDebutPreparationCommande ?: '';
			$return_field['timeDebutPreparationCommande']  = $timeDebutPreparationCommande ?: '';
			$return_field['heureFinPreparationCommande']   = $heureFinPreparationCommande ?: '';
			$return_field['timeFinPreparationCommande']    = $timeFinPreparationCommande ?: '';
			$return_field['heureLivraisonCommande']        = $heureLivraisonCommande ?: '';
			$return_field['timeLivraisonCommande']         = $timeLivraisonCommande ?: '';

			return $return_field;
		}

		function get_value_wait_time_shop_secteur($idshop) {
			$time_livraison   = $this->get_wait_time_shop_secteur($idshop);
			$secondeLivraison = $time_livraison - time(); // en secondes
			$heures           = (int)($secondeLivraison / 3600);
			$minutes          = (int)(($secondeLivraison % 3600) / 60);
			$str_hours        = empty($heures) ? '' : $heures . ' hr';
			$str_hours        .= ($heures > 1) ? 's' : '';

			$str_minutes = ($minutes == 0) ? '' : ' ' . $minutes . ' mn';

			return $tempsLivraison = $str_hours . $str_minutes;
		}

		function get_wait_time_shop_secteur($idshop) {
			if (empty($idshop)) {
				return false;
			}
			$duree_realisationCommande = DUREE_REALISATION_COMMANDE;// de la prise de commande à la livraison, en minutes
			$time_preparation_commande = TIME_PREPARATION_COMMANDE;
			$temps_livraison           = TEMPS_LIVRAISON_COMMANDE;

			$APP_COMMANDE = new App('commande');
			$APP_SHOP     = new App('shop');
			$ARR_SHOP     = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$idsecteur    = (int)$ARR_SHOP['idsecteur'];
			$arr_liv_free = $this->test_livreur_affect_free($idsecteur);

			$return_field = $this->get_estimation_wait_time_fields($idshop);

			// si nombre de livreurs < nombre de commandes en cours...
			$nb_liv_free = sizeof($arr_liv_free);
			//$secteur_commande_nonfree_count = CommandeQueue::secteur_commande_nonfree_count($idsecteur);
			$secteur_commande_nonfree_count = CommandeQueue::secteur_commande_queue_count($idsecteur);

			$timeFinPreparationCommande = $return_field['timeFinPreparationCommande'];
			$timeLivraisonCommande      = $return_field['timeLivraisonCommande'];

			// si commande engagées dans autre shops

			if ($nb_liv_free <= $secteur_commande_nonfree_count) {
				$plus                   = CommandeQueue::secteur_shop_other_commande_non_prefin_count($idshop);
				$timeLivraisonCommande  = $timeFinPreparationCommande + ($plus * ($temps_livraison * 60));// * $attentePreparationCommande
				$heureLivraisonCommande = date('H:i:00', $timeLivraisonCommande);
			}

			return $timeLivraisonCommande;
		}

		/**
		 * @deprecated move to CommandeQueue::shop_commande_queue_list
		 *
		 * @param        $idshop
		 * @param string $day
		 * @param string $now
		 *
		 * @return array
		 */
		function shop_commande_queue($idshop, $day = '', $now = '') {
			$APP_COMMANDE = new App('commande');
			$day          = date('Y-m-d');
			$now          = date('H:i:s');

			$rs_test_commande_shop = $APP_COMMANDE->find(['codeCommande_statut' => ['$nin' => ['PREFIN',
			                                                                                   'LIVENCOU',
			                                                                                   'END']],
			                                              'idshop'              => (int)$idshop,
			                                              'dateCommande'        => $day], ['_id' => 0])->sort(['timeLivraisonCommande' => 1]);

			return iterator_to_array($rs_test_commande_shop);

		}

		function secteur_commande_free_queue($idsecteur) {
			$APP_COMMANDE = new App('commande');
			$day          = date('Y-m-d');

			$rs_test_commande_secteur = $APP_COMMANDE->find(['idlivreur'    => ['$in' => ['',
			                                                                              0,
			                                                                              null]],
			                                                 'idsecteur'    => (int)$idsecteur,
			                                                 'dateCommande' => $day], ['_id' => 0])->sort(['rangCommande' => 1, 'slotCommande' => 1, 'heureCommande' => 1]);

			return iterator_to_array($rs_test_commande_secteur);

		}

		/**
		 * @deprecated CommandeQueue::secteur_commande_nonfree_list($idsecteur)
		 *
		 * @param        $idsecteur
		 * @param string $day
		 * @param string $now
		 *
		 * @return array
		 */
		function secteur_commande_queue($idsecteur, $day = '', $now = '') {
			$APP_COMMANDE = new App('commande');
			$day          = date('Y-m-d');
			$now          = date('H:i:s');

			$rs_test_commande_secteur = $APP_COMMANDE->find(['codeCommande_statut' => ['$nin' => ['PREFIN',
			                                                                                      'LIVENCOU',
			                                                                                      'END']],
			                                                 'idsecteur'           => (int)$idsecteur,
			                                                 'dateCommande'        => $day], ['_id' => 0])->sort(['heureCommande' => 1]);

			return iterator_to_array($rs_test_commande_secteur);

		}

		function secteur_commande_queue_all($idsecteur, $day = '', $now = '') {
			$APP_COMMANDE = new App('commande');
			$day          = date('Y-m-d');
			$now          = date('H:i:s');

			$rs_test_commande_secteur = $APP_COMMANDE->find(['codeCommande_statut' => ['$nin' => ['END']],
			                                                 'idsecteur'           => (int)$idsecteur,
			                                                 'dateCommande'        => $day], ['_id' => 0])->sort(['heureCommande' => 1]);

			return iterator_to_array($rs_test_commande_secteur);

		}

		function test_commande_shop_livencou($idshop, $day = '', $now = '') {
			$APP_COMMANDE = new App('commande');
			$day          = date('Y-m-d');
			$now          = date('H:i:s');

			$rs_test_commande_shop = $APP_COMMANDE->find(['codeCommande_statut' => 'LIVENCOU',
			                                              'idshop'              => $idshop,
			                                              'dateCommande'        => $day], ['_id' => 0]);

			return iterator_to_array($rs_test_commande_shop);

		}

		static function test_shop_open($idshop) {
			//  Shop
			$NOW            = date('H:i:s');
			$APP_SHOP       = new App('shop');
			$APP_SH_J       = new App('shop_jours');
			$APP_SH_J_SHIFT = new App('shop_jours_shift');

			$index_jour = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;

			$arr_shop = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$idshop   = (int)$arr_shop['idshop'];

			$arr_sh_j     = $APP_SH_J->findOne(['idshop'     => $idshop,
			                                    'ordreJours' => $index_jour]);
			$arr_sh_shift = iterator_to_array($APP_SH_J_SHIFT->find(['idshop'                     => $idshop,
			                                                         'idshop_jours'               => (int)$arr_sh_j['idshop_jours'],
			                                                         'actifShop_jours_shift'      => 1,
			                                                         'heureDebutShop_jours_shift' => ['$lte' => $NOW],
			                                                         'heureFinShop_jours_shift'   => ['$gte' => $NOW]], ['_id' => 0])->sort(['heureDebutShop_jours_shift' => 1]));
			if (sizeof($arr_sh_shift) != 0 && $arr_shop['actifShop'] == 1) {
				$parameters['heureDebutShop_jours_shift'] = maskHeure($arr_sh_shift[0]['heureDebutShop_jours_shift']);
				$parameters['heureFinShop_jours_shift']   = maskHeure($arr_sh_shift[0]['heureFinShop_jours_shift']);

				return true;
			} else {
				return false;
			}
		}

		/**
		 * @param array $array_vars
		 *
		 * @return array
		 * @throws \MongoConnectionException
		 * @throws \MongoCursorException
		 * @throws \MongoCursorTimeoutException
		 */
		function test_delivery_reserv($array_vars = []) {
			global $LATTE;

			$idcommande = (int)$array_vars['idcommande'];
			$idlivreur  = (int)$array_vars['idlivreur'];

			$APP_COMMANDE        = new App("commande");
			$APP_COMMANDE_STATUT = new App('commande_statut');
			$APP_LIVREUR         = new App('livreur');

			$arr_livreur  = $APP_LIVREUR->findOne(['idlivreur' => $idlivreur]);
			$arr_commande = $APP_COMMANDE->findOne(['idcommande' => $idcommande]);

			$idshop    = (int)$arr_commande['idshop'];
			$idsecteur = (int)$arr_commande['idsecteur'];

			$room_livreur = "livreur_$idlivreur";

			$commande_non_prefin       = CommandeQueue::shop_has_other_commandefree_non_prefin_list($idshop, $idcommande);
			$commande_non_prefin_count = $commande_non_prefin->count();
			if ($commande_non_prefin_count != 0) {
				$arr_test_first = array_values(iterator_to_array($commande_non_prefin))[0];

				return ['err' => 1,
				        'msg' => 'Merci de prendre la premiere commande disponible ' . $arr_test_first['referenceCommande']];
			}

			if (empty($arr_commande['idlivreur'])) {
				// nombre de commandes en cours !!
				$max_commande      = 1;
				$test_max_commande = $APP_COMMANDE->find(['dateCommande'        => date('Y-m-d'),
				                                          'idlivreur'           => $idlivreur,
				                                          'codeCommande_statut' => ['$nin' => ['END']]]);

				/*$test_max_commande_before = $APP_COMMANDE->find(['dateCommande'        => date('Y-m-d'),
				                                                 'idsecteur'           => $idsecteur,
				                                                 'idlivreur'           => ['$in' => ['',
				                                                                                     null,
				                                                                                     0]],
				                                                 'codeCommande_statut' => ['$nin' => ['END']]])->sort(['timeFinPreparationCommande' => 1]);*/

				//
				//$arr_test_first = $test_max_commande_before->getNext();
				/*				Helper::dump($test_max_commande->count());
								Helper::dump($arr_test_first);*/

				//if ($arr_test_first['idcommande'] != $idcommande) {
				//	NotifySite::notify_modal('Merci de prendre la premiere commande disponible '.$arr_test_first['referenceCommande'], 'error', null, $room_livreur);

				/*return ['err' => 1,
						'msg' => 'Merci de prendre la premiere commande disponible ' . $arr_test_first['referenceCommande']];*/
				//}
				if ($test_max_commande->count() >= $max_commande) {
					//	NotifySite::notify_idae('trop de commandes ou commande en cours', 'alert', null, $room_livreur);

					return ['err' => 1,
					        'msg' => 'trop de commandes ou commande actuellement en cours'];
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

				return ['err'        => 0,
				        'idcommande' => $idcommande];

			} else {
				$msg = "Un livreur est déja affecté à cette commande";

				return ['err' => 1,
				        'msg' => $msg];
			}
		}
	}
<?php

	/**
	 * Class CommandeQueueTimeFabric
	 * Trions les commandes par catégorie, en fonction de leurs temps:
	 * -Une commande avec 30mn d'attente = catégorie commande 30
	 * -Une commande avec 60 mn d'attente = catégorie commande 60
	 * -Etc...
	 * Il y a autant de commande de catégorie 30, 60,... qu'il y a de coursiers dispo (ex: 3 coursiers ce soir = 3 commandes catégorie 30, 3 commandes catégorie 60,...) et a chaque fois qu'une commande est livrée, la prochaine de catégorie 90 passe à catégorie 60, celle de 60 passe à 30, etc...
	 * Une commande tombe dans un shop: cette commande fait automatiquement monter le délai d'attente de ce shop de 30mn. Le temps des autres shop ne bouge pas, sauf si c'etait la dernière commande de sa catégorie. Si tel est le cas, le temps de chaque shop monte dans la catégorie suivante.
	 * Ex: Ce soir à 20h00, pour 2 coursiers (donc 2 commandes de catégorie 30, 2 de catégorie 60, 2 de 90, etc..) et 3 shops, La Malo, Le Jus , Le Thai:
	 * - La malo a 1 commande = 60mn d'attente (1ère commande catégorie 30)
	 * - Le Thai en a 2 = 90 mn (2ème de catégorie 30, 1ère de catégorie 60)
	 * - Le jus n'en a pas, mais comme les 2 coursiers ont déjà chacun une commande catégorie 30, le temps annoncé pour ce shop est de 60mn
	 * - Il reste 1 commande de catégorie 60 à prendre, elle peut être prise au Jus ou à la Malo (Si elle est prise au Thai, c'est la 1ère de catégorie 90)).
	 * - Qu'elle soit prise à la Malo ou au Jus, les 2 afficherons 90mn puisque c'était la dernière de catégorie 60.
	 * Pour le délai affiché aux clients sur le site, les commandes de catégorie 30 ne bougent jamais en temps réel. Par contre, le  délai des commandes suivantes est indexé sur le temps réel.
	 * Pour 1 shop:
	 * 1 commande = 30mn.
	 * 2 commandes = 30mn + 30mn - le temps passé entre la commande 1 et 2 = X. X est donc variable en fonction du temps réel passé.
	 * 3 commandes = X + 30mn
	 * max_command_rang = max_command_shop_rang < max_command_secteur
	 * command_shop_rang (nb_livreur_disp % nb_cmande_queue)
	 * tps = (max_command_shop_rang + 1) * TEMP_PREP_LIV_COMMANDE
	 * tps = (si nb_liv_disp_secteur > nb_commandes_secteur ) => (last_commande_shop_heure_livraison - NOW) + TEMP_PREP_LIV_COMMANDE sinon max_command_shop_rang + TEMP_PREP_LIV_COMMANDE
	 * exemple :
	 * Pour une commande classée R1 après le filtrage shop
	 * - si un coursier au moins n'as pas de commande R1, C=R1 => R secteur =  nombre_commande * nb_livreurs , ex 1 * 2 = 2
	 * - si tous les coursiers ont déjà une R1, C=R2
	 * - si tous les coursiers ont déjà une R2, C=R3
	 * -etc
	 */
	class CommandeQueueTimeFabric {

		static public function get_times_config() {
			$current = [];

			$current['DUREE_REALISATION_COMMANDE'] = DUREE_REALISATION_COMMANDE;
			$current['TIME_PREPARATION_COMMANDE']  = TIME_PREPARATION_COMMANDE;
			$current['TEMPS_LIVRAISON_COMMANDE']   = TEMPS_LIVRAISON_COMMANDE;

			return $current;
		}

		static public function get_times_secteur_livreur($idsecteur) {
			$current = [];

			$current['LIVREUR_WORKING_NB']          = CommandeQueue::secteur_has_livreur_count($idsecteur);
			$current['LIVREUR_DISPONIBLE_NB']       = CommandeQueue::secteur_has_livreur_free_count($idsecteur);
			$current['LIVREUR_WAITING_COMMANDE_NB'] = CommandeQueue::secteur_has_livreur_waiting_count($idsecteur);

			$pluriel                = ($current['LIVREUR_WORKING_NB'] > 1) ? 's' : '';
			$msg                    = $current['LIVREUR_WORKING_NB'] . " coursier$pluriel disponible$pluriel actuellement";
			$current['LIVREUR_MSG'] = ($current['LIVREUR_WORKING_NB'] == 0) ? "Aucun coursier n'est disponible actuellement" : $msg;

			return $current;
		}

		static public function get_times_shop($idshop) {
			$shop         = new IdaeDataDB('shop');
			$ARR_SHOP     = $shop->findOne(['idshop' => $idshop]);
			$idsecteur    = (int)$ARR_SHOP['idsecteur'];
			$CommandeSlot = new CommandeSlot($idsecteur);
			$CommandeSlot->distribute($idsecteur);
			$test_shop_open = Bin::test_shop_open($idshop);
			$current        = [];

			$NB_LIVREUR                        = CommandeQueue::secteur_has_livreur_count($idsecteur);
			$ARR_COMMAND_SHOP_LAST             = CommandeQueue::shop_commande_queue_last($idshop);
			$ARR_COMMAND_SHOP_UNDELIVERED_LAST = CommandeQueue::shop_commande_queue_undelivered_last_elemnt($idshop);
			$COMMAND_SHOP_WAITING_NB           = CommandeQueue::shop_commande_queue_count($idshop);
			$COMMAND_SHOP_SHIFT_NB             = CommandeQueue::shop_commande_queue_shift_count($idshop);
			$COMMAND_SHOP_NEXT_SLOT            = str_replace('R', '', $CommandeSlot->get_next_slot_shop($idshop));

			$COMMAND_SHOP_NEXT_SLOT_MINUTES  = TEMPS_LIVRAISON_COMMANDE + ($COMMAND_SHOP_NEXT_SLOT * TIME_PREPARATION_COMMANDE);
			$COMMAND_SHOP_NEXT_SLOT_SECONDES = $COMMAND_SHOP_NEXT_SLOT_MINUTES * 60;
			$COMMAND_SHOP_RANG               = $ARR_COMMAND_SHOP_LAST['rangCommande'] ?: "S0";

			$COMMAND_SHOP_UNDELIVERED_LAST_ORDER = $ARR_COMMAND_SHOP_UNDELIVERED_LAST['ordreSecteurCommande'];

			$COMMAND_SHOP_FINAL_DELIVERY_DELAY_RANGE = self::str_delay_range($COMMAND_SHOP_NEXT_SLOT_SECONDES);
			$COMMAND_SHOP_FINAL_DELIVERY_DATETIME    = date('H:i', ($COMMAND_SHOP_NEXT_SLOT_SECONDES + time()));

			$timeCreationCommande         = strtotime($ARR_COMMAND_SHOP_LAST['dateCreationCommande'] . ' ' . $ARR_COMMAND_SHOP_LAST['heureCreationCommande']);
			$COMMAND_SHOP_NEXT_SLOT_DELAY = ceil((($timeCreationCommande + $COMMAND_SHOP_NEXT_SLOT_SECONDES) - time()) / 60);

			$current['COMMAND_SHOP_NEXT_SLOT']         = "R$COMMAND_SHOP_NEXT_SLOT";
			$current['COMMAND_SHOP_NEXT_SLOT_MINUTES'] = $COMMAND_SHOP_NEXT_SLOT_MINUTES;
			$current['COMMAND_SHOP_RANG']              = $COMMAND_SHOP_RANG;
			$current['COMMAND_SHOP_SHIFT_NB']          = $COMMAND_SHOP_SHIFT_NB;
			$current['COMMAND_SHOP_WAITING_NB']        = $COMMAND_SHOP_WAITING_NB;

			$current['COMMAND_SHOP_NEXT_SLOT_DYNAMIC_DELAY']    = $COMMAND_SHOP_NEXT_SLOT_DELAY;
			$current['COMMAND_SHOP_FINAL_DELIVERY_DELAY_RANGE'] = (!$test_shop_open || ($COMMAND_SHOP_SHIFT_NB >= NB_MAX_COMMANDE_SHOP)) ? '' : $COMMAND_SHOP_FINAL_DELIVERY_DELAY_RANGE . ' - R' . $COMMAND_SHOP_NEXT_SLOT . ' ' . $COMMAND_SHOP_SHIFT_NB . ' cmd(s)';
			$current['COMMAND_SHOP_FINAL_DELIVERY_DATETIME']    = (!$test_shop_open || ($COMMAND_SHOP_SHIFT_NB >= NB_MAX_COMMANDE_SHOP)) ? '' : $COMMAND_SHOP_FINAL_DELIVERY_DATETIME;
			$current['COMMAND_SHOP_FINAL_DELIVERY_THUMB_STATE'] = self::get_time_livraison_thumb(($test_shop_open) ? $COMMAND_SHOP_NEXT_SLOT : 0);

			$current['COMMAND_SHOP_MSG'] = ($COMMAND_SHOP_SHIFT_NB >= NB_MAX_COMMANDE_SHOP) ? "Actuellement ne prend plus de nouvelle commande" : '';
			$current['COMMAND_SHOP_MSG'] = (!$test_shop_open) ? "Actuellement fermé" : $current['COMMAND_SHOP_MSG'];

			$index = TEMPS_LIVRAISON_COMMANDE + TIME_PREPARATION_COMMANDE * ceil($NB_LIVREUR / ($COMMAND_SHOP_UNDELIVERED_LAST_ORDER + 1));
			$index = $COMMAND_SHOP_UNDELIVERED_LAST_ORDER / $NB_LIVREUR;

			$current['COMMAND_SHOP_TIME'] = $COMMAND_SHOP_UNDELIVERED_LAST_ORDER . ' => ' . $index . ' ' . ceil($index);

			return $current;
		}

		static private function get_time_livraison_thumb($COMMAND_SHOP_SLOT) {


			return '<i class="fa fa-circle slot_R' . $COMMAND_SHOP_SLOT . '"></i>';
		}

		static private function get_time_livraison_time($time) {
			if (empty($time)) return time();
			if ($time < time()) return time() + TEMPS_LIVRAISON_COMMANDE;

			return $time;
		}

		static public function get_times_secteur($idsecteur) {
			$current = [];

			$ARR_COMMAND_SECTEUR_LAST                  = CommandeQueue::secteur_commande_queue_list_last($idsecteur);
			$current['COMMAND_SECTEUR_NB']             = CommandeQueue::secteur_commande_queue_count($idsecteur);
			$current['COMMAND_SECTEUR_UNASSIGNED_NB']  = CommandeQueue::secteur_commande_free_count($idsecteur); // sans livreur
			$current['COMMAND_SECTEUR_UNDELIVERED_NB'] = CommandeQueue::secteur_commande_nonfree_count($idsecteur); // before prefin, avec ou sans livreur

			$timeLivraisonCommande = $ARR_COMMAND_SECTEUR_LAST['timeLivraisonCommande'];

			if (empty($timeLivraisonCommande)) {
				$timeLivraisonCommande          = time();
				$DELAY_TIME_END_COMMAND_SECTEUR = TEMPS_LIVRAISON_COMMANDE * 60;

			} else if ($timeLivraisonCommande < time()) {
				$timeLivraisonCommande          = time() + (TEMPS_LIVRAISON_COMMANDE * 60);
				$DELAY_TIME_END_COMMAND_SECTEUR = TEMPS_LIVRAISON_COMMANDE * 60;

			} else {
				$DELAY_TIME_END_COMMAND_SECTEUR = $timeLivraisonCommande - time();

			}

			$current['COMMAND_SECTEUR_LAST_DELIVERY_HOUR']          = date('H:i', $timeLivraisonCommande);
			$current['COMMAND_SECTEUR_LAST_DELIVERY_HOUR_DELAY']    = self::str_delay($DELAY_TIME_END_COMMAND_SECTEUR);
			$current['COMMAND_SECTEUR_LAST_DELIVERY_MINUTES_DELAY'] = ceil($DELAY_TIME_END_COMMAND_SECTEUR / 60);
			$current['COMMAND_SECTEUR_LAST_DELIVERY_THUMB_STATE']   = self::get_time_livraison_thumb($current['COMMAND_SECTEUR_LAST_DELIVERY_MINUTES_DELAY']);

			return $current;
		}

		static private function str_delay($secondeLivraison) {
			$heures      = (int)($secondeLivraison / 3600);
			$minutes     = (int)(($secondeLivraison % 3600) / 60);
			$str_hours   = empty($heures) ? '' : $heures . ' hr';
			$str_hours   .= ($heures > 1) ? 's' : '';
			$str_minutes = ($minutes == 0) ? '' : ' ' . $minutes . ' mn';

			return $str_hours . $str_minutes;
		}

		static private function str_delay_range($secondeLivraison) {
			$minutes = ceil($secondeLivraison / 60);

			$p               = pow(5, 1);
			$minutes_round_2 = ceil($minutes / $p) * $p;

			$str = ($minutes_round_2 - 5) . ' - ' . ($minutes_round_2 + 5);

			return "$str mins";
		}
	}


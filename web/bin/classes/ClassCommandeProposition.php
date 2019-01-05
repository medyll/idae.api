<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 20/06/2018
	 * Time: 11:08
	 */
	class CommandeProposition {

		static public function livreur_has_proposition_before_commande($idlivreur, $idcommande) {
			$APP_COMMANDE             = new App('commande');
			$APP_COMMANDE_PROPOSITION = new App('commande_proposition');

			$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => (int)$idcommande]);

			$TEST_COMMANDE_PROPOSITION = $APP_COMMANDE_PROPOSITION->find(['idlivreur'                              => (int)$idlivreur,
			                                                              'idsecteur'                              => (int)$ARR_COMMANDE['idsecteur'],
			                                                              'livreur_take'                           => ['$ne' => (int)$idlivreur],
			                                                              'dateCommande_proposition'               => date('Y-m-d'),
			                                                              'endedCommande_proposition'              => ['$ne' => 1],
			                                                              'timeFinPreparationCommande_proposition' => ['$lt' => $ARR_COMMANDE['timeFinPreparationCommande']],
			                                                              'actifCommande_proposition'              => 1])->sort(['timeFinPreparationCommande_proposition' => 1]);

			return iterator_to_array($TEST_COMMANDE_PROPOSITION);
		}

		static public function livreur_last_commande_seen_still_active($idlivreur) {

			$APP_COMMANDE_PROPOSITION = new App('commande_proposition');

			$arr_find = ['dateCommande_proposition'  => date('Y-m-d'),
			             'idlivreur'                 => (int)$idlivreur,
			             'actifCommande_proposition' => 1,
			             'vuCommande_proposition'    => 1,];

			$ARR_COMMANDE = $APP_COMMANDE_PROPOSITION->findOne($arr_find);

			return $ARR_COMMANDE;
		}
	}
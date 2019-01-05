<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 03/07/2018
	 * Time: 19:17
	 */

	class ClassRangCommande {

		static public function updateRangShopCommandes($idshop) {
			$day = date('Y-m-d');

			$APP_COMMANDE        = new IdaeDataDB('commande');
			$APP_COMMANDE_STATUT = new IdaeDataDB('commande_statut');
			$APP_SHOP            = new IdaeDataDB('shop');

			$arr_shop            = $APP_SHOP->findOne(['idshop' => (int)$idshop]);
			$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'END']);

			$list = $APP_COMMANDE->find(['ordreCommande_statut' => ['$lt' => $arr_commande_statut['ordreCommande_statut']], 'idshop' => (int)$idshop, 'dateCommande' => $day])->sort(['heureCommande' => 1]);

			$nb_livreur_disp = CommandeQueue::secteur_has_livreur_count($arr_shop['idsecteur']);
			$i               = 0;
			$rang            = 1;

			$index_turn = 0;
			foreach ($list as $index => $item) {
				if ($i >= $nb_livreur_disp) {
					$i = 0;
					++$rang;
				}
				$heureCommande =  $item['heureCommande'];
				++$i;
				++$index_turn;

				$idcommande = (int)$item['idcommande'];

				$arr_upd['idcommande']                 = $idcommande;
				$arr_upd['ordreCommande']              = $index_turn;
				$arr_upd['debugRangCommandeModulo']    = $index_turn;
				$arr_upd['debugRangCommande']          = "S$index_turn";
				$arr_upd['rangCommande']               = "S$index_turn";
				$arr_upd['attentePreparationCommande'] = $rang;

				$APP_COMMANDE->update_id($idcommande, $arr_upd);
			}
		}
	}
<?php

	class AppCharges {

		private static function tactac_charge($price_brut) {
			//	6% du montant
			return ($price_brut * 0.06);
		}

		private static function stripe_charge($price_brut) {
			//	1,4% du montant total + 0,25€
			return 0;
			//return ($price_brut * 0.014) + 0.25;
		}

		/**
		 * @param $total_commande_client
		 * @param $total_livraison_commande
		 *
		 * @return array
		 */
		public static function get_commandeParts($total_commande_client, $total_livraison_commande) {
			/*$total_commande_client    = 26;
			$total_livraison_commande = 3;*/
			$total_price_brut = $total_commande_client + $total_livraison_commande;
			$stripe_charge    = self::stripe_charge($total_price_brut);
			$total_price_net  = ($total_price_brut) - $stripe_charge;
			$toshare          = $total_price_net - self::tactac_charge($total_price_net);
			$toshare_shop     = $toshare - $total_livraison_commande;
			$toshare_agent    = $total_livraison_commande;
			$gain             = ($total_price_net - $toshare);

			$return = ['total_commande_client'       => $total_commande_client,
			           'total_livraison_commande'    => $total_livraison_commande,
			           'margeCommande_facture'       => round($gain, 3),
			           'totalCommande_facture'       => round($toshare, 3),
			           'partShopCommande_facture'    => round($toshare_shop, 3),
			           'partLivreurCommande_facture' => round($toshare_agent, 3)];

			return $return;

		}

	}
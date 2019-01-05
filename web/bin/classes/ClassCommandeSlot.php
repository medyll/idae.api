<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 03/07/2018
	 * Time: 19:52
	 */

	class CommandeSlot {

		private $idsecteur;
		private $nb_livreur_disp;
		private $list_open_shops = [];
		private $slot_arr        = [];

		public function __construct($idsecteur) {
			$init = new IdaeDataSchemeInit();
			$init->init_scheme('sitebase_base', 'commande_slot', ['fields' => ['nom', 'code']]);
			$init->init_scheme('sitebase_base', 'commande_slot_ligne', ['fields' => ['nom', 'code']]);

			$this->idsecteur       = $idsecteur;
			$this->nb_livreur_disp = CommandeQueue::secteur_has_livreur_count($this->idsecteur);
			$this->build_shop_list();
			$this->build_slot();
			$this->feed_slot();
		}

		private function build_shop_list() {
			$RS                    = Bin::get_shop_secteur_shift_current($this->idsecteur);
			$this->list_open_shops = iterator_to_array($RS);
		}

		private function build_slot() {
			$i = 0;
			while ($i <= NB_MAX_COMMANDE_SHOP) {
				++$i;
				$this->slot_arr["R$i"] = [];
			}
		}

		private function feed_slot() {
			$idsecteur    = $this->idsecteur;
			$day          = date('Y-m-d');
			$APP_COMMANDE = new IdaeDataDB('commande');
			$RS           = $APP_COMMANDE->find(['codeCommande_statut' => ['$nin' => ['END']], 'idsecteur' => (int)$idsecteur, 'dateCommande' => $day])->sort(['heureCommande' => 1]);

			while ($ARR = $RS->getNext()) {
				$this->set_slot_commande($ARR);
			}

			$this->clean($idsecteur);
		}

		private function set_slot_commande($ARR) {
			$ordreCommande = $ARR['ordreCommande'];

			foreach ($this->slot_arr as $index => $slot_content) {
				$rangSlot = (int)str_replace('R', '', $index);
				if ($ordreCommande > $rangSlot) continue;
				if (sizeof($slot_content) >= $this->nb_livreur_disp) continue;
				$this->slot_arr[$index][] = $ARR;

				return $index;
			}
		}

		private function find_idcommande_in_slot($idcommande, $haystack = []) {

			if (empty($haystack)) $haystack = $this->slot_arr;

			foreach ($haystack as $key => $value) {
				$current_key = $key;
				if ($idcommande == $value || (is_array($value) && $this->find_idcommande_in_slot($idcommande, $value) !== false)) {
					return $current_key;
				}
			}

			return false;
		}

		public function get_next_slot_shop($idshop) {
			$ARR = CommandeQueue::shop_commande_queue_last($idshop);

			$ordreCommande = $ARR['ordreCommande'] + 1;
			$slot_arr      = $this->slot_arr;
			foreach ($slot_arr as $index => $slot_content) {
				$rangSlot = (int)str_replace('R', '', $index);
				if ($ordreCommande > $rangSlot) continue;
				if (sizeof($slot_content) >= $this->nb_livreur_disp) continue;
				$slot_arr[$index][] = $ARR;

				return "$index";
			}
		}

		public function draw_debug() {
			$this->draw_slot();
			echo "<hr>";
			$this->draw_slot_shop();
		}

		public function draw_slot() {
			$out      = '<table>';
			$slot_arr = array_reverse($this->slot_arr);
			foreach ($slot_arr as $rangSlot => $slot_content) {
				$out .= '<tr>';
				$out .= '<td>' . $rangSlot . '</td>';
				$out .= '<td>[</td>';
				foreach ($slot_content as $index => $ARR) {
					$out .= '<td>' . $ARR['rangCommande'] . ' ' . $ARR['referenceCommande'] . ' ' . $ARR['codeShop'] . '</td>';
				}
				$out .= '<td>]</td>';
				$out .= '</tr>';
			}

			$out .= '</table>';
			echo $out;
		}

		public function draw_slot_shop() {
			$out = '<table>';

			$DB = new IdaeDataDB('shop');
			$rs = $DB->find(['actifShop' => 1, 'idsecteur' => $this->idsecteur]);

			while ($arr = $rs->getNext()) {

				$idshop = (int)$arr['idshop'];
				$slot   = $this->get_next_slot_shop($idshop);
				$shop   = $arr['nomShop'];
				$out    .= '<tr>';
				$out    .= "<td>$shop</td>";
				$out    .= "<td> next </td>";
				$out    .= "<td>$slot</td>";
				$out    .= '</tr>';
			}

			$out .= '</table>';
			echo $out;
		}

		public function distribute() { // 59831
			$idsecteur    = $this->idsecteur;
			$APP_COMMANDE = new IdaeDataDB('commande');
			$day          = date('Y-m-d');

			$RS              = $APP_COMMANDE->find(['codeCommande_statut' => ['$nin' => ['END']], 'idsecteur' => (int)$idsecteur, 'dateCommande' => $day])->sort(['heureCommande' => 1]);
			$nb_livreur_disp = CommandeQueue::secteur_has_livreur_count($idsecteur);

			$DIST_SHOP = $APP_COMMANDE->distinct('idshop', ['codeCommande_statut' => ['$nin' => ['END']], 'idsecteur' => (int)$idsecteur, 'dateCommande' => $day]);
			foreach ($DIST_SHOP as $index => $value) {
				ClassRangCommande::updateRangShopCommandes($value);
			}

			$tot_sect = 0;
			while ($ARR = $RS->getNext()) {
				++$tot_sect;
				$idcommande   = (int)$ARR['idcommande'];
				$slotCommande = $this->find_idcommande_in_slot($idcommande);
				//$slotCommande = $this->set_slot_commande($ARR);
				$APP_COMMANDE->update_id($idcommande, ['slotCommande' => $slotCommande, 'ordreSecteurCommande' => $tot_sect, 'internalSlotCommande' => ceil($tot_sect / $nb_livreur_disp)]);
			}

			$this->clean($idsecteur);
		}

		private function clean($idsecteur) {

			$DB_COMMANDE = new IdaeDataDB('commande');
			$RS          = CommandeQueue::secteur_commande_queue_ended_list($idsecteur);
			while ($ARR = $RS->getNext()) {
				$idcommande = (int)$ARR['idcommande'];
				if (!empty($ARR['heureFinCommande']) && empty($ARR['tempsTotalCommande'])) {
					$timeCreationCommande = strtotime($ARR['dateCommande'] . ' ' . $ARR['heureCommande']);
					$timeFinCommande      = strtotime($ARR['dateCommande'] . ' ' . $ARR['heureFinCommande']);
					$total                = (int)ceil(($timeFinCommande - $timeCreationCommande) / 60);
					$DB_COMMANDE->update_id((int)$idcommande, ['tempsTotalCommande' => $total]);
				}
				$slotCommande = "";
				$DB_COMMANDE->update_id((int)$idcommande, ['slotCommande' => $slotCommande, 'rangCommande' => '', 'ordreSecteurCommande' => '']);
			}
		}

	}
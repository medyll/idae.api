<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 16/08/2017
	 * Time: 14:59
	 */
	class CartLivraison extends App {

		protected $cart_idshop;
		protected $cart_id;
		protected $cart_arr;
		protected $cart_lines;
		protected $cart_adresse;

		protected $cart_allowed_keys = ['cart_id', 'nomCart', 'init_time', 'cart_adresse', 'cart_lines'];

		function __construct($cart_sess = '') {
			parent::__construct();
			$this->APP_CART_LIVRAISON     = new App('cart_livraison'); 

			$this->cart_id = empty($cart_sess)? session_id() : $cart_sess;
			//
			$this->cart_arr = $this->get_cart();

		}

		function get_cart() {

			$var_cart = $this->APP_CART_LIVRAISON->findOne(['cart_id' => $this->cart_id]);
			if (empty($var_cart['cart_id'])) $this->APP_CART_LIVRAISON->insert(['cart_id' => $this->cart_id, 'nomCart' => $this->cart_id, 'init_time' => time(), 'cart_adresse' => [], 'cart_lines' => []]);
			$var_cart = $this->APP_CART_LIVRAISON->findOne(['cart_id' => $this->cart_id], ['_id' => 0]);

			$this->cart_lines   = $var_cart['cart_lines'];
			$this->cart_adresse = $var_cart['cart_adresse'];

			$this->cart_arr = $var_cart;

			return $var_cart;
		}

		function do_action($params = ['action', 'value']) {
			//

			if (strpos($params['value'], '/') === false) {
				$this->$params['action']($params['value']);
			} else {
				$this->$params['action'](explode('/', $params['value']));
			}

		}

		function update_meta($arr_meta=[]) {
			$post = (sizeof($arr_meta)==0)? $_POST : $arr_meta;
			foreach ($_POST as $key => $value) {
				if (in_array($key, $this->cart_allowed_keys)) {
					$this->$key = $value;
				}
				$this->update_cart();
			}
			// Helper::dump($this->get_cart());
			// Helper::dump([array_keys($arr_meta)[0] , array_values($arr_meta)[0]]);
			// $this->array_keys($arr_meta)[0] = array_values($arr_meta)[0];
		}

		function update_cart() {

			$tot     = 0;
			$tot_vol = 0;
			$duree   = 0;
			foreach ($this->cart_lines as $key => $value) {
				$tot += $value['total'];
				$tot_vol += (float)$value['id']['volumeProduit'] * $value['qte'];
				if ($value['id']['duree_realisationProduit'] > $duree) $duree = $value['id']['duree_realisationProduit'];
			}
			//
			$insert_fields                      = [];
			$insert_fields['last_time']         = time();
			$insert_fields['cart_lines']        = $this->cart_lines;
			$insert_fields['cart_total']        = $tot;
			$insert_fields['cart_total_volume'] = $tot_vol;
			$insert_fields['cart_total_time']   = $duree;
			$insert_fields['cart_adresse']      = $this->cart_adresse;
			//
			if ($insert_fields['cart_total_volume'] > 100 && $this->cart_arr['cart_total_volume'] < $insert_fields['cart_total_volume']) {
				AppSocket::send_cmd('act_script', ['script'    => 'cart_notify',
				                                   'arguments' => ['msg' => 'enregistrement impossible, volume dépassé', 'type' => 'error'],
				                                   'options'   => []], $this->cart_id);
			} else {
				$this->APP_CART_LIVRAISON->update(['cart_id' => $this->cart_id], $insert_fields);
			}
			//
			$this->json_export();
			/*AppSocket::run('act_run', ['route'  => 'demo/dump/apoil:vert/aussi:rouge',
			                           'method' => 'POST',
			                           'delay'  => 10,
			                           'vars'   => ['idoine' => 'red']]);*/
		}

		function json_export() {
			$cart = json_encode($this->get_cart());
			AppSocket::send_cmd('act_script', ['script'    => 'cart_update_json',
			                                   'arguments' => $cart,
			                                   'options'   => []], $this->cart_id);
			// echo $cart;
		}

		function delete_adresse() {
			$this->cart_adresse = [];
			$this->update_cart();
		}

		function add_item($item_id) {
			# produit
			$item     = $this->get_produit($item_id);
			$arr_shop = $this->get_shop((int)$item['idshop']);

			# verif
			if (empty($this->cart_arr['idshop']) && !empty($arr_shop['idshop'])) {
				$this->APP_CART_LIVRAISON->update(['cart_id' => $this->cart_id], ['idshop' => (int)$item['idshop'], 'idsecteur' => (int)$arr_shop['idsecteur'], 'shop' => $arr_shop]);
			} else { 
			 
			}

			if (!empty($this->cart_lines["prod_$item_id"]['id']) && !empty($this->cart_lines["prod_$item_id"]['qte'])) {
				$this->cart_lines["prod_$item_id"]['qte']++;
				$this->cart_lines["prod_$item_id"]['total'] = $this->cart_lines["prod_$item_id"]['qte'] * $item['prix_siteProduit'];
			} else {
				$this->cart_lines["prod_$item_id"]['id']     = $item;
				$this->cart_lines["prod_$item_id"]['qte']    = 1;
				$this->cart_lines["prod_$item_id"]['total'] = $item['prix_siteProduit'];
				$this->cart_lines["prod_$item_id"]['idshop'] = (int)$item['idshop'];
			}

			$this->update_cart();
		}

		function get_produit() {
			return '';

		}

		function get_shop($id) {
			$allowed_c = ['idshop' => 1, 'nomShop' => 1, 'codeShop' => 1, 'slugShop' => 1, 'idsecteur' => 1, '_id' => 0];
			$arr       = $this->APP_SHOP->findOne(['idshop' => (int)$id], $allowed_c);;
			unset($arr['_id']);

			return $arr;

		}

		function update_cart_line($vars = ['prod_id' => 'test', 'qte']) {

			if ($vars[1] == 0) {
				unset($this->cart_lines[$vars[0]]);
			} else {
				$this->cart_lines[$vars[0]]['qte']   = $vars[1];
				$this->cart_lines[$vars[0]]['total'] = number_format($this->cart_lines[$vars[0]]['id']['prix_siteProduit'] * $vars[1], 2, '.', ' ');

			}

			$this->update_cart();
		}

		function remove_item() {
			$this->update_cart();
		}

		function calcul_total() {

		}

		function empty_cart($all = 'none') {
			if ($all !== 'all') return;
			$this->cart_lines = [];
			$this->update_cart();
		}
	}
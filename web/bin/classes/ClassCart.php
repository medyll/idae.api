<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 16/08/2017
	 * Time: 14:59
	 */
	class Cart extends App {

		protected $cart_idshop;
		protected $idshop;
		protected $idsecteur;
		protected $cart_id;
		protected $cart_arr;
		protected $cart_lines;
		protected $cart_adresse;
		protected $cart_room;

		protected $cart_allowed_keys = ['cart_id',
		                                'nomCart',
		                                'init_time',
		                                'idsecteur',
		                                'cart_adresse',
		                                'cart_lines'];

		function __construct($cart_sess = '') {
			parent::__construct();
			$this->APP_CART     = new App('cart');
			$this->APP_PROD     = new App('produit');
			$this->APP_PROD_CAT = new App('produit_categorie');
			$this->APP_SHOP     = new App('shop');

			$this->cart_id   = empty($cart_sess) ? session_id() : $cart_sess;
			$this->cart_room = 'client_' . $this->cart_id;
			$this->init_cart();
			$this->cart_arr = $this->get_cart();

			# join socket pour publication
			AppSocket::send_grantIn(['room' => $this->cart_room]); // room personnelle de type shop_7
		}

		function get_cart() {
			$var_cart = $this->APP_CART->findOne(['cart_id' => $this->cart_id], ['_id' => 0]);

			//$this->idshop       = $var_cart['idshop'];
			//$this->idsecteur    = $var_cart['idsecteur'];
			//$this->cart_lines   = $var_cart['cart_lines'];
			//$this->cart_adresse = $var_cart['cart_adresse'];

			return $var_cart;
		}

		function do_action($params = ['action',
		                              'value']) {
			//
			if (strpos($params['value'], '/') === false) {
				$this->$params['action']($params['value']);
			} else {
				$this->$params['action'](explode('/', $params['value']));
			}

		}

		function update_meta($arr_meta = []) {
			$post = (sizeof($arr_meta) == 0) ? $_POST : $arr_meta;
			Helper::dump($post);
			foreach ($post as $key => $value) {
				Helper::dump($value);

				if (in_array($key, $this->cart_allowed_keys) && !empty($value)) {
					$this->$value = $post[$value];
				}
				$this->update_cart();
			}
		}

		private function init_cart() {
			$var_cart = $this->APP_CART->findOne(['cart_id' => $this->cart_id]);
			if (empty($var_cart['cart_id'])) $this->APP_CART->insert(['cart_id'      => $this->cart_id,
			                                                          'nomCart'      => $this->cart_id,
			                                                          'init_time'    => time(),
			                                                          'cart_adresse' => [],
			                                                          'cart_lines'   => []]);

		}

		function init_adress() {
			$this->empty_cart('all');
			/*$this->cart_adresse = $_POST['cart_adresse'];
			$this->idsecteur    = (int)$_POST['idsecteur'];
			$this->idshop       = null;*/

			$this->APP_CART->update_native(['cart_id' => $this->cart_id], ['init_time'    => time(),
			                                                               'cart_adresse' => $_POST['cart_adresse'],
			                                                               'idsecteur'    => (int)$_POST['idsecteur'],
			                                                               'idshop'       => null,
			                                                               'cart_lines'   => []]);

		}

		function validate_cart() {
			$err      = 0;
			$json_msg = ['err'       => $err,
			             'msg'       => '',
			             'data_html' => ''];
			$cart_arr = $this->get_cart();
			if (!Bin::test_shop_open((int)$cart_arr['idshop'])) {
				NotifySite::notify_modal("Le restaurant est actuellement fermé !!", 'alert', [], $this->cart_room);
				$err = 1;
			}
			if ($cart_arr['idsecteur'] != $cart_arr['shop']['idsecteur']) {
				NotifySite::notify_modal("Vous n'êtes pas situé sur le bon secteur !!", 'alert', [], $this->cart_room);
				$err = 1;
			}
			$BIN              = new Bin();
			$nb_commande_wait = CommandeQueue::shop_commande_queue_count($cart_arr['idshop']);//  $BIN->shop_commande_queue((int)$cart_arr['idshop']);

			if ($nb_commande_wait > NB_MAX_COMMANDE_SHOP) {
				NotifySite::notify_modal("Ce restaurant a trop de commandes en cours", 'alert', [], $this->cart_room);
				$err = 1;
			}

			$json_msg = ['err'       => $err,
			             'msg'       => '',
			             'data_html' => ''];

			echo json_encode($json_msg, JSON_FORCE_OBJECT);
		}

		function reload_cart() {
			$cart_arr = $this->APP_CART->findOne(['cart_id' => $this->cart_id], ['_id' => 0]);
			$cart_arr = json_decode(json_encode($cart_arr, JSON_FORCE_OBJECT), false);
		}

		function update_cart() {

			//$this->reload_cart();
			$cart_arr = $this->get_cart();
			$tot      = 0;
			$tot_vol  = 0;
			$duree    = 0;
			//
			$insert_fields              = [];
			$insert_fields['last_time'] = time();
			if (!empty($cart_arr['cart_lines'])) {
				foreach ($cart_arr['cart_lines'] as $key => $value) {
					$tot += $value['total'];
					$tot_vol += (float)$value['id']['volumeProduit'] * $value['qte'];
					if ($value['id']['duree_realisationProduit'] > $duree) $duree = $value['id']['duree_realisationProduit'];
				}
				// $insert_fields['cart_lines'] = $this->cart_lines;

			}
			// pas glop
			if ($tot == 0) {
				$final_total   = 0;
				$final_sstotal = 0;
			} else if ($tot < 15) {
				$final_total   = $tot + 6;
				$final_sstotal = 6;
			} else if ($tot < 35) {
				$final_total   = $tot + 3;
				$final_sstotal = 3;
			} else {
				$final_total   = $tot;
				$final_sstotal = 0;
			}
			$insert_fields['cart_total']        = (float)$final_total;
			$insert_fields['cart_sous_total']   = (float)$final_sstotal;
			$insert_fields['cart_total_volume'] = $tot_vol;
			$insert_fields['cart_total_time']   = 30;//$duree;
			//$insert_fields['cart_adresse']      = $this->cart_adresse;
			//$insert_fields['idsecteur']         = (int)$this->idsecteur;
			//
			$this->APP_CART->update_native(['cart_id' => $this->cart_id], $insert_fields);
			/*if ($insert_fields['cart_total_volume'] > 10 && $cart_arr['cart_total_volume'] < $insert_fields['cart_total_volume']) {

				AppSocket::send_cmd('act_script', ['script'    => 'cart_notify',
				                                   'arguments' => ['msg'  => 'enregistrement impossible, volume dépassé',
				                                                   'type' => 'error'],
				                                   'options'   => ['sticky' => 1]], $this->cart_id);
			} else {

			}*/
			$this->json_export();
		}

		function update_native($insert_fields) {
			$this->APP_CART->update_native(['cart_id' => $this->cart_id], $insert_fields);
		}

		function json_export() {
			$cart = json_encode($this->get_cart());

			AppSocket::send_cmd('act_script', ['script'    => 'cart_update_json',
			                                   'arguments' => $cart,
			                                   'options'   => []], $this->cart_id);
			// echo $cart;
		}

		function set_adresse($arr_meta = []) {
			if (!isset($arr_meta)) return false;
			$this->cart_adresse = $arr_meta;
			$this->update_native(['cart_adresse' => $arr_meta]);
		}

		function delete_adresse() {
			$this->set_adresse([]);
			/*$this->cart_adresse = [];
			$this->update_native(['cart_id'=>$this->cart_id],['cart_adresse'=> []]);*/
		}

		function add_item($item_id) {
			# produit
			$cart_arr   = $this->get_cart();
			$item       = $this->get_produit((int)$item_id);
			$arr_shop   = $this->get_shop((int)$item['idshop']);
			$cart_lines = $cart_arr['cart_lines'];
			if (empty($arr_shop['actifShop'])) {
				AppSocket::send_cmd('act_notify', ['msg' => "Ce restaurant est fermé !!"], $this->cart_room);

				return;
			}
			if (!Bin::test_shop_open((int)$item['idshop'])) {
				NotifySite::notify_modal("Le restaurant est actuellement fermé !!", 'alert', [], $this->cart_room);

				return;
			}
			if (empty($cart_arr['idsecteur'])) {
				NotifySite::notify_modal("Merci de choisir votre adresse", 'alert', [], $this->cart_room);

				return;
			}
			# verif
			if (empty($cart_arr['idshop']) && !empty($arr_shop['idshop'])) {
				$this->APP_CART->update_native(['cart_id' => $this->cart_id], ['idshop'    => (int)$item['idshop'],
				                                                               'idsecteur' => (int)$arr_shop['idsecteur'],
				                                                               'shop'      => $arr_shop]);
			} else {
				if ($cart_arr['idshop'] != $arr_shop['idshop']) {
					# reset cart to default !
					$this->APP_CART->update_native(['cart_id' => $this->cart_id], ['idshop'    => (int)$item['idshop'],
					                                                               'idsecteur' => (int)$arr_shop['idsecteur'],
					                                                               'shop'      => $arr_shop]);
					$this->empty_cart('all');
				}
			}
			$tes1  = $cart_arr['idsecteur'];
			$test2 = $arr_shop['idsecteur'];

			if ($tes1 != $test2) {
				NotifySite::notify_modal("Vous n'êtes pas situé sur le bon secteur !!", 'alert', [], $this->cart_room);

				return;
			}
			if (!empty($cart_arr['idshop']) && $cart_arr['idshop'] != $arr_shop['idshop']) {
				NotifySite::notify_modal("Vous n'êtes pas situé sur le bon restaurant", 'alert', [], $this->cart_room);

				return;
			}

			$volumeProduit = (float)$item ['volumeProduit'];

			if ($cart_arr['cart_total_volume'] + $volumeProduit > 100) {
				$this->json_export();
				AppSocket::send_cmd('act_script', ['script'    => 'cart_notify',
				                                   'arguments' => ['msg'  => 'enregistrement impossible, volume dépassé',
				                                                   'type' => 'error'],
				                                   'options'   => ['sticky' => 1]], $this->cart_id);

				return false;
			}
			if (!empty($cart_lines["prod_$item_id"]['id']) && !empty($cart_lines["prod_$item_id"]['qte'])) {
				$cart_lines["prod_$item_id"]['qte']++;
				$cart_lines["prod_$item_id"]['total'] = $cart_lines["prod_$item_id"]['qte'] * $item['prix_siteProduit'];
			} else {
				$cart_lines["prod_$item_id"]['id']     = $item;
				$cart_lines["prod_$item_id"]['qte']    = 1;
				$cart_lines["prod_$item_id"]['total']  = $item['prix_siteProduit'];
				$cart_lines["prod_$item_id"]['idshop'] = (int)$item['idshop'];
			}

			$this->update_native(['cart_lines' => $cart_lines]);
			$this->update_cart();
			$this->json_export();
		}

		function get_produit($id) {
			$allowed_c   = ['idshop'                   => 1,
			                'idproduit'                => 1,
			                'idproduit_categorie'      => 1,
			                'nomProduit'               => 1,
			                'codeProduit'              => 1,
			                'prixProduit'              => 1,
			                'prix_siteProduit'         => 1,
			                'volumeProduit'            => 1,
			                'duree_realisationProduit' => 1,
			                '_id'                      => 0];
			$allowed_p_c = ['idproduit_categorie'    => 1,
			                'ordreProduit_categorie' => 1,
			                'codeProduit_categorie'  => 1,
			                '_id'                    => 0];

			$arr     = $this->APP_PROD->findOne(['idproduit' => (int)$id], $allowed_c);
			$arr_cat = $this->APP_PROD_CAT->findOne(['idproduit_categorie' => (int)$arr['idproduit_categorie']], $allowed_p_c);

			return array_merge($arr, $arr_cat);

		}

		function get_shop($id) {
			$allowed_c = ['idshop'      => 1,
			              'nomShop'     => 1,
			              'codeShop'    => 1,
			              'slugShop'    => 1,
			              'idsecteur'   => 1,
			              'slugSecteur' => 1,
			              'actifShop'   => 1,
			              '_id'         => 0];
			$arr       = $this->APP_SHOP->findOne(['idshop' => (int)$id], $allowed_c);;
			unset($arr['_id']);

			return $arr;
		}

		function set_shop($idshop) {
			if (empty($idshop)) return false;
			$this->update_native(['idshop' => (int)$idshop]);
		}

		function set_secteur($idsecteur) {
			if (empty($idsecteur)) return false;
			$this->update_native(['idsecteur' => (int)$idsecteur]);
		}

		function empty_cart($all = 'none') {
			if ($all !== 'all') return;
			$this->cart_lines = [];
			$this->update_native(['cart_lines' => $this->cart_lines]);
			$this->update_cart();
		}

		function cart_edit_line() {
			if (empty($_REQUEST['cart_line_key'])) return false;

			$cart_line_key = $_REQUEST['cart_line_key'];

			$arr_cart   = $this->get_cart();
			$cart_lines = $arr_cart['cart_lines'];
			$cart_line  = $cart_lines[$cart_line_key];
			if (isset($_REQUEST['cart_line_description'])) {
				$cart_lines[$cart_line_key]['description'] = $_REQUEST['cart_line_description'];
				$this->update_native(['cart_lines' => $cart_lines]);
				$this->update_cart();
			}
		}

		/**
		 * need prod_id and qte
		 *
		 * @param array $vars
		 *
		 * @return bool
		 */
		function update_cart_line($vars = ['prod_id' => 'test',
		                                   'qte']) {
			$arr_cart   = $this->get_cart();
			$cart_lines = $arr_cart['cart_lines'];
			if ($vars[1] == 0) {
				unset($cart_lines[$vars[0]]);
			} else {
				//$this->add_item($vars[1]);
				$cart_lines[$vars[0]]['qte']   = $vars[1];
				$cart_lines[$vars[0]]['total'] = number_format($cart_lines[$vars[0]]['id']['prix_siteProduit'] * $vars[1], 2, '.', ' ');

			}
			$tot     = 0;
			$tot_vol = 0;
			foreach ($cart_lines as $key => $value) {
				$tot += $value['total'];
				$tot_vol += (float)$value['id']['volumeProduit'] * $value['qte'];
			}

			if ($tot_vol > 10) {
				AppSocket::send_cmd('act_script', ['script'    => 'cart_notify',
				                                   'arguments' => ['msg'  => 'enregistrement impossible, volume dépassé',
				                                                   'type' => 'error'],
				                                   'options'   => ['sticky' => 1]], $this->cart_id);

				return false;
			}
			$this->update_native(['cart_lines' => $cart_lines]);
			$this->update_cart();
		}

		function remove_item() {
			$this->update_cart();
		}

		function calcul_total() {

		}
	}
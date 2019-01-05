<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 29/09/2017
	 * Time: 20:24
	 */
	class IdaeAction extends App {

		function __construct($table = null) {
			parent::__construct($table);

		}

		function app_create($update_vars = []) {
			if (empty(array_filter($update_vars))) return false;
			$table = $this->table;
			$Table = ucfirst($this->table);

			$update_vars['dateCreation' . ucfirst($table)]  = date('Y-m-d');
			$update_vars['heureCreation' . ucfirst($table)] = date('H:i:s');
			$update_vars['timeCreation' . ucfirst($table)]  = time();
			//
			$vars = empty($update_vars) ? [] : function_prod::cleanPostMongo($update_vars, 1);
			// INSERT
			$APP_TABLE   = new App($table);
			$table_value = $APP_TABLE->insert($vars);
			$arr         = $APP_TABLE->findOne(["id$table" => $table_value]);
			AppSocket::send_cmd('act_add_data', $arr, 'OWN');

			if (sizeof($arr) != 0) {
				SendCmd::notify_mdl('app_fiche_mini', $table, $arr["id$table"], [], session_id());
			}

			echo json_encode($arr, JSON_FORCE_OBJECT);
		}

		function app_update_silent($table_value = null, $update_vars = []) {
			$this->app_update($table_value, $update_vars, true);
		}

		function app_update($table_value = null, $update_vars = [], $silent = false) {
			if (empty($table_value)) {
				return;
			}
			$table       = $this->table;
			$table_value = (int)$table_value;
			$name_id     = 'id' . $this->table;
			$Table       = ucfirst($this->table);

			array_walk_recursive($update_vars, 'CleanStr', $update_vars);
			$vars = empty($update_vars) ? [] : function_prod::cleanPostMongo($update_vars, 1);

			$arr_one = $this->findOne([$name_id => $table_value]);

			if (isset($vars['password' . $Table]) && empty(trim($vars['password' . $Table]))) {
				unset($vars['password' . $Table]);
			}

			// assignation livreur
			if ($table == 'commande' && empty($arr_one['idlivreur']) && !empty($vars['idlivreur'])) {
				$BIN  = new Bin();
				$test = $BIN->test_delivery_reserv(['idcommande' => $table_value,
				                                    'idlivreur'  => (int)$vars['idlivreur']]);

				if ($test == false) return false;

				if ($test['err'] == 1) {
					$Dispatch = new Dispatch();
					$Dispatch->remove_propose_commande_coursier($table_value, $vars['idlivreur']);

					SendCmd::notify_mdl('/app_gui/app_notify', $table, $table_value, $test, session_id());

					return false;
				}
				// statut
				$APP_COMMANDE_STATUT = new App('commande_statut');
				// statut + livreur
				$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['codeCommande_statut' => 'RESERV']);
				if ((int)$arr_one['ordreCommande_statut'] < (int)$arr_commande_statut['ordreCommande_statut']) {
					// statut "reserv" seulement si pas deja avancée
					$vars['idcommande_statut']    = (int)$arr_commande_statut['idcommande_statut'];
					$vars['ordreCommande_statut'] = (int)$arr_commande_statut['ordreCommande_statut'];
					$vars['codeCommande_statut']  = 'RESERV';
				}
				// commande facture
				$APP_CMD_FACT = new App('commande_facture');
				$APP_CMD_FACT->update_native(['idcommande' => $table_value], ['idlivreur' => (int)$vars['idlivreur']]);

				$vars['idlivreur'] = $idlivreur = (int)$vars['idlivreur'];
				$idcommande        = $table_value;
				$room_livreur      = 'livreur_' . $idlivreur;
				$html_vars         = "table=commande&table_value=$idcommande";

			}
			/**
			 * UPDATE
			 */
			$updated_fields = $this->update([$name_id => $table_value], $vars);

			$postAction = new ActionPost($table);
			$postAction->app_update($table,$table_value,$arr_one);

/*			if (!empty($_POST['data-reload']) && $table == 'commande') {
				$data_reload = !empty($_POST['data-reload']) ? $_POST['data-reload'] : "idae/fiche_next_statut/$table/$table_value";
				AppSocket::reloadModule($data_reload, $table_value);
			}
			if ($_POST['data-remove']) {
				AppSocket::reloadModule($_POST['data-remove'], $table_value);
			}
			if ($table == 'livreur_affectation' && isset($update_vars['actifLivreur_affectation']) && isset($update_vars['idsecteur'])) {
				$Notify = new Notify();
				$Notify->notify_livreur_affect($update_vars['idsecteur']);
			}
			if ($table == 'shop' && isset($update_vars['tempsAttenteShop'])) {
				$Notify = new Notify();
				$Notify->notify_wait_time_secteur($arr_one['idsecteur']);
			}
			if ($table == 'commande') {
				$Notify = new Notify();
				$Notify->notify_commande_change((int)$table_value);

				$Demo = new Demo();
				$Demo->animate_step(['idcommande' => $table_value]);

				if (ENVIRONEMENT == 'PREPROD_LAN') {
					if (!empty($arr_one['idlivreur'])) {
					}
				}
			}
			if ($table == 'commande' && !empty($vars[$name_id . '_statut'])) {

				$idshop   = $arr_new['idshop'];
				$Dispatch = new Dispatch();

				if (!empty($vars['idcommande_statut'])) {

					$APP_COMMANDE_STATUT = new App('commande_statut');

					$arr_commande_statut = $APP_COMMANDE_STATUT->findOne(['idcommande_statut' => (int)$vars['idcommande_statut']]);
					if ((int)$arr_one['ordreCommande_statut'] <> (int)$arr_commande_statut['ordreCommande_statut']) {
						$Dispatch->propose_commande_statut($table_value);

						switch ($arr_commande_statut['codeCommande_statut']):
							case 'RESERV';
							case 'LIVENCOU';
								SendCmd::play_sound("shop_$idshop");
								break;
							case 'PREFIN';
								SendCmd::play_sound("livreur_$idlivreur");
								break;
						endswitch;
					}
				}

				if ($table == 'commande' && empty($arr_one['idlivreur']) && !empty($vars['idlivreur'])) {
					$Dispatch->remove_propose_commande($table_value);
				}

				AppSocket::reloadMdlTest('fiche_next_statut_' . $arr_one['idsecteur'], '*');

				$codeStatut = $arr_new['code' . $Table . '_statut'];

				if (ENVIRONEMENT == 'PREPROD_LAN') {

				}
				if ($codeStatut == 'LIVENCOU' && ($arr_one['demo_mode'] != 1)) {

					$apiKey      = "9635f9c81664a3d549b4e76f1211963a7595bc88";
					$smspartner  = new SMSPartnerAPI(false);
					$result      = $smspartner->checkCredits("?apiKey=$apiKey");
					$phoneNumber = ($arr_one['demo_mode'] == 1) ? '0688096140' : $arr_one['telephoneCommande'];

					$fields = ["apiKey"       => $apiKey,
					           "phoneNumbers" => $phoneNumber,
					           "message"      => "La livraison de votre commande est en cours",
					           "sender"       => "TACTACITY"];

					$result = $smspartner->sendSms($fields);
				}
				if ($codeStatut == 'END') {
					$Dispatch = new Dispatch();
					$Dispatch->remove_commande($table_value);
				}
			}*/

			if (!$silent) {
				if (sizeof($updated_fields) != 0) {
					SendCmd::notify_mdl('app_fiche/app_fiche_updated', $table, $table_value, [], session_id());
				}
			}

			echo json_encode($updated_fields, JSON_PRETTY_PRINT);
		}

		function app_delete($table_value = null) {
			if (empty($table_value)) return false;
			$table       = $_REQUEST['table'];
			$table_value = (int)$_REQUEST['table_value'];

			$vars = ['table'       => $table,
			         'table_value' => $table_value];
			AppSocket::send_cmd('act_close_mdl', $vars);
		}

		function app_img_delete($table_value = null, $update_vars = []) {

			$APP  = IdaeConnect::getInstance();
			$base = $_REQUEST['base'] ?: 'sitebase_image';
			$db   = $APP->plug_base($base);
			$grid = empty($collection) ? $db->getGridFs() : $db->getGridFs($collection);
			$grid->remove(['filename' => $_REQUEST['fileName']]);
			$grid->remove(['filename' => $_REQUEST['fileName'] . ".jpg"]);

			SendCmd::sendScript('reloadModule', ['idae/module/app_img_dyn']);
		}

		function upload_img($table_value = null, $update_vars = []) {

			global $IMG_SIZE_ARR;

			$table       = $this->table;
			$table_value = (int)$table_value;

			$bytes           = file_get_contents($_FILES['files']['tmp_name'][0]);
			$arr_type        = explode('.', $_FILES['files']['name'][0]);
			$ext             = strtolower(end($arr_type));
			$codeTailleImage = $_POST['size'];
			$codeImage       = empty($_POST['codeImage']) ? $table . '-' . strtolower($codeTailleImage) . '-' . $table_value : $_POST['codeImage'];
			$width           = $IMG_SIZE_ARR[$codeTailleImage][0];
			$height          = $IMG_SIZE_ARR[$codeTailleImage][1];

			$file_name     = empty($_POST['keep_file_name']) ? $codeImage . '.' . strtolower($ext) : $_FILES['files']['name'][0];
			$real_filename = $_FILES['files']['name'][0];

			$base       = empty($_POST['base']) ? 'sitebase_image' : $_POST['base'];
			$collection = empty($_POST['collection']) ? 'fs' : $_POST['collection'];

			// Taille demandée
			$Rz['width']  = $width;
			$Rz['height'] = $height;
			$ins          = ['table'           => $table,
			                 'table_value'     => $table_value,
			                 'tag'             => $table,
			                 'codeTailleImage' => $codeTailleImage,
			                 'filename'        => $file_name,
			                 'real_filename'   => $real_filename,
			                 'width'           => $width,
			                 'height'          => $height];

			$resized_bytes = IdaeImage::thumbImageBytes($bytes, $Rz);
			IdaeImage::saveImageBytes($file_name, $resized_bytes, $ins, $base, $collection);
			SendCmd::sendScript('reloadModule', ['idae/module/app_img_dyn']);

			// THUMB
			$new_file_name = str_replace($codeTailleImage, 'thumb', $file_name);

			$ins_thumb = ['table'           => $table,
			              'table_value'     => $table_value,
			              'codeTailleImage' => 'thumb',
			              'thumb'           => 1,
			              'filename'        => $new_file_name,
			              'real_filename'   => $real_filename,
			              'tag'             => $table,
			              'width'           => 50,
			              'height'          => 50];

			$Rz['width']  = 50;
			$Rz['height'] = 50;
			$smallbytes   = IdaeImage::thumbImageBytes($bytes, $Rz);
			IdaeImage::saveImageBytes($new_file_name, $smallbytes, $ins_thumb, $base, $collection);

			IdaeDataSchemeImage::buildImageSizes($table, $table_value, $codeTailleImage, $base, $collection);

		}
	}
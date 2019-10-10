<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 29/09/2017
	 * Time: 20:24
	 */
	class ActionPost extends IdaeDB {

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

		function app_update($table, $table_value, $arr_one = []) {
			if (empty($table_value)) {
				return;
			}
			$table_value = (int)$table_value;
			$name_id     = "id$table";
			$Table       = ucfirst($table);

			$arr_new = $this->findOne([$name_id => $table_value]);

			$Dispatch = new Dispatch();
			$Notify   = new Notify();
			$Bin      = new Bin();

			switch ($table) {
				case 'commande':
					$codeStatut = $arr_new['code' . $Table . '_statut'];

					// livreur dé-assignation
					if (empty($arr_one['idlivreur']) && !empty($arr_new['idlivreur'])) {
						$Dispatch->remove_propose_commande($table_value);
					}
					// changement de statut
					if (!empty($arr_one['idcommande_statut'])) {
						// si vrai changement
						if ($arr_one[$name_id . '_statut'] <> $arr_new[$name_id . '_statut']) {
							$Dispatch->propose_commande_statut($table_value);
							if (ENVIRONEMENT != 'PREPROD_LAN') {
								$Dispatch->propose_commande_sound($table_value);
							}
							$data_reload = "idae/fiche_next_statut/$table/$table_value";

							AppSocket::reloadModule($data_reload, $table_value);
							ClassRangCommande::updateRangShopCommandes($arr_one['idshop']);
							$CommandeSlot = new CommandeSlot($arr_one['idsecteur']);
							$CommandeSlot->distribute($arr_one['idsecteur']);
						}

						if (ENVIRONEMENT == 'PREPROD_LAN') {
							/*if ($codeStatut == 'RESERV') {
								AppSocket::run('act_run' , [ 'route'  => 'demo/commande_step_statut/idcommande:' . $table_value ,
															 'method' => 'POST' ,
															 'delay'  => 3000 ]);
							}*/
						}
						if ($codeStatut == 'LIVENCOU' && ($arr_one['demo_mode'] != 1) && ENVIRONEMENT != 'PREPROD_LAN') {
							// sms !!!
							$apiKey      = "9635f9c81664a3d549b4e76f1211963a7595bc88";
							$smspartner  = new SMSPartnerAPI(false);
							$result      = $smspartner->checkCredits("?apiKey=$apiKey");
							$phoneNumber = ($arr_one['demo_mode'] == 1) ? '0688096140' : $arr_one['telephoneCommande']; // 0615956796

							//send SMS
							$fields = ["apiKey"       => $apiKey,
							           "phoneNumbers" => $phoneNumber,
							           "message"      => "La livraison de votre commande est en cours",
							           "sender"       => "TACTACITY"];

							$result = $smspartner->sendSms($fields);
						}
						if ($codeStatut == 'END') {
							$Dispatch->remove_commande($table_value);
						}
					}

					if (!empty($_POST['data-reload'])) {
						$data_reload = !empty($_POST['data-reload']) ? $_POST['data-reload'] : "idae/fiche_next_statut/$table/$table_value";
						AppSocket::reloadModule($data_reload, $table_value);
					}

					$Demo = new Demo();
					$Demo->animate_step(['idcommande' => $table_value]);

					if (ENVIRONEMENT == 'PREPROD_LAN') {
						if (!empty($arr_one['idlivreur'])) {
							/*AppSocket::run('act_run' , [ 'route'  => 'demo/commande_step_statut/idcommande:' . $table_value ,
														 'method' => 'POST' ,
														 'delay'  => 120000 ]);*/
						}
					}

					break;

				case 'livreur_affectation':
					if ($table == 'livreur_affectation' && isset($arr_new['actifLivreur_affectation']) && isset($arr_new['idsecteur'])) {
						$Notify->notify_livreur_affect($arr_new['idsecteur']);
					}
					break;
				case 'shop':
					if ($table == 'shop' && isset($arr_new['tempsAttenteShop']) && isset($arr_new['idsecteur'])) {
						$Notify->notify_wait_time_secteur($arr_new['idsecteur']);
					}
					break;
			}

			if ($_POST['data-remove']) {
				AppSocket::reloadModule($_POST['data-remove'], $table_value);
			}
		}

	}

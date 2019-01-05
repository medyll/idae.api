<?php

	include_once($_SERVER['CONF_INC']);

	if (ENVIRONEMENT != 'PREPROD_LAN') {
		return;
	}

	$DB = new IdaeDataDB('secteur');
	$rs = $DB->find();
	while ($arr = $rs->getNext()) {
		$idsecteur    = (int)$arr['idsecteur'];
		$CommandeSlot = new CommandeSlot($idsecteur);
		$CommandeSlot->distribute($idsecteur);
		$CommandeSlot->draw_debug();
	}



	exit;
	echo ceil(time() - strtotime('2018/07/06 14:52:00')) / 60;

	$arr_shops     = ['LAMALO', 'PAULE', 'THAI', 'JUSOCT'];
	$arr_commandes = [];

	$tot_commande = 0;
	foreach ($arr_shops as $index => $shop) {
		$nbcommand    = rand(1, 4);
		$from_command = rand(1, 3);
		$i            = 1;
		while ($i <= $nbcommand && $tot_commande <= 12) {
			$code                     = '#' . sprintf("%03d", $i + $from_command);
			$rang                     = "S$i";
			$slot                     = "R$i";
			$arr_commandes[]          = ['shop' => $shop, 'code' => $code, 'S' => $rang, 'R' => $slot];
			$arr_commandes_1[$shop][] = "$shop [ $code ,  $rang ,  $slot ]";
			$arr_commandes_2[$rang][] = "$rang [$shop  $code ,   $slot ]";
			++$i;
			++$tot_commande;
		}
	}

	/*var_dump($arr_commandes_1);
	var_dump($arr_commandes_2);*/

	$adadou = array_column($arr_commandes, 'S');
	$ADADA  = array_count_values($adadou);

	var_dump($ADADA);

	//$Demo = new Demo();
	//$Demo->launch_demo();

	/*var_dump($arr_values_1);
	var_dump($arr_values_2);
	var_dump($arr_values_3);*/

	/*$test = $app->get_schemeFields();
	Helper::dump($test);
	$test = $app->get_schemeFieldsMini();
	Helper::dump($test);
	$test = $app->get_schemeFieldsTable();
	Helper::dump($test);
	$test = $app->get_schemeFieldsFk();
	Helper::dump($test);*/
	// $test = $app->get_schemeFieldsTyped();
	// $app->draw_current_field_collection();
	exit;
	IdaeConnect::getInstance()->connect();

	$app = new App('produit');

	Helper::dump($app->findOne([]));
	exit;
	$test = IdaeDroitsFields::getInstance()->droit_session_table_crud("shop", "commande", "U");

	Helper::dump($test);

	exit;
	$Demo = new Demo();

	$Demo->create_affectation_period();

	$Bin = new Bin();

	$test = $Bin->test_livreur_affect(59829);
	//$Dispatch->test_livreur_affect(54155);

	Helper::dump($test);

	$Demo = new Demo();
	$Demo->create_secteur_shift();

	$Dispatch = new Dispatch();
	exit;
	$table_value              = 54155;
	$APP_COMMANDE_PROPOSITION = new App('commande_proposition');
	$APP_COMMANDE             = new App('commande');

	$ARR_COMMANDE = $APP_COMMANDE->findOne(['idcommande' => (int)$table_value]);

	$room_shop    = 'shop_' . $ARR_COMMANDE['idshop'];
	$room_livreur = 'livreur_' . $ARR_COMMANDE['idlivreur'];

	exit;
	$Notify = new Notify();
	$Notify->notify_commande_shop(1);

	/*SendCmd::insert_mdl('app_console_thumb', 'commande', 136, ['data-table' => 'commande',
	                                                                   'data-type_session' => 'shop']);*/

	$Dispatch = new Dispatch();
	$Dispatch->propose_commande_shop(54159);
	// $Dispatch->propose_commande_secteur_all_pool();

	//$BIN = new Bin();

	//$arr = $BIN->get_estimation_wait_time_fields(1);

	Helper::dump($arr);
	$a = SendCmd::build_selector(['table' => 'commande',
	                              ['index'       => 'delex',
	                               'table_value' => 125]]);

	Helper::dump($a);

	exit;
	$Bin = new Bin();

	$Bin->get_elapsed_minutes_arr_for_commande(53369);

	exit;

	$Idae        = new Idae('commande');
	$ARR_COLLECT = $Idae->get_table_fields($table_value, ['in_min_fiche' => 1]);

	$COLLECT_VALUES               = array_values($ARR_COLLECT);
	$COLLECT_VALUES_COLUMN        = array_column(array_values($ARR_COLLECT), 'appscheme_fields');
	$COLLECT_VALUES_COLUMN_VALUES = array_values($COLLECT_VALUES_COLUMN);

	$COLLECT_FIELDS = [];
	foreach ($ARR_COLLECT as $key => $ARR_FIELD_GROUP) {
		foreach ($ARR_FIELD_GROUP['appscheme_fields'] as $CODE_ARR_FIELD => $ARR_FIELD) {
			$COLLECT_FIELDS[] = $ARR_FIELD;
		}

	}

	Helper::dump($COLLECT_FIELDS);

	exit;
	$table       = 'commande';
	$table_value = 52646;
	$Table       = ucfirst($table);

	$Idae    = new Idae($table);
	$AppMail = new AppMail();
	$instyle = new InStyle();
	$Idae->consolidate_scheme($table_value);
	$ARR_COMMANDE = $Idae->findOne(["id$table" => (int)$table_value]);
	$Body         = $instyle->convert($Idae->fiche_mail($table_value), true);
	/*$AppMail->set_body($Body);
	$AppMail->set_destinataire_email($ARR_COMMANDE["email$Table"]?:'m.mydde@gmail.com');
	$AppMail->set_destinataire_name($ARR_COMMANDE["prenom$Table"].' '.$ARR_COMMANDE["nom$Table"]);
	$AppMail->set_subject('Votre commande '.$ARR_COMMANDE["code$Table"].' avec TAC-TAC');
	$AppMail->sendMail();*/

	echo $Body;
	/*echo  $Idae->module('app_fiche/app_fiche_mail', ['table'         => 'commande',
			                                               'table_value'   => 52557]);*/

	exit;









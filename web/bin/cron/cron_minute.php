<?
	include_once($_SERVER['CONF_INC']);
	$Demo = new Demo();
	$Bin  = new Bin();

	$Demo->create_secteur_shift();
	$Demo->create_affectation_period();

	if (ENVIRONEMENT == 'PREPROD_LAN') {
		$Demo = new Demo();
		// $Demo->launch_demo();
		// $Demo->commande_pool();
	}
	$Dispatch = new Dispatch();
	$Dispatch->propose_commande_secteur_all_pool();

	$commandeQueueConsole = new CommandeQueueConsole();
	$configs              = $commandeQueueConsole->get_times_config();

	$configs->get_times_secteur->update();
	$configs->get_times_secteur_livreur->update();

	$DB = new IdaeDataDB('shop');
	$rs = $DB->find(['actifShop' => 1]);

	// move to Class
	while ($arr = $rs->getNext()) {
		Demo::create_shop_shift($arr['idshop']);
		ClassRangCommande::updateRangShopCommandes($arr ['idshop']);

		$configs = CommandeQueueConsole::consoleShop($arr['idshop']);
		$configs->console_secteur_livreur->update();
		$configs->console_secteur->update();
		$configs->console_shop->update();
		$configs = CommandeQueueConsole::consoleShopSite($arr['idshop']);
		$configs->console_shop->update();
		$configs->console_secteur_livreur->update();

	}

	$DB = new IdaeDataDB('secteur');
	$rs = $DB->find();
	while ($arr = $rs->getNext()) {
		$idsecteur = (int)$arr['idsecteur'];
		$CommandeSlot = new CommandeSlot($idsecteur);
		$CommandeSlot->distribute($idsecteur);
	}









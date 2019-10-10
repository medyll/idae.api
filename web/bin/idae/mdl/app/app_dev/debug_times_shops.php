<?
	include_once($_SERVER['CONF_INC']);

	$DB           = new IdaeDB('shop');
	$session_data = IdaeSession::getInstance()->get_session();

	$rs = $DB->find(['actifShop' => 1]);

	while ($arr = $rs->getNext()) {
		?>
        <div class="padding text-bolc"><?= strtoupper($arr['nomShop']) ?></div>
		<?
		echo "<br>";
		$configs = CommandeQueueConsole::consoleShop($arr['idshop']);
		echo $configs->console_shop->get_templateHTML();
		echo "<br>";
	}

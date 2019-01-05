<?
	include_once($_SERVER['CONF_INC']);

	$Idae         = new Idae();
	$session_data = IdaeSession::getInstance()->get_session();

	$configs = CommandeQueueConsole::get_times_config();

	$HTMLSHOPS = $Idae->module('app_dev/debug_times_shops');

?>

    <div id="debugPanel" class="flex_h flex_align_top flex_wrap animated slideInUp bordert color-base-dark relative" style="display: none;max-height: 100%;overflow:auto;">
        <div class="padding_more flex_main"><?= $configs->get_times_secteur->get_templateHTML() ?></div>
        <div class="padding_more flex_main"><?= $configs->get_times_secteur_livreur->get_templateHTML() ?></div>
        <div class="flex_main">
	        <?=$HTMLSHOPS?>

        </div>
    </div>
<?
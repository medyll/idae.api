<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 25/12/2017
	 * Time: 15:30
	 */
	include_once($_SERVER['CONF_INC']);

	/*$session_data = IdaeSession::getInstance()->get_session();*/

	$type_session        = $_SESSION['type_session'];
	$Type_session        = ucfirst($type_session);
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$APP = new App('appscheme');
	//
	$arr_allowed_c = droit_table($type_session, 'C');
	$arr_allowed_r = droit_table($type_session, 'R');
	$arr_allowed_l = droit_table($type_session, 'L');
	//
	$APP_SCH    = new IdaeDataDB('appscheme');
	$APP_SCH_TY = new IdaeDataDB('appscheme_type');

	$IDAE_SELF = new Idae($type_session);
	$APPOBJ    = $IDAE_SELF->appobj($idtype_session, $vars);
	$ARR       = $APPOBJ->ARR;


	$table = $type_session;


	$LIVREUR_DISPONIBLE_NB = null;
	$LIVREUR_WORKING_NB    = null;
	$COMMAND_UNASSIGNED_NB = null;
	$COMMAND_NB            = null;

	if ($type_session == 'shop') {

		$configs = CommandeQueueConsole::consoleShop($idtype_session);

		$times_secteur = $configs->console_secteur_livreur->get_templateObjHTML();
		$times_shop    = $configs->console_shop->get_templateObjHTML();

		$LIVREUR_DISPONIBLE_NB = $times_secteur->LIVREUR_DISPONIBLE_NB;
		$LIVREUR_WORKING_NB    = $times_secteur->LIVREUR_WORKING_NB;

		$COMMAND_UNASSIGNED_NB = $times_shop->COMMAND_SHOP_WAITING_NB;
		$COMMAND_NB            = $times_shop->COMMAND_SHOP_SHIFT_NB;
	}

	if ($type_session == 'livreur') {
		$configs               = CommandeQueueConsole::consoleLivreur($idtype_session);
		$times_secteur_livreur = $configs->console_secteur_livreur->get_templateObjHTML();
		$times_secteur         = $configs->console_secteur->get_templateObjHTML();

		$LIVREUR_DISPONIBLE_NB = $times_secteur_livreur->LIVREUR_DISPONIBLE_NB;
		$LIVREUR_WORKING_NB    = $times_secteur_livreur->LIVREUR_WORKING_NB;

		$COMMAND_UNASSIGNED_NB = $times_secteur->COMMAND_SECTEUR_UNASSIGNED_NB;
		$COMMAND_NB            = $times_secteur->COMMAND_SECTEUR_NB;
	}

?>
<div class="relative padding_more">
    <div class="flex_h flex_align_middle">
        <div class="">
            <a data-menu="data-menu" data-toggle="offCanvasLeft"><i class="fa fa-navicon fa-2x"></i></a>
        </div>
        <div class=" flex_main text-center relative">
            <div class="absolute" style="left:0;top:0;">
                <div class="flex_h flex_align_middle flex_padding">
                    <div></div>
                    <div>
						<?= $LIVREUR_DISPONIBLE_NB ?>
                    </div>
                    <div><i class="fa fa-bicycle"></i></div>
                    <div><?= $LIVREUR_WORKING_NB ?>
                    </div>
                </div>
            </div>
            <div class="padding">
                <div>
					<?= $ARR["nom$Type_session"] ?>
					<?= $ARR["prenom$Type_session"] ?>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute" style="right:0;top:0;">
        <div class="flex_h flex_align_middle">
            <div class="flex_h flex_align_middle flex_padding">
 	            <?= $COMMAND_UNASSIGNED_NB ?>
                <div><i class="fa fa-cubes"></i></div>
				<?= $COMMAND_NB ?>
                <div><i class="fa fa-minus fa-rotate-90"></i></div>
            </div>
            <div class="cursor none" onclick="$('#debugPanel').toggle();">
                <i class="fa fa-cog fa-2x"></i>
            </div>
            <div class="item_icon more text-center cursor" data-module_link="home" data-vars="table=<?= $type_session ?>&table_value=<?= $idtype_session ?>">
                <i class="fa fa-user-circle fa-2x"></i>
            </div>
            <div class="padding">
                <div id="connect_status"></div>
            </div>
        </div>
    </div>
</div>
<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table     = $_POST['table'];
	$Table     = ucfirst($table);
	$query_str = http_build_query($_POST);

	$table_value = (int)$_POST['table_value'];
	$vars        = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);
	$vars_http   = $this->translate_vars($vars);
	$groupBy     = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	//
	$APP        = new App($table);
	$APP_TABLE  = $APP->app_table_one;
	$APPOBJ     = $APP->appobj($table_value, $vars);
	$ICON_COLOR = $APPOBJ->ICON_COLOR;
	$ARR        = $APPOBJ->ARR;
	//
	$name_id = 'id' . $table;
	$nom     = 'nom' . ucfirst($table);

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$white_COLOR = '#fff';
	$rand        = uniqid();

	$css_table = "fiche_$table";

	$color          = empty($APP->colorAppscheme) ? '#c4c4c4' : $APP->colorAppscheme;
	$color_contrast = color_contrast($APP->colorAppscheme);
	$color_inverse  = color_inverse($APP->colorAppscheme);

	$crypted_create_link         = AppLink::create($table, null, [$name_idtype_session => $idtype_session]);
	$crypted_update_link         = AppLink::update($table, $table_value);
	$crypted_delete_link         = AppLink::delete($table, $table_value);
	$crypted_map_link            = AppLink::map($table, $table_value);
	$crypted_liste_link          = AppLink::liste($table);
	$crypted_liste_groupby_link  = AppLink::liste_groupby($table);
	$crypted_liste_calendar_link = AppLink::liste_calendrier($table);

?>
<div class="blanc   flex_h flex_align_middle   bordert ">
	<? if ($arr_allowed_c) { ?>
		<div class="text-center  padding_more cursor flex_main" data-crypted_link="<?= $crypted_create_link ?>">
			<div class="item_icon item_icon_shadow more inline">
				<i class="fa fa-plus-circle fa-2x"></i>
			</div>
		</div>
	<? } ?>
	<? if ($arr_allowed_u) { ?>
		<div class="text-center padding_more  cursor flex_main" data-crypted_link="<?= $crypted_update_link ?>">
			<div class="item_icon item_icon_shadow more inline">
				<i class="fa fa-pencil-square-o fa-2x"></i>
			</div>
		</div>
	<? } ?>
	<? if ($arr_allowed_r && $APP->has_field('adresse')) { ?>
		<div class="text-center padding_more cursor flex_main" data-crypted_link="<?= $crypted_map_link ?>">
			<div class="item_icon item_icon_shadow more inline ">
				<i class="fa fa-map-marker fa-2x"></i>
			</div>
		</div>
	<? } ?>
	<? if ($arr_allowed_l && $APP->has_field(['date', 'dateDebut'])) { ?>
		<div class="text-center padding_more cursor flex_main" data-crypted_link="<?= $crypted_liste_calendar_link ?>">
			<div class="item_icon item_icon_shadow more inline">
				<i class="fa fa-calendar fa-2x"></i>
			</div>
		</div>
	<? } ?>
	<? if ($arr_allowed_l) { ?>
		<div class="text-center padding_more cursor flex_main" data-crypted_link="<?= $crypted_liste_link ?>">
			<div class="item_icon item_icon_shadow more inline">
				<i class="fa fa-list fa-2x"></i>
			</div>
		</div>
	<? } ?>
    <? if ($arr_allowed_d) { ?>
    <div data-module_link="app_crud/app_delete" data-vars="table=<?= $table ?>&table_value=<?= $table_value ?>"
       data-target_flyout="true"
       class="text-center padding_more cursor flex_main" data-link
       data-table="<?= $table ?>" >
        <div class="item_icon item_icon_shadow more inline">
            <i class="fa fa-times fa-2x textrouge"  ></i>
        </div>
    </div>
    <? } ?>
</div>
<style>
	/*.






	<?=$css_table?>






							{
								background :






	<?= $white_COLOR ?>






							;
								background : linear-gradient(135deg,






	<?= $ICON_COLOR ?>






							 60%,






	<?= $white_COLOR ?>






							100%);
								}*/
	.item_icon_shadow .fa {
		color       : #666;;
		text-shadow : 2px 1px 0 rgba(255, 255, 255, 0.15), 4px 2px 0 rgba(0, 0, 0, 0.15);
	}
</style>
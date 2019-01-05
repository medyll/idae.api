<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table = $this->HTTP_VARS['table'];
	$Table = ucfirst($table);

	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$groupBy     = empty($this->HTTP_VARS['groupBy']) ? '' : $this->HTTP_VARS['groupBy'];
	$page        = (!isset($this->HTTP_VARS['page'])) ? 0 : $this->HTTP_VARS['page'];
	$nbRows      = (empty($this->HTTP_VARS['nbRows'])) ? empty($settings_nbRows) ? 10 : (int)$settings_nbRows : $this->HTTP_VARS['nbRows'];
	//
	$APP    = new App($table);
	$Idae   = new Idae($table);
	$APPOBJ = $APP->appobj($table_value, $vars);
	//
	$white_COLOR = '#ffffff';

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$html_attr_create = '';
	$html_attr        = 'table=' . $table;
	$html_create_link = $html_attr;
	$html_create_link .= '&' . http_build_query($this->HTTP_VARS);
	if ($APP->has_field_fk($type_session)) {
		$html_create_link .= "&vars[$name_idtype_session]=$idtype_session";
	}

	$crypted_create_link         = AppLink::create($table,$table_value,[$name_idtype_session=>$idtype_session]);
	$crypted_liste_link          = AppLink::liste($table);
	$crypted_liste_groupby_link  = AppLink::liste_groupby($table);
	$crypted_liste_calendar_link = AppLink::liste_calendrier($table);

?>
<div class="blanc boxshadow flex_h flex_align_middle boxshadow bordert  ">
	<? if ($arr_allowed_c) { ?>
		<div class="text-center padding_more  cursor flex_main" data-crypted_link="<?= $crypted_create_link ?>" >
			<div class="item_icon item_icon_shadow more inline">
				<i class="fa fa-plus-circle fa-2x"></i>
			</div>
		</div>
	<? } ?>
	<? if ($arr_allowed_l) { ?>
		<div class="text-center padding_more  cursor flex_main none" data-crypted_link="<?= $crypted_liste_groupby_link ?>" >
			<div class="item_icon item_icon_shadow more inline">
				<i class="fa fa-list fa-2x"></i>
			</div>
		</div>
		<div class="text-center padding_more  cursor flex_main" data-crypted_link="<?= $crypted_liste_link ?>" >
			<div class="item_icon item_icon_shadow more inline">
				<i class="fa fa-list fa-2x"></i>
			</div>
		</div>
	<? } ?>
	<? if ($APP->has_field(['date', 'dateDebut']) && $arr_allowed_l) { ?>
		<div class="text-center padding_more  cursor flex_main " data-crypted_link="<?= $crypted_liste_calendar_link ?>" >
			<div class="item_icon item_icon_shadow more inline  ">
				<i class="fa fa-calendar fa-2x"></i>
			</div>
		</div>
	<? } ?>
</div>
<style>
	.<?=$css_table?> {
		background : <?= $white_COLOR ?>;
		background : linear-gradient(135deg, <?= $ICON_COLOR ?> 60%,<?= $white_COLOR ?> 100%);
	}
	.item_icon_shadow .fa {
		color       : #666;;
		text-shadow : 2px 1px 0 rgba(255, 255, 255, 0.15), 4px 2px 0 rgba(0, 0, 0, 0.15);
	}
</style>
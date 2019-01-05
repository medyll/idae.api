<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table     = $this->HTTP_VARS['table'];
	$Table     = ucfirst($table);
	$query_str = http_build_query($this->HTTP_VARS);

	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$groupBy     = empty($this->HTTP_VARS['groupBy']) ? '' : $this->HTTP_VARS['groupBy'];
	//
	$APP       = new App($table);
	$APP_TABLE = $APP->app_table_one;
	$APPOBJ    = $APP->appobj($table_value, $vars);
	$ARR       = $APPOBJ->ARR;
	//
	$EXTRACTS_VARS = $APP->extract_vars($table_value, $vars);
	extract($EXTRACTS_VARS, EXTR_OVERWRITE);
	//
	//
	$name_id = 'id' . $table;

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$html_fiche_link = '<a   href="#idae/module/fiche/table=' . $table . '&table_value=' . $table_value . '">back</a>';
	$white_COLOR   = '#fff';

	if ($APP->has_field('adresse')) {
		$html_map_link = 'table=' . $table . '&table_value=' . $table_value;
	}

	$rand = uniqid();

	$css_table = "fiche_$table";

	$color          = empty($APP->colorAppscheme) ? '#c4c4c4' : $APP->colorAppscheme;
	$color_contrast = color_contrast($APP->colorAppscheme);
	$color_inverse  = color_inverse($APP->colorAppscheme);

	$fieldTypeShow = ($table=='commande')? 'code' : 'nom' ;
?>
<div class="" data-table="<?= $table ?>" data-table_value="<?= $table_value ?>">
	<div class="flex_h  flex_align_middle borderb ">
		<div class=" ">
			<? if (!empty($APP_TABLE['hasImageScheme']) && !empty($this->HTTP_VARS['table_value'])): ?>
				<div class="padding_more relative">
					<div class="padding  text-center    absolute " style="width:100%;left:0;bottom:0;border-color:<?= $ICON_COLOR ?>">
						<i class="fa fa-<?= $APPOBJ->ICON ?> text-bold" style="color:<?= $color_contrast ?>"></i>
					</div>
					<img class="boxshadowb border4" style="max-width:70px;border-radius: 50px;" src="<?= AppSite::imgApp($table, $table_value, 'square') ?>">
				</div>
			<? else: ?>
				<div class="<?= $css_table ?> padding_more"><i class="fa fa-<?= $APPOBJ->ICON ?> fa-2x" style="color:<?= $color_contrast ?>"></i></div>
			<? endif; ?>
		</div>
		<div class="flex_main">
			<div class="padding_more relative">
				<? if (!empty($this->HTTP_VARS['table_value'])): ?>
					<h3><?= $APP->draw_field(['field_name_raw' => 'nom',
					                          'table'          => $table,
					                          'field_value'    => $ARR[$fieldTypeShow . $Table]]) ?>
					</h3>
				<? else : ?>
					<h3><?= $APP->nomAppscheme ?></h3>
				<? endif; ?>
			</div>
		</div>
	</div>
</div>
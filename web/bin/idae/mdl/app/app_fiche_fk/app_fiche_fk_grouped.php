<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table = $this->table;
	$Table = ucfirst($table);

	$APP = new App($table);
	if (sizeof($APP->get_grille_fk()) == 0) { return; }

	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$groupBy     = empty($this->HTTP_VARS['groupBy']) ? '' : $this->HTTP_VARS['groupBy'];
	$mode        = empty($this->HTTP_VARS['mode']) ? 'icone' : $this->HTTP_VARS['mode'];

	$name_id = 'id' . $table;
	$ARR     = $APP->findOne([$name_id => $table_value]);
?>
<div class="grid-x">
	<? foreach ($APP->get_grille_fk_grouped() as $field):
		// query for name
		$table_fk      = $field['table_fk'];
		$Table_fk = ucfirst($table_fk);

		$arr_allowed_c = droit_table($type_session, 'R', $table_fk);
		$arr_allowed_l = droit_table($type_session, 'L', $table_fk);

		$APP_TMP = new App($table_fk);
		$TBL_TMP = $APP_TMP->app_table_one;

		$arr           = $APP->plug($field['base_fk'], $table_fk)->findOne([$field['idtable_fk'] => $ARR[$field['idtable_fk']]]);
		$dsp_fieldname = 'nom' . ucfirst($table_fk);
		$dsp_fieldcode = 'code' . ucfirst($table_fk);
		$dsp_name      = $arr[$dsp_fieldname] ?: $arr[$dsp_fieldcode];
		if (empty($dsp_name)) {
			$dsp_name = 'Aucun';
		}

		$css_cursor    = ($arr_allowed_c) ? 'cursor' : '';
		?>
		<div class="cell small-12 medium-6 large-4 ">
			<div class="padding">
				<div class="flex_h <?= $css_cursor ?> flex_align_middle borderb borderl">
					<div class="padding_more boxshadowr  " style="width:40px;"><i class="fa fa-<?= $APP_TMP->iconAppscheme ?>" style="color: <?= $APP_TMP->colorAppscheme ?>"></i></div>
					<div class="padding_more flex_main">
						<div class="  ellipsis textgris"><?= $APP_TMP->nomAppscheme ?></div>
						<div class="flex_h flex_align_middle" style="font-size:1.15rem">
							<div class=""><?= $APP_TMP->draw_field(['field_name_raw' => 'nom', 'field_name' => $dsp_fieldname, 'table' => $table, 'field_value' => $dsp_name]) ?></div>
							<div class="padding"><?= $APP->cf_output('color', $ARR, "color$Table_fk"); ?></div>
							<div class="padding"><?= $APP->cf_output('icon', $ARR, "icon$Table_fk"); ?></div>
						</div>
					</div>
					<div class="padding_more">
						<i class="fa fa-ellipsis-h"></i>
					</div>
				</div>
			</div>
		</div>
	<? endforeach; ?>
</div>
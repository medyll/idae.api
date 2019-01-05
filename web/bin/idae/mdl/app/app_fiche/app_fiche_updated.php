<?
	include_once($_SERVER['CONF_INC']);

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$table       = $this->HTTP_VARS['table'];
	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

	//
	$APP       = new App($table);
	$Idae      = new Idae($table);
	$APP_TABLE = $APP->app_table_one;
	$APPOBJ    = $APP->appobj($table_value, $vars);
	$ARR       = $APP->findOne(["id$table" => (int)$table_value]);

	$UPD_F = $ARR['updated_fields'] ?: [];

	$ARR_FIELDS = $APP->get_field_list();

?>
<div class="padding blanc">
	<div class="flex_h">
		<div class="padding_more boxshadowr text-center">
			<i class="fa fa-<?= $APP->iconAppscheme; ?> fa-2x" style="color:<?= $APP->colorAppscheme; ?>"></i>
		</div>
		<div class="padding flex_main ">
			<table>

			<?
				foreach ($UPD_F as $field => $value) {
					if(!isset($ARR_FIELDS[$field])) continue;
					$field_table    = $ARR_FIELDS[$field]['nomAppscheme'];
					$field_name     = $ARR_FIELDS[$field]['nomAppscheme_field'];
					$field_name_raw = $ARR_FIELDS[$field]['field_name_raw'];
					$field_value    = $APP->draw_field(['field_name' => $field, 'field_name_raw' => $field_name_raw, 'field_value' => $value]);
					?>
					<tr class="padding     borderb">
						<td class="   "><?= $field_name ?></td>
						<td class="    text-bold"><?= $field_value ?></td>
					</tr>
					<?
				}
			?>
			</table>
		</div>
	</div>
	<div class="padding text-center">
		<?= $APP->nomAppscheme; ?> <?=$APP->cf_output('nom',$ARR)?>
	</div>
</div>

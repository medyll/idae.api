<?
	include_once($_SERVER['CONF_INC']);

	$table = $_POST['table'];
	$Table = ucfirst($table);

	$table_value = (int)$_POST['table_value'];
	$vars = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);
	$groupBy     = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];

	$APP       = new App($table);
	$APP_TABLE = $APP->app_table_one;
	$APPOBJ = $APP->appobj($table_value, $vars);
	$ARR       = $APPOBJ->ARR;
?>
<div >
	<span   data-thumb_order="<?= ($ARR['timeFinPreparationCommande'])? (int)$ARR['timeFinPreparationCommande'] : ''; ?>"></span>
	<div class="flex_h flex_wrap " style="width: 100%;">
		<?
			$arr_has = ['statut', 'type', 'categorie', 'group'];
			foreach ($arr_has as $key => $value):
				$APPTMP = new App("$table" . "_" . "$value");

				$Value  = ucfirst($value);
				$_table = $table . '_' . $value;
				$_Table = ucfirst($_table);
				$_id    = 'id' . $_table;
				$_nom   = 'nom' . $_Table;
				$_code  = 'code' . $_Table;
				$_icon  = 'icon' . $_Table;
				$_color = 'color' . $_Table;
				if (!empty($ARR[$_nom])): ?>
					<div class="flex_h flex_padding flex_align_middle">
						<div class="relative flex_v flex_inline flex_align_middle blanc    ">
							<div class="    text-center padding ">
								<?= $APP->draw_field(['field_name_raw' => 'color',
								                      'field_name'     => $_color,
								                      'table'          => $value,
								                      'field_value'    => $ARR[$_color]]) ?>
							</div>
							<div class="    text-center  padding " style="font-size: 1.3em">
								<?= $APP->draw_field(['field_name_raw' => 'icon',
								                      'field_name'     => $_icon,
								                      'table'          => $value,
								                      'field_value'    => $ARR[$_icon]]) ?>
							</div>
						</div>
						<div class="flex_main text-shadow">
							<div class="text-bold"><?= $APP->draw_field(['field_name_raw' => 'nom',
							                                             'field_name'     => $_nom,
							                                             'table'          => $value,
							                                             'field_value'    => $ARR[$_nom]]) ?></div>
							<div><span class=""><?= ucfirst($APPTMP->nomAppscheme) ?></span></div>
						</div>
					</div>
				<? endif; ?>
			<? endforeach; ?>
	</div>
</div>
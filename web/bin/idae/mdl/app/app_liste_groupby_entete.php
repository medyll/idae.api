<?
	include_once($_SERVER['CONF_INC']);

	$table = $_POST['table'];
	$Table = ucfirst($table);

	$table_value = (int)$_POST['table_value'];
	$vars        = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);
	$groupBy     = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	// 
	$APP            = new App($table);
	$html_home_link = 'table=' . $table . '&table_value=' . $table_value;

	$Idae        = new Idae($table);
	$ARR_COLLECT = $Idae->get_table_fields($table_value, ['codeAppscheme_field' => ['nom', 'code', 'reference']]);

	$COLLECT_FIELDS = [];
	foreach ($ARR_COLLECT as $key => $ARR_FIELD_GROUP) {
		foreach ($ARR_FIELD_GROUP['appscheme_fields'] as $CODE_ARR_FIELD => $ARR_FIELD) {
			$COLLECT_FIELDS[] = $ARR_FIELD;
		}
	}

?>
<div class=" blanc        " style="position:sticky;top:0px;margin-top:0em;z-index:600">
	<div class=" flex_h flex_align_middle ">
		<div  >
			<div class="padding_more text-center boxshadowr">
				<i class="fa fa-<?= $APP->iconAppscheme ?> fa-2x" style="color:<?= $APP->colorAppscheme ?>"></i>
			</div>
		</div>
		<div class="flex_main padding  ">
			<div class="flex_h flex_align_middle flex_wrap">
				<?
					foreach ($COLLECT_FIELDS as $CODE_ARR_FIELD => $ARR_FIELD) { ?>
						<div class="flex_main">
							<div class="flex_h flex_align_middle">
								<div class="padding textgris" style="width:25px;">
									<i class="fa fa-<?= $ARR_FIELD['icon'] ?> "></i>
								</div>
								<div class="padding text-bold borderb" style="font-size:1.4em;">
									<?= empty($this->HTTP_VARS['edit_field']) ? $ARR_FIELD['value_html'] : $ARR_FIELD['value_input'] ?>
								</div>
							</div>
						</div>
					<? } ?>
			</div>
			<div class="padding_more textgrisfonce"><?= $APP->nomAppscheme ?></div>
		</div>
	</div>
</div>
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

	$color          = empty($APP->colorAppscheme) ? '#c4c4c4' : $APP->colorAppscheme;
	$color_contrast = color_contrast($APP->colorAppscheme);
	$color_inverse  = color_inverse($APP->colorAppscheme);
?>
<div class=" blanc padding_more       " style=" ">
	<div class=" flex_h flex_align_middle   list_thumb_icon " style="background-color: <?=$color?>;color:<?=$color_contrast?>">
		<div  class="table_icon">
			<div class="padding_more text-center boxshadowr">
				<i class="fa fa-<?= $APP->iconAppscheme ?> fa-2x" style="color:<?=$color_contrast?>"></i>
			</div>
		</div>
		<div class="flex_main padding  ">
			<div class="flex_h flex_align_middle flex_wrap">
				<?
					foreach ($COLLECT_FIELDS as $CODE_ARR_FIELD => $ARR_FIELD) { ?>
						<div class="flex_main">
							<div class="flex_h flex_align_middle">
								<div class="padding text-center " style="font-size:1.2em;">
									<?= empty($this->HTTP_VARS['edit_field']) ? $ARR_FIELD['value_html'] : $ARR_FIELD['value_input'] ?>
								</div>
							</div>
						</div>
					<? } ?>
			</div>
		</div>
	</div>
</div>
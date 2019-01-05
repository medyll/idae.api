<?
	include_once($_SERVER['CONF_INC']);

	$table       = $this->HTTP_VARS['table'];
	$Table       = ucfirst($table);
	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

	$Idae        = new Idae($table);
	$ARR_COLLECT = $Idae->get_table_fields($table_value, $this->HTTP_VARS);
	if ($Idae->has_field('adresse')) {
		$html_map_link = 'table=' . $table . '&table_value=' . $table_value;
	}
?>
<div data-table="<?= $table ?>" data-table_value="<?= $table_value ?>">
	<div class=" flex_h flex_padding flex_wrap">
		<? foreach ($ARR_COLLECT as $key => $ARR_FIELD_GROUP) {
			?>
			<? if (!empty($this->HTTP_VARS['titre'])) { ?>
				<div class=" " style="width:100%;">
					<div class=" flex_main   borderb flex_h flex_align_middle    relative">
						<div class="boxshadowr padding_more textgrisfonce"><i class="fa fa-<?= $ARR_FIELD_GROUP['appscheme_field_group']['iconAppscheme_field_group'] ?>"></i></div>
						<div class="padding_more text-bold"> <?= $ARR_FIELD_GROUP['appscheme_field_group']['nomAppscheme_field_group'] ?></div>
					</div>
				</div>
			<? } ?>
			<? if (!empty($this->HTTP_VARS['cesure'])) { ?>
				<div class="boxshadowb" style="width:100%;"></div>
			<? } ?>
			<div class="  flex_h flex_wrap flex_align_middle" style="width:100%;">
				<?
					foreach ($ARR_FIELD_GROUP['appscheme_fields'] as $CODE_ARR_FIELD => $ARR_FIELD) {

						  $html_edit_link = 'field_name_raw='.$ARR_FIELD['field_name_raw'].'&field_name='.$ARR_FIELD['field_name'].'&table=' . $table . '&table_value=' . $table_value;

						?>
						<div class="" style="min-width:50%;">
							<div class="flex_h flex_align_middle">
								<div class="padding   ellipsis textgrisfonce borderb" style="width:95px;min-width:95px;;max-width:95px;;">
									- <?= ucfirst(idioma($ARR_FIELD['nom'])) ?>
								</div>
								<div class="padding    textgris borderb" style="width:25px;">
									<i class="fa fa-<?= $ARR_FIELD['icon'] ?> "></i>
								</div>
								<div class="padding no_wrap <?= $ARR_FIELD['css_bol'] ?> ">
									<?= empty($this->HTTP_VARS['edit_field']) ? $ARR_FIELD['value_html'] : $ARR_FIELD['value_input'] ?>
								</div>
							</div>
						</div>
					<? } ?>
			</div>
			<? if (!empty($this->HTTP_VARS['titre'])) { ?>
				<div>
					<br>
				</div>
			<? } ?>
		<? } ?>
	</div>
</div>
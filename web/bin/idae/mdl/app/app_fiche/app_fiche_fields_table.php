<?
	include_once($_SERVER['CONF_INC']);

	$table       = $this->HTTP_VARS['table'];
	$Table       = ucfirst($table);
	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

	$Idae        = new Idae($table);
	$ARR         = $Idae->findOne(["id$table" => $table_value]);
	$ARR_COLLECT = $Idae->get_table_fields($table_value, $this->HTTP_VARS);

	$COLLECT_FIELDS = [];
	foreach ($ARR_COLLECT as $key => $ARR_FIELD_GROUP) {
		foreach ($ARR_FIELD_GROUP['appscheme_fields'] as $CODE_ARR_FIELD => $ARR_FIELD) {
			$COLLECT_FIELDS[] = $ARR_FIELD;
		}
	}

	$FK = $Idae->get_grille_fk();
?>
<div data-table="<?= $table ?>" data-table_value="<?= $table_value ?>">
	<div class="padding_more flex_h   flex_wrap">
		<?
			foreach ($COLLECT_FIELDS as $CODE_ARR_FIELD => $ARR_FIELD) { ?>
				<div class="" style="min-width:100%;">
					<div class="flex_h flex_align_middle">
						<div class="padding    textgris" style="width:25px;">
							<i class="fa fa-<?= $ARR_FIELD['icon'] ?> "></i>
						</div>
						<div class="padding borderb ellipsis textgrisfonce text-right" style="width:calc(50% - 25px);">
							<?= ucfirst(idioma($ARR_FIELD['nom'])) ?>
						</div>
						<div class="padding <?= $ARR_FIELD['css_bol'] ?>">
							<?= empty($this->HTTP_VARS['edit_field']) ? $ARR_FIELD['value_html'] : $ARR_FIELD['value_input'] ?>
						</div>
					</div>
				</div>
			<? } ?>
		<?
			foreach ($FK as $TABLE_FK => $ARR_FK) { ?>
				<div class="" style=" width:100%;">
					<div class="flex_h flex_align_middle">
						<div class="padding    textgris" style="width:25px;">
							<i class="fa fa-<?= $ARR_FK['iconAppscheme'] ?> " style="color:<?= $ARR_FK['colorAppscheme'] ?>"></i>
						</div>
						<div class="padding borderb ellipsis textgrisfonce  text-right" style="width:calc(50% - 25px);">
							<?= ucfirst(idioma($ARR_FK['nomAppscheme'])) ?>
							<?= $Idae->cf_output('color', $ARR, 'color' . ucfirst($ARR_FK['codeAppscheme'])); ?>
						</div>
						<div class="padding  "><?= $Idae->cf_output('icon', $ARR, 'icon' . ucfirst($ARR_FK['codeAppscheme'])); ?>
							<?= $Idae->cf_output('nom', $ARR, 'nom' . ucfirst($ARR_FK['codeAppscheme'])); ?>
							<?= $Idae->cf_output('prenom', $ARR, 'prenom' . ucfirst($ARR_FK['codeAppscheme'])); ?>
						</div>
					</div>
				</div>
			<? } ?>
	</div>
</div>
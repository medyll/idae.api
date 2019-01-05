<?
	include_once($_SERVER['CONF_INC']);

	$table       = $this->table;
	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

	$APP    = new App($table);
	$APPOBJ = $APP->appobj($table_value, $vars);
	$ARR    = $APPOBJ->ARR;
	//
	$EXTRACTS_VARS = $APP->extract_vars($table_value, $vars);
	extract($EXTRACTS_VARS, EXTR_OVERWRITE);
	//

	$module_link    = 'fiche';
	$html_edit_link = '';
	if ($_SESSION['shop']) {
		$ARR_DROIT_SHOP = ['shop', 'shop_jours', 'produit'];
		if (in_array($table, $ARR_DROIT_SHOP)) {
			$html_edit_link = '<a class="button" href="#idae/module/update/table=' . $table . '&table_value=' . $table_value . '">modifier</a>';
		}
		$module_link = 'fiche';
	}
	if ($_SESSION['livreur']) {
		$ARR_DROIT_LIVREUR = ['livreur', 'livreur_affectation'];
		if (in_array($table, $ARR_DROIT_LIVREUR)) {
			$html_edit_link = '<a class="button" href="#idae/module/update/table=' . $table . '&table_value=' . $table_value . '">modifier</a>';

		}
		$module_link = 'app_fiche/app_fiche_reserv';
	}

	$iconAppscheme = $APP->iconAppscheme;

	$html_edit_link = 'table=' . $table . '&table_value=' . $table_value;

	$Idae = new Idae($table);

	$ARR_COLLECT = $Idae->get_table_fields($table_value, ['codeAppscheme_field' => ['code']]);

?>
<div class="animated fadeIn" data-table="<?= $table ?>" data-table_value="<?= $table ?>" data-module_link="<?= $module_link ?>" data-target="console_<?= $table ?>" data-vars="<?= $html_edit_link ?>">
	<div class=" blanc " data-table="<?= $table ?>" data-table_value="<?= $table_value ?>">
		<div class=" flex_h flex_wrap flex_align_middle cursor">
			<div class="console_thumb">
				<div class="padding boxshadowb text-right" style="font-size: 1.5em;">
					<?=maskHeure_sweet($ARR['heureLivraisonCommande']) ?>
				</div>
				<? foreach ($ARR_COLLECT as $key => $ARR_FIELD_GROUP) {
					?>
					<?
					foreach ($ARR_FIELD_GROUP['appscheme_fields'] as $CODE_ARR_FIELD => $ARR_FIELD) { ?>
						<div class="padding text-center">
							<span class="ellipsis text-bold h3"><?= $ARR_FIELD['value_html'] ?></span>
						</div>
					<? } ?>
				<? } ?>
				<div class="bordert live_color">
					<?= $Idae->module('fiche_has', ['table' => $table, 'table_value' => $table_value]) ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$table       = $_POST['table'];
	$table_value = (int)$_POST['table_value'];
	$vars        = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);

	//
	$APP       = new App($table);
	$Idae      = new Idae($table);
	$APP_TABLE = $APP->app_table_one;
	$APPOBJ    = $APP->appobj($table_value, $vars);
	$ARR       = $APP->findOne(["id$table" => (int)$table_value]);
	//
	$EXTRACTS_VARS = $APP->extract_vars($table_value, $vars);
	extract($EXTRACTS_VARS, EXTR_OVERWRITE);
	//
	$html_edit_link = '';
	if ($_SESSION['shop']) {
		$ARR_DROIT_SHOP = ['shop', 'shop_jours', 'produit'];
		if (in_array($table, $ARR_DROIT_SHOP)) {
			$html_edit_link = '<a class="button" href="#idae/module/update/table=' . $table . '&table_value=' . $table_value . '">modifier</a>';
		}
	}
	if ($_SESSION['livreur']) {
		$ARR_DROIT_LIVREUR = ['livreur', 'livreur_affectation'];
		if (in_array($table, $ARR_DROIT_LIVREUR)) {
			$html_edit_link = '<a class="button" href="#idae/module/update/table=' . $table . '&table_value=' . $table_value . '">modifier</a>';

		}
	}

	if ($APP->has_field("dateDebut$table")) {
		echo "date";
	}
	$Idae        = new Idae($table);
	$ARR_COLLECT = $Idae->get_table_fields($table_value);
?>
<span data-thumb_order="<?= (int)$ARR['timeFinPreparationCommande'] ?>"></span>
<div class="flex_v" style="min-height:100%;">
	<div style="">
		<?= $Idae->module('fiche_entete', ['table'       => $table,
		                                   'table_value' => $table_value]) ?>
	</div>
	<div class="flex_main ededed" style="overflow-y:auto;overflow-x:hidden;">
		<div class="padding margin">
			<div class="grid-x ">
				<div class="cell small-6 medium-6 large-6">
					<div class="padding">
						<?= $Idae->module('fiche_has', ['table' => $table, 'table_value' => $table_value]) ?>
					</div>
				</div>
				<div class="cell small-6 medium-6 large-6">
					<div class="padding">
						<?= $Idae->fiche_next_statut($table_value); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="relative text-center" style="width:100%;z-index:2000;">
			<?= $Idae->module("app_console/commande/app_console_progress", ['table'       => $table,
			                                                                'table_value' => $table_value]) ?></div>
		<? if (!empty($APPOBJ->APP_TABLE['hasLigneScheme'])): ?>
			<div class="padding margin border4 blanc">
				<?= $Idae->module('app_fiche/app_fiche_ligne', ['table' => $table . '_ligne',
				                                            'vars'  => ["id$table" => $table_value]]) ?>
			</div>
		<? endif; ?>
		<div class="padding margin border4 blanc">
			<?= $Idae->module('app_fiche/fiche_fields_table', ['table'               => $table,
			                                                   'table_value'         => $table_value,
			                                                   'show_empty'          => 0,
			                                                   'codeAppscheme_field' => ['nom', 'distance', 'dureeLivraison', 'volume']]) ?>
		</div>
		<div class="padding margin  ededed ">
			<?= $Idae->module('fiche_rfk', ['table'       => $table,
			                                'table_value' => $table_value]) ?>
		</div>
	</div>
</div>
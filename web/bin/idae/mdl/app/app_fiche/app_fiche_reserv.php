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

	$table       = $this->HTTP_VARS['table'];
	$Table       = ucfirst($table);
	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

	//
	$APP       = new App($table);
	$APP_TABLE = $APP->app_table_one;
	$APPOBJ    = $APP->appobj($table_value, $vars);
	$ARR       = $APP->findOne(["id$table" => (int)$table_value]);

	//
	$html_edit_link = '';
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
<div class="flex_v animated slideInLeft  blanc boxshadow relative" style="height:100%;max-height:100%;z-index:100;">
	<div class="flex_main" style="overflow-y:auto;overflow-x:hidden;">
		<div data-see_more_commande="commande" data-type_session="<?= $type_session ?>" data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" data-table_value="<?= $table_value ?>" class="flex_main " style="overflow-y:auto;overflow-x:hidden;">
			<?= $Idae->module('app_fiche/app_fiche_reserv_ok', ['table'       => $table,
			                                                    'table_value' => $table_value]) ?>
		</div>
	</div>
	<div class="text-center blanc bordert" style="box-shadow: 0 0 10px 10px #fff;z-index: 3000;position: relative;">
		<div class="text-center padding  ">
			<module data-act_defer="" data-mdl="idae/fiche_next_statut/<?= $table ?>/<?= $table_value ?>" data-vars="<?= "table=$table&table_value=$table_value" ?>"></module>
		</div>
	</div>
</div>
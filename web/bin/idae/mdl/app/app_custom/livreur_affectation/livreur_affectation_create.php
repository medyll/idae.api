<?
	include_once($_SERVER['CONF_INC']);

	$table                 = $this->HTTP_VARS['table'];
	$Table                 = ucfirst($table);
	$vars                  = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$http_vars             = $this->translate_vars($vars);
	$type_date             = "dateDebut";
	$type_date_field       = "$type_date$Table";
	$type_date_field_value = empty($vars[$type_date_field]) ? date('Y-m-d') : $vars[$type_date_field];
	$date_str              = function_prod::jourMoisDate_fr($type_date_field_value);

	$calId          = 'cal_id_table' . $table;
	$calReport      = 'cal_report' . $table;
	$calInput       = 'cal_input_' . $table;
	$calInput_count = 'cal_input_count_' . $table;
	//
	$APP            = new App($table);
	$Idae           = new Idae($table);
	//
	$APP_TABLE = $APP->app_table_one;
	$GRILLE_FK = $APP->get_grille_fk();
	//
	$id     = 'id' . $table;
	$ct     = $APP->find($vars + [$type_date . $Table => $type_date_field_value])->count();
	$ct_css = ($ct != 0) ? 'nosne' : ' ';

	$ARR_COLLECT = $Idae->get_table_fields(null, ['nocodeAppscheme_field' => ['nom', 'heureFin', 'heureDebut', 'dateFin', 'dateDebut']]);

	$F_action = ($ct != 0) ? 'app_update' : 'app_create';


?>
<div class="flex_v blanc" id="for_form_livreur_affectation" style="overflow: hidden;">
	<div class="flex_main flex_v" style="overflow-y: auto;overflow-x:hidden;">
		<div id="<?= $calId ?>" class="relative blanc boxshadowb">
			<?= $Idae->module('app_calendrier/app_calendrier', array_merge($this->HTTP_VARS, ['mode' => 'fiche'])) ?>
		</div>
		<div>
			<?= $Idae->module('app_custom/livreur_affectation/livreur_affectation_create_form', array_merge($this->HTTP_VARS, ['mode' => 'fiche'])) ?>
		</div>
	</div>
	<div class="<?= $ct_css ?> towohs bordert flex_h flex_align_middle">
		<div class="text-center  padding_more cursor flex_main">
			<div class="item_icon item_icon_shadow more inline text-green" onclick="validate_liv_affect()">
				<i class="fa fa-check fa-2x"></i>
			</div>
		</div>
		<div class="text-center  padding_more cursor flex_main" data-module_link="app_liste/app_liste" data-vars='<?= "table=$table&$http_vars" ?>'>
			<div class="item_icon item_icon_shadow more inline">
				<i class="fa fa-times fa-2x"></i>
			</div>
		</div>
	</div>
</div>
<script>
	validate_liv_affect = function () {
		$ ('#form_PM').find ('[type="submit"]').trigger ('click');
		$ ('#form_AM').find ('[type="submit"]').trigger ('click');
	}
</script>
<script>
	$ ('#<?= $calId ?>').on ('dom:act_click', (event, eventData)=> {
		const date_us = eventData.value_us;
		var vars      = `vars[<?=$type_date?><?=ucfirst($table)?>]=${date_us}`;
		reloadModule ('idae/module/app_custom/livreur_affectation/livreur_affectation_create_form', '*', vars)

	});
</script>
<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 24/12/2017
	 * Time: 16:14
	 */

	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$arr_allowed_c = droit_table($type_session, 'C', $table);
	$arr_allowed_r = droit_table($type_session, 'R', $table);
	$arr_allowed_u = droit_table($type_session, 'U', $table);
	$arr_allowed_d = droit_table($type_session, 'D', $table);
	$arr_allowed_l = droit_table($type_session, 'L', $table);

	$table = $_POST['table'];
	$Table = ucfirst($table);

	$vars    = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);
	$http_vars = $this->translate_vars($vars);
	$groupBy = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	$page    = (!isset($_POST['page'])) ? 0 : $_POST['page'];
	$nbRows  = (empty($_POST['nbRows'])) ? empty($settings_nbRows) ? 10 : (int)$settings_nbRows : $_POST['nbRows'];

	$Idae = new Idae($table);
	$APP  = new App($table);
	//
	$APP_TABLE = $APP->app_table_one;
	$type_date = $APP->has_field('dateDebut') ? 'dateDebut' : 'date';
	$APPOBJ    = $APP->appobj(null, $vars);
	$name_id   = "id$table";

	$arr_sort_by = array_filter([$sortBy => $sortDir, $sortBySecond => $sortDirSecond]);
	$rs_app      = $APP->find($vars)->sort($arr_sort_by)->skip(($nbRows * $page))->limit($nbRows);

	$type_fiche = ($table == 'produit') ? 'fiche_mini' : 'fiche_micro';

?>
<div style="height:100%;width:100%;overflow:hidden;">
	<div class="flex_v" style="overflow:auto">
		<div class="boxshadowb relative">
			<div class="boxshadowb borderb blanc">
				<div id="call"><?= $Idae->module('app_calendrier/app_calendrier', ['mode' => 'fiche', 'table' => $table, 'table_value' => $table_value]) ?></div>
			</div>
		</div>
		<div id="cal_ente"></div>
		<div id="cal_li" class="relative flex_main"></div>
		<div id="cal_craete"></div>
	</div>
</div>
<script>
	$ ('#call').on ('dom:act_click', (event, eventData)=> {
		var vars = `table=<?=$table?>&<?=$http_vars?>&vars[<?=$type_date?><?=ucfirst($table)?>]=${eventData.value_us}`;

		$ ("#cal_li").loadModule ('idae/module/app_liste/app_liste_inner/' + vars);
		$ ("#cal_ente").loadModule ('idae/module/app_liste/app_liste_entete/' + vars);
		$ ("#cal_craete").loadModule ('idae/module/app_fiche/app_fiche_menu/' + vars);
	});
	$ ("#cal_li").loadModule ('idae/module/app_liste/app_liste_inner/table=<?=$table?>&<?=$http_vars?>&vars[<?=$type_date?><?=ucfirst($table)?>]=<?=date('Y-m-d')?>');
	$ ("#cal_ente").loadModule ('idae/module/app_liste/app_liste_entete/table=<?=$table?>&<?=$http_vars?>&vars[<?=$type_date?><?=ucfirst($table)?>]=<?=date('Y-m-d')?>');
	$ ("#cal_craete").loadModule ('idae/module/app_fiche/app_fiche_menu/table=<?=$table?>&<?=$http_vars?>&vars[<?=$type_date?><?=ucfirst($table)?>]=<?=date('Y-m-d')?>');
</script>
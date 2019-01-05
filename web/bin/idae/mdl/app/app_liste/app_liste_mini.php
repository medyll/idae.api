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

	$table = $this->HTTP_VARS['table'];
	$Table = ucfirst($table);

	$vars    = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$groupBy = empty($this->HTTP_VARS['groupBy']) ? '' : $this->HTTP_VARS['groupBy'];
	$page    = (!isset($this->HTTP_VARS['page'])) ? 0 : $this->HTTP_VARS['page'];
	$nbRows  = (empty($this->HTTP_VARS['nbRows'])) ? empty($settings_nbRows) ? 10 : (int)$settings_nbRows : $this->HTTP_VARS['nbRows'];

	$Idae = new Idae($table);
	$APP  = new App($table);
	//
	$APP_TABLE = $APP->app_table_one;

	$APPOBJ  = $APP->appobj(null, $vars);
	$name_id = "id$table";

	$sortBy        = empty($this->HTTP_VARS['sortBy']) ? empty($APP_TABLE['sortFieldName']) ? $APP->nomAppscheme : $APP_TABLE['sortFieldName'] : $this->HTTP_VARS['sortBy'];
	$sortDir       = empty($this->HTTP_VARS['sortDir']) ? empty($APP_TABLE['sortFieldOrder']) ? 1 : (int)$APP_TABLE['sortFieldOrder'] : (int)$this->HTTP_VARS['sortDir'];
	$sortBySecond  = empty($this->HTTP_VARS['sortBySecond']) ? empty($APP_TABLE['sortFieldSecondName']) ? 'dateCreation' . $Table : $APP_TABLE['sortFieldSecondName'] : $this->HTTP_VARS['sortBySecond'];
	$sortDirSecond = empty($this->HTTP_VARS['sortDirSecond']) ? empty($APP_TABLE['sortFieldSecondOrder']) ? 1 : (int)$APP_TABLE['sortFieldSecondOrder'] : (int)$this->HTTP_VARS['sortDirSecond'];

	$arr_sort_by = array_filter([$sortBy => $sortDir, $sortBySecond => $sortDirSecond]);
	$rs_app      = $APP->find($vars)->sort($arr_sort_by)->skip(($nbRows * $page))->limit($nbRows);

	$count_tot  = $rs_app->count();
	$count_rows = $rs_app->count(true);
	$nbPage     = ceil($count_tot / $nbRows);

	$type_fiche = ($table == 'produit') ? 'fiche_mini' : 'fiche_micro';
	$type_fiche = ($table == 'shop_jours_shift') ? 'fiche_mini' : 'fiche_micro';
	$type_fiche = ($table == 'livreur_affectation') ? 'fiche_mini' : $type_fiche;
	$type_fiche = 'app_fiche/app_fiche_fields_table';
	?>
	<div class="flex_h flex_wrap flex_col_2 ededed">
		<?
			while ($arr_app = $rs_app->getNext()) {
				$value = $arr_app[$name_id];
				?>
				<div class="padding ">
					<div class="padding border4 blanc">
						<?=$Idae->module($type_fiche, "table=$table&table_value=$value");?>
					</div>
				</div>
				<? } ?>
	</div>
<?

	if ($count_tot != $count_rows && $page < $nbPage) {
		$idnext                  = "next$page$table";
		$this->HTTP_VARS['page'] = $page + 1;
		?>
		<div>
			<div  onclick="$(this).fadeOut();" class="text-center padding_more" data-module_link="app_liste/app_liste_inner" data-target="<?= $idnext ?>" data-vars="<?= http_build_query($this->HTTP_VARS) ?>" >
				<i class="fa fa-chevron-down item_icon_more fa-2x"></i>
			</div>
			<div id="<?= $idnext ?>">
			</div>
		</div>
	<? }

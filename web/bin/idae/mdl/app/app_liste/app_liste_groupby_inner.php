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

	if (empty($this->HTTP_VARS['groupBy'])) {
		if ($table == 'livreur_affectation') {
			$this->HTTP_VARS['groupBy'] = "dateDebut$Table";
			$field_raw_group_by         = 'dateDebut';
		}
		if ($table == 'shop_jours_shift') {
			$this->HTTP_VARS['groupBy'] = 'shop_jours';
		}
		if ($table == 'commande') {
			$this->HTTP_VARS['groupBy'] = 'shop_jours_shift_run';
		}
		if ($table == 'produit') {
			$this->HTTP_VARS['groupBy'] = 'produit_categorie';
		}
	}
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

	if (!empty($groupBy)):
		if (!empty($APPOBJ->GRILLE_FK[$groupBy])) {
			$APP_GROUPBY = new App($groupBy);
			$rs_dist     = $APP->distinct($groupBy, $vars, $nbRows);
			$rs_distall  = $APP->distinct_all("id$groupBy", $vars);

			$sortByGB        = $APP_GROUPBY->app_table_one['sortFieldName'];
			$sortDirGB       = (int)$APP_GROUPBY->app_table_one['sortFieldOrder'];
			$sortBySecondGB  = $APP_GROUPBY->app_table_one['sortFieldSecondName'];
			$sortDirSecondGB = (int)$APP_GROUPBY->app_table_one['sortFieldSecondOrder'];

			$arr_sort_groupby = array_filter([$sortByGB => $sortDirGB, $sortBySecondGB => $sortDirSecondGB]);

			$rsGroupBy      = $APP_GROUPBY->find(["id$groupBy" => ['$in' => $rs_distall]])->sort($arr_sort_groupby)->limit(10);
			$dist_count     = $rs_dist->count();
			$dist_max_count = $rs_dist->count(true);

		} else {
			$rs_dist        = $APP->distinct_all($groupBy, array_merge($vars));
			$rsfetch        = $APP->find($vars)->sort($arr_sort_by)->skip(($nbRows * $page))->limit($nbRows);
			$fetch          = array_column(iterator_to_array($rsfetch), $groupBy);
			$rsGroupBy      = array_unique($fetch);
			$dist_count     = (sizeof($rs_dist) > $nbRows) ? $nbRows : sizeof($rs_dist);
			$dist_max_count = sizeof($rs_dist);

		}
	endif;

	$rs_app = $APP->find($vars)->sort($arr_sort_by)->skip(($nbRows * $page))->limit($nbRows);

	$type_fiche = ($table == 'produit') ? 'fiche_mini' : 'fiche_micro';
	$type_fiche = ($table == 'livreur_affectation') ? 'fiche_mini' : $type_fiche;
?>
<div class=" ">
	<?
	?>
	<div class="padding_more text-left  flex_main">
		<div class="list_thumb_icon ">
			<?= $groupBy ?>
		</div>
	</div>
	<div class="flex_h flex_align_middle flex_wrap flex_col_4">
	<?

		if (  !empty($rs_dist)):
			foreach ($rsGroupBy as $keyBy => $arr_groupby):
				if (!empty($arr_groupby["id$groupBy"])) {
					$vars["id$groupBy"] = (int)$arr_groupby["id$groupBy"]; // unset apres
					$new_vars           = array_merge($this->HTTP_VARS, ['vars' => ["id$groupBy" => $arr_groupby['id' . $groupBy]]]);
				} else {
					$vars["$groupBy"] = $arr_groupby;
					$new_vars         = array_merge($this->HTTP_VARS, ['vars' => ["$groupBy" => $arr_groupby]]);

				}

				if (!empty($arr_groupby["id$groupBy"])) {
					echo $Idae->module('app_liste/app_liste_groupby_inner_entete', "table=$groupBy&table_value=" . $arr_groupby['id' . $groupBy]);
				} else {
					$titre = $APP->cf_output_icon($field_raw_group_by);

					$text = (strpos($groupBy, 'date') !== false) ? function_prod::jourMoisDate_fr($arr_groupby) : $arr_groupby;
					echo '<div class="padding_more borderb  boxshadowb">';
					echo '<h4 class="padding_more flex_h flex_align_middle">';
					echo $titre . '   ' . $text;
					echo '</h4>';
					echo '</div>';
				}
				if (!empty($GRILLE_FK[$groupBy])) {
					$vars['id' . $groupBy] = (int)$arr_groupby['id' . $groupBy]; // unset apres
				}

				unset($vars['id' . $groupBy]);
				unset($vars[$groupBy]);

			endforeach;
		endif;
	?></div>
</div>

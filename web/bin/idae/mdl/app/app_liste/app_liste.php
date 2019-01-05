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
			$field_raw_group_by = 'dateDebut';
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

    //var_dump($vars);

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
			$rs_dist   = $APP->distinct_all($groupBy, array_merge($vars));
			$rsfetch   = $APP->find($vars)->sort($arr_sort_by)->skip(($nbRows * $page))->limit($nbRows);
			$fetch     = array_column(iterator_to_array($rsfetch), $groupBy);
			$rsGroupBy = array_unique($fetch);
			$dist_count     = (sizeof($rs_dist) > $nbRows) ? $nbRows : sizeof($rs_dist);
			$dist_max_count = sizeof($rs_dist);

		}
	endif;

	$rs_app      = $APP->find($vars)->sort($arr_sort_by)->skip(($nbRows * $page))->limit($nbRows);

	$type_fiche = ($table == 'produit') ? 'fiche_mini' : 'fiche_micro';
	$type_fiche = ($table == 'livreur_affectation') ? 'fiche_mini' : $type_fiche;
?>
<div class="flex_v">
	<div style="margin-top:0;z-index:1000">
		<?= $Idae->module('app_liste/app_liste_entete_search', $this->HTTP_VARS); ?>
	</div>
	<div id="main_item_search_input" class="avoid borderb    " style="display:none;">
		<div class="flex_h flex_align_middle">
			<div class="text-center borderr">
				<div class="padding_more"><i class="fa fa-<?= $APPOBJ->ICON ?>" style="color:<?= $APPOBJ->ICON_COLOR ?>"></i></div>
			</div>
			<div class="flex_main  padding_more">
				<form onsubmit="main_item_search.load_data($(this).serialize());$('#main_item_search_zone').toggleContent();return false;">
					<input type="hidden" name="table" value="<?= $table ?>"/>
					<input type="hidden" name="stream_to" value="stream_to_<?= $table ?>_liste"/>
					<div class="relative flex_h boxshadowb border4">
						<input placeholder="Recherche <?= $APP->nomAppscheme ?>" name="search"
						       style="position: relative;height:auto;font-size:1.7em;margin-right:0px;z-index:1;width:100%;"
						       type="text" class="flex_main color-base-dark text-center"/>
						<div   style="z-index: 10;">
							<button style="border: none;background-color: transparent;" type="submit"><i class="fa fa-search fa-2x"></i></button>
						</div>
					</div>
				</form>
			</div>
			<div class="text-center cursor" onclick="$('#main_item_search_zone').unToggleContent();$('#main_item_search_input').hide();">
				<div class="padding_more"><i class="fa fa-times fa-2x" style="color:darkred"></i></div>
			</div>
		</div>
	</div>
	<div id="main_item_search_zone" class="flex_main" style="overflow:hidden;width:100%;height:100%;display:none;">
		<div class="flex_v padding_more" style="height:100%;">
			<div class="padding color_fond_noir  alignright  flex_h  ">
				<a class="flex_main padding color_fond_noir" onclick="$('#main_item_search_zone').unToggleContent();$('#main_item_search_input').hide();">
					<?= idioma('Recherche terminée') ?></a>
			</div>
			<div class="blanc flex_main flex_h flex_wrap flex_align_top" style="overflow:hidden;">
				<div class="flex_main boxshadow relative" id="patolaon" style="overflow:auto;height:100%;">
				</div>
			</div>
		</div>
		<script>
			main_item_search = new BuildSearch ($ ('#patolaon'));
		</script>
	</div>
	<div class="flex_main " style="overflow-y:auto;overflow-x:hidden;max-width:100%;">
		<?
			if (!empty($this->HTTP_VARS['type_liste'])) { ?>
				<div class="padding_more ededed flex_main">
					<a class="item_icon more">
						<?= $this->HTTP_VARS['type_liste']; ?>
					</a>
				</div>
			<? }

			if (!empty($groupBy) && !empty($rs_dist)):
				foreach ($rsGroupBy as $keyBy=>$arr_groupby):
					if (!empty($arr_groupby["id$groupBy"])) {
						$vars["id$groupBy"] = (int)$arr_groupby["id$groupBy"]; // unset apres
						$new_vars           = array_merge($this->HTTP_VARS, ['vars' => ["id$groupBy" => $arr_groupby['id' . $groupBy]]]);
					} else {
						$vars["$groupBy"] = $arr_groupby;
						$new_vars         = array_merge($this->HTTP_VARS, ['vars' => ["$groupBy" => $arr_groupby]]);

					}

					if (!empty($arr_groupby["id$groupBy"])) {
						echo $Idae->module('liste_groupby_entete', "table=$groupBy&table_value=" . $arr_groupby['id' . $groupBy]);
					} else {
						$titre=$APP->cf_output_icon($field_raw_group_by);
						//$titre = (strpos($groupBy,'date')!==false)? '<i class="fa fa-calendar"></i>' : $groupBy ;
						$text = (strpos($groupBy,'date')!==false)? function_prod::jourMoisDate_fr($arr_groupby) : $arr_groupby ;
						echo '<div class="padding_more borderb  boxshadowb">';
							echo '<h4 class="padding_more flex_h flex_align_middle">';
								echo $titre.'   '.$text;
							echo '</h4>';
						echo '</div>';
					}
					if (!empty($GRILLE_FK[$groupBy])) {
						$vars['id' . $groupBy] = (int)$arr_groupby['id' . $groupBy]; // unset apres
					}
					echo "<div class=' ' style='margin-left:20px; ' >";
					echo $Idae->module('app_liste/app_liste_inner', $new_vars);
					echo "</div>";
					unset($vars['id' . $groupBy]);
					unset($vars[$groupBy]);

				endforeach;
			else:

				echo $Idae->module('app_liste/app_liste_inner', $this->HTTP_VARS);
			endif;
		?>
	</div>
	<div>
		<?= $Idae->module('app_liste/app_liste_menu', "table=$table&table_value=$value"); ?>
	</div>
</div>

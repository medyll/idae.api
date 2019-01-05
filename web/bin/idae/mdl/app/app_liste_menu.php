<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 22/05/14
	 * Time: 03:24
	 */
	include_once($_SERVER['CONF_INC']);
	//
	$table = $_POST['table'];
	$Table = ucfirst($table);

	$nom = 'nom' . ucfirst($table);

	$vars         = empty($_POST['vars']) ? [ ] : function_prod::cleanPostMongo($_POST['vars'] , 1);
	$groupBy      = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	$sortBy       = empty($_POST['sortBy']) ? $nom : $_POST['sortBy'];
	$sortDir      = empty($_POST['sortDir']) ? 1 : (int)$_POST['sortDir'];
	$vars_noagent = $vars;
	unset($vars_noagent['idagent']);
	//
	$APP         = new App($table);
	$Idae        = new Idae($table);
	$ARR_COLLECT = $Idae->get_table_fields();
	//
	$APP_TABLE       = $APP->app_table_one;
	$GRILLE_FK       = $APP->get_grille_fk($table);
	$HTTP_VARS       = $APP->translate_vars($vars);//http_build_query($_POST) .'&' .$APP->translate_vars($vars);
	//
	$HTTP_BASE_VARS = http_build_query(array_filter([ 'table' => $table , 'sortBy' => $sortBy , 'sortDir' => $sortDir , 'groupBy' => $groupBy ]));
	//
	$settings_button_group = empty($groupBy) ? $APP->get_settings($_SESSION['idagent'] , 'list_data_button_group' , $table) : $groupBy;
	$settings_button_sort  = empty($sortBy) ? $APP->get_settings($_SESSION['idagent'] , 'list_data_button_sort' , $table) : $sortBy;


	$ARR_LINK_GROUPBY = [ ];
	$arr_has          = [ 'statut' , 'type' , 'categorie' , 'groupe' ];
	foreach ($arr_has as $key => $value):
		$Value  = ucfirst($value);
		$_table = $table . '_' . $value;
		if ( !empty($APP_TABLE['has' . $Value . 'Scheme']) ):
			$ARR_LINK_GROUPBY [$_table] = $value;
		endif;
	endforeach;
	if ( sizeof($GRILLE_FK) != 0 ):
		foreach ($GRILLE_FK as $fk):
			$ARR_LINK_GROUPBY [$fk['table_fk']] = $fk['nomAppscheme'];
		endforeach;
	endif;
?>
<div class = "boxshadow   relative toggler applink borderb">
	<div class = "flex_h   flex_margin flex_padding    flex_align_middle">
		<div class = "flex_grow_1">
			<? if ( !empty($APP_TABLE['hasTypeScheme']) || sizeof($GRILLE_FK) != 0 ):
				$css = (!empty($settings_button_group)) ? 'active' : '';
				?>
				<div class = "padding_more">
					<a data-toggle="menuBar<?=$table?>" data-menu = "data-menu" class = "<?= $css ?> aligncenter ellipsis padding_more">
						<i class = "fa fa-database" style = "color: #BEAC8B"></i>
						Grouper <?= $settings_button_group ?>
					</a>
				</div>
				<div id="menuBar<?=$table?>" data-toggler class = "toggler boxshadow   contextmenu applinkblock hide_on_click" style = "display: none ;z-index:1000;">
					<div class = "flex_h flex_wrap flex_col_2 ">
						<?
							foreach ($ARR_LINK_GROUPBY as $key_link => $value_link):
								$css = ($settings_button_group == $key_link) ? 'active' : '';
								?>
								<div class = "padding_more flex_grow_1">
									<a class="<?= $css ?>" data-act_target="mdl_liste" data-module_link="app_liste/app_liste"
									   data-vars = "<?= $HTTP_BASE_VARS ?>&groupBy=<?= $key_link ?>&<?= $HTTP_VARS ?>">
										<?= $value_link ?>
									</a>
								</div>
							<? endforeach; ?>
					</div>
					<div class = "padding_more">
						<a data-act_target="mdl_liste" data-module_link="app_liste/app_liste" data-vars="<?= $HTTP_BASE_VARS ?>&groupBy=&<?= $HTTP_VARS ?>">
							ne plus grouper
						</a>
					</div>
				</div>
			<? endif; ?>
		</div>
		<div class = "borderl flex_h flex_padding flex_align_middle flex_grow_1">
			<a class = " aligncenter ellipsis" data-menu = "data-menu">
				<i class = "fa fa-unsorted" style = "color: #BEAC8B"></i>
				Trier par <?=$sortBy ?>
			</a>
			<div class = "blanc absolute" style = "display: none ;z-index:1000;">
				<div class = "flex_h flex_wrap flex_col_3 ">
					<? foreach ($ARR_COLLECT as $key => $ARR_FIELD_GROUP) {
						?>
						<?
						foreach ($ARR_FIELD_GROUP['appscheme_fields'] as $CODE_ARR_FIELD => $ARR_FIELD) { ?>
							<div class="padding_more cursor flex_grow_1" data-act_target="mdl_liste" data-module_link="app_liste/app_liste"
							     data-vars = "<?= $HTTP_BASE_VARS ?>&sortBy=<?= $key_link ?>&<?= $HTTP_VARS ?>">
								<div><i class = "fa fa-<?= $ARR_FIELD['icon'] ?> item_icon"></i> <?= ucfirst(idioma($ARR_FIELD['nom'])) ?></div>
							</div>
						<? } ?>
					<? } ?>
					<?
						foreach ($ARR_LINK_GROUPBY as $key_link => $value_link):
							$css = ($settings_button_group == $key_link) ? 'active' : '';
							?>
							<div class = "padding_more cursor flex_grow_1">
								<a class="<?= $css ?>" data-act_target="mdl_liste" data-module_link="app_liste/app_liste"
								   data-vars = "<?= $HTTP_BASE_VARS ?>&sortBy=<?= $key_link ?>&<?= $HTTP_VARS ?>">
									<?= $value_link ?>
								</a>
							</div>
						<? endforeach; ?>
				</div>
			</div>
			<div class = "flex_h flex_padding flex_align_middle toggler">
				<a class = "autoToggle bordert" app_button = "app_button" vars = "<?= $HTTP_BASE_VARS ?>&sortDir=<?= (($sortDir == 1) ? '1' : '-1'); ?>&<?= $HTTP_VARS ?>">
					<i class = "fa fa-caret-<?= (($sortDir == 1) ? 'up' : 'down'); ?>"></i>
				</a>
				<a class = "autoToggle borderb" app_button = "app_button" vars = "<?= $HTTP_BASE_VARS ?>&sortDir=<?= (($sortDir == 1) ? '-1' : '1'); ?>&<?= $HTTP_VARS ?>">
					<i class = "fa fa-caret-<?= (($sortDir == 1) ? 'down' : 'up'); ?>"></i>
				</a>
			</div>
		</div>
	</div>
</div>
<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 24/12/2017
	 * Time: 16:14
	 */

	include_once($_SERVER['CONF_INC']);

	// ini_set('display_errors', 55);
	$table = $this->table;;
	$Table = ucfirst($table);

	$vars       = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$groupBy    = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	$page       = (!isset($_POST['page'])) ? 0 : $_POST['page'];
	$type_liste = (!isset($_POST['type_liste'])) ? 0 : $_POST['type_liste'];
	$nbRows     = (empty($_POST['nbRows'])) ? empty($settings_nbRows) ? 20 : (int)$settings_nbRows : $_POST['nbRows'];

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$Idae        = new Idae($table);
	$APP         = new App($table);
	$APP_SESSION = new App($type_session);
	$ARR_SESSION = $APP_SESSION->findOne([$name_idtype_session => $idtype_session]);
	//
	$APP_TABLE = $APP->app_table_one;

	$APPOBJ      = $APP->appobj(null, $vars);
	$ARR         = $APPOBJ->ARR;
	$idappscheme = (int)$APP->idappscheme;
	$name_id     = "id$table";

	$sortBy        = empty($_POST['sortBy']) ? empty($APP_TABLE['sortFieldName']) ? $APP->nomAppscheme : $APP_TABLE['sortFieldName'] : $_POST['sortBy'];
	$sortDir       = empty($_POST['sortDir']) ? empty($APP_TABLE['sortFieldOrder']) ? 1 : (int)$APP_TABLE['sortFieldOrder'] : (int)$_POST['sortDir'];
	$sortBySecond  = empty($_POST['sortBySecond']) ? empty($APP_TABLE['sortFieldSecondName']) ? 'dateCreation' . $Table : $APP_TABLE['sortFieldSecondName'] : $_POST['sortBySecond'];
	$sortDirSecond = empty($_POST['sortDirSecond']) ? empty($APP_TABLE['sortFieldSecondOrder']) ? 1 : (int)$APP_TABLE['sortFieldSecondOrder'] : (int)$_POST['sortDirSecond'];

	if (!empty($vars['idlivreur'])) {
		$vars['idlivreur'] = ['$in' => [(int)$vars['idlivreur'], null, 0, '']];
		$vars['idsecteur'] = (int)$ARR_SESSION['idsecteur'];
		//Helper::dump($vars);
	}
	$rs_app = $APP->find($vars + ['code' . $Table . '_statut' => ['$nin' => ['END']], "dateCreation$Table" => date('Y-m-d')])->sort([$sortBy => $sortDir, $sortBySecond => $sortDirSecond])->skip(($nbRows * $page))->limit($nbRows);
	//$rs_app = $APP->find($vars)->sort([$sortBy => $sortDir, $sortBySecond => $sortDirSecond])->skip(($nbRows * $page))->limit($nbRows);

	$Idae               = new Idae($table);
	$IDAE_SELF          = new Idae($type_session);
	$html_entete        = $Idae->module('liste_entete', "table=$table&table_value=$table_value");
	$html_entete_fields = $IDAE_SELF->module('fiche_fields', "edit_field=true&codeAppscheme_field_type=bool&table=$type_session&table_value=" . $_SESSION["id$type_session"]);
	$ARR_SELF           = $IDAE_SELF->findOne([$name_idtype_session => $idtype_session]);
	if ($type_session == 'shop') {
		$actifCss   = empty($ARR_SELF['actifShop']) ? '' : 'active';
		$inactifCss = empty($ARR_SELF['actifShop']) ? 'active' : '';
	}
	$mdl = ($type_session == 'livreur') ? 'app_fiche/app_fiche_reserv' : 'app_console_thumb';

	$Notify = new Notify();
	if (!empty($ARR_SESSION['idlivreur'])) $Notify->notify_commande_livreur((int)$ARR_SESSION['idlivreur']);
	if (!empty($ARR_SESSION['idsecteur'])) $Notify->notify_livreur_affect($ARR_SESSION['idsecteur']);
	if (!empty($ARR_SESSION['idsecteur'])) $Notify->notify_commande_secteur($ARR_SESSION['idsecteur']);
	if (!empty($ARR_SESSION['idshop']))    $Notify->notify_commande_shop($ARR_SESSION['idshop']);

	$css_entete = ($type_session == 'livreur') ? 'none' : ' ';

	$notify_count_id = ($type_session == 'shop') ?'idshop ' : 'idsecteur';
	$notify_count = ($type_session == 'shop') ?'shop ' : 'idsecteur';
?>
<div class="flex_v" style="height:100%;overflow:hidden;">
	<div class="padding align-center flex_h flex_align_middle flex_padding boxshadowb <?=$css_entete ?>">
		<div>
			<i class="fa fa-<?= $APPOBJ->ICON ?> fa-2x"></i>
		</div>
		<div style="font-size: 3em" class="text-bold" data-count_<?=$type_session?>="commande" data-<?=$notify_count_id?>="<?= $ARR_SESSION[$notify_count_id] ?>" data-type_session="<?= $type_session ?>" data-<?= $name_idtype_session ?>="<?= $idtype_session ?>">
			<?= $rs_app->count(); ?>
		</div>
		<div class=" flex_main">
				Console  <?= $APP->nomAppscheme ?>
		</div>
		<div class="flex_main">
			<div class="flex_h flex_align_middle">
			</div>
		</div>
		<? if ($type_session == 'shop') { ?>
			<div class="flex_h flex_align_middle ">
				<div class="padding_more">Actif</div>
				<div class="flex_h flex_align_middle border4" data-toggler>
					<div class="button_app_console cursor autoToggle <?= $actifCss ?>" data-table="shop" data-table_value="<?= $idtype_session ?>" data-action="app_update" data-field="vars[actifShop]" data-value="1">
						oui
					</div>
					<div class="button_app_console cursor autoToggle <?= $inactifCss ?>" data-table="shop" data-table_value="<?= $idtype_session ?>" data-action="app_update" data-field="vars[actifShop]" data-value="0">
						non
					</div>
				</div>
			</div>
			<div class="flex_h flex_align_middle ">
				<div class="padding_more  "><i class="fa fa-clock-o fa-2x"></i></div>
				<div class="flex_h flex_align_middle border4" data-toggler>
					<? foreach (['0', '10', '20'] as $key => $value) {
						$actifCss = ($value == $ARR_SELF['tempsAttenteShop']) ? 'active' : '';
						?>
						<div class="button_app_console cursor autoToggle <?= $actifCss ?>" data-table="shop" data-table_value="<?= $idtype_session ?>" data-action="app_update" data-field="vars[tempsAttenteShop]" data-value="<?= $value ?>">+ <?= $value ?></div>
					<? } ?>
				</div>
			</div>
		<? } ?>
	</div>
	<div class="flex_main" style="overflow: hidden;">
		<div class="flex_h" style="height:100%;overflow:hidden;">
			<div class="<?= ($type_session == 'shop') ? 'borderr' : 'flex_main'; ?>" style="height:100%;overflow-y:auto;overflow-x:hidden;">
				<div data-idsecteur="<?= $ARR_SESSION['idsecteur'] ?>" data-console_liste="" data-table="<?= $table ?>" data-type_session="<?= $type_session ?>" data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" data-type_liste="<?= $type_liste; ?>" class="console_gutter flex_v">
					<?
						while ($arr_app = $rs_app->getNext()) {
							$value = $arr_app[$name_id];
							echo $Idae->module($mdl, "table=$table&table_value=$value");
						}
						$rs_app->reset(); ?>
				</div>
			</div>
			<? if ($type_session == 'shop') { ?>
				<div class="flex_main">
				<div  data-console_liste_detail="true" data-table="<?= $table ?>" data-type_session="<?= $type_session ?>" data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" id="console_<?= $table ?>" style="height: 100%;overflow:hidden;" class="">
				</div>
				</div>
			<? } ?>
		</div>
	</div>
</div>

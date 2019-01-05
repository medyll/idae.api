<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 24/12/2017
	 * Time: 16:14
	 */

	include_once($_SERVER['CONF_INC']);

	// ini_set('display_errors', 55);
	$table = $_POST['table'];
	$Table = ucfirst($table);

	$vars       = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);
	$groupBy    = empty($_POST['groupBy']) ? '' : $_POST['groupBy'];
	$page       = (!isset($_POST['page'])) ? 0 : $_POST['page'];
	$type_liste = (!isset($_POST['type_liste'])) ? 0 : $_POST['type_liste'];
	$nbRows     = (empty($_POST['nbRows'])) ? empty($settings_nbRows) ? 20 : (int)$settings_nbRows : $_POST['nbRows'];

	// Helper::dump($vars);
	// Helper::dump($this->HTTP_VARS['vars']);
	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$Idae                = new Idae($table);
	$APP                 = new App($table);
	$APP_SCHEME          = new App('appscheme');
	$APP_FIELD           = new App('appscheme_field');
	$APP_HAS_FIELD       = new App('appscheme_has_field');
	$APP_HAS_TABLE_FIELD = new App('appscheme_has_table_field');
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

	$rs_app = $APP->find($vars)->sort([$sortBy => $sortDir, $sortBySecond => $sortDirSecond])->skip(($nbRows * $page))->limit($nbRows);

	//
	$RS_HAS_FIELD       = $APP_HAS_FIELD->find(['idappscheme' => (int)$idappscheme]);
	$RS_HAS_TABLE_FIELD = $APP_HAS_TABLE_FIELD->find(['idappscheme' => (int)$idappscheme])->sort(['ordreAppscheme_has_table_field' => 1]);
	$RS_HAS_MINI_FIELD  = $APP_HAS_FIELD->find(['idappscheme' => (int)$idappscheme, 'in_mini_fiche' => 1])->sort(['ordreAppscheme_has_table_field' => 1]);

	$ARR_HAS_TABLE_FIELD = iterator_to_array($RS_HAS_TABLE_FIELD);

	foreach ($RS_HAS_MINI_FIELD as $ARR_HAS_FIELD): // tout les champs declarés dans skel.
		$ARR_FIELD    = $APP_FIELD->findOne(['idappscheme_field' => (int)$ARR_HAS_FIELD['idappscheme_field']]);
		$fieldModel[] = ['field_name'       => $ARR_FIELD['codeAppscheme_field'] . $Table,
		                 'field_name_raw'   => $ARR_FIELD['codeAppscheme_field'],
		                 'field_name_group' => $ARR_FIELD['codeAppscheme_field_group'],
		                 'iconAppscheme'    => $ARR_FIELD['iconAppscheme_field'],
		                 'icon'             => $ARR_FIELD['iconAppscheme_field'],
		                 'title'            => $ARR_FIELD['nomAppscheme_field']];
	endforeach;

	$Idae = new Idae($table);
	echo $Idae->module('liste_entete', "table=$table&table_value=$table_value");

?>
<div class="padding_more ededed flex_h flex_col_2  " style="z-index: 500;">
	<a class="item_icon_more">
		<? if (!empty($type_liste)) { ?>    <?= $type_liste; ?><? } ?>
	</a>
	<div>
		<?= $rs_app->count() ?>
	</div>
</div>
<div class="grid-x " style="margin-top:2em;">
	<div class="cell small-3">
		<div data-idsecteur="54725" data-table="<?= $table ?>" data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" data-type_liste="<?= $type_liste; ?>">
			<?
				while ($arr_app = $rs_app->getNext()) {
					$value = $arr_app[$name_id];
					echo $Idae->module('fiche_micro', "table=$table&table_value=$value");
				} $rs_app->reset();?>
		</div>
	</div>
	<div class="cell small-9">
		<div data-idsecteur="54725" data-table="<?= $table ?>" data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" data-type_liste="<?= $type_liste; ?>">
			<?
				while ($arr_app = $rs_app->getNext()) {
					$value = $arr_app[$name_id];
					echo $Idae->module('fiche_mini', "table=$table&table_value=$value");
				} ?>
		</div>
	</div>
</div>

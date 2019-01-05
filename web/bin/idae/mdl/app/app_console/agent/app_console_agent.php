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

	$nbRows     = (empty($_POST['nbRows'])) ? empty($settings_nbRows) ? 15 : (int)$settings_nbRows : $_POST['nbRows'];

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$BIN         = new Bin();
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

	unset($vars['idagent']);
	$rs_app = $APP->find($vars + ['code' . $Table . '_statut' => ['$nin' => ['END']], "dateCreation$Table" => date('Y-m-d')])->sort(['timeFinPreparationCommande' => 1, $sortBySecond => $sortDirSecond])->skip(($nbRows * $page))->limit($nbRows);

	$Idae               = new Idae($table);
	$IDAE_SELF          = new Idae($type_session);
	$html_entete        = $Idae->module('liste_entete', "table=$table&table_value=$table_value");
	$html_entete_fields = $IDAE_SELF->module('fiche_fields', "edit_field=true&codeAppscheme_field_type=bool&table=$type_session&table_value=" . $_SESSION["id$type_session"]);
	$ARR_SELF           = $IDAE_SELF->findOne([$name_idtype_session => $idtype_session]);
	if ($type_session == 'shop') {
		$actifCss   = empty($ARR_SELF['actifShop']) ? '' : 'active';
		$inactifCss = empty($ARR_SELF['actifShop']) ? 'active' : '';
	}
	$mdl = 'app_console/agent/app_console_fiche';

?>
<div class=" " style="height:100%;overflow:auto;">
	<?
		;
		//if ( $ARR_SCH['hasStatutScheme'] ) {
		$value  = 'statut';

		$Value  = ucfirst($value);
		$_table = $table . '_' . $value;
		$_Table = ucfirst($_table);
		$_id    = 'id' . $_table;
		$_nom   = 'nom' . $_Table;
		$_code  = 'code' . $_Table;
		$_icon  = 'icon' . $_Table;
		$_color = 'color' . $_Table;

		$APPTMP = new App("$table" . "_" . "$value");
		$RS_TMP = $APPTMP->find()->sort(["ordre$_Table"=>1]);

		while ($ARR_TMP = $RS_TMP->getNext()) {
			$tpl_color = $APP->draw_field(['field_name_raw' => 'color',
			                               'field_name'     => $_color,
			                               'table'          => $value,
			                               'field_value'    => $ARR_TMP[$_color]]);
			$tpl_icon  = $APP->draw_field(['field_name_raw' => 'icon',
			                               'field_name'     => $_icon,
			                               'table'          => $value,
			                               'field_value'    => $ARR_TMP[$_icon]]);
			$tpl_nom   = $APP->draw_field(['field_name_raw' => 'nom',
			                               'field_name'     => $_nom,
			                               'table'          => $value,
			                               'field_value'    => $ARR_TMP[$_nom]]);

			// si commandes, on rajoute secteur pour livreur, idshop pour shop
			$ARR_SELF = $IDAE_SELF->findOne([$name_idtype_session => $idtype_session]);
			// si statut plus que START :
			// idlivreur pour livreur, idshop pour shop
			$add_vars = '';
			if ($table == 'commande') {
				$add_vars = "&type_liste=".$type_session.'_' . $ARR_TMP[$_code];
			}
			$data_vars  = "table=$table&vars[$_id]=$ARR_TMP[$_id]$add_vars";
			$vars[$_id] = $ARR_TMP[$_id];

			$rs_app = $APP->find($vars + [ "dateCreation$Table" => date('Y-m-d')])->sort(['timeFinPreparationCommande' => 1, $sortBySecond => $sortDirSecond])->skip(($nbRows * $page))->limit($nbRows);

			$type_liste = 'pool_statut_'.$ARR_TMP[$_code];
			?>
			<div class="bordert padding relative ">
				<div data-module_link="app_console/app_console_<?= $type_session ?>" data-vars="<?= $data_vars ?>"   class="  flex_h flex_align_middle flex_padding flex_margin cursor borderb  " style="line-height:2rem;">
					<div class="padding_more"><?= $tpl_icon ?></div>
					<div class="flex_main text-bold"><?= $tpl_nom ?></div>
					<div>
						<span data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" data-idsecteur="<?= $ARR_SELF['idsecteur'] ?>" data-count="<?= $_table ?>" data-table_value="<?= $ARR_TMP[$_id] ?>" data-table="<?= $table ?>" class="ellipsis"></span>
					</div>
					<div class="item_icon"><?= $tpl_color ?></div>
				</div>
				<div>
					<div style=" "  class="relative flex_v"  data-console_liste="" data-table="<?= $table ?>" data-type_session="<?= $type_session ?>" data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" data-type_liste="<?= $type_liste; ?>">
						<?
							while ($arr_app = $rs_app->getNext()) {

								$value = $arr_app[$name_id];
								echo $Idae->module($mdl, "table=$table&table_value=$value");
							}
							$rs_app->reset(); ?>
					</div>
				</div>
			</div>
			<?
		}
		//} ?>

</div>

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

	$GRILLE_FK  = $APP->get_grille_fk();

	$ARR_RFK = $APP->get_table_rfk($table_value);

	if (is_array($arr_allowed_c) && is_array($ARR_RFK)) {
		$arr_allowed_c = droit_table($type_session, 'L');
		$ARR_RFK       = array_intersect($arr_allowed_c, $ARR_RFK);
	}

?>
<div class="flex_v">

	<div class=" " style="overflow-y:auto;overflow-x:hidden;max-width:100%;">
		<?
			foreach ($GRILLE_FK as $ARR_FK):
				// Helper::dump($ARR_FK);
				$table_fk      = $ARR_FK['table_fk'];
				$APP_TMP  = new App($table_fk);
				$arr      = $APP->plug($ARR_FK['base_fk'], $table_fk)->findOne([$ARR_FK['idtable_fk'] => $ARR[$ARR_FK['idtable_fk']]]);
				$dsp_code = $arr['code' . ucfirst($table_fk)];
				$dsp_fieldname =  'nom' . ucfirst($table_fk) ;
				$dsp_name = $arr[$dsp_fieldname];
				if ( empty($dsp_name) ) {
					$dsp_name = 'Aucun';
				}

				$html_fiche_link = 'table=' . $APP_TMP->codeAppscheme . '&table_value=' . $ARR[$field['idtable_fk']];

				$arr_allowed_c = droit_table($type_session, 'R', $table_fk);
				$css_cursor    = ($arr_allowed_c) ? 'cursor' : '';
				$html_attr     = ($arr_allowed_c) ? "data-module_link='fiche' data-vars='$html_fiche_link'" : '';
				?>
				<div>
					<?=	  $Idae->module('app_liste/app_liste_groupby_inner', "table=$table&groupBy=$table_fk&table_value=" . $arr_groupby['id' . $groupBy]); ?>
				</div>
				<?
			endforeach;

		?>
	</div>
	<div>
		<?= $Idae->module('app_liste/app_liste_menu', "table=$table&table_value=$value"); ?>
	</div>
</div>

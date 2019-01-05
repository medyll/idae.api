<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table = $this->table;
	$Table = ucfirst($table);

	$table_value = (int)$this->HTTP_VARS['table_value'];
	$vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$groupBy     = empty($this->HTTP_VARS['groupBy']) ? '' : $this->HTTP_VARS['groupBy'];
	$mode        = empty($this->HTTP_VARS['mode']) ? 'icone' : $this->HTTP_VARS['mode'];

	$APP = new App($table);

	if (sizeof($APP->get_grille_fk()) == 0) {
		return;
	}

	$name_id = 'id' . $table;
	$ARR     = $APP->findOne([$name_id => $table_value]);
?>
<div class="grid-x" data-table="<?= $table ?>" data-table_value="<?= $table_value ?>">
	<? foreach ($APP->get_grille_fk() as $field):
		$APP_TMP = new App($field['table_fk']);
		$GRILLE_FK_TMP = $APP_TMP->get_grille_fk($field['table_fk']);

		$arr_extends = array_intersect(array_keys($APP->get_grille_fk()), array_keys($GRILLE_FK_TMP));

		// query for name
		$table_fk      = $field['table_fk'];
		$APP_TMP       = new App($table_fk);
		$arr           = $APP->plug($field['base_fk'], $table_fk)->findOne([$field['idtable_fk'] => $ARR[$field['idtable_fk']]]);
		$dsp_code      = $arr['code' . ucfirst($table_fk)];
		$dsp_fieldname = 'nom' . ucfirst($table_fk);
		$dsp_name      = $arr[$dsp_fieldname];
		if (empty($dsp_name)) {
			$dsp_name = 'Aucun';
		}

		$html_fiche_link = 'table=' . $APP_TMP->codeAppscheme . '&table_value=' . $ARR[$field['idtable_fk']];
		$data_vars       = "table=$table_fk&table_value_from=$table_value&table_from=$table";

		$arr_allowed_c = droit_table($type_session, 'R', $table_fk);
		$css_cursor    = ($arr_allowed_c) ? 'cursor' : '';
		$html_attr     = ($arr_allowed_c) ? "data-module_link='app_select_line' data-vars='$data_vars'" : '';
		$add_vars      = [];
		if (sizeof($arr_extends) != 0) {
			foreach ($arr_extends as $key_ext => $table_ext) {
				$idtable_ext = 'id' . $table_ext;
				if (!empty($ARR[$idtable_ext])) {
					$add_vars[$idtable_ext] = (int)$ARR[$idtable_ext];
				}
			}
			if (!empty($add_vars)) {
				$http_add_vars = $APP->translate_vars($add_vars);
			}
			// vardump($arr_extends);
		}

		$select = AppSocket::cf_module('app/app_select', ['vars'        => $add_vars,
		                                                  'table'       => $field['table_fk'],
		                                                  'table_value' => $ARR[$field['idtable_fk']]], 1235);
		$html_attr     =  "data-module_link='app_select_line' data-vars='$data_vars'" ;
		?>
        <div class="cell small-6 medium-12 large-12 ">
            <div class="padding">
                <div <?= $html_attr ?> data-module_link="app_select_line"
                                       data-target_flyout="true"
                                       class="flex_h <?= $css_cursor ?> flex_align_middle borderb">
                    <div class="padding_more boxshadowr" style="width:40px;">
                        <i class="fa fa-<?= $APP_TMP->iconAppscheme ?>"
                           style="color: <?= $APP_TMP->colorAppscheme ?>"></i></div>
                    <div class="padding_more">
                        <div class=" " style="font-size:1.15rem">
							<?= $APP_TMP->draw_field(['field_name_raw' => 'nom',
							                          'field_name'     => $dsp_fieldname,
							                          'table'          => $table,
							                          'field_value'    => $dsp_name]) ?>
                        </div>
                        <div class="no_wrap ellipsis" style="overflow:hidden;max-width:100%;"><a class="textbleu">Modifier <?= $APP_TMP->nomAppscheme ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<? endforeach; ?>
</div>
<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 22/09/2017
	 * Time: 23:53
	 */
	class Idae extends App {

		public         $HTTP_VARS;
		private        $module_route;
		private static $_instance = null;
		public         $table;

		function __construct($table = null) {
			$this->table = $table;
			parent::__construct($table);
		}

		static function module_exists($module) {
			$arr_mdl = explode('/', $module);
			$file    = end($arr_mdl);
			array_pop($arr_mdl);
			$path = implode('/', $arr_mdl);

			$arr_file[] = APPMDL . 'app/app_' . $module . '.php';
			$arr_file[] = APPMDL . 'app/' . $module . '.php';
			$arr_file[] = APPMDL . 'app/' . $path . '/app_' . $file . '.php';
			$arr_file[] = APPMDL . 'app/' . $path . '/' . $file . '.php';

			foreach ($arr_file as $val) {
				if (file_exists($val)) {
					//echo $val;
					return $val;
				}
			}

			return false;
		}

		function fiche_mail($table_value) {
			global $LATTE;

			$APP_HAS_FIELD       = new App('appscheme_has_field');
			$APP_HAS_TABLE_FIELD = new App('appscheme_has_table_field');
			$APP_LIGNE           = new App('commande_ligne');

			$table = $this->table;
			$Table = ucfirst($table);

			$APPOBJ    = $this->appobj($table_value);
			$APP_TABLE = $this->app_table_one;

			$ARR = $APPOBJ->ARR;

			$parameters                = $ARR;
			$parameters['table']       = $table;
			$parameters['table_value'] = (int)$table_value;
			$parameters['nomTable']    = $APPOBJ->NAME_APP;

			$field_list = $this->get_field_list_all();
			foreach ($this->grilleFK as $keyFK => $fieldFK) {
				$table_fk = $fieldFK['table'];
				if (!isset($ARR["id$table_fk"])) continue;
				$APP_TMP       = new App($table_fk);
				$field_list_fk = $APP_TMP->get_field_list_raw();
				$ARR_TMP       = $APP_TMP->findOne(["id$table_fk" => (int)$ARR["id$table_fk"]], ['_id' => 0]);
				foreach ($field_list_fk as $key => $field) {
					$field_name = $field['field_name'];
					if (isset($ARR_TMP[$field_name])) {
						$parameters[$field_name] = $this->cast_field(['codeAppscheme_field_type' => $field['codeAppscheme_field_type'],
						                                              'field_value'              => $ARR_TMP[$field_name],
						                                              'field_name'               => $field['field_name'],
						                                              'field_name_raw'           => $field['field_name_raw'],]);
					}

				}
			}
			foreach ($field_list as $key => $field) {
				$field_name = $field['field_name'];
				if (isset($ARR[$field_name])) {
					$parameters[$field_name] = $this->cast_field(['codeAppscheme_field_type' => $field['codeAppscheme_field_type'],
					                                              'field_value'              => $ARR[$field_name],
					                                              'field_name'               => $field['field_name'],
					                                              'field_name_raw'           => $field['field_name_raw'],]);
				}
			}

			if (!empty($APP_TABLE['hasLigneScheme'])) {
				$rs_ligne  = $APP_LIGNE->find(["id$table" => $table_value]);
				$arr_ligne = iterator_to_array($rs_ligne);

				$RS_HAS_TABLE_FIELD  = $APP_HAS_TABLE_FIELD->find(['idappscheme' => (int)$APP_LIGNE->idappscheme])->sort(['ordreAppscheme_has_table_field' => 1]);
				$ARR_HAS_TABLE_FIELD = iterator_to_array($RS_HAS_TABLE_FIELD);

				$parameters['fields'] = $ARR_HAS_TABLE_FIELD;
				$parameters['liste']  = $arr_ligne;
			}
			$LATTE->setAutoRefresh(true);

			return $html = $LATTE->renderToString(APPTPL . "idae/fiche_mail.html", $parameters);
		}

		/**
		 * @deprecated
		 */
		function fiche($table_value) {
			global $LATTE;

			$table = $this->table;
			$Table = ucfirst($table);

			$APPOBJ    = $this->appobj($table_value);
			$APP_TABLE = $this->app_table_one;

			$ARR = $APPOBJ->ARR;
			// Helper::dump($ARR);
			$parameters['table']       = $table;
			$parameters['table_value'] = (int)$table_value;
			$parameters['nomTable']    = $APPOBJ->NAME_APP;
			$export_values             = [];

			// Helper::dump($APP_TABLE);
			if (!empty($APP_TABLE['hasLigneScheme'])) {
				$idae_liste                      = new Idae($table . '_ligne');
				$table_ligne                     = $idae_liste->liste(["id$table" => $table_value]);
				$parameters ['html_fiche_ligne'] = $table_ligne;
			}

			foreach ($APPOBJ->ARR_GROUP_FIELD as $key => $val) {
				$arrg       = $val['group'];
				$arr_fields = $val['field'];

				$arr_fields = array_filter($arr_fields);

				$export_values[$key] = [];
				foreach ($arr_fields as $keyf => $valf) {
					if (empty($ARR[$valf['codeAppscheme_field'] . $Table])) {
						continue;
					}

					$export_key   = ucfirst(idioma($valf['nomAppscheme_field']));
					$export_code  = ucfirst($valf['codeAppscheme_field']);
					$export_icon  = $valf['iconAppscheme_field'];
					$export_value = $this->draw_field(['field_name_raw' => $valf['codeAppscheme_field'],
					                                   'table'          => $table,
					                                   'field_value'    => $ARR[$valf['codeAppscheme_field'] . $Table]]);

					$export_values[$key][$export_code] = [$export_icon,
					                                      $export_key,
					                                      $export_value];
				}
			}
			$parameters ['var_fiche_main'] = array_filter($export_values);

			// GRILLE FK
			$export_values_fk = [];
			foreach ($APPOBJ->GRILLE_FK as $field):
				$table_k = $field['table_fk'];
				$Table_k = ucfirst($field['table_fk']);
				// query for name if needed
				if (empty($ARR['nom' . ucfirst($field['table_fk'])])) {
					$arr      = $this->plug($field['base_fk'], $field['table_fk'])->findOne([$field['idtable_fk'] => $ARR[$field['idtable_fk']]]);
					$dsp_code = $arr['code' . $Table_k];
					$dsp_name = $arr['nom' . $Table_k];

				} else {
					$dsp_code = $ARR['code' . $Table_k];
					$dsp_name = $ARR['nom' . $Table_k];
				}
				if (empty($dsp_name) && empty($dsp_code)) {
					continue;
				}
				$export_value_fk                     = [];
				$export_value_fk['code ' . $Table_k] = $this->draw_field(['field_name_raw' => 'code',
				                                                          'table'          => $table_k,
				                                                          'field_value'    => $dsp_code]);
				$export_value_fk['nom ' . $Table_k]  = $this->draw_field(['field_name_raw' => 'nom',
				                                                          'table'          => $table_k,
				                                                          'field_value'    => $dsp_name]);

				$export_values_fk[$field['table_fk']] = $export_value_fk;
			endforeach;
			$parameters ['var_fiche_fk'] = array_filter($export_values_fk);

			// Statuts ++
			$export_values_state = [];
			$arr_has             = ['statut',
			                        'type',
			                        'categorie',
			                        'group'];
			foreach ($arr_has as $key => $value):
				$Value = ucfirst($value);
				if (empty($APP_TABLE['has' . $Value . 'Scheme'])) {
					continue;
				}
				$_table = $table . '_' . $value;
				$_Table = ucfirst($_table);
				$APPTMP = new App($_table);
				$_id    = 'id' . $_table;
				$_nom   = 'nom' . $_Table;
				$_icon  = 'icon' . $_Table;
				$_code  = 'code' . $_Table;
				if (!empty($ARR[$_nom])):
					//
					$export_value_fk          = [];
					$export_value_fk['icon']  = $this->draw_field(['field_name_raw' => 'icon',
					                                               'table'          => $_table,
					                                               'field_value'    => $ARR[$_icon]]);
					$export_value_fk['code']  = $this->draw_field(['field_name_raw' => 'code',
					                                               'table'          => $_table,
					                                               'field_value'    => $ARR[$_code]]);
					$export_value_fk['nom']   = $this->draw_field(['field_name_raw' => 'nom',
					                                               'table'          => $_table,
					                                               'field_value'    => $ARR[$_nom]]);
					$export_value_fk['color'] = $this->draw_field(['field_name_raw' => 'color',
					                                               'table'          => $_table,
					                                               'field_value'    => $ARR['color' . $_Table]]);

					$export_values_state[$_table] = $export_value_fk;

					// $rs_tmp = $APPTMP->find();
					// echo function_prod::draw_select_input($_table, $table_value);
				endif;
				if ($value == 'statut') {
					$add_vars = [];
					if ($_SESSION['livreur']) {
						$add_vars = ['START',
						             'RESERV',
						             'LIVENCOU',
						             'END'];
					}
					if ($_SESSION['shop']) {
						$add_vars = ['START',
						             'RESERV',
						             'PREFIN'];
					}
					$step      = (int)$ARR['ordre' . $_Table];
					$next_step = ++$step;
					$arr_tmp   = $APPTMP->find([$_code            => ['$in' => $add_vars],
					                            'ordre' . $_Table => (int)$next_step])->getNext();

					$next_idcommande_statut                = $arr_tmp[$_id];
					$next_nomCommande_statut               = $arr_tmp[$_nom];
					$next_icon_statut                      = $this->draw_field(['field_name_raw' => 'icon',
					                                                            'table'          => $_table,
					                                                            'field_value'    => $arr_tmp[$_icon]]);
					$parameters['next_idcommande_statut']  = $next_idcommande_statut;
					$parameters['next_icon_statut']        = $next_icon_statut;
					$parameters['next_nomCommande_statut'] = $next_nomCommande_statut;
				}
			endforeach;

			$parameters ['var_fiche_state'] = array_filter($export_values_state);
			// Helper::dump($parameters ['var_fiche_state']);
			if (!empty($APP_TABLE['hasStatutScheme'])) {
				$parameters ['html_fiche_statut']      = $LATTE->renderToString(APPTPL . "idae/fiche_statut.html", $parameters);
				$parameters ['html_fiche_next_statut'] = $this->fiche_next_statut($table_value, true);
			}

			if ($_SESSION['shop']) {
				if ($this->table == 'produit') {
					$parameters['html_edit_link'] = '<a class="button" href="#idae/module/update/table=' . $table . '&table_value=' . $table_value . '">modifier</a>';
				}
			}

			if (!empty($APP_TABLE['hasImageScheme'])) {
				$parameters ['html_fiche_image'] = $this->fiche_image($table_value, true);
			}
			echo $html = $LATTE->renderToString(APPTPL . "idae/fiche.html", $parameters);

			//Helper::dump($parameters);
		}
		/**
		 * @deprecated
		 */
		function liste($vars = []) {
			global $LATTE;

			$table = $this->table;
			$Table = ucfirst($table);

			$parameters['table'] = $table;

			array_walk_recursive($vars, 'CleanStr', $vars);
			$vars = empty($vars) ? [] : function_prod::cleanPostMongo($vars, 1);

			$rs  = $this->find($vars);
			$arr = iterator_to_array($rs);

			$APP_HAS_FIELD       = new App('appscheme_has_field');
			$APP_HAS_TABLE_FIELD = new App('appscheme_has_table_field');
			$RS_HAS_TABLE_FIELD  = $APP_HAS_TABLE_FIELD->find(['idappscheme' => (int)$this->idappscheme])->sort(['ordreAppscheme_has_table_field' => 1]);
			$ARR_HAS_TABLE_FIELD = iterator_to_array($RS_HAS_TABLE_FIELD);
			// has_totalAppscheme_field
			$parameters['fields'] = $ARR_HAS_TABLE_FIELD;
			$parameters['liste']  = $arr;

			return $html = $LATTE->renderToString(APPTPL . "idae/table.html", $parameters);
		}

		function fiche_next_statut($table_value, $inner = false) {

			global $LATTE;

			$table = $this->table;
			$Table = ucfirst($table);

			$APPOBJ    = $this->appobj((int)$table_value);
			$ARR       = $APPOBJ->ARR;
			$APP_TABLE = $APPOBJ->APP_TABLE;
			if (empty($APP_TABLE['hasStatutScheme'])) {
				return false;
			}
			if (!empty($_SESSION['type_session'])) {
				$type_session        = $_SESSION['type_session'];
				$name_idtype_session = "id$type_session";
				$idtype_session      = (int)$_SESSION[$name_idtype_session];
				if (!empty($APPOBJ->$name_idtype_session) && $APPOBJ->$name_idtype_session != $idtype_session) {
					return false;
				}
				if ($type_session == 'livreur') {
					if (!empty($ARR[$name_idtype_session]) && $ARR[$name_idtype_session] != $idtype_session) return false;
				}
			}
			$parameters                      = $ARR;
			$parameters['table']             = $table;
			$parameters['table_value']       = (int)$table_value;
			$parameters['session_idlivreur'] = $idtype_session;
			$export_values                   = [];

			// Statuts ++
			$export_values_state = [];
			$arr_has             = ['statut'];
			$value               = 'statut';

			$Value  = ucfirst($value);
			$_table = $table . '_' . $value;
			$_Table = ucfirst($_table);
			$APPTMP = new App($_table);
			$_id    = 'id' . $_table;
			$_nom   = 'nom' . $_Table;
			$_icon  = 'icon' . $_Table;
			$_code  = 'code' . $_Table;
			$_color = 'color' . $_Table;

			$add_vars = [];
			if ($_SESSION['livreur']) {
				$add_vars = ['START',
				             'RESERV',
				             'LIVENCOU',
				             'END']; // 'LIVENCOU',
			}
			if ($_SESSION['shop']) {
				$add_vars = ['START',
				             'RUN',
				             'PREFIN'];
			}
			$step      = (int)$ARR['ordre' . $_Table];
			$next_step = ++$step;
			$arr_tmp   = $APPTMP->find([$_code            => ['$in' => $add_vars],
			                            'ordre' . $_Table => (int)$next_step])->getNext();

			$next_idcommande_statut    = $arr_tmp[$_id];
			$next_nomCommande_statut   = $arr_tmp[$_nom];
			$next_colorCommande_statut = $arr_tmp[$_color];
			$next_icon_statut          = $this->draw_field(['field_name_raw' => 'icon',
			                                                'table'          => $_table,
			                                                'field_value'    => $arr_tmp[$_icon]]);

			$parameters['next_idcommande_statut']    = $next_idcommande_statut;
			$parameters['next_icon_statut']          = $next_icon_statut;
			$parameters['next_nomCommande_statut']   = $next_nomCommande_statut;
			$parameters['next_colorCommande_statut'] = $next_colorCommande_statut;
			$parameters['codeCommande_statut']       = $ARR['code' . $_Table];

			$parameters['idsecteur'] = $ARR['idsecteur'];
			$LATTE->setAutoRefresh(true);

			if ($inner) {
				return $html = $LATTE->renderToString(APPTPL . "idae/fiche_next_statut.html", $parameters);

			} else {
				echo $html = $LATTE->renderToString(APPTPL . "idae/fiche_next_statut.html", $parameters);
			}
		}
		/**
		 * @deprecated
		 */
		function fiche_image($table_value, $inner = false) {
			global $LATTE;

			$table = $this->table;
			$Table = ucfirst($table);

			$parameters['table']       = $table;
			$parameters['table_value'] = (int)$table_value;

			$AppSite                   = new AppSite();
			$arr_tpl                   = $AppSite->makeTplData($table, $table_value);
			$parameters['fiche_image'] = $arr_tpl;

			if ($inner) {
				return $html = $LATTE->renderToString(APPTPL . "idae/fiche_image.html", $parameters);

			} else {
				echo $html = $LATTE->renderToString(APPTPL . "idae/fiche_image.html", $parameters);
			}
		}

		/**
		 * @param null  $table_value
		 * @param array $arr_vars
		 *
		 * @return array
		 *
		 * @deprecated
		 */
		public function get_table_fields($table_value = null, $arr_vars = []) {
			$table = $this->table;;
			$Table = ucfirst($table);

			$vars = empty($arr_vars['vars']) ? [] : function_prod::cleanPostMongo($arr_vars['vars'], 1);
			//
			$APP_FIELD       = new App('appscheme_field');
			$APP_FIELD_GROUP = new App('appscheme_field_group');
			$APP_HAS_FIELD   = new App('appscheme_has_field');

			$APPOBJ      = $this->appobj($table_value, $vars);
			$ARR         = $APPOBJ->ARR;
			$idappscheme = $this->idappscheme;

			$DIST_FIELD_FILTER   = [];
			$DIST_NOFIELD_FILTER = [];
			if (!empty($arr_vars['codeAppscheme_field'])) {
				$DIST_FIELD_FILTER_APP = $APP_FIELD->distinct_all('idappscheme_field', ['codeAppscheme_field' => ['$in' => (array)$arr_vars['codeAppscheme_field']]]);
				$DIST_FIELD_FILTER     = array_merge($DIST_FIELD_FILTER_APP, $DIST_FIELD_FILTER);
			}
			if (!empty($arr_vars['nocodeAppscheme_field'])) {
				$DIST_FIELD_FILTER_APP = $APP_FIELD->distinct_all('idappscheme_field', ['codeAppscheme_field' => ['$in' => (array)$arr_vars['nocodeAppscheme_field']]]);
				$DIST_NOFIELD_FILTER   = array_merge($DIST_FIELD_FILTER_APP, $DIST_NOFIELD_FILTER);
			}
			$DIST_FIELD_TYPE_FILTER = [];
			if (!empty($arr_vars['codeAppscheme_field_type'])) {
				$DIST_FIELD_FILTER_APP  = $APP_FIELD->distinct_all('idappscheme_field', ['codeAppscheme_field_type' => $arr_vars['codeAppscheme_field_type']]);
				$DIST_FIELD_TYPE_FILTER = array_merge($DIST_FIELD_FILTER_APP, $DIST_FIELD_TYPE_FILTER);
			}
			$DIST_GROUP_FILTER = [];
			if (!empty($arr_vars['codeAppscheme_field_group'])) {
				if (is_array($arr_vars['codeAppscheme_field_group'])) {
					$DIST_GROUP_FILTER_APP = $APP_FIELD->distinct_all('idappscheme_field', ['codeAppscheme_field_group' => ['$in' => $arr_vars['codeAppscheme_field_group']]]);
				} else {
					$DIST_GROUP_FILTER_APP = $APP_FIELD->distinct_all('idappscheme_field', ['codeAppscheme_field_group' => $arr_vars['codeAppscheme_field_group']]);
				}
				$DIST_GROUP_FILTER = array_merge($DIST_GROUP_FILTER_APP, $DIST_GROUP_FILTER);
			}
			//
			$ARGS = ['idappscheme' => $idappscheme];
			if (!empty($DIST_FIELD_FILTER)) {
				$ARGS['idappscheme_field'] = ['$in' => $DIST_FIELD_FILTER];
			}
			if (!empty($DIST_NOFIELD_FILTER)) {
				$ARGS['idappscheme_field'] = ['$nin' => $DIST_NOFIELD_FILTER];
			}
			if (!empty($DIST_FIELD_TYPE_FILTER)) {
				$ARGS['idappscheme_field'] = ['$in' => $DIST_FIELD_TYPE_FILTER];
			}
			if (!empty($DIST_FIELD_TYPE_FILTER)) {
				$ARGS['idappscheme_field'] = ['$in' => $DIST_FIELD_TYPE_FILTER];
			}
			if (isset($arr_vars['in_mini_fiche'])) {
				$ARGS['in_mini_fiche'] = $arr_vars['in_mini_fiche'];
			}
			$ARGS_GROUP = [];
			if (!empty($DIST_GROUP_FILTER)) {
				$ARGS_GROUP['idappscheme_field'] = ['$in' => $DIST_GROUP_FILTER];
			}
			$RS_HAS_FIELD = $APP_HAS_FIELD->distinct_all('idappscheme_field', $ARGS);

			$DIST_FIELD_GROUP = $APP_FIELD->distinct_all('idappscheme_field_group', $ARGS_GROUP + ['idappscheme_field' => ['$in' => $RS_HAS_FIELD]]);
			$RS_FIELD_GROUP   = $APP_FIELD_GROUP->find(['idappscheme_field_group' => ['$in' => $DIST_FIELD_GROUP]])->sort(['ordreAppscheme_field_group' => 1]);
			$COLLECT          = [];
			foreach ($RS_FIELD_GROUP as $key => $ARR_FIELD_GROUP) {
				$idappscheme_field_group                = (int)$ARR_FIELD_GROUP['idappscheme_field_group'];
				$RS_FIELD                               = $APP_FIELD->find(['idappscheme_field_group' => $idappscheme_field_group,
				                                                            'idappscheme_field'       => ['$in' => $RS_HAS_FIELD]]);
				$COLLECT[$key]['appscheme_field_group'] = $ARR_FIELD_GROUP;
				foreach ($RS_FIELD as $keyf => $ARR_FIELD) {
					/*if (!empty($arr_vars['show_empty'])) {
						if (!isset($ARR[$ARR_FIELD['codeAppscheme_field'] . $Table])) continue;
					}*/
					$code                                                 = $ARR_FIELD['codeAppscheme_field'];
					$COLLECT[$key]['appscheme_fields'][$code]['css_bold'] = in_array($code, ['nom',
					                                                                         'code']) ? 'text-bold' : '';
					foreach (['nom',
					          'petitNom',
					          'icon',
					          'color'] as $keyff => $valuef) {
						$COLLECT[$key]['appscheme_fields'][$code][$valuef] = $ARR_FIELD[$valuef . 'Appscheme_field'];

					}
					$COLLECT[$key]['appscheme_fields'][$code]['value_raw']   = $ARR[$ARR_FIELD['codeAppscheme_field'] . $Table];
					$COLLECT[$key]['appscheme_fields'][$code]['value_html']  = $this->draw_field(['field_name_raw' => $ARR_FIELD['codeAppscheme_field'],
					                                                                              'table'          => $table,
					                                                                              'field_value'    => $ARR[$ARR_FIELD['codeAppscheme_field'] . $Table]]);
					$COLLECT[$key]['appscheme_fields'][$code]['value_input'] = $this->draw_field_input(['field_name_raw' => $ARR_FIELD['codeAppscheme_field'],
					                                                                                    'table'          => $table,
					                                                                                    'field_value'    => ($ARR_FIELD['codeAppscheme_field'] != 'password') ? $ARR[$ARR_FIELD['codeAppscheme_field'] . $Table] : '']);

				}
			}

			return $COLLECT;
		}

		function get_fk_fields($table_value = null, $arr_vars = []) { // grille_fk
			$APPOBJ  = $this->appobj($table_value, $arr_vars);
			$ARR     = $APPOBJ->ARR;
			$COLLECT = [];
			foreach ($this->get_grille_fk() as $field):
				// query for name
				$arr      = $this->plug($field['base_fk'], $field['table_fk'])->findOne([$field['idtable_fk'] => $ARR[$field['idtable_fk']]]);
				$dsp_code = $arr['code' . ucfirst($field['table_fk'])];
				$dsp_name = $arr['nom' . ucfirst($field['table_fk'])];
				if (empty($dsp_name)) continue;
				$table                             = $field['table_fk'];
				$table_value                       = $ARR[$field['idtable_fk']];
				$COLLECT[$dsp_code]['table']       = $table;
				$COLLECT[$dsp_code]['table_value'] = $table_value;
				$COLLECT[$dsp_code]['value_html']  = $table_value;
				$COLLECT[$dsp_code]['value_mdl']   = $table_value;
				$COLLECT[$dsp_code]['value_input'] = $table;
			endforeach;

			return $COLLECT;
		}

		public function liste_titre($arr_vars = []) {

			$out_vars = [];
			$APP_SCH  = new App('appscheme');
			$APP      = new App('appscheme_has_field');
			if (!empty($arr_vars['vars'])) {
				$arr_vars += $arr_vars['vars'];
				unset($arr_vars['vars']);
			}
			foreach ($arr_vars as $name_vars => $value_vars):
				$ARR = $APP->findOne(['codeAppscheme_has_field' => $name_vars]);
				if ($ARR['_id'] != ''):
					$out_vars[] = $ARR['nomAppscheme_field'] . ' ' . $ARR['nomAppscheme'] . ' : ' . $value_vars;
				else:
					$table = str_replace('id', '', $name_vars);
					$ARR   = $APP_SCH->findOne(['codeAppscheme' => $table]);
					if ($ARR['_id'] != ''):
						$APP_TMP = new App($table);
						$ARR_TMP = $APP_TMP->findOne([$name_vars => (int)$value_vars]);
						if (empty($ARR_TMP['nom' . ucfirst($table)])) continue;
						$out_vars[] = ' ' . $ARR['nomAppscheme'] . ' : ' . $ARR_TMP['nom' . ucfirst($table)];
					endif;
				endif;

			endforeach;
			$out = implode('<i class="fa fa-caret-right"></i>', $out_vars);
			if (!empty($arr_vars['groupBy'])) $out .= ' <i class="fa fa-caret-right"></i> groupé par : ' . $arr_vars['groupBy'];
			if (!empty($arr_vars['sortBy'])) $out .= ' <i class="fa fa-caret-right"></i> trié par : ' . $arr_vars['sortBy'];
			if (!empty($arr_vars['search'])) $out .= ' <i class="fa fa-caret-right"></i> Recherche : "' . $arr_vars['search'] . '"';

			return $out;
		}

		/**
		 * @param array $vars
		 *
		 * @return mixed
		 */
		public function liste_statut($vars = []) {
			global $LATTE;

			$table = $this->table;
			$Table = ucfirst($table);

			$APPOBJ = $this->appobj();
			$ARR    = $APPOBJ->ARR;

			$parameters['table'] = [];

			$_table = $table . '_statut';
			$_Table = ucfirst($_table);
			$APPTMP = new App($_table);
			$_id    = 'id' . $_table;
			$_nom   = 'nom' . $_Table;
			$_icon  = 'icon' . $_Table;
			$_code  = 'code' . $_Table;
			$_ordre = 'ordre' . $_Table;

			$RS_STATUT = $APPTMP->find($vars)->sort([$_ordre => 1]);

			while ($ARR_STATUT = $RS_STATUT->getNext()) {
				$add_vars = [];
				$collect  = [];
				if ($_SESSION['livreur']) {
					$add_vars = ['START',
					             'RESERV',
					             'LIVENCOU',
					             'END'];
				}
				if ($_SESSION['shop']) {
					$add_vars = ['START',
					             'RUN',
					             'PREFIN'];
				}
				$arr_has = ['icon',
				            'nom',
				            'code',
				            'color'];

				$collect["id"]           = $ARR_STATUT[$_id];
				$collect["id" . $_table] = $ARR_STATUT[$_id];
				foreach ($arr_has as $keyh => $valueh):
					$collect[$valueh] = $this->draw_field(['field_name'     => $valueh . $_table,
					                                       'field_name_raw' => $valueh,
					                                       'table'          => $_table,
					                                       'field_value'    => $ARR_STATUT[$valueh . $_Table]]);
				endforeach;

				$step      = (int)$ARR['ordre' . $_Table];
				$next_step = ++$step;
				$arr_tmp   = $APPTMP->find([$_code            => ['$in' => $add_vars],
				                            'ordre' . $_Table => (int)$next_step])->getNext();

				$next_idcommande_statut  = $arr_tmp[$_id];
				$next_nomCommande_statut = $arr_tmp[$_nom];
				$next_icon_statut        = $this->draw_field(['field_name_raw' => 'icon',
				                                              'table'          => $_table,
				                                              'field_value'    => $arr_tmp[$_icon]]);

				$collect['next_idcommande_statut']        = $next_idcommande_statut;
				$collect['next_icon_statut']              = $next_icon_statut;
				$collect['next_nom' . $table . '_statut'] = $next_nomCommande_statut;
				$collect['codeCommande_statut']           = $ARR['code' . $_Table];

				$parameters[] = $collect;

			}

			return $parameters;
		}

		/**
		 * @param string $module
		 * @param array  $http_vars
		 *
		 * @deprecated
		 */
		public function module($module, $http_vars = []) {

			$type_session        = $_SESSION['type_session'];
			$name_idtype_session = "id$type_session";
			$idtype_session      = (int)$_SESSION[$name_idtype_session];
			$APP_SESSION         = new App($type_session);
			global $ARR_SESSION;
			$ARR_SESSION = $APP_SESSION->findOne([$name_idtype_session => $idtype_session]);
			if (empty($_POST)) {
				// $_POST = [];
			}
			if (!empty($_GET)) {
				$_POST = array_merge($_GET, $_POST);;
			}
			if (is_array($http_vars)) {
				$_POST           = array_merge($_POST, $http_vars);
				$this->HTTP_VARS = array_merge($_POST, $http_vars);
			} else {
				parse_str($http_vars, $_POST);
				$this->HTTP_VARS = $_POST;
			}
			$attr = '';
			if (!empty($this->table)) {
				$table = $this->table;
				$attr  .= "data-table='$table' ";
				$attr  .= "data-crypted_table='" . encrypt_url($table) . "' ";
			} elseif (!empty($this->HTTP_VARS['table'])) {
				$table = $this->HTTP_VARS['table'];
				$this->set_table($table);
				$attr .= "data-table='$table' ";
				$attr .= "data-crypted_table='" . encrypt_url($table) . "' ";

			}
			if (!empty($this->HTTP_VARS['table_value'])) {
				$table_value = $this->HTTP_VARS['table_value'];
				$attr        .= "data-table_value='$table_value' ";
				$attr        .= "data-crypted_table_value='" . encrypt_url($table_value) . "' ";

			}

			if (!empty($this->table)) {
				if ($this->has_field_fk($type_session)) {
					$this->HTTP_VARS['vars'][$name_idtype_session] = $idtype_session;
				}
				if ($this->grilleFK && $APP_SESSION->grilleFK) {
					$intersect_fk = array_intersect(array_column($this->grilleFK, 'table'), array_column($APP_SESSION->grilleFK, 'table'));
					if (sizeof($intersect_fk != 0)) {
						$vars = [];
						foreach ($intersect_fk as $key_ifk => $value_fk) {
							if (!empty($ARR_SESSION["id$value_fk"])) $this->HTTP_VARS['vars']["id$value_fk"] = (int)$ARR_SESSION["id$value_fk"];
						}
					}

				}
			}

			$this->module_route = "idae/module/$module";

			$attr .= 'data-vars="' . http_build_query($this->HTTP_VARS) . '" ';
			$attr .= 'data-crypted_vars="' . encrypt_url(http_build_query($this->HTTP_VARS)) . '" ';

			$attr = html_entity_decode($attr);

			$moduleTag = 'module';
			$start     = ($moduleTag != 'none') ? "<$moduleTag  data-mdl='idae/module/$module' $attr    title='module=$module' value='red'  >" : ""; //
			$end       = ($moduleTag != 'none') ? "</$moduleTag>" : "";

			$arr_mdl = explode('/', $module);
			$file    = end($arr_mdl);
			array_pop($arr_mdl);
			$path = implode('/', $arr_mdl);

			$file_1 = APPMDL . 'app/app_' . $module . '.php';
			$file_2 = APPMDL . 'app/' . $module . '.php';
			$file_3 = APPMDL . 'app/' . $path . '/app_' . $file . '.php';
			$file_4 = APPMDL . 'app/' . $path . '/' . $file . '.php';

			if (!empty($table)) {
				$a           = 'app_custom/' . $table . '/' . $table . '_' . $file;
				$test_custom = Idae::module_exists('app_custom/' . $table . '/' . $table . '_' . $file);
				if (!empty($test_custom)) {
					$Idae_tmp = new Idae($table);

					return $Idae_tmp->module('app_custom/' . $table . '/' . $table . '_' . $file, $this->HTTP_VARS);
				}
			}

			ob_start();

			if (file_exists($file_1)) {
				include($file_1);
			} elseif (file_exists($file_2)) {
				include($file_2);
			} elseif (file_exists($file_3)) {
				include($file_3);
			} elseif (file_exists($file_4)) {
				include($file_4);
			} else {
				echo "missing module $module <br>";
			}
			$final = ob_get_contents();
			$final = $start . $final . $end;
			ob_end_clean();

			return trim($final);
		}

		public static function getInstance($table = '') {

			if (is_null(self::$_instance)) {
				self::$_instance = new Idae($table);
			} else if (!is_null(self::$_instance) && self::$_instance->table = $table) {
				self::$_instance = new Idae($table);
			}

			return self::$_instance;
		}
	}
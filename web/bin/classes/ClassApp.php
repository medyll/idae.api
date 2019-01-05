<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 23/05/14
	 * Time: 20:26
	 */
	global $app_conn_nb;
	global $PERSIST_CON;

	/**
	 * Class App
	 *
	 * @deprecated in favor of IdaeDataDB
	 */
	class App extends IdaeConnect {

		private static $_instance = null;

		public $app_table_one;
		public $app_db;

		public $conn;
		public $app_conn;

		public $obj;

		function set_table($table) {
			return $this->__construct($table);
		}

		/**
		 * App constructor.
		 * @deprecated
		 *
		 * @param string $table
		 */
		public function __construct($table = '') {
			global $app_conn_nb;
			global $PERSIST_CON;
			//
			if (!defined('MDB_USER')) {
				return 'Utilisateur DB non defini';
			}
			parent::__construct();
			$this->conn = IdaeConnect::getInstance()->connect();

			$sitebase_app     = MDB_PREFIX . 'sitebase_app';
			$sitebase_sockets = MDB_PREFIX . 'sitebase_sockets';

			$this->table                     = $table;
			$this->app_conn                  = $this->conn->$sitebase_app->appscheme;
			$this->appscheme                 = $this->conn->$sitebase_app->appscheme;
			$this->appscheme_type            = $this->conn->$sitebase_app->appscheme_type;
			$this->appscheme_base            = $this->conn->$sitebase_app->appscheme_base;
			$this->appscheme_field           = $this->conn->$sitebase_app->appscheme_field;
			$this->appscheme_field_type      = $this->conn->$sitebase_app->appscheme_field_type;
			$this->appscheme_field_group     = $this->conn->$sitebase_app->appscheme_field_group;
			$this->appscheme_has_field       = $this->conn->$sitebase_app->appscheme_has_field;
			$this->appscheme_has_table_field = $this->conn->$sitebase_app->appscheme_has_table_field;
			$this->APPCACHE                  = $this->conn->$sitebase_sockets->data_activity;

			if (!empty($table)) {
				$this->app_table_one           = $this->app_conn->findOne(['codeAppscheme' => $table]); // si pas reg ?
				$this->app_field_name_id       = 'id' . $table;
				$this->app_field_name_id_type  = 'id' . $table . '_type';
				$this->app_field_name_nom      = $this->app_table_one['nomAppscheme'];
				$this->app_field_name_nom_type = 'nom' . ucfirst($table) . '_type';
				$this->app_field_name_top      = 'estTop' . ucfirst($table);
				$this->app_field_name_actif    = 'estActif' . ucfirst($table);
				$this->idappscheme             = (int)$this->app_table_one['idappscheme'];
				$this->codeAppscheme           = $this->app_table_one['codeAppscheme'];
				$this->iconAppscheme           = $this->app_table_one['iconAppscheme'];
				$this->colorAppscheme          = $this->app_table_one['colorAppscheme'];
				$this->nomAppscheme            = $this->app_table_one['nomAppscheme'];
				$this->codeAppscheme_base      = $this->app_table_one['codeAppscheme_base'];
				$this->app_table_icon          = $this->app_table_one['icon'];
				$this->grilleFK                = $this->app_table_one['grilleFK'];
				$this->hasImageScheme          = $this->app_table_one['hasImageScheme'];
			}

			$this->app_default_fields_add  = ['petitNom',
			                                  'nom',
			                                  'bgcolor',
			                                  'code',
			                                  'color',
			                                  'icon',
			                                  'ordre',
			                                  'slug',
			                                  'actif'];
			$this->app_default_fields      = ['nom'          => '',
			                                  'prenom'       => '',
			                                  'petitNom'     => 'nom court',
			                                  'code'         => '',
			                                  'reference'    => 'reference',
			                                  'description'  => '',
			                                  'quantite'     => '',
			                                  'prix'         => '',
			                                  'total'        => '',
			                                  'atout'        => '',
			                                  'valeur'       => '',
			                                  'rang'         => '',
			                                  'dateCreation' => 'crée le',
			                                  'dateDebut'    => 'date debut',
			                                  'dateFin'      => 'date de fin',
			                                  'duree'        => '',
			                                  'heure'        => '',
			                                  'email'        => '',
			                                  'telephone'    => '',
			                                  'fax'          => '',
			                                  'adresse'      => '',
			                                  'url'          => '',
			                                  'totalHt'      => 'total HT',
			                                  'totalTtc'     => 'total TTC',
			                                  'totalMarge'   => 'total Marge',
			                                  'totalTva'     => 'total TVA',
			                                  'ordre'        => '',
			                                  'color'        => '',
			                                  'image'        => ''];
			$this->app_default_group_field = ['codification'   => '',
			                                  'identification' => '',
			                                  'date'           => '',
			                                  'prix'           => '',
			                                  'localisation'   => '',
			                                  'valeur'         => '',
			                                  'texte'          => '',
			                                  'image'          => '',
			                                  'telephonie'     => '',
			                                  'heure'          => '',
			                                  'divers'         => 'Autres'];

			//$this->make_classes_app();
		}

		public static function getInstance($table = '') {

			if (is_null(self::$_instance)) {
				self::$_instance = new App($table);
			}

			return self::$_instance;
		}

		function make_classes_app() {
			if (empty($this->table)) {return false;}
			if (!is_string($this->table)) {return false;}
			$table     = $this->table;
			$file_name = $table;
			$path      = APPCLASSES_APP . '/' . BUSINESS . '/' . CUSTOMER . '/' . $table . '/';
			foreach (['app',
			          'act',
			          'ui',
			          'pre_act',
			          'post_act'] as $key => $extension) {
				$className     = $file_name . '_' . $extension;
				$path_and_file = $path . $className . '.php';
				if (!file_exists($path)) {
					mkdir($path, 0777, true);
				}
				if (!file_exists($path_and_file)) {
					$monfichier = @fopen($path_and_file, 'a+');
					if (!$monfichier) continue;
					$content = $this->write_classes_app($className, $table, $extension);
					@fputs($monfichier, $content);
					@fclose($monfichier);
				}

			}
		}

		function write_classes_app($className, $table, $type) {
			$content = file_get_contents(APP_CONFIG_DIR . 'classes_app_models/class_model_' . $type . '.php');
			$content = str_replace('TABLE_CLASS', $table, $content);
			$content = str_replace('NAME_CLASS', $className, $content);

			return $content;
		}

		function rest($params = ['table',
		                         'action',
		                         'vars']) {
			//
			Helper::dump(func_get_args());

			if (strpos($params['vars'], '/') === false) {
				$value = $params['vars'];

			} else {
				$value = explode('/', $params['vars']);
			}
			$new_value = array_map(function ($node) {
				if (strpos($node, ':') === false) {

					return $node;
				}
				$tmp = explode(':', $node);

				if (strpos($tmp[0], '[]') === true) {
					echo "array";
				}

				return [$tmp[0] => $tmp[1]];

			}, $value);
			Helper::dump($new_value);
			$app = new App($params['table']);

			if ($params['action'] == 'remove') {
				die('verboten');
			}

			$rs = $app->$params['action']();

			echo json_encode(iterator_to_array($rs));
			// $this->$params['action']($value);

		}

		/**
		 * @param       $table_value
		 * @param array $vars
		 *
		 * @return stdClass
		 */
		function appobj($table_value = null, $vars = []) {

			$this->obj                     = new stdClass();
			$this->obj->NAME_ID            = 'id' . $this->app_table_one['codeAppscheme'];
			$this->obj->NAME_APP           = $this->app_table_one['nomAppscheme'];
			$this->obj->ICON               = $this->app_table_one['iconAppscheme'];
			$this->obj->ICON_COLOR         = $this->app_table_one['colorAppscheme'];
			$this->obj->APP_TABLE          = $this->app_table_one;
			$this->obj->GRILLE_FK          = $this->get_grille_fk();
			$this->obj->HTTP_VARS          = $this->translate_vars($vars);
			$this->obj->ARR_GROUP_FIELD    = $this->get_field_group_list();
			$this->obj->app_default_fields = $this->app_default_fields;
			$this->obj->R_FK               = $this->get_reverse_grille_fk($this->app_table_one['codeAppscheme'], $table_value);
			if (!empty($table_value)) {
				$this->obj->ARR = $this->findOne([$this->obj->NAME_ID => (int)$table_value]);
			}

			return $this->obj;
		}

		function get_grille_fk_grouped($table = '', $vars = []) {
			return $this->get_grille_fk($table, ['grouped_scheme' => 1]);
		}

		function get_grille_fk_nongrouped($table = '', $vars = []) {
			return $this->get_grille_fk($table, ['grouped_scheme' => ['$ne' => 1]]);
		}

		/**
		 * @param string $table
		 * @param array  $vars
		 *
		 * @return array
		 */
		function get_grille_fk($table = '', $vars = []) {
			if (empty($table) && !empty($this->table)) {
				$grille_fk = $this->grilleFK;
			} elseif (empty($table)) {
				$arr       = $this->app_conn->findOne(array_merge(['codeAppscheme' => $table], $vars));
				$grille_fk = $arr['grilleFK'];
			}
			$out = [];
			if (empty($grille_fk)) {
				return [];
			}
			foreach ($grille_fk as $arr_fk):
				$table_fk = $arr_fk['table'];
				$index    = $arr_fk['ordreTable'];

				$db_fk = $this->app_conn->findOne($vars + ['codeAppscheme' => $table_fk]);
				if (!empty($db_fk['codeAppscheme_base'])):
					$out[$table_fk] = ['base_fk'        => $db_fk['codeAppscheme_base'],
					                   // $out[$index]
					                   'collection_fk'  => $db_fk['codeAppscheme'],
					                   'nomAppscheme'   => $db_fk['nomAppscheme'],
					                   'codeAppscheme'  => $db_fk['codeAppscheme'],
					                   'iconAppscheme'  => $db_fk['iconAppscheme'],
					                   'colorAppscheme' => $db_fk['colorAppscheme'],
					                   'table_fk'       => $table_fk,
					                   'idtable_fk'     => 'id' . $table_fk,
					                   'nomtable_fk'    => 'nom' . ucfirst($table_fk),
					                   'icon_fk'        => $db_fk['icon']];
				endif;
			endforeach;

			return $out;
		}

		function set_grille_fk($fk_table) {
			if (empty($this->table)) return false;
			$arr['uid']        = uniqid();
			$arr['ordreTable'] = (int)(sizeof($this->get_fk_tables())) + 1;
			$arr['table']      = $fk_table;
			$test              = $this->get_fk_tables();
			if (!in_array($fk_table, $test)) {
				// do it
				//vardump_async("$this->idappscheme , $this->table => $fk_table",true);
				$this->plug('sitebase_app', 'appscheme')->update(['idappscheme' => $this->idappscheme], ['$push' => ['grilleFK' => $arr]]);
			}
		}

		function translate_vars($arr_vars = []) {
			// vars to http ?
			$out_vars = [];
			foreach ($arr_vars as $name_vars => $value_vars):
				$out_vars['vars'][$name_vars] = $value_vars;
			endforeach;

			return http_build_query($out_vars);
		}

		function get_field_group_list($codeGroupe = '', $qy_has_field = [], $excludedCode = []) {
			$out         = [];
			$vars_code   = empty($codeGroupe) ? [] : ['codeAppscheme_field_group' => $codeGroupe];
			$arr_field   = $this->appscheme_has_field->distinct('idappscheme_field', ['codeAppscheme_field' => ['$nin' => $excludedCode]] + $qy_has_field + ['idappscheme' => $this->idappscheme]);
			$arr_field_2 = $this->appscheme_field->distinct('idappscheme_field_group', ['idappscheme_field' => ['$in' => $arr_field]]);
			$rsG         = $this->appscheme_field_group->find($vars_code + ['idappscheme_field_group' => ['$in' => $arr_field_2]])->sort(['ordreAppscheme_field_group' => 1]);
			$i           = 0;
			while ($arrg = $rsG->getNext()) {

				$out2                  = [];
				$out[(int)$i]['group'] = $arrg;
				$arr_field_tmp         = $this->appscheme_field->distinct('idappscheme_field', ['idappscheme_field_group' => (int)$arrg['idappscheme_field_group']]);
				$rstmp_ar              = $this->appscheme_has_field->find(['idappscheme'       => $this->idappscheme,
				                                                           'idappscheme_field' => ['$in' => $arr_field_tmp]])->sort(['ordreAppscheme_has_field' => 1]);

				while ($arrf = $rstmp_ar->getNext()) {
					// vardump_async($arrf['ordreAppscheme_has_field']);
					$tmp_ar = $this->appscheme_field->findOne(['idappscheme_field' => (int)$arrf['idappscheme_field']]);
					$out2[] = $tmp_ar;

				}
				// $rsF                   = $this->appscheme_field->find(['idappscheme_field_group' => (int)$arrg['idappscheme_field_group'], 'idappscheme_field' => ['$in' => $arr_field]])->sort(['ordreAppscheme_field' => 1]);
				/*	while ($arrf = $rsF->getNext()) {
						$tmp_ar = $this->appscheme_has_field->findOne(['idappscheme' => $this->idappscheme,'idappscheme_field'=>(int)$arrf['idappscheme_field']]);
						$out2[] = $arrf;
					}*/
				$out[(int)$i]['field'] = $out2;
				$i++;
			}

			return $out;
		}

		/**
		 * @param $table $table_value
		 * @param $table_value
		 *
		 * @return array $out
		 */
		function get_reverse_grille_fk($table, $table_value = '', $add = []) {
			$id   = 'id' . $table;
			$vars = $out = [];
			if (!empty($table_value)):
				$vars[$id] = (int)$table_value;
			endif;
			if (empty($add)) {
				$rs = $this->app_conn->find(['grilleFK.table' => $table]);
			} else {
				$rs = $this->app_conn->find($add + ['grilleFK.table' => $table]);
			}

			foreach ($rs as $arr):
				if (empty($table_value)) {
					if (str_find('_ligne', $arr['codeAppscheme_base'])) continue;
					$rs_fk = $this->plug($arr['codeAppscheme_base'], $arr['codeAppscheme'])->find();
				} else {
					$rs_fk = $this->plug($arr['codeAppscheme_base'], $arr['codeAppscheme'])->find([$id => (int)$table_value]);
				}

				$index = $rs_fk->count();
				//
				$out[$arr['codeAppscheme']] = $arr + ['count'       => $index,
				                                      'table'       => $arr['codeAppscheme'],
				                                      'scope'       => $arr['codeAppscheme_type'],
				                                      'icon'        => $arr['icon'],
				                                      'table_value' => $table_value
				                                      /*,  'rs_fk'=>$rs_fk*/];
			endforeach;

			return $out;
		}

		/*		function  plug($base, $table) {
					if (empty($table) || empty($base) || !defined('MDB_USER')) {
						return 'choisir une base';
					}
					// PREFIX HERE POUR BASE
					$db         = $this->plug_base($base);
					$collection = $db->$table;

					return $collection;
				}

				function  plug_base($base) {
					if (empty($base) || !defined('MDB_USER')) {
						return 'choisir une base';
					}
					// PREFIX HERE POUR BASE
					$base = MDB_PREFIX . $base;
					$db   = $this->conn->$base;

					return $db;
				}*/

		function findOne($vars, $out = []) {
			if (empty($this->app_table_one['codeAppscheme_base'])) {
				vardump($this->table);
			}
			if (sizeof($out) == 0) {
				$arr = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->findOne($vars);
			} else {
				$arr = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->findOne($vars, $out);
			}

			return $arr;
		}

		/**
		 * @param $table $table_value
		 * @param $table_value
		 *
		 * @return array $out
		 */
		function get_grille_rfk($table, $table_value = '', $add = []) {
			$id   = 'id' . $table;
			$vars = $out = [];
			if (!empty($table_value)):
				$vars[$id] = (int)$table_value;
			endif;
			if (empty($add)) {
				$rs = $this->app_conn->find(['grilleFK.table' => $table]);
			} else {
				$rs = $this->app_conn->find($add + ['grilleFK.table' => $table]);
			}
			$arr_ty = $this->appscheme->distinct('idappscheme_type', ['grilleFK.table' => $table]);
			$rs_ty  = $this->appscheme_type->find(['idappscheme_type' => ['$in' => $arr_ty]])->sort(['nomAppscheme_type' => 1]);

			$arr_final = [];
			while ($arr_ty = $rs_ty->getNext()) {
				$arr_tmp = $arr_out = [];
				//
				$vars_type = ['idappscheme_type' => (int)$arr_ty['idappscheme_type']];

				if (empty($add)) {
					$rs_det = $this->appscheme->find($vars_type + ['grilleFK.table' => $table]);
				} else {
					$rs_det = $this->appscheme->find($vars_type + $add + ['grilleFK.table' => $table]);
				}
				while ($arr_det = $rs_det->getNext()) {
					if (empty($table_value)) {
						if (str_find('_ligne', $arr_det['codeAppscheme_base'])) continue;
						$rs_fk = $this->plug($arr_det['codeAppscheme_base'], $arr_det['codeAppscheme'])->find();
					} else {
						$rs_fk = $this->plug($arr_det['codeAppscheme_base'], $arr_det['codeAppscheme'])->find([$id => (int)$table_value]);
					}
					if ($rs_fk->count() == 0) continue;
					$arr_det['count']                                = $rs_fk->count();
					$arr_det['table']                                = $arr_det['codeAppscheme'];
					$arr_tmp['appscheme'][$arr_det['codeAppscheme']] = $arr_det;
				}

				if (!empty($arr_tmp['appscheme'])) {
					$arr_out     = array_merge($arr_ty, $arr_tmp);
					$arr_final[] = $arr_out;
				}

			}

			return $arr_final;

		}

		function get_table_rfk($table_value = '', $add = []) {
			$table = $this->table;
			$id    = "id$table";
			$vars  = $out = [];
			if (!empty($table_value)):
				$vars[$id] = (int)$table_value;
			endif;

			$arr_table_rfk = $this->appscheme->distinct('codeAppscheme', $add + ['grilleFK.table' => $table]);

			return $arr_table_rfk;
		}

		/**
		 * @param $table_value
		 * @param $vars
		 */
		function extract_vars($table_value, $vars = []) {
			$out['ARR_GROUP_FIELD'] = $this->get_field_group_list();
			$out['NAME_APP']        = $this->app_table_one['nomAppscheme'];
			$out['ICON']            = $this->app_table_one['iconAppscheme'];
			$out['ICON_COLOR']      = $this->app_table_one['colorAppscheme'];
			$out['APP_TABLE']       = $this->app_table_one;
			$out['GRILLE_FK']       = $this->get_grille_fk($this->app_table_one['codeAppscheme']);
			$out['R_FK']            = $this->get_reverse_grille_fk($this->app_table_one['codeAppscheme'], $table_value);
			$out['HTTP_VARS']       = $this->translate_vars($vars);

			return $out;
		}

		function set_hist($idagent, $vars) {

			$vars['dateCreationActivity_expl']  = date('Y-m-d');
			$vars['heureCreationActivity_expl'] = date('H:i:s');
			$vars['timeCreationActivity_expl']  = time();
			$vars['idactivity_expl']            = (int)$this->getNext('idactivity_expl');
			$vars['idagent']                    = (int)$idagent;
			$vars['nomActivity_expl']           = $vars['vars']['table'] . ' ' . $vars['vars']['groupBy']; //.' '  .App::get_full_titre_vars($vars['vars']);
			$this->plug('sitebase_base', 'activity_expl')->update(['uid' => $vars['uid']], ['$set' => $vars], ['upsert' => true]);
		}

		function getNext($id, $min = 1) {

			if (!empty($min)) {
				$test = $this->plug('sitebase_increment', 'auto_increment')->findOne(['_id' => $id]);
				if (!empty($test['value'])) {
					if ($test['value'] < $min) {
						$this->plug('sitebase_increment', 'auto_increment')->update(['_id' => $id], ['value' => (int)$min], ["upsert" => true]);
					}
				}
			}
			$this->plug('sitebase_increment', 'auto_increment')->update(['_id' => $id], ['$inc' => ['value' => 1]], ["upsert" => true]);
			$ret = $this->plug('sitebase_increment', 'auto_increment')->findOne(['_id' => $id]);

			return (int)$ret['value'];
		}

//

		function set_log($idagent, $table, $table_value, $log_type) {
			$round_numerator      = 60 * 5;
			$rounded_time         = (round(time() / $round_numerator) * $round_numerator);
			$upd                  = [];
			$upd['codeActivite']  = strtoupper($table) . '_' . strtoupper($log_type);
			$upd['timeActivite']  = (int)$rounded_time;
			$upd['dateActivite']  = date('Y-m-d', $rounded_time);
			$upd['heureActivite'] = date('H:i:s', $rounded_time);
			$upd['idagent']       = (int)$idagent;
			$upd['table']         = $table;
			$upd['table_value']   = (int)$table_value;
			// sitebase_pref // agent activite
			$this->plug('sitebase_base', 'activity')->update($upd, ['$set' => $upd], ['upsert' => true]);
			// nlle table agent_history
			$index_upd['idagent']             = (int)$idagent;
			$index_upd['codeAgent_history']   = $table;
			$index_upd['valeurAgent_history'] = (int)$table_value;
			//
			$APP_TMP                     = new App($table);
			$ARR_TMP                     = $APP_TMP->query_one(['id' . $table => (int)$table_value]);
			$h_upd['nomAgent_history']   = strtolower($ARR_TMP['nom' . ucfirst($table)]);
			$h_upd['timeAgent_history']  = (int)time();
			$h_upd['dateAgent_history']  = date('Y-m-d');
			$h_upd['heureAgent_history'] = date('H:i:s');
			$test                        = $this->plug('sitebase_pref', 'agent_history')->findOne($index_upd);
			if (empty($test['idagent_history'])) {
				$index_upd['idagent_history'] = $h_upd['idagent_history'] = (int)App::getNext('idagent_history');
			}
			$this->plug('sitebase_pref', 'agent_history')->update($index_upd, ['$set' => $h_upd,
			                                                                   '$inc' => ['quantiteAgent_history' => 1]], ['upsert' => true]);
			AppSocket::reloadModule('app/app_gui/app_gui_panel', $table);
		}

		function query_one($vars, $fields = []) {
			$arr = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->findOne($vars, $fields);

			return $arr;
		}

		function get_settings($idagent, $key, $table = '') {
			$width_table = empty($table) ? '' : '_' . $table;
			$arr         = $this->plug('sitebase_pref', 'agent_pref')->findOne(['idagent'        => (int)$idagent,
			                                                                    'codeAgent_pref' => $key . $width_table]);

			return $arr['valeurAgent_pref'];
		}

		/** $field_name_raw */

		function set_settings($idagent, $vars) {
			foreach ($vars as $key => $val) {
				$out['valeurAgent_pref'] = $val;
				$out['codeAgent_pref']   = $key;
				$out['idagent']          = (int)$idagent;

				$arr = $this->plug('sitebase_pref', 'agent_pref')->findOne(['idagent'        => (int)$idagent,
				                                                            'codeAgent_pref' => $key]);
				if (empty($arr['idagent_pref'])) {
					$out['idagent_pref'] = (int)$this->getNext('idagent_pref');
				}
				$this->plug('sitebase_pref', 'agent_pref')->update(['idagent'        => (int)$idagent,
				                                                    'codeAgent_pref' => $key], ['$set' => $out], ['upsert' => true]);
			}
		}

		/** $table */

		function del_settings($idagent, $key, $table = '') {
			$width_table = empty($table) ? '' : '_' . $table;
			$del         = $this->plug('sitebase_pref', 'agent_pref')->remove(['idagent'        => (int)$idagent,
			                                                                   'codeAgent_pref' => $key . $width_table]);

			//$del         = $this->plug('sitebase_pref', 'agent_pref')->update(array('idagent' => (int)$idagent), array('$pull' => array('settings.' . $key . $width_table => 1)));

			return $del;
		}

		function is_confident_table($table) {
			$conf = ['agent_note',
			         'todo',
			         'tache'];

			return in_array($table, $conf);
		}

		function get_array_field_bool() {
			$arr = $arrFieldsBool = ['estTop'     => ['star',
			                                          'star-o textgris'],
			                         'estActif'   => ['check',
			                                          'square-o textgris'],
			                         'estVisible' => ['eye-slash',
			                                          'eye textgris']];

			return $arr;
		}

		function scheme($table) {
			// return $this->app_conn->findOne($arr_vars);
			$base = $this->get_base_from_table($table);

			return $this->conn->$base->$table;
		}

		function get_base_from_table($table) {
			$arr = $this->app_conn->findOne(['codeAppscheme' => $table]);

			return $arr['codeAppscheme_base'];
		}

		function get_one_scheme($arr_vars = []) {
			return $this->app_conn->findOne($arr_vars);
		}

		function get_table_scheme($table) {
			return $this->app_conn->findOne(['codeAppscheme' => $table]);
		}

		/**
		 * @param        $field_name_raw
		 * @param array  $ARR
		 * @param string $field_name
		 *
		 * @return string
		 */
		function cf_output($field_name_raw, $ARR = [], $field_name = '') {
			$table = $this->table;;
			$Table      = ucfirst($table);
			$field_name = $field_name ?: $field_name_raw . $Table;

			return $this->draw_field(['field_name'     => $field_name,
			                          'field_name_raw' => $field_name_raw,
			                          'field_value'    => $ARR[$field_name] ?: '']);
		}

		/**
		 * @param $field_name_raw
		 *
		 * @return string
		 */
		function cf_output_icon($field_name_raw) {
			$table = $this->table;;
			$Table = ucfirst($table);

			$arrF = $this->plug('sitebase_app', 'appscheme_field')->findOne(['codeAppscheme_field' => $field_name_raw]);
			$icon = $arrF['iconAppscheme_field'];

			return $value = "<i class = 'fa fa-$icon'  ></i >";;
		}

		/** field_value */
		function draw_field($vars = [], $nude = false) {
			$field_name_raw = $vars['field_name_raw'];
			$table          = $vars['table'];
			$Table          = ucfirst($table);
			$field_name     = empty($vars['field_name']) ? $field_name_raw . $Table : $vars['field_name'];
			if (is_array($vars['field_value'])) {
				if ($vars['field_value'][$field_name]) {
					$vars['field_value'] = $vars['field_value'][$field_name];
				} elseif ($vars['field_value']['emailDevis']) {
					$vars['field_value'] = $vars['field_value']['emailDevis'];
				} else {
					return implode(',', $vars['field_value']);
				}

			}
			$value = $vars['field_value'];//nl2br($vars['field_value']);
			// le type
			$arrF = $this->plug('sitebase_app', 'appscheme_field')->findOne(['codeAppscheme_field' => $field_name_raw]);

			if (empty($arrF['codeAppscheme_field_type'])) {
				// $arrF                          = $this->appscheme_field->findOne(['codeAppscheme_field' => $field_name_raw]);
				// $vars['codeAppscheme_field_type'] = $arr_tmp['codeAppscheme_field_type'];
			}

			//if (!empty($value)):
			switch (strtolower($arrF['codeAppscheme_field_type'])):
				case 'distance':
					$value = round($value / 1000, 2) . ' kms';
					break;
				case 'minutes':
					if (empty($value)) break;
					$value = ceil($value / 60) . ' minutes';
					break;
				case 'valeur':
					$value = (is_int($value)) ? maskNbre($value, 0) : $value;
					break;
				case 'prix':
					if (empty($value)) break;
					$value = maskNbre((float)$value, 2) . ' €';
					break;
				case 'prix_precis':
					if (empty($value)) break;
					$value = maskNbre((float)$value, 6) . ' €';
					break;
				case 'pourcentage':
					$value = (float)$value . ' % ';
					break;
				case 'date':
					if (empty($value)) break;
					$value = date_fr($value);
					break;
				case 'heure':
					if (empty($value)) break;
					$value = maskHeure($value);
					break;
				case 'phone':
					if (empty($value)) break;
					$value = "<div class='inline nowrap'>" . maskTel($value) . "</div>";
					break;
				case 'textelibre':
					if (empty($value)) break;
					$value = "<div style='max-height:200px;overflow:auto;'>" . nl2br(stripslashes($value)) . "</div>";
					break;
				case 'texteformate':
					if (empty($value)) break;
					$value = $value;
					break;
				case 'bool':
					$arr_tmp = $this->appscheme_field->findOne(['codeAppscheme_field' => $field_name_raw]);
					$icon    = $arr_tmp['iconAppscheme_field'];
					$css     = empty($value) ? 'textgris' : 'textvert';
					$text    = empty($icon) ? ouiNon($value) : '';
					$value   = "<i class = 'fa fa-$icon  $css'  ></i >$text";
					break;
				case 'icon':
					if (empty($value)) break;
					$value = "<i class= 'fa fa-$value'></i>";
					break;
				case 'password':
					if (empty($value)) break;
					$value = "***********";
					break;
				case 'color':
					if (empty($value)) break;
					$value = "<i class='fa fa-circle' style='color:$value ;'  ></i>";
					break;
			endswitch;
			//endif;
			if ($nude == false):
				$str = "<span data-field_name='$field_name'  data-field_name_raw='$field_name_raw'  >$value </span>";
			else:
				$str = "$value";
			endif;

			return $str;
		}

		function draw_field_input($vars = [], $var_name = 'vars') {

			$field_name_raw = $vars['field_name_raw'];
			$table          = $vars['table'];
			$value          = nl2br($vars['field_value']);
			$value          = $vars['field_value'];//nl2br($vars['field_value']);
			$Table          = ucfirst($table);
			$field_name     = empty($vars['field_name']) ? $field_name_raw . $Table : $vars['field_name'];
			$a1             = $this->appscheme_has_field->findOne(['codeAppscheme_has_field' => $field_name]);
			$a2             = $this->appscheme_field->findOne(['idappscheme_field' => (int)$a1['idappscheme_field']]);

			// echo $a2['codeAppscheme_field_type'];
			// classname // tagg
			$tag  = "text";
			$type = 'text';
			$attr = '';

			if (is_array($vars['field_value'])) {
				if ($vars['field_value'][$field_name]) {
					$vars['field_value'] = $vars['field_value'][$field_name];
				}
				if (is_array($vars['field_value'])) {
					$value = $vars['field_value'] = implode(',', $vars['field_value']);
				}

			}

			switch (strtolower($a2['codeAppscheme_field_type'])):
				case "icon":
					$attr  = 'act_defer mdl="app/app_select_icon_fa" vars="' . http_build_query($vars) . '"';
					$class = "fauxInput";
					$tag   = "div";
					break;
				case "date":
					$class = "validate-date-au";
					$value = $value;
					$type  = "date";
					break;
				case "heure":
					$type  = "time";
					$class = "heure inputSmall";
					break;
				case "email":
					$class = "email";
					$type  = 'email';
					break;
				case "identification":
					$class = "inputLarge";
					break;
				case "codification":
					$class = "inputSmall";
					break;
				case "localisation":
					$class = "inputLarge";
					break;
				case "bool":
					$class = "inputLarge";
					$tag   = 'checkbox';
					break;
				case "textelibre":
					//$value = br2nl(strip_tags($value));
					$value = br2nl($value);
					$class = "inputLarge";
					$tag   = 'textarea';
					break;
				case "texteformate":
					//$value = br2nl(strip_tags($value));
					$value = br2nl($value);
					$class = "inputLarge";
					$tag   = 'textarea';
					$attr  = 'ext_mce_textarea';
					break;
				case "color":
					$class = "inputTiny";
					$type  = 'color';
					if (empty($value)) $value = '_';
					break;
				case "valeur":
					$class = "inputTiny";
					break;
				case "texte":
					$class = "inputLarge";
					break;
				case "prix_precis":
					$value = maskNbre((float)$value, 6);
					$class = "inputSmall";
					break;
				case "prix":
					$value = maskNbre((float)$value, 2);
					$class = "inputSmall";
					break;
				case "password":
					$class = "inputMedium";
					$type  = 'password';
					break;
				case 'phone':
					$value = maskTel($value);
					break;
				default:
					$class = "";
					$type  = 'text';
					break;
			endswitch;
			$required      = empty($a1['required']) ? '' : 'required="required"';
			$required_hash = empty($a1['required']) ? '' : ' * ';
			switch ($tag):
				case 'div':
					$str = '<div ' . $required . ' class="' . $class . '" ' . $attr . '  >' . $value . '</div>';
					break;
				case 'textarea':
					$str = '<textarea  ' . $attr . ' ' . $required . ' class="' . $class . '" name="' . $var_name . '[' . $field_name . ']">' . $value . '</textarea>';
					break;
				case 'checkbox':
					$str = chkSch($field_name, $value);
					break;
				default:
					$placeholder = 'placeholder="' . $a2['nomAppscheme_field'] . '"';
					$str         = '<input ' . $attr . ' ' . $required . ' ' . $placeholder . ' class="' . $class . '" type="' . $type . '" name="' . $var_name . '[' . $field_name . ']" value="' . $value . '" >';
					break;
			endswitch;

			return $required_hash . $str;
		}

		/**
		 * Test si table a colonne agent
		 * @return bool
		 */
		function has_agent() {
			$TEST_AGENT = array_search('agent', array_column($this->get_grille_fk($this->table), 'table_fk'));

			return ($TEST_AGENT !== false);
		}

		public function has_field_fk($table) {
			$arr_test = array_search($table, array_column($this->get_grille_fk(), 'table_fk'));

			return ($arr_test === false) ? false : true;
		}

		function get_table_field_list() {
			$out = [];
			$rsG = $this->appscheme_has_table_field->find(['idappscheme' => (int)$this->idappscheme]);
			// idappscheme_link
			$a = [];
			while ($arrg = $rsG->getNext()) :
				$test                           = $this->appscheme->findOne(['idappscheme' => (int)$arrg['idappscheme_link']]);
				$a['nomAppscheme']              = $test['nomAppscheme'];
				$a['codeAppscheme']             = $test['codeAppscheme'];
				$test                           = $this->appscheme_field->findOne(['idappscheme_field' => (int)$arrg['idappscheme_field']]);
				$a['nomAppscheme_field_type']   = $test['nomAppscheme_field_type'];
				$a['codeAppscheme_field_type']  = $test['codeAppscheme_field_type'];
				$a['nomAppscheme_field_group']  = $test['nomAppscheme_field_group'];
				$a['codeAppscheme_field_group'] = $test['codeAppscheme_field_group'];

				$a['field_name']       = $test['codeAppscheme_field'] . ucfirst($a['nomAppscheme']);
				$a['field_name_raw']   = $test['codeAppscheme_field'];
				$out[$a['field_name']] = $a;
			endwhile;

			return $out;
		}

		function vars_to_titre($arr_vars = []) {

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
			if (!empty($arr_vars['groupBy'])) $out .= ' <i class="fa fa-caret-right"></i> par ' . $arr_vars['groupBy'];
			if (!empty($arr_vars['sortBy'])) $out .= ' <i class="fa fa-caret-right"></i> tri par' . $arr_vars['sortBy'];
			if (!empty($arr_vars['search'])) $out .= ' <i class="fa fa-caret-right"></i> Recherche : "' . $arr_vars['search'] . '"';

			return $out;
		}

		function get_titre_vars($arr_vars = []) {
			// vars to http ?
			$out_vars = [];
			foreach ($arr_vars as $name_vars => $value_vars):
				// table = namevars - id
				$table = str_replace('id', '', $name_vars);
				$nom   = 'nom' . ucfirst($table);
				$base  = $this->get_base_from_table($table);
				if (empty($table) || empty($base)) continue;
				$arr        = $this->plug($base, $table)->findOne([$name_vars => (int)$value_vars]);
				$out_vars[] = $arr[$nom];
			endforeach;
			$out = implode(' ; ', $out_vars);
			if (!empty($arr_vars['groupBy'])) $out .= ' par ' . $arr_vars['groupBy'];
			if (!empty($arr_vars['sortBy'])) $out .= ' tri par' . $arr_vars['sortBy'];

			return $out;
		}

		/*		function  plug_fs($base) {
					// PREFIX HERE POUR BASE
					$db = $this->plug_base($base);

					return $db->getGridFS();
				}*/

		function update_inc($vars, $field = '') {
			$table = $this->app_table_one['codeAppscheme'];

			if (empty($field)) $field = 'nombreVue' . ucfirst($table);
			//
			$this->plug($this->app_table_one['codeAppscheme_base'], $table)->update($vars, ['$inc' => [$field => 1]], ["upsert" => true]);

		}

		function readNext($id) {
			$arr = $this->plug('sitebase_increment', 'auto_increment')->findOne(['_id' => $id]);

			return (int)$arr['value'];
		}

		function setNext($id, $value) {
			$this->plug('sitebase_increment', 'auto_increment')->update(['_id' => $id], ['value' => (int)$value], ["upsert" => true]);

			return $value;
		}

		function resetNext($id) {
			$this->plug('sitebase_increment', 'auto_increment')->remove(['_id' => $id]);
		}

		function distinct($groupBy, $vars = [], $limit = 200, $mode = 'full', $field = '', $sort_field = ['ordre',
		                                                                                                  1]) {
			if (empty($field)) $field = 'id' . $groupBy;
			// table sur laquelle on bosse
			$dist = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme']);
			if (sizeof($vars) != 0) {
				$first_arr_dist = $dist->distinct($field, $vars);
			} else {
				$first_arr_dist = $dist->distinct($field);
			}

			if (empty($first_arr_dist)) $first_arr_dist = [];

			//  ! $groupbBy : nom de la table
			$base = $this->get_base_from_table($groupBy);
			if (empty($base)) {
				$base = "sitebase_base";
			}
			//
			if ($mode == 'full'):

				$rs_dist = $this->plug($base, $groupBy)->find(['id' . $groupBy => ['$in' => $first_arr_dist]])->sort([$sort_field[0] . ucfirst($groupBy) => $sort_field[1]])->limit($limit);

				return $rs_dist;
			endif;

			return $first_arr_dist;
		}

		function distinct_rs($vars_dist) {
			// $groupBy_table, $vars = ['1' => '1'], $limit = 250, $mode = 'full', $field = '', $sort_field = ['nom', 1]

			$groupBy_table = $vars_dist['groupBy_table'];
			$vars          = empty($vars_dist['vars']) ? [] : $vars_dist['vars'];
			$limit         = empty($vars_dist['limit']) ? 250 : $vars_dist['limit'];
			$field         = empty($vars_dist['field']) ? '' : $vars_dist['field'];
			$sort_field    = empty($vars_dist['sort_field']) ? ['nom',
			                                                    1] : $vars_dist['sort_field'];

			if (empty($field)) $field = 'id' . $groupBy_table;
			$idgroupBy_table   = 'id' . $groupBy_table;
			$arr_collect_rs    = [];
			$arr_collect       = [];
			$arr_collect_field = [];
			$tmp_iddistinct    = [];

			# tri sur table principale $this ou $table_group_by
			if ($this->has_field($sort_field[0] . ucfirst($this->table))) {
				$sort_on = $sort_field[0] . ucfirst($this->table);
			} else {
				$sort_on = $sort_field[0];
			}

			// echo "on collecte $field et tri sur " . $this->app_table_one['codeAppscheme'] . ' ' . $sort_field[0] . '<br>';
			$base_rs  = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme']);
			$basedist = new App($groupBy_table);
			// sort field peut etre count +1 ou count -1

			if ($sort_field[0] != 'count') {
				$rs_basedist = $base_rs->find($vars, ['_id' => 0])->limit(3000)->sort([$sort_on => $sort_field[1]]);
			} else
				$rs_basedist = $base_rs->find($vars, ['_id' => 0])->limit(3000)->sort(['nom' . ucfirst($this->table) => 1]);

			# boucle dans liste triée

			while ($arr_basedist = $rs_basedist->getNext()) {
				//
				# collecter ids
				# sauf si déja collecter

				$arr_collect_field[$arr_basedist[$field]][] = $arr_basedist[$this->app_field_name_id];
				if (empty($arr_basedist[$idgroupBy_table])) continue;
				//	if (empty($arr_basedist[$field])) continue;
				if (array_key_exists($arr_basedist[$field], $arr_collect)) continue;

				# table de groupby
				$arr_dist = $basedist->findOne([$idgroupBy_table => (int)$arr_basedist[$idgroupBy_table]], ['_id' => 0]);
				if (empty($arr_dist)) continue;
				$arr_collect[$arr_basedist[$field]] = $arr_basedist[$field];
				# charge
				$arr_collect_rs[$arr_basedist[$field]] = $arr_dist + $arr_basedist + ['nomAppscheme'           => $groupBy_table,
				                                                                      'count'                  => 0,
				                                                                      'groupBy'                => $groupBy_table,
				                                                                      $field                   => $arr_basedist[$field],
				                                                                      $this->app_field_name_id => (int)$arr_basedist[$this->app_field_name_id],
				                                                                      $sort_field[0]           => $arr_basedist[$sort_field[0] . ucfirst($this->table)]];

				$tmp_iddistinct[] = (int)$arr_basedist[$this->app_field_name_id];;
				$rs_base_rs_tmp                                 = $base_rs->find($vars + [$idgroupBy_table => (int)$arr_basedist[$idgroupBy_table]], ['_id' => 0]);
				$rs_base_rs_count                               = $rs_base_rs_tmp->count();
				$arr_collect_rs[$arr_basedist[$field]]['count'] = $rs_base_rs_count;
				//
				if ($sort_field[0] != 'count') if (sizeof($arr_collect_rs) == $limit) break;
			}
			if ($sort_field[0] == 'count') {
				usort($arr_collect_rs, function ($a, $b) {
					return (int)$a['count'] < (int)$b['count'];
				});
				$arr_collect_rs = array_slice($arr_collect_rs, 0, $limit - 1);
			} else {
				global $sort_vars;
				$sort_vars = $sort_on;
				usort($arr_collect_rs, function ($a, $b) {
					global $sort_vars;

					return (int)$a[$sort_vars] > (int)$b[$sort_vars];
				});
				$arr_collect_rs = array_slice($arr_collect_rs, 0, $limit - 1);
			}

			return $arr_collect_rs;

		}

		function groupBy() {

		}

		#   cds

		function has_field($field) {
			if (is_array($field)) {
				foreach ($field as $key => $value) {
					$arr_test = $this->appscheme_has_field->findOne(['idappscheme'         => (int)$this->idappscheme,
					                                                 'codeAppscheme_field' => $value]);
					if (!empty($arr_test['idappscheme'])) return true;
				}

				return false;
			}
			$arr_test = $this->appscheme_has_field->findOne(['idappscheme'         => (int)$this->idappscheme,
			                                                 'codeAppscheme_field' => $field]);

			return (!empty($arr_test['idappscheme']));
		}

		function get_full_titre_vars($arr_vars = []) {
			// vars to http ?
			$out_vars = [];
			foreach ($arr_vars as $name_vars => $value_vars):
				// table = namevars - id
				$table      = str_replace('id', '', $name_vars);
				$nom        = 'nom' . ucfirst($table);
				$base       = $this->get_base_from_table($table);
				$arr        = $this->plug($base, $table)->findOne([$name_vars => (int)$value_vars]);
				$out_vars[] = ucfirst($table) . ' ' . $arr[$nom];
			endforeach;

			return implode(' ; ', $out_vars);
		}

		function get_fk_tables($table = null) {
			if (empty($table)) $table = $this->table;
			$arr       = $this->app_conn->findOne(['codeAppscheme' => $table]);
			$grille_fk = $arr['grilleFK'];
			$out       = [];
			if (empty($grille_fk)) {
				return [];
			}
			foreach ($grille_fk as $arr_fk):
				$table_fk = $arr_fk['table'];
				$index    = $arr_fk['ordreTable'];

				$db_fk = $this->app_conn->findOne(['codeAppscheme' => $table_fk]);
				if (!empty($db_fk['codeAppscheme_base'])):
					$out[$index] = $table_fk;
				endif;
			endforeach;
			ksort($out);

			return $out;
		}

		function get_fk_id_tables($table) {
			$arr       = $this->app_conn->findOne(['codeAppscheme' => $table]);
			$grille_fk = $arr['grilleFK'];
			$out       = [];
			if (empty($grille_fk)) {
				return [];
			}
			foreach ($grille_fk as $arr_fk):
				$table_fk = $arr_fk['table'];
				$index    = $arr_fk['ordreTable'];

				$db_fk = $this->app_conn->findOne(['codeAppscheme' => $table_fk]);
				if (!empty($db_fk['codeAppscheme_base'])):
					$out['id' . $table_fk] = 'id' . $table_fk;
				endif;
			endforeach;
			ksort($out);

			return $out;
		}

		function get_display_fields($table = '') {
			$APP_TABLE      = $this->app_table_one;
			$Table          = ucfirst($table);
			$default_model  = [];
			$DEFAULT_FIELDS = $this->app_default_fields;

			foreach ($DEFAULT_FIELDS as $key_df => $value_df) {
				$Key  = ucfirst($key_df);
				$Name = empty($value_df) ? $key_df : $value_df;

				if (!empty($APP_TABLE['has' . ucfirst($Key) . 'Scheme'])):
					$default_model[$key_df] = ['field_name'     => $key_df . $Table,
					                           'field_name_raw' => $key_df,
					                           'title'          => idioma($Name)];
					if ($key_df == 'adresse'):
						// $default_model[$key_df.'2'] = array( 'field_name' => 'adresse2' . $Table , 'field_name_raw' => 'adresse2' , 'title' => idioma('..') );
						//$default_model['codePostal'] = array( 'field_name' => 'codePostal' . $Table , 'field_name_raw' => 'codePostal' , 'title' => idioma('code postal') );
						//$default_model['ville'] = array( 'field_name' => 'ville' . $Table , 'field_name_raw' => 'ville' , 'title' => idioma('ville') );
					endif;
				endif;
			}

			return $default_model;
		}

		function get_grille_count($table) {
			$arr         = $this->app_conn->findOne(['codeAppscheme' => $table]);
			$grilleCount = empty($arr['grilleCount']) ? [] : $arr['grilleCount'];

			return $grilleCount;
		}

		function get_grille($table) {
			$arr       = $this->app_conn->findOne(['codeAppscheme' => $table]);
			$grille_fk = $arr['grille'];
			$out       = [];
			if (empty($grille_fk)) {
				return [];
			}
			foreach ($grille_fk as $arr_fk):
				$table_fk = $arr_fk['table'];
				$index    = $arr_fk['ordreTable'];

				$db_fk = $this->app_conn->findOne(['codeAppscheme' => $table_fk]);
				if (!empty($db_fk['codeAppscheme_base'])):
					$out[$index] = ['base_grille'       => $db_fk['codeAppscheme_base'],
					                'collection_grille' => $db_fk['codeAppscheme'],
					                'table_grille'      => $table_fk,
					                'idtable_grille'    => 'id' . $table_fk,
					                'nomtable_grille'   => 'nom' . ucfirst($table_fk),
					                'icon_grille'       => $db_fk['icon']];
				endif;
			endforeach;
			ksort($out);

			return $out;
		}

		function get_schemes($arr_vars = [], $page = 0, $rppage = 250) {
			return $this->app_conn->find($arr_vars)->sort(['nomAppscheme' => 1])->skip($page * $rppage)->limit($rppage);
		}

		function get_http_mdl($mdl, $vars = [], $value = '', $attributes = '') {
			// http_post_data()
			$fields = ['name' => 'mike',
			           'pass' => 'se_ret'];
			$files  = [['name' => 'uimg',
			            'type' => 'image/jpeg',
			            'file' => './profile.jpg',]];

			$response = http_post_fields("http://www.example.com/", $fields, $files);

			return $response;
		}

		function consolidate_app_scheme($table) {

			$APP_GROUPE        = new App('agent_groupe');
			$APP_DROIT         = new App('agent_groupe_droit');
			$APP_BASE          = new App('appscheme_base');
			$APP_SCH           = new App('appscheme');
			$APP_SCH_FIELD     = new App('appscheme_field');
			$APP_SCH_HAS       = new App('appscheme_has_field');
			$APP_SCH_HAS_TABLE = new App('appscheme_has_table_field');
			$APP_CONSOLIDATE   = new App($table);

			$ARR         = $APP_SCH->findOne(['codeAppscheme' => $table]);
			$idappscheme = (int)$ARR['idappscheme'];
			if (empty($idappscheme)) {

				$ARR = $APP_SCH->findOne(['collection' => $table]);
				if (!empty($ARR['collection'])) {
					// echo "<br> non déclarée fallback collection";
					$idappscheme = $APP_SCH->create_update(['collection' => $table], ['codeAppscheme' => $table,
					                                                                  'nomAppscheme'  => $table]);
					$APP_SCH->consolidate_app_scheme($table);
				}

				return;
			}

			$arr_main   = $APP_SCH_HAS_TABLE->distinct_all('idappscheme_field', ['idappscheme' => $idappscheme]);
			$arr_main_2 = $APP_SCH_HAS->distinct_all('idappscheme_field', ['idappscheme' => $idappscheme]);

			if (sizeof($arr_main) != 0 && sizeof($arr_main_2) != 0) {
				$diff = array_values(array_diff($arr_main, $arr_main_2));

				if (sizeof($diff) != 0) {
					$APP_SCH_HAS_TABLE->remove(['idappscheme'       => $idappscheme,
					                            'idappscheme_field' => ['$in' => $diff]]);
				}
			}

			$ARR_GR        = $APP_GROUPE->findOne(['codeAgent_groupe' => 'ADMIN']);
			$ARR_FIELD_NOM = $APP_SCH_FIELD->findOne(['codeAppscheme_field' => 'nom']);
			$IDFIELD_NOM   = (int)$ARR_FIELD_NOM['idappscheme_field'];

			if (!empty($ARR_GR['idagent_groupe'])) {
				$APP_DROIT->create_update(['idagent_groupe' => (int)$ARR_GR['idagent_groupe'],
				                           'idappscheme'    => $idappscheme], ['C' => true,
				                                                               'R' => true,
				                                                               'U' => true,
				                                                               'D' => true,
				                                                               'L' => true]);
			}
			if (empty($ARR['codeAppscheme_base']) && !empty($ARR['base'])) {
				$idappscheme_base = $APP_BASE->create_update(['codeAppscheme_base' => $ARR['base']], ['nomAppscheme_base' => $ARR['base']]);
			}
			if (empty($ARR['iconAppscheme']) && !empty($ARR['icon'])) {
				$APP_SCH->update(['idappscheme' => $idappscheme], ['iconAppscheme' => $ARR['icon']]);
			}
			//
			$arr_has = ['statut',
			            'type',
			            'categorie',
			            'group',
			            'groupe'];

			$GRILLEFK = $APP_CONSOLIDATE->get_grille_fk();
			foreach ($arr_has as $key => $value):
				$Value  = ucfirst($value);
				$_table = $table . '_' . $value;

				if (!empty((int)$ARR['has' . $Value . 'Scheme']) && empty($GRILLEFK[$_table]) && !empty($ARR['codeAppscheme_base'])):
					$this->init_scheme($ARR['codeAppscheme_base'], $_table);
					// put it in grilleFK
					$APP_CONSOLIDATE->set_grille_fk($_table);
				endif;
				$test = strpos($table, "_$value");
				if (strpos($table, "_$value") !== false && (empty($ARR['is' . $Value . 'Scheme']) || empty($ARR['grouped_scheme']))):
					$APP_SCH->update(['idappscheme' => $idappscheme], ['is' . $Value . 'Scheme' => 1,
					                                                   'grouped_scheme'         => 1]);
				endif;
			endforeach;

			$testSF = $APP_SCH_HAS->find(['idappscheme' => $idappscheme]);
			$arrSF  = $APP_SCH_HAS->findOne(['idappscheme'       => $idappscheme,
			                                 'idappscheme_field' => $IDFIELD_NOM]);

			if (empty($arrSF['idappscheme_field']) && $testSF->count() == 0) {
				// echo "<br>champ nom par defaut vide";
				$ins                            = ['codeAppscheme_has_field' => 'nom' . ucfirst($table),
				                                   'codeAppscheme_field'     => 'nom'];
				$ins['nomAppscheme_has_field']  = $ARR_FIELD_NOM['nomAppscheme_field'] . ' ' . $ARR['nomAppscheme'];
				$ins['codeAppscheme_has_field'] = $ARR_FIELD_NOM['codeAppscheme_field'] . ucfirst($table);
				$ins['in_mini_fiche']           = 1;

				$idappscheme_has_field = $APP_SCH_HAS->create_update(['idappscheme'       => $idappscheme,
				                                                      'idappscheme_field' => (int)$IDFIELD_NOM], $ins);
				$APP_SCH_FIELD->consolidate_scheme($idappscheme_has_field);
			}
			$testSF = $APP_SCH_HAS_TABLE->find(['idappscheme' => $idappscheme]);
			$arrSF  = $APP_SCH_HAS_TABLE->findOne(['idappscheme'       => $idappscheme,
			                                       'idappscheme_link'  => $idappscheme,
			                                       'idappscheme_field' => $IDFIELD_NOM]);
			if (empty($arrSF['codeAppscheme_field']) && $testSF->count() == 0) {
				// echo "<br>champ nom personnalisé vide";
				$ins                                  = ['idappscheme'       => $idappscheme,
				                                         'idappscheme_link'  => $idappscheme,
				                                         'idappscheme_field' => $IDFIELD_NOM];
				$ins['nomAppscheme_has_table_field']  = $ARR_FIELD_NOM['nomAppscheme_field'] . ' ' . $ARR['nomAppscheme'];
				$ins['codeAppscheme_has_table_field'] = $ARR_FIELD_NOM['codeAppscheme_field'] . ucfirst($table);
				$ins['idappscheme_field']             = (int)$ARR_FIELD_NOM['idappscheme_field'];

				$idappscheme_has_table_field = $APP_SCH_HAS_TABLE->create_update(['idappscheme'      => $idappscheme,
				                                                                  'idappscheme_link' => $idappscheme], $ins);
				$APP_SCH_HAS_TABLE->consolidate_scheme($idappscheme_has_table_field);
			}

		}

		function create_update($vars, $fields = []) {
			if (empty($vars)) return false;
			$table = $this->app_table_one['codeAppscheme'];
			$test  = $this->find($vars);
			if ($test->count() == 0):
				if (empty($vars['id' . $table])) {
					$id                    = (int)$this->getNext('id' . $table);
					$fields['id' . $table] = $id;
				}
				$fields = array_merge($vars, $fields);
				$id     = $this->insert($fields);
			else:
				$arr_c  = $test->getNext();
				$id     = (int)$arr_c['id' . $table];
				$fields = array_merge($vars, $fields);
				$this->update(['id' . $table => $id], $fields);
			endif;

			return (int)$id;
		}

		function find($vars = [], $proj = []) {

			if (empty($this->app_table_one['codeAppscheme_base'])) {
				vardump($this->table);
			}
			// echo  '<br>'.$this->table.' - '.($this->app_table_one['codeAppscheme_base'].' - '. $this->app_table_one['codeAppscheme']);
			if (sizeof($proj) == 0) {
				$rs = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->find($vars);
			} else {
				$rs = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->find($vars, $proj);
			}

			return $rs;
		}

		function insert($vars = []) {
			if (empty($vars[$this->app_field_name_id])):
				$vars[$this->app_field_name_id] = (int)$this->getNext($this->app_field_name_id);
			endif;
			$rs = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->insert($vars);

			//appSOcket::send_cmd('act_add_data', $g_vars);
			$this->consolidate_scheme($vars[$this->app_field_name_id]);

			return (int)$vars[$this->app_field_name_id];
		}

		function consolidate_scheme($table_value = '') { // nom et codetype, grille_fk
			// table sur laquelle on bosse
			$name_id    = $this->app_field_name_id;
			$name_table = $this->app_table_one['codeAppscheme'];
			$Name_table = ucfirst($name_table);
			$GRILLE_FK  = $this->get_grille_fk();
			$col        = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme']);
			$arr_vars   = (empty($table_value)) ? [] : [$name_id => $table_value];
			$rs         = $col->find($arr_vars);
			// table_has_field ?
			//
			while ($arr = $rs->getNext()):
				$arr_new  = [];
				$value_id = (int)$arr[$name_id];
				// DATES debut et fin => t(ime)
				foreach ([null,
				          'Creation',
				          'Debut',
				          'Fin'] as $k => $TYPE_DATE) {
					$suffix_field = $TYPE_DATE . $Name_table;
					$to_time      = strtotime($arr['date' . $suffix_field]);
					if (!empty($arr['date' . $suffix_field])):
						if (($arr['time' . $suffix_field]) != $to_time) {
							//$arr_new['time' . $suffix_field]    = $to_time;
							$arr_new['isoDate' . $suffix_field] = new MongoDate($to_time);
						}
					endif;
				}
				//

				if (!empty($arr['color' . $Name_table]) && empty($arr['bgcolor' . $Name_table]) && $arr['color' . $Name_table] != $arr['bgcolor' . $Name_table]):

				endif;

				if ($this->has_field('slug')) {
					if (empty($arr['slug' . $Name_table]) && !empty($arr['nom' . $Name_table])) {
						$arr_new['slug' . $Name_table] = format_uri($arr['nom' . $Name_table]);
					}
				}
				// FKS : prendre nom et code => appscheme_has_table_field

				foreach ($GRILLE_FK as $field):
					$id_fk = $field['idtable_fk'];
					if (empty($arr[$id_fk])) continue;
					$arr_lnk   = $this->appscheme->findOne(['codeAppscheme' => $field['table_fk']]);
					$rs_fields = $this->appscheme_has_table_field->find(['idappscheme'      => $this->idappscheme,
					                                                     'idappscheme_link' => (int)$arr_lnk['idappscheme']]);

					// echo $rs_fields->count();
					$arrq = $this->plug($field['base_fk'], $field['table_fk'])->findOne([$id_fk => (int)$arr[$id_fk]]);
					if (empty($arrq)) continue;
					foreach ($rs_fields as $arr_fields) {
						if ($arr[$arr_fields['codeAppscheme_field'] . ucfirst($field['table_fk'])] == $arrq[$arr_fields['codeAppscheme_field'] . ucfirst($field['table_fk'])]) continue;
						$arr_new[$arr_fields['codeAppscheme_field'] . ucfirst($field['table_fk'])] = $arrq[$arr_fields['codeAppscheme_field'] . ucfirst($field['table_fk'])];
					}
					//
					foreach ($this->app_default_fields_add as $pfield_name) {
						$full_field_name = $pfield_name . ucfirst($field['table_fk']);
						if (array_key_exists($full_field_name, $arrq)) {
							if ($arr[$full_field_name] !== $arrq[$full_field_name]) {
								$arr_new[$full_field_name] = $arrq[$full_field_name];
							}
						}
					}

				endforeach;

				// TYPE STATUT
				$arr_has = ['statut',
				            'type',
				            'categorie',
				            'groupe'];
				foreach ($arr_has as $key => $statut_type):
					$Statut_type        = ucfirst($statut_type);
					$name_table_type    = $name_table . '_' . $statut_type;
					$Name_table_type    = ucfirst($name_table_type);
					$name_table_type_id = 'id' . $name_table_type;
					$_nom               = 'nom' . $name_table_type;
					//
					if (!empty($this->app_table_one['has' . $Statut_type . 'Scheme']) && !empty($arr[$name_table_type_id])):
						$APP_TYPE = new App($name_table_type);
						$arr_tmp  = $APP_TYPE->findOne([$name_table_type_id => (int)$arr[$name_table_type_id]]) || [];

						foreach ($this->app_default_fields_add as $pfield_name) {
							if (array_key_exists($pfield_name . $Name_table_type, $arr_tmp)) {
								if ($arr_tmp[$pfield_name . $Name_table_type] !== $arr[$pfield_name . $Name_table_type]) {
									$arr_new[$pfield_name . $Name_table_type] = $arr_tmp[$pfield_name . $Name_table_type];
								}
							}
						}

					endif;

				endforeach;
//

				// partie metier
				if ($name_table == 'appscheme' && !empty($arr['codeAppscheme'])):
					$this->consolidate_app_scheme($arr['codeAppscheme']);
				endif;
				if ($name_table == 'contrat'):
					if (!empty($arr['idclient'])):
						$APP_TMP  = new App('client');
						$rs_test  = $col->find(['idclient' => (int)$arr['idclient']])->sort(['dateFinContrat' => 1]);
						$arr_test = $rs_test->getNext();
						$arr_cl   = $APP_TMP->findOne(['idclient' => (int)$arr['idclient']]);

						if ($arr_cl['dateFinClient'] != $arr_test['dateFinContrat']) $APP_TMP->update(['idclient' => (int)$arr['idclient']], ['dateFinClient' => $arr_test['dateFinContrat']]);
					endif;
				endif;
				if ($name_table == 'conge'):
					$new_value = $arr['nomAgent'] . ' ' . strtolower($arr['codeConge_type']) . ' ' . fonctionsProduction::moisDate_fr($arr['dateDebutConge']);
					if ($arr['nom' . $Name_table] != $new_value) {
						$arr_new['nom' . $Name_table] = $new_value;
					}
				endif;
				if ($name_table == 'ressource'):
					$new_value = $arr['quantite' . $Name_table] . ' * ' . $arr['nomProduit'] . ' - ' . $arr['nomProspect'] . $arr['nomClient'];
					if ($arr['nom' . $Name_table] != $new_value) {
						$arr_new['nom' . $Name_table] = $new_value;
					};
				endif;
				if ($name_table == 'opportunite_ligne'):
					$new_value = $arr['quantite' . $Name_table] . ' * ' . $arr['nomProduit'];
					if ($arr['nom' . $Name_table] != $new_value) {
						$arr_new['nom' . $Name_table] = $new_value;
					};
				endif;

				if ($name_table == 'commande'):
					$new_value = date('dmy') . auto_code($arr['codeClient']) . auto_code($arr['codeShop']);
					if ($arr['code' . $Name_table] != $new_value) {
						// $arr_new['code' . $Name_table] = $new_value;
					};
				endif;

				if (empty($arr['code' . $Name_table]) && empty($arr_new['code' . $Name_table])):
					$arr_new['code' . $Name_table] = auto_code($arr['nom' . $Name_table]);
				endif;
				if (empty($arr['nom' . $Name_table]) && empty($arr_new['nom' . $Name_table])):
					foreach ($GRILLE_FK as $field):
						$id_fk = $field['idtable_fk'];
						if (empty($arr[$id_fk])) continue;
						//
						$dsp_name = $arr['nom' . ucfirst($field['table_fk'])];
						//
						$arr_new['nom' . $Name_table] .= strtolower($dsp_name) . ' ';
					endforeach;
				endif;
				if (empty($arr['nom' . $Name_table]) && empty($arr_new['nom' . $Name_table])):
					if (!empty($APP_TABLE['hasTypeScheme'])):
						//	$arr_new['nom' . $Name_table] = $arr['nom' . $Name_table.'_type'];
					endif;
					// $arr_new['nom' . $Name_table] .= substr(strip_tags($arr['code' . $Name_table]),0,5)  ;
					if (!empty($APP_TABLE['hasDateScheme']) && !empty($arr['dateDebut' . $Name_table])):
						$arr_new['nom' . $Name_table] .= ' ' . date_fr($arr['dateDebut' . $Name_table]);
					endif;
					// $arr_new['nom' . $Name_table] .= substr(strip_tags($arr['description' . $Name_table]),0,10);
				endif;
				if (sizeof($arr_new) != 0) {
					$arr_new ['updated_fields'] = $arr_new; // log ???
					// versioning
					$arr_new['dateModification' . $Name_table]  = date('Y-m-d');
					$arr_new['heureModification' . $Name_table] = date('H:i:s');
					$arr_new['timeModification' . $Name_table]  = time();

					$col->update([$name_id => $value_id], ['$set' => $arr_new], ['upsert' => true]);
				}
				if ($rs->count() == 1) {
					return $arr_new;
				}

			endwhile;
		}

		function update($vars, $fields = [], $upsert = true) {
			$table       = $this->app_table_one['codeAppscheme'];
			$table_value = (int)$vars[$this->app_field_name_id];
			// anciennes valeurs
			if (empty($table_value)) {
				echo "probleme de mise à jour";

				//vardump_async("$table sans value en update");

				return;
			}
			$arr_one_before = $this->findOne([$this->app_field_name_id => $table_value]);
			// differences avec anciennes valeurs
			$arr_inter = array_diff_assoc($fields, (array)$arr_one_before);
			if (sizeof($arr_inter) == 0) {
				//	AppSocket::send_cmd('act_notify', ['msg' => 'Mise à jour inutile'], session_id());

				return;
			}
			// on garde la différence
			$fields = $arr_inter;
			// UPDATE !!!
			$this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->update($vars, ['$set' => $fields], ['upsert' => $upsert]);
			$this->consolidate_scheme($table_value);
			//
			$arr_one_after = $this->findOne([$this->app_field_name_id => $table_value]);

			$updated_fields_real = array_diff_assoc((array)$arr_one_after, (array)$arr_one_before);
			$this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->update($vars, ['$set' => ['updated_fields' => $updated_fields_real]], ['upsert' => $upsert]);

			// AppSocket::send_cmd('act_notify',['msg'=>json_encode($updated_fields_real,JSON_PRETTY_PRINT),'options'=>['sticky'=>true]],session_id());

			$update_diff_cast = [];
			foreach ($updated_fields_real as $k => $v):
				$exp['field_name']  = $k;
				$exp['field_value'] = $v;
				//if($v==$vars[$k] || empty($vars[$k])) continue;
				$update_diff_cast[$k] = App::cast_field_all($exp, true); // new_vars deviendra vars
			endforeach;

			AppSocket::send_cmd('act_upd_data', ['table'       => $table,
			                                     'table_value' => $table_value,
			                                     'new_vars'    => $update_diff_cast]);

			// log
			$R_FK = $this->get_reverse_grille_fk($this->app_table_one['codeAppscheme'], (int)$vars[$this->app_field_name_id]);
			//
			if (!empty($fields['nom' . ucfirst($table)])):
				foreach ($R_FK as $arr_fk):
					$value_rfk               = $arr_fk['table_value'];
					$table_rfk               = $arr_fk['table'];
					$vars_rfk['vars']        = ['id' . $table => $table_value];
					$vars_rfk['table']       = $table_rfk;
					$vars_rfk['table_value'] = $value_rfk;
					$count                   = $arr_fk['count'];

					if (!empty($count)):
						$APPCONV   = new App($table_rfk);
						$sd_vars   = ['id' . $table => $table_value];
						$fields_fk = ['nom' . ucfirst($table) => $fields['nom' . ucfirst($table)]];
						$APPCONV->update($sd_vars, $fields_fk);

					endif;
				endforeach;
			endif;

			return $update_diff_cast;
		}

		function cast_field_all($vars = [], $nude = false) { // json_data_table !
			$field_name = $vars['field_name'];

			if (is_array($vars['field_value'])) {

				return $vars['field_value'];
			}
			$value = $vars['field_value'];

			if (empty($vars['codeAppscheme_field_type'])) {
				$arr_tmp                          = $this->appscheme_has_field->findOne(['codeAppscheme_has_field' => $field_name]);
				$arr_tmp                          = $this->appscheme_field->findOne(['codeAppscheme_field' => $arr_tmp['codeAppscheme_field']]);
				$vars['codeAppscheme_field_type'] = $arr_tmp['codeAppscheme_field_type'];
			}

			switch ($vars['codeAppscheme_field_type']):
				case 'distance':
					$value = round($value / 1000, 2) . ' kms';
					break;
				case 'minutes':
					if (empty($value)) break;
					$value = ceil($value / 60) . ' minutes ';
					break;
				case 'bool':
					$arr_field = $this->appscheme_has_field->findOne(['codeAppscheme_has_field' => $field_name]);
					$arr_tmp   = $this->appscheme_field->findOne(['codeAppscheme_field' => $arr_field['codeAppscheme_field']]);
					$icon      = $arr_tmp['iconAppscheme_field'];
					$css       = empty($value) ? 'textgris' : 'textvert';
					$text      = empty($icon) ? ouiNon($value) : ouiNon($value);
					$value     = "<i class = 'fa fa-$icon  $css'  ></i > $text";
					break;
				case 'valeur':
					$value = $value;
					break;
				case 'prix':
					$value = maskNbre($value, 2) . ' €';
					break;
				case 'prix_precis':
					$value = maskNbre((float)$value, 6) . ' €';
					break;
				case 'pourcentage':
					$value = (float)$value . ' %';
					break;
				case 'date':
					$value = date_fr($value);
					break;
				case 'heure':
					$value = maskHeure($value);
					break;
				case 'phone':
					$value = maskTel($value);
					break;
				case 'icon':
					$value = "<i class= 'fa fa-$value'></i>";
					break;
				case 'color':
					$value = "<i class='fa fa-square' style='color:$value ;margin:auto auto;position:relative;'  ></i>";
					break;
				case 'textelibre':
					$value = nl2br(stripslashes($value));
					break;
			endswitch;
			$str = "$value";

			return $str;
		}

		function distinct_all($groupBy, $vars = [], $limit = 200, $mode = 'full') {

			// table sur laquelle on bosse
			$dist = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme']);
			if (sizeof($vars) != 0) {
				$first_arr_dist = $dist->distinct($groupBy, $vars);
			} else {
				$first_arr_dist = $dist->distinct($groupBy);
			}

			return $first_arr_dist;
		}

		function remove($vars = []) {
			if (sizeof($vars) == 0) return;
			$this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->remove($vars);
		}

		function init_scheme($base, $table, $options = [], $force = false) {
			if (empty($table) || empty($base)) return false;
			$test_base = $this->appscheme_base->findOne(['codeAppscheme_base' => $base]);
			$test      = $this->appscheme->findOne(['codeAppscheme' => $table]);
			if (empty($test_base['idappscheme_base'])) {
				$ins['idappscheme_base']   = $this->getNext('idappscheme_base');
				$ins['codeAppscheme_base'] = $base;
				$ins['nomAppscheme_base']  = $base;
				$this->appscheme_base->insert($ins);

			} else {
				$ins['idappscheme_base']   = (int)$test_base['idappscheme_base'];
				$ins['codeAppscheme_base'] = $base;
				$ins['nomAppscheme_base']  = $test_base['nomAppscheme_base'];
			}
			if (empty($test['idappscheme'])) {
				$ins['idappscheme'] = $this->getNext('idappscheme');
			} elseif ($force == true) {

				$ins['idappscheme'] = (int)$test['idappscheme'];
				unset($ins['_id']);
			} else {
				return;
			}

			$ins['codeAppscheme'] = $table;
			$ins['nomAppscheme']  = $table;
			if (!empty($options['has'])) {
				foreach ($options['has'] as $key => $value) {
					$ins['has' . ucfirst($value) . 'Scheme'] = 1;
				}
				unset($options['has']);
			}
			if (!empty($options['has'])) {
				foreach ($options['has'] as $key => $value) {
					$ins['has' . ucfirst($value) . ucfirst($table)] = 1;
				}
				unset($options['has']);
			}
			if (!empty($options['fields'])) {
				foreach ($options['fields'] as $key => $value) {
					$ARRF = $this->appscheme_field->findOne(['codeAppscheme_field' => $value]);
					if (!empty($ARRF['idappscheme_field'])) $arr_field[] = (int)$ARRF['idappscheme_field']; else {
						$APPF        = new App('appscheme_field');
						$arr_field[] = $APPF->create_update(['codeAppscheme_field' => $value], ['nomAppscheme_field' => $value]);
					}
				}
				unset($options['fields']);
			}
			if (!empty($options['grilleFK'])) {
				$ARRFK = [];
				foreach ($options['grilleFK'] as $key => $value) {
					$ARRFK [(int)$key] = ['table'      => $value,
					                      'ordreTable' => (int)$key,
					                      'uid'        => uniqid()];
				}
				$ins['grilleFK'] = $ARRFK;
				unset($options['grilleFK']);
			}
			$ins = array_filter($ins);
			$this->appscheme->update(['idappscheme' => $ins['idappscheme']], ['$set' => $ins], ['upsert' => 1]);

			$APP_TMP = new App('appscheme');
			$APP_TMP->consolidate_scheme($ins['idappscheme']);
			if (!empty($arr_field)) {
				$APP_SCH_HAS = new App('appscheme_has_field');
				foreach ($arr_field as $key => $value) {
					$id      = (int)$value;
					$idschas = $APP_SCH_HAS->create_update(['idappscheme'       => $ins['idappscheme'],
					                                        'idappscheme_field' => $id]);
					$APP_SCH_HAS->consolidate_scheme($idschas);
				}
			}

			return new App($table);
		}

		function update_native($vars, $fields = [], $upsert = true) {
			$table = $this->app_table_one['codeAppscheme'];
			if (empty($vars[$this->app_field_name_id]) && empty($vars['_id'])) {
				if (empty($table_value)) {
					// vardump_async("$table sans value en update");
					//return;
				}
			}
			$this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->update($vars, ['$set' => $fields], ['upsert' => $upsert,
			                                                                                                                                    'multi'  => 1]);

		}

		/**
		 * field list + grille fk
		 *
		 * @param array $in
		 *
		 * @return array
		 */
		function get_field_list_all($in = []) {
			$out = [];
			if (!empty($in)) $DIST = $this->appscheme_field->distinct('idappscheme_field', $in);
			if (!empty($in)) $DIST_2 = $this->appscheme_has_field->distinct('idappscheme_field');
			if (empty($DIST)) return $out;
			$DIST_all = array_merge($DIST, $DIST_2);
			$DIST_all = (!empty($DIST_all)) ? ['idappscheme_field' => ['$in' => $DIST_all]] : [];

			$rsG = $this->appscheme_has_field->find($DIST_all + ['idappscheme' => (int)$this->idappscheme]);
			// $rsG = $this->appscheme_field->find( ['idappscheme_field' => ['$in' => $arr_field]]);
			$a = [];
			while ($arrg = $rsG->getNext()) :
				$test                           = $this->appscheme_field->findOne(['idappscheme_field' => (int)$arrg['idappscheme_field']]);
				$a['iconAppscheme_field']       = $test['iconAppscheme_field'];
				$a['nomAppscheme_field']        = $test['nomAppscheme_field'];
				$a['codeAppscheme_field']       = $test['codeAppscheme_field'];
				$a['nomAppscheme_field_type']   = $test['nomAppscheme_field_type'];
				$a['codeAppscheme_field_type']  = $test['codeAppscheme_field_type'];
				$a['nomAppscheme_field_group']  = $test['nomAppscheme_field_group'];
				$a['codeAppscheme_field_group'] = $test['codeAppscheme_field_group'];
				$a['codeAppscheme_field']       = $test['codeAppscheme_field'] . ucfirst($arrg['codeAppscheme']);
				$a['field_name']                = $test['codeAppscheme_field'] . ucfirst($arrg['codeAppscheme']);
				$a['field_name_raw']            = $test['codeAppscheme_field'];
				$out[$a['field_name']]          = $a;
			endwhile;
			$a = [];
			foreach ($this->grilleFK as $keyFK => $fieldFK) {
				$table_fk = $fieldFK['table'];
				$rsG      = $this->appscheme_has_field->find(['codeAppscheme' => $table_fk]);

				while ($arrg = $rsG->getNext()) :
					$test                           = $this->appscheme_field->findOne(['idappscheme_field' => (int)$arrg['idappscheme_field']]);
					$a['iconAppscheme_field']       = $test['iconAppscheme_field'];
					$a['nomAppscheme_field']        = $test['nomAppscheme_field'];
					$a['codeAppscheme_field']       = $test['codeAppscheme_field'];
					$a['nomAppscheme_field_type']   = $test['nomAppscheme_field_type'];
					$a['codeAppscheme_field_type']  = $test['codeAppscheme_field_type'];
					$a['nomAppscheme_field_group']  = $test['nomAppscheme_field_group'];
					$a['codeAppscheme_field_group'] = $test['codeAppscheme_field_group'];
					$a['codeAppscheme_field']       = $test['codeAppscheme_field'] . ucfirst($arrg['codeAppscheme']);
					$a['field_name']                = $test['codeAppscheme_field'] . ucfirst($arrg['codeAppscheme']);
					$a['field_name_raw']            = $test['codeAppscheme_field'];
					$out[$a['field_name']]          = $a;
				endwhile;
			}

			return $out;
		}

		/**
		 * @param array $in
		 *
		 * @return  array
		 */
		function get_field_list($in = []) {
			$out = $DIST = $DIST_2 = [];
			if (!empty($in)) $DIST = $this->appscheme_field->distinct('idappscheme_field', $in);
			if (!empty($in)) $DIST_2 = $this->appscheme_has_field->distinct('idappscheme_field');
			$DIST_all = array_merge($DIST, $DIST_2);
			$DIST_all = (!empty($DIST_all)) ? ['idappscheme_field' => ['$in' => $DIST_all]] : [];
			$rsG      = $this->appscheme_has_field->find($DIST + ['idappscheme' => (int)$this->idappscheme]);
			// $rsG = $this->appscheme_field->find( ['idappscheme_field' => ['$in' => $arr_field]]);
			$a = [];
			while ($arrg = $rsG->getNext()) :
				$test                           = $this->appscheme_field->findOne(['idappscheme_field' => (int)$arrg['idappscheme_field']]);
				$a['iconAppscheme_field']       = $test['iconAppscheme_field'];
				$a['nomAppscheme_field']        = $test['nomAppscheme_field'];
				$a['codeAppscheme_field']       = $test['codeAppscheme_field'];
				$a['nomAppscheme_field_type']   = $test['nomAppscheme_field_type'];
				$a['codeAppscheme_field_type']  = $test['codeAppscheme_field_type'];
				$a['nomAppscheme_field_group']  = $test['nomAppscheme_field_group'];
				$a['codeAppscheme_field_group'] = $test['codeAppscheme_field_group'];
				$a['codeAppscheme_field']       = $test['codeAppscheme_field'] . ucfirst($arrg['codeAppscheme']);
				$a['field_name']                = $test['codeAppscheme_field'] . ucfirst($arrg['codeAppscheme']);
				$a['field_name_raw']            = $test['codeAppscheme_field'];
				$out[$a['field_name']]          = $a;
			endwhile;

			$rsG = $this->appscheme_has_table_field->find($DIST + ['idappscheme' => (int)$this->idappscheme]);
			// idappscheme_link
			$a = [];
			while ($arrg = $rsG->getNext()) :
				$test                           = $this->appscheme->findOne(['idappscheme' => (int)$arrg['idappscheme_link']]);
				$a['nomAppscheme']              = $test['nomAppscheme'];
				$a['codeAppscheme']             = $test['codeAppscheme'];
				$test                           = $this->appscheme_field->findOne(['idappscheme_field' => (int)$arrg['idappscheme_field']]);
				$a['iconAppscheme_field']       = $test['iconAppscheme_field'];
				$a['nomAppscheme_field']        = $test['nomAppscheme_field'];
				$a['codeAppscheme_field']       = $test['codeAppscheme_field'];
				$a['nomAppscheme_field_type']   = $test['nomAppscheme_field_type'];
				$a['codeAppscheme_field_type']  = $test['codeAppscheme_field_type'];
				$a['nomAppscheme_field_group']  = $test['nomAppscheme_field_group'];
				$a['codeAppscheme_field_group'] = $test['codeAppscheme_field_group'];

				$a['field_name']       = $test['codeAppscheme_field'] . ucfirst($a['codeAppscheme']);
				$a['field_name_raw']   = $test['codeAppscheme_field'];
				$out[$a['field_name']] = $a;
			endwhile;

			return $out;
		}

		/**
		 * @param array $vars
		 * @param bool  $nude
		 */
		function cast_field($vars = [], $nude = false) { // json_data_table !
			$field_name     = $vars['field_name'];
			$field_name_raw = $vars['field_name_raw'];
			if (is_array($vars['field_value'])) {

				return $vars['field_value'];
			}
			$value = $vars['field_value'];//nl2br($vars['field_value']);

			if (str_find($field_name, 'date')) {
				$value = date_fr($value);
			}
			if (str_find($field_name, 'nbre')) {
				$value = maskNbre($value);
			}
			if (str_find($field_name, 'prix')) {
				// $value = maskNbre($value);
			}
			if (str_find($field_name, 'telephone')) {
				$value = maskTel($value);
			}
			if (str_find($field_name, 'mobile')) {
				$value = maskTel($value);
			}
			if (str_find($field_name, 'duree')) {
				$value = (float)$value;
			}
			if (str_find($field_name, 'code')) {

			}
			if (str_find($field_name, 'color')) {
				$vars['codeAppscheme_field_type'] = 'color';
			}
			if (str_find($field_name, 'icon')) {
				$vars['codeAppscheme_field_type'] = 'icon';
			}
			if (str_find($field_name, 'actif')) {
				$vars['codeAppscheme_field_type'] = 'bool';
			}
			if (empty($vars['codeAppscheme_field_type'])) {
				$arr_tmp                          = $this->appscheme_field->findOne(['codeAppscheme_field' => $field_name_raw]);
				$vars['codeAppscheme_field_type'] = $arr_tmp['codeAppscheme_field_type'];
			}
			switch ($vars['codeAppscheme_field_type']):
				case 'distance':
					$value = round($value / 1000, 2) . ' kms';
					break;
				case 'minutes':
					if (empty($value)) break;
					$value = ceil($value / 60);
					break;
				case 'bool':
					$arr_tmp = $this->appscheme_field->findOne(['codeAppscheme_field' => $field_name_raw]);
					$icon    = $arr_tmp['iconAppscheme_field'];
					$css     = empty($value) ? 'textgris' : '';
					$text    = empty($icon) ? ouiNon($value) : '';
					$value   = "<i class = 'fa fa-$icon  $css'  ></i >";
					break;
				case 'valeur':
					$value = $value;
					break;
				case 'prix':
					$value = maskNbre($value, 2) . ' €';
					break;
				case 'prix_precis':
					$value = maskNbre((float)$value, 6) . ' €';
					break;
				case 'pourcentage':
					$value = (float)$value . ' %';
					break;
				case 'date':
					$value = date_fr($value);
					break;
				case 'heure':
					$value = maskHeure($value);
					break;
				case 'phone':
					$value = maskTel($value);
					break;
				case 'icon':
					$value = "<i class= 'fa fa-$value'></i>";
					break;
				case 'color':
					$value = "<i class='fa fa-circle' style='color:$value ;margin:auto auto;position:relative;'  ></i>";
					break;
				case 'textelibre':
					$value = nl2br(stripslashes($value));
					break;
			endswitch;
			$str = "$value";

			return $str;
		}

		function query($vars = [], $page = 0, $rppage = 40, $fields = []) {
			if (empty($rppage)) {
				$rppage = 15;
			}
			if (empty($this->app_table_one['codeAppscheme_base'])) {
				die('   [' . $this->table . '-' . $this->app_table_one['codeAppscheme_base'] . '-' . $this->app_table_one['codeAppscheme'] . ']');
				exit;
			}
			$rs       = $this->plug($this->app_table_one['codeAppscheme_base'], $this->app_table_one['codeAppscheme'])->find($vars, $fields);
			$totcount = $rs->count();
			$rs->sort([$this->app_field_name_top => -1,
			           $this->app_field_name_nom => 1]);
			$rs->skip($page * $rppage)->limit($rppage);

			return $rs;
		}

		function get_field_list_raw($in = []) {
			$out = [];
			if (!empty($in)) $DIST = $this->appscheme_field->distinct('idappscheme_field', $in);
			$DIST = (!empty($DIST)) ? ['idappscheme_field' => ['$in' => $DIST]] : [];

			$rsG = $this->appscheme_has_field->find($DIST + ['idappscheme' => (int)$this->idappscheme]);
			// $rsG = $this->appscheme_field->find( ['idappscheme_field' => ['$in' => $arr_field]]);
			$a = [];
			while ($arrg = $rsG->getNext()) :
				$test                           = $this->appscheme_field->findOne(['idappscheme_field' => (int)$arrg['idappscheme_field']]);
				$a['iconAppscheme_field']       = $test['iconAppscheme_field'];
				$a['nomAppscheme_field']        = $test['nomAppscheme_field'];
				$a['codeAppscheme_field']       = $test['codeAppscheme_field'];
				$a['nomAppscheme_field_type']   = $test['nomAppscheme_field_type'];
				$a['codeAppscheme_field_type']  = $test['codeAppscheme_field_type'];
				$a['nomAppscheme_field_group']  = $test['nomAppscheme_field_group'];
				$a['codeAppscheme_field_group'] = $test['codeAppscheme_field_group'];
				$a['codeAppscheme_field']       = $test['codeAppscheme_field'] . ucfirst($arrg['codeAppscheme']);
				$a['field_name']                = $test['codeAppscheme_field'] . ucfirst($arrg['codeAppscheme']);
				$a['field_name_raw']            = $test['codeAppscheme_field'];
				$out[$a['field_name']]          = $a;
			endwhile;

			return $out;
		}
	}
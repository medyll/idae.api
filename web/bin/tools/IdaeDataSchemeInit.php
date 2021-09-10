<?php

	use Idae\Connect\IdaeConnect;

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 29/06/2018
	 * Time: 01:06
	 */

	class IdaeDataSchemeInit extends IdaeConnect {

		public function __construct($table = '') {
			parent::__construct();
		}

		private function getNext($id, $min = 1) {

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

		private function create_base_model($base) {

			$ins['codeAppscheme_base'] = $base;
			$ins['nomAppscheme_base']  = $base;
			$ins['idappscheme_base']   = $this->getNext('idappscheme_base');

			$this->appscheme_base_model_instance->insert($ins);

			return $ins['idappscheme_base'];
		}

		private function create_appscheme_model($table) {

			$ins['codeAppscheme'] = $table;
			$ins['nomAppscheme']  = $table;
			$ins['idappscheme']   = $this->getNext('idappscheme');

			$this->appscheme_model_instance->insert($ins);

			return $ins['idappscheme'];
		}

		private function create_appscheme_field_group_model($codeAppscheme_field_group, $nomAppscheme_field_group = null) {

			$ins['codeAppscheme_field_group'] = $codeAppscheme_field_group;
			$ins['nomAppscheme_field_group']  = $nomAppscheme_field_group ?: $codeAppscheme_field_group;
			$ins['idappscheme_field_group']   = $this->getNext('idappscheme_field_group');

			$this->appscheme_field_group_model_instance->insert($ins);

			return $ins['idappscheme_field_group'];
		}

		private function create_appscheme_field_type_model($codeAppscheme_field_type, $nomAppscheme_field_type = null) {

			$ins['codeAppscheme_field_type'] = $codeAppscheme_field_type;
			$ins['nomAppscheme_field_type']  = $nomAppscheme_field_type ?: $codeAppscheme_field_type;
			$ins['idappscheme_field_type']   = $this->getNext('idappscheme_field_type');

			$this->appscheme_field_type_model_instance->insert($ins);

			return $ins['idappscheme_field_type'];
		}

		private function create_appscheme_field_model($codeAppscheme_field, $nomAppscheme_field = null, $codeAppscheme_field_group = null, $codeAppscheme_field_type = null) {

			$ins['codeAppscheme_field'] = $codeAppscheme_field;
			$ins['nomAppscheme_field']  = $nomAppscheme_field ?: $codeAppscheme_field;
			$ins['idappscheme_field']   = $this->getNext('idappscheme_field');
			if (!empty($codeAppscheme_field_group)) {
				$ins['idappscheme_field_group'] = $this->init_scheme_field_group($codeAppscheme_field_group);
			}
			if (!empty($codeAppscheme_field_type)) {
				$ins['idappscheme_field_type'] = $this->init_scheme_field_type($codeAppscheme_field_type);
			}

			$this->appscheme_field_model_instance->insert($ins);

			return $ins['idappscheme_field'];
		}

		private function create_appscheme_has_field_model($codeAppscheme, $codeAppscheme_field, $nomAppscheme_has_field = null) {

			$ins['codeAppscheme']           = $codeAppscheme;
			$ins['codeAppscheme_field']     = $codeAppscheme_field;
			$ins['codeAppscheme_has_field'] = $codeAppscheme_field . ucfirst($codeAppscheme);

			$ARRS = $this->appscheme_model_instance->findOne(['codeAppscheme' => $codeAppscheme]);
			$ARRF = $this->appscheme_field_model_instance->findOne(['codeAppscheme_field' => $codeAppscheme_field]);

			$ins['nomAppscheme_field']     = $ARRF['nomAppscheme_field'];
			$ins['nomAppscheme_has_field'] = $nomAppscheme_has_field ?: $ARRF['nomAppscheme_field'] . ' ' . $ARRS['nomAppscheme'];

			$ins['idappscheme']       = (int)$ARRS['idappscheme'];
			$ins['idappscheme_field'] = (int)$ARRF['idappscheme_field'];

			$ins['idappscheme_has_field'] = $this->getNext('idappscheme_has_field');

			$this->appscheme_has_field_model_instance->insert($ins);
		}

		private function create_appscheme_has_table_field_model($codeAppscheme, $codeAppscheme_link, $codeAppscheme_field, $nomAppscheme_has_table_field = null) {


			$ARR_SCHEME      = $this->appscheme_model_instance->findOne(['codeAppscheme' => $codeAppscheme]);
			$ARR_SCHEME_LINK = $this->appscheme_model_instance->findOne(['codeAppscheme' => $codeAppscheme_link]);
			$ARR_FIELD       = $this->appscheme_field_model_instance->findOne(['codeAppscheme_field' => $codeAppscheme_field]);

			$ins['codeAppscheme']                 = $codeAppscheme;
			$ins['codeAppscheme_field']           = $codeAppscheme_field;
			$ins['codeAppscheme_has_table_field'] = $codeAppscheme_field . ucfirst($codeAppscheme) . ' ' . $codeAppscheme_link;
			$ins['nomAppscheme_has_table_field']  = $nomAppscheme_has_table_field ?: $ARR_FIELD['nomAppscheme_field'] . ' ' . $ARR_SCHEME['nomAppscheme'] . ' ' . $ARR_SCHEME_LINK['nomAppscheme'];

			$ins['idappscheme']       = (int)$ARR_SCHEME['idappscheme'];
			$ins['idappscheme_link']  = (int)$ARR_SCHEME_LINK['idappscheme'];
			$ins['idappscheme_field'] = (int)$ARR_FIELD['idappscheme_field'];

			$ins['idappscheme_has_field'] = $this->getNext('idappscheme_has_field');

			$this->appscheme_has_table_field_model_instance->insert($ins);
		}

		public function init_scheme_field($codeAppscheme_field, $nomAppscheme_field = null, $codeAppscheme_field_group = null, $codeAppscheme_field_type = null) {

			$ARRF = $this->appscheme_field_model_instance->findOne(['codeAppscheme_field' => $codeAppscheme_field]);

			if (!empty($ARRF['idappscheme_field'])) {
				return (int)$ARRF['idappscheme_field'];
			} else {
				return $this->create_appscheme_field_model($codeAppscheme_field, $nomAppscheme_field ?: $codeAppscheme_field , $codeAppscheme_field_group, $codeAppscheme_field_type);
			}
		}

		public function init_scheme_field_type($codeAppscheme_field_type, $nomAppscheme_field_type = null) {

			$ARRF = $this->appscheme_field_type_model_instance->findOne(['codeAppscheme_field_type' => $codeAppscheme_field_type]);

			if (!empty($ARRF['idappscheme_field_type'])) {
				return (int)$ARRF['idappscheme_field_type'];
			} else {
				return $this->create_appscheme_field_type_model($codeAppscheme_field_type, $nomAppscheme_field_type);
			}
		}

		public function init_scheme_field_group($codeAppscheme_field_group, $nomAppscheme_field_group = null) {

			$ARRF = $this->appscheme_field_group_model_instance->findOne(['codeAppscheme_field_group' => $codeAppscheme_field_group]);

			if (!empty($ARRF['idappscheme_field_group'])) {
				return (int)$ARRF['idappscheme_field_group'];
			} else {
				return $this->create_appscheme_field_group_model($codeAppscheme_field_group, $nomAppscheme_field_group);
			}
		}

		public function init_scheme_has_field($codeAppscheme, $codeAppscheme_field, $nomAppscheme_has_field = null) {

			$this->init_scheme_field($codeAppscheme_field);

			$ARRF = $this->appscheme_has_field_model_instance->findOne(['codeAppscheme'       => $codeAppscheme,
			                                                            'codeAppscheme_field' => $codeAppscheme_field]);

			if (!empty($ARRF['idappscheme_has_field'])) {
				return (int)$ARRF['idappscheme_has_field'];
			} else {
				return $this->create_appscheme_has_field_model($codeAppscheme, $codeAppscheme_field, $nomAppscheme_has_field);
			}
		}

		public function init_scheme_has_table_field($codeAppscheme, $codeAppscheme_link, $codeAppscheme_field, $nomAppscheme_has_table_field = null) {

			$test_appscheme      = $this->appscheme_model_instance->findOne(['codeAppscheme' => $codeAppscheme]);
			$test_appscheme_link = $this->appscheme_model_instance->findOne(['codeAppscheme' => $codeAppscheme_link]);

			$idappscheme_field = $this->init_scheme_field($codeAppscheme_field);
			$idappscheme       = (int)$test_appscheme['idappscheme'];
			$idappscheme_link  = (int)$test_appscheme_link['idappscheme'];

			$ARRF = $this->appscheme_has_table_field_model_instance->findOne(['idappscheme'       => $idappscheme,
			                                                                  'idappscheme_link'  => $idappscheme_link,
			                                                                  'idappscheme_field' => $idappscheme_field]);

			if (!empty($ARRF['idappscheme_has_table_field'])) {
				return (int)$ARRF['idappscheme_has_table_field'];
			} else {
				return $this->create_appscheme_has_table_field_model($codeAppscheme, $codeAppscheme_link, $codeAppscheme_field, $nomAppscheme_has_table_field);
			}
		}

		public function consolidate_app_scheme($table) {

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
			            'ligne',
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
			// todo activate this
			/*if (empty($arrSF['idappscheme_field']) && $testSF->count() == 0) {
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
			}*/

		}

		private function init_scheme_droits($idappscheme) {
			$APP_DROIT  = new App('agent_groupe_droit');
			$APP_GROUPE = new App('agent_groupe');

			$ARR_GR = $APP_GROUPE->findOne(['codeAgent_groupe' => 'ADMIN']);

			if (!empty($ARR_GR['idagent_groupe'])) {
				$APP_DROIT->create_update(['idagent_groupe' => (int)$ARR_GR['idagent_groupe'],
				                           'idappscheme'    => $idappscheme], ['C' => true,
				                                                               'R' => true,
				                                                               'U' => true,
				                                                               'D' => true,
				                                                               'L' => true]);
			}
		}

		public function init_scheme($base, $table, $options = [], $force = false) {

			if (empty($table) || empty($base)) return false;

			$test_base = $this->appscheme_base_model_instance->findOne(['codeAppscheme_base' => $base]);

			if (empty($test_base['idappscheme_base'])) {
				$ins['idappscheme_base']   = $this->create_base_model($base);
				$ins['codeAppscheme_base'] = $base;
				$ins['nomAppscheme_base']  = $base;

			} else {
				$ins['idappscheme_base']   = (int)$test_base['idappscheme_base'];
				$ins['codeAppscheme_base'] = $base;
				$ins['nomAppscheme_base']  = $test_base['nomAppscheme_base'];
			}

			$test_appscheme = $this->appscheme_model_instance->findOne(['codeAppscheme' => $table]);

			if (empty($test_appscheme['idappscheme'])) {
				$ins['idappscheme']   = $this->create_appscheme_model($table);
				$ins['codeAppscheme'] = $table;
				$ins['nomAppscheme']  = $table;
			} elseif ($force == true) {
				$ins['idappscheme']   = (int)$test_appscheme['idappscheme'];
				$ins['codeAppscheme'] = $table;
				unset($ins['_id']);
			} else {
				// return false;
				$ins['idappscheme']   = (int)$test_appscheme['idappscheme'];
				$ins['codeAppscheme'] = $table;
				unset($ins['_id']);
			}

			if (!empty($options['has'])) {
				foreach ($options['has'] as $key => $value) {
					$ins['has' . ucfirst($value) . 'Scheme'] = 1;
				}
				unset($options['has']);
			}

			if (!empty($options['is'])) {
				foreach ($options['is'] as $key => $value) {
					$ins['is' . ucfirst($value) . ucfirst($table)] = 1;
				}
				unset($options['is']);
			}

			if (!empty($options['fields'])) {
				foreach ($options['fields'] as $key => $value) {
					$this->init_scheme_has_field($table, $value);
				}
				unset($options['fields']);
			}
			if (!empty($options['grilleFK'])) { // should exists in the same base
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

			$this->appscheme_model_instance->update(['idappscheme' => $ins['idappscheme']], ['$set' => $ins], ['upsert' => true]);

			$this->init_scheme_droits($ins['idappscheme']);

			return new App($table);
		}
	}

<?php

	/**
	 * Class AppDataSchemeModel
	 * build and define model for scheme
	 * receive rights , in form oh [] or \IdaeDroitsFields, passed by set_appDroitsFields
	 */

	namespace Idae\Data\Scheme\Field;

	use Idae\Connect\IdaeConnect;
	use Idae\Data\Scheme\IdaeDataScheme;
	use Idae\Data\Scheme\Parts\IdaeDataSchemeParts;
	use Idae\Data\Scheme\Views\IdaeDataSchemeViews;
	use function array_column;
	use function array_filter;
	use function array_intersect_key;
	use function array_keys;
	use function array_map;
	use function array_merge;
	use function array_search;
	use function array_values;
	use function in_array;
	use function iterator_to_array;
	use function strtolower;
	use function ucfirst;
	use function usort;
	use function var_dump;
	use const ARRAY_FILTER_USE_BOTH;
	use const ARRAY_FILTER_USE_KEY;

	class IdaeDataSchemeField extends IdaeDataScheme {

		/**
		 * IdaeDataSchemeModel constructor.
		 *
		 * @param string $table
		 * @param array  $param_droits_fields
		 *
		 * @throws \Exception
		 */
		public function __construct($table, $param_droits_fields = []) {

			try {
				if (empty($table)) {
					throw new \Exception('appscheme non defini', 'EMPTY_PARAMETER_SCHEME', true);
				}
			} catch (\Exception $e) {
				echo 'Exception reçue : ', $e->getMessage(), "\n";

				return false;
			};
			
			parent::__construct($table);

			var_dump($this->appscheme_model_name);

			return $this;
		}

		private function set_appscheme_instance() {
			$db_name                  = $this->scheme_data['codeAppscheme_base']; // => getShemeData
			$this->appscheme_instance = $this->AppConnectInstance->plug($db_name, $this->appscheme_code);
		}

		/*public function set_appDroitsFields($param_droits_fields) {
			$this->appDroitsFields = IdaeDroitsFields::getInstance()->droit_session_table_crud($param_droits_fields[0], $this->appscheme_name, $param_droits_fields[1]);
		}*/

		private function build_query_droits() {
			$vars_appDroitsFields = [];

			$arr_allowed   = $this->appDroitsFields['allowed'];
			$arr_forbidden = $this->appDroitsFields['forbidden'];

			unset($arr_allowed['grilleFK']);
			unset($arr_forbidden['grilleFK']);
			$allowed   = array_map(function ($value) {
				return "$value" . ucfirst($this->appscheme_code);
			}, array_filter(array_values($arr_allowed)));
			$forbidden = array_map(function ($value) {
				return "$value" . ucfirst($this->appscheme_code);
			}, array_filter(array_values($arr_forbidden)));
			if ($allowed) $vars_appDroitsFields['$in'] = $allowed;
			if ($forbidden) $vars_appDroitsFields['$nin'] = $forbidden;

			return $vars_appDroitsFields;
		}

		/**
		 * @param array $vars
		 *
		 * @return array
		 * @todo : too fat
		 *
		 */
		public function getGrilleFK($vars = []) {
			$out       = [];
			$grille_fk = $this->scheme_data['grilleFK'];

			if (!$grille_fk) return [];
			usort($grille_fk, function ($a, $b) {
				return $a['ordreTable'] > $b['ordreTable'];
			});

			foreach ($grille_fk as $arr_fk):
				$table_fk = $arr_fk['table'];
				$det_fk   = $this->appscheme_model_instance->findOne($vars + ['codeAppscheme' => $table_fk]);

				if (empty($det_fk)) continue;

				$vars_fields = ['codeAppscheme_has_field' => ['$in' => ['nom' . ucfirst($table_fk)]]];
				$arr_rs      = $this->sitebase_app_instance->appscheme_has_field->findOne(array_merge($vars_fields, ['codeAppscheme' => $table_fk]));

				// V 0.3
				$arr_rs['field_code']     = $arr_rs['codeAppscheme_has_field'];
				$arr_rs['field_name']     = $arr_rs['nomAppscheme_has_field'];
				$arr_rs['field_code_raw'] = $arr_rs['codeAppscheme_field'];
				$arr_rs['field_name_raw'] = $arr_rs['nomAppscheme_field'];
				$arr_rs['field_icon']     = $arr_rs['iconAppscheme_field'];
				$arr_rs['field_order']    = $arr_rs['ordreAppscheme_has_field'];

				unset($arr_rs['updated_fields'], $arr_rs['collection']);
				$out[$table_fk] = $arr_rs;

			endforeach;

			return $out;
		}



		private function set_grille_fk_grouped() {
			$this->grille_fk_grouped = $this->getGrilleFK(['grouped_scheme' => 1]);
		}

		private function set_grille_fk_nongrouped() {
			$this->grille_fk_nongrouped = $this->getGrilleFK(['grouped_scheme' => ['$ne' => 1]]);
		}

		private function setGrilleRFK() {
			$table         = $this->appscheme_code;
			$rs_grille_rfk = $this->appscheme_model_instance->find(['grilleFK.table' => $table])->sort(['ordreAppscheme' => 1]);
			$out           = [];

			while ($arr_grille_rfk = $rs_grille_rfk->getNext()) {
				$codeAppscheme       = $arr_grille_rfk['codeAppscheme'];
				$out[$codeAppscheme] = $arr_grille_rfk;
			}
			$this->grille_rfk = $out;
		}

		private function set_grille_count() {
			$this->grille_count = empty($this->scheme_data['grilleCount']) ? [] : $this->scheme_data['grilleCount'];
		}

		/**
		 * @param $schemeFieldsType
		 *
		 * @return array
		 * @throws \Exception
		 * @throws \MongoCursorException
		 */
		private function make_view_model($schemeFieldsType) {
			/**
			 * DROITS  filtrer sur $this->appDroitsFields
			 * on enleve les droits grille FK, pour le moment
			 */
			$vars_appDroitsFields = [];

			if (!empty($this->appDroitsFields)) {
				$vars_appDroitsFields = $this->build_query_droits();
			}

			switch (strtolower($schemeFieldsType)) {
				case 'mini':
					$vars_droits = (!$vars_appDroitsFields) ? [] : ['codeAppscheme_has_field' => $vars_appDroitsFields];
					$rs          = $this->sitebase_app_instance->selectCollection('appscheme_has_field')->find($vars_droits + ['idappscheme'   => $this->table_id,
					                                                                                                           'in_mini_fiche' => 1])->sort(['ordreAppscheme_has_field' => 1,
					                                                                                                                                         'ordreAppscheme_field'     => 1]);

					break;
				case 'table':
					$vars_droits = (!$vars_appDroitsFields) ? [] : ['codeAppscheme_has_table_field' => $vars_appDroitsFields];
					$rs          = $this->sitebase_app_instance->appscheme_has_table_field->find($vars_droits + ['idappscheme' => $this->table_id])->sort(['ordreAppscheme_has_table_field' => 1,
					                                                                                                                                       'ordreAppscheme_has_field'       => 1,
					                                                                                                                                       'ordreAppscheme_field'           => 1]);
					break;
				case 'native':
					$vars_droits = (!$vars_appDroitsFields) ? [] : ['codeAppscheme_has_field' => $vars_appDroitsFields];
					$rs          = $this->sitebase_app_instance->selectCollection('appscheme_has_field')->find($vars_droits + ['idappscheme' => $this->table_id])->sort(['ordreAppscheme_has_field' => 1,
					                                                                                                                                                     'ordreAppscheme_field'     => 1]);
					break;
				case 'short':
					$vars_droits = (!$vars_appDroitsFields) ? [] : ['codeAppscheme_has_field' => $vars_appDroitsFields];
					$vars        = $vars_droits + ['codeAppscheme_has_field' => ['$in' => ['nom' . ucfirst($this->appscheme_code),
							'code' . ucfirst($this->appscheme_code)]]];
					$rs          = $this->sitebase_app_instance->selectCollection('appscheme_has_field')->find(array_merge($vars, ['idappscheme' => $this->table_id]))->sort(['ordreAppscheme_has_field' => 1,
					                                                                                                                                                          'ordreAppscheme_field'     => 1]);
					break;
			}

			if (empty($rs)) return [];

			$arr_rs     = iterator_to_array($rs);
			$arr_export = [];
			foreach ($arr_rs as $key_sh => $arr) {
				$arr_field = $this->sitebase_app_instance->appscheme_field->findOne(['idappscheme_field' => (int)$arr['idappscheme_field']]);

				$arr_field_group                               = $this->sitebase_app_instance->appscheme_field_group->findOne(['idappscheme_field_group' => (int)$arr_field['idappscheme_field_group']]);
				$arr_field_type                                = $this->sitebase_app_instance->selectCollection('appscheme_field_type')->findOne(['idappscheme_field_type' => (int)$arr_field['idappscheme_field_type']]);
				$keyCode                                       = $arr['codeAppscheme_has_field'] ?: $arr['codeAppscheme_has_table_field'];
				$arr_rs[$key_sh]['codeAppscheme_field_group']  = $arr_field_group['codeAppscheme_field_group'];
				$arr_rs[$key_sh]['ordreAppscheme_field_group'] = $arr_field_group['ordreAppscheme_field_group'];
				$arr_rs[$key_sh]['codeAppscheme_field_type']   = $arr_field_type['codeAppscheme_field_type'];
				$arr_rs[$key_sh]['ordreAppscheme_field_type']  = $arr_field_type['ordreAppscheme_field_type'];

				$arr_export[$keyCode] = $arr_rs[$key_sh];
			}

			return $arr_export;
		}

		/**
		 * @param $field
		 *
		 * @return bool
		 */
		public function has_field($field) {
			$z = array_values(array_column($this->model_native, 'codeAppscheme_field'));

			return in_array($field, $z);
		}


	}


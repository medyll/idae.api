<?php

	/**
	 * Class AppDataSchemeModel
	 * build and define model for scheme
	 * receive rights , in form oh [] or \IdaeDroitsFields, passed by set_appDroitsFields
	 */

	namespace Idae\Data\Scheme\Model;

	use Idae\Connect\IdaeConnect;
	use Idae\Data\Scheme\IdaeDataScheme;
	use Idae\Data\Scheme\Parts\IdaeDataSchemeParts;
	use Idae\Data\Scheme\Views\IdaeDataSchemeViews;
	use function var_dump;

	class IdaeDataSchemeModel extends IdaeDataScheme {

		//private $connection;
		//private $sitebase_app_name;
		//private $sitebase_app_instance;

		public $scheme_data = [];
		public $scheme_model;
		public $table_id;
		/**
		 * @var string @deprecated
		 */
		public $table;

		private $appDroitsFields;
		private $AppConnectInstance;

		public $grille_fk;
		public $grille_fk_grouped;
		public $grille_fk_nongrouped;
		public $grille_rfk;
		public $grille_count;

		public $model_native;
		public $model_image;
		public $model_table;
		public $model_mini;
		public $model_short;
		/**
		 * @var \MongoCollection $appscheme_model_instance
		 */
		//public $appscheme_model_instance;
		//public $appscheme_model_name;
		/**
		 * @var \MongoCollection $appscheme_instance
		 */
		public $appscheme_instance;
		public $appscheme_code;
		public $appscheme_name;

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


			$this->table          = $table;
			$this->appscheme_name = $table;
			$this->appscheme_code = $table;

			//	$this->AppConnectInstance = IdaeConnect::getInstance();

			$this->appscheme_model_name     = 'appscheme';
			$this->appscheme_model_instance = $this->sitebase_app_instance->selectCollection($this->appscheme_model_name);


			// consolidate !!

			if ($param_droits_fields) {
				$this->set_appDroitsFields($param_droits_fields);
			}


			$this->set_scheme_data();
			$this->set_scheme_model();
			//$this->set_appscheme_instance();
			$this->table_id = $this->scheme_data ['idappscheme'];


			// $this->set_view_models();

			// die('died');
			/*$this->set_grille_fk();
			$this->set_grille_fk_grouped();*/
			$this->set_grille_fk_nongrouped();

			$this->set_grille_rfk();
			$this->set_grille_count();

			return $this;

		}

		private function set_appscheme_instance() {
			$db_name                  = $this->scheme_data['codeAppscheme_base'];
			$this->appscheme_instance = $this->AppConnectInstance->plug($db_name, $this->appscheme_code);
		}

		public function set_appDroitsFields($param_droits_fields) {
			$this->appDroitsFields = IdaeDroitsFields::getInstance()->droit_session_table_crud($param_droits_fields[0], $this->appscheme_name, $param_droits_fields[1]);
		}

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
		private function make_grille_fk($vars = []) {
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

		/**
		 * @param array $scheme_field_types
		 *
		 * @return mixed
		 */
		public function get_schemeFieldsAllByZone($scheme_field_types = []) {

			$out['zone_fk']['scheme_field_fk_all']        = $this->grille_fk;
			$out['zone_fk']['scheme_field_fk_grouped']    = $this->grille_fk_grouped;
			$out['zone_fk']['scheme_field_fk_nongrouped'] = $this->grille_fk_nongrouped;

			$out['zone_main']['scheme_field_mini']   = $this->model_mini;
			$out['zone_main']['scheme_field_native'] = $this->model_native;
			$out['zone_main']['scheme_field_table']  = $this->model_table;
			$out['zone_main']['scheme_field_short']  = $this->model_short;

			$out['zone_rfk']['scheme_field_rfk'] = $this->grille_rfk;

			if (!empty($scheme_field_types)) {
				$out = array_filter($out, function ($value, $key) use ($scheme_field_types) {
					$a = array_intersect_key(array_keys($value), $scheme_field_types);
					if (!empty($a)) return true;
				}, ARRAY_FILTER_USE_BOTH);
			}

			return $out;
		}

		/**
		 * @param string|array $scheme_field_types
		 *
		 * @return mixed
		 */
		public function get_schemeParts($scheme_field_types = []) {
			$scheme_field_types = (array)$scheme_field_types;

			$out[IdaeDataSchemeParts::SCHEME_MAIN]          = IdaeDataSchemeParts::setSchemePart(IdaeDataSchemeParts::SCHEME_MAIN, $this->model_native);
			$out[IdaeDataSchemeParts::SCHEME_FK_ALL]        = IdaeDataSchemeParts::setSchemePart(IdaeDataSchemeParts::SCHEME_FK_ALL, $this->grille_fk);
			$out[IdaeDataSchemeParts::SCHEME_FK_GROUPED]    = IdaeDataSchemeParts::setSchemePart(IdaeDataSchemeParts::SCHEME_FK_GROUPED, $this->grille_fk_grouped);
			$out[IdaeDataSchemeParts::SCHEME_FK_NONGROUPED] = IdaeDataSchemeParts::setSchemePart(IdaeDataSchemeParts::SCHEME_FK_NONGROUPED, $this->grille_fk_nongrouped);
			$out[IdaeDataSchemeParts::SCHEME_RFK]           = IdaeDataSchemeParts::setSchemePart(IdaeDataSchemeParts::SCHEME_RFK, $this->grille_rfk);
			$out[IdaeDataSchemeParts::SCHEME_COUNT]         = IdaeDataSchemeParts::setSchemePart(IdaeDataSchemeParts::SCHEME_COUNT, $this->grille_count);
			$out[IdaeDataSchemeParts::SCHEME_IMAGE]         = IdaeDataSchemeParts::setSchemePart(IdaeDataSchemeParts::SCHEME_IMAGE, $this->model_image);

			if (!empty($scheme_field_types)) {
				$out = array_filter($out, function ($key) use ($scheme_field_types) {
					if (in_array($key, $scheme_field_types)) return true;
				}, ARRAY_FILTER_USE_KEY);
			}

			return $out;
		}

		/**
		 * @param string|array $scheme_field_views
		 *
		 * @return array
		 */
		public function get_schemeViews($scheme_field_views) {
			$scheme_field_views                           = (array)$scheme_field_views;
			$out[IdaeDataSchemeViews::SCHEME_VIEW_MINI]   = IdaeDataSchemeViews::setSchemeView(IdaeDataSchemeViews::SCHEME_VIEW_MINI, $this->model_mini);
			$out[IdaeDataSchemeViews::SCHEME_VIEW_NATIVE] = IdaeDataSchemeViews::setSchemeView(IdaeDataSchemeViews::SCHEME_VIEW_NATIVE, $this->model_native);
			$out[IdaeDataSchemeViews::SCHEME_VIEW_TABLE]  = IdaeDataSchemeViews::setSchemeView(IdaeDataSchemeViews::SCHEME_VIEW_TABLE, $this->model_mini);
			$out[IdaeDataSchemeViews::SCHEME_VIEW_SHORT]  = IdaeDataSchemeViews::setSchemeView(IdaeDataSchemeViews::SCHEME_VIEW_SHORT, $this->model_short);

			if (!empty($scheme_field_views)) {
				$out = array_filter($out, function ($key) use ($scheme_field_views) {
					if (in_array($key, $scheme_field_views)) return true;
				}, ARRAY_FILTER_USE_KEY);
			}

			return $out;
		}

		/**
		 * @param array $scheme_field_types
		 *
		 * @return \IdaeDataSchemeParts[]
		 * @deprecated
		 */
		public function get_schemeFieldsAll($scheme_field_types = []) {


			$out['scheme_field_fk_all']        = $this->grille_fk;
			$out['scheme_field_fk_grouped']    = $this->grille_fk_grouped;
			$out['scheme_field_fk_nongrouped'] = $this->grille_fk_nongrouped;
			$out['scheme_field_rfk']           = $this->grille_rfk;
			$out['scheme_field_image']         = $this->model_image;

			$out['scheme_field_mini']   = $this->model_mini;
			$out['scheme_field_native'] = $this->model_native;
			$out['scheme_field_table']  = $this->model_table;
			$out['scheme_field_short']  = $this->model_short;

			if (!empty($scheme_field_types)) {
				$out = array_filter($out, function ($key) use ($scheme_field_types) {
					if (in_array($key, $scheme_field_types)) return true;
				}, ARRAY_FILTER_USE_KEY);
			}

			return $out;
		}

		private function set_scheme_data() {
			$this->scheme_data = $this->appscheme_model_instance->findOne(['codeAppscheme' => $this->appscheme_code]);
		}

		private function set_scheme_model() {
			$data = $this->scheme_data;

			$properties         = ['idappscheme'                => $data['idappscheme'],
			                       'nomAppscheme'               => $data['nomAppscheme'],
			                       'icon'                       => $data['icon'],
			                       'dateModificationAppscheme'  => $data['dateModificationAppscheme'],
			                       'heureModificationAppscheme' => $data['heureModificationAppscheme'],
			                       'timeModificationAppscheme'  => $data['timeModificationAppscheme'],
			                       'idappscheme_base'           => $data['idappscheme_base'],
			                       'nomAppscheme_base'          => $data['nomAppscheme_base'],
			                       'codeAppscheme_base'         => $data['codeAppscheme_base'],
			                       'm_mode'                     => $data['m_mode'],
			                       'codeAppscheme'              => $data['codeAppscheme'],
			                       'hasImageScheme'             => $data['hasImageScheme'],
			                       'nomAppscheme_type'          => $data['nomAppscheme_type'],
			                       'idappscheme_type'           => $data['idappscheme_type'],
			                       'codeAppscheme_type'         => $data['codeAppscheme_type'],
			                       'bgcolorAppscheme_type'      => $data['bgcolorAppscheme_type'],
			                       'colorAppscheme_type'        => $data['colorAppscheme_type'],
			                       'iconAppscheme_type'         => $data['iconAppscheme_type'],
			                       'iconAppscheme'              => $data['iconAppscheme'],
			                       'hasTypeScheme'              => $data['hasTypeScheme'],
			                       'hasLigneScheme'             => $data['hasLigneScheme'],
			                       'hasCategorieScheme'         => $data['hasCategorieScheme'],
			                       'hasGroupScheme'             => $data['hasGroupScheme'],
			                       'hasImagesquareScheme'       => $data['hasImagesquareScheme'],
			                       'hasImagesmallScheme'        => $data['hasImagesmallScheme'],
			                       'hasImagelargeScheme'        => $data['hasImagelargeScheme'],
			                       'hasImagewallpaperScheme'    => $data['hasImagewallpaperScheme'],
			                       'colorAppscheme'             => $data['colorAppscheme'],
			                       'hasImagetinyScheme'         => $data['hasImagetinyScheme'],
			                       'sortFieldId'                => $data['sortFieldId'],
			                       'sortFieldName'              => $data['sortFieldName'],
			                       'sortFieldOrder'             => $data['sortFieldOrder'],
			                       'hasImagelongScheme'         => $data['hasImagelongScheme'],
			                       'isTypeScheme'               => $data['isTypeScheme'],
			                       'isStatutScheme'             => $data['isStatutScheme'],
			                       'isLigneScheme'              => $data['isLigneScheme']];
			$this->scheme_model = (object)$properties;
		}

		private function set_grille_fk($vars = []) {

			$this->grille_fk = $this->make_grille_fk($vars);
		}

		private function set_grille_fk_grouped() {
			$this->grille_fk_grouped = $this->make_grille_fk(['grouped_scheme' => 1]);
		}

		private function set_grille_fk_nongrouped() {
			$this->grille_fk_nongrouped = $this->make_grille_fk(['grouped_scheme' => ['$ne' => 1]]);
		}

		private function set_grille_rfk() {
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
		 * todo apply droits
		 *
		 * @throws \Exception
		 */
		private function set_view_models() {

			$this->model_image = $this->make_model_image();

			$this->model_native = $this->make_view_model('Native');
			$this->model_table  = $this->make_view_model('Table');
			$this->model_mini   = $this->make_view_model('Mini');
			$this->model_short  = $this->make_view_model('Short');
		}

		private function make_model_image() {
			$out = [];
			global $IMG_SIZE_ARR;
			if ($this->scheme_data['hasImageScheme']) {
				foreach ($IMG_SIZE_ARR as $key => $value) {

					if (empty($this->scheme_data['hasImage' . $key . 'Scheme'])) {
						continue;
					}
					$out[$key] = ['table'                    => $this->appscheme_code,
					              'codeTailleImage'          => $key,
					              'codeAppscheme_field'      => $key,
					              'codeAppscheme_has_field'  => $key,
					              'urlImage'                 => $this->appscheme_code . "-" . $key,
					              'pathImage'                => $this->appscheme_code . "/" . $key,
					              'is_image'                 => true,
					              'typeOf'                   => 'image',
					              'codeAppscheme'            => $this->appscheme_code,
					              'nomAppscheme'             => $this->appscheme_name,
					              'codeAppscheme_field_type' => 'custom_image',
					              'iconAppscheme_field'      => $this->appscheme_code];
				}
			};

			/*$out['thumb'] = ['table'                    => $this->appscheme_code,
			              'codeTailleImage'          => 'thumb',
			              'codeAppscheme_field'      => 'thumb',
			              'codeAppscheme_has_field'      => 'thumb',
			              'urlImage'                 => $this->appscheme_code . "-" . 'thumb',
			              'pathImage'                => $this->appscheme_code . "/" . 'thumb',
			              'is_image'                 => true,
			              'codeAppscheme'            => $this->appscheme_code,
			              'nomAppscheme'             => $this->appscheme_name,
			              'codeAppscheme_field_type' => 'custom_image',
			              'iconAppscheme_field'      => $this->appscheme_code];*/

			return $out;
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

		public function has_field_fk($table) {
			$arr_test = array_search($table, array_column($this->grille_fk, 'table_fk'));

			return ($arr_test === false) ? false : true;
		}
	}


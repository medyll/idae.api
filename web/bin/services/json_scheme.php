<?php
	header('Content-Type: application/json');

	include_once($_SERVER['CONF_INC']);

	$_POST = array_merge($_GET, $_POST);

	class JsonScheme extends App {

		public $IDB;
		public $APP;
		public $APP_SCH;
		public $APP_FIELD;
		public $APP_HAS_FIELD;
		public $APP_HAS_TABLE_FIELD;

		public function __construct($table = null) {

			$this->APP                 = new App();
			$this->APP_SCH             = new App('appscheme');
			$this->APP_FIELD           = new App('appscheme_field');
			$this->APP_HAS_FIELD       = new App('appscheme_has_field');
			$this->APP_HAS_TABLE_FIELD = new App('appscheme_has_table_field');

			return parent::__construct($table);
		}

		/**
		 * return mainly : icon, color ...
		 *
		 * @param $idappscheme_field
		 *
		 * @return array
		 */
		private function GetField($idappscheme_field) {
			$ARR_FIELD = $this->APP_FIELD->findOne(['idappscheme_field' => (int)$idappscheme_field]);

			return ['idppscheme_field'          => $idappscheme_field,
			        'codeAppscheme_field'       => $ARR_FIELD['codeAppscheme_field'],
			        'nomAppscheme_field'        => $ARR_FIELD['nomAppscheme_field'],
			        'codeAppscheme_field_group' => $ARR_FIELD['codeAppscheme_field_group'],
			        'field_group'                => $ARR_FIELD['codeAppscheme_field_group'],
			        'iconAppscheme_field'       => $ARR_FIELD['iconAppscheme_field'],
			        'codeAppscheme_field_type'  => $ARR_FIELD['codeAppscheme_field_type'],
			        'field_type'                => $ARR_FIELD['codeAppscheme_field_type'],
			        'css'                       => $this->GetFieldCssRule($ARR_FIELD['codeAppscheme_field_type']),
			        'viewFieldType'             => 'SCHEME'];
		}

		private function GetFieldCssRule($codeAppscheme_field_type = '') {

			$css = '';

			switch ($codeAppscheme_field_type):
				case "date":
					$css = 'date_field';
					break;
				case "heure":
					$css = 'heure_field';
					break;
				case "color":
					$css = 'color_field';
					break;
				default:
					if (empty($codeAppscheme_field_type)) {
						$css = 'fk ';
					} else {
						$css = 'css_field_' . $codeAppscheme_field_type;
					}

					break;
			endswitch;

			return $css;
		}

		/**
		 * fieldModel
		 *
		 * @param $idappscheme
		 *
		 * @return array
		 */
		private function RsHasField($idappscheme) {

			$RS_HAS_FIELD = $this->APP_HAS_FIELD->find(['idappscheme' => (int)$idappscheme]);
			$fieldModel   = [];

			foreach ($RS_HAS_FIELD as $ARR_HAS_FIELD):

				$ARR_FIELD = $this->GetField((int)$ARR_HAS_FIELD['idappscheme_field']);

				$ARR_MORE['field_name']     = $ARR_HAS_FIELD['nomAppscheme_has_field'];
				$ARR_MORE['field_name_raw'] = $ARR_HAS_FIELD['nomAppscheme_field'];
				$ARR_MORE['field_code']     = $ARR_HAS_FIELD['codeAppscheme_has_field'];
				$ARR_MORE['field_code_raw'] = $ARR_HAS_FIELD['codeAppscheme_field'];
				$ARR_MORE['field_icon']     = $ARR_FIELD['iconAppscheme_field'];
				$ARR_MORE['field_type']     = $ARR_FIELD['field_type'];
				$ARR_MORE['field_group']     = $ARR_FIELD['codeAppscheme_field_group'];

				$fieldModel[$ARR_HAS_FIELD['codeAppscheme_has_field']] = $ARR_MORE;//array_merge($ARR_FIELD, $ARR_MORE, $ARR_HAS_FIELD);

				if ($ARR_FIELD['codeAppscheme_field_group'] == 'codification' || $ARR_FIELD['codeAppscheme_field_group'] == 'identification') {
					/*$fieldModel[$ARR_FIELD['codeAppscheme_field']] = $ARR_FIELD;*/
					$hasModel[$ARR_FIELD['codeAppscheme_field']] = $ARR_FIELD;
				};
			endforeach;

			return $fieldModel;
		}

		private function RsColumnModel($idappscheme) {

			$ARR_APP = $this->APP_SCH->findOne(['idappscheme' => (int)$idappscheme]);
			$table   = $ARR_APP['codeAppscheme'];

			$APP          = new App($table);
			$GRILLE_FK    = $APP->get_grille_fk();
			$GRILLE_COUNT = $APP->get_grille_count($table);

			$RS_HAS_FIELD = $this->APP_HAS_FIELD->find(['idappscheme' => (int)$idappscheme]);
			$columnModel  = [];

			foreach ($RS_HAS_FIELD as $ARR_HAS_FIELD):
				$ARR_FIELD = $this->GetField((int)$ARR_HAS_FIELD['idappscheme_field']);

				$ARR_MORE['field_code']     = $ARR_HAS_FIELD['codeAppscheme_has_field'];
				$ARR_MORE['field_name']     = $ARR_HAS_FIELD['nomAppscheme_has_field'];
				$ARR_MORE['field_name_raw'] = $ARR_FIELD['nomAppscheme_field'];
				$ARR_MORE['field_code_raw'] = $ARR_FIELD['codeAppscheme_field'];
								$ARR_MORE['field_icon']     = $ARR_FIELD['iconAppscheme_field'];
								$ARR_MORE['field_type']     = $ARR_FIELD['field_type'];
								$ARR_MORE['field_group']     = $ARR_FIELD['field_group'];

				$columnModel[$ARR_HAS_FIELD['codeAppscheme_has_field']] = $ARR_MORE;//array_merge($ARR_FIELD, $ARR_MORE);
			endforeach;

			foreach ($GRILLE_FK as $fk):
				$columnModel['grilleFk_' . $fk['table_fk']] = ['field_name_group' => '',
				                                               'viewFieldType'    => 'GRILLE_FK',
				                                               'field_name'       => 'nom' . ucfirst($fk['table_fk']),
				                                               'field_name_raw'   => $fk['table_fk'],
				                                               'title'            => $fk['table_fk'],
				                                               'className'        => 'fk',/* 'field_icon'       => $fk['icon_fk'],
				                                               'icon'             => $fk['icon_fk']*/];
			endforeach;

			foreach ($GRILLE_COUNT as $key_count => $fk):
				$columnModel['count_' . $key_count]   = ['viewFieldType' => 'GRILLE_COUNT', 'field_name_group' => '', 'field_name' => 'count_' . $key_count, 'field_name_raw' => 'count_' . $key_count, 'title' => 'nb ' . $key_count, 'className' => 'nb_field', 'icon' => ''];
				$default_model['count_' . $key_count] = ['viewFieldType' => 'GRILLE_COUNT', 'field_name_group' => '', 'field_name' => 'count_' . $key_count, 'field_name_raw' => 'count_' . $key_count, 'title' => 'nb ' . $key_count, 'className' => 'nb_field', 'icon' => ''];
			endforeach;

			return $columnModel;
		}

		private function RsDefaultModel($idappscheme = null) {
			$default_model      = [];
			$RS_HAS_TABLE_FIELD = $this->APP_HAS_TABLE_FIELD->find(['idappscheme' => (int)$idappscheme])->sort(['ordreAppscheme_has_table_field' => 1]);

			foreach ($RS_HAS_TABLE_FIELD as $ARR_HAS_TABLE_FIELD): // tout les champs declarés dans has_table_field.
				$ARR_SCH_FIELD = $this->APP_SCH->findOne(['idappscheme' => (int)$ARR_HAS_TABLE_FIELD['idappscheme_link']]);
				$ARR_FIELD     = $this->APP_FIELD->findOne(['idappscheme_field' => (int)$ARR_HAS_TABLE_FIELD['idappscheme_field']]);
				$DA_TABLE_NANE = $ARR_SCH_FIELD['nomAppscheme'];
				$title         = ($ARR_HAS_TABLE_FIELD['idappscheme'] == $ARR_HAS_TABLE_FIELD['idappscheme_link']) ? $ARR_FIELD['nomAppscheme_field'] : $ARR_FIELD['nomAppscheme_field'] . ' ' . $DA_TABLE_NANE;

				$ARR_MORE['field_name']     = $ARR_HAS_TABLE_FIELD['codeAppscheme_has_field'];
				$ARR_MORE['field_name_raw'] = $ARR_HAS_TABLE_FIELD['codeAppscheme_field'];
				$ARR_MORE['field_code']     = $ARR_FIELD['codeAppscheme_has_field'];
				$ARR_MORE['field_code_raw'] = $ARR_FIELD['codeAppscheme_field'];
				/*				$ARR_MORE['field_icon']     = $ARR_HAS_TABLE_FIELD['iconAppscheme_field'];
								$ARR_MORE['field_color']    = $ARR_HAS_TABLE_FIELD['colorAppscheme_field'];
								$ARR_MORE['field_type']     = $ARR_FIELD['field_type'];*/
				$ARR_MORE['field_group']     = $ARR_FIELD['field_group'];
				$ARR_MORE['field_type']     = $ARR_FIELD['field_type'];
				$default_model[$ARR_HAS_TABLE_FIELD['codeAppscheme_has_table_field']] = array_merge($ARR_MORE, $this->GetField($ARR_FIELD['codeAppscheme_field']));

			endforeach;

			return $default_model;
		}

		private function RsDynamicView($idappscheme = null, $type = 'in_mini_fiche') {
			$miniModel = [];
			if ($type) {
				$RS_HAS_MINI_FIELD = $this->APP_HAS_FIELD->find(['idappscheme' => (int)$idappscheme, $type => 1])->sort(['ordreAppscheme_has_table_field' => 1]);
			} else {
				$RS_HAS_MINI_FIELD = $this->APP_HAS_FIELD->find(['idappscheme' => (int)$idappscheme])->sort(['ordreAppscheme_has_table_field' => 1]);
			}

			foreach ($RS_HAS_MINI_FIELD as $ARR_HAS_MINI_FIELD):
				$ARR_FIELD                                                 = $this->GetField((int)$ARR_HAS_MINI_FIELD['idappscheme_field']);
				$ARR_MORE['field_code']                                    = $ARR_HAS_MINI_FIELD['codeAppscheme_has_field'];
				$ARR_MORE['field_name_raw']                                = $ARR_FIELD['nomAppscheme_field'];
				$ARR_MORE['field_code_raw']                                = $ARR_FIELD['codeAppscheme_field'];
				$ARR_MORE['field_icon']                                    = $ARR_FIELD['iconAppscheme_field'];
				$ARR_MORE['field_color']                                   = $ARR_FIELD['colorAppscheme_field'];
				$ARR_MORE['field_type']                                    = $ARR_FIELD['codeAppscheme_field_type'];
				$ARR_MORE['field_group']                                   = $ARR_FIELD['codeAppscheme_field_group'];
				$ARR_MORE['field_order']                                   = $ARR_HAS_MINI_FIELD['ordreAppscheme_has_field'];
				$miniModel[$ARR_HAS_MINI_FIELD['codeAppscheme_has_field']] = $ARR_MORE;//array_merge($ARR_MORE,$ARR_FIELD );
			endforeach;

			return $miniModel;
		}

		/**
		 * @param $table
		 *
		 * @return mixed
		 * @throws \Exception
		 */
		public function get_schemeParts($table) {

			$this->IDB = new IdaeDataSchemeModel($table);

			return $this->IDB->get_schemeParts();
		}

		/**
		 * @throws \Exception
		 * @throws \MongoCursorException
		 */
		public function legacyParse() {
			$INIT  = new IdaeDataSchemeInit();
			$PIECE = !isset($_POST['piece']) ? 'scheme' : $_POST['piece'];

			$vars    = empty($_GET['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);
			//	$RS_APP  = $this->APP_SCH->find($vars)->sort(['codeAppscheme' => 1]);
			$RS_APP  = $this->APP_SCH->find($vars+['grouped_scheme'=>['$ne'=>1]])->sort(['codeAppscheme' => 1]);
			$COLLECT = [];

			foreach ($RS_APP as $ARR_APP):
				$INIT->consolidate_app_scheme($ARR_APP['codeAppscheme']);

				$idappscheme = (int)$ARR_APP['idappscheme'];
				$table       = $ARR_APP['codeAppscheme'];
				$base        = $ARR_APP['codeAppscheme_base'];
				$Table       = ucfirst($ARR_APP['codeAppscheme']);

				$RS_HAS_FIELD = $this->APP_HAS_FIELD->find(['idappscheme' => (int)$idappscheme]);

				$APP          = new App($table);
				$APP_TABLE    = $APP->app_table_one;
				$ENTITY       = $APP_TABLE;
				$GRILLE_FK    = $APP->get_grille_fk();
				$GRILLE_RFK    = array_reduce(array_map(function($scheme){ return [$scheme=>$scheme];},$APP->get_table_rfk($table)),'array_merge',[]);
				$GRILLE_COUNT = $APP->get_grille_count($table);
				//$arrFields    = $APP->get_basic_fields_nude($table);

				$ENTITY['grilleFK'] = $GRILLE_FK;
				$ENTITY['grilleRFK'] = $GRILLE_RFK;

				$fieldModel       = $this->RsHasField($idappscheme);
				$miniModel        = $this->RsDynamicView($idappscheme, 'in_mini_fiche');
				$schemeModel      = $this->RsDynamicView($idappscheme);
				$default_model    = $this->RsDefaultModel($idappscheme);
				$columnModel      = $this->RsColumnModel($idappscheme);
				$schemePartsModel = $this->get_schemeParts($table);
				//
				$hasModel    = [];
				$updateModel = [];

				// start here for all : columnModel => pour table par defaut, sans description, sueleument code et identification + fk
				foreach ($RS_HAS_FIELD as $ARR_HAS_FIELD):
					$ARR_FIELD = $this->APP_FIELD->findOne(['idappscheme_field' => (int)$ARR_HAS_FIELD['idappscheme_field']]);
					if ($ARR_FIELD['codeAppscheme_field_group'] == 'codification' || $ARR_FIELD['codeAppscheme_field_group'] == 'identification') {

						$hasModel[$ARR_FIELD['codeAppscheme_field']] = $ARR_FIELD;
					};
				endforeach;

				foreach ($GRILLE_COUNT as $key_count => $fk):
					// $columnModel[]   = ['field_name_group' => '', 'field_name' => 'count_' . $key_count, 'field_name_raw' => 'count_' . $key_count, 'title' => 'nb ' . $key_count, 'className' => 'nb_field', 'icon' => ''];
					$default_model[] = ['field_name_group' => '', 'field_name' => 'count_' . $key_count, 'field_name_raw' => 'count_' . $key_count, 'title' => 'nb ' . $key_count, 'className' => 'nb_field', 'icon' => ''];
				endforeach;

				$APP_MODEL['columnModel']  = $columnModel;
				$APP_MODEL['defaultModel'] = $default_model; // utilisateur
				$APP_MODEL['hasModel']     = $hasModel; // sans fk
				$APP_MODEL['fieldModel']   = $fieldModel; // tout les champs
				$APP_MODEL['miniModel']    = $miniModel; // mini
				//
				$COLLECT[$base][$table]           = $APP_TABLE;
				$COLLECT[$base][$table]['entity'] = $ENTITY;
				$COLLECT[$base][$table]['views']  = $APP_MODEL;
				$COLLECT[$base][$table]['parts']  = $schemePartsModel;  // le futur
			endforeach;

			if ($PIECE == 'scheme'):
				echo trim(json_encode($COLLECT, JSON_FORCE_OBJECT));
				exit;
			endif;
			if ($PIECE == 'fields'):
				//echo trim(json_encode($arrFields));
				exit;
			endif;
		}

	}

	$JsonSchemeClass = new JsonScheme();
	$JsonSchemeClass->legacyParse();

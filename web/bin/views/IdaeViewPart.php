<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 28/06/2018
	 * Time: 23:30
	 */

	/**
	 * Class IdaeViewPart
	 *
	 * @property \IdaeDataSchemeFieldDrawerFabricOption $defaultFabricOptions
	 * @property string                                $drawMode  draw_cast_field|draw_html_field|draw_html_input|draw_json
	 */
	class IdaeViewPart {

		private $crudCode          = 'R';
		private $drawMode          = 'draw_html_field';
		private $scheme_field_view = 'native';

		private $defaultFabricOptions;

		public function __construct() {


			$this->set_defaultFabricOptions();

		}

		function getPart($part,$table) {

		}

		public function field_native($table, $table_value) {

			$this->defaultFabricOptions->set_option('data_mode', 'fiche');
			$this->defaultFabricOptions->set_option('scheme_field_view', 'native');
			$this->defaultFabricOptions->set_option('scheme_field_view_groupby', 'group');
			$this->defaultFabricOptions->set_option('apply_droit', [IdaeSession::getInstance()->type_session,
			                                                        $this->crudCode]);
			$IdaeDataDB = IdaeDB::getInstance($table);
			$ARR        = $IdaeDataDB->findOne(["id$table" => (int)$table_value]);
			$Fabric     = $this->getFabric($table, 'findOne', $ARR);

			$tplData = $Fabric->get_templateDataHTML();

			$parameters['scheme_field_native'] = $tplData['scheme_field_native'];
		}

		private function getFabric($table, $data_mode = 'findOne', $resultset) {

			$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $this->defaultFabricOptions);
			$Fabric->fetch_query($resultset, $data_mode);

			return $Fabric;
		}

		private function fields($type = 'native') {

		}

		private function set_defaultFabricOptions() {
			$this->defaultFabricOptions = new IdaeDataSchemeFieldDrawerFabricOption();
			$this->defaultFabricOptions->set_options(['field_draw_style'   => $this->drawMode,
			                                          'fields_scheme_part' => 'main']);
		}
	}

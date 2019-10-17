<?

	/**
	 * Class IdaeDataSchemeFieldDrawerFabric
	 */

	namespace Idae\Data\Scheme\Field\Drawer\Fabric ;


	use Idae\Data\Scheme\IdaeDataScheme;
	use Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use Idae\Data\Scheme\Parts\IdaeDataSchemeParts;
	use Idae\Data\Scheme\Field\Drawer\IdaeDataSchemeFieldDrawer;
	use function array_merge;

	class IdaeDataSchemeFieldDrawerFabric {

		private $export_mode = 'html';
		private $presets     = [];
		/**
		 * @var IdaeDataScheme
		 */
		private $IdaeDataScheme;
		private $iteratorType;

		/**
		 * @var \IdaeDataSchemeFieldDrawer[] $AppDataSchemeFieldDrawerPipe
		 */
		private $AppDataSchemeFieldDrawerPipe;
		private $fields_groupby_mode = null;
		private $scheme_field_drawer_tpl_data;

		private $options;
		/**
		 * @var $resultset [][]
		 */
		private $resultset = [];

		/**
		 * @param                                             $table
		 * @param array|IdaeDataSchemeFieldDrawerFabricOption $options
		 */
		public function __construct($table, $options = []) {

			if ($options && gettype($options) == 'IdaeDataSchemeFieldDrawerFabricOption') {
				$this->options = $options;
			} else {
				$this->options = new IdaeDataSchemeFieldDrawerFabricOption ();
				if ($options) $this->set_options($options);
			}

			$this->set_preset();

			$this->IdaeDataScheme      = new IdaeDataScheme($table, $this->options->apply_droit);
			$this->IdaeDataSchemeModel = new IdaeDataSchemeModel($table, $this->options->apply_droit);//$this->IdaeDataScheme->IdaeDataSchemeModel; //
			$this->FabricHTML          = new AppDataSchemeFieldDrawerFabricHTML();

		}

		/**
		 * todo array_merge
		 *
		 * @param $options
		 */
		public function set_options($options) {
			if ($this->options->apply_droit) {
				$this->options->apply_droit = $options['apply_droit'];
			}
			foreach ($options as $index_option => $option) {
				if (isset($this->options->$index_option)) {
					if (gettype($options) == 'object') {
						$this->options->$index_option = $options->$index_option;
					} else {
						$this->options->$index_option = $options[$index_option];
					}
				} else {
					//echo "not set $index_option <br>";
				}
			}
		}

		public function set_option($option, $value) {
			$this->options->$option = $value;
		}

		public function fetch_query($resultset, $data_mode) {
			$this->options->data_mode = $data_mode;
			$this->resultset          = $resultset;
			$this->init_app_fields();
		}

		/**
		 * @deprecated
		 *
		 * @param array $query_vars
		 *
		 * @throws MongoCursorException
		 */
		public function fetch_data($query_vars = []) {

			$this->init_app_fields();

			switch ($this->options->data_mode) {
				case 'fiche':
				case 'findOne':
					$this->iteratorType = 'findOne';
					$this->resultset    = $this->IdaeDataScheme->findOne($query_vars);// think projection to moderate dataloading
					break;
				case 'liste':
				case 'find':
					$this->iteratorType = 'find';
					$this->resultset    = $this->IdaeDataScheme->find($query_vars);// think projection to moderate dataloading
					break;
				case 'group':
					$this->iteratorType = 'group';
					$this->resultset    = $this->IdaeDataScheme->group('$groupBy', $query_vars);// think projection to moderate dataloading
					break;
				case 'distinct':
					$this->iteratorType = 'distinct';
					$this->resultset    = $this->IdaeDataScheme->distinct($query_vars);// think projection to moderate dataloading
					break;
			}
		}

		public function set_preset() {
			$tpl                  = [];
			$tpl['main']          = [$this->options->scheme_field_view];
			$tpl['fk']            = [IdaeDataSchemeParts::SCHEME_FK_ALL];
			$tpl['fk_grouped']    = [IdaeDataSchemeParts::SCHEME_FK_GROUPED];
			$tpl['fk_nongrouped'] = [IdaeDataSchemeParts::SCHEME_FK_NONGROUPED]; // grouped, nongrouped, all
			$tpl['all']           = [IdaeDataSchemeParts::SCHEME_FK_GROUPED,
			                         IdaeDataSchemeParts::SCHEME_FK_NONGROUPED,
			                         IdaeDataSchemeParts::SCHEME_FK_ALL,
			                         IdaeDataSchemeParts::SCHEME_RFK,
			                         IdaeDataSchemeParts::SCHEME_IMAGE,
			                         $this->options->scheme_field_view];

			$this->presets = $tpl;
		}

		public function init_app_fields() {

			$scheme_field = 'scheme_field_' . strtolower($this->options->scheme_field_view);

			switch ($this->options->fields_scheme_part) {
				case 'main':
					$tpl = [$scheme_field];
					break;
				case 'fk':
					$tpl = ['scheme_field_fk_all']; // grouped, nongrouped, all
					break;
				case 'fk_grouped':
					$tpl = ['scheme_field_fk_grouped']; // grouped, nongrouped, all
					break;
				case 'fk_nongrouped':
					$tpl = ['scheme_field_fk_nongrouped']; // grouped, nongrouped, all
					break;
				case 'model':
					$tpl = [];
					break;
				case 'all':
					$tpl = ['scheme_field_fk_grouped',
					        'scheme_field_fk_nongrouped',
					        'scheme_field_fk_all',
					        'scheme_field_rfk',
					        'scheme_field_image',
					        $scheme_field];
					break;
				default:
					$tpl = [$this->options->fields_groupby_mode,
					        $scheme_field];
			}

			if (ENVIRONEMENT == 'PREPROD_LAN') {
				$this->fieldDrawPipe();
			} else {
				$this->set_fieldDrawPipe($tpl);
			}
		}

		/**
		 * ,need options->scheme_part
		 * ,need options->scheme_view
		 *
		 * @param array $scheme_field_types new base
		 */

		public function fieldDrawPipe() {

			/*if (!empty($this->options->preset)) {
				$out = $this->presets[$this->options->preset];
			} else if (!empty($this->options->scheme_part)) {
				$out = $this->presets[$this->options->scheme_part];
			}
			{
				$out_1 = $this->IdaeDataSchemeModel->get_schemeViews($this->options->scheme_view);
				$out_2 = $this->IdaeDataSchemeModel->get_schemeParts($this->options->scheme_part);
				$out   = array_merge($out_1, $out_2);
			}*/

			$out_1 = $this->IdaeDataSchemeModel->get_schemeViews($this->options->scheme_view);
			$out_2 = $this->IdaeDataSchemeModel->get_schemeParts($this->options->scheme_part);
			$out   = array_merge($out_1, $out_2);

			foreach ($out as $index => $tpl) {
				$out[$index] = new IdaeDataSchemeFieldDrawer($tpl, $this->IdaeDataScheme, $index);
			}

			$this->AppDataSchemeFieldDrawerPipe = $out;
		}

		/**
		 * todo moved from model( keep  drawer typing, delete method )
		 *
		 * @param array $scheme_field_types
		 *
		 * @return mixed
		 */
		public function set_fieldDrawPipe($scheme_field_types = []) {

			$out = $this->IdaeDataSchemeModel->get_schemeFieldsAll($scheme_field_types);

			if (!empty($scheme_field_types)) {
				$out = array_filter($out, function ($key) use ($scheme_field_types) {
					if (in_array($key, $scheme_field_types)) return true;
				}, ARRAY_FILTER_USE_KEY);
			}

			foreach ($out as $index => $tpl) {
				$out[$index] = new IdaeDataSchemeFieldDrawer($tpl, $this->IdaeDataScheme, $index);
			}

			$this->AppDataSchemeFieldDrawerPipe = $out;
		}

		public function get_templateDataFields() {
			// set export mode type
			$this->export_mode = 'raw_fields';

			return $this->get_templateData();
		}

		public function get_templateDataRaw() {
			// set export mode type
			$this->export_mode = 'raw';

			return $this->get_templateData();
		}

		public function get_templateDataHTML() {
			// set export mode type
			$this->export_mode = 'html';

			return $this->get_templateData();
		}

		public function get_templateData() {
			// set export mode type
			/**
			 * @var  \IdaeDataSchemeFieldElement[] $test
			 */
			$test = [];

			switch ($this->options->data_mode) {
				case 'fiche':
				case 'findOne':
					$test = $this->parse_drawerPipe($this->resultset);
					break;
				case 'liste':
				case 'find':
					foreach ($this->resultset as $index => $row) {
						$test_out         = [];
						$mixed            = $this->parse_drawerPipe($row);
						$test_out[$index] = $mixed;
						$test[$index]     = $test_out;
					}
					break;
				case 'group':
					$i = 0;
					foreach ($this->resultset as $index => $groups) {
						$test_out = [];

						$test_out_group = [];
						foreach ($groups['group'] as $index_group => $row) {
							$mixed                        = $this->parse_drawerPipe($row);
							$test_out_group[$index_group] = $mixed;

						}
						$test[$index] = ['group_index' => $groups['_id'],
						                 'group_data'  => $test_out_group];
					}
					break;
			}

			return $test;

		}

		private function parse_drawerPipe($row) {
			$test_out = [];

			foreach ($this->AppDataSchemeFieldDrawerPipe as $scheme_part_name => $AppDataSchemeFieldDrawer) {

				if ($scheme_part_name === IdaeDataSchemeParts::SCHEME_IMAGE) {

				}

				$fields = $this->AppDataSchemeFieldDrawerPipe[$scheme_part_name];

				$out_to = $AppDataSchemeFieldDrawer->mix_resultset_template_fields($fields, $row, $this->options->field_draw_style);

				if (!empty($this->options->show_only_fields)) {
					$out_to = array_filter($out_to, function ($value, $index) {
						return in_array($value['field_element']->field_model_code, $this->options->show_only_fields);
					}, ARRAY_FILTER_USE_BOTH);
				}

				if ($this->options->scheme_field_view_groupby) {
					$out_to = $this->group_field_by($out_to);
				}

				if ($this->export_mode == 'html') {
					$test_out[$scheme_part_name] = $this->get_templateDataFiche($out_to);
				} else if ($this->export_mode == 'raw_fields') {
					$test_out[$scheme_part_name] = $this->get_template_fields($out_to);
				} else {
					$test_out[$scheme_part_name] = $out_to;
				}
			}

			return $test_out;
		}

		/**
		 * does the grouping for fields presentation order group|type|all
		 *
		 * @param $out_to
		 *
		 * @return array
		 */
		private function group_field_by($out_to) {

			$type = $this->options->scheme_field_view_groupby;

			$arr_field_info      = array_column(array_values(array_values($out_to)), 'field_info');
			$arr_field_info_test = array_column($arr_field_info, "ordreAppscheme_field_$type");

			if (empty($arr_field_info_test)) return $out_to;

			usort($arr_field_info, function ($a, $b) use ($type) {
				return $a["ordreAppscheme_field_$type"] > $b["ordreAppscheme_field_$type"];
			});

			$arr_line_groupBy = array_unique(array_column($arr_field_info, "codeAppscheme_field_$type"));

			foreach ($arr_line_groupBy as $index => $key_group) {
				$new_array_out[$key_group] = [];
			}

			foreach ($out_to as $key_out_to => $line_out_to) {
				$key_group                            = $line_out_to['field_element']->field_model['codeAppscheme_field_' . $this->options->scheme_field_view_groupby];
				$key_code                             = $line_out_to['field_element']->field_code;
				$new_array_out[$key_group][$key_code] = $line_out_to;
			}

			return $new_array_out;
		}

		/**
		 * @param null $tpl_data
		 *
		 * @return string
		 */
		private function get_templateDataFiche($tpl_data = null) {
			$tpl_data = $tpl_data ?: $this->scheme_field_drawer_tpl_data;

			return AppDataSchemeFieldDrawerFabricHTML::enclose_template($this->get_template_fiche($tpl_data));
		}

		public function get_templateDataListe($tpl_data = null) {
			$tpl_data = $tpl_data ?: $this->scheme_field_drawer_tpl_data;
			$dsp      = '';
			foreach ($tpl_data as $key_index => $tpl_data_row) {
				$dsp .= $this->get_template_fiche($tpl_data_row);
			}

			return AppDataSchemeFieldDrawerFabricHTML::enclose_template($dsp);
		}

		/**
		 * @param IdaeDataSchemeFieldElement[] $tpl_data_row
		 *
		 * @return string
		 */
		private function get_template_fiche($tpl_data_row) {

			$arr_out = AppDataSchemeFieldDrawerFabricHTML::build_row($tpl_data_row, $this->options);

			$dsp = $this->loop_html($arr_out);

			return $dsp;
		}

		/**
		 * @param IdaeDataSchemeFieldElement[] $tpl_data_row
		 *
		 * @return array
		 */
		private function get_template_fields($tpl_data_row) {

			$arr_out = AppDataSchemeFieldDrawerFabricHTML::build_row($tpl_data_row, $this->options);
			$out     = $tpl_data_row;
			foreach ($tpl_data_row as $index => $tpl_tow) {
				$out[$index]['field_html'] = array_column($arr_out, 'codeProduit_type')[0];
			}

			return [$out];
		}

		private function loop_html($arr_out = []) {
			$html = '';
			if (empty($arr_out)) {
				return '';
			}
			foreach ($arr_out as $row_index => $row) {
				$html_line = '';
				foreach ($row as $line_index => $line) {
					$html_line .= AppDataSchemeFieldDrawerFabricHTML::enclose_template_field_cell($line);
				}
				if ($this->options->fields_groupby_mode) {
					$html .= AppDataSchemeFieldDrawerFabricHTML::enclose_template_field_row($html_line);
				}
			}
			if (!$this->options->fields_groupby_mode) {
				$html .= AppDataSchemeFieldDrawerFabricHTML::enclose_template_field_row($html);
			}

			return $html;
		}

	}

	class AppDataSchemeFieldDrawerFabricHTML {

		private $wrapper_table = <<<EOT
<div class='css_template_field_table' style='width:100%;' >content</div>
EOT;
		private $wrapper_row   = <<<EOT
<div class='css_template_field_row flex_h flex_wrap flex_align_middle' style='width:100%;' >content</div>
EOT;
		private $wrapper_cell  = "<div class='css_template_field_cell flex_h flex_align_middle' >content</div>";

		public function __construct() {

		}

		public static function enclose_template($html) {
			return "<div class='css_template_field_table' style='width:100%;' >$html</div>";

		}

		public static function enclose_template_field_row($html) {
			return "<div class='css_template_field_row flex_h flex_wrap flex_align_middle' style='width:100%;' >$html</div>";

		}

		static public function enclose_template_field_cell($html) {
			return "<div class='css_template_field_cell flex_h flex_align_middle' >$html</div>";

		}

		/**
		 * @param                                        $tpl_data_row
		 * @param \IdaeDataSchemeFieldDrawerFabricOption $options
		 *
		 * @return array
		 */
		public static function build_row($tpl_data_row, $options) {
			$arr_out = [];

			if (empty($tpl_data_row)) return;

			$testNonGroupedFields = array_column($tpl_data_row, 'field_element');
			foreach ($tpl_data_row as $key_line => $line_in) {
				if (empty($testNonGroupedFields)) {
					foreach ($line_in as $key_line_in => $line_in_item) {
						$key_group             = $line_in_item['field_element']->field_model['codeAppscheme_field_' . $options->scheme_field_view_groupby];
						$arr_out[$key_group][] = AppDataSchemeFieldDrawerFabricHTML::build_cell($line_in_item, $options);
					}
				} else {
					$field_element                                     = $line_in['field_element'];
					$field_model                                       = $field_element->field_model;
					$arr_out['unique_key'][$field_element->field_code] = AppDataSchemeFieldDrawerFabricHTML::build_cell($line_in, $options);
				}
			}

			return $arr_out;
		}

		/**
		 * todo value_to_input, to_raw
		 *
		 * @param \IdaeDataSchemeFieldElement             $arr_line_in
		 * @param  \IdaeDataSchemeFieldDrawerFabricOption $options
		 *
		 * @return string
		 */
		public static function build_cell($arr_line_in, $options) {
			switch ($options->field_draw_style) {
				case 'draw_html_field':
					$field_value = $arr_line_in['field_element']->value_to_html;
					break;
				case 'draw_html_input':
					$field_value = $arr_line_in['field_element']->value_to_input_field;
					break;
				case 'draw_json_field':
					$field_value = $arr_line_in['field_element']->value_to_json;
					break;
				case 'draw_cast_field':
					$field_value = $arr_line_in['field_element']->value_to_raw;
					break;
			}

			// detect fk, rfk : if  $arr_line_in['field_element']['field_model_scheme_code'] != $arr_line_in['field_element']['appscheme_name']
			//echo $drawer_type;
			$field_infos              = $arr_line_in['field_info'];
			$field_element            = $arr_line_in['field_element'];
			$field_model_scheme_code  = $field_element->field_model_scheme_code;
			$field_model_scheme_color = $field_element->field_model_scheme_color;
			$field_icone              = $field_element->field_model_icon ?: $field_element->field_model_scheme_icon;
			$field_name               = ucfirst($field_element->field_name);

			$out = '';
			switch ($field_element->typeOf) {
				case "internal":
					if ($options->show_field_name) $out .= "<div class='css_template_field_cell_name'>$field_name</div>";
					if ($options->show_field_icon) $out .= "<div class='css_template_field_cell_icone padding textgris borderb' ><i class='fa fa-$field_icone'></i></div>";
					if ($options->show_field_value) $out .= "<div class='css_template_field_cell_value padding ellipsis'>$field_value</div>";
					if ($options->show_field_edit) $out .= "<div class='css_template_field_cell_edit none' data-field_update ><i class='fa fa-ellipsis-h'></i></div>";

					break;
				case "external":
					$out .= "<div class='css_template_field_cell_external flex_h flex_align_middle'>";
					if ($options->show_field_icon) $out .= "<div class='css_template_field_cell_icone boxshadowr'><i class='fa fa-$field_icone' style='color:$field_model_scheme_color'></i></div>";
					$out .= "<div class='css_template_field_cell_main'>";
					if ($options->show_field_name) $out .= "<div class='css_template_field_cell_name'>$field_name</div>";
					if ($options->show_field_value) $out .= "<div class='css_template_field_cell_value'>$field_value</div>";
					$out .= "</div>";
					if ($options->show_field_edit) $out .= "<div class='css_template_field_cell_edit' data-field_update ><i class='fa fa-ellipsis-h'></i></div>";
					$out .= "</div>";
					break;
			}

			return $out;

		}
	}

<?php

	namespace Idae\Data\Scheme\Field\Drawer;

	use Idae\Data\Scheme\IdaeDataScheme;
	use Idae\Data\Scheme\Parts\IdaeDataSchemeParts;
	use Idae\Data\Scheme\Field\Element\IdaeDataSchemeFieldElement;



	class IdaeDataSchemeFieldDrawerTest {

		public function __construct(IdaeDataSchemeParts $IdaeDataSchemeParts, IdaeDataScheme $IdaeDataScheme, $schemeFieldGrouped = false) {

		}
	}

	/**
	 * User: Mydde
	 * Date: 07/06/2018
	 * works with DataSchemeModel and AppDataSchemeFieldElement
	 * build array of AppDataSchemeFieldElement
	 * with a draw_metod
	 */
	class IdaeDataSchemeFieldDrawer {

		/**
		 * @var \IdaeDataScheme $IdaeDataScheme
		 */
		private $IdaeDataScheme;
		/**
		 * hold query resultset
		 *
		 * @todo hold query row resultset
		 * @var array $arr_dataFields
		 */
		private $arr_dataFields;
		private $arr_dataFieldsByCode;

		private $appscheme_name;
		private $appscheme_code;

		private $dataSchemeFieldModel;
		private $looped_values;

		private $iteratorType;
		private $drawMethod = 'draw_cast_field';
		private $arrDroitFields;

		private $appscheme_instance;

		// $fields-> \IdaeDataSchemeParts
		public function __construct($IdaeDataSchemeParts, IdaeDataScheme $IdaeDataScheme, $schemeFieldGrouped = false) {


			$this->arr_dataFields = $IdaeDataSchemeParts;
			$this->IdaeDataScheme = $IdaeDataScheme;
			$this->appscheme_name = $IdaeDataScheme->appscheme_name;
			$this->appscheme_code = $IdaeDataScheme->table;

			$this->appscheme_model_instance = $this->IdaeDataScheme->appscheme_model_instance;
			$this->appscheme_instance       = $this->IdaeDataScheme->appscheme_instance;

			$this->set_arr_dataFieldsByCode();

		}

		private function set_arr_dataFieldsByCode() {
			$this->arr_dataFieldsByCode = [];

			if (gettype($this->arr_dataFields) == 'object') {
				switch (get_class($this->arr_dataFields)) {
					case 'IdaeDataSchemeViews':
						$fields = $this->arr_dataFields->scheme_view_content;

						break;
					case 'IdaeDataSchemeParts':
						$fields = $this->arr_dataFields->scheme_part_content;

						break;
				}
			} else {
				$fields = $this->arr_dataFields;
			}
if(!empty($fields)){

	foreach ((array)$fields as $key => $arr_fields) {
		$keyCode                              = $arr_fields['codeAppscheme_has_table_field'] ?: $arr_fields['codeAppscheme_has_field'];
		$this->arr_dataFieldsByCode[$keyCode] = $arr_fields;
	}
}
		}

		public function set_dataSchemeFieldModel($model) {
			$this->dataSchemeFieldModel = $model;
		}

		/**
		 * @param $drawMethod @values draw_cast_field|draw_html_field|draw_html_input|draw_json
		 */
		public function set_drawMethod($drawMethod) {
			$this->drawMethod = $drawMethod;
		}

		/**
		 * set iteratorType
		 *t odo not needed
		 *
		 * @param string $iteratorType findOne|find|distinct|group
		 */
		public function set_iteratorType($iteratorType) {
			$this->iteratorType = $iteratorType;
		}

		public function get_arr_dataFields() {
			return $this->arr_dataFields;
		}

		/**
		 * @param \IdaeDataSchemeFieldDrawer $field_drawer
		 * @param                            $resultset
		 * @param                            $field_draw_style
		 *
		 * @return array
		 */
		public function mix_resultset_template_fields($field_drawer, $resultset, $field_draw_style) {
			$tpl_data = [];
			$out      = $this->get_schemeFieldElements($field_drawer, $resultset, $field_draw_style);
			if (!$out) return $tpl_data;
			$tpl_data = $this->get_tplData($out);

			return $tpl_data;
		}

		/**
		 * @param array $scheme_field_types
		 *
		 * @return mixed
		 * @deprecated moved to drawerFabric
		 *             todo moved from model( keep  drawer typing, delete method )
		 *
		 */
		public function get_schemeFieldsAll($scheme_field_types = []) {
			$out = $this->IdaeDataScheme->AppDataSchemeModel->get_schemeFieldsAll($scheme_field_types);

			if (!empty($scheme_field_types)) {
				$out = array_filter($out, function ($key) use ($scheme_field_types) {
					if (in_array($key, $scheme_field_types)) return true;
				}, ARRAY_FILTER_USE_KEY);
			}

			foreach ($out as $index => $tpl) {
				$out[$index] = new IdaeDataSchemeFieldDrawer($tpl, new IdaeDataScheme($this->appscheme_code));
			}

			return $out;
		}

		/**
		 * @param \IdaeDataSchemeFieldDrawer $field_dwrawer
		 * @param array                      $row_data
		 * @param string                     $drawMethod draw_html_field|draw_html_input|draw_cast_field
		 *
		 * @return \IdaeDataSchemeFieldElement|\IdaeDataSchemeFieldElement[]
		 */
		public function get_schemeFieldElements($field_dwrawer, $row_data, $drawMethod = 'draw_html_field') {
			$arr_tmp = [];
			if (gettype($field_dwrawer->arr_dataFields) == 'object') {
				switch (get_class($field_dwrawer->arr_dataFields)) {
					case 'IdaeDataSchemeViews':
						$fields = $field_dwrawer->arr_dataFields->scheme_view_content;

						break;
					case 'IdaeDataSchemeParts':
						$fields = $field_dwrawer->arr_dataFields->scheme_part_content;

						break;
				}
			} else {
				$fields = $field_dwrawer->arr_dataFields;
			}

			if(!empty($fields)){


				foreach ($fields as $key => $arr_field) {
					$erzrez              = new IdaeDataSchemeFieldElement($arr_field, $row_data, $this->appscheme_name, $drawMethod);
					$codeField           = $erzrez->field_code ?: uniqid();
					$arr_tmp[$codeField] = $erzrez;
				}

			}
			return $arr_tmp;

		}

		/**
		 * returns template data as array
		 *
		 * @param array $arr_values
		 *
		 * @return array
		 */
		public function get_tplData($arr_values = []) {

			$arr_values = $arr_values ?: $this->looped_values;

			$out = [];
			foreach ($arr_values as $codeField => $valueField) {
				$out = array_merge($out, $this->get_tplDataLine($codeField, $this->arr_dataFieldsByCode[$codeField], $valueField));
			}

			return $out;

		}

		/**
		 * @param                                  $keyCode
		 * @param array                            $fieldInfo
		 * @param \IdaeDataSchemeFieldElement|null $field_element
		 *
		 * @return array
		 */
		private function get_tplDataLine($keyCode, $fieldInfo = [], IdaeDataSchemeFieldElement $field_element = null) {

			return [$keyCode => ['field_info'    => $fieldInfo,
			                     'field_element' => $field_element]];
		}

	}

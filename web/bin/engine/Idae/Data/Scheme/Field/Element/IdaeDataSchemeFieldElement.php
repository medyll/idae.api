<?php

	/**
	 * Class IdaeDataSchemeRowElement
	 */
	namespace Idae\Data\Scheme\Field\Element;

	use Idae\Data\Scheme\Field\Element\IdaeDataSchemeFieldElementCast;

	class IdaeDataSchemeRowElement {

	}

	/**
	 * Class IdaeDataSchemeFieldElement
	 */
	class IdaeDataSchemeFieldElement {

		public $field_model_scheme_code;
		public $field_model_scheme_name;

		public $field_model;
		public $field_model_code;
		public $field_model_type;
		public $field_model_group;
		public $field_code;
		public $field_required;
		public $field_name;
		public $field_value_id;

		public  $field_table        = null;
		public  $field_table_value  = null;
		public  $field_value        = null;
		public  $field_value_casted = null;
		private $field_row_data     = [];

		public $value_to_raw;
		public $value_to_html;
		public $value_to_input_field;
		public $value_to_json;

		private $drawMethod = 'draw_html_field';
		private $appscheme_name;

		public $typeOf = 'internal';

		/**
		 * todo feed data only for requested template ( input field, html, casted, json ....)
		 *
		 * @param array  $arr_field  infos about the field model
		 * @param array  $arrData    data from db for a given row
		 * @param string $appscheme_name
		 * @param string $drawMethod draw_html_field|draw_html_input|draw_cast_field
		 */
		public function __construct($arr_field, $arrData = [], $appscheme_name = '', $drawMethod = 'draw_html_field') {

			$this->appscheme_name          = $appscheme_name;
			$this->field_model             = $arr_field;
			$this->field_model_scheme_code = $arr_field['codeAppscheme'];
			$this->field_model_scheme_nom  = $arr_field['nomAppscheme'];
			$this->field_model_scheme_icon = $arr_field['iconAppscheme'];
			$this->field_model_scheme_color = $arr_field['colorAppscheme'];
			$this->field_model_code        = $arr_field['codeAppscheme_field'];
			$this->field_model_icon        = $arr_field['iconAppscheme_field'];
			$this->field_model_type        = $arr_field['codeAppscheme_field_type'];
			$this->field_model_group       = $arr_field['codeAppscheme_field_group'];
			$this->field_name              = $arr_field['nomAppscheme_field'];
			$this->field_code              = $arr_field['codeAppscheme_has_field'] ?: $arr_field['codeAppscheme_has_table_field'];
			$this->field_required          = $arr_field['required'] ?: null;

			// check internal / external ... native vs fks
			if ($this->field_model_scheme_code != $appscheme_name) {
				$this->typeOf             = 'external';
				$this->field_name       = $this->field_model_scheme_nom;
				$this->field_model_icon = $arr_field['iconAppscheme'];
			}

			$this->appscheme_value = $arrData['id' . $this->field_model_scheme_code];

			if (!empty($arrData)) $this->set_data($arrData);

			$this->feed_data();

		}

		public function reloadData($arrData = []) {
			if (!empty($arrData)) $this->set_data($arrData);
			$this->feed_data();
		}

		private function set_data($arrData) {
			$this->field_value        = $arrData[$this->field_code] ?: null;
			$this->field_value_casted = IdaeDataSchemeFieldElementCast::cast_field($this);
			$this->field_row_data     = $arrData;
		}

		private function feed_data() {
			switch ($this->drawMethod) {
				case 'draw_html_field':
					$this->value_to_html = $this->draw_html_field();
					break;
				case 'draw_html_input':
					$this->value_to_input_field = $this->draw_html_input();
					break;
				case 'draw_cast_field':
					$this->value_to_raw = $this->draw_raw();
					break;
				case 'draw_json_field':
					$this->value_to_raw = $this->draw_json_field();
					break;
			}
			$this->value_to_html        = $this->draw_html_field();
			$this->value_to_input_field = $this->draw_html_input();
			$this->value_to_raw         = $this->draw_raw();
			$this->value_to_json        = $this->draw_json_field();
		}

		private function draw_raw() {
			return IdaeDataSchemeFieldElementCast::cast_field($this);
		}

		private function draw_html_input() {
			return IdaeDataSchemeFieldElementCast::field_input_template($this);
		}

		private function draw_html_field() {
			return IdaeDataSchemeFieldElementCast::field_html_template($this);
		}

		private function draw_json_field() {
			return IdaeDataSchemeFieldElementCast::field_json_template($this);
		}
	}

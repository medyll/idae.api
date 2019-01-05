<?php


	/**
	 * Class IdaeDataSchemeFieldDrawerFabricOption
	 * @property string $scheme_part                          IdaeDataSchemeParts
	 * @property string $scheme_view                          IdaeDataSchemeViews
	 * @property        $fields_scheme_part                   full|all|fk
	 * @property string $preset
	 * @property string $scheme_field_view
	 * @property string $scheme_field_view_groupby            group|type
	 * @property string $data_mode                            group|type
	 * @property string $fields_groupby_mode                  group|type
	 * @property bool   $fields_show_empty                    group|type|all @deprecated , should be grouped, nongrouped ?
	 * @property        $field_draw_style                     draw_html_field
	 * @property bool   $hide_field_icon
	 * @property bool   $hide_field_name
	 * @property bool   $hide_field_value
	 * @property array  $apply_droit                          null
	 * @property array  $show_only_fields                     null
	 */
	class IdaeDataSchemeFieldDrawerFabricOption {

		public $scheme_part = IdaeDataSchemeParts::SCHEME_MAIN; // IdaeDataSchemeParts
		public $scheme_view = IdaeDataSchemeViews::SCHEME_VIEW_NATIVE; // IdaeDataSchemeViews

		public $fields_scheme_part = 'full';
		public $preset             = 'full';
		/**
		 * @var string $scheme_field_view mini|native|table|short|fk_all|fk_grouped|fk_nongrouped
		 */
		public $scheme_field_view         = 'mini';
		public $scheme_field_view_groupby = ''; // group|type
		public $data_mode                 = 'query_one';
		public $fields_groupby_mode       = 'group';
		public $fields_show_empty         = false;

		public $field_draw_style = 'draw_html_field';

		public $show_field_icon  = true;
		public $show_field_name  = true;
		public $show_field_value = true;
		public $show_field_edit  = false;

		public $hide_field_icon  = false;
		public $hide_field_name  = false;
		public $hide_field_value = false;

		public $apply_droit = null;

		public $show_only_fields = [];

		public function __construct() {

		}

		public function __set($name, $value) {
			$this->$name = $value;
		}

		public static function getopt() {
			$a = new IdaeDataSchemeFieldDrawerFabricOption();

			return $a;
		}

		public function set_option($option, $value) {
			$this->$option = $value;
		}

		public function set_options($options) {
			foreach ($options as $index_option => $option) {
				if (isset($this->$index_option)) {
					$this->$index_option = $options[$index_option];
				}
			}
		}

		public function get_options() {
			return get_object_vars($this);
		}
	}
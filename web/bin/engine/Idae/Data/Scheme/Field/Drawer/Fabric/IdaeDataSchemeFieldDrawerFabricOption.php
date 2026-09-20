<?php

	/**
	 * Class IdaeDataSchemeFieldDrawerFabricOption
	 *
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

	namespace Idae\Data\Scheme\Field\Drawer\Fabric;

	use  Idae\Data\Scheme\Parts\IdaeDataSchemeParts;
	use Idae\Data\Scheme\Views\IdaeDataSchemeViews;

	/**
	 * Settings for an IdaeDataSchemeFieldDrawerFabric: which fields it renders, how
	 * it groups them, and which parts of each field it shows. Every property is
	 * public and documented in the class block above.
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

		/**
		 * Every property has a default; set the ones you need afterwards.
		 */
		public function __construct() {

		}

		/**
		 * Sets any property, including one the class does not declare.
		 *
		 * @param string $name
		 * @param mixed  $value
		 * @return void
		 */
		public function __set($name, $value) {
			$this->$name = $value;
		}

		/**
		 * Returns a fresh option object with the defaults.
		 *
		 * @return self
		 */
		public static function getopt() {
			$a = new IdaeDataSchemeFieldDrawerFabricOption();

			return $a;
		}

		/**
		 * Sets one option. Like __set(), an unknown name is accepted.
		 *
		 * @param string $option
		 * @param mixed  $value
		 * @return void
		 */
		public function set_option($option, $value) {
			$this->$option = $value;
		}

		/**
		 * Sets several options at once.
		 *
		 * Unlike set_option(), the guard is isset(), so an option is only applied when
		 * the property already holds a non-null value. A typo is silently dropped, and
		 * so is any option whose current value is null, such as `apply_droit` - set
		 * those with set_option() instead.
		 *
		 * @param array $options
		 * @return void
		 */
		public function set_options($options) {
			foreach ($options as $index_option => $option) {
				if (isset($this->$index_option)) {
					$this->$index_option = $options[$index_option];
				}
			}
		}

		/**
		 * Every option as a plain array, including any set through __set().
		 *
		 * @return array
		 */
		public function get_options() {
			return get_object_vars($this);
		}
	}

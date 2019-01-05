<?php


	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 28/06/2018
	 * Time: 00:18
	 */
	class IdaeModules {

		public $modules = (object)['fiche' => 'coo'];

		public function __construct() {
			$this->default_modules();
		}

		public function default_modules() {
			$IdaeModule           = new IdaeModule();
			$fiche                = $IdaeModule->install_module('fiche', 'fiche', 'app_fiche');
			$this->modules->fiche = $fiche;
		}
	}

	class IdaeModule extends Idae {

		public $module_code;
		public $module_name;
		public $module_path;
		public $module_link;

		public function __construct() {

			parent::__construct();

			$init = new IdaeDataSchemeInit();
			$init->init_scheme('sitebase_app', 'appscheme_module', ['fields' => ['nom',
			                                                                     'code']]);
			$init->init_scheme('sitebase_app', 'appscheme_module_namespace', ['fields' => ['nom',
			                                                                               'code',
			                                                                               'url']]);

		}

		/**
		 * @param string $module_code
		 * @param string $module_name
		 * @param string $module_path
		 * @param string $module_link
		 *
		 * @return \IdaeModule $this
		 */
		public function install_module($module_code, $module_name, $module_path, $module_link) {
			$this->module_code = $module_code;
			$this->module_name = $module_name;
			$this->module_path = $module_path;
			$this->module_link = $module_link;

			return $this;
		}

		/**
		 * brings installation to life
		 *
		 * @param $module_code
		 *
		 * @return $this
		 */
		public function call_module($module_code) {

			return $module_code;
		}
	}
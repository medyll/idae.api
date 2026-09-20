<?php


	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 28/06/2018
	 * Time: 00:18
	 */
	/**
	 * The installed modules, as a registry. Only `fiche` is installed today.
	 */
	class IdaeModules {

		public $modules = (object)['fiche' => 'coo'];

		/**
		 * Installs the default modules.
		 */
		public function __construct() {
			$this->default_modules();
		}

		/**
		 * Installs the modules every instance gets.
		 *
		 * @return void
		 */
		public function default_modules() {
			$IdaeModule           = new IdaeModule();
			$fiche                = $IdaeModule->install_module('fiche', 'fiche', 'app_fiche');
			$this->modules->fiche = $fiche;
		}
	}

	/**
	 * One module: its code, its display name, and where its files and route live.
	 */
	class IdaeModule extends Idae {

		public $module_code;
		public $module_name;
		public $module_path;
		public $module_link;

		/**
		 * Initialises the module scheme before the module can be installed or read.
		 */
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
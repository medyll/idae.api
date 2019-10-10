<?php

	namespace Idae\Generator;

	use Idae\Data\IdaeData;
	use function file_exists;
	use function iterator_to_array;
	use function mkdir;
	use function str_replace;
	use function ucfirst;
	use Nayjest\StrCaseConverter\Str;
	use Functions\StrFunc;
	use function vardump;
	use const APPCLASSES_APP;
	use const BUSINESS;
	use const CUSTOMER;

	/**
	 * Class IdaeGeneratorAppClass
	 *
	 * $generator = new IdaeGeneratorAppClass();
	 * $generator->init();
	 * $generator->travel();
	 *
	 * @package Idae\Generator
	 */
	class IdaeGeneratorAppClass {

		private $idae_data;
		private $scheme_list;

		// private $code_appscheme;

		public function __construct() {

			$this->idae_data = new IdaeData();
		}

		public function init() {

			$this->scheme_list = $this->idae_data->getSchemeList();
		}

		public function travel() {

			$i = 0;
			foreach ($this->scheme_list as $key => $idaeDatu) {
				echo $idaeDatu['codeAppscheme_base'] . '.' . $idaeDatu['codeAppscheme'];
				echo "<br />----------------------------------------------------------------------------------------------------<br />";
				// $this->writeSchemeFields($idaeDatu['codeAppscheme']);
				$this->createDirectories($idaeDatu['codeAppscheme_base'], $idaeDatu['codeAppscheme']);
			}
		}

		public function createDirectories($codeAppscheme_base, $codeAppscheme) {
			$bundle = str_replace('sitebase', '', $codeAppscheme_base) . 'Bundle';
			echo $path = APPCLASSES_ORM . StrFunc::toCamelCase(CUSTOMER) . '/' . StrFunc::toCamelCase(BUSINESS) . '/' . StrFunc::toCamelCase($bundle) . '/' . StrFunc::toCamelCase($codeAppscheme) . '/';
			echo "<br>";
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			foreach (['Repository', 'Entity', 'Controller', 'Interface'] as $index => $item) {
				if (!file_exists($path . "/$item")) {
					echo "<br><br><br><br><br>";
					mkdir($path . "/$item", 0777, true);
				}

			}
		}

		private function writeSchemeFields(string $codeAppscheme) {

			$ListFields = $this->idae_data->getSchemeFieldList($codeAppscheme);

			foreach (iterator_to_array($ListFields) as $keyField => $chemeField) {
				//vardump($chemeField);

				$code = $chemeField['codeAppscheme_has_field'];

				$str = "public get" . ucfirst($code) . "(){<br>";
				$str .= '<br>                return $this->table_id<br>';
				$str .= '}<br>--------------------------------------<br>';
				$str .= "public set" . ucfirst($code) . "(){";
				$str .= '$this->table_id = $table_id;';

				$str .= 'return $this;';
				echo "<pre>";
				echo $str;
				echo "</pre>";
			}
		}
	}

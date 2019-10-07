<?php

	include_once($_SERVER['CONF_INC']);


	die('died');
	use  Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use  Idae\Data\Scheme\Field\IdaeDataSchemeField;
	use  Idae\Data\Scheme\IdaeDataScheme;
	use  Idae\Data\IdaeData;

	// $IdaeDataSchemeModel = new IdaeDataSchemeModel('agent');
	// $IdaeDataSchemeModel = new IdaeDataSchemeField('agent');
	$IdaeData = new IdaeData();

	$IdaeDataScheme = new IdaeDataScheme('produit');
	$ListIdaeData   = $IdaeData->getSchemeList(['codeAppscheme_base' => 'sitebase_app'], ['codeAppscheme' => 1]);

	$FK = $IdaeDataScheme->getSchemeFields();

	$i = 0;
	foreach ($ListIdaeData as $key => $idaeDatu) {
		$i++;
		echo '<pre>';

		if (!empty($idaeDatu['codeAppscheme_base'])) {
			echo $idaeDatu['codeAppscheme_base'] . '.' . $idaeDatu['codeAppscheme'];
			$ListFields = $IdaeData->getSchemeFieldList($idaeDatu['codeAppscheme']);

			foreach (iterator_to_array($ListFields) as $keyField => $Field) {
				if($i < 7)continue;
				var_dump($Field['nomAppscheme_has_field']);
				// if($i===7) die();
			}
		} else {
			//var_dump($idaeDatu);
			die('died');
		}
		// if($i===3) die();
		echo '</pre>';
	}
	$i = 0;
	foreach ($FK as $index => $chemeField) {
		$code = $chemeField['codeAppscheme_has_field'];
		$str  = "public get" . ucfirst($code) . "(){";
		$str  .= '<br>return $this->table_id<br>';
		$str  .= '}<br>--------------------------------------<br>';
		$str  .= "public set" . ucfirst($code) . "(){";
		$str  .= '$this->table_id = $table_id;';

		$str .= 'return $this;';

		echo $str;
		// vardump($chemeField['codeAppscheme_has_field']);
		//if ($i===7) die('died');
	}

	// print_r($FK);
	// vardump($SCHDATA);
	/*$parts = $IdaeDataSchemeModel->get_schemeParts('scheme_field_main');
	var_dump($parts);*/
	// var_dump($IdaeDataSchemeModel);

	/*$ClassApp = new App('agent');
	$ClassApp->make_classes_app();*/

	$Router = new Router();

	// $Router->setBasePath('/web/');

<?php

	include_once($_SERVER['CONF_INC']);

	use  Idae\Data\Scheme\Model\IdaeDataSchemeModel;
	use  Idae\Data\Scheme\Field\IdaeDataSchemeField;
	use  Idae\Data\Scheme\IdaeDataScheme;
	use Idae\Data\IdaeData;

	// $IdaeDataSchemeModel = new IdaeDataSchemeModel('agent');
	// $IdaeDataSchemeModel = new IdaeDataSchemeField('agent');
	$IdaeData = new IdaeData();

	$IdaeDataScheme = new IdaeDataScheme('produit');
	$ListIdaeData   = $IdaeData->getSchemeList();

	$FK = $IdaeDataScheme->getSchemeFields();
	// $SCHDATA = $IdaeDataScheme->getSchemeData();
	foreach ($ListIdaeData as $key => $idaeDatu) {
		echo '<pre>';
		//var_dump($idaeDatu);
		//var_dump($idaeDatu['sitebase_app_name']);

		if (!empty($idaeDatu['codeAppscheme_base'])) {
			echo $idaeDatu['codeAppscheme_base'] . '.' . $idaeDatu['codeAppscheme'];
			}else{
				var_dump($idaeDatu);
				die('died');
			}
			//die();
			//echo "...";
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

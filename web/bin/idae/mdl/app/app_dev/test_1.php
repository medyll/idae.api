<?
	include_once($_SERVER['CONF_INC']);

	$DB = new IdaeDataDB('shop');
	$rs = $DB->find(['actifShop' => 1]);

	$Bin = new Bin();
	while ($arr = $rs->getNext()) {
		ClassRangCommande::updateRangShopCommandes($arr['idshop']);

	}

	/*ini_set('mongo.long_as_object', true);
	ini_set('mongo.long_as_object', false);*/

	/*$init = new IdaeDataSchemeInit();
	$init->init_scheme('sitebase_app', 'appscheme_view_type', ['fields' => ['nom', 'code']]);*/

	$init = new IdaeDataSchemeInit();
	$init->init_scheme_field('image_large', 'image_large', 'image', 'image');
	$init->init_scheme_field('image_long', 'image_long', 'image', 'image');
	$init->init_scheme_field('image_small', 'image_small', 'image', 'image');
	$init->init_scheme_field('image_tiny', 'image_tiny', 'image', 'image');
	$init->init_scheme_field('image_square', 'image_square', 'image', 'image');

	$init = new IdaeDataSchemeInit();
	$init->init_scheme('sitebase_base', 'agent', ['fields' => ['nom', 'code', 'image_small']]);

	$options_sc                            = new IdaeDataSchemeFieldDrawerFabricOption();
	$options_sc->apply_droit               = [$type_session, 'R'];
	$options_sc->data_mode                 = 'fiche';
	$options_sc->scheme_field_view         = 'native';
	$options_sc->field_draw_style          = 'draw_html_field';
	$options_sc->scheme_field_view_groupby = 'group';
	$options_sc->fields_scheme_part        = 'all';
	$options_sc->preset                    = 'all';
	$options_sc->scheme_part               = [IdaeDataSchemeParts::SCHEME_MAIN, IdaeDataSchemeParts::SCHEME_FK_GROUPED, IdaeDataSchemeParts::SCHEME_FK_NONGROUPED,];
	$options_sc->scheme_view               = IdaeDataSchemeViews::SCHEME_VIEW_NATIVE;

	//var_dump($options);
	$table  = 'agent';
	$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options_sc);
	$DB     = new IdaeDataDB($table);
	$RS     = $DB->findOne(['idagent' => 1]);
	$Fabric->fetch_query($RS, 'findOne');
	$tplData = $Fabric->get_templateDataHTML();

	$colorindex = lineargradient('#98ff2e', 'e73827', 5  // number of colors in your linear gradient
	);

	$color = $colorindex[4];

	foreach ($colorindex as $index => $color) {
		?><i class="fa fa-circle" style="color:<?= $color ?>"></i> <?
	}

?>
<div style="height:100%;">
    <div class="flex_h">
		<? echo($tplData[IdaeDataSchemeViews::SCHEME_VIEW_NATIVE]); ?>
        <hr>
		<? echo($tplData[IdaeDataSchemeParts::SCHEME_MAIN]); ?>
    </div>
	<? if (ENVIRONEMENT == 'PREPROD_LAN') { ?>
        <div class="padding_more  ">
            <a data-module_link="app_dev/test_1" data-close>page test 1</a>
            <a data-module_link="app_dev/test_2" data-close>page test 2</a>
        </div>
	<? } ?>
</div>
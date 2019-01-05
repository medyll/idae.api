<?
    include_once($_SERVER['CONF_INC']);

    $table = $this->HTTP_VARS['table'];

    $table_value = (int)$this->HTTP_VARS['table_value'];
    $vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);

    $APP    = new App($table);
    $Idae   = new Idae($table);
    $APPOBJ = $APP->appobj($table_value, $vars);

    /*$test_custom = Idae::module_exists('app_custom/' . $table . '/' . $table . '_fiche_mini');
	if (!empty($test_custom)) {
		echo $Idae->module('app_custom/' . $table . '/' . $table . '_fiche_mini', $this->HTTP_VARS);

		return;
	}*/

    //
    $EXTRACTS_VARS = $APP->extract_vars($table_value, $vars);
    extract($EXTRACTS_VARS, EXTR_OVERWRITE);
    //
    $APP_TABLE = $APP->app_table_one;

    $iconAppscheme = $APP->iconAppscheme;

    $Idae = new Idae($table);

    $html_table_statut     = $Idae->fiche_next_statut($table_value, true);
    $html_table_statut_has = $Idae->module('fiche_has', "table=$table&table_value=$table_value&table_type=statut");

    $module_link  = in_array($table, ['livreur_affectation']) ? 'update' : 'fiche';
    $crypted_link = AppLink::$module_link($table, $table_value);

    $options = ['apply_droit'         => [$type_session,
                                          'R'],
                'data_mode'           => 'fiche',
                'scheme_field_view'   => 'mini',
                'field_draw_style'    => 'draw_html_field',
                'scheme_field_view_groupby' => null,
                /*'field_group_type'   => 'group',*/
                'fields_scheme_part'  => 'main',
                'hide_field_empty'    => 1,
                'hide_field_icon'     => 1,
                'hide_field_name'     => 1,
                'hide_field_value'    => 1,
                'field_composition'   => ['hide_field_icon'  => 1,
                                          'hide_field_name'  => 1,
                                          'hide_field_value' => 1]];

    $Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
    $Fabric->fetch_data(["id$table" => $table_value]);
    $tplData = $Fabric->get_templateData();

?>
<div class="animated fadeIn blanc">
    <div class="flex_h borderb boxshadowb ">
        <div class="flex_h flex_align_stretch ">
            <div class="padding hide-for-small-only">
                <? if (!empty($APP_TABLE['hasImageScheme']) && !empty($this->HTTP_VARS['table_value'])): ?>
                    <div class="relative">
                        <div class="padding  text-center    absolute "
                             style="width:100%;left:0;bottom:0;border-color:<?= $ICON_COLOR ?>">
                            <i class="fa fa-<?= $APPOBJ->ICON ?> text-bold" style="color:<?= $ICON_COLOR ?>"></i>
                        </div>
                        <img class="boxshadowb" style="width:70px;border-radius: 50px;"
                             src="<?= AppSite::imgApp($table, $table_value, 'square') ?>">
                    </div>
                <? else : ?>
                    <div class="relative padding">
                        <div class="padding text-center  " style="width:70px;">
                            <i class="fa fa-<?= $iconAppscheme ?> fa-2x" style=";color:<?= $APP->colorAppscheme ?>"></i>
                        </div>
                    </div>
                <? endif; ?>
            </div>
        </div>
        <div class="flex_main">
            <div class="padding_more">
                <?= $tplData['scheme_field_mini'] ?>
            </div>
            <div class="padding_more none">
                <?= $Idae->module('fiche_fields', ['table'         => $table,
                                                   'table_value'   => $table_value,
                                                   'in_mini_fiche' => 1]) ?>
            </div>
        </div>
        <a class="item_icon more cursor" data-crypted_link="<?= $crypted_link ?>">
            <i class="fa fa-ellipsis-h fa-2x"></i>
        </a>
    </div>
</div>
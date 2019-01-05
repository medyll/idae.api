<?
    include_once($_SERVER['CONF_INC']);
    // POST
    $table = $_POST['table'];
    $Table = ucfirst($table);
    $vars  = empty($_POST['vars']) ? [] : function_prod::cleanPostMongo($_POST['vars'], 1);
    //
    $type_session        = $_SESSION['type_session'];
    $name_idtype_session = "id$type_session";
    $idtype_session      = (int)$_SESSION[$name_idtype_session];
    // Helper::dump($_POST);
    $arr_allowed_c = droit_table($type_session, 'C', $table);
    $arr_allowed_r = droit_table($type_session, 'R', $table);
    $arr_allowed_u = droit_table($type_session, 'U', $table);
    $arr_allowed_d = droit_table($type_session, 'D', $table);
    $arr_allowed_l = droit_table($type_session, 'L', $table);
    //
    $APP  = new App($table);
    $Idae = new Idae($table);
    //
    $APP_TABLE = $APP->app_table_one;
    $GRILLE_FK = $APP->get_grille_fk($table);
    //
    $id  = 'id' . $table;
    $ARR = $APP->findOne([$id => $table_value]);

    $ARR_COLLECT = $Idae->get_table_fields();

    $test_custom = Idae::module_exists('app_custom/' . $table . '/' . $table . '_create');
    if (!empty($test_custom)) {
        echo $Idae->module('app_custom/' . $table . '/' . $table . '_create', $this->HTTP_VARS);

        return;
    }

    $crypted_liste_link = AppLink::liste($table);

    $options = ['apply_droit'               => [$type_session,
                                                'C'],
                'data_mode'                 => 'fiche',
                'scheme_field_view'         => 'native',
                'field_draw_style'          => 'draw_html_input',
                'scheme_field_view_groupby' => 'group',
                'fields_scheme_part'        => 'all',
                'field_composition'         => ['hide_field_icon'  => 1,
                                                'hide_field_name'  => 1,
                                                'hide_field_value' => 1]];

    $Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
    $Fabric->init_app_fields();
    $tplData = $Fabric->get_templateData();

?>
<div class="flex_v">
    <div>
        <?= $Idae->module('fiche_entete', ['table'       => $table,
                                           'table_value' => $table_value]) ?>
    </div>
    <div class="flex_main relative">
        <form class="flex_v" action="<?= ACTIONMDL ?>app/actions.php" onsubmit="ajaxFormValidation(this);return false;">
            <input type="hidden" name="F_action" value="app_create"/>
            <input type="hidden" name="reloadModule[app/app_select]" value="app_select_<?= $table ?>"/>
            <input type="hidden" name="table" value="<?= $table ?>"/>
            <input type="hidden" name="vars[m_mode]" value="1"/>
            <? foreach ($vars as $key => $input): ?>
                <input type="hidden" name="vars[<?= $key ?>]" value="<?= $input ?>">
            <? endforeach; ?>
            <div class="flex_main padding" style="overflow-y:auto;overflow-x:hidden;">
                <div class="grid-x">
                    <?
                        $arr_has = ['statut',
                                    'type',
                                    'categorie',
                                    'group'];
                        foreach ($arr_has as $key => $value):
                            $Value  = ucfirst($value);
                            $_table = $table . '_' . $value;
                            $_id    = 'id' . $table . '_' . $value;
                            $_nom   = 'nom' . ucfirst($value);
                            if (!empty($APP_TABLE['has' . $Value . 'Scheme'])):
                                $APP_TMP = new App($_table);
                                $select  = AppSocket::cf_module('app/app_select', ['table'        => $table . '_' . $value,
                                                                                   'module_value' => 1235,
                                                                                   'field'        => ['prospect',
                                                                                                      'client']], 1235);
                                ?>
                                <div class="cell small-12 medium-6 large-3 borderb">
                                    <div class="flex_h flex_align_middle">
                                        <div class="padding_more boxshadowr text-bold">
                                            <i class="fa fa-<?= $APP_TMP->iconAppscheme ?>"
                                               style="color:<?= $APP_TMP->colorAppscheme ?>"></i>

                                        </div>
                                        <div class="padding   text-bold">
                                            <?= $APP_TMP->nomAppscheme ?>
                                        </div>

                                    </div>
                                    <div class="padding  ">
                                        <?= $select ?>
                                    </div>
                                </div>                            <?
                            endif;
                        endforeach; ?>
                </div>
                <div class="grid-x ">
                    <div class="cell small-12 medium-12 large-12">
                        <?= $tplData['scheme_field_native'] ?>
                    </div>
                </div>
            </div>
            <div class="  flex_h   flex_align_middle boxshadow">
                <div class="text-center  padding_more cursor flex_main">
                    <div class="    padding_more inline">
                        <button class="blanc textvert" type="submit" value="<?= idioma('Valider') ?>"
                                style="border:none;padding:0;"><i class="fa fa-check-circle fa-2x"></i></button>
                    </div>
                </div>
                <? if ($arr_allowed_l) { ?>
                    <div class="text-center padding_more cursor flex_main"
                         data-crypted_link="<?= $crypted_liste_link ?>">
                        <div class="item_icon item_icon_shadow more inline">
                            <i class="fa fa-list fa-2x"></i>
                        </div>
                    </div>
                <? } ?>
                <div class="text-center  padding_more cursor flex_main" onclick="history.back();">
                    <div class="item_icon item_icon_shadow more inline" onclick="history.back();">
                        <i class="fa fa-ban fa-2x"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
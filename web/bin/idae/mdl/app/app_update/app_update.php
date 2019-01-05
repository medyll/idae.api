<?
    include_once($_SERVER['CONF_INC']);

    $table       = $this->HTTP_VARS['table'];
    $Table       = ucfirst($this->HTTP_VARS['table']);
    $table_value = (int)$this->HTTP_VARS['table_value'];
    $vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
    $type_session        = $_SESSION['type_session'];
    $name_idtype_session = "id$type_session";

    //
    $APP  = new App($table);
    $Idae = new Idae($table);

    $test_custom = Idae::module_exists('app_custom/' . $table . '/' . $table . '_update');
    if (!empty($test_custom)) {
        echo $Idae->module('app_custom/' . $table . '/' . $table . '_update', $this->HTTP_VARS);

        return;
    }

    $GRILLE_FK = $APP->get_grille_fk();
    //
    //
    $id  = 'id' . $table;
    $ARR = $APP->findOne([$id => $table_value]);

    $Idae = new Idae($table);

	$crypted_image_link = AppLink::liste_images($table, $table_value);

    $options = ['apply_droit'        => [$type_session,
                                         'U'],
                'data_mode'          => 'fiche',
                'scheme_field_view'  => 'native',
                'field_draw_style'   => 'draw_html_input',
                'scheme_field_view_groupby' =>  'group',
                'fields_scheme_part' => 'all',
                'hide_field_empty'   => 0,
                'hide_field_icon'    => 0,
                'hide_field_name'    => 0,
                'hide_field_value'   => 0,
                'field_composition'  => ['hide_field_icon'  => 0,
                                         'hide_field_name'  => 0,
                                         'hide_field_value' => 0]];

    $Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
    $Fabric->fetch_data(["id$table" => $table_value]);
    $tplData = $Fabric->get_templateData();
?>
<div class="flex_v">
    <div>
        <?= $Idae->module('app_fiche_entete', ['table'       => $table,
                                               'table_value' => $table_value]) ?>
    </div>
    <div class="flex_main">
        <form class="flex_v" action="app/actions.php" onsubmit="ajaxFormValidation(this);return false;">
            <input type="hidden" name="F_action" value="app_update"/>
            <!--<input type="hidden" name="afterAction" value="app_fiche/app_fiche_updated"/>-->
            <input type="hidden" name="table" value="<?= $table ?>"/>
            <input type="hidden" name="table_value" value="<?= $table_value ?>"/>
            <input type="hidden" name="<?= $id ?>" value="<?= $table_value ?>"/>
            <input type="hidden" name="vars[<?= $id ?>]" value="<?= $table_value ?>"/>
            <input type="hidden" name="vars[m_mode]" value="1"/>

            <div class="grid-x flex_main  " style="overflow-y:auto;overflow-x:hidden;">
                <div class="cell small-12 medium-9    large-8    small-order-2 medium-order-1  ">
                    <div class="padding_more">
                        <?= $tplData['scheme_field_native'] ?>
                    </div>
                    <div class="padding_more none">
	                    <?= $tplData['scheme_field_image'] ?>
                    </div>
                </div>
                <div class="cell small-12 medium-3 large-4 small-order-1 medium-order-2">
                    <div class="text-center padding_more  ">
                        <a class="button button_full" data-crypted_link="<?= $crypted_image_link ?>" type="button">
                            Voir images
                        </a>
                    </div>
                    <br>
                    <?= $Idae->module('app_update_fk', ['table'       => $table,
                                                        'table_value' => $table_value,
                                                        'edit_field'  => 1,
                                                        'titre'       => 0,
                                                        'show_empty'  => 0]) ?>
                    <? if (sizeof($GRILLE_FK != 0)): ?>
                        <div class="  padding_more none">
                            <div class="blanc border4">
                                <?

                                    foreach ($GRILLE_FK as $key_table => $field):
                                        $APP_TMP = new App($field['table_fk']);
                                        $GRILLE_FK_TMP = $APP_TMP->get_grille_fk($field['table_fk']);
                                        $id            = 'id' . $field['table_fk'];
                                        $arr           = $APP_TMP->findOne([$field['idtable_fk'] => $ARR[$field['idtable_fk']]]);

                                        if (!empty($vars[$id])) {
                                            continue;
                                        }

                                        $arr_extends   = array_intersect(array_keys($GRILLE_FK), array_keys($GRILLE_FK_TMP));
                                        $http_add_vars = '';
                                        $add_vars      = [];
                                        if (sizeof($arr_extends) != 0) {
                                            foreach ($arr_extends as $key_ext => $table_ext) {
                                                $idtable_ext = 'id' . $table_ext;
                                                if (!empty($ARR[$idtable_ext])) {
                                                    $add_vars[$idtable_ext] = (int)$ARR[$idtable_ext];
                                                }
                                            }
                                            if (!empty($add_vars)) {
                                                $http_add_vars = $APP->translate_vars($add_vars);
                                            }
                                        }
                                        if ($table == 'produit_tarif_gamme' && $field['table_fk'] == 'transport_gamme') {
                                            $APP_TMP = new App('produit');
                                            $arr     = $APP_TMP->findOne(['idproduit' => $ARR['idproduit']]);
                                        }
                                        $select  = AppSocket::cf_module('app/app_select', ['vars'        => $add_vars,
                                                                                           'table'       => $field['table_fk'],
                                                                                           'table_value' => $ARR[$field['idtable_fk']]], 1235);

                                        ?>
                                        <div>
                                            <div class="padding borderb"><?= ucfirst($field['nomAppscheme']) ?></div>
                                            <div class="padding"><?= $select ?></div>
                                        </div>
                                    <? endforeach; ?>
                            </div>
                        </div>
                    <? endif; ?>
                </div>
            </div>
            <br>

            <div class="  flex_h   flex_align_middle boxshadow">
                <div class="text-center  padding_more cursor flex_main">
                    <div class="item_icon item_icon_shadow more inline">
                        <button class="blanc" type="submit" value="<?= idioma('Valider') ?>"
                                style="border:none;padding:0;"><i class="fa fa-check-circle fa-2x"></i></button>
                    </div>
                </div>
                <div class="text-center  padding_more cursor flex_main" onclick="history.back();">
                    <div class="item_icon item_icon_shadow more inline" onclick="history.back();">
                        <i class="fa fa-ban fa-2x"></i>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

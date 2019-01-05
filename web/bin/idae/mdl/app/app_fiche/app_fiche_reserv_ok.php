<?
    include_once($_SERVER['CONF_INC']);

    $type_session        = $_SESSION['type_session'];
    $name_idtype_session = "id$type_session";
    $idtype_session      = (int)$_SESSION[$name_idtype_session];

    $arr_allowed_c = droit_table($type_session, 'C', $table);
    $arr_allowed_r = droit_table($type_session, 'R', $table);
    $arr_allowed_u = droit_table($type_session, 'U', $table);
    $arr_allowed_d = droit_table($type_session, 'D', $table);
    $arr_allowed_l = droit_table($type_session, 'L', $table);

    $table       = $this->HTTP_VARS['table'];
    $Table       = ucfirst($table);
    $table_value = (int)$this->HTTP_VARS['table_value'];
    $vars        = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
    //
    $APP       = new App($table);
    $APP_TABLE = $APP->app_table_one;
    $APPOBJ    = $APP->appobj($table_value, $vars);
    $ARR       = $APP->findOne(["id$table" => (int)$table_value]);

    //
    $html_edit_link = '';
    if ($_SESSION['shop']) {
        $ARR_DROIT_SHOP = ['shop',
                           'shop_jours',
                           'produit'];
        if (in_array($table, $ARR_DROIT_SHOP)) {
            $html_edit_link = '<a class="button" href="#idae/module/update/table=' . $table . '&table_value=' . $table_value . '">modifier</a>';
        }
    }
    if ($_SESSION['livreur']) {
        $ARR_DROIT_LIVREUR = ['livreur',
                              'livreur_affectation'];
        if (in_array($table, $ARR_DROIT_LIVREUR)) {
            $html_edit_link = '<a class="button" href="#idae/module/update/table=' . $table . '&table_value=' . $table_value . '">modifier</a>';

        }
    }

    if ($APP->has_field("dateDebut$table")) {
        echo "date";
    }

    $Idae        = new Idae($table);
    $APP_CMD_PRP = new App('commande_proposition');
    $BIN         = new Bin();
    $ARR_COLLECT = $Idae->get_table_fields($table_value);

    if ($APP->has_field('adresse')) {
        $html_map_link = $html_attr = 'table=' . $table . '&table_value=' . $table_value;
    }

    $ARR_CMD_PROP = $APP_CMD_PRP->findOne(['idcommande' => $table_value,
                                           'idlivreur'  => $idtype_session]);
    if (empty($ARR_CMD_PROP['vuCommande_proposition'])) {
        $ARR_CMD_PROP = $APP_CMD_PRP->update(['idcommande_proposition' => (int)$ARR_CMD_PROP['idcommande_proposition']], ['vuCommande_proposition' => 1]);
    }

    $crypted_link_itinerary = AppLink::map_itinerary($table, $table_value);
?>
<div class="grid-x bordert ">
    <div class="cell small-12 medium-12 blanc  large-12      ">
        <div class="color-base-5  " style="height:300px;min-height:300px;position:relative;z-index:0;">
            <?= $Idae->module('app_fiche_map/app_fiche_map_mini', ['table'         => $table,
                                                                   'table_value'   => $table_value,
                                                                   'show_empty'    => 0,
                                                                   'in_mini_fiche' => 1]) ?>
        </div>
        <div class="cursor    relative " data-crypted_link="<?= $crypted_link_itinerary ?>"
             style="width:100%;z-index:100;">
            <div class="absolute text-center" style="width:100%;left:0;top:-45px;">
                <div class=" color-base-dark inline padding_more border4 boxshadow  "
                     style="z-index:100;border-radius: 50%;margin:0 auto;">
                    <?= $ARR['referenceCommande'] ?>
                </div>
            </div>
            <div class="padding   text-center text-bold uppercase" style="font-size: 1.4em">
                <?= $ARR['adresseCommande'] . ' ' . $ARR['codePostalCommande'] . ' ' . $ARR['villeCommande'] ?>
            </div>
            <? if ($ARR['adresse2Commande']) { ?>
                <div class="padding">
                    <?= $ARR['adresse2Commande'] ?>
                </div>
            <? } ?>
        </div>
        <div class="  relative">
            <div class="flex_h flex_align_middle">
                <div class=" text-center flex_h flex_align_middle" style="width:100%;">
                    <div class="flex_main none "><?= $Idae->cf_output('heure', $ARR); ?></div>
                    <div class="flex_main  " title="emportée">
                        <div class="flex_h flex_align_middle flex_inline">
                            <i class="fa fa-upload fa-2x textgris"></i>

                            <div class="padding text-bold">
                                <?= $Idae->cf_output('heureFinPreparation', $ARR); ?>
                            </div>
                        </div>
                    </div>
                    <div class="padding" style="min-width:90px;">
                        <div class="text-bold  text-center">
                            <?= $ARR['codeCommande'] ?>
                        </div>
                    </div>
                    <div class="flex_main">
                        <div class="flex_h flex_align_middle flex_inline">
                            <i class="fa fa-bicycle fa-2x textgris"></i>

                            <div class="padding text-bold">
                                <?= $Idae->cf_output('heureLivraison', $ARR); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative">
            <div class="relative text-center" style="width:100%;z-index:2000;">
                <? //= $Idae->module("app_console/$table/app_console_progress", ['table'       => $table, 'table_value' => $table_value]) ?>
            </div>
            <div class="blanc">
                <?= $Idae->module('app_fiche/fiche_fields_table', ['table'               => $table,
                                                                   'table_value'         => $table_value,
                                                                   'show_empty'          => 0,
                                                                   'codeAppscheme_field' => ['nom',
                                                                                             'distance',
                                                                                             'dureeLivraison',
                                                                                             'volume']]) ?>
            </div>
        </div>
    </div>
</div>
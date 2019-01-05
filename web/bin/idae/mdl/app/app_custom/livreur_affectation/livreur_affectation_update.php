<?
    include_once($_SERVER['CONF_INC']);

    $table                 = $this->HTTP_VARS['table'];
    $table_value           = (int)$this->HTTP_VARS['table_value'];
    $Table                 = ucfirst($table);
    $vars                  = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
    $http_vars             = $this->translate_vars($vars);
    $type_date             = "dateDebut";
    $type_date_field       = "$type_date$Table";
    $type_date_field_value = empty($vars[$type_date_field]) ? date('Y-m-d') : $vars[$type_date_field];

    // Helper::dump($this->HTTP_VARS);
    $calId          = 'cal_id_table' . $table;
    $calReport      = 'cal_report' . $table;
    $calInput       = 'cal_input_' . $table;
    $calInput_count = 'cal_input_count_' . $table;
    //
    $APP  = new App($table);
    $Idae = new Idae($table);
    //
    $APP_TABLE = $APP->app_table_one;
    $GRILLE_FK = $APP->get_grille_fk();
    //
    $id      = 'id' . $table;
    $ARR_LIV = $APP->findOne(["id$table" => $table_value]);

    $date_str   = function_prod::jourMoisDate_fr($ARR_LIV['dateDebutLivreur_affectation']);
    $heureDebut = maskHeure_sweet($ARR_LIV['heureDebutLivreur_affectation']);
    $heureFin   = maskHeure_sweet($ARR_LIV['heureFinLivreur_affectation']);

    $ID_AM    = $ARR_LIV['idlivreur_affectation'];
    $ACTIF_AM = $ARR_LIV['actifLivreur_affectation'];
    $DATE_LIV = $ARR_LIV['dateDebutLivreur_affectation'];

    $RDO_AM = chkSch("actif$Table", $ACTIF_AM);
    if (!function_exists('tactac_charge')) {
        function tactac_charge($price_brut) {
            //	6% du montant
            return ($price_brut * 0.06);
        }

    }
    if (!function_exists('stripe_charge')) {
        function stripe_charge($price_brut) {
            //	1,4% du montant total + 0,25€
            return ($price_brut * 0.014) + 0.25;
        }

    }
    //

    $total_commande_client    = 26;
    $total_livraison_commande = 3;
    $total_price_brut         = $total_commande_client + $total_livraison_commande;
    $stripe_charge            = stripe_charge($total_price_brut);
    $total_price_net          = ($total_price_brut) - $stripe_charge;
    $toshare                  = $total_price_net - tactac_charge($total_price_net);
    $toshare_shop             = $toshare - $total_livraison_commande;
    $toshare_agent            = $total_livraison_commande;

    // echo "$total_commande_client , $total_livraison_commande , ceil($toshare_shop) , $toshare_agent"
    $here = AppCharges::get_commandeParts(26, 3);
 //   var_dump($here);
?>
<div class="flex_h">
    <form class="flex_main padding_more  " id="form_AM" action="app/actions.php"
          onchange="ajaxFormValidation(this);return false;">
        <button type="submit" class="none"></button>
        <input type="hidden" name="F_action" value="app_update"/>
        <input type="hidden" name="table" value="<?= $table ?>"/>
        <input type="hidden" name="table_value" value="<?= $ID_AM ?>"/>
        <input type="hidden" name="vars[m_mode]" value="1"/>

        <div class="padding_more text-center borderb h3">
            <?= $date_str; ?>
        </div>
        <div class="padding text-center"><?= $ARR_LIV['nomSecteur'] ?></div>
        <div class="padding_more text-center borderb h3 flex_h">
            <div class="text-center flex_main"><?= $heureDebut ?></div>
            <div class="text-center flex_main"><?= $heureFin ?></div>
        </div>
        <div class="padding_more text-center ">
            <div class="padding_more borderb">
                <?= $APP->draw_field(['field_name'     => "actif$Table",
                                      'field_name_raw' => 'actif',
                                      'field_value'    => $ACTIF_AM]); ?>
            </div>
            <div class="padding_more text-center inline">
                <?= $RDO_AM ?>
            </div>
        </div>
    </form>
</div>
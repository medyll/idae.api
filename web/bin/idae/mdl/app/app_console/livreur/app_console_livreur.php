<?php
    /**
     * Created by PhpStorm.
     * User: Mydde
     * Date: 24/12/2017
     * Time: 16:14
     */

    include_once($_SERVER['CONF_INC']);

    $table = $this->table;;
    $Table = ucfirst($table);

    $vars       = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
    $groupBy    = empty($this->HTTP_VARS['groupBy']) ? '' : $this->HTTP_VARS['groupBy'];
    $page       = (!isset($this->HTTP_VARS['page'])) ? 0 : $this->HTTP_VARS['page'];
    $type_liste = (!isset($this->HTTP_VARS['type_liste'])) ? 0 : $this->HTTP_VARS['type_liste'];
    $nbRows     = (empty($this->HTTP_VARS['nbRows'])) ? empty($settings_nbRows) ? 1 : (int)$settings_nbRows : $this->HTTP_VARS['nbRows'];

    $type_session        = $_SESSION['type_session'];
    $name_idtype_session = "id$type_session";
    $idtype_session      = (int)$_SESSION[$name_idtype_session];

    $BIN                      = new Bin();
    $Idae                     = new Idae($table);
    $APP                      = new App($table);
    $APP_COMMANDE_PROPOSITION = new App('commande_proposition');
    $APP_SESSION              = new App($type_session);

    $ARR_SESSION = $APP_SESSION->findOne([$name_idtype_session => $idtype_session]);

    // livreur affecté ?
    $test_affect = $BIN->test_livreur_is_affected($idtype_session);

    $idsecteur = (int)$ARR_SESSION['idsecteur'];

    $APP_TABLE = $APP->app_table_one;

    $APPOBJ      = $APP->appobj(null, $vars);
    $ARR         = $APPOBJ->ARR;
    $idappscheme = (int)$APP->idappscheme;
    $name_id     = "id$table";

    $mdl  = 'app_fiche/app_fiche_reserv';
    $html = '';

    $sortBy        = empty($this->HTTP_VARS['sortBy']) ? empty($APP_TABLE['sortFieldName']) ? $APP->nomAppscheme : $APP_TABLE['sortFieldName'] : $this->HTTP_VARS['sortBy'];
    $sortDir       = empty($this->HTTP_VARS['sortDir']) ? empty($APP_TABLE['sortFieldOrder']) ? 1 : (int)$APP_TABLE['sortFieldOrder'] : (int)$this->HTTP_VARS['sortDir'];
    $sortBySecond  = empty($this->HTTP_VARS['sortBySecond']) ? empty($APP_TABLE['sortFieldSecondName']) ? 'dateCreation' . $Table : $APP_TABLE['sortFieldSecondName'] : $this->HTTP_VARS['sortBySecond'];
    $sortDirSecond = empty($this->HTTP_VARS['sortDirSecond']) ? empty($APP_TABLE['sortFieldSecondOrder']) ? 1 : (int)$APP_TABLE['sortFieldSecondOrder'] : (int)$this->HTTP_VARS['sortDirSecond'];

    $vars_cmd = [];

    if (!empty($vars['idlivreur'])) {
        $vars_cmd['idlivreur']                 = ['$in' => [(int)$idtype_session]];
        $vars_cmd['idsecteur']                 = (int)$ARR_SESSION['idsecteur'];
        $vars_cmd['code' . $Table . '_statut'] = ['$nin' => ['END']];
        $vars_cmd["dateCreation$Table"]        = date('Y-m-d');
    }

    $RS_COMMANDE = $APP->find($vars_cmd)->sort(['timeFinPreparationCommande' => 1,
                                                $sortBySecond                => $sortDirSecond])->skip(($nbRows * $page))->limit($nbRows);

    $where['idlivreur']                 = $idtype_session;
    $where['idsecteur']                 = $idsecteur;
    $where['dateCommande_proposition']  = date('Y-m-d');
    $where['endedCommande_proposition'] = ['$ne' => 1];
    $where['$or'][]                     = ['actifCommande_proposition' => 1];
    $where['$or'][]                     = ['livreur_take' => (int)$idtype_session];

    $RS_CMD_PROP  = $APP_COMMANDE_PROPOSITION->find(['idlivreur'                 => (int)$idtype_session,
                                                     'actifCommande_proposition' => 1,
                                                     'dateCommande_proposition'  => date('Y-m-d')]);
    $ARR_CMD_PROP = $RS_CMD_PROP->getNext();

    if (!empty($ARR_CMD_PROP['idcommande'])) {
        // $html = $Idae->module($mdl, "table=$table&table_value=" . $ARR_CMD_PROP['idcommande']);
    }
    if ($RS_COMMANDE->count() != 0) {
        while ($arr_app = $RS_COMMANDE->getNext()) {
            $value = $arr_app[$name_id];
            $html .= $Idae->module($mdl, "table=$table&table_value=$value");
        }
    } else {

    }
?>
<div style="height:100%;overflow:hidden;">
    <? if (empty($test_affect)) { ?>
        Aucune affectation ce jour
    <? } ?>
    <div style="height: 100%;z-index:10" class="relative" data-idsecteur="<?= $ARR_SESSION['idsecteur'] ?>"
         data-console_liste="" data-table="<?= $table ?>" data-type_session="<?= $type_session ?>"
         data-<?= $name_idtype_session ?>="<?= $idtype_session ?>" data-type_liste="<?= $type_liste; ?>">
        <?= $html; ?>
        <div class="absolute" style="z-index:0;top: 0;bottom: 0%;left:0;right:0;">
            <div class="absolute text-center" style="z-index:0;top: 25%;bottom:25%;left:25%;right:25%;">
                <div class="padding_more border4   text-center inline ededed ">
                    <div>
                        <div class="padding_more">
                            Actuellement<br>
                            <span data-idsecteur="<?= $idsecteur ?>" data-count_secteur_commande_free="commande">
                            <?= CommandeQueue::secteur_commande_free_count($idsecteur) ?>
                            </span>
                        </div>
                        <button class="padding_more boxshadow" style=""
                                data-action_link="action/propose_commande_coursier/idlivreur:<?= $idtype_session ?>">
                            demande commande secteur
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?
	include_once($_SERVER['CONF_INC']);

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table                 = $this->HTTP_VARS['table'];
	$Table                 = ucfirst($table);
	$vars                  = empty($this->HTTP_VARS['vars']) ? [] : function_prod::cleanPostMongo($this->HTTP_VARS['vars'], 1);
	$http_vars             = $this->translate_vars($vars);
	$type_date             = "dateDebut";
	$type_date_field       = "$type_date$Table";
	$type_date_field_value = empty($vars[$type_date_field]) ? date('Y-m-d') : $vars[$type_date_field];
	$date_str              = function_prod::jourMoisDate_fr($type_date_field_value);

	$calId          = 'cal_id_table' . $table;
	$calReport      = 'cal_report' . $table;
	$calInput       = 'cal_input_' . $table;
	$calInput_count = 'cal_input_count_' . $table;
	//
	$APP  = new App($table);
	$Idae = new Idae($table);

	$APP_SHOP                = new App('shop');
	$APP_SHOP_JOURS          = new App('shop_jours');
	$APP_SHOP_SHIFT          = new App('shop_jours_shift');
	$APP_JOURS               = new App('jours');
	$APP_LIVREUR             = new App('livreur');
	$APP_LIV_AFFECT          = new App('livreur_affectation');
	$APP_SECTEUR_JOURS_SHIFT = new App('secteur_jours_shift');

	//
	$APP_TABLE = $APP->app_table_one;
	$GRILLE_FK = $APP->get_grille_fk();
	//
	$ARR_LIVREUR = $APP_LIVREUR->findOne(['idlivreur' => $idtype_session]);
	$idsecteur   = (int)$ARR_LIVREUR['idsecteur'];
	$id          = 'id' . $table;
	$rs          = $APP->find($vars + [$type_date . $Table => $type_date_field_value])->sort(['heureDebutLivreur_affectation' => 1]);
	$ct          = $rs->count();
	$ACTIF_AM    = 0;
	$ACTIF_PM    = 0;
	$ID_AM       = '';
	$ID_PM       = '';

	$time                        = strtotime($type_date_field_value);
	$date_affectation            = date('Y-m-d', $time);
	$date_affectation_fr         = date('d-m-Y', $time);
	$date_affectation_code       = date('dmy', $time);
	$date_affectation_jour       = date('w', $time);
	$date_affectation_ordre_jour = date('l', $time);

	$index_jour = ((int)$date_affectation_jour - 1 < 0) ? 6 : (int)$date_affectation_jour - 1;

	$ARR_JOURS = $APP_JOURS->findOne(['ordreJours' => $index_jour]);
	$idjours   = $ARR_JOURS['idjours'];

	$TEST_JOURS_AM = $APP_SECTEUR_JOURS_SHIFT->findOne(['idsecteur' => $idsecteur, 'idjours' => $idjours, 'code_auto' => 'AM']);
	$TEST_JOURS_PM = $APP_SECTEUR_JOURS_SHIFT->findOne(['idsecteur' => $idsecteur, 'idjours' => $idjours, 'code_auto' => 'PM']);

	$vars_qy_liv              = ['idlivreur' => $idtype_session, 'idsecteur' => $idsecteur, 'dateDebutLivreur_affectation' => $date_affectation];
	$vars_qy_liv['code_auto'] = 'AM';
	$ARR_AM                   = $APP_LIV_AFFECT->findOne($vars_qy_liv, ['_id' => 0]);
	$vars_qy_liv['code_auto'] = 'PM';
	$ARR_PM                   = $APP_LIV_AFFECT->findOne($vars_qy_liv, ['_id' => 0]);

	$heureDebut_AM = $ARR_AM['heureDebutLivreur_affectation'] ?: $TEST_JOURS_AM['heureDebutSecteur_jours_shift'];
	$heureFin_AM   = $ARR_AM['heureFinLivreur_affectation'] ?: $TEST_JOURS_AM['heureFinSecteur_jours_shift'];
	$heureDebut_PM = $ARR_PM['heureDebutLivreur_affectation'] ?: $TEST_JOURS_PM['heureDebutSecteur_jours_shift'];
	$heureFin_PM   = $ARR_PM['heureFinLivreur_affectation'] ?: $TEST_JOURS_PM['heureFinSecteur_jours_shift'];

	$heureDebut_AM_str = maskHeure_sweet($heureDebut_AM);
	$heureFin_AM_str   = maskHeure_sweet($heureFin_AM);
	$heureDebut_PM_str = maskHeure_sweet($heureDebut_PM);
	$heureFin_PM_str   = maskHeure_sweet($heureFin_PM);

	$ID_AM    = $ARR_AM['idlivreur_affectation'] ?: null;
	$ID_PM    = $ARR_PM['idlivreur_affectation'] ?: null;
	$ACTIF_AM = $ARR_AM['actifLivreur_affectation'] ?: null;
	$ACTIF_PM = $ARR_PM['actifLivreur_affectation'] ?: null;

	$ARR_COLLECT = $Idae->get_table_fields(null, ['codeAppscheme_field' => ['actif']]);

	$F_action = ($ct != 0) ? 'app_update' : 'app_create';

	$RDO_AM = chkSch("actif$Table", $ACTIF_AM);
	$RDO_PM = chkSch("actif$Table", $ACTIF_PM);

	$index_jour = ((int)date('w') - 1 < 0) ? 6 : (int)date('w') - 1;

	$edit_time_input_type = in_array(ENVIRONEMENT, ['PREPROD', 'PREPROD_LAN']) ? 'text' : 'hidden';

?>
<? if (in_array(ENVIRONEMENT, ['PREPROD', 'PREPROD_LAN'])) { ?>
	<div class="padding borderb text-center">
		heures de début et fin editable en preprod
	</div>
<? } ?>
<div class="flex_h">
	<form class="flex_main padding_more  " id="form_AM" action="app/actions.php" onsubmit="ajaxFormValidation(this);return false;">
		<button type="submit" class="none"></button>
		<input type="hidden" name="F_action" value="<?= $F_action ?>"/>
		<input type="hidden" name="reloadModule[<?= $this->module_route ?>]" value="AM"/>
		<input type="hidden" name="table" value="<?= $table ?>"/>
		<? if ($ID_AM) { ?>
			<input type="hidden" name="table_value" value="<?= $ID_AM ?>"/>
		<? } ?>
		<input type="hidden" name="vars[m_mode]" value="1"/>
		<input type="hidden" name="vars[code_auto]" value="AM"/>
		<input type="<?= $edit_time_input_type ?>" name="vars[heureDebut<?= $Table ?>]" value="<?= $heureDebut_AM ?>"/>
		<input type="<?= $edit_time_input_type ?>" name="vars[heureFin<?= $Table ?>]" value="<?= $heureFin_AM ?>"/>
		<input type="hidden" name="vars[code<?= $Table ?>]" value="<?= 'AM-' . date('dmy') ?>"/>
		<? foreach ($vars as $key => $input):
			if ($key === $type_date_field) continue;
			?>
			<input type="hidden" name="vars[<?= $key ?>]" value="<?= $input ?>">
		<? endforeach; ?>
		<input class="<?= $calInput ?>" type="hidden" name="vars[<?= $type_date_field ?>]" value="<?= $type_date_field_value ?>">
		<input class="<?= $calInput_count ?>" type="hidden" value="<?= $ct ?>">
		<div class="padding_more text-center borderb h3">
			AM
		</div>
		<div class="padding_more text-center ">
			<div class="padding_more borderb">
				<i class="fa fa-check-circle item_icon"></i> Actif
			</div>
			<div class="padding_more text-center inline">
				<?= $RDO_AM ?>
			</div>
		</div>
		<div class="padding text-center">
			<?= $heureDebut_AM_str ?> - <?= $heureFin_AM_str ?>
		</div>
		<div class="padding text-center"><?=$ARR_AM['nomSecteur']?></div>

	</form>
	<form class="flex_main padding_more" id="form_PM" action="<?= ACTIONMDL ?>app/actions.php" onsubmit="ajaxFormValidation(this);return false;">
		<button type="submit" class="none"></button>
		<input type="hidden" name="F_action" value="<?= $F_action ?>"/>
		<input type="hidden" name="reloadModule[<?= $this->module_route ?>]" value="PM"/>
		<input type="hidden" name="table" value="<?= $table ?>"/>
		<? if ($ID_PM) { ?>
			<input type="hidden" name="table_value" value="<?= $ID_PM ?>"/>
		<? } ?>
		<input type="hidden" name="vars[m_mode]" value="1"/>
		<input type="hidden" name="vars[code_auto]" value="PM"/>
		<input type="<?= $edit_time_input_type ?>" name="vars[heureDebut<?= $Table ?>]" value="<?= $heureDebut_PM ?>"/>
		<input type="<?= $edit_time_input_type ?>" name="vars[heureFin<?= $Table ?>]" value="<?= $heureFin_PM ?>"/>
		<input type="hidden" name="vars[code<?= $Table ?>]" value="<?= 'PM-' . date('dmy') ?>"/>
		<? foreach ($vars as $key => $input):
			if ($key === $type_date_field) continue; ?>
			<input type="hidden" name="vars[<?= $key ?>]" value="<?= $input ?>">
		<? endforeach; ?>
		<input class="<?= $calInput ?>" type="hidden" name="vars[<?= $type_date_field ?>]" value="<?= $type_date_field_value ?>">
		<div class="padding_more text-center borderb h3">
			PM
		</div>
		<div class="padding_more text-center ">
			<div class="padding_more borderb">
				<i class="fa fa-check-circle item_icon"></i> Actif
			</div>
			<div class="padding_more text-center inline">
				<?= $RDO_PM ?>
			</div>
		</div>
		<div class="padding text-center">
			<?= $heureDebut_PM_str ?> - <?= $heureFin_PM_str ?>
		</div>
		<div class="padding text-center"><?=$ARR_AM['nomSecteur']?></div>

	</form>
</div>
<script>
	validate_liv_affect = function () {
		$ ('#form_PM').find ('[type="submit"]').trigger ('click');
		$ ('#form_AM').find ('[type="submit"]').trigger ('click');
	}
</script>
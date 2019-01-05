<?
	include_once($_SERVER['CONF_INC']);

	/**
	 * Integer $idlivreur
	 * Date $dateDebut Y-m-d
	 * String code_auto AM|PM
	 *
	 */

	$table                 = 'livreur_affectation';
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

?>
<div title="<?= $date_str.' '.$heureDebut.' '.$heureFin ?>" class="bordert inline">
	<?= $APP->draw_field(['field_name' => "actif$Table", 'field_name_raw' => 'actif', 'field_value' => $ACTIF_AM]); ?>
</div>

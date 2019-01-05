<?
	include_once($_SERVER['CONF_INC']);
	$uniqid    = uniqid();
	$daCal     = 'idcal' . $uniqid;
	$calReport = 'idcalreport' . $uniqid;

	//Helper::dump(array_merge($this->HTTP_VARS, ['mode' => 'fiche']));

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table = empty($this->HTTP_VARS['table']) ? '' : $this->HTTP_VARS['table'];

	if (!empty($table)) {
		$Idae = new Idae($table);
		if ($Idae->has_field_fk($type_session)) {
			//$this->HTTP_VARS['vars'][$name_idtype_session] = $idtype_session;
		}
	} else {
		$Idae = new Idae();
	}

	if (!empty($this->HTTP_VARS['date'])) {
		$sd = $this->HTTP_VARS['sd'] = strtotime(date_mysql($this->HTTP_VARS['date']));
	}
	if (!empty($this->HTTP_VARS['vars']['dateDebutLivreur_affectation'])) {
		$sd = $this->HTTP_VARS['sd'] = strtotime(date_mysql($this->HTTP_VARS['vars']['dateDebutLivreur_affectation']));
	}
	unset($this->HTTP_VARS['date']);
	if (empty($this->HTTP_VARS['sd'])) {
		$sd = $this->HTTP_VARS['sd'] = time();
	} else {
		$sd = $this->HTTP_VARS['sd'];
	};

	$attr = empty($this->HTTP_VARS['calendar_target']) ? '' : "data-calendar_target=" . $this->HTTP_VARS['calendar_target'];

	$date_str = function_prod::jourMoisDate_fr(date('Y-m-d', $sd));
	// Helper::dump(array_merge($this->HTTP_VARS, ['mode' => 'fiche']));

?>
<div data-app_calendrier <?= $attr ?> class="flex_v padding" style="margin:0 auto;overflow:hidden;padding:0;width:100%;max-width:100%;">
	<div data-nav_cal class="color-base-3"><?= $Idae->module('app_calendrier/app_calendrier_nav', $this->HTTP_VARS); ?></div>
	<div id="<?= $daCal ?>" data-nav_zone class="flex_main boxshadowb"><?= $Idae->module('app_calendrier/app_calendrier_day', $this->HTTP_VARS); ?></div>
	<div id="<?= $calReport ?>" class="text-center padding_more h3   "><?= $date_str ?></div>
</div>
<script>
	$ ('#<?= $daCal ?>').on ('dom:act_click', (event, eventData)=> {
		$ ('#<?= $calReport ?>').html (eventData.value_str);
	});
</script>
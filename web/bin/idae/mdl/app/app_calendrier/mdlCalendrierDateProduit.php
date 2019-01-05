<?
	if (file_exists('../conf.inc.php')) include_once('../conf.inc.php');
	if (file_exists('../../conf.inc.php')) include_once('../../conf.inc.php');
	// generalement , le nom du container
	$calendarId       = $_POST['calendarId'];
	$iddevis          = $_POST['iddevis'];
	$time             = time();
	$ClassPrestation  = new Prestation();
	$ClassDateProduit = new DateProduit();
	$rsPrestation     = $ClassPrestation->getOnePrestation(['iddevis' => $iddevis, 'codeType_prestation' => CODETYPE_DEVIS]);
	$idproduit        = $rsPrestation->fields['idproduit'];
	$rsDateProduit    = $ClassDateProduit->getOneDateProduit(['idproduit' => $idproduit]);
	$tabmonth         = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
	$tabjour          = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
	$tabjour          = ["L", "M", "M", "J", "V", "S", "D"];

	if (!empty($_POST['form'])) {
		$inputString = "$('" . $_POST['form'] . "')." . $_POST['input'];
	}
	if (empty($_POST['form']) && !empty($_POST['input'])) {
		$inputString = "$('" . $_POST['input'] . "')";
	}
	if (!empty($_POST['function'])) {
		$inputString = $_POST['function'] . "()";
	}
	//$inputString = "$('".$_POST['input']."')";
	$input = time();

	if (!empty($_POST['date'])) {
		$tmpdate = explode("/", $_POST['date']);
		$jour    = $tmpdate[0];
		$mois    = !empty($tmpdate[1]) ? $tmpdate[1] : date("m");
		$annee   = !empty($tmpdate[2]) ? $tmpdate[2] : date("Y");

		$_POST['sd'] = mktime(12, 0, 0, $mois, $jour, $annee);
	}
	unset($_POST['date']);
	if (empty($_POST['sd'])) {
		if (!isset($jour)) $jour = ((!empty($mois)) ? 1 : date("j"));
		if (!isset($mois)) $mois = date("m");
		if (!isset($annee)) $annee = date("Y");
		$sd = $_POST['sd'] = mktime(12, 0, 0, $mois, $jour, $annee);
	} else {
		$sd = $_POST['sd'];
	}

	$jourEnCours  = date("d", $sd);
	$moisEnCours  = date("m", $sd);
	$anneeEnCours = date("Y", $sd);
	$indexJourCrt = date("w", $sd);
	if ($indexJourCrt == 0)
		$indexJourCrt = 7;

	$lienCalAvant = gmmktime(12, 0, 0, $moisEnCours - 1, $jourEnCours, $anneeEnCours);
	$lienCalApres = gmmktime(12, 0, 0, $moisEnCours + 1, $jourEnCours, $anneeEnCours);

	$anneeAvant = $moisEnCours . "','" . ($anneeEnCours - 1);
	$anneeApres = $moisEnCours . "','" . ($anneeEnCours + 1);
	$moyear     = $tabmonth[intval($moisEnCours)] . "&nbsp;&nbsp;" . $anneeEnCours;
	$now        = date("Y/m/d", $sd);

	$moisPrec = mktime(12, 0, 0, $moisEnCours - 1, $jourEnCours, $anneeEnCours);
	$moisSuiv = mktime(12, 0, 0, $moisEnCours + 1, $jourEnCours, $anneeEnCours);

?>
<div style="width:200px;"></div>
<div style="width:200px;margin:auto;height:170px;" class="blanc">
	<div class="blanc" style="width:100%;display:block;" id="outTblCalTache<?= $time ?>">
		<span class="titre3"> <?= idioma('Dates disponibles') ?></span>

		<div class="flowDown" style="overflow:auto;">
			<?
				while ($arr = $rsDateProduit->fetchRow()) {
					?>
					<br/>
					<a onclick="fillInput(<?= $inputString ?>,'<?= date_fr($arr['dateDebutDate_produit']) ?>');"><?= date_fr($arr['dateDebutDate_produit']) ?></a>
					<?
				} ?></div>
	</div>
</div>
<script>
	fillInput = function (input, vars) {
		<? if(!empty($_POST['function'])){ ?>
		<?=$_POST['function']?>(vars);
		<? }else{ ?>
		$ (input).value = vars;
		<? }?>
		return false;
	}
</script>

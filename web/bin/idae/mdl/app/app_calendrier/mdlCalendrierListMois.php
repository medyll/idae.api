<?
	if (file_exists('../conf.inc.php')) include_once('../conf.inc.php');
	if (file_exists('../../conf.inc.php')) include_once('../../conf.inc.php');
	$sd         = $_POST['sd'];
	$calendarId = $_POST['calendarId'];
	$jour       = date("d", $sd);
	$mois       = date("m", $sd);
	$annee      = date("Y", $sd);
	$tabMois2   = [1 => "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
	$lienP      = gmmktime(12, 0, 0, $mois, $jour, $annee - 1);
	$lienS      = gmmktime(12, 0, 0, $mois, $jour, $annee + 1);
	// vardump($_POST);
?>
<div class="padding applink aligncenter">
	<a onClick="ajaxInMdl('app/app_calendrier/mdlCalendrierListMois','<?= $_POST['yearCal'] ?>','<?= sendPost("sd=$lienP") ?>');"><<</a>
	<span><?= $annee ?></span>
	<a onClick="ajaxInMdl('app/app_calendrier/mdlCalendrierListMois','<?= $_POST['yearCal'] ?>','<?= sendPost("sd=$lienS") ?>');">>></a>
</div>
<div class="relative" style="text-align:left;padding:0px;height: 100%;width:100%;overflow:hidden;" id="dynlistMois">
	<table style="width:100%;" class="applink toggler">
		<?
			$i = 0;
			for ($m = 1; $m <= 12; $m++) {
				$lien = gmmktime(12, 0, 0, $m, $jour, $annee);
				($lien == $sd) ? $class = 'active' : $class = '';
				if ($i != 0 && $i % 3 == 0) echo '</tr><tr>';
				$i++;
				?>
				<td class="listMois  aligncenter " onclick="reloadModule('app/app_calendrier/mdlCalendrier','<?= $calendarId ?>','<?= sendPost("sd=$lien", $_POST) ?>');">
					<a class="<?= $class ?> ellipsis autoToggle"> <?= $tabMois2[$m] ?> </a>
				</td>
			<? } ?>
	</table>
</div>  
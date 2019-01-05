<?
	include_once($_SERVER['CONF_INC']);
	$uniqid = uniqid();
	// 7 derniers jours
	$startTime = date('d/m/Y', mktime() - 7 * 3600 * 24);
	$endTime   = date('d/m/Y', mktime());
	$label     = '7 derniers jours';
	$out[]     = ['startTime' => $startTime, 'endTime' => $endTime, 'label' => $label];
	// cette semaine
	$startTime = date('d/m/Y', mktime(0, 0, 0, date('n'), date('j'), date('Y')) - ((date('N') - 1) * 3600 * 24));
	$endTime   = date('d/m/Y', mktime());
	$label     = 'Cette semaine';
	$out[]     = ['startTime' => $startTime, 'endTime' => $endTime, 'label' => $label];
	// semaine derniere
	$startTime = date('d/m/Y', mktime(0, 0, 0, date('n'), date('j') - 6, date('Y')) - ((date('N')) * 3600 * 24));
	$endTime   = date('d/m/Y', mktime(23, 59, 59, date('n'), date('j'), date('Y')) - ((date('N')) * 3600 * 24));
	$label     = 'semaine derniere';
	$out[]     = ['startTime' => $startTime, 'endTime' => $endTime, 'label' => $label];
	// mois en cours
	$startTime = date('d/m/Y', mktime(0, 0, 0, date('m'), 1, date('Y')));
	$endTime   = date('d/m/Y', mktime());
	$label     = 'mois en cours';
	$out[]     = ['startTime' => $startTime, 'endTime' => $endTime, 'label' => $label];
	// le mois dernier
	$startTime = date('d/m/Y', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
	$endTime   = date('d/m/Y', mktime(23, 59, 59, date('m'), date('d') - date('j'), date('Y')));
	$label     = 'le mois dernier';
	$out[]     = ['startTime' => $startTime, 'endTime' => $endTime, 'label' => $label];
	// 30 derniers jours
	$startTime = date('d/m/Y', mktime() - 30 * 3600 * 24);
	$endTime   = date('d/m/Y', mktime());
	$label     = '30 derniers jours';
	$out[]     = ['startTime' => $startTime, 'endTime' => $endTime, 'label' => $label];
	// 90 derniers jours
	$startTime = date('d/m/Y', mktime() - 90 * 3600 * 24);
	$endTime   = date('d/m/Y', mktime());
	$label     = '90 derniers jours';
	$out[]     = ['startTime' => $startTime, 'endTime' => $endTime, 'label' => $label];
?>
<div class="applink applinkblock toggler">
	<? foreach ($out as $inlink): ?>
		<a class="autoToggle" onClick="$(this).fire('dom:act_click',{dateDebut:'<?= $inlink['startTime'] ?>',dateFin:'<?= $inlink['endTime'] ?>',value:'<?= $inlink['label'] ?>'})"><?= $inlink['label'] ?></a>
	<? endforeach; ?>
</div>
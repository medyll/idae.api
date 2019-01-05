<?
	include_once($_SERVER['CONF_INC']);

	$_POST['table_value'] = (int)$_POST['table_value'];

	$type_session        = $_SESSION['type_session'];
	$name_idtype_session = "id$type_session";
	$idtype_session      = (int)$_SESSION[$name_idtype_session];

	$table            = $_POST['table'];
	$Table            = ucfirst($table);
	$name_id          = "id$table";
	$value_id         = (int)$_POST['table_value'];
	$field_name_table = "nom$Table";

	$APP              = new App($table);
	$Idae             = new Idae($table);
	$uniqid           = 't' . uniqid();
	$form_submit_auto = 'auto_' . uniqid();

	$lat_field = "latitude$Table";
	$lng_field = "longitude$Table";
	//
	$arrV = $APP->findOne([$name_id => (int)$value_id]);

	$zoom = 5;
	if (empty($arrV[$lat_field]) || empty($arrV[$lng_field])) {
		$arrV[$lat_field] = $arrV[$lng_field] = 0;
		$zoom             = 2;
	}

	$adresse = $arrV["adresse$Table"] . ' ' . $arrV["codePostal$Table"] . ' ' . $arrV["ville$Table"];
	if (!empty($arrV['idshop'])) {
		$APP_SHOP     = new App('shop');
		$ARR_SHOP     = $APP_SHOP->findOne(['idshop' => (int)$arrV['idshop']]);
		$address_shop = $ARR_SHOP['adresseShop'] . ' ' . $ARR_SHOP['codePostalShop'] . ' ' . $ARR_SHOP['nomVille'];
	}
?>
<style>
	#map_canvas {
		display : block !important;
		height  : 450px;
	}
</style>
<div class="flex_v">
	<form id="<?= $form_submit_auto ?>" class="   none  " action="<?= ACTIONMDL ?>app/actions.php" onsubmit="ajaxFormValidation(this);return false;">
		<button type="submit" class="none"></button>
		<input type="hidden" name="F_action" value="app_update"/>
		<input type="hidden" name="table" value="<?= $table ?>"/>
		<input type="hidden" name="table_value" value="<?= $table_value ?>"/>
		<input type="text" field="duration" name="vars[tempsLivraisonCommande]" value="<?= $arrV["tempsLivraison$Table"] ?>">
		<input type="text" field="distance" name="vars[distanceLivraisonCommande]" value="<?= $arrV["distanceLivraison$Table"] ?>">
	</form>
	<form id="<?= $uniqid ?>"
	      onsubmit="ajaxFormValidation(this);return false;"
	      action="<?= ACTIONMDL ?>app/actions.php">
		<!--<input type="hidden"
			   name="F_action"
			   value="app_update"/>-->
		<input type="hidden" name="table" value="<?= $table ?>">
		<input type="hidden" name="table_value" value="<?= $value_id ?>">
		<input id="gps<?= $Table ?>_lat" type="hidden" name="vars[gps<?= $Table ?>][lat]" value="<?= $arrV[$lat_field] ?>">
		<input id="gps<?= $Table ?>_lng" type="hidden" name="vars[gps<?= $Table ?>][lng]" value="<?= $arrV[$lng_field] ?>">
		<input id="gps<?= $Table ?>_lng" type="hidden" name="vars[gps<?= $Table ?>][type]" value="Point">
		<input id="gps<?= $Table ?>_lng" type="hidden" name="vars[gps<?= $Table ?>][coords][]" value="<?= $arrV[$lat_field] ?>">
		<input id="gps<?= $Table ?>_lng" type="hidden" name="vars[gps<?= $Table ?>][coords][]" value="<?= $arrV[$lng_field] ?>">
		<table class="none ">
			<tr>
				<td>
					<?= idioma('Adresse pour ') . $APP->nomAppscheme ?>
				</td>
				<td colspan="3">
					<input id="z<?= $uniqid ?>"
					       type="text"
					       class="inputLarge"
					       value="<?= $arrV["adresse$Table"] . ' ' . $arrV["codePostal$Table"] . ' ' . $arrV["ville$Table"] ?>"/>
				</td>
				<td>
					<button id="button_fiche_map<?= $table_value ?>" type="button"
					        class="none validButton"
					        value="Situer">
						<i class="fa fa-map-marker"></i> <?= idioma('Situer') ?></button>
				</td>
			</tr>
			<tr class="none">
				<td>
					<?= idioma('latitude') ?>
				</td>
				<td>
					<input type="text"
					       class="inputSmall"
					       value="<?= $arrV[$lat_field] ?>"
					       name="vars[<?= $lat_field ?>]"
					       id="<?= $lat_field ?><?= $uniqid ?>">
				</td>
				<td>
					<?= idioma('longitude') ?>
				</td>
				<td>
					<input type="text"
					       class=" inputSmall"
					       value="<?= $arrV[$lng_field] ?>"
					       name="vars[<?= $lng_field ?>]"
					       id="<?= $lng_field ?><?= $uniqid ?>">
				</td>
				<td></td>
			</tr>
		</table>
	</form>
	<div id="map_canvas"
	     class="relative flex_main ededed"
	     style="width:100%;position:relative;overflow:hidden">
	</div>
	<div class="flex_h  flex_align_middle ededed bordert">
		<div class="padding_more flex_main">
			<div class="text-bold h3 text-center"> <?= $arrV["code$Table"] ?></div>
		</div>
		<div class="padding_more flex_main">
			<?= idioma('Adresse ') . $APP->nomAppscheme ?>
			<br>
			<span class="text-bold"> <?= $adresse ?></span>
		</div>
	</div>
	<div class="padding_more color-base-6 text-center bordert">
		<div class="inline text-left">
			<?= $Idae->fiche_next_statut($table_value); ?>
		</div>
	</div>
</div>
<script>
	(function () {
		var mapette = new adminMap ('map_canvas', { map_adresse : '<?=$adresse?>', map_adresse_from : '<?=$address_shop?>' });
		<? if(empty($arrV['dureeLivraisonCommande'])){ ?>
		mapette.register_itinerary();
		<? }?>
		mapette.show_itinerary ();
		// console.log ('request adminMap', { map_adresse : '<?=$adresse?>', map_adresse_from : '<?=$address_shop?>' });
	}).call (this)
</script>

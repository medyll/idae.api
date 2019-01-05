<?
	include_once($_SERVER['CONF_INC']);

	$_POST['table_value'] = (int)$_POST['table_value'];

	$table            = $_POST['table'];
	$Table            = ucfirst($table);
	$name_id          = "id$table";
	$value_id         = (int)$_POST['table_value'];
	$field_name_table = "nom$Table";
	$map_canvas_id    = "map_canvas_$table" . "_$value_id";

	$APP    = new App($table);
	$Idae   = new Idae($table);
	$uniqid = 't' . uniqid();

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
	#<?=$map_canvas_id?> {
		display : block !important;
		height  : 300px;
	}
</style>
<div id="<?=$map_canvas_id?>"
     class="relative    "
     style="width:100%;position:relative;overflow:hidden;z-index:0;">
</div>
<script type="text/javascript">
	(function(){
		var mapette = new adminMap ('<?=$map_canvas_id?>',{map_adresse:'<?=$adresse?>','map_adresse_from':'<?=$address_shop?>',table:'<?=$table?>',table_value:'<?=$table_value?>'});
		<? if(empty($arrV['dureeLivraisonCommande'])){ ?>
		mapette.register_itinerary();
		<? }?>
		mapette.show_itinerary ();
	}).call(this)
</script>

<?
include_once($_SERVER['CONF_INC']);
	$APP = new App('ville');
$uniqid = 't' . uniqid();
$map_canvas = 'm' . $uniqid;

$idville = (int)$_POST['idville'];
//
$arrV = $APP->query_one(array("idville" => (int)$idville));

$zoom = 5;
if (empty($arrV['latitudeVille']) || empty($arrV['longitudeVille'])) {
    $arrV['latitudeVille'] = $arrV['longitudeVille'] = 0;
    $zoom = 2;
}
?>
<style>
    # <?=$map_canvas?> {
        display: block !important;
        height: 450px;
    }
</style>
<div id="<?= $map_canvas ?>"
     class="relative flowDown"
     style="width:100%;position:relative;overflow:hidden"></div>
<script type="text/javascript">
    initializeMap = function () {
        var geocoder = new google.maps.Geocoder();
        var markers = new Array();
        var i = 0;
        var originlat = new google.maps.LatLng(<?=$arrV['latitudeVille']?>, <?=$arrV['longitudeVille']?>);

        var mapOptions = {
            zoom: 9,
            center: new google.maps.LatLng(<?=$arrV['latitudeVille']?>, <?=$arrV['longitudeVille']?>),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        var map = new google.maps.Map($("<?=$map_canvas?>"), mapOptions);
        var marker = new google.maps.Marker({
            position: originlat,
            map: map
        });
        markers.push(marker);

        //
        google.maps.event.addListener(map, 'click', function (event) {
           //  $('latitudeVille<?=$uniqid?>').value = event.latLng.lb;
            // $('longitudeVille<?=$uniqid?>').value = event.latLng.mb;
            // placeMarker(event.latLng);
        }.bind(this));

        placeMarker = function (location) {
            markers.each(function (node, index) {
                markers[index].setMap(null);
            })
            marker = new google.maps.Marker({
                position: location,
                map: map
            });
            markers.push(marker);
            map.setCenter(location);
        }
        searchMap = function (value) {
            geocoder.geocode(
                {'address': value},
                function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var loc = results[0].geometry.location;
                       // placeMarker(loc)
                        //$('latitudeVille<?=$uniqid?>').value = loc.k;
                        //$('longitudeVille<?=$uniqid?>').value = loc.A;
                    }
                    else {
                        alert("Non trouvé: " + status);
                    }
                }
            );
        };
    }
    loadScriptMap = function () {
        if ($('script_map')) {
            initializeMap();
            return;
        }
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.id = 'script_map';
        script.src = "http://maps.googleapis.com/maps/api/js?sensor=true&callback=initializeMap";
        document.body.appendChild(script);
    }
    loadScriptMap();
</script>

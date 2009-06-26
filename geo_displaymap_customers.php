<?

$PageSecurity = 3;
$title = _('Geocoded Customer Branches Report');

include ('includes/session.inc');
include ('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$sql="SELECT * FROM geocode_param WHERE 1";
$ErrMsg = _('An error occurred in retrieving the currency information');;
$result = DB_query($sql, $db, $ErrMsg);
$myrow = DB_fetch_array($result);

$api_key = $myrow['geocode_key'];
$center_long = $myrow['center_long'];
$center_lat = $myrow['center_lat'];
$map_height = $myrow['map_height'];
$map_width = $myrow['map_width'];
$map_host = $myrow['map_host'];

echo '<script src="http://' . $map_host . '/maps?file=api&v=2&key=' . $api_key . '"';
echo ' type="text/javascript"></script>';
echo ' <script type="text/javascript">';
echo '    //<![CDATA[ '; ?>

 var iconBlue = new GIcon(); 
    iconBlue.image = 'http://labs.google.com/ridefinder/images/mm_20_blue.png';
    iconBlue.shadow = 'http://labs.google.com/ridefinder/images/mm_20_shadow.png';
    iconBlue.iconSize = new GSize(12, 20);
    iconBlue.shadowSize = new GSize(22, 20);
    iconBlue.iconAnchor = new GPoint(6, 20);
    iconBlue.infoWindowAnchor = new GPoint(5, 1);	

var iconRed = new GIcon(); 
    iconRed.image = 'http://labs.google.com/ridefinder/images/mm_20_red.png';
    iconRed.shadow = 'http://labs.google.com/ridefinder/images/mm_20_shadow.png';
    iconRed.iconSize = new GSize(12, 20);
    iconRed.shadowSize = new GSize(22, 20);
    iconRed.iconAnchor = new GPoint(6, 20);
    iconRed.infoWindowAnchor = new GPoint(5, 1);

    var customIcons = [];
    customIcons["commercial"] = iconBlue;
    customIcons["domestic"] = iconRed;

    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());

<? echo 'map.setCenter(new GLatLng(' . $center_lat . ', ' . $center_long . '), 10);'; ?>

        GDownloadUrl("geocode_genxml_customers.php", function(data) {
          var xml = GXml.parse(data);
          var markers = xml.documentElement.getElementsByTagName("marker");
          for (var i = 0; i < markers.length; i++) {
            var name = markers[i].getAttribute("name");
            var address = markers[i].getAttribute("address");
	    var type = markers[i].getAttribute("type");
            var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),
                                    parseFloat(markers[i].getAttribute("lng")));
            var marker = createMarker(point, name, address, type);
            map.addOverlay(marker);
          }
        });
      }
    }
 function createMarker(point, name, address, type) {
      var marker = new GMarker(point, customIcons[type]);
      var html = "<b>" + name + "</b> <br/>" + address;
      GEvent.addListener(marker, 'click', function() {
        marker.openInfoWindowHtml(html);
      });
      return marker;
}

    //]]>
  </script>
  </head>

  <body onload="load()" onunload="GUnload()">
<p>
<? echo '<div class="centre" id="map" style="width: ' . $map_width . 'px; height: ' . $map_height . 'px"></div>'; ?>
</p>
  </body>
<?
echo '<div class="centre"><a href="' . $rootpath . '/GeocodeSetup.php">' . _('Go to Geocode Setup') . '</a></div></p>';
include ('includes/footer.inc');
?>
</html>

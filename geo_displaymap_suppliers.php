<?php

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');

$SQL="SELECT map_height, map_width FROM geocode_param";
$ErrMsg = __('An error occurred in retrieving the geocode information');
$Result = DB_query($SQL, $ErrMsg);
$MyRow = DB_fetch_array($Result);

$MapHeight = $MyRow['map_height'];
$MapWidth = $MyRow['map_width'];

$Title = __('Geocoded Supplier Report');

/* include the stylesheet nad javascripts needed to show the open street map */
$ExtraHeadContent = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>' . "\n";
$ExtraHeadContent .= '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>' . "\n";

include('includes/header.php');

// Get supplier data with geocodes
$SQL = "SELECT suppliers.suppname,
			   suppliers.address1,
			   suppliers.address2,
			   suppliers.address3,
			   suppliers.address4,
			   suppliers.lat,
			   suppliers.lng,
			   suppliers.supptype
		FROM suppliers
		WHERE suppliers.lat != 0 AND suppliers.lng != 0
		ORDER BY suppliers.suppname";
$Result = DB_query($SQL);
$Suppliers = array();
while ($MyRow = DB_fetch_array($Result)) {
	$Suppliers[] = $MyRow;
}

echo '<div style="height: ' . $MapHeight . 'px; width: ' . $MapWidth . 'px; margin: 0 auto;" id="map"></div>';
?>

<script>
// Initialize the map
var map = L.map('map').setView([0, 0], 2);

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
	attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
	maxZoom: 19
}).addTo(map);

// Add markers for each supplier
var markers = [];
<?php
foreach ($Suppliers as $supplier) {
	$name = htmlspecialchars($supplier['suppname'], ENT_QUOTES, 'UTF-8');
	$address = htmlspecialchars($supplier['address1'] . ', ' . $supplier['address2'] . ', ' . $supplier['address3'] . ', ' . $supplier['address4'], ENT_QUOTES, 'UTF-8');
	$lat = $supplier['lat'];
	$lng = $supplier['lng'];
	echo "var marker = L.marker([{$lat}, {$lng}]).addTo(map);\n";
	echo "marker.bindPopup('<b>{$name}</b><br>{$address}');\n";
	echo "markers.push([{$lat}, {$lng}]);\n";
}
?>

// Fit map to show all markers
if (markers.length > 0) {
	var bounds = L.latLngBounds(markers);
	map.fitBounds(bounds, {padding: [50, 50]});
}
</script>
<?php

echo '<div class="centre">
		<br />
		<a href="' . $RootPath . '/GeocodeSetup.php">' . __('Go to Geocode Setup') . '</a>
	</div>';

include('includes/footer.php');

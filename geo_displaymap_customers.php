<?php

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');

$SQL="SELECT * FROM geocode_param";
$ErrMsg = __('An error occurred in retrieving the geocode information');
$Result = DB_query($SQL, $ErrMsg);
$MyRow = DB_fetch_array($Result);

$Map_Height = $MyRow['map_height'];
$Map_Width = $MyRow['map_width'];

$Title = __('Geocoded Customers Report');

/* include the stylesheet nad javascripts needed to show the open street map */
$ExtraHeadContent = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>' . "\n";
$ExtraHeadContent .= '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>' . "\n";

include('includes/header.php');

// Get customer branch data with geocodes
$SQL = "SELECT custbranch.brname,
			   custbranch.braddress1,
			   custbranch.braddress2,
			   custbranch.braddress3,
			   custbranch.braddress4,
			   custbranch.lat,
			   custbranch.lng,
			   custbranch.area
		FROM custbranch
		WHERE custbranch.lat != 0 AND custbranch.lng != 0
		ORDER BY custbranch.brname";
$Result = DB_query($SQL);
$Customers = array();
while ($MyRow = DB_fetch_array($Result)) {
	$Customers[] = $MyRow;
}

?>
<div style="height: <?php echo $Map_Height; ?>px; width: <?php echo $Map_Width; ?>px; margin: 0 auto;" id="map"></div>

<script>
// Initialize the map
var map = L.map('map').setView([0, 0], 2);

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
	attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
	maxZoom: 19
}).addTo(map);

// Add markers for each customer
var markers = [];
<?php
foreach ($Customers as $customer) {
	$name = htmlspecialchars($customer['brname'], ENT_QUOTES, 'UTF-8');
	$address = htmlspecialchars($customer['braddress1'] . ', ' . $customer['braddress2'] . ', ' . $customer['braddress3'] . ', ' . $customer['braddress4'], ENT_QUOTES, 'UTF-8');
	$lat = $customer['lat'];
	$lng = $customer['lng'];
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

<div class="centre">
	<br />
	<a href="<?php echo $RootPath; ?>/GeocodeSetup.php"><?php echo __('Go to Geocode Setup'); ?></a>
</div>

<?php
include('includes/footer.php');

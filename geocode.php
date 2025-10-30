<?php
require (__DIR__ . '/includes/session.php');

$Title = __('Geocode Generate');
include ('includes/header.php');

//include('includes/SQL_CommonFunctions.php');
$SQL = "SELECT * FROM geocode_param";
$ResultGeo = DB_query($SQL);
$MyRow = DB_fetch_array($ResultGeo);

$MapHeight = $MyRow['map_height'];
$MapWidth = $MyRow['map_width'];

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Geocode Setup') . '" alt="" />' . ' ' . __('Geocoding of Customers and Suppliers') . '
	</p>';

// select all the customer branches
$SQL = "SELECT * FROM custbranch";
$Result = DB_query($SQL);

// select all the suppliers
$SQL = "SELECT * FROM suppliers WHERE 1";
$Result2 = DB_query($SQL);

// Using OpenStreetMap Nominatim for geocoding
// Initialize delay to respect Nominatim usage policy (1 request per second)
$Delay = 1000000; // 1 second in microseconds
$BaseURL = "https://nominatim.openstreetmap.org/search?format=json&q=";

// Iterate through the customer branch rows, geocoding each address

echo '<table>
		<tr>
			<th>' . __('Customer Code') . '</th>
			<th>' . __('Address') . '</th>
			<th>' . __('Latitude') . '</th>
			<th>' . __('Longitude') . '</th>
		</tr>';

while ($MyRow = DB_fetch_array($Result)) {
	$GeocodePending = true;
	while ($GeocodePending) {
		$Address = urlencode($MyRow['braddress1'] . ',' . $MyRow['braddress2'] . ',' . $MyRow['braddress3'] . ',' . $MyRow['braddress4']);
		$DisplayAddress = $MyRow['braddress1'] . '<br />' . $MyRow['braddress2'] . '<br />' . $MyRow['braddress3'] . '<br />' . $MyRow['braddress4'];
		$id = $MyRow['branchcode'];
		$DebtorNo = $MyRow['debtorno'];
		$RequestURL = $BaseURL . $Address . '&limit=1';

		$Options = array('http' => array('method' => "GET", 'header' => "User-Agent: webERP-geocoding\r\n"));
		$Context = stream_context_create($Options);
		$Buffer = @file_get_contents($RequestURL, false, $Context);

		if ($Buffer !== false) {
			$Json = json_decode($Buffer, true);
			if (!empty($Json) && isset($Json[0]['lat']) && isset($Json[0]['lon'])) {
				// Successful geocode
				$GeocodePending = false;
				$lat = $Json[0]['lat'];
				$lng = $Json[0]['lon'];

				$SQL = "UPDATE custbranch SET lat = '" . $lat . "',
											lng = '" . $lng . "'
										WHERE branchcode = '" . $id . "'
										AND debtorno = '" . $DebtorNo . "'
										LIMIT 1";

				$UpdateResult = DB_query($SQL);

				if ($UpdateResult == 1) {
					echo '<tr class="striped_row">
							<td>' . $id . '</td>
							<td>' . $DisplayAddress . '</td>
							<td>' . $lat . '</td>
							<td>' . $lng . '</td>
						</tr>';
				}
			}
			else {
				// No results found
				$GeocodePending = false;
				echo '<br />' . 'Address: ' . $Address . ' ' . __('failed to geocode.');
				echo 'No results found<br />';
			}
		}
		else {
			// failure to connect
			$GeocodePending = false;
			echo '<br />' . 'Address: ' . $Address . ' ' . __('failed to geocode.');
			echo 'Connection failed<br />';
		}
		usleep($Delay);
	}
}
echo '</table>';

echo '<table>
		<tr>
			<th>' . __('Supplier Code') . '</th>
			<th>' . __('Address') . '</th>
			<th>' . __('Latitude') . '</th>
			<th>' . __('Longitude') . '</th>
		</tr>';

// Iterate through the Supplier rows, geocoding each address
while ($MyRow2 = DB_fetch_array($Result2)) {
	$GeocodePending = true;

	while ($GeocodePending) {
		$Address = urlencode($MyRow2["address1"] . "," . $MyRow2["address2"] . "," . $MyRow2["address3"] . "," . $MyRow2["address4"]);
		$DisplayAddress = $MyRow2['address1'] . '<br />' . $MyRow2['address2'] . '<br />' . $MyRow2['address3'] . '<br />' . $MyRow2['address4'];
		$id = $MyRow2["supplierid"];
		$RequestURL = $BaseURL . $Address . '&limit=1';

		$Options = array('http' => array('method' => "GET", 'header' => "User-Agent: webERP-geocoding\r\n"));
		$Context = stream_context_create($Options);
		$Buffer = @file_get_contents($RequestURL, false, $Context);

		if ($Buffer !== false) {
			$Json = json_decode($Buffer, true);
			if (!empty($Json) && isset($Json[0]['lat']) && isset($Json[0]['lon'])) {
				// Successful geocode
				$GeocodePending = false;
				$lat = $Json[0]['lat'];
				$lng = $Json[0]['lon'];

				$SQL = "UPDATE suppliers SET lat = '" . $lat . "',
											lng = '" . $lng . "'
											WHERE supplierid = '" . $id . "'
											LIMIT 1;";

				$UpdateResult = DB_query($SQL);

				if ($UpdateResult == 1) {
					echo '<tr class="striped_row">
							<td>' . $id . '</td>
							<td>' . $DisplayAddress . '</td>
							<td>' . $lat . '</td>
							<td>' . $lng . '</td>
						</tr>';
				}
			}
			else {
				// No results found
				$GeocodePending = false;
				echo '<br />' . 'Address: ' . $Address . ' failed to geocode.';
				echo 'No results found<br />';
			}
		}
		else {
			// failure to connect
			$GeocodePending = false;
			echo '<br />' . 'Address: ' . $Address . ' failed to geocode.';
			echo '<br />' . 'Connection failed<br />';
		}
		usleep($Delay);
	}
}
echo '</table>';
echo '<br /><div class="centre"><a href="' . $RootPath . '/GeocodeSetup.php">' . __('Go back to Geocode Setup') . '</a></div>';
include ('includes/footer.php');


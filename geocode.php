<?php
$PageSecurity = 3;
$title = _('Geocode Generate');

include ('includes/session.inc');
include ('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$sql = "SELECT * FROM geocode_param WHERE 1";
$ErrMsg = _('An error occurred in retrieving the information');
$resultgeo = DB_query($sql, $db, $ErrMsg);
$row = DB_fetch_array($resultgeo);

$api_key = $row['geocode_key'];
$center_long = $row['center_long'];
$center_lat = $row['center_lat'];
$map_height = $row['map_height'];
$map_width = $row['map_width'];
$map_host = $row['map_host'];

define("MAPS_HOST", $map_host);
define("KEY", $api_key);

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Geocode Setup') . '" alt="">' . ' ' . _('Geocoding of Customers and Suppliers') .'</p>';

// select all the customer branches
$sql = "SELECT * FROM custbranch WHERE 1";
$ErrMsg = _('An error occurred in retrieving the information');
$result = DB_query($sql, $db, $ErrMsg);
$row = DB_fetch_array($result);
// select all the suppliers
$sql = "SELECT * FROM suppliers WHERE 1";
$ErrMsg = _('An error occurred in retrieving the information');
$result2 = DB_query($sql, $db, $ErrMsg);
$row2 = DB_fetch_array($result2);

// Initialize delay in geocode speed
$delay = 0;
$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;

// Iterate through the customer branch rows, geocoding each address
while ($row = @mysql_fetch_assoc($result)) {
  $geocode_pending = true;

  while ($geocode_pending) {
    $address = $row["braddress1"] . ", " . $row["braddress2"] . ", " . $row["braddress3"] . ", " . $row["braddress4"];
    $id = $row["branchcode"];
    $debtorno =$row["debtorno"];
    $request_url = $base_url . "&q=" . urlencode($address);
    $xml = simplexml_load_string(utf8_encode(file_get_contents($request_url))) or die("url not loading");
//    $xml = simplexml_load_file($request_url) or die("url not loading");

    $status = $xml->Response->Status->code;
    if (strcmp($status, "200") == 0) {
      // Successful geocode
      $geocode_pending = false;
      $coordinates = $xml->Response->Placemark->Point->coordinates;
      $coordinatesSplit = split(",", $coordinates);
      // Format: Longitude, Latitude, Altitude
      $lat = $coordinatesSplit[1];
      $lng = $coordinatesSplit[0];

      $query = sprintf("UPDATE custbranch " .
             " SET lat = '%s', lng = '%s' " .
             " WHERE branchcode = '%s' " .
 	     " AND debtorno = '%s' LIMIT 1;",
             mysql_real_escape_string($lat),
             mysql_real_escape_string($lng),
             mysql_real_escape_string($id),
             mysql_real_escape_string($debtorno));
      $update_result = mysql_query($query);
      if (!$update_result) {
        die("Invalid query: " . mysql_error());
      }
    } else if (strcmp($status, "620") == 0) {
      // sent geocodes too fast
      $delay += 100000;
    } else {
      // failure to geocode
      $geocode_pending = false;
      echo '<p>' . _('Customer Branch Code:') . $id . ', Address: ' . $address . _('failed to geocode.');
      echo 'Received status ' . $status . '<br>';
    }
    usleep($delay);
  }
}

// Iterate through the Supplier rows, geocoding each address
while ($row2 = @mysql_fetch_assoc($result2)) {
  $geocode_pending = true;

  while ($geocode_pending) {
    $address = $row2["address1"] . ", " . $row2["address2"] . ", " . $row2["address3"] . ", " . $row2["address4"];
    $id = $row2["supplierid"];
    $request_url = $base_url . "&q=" . urlencode($address);
    $xml = simplexml_load_string(utf8_encode(file_get_contents($request_url))) or die("url not loading");
//    $xml = simplexml_load_file($request_url) or die("url not loading");

    $status = $xml->Response->Status->code;
    if (strcmp($status, "200") == 0) {
      // Successful geocode
      $geocode_pending = false;
      $coordinates = $xml->Response->Placemark->Point->coordinates;
      $coordinatesSplit = split(",", $coordinates);
      // Format: Longitude, Latitude, Altitude
      $lat = $coordinatesSplit[1];
      $lng = $coordinatesSplit[0];

      $query = sprintf("UPDATE suppliers " .
             " SET lat = '%s', lng = '%s' " .
             " WHERE supplierid = '%s' LIMIT 1;",
             mysql_real_escape_string($lat),
             mysql_real_escape_string($lng),
             mysql_real_escape_string($id));
      $update_result = mysql_query($query);
      if (!$update_result) {
        die("Invalid query: " . mysql_error());
      }
    } else if (strcmp($status, "620") == 0) {
      // sent geocodes too fast
      $delay += 100000;
    } else {
      // failure to geocode
      $geocode_pending = false;
      echo '<p>' . _('Supplier Code: ') . $id . ', Address: ' . $address . ' failed to geocode.';
      echo 'Received status ' . $status . '<br>';
    }
    usleep($delay);
  }
}
echo '</p>';

echo '<br><div class="centre"><a href="' . $rootpath . '/GeocodeSetup.php">' . _('Go back to Geocode Setup') . '</a></div>';
include ('includes/footer.inc');
?>

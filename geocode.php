<?php

//$PageSecurity = 3;

require(__DIR__ . '/includes/session.php');

$Title = __('Geocode Generate');
include('includes/header.php');

//include('includes/SQL_CommonFunctions.php');

$SQL = "SELECT * FROM geocode_param";
$Resultgeo = DB_query($SQL);
$Row = DB_fetch_array($Resultgeo);

$APIKey = $Row['geocode_key'];
$center_long = $Row['center_long'];
$center_lat = $Row['center_lat'];
$map_height = $Row['map_height'];
$map_width = $Row['map_width'];
$MapHost = $Row['map_host'];

define("MAPS_HOST", $MapHost);
define("KEY", $APIKey);

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Geocode Setup') . '" alt="" />' . ' ' . __('Geocoding of Customers and Suppliers')  . '</p>';

// select all the customer branches
$SQL = "SELECT * FROM custbranch";
$Result = DB_query($SQL);

// select all the suppliers
$SQL = "SELECT * FROM suppliers WHERE 1";
$Result2 = DB_query($SQL);

/// @todo move getting of geocode info into a dedicated function, and move off google maps

// Initialize delay in geocode speed
$delay = 0;
$BaseURLl = "https://" . MAPS_HOST . "/maps/api/geocode/xml?address=";

// Iterate through the customer branch rows, geocoding each address

while ($Row = DB_fetch_array($Result)) {
  $geocode_pending = true;

  while ($geocode_pending) {
    $Address = urlencode($Row["braddress1"] . "," . $Row["braddress2"] . "," . $Row["braddress3"] . "," . $Row["braddress4"]);
    $id = $Row["branchcode"];
    $DebtorNo = $Row["debtorno"];
    $RequestURL = $BaseURLl . $Address . '&key=' . KEY . '&sensor=true';

    echo '<br \>', __('Customer Code'), ': ', $id;

    $xml = simplexml_load_string(utf8_encode(file_get_contents($RequestURL))) or die("url not loading");
//    $xml = simplexml_load_file($RequestURL) or die("url not loading");

    $status = $xml->status;

    if (strcmp($status, "OK") == 0) {
      // Successful geocode
      $geocode_pending = false;
      $coordinates = $xml->GeocodeResponse->result->geometry->location;
      $coordinatesSplit = explode(",", $coordinates);
      // Format: Longitude, Latitude, Altitude
      $lat = $xml->result->geometry->location->lat;
      $lng = $xml->result->geometry->location->lng;

      $Query = sprintf("UPDATE custbranch " .
             " SET lat = '%s', lng = '%s' " .
             " WHERE branchcode = '%s' " .
 	     " AND debtorno = '%s' LIMIT 1;",
             ($lat),
             ($lng),
             ($id),
             ($DebtorNo));

      $Update_result = DB_query($Query);

      if ($Update_result==1) {
      echo '<br />'. 'Address: ' . $Address . ' updated to geocode.';
      echo '<br />'. 'Received status ' . $status . '<br />';
	}
    } else {
      // failure to geocode
      $geocode_pending = false;
      echo '<br />' . 'Address: ' . $Address . __('failed to geocode.');
      echo 'Received status ' . $status . '<br />';
    }
    usleep($delay);
  }
}

// Iterate through the Supplier rows, geocoding each address
while ($Row2 = DB_fetch_array($Result2)) {
  $geocode_pending = true;

  while ($geocode_pending) {
    $Address = $Row2["address1"] . ",+" . $Row2["address2"] . ",+" . $Row2["address3"] . ",+" . $Row2["address4"];
    $Address = urlencode($Row2["address1"] . "," . $Row2["address2"] . "," . $Row2["address3"] . "," . $Row2["address4"]);
    $id = $Row2["supplierid"];
    $RequestURL = $BaseURLl . $Address . '&key=' . KEY . '&sensor=true';

    echo '<p>' . __('Supplier Code: ') . $id;

    $xml = simplexml_load_string(utf8_encode(file_get_contents($RequestURL))) or die("url not loading");
//    $xml = simplexml_load_file($RequestURL) or die("url not loading");

    $status = $xml->status;

    if (strcmp($status, "OK") == 0) {
      // Successful geocode
      $geocode_pending = false;
      $coordinates = $xml->GeocodeResponse->result->geometry->location;
      $coordinatesSplit = explode(",", $coordinates);
      // Format: Longitude, Latitude, Altitude
      $lat = $xml->result->geometry->location->lat;
      $lng = $xml->result->geometry->location->lng;


      $Query = sprintf("UPDATE suppliers " .
             " SET lat = '%s', lng = '%s' " .
             " WHERE supplierid = '%s' LIMIT 1;",
             ($lat),
             ($lng),
             ($id));

      $Update_result = DB_query($Query);

      if ($Update_result==1) {
      echo '<br />' . 'Address: ' . $Address . ' updated to geocode.';
      echo '<br />' . 'Received status ' . $status . '<br />';
      }
    } else {
      // failure to geocode
      $geocode_pending = false;
      echo '<br />' . 'Address: ' . $Address . ' failed to geocode.';
      echo '<br />' . 'Received status ' . $status . '<br />';
    }
    usleep($delay);
  }
}
echo '<br /><div class="centre"><a href="' . $RootPath . '/GeocodeSetup.php">' . __('Go back to Geocode Setup') . '</a></div>';
include('includes/footer.php');

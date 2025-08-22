<?php

//$PageSecurity = 3;

include('includes/session.php');

include('includes/SQL_CommonFunctions.php');

function parseToXML($htmlStr)
{
	$xmlStr = str_replace('<','&lt;',$htmlStr);
	$xmlStr = str_replace('>','&gt;',$xmlStr);
	$xmlStr = str_replace('"','&quot;',$xmlStr);
	$xmlStr = str_replace("'",'&#39;',$xmlStr);
	$xmlStr = str_replace("&",'&amp;',$xmlStr);
	return $xmlStr;
}

$Title = __('Geocode Generate XML');

$SQL = "SELECT * FROM suppliers";
$Result = DB_query($SQL);

header("Content-type: text/xml");

// Iterate through the rows, printing XML nodes for each
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . "\n<markers>\n";

while ($MyRow = DB_fetch_array($Result)){
  // ADD TO XML DOCUMENT NODE
  echo '<marker ';
  echo 'name="' . parseToXML($MyRow['suppname']) . '" ';
  echo 'address="' . parseToXML($MyRow["address1"] . ", " . $MyRow["address2"] . ", " . $MyRow["address3"] . ", " . $MyRow["address4"]) . '" ';
  echo 'lat="' . $MyRow['lat'] . '" ';
  echo 'lng="' . $MyRow['lng'] . '" ';
  echo 'type="' . $MyRow['supptype'] . '" ';
  echo "/>\n";
}

// End XML file
echo '</markers>';

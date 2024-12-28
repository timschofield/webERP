<?php

//$PageSecurity = 3;
$Title = _('Geocode Generate XML');

include ('includes/session.php');
include('includes/SQL_CommonFunctions.inc');

function parseToXML($htmlStr)
{
$xmlStr=str_replace('<','&lt;',$htmlStr);
$xmlStr=str_replace('>','&gt;',$xmlStr);
$xmlStr=str_replace('"','&quot;',$xmlStr);
$xmlStr=str_replace("'",'&#39;',$xmlStr);
$xmlStr=str_replace("&",'&amp;',$xmlStr);
return $xmlStr;
}

$SQL = "SELECT * FROM suppliers WHERE 1";
$ErrMsg = _('An error occurred in retrieving the information');;
$Result = DB_query($SQL, $ErrMsg);

header("Content-type: text/xml");

// Iterate through the rows, printing XML nodes for each
echo '<markers>';

while ($MyRow = DB_fetch_array($Result)){
  // ADD TO XML DOCUMENT NODE
  echo '<marker ';
  echo 'name="' . parseToXML($MyRow['suppname']) . '" ';
  echo 'address="' . parseToXML($MyRow["address1"] . ", " . $MyRow["address2"] . ", " . $MyRow["address3"] . ", " . $MyRow["address4"]) . '" ';
  echo 'lat="' . $MyRow['lat'] . '" ';
  echo 'lng="' . $MyRow['lng'] . '" ';
  echo 'type="' . $MyRow['supptype'] . '" ';
  echo '/>';
}

// End XML file
echo '</markers>';

?>
<?php

/* $Id: geocode_genxml_customers.php 6565 2014-02-06 08:42:57Z daintree $*/
//$PageSecurity = 3;
$Title = _('Geocode Generate XML');

include ('includes/session.inc');
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

$sql = "SELECT * FROM custbranch WHERE 1";
$ErrMsg = _('An error occurred in retrieving the information');;
$result = DB_query($sql, $db, $ErrMsg);

header("Content-type: text/xml");

// Iterate through the rows, printing XML nodes for each
echo '<markers>';

while ($myrow = DB_fetch_array($result)){
    // ADD TO XML DOCUMENT NODE
    echo '<marker ';
    echo 'name="' . parseToXML($myrow['brname']) . '" ';
    echo 'address="' . parseToXML($myrow["braddress1"] . ", " . $myrow["braddress2"] . ", " . $myrow["braddress3"] . ", " . $myrow["braddress4"]) . '" ';
    echo 'lat="' . $myrow['lat'] . '" ';
    echo 'lng="' . $myrow['lng'] . '" ';
    echo 'type="' . $myrow['area'] . '" ';
    echo '/>';
}

// End XML file
echo '</markers>';

?>
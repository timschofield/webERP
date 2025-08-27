<?php

//$PageSecurity = 3;

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');

function parseToXML($htmlStr)
{
    $xmlStr=str_replace('<','&lt;',$htmlStr);
    $xmlStr=str_replace('>','&gt;',$xmlStr);
    $xmlStr=str_replace('"','&quot;',$xmlStr);
    $xmlStr=str_replace("'",'&#39;',$xmlStr);
    $xmlStr=str_replace("&",'&amp;',$xmlStr);
    return $xmlStr;
}

$Title = __('Geocode Generate XML');

$SQL = "SELECT * FROM custbranch";
$Result = DB_query($SQL);

header("Content-type: text/xml");

// Iterate through the rows, printing XML nodes for each
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . "\n<markers>\n";

while ($MyRow = DB_fetch_array($Result)){
    // ADD TO XML DOCUMENT NODE
    echo '<marker ';
    echo 'name="' . parseToXML($MyRow['brname']) . '" ';
    echo 'address="' . parseToXML($MyRow["braddress1"] . ", " . $MyRow["braddress2"] . ", " . $MyRow["braddress3"] . ", " . $MyRow["braddress4"]) . '" ';
    echo 'lat="' . $MyRow['lat'] . '" ';
    echo 'lng="' . $MyRow['lng'] . '" ';
    echo 'type="' . $MyRow['area'] . '" ';
    echo "/>\n";
}

// End XML file
echo '</markers>';

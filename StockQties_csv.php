<?php

$title = "Produce Stock Quantities CSV";
$PageSecurity = 5;

include ("includes/session.inc");
include ("includes/header.inc");

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",","",$str);
}

echo "<P>Making a comma seperated values file of the current stock quantities";

$sql = "SELECT StockID, Sum(Quantity) FROM LocStock GROUP BY StockID HAVING Sum(Quantity)<>0";
$result = DB_query($sql,$db);

if (DB_error_no($db)!=0){
	echo "<BR>The SQL to get the stock quantites failed with the message<BR>" . DB_error_msg($db);
	exit;
}


$fp = fopen( $reports_dir . "/StockQties.csv", "w");

While ($myrow = DB_fetch_row($result)){

	$line = stripcomma($myrow[0]) . ", " . stripcomma($myrow[1]);
	fputs($fp, $line."\n");
}

fclose($fp);

echo "<P><a href='" . $rootpath . "/" . $reports_dir . "/StockQties.csv'>click here</a> to view the file<br>";

include("includes/footer.inc");

?>
<?php
/* $Revision: 1.2 $ */
/* Script to make stock locations for all parts that do not have stock location records set up*/

$title = "UTILITY PAGE That makes stock location records for parts where they do not already exist";
$PageSecurity=15;
include ("includes/session.inc");
include("includes/header.inc");

$sql = "SELECT StockMaster.StockID FROM StockMaster LEFT JOIN LocStock ON StockMaster.StockID=LocStock.StockID RIGHT JOIN Locations ON Locations.LocCode = LocStock.LocCode WHERE LocStock.StockID IS NULL";

$Result = DB_query($sql,$db);
if (DB_error_no($db) !=0) {
	echo "The items/locations that need stock location records created cannot be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
}

while ($myrow=DB_fetch_row($Result)){

	echo "<BR>Inserting location records for " . $myrow[0];

	$sql = "INSERT INTO LocStock (LocCode, StockID) SELECT Locations.LocCode, '" . $myrow[0] . "' FROM Locations";

	$InsResult = DB_query($sql,$db);
	if (DB_error_no($db)!=0 && $debug==1){

		echo "<BR>The SQL to insert location records for part " . $myrow[0] . " failed, the SQL statement was:<BR>$sql";
		exit;			
	}

}

echo "<P>Any stock items that may not have had stock location records, have now been given new location stock records.";

include("includes/footer.inc");
?>

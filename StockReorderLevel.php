<?php
$title = "Stock Re-Order Level Maintenance";

$PageSecurity = 4;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}

$result = DB_query("SELECT Description, Units FROM StockMaster WHERE StockID='$StockID'",$db);
$myrow = DB_fetch_row($result);
echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (In Units of $myrow[1] )</FONT>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo "Stock Code:<input type=text name='StockID' size=21 value='$StockID' maxlength=20>";

echo "     <INPUT TYPE=SUBMIT NAME='Show' VALUE='Show Re-Order Levels'><HR>";



$sql = "SELECT LocStock.LocCode, Locations.LocationName, LocStock.Quantity, LocStock.ReorderLevel FROM LocStock, Locations WHERE LocStock.LocCode=Locations.LocCode AND LocStock.StockID = '" . $StockID . "' ORDER BY LocStock.LocCode";
$LocStockResult = DB_query($sql, $db);

if (DB_error_no($db) !=0) {
	echo "The stock held at each location cannot be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
}

echo "<CENTER><TABLE CELLPADDING=2 BORDER=2>";
$TableHeader = "<TR><TD class='tableheader'>Location</TD><TD class='tableheader'>Quantity On Hand</TD><TD class='tableheader'>Re-Order Level</TD></TR>";

echo $TableHeader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}


	if (isset($_POST['UpdateData']) AND is_numeric($_POST[$myrow["LocCode"]]) AND $_POST[$myrow["LocCode"]]>0){

	   $myrow["ReorderLevel"] = $_POST[$myrow["LocCode"]];
	   $sql = "UPDATE LocStock SET ReorderLevel = " . $_POST[$myrow["LocCode"]] . " WHERE StockID = '" . $StockID . "' AND LocCode = '"  . $myrow["LocCode"] ."'";
	   $UpdateReorderLevel = DB_query($sql,$db);

	}
	/*			Location				   Quantity On Hand				    Re-Order Level					     Location 	     Quantity On Hand					      Re-Order Level */
	printf("<td>%s</td><td ALIGN=RIGHT>%s</td><td><INPUT TYPE=TEXT NAME=%s MAXLENGTH=10 SIZE=10 VALUE=%s></td></tr>", $myrow["LocationName"], number_format($myrow["Quantity"]), $myrow["LocCode"], $myrow["ReorderLevel"]);
	$j++;
	If ($j == 12){
		$j=1;
		echo $TableHeader;
	}
//end of page full new headings if
}
//end of while loop

echo "</TABLE><INPUT TYPE=SUBMIT NAME='UpdateData' VALUE='Update'><HR>";
echo "<A HREF='$rootpath/StockMovements.php?" . SID . "StockID=$StockID'>Show Stock Movements</A>";
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "StockID=$StockID'>Show Stock Usage</A>";
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Outstanding Sales Orders</A>";
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A>";

echo "</form>";
include("includes/footer.inc");

?>

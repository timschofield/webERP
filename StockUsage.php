<?php
$title = "Stock Usage";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}

// Sherifoz 25.06.03 Check if the item is dummy/assembly/kit. no stock usage for these.
$result = DB_query("SELECT Description, Units, MBflag FROM StockMaster WHERE StockID='$StockID'",$db);
$myrow = DB_fetch_row($result);

$Its_A_KitSet_Assembly_Or_Dummy =False;
if (($myrow[2]=="K") OR ($myrow[2]=="A") OR ($myrow[2]=="D")) {
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B></FONT>";

	echo "<BR>The selected item is a dummy, assembly or kitset item and cannot have a stock holding, please select a different item.";

	$StockID = '';
} else {
	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (in units of $myrow[1])</FONT>";
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID ."' METHOD=POST>";
echo "Stock Code:<input type=text name='StockID' size=21 maxlength=20 value='$StockID' >";

echo "  From Stock Location:<SELECT name='StockLocation'> ";

$sql = "SELECT LocCode, LocationName FROM Locations";
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($myrow["LocCode"] == $_POST['StockLocation']){
		     echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		} else {
		     echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		}
	} elseif ($myrow["LocCode"]==$_SESSION['UserStockLocation']){
		 echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		 $_POST['StockLocation']=$myrow["LocCode"];
	} else {
		 echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
	}
}
if (isset($_POST['StockLocation'])){
	if ("All"== $_POST['StockLocation']){
	     echo "<OPTION SELECTED Value='All'>All Locations";
	} else {
	     echo "<OPTION Value='All'>All Locations";
	}
}
echo "</SELECT>";

echo " <INPUT TYPE=SUBMIT NAME='ShowUsage' VALUE='Show Stock Usage'>";
echo "<HR>";

/* $NumberOfPeriodsOfStockUsage  is defined in config.php as a user definable variable 
config.php is loaded by header.inc */


if($_POST['StockLocation']=='All'){
	$sql = "SELECT Periods.PeriodNo, Periods.LastDate_in_Period, Sum(-StockMoves.Qty) AS QtyUsed FROM StockMoves INNER JOIN Periods ON StockMoves.Prd=Periods.PeriodNo WHERE (StockMoves.Type=10 or StockMoves.Type=11 or StockMoves.Type=28) AND StockMoves.HideMovt=0 AND StockMoves.StockID = '" . $StockID . "' GROUP BY Periods.PeriodNo, Periods.LastDate_in_Period ORDER BY PeriodNo DESC LIMIT " . $NumberOfPeriodsOfStockUsage;
} else {
	$sql = "SELECT Periods.PeriodNo, Periods.LastDate_in_Period, Sum(-StockMoves.Qty) AS QtyUsed FROM StockMoves INNER JOIN Periods ON StockMoves.Prd=Periods.PeriodNo WHERE (StockMoves.Type=10 or StockMoves.Type=11 or StockMoves.Type=28) AND StockMoves.HideMovt=0 AND StockMoves.LocCode='" . $_POST['StockLocation'] . "' AND StockMoves.StockID = '" . $StockID . "' GROUP BY Periods.PeriodNo, Periods.LastDate_in_Period ORDER BY PeriodNo DESC LIMIT " . $NumberOfPeriodsOfStockUsage;
}
$MovtsResult = DB_query($sql, $db);
if (DB_error_no($db) !=0) {
	echo "The stock usage for the selected criteria could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
}

echo "<TABLE CELLPADDING=2 BORDER=0>";
$tableheader = "<TR><TD class='tableheader'>Month</TD><TD class='tableheader'>Usage</TD></TR>";
echo $tableheader;

$j = 1;
$k=0; //row colour counter

$TotalUsage = 0;
$PeriodsCounter =0;

while ($myrow=DB_fetch_array($MovtsResult)) {

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k++;
	}

	$DisplayDate = MonthAndYearFromSQLDate($myrow["LastDate_in_Period"]);

	$TotalUsage += $myrow["QtyUsed"];
	$PeriodsCounter++;
	printf("<td>%s</td><td ALIGN=RIGHT>%s</td></tr>", $DisplayDate, number_format($myrow["QtyUsed"],2));

	$j++;
	If ($j == 12){
		$j=1;
		echo $tableheader;
	}
//end of page full new headings if
}
//end of while loop

echo "</TABLE>";
if ($TotalUsage>0 && $PeriodsCounter>0){
   echo "<BR>Average Usage per month is " . number_format($TotalUsage/$PeriodsCounter);
}
echo "<HR><A HREF='$rootpath/StockStatus.php?". SID . "StockID=$StockID'>Show Stock Status</A>";
echo "<BR><A HREF='$rootpath/StockMovements.php?". SID . "StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>Show Stock Movements</A>";
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?". SID . "SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>Search Outstanding Sales Orders</A>";
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?". SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A>";
echo "<BR><A HREF='$rootpath/PO_SelectPurchOrder.php?" .SID . "SelectedStockItem=$StockID'>Search Outstanding Purchase Orders</A>";

echo "</form></center>";
include("includes/footer.inc");

?>

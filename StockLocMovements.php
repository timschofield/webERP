<?php
$title = "All Stock Movements By Location";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "  From Stock Location:<SELECT name='StockLocation'> ";

$sql = "SELECT LocCode, LocationName FROM Locations";
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
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

echo "</SELECT><BR>";

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($DefaultDateFormat);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0,0,0,Date("m")-1,Date("d"),Date("y")));
}
echo " Show Movements before: <INPUT TYPE=TEXT NAME='BeforeDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['BeforeDate'] . "'>";
echo " But after: <INPUT TYPE=TEXT NAME='AfterDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['AfterDate'] . "'>";
echo " <INPUT TYPE=SUBMIT NAME='ShowMoves' VALUE='Show Stock Movements'>";
echo "<HR>";


$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

$sql = "SELECT StockMoves.StockID,TypeName, StockMoves.Type, TransNo, TranDate, DebtorNo, BranchCode, Qty, Reference, Price, DiscountPercent, NewQOH FROM StockMoves, SysTypes WHERE StockMoves.Type=SysTypes.TypeID AND StockMoves.LocCode='" . $_POST['StockLocation'] . "' AND StockMoves.TranDate >= '". $SQLAfterDate . "' AND StockMoves.TranDate <= '" . $SQLBeforeDate . "' AND HideMovt=0 ORDER BY StkMoveNo DESC";
$MovtsResult = DB_query($sql, $db);
if (DB_error_no($db) !=0) {
	echo "The stock movements for the selected criteria could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
}

echo "<TABLE CELLPADDING=5 CELLSPACING=4 BORDER=0>";
$tableheader = "<TR>
		<TD class='tableheader'>StockID</TD>
		<TD class='tableheader'>Type</TD>
		<TD class='tableheader'>Trans ID</TD>
		<TD class='tableheader'>Date</TD>
		<TD class='tableheader'>Customer</TD>
		<TD class='tableheader'>Quantity</TD>
		<TD class='tableheader'>Reference</TD>
		<TD class='tableheader'>Price</TD>
		<TD class='tableheader'>Discount</TD>
		</TR>";
echo $tableheader;

$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($MovtsResult)) {

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	$DisplayTranDate = ConvertSQLDate($myrow["TranDate"]);

		/*			 TypeName		StockID	     TransNo				  TranDate			   DebtorNo				BranchCode					    Qty				Reference				     Price					   DiscountPercent		    TypeName			  TransNo	TranDate		  DebtorNo	  BranchCode			Qty		  Reference			Price				  DiscountPercent*/
		printf("<td><a target='_blank' href='StockStatus.php?StockID=%s'>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td></tr>",strtoupper($myrow["StockID"]),strtoupper($myrow["StockID"]), $myrow["TypeName"], $myrow["TransNo"], $DisplayTranDate,$myrow["DebtorNo"], number_format($myrow["Qty"],2), $myrow["Reference"], number_format($myrow["Price"],2), number_format($myrow["DiscountPercent"]*100,2));
	$j++;
	If ($j == 16){
		$j=1;
		echo $tableheader;
	}
//end of page full new headings if
}
//end of while loop

echo "</TABLE><HR>";
echo "</form>";

include("includes/footer.inc");

?>
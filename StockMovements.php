<?php
/* $Revision: 1.3 $ */
$title = "Stock Movements";

$PageSecurity = 2;

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
echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (In units of $myrow[1])</FONT>";

echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo "Stock Code:<input type=text name='StockID' size=21 value='$StockID' maxlength=20>";

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
   $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0,0,0,Date("m")-3,Date("d"),Date("y")));
}
echo " Show Movements before: <INPUT TYPE=TEXT NAME='BeforeDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['BeforeDate'] . "'>";
echo " But after: <INPUT TYPE=TEXT NAME='AfterDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['AfterDate'] . "'>";
echo "     <INPUT TYPE=SUBMIT NAME='ShowMoves' VALUE='Show Stock Movements'>";
echo "<HR>";


$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

$sql = "SELECT StockMoves.StockID,
		SysTypes.TypeName,
		StockMoves.Type,
		StockMoves.TransNo,
		StockMoves.TranDate,
		StockMoves.DebtorNo,
		StockMoves.BranchCode,
		StockMoves.Qty,
		StockMoves.Reference,
		StockMoves.Price,
		StockMoves.DiscountPercent,
		StockMoves.NewQOH,
		StockMaster.DecimalPlaces
	FROM StockMoves
	INNER JOIN SysTypes ON StockMoves.Type=SysTypes.TypeID
	INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
	WHERE  StockMoves.LocCode='" . $_POST['StockLocation'] . "'
	AND StockMoves.TranDate >= '". $SQLAfterDate . "'
	AND StockMoves.StockID = '" . $StockID . "'
	AND StockMoves.TranDate <= '" . $SQLBeforeDate . "'
	AND HideMovt=0
	ORDER BY StkMoveNo DESC";

$MovtsResult = DB_query($sql, $db);
if (DB_error_no($db) !=0) {
	echo "<BR>The stock movements for the selected criteria could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
}

echo "<TABLE CELLPADDING=2 BORDER=0>";
$tableheader = "<TR>
		<TD class='tableheader'>Type</TD><TD class='tableheader'>Number</TD>
		<TD class='tableheader'>Date</TD><TD class='tableheader'>Customer</TD>
		<TD class='tableheader'>Branch</TD><TD class='tableheader'>Quantity</TD>
		<TD class='tableheader'>Reference</TD><TD class='tableheader'>Price</TD>
		<TD class='tableheader'>Discount</TD><TD class='tableheader'>New Qty</TD>
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

	if ($myrow["Type"]==10){ /*its a sales invoice allow link to show invoice it was sold on*/
		/*				      rootpath			     TransNo		     TypeName 			  TransNo			      TranDate			DebtorNo			    BranchCode					 Qty				    Reference 				  Price					DiscountPercent			      TransNo 		     TypeName 		   TransNo	 TranDate		   DebtorNo	   BranchCode 		 Qty		   Reference			 Price 			   DiscountPercent*/
		printf("<td><a target='_blank' href='%s/PrintCustTrans.php?%sFromTransNo=%s&InvOrCredit=Invoice'>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td ALIGN=RIGHT>%s</td>
		<td>%s</td>
		<td ALIGN=RIGHT>%s</td>
		<td ALIGN=RIGHT>%s%%</td>
		<td ALIGN=RIGHT>%s</td>
		</tr>",
		$rootpath,
		SID,
		$myrow["TransNo"],
		$myrow["TypeName"],
		$myrow["TransNo"],
		$DisplayTranDate,
		$myrow["DebtorNo"],
		$myrow["BranchCode"],
		number_format($myrow["Qty"],$myrow['DecimalPlaces']),
		$myrow["Reference"],
		number_format($myrow["Price"],2),
		number_format($myrow["DiscountPercent"]*100,2),
		number_format($myrow['NewQOH'],$myrow['DecimalPlaces']));

	} elseif ($myrow["Type"]==11){

		printf("<td><a target='_blank' href='%s/PrintCustTrans.php?%sFromTransNo=%s&InvOrCredit=Credit'>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td ALIGN=RIGHT>%s</td>
		<td>%s</td>
		<td ALIGN=RIGHT>%s</td>
		<td ALIGN=RIGHT>%s%%</td>
		<td ALIGN=RIGHT>%s</td>
		</tr>",
		$rootpath,
		SID,
		$myrow["TransNo"],
		$myrow["TypeName"],
		$myrow["TransNo"],
		$DisplayTranDate,
		$myrow["DebtorNo"],
		$myrow["BranchCode"],
		number_format($myrow["Qty"],$myrow['DecimalPlaces']),
		$myrow["Reference"],
		number_format($myrow["Price"],2),
		number_format($myrow["DiscountPercent"]*100,2),
		number_format($myrow['NewQOH'],$myrow['DecimalPlaces']));
	} else {

		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s%%</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>",
			$myrow["TypeName"],
			$myrow["TransNo"],
			$DisplayTranDate,
			$myrow["DebtorNo"],
			$myrow["BranchCode"],
			number_format($myrow["Qty"],$myrow['DecimalPlaces']),
			$myrow["Reference"],
			number_format($myrow["Price"],2),
			number_format($myrow["DiscountPercent"]*100,2),
			number_format($myrow['NewQOH'],$myrow['DecimalPlaces']));
	}
	$j++;
	If ($j == 12){
		$j=1;
		echo $tableheader;
	}
//end of page full new headings if
}
//end of while loop

echo "</TABLE><HR>";
echo "<A HREF='$rootpath/StockStatus.php?" . SID . "StockID=$StockID'>Show Stock Status</A>";
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>Show Stock Usage</A>";
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>Search Outstanding Sales Orders</A>";
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A>";


echo "</form>";

include("includes/footer.inc");

?>

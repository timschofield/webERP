<?php
/* $Revision: 1.4 $ */
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Stock Movements');
include('includes/header.inc');
include('includes/DateFunctions.inc');


if (isset($_GET['StockID'])){
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
}

$result = DB_query("SELECT Description, Units FROM StockMaster WHERE StockID='$StockID'",$db);
$myrow = DB_fetch_row($result);
echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (" . _('In units of') . " $myrow[1])</FONT>";

echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo _('Stock Code') . ":<INPUT TYPE=TEXT NAME='StockID' SIZE=21 VALUE='$StockID' MAXLENGTH=20>";

echo '  ' . _('From Stock Location') . ":<SELECT NAME='StockLocation'> ";

$sql = 'SELECT LocCode, LocationName FROM Locations';
$resultStkLocs = DB_query($sql,$db);

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['LocCode'] == $_POST['StockLocation']){
		     echo "<OPTION SELECTED VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
		} else {
		     echo "<OPTION VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
		}
	} elseif ($myrow['LocCode']==$_SESSION['UserStockLocation']){
		 echo "<OPTION SELECTED VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
		 $_POST['StockLocation']=$myrow['LocCode'];
	} else {
		 echo "<OPTION VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
	}
}

echo '</SELECT><BR>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($DefaultDateFormat);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0,0,0,Date("m")-3,Date("d"),Date("y")));
}
echo ' ' . _('Show Movements before') . ": <INPUT TYPE=TEXT NAME='BeforeDate' SIZE=12 MAXLENGTH=12 VALUE='" . $_POST['BeforeDate'] . "'>";
echo ' ' . _('But after') . ": <INPUT TYPE=TEXT NAME='AfterDate' SIZE=12 MAXLENGTH=12 VALUE='" . $_POST['AfterDate'] . "'>";
echo "     <INPUT TYPE=SUBMIT NAME='ShowMoves' VALUE='" . _('Show Stock Movements') . "'>";
echo '<HR>';

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

$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
$DbgMsg = _('The SQL that failed was') . ' ';

$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo '<TABLE CELLPADDING=2 BORDER=0>';
$tableheader = "<TR>
		<TD CLASS='tableheader'>" . _('Type') . "</TD><TD CLASS='tableheader'>" . _('Number') . "</TD>
		<TD CLASS='tableheader'>" . _('Date') . "</TD><TD CLASS='tableheader'>" . _('Customer') . "</TD>
		<TD CLASS='tableheader'>" . _('Branch') . "</TD><TD CLASS='tableheader'>" . _('Quantity') . "</TD>
		<TD CLASS='tableheader'>" . _('Reference') . "</TD><TD CLASS='tableheader'>" . _('Price') . "</TD>
		<TD CLASS='tableheader'>" . _('Discount') . "</TD><TD CLASS='tableheader'>" . _('New Qty') . "</TD>
		</TR>";

echo $tableheader;

$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($MovtsResult)) {

	if ($k==1){
		echo "<TR BGCOLOR='#CCCCCC'>";
		$k=0;
	} else {
		echo "<TR BGCOLOR='#EEEEEE'>";
		$k=1;
	}

	$DisplayTranDate = ConvertSQLDate($myrow['TranDate']);

	if ($myrow['Type']==10){ /*its a sales invoice allow link to show invoice it was sold on*/

		printf("<TD><A TARGET='_blank' HREF='%s/PrintCustTrans.php?%s&FromTransNo=%s&InvOrCredit=Invoice'>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		<TD ALIGN=RIGHT>%s%%</TD>
		<TD ALIGN=RIGHT>%s</TD>
		</TR>",
		$rootpath,
		SID,
		$myrow['TransNo'],
		$myrow['TypeName'],
		$myrow['TransNo'],
		$DisplayTranDate,
		$myrow['DebtorNo'],
		$myrow['BranchCode'],
		number_format($myrow['Qty'],$myrow['DecimalPlaces']),
		$myrow['Reference'],
		number_format($myrow['Price'],2),
		number_format($myrow['DiscountPercent']*100,2),
		number_format($myrow['NewQOH'],$myrow['DecimalPlaces']));

	} elseif ($myrow['Type']==11){

		printf("<TD><A TARGET='_blank' HREF='%s/PrintCustTrans.php?%s&FromTransNo=%s&InvOrCredit=Credit'>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		<TD ALIGN=RIGHT>%s%%</TD>
		<TD ALIGN=RIGHT>%s</TD>
		</TR>",
		$rootpath,
		SID,
		$myrow['TransNo'],
		$myrow['TypeName'],
		$myrow['TransNo'],
		$DisplayTranDate,
		$myrow['DebtorNo'],
		$myrow['BranchCode'],
		number_format($myrow['Qty'],$myrow['DecimalPlaces']),
		$myrow['Reference'],
		number_format($myrow['Price'],2),
		number_format($myrow['DiscountPercent']*100,2),
		number_format($myrow['NewQOH'],$myrow['DecimalPlaces']));
	} else {

		printf("<TD>%s</TD>
			<TD>%s</TD>
			<TD>%s</TD>
			<TD>%s</TD>
			<TD>%s</TD>
			<TD ALIGN=RIGHT>%s</TD>
			<TD>%s</TD>
			<TD ALIGN=RIGHT>%s</TD>
			<TD ALIGN=RIGHT>%s%%</TD>
			<TD ALIGN=RIGHT>%s</TD>
			</TR>",
			$myrow['TypeName'],
			$myrow['TransNo'],
			$DisplayTranDate,
			$myrow['DebtorNo'],
			$myrow['BranchCode'],
			number_format($myrow['Qty'],$myrow['DecimalPlaces']),
			$myrow['Reference'],
			number_format($myrow['Price'],2),
			number_format($myrow['DiscountPercent']*100,2),
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

echo '</TABLE><HR>';
echo "<A HREF='$rootpath/StockStatus.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Status') . '</A>';
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Show Stock Usage') . '</A>';
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "&SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Search Outstanding Sales Orders') . '</A>';
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Completed Sales Orders') . '</A>';

echo '</FORM>';

include('includes/footer.inc');

?>
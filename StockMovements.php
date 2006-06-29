<?php

/* $Revision: 1.9 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Stock Movements');
include('includes/header.inc');


if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}


echo "<A HREF='" . $rootpath . '/SelectProduct.php?' . SID . "'>" .  _('Back to Items') . '</A><BR>';

$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='$StockID'",$db);
$myrow = DB_fetch_row($result);
echo "<CENTER><BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (" . _('In units of') . " $myrow[1])</FONT>";

echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo _('Stock Code') . ":<INPUT TYPE=TEXT NAME='StockID' SIZE=21 VALUE='$StockID' MAXLENGTH=20>";

echo '  ' . _('From Stock Location') . ":<SELECT NAME='StockLocation'> ";

$sql = 'SELECT loccode, locationname FROM locations';
$resultStkLocs = DB_query($sql,$db);

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo "<OPTION SELECTED VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
		     echo "<OPTION VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo "<OPTION SELECTED VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo "<OPTION VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	}
}

echo '</SELECT><BR>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date("m")-3,Date("d"),Date("y")));
}
echo ' ' . _('Show Movements before') . ": <INPUT TYPE=TEXT NAME='BeforeDate' SIZE=12 MAXLENGTH=12 VALUE='" . $_POST['BeforeDate'] . "'>";
echo ' ' . _('But after') . ": <INPUT TYPE=TEXT NAME='AfterDate' SIZE=12 MAXLENGTH=12 VALUE='" . $_POST['AfterDate'] . "'>";
echo "     <INPUT TYPE=SUBMIT NAME='ShowMoves' VALUE='" . _('Show Stock Movements') . "'>";
echo '<HR>';

$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

$sql = "SELECT stockmoves.stockid,
		systypes.typename,
		stockmoves.type,
		stockmoves.transno,
		stockmoves.trandate,
		stockmoves.debtorno,
		stockmoves.branchcode,
		stockmoves.qty,
		stockmoves.reference,
		stockmoves.price,
		stockmoves.discountpercent,
		stockmoves.newqoh,
		stockmaster.decimalplaces
	FROM stockmoves
	INNER JOIN systypes ON stockmoves.type=systypes.typeid
	INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
	WHERE  stockmoves.loccode='" . $_POST['StockLocation'] . "'
	AND stockmoves.trandate >= '". $SQLAfterDate . "'
	AND stockmoves.stockid = '" . $StockID . "'
	AND stockmoves.trandate <= '" . $SQLBeforeDate . "'
	AND hidemovt=0
	ORDER BY stkmoveno DESC";

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

	$DisplayTranDate = ConvertSQLDate($myrow['trandate']);

	if ($myrow['type']==10){ /*its a sales invoice allow link to show invoice it was sold on*/

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
		$myrow['transno'],
		$myrow['typename'],
		$myrow['transno'],
		$DisplayTranDate,
		$myrow['debtorno'],
		$myrow['branchcode'],
		number_format($myrow['qty'],
		$myrow['decimalplaces']),
		$myrow['reference'],
		number_format($myrow['price'],2),
		number_format($myrow['discountpercent']*100,2),
		number_format($myrow['newqoh'],$myrow['decimalplaces']));

	} elseif ($myrow['type']==11){

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
		$myrow['transno'],
		$myrow['typename'],
		$myrow['transno'],
		$DisplayTranDate,
		$myrow['debtorno'],
		$myrow['branchcode'],
		number_format($myrow['qty'],$myrow['decimalplaces']),
		$myrow['reference'],
		number_format($myrow['price'],2),
		number_format($myrow['discountpercent']*100,2),
		number_format($myrow['newqoh'],$myrow['decimalplaces']));
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
			$myrow['typename'],
			$myrow['transno'],
			$DisplayTranDate,
			$myrow['debtorno'],
			$myrow['branchcode'],
			number_format($myrow['qty'],$myrow['decimalplaces']),
			$myrow['reference'],
			number_format($myrow['price'],2),
			number_format($myrow['discountpercent']*100,2),
			number_format($myrow['newqoh'],$myrow['decimalplaces']));
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

echo '</FORM></CENTER>';

include('includes/footer.inc');

?>
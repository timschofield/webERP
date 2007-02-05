<?php

$PageSecurity = 2;

include('includes/session.inc');

$title = _('All Stock Movements By Location');

include('includes/header.inc');

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

echo '  ' . _('From Stock Location') . ':<SELECT name="StockLocation"> ';

$sql = 'SELECT loccode, locationname FROM locations';
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
		     echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
}

echo '</SELECT><BR>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')-1,Date('d'),Date('y')));
}
echo ' ' . _('Show Movements before') . ': <INPUT TYPE=TEXT NAME="BeforeDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['BeforeDate'] . '">';
echo ' ' . _('But after') . ': <INPUT TYPE=TEXT NAME="AfterDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['AfterDate'] . '">';
echo ' <INPUT TYPE=SUBMIT NAME="ShowMoves" VALUE="' . _('Show Stock Movements') . '">';
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
	AND stockmoves.trandate <= '" . $SQLBeforeDate . "'
	AND hidemovt=0
	ORDER BY stkmoveno DESC";

$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because');
$MovtsResult = DB_query($sql, $db,$ErrMsg);

echo '<TABLE CELLPADDING=5 CELLSPACING=4 BORDER=0>';
$tableheader = '<TR>
		<TD class="tableheader">' . _('Item Code') . '</TD>
		<TD class="tableheader">' . _('Type') . '</TD>
		<TD class="tableheader">' . _('Trans No') . '</TD>
		<TD class="tableheader">' . _('Date') . '</TD>
		<TD class="tableheader">' . _('Customer') . '</TD>
		<TD class="tableheader">' . _('Quantity') . '</TD>
		<TD class="tableheader">' . _('Reference') . '</TD>
		<TD class="tableheader">' . _('Price') . '</TD>
		<TD class="tableheader">' . _('Discount') . '</TD>
		</TR>';
echo $tableheader;

$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($MovtsResult)) {

	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k=1;
	}

	$DisplayTranDate = ConvertSQLDate($myrow['trandate']);


		printf("<td><a target='_blank' href='StockStatus.php?" . SID . "&StockID=%s'>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>",
			strtoupper($myrow['stockid']),
			strtoupper($myrow['stockid']),
			$myrow['typename'],
			$myrow['transno'],
			$DisplayTranDate,
			$myrow['debtorno'],
			number_format($myrow['qty'],
			$myrow['decimalplaces']),
			$myrow['reference'],
			number_format($myrow['price'],2),
			number_format($myrow['discountpercent']*100,2));
	$j++;
	If ($j == 16){
		$j=1;
		echo $tableheader;
	}
//end of page full new headings if
}
//end of while loop

echo '</TABLE><HR>';
echo '</form>';

include('includes/footer.inc');

?>
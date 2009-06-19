<?php

$PageSecurity = 2;

include('includes/session.inc');

$title = _('All Stock Movements By Location');

include('includes/header.inc');

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

echo '  ' . _('From Stock Location') . ':<select name="StockLocation"> ';

$sql = 'SELECT loccode, locationname FROM locations';
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
		     echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
}

echo '</select><br>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')-1,Date('d'),Date('y')));
}
echo ' ' . _('Show Movements before') . ': <input type=TEXT name="BeforeDate" size=12 maxlength=12 Value="' . $_POST['BeforeDate'] . '">';
echo ' ' . _('But after') . ': <input type=TEXT name="AfterDate" size=12 maxlength=12 Value="' . $_POST['AfterDate'] . '">';
echo ' <input type=submit name="ShowMoves" VALUE="' . _('Show Stock Movements') . '">';
echo '<hr>';


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

echo '<table cellpadding=5 CELLSPACING=4 BORDER=0>';
$tableheader = '<tr>
		<th>' . _('Item Code') . '</th>
		<th>' . _('Type') . '</th>
		<th>' . _('Trans No') . '</th>
		<th>' . _('Date') . '</th>
		<th>' . _('Customer') . '</th>
		<th>' . _('Quantity') . '</th>
		<th>' . _('Reference') . '</th>
		<th>' . _('Price') . '</th>
		<th>' . _('Discount') . '</th>
		</tr>';
echo $tableheader;

$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($MovtsResult)) {

	if ($k==1){
		echo '<tr class="OddTableRows">';
		$k=0;
	} else {
		echo '<tr class="EvenTableRows">';
		$k=1;
	}

	$DisplayTranDate = ConvertSQLDate($myrow['trandate']);


		printf("<td><a target='_blank' href='StockStatus.php?" . SID . "&StockID=%s'>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td align=right>%s</td>
			<td>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>
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

echo '</table><hr>';
echo '</form>';

include('includes/footer.inc');

?>
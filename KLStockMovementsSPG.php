<?php

include('includes/session.inc');
$Title = _('Stock Movements for SPG');
/* webERP manual links before header.inc */
$ViewTopic= "Inventory";
$BookMark = "InventoryMovement";
include('includes/header.inc');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} else {
	$StockID = '';
}

if (isset($_GET['Location'])){
	$Location = trim(mb_strtoupper($_GET['Location']));
} else {
	$Location = '';
}
$SQL="SELECT locationname FROM locations WHERE loccode='" . $Location . "'";
$result = DB_query($SQL,$ErrMsg);
$Row = DB_fetch_row($result);
$LocationName = $Row['0'];

$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='".$StockID."'");
$myrow = DB_fetch_row($result);

$Today  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-STOCK_MOVEMENT_DAYS_FOR_SPG));

$sql = "SELECT stockmoves.stockid,
				systypes.typename,
				stockmoves.type,
				stockmoves.transno,
				stockmoves.trandate,
				stockmoves.userid,
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
		WHERE  stockmoves.loccode='" . $Location . "'
		AND stockmoves.trandate >= '". $StartDate . "'
		AND stockmoves.stockid = '" . $StockID . "'
		AND stockmoves.trandate <= '" . $Today . "'
		AND hidemovt=0
		ORDER BY stkmoveno DESC";

$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
$DbgMsg = _('The SQL that failed was') . ' ';

$MovtsResult = DB_query($sql, $ErrMsg, $DbgMsg);

echo '<p class="page_title_text" align="center"><strong>' . _('Movements of ') . $StockID . " at " . $LocationName . " for the last " . STOCK_MOVEMENT_DAYS_FOR_SPG . " days" .'</strong></p>';
echo '<div>';
echo '<table class="selection">';
$tableheader = '<tr>
					<th>' . _('Date') . '</th>
					<th>' . _('User') . '</th>
					<th>' . _('Type') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Reference') . '</th>
					<th>' . _('Quantity') . '</th>
					<th>' . _('New Qty') . '</th>
				</tr>';

echo $tableheader;

$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($MovtsResult)) {
	$k = StartEvenOrOddRow($k);

	$DisplayTranDate = ConvertSQLDate($myrow['trandate']);

	if ($myrow['type']==10){ /*its a sales invoice allow link to show invoice it was sold on*/

		$InvoiceLink = '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $myrow['transno'] . '&amp;InvOrCredit=Invoice">' . $myrow['typename'] . '</a>';

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>',
				$DisplayTranDate,
				$myrow['userid'],
				$InvoiceLink,
				$myrow['transno'],
				'',
				locale_number_format($myrow['qty'],$myrow['decimalplaces']),
				locale_number_format($myrow['newqoh'],$myrow['decimalplaces']));

	} elseif ($myrow['type']==11){

		printf('<td>%s</td>
				<td>%s</td>
				<td><a target="_blank" href="%s/PrintCustTrans.php?FromTransNo=%s&amp;InvOrCredit=Credit">%s</a></td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>',
				$DisplayTranDate,
				$myrow['userid'],
				$RootPath,
				$myrow['transno'],
				$myrow['typename'],
				$myrow['transno'],
				'',
				locale_number_format($myrow['qty'],$myrow['decimalplaces']),
				locale_number_format($myrow['newqoh'],$myrow['decimalplaces']));
	} else {

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>',
				$DisplayTranDate,
				$myrow['userid'],
				$myrow['typename'],
				$myrow['transno'],
				$myrow['reference'],
				locale_number_format($myrow['qty'],$myrow['decimalplaces']),
				locale_number_format($myrow['newqoh'],$myrow['decimalplaces']));
	}
//end of page full new headings if
}
//end of while loop

echo '</table>
		</div>
		</form>';

include('includes/footer.inc');

?>

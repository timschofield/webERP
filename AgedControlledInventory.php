<?php


include('includes/session.php');
$PricesSecurity = 12;//don't show pricing info unless security token 12 available to user

$Today =  time();
$Title = _('Aged Controlled Inventory') . ' ' . _('as-of') . ' ' . Date(($_SESSION['DefaultDateFormat']), $Today);

include('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $Theme, '/images/inventory.png" title="', _('Inventory'), '" alt="" /><b>', $Title, '</b>
	</p>';

$sql = "SELECT stockserialitems.stockid,
				stockmaster.description,
				stockserialitems.serialno,
				stockserialitems.quantity,
				stockmoves.trandate,
				stockmaster.units,
				stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS cost,
				createdate,
				decimalplaces
			FROM stockserialitems
			LEFT JOIN stockserialmoves
				ON stockserialitems.serialno=stockserialmoves.serialno
			LEFT JOIN stockmoves
				ON stockserialmoves.stockmoveno=stockmoves.stkmoveno
			INNER JOIN stockmaster
				ON stockmaster.stockid = stockserialitems.stockid
			INNER JOIN locationusers
				ON locationusers.loccode=stockserialitems.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE quantity > 0
			ORDER BY createdate, quantity";

$ErrMsg =  _('The stock held could not be retrieved because');
$LocStockResult = DB_query($sql, $ErrMsg);
$NumRows = DB_num_rows($LocStockResult);

$TotalQty=0;
$TotalVal=0;

echo '<table>
		<thead>
		<tr>
			<th class="ascending">', _('Stock'), '</th>
			<th class="ascending">', _('Description'), '</th>
			<th class="ascending">', _('Batch'), '</th>
			<th class="ascending">', _('Quantity Remaining'), '</th>
			<th class="ascending">', _('Units'), '</th>
			<th class="ascending">', _('Inventory Value'), '</th>
			<th class="ascending">', _('Date'), '</th>
			<th class="ascending">', _('Days Old'), '</th>
			</tr>
		</thead>
		<tbody>';

while ($LocQtyRow=DB_fetch_array($LocStockResult)) {

	$DaysOld = floor(($Today - strtotime($LocQtyRow['createdate']))/(60*60*24));
	$TotalQty += $LocQtyRow['quantity'];
	$DispVal =  '-----------';

	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
		$DispVal = locale_number_format(($LocQtyRow['quantity']*$LocQtyRow['cost']),$LocQtyRow['decimalplaces']);
		$TotalVal += ($LocQtyRow['quantity'] * $LocQtyRow['cost']);
	}

	printf('<tr class="striped_row">
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td>%s</td>
			<td class="number">%s</td>
		</tr>',
			mb_strtoupper($LocQtyRow['stockid']),
			$LocQtyRow['description'],
			$LocQtyRow['serialno'],
			locale_number_format($LocQtyRow['quantity'],$LocQtyRow['decimalplaces']),
			$LocQtyRow['units'],
			$DispVal,
			ConvertSQLDate($LocQtyRow['createdate']),
			$DaysOld
		);
} //while

echo '</tbody>
		<tfoot>
			<tr class="striped_row">
				<td colspan="3"><b>', _('Total'), '</b></td>
				<td class="number"><b>', locale_number_format($TotalQty,2), '</b></td>
				<td class="number"><b>', locale_number_format($TotalVal,2), '</b></td>
      <td colspan="2"></td>
			</tr>
		</tfoot>
	</table>';

include('includes/footer.php');
?>
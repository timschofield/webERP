<?php

require(__DIR__ . '/includes/session.php');

$PricesSecurity = 12; // don't show pricing info unless security token 12 available to user

$Today =  time();
$Title = __('Aged Controlled Inventory') . ' ' . __('as-of') . ' ' . Date(($_SESSION['DefaultDateFormat']), $Today);
$ViewTopic = 'Inventory';
$BookMark = 'AgedControlled';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $Theme, '/images/inventory.png" title="', __('Inventory'), '" alt="" /><b>', $Title, '</b>
	</p>';

$SQL = "SELECT stockserialitems.stockid,
				stockmaster.description,
				stockserialitems.serialno,
				stockserialitems.quantity,
				stockmoves.trandate,
				stockmaster.units,
				stockmaster.actualcost AS cost,
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

$ErrMsg =  __('The stock held could not be retrieved because');
$LocStockResult = DB_query($SQL, $ErrMsg);
$NumRows = DB_num_rows($LocStockResult);

$TotalQty=0;
$TotalVal=0;

echo '<table>
		<thead>
		<tr>
			<th class="SortedColumn">', __('Stock'), '</th>
			<th class="SortedColumn">', __('Description'), '</th>
			<th class="SortedColumn">', __('Batch'), '</th>
			<th class="SortedColumn">', __('Quantity Remaining'), '</th>
			<th class="SortedColumn">', __('Units'), '</th>
			<th class="SortedColumn">', __('Inventory Value'), '</th>
			<th class="SortedColumn">', __('Date'), '</th>
			<th class="SortedColumn">', __('Days Old'), '</th>
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

	echo '<tr class="striped_row">
			<td>', mb_strtoupper($LocQtyRow['stockid']), '</td>
			<td>', $LocQtyRow['description'], '</td>
			<td>', $LocQtyRow['serialno'], '</td>
			<td class="number">', locale_number_format($LocQtyRow['quantity'],$LocQtyRow['decimalplaces']), '</td>
			<td>', $LocQtyRow['units'], '</td>
			<td class="number">', $DispVal, '</td>
			<td class="date">', ConvertSQLDate($LocQtyRow['createdate']), '</td>
			<td class="number">', $DaysOld, '</td>
		</tr>';
} //while

echo '</tbody>
		<tfoot>
			<tr class="total_row">
				<td colspan="3"><b>', __('Total'), '</b></td>
				<td class="number"><b>', locale_number_format($TotalQty,2), '</b></td>
				<td class="number"><b>', locale_number_format($TotalVal,2), '</b></td>
				<td colspan="3"></td>
			</tr>
		</tfoot>
	</table>';

include('includes/footer.php');

<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Shop Inventory Control for SPG');
include('includes/header.php');

include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLDefines.php');

$LocationName = GetLocationNameFromCode($_SESSION['UserStockLocation']);

$SQL = "SELECT locstock.stockid, 
				stockmaster.description,
				locstock.quantity
		FROM locstock, stockmaster
		WHERE locstock.stockid = stockmaster.stockid
			AND locstock.loccode = '". $_SESSION['UserStockLocation'] ."'
			AND locstock.quantity > 0
		ORDER BY locstock.stockid";
$Result = DB_query($SQL);

$TableTitleText = __('Stock Available at ') . $LocationName;
echo ShowTableTitle($TableTitleText);

echo '<table class="selection">
		<thead>';
$TableHeader = '<tr>
					<th class="SortedColumn">' . '#' . '</th>
					<th class="SortedColumn">' . __('Code') . '</th>
					<th class="SortedColumn">' . __('Description') . '</th>
					<th class="SortedColumn">' . __('QOH') . '</th>
				</tr>';
echo $TableHeader;
echo '</thead>
	<tbody>';
$i = 1;
while ($MyRow = DB_fetch_array($Result)) {
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $MyRow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $MyRow['stockid'] . '</a>';
	echo '<tr class="striped_row">
			<td class="number">' . locale_number_format($i,0) . '</td>
			<td>' . $CodeLink . '</td>
			<td>' . $MyRow['description'] . '</td>
			<td class="number">' . locale_number_format($MyRow['quantity'],0) . '</td>
			</tr>';
	$i++;
}
echo '</tbody>
	</table>';

include('includes/footer.php');

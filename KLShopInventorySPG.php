<?php
include ('includes/session.php');
$Title = _('Shop Inventory Control for SPG');
include ('includes/header.php');
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

$TableTitleText = _('Stock Available at ') . $LocationName;
echo ShowTableTitle($TableTitleText);

echo '<table class="selection">
		<thead>';
$TableHeader = '<tr>
					<th class="SortedColumn">' . '#' . '</th>
					<th class="SortedColumn">' . _('Code') . '</th>
					<th class="SortedColumn">' . _('Description') . '</th>
					<th class="SortedColumn">' . _('QOH') . '</th>
				</tr>';
echo $TableHeader;
echo '</thead>
	<tbody>';
$i = 1;
while ($MyRow = DB_fetch_array($Result)) {
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $MyRow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $MyRow['stockid'] . '</a>';
	printf('<tr class="striped_row">
			<td class="number">%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			locale_number_format($i,0),
			$CodeLink, 
			$MyRow['description'], 
			locale_number_format($MyRow['quantity'],0)
			);
	$i++;
}
echo '</tbody>
	</table>
	</div>
	</form>';
echo '<br />';

include ('includes/footer.php');
?>
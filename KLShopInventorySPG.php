<?php
include ('includes/session.php');
$Title = _('Shop Inventory Control for SPG');
include ('includes/header.php');
include('includes/KLGeneralFunctions.php');
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
$result = DB_query($SQL);

echo '<p class="page_title_text" align="center"><strong>' . _('Stock Available at ') . $LocationName  . '</strong></p>';
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
while ($myrow = DB_fetch_array($result)) {
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $myrow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $myrow['stockid'] . '</a>';
	printf('<tr class="striped_row">
			<td class="number">%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			locale_number_format($i,0),
			$CodeLink, 
			$myrow['description'], 
			locale_number_format($myrow['quantity'],0)
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
<?php
include ('includes/session.inc');
$Title = _('Shop Inventory Control for SPG');
include ('includes/header.inc');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');

$SQL="SELECT locationname FROM locations WHERE loccode='" . $_SESSION['UserStockLocation'] . "'";
$result = DB_query($SQL,$ErrMsg);
$Row = DB_fetch_row($result);
$LocationName = $Row['0'];

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
echo '<table class="selection">';
$TableHeader = '<tr>
					<th class="ascending">' . '#' . '</th>
					<th class="ascending">' . _('Code') . '</th>
					<th class="ascending">' . _('Description') . '</th>
					<th class="ascending">' . _('QOH') . '</th>
				</tr>';
echo $TableHeader;
$k = 0; //row colour counter
$i = 1;
while ($myrow = DB_fetch_array($result)) {
	$k = StartEvenOrOddRow($k);
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $myrow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $myrow['stockid'] . '</a>';
	printf('<td class="number">%s</td>
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
echo '</table>';
echo '<br />';

include ('includes/footer.inc');
?>
<?php
include ('includes/session.php');
$Title = _('List of Items Sold in Shop');
include ('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');

$LocationName = GetLocationNameFromCode($_SESSION['UserStockLocation']);
$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-TRANSFER_LIST_DAYS_FOR_SPG));

$SQL = "SELECT salesorders.orddate, 
				salesorderdetails.stkcode, 
				SUM(salesorderdetails.qtyinvoiced) AS sold
		FROM salesorders, salesorderdetails
		WHERE salesorders.orderno = salesorderdetails.orderno
			AND  salesorders.fromstkloc = '". $_SESSION['UserStockLocation'] ."'
			AND  salesorders.orddate >='". $StartDate ."'
		GROUP BY salesorders.orddate, salesorderdetails.stkcode
		ORDER BY salesorders.orddate DESC, salesorderdetails.stkcode";
				
$result = DB_query($SQL);

echo '<p class="page_title_text" align="center"><strong>' . 'Items sold in ' . $LocationName  . ' in the last ' . TRANSFER_LIST_DAYS_FOR_SPG .' days</strong></p>';
echo '<table class="selection">
		<thead>';
$TableHeader = '<tr>
					<th class="SortedColumn">' . _('Date') . '</th>
					<th class="SortedColumn">' . _('Item') . '</th>
					<th class="SortedColumn">' . _('Sold') . '</th>
				</tr>';
echo $TableHeader;
echo '</thead>
	<tbody>';

while ($myrow = DB_fetch_array($result)) {
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $myrow['stkcode'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $myrow['stkcode'] . '</a>';
	printf('<tr class="striped_row">
		<td>%s</td>
		<td>%s</td>
		<td class="number">%s</td>
		</tr>', 
		ConvertSQLDate($myrow['orddate']),
		$CodeLink, 
		locale_number_format($myrow['sold'],0)
	);
}

echo '</tbody>
	</table>
	</div>
	</form>';

include ('includes/footer.php');
?>
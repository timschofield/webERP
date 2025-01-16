<?php
include ('includes/session.php');
$Title = _('List of Items Sold in Shop');
include ('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLUIGeneralFunctions.php');

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
				
$Result = DB_query($SQL);

$TableTitleText = 'Items sold in ' . $LocationName  . ' in the last ' . TRANSFER_LIST_DAYS_FOR_SPG .' days';
ShowTableTitle($TableTitleText);

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

while ($MyRow = DB_fetch_array($Result)) {
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $MyRow['stkcode'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $MyRow['stkcode'] . '</a>';
	printf('<tr class="striped_row">
		<td>%s</td>
		<td>%s</td>
		<td class="number">%s</td>
		</tr>', 
		ConvertSQLDate($MyRow['orddate']),
		$CodeLink, 
		locale_number_format($MyRow['sold'],0)
	);
}

echo '</tbody>
	</table>
	</div>
	</form>';

include ('includes/footer.php');
?>
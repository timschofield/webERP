<?php
include ('includes/session.php');
$Title = _('List if Items Sold in Shop');
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
echo '<table class="selection">';
$TableHeader = '<tr>
					<th class="ascending">' . _('Date') . '</th>
					<th class="ascending">' . _('Item') . '</th>
					<th class="ascending">' . _('Sold') . '</th>
				</tr>';
echo $TableHeader;
$k = 0; //row colour counter
$i = 1;
$DateOrder = '0000-00-00';
while ($myrow = DB_fetch_array($result)) {
	$k = StartEvenOrOddRow($k);
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $myrow['stkcode'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $myrow['stkcode'] . '</a>';
	if ($DateOrder != $myrow['orddate']){
		// The first item of the day
		$DateOrder = $myrow['orddate'];
		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				ConvertSQLDate($myrow['orddate']),
				$CodeLink, 
				locale_number_format($myrow['sold'],0)
				);
	}else{
		// the other items of the day
		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'',
				$CodeLink, 
				locale_number_format($myrow['sold'],0)
				);
	}
	$i++;
}
echo '</table>';
echo '<br />';

include ('includes/footer.php');
?>
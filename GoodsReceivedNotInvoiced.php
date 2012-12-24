<?php

/* Session started in session.inc for password checking and authorisation level check config.php is in turn included in session.inc*/

include ('includes/session.inc');
$title = _('Goods Received But Not Invoiced Yet');
include ('includes/header.inc');

$SQL = "SELECT grns.supplierid,
				purchorderdetails.orderno,
				grns.itemcode,
				grns.qtyrecd,
				grns.quantityinv,
				purchorderdetails.unitprice,
				suppliers.currcode,
				currencies.rate,
				currencies.decimalplaces
		FROM grns INNER JOIN purchorderdetails
		ON grns.podetailitem=purchorderdetails.podetailitem
		INNER JOIN suppliers
		ON grns.supplierid = suppliers.supplierid
		INNER JOIN currencies
		ON suppliers.currcode = currencies.currabrev
		WHERE grns.qtyrecd - grns.quantityinv > 0
		ORDER BY grns.supplierid,
			purchorderdetails.orderno,
			grns.itemcode";
$result = DB_query($SQL, $db);

if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Goods Received but not invoiced Yet') . '</strong></p>';

	echo '<div class="page_help_text">'
	. _('Shows the list of Goods Received Not Yet Invoiced, both in supplier currency and home currency.'). '<br />'
	. _('Total in home currency calculated at Purchasing Price converted at current exchange rate, not Standard cost!'). '<br />'
	. '</div>';

	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('Supplier') . '</th>
						<th>' . _('PO#') . '</th>
						<th>' . _('Item Code') . '</th>
						<th>' . _('Qty Received') . '</th>
						<th>' . _('Qty Invoiced') . '</th>
						<th>' . _('Qty Pending') . '</th>
						<th>' . _('Unit Price') . '</th>
						<th>' .'' . '</th>
						<th>' . _('Line Total') . '</th>
						<th>' . '' . '</th>
						<th>' . _('Line Total') . '</th>
						<th>' . '' . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	$TotalHomeCurrency = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$QtyPending = $myrow['qtyrecd'] - $myrow['quantityinv'];
		$TotalHomeCurrency = $TotalHomeCurrency + ($QtyPending * $myrow['unitprice'] / $myrow['rate']);
		printf('<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>', 
				$myrow['supplierid'], 
				$myrow['orderno'], 
				$myrow['itemcode'], 
				$myrow['qtyrecd'], 
				$myrow['quantityinv'], 
				$QtyPending, 
				locale_number_format($myrow['unitprice'],$myrow['decimalplaces']), 
				$myrow['currcode'], 
				locale_number_format(($QtyPending * $myrow['unitprice']),$myrow['decimalplaces']), 
				$myrow['currcode'], 
				locale_number_format(($QtyPending * $myrow['unitprice'] / $myrow['rate']),$_SESSION['CompanyRecord']['decimalplaces']),
				$_SESSION['CompanyRecord']['currencydefault']);

		if ($i==15){
			$i=0;
			echo $TableHeader;
		} else {
			$i++;
		}
	}
	printf('<td colspan="10">%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td>%s</td>
			</tr>', 
			_('Total').':', 
			locale_number_format($TotalHomeCurrency,$_SESSION['CompanyRecord']['decimalplaces']),
			$_SESSION['CompanyRecord']['currencydefault']);
	
	echo '</table>
			</div>
			</form>';
}

include ('includes/footer.inc');

?>
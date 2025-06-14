<?php

include('includes/session.php');
$Title = _('Goods Received But Not Invoiced Yet');
include('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$SQL = "SELECT grns.supplierid,
				grns.deliverydate,
				purchorderdetails.orderno,
				grns.itemcode,
				grns.qtyrecd,
				grns.quantityinv,
				purchorderdetails.unitprice,
				suppliers.currcode,
				stockmaster.actualcost AS standardcost,
				currencies.rate,
				currencies.decimalplaces
		FROM grns 
		INNER JOIN purchorderdetails
			ON grns.podetailitem = purchorderdetails.podetailitem
		INNER JOIN suppliers
			ON grns.supplierid = suppliers.supplierid
		INNER JOIN stockmaster
			ON grns.itemcode = stockmaster.stockid
		INNER JOIN currencies
			ON suppliers.currcode = currencies.currabrev
		WHERE grns.qtyrecd > grns.quantityinv
		ORDER BY grns.supplierid,
			purchorderdetails.orderno,
			grns.itemcode";
$Result = DB_query($SQL);

if (DB_num_rows($Result) != 0) {
	$TableTitleText = _('Goods Received but not invoiced Yet');
	ShowTableTitle($TableTitleText);

	echo '<div class="page_help_text">'
		. _('Shows the list of Goods Received Not Yet Invoiced, both in supplier currency and home currency.') . '<br />'
		. _('Total in home currency calculated at Purchasing Price converted at current exchange rate, not Standard cost!') . '<br />'
		. '</div>';

	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('Supplier') . '</th>
							<th class="SortedColumn">' . _('PO#') . '</th>
							<th class="SortedColumn">' . _('Item Code') . '</th>
							<th>' . _('Date') . '</th>
							<th>' . _('Qty Received') . '</th>
							<th>' . _('Qty Invoiced') . '</th>
							<th>' . _('Qty Pending') . '</th>
							<th>' . _('Unit Price') . '</th>
							<th>' . '' . '</th>
							<th>' . _('Line Total') . '</th>
							<th>' . '' . '</th>
							<th>' . _('IDR Total') . '</th>
							<th>' . '' . '</th>
							<th>' . _('Std Cost') . '</th>
							<th>' . _('Std Cost Total') . '</th>
							<th>' . '' . '</th>
						</tr>
					</thead>
					<tbody>';
	echo $TableHeader;
	$TotalHomeCurrency = 0;
	$TotalAtStandardCost = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		$QtyPending = $MyRow['qtyrecd'] - $MyRow['quantityinv'];
		$Rate = $MyRow['rate'] == 0 ? 1 : $MyRow['rate'];
		$TotalHomeCurrency = $TotalHomeCurrency + ($QtyPending * $MyRow['unitprice'] / $Rate);
		$TotalAtStandardCost = $TotalAtStandardCost + ($QtyPending * $MyRow['standardcost']);
		echo '<tr class="striped_row">
				<td>' . $MyRow['supplierid'] . '</td>
				<td class="number">' . $MyRow['orderno'] . '</td>
				<td>' . $MyRow['itemcode'] . '</td>
				<td class="date">' . ConvertSQLDate($MyRow['deliverydate']) . '</td>
				<td class="number">' . $MyRow['qtyrecd'] . '</td>
				<td class="number">' . $MyRow['quantityinv'] . '</td>
				<td class="number">' . $QtyPending . '</td>
				<td class="number">' . locale_number_format($MyRow['unitprice'], $MyRow['decimalplaces']) . '</td>
				<td>' . $MyRow['currcode'] . '</td>
				<td class="number">' . locale_number_format(($QtyPending * $MyRow['unitprice']), $MyRow['decimalplaces']) . '</td>
				<td>' . $MyRow['currcode'] . '</td>
				<td class="number">' . locale_number_format(($QtyPending * $MyRow['unitprice'] / $Rate), $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
				<td class="number">' . locale_number_format($MyRow['standardcost'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format(($QtyPending * $MyRow['standardcost']), $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
			</tr>';
	}

	echo '</tbody>
			<tfooter>';

	echo '<tr class="striped_row">
			<td colspan="10">' . '' . '</td>
			<td>' . _('Total') . ':' . '</td>
			<td class="number">' . locale_number_format($TotalHomeCurrency, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
			<td>' . '' . '</td>
			<td class="number">' . locale_number_format($TotalAtStandardCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
		</tr>';

	echo '</tfooter>
			</table>
			</div>';
}

include('includes/footer.php');

?>
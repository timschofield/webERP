<?php
/* $Revision: 1.25 $ */
/* $Id$*/

//$PageSecurity = 2;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$_GET['OrderNumber']=(int)$_GET['OrderNumber'];

if (isset($_GET['OrderNumber'])) {
	$title = _('Reviewing Sales Order Number') . ' ' . $_GET['OrderNumber'];
} else {
	include('includes/header.inc');
	echo '<br /><br /><br />';
	prnMsg(_('This page must be called with a sales order number to review') . '.<br />' . _('i.e.') . ' http://????/OrderDetails.php?OrderNumber=<i>xyz</i><br />' . _('Click on back') . '.','error');
	include('includes/footer.inc');
	exit;
}

include('includes/header.inc');

$OrderHeaderSQL = "SELECT
			salesorders.debtorno,
			debtorsmaster.name,
			salesorders.branchcode,
			salesorders.customerref,
			salesorders.comments,
			salesorders.orddate,
			salesorders.ordertype,
			salesorders.shipvia,
			salesorders.deliverto,
			salesorders.deladd1,
			salesorders.deladd2,
			salesorders.deladd3,
			salesorders.deladd4,
			salesorders.deladd5,
			salesorders.deladd6,
			salesorders.contactphone,
			salesorders.contactemail,
			salesorders.freightcost,
			salesorders.deliverydate,
			debtorsmaster.currcode,
			salesorders.fromstkloc
		FROM
			salesorders,
			debtorsmaster
		WHERE
			salesorders.debtorno = debtorsmaster.debtorno
		AND salesorders.orderno = '" . $_GET['OrderNumber'] . "'";

$ErrMsg =  _('The order cannot be retrieved because');
$DbgMsg = _('The SQL that failed to get the order header was');
$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg, $DbgMsg);

if (DB_num_rows($GetOrdHdrResult)==1) {
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' .
		_('Order Details') . '" alt="" />' . ' ' . $title . '</p>';

	$myrow = DB_fetch_array($GetOrdHdrResult);
	echo '<table class=selection>';
	echo '<tr><th colspan=4><font color=blue>'._('Order Header Details For Order No').' '.$_GET['OrderNumber'].'</font></th></tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Customer Code') . ':</th>
		<td class="OddTableRows"><font><a href="' . $rootpath . '/SelectCustomer.php?Select=' . $myrow['debtorno'] . '">' . $myrow['debtorno'] . '</a></td>
		<th style="text-align: left">' . _('Customer Name') . ':</th><td><font>' . $myrow['name'] . '</td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Customer Reference') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['customerref'] . '</font></td>
		<th style="text-align: left">' . _('Deliver To') . ':</th><td><font>' . $myrow['deliverto'] . '</td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Ordered On') . ':</th>
		<td class="OddTableRows"><font>' . ConvertSQLDate($myrow['orddate']) . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 1') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd1'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Requested Delivery') . ':</th>
		<td class="OddTableRows"><font>' . ConvertSQLDate($myrow['deliverydate']) . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 2') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd2'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left"h>' . _('Order Currency') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['currcode'] . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 3') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd3'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Deliver From Location') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['fromstkloc'] . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 4') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd4'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Telephone') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['contactphone'] . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 5') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd5'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Email') . ':</th>
		<td class="OddTableRows"><font><a href="mailto:' . $myrow['contactemail'] . '">' . $myrow['contactemail'] . '</a></font></td>
		<th style="text-align: left">' . _('Delivery Address 6') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd6'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Freight Cost') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['freightcost'] . '</font></td>
	</tr>';
	echo '<tr><th style="text-align: left">'._('Comments'). ': ';
	echo '</th><td colspan=3>'.$myrow['comments'] . '</td></tr>';
	echo '</table>';
}

/*Now get the line items */

	$LineItemsSQL = "SELECT
				stkcode,
				stockmaster.description,
				stockmaster.volume,
				stockmaster.kgs,
				stockmaster.decimalplaces,
				stockmaster.mbflag,
				stockmaster.units,
				stockmaster.discountcategory,
				stockmaster.controlled,
				stockmaster.serialised,
				unitprice,
				quantity,
				discountpercent,
				actualdispatchdate,
				qtyinvoiced
			FROM salesorderdetails, stockmaster
			WHERE salesorderdetails.stkcode = stockmaster.stockid AND orderno ='" . $_GET['OrderNumber'] . "'";

	$ErrMsg =  _('The line items of the order cannot be retrieved because');
	$DbgMsg =  _('The SQL used to retrieve the line items, that failed was');
	$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg, $DbgMsg);

	if (db_num_rows($LineItemsResult)>0) {

		$OrderTotal = 0;
		$OrderTotalVolume = 0;
		$OrderTotalWeight = 0;

		echo '<br /><table cellpadding=2 colspan=9 class=selection>';
		echo '<tr><th colspan=9><font color=blue>'._('Order Line Details For Order No').' '.$_GET['OrderNumber'].'</font></th></tr>';
		echo '<tr>
			<th>' . _('Item Code') . '</th>
			<th>' . _('Item Description') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('Unit') . '</th>
			<th>' . _('Price') . '</th>
			<th>' . _('Discount') . '</th>
			<th>' . _('Total') . '</th>
			<th>' . _('Qty Del') . '</th>
			<th>' . _('Last Del') . '</th>
			</tr>';
		$k=0;
		while ($myrow=db_fetch_array($LineItemsResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			if ($myrow['qtyinvoiced']>0){
				$DisplayActualDeliveryDate = ConvertSQLDate($myrow['actualdispatchdate']);
			} else {
		  		$DisplayActualDeliveryDate = _('N/A');
			}

			echo 	'<td>' . $myrow['stkcode'] . '</td>
				<td>' . $myrow['description'] . '</td>
				<td class=number>' . $myrow['quantity'] . '</td>
				<td>' . $myrow['units'] . '</td>
				<td class=number>' . number_format($myrow['unitprice'],2) . '</td>
				<td class=number>' . number_format(($myrow['discountpercent'] * 100),2) . '%' . '</td>
				<td class=number>' . number_format($myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']),2) . '</td>
				<td class=number>' . number_format($myrow['qtyinvoiced'],2) . '</td>
				<td>' . $DisplayActualDeliveryDate . '</td>
			</tr>';

			$OrderTotal = $OrderTotal + $myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']);
			$OrderTotalVolume = $OrderTotalVolume + $myrow['quantity'] * $myrow['volume'];
			$OrderTotalWeight = $OrderTotalWeight + $myrow['quantity'] * $myrow['kgs'];

		}
		$DisplayTotal = number_format($OrderTotal,2);
		$DisplayVolume = number_format($OrderTotalVolume,2);
		$DisplayWeight = number_format($OrderTotalWeight,2);

		echo '<tr>
			<td colspan=5 class=number><b>' . _('TOTAL Excl Tax/Freight') . '</b></td>
			<td colspan=2 class=number>' . $DisplayTotal . '</td>
			</tr>
		</table>';

		echo '<br /><table class=selection>
			<tr>
				<td>' . _('Total Weight') . ':</td>
				<td>' . $DisplayWeight . '</td>
				<td>' . _('Total Volume') . ':</td>
				<td>' . $DisplayVolume . '</td>
			</tr>
		</table>';
	}

include('includes/footer.inc');
?>
<?php

/* $Id: OrderDetails.php 6310 2013-08-29 10:42:50Z daintree $*/

/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$_GET['OrderNumber']=(int)$_GET['OrderNumber'];

if (isset($_GET['OrderNumber'])) {
	$Title = _('Reviewing Sales Order Number') . ' ' . $_GET['OrderNumber'];
} else {
	include('includes/header.inc');
	echo '<br /><br /><br />';
	prnMsg(_('This page must be called with a sales order number to review') . '.<br />' . _('i.e.') . ' http://????/OrderDetails.php?OrderNumber=<i>xyz</i><br />' . _('Click on back') . '.','error');
	include('includes/footer.inc');
	exit;
}

include('includes/header.inc');

$OrderHeaderSQL = "SELECT salesorders.debtorno,
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
							salesorders.fromstkloc,
							currencies.decimalplaces
					FROM salesorders INNER JOIN 	debtorsmaster
					ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN currencies
					ON debtorsmaster.currcode=currencies.currabrev
					WHERE salesorders.orderno = '" . $_GET['OrderNumber'] . "'";

$ErrMsg =  _('The order cannot be retrieved because');
$DbgMsg = _('The SQL that failed to get the order header was');
$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg, $DbgMsg);

if (DB_num_rows($GetOrdHdrResult)==1) {
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Order Details') . '" alt="" />' . ' ' . $Title . '
		</p>';

	$myrow = DB_fetch_array($GetOrdHdrResult);
	$CurrDecimalPlaces = $myrow['decimalplaces'];

	if ($CustomerLogin ==1 AND $myrow['debtorno']!= $_SESSION['CustomerID']) {
		prnMsg (_('Your customer login will only allow you to view your own purchase orders'),'error');
		include('includes/footer.inc');
		exit;
	}

	echo '<table class="selection">
			<tr>
				<th colspan="4"><h3>' . _('Order Header Details For Order No').' '.$_GET['OrderNumber'] . '</h3></th>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Customer Code') . ':</th>
				<td class="OddTableRows"><a href="' . $RootPath . '/SelectCustomer.php?Select=' . $myrow['debtorno'] . '">' . $myrow['debtorno'] . '</a></td>
				<th style="text-align: left">' . _('Customer Name') . ':</th>
				<th>' . $myrow['name'] . '</th>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Customer Reference') . ':</th>
				<td class="OddTableRows">' . $myrow['customerref'] . '</td>
				<th style="text-align: left">' . _('Deliver To') . ':</th>
				<th>' . $myrow['deliverto'] . '</th>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Ordered On') . ':</th>
				<td class="OddTableRows">' . ConvertSQLDate($myrow['orddate']) . '</td>
				<th style="text-align: left">' . _('Delivery Address 1') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd1'] . '</td>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Requested Delivery') . ':</th>
				<td class="OddTableRows">' . ConvertSQLDate($myrow['deliverydate']) . '</td>
				<th style="text-align: left">' . _('Delivery Address 2') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd2'] . '</td>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Order Currency') . ':</th>
				<td class="OddTableRows">' . $myrow['currcode'] . '</td>
				<th style="text-align: left">' . _('Delivery Address 3') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd3'] . '</td>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Deliver From Location') . ':</th>
				<td class="OddTableRows">' . $myrow['fromstkloc'] . '</td>
				<th style="text-align: left">' . _('Delivery Address 4') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd4'] . '</td>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Telephone') . ':</th>
				<td class="OddTableRows">' . $myrow['contactphone'] . '</td>
				<th style="text-align: left">' . _('Delivery Address 5') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd5'] . '</td>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Email') . ':</th>
				<td class="OddTableRows"><a href="mailto:' . $myrow['contactemail'] . '">' . $myrow['contactemail'] . '</a></td>
				<th style="text-align: left">' . _('Delivery Address 6') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd6'] . '</td>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Freight Cost') . ':</th>
				<td class="OddTableRows">' . $myrow['freightcost'] . '</td>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Comments'). ': </th>
				<td colspan="3">' . $myrow['comments'] . '</td>
			</tr>
			</table>';
}

/*Now get the line items */

	$LineItemsSQL = "SELECT stkcode,
							stockmaster.description,
							stockmaster.volume,
							stockmaster.grossweight,
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
						FROM salesorderdetails INNER JOIN stockmaster
						ON salesorderdetails.stkcode = stockmaster.stockid
						WHERE orderno ='" . $_GET['OrderNumber'] . "'";

	$ErrMsg =  _('The line items of the order cannot be retrieved because');
	$DbgMsg =  _('The SQL used to retrieve the line items, that failed was');
	$LineItemsResult = DB_query($LineItemsSQL,$db, $ErrMsg, $DbgMsg);

	if (DB_num_rows($LineItemsResult)>0) {

		$OrderTotal = 0;
		$OrderTotalVolume = 0;
		$OrderTotalWeight = 0;

		echo '<br />
			<table class="selection">
			<tr>
				<th colspan="9"><h3>' . _('Order Line Details For Order No').' '.$_GET['OrderNumber'] . '</h3></th>
			</tr>
			<tr>
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
		while ($myrow=DB_fetch_array($LineItemsResult)) {

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
				<td class="number">' . $myrow['quantity'] . '</td>
				<td>' . $myrow['units'] . '</td>
				<td class="number">' . locale_number_format($myrow['unitprice'],$CurrDecimalPlaces) . '</td>
				<td class="number">' . locale_number_format(($myrow['discountpercent'] * 100),2) . '%' . '</td>
				<td class="number">' . locale_number_format($myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']),$CurrDecimalPlaces) . '</td>
				<td class="number">' . locale_number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']) . '</td>
				<td>' . $DisplayActualDeliveryDate . '</td>
			</tr>';

			$OrderTotal += ($myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']));
			$OrderTotalVolume += ($myrow['quantity'] * $myrow['volume']);
			$OrderTotalWeight += ($myrow['quantity'] * $myrow['grossweight']);

		}
		$DisplayTotal = locale_number_format($OrderTotal,$CurrDecimalPlaces);
		$DisplayVolume = locale_number_format($OrderTotalVolume,2);
		$DisplayWeight = locale_number_format($OrderTotalWeight,2);

		echo '<tr>
				<td colspan="5" class="number"><b>' . _('TOTAL Excl Tax/Freight') . '</b></td>
				<td colspan="2" class="number">' . $DisplayTotal . '</td>
			</tr>
			</table>';

		echo '<br />
			<table class="selection">
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
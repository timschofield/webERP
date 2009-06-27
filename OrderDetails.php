<?php
/* $Revision: 1.25 $ */


$PageSecurity = 2;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

if (isset($_GET['OrderNumber'])) {
	$title = _('Reviewing Sales Order Number') . ' ' . $_GET['OrderNumber'];
} else {
	include('includes/header.inc');
	echo '<br><br><br>';
	prnMsg(_('This page must be called with a sales order number to review') . '.<br>' . _('i.e.') . ' http://????/OrderDetails.php?OrderNumber=<i>xyz</i><br>' . _('Click on back') . '.','error');
	include('includes/footer.inc');
	exit;
}

include('includes/header.inc');

$OrderHeaderSQL = 'SELECT
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
		AND salesorders.orderno = ' . $_GET['OrderNumber'];

$ErrMsg =  _('The order cannot be retrieved because');
$DbgMsg = _('The SQL that failed to get the order header was');
$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg, $DbgMsg);

if (DB_num_rows($GetOrdHdrResult)==1) {

	$myrow = DB_fetch_array($GetOrdHdrResult);
	echo '<br><br><table bgcolor="#CCCCCC">';
	echo '<tr>
		<th>' . _('Customer Code') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b><a href="' . $rootpath . '/SelectCustomer.php?Select=' . $myrow['debtorno'] . '">' . $myrow['debtorno'] . '</a></b></td>
		<th>' . _('Customer Name') . ':</th><td bgcolor="#CCCCCC"><font color=BLUE><b>' . $myrow['name'] . '</b></td>
	</tr>';
	echo '<tr>
		<th>' . _('Customer Reference') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['customerref'] . '</font></b></td>
		<th>' . _('Deliver To') . ':</th><td bgcolor="#CCCCCC"><font color=BLUE><b>' . $myrow['deliverto'] . '</b></td>
	</tr>';
	echo '<tr>
		<th>' . _('Ordered On') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . ConvertSQLDate($myrow['orddate']) . '</font></b></td>
		<th>' . _('Delivery Address 1') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['deladd1'] . '</font></b></td>
	</tr>';
	echo '<tr>
		<th>' . _('Requested Delivery') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . ConvertSQLDate($myrow['deliverydate']) . '</font></b></td>
		<th>' . _('Delivery Address 2') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['deladd2'] . '</font></b></td>
	</tr>';
	echo '<tr>
		<th>' . _('Order Currency') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['currcode'] . '</font></b></td>
		<th>' . _('Delivery Address 3') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['deladd3'] . '</font></b></td>
	</tr>';
	echo '<tr>
		<th>' . _('Deliver From Location') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['fromstkloc'] . '</font></b></td>
		<th>' . _('Delivery Address 4') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['deladd4'] . '</font></b></td>
	</tr>';
	echo '<tr>
		<th>' . _('Telephone') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['contactphone'] . '</font></b></td>
		<th>' . _('Delivery Address 5') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['deladd5'] . '</font></b></td>
	</tr>';
	echo '<tr>
		<th>' . _('Email') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b><a href="mailto:' . $myrow['contactemail'] . '">' . $myrow['contactemail'] . '</a></font></b></td>
		<th>' . _('Delivery Address 6') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['deladd6'] . '</font></b></td>
	</tr>';
	echo '<tr>
		<th>' . _('Freight Cost') . ':</th>
		<td class="EvenTableRows"><font color=BLUE><b>' . $myrow['freightcost'] . '</font></b></td>
	</tr>';
	echo '</table><div class="centre">';
	echo _('Comments'). ': ' . $myrow['comments'] . '<br></div>';
}

/*Now get the line items */

	$LineItemsSQL = 'SELECT
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
			WHERE salesorderdetails.stkcode = stockmaster.stockid AND orderno =' . $_GET['OrderNumber'];

	$ErrMsg =  _('The line items of the order cannot be retrieved because');
	$DbgMsg =  _('The SQL used to retrieve the line items, that failed was');
	$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg, $DbgMsg);
                                                                                                              																																
	if (db_num_rows($LineItemsResult)>0) {
		
		$OrderTotal = 0;
		$OrderTotalVolume = 0;
		$OrderTotalWeight = 0;

		echo '<br><div class="centre"><b>' . _('Line Details') . '</div></b>
			<table cellpadding=2 colspan=9 border=1>
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
				<td align=right>' . $myrow['quantity'] . '</td>
				<td>' . $myrow['units'] . '</td>
				<td align=right>' . number_format($myrow['unitprice'],2) . '</td>
				<td align=right>' . number_format(($myrow['discountpercent'] * 100),2) . '%' . '</td>
				<td align=right>' . number_format($myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']),2) . '</td>
				<td align=right>' . number_format($myrow['qtyinvoiced'],2) . '</td>
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
			<td colspan=5 align=right><b>' . _('TOTAL Excl Tax/Freight') . '</b></td>
			<td colspan=2 align=right>' . $DisplayTotal . '</td>
			</tr>
		</table>';
		
		echo '<table border=1>
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

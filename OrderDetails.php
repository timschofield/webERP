<?php

require(__DIR__ . '/includes/session.php');

$_GET['OrderNumber'] = (int)$_GET['OrderNumber'];

if (isset($_GET['OrderNumber'])) {
	$Title = __('Reviewing Sales Order Number') . ' ' . $_GET['OrderNumber'];
} else {
	include('includes/header.php');
	echo '<br /><br /><br />';
	prnMsg(__('This page must be called with a sales order number to review') . '.<br />' . __('i.e.') . ' http://????/OrderDetails.php?OrderNumber=<i>xyz</i><br />' . __('Click on back') . '.','error');
	include('includes/footer.php');
	exit();
}

$ViewTopic = 'SalesOrders';
$BookMark = '';
include('includes/header.php');

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

$ErrMsg =  __('The order cannot be retrieved because');
$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg);

if (DB_num_rows($GetOrdHdrResult)==1) {

	echo '<a class="toplink" href="' . $RootPath . '/SelectCompletedOrder.php">' . __('Return to Sales Order Inquiry') . '</a><br/><br/>
		<a class="toplink" href="' . $RootPath . '/SelectCustomer.php">' . __('Return to Customer Inquiry Interface') . '</a>';

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Order Details') . '" alt="" />' . ' ' . $Title . '
		</p>';

	$MyRow = DB_fetch_array($GetOrdHdrResult);
	$CurrDecimalPlaces = $MyRow['decimalplaces'];

	if ($CustomerLogin ==1 AND $MyRow['debtorno']!= $_SESSION['CustomerID']) {
		prnMsg(__('Your customer login will only allow you to view your own purchase orders'),'error');
		include('includes/footer.php');
		exit();
	}
	//retrieve invoice number
	$Invs = explode(' Inv ',$MyRow['comments']);
	$Inv = '';
	foreach ($Invs as $Value) {
		if (is_numeric($Value)) {
			$Inv .= '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $Value . '&InvOrCredit=Invoice">'.$Value.'</a>  ';
		}
	}

	echo '<table class="selection">
			<tr>
				<th colspan="4"><h3>' . __('Order Header Details For Order No').' '.$_GET['OrderNumber'] . '</h3></th>
			</tr>
			<tr>
				<th class="text">' . __('Customer Code') . ':</th>
				<td><a href="' . $RootPath . '/SelectCustomer.php?Select=' . $MyRow['debtorno'] . '">' . $MyRow['debtorno'] . '</a></td>
				<th class="text">' . __('Customer Name') . ':</th>
				<th>' . $MyRow['name'] . '</th>
			</tr>
			<tr>
				<th class="text">' . __('Customer Reference') . ':</th>
				<td>' . $MyRow['customerref'] . '</td>
				<th class="text">' . __('Deliver To') . ':</th>
				<th>' . $MyRow['deliverto'] . '</th>
			</tr>
			<tr>
				<th class="text">' . __('Ordered On') . ':</th>
				<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
				<th class="text">' . __('Delivery Address 1') . ':</th>
				<td>' . $MyRow['deladd1'] . '</td>
			</tr>
			<tr>
				<th class="text">' . __('Requested Delivery') . ':</th>
				<td>' . ConvertSQLDate($MyRow['deliverydate']) . '</td>
				<th class="text">' . __('Delivery Address 2') . ':</th>
				<td>' . $MyRow['deladd2'] . '</td>
			</tr>
			<tr>
				<th class="text">' . __('Order Currency') . ':</th>
				<td>' . $MyRow['currcode'] . '</td>
				<th class="text">' . __('Delivery Address 3') . ':</th>
				<td>' . $MyRow['deladd3'] . '</td>
			</tr>
			<tr>
				<th class="text">' . __('Deliver From Location') . ':</th>
				<td>' . $MyRow['fromstkloc'] . '</td>
				<th class="text">' . __('Delivery Address 4') . ':</th>
				<td>' . $MyRow['deladd4'] . '</td>
			</tr>
			<tr>
				<th class="text">' . __('Telephone') . ':</th>
				<td>' . $MyRow['contactphone'] . '</td>
				<th class="text">' . __('Delivery Address 5') . ':</th>
				<td>' . $MyRow['deladd5'] . '</td>
			</tr>
			<tr>
				<th class="text">' . __('Email') . ':</th>
				<td><a href="mailto:' . $MyRow['contactemail'] . '">' . $MyRow['contactemail'] . '</a></td>
				<th class="text">' . __('Delivery Address 6') . ':</th>
				<td>' . $MyRow['deladd6'] . '</td>
			</tr>
			<tr>
				<th class="text">' . __('Freight Cost') . ':</th>
				<td>' . $MyRow['freightcost'] . '</td>
			</tr>
			<tr>
				<th class="text">' . __('Comments'). ': </th>
				<td colspan="3">' . $MyRow['comments'] . '</td>
			</tr>
			<tr>
				<th class="text">' . __('Invoices') . ': </th>
				<td colspan="3">' . $Inv . '</td>
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
							qtyinvoiced,
							itemdue,
							poline,
							narrative
						FROM salesorderdetails INNER JOIN stockmaster
						ON salesorderdetails.stkcode = stockmaster.stockid
						WHERE orderno ='" . $_GET['OrderNumber'] . "'";

	$ErrMsg =  __('The line items of the order cannot be retrieved because');
	$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);

	if (DB_num_rows($LineItemsResult)>0) {

		$OrderTotal = 0;
		$OrderTotalVolume = 0;
		$OrderTotalWeight = 0;

		echo '<br />
			<table class="selection">
			<tr>
				<th colspan="13"><h3>' . __('Order Line Details For Order No').' '.$_GET['OrderNumber'] . '</h3></th>
			</tr>
			<tr>
				<th>' . __('PO Line') . '</th>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Item Description') . '</th>
				<th>' . __('Quantity') . '</th>
				<th>' . __('Unit') . '</th>
				<th>' . __('Price') . '</th>
				<th>' . __('Discount') . '</th>
				<th>' . __('Total') . '</th>
				<th>' . __('Qty Del') . '</th>
				<th>' . __('Last Del') . '/' . __('Due Date') . '</th>
				<th>' . __('Narrative') . '</th>
			</tr>';

		while ($MyRow=DB_fetch_array($LineItemsResult)) {

			if ($MyRow['qtyinvoiced']>0){
				$DisplayActualDeliveryDate = ConvertSQLDate($MyRow['actualdispatchdate']);
			} else {
		  		$DisplayActualDeliveryDate = '<span style="color:red;">' . ConvertSQLDate($MyRow['itemdue']) . '</span>';
			}

			echo '<tr class="striped_row">
				<td>' . $MyRow['poline'] . '</td>
				<td>' . $MyRow['stkcode'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td class="number">' . $MyRow['quantity'] . '</td>
				<td>' . $MyRow['units'] . '</td>
				<td class="number">' . locale_number_format($MyRow['unitprice'],$CurrDecimalPlaces) . '</td>
				<td class="number">' . locale_number_format(($MyRow['discountpercent'] * 100),2) . '%' . '</td>
				<td class="number">' . locale_number_format($MyRow['quantity'] * $MyRow['unitprice'] * (1 - $MyRow['discountpercent']),$CurrDecimalPlaces) . '</td>
				<td class="number">' . locale_number_format($MyRow['qtyinvoiced'],$MyRow['decimalplaces']) . '</td>
				<td>' . $DisplayActualDeliveryDate . '</td>
				<td>' . $MyRow['narrative'] . '</td>
			</tr>';

			$OrderTotal += ($MyRow['quantity'] * $MyRow['unitprice'] * (1 - $MyRow['discountpercent']));
			$OrderTotalVolume += ($MyRow['quantity'] * $MyRow['volume']);
			$OrderTotalWeight += ($MyRow['quantity'] * $MyRow['grossweight']);

		}
		$DisplayTotal = locale_number_format($OrderTotal,$CurrDecimalPlaces);
		$DisplayVolume = locale_number_format($OrderTotalVolume,2);
		$DisplayWeight = locale_number_format($OrderTotalWeight,2);

		echo '<tr class="total_row">
				<td colspan="6" class="number"><b>' . __('TOTAL Excl Tax/Freight') . '</b></td>
				<td colspan="2" class="number">' . $DisplayTotal . '</td>
			</tr>
			</table>';

		echo '<br />
			<table class="selection">
			<tr>
				<td>' . __('Total Weight') . ':</td>
				<td>' . $DisplayWeight . '</td>
				<td>' . __('Total Volume') . ':</td>
				<td>' . $DisplayVolume . '</td>
			</tr>
		</table>';
	}

include('includes/footer.php');

<?php
require (__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

include ('includes/SQL_CommonFunctions.php');

//Get Out if we have no order number to work with
if (!isset($_GET['TransNo']) or $_GET['TransNo'] == '') {
	$Title = __('Select Order To Print');
	include ('includes/header.php');
	echo '<div class="centre">';
	prnMsg(__('Select an Order Number to Print before calling this page'), 'error');
	echo '<table class="table_index">
			<tr>
			<td class="menu_group_item">
				 <ul>
					<li><a href="' . $RootPath . '/SelectSalesOrder.php">' . __('Outstanding Sales Orders') . '</a></li>
					<li><a href="' . $RootPath . '/SelectCompletedOrder.php">' . __('Completed Sales Orders') . '</a></li>
				 </ul>
				 </td>
				 </tr>
			</table>
			</div>';
	include ('includes/footer.php');
	exit();
}

/*retrieve the order details from the database to print */
$ErrMsg = __('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . __('from the database');

$SQL = "SELECT salesorders.customerref,
			salesorders.comments,
			salesorders.orddate,
			salesorders.deliverto,
			salesorders.deladd1,
			salesorders.deladd2,
			salesorders.deladd3,
			salesorders.deladd4,
			salesorders.deladd5,
			salesorders.deladd6,
			salesorders.debtorno,
			salesorders.branchcode,
			salesorders.deliverydate,
			debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			shippers.shippername,
			salesorders.printedpackingslip,
			salesorders.datepackingslipprinted,
			locations.locationname
		FROM salesorders INNER JOIN debtorsmaster
			ON salesorders.debtorno=debtorsmaster.debtorno
		INNER JOIN shippers
			ON salesorders.shipvia=shippers.shipper_id
		INNER JOIN locations
			ON salesorders.fromstkloc=locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
		WHERE salesorders.orderno='" . $_GET['TransNo'] . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$Result = DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result) == 0) {

	$ListCount = 0;

	$Title = __('Print Packing Slip Error');
	include ('includes/header.php');
	echo '<div class="centre"><br /><br /><br />';
	prnMsg(__('Unable to Locate Order Number') . ' : ' . $_GET['TransNo'] . ' ', 'error');
	echo '<table class="table_index">
		<tr>
		<td class="menu_group_item">
			<li><a href="' . $RootPath . '/SelectSalesOrder.php">' . __('Outstanding Sales Orders') . '</a></li>
			<li><a href="' . $RootPath . '/SelectCompletedOrder.php">' . __('Completed Sales Orders') . '</a></li>
		</td>
		</tr>
		</table>
		</div>';
	include ('includes/footer.php');
	exit();
} elseif (DB_num_rows($Result) == 1) {

	$ListCount = 1;

	$MyRow = DB_fetch_array($Result);
	if ($MyRow['printedpackingslip'] == 1 and ($_GET['Reprint'] != 'OK' or !isset($_GET['Reprint']))) {
		$Title = __('Print Packing Slip Error');
		include ('includes/header.php');
		echo '<p>';
		prnMsg(__('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' . __('has previously been printed') . '. ' . __('It was printed on') . ' ' . ConvertSQLDate($MyRow['datepackingslipprinted']) . '<br />' . __('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn');
		echo '<p><a href="' . $RootPath . '/PrintCustOrder.php?TransNo=' . $_GET['TransNo'] . '&Reprint=OK">' . __('Do a Re-Print') . ' (' . __('On Pre-Printed Stationery') . ') ' . __('Even Though Previously Printed') . '</a><p>' . '<a href="' . $RootPath . '/PrintCustOrder_generic.php?TransNo=' . $_GET['TransNo'] . '&Reprint=OK">' . __('Do a Re-Print') . ' (' . __('Plain paper') . ' - ' . __('A4') . ' ' . __('landscape') . ')</a>';

		echo __('Or select another Order Number to Print');
		echo '<table class="table_index">
				<tr>
					<td class="menu_group_item">
						<li><a href="' . $RootPath . '/SelectSalesOrder.php">' . __('Outstanding Sales Orders') . '</a></li>
						<li><a href="' . $RootPath . '/SelectCompletedOrder.php">' . __('Completed Sales Orders') . '</a></li>
					</td>
				</tr>
			</table>
			</div>';

		include ('includes/footer.php');
		exit();
	}
}

/* Now ... Has the order got any line items still outstanding to be invoiced */

$ErrMsg = __('There was a problem retrieving the details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . __('from the database');
$SQL = "SELECT salesorderdetails.stkcode,
			stockmaster.description,
			salesorderdetails.quantity,
			salesorderdetails.qtyinvoiced,
			salesorderdetails.unitprice,
			stockmaster.decimalplaces,
			stockmaster.units
		FROM salesorderdetails INNER JOIN stockmaster
			ON salesorderdetails.stkcode=stockmaster.stockid
		 WHERE salesorderdetails.orderno='" . $_GET['TransNo'] . "'";
$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result) > 0) {

	// ---- Prepare HTML for DomPDF ----
	$DeliveryAddress = '';
	for ($j = 1;$j < 5;$j++) {
		if ($MyRow['deladd' . $j] != '') {
			$DeliveryAddress .= htmlspecialchars($MyRow['deladd' . $j]) . "<br />";
		}
	}
	$DeliveryAddress .= htmlspecialchars($MyRow['deladd5']);
	$CustomerAddress = '';
	for ($j = 1;$j < 6;$j++) {
		if ($MyRow['address' . $j] != '') {
			$CustomerAddress .= htmlspecialchars($MyRow['address' . $j]) . "<br />";
		}
	}
	$CustomerAddress .= htmlspecialchars($MyRow['address6']);
	$HTML = '';
	$HTML .= '<!DOCTYPE html>
	<head>
		<link href="css/reports.css" rel="stylesheet" type="text/css" />
		<meta name="author" content="WebERP">
		<meta name="Creator" content="webERP https://www.weberp.org">
	</head>
	<body>
			<h1>' . __('Customer Packing Slip') . '</h1>
			<table>
				<tr>
					<td style="vertical-align:top">
						<strong>' . __('Order Number') . ':</strong> ' . $_GET['TransNo'] . '<br />
						<strong>' . __('Order Date') . ':</strong> ' . htmlspecialchars(ConvertSQLDate($MyRow['orddate'])) . '<br />
						<strong>' . __('Delivery Date') . ':</strong> ' . htmlspecialchars(ConvertSQLDate($MyRow['deliverydate'])) . '<br />
						<strong>' . __('Customer Reference') . ':</strong> ' . htmlspecialchars($MyRow['customerref']) . '<br />
						<strong>' . __('Comments') . ':</strong> ' . htmlspecialchars($MyRow['comments']) . '
					</td>
					<td>
						<strong>' . __('Customer') . ':</strong> ' . htmlspecialchars($MyRow['name']) . '<br />
						' . $CustomerAddress . '
					</td>
					<td style="vertical-align:top">
						<strong>' . __('Deliver To') . ':</strong> ' . htmlspecialchars($MyRow['deliverto']) . '<br />
						' . $DeliveryAddress . '
					</td>
				</tr>
			</table>
			<br />
			<table border="1" cellspacing="0" cellpadding="3" width="100%">
				<thead>
					<tr>
						<th>' . __('Code') . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('Ordered') . '</th>
						<th>' . __('Units') . '</th>
						<th>' . __('To Supply') . '</th>
						<th>' . __('Prev. Supplied') . '</th>
					</tr>
				</thead>
				<tbody>';

	while ($MyRow2 = DB_fetch_array($Result)) {
		$DisplayQty = locale_number_format($MyRow2['quantity'], $MyRow2['decimalplaces']);
		$DisplayPrevDel = locale_number_format($MyRow2['qtyinvoiced'], $MyRow2['decimalplaces']);
		$DisplayQtySupplied = locale_number_format($MyRow2['quantity'] - $MyRow2['qtyinvoiced'], $MyRow2['decimalplaces']);

		$HTML .= '<tr>
						<td>' . htmlspecialchars($MyRow2['stkcode']) . '</td>
						<td>' . htmlspecialchars($MyRow2['description']) . '</td>
						<td style="text-align: right;">' . $DisplayQty . '</td>
						<td>' . htmlspecialchars($MyRow2['units']) . '</td>
						<td style="text-align: right;">' . $DisplayQtySupplied . '</td>
						<td style="text-align: right;">' . $DisplayPrevDel . '</td>
					</tr>';
	}

	$HTML .= '</tbody>
			</table>
			<br /><br />
			<div class="footer">
				' . __('Generated by webERP on') . ' ' . date('Y-m-d H:i') . '
			</div>
	</body>
</html>';

	// ---- End HTML for DomPDF ----

	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);
	// (Optional) Setup the paper size and orientation
	$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');

	// Render the HTML as PDF
	$DomPDF->render();

	// Output the generated PDF to Browser
	$DomPDF->stream($_SESSION['DatabaseName'] . '_Customer_Order_' . $_GET['TransNo'] . date('Y-m-d') . '.pdf', array("Attachment" => false));

	// Mark as printed
	$SQL = "UPDATE salesorders
			SET printedpackingslip = 1,
				datepackingslipprinted = CURRENT_DATE
			WHERE salesorders.orderno = '" . $_GET['TransNo'] . "'";
	$Result = DB_query($SQL);

} else {
	$Title = __('Print Packing Slip Error');
	include ('includes/header.php');
	echo '<p>' . __('There were no outstanding items on the order to deliver. A dispatch note cannot be printed') . '<br /><a href="' . $RootPath . '/SelectSalesOrder.php">' . __('Print Another Packing Slip/Order') . '</a>' . '<br />' . '<a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include ('includes/footer.php');
	exit();
} /*end if there are order details to show on the order*/


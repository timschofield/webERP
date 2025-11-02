<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

include('includes/SQL_CommonFunctions.php');

//Get Out if we have no order number to work with
if (!isset($_GET['TransNo']) || $_GET['TransNo'] == '') {
	$Title = __('Select Order To Print');
	include('includes/header.php');
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
	include('includes/footer.php');
	exit();
}

/*retrieve the order details from the database to print */
$ErrMsg = __('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . __('from the database');

$SQL = "SELECT salesorders.debtorno,
			salesorders.customerref,
			salesorders.comments,
			salesorders.orddate,
			salesorders.deliverto,
			salesorders.deladd1,
			salesorders.deladd2,
			salesorders.deladd3,
			salesorders.deladd4,
			salesorders.deladd5,
			salesorders.deladd6,
			salesorders.deliverblind,
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
			locations.locationname,
			salesorders.fromstkloc
		FROM salesorders
		INNER JOIN debtorsmaster
			ON salesorders.debtorno=debtorsmaster.debtorno
		INNER JOIN shippers
			ON salesorders.shipvia=shippers.shipper_id
		INNER JOIN locations
			ON salesorders.fromstkloc=locations.loccode
		INNER JOIN locationusers
			ON locationusers.loccode=locations.loccode
			AND locationusers.userid='" .  $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		WHERE salesorders.orderno='" . $_GET['TransNo'] . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$Result = DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result) == 0) {
	$Title = __('Print Packing Slip Error');
	include('includes/header.php');
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
	include('includes/footer.php');
	exit();
} elseif (DB_num_rows($Result) == 1) {
	$MyRow = DB_fetch_array($Result);
	$DeliverBlind = $MyRow['deliverblind'];
	if ($MyRow['printedpackingslip'] == 1 && ($_GET['Reprint'] != 'OK' || !isset($_GET['Reprint']))) {
		$Title = __('Print Packing Slip');
		$DatePrinted = $MyRow['datepackingslipprinted'];
		include('includes/header.php');
		echo '<p>';
		prnMsg(__('The packing slip for order number') . ' ' . $_GET['TransNo'] . ' ' .
			__('has previously been printed') . '. ' . __('It was printed on') . ' ' . ConvertSQLDate($DatePrinted) .
			'<br />' . __('This check is there to ensure that duplicate packing slips are not produced and dispatched more than once to the customer'), 'warn');
		echo '<p><a href="' . $RootPath . '/PrintCustOrder.php?TransNo=' . urlencode($_GET['TransNo']) . '&Reprint=OK">'
			. __('Do a Re-Print') . ' (' . __('On Pre-Printed Stationery') . ') ' . __('Even Though Previously Printed') . '</a><p>' .
				'<a href="' . $RootPath. '/PrintCustOrder_generic.php?TransNo=' . urlencode($_GET['TransNo']) . '&Reprint=OK">' .  __('Do a Re-Print') . ' (' . __('Plain paper') . ' - ' . __('A4') . ' ' . __('landscape') . ') ' . __('Even Though Previously Printed'). '</a>';

		echo '<br /><br /><br />';
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

		include('includes/footer.php');
		exit();
	}
}

// Build HTML for dompdf
$ListCount = 0;
$Volume = 0;
$Weight = 0;

$HTML = '<html><head><link href="css/reports.css" rel="stylesheet" type="text/css" /><style>
body { font-family: Arial, sans-serif; font-size: 12px; }
.table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.table th, .table td { border: 1px solid #000; padding: 5px; }
.header { font-size: 16px; font-weight: bold; margin-bottom: 10px; }
</style></head><body>';

	$DeliveryAddress = '';
	for ($j = 1;$j < 5;$j++) {
		if ($MyRow['deladd' . $j] != '') {
			$DeliveryAddress .= htmlspecialchars($MyRow['deladd' . $j]) . ", ";
		}
	}
	$DeliveryAddress .= htmlspecialchars($MyRow['deladd5']);

for ($i = 1; $i <= 2; $i++) {  // Office + Customer copy
	$HTML .= '<div class="header">' . __('Customer Laser Packing Slip') . ' - ' . ($_SESSION['CompanyRecord']['coyname']) . '</div>';
	$HTML .= '<div>' . __('Order Number') . ': <strong>' . $_GET['TransNo'] . '</strong></div>';
	$HTML .= '<div>' . __('Customer') . ': ' . htmlspecialchars($MyRow['name']) . '</div>';
	$HTML .= '<div>' . __('Delivery To') . ': ' . htmlspecialchars($MyRow['deliverto']) . '</div>';
	$HTML .= '<div>' . __('Address') . ': ' . $DeliveryAddress . '</div>';

	$ErrMsg = __('There was a problem retrieving the order details for Order Number') . ' ' . $_GET['TransNo'] . ' ' . __('from the database');

	$SQL = "SELECT salesorderdetails.stkcode,
					stockmaster.description,
					salesorderdetails.quantity,
					salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.narrative,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					stockmaster.grossweight,
					stockmaster.volume,
					stockmaster.units,
					stockmaster.controlled,
					stockmaster.serialised,
					pickreqdetails.qtypicked,
					pickreqdetails.detailno,
					custitem.cust_part,
					custitem.cust_description,
					locstock.bin
				FROM salesorderdetails
				INNER JOIN stockmaster
					ON salesorderdetails.stkcode=stockmaster.stockid
				INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
				LEFT OUTER JOIN pickreq
					ON pickreq.orderno=salesorderdetails.orderno
					AND pickreq.closed=0
				LEFT OUTER JOIN pickreqdetails
					ON pickreqdetails.prid=pickreq.prid
					AND pickreqdetails.orderlineno=salesorderdetails.orderlineno
				LEFT OUTER JOIN custitem
					ON custitem.debtorno='" . $MyRow['debtorno'] . "'
					AND custitem.stockid=salesorderdetails.stkcode
				WHERE locstock.loccode = '" . $MyRow['fromstkloc'] . "'
					AND salesorderdetails.orderno='" . $_GET['TransNo'] . "'";
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) > 0) {
		$HTML .= '<table class="table">
					<tr>
						<th>' . __('Code') . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('Qty') . '</th>
						<th>' . __('Units') . '</th>
						<th>' . __('Bin') . '</th>
						<th>' . __('Supplied') . '</th>
						<th>' . __('Prev Delivered') . '</th>
					</tr>';

		while ($MyRow2 = DB_fetch_array($Result)) {
			$ListCount++;
			$Volume += $MyRow2['quantity'] * $MyRow2['volume'];
			$Weight += $MyRow2['quantity'] * $MyRow2['grossweight'];

			$DisplayQty = locale_number_format($MyRow2['quantity'], $MyRow2['decimalplaces']);
			$DisplayPrevDel = locale_number_format($MyRow2['qtyinvoiced'], $MyRow2['decimalplaces']);

			if ($MyRow2['qtypicked'] > 0) {
				$DisplayQtySupplied = locale_number_format($MyRow2['qtypicked'], $MyRow2['decimalplaces']);
			} else {
				$DisplayQtySupplied = locale_number_format($MyRow2['quantity'] - $MyRow2['qtyinvoiced'], $MyRow2['decimalplaces']);
			}

			$HTML .= '<tr>
						<td>' . htmlspecialchars($MyRow2['stkcode']) . '</td>
						<td>' . htmlspecialchars($MyRow2['description']) . '</td>
						<td style="text-align:right">' . $DisplayQty . '</td>
						<td>' . htmlspecialchars($MyRow2['units']) . '</td>
						<td>' . htmlspecialchars($MyRow2['bin']) . '</td>
						<td class="number">' . $DisplayQtySupplied . '</td>
						<td class="number">' . $DisplayPrevDel . '</td>
					</tr>';

			if ($_SESSION['AllowOrderLineItemNarrative'] == 1 && !empty($MyRow2['narrative'])) {
				$HTML .= '<tr><td colspan="7"><em>' . htmlspecialchars($MyRow2['narrative']) . '</em></td></tr>';
			}

			// Customer part info
			if ($MyRow2['cust_part'] > '') {
				$HTML .= '<tr>
							<td colspan="2">' . htmlspecialchars($MyRow2['cust_part']) . ' - ' . htmlspecialchars($MyRow2['cust_description']) . '</td>
							<td colspan="5"></td>
						</tr>';
			}

			// Assembly components
			if ($MyRow2['mbflag'] == 'A') {
				$SQL = "SELECT bom.component,
									bom.quantity,
									stockmaster.description,
									stockmaster.decimalplaces
							FROM bom
							INNER JOIN stockmaster
								ON bom.component=stockmaster.stockid
							WHERE bom.parent='" . $MyRow2['stkcode'] . "'
								AND bom.effectiveafter <= CURRENT_DATE
								AND bom.effectiveto > CURRENT_DATE";
				$ErrMsg = __('Could not retrieve the components of the ordered assembly item');
				$AssemblyResult = DB_query($SQL, $ErrMsg);
				$HTML .= '<tr>
							<td colspan="7"><strong>' . __('Assembly Components:-') . '</strong></td>
						</tr>';
				while ($ComponentRow = DB_fetch_array($AssemblyResult)) {
					$DisplayQtySupplied = locale_number_format($ComponentRow['quantity'] * ($MyRow2['quantity'] - $MyRow2['qtyinvoiced']), $ComponentRow['decimalplaces']);
					$HTML .= '<tr>
								<td>' . htmlspecialchars($ComponentRow['component']) . '</td>
								<td>' . htmlspecialchars($ComponentRow['description']) . '</td>
								<td colspan="3"></td>
								<td style="text-align:right">' . $DisplayQtySupplied . '</td>
								<td></td>
							</tr>';
				}
			}

			// Controlled/serialised items
			if ($MyRow2['controlled'] == '1') {
				$ControlLabel = __('Lot') . ':';
				if ($MyRow2['serialised'] == 1) {
					$ControlLabel = __('Serial') . ':';
				}
				$SerSQL = "SELECT serialno,
										moveqty
								FROM pickserialdetails
								WHERE pickserialdetails.detailno='" . $MyRow2['detailno'] . "'";
				$SerResult = DB_query($SerSQL, $ErrMsg);
				while ($MySer = DB_fetch_array($SerResult)) {
					$HTML .= '<tr>
								<td>' . $ControlLabel . '</td>
								<td>' . htmlspecialchars($MySer['serialno']) . '</td>
								<td colspan="3"></td>
								<td style="text-align:right">' . htmlspecialchars($MySer['moveqty']) . '</td>
								<td></td>
							</tr>';
				}
			}
		}
		$HTML .= '</table>';
	}

	// Signature line (only on office copy)
	if ($i == 1) {
		$HTML .= '<div style="margin-top:40px;">Accepted/Received By: ________________________ Date: ________________</div>';
	}

	$HTML .= '<div style="margin-top:20px;">Volume: ' . round($Volume) . ' GA &nbsp;&nbsp; Weight: ' . round($Weight) . ' LB (approximate)</div>';

	// Reset for next copy
	$Volume = 0;
	$Weight = 0;

	if ($i == 1) {
		$HTML .= '<div style="page-break-after:always"></div>';
	}
}

$HTML .= '</body></html>';

if ($ListCount == 0) {
	$Title = __('Print Packing Slip Error');
	include('includes/header.php');
	echo '<p>' . __('There were no outstanding items on the order to deliver') . '. ' . __('A packing slip cannot be printed') .
		'<br /><a href="' . $RootPath . '/SelectSalesOrder.php">' . __('Print Another Packing Slip/Order') .
		'</a>
		<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');
	exit();
} else {
	// Generate PDF using dompdf
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);
	// (Optional) Setup the paper size and orientation
	$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');

	// Render the HTML as PDF
	$DomPDF->render();

	// Output the generated PDF to Browser

	$FileName = $_SESSION['DatabaseName'] . '_PackingSlip_' . $_GET['TransNo'] . '_' . date('Y-m-d') . '.pdf';
	$DomPDF->stream($FileName, array("Attachment" => false));

	$SQL = "UPDATE salesorders
			SET printedpackingslip = 1,
				datepackingslipprinted = CURRENT_DATE
			WHERE salesorders.orderno = '" . $_GET['TransNo'] . "'";
	$Result = DB_query($SQL);
}
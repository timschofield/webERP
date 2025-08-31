<?php

// Generate a picking list using DomPDF.

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SQL_CommonFunctions.php');

/* $Title is set in several parts of this script. */
$ViewTopic = 'Sales';
$BookMark = 'GeneratePickingList';

if (isset($_POST['TransDate'])){$_POST['TransDate'] = ConvertSQLDate($_POST['TransDate']);}

/* Check that the config variable is set for picking notes and get out if not. */
if ($_SESSION['RequirePickingNote'] == 0) {
	$Title = __('Picking Lists Not Enabled');
	include('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/error.png" title="',
		$Title, '" /> ',
		$Title, '</p>';
	echo '<br />';
	prnMsg(__('The system is not configured for picking lists. A configuration parameter is required where picking slips are required. Please consult your system administrator.'), 'info');
	include('includes/footer.php');
	exit();
}

/* Show selection screen if we have no orders to work with */
if ((!isset($_GET['TransNo']) or $_GET['TransNo'] == '') and !isset($_POST['TransDate'])) {
	$Title = __('Select Picking Lists');
	include('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/sales.png" title="',
		__('Search'), '" /> ',
		$Title, '</p>';
	$SQL = "SELECT locations.loccode,
			locationname
		FROM locations
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1";
	$Result = DB_query($SQL);
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" name="form">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Picking List Criteria'), '</legend>';
	echo '<field>
			<label for="TransDate">' . __('Create picking lists for all deliveries to be made on') . ' : ' . '</label>
			<input required="required" autofocus="autofocus" type="date" name="TransDate" maxlength="10" size="11" value="' . date('Y-m-d', mktime(date('m'), date('Y'), date('d') + 1)) . '" />
		</field>
		<field>
			<label for="loccode">' . __('From Warehouse') . ' : ' . '</label>
			<select required="required" name="loccode">';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Process" value="' . __('Print Picking Lists') . '" />
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}

/*retrieve the order details from the database to print */
$ErrMsg = __('There was a problem retrieving the order header details from the database');

if (!isset($_POST['TransDate']) and $_GET['TransNo'] != 'Preview') {
	$SQL = "SELECT salesorders.debtorno,
					salesorders.orderno,
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
					locations.loccode,
					locations.locationname
				FROM salesorders INNER JOIN salesorderdetails on salesorderdetails.orderno=salesorders.orderno,
					debtorsmaster,
					shippers,
					locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
				WHERE salesorders.debtorno=debtorsmaster.debtorno
					AND salesorders.shipvia=shippers.shipper_id
					AND salesorders.fromstkloc=locations.loccode
					AND salesorders.orderno='" . $_GET['TransNo'] . "'
					AND salesorderdetails.completed=0
				GROUP BY salesorders.orderno";
} else if (isset($_POST['TransDate']) or (isset($_GET['TransNo']) and $_GET['TransNo'] != 'Preview')) {
	$SQL = "SELECT salesorders.debtorno,
					salesorders.orderno,
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
					locations.loccode,
					locations.locationname
				FROM salesorders INNER JOIN salesorderdetails on salesorderdetails.orderno=salesorders.orderno,
					debtorsmaster,
					shippers,
					locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
				WHERE salesorders.debtorno=debtorsmaster.debtorno
					AND salesorders.shipvia=shippers.shipper_id
					AND salesorders.fromstkloc=locations.loccode
					AND salesorders.fromstkloc='" . $_POST['loccode'] . "'
					AND salesorders.deliverydate<='" . FormatDateForSQL($_POST['TransDate']) . "'
					AND salesorderdetails.completed=0
				GROUP BY salesorders.orderno
				ORDER BY salesorders.deliverydate, salesorders.orderno";
}

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

if (isset($_POST['TransDate']) or (isset($_GET['TransNo']) and $_GET['TransNo'] != 'Preview')) {
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('Print Picking List Error');
		include('includes/header.php');
		echo '<br />';
		prnMsg(__('Unable to Locate any orders for this criteria '), 'info');
		echo '<br />
			<table class="selection">
			<tr>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Enter Another Date') . '</a></td>
			</tr>
			</table>
			<br />';
		include('includes/footer.php');
		exit();
	}

	while ($MyRow = DB_fetch_array($Result)) {
		$OrdersToPick[] = $MyRow;
	}
}
else {
	$OrdersToPick[0]['debtorno'] = str_pad('', 10, 'x');
	$OrdersToPick[0]['orderno'] = 'Preview';
	$OrdersToPick[0]['customerref'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['comments'] = str_pad('', 100, 'x');
	$OrdersToPick[0]['orddate'] = '1000-01-01';
	$OrdersToPick[0]['deliverto'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['deladd1'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['deladd2'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['deladd3'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['deladd4'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['deladd5'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['deladd6'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['deliverblind'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['deliverydate'] = '1000-01-01';
	$OrdersToPick[0]['name'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['address1'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['address2'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['address3'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['address4'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['address5'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['address6'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['shippername'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['printedpackingslip'] = str_pad('', 20, 'x');
	$OrdersToPick[0]['datepackingslipprinted'] = '1000-01-01';
	$OrdersToPick[0]['locationname'] = str_pad('', 15, 'x');
}

// Prepare HTML for DomPDF
$HTML = '<html><head>
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { border: 1px solid #000; padding: 4px; }
.header { font-weight: bold; font-size: 16px; margin-bottom: 10px; }
</style>
</head><body>';

$TotalOrderCount = sizeof($OrdersToPick);

for ( $i = 0; $i < $TotalOrderCount; $i++ ){
	$HTML .= '<div class="header">'.__('Picking List').'</div>';
	$HTML .= '<table class="table">';
	$HTML .= '<tr>
		<th>' . __('Stock Code') . '</th>
		<th>' . __('Description') . '</th>
		<th>' . __('Bin') . '</th>
		<th>' . __('Qty To Pick') . '</th>
		<th>' . __('Qty Available') . '</th>
		<th>' . __('Qty Picked') . '</th>
	</tr>';

	// Build SQL for lines
	$Order = $OrdersToPick[$i];

	$SQL = "SELECT salesorderdetails.stkcode,
					stockmaster.description,
					stockmaster.controlled,
					stockmaster.serialised,
					salesorderdetails.orderlineno,
					(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) as qtyexpected,
					salesorderdetails.quantity,
					salesorderdetails.qtyinvoiced,
					salesorderdetails.narrative,
					stockmaster.decimalplaces,
					custitem.cust_part,
					custitem.cust_description,
					locstock.quantity qtyavail,
					bin
				FROM salesorderdetails
				INNER JOIN locstock
					ON locstock.loccode='" . $Order['loccode'] . "'
					AND locstock.stockid=salesorderdetails.stkcode
				INNER JOIN stockmaster
					ON salesorderdetails.stkcode=stockmaster.stockid
				LEFT OUTER JOIN custitem
					ON custitem.debtorno='" . $Order['debtorno'] . "'
					AND custitem.stockid=stockmaster.stockid
				WHERE salesorderdetails.orderno='" . $Order['orderno'] . "'
				AND salesorderdetails.completed=0";
	$LineResult = DB_query($SQL);

	while ($MyRow2 = DB_fetch_array($LineResult)) {
		$DisplayQtySupplied = locale_number_format($MyRow2['quantity'] - $MyRow2['qtyinvoiced'], $MyRow2['decimalplaces']);
		$DisplayQtyAvail = locale_number_format($MyRow2['qtyavail'], $MyRow2['decimalplaces']);
		$DisplayPicked = '____________'; // Picking input or previously picked qty

		$HTML .= '<tr>
			<td>' . htmlspecialchars($MyRow2['stkcode']) . '</td>
			<td>' . htmlspecialchars($MyRow2['description']) . '</td>
			<td>' . htmlspecialchars($MyRow2['bin']) . '</td>
			<td style="text-align:right">' . $DisplayQtySupplied . '</td>
			<td style="text-align:right">' . $DisplayQtyAvail . '</td>
			<td style="text-align:right">' . $DisplayPicked . '</td>
		</tr>';

		if ($MyRow2['cust_part'] > '') {
			$HTML .= '<tr>
				<td></td>
				<td colspan="5">' . htmlspecialchars($MyRow2['cust_part'] . ' ' . $MyRow2['cust_description']) . '</td>
			</tr>';
		}
		if ($MyRow2['narrative'] > '') {
			$HTML .= '<tr>
				<td></td>
				<td colspan="5">' . htmlspecialchars($MyRow2['narrative']) . '</td>
			</tr>';
		}

		// Serial/batch details if controlled
		if ($MyRow2['controlled'] == 1) {
			$label = $MyRow2['serialised'] == 1 ? __('Serial number') : __('Lot Number');
			$SQLBundles = "SELECT serialno, quantity
				FROM stockserialitems
				WHERE stockid='" . $MyRow2['stkcode'] . "'
				AND stockserialitems.loccode ='" . $Order['loccode'] . "'
				AND quantity > 0
				ORDER BY createdate, quantity";
			$Bundles = DB_query($SQLBundles);
			while ($MyBundles = DB_fetch_array($Bundles)) {
				$HTML .= '<tr>
					<td></td>
					<td colspan="2">' . $label . ': ' . htmlspecialchars($MyBundles['serialno']) . '</td>
					<td colspan="3">' . locale_number_format($MyBundles['quantity'], $MyRow2['decimalplaces']) . '</td>
				</tr>';
			}
		}
	}
	$HTML .= '</table>';
	$HTML .= '<br><strong>'.__('Signed for').': ______________________  '. __('Date').': __________</strong>';

	// Add page break for DomPDF for next order
	if ($i < $TotalOrderCount-1) {
		$HTML .= '<div style="page-break-after:always"></div>';
	}
}

$HTML .= '</body></html>';

// Output PDF
$dompdf = new Dompdf(['chroot' => __DIR__]);
$dompdf->loadHtml($HTML);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($_SESSION['DatabaseName'] . '_PickingLists_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
exit();

<?php
require (__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

include ('includes/SQL_CommonFunctions.php');

if (isset($_POST['Process'])) {
	if (isset($_POST['FromDate'])) {
		$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);
	}

	/* Check that the config variable is set for picking notes and get out if not. */
	if ($_SESSION['RequirePickingNote'] == 0) {
		$Title = __('Picking Lists Not Enabled');
		include ('includes/header.php');
		echo '<br />';
		prnMsg(__('The system is not configured for picking lists. A configuration parameter is required where picking slips are required. Please consult your system administrator.'), 'info');
		include ('includes/footer.php');
		exit();
	}

	/* Retrieve the order details from the database to print */
	$ErrMsg = __('There was a problem retrieving the order header details from the database');

	if (!isset($_POST['TransDate']) and $_GET['TransNo'] !=  'Preview') {
		/* If there is no transaction date set, then it must be for a single order */
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
				locations.locationname
			FROM salesorders,
				debtorsmaster,
				shippers,
				locations
			WHERE salesorders.debtorno=debtorsmaster.debtorno
			AND salesorders.shipvia=shippers.shipper_id
			AND salesorders.fromstkloc=locations.loccode
			AND salesorders.orderno='" . $_GET['TransNo'] . "'";
	}
	elseif (isset($_POST['TransDate']) || (isset($_GET['TransNo']) and $_GET['TransNo'] !=  'Preview')) {
		/* We are printing picking lists for all orders on a day */
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
					locations.locationname
				FROM salesorders,
					debtorsmaster,
					shippers,
					locations
				WHERE salesorders.debtorno=debtorsmaster.debtorno
				AND salesorders.shipvia=shippers.shipper_id
				AND salesorders.fromstkloc=locations.loccode
				AND salesorders.fromstkloc='" . $_POST['loccode'] . "'
				AND salesorders.deliverydate<='" . FormatDateForSQL($_POST['TransDate']) . "'";
	}

	if ($_SESSION['SalesmanLogin'] !=  '') {
		$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}

	if (isset($_POST['TransDate']) || (isset($_GET['TransNo']) and $_GET['TransNo'] !=  'Preview')) {
		$Result = DB_query($SQL, $ErrMsg);

		/* If there are no rows, there's a problem. */
		if (DB_num_rows($Result) == 0) {
			$Title = __('Print Picking List Error');
			include ('includes/header.php');
			echo '<br />';
			prnMsg(__('Unable to Locate any orders for this criteria '), 'info');
			echo '<br />
				<table class="selection">
				<tr>
					<td><a href="' . $RootPath . '/PDFPickingList.php">' . __('Enter Another Date') . '</a></td>
				</tr>
				</table>
				<br />';
			include ('includes/footer.php');
			exit();
		}

		/* Retrieve the order details from the database and place them in an array */
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$OrdersToPick[$i] = $MyRow;
			$i++;
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

	$ListCount = 0;
	$HTML = '<html>
	<head>
	<link href="css/reports.css" rel="stylesheet" type="text/css" />
	</head>
	<body>';

	for ($i = 0;$i < sizeof($OrdersToPick);$i++) {
		$order = $OrdersToPick[$i];
		$DeliveryAddress = '';
		for ($j = 1; $j<5; $j++) {
			if ($order['deladd' . $j] !=  '') {
				$DeliveryAddress .= htmlspecialchars($order['deladd' . $j]) . ", ";
			}
		}
		$DeliveryAddress .= htmlspecialchars($order['deladd5']);
		$HTML .= '<div style="page-break-after: always;">';
		$HTML .= '<h2>' . __('Picking List') . '</h2>';
		$HTML .= "<table border='0' style='width:100%;'>";
		$HTML .= "<tr>
		<td><b>" . __('Order No') . ":</b> " . htmlspecialchars($order['orderno']) . "</td>
		<td><b>" . __('Customer') . ":</b> " . htmlspecialchars($order['name']) . "</td>
	</tr>
	<tr>
		<td><b>" . __('Delivery Date') . ":</b> " . htmlspecialchars($order['deliverydate']) . "</td>
		<td><b>" . __('Warehouse') . ":</b> " . htmlspecialchars($order['locationname']) . "</td>
	</tr>
	<tr>
		<td colspan='2'><b>" . __('Deliver To') . ":</b> " . htmlspecialchars($order['deliverto']) . ", " . $DeliveryAddress . "
		</td>
	</tr>
	<tr>
		<td colspan='2'><b>" . __('Comments') . ":</b> " . htmlspecialchars($order['comments']) . "</td>
	</tr>
	</table>";

		// Get line items
		if ($order['orderno'] == 'Preview') {
			$lineItems = [['stkcode' => str_pad('', 10, 'x'), 'description' => str_pad('', 18, 'x'), 'narrative' => str_pad('', 18, 'x'), 'quantity' => 'XXXX.XX', 'qtyinvoiced' => 'XXXX.XX', 'supplied' => 'XXXX.XX']];
		}
		else {
			$SQL = "SELECT salesorderdetails.stkcode,
					stockmaster.description,
					salesorderdetails.orderlineno,
					salesorderdetails.quantity,
					salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.narrative,
					stockmaster.decimalplaces
				FROM salesorderdetails
				INNER JOIN stockmaster
					ON salesorderdetails.stkcode=stockmaster.stockid
				WHERE salesorderdetails.orderno='" . $order['orderno'] . "'";
			$LineResult = DB_query($SQL);
			$lineItems = [];
			while ($row = DB_fetch_array($LineResult)) {
				$DisplayQty = locale_number_format($row['quantity'], $row['decimalplaces']);
				$DisplayPrevDel = locale_number_format($row['qtyinvoiced'], $row['decimalplaces']);
				$DisplayQtySupplied = locale_number_format($row['quantity'] - $row['qtyinvoiced'], $row['decimalplaces']);
				$lineItems[] = ['stkcode' => $row['stkcode'], 'description' => $row['description'], 'narrative' => $row['narrative'], 'quantity' => $DisplayQty, 'qtyinvoiced' => $DisplayPrevDel, 'supplied' => $DisplayQtySupplied];
			}
		}

		// Table header for line items
		$HTML .= "<table border='1' cellpadding='4' cellspacing='0' style='width:100%;margin-top:15px;'>
		<tr style='background:#e0e0e0;'>
			<th>" . __('Stock Code') . "</th>
			<th>" . __('Description') . "</th>
			<th>" . __('Quantity Ordered') . "</th>
			<th>" . __('Quantity To Pick') . "</th>
			<th>" . __('Previously Delivered') . "</th>
		</tr>";

		foreach ($lineItems as $item) {
			$ItemDescription = htmlspecialchars($item['description']);
			$Narrative = htmlspecialchars($item['narrative']);
			$HTML .= "<tr>
			<td>" . htmlspecialchars($item['stkcode']) . "</td>
			<td>" . $ItemDescription . ($Narrative ? ' - ' . $Narrative : '') . "</td>
			<td style='text-align:right;'>" . htmlspecialchars($item['quantity']) . "</td>
			<td style='text-align:right;'>" . htmlspecialchars($item['supplied']) . "</td>
			<td style='text-align:right;'>" . htmlspecialchars($item['qtyinvoiced']) . "</td>
		</tr>";
		}
		$HTML .= "</table>";
		$HTML .= "</div>";
		$ListCount++;
	}

	if ($ListCount == 0) {
		$Title = __('Print Picking List Error');
		include ('includes/header.php');
		include ('includes/footer.php');
		exit();
	}
	else {
		$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
		$DomPDF->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$DomPDF->render();

		// Output the generated PDF to Browser
		$DomPDF->stream($_SESSION['DatabaseName'] . '_PickingLists_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));

	}
	/* Show selection screen if we have no orders to work with */
} else {
	if ((!isset($_GET['TransNo']) or $_GET['TransNo'] == '') and !isset($_POST['TransDate'])) {
		$Title = __('Select Picking Lists');
		$ViewTopic = 'Sales';
		$BookMark = '';
		include ('includes/header.php');
		$SQL = "SELECT locations.loccode,
				locationname
			FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1";
		$Result = DB_query($SQL);
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" name="form" target="_blank">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
			<legend>', __('Selection Criteria'), '</legend>
		<field>
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
		include ('includes/footer.php');
		exit();
	}
}


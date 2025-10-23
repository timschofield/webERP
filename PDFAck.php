<?php

/* Prints an acknowledgement using DomPDF */

require_once('includes/session.php');

use Dompdf\Dompdf;

require_once('includes/SQL_CommonFunctions.php');

//Get Out if we have no order number to work with
if (!isset($_GET['AcknowledgementNo']) || $_GET['AcknowledgementNo'] == "") {
	$Title = __('Select Acknowledgement To Print');
	include('includes/header.php');
	prnMsg(__('Select an Acknowledgement to Print before calling this page'), 'error');
	echo '<table class="table_index">
				<tr>
					<td class="menu_group_item">
						<ul><li><a href="' . $RootPath . '/SelectSalesOrder.php?Acknowledgements=Quotes_Only">' . __('Acknowledgements') . '</a></li>
						</ul>
					</td>
				</tr>
				</table>';
	include('includes/footer.php');
	exit();
}

/* Retrieve the order details from the database to print */
$ErrMsg = __('There was a problem retrieving the Acknowledgement header details for Order Number') . ' ' . $_GET['AcknowledgementNo'] . ' ' . __('from the database');
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
				salesorders.freightcost,
				debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.currcode,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtorsmaster.address6,
				shippers.shippername,
				salesorders.printedpackingslip,
				salesorders.datepackingslipprinted,
				salesorders.branchcode,
				locations.taxprovinceid,
				locations.locationname,
				currencies.decimalplaces AS currdecimalplaces
			FROM salesorders
			INNER JOIN debtorsmaster
				ON salesorders.debtorno=debtorsmaster.debtorno
			INNER JOIN shippers
				ON salesorders.shipvia=shippers.shipper_id
			INNER JOIN locations
				ON salesorders.fromstkloc=locations.loccode
			INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
				AND salesorders.orderno='" . $_GET['AcknowledgementNo'] . "'";

$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result) == 0) {
	$Title = __('Print Acknowledgement Error');
	include('includes/header.php');
	prnMsg(__('Unable to Locate Acknowledgement Number') . ' : ' . $_GET['AcknowledgementNo'] . ' ', 'error');
	echo '<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<ul><li><a href="' . $RootPath . '/SelectSalesOrder.php?Acknowledgements=Quotes_Only">' . __('Outstanding Acknowledgements') . '</a></li></ul>
				</td>
			</tr>
			</table>';
	include('includes/footer.php');
	exit();
} elseif (DB_num_rows($Result) == 1) {
	$MyRow = DB_fetch_array($Result);
}

$Terms = $_SESSION['RomalpaClause'];

/* Now ... Has the order got any line items still outstanding to be invoiced */
$ErrMsg = __('There was a problem retrieving the Acknowledgement line details for Acknowledgement Number') . ' ' . $_GET['AcknowledgementNo'] . ' ' . __('from the database');
$SQL = "SELECT salesorderdetails.stkcode,
		stockmaster.description,
		salesorderdetails.quantity,
		salesorderdetails.qtyinvoiced,
		salesorderdetails.unitprice,
		salesorderdetails.itemdue,
		salesorderdetails.narrative,
		stockmaster.taxcatid,
		stockmaster.units,
		salesorderdetails.discountpercent,
		stockmaster.decimalplaces,
		custitem.cust_part,
		custitem.cust_description
	FROM salesorderdetails
	INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
	LEFT OUTER JOIN custitem
		ON custitem.debtorno='" . $MyRow['debtorno'] . "'
		AND custitem.stockid=stockmaster.stockid
	WHERE salesorderdetails.orderno='" . $_GET['AcknowledgementNo'] . "'";

$Result = DB_query($SQL, $ErrMsg);

$ListCount = 0;
$lineItemsHtml = '';
$AcknowledgementTotal = $MyRow['freightcost'];
$AcknowledgementTotalEx = 0;
$TaxTotal = 0;

while ($MyRow2 = DB_fetch_array($Result)) {
	$ListCount++;
	$DisplayQty = locale_number_format($MyRow2['quantity'], $MyRow2['decimalplaces']);
	$DisplayUOM = $MyRow2['units'];
	$DisplayPrevDel = locale_number_format($MyRow2['qtyinvoiced'], $MyRow2['decimalplaces']);
	$DisplayPrice = locale_number_format($MyRow2['unitprice'], 4);
	$SubTot = $MyRow2['unitprice'] * $MyRow2['quantity'] * (1 - $MyRow2['discountpercent']);
	$TaxProv = $MyRow['taxprovinceid'];
	$TaxCat = $MyRow2['taxcatid'];
	$Branch = $MyRow['branchcode'];

	// Get TaxAuth
	$SQL3 = "SELECT taxgrouptaxes.taxauthid
				FROM taxgrouptaxes
				INNER JOIN custbranch
					ON taxgrouptaxes.taxgroupid=custbranch.taxgroupid
				WHERE custbranch.branchcode='" . $Branch . "'";
	$Result3 = DB_query($SQL3, $ErrMsg);
	$TaxAuth = '';
	while ($MyRow3 = DB_fetch_array($Result3)) {
		$TaxAuth = $MyRow3['taxauthid'];
	}

	// Get Tax Rate
	$SQL4 = "SELECT taxrate
				FROM taxauthrates
				WHERE dispatchtaxprovince='" . $TaxProv . "'
					AND taxcatid='" . $TaxCat . "'
					AND taxauthority='" . $TaxAuth . "'";
	$Result4 = DB_query($SQL4, $ErrMsg);
	$TaxClass = 0;
	while ($MyRow4 = DB_fetch_array($Result4)) {
		$TaxClass = 100 * $MyRow4['taxrate'];
	}

	$TaxAmount = (($SubTot / 100) * (100 + $TaxClass)) - $SubTot;
	$LineTotal = $SubTot + $TaxAmount;

	$AcknowledgementTotal += $LineTotal;
	$AcknowledgementTotalEx += $SubTot;
	$TaxTotal += $TaxAmount;

	$lineItemsHtml .= '<tr>
		<td>' . htmlspecialchars($MyRow2['stkcode']) . '</td>
		<td>' . htmlspecialchars($MyRow2['description']) . '</td>
		<td>' . ConvertSQLDate($MyRow2['itemdue']) . '</td>
		<td style="text-align:right;">' . $DisplayQty . '</td>
		<td>' . htmlspecialchars($DisplayUOM) . '</td>
		<td style="text-align:right;">' . $DisplayPrice . '</td>
		<td style="text-align:right;">' . locale_number_format($LineTotal, $MyRow['currdecimalplaces']) . '</td>
	</tr>';

	// Customer part and description
	if ($MyRow2['cust_part'] > '') {
		$lineItemsHtml .= '<tr>
			<td colspan="7">' . __('Customer Part') . ': ' . htmlspecialchars($MyRow2['cust_part']) . ' ' . htmlspecialchars($MyRow2['cust_description']) . '</td>
		</tr>';
	}

	// Narrative
	if (!empty($MyRow2['narrative'])) {
		$Split = explode("\r\n", wordwrap($MyRow2['narrative'], 130, "\r\n"));
		foreach ($Split as $TextLine) {
			$lineItemsHtml .= '<tr>
				<td colspan="7">' . htmlspecialchars($TextLine) . '</td>
			</tr>';
		}
	}
}

if ($ListCount == 0) {
	$Title = __('Print Acknowledgement Error');
	include('includes/header.php');
	echo '<p>' . __('There were no items on the Acknowledgement') . '. ' . __('The Acknowledgement cannot be printed') . '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?Acknowledgement=Quotes_only">Back</a></p>';
	include('includes/footer.php');
	exit();
}

if ($MyRow['comments'] == null) {
	$MyRow['comments'] = '';
}

// Build HTML for DomPDF
$HTML = '
<html>
<head>
	<style>
		body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
		table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
		th, td { border: 1px solid #333; padding: 4px; }
		th { background: #eee; }
		.totals td { border: none; }
		.notes { margin-top: 30px; }
	</style>
</head>
<body>
	<h2>' . __('Customer Acknowledgement') . '</h2>
	<p><strong>' . __('Acknowledgement No') . ':</strong> ' . $_GET['AcknowledgementNo'] . '</p>
	<p><strong>' . __('Customer') . ':</strong> ' . htmlspecialchars($MyRow['name']) . ' (' . htmlspecialchars($MyRow['debtorno']) . ')</p>
	<p><strong>' . __('Order Date') . ':</strong> ' . ConvertSQLDate($MyRow['orddate']) . '</p>
	<p><strong>' . __('Deliver To') . ':</strong> ' . htmlspecialchars($MyRow['deliverto']) . '<br>
		' . htmlspecialchars($MyRow['deladd1']) . '<br>
		' . htmlspecialchars($MyRow['deladd2']) . '<br>
		' . htmlspecialchars($MyRow['deladd3']) . '<br>
		' . htmlspecialchars($MyRow['deladd4']) . '<br>
		' . htmlspecialchars($MyRow['deladd5']) . '<br>
		' . htmlspecialchars($MyRow['deladd6']) . '
	</p>
	<table>
		<tr>
			<th>' . __('Item Code') . '</th>
			<th>' . __('Description') . '</th>
			<th>' . __('Due Date') . '</th>
			<th>' . __('Quantity') . '</th>
			<th>' . __('UOM') . '</th>
			<th>' . __('Unit Price') . '</th>
			<th>' . __('Line Total') . '</th>
		</tr>
		' . $lineItemsHtml . '
	</table>
	<table class="totals">
		<tr>
			<td style="text-align:right;" colspan="6">' . __('Total Excluding Tax') . ':</td>
			<td style="text-align:right;">' . locale_number_format($AcknowledgementTotalEx, $MyRow['currdecimalplaces']) . '</td>
		</tr>
		<tr>
			<td style="text-align:right;" colspan="6">' . __('Tax') . ':</td>
			<td style="text-align:right;">' . locale_number_format($TaxTotal, $MyRow['currdecimalplaces']) . '</td>
		</tr>
		<tr>
			<td style="text-align:right;" colspan="6">' . __('Freight') . ':</td>
			<td style="text-align:right;">' . locale_number_format($MyRow['freightcost'], $MyRow['currdecimalplaces']) . '</td>
		</tr>
		<tr>
			<td style="text-align:right;" colspan="6"><strong>' . __('Total Including Tax and Freight') . ':</strong></td>
			<td style="text-align:right;"><strong>' . locale_number_format($AcknowledgementTotal, $MyRow['currdecimalplaces']) . '</strong></td>
		</tr>
	</table>
	<div class="notes">
		<strong>' . __('Notes:') . '</strong>
		<p>' . nl2br(htmlspecialchars($MyRow['comments'])) . '</p>
	</div>
	<div class="terms">
		<strong>' . __('Terms & Conditions') . ':</strong>
		<p>' . nl2br(htmlspecialchars($Terms)) . '</p>
	</div>
</body>
</html>
';

// Output PDF using DomPDF
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_OrderAcknowledgement_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));

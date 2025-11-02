<?php

$AllowAnyone = true;

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

$FromCriteria = '1'; /*Category From */
$ToCriteria = 'zzzzzzzz'; /*Category To */
$Location = 'All'; /* Location to report on */
$DetailedReport = 'Yes'; /* Total by category or complete listing */

$_POST['DetailedReport'] = $DetailedReport;
$_POST['FromCriteria'] = $FromCriteria;
$_POST['ToCriteria'] = $ToCriteria;
$_POST['Location'] = $Location;

$Recipients = GetMailList('InventoryValuationRecipients');

if (sizeOf($Recipients) == 0) {
	$Title = __('Inventory Valuation') . ' - ' . __('Problem Report');
	include('includes/header.php');
	prnMsg(__('There are no members of the "InventoryValuationRecipients" email group'), 'warn');
	include('includes/footer.php');
	exit();
}

$SQL = "SELECT stockmaster.categoryid,
				stockcategory.categorydescription,
				stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) as qtyonhand,
				stockmaster.actualcost AS unitcost,
				SUM(locstock.quantity) *(stockmaster.actualcost) AS itemtotal
			FROM stockmaster,
				stockcategory,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
			GROUP BY stockmaster.categoryid,
				stockcategory.categorydescription,
				unitcost,
				stockmaster.stockid,
				stockmaster.description
			HAVING SUM(locstock.quantity)!=0
			AND stockmaster.categoryid >= '" . $FromCriteria . "'
			AND stockmaster.categoryid <= '" . $ToCriteria . "'
			ORDER BY stockmaster.categoryid,
				stockmaster.stockid";

$ErrMsg = __('The inventory valuation could not be retrieved');
$InventoryResult = DB_query($SQL, $ErrMsg);

$ListCount = DB_num_rows($InventoryResult);

$HTML = '';

$HTML .= '<html>
			<head>';
$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';

$HTML .= '<meta name="author" content="WebERP " . $Version">
			<meta name="Creator" content="webERP https://www.weberp.org">
		</head>
		<body>
			<div class="centre" id="ReportHeader">
				' . $_SESSION['CompanyRecord']['coyname'] . '<br />
				' . __('Inventory Valuation Report') . '<br />
				' . __('Printed') . ': ' . date($_SESSION['DefaultDateFormat']) . '<br />
			</div>
			<table>
				<thead>
					<tr>
						<th>' . __('Category') . '/' . __('Item') . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('Quantity') . '</th>
						<th>' . __('Cost Per Unit') . '</th>
						<th>' . __('Extended Cost') . '</th>
					</tr>
				</thead>
				<tbody>';

$Tot_Val = 0;
$Category = '';
$CatTot_Val = 0;
while ($InventoryValn = DB_fetch_array($InventoryResult)) {

	if ($Category != $InventoryValn['categoryid']) {

		if ($Category != '') { /*Then it's NOT the first time round */

			/* need to print the total of previous category */
			$DisplayCatTotVal = locale_number_format($CatTot_Val, 2);
			$HTML .= '<tr class="total_row">
						<td colspan="3"></td>
						<td>' . __('Total for') . ' ' . $Category . " - " . $CategoryName . '</td>
						<td class="number">' . $DisplayCatTotVal . '</td>
					</tr>';

			$CatTot_Val = 0;
		}
		$HTML .= '<tr class="total_row">
					<td colspan="5"><h3>' . $InventoryValn['categoryid'] . " - " . $InventoryValn['categorydescription'] . '</h3></td>
				</tr>';
		$Category = $InventoryValn['categoryid'];
		$CategoryName = $InventoryValn['categorydescription'];
	}

	$DisplayUnitCost = locale_number_format($InventoryValn['unitcost'], $_SESSION['CompanyRecord']['decimalplaces']);
	$DisplayQtyOnHand = locale_number_format($InventoryValn['qtyonhand'], 0);
	$DisplayItemTotal = locale_number_format($InventoryValn['itemtotal'], $_SESSION['CompanyRecord']['decimalplaces']);
	$HTML .= '<tr>
				<td>' . $InventoryValn['stockid'] . '</td>
				<td>' . $InventoryValn['description'] . '</td>
				<td class="number">' . $DisplayQtyOnHand . '</td>
				<td class="number">' . $DisplayUnitCost . '</td>
				<td class="number">' . $DisplayItemTotal . '</td>
			</tr>';

	$Tot_Val += $InventoryValn['itemtotal'];
	$CatTot_Val += $InventoryValn['itemtotal'];

} /*end inventory valn while loop */

$DisplayCatTotVal = locale_number_format($CatTot_Val, 2);
$HTML .= '<tr class="total_row">
			<td colspan="3"></td>
			<td>' . __('Total for') . ' ' . $Category . ' - ' . $CategoryName . '</td>
			<td class="number">' . $DisplayCatTotVal . '</td>
		</tr>';

$DisplayTotalVal = locale_number_format($Tot_Val, 2);
/*Print out the grand totals */
$HTML .= '<tr class="total_row">
			<td colspan="3"></td>
			<td>' . __('Grand Total Value') . '</td>
			<td class="number">' . $DisplayTotalVal . '</td>
		</tr>';

$HTML .= '</tbody>
		</table>';

if ($ListCount == 0) {
	$Title = __('Print Inventory Valuation Error');
	include('includes/header.php');
	echo '<p>' . __('There were no items with any value to print out for the location specified');
	echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');
	exit(); // Javier: needs check

} else {

	/// @todo we could skip generating the pdf if $From == ''
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);
	// (Optional) set up the paper size and orientation
	$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');
	// Render the HTML as PDF
	$DomPDF->render();
	// Output the generated PDF to a temporary file
	$output = $DomPDF->output();

	$PDFFileName = sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_InventoryValuation_' . date('Y-m-d') . '.pdf';
	file_put_contents($PDFFileName, $output);

	$From = $_SESSION['CompanyRecord']['email'];
	if ($From != '') {
		$Subject = __('Inventory Valuation Report');
		$Body = __('Please find herewith the stock valuation report');
		$ConfirmationText = __('Please find attached the Inventory Valuation report, generated by user') . ' ' . $_SESSION['UserID'] . ' ' . __('at') . ' ' . date('Y-m-d H:i:s');
		$EmailSubject = $_SESSION['DatabaseName'] . '_InventoryValuation_' . date('Y-m-d') . '.pdf';
		/// @todo drop this IF - it's handled within SendEmailFromWebERP
		if ($_SESSION['SmtpSetting'] == 0) {
			mail($_SESSION['InventoryManagerEmail'], $EmailSubject, $ConfirmationText);
		} else {
			$Result = SendEmailFromWebERP($From, $Recipients, $Subject, $Body, array($PDFFileName), false);
		}
	}
	unlink($PDFFileName);

	$Title = __('Send Report By Email');
	include('includes/header.php');

	if ($Result) {
		$Title = __('Print Inventory Valuation');
		prnMsg(__('The Inventory valuation report has been mailed'), 'success');
		echo '<div class="centre"><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a></div>';
	} else {
		$Title = __('Print Inventory Valuation Error');
		prnMsg(__('There are errors and the emails were not sent'), 'error');
		echo '<div class="centre"><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a></div>';
	}

	include('includes/footer.php');
}

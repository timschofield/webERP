<?php
$AllowAnyone = true;

$FromCriteria = '1'; /*Category From */
$ToCriteria = 'zzzzzzzz'; /*Category To */
$Location = 'All'; /* Location to report on */
$DetailedReport = 'Yes'; /* Total by category or complete listing */

$_POST['DetailedReport'] = $DetailedReport; /* so PDFInventoryValnPageHeader.php works too */
$_POST['FromCriteria'] = $FromCriteria; /* so PDFInventoryValnPageHeader.php works too */
$_POST['ToCriteria'] = $ToCriteria; /* so PDFInventoryValnPageHeader.php works too */
$_POST['Location'] = $Location; /* so PDFInventoryValnPageHeader.php works too */

include('includes/session.php');
use Dompdf\Dompdf;

$Recipients = GetMailList('InventoryValuationRecipients');

if (sizeOf($Recipients) == 0) {
	$Title = _('Inventory Valuation') . ' - ' . _('Problem Report');
	include('includes/header.php');
	prnMsg(_('There are no members of the "InventoryValuationRecipients" email group'), 'warn');
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

$InventoryResult = DB_query($SQL, '', '', false, true);
$ListCount = DB_num_rows($InventoryResult);

if (DB_error_no() != 0) {
	$Title = _('Inventory Valuation') . ' - ' . _('Problem Report');
	include('includes/header.php');
	echo _('The inventory valuation could not be retrieved by the SQL because') . ' - ' . DB_error_msg();
	echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
	if ($Debug == 1) {
		echo '<br />' . $SQL;
	}

	include('includes/footer.php');
	exit();
}

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
				' . _('Inventory Valuation Report') . '<br />
				' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
			</div>
			<table>
				<thead>
					<tr>
						<th>' . _('Category') . '/' . _('Item') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Cost Per Unit') . '</th>
						<th>' . _('Extended Cost') . '</th>
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
						<td>' . _('Total for') . ' ' . $Category . " - " . $CategoryName . '</td>
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
			<td>' . _('Total for') . ' ' . $Category . ' - ' . $CategoryName . '</td>
			<td class="number">' . $DisplayCatTotVal . '</td>
		</tr>';

$DisplayTotalVal = locale_number_format($Tot_Val, 2);
/*Print out the grand totals */
$HTML .= '<tr class="total_row">
			<td colspan="3"></td>
			<td>' . _('Grand Total Value') . '</td>
			<td class="number">' . $DisplayTotalVal . '</td>
		</tr>';

$HTML .= '</tbody>
		</table>';

if ($ListCount == 0) {
	$Title = _('Print Inventory Valuation Error');
	include('includes/header.php');
	echo '<p>' . _('There were no items with any value to print out for the location specified');
	echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
	include('includes/footer.php');
	exit(); // Javier: needs check

} else {
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to a temporary file
	$output = $dompdf->output();
	file_put_contents(sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_InventoryValuation_' . date('Y-m-d') . '.pdf', $output);
	$From = $_SESSION['CompanyRecord']['email'];
	$Subject = _('Inventory Valuation Report');
	$Body = _('Please find herewith the stock valuation report');
	if ($From != '') {
		$ConfirmationText = _('Please find attached the Reorder level report, generated by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
		$EmailSubject = $_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf';
		if ($_SESSION['SmtpSetting'] == 0) {
			mail($_SESSION['InventoryManagerEmail'], $EmailSubject, $ConfirmationText);
		} else {
			$Result = SendEmailFromWebERP($From, $Recipients, $Subject, $Body, array(sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_InventoryValuation_' . date('Y-m-d') . '.pdf'), false);
		}
		unlink(sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf');
	}
	$Title = _('Send Report By Email');
	include('includes/header.php');

	if ($Result) {
		$Title = _('Print Inventory Valuation');
		prnMsg(_('The Inventory valuation report has been mailed'), 'success');
		echo '<div class="centre"><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a></div>';
	} else {
		$Title = _('Print Inventory Valuation Error');
		prnMsg(_('There are errors and the emails were not sent'), 'error');
		echo '<div class="centre"><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a></div>';
	}

	include('includes/footer.php');
}

<?php

require(__DIR__ . '/includes/session.php');
require_once('vendor/autoload.php'); // Ensure DomPDF is autoloaded

use Dompdf\Dompdf;

// Fetch values from session and GET
$BatchNumber = $_GET['BatchNumber'];
$ReceiptNumber = $_GET['ReceiptNumber'];

// SQL Queries
$SQL = "SELECT MIN(id) as start FROM debtortrans WHERE type=12 AND transno='" . $BatchNumber . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$StartReceiptNumber = $MyRow['start'];

$SQL = "SELECT debtorno, ovamount, invtext FROM debtortrans WHERE type=12 AND transno='" . $BatchNumber . "' AND id='" . ($StartReceiptNumber - 1 + $ReceiptNumber) . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$DebtorNo = $MyRow['debtorno'];
$Amount = $MyRow['ovamount'];
$Narrative = $MyRow['invtext'];

$SQL = "SELECT currabrev, decimalplaces FROM currencies WHERE currabrev=(SELECT currcode FROM banktrans WHERE type=12 AND transno='" . $BatchNumber . "')";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$CurrencyCode = $MyRow['currabrev'];
$DecimalPlaces = $MyRow['decimalplaces'];

$SQL = "SELECT name, address1, address2, address3, address4, address5, address6 FROM debtorsmaster WHERE debtorno='" . $DebtorNo . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

$CustomerName = htmlspecialchars_decode($MyRow['name']);
$CustomerAddress = [
	htmlspecialchars_decode($MyRow['address1']),
	htmlspecialchars_decode($MyRow['address2']),
	htmlspecialchars_decode($MyRow['address3']),
	htmlspecialchars_decode($MyRow['address4']),
	htmlspecialchars_decode($MyRow['address5']),
	htmlspecialchars_decode($MyRow['address6'])
];

// Get company info from session
$Company = $_SESSION['CompanyRecord'];
$CompanyName = $Company['coyname'];
$CompanyAddress = [
	$Company['regoffice1'],
	$Company['regoffice2'],
	$Company['regoffice3'],
	$Company['regoffice4'],
	$Company['regoffice5'],
	$Company['regoffice6']
];

// Logo
$LogoFile = $_SESSION['LogoFile'];
$LogoBase64 = '';
if (file_exists($LogoFile)) {
	$LogoData = file_get_contents($LogoFile);
	$LogoBase64 = 'data:image/jpeg;base64,' . base64_encode($LogoData);
}

// Get currency name
include('includes/CurrenciesArray.php'); // $CurrencyName array
$CurrencyLongName = isset($CurrencyName[$CurrencyCode]) ? $CurrencyName[$CurrencyCode] : $CurrencyCode;

// Format amount
//require_once('includes/NumberFormat.php'); // ensure locale_number_format is available
$AmountFormatted = locale_number_format(-$Amount, $DecimalPlaces) . ' ' . $CurrencyCode . ' - ' . $CurrencyLongName;

// Date
$PrintedDate = date($_SESSION['DefaultDateFormat']);

// Build HTML for DomPDF
$HTML = '<!DOCTYPE html>
<html>
<head>';
$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
$HTML .= '<style>
		body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 10pt; }
		.header-logo { float: left; margin-right: 20px; }
		.company-info { margin-bottom: 20px; }
		.customer-info, .company-info { margin-left: 30px; }
		.section { margin-bottom: 18px; }
		.label { font-weight: bold; }
		.line { border-bottom: 1px solid #000; margin: 24px 0; }
		.signed { margin-top: 40px; }
	</style>';

$HTML .='</head>
<body>';
$HTML .= '<div><img class="logo" src="' . $_SESSION['LogoFile'] . '" /></div>';
$HTML .= '<div><span class="label">' . $CompanyName . '</span></div>';

foreach ($CompanyAddress as $line) {
	if (trim($line) != '') {
		$HTML .= '<div>' . $line . '</div>';
	}
}

$HTML .= '<div style="float:right; text-align:right;">
		<div><span class="label">' . __('Customer Receipt Number') . '</span> : ' . $BatchNumber . '/' . $ReceiptNumber . '</div>
		<div><span class="label">' . __('Printed') . '</span>: ' . $PrintedDate . ' &nbsp; <span class="label">' . __('Page') . '</span> 1</div>
	</div>
	<div style="clear:both;"></div>
	<div class="line"></div>
	<div class="section customer-info">
		<div><span class="label">' . __('Received From') . '</span> : ' . $CustomerName . '</div>';

foreach ($CustomerAddress as $AddressLine) {
	if (trim($AddressLine) != '') {
		$HTML .= '<div>' . $AddressLine . '</div>';
	}
}

$HTML .= '</div>
	<div class="section">
		<div><span class="label">' . __('The Sum Of') . '</span> : ' . $AmountFormatted . '</div>
	</div>
	<div class="section">
		<div><span class="label">' . __('Details') . '</span> : ' . $Narrative . '</div>
	</div>
	<div class="signed">
		<div><span class="label">' . __('Signed On Behalf Of') . '</span> : ' . $CompanyName . '</div>
	</div>
	<div class="line" style="margin-top:80px"></div>
</body>
</html>';

// Generate PDF with DomPDF
$PdfFileName = $_SESSION['DatabaseName'] . '_CustomerReceipt_No_' . $BatchNumber . ' - ' . $ReceiptNumber . ' _ ' . date('Y-m-d') . '.pdf';
// Display PDF in browser
$dompdf = new Dompdf(['chroot' => __DIR__]);
$dompdf->loadHtml($HTML);

$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($PdfFileName, array("Attachment" => false));

<?php

/* Hard coded for currencies with 2 decimal places */
include(__DIR__ . '/includes/DefinePaymentClass.php');
require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

if (isset($_GET['identifier'])){
	$identifier = $_GET['identifier'];
} else {
	prnMsg(__('Something was wrong without an identifier, please ask administrator for help'),'error');
	include('includes/footer.php');
	exit;
}

$Result = DB_query("SELECT hundredsname,
						   decimalplaces,
						   currency
					FROM currencies
					WHERE currabrev='" . $_SESSION['PaymentDetail' . $identifier]->Currency . "'");

if (DB_num_rows($Result) == 0){
	include ('includes/header.php');
	prnMsg(__('Can not get hundreds name'), 'warn');
	include ('includes/footer.php');
	exit;
}

$CurrencyRow = DB_fetch_array($Result);
$HundredsName = $CurrencyRow['hundredsname'];
$CurrDecimalPlaces = $CurrencyRow['decimalplaces'];
$CurrencyName = mb_strtolower($CurrencyRow['currency']);

$Amount = $_SESSION['PaymentDetail' . $identifier]->Amount;
$AmountWords = number_to_words($Amount) . ' ' . $CurrencyName;
$Cents = intval(round(($Amount - intval($Amount)) * 100, 0));
if ($Cents > 0){
	$AmountWords .= ' ' . __('and') . ' ' .  strval($Cents) . ' ' . $HundredsName;
} else {
	$AmountWords .= ' ' . __('only');
}

// Prepare address
$Address3 = $_SESSION['PaymentDetail' . $identifier]->Address3 . ' ' .
			$_SESSION['PaymentDetail' . $identifier]->Address4 . ' ' .
			$_SESSION['PaymentDetail' . $identifier]->Address5 . ' ' .
			$_SESSION['PaymentDetail' . $identifier]->Address6;

// Prepare HTML for DomPDF
$HTML = '
<style>
	body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 10pt; }
	.cheque-section { margin-bottom: 40px; }
	.cheque-header { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 20px; }
	.amount-words { margin-bottom: 10px; font-style: italic; }
	.row { display: flex; margin-bottom: 8px; }
	.label { width: 100px; font-weight: bold; }
	.value { flex: 1; }
	.remittance { margin-top: 30px; }
	.remittance-header { font-size: 12pt; text-align: center; margin-bottom: 10px; }
	table { width: 100%; border-collapse: collapse; margin-bottom: 10px;}
	th, td { border: 1px solid #000; padding: 4px 8px; font-size: 10pt; text-align: left;}
	th { background: #eee; }
</style>
<div class="cheque-section">
	<div class="cheque-header">' . __('Print Cheque') . '</div>
	<div class="row"><span class="label">' . __('Cheque No.') . ':</span><span class="value">' . htmlspecialchars($_GET['ChequeNum']) . '</span></div>
	<div class="row"><span class="label">' . __('Date Paid') . ':</span><span class="value">' . htmlspecialchars($_SESSION['PaymentDetail' . $identifier]->DatePaid) . '</span></div>
	<div class="row"><span class="label">' . __('Payee') . ':</span><span class="value">' . htmlspecialchars($_SESSION['PaymentDetail' . $identifier]->SuppName) . '</span></div>
	<div class="row"><span class="label">' . __('Address 1') . ':</span><span class="value">' . htmlspecialchars($_SESSION['PaymentDetail' . $identifier]->Address1) . '</span></div>
	<div class="row"><span class="label">' . __('Address 2') . ':</span><span class="value">' . htmlspecialchars($_SESSION['PaymentDetail' . $identifier]->Address2) . '</span></div>
	<div class="row"><span class="label">' . __('Address 3-6') . ':</span><span class="value">' . htmlspecialchars($Address3) . '</span></div>
	<div class="row"><span class="label">' . __('Amount') . ':</span><span class="value">' . locale_number_format($Amount, $CurrDecimalPlaces) . '</span></div>
	<div class="amount-words">' . htmlspecialchars($AmountWords) . '</div>
</div>

<div class="remittance">
	<div class="remittance-header">' . __('Remittance Advice') . '</div>
	<table>
		<tr>
			<th>' . __('Date Paid') . '</th>
			<th>' . __('Vendor No.') . '</th>
			<th>' . __('Cheque No.') . '</th>
			<th>' . __('Amount') . '</th>
		</tr>
		<tr>
			<td>' . htmlspecialchars($_SESSION['PaymentDetail' . $identifier]->DatePaid) . '</td>
			<td>' . htmlspecialchars($_SESSION['PaymentDetail' . $identifier]->SupplierID) . '</td>
			<td>' . htmlspecialchars($_GET['ChequeNum']) . '</td>
			<td>' . locale_number_format($Amount, $CurrDecimalPlaces) . '</td>
		</tr>
	</table>
	<div class="remittance-header">' . __('Remittance Advice') . '</div>
	<table>
		<tr>
			<th>' . __('Date Paid') . '</th>
			<th>' . __('Vendor No.') . '</th>
			<th>' . __('Cheque No.') . '</th>
			<th>' . __('Amount') . '</th>
		</tr>
		<tr>
			<td>' . htmlspecialchars($_SESSION['PaymentDetail' . $identifier]->DatePaid) . '</td>
			<td>' . htmlspecialchars($_SESSION['PaymentDetail' . $identifier]->SupplierID) . '</td>
			<td>' . htmlspecialchars($_GET['ChequeNum']) . '</td>
			<td>' . locale_number_format($Amount, $CurrDecimalPlaces) . '</td>
		</tr>
	</table>
</div>
';

// DomPDF options and generation
$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
$DomPDF->loadHtml($HTML);
$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');
$DomPDF->render();

$FileName = $_SESSION['DatabaseName'] . '_Cheque_' . date('Y-m-d') . '_ChequeNum_' . $_GET['ChequeNum'] . '.pdf';
$DomPDF->stream($FileName, ['Attachment' => false]);

exit;
/* ****************************************************************************************** */

function number_to_words($Number) {

	if (($Number < 0) OR ($Number > 999999999)) {
		prnMsg(__('Number is out of the range of numbers that can be expressed in words'),'error');
		return __('error');
	}

	$Millions = floor($Number / 1000000);
	$Number -= $Millions * 1000000;
	$Thousands = floor($Number / 1000);
	$Number -= $Thousands * 1000;
	$Hundreds = floor($Number / 100);
	$Number -= $Hundreds * 100;
	$NoOfTens = floor($Number / 10);
	if (isset($Number) && is_numeric($Number)) {
		$NoOfOnes = ((int)$Number) % 10;
	} else {
		$NoOfOnes = 0; // Default value if $Number is not set or not numeric
	}

	$NumberInWords = '';

	if ($Millions) {
		$NumberInWords .= number_to_words($Millions) . ' ' . __('million');
	}

	if ($Thousands) {
		$NumberInWords .= (empty($NumberInWords) ? '' : ' ') . number_to_words($Thousands) . ' ' . __('thousand');
	}

	if ($Hundreds) {
		$NumberInWords .= (empty($NumberInWords) ? '' : ' ') . number_to_words($Hundreds) . ' ' . __('hundred');
	}

	$Ones = array( 0 => '',
				   1 => __('one'),
				   2 => __('two'),
				   3 => __('three'),
				   4 => __('four'),
				   5 => __('five'),
				   6 => __('six'),
				   7 => __('seven'),
				   8 => __('eight'),
				   9 => __('nine'),
				   10 => __('ten'),
				   11 => __('eleven'),
				   12 => __('twelve'),
				   13 => __('thirteen'),
				   14 => __('fourteen'),
				   15 => __('fifteen'),
				   16 => __('sixteen'),
				   17 => __('seventeen'),
				   18 => __('eighteen'),
				   19 => __('nineteen') );

	$Tens = array( 0 => '',
				   1 => '',
				   2 => __('twenty'),
				   3 => __('thirty'),
				   4 => __('forty'),
				   5 => __('fifty'),
				   6 => __('sixty'),
				   7 => __('seventy'),
				   8 => __('eighty'),
				   9 => __('ninety') );


	if ($NoOfTens OR $NoOfOnes) {
		if (!empty($NumberInWords)) {
			$NumberInWords .= ' ' . __('and') . ' ';
		}

		if ($NoOfTens < 2){
			$NumberInWords .= $Ones[$NoOfTens * 10 + $NoOfOnes];
		}
		else {
			$NumberInWords .= $Tens[$NoOfTens];
			if ($NoOfOnes) {
				$NumberInWords .= '-' . $Ones[$NoOfOnes];
			}
		}
	}

	if (empty($NumberInWords)){
		$NumberInWords = __('zero');
	}

	return $NumberInWords;
}


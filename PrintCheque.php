<?php

/* Hard coded for currencies with 2 decimal places */

require(__DIR__ . '/includes/session.php');

include('includes/DefinePaymentClass.php');

if (isset($_GET['identifier'])){
	$identifier = $_GET['identifier'];
}else{
	prnMsg(__('Something was wrong without an identifier, please ask administrator for help'),'error');
	include('includes/footer.php');
	exit();
}
include('includes/PDFStarter.php');
$pdf->addInfo('Title', __('Print Cheque'));
$pdf->addInfo('Subject', __('Print Cheque'));
$FontSize=10;
$PageNumber=1;
$LineHeight=12;

$Result = DB_query("SELECT hundredsname,
                           decimalplaces,
                           currency
                    FROM currencies
                    WHERE currabrev='" . $_SESSION['PaymentDetail' . $identifier]->Currency . "'");

if (DB_num_rows($Result) == 0){
	include('includes/header.php');
	prnMsg(__('Can not get hundreds name'), 'warn');
	include('includes/footer.php');
	exit();
}

$CurrencyRow = DB_fetch_array($Result);
$HundredsName = $CurrencyRow['hundredsname'];
$CurrDecimalPlaces = $CurrencyRow['decimalplaces'];
$CurrencyName = mb_strtolower($CurrencyRow['currency']);

// cheque
$YPos= $Page_Height-5*$LineHeight;
$pdf->addTextWrap($Page_Width-75,$YPos,100,$FontSize,$_GET['ChequeNum'], 'left');
$YPos -= 3*$LineHeight;

$AmountWords = number_to_words($_SESSION['PaymentDetail' . $identifier]->Amount) . ' ' . $CurrencyName;
$Cents = intval(round(($_SESSION['PaymentDetail' . $identifier]->Amount - intval($_SESSION['PaymentDetail' . $identifier]->Amount))*100,0));
if ($Cents > 0){
	$AmountWords .= ' ' . __('and') . ' ' .  strval($Cents) . ' ' . $HundredsName;
} else {
	$AmountWords .= ' ' . __('only');
}

$pdf->addTextWrap(75,$YPos,475,$FontSize,$AmountWords, 'left');
$YPos -= 1*$LineHeight;
$pdf->addTextWrap($Page_Width-225,$YPos,100,$FontSize,$_SESSION['PaymentDetail' . $identifier]->DatePaid, 'left');
$pdf->addTextWrap($Page_Width-75,$YPos,75,$FontSize,locale_number_format($_SESSION['PaymentDetail' . $identifier]->Amount,$CurrDecimalPlaces), 'left');

$YPos -= 1*$LineHeight;
$pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail' . $identifier]->SuppName, 'left');
$YPos -= 1*$LineHeight;
$pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail' . $identifier]->Address1, 'left');
$YPos -= 1*$LineHeight;
$pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail' . $identifier]->Address2, 'left');
$YPos -= 1*$LineHeight;
$Address3 = $_SESSION['PaymentDetail' . $identifier]->Address3 . ' ' . $_SESSION['PaymentDetail' . $identifier]->Address4 . ' ' . $_SESSION['PaymentDetail' . $identifier]->Address5 . ' ' . $_SESSION['PaymentDetail' . $identifier]->Address6;
$pdf->addTextWrap(75,$YPos,300,$FontSize, $Address3, 'left');


$YPos -= 2*$LineHeight;
$pdf->addTextWrap(75,$YPos,300,$FontSize, $AmountWords, 'left');
$pdf->addTextWrap(375,$YPos,100,$FontSize, locale_number_format($_SESSION['PaymentDetail' . $identifier]->Amount,$CurrDecimalPlaces), 'right');


// remittance advice 1
$YPos -= 14*$LineHeight;
$pdf->addTextWrap(0,$YPos,$Page_Width,$FontSize,__('Remittance Advice'), 'center');
$YPos -= 2*$LineHeight;
$pdf->addTextWrap(25,$YPos,75,$FontSize,__('DatePaid'), 'left');
$pdf->addTextWrap(100,$YPos,100,$FontSize,__('Vendor No.'), 'left');
$pdf->addTextWrap(250,$YPos,75,$FontSize,__('Cheque No.'), 'left');
$pdf->addTextWrap(350,$YPos,75,$FontSize,__('Amount'), 'left');
$YPos -= 2*$LineHeight;
$pdf->addTextWrap(25,$YPos,75,$FontSize,$_SESSION['PaymentDetail' . $identifier]->DatePaid, 'left');
$pdf->addTextWrap(100,$YPos,100,$FontSize,$_SESSION['PaymentDetail' . $identifier]->SupplierID, 'left');
$pdf->addTextWrap(250,$YPos,75,$FontSize,$_GET['ChequeNum'], 'left');
$pdf->addTextWrap(350,$YPos,75,$FontSize,locale_number_format($_SESSION['PaymentDetail' . $identifier]->Amount,$CurrDecimalPlaces), 'left');

// remittance advice 2
$YPos -= 15*$LineHeight;
$pdf->addTextWrap(0,$YPos,$Page_Width,$FontSize,__('Remittance Advice'), 'center');
$YPos -= 2*$LineHeight;
$pdf->addTextWrap(25,$YPos,75,$FontSize,__('DatePaid'), 'left');
$pdf->addTextWrap(100,$YPos,100,$FontSize,__('Vendor No.'), 'left');
$pdf->addTextWrap(250,$YPos,75,$FontSize,__('Cheque No.'), 'left');
$pdf->addTextWrap(350,$YPos,75,$FontSize,__('Amount'), 'left');
$YPos -= 2*$LineHeight;
$pdf->addTextWrap(25,$YPos,75,$FontSize,$_SESSION['PaymentDetail' . $identifier]->DatePaid, 'left');
$pdf->addTextWrap(100,$YPos,100,$FontSize,$_SESSION['PaymentDetail' . $identifier]->SupplierID, 'left');
$pdf->addTextWrap(250,$YPos,75,$FontSize,$_GET['ChequeNum'], 'left');
$pdf->addTextWrap(350,$YPos,75,$FontSize,locale_number_format($_SESSION['PaymentDetail' . $identifier]->Amount,$CurrDecimalPlaces), 'left');

$pdf->OutputD($_SESSION['DatabaseName'] . '_Cheque_' . date('Y-m-d') . '_ChequeNum_' . $_GET['ChequeNum'] . '.pdf');
$pdf->__destruct();

exit();
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
	$NoOfOnes = $Number % 10;

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

	$Ones = array(	0 => '',
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
					19 => __('nineteen')	);

	$Tens = array(	0 => '',
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

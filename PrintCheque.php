<?php

/*$Id$*/

/* $Revision: 1.5 $ */

$PageSecurity = 5;

include('includes/DefinePaymentClass.php');
include('includes/session.inc');
include('Numbers/Words.php');

include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Print Cheque'));
$pdf->addInfo('Subject', _('Print Cheque'));
$FontSize=10;
$PageNumber=1;
$line_height=12;

$result = db_query("SELECT hundredsname FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->Currency . "'",$db);

If (DB_num_rows($result) == 0){
	include ('includes/header.inc');
	prnMsg(_('Can not get hundreds name'), 'warn');
	include ('includes/footer.inc');
	exit;
}

$CurrencyRow = db_fetch_row($result);
$HundredsName = $CurrencyRow[0];

// cheque
$YPos= $Page_Height-5*$line_height;
$LeftOvers = $pdf->addTextWrap($Page_Width-75,$YPos,100,$FontSize,$_GET['ChequeNum'], 'left');					
$YPos -= 3*$line_height;


$LeftOvers = $pdf->addTextWrap(75,$YPos,475,$FontSize,$AmountWords, 'left');					
$YPos -= 1*$line_height;
$LeftOvers = $pdf->addTextWrap($Page_Width-225,$YPos,100,$FontSize,$_SESSION['PaymentDetail']->DatePaid, 'left');					
$LeftOvers = $pdf->addTextWrap($Page_Width-75,$YPos,75,$FontSize,number_format(round($_SESSION['PaymentDetail']->Amount,2),2), 'left');

$YPos -= 1*$line_height;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail']->SuppName, 'left');					
$YPos -= 1*$line_height;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail']->Address1, 'left');					
$YPos -= 1*$line_height;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail']->Address2, 'left');					
$YPos -= 1*$line_height;
$Address3 = $_SESSION['PaymentDetail']->Address3 . ' ' . $_SESSION['PaymentDetail']->Address4 . ' ' . $_SESSION['PaymentDetail']->Address5 . ' ' . $_SESSION['PaymentDetail']->Address6;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize, $Address3, 'left');					

$AmountWords = Numbers_Words::toWords(intval($_SESSION['PaymentDetail']->Amount),$Locale);
$AmountWords .= ' ' . _('and') . ' ' .  Numbers_Words::toWords(intval(($_SESSION['PaymentDetail']->Amount - intval($_SESSION['PaymentDetail']->Amount))*100),$Locale) . ' ' . $HundredsName;

$YPos -= 2*$line_height;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize, $AmountWords, 'left');
$LeftOvers = $pdf->addTextWrap(375,$YPos,100,$FontSize, number_format($_SESSION['PaymentDetail']->Amount,2), 'right');




// remittance advice 1
$YPos -= 14*$line_height;
$LeftOvers = $pdf->addTextWrap(0,$YPos,$Page_Width,$FontSize,_('Remittance Advice'), 'center');					
$YPos -= 2*$line_height;
$LeftOvers = $pdf->addTextWrap(25,$YPos,75,$FontSize,_('DatePaid'), 'left');					
$LeftOvers = $pdf->addTextWrap(100,$YPos,100,$FontSize,_('Vendor No.'), 'left');					
$LeftOvers = $pdf->addTextWrap(250,$YPos,75,$FontSize,_('Cheque No.'), 'left');					
$LeftOvers = $pdf->addTextWrap(350,$YPos,75,$FontSize,_('Amount'), 'left');					
$YPos -= 2*$line_height;
$LeftOvers = $pdf->addTextWrap(25,$YPos,75,$FontSize,$_SESSION['PaymentDetail']->DatePaid, 'left');					
$LeftOvers = $pdf->addTextWrap(100,$YPos,100,$FontSize,$_SESSION['PaymentDetail']->SupplierID, 'left');					
$LeftOvers = $pdf->addTextWrap(250,$YPos,75,$FontSize,$_GET['ChequeNum'], 'left');					
$LeftOvers = $pdf->addTextWrap(350,$YPos,75,$FontSize,number_format(round($_SESSION['PaymentDetail']->Amount,2),2), 'left');					

// remittance advice 2
$YPos -= 15*$line_height;
$LeftOvers = $pdf->addTextWrap(0,$YPos,$Page_Width,$FontSize,_('Remittance Advice'), 'center');					
$YPos -= 2*$line_height;
$LeftOvers = $pdf->addTextWrap(25,$YPos,75,$FontSize,_('DatePaid'), 'left');					
$LeftOvers = $pdf->addTextWrap(100,$YPos,100,$FontSize,_('Vendor No.'), 'left');					
$LeftOvers = $pdf->addTextWrap(250,$YPos,75,$FontSize,_('Cheque No.'), 'left');					
$LeftOvers = $pdf->addTextWrap(350,$YPos,75,$FontSize,_('Amount'), 'left');					
$YPos -= 2*$line_height;
$LeftOvers = $pdf->addTextWrap(25,$YPos,75,$FontSize,$_SESSION['PaymentDetail']->DatePaid, 'left');					
$LeftOvers = $pdf->addTextWrap(100,$YPos,100,$FontSize,$_SESSION['PaymentDetail']->SupplierID, 'left');					
$LeftOvers = $pdf->addTextWrap(250,$YPos,75,$FontSize,$_GET['ChequeNum'], 'left');
$LeftOvers = $pdf->addTextWrap(350,$YPos,75,$FontSize,number_format(round($_SESSION['PaymentDetail']->Amount,2),2), 'left');					
/* UldisN
$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ($len<=1){
	$title = _('Print Check Error');
	include('includes/header.inc');
	prnMsg(_('Could not print the cheque'),'warn');
	include('includes/footer.inc');
	exit;
} else {
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=Cheque.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	$pdf->Output('PrintCheque.pdf', 'I');
}
*/
$pdf->OutputD($_SESSION['DatabaseName'] . '_Cheque_' . date('Y-m-d') . '_ChequeNum_' . $_GET['ChequeNum'] . '.pdf');//UldisN
$pdf->__destruct(); //UldisN
?>

<?php

/* $Revision: 1.1 $ */

$PageSecurity = 5;

include('includes/DefinePaymentClass.php');
include('includes/session.inc');
include('includes/PDFStarter.php');

$FontSize=10;

$pdf->addinfo('Title', _('Print Cheque'));
$pdf->addinfo('Subject', _('Print Cheque'));

$PageNumber=1;

$line_height=12;

// cheque
$FontSize=10;
$YPos= $Page_Height-5*$line_height;
$LeftOvers = $pdf->addTextWrap($Page_Width-75,$YPos,100,$FontSize,$_GET['ChequeNum'], 'left');					
$YPos -= 3*$line_height;
$AmountWords = ConvertNumberToWords(round($_SESSION['PaymentDetail']->Amount,2));
$LeftOvers = $pdf->addTextWrap(75,$YPos,475,$FontSize,$AmountWords, 'left');					
$YPos -= 1*$line_height;
$LeftOvers = $pdf->addTextWrap($Page_Width-225,$YPos,100,$FontSize,$_SESSION['PaymentDetail']->DatePaid, 'left');					
$LeftOvers = $pdf->addTextWrap($Page_Width-75,$YPos,75,$FontSize,number_format(round($_SESSION['PaymentDetail']->Amount,2),2), 'left');
//$AmountWords = ConvertNumberToWords($_SESSION['PaymentDetail']->Amount);
$YPos -= 1*$line_height;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail']->SuppName, 'left');					
$YPos -= 1*$line_height;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail']->Address1, 'left');					
$YPos -= 1*$line_height;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize,$_SESSION['PaymentDetail']->Address2, 'left');					
$YPos -= 1*$line_height;
$Address3 = $_SESSION['PaymentDetail']->Address3 . ' ' . $_SESSION['PaymentDetail']->Address4 . ' ' . $_SESSION['PaymentDetail']->Address5 . ' ' . $_SESSION['PaymentDetail']->Address6;
$LeftOvers = $pdf->addTextWrap(75,$YPos,300,$FontSize, $Address3, 'left');					

// remittance advice 1
$YPos -= 16*$line_height;
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
	$pdf->Stream();
}
?>
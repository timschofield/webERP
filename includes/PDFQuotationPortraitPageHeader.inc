<?php
/*	$Id: PDFQuotationPortraitPageHeader.inc 6822 2014-08-15 20:24:57Z rchacon $*/

/*	Please note that addTextWrap prints a font-size-height further down than
	addText and other functions.*/

// $PageNumber is initialised in 0 by includes/PDFStarter.php.
$PageNumber ++;// Increments $PageNumber before printing.
if ($PageNumber>1) {// Inserts a page break if it is not the first page.
	$pdf->newPage();
}

// Prints company logo:
$XPos = $Page_Width/2 - 140;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos+90,720,0,60);

// Prints 'Quotation' title:
$pdf->addTextWrap(0, $Page_Height-$Top_Margin-18, $Page_Width, 18, _('Quotation'), 'center');

// Prints company info:
$XPos = $Page_Width/2+$Left_Margin;
$YPos = 720;
$FontSize = 12;
$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);
$YPos -= $FontSize;
$FontSize = 10;
$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($XPos, $YPos-$FontSize*1, $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($XPos, $YPos-$FontSize*2, $FontSize, $_SESSION['CompanyRecord']['regoffice3']);
$pdf->addText($XPos, $YPos-$FontSize*3, $FontSize, $_SESSION['CompanyRecord']['regoffice4']);
$pdf->addText($XPos, $YPos-$FontSize*4, $FontSize, $_SESSION['CompanyRecord']['regoffice5'] .
	' ' . $_SESSION['CompanyRecord']['regoffice6']);
$pdf->addText($XPos, $YPos-$FontSize*5, $FontSize,  _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] .
	' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
$pdf->addText($XPos, $YPos-$FontSize*6, $FontSize, $_SESSION['CompanyRecord']['email']);

// Prints 'Delivery To' info:
$XPos = 46;
$YPos = 770;
$FontSize=12;
$myrow = array_map(html_entity_decode, $myrow);
$pdf->addText($XPos, $YPos+10,$FontSize, _('Delivery To').':' );
$pdf->addText($XPos, $YPos- 3,$FontSize, $myrow['deliverto']);
$pdf->addText($XPos, $YPos-15,$FontSize, $myrow['deladd1']);
$pdf->addText($XPos, $YPos-30,$FontSize, $myrow['deladd2']);
$pdf->addText($XPos, $YPos-45,$FontSize, $myrow['deladd3'] . ' ' . $myrow['deladd4'] . ' ' . $myrow['deladd5']);

// Prints 'Quotation For' info:
$YPos -= 80;
$pdf->addText($XPos, $YPos,$FontSize, _('Quotation For').':');
$pdf->addText($XPos, $YPos-15,$FontSize, $myrow['name']);
$pdf->addText($XPos, $YPos-30,$FontSize, $myrow['address1']);
$pdf->addText($XPos, $YPos-45,$FontSize, $myrow['address2']);
$pdf->addText($XPos, $YPos-60,$FontSize, $myrow['address3'] . ' ' . $myrow['address4'] . ' ' . $myrow['address5']);

// Draws a box with round corners around 'Delivery To' info:
$XPos = 50;
$YPos += 25;
$pdf->RoundRectangle(
	$XPos-10,// RoundRectangle $XPos.
	$YPos+60+10,// RoundRectangle $YPos.
	200+10+10,// RoundRectangle $Width.
	60+10+10,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.

// Draws a box with round corners around around 'Quotation For' info:
$YPos -= 90;
$pdf->RoundRectangle(
	$XPos-10,// RoundRectangle $XPos.
	$YPos+60+10,// RoundRectangle $YPos.
	200+10+10,// RoundRectangle $Width.
	60+10+10,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.

// Prints quotation info:
$pdf->addTextWrap($Page_Width-$Right_Margin-200, $Page_Height-$Top_Margin-$FontSize*1, 200, $FontSize, _('Number'). ': '.$_GET['QuotationNo'], 'right');
$pdf->addTextWrap($Page_Width-$Right_Margin-200, $Page_Height-$Top_Margin-$FontSize*2, 200, $FontSize, _('Your Ref'). ': '.$myrow['customerref'], 'right');
$pdf->addTextWrap($Page_Width-$Right_Margin-200, $Page_Height-$Top_Margin-$FontSize*3, 200, $FontSize, _('Date'). ': '.ConvertSQLDate($myrow['quotedate']), 'right');
$pdf->addTextWrap($Page_Width-$Right_Margin-200, $Page_Height-$Top_Margin-$FontSize*4, 200, $FontSize, _('Page').': '.$PageNumber, 'right');

$FontSize=10;

// Prints the currency name:
require_once('includes/CurrenciesArray.php');// To get the currency name from the currency code.
$pdf->addText($Page_Width/2+$Left_Margin, $YPos+5, $FontSize,
	_('All amounts stated in') . ' ' . $myrow['currcode'] . ' - ' . $CurrencyName[$myrow['currcode']]);

// Prints table header:
$YPos -= 45;
$XPos = 40;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,100,$FontSize, _('Item Code'));
$LeftOvers = $pdf->addTextWrap(120,$YPos,235,$FontSize, _('Item Description'));
$LeftOvers = $pdf->addTextWrap(180,$YPos,85,$FontSize, _('Quantity'),'right');
$LeftOvers = $pdf->addTextWrap(230,$YPos,85,$FontSize,_('Price'),'right');
$LeftOvers = $pdf->addTextWrap(280,$YPos,85,$FontSize, _('Discount'),'right');
$LeftOvers = $pdf->addTextWrap(330,$YPos,85,$FontSize, _('Tax Class'),'right');
$LeftOvers = $pdf->addTextWrap(400,$YPos,85,$FontSize, _('Tax Amount'),'right');
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-90, $YPos, 90, $FontSize, _('Total'),'right');

// Draws a box with round corners around line items:
$pdf->RoundRectangle(
	$Left_Margin,// RoundRectangle $XPos.
	$YPos+$FontSize+5,// RoundRectangle $YPos.
	$Page_Width-$Left_Margin-$Right_Margin,// RoundRectangle $Width.
	$YPos+$FontSize-$Bottom_Margin+5,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.

// Line under table headings:
$LineYPos = $YPos - $FontSize -1;
$pdf->line($Page_Width-$Right_Margin, $LineYPos, $Left_Margin, $LineYPos);

$YPos -= $FontSize;// This is to use addTextWrap's $YPos instead of normal $YPos.

?>

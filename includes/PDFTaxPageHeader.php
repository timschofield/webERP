<?php

/*	Please note that addTextWrap() YPos is a font-size-height further down than
	addText() and other functions. Use addText() instead of addTextWrap() to
	print left aligned elements.*/

// $PageNumber is initialised in 0 by includes/PDFStarter.php.
$PageNumber ++;// Increments $PageNumber before printing.
if ($PageNumber>1) {// Inserts a page break if it is not the first page.
	$pdf->newPage();
}

$YPos = $Page_Height-$Top_Margin;
$FontSizeLast = $FontSize;// To preserve the main font size.

$FontSize = 10;
$pdf->addText($Left_Margin, $YPos, $FontSize,
	$_SESSION['CompanyRecord']['coyname']);// Prints company name.
$pdf->addTextWrap($Page_Width-$Right_Margin-140, $YPos-$FontSize, 140, $FontSize,
	__('Page'). ' ' . $PageNumber, 'right');// Page number.
$YPos -= $FontSize;

$pdf->addText($Left_Margin, $YPos, $FontSize,
	__('Tax Report') . ': ' . $TaxAuthorityName);// Prints tax authority name.
$pdf->addTextWrap($Page_Width-$Right_Margin-140, $YPos-$FontSize, 140, $FontSize,
	__('Printed') . ': ' . date($_SESSION['DefaultDateFormat']), 'right');// Date printed.
$YPos -= $FontSize;

$pdf->addText($Left_Margin, $YPos, $FontSize,
	$_POST['NoOfPeriods'] . ' ' . __('months to') . ' ' . $PeriodEnd);// Prints NoOfPeriods and PeriodEnd.
$pdf->addTextWrap($Page_Width-$Right_Margin-140, $YPos-$FontSize, 140, $FontSize,
	date('H:i:s'), 'right');// Time printed.
$YPos -= $FontSize;

$FontSize = $FontSizeLast;// Resets to the main font size.

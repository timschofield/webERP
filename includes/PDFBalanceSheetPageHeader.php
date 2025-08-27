<?php

/*
 * PDF page header for the balance sheet report.
 * Suren Naidu 10/08/2005
 */

$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize = 8;
$YPos = $Page_Height - $Top_Margin;
$pdf->addText($Left_Margin,$YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -= $LineHeight;
$FontSize =10;
$Heading = __('Balance Sheet as at').' ' . $BalanceDate;
$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize,
	__('Printed'). ': ' . Date($_SESSION['DefaultDateFormat'])
	. '   '. __('Page'). ' ' . $PageNumber);

$YPos -= (2 * $LineHeight);
$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,$BalanceDate,'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,__('Last Year'),'right');
$YPos -= (2 * $LineHeight);

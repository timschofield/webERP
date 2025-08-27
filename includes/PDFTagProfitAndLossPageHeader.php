<?php

/*
 * PDF page header for the profit and loss report.
 * Suren Naidu 28/08/2005
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
$pdf->setFont('','B');
$Heading = __('Profit and loss for the ') . $NumberOfMonths . __(' months to ') . __('and including ') . $PeriodToDate;
$Heading1 = __('Transactions for tag') . ':     '.$_POST['tag'].' - '.$Tag;
$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);
$pdf->addText($Left_Margin, $YPos-$LineHeight, $FontSize, $Heading1);

$FontSize = 8;
$pdf->setFont('','');
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize,
	__('Printed'). ': ' . Date($_SESSION['DefaultDateFormat'])
	. '   '. __('Page'). ' ' . $PageNumber);

$YPos -= (3 * $LineHeight);
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,__('Account'));
$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,100,$FontSize,__('Account Name'));
$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,__('Period Actual'),'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,__('Period Budget'),'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,__('Last Year'),'right');
$YPos -= (3 * $LineHeight);

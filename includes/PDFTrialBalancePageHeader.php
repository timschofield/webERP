<?php

/*
 * PDF page header for the trial balance report.
 * Suren Naidu 18/08/2005
 */

$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize = 8;
$YPos = $Page_Height - $Top_Margin;
$pdf->setFont('','');
$pdf->addText($Left_Margin,$YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, __('Printed'). ': ' . Date($_SESSION['DefaultDateFormat'])  . '   ' . __('Page'). ' ' . $PageNumber);

$YPos -= $LineHeight;
$FontSize =10;
$pdf->setFont('','B');
$Heading = __('Trial Balance for the month of ') . $PeriodToDate . __(' and for the ') . $NumberOfMonths . __(' months to ') . $PeriodToDate;
$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);

$YPos -= (2 * $LineHeight);
$FontSize = 8;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,__('Account'));
$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,100,$FontSize,__('Account Name'));
$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,70,$FontSize,__('Month Actual'),'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,__('Month Budget'),'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,__('Period Actual'),'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,__('Period Budget'),'right');
$pdf->setFont('','');
$YPos -= (2 * $LineHeight);

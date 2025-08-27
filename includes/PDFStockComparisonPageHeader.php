<?php

/*PDF page header for inventory check report */

if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=12;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize, __('Printed'). ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page').' ' . $PageNumber);

$YPos -=15;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize, __('Stock Check Comparison at') . ' ' . $LocationName );


$YPos -=15;
/*Draw a rectangle to put the headings in     */
$BoxHeight =10;

$pdf->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight);
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Left_Margin, $YPos- $BoxHeight);
$pdf->line($Left_Margin, $YPos-$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);

/*set up the headings */
$Xpos = $Left_Margin+1;

$YPos -=3;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize, __('Item'), 'centre');
$LeftOvers = $pdf->addTextWrap(330,$YPos,60,$FontSize, __('QOH'), 'centre');
$LeftOvers = $pdf->addTextWrap(330+41,$YPos,60,$FontSize, __('Counted'), 'centre');
$LeftOvers = $pdf->addTextWrap(330+41+61,$YPos,60,$FontSize, __('Reference'), 'centre');
$LeftOvers = $pdf->addTextWrap(330+41+61+60,$YPos,70,$FontSize, __('Adjustment'), 'centre');

$FontSize=10;
$YPos -=(2*$LineHeight);

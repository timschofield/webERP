<?php

/* PDF page header for aged analysis reports */

$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;

$pdf->addText($Left_Margin, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

$YPos -=$LineHeight;

$FontSize =10;
$pdf->addText($Left_Margin, $YPos,$FontSize, __('Aged Supplier Balances For Suppliers from') . ' ' . $_POST['FromCriteria'] . ' ' . __('to') . ' ' . $_POST['ToCriteria']);
$pdf->addText($Left_Margin, $YPos-$LineHeight,$FontSize, __('And Trading in') . ' ' . $_POST['Currency']);

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, __('Printed') . ': ' . Date("d M Y") . '  ' .__('Page') . ' ' . $PageNumber);

$YPos -=(3*$LineHeight);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Left_Margin, $YPos+$LineHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,220 - $Left_Margin,$FontSize,__('Supplier'),'centre');
$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,__('Balance'),'centre');
$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,__('Current'),'centre');
$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,__('Due Now'),'centre');
$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,"> " . $_SESSION['PastDueDays1'] . ' ' . __('Days Over'),'centre');
$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,"> " . $_SESSION['PastDueDays2'] . ' ' . __('Days Over'),'centre');

$YPos =$YPos - (2*$LineHeight);

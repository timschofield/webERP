<?php
/* $Revision: 1.2 $ */
/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=12;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,"Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=15;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,"Check Sheets for Categories between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria'] . " for stock at " . $_POST['Location']);

$YPos -=20;
/*Draw a rectangle to put the headings in     */
$BoxHeight =15;

$pdf->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight);
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Left_Margin, $YPos- $BoxHeight);
$pdf->line($Left_Margin, $YPos-$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,"Item", "centre");
$LeftOvers = $pdf->addTextWrap(341,$YPos,60,$FontSize,"QOH", "centre");
$LeftOvers = $pdf->addTextWrap(341+61,$YPos,80,$FontSize,"Cust Ords", "centre");
$LeftOvers = $pdf->addTextWrap(341+61+61,$YPos,80,$FontSize,"Available", "centre");

$FontSize=10;
$YPos -=($line_height);
?>

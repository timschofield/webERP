<?php

/*PDF page header for inventory planning report */

if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);

$YPos -=$line_height;

$FontSize=10;
if ($_POST["Location"]=="All"){

	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos,450,$FontSize,"Inventory Planning for Categories Between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria'] . " for all stock locations");

} else {

	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos,450,$FontSize,"Inventory Planning for Categories Between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria'] . " for stock at " . $_POST['Location']);

}

$FontSize=8;
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,"Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$XPos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($XPos,$YPos,180,$FontSize,"Item","centre");
$LeftOvers = $pdf->addTextWrap(251,$YPos,40,$FontSize,$Period_4_Name . " Qty","centre");
$LeftOvers = $pdf->addTextWrap(292,$YPos,40,$FontSize,$Period_3_Name . " Qty","centre");
$LeftOvers = $pdf->addTextWrap(333,$YPos,40,$FontSize,$Period_2_Name . " Qty","centre");
$LeftOvers = $pdf->addTextWrap(374,$YPos,40,$FontSize,$Period_1_Name . " Qty","centre");
$LeftOvers = $pdf->addTextWrap(415,$YPos,40,$FontSize,$Period_0_Name . " MTD","centre");

$LeftOvers = $pdf->addTextWrap(456,$YPos,40,$FontSize,$_POST["NumberMonthsHolding"] . " ms stk","centre");
$LeftOvers = $pdf->addTextWrap(597,$YPos,40,$FontSize,"QOH","centre");
$LeftOvers = $pdf->addTextWrap(638,$YPos,40,$FontSize,"Cust Ords","centre");
$LeftOvers = $pdf->addTextWrap(679,$YPos,40,$FontSize,"Splr Ords","centre");
$LeftOvers = $pdf->addTextWrap(720,$YPos,40,$FontSize,"Sugg Ord","centre");


$YPos =$YPos - (2*$line_height);
$FontSize=8;
?>

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

$LeftOvers = $pdf->addTextWrap($XPos,$YPos,130,$FontSize,"Item","centre");
$LeftOvers = $pdf->addTextWrap(190,$YPos,40,$FontSize,$Period_4_Name . " Qty","centre");
$LeftOvers = $pdf->addTextWrap(231,$YPos,40,$FontSize,$Period_3_Name . " Qty","centre");
$LeftOvers = $pdf->addTextWrap(272,$YPos,40,$FontSize,$Period_2_Name . " Qty","centre");
$LeftOvers = $pdf->addTextWrap(313,$YPos,40,$FontSize,$Period_1_Name . " Qty","centre");
$LeftOvers = $pdf->addTextWrap(354,$YPos,40,$FontSize,$_POST["NumberMonthsHolding"] . " ms stk","centre");
$LeftOvers = $pdf->addTextWrap(395,$YPos,40,$FontSize,"QOH","centre");
$LeftOvers = $pdf->addTextWrap(436,$YPos,40,$FontSize,"Cust Ords","centre");
$LeftOvers = $pdf->addTextWrap(477,$YPos,40,$FontSize,"Splr Ords","centre");
$LeftOvers = $pdf->addTextWrap(518,$YPos,40,$FontSize,"Sugg Ord","centre");


$YPos =$YPos - (2*$line_height);
$FontSize=8;
?>

<?php

/*PDF page header for inventory valuation report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);

$YPos -=$line_height;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,"Inventory Valuation for Categories between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria'] . " at " . $_POST['Location'] . " location");
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,"Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

if ($_POST["DetailedReport"]=="Yes"){
	
	$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,"Category/Item", "centre");
	$LeftOvers = $pdf->addTextWrap(390,$YPos,60,$FontSize,"Quantity", "centre");
	$LeftOvers = $pdf->addTextWrap(450,$YPos,60,$FontSize,"Unit Cost", "centre");
	$LeftOvers = $pdf->addTextWrap(510,$YPos,60,$FontSize,"Item Value", "centre");
} else {
	$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,320-$Left_Margin,$FontSize,"Category", "centre");
	$LeftOvers = $pdf->addTextWrap(510,$YPos,60,$FontSize,"Value", "centre");
}

$FontSize=8;
$YPos =$YPos - (2*$line_height);

$PageNumber++;

?>

<?php
/* $Revision: 1.2 $ */
/*PDF page header for price list report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,"Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=$line_height;

if ($_POST['CustomerSpecials']=="Customer Special Prices Only"){

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,"Special Prices for Inventory Categories between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria'] . " for " . $CustomerName);

} else {
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,"Price List for Inventory Categories between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria'] . " for " . $SalesTypeName);

}


$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,"Category/Item", "centre");

if ($_POST["CustomerSpecials"]=="Customer Special Prices Only"){

	$LeftOvers = $pdf->addTextWrap(300,$YPos,120,$FontSize,"Branch", "centre");

}

$LeftOvers = $pdf->addTextWrap(450,$YPos,60,$FontSize,"Price", "centre");

if ($_POST['ShowGPPercentages']=='Yes'){
	$LeftOvers = $pdf->addTextWrap(510,$YPos,60,$FontSize,"GP %", "centre");
}


$FontSize=8;
$YPos -= (1.5 * $line_height);

$PageNumber++;

?>

<?php

/*PDF page header for inventory valuation report */

if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -=$LineHeight;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,__('Inventory Valuation for Categories between') . ' ' . $_POST['FromCriteria'] . ' ' . __('and') . ' ' . $_POST['ToCriteria'] . ' ' . __('at') . ' ' . $_POST['Location'] . ' ' . __('location'));
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,__('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber);

$YPos -=(2*$LineHeight);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);
$pdf->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos- $LineHeight);
$pdf->line($Left_Margin, $YPos- $LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);

/*set up the headings */
$Xpos = $Left_Margin+1;

if ($_POST['DetailedReport']=='Yes'){

	$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,__('Category') . '/' . __('Item'), 'left');
	$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,__('Quantity'), 'center');
	$LeftOvers = $pdf->addTextWrap(422,$YPos,15,$FontSize,__('Units'), 'center');
	$LeftOvers = $pdf->addTextWrap(437,$YPos,60,$FontSize,__('Cost'), 'right');
	$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,__('Extended Cost'), 'right');
} else {
	$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,320-$Left_Margin,$FontSize,__('Category'), 'center');
	$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,__('Quantity'), 'right');
	$LeftOvers = $pdf->addTextWrap(490,$YPos,60,$FontSize,__('Cost'), 'right');
}

$FontSize=8;
$YPos =$YPos - (2*$LineHeight);

$PageNumber++;

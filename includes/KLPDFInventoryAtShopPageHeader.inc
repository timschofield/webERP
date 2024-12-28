<?php
/* $Id: KLPDFInventoryAtShopPageHeader.inc $*/

/*PDF page header for inventory valuation report */
if ($PageNumber>1){
	$pdf->newPage();
}

$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -=$Line_height;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Inventory Control at ') . ' ' . $_POST['Location'] . ' ' . _('location'));
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,_('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);

$YPos -=(2*$Line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$Line_height,$Page_Width-$Right_Margin, $YPos+$Line_height);
$pdf->line($Left_Margin, $YPos+$Line_height,$Left_Margin, $YPos- $Line_height);
$pdf->line($Left_Margin, $YPos- $Line_height,$Page_Width-$Right_Margin, $YPos- $Line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$Line_height,$Page_Width-$Right_Margin, $YPos- $Line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

	
$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize, _('Item'), 'left');
$LeftOvers = $pdf->addTextWrap(200,$YPos,60,$FontSize, _('Description'), 'left');
$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,_('Qty'), 'left');
$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,_('Check pricetags'), 'left');

$YPos =$YPos - (2*$Line_height);

$PageNumber++;

?>
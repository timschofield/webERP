<?php

/*PDF page header for outstanding GRNs report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -=$LineHeight;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,__('Outstanding GRNs Valuation for Suppliers between') . ' ' . $_POST['FromCriteria'] . ' ' . __('and') . ' ' . $_POST['ToCriteria']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,220,$FontSize,__('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber);

$YPos -=(2*$LineHeight);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos-$LineHeight,$Page_Width-$Right_Margin, $YPos-$LineHeight);
$pdf->line($Left_Margin, $YPos-$LineHeight,$Left_Margin, $YPos+10);
$pdf->line($Left_Margin, $YPos+10,$Page_Width-$Right_Margin, $YPos+10);
$pdf->line($Page_Width-$Right_Margin, $YPos-$LineHeight,$Page_Width-$Right_Margin, $YPos+10);

/*Draw a rectangle to put the details in     */

$pdf->line($Left_Margin, $Bottom_Margin, $Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Left_Margin, $Bottom_Margin, $Left_Margin, $YPos+10);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin, $Page_Width-$Right_Margin, $YPos+10);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap(32,$YPos,40,$FontSize,__('GRN'), 'centre');
$LeftOvers = $pdf->addTextWrap(70,$YPos,40,$FontSize,__('Order') . ' #', 'centre');
$LeftOvers = $pdf->addTextWrap(110,$YPos,200,$FontSize,__('Item') . ' / ' . __('Description'), 'centre');
$LeftOvers = $pdf->addTextWrap(310,$YPos,50,$FontSize,__('Qty Recd'), 'centre');
$LeftOvers = $pdf->addTextWrap(360,$YPos,50,$FontSize,__('Qty Inv'), 'centre');
$LeftOvers = $pdf->addTextWrap(410,$YPos,50,$FontSize,__('Balance'), 'centre');
$LeftOvers = $pdf->addTextWrap(460,$YPos,50,$FontSize,__('Std Cost'), 'centre');
$LeftOvers = $pdf->addTextWrap(510,$YPos,50,$FontSize,__('Value'), 'centre');

$YPos =$YPos - (2*$LineHeight);

$PageNumber++;
$FontSize=8;

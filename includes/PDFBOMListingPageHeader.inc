<?php
/*PDF page header for inventory valuation report */

$PageNumber++;
/* first time round $PageNumber will only be 1 and page created on initiation of $pdf */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;


$pdf->addText($Left_Margin, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

$YPos -=$line_height;

$FontSize =10;

$pdf->addText($Left_Margin, $YPos, $FontSize, _('Bill Of Material Listing for Parts Between') . ' ' . $_POST['FromCriteria'] . ' ' . _('and') . ' ' . $_POST['ToCriteria']);



$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,320 - $Left_Margin,$FontSize,_('Component Part/Description'),'center');
$LeftOvers = $pdf->addTextWrap(320,$YPos,50,$FontSize,_('Effective After'),'left');
$LeftOvers = $pdf->addTextWrap(380,$YPos,50,$FontSize,_('Effective To'),'left');
$LeftOvers = $pdf->addTextWrap(440,$YPos,30,$FontSize,_('Locn'),'left');
$LeftOvers = $pdf->addTextWrap(480,$YPos,30,$FontSize,_('Wrk Cntr'),'left');
$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,_('Quantity'),'right');

$YPos =$YPos - (2*$line_height);

$FontSize=10;
?>
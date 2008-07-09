<?php
/* $Revision: 1.5 $ */
/*PDF page header for aged analysis reports */
$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;

$pdf->addText($Left_Margin, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

$YPos -=$line_height;

$FontSize =10;
$pdf->addText($Left_Margin, $YPos, $FontSize, $ReportTitle);

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Printed') . ': ' . Date("d M Y") . '    ' . _('Page') . ' ' . $PageNumber);

$YPos -=(3*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap(40,$YPos,60 - $Left_Margin,$FontSize,_('Type'),'centre');
$LeftOvers = $pdf->addTextWrap(100,$YPos,40 - $Left_Margin,$FontSize,_('No'),'centre');
$LeftOvers = $pdf->addTextWrap(140,$YPos,60,$FontSize,_('Date'),'centre');
$LeftOvers = $pdf->addTextWrap(240,$YPos,60,$FontSize,_('Name'),'centre');
$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,_('Code'),'centre');
$LeftOvers = $pdf->addTextWrap(450,$YPos,60,$FontSize,_('Net'),'centre');
$LeftOvers = $pdf->addTextWrap(510,$YPos,60,$FontSize,_('Tax'),'centre');

$YPos =$YPos - (2*$line_height);

?>

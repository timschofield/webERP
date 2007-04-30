<?php
/* $Revision: 1.5 $ */
/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=12;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos,180,$FontSize,_('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);

$YPos -=15;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,_('Check Sheets for Categories between') . ' ' . $_POST['FromCriteria'] . ' ' . _('and') . ' ' . $_POST['ToCriteria'] . ' ' . _('for stock at') . ' ' . $_POST['Location']);

$YPos -=20;
/*Draw a rectangle to put the headings in     */
$BoxHeight =15;

$pdf->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight);
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Left_Margin, $YPos- $BoxHeight);
$pdf->line($Left_Margin, $YPos-$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);

/*set up the headings */
$Xpos = $Left_Margin+1;

/*draw vertical lines */
$pdf->line(350, $YPos-$line_height,350, $Bottom_Margin+24);
$pdf->line(415, $YPos-$line_height,415, $Bottom_Margin+24);
$pdf->line(475, $YPos-$line_height,475, $Bottom_Margin+24);

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,_('Item'), 'centre');
if ($_POST['ShowInfo']==true){
	$LeftOvers = $pdf->addTextWrap(341,$YPos,60,$FontSize,_('QOH'), 'centre');
	$LeftOvers = $pdf->addTextWrap(341+61,$YPos,80,$FontSize,_('Cust Ords'), 'centre');
	$LeftOvers = $pdf->addTextWrap(341+61+61,$YPos,80,$FontSize,_('Available'), 'centre');
	}
$FontSize=10;
$YPos -=($line_height);
?>

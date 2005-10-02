<?php
/* $Revision: 1.5 $ */
if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - 50;

$pdf->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/logo.jpg',$Left_Margin,$YPos,60);

$FontSize=15;

$XPos = $Left_Margin;
$YPos -= 40;
$pdf->addText($XPos, $YPos,$FontSize, _('Order Status Listing'));
$FontSize=12;

if ($_POST['CategoryID']!='All') {
	$pdf->addText($XPos, $YPos-20,$FontSize, _('For Stock Category') . ' ' . $_POST['CategoryID'] . ' ' . _('From') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' .  $_POST['ToDate']);
} else {
	$pdf->addText($XPos, $YPos-20,$FontSize, _('From') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' .  $_POST['ToDate']);
}
if ($_POST['Location']!='All'){
	$pdf->addText($XPos+300, $YPos-20, $FontSize, ' ' . _('for delivery ex') . ' ' . $_POST['Location'] . ' ' ._('only'));
}

$XPos = $Page_Width-$Right_Margin-50;
$YPos -=30;
$pdf->addText($XPos, $YPos,$FontSize, _('Page') . ': ' . $PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= $line_height;


$YPos -= $line_height;
/*Set up headings */
$FontSize=8;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Order'), 'center');
$LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos,80,$FontSize,_('Customer'), 'center');
$LeftOvers = $pdf->addTextWrap($Left_Margin+120,$YPos,80,$FontSize,_('Branch'), 'center');
$LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos,100,$FontSize,_('Customer Ref'), 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,80,$FontSize,_('Ord Date'), 'center');
$LeftOvers = $pdf->addTextWrap($Left_Margin+380,$YPos,20,$FontSize,_('Loc'), 'center');

$YPos-=$line_height;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($line_height);
?>
<?php
/* $Revision: 1.8 $ */
if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - 50;

$pdf->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/logo.jpg',$Left_Margin,$YPos,0,50);

$FontSize=15;

$XPos = $Left_Margin;
$YPos -= 40;
$pdf->addText($XPos, $YPos,$FontSize, _('Variances Between Orders and Deliveries Listing'));
$FontSize=12;

if ($_POST['CategoryID']!='All') {
	$pdf->addText($XPos, $YPos-20,$FontSize, _('For Inventory Category') . ' ' . $_POST['CategoryID'] . ' '. _('From') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' .  $_POST['ToDate']);
} else {
	$pdf->addText($XPos, $YPos-20,$FontSize, _('From') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' .  $_POST['ToDate']);
}
if ($_POST['Location']!='All'){
	$pdf->addText($XPos+300, $YPos-20, $FontSize, _('Deliveries ex') . ' '. $_POST['Location'] . ' ' . _('only'));
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

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Invoice'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos,40,$FontSize,_('Order'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+80,$YPos,200,$FontSize,_('Item and Description'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+280,$YPos,50,$FontSize,_('Quantity'), 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+335,$YPos,45,$FontSize,_('Customer'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+385,$YPos,45,$FontSize,_('Branch'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+420,$YPos,50,$FontSize,_('Inv Date'), 'centre');

$YPos-=$line_height;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($line_height);
?>

<?php

if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - 50;

$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos,0,50);

$FontSize=15;

$XPos = $Left_Margin;
$YPos -= 40;
$pdf->addText($XPos, $YPos,$FontSize, __('Variances Between Orders and Deliveries Listing'));
$FontSize=12;

if ($_POST['CategoryID']!='All') {
	$pdf->addText($XPos, $YPos-20,$FontSize, __('For Inventory Category') . ' ' . $_POST['CategoryID'] . ' '. __('From') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' .  $_POST['ToDate']);
} else {
	$pdf->addText($XPos, $YPos-20,$FontSize, __('From') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' .  $_POST['ToDate']);
}
if ($_POST['Location']!='All'){
	$pdf->addText($XPos+300, $YPos-20, $FontSize, __('Deliveries ex') . ' '. $_POST['Location'] . ' ' . __('only'));
}

$XPos = $Page_Width-$Right_Margin-50;
$YPos -=30;
$pdf->addText($XPos, $YPos,$FontSize, __('Page') . ': ' . $PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= $LineHeight;


$YPos -= $LineHeight;
/*Set up headings */
$FontSize=8;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Invoice'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos,40,$FontSize,__('Order'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+80,$YPos,200,$FontSize,__('Item and Description'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+280,$YPos,50,$FontSize,__('Quantity'), 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+335,$YPos,45,$FontSize,__('Customer'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+385,$YPos,45,$FontSize,__('Branch'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+420,$YPos,50,$FontSize,__('Inv Date'), 'centre');

$YPos-=$LineHeight;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($LineHeight);

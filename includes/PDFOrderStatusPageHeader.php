<?php

if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - 50;

$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos,0,50);

$FontSize=15;

$XPos = $Page_Width/2;
$YPos = $Page_Height - $Top_Margin;
$pdf->addText($XPos, $YPos,$FontSize, __('Order Status Listing'));
$FontSize=12;

if ($_POST['CategoryID']!='All') {
	$pdf->addText($XPos, $YPos-20,$FontSize, __('For Stock Category') . ' ' . $_POST['CategoryID'] . ' ' . __('From') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' .  $_POST['ToDate']);
} else {
	$pdf->addText($XPos, $YPos-20,$FontSize, __('From') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' .  $_POST['ToDate']);
}
if ($_POST['Location']!='All'){
	$pdf->addText($XPos+300, $YPos-20, $FontSize, ' ' . __('for delivery ex') . ' ' . $_POST['Location'] . ' ' .__('only'));
}

$XPos = $Page_Width-$Right_Margin-50;
$YPos = $Page_Height - $Top_Margin - 50;
$pdf->addText($XPos, $YPos,$FontSize, __('Page') . ': ' . $PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= $LineHeight*2;
$FontSize=8;

$NewPage=true;

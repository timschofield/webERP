<?php

if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - 50;

$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos,0,50);

$FontSize=15;

$XPos = $Page_Width/2;
$YPos += 30;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);
$FontSize=12;
$YPos -=30;
$pdf->addText($XPos, $YPos,$FontSize, $TransType . ' ' .__('dated from') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' . $_POST['ToDate']);

$XPos = $Page_Width-$Right_Margin-50;
$YPos -=30;
$pdf->addText($XPos, $YPos+10,$FontSize, __('Page') . ': ' . $PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= $LineHeight;

/*draw a square grid for entering line items */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos,$Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$XPos, $Bottom_Margin);
$pdf->line($XPos, $Bottom_Margin,$XPos, $YPos);

$pdf->line($Left_Margin+160, $YPos,$Left_Margin+160, $Bottom_Margin);
$pdf->line($Left_Margin+240, $YPos,$Left_Margin+240, $Bottom_Margin);
$pdf->line($Left_Margin+310, $YPos,$Left_Margin+310, $Bottom_Margin);
$pdf->line($Left_Margin+384, $YPos,$Left_Margin+384, $Bottom_Margin);
$pdf->line($Left_Margin+454, $YPos,$Left_Margin+454, $Bottom_Margin);

$YPos -= $LineHeight;
/*Set up headings */
$FontSize=8;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,160,$FontSize,__('Stock Item'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+162,$YPos,80,$FontSize,__('Reference'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+242,$YPos,70,$FontSize,__('Trans Date'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+312,$YPos,70,$FontSize,__('Quantity'), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+382,$YPos,70,$FontSize,__('Location'), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,__('Reference'), 'right');
$YPos-=$LineHeight;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($LineHeight);

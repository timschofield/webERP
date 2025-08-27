<?php

$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - 50;

$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos,0,60);

$FontSize=15;

$XPos = $Page_Width/2 - 80;

$YPos -= 40;
$pdf->addText($XPos, $YPos,$FontSize, __('Banking Summary'));
$FontSize=12;
$pdf->addText($XPos-50, $YPos-20,$FontSize, __('for Receipt Batch') . ' # ' . $_POST['BatchNo'] . ' ' . __('of') . ' ' . $BankTransType);

$XPos = $Page_Width-$Right_Margin-50;
$YPos -=30;
$pdf->addText($XPos, $YPos,$FontSize, __('Page') .': ' . $PageNumber);

/* Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= $LineHeight;

$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);
$FontSize=10;

$YPos -=$LineHeight;
$XPos = $Left_Margin;


$pdf->addText($XPos, $YPos,$FontSize, __('Date of Banking') .': ' . ConvertSQLDate($MyRow['transdate']));
$YPos -= $LineHeight;
$pdf->addText($XPos, $YPos,$FontSize, __('Banked into') . ': ' . $BankActName . ' - ' . __('Account Number') . ': ' . $BankActNumber);
$YPos -= $LineHeight;
$pdf->addText($XPos, $YPos,$FontSize, __('Reference') . ': ' . $BankingReference);
$YPos -= $LineHeight;
$pdf->addText($XPos, $YPos,$FontSize, __('Currency') . ': ' . $Currency);
$YPos -= $LineHeight;

/*draw a square grid for entering line items */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos,$Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$XPos, $Bottom_Margin);
$pdf->line($XPos, $Bottom_Margin,$XPos, $YPos);

$YPos -= $LineHeight;
/*Set up headings */
$FontSize=8;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,__('Amount'), 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+62,$YPos,150,$FontSize,__('Customer'), 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+212,$YPos,100,$FontSize,__('Bank Details'), 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+312,$YPos,100,$FontSize,__('Narrative'), 'centre');
$YPos-=$LineHeight;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($LineHeight);

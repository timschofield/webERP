<?php
/* $Revision: 1.2 $ */
$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - 50;

$pdf->addJpegFromFile('logo.jpg',$Left_Margin,$YPos,90,60);

$FontSize=15;

$XPos = $Page_Width/2 - 80;

$YPos -= 40;
$pdf->addText($XPos, $YPos,$FontSize, "Banking Summary");
$FontSize=12;
$pdf->addText($XPos-50, $YPos-20,$FontSize, "for Receipt Batch # " . $_POST['BatchNo'] . " of " . $BankTransType);

$XPos = $Page_Width-$Right_Margin-50;
$YPos -=30;
$pdf->addText($XPos, $YPos,$FontSize, "Page: " . $PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= $line_height;

$pdf->addText($XPos, $YPos,$FontSize, $CompanyName);
$FontSize=10;

$YPos -=$line_height;
$XPos = $Left_Margin;


$pdf->addText($XPos, $YPos,$FontSize, "Date of Banking: " . ConvertSQLDate($myrow["TransDate"]));
$YPos -= $line_height;
$pdf->addText($XPos, $YPos,$FontSize, "Banked into: " . $BankActName . " - Account Number: " . $BankActNumber);
$YPos -= $line_height;
$pdf->addText($XPos, $YPos,$FontSize, "Reference: " . $BankingReference);
$YPos -= $line_height;
$pdf->addText($XPos, $YPos,$FontSize, "Currency: " . $Currency);
$YPos -= $line_height;

/*draw a square grid for entering line items */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos,$Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$XPos, $Bottom_Margin);
$pdf->line($XPos, $Bottom_Margin,$XPos, $YPos);

$YPos -= $line_height;
/*Set up headings */
$FontSize=8;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,"Amount", 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+62,$YPos,150,$FontSize,"Customer", 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+212,$YPos,100,$FontSize,"Bank Details", 'centre');
$LeftOvers = $pdf->addTextWrap($Left_Margin+312,$YPos,100,$FontSize,"Narrative", 'centre');
$YPos-=$line_height;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($line_height);
?>

<?php
/* $Revision: 1.2 $ */
/*PDF page header for aged analysis reports */
$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;

$pdf->addText($Left_Margin, $YPos,$FontSize, $CompanyName);

$YPos -=$line_height;

$FontSize =10;
$pdf->addText($Left_Margin, $YPos,$FontSize, "Aged Customer Balances For Customers between "  . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria']);
$pdf->addText($Left_Margin, $YPos-$line_height,$FontSize, "And Trading in " . $_POST['Currency']);

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, "Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=(3*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,220 - $Left_Margin,$FontSize,"Customer",'centre');
$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,"Balance",'centre');
$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,"Current",'centre');
$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,"Due Now",'centre');
$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,"> " . $PastDueDays1 . " Days Over",'centre');
$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,"> " . $PastDueDays2 . " Days Over",'centre');

$YPos =$YPos - (2*$line_height);

?>

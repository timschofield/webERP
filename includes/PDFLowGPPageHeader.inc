<?php
/* $Revision: 1.2 $ */
/*PDF page header for inventory valuation report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);

$YPos -=$line_height;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,"Low GP Sales Between " . $_POST['FromDate'] . " and " . $_POST['ToDate'] . " less than " . $_POST['GPMin'] . "%");
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,"Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,100-$Left_Margin,$FontSize,"Trans", "centre");
$LeftOvers = $pdf->addTextWrap(100,$YPos,50,$FontSize,"No", "centre");
$LeftOvers = $pdf->addTextWrap(150,$YPos,50,$FontSize,"Item", "centre");
$LeftOvers = $pdf->addTextWrap(250,$YPos,100,$FontSize,"Customer", "centre");
$LeftOvers = $pdf->addTextWrap(350,$YPos,50,$FontSize,"Cost", "centre");
$LeftOvers = $pdf->addTextWrap(390,$YPos,60,$FontSize,"Sell Price", "centre");
$LeftOvers = $pdf->addTextWrap(450,$YPos,60,$FontSize,"GP", "centre");
$LeftOvers = $pdf->addTextWrap(510,$YPos,60,$FontSize,"GP %", "centre");

$FontSize=8;
$YPos =$YPos - (2*$line_height);

$PageNumber++;

?>

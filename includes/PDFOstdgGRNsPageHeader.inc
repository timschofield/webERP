<?php
/* $Revision: 1.2 $ */
/*PDF page header for outstanding GRNs report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260,$FontSize,$CompanyName);

$YPos -=$line_height;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,"Outstanding GRNs Valuation for Suppliers between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,"Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos-$line_height,$Page_Width-$Right_Margin, $YPos-$line_height);
$pdf->line($Left_Margin, $YPos-$line_height,$Left_Margin, $YPos+10);
$pdf->line($Left_Margin, $YPos+10,$Page_Width-$Right_Margin, $YPos+10);
$pdf->line($Page_Width-$Right_Margin, $YPos-$line_height,$Page_Width-$Right_Margin, $YPos+10);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap(30,$YPos,40,$FontSize,"GRN", "centre");
$LeftOvers = $pdf->addTextWrap(70,$YPos,40,$FontSize,"Order #", "centre");
$LeftOvers = $pdf->addTextWrap(110,$YPos,200,$FontSize,"Item / Description", "centre");
$LeftOvers = $pdf->addTextWrap(310,$YPos,50,$FontSize,"Qty Recd", "centre");
$LeftOvers = $pdf->addTextWrap(360,$YPos,50,$FontSize,"Qty Inv", "centre");
$LeftOvers = $pdf->addTextWrap(410,$YPos,50,$FontSize,"Balance", "centre");
$LeftOvers = $pdf->addTextWrap(460,$YPos,50,$FontSize,"Std Cost", "centre");
$LeftOvers = $pdf->addTextWrap(510,$YPos,50,$FontSize,"Value", "centre");

$YPos =$YPos - (2*$line_height);

$PageNumber++;
$FontSize=8;
?>

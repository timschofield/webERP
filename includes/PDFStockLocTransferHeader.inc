<?php

/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=12;
$YPos= $Page_Height-$Top_Margin;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,"Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=15;
$FontSize=12;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,"Transfer No: " . $_GET['TransferNo'] . "  From " . $TransferRow['ShipLocName'] . " to " . $TransferRow['RecLocName'] . " Shipped " . ConvertSQLDate($TransferRow['ShipDate']));

$YPos -=25;
/*Draw a rectangle to put the headings in     */
$BoxHeight =20;
$FontSize=12;
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight);
$pdf->line($Left_Margin, $YPos+$BoxHeight,$Left_Margin, $YPos);
$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos);

$YPos +=5;

/*set up the headings */
$Xpos = $Left_Margin+1;
$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,100-$Left_Margin,$FontSize,"Item Code", "centre");
$LeftOvers = $pdf->addTextWrap(150,$YPos,150-$Left_Margin,$FontSize,"Description", "centre");
$LeftOvers = $pdf->addTextWrap(341,$YPos,120,$FontSize,"Qty Shipped", "centre");
$LeftOvers = $pdf->addTextWrap(341+120,$YPos,120,$FontSize,"Qty Received", "centre");

$FontSize=10;
$YPos -= 30;

?>

<?php

/*PDF page header for price list report */

if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos+20,$YPos-50,0,60);

$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos-($LineHeight*1.5),550,$FontSize, __('Stock transfer number ').' ' . $_GET['TransferNo'] );
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos-($LineHeight*3),140,$FontSize, __('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. __('Page'). ' ' . $PageNumber);

$YPos -= 60;

$YPos -=$LineHeight;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(2*$LineHeight);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);
$pdf->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos- $LineHeight);
$pdf->line($Left_Margin, $YPos- $LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,  __('Item Number'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+75,$YPos,300-$Left_Margin,$FontSize,  __('Description'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+250,$YPos,300-$Left_Margin,$FontSize,  __('Transfer From'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+350,$YPos,300-$Left_Margin,$FontSize,  __('Transfer To'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+450,$YPos,300-$Left_Margin,$FontSize,  __('Quantity'), 'centre');


$FontSize=8;
$YPos -= (1.5 * $LineHeight);

$PageNumber++;

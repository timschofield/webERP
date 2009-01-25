<?php
/* $Revision: 1.2 $ */
/*PDF page header for price list report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/logo.jpg',$XPos+20,$YPos-50,0,60);


$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos-($line_height*1.5),550,$FontSize, _('Stock transfer number ').' ' . $_GET['TransferNo'] );
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos-($line_height*3),140,$FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. _('Page'). ' ' . $PageNumber);

$YPos -= 60;

$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,  _('Item Number'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+75,$YPos,300-$Left_Margin,$FontSize,  _('Description'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+250,$YPos,300-$Left_Margin,$FontSize,  _('Transfer From'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+350,$YPos,300-$Left_Margin,$FontSize,  _('Transfer To'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+450,$YPos,300-$Left_Margin,$FontSize,  _('Quantity'), 'centre');


$FontSize=8;
$YPos -= (1.5 * $line_height);

$PageNumber++;

?>
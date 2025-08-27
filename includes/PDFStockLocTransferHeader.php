<?php

/*PDF page header for inventory check report */

if ($PageNumber>1){
	$pdf->newPage();
}

$YPos= $Page_Height-$Top_Margin;
$YPos -=15; // Downs one line height (coordinates are from bottom left page to bottom left text).
$FontSize=12;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$LeftOvers = $pdf->addTextWrap(	$Page_Width-$Right_Margin-550,
								$Page_Height-$Top_Margin-$FontSize*1,
								550,
								$FontSize,
								__('Transfer No').': ' . $_GET['TransferNo'],
								'right'); // Prints inventory location transfer reference number.

$LeftOvers = $pdf->addTextWrap(	$Page_Width-$Right_Margin-120,
								$Page_Height-$Top_Margin-$FontSize*2,
								120,
								$FontSize,
								__('Printed'). ': ' . Date($_SESSION['DefaultDateFormat']),
								'right'); // Prints current date.

$LeftOvers = $pdf->addTextWrap(	$Page_Width-$Right_Margin-120,
								$Page_Height-$Top_Margin-$FontSize*3,
								120,
								$FontSize,
								__('Page'). ' ' . $PageNumber,
								'right'); // Prints current page number.

$YPos -=15;
$FontSize=12;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,
__('Shipped'). ' ' . ConvertSQLDateTime($TransferRow['shipdate']));

$YPos -=15;
$FontSize=12;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,
__('From').' ' . $TransferRow['shiplocname']);

$YPos -=15;
$FontSize=12;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,
__('to').' ' . $TransferRow['reclocname']);

/*Draw a rectangle to put the headings in     */
$BoxHeight =20;
$FontSize=12;
$YPos-=20;
$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Left_Margin, $YPos,$Left_Margin, $YPos-$BoxHeight);
$pdf->line($Left_Margin, $YPos-$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$YPos -=20;

$YPos +=5;

/*set up the headings */
$Xpos = $Left_Margin+1;
$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 100, $FontSize, __('Item Code'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+100, $YPos, 250, $FontSize, __('Description'), 'left');
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-100-100, $YPos, 100, $FontSize, __('Qty Shipped'), 'right');
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-100, $YPos, 100, $FontSize, __('Qty Received'), 'right');

$FontSize=10;
$YPos -= 30;

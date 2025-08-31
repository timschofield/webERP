<?php

/* pdf-php by R&OS code to set up a new sales order page */

if ($PageNumber>1){
	$pdf->newPage();
}

$XPos = $Page_Width/2 - 60;
/* if the deliver blind flag is set on the order, we do not want to output
the company logo */
if ($DeliverBlind < 2) {
    $pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,490,0,60);
}
$FontSize=18;

if ($Copy=='Customer'){
	$pdf->addText($XPos-40, 585,$FontSize, __('Packing Slip') . ' - ' . __('Customer Copy') );
} else {
	$pdf->addText($XPos-40, 585,$FontSize, __('Packing Slip') . ' - ' . __('Office Copy') );
}

/* if the deliver blind flag is set on the order, we do not want to output
the company contact info */
if ($DeliverBlind < 2) {
    $YPos = 480;
	PrintOurCompanyInfo($pdf,$_SESSION['CompanyRecord'],$XPos,$YPos);
}

$XPos = 46;
$YPos = 566;
PrintDeliverTo($pdf,$MyRow,__('Delivered To'),$XPos,$YPos);

$YPos -= 82;
PrintCompanyTo($pdf,$MyRow,__('Customer'),$XPos,$YPos);


$FontSize = 14;
$pdf->addText($XPos, $YPos-82,$FontSize, __('Customer No.'). ': ' . $MyRow['debtorno']);
$pdf->addText($XPos, $YPos-100,$FontSize, __('Shipped by'). ': ' . $MyRow['shippername']);

$FontSize=12;
$LeftOvers = $pdf->addTextWrap($XPos,$YPos-130,250,$FontSize,__('Comments').': '.stripcslashes($MyRow['comments']));

if (mb_strlen($LeftOvers)>1){
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos-145,250,$FontSize,$LeftOvers);
	if (mb_strlen($LeftOvers)>1){
		$LeftOvers = $pdf->addTextWrap($XPos,$YPos-160,250,$FontSize,$LeftOvers);
		if (mb_strlen($LeftOvers)>1){
			$LeftOvers = $pdf->addTextWrap($XPos,$YPos-175,250,$FontSize,$LeftOvers);
			if (mb_strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($XPos,$YPos-180,250,$FontSize,$LeftOvers);
			}
		}
	}
}

$FontSize=14;
$pdf->addText(620, 560,$FontSize, __('Order No'). ':');
$pdf->addText(700, 560,$FontSize, $_GET['TransNo']);
$pdf->addText(620, 560-15,$FontSize, __('Your Ref'). ':');
$pdf->addText(700, 560-15,$FontSize, $MyRow['customerref']);
$pdf->addText(620, 560-45,$FontSize,  __('Order Date'). ':');
$pdf->addText(700, 560-45,$FontSize,  ConvertSQLDate($MyRow['orddate']));
$pdf->addText(620, 560-60,$FontSize,  __('Printed') . ': ');
$pdf->addText(700, 560-60,$FontSize,  Date($_SESSION['DefaultDateFormat']));
$pdf->addText(620, 560-75,$FontSize,  __('From').': ');
$pdf->addText(700, 560-75,$FontSize,  $MyRow['locationname']);
$pdf->addText(620, 560-90,$FontSize,  __('Page'). ':');
$pdf->addText(700, 560-90,$FontSize,  $PageNumber);

$YPos -= 170;
$XPos = 15;

$HeaderLineHeight = $LineHeight+25;

$LeftOvers = $pdf->addTextWrap($XPos,$YPos,127,$FontSize, __('Item Code'),'left');
$LeftOvers = $pdf->addTextWrap(147,$YPos,255,$FontSize, __('Item Description'),'left');
$LeftOvers = $pdf->addTextWrap(400,$YPos,85,$FontSize, __('Quantity'),'right');
$LeftOvers = $pdf->addTextWrap(487,$YPos,85,$FontSize, __('Units'),'left');
$LeftOvers = $pdf->addTextWrap(527,$YPos,70,$FontSize, __('Bin Locn'),'left');
$LeftOvers = $pdf->addTextWrap(593,$YPos,85,$FontSize,__('This Del'),'right');
$LeftOvers = $pdf->addTextWrap(692,$YPos,85,$FontSize, __('Prev Dels'),'right');

$YPos -= $LineHeight;

$FontSize =12;

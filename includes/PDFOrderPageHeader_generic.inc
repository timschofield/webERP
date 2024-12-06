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
	$pdf->addText($XPos-40, 585,$FontSize, _('Packing Slip') . ' - ' . _('Customer Copy') );
} else {
	$pdf->addText($XPos-40, 585,$FontSize, _('Packing Slip') . ' - ' . _('Office Copy') );
}

/* if the deliver blind flag is set on the order, we do not want to output
the company contact info */
if ($DeliverBlind < 2) {
    $FontSize=12;
    $YPos = 480;
    $pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);
    $FontSize=10;
    $pdf->addText($XPos, $YPos-14,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
    $pdf->addText($XPos, $YPos-26,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
    $pdf->addText($XPos, $YPos-38,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . '  ' . $_SESSION['CompanyRecord']['regoffice4'] . '  ' . $_SESSION['CompanyRecord']['regoffice5']);
    $pdf->addText($XPos, $YPos-50,$FontSize, $_SESSION['CompanyRecord']['regoffice6']);
    $pdf->addText($XPos, $YPos-62,$FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
    $pdf->addText($XPos, $YPos-74,$FontSize, $_SESSION['CompanyRecord']['email']);
}

$XPos = 46;
$YPos = 566;

$FontSize=14;
$pdf->addText($XPos, $YPos,$FontSize, _('Delivered To').':' );
$pdf->addText($XPos, $YPos-15,$FontSize, $myrow['deliverto']);
$pdf->addText($XPos, $YPos-30,$FontSize, $myrow['deladd1']);
$pdf->addText($XPos, $YPos-45,$FontSize, $myrow['deladd2']);
$pdf->addText($XPos, $YPos-60,$FontSize, $myrow['deladd3'] . ' ' . $myrow['deladd4'] . ' ' . $myrow['deladd5'] . ' ' . $myrow['deladd6']);

$YPos -= 80;

$pdf->addText($XPos, $YPos,$FontSize, _('Customer').':');
$pdf->addText($XPos, $YPos-15,$FontSize, $myrow['name']);
$pdf->addText($XPos, $YPos-30,$FontSize, $myrow['address1']);
$pdf->addText($XPos, $YPos-45,$FontSize, $myrow['address2']);
$pdf->addText($XPos, $YPos-60,$FontSize, $myrow['address3'] . ' ' . $myrow['address4'] . ' ' . $myrow['address5']. ' ' . $myrow['address6']);


$pdf->addText($XPos, $YPos-82,$FontSize, _('Customer No.'). ': ' . $myrow['debtorno']);
$pdf->addText($XPos, $YPos-100,$FontSize, _('Shipped by'). ': ' . $myrow['shippername']);

$FontSize=12;
$LeftOvers = $pdf->addTextWrap($XPos,$YPos-130,250,$FontSize,_('Comments').': '.stripcslashes($myrow['comments']));

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
$pdf->addText(620, 560,$FontSize, _('Order No'). ':');
$pdf->addText(700, 560,$FontSize, $_GET['TransNo']);
$pdf->addText(620, 560-15,$FontSize, _('Your Ref'). ':');
$pdf->addText(700, 560-15,$FontSize, $myrow['customerref']);
$pdf->addText(620, 560-45,$FontSize,  _('Order Date'). ':');
$pdf->addText(700, 560-45,$FontSize,  ConvertSQLDate($myrow['orddate']));
$pdf->addText(620, 560-60,$FontSize,  _('Printed') . ': ');
$pdf->addText(700, 560-60,$FontSize,  Date($_SESSION['DefaultDateFormat']));
$pdf->addText(620, 560-75,$FontSize,  _('From').': ');
$pdf->addText(700, 560-75,$FontSize,  $myrow['locationname']);
$pdf->addText(620, 560-90,$FontSize,  _('Page'). ':');
$pdf->addText(700, 560-90,$FontSize,  $PageNumber);

$YPos -= 170;
$XPos = 15;

$header_line_height = $line_height+25;

$LeftOvers = $pdf->addTextWrap($XPos,$YPos,127,$FontSize, _('Item Code'),'left');
$LeftOvers = $pdf->addTextWrap(147,$YPos,255,$FontSize, _('Item Description'),'left');
$LeftOvers = $pdf->addTextWrap(400,$YPos,85,$FontSize, _('Quantity'),'right');
$LeftOvers = $pdf->addTextWrap(487,$YPos,85,$FontSize, _('Units'),'left');
$LeftOvers = $pdf->addTextWrap(527,$YPos,70,$FontSize, _('Bin Locn'),'left');
$LeftOvers = $pdf->addTextWrap(593,$YPos,85,$FontSize,_('This Del'),'right');
$LeftOvers = $pdf->addTextWrap(692,$YPos,85,$FontSize, _('Prev Dels'),'right');

$YPos -= $line_height;

$FontSize =12;

?>

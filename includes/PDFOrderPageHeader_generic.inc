<?php
/* $Revision: 1.3 $ */
/* pdf-php by R&OS code to set up a new sales order page */
if ($PageNumber>1){
	$pdf->newPage();
}

$XPos = $Page_Width/2 - 60;
$pdf->addJpegFromFile('logo.jpg',$XPos+20,520,90,60);
$FontSize=18;

if ($Copy=='Customer'){
	$pdf->addText($XPos-20, 500,$FontSize, _('Packing Slip - Customer Copy') );
} else {
	$pdf->addText($XPos-20, 500,$FontSize, _('Packing Slip - Office Copy') );
}
$FontSize=14;
$YPos = 480;
$pdf->addText($XPos, $YPos,$FontSize, $CompanyName);
$FontSize =8;
$pdf->addText($XPos, $YPos-12,$FontSize, $CompanyRecord['PostalAddress']);
$pdf->addText($XPos, $YPos-21,$FontSize, $CompanyRecord['RegOffice1'] . ' ' . $CompanyRecord['RegOffice2']);
$pdf->addText($XPos, $YPos-30,$FontSize, _('Ph:') . ' ' . $CompanyRecord['Telephone'] . ' ' . _('Fax'). ': ' . $CompanyRecord['Fax']);
$pdf->addText($XPos, $YPos-39,$FontSize, $CompanyRecord['Email']);


$XPos = 46;
$YPos = 566;

$FontSize=14;
$pdf->addText($XPos, $YPos,$FontSize, _('Delivered To').':' );
$pdf->addText($XPos, $YPos-15,$FontSize, $myrow['DeliverTo']);
$pdf->addText($XPos, $YPos-30,$FontSize, $myrow['DelAdd1']);
$pdf->addText($XPos, $YPos-45,$FontSize, $myrow['DelAdd2']);
$pdf->addText($XPos, $YPos-60,$FontSize, $myrow['DelAdd3'] . ' ' . $myrow['DelAdd4']);

$YPos -= 80;

$pdf->addText($XPos, $YPos,$FontSize, _('Customer').':');
$pdf->addText($XPos, $YPos-15,$FontSize, $myrow['Name']);
$pdf->addText($XPos, $YPos-30,$FontSize, $myrow['Address1']);
$pdf->addText($XPos, $YPos-45,$FontSize, $myrow['Address2']);
$pdf->addText($XPos, $YPos-60,$FontSize, $myrow['Address3']);
$pdf->addText($XPos, $YPos-75,$FontSize, $myrow['Address4']);



$pdf->addText($XPos, $YPos-100,$FontSize, _('Shipped by'). ' : ' . $myrow['ShipperName']);

$LeftOvers = $pdf->addTextWrap($XPos,$YPos-115,170,$FontSize,$myrow['Comments']);

if (strlen($LeftOvers)>1){
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos-130,170,$FontSize,$LeftOvers);
	if (strlen($LeftOvers)>1){
		$LeftOvers = $pdf->addTextWrap($XPos,$YPos-145,170,$FontSize,$LeftOvers);
		if (strlen($LeftOvers)>1){
			$LeftOvers = $pdf->addTextWrap($XPos,$YPos-160,170,$FontSize,$LeftOvers);
			if (strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($XPos,$YPos-175,170,$FontSize,$LeftOvers);
			}
		}
	}
}

$pdf->addText(620, 560,$FontSize, _('Order No'). ':');
$pdf->addText(700, 560,$FontSize, $_GET['TransNo']);
$pdf->addText(620, 560-15,$FontSize, _('Your Ref'). ':');
$pdf->addText(700, 560-15,$FontSize, $myrow['CustomerRef']);
$pdf->addText(620, 560-45,$FontSize,  _('Order Date'). ':');
$pdf->addText(700, 560-45,$FontSize,  ConvertSQLDate($myrow['OrdDate']));
$pdf->addText(620, 560-60,$FontSize,  _('Printed') . ': ');
$pdf->addText(700, 560-60,$FontSize,  Date($DefaultDateFormat));
$pdf->addText(620, 560-75,$FontSize,  _('From').': ');
$pdf->addText(700, 560-75,$FontSize,  $myrow['LocationName']);
$pdf->addText(620, 560-90,$FontSize,  _('Page'). ':');
$pdf->addText(700, 560-90,$FontSize,  $PageNumber);

$YPos -= 170;
$XPos = 15;

$header_line_height = $line_height+25;

$LeftOvers = $pdf->addTextWrap($XPos,$YPos,127,$FontSize, _('Item Code'),'left');
$LeftOvers = $pdf->addTextWrap(147,$YPos,255,$FontSize, _('Item Description'),'left');
$LeftOvers = $pdf->addTextWrap(400,$YPos,85,$FontSize, _('Quantity'),'right');
$LeftOvers = $pdf->addTextWrap(503,$YPos,85,$FontSize,_('This Del'),'right');
$LeftOvers = $pdf->addTextWrap(602,$YPos,85,$FontSize, _('Prev Dels'),'right');

$YPos -= $line_height;

$FontSize =12;

?>

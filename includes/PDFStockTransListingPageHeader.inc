<?php
/* $Id$*/
if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - 50;

$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos,0,50);

$FontSize=15;

Switch ($_POST['TransType']) {
	case 10:
		$TransType=_('Customer Invoices');
		break;
	case 11:
		$TransType=_('Customer Credit Notes');
		break;
	case 16:
		$TransType=_('Location Transfers');
		break;
	case 17:
		$TransType=_('Stock Adjustments');
		break;
	case 25:
		$TransType=_('Purchase Order Deliveries');
		break;
	case 26:
		$TransType=_('Work Order Receipts');
		break;
	case 28:
		$TransType=_('Work Order Issues');
		break;
}

$XPos = $Page_Width/2;
$YPos += 30;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);
$FontSize=12;
$pdf->addText($XPos, $YPos-20,$FontSize, $TransType . ' ' ._('dated') . ' ' . $_POST['Date']);

$XPos = $Page_Width-$Right_Margin-50;
$YPos -=30;
$pdf->addText($XPos, $YPos+10,$FontSize, _('Page') . ': ' . $PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= $line_height;

/*draw a square grid for entering line items */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos,$Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$XPos, $Bottom_Margin);
$pdf->line($XPos, $Bottom_Margin,$XPos, $YPos);

$pdf->line($Left_Margin+160, $YPos,$Left_Margin+160, $Bottom_Margin);
$pdf->line($Left_Margin+240, $YPos,$Left_Margin+240, $Bottom_Margin);
$pdf->line($Left_Margin+310, $YPos,$Left_Margin+310, $Bottom_Margin);
$pdf->line($Left_Margin+384, $YPos,$Left_Margin+384, $Bottom_Margin);
$pdf->line($Left_Margin+454, $YPos,$Left_Margin+454, $Bottom_Margin);

$YPos -= $line_height;
/*Set up headings */
$FontSize=8;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,160,$FontSize,_('Stock Item'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+162,$YPos,80,$FontSize,_('Reference'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+242,$YPos,70,$FontSize,_('Trans Date'), 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+312,$YPos,70,$FontSize,_('Quantity'), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+382,$YPos,70,$FontSize,_('Location'), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,_('Reference'), 'right');
$YPos-=$line_height;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($line_height);
?>
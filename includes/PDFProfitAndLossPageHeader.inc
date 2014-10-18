<?php
	/*
	 * PDF page header for the profit and loss report.
	 * Suren Naidu 28/08/2005
	 *
	 */
/* $Id: PDFProfitAndLossPageHeader.inc 5768 2012-12-20 08:38:22Z daintree $*/

	$PageNumber++;
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize = 8;
	$YPos = $Page_Height - $Top_Margin;
	$pdf->addText($Left_Margin,$YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -= $line_height;
	$FontSize =10;
	$pdf->setFont('','B');
	$Heading = _('Profit and loss for the ') . $NumberOfMonths . _(' months to ') . _('and including ') . $PeriodToDate;
	$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);

	$FontSize = 8;
	$pdf->setFont('','');
	$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize,
		_('Printed'). ': ' . Date($_SESSION['DefaultDateFormat'])
		. '   '. _('Page'). ' ' . $PageNumber);

	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Account'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,100,$FontSize,_('Account Name'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,_('Period Actual'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,_('Period Budget'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,_('Last Year'),'right');
	$YPos -= (2 * $line_height);
?>
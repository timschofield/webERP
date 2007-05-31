<?php
	/* 
	 * PDF page header for the balance sheet report.
	 * Suren Naidu 10/08/2005
	 *
	 */
	 
	$PageNumber++;
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize = 8;
	$YPos = $Page_Height - $Top_Margin;
	$pdf->addText($Left_Margin,$YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -= $line_height;
	$FontSize =10;
	$Heading = _('Balance Sheet as at ') . $BalanceDate;
	$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);

	$FontSize = 8;
	$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize,
		_('Printed'). ': ' . Date($_SESSION['DefaultDateFormat'])
		. '   '. _('Page'). ' ' . $PageNumber);

	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,$BalanceDate,'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,_('Last Year'),'right');
	$YPos -= (2 * $line_height);
?>
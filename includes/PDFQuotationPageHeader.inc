<?php
/* $Id$*/
/* pdf-php by R&OS code to set up a new sales order page */

// The $PageNumber variable is initialised in 0 by PDFStarter.php
// This page header increments variable $PageNumber before printing it.
$PageNumber++;
// Inserts a page break if it is not the first page
if ($PageNumber>1) {
	$pdf->newPage();
}

// Print 'Quotation' title
$pdf->addText(361, 500, 18, _('Quotation'));

// Print 'Quotation' info
$pdf->addTextWrap($Page_Width-$Right_Margin-200, $Page_Height-$Top_Margin-$FontSize*1, 200, $FontSize, _('Number'). ': '.$_GET['QuotationNo'], 'right');
$pdf->addTextWrap($Page_Width-$Right_Margin-200, $Page_Height-$Top_Margin-$FontSize*2, 200, $FontSize, _('Your Ref'). ': '.$myrow['customerref'], 'right');
$pdf->addTextWrap($Page_Width-$Right_Margin-200, $Page_Height-$Top_Margin-$FontSize*3, 200, $FontSize, _('Date'). ': '.ConvertSQLDate($myrow['quotedate']), 'right');
$pdf->addTextWrap($Page_Width-$Right_Margin-200, $Page_Height-$Top_Margin-$FontSize*4, 200, $FontSize, _('Page').': '.$PageNumber, 'right');

// Print company logo
$pdf->addJpegFromFile($_SESSION['LogoFile'], 301, 520, 0, 60);

// Print company info
$XPos = 361;
$YPos = 482;
$FontSize=14;
$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);
$FontSize =10;
$pdf->addText($XPos, $YPos-12, $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($XPos, $YPos-21, $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($XPos, $YPos-30, $FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$pdf->addText($XPos, $YPos-39, $FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
$pdf->addText($XPos, $YPos-48, $FontSize, $_SESSION['CompanyRecord']['email']);

// Print 'Delivery To' info
$XPos = 46;
$YPos = 566;
$FontSize=14;
$myrow = array_map(html_entity_decode, $myrow);
$pdf->addText($XPos, $YPos, $FontSize, _('Delivery To').':' );
$pdf->addText($XPos, $YPos-15, $FontSize, $myrow['deliverto']);
$pdf->addText($XPos, $YPos-30, $FontSize, $myrow['deladd1']);
$pdf->addText($XPos, $YPos-45, $FontSize, $myrow['deladd2']);
$pdf->addText($XPos, $YPos-60, $FontSize, $myrow['deladd3'] . ' ' . $myrow['deladd4'] . ' ' . $myrow['deladd5']);

// Print 'Quotation For' info
$YPos -= 80;
$pdf->addText($XPos, $YPos, $FontSize, _('Quotation For').':');
$pdf->addText($XPos, $YPos-15, $FontSize, $myrow['name']);
$pdf->addText($XPos, $YPos-30, $FontSize, $myrow['address1']);
$pdf->addText($XPos, $YPos-45, $FontSize, $myrow['address2']);
$pdf->addText($XPos, $YPos-60, $FontSize, $myrow['address3'] . ' ' . $myrow['address4'] . ' ' . $myrow['address5']);

// Draw a rectangle with rounded corners around 'Delivery To' info
$XPos= 50;
$YPos += 12;
$pdf->RoundRectangle($XPos-10, $YPos+60+10, 225+10+10, 60+10+10, 10, 10);// Function RoundRectangle from includes/class.pdf.php

// Draw a rectangle with rounded corners around 'Quotation For' info
$YPos -= 82;
$pdf->RoundRectangle($XPos-10, $YPos+60+10, 225+10+10, 60+10+10, 10, 10);// Function RoundRectangle from includes/class.pdf.php

// Print the currency name
include($PathPrefix . 'includes/CurrenciesArray.php'); // To get the currency name from the currency code.
$pdf->addText($Page_Width/2-60, $YPos-5, $FontSize, _('All amounts stated in') . ' - ' . $myrow['currcode'] . ' ' . $CurrencyName[$myrow['currcode']]);

// Print table header
$XPos = 40;
$YPos -= 37;
$FontSize=10;
$LeftOvers = $pdf->addTextWrap( 42, $YPos,103, $FontSize, _('Item Code'),'left');
	if (strlen($LeftOvers) > 0) { // If translated text is greater than 103, prints remainder
		$LeftOvers = $pdf->addTextWrap(585,$YPos-$FontSize,65, $FontSize, $LeftOvers,'left');
	}
$LeftOvers = $pdf->addTextWrap(145, $YPos,250, $FontSize, _('Item Description'),'left');
$LeftOvers = $pdf->addTextWrap(420, $YPos, 85, $FontSize, _('Quantity'),'right');
$LeftOvers = $pdf->addTextWrap(485, $YPos, 85, $FontSize, _('Price'),'right');
$LeftOvers = $pdf->addTextWrap(535, $YPos, 85, $FontSize, _('Discount'),'right');
$LeftOvers = $pdf->addTextWrap(615, $YPos, 55, $FontSize, _('Tax Class'),'right');
	if (strlen($LeftOvers) > 0) { // If translated text is greater than 55, prints remainder
		$LeftOvers = $pdf->addTextWrap(615,$YPos-$FontSize,55, $FontSize, $LeftOvers,'right');
	}
$LeftOvers = $pdf->addTextWrap(665, $YPos, 70, $FontSize, _('Tax Amount'),'right');
	if (strlen($LeftOvers) > 0) { // If translated text is greater than 70, prints remainder
		$LeftOvers = $pdf->addTextWrap(665, $YPos-$FontSize, 70, $FontSize, $LeftOvers,'right');
	}
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-90, $YPos, 90, $FontSize, _('Total'),'right');

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+350,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+360,$Left_Margin+10, $Bottom_Margin+360);

/*line under headings to top left */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+335,$Left_Margin, $Bottom_Margin+335);


/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+350,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+350,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,350,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+350);

$YPos -= (2*$line_height);
?>

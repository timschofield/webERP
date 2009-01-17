<?php
/* $Revision: 1.9 $ */
/* pdf-php by R&OS code to set up a new sales order page */
if ($PageNumber>1){
	$pdf->newPage();
}
// Modified 60->140 and 60->50 by Arnold on 1-8-2008
$XPos = $Page_Width/2 - 140;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos+20,520,0,60);

// Added by Arnold on 1-8-2008 to move text to right
$XPos = $XPos + 80;

$FontSize=18;
$pdf->addText($XPos, 500,$FontSize, _('Quotation'));
$FontSize=14;
$YPos = 480;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);
$FontSize =10;
$pdf->addText($XPos, $YPos-12,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($XPos, $YPos-21,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($XPos, $YPos-30,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$pdf->addText($XPos, $YPos-39,$FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
$pdf->addText($XPos, $YPos-48,$FontSize, $_SESSION['CompanyRecord']['email']);


$XPos = 46;
$YPos = 566;

$FontSize=14;
$pdf->addText($XPos, $YPos,$FontSize, _('Delivery To').':' );
$pdf->addText($XPos, $YPos-15,$FontSize, $myrow['deliverto']);
$pdf->addText($XPos, $YPos-30,$FontSize, $myrow['deladd1']);
$pdf->addText($XPos, $YPos-45,$FontSize, $myrow['deladd2']);
$pdf->addText($XPos, $YPos-60,$FontSize, $myrow['deladd3'] . ' ' . $myrow['deladd4'] . ' ' . $myrow['deladd5']);

$YPos -= 80;

$pdf->addText($XPos, $YPos,$FontSize, _('Quotation For').':');
$pdf->addText($XPos, $YPos-15,$FontSize, $myrow['name']);
$pdf->addText($XPos, $YPos-30,$FontSize, $myrow['address1']);
$pdf->addText($XPos, $YPos-45,$FontSize, $myrow['address2']);
$pdf->addText($XPos, $YPos-60,$FontSize, $myrow['address3'] . ' ' . $myrow['address4'] . ' ' . $myrow['address5']);


$XPos= 50;
$YPos += 25;
/*draw a nice curved corner box around the delivery details */
/*from the top right */
$pdf->partEllipse($XPos+225,$YPos+60,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+225, $YPos+70,$XPos, $YPos+70);
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+60,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+60,$XPos-10, $YPos);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-10,$XPos+225, $YPos-10);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+225, $YPos,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+235, $YPos,$XPos+235, $YPos+60);


$YPos -= 82;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+225,$YPos+60,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+225, $YPos+70,$XPos, $YPos+70);
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+60,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+60,$XPos-10, $YPos);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-10,$XPos+225, $YPos-10);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+225, $YPos,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+235, $YPos,$XPos+235, $YPos+60);


$pdf->addText(620, 560,$FontSize, _('Number'). ':');
$pdf->addText(700, 560,$FontSize, $_GET['QuotationNo']);
$pdf->addText(620, 560-15,$FontSize, _('Your Ref'). ':');
$pdf->addText(700, 560-15,$FontSize, $myrow['customerref']);
$pdf->addText(620, 560-30,$FontSize,  _('Date'). ':');
$pdf->addText(700, 560-30,$FontSize,  ConvertSQLDate($myrow['orddate']));
$pdf->addText(620, 560-45,$FontSize,  _('Page'). ':');
$pdf->addText(700, 560-45,$FontSize,  $PageNumber);

$YPos -= 37;
$XPos = 40;

$FontSize=10;
$LeftOvers = $pdf->addTextWrap($XPos+2,$YPos,127,$FontSize, _('Item Code'),'left');
$LeftOvers = $pdf->addTextWrap(145,$YPos,250,$FontSize, _('Item Description'),'left');
$LeftOvers = $pdf->addTextWrap(420,$YPos,85,$FontSize, _('Quantity'),'right');
$LeftOvers = $pdf->addTextWrap(485,$YPos,85,$FontSize,_('Price'),'right');
$LeftOvers = $pdf->addTextWrap(535,$YPos,85,$FontSize, _('Discount'),'right');
$LeftOvers = $pdf->addTextWrap(585,$YPos,85,$FontSize, _('Tax Class'),'right');
$LeftOvers = $pdf->addTextWrap(650,$YPos,85,$FontSize, _('Tax Amount'),'right');
$LeftOvers = $pdf->addTextWrap(700,$YPos,90,$FontSize, _('Total'),'right');


/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+370,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+380,$Left_Margin+10, $Bottom_Margin+380);

/*line under headings to top left */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+355,$Left_Margin, $Bottom_Margin+355);


/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+370,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+370,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+370);

$YPos -= $line_height;

$FontSize =12;

?>

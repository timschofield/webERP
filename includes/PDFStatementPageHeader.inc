<?php
/* $Revision: 1.4 $ */
/* PDFlib code to set up a new page */

$Perforation = $Page_Width - $RightMargin - 160;

$YPos = $Page_Height - $Top_Margin-30;

$pdf->addJpegFromFile('logo.jpg',$Left_Margin,$YPos,90,60);
$FontSize =15;

$XPos = $Page_Width/2 - 100;
$pdf->addText($XPos, $YPos+30,$FontSize, _('Statement') );

$FontSize = 12;
$pdf->addText($XPos + 70, $YPos+30,$FontSize, ' '. _('as of').' ' . Date($_SESSION['DefaultDateFormat']));

$FontSize = 10;
$XPos = $Perforation +2;
$pdf->addText($XPos, $YPos+30,$FontSize, _('Remittance Advice') );
$pdf->addText($XPos, $YPos+18,$FontSize, _('Statement dated').' ' . Date($_SESSION['DefaultDateFormat']));

$XPos = $Perforation-50;
$pdf->addText($XPos, $YPos+8,$FontSize, _('Page').': ' . $PageNumber);

/*Also show the page number on the remittance section */
$XPos = $Perforation;
$pdf->addText($XPos+1, $YPos+8,$FontSize, _('Page').': ' . $PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin - 45;

$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

$FontSize =8;
$pdf->addText($XPos, $YPos-12,$FontSize, $_SESSION['CompanyRecord']['postaladdress']);
$pdf->addText($XPos, $YPos-21,$FontSize, $_SESSION['CompanyRecord']['regoffice1'] . ' ' . $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($XPos, $YPos-30,$FontSize, _('Ph').': ' . $_SESSION['CompanyRecord']['telephone'] . ' '._('Fax').': ' . $_SESSION['CompanyRecord']['fax']);
$pdf->addText($XPos, $YPos-39,$FontSize, $_SESSION['CompanyRecord']['email']);

/*Now the remittance section just company and return postal address */
$pdf->addText($Perforation+1, $YPos-15,$FontSize, _('Please send with remittance to').':');
$pdf->addText($Perforation+1, $YPos-24,$FontSize, $_SESSION['CompanyRecord']['coyname']);
$LeftOvers=$pdf->addTextWrap($Perforation+1,$YPos-33,$Page_Width-$Right_Margin-$Perforation,$FontSize,$_SESSION['CompanyRecord']['postaladdress'],'left');
$LeftOvers = $pdf->addTextWrap($Perforation+1, $YPos-42,$Page_Width-$Right_Margin-$Perforation,$FontSize,$LeftOvers, 'left');

/*Now the customer details and statement address */

$XPos = $Left_Margin;
$YPos -= ($line_height*4);

$FontSize=12;

$pdf->addText($XPos, $YPos,$FontSize, $StmtHeader['name']);
$pdf->addText($XPos, $YPos-14,$FontSize, $StmtHeader['address1']);
$pdf->addText($XPos, $YPos-28,$FontSize, $StmtHeader['address2']);
$pdf->addText($XPos, $YPos-42,$FontSize, $StmtHeader['address3']);
$pdf->addText($XPos, $YPos-56,$FontSize, $StmtHeader['address4']);

$YPos -= ($line_height*4);
/*Now note the customer code in the remittance section too */
$FontSize=8;
$pdf->addText($Perforation+1, $YPos,$FontSize, _('Customer Code').': ' . $StmtHeader['debtorno']);

$XPos = $Page_Width/2 - 60;
$pdf->addText($XPos, $YPos,$FontSize, _('All amounts stated in') . ' - ' . $StmtHeader['currency']);
$YPos -= $line_height;
$pdf->addText($XPos, $YPos,$FontSize, $StmtHeader['terms']);

$YPos -= $line_height;
$XPos = $Left_Margin;


/*draw a nice curved corner box around the statement details */
/*from the top right */
$pdf->partEllipse($Perforation-10,$YPos-10,0,90,10,10);
/*line to the top left */
$pdf->line($Perforation-10, $YPos,$Left_Margin+10, $YPos);
/*Do top left corner */
$pdf->partEllipse($Left_Margin+10, $YPos-10,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $YPos-10,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Perforation-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Perforation-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Perforation, $Bottom_Margin+10,$Perforation, $YPos-10);


/*draw a nice curved corner box around the remittance advice section */
$pdf->partEllipse($Page_Width-$Right_Margin-10,$YPos-10,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $YPos, $Perforation + 11, $YPos);
/*Do top left corner */
$pdf->partEllipse($Perforation+11, $YPos-10,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Perforation+1, $YPos-10,$Perforation+1, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Perforation+11, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Perforation+11, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $YPos-10);




$YPos -= $line_height;
$FontSize =10;
/*Set up headings */
$pdf->addText($Left_Margin+1, $YPos,$FontSize, _('Trans Type') );
$pdf->addText($Left_Margin+57, $YPos,$FontSize, _('Number') );
$pdf->addText($Left_Margin+108, $YPos,$FontSize, _('Date') );
$pdf->addText($Left_Margin+178, $YPos,$FontSize, _('Charges') );
$pdf->addText($Left_Margin+239, $YPos,$FontSize, _('Credits') );
$pdf->addText($Left_Margin+289, $YPos,$FontSize, _('Allocated') );
$pdf->addText($Left_Margin+336, $YPos,$FontSize, _('Outstanding') );


/*Set up remittance section headings */
$FontSize=8;
$pdf->addText($Perforation+1, $YPos,$FontSize, _('Trans') );
$pdf->addText($Perforation+35, $YPos,$FontSize, _('Number') );
$pdf->addText($Perforation+80, $YPos,$FontSize, _('Outstanding') );


$YPos -= $line_height;
/*draw a line */
$pdf->line($Page_Width-$Right_Margin, $YPos,$XPos, $YPos);

$YPos -= $line_height;
$XPos = $Left_Margin;

?>
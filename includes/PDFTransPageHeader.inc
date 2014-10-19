<?php
/* $Id: PDFTransPageHeader.inc 6826 2014-08-17 21:53:13Z rchacon $ */

/*	Please note that addTextWrap() prints a font-size-height further down than
	addText() and other functions. Use addText() instead of addTextWrap() to
	print left aligned elements.*/

if (!$FirstPage){ /* only initiate a new page if its not the first */
	$pdf->newPage();
}

$YPos = $Page_Height-$Top_Margin;

$pdf->addJpegFromFile($_SESSION['LogoFile'],$Page_Width/2 -120,$YPos-40,0,60);
$FontSize =15;
if ($InvOrCredit=='Invoice') {

        $pdf->addText($Page_Width - 200, $YPos, $FontSize, _('TAX INVOICE') . ' ');
} else {
	$pdf->addText($Page_Width - 200, $YPos, $FontSize, _('CREDIT NOTE') . ' ');
}

$XPos = $Page_Width - 265;
$YPos -= 111;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+225,$YPos+100,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+225, $YPos+110,$XPos, $YPos+110);
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+100,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+100,$XPos-10, $YPos+5);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+5,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-5,$XPos+225, $YPos-5);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+225, $YPos+5,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+235, $YPos+5,$XPos+235, $YPos+100);

$YPos = $Page_Height - $Top_Margin - 10;

$FontSize = 10;

$pdf->addText($Page_Width-268, $YPos-13, $FontSize, _('Number'));
$pdf->addText($Page_Width-180, $YPos-13, $FontSize, $FromTransNo);
$pdf->addText($Page_Width-268, $YPos-26, $FontSize, _('Customer Code'));
$pdf->addText($Page_Width-180, $YPos-26, $FontSize, $myrow['debtorno'] . ' ' . _('Branch') . ' ' . $myrow['branchcode']);
$pdf->addText($Page_Width-268, $YPos-39, $FontSize, _('Date'));
$pdf->addText($Page_Width-180, $YPos-39, $FontSize, ConvertSQLDate($myrow['trandate']));


if ($InvOrCredit=='Invoice') {

	$pdf->addText($Page_Width-268, $YPos-52, $FontSize, _('Order No'));
	$pdf->addText($Page_Width-180, $YPos-52, $FontSize, $myrow['orderno']);
	$pdf->addText($Page_Width-268, $YPos-65, $FontSize, _('Order Date'));
	$pdf->addText($Page_Width-180, $YPos-65, $FontSize, ConvertSQLDate($myrow['orddate']));
	$pdf->addText($Page_Width-268, $YPos-78, $FontSize, _('Dispatch Detail'));
	$pdf->addText($Page_Width-180, $YPos-78, $FontSize, $myrow['shippername'] . '-' . $myrow['consignment']);
	$pdf->addText($Page_Width-268, $YPos-91, $FontSize, _('Dispatched From'));
	$pdf->addText($Page_Width-180, $YPos-91, $FontSize, $myrow['locationname']);
}


$pdf->addText($Page_Width-268, $YPos-108, $FontSize, _('Page'));
$pdf->addText($Page_Width-180, $YPos-108, $FontSize, $PageNumber);

/*End of the text in the right side box */

/*Now print out the company name and address in the middle under the logo */
$XPos = $Page_Width/2 -90;
$YPos = $Page_Height - $Top_Margin-60;
$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);

$FontSize=8;
$pdf->addText($XPos, $YPos-10, $FontSize, $_SESSION['TaxAuthorityReferenceName'] . ': ' . $_SESSION['CompanyRecord']['gstno']);
$pdf->addText($XPos, $YPos-19,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($XPos, $YPos-28,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($XPos, $YPos-37,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$pdf->addText($XPos, $YPos-46, $FontSize, $_SESSION['CompanyRecord']['regoffice6']);
$pdf->addText($XPos, $YPos-54, $FontSize, _('Phone') . ':' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$pdf->addText($XPos, $YPos-63, $FontSize, _('Email') . ': ' . $_SESSION['CompanyRecord']['email']);

/*Now the customer charged to details top left */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin;

$FontSize=10;

$pdf->addText($XPos, $YPos, $FontSize, _('Sold To') . ':');
$XPos +=80;

if ($myrow['invaddrbranch']==0){
	$pdf->addText($XPos, $YPos, $FontSize, html_entity_decode($myrow['name']));
	$pdf->addText($XPos, $YPos-14, $FontSize, html_entity_decode($myrow['address1']));
	$pdf->addText($XPos, $YPos-28, $FontSize, html_entity_decode($myrow['address2']));
	$pdf->addText($XPos, $YPos-42, $FontSize, html_entity_decode($myrow['address3']) . ' ' . html_entity_decode($myrow['address4']));
} else {
	$pdf->addText($XPos, $YPos, $FontSize, html_entity_decode($myrow['name']));
	$pdf->addText($XPos, $YPos-14, $FontSize, html_entity_decode($myrow['brpostaddr1']));
	$pdf->addText($XPos, $YPos-28, $FontSize, html_entity_decode($myrow['brpostaddr2']));
	$pdf->addText($XPos, $YPos-42, $FontSize, html_entity_decode($myrow['brpostaddr3']) . ' ' . html_entity_decode($myrow['brpostaddr4']) . ' ' . html_entity_decode($myrow['brpostaddr5']) . ' ' . html_entity_decode($myrow['brpostaddr6']));
}


$XPos -=80;
$YPos -=($line_height*4);

if ($InvOrCredit=='Invoice') {

	$pdf->addText($XPos, $YPos, $FontSize, _('Delivered To') . ':');
	$XPos +=80;
// Before trying to call htmlspecialchars_decode, check that its supported, if not substitute a compatible version
if (!function_exists('htmlspecialchars_decode')) {
        function htmlspecialchars_decode($str) {
                $trans = get_html_translation_table(HTML_SPECIALCHARS);

                $decode = ARRAY();
                foreach ($trans AS $char=>$entity) {
                        $decode[$entity] = $char;
                }

                $str = strtr($str, $decode);

                return $str;
        }
}
	$pdf->addText($XPos, $YPos, $FontSize, html_entity_decode($myrow['deliverto']));
	$pdf->addText($XPos, $YPos-14, $FontSize, html_entity_decode($myrow['deladd1']));
	$pdf->addText($XPos, $YPos-28, $FontSize, html_entity_decode($myrow['deladd2']));
	$pdf->addText($XPos, $YPos-42, $FontSize, html_entity_decode($myrow['deladd3']) . ' ' . html_entity_decode($myrow['deladd4']) . ' ' . html_entity_decode($myrow['deladd5']));
	$XPos -=80;
}
if ($InvOrCredit=='Credit'){
/* then its a credit note */

	$pdf->addText($XPos, $YPos, $FontSize, _('Charge Branch') . ':');
	$XPos +=80;
	$pdf->addText($XPos, $YPos, $FontSize, html_entity_decode($myrow['brname']));
	$pdf->addText($XPos, $YPos-14, $FontSize, html_entity_decode($myrow['braddress1']));
	$pdf->addText($XPos, $YPos-28, $FontSize, html_entity_decode($myrow['braddress2']));
	$pdf->addText($XPos, $YPos-42, $FontSize, html_entity_decode($myrow['braddress3']) . ' ' . html_entity_decode($myrow['braddress4']) . ' ' . html_entity_decode($myrow['braddress5']) . ' ' . html_entity_decode($myrow['braddress6']));
	$XPos -=80;
}

$XPos = $Left_Margin;

$YPos = $Page_Height - $Top_Margin - 80;
/*draw a line under the company address and charge to address
$pdf->line($XPos, $YPos,$Right_Margin, $YPos); */

$XPos = $Page_Width/2;

$XPos = $Left_Margin;
$YPos -= ($line_height*2);

include($PathPrefix . 'includes/CurrenciesArray.php'); // To get the currency name from the currency code.
$pdf->addText($Left_Margin, $YPos-8, $FontSize, _('All amounts stated in') . ': ' . $myrow['currcode'] . ' ' . $CurrencyName[$myrow['currcode']]);

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+390,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+400,$Left_Margin+10, $Bottom_Margin+400);
/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+390,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+390,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+390);


$YPos -= ($line_height*2);
/*Set up headings */
$FontSize=10;
$pdf->addText($Left_Margin + 2, $YPos, $FontSize, _('Customer Tax Ref') . ':');
$pdf->addText($Left_Margin+100, $YPos, $FontSize, $myrow['taxref']);


/*Print a vertical line */
$pdf->line($Left_Margin+248, $YPos-10+$line_height+3,$Left_Margin+248, $YPos - 18);
if ($InvOrCredit=='Invoice'){
	$pdf->addText($Left_Margin + 252, $YPos, $FontSize, _('Customer Order Ref.') . ':');
	$pdf->addText($Left_Margin+370, $YPos, $FontSize, $myrow['customerref']);
}
/*Print a vertical line */
$pdf->line($Left_Margin+450, $YPos+$line_height-7,$Left_Margin+450,$YPos-18);

$pdf->addText($Left_Margin+453, $YPos, $FontSize, _('Sales Person') . ':');
$pdf->addText($Left_Margin+510, $YPos, $FontSize, $myrow['salesmanname']);

$YPos -= 8;
/*draw a line */
$pdf->line($XPos, $YPos-10,$Page_Width-$Right_Margin, $YPos-10);

$YPos -= 12;

$TopOfColHeadings = $YPos-10;

$pdf->addText($Left_Margin+5, $YPos, $FontSize, _('Item Code'));
$pdf->addText($Left_Margin+100, $YPos, $FontSize, _('Description'));
$pdf->addText($Left_Margin+382, $YPos, $FontSize, _('Unit Price'));
$pdf->addText($Left_Margin+485, $YPos, $FontSize, _('Quantity'));
$pdf->addText($Left_Margin+555, $YPos, $FontSize, _('UOM'));
$pdf->addText($Left_Margin+595, $YPos, $FontSize, _('Discount'));
$pdf->addText($Left_Margin+690, $YPos, $FontSize, _('Extended Price'));

$YPos-=8;

/*draw a line */
$pdf->line($XPos, $YPos-5,$Page_Width-$Right_Margin, $YPos-5);

$YPos -= ($line_height);

?>

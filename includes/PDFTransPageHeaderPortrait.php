<?php

/*	Please note that addTextWrap() prints a font-size-height further down than
	addText() and other functions. Use addText() instead of addTextWrap() to
	print left aligned elements.*/

if(!$FirstPage) { /* only initiate a new page if its not the first */
	$pdf->newPage();
}

$YPos = $Page_Height-$Top_Margin;

// Company Logo:
/*$pdf->addJpegFromFile($_SESSION['LogoFile'], $Page_Width/2-118, $YPos-60, 0, 35);*/
$pdf->Image(
	$_SESSION['LogoFile'],// Name of the file containing the image.
	$Page_Width/2-118,// Abscissa from left border to the upper-left corner (LTR).
	$Page_Height -($YPos-60) -(35),// Ordinate from top border to the upper-left corner (LTR).
	0,// Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
	35,// Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
	''// Image format. If not specified, the type is inferred from the file extension.
);// Public function Image() in tcpdf/tcpdf.php.

$FontSize =15;
if($InvOrCredit=='Invoice') {

	$pdf->addText($Page_Width/2 - 60, $YPos, $FontSize, __('TAX INVOICE') . ' ');
} else {
	$pdf->addText($Page_Width/2 - 60, $YPos, $FontSize, __('TAX CREDIT NOTE') . ' ');
}

// Prints page number:
$FontSize = 10;
$YPos -= $FontSize; //Downs one line height mesure (addText position is from left-bottom).
$pdf->addTextWrap($Page_Width-$Left_Margin-72, $YPos, 72, $FontSize, __('Page') . ' ' . $PageNumber, 'right');


$XPos = $Page_Width - 265;
$YPos -= 85;
// Draws a rounded rectangle around billing details:
$pdf->RoundRectangle(
	$XPos-10,// RoundRectangle $XPos.
	$YPos+77,// RoundRectangle $YPos.
	245,// RoundRectangle $Width.
	97,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.

$YPos = $Page_Height - $Top_Margin - 10;

$FontSize = 10;
$LineHeight = 13;
$LineCount = 1;
$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, __('Number'));
$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $FromTransNo);
$LineCount += 1;
$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, __('Customer Code'));
$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $MyRow['debtorno'] . ' ' . __('Branch') . ' ' . $MyRow['branchcode']);
$LineCount += 1;
$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, __('Date'));
$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, ConvertSQLDate($MyRow['trandate']));

if($InvOrCredit=='Invoice') {
	$LineCount += 1;
	$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, __('Order No'));
	$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $MyRow['orderno']);
	$LineCount += 1;
	$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, __('Order Date'));
	$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, ConvertSQLDate($MyRow['orddate']));
	$LineCount += 1;
	$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, __('Dispatch Detail'));
	$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $MyRow['shippername'] . '-' . $MyRow['consignment']);
	$LineCount += 1;
	$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, __('Dispatched From'));
	$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $MyRow['locationname']);
}

/*End of the text in the right side box */

/*Now print out company info at the top left */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin - 20;

$FontSize = 10;
$LineHeight = 13;
$LineCount = 0;

$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $_SESSION['CompanyRecord']['coyname']);

$FontSize = 8;
$LineHeight = 10;

if($_SESSION['CompanyRecord']['regoffice1'] <> '') {
  $LineCount += 1;
  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
}
if($_SESSION['CompanyRecord']['regoffice2'] <> '') {
  $LineCount += 1;
  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
}
if(($_SESSION['CompanyRecord']['regoffice3'] <> '') OR ($_SESSION['CompanyRecord']['regoffice4'] <> '') OR ($_SESSION['CompanyRecord']['regoffice5'] <> '')) {
  $LineCount += 1;
  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . '  ' . $_SESSION['CompanyRecord']['regoffice4'] . '  ' . $_SESSION['CompanyRecord']['regoffice5']);  // country in 6 not printed
}
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, __('Phone') . ':' . $_SESSION['CompanyRecord']['telephone']);
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, __('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, __('Email') . ': ' . $_SESSION['CompanyRecord']['email']);
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $_SESSION['TaxAuthorityReferenceName'] . ': ' . $_SESSION['CompanyRecord']['gstno']);

/*Now the customer company info */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin - 120;

$XPos += 20;
$FontSize = 10;
$LineHeight = 13;
$LineCount = 0;

if($MyRow['invaddrbranch']==0) {
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['name']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['address1']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['address2']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['address3']) . '  ' . html_entity_decode($MyRow['address4'])  . '  ' . html_entity_decode($MyRow['address5'])  . ' ' . html_entity_decode($MyRow['address6']));
} else {
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['name']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['brpostaddr1']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['brpostaddr2']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['brpostaddr3']) . '  ' . html_entity_decode($MyRow['brpostaddr4']) . '  ' . html_entity_decode($MyRow['brpostaddr5']) . ' ' . html_entity_decode($MyRow['brpostaddr6']));
}

$XPos = $Page_Width - 265;
$YPos = $Page_Height - $Top_Margin - 120;

$FontSize = 8;
$LineHeight = 10;
$LineCount = 0;

$pdf->addText($Left_Margin, $YPos, $FontSize, __('Sold To') . ':');

if($InvOrCredit=='Invoice') {
	$pdf->addText($XPos, $YPos, $FontSize, __('Delivered To (check Dispatch Detail)') . ':');
	$FontSize = 10;
	$LineHeight = 13;
	$XPos += 20;
	$LineCount += 1;

    // Before trying to call htmlspecialchars_decode, check that its supported, if not substitute a compatible version
    if(!function_exists('htmlspecialchars_decode')) {
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

	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['deliverto']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['deladd1']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['deladd2']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['deladd3']) . '  ' . html_entity_decode($MyRow['deladd4']) . '  ' . html_entity_decode($MyRow['deladd5']) . ' ' . html_entity_decode($MyRow['deladd6']));
}
else {
/* then its a credit note */
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, __('Charge Branch') . ':');
	$FontSize = 10;
	$LineHeight = 13;
	$XPos +=20;
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['brname']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['braddress1']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['braddress2']));
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, html_entity_decode($MyRow['braddress3']) . '  ' . html_entity_decode($MyRow['braddress4']) . '  ' . html_entity_decode($MyRow['braddress5']) . ' ' . html_entity_decode($MyRow['braddress6']));
}

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin - 190;
$FontSize = 8;

require_once('includes/CurrenciesArray.php');// To get the currency name from the currency code.
$pdf->addText($Left_Margin, $YPos-8, $FontSize, __('All amounts stated in') . ': ' . $MyRow['currcode'] . ' - ' . $CurrencyName[$MyRow['currcode']]);

if ($InvOrCredit=='Invoice') {
	$pdf->addText($Page_Width-$Left_Margin-70, $YPos-8, $FontSize, __('Due Date') . ': ' . $DisplayDueDate);
}

$BoxHeight = $Page_Height-282;

// Draws a rounded rectangle around line items:
$pdf->RoundRectangle(
	$Left_Margin,// RoundRectangle $XPos.
	$Bottom_Margin+$BoxHeight+10,// RoundRectangle $YPos.
	$Page_Width-$Right_Margin-$Left_Margin,// RoundRectangle $Width.
	$BoxHeight+10,// RoundRectangle $Height.
	10,// RoundRectangle $RadiusX.
	10);// RoundRectangle $RadiusY.

$YPos -= 35;
/*Set up headings */
$FontSize=10;
$LineHeight = 12;

$pdf->addText($Left_Margin+2, $YPos+$LineHeight, $FontSize, __('Cust. Tax Ref') . ':');
/*Print a vertical line */
$pdf->line($Left_Margin+178, $YPos+$LineHeight,$Left_Margin+178, $YPos-$LineHeight*2+4);
$pdf->addText($Left_Margin+180, $YPos+$LineHeight, $FontSize, __('Cust. Reference No.') . ':');
/*Print a vertical line */
$pdf->line($Left_Margin+358, $YPos+$LineHeight,$Left_Margin+358, $YPos-$LineHeight*2+4);
$pdf->addText($Left_Margin+360, $YPos+$LineHeight, $FontSize, __('Sales Person') . ':');
$pdf->addText($Left_Margin+12, $YPos, $FontSize, $MyRow['taxref']);
if($InvOrCredit=='Invoice') {
	$pdf->addText($Left_Margin+190, $YPos, $FontSize, $MyRow['customerref']);
}
$pdf->addText($Left_Margin+370, $YPos, $FontSize, $MyRow['salesmanname']);

$YPos -= 20;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$TopOfColHeadings = $YPos;

$pdf->addText($Left_Margin, $YPos, $FontSize, __('Item Code'));
$pdf->addText($Left_Margin+80, $YPos, $FontSize, __('Description'));
$pdf->addText($Left_Margin+270, $YPos, $FontSize, __('Unit Price'));
$pdf->addText($Left_Margin+350, $YPos, $FontSize, __('Qty'));
$pdf->addText($Left_Margin+390, $YPos, $FontSize, __('UOM'));
$pdf->addText($Left_Margin+420, $YPos, $FontSize, __('Disc.'));
$pdf->addText($Left_Margin+450, $YPos, $FontSize, __('Price'));

$YPos -= 12;

/*draw a line */
$pdf->line($XPos, $YPos-1,$Page_Width-$Right_Margin, $YPos-1);

$YPos -= ($LineHeight);

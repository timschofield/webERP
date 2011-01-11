<?php

/* $Id$ */
/* R&OS PHP-pdf class code to head up a new page */

if (!$FirstPage){ /* only initiate a new page if its not the first */
	$pdf->newPage();
}

// Before trying to call htmlspecialchars_decode, check that its supported, if not substitute a compatible version
if (!function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode($str) {
		$trans = get_html_translation_table(HTML_SPECIALCHARS);

		$decode = array();
		foreach ($trans AS $char=>$entity) {
			$decode[$entity] = $char;
		}

		$str = strtr($str, $decode);

		return $str;
	}
}

if ($FromTransNo=='Preview') {
	$myrow['debtorno']=str_pad('',10,'x');
	$myrow['branchcode']=str_pad('',6,'x');
	$myrow['trandate']='1901-01-01';
	$myrow['orderno']=str_pad('',6,'X');
	$myrow['orddate']='1901-01-01';
	$myrow['shippername']=str_pad('',6,'x');
	$myrow['consignment']=str_pad('',6,'X');
	$myrow['locationname']=str_pad('',20,'x');
	$myrow['name']=str_pad('',40,'x');
	$myrow['address1']=str_pad('',30,'x');
	$myrow['address2']=str_pad('',30,'x');
	$myrow['address3']=str_pad('',30,'x');
	$myrow['address4']=str_pad('',30,'x');
	$myrow['deliverto']=str_pad('',40,'x');
	$myrow['deladd1']=str_pad('',30,'x');
	$myrow['deladd2']=str_pad('',30,'x');
	$myrow['deladd3']=str_pad('',30,'x');
	$myrow['deladd4']=str_pad('',30,'x');
	$myrow['deladd5']=str_pad('',20,'x');
	$myrow['invaddrbranch']=0;
	$myrow['currcode']=str_pad('',10,'x');
	$myrow['taxref']=str_pad('',10,'x');
	$myrow['customerref']=str_pad('',10,'x');
	$myrow['salesmanname']=str_pad('',20,'x');
	$myrow2['discountpercent']='XXX';
	$myrow2['fxnet']='XXX';
	$myrow2['fxprice']='XXX';
	$myrow2['quantity']='XXX';
	$myrow2['stockid']=str_pad('',10,'x');
	$myrow2['description']=str_pad('',50,'x');
	$myrow2['units']=str_pad('',6,'x');
	$myrow2['narrative']=str_pad('',200,'x');
	$myrow['ovamount']='XXX';
	$myrow['ovfreight']='XXX';
	$myrow['ovgst']='XXX';
	$myrow['terms']=str_pad('',20,'x');
	$myrow['invtext']=str_pad('',200,'x');
}

$pdf->addJpegFromFile($_SESSION['LogoFile'],$FormDesign->logo->x,$Page_Height -$FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);
if ($InvOrCredit=='Invoice') {
	$pdf->addText($FormDesign->Title->x, $Page_Height -$FormDesign->Title->y, $FormDesign->Title->FontSize, _('TAX INVOICE') . ' ');
} else {
	$pdf->addText($FormDesign->Title->x, $Page_Height -$FormDesign->Title->y, $FormDesign->Title->FontSize, _('CREDIT NOTE') . ' ');
}
/*draw a nice curved corner box around the billing details */
/*from the top right*/
$pdf->RoundRectangle($FormDesign->BillingDetailBox->x, 
											$Page_Height - $FormDesign->BillingDetailBox->y,
											$FormDesign->BillingDetailBox->width, 
											$FormDesign->BillingDetailBox->height, 
											$FormDesign->BillingDetailBox->radius);

$pdf->addText($FormDesign->NumberCaption->x, $Page_Height -$FormDesign->NumberCaption->y, $FormDesign->NumberCaption->FontSize, _('Number'));
$pdf->addText($FormDesign->InvoiceNumber->x, $Page_Height -$FormDesign->InvoiceNumber->y, $FormDesign->InvoiceNumber->FontSize, $FromTransNo);
$pdf->addText($FormDesign->CodeCaption->x, $Page_Height -$FormDesign->CodeCaption->y, $FormDesign->CodeCaption->FontSize, _('Customer Code'));
$pdf->addText($FormDesign->CustomerCode->x, $Page_Height -$FormDesign->CustomerCode->y, $FormDesign->CustomerCode->FontSize, $myrow['debtorno'] . ' ' . _('Branch') . ' ' . $myrow['branchcode']);
$pdf->addText($FormDesign->DateCaption->x, $Page_Height -$FormDesign->DateCaption->y, $FormDesign->DateCaption->FontSize, _('Date'));
$pdf->addText($FormDesign->Date->x, $Page_Height -$FormDesign->Date->y, $FormDesign->Date->FontSize, ConvertSQLDate($myrow['trandate']));


if ($InvOrCredit=='Invoice') {

	$pdf->addText($FormDesign->OrderNumberCaption->x, $Page_Height -$FormDesign->OrderNumberCaption->y, $FormDesign->OrderNumberCaption->FontSize, _('Order No'));
	$pdf->addText($FormDesign->OrderNumber->x, $Page_Height -$FormDesign->OrderNumber->y, $FormDesign->OrderNumber->FontSize, $myrow['orderno']);
	$pdf->addText($FormDesign->OrderDateCaption->x, $Page_Height -$FormDesign->OrderDateCaption->y, $FormDesign->OrderDateCaption->FontSize, _('Order Date'));
	$pdf->addText($FormDesign->OrderDate->x, $Page_Height -$FormDesign->OrderDate->y, $FormDesign->OrderDate->FontSize, ConvertSQLDate($myrow['orddate']));
	$pdf->addText($FormDesign->ShipperCaption->x, $Page_Height -$FormDesign->ShipperCaption->y, $FormDesign->ShipperCaption->FontSize, _('Dispatch Detail'));
	$pdf->addText($FormDesign->ShipperName->x, $Page_Height -$FormDesign->ShipperName->y, $FormDesign->ShipperName->FontSize, $myrow['shippername'] . '-' . $myrow['consignment']);
	$pdf->addText($FormDesign->LocationCaption->x, $Page_Height -$FormDesign->LocationCaption->y, $FormDesign->LocationCaption->FontSize, _('Dispatched From'));
	$pdf->addText($FormDesign->Location->x, $Page_Height -$FormDesign->Location->y, $FormDesign->Location->FontSize, $myrow['locationname']);
}


$pdf->addText($FormDesign->PageNumberCaption->x, $Page_Height -$FormDesign->PageNumberCaption->y, $FormDesign->PageNumberCaption->FontSize, _('Page'));
$pdf->addText($FormDesign->PageNumber->x, $Page_Height -$FormDesign->PageNumber->y, $FormDesign->PageNumber->FontSize, $PageNumber);

/*End of the text in the right side box */

/*Now print out the company name and address in the middle under the logo */
$pdf->addText($FormDesign->CompanyName->x, $Page_Height -$FormDesign->CompanyName->y, $FormDesign->CompanyName->FontSize, $_SESSION['CompanyRecord']['coyname']);

$FontSize=8;
$pdf->addText($FormDesign->TaxReference->x, $Page_Height -$FormDesign->TaxReference->y, $FormDesign->TaxReference->FontSize, $_SESSION['TaxAuthorityReferenceName'] . ': ' . $_SESSION['CompanyRecord']['gstno']);
$pdf->addText($FormDesign->CompanyAddress->Line1->x, $Page_Height -$FormDesign->CompanyAddress->Line1->y, $FormDesign->CompanyAddress->Line1->FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($FormDesign->CompanyAddress->Line2->x, $Page_Height -$FormDesign->CompanyAddress->Line2->y, $FormDesign->CompanyAddress->Line2->FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($FormDesign->CompanyAddress->Line3->x, $Page_Height -$FormDesign->CompanyAddress->Line3->y, $FormDesign->CompanyAddress->Line3->FontSize, $_SESSION['CompanyRecord']['regoffice3']);
$pdf->addText($FormDesign->CompanyAddress->Line4->x, $Page_Height -$FormDesign->CompanyAddress->Line4->y, $FormDesign->CompanyAddress->Line4->FontSize, $_SESSION['CompanyRecord']['regoffice4']);
$pdf->addText($FormDesign->CompanyAddress->Line5->x, $Page_Height -$FormDesign->CompanyAddress->Line5->y, $FormDesign->CompanyAddress->Line5->FontSize, $_SESSION['CompanyRecord']['regoffice5']);
$pdf->addText($FormDesign->CompanyAddress->Line6->x, $Page_Height -$FormDesign->CompanyAddress->Line6->y, $FormDesign->CompanyAddress->Line6->FontSize, $_SESSION['CompanyRecord']['regoffice6']);
$pdf->addText($FormDesign->CompanyPhone->x, $Page_Height -$FormDesign->CompanyPhone->y, $FormDesign->CompanyPhone->FontSize, _('Phone') . ':' . $_SESSION['CompanyRecord']['telephone']);
$pdf->addText($FormDesign->CompanyFax->x, $Page_Height -$FormDesign->CompanyFax->y, $FormDesign->CompanyFax->FontSize, _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$pdf->addText($FormDesign->CompanyEmail->x, $Page_Height -$FormDesign->CompanyEmail->y, $FormDesign->CompanyEmail->FontSize, _('Email') . ': ' . $_SESSION['CompanyRecord']['email']);

/*Now the customer charged to details top left */
$pdf->addText($FormDesign->InvoiceAddressCaption->x, $Page_Height -$FormDesign->InvoiceAddressCaption->y, $FormDesign->InvoiceAddressCaption->FontSize, _('Sold To') . ':');

if ($myrow['invaddrbranch']==0){
	$pdf->addText($FormDesign->InvoiceAddressName->x, $Page_Height -$FormDesign->InvoiceAddressName->y, $FormDesign->InvoiceAddressName->FontSize, htmlspecialchars_decode($myrow['name']));
	$pdf->addText($FormDesign->InvoiceAddress->Line1->x, $Page_Height -$FormDesign->InvoiceAddress->Line1->y, $FormDesign->InvoiceAddress->Line1->FontSize, $myrow['address1']);
	$pdf->addText($FormDesign->InvoiceAddress->Line2->x, $Page_Height -$FormDesign->InvoiceAddress->Line2->y, $FormDesign->InvoiceAddress->Line2->FontSize, $myrow['address2']);
	$pdf->addText($FormDesign->InvoiceAddress->Line3->x, $Page_Height -$FormDesign->InvoiceAddress->Line3->y, $FormDesign->InvoiceAddress->Line3->FontSize, $myrow['address3']);
	$pdf->addText($FormDesign->InvoiceAddress->Line4->x, $Page_Height -$FormDesign->InvoiceAddress->Line4->y, $FormDesign->InvoiceAddress->Line4->FontSize, $myrow['address4']);
} else {
	$pdf->addText($FormDesign->InvoiceAddressName->x, $Page_Height -$FormDesign->InvoiceAddressName->y, $FormDesign->InvoiceAddressName->FontSize, htmlspecialchars_decode($myrow['name']));
	$pdf->addText($FormDesign->InvoiceAddress->Line1->x, $Page_Height -$FormDesign->InvoiceAddress->Line1->y, $FormDesign->InvoiceAddress->Line1->FontSize, $myrow['brpostaddr1']);
	$pdf->addText($FormDesign->InvoiceAddress->Line2->x, $Page_Height -$FormDesign->InvoiceAddress->Line2->y, $FormDesign->InvoiceAddress->Line2->FontSize, $myrow['brpostaddr2']);
	$pdf->addText($FormDesign->InvoiceAddress->Line3->x, $Page_Height -$FormDesign->InvoiceAddress->Line3->y, $FormDesign->InvoiceAddress->Line3->FontSize, $myrow['brpostaddr3']);
	$pdf->addText($FormDesign->InvoiceAddress->Line4->x, $Page_Height -$FormDesign->InvoiceAddress->Line4->y, $FormDesign->InvoiceAddress->Line4->FontSize, $myrow['brpostaddr4']);
	$pdf->addText($FormDesign->InvoiceAddress->Line5->x, $Page_Height -$FormDesign->InvoiceAddress->Line5->y, $FormDesign->InvoiceAddress->Line5->FontSize, $myrow['brpostaddr5']);
	$pdf->addText($FormDesign->InvoiceAddress->Line6->x, $Page_Height -$FormDesign->InvoiceAddress->Line6->y, $FormDesign->InvoiceAddress->Line6->FontSize, $myrow['brpostaddr6']);
}

if ($InvOrCredit=='Invoice') {
	$pdf->addText($FormDesign->DeliveryAddressCaption->x, $Page_Height -$FormDesign->DeliveryAddressCaption->y, $FormDesign->DeliveryAddressCaption->FontSize, _('Delivered To') . ':');
	$pdf->addText($FormDesign->DeliveryAddressName->x, $Page_Height -$FormDesign->DeliveryAddressName->y, $FormDesign->DeliveryAddressName->FontSize, $myrow['deliverto']);
	$pdf->addText($FormDesign->DeliveryAddress->Line1->x, $Page_Height -$FormDesign->DeliveryAddress->Line1->y, $FormDesign->DeliveryAddress->Line1->FontSize, $myrow['deladd1']);
	$pdf->addText($FormDesign->DeliveryAddress->Line2->x, $Page_Height -$FormDesign->DeliveryAddress->Line2->y, $FormDesign->DeliveryAddress->Line2->FontSize, $myrow['deladd2']);
	$pdf->addText($FormDesign->DeliveryAddress->Line3->x, $Page_Height -$FormDesign->DeliveryAddress->Line3->y, $FormDesign->DeliveryAddress->Line3->FontSize, $myrow['deladd3']);
	$pdf->addText($FormDesign->DeliveryAddress->Line4->x, $Page_Height -$FormDesign->DeliveryAddress->Line4->y, $FormDesign->DeliveryAddress->Line4->FontSize, $myrow['deladd4']);
	$pdf->addText($FormDesign->DeliveryAddress->Line5->x, $Page_Height -$FormDesign->DeliveryAddress->Line5->y, $FormDesign->DeliveryAddress->Line5->FontSize, $myrow['deladd5']);
}
if ($InvOrCredit=='Credit'){
/* then its a credit note */
	$pdf->addText($FormDesign->DeliveryAddressCaption->x, $Page_Height -$FormDesign->DeliveryAddressCaption->y, $FormDesign->DeliveryAddressCaption->FontSize, _('Charge Branch') . ':');
	$pdf->addText($FormDesign->DeliveryAddressName->x, $Page_Height -$FormDesign->DeliveryAddressName->y, $FormDesign->DeliveryAddressName->FontSize, $myrow['brname']);
	$pdf->addText($FormDesign->DeliveryAddress->Line1->x, $Page_Height -$FormDesign->DeliveryAddress->Line1->y, $FormDesign->DeliveryAddress->Line1->FontSize, $myrow['braddress1']);
	$pdf->addText($FormDesign->DeliveryAddress->Line2->x, $Page_Height -$FormDesign->DeliveryAddress->Line2->y, $FormDesign->DeliveryAddress->Line2->FontSize, $myrow['braddress2']);
	$pdf->addText($FormDesign->DeliveryAddress->Line3->x, $Page_Height -$FormDesign->DeliveryAddress->Line3->y, $FormDesign->DeliveryAddress->Line3->FontSize, $myrow['braddress3']);
	$pdf->addText($FormDesign->DeliveryAddress->Line4->x, $Page_Height -$FormDesign->DeliveryAddress->Line4->y, $FormDesign->DeliveryAddress->Line4->FontSize, $myrow['braddress4']);
	$pdf->addText($FormDesign->DeliveryAddress->Line5->x, $Page_Height -$FormDesign->DeliveryAddress->Line5->y, $FormDesign->DeliveryAddress->Line5->FontSize, $myrow['braddress5']);
	$pdf->addText($FormDesign->DeliveryAddress->Line6->x, $Page_Height -$FormDesign->DeliveryAddress->Line6->y, $FormDesign->DeliveryAddress->Line6->FontSize, $myrow['braddress6']);
}

/*draw a line under the company address and charge to address*/

$pdf->addText($FormDesign->Currency->x, $Page_Height -$FormDesign->Currency->y, $FormDesign->Currency->FontSize, _('All amounts stated in') . ' - ' . $myrow['currcode']);

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->RoundRectangle($FormDesign->MainOutputBox->x, 
											$Page_Height - $FormDesign->MainOutputBox->y,
											$FormDesign->MainOutputBox->width, 
											$FormDesign->MainOutputBox->height, 
											$FormDesign->MainOutputBox->radius);

/*Set up headings */
$pdf->addText($FormDesign->CustomerTaxReferenceCaption->x, $Page_Height -$FormDesign->CustomerTaxReferenceCaption->y, $FormDesign->CustomerTaxReferenceCaption->FontSize, _('Customer Tax Ref') . ':');
$pdf->addText($FormDesign->CustomerTaxReference->x, $Page_Height -$FormDesign->CustomerTaxReference->y, $FormDesign->CustomerTaxReference->FontSize, $myrow['taxref']);

/*Print a vertical line */
$pdf->line($FormDesign->OrderRefLine->startx, 
					$Page_Height -$FormDesign->OrderRefLine->starty,
					$FormDesign->OrderRefLine->endx, 
					$Page_Height -$FormDesign->OrderRefLine->endy);

$pdf->addText($FormDesign->CustomerOrderReferenceCaption->x, $Page_Height -$FormDesign->CustomerOrderReferenceCaption->y, $FormDesign->CustomerOrderReferenceCaption->FontSize, _('Customer Order Ref.') . ':');
$pdf->addText($FormDesign->CustomerOrderReference->x, $Page_Height -$FormDesign->CustomerOrderReference->y, $FormDesign->CustomerOrderReference->FontSize, $myrow['customerref']);

/*Print a vertical line */
$pdf->line($FormDesign->SalesmanLine->startx, 
					$Page_Height -$FormDesign->SalesmanLine->starty,
					$FormDesign->SalesmanLine->endx, 
					$Page_Height -$FormDesign->SalesmanLine->endy);

$pdf->addText($FormDesign->SalesmanReferenceCaption->x, $Page_Height -$FormDesign->SalesmanReferenceCaption->y, $FormDesign->SalesmanReferenceCaption->FontSize, _('Sales Person') . ':');
$pdf->addText($FormDesign->SalesmanReference->x, $Page_Height -$FormDesign->SalesmanReference->y, $FormDesign->SalesmanReference->FontSize, $myrow['salesmanname']);

/*draw a line */
$pdf->line($FormDesign->LineAboveColumns->startx, 
					$Page_Height -$FormDesign->LineAboveColumns->starty,
					$FormDesign->LineAboveColumns->endx, 
					$Page_Height -$FormDesign->LineAboveColumns->endy);

$pdf->addText($FormDesign->Headings->Column1->x, $Page_Height - $FormDesign->Headings->Column1->y, $FormDesign->Headings->Column1->FontSize, _('Item Code'));
$pdf->addText($FormDesign->Headings->Column2->x,$Page_Height - $FormDesign->Headings->Column2->y, $FormDesign->Headings->Column2->FontSize, _('Description'));
$pdf->addText($FormDesign->Headings->Column3->x,$Page_Height - $FormDesign->Headings->Column3->y, $FormDesign->Headings->Column3->FontSize, _('Unit Price'));
$pdf->addText($FormDesign->Headings->Column4->x,$Page_Height - $FormDesign->Headings->Column4->y, $FormDesign->Headings->Column4->FontSize, _('Quantity'));
$pdf->addText($FormDesign->Headings->Column5->x,$Page_Height - $FormDesign->Headings->Column5->y, $FormDesign->Headings->Column5->FontSize, _('UOM'));
$pdf->addText($FormDesign->Headings->Column6->x,$Page_Height - $FormDesign->Headings->Column6->y, $FormDesign->Headings->Column6->FontSize, _('Discount'));
$pdf->addText($FormDesign->Headings->Column7->x,$Page_Height - $FormDesign->Headings->Column7->y, $FormDesign->Headings->Column7->FontSize, _('Extended Price'));

/*draw a line */
$pdf->line($FormDesign->LineBelowColumns->startx, 
					$Page_Height -$FormDesign->LineBelowColumns->starty,
					$FormDesign->LineBelowColumns->endx, 
					$Page_Height -$FormDesign->LineBelowColumns->endy);

?>
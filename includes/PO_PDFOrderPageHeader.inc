<?php
/* $Id: PO_PDFOrderPageHeader.inc 6941 2014-10-26 23:18:08Z daintree $*/
/*
	R & OS PHP-PDF class code to set up a new page
	a new page is implicit on the establishment of a new pdf object so
	only for subsequent pages
*/
if ($PageNumber>1){
	$pdf->newPage();
}
$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin+$FormDesign->logo->x,$Page_Height- $FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);
$pdf->addText($FormDesign->OrderNumber->x,$Page_Height- $FormDesign->OrderNumber->y,$FormDesign->OrderNumber->FontSize, _('Purchase Order Number'). ' ' . $OrderNo);
if ($ViewingOnly!=0) {
	$pdf->addText($FormDesign->ViewingOnly->x,$Page_Height - $FormDesign->ViewingOnly->y,$FormDesign->ViewingOnly->FontSize, _('FOR VIEWING ONLY') . ', ' . _('DO NOT SEND TO SUPPLIER') );
	$pdf->addText($FormDesign->ViewingOnly->x,$Page_Height - $FormDesign->ViewingOnly->y-$line_height,$FormDesign->ViewingOnly->FontSize, _('SUPPLIERS') . ' - ' . _('THIS IS NOT AN ORDER'));
}
$pdf->addText($FormDesign->PageNumber->x,$Page_Height - $FormDesign->PageNumber->y, $FormDesign->PageNumber->FontSize, _('Page') . ': ' .$PageNumber);
/*Now print out the company Tax authority reference */
$pdf->addText($FormDesign->TaxAuthority->x,$Page_Height - $FormDesign->TaxAuthority->y, $FormDesign->TaxAuthority->FontSize, $_SESSION['TaxAuthorityReferenceName'] . ' ' . $_SESSION['CompanyRecord']['gstno']);
/*Now print out the company name and address */
$pdf->addText($FormDesign->CompanyName->x,$Page_Height - $FormDesign->CompanyName->y, $FormDesign->CompanyName->FontSize, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText($FormDesign->CompanyAddress->Line1->x,$Page_Height - $FormDesign->CompanyAddress->Line1->y, $FormDesign->CompanyAddress->Line1->FontSize,  $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($FormDesign->CompanyAddress->Line2->x,$Page_Height - $FormDesign->CompanyAddress->Line2->y, $FormDesign->CompanyAddress->Line2->FontSize,  $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($FormDesign->CompanyAddress->Line3->x,$Page_Height - $FormDesign->CompanyAddress->Line3->y, $FormDesign->CompanyAddress->Line3->FontSize,  $_SESSION['CompanyRecord']['regoffice3']);
$pdf->addText($FormDesign->CompanyAddress->Line4->x,$Page_Height - $FormDesign->CompanyAddress->Line4->y, $FormDesign->CompanyAddress->Line4->FontSize,  $_SESSION['CompanyRecord']['regoffice4']);
$pdf->addText($FormDesign->CompanyAddress->Line5->x,$Page_Height - $FormDesign->CompanyAddress->Line5->y, $FormDesign->CompanyAddress->Line5->FontSize,  $_SESSION['CompanyRecord']['regoffice5'] . ' ' . $_SESSION['CompanyRecord']['regoffice6']);	// Includes company postal code and country.
$pdf->addText($FormDesign->CompanyPhone->x,$Page_Height - $FormDesign->CompanyPhone->y, $FormDesign->CompanyPhone->FontSize, _('Tel'). ': ' . $_SESSION['CompanyRecord']['telephone']);
$pdf->addText($FormDesign->CompanyFax->x,$Page_Height - $FormDesign->CompanyFax->y, $FormDesign->CompanyFax->FontSize, _('Fax').': ' . $_SESSION['CompanyRecord']['fax']);
$pdf->addText($FormDesign->CompanyEmail->x,$Page_Height - $FormDesign->CompanyEmail->y, $FormDesign->CompanyEmail->FontSize, _('Email'). ': ' .$_SESSION['CompanyRecord']['email']);
/*Now the delivery details */
$pdf->addText($FormDesign->DeliveryAddress->Caption->x,$Page_Height - $FormDesign->DeliveryAddress->Caption->y, $FormDesign->DeliveryAddress->Caption->FontSize, _('Deliver To') . ':' );
$pdf->addText($FormDesign->DeliveryAddress->Line1->x,$Page_Height - $FormDesign->DeliveryAddress->Line1->y, $FormDesign->DeliveryAddress->Line1->FontSize, $POHeader['deladd1']);
$pdf->addText($FormDesign->DeliveryAddress->Line2->x,$Page_Height - $FormDesign->DeliveryAddress->Line2->y, $FormDesign->DeliveryAddress->Line2->FontSize, $POHeader['deladd2']);
$pdf->addText($FormDesign->DeliveryAddress->Line3->x,$Page_Height - $FormDesign->DeliveryAddress->Line3->y, $FormDesign->DeliveryAddress->Line3->FontSize, $POHeader['deladd3']);
$pdf->addText($FormDesign->DeliveryAddress->Line4->x,$Page_Height - $FormDesign->DeliveryAddress->Line4->y, $FormDesign->DeliveryAddress->Line4->FontSize, $POHeader['deladd4']);
$pdf->addText($FormDesign->DeliveryAddress->Line5->x,$Page_Height - $FormDesign->DeliveryAddress->Line5->y, $FormDesign->DeliveryAddress->Line5->FontSize, $POHeader['deladd5'] . ' ' . $POHeader['deladd6']);// Includes delivery postal code and country.
/*draw a nice curved corner box around the delivery to address */
$pdf->RoundRectangle($FormDesign->DeliveryAddressBox->x, $Page_Height - $FormDesign->DeliveryAddressBox->y,$FormDesign->DeliveryAddressBox->width, $FormDesign->DeliveryAddressBox->height, $FormDesign->DeliveryAddressBox->radius, $FormDesign->DeliveryAddressBox->radius);// Function RoundRectangle from includes/class.pdf.php
/*Now the Supplier details */
$pdf->addText($FormDesign->SupplierName->x,$Page_Height - $FormDesign->SupplierName->y, $FormDesign->SupplierName->FontSize, _('To').': ');
$pdf->addText($FormDesign->SupplierName->x+30,$Page_Height - $FormDesign->SupplierName->y, $FormDesign->SupplierName->FontSize, $POHeader['suppname']);
$pdf->addText($FormDesign->SupplierAddress->Line1->x,$Page_Height - $FormDesign->SupplierAddress->Line1->y, $FormDesign->SupplierAddress->Line1->FontSize, $POHeader['address1']);
$pdf->addText($FormDesign->SupplierAddress->Line2->x,$Page_Height - $FormDesign->SupplierAddress->Line2->y, $FormDesign->SupplierAddress->Line2->FontSize, $POHeader['address2']);
$pdf->addText($FormDesign->SupplierAddress->Line3->x,$Page_Height - $FormDesign->SupplierAddress->Line3->y, $FormDesign->SupplierAddress->Line3->FontSize, $POHeader['address3']);
$pdf->addText($FormDesign->SupplierAddress->Line4->x,$Page_Height - $FormDesign->SupplierAddress->Line4->y, $FormDesign->SupplierAddress->Line4->FontSize, $POHeader['address4']);
$pdf->addText($FormDesign->SupplierAddress->Line5->x,$Page_Height - $FormDesign->SupplierAddress->Line5->y, $FormDesign->SupplierAddress->Line5->FontSize, $POHeader['address5'] . ' ' . $POHeader['address6']);	// Includes supplier postal code and country.
/*Now the Requisition Number */
$pdf->addText($FormDesign->RequisitionNumber->x,$Page_Height - $FormDesign->RequisitionNumber->y, $FormDesign->RequisitionNumber->FontSize, _('Requisition Number') . ':' );
$pdf->addText($FormDesign->RequisitionNumber->x+120,$Page_Height - $FormDesign->RequisitionNumber->y, $FormDesign->RequisitionNumber->FontSize, $POHeader['requisitionno']);
/*Now the Order date */
$pdf->addText($FormDesign->OrderDate->x,$Page_Height - $FormDesign->OrderDate->y, $FormDesign->OrderDate->FontSize, _('Order Date') . ': ' . ConvertSQLDate($POHeader['orddate']) );
/*Now the Initiator */
$pdf->addText($FormDesign->Initiator->x,$Page_Height - $FormDesign->Initiator->y, $FormDesign->Initiator->FontSize, _('Initiator').': ' . $POHeader['initiator']);
/*Find the description of the payment terms and display.
 * If it is a preview then just insert dummy data */
if ($OrderNo != 'Preview') {
	$sql="SELECT terms FROM paymentterms where termsindicator='".$POHeader['paymentterms']."'";
	$termsresult=DB_query($sql);
	$myrow=DB_fetch_array($termsresult);
	$pdf->addText($FormDesign->PaymentTerms->x,$Page_Height - $FormDesign->PaymentTerms->y, $FormDesign->PaymentTerms->FontSize, _('Payment Terms').': '.$myrow['terms']);
} else {
	$pdf->addText($FormDesign->PaymentTerms->x,$Page_Height - $FormDesign->PaymentTerms->y, $FormDesign->PaymentTerms->FontSize, _('Payment Terms').': '.'XXXXXXXX');
}
/*Now the Comments split over two lines if necessary */
$LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x, $Page_Height - $FormDesign->Comments->y,$FormDesign->Comments->Length,$FormDesign->Comments->FontSize,_('Comments') . ':' .$POHeader['comments'], 'left');
if (mb_strlen($LeftOvers)>0){
	$LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x, $Page_Height - $FormDesign->Comments->y-$line_height,$FormDesign->Comments->Length,$FormDesign->Comments->FontSize,$LeftOvers, 'left');
}
/*Now the currency the order is in */
include($PathPrefix . 'includes/CurrenciesArray.php'); // To get the currency name from the currency code.
$pdf->addText($FormDesign->Currency->x,$Page_Height - $FormDesign->Currency->y,$FormDesign->Currency->FontSize, _('All amounts stated in').' - ' . $POHeader['currcode'] . ' ' . $CurrencyName[$POHeader['currcode']]);
/*draw a square grid for entering line headings */
$pdf->Rectangle($FormDesign->HeaderRectangle->x, $Page_Height - $FormDesign->HeaderRectangle->y, $FormDesign->HeaderRectangle->width,$FormDesign->HeaderRectangle->height);
/*Set up headings */
$pdf->addText($FormDesign->Headings->Column1->x,$Page_Height - $FormDesign->Headings->Column1->y, $FormDesign->Headings->Column1->FontSize, _('Code') );
$pdf->addText($FormDesign->Headings->Column2->x,$Page_Height - $FormDesign->Headings->Column2->y, $FormDesign->Headings->Column2->FontSize, _('Item Description') );
$pdf->addText($FormDesign->Headings->Column3->x,$Page_Height - $FormDesign->Headings->Column3->y, $FormDesign->Headings->Column3->FontSize, _('Quantity') );
$pdf->addText($FormDesign->Headings->Column4->x,$Page_Height - $FormDesign->Headings->Column4->y, $FormDesign->Headings->Column4->FontSize, _('Unit') );
$pdf->addText($FormDesign->Headings->Column5->x,$Page_Height - $FormDesign->Headings->Column5->y, $FormDesign->Headings->Column5->FontSize, _('Date Reqd'));
$pdf->addText($FormDesign->Headings->Column6->x,$Page_Height - $FormDesign->Headings->Column6->y, $FormDesign->Headings->Column6->FontSize, _('Price') );
$pdf->addText($FormDesign->Headings->Column7->x,$Page_Height - $FormDesign->Headings->Column7->y, $FormDesign->Headings->Column7->FontSize, _('Total') );
/*draw a rectangle to hold the data lines */
$pdf->Rectangle($FormDesign->DataRectangle->x, $Page_Height - $FormDesign->DataRectangle->y, $FormDesign->DataRectangle->width,$FormDesign->DataRectangle->height);
?>
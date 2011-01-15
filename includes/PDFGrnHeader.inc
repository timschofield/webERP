<?php
/* $Id$*/
/*PDF page header for price list report */
if ($PageNumber>1){
	$pdf->newPage();
}

$sql="SELECT suppliers.supplierid,
				suppname,
				address1,
				address2 ,
				address3,
				address4,
				address5,
				address6
			FROM suppliers
			LEFT JOIN grns
			ON grns.supplierid=suppliers.supplierid
			WHERE grnbatch='".$GRNNo."'";
$supplierresult=DB_query($sql, $db);
$supplierrow=DB_fetch_array($supplierresult);

$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$FormDesign->logo->x,$Page_Height-$FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);

$LeftOvers = $pdf->addText($FormDesign->SupplierName->x,$Page_Height-$FormDesign->SupplierName->y,$FormDesign->SupplierName->FontSize,$supplierrow['suppname']);
$LeftOvers = $pdf->addText($FormDesign->SupplierAddress1->x,$Page_Height-$FormDesign->SupplierAddress1->y,$FormDesign->SupplierAddress1->FontSize,$supplierrow['address1']);
$LeftOvers = $pdf->addText($FormDesign->SupplierAddress2->x,$Page_Height-$FormDesign->SupplierAddress2->y,$FormDesign->SupplierAddress2->FontSize,$supplierrow['address2']);
$LeftOvers = $pdf->addText($FormDesign->SupplierAddress3->x,$Page_Height-$FormDesign->SupplierAddress3->y,$FormDesign->SupplierAddress3->FontSize,$supplierrow['address3']);
$LeftOvers = $pdf->addText($FormDesign->SupplierAddress4->x,$Page_Height-$FormDesign->SupplierAddress4->y,$FormDesign->SupplierAddress4->FontSize,$supplierrow['address4']);
$LeftOvers = $pdf->addText($FormDesign->SupplierAddress5->x,$Page_Height-$FormDesign->SupplierAddress5->y,$FormDesign->SupplierAddress5->FontSize,$supplierrow['address5']);
$LeftOvers = $pdf->addText($FormDesign->SupplierAddress6->x,$Page_Height-$FormDesign->SupplierAddress6->y,$FormDesign->SupplierAddress6->FontSize,$supplierrow['address6']);


$LeftOvers = $pdf->addText($FormDesign->CompanyName->x,$Page_Height-$FormDesign->CompanyName->y,$FormDesign->CompanyName->FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addText($FormDesign->GRNNumber->x,$Page_Height-$FormDesign->GRNNumber->y,$FormDesign->GRNNumber->FontSize, _('GRN number ').' ' . $GRNNo );
$LeftOvers = $pdf->addText($FormDesign->OrderNumber->x,$Page_Height-$FormDesign->OrderNumber->y,$FormDesign->OrderNumber->FontSize, _('PO number ').' ' . $_GET['PONo'] );
$LeftOvers = $pdf->addText($FormDesign->PrintDate->x,$Page_Height-$FormDesign->PrintDate->y,$FormDesign->PrintDate->FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. _('Page'). ' ' . $PageNumber);

/*Draw a rectangle to put the headings in     */
$pdf->Rectangle($FormDesign->HeaderRectangle->x, $Page_Height - $FormDesign->HeaderRectangle->y, $FormDesign->HeaderRectangle->width,$FormDesign->HeaderRectangle->height);

/*set up the headings */
$LeftOvers = $pdf->addText($FormDesign->Headings->Column1->x,$Page_Height - $FormDesign->Headings->Column1->y, $FormDesign->Headings->Column1->FontSize,  _('Item Number'));
$LeftOvers = $pdf->addText($FormDesign->Headings->Column2->x,$Page_Height - $FormDesign->Headings->Column2->y, $FormDesign->Headings->Column2->FontSize,  _('Description'));
$LeftOvers = $pdf->addText($FormDesign->Headings->Column3->x,$Page_Height - $FormDesign->Headings->Column3->y, $FormDesign->Headings->Column3->FontSize,  _('Date Recd'));
$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column4->x,$Page_Height - $FormDesign->Headings->Column4->y, $FormDesign->Headings->Column4->Length, $FormDesign->Headings->Column4->FontSize,  _('Qty in Suppliers UOM'), 'right');
$LeftOvers = $pdf->addTextWrap($FormDesign->Headings->Column5->x,$Page_Height - $FormDesign->Headings->Column5->y, $FormDesign->Headings->Column5->Length, $FormDesign->Headings->Column5->FontSize,  _('Qty in Stock UOM'), 'right');

/*Draw a rectangle to put the data in     */
$pdf->Rectangle($FormDesign->DataRectangle->x, $Page_Height - $FormDesign->DataRectangle->y, $FormDesign->DataRectangle->width,$FormDesign->DataRectangle->height);

$pdf->Line($FormDesign->LineAboveFooter->startx, $Page_Height - $FormDesign->LineAboveFooter->starty, $FormDesign->LineAboveFooter->endx,$Page_Height - $FormDesign->LineAboveFooter->endy);

$pdf->Line($FormDesign->Column1->startx, $Page_Height - $FormDesign->Column1->starty, $FormDesign->Column1->endx,$Page_Height - $FormDesign->Column1->endy);
$pdf->Line($FormDesign->Column3->startx, $Page_Height - $FormDesign->Column3->starty, $FormDesign->Column3->endx,$Page_Height - $FormDesign->Column3->endy);
$pdf->Line($FormDesign->Column4->startx, $Page_Height - $FormDesign->Column4->starty, $FormDesign->Column4->endx,$Page_Height - $FormDesign->Column4->endy);
$pdf->Line($FormDesign->Column5->startx, $Page_Height - $FormDesign->Column5->starty, $FormDesign->Column5->endx,$Page_Height - $FormDesign->Column5->endy);

$PageNumber++;
?>
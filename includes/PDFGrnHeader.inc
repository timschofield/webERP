<?php
/* $Id$*/
/*PDF page header for price list report */
if ($PageNumber>1){
	$pdf->newPage();
}

$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$FormDesign->logo->x,$Page_Height-$FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);

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
$LeftOvers = $pdf->addText($FormDesign->Headings->Column4->x,$Page_Height - $FormDesign->Headings->Column4->y, $FormDesign->Headings->Column4->FontSize,  _('Supplier'));
$LeftOvers = $pdf->addText($FormDesign->Headings->Column5->x,$Page_Height - $FormDesign->Headings->Column5->y, $FormDesign->Headings->Column5->FontSize,  _('Quantity'));

/*Draw a rectangle to put the data in     */
$pdf->Rectangle($FormDesign->DataRectangle->x, $Page_Height - $FormDesign->DataRectangle->y, $FormDesign->DataRectangle->width,$FormDesign->DataRectangle->height);

$PageNumber++;
?>
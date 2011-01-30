<?php

/* $Id$*/

//$PageSecurity = 2; Now comes from DB - read in from session
include('includes/session.inc');
include('includes/DefinePOClass.php');

if (isset($_GET['GRNNo'])) {
	$GRNNo=$_GET['GRNNo'];
} else {
	$GRNNo='';
}

if ($GRNNo=='Preview') {
	$FormDesign = simplexml_load_file(sys_get_temp_dir().'/GoodsReceived.xml');
} else {
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/GoodsReceived.xml');
}

// Set the paper size/orintation
$PaperSize = $FormDesign->PaperSize;
$PageNumber=1;
$line_height=$FormDesign->LineHeight;
include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Goods Received Note') );

if ($GRNNo=='Preview') {
	$ListCount = 1; 
} else {
	$sql="SELECT grns.itemcode,
			grns.grnno,
			grns.deliverydate,
			grns.itemdescription,
			grns.qtyrecd,
			grns.supplierid,
			grns.podetailitem
		FROM grns
		WHERE grnbatch='".$GRNNo."'";
	$GRNResult=DB_query($sql, $db);
	$NoOfGRNs = DB_num_rows($GRNResult);
	if ($NoOfGRNs>0){
		$_GET['ModifyOrderNumber'] = (int)$_GET['PONo'];
		$identifier=date('U');
		include('includes/PO_ReadInOrder.inc'); //Read the PO in
		include('includes/PDFGrnHeader.inc');
	}
}
$i=1;
$YPos=$FormDesign->Data->y;
while ($i<=$NoOfGRNs) {
	if ($GRNNo=='Preview') {
		$StockID=str_pad('',10,'x');
		$Date='1/1/1900';
		$Description=str_pad('',30,'x');
		$SuppliersQuantity='XXXXX.XX';
		$OurUnitsQuantity='XXXXX.XX';
		$Supplier=str_pad('',25,'x');
		$Units = str_pad('',10,'x');
		$SupplierUnits =str_pad('',10,'x');
	} else {
		$myrow = DB_fetch_array($GRNResult);
		$LineNo = $_SESSION['PO'.$identifier]->GetLineNoFromPODetailItem($myrow['podetailitem']);
		echo '<br />The podetailitem is ' . $myrow['podetailitem'] . '<br />Got the line number it is: ' . $LineNo;
		$StockID=$myrow['itemcode'];
		$GRNNo=$myrow['grnno'];
		$Date=ConvertSQLDate($myrow['deliverydate']);
		$Description=$myrow['itemdescription'];
		$SuppliersQuantity=number_format($myrow['qtyrecd']/$_SESSION['PO' . $identifier]->LineItems[$LineNo]->ConversionFactor,$_SESSION['PO' . $identifier]->LineItems[$LineNo]->DecimalPlaces);
		$OurUnitsQuantity=number_format($myrow['qtyrecd'],$_SESSION['PO' . $identifier]->LineItems[$LineNo]->DecimalPlaces);
		$SupplierID=$myrow['supplierid'];
		$Units = $_SESSION['PO' . $identifier]->LineItems[$LineNo]->Units;
		$SuppliersUnit = $_SESSION['PO' . $identifier]->LineItems[$LineNo]->SuppliersUnit;
		$Supplier = $_SESSION['PO' . $identifier]->SupplierName;
	}
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column1->x,$Page_Height-$YPos,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize, $StockID);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x,$Page_Height-$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, $Description);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column3->x,$Page_Height-$YPos,$FormDesign->Data->Column3->Length,$FormDesign->Data->Column3->FontSize, $Date);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column4->x,$Page_Height-$YPos,$FormDesign->Data->Column4->Length,$FormDesign->Data->Column4->FontSize, $SuppliersQuantity, 'right');
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x,$Page_Height-$YPos,$FormDesign->Data->Column5->Length,$FormDesign->Data->Column5->FontSize, $SuppliersUnit, 'left');
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column6->x,$Page_Height-$YPos,$FormDesign->Data->Column6->Length,$FormDesign->Data->Column6->FontSize, $OurUnitsQuantity, 'right');
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column7->x,$Page_Height-$YPos,$FormDesign->Data->Column7->Length,$FormDesign->Data->Column7->FontSize, $Units, 'left');
	$YPos += $line_height;
	$i++;
	if ($YPos >= $FormDesign->LineAboveFooter->starty){
		/* We reached the end of the page so finsih off the page and start a newy */
		$PageNumber++;
		$YPos=$FormDesign->Data->y;
		include ('includes/PDFGrnHeader.inc');
	} //end if need a new page headed up
}

$LeftOvers = $pdf->addText($FormDesign->ReceiptDate->x,$Page_Height-$FormDesign->ReceiptDate->y,$FormDesign->ReceiptDate->FontSize, _('Date of Receipt: ').$Date);

$LeftOvers = $pdf->addText($FormDesign->SignedFor->x,$Page_Height-$FormDesign->SignedFor->y,$FormDesign->SignedFor->FontSize, _('Signed for ').'______________________');

if ($NoOfGRNs == 0) {  
	$title = _('GRN Error');
	include('includes/header.inc');
	prnMsg(_('There were no GRNs to print'),'warn');
	echo '<br><a href="'.$rootpath.'/index.php?' . SID . '">'. _('Back to the menu').'</a>';
	include('includes/footer.inc');
	exit;
} else {
    $pdf->OutputD($_SESSION['DatabaseName'] . '_GRN_' . date('Y-m-d').'.pdf');//UldisN
    $pdf->__destruct(); //UldisN
}
?>
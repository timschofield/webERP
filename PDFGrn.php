<?php

/* $Id$*/

/* $Revision: 1.5 $ */

$PageSecurity = 2;
include('includes/session.inc');

$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/GoodsReceived.xml');

// Set the paper size/orintation
$PaperSize = $FormDesign->PaperSize;
$PageNumber=1;
$line_height=$FormDesign->LineHeight;

include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Goods Received Note') );

if (isset($_POST['GRNNo'])) {
	$GRNNo=$_POST['GRNNo'];
} else if (isset($_GET['GRNNo'])) {
	$GRNNo=$_GET['GRNNo'];
} else {
	$GRNNo='';
}

include('includes/PDFGrnHeader.inc');

if ($GRNNo=='Preview') {
	$ListCount = 1; // UldisN
} else {
	$sql='SELECT itemcode, grnno, deliverydate, itemdescription, qtyrecd, supplierid from grns where grnbatch='.$GRNNo;
	$result=DB_query($sql, $db);
	$ListCount = DB_num_rows($result); // UldisN
}
$counter=1;
$YPos=$FormDesign->Data->y;
while ($counter<=$ListCount) {
	if ($GRNNo=='Preview') {
		$StockID=str_pad('',10,'x');
		$Date='1/1/1900';
		$Description=str_pad('',30,'x');
		$Quantity='XXXXX.XX';
		$Supplier=str_pad('',25,'x');
	} else {
		$myrow=DB_fetch_array($result);
		$StockID=$myrow[0];
		$GRNNo=$myrow[1];
		$Date=ConvertSQLDate($myrow[2]);
		$Description=$myrow[3];
		$Quantity=$myrow[4];
		$SupplierID=$myrow[5];

		$sql='select suppname from suppliers where supplierid="'.$SupplierID.'"';
		$supplierresult=DB_query($sql, $db);
		$suppliermyrow=DB_fetch_array($supplierresult);
		$Supplier=$suppliermyrow[0];
	}

	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column1->x,$Page_Height-$YPos,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize, $StockID);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x,$Page_Height-$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, $Description);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column3->x,$Page_Height-$YPos,$FormDesign->Data->Column3->Length,$FormDesign->Data->Column3->FontSize, $Date);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column4->x,$Page_Height-$YPos,$FormDesign->Data->Column4->Length,$FormDesign->Data->Column4->FontSize, $Supplier);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x,$Page_Height-$YPos,$FormDesign->Data->Column5->Length,$FormDesign->Data->Column5->FontSize, $Quantity);
	$YPos += $line_height;
	$counter++;
}

$LeftOvers = $pdf->addText($FormDesign->ReceiptDate->x,$Page_Height-$FormDesign->ReceiptDate->y,$FormDesign->ReceiptDate->FontSize, _('Date of Receipt: ').$Date);

$LeftOvers = $pdf->addText($FormDesign->SignedFor->x,$Page_Height-$FormDesign->SignedFor->y,$FormDesign->SignedFor->FontSize, _('Signed for ').'______________________');

if ($ListCount == 0) {   //UldisN
	$title = _('GRN Error');
	include('includes/header.inc');
	prnMsg(_('There were no GRN to print'),'warn');
	echo '<br><a href="'.$rootpath.'/index.php?' . SID . '">'. _('Back to the menu').'</a>';
	include('includes/footer.inc');
	exit;
} else {
    $pdf->OutputD($_SESSION['DatabaseName'] . '_GRN_' . date('Y-m-d').'.pdf');//UldisN
    $pdf->__destruct(); //UldisN
}
?>
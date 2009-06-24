<?php
/* $Revision: 1.4 $ */

$PageSecurity = 2;
include('includes/session.inc');


include('includes/PDFStarter.php');

$FontSize=10;
$pdf->addinfo('Title', _('Goods Received Note') );

$PageNumber=1;
$line_height=12;


include('includes/PDFGrnHeader.inc');

$FontSize =10;
/*Print out the category totals */

$sql='SELECT itemcode, grnno, deliverydate, itemdescription, qtyrecd, supplierid from grns where grnbatch='.
	$_GET['GRNNo'];
$result=DB_query($sql, $db);
$counter=1;
while ($myrow=DB_fetch_array($result)) {
	$StockID=$myrow[0];
	$GRNNo=$myrow[1];
	$Date=$myrow[2];
	$Description=$myrow[3];
	$Quantity=$myrow[4];
	$SupplierID=$myrow[5];

	$sql='select suppname from suppliers where supplierid="'.$SupplierID.'"';
	$supplierresult=DB_query($sql, $db);
	$suppliermyrow=DB_fetch_array($supplierresult);
	$Supplier=$suppliermyrow[0];

	$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos-(10*$counter),70,$FontSize, $StockID);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+75,$YPos-(10*$counter),175,$FontSize, $Description);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos-(10*$counter),300-$Left_Margin,$FontSize, $Date);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+315,$YPos-(10*$counter),150,$FontSize, $Supplier);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+475,$YPos-(10*$counter),300-$Left_Margin,$FontSize, $Quantity);
	$counter = $counter + 1;
}

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-(10*$counter+80),300-$Left_Margin,$FontSize, _('Date of Receipt: ').$Date);

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-(10*$counter+130),300-$Left_Margin,$FontSize, _('Signed for ').'______________________');

$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ($len<=20){
	$title = _('Print Price List Error');
	include('includes/header.inc');
	prnMsg(_('There were no stock transfer details to print'),'warn');
	echo '<br><a href="'.$rootpath.'/index.php?' . SID . '">'. _('Back to the menu').'</a>';
	include('includes/footer.inc');
	exit;
} else {
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=GRN.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->Stream();
}


 /*end of else not PrintPDF */
?>

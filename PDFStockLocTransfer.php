<?php
/* $Revision: 1.10 $ */

$PageSecurity =1;
include('includes/session.inc');
include('includes/PDFStarter.php');

$title = _('Stock Location Transfer Docket Error');

if (!isset($_GET['TransferNo'])){

	include ('includes/header.inc');
	echo '<p>';
	prnMsg( _('This page must be called with a location transfer reference number'),'error' );
	include ('includes/footer.inc');
	exit;
}

$FontSize=10;
$pdf->addinfo('Title', _('Inventory Location Transfer BOL') );
$pdf->addinfo('Subject', _('Inventory Location Transfer BOL') . ' # ' . $_GET['TransferNo']);

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>'. _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT loctransfers.reference,
			   loctransfers.stockid,
			   stockmaster.description,
			   loctransfers.shipqty,
			   loctransfers.shipdate,
			   loctransfers.shiploc,
			   locations.locationname as shiplocname,
			   loctransfers.recloc,
			   locationsrec.locationname as reclocname
			   FROM loctransfers
			   INNER JOIN stockmaster ON loctransfers.stockid=stockmaster.stockid
			   INNER JOIN locations ON loctransfers.shiploc=locations.loccode
			   INNER JOIN locations AS locationsrec ON loctransfers.recloc = locationsrec.loccode
			   WHERE loctransfers.reference=" . $_GET['TransferNo'];

$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

If (DB_num_rows($result)==0){

	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);

$PageNumber=1;
include ('includes/PDFStockLocTransferHeader.inc');
$line_height=30;
$FontSize=10;

do {

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,100,$FontSize,$TransferRow['stockid'], 'left');
	$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize,$TransferRow['description'], 'left');
	$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,$TransferRow['shipqty'], 'right');

	$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

	$YPos -= $line_height;

	if ($YPos < $Bottom_Margin + $line_height) {
		$PageNumber++;
		include('includes/PDFStockLocTransferHeader.inc');
	}

} while ($TransferRow = DB_fetch_array($result));

$pdfcode = $pdf->output();
$len = strlen($pdfcode);


if ($len<=20){
	include('includes/header.inc');
	echo '<p>';
	prnMsg( _('There was no stock location transfer to print out'), 'warn');
	echo '<br><a href="' . $rootpath. '/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
	include('includes/footer.inc');
	exit;
} else {
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=StockLocTrfShipment.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->Stream();
}
?>

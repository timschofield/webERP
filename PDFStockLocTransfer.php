<?php
/* $Revision: 1.3 $ */

$PageSecurity =1;

include('config.php');
$title = _('Stock Location Transfer Docket Error');
include('includes/ConnectDB.inc');
session_start();
include('includes/PDFStarter_ros.inc');
include('includes/DateFunctions.inc');

if (!isset($_GET['TransferNo'])){

	include ('includes/header.inc');
	echo '<P>';
	prnMsg( _('This page must be called with a location transfer reference number'),'error' );
	include ('includes/footer.inc');
	exit;
}

$FontSize=10;
$pdf->addinfo('Title', _('Inventory Location Transfer BOL') );
$pdf->addinfo('Subject', _('Inventory Location Transfer BOL') . ' # ' . $_GET['Trf_ID']);

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<P>'. _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT LocTransfers.Reference,
			   LocTransfers.StockID,
			   StockMaster.Description,
			   LocTransfers.ShipQty,
			   LocTransfers.ShipDate,
			   LocTransfers.ShipLoc,
			   Locations.LocationName AS ShipLocName,
			   LocTransfers.RecLoc,
			   LocationsRec.LocationName AS RecLocName
			   FROM LocTransfers
			   INNER JOIN StockMaster ON LocTransfers.StockID=StockMaster.StockID
			   INNER JOIN Locations ON LocTransfers.ShipLoc=Locations.LocCode
			   INNER JOIN Locations AS LocationsRec ON LocTransfers.RecLoc = LocationsRec.LocCode
			   WHERE LocTransfers.Reference=" . $_GET['TransferNo'];

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

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,100,$FontSize,$TransferRow['StockID'], 'left');
	$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize,$TransferRow['Description'], 'left');
	$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,$TransferRow['ShipQty'], 'right');

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
	echo '<BR><A HREF="' . $rootpath. '/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
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

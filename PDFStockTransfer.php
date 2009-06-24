<?php
/* $Revision: 1.2 $ */

$PageSecurity = 2;
include('includes/session.inc');


include('includes/PDFStarter.php');

$FontSize=10;
$pdf->addinfo('Title', _('Stock Transfer Form') );

$PageNumber=1;
$line_height=12;


include('includes/PDFStockTransferHeader.inc');

$FontSize =10;
/*Print out the category totals */

$sql='SELECT stockid, transno, loccode, trandate, qty from stockmoves where transno='.$_GET['TransferNo'].' and type=16';
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$StockID=$myrow[0];
$FromCode=$myrow[2];
$Date=$myrow[3];

$myrow=DB_fetch_array($result);
$ToCode=$myrow[2];
$Quantity=$myrow[4];

$sql='select description from stockmaster where stockid="'.$StockID.'"';
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$Description=$myrow[0];

$sql='select locationname from locations where loccode="'.$FromCode.'"';
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$From=$myrow[0];

$sql='select locationname from locations where loccode="'.$ToCode.'"';
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$To=$myrow[0];

$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos-10,300-$Left_Margin,$FontSize, $StockID);
$LeftOvers = $pdf->addTextWrap($Left_Margin+75,$YPos-10,300-$Left_Margin,$FontSize, $Description);
$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos-10,300-$Left_Margin,$FontSize, $From);
$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos-10,300-$Left_Margin,$FontSize, $To);
$LeftOvers = $pdf->addTextWrap($Left_Margin+475,$YPos-10,300-$Left_Margin,$FontSize, $Quantity);

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-70,300-$Left_Margin,$FontSize, _('Date of transfer: ').$Date);

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-120,300-$Left_Margin,$FontSize, _('Signed for ').$From.'______________________');
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-160,300-$Left_Margin,$FontSize, _('Signed for ').$To.'______________________');

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
	header('Content-Disposition: inline; filename=StockTransfer.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->Stream();
}


 /*end of else not PrintPDF */
?>
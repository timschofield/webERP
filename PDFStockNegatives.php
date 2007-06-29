<?php
/* $Revision: 1.5 $ */

$PageSecurity =1;
include('includes/session.inc');
include('includes/PDFStarter.php');
$title = _('Negative Stock Listing Error');

$FontSize=10;
$pdf->addinfo('Title', _('Inventory Negatives Listing') );
$pdf->addinfo('Subject', _('Inventory Negatives Listing'));

$ErrMsg = _('An error occurred retrieving the negative quantities.');

$sql = "SELECT stockmaster.stockid,
               stockmaster.description,
               stockmaster.categoryid,
               locstock.loccode,
               locations.locationname,
               locstock.quantity
        FROM stockmaster INNER JOIN locstock ON stockmaster.stockid=locstock.stockid
        INNER JOIN locations ON locstock.loccode = locations.loccode
        WHERE locstock.quantity < 0
        ORDER BY locstock.loccode, stockmaster.categoryid, stockmaster.stockid";

$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

If (DB_num_rows($result)==0){
	include ('includes/header.inc');
	prnMsg(_('There are no negative stocks to list'),'error');
	include ('includes/footer.inc');
	exit;
}

$NegativesRow = DB_fetch_array($result);

$PageNumber=1;
include ('includes/PDFStockNegativesHeader.inc');
$line_height=15;
$FontSize=10;

do {

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,100,$FontSize, $NegativesRow['loccode'] . ' - ' . $NegativesRow['locationname'], 'left');
	$LeftOvers = $pdf->addTextWrap(130,$YPos,250,$FontSize,$NegativesRow['stockid'] . ' - ' .$NegativesRow['description'], 'left');
	$LeftOvers = $pdf->addTextWrap(400,$YPos,70,$FontSize,$NegativesRow['quantity'], 'right');

	$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

	$YPos -= $line_height;

	if ($YPos < $Bottom_Margin + $line_height) {
		$PageNumber++;
		include('includes/PDFStockNegativesHeader.inc');
	}

} while ($NegativesRow = DB_fetch_array($result));

$pdfcode = $pdf->output();
$len = strlen($pdfcode);


if ($len<=10){
	include('includes/header.inc');
	echo '<p>';
	prnMsg( _('There was no negative stocks to print out'), 'warn');
	echo '<BR><A HREF="' . $rootpath. '/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
	include('includes/footer.inc');
	exit;
} else {
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=NegativeStocks.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->Stream();
}
?>

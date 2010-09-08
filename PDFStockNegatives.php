<?php
/* $Revision: 1.7 $ */

/* $Id$*/

/* $Revision: 1.8 $ */

$PageSecurity =1;
include('includes/session.inc');

include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Inventory Negatives Listing') );
$pdf->addInfo('Subject', _('Inventory Negatives Listing'));
$FontSize=10;
$PageNumber=1;
$line_height=15;

$title = _('Negative Stock Listing Error');
$ErrMsg = _('An error occurred retrieving the negative quantities.');
$DbgMsg = _('The sql that failed to retrieve the negative quantities was');

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

$pdf->OutputD($_SESSION['DatabaseName'] . '_NegativeStocks_' . date('Y-m-d') . '.pdf');//UldisN
$pdf->__destruct(); //UldisN
?>

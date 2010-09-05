<?php

/* $Id$*/

/* $Revision: 1.13 $ */

$PageSecurity = 3;
include('includes/SQL_CommonFunctions.inc');
include ('includes/session.inc');

$InputError=0;
if (isset($_POST['Date']) AND !Is_Date($_POST['Date'])){
	$msg = _('The date must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['Date']);
}

if (!isset($_POST['Date'])){

	 $title = _('Stock Transaction Listing');
	 include ('includes/header.inc');

	echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title . '" alt="">' . ' '
		. _('Stock Transaction Listing').'</img></p></div>';

	if ($InputError==1){
		prnMsg($msg,'error');
	}

	 echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	 echo '<table class=selection>
	 			<tr>
				<td>' . _('Enter the date for which the transactions are to be listed') . ":</td>
				<td><input type=text name='Date' maxlength=10 size=10 class=date alt='" . $_SESSION['DefaultDateFormat'] . "' value='" . Date($_SESSION['DefaultDateFormat']) . "'></td>
			</tr>";

	echo '<tr><td>' . _('Transaction type') . '</td><td>';

	echo "<select name='TransType'>";

	echo '<option value=10>' . _('Sales Invoice').'</option>';
	echo '<option value=11>' . _('Sales Credit Note').'</option>';
	echo '<option value=16>' . _('Location Transfer').'</option>';
	echo '<option value=17>' . _('Stock Adjustment').'</option>';
	echo '<option value=25>' . _('Purchase Order Delivery').'</option>';
	echo '<option value=26>' . _('Work Order Receipt').'</option>';
	echo '<option value=28>' . _('Work Order Issue').'</option>';

	 echo '</select></td></tr>';

	 echo "</select></td></tr></table><br><div class='centre'><input type=submit name='Go' value='" . _('Create PDF') . "'></div>";


	 include('includes/footer.inc');
	 exit;
} else {

	include('includes/ConnectDB.inc');
}

$sql= "SELECT stockmoves.type,
		stockmoves.stockid,
		stockmaster.description,
		stockmaster.decimalplaces,
		stockmoves.transno,
		stockmoves.trandate,
		stockmoves.qty,
		stockmoves.reference,
		stockmoves.narrative,
		locations.locationname
	FROM stockmoves
	LEFT JOIN stockmaster
	ON stockmoves.stockid=stockmaster.stockid
	LEFT JOIN locations
	ON stockmoves.loccode=locations.loccode
	WHERE type='" . $_POST['TransType'] . "'
	AND date_format(trandate, '%Y-%m-%d')='".FormatDateForSQL($_POST['Date'])."'";

$result=DB_query($sql,$db,'','',false,false);

if (DB_error_no($db)!=0){
	$title = _('Transaction Listing');
	include('includes/header.inc');
	prnMsg(_('An error occurred getting the transactions'),'error');
	include('includes/footer.inc');
	exit;
} elseif (DB_num_rows($result) == 0){
	$title = _('Transaction Listing');
	include('includes/header.inc');
	echo '<br>';
  	prnMsg (_('There were no transactions found in the database for the date') . ' ' . $_POST['Date'] .'. '._('Please try again selecting a different date'), 'info');
	include('includes/footer.inc');
  	exit;
}

include('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addInfo('Title',_('Stock Transaction Listing'));
$pdf->addInfo('Subject',_('Stock transaction listing from') . '  ' . $_POST['Date'] );
$line_height=12;
$PageNumber = 1;

include ('includes/PDFStockTransListingPageHeader.inc');

while ($myrow=DB_fetch_array($result)){

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,160,$FontSize,$myrow['description'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+162,$YPos,80,$FontSize,$myrow['transno'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+242,$YPos,70,$FontSize,ConvertSQLDate($myrow['trandate']), 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+312,$YPos,70,$FontSize,number_format($myrow['qty'],$myrow['decimalplaces']), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+382,$YPos,70,$FontSize,$myrow['locationname'], 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,$myrow['reference'], 'right');

	$YPos -= ($line_height);

	  if ($YPos - (2 *$line_height) < $Bottom_Margin){
		  /*Then set up a new page */
			  $PageNumber++;
		  include ('includes/PDFStockTransListingPageHeader.inc');
	  } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$YPos-=$line_height;

/* UldisN
$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header('Content-type: application/pdf');
header('Content-Length: ' . $len);
header('Content-Disposition: inline; filename=ChequeListing.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

$pdf->stream();
*/
$ReportFileName = $_SESSION['DatabaseName'] . '_StockTransListing_' . date('Y-m-d').'.pdf';
$pdf->OutputD($ReportFileName);//UldisN
$pdf->__destruct(); //UldisN

?>
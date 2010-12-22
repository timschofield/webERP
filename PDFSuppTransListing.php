<?php

/* $Id$*/

/* $Revision: 1.13 $ */

//$PageSecurity = 3;
include('includes/SQL_CommonFunctions.inc');
include ('includes/session.inc');

$InputError=0;
if (isset($_POST['Date']) AND !Is_Date($_POST['Date'])){
	$msg = _('The date must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['Date']);
}

if (!isset($_POST['Date'])){

	 $title = _('Supplier Transaction Listing');
	 include ('includes/header.inc');

	echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title . '" alt="">' . ' '
		. _('Supplier Transaction Listing');

	if ($InputError==1){
		prnMsg($msg,'error');
	}

	 echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	 echo '<table class=selection>
	 			<tr>
				<td>' . _('Enter the date for which the transactions are to be listed') . ":</td>
				<td><input type=text name='Date' maxlength=10 size=10 class=date alt='" . $_SESSION['DefaultDateFormat'] . "' VALUE='" . Date($_SESSION['DefaultDateFormat']) . "'></td>
			</tr>";

	echo '<tr><td>' . _('Transaction type') . '</td><td>';

	echo "<select name='TransType'>";

	echo '<option value=20>' . _('Invoices').'</option>';
	echo '<option value=21>' . _('Credit Notes').'</option>';
	echo '<option value=22>' . _('Payments').'</option>';

	 echo '</select></td></tr>';

	 echo "</select></td></tr></table><br><div class='centre'><input type=submit name='Go' value='" . _('Create PDF') . "'></div>";


	 include('includes/footer.inc');
	 exit;
} else {

	include('includes/ConnectDB.inc');
}

$sql= "SELECT type,
		supplierno,
		suppreference,
		trandate,
		ovamount,
		ovgst,
		transtext
	FROM supptrans
	WHERE type='" . $_POST['TransType'] . "'
	AND date_format(inputdate, '%Y-%m-%d')='".FormatDateForSQL($_POST['Date'])."'";

$result=DB_query($sql,$db,'','',false,false);

if (DB_error_no($db)!=0){
	$title = _('Payment Listing');
	include('includes/header.inc');
	prnMsg(_('An error occurred getting the payments'),'error');
	if ($Debug==1){
			prnMsg(_('The SQL used to get the receipt header information that failed was') . ':<br>' . $SQL,'error');
	}
	include('includes/footer.inc');
  	exit;
} elseif (DB_num_rows($result) == 0){
	$title = _('Payment Listing');
	include('includes/header.inc');
	echo '<br>';
  	prnMsg (_('There were no transactions found in the database for the date') . ' ' . $_POST['Date'] .'. '._('Please try again selecting a different date'), 'info');
	include('includes/footer.inc');
  	exit;
}

include('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addInfo('Title',_('Supplier Transaction Listing'));
$pdf->addInfo('Subject',_('Supplier transaction listing from') . '  ' . $_POST['Date'] );
$line_height=12;
$PageNumber = 1;
$TotalCheques = 0;

include ('includes/PDFSuppTransListingPageHeader.inc');

while ($myrow=DB_fetch_array($result)){

	$sql='SELECT suppname FROM suppliers WHERE supplierid="'.$myrow['supplierno'].'"';
	$supplierresult=DB_query($sql, $db);
	$supplierrow=DB_fetch_array($supplierresult);

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,160,$FontSize,$supplierrow['suppname'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+162,$YPos,80,$FontSize,$myrow['suppreference'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+242,$YPos,70,$FontSize,ConvertSQLDate($myrow['trandate']), 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+312,$YPos,70,$FontSize,number_format($myrow['ovamount'],2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+382,$YPos,70,$FontSize,number_format($myrow['ovgst'],2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,number_format($myrow['ovamount']+$myrow['ovgst'],2), 'right');

	  $YPos -= ($line_height);
	  $TotalCheques = $TotalCheques - $myrow['ovamount'];

	  if ($YPos - (2 *$line_height) < $Bottom_Margin){
		  /*Then set up a new page */
			  $PageNumber++;
		  include ('includes/PDFChequeListingPageHeader.inc');
	  } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,number_format(-$TotalCheques,2), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+265,$YPos,300,$FontSize,_('Total') . '  ' . _('Transactions'), 'left');

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
$ReportFileName = $_SESSION['DatabaseName'] . '_SuppTransListing_' . date('Y-m-d').'.pdf';
$pdf->OutputD($ReportFileName);//UldisN
$pdf->__destruct(); //UldisN

?>
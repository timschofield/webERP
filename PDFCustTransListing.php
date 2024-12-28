<?php


include('includes/SQL_CommonFunctions.inc');
include ('includes/session.php');
if (isset($_POST['Date'])){$_POST['Date'] = ConvertSQLDate($_POST['Date']);};

$InputError=0;
if (isset($_POST['Date']) AND !Is_Date($_POST['Date'])){
	$Msg = _('The date must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['Date']);
}

if (!isset($_POST['Date'])){

	 $Title = _('Customer Transaction Listing');

	$ViewTopic = 'ARReports';
	$BookMark = 'DailyTransactions';

	 include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . _('Customer Transaction Listing').
		'</p>';

	if ($InputError==1){
		prnMsg($Msg,'error');
	}

	 echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	 echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /></div>';
	 echo '<fieldset>
	 		<field>
				<label for="Date">' . _('Enter the date for which the transactions are to be listed') . ':</label>
				<input name="Date" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
			</field>';

	echo '<field>
			<label for="TransType">' . _('Transaction type') . '</label>
			<select name="TransType">
				<option value="10">' . _('Invoices') . '</option>
				<option value="11">' . _('Credit Notes') . '</option>
				<option value="12">' . _('Receipts') . '</option>';

	 echo '</select>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Go" value="' . _('Create PDF') . '" />
		</div>
	</form>';

	 include('includes/footer.php');
	 exit;
} else {

	include('includes/ConnectDB.inc');
}

$SQL= "SELECT type,
			debtortrans.debtorno,
			transno,
			trandate,
			ovamount,
			ovgst,
			invtext,
			debtortrans.rate,
			decimalplaces
		FROM debtortrans INNER JOIN debtorsmaster
		ON debtortrans.debtorno=debtorsmaster.debtorno
		INNER JOIN currencies
		ON debtorsmaster.currcode=currencies.currabrev
		WHERE type='" . $_POST['TransType'] . "'
		AND date_format(inputdate, '%Y-%m-%d')='".FormatDateForSQL($_POST['Date'])."'";

$Result=DB_query($SQL,'','',false,false);

if (DB_error_no()!=0){
	$Title = _('Payment Listing');
	include('includes/header.php');
	prnMsg(_('An error occurred getting the transactions'),'error');
	if ($Debug==1){
		prnMsg(_('The SQL used to get the transaction information that failed was') . ':<br />' . $SQL,'error');
	}
	include('includes/footer.php');
	exit;
} elseif (DB_num_rows($Result) == 0){
	$Title = _('Payment Listing');
	include('includes/header.php');
	echo '<br />';
  	prnMsg (_('There were no transactions found in the database for the date') . ' ' . $_POST['Date'] .'. '._('Please try again selecting a different date'), 'info');
	include('includes/footer.php');
  	exit;
}

include('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addInfo('Title',_('Customer Transaction Listing'));
$pdf->addInfo('Subject',_('Customer transaction listing from') . '  ' . $_POST['Date'] );
$LineHeight=12;
$PageNumber = 1;
$TotalAmount = 0;

include ('includes/PDFCustTransListingPageHeader.inc');

while ($MyRow=DB_fetch_array($Result)){

	$SQL="SELECT name FROM debtorsmaster WHERE debtorno='" . $MyRow['debtorno'] . "'";
	$CustomerResult=DB_query($SQL);
	$CustomerRow=DB_fetch_array($CustomerResult);

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,160,$FontSize,$CustomerRow['name'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+162,$YPos,80,$FontSize,$MyRow['transno'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+242,$YPos,70,$FontSize,ConvertSQLDate($MyRow['trandate']), 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+312,$YPos,70,$FontSize,locale_number_format($MyRow['ovamount'],$MyRow['decimalplaces']), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+382,$YPos,70,$FontSize,locale_number_format($MyRow['ovgst'],$MyRow['decimalplaces']), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,locale_number_format($MyRow['ovamount']+$MyRow['ovgst'],$MyRow['decimalplaces']), 'right');

	  $YPos -= ($LineHeight);
	  $TotalAmount = $TotalAmount + ($MyRow['ovamount']/$MyRow['rate']);

	  if ($YPos - (2 *$LineHeight) < $Bottom_Margin){
		  /*Then set up a new page */
			  $PageNumber++;
		  include ('includes/PDFCustTransListingPageHeader.inc');
	  } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$YPos-=$LineHeight;
$LeftOvers = $pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,locale_number_format($TotalAmount,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+265,$YPos,300,$FontSize,_('Total') . '  ' . _('Transactions') . ' ' . $_SESSION['CompanyRecord']['currencydefault'], 'left');

$ReportFileName = $_SESSION['DatabaseName'] . '_CustTransListing_' . date('Y-m-d').'.pdf';
$pdf->OutputD($ReportFileName);
$pdf->__destruct();

?>
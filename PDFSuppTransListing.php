<?php

include('includes/SQL_CommonFunctions.php');
include('includes/session.php');
if (isset($_POST['Date'])){$_POST['Date'] = ConvertSQLDate($_POST['Date']);}

$InputError=0;
if (isset($_POST['Date']) AND !Is_Date($_POST['Date'])){
	$Msg = __('The date must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['Date']);
}

if (!isset($_POST['Date'])){

	 $Title = __('Supplier Transaction Listing');
	 $ViewTopic = 'AccountsPayable';
	 $BookMark = '';
	 include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' '
		. __('Supplier Transaction Listing') . '</p>';

	if ($InputError==1){
		prnMsg($Msg,'error');
	}

	 echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	 echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	 echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="Date">' . __('Enter the date for which the transactions are to be listed') . ':</label>
				<input name="Date" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
			</field>';

	echo '<field>
			<label for="TransType">' . __('Transaction type') . '</label>
			<select name="TransType">
				<option value="20">' . __('Invoices') . '</option>
				<option value="21">' . __('Credit Notes') . '</option>
				<option value="22">' . __('Payments') . '</option>
			</select>
		</field>';

	 echo '</fieldset>
			<div class="centre">
				<input type="submit" name="Go" value="' . __('Create PDF') . '" />
			</div>';
     echo '</form>';

	 include('includes/footer.php');
	 exit();
} else {

	include('includes/ConnectDB.php');
}

$SQL= "SELECT type,
			supplierno,
			suppreference,
			trandate,
			ovamount,
			ovgst,
			transtext,
			currcode,
			decimalplaces AS currdecimalplaces,
			suppname
		FROM supptrans INNER JOIN suppliers
		ON supptrans.supplierno = suppliers.supplierid
		INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE type='" . $_POST['TransType'] . "'
		AND trandate='" . FormatDateForSQL($_POST['Date']) . "'";

$ErrMsg = __('An error occurred getting the payments');
$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result) == 0){
	$Title = __('Payment Listing');
	include('includes/header.php');
	echo '<br />';
  	prnMsg(__('There were no transactions found in the database for the date') . ' ' . $_POST['Date'] .'. '.__('Please try again selecting a different date'), 'info');
	include('includes/footer.php');
  	exit();
}

include('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addInfo('Title',__('Supplier Transaction Listing'));
$pdf->addInfo('Subject',__('Supplier transaction listing from') . '  ' . $_POST['Date'] );
$LineHeight=12;
$PageNumber = 1;
$TotalCheques = 0;

include('includes/PDFSuppTransListingPageHeader.php');

while ($MyRow=DB_fetch_array($Result)){
    $CurrDecimalPlaces = $MyRow['currdecimalplaces'];
	$pdf->addTextWrap($Left_Margin,$YPos,160,$FontSize,$MyRow['suppname'], 'left');
	$pdf->addTextWrap($Left_Margin+162,$YPos,80,$FontSize,$MyRow['suppreference'], 'left');
	$pdf->addTextWrap($Left_Margin+242,$YPos,70,$FontSize,ConvertSQLDate($MyRow['trandate']), 'left');
	$pdf->addTextWrap($Left_Margin+312,$YPos,70,$FontSize,locale_number_format($MyRow['ovamount'],$CurrDecimalPlaces), 'right');
	$pdf->addTextWrap($Left_Margin+382,$YPos,70,$FontSize,locale_number_format($MyRow['ovgst'],$CurrDecimalPlaces), 'right');
	$pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,locale_number_format($MyRow['ovamount']+$MyRow['ovgst'],$CurrDecimalPlaces), 'right');

	  $YPos -= ($LineHeight);
	  $TotalCheques = $TotalCheques - $MyRow['ovamount'];

	  if ($YPos - (2 *$LineHeight) < $Bottom_Margin){
		/*Then set up a new page */
		$PageNumber++;
		include('includes/PDFChequeListingPageHeader.php');
	  } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$YPos-=$LineHeight;
$pdf->addTextWrap($Left_Margin+452,$YPos,70,$FontSize,locale_number_format(-$TotalCheques,$CurrDecimalPlaces), 'right');
$pdf->addTextWrap($Left_Margin+265,$YPos,300,$FontSize,__('Total') . '  ' . __('Transactions'), 'left');

$ReportFileName = $_SESSION['DatabaseName'] . '_SuppTransListing_' . date('Y-m-d').'.pdf';
$pdf->OutputD($ReportFileName);
$pdf->__destruct();

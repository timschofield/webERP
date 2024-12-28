<?php


include('includes/SQL_CommonFunctions.inc');
include ('includes/session.php');
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};
$ViewTopic= 'GeneralLedger';
$BookMark = 'ChequePaymentListing';

$InputError=0;
if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
	$Msg = _('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['FromDate']);
}
if (isset($_POST['ToDate']) and !Is_Date($_POST['ToDate'])){
	$Msg = _('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['ToDate']);
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])){


	 $Title = _('Payment Listing');
	 include ('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		 $Title . '" alt="" />' . ' ' . $Title . '</p>';

	if ($InputError==1){
		prnMsg($Msg,'error');
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';

	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /></div>';
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
	 		<field>
				<label for="FromDate">' . _('Enter the date from which cheques are to be listed') . ':</label>
				<input name="FromDate" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
			</field>';
	 echo '<field>
				<label for="ToDate">' . _('Enter the date to which cheques are to be listed') . ':</label>
				<input name="ToDate" maxlength="10" size="11"  type="date" value="' . Date('Y-m-d') . '" />
		</field>';
	 echo '<field>
			<label for="BankAccount">' . _('Bank Account') . '</label>';

	 $SQL = "SELECT bankaccountname, accountcode FROM bankaccounts";
	 $Result = DB_query($SQL);

	 echo '<select name="BankAccount">';

	 while ($MyRow=DB_fetch_array($Result)){
		echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . '</option>';
	 }

	 echo '</select>
		</field>';

	 echo '<field>
				<label for="Email">' . _('Email the report off') . ':</label>
				<select name="Email">
					<option selected="selected" value="No">' . _('No') . '</option>
					<option value="Yes">' . _('Yes') . '</option>
				</select>
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

$SQL = "SELECT bankaccountname,
				decimalplaces AS bankcurrdecimalplaces
		FROM bankaccounts INNER JOIN currencies
		ON bankaccounts.currcode=currencies.currabrev
		WHERE accountcode = '" .$_POST['BankAccount'] . "'";
$BankActResult = DB_query($SQL);
$MyRow = DB_fetch_array($BankActResult);
$BankAccountName = $MyRow['bankaccountname'];
$BankCurrDecimalPlaces = $MyRow['bankcurrdecimalplaces'];

$SQL= "SELECT amount,
			ref,
			transdate,
			banktranstype,
			type,
			transno
		FROM banktrans
		WHERE banktrans.bankact='" . $_POST['BankAccount'] . "'
		AND (banktrans.type=1 or banktrans.type=22)
		AND transdate >='" . FormatDateForSQL($_POST['FromDate']) . "'
		AND transdate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

$Result=DB_query($SQL,'','',false,false);
if (DB_error_no()!=0){
	$Title = _('Payment Listing');
	include('includes/header.php');
	prnMsg(_('An error occurred getting the payments'),'error');
	if ($Debug==1){
		prnMsg(_('The SQL used to get the receipt header information that failed was') . ':<br />' . $SQL,'error');
	}
	include('includes/footer.php');
  	exit;
} elseif (DB_num_rows($Result) == 0){
	$Title = _('Payment Listing');
	include('includes/header.php');
  	prnMsg (_('There were no bank transactions found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' ._('Please try again selecting a different date range or account'), 'error');
	include('includes/footer.php');
  	exit;
}

include('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addInfo('Title',_('Cheque Listing'));
$pdf->addInfo('Subject',_('Cheque listing from') . '  ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
$LineHeight=12;
$PageNumber = 1;
$TotalCheques = 0;

include ('includes/PDFChequeListingPageHeader.inc');

while ($MyRow=DB_fetch_array($Result)){

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,locale_number_format(-$MyRow['amount'],$BankCurrDecimalPlaces), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,90,$FontSize,$MyRow['ref'], 'left');

	$SQL = "SELECT accountname,
					accountcode,
					amount,
					narrative
			FROM gltrans INNER JOIN chartmaster
			ON gltrans.account=chartmaster.accountcode
			WHERE gltrans.typeno ='" . $MyRow['transno'] . "'
			AND gltrans.type='" . $MyRow['type'] . "'";

	$GLTransResult = DB_query($SQL,'','',false,false);
	if (DB_error_no()!=0){
		$Title = _('Payment Listing');
		include('includes/header.php');
   		prnMsg(_('An error occurred getting the GL transactions'),'error');
		if ($Debug==1){
				prnMsg( _('The SQL used to get the receipt header information that failed was') . ':<br />' . $SQL, 'error');
		}
		include('includes/footer.php');
  		exit;
	}
	while ($GLRow=DB_fetch_array($GLTransResult)){
		// if user is allowed to see the account we show it, other wise we show "OTHERS ACCOUNTS"
		$CheckSql = "SELECT count(*)
					 FROM glaccountusers
					 WHERE accountcode= '" . $GLRow['accountcode'] . "'
						 AND userid = '" . $_SESSION['UserID'] . "'
						 AND canview = '1'";
		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);

		if ($CheckRow[0] > 0) {
			$AccountName = $GLRow['accountname'];
		}else{
			$AccountName = _('Other GL Accounts');
		}
		$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,90,$FontSize,$AccountName, 'left');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+245,$YPos,60,$FontSize,locale_number_format($GLRow['amount'],$_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,120,$FontSize,$GLRow['narrative'], 'left');
		$YPos -= ($LineHeight);
		if ($YPos - (2 *$LineHeight) < $Bottom_Margin){
		  		/*Then set up a new page */
			  		$PageNumber++;
		  		include ('includes/PDFChequeListingPageHeader.inc');
	  		} /*end of new page header  */
	}
	DB_free_result($GLTransResult);

	$YPos -= ($LineHeight);
	$TotalCheques = $TotalCheques - $MyRow['amount'];

	if ($YPos - (2 *$LineHeight) < $Bottom_Margin){
		  /*Then set up a new page */
		  $PageNumber++;
		  include ('includes/PDFChequeListingPageHeader.inc');
	  } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$YPos-=$LineHeight;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,locale_number_format($TotalCheques,2), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,300,$FontSize,_('TOTAL') . ' ' . $Currency . ' ' . _('CHEQUES'), 'left');

$ReportFileName = $_SESSION['DatabaseName'] . '_ChequeListing_' . date('Y-m-d').'.pdf';
$pdf->Output($_SESSION['reports_dir'].'/'.$ReportFileName,'F');
$pdf->OutputD($ReportFileName);
$pdf->__destruct();
if ($_POST['Email']=='Yes'){

	include('includes/htmlMimeMail.php');
	$mail = new htmlMimeMail();
	$Attachment = $mail->getFile($_SESSION['reports_dir'] . '/'.$ReportFileName);
	$mail->setSubject(_('Payments check list'));
	$mail->setText(_('Please find herewith payments listing from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
	$mail->addAttachment($Attachment, 'PaymentListing.pdf', 'application/pdf');
	$ChkListingRecipients = GetMailList('ChkListingRecipients');
	if(sizeOf($ChkListingRecipients) == 0){
		prnMsg(_('There are no member in Check Listing Recipients email group,  no mail will be sent'),'error');
		include('includes/footer.php');
		exit;
	}

	if($_SESSION['SmtpSetting']==0){
		$mail->setFrom(array('"' . $_SESSION['CompanyRecord']['coyname'] . '" <' . $_SESSION['CompanyRecord']['email'] . '>'));
		$Result = $mail->send($ChkListingRecipients);
	}else{
		$Result = SendmailBySmtp($mail,$ChkListingRecipients);
	}
}
?>

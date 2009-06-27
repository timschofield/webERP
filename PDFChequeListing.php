<?php

/* $Revision: 1.12 $ */

$PageSecurity = 3;
include('includes/SQL_CommonFunctions.inc');
include ('includes/session.inc');

$InputError=0;
if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
	$msg = _('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['FromDate']);
}
if (isset($_POST['ToDate']) and !Is_Date($_POST['ToDate'])){
	$msg = _('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['ToDate']);
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])){


     $title = _('Payment Listing');
     include ('includes/header.inc');

     if ($InputError==1){
	prnMsg($msg,'error');
     }

     echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
     echo '<table>
     			<tr>
				<td>' . _('Enter the date from which cheques are to be listed') . ":</td>
				<td><input type=text name='FromDate' maxlength=10 size=10 onChange='return isDate(this, this.value, ".'"'.$_SESSION['DefaultDateFormat'].'"'.")'  VALUE='" . Date($_SESSION['DefaultDateFormat']) . "'></td>
			</tr>";
     echo '<tr><td>' . _('Enter the date to which cheques are to be listed') . ":</td>
     		<td><input type=text name='ToDate' maxlength=10 size=10 onChange='return isDate(this, this.value, ".'"'.$_SESSION['DefaultDateFormat'].'"'.")'  VALUE='" . Date($_SESSION['DefaultDateFormat']) . "'></td>
	</tr>";
     echo '<tr><td>' . _('Bank Account') . '</td><td>';

     $sql = 'SELECT bankaccountname, accountcode FROM bankaccounts';
     $result = DB_query($sql,$db);


     echo "<select name='BankAccount'>";

     while ($myrow=DB_fetch_array($result)){
	echo '<option VALUE=' . $myrow['accountcode'] . '>' . $myrow['bankaccountname'];
     }


     echo '</select></td></tr>';

     echo '<tr><td>' . _('Email the report off') . ":</td><td><select name='Email'>";
     echo "<option selected VALUE='No'>" . _('No');
     echo "<option VALUE='Yes'>" . _('Yes');
     echo "</select></td></tr></table><div class='centre'><input type=submit name='Go' VALUE='" . _('Create PDF') . "'></div>";


     include('includes/footer.inc');
     exit;
} else {

	include('includes/ConnectDB.inc');
}


$SQL = 'SELECT bankaccountname
	FROM bankaccounts
	WHERE accountcode = ' .$_POST['BankAccount'];
$BankActResult = DB_query($SQL,$db);
$myrow = DB_fetch_row($BankActResult);
$BankAccountName = $myrow[0];

$SQL= "SELECT amount,
		ref,
		transdate,
		banktranstype,
		type,
		transno
	FROM banktrans
	WHERE banktrans.bankact=" . $_POST['BankAccount'] . "
	AND (banktrans.type=1 or banktrans.type=22)
	AND transdate >='" . FormatDateForSQL($_POST['FromDate']) . "'
	AND transdate <='" . FormatDateForSQL($_POST['ToDate']) . "'";


$Result=DB_query($SQL,$db,'','',false,false);
if (DB_error_no($db)!=0){
	$title = _('Payment Listing');
	include('includes/header.inc');
	prnMsg(_('An error occurred getting the payments'),'error');
	if ($Debug==1){
        	prnMsg(_('The SQL used to get the receipt header information that failed was') . ':<br>' . $SQL,'error');
	}
	include('includes/footer.inc');
  	exit;
} elseif (DB_num_rows($Result)==0){
	$title = _('Payment Listing');
	include('includes/header.inc');
  	prnMsg (_('There were no bank transactions found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' ._('Please try again selecting a different date range or account'), 'error');
	include('includes/footer.inc');
  	exit;
}

include('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title',_('Cheque Listing'));
$pdf->addinfo('Subject',_('Cheque listing from') . '  ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);

$line_height=12;
$PageNumber = 1;

$TotalCheques = 0;

include ('includes/PDFChequeListingPageHeader.inc');

while ($myrow=DB_fetch_array($Result)){

      	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format(-$myrow['amount'],2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,90,$FontSize,$myrow['ref'], 'left');

	$sql = 'SELECT accountname,
			amount,
			narrative
		FROM gltrans,
			chartmaster
		WHERE chartmaster.accountcode=gltrans.account
		AND gltrans.typeno =' . $myrow['transno'] . '
		AND gltrans.type=' . $myrow['type'];

	$GLTransResult = DB_query($sql,$db,'','',false,false);
	if (DB_error_no($db)!=0){
		$title = _('Payment Listing');
		include('includes/header.inc');
   		prnMsg(_('An error occurred getting the GL transactions'),'error');
		if ($debug==1){
        		prnMsg( _('The SQL used to get the receipt header information that failed was') . ':<br>' . $sql, 'error');
		}
		include('includes/footer.inc');
  		exit;
	}
	while ($GLRow=DB_fetch_array($GLTransResult)){
		$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,90,$FontSize,$GLRow['accountname'], 'left');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+245,$YPos,60,$FontSize,number_format($GLRow['amount'],2), 'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,120,$FontSize,$GLRow['narrative'], 'left');
		$YPos -= ($line_height);
		if ($YPos - (2 *$line_height) < $Bottom_Margin){
          		/*Then set up a new page */
              		$PageNumber++;
	      		include ('includes/PDFChequeListingPageHeader.inc');
      		} /*end of new page header  */
	}
	DB_free_result($GLTransResult);

      $YPos -= ($line_height);
      $TotalCheques = $TotalCheques - $myrow['amount'];

      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              $PageNumber++;
	      include ('includes/PDFChequeListingPageHeader.inc');
      } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format($TotalCheques,2), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,300,$FontSize,_('TOTAL') . ' ' . $Currency . ' ' . _('CHEQUES'), 'left');


$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header('Content-type: application/pdf');
header('Content-Length: ' . $len);
header('Content-Disposition: inline; filename=ChequeListing.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

$pdf->stream();

if ($_POST['Email']=='Yes'){
	if (file_exists($_SESSION['reports_dir'] . '/PaymentListing.pdf')){
		unlink($_SESSION['reports_dir'] . '/PaymentListing.pdf');
	}
    	$fp = fopen( $_SESSION['reports_dir'] . '/PaymentListing.pdf','wb');
	fwrite ($fp, $pdfcode);
	fclose ($fp);

	include('includes/htmlMimeMail.php');

	$mail = new htmlMimeMail();
	$attachment = $mail->getFile($_SESSION['reports_dir'] . '/PaymentListing.pdf');
	$mail->setText(_('Please find herewith payments listing from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
	$mail->addAttachment($attachment, 'PaymentListing.pdf', 'application/pdf');
	$mail->setFrom(array('"' . $_SESSION['CompanyRecord']['coyname'] . '" <' . $_SESSION['CompanyRecord']['email'] . '>'));

	/* $ChkListingRecipients defined in config.php */
	$result = $mail->send($ChkListingRecipients);
}

?>
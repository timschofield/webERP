<?php
/* $Revision: 1.3 $ */
$PageSecurity = 3;
include("includes/SQL_CommonFunctions.inc");
include("includes/DateFunctions.inc");
include("config.php");

$InputError=0;

if (!Is_Date($_POST['FromDate'])){
	$msg = "<BR>The date entered was in an unrecognised format it must be specified in the format $DefaultDateFormat";
	$InputError=1;
}
if (!Is_Date($_POST['ToDate'])){
	$msg = "<BR>The date to must be specified in the format $DefaultDateFormat";
	$InputError=1;
}

if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate']) OR $InputError==1){
     $title = "Payment Listing";
     include ("includes/session.inc");
     include ("includes/header.inc");

     echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
     echo "<CENTER><TABLE><TR><TD>Enter the date from which cheques are to be listed:</TD><TD><INPUT TYPE=text NAME='FromDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD></TR>";
     echo "<TR><TD>Enter the date to which cheques are to be listed:</TD><TD><INPUT TYPE=text NAME='ToDate' MAXLENGTH=10 SIZE=10 VALUE='" . Date($DefaultDateFormat) . "'></TD></TR>";
     echo "<TR><TD>Bank Account</TD><TD>";

     $sql = "SELECT BankAccountName, AccountCode FROM BankAccounts";
     $result = DB_query($sql,$db);


     echo "<SELECT NAME='BankAccount'>";

     while ($myrow=DB_fetch_array($result)){
	echo "<OPTION VALUE=" . $myrow['AccountCode'] . ">" . $myrow['BankAccountName'];
     }


     echo "</SELECT></TD></TR>";

     echo "<TR><TD>Email the report off:</TD><TD><SELECT NAME='Email'>";
     echo "<OPTION SELECTED VALUE='No'>No";
     echo "<OPTION VALUE='Yes'>Yes";
     echo "</SELECT></TD></TR></TABLE><INPUT TYPE=SUBMIT NAME='Go' VALUE='Create PDF'></CENTER>";

     if ($InputError==1){
     	echo $msg;
     }
     include("includes/footer.inc");
     exit;
} else {
	include("includes/ConnectDB.inc");
}


$SQL = "SELECT BankAccountName FROM BankAccounts WHERE AccountCode = " .$_POST['BankAccount'];
$BankActResult = DB_query($SQL,$db);
$myrow = DB_fetch_row($BankActResult);
$BankAccountName = $myrow[0];

$SQL= "SELECT Amount, Ref, TransDate, BankTransType, Type, TransNo FROM BankTrans WHERE BankTrans.BankAct=" . $_POST['BankAccount'] . " AND (BankTrans.Type=1 OR BankTrans.Type=22) AND TransDate >='" . FormatDateForSQL($_POST['FromDate']) . "' AND TransDate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

//echo $SQL;
//exit;

$Result=DB_query($SQL,$db);
if (DB_error_no($db)!=0){
   echo "<BR>An error occurred getting the payments";
  if ($Debug==1){
        echo "The SQL used to get the receipt header information (that failed) was:<BR>$SQL";
  }
  break;
  exit;
} elseif (DB_num_rows($Result)==0){
  	die ("<BR>There were no bank transactions found in the database within the period from " . $_POST['FromDate'] . " to " . $_POST['ToDate'] . ". Please try again selecting a different date range or account");
}

$CompanyRecord = ReadInCompanyRecord($db);

include("includes/PDFStarter_ros.inc");

/*PDFStarter_ros.inc has all the variables for page size and width set up depending on the users default preferences for paper size */

$pdf->addinfo('Title',"Cheque Listing");
$pdf->addinfo('Subject',"Cheque listing from  " . $_POST['FromDate'] . " to " . $_POST['ToDate']);

$line_height=12;
$PageNumber = 1;

$TotalCheques = 0;

include ("includes/PDFChequeListingPageHeader.inc");

while ($myrow=DB_fetch_array($Result)){

      	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format(-$myrow["Amount"],2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,90,$FontSize,$myrow["Ref"], 'left');

	$sql = "SELECT AccountName, Amount, Narrative FROM GLTrans, ChartMaster WHERE ChartMaster.AccountCode=GLTrans.Account AND GLTrans.TypeNo =" . $myrow['TransNo'] . " AND GLTrans.Type=" . $myrow['Type'];
	$GLTransResult = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
   		echo "<BR>An error occurred getting the GL transactions";

        		echo "<BR>The SQL used to get the receipt header information (that failed) was:<BR>$sql";

  		break;
  		exit;
	}
	while ($GLRow=DB_fetch_array($GLTransResult)){
		$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,90,$FontSize,$GLRow["AccountName"], 'left');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+245,$YPos,60,$FontSize,number_format($GLRow["Amount"],2), 'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,120,$FontSize,$GLRow["Narrative"], 'left');
		$YPos -= ($line_height);
		if ($YPos - (2 *$line_height) < $Bottom_Margin){
          		/*Then set up a new page */
              		$PageNumber++;
	      		include ("includes/PDFChequeListingPageHeader.inc");
      		} /*end of new page header  */
	}
	DB_free_result($GLTransResult);

      $YPos -= ($line_height);
      $TotalCheques = $TotalCheques - $myrow["Amount"];

      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              $PageNumber++;
	      include ("includes/PDFChequeListingPageHeader.inc");
      } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */


$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format($TotalCheques,2), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,300,$FontSize,"TOTAL " . $Currency . " CHEQUES", 'left');


$pdfcode = $pdf->output();
$len = strlen($pdfcode);
header("Content-type: application/pdf");
header("Content-Length: " . $len);
header("Content-Disposition: inline; filename=ChequeListing.pdf");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

$pdf->stream();

if ($_POST['Email']=="Yes"){
	if (file_exists($reports_dir . "/PaymentListing.pdf")){
		unlink($reports_dir . "/PaymentListing.pdf");
	}
    	$fp = fopen( $reports_dir . "/PaymentListing.pdf","wb");
	fwrite ($fp, $pdfcode);
	fclose ($fp);

	include('includes/htmlMimeMail.php');

	$mail = new htmlMimeMail();
	$attachment = $mail->getFile($reports_dir . "/PaymentListing.pdf");
	$mail->setText("Please find herewith payments listing from " . $_POST['FromDate'] . " to " . $_POST['ToDate']);
	$mail->addAttachment($attachment, 'PaymentListing.pdf', 'application/pdf');
	$mail->setFrom(array('$CompanyName <' . $CompanyRecord["Email"] .'>'));

	/* $ChkListingRecipients defined in config.php */
	$result = $mail->send($ChkListingRecipients);
}

?>

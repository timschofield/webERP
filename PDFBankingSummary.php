<?php
/* $Revision: 1.4 $ */
$PageSecurity = 3;

if (isset($_GET['BatchNo'])){
	$_POST['BatchNo'] = $_GET['BatchNo'];
}

if (!isset($_POST['BatchNo'])){

     include ('includes/session.inc');
     $title = _('Create PDF Print Out For A Batch Of Receipts');
     include ('includes/header.inc');
     echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';
     echo '<P>' . _('Enter the batch number of receipts to be printed') . ': <INPUT TYPE=text NAME=BatchNo MAXLENGTH=6 SIZE=6>';
     echo "<CENTER><INPUT TYPE=SUBMIT NAME='EnterBatchNo' VALUE='" . _('Create PDF') . "'></CENTER>";
     exit;
}

include('config.php');
include('includes/ConnectDB.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');

$SQL= 'SELECT BankAccountName,
		BankAccountNumber,
		Ref,
		TransDate,
		BankTransType,
		BankAct,
		BankTrans.ExRate,
		BankTrans.CurrCode
	FROM BankAccounts,
		BankTrans
	WHERE BankAccounts.AccountCode=BankTrans.BankAct
	AND BankTrans.TransNo=' . $_POST['BatchNo'] . '
	AND BankTrans.Type=12';

$ErrMsg = _('An error occurred getting the header information about the receipt batch number') . ' ' . $_POST['BatchNo'];
$DbgMsg = _('The SQL used to get the receipt header information that failed was');
$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);

if (DB_num_rows($Result)==0){
	$title = _('Create PDF Print-out For A Batch Of Receipts');
	include ('includes/header.inc');
	prnMsg(_('The receipt batch number') . ' ' . $_POST['BatchNo'] . ' ' . _('was not found in the database') . '. ' . _('Please try again selecting a different batch number'),'error');
	include('includes/footer.inc');
	exit;
}
/* OK get the row of receipt batch header info from the BankTrans table */
$myrow = DB_fetch_array($Result);
$ExRate = $myrow['ExRate'];
$Currency = $myrow['CurrCode'];
$BankTransType = $myrow['BankTransType'];
$BankedDate =  $myrow['TransDate'];
$BankActName = $myrow['BankAccountName'];
$BankActNumber = $myrow['BankAccountNumber'];
$BankingReference = $myrow['Ref'];

$CompanyRecord = ReadInCompanyRecord($db);

$SQL = "SELECT DebtorsMaster.Name,
		OvAmount,
		InvText,
		Reference
	FROM DebtorsMaster,
		DebtorTrans
	WHERE DebtorsMaster.DebtorNo=DebtorTrans.DebtorNo
	AND DebtorTrans.TransNo=" . $_POST['BatchNo'] . '
	AND DebtorTrans.Type=12';

$CustRecs=DB_query($SQL,$db,'','',false,false);
if (DB_error_no($db)!=0){
	$title = _('Create PDF Print-out For A Batch Of Receipts');
	include ('includes/header.inc');
   	prnMsg(_('An error occurred getting the customer receipts for batch number') . ' ' . $_POST['BatchNo'],'error');
	if ($debug==1){
        	prnMsg(_('The SQL used to get the customer receipt information that failed was') . '<BR>' . $SQL,'error');
  	}
	include('includes/footer.inc');
  	exit;
}
$SQL = "SELECT Narrative,
		Amount
	FROM GLTrans
	WHERE GLTrans.TypeNo=" . $_POST['BatchNo'] . "
	AND GLTrans.Type=12 AND GLTrans.Amount <0
	AND GLTrans.Account !=" . $myrow['BankAct'] . '
	AND GLTrans.Account !=' . $CompanyRecord['DebtorsAct'];

$GLRecs=DB_query($SQL,$db,'','',false,false);
if (DB_error_no($db)!=0){
	$title = _('Create PDF Print-out For A Batch Of Receipts');
	include ('includes/header.inc');
	prnMsg(_('An error occurred getting the GL receipts for batch number') . ' ' . $_POST['BatchNo'],'error');
	if ($debug==1){
        	prnMsg(_('The SQL used to get the GL receipt information that failed was') . ':<BR>' . $SQL,'error');
	}
	include('includes/footer.inc');
  	exit;
}

include('includes/PDFStarter_ros.inc');

/*PDFStarter.inc has all the variables for page size and width set up depending on the users default preferences for paper size */


$pdf->addinfo('Title',_('Banking Summary'));
$pdf->addinfo('Subject',_('Banking Summary Number') . ' ' . $_POST['BatchNo']);

$line_height=12;
$PageNumber = 0;

$TotalBanked = 0;

include ('includes/PDFBankingSummaryPageHeader.inc');

while ($myrow=DB_fetch_array($CustRecs)){

      	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format(-$myrow['OvAmount'],2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,150,$FontSize,$myrow['Name'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+215,$YPos,100,$FontSize,$myrow['InvText'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+315,$YPos,100,$FontSize,$myrow['Reference'], 'left');

      $YPos -= ($line_height);
      $TotalBanked = $TotalBanked - $myrow['OvAmount'];

      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              include ('includes/PDFBankingSummaryPageHeader.inc');
      } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */

/* Right now print out the GL receipt entries in the batch */
while ($myrow=DB_fetch_array($GLRecs)){

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format(-$myrow['Amount']*$ExRate,2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,300,$FontSize,$myrow['Narrative'], 'left');
        $YPos -= ($line_height);
        $TotalBanked = $TotalBanked + (-$myrow['Amount']*$ExRate);

      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              include ('includes/PDFBankingSummaryPageHeader.inc');
      } /*end of new page header  */
} /* end of while there are GL receipts in the batch to print */

$YPos-=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format($TotalBanked,2), 'right');
$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,300,$FontSize,_('TOTAL') . ' ' . $Currency . ' ' . _('BANKED'), 'left');


$buf = $pdf->output();
$len = strlen($buf);
header('Content-type: application/pdf');
header('Content-Length: ' . $len);
header('Content-Disposition: inline; filename=BankingSummary.pdf');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

$pdf->stream()

?>

<?php

/* $Revision: 1.8 $ */

$PageSecurity = 3;
include ('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
 
if (isset($_GET['BatchNo'])){
	$_POST['BatchNo'] = $_GET['BatchNo'];
}

if (!isset($_POST['BatchNo'])){
 
     $title = _('Create PDF Print Out For A Batch Of Receipts');
     include ('includes/header.inc');
     echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';
     echo '<P>' . _('Enter the batch number of receipts to be printed') . ': <INPUT TYPE=text NAME=BatchNo MAXLENGTH=6 SIZE=6>';
     echo "<CENTER><INPUT TYPE=SUBMIT NAME='EnterBatchNo' VALUE='" . _('Create PDF') . "'></CENTER>";
     exit;
}

$SQL= 'SELECT bankaccountname,
		bankaccountnumber,
		ref,
		transdate,
		banktranstype,
		bankact,
		banktrans.exrate,
		banktrans.currcode
	FROM bankaccounts,
		banktrans
	WHERE bankaccounts.accountcode=banktrans.bankact
	AND banktrans.transno=' . $_POST['BatchNo'] . '
	AND banktrans.type=12';

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
$ExRate = $myrow['exrate'];
$Currency = $myrow['currcode'];
$BankTransType = $myrow['banktranstype'];
$BankedDate =  $myrow['transdate'];
$BankActName = $myrow['bankaccountname'];
$BankActNumber = $myrow['bankaccountnumber'];
$BankingReference = $myrow['ref'];


$SQL = "SELECT debtorsmaster.name,
		ovamount,
		invtext,
		reference
	FROM debtorsmaster,
		debtortrans
	WHERE debtorsmaster.debtorno=debtortrans.debtorno
	AND debtortrans.transno=" . $_POST['BatchNo'] . '
	AND debtortrans.type=12';

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
$SQL = "SELECT narrative,
		amount
	FROM gltrans
	WHERE gltrans.typeno=" . $_POST['BatchNo'] . "
	AND gltrans.type=12 and gltrans.amount <0
	AND gltrans.account !=" . $myrow['bankact'] . '
	AND gltrans.account !=' . $_SESSION['CompanyRecord']['debtorsact'];

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

include('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */


$pdf->addinfo('Title',_('Banking Summary'));
$pdf->addinfo('Subject',_('Banking Summary Number') . ' ' . $_POST['BatchNo']);

$line_height=12;
$PageNumber = 0;

$TotalBanked = 0;

include ('includes/PDFBankingSummaryPageHeader.inc');

while ($myrow=DB_fetch_array($CustRecs)){

      	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format(-$myrow['ovamount'],2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,150,$FontSize,$myrow['name'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+215,$YPos,100,$FontSize,$myrow['invtext'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+315,$YPos,100,$FontSize,$myrow['reference'], 'left');

      $YPos -= ($line_height);
      $TotalBanked = $TotalBanked - $myrow['ovamount'];

      if ($YPos - (2 *$line_height) < $Bottom_Margin){
          /*Then set up a new page */
              include ('includes/PDFBankingSummaryPageHeader.inc');
      } /*end of new page header  */
} /* end of while there are customer receipts in the batch to print */

/* Right now print out the GL receipt entries in the batch */
while ($myrow=DB_fetch_array($GLRecs)){

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,number_format(-$myrow['amount']*$ExRate,2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,300,$FontSize,$myrow['narrative'], 'left');
        $YPos -= ($line_height);
        $TotalBanked = $TotalBanked + (-$myrow['amount']*$ExRate);

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
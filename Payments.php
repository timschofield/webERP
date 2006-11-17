<?php

/* $Revision: 1.12 $ */

$PageSecurity = 5;

include('includes/DefinePaymentClass.php');
include('includes/session.inc');

$title = _('Payment Entry');

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['PaymentCancelled'])) {
  prnMsg(_('Payment Cancelled since cheque was not printed'), 'warning');
  include('includes/footer.inc');
  exit();
}

if ($_GET['NewPayment']=='Yes'){
	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);
}

if (!isset($_SESSION['PaymentDetail'])){
	$_SESSION['PaymentDetail'] = new Payment;
	$_SESSION['PaymentDetail']->GLItemCounter = 1;
}

echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Back to Suppliers') . '</a><BR>';

if (isset($_GET['SupplierID'])){
	/*The page was called with a supplierID check it is valid and default the inputs for Supplier Name and currency of payment */

	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);
	$_SESSION['PaymentDetail'] = new Payment;
	$_SESSION['PaymentDetail']->GLItemCounter = 1;


	$SQL= "SELECT suppname,
			address1,
			address2,
			address3,
			address4,
			address5,
			address6,
			currcode
		FROM suppliers
		WHERE supplierid='" . $_GET['SupplierID'] . "'";

	$Result = DB_query($SQL, $db);
	if (DB_num_rows($Result)==0){
		prnMsg( _('The supplier code that this payment page was called with is not a currently defined supplier code') . '. ' . _('If this page is called from the SelectSupplier page then this assures that a valid supplier is selected'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow = DB_fetch_array($Result);
		$_SESSION['PaymentDetail']->SuppName = $myrow['suppname'];
		$_SESSION['PaymentDetail']->Address1 = $myrow['address1'];
		$_SESSION['PaymentDetail']->Address2 = $myrow['address2'];
		$_SESSION['PaymentDetail']->Address3 = $myrow['address3'];
		$_SESSION['PaymentDetail']->Address4 = $myrow['address4'];
		$_SESSION['PaymentDetail']->Address5 = $myrow['address5'];
		$_SESSION['PaymentDetail']->Address6 = $myrow['address6'];
		$_SESSION['PaymentDetail']->SupplierID = $_GET['SupplierID'];
		$_SESSION['PaymentDetail']->Currency = $myrow['currcode'];
	}
}

if ($_POST['BankAccount']!=""){
	$_SESSION['PaymentDetail']->Account=$_POST['BankAccount'];
}
if ($_POST['DatePaid']!="" AND Is_Date($_POST['DatePaid'])){
	$_SESSION['PaymentDetail']->DatePaid=$_POST['DatePaid'];
}
if ($_POST['ExRate']!=""){
	$_SESSION['PaymentDetail']->ExRate=$_POST['ExRate'];
}
if ($_POST['PaymentType']!=""){
	$_SESSION['PaymentDetail']->PaymentType = $_POST['PaymentType'];
}
if ($_POST['Currency']!=""){
	$_SESSION['PaymentDetail']->Currency=$_POST['Currency'];
}
if ($_POST['Narrative']!=""){
	$_SESSION['PaymentDetail']->Narrative=$_POST['Narrative'];
}
if ($_POST['Amount']!=""){
	$_SESSION['PaymentDetail']->Amount=$_POST['Amount'];
} else {
	if (!isset($_SESSION['PaymentDetail']->Amount)) {
	  $_SESSION['PaymentDetail']->Amount=0;
  }
}
if ($_POST['Discount']!=""){
	$_SESSION['PaymentDetail']->Discount=$_POST['Discount'];
} else {
	if (!isset($_SESSION['PaymentDetail']->Discount)) {
	  $_SESSION['PaymentDetail']->Discount=0;
  }
}


$msg="";

if (isset($_POST['CommitBatch'])){

  /* once the GL analysis of the payment is entered (if the Creditors_GLLink is active),
  process all the data in the session cookie into the DB creating a banktrans record for
  the payment in the batch and SuppTrans record for the supplier payment if a supplier was selected
  A GL entry is created for each GL entry (only one for a supplier entry) and one for the bank
  account credit.

  NB allocations against supplier payments are a seperate exercice

  if GL integrated then
  first off run through the array of payment items $_SESSION['Payment']->GLItems and
  create GL Entries for the GL payment items
  */

  /*First off  check we have an amount entered as paid ?? */
  $TotalAmount =0;
  foreach ($_SESSION['PaymentDetail']->GLItems AS $PaymentItem) {
	  $TotalAmount += $PaymentItem->Amount;
  }

  if ($TotalAmount==0 AND 
	  ($_SESSION["PaymentDetail"]->Discount + $_SESSION["PaymentDetail"]->Amount)/$_SESSION['PaymentDetail']->ExRate ==0){
	  prnMsg( _('This payment has no amounts entered and will not be processed'),'warn');
	  include('includes/footer.inc');
	  exit;
  }
	
  /*Make an array of the defined bank accounts */
	$SQL = "SELECT bankaccounts.accountcode
			FROM bankaccounts,
				chartmaster
		WHERE bankaccounts.accountcode=chartmaster.accountcode";
	$result = DB_query($SQL,$db);
	$BankAccounts = array();
	$i=0;

	while ($Act = DB_fetch_row($result)){
		$BankAccounts[$i]= $Act[0];
		$i++;
  	}

  $PeriodNo = GetPeriod($_SESSION['PaymentDetail']->DatePaid,$db);

  // first time through commit if supplier cheque then print it first 
	if ((!isset($_POST['ChequePrinted']))
		  AND (!isset($_POST['PaymentCancelled']))
		  AND ($_SESSION['PaymentDetail']->PaymentType == 'Cheque')) {
     // it is a supplier payment by cheque and haven't printed yet so print cheque 

    echo '<BR><A  HREF="' . $rootpath . '/PrintCheque.php?' . SID . '&ChequeNum=' . $_POST['ChequeNum'] . '">' . _('Print Cheque') . '</A><BR><BR>';
	
	  echo '<FORM METHOD="post" action="' . $_SERVER['PHP_SELF'] . '">';
	  echo _('Has the cheque been printed') . '?<BR>';
	  echo '<INPUT TYPE="hidden" NAME="CommitBatch" VALUE="' . $_POST['CommitBatch'] . '">';
	  echo '<INPUT TYPE="submit" NAME="ChequePrinted" VALUE="' . _('Yes') . '">&nbsp;&nbsp;';
	  echo '<INPUT TYPE="submit" NAME="PaymentCancelled" VALUE="' . _('No') . '">';
  } else {

  //Start a transaction to do the whole lot inside 
  $SQL = 'BEGIN';
  $result = DB_query($SQL,$db);


  if ($_SESSION['PaymentDetail']->SupplierID=='') {

	  //its a nominal bank transaction type 1 

	  $TransNo = GetNextTransNo( 1, $db);
	  $TransType = 1;

		if ($_SESSION['CompanyRecord']['gllink_creditors']==1){ /* then enter GLTrans */
			$TotalAmount=0;
			foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {

				 $SQL = 'INSERT INTO gltrans (type,
				 				typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount) ';
			 	$SQL= $SQL . "VALUES (1,
						" . $TransNo . ",
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $PaymentItem->GLCode . ",
						'" . $PaymentItem->Narrative . "',
						" . ($PaymentItem->Amount/$_SESSION['PaymentDetail']->ExRate) . "
						)";
			 	$ErrMsg = _('Cannot insert a GL entry for the payment using the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,_('The SQL that failed was'),true);

			 	$TotalAmount += $PaymentItem->Amount;
			}
			$_SESSION['PaymentDetail']->Amount = $TotalAmount;
			$_SESSION['PaymentDetail']->Discount=0;
   	}

		//Run through the GL postings to check to see if there is a posting to another bank account (or the same one) if there is then a receipt needs to be created for this account too 

		foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {

			if (in_array($PaymentItem->GLCode, $BankAccounts)) {

				$ReceiptTransNo = GetNextTransNo( 2, $db);
				$SQL="INSERT INTO banktrans (transno,
								type,
								bankact,
								ref,
								exrate,
								transdate,
								banktranstype,
								amount,
								currcode) ";
				$SQL= $SQL . "VALUES (" . $ReceiptTransNo . ",
							2,
							" . $PaymentItem->GLCode . ", '" 
							. _('Act Transfer') . ' - ' . $PaymentItem->Narrative . "',
							" . $_SESSION['PaymentDetail']->ExRate . " ,
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							'" . $_SESSION['PaymentDetail']->PaymentType . "',
							" . $PaymentItem->Amount . ",
							'" . $_SESSION['PaymentDetail']->Currency . "'
						)";
   				$ErrMsg = _('Cannot insert a bank transaction because');
				$DbgMsg =  _('Cannot insert a bank transaction with the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			}
   	}
  } else {
	  /*Its a supplier payment type 22 */
   	$CreditorTotal = (($_SESSION['PaymentDetail']->Discount + $_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate);
		
		$TransNo = GetNextTransNo(22, $db);
		$TransType = 22;

		/* Create a SuppTrans entry for the supplier payment */
		$SQL = "INSERT INTO supptrans (transno,
						type,
						supplierno,
						trandate,
						suppreference,
						rate,
						ovamount,
						transtext) ";
		$SQL = $SQL . "VALUES (" . $TransNo . ",
					22,
					'" . $_SESSION["PaymentDetail"]->SupplierID . "',
					'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
					'" . $_SESSION['PaymentDetail']->PaymentType . "',
					" . $_SESSION['PaymentDetail']->ExRate . ",
					" . (-$_SESSION['PaymentDetail']->Amount-$_SESSION['PaymentDetail']->Discount) . ",
					'" . $_SESSION['PaymentDetail']->Narrative . "'
				)";

		$ErrMsg =  _('Cannot insert a payment transaction against the supplier because');
		$DbgMsg = _('Cannot insert a payment transaction against the supplier using the SQL');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*Update the supplier master with the date and amount of the last payment made */
		$SQL = "UPDATE suppliers SET
		    lastpaiddate = '" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
		    lastpaid=" . $_SESSION['PaymentDetail']->Amount ."
			  WHERE suppliers.supplierid='" . $_SESSION['PaymentDetail']->SupplierID . "'";



		$ErrMsg = _('Cannot update the supplier record for the date of the last payment made because');
		$DbgMsg = _('Cannot update the supplier record for the date of the last payment made using the SQL');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$_SESSION['PaymentDetail']->Narrative = $_SESSION['PaymentDetail']->SupplierID . "-" . $_SESSION['PaymentDetail']->Narrative;

		if ($_SESSION['CompanyRecord']['gllink_creditors']==1){ /* then do the supplier control GLTrans */
	      /* Now debit creditors account with payment + discount */
			
			$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount) ";
			$SQL=$SQL . "VALUES (
						22,
						" . $TransNo . ",
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $_SESSION['CompanyRecord']['creditorsact'] . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . $CreditorTotal . "
					)";
			$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
			$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			if ($_SESSION["PaymentDetail"]->Discount !=0){
				/* Now credit Discount received account with discounts */
				$SQL="INSERT INTO gltrans ( type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount) ";
				$SQL=$SQL . "VALUES (22,
						" . $TransNo . ",
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $_SESSION['CompanyRecord']["pytdiscountact"] . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . (-$_SESSION['PaymentDetail']->Discount/$_SESSION['PaymentDetail']->ExRate) . "
					  )";
				$ErrMsg = _('Cannot insert a GL transaction for the payment discount credit because');
				$DbgMsg = _('Cannot insert a GL transaction for the payment discount credit using the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} // end if discount
		} // end if gl creditors
	} // end if supplier
  
	if ($_SESSION['CompanyRecord']["gllink_creditors"]==1){ /* then do the common GLTrans */

	  if ($_SESSION['PaymentDetail']->Amount/$_SESSION['PaymentDetail']->ExRate !=0){
		  /* Bank account entry first */
		  $SQL = "INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount) ";
		  $SQL = $SQL . "VALUES (" . $TransType . ",
						" . $TransNo . ",
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $_SESSION['PaymentDetail']->Account . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . (-$_SESSION['PaymentDetail']->Amount/$_SESSION['PaymentDetail']->ExRate) . "
					)";

		  $ErrMsg =  _('Cannot insert a GL transaction for the bank account credit because');
		  $DbgMsg =  _('Cannot insert a GL transaction for the bank account credit using the SQL');
		  $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	  }
  }

  /*now enter the BankTrans entry */

  $SQL="INSERT INTO banktrans (transno,
					type,
					bankact,
					ref,
					exrate,
					transdate,
					banktranstype,
					amount,
					currcode) ";
  $SQL= $SQL . "VALUES (" . $TransNo . ",
				" . $TransType . ",
				" . $_SESSION['PaymentDetail']->Account . ",
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . $_SESSION['PaymentDetail']->ExRate . " ,
				'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				'" . $_SESSION['PaymentDetail']->PaymentType . "',
				" . -$_SESSION['PaymentDetail']->Amount . ",
				'" . $_SESSION['PaymentDetail']->Currency . "'
			)";

	$ErrMsg = _('Cannot insert a bank transaction because');
	$DbgMsg = _('Cannot insert a bank transaction using the SQL');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

  $SQL = "COMMIT";
  $ErrMsg = _('Cannot commit the changes because');
	$DbgMsg = _('The commit of the database transaction failed');
	$result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	prnMsg(_('Payment') . ' ' . $TransNo . ' ' . _('has been sucessfully entered'),'success');

	$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);

	unset($_POST['BankAccount']);
	unset($_POST['DatePaid']);
	unset($_POST['ExRate']);
	unset($_POST['PaymentType']);
	unset($_POST['Currency']);
	unset($_POST['Narrative']);
	unset($_POST['Amount']);
	unset($_POST['Discount']);
	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);

  /*Set up a newy in case user wishes to enter another */
	echo '<BR><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Enter a General Ledger Payment') . '</A>';
	echo '<BR><A HREF="' . $rootpath . '/SelectSupplier.php?' . SID . '">' . _('Enter a Supplier Payment') . '</A>';
  }

  include('includes/footer.inc');
  exit;

} elseif (isset($_GET['Delete'])){
  /* User hit delete the receipt entry from the batch */
	$_SESSION['PaymentDetail']->Remove_GLItem($_GET['Delete']);
} elseif (isset($_POST['Process'])){ //user hit submit a new GL Analysis line into the payment

   if ($_POST['GLManualCode']!="" AND is_numeric($_POST['GLManualCode'])){

	$SQL = "SELECT accountname
			FROM chartmaster
			WHERE accountcode=" . $_POST['GLManualCode'];

	$Result=DB_query($SQL,$db);

	if (DB_num_rows($Result)==0){
		prnMsg( _('The manual GL code entered does not exist in the database') . ' - ' . _('so this GL analysis item could not be added'),'warn');
		unset($_POST['GLManualCode']);
	} else {
		$myrow = DB_fetch_array($Result);
		$_SESSION['PaymentDetail']->add_to_glanalysis($_POST['GLAmount'],
								$_POST['GLNarrative'],
								$_POST['GLManualCode'],
								$myrow['accountname']);
	}
   } else {
   	$SQL = "SELECT accountname FROM chartmaster WHERE accountcode=" . $_POST['GLCode'];
	$Result=DB_query($SQL,$db);
	$myrow=DB_fetch_array($Result);
   	$_SESSION['PaymentDetail']->add_to_glanalysis($_POST['GLAmount'],
							$_POST['GLNarrative'],
							$_POST['GLCode'],
							$myrow['accountname']);
   }

   /*Make sure the same receipt is not double processed by a page refresh */
   $Cancel = 1;
}

if (isset($Cancel)){
   unset($_POST['GLAmount']);
   unset($_POST['GLNarrative']);
   unset($_POST['GLCode']);
   unset($_POST['AccountName']);
}

/*set up the form whatever */
if ($_POST['DatePaid']=="" OR !Is_Date($_SESSION['PaymentDetail']->DatePaid)){
	 $_POST['DatePaid']= Date($_SESSION['DefaultDateFormat']);
	 $_SESSION['PaymentDetail']->DatePaid = $_POST['DatePaid'];
}

if ($_SESSION['PaymentDetail']->Currency=="" AND $_SESSION['PaymentDetail']->SupplierID==""){
	$_SESSION['PaymentDetail']->Currency=$_SESSION['CompanyRecord']['currencydefault'];
}


if (isset($_POST['BankAccount']) AND $_POST['BankAccount']!='') {
	$SQL = "SELECT bankaccountname
			FROM bankaccounts,
				chartmaster
		WHERE bankaccounts.accountcode= chartmaster.accountcode
		AND chartmaster.accountcode=" . $_POST['BankAccount'];

	$ErrMsg = _('The bank account name cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the bank account name was');

	$result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($result)==1){
		$myrow = DB_fetch_row($result);
		$_SESSION['PaymentDetail']->BankAccountName = $myrow[0];
		unset($result);
	} elseif (DB_num_rows($result)==0){
		prnMsg( _('The bank account number') . ' ' . $_POST['BankAccount'] . ' ' . _('is not set up as a bank account with a valid general ledger account'),'error');
	}
}


echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . ' METHOD=POST>';


echo '<FONT SIZE=3 COLOR=BLUE>' . _('Payment');

if ($_SESSION['PaymentDetail']->SupplierID!=""){
	echo ' ' . _('to') . ' ' . $_SESSION['PaymentDetail']->SuppName;
}

if ($_SESSION['PaymentDetail']->BankAccountName!=""){
	echo ' ' . _('from the') . ' ' . $_SESSION['PaymentDetail']->BankAccountName;
}

echo ' ' . _('on') . ' ' . $_SESSION['PaymentDetail']->DatePaid . '</FONT>';

echo '<P><TABLE>';

$SQL = "SELECT bankaccountname,
		bankaccounts.accountcode
	FROM bankaccounts,
		chartmaster
	WHERE bankaccounts.accountcode=chartmaster.accountcode";

$ErrMsg = _('The bank accounts could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve the bank acconts was');
$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

echo '<TR><TD>' . _('Bank Account') . ':</TD><TD><SELECT name="BankAccount">';

if (DB_num_rows($AccountsResults)==0){
	echo '</SELECT></TD></TR></TABLE><P>';
	prnMsg( _('Bank Accounts have not yet been defined. You must first') . ' <A HREF="' . $rootpath . '/BankAccounts.php">' . _('define the bank accounts') . '</A> ' . _('and general ledger accounts to be affected'),'warn');
	include('includes/footer.inc');
	exit;
} else {
	while ($myrow=DB_fetch_array($AccountsResults)){
	/*list the bank account names */
		if ($_POST['BankAccount']==$myrow['accountcode']){
			echo '<OPTION SELECTED VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'];
		} else {
			echo '<OPTION VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'];
		}
	}
	echo '</SELECT></TD></TR>';
}


echo '<TR><TD>' . _('Date Paid') . ':</TD>
	<TD><INPUT TYPE="text" name="DatePaid" maxlength=10 size=11 value="' . $_SESSION['PaymentDetail']->DatePaid . '"></TD>
	</TR>';


if ($_SESSION['PaymentDetail']->SupplierID==""){
	echo '<TR><TD>' . _('Currency') . ':</TD><TD><SELECT name="Currency">';
	$SQL = "SELECT currency, currabrev, rate FROM currencies";
	$result=DB_query($SQL,$db);

	if (DB_num_rows($result)==0){
		echo '</SELECT></TD></TR>';
		prnMsg( _('No currencies are defined yet') . '. ' . _('Payments cannot be entered until a currency is defined'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
		if ($_SESSION['PaymentDetail']->Currency==$myrow["currabrev"]){
			if (!isset($_POST['ExRate']) OR $_POST['ExRate']==""){
				$_POST['ExRate'] = $myrow["rate"];
			}
			echo '<OPTION SELECTED value=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		} else {
			echo '<OPTION value=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		}
		}
		echo '</SELECT></TD></TR>';
	}
} else { /*its a supplier payment so it must be in the suppliers currency */
	echo '<TR><TD>' . _('Currency') . ':</TD><TD>' . $_SESSION['PaymentDetail']->Currency . '</TD></TR>';
	/*get the default rate from the currency table if it has not been set */
	if (!isset($_POST['ExRate']) OR $_POST["ExRate"]==""){
		$SQL = "SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->Currency ."'";
		$Result=DB_query($SQL,$db);
		$myrow=DB_fetch_row($Result);
		$_POST['ExRate']=$myrow[0];
	}

}

if (!isset($_POST['ExRate'])){
	$_POST['ExRate']=1;
}
echo '<TR><TD>' . _('Exchange Rate') . ':</TD><TD><INPUT TYPE="text" name="ExRate" maxlength=10 size=12 value="' . $_POST['ExRate'] . '"></TD></TR>';
echo '<TR><TD>' . _('Payment Type') . ':</TD><TD><SELECT name=PaymentType>';

include('includes/GetPaymentMethods.php');
/* The array PaytTypes is set up in includes/GetPaymentMethods.php 
payment methods can be modified from the setup tab of the main menu under payment methods*/

foreach ($PaytTypes as $PaytType) {

if ($_POST['PaymentType']==$PaytType){
	echo '<OPTION SELECTED Value="' . $PaytType . '">' . $PaytType;
} else {
	echo '<OPTION Value="' . $PaytType . '">' . $PaytType;
}
}
echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Cheque Number') . ':</TD><TD><INPUT TYPE="text" name="ChequeNum" maxlength=8 size=10 value="' . $_POST['ChequeNum'] . '"></TD></TR>';
echo '<TR><TD>' . _('Ref') . ':</TD><TD><INPUT TYPE="text" name="Narrative" maxlength=50 size=52 value="' . $_POST['Narrative'] . '"></TD></TR>';
echo '</TABLE>';


if ($_SESSION['CompanyRecord']["gllink_creditors"]==1 AND $_SESSION['PaymentDetail']->SupplierID==""){
/* Set upthe form for the transaction entry for a GL Payment Analysis item */

	echo '<TABLE WIDTH=100% BORDER=1><TR>
			<TD class="tableheader">' . _('Amount') . '</TD>
			<TD class="tableheader">' . _('GL Account') . '</TD>
			<td class="tableheader">' . _('Narrative') . '</TD>
		</TR>';

	$PaymentTotal = 0;
   	foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
	    	    echo '<TR>
		    		<TD ALIGN=RIGHT>' . number_format($PaymentItem->Amount,2) . '</TD>
				<TD>' . $PaymentItem->GLCode . ' - ' . $PaymentItem->GLActName . '</TD>
				<TD>' . $PaymentItem->Narrative  . '</TD>
				<TD><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $PaymentItem->ID . '">' . _('Delete') . '</a></TD>
				</TR>';
	    $PaymentTotal += $PaymentItem->Amount;

   	}
   	echo '<TR><TD ALIGN=RIGHT><B>' . number_format($PaymentTotal,2) . '</B></TD></TR></TABLE>';


	echo '<BR><CENTER>' . _('General Ledger Payment Analysis Entry') . '<TABLE>';

	/*now set up a GLCode field to select from avaialble GL accounts */
	echo '<TR><TD>' . _('Enter GL Account Manually') . ':</TD>
		<TD><INPUT TYPE=Text Name="GLManualCode" Maxlength=12 SIZE=12 VALUE=' . $_POST['GLManualCode'] . '></TD></TR>';
	echo '<TR><TD>' . _('Select GL Account') . ':</TD>
		<TD><SELECT name="GLCode">';

	$SQL = "SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode";
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
	   echo '</SELECT></TD></TR>';
	   prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
		    if ($_POST['GLCode']==$myrow["accountcode"]){
			echo '<OPTION SELECTED value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		    } else {
			echo '<OPTION value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		    }
		}
		echo '</SELECT></TD></TR>';
	}
	echo '<TR><TD>' . _('GL Narrative') . ':</TD><TD><INPUT TYPE="text" name="GLNarrative" maxlength=50 size=52 value="' . $_POST['GLNarrative'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Amount') . ':</TD><TD><INPUT TYPE=Text Name="GLAmount" Maxlength=12 SIZE=12 VALUE=' . $_POST['GLAmount'] . '></TD></TR>';
	echo '</TABLE>';
	echo '<CENTER><INPUT TYPE=SUBMIT name=Process value="' . _('Accept') . '"><INPUT TYPE=SUBMIT name=Cancel value="' . _('Cancel') . '"></CENTER>';

} else {
/*a supplier is selected or the GL link is not active then set out
the fields for entry of receipt amt and disc */


	echo '<TABLE><TR><TD>' . _('Amount of Payment') . ':</TD><TD><INPUT TYPE="text" name="Amount" maxlength=12 size=13 value=' . $_SESSION['PaymentDetail']->Amount . '></TD></TR>';

	if (isset($_SESSION['PaymentDetail']->SupplierID)){ /*So it is a supplier payment so show the discount entry item */
		echo '<TR><TD>' . _('Amount of Discount') . ':</TD><TD><INPUT TYPE="text" name="Discount" maxlength=12 size=13 value=' . $_SESSION['PaymentDetail']->Discount . '></TD></TR>';
		echo '<INPUT TYPE="hidden" name="SuppName" value="' . $_SESSION['PaymentDetail']->SuppName . '">';
	} else {
		echo '<INPUT TYPE="HIDDEN" NAME="discount" Value=0>';
	}
	echo '</TABLE>';

}
echo '<BR><BR><INPUT TYPE=SUBMIT NAME="CommitBatch" VALUE="' . _('Accept and Process Payment') . '">';
echo '</FORM>';

include('includes/footer.inc');
?>
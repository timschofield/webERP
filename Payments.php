<?php
/* $Revision: 1.4 $ */

$PageSecurity = 5;

include('includes/DefinePaymentClass.php');
include('includes/session.inc');

$title = _('Payment Entry');

include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');


if ($_GET['NewPayment']=='Yes'){
	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);
}

if (!isset($_SESSION['PaymentDetail'])){
	$_SESSION['PaymentDetail'] = new Payment;
	$_SESSION['PaymentDetail']->GLItemCounter = 1;
}

if (isset($_GET['SupplierID'])){
	/*The page was called with a supplierID check it is valid and default the inputs for Supplier Name and currency of payment */

	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);
	$_SESSION['PaymentDetail'] = new Payment;
	$_SESSION['PaymentDetail']->GLItemCounter = 1;


	$SQL= "SELECT SuppName,
			CurrCode
		FROM Suppliers
		WHERE SupplierID='" . $_GET['SupplierID'] . "'";

	$Result = DB_query($SQL, $db);
	if (DB_num_rows($Result)==0){
		prnMsg( _('The supplier code that this payment page was called with is not a currently defined supplier code') . '. ' . _('If this page is called from the SelectSupplier page then this assures that a valid supplier is selected'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow = DB_fetch_array($Result);
		$_SESSION['PaymentDetail']->SuppName = $myrow['SuppName'];
		$_SESSION['PaymentDetail']->SupplierID = $_GET['SupplierID'];
		$_SESSION['PaymentDetail']->Currency = $myrow['CurrCode'];
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
	$_SESSION['PaymentDetail']->Amount=0;
}
if ($_POST['Discount']!=""){
	$_SESSION['PaymentDetail']->Discount=$_POST['Discount'];
} else {
	$_SESSION['PaymentDetail']->Discount=0;
}

$CompanyRecord = ReadInCompanyRecord($db);

if ($CompanyRecord==0){
	prnMsg( _('The company has not yet been set up properly') . ' - ' . _('this information is needed to process the payment') . '. ' . _('Processing has been cancelled'),'error');
	include('includes/footer.inc');
	exit;
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

if ($TotalAmount==0 AND ($_SESSION["PaymentDetail"]->Discount + $_SESSION["PaymentDetail"]->Amount)/$_SESSION['PaymentDetail']->ExRate ==0){
	prnMsg( _('This payment has no amounts entered and will not be processed'),'warn');
	include('includes/footer.inc');
	exit;
}


/*Make an array of the defined bank accounts */
	$SQL = "SELECT BankAccounts.AccountCode
			FROM BankAccounts,
				ChartMaster
		WHERE BankAccounts.AccountCode=ChartMaster.AccountCode";
  	$result = DB_query($SQL,$db);
  	$BankAccounts = array();
  	$i=0;
  	while ($Act = DB_fetch_row($result)){
		$BankAccounts[$i]= $Act[0];
		$i++;
  	}

  	$PeriodNo = GetPeriod($_SESSION['PaymentDetail']->DatePaid,$db);


     	/*Start a transaction to do the whole lot inside */
   	$SQL = "BEGIN";
   	$result = DB_query($SQL,$db);


   	if ($_SESSION["PaymentDetail"]->SupplierID=="") {

	/*its a nominal bank transaction type 1 */

		$TransNo = GetNextTransNo( 1, $db);
		$TransType = 1;

		if ($CompanyRecord["GLLink_Creditors"]==1){ /* then enter GLTrans */
			$TotalAmount=0;
			foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {

				 $SQL = "INSERT INTO GLTrans (Type,
				 				TypeNo,
								TranDate,
								PeriodNo,
								Account,
								Narrative,
								Amount) ";
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

		/*Run through the GL postings to check to see if there is a posting to another bank account (or the same one) if there is then a receipt needs to be created for this account too */

		foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {

			if (in_array($PaymentItem->GLCode, $BankAccounts)) {

				$ReceiptTransNo = GetNextTransNo( 2, $db);
				$SQL="INSERT INTO BankTrans (TransNo,
								Type,
								BankAct,
								Ref,
								ExRate,
								TransDate,
								BankTransType,
								Amount,
								CurrCode) ";
				$SQL= $SQL . "VALUES (" . $ReceiptTransNo . ",
							2,
							" . $PaymentItem->GLCode . ",
							'Act Transfer  - " . $PaymentItem->Narrative . "',
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
   		$CreditorTotal = (($_SESSION["PaymentDetail"]->Discount + $_SESSION["PaymentDetail"]->Amount)/$_SESSION['PaymentDetail']->ExRate);

		$TransNo = GetNextTransNo(22, $db);
		$TransType = 22;

		/* Create a SuppTrans entry for the supplier payment */
		$SQL = "INSERT INTO SuppTrans (TransNo,
						Type,
						SupplierNo,
						TranDate,
						SuppReference,
						Rate,
						OvAmount,
						TransText) ";
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
		$SQL = "UPDATE Suppliers SET
				LastPaidDate = '" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				LastPaid=" . $_SESSION['PaymentDetail']->Amount ."
			WHERE Suppliers.SupplierID='" . $_SESSION['PaymentDetail']->SupplierID . "'";



		$ErrMsg = _('Cannot update the supplier record for the date of the last payment made because');
		$DbgMsg = _('Cannot update the supplier record for the date of the last payment made using the SQL');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$_SESSION['PaymentDetail']->Narrative = $_SESSION['PaymentDetail']->SupplierID . "-" . $_SESSION['PaymentDetail']->Narrative;

		if ($CompanyRecord['GLLink_Creditors']==1){ /* then do the supplier control GLTrans */
	/* Now debit creditors account with payment + discount */
			$SQL="INSERT INTO GLTrans ( Type,
							TypeNo,
							TranDate,
							PeriodNo,
							Account,
							Narrative,
							Amount) ";
			$SQL=$SQL . "VALUES (
						22,
						" . $TransNo . ",
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $CompanyRecord['CreditorsAct'] . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . $CreditorTotal . "
					)";
			$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
			$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			if ($_SESSION["PaymentDetail"]->Discount !=0){
				/* Now credit Discount received account with discounts */
				$SQL="INSERT INTO GLTrans ( Type,
								TypeNo,
								TranDate,
								PeriodNo,
								Account,
								Narrative,
								Amount) ";
				$SQL=$SQL . "VALUES (22,
						" . $TransNo . ",
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $CompanyRecord["PytDiscountAct"] . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . (-$_SESSION['PaymentDetail']->Discount/$_SESSION['PaymentDetail']->ExRate) . "
					)";
				$ErrMsg = _('Cannot insert a GL transaction for the payment discount credit because');
				$DbgMsg = _('Cannot insert a GL transaction for the payment discount credit using the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		}
  	}

  	if ($CompanyRecord["GLLink_Creditors"]==1){ /* then do the common GLTrans */

		if ($_SESSION['PaymentDetail']->Amount/$_SESSION['PaymentDetail']->ExRate !=0){
			/* Bank account entry first */
			$SQL = "INSERT INTO GLTrans ( Type,
							TypeNo,
							TranDate,
							PeriodNo,
							Account,
							Narrative,
							Amount) ";
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

   	$SQL="INSERT INTO BankTrans (TransNo,
					Type,
					BankAct,
					Ref,
					ExRate,
					TransDate,
					BankTransType,
					Amount,
					CurrCode) ";
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

   	$SQL = "Commit";
   	$ErrMsg = _('Cannot commit the changes because');
	$DbgMsg = _('The commit of the database transaction failed');
	$result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	prnMsg(_('Payment') . ' ' . $TransNo . ' ' . _('has been sucessfully entered'),'success');

	$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);

	unset($_POST['BankAccount']);
	unset($_POST['DatePaid']);
	unset($_POST['ExRate']);
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

	exit;

} elseif (isset($_GET['Delete'])){
  /* User hit delete the receipt entry from the batch */
   $_SESSION['PaymentDetail']->Remove_GLItem($_GET['Delete']);
} elseif (isset($_POST['Process'])){ //user hit submit a new GL Analysis line into the payment


   if ($_POST['GLManualCode']!="" AND is_numeric($_POST['GLManualCode'])){

	$SQL = "SELECT AccountName
			FROM ChartMaster
			WHERE AccountCode=" . $_POST['GLManualCode'];

	$Result=DB_query($SQL,$db);

	if (DB_num_rows($Result)==0){
		prnMsg( _('The manual GL code entered does not exist in the database') . ' - ' . _('so this GL analysis item could not be added'),'warn');
		unset($_POST['GLManualCode']);
	} else {
		$myrow = DB_fetch_array($Result);
		$_SESSION['PaymentDetail']->add_to_glanalysis($_POST['GLAmount'],
								$_POST['GLNarrative'],
								$_POST['GLManualCode'],
								$myrow['AccountName']);
	}
   } else {
   	$SQL = "SELECT AccountName FROM ChartMaster WHERE AccountCode=" . $_POST['GLCode'];
	$Result=DB_query($SQL,$db);
	$myrow=DB_fetch_array($Result);
   	$_SESSION['PaymentDetail']->add_to_glanalysis($_POST['GLAmount'],
							$_POST['GLNarrative'],
							$_POST['GLCode'],
							$myrow['AccountName']);
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
	 $_POST['DatePaid']= Date($DefaultDateFormat);
	 $_SESSION['PaymentDetail']->DatePaid = $_POST['DatePaid'];
}

if ($_SESSION['PaymentDetail']->Currency=="" AND $_SESSION['PaymentDetail']->SupplierID==""){
/* find out what the functional currency of the company is */

	$SQL = 'SELECT CurrencyDefault FROM Companies WHERE CoyCode=1';
	$result=DB_query($SQL,$db);
	$myrow=DB_fetch_row($result);
	$_SESSION['PaymentDetail']->Currency=$myrow[0];
	unset($result);
}


if (isset($_POST['BankAccount']) AND $_POST['BankAccount']!='') {
	$SQL = "SELECT BankAccountName
			FROM BankAccounts,
				ChartMaster
		WHERE BankAccounts.AccountCode= ChartMaster.AccountCode
		AND ChartMaster.AccountCode=" . $_POST['BankAccount'];

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

$SQL = "SELECT BankAccountName,
		BankAccounts.AccountCode
	FROM BankAccounts,
		ChartMaster
	WHERE BankAccounts.AccountCode=ChartMaster.AccountCode";

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
		if ($_POST['BankAccount']==$myrow["AccountCode"]){
			echo '<OPTION SELECTED VALUE="' . $myrow['AccountCode'] . '">' . $myrow['BankAccountName'];
		} else {
			echo '<OPTION VALUE="' . $myrow['AccountCode'] . '">' . $myrow['BankAccountName'];
		}
	}
	echo '</SELECT></TD></TR>';
}


echo '<TR><TD>' . _('Date Paid') . ':</TD>
	<TD><INPUT TYPE="text" name="DatePaid" maxlength=10 size=11 value="' . $_SESSION['PaymentDetail']->DatePaid . '"></TD>
	</TR>';


if ($_SESSION['PaymentDetail']->SupplierID==""){
	echo '<TR><TD>' . _('Currency') . ':</TD><TD><SELECT name="Currency">';
	$SQL = "SELECT Currency, CurrAbrev, Rate FROM Currencies";
	$result=DB_query($SQL,$db);

	if (DB_num_rows($result)==0){
		echo '</SELECT></TD></TR>';
		prnMsg( _('No currencies are defined yet') . '. ' . _('Payments cannot be entered until a currency is defined'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
		if ($_SESSION['PaymentDetail']->Currency==$myrow["CurrAbrev"]){
			if (!isset($_POST['ExRate']) OR $_POST['ExRate']==""){
				$_POST['ExRate'] = $myrow["Rate"];
			}
			echo '<OPTION SELECTED value=' . $myrow['CurrAbrev'] . '>' . $myrow['Currency'];
		} else {
			echo '<OPTION value=' . $myrow['CurrAbrev'] . '>' . $myrow['Currency'];
		}
		}
		echo '</SELECT></TD></TR>';
	}
} else { /*its a supplier payment so it must be in the suppliers currency */
	echo '<TR><TD>' . _('Currency') . ':</TD><TD>' . $_SESSION['PaymentDetail']->Currency . '</TD></TR>';
	/*get the default rate from the currency table if it has not been set */
	if (!isset($_POST['ExRate']) OR $_POST["ExRate"]==""){
		$SQL = "SELECT Rate FROM Currencies WHERE CurrAbrev='" . $_SESSION['PaymentDetail']->Currency ."'";
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

/* The array PaytTypes is set up in config.php for user modification
payment types can be modified by editing that file */

foreach ($PaytTypes as $PaytType) {

if ($_POST['PaymentType']==$PaytType){
	echo '<OPTION SELECTED Value="' . $PaytType . '">' . $PaytType;
} else {
	echo '<OPTION Value="' . $PaytType . '">' . $PaytType;
}
}
echo '</SELECT></TD></TR>';


echo '<TR><TD>' . _('Ref') . ':</TD><TD><INPUT TYPE="text" name="Narrative" maxlength=50 size=52 value="' . $_POST['Narrative'] . '"></TD></TR>';
echo '</TABLE>';


if ($CompanyRecord["GLLink_Creditors"]==1 AND $_SESSION['PaymentDetail']->SupplierID==""){
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
				<TD><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . 'Delete=' . $PaymentItem->ID . '">' . _('Delete') . '</a></TD>
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

	$SQL = "SELECT AccountCode, AccountName FROM ChartMaster ORDER BY AccountCode";
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
	   echo '</SELECT></TD></TR>';
	   prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
		    if ($_POST['GLCode']==$myrow["AccountCode"]){
			echo '<OPTION SELECTED value=' . $myrow['AccountCode'] . '>' . $myrow['AccountCode'] . ' - ' . $myrow['AccountName'];
		    } else {
			echo '<OPTION value=' . $myrow['AccountCode'] . '>' . $myrow['AccountCode'] . ' - ' . $myrow['AccountName'];
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
	} else {
		echo '<INPUT TYPE="HIDDEN" NAME="discount" Value=0>';
	}
	echo '</TABLE>';

}
echo '<BR><BR><INPUT TYPE=SUBMIT NAME="CommitBatch" VALUE="' . _('Accept and Process Payment') . '">';
echo '</FORM>';
include('includes/footer.inc');
?>

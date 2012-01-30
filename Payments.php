<?php

/* $Id$*/

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
if (empty($_GET['identifier'])){
	/*unique session identifier to ensure that there is no conflict with other order enty session on the same machine */
	$identifier=date('U');
}else{
	$identifier=$_GET['identifier'];//edit GLItems
}
if (isset($_GET['NewPayment']) AND $_GET['NewPayment']=='Yes'){
	unset($_SESSION['PaymentDetail' . $identifier]->GLItems);
	unset($_SESSION['PaymentDetail' . $identifier]);
}

if (!isset($_SESSION['PaymentDetail' . $identifier])){
	$_SESSION['PaymentDetail' . $identifier] = new Payment;
	$_SESSION['PaymentDetail' . $identifier]->GLItemCounter = 1;
}

if ((isset($_POST['UpdateHeader']) 
	AND $_POST['BankAccount']=='') 
	OR (isset($_POST['Process']) AND $_POST['BankAccount']=='')) {
		
	prnMsg(_('A bank account must be selected to make this payment from'), 'warn');
	$BankAccountEmpty=true;
} else {
	
	$BankAccountEmpty=false;
}

echo '<p class="page_title_text">
		<img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Payment Entry')
. '" alt="" />' . ' ' . _('Payment Entry') . '
	</p>';
echo '<div class="page_help_text">' . _('Use this screen to enter payments FROM your bank account.  <br />Note: To enter a payment FROM a supplier, first select the Supplier, click Enter a Payment to, or Receipt from the Supplier, and use a negative Payment amount on this form.') . '</div>
	<br />
	<div class="centre">';

if (isset($_GET['SupplierID'])){
	/*The page was called with a supplierID check it is valid and default the inputs for Supplier Name and currency of payment */

	unset($_SESSION['PaymentDetail' . $identifier]->GLItems);
	unset($_SESSION['PaymentDetail' . $identifier]);
	$_SESSION['PaymentDetail' . $identifier] = new Payment;
	$_SESSION['PaymentDetail' . $identifier]->GLItemCounter = 1;


	$SQL= "SELECT suppname,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6,
				currcode,
				factorcompanyid
			FROM suppliers 
			WHERE supplierid='" . $_GET['SupplierID'] . "'";

	$Result = DB_query($SQL, $db);
	if (DB_num_rows($Result)==0){
		prnMsg( _('The supplier code that this payment page was called with is not a currently defined supplier code') . '. ' . _('If this page is called from the selectSupplier page then this assures that a valid supplier is selected'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow = DB_fetch_array($Result);
		if ($myrow['factorcompanyid'] == 0) {
			$_SESSION['PaymentDetail' . $identifier]->SuppName = $myrow['suppname'];
			$_SESSION['PaymentDetail' . $identifier]->Address1 = $myrow['address1'];
			$_SESSION['PaymentDetail' . $identifier]->Address2 = $myrow['address2'];
			$_SESSION['PaymentDetail' . $identifier]->Address3 = $myrow['address3'];
			$_SESSION['PaymentDetail' . $identifier]->Address4 = $myrow['address4'];
			$_SESSION['PaymentDetail' . $identifier]->Address5 = $myrow['address5'];
			$_SESSION['PaymentDetail' . $identifier]->Address6 = $myrow['address6'];
			$_SESSION['PaymentDetail' . $identifier]->SupplierID = $_GET['SupplierID'];
			$_SESSION['PaymentDetail' . $identifier]->Currency = $myrow['currcode'];
			$_POST['Currency'] = $_SESSION['PaymentDetail' . $identifier]->Currency;
			
		} else {
			$factorsql= "SELECT coyname,
			 					address1,
			 					address2,
			 					address3,
			 					address4,
			 					address5,
			 					address6
							FROM factorcompanies
							WHERE id='" . $myrow['factorcompanyid'] . "'";

			$FactorResult = DB_query($factorsql, $db);
			$myfactorrow = DB_fetch_array($FactorResult);
			$_SESSION['PaymentDetail' . $identifier]->SuppName = $myrow['suppname'] . ' ' . _('care of') . ' ' . $myfactorrow['coyname'];
			$_SESSION['PaymentDetail' . $identifier]->Address1 = $myfactorrow['address1'];
			$_SESSION['PaymentDetail' . $identifier]->Address2 = $myfactorrow['address2'];
			$_SESSION['PaymentDetail' . $identifier]->Address3 = $myfactorrow['address3'];
			$_SESSION['PaymentDetail' . $identifier]->Address4 = $myfactorrow['address4'];
			$_SESSION['PaymentDetail' . $identifier]->Address5 = $myfactorrow['address5'];
			$_SESSION['PaymentDetail' . $identifier]->Address6 = $myfactorrow['address6'];
			$_SESSION['PaymentDetail' . $identifier]->SupplierID = $_GET['SupplierID'];
			$_SESSION['PaymentDetail' . $identifier]->Currency = $myrow['currcode'];
			$_POST['Currency'] = $_SESSION['PaymentDetail' . $identifier]->Currency;
		}
	}
}

if (isset($_POST['BankAccount']) AND $_POST['BankAccount']!=''){
	
	$_SESSION['PaymentDetail' . $identifier]->Account=$_POST['BankAccount'];
	/*Get the bank account currency and set that too */
	$ErrMsg = _('Could not get the currency of the bank account');
	$result = DB_query("SELECT currcode, 
								decimalplaces 
						FROM bankaccounts INNER JOIN currencies
						ON bankaccounts.currcode = currencies.currabrev
						WHERE accountcode ='" . $_POST['BankAccount'] . "'",
						$db,
						$ErrMsg);
						
	$myrow = DB_fetch_array($result);
	$_SESSION['PaymentDetail' . $identifier]->AccountCurrency=$myrow['currcode'];
	$_SESSION['PaymentDetail' . $identifier]->CurrDecimalPlaces=$myrow['decimalplaces'];

} else {
	$_SESSION['PaymentDetail' . $identifier]->AccountCurrency =$_SESSION['CompanyRecord']['currencydefault'];
	$_SESSION['PaymentDetail' . $identifier]->CurrDecimalPlaces=$_SESSION['CompanyRecord']['decimalplaces'];
}
if (isset($_POST['DatePaid']) AND $_POST['DatePaid']!='' AND Is_Date($_POST['DatePaid'])){
	$_SESSION['PaymentDetail' . $identifier]->DatePaid=$_POST['DatePaid'];
}
if (isset($_POST['ExRate']) AND $_POST['ExRate']!=''){
	$_SESSION['PaymentDetail' . $identifier]->ExRate=filter_number_format($_POST['ExRate']); //ex rate between payment currency and account currency
}
if (isset($_POST['FunctionalExRate']) AND $_POST['FunctionalExRate']!=''){
	$_SESSION['PaymentDetail' . $identifier]->FunctionalExRate=filter_number_format($_POST['FunctionalExRate']); //ex rate between payment currency and account currency
}
if (isset($_POST['Paymenttype']) AND $_POST['Paymenttype']!=''){
	$_SESSION['PaymentDetail' . $identifier]->Paymenttype = $_POST['Paymenttype'];
}

if (isset($_POST['Currency']) AND $_POST['Currency']!=''){
	$_SESSION['PaymentDetail' . $identifier]->Currency=$_POST['Currency']; //payment currency
	/*Get the exchange rate between the functional currency and the payment currency*/
	$result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail' . $identifier]->Currency . "'",$db);
	$myrow = DB_fetch_row($result);
	$tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency

	if ($_POST['Currency']==$_SESSION['PaymentDetail' . $identifier]->AccountCurrency){
		$_POST['ExRate']=1;
		$_SESSION['PaymentDetail' . $identifier]->ExRate=filter_number_format($_POST['ExRate']); //ex rate between payment currency and account currency
		$SuggestedExRate=1;
	}
	if ($_SESSION['PaymentDetail' . $identifier]->AccountCurrency==$_SESSION['CompanyRecord']['currencydefault']){
		$_POST['FunctionalExRate']=1;
		$_SESSION['PaymentDetail' . $identifier]->FunctionalExRate=filter_number_format($_POST['FunctionalExRate']);
		$SuggestedExRate = $tableExRate;
		$SuggestedFunctionalExRate =1;

	} else {
		/*To illustrate the rates required
			Take an example functional currency NZD payment in USD from an AUD bank account
			1 NZD = 0.80 USD
			1 NZD = 0.90 AUD
			The FunctionalExRate = 0.90 - the rate between the functional currency and the bank account currency
			The payment ex rate is the rate at which one can purchase the payment currency in the bank account currency
			or 0.8/0.9 = 0.88889
		*/

		/*Get suggested FunctionalExRate */
		$result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail' . $identifier]->AccountCurrency . "'",$db);
		$myrow = DB_fetch_row($result);
		$SuggestedFunctionalExRate = $myrow[0];

		/*Get the exchange rate between the functional currency and the payment currency*/
		$result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail' . $identifier]->Currency . "'",$db);
		$myrow = DB_fetch_row($result);
		$tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
		/*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
		$SuggestedExRate = $tableExRate/$SuggestedFunctionalExRate;

	}
}


if (isset($_POST['Narrative']) AND $_POST['Narrative']!=''){
	$_SESSION['PaymentDetail' . $identifier]->Narrative=$_POST['Narrative'];
}
if (isset($_POST['Amount']) AND $_POST['Amount']!=''){
	$_SESSION['PaymentDetail' . $identifier]->Amount=filter_number_format($_POST['Amount']);
} else {
	if (!isset($_SESSION['PaymentDetail' . $identifier]->Amount)) {
		$_SESSION['PaymentDetail' . $identifier]->Amount=0;
	}
}
if (isset($_POST['Discount']) AND $_POST['Discount']!=''){
	$_SESSION['PaymentDetail' . $identifier]->Discount=filter_number_format($_POST['Discount']);
} else {
	if (!isset($_SESSION['PaymentDetail' . $identifier]->Discount)) {
	  $_SESSION['PaymentDetail' . $identifier]->Discount=0;
  }
}


if (isset($_POST['CommitBatch'])){

  /* once the GL analysis of the payment is entered (if the Creditors_GLLink is active),
  process all the data in the session cookie into the DB creating a banktrans record for
  the payment in the batch and SuppTrans record for the supplier payment if a supplier was selected
  A GL entry is created for each GL entry (only one for a supplier entry) and one for the bank
  account credit.

  NB allocations against supplier payments are a separate exercice

  if GL integrated then
  first off run through the array of payment items $_SESSION['Payment']->GLItems and
  create GL Entries for the GL payment items
  */

	/*First off  check we have an amount entered as paid ?? */
	$TotalAmount =0;
	foreach ($_SESSION['PaymentDetail' . $identifier]->GLItems AS $PaymentItem) {
		$TotalAmount += $PaymentItem->Amount;
	}

	if ($TotalAmount==0 AND
		($_SESSION['PaymentDetail' . $identifier]->Discount + $_SESSION['PaymentDetail' . $identifier]->Amount)/$_SESSION['PaymentDetail' . $identifier]->ExRate ==0){
		prnMsg( _('This payment has no amounts entered and will not be processed'),'warn');
		include('includes/footer.inc');
		exit;
	}

	if ($_POST['BankAccount']=='') {
		prnMsg( _('No bank account has been selected so this payment cannot be processed'),'warn');
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

	$PeriodNo = GetPeriod($_SESSION['PaymentDetail' . $identifier]->DatePaid,$db);

	$sql="SELECT usepreprintedstationery
			FROM paymentmethods
			WHERE paymentname='" . $_SESSION['PaymentDetail' . $identifier]->Paymenttype ."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	// first time through commit if supplier cheque then print it first
	if ((!isset($_POST['ChequePrinted']))
		AND (!isset($_POST['PaymentCancelled']))
		AND ($myrow[0] == 1)) {
	// it is a supplier payment by cheque and haven't printed yet so print cheque

		echo '<br />
			<a href="' . $rootpath . '/PrintCheque.php?ChequeNum=' . $_POST['ChequeNum'] . '">' . _('Print Cheque using pre-printed stationery') . '</a>
			<br />
			<br />';

		echo '<form method=post action="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?identifier=' . $identifier) . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo _('Has the cheque been printed') . '?
			<br />
			<br />
			<input type="hidden" name="CommitBatch" value="' . $_POST['CommitBatch'] . '" />
			<input type="hidden" name="BankAccount" value="' . $_POST['BankAccount'] . '" />
			<input type="submit" name="ChequePrinted" value="' . _('Yes / Continue') . '" />&nbsp;&nbsp;
			<input type="submit" name="PaymentCancelled" value="' . _('No / Cancel Payment') . '" />';
			
		echo '<br />Payment amount = ' . $_SESSION['PaymentDetail' . $identifier]->Amount;
		
	} else {

		//Start a transaction to do the whole lot inside
		
		$result = DB_Txn_Begin($db);


		if ($_SESSION['PaymentDetail' . $identifier]->SupplierID=='') {

		//its a nominal bank transaction type 1

			$TransNo = GetNextTransNo( 1, $db);
			$TransType = 1;

			if ($_SESSION['CompanyRecord']['gllink_creditors']==1){ /* then enter GLTrans */
				$TotalAmount=0;
				foreach ($_SESSION['PaymentDetail' . $identifier]->GLItems as $PaymentItem) {

					 /*The functional currency amount will be the
					 payment currenct amount  / the bank account currency exchange rate  - to get to the bank account currency
					 then / the functional currency exchange rate to get to the functional currency */
					if ($PaymentItem->Cheque=='') $PaymentItem->Cheque=0;
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount,
												chequeno,
												tag) ";
				 	$SQL= $SQL . "VALUES (1,
						'" . $TransNo . "',
						'" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
						'" . $PeriodNo . "',
						'" . $PaymentItem->GLCode . "',
						'" . $PaymentItem->Narrative . "',
						'" . ($PaymentItem->Amount/$_SESSION['PaymentDetail' . $identifier]->ExRate/$_SESSION['PaymentDetail' . $identifier]->FunctionalExRate) . "',
						'". $PaymentItem->Cheque ."',
						'" . $PaymentItem->Tag . "'
						)";
					$ErrMsg = _('Cannot insert a GL entry for the payment using the SQL');
					$result = DB_query($SQL,$db,$ErrMsg,_('The SQL that failed was'),true);

					$TotalAmount += $PaymentItem->Amount;
				}
				$_SESSION['PaymentDetail' . $identifier]->Amount = $TotalAmount;
				$_SESSION['PaymentDetail' . $identifier]->Discount=0;
			}

			//Run through the GL postings to check to see if there is a posting to another bank account (or the same one) if there is then a receipt needs to be created for this account too

			foreach ($_SESSION['PaymentDetail' . $identifier]->GLItems as $PaymentItem) {

				if (in_array($PaymentItem->GLCode, $BankAccounts)) {

					/*Need to deal with the case where the payment from one bank account could be to a bank account in another currency */

					/*Get the currency and rate of the bank account transferring to*/
					$SQL = "SELECT currcode, rate
							FROM bankaccounts INNER JOIN currencies
							ON bankaccounts.currcode = currencies.currabrev
							WHERE accountcode='" . $PaymentItem->GLCode . "'";
					$TrfToAccountResult = DB_query($SQL,$db);
					$TrfToBankRow = DB_fetch_array($TrfToAccountResult) ;
					$TrfToBankCurrCode = $TrfToBankRow['currcode'];
					$TrfToBankExRate = $TrfToBankRow['rate'];

					if ($_SESSION['PaymentDetail' . $identifier]->AccountCurrency == $TrfToBankCurrCode){
					/*Make sure to use the same rate if the transfer is between two bank accounts in the same currency */
						$TrfToBankExRate = $_SESSION['PaymentDetail' . $identifier]->FunctionalExRate;
					}

					/*Consider an example
					 functional currency NZD
					 bank account in AUD - 1 NZD = 0.90 AUD (FunctionalExRate)
					 paying USD - 1 AUD = 0.85 USD  (ExRate)
					 to a bank account in EUR - 1 NZD = 0.52 EUR

					 oh yeah - now we are getting tricky!
					 Lets say we pay USD 100 from the AUD bank account to the EUR bank account

					 To get the ExRate for the bank account we are transferring money to
					 we need to use the cross rate between the NZD-AUD/NZD-EUR
					 and apply this to the

					 the payment record will read
					 exrate = 0.85 (1 AUD = USD 0.85)
					 amount = 100 (USD)
					 functionalexrate = 0.90 (1 NZD = AUD 0.90)

					 the receipt record will read

					 amount 100 (USD)
					 exrate    (1 EUR =  (0.85 x 0.90)/0.52 USD)
					 					(ExRate x FunctionalExRate) / USD Functional ExRate
					 functionalexrate =     (1NZD = EUR 0.52)

				*/

					$ReceiptTransNo = GetNextTransNo( 2, $db);
					$SQL= "INSERT INTO banktrans (transno,
													type,
													bankact,
													ref,
													exrate,
													functionalexrate,
													transdate,
													banktranstype,
													amount,
													currcode)
						VALUES ('" . $ReceiptTransNo . "',
							2,
							'" . $PaymentItem->GLCode . "',
							'" . _('Act Transfer From ') . $_SESSION['PaymentDetail' . $identifier]->Account . ' - ' . $PaymentItem->Narrative . "',
							'" . (($_SESSION['PaymentDetail' . $identifier]->ExRate * $_SESSION['PaymentDetail' . $identifier]->FunctionalExRate)/$TrfToBankExRate). "',
							'" . $TrfToBankExRate . "',
							'" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
							'" . $_SESSION['PaymentDetail' . $identifier]->Paymenttype . "',
							'" . $PaymentItem->Amount . "',
							'" . $_SESSION['PaymentDetail' . $identifier]->Currency . "'
						)";
					$ErrMsg = _('Cannot insert a bank transaction because');
					$DbgMsg =  _('Cannot insert a bank transaction with the SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				}
			}
		} else {
	  /*Its a supplier payment type 22 */
			$CreditorTotal = (($_SESSION['PaymentDetail' . $identifier]->Discount + $_SESSION['PaymentDetail' . $identifier]->Amount)/$_SESSION['PaymentDetail' . $identifier]->ExRate)/$_SESSION['PaymentDetail' . $identifier]->FunctionalExRate;

			$TransNo = GetNextTransNo(22, $db);
			$TransType = 22;

			/* Create a SuppTrans entry for the supplier payment */
			$SQL = "INSERT INTO supptrans (transno,
											type,
											supplierno,
											trandate,
											inputdate,
											suppreference,
											rate,
											ovamount,
											transtext) ";
			$SQL = $SQL . "valueS ('" . $TransNo . "',
					22,
					'" . $_SESSION['PaymentDetail' . $identifier]->SupplierID . "',
					'" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
					'" . date('Y-m-d H-i-s') . "',
					'" . $_SESSION['PaymentDetail' . $identifier]->Paymenttype . "',
					'" . ($_SESSION['PaymentDetail' . $identifier]->FunctionalExRate/$_SESSION['PaymentDetail' . $identifier]->ExRate) . "',
					'" . (-$_SESSION['PaymentDetail' . $identifier]->Amount-$_SESSION['PaymentDetail' . $identifier]->Discount) . "',
					'" . $_SESSION['PaymentDetail' . $identifier]->Narrative . "'
				)";

			$ErrMsg =  _('Cannot insert a payment transaction against the supplier because');
			$DbgMsg = _('Cannot insert a payment transaction against the supplier using the SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			/*Update the supplier master with the date and amount of the last payment made */
			$SQL = "UPDATE suppliers 
					SET	lastpaiddate = '" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
						lastpaid='" . $_SESSION['PaymentDetail' . $identifier]->Amount ."'
					WHERE suppliers.supplierid='" . $_SESSION['PaymentDetail' . $identifier]->SupplierID . "'";



			$ErrMsg = _('Cannot update the supplier record for the date of the last payment made because');
			$DbgMsg = _('Cannot update the supplier record for the date of the last payment made using the SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$_SESSION['PaymentDetail' . $identifier]->Narrative = $_SESSION['PaymentDetail' . $identifier]->SupplierID . '-' . $_SESSION['PaymentDetail' . $identifier]->Narrative;

			if ($_SESSION['CompanyRecord']['gllink_creditors']==1){ /* then do the supplier control GLTrans */
			/* Now debit creditors account with payment + discount */

				$SQL="INSERT INTO gltrans ( type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount) ";
				$SQL=$SQL . "VALUES (22,
								'" . $TransNo . "',
								'" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
								'" . $PeriodNo . "',
								'" . $_SESSION['CompanyRecord']['creditorsact'] . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->Narrative . "',
								'" . $CreditorTotal . "')";
				$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
				$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				if ($_SESSION['PaymentDetail' . $identifier]->Discount !=0){
					/* Now credit Discount received account with discounts */
					$SQL="INSERT INTO gltrans ( type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount) 
								VALUES (22,
										'" . $TransNo . "',
										'" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['pytdiscountact'] . "',
										'" . $_SESSION['PaymentDetail' . $identifier]->Narrative . "',
										'" . (-$_SESSION['PaymentDetail' . $identifier]->Discount/$_SESSION['PaymentDetail' . $identifier]->ExRate/$_SESSION['PaymentDetail' . $identifier]->FunctionalExRate) . "'
					  )";
					$ErrMsg = _('Cannot insert a GL transaction for the payment discount credit because');
					$DbgMsg = _('Cannot insert a GL transaction for the payment discount credit using the SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount
				
			} // end if gl creditors
		} // end if supplier

		if ($_SESSION['CompanyRecord']['gllink_creditors']==1){ /* then do the common GLTrans */

			if ($_SESSION['PaymentDetail' . $identifier]->Amount !=0){
				/* Bank account entry first */
				$SQL = "INSERT INTO gltrans ( type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount) 
						VALUES ('" . $TransType . "',
								'" . $TransNo . "',
								'" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
								'" . $PeriodNo . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->Account . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->Narrative . "',
								'" . (-$_SESSION['PaymentDetail' . $identifier]->Amount/$_SESSION['PaymentDetail' . $identifier]->ExRate/$_SESSION['PaymentDetail' . $identifier]->FunctionalExRate) . "')";

				$ErrMsg =  _('Cannot insert a GL transaction for the bank account credit because');
				$DbgMsg =  _('Cannot insert a GL transaction for the bank account credit using the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				EnsureGLEntriesBalance($TransType,$TransNo,$db);
			}
		}

		/*now enter the BankTrans entry */
		if ($TransType==22) {
			$SQL="INSERT INTO banktrans (transno,
										type,
										bankact,
										ref,
										exrate,
										functionalexrate,
										transdate,
										banktranstype,
										amount,
										currcode) 
							VALUES ('" . $TransNo . "',
									'" . $TransType . "',
									'" . $_SESSION['PaymentDetail' . $identifier]->Account . "',
									'" . $_SESSION['PaymentDetail' . $identifier]->Narrative . "',
									'" . $_SESSION['PaymentDetail' . $identifier]->ExRate . "',
									'" . $_SESSION['PaymentDetail' . $identifier]->FunctionalExRate . "',
									'" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
									'" . $_SESSION['PaymentDetail' . $identifier]->Paymenttype . "',
									'" . -$_SESSION['PaymentDetail' . $identifier]->Amount . "',
									'" . $_SESSION['PaymentDetail' . $identifier]->Currency . "'
								)";

			$ErrMsg = _('Cannot insert a bank transaction because');
			$DbgMsg = _('Cannot insert a bank transaction using the SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} else {
			foreach ($_SESSION['PaymentDetail' . $identifier]->GLItems as $PaymentItem) {
				$SQL="INSERT INTO banktrans (transno,
											type,
											bankact,
											ref,
											exrate,
											functionalexrate,
											transdate,
											banktranstype,
											amount,
											currcode) 
						VALUES ('" . $TransNo . "',
								'" . $TransType . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->Account . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->Narrative . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->ExRate . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->FunctionalExRate . "',
								'" . FormatDateForSQL($_SESSION['PaymentDetail' . $identifier]->DatePaid) . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->Paymenttype . "',
								'" . -$PaymentItem->Amount . "',
								'" . $_SESSION['PaymentDetail' . $identifier]->Currency . "' )";

				$ErrMsg = _('Cannot insert a bank transaction because');
				$DbgMsg = _('Cannot insert a bank transaction using the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		}

		DB_Txn_Commit($db);
		prnMsg(_('Payment') . ' ' . $TransNo . ' ' . _('has been successfully entered'),'success');

		$LastSupplier = ($_SESSION['PaymentDetail' . $identifier]->SupplierID);

		unset($_POST['BankAccount']);
		unset($_POST['DatePaid']);
		unset($_POST['ExRate']);
		unset($_POST['Paymenttype']);
		unset($_POST['Currency']);
		unset($_POST['Narrative']);
		unset($_POST['Amount']);
		unset($_POST['Discount']);
		unset($_SESSION['PaymentDetail' . $identifier]->GLItems);
		unset($_SESSION['PaymentDetail' . $identifier]);

		/*Set up a newy in case user wishes to enter another */
		if (isset($LastSupplier) and $LastSupplier!='') {
			$sql="SELECT suppname FROM suppliers 
					WHERE supplierid='".$LastSupplier."'";
			$result=DB_query($sql, $db);
			$myrow=DB_fetch_array($result);
			echo '<br /><a href="' . $rootpath . '/Payments.php?SupplierID=' . $LastSupplier . '">' .
				_('Enter another Payment for') . ' ' . $myrow['suppname'] . '</a>';
		} else {
			echo '<br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">' . _('Enter another General Ledger Payment') . '</a><br />';
		}
	}

	include('includes/footer.inc');
	exit;

} elseif (isset($_GET['Delete'])){
  /* User hit delete the receipt entry from the batch */
	$_SESSION['PaymentDetail' . $identifier]->Remove_GLItem($_GET['Delete']);
	
} elseif (isset($_POST['Process']) AND !$BankAccountEmpty){ //user hit submit a new GL Analysis line into the payment

	$ChequeNoSQL="SELECT account FROM gltrans WHERE chequeno='" . $_POST['Cheque'] ."'";
	$ChequeNoResult=DB_query($ChequeNoSQL, $db);

	if (is_numeric($_POST['GLManualCode'])){

		$SQL = "SELECT accountname
				FROM chartmaster
				WHERE accountcode='" . $_POST['GLManualCode'] . "'";

		$Result=DB_query($SQL,$db);

		if (DB_num_rows($Result)==0){
			prnMsg( _('The manual GL code entered does not exist in the database') . ' - ' . _('so this GL analysis item could not be added'),'warn');
			unset($_POST['GLManualCode']);
		} else if (DB_num_rows($ChequeNoResult)!=0 AND $_POST['Cheque']!=''){
			prnMsg( _('The Cheque/Voucher number has already been used') . ' - ' . _('This GL analysis item could not be added'),'error');
		} else {
			$myrow = DB_fetch_array($Result);
			$_SESSION['PaymentDetail' . $identifier]->add_to_glanalysis(filter_number_format($_POST['GLAmount']),
															$_POST['GLNarrative'],
															$_POST['GLManualCode'],
															$myrow['accountname'],
															$_POST['Tag'],
															$_POST['Cheque']);
			unset($_POST['GLManualCode']);
		}
	} else if (DB_num_rows($ChequeNoResult)!=0 AND $_POST['Cheque']!=''){
		prnMsg( _('The cheque number has already been used') . ' - ' . _('This GL analysis item could not be added'),'error');
	} else if ($_POST['GLCode'] == '') {
			prnMsg( _('No General Ledger code has been chosen') . ' - ' . _('so this GL analysis item could not be added'),'warn');
	} else {
		$SQL = "SELECT accountname FROM chartmaster WHERE accountcode='" . $_POST['GLCode'] . "'";
		$Result=DB_query($SQL,$db);
		$myrow=DB_fetch_array($Result);
		$_SESSION['PaymentDetail' . $identifier]->add_to_glanalysis(filter_number_format($_POST['GLAmount']),
														$_POST['GLNarrative'],
														$_POST['GLCode'],
														$myrow['accountname'],
														$_POST['Tag'],
														$_POST['Cheque']);
	}

	/*Make sure the same receipt is not double processed by a page refresh */
	$_POST['Cancel'] = 1;
}

if (isset($_POST['Cancel'])){
	unset($_POST['GLAmount']);
	unset($_POST['GLNarrative']);
	unset($_POST['GLCode']);
	unset($_POST['AccountName']);
}

/*set up the form whatever */
if (!isset($_POST['DatePaid'])) {
	$_POST['DatePaid'] = '';
}

if (isset($_POST['DatePaid']) 
	AND ($_POST['DatePaid']=='' 
		OR !Is_Date($_SESSION['PaymentDetail' . $identifier]->DatePaid))){
			
	$_POST['DatePaid']= Date($_SESSION['DefaultDateFormat']);
	$_SESSION['PaymentDetail' . $identifier]->DatePaid = $_POST['DatePaid'];
}

if ($_SESSION['PaymentDetail' . $identifier]->Currency=='' AND $_SESSION['PaymentDetail' . $identifier]->SupplierID==''){
	$_SESSION['PaymentDetail' . $identifier]->Currency=$_SESSION['CompanyRecord']['currencydefault'];
}


if (isset($_POST['BankAccount']) AND $_POST['BankAccount']!='') {
	$SQL = "SELECT bankaccountname
			FROM bankaccounts,
				chartmaster
			WHERE bankaccounts.accountcode= chartmaster.accountcode
			AND chartmaster.accountcode='" . $_POST['BankAccount'] . "'";

	$ErrMsg = _('The bank account name cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the bank account name was');

	$result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($result)==1){
		$myrow = DB_fetch_row($result);
		$_SESSION['PaymentDetail' . $identifier]->BankAccountName = $myrow[0];
		unset($result);
	} elseif (DB_num_rows($result)==0){
		prnMsg( _('The bank account number') . ' ' . $_POST['BankAccount'] . ' ' . _('is not set up as a bank account with a valid general ledger account'),'error');
	}
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?identifier=' . $identifier) . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p><table class="selection">';

echo '<tr><th colspan="4"><font size="3" color="blue">' . _('Payment');

if ($_SESSION['PaymentDetail' . $identifier]->SupplierID!=''){
	echo ' ' . _('to') . ' ' . $_SESSION['PaymentDetail' . $identifer]->SuppName;
}

if ($_SESSION['PaymentDetail' . $identifier]->BankAccountName!=''){
	echo ' ' . _('from the') . ' ' . $_SESSION['PaymentDetail' . $identifier]->BankAccountName;
}

echo ' ' . _('on') . ' ' . $_SESSION['PaymentDetail' . $identifier]->DatePaid . '</font></th></tr>';

$SQL = "SELECT bankaccountname,
				bankaccounts.accountcode,
				bankaccounts.currcode
		FROM bankaccounts INNER JOIN chartmaster
		ON bankaccounts.accountcode=chartmaster.accountcode";

$ErrMsg = _('The bank accounts could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve the bank accounts was');
$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

echo '<tr>
		<td>' . _('Bank Account') . ':</td>
		<td><select name="BankAccount" onChange="ReloadForm(UpdateHeader)">';

if (DB_num_rows($AccountsResults)==0){
	echo '</select></td>
		</tr>
		</table>
		<p />';
	prnMsg( _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'),'warn');
	include('includes/footer.inc');
	exit;
} else {
	echo '<option value=""></option>';
	while ($myrow=DB_fetch_array($AccountsResults)){
	/*list the bank account names */
		if (isset($_POST['BankAccount']) AND $_POST['BankAccount']==$myrow['accountcode']){
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';
}

echo '<tr>
		<td>' . _('Date Paid') . ':</td>
		<td><input type="text" name="DatePaid" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" maxlength="10" size="11" onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' . $_SESSION['PaymentDetail' . $identifier]->DatePaid . '" /></td>
	</tr>';


if ($_SESSION['PaymentDetail' . $identifier]->SupplierID==''){
	echo '<tr><td>' . _('Currency of Payment') . ':</td>
			<td><select name="Currency" onChange="ReloadForm(UpdateHeader)">';
	$SQL = "SELECT currency, currabrev, rate FROM currencies";
	$result=DB_query($SQL,$db);

	if (DB_num_rows($result)==0){
		echo '</select></td>
			</tr>';
		prnMsg( _('No currencies are defined yet. Payments cannot be entered until a currency is defined'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
			if ($_SESSION['PaymentDetail' . $identifier]->Currency==$myrow['currabrev']){
				echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
			} else {
				echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
			}
		}
		echo '</select></td>
				<td><i>' . _('The transaction currency does not need to be the same as the bank account currency') . '</i></td>
			</tr>';
	}
} else { /*its a supplier payment so it must be in the suppliers currency */
	echo '<tr>
			<td>' . _('Supplier Currency') . ':</td>
			<td>' . $_SESSION['PaymentDetail' . $identifier]->Currency . '</td>
		</tr>';
	echo '<input type="hidden" name="Currency" value="' . $_SESSION['PaymentDetail' . $identifier]->Currency . '" />';
	/*get the default rate from the currency table if it has not been set */
	if (!isset($_POST['ExRate']) OR $_POST['ExRate']==''){
		$SQL = "SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail' . $identifier]->Currency ."'";
		$Result=DB_query($SQL,$db);
		$myrow=DB_fetch_row($Result);
		$_POST['ExRate']=locale_number_format($myrow[0],'Variable');
	}
}

if (!isset($_POST['ExRate'])){
	$_POST['ExRate']=1;
}

if (!isset($_POST['FunctionalExRate'])){
	$_POST['FunctionalExRate']=1;
}
if ($_SESSION['PaymentDetail' . $identifier]->AccountCurrency!=$_SESSION['PaymentDetail' . $identifier]->Currency AND isset($_SESSION['PaymentDetail' . $identifier]->AccountCurrency)){
	if (isset($SuggestedExRate)){
		$SuggestedExRateText = '<b>' . _('Suggested rate:') . ' ' . locale_number_format($SuggestedExRate,6) . '</b>';
	} else {
		$SuggestedExRateText ='';
	}
	if ($_POST['ExRate']==1 AND isset($SuggestedExRate)){
		$_POST['ExRate'] = locale_number_format($SuggestedExRate,6);
	}
	echo '<tr>
			<td>' . _('Payment Exchange Rate') . ':</td>
			<td><input class="number" type="text" name="ExRate" maxlength="10" size="12" value="' . $_POST['ExRate'] . '" /></td>
			<td>' . $SuggestedExRateText . ' <i>' . _('The exchange rate between the currency of the bank account currency and the currency of the payment') . '. 1 ' . $_SESSION['PaymentDetail' . $identifier]->AccountCurrency . ' = ? ' . $_SESSION['PaymentDetail' . $identifier]->Currency . '</i></td>
		</tr>';
}

if ($_SESSION['PaymentDetail' . $identifier]->AccountCurrency!=$_SESSION['CompanyRecord']['currencydefault']
										AND isset($_SESSION['PaymentDetail' . $identifier]->AccountCurrency)){
	if (isset($SuggestedFunctionalExRate)){
		$SuggestedFunctionalExRateText = '<b>' . _('Suggested rate:') . ' ' . locale_number_format($SuggestedFunctionalExRate,6) . '</b>';
	} else {
		$SuggestedFunctionalExRateText ='';
	}
	if ($_POST['FunctionalExRate']==1 AND isset($SuggestedFunctionalExRate)){
		$_POST['FunctionalExRate'] = locale_number_format($SuggestedFunctionalExRate,'Variable');
	}
	echo '<tr>
			<td>' . _('Functional Exchange Rate') . ':</td>
			<td><input type="text" name="FunctionalExRate" maxlength="10" size="12" value="' . $_POST['FunctionalExRate'] . '" /></td>
			<td>' . ' ' . $SuggestedFunctionalExRateText . ' <i>' . _('The exchange rate between the currency of the business (the functional currency) and the currency of the bank account') .  '. 1 ' . $_SESSION['CompanyRecord']['currencydefault'] . ' = ? ' . $_SESSION['PaymentDetail' . $identifier]->AccountCurrency . '</i></td>
		</tr>';
}
echo '<tr>
		<td>' . _('Payment type') . ':</td>
		<td><select name="Paymenttype">';

include('includes/GetPaymentMethods.php');
/* The array Payttypes is set up in includes/GetPaymentMethods.php
payment methods can be modified from the setup tab of the main menu under payment methods*/

foreach ($PaytTypes as $PaytType) {

	if (isset($_POST['Paymenttype']) AND $_POST['Paymenttype']==$PaytType){
		echo '<option selected="selected" value="' . $PaytType . '">' . $PaytType . '</option>';
	} else {
		echo '<option value="' . $PaytType . '">' . $PaytType . '</option>';
	}
} //end foreach
echo '</select></td>
	</tr>';

if (!isset($_POST['ChequeNum'])) {
	$_POST['ChequeNum']='';
}

echo '<tr>
		<td>' . _('Cheque Number') . ':</td>
		<td><input type="text" name="ChequeNum" maxlength="8" size="10" value="' . $_POST['ChequeNum'] . '" /> ' . _('(if using pre-printed stationery)') . '</td>
	</tr>';

if (!isset($_POST['Narrative'])) {
	$_POST['Narrative']='';
}

echo '<tr>
		<td>' . _('Reference / Narrative') . ':</td>
		<td colspan="2"><input type="text" name="Narrative" maxlength="80" size="82" value="' . $_POST['Narrative'] . '" />  ' . _('(Max. length 80 characters)') . '</td>
	</tr>';
echo '<tr>
		<td colspan="3"><div class="centre"><input type="submit" name="UpdateHeader" value="' . _('Update'). '" /></td>
	</tr>';


echo '</table><br />';


if ($_SESSION['CompanyRecord']['gllink_creditors']==1 AND $_SESSION['PaymentDetail' . $identifier]->SupplierID==''){
/* Set upthe form for the transaction entry for a GL Payment Analysis item */

	echo '<br /><table class="selection">';
	echo '<tr><th colspan="2"><font size="3" color="blue">' . _('General Ledger Payment Analysis Entry') . '</font></th></tr>';

	//Select the Tag
	echo '<tr>
			<td>' . _('Select Tag') . ':</td>
			<td><select name="Tag">';

	$SQL = "SELECT tagref,
				tagdescription
			FROM tags
			ORDER BY tagref";

	$result=DB_query($SQL,$db);
	echo '<option value="0"></option>';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['Tag']) AND $_POST['Tag']==$myrow['tagref']){
			echo '<option selected="selected" value="' . $myrow['tagref'] . '">' . $myrow['tagref'].' - ' .$myrow['tagdescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['tagref'] . '">' . $myrow['tagref'].' - ' .$myrow['tagdescription'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';
// End select Tag

	/*now set up a GLCode field to select from avaialble GL accounts */
	if (isset($_POST['GLManualCode'])) {
		echo '<tr>
				<td>' . _('Enter GL Account Manually') . ':</td>
				<td><input type="text" class="number" name="GLManualCode" maxlength="12" size="12" onChange="return inArray(this, GLCode.options,'.    "'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')"' . ' value="'. $_POST['GLManualCode'] .'"   /></td>
			</tr>';
	} else {
		echo '<tr>
				<td>' . _('Enter GL Account Manually') . ':</td>
				<td><input type="text" class="number" Name="GLManualCode" maxlength="12" size="12" onChange="return inArray(this, GLCode.options,'.    "'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /></td></tr>';
	}
	
	echo '<tr>
			<td>' . _('Select GL Group') . ':</td>
			<td><select name="GLGroup" onChange="return ReloadForm(UpdateCodes)">';

	$SQL = "SELECT groupname
			FROM accountgroups
			ORDER BY sequenceintb";

	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
		echo '</select></td>
			</tr>';
		prnMsg(_('No General ledger account groups have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'),'error');
	} else {
		echo '<option value=""></option>';
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['GLGroup']) AND ($_POST['GLGroup']==$myrow['groupname'])){
				echo '<option selected="selected" value="' . $myrow['groupname'] . '">' . $myrow['groupname'] . '</option>';
			} else {
				echo '<option value="' . $myrow['groupname'] . '">' . $myrow['groupname'] . '</option>';
			}
		}
		echo '</select>
				<input type="submit" name="UpdateCodes" value="Select" /></td>
				</tr>';
	}

	if (isset($_POST['GLGroup']) AND $_POST['GLGroup']!='') {
		$SQL = "SELECT accountcode,
						accountname
				FROM chartmaster
				WHERE group_='".$_POST['GLGroup']."'
				ORDER BY accountcode";
	} else {
		$SQL = "SELECT accountcode,
						accountname
				FROM chartmaster
				ORDER BY accountcode";
	}
	
	
	echo '<tr>
			<td>' . _('Select GL Account') . ':</td>
			<td><select name="GLCode" onChange="return assignComboToInput(this,'.'GLManualCode'.')">';

	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
		echo '</select></td></tr>';
		prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'),'error');
	} else {
		echo '<option value=""></option>';
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['GLCode']) AND $_POST['GLCode']==$myrow['accountcode']){
				echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';
			} else {
				echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	}

	echo '<tr>
			<td>'. _('Cheque/Voucher Number') .'</td>
			<td><input type="text" name="Cheque" maxlength="12" size="12" /></td>
		</tr>';

	if (isset($_POST['GLNarrative'])) {
		echo '<tr>
				<td>' . _('GL Narrative') . ':</td>
				<td><input type="text" name="GLNarrative" maxlength="50" size="52" value="' . $_POST['GLNarrative'] . '" /></td>
			</tr>';
	} else {
		echo '<tr>
				<td>' . _('GL Narrative') . ':</td>
				<td><input type="text" name="GLNarrative" maxlength="50" size="52" /></td>
			</tr>';
	}

	if (isset($_POST['GLAmount'])) {
		echo '<tr>
				<td>' . _('Amount') . ' (' . $_SESSION['PaymentDetail' . $identifier]->Currency . '):</td>
				<td><input type="text" name="GLAmount" maxlength="12" size="12" class="number" value="' . $_POST['GLAmount'] . '" /></td>
			</tr>';
	} else {
		echo '<tr><td>' . _('Amount') . ' (' . $_SESSION['PaymentDetail' . $identifier]->Currency . '):</td>
				<td><input type="text" name="GLAmount" maxlength="12" size="12" class="number" /></td>
			</tr>';
	}

	echo '</table><br />';
	echo '<div class="centre">
			<input type="submit" name="Process" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>';

	if (sizeOf($_SESSION['PaymentDetail' . $identifier]->GLItems)>0) {
		echo '<br />
			<table class="selection">
			<tr>
				<th>' . _('Cheque No').'</th>
				<th>' . _('Amount') . ' (' . $_SESSION['PaymentDetail' . $identifier]->Currency . ')</th>
				<th>' . _('GL Account') . '</th>
				<th>' . _('Narrative') . '</th>
				<th>' . _('Tag') . '</th>
			</tr>';

		$PaymentTotal = 0;
		foreach ($_SESSION['PaymentDetail' . $identifier]->GLItems as $PaymentItem) {
			$Tagsql="SELECT tagdescription from tags where tagref='" . $PaymentItem->Tag . "'";
			$TagResult=DB_query($Tagsql, $db);
			$TagMyrow=DB_fetch_row($TagResult);
			if ($PaymentItem->Tag==0) {
				$TagName='None';
			} else {
				$TagName=$TagMyrow[0];
			}
			
			echo '<tr>
				<td>' . $PaymentItem->Cheque . '</td>
				<td class="number">' . locale_number_format($PaymentItem->Amount,$_SESSION['PaymentDetail' . $identifier]->CurrDecimalPlaces) . '</td>
				<td>' . $PaymentItem->GLCode . ' - ' . $PaymentItem->GLActName . '</td>
				<td>' . stripslashes($PaymentItem->Narrative)  . '</td>
				<td>' . $PaymentItem->Tag . ' - ' . $TagName . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?identifier=' . $identifier) . '?Delete=' . $PaymentItem->ID . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this payment analysis item?') . '\');">' . _('Delete') . '</a></td>
				</tr>';
			$PaymentTotal += $PaymentItem->Amount;
		}
		echo '<tr>
				<td></td>
				<td class="number"><b>' . locale_number_format($PaymentTotal,$_SESSION['PaymentDetail' . $identifier]->CurrDecimalPlaces) . '</b></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			</table>
			<br />';
		echo '<input type="submit" name="CommitBatch" value="' . _('Accept and Process Payment') . '" />';
	}

} else {
/*a supplier is selected or the GL link is not active then set out
the fields for entry of receipt amt and disc */

	echo '<table class="selection">
		<tr>
			<td>' . _('Amount of Payment') . ' ' . $_SESSION['PaymentDetail' . $identifier]->Currency . ':</td>
			<td><input class="number" type="text" name="Amount" maxlength="12" size="13" value="' . $_SESSION['PaymentDetail' . $identifier]->Amount . '" /></td>
		</tr>';

	if (isset($_SESSION['PaymentDetail' . $identifier]->SupplierID)){ /*So it is a supplier payment so show the discount entry item */
		echo '<tr>
				<td>' . _('Amount of Discount') . ':</td>
				<td><input class="number" type="text" name="Discount" maxlength="12" size="13" value="' . $_SESSION['PaymentDetail' . $identifier]->Discount . '" /></td>
			</tr>';
		echo '<input type="hidden" name="SuppName" value="' . $_SESSION['PaymentDetail' . $identifier]->SuppName . '" />';
	} else {
		echo '<input type="hidden" name="Discount" Value="0" />';
	}
	echo '</table><br />';
	echo '<input type="submit" name="CommitBatch" value="' . _('Accept and Process Payment') . '" />';
}
echo '</form>';

include('includes/footer.inc');
?>

<?php

/* Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts */

/// @todo move to after session.php inclusion, unless there are side effects
include('includes/DefineReceiptClass.php');

require(__DIR__ . '/includes/session.php');

if (isset($_POST['DateBanked'])) {
	$_POST['DateBanked'] = ConvertSQLDate($_POST['DateBanked']);
}

include('includes/GetPaymentMethods.php');

$Title = __('Receipt Entry');

if ($_GET['Type']=='GL') {
	$ViewTopic = 'GeneralLedger';
	$BookMark = 'GLReceipts';
} else {
	$ViewTopic = 'ARTransactions';
	$BookMark = 'CustomerReceipts';
}

include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/GLFunctions.php');

if (empty($_GET['identifier'])) {
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}

$Msg='';

if (isset($_GET['NewReceipt'])){
	unset($_SESSION['ReceiptBatch' . $identifier]->Items);
	unset($_SESSION['ReceiptBatch' . $identifier]);
	unset($_SESSION['CustomerRecord' . $identifier]);
}

if (isset($_POST['Cancel'])) {
	$Cancel=1;
}

if (isset($_GET['Type']) AND $_GET['Type']=='GL') {
	$_POST['GLEntry']=1;
}

if ((isset($_POST['BatchInput'])
	AND $_POST['BankAccount']=='')
	OR (isset($_POST['Process'])
	AND $_POST['BankAccount']=='')) {

	echo '<br />';
	prnMsg(__('A bank account must be selected for this receipt'), 'warn');
	$BankAccountEmpty=true;
} else if(isset($_GET['NewReceipt'])) {
	$BankAccountEmpty=true;
} else {
	$BankAccountEmpty=false;
}

$Errors = array();

if (!isset($_GET['Delete']) AND isset($_SESSION['ReceiptBatch' . $identifier])){
	//always process a header update unless deleting an item

	$_SESSION['ReceiptBatch' . $identifier]->Account = $_POST['BankAccount'];
	/*Get the bank account currency and set that too */

	$SQL = "SELECT bankaccountname,
					currcode,
					decimalplaces
			FROM bankaccounts
			INNER JOIN currencies
			ON bankaccounts.currcode=currencies.currabrev
			WHERE accountcode='" . $_POST['BankAccount']."'";

	$ErrMsg =__('The bank account name cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result)==1){
		$MyRow = DB_fetch_array($Result);
		$_SESSION['ReceiptBatch' . $identifier]->BankAccountName = $MyRow['bankaccountname'];
		$_SESSION['ReceiptBatch' . $identifier]->AccountCurrency=$MyRow['currcode'];
		$_SESSION['ReceiptBatch' . $identifier]->CurrDecimalPlaces=$MyRow['decimalplaces'];
		unset($Result);
	} elseif (DB_num_rows($Result)==0 AND !$BankAccountEmpty){
		prnMsg( __('The bank account number') . ' ' . $_POST['BankAccount'] . ' ' . __('is not set up as a bank account'),'error');
		include('includes/footer.php');
		exit();
	}

	if (!Is_Date($_POST['DateBanked'])){
		$_POST['DateBanked'] = Date($_SESSION['DefaultDateFormat']);
	}
	$_SESSION['ReceiptBatch' . $identifier]->DateBanked = $_POST['DateBanked'];
	if (isset($_POST['ExRate']) AND $_POST['ExRate']!=''){
		if (is_numeric(filter_number_format($_POST['ExRate']))){
			$_SESSION['ReceiptBatch' . $identifier]->ExRate = filter_number_format($_POST['ExRate']);
		} else {
			prnMsg(__('The exchange rate entered should be numeric'),'warn');
		}
	}
	if (isset($_POST['FunctionalExRate']) AND $_POST['FunctionalExRate']!=''){
		if (is_numeric(filter_number_format($_POST['FunctionalExRate']))){
			$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate=filter_number_format($_POST['FunctionalExRate']); //ex rate between receipt currency and account currency
		} else {
			prnMsg(__('The functional exchange rate entered should be numeric'),'warn');
		}
	}
	if (!isset($_POST['ReceiptType'])) {
		$_POST['ReceiptType'] = '';
	}
	$_SESSION['ReceiptBatch' . $identifier]->ReceiptType = $_POST['ReceiptType'];

	if (!isset($_POST['Currency'])){
		$_POST['Currency']=$_SESSION['CompanyRecord']['currencydefault'];
	}

	if ($_SESSION['ReceiptBatch' . $identifier]->Currency!=$_POST['Currency']){

		$_SESSION['ReceiptBatch' . $identifier]->Currency=$_POST['Currency']; //receipt currency
		/*Now customer receipts entered using the previous currency need to be ditched
		and a warning message displayed if there were some customer receipted entered */
		if (count($_SESSION['ReceiptBatch' . $identifier]->Items)>0){
			unset($_SESSION['ReceiptBatch' . $identifier]->Items);
			prnMsg(__('Changing the currency of the receipt means that existing entries need to be re-done - only customers trading in the selected currency can be selected'),'warn');
		}

	}

	if ($_SESSION['ReceiptBatch' . $identifier]->AccountCurrency==$_SESSION['CompanyRecord']['currencydefault']){
		$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate = 1;
		$SuggestedFunctionalExRate =1;
	} elseif (!$BankAccountEmpty) {
		/*To illustrate the rates required
			Take an example functional currency NZD receipt in USD from an AUD bank account
			1 NZD = 0.80 USD
			1 NZD = 0.90 AUD
			The FunctionalExRate = 0.90 - the rate between the functional currency and the bank account currency
			The receipt ex rate is the rate at which one can sell the received currency and purchase the bank account currency
			or 0.8/0.9 = 0.88889
		*/

		/*Get suggested FunctionalExRate between the bank account currency and the home (functional) currency */
		$Result = DB_query("SELECT rate, decimalplaces FROM currencies WHERE currabrev='" . $_SESSION['ReceiptBatch' . $identifier]->AccountCurrency . "'");
		$MyRow = DB_fetch_array($Result);
		$SuggestedFunctionalExRate = $MyRow['rate'];
		$_SESSION['ReceiptBatch' . $identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];

	} //end else account currency != functional currency

	if ($_POST['Currency']==$_SESSION['ReceiptBatch' . $identifier]->AccountCurrency){
		$_SESSION['ReceiptBatch' . $identifier]->ExRate = 1; //ex rate between receipt currency and account currency
		$SuggestedExRate=1;
	} elseif(isset($_POST['Currency'])) {
		/*Get the exchange rate between the functional currency and the receipt currency*/
		$Result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['ReceiptBatch' . $identifier]->Currency . "'");
		$MyRow = DB_fetch_array($Result);
		$TableExRate = $MyRow['rate']; //this is the rate of exchange between the functional currency and the receipt currency
		/*Calculate cross rate to suggest appropriate exchange rate between receipt currency and account currency */
		$SuggestedExRate = $TableExRate/$SuggestedFunctionalExRate;
	}

	$_SESSION['ReceiptBatch' . $identifier]->BankTransRef = $_POST['BankTransRef'];
	$_SESSION['ReceiptBatch' . $identifier]->Narrative = $_POST['BatchNarrative'];

} elseif (isset($_GET['Delete'])) {
	/* User hit delete the receipt entry from the batch */
	$_SESSION['ReceiptBatch' . $identifier]->remove_receipt_item($_GET['Delete']);
} else { //it must be a new receipt batch
	$_SESSION['ReceiptBatch' . $identifier] = new Receipt_Batch;
}


if (isset($_POST['Process'])){ //user hit submit a new entry to the receipt batch

	if (!isset($_POST['GLCode'])) {
		$_POST['GLCode']='';
	}
	if (!isset($_POST['tag'])) {
		$_POST['tag']='';
	}
	if (!isset($_POST['CustomerID'])) {
		$_POST['CustomerID']='';
	}
	if (!isset($_POST['CustomerName'])) {
		$_POST['CustomerName']='';
	}
	if ($_POST['Discount']==0 AND $ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['percentdiscount']>0){
		if (isset($_GET['Type']) AND $_GET['Type'] == 'Customer') {
			$_POST['Discount'] = $_POST['Amount']*$ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['percentdiscount'];
		}
	}

	if ($_POST['GLCode'] == '' AND $_GET['Type']=='GL') {
		prnMsg( __('No General Ledger code has been chosen') . ' - ' . __('so this GL analysis item could not be added'),'warn');

	} else {
		$AllowThisPosting = true;
 		if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1) {
 			if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' AND $_POST['GLCode'] == $_SESSION['CompanyRecord']['debtorsact']) {
 				prnMsg(__('Payments involving the debtors control account cannot be entered. The general ledger debtors ledger (AR) integration is enabled so control accounts are automatically maintained. This setting can be disabled in System Configuration'), 'warn');
 				$AllowThisPosting = false;
 			}
 			if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' AND
				($_POST['GLCode'] == $_SESSION['CompanyRecord']['creditorsact'] OR $_POST['GLCode'] == $_SESSION['CompanyRecord']['grnact'])) {
 				prnMsg(__('Payments involving the creditors control account or the GRN suspense account cannot be entered. The general ledger creditors ledger (AP) integration is enabled so control accounts are automatically maintained. This setting can be disabled in System Configuration'), 'warn');
 				$AllowThisPosting = false;
 			}
 			if ($_POST['GLCode'] == $_SESSION['CompanyRecord']['retainedearnings']) {
 				prnMsg(__('Payments involving the retained earnings control account cannot be entered. This account is automtically maintained.'), 'warn');
 				$AllowThisPosting = false;
 			}
 		}
 		if ($AllowThisPosting) {
 			$_SESSION['ReceiptBatch' . $identifier]->add_to_batch(filter_number_format($_POST['Amount']),
													$_POST['CustomerID'],
													filter_number_format($_POST['Discount']),
													$_POST['Narrative'],
													$_POST['GLCode'],
													$_POST['PayeeBankDetail'],
													$_POST['CustomerName'],
													$_POST['tag']);
			/*Make sure the same receipt is not double processed by a page refresh */
			$Cancel = 1;
		}
	}
}

if (isset($Cancel)){
	unset($_SESSION['CustomerRecord' . $identifier]);
	unset($_POST['CustomerID']);
	unset($_POST['CustomerName']);
	unset($_POST['Amount']);
	unset($_POST['Discount']);
	unset($_POST['Narrative']);
	unset($_POST['PayeeBankDetail']);
}


if (isset($_POST['CommitBatch'])){

 /* once all receipts items entered, process all the data in the
  session cookie into the DB creating a single banktrans for the whole amount
  of all receipts in the batch and DebtorTrans records for each receipt item
  all DebtorTrans will refer to a single banktrans. A GL entry is created for
  each GL receipt entry and one for the debtors entry and one for the bank
  account debit

  NB allocations against debtor receipts are a separate exercice

  first off run through the array of receipt items $_SESSION['ReceiptBatch']->Items and
  if GL integrated then create GL Entries for the GL Receipt items
  and add up the non-GL ones for posting to debtors later,
  also add the total discount total receipts*/

	$PeriodNo = GetPeriod($_SESSION['ReceiptBatch' . $identifier]->DateBanked);

	if ($_SESSION['CompanyRecord']==0){
		prnMsg(__('The company has not yet been set up properly') . ' - ' . __('this information is needed to process the batch') . '. ' . __('Processing has been cancelled'),'error');
		include('includes/footer.php');
		exit();
	}

	/*Make an array of the defined bank accounts */
	$SQL = "SELECT accountcode FROM bankaccounts";
	$Result = DB_query($SQL);
	$BankAccounts = array();
	$i=0;
	while ($Act = DB_fetch_row($Result)){
		$BankAccounts[$i]= $Act[0];
		$i++;
	}

	/*Start a transaction to do the whole lot inside */
	DB_Txn_Begin();
	$_SESSION['ReceiptBatch' . $identifier]->BatchNo = GetNextTransNo(12);


	$BatchReceiptsTotal = 0; //in functional currency
	$BatchDiscount = 0; //in functional currency
	$BatchDebtorTotal = 0; //in functional currency
	$CustomerReceiptCounter=1; //Count lines of customer receipts in this batch

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
			'/images/money_add.png" title="',// Icon image.
			__('Summary of Receipt Batch'), '" /> ',// Icon title.
			__('Summary of Receipt Batch'), '</p>',// Page title.
		'<table class="selection">
		<thead>
			<tr>
				<th>', __('Batch Number'), '</th>
				<th>', __('Date Banked'), '</th>
				<th>', __('Customer Name'), '</th>
				<th class="text">', __('GL Code'), '</th>
				<th class="number">', __('Amount of Receipt'), '</th>';
	if(isset($ReceiptItem) AND $ReceiptItem->GLCode =='') {
		echo '<th class="noPrint">&nbsp;</th>';
	}
	echo '</tr>
		</thead><tbody>';

	foreach ($_SESSION['ReceiptBatch' . $identifier]->Items as $ReceiptItem) {

		$SQL = "SELECT accountname FROM chartmaster WHERE accountcode='" . $ReceiptItem->GLCode . "'";
		$Result = DB_query($SQL);
		$MyRow=DB_fetch_array($Result);

		echo '<tr class="striped_row">
			<td>' . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . '</td>
			<td>' . $_SESSION['ReceiptBatch' . $identifier]->DateBanked . '</td>
			<td>' . $ReceiptItem->CustomerName . '</td>
			<td class="text">' . $ReceiptItem->GLCode . ' - ' . ($MyRow['accountname'] ?? '') . '</td>
			<td class="number">' . locale_number_format($ReceiptItem->Amount/$_SESSION['ReceiptBatch' . $identifier]->ExRate/$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate,$_SESSION['ReceiptBatch' . $identifier]->CurrDecimalPlaces)  . '</td>';

		if ($ReceiptItem->GLCode ==''){
			echo '<td class="noPrint"><a target="_blank" href="', $RootPath, '/PDFReceipt.php?BatchNumber=', $_SESSION['ReceiptBatch' . $identifier]->BatchNo, '&ReceiptNumber=', $CustomerReceiptCounter, '">', __('Print a Customer Receipt'), '</a></td></tr>';
			$CustomerReceiptCounter += 1;
		}

		if ($ReceiptItem->GLCode !=''){ //so its a GL receipt
			if ($_SESSION['CompanyRecord']['gllink_debtors']==1){ /* then enter a GLTrans record */
				 $SQL = "INSERT INTO gltrans (type,
								 			typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
					VALUES (
						12,
						'" . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . "',
						'" . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . "',
						'" . $PeriodNo . "',
						'" . $ReceiptItem->GLCode . "',
						'" . mb_substr($ReceiptItem->Narrative, 0, 200) . "',
						'" . -($ReceiptItem->Amount/$_SESSION['ReceiptBatch' . $identifier]->ExRate/$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate) . "'
					)";
				$ErrMsg = __('Cannot insert a GL entry for the receipt because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
				InsertGLTags($ReceiptItem->tag);
			}

			/*check to see if this is a GL posting to another bank account (or the same one)
			if it is then a matching payment needs to be created for this account too */

			if (in_array($ReceiptItem->GLCode, $BankAccounts)) {

			/*Need to deal with the case where the payment from one bank account could be to a bank account in another currency */

				/*Get the currency and rate of the bank account transferring to*/
				$SQL = "SELECT currcode, rate
							FROM bankaccounts INNER JOIN currencies
							ON bankaccounts.currcode = currencies.currabrev
							WHERE accountcode='" . $ReceiptItem->GLCode."'";
				$TrfFromAccountResult = DB_query($SQL);
				$TrfFromBankRow = DB_fetch_array($TrfFromAccountResult) ;
				$TrfFromBankCurrCode = $TrfFromBankRow['currcode'];
				$TrfFromBankExRate = $TrfFromBankRow['rate'];

				if ($_SESSION['ReceiptBatch' . $identifier]->AccountCurrency == $TrfFromBankCurrCode){
					/*Make sure to use the same rate if the transfer is between two bank accounts in the same currency */
					$TrfFromBankExRate = $_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate;
				}

				/*Consider an example - had to be currencies I am familar with sorry so I could figure it out!!
					 functional currency NZD
					 bank account in AUD - 1 NZD = 0.90 AUD (FunctionalExRate)
					 receiving USD - 1 AUD = 0.85 USD  (ExRate)
					 from a bank account in EUR - 1 NZD = 0.52 EUR

					 oh yeah - now we are getting tricky!
					 Lets say we received USD 100 to the AUD bank account from the EUR bank account

					 To get the ExRate for the bank account we are transferring money from
					 we need to use the cross rate between the NZD-AUD/NZD-EUR
					 and apply this to the

					 the receipt record will read
					 exrate = 0.85 (1 AUD = USD 0.85)
					 amount = 100 (USD)
					 functionalexrate = 0.90 (1 NZD = AUD 0.90)

					 the payment record will read

					 amount 100 (USD)
					 exrate    (1 EUR =  (0.85 x 0.90)/0.52 USD  ~ 1.47
					  					(ExRate x FunctionalExRate) / USD Functional ExRate
					 Check this is 1 EUR = 1.47 USD
					 functionalexrate =  (1NZD = EUR 0.52)

				*/

				$PaymentTransNo = GetNextTransNo( 1 );
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
						VALUES (
							'" . $PaymentTransNo . "',
							1,
							'" . $ReceiptItem->GLCode . "',
							'" . __('Act Transfer') ." - " . $ReceiptItem->Narrative . "',
							'" . (($_SESSION['ReceiptBatch' . $identifier]->ExRate * $_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate)/$TrfFromBankExRate). "',
							'" . $TrfFromBankExRate . "',
							'" . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . "',
							'" . $ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['paymentname'] . "',
							'" . -$ReceiptItem->Amount . "',
							'" . $_SESSION['ReceiptBatch' . $identifier]->Currency . "'
						)";

				$ErrMsg = __('Cannot insert a bank transaction using the SQL');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			} //end if an item is a transfer between bank accounts

		} else { //its not a GL item - its a customer receipt then
			/*Accumulate the total debtors credit including discount */
			$BatchDebtorTotal += (($ReceiptItem->Discount + $ReceiptItem->Amount)/$_SESSION['ReceiptBatch' . $identifier]->ExRate/$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate);
			/*Create a DebtorTrans entry for each customer deposit */

			/*The rate of exchange required here is the rate between the functional (home) currency and the customer receipt currency
			 * We have the exchange rate between the bank account and the functional home currency  $_SESSION['ReceiptBatch']->ExRate
			 * and the exchange rate betwen the currency being paid and the bank account */

			$SQL = "INSERT INTO debtortrans (transno,
											type,
											debtorno,
											branchcode,
											order_,
											trandate,
											inputdate,
											prd,
											reference,
											tpe,
											rate,
											ovamount,
											ovdiscount,
											invtext,
											salesperson)
					VALUES (
						'" . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . "',
						12,
						'" . $ReceiptItem->Customer . "',
						'',
						'" . $ReceiptItem->ID . "',
						'" . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $PeriodNo . "',
						'" . $ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['paymentname']  . ' ' . $ReceiptItem->PayeeBankDetail . "',
						'',
						'" . ($_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate*$_SESSION['ReceiptBatch' . $identifier]->ExRate) . "',
						'" . -$ReceiptItem->Amount . "',
						'" . -$ReceiptItem->Discount . "',
						'" . $ReceiptItem->Narrative. "',
						'" . $_SESSION['SalesmanLogin']. "'
					)";
			$ErrMsg = __('Cannot insert a receipt transaction against the customer because') ;
			$Result = DB_query($SQL, $ErrMsg, '', true);

			$SQL = "UPDATE debtorsmaster
						SET lastpaiddate = '" . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . "',
						lastpaid='" . $ReceiptItem->Amount ."'
					WHERE debtorsmaster.debtorno='" . $ReceiptItem->Customer . "'";

			$ErrMsg = __('Cannot update the customer record for the date of the last payment received because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

		} //end of if its a customer receipt
		$BatchDiscount += ($ReceiptItem->Discount/$_SESSION['ReceiptBatch' . $identifier]->ExRate/$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate);
		$BatchReceiptsTotal += ($ReceiptItem->Amount/$_SESSION['ReceiptBatch' . $identifier]->ExRate/$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate);

	} /*end foreach $ReceiptItem */
	echo '</tbody></table>';

	/*now enter the BankTrans entry */

	$SQL="INSERT INTO banktrans (type,
								transno,
								bankact,
								ref,
								exrate,
								functionalexrate,
								transdate,
								banktranstype,
								amount,
								currcode)
		VALUES (
			12,
			'" . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . "',
			'" . $_SESSION['ReceiptBatch' . $identifier]->Account . "',
			'" . $_SESSION['ReceiptBatch' . $identifier]->BankTransRef . "',
			'" . $_SESSION['ReceiptBatch' . $identifier]->ExRate . "',
			'" . $_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate . "',
			'" . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . "',
			'" . $ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['paymentname'] . "',
			'" . ($BatchReceiptsTotal * $_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate * $_SESSION['ReceiptBatch' . $identifier]->ExRate) . "',
			'" . $_SESSION['ReceiptBatch' . $identifier]->Currency . "'
		)";
	$ErrMsg = __('Cannot insert a bank transaction');
	$Result = DB_query($SQL, $ErrMsg, '', true);


	if ($_SESSION['CompanyRecord']['gllink_debtors']==1){ /* then enter GLTrans records for discount, bank and debtors */

		if ($BatchReceiptsTotal!=0){
			/* Bank account entry first */
			$SQL="INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
				VALUES (
					12,
					'" . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . "',
					'" . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['ReceiptBatch' . $identifier]->Account . "',
					'" . mb_substr($_SESSION['ReceiptBatch' . $identifier]->Narrative, 0, 200) . "',
					'" . $BatchReceiptsTotal . "'
				)";
			$ErrMsg = __('Cannot insert a GL transaction for the bank account debit');
			$Result = DB_query($SQL, $ErrMsg, '', true);


		}
		if ($BatchDebtorTotal!=0){
			/* Now Credit Debtors account with receipts + discounts */
			$SQL="INSERT INTO gltrans ( type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
						VALUES (
							12,
							'" . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . "',
							'" . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . "',
							'" . $PeriodNo . "',
							'". $_SESSION['CompanyRecord']['debtorsact'] . "',
							'" . mb_substr($_SESSION['ReceiptBatch' . $identifier]->Narrative, 0, 200) . "',
							'" . -$BatchDebtorTotal . "'
							)";
			$ErrMsg = __('Cannot insert a GL transaction for the debtors account credit');
			$Result = DB_query($SQL, $ErrMsg, '', true);

		} //end if there are some customer deposits in this batch

		if ($BatchDiscount!=0){
			/* Now Debit Discount account with discounts allowed*/
			$SQL="INSERT INTO gltrans ( type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
						VALUES (
								12,
								'" . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . "',
								'" . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . "',
								'" . $PeriodNo . "',
								'" . $_SESSION['CompanyRecord']['pytdiscountact'] . "',
								'" . mb_substr($_SESSION['ReceiptBatch' . $identifier]->Narrative, 0, 200) . "',
								'" . $BatchDiscount . "'
							)";
			$ErrMsg = __('Cannot insert a GL transaction for the payment discount debit');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		} //end if there is some discount

	} //end if there is GL work to be done - ie config is to link to GL
	EnsureGLEntriesBalance(12,$_SESSION['ReceiptBatch' . $identifier]->BatchNo);

	$ErrMsg = __('Cannot commit the changes');
	DB_Txn_Commit();
	prnMsg( __('Receipt batch') . ' ' . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . ' ' . __('has been successfully entered into the database'),'success');

	echo '<div class="centre noPrint">',
		'<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print') . '" alt="" />' . ' ' . '<a href="' . $RootPath . '/PDFBankingSummary.php?BatchNo=' . $_SESSION['ReceiptBatch' . $identifier]->BatchNo . '">' . __('Print PDF Batch Summary') . '</a></p>';
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/allocation.png" title="' . __('Allocate') . '" alt="" />' . ' ' . '<a href="' . $RootPath . '/CustomerAllocations.php">' . __('Allocate Receipts') . '</a></p>';
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/transactions.png" title="', __('Enter Receipts'), '" /> ', '<a href="', $RootPath, '/CustomerReceipt.php?NewReceipt=Yes&Type=', urlencode($_GET['Type']), '">', __('Enter Receipts'), '</a></p>',
		'</div>';

	unset($_SESSION['ReceiptBatch' . $identifier]);
	include('includes/footer.php');
	exit();

} /* End of commit batch */

if (isset($_POST['Search'])){
/*Will only be true if clicked to search for a customer code */

	if ($_POST['Keywords'] AND $_POST['CustCode']) {
		$Msg=__('Customer name keywords have been used in preference to the customer code extract entered');
	}
	if ($_POST['Keywords']==''
		AND $_POST['CustCode']==''
		AND $_POST['CustInvNo']=='') {
			$SQL = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name
					FROM debtorsmaster
					WHERE debtorsmaster.currcode= '" . $_SESSION['ReceiptBatch' . $identifier]->Currency . "'";
	} else {
		if (mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$SQL = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name
					FROM debtorsmaster
					WHERE debtorsmaster.name " . LIKE . " '". $SearchString . "'
					AND debtorsmaster.currcode= '" . $_SESSION['ReceiptBatch' . $identifier]->Currency . "'";

		} elseif (mb_strlen($_POST['CustCode'])>0){
			$SQL = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name
					FROM debtorsmaster
					WHERE debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
					AND debtorsmaster.currcode= '" . $_SESSION['ReceiptBatch' . $identifier]->Currency . "'";
		} elseif (mb_strlen($_POST['CustInvNo'])>0){
			$SQL = "SELECT debtortrans.debtorno,
						debtorsmaster.name
					FROM debtorsmaster LEFT JOIN debtortrans
					ON debtorsmaster.debtorno=debtortrans.debtorno
					WHERE debtortrans.transno " . LIKE . " '%" . $_POST['CustInvNo'] . "%'
					AND debtorsmaster.currcode= '" . $_SESSION['ReceiptBatch' . $identifier]->Currency . "'";
		}
	}
		if ($_SESSION['SalesmanLogin'] != '') {
			$SQL .= " AND EXISTS (
						SELECT *
						FROM 	custbranch
						WHERE 	custbranch.debtorno = debtorsmaster.debtorno
							AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "')";
		}
		$ErrMsg = __('The searched customer records requested cannot be retrieved');
		$CustomerSearchResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($CustomerSearchResult)==1){
			$MyRow=DB_fetch_array($CustomerSearchResult);
			$Select = $MyRow['debtorno'];
			unset($CustomerSearchResult);
		} elseif (DB_num_rows($CustomerSearchResult)==0){
			prnMsg( __('No customer records contain the selected text') . ' - ' . __('please alter your search criteria and try again'),'info');
		}

	 //one of keywords or custcode was more than a zero length string
} //end of if search

if (isset($_POST['Select'])){
	$Select = $_POST['Select'];
}

if (isset($Select)) {
/*will only be true if a customer has just been selected by clicking on the customer or only one
customer record returned by the search - this record is then auto selected */

	$_POST['CustomerID']=$Select;
	/*need to get currency sales type - payment discount percent and GL code
	as well as payment terms and credit status and hold the lot as session variables
	the receipt held entirely as session variables until the button clicked to process*/


	if (isset($_SESSION['CustomerRecord' . $identifier])){
	   unset($_SESSION['CustomerRecord' . $identifier]);
	}

	$SQL = "SELECT debtorsmaster.name,
				debtorsmaster.pymtdiscount,
				debtorsmaster.currcode,
				currencies.currency,
				currencies.rate,
				currencies.decimalplaces AS currdecimalplaces,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription,
				SUM(debtortrans.balance) AS balance,
				SUM(CASE WHEN paymentterms.daysbeforedue > 0  THEN
					CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue  THEN debtortrans.balance ELSE 0 END
				ELSE
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate), paymentterms.dayinfollowingmonth)) >= 0 THEN debtortrans.balance ELSE 0 END
				END) AS due,
				SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight - debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
				ELSE
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate), paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN debtortrans.balance ELSE 0 END
				END) AS overdue1,
				SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.balance ELSE 0 END
				ELSE
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate), paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN debtortrans.balance ELSE 0 END
				END) AS overdue2
			FROM debtorsmaster INNER JOIN paymentterms
			ON debtorsmaster.paymentterms = paymentterms.termsindicator
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason = holdreasons.reasoncode
			INNER JOIN currencies
			ON debtorsmaster.currcode = currencies.currabrev
			INNER JOIN debtortrans
			ON debtorsmaster.debtorno = debtortrans.debtorno
			WHERE debtorsmaster.debtorno = '" . $_POST['CustomerID'] . "'";
	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$SQL .= " GROUP BY debtorsmaster.name,
				debtorsmaster.pymtdiscount,
				debtorsmaster.currcode,
				currencies.currency,
				currencies.rate,
				currencies.decimalplaces,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				paymentterms.daysbeforedue,
				paymentterms.dayinfollowingmonth,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription";


	$ErrMsg = __('The customer details could not be retrieved because');
	$CustomerResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($CustomerResult)==0){

		/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

		$NIL_BALANCE = true;

		$SQL = "SELECT debtorsmaster.name,
						debtorsmaster.pymtdiscount,
						currencies.currency,
						currencies.rate,
						currencies.decimalplaces AS currdecimalplaces,
						paymentterms.terms,
						debtorsmaster.creditlimit,
						debtorsmaster.currcode,
						holdreasons.dissallowinvoices,
						holdreasons.reasondescription
					FROM debtorsmaster INNER JOIN paymentterms
					ON debtorsmaster.paymentterms = paymentterms.termsindicator
					INNER JOIN holdreasons
					ON debtorsmaster.holdreason = holdreasons.reasoncode
					INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
					WHERE debtorsmaster.debtorno = '" . $_POST['CustomerID'] . "'";

		$ErrMsg = __('The customer details could not be retrieved because');
		$CustomerResult = DB_query($SQL, $ErrMsg);

	} else {
		$NIL_BALANCE = false;
	}

	$_SESSION['CustomerRecord' . $identifier] = DB_fetch_array($CustomerResult);

	if ($NIL_BALANCE==true){
		$_SESSION['CustomerRecord' . $identifier]['balance']=0;
		$_SESSION['CustomerRecord' . $identifier]['due']=0;
		$_SESSION['CustomerRecord' . $identifier]['overdue1']=0;
		$_SESSION['CustomerRecord' . $identifier]['overdue2']=0;
	}
} /*end of if customer has just been selected  all info required read into $_SESSION['CustomerRecord']*/

/*set up the form whatever */


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Type=' . urlencode($_GET['Type']) . '&amp;identifier=' . urlencode($identifier) . '" method="post" id="form1">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/*show the batch header details and the entries in the batch so far */

$SQL = "SELECT bankaccountname,
				bankaccounts.accountcode,
				bankaccounts.currcode
		FROM bankaccounts
		INNER JOIN chartmaster
			ON bankaccounts.accountcode=chartmaster.accountcode
		INNER JOIN bankaccountusers
			ON bankaccounts.accountcode=bankaccountusers.accountcode
		WHERE bankaccountusers.userid = '" . $_SESSION['UserID'] ."'
		ORDER BY bankaccountname";

$ErrMsg = __('The bank accounts could not be retrieved because');
$AccountsResults = DB_query($SQL, $ErrMsg);

if (isset($_POST['GLEntry'])) {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . __('Bank Account Receipts Entry') . '" alt="" />' . ' ' . __('Bank Account Receipts Entry') . '</p>';
} else {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . __('Enter Receipt') . '" alt="" />' . ' ' . __('Enter Customer Receipt') . '</p>';
	echo '<div class="page_help_text">' . __('To enter a payment TO a customer (ie. to pay out a credit note), enter a negative payment amount.') . '</div>';
}
echo '<fieldset>
		<legend>', __('Receipt Batch Header Details'), '</legend>
		<field>
			<label for="BankAccount">' . __('Bank Account') . ':</label>
			<select tabindex="1" autofocus="autofocus" name="BankAccount" onchange="ReloadForm(form1.BatchInput)">';
if (DB_num_rows($AccountsResults)==0){
	echo '</select>
		</field>
		</fieldset>';
	prnMsg(__('Bank Accounts have not yet been defined') . '. ' . __('You must first') . ' ' . '<a href="' . $RootPath . '/BankAccounts.php">' . __('define the bank accounts') . '</a>' . __('and general ledger accounts to be affected'),'info');
	include('includes/footer.php');
	 exit();
} else {
	echo '<option value=""></option>';
	while ($MyRow=DB_fetch_array($AccountsResults)){
		/*list the bank account names */
		if ($_SESSION['ReceiptBatch' . $identifier]->Account==$MyRow['accountcode']){
			echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname']. ' - ' . $MyRow['currcode'] . '</option>';
		}
	}
	echo '</select>
		</field>';
}

if ($_SESSION['ReceiptBatch' . $identifier]->DateBanked == '' or !Is_Date($_SESSION['ReceiptBatch' . $identifier]->DateBanked)){
	$_SESSION['ReceiptBatch' . $identifier]->DateBanked = Date($_SESSION['DefaultDateFormat']);
}

echo '<field>
		<label for="DateBanked">' . __('Date Banked') . ':</label>
		<input tabindex="2" required="required" type="date" name="DateBanked" maxlength="10" size="11" value="' . FormatDateForSQL($_SESSION['ReceiptBatch' . $identifier]->DateBanked) . '" />
	</field>';

echo '<field>
		<label for="Currency">' . __('Currency') . ':</label>
		<select tabindex="3" name="Currency" onchange="ReloadForm(form1.BatchInput)">';
if (!isset($_SESSION['ReceiptBatch' . $identifier]->Currency)){
  $_SESSION['ReceiptBatch' . $identifier]->Currency=$_SESSION['CompanyRecord']['currencydefault'];
}
$SQL = "SELECT currency, currabrev, rate FROM currencies";
$Result = DB_query($SQL);
if (DB_num_rows($Result)==0){
	echo '</select></field>';
	prnMsg(__('No currencies are defined yet') . '. ' . __('Receipts cannot be entered until a currency is defined'),'warn');

} else {
	include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.
	while ($MyRow=DB_fetch_array($Result)){
		if ($_SESSION['ReceiptBatch' . $identifier]->Currency==$MyRow['currabrev']){
			echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $CurrencyName[$MyRow['currabrev']] . '</option>';
		} else {
			echo '<option value="' . $MyRow['currabrev'] . '">' . $CurrencyName[$MyRow['currabrev']] . '</option>';
		}
	}
	echo '</select>
		</field>';
}

if (!isset($_SESSION['ReceiptBatch' . $identifier]->ExRate)){
	$_SESSION['ReceiptBatch' . $identifier]->ExRate=1;
}

if (!isset($_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate)){
	$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate=1;
}
if ($_SESSION['ReceiptBatch' . $identifier]->AccountCurrency != $_SESSION['ReceiptBatch' . $identifier]->Currency AND isset($_SESSION['ReceiptBatch' . $identifier]->AccountCurrency)) {
	if($_SESSION['ReceiptBatch' . $identifier]->ExRate==1 AND isset($SuggestedExRate)) {
		$_SESSION['ReceiptBatch' . $identifier]->ExRate = $SuggestedExRate;
	} elseif($_POST['Currency'] != $_POST['PreviousCurrency'] AND isset($SuggestedExRate)) {//the user has changed the currency, then we should revise suggested rate
		$_SESSION['ReceiptBatch' . $identifier]->ExRate = $SuggestedExRate;
	}

	if(isset($SuggestedExRate)) {
		$SuggestedExRateText = '<b>' . __('Suggested rate:') . ' 1 ' . $_SESSION['ReceiptBatch' . $identifier]->AccountCurrency . ' = ' . locale_number_format($SuggestedExRate,8) . ' ' . $_SESSION['ReceiptBatch' . $identifier]->Currency . '</b>';
	} else {
		$SuggestedExRateText = '<b>1 ' . $_SESSION['ReceiptBatch' . $identifier]->AccountCurrency . ' = ? ' . $_SESSION['ReceiptBatch' . $identifier]->Currency . '</b>';
	}
	echo '<field>
			<label for="ExRate">', __('Receipt Exchange Rate'), ':</label>
			<input class="number" maxlength="12" name="ExRate" required="required" size="14" tabindex="4" type="text" value="', locale_number_format($_SESSION['ReceiptBatch' . $identifier]->ExRate,8), '" />
			<fieldhelp>', $SuggestedExRateText, ' <i>', __('The exchange rate between the currency of the bank account currency and the currency of the receipt'), '.</i></fieldhelp>
		</field>';
}

if($_SESSION['ReceiptBatch' . $identifier]->AccountCurrency != $_SESSION['CompanyRecord']['currencydefault'] AND isset($_SESSION['ReceiptBatch' . $identifier]->AccountCurrency)) {

	if($_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate==1 AND isset($SuggestedFunctionalExRate)) {
		$_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate = $SuggestedFunctionalExRate;
	}
	if(isset($SuggestedFunctionalExRate)) {
		$SuggestedFunctionalExRateText = '<b>' . __('Suggested rate:') . ' 1 ' . $_SESSION['CompanyRecord']['currencydefault'] . ' = ' . locale_number_format($SuggestedFunctionalExRate,8) . ' ' . $_SESSION['ReceiptBatch' . $identifier]->AccountCurrency . '</b>';
	} else {
		$SuggestedFunctionalExRateText = '<b>1 ' . $_SESSION['CompanyRecord']['currencydefault'] . ' = ? ' . $_SESSION['ReceiptBatch']->AccountCurrency . '</b>';
	}
	echo '<field>
			<label for="FunctionalExRate">', __('Functional Exchange Rate'), ':</label>
			<input class="number" maxlength="12" name="FunctionalExRate" pattern="[0-9\.,]*" required="required" size="14" tabindex="5" type="text" value="', $_SESSION['ReceiptBatch' . $identifier]->FunctionalExRate, '" />
			<fieldhelp>', $SuggestedFunctionalExRateText, ' <i>', __('The exchange rate between the currency of the business (the functional currency) and the currency of the bank account'),  '.</i></fieldhelp>
		</field>';
}

echo '<field>
		<label for="ReceiptType">' . __('Receipt Type') . ':</label>
		<select name="ReceiptType" tabindex="6" onchange="ReloadForm(form1.BatchInput)">';

/* The array ReceiptTypes is defined from the setup tab of the main menu under
payment methods - the array is populated from the include file GetPaymentMethods.php */

foreach ($ReceiptTypes as $RcptType) {
	if (isset($_POST['ReceiptType']) AND $_POST['ReceiptType']==$RcptType['paymentid']){
		echo '<option selected="selected" value="' . $RcptType['paymentid'] . '">' . $RcptType['paymentname']  . '</option>';
	} else {
		echo '<option value="' . $RcptType['paymentid'] . '">' . $RcptType['paymentname']  . '</option>';
	}
}
echo '</select>
	</field>';

/* Receipt (Bank Account) info to be inserted on banktrans.ref, varchar(50). */
if (!isset($_SESSION['ReceiptBatch' . $identifier]->BankTransRef)) {
	$_SESSION['ReceiptBatch' . $identifier]->BankTransRef='';
}
echo '<field>
		<label for="BankTransRef">', __('Reference'), ':</label>
		<input maxlength="50" name="BankTransRef" size="52" tabindex="7" type="text" value="', $_SESSION['ReceiptBatch' . $identifier]->BankTransRef,'" />
		<fieldhelp><i>', __('Reference on Bank Transactions Inquiry'), '.</i></fieldhelp>
	</field>';

/* Receipt (Bank Account) info to be inserted on gltrans.narrative, varchar(200). */
if (!isset($_SESSION['ReceiptBatch' . $identifier]->Narrative)) {
	$_SESSION['ReceiptBatch' . $identifier]->Narrative='';
}
if (!isset($_POST['Currency'])){
	$_POST['Currency'] = $_SESSION['CompanyRecord']['currencydefault'];
}
echo '<field>
		<label for="BatchNarrative">', __('Narrative'), ':</label>
		<input maxlength="200" name="BatchNarrative" size="52" tabindex="8" type="text" value="', $_SESSION['ReceiptBatch' . $identifier]->Narrative, '" />
		<fieldhelp><i>', __('Narrative on General Ledger Account Inquiry'), '.</i></fieldhelp>
	</field>
	</fieldset>
	<input name="PreviousCurrency" type="hidden" value="', $_POST['Currency'], '" />
	<tr>
		<td colspan="3">
		<div class="centre">
			<input name="BatchInput" tabindex="9" type="submit" value="', __('Accept'), '" />
		</div>
		</td>
	</tr>';

if (isset($_SESSION['ReceiptBatch' . $identifier])){
	/* Now show the entries made so far */
	if (!$BankAccountEmpty) {
		if (!isset($ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['paymentname'])) {
			$PaymentTypeString = '';
		} else {
			$PaymentTypeString = $ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['paymentname'];

		}
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . __('Banked') . '" alt="" />
			 ' . ' ' . $PaymentTypeString . ' - ' . __('Banked into the') . " " .
				$_SESSION['ReceiptBatch' . $identifier]->BankAccountName . ' ' . __('on') . ' ' . $_SESSION['ReceiptBatch' . $identifier]->DateBanked . '</p>';
	}

	$BatchTotal = 0;

	if ($_GET['Type'] == 'Customer') {
		// It's a customer receipt

		echo '<table width="90%" class="selection">
			<tr>
				<th>' . __('Amount') . ' ' . __('Received') . '</th>
				<th>' . __('Discount') . '</th>
				<th>' . __('Customer') . '</th>
				<th>' . __('Narrative') . '</th>
			</tr>';

		foreach ($_SESSION['ReceiptBatch' . $identifier]->Items as $ReceiptItem) {

			echo '<tr>
					<td class="number">' . locale_number_format($ReceiptItem->Amount,$_SESSION['ReceiptBatch' . $identifier]->CurrDecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($ReceiptItem->Discount,$_SESSION['ReceiptBatch' . $identifier]->CurrDecimalPlaces) . '</td>
					<td>' . stripslashes($ReceiptItem->CustomerName) . '</td>
					<td>' .  stripslashes($ReceiptItem->Narrative) . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete='     . urlencode($ReceiptItem->ID)
																							. '&Type='       . urlencode($_GET['Type'])
																						.	 '&identifier=' . urlencode($identifier) . '">'
																							. __('Delete') . '</a></td>
				</tr>';
			$BatchTotal= $BatchTotal + $ReceiptItem->Amount;
		}
	} else {
			// It's a GL receipt
		echo '<table width="90%" class="selection">
			<tr>
				<th>' . __('Amount') . ' ' . __('Received') . '</th>
				<th>' . __('GL Code') . '</th>
				<th>' . __('Narrative') . '</th>
				<th>' . __('Tag') . '</th>
			</tr>';

		foreach ($_SESSION['ReceiptBatch' . $identifier]->Items as $ReceiptItem) {

			$TagDescriptions = GetDescriptionsFromTagArray($ReceiptItem->tag);

			$SQL = "SELECT accountname FROM chartmaster WHERE accountcode='" . $ReceiptItem->GLCode . "'";
			$Result = DB_query($SQL);
			$MyRow=DB_fetch_array($Result);

			echo '<tr>
					<td class="number">' . locale_number_format($ReceiptItem->Amount,$_SESSION['ReceiptBatch' . $identifier]->CurrDecimalPlaces) . '</td>
					<td>' . $ReceiptItem->GLCode.' - '.$MyRow['accountname'] . '</td>
					<td>' .  stripslashes($ReceiptItem->Narrative) . '</td>
					<td>' .  $TagDescriptions . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete='     . urlencode($ReceiptItem->ID)
																								. '&Type='       . urlencode($_GET['Type'])
																								. '&identifier=' . urlencode($identifier) . '">'
																								. __('Delete') . '</a></td>
				</tr>';
			$BatchTotal= $BatchTotal + $ReceiptItem->Amount;
		}
	}

	echo '<tr>
			<td class="number"><b>' . locale_number_format($BatchTotal,$_SESSION['ReceiptBatch' . $identifier]->CurrDecimalPlaces) . '</b></td>
		</tr>
		</table>';
}

/*this next block of ifs deals with what information to display for input into the form
the info depends on where the user is up to ie the first stage is to select a bank
account, currency being banked and a batch number - or start a new batch by leaving the batch no blank
and a date for the banking. The second stage is to select a customer or GL account.
Finally enter the amount */


/*if a customer has been selected (and a receipt batch is underway)
then set out the customers account summary */


if (isset($_SESSION['CustomerRecord' . $identifier])
		AND $_SESSION['CustomerRecord' . $identifier]['currcode'] != $_SESSION['ReceiptBatch' . $identifier]->Currency){
	prnMsg(__('The selected customer does not trade in the currency of the receipt being entered - either the currency of the receipt needs to be changed or a different customer selected'),'warn');
	unset($_SESSION['CustomerRecord' . $identifier]);
}


if (isset($_SESSION['CustomerRecord' . $identifier])
		AND isset($_POST['CustomerID'])
		AND $_POST['CustomerID']!=''
		AND isset($_SESSION['ReceiptBatch' . $identifier])){
/*a customer is selected  */

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . __('Customer') . '" alt="" />' . ' ' . $_SESSION['CustomerRecord' . $identifier]['name'] . ' - (' . __('All amounts stated in') . ' ' . $_SESSION['CustomerRecord' . $identifier]['currency'] . ')' . __('Terms') . ': ' . $_SESSION['CustomerRecord' . $identifier]['terms'] . '<br/>' . __('Credit Limit') . ': ' . locale_number_format($_SESSION['CustomerRecord'.$identifier]['creditlimit'],0) . '  ' . __('Credit Status') . ': ' . $_SESSION['CustomerRecord'.$identifier]['reasondescription'];

	if ($_SESSION['CustomerRecord' . $identifier]['dissallowinvoices']!=0){
	   echo '<br />
			<font color="red" size="4"><b>' . __('ACCOUNT ON HOLD') . '</font></b>
			<br/>';
	}

	echo '<table width="90%" class="selection">
			<tr>
				<th width="20%">' . __('Total Balance') . '</th>
				<th width="20%">' . __('Current') . '</th>
				<th width="20%">' . __('Now Due') . '</th>
				<th width="20%">' . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . __('Days Overdue') . '</th>
				<th width="20%">' . __('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . __('Days Overdue') . '</th>
				<th width="20%">' . __('Customer Transaction Inquiry') . '</th>
			</tr>';

	echo '<tr>
		<td class="number">' . locale_number_format($_SESSION['CustomerRecord' . $identifier]['balance'],$_SESSION['CustomerRecord' . $identifier]['currdecimalplaces']) . '</td>
		<td class="number">' . locale_number_format(($_SESSION['CustomerRecord' . $identifier]['balance'] - $_SESSION['CustomerRecord' . $identifier]['due']),$_SESSION['CustomerRecord' . $identifier]['currdecimalplaces']) . '</td>
		<td class="number">' . locale_number_format(($_SESSION['CustomerRecord' . $identifier]['due']-$_SESSION['CustomerRecord' . $identifier]['overdue1']),$_SESSION['CustomerRecord' . $identifier]['currdecimalplaces']) . '</td>
		<td class="number">' . locale_number_format(($_SESSION['CustomerRecord' . $identifier]['overdue1']-$_SESSION['CustomerRecord' . $identifier]['overdue2']) ,$_SESSION['CustomerRecord' . $identifier]['currdecimalplaces']) . '</td>
		<td class="number">' . locale_number_format($_SESSION['CustomerRecord' . $identifier]['overdue2'],$_SESSION['CustomerRecord' . $identifier]['currdecimalplaces']) . '</td>
		<td><a href="' . $RootPath . '/CustomerInquiry.php?CustomerID=' . $_POST['CustomerID'] . '&Status=0" target="_blank">' . __('Inquiry') . '</td>
		</tr>
		</table>';

	echo '<fieldset>';
	echo '<legend>', __('Receipt Details'), '</legend>';

	if ($_SESSION['CustomerRecord' . $identifier]['pymtdiscount'] > $ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['percentdiscount']) {
		$DisplayDiscountPercent = locale_number_format($_SESSION['CustomerRecord' . $identifier]['pymtdiscount']*100,2) . '%';
	} else {
		$DisplayDiscountPercent = locale_number_format($ReceiptTypes[$_SESSION['ReceiptBatch' . $identifier]->ReceiptType]['percentdiscount']*100,2) . '%';
	}

	echo '<input type="hidden" name="CustomerID" value="' . $_POST['CustomerID'] . '" />';
	echo '<input type="hidden" name="CustomerName" value="' . $_SESSION['CustomerRecord' . $identifier]['name'] . '" />';

}

if (isset($_POST['GLEntry']) AND isset($_SESSION['ReceiptBatch' . $identifier])){
	/* Set up a heading for the transaction entry for a GL Receipt */
	echo '<fieldset>
			<legend>' . __('General Ledger Receipt Entry') . '</legend>';

	//Select the tag
	echo '<field>
			<label for="tag[]">', __('Select Tag(s)'), ':</label>
			<select multiple="multiple" name="tag[]">';

	$SQL = "SELECT tagref,
					tagdescription
				FROM tags
				ORDER BY tagref";

	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref']) {
			echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
		} else {
			echo '<option value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', __('Select any number of tags to associate this receipt with - use the ctrl button to pick multiple tags.'), '</fieldhelp>
	</field>';

// End select tag

	/*now set up a GLCode field to select from avaialble GL accounts */
	echo '<field>
			<label for="GLCode">' . __('GL Account') . ':</label>
			<select tabindex="8" name="GLCode">';

	$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname
			FROM chartmaster
				INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canupd=1
			ORDER BY chartmaster.accountcode";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0){
		echo '</select>
			<fieldhelp>' . __('No General ledger accounts have been set up yet') . ' - ' . __('receipts cannot be entered against GL accounts until the GL accounts are set up') . '</fieldhelp>
		</field>';
	} else {
		echo '<option value=""></option>';
		while ($MyRow=DB_fetch_array($Result)){
			if ($_POST['GLCode']==$MyRow['accountcode']){
				echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . $MyRow['accountname'] . '</option>';
			} else {
			echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . $MyRow['accountname'] . '</option>';
			}
		}
		echo '</select>
			</field>';
	}
}

/*if either a customer is selected or its a GL Entry then set out
the fields for entry of receipt amt, disc, payee details, narrative */

if (((isset($_SESSION['CustomerRecord' . $identifier])
		AND isset($_POST['CustomerID'])
		AND $_POST['CustomerID']!='')
			OR isset($_POST['GLEntry']))
		AND isset($_SESSION['ReceiptBatch' . $identifier])){

	if (!isset($_POST['Amount'])) {
		$_POST['Amount']=0;
	}
	if (!isset($_POST['Discount'])) {
		$_POST['Discount']=0;
	}
	if (!isset($_POST['PayeeBankDetail'])) {
		$_POST['PayeeBankDetail']='';
	}
	if (!isset($_POST['Narrative'])) {
		$_POST['Narrative']='';
	}
	echo '<field>
			<label for="Amount">' . __('Amount of Receipt') . ':</label>
			<input tabindex="9" type="text" name="Amount" required="required" maxlength="12" size="13" class="number" value="' . $_POST['Amount'] . '" />
		</field>';

	if (!isset($_POST['GLEntry'])){
		echo '<field>
				<label for="Discount">' . __('Amount of Discount') . ':</label>
				<input tabindex="10" type="text" name="Discount" maxlength="12" size="13" class="number" value="' . $_POST['Discount'] . '" />
				<fieldhelp>' . __('agreed prompt payment discount is') . ' ' . $DisplayDiscountPercent . '</fieldhelp>
			</field>';
	} else {
		echo '<input tabindex="11" type="hidden" name="Discount" value="0" />';
	}

	echo '<field>
			<label for="PayeeBankDetail">' . __('Payee Bank Details') . ':</label>
			<input tabindex="12" type="text" name="PayeeBankDetail" maxlength="22" size="20" value="' . $_POST['PayeeBankDetail'] . '" />
		</field>
		<field>
			<label for="Narrative">' . __('Narrative') . ':</label>
			<textarea name="Narrative"  cols="40" rows="1"></textarea>
		</field>
		</fieldset>
		<div class="centre">
			<input tabindex="14" type="submit" name="Process" value="' . __('Accept') . '" />
			<input tabindex="15" type="submit" name="Cancel" value="' . __('Cancel') . '" />
		</div>';

} elseif (isset($_SESSION['ReceiptBatch' . $identifier])
			AND !isset($_POST['GLEntry'])){

	/*Show the form to select a customer */
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . __('Customer') . '" alt="" />' . ' ' . __('Select a Customer') . '</p>
		<fieldset>
		<tr>
			<td>' . __('Text in the Customer') . ' ' . '<b>' . __('name') . '</b>:</td>
			<td><input tabindex="9" type="text" name="Keywords" size="15" maxlength="25" /></td>
			<td><b>' . __('OR') . ' </b></td>
			<td>' . __('Text extract in the Customer') . ' ' . '<b>' . __('code') . '</b>:</td>
			<td><input tabindex="10" type="text" name="CustCode" data-type="no-illegal-chars" title="' . __('Enter an extract of the customer code to search for. Customer codes can contain any alpha-numeric character or underscore') . '" size="10" maxlength="18" /></td>
			<td><b>' . __('OR') . ' </b></td>
			<td>' . __('Customer invoice number') . ':</td>
			<td><input tabindex="11" type="text" name="CustInvNo" class="integer" size="8" maxlength="8" /></td>
		</tr>
		</fieldset>
		<div class="centre">
			<input tabindex="11" type="submit" name="Search" value="' . __('Search Now') . '" />
			<input tabindex="12" type="submit" name="GLEntry" value="' . __('Enter A GL Receipt') . '" />
		</div>';

	if (isset($CustomerSearchResult)) {

		echo '<table class="selection">
				<tr>
					<th>' . __('Code') . '</th>
					<th>' . __('Customer Name') . '</th>
				</tr>';

		while ($MyRow=DB_fetch_array($CustomerSearchResult)) {

			echo '<tr class="striped_row">
					<td><input tabindex="'. strval(12+$j).'" type="submit" name="Select" value="', $MyRow['debtorno'], '" /></td>
					<td>', $MyRow['name'], '</td>
				</tr>';

	//end of page full new headings if
		}
	//end of while loop

		echo '</table>';

	}	//end if results to show

}
if (isset($_SESSION['ReceiptBatch' . $identifier]->Items) AND count($_SESSION['ReceiptBatch' . $identifier]->Items) > 0){
	echo '<div class="centre">
			<input tabindex="13" type="submit" name="CommitBatch" value="' . __('Accept and Process Batch') . '" />
		</div>';
}
echo '</form>';
include('includes/footer.php');

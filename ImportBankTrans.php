<?php

/* $Id: ImportBankTrans.php 4213 2010-12-22 14:33:20Z tim_schofield $*/
include('includes/DefineImportBankTransClass.php');
include ('includes/session.inc');

$Title = _('Import Bank Transactions');

include ('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/*
Read in the flat file one line at a time
parse the data in the line of text from the flat file to read the bank transaction into an SESSION array of banktransactions objects
*/

if (!isset($_FILES['ImportFile']) AND !isset($_SESSION['Statement'])) {
    echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>
			 <tr>
				 <td>' .  _('MT940 format Bank Statement File to import') . '</td>
	             <td><input type="file" id="ImportFile" autofocus="autofocus" required="required" title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile"></td>
			 </tr>
        </table>';
	echo '<div class="centre"><input type="submit" name="Import" value="Process"></div>';
	echo '</form>';

} elseif (isset($_POST['Import'])){

	$result	= $_FILES['ImportFile']['error'];
 	$ReadTheFile = 'Yes'; //Assume all is well to start off with

	 //But check for the worst
    if ($_FILES['ImportFile']['size'] > (1024*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed is 1 megabyte'),'warn');
		$ReadTheFile ='No';
	} 
	
	/*elseif ( $_FILES['ImportFile']['type'] != 'text/plain' ) {  //File Type Check
		prnMsg( _('A plain text file is expected, this file is a') . ' ' . $_FILES['ImportFile']['type'],'warn');
		$ReadTheFile ='No';
	} */

	if ($ReadTheFile=='No'){
        prnMsg(_('The MT940 bank statement file cannot be imported and processed'),'error');
        include('includes/footer.inc');
        exit;
	}
    $fp = fopen($_FILES['ImportFile']['tmp_name'], 'r');
    
    $TransactionLine = false;
	$i=0;
	$_SESSION['Statement'] = new BankStatement;
	$_SESSION['Trans'] = array();
	
	$_SESSION['Statement']->FileName = $_FILES['ImportFile']['tmp_name'];
	
	while ($LineText = fgets($fp)){ /* get each line of the order file */
		/*
		 * This block of code could be in an include and different includes included depending on which type of transaction file is being imported 
		 * */
		
  		  if (substr($LineText,0,4)==':20:'){ //Timestamp of report MT940 generation
  			$_SESSION['Statement']->ReportCreated = substr($LineText,4); //in format DDDHHMM where DDD is the number of day in year and HHMM is the time
  			$TransactionLine = false;
  		  }
          if (substr($LineText,0,4)==':25:'){//The account number in IBAN format
             $_SESSION['Statement']->AccountNumber = trim(substr($LineText,4));
             $TransactionLine = false;
          }
          if (substr($LineText,0,5)==':28C:'){//The statement number
             $_SESSION['Statement']->StatementNumber = trim(substr($LineText,5));
             $TransactionLine = false;
          }
          if (substr($LineText,0,6)==':NS:22'){//The account owner name
             $_SESSION['Statement']->AccountOwner = trim(substr($LineText,6));
             $TransactionLine = false;
          }
          if (substr($LineText,0,6)==':NS:23'){//The account name
             $_SESSION['Statement']->AccountName = trim(substr($LineText,6));
             $TransactionLine = false;
          }
          if (substr($LineText,0,5)==':60F:'){//The account opening balance
             $DebitOrCredit = substr($LineText,5,1); //D or C
             $_SESSION['Statement']->OpeningDate = ConvertSQLDate('20' . substr($LineText,6,2) . '-' . substr($LineText,8,2) . '-' . substr($LineText,10,2));
             $_SESSION['Statement']->CurrCode = substr($LineText,12,3);
             if ($DebitOrCredit =='D'){
                $_SESSION['Statement']->OpeningBalance = doubleval(str_replace(',','.',substr($LineText,15)));
             } else {
                $_SESSION['Statement']->OpeningBalance = doubleval('-' . str_replace(',','.',substr($LineText,15)));
             }
             $TransactionLine = false;
          }
          if (substr($LineText,0,4)==':61:'){//It's a transaction line
				$TransactionLine = true;
				$TransDate = ConvertSQLDate('20' . substr($LineText,4,2) . '-' . substr($LineText,6,2) . '-' . substr($LineText,8,2));
				$DebitOrCredit = substr($LineText,10,1); //D or C or R
				if ($DebitOrCredit =='R'){ //then it is a 2 character reversal
					if (substr($LineText,10,2)=='RC'){
						$DebitOrCredit ='D';
					} else {
						$DebitOrCredit ='C';
					}
					if ($DebitOrCredit =='D'){
						$TransAmount = doubleval(str_replace(',','.',substr($LineText,12,-10)));
					} else {
						$TransAmount = doubleval('-' . str_replace(',','.',substr($LineText,12,-10)));
					}
				} else { // it will be either D or C
					if ($DebitOrCredit =='D'){
						$TransAmount = doubleval(str_replace(',','.',substr($LineText,11,-10)));
					} else {
						$TransAmount = doubleval('-' . str_replace(',','.',substr($LineText,11,-10)));
					}
				}
				$i++;
				$_SESSION['Trans'][$i] = new BankTrans($TransDate,$TransAmount) ;
          }
          if (substr($LineText,0,4)==':86:'){
             if ($TransactionLine) {
                $_SESSION['Trans'][$i]->Code = substr($LineText,4,3);
                $_SESSION['Trans'][$i]->Description = substr($LineText,7);
             }
          }
          
          if (substr($LineText,0,1)!=':' AND $TransactionLine){ 
			  //then it is the continuation of an :86: line
			  $_SESSION['Trans'][$i]->Description .= $LineText;
		  }
		  
          if (substr($LineText,0,5)==':62F:'){
             $DebitOrCredit = substr($LineText,5,1); //D or C
             $_SESSION['Statement']->ClosingDate = ConvertSQLDate('20' . substr($LineText,6,2) . '-' . substr($LineText,8,2) . '-' . substr($LineText,10,2));
             $CurrCode = substr($LineText,12,3);
             if ($DebitOrCredit =='D'){
                $_SESSION['Statement']->ClosingBalance = doubleval(str_replace(',','.',substr($LineText,15)));
             } else {
                $_SESSION['Statement']->ClosingBalance = doubleval('-' . str_replace(',','.',substr($LineText,15)));
             }
             $TransactionLine = false;
          }
          /* end of MT940 specific import code - that could be in an include if we get other file formats
           * */
	} /*end while get next line of message */
	
	/* Look to match up the account for which transactions are being imported with a bank account in webERP */
	$sql = "SELECT accountcode,
					bankaccountname,
					decimalplaces,
					rate
			FROM bankaccounts INNER JOIN currencies
			ON bankaccounts.currcode=currencies.currabrev
			WHERE bankaccountnumber " . LIKE . " '" . $_SESSION['Statement']->AccountNumber ."' 
			AND currcode = '" . $_SESSION['Statement']->CurrCode . "'";
		
	$ErrMsg = _('Could not retrieve bank accounts that match with the statement being imported');
	
	$result = DB_query($sql,$db,$ErrMsg);
	if (DB_num_rows($result)==0){ //but check for the worst!
		//there is no bank account set up for the bank account being imported
		prnMsg(_('The account') . ' ' . $_SESSION['Statement']->AccountNumber . ' ' . _('is not defined as a bank account of the business. No imports can be processed'), 'warn');
	} else {
		$BankAccountRow = DB_fetch_array($result);
		$_SESSION['Statement']->BankGLAccount = $BankAccountRow['accountcode'];
		$_SESSION['Statement']->BankAccountName = $BankAccountRow['bankaccountname'];
		$_SESSION['Statement']->CurrDecimalPlaces = $BankAccountRow['decimalplaces'];
		$_SESSION['Statement']->ExchangeRate = $BankAccountRow['rate'];
	
		/* Now check to see if each transaction has already been entered */
		for($i=1;$i<=count($_SESSION['Trans']);$i++){
		
			$SQL = "SELECT banktransid FROM banktrans
					WHERE transdate='" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "'
					AND amount='" . $_SESSION['Trans'][$i]->Amount . "' 
					AND bankact='" . $_SESSION['Statement']->BankGLAccount . "'";
			$result = DB_query($SQL,$db,_('There was a problem identifying a matching bank transaction'));
			if (DB_num_rows($result)>0){
				$myrow = DB_fetch_array($result);
				$_SESSION['Trans'][$i]->BankTransID = $myrow['banktransid'];
			}
		}
	} //end if there is a matching bank account in the system
} //end if read in transaction/statement

if (isset($_POST['ProcessBankTrans'])){
	$InputError = false; //assume the best
	if ($_SESSION['Statement']->CurrCode != $_SESSION['CompanyRecord']['currencydefault']
		AND $_POST['ExchangeRate']==1){
		prnMsg(_('It is necessary to enter the exchange rate to convert the bank receipts and payments into local currency for the purposes of calculating the general ledger entries necessary. The currency of this bank account is not the same as the company functional currency so an exchange rate of 1 is inappropriate'),'error');
		$InputError = true;
	}
	if (!is_numeric($_POST['ExchangeRate'])){
		prnMsg(_('The exchange rate is expected to be the number of the bank account currency that would purchase one unit of the company functional currency. A number is expected'),'error');
		$InputError = true;
	}
	if ($InputError == false){
		/*This is it - process the data into the DB 
		 * First check to see if the item is flagged as matching an existing bank transaction - if it does and there is no analysis of the transaction then we need to flag the existing bank transaction as matched off the bank statement for reconciliation purposes.
		 * Then, if the transaction is analysed:
			* 1. create the bank transaction
			* 2. if it is a debtor receipt create a debtortrans systype 12 against the selected customer
			* 3. if it is a supplier payment create a supptrans systype 22 against the selected supplier
			* 4. create the gltrans for either the gl analysis or the debtor/supplier receipt/payment created
		*/
		
		for($i=1;$i<=count($_SESSION['Trans']);$i++){
			DB_Txn_Begin($db);
			if ($_SESSION['Trans'][$i]->DebtorNo!='' OR
				$_SESSION['Trans'][$i]->SupplierID!='' OR
				$_SESSION['Trans'][$i]->GLTotal == $_SESSION['Trans'][$i]->Amount){
				/*A Debtor or Supplier is entered or there is GL analysis for the bank trans
				 */
				$PeriodNo = GetPeriod($_SESSION['Trans'][$i]->ValueDate,$db);
				$InsertBankTrans = true;
			} elseif ($_SESSION['Trans'][$i]->BankTransID!=0) {
				//Update the banktrans to show it has cleared the bank
				$result = DB_query("UPDATE banktrans SET amountcleared=amount 
									WHERE banktransid = '" . $_SESSION['Trans'][$i]->BankTransID . "'",
									$db,
									_('Could not update the bank transaction as cleared'),
									_('The SQL that failed to update the bank transaction as cleared was'),
									true);
				$InsertBankTrans = false;
			} else {
				$InsertBankTrans = false;
			}
			
			if ($_SESSION['Trans'][$i]->Amount >0){ //its a receipt
	
				if ($_SESSION['Trans'][$i]->DebtorNo!='') {
					$TransType = 12;
					$TransNo = GetNextTransNo(12,$db); //debtors receipt
					/* First insert the debtortrans record */
					$result = DB_query("INSERT INTO debtortrans (transno,
																type,
																debtorno,
																trandate,
																inputdate,
																prd,
																rate,
																reference,
																invtext,
																ovamount)
										VALUES ('" . $TransNo . "',
												'" . $TransType . "',
												'" . $_SESSION['Trans'][$i]->DebtorNo . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . Date('Y-m-d h:m:s') . "',
												'" . $PeriodNo . "',
												'" . $_POST['ExchangeRate'] . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . -$_SESSION['Trans'][$i]->Amount . "')",
											$db,
											_('Could not insert the customer transaction'),
											_('The SQL used to insert the debtortrans was'),
											true);
					/*Now update the debtors master for the last payment date */
					$result = DB_query("UPDATE debtorsmaster
										SET lastpaiddate = '" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
											lastpaid='" . $_SESSION['Trans'][$i]->Amount ."'
										WHERE debtorno='" . $_SESSION['Trans'][$i]->DebtorNo . "'",
										$db,
										_('Could not update the last payment date and amount paid'),
										_('The SQL that failed to update the debtorsmaster was'),
										true);
					
					/* Now insert the gl trans to credit debtors control and debit bank account */
					/*First credit debtors control from CompanyRecord */
					$result = DB_query("INSERT INTO gltrans (type,
												 			typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
										VALUES (12,
												'" . $TransNo . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . -round($_SESSION['Trans'][$i]->Amount/$_POST['ExchangeRate'],$_SESSION['CompanyRecord']['decimalplaces']+1) . "')",
										$db,
										_('Cannot insert a GL entry for the receipt because'),
										_('The SQL that failed to insert the receipt GL entry was'),
										true);
					/*Now debit the bank account from $_SESSION['Statement']->BankGLAccount */
					$result = DB_query("INSERT INTO gltrans (type,
												 			typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
										VALUES (12,
												'" . $TransNo . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['Statement']->BankGLAccount . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . round($_SESSION['Trans'][$i]->Amount/$_POST['ExchangeRate'],$_SESSION['CompanyRecord']['decimalplaces']+1) . "')",
										$db,
										_('Cannot insert a GL entry for the receipt because'),
										_('The SQL that failed to insert the receipt GL entry was'),
										true);
																
				} elseif ($_SESSION['Trans'][$i]->GLTotal == $_SESSION['Trans'][$i]->Amount){
					$TransType=2; //gl receipt
					$TransNo = GetNextTransNo(2,$db); 
					foreach ($_SESSION['Trans'][$i]->GLEntries as $GLAnalysis){
						/*Credit each analysis account */
						$result = DB_query("INSERT INTO gltrans (type,
													 			typeno,
																trandate,
																periodno,
																account,
																narrative,
																amount)
											VALUES (2,
													'" . $TransNo . "',
													'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
													'" . $PeriodNo . "',
													'" . $GLAnalysis->GLCode . "',
													'" . DB_escape_string($GLAnalysis->Narrative . ' ' . $_SESSION['Trans'][$i]->Description) . "',
													'" . -round($GLAnalysis->Amount/$_POST['ExchangeRate'],$_SESSION['CompanyRecord']['decimalplaces']+1) . "')",
											$db,
											_('Cannot insert a GL entry for the receipt gl analysis because'),
											_('The SQL that failed to insert the gl analysis of this receipt was'),
											true);

					} //end loop around GLAnalysis
					/*Now debit the bank account from $_SESSION['Statement']->BankGLAccount */
					$result = DB_query("INSERT INTO gltrans (type,
												 			typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
										VALUES (2,
												'" . $TransNo . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['Statement']->BankGLAccount . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . round($_SESSION['Trans'][$i]->Amount/$_POST['ExchangeRate'],$_SESSION['CompanyRecord']['decimalplaces']+1) . "')",
										$db,
										_('Cannot insert a GL entry for the receipt because'),
										_('The SQL that failed to insert the receipt GL entry was'),
										true);
				} 
			} else { //its a payment
				if ($_SESSION['Trans'][$i]->SupplierID!='') { //its a supplier payment
					$TransType = 22;
					$TransNo = GetNextTransNo(22,$db); 
					$result = DB_query("INSERT INTO supptrans (transno,
																type,
																supplierno,
																trandate,
																inputdate,
																duedate,
																rate,
																suppreference,
																transtext,
																ovamount)
										VALUES ('" . $TransNo . "',
												'" . $TransType . "',
												'" . $_SESSION['Trans'][$i]->SupplierID . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . Date('Y-m-d h:m:s') . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . $_POST['ExchangeRate'] . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . $_SESSION['Trans'][$i]->Amount . "')",
											$db,
											_('Could not insert the supplier transaction'),
											_('The SQL used to insert the supptrans was'),
											true);
					/*Now update the suppliers master for the last payment date */
					$result = DB_query("UPDATE suppliers
										SET lastpaiddate = '" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
											lastpaid='" . $_SESSION['Trans'][$i]->Amount ."'
										WHERE supplierid='" . $_SESSION['Trans'][$i]->SupplierID . "'",
										$db,
										_('Could not update the supplier last payment date and amount paid'),
										_('The SQL that failed to update the supplier with the last payment amount and date was'),
										true);
					/* Now insert the gl trans to debit creditors control and credit bank account */
					/*First debit creditors control from CompanyRecord */
					$result = DB_query("INSERT INTO gltrans (type,
												 			typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
										VALUES (22,
												'" . $TransNo . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['CompanyRecord']['creditorsact'] . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . round(-$_SESSION['Trans'][$i]->Amount/$_POST['ExchangeRate'],$_SESSION['CompanyRecord']['decimalplaces']+1) . "')",
										$db,
										_('Cannot insert a GL entry for the supplier payment to creditors control because'),
										_('The SQL that failed to insert the creditors control GL entry was'),
										true);
					/*Now credit the bank account from $_SESSION['Statement']->BankGLAccount 
					 * note payments are recorded as negatives in the import */
					$result = DB_query("INSERT INTO gltrans (type,
												 			typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
										VALUES (22,
												'" . $TransNo . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['Statement']->BankGLAccount . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . round($_SESSION['Trans'][$i]->Amount/$_POST['ExchangeRate'],$_SESSION['CompanyRecord']['decimalplaces']+1) . "')",
										$db,
										_('Cannot insert a GL entry for the supplier payment because'),
										_('The SQL that failed to insert the supplier payment GL entry to the bank account was'),
										true);
					
				} elseif($_SESSION['Trans'][$i]->GLTotal == $_SESSION['Trans'][$i]->Amount){
					//its a GL payment
					$TransType = 1; //gl payment
					$TransNo = GetNextTransNo(1,$db); 
					foreach ($_SESSION['Trans'][$i]->GLEntries as $GLAnalysis){
						/*Debit each analysis account  note payments are recorded as negative so need negative negative to make a debit (positive)*/
						$result = DB_query("INSERT INTO gltrans (type,
													 			typeno,
																trandate,
																periodno,
																account,
																narrative,
																amount)
											VALUES (1,
													'" . $TransNo . "',
													'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
													'" . $PeriodNo . "',
													'" . $GLAnalysis->GLCode . "',
													'" . DB_escape_string($GLAnalysis->Narrative . ' ' . $_SESSION['Trans'][$i]->Description) . "',
													'" . -round($GLAnalysis->Amount/$_POST['ExchangeRate'],$_SESSION['CompanyRecord']['decimalplaces']+1) . "')",
											$db,
											_('Cannot insert a GL entry for the payment gl analysis because'),
											_('The SQL that failed to insert the gl analysis of this payment was'),
											true);

					} //end loop around GLAnalysis
					/*Now credit the gl account from $_SESSION['Statement']->BankGLAccount 
					 * Note payments are negatives*/
					$result = DB_query("INSERT INTO gltrans (type,
												 			typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
										VALUES (1,
												'" . $TransNo . "',
												'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['Statement']->BankGLAccount . "',
												'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
												'" . round($_SESSION['Trans'][$i]->Amount/$_POST['ExchangeRate'],$_SESSION['CompanyRecord']['decimalplaces']+1) . "')",
										$db,
										_('Cannot insert a GL entry for the payment because'),
										_('The SQL that failed to insert the payment GL entry was'),
										true);
				}
				 
			} //end if its a payment
			if ($InsertBankTrans==true){
			/* Now insert the bank transaction if necessary */
			/* it is not possible to import transaction that were originally in another currency converted to the currency of the bank account by the bank - these entries would need to be done through the usual method */
			
				$result=DB_query("INSERT INTO banktrans (transno,
														type,
														bankact,
														ref,
														exrate,
														functionalexrate,
														transdate,
														banktranstype,
														amount,
														currcode,
														amountcleared)
								VALUES (
									'" . $TransNo . "',
									'" . $TransType . "',
									'" . $_SESSION['Statement']->BankGLAccount . "',
									'" . DB_escape_string($_SESSION['Trans'][$i]->Description) . "',
									'1',
									'" . $_POST['ExchangeRate'] . "',
									'" . FormatDateForSQL($_SESSION['Trans'][$i]->ValueDate) . "',
									'" . _('Imported') . "',
									'" . $_SESSION['Trans'][$i]->Amount . "',
									'" . $_SESSION['Statement']->CurrCode . "',
									'" . $_SESSION['Trans'][$i]->Amount . "')",
								$db,
								_('Could not insert the bank transaction'),
								_('The SQL that failed to insert the bank transaction was'),
								true);
			}
			DB_Txn_Commit($db); // complete this bank transactions posting
		} //end loop around the transactions
		echo '<p />';
		prnMsg(_('Completed the importing of analysed bank transactions'),'info');
		unset($_SESSION['Trans']->GLEntries);
		unset($_SESSION['Trans']);
		unset($_SESSION['Statement']);
	} // there were no input errors - the exchange rate was entered
}



if (isset($_SESSION['Statement'])){

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" >';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if (!isset($_SESSION['Statement']->BankGLAccount)){
		$AllowImport = false;
	} else {
		$AllowImport = true;
	}
	/* show the statement in any event - just don't have links to process transactions if NOT $AllowImport
	*/
	echo '<table class="selection">
			<tr>
				<th colspan="5">' . _('Bank Statement No') . ' ' . $_SESSION['Statement']->StatementNumber . ' ' . _('for') . ' ' . $_SESSION['Statement']->BankAccountName  . ' ' . _('Number') . ' ' . $_SESSION['Statement']->AccountNumber . '</th>
			</tr>
			<tr>
				<th colspan ="3">' . _('Opening Balance as at') . ' ' . $_SESSION['Statement']->OpeningDate . ' ' . _('in') . ' ' .$_SESSION['Statement']->CurrCode . '</th>';
	if ($_SESSION['Statement']->OpeningBalance >=0){
		echo '<th class="number">' . number_format($_SESSION['Statement']->OpeningBalance,$_SESSION['Statement']->CurrDecimalPlaces) . '</th><th></th></tr>';
	} else {
		echo '<th></th><th class="number">' . number_format($_SESSION['Statement']->OpeningBalance,$_SESSION['Statement']->CurrDecimalPlaces) . '</th></tr>';
	}
	for ($i=1; $i<=count($_SESSION['Trans']); $i++){
		
		if ($_SESSION['Trans'][$i]->Amount >0){
			if ($_SESSION['Trans'][$i]->DebtorNo!=''
				OR $_SESSION['Trans'][$i]->GLTotal == $_SESSION['Trans'][$i]->Amount){
				echo '<tr style="background-color: #FFFCCC;">';
			} elseif ($_SESSION['Trans'][$i]->BankTransID!=0) {
				echo '<tr style="background-color: #FFF222;">';
			} else {
				echo '<tr>';
			}
		} else { //its a payment
			if ($_SESSION['Trans'][$i]->SupplierID!=''
				OR $_SESSION['Trans'][$i]->GLTotal == $_SESSION['Trans'][$i]->Amount){
				echo '<tr style="background-color: #FFFCCC;">';
			} elseif ($_SESSION['Trans'][$i]->BankTransID!=0) {
				echo '<tr style="background-color: #FFF222;">';
			} else {
				echo '<tr>';
			}
		}		
		echo '<td>' . $_SESSION['Trans'][$i]->Code . '</td>
				<td>' . $_SESSION['Trans'][$i]->ValueDate . '</td>
				<td>' . $_SESSION['Trans'][$i]->Description . '</td>';
				
		if ($_SESSION['Trans'][$i]->Amount>=0){
			echo '<td class="number">' . number_format($_SESSION['Trans'][$i]->Amount,$_SESSION['Statement']->CurrDecimalPlaces) . '</td><td></td>';
		} else {
			echo '<td></td><td class="number">' . number_format($_SESSION['Trans'][$i]->Amount,$_SESSION['Statement']->CurrDecimalPlaces) . '</td>';
		}
		if ($AllowImport==true) {
			echo '<td><a href="' . $RootPath . '/ImportBankTransAnalysis.php?TransID=' . $i .'">' . _('Analysis')  . '</a></td>';
		}
		echo '</tr>';
	}
	echo '<tr>
			<th colspan="3">' . _('Closing Balance as at') . ' ' . $_SESSION['Statement']->ClosingDate . ' ' . _('in') . ' ' .$_SESSION['Statement']->CurrCode . '</th>';
	if ($_SESSION['Statement']->ClosingBalance>=0){
		echo '<th class="number">' . number_format($_SESSION['Statement']->ClosingBalance,$_SESSION['Statement']->CurrDecimalPlaces) . '</th><th></th>
			</tr>';
	} else {
		echo '<th></th><th class="number">' . number_format($_SESSION['Statement']->ClosingBalance,$_SESSION['Statement']->CurrDecimalPlaces) . '</th>
			</tr>';
	}
	echo '</table>';
	echo '<br />
	<table class="selection">';
	if ($_SESSION['Statement']->CurrCode!=$_SESSION['CompanyRecord']['currencydefault']){
		
		echo '<tr>
				<td>' . _('Exchange Rate to Use When Processing Transactions') . '</td>
				<td><input type="text" class="number" required="required" name="ExchangeRate" value="' . $_SESSION['Statement']->ExchangeRate . '" /></td>
			</tr>';
	} else {
		echo '<input type="hidden" name="ExchangeRate" value="1" />';
	}
	echo '<tr>
			<th colspan="2"><input type="submit" name="ProcessBankTrans" value="' . _('Process Bank Transactions') . '" onclick="return confirm(\'' . _('This process will create bank transactions for ONLY THE ANALYSED transactions shown in yellow above together with the necessary general ledger journals and customer or supplier transactions. Are You Sure?') . '\');" /></th>
		</tr>
		</table>';
}


include ('includes/footer.inc');
?>
<?php

/* $Revision: 1.17 $ */

include('includes/DefineReceiptClass.php');

$PageSecurity = 3;
include('includes/session.inc');

$title = _('Receipt Entry');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$msg='';


if (isset($_POST['CommitBatch'])){

 /* once all receipts items entered, process all the data in the
  session cookie into the DB creating a single banktrans for the whole amount
  of all receipts in the batch and DebtorTrans records for each receipt item
  all DebtorTrans will refer to a single banktrans. A GL entry is created for
  each GL receipt entry and one for the debtors entry and one for the bank
  account debit

  NB allocations against debtor receipts are a seperate exercice

  first off run through the array of receipt items $_SESSION['ReceiptBatch']->Items and
  if GL integrated then create GL Entries for the GL Receipt items
  and add up the non-GL ones for posting to debtors later,
  also add the total discount total receipts*/

   $PeriodNo = GetPeriod($_SESSION['ReceiptBatch']->DateBanked,$db);

   if ($_SESSION['CompanyRecord']==0){
	prnMsg(_('The company has not yet been set up properly') . ' - ' . _('this information is needed to process the batch') . '. ' . _('Processing has been cancelled'),'error');
	include('includes/footer.inc');
	exit;
   }

   /*Make an array of the defined bank accounts */
   $SQL = "SELECT accountcode FROM bankaccounts";
   $result = DB_query($SQL,$db);
   $BankAccounts = array();
   $i=0;
   while ($Act = DB_fetch_row($result)){
 	$BankAccounts[$i]= $Act[0];
	$i++;
   }

   /*Start a transaction to do the whole lot inside */
   $SQL = "BEGIN";
   $result = DB_query($SQL,$db);

   $BatchReceiptsTotal = 0;
   $BatchDiscount = 0;
   $BatchDebtorTotal = 0;

   foreach ($_SESSION['ReceiptBatch']->Items as $ReceiptItem) {

	    if ($ReceiptItem->GLCode !=''){
		if ($_SESSION['CompanyRecord']['gllink_debtors']==1){ /* then enter a GLTrans record */
			 $SQL = "INSERT INTO gltrans (type,
			 			typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount) ";
			 $SQL= $SQL . "VALUES (12,
			 			" . $_SESSION['ReceiptBatch']->BatchNo . ",
						'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "',
						" . $PeriodNo . ",
						" . $ReceiptItem->GLCode . ",
						'" . DB_escape_string($ReceiptItem->Narrative) . "',
						" . -$ReceiptItem->Amount/$_SESSION['ReceiptBatch']->ExRate . ")";
			 $ErrMsg = _('Cannot insert a GL entry for the receipt because');
			 $DbgMsg = _('The SQL that failed to insert the receipt GL entry was');
			 $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		}

		/*check to see if this is a GL posting to another bank account (or the same one)
		if it is then a matching payment needs to be created for this account too */

		if (in_array($ReceiptItem->GLCode, $BankAccounts)) {

			$PaymentTransNo = GetNextTransNo( 1, $db);
			$SQL="INSERT INTO banktrans (transno,
							type,
							bankact,
							ref,
							exrate,
							transdate,
							banktranstype,
							amount,
							currcode) ";
			$SQL= $SQL . "VALUES (" . $PaymentTransNo . ",
						1,
						" . $ReceiptItem->GLCode . ",
						'" . _('Act Transfer') .' - ' . DB_escape_string($ReceiptItem->Narrative) . "',
						" . $_SESSION['ReceiptBatch']->ExRate . " ,
						'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "',
						'" . $_SESSION['ReceiptBatch']->ReceiptType . "',
						" . -$ReceiptItem->Amount . ",
						'" . $_SESSION['ReceiptBatch']->Currency . "'
					)";

			$DbgMsg = _('The SQL that failed to insert the bank transaction was');
			$ErrMsg = _('Cannot insert a bank transaction using the SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

	    } else {
		   /*Accumulate the total debtors credit including discount */
		   $BatchDebtorTotal = $BatchDebtorTotal + (($ReceiptItem->Discount + $ReceiptItem->Amount)/$_SESSION['ReceiptBatch']->ExRate);
		   /*Create a DebtorTrans entry for each customer deposit */

		   $SQL = "INSERT INTO debtortrans (transno,
		   					type,
							debtorno,
							branchcode,
							trandate,
							prd,
							reference,
							tpe,
							rate,
							ovamount,
							ovdiscount,
							invtext) ";
		   $SQL = $SQL . "VALUES (" . $_SESSION['ReceiptBatch']->BatchNo . ",
		   				12,
						'" . $ReceiptItem->Customer . "',
						'',
						'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "',
						" . $PeriodNo . ",
						'" . DB_escape_string($_SESSION['ReceiptBatch']->ReceiptType  . " " . $ReceiptItem->Narrative) . "',
						'',
						" . $_SESSION['ReceiptBatch']->ExRate . ",
						" . -$ReceiptItem->Amount . ",
						" . -$ReceiptItem->Discount . ",
						'" . $ReceiptItem->PayeeBankDetail . "'
					)";
		$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
		$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$SQL = "UPDATE debtorsmaster SET lastpaiddate = '" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "',
						lastpaid=" . $ReceiptItem->Amount ." 
					WHERE debtorsmaster.debtorno='" . $ReceiptItem->Customer . "'";
					
		$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
		$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	    }
	    $BatchDiscount = $BatchDiscount + $ReceiptItem->Discount/$_SESSION['ReceiptBatch']->ExRate;
	    $BatchReceiptsTotal = $BatchReceiptsTotal + $ReceiptItem->Amount/$_SESSION['ReceiptBatch']->ExRate;

   }
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
			VALUES (12,
				" . $_SESSION['ReceiptBatch']->BatchNo . ",
				'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "',
				" . $PeriodNo . ",
				" . $_SESSION['ReceiptBatch']->Account . ",
				'" . DB_escape_string($_SESSION['ReceiptBatch']->Narrative) . "',
				" . $BatchReceiptsTotal . "
			)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction fro the bank account debit was');
		$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                
                /*now enter the BankTrans entry */

                $SQL="INSERT INTO banktrans (type,
   				transno,
				bankact,
				ref,
				exrate,
				transdate,
				banktranstype,
				amount,
				currcode)
                VALUES (12,
                      " . $_SESSION['ReceiptBatch']->BatchNo . ",
                      " . $_SESSION['ReceiptBatch']->Account . ",
                      '" . $_SESSION['ReceiptBatch']->Narrative . "',
                      " . $_SESSION['ReceiptBatch']->ExRate . ",
                      '" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "',
                      '" . $_SESSION['ReceiptBatch']->ReceiptType . "',
                      " . ($BatchReceiptsTotal * $_SESSION["ReceiptBatch"]->ExRate) . ",
                      '" . $_SESSION['ReceiptBatch']->Currency . "'
                        )";
              $DbgMsg = _('The SQL that failed to insert the bank account transaction was');
              $ErrMsg = _('Cannot insert a bank transaction');
              $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
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
			VALUES (12,
				" . $_SESSION['ReceiptBatch']->BatchNo . ",
				'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "',
				" . $PeriodNo . ",
				" . $_SESSION['CompanyRecord']['debtorsact'] . ",
					'" . DB_escape_string($_SESSION['ReceiptBatch']->Narrative) . "',
					" . -$BatchDebtorTotal . "
				)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
			$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

      }

      if ($BatchDiscount!=0){
			/* Now Debit Discount account with discounts allowed*/
		$SQL="INSERT INTO gltrans ( type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount)
			VALUES (12,
				" . $_SESSION['ReceiptBatch']->BatchNo . ",
				'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "',
				" . $PeriodNo . ",
				" . $_SESSION['CompanyRecord']['pytdiscountact'] . ",
					'" . DB_escape_string($_SESSION['ReceiptBatch']->Narrative) . "',
				" . $BatchDiscount . "
			)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction for the payment discount debit was');
		$ErrMsg = _('Cannot insert a GL transaction for the payment discount debit');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	}
   }


   $ErrMsg = _('Cannot commit the changes');
   $DbgMsg = _('The SQL that failed was');
   $result = DB_query('COMMIT',$db,$ErrMsg,$DbgMsg,true);

   echo '<P>';

   prnMsg( _('Receipt batch') . ' ' . $_SESSION['ReceiptBatch']->BatchNo . ' ' . _('has been successfully entered into the database'),'success');

   echo "<BR><A HREF='" . $rootpath . "/PDFBankingSummary.php?BatchNo=" . $_SESSION['ReceiptBatch']->BatchNo . "'>" . _('Print PDF Batch Summary') . "</A>";
   unset($_SESSION['ReceiptBatch']);

} elseif (isset($_POST['BatchInput'])){ //submitted a new batch

/*Need to do a reality check on exchange rate entered initially to ensure sensible to proceed */
	if ($_POST['Currency']!=$_SESSION['CompanyRecord']['currencydefault'] AND $_POST['ExRate']==1){
		prnMsg(_('An exchange rate of 1 is only appropriate for receipts in the companies functional currency - enter an appropriate exchange rate'),'error');
		
	} else {
		
		$_POST['BatchNo'] = GetNextTransNo(12,$db);
		
		
		/*if the session already has a $_SESSION['ReceiptBatch'] set up ... lose it
		and start a fresh! */
		if (isset($_SESSION['ReceiptBatch'])){
		unset($_SESSION['ReceiptBatch']);
		}
		$_SESSION['ReceiptBatch'] = new Receipt_Batch;
		$_SESSION['ReceiptBatch']->BatchNo = $_POST['BatchNo'];
		$_SESSION['ReceiptBatch']->Account = $_POST['BankAccount'];
			if (!Is_Date($_POST['DateBanked'])){
			$_POST['DateBanked'] = Date($_SESSION['DefaultDateFormat']);
			}
		$_SESSION['ReceiptBatch']->DateBanked = $_POST['DateBanked'];
		$_SESSION['ReceiptBatch']->ExRate = $_POST['ExRate'];
		$_SESSION['ReceiptBatch']->ReceiptType = $_POST['ReceiptType'];
		$_SESSION['ReceiptBatch']->Currency = $_POST['Currency'];
		$_SESSION['ReceiptBatch']->Narrative = $_POST['BatchNarrative'];
		$_SESSION['ReceiptBatch']->ID = 1;
		
		$SQL = "SELECT bankaccountname FROM bankaccounts WHERE accountcode=" . $_POST['BankAccount'];
		$result= DB_query($SQL,$db,'','',false,false);
		
		if (DB_error_no($db) !=0) {
			prnMsg(_('The bank account name cannot be retrieved because') . ' - ' . DB_error_msg($db),'error');
			if ($debug==1) {
				echo '<BR>' . _('SQL used to retrieve the bank account name was') . '<BR>' . $sql;
			}
			include ('includes/footer.inc');
			exit;
		} elseif (DB_num_rows($result)==1){
			$myrow = DB_fetch_row($result);
			$_SESSION['ReceiptBatch']->BankAccountName = $myrow[0];
			unset($result);
		} elseif (DB_num_rows($result)==0){
			prnMsg( _('The bank account number') . ' ' . $_POST['BankAccount'] . ' ' . _('is not set up as a bank account'),'error');
			include ('includes/footer.inc');
			exit;
		}
	}

} elseif (isset($_GET['Delete'])){
  /* User hit delete the receipt entry from the batch */
   $_SESSION['ReceiptBatch']->remove_receipt_item($_GET['Delete']);
} elseif (isset($_POST['Process'])){ //user hit submit a new entry to the receipt batch

   $_SESSION['ReceiptBatch']->add_to_batch($_POST['amount'], 
   						$_POST['CustomerID'], 
						$_POST['discount'], 
						$_POST['Narrative'],
						$_POST['GLCode'], 
						$_POST['PayeeBankDetail'], 
						$_POST['CustomerName']);

   /*Make sure the same receipt is not double processed by a page refresh */
   $Cancel = 1;

}

if (isset($Cancel)){
   unset($_SESSION['CustomerRecord']);
   unset($_POST['CustomerID']);
   unset($_POST['CustomerName']);
   unset($_POST['amount']);
   unset($_POST['discount']);
   unset($_POST['Narrative']);
   unset($_POST['PayeeBankDetail']);
}

if (isset($_POST['Search'])){
/*Will only be true if clicked to search for a customer code */

	If ($_POST['Keywords'] AND $_POST['CustCode']) {
		$msg=_('Customer name keywords have been used in preference to the customer code extract entered');
	}
	If ($_POST['Keywords']=="" AND $_POST['CustCode']=="") {
		$msg=_('At least one Customer Name keyword OR an extract of a Customer Code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = "%";
			while (strpos($_POST['Keywords'], " ", $i)) {
				$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
				$i=strpos($_POST['Keywords']," ",$i) +1;
			}
			$SearchString = $SearchString . substr($_POST['Keywords'],$i)."%";
			$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name
				FROM debtorsmaster
				WHERE debtorsmaster.name " . LIKE . " '$SearchString'
				AND debtorsmaster.currcode= '" . $_SESSION['ReceiptBatch']->Currency . "'";

		} elseif (strlen($_POST['CustCode'])>0){
			$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name
				FROM debtorsmaster
				WHERE debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
				AND debtorsmaster.currcode= '" . $_SESSION['ReceiptBatch']->Currency . "'";
		}

		$result = DB_query($SQL,$db,'','',false,false);
		if (DB_error_no($db) !=0) {
			prnMsg(_('The searched customer records requested cannot be retrieved because') . ' - ' . DB_error_msg($db),'error');
			if ($debug==1){
				prnMsg(_('SQL used to retrieve the customer details was') . '<BR>' . $sql,'error');
			}
		} elseif (DB_num_rows($result)==1){
			$myrow=DB_fetch_array($result);
			$Select = $myrow["debtorno"];
			unset($result);
		} elseif (DB_num_rows($result)==0){
			prnMsg( _('No customer records contain the selected text') . ' - ' . _('please alter your search criteria and try again'),'info');
		}

	} //one of keywords or custcode was more than a zero length string
} //end of if search

If (isset($_POST['Select'])){
	$Select = $_POST['Select'];
}

If (isset($Select)) {
/*will only be true if a customer has just been selected by clicking on the customer or only one
customer record returned by the search - this record is then auto selected */

	$_POST['CustomerID']=$Select;
	/*need to get currency sales type - payment discount percent and GL code
	as well as payment terms and credit status and hold the lot as session variables
	the receipt held entirely as session variables until the button clicked to process*/


	if (isset($_SESSION['CustomerRecord'])){
	   unset($_SESSION['CustomerRecord']);
	}

	$SQL = "SELECT debtorsmaster.name,
			debtorsmaster.pymtdiscount,
			debtorsmaster.currcode,
			currencies.currency,
			currencies.rate,
			paymentterms.terms,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription,
			SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight - debtortrans.ovdiscount - debtortrans.alloc) AS balance,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0  THEN 
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue  THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight - debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
			ELSE 
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= 0 THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight - debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
			END) AS due,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight - debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
			ELSE 
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') ."), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight - debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
			END) AS overdue1,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight - debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight - debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
			END) AS overdue2
			FROM debtorsmaster,
				paymentterms,
				holdreasons,
				currencies,
				debtortrans
			WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
			AND debtorsmaster.currcode = currencies.currabrev
			AND debtorsmaster.holdreason = holdreasons.reasoncode
			AND debtorsmaster.debtorno = '" . $_POST['CustomerID'] . "'
			AND debtorsmaster.debtorno = debtortrans.debtorno
			GROUP BY debtorsmaster.name,
				debtorsmaster.pymtdiscount,
				debtorsmaster.currcode,
				currencies.currency,
				currencies.rate,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				paymentterms.daysbeforedue,
				paymentterms.dayinfollowingmonth,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription";
				

	$ErrMsg = _('The customer details could not be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$CustomerResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

	if (DB_num_rows($CustomerResult)==0){

		/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

		$NIL_BALANCE = True;

		$SQL = "SELECT debtorsmaster.name,
				debtorsmaster.pymtdiscount,
				currencies.currency,
				currencies.rate,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				debtorsmaster.currcode,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription
			FROM debtorsmaster,
				paymentterms,
				holdreasons,
				currencies
			WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
			AND debtorsmaster.currcode = currencies.currabrev
			AND debtorsmaster.holdreason = holdreasons.reasoncode
			AND debtorsmaster.debtorno = '" . $_POST['CustomerID'] . "'";

		$ErrMsg = _('The customer details could not be retrieved because');
		$DbgMsg = _('The SQL that failed was');
		$CustomerResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

	} else {
		$NIL_BALANCE = False;
	}

	$_SESSION['CustomerRecord'] = DB_fetch_array($CustomerResult);

	if ($NIL_BALANCE==True){
		$_SESSION['CustomerRecord']['balance']=0;
		$_SESSION['CustomerRecord']['due']=0;
		$_SESSION['CustomerRecord']['overdue1']=0;
		$_SESSION['CustomerRecord']['overdue2']=0;
	}
} /*end of if customer has just been selected  all info required read into $_SESSION['CustomerRecord']*/

/*set up the form whatever */


echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . " METHOD=POST>";

/*this block of ifs decides whether to show the new batch entry screen or not
based on the settings for $_POST['BatchNo'] and $_POST['BankAccount'] if they have already been
selected ie the form has called itself at least once then the page saves the variable
settings in a cookie. */

if (isset($_SESSION['ReceiptBatch'])){

   /*show the batch header details and the entries in the batch so far */

   echo "<FONT SIZE=3 COLOR=BLUE>" . $_SESSION['ReceiptBatch']->ReceiptType . " " . _('Batch') . ": " . $_SESSION['ReceiptBatch']->BatchNo . " - " . _('Banked into the') . " " . $_SESSION['ReceiptBatch']->BankAccountName . " " . _('on') . " " . $_SESSION['ReceiptBatch']->DateBanked . "</FONT>";

   echo "<TABLE WIDTH=100% BORDER=1><TR>
   		<TD class='tableheader'>" . _('Amount') .'<BR>' . _('Received') . "</TD>
		<TD class='tableheader'>" . _('Discount') . "</TD>
		<TD class='tableheader'>" . _('Customer') . "</TD>
		<TD class='tableheader'>" . _('GL Code') . "</TD>
	</TR>";

   $BatchTotal = 0;

   foreach ($_SESSION['ReceiptBatch']->Items as $ReceiptItem) {

	    echo "<TR>
	    		<TD ALIGN=RIGHT>" . number_format($ReceiptItem->Amount,2) . "</TD>
			<TD ALIGN=RIGHT>" . number_format($ReceiptItem->Discount,2) . "</TD>
			<TD>" . $ReceiptItem->CustomerName . "</TD>
			<TD>" . $ReceiptItem->GLCode . "</TD>
			<TD><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $ReceiptItem->ID . "'>" . _('Delete') . '</a></TD>
		</TR>';

	    $BatchTotal= $BatchTotal + $ReceiptItem->Amount;

   }

   echo "<TR><TD ALIGN=RIGHT><B>" . number_format($BatchTotal,2) . "</B></TD></TR></TABLE>";


} else {
  /*need to enter batch no or select a bank account and bank date*/

	echo '<CENTER><FONT SIZE=4><B><U>' . _('Set up a New Batch') . '</B></U></FONT>';
	echo "<INPUT TYPE='hidden' name='BatchNo'value='" . $_POST['BatchNo'] . "'>";
	echo "<P><TABLE>";

	$SQL = "SELECT bankaccountname,
			bankaccounts.accountcode
		FROM bankaccounts,
			chartmaster
		WHERE bankaccounts.accountcode=chartmaster.accountcode";


	$ErrMsg = _('The bank accounts could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank acconts was');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	echo '<TR><TD>' . _('Bank Account') . ":</TD><TD><SELECT name='BankAccount'>";

	if (DB_num_rows($AccountsResults)==0){
		echo '</SELECT></TD></TR></TABLE><P>';
		prnMsg(_('Bank Accounts have not yet been defined') . '. ' . _('You must first') . ' ' . "<A HREF='$rootpath/BankAccounts.php'>" . _('define the bank accounts') . '</A>' . _('and general ledger accounts to be affected'),'info');
		include('includes/footer.inc');
		 exit;
	} else {
		while ($myrow=DB_fetch_array($AccountsResults)){
		      /*list the bank account names */
			if ($_POST['BankAccount']==$myrow['accountcode']){
				echo "<OPTION SELECTED VALUE='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
			} else {
				echo "<OPTION VALUE='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
			}
		}
		echo "</SELECT></TD></TR>";
	}

	$_POST['DateBanked'] = Date($_SESSION['DefaultDateFormat']);

	echo '<TR><TD>' . _('Date Banked') . ":</TD><TD><INPUT TYPE='text' name='DateBanked' maxlength=10 size=11 value='" . $_POST['DateBanked'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Currency') . ":</TD><TD><SELECT name='Currency'>";

	if (!isset($_POST['Currency'])){
	  /* find out what the functional currency of the company is */

		$SQL = "SELECT currencydefault FROM companies WHERE coycode=1";
		$result=DB_query($SQL,$db);
		$myrow=DB_fetch_row($result);
		$_POST['Currency']=$myrow[0];
		unset($result);
	}

	$SQL = "SELECT currency, currabrev, rate FROM currencies";
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
	   echo '</SELECT></TD></TR>';
	   prnMsg(_('No currencies are defined yet') . '. ' . _('Receipts cannot be entered until a currency is defined'),'warn');

	} else {
		while ($myrow=DB_fetch_array($result)){
		    if ($_POST['Currency']==$myrow['currabrev']){
			echo "<OPTION SELECTED value=" . $myrow['currabrev'] . '>' . $myrow['currency'];
		    } else {
			echo "<OPTION value=" . $myrow['currabrev'] . '>' . $myrow['currency'];
		    }
		}
		echo '</SELECT></TD></TR>';
	}

	if (!isset($_POST['ExRate'])){
	     $_POST['ExRate']=1;
	}
	echo '<TR><TD>' . _('Exchange Rate') . ":</TD><TD><INPUT TYPE='text' name='ExRate' maxlength=10 size=12 value='" . $_POST['ExRate'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Receipt Type') . ":</TD><TD><SELECT name=ReceiptType>";

	include('includes/GetPaymentMethods.php');
/* The array ReceiptTypes is defined from the setup tab of the main menu under payment methods - the array is populated from the include file GetPaymentMethods.php */

	foreach ($ReceiptTypes as $RcptType) {
	     if ($_POST['ReceiptType']==$RcptType){
		   echo "<OPTION SELECTED Value='$RcptType'>$RcptType";
	     } else {
		   echo "<OPTION Value='$RcptType'>$RcptType";
	     }
	}
	echo "</SELECT></TD></TR>";

	echo '<TR><TD>' . _('Narrative') . ":</TD><TD><INPUT TYPE='text' name='BatchNarrative' maxlength=50 size=52 value='" . $_POST['BatchNarrative'] . "'></TD></TR>";

	echo "</TABLE>";

	echo "<CENTER><INPUT TYPE=SUBMIT Name='BatchInput' Value='" . _('Accept') . "'></CENTER>";
}

/*this next block of ifs deals with what information to display for input into the form
the info depends on where the user is up to ie the first stage is to select a bank
account, currency being banked and a batch number - or start a new batch by leaving the batch no blank
and a date for the banking. The second stage is to select a customer or GL account.
Finally enter the amount */


/*if a customer has been selected (and a receipt batch is underway)
then set out the customers account summary */

if (isset($_SESSION['CustomerRecord']) AND isset($_POST['CustomerID']) AND $_POST['CustomerID']!="" AND isset($_SESSION['ReceiptBatch'])){
/*a customer is selected  */

	echo "<BR><CENTER><FONT SIZE=4>" . $_SESSION['CustomerRecord']['name'] . ' </FONT></B> - (' . _('All amounts stated in') . ' ' . $_SESSION['CustomerRecord']['currency'] . ')</CENTER><BR><B><FONT COLOR=BLUE>' . _('Terms') . ': ' . $_SESSION['CustomerRecord']['terms'] . "<BR>" . _('Credit Limit') . ": </B></FONT> " . number_format($_SESSION['CustomerRecord']['creditlimit'],0) . '  <B><FONT COLOR=BLUE>' . _('Credit Status') . ':</B></FONT> ' . $_SESSION['CustomerRecord']['reasondescription'];

	if ($_SESSION['CustomerRecord']['dissallowinvoices']!=0){
	   echo '<BR><FONT COLOR=RED SIZE=4><B>' . _('ACCOUNT ON HOLD') . '</FONT></B><BR>';
	}

	echo "<TABLE WIDTH=100% BORDER=1>
		<TR>
			<TD class='tableheader'>" . _('Total Balance') . "</TD>
			<TD class='tableheader'>" . _('Current') . "</TD>
			<TD class='tableheader'>" . _('Now Due') . "</TD>
			<TD class='tableheader'>" . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . "</TD>
			<TD class='tableheader'>" . _('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</TD>
		</TR>';

	echo "<TR>
		<TD ALIGN=RIGHT>" . number_format($_SESSION['CustomerRecord']['balance'],2) . "</TD>
		<TD ALIGN=RIGHT>" . number_format(($_SESSION['CustomerRecord']['balance'] - $_SESSION['CustomerRecord']['due']),2) . "</TD>
		<TD ALIGN=RIGHT>" . number_format(($_SESSION['CustomerRecord']['due']-$_SESSION['CustomerRecord']['overdue1']),2) . "</TD>
		<TD ALIGN=RIGHT>" . number_format(($_SESSION['CustomerRecord']['overdue1']-$_SESSION['CustomerRecord']['overdue2']) ,2) . "</TD>
		<TD ALIGN=RIGHT>" . number_format($_SESSION['CustomerRecord']['overdue2'],2) . "</TD>
	</TR>
	</TABLE>";

	echo "<CENTER><TABLE>";

        $DisplayDiscountPercent = number_format($_SESSION['CustomerRecord']['pymtdiscount']*100,2) . "%";

	echo "<INPUT TYPE='hidden' name='CustomerID' value=" . $_POST['CustomerID'] . ">";
	echo "<INPUT TYPE='hidden' name='CustomerName' value='" . $_SESSION['CustomerRecord']['name'] . "'>";

}

if (isset($_POST['GLEntry']) AND isset($_SESSION['ReceiptBatch'])){
/* Set up a heading for the transaction entry for a GL Receipt */

	echo '<BR><CENTER><FONT SIZE=4>' . _('General Ledger Receipt Entry') . '</FONT><TABLE>';

	/*now set up a GLCode field to select from avaialble GL accounts */
	echo '<TR><TD>' . _('GL Account') . ":</TD><TD><SELECT name='GLCode'>";
	$SQL = "SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode";
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
	   echo '</SELECT>' . _('No General ledger accounts have been set up yet') . ' - ' . _('receipts cannot be entered against GL accounts until the GL accounts are set up') . '</TD></TR>';
	} else {
		while ($myrow=DB_fetch_array($result)){
		    if ($_POST['GLCode']==$myrow["accountcode"]){
			echo "<OPTION SELECTED value=" . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		    } else {
			echo '<OPTION value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		    }
		}
		echo '</SELECT></TD></TR>';
	}

}

/*if either a customer is selected or its a GL Entry then set out
the fields for entry of receipt amt, disc, payee details, narrative */

if (((isset($_SESSION['CustomerRecord']) 
	AND isset($_POST['CustomerID']) 
	AND $_POST['CustomerID']!="") 
		OR isset($_POST['GLEntry'])) 
		AND isset($_SESSION['ReceiptBatch'])){

	echo '<TR><TD>' . _('Amount of Receipt') . ":</TD>
		<TD><INPUT TYPE='text' name='amount' maxlength=12 size=13 value='" . $_POST['amount'] . "'></TD>
	</TR>";

	if (!isset($_POST['GLEntry'])){
		echo '<TR><TD>' . _('Amount of Discount') . ":</TD>
			<TD><INPUT TYPE='text' name='discount' maxlength=12 size=13 value='" . $_POST['discount'] . "'> " . _('agreed prompt payment discount is') . ' ' . $DisplayDiscountPercent . '</TD></TR>';
	} else {
		echo "<INPUT TYPE='HIDDEN' NAME='discount' Value=0>";
	}

	echo '<TR><TD>' . _('Payee Bank Details') . ":</TD>
		<TD><INPUT TYPE='text' name='PayeeBankDetail' maxlength=22 size=20 value='" . $_POST['PayeeBankDetail'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Narrative') . ":</TD>
		<TD><INPUT TYPE='text' name='Narrative' maxlength=30 size=32 value='" . $_POST['Narrative'] . "'></TD></TR>";
	echo "</TABLE>";
	echo "<INPUT TYPE=SUBMIT name=Process value='" . _('Accept') . "'><INPUT TYPE=SUBMIT name=Cancel value='" . _('Cancel') . "'>";

} elseif (isset($_SESSION['ReceiptBatch']) && !isset($_POST['GLEntry'])){

      /*Show the form to select a customer */
        echo '<B>';

	echo $msg;
	echo '<BR><U>' . _('Select A Customer') . '</U></B>';
	echo '<TABLE CELLPADDING=3 COLSPAN=4>';
	echo '<TR><TD>' . _('Text in the') . ' ' . '<B>' . _('name') . '</B>:</TD>';
	echo "<TD><INPUT TYPE='Text' NAME='Keywords' SIZE=20 MAXLENGTH=25></TD>";
	echo '<TD><FONT SIZE=3><B>OR</B></FONT></TD>';
	echo '<TD>' . _('Text extract in the customer') . ' ' . '<B>' . _('code') . '</B>:</TD>';
	echo "<TD><INPUT TYPE='Text' NAME='CustCode' SIZE=15 MAXLENGTH=18></TD>";
	echo '</TR></TABLE>';
	echo '<CENTER>';
	echo "<INPUT TYPE=SUBMIT NAME='Search' VALUE='" . _('Search Now') . "'>";
	echo "<BR><BR><INPUT TYPE=SUBMIT NAME='GLEntry' VALUE='" . _('Enter A GL Receipt') . "'>";

	if (count($_SESSION['ReceiptBatch']->Items) > 0){
		echo "<BR><BR><INPUT TYPE=SUBMIT NAME='CommitBatch' VALUE='" . _('Accept and Process Batch') . "'></CENTER>";
	}

	If ($result) {

		echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';
		$TableHeader = "<TR><TD class='tableheader'>" . _('Code') . "</TD><TD class='tableheader'>" . _('Customer Name') . '</TD></TR>';
		echo $TableHeader;
		$j = 1;
		$k = 0; //row counter to determine background colour

		while ($myrow=DB_fetch_array($result)) {

			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k=1;
			}

			printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</FONT></td><td>%s</td></tr>", $myrow['debtorno'],$myrow['name']);

			$j++;
			If ($j == 11){
				$j=1;
				echo $TableHeader;

			}
	//end of page full new headings if
		}
	//end of while loop

		echo '</TABLE></CENTER>';

	}
	//end if results to show
}

echo '</form>';
include('includes/footer.inc');
?>

<?php
/* $Revision: 1.2 $ */
include("includes/DefineReceiptClass.php");

$PageSecurity = 3;

$title = "Receipt Entry";
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");

$msg="";


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

   $CompanyRecord = ReadInCompanyRecord($db);
   $PeriodNo = GetPeriod($_SESSION['ReceiptBatch']->DateBanked,$db);

   if ($CompanyRecord==0){
	die("<BR>The company has not yet been set up properly - this information is needed to process the batch. Processing has been cancelled.");
   }

   /*Make an array of the defined bank accounts */
   $SQL = "SELECT AccountCode FROM BankAccounts";
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

	    if ($ReceiptItem->GLCode !=""){
		if ($CompanyRecord["GLLink_Debtors"]==1){ /* then enter a GLTrans record */
			 $SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) ";
			 $SQL= $SQL . "VALUES (12, " . $_SESSION['ReceiptBatch']->BatchNo . ", '" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "', " . $PeriodNo . ", " . $ReceiptItem->GLCode . ", '" . $ReceiptItem->Narrative . "', " . -$ReceiptItem->Amount/$_SESSION['ReceiptBatch']->ExRate . ")";
			 $result = DB_query($SQL,$db);
			 if (DB_error_no($db)!=0){
			    echo "Cannot insert a GL entry for the receipt using the SQL: -<BR>$SQL";
			    $SQL = "Rollback";
			    $result= DB_query($SQL,$db);
			    exit;
			 }
		}

		/*check to see if this is a GL posting to another bank account (or the same one)
		if it is then a matching payment needs to be created for this account too */

		if (in_array($ReceiptItem->GLCode, $BankAccounts)) {

			$PaymentTransNo = GetNextTransNo( 1, $db);
			$SQL="INSERT INTO BankTrans (TransNo, Type, BankAct, Ref, ExRate, TransDate, BankTransType, Amount, CurrCode) ";
			$SQL= $SQL . "VALUES (" . $PaymentTransNo . ", 1, " . $ReceiptItem->GLCode . ", 'Act Transfer  - " . $ReceiptItem->Narrative . "', " . $_SESSION['ReceiptBatch']->ExRate . " ,'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "','" . $_SESSION['ReceiptBatch']->ReceiptType . "', " . -$ReceiptItem->Amount . ", '" . $_SESSION['ReceiptBatch']->Currency . "')";
   			$result = DB_query($SQL,$db);
   			if (DB_error_no($db)!=0){
	  			echo "<BR><B>Problem Report:</B><BR>Cannot insert a bank transaction using the SQL: -<BR>$SQL";
	  			$SQL = "Rollback";
	  			$result= DB_query($SQL,$db);
	  			exit;
			}
		}

	    } else {
		   /*Accumulate the total debtors credit including discount */
		   $BatchDebtorTotal = $BatchDebtorTotal + (($ReceiptItem->Discount + $ReceiptItem->Amount)/$_SESSION['ReceiptBatch']->ExRate);
		   /*Create a DebtorTrans entry for each customer deposit */

		   $SQL = "INSERT INTO DebtorTrans (TransNo, Type, DebtorNo, BranchCode, TranDate, Prd, Reference, Tpe, Rate, OvAmount, OVDiscount, InvText) ";
		   $SQL = $SQL . "VALUES (" . $_SESSION['ReceiptBatch']->BatchNo . ", 12, '" . $ReceiptItem->Customer . "', '', '" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "', " . $PeriodNo . ",'" . $_SESSION['ReceiptBatch']->ReceiptType  . " " . $ReceiptItem->Narrative . "',''," . $_SESSION['ReceiptBatch']->ExRate . ", " . -$ReceiptItem->Amount . ", " . -$ReceiptItem->Discount . ", '" . $ReceiptItem->PayeeBankDetail . "')";
		   $result = DB_query($SQL,$db);
		   if (DB_error_no($db)!=0){
			echo "Cannot insert a receipt transaction against the customer using the SQL: -<BR>$SQL";
			$SQL = "Rollback";
			$result= DB_query($SQL,$db);
			exit;
		   }
		   $SQL = "UPDATE DebtorsMaster SET LastPaidDate = '" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "', LastPaid=" . $ReceiptItem->Amount ." WHERE DebtorsMaster.DebtorNo='" . $ReceiptItem->Customer . "'";
		   $result = DB_query($SQL,$db);
		   if (DB_error_no($db)!=0){
			echo "Cannot update the customer record for the date of the last payment received using the SQL: -<BR>$SQL";
			$SQL = "Rollback";
			$result= DB_query($SQL,$db);
			exit;
		   }

	    }
	    $BatchDiscount = $BatchDiscount + $ReceiptItem->Discount/$_SESSION['ReceiptBatch']->ExRate;
	    $BatchReceiptsTotal = $BatchReceiptsTotal + $ReceiptItem->Amount/$_SESSION['ReceiptBatch']->ExRate;

   }
   if ($CompanyRecord["GLLink_Debtors"]==1){ /* then enter GLTrans records for discount, bank and debtors */

	if ($BatchReceiptsTotal!=0){
		/* Bank account entry first */
		$SQL="INSERT INTO GLTrans ( Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) ";
		$SQL=$SQL . "VALUES (12, " . $_SESSION['ReceiptBatch']->BatchNo . ",'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "', " . $PeriodNo . ", " . $_SESSION['ReceiptBatch']->Account . ",'" . $_SESSION['ReceiptBatch']->Narrative . "', " . $BatchReceiptsTotal . ")";
		$result = DB_query($SQL,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>Cannot insert a GL transaction for the bank account debit using the SQL: -<BR>$SQL";
			$SQL = "Rollback";
			$result= DB_query($SQL,$db);
			exit;
		}

		if ($BatchDebtorTotal!=0){
			/* Now Credit Debtors account with receipts + discounts */
			$SQL="INSERT INTO GLTrans ( Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) ";
			$SQL=$SQL . "VALUES (12, " . $_SESSION['ReceiptBatch']->BatchNo . ",'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "', " . $PeriodNo . ", " . $CompanyRecord["DebtorsAct"] . ",'" . $_SESSION['ReceiptBatch']->Narrative . "', " . -$BatchDebtorTotal . ")";
			$result = DB_query($SQL,$db);
			if (DB_error_no($db)!=0){
				echo "Cannot insert a GL transaction for the debtors account credit using the SQL: -<BR>$SQL";
				$SQL = "Rollback";
				$result= DB_query($SQL,$db);
				exit;
			}
		}

		if ($BatchDiscount!=0){
			/* Now Debit Discount account with discounts allowed*/
			$SQL="INSERT INTO GLTrans ( Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) ";
			$SQL=$SQL . "VALUES (12, " . $_SESSION['ReceiptBatch']->BatchNo . ",'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "', " . $PeriodNo . ", " . $CompanyRecord["PytDiscountAct"] . ",'" . $_SESSION['ReceiptBatch']->Narrative . "', " . $BatchDiscount . ")";
			$result = DB_query($SQL,$db);
			if (DB_error_no($db)!=0){
				echo "Cannot insert a GL transaction for the payment discount debit using the SQL: -<BR>$SQL";
				$SQL = "Rollback";
				$result= DB_query($SQL,$db);
				exit;
			}
		}
	}
   }

   /*now enter the BankTrans entry */

   $SQL="INSERT INTO BankTrans (Type, TransNo, BankAct, Ref, ExRate, TransDate, BankTransType, Amount, CurrCode) ";
   $SQL= $SQL . "VALUES (12," . $_SESSION['ReceiptBatch']->BatchNo . ", " . $_SESSION['ReceiptBatch']->Account . ", '" . $_SESSION['ReceiptBatch']->Narrative . "', " . $_SESSION['ReceiptBatch']->ExRate . " ,'" . FormatDateForSQL($_SESSION['ReceiptBatch']->DateBanked) . "','" . $_SESSION['ReceiptBatch']->ReceiptType . "', " . ($BatchReceiptsTotal * $_SESSION["ReceiptBatch"]->ExRate) . ", '" . $_SESSION['ReceiptBatch']->Currency . "')";
   $result = DB_query($SQL,$db);
   if (DB_error_no($db)!=0){
	  echo "Cannot insert a bank transaction using the SQL: -<BR>$SQL";
	  $SQL = "Rollback";
	  $result= DB_query($SQL,$db);
	  exit;
   }

   $SQL = "Commit";
   $result= DB_query($SQL,$db);
   if (DB_error_no($db)!=0){
	  echo "Cannot commit the changes: -<BR>$SQL";
	  $SQL = "Rollback";
	  $result= DB_query($SQL,$db);
	  exit;
   }

   echo "<P>Receipt batch " . $_SESSION['ReceiptBatch']->BatchNo . " has been sucessfully entered into the database.";
   echo "<BR><A HREF='" . $rootpath . "/PDFBankingSummary.php?BatchNo=" . $_SESSION['ReceiptBatch']->BatchNo . "'>Print PDF Batch Summary</A>";
   unset($_SESSION['ReceiptBatch']);

} elseif (isset($_POST['BatchInput'])){ //submitted a new batch
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
	       $_POST['DateBanked'] = Date($DefaultDateFormat); 
	  } 
   $_SESSION['ReceiptBatch']->DateBanked = $_POST['DateBanked'];
   $_SESSION['ReceiptBatch']->ExRate = $_POST['ExRate'];
   $_SESSION['ReceiptBatch']->ReceiptType = $_POST['ReceiptType'];
   $_SESSION['ReceiptBatch']->Currency = $_POST['Currency'];
   $_SESSION['ReceiptBatch']->Narrative = $_POST['BatchNarrative'];
   $_SESSION['ReceiptBatch']->ID = 1;

   $SQL = "SELECT BankAccountName FROM BankAccounts WHERE AccountCode=" . $_POST['BankAccount'];
   $result= DB_query($SQL,$db);

   if (DB_error_no($db) !=0) {
	  echo "The bank account name cannot be retrieved because - " . DB_error_msg($db) . "<BR>SQL used to retrieve the bank account name was:<BR>$sql";
   } elseif (DB_num_rows($result)==1){
          $myrow = DB_fetch_row($result);
          $_SESSION['ReceiptBatch']->BankAccountName = $myrow[0];
          unset($result);
   } elseif (DB_num_rows($result)==0){
          echo "<P>The bank account number " . $_POST['BankAccount'] . " is not set up as a bank account.";
   }

} elseif (isset($_GET['Delete'])){
  /* User hit delete the receipt entry from the batch */
   $_SESSION['ReceiptBatch']->remove_receipt_item($_GET['Delete']);
} elseif (isset($_POST['Process'])){ //user hit submit a new entry to the receipt batch

   $_SESSION['ReceiptBatch']->add_to_batch($_POST['amount'], $_POST['CustomerID'], $_POST['discount'], $_POST['Narrative'], $_POST['GLCode'], $_POST['PayeeBankDetail'], $_POST['CustomerName']);

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
		$msg="Customer name keywords have been used in preference to the customer code extract entered.";
	}
	If ($_POST['Keywords']=="" AND $_POST['CustCode']=="") {
		$msg="At least one customer name keyword OR an extract of a customer code must be entered for the search";
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
			$SQL = "SELECT DebtorsMaster.DebtorNo, DebtorsMaster.Name FROM DebtorsMaster WHERE DebtorsMaster.Name LIKE '$SearchString' AND DebtorsMaster.CurrCode= '" . $_SESSION['ReceiptBatch']->Currency . "'";

		} elseif (strlen($_POST['CustCode'])>0){
			$SQL = "SELECT DebtorsMaster.DebtorNo, DebtorsMaster.Name FROM DebtorsMaster WHERE DebtorsMaster.DebtorNo LIKE '%" . $_POST['CustCode'] . "%' AND DebtorsMaster.CurrCode= '" . $_SESSION['ReceiptBatch']->Currency . "'";
		}

		$result = DB_query($SQL,$db);
		if (DB_error_no($db) !=0) {
			echo "The searched customer records requested cannot be retrieved because - " . DB_error_msg($db) . "<BR>SQL used to retrieve the customer details was:<BR>$sql";
		} elseif (DB_num_rows($result)==1){
			$myrow=DB_fetch_array($result);
			$Select = $myrow["DebtorNo"];
			unset($result);
		} elseif (DB_num_rows($result)==0){
			echo "<P>No customer records contain the selected text - please alter your search criteria and try again.";
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

	$SQL = "SELECT DebtorsMaster.Name, DebtorsMaster.PymtDiscount, DebtorsMaster.CurrCode, Currencies.Currency, Currencies.Rate, PaymentTerms.Terms,
	DebtorsMaster.CreditLimit, HoldReasons.DissallowInvoices, HoldReasons.ReasonDescription,

	Sum(DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight - DebtorTrans.OvDiscount - DebtorTrans.Alloc) AS Balance,

	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate)) >= PaymentTerms.DaysBeforeDue  THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight - DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END,

		CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1 MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= 0 THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight - DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END

	)) AS Due,


	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) > PaymentTerms.DaysBeforeDue	AND TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays1 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight - DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END,

		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= " . $PastDueDays1 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight - DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END

	)) AS Overdue1,

	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
		CASE WHEN TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) > PaymentTerms.DaysBeforeDue	AND TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays2 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight - DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END,

		CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= " . $PastDueDays2 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight - DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END

	)) AS Overdue2

	FROM DebtorsMaster, PaymentTerms, HoldReasons, Currencies, DebtorTrans WHERE DebtorsMaster.PaymentTerms = PaymentTerms.TermsIndicator AND DebtorsMaster.CurrCode = Currencies.CurrAbrev AND DebtorsMaster.HoldReason = HoldReasons.ReasonCode AND DebtorsMaster.DebtorNo = '" . $_POST['CustomerID'] . "' AND DebtorsMaster.DebtorNo = DebtorTrans.DebtorNo GROUP BY DebtorsMaster.Name, Currencies.Currency, PaymentTerms.Terms, PaymentTerms.DaysBeforeDue, PaymentTerms.DayInFollowingMonth, DebtorsMaster.CreditLimit, HoldReasons.DissallowInvoices, HoldReasons.ReasonDescription";

	$CustomerResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "The customer details could not be retrieved by the SQL because - " . DB_error_msg($db);

		if ($debug==1){
			echo "<BR>$SQL";
		}
		exit;
	}

	if (DB_num_rows($CustomerResult)==0){

		/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

		$NIL_BALANCE = True;

		$SQL = "SELECT DebtorsMaster.Name, DebtorsMaster.PymtDiscount, Currencies.Currency,  Currencies.Rate, PaymentTerms.Terms,
		DebtorsMaster.CreditLimit, DebtorsMaster.CurrCode, HoldReasons.DissallowInvoices, HoldReasons.ReasonDescription
		FROM DebtorsMaster, PaymentTerms, HoldReasons, Currencies WHERE DebtorsMaster.PaymentTerms = PaymentTerms.TermsIndicator AND DebtorsMaster.CurrCode = Currencies.CurrAbrev AND DebtorsMaster.HoldReason = HoldReasons.ReasonCode AND DebtorsMaster.DebtorNo = '" . $_POST['CustomerID'] . "'";

		$CustomerResult = DB_query($SQL,$db);

		if (DB_error_no($db) !=0) {
			echo "The customer details could not be retrieved by the SQL because - " . DB_error_msg($db);

			if ($debug==1){
				echo "<BR>$SQL";
			}
			exit;
		}

	} else {
		$NIL_BALANCE = False;
	}

	$_SESSION['CustomerRecord'] = DB_fetch_array($CustomerResult);

	if ($NIL_BALANCE==True){
		$_SESSION['CustomerRecord']["Balance"]=0;
		$_SESSION['CustomerRecord']["Due"]=0;
		$_SESSION['CustomerRecord']["Overdue1"]=0;
		$_SESSION['CustomerRecord']["Overdue2"]=0;
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

   echo "<FONT SIZE=3 COLOR=BLUE>" . $_SESSION['ReceiptBatch']->ReceiptType . " Batch: " . $_SESSION['ReceiptBatch']->BatchNo . " - Banked into the " . $_SESSION['ReceiptBatch']->BankAccountName . " on " . $_SESSION['ReceiptBatch']->DateBanked . "</FONT>";

   echo "<TABLE WIDTH=100% BORDER=1><TR><td class='tableheader'>Amount Rec'd</TD><td class='tableheader'>Discount</TD><td class='tableheader'>Customer</TD><td class='tableheader'>GL Code</TD></TR>";

   $BatchTotal = 0;

   foreach ($_SESSION['ReceiptBatch']->Items as $ReceiptItem) {

	    echo "<TR><TD ALIGN=RIGHT>" . number_format($ReceiptItem->Amount,2) . "</TD><TD ALIGN=RIGHT>" . number_format($ReceiptItem->Discount,2) . "</TD><TD>" . $ReceiptItem->CustomerName . "</TD><TD>" . $ReceiptItem->GLCode . "</TD><TD><a href='" . $_SERVER['PHP_SELF'] . "?Delete=" . $ReceiptItem->ID . "'>Delete</a></TD></TR>";

	    $BatchTotal= $BatchTotal + $ReceiptItem->Amount;

   }

   echo "<TR><TD ALIGN=RIGHT><B>" . number_format($BatchTotal,2) . "</B></TD></TR></TABLE>";


} else {
  /*need to enter batch no or select a bank account and bank date*/

	echo "<CENTER><FONT SIZE=4><B><U>Set up a New Batch</B></U></FONT>";
	echo "<INPUT TYPE='hidden' name='BatchNo'value='" . $_POST['BatchNo'] . "'>";
	echo "<P><TABLE>";

	$SQL = "SELECT BankAccountName, BankAccounts.AccountCode FROM BankAccounts, ChartMaster WHERE BankAccounts.AccountCode=ChartMaster.AccountCode";

	$AccountsResults = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		 echo "<P>The bank accounts could not be retrieved by the SQL because - " . DB_error_msg($db);
		 if ($debug==1){
			echo "<BR>The SQL used to retrieve the bank acconts was:<BR>$SQL";
		 }
		 exit;
	}

	echo "<TR><TD>Bank Account:</TD><TD><SELECT name='BankAccount'>";

	if (DB_num_rows($AccountsResults)==0){
		 echo "</SELECT></TD></TR></TABLE><P>Bank Accounts have not yet been defined. You must first <A HREF='$rootpath/BankAccounts.php'>define the bank accounts</A> and general ledger accounts to be affected.";
		 exit;
	} else {
		while ($myrow=DB_fetch_array($AccountsResults)){
		      /*list the bank account names */
			if ($_POST['BankAccount']==$myrow['AccountCode']){
				echo "<OPTION SELECTED VALUE='" . $myrow["AccountCode"] . "'>" . $myrow["BankAccountName"];
			} else {
				echo "<OPTION VALUE='" . $myrow["AccountCode"] . "'>" . $myrow["BankAccountName"];
			}
		}
		echo "</SELECT></TD></TR>";
	}

	$_POST['DateBanked'] = Date($DefaultDateFormat);

	echo "<TR><TD>Date Banked:</TD><TD><INPUT TYPE='text' name='DateBanked' maxlength=10 size=11 value='" . $_POST['DateBanked'] . "'></TD></TR>";
	echo "<TR><TD>Currency:</TD><TD><SELECT name='Currency'>";

	if (!isset($_POST['Currency'])){
	  /* find out what the functional currency of the company is */

		$SQL = "SELECT CurrencyDefault FROM Companies WHERE CoyCode=1";
		$result=DB_query($SQL,$db);
		$myrow=DB_fetch_row($result);
		$_POST['Currency']=$myrow[0];
		unset($result);
	}

	$SQL = "SELECT Currency, CurrAbrev, Rate FROM Currencies";
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
	   echo "</SELECT>No currencies are defined yet. Receipts cannot be entered until a currency is defined.</TD></TR>";
	} else {
		while ($myrow=DB_fetch_array($result)){
		    if ($_POST['Currency']==$myrow["CurrAbrev"]){
			echo "<OPTION SELECTED value=" . $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		    } else {
			echo "<OPTION value=" . $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		    }
		}
		echo "</SELECT></TD></TR>";
	}

	if (!isset($_POST['ExRate'])){
	     $_POST['ExRate']=1;
	}
	echo "<TR><TD>Exchange Rate:</TD><TD><INPUT TYPE='text' name='ExRate' maxlength=10 size=12 value='" . $_POST['ExRate'] . "'></TD></TR>";
	echo "<TR><TD>Receipt Type:</TD><TD><SELECT name=ReceiptType>";

/* The array ReceiptTypes is set up in config.php for user modification
receipt types can be modified by editing that file */

	foreach ($ReceiptTypes as $RcptType) {
	     if ($_POST['ReceiptType']==$RcptType){
		   echo "<OPTION SELECTED Value='$RcptType'>$RcptType";
	     } else {
		   echo "<OPTION Value='$RcptType'>$RcptType";
	     }
	}
	echo "</SELECT></TD></TR>";

	echo "<TR><TD>Narrative:</TD><TD><INPUT TYPE='text' name='BatchNarrative' maxlength=50 size=52 value='" . $_POST['BatchNarrative'] . "'></TD></TR>";

	echo "</TABLE>";

	echo "<CENTER><INPUT TYPE=SUBMIT Name='BatchInput' Value='Accept'></CENTER>";
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

	echo "<BR><CENTER><FONT SIZE=4>" . $_SESSION['CustomerRecord']["Name"] . " </FONT></B> - (All amounts stated in " . $_SESSION['CustomerRecord']["Currency"] . ")</CENTER><BR><B><FONT COLOR=BLUE>Terms: " . $_SESSION['CustomerRecord']["Terms"] . "<BR>Credit Limit: </B></FONT> " . number_format($_SESSION['CustomerRecord']["CreditLimit"],0) . "  <B><FONT COLOR=BLUE>Credit Status:</B></FONT> " . $_SESSION['CustomerRecord']["ReasonDescription"];

	if ($_SESSION['CustomerRecord']["DissallowInvoices"]!=0){
	   echo "<BR><FONT COLOR=RED SIZE=4><B>ACCOUNT ON HOLD</FONT></B><BR>";
	}

	echo "<TABLE WIDTH=100% BORDER=1><TR><td class='tableheader'>Total Balance</TD><td class='tableheader'>Current</TD><td class='tableheader'>Now Due</TD><td class='tableheader'>" . $PastDueDays1 . "-" . $PastDueDays2 . " Days Overdue</TD><td class='tableheader'>Over " . $PastDueDays2 . " Days Overdue</TD></TR>";

	echo "<TR><TD ALIGN=RIGHT>" . number_format($_SESSION['CustomerRecord']["Balance"],2) . "</TD><TD ALIGN=RIGHT>" . number_format(($_SESSION['CustomerRecord']["Balance"] - $_SESSION['CustomerRecord']["Due"]),2) . "</TD><TD ALIGN=RIGHT>" . number_format(($_SESSION['CustomerRecord']["Due"]-$_SESSION['CustomerRecord']["Overdue1"]),2) . "</TD><TD ALIGN=RIGHT>" . number_format(($_SESSION['CustomerRecord']["Overdue1"]-$_SESSION['CustomerRecord']["Overdue2"]) ,2) . "</TD><TD ALIGN=RIGHT>" . number_format($_SESSION['CustomerRecord']["Overdue2"],2) . "</TD></TR></TABLE>";

	echo "<CENTER><TABLE>";

        $DisplayDiscountPercent = number_format($_SESSION['CustomerRecord']["PymtDiscount"]*100,2) . "%";

	echo "<INPUT TYPE='hidden' name='CustomerID' value=" . $_POST['CustomerID'] . ">";
	echo "<INPUT TYPE='hidden' name='CustomerName' value='" . $_SESSION['CustomerRecord']["Name"] . "'>";

}

if (isset($_POST['GLEntry']) AND isset($_SESSION['ReceiptBatch'])){
/* Set up a heading for the transaction entry for a GL Receipt */

	echo "<BR><CENTER><FONT SIZE=4>General Ledger Receipt Entry</FONT><TABLE>";

	/*now set up a GLCode field to select from avaialble GL accounts */
	echo "<TR><TD>GL Account:</TD><TD><SELECT name='GLCode'>";
	$SQL = "SELECT AccountCode, AccountName FROM ChartMaster ORDER BY AccountCode";
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
	   echo "</SELECT>No General ledger accounts have been set up yet - receipts cannot be entered against GL accounts until the GL accounts are set up.</TD></TR>";
	} else {
		while ($myrow=DB_fetch_array($result)){
		    if ($_POST['GLCode']==$myrow["AccountCode"]){
			echo "<OPTION SELECTED value=" . $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " - " . $myrow["AccountName"];
		    } else {
			echo "<OPTION value=" . $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " - " . $myrow["AccountName"];
		    }
		}
		echo "</SELECT></TD></TR>";
	}

}

/*if either a customer is selected or its a GL Entry then set out
the fields for entry of receipt amt, disc, payee details, narrative */

if (((isset($_SESSION['CustomerRecord']) AND isset($_POST['CustomerID']) AND $_POST['CustomerID']!="") OR isset($_POST['GLEntry'])) AND isset($_SESSION['ReceiptBatch'])){

	echo "<TR><TD>Amount of Receipt:</TD><TD><INPUT TYPE='text' name='amount' maxlength=12 size=13 value='" . $_POST['amount'] . "'></TD></TR>";

	if (!isset($_POST['GLEntry'])){
		echo "<TR><TD>Amount of Discount:</TD><TD><INPUT TYPE='text' name='discount' maxlength=12 size=13 value='" . $_POST['discount'] . "'> agreed prompt payment discount is " . $DisplayDiscountPercent . "</TD></TR>";
	} else {
		echo "<INPUT TYPE='HIDDEN' NAME='discount' Value=0>";
	}

	echo "<TR><TD>Payee Bank Details:</TD><TD><INPUT TYPE='text' name='PayeeBankDetail' maxlength=22 size=20 value='" . $_POST['PayeeBankDetail'] . "'></TD></TR>";
	echo "<TR><TD>Narrative:</TD><TD><INPUT TYPE='text' name='Narrative' maxlength=30 size=32 value='" . $_POST['Narrative'] . "'></TD></TR>";
	echo "</TABLE>";
	echo "<INPUT TYPE=SUBMIT name=Process value='Accept'><INPUT TYPE=SUBMIT name=Cancel value='Cancel'>";

} elseif (isset($_SESSION['ReceiptBatch']) && !isset($_POST['GLEntry'])){

      /*Show the form to select a customer */
	?>

	<B><?php echo $msg; ?><BR><U>Select A Customer</U></B>
	<TABLE CELLPADDING=3 COLSPAN=4>
	<TR>
	<TD>Text in the <B>name</B>:</TD>
	<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25></TD>
	<TD><FONT SIZE=3><B>OR</B></FONT></TD>
	<TD>Text extract in the customer <B>code</B>:</TD>
	<TD><INPUT TYPE="Text" NAME="CustCode" SIZE=15 MAXLENGTH=18></TD>
	</TR>
	</TABLE>
	<CENTER>
	<INPUT TYPE=SUBMIT NAME="Search" VALUE="Search Now">
	<BR><BR><INPUT TYPE=SUBMIT NAME="GLEntry" VALUE="Enter A GL Receipt">

<?php
	if (count($_SESSION['ReceiptBatch']->Items) > 0){
		echo "<BR><BR><INPUT TYPE=SUBMIT NAME='CommitBatch' VALUE='Accept and Process Batch'></CENTER>";
	}

	If ($result) {

		echo "<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>";
		$TableHeader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Customer Name</TD></TR>";
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

			printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</FONT></td><td>%s</td></tr>", $myrow["DebtorNo"],$myrow["Name"]);

			$j++;
			If ($j == 11){
				$j=1;
				echo $TableHeader;

			}
	//end of page full new headings if
		}
	//end of while loop

		echo "</TABLE></CENTER>";

	}
	//end if results to show
}

echo "</form>";
include("includes/footer.inc");
?>

<?php

/*This page is very largely the same as the SupplierInvoice.php script
the same result could have been acheived by using if statements in that script and just having the one
SupplierTransaction.php script. However, to aid readability - variable names have been changed  -
and reduce clutter (in the form of a heap of if statements) two seperate scripts have been used, 
both with very similar code.

This does mean that if the logic is to be changed for supplier transactions then it needs to be changed
in both scripts.

This is widely considered poor programming but in my view, much easier to read for the uninitiated

*/

/*The supplier transaction uses the SuppTrans class to hold the information about the credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

$title = "Supplier Credit Note";

include("includes/DefineSuppTransClass.php");

$PageSecurity=5;

/* Session started in header.inc for password checking and authorisation level check */

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");


if (isset($_GET['SupplierID'])){

 /*It must be a new credit note entry - clear any existing credit note details from the SuppTrans object and initiate a newy*/
	if (isset($_SESSION['SuppTrans'])){
		unset ($_SESSION['SuppTrans']->GRNs);
		unset ($_SESSION['SuppTrans']->Shipts);
		unset ($_SESSION['SuppTrans']->GLCodes);
		unset ($_SESSION['SuppTrans']);
	}

	 $_SESSION['SuppTrans'] = new SuppTrans;

/*Now retrieve supplier information - name, currency, default ex rate, terms, tax rate etc */

	 $sql = "SELECT Suppliers.SuppName, PaymentTerms.Terms, PaymentTerms.DaysBeforeDue, PaymentTerms.DayInFollowingMonth, Suppliers.CurrCode, Currencies.Rate As ExRate, TaxAuthorities.Description As TaxDesc, TaxAuthorities.TaxID, TaxAuthorities.PurchTaxGLAccount AS TaxGLCode  From Suppliers, Currencies, PaymentTerms, TaxAuthorities WHERE Suppliers.TaxAuthority = TaxAuthorities.TaxID AND Suppliers.CurrCode=Currencies.CurrAbrev AND Suppliers.PaymentTerms=PaymentTerms.TermsIndicator AND Suppliers.SupplierID = '" . $_GET['SupplierID'] . "'";

	 $result =DB_query($sql,$db);
	 if (DB_error_no($db) !=0) {
		  echo "The supplier record selected: " . $_GET['SupplierID'] . " cannot be retrieved because - " . DB_error_msg($db);

		  if ($debug==1){
			   echo "<BR>The SQL used to retrieve the supplier details (and failed) was:<BR>$sql";
		  }
	 }

	 $myrow = DB_fetch_array($result);

	 $_SESSION['SuppTrans']->SupplierName = $myrow['SuppName'];
	 $_SESSION['SuppTrans']->TermsDescription = $myrow['Terms'];
	 $_SESSION['SuppTrans']->CurrCode = $myrow['CurrCode'];
	 $_SESSION['SuppTrans']->ExRate = $myrow['ExRate'];

	 if ($myrow['DaysBeforeDue']==0){
	 	$_SESSION['SuppTrans']->Terms = "1" . $myrow['DayInFollowingMonth'];
	 } else {
		$_SESSION['SuppTrans']->Terms = "0" . $myrow['DaysBeforeDue'];
	 }
	 $_SESSION['SuppTrans']->SupplierID = $_GET['SupplierID'];
	 $_SESSION['SuppTrans']->TaxDescription = $myrow['TaxDesc'];
	 $_SESSION['SuppTrans']->TaxRate = GetTaxRate($myrow['TaxID'],$DefaultTaxLevel,&$db);
	 $_SESSION['SuppTrans']->TaxGLCode = $myrow['TaxGLCode'];


	 $CompanyRecord = ReadInCompanyRecord($db);
	 $_SESSION['SuppTrans']->GLLink_Creditors = $CompanyRecord["GLLink_Creditors"];
	 $_SESSION['SuppTrans']->GRNAct = $CompanyRecord["GRNAct"];
	 $_SESSION['SuppTrans']->CreditorsAct = $CompanyRecord["CreditorsAct"];
	 $_SESSION['SuppTrans']->InvoiceOrCredit = "Credit Note";

} elseif (!isset($_SESSION['SuppTrans'])){
	echo "<P>To enter a supplier credit note the supplier must first be selected from the supplier selection screen.";
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>Select A Supplier to Enter an Credit Note For</A>";
	exit;
	/*It all stops here if there aint no supplier selected */
}


/* Set the session variables to the posted data from the form if the page has called itself */
if (isset($_POST['ExRate'])){
	$_SESSION['SuppTrans']->ExRate = $_POST['ExRate'];
	$_SESSION['SuppTrans']->Comments = $_POST['Comments'];
	$_SESSION['SuppTrans']->TranDate = $_POST['TranDate'];


	if (substr($_SESSION['SuppTrans']->$Terms,0,1)=="1") {
		$_SESSION["SuppTrans"]->DueDate = Date($DefaultDateFormat, Mktime(0,0,0,Date("m")+1,substr($_SESSION['SuppTrans']->$Terms,1),Date("y")));
	} else {
		$_SESSION["SuppTrans"]->DueDate = Date($DefaultDateFormat, Mktime(0,0,0,Date("m")+1,Date("d") + (int) substr($_SESSION['SuppTrans']->$Terms,1),Date("y")));
	}

	$_SESSION['SuppTrans']->SuppReference = $_POST['SuppReference'];


	if ($_SESSION['SuppTrans']->GLLink_Creditors ==1){

/*The link to GL from creditors is active so the total should be built up from GLPostings and GRN entries
if the link is not active then OvAmount must be entered manually. */

		$_SESSION['SuppTrans']->OvAmount =0; /* for starters */
		foreach ($_SESSION['SuppTrans']->GRNs as $GRN){
			$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + ($GRN->This_QuantityInv * $GRN->ChgPrice);
		}

		foreach ($_SESSION['SuppTrans']->GLCodes as $GLLine){
			$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + $GLLine->Amount;
		}

		$_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount,2);
	}else {
/*OvAmount must be entered manually */
		$_SESSION['SuppTrans']->OvAmount = round($_POST['OvAmount'],2);
	}
	if ($_POST['OverrideTax']=="Man"){
		$_SESSION['SuppTrans']->OvGST = round($_POST["OvGST"],2);
	} else {
		$_SESSION['SuppTrans']->OvGST = round($_SESSION['SuppTrans']->TaxRate * $_SESSION['SuppTrans']->OvAmount,2);
	}
}


if ($_POST["GRNS"]=="Enter Credit Against Goods Recd"){
	/*This ensures that any changes in the page are stored in the session before calling the grn page */
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SuppCreditGRNs.php?" . SID . "'>";
	echo "<P>You should automatically be forwarded to the entry of credit notes against goods received page. If this does not happen (if the browser doesn't support META Refresh) <a href='" . $rootpath . "/SuppCreditGRNs.php?" . SID . "'>click here</a> to continue.<br>";
	exit;
}
if ($_POST["Shipts"]=="Enter Credit Against Shipment"){
	/*This ensures that any changes in the page are stored in the session before calling the shipments page */
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SuppShiptChgs.php?" . SID . "'>";
	echo "<P>You should automatically be forwarded to the entry of credit notes against shipments page. If this does not happen (if the browser doesn't support META Refresh) <a href='" . $rootpath . "/SuppShiptChgs.php?" . SID . "'>click here</a> to continue.<br>";
	exit;
}
if ($_POST["GL"]=="Enter General Ledger Analysis"){
	/*This ensures that any changes in the page are stored in the session before calling the shipments page */
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SuppTransGLAnalysis.php?" . SID . "'>";
	echo "<P>You should automatically be forwarded to the entry of credit notes against the general ledger page. If this does not happen (if the browser doesn't support META Refresh) <a href='" . $rootpath . "/SuppTransGLAnalysis.php?" . SID . "'>click here</a> to continue.<br>";
	exit;
}
/* everything below here only do if a Supplier is selected
   fisrt add a header to show who we are making an credit note for */

echo "<CENTER><TABLE BORDER=2 COLSPAN=4><TR><TD class='tableheader'>Supplier</TD><TD class='tableheader'>Currency</TD><TD class='tableheader'>Terms</TD><TD class='tableheader'>Tax Authority</TD></TR>";

echo "<TR><TD><FONT color=red><B>" . $_SESSION['SuppTrans']->SupplierID . " - " . $_SESSION['SuppTrans']->SupplierName . "</TD><TD ALIGN=CENTER><FONT color=red><B>" . $_SESSION['SuppTrans']->CurrCode . "</TD><TD><FONT color=red><B>" . $_SESSION['SuppTrans']->TermsDescription . "</TD><TD ALIGN=CENTER><FONT color=red><B>" . $_SESSION['SuppTrans']->TaxDescription . " (" . (($_SESSION['SuppTrans']->TaxRate)*100) . "%)</TD></TR></TABLE></B></FONT>";


echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<TABLE>";
echo "<TR><TD><FONT COLOR=RED>Supplier's Credit Note Reference:</FONT></TD><TD><FONT SIZE=2><INPUT TYPE=TEXT SIZE=20 MAXLENGTH=20 NAME=SuppReference VALUE='" . $_SESSION['SuppTrans']->SuppReference . "'></TD></TR>";

if (!isset($_SESSION['SuppTrans']->TranDate)){
	$_SESSION['SuppTrans']->TranDate= Date($DefaultDateFormat, Mktime(0,0,0,Date("m"),Date("d")-1,Date("y")));
}
echo "<TR><TD><FONT COLOR=RED>Credit Note Date (in format " . $DefaultDateFormat . ") :</FONT></TD><TD><INPUT TYPE=TEXT SIZE=11 MAXLENGTH=10 NAME='TranDate' VALUE=" . $_SESSION['SuppTrans']->TranDate . "></TD></TR>";
echo "<TR><TD><FONT COLOR=RED>Exchange Rate:</FONT></TD><TD><INPUT TYPE=TEXT SIZE=11 MAXLENGTH=10 NAME='ExRate' VALUE=" . $_SESSION['SuppTrans']->ExRate . "></TD></TR>";
echo "</TABLE>";

echo "<BR><CENTER><INPUT TYPE=SUBMIT NAME='GRNS' VALUE='Enter Credit Against Goods Recd'> ";
echo "<INPUT TYPE=SUBMIT NAME='Shipts' VALUE='Enter Credit Against Shipment'> ";
if ( $_SESSION['SuppTrans']->GLLink_Creditors ==1){
	echo "<INPUT TYPE=SUBMIT NAME='GL' VALUE='Enter General Ledger Analysis'></CENTER>";
} else {
	echo "</CENTER>";
}


if (count($_SESSION['SuppTrans']->GRNs)>0){   /*if there are some GRNs selected for crediting then */
	/*Show all the selected GRNs so far from the SESSION['SuppInv']->GRNs array
	Note that the class for carrying GRNs refers to quantity invoiced read credited in this context*/

	echo "<TABLE CELLPADDING=2>";
	$TableHeader = "<TR><TD class='tableheader'>GRN</TD><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Quantity<BR>Credited</TD><TD class='tableheader'>Price Credited<BR>in " . $_SESSION['SuppTrans']->CurrCode . "</TD><TD class='tableheader'>Line Total<BR>In " . $_SESSION['SuppTrans']->CurrCode . "</TD></TR>";
	echo $TableHeader;
	$TotalGRNValue=0;

	foreach ($_SESSION["SuppTrans"]->GRNs as $EnteredGRN){

		echo "<TR><TD>" . $EnteredGRN->GRNNo . "</TD><TD>" . $EnteredGRN->ItemCode . "</TD><TD>" . $EnteredGRN->ItemDescription . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->This_QuantityInv,2) . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->ChgPrice,2) . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . "</TD><TD></TR>";

		$TotalGRNValue = $TotalGRNValue + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

		$i++;
		if ($i>15){
			$i=0;
			echo $TableHeader;
		}
	}

	echo "<TR><TD COLSPAN=5 ALIGN=RIGHT><FONT COLOR=red>Total Value of Goods Credited:</FONT></TD><TD ALIGN=RIGHT><FONT COLOR=red><U>" . number_format($TotalGRNValue,2) . "</U></FONT></TD></TR>";
	echo "</TABLE>";
}


if (count($_SESSION['SuppTrans']->Shipts)>0){   /*if there are any Shipment charges on the credit note*/

	echo "<TABLE CELLPADDING=2><TR><TD class='tableheader'>Shipment</TD><TD class='tableheader'>Credit Amount</B></TD></TR>";

	$TotalShiptValue=0;

	foreach ($_SESSION["SuppTrans"]->Shipts as $EnteredShiptRef){

		echo "<TR><TD>" . $EnteredShiptRef->ShiptRef . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredShiptRef->Amount,2) . "</TD></TR>";

		$TotalShiptValue = $TotalShiptValue + $EnteredShiptRef->Amount;

		$i++;
		if ($i>15){
			$i=0;
			echo "<TR><TD class='tableheader'>Shipment</B></TD><TD class='tableheader'>Credit Amount</B></TD></TR>";
		}
	}

	echo "<TR><TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=red>Total Credited Against Shipments:</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=4 COLOR=red><U>" . number_format($TotalShiptValue,2) . "</U></FONT></TD></TR>";
}


if ($_SESSION['SuppTrans']->GLLink_Creditors ==1){

	if (count($_SESSION["SuppTrans"]->GLCodes)>0){
		echo "<TABLE CELLPADDING=2>";
		$TableHeader = "<TR><TD class='tableheader'>Account</TD><TD class='tableheader'>Name</TD><TD class='tableheader'>Amount<BR>in " . $_SESSION['SuppTrans']->CurrCode . "</TD><TD class='tableheader'>Shipment</TD><TD class='tableheader'>Job</TD><TD class='tableheader'>Narrative</TD></TR>";
		echo $TableHeader;

		$TotalGLValue=0;

		foreach ($_SESSION["SuppTrans"]->GLCodes as $EnteredGLCode){

			echo "<TR><TD>" . $EnteredGLCode->GLCode . "</TD><TD>" . $EnteredGLCode->GLActName . "</TD><TD ALIGN=RIGHT>" . number_format($EnteredGLCode->Amount,2) . "</TD><TD>" . $EnteredGLCode->ShiptRef . "</TD><TD>" .$EnteredGLCode->JobRef . "</TD><TD>" . $EnteredGLCode->Narrative . "</TD></TR>";

			$TotalGLValue = $TotalGLValue + $EnteredGLCode->Amount;

			$i++;
			if ($i>15){
				$i=0;
				echo $TableHeader;
			}
		}

		echo "<TR><TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=red>Total:</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=4 COLOR=red><U>" . number_format($TotalGLValue,2) . "</U></FONT></TD></TR></TABLE>";
	}

	$_SESSION['SuppTrans']->OvAmount = round($TotalGRNValue + $TotalGLValue + $TotalShiptValue,2);
	echo "<TABLE><TR><TD><FONT COLOR=RED>Credit Amount in Supplier's Currency:</FONT></TD><TD ALIGN=RIGHT>" . number_format($_SESSION['SuppTrans']->OvAmount,2) . "</TD></TR>";
} else {
	echo "<TABLE><TR><TD><FONT COLOR=RED>Credit Amount in Supplier's Currency:</FONT></TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT SIZE=12 MAXLENGTH=10 NAME=OvAmount VALUE=" . number_format($_SESSION['SuppTrans']->OvAmount,2) . "></TD></TR>";
}

echo "<TR><TD><INPUT TYPE=Submit NAME='ToggleTaxMethod' VALUE='Change Tax Calculation Method'></TD><TD><SELECT NAME='OverRideTax'>";

if ($_POST['OverRideTax']=="Man"){
	echo "<OPTION VALUE='Auto'>Automatic<OPTION SELECTED VALUE='Man'>Manual";
	if (!isset($_SESSION['SuppTrans']->OvGST) OR $_SESSION['SuppTrans']->OvGST==""){
		$_SESSION['SuppTrans']->OvGST=0;
	}

} else {
	echo "<OPTION SELECTED VALUE='Auto'>Automatic<OPTION VALUE='Man'>Manual";
	$_SESSION['SuppTrans']->OvGST = $_SESSION['SuppTrans']->TaxRate * $_SESSION['SuppTrans']->OvAmount;
}

echo "</SELECT></TD></TR>";


if ($_POST["OverRideTax"]=="Man"){
	$_SESSION['SuppTrans']->OvGST = $_POST['OvGST'];
	echo "<TR><TD><FONT COLOR=RED>Tax:</FONT></TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT SIZE=6 MAXLENGTH=6 NAME=OvGST VALUE=" . number_format($_SESSION['SuppTrans']->OvGST,2) . "></TD></TR>";
} else {
	echo "<TR><TD><FONT COLOR=RED>Tax:</FONT></TD><TD ALIGN=RIGHT>" . number_format($_SESSION['SuppTrans']->OvGST,2) . "</TD></TR>";
}
$DisplayTotal = number_format(($_SESSION['SuppTrans']->OvAmount + $_SESSION['SuppTrans']->OvGST), 2);

echo "<TR><TD><FONT COLOR=RED>Credit Note Total</FONT></TD><TD ALIGN=RIGHT><B>$DisplayTotal</B></TD></TR></TABLE>";

echo "<TABLE><TR><TD><FONT COLOR=RED>Comments</FONT></TD><TD><TEXTAREA NAME=Comments COLS=40 ROWS=2>" .  $_SESSION['SuppTrans']->Comments . "</TEXTAREA></TD></TR></TABLE>";

echo "<P><INPUT TYPE=SUBMIT NAME='PostCreditNote' Value='Enter Credit Note'>";


if ($_POST['PostCreditNote']=="Enter Credit Note"){

/*First do input reasonableness checks
then do the updates and inserts to process the credit note entered */

	$InputError=False;
	If ($_SESSION['SuppTrans']->OvGST + $_SESSION['SuppTrans']->OvAmount <=0){
		$InputError=True;
		echo "<P>The credit note as entered cannot be processed because the total amount of the credit note is less than or equal to 0! Credit notes are expected to entered as positive amounts to credit.";
	} elseif (strlen($_SESSION['SuppTrans']->SuppReference)<1){
		$InputError=True;
		echo "<P>The credit note as entered cannot be processed because the there is no suppliers credit note number or reference entered. The supplier credit note number must be entered.";
	} elseif (!is_date($_SESSION['SuppTrans']->TranDate)){
		$InputError=True;
		echo "<P>The credit note as entered cannot be processed because the date entered is not in the format" . $DefaultDateFormat;
	} elseif (DateDiff(Date($DefaultDateFormat), $_SESSION['SuppTrans']->TranDate, "d")<0){
		$InputError=True;
		echo "<P>The credit note as entered cannot be processed because the date is after today. Purchase credit notes are expected to have a date prior to or today";
	}elseif ($_SESSION['SuppTrans']->ExRate<=0){
		$InputError=True;
		echo "<P>The credit note as entered cannot be processed because the exchange rate for the credit note has been entered as a negative or zero number. The exchange rate is expected to show how many of the suppliers currency there are in 1 of the local currency.";
	}elseif ($_SESSION['SuppTrans']->OvAmount < round($TotalShiptValue + $TotalGLValue + $TotalGRNValue,2)){
		echo "<P>The credit note total as entered is less than the sum of the shipment charges, the general ledger entires (if any) and the charges for goods received. There must be a mistake somewhere, the credit note as entered will not be processed.";
		$InputError = True;
	} else {

	/* SQL to process the postings for purchase credit note */

	/*Start an SQL transaction */

		$SQL = "Begin";
		$Result = DB_query($SQL,$db);
		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The database does not support transactions: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to start an SQL transaction was used:<BR>$SQL<BR>";
			}
			exit;
		}

		/*Get the next transaction number for internal purposes and the period to post GL transactions in based on the credit note date*/
		$CreditNoteNo = GetNextTransNo(21, $db);
		$PeriodNo = GetPeriod($_SESSION['SuppTrans']->TranDate, $db);
		$SQLCreditNoteDate = FormatDateForSQL($_SESSION['SuppTrans']->TranDate);


		if ($_SESSION['SuppTrans']->GLLink_Creditors ==1){
		/*Loop through the GL Entries and create a debit posting for each of the accounts entered */
			$LocalTotal = 0;

			/*the postings here are a little tricky, the logic goes like this:

			> if its a shipment entry then the cost must go against the GRN suspense account defined in the company record

			> if its a general ledger amount it goes straight to the account specified

			> if its a GRN amount credited then there are two possibilities:

			1 The PO line is on a shipment.
			The whole charge goes to the GRN suspense account pending the closure of the
			shipment where the variance is calculated on the shipment as a whole and the clearing entry to the GRN suspense
			is created. Also, shipment records are created for the charges in local currency.

			2. The order line item is not on a shipment
			The whole amount of the credit is written off to the purchase price variance account applicable to the
			stock category record of the stock item being credited.
			Or if its not a stock item but a nominal item then the GL account in the orignal order is used for the
			price variance account.
			*/

			foreach ($_SESSION["SuppTrans"]->GLCodes as $EnteredGLCode){

			/*GL Items are straight forward - just do the credit postings to the GL accounts specified -
			the debit is to creditors control act  done later for the total credit note value + tax*/

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount, JobRef) VALUES (21, " . $CreditNoteNo . ", '" . $SQLCreditNoteDate . "', " . $PeriodNo . ", " . $EnteredGLCode->GLCode . ", '" . $_SESSION['SuppTrans']->SupplierID . " " . $EnteredGLCode->Narrative . "', " . round(-$EnteredGLCode->Amount/$_SESSION['SuppTrans']->ExRate,2) . ", '" . $EnteredGLCode->JobRef . "')";
				$Result = DB_query($SQL,$db);
				$LocalTotal += ($EnteredGLCode->Amount/$_SESSION['SuppTrans']->ExRate);
				if (DB_error_no($db) !=0){
					echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction could not be added because " . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GL transaction was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}
			}

			foreach ($_SESSION["SuppTrans"]->Shipts as $ShiptChg){

			/*shipment postings are also straight forward - just do the credit postings to the GRN suspense account
			these entries are reversed from the GRN suspense when the shipment is closed - entries only to open shipts*/

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (21, " . $CreditNoteNo . ", '" . $SQLCreditNoteDate . "', " . $PeriodNo . ", " . $_SESSION['SuppTrans']->GRNAct . ", '" . $_SESSION['SuppTrans']->SupplierID . " Shipment credit against " . $ShiptChg->ShiptRef . "', " . round(-$ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate,2) . ")";
				$Result = DB_query($SQL,$db);
				$LocalTotal += round($ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate,2);
				if (DB_error_no($db) !=0){
					echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction for the shipment " . $ShiptChg->ShiptRef . " could not be added because " . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GL transaction was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}
			}

			foreach ($_SESSION["SuppTrans"]->GRNs as $EnteredGRN){

				if (strlen($EnteredGRN->ShiptRef)==0 OR $EnteredGRN->ShiptRef=="" OR $EnteredGRN->ShiptRef==0){ /*so its not a shipment item */

					$PurchPriceVar = round($EnteredGRN->This_QuantityInv * ($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate),2);

					/*Yes but where to post this difference to - if its a stock item the variance account must be retreived from the stock category record
					if its a nominal purchase order item with no stock item then  post it to the account specified in the purchase order detail record */

					if ($PurchPriceVar !=0){ /* don't bother with this lot if there is no value to post ! */
						if (strlen($EnteredGRN->ItemCode)>0 OR $EnteredGRN->ItemCode!=""){ /*so it is a stock item */

							/*need to get the stock category record for this stock item - this is function in SQL_CommonFunctions.inc */
							$StockGLCode = GetStockGLCode($EnteredGRN->ItemCode,$db);
							$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (21, " . $CreditNoteNo . ", '" . $SQLCreditNoteDate . "', " . $PeriodNo . ", " . $StockGLCode["PurchPriceVarAct"] . ", '" . $_SESSION['SuppTrans']->SupplierID . " - GRN Credit Note " . $EnteredGRN->GRNNo . " - " . $EnteredGRN->ItemCode . " x " . $EnteredGRN->This_QuantityInv . " x  " . number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate),2)  . "', " . (-$PurchPriceVar) . ")";
							$Result = DB_query($SQL,$db);

							if (DB_error_no($db) !=0){
								echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction could not be added for the price variance of the stock item, because " . DB_error_msg($db);
								if ($debug==1){
									echo "<BR>The following SQL to insert the GL transaction was used:<BR>$SQL<BR>";
								}
								$SQL = "rollback";
								$Result = DB_query($SQL,$db);
								exit;
							}
						} else {
						/* its a nominal purchase order item that is not on a shipment so post the whole lot to the GLCode specified in the order, the purchase price var is actually the diff between the
						order price and the actual credit note price since the std cost was made equal to the order price in local currency at the time
						the goods were received */
							$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (21, " . $CreditNoteNo . ", '" . $SQLCreditNoteDate . "', " . $PeriodNo . ", " . $EnteredGRN->GLCode . ", '" . $_SESSION['SuppTrans']->SupplierID . " - GRN Credit Note " . $EnteredGRN->GRNNo . " - " . $EnteredGRN->ItemDescription . " x " . $EnteredGRN->This_QuantityInv . " x  price var " . number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate),2) . "', " . (-$PurchPriceVar) . ")";
							$Result = DB_query($SQL,$db);

							if (DB_error_no($db) !=0){
								echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction could not be added for the price variance of the stock item, because " . DB_error_msg($db);
								if ($debug==1){
									echo "<BR>The following SQL to insert the GL transaction was used:<BR>$SQL<BR>";
								}
								$SQL = "rollback";
								$Result = DB_query($SQL,$db);
								exit;
							}

						}
					}

				} else {
					/*then its a purchase order item on a shipment - whole charge amount to GRN suspense pending closure of the shipment	when the variance is calculated and the GRN act cleared up for the shipment */

					$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (21, " . $CreditNoteNo . ", '" . $SQLCreditNoteDate . "', " . $PeriodNo . ", " . $_SESSION['SuppTrans']->GRNAct . ", '" . $_SESSION['SuppTrans']->SupplierID . " - GRN " . $EnteredGRN->GRNNo . " - " . $EnteredGRN->ItemCode . " x " . $EnteredGRN->This_QuantityInv . " @ " . $_SESSION['SuppTrans']->CurrCode . $EnteredGRN->ChgPrice . " @ a rate of " . $_SESSION['SuppTrans']->ExRate . "', " . round(-$EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) / $_SESSION['SuppTrans']->ExRate . ")";
					$Result = DB_query($SQL,$db);

					if (DB_error_no($db) !=0){
						echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction could not be added because " . DB_error_msg($db);
						if ($debug==1){
							echo "<BR>The following SQL to insert the GL transaction was used:<BR>$SQL<BR>";
						}
						$SQL = "rollback";
						$Result = DB_query($SQL,$db);
						exit;
					}
				}
				$LocalTotal += round(($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) / $_SESSION['SuppTrans']->ExRate,2);
			} /* end of GRN postings */

			if ($debug==1 AND abs(($_SESSION['SuppTrans']->OvAmount/ $_SESSION['SuppTrans']->ExRate) - $LocalTotal)>0.004){

				echo "<P>The total posted to the credit accounts is $LocalTotal but the sum of OvAmount converted at ExRate = " . ($_SESSION['SuppTrans']->OvAmount / $_SESSION['SuppTrans']->ExRate);
			}

			if ($_SESSION['SuppTrans']->OvGST !=0){

				/* Now the TAX account */
				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (21, " . $CreditNoteNo . ", '" . $SQLCreditNoteDate . "', " . $PeriodNo . ", " . $_SESSION['SuppTrans']->TaxGLCode . ", '" . $_SESSION['SuppTrans']->SupplierID . " - Credit Note " . $_SESSION['SuppTrans']->SuppReference . " " . $_SESSION['SuppTrans']->CurrCode . $_SESSION['SuppTrans']->OvGST  . " @ a rate of " . $_SESSION['SuppTrans']->ExRate . "', " . round(-$_SESSION['SuppTrans']->OvGST/ $_SESSION['SuppTrans']->ExRate,2) . ")";
				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction for the tax on the suppliers credit note could not be added because " . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GL transaction was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}
			}

			/* Now the control account */
			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (21, " . $CreditNoteNo . ", '" . $SQLCreditNoteDate . "', " . $PeriodNo . ", " . $_SESSION['SuppTrans']->CreditorsAct . ", '" . $_SESSION['SuppTrans']->SupplierID . " - Credit Note " . $_SESSION['SuppTrans']->SuppReference . " " . $_SESSION['SuppTrans']->CurrCode . number_format($_SESSION['SuppTrans']->OvAmount + $_SESSION['SuppTrans']->OvGST,2)  . " @ a rate of " . $_SESSION['SuppTrans']->ExRate . "', " . round($LocalTotal + ($_SESSION['SuppTrans']->OvGST / $_SESSION['SuppTrans']->ExRate),2) . ")";
			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction for the control total could not be added because " . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the GL transaction was used:<BR>$SQL<BR>";
				}
				$SQL = "rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}
		} /*Thats the end of the GL postings */

	/*Now insert the credit note into the SuppTrans table*/

		$SQL = "INSERT INTO SuppTrans (TransNo, Type, SupplierNo, SuppReference, TranDate, DueDate, OvAmount, OvGST, Rate, TransText)
			VALUES (". $CreditNoteNo . ",21, '" . $_SESSION['SuppTrans']->SupplierID . "', '" . $_SESSION['SuppTrans']->SuppReference . "',
			'" . $SQLCreditNoteDate . "', '" . FormatDateForSQL($_SESSION["SuppTrans"]->DueDate) . "', " . round(-$_SESSION['SuppTrans']->OvAmount,2) . ", " .(-$_SESSION['SuppTrans']->OvGST) . ", " .  $_SESSION['SuppTrans']->ExRate . ", '" . $_SESSION['SuppTrans']->Comments . "')";

		$Result = DB_query($SQL,$db);

		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The supplier credit note transaction could not be added to the database because: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to insert the supplier credit note was used:<BR>$SQL<BR>";
			}

			$SQL = "rollback";
			$Result = DB_query($SQL,$db);
			exit;
		}
		/* Now update the GRN and PurchOrderDetails records for amounts credited */
		foreach ($_SESSION["SuppTrans"]->GRNs as $EnteredGRN){

			$SQL = "UPDATE PurchOrderDetails SET QtyInvoiced = QtyInvoiced - " . $EnteredGRN->This_QuantityInv . " WHERE PODetailItem = " . $EnteredGRN->PODetailItem;
			$Result = DB_query($SQL,$db);

			if (DB_error_no($db) !=0){
				echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The quantity credited of the purchase order line could not be updated because " . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to update the purchase order details was used:<BR>$SQL<BR>";
				}
				$SQL= "rollback";
				$Result = DB_query($SQL, $db);
				exit;
			}

			$SQL = "UPDATE GRNs SET QuantityInv = QuantityInv - " . $EnteredGRN->This_QuantityInv . " WHERE GRNNo = " . $EnteredGRN->GRNNo;
			$Result = DB_query($SQL,$db);

			if (DB_error_no($db) !=0){
				echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The quantity credited off the goods received record could not be updated because " . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to update the GRN quantity credited was used:<BR>$SQL<BR>";
				}
				$SQL= "rollback";
				$Result = DB_query($SQL, $db);
				exit;
			}


			/*Update the shipment's accum value for the total local cost of shipment items being credited
			the total value credited against shipments is apportioned between all the items on the shipment
			later when the shipment is closed*/

			if (strlen($EnteredGRN->ShiptRef)>0 AND $EnteredGRN->ShiptRef!=0){

				/* and insert the shipment charge records */
				$SQL = "INSERT INTO ShipmentCharges (ShiptRef, TransType, TransNo, StockID, Value) VALUES (" . $EnteredGRN->ShiptRef . ", 21, " . $CreditNoteNo . ", '" . $EnteredGRN->ItemCode . "', " . round(-$EnteredGRN->This_QuantityInv * $EnteredGRN->ChgPrice / $_SESSION['SuppTrans']->ExRate,2) . ")";
				$Result = DB_query($SQL,$db);

				if (DB_error_no($db) !=0){
					echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The shipment charge record for the shipment " . $EnteredGRN->ShiptRef . " could not be added because " . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the Shipment charge record was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}

			}

		} /* end of the loop to do the updates for the quantity of order items the supplier has credited */

		/*Add shipment charges records as necessary */
		foreach ($_SESSION["SuppTrans"]->Shipts as $ShiptChg){

			$SQL = "INSERT INTO ShipmentCharges (ShiptRef, TransType, TransNo, Value) VALUES (" . $ShiptChg->ShiptRef . ", 21, " . $CreditNoteNo . ", " . (-$ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate) . ")";
			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The shipment charge record for the shipment " . $ShiptChg->ShiptRef . " could not be added because " . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the Shipment charge record was used:<BR>$SQL<BR>";
				}
				$SQL = "rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}
		}


		$SQL="Commit";
		$Result = DB_query($SQL,$db);

		unset($_SESSION['SuppTrans']->GRNs);
		unset($_SESSION['SuppTrans']->Shipts);
		unset($_SESSION['SuppTrans']->GLCodes);
		unset($_SESSION['SuppTrans']);

		echo "<P>Supplier credit note number $CreditNoteNo has been processed<BR>";
		echo "<P><A HREF='$rootpath/SelectSupplier.php'>Enter Another Credit Note</A>";
	}
} /*end of process credit note */

echo "</form>";
include("includes/footer.inc");
?>

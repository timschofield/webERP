<?php
/* $Revision: 1.11 $ */
/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

$PageSecurity = 5;

include('includes/DefineSuppTransClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Enter Supplier Invoice');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['SupplierID'])){

 /*It must be a new invoice entry - clear any existing invoice details from the SuppTrans object and initiate a newy*/
	if (isset( $_SESSION['SuppTrans'])){
		unset ( $_SESSION['SuppTrans']->GRNs);
		unset ( $_SESSION['SuppTrans']->GLCodes);
		unset ( $_SESSION['SuppTrans']);
	}

	 if (isset( $_SESSION['SuppTransTmp'])){
		unset ( $_SESSION['SuppTransTmp']->GRNs);
		unset ( $_SESSION['SuppTransTmp']->GLCodes);
		unset ( $_SESSION['SuppTransTmp']);
	}
	 Session_register('SuppInv');
	 $_SESSION['SuppTrans'] = new SuppTrans;

/*Now retrieve supplier information - name, currency, default ex rate, terms, tax rate etc */

	 $sql = "SELECT suppliers.suppname,
	 		paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
	 		suppliers.currcode,
			currencies.rate AS exrate,
			taxauthorities.description AS taxdesc,
	 		taxauthorities.taxid,
			taxauthorities.purchtaxglaccount AS taxglcode
	 	FROM suppliers,
			currencies,
			paymentterms,
			taxauthorities
	 	WHERE suppliers.taxauthority = taxauthorities.taxid
	 	AND suppliers.currcode=currencies.currabrev
	 	AND suppliers.paymentterms=paymentterms.termsindicator
	 	AND suppliers.supplierid = '" . $_GET['SupplierID'] . "'";

	$ErrMsg = _('The supplier record selected') . ': ' . $_GET['SupplierID'] . ' ' ._('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');

	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);


	$myrow = DB_fetch_array($result);

	$_SESSION['SuppTrans']->SupplierName = $myrow['suppname'];
	$_SESSION['SuppTrans']->TermsDescription = $myrow['terms'];
	$_SESSION['SuppTrans']->CurrCode = $myrow['currcode'];
	$_SESSION['SuppTrans']->ExRate = $myrow['exrate'];

	if ($myrow['daysbeforedue'] == 0){
		 $_SESSION['SuppTrans']->Terms = '1' . $myrow['dayinfollowingmonth'];
	} else {
		 $_SESSION['SuppTrans']->Terms = '0' . $myrow['daysbeforedue'];
	}
	$_SESSION['SuppTrans']->SupplierID = $_GET['SupplierID'];
	$_SESSION['SuppTrans']->TaxDescription = $myrow['taxdesc'];

	$LocalTaxAuthResult = DB_query("SELECT taxauthority FROM locations WHERE loccode='" .
	 										  $_SESSION['UserStockLocation'] . "'", $db);

	if(DB_num_rows($LocalTaxAuthResult)==0){
		prnMsg(_('The tax authority associated with your user account has not been set up in this database. Tax calculations are based on the tax authority of the supplier and the tax authority of the user entering the invoice. The system administrator should redefine your account with a valid default stocking location and this location should refer to a valid tax authority'),'error');
		include('includes/footer.inc');
		exit;
	}
	
	$LocalTaxAuthRow = DB_fetch_row($LocalTaxAuthResult);

	
	$_SESSION['SuppTrans']->TaxRate = GetTaxRate($myrow['taxid'],$LocalTaxAuthRow[0], $_SESSION['DefaultTaxLevel'], $db);
	$_SESSION['SuppTrans']->TaxGLCode = $myrow['taxglcode'];

	$_SESSION['SuppTrans']->GLLink_Creditors = $_SESSION['CompanyRecord']['gllink_creditors'];
	$_SESSION['SuppTrans']->GRNAct = $_SESSION['CompanyRecord']['grnact'];
	$_SESSION['SuppTrans']->CreditorsAct = $_SESSION['CompanyRecord']['creditorsact'];

	$_SESSION['SuppTrans']->InvoiceOrCredit = 'Invoice';

} elseif (!isset( $_SESSION['SuppTrans'])){

	prnMsg( _('To enter a supplier invoice the supplier must first be selected from the supplier selection screen'),'warn');
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Select A Supplier to Enter an Invoice For') . '</A>';
	include('includes/footer.inc');
	exit;

	/*It all stops here if there ain't no supplier selected */
}

/* Set the session variables to the posted data from the form if the page has called itself */
if (isset($_POST['ExRate'])){
	$_SESSION['SuppTrans']->ExRate = $_POST['ExRate'];
	$_SESSION['SuppTrans']->Comments = $_POST['Comments'];
	$_SESSION['SuppTrans']->TranDate = $_POST['TranDate'];

	if (substr( $_SESSION['SuppTrans']->Terms,0,1)=="1") { /*Its a day in the following month when due */
		$_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date("m")+1, substr( $_SESSION['SuppTrans']->Terms,1),Date("y")));
	} else { /*Use the Days Before Due to add to the invoice date */
		$_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date("m"),Date("d") + (int) substr( $_SESSION['SuppTrans']->$Terms,1),Date("y")));
	}

	$_SESSION['SuppTrans']->SuppReference = $_POST['SuppReference'];

	if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){

/*The link to GL from creditors is active so the total should be built up from GLPostings and GRN entries
if the link is not active then OvAmount must be entered manually. */

		$_SESSION['SuppTrans']->OvAmount = 0; /* for starters */
		if (count($_SESSION['SuppTrans']->GRNs) > 0){
			foreach ( $_SESSION['SuppTrans']->GRNs as $GRN){
				$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + ($GRN->This_QuantityInv * $GRN->ChgPrice);
			}
		}
		if (count($_SESSION['SuppTrans']->GLCodes) > 0){
			foreach ( $_SESSION['SuppTrans']->GLCodes as $GLLine){
				$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + $GLLine->Amount;
			}
		}
		if (count($_SESSION['SuppTrans']->Shipts) > 0){
			foreach ( $_SESSION['SuppTrans']->Shipts as $ShiptLine){
				$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + $ShiptLine->Amount;
			}
		}
		$_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount,2);
	}else {
/*OvAmount must be entered manually */
		 $_SESSION['SuppTrans']->OvAmount = round($_POST['OvAmount'],2);
	}
	if ($_POST['OverrideTax']=='Man'){
		 $_SESSION['SuppTrans']->OvGST = round($_POST['OvGST'],2);
	} else {
		 $_SESSION['SuppTrans']->OvGST = round($_SESSION['SuppTrans']->TaxRate * $_SESSION['SuppTrans']->OvAmount,2);
	}
}

if ($_POST['GRNS'] == _('Enter Against Goods Recd')){
	/*This ensures that any changes in the page are stored in the session before calling the grn page */
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SuppInvGRNs.php?" . SID . "'>";
	echo '<P>' . _('You should automatically be forwarded to the entry of invoices against goods received page') .
		  '. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
		  "<A HREF='" . $rootpath . "/SuppInvGRNs.php?" . SID . "'>" . _('click here') . '</a> ' . _('to continue') . '.<BR>';
	exit;
}
if ($_POST['Shipts'] == _('Enter Against Shipment')){
	/*This ensures that any changes in the page are stored in the session before calling the shipments page */
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SuppShiptChgs.php?" . SID . "'>";
	echo '<P>' . _('You should automatically be forwarded to the entry of invoices against goods received page') .
		  '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh'). ') ' .
		  "<A HREF='" . $rootpath . "/SuppShiptChgs.php?" . SID . "'>" . _('click here') . '</a> ' . _('to continue') . '.<BR>';
	exit;
}
if ($_POST['GL'] == _('Enter General Ledger Analysis')){
	/*This ensures that any changes in the page are stored in the session before calling the shipments page */
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SuppTransGLAnalysis.php?" . SID . "'>";
	echo '<P>' . _('You should automatically be forwarded to the entry of invoices against the general ledger page') .
		  '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh'). ') ' .
		  "<A HREF='" . $rootpath . "/SuppTransGLAnalysis.php?" . SID . "'>" . _('click here') . '</a> ' . _('to continue') . '.<BR>';
	exit;
}

/* everything below here only do if a Supplier is selected
   fisrt add a header to show who we are making an invoice for */

echo "<CENTER><TABLE BORDER=2 COLSPAN=4><TR><TD CLASS='tableheader'>" . _('Supplier') .
	  "</TD><TD CLASS='tableheader'>" . _('Currency') .
	  "</TD><TD CLASS='tableheader'>" . _('Terms').
	  "</TD><TD CLASS='tableheader'>" . _('Tax Authority') . '</TD></TR>';

echo '<TR><TD><FONT COLOR=blue><B>' . $_SESSION['SuppTrans']->SupplierID . ' - ' .
	  $_SESSION['SuppTrans']->SupplierName . '</B></FONT></TD>
	  <TD ALIGN=CENTER><FONT COLOR=blue><B>' .  $_SESSION['SuppTrans']->CurrCode . '</B></FONT></TD>
	  <TD><FONT COLOR=blue><B>' . $_SESSION['SuppTrans']->TermsDescription . '</B></FONT></TD>
	  <TD ALIGN=CENTER><FONT COLOR=blue><B>' . $_SESSION['SuppTrans']->TaxDescription . ' (' . (($_SESSION['SuppTrans']->TaxRate)*100) .
	  '%)</B></FONT></TD>
	  </TR>
	  </TABLE>';

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo '<TABLE>';
echo '<TR><TD>' . _('Supplier Invoice Reference') . ":</TD>
	   <TD><FONT SIZE=2><INPUT TYPE=TEXT SIZE=20 MAXLENGTH=20 NAME=SuppReference VALUE='" .
	   $_SESSION['SuppTrans']->SuppReference . "'></TD>";

if (!isset($_SESSION['SuppTrans']->TranDate)){
	 $_SESSION['SuppTrans']->TranDate= Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')-1,Date('y')));
}
echo '<TD>' . _('Invoice Date') . ' (' . _('in format') . ' ' . $_SESSION['DefaultDateFormat'] . ") :</TD>
		<TD><INPUT TYPE=TEXT SIZE=11 MAXLENGTH=10 NAME='TranDate' VALUE=" . $_SESSION['SuppTrans']->TranDate . '></TD>';

echo '<TD>' . _('Exchange Rate') . ":</TD>
		<TD><INPUT TYPE=TEXT SIZE=11 MAXLENGTH=10 NAME='ExRate' VALUE=" . $_SESSION['SuppTrans']->ExRate . '></TD></TR>';
echo '</TABLE>';

echo "<BR><CENTER><INPUT TYPE=SUBMIT NAME='GRNS' VALUE='" . _('Enter Against Goods Recd') . "'> ";
if ($hide_incomplete_features == False) {
echo "<INPUT TYPE=SUBMIT NAME='Shipts' VALUE='" . _('Enter Against Shipment') . "'> ";
}

if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){
	echo "<INPUT TYPE=SUBMIT NAME='GL' VALUE='" . _('Enter General Ledger Analysis') . "'></CENTER>";
} else {
	echo '</CENTER>';
}


if (count( $_SESSION['SuppTrans']->GRNs)>0){   /*if there are any GRNs selected for invoicing then */
	/*Show all the selected GRNs so far from the SESSION['SuppInv']->GRNs array */

	echo '<TABLE CELLPADDING=2>';
	$tableheader = "<TR BGCOLOR=#800000><TD CLASS='tableheader'>" . _('Seq') . " #</TD>
			<TD CLASS='tableheader'>" . _('Item Code') . "</TD>
			<TD CLASS='tableheader'>" . _('Description') . "</TD>
			<TD CLASS='tableheader'>" . _('Quantity Charged') . "</TD>
			<TD CLASS='tableheader'>" . _('Price in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</TD>
			<TD CLASS='tableheader'>" . _('Line Total') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</TD></TR>';
	echo $tableheader;

	$TotalGRNValue = 0;

	foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

		echo '<TR><TD>' . $EnteredGRN->GRNNo . '</TD><TD>' . $EnteredGRN->ItemCode .
			  '</TD><TD>' . $EnteredGRN->ItemDescription . '</TD><TD ALIGN=RIGHT>' .
			  number_format($EnteredGRN->This_QuantityInv,2) . '</TD><TD ALIGN=RIGHT>' .
			  number_format($EnteredGRN->ChgPrice,2) . '</TD><TD ALIGN=RIGHT>' .
			  number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . '</TD><TD></TR>';

		$TotalGRNValue = $TotalGRNValue + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

		$i++;
		if ($i > 15){
			$i = 0;
			echo $tableheader;
		}
	}

	echo '<TR><TD COLSPAN=5 ALIGN=RIGHT><FONT COLOR=blue>' . _('Total Value of Goods Charged') . ':</FONT></TD>
		<TD ALIGN=RIGHT><FONT COLOR=blue><U>' . number_format($TotalGRNValue,2) . '</U></FONT></TD></TR>';
	echo '</TABLE>';
}

if (count( $_SESSION['SuppTrans']->Shipts) > 0){   /*if there are any Shipment charges on the invoice*/

	echo '<TABLE CELLPADDING=2>';
	$TableHeader = "<TR><TD CLASS='tableheader'>" . _('Shipment') . "</TD>
			<TD CLASS='tableheader'>" . _('Amount') . '</TD></TR>';
	echo $TableHeader;

	$TotalShiptValue = 0;

	foreach ($_SESSION['SuppTrans']->Shipts as $EnteredShiptRef){

		echo '<TR><TD>' . $EnteredShiptRef->ShiptRef . '</TD><TD ALIGN=RIGHT>' .
			  number_format($EnteredShiptRef->Amount,2) . '</TD></TR>';

		$TotalShiptValue = $TotalShiptValue + $EnteredShiptRef->Amount;

		$i++;
		if ($i > 15){
			$i = 0;
			echo $TableHeader;
		}
	}

	echo '<TR><TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=blue>' . _('Total') . ':</FONT></TD>
		<TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>' .  number_format($TotalShiptValue,2) . '</U></FONT></TD></TR>';
}


if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){

	if (count($_SESSION['SuppTrans']->GLCodes) > 0){
		echo '<TABLE CELLPADDING=2>';
		$TableHeader = "<TR><TD CLASS='tableheader'>" . _('Account') .
				"</TD><TD CLASS='tableheader'>" . _('Name') .
				"</TD><TD CLASS='tableheader'>" . _('Amount') . '<BR>' . _('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</TD>
				<TD CLASS='tableheader'>" . _('Shipment') ."</TD>
				<TD CLASS='tableheader'>" . _('Job') . 	"</TD>
				<TD class='tableheader'>" . _('Narrative') . '</TD></TR>';
		echo $TableHeader;

		$TotalGLValue = 0;

		foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

			echo '<TR><TD>' . $EnteredGLCode->GLCode . '</TD><TD>' . $EnteredGLCode->GLActName .
				  '</TD><TD ALIGN=RIGHT>' . number_format($EnteredGLCode->Amount,2) .
				  '</TD><TD>' . $EnteredGLCode->ShiptRef . '</TD><TD>' .$EnteredGLCode->JobRef .
				  '</TD><TD>' . $EnteredGLCode->Narrative . '</TD></TR>';

			$TotalGLValue = $TotalGLValue + $EnteredGLCode->Amount;

			$i++;
			if ($i > 15){
				$i = 0;
				echo $TableHeader;
			}
		}

		echo '<TR><TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=blue>' . _('Total') .  ':</FONT></TD>
				<TD ALIGN=RIGHT><FONT SIZE=4 COLOR=blue><U>' .  number_format($TotalGLValue,2) . '</U></FONT></TD>
			</TR></TABLE>';
	}

	if (!isset($TotalGRNValue)){
		$TotalGRNValue = 0;
	}
	if (!isset($TotalGLValue)){
		$TotalGLValue = 0;
	}
	if (!isset($TotalShiptValue)){
		$TotalShiptValue = 0;
	}
	
	$_SESSION['SuppTrans']->OvAmount = $TotalGRNValue + $TotalGLValue + $TotalShiptValue;
	 
	echo '<TABLE><TR><TD>' . _('Amount in supplier currency') . ':</TD><TD ALIGN=RIGHT>' .
		  number_format( $_SESSION['SuppTrans']->OvAmount,2) . '</TD></TR>';
} else {
	echo '<TABLE><TR><TD>' . _('Amount in supplier currency') .
		  ':</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT SIZE=12 MAXLENGTH=10 NAME=OvAmount VALUE=' .
		  number_format( $_SESSION['SuppTrans']->OvAmount,2) . '></TD></TR>';
}

echo "<TR><TD><INPUT TYPE=Submit NAME='ToggleTaxMethod' VALUE='" . _('Change Tax Calculation Method') . "'></TD><TD><SELECT NAME='OverRideTax'>";

if ($_POST['OverRideTax'] == 'Man'){
	echo "<OPTION VALUE='Auto'>" . _('Automatic') . "<OPTION SELECTED VALUE='Man'>" . _('Manual');
	if (!isset( $_SESSION['SuppTrans']->OvGST) OR $_SESSION['SuppTrans']->OvGST == ''){
		 $_SESSION['SuppTrans']->OvGST = 0;
	}

} else {
	echo "<OPTION SELECTED VALUE='Auto'>" . _('Automatic') . "<OPTION VALUE='Man'>" . _('Manual');
	 $_SESSION['SuppTrans']->OvGST = round($_SESSION['SuppTrans']->TaxRate * $_SESSION['SuppTrans']->OvAmount,2);
}

echo '</SELECT></TD></TR>';

$_SESSION['SuppTrans']->OvGST = round($_SESSION['SuppTrans']->OvGST,2);
$_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount,2);

if ($_POST['OverRideTax'] == 'Man'){

	if (is_numeric($_POST['OvGST'])){  /*If Post['OvGST'] has not yet been set cos first call to page then dont alter it from the previous auto calculation */
	 	$_SESSION['SuppTrans']->OvGST = round($_POST['OvGST'],2);
	}

	echo '<TR><TD>' . _('Tax') . ':</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT SIZE=12 MAXLENGTH=12 NAME=OvGST VALUE=' .
		  round( $_SESSION['SuppTrans']->OvGST,2) . '></TD></TR>';

} else {

	echo '<TR><TD>' . _('Tax') . ':</TD><TD ALIGN=RIGHT>' .
		  number_format( $_SESSION['SuppTrans']->OvGST,2) . '</TD></TR>';

}

$DisplayTotal = number_format(( $_SESSION['SuppTrans']->OvAmount + $_SESSION['SuppTrans']->OvGST), 2);

echo '<TR><TD>' . _('Invoice Total') . ':</TD><TD ALIGN=RIGHT><B>' . $DisplayTotal . '</B></TD></TR></TABLE>';

echo '<TABLE><TR><TD>Comments</TD><TD><TEXTAREA NAME=Comments COLS=40 ROWS=2>' .
	  $_SESSION['SuppTrans']->Comments . '</TEXTAREA></TD></TR></TABLE>';

echo "<P><INPUT TYPE=SUBMIT NAME='PostInvoice' VALUE='" . _('Enter Invoice') . "'>";


if ($_POST['PostInvoice'] == _('Enter Invoice') AND $InputError != 1){

/*First do input reasonableness checks
then do the updates and inserts to process the invoice entered */

	$InputError = False;
	if ( $_SESSION['SuppTrans']->OvGST + $_SESSION['SuppTrans']->OvAmount <= 0){
		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the total amount of the invoice is less than or equal to 0') . '. ' . _('Invoices are expected to have a charge'),'error');

	} elseif (strlen( $_SESSION['SuppTrans']->SuppReference)<1){
		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the there is no suppliers invoice number or reference entered') . '. ' . _('The supplier invoice number must be entered'),'error');

	} elseif (!is_date( $_SESSION['SuppTrans']->TranDate)){
		$InputError = True;
		prnMsg( _('The invoice as entered cannot be processed because the invoice date entered is not in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');

	} elseif (DateDiff(Date($_SESSION['DefaultDateFormat']), $_SESSION['SuppTrans']->TranDate, "d") < 0){
		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the invoice date is after today') . '. ' . _('Purchase invoices are expected to have a date prior to or today'),'error');

	}elseif ( $_SESSION['SuppTrans']->ExRate <= 0){
		$InputError = True;
		prnMsg( _('The invoice as entered cannot be processed because the exchange rate for the invoice has been entered as a negative or zero number') . '. ' . _('The exchange rate is expected to show how many of the suppliers currency there are in 1 of the local currency'),'error');

	}elseif ( $_SESSION['SuppTrans']->OvAmount < round($TotalShiptValue + $TotalGLValue + $TotalGRNValue,2)){
		prnMsg( _('The invoice total as entered is less than the sum of the shipment charges, the general ledger entires (if any) and the charges for goods received') . '. ' . _('There must be a mistake somewhere, the invoice as entered will not be processed'),'error');
		$InputError = True;

	} else {
		$sql = "SELECT count(*) 
			FROM supptrans 
			WHERE supplierno='" . $_SESSION['SuppTrans']->SupplierID . "' 
			AND supptrans.suppreference='" . $_POST['SuppReference'] . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sql to check for the previous entry of the same invoice failed');
		$DbgMsg = _('The following SQL to start an SQL transaction was used');

		$result=DB_query($sql, $db, $ErrMsg, $DbgMsg, True);

		$myrow=DB_fetch_row($result);
		if ($myrow[0] == 1){ /*Transaction reference already entered */
			prnMsg( _('The invoice number') . ' : ' . $_POST['SuppReference'] . ' ' . _('has already been entered') . '. ' . _('It cannot be entered again'),'error');
			$InputError = True;
		}
	}

	if ($InputError == False){

	/* SQL to process the postings for purchase invoice */

	/*Start an SQL transaction */

		$SQL = "BEGIN";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The database does not support transactions');
		$DbgMsg = _('The following SQL to start an SQL transaction was used');

		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

		/*Get the next transaction number for internal purposes and the period to post GL transactions in based on the invoice date*/
		$InvoiceNo = GetNextTransNo(20, $db);
		$PeriodNo = GetPeriod( $_SESSION['SuppTrans']->TranDate, $db);
		$SQLInvoiceDate = FormatDateForSQL( $_SESSION['SuppTrans']->TranDate);

		if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){
		/*Loop through the GL Entries and create a debit posting for each of the accounts entered */
			$LocalTotal = 0;

			/*the postings here are a little tricky, the logic goes like this:
			if its a shipment entry then the cost must go against the GRN suspense account defined in the company record
			if its a general ledger amount it goes straight to the account specified

			if its a GRN amount invoiced then there are two possibilities:

			1 The PO line is on a shipment.
			The whole charge goes to the GRN suspense account pending the closure of the
			shipment where the variance is calculated on the shipment as a whole and the clearing entry to the GRN suspense
			is created. Also, shipment records are created for the charges in local currency.

			2. The order line item is not on a shipment
			The cost as originally credited to GRN suspense on arrival of goods is debited to GRN suspense. Any difference
			between the std cost and the currency cost charged as converted at the ex rate of of the invoice is written off
			to the purchase price variance account applicable to the stock item being invoiced. Or if its not a stock item
			but a nominal item then the GL account in the orignal order is used for the price variance account.
			*/

			foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

			/*GL Items are straight forward - just do the debit postings to the GL accounts specified -
			the credit is to creditors control act  done later for the total invoice value + tax*/

				$SQL = 'INSERT INTO gltrans (type, 
								typeno, 
								trandate, 
								periodno, 
								account, 
								narrative, 
								amount, 
								jobref) 
						VALUES (20, ' .
							$InvoiceNo . ", 
							'" . $SQLInvoiceDate . "', 
							" . $PeriodNo . ', 
							' . $EnteredGLCode->GLCode . ", 
							'" . $_SESSION['SuppTrans']->SupplierID . ' ' . $EnteredGLCode->Narrative . "', 
							" . round($EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate,2) . ", 
							'" . $EnteredGLCode->JobRef . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
				$DbgMsg = _('The following SQL to insert the GL transaction was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$LocalTotal += round($EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate,2);
			}

			foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg){

			/*shipment postings are also straight forward - just do the debit postings to the GRN suspense account
			these entries are reversed from the GRN suspense when the shipment is closed*/

				$SQL = 'INSERT INTO gltrans (type, 
								typeno, 
								trandate, 
								periodno, 
								account, 
								narrative, 
								amount) 
							VALUES (20, ' .
						 		$InvoiceNo . ", 
								'" . $SQLInvoiceDate . "', 
								" . $PeriodNo . ', 
								' . $_SESSION['SuppTrans']->GRNAct . ", 
								'" . $_SESSION['SuppTrans']->SupplierID . ' ' . _('Shipment charge against') . ' ' . $ShiptChg->ShiptRef . "', 
								" . $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate . ')';

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the shipment') .
							' ' . $ShiptChg->ShiptRef . ' ' . _('could not be added because');

				$DbgMsg = _('The following SQL to insert the GL transaction was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$LocalTotal += $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate;

			}

			foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

				if (strlen($EnteredGRN->ShiptRef) == 0 OR $EnteredGRN->ShiptRef == 0){ /*so its not a shipment item */
				/* enter the GL entry to reverse the GRN suspense entry created on delivery at standard cost used on delivery */

					if ($EnteredGRN->StdCostUnit * $EnteredGRN->This_QuantityInv != 0) {
						$SQL = 'INSERT INTO gltrans (type, 
										typeno, 
										trandate, 
										periodno, 
										account, 
										narrative, 
										amount) 
								VALUES (20, ' . $InvoiceNo . ", 
									'" . $SQLInvoiceDate . "', 
									" . $PeriodNo . ', 
									' . $_SESSION['SuppTrans']->GRNAct . ", 
									'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' @  ' .
								 _('std cost of') . ' ' . $EnteredGRN->StdCostUnit  . "', 
								 	" . $EnteredGRN->StdCostUnit * $EnteredGRN->This_QuantityInv . ')';

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');

						$DbgMsg = _('The following SQL to insert the GL transaction was used');

						$Result = DB_query($SQL, $db, $ErrMsg, $Dbg, True);

					}
					$PurchPriceVar = round($EnteredGRN->This_QuantityInv * (($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit),2);

					/*Yes but where to post this difference to - if its a stock item the variance account must be retreived from the stock category record
					if its a nominal purchase order item with no stock item then there will be no standard cost and it will all be variance so post it to the
					account specified in the purchase order detail record */

					if ($PurchPriceVar !=0){ /* don't bother with this lot if there is no difference ! */
						if (strlen($EnteredGRN->ItemCode)>0 OR $EnteredGRN->ItemCode != ''){ /*so it is a stock item */

							/*need to get the stock category record for this stock item - this is function in SQL_CommonFunctions.inc */
							$StockGLCode = GetStockGLCode($EnteredGRN->ItemCode,$db);

							$SQL = 'INSERT INTO gltrans (type, 
											typeno, 
											trandate, 
											periodno, 
											account, 
											narrative, 
											amount) 
									VALUES (20, ' .
									 $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ', ' . $StockGLCode['purchpricevaract'] .
									 ", '" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo .
									 ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' x  ' . _('price var of') . ' ' .
									 number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,2)  .
									 "', " . $PurchPriceVar . ')';

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
							$DbgMsg = _('The following SQL to insert the GL transaction was used');

							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

						} else {

						/* its a nominal purchase order item that is not on a shipment so post the whole lot to the GLCode specified in the order, the purchase price var is actually the diff between the
						order price and the actual invoice price since the std cost was made equal to the order price in local currency at the time
						the goods were received */

							$SQL = 'INSERT INTO gltrans (type, 
											typeno, 
											trandate, 
											periodno, 
											account, 
											narrative, 
											amount) 
									VALUES (20, ' .
									 $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ', ' . $EnteredGRN->GLCode . ", '" .
									 $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' .
									 $EnteredGRN->ItemDescription . ' x ' . $EnteredGRN->This_QuantityInv . ' x  ' . _('price var') .
									 ' ' . number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,2) .
									 "', " . $PurchPriceVar . ')';

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');

							$DbgMsg = _('The following SQL to insert the GL transaction was used');

							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

						}
					}

				} else {
					/*then its a purchase order item on a shipment - whole charge amount to GRN suspense pending closure of the shipment when the variance is calculated and the GRN act cleared up for the shipment */

					$SQL = 'INSERT INTO gltrans (type, 
									typeno, 
									trandate, 
									periodno, 
									account, 
									narrative, 
									amount) 
							VALUES (20, ' .
							 $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ', ' . $_SESSION['SuppTrans']->GRNAct .
							 ", '" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' .
							 $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' @ ' .
							 $_SESSION['SuppTrans']->CurrCode . $EnteredGRN->ChgPrice . ' @ ' . _('a rate of') . ' ' .
							 $_SESSION['SuppTrans']->ExRate . "', " .
							 round(($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) / $_SESSION['SuppTrans']->ExRate,2) . ')';

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');

					$DbgMsg = _('The following SQL to insert the GL transaction was used');

					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				}
				$LocalTotal += round(($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) / $_SESSION['SuppTrans']->ExRate,2);
			} /* end of GRN postings */

			if ($debug == 1 AND ( abs($_SESSION['SuppTrans']->OvAmount/ $_SESSION['SuppTrans']->ExRate) - $LocalTotal) >0.009999){

				echo '<P>' . _('The total posted to the debit accounts is') . ' ' .
						$LocalTotal . ' ' . _('but the sum of OvAmount converted at ExRate') . ' = ' .
						( $_SESSION['SuppTrans']->OvAmount / $_SESSION['SuppTrans']->ExRate);
			}

			if ($_SESSION['SuppTrans']->OvGST != 0){
				/* Now the TAX account */

				$SQL = 'INSERT INTO gltrans (type, 
								typeno, 
								trandate, 
								periodno, 
								account, 
								narrative, 
								amount) 
						VALUES (20, ' .
						 $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ', ' . $_SESSION['SuppTrans']->TaxGLCode .
						 ", '" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('Inv') . ' ' .
						 $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode .
						 $_SESSION['SuppTrans']->OvGST  . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate .
						 "', " . round( $_SESSION['SuppTrans']->OvGST/ $_SESSION['SuppTrans']->ExRate,2) . ')';

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the tax could not be added because');

				$DbgMsg = _('The following SQL to insert the GL transaction was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			}
			/* Now the control account */

			$SQL = 'INSERT INTO gltrans (type, 
							typeno, 
							trandate, 
							periodno, 
							account, 
							narrative, 
							amount) 
					VALUES (20, ' .
					 $InvoiceNo . ", '" . $SQLInvoiceDate . "', " . $PeriodNo . ', ' . $_SESSION['SuppTrans']->CreditorsAct .
					 ", '" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('Inv') . ' ' .
					 $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode .
					 number_format( $_SESSION['SuppTrans']->OvAmount + $_SESSION['SuppTrans']->OvGST,2)  .
					 ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate . "', " .
					 -round(($LocalTotal + ( $_SESSION['SuppTrans']->OvGST / $_SESSION['SuppTrans']->ExRate)),2) . ')';

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the control total could not be added because');

			$DbgMsg = _('The following SQL to insert the GL transaction was used');

			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

		} /*Thats the end of the GL postings */

	/*Now insert the invoice into the SuppTrans table*/

		$SQL = 'INSERT INTO supptrans (transno, 
						type, 
						supplierno, 
						suppreference, 
						trandate, 
						duedate, 
						ovamount, 
						ovgst, 
						rate, 
						transtext)
			VALUES ('. $InvoiceNo . ",
				20 , 
				'" . $_SESSION['SuppTrans']->SupplierID . "', 
				'" . $_SESSION['SuppTrans']->SuppReference . "',
				'" . $SQLInvoiceDate . "', 
				'" . FormatDateForSQL($_SESSION['SuppTrans']->DueDate) . "', 
				" . round($_SESSION['SuppTrans']->OvAmount,2) . ', 
				' . round($_SESSION['SuppTrans']->OvGST,2) . ', 
				' .  $_SESSION['SuppTrans']->ExRate . ", 
				'" . $_SESSION['SuppTrans']->Comments . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');

		$DbgMsg = _('The following SQL to insert the supplier invoice was used');

		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

		/* Now update the GRN and PurchOrderDetails records for amounts invoiced */

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

			$SQL = 'UPDATE purchorderdetails SET qtyinvoiced = qtyinvoiced + ' . $EnteredGRN->This_QuantityInv .',
								actprice = ' . $EnteredGRN->ChgPrice . ' 
						WHERE podetailitem = ' . $EnteredGRN->PODetailItem;

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity invoiced of the purchase order line could not be updated because');

			$DbgMsg = _('The following SQL to update the purchase order details was used');

			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);


			$SQL = 'UPDATE grns SET quantityinv = quantityinv + ' . $EnteredGRN->This_QuantityInv .
					 ' WHERE grnno = ' . $EnteredGRN->GRNNo;

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity invoiced off the goods received record could not be updated because');

			$DbgMsg = _('The following SQL to update the GRN quantity invoiced was used');

			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);


			if (strlen($EnteredGRN->ShiptRef)>0 AND $EnteredGRN->ShiptRef != '0'){

				/* insert the shipment charge records */

				$SQL = 'INSERT INTO shipmentcharges (shiptref, 
									transtype, 
									transno, 
									stockid, 
									value) 
						VALUES (' . $EnteredGRN->ShiptRef . ', 
							20, 
							' . $InvoiceNo . ", 
							'" . $EnteredGRN->ItemCode . "', 
							" . ($EnteredGRN->This_QuantityInv * $EnteredGRN->ChgPrice) / $_SESSION['SuppTrans']->ExRate . ')';

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment charge record for the shipment') .
							 ' ' . $EnteredGRN->ShiptRef . ' ' . _('could not be added because');

				$DbgMsg = _('The following SQL to insert the Shipment charge record was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			}

		} /* end of the loop to do the updates for the quantity of order items the supplier has invoiced */

		/*Add shipment charges records as necessary */

		foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg){

			$SQL = 'INSERT INTO shipmentcharges (shiptref, 
								transtype, 
								transno, 
								value) 
					VALUES (' . $ShiptChg->ShiptRef . ', 
						20, 
						' . $InvoiceNo . ', 
						' . $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate . ')';

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment charge record for the shipment') . 
						 ' ' . $ShiptChg->ShiptRef . ' ' . _('could not be added because');
			
			$DbgMsg = _('The following SQL to insert the Shipment charge record was used');

			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

		}


		$SQL="COMMIT";
		
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The SQL COMMIT failed because');
		
		$DbgMsg = _('The SQL COMMIT failed');
		
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

		unset( $_SESSION['SuppTrans']->GRNs);
		unset( $_SESSION['SuppTrans']->Shipts);
		unset( $_SESSION['SuppTrans']->GLCodes);
		unset( $_SESSION['SuppTrans']);

		prnMsg(_('Supplier invoice number') . ' ' . $InvoiceNo . ' ' . _('has been processed'),'success');
		echo "<P><A HREF='$rootpath/SelectSupplier.php'>" . _('Enter Another invoice') . '</A>';
	}

} /*end of process invoice */

echo '</FORM>';
include('includes/footer.inc');
?>

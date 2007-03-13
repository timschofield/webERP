<?php

/* $Revision: 1.28 $ */

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing 
Also an array of GLCodes objects - only used if the AP - GL link is effective
Also an array of shipment charges for charges to shipments to be apportioned accross the cost of stock items */

$PageSecurity = 5;

include('includes/DefineSuppTransClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Enter Supplier Invoice');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo "<A HREF='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Back to Suppliers') . '</A><BR>';

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
	  $_SESSION['SuppTrans'] = new SuppTrans;

/*Now retrieve supplier information - name, currency, default ex rate, terms, tax rate etc */

	 $sql = "SELECT suppliers.suppname,
	 		paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
	 		suppliers.currcode,
			currencies.rate AS exrate,
			suppliers.taxgroupid,
			taxgroups.taxgroupdescription
	 	FROM suppliers,
			taxgroups,
			currencies,
			paymentterms,
			taxauthorities
	 	WHERE suppliers.taxgroupid=taxgroups.taxgroupid
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
	$_SESSION['SuppTrans']->TaxGroup = $myrow['taxgroupid'];
	$_SESSION['SuppTrans']->TaxGroupDescription = $myrow['taxgroupdescription'];
	
	if ($myrow['daysbeforedue'] == 0){
		 $_SESSION['SuppTrans']->Terms = '1' . $myrow['dayinfollowingmonth'];
	} else {
		 $_SESSION['SuppTrans']->Terms = '0' . $myrow['daysbeforedue'];
	}
	$_SESSION['SuppTrans']->SupplierID = $_GET['SupplierID'];
	
	$LocalTaxProvinceResult = DB_query("SELECT taxprovinceid 
						FROM locations 
						WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'", $db);
						
	if(DB_num_rows($LocalTaxProvinceResult)==0){
		prnMsg(_('The tax province associated with your user account has not been set up in this database. Tax calculations are based on the tax group of the supplier and the tax province of the user entering the invoice. The system administrator should redefine your account with a valid default stocking location and this location should refer to a valid tax provincce'),'error');
		include('includes/footer.inc');
		exit;
	}
	
	$LocalTaxProvinceRow = DB_fetch_row($LocalTaxProvinceResult);
	$_SESSION['SuppTrans']->LocalTaxProvince = $LocalTaxProvinceRow[0];
	
	$_SESSION['SuppTrans']->GetTaxes();
	
	
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

	if (substr( $_SESSION['SuppTrans']->Terms,0,1)=='1') { /*Its a day in the following month when due */
		$_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')+1, substr( $_SESSION['SuppTrans']->Terms,1),Date('y')));
	} else { /*Use the Days Before Due to add to the invoice date */
		$_SESSION['SuppTrans']->DueDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d') + (int) substr( $_SESSION['SuppTrans']->Terms,1),Date('y')));
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
}


if (!isset($_POST['PostInvoice'])){
	
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
		echo '<P>' . _('You should automatically be forwarded to the entry of invoices against shipments page') .
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
		<TD><FONT COLOR=blue><B>' . $_SESSION['SuppTrans']->TaxGroupDescription . '</B></FONT></TD>
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
	
	echo "<INPUT TYPE=SUBMIT NAME='Shipts' VALUE='" . _('Enter Against Shipment') . "'> ";
	
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
				number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . '</TD>
				</TR>';
	
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
			<TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>' .  number_format($TotalShiptValue,2) . '</U></FONT></TD></TR></TABLE>';
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
		
		echo '<TABLE><TR><TD>' . _('Amount in supplier currency') . ':</TD><TD COLSPAN=2 ALIGN=RIGHT>' .
			number_format( $_SESSION['SuppTrans']->OvAmount,2) . '</TD></TR>';
	} else {
		echo '<TABLE><TR><TD>' . _('Amount in supplier currency') .
			':</TD><TD COLSPAN=2 ALIGN=RIGHT><INPUT TYPE=TEXT SIZE=12 MAXLENGTH=10 NAME=OvAmount VALUE=' .
			number_format( $_SESSION['SuppTrans']->OvAmount,2) . '></TD></TR>';
	}
	
	echo "<TR><TD COLSPAN=2><INPUT TYPE=Submit NAME='ToggleTaxMethod' VALUE='" . _('Change Tax Calculation Method') .
		"'></TD><TD><SELECT NAME='OverRideTax'>";
	
	if ($_POST['OverRideTax']=='Man'){
		echo "<OPTION VALUE='Auto'>" . _('Automatic') . "<OPTION SELECTED VALUE='Man'>" . _('Manual');
	} else {
		echo "<OPTION SELECTED VALUE='Auto'>" . _('Automatic') . "<OPTION VALUE='Man'>" . _('Manual');
	}
	
	echo '</SELECT></TD></TR>';
	$TaxTotal =0; //initialise tax total
		
	foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
		
		echo '<TR><TD>'  . $Tax->TaxAuthDescription . '</TD><TD>';
		
		/*Set the tax rate to what was entered */
		if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])){
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = $_POST['TaxRate'  . $Tax->TaxCalculationOrder]/100;
		}
		
		/*If a tax rate is entered that is not the same as it was previously then recalculate automatically the tax amounts */
		
		if ($_POST['OverRideTax']=='Auto' OR !isset($_POST['OverRideTax'])){
		
			echo  ' <INPUT TYPE=TEXT NAME=TaxRate' . $Tax->TaxCalculationOrder . ' MAXLENGTH=4 SIZE=4 VALUE=' . $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100 . '>%';
			
			/*Now recaluclate the tax depending on the method */
			if ($Tax->TaxOnTax ==1){
				
				$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * ($_SESSION['SuppTrans']->OvAmount + $TaxTotal);
			
			} else { /*Calculate tax without the tax on tax */
				
				$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * $_SESSION['SuppTrans']->OvAmount;
			
			}
			
			
			echo '<INPUT TYPE=HIDDEN NAME="TaxAmount'  . $Tax->TaxCalculationOrder . '"  VALUE=' . round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,2) . '>';
			
			echo '</TD><TD ALIGN=RIGHT>' . number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,2);
			
		} else { /*Tax being entered manually accept the taxamount entered as is*/
			
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_POST['TaxAmount'  . $Tax->TaxCalculationOrder];
			
			echo  ' <INPUT TYPE=HIDDEN NAME=TaxRate' . $Tax->TaxCalculationOrder . ' VALUE=' . $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100 . '>';

			echo '</TD><TD><INPUT TYPE=TEXT SIZE=12 MAXLENGTH=12 NAME="TaxAmount'  . $Tax->TaxCalculationOrder . '"  VALUE=' . round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,2) . '>';
		}
		
		$TaxTotal += $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount;
		echo '</TD></TR>';	
	}
	
	$_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount,2);
	
	$DisplayTotal = number_format(( $_SESSION['SuppTrans']->OvAmount + $TaxTotal), 2);
	
	echo '<TR><TD>' . _('Invoice Total') . ':</TD><TD COLSPAN=2 ALIGN=RIGHT><B>' . $DisplayTotal . '</B></TD></TR></TABLE>';
	
	echo '<TABLE><TR><TD>' . _('Comments') . '</TD><TD><TEXTAREA NAME=Comments COLS=40 ROWS=2>' .
		$_SESSION['SuppTrans']->Comments . '</TEXTAREA></TD></TR></TABLE>';
	
	echo "<P><INPUT TYPE=SUBMIT NAME='PostInvoice' VALUE='" . _('Enter Invoice') . "'>";
	
} else { //do the postings -and dont show the button to process

/*First do input reasonableness checks
then do the updates and inserts to process the invoice entered */

	foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
		
	
		/*Set the tax rate to what was entered */
		if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])){
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = $_POST['TaxRate'  . $Tax->TaxCalculationOrder]/100;
		}
		

		if ($_POST['OverRideTax']=='Auto' OR !isset($_POST['OverRideTax'])){
		
			/*Now recaluclate the tax depending on the method */
			if ($Tax->TaxOnTax ==1){
				
				$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * ($_SESSION['SuppTrans']->OvAmount + $TaxTotal);
			
			} else { /*Calculate tax without the tax on tax */
				
				$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * $_SESSION['SuppTrans']->OvAmount;
			
			}
			

		} else { /*Tax being entered manually accept the taxamount entered as is*/

			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_POST['TaxAmount'  . $Tax->TaxCalculationOrder];

		}
		
	}

/*Need to recalc the taxtotal */

	$TaxTotal=0;
	foreach ($_SESSION['SuppTrans']->Taxes as $Tax){
		$TaxTotal +=  $Tax->TaxOvAmount;
	}

	$InputError = False;
	if ( $TaxTotal + $_SESSION['SuppTrans']->OvAmount <= 0){
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

		$SQL = 'BEGIN';

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
			The cost as originally credited to GRN suspense on arrival of goods is debited to GRN suspense. 
			Depending on the setting of WeightedAverageCosting:
			If the order line item is a stock item and WeightedAverageCosting set to OFF then use standard costing .....
				Any difference
				between the std cost and the currency cost charged as converted at the ex rate of of the invoice is written off
				to the purchase price variance account applicable to the stock item being invoiced. 
			Otherwise 
				Recalculate the new weighted average cost of the stock and update the cost - post the difference to the appropriate stock code
			
			Or if its not a stock item
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
							'" . DB_escape_string($_SESSION['SuppTrans']->SupplierID) . ' ' . DB_escape_string($EnteredGLCode->Narrative) . "', 
							" . round($EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate,2) . ", 
							'" . DB_escape_string($EnteredGLCode->JobRef) . "')";

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
								'" . DB_escape_string($_SESSION['SuppTrans']->SupplierID) . ' ' . _('Shipment charge against') . ' ' . $ShiptChg->ShiptRef . "', 
								" . $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate . ')';

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the shipment') .
							' ' . $ShiptChg->ShiptRef . ' ' . _('could not be added because');

				$DbgMsg = _('The following SQL to insert the GL transaction was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$LocalTotal += $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate;

			}

			foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

				if (strlen($EnteredGRN->ShiptRef) == 0 OR $EnteredGRN->ShiptRef == 0){ 
				/*so its not a shipment item 
				  enter the GL entry to reverse the GRN suspense entry created on delivery at standard cost used on delivery */

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
									'" . DB_escape_string($_SESSION['SuppTrans']->SupplierID) . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' . DB_escape_string($EnteredGRN->ItemCode) . ' x ' . $EnteredGRN->This_QuantityInv . ' @  ' .
								 _('std cost of') . ' ' . $EnteredGRN->StdCostUnit  . "', 
								 	" . $EnteredGRN->StdCostUnit * $EnteredGRN->This_QuantityInv . ')';

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');

						$DbgMsg = _('The following SQL to insert the GL transaction was used');

						$Result = DB_query($SQL, $db, $ErrMsg, $Dbg, True);

					}

					
					$PurchPriceVar = round($EnteredGRN->This_QuantityInv * (($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit),2);
					
					
					/*Yes.... but where to post this difference to - if its a stock item the variance account must be retreived from the stock category record
					if its a nominal purchase order item with no stock item then there will be no standard cost and it will all be variance so post it to the
					account specified in the purchase order detail record */

					if ($PurchPriceVar !=0){ /* don't bother with this lot if there is no difference ! */
						if (strlen($EnteredGRN->ItemCode)>0 OR $EnteredGRN->ItemCode != ''){ /*so it is a stock item */

							/*need to get the stock category record for this stock item - this is function in SQL_CommonFunctions.inc */
							$StockGLCode = GetStockGLCode($EnteredGRN->ItemCode,$db);

							/*We have stock item and a purchase price variance need to see whether we are using Standard or WeightedAverageCosting */

							if ($_SESSION['WeightedAverageCosting']==1){ /*Weighted Average costing */

								/*
								First off figure out the new weighted average cost Need the following data:

								How many in stock now
								The quantity being invoiced here - $EnteredGRN->This_QuantityInv
								The cost of these items - $EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate
								*/
								
								$sql ='SELECT SUM(quantity) FROM locstock WHERE stockid="' . $EnteredGRN->ItemCode . '"';
								$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity on hand could not be retrieved from the database');
								$DbgMsg = _('The following SQL to retrieve the total stock quantity was used');
								$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, True);
								$QtyRow = DB_fetch_row($Result);
								$TotalQuantityOnHand = $QtyRow[0];

								
								/*The cost adjustment is the price variance / the total quantity in stock 
								But that's only provided that the total quantity in stock is > the quantity charged on this invoice 
								
								If the quantity on hand is less the amount charged on this invoice then some must have been sold and the price variance on these must be written off to price variances*/
								
								$WriteOffToVariances =0;
								
								if ($EnteredGRN->This_QuantityInv > $TotalQuantityOnHand){

									/*So we need to write off some of the variance to variances and only the balance of the quantity in stock to go to stock value */
	
									$WriteOffToVariances =  ($EnteredGRN->This_QuantityInv
										- $TotalQuantityOnHand)
									* (($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit);

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
									 ' - ' . DB_escape_string($EnteredGRN->ItemCode) . ' x ' . ($EnteredGRN->This_QuantityInv-$TotalQuantityOnHand) . ' x  ' . _('price var of') . ' ' .
									 number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,2)  .
									 "', " . $WriteOffToVariances . ')';

									$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
									$DbgMsg = _('The following SQL to insert the GL transaction was used');


									$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
								}
								/*Now post any remaining price variance to stock rather than price variances */

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
									' . $StockGLCode['stockact'] . ", 
									'" . DB_escape_string($_SESSION['SuppTrans']->SupplierID) . ' - ' . ('Average Cost Adj') .
									 ' - ' . DB_escape_string($EnteredGRN->ItemCode) . ' x ' . $TotalQuantityOnHand  . ' x ' .
									 number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,2)  .
									 "', " . ($PurchPriceVar - $WriteOffToVariances) . ')';

								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
								$DbgMsg = _('The following SQL to insert the GL transaction was used');

								$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

								/*Now to update the stock cost with the new weighted average */
								
								/*Need to consider what to do if the cost has been changed manually between receiving the stock and entering the invoice - this code assumes there has been no cost updates made manually and all the price variance is posted to stock.

								A nicety or important?? */


								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost could not be updated because');
								$DbgMsg = _('The following SQL to update the cost was used');

								if ($TotalQuantityOnHand>0) {

									
									$CostIncrement = ($PurchPriceVar - $WriteOffToVariances) / $TotalQuantityOnHand;

									$sql = 'UPDATE stockmaster SET lastcost=materialcost+overheadcost+labourcost, 
									materialcost=materialcost+' . $CostIncrement . ' WHERE stockid="' . $EnteredGRN->ItemCode . '"';
									$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, True);
								} else {
									$sql = 'UPDATE stockmaster SET lastcost=materialcost+overheadcost+labourcost,
									materialcost=' . ($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) . ' WHERE stockid="' . $EnteredGRN->ItemCode . '"';
									$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, True);
								}
								/* End of Weighted Average Costing Code */

							} else { //It must be Standard Costing 

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
									 ' - ' . DB_escape_string($EnteredGRN->ItemCode) . ' x ' . $EnteredGRN->This_QuantityInv . ' x  ' . _('price var of') . ' ' .
									 number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,2)  .
									 "', " . $PurchPriceVar . ')';

								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
								$DbgMsg = _('The following SQL to insert the GL transaction was used');

								$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
							}
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
									VALUES (20, 
											' . $InvoiceNo . ", 
											'" . $SQLInvoiceDate . "', 
											" . $PeriodNo . ', 
											' . $EnteredGRN->GLCode . ", 
											 '" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' .
									 $EnteredGRN->ItemDescription . ' x ' . $EnteredGRN->This_QuantityInv . ' x  ' . _('price var') .
									 ' ' . number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,2) . "', 
									 " . $PurchPriceVar . ')';

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
							 $_SESSION['SuppTrans']->CurrCode . ' ' . $EnteredGRN->ChgPrice . ' @ ' . _('a rate of') . ' ' .
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

			foreach ($_SESSION['SuppTrans']->Taxes as $Tax){
				/* Now the TAX account */
                                if ($Tax->TaxOvAmount <>0){
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
							' . $Tax->TaxGLCode . ",
						 	'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('Inv') . ' ' .
						 $_SESSION['SuppTrans']->SuppReference . ' ' . $Tax->TaxAuthDescription . ' ' . number_format($Tax->TaxRate*100,2) . '% ' . $_SESSION['SuppTrans']->CurrCode .
						 $Tax->TaxOvAmount  . ' @ ' . _('exch rate') . ' ' . $_SESSION['SuppTrans']->ExRate .
						 "',
						 	" . round( $Tax->TaxOvAmount/ $_SESSION['SuppTrans']->ExRate,2) . ')';

				        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the tax could not be added because');

				        $DbgMsg = _('The following SQL to insert the GL transaction was used');

				        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                                }

			} /*end of loop to post the tax */
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
					 number_format( $_SESSION['SuppTrans']->OvAmount + $TaxTotal,2)  .
					 ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate . "', " .
					 -round(($LocalTotal + ( $TaxTotal / $_SESSION['SuppTrans']->ExRate)),2) . ')';

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
				' . round($TaxTotal,2) . ', 
				' .  $_SESSION['SuppTrans']->ExRate . ", 
				'" . DB_escape_string($_SESSION['SuppTrans']->Comments) . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');

		$DbgMsg = _('The following SQL to insert the supplier invoice was used');

		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		
		$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');
		
		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		foreach ($_SESSION['SuppTrans']->Taxes AS $TaxTotals) {
	
			$SQL = 'INSERT INTO supptranstaxes (supptransid,
							taxauthid,
							taxamount)
				VALUES (' . $SuppTransID . ',
					' . $TaxTotals->TaxAuthID . ',
					' . $TaxTotals->TaxOvAmount . ')';
		
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier transaction taxes records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the supplier transaction taxes record was used:');
 			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		
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
							'" . DB_escape_string($EnteredGRN->ItemCode) . "', 
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
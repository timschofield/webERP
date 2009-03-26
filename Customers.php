<?php

/* $Revision: 1.35 $ */

$PageSecurity = 3;

include('includes/session.inc');

$title = _('Customer Maintenance');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['DebtorNo'] = strtoupper($_POST['DebtorNo']);

	$sql="SELECT COUNT(debtorno) FROM debtorsmaster WHERE debtorno='".$_POST['DebtorNo']."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]>0 and isset($_POST['New'])) {
		$InputError = 1;
		prnMsg( _('The customer number already exists in the database'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif (strlen($_POST['CustName']) > 40 OR strlen($_POST['CustName'])==0) {
		$InputError = 1;
		prnMsg( _('The customer name must be entered and be forty characters or less long'),'error');
		$Errors[$i] = 'CustName';
		$i++;
	} elseif ($_SESSION['AutoDebtorNo']==0 AND strlen($_POST['DebtorNo']) ==0) {
		$InputError = 1;
		prnMsg( _('The debtor code cannot be empty'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif ($_SESSION['AutoDebtorNo']==0 AND ContainsIllegalCharacters($_POST['DebtorNo'])) {
		$InputError = 1;
		prnMsg( _('The customer code cannot contain any of the following characters') . " . - ' & + \" " . _('or a space'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
//	} elseif (ContainsIllegalCharacters($_POST['Address1']) OR ContainsIllegalCharacters($_POST['Address2'])) {
//		$InputError = 1;
//		prnMsg( _('Lines of the address  must not contain illegal characters'),'error');
	} elseif (strlen($_POST['Address1']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address1';
		$i++;
	} elseif (strlen($_POST['Address2']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 2 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address2';
		$i++;
	} elseif (strlen($_POST['Address3']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 3 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address3';
		$i++;
	} elseif (strlen($_POST['Address4']) >50) {
		$InputError = 1;
		prnMsg( _('The Line 4 of the address must be fifty characters or less long'),'error');
		$Errors[$i] = 'Address4';
		$i++;
	} elseif (strlen($_POST['Address5']) >20) {
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address5';
		$i++;
	} elseif (strlen($_POST['Address6']) >15) {
		$InputError = 1;
		prnMsg( _('The Line 6 of the address must be fifteen characters or less long'),'error');
		$Errors[$i] = 'Address6';
		$i++;
	} elseif (!is_numeric($_POST['CreditLimit'])) {
		$InputError = 1;
		prnMsg( _('The credit limit must be numeric'),'error');
		$Errors[$i] = 'CreditLimit';
		$i++;
	} elseif (!is_numeric($_POST['PymtDiscount'])) {
		$InputError = 1;
		prnMsg( _('The payment discount must be numeric'),'error');
		$Errors[$i] = 'PymtDiscount';
		$i++;
	} elseif (!is_date($_POST['ClientSince'])) {
		$InputError = 1;
		prnMsg( _('The customer since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$Errors[$i] = 'ClientSince';
		$i++;
	} elseif (!is_numeric($_POST['Discount'])) {
		$InputError = 1;
		prnMsg( _('The discount percentage must be numeric'),'error');
		$Errors[$i] = 'Discount';
		$i++;
	} elseif ((double) $_POST['CreditLimit'] <0) {
		$InputError = 1;
		prnMsg( _('The credit limit must be a positive number'),'error');
		$Errors[$i] = 'CreditLimit';
		$i++;
	} elseif (((double) $_POST['PymtDiscount']> 10) OR ((double) $_POST['PymtDiscount'] <0)) {
		$InputError = 1;
		prnMsg( _('The payment discount is expected to be less than 10% and greater than or equal to 0'),'error');
		$Errors[$i] = 'PymtDiscount';
		$i++;
	} elseif (((double) $_POST['Discount']> 100) OR ((double) $_POST['Discount'] <0)) {
		$InputError = 1;
		prnMsg( _('The discount is expected to be less than 100% and greater than or equal to 0'),'error');
		$Errors[$i] = 'Discount';
		$i++;
	}

	if ($InputError !=1){

		$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

		if (!isset($_POST['New'])) {

			$sql = "SELECT count(id)
					  FROM debtortrans
					where debtorno = '" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_array($result);

			if ($myrow[0] == 0) {
			  $sql = "UPDATE debtorsmaster SET
					name='" . $_POST['CustName'] . "',
					address1='" . $_POST['Address1'] . "',
					address2='" . $_POST['Address2'] . "',
					address3='" . $_POST['Address3'] ."',
					address4='" . $_POST['Address4'] . "',
					address5='" . $_POST['Address5'] . "',
					address6='" . $_POST['Address6'] . "',
					currcode='" . $_POST['CurrCode'] . "',
					clientsince='$SQL_ClientSince',
					holdreason='" . $_POST['HoldReason'] . "',
					paymentterms='" . $_POST['PaymentTerms'] . "',
					discount=" . ($_POST['Discount'])/100 . ",
					discountcode='" . $_POST['DiscountCode'] . "',
					pymtdiscount=" . ($_POST['PymtDiscount'])/100 . ",
					creditlimit=" . $_POST['CreditLimit'] . ",
					salestype = '" . $_POST['SalesType'] . "',
					invaddrbranch='" . $_POST['AddrInvBranch'] . "',
					taxref='" . $_POST['TaxRef'] . "',
					customerpoline='" . $_POST['CustomerPOLine'] . "',
					typeid='" . $_POST['typeid'] . "'
				  WHERE debtorno = '" . $_POST['DebtorNo'] . "'";
			} else {

			  $currsql = "SELECT currcode
					  		FROM debtorsmaster
							where debtorno = '" . $_POST['DebtorNo'] . "'";
			  $currresult = DB_query($currsql,$db);
			  $currrow = DB_fetch_array($currresult);
			  $OldCurrency = $currrow[0];

			  $sql = "UPDATE debtorsmaster SET
					name='" . $_POST['CustName'] . "',
					address1='" . $_POST['Address1'] . "',
					address2='" . $_POST['Address2'] . "',
					address3='" . $_POST['Address3'] ."',
					address4='" . $_POST['Address4'] . "',
					address5='" . $_POST['Address5'] . "',
					address6='" . $_POST['Address6'] . "',
					clientsince='$SQL_ClientSince',
					holdreason='" . $_POST['HoldReason'] . "',
					paymentterms='" . $_POST['PaymentTerms'] . "',
					discount=" . ($_POST['Discount'])/100 . ",
					discountcode='" . $_POST['DiscountCode'] . "',
					pymtdiscount=" . ($_POST['PymtDiscount'])/100 . ",
					creditlimit=" . $_POST['CreditLimit'] . ",
					salestype = '" . $_POST['SalesType'] . "',
					invaddrbranch='" . $_POST['AddrInvBranch'] . "',
					taxref='" . $_POST['TaxRef'] . "',
					customerpoline='" . $_POST['CustomerPOLine'] . "',
					typeid='" . $_POST['typeid'] . "'
				  WHERE debtorno = '" . $_POST['DebtorNo'] . "'";

			  if ($OldCurrency != $_POST['CurrCode']) {
			  	prnMsg( _('The currency code cannot be updated as there are already transactions for this customer'),'info');
			  }
			}

			$ErrMsg = _('The customer could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Customer updated'),'success');

		} else { //it is a new customer
			/* set the DebtorNo if $AutoDebtorNo in config.php has been set to
			something greater 0 */
			if ($_SESSION['AutoDebtorNo'] > 0) {
				/* system assigned, sequential, numeric */
				if ($_SESSION['AutoDebtorNo']== 1) {
					$_POST['DebtorNo'] = GetNextTransNo(500, $db);
				}
			}

			$sql = "INSERT INTO debtorsmaster (
							debtorno,
							name,
							address1,
							address2,
							address3,
							address4,
							address5,
							address6,
							currcode,
							clientsince,
							holdreason,
							paymentterms,
							discount,
							discountcode,
							pymtdiscount,
							creditlimit,
							salestype,
							invaddrbranch,
							taxref,
							customerpoline,
							typeid)
				VALUES ('" . $_POST['DebtorNo'] ."',
					'" . $_POST['CustName'] ."',
					'" . $_POST['Address1'] ."',
					'" . $_POST['Address2'] ."',
					'" . $_POST['Address3'] . "',
					'" . $_POST['Address4'] . "',
					'" . $_POST['Address5'] . "',
					'" . $_POST['Address6'] . "',
					'" . $_POST['CurrCode'] . "',
					'" . $SQL_ClientSince . "',
					" . $_POST['HoldReason'] . ",
					'" . $_POST['PaymentTerms'] . "',
					" . ($_POST['Discount'])/100 . ",
					'" . $_POST['DiscountCode'] . "',
					" . ($_POST['PymtDiscount'])/100 . ",
					" . $_POST['CreditLimit'] . ",
					'" . $_POST['SalesType'] . "',
					'" . $_POST['AddrInvBranch'] . "',
					'" . $_POST['TaxRef'] . "',
					'" . $_POST['CustomerPOLine'] . "',
					'" . $_POST['typeid'] . "'
					)";

			$ErrMsg = _('This customer could not be added because');
			$result = DB_query($sql,$db,$ErrMsg);

			$BranchCode = substr($_POST['DebtorNo'],0,4);

			echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/CustomerBranches.php?" . SID . "&DebtorNo=" . $_POST['DebtorNo'] . "'>";

			echo '<P>' . _('You should automatically be forwarded to the entry of a new Customer Branch page') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			"<A HREF='" . $rootpath . "/CustomerBranches.php?" . SID . "&DebtorNo=" . $_POST['DebtorNo']  . '.<BR>';

			include('includes/footer.inc');
			exit;
		}
	} else {
		prnMsg( _('Validation failed') . '. ' . _('No updates or deletes took place'),'error');
	}

} elseif (isset($_POST['delete'])) {

//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$sql= "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST['DebtorNo'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg( _('This customer cannot be deleted because there are transactions that refer to it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions against this customer');

	} else {
		$sql= "SELECT COUNT(*) FROM salesorders WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete the customer record because orders have been created against it'),'warn');
			echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('orders against this customer');
		} else {
			$sql= "SELECT COUNT(*) FROM salesanalysis WHERE cust='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg( _('Cannot delete this customer record because sales analysis records exist for it'),'warn');
				echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales analysis records against this customer');
			} else {
				$sql= "SELECT COUNT(*) FROM custbranch WHERE debtorno='" . $_POST['DebtorNo'] . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this customer because there are branch records set up against it'),'warn');
					echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('branch records relating to this customer');
				}
			}
		}

	}
	if ($CancelDelete==0) { //ie not cancelled the delete as a result of above tests
		$sql="DELETE FROM custcontacts WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql,$db);
		$sql="DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql,$db);
		prnMsg( _('Customer') . ' ' . $_POST['DebtorNo'] . ' ' . _('has been deleted - together with all the associated contacts') . ' !','success');
		include('includes/footer.inc');
		exit;
	} //end if Delete Customer
}

if(isset($reset)){
	unset($_POST['CustName']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['HoldReason']);
	unset($_POST['PaymentTerms']);
	unset($_POST['Discount']);
	unset($_POST['DiscountCode']);
	unset($_POST['PymtDiscount']);
	unset($_POST['CreditLimit']);
	unset($_POST['SalesType']);
	unset($_POST['DebtorNo']);
	unset($_POST['InvAddrBranch']);
	unset($_POST['TaxRef']);
	unset($_POST['CustomerPOLine']);
	unset($_POST['typeid']);
}

/*DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['DebtorNo'])){
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = $_GET['DebtorNo'];
}
if (isset($_POST['ID'])){
	$ID = $_POST['ID'];
} elseif (isset($_GET['ID'])){
	$ID = $_GET['ID'];
} else {
	$ID='';
}
if (isset($_POST['ws'])){
	$ws = $_POST['ws'];
} elseif (isset($_GET['ws'])){
	$ws = $_GET['ws'];
}
if (isset($_POST['Edit'])){
	$Edit = $_POST['Edit'];
} elseif (isset($_GET['Edit'])){
	$Edit = $_GET['Edit'];
} else {
	$Edit='';
}

if (isset($_POST['Add'])){
	$Add = $_POST['Add'];
} elseif (isset($_GET['Add'])){
	$Add = $_GET['Add'];
}


echo "<A HREF='" . $rootpath . '/SelectCustomer.php?' . SID . "'>" . _('Back to Customers') . '</A><BR>';

if (!isset($DebtorNo)) {

/*If the page was called without $_POST['DebtorNo'] passed to page then assume a new customer is to be entered show a form with a Debtor Code field other wise the form showing the fields with the existing entries against the customer will show for editing with only a hidden DebtorNo field*/

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';

	echo "<input type='Hidden' name='New' value='Yes'>";

	$DataError =0;

	echo '<CENTER><TABLE BORDER=2 CELLSPACING=4><TR><TD><TABLE>';

	/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
	then provide an input box for the DebtorNo to manually assigned */
	if ($_SESSION['AutoDebtorNo']==0)  {
		echo '<TR><TD>' . _('Customer Code') . ":</TD><TD><input tabindex=1 type='Text' name='DebtorNo' SIZE=11 MAXLENGTH=10></TD></TR>";
	}

	echo '<TR><TD>' . _('Customer Name') . ':</TD>
		<TD><input tabindex=2 type="Text" name="CustName" SIZE=42 MAXLENGTH=40></TD></TR>';
	echo '<TR><TD>' . _('Address Line 1 (Street)') . ':</TD>
		<TD><input tabindex=3 type="Text" name="Address1" SIZE=42 MAXLENGTH=40></TD></TR>';
	echo '<TR><TD>' . _('Address Line 2 (Suburb/City)') . ':</TD>
		<TD><input tabindex=4 type="Text" name="Address2" SIZE=42 MAXLENGTH=40></TD></TR>';
	echo '<TR><TD>' . _('Address Line 3 (State/Province)') . ':</TD>
		<TD><input tabindex=5 type="Text" name="Address3" SIZE=42 MAXLENGTH=40></TD></TR>';
	echo '<TR><TD>' . _('Address Line 4 (Postal Code)') . ':</TD>
		<TD><input tabindex=6 type="Text" name="Address4" SIZE=42 MAXLENGTH=40></TD></TR>';
	echo '<TR><TD>' . _('Address Line 5') . ':</TD>
		<TD><input tabindex=7 type="Text" name="Address5" SIZE=22 MAXLENGTH=20></TD></TR>';
	echo '<TR><TD>' . _('Address Line 6') . ':</TD>
		<TD><input tabindex=8 type="Text" name="Address6" SIZE=17 MAXLENGTH=15></TD></TR>';

  echo '</TABLE></TD><TD><TABLE>';

// Show Sales Type drop down list
	$result=DB_query('SELECT typeabbrev, sales_type FROM salestypes ',$db);
	if (DB_num_rows($result)==0){
               $DataError =1;
               echo '<a href="SalesTypes.php?" target="_parent">Setup Types</a>';
               echo '<TR><TD COLSPAN=2>' . prnMsg(_('No sales types/price lists defined'),'error') . '</TD></TR>';
       } else {
               echo '<TR><TD>' . _('Sales Type/Price List') . ":</TD>
                       <TD><SELECT tabindex=9 name='SalesType'>";

               while ($myrow = DB_fetch_array($result)) {
                       echo "<OPTION VALUE='". $myrow['typeabbrev'] . "'>" . $myrow['sales_type'];
               } //end while loop
               DB_data_seek($result,0);
               echo '</SELECT></TD></TR>';
       }

// Show Customer Type drop down list
       $result=DB_query('SELECT typeid, typename FROM debtortype ',$db);
       if (DB_num_rows($result)==0){
               $DataError =1;
               echo '<a href="SalesTypes.php?" target="_parent">Setup Types</a>';
               echo '<TR><TD COLSPAN=2>' . prnMsg(_('No Customer types/price lists defined'),'error') . '</TD></TR>';
	        } else {
                echo '<TR><TD>' . _('Customer Type') . ":</TD>
                        <TD><SELECT tabindex=9 name='typeid'>";

                while ($myrow = DB_fetch_array($result)) {
                        echo "<OPTION VALUE='". $myrow['typeid'] . "'>" . $myrow['typename'];
                } //end while loop
                DB_data_seek($result,0);
                echo '</SELECT></TD></TR>';
        }





	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<TR><TD>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . "):</TD><TD><input tabindex=10 type='Text' name='ClientSince' value=$DateString SIZE=12 MAXLENGTH=10></TD></TR>";
	echo '<TR><TD>' . _('Discount Percent') . ":</TD>
		<TD><input tabindex=11 type='Text' name='Discount' value=0 SIZE=5 MAXLENGTH=4></TD></TR>";
	echo '<TR><TD>' . _('Discount Code') . ":</TD>
		<TD><input tabindex=12 type='Text' name='DiscountCode' SIZE=3 MAXLENGTH=2></TD></TR>";
	echo '<TR><TD>' . _('Payment Discount Percent') . ":</TD>
		<TD><input tabindex=13 type='Text' name='PymtDiscount' value=0 SIZE=5 MAXLENGTH=4></TD></TR>";
	echo '<TR><TD>' . _('Credit Limit') . ":</TD>
		<TD><input tabindex=14 type='Text' name='CreditLimit' value=" . $_SESSION['DefaultCreditLimit'] . " SIZE=16 MAXLENGTH=14></TD></TR>";
	echo '<TR><TD>' . _('Tax Reference') . ":</TD>
		<TD><input tabindex=15 type='Text' name='TaxRef' SIZE=22 MAXLENGTH=20></TD></TR>";

	$result=DB_query('SELECT terms, termsindicator FROM paymentterms',$db);
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<TR><TD COLSPAN=2>' . prnMsg(_('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</TD></TR>';
	} else {

		echo '<TR><TD>' . _('Payment Terms') . ":</TD>
			<TD><SELECT tabindex=15 name='PaymentTerms'>";

		while ($myrow = DB_fetch_array($result)) {
			echo "<OPTION VALUE='". $myrow['termsindicator'] . "'>" . $myrow['terms'];
		} //end while loop
		DB_data_seek($result,0);

		echo '</SELECT></TD></TR>';
	}
	echo '<TR><TD>' . _('Credit Status') . ":</TD><TD><SELECT tabindex=16 name='HoldReason'>";

	$result=DB_query('SELECT reasoncode, reasondescription FROM holdreasons',$db);
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<TR><TD COLSPAN=2>' . prnMsg(_('There are no credit statuses currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</TD></TR>';
	} else {
		while ($myrow = DB_fetch_array($result)) {
			echo "<OPTION VALUE='". $myrow['reasoncode'] . "'>" . $myrow['reasondescription'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</SELECT></TD></TR>';
	}

	$result=DB_query('SELECT currency, currabrev FROM currencies',$db);
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<TR><TD COLSPAN=2>' . prnMsg(_('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</TD></TR>';
	} else {
		if (!isset($_POST['CurrCode'])){
			$CurrResult = DB_query('SELECT currencydefault FROM companies WHERE coycode=1',$db);
			$myrow = DB_fetch_row($CurrResult);
			$_POST['CurrCode'] = $myrow[0];
		}
		echo '<TR><TD>' . _('Customer Currency') . ":</TD><TD><SELECT tabindex=17 name='CurrCode'>";
		while ($myrow = DB_fetch_array($result)) {
			if ($_POST['CurrCode']==$myrow['currabrev']){
				echo '<OPTION SELECTED VALUE='. $myrow['currabrev'] . '>' . $myrow['currency'];
			} else {
				echo '<OPTION VALUE='. $myrow['currabrev'] . '>' . $myrow['currency'];
			}
		} //end while loop
		DB_data_seek($result,0);

		echo '</SELECT></TD></TR>';
	}

	/*added line 8/23/2007 by Morris Kelly to set po line parameter Y/N*/
	echo '<tr><td>' . _('Customer PO Line on SO') . ":</td><td><select tabindex=18 name='CustomerPOLine'>";
		echo '<option selected value=0>' . _('No');
		echo '<option value=1>' . _('Yes');
	echo '</select></td></tr>';

	echo '<TR><TD>' . _('Invoice Addressing') . ":</TD><TD><SELECT tabindex=19 NAME='AddrInvBranch'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Address to HO');
		echo '<OPTION VALUE=1>' . _('Address to Branch');
	echo '</SELECT></TD></TR>';

	echo'</TABLE></TD></TR></TABLE></CENTER>';
	if ($DataError ==0){
		echo "<CENTER><input tabindex=20 type='Submit' name='submit' value='" . _('Add New Customer') . "'><BR><INPUT tabindex=21 TYPE=SUBMIT ACTION=RESET VALUE='" . _('Reset') . "'></CENTER>";
	}
	echo '</FORM>';

} else {

//DebtorNo exists - either passed when calling the form or from the form itself

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID ."'>";
	echo '<CENTER><TABLE BORDER=2 CELLSPACING=4><TR><TD><TABLE>';

	if (!isset($_POST['New'])) {
		$sql = "SELECT debtorno,
				name,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6,
				currcode,
				salestype,
				clientsince,
				holdreason,
				paymentterms,
				discount,
				discountcode,
				pymtdiscount,
				creditlimit,
				invaddrbranch,
				taxref,
				customerpoline,
				typeid
				FROM debtorsmaster
			WHERE debtorno = '" . $DebtorNo . "'";

		$ErrMsg = _('The customer details could not be retrieved because');
		$result = DB_query($sql,$db,$ErrMsg);


		$myrow = DB_fetch_array($result);

		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then display the DebtorNo */
		if ($_SESSION['AutoDebtorNo']== 0 )  {
			echo '<TR><TD>' . _('Customer Code') . ":</TD>
				<TD>" . $DebtorNo . "</TD></TR>";
		}
		$_POST['CustName'] = $myrow['name'];
		$_POST['Address1']  = $myrow['address1'];
		$_POST['Address2']  = $myrow['address2'];
		$_POST['Address3']  = $myrow['address3'];
		$_POST['Address4']  = $myrow['address4'];
		$_POST['Address5']  = $myrow['address5'];
		$_POST['Address6']  = $myrow['address6'];
		$_POST['SalesType'] = $myrow['salestype'];
		$_POST['CurrCode']  = $myrow['currcode'];
		$_POST['ClientSince'] = ConvertSQLDate($myrow['clientsince']);
		$_POST['HoldReason']  = $myrow['holdreason'];
		$_POST['PaymentTerms']  = $myrow['paymentterms'];
		$_POST['Discount']  = $myrow['discount'] * 100; // Sherifoz 21.6.03 convert to displayable percentage
		$_POST['DiscountCode']  = $myrow['discountcode'];
		$_POST['PymtDiscount']  = $myrow['pymtdiscount'] * 100; // Sherifoz 21.6.03 convert to displayable percentage
		$_POST['CreditLimit']	= $myrow['creditlimit'];
		$_POST['InvAddrBranch'] = $myrow['invaddrbranch'];
		$_POST['TaxRef'] = $myrow['taxref'];
		$_POST['CustomerPOLine'] = $myrow['customerpoline'];
		$_POST['typeid'] = $myrow['typeid'];

		echo "<INPUT TYPE=HIDDEN NAME='DebtorNo' VALUE='" . $DebtorNo . "'>";

	} else {
	// its a new customer being added
		echo "<INPUT TYPE=HIDDEN NAME='New' VALUE='Yes'>";

		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then provide an input box for the DebtorNo to manually assigned */
		if ($_SESSION['AutoDebtorNo']== 0 )  {
			echo '<TR><TD>' . _('Customer Code') . ':</TD>
				<TD><input ' . (in_array('DebtorNo',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="DebtorNo" value="' . $DebtorNo . '" SIZE=12 MAXLENGTH=10></TD></TR>';
		}
	}

	echo '<TR><TD>' . _('Customer Name') . ':</TD>
		<TD><input ' . (in_array('CustName',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="CustName" value="' . $_POST['CustName'] . '" SIZE=42 MAXLENGTH=40></TD></TR>';
	echo '<TR><TD>' . _('Address Line 1 (Street)') . ':</TD>
		<TD><input ' . (in_array('Address1',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address1" SIZE=42 MAXLENGTH=40 value="' . $_POST['Address1'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Address Line 2 (Suburb/City)') . ':</TD>
		<TD><input ' . (in_array('Address2',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address2" SIZE=42 MAXLENGTH=40 value="' . $_POST['Address2'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Address Line 3 (State/Province)') . ':</TD>
		<TD><input ' . (in_array('Address3',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address3" SIZE=42 MAXLENGTH=40 value="' . $_POST['Address3'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Address Line 4 (Postal Code)') . ':</TD>
		<TD><input ' . (in_array('Address4',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address4" SIZE=42 MAXLENGTH=40 value="' . $_POST['Address4'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Address Line 5') . ':</TD>
		<TD><input ' . (in_array('Address5',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address5" SIZE=42 MAXLENGTH=40 value="' . $_POST['Address5'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Address Line 6') . ':</TD>
		<TD><input ' . (in_array('Address6',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address6" SIZE=42 MAXLENGTH=40 value="' . $_POST['Address6'] . '"></TD></TR>';
  echo '</TABLE></TD><TD><TABLE>';

// Select sales types for drop down list
	$result=DB_query('SELECT typeabbrev, sales_type FROM salestypes ',$db);
	echo '<TR><TD>' . _('Sales Type') . '/' . _('Price List') . ":</TD>
		<TD><SELECT name='SalesType'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['SalesType']==$myrow['typeabbrev']){
			echo "<OPTION SELECTED VALUE='". $myrow['typeabbrev'] . "'>" . $myrow['sales_type'];
		} else {
			echo "<OPTION VALUE='". $myrow['typeabbrev'] . "'>" . $myrow['sales_type'];
		}
	} //end while loop
	DB_data_seek($result,0);

// Select Customer types for drop down list for SELECT/UPDATE
        $result=DB_query('SELECT typeid, typename FROM debtortype ORDER BY typename',$db);
        echo '<TR><TD>' . _('Customer Type') . ":</TD>
                <TD><SELECT name='typeid'>";
        while ($myrow = DB_fetch_array($result)) {
                if ($_POST['typeid']==$myrow['typeid']){
                        echo "<OPTION SELECTED VALUE='". $myrow['typeid'] . "'>" . $myrow['typename'];
                } else {
                        echo "<OPTION VALUE='". $myrow['typeid'] . "'>" . $myrow['typename'];
                }
        } //end while loop
        DB_data_seek($result,0);

	echo '</SELECT></TD></TR>
		<TR><TD>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</TD>
			<TD><input ' . (in_array('ClientSince',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="ClientSince" SIZE=12 MAXLENGTH=10 value=' . $_POST['ClientSince'] . '></TD></TR>';
	echo '<TR><TD>' . _('Discount Percent') . ':</TD>
		<TD><input type="Text" name="Discount" SIZE=5 MAXLENGTH=4 value=' . $_POST['Discount'] . '></TD></TR>';
	echo '<TR><TD>' . _('Discount Code') . ':</TD>
		<TD><input ' . (in_array('DiscountCode',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="DiscountCode" SIZE=3 MAXLENGTH=2 value="' . $_POST['DiscountCode'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Payment Discount Percent') . ':</TD>
		<TD><input ' . (in_array('PymtDiscount',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="PymtDiscount" SIZE=5 MAXLENGTH=4 value=' . $_POST['PymtDiscount'] . '></TD></TR>';
	echo '<TR><TD>' . _('Credit Limit') . ':</TD>
		<TD><input ' . (in_array('CreditLimit',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="CreditLimit" SIZE=16 MAXLENGTH=14 value=' . $_POST['CreditLimit'] . '></TD></TR>';
	echo '<TR><TD>' . _('Tax Reference') . ':</TD>
		<TD><input type="Text" name="TaxRef" SIZE=22 MAXLENGTH=20  value="' . $_POST['TaxRef'] . '"></TD></TR>';
	$result=DB_query('SELECT terms, termsindicator FROM paymentterms',$db);

	echo '<TR><TD>' . _('Payment Terms') . ":</TD>
		<TD><SELECT name='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['PaymentTerms']==$myrow['termsindicator']){
		echo "<OPTION SELECTED VALUE=". $myrow['termsindicator'] . '>' . $myrow['terms'];
		} else {
		echo '<OPTION VALUE='. $myrow['termsindicator'] . '>' . $myrow['terms'];
		}
	} //end while loop
	DB_data_seek($result,0);

	$result=DB_query('SELECT reasoncode, reasondescription FROM holdreasons',$db);

	echo '</SELECT></TD></TR><TR><TD>' . _('Credit Status') . ":</TD>
		<TD><SELECT name='HoldReason'>";
	while ($myrow = DB_fetch_array($result)) {

		if ($_POST['HoldReason']==$myrow['reasoncode']){
			echo '<OPTION SELECTED VALUE='. $myrow['reasoncode'] . '>' . $myrow['reasondescription'];
		} else {
			echo '<OPTION VALUE='. $myrow['reasoncode'] . '>' . $myrow['reasondescription'];
		}

	} //end while loop
	DB_data_seek($result,0);

	$result=DB_query('SELECT currency, currabrev FROM currencies',$db);

	echo '</select></td></tr>
		<tr><td>' . _('Customers Currency') . ":</td>
		<td><select name='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode']==$myrow['currabrev']){
			echo '<option selected value='. $myrow['currabrev'] . '>' . $myrow['currency'];
		} else {
			echo '<option value='. $myrow['currabrev'] . '>' . $myrow['currency'];
		}
	} //end while loop
	DB_data_seek($result,0);
	echo '</select></td></tr>';

	/*added lines 8/23/2007 by Morris Kelly to get po line parameter Y/N*/
	echo '<tr><td>' . _('Require Customer PO Line on SO') . ":</TD>
		<TD><SELECT NAME='CustomerPOLine'>";
	if ($_POST['CustomerPOLine']==0){
		echo '<option selected value=0>' . _('No');
		echo '<option value=1>' . _('Yes');
	} else {
		echo '<option value=0>' . _('No');
		echo '<option selected value=1>' . _('Yes');
	}
	echo '</select></td></tr>';

	echo '<TR><TD>' . _('Invoice Addressing') . ":</TD>
		<TD><SELECT NAME='AddrInvBranch'>";
	if ($_POST['InvAddrBranch']==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Address to HO');
		echo '<OPTION VALUE=1>' . _('Address to Branch');
	} else {
		echo '<OPTION VALUE=0>' . _('Address to HO');
		echo '<OPTION SELECTED VALUE=1>' . _('Address to Branch');
	}

	echo '</SELECT></TD></TR></TABLE></TD></TR>';
	echo '<TR><TD colspan=2>';

  	$sql = 'SELECT * FROM custcontacts where debtorno="'.$DebtorNo.'" ORDER BY contid';
	$result = DB_query($sql,$db);

	echo '<CENTER><table border=1>';
	echo '<tr>
			<th>' . _('Name') . '</th>
			<th>' . _('Role') . '</th>
			<th>' . _('Phone Number') . '</th>
			<th>' . _('Notes') . '</th>
			<th>' . _('Edit') . '</th>
			<th colspan=2><INPUT TYPE="Submit" NAME="addcontact" VALUE="Add Contact"></th></tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k=1;
		}

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="AddCustomerContacts.php?Id=%s&DebtorNo=%s">'. _('Edit'). '</a></td>
				<td><a href="%sID=%s&DebtorNo=%s&delete=1">'. _('Delete'). '</a></td>
				</tr>',
				$myrow[2],
				$myrow[3],
				$myrow[4],
				$myrow[5],
				$myrow[0],
				$myrow[1],
				$_SERVER['PHP_SELF'] . "?" . SID,
				$myrow[0],
				$myrow[1]);

	}//END WHILE LIST LOOP
	echo '</CENTER></table>';
		//	echo "<CENTER><INPUT TYPE='Submit' NAME='addcontact' VALUE='" . _('ADD Contact') . "'>";
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?'.SID.'&DebtorNo="'.$DebtorNo.'"&ID='.$ID.'&Edit'.$Edit.'>';
	if (isset($Edit) and $Edit!='') {
		$SQLcustcontacts='SELECT * from custcontacts
							WHERE debtorno="'.$DebtorNo.'"
							and contid='.$ID;
		$resultcc = DB_query($SQLcustcontacts,$db);
		$myrowcc = DB_fetch_array($resultcc);
		$_POST['custname']=$myrowcc['contactname'];
		$_POST['role']=$myrowcc['role'];
		$_POST['phoneno']=$myrowcc['phoneno'];
		$_POST['notes']=$myrowcc['notes'];
		echo '<CENTER><table border=1>';
		echo "<tr>
				<td>" . _('Name') . "</td><TD><INPUT TYPE=TEXT name='custname' value='".$_POST['custname']."'></TD></tr><tr>
				<td>" . _('Role') . "</td><TD><INPUT TYPE=TEXT name='role' value='".$_POST['role']."'></TD></tr><tr>
				<td>" . _('Phone no') . "</td><TD><INPUT TYPE=TEXT name='phoneno' value='".$_POST['phoneno']."'></TD></tr><tr>
				<td>" . _('Notes') . "</td><TD><textarea name='notes'>".$_POST['notes']."</textarea></TD></tr>
				<tr><td colspan=2 align=center><input type=submit name=update value=update></td></tr></table>
				";

		echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?'.SID.'&DebtorNo="'.$DebtorNo.'"&ID"'.$ID.'">';


	}
	if (isset($_POST['update'])) {

			$SQLupdatecc='UPDATE custcontacts
							SET contactname="'.$_POST['custname'].'",
							role="'.$_POST['role'].'",
							phoneno="'.$_POST['phoneno'].'",
							notes="'.DB_escape_string($_POST['notes']).'"
							Where debtorno="'.$DebtorNo.'"
							and contid="'.$Edit.'"';
			$resultupcc = DB_query($SQLupdatecc,$db);
			echo '<br>'.$SQLupdatecc;
			echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL="' . $_SERVER['PHP_SELF'] . '?'.SID.'&DebtorNo='.$DebtorNo.'&ID='.$ID.'">';
		}
	if (isset($_GET['delete'])) {
		$SQl='DELETE FROM custcontacts where debtorno="'.$DebtorNo.'"
				and contid="'.$ID.'"';
		$resultupcc = DB_query($SQl,$db);

		echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=' . $_SERVER['PHP_SELF'] . '?'.SID.'&DebtorNo='.$DebtorNo.'">';
		echo '<br>'.$SQl;
		prnmsg('Contact Deleted','success');
	}


	echo'</TD></TR></TABLE></CENTER>';

	if (isset($_POST['New']) and $_POST['New']) {
		echo "<CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Add New Customer') . "'><BR><INPUT TYPE=SUBMIT name='reset' VALUE='" . _('Reset') . "'></FORM>";
	} else {
		echo "<HR><CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Update Customer') . "'>";
		echo '<P><INPUT TYPE="Submit" NAME="delete" VALUE="' . _('Delete Customer') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">';
	}
	if(isset($_POST['addcontact']) AND (isset($_POST['addcontact'])!=''))
	{
		echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=' . $rootpath . '/AddCustomerContacts.php?' . SID . '&DebtorNo=' .$DebtorNo.'">';
	}

} // end of main ifs

include('includes/footer.inc');
?>

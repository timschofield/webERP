<?php

/* $Revision: 1.42 $ */

$PageSecurity = 3;

include('includes/session.inc');

$title = _('Customer Maintenance');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Customer') . '" alt="">' . ' ' . _('Customer Maintenance') . '';

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
			echo '<br>';

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

			echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath ."/CustomerBranches.php?" . SID . "&DebtorNo=" . $_POST['DebtorNo'] . "'>";

			echo '<div class="centre">' . _('You should automatically be forwarded to the entry of a new Customer Branch page') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			"<a href='" . $rootpath . "/CustomerBranches.php?" . SID . "&DebtorNo=" . $_POST['DebtorNo']  . '.</div>';

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
// Leave Sales Type set so as to faciltate fast customer setup
//	unset($_POST['SalesType']);
	unset($_POST['DebtorNo']);
	unset($_POST['InvAddrBranch']);
	unset($_POST['TaxRef']);
	unset($_POST['CustomerPOLine']);
// Leave Type ID set so as to faciltate fast customer setup
//	unset($_POST['typeid']);
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

// This link is already on menu bar
//echo "<a href='" . $rootpath . '/SelectCustomer.php?' . SID . "'>" . _('Back to Customers') . '</a><br>';

if (!isset($DebtorNo)) {

/*If the page was called without $_POST['DebtorNo'] passed to page then assume a new customer is to be entered show a form with a Debtor Code field other wise the form showing the fields with the existing entries against the customer will show for editing with only a hidden DebtorNo field*/

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';

	echo "<input type='Hidden' name='New' value='Yes'>";

	$DataError =0;

	echo '<table border=2 cellspacing=4><tr><td><table>';

	/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
	then provide an input box for the DebtorNo to manually assigned */
	if ($_SESSION['AutoDebtorNo']==0)  {
		echo '<tr><td>' . _('Customer Code') . ":</td><td><input tabindex=1 type='Text' name='DebtorNo' size=11 maxlength=10></td></tr>";
	}

	echo '<tr><td>' . _('Customer Name') . ':</td>
		<td><input tabindex=2 type="Text" name="CustName" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 1 (Street)') . ':</td>
		<td><input tabindex=3 type="Text" name="Address1" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 2 (Suburb/City)') . ':</td>
		<td><input tabindex=4 type="Text" name="Address2" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 3 (State/Province)') . ':</td>
		<td><input tabindex=5 type="Text" name="Address3" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 4 (Postal Code)') . ':</td>
		<td><input tabindex=6 type="Text" name="Address4" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 5') . ':</td>
		<td><input tabindex=7 type="Text" name="Address5" size=22 maxlength=20></td></tr>';
	echo '<tr><td>' . _('Address Line 6') . ':</td>
		<td><input tabindex=8 type="Text" name="Address6" size=17 maxlength=15></td></tr>';

  echo '</table></td><td><table>';

// Show Sales Type drop down list
	$result=DB_query('SELECT typeabbrev, sales_type FROM salestypes ',$db);
	if (DB_num_rows($result)==0){
               $DataError =1;
               echo '<a href="SalesTypes.php?" target="_parent">Setup Types</a>';
               echo '<tr><td colspan=2>' . prnMsg(_('No sales types/price lists defined'),'error') . '</td></tr>';
       } else {
               echo '<tr><td>' . _('Sales Type/Price List') . ':</td>
                       <td><select tabindex=9 name="SalesType">';

               while ($myrow = DB_fetch_array($result)) {
                       echo '<option value="'. $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
               } //end while loop
               DB_data_seek($result,0);
			   echo '</select></td></tr>';
       }

// Show Customer Type drop down list
       $result=DB_query('SELECT typeid, typename FROM debtortype ',$db);
       if (DB_num_rows($result)==0){
               $DataError =1;
               echo '<a href="SalesTypes.php?" target="_parent">Setup Types</a>';
               echo '<tr><td colspan=2>' . prnMsg(_('No Customer types/price lists defined'),'error') . '</td></tr>';
	        } else {
                echo '<tr><td>' . _('Customer Type') . ':</td>
                        <td><select tabindex=9 name="typeid">';

                while ($myrow = DB_fetch_array($result)) {
                        echo '<option value="'. $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
                } //end while loop
                DB_data_seek($result,0);
                echo '</select></td></tr>';
        }





	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<tr><td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td><td><input tabindex=10 type="text" class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="ClientSince" value="' . $DateString . '" size=12 maxlength=10></td></tr>';
	echo '<tr><td>' . _('Discount Percent') . ':</td>
		<td><input tabindex=11 type="textbox" class="number" name="Discount" value=0 size=5 maxlength=4></td></tr>';
	echo '<tr><td>' . _('Discount Code') . ':</td>
		<td><input tabindex=12 type="text" name="DiscountCode" size=3 maxlength=2></td></tr>';
	echo '<tr><td>' . _('Payment Discount Percent') . ':</td>
		<td><input tabindex=13 type="textbox" class ="number" name="PymtDiscount" value=0 size=5 maxlength=4></td></tr>';
	echo '<tr><td>' . _('Credit Limit') . ':</td>
		<td><input tabindex=14 type="text" class="number" name="CreditLimit" value=' . $_SESSION['DefaultCreditLimit'] . ' size=16 maxlength=14></td></tr>';
	echo '<tr><td>' . _('Tax Reference') . ":</td>
		<td><input tabindex=15 type='Text' name='TaxRef' size=22 maxlength=20></td></tr>";

	$result=DB_query('SELECT terms, termsindicator FROM paymentterms',$db);
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr><td colspan=2>' . prnMsg(_('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td></tr>';
	} else {

		echo '<tr><td>' . _('Payment Terms') . ':</td>
			<td><select tabindex=15 name="PaymentTerms">';

		while ($myrow = DB_fetch_array($result)) {
			echo '<option value="'. $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
		} //end while loop
		DB_data_seek($result,0);

		echo '</select></td></tr>';
	}
	echo '<tr><td>' . _('Credit Status') . ':</td><td><select tabindex=16 name="HoldReason">';

	$result=DB_query('SELECT reasoncode, reasondescription FROM holdreasons',$db);
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr><td colspan=2>' . prnMsg(_('There are no credit statuses currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td></tr>';
	} else {
		while ($myrow = DB_fetch_array($result)) {
			echo '<option value="'. $myrow['reasoncode'] . '">' . $myrow['reasondescription'] . '</option>';
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';
	}

	$result=DB_query('SELECT currency, currabrev FROM currencies',$db);
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr><td colspan=2>' . prnMsg(_('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td></tr>';
	} else {
		if (!isset($_POST['CurrCode'])){
			$CurrResult = DB_query('SELECT currencydefault FROM companies WHERE coycode=1',$db);
			$myrow = DB_fetch_row($CurrResult);
			$_POST['CurrCode'] = $myrow[0];
		}
		echo '<tr><td>' . _('Customer Currency') . ':</td><td><select tabindex=17 name="CurrCode">';
		while ($myrow = DB_fetch_array($result)) {
			if ($_POST['CurrCode']==$myrow['currabrev']){
				echo '<option selected value='. $myrow['currabrev'] . '>' . $myrow['currency'] . '</option>';
			} else {
				echo '<option value='. $myrow['currabrev'] . '>' . $myrow['currency'] . '</option>';
			}
		} //end while loop
		DB_data_seek($result,0);

		echo '</select></td></tr>';
	}

	/*added line 8/23/2007 by Morris Kelly to set po line parameter Y/N*/
	echo '<tr><td>' . _('Customer PO Line on SO') . ":</td><td><select tabindex=18 name='CustomerPOLine'>";
		echo '<option selected value=0>' . _('No');
		echo '<option value=1>' . _('Yes');
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Invoice Addressing') . ":</td><td><select tabindex=19 name='AddrInvBranch'>";
		echo '<option selected VALUE=0>' . _('Address to HO');
		echo '<option VALUE=1>' . _('Address to Branch');
	echo '</select></td></tr>';

	echo'</table></td></tr></table>';
	if ($DataError ==0){
		echo "<div class='centre'><input tabindex=20 type='Submit' name='submit' value='" . _('Add New Customer') . "'><br><input tabindex=21 type=submit action=RESET VALUE='" . _('Reset') . "'></div>";
	}
	echo '</form>';

} else {

//DebtorNo exists - either passed when calling the form or from the form itself

	echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID ."'>";
	echo '<table border=2 cellspacing=4><tr><td><table>';

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
			echo '<tr><td>' . _('Customer Code') . ":</td>
				<td>" . $DebtorNo . "</td></tr>";
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

		echo '<input type=hidden name="DebtorNo" value="' . $DebtorNo . '">';

	} else {
	// its a new customer being added
		echo '<input type=hidden name="New" value="Yes">';

		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then provide an input box for the DebtorNo to manually assigned */
		if ($_SESSION['AutoDebtorNo']== 0 )  {
			echo '<tr><td>' . _('Customer Code') . ':</td>
				<td><input ' . (in_array('DebtorNo',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="DebtorNo" value="' . $DebtorNo . '" size=12 maxlength=10></td></tr>';
		}
	}

	echo '<tr><td>' . _('Customer Name') . ':</td>
		<td><input ' . (in_array('CustName',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="CustName" value="' . $_POST['CustName'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 1 (Street)') . ':</td>
		<td><input ' . (in_array('Address1',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address1" size=42 maxlength=40 value="' . $_POST['Address1'] . '"></td></tr>';
	echo '<tr><td>' . _('Address Line 2 (Suburb/City)') . ':</td>
		<td><input ' . (in_array('Address2',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address2" size=42 maxlength=40 value="' . $_POST['Address2'] . '"></td></tr>';
	echo '<tr><td>' . _('Address Line 3 (State/Province)') . ':</td>
		<td><input ' . (in_array('Address3',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address3" size=42 maxlength=40 value="' . $_POST['Address3'] . '"></td></tr>';
	echo '<tr><td>' . _('Address Line 4 (Postal Code)') . ':</td>
		<td><input ' . (in_array('Address4',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address4" size=42 maxlength=40 value="' . $_POST['Address4'] . '"></td></tr>';
	echo '<tr><td>' . _('Address Line 5') . ':</td>
		<td><input ' . (in_array('Address5',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address5" size=42 maxlength=40 value="' . $_POST['Address5'] . '"></td></tr>';
	echo '<tr><td>' . _('Address Line 6') . ':</td>
		<td><input ' . (in_array('Address6',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="Address6" size=42 maxlength=40 value="' . $_POST['Address6'] . '"></td></tr>';
  echo '</table></td><td><table>';

// Select sales types for drop down list
	$result=DB_query('SELECT typeabbrev, sales_type FROM salestypes ',$db);
	echo '<tr><td>' . _('Sales Type') . '/' . _('Price List') . ":</td>
		<td><select name='SalesType'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['SalesType']==$myrow['typeabbrev']){
			echo "<option selected value='". $myrow['typeabbrev'] . "'>" . $myrow['sales_type'];
		} else {
			echo "<option value='". $myrow['typeabbrev'] . "'>" . $myrow['sales_type'];
		}
	} //end while loop
	DB_data_seek($result,0);

// Select Customer types for drop down list for SELECT/UPDATE
        $result=DB_query('SELECT typeid, typename FROM debtortype ORDER BY typename',$db);
        echo '<tr><td>' . _('Customer Type') . ":</td>
                <td><select name='typeid'>";
        while ($myrow = DB_fetch_array($result)) {
                if ($_POST['typeid']==$myrow['typeid']){
                        echo "<option selected VALUE='". $myrow['typeid'] . "'>" . $myrow['typename'];
                } else {
                        echo "<option VALUE='". $myrow['typeid'] . "'>" . $myrow['typename'];
                }
        } //end while loop
        DB_data_seek($result,0);

	echo '</select></td></tr>
		<tr><td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
			<td><input ' . (in_array('ClientSince',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="ClientSince" size=12 maxlength=10 value=' . $_POST['ClientSince'] . '></td></tr>';
	echo '<tr><td>' . _('Discount Percent') . ':</td>
		<td><input type="Text" name="Discount" size=5 maxlength=4 value=' . $_POST['Discount'] . '></td></tr>';
	echo '<tr><td>' . _('Discount Code') . ':</td>
		<td><input ' . (in_array('DiscountCode',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="DiscountCode" size=3 maxlength=2 value="' . $_POST['DiscountCode'] . '"></td></tr>';
	echo '<tr><td>' . _('Payment Discount Percent') . ':</td>
		<td><input ' . (in_array('PymtDiscount',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="PymtDiscount" size=5 maxlength=4 value=' . $_POST['PymtDiscount'] . '></td></tr>';
	echo '<tr><td>' . _('Credit Limit') . ':</td>
		<td><input ' . (in_array('CreditLimit',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="CreditLimit" size=16 maxlength=14 value=' . $_POST['CreditLimit'] . '></td></tr>';
	echo '<tr><td>' . _('Tax Reference') . ':</td>
		<td><input type="Text" name="TaxRef" size=22 maxlength=20  value="' . $_POST['TaxRef'] . '"></td></tr>';
	$result=DB_query('SELECT terms, termsindicator FROM paymentterms',$db);

	echo '<tr><td>' . _('Payment Terms') . ":</td>
		<td><select name='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['PaymentTerms']==$myrow['termsindicator']){
		echo "<option selected VALUE=". $myrow['termsindicator'] . '>' . $myrow['terms'];
		} else {
		echo '<option VALUE='. $myrow['termsindicator'] . '>' . $myrow['terms'];
		}
	} //end while loop
	DB_data_seek($result,0);

	$result=DB_query('SELECT reasoncode, reasondescription FROM holdreasons',$db);

	echo '</select></td></tr><tr><td>' . _('Credit Status') . ":</td>
		<td><select name='HoldReason'>";
	while ($myrow = DB_fetch_array($result)) {

		if ($_POST['HoldReason']==$myrow['reasoncode']){
			echo '<option selected VALUE='. $myrow['reasoncode'] . '>' . $myrow['reasondescription'];
		} else {
			echo '<option VALUE='. $myrow['reasoncode'] . '>' . $myrow['reasondescription'];
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
	echo '<tr><td>' . _('Require Customer PO Line on SO') . ":</td>
		<td><select name='CustomerPOLine'>";
	if ($_POST['CustomerPOLine']==0){
		echo '<option selected value=0>' . _('No');
		echo '<option value=1>' . _('Yes');
	} else {
		echo '<option value=0>' . _('No');
		echo '<option selected value=1>' . _('Yes');
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Invoice Addressing') . ":</td>
		<td><select name='AddrInvBranch'>";
	if ($_POST['InvAddrBranch']==0){
		echo '<option selected VALUE=0>' . _('Address to HO');
		echo '<option VALUE=1>' . _('Address to Branch');
	} else {
		echo '<option VALUE=0>' . _('Address to HO');
		echo '<option selected VALUE=1>' . _('Address to Branch');
	}

	echo '</select></td></tr></table></td></tr>';
	echo '<tr><td colspan=2>';

  	$sql = 'SELECT * FROM custcontacts where debtorno="'.$DebtorNo.'" ORDER BY contid';
	$result = DB_query($sql,$db);

	echo '<table border=1>';
	echo '<tr>
			<th>' . _('Name') . '</th>
			<th>' . _('Role') . '</th>
			<th>' . _('Phone Number') . '</th>
			<th>' . _('Notes') . '</th>
			<th>' . _('Edit') . '</th>
			<th colspan=2><input type="Submit" name="addcontact" VALUE="Add Contact"></th></tr>';

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
	echo '</table>';
		//	echo "<input type='Submit' name='addcontact' VALUE='" . _('ADD Contact') . "'>";
	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?'.SID.'&DebtorNo="'.$DebtorNo.'"&ID='.$ID.'&Edit'.$Edit.'>';
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
		echo '<table border=1>';
		echo "<tr>
				<td>" . _('Name') . "</td><td><input type=text name='custname' value='".$_POST['custname']."'></td></tr><tr>
				<td>" . _('Role') . "</td><td><input type=text name='role' value='".$_POST['role']."'></td></tr><tr>
				<td>" . _('Phone no') . "</td><td><input type='text' name='phoneno' value='".$_POST['phoneno']."'></td></tr><tr>
				<td>" . _('Notes') . "</td><td><textarea name='notes'>".$_POST['notes']."</textarea></td></tr>
				<tr><td colspan=2><input type=submit name=update value=update></td></tr></table>
				";

		echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?'.SID.'&DebtorNo="'.$DebtorNo.'"&ID"'.$ID.'">';


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
			echo '<meta http-equiv="Refresh" content="0; url="' . $_SERVER['PHP_SELF'] . '?'.SID.'&DebtorNo='.$DebtorNo.'&ID='.$ID.'">';
		}
	if (isset($_GET['delete'])) {
		$SQl='DELETE FROM custcontacts where debtorno="'.$DebtorNo.'"
				and contid="'.$ID.'"';
		$resultupcc = DB_query($SQl,$db);

		echo '<meta http-equiv="Refresh" content="0; url=' . $_SERVER['PHP_SELF'] . '?'.SID.'&DebtorNo='.$DebtorNo.'">';
		echo '<br>'.$SQl;
		prnmsg('Contact Deleted','success');
	}


	echo'</td></tr></table>';

	if (isset($_POST['New']) and $_POST['New']) {
		echo "<div class='centre'><input type='Submit' name='submit' VALUE='" . _('Add New Customer') . "'><br><input type=submit name='reset' VALUE='" . _('Reset') . "'></form>";
	} else {
		echo "<hr><div class='centre'><input type='Submit' name='submit' VALUE='" . _('Update Customer') . "'>";
		echo '<p><input type="Submit" name="delete" VALUE="' . _('Delete Customer') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">';
	}
	if(isset($_POST['addcontact']) AND (isset($_POST['addcontact'])!=''))
	{
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/AddCustomerContacts.php?' . SID . '&DebtorNo=' .$DebtorNo.'">';
	}
	echo '</div>';
} // end of main ifs

include('includes/footer.inc');
?>

<?php


include('includes/session.php');
include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.

if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
	$ViewTopic = 'AccountsReceivable';
	$BookMark = 'AmendCustomer';
} else {
	$ViewTopic = 'AccountsReceivable';
	$BookMark = 'NewCustomer';
}

$Title = _('Customer Maintenance');
/* webERP manual links before header.php */
$ViewTopic= 'AccountsReceivable';
$BookMark = 'NewCustomer';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') .
	'" alt="" />' . ' ' . _('Customer Maintenance') . '
	</p>';

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

	$_POST['DebtorNo'] = mb_strtoupper($_POST['DebtorNo']);

	$sql="SELECT COUNT(debtorno) FROM debtorsmaster WHERE debtorno='".$_POST['DebtorNo']."'";
	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]>0 AND isset($_POST['New'])) {
		$InputError = 1;
		prnMsg( _('The customer number already exists in the database'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	}elseif (mb_strlen($_POST['CustName']) > 40 OR mb_strlen($_POST['CustName'])==0) {
		$InputError = 1;
		prnMsg( _('The customer name must be entered and be forty characters or less long'),'error');
		$Errors[$i] = 'CustName';
		$i++;
	} elseif ($_SESSION['AutoDebtorNo']==0 AND mb_strlen($_POST['DebtorNo']) ==0) {
		$InputError = 1;
		prnMsg( _('The debtor code cannot be empty'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif ($_SESSION['AutoDebtorNo']==0 AND (ContainsIllegalCharacters($_POST['DebtorNo']) OR mb_strpos($_POST['DebtorNo'], ' '))) {
		$InputError = 1;
		prnMsg( _('The customer code cannot contain any of the following characters') . " . - ' &amp; + \" " . _('or a space'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif (mb_strlen($_POST['Address1']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address1';
		$i++;
	} elseif (mb_strlen($_POST['Address2']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 2 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address2';
		$i++;
	} elseif (mb_strlen($_POST['Address3']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 3 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address3';
		$i++;
	} elseif (mb_strlen($_POST['Address4']) >50) {
		$InputError = 1;
		prnMsg( _('The Line 4 of the address must be fifty characters or less long'),'error');
		$Errors[$i] = 'Address4';
		$i++;
	} elseif (mb_strlen($_POST['Address5']) >20) {
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address5';
		$i++;
	} elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
		$InputError = 1;
		prnMsg( _('The credit limit must be numeric'),'error');
		$Errors[$i] = 'CreditLimit';
		$i++;
	} elseif (!is_numeric(filter_number_format($_POST['PymtDiscount']))) {
		$InputError = 1;
		prnMsg( _('The payment discount must be numeric'),'error');
		$Errors[$i] = 'PymtDiscount';
		$i++;
	} elseif (!Is_Date($_POST['ClientSince'])) {
		$InputError = 1;
		prnMsg( _('The customer since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$Errors[$i] = 'ClientSince';
		$i++;
	} elseif (!is_numeric(filter_number_format($_POST['Discount']))) {
		$InputError = 1;
		prnMsg( _('The discount percentage must be numeric'),'error');
		$Errors[$i] = 'Discount';
		$i++;
	} elseif (filter_number_format($_POST['CreditLimit']) <0) {
		$InputError = 1;
		prnMsg( _('The credit limit must be a positive number'),'error');
		$Errors[$i] = 'CreditLimit';
		$i++;
	} elseif ((filter_number_format($_POST['PymtDiscount'])> 10) OR (filter_number_format($_POST['PymtDiscount']) <0)) {
		$InputError = 1;
		prnMsg( _('The payment discount is expected to be less than 10% and greater than or equal to 0'),'error');
		$Errors[$i] = 'PymtDiscount';
		$i++;
	} elseif ((filter_number_format($_POST['Discount'])> 100) OR (filter_number_format($_POST['Discount']) <0)) {
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
			$result = DB_query($sql);
			$myrow = DB_fetch_array($result);

			if ($myrow[0] == 0) {
			  $sql = "UPDATE debtorsmaster SET	name='" . $_POST['CustName'] . "',
												address1='" . $_POST['Address1'] . "',
												address2='" . $_POST['Address2'] . "',
												address3='" . $_POST['Address3'] ."',
												address4='" . $_POST['Address4'] . "',
												address5='" . $_POST['Address5'] . "',
												address6='" . $_POST['Address6'] . "',
												currcode='" . $_POST['CurrCode'] . "',
												clientsince='" . $SQL_ClientSince. "',
												holdreason='" . $_POST['HoldReason'] . "',
												paymentterms='" . $_POST['PaymentTerms'] . "',
												discount='" . filter_number_format($_POST['Discount'])/100 . "',
												discountcode='" . $_POST['DiscountCode'] . "',
												pymtdiscount='" . filter_number_format($_POST['PymtDiscount'])/100 . "',
												creditlimit='" . filter_number_format($_POST['CreditLimit']) . "',
												salestype = '" . $_POST['SalesType'] . "',
												invaddrbranch='" . $_POST['AddrInvBranch'] . "',
												taxref='" . $_POST['TaxRef'] . "',
												customerpoline='" . $_POST['CustomerPOLine'] . "',
												typeid='" . $_POST['typeid'] . "',
												language_id='" . $_POST['LanguageID'] . "'
					  WHERE debtorno = '" . $_POST['DebtorNo'] . "'";
			} else {

			  $CurrSQL = "SELECT currcode
					  		FROM debtorsmaster
							where debtorno = '" . $_POST['DebtorNo'] . "'";
			  $CurrResult = DB_query($CurrSQL);
			  $CurrRow = DB_fetch_array($CurrResult);
			  $OldCurrency = $CurrRow[0];

			  $sql = "UPDATE debtorsmaster SET	name='" . $_POST['CustName'] . "',
												address1='" . $_POST['Address1'] . "',
												address2='" . $_POST['Address2'] . "',
												address3='" . $_POST['Address3'] ."',
												address4='" . $_POST['Address4'] . "',
												address5='" . $_POST['Address5'] . "',
												address6='" . $_POST['Address6'] . "',
												clientsince='" . $SQL_ClientSince . "',
												holdreason='" . $_POST['HoldReason'] . "',
												paymentterms='" . $_POST['PaymentTerms'] . "',
												discount='" . filter_number_format($_POST['Discount'])/100 . "',
												discountcode='" . $_POST['DiscountCode'] . "',
												pymtdiscount='" . filter_number_format($_POST['PymtDiscount'])/100 . "',
												creditlimit='" . filter_number_format($_POST['CreditLimit']) . "',
												salestype = '" . $_POST['SalesType'] . "',
												invaddrbranch='" . $_POST['AddrInvBranch'] . "',
												taxref='" . $_POST['TaxRef'] . "',
												customerpoline='" . $_POST['CustomerPOLine'] . "',
												typeid='" . $_POST['typeid'] . "',
												language_id='" . $_POST['LanguageID'] . "'
						WHERE debtorno = '" . $_POST['DebtorNo'] . "'";

			  if ($OldCurrency != $_POST['CurrCode']) {
			  	prnMsg( _('The currency code cannot be updated as there are already transactions for this customer'),'info');
			  }
			}

			$ErrMsg = _('The customer could not be updated because');
			$result = DB_query($sql,$ErrMsg);
			prnMsg( _('Customer updated'),'success');
			echo '<br />';

		} else { //it is a new customer
			/* set the DebtorNo if $AutoDebtorNo in config.php has been set to
			something greater 0 */
			if ($_SESSION['AutoDebtorNo'] > 0) {
				/* system assigned, sequential, numeric */
				if ($_SESSION['AutoDebtorNo']== 1) {
					$_POST['DebtorNo'] = GetNextTransNo(500);
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
							typeid,
							language_id)
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
						'" . $_POST['HoldReason'] . "',
						'" . $_POST['PaymentTerms'] . "',
						'" . filter_number_format($_POST['Discount'])/100 . "',
						'" . $_POST['DiscountCode'] . "',
						'" . filter_number_format($_POST['PymtDiscount'])/100 . "',
						'" . filter_number_format($_POST['CreditLimit']) . "',
						'" . $_POST['SalesType'] . "',
						'" . $_POST['AddrInvBranch'] . "',
						'" . $_POST['TaxRef'] . "',
						'" . $_POST['CustomerPOLine'] . "',
						'" . $_POST['typeid'] . "',
						'" . $_POST['LanguageID'] . "')";

			$ErrMsg = _('This customer could not be added because');
			$result = DB_query($sql,$ErrMsg);

			$BranchCode = mb_substr($_POST['DebtorNo'],0,4);

			echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath .'/CustomerBranches.php?DebtorNo=' . $_POST['DebtorNo'] . '">';

			echo '<div class="centre">' . _('You should automatically be forwarded to the entry of a new Customer Branch page') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			'<a href="' . $RootPath . '/CustomerBranches.php?DebtorNo=' . $_POST['DebtorNo']  . '"></a></div>';

			include('includes/footer.php');
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
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg( _('This customer cannot be deleted because there are transactions that refer to it'),'warn');
		echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions against this customer');

	} else {
		$sql= "SELECT COUNT(*) FROM salesorders WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete the customer record because orders have been created against it'),'warn');
			echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('orders against this customer');
		} else {
			$sql= "SELECT COUNT(*) FROM salesanalysis WHERE cust='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg( _('Cannot delete this customer record because sales analysis records exist for it'),'warn');
				echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales analysis records against this customer');
			} else {

				// Check if there are any users that refer to this CUSTOMER code
				$SQL= "SELECT COUNT(*) FROM www_users WHERE www_users.customerid = '" . $_POST['DebtorNo'] . "'";

				$result = DB_query($SQL);
				$myrow = DB_fetch_row($result);

				if ($myrow[0]>0) {
					prnMsg(_('Cannot delete this customer because users exist that refer to it') . '. ' . _('Purge old users first'),'warn');
					echo '<br />' . _('There are') . ' ' . $myrow[0] . ' '._('users referring to this Branch/customer');
				} else {
						// Check if there are any contract that refer to this branch code
					$SQL = "SELECT COUNT(*) FROM contracts WHERE contracts.debtorno = '" . $_POST['DebtorNo'] . "'";

					$result = DB_query($SQL);
					$myrow = DB_fetch_row($result);

					if ($myrow[0]>0) {
						prnMsg(_('Cannot delete this customer because contracts have been created that refer to it') . '. ' . _('Purge old contracts first'),'warn');
						echo '<br />' . _('There are') . ' ' . $myrow[0] . ' '._('contracts referring to this customer');
					}
				}
			}
		}

	}
	if ($CancelDelete==0) { //ie not cancelled the delete as a result of above tests
		$SQL="DELETE FROM custbranch WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($SQL,$ErrMsg);
		$sql="DELETE FROM custcontacts WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql);
		$sql="DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql);
		prnMsg( _('Customer') . ' ' . $_POST['DebtorNo'] . ' ' . _('has been deleted - together with all the associated branches and contacts'),'success');
		include('includes/footer.php');
		unset($_SESSION['CustomerID']);
		exit;
	} //end if Delete Customer
}

if(isset($_POST['Reset'])){
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
	unset($_POST['LanguageID']);
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

if(isset($_POST['AddContact']) AND (isset($_POST['AddContact'])!='')){
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/AddCustomerContacts.php?DebtorNo=' .$DebtorNo.'">';
}

if (!isset($DebtorNo)) {

/*If the page was called without $_POST['DebtorNo'] passed to page then assume a new customer is to be entered show a form with a Debtor Code field other wise the form showing the fields with the existing entries against the customer will show for editing with only a hidden DebtorNo field*/

/* First check that all the necessary items have been setup */

	$SetupErrors=0; //Count errors
	$sql="SELECT COUNT(typeabbrev)
				FROM salestypes";
	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]==0) {
		prnMsg( _('In order to create a new customer you must first set up at least one sales type/price list') . '<br />' .
			_('Click').' ' . '<a target="_blank" href="' . $RootPath . '/SalesTypes.php">' . _('here').' ' . '</a>' . _('to set up your price lists'),'warning') . '<br />';
		$SetupErrors += 1;
	}
	$sql="SELECT COUNT(typeid)
				FROM debtortype";
	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]==0) {
		prnMsg( _('In order to create a new customer you must first set up at least one customer type') . '<br />' .
			_('Click').' ' . '<a target="_blank" href="' . $RootPath . '/CustomerTypes.php">' . _('here').' ' . '</a>' . _('to set up your customer types'),'warning');
		$SetupErrors += 1;
	}

	if ($SetupErrors>0) {
		echo '<br /><div class="centre"><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'" >' . _('Click here to continue') . '</a></div>';
		include('includes/footer.php');
		exit;
	}
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="New" value="Yes" />';

	$DataError =0;

	echo '<table class="selection" cellspacing="4">
			<tr>
				<td valign="top">
					<table class="selection">';

	/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
	then provide an input box for the DebtorNo to manually assigned */
	if ($_SESSION['AutoDebtorNo']==0)  {
		echo '<tr>
				<td>' . _('Customer Code') . ':</td>
				<td><input type="text" data-type="no-illegal-chars" tabindex="1"  name="DebtorNo"  required="required" autofocus="autofocus" title ="'._('Up to 10 characters for the customer code. The following characters are prohibited:') . ' \' &quot; + . &amp; \\ &gt; &lt;" placeholder="'._('alpha-numeric').'" size="11" maxlength="10" /></td></tr>';
	}

	echo '<tr>
			<td>' . _('Customer Name') . ':</td>
			<td><input tabindex="2" type="text" name="CustName" required="required" size="42" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 1 (Street)') . ':</td>
			<td><input tabindex="3" type="text" name="Address1" required="required" size="42" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 2 (Street)') . ':</td>
			<td><input tabindex="4" type="text" name="Address2" size="42" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 3 (Suburb/City)') . ':</td>
			<td><input tabindex="5" type="text" name="Address3" size="42" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 4 (State/Province)') . ':</td>
			<td><input tabindex="6" type="text" name="Address4" size="42" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Address Line 5 (Postal Code)') . ':</td>
			<td><input tabindex="7" type="text" name="Address5" size="22" maxlength="20" /></td>
		</tr>';

	if (!isset($_POST['Address6'])) {
		 $_POST['Address6'] = $CountriesArray[$_SESSION['CountryOfOperation']];
	}
	echo '<tr>
			<td>' . _('Country') . ':</td>
			<td><select name="Address6">';
	foreach ($CountriesArray as $CountryEntry => $CountryName){
		if (isset($_POST['Address6']) AND (strtoupper($_POST['Address6']) == strtoupper($CountryName))){
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
		} else {
			echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

// Show Sales Type drop down list
	$result=DB_query("SELECT typeabbrev, sales_type FROM salestypes ORDER BY sales_type");
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr>
				<td colspan="2">' . prnMsg(_('No sales types/price lists defined'),'error') . '<br /><a href="SalesTypes.php?" target="_parent">' . _('Setup Types') . '</a></td>
			</tr>';
	} else {
        echo '<tr>
				<td>' . _('Sales Type') . '/' . _('Price List') . ':</td>
			   <td><select tabindex="9" name="SalesType" required="required">';

		while ($myrow = DB_fetch_array($result)) {
		   echo '<option value="'. $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
		} //end while loopre
		DB_data_seek($result,0);
        echo '</select></td>
			</tr>';
	}

// Show Customer Type drop down list
	$result=DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	if (DB_num_rows($result)==0){
	   $DataError =1;
	   echo '<a href="SalesTypes.php?" target="_parent">' . _('Setup Types') . '</a>';
	   echo '<tr>
				<td colspan="2">' . prnMsg(_('No Customer types/price lists defined'),'error') . '</td>
			</tr>';
	} else {
		echo '<tr>
				<td>' . _('Customer Type') . ':</td>
				<td><select tabindex="9" name="typeid" required="required">';

		while ($myrow = DB_fetch_array($result)) {
			echo '<option value="'. $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>
			</tr>';
	}

	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<tr>
			<td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
			<td><input tabindex="10" type="text" class="date" name="ClientSince" value="' . $DateString . '" size="11" maxlength="10" /></td>
		</tr>';

	echo '</table></td>
			<td><table class="selection">
				<tr>
					<td>' . _('Discount Percent') . ':</td>
					<td><input tabindex="11" type="text" class="number" name="Discount" value="0" size="5" maxlength="4" /></td>
				</tr>
				<tr>
					<td>' . _('Discount Code') . ':</td>
					<td><input tabindex="12" type="text" name="DiscountCode" size="3" maxlength="2" /></td>
				</tr>
				<tr>
					<td>' . _('Payment Discount Percent') . ':</td>
					<td><input tabindex="13" type="text" class ="number" name="PymtDiscount" value="0" size="5" maxlength="4" /></td>
				</tr>
				<tr>
					<td>' . _('Credit Limit') . ':</td>
					<td><input tabindex="14" type="text" class="integer" name="CreditLimit" required="required" value="' . locale_number_format($_SESSION['DefaultCreditLimit'],0) . '" size="16" maxlength="14" /></td>
				</tr>
				<tr>
					<td>' . _('Tax Reference') . ':</td>
					<td><input tabindex="15" type="text" name="TaxRef" size="22" maxlength="20" /></td>
				</tr>';

	$result=DB_query("SELECT terms, termsindicator FROM paymentterms");
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr><td colspan="2">' . prnMsg(_('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td></tr>';
	} else {

		echo '<tr>
				<td>' . _('Payment Terms') . ':</td>
				<td><select tabindex="15" name="PaymentTerms" required="required">';

		while ($myrow = DB_fetch_array($result)) {
			echo '<option value="'. $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
		} //end while loop
		DB_data_seek($result,0);

		echo '</select></td></tr>';
	}
	echo '<tr>
			<td>' . _('Credit Status') . ':</td>
			<td><select tabindex="16" name="HoldReason" required="required">';

	$result=DB_query("SELECT reasoncode, reasondescription FROM holdreasons");
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr>
				<td colspan="2">' . prnMsg(_('There are no credit statuses currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td>
			</tr>';
	} else {
		while ($myrow = DB_fetch_array($result)) {
			echo '<option value="'. $myrow['reasoncode'] . '">' . $myrow['reasondescription'] . '</option>';
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';
	}

	$result=DB_query("SELECT currency, currabrev FROM currencies");
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr>
				<td colspan="2">' . prnMsg(_('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td>
			</tr>';
	} else {
		if (!isset($_POST['CurrCode'])){
			$CurrResult = DB_query("SELECT currencydefault FROM companies WHERE coycode=1");
			$myrow = DB_fetch_row($CurrResult);
			$_POST['CurrCode'] = $myrow[0];
		}
		echo '<tr>
				<td>' . _('Customer Currency') . ':</td>
				<td><select tabindex="17" name="CurrCode" required="required">';
		while ($myrow = DB_fetch_array($result)) {
			if ($_POST['CurrCode']==$myrow['currabrev']){
				echo '<option selected="selected" value="'. $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
			} else {
				echo '<option value="'. $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
			}
		} //end while loop
		DB_data_seek($result,0);

		echo '</select></td>
			</tr>';
	}

	echo '<tr>
			<td>' . _('Language') . ':</td>
			<td><select name="LanguageID" required="required">';

	if (!isset($_POST['LanguageID']) OR $_POST['LanguageID']==''){
		$_POST['LanguageID']=$_SESSION['Language'];
	}

	foreach ($LanguagesArray as $LanguageCode => $LanguageName){
		if ($_POST['LanguageID'] == $LanguageCode){
			echo '<option selected="selected" value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
		} else {
			echo '<option value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
		}
	}
	echo '</select></td>
			</tr>';

	echo '<tr>
			<td>' . _('Customer PO Line on SO') . ':</td>
			<td><select tabindex="18" name="CustomerPOLine" required="required">
				<option selected="selected" value="0">' . _('No') . '</option>
				<option value="1">' . _('Yes') . '</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>' . _('Invoice Addressing') . ':</td>
			<td><select tabindex="19" name="AddrInvBranch" required="required">
				<option selected="selected" value="0">' . _('Address to HO') . '</option>
				<option value="1">' . _('Address to Branch') . '</option>
				</select>
			</td>
		</tr>
		</table></td>
		</tr>
		</table>';
	if ($DataError ==0){
		echo '<br />
			<div class="centre">
				<input tabindex="20" type="submit" name="submit" value="' . _('Add New Customer') . '" />&nbsp;<input tabindex="21" type="submit" value="' . _('Reset') . '" />
			</div>';

	}
    echo '</div>';
	echo '</form>';

} else {

//DebtorNo exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr><td valign="top">';

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
						typeid,
						language_id
				FROM debtorsmaster
				WHERE debtorno = '" . $DebtorNo . "'";

		$ErrMsg = _('The customer details could not be retrieved because');
		$result = DB_query($sql,$ErrMsg);

		$myrow = DB_fetch_array($result);
		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then display the DebtorNo */
		if ($_SESSION['AutoDebtorNo']== 0 )  {
			echo '<table class="selection"><tr>
					<td>' . _('Customer Code') . ':</td>
					<td>' . $DebtorNo. '</td>
				</tr></table><br />';
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
		$_POST['Discount']  = locale_number_format($myrow['discount'] * 100,2);
		$_POST['DiscountCode']  = $myrow['discountcode'];
		$_POST['PymtDiscount']  = locale_number_format($myrow['pymtdiscount'] * 100,2);
		$_POST['CreditLimit']	= locale_number_format($myrow['creditlimit'],0);
		$_POST['InvAddrBranch'] = $myrow['invaddrbranch'];
		$_POST['TaxRef'] = $myrow['taxref'];
		$_POST['CustomerPOLine'] = $myrow['customerpoline'];
		$_POST['typeid'] = $myrow['typeid'];
		$_POST['LanguageID'] = $myrow['language_id'];

		echo '<input type="hidden" name="DebtorNo" value="' . $DebtorNo . '" />';
        echo '<table class="selection">';

	} else {
	// its a new customer being added
		echo '<input type="hidden" name="New" value="Yes" />';
        echo '<table class="selection">';

		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then provide an input box for the DebtorNo to manually assigned */
		if ($_SESSION['AutoDebtorNo']== 0 )  {
			echo '<tr>
					<td>' . _('Customer Code') . ':</td>
					<td><input ' . (in_array('DebtorNo',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="DebtorNo" required="required" data-type="no-illegal-chars" title="' . _('The customer code can be up to 10 alpha-numeric characters long or underscore') . '" value="' . $DebtorNo . '" size="12" maxlength="10" /></td></tr>';
		}
	}
	if (isset($_GET['Modify'])) {
		echo '<tr>
				<td>' . _('Customer Name') . ':</td>
				<td>' . $_POST['CustName'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 1 (Street)') . ':</td>
				<td>' . $_POST['Address1'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 2 (Street)') . ':</td>
				<td>' . $_POST['Address2'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 3 (Suburb/City)') . ':</td>
				<td>' . $_POST['Address3'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 4 (State/Province)') . ':</td>
				<td>' . $_POST['Address4'] . '</td>
			</tr>
			<tr>
				<td>' . _('Address Line 5 (Postal Code)') . ':</td>
				<td>' . $_POST['Address5'] . '</td>
			</tr>
			<tr>
				<td>' . _('Country') . ':</td>
				<td>' . $_POST['Address6'] . '</td>
			</tr>';
	} else {
		echo '<tr>
				<td>' . _('Customer Name') . ':</td>
				<td><input ' . (in_array('CustName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="CustName" required="required" autofocus="autofocus" value="' . $_POST['CustName'] . '" size="42" maxlength="40" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 1 (Street)') . ':</td>
				<td><input ' . (in_array('Address1',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address1" required="required" size="42" maxlength="40" value="' . $_POST['Address1'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 2 (Street)') . ':</td>
				<td><input ' . (in_array('Address2',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address2" size="42" maxlength="40" value="' . $_POST['Address2'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 3 (Suburb/City)') . ':</td>
				<td><input ' . (in_array('Address3',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address3" size="42" maxlength="40" value="' . $_POST['Address3'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 4 (State/Province)') . ':</td>
				<td><input ' . (in_array('Address4',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address4" size="42" maxlength="40" value="' . $_POST['Address4'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Address Line 5 (Postal Code)') . ':</td>
				<td><input ' . (in_array('Address5',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address5" size="42" maxlength="40" value="' . $_POST['Address5'] . '" /></td>
			</tr>';
		echo '<tr>
				<td>' . _('Country') . ':</td>
				<td><select name="Address6">';
		foreach ($CountriesArray as $CountryEntry => $CountryName){
			if (isset($_POST['Address6']) AND (strtoupper($_POST['Address6']) == strtoupper($CountryName))){
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
			}elseif (!isset($_POST['Address6']) AND $CountryName == "") {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
			} else {
				echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
			}
		}
		echo '</select></td>
			</tr>';

	}
// Select sales types for drop down list
	if (isset($_GET['Modify'])) {
		$result=DB_query("SELECT sales_type FROM salestypes WHERE typeabbrev='".$_POST['SalesType']."'");
		$myrow=DB_fetch_array($result);
		echo '<tr>
				<td>' . _('Sales Type') . ':</td>
				<td>' . $myrow['sales_type'] . '</td></tr>';
	} else {
		$result=DB_query("SELECT typeabbrev, sales_type FROM salestypes");
		echo '<tr>
				<td>' . _('Sales Type') . '/' . _('Price List') . ':</td>
				<td><select name="SalesType" required="required">';
		while ($myrow = DB_fetch_array($result)) {
			if ($_POST['SalesType']==$myrow['typeabbrev']){
				echo '<option selected="selected" value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			} else {
				echo '<option value="'. $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			}
		} //end while loop
		DB_data_seek($result,0);
        echo '</select></td></tr>';
	}

// Select Customer types for drop down list for SELECT/UPDATE
	if (isset($_GET['Modify'])) {
		$result=DB_query("SELECT typename FROM debtortype WHERE typeid='".$_POST['typeid']."'");
		$myrow=DB_fetch_array($result);
		echo '<tr>
				<td>' . _('Customer Type') . ':</td>
				<td>' . $myrow['typename'] . '</td>
			</tr>';
	} else {
		$result=DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
		echo '<tr>
				<td>' . _('Customer Type') . ':</td>
				<td><select name="typeid" required="required">';
		while ($myrow = DB_fetch_array($result)) {
				if ($_POST['typeid']==$myrow['typeid']){
					echo '<option selected="selected" value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
				} else {
					echo '<option value="'. $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
				}
		} //end while loop
		DB_data_seek($result,0);
	}

	if (isset($_GET['Modify'])) {
		echo '</select></td></tr>
			<tr><td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
				<td>' . $_POST['ClientSince'] . '</td></tr>';

		echo '</table></td>
				<td><table class="selection">';

		echo '<tr>
				<td>' . _('Discount Percent') . ':</td>
				<td>' . $_POST['Discount'] . '</td>
			</tr>
			<tr>
				<td>' . _('Discount Code') . ':</td>
				<td>' . $_POST['DiscountCode'] . '</td>
			</tr>
			<tr>
				<td>' . _('Payment Discount Percent') . ':</td>
				<td>' . $_POST['PymtDiscount'] . '</td>
			</tr>
			<tr>
				<td>' . _('Credit Limit') . ':</td>
				<td>' . $_POST['CreditLimit'] . '</td>
			</tr>
			<tr>
				<td>' . _('Tax Reference') . ':</td>
				<td>' . $_POST['TaxRef'] . '</td>
			</tr>';
	} else {
		echo '</select></td>
			</tr>
			<tr>
				<td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
				<td><input ' . (in_array('ClientSince',$Errors) ?  'class="inputerror"' : '' ) .' type="text" class="date" name="ClientSince" size="11" maxlength="10" value="' . $_POST['ClientSince'] . '" /></td>
			</tr>
			</table></td>
			<td><table class="selection">';

		echo '<tr>
				<td>' . _('Discount Percent') . ':</td>
				<td><input type="text" name="Discount" class="number" size="5" maxlength="4" value="' . $_POST['Discount'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Discount Code') . ':</td>
				<td><input ' . (in_array('DiscountCode',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="DiscountCode" size="3" maxlength="2" value="' . $_POST['DiscountCode'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Payment Discount Percent') . ':</td>
				<td><input ' . (in_array('PymtDiscount',$Errors) ?  'class="inputerror"' : '' ) .' type="text" class="number" name="PymtDiscount" size="5" maxlength="4" value="' . $_POST['PymtDiscount'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Credit Limit') . ':</td>
				<td><input ' . (in_array('CreditLimit',$Errors) ?  'class="inputerror"' : '' ) .' type="text" class="integer" name="CreditLimit" required="required" size="16" maxlength="14" value="' . $_POST['CreditLimit'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Tax Reference') . ':</td>
				<td><input type="text" name="TaxRef" size="22" maxlength="20"  value="' . $_POST['TaxRef'] . '" /></td>
			</tr>';
	}

	if (isset($_GET['Modify'])) {
		$result=DB_query("SELECT terms FROM paymentterms WHERE termsindicator='".$_POST['PaymentTerms']."'");
		$myrow=DB_fetch_array($result);
		echo '<tr>
				<td>' . _('Payment Terms') . ':</td>
				<td>' . $myrow['terms'] . '</td>
			</tr>';
	} else {
		$result=DB_query("SELECT terms, termsindicator FROM paymentterms");
		echo '<tr>
				<td>' . _('Payment Terms') . ':</td>
				<td><select name="PaymentTerms" required="required">';
		while ($myrow = DB_fetch_array($result)) {
			if ($_POST['PaymentTerms']==$myrow['termsindicator']){
				echo '<option selected="selected" value="'. $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
			} else {
				echo '<option value="'. $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
			}
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>
			</tr>';
	}

	if (isset($_GET['Modify'])) {
		$result=DB_query("SELECT reasondescription FROM holdreasons WHERE reasoncode='".$_POST['HoldReason']."'");
		$myrow=DB_fetch_array($result);
		echo '<tr>
				<td>' . _('Credit Status') . ':</td>
				<td>' . $myrow['reasondescription'] . '</td>
			</tr>';
	} else {
		$result=DB_query("SELECT reasoncode, reasondescription FROM holdreasons");
		echo '<tr>
				<td>' . _('Credit Status') . ':</td>
				<td><select name="HoldReason" required="required">';
		while ($myrow = DB_fetch_array($result)) {
			if ($_POST['HoldReason']==$myrow['reasoncode']){
				echo '<option selected="selected" value="'. $myrow['reasoncode'] . '">' . $myrow['reasondescription'] . '</option>';
			} else {
				echo '<option value="'. $myrow['reasoncode'] . '">' . $myrow['reasondescription'] . '</option>';
			}
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>
			</tr>';
	}

	if (isset($_GET['Modify'])) {
		echo '<tr>
				<td>' . _('Customer Currency') . ':</td>
				<td>' . $CurrencyName[$_POST['CurrCode']] . '</td></tr>';
	} else {
		$result=DB_query("SELECT currency, currabrev FROM currencies");
		echo '<tr>
				<td>' . _('Customer Currency') . ':</td>
				<td><select name="CurrCode" required="required">';
		while ($myrow = DB_fetch_array($result)) {
			echo '<option';
			if ($_POST['CurrCode']==$myrow['currabrev']){
				echo ' selected="selected"';
			}
			echo ' value="'. $myrow['currabrev'] . '">' . $CurrencyName[$myrow['currabrev']] . '</option>';
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>
			</tr>';
	}

    if (!isset($_POST['LanguageID']) OR $_POST['LanguageID']==''){
        $_POST['LanguageID']=$_SESSION['Language'];
    }

	if (isset($_GET['Modify'])) {
        echo '<tr>
                <td>' . _('Language') . ':</td>';
        foreach ($LanguagesArray as $LanguageCode => $LanguageName){
            if ($_POST['LanguageID'] == $LanguageCode){
                echo '<td>' . $LanguageName['LanguageName'];
            }
        }
        echo '</td>
        </tr>';
	} else {
        echo '<tr>
                <td>' . _('Language') . ':</td>
                <td><select name="LanguageID" required="required">';
        foreach ($LanguagesArray as $LanguageCode => $LanguageName){
            if ($_POST['LanguageID'] == $LanguageCode){
                echo '<option selected="selected" value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
            } else {
                echo '<option value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
            }
        }
        echo '</select></td>
        </tr>';
    }
	echo '<tr>
			<td>' . _('Require Customer PO Line on SO') . ':</td>';
	if (isset($_GET['Modify'])) {
		if ($_POST['CustomerPOLine']==0){
			echo '<td>' . _('No') . '</td>';
		} else {
			echo '<td>' . _('Yes') . '</td>';
		}
	} else {
		echo '<td><select name="CustomerPOLine">';
		if ($_POST['CustomerPOLine']==0){
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
			echo '<option value="1">' . _('Yes') . '</option>';
		} else {
			echo '<option value="0">' . _('No') . '</option>';
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		}
		echo '</select></td>';
	}
	echo '</tr>';

	if (isset($_GET['Modify'])) {
		if ($_POST['InvAddrBranch']==0){
			echo '<tr>
					<td>' . _('Invoice Addressing') . ':</td>
					<td>' . _('Address to HO') . '</td>
				</tr>';
		} else {
			echo '<tr>
					<td>' . _('Invoice Addressing') . ':</td>
					<td>' . _('Address to Branch') . '</td>
				</tr>';
		}
	} else {
		echo '<tr>
				<td>' . _('Invoice Addressing') . ':</td>
				<td><select name="AddrInvBranch">';
		if ($_POST['InvAddrBranch']==0){
			echo '<option selected="selected" value="0">' . _('Address to HO') . '</option>';
			echo '<option value="1">' . _('Address to Branch') . '</option>';
		} else {
			echo '<option value="0">' . _('Address to HO') . '</option>';
			echo '<option selected="selected" value="1">' . _('Address to Branch') . '</option>';
		}
	}

	echo '</select></td>
		</tr>
		</table></td>
		</tr>
		<tr><td colspan="2">';

	if (isset($_GET['delete'])) { //User hit delete link on customer contacts
		/*Process this first before showing remaining contacts */
		$resultupcc = DB_query("DELETE FROM custcontacts
								WHERE debtorno='".$DebtorNo."'
								AND contid='".$ID."'");
		prnMsg(_('Contact Deleted'),'success');
	}

  	$sql = "SELECT contid,
					debtorno,
					contactname,
					role,
					phoneno,
					notes,
					email
			FROM custcontacts
			WHERE debtorno='".$DebtorNo."'
			ORDER BY contid";
	$result = DB_query($sql);

	echo '<table class="selection">';
	if (isset($_GET['Modify'])) {
		echo '<tr>
				<th>' . _('Name') . '</th>
				<th>' . _('Role') . '</th>
				<th>' . _('Phone Number') . '</th>
				<th>' . _('Email') . '</th>
				<th>' . _('Notes') . '</th>
			</tr>';
	} else {
		echo '<tr>
				<th>' . _('Name') . '</th>
				<th>' . _('Role') . '</th>
				<th>' . _('Phone Number') . '</th>
				<th>' . _('Email') . '</th>
				<th>' . _('Notes') . '</th>
				<th>' . _('Edit') . '</th>
				<th colspan="2"><input type="submit" name="AddContact" value="' . _('Add Contact') . '" /></th>
			</tr>';
	}

	while ($myrow = DB_fetch_array($result)) {

		if (isset($_GET['Modify'])) {
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="mailto:%s">%s</a></td>
					<td>%s</td>
					</tr>',
					$myrow['contactname'],
					$myrow['role'],
					$myrow['phoneno'],
					$myrow['email'],
					$myrow['email'],
					$myrow['notes']);
		} else {
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="mailto:%s">%s</a></td>
					<td>%s</td>
					<td><a href="AddCustomerContacts.php?Id=%s&amp;DebtorNo=%s">' .  _('Edit'). '</a></td>
					<td><a href="%sID=%s&amp;DebtorNo=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this customer contact?') . '\');">' .  _('Delete'). '</a></td>
					</tr>',
					$myrow['contactname'],
					$myrow['role'],
					$myrow['phoneno'],
					$myrow['email'],
					$myrow['email'],
					$myrow['notes'],
					$myrow['contid'],
					$myrow['debtorno'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$myrow['contid'],
					$myrow['debtorno']);
		}
	}//END WHILE LIST LOOP
	echo '</table>';

	echo'</td></tr></table>';

	if (isset($_POST['New']) AND $_POST['New']) {
		echo '<div class="centre">
				<input type="submit" name="submit" value="' . _('Add New Customer') . '" />&nbsp;
				<input type="submit" name="Reset" value="' . _('Reset') . '" />
			</div>';
	} elseif (!isset($_GET['Modify'])){
		echo '<br />
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Update Customer') . '" />&nbsp;
				<input type="submit" name="delete" value="' . _('Delete Customer') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
            </div>';
	}

	echo '</div>
          </form>';
} // end of main ifs

include('includes/footer.php');
?>

<?php

require(__DIR__ . '/includes/session.php');

include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.
if (isset($_POST['ClientSince'])){$_POST['ClientSince'] = ConvertSQLDate($_POST['ClientSince']);}

if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
	$ViewTopic = 'AccountsReceivable';
	$BookMark = 'AmendCustomer';
} else {
	$ViewTopic = 'AccountsReceivable';
	$BookMark = 'NewCustomer';
}

$Title = __('Customer Maintenance');
/* webERP manual links before header.php */
$ViewTopic = 'AccountsReceivable';
$BookMark = 'NewCustomer';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . __('Customer') .
	'" alt="" />' . ' ' . __('Customer Maintenance') . '
	</p>';

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['DebtorNo'] = mb_strtoupper($_POST['DebtorNo']);

	$SQL="SELECT COUNT(debtorno) FROM debtorsmaster WHERE debtorno='".$_POST['DebtorNo']."'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_row($Result);
	if ($MyRow[0]>0 AND isset($_POST['New'])) {
		$InputError = 1;
		prnMsg( __('The customer number already exists in the database'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	}elseif (mb_strlen($_POST['CustName']) > 40 OR mb_strlen($_POST['CustName'])==0) {
		$InputError = 1;
		prnMsg( __('The customer name must be entered and be forty characters or less long'),'error');
		$Errors[$i] = 'CustName';
		$i++;
	} elseif ($_SESSION['AutoDebtorNo']==0 AND mb_strlen($_POST['DebtorNo']) ==0) {
		$InputError = 1;
		prnMsg( __('The debtor code cannot be empty'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif ($_SESSION['AutoDebtorNo']==0 AND (ContainsIllegalCharacters($_POST['DebtorNo']) OR mb_strpos($_POST['DebtorNo'], ' '))) {
		$InputError = 1;
		prnMsg( __('The customer code cannot contain any of the following characters') . " . - ' &amp; + \" " . __('or a space'),'error');
		$Errors[$i] = 'DebtorNo';
		$i++;
	} elseif (mb_strlen($_POST['Address1']) >40) {
		$InputError = 1;
		prnMsg( __('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address1';
		$i++;
	} elseif (mb_strlen($_POST['Address2']) >40) {
		$InputError = 1;
		prnMsg( __('The Line 2 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address2';
		$i++;
	} elseif (mb_strlen($_POST['Address3']) >40) {
		$InputError = 1;
		prnMsg( __('The Line 3 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address3';
		$i++;
	} elseif (mb_strlen($_POST['Address4']) >50) {
		$InputError = 1;
		prnMsg( __('The Line 4 of the address must be fifty characters or less long'),'error');
		$Errors[$i] = 'Address4';
		$i++;
	} elseif (mb_strlen($_POST['Address5']) >20) {
		$InputError = 1;
		prnMsg( __('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address5';
		$i++;
	} elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
		$InputError = 1;
		prnMsg( __('The credit limit must be numeric'),'error');
		$Errors[$i] = 'CreditLimit';
		$i++;
	} elseif (!is_numeric(filter_number_format($_POST['PymtDiscount']))) {
		$InputError = 1;
		prnMsg( __('The payment discount must be numeric'),'error');
		$Errors[$i] = 'PymtDiscount';
		$i++;
	} elseif (!Is_Date($_POST['ClientSince'])) {
		$InputError = 1;
		prnMsg( __('The customer since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$Errors[$i] = 'ClientSince';
		$i++;
	} elseif (!is_numeric(filter_number_format($_POST['Discount']))) {
		$InputError = 1;
		prnMsg( __('The discount percentage must be numeric'),'error');
		$Errors[$i] = 'Discount';
		$i++;
	} elseif (filter_number_format($_POST['CreditLimit']) <0) {
		$InputError = 1;
		prnMsg( __('The credit limit must be a positive number'),'error');
		$Errors[$i] = 'CreditLimit';
		$i++;
	} elseif ((filter_number_format($_POST['PymtDiscount'])> 10) OR (filter_number_format($_POST['PymtDiscount']) <0)) {
		$InputError = 1;
		prnMsg( __('The payment discount is expected to be less than 10% and greater than or equal to 0'),'error');
		$Errors[$i] = 'PymtDiscount';
		$i++;
	} elseif ((filter_number_format($_POST['Discount'])> 100) OR (filter_number_format($_POST['Discount']) <0)) {
		$InputError = 1;
		prnMsg( __('The discount is expected to be less than 100% and greater than or equal to 0'),'error');
		$Errors[$i] = 'Discount';
		$i++;
	}

	if ($InputError !=1){

		$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

		if (!isset($_POST['New'])) {

			$SQL = "SELECT count(id)
					  FROM debtortrans
					where debtorno = '" . $_POST['DebtorNo'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);

			if ($MyRow[0] == 0) {
				$SQL = "UPDATE debtorsmaster SET name='" . $_POST['CustName'] . "',
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

				$SQL = "UPDATE debtorsmaster SET	name='" . $_POST['CustName'] . "',
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
					prnMsg( __('The currency code cannot be updated as there are already transactions for this customer'),'info');
				}
			}

			$ErrMsg = __('The customer could not be updated because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg( __('Customer updated'),'success');
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

			$SQL = "INSERT INTO debtorsmaster (
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

			$ErrMsg = __('This customer could not be added because');
			$Result = DB_query($SQL, $ErrMsg);

			$BranchCode = mb_substr($_POST['DebtorNo'],0,4);

			echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath .'/CustomerBranches.php?DebtorNo=' . $_POST['DebtorNo'] . '">';

			echo '<div class="centre">' . __('You should automatically be forwarded to the entry of a new Customer Branch page') .
			'. ' . __('If this does not happen') .' (' . __('if the browser does not support META Refresh') . ') ' .
			'<a href="' . $RootPath . '/CustomerBranches.php?DebtorNo=' . $_POST['DebtorNo']  . '"></a></div>';

			include('includes/footer.php');
			exit();
		}
	} else {
		prnMsg( __('Validation failed') . '. ' . __('No updates or deletes took place'),'error');
	}

} elseif (isset($_POST['delete'])) {

//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$SQL= "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST['DebtorNo'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		$CancelDelete = 1;
		prnMsg( __('This customer cannot be deleted because there are transactions that refer to it'),'warn');
		echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('transactions against this customer');

	} else {
		$SQL= "SELECT COUNT(*) FROM salesorders WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			$CancelDelete = 1;
			prnMsg( __('Cannot delete the customer record because orders have been created against it'),'warn');
			echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('orders against this customer');
		} else {
			$SQL= "SELECT COUNT(*) FROM salesanalysis WHERE cust='" . $_POST['DebtorNo'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0]>0) {
				$CancelDelete = 1;
				prnMsg( __('Cannot delete this customer record because sales analysis records exist for it'),'warn');
				echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('sales analysis records against this customer');
			} else {

				// Check if there are any users that refer to this CUSTOMER code
				$SQL= "SELECT COUNT(*) FROM www_users WHERE www_users.customerid = '" . $_POST['DebtorNo'] . "'";

				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);

				if ($MyRow[0]>0) {
					prnMsg(__('Cannot delete this customer because users exist that refer to it') . '. ' . __('Purge old users first'),'warn');
					echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' '.__('users referring to this Branch/customer');
				} else {
						// Check if there are any contract that refer to this branch code
					$SQL = "SELECT COUNT(*) FROM contracts WHERE contracts.debtorno = '" . $_POST['DebtorNo'] . "'";

					$Result = DB_query($SQL);
					$MyRow = DB_fetch_row($Result);

					if ($MyRow[0]>0) {
						prnMsg(__('Cannot delete this customer because contracts have been created that refer to it') . '. ' . __('Purge old contracts first'),'warn');
						echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' '.__('contracts referring to this customer');
					}
				}
			}
		}

	}
	if ($CancelDelete==0) { //ie not cancelled the delete as a result of above tests
		$SQL="DELETE FROM custbranch WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$Result = DB_query($SQL, $ErrMsg);
		$SQL="DELETE FROM custcontacts WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$Result = DB_query($SQL);
		$SQL="DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$Result = DB_query($SQL);
		prnMsg( __('Customer') . ' ' . $_POST['DebtorNo'] . ' ' . __('has been deleted - together with all the associated branches and contacts'),'success');
		include('includes/footer.php');
		unset($_SESSION['CustomerID']);
		exit();
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
	$SQL="SELECT COUNT(typeabbrev)
				FROM salestypes";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_row($Result);
	if ($MyRow[0]==0) {
		prnMsg( __('In order to create a new customer you must first set up at least one sales type/price list') . '<br />' .
			__('Click').' ' . '<a target="_blank" href="' . $RootPath . '/SalesTypes.php">' . __('here').' ' . '</a>' . __('to set up your price lists'),'warning') . '<br />';
		$SetupErrors += 1;
	}
	$SQL="SELECT COUNT(typeid)
				FROM debtortype";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_row($Result);
	if ($MyRow[0]==0) {
		prnMsg( __('In order to create a new customer you must first set up at least one customer type') . '<br />' .
			__('Click').' ' . '<a target="_blank" href="' . $RootPath . '/CustomerTypes.php">' . __('here').' ' . '</a>' . __('to set up your customer types'),'warning');
		$SetupErrors += 1;
	}

	if ($SetupErrors>0) {
		echo '<br /><div class="centre"><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'" >' . __('Click here to continue') . '</a></div>';
		include('includes/footer.php');
		exit();
	}
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="New" value="Yes" />';

	$DataError =0;

	echo '<fieldset>
			<legend>', __('Create Customer Details'), '</legend>
			<fieldset class="Column1">';

	/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
	then provide an input box for the DebtorNo to manually assigned */
	if ($_SESSION['AutoDebtorNo']==0)  {
		echo '<field>
				<label for="DebtorNo">' . __('Customer Code') . ':</label>
				<input type="text" data-type="no-illegal-chars" tabindex="1"  name="DebtorNo"  required="required" autofocus="autofocus" title ="" placeholder="'.__('alpha-numeric').'" size="11" maxlength="10" />
				<fieldhelp>'.__('Up to 10 characters for the customer code. The following characters are prohibited:') . ' \' &quot; + . &amp; \\ &gt; &lt;</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="CustName">' . __('Customer Name') . ':</label>
			<input tabindex="2" type="text" name="CustName" required="required" size="42" maxlength="40" />
		</field>
		<field>
			<label for="Address1">' . __('Address Line 1 (Street)') . ':</label>
			<input tabindex="3" type="text" name="Address1" required="required" size="42" maxlength="40" />
		</field>
		<field>
			<label for="Address2">' . __('Address Line 2 (Street)') . ':</label>
			<input tabindex="4" type="text" name="Address2" size="42" maxlength="40" />
		</field>
		<field>
			<label for="Address3">' . __('Address Line 3 (Suburb/City)') . ':</label>
			<input tabindex="5" type="text" name="Address3" size="42" maxlength="40" />
		</field>
		<field>
			<label for="Address4">' . __('Address Line 4 (State/Province)') . ':</label>
			<input tabindex="6" type="text" name="Address4" size="42" maxlength="40" />
		</field>
		<field>
			<label for="Address5">' . __('Address Line 5 (Postal Code)') . ':</label>
			<input tabindex="7" type="text" name="Address5" size="22" maxlength="20" />
		</field>';

	if (!isset($_POST['Address6'])) {
		$_POST['Address6'] = $CountriesArray[$_SESSION['CountryOfOperation']];
	}
	echo '<field>
			<label for="Address6">' . __('Country') . ':</label>
			<select name="Address6">';
	foreach ($CountriesArray as $CountryEntry => $CountryName){
		if (isset($_POST['Address6']) AND (strtoupper($_POST['Address6']) == strtoupper($CountryName))){
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
		} else {
			echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
		}
	}
	echo '</select>
		</field>';

// Show Sales Type drop down list
	$Result = DB_query("SELECT typeabbrev, sales_type FROM salestypes ORDER BY sales_type");
	if (DB_num_rows($Result)==0){
		$DataError =1;
		echo '<field>
				<td colspan="2">' . prnMsg(__('No sales types/price lists defined'),'error') . '<br /><a href="' . $RootPath . '/SalesTypes.php?" target="_parent">' . __('Setup Types') . '</a></td>
			</field>';
	} else {
		echo '<field>
				<label for="SalesType">' . __('Sales Type') . '/' . __('Price List') . ':</label>
				<select tabindex="9" name="SalesType" required="required">';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option value="'. $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
		} //end while loopre
		DB_data_seek($Result,0);
		echo '</select>
			</field>';
	}

// Show Customer Type drop down list
	$Result = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	if (DB_num_rows($Result)==0){
		$DataError =1;
		echo '<a href="' . $RootPath . '/SalesTypes.php?" target="_parent">' . __('Setup Types') . '</a>';
		echo '<field>
				<td colspan="2">' . prnMsg(__('No Customer types/price lists defined'),'error') . '</td>
			</field>';
	} else {
		echo '<field>
				<label for="typeid">' . __('Customer Type') . ':</label>
				<select tabindex="9" name="typeid" required="required">';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option value="'. $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($Result,0);
		echo '</select>
			</field>';
	}

	$DateString = Date('Y-m-d');
	echo '<field>
			<label for="ClientSince">' . __('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</label>
			<input tabindex="10" type="date" name="ClientSince" value="' . $DateString . '" size="11" maxlength="10" />
		</field>
	</fieldset>';

	echo '<fieldset class="Column2">
				<field>
					<label for="Discount">' . __('Discount Percent') . ':</label>
					<input tabindex="11" type="text" class="number" name="Discount" value="0" size="5" maxlength="4" />
				</field>
				<field>
					<label for="DiscountCode">' . __('Discount Code') . ':</label>
					<input tabindex="12" type="text" name="DiscountCode" size="3" maxlength="2" />
				</field>
				<field>
					<label for="PymtDiscount">' . __('Payment Discount Percent') . ':</label>
					<input tabindex="13" type="text" class ="number" name="PymtDiscount" value="0" size="5" maxlength="4" />
				</field>
				<field>
					<label for="CreditLimit">' . __('Credit Limit') . ':</label>
					<input tabindex="14" type="text" class="integer" name="CreditLimit" required="required" value="' . locale_number_format($_SESSION['DefaultCreditLimit'],0) . '" size="16" maxlength="14" />
				</field>
				<field>
					<label for="TaxRef">' . __('Tax Reference') . ':</label>
					<input tabindex="15" type="text" name="TaxRef" size="22" maxlength="20" />
				</field>';

	$Result = DB_query("SELECT terms, termsindicator FROM paymentterms");
	if (DB_num_rows($Result)==0){
		$DataError =1;
		echo '<field><td colspan="2">' . prnMsg(__('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first'),'error','',true) . '</td></field>';
	} else {

		echo '<field>
				<label for="PaymentTerms">' . __('Payment Terms') . ':</label>
				<select tabindex="15" name="PaymentTerms" required="required">';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option value="'. $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
		} //end while loop
		DB_data_seek($Result,0);

		echo '</select>
			</field>';
	}
	echo '<field>
			<label for="HoldReason">' . __('Credit Status') . ':</label>
			<select tabindex="16" name="HoldReason" required="required">';

	$Result = DB_query("SELECT reasoncode, reasondescription FROM holdreasons");
	if (DB_num_rows($Result)==0){
		$DataError =1;
		echo '<field>
				<td colspan="2">' . prnMsg(__('There are no credit statuses currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td>
			</field>';
	} else {
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option value="'. $MyRow['reasoncode'] . '">' . $MyRow['reasondescription'] . '</option>';
		} //end while loop
		DB_data_seek($Result,0);
		echo '</select>
			</field>';
	}

	$Result = DB_query("SELECT currency, currabrev FROM currencies");
	if (DB_num_rows($Result)==0){
		$DataError =1;
		echo '<field>
				<td colspan="2">' . prnMsg(__('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td>
			</field>';
	} else {
		if (!isset($_POST['CurrCode'])){
			$CurrResult = DB_query("SELECT currencydefault FROM companies WHERE coycode=1");
			$MyRow = DB_fetch_row($CurrResult);
			$_POST['CurrCode'] = $MyRow[0];
		}
		echo '<field>
				<label for="CurrCode">' . __('Customer Currency') . ':</label>
				<select tabindex="17" name="CurrCode" required="required">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['CurrCode']==$MyRow['currabrev']){
				echo '<option selected="selected" value="'. $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			} else {
				echo '<option value="'. $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result,0);

		echo '</select>
			</field>';
	}

	echo '<field>
			<label for="LanguageID">' . __('Language') . ':</label>
			<select name="LanguageID" required="required">';

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
	echo '</select>
		</field>';

	echo '<field>
			<label for="CustomerPOLine">' . __('Customer PO Line on SO') . ':</label>
			<select tabindex="18" name="CustomerPOLine" required="required">
				<option selected="selected" value="0">' . __('No') . '</option>
				<option value="1">' . __('Yes') . '</option>
			</select>
		</field>
		<field>
			<label for="AddrInvBranch">' . __('Invoice Addressing') . ':</label>
			<select tabindex="19" name="AddrInvBranch" required="required">
				<option selected="selected" value="0">' . __('Address to HO') . '</option>
				<option value="1">' . __('Address to Branch') . '</option>
			</select>
		</field>
		</fieldset>
		</fieldset>';
	if ($DataError ==0){
		echo '<div class="centre">
				<input tabindex="20" type="submit" name="submit" value="' . __('Add New Customer') . '" />&nbsp;<input tabindex="21" type="submit" value="' . __('Reset') . '" />
			</div>';

	}
	echo '</form>';

} else {

//DebtorNo exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Edit Customer Details'), '</legend>';

	if (!isset($_POST['New'])) {
		$SQL = "SELECT debtorno,
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

		$ErrMsg = __('The customer details could not be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);

		$MyRow = DB_fetch_array($Result);
		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then display the DebtorNo */
		if ($_SESSION['AutoDebtorNo']== 0 )  {
			echo '<fieldset class="Column1"><field>
					<label for="DebtorNo">' . __('Customer Code') . ':</label>
					<fieldtext>' . $DebtorNo. '</fieldtext>
				</field>';
		}
		$_POST['CustName'] = $MyRow['name'];
		$_POST['Address1']  = $MyRow['address1'];
		$_POST['Address2']  = $MyRow['address2'];
		$_POST['Address3']  = $MyRow['address3'];
		$_POST['Address4']  = $MyRow['address4'];
		$_POST['Address5']  = $MyRow['address5'];
		$_POST['Address6']  = $MyRow['address6'];
		$_POST['SalesType'] = $MyRow['salestype'];
		$_POST['CurrCode']  = $MyRow['currcode'];
		$_POST['ClientSince'] = ConvertSQLDate($MyRow['clientsince']);
		$_POST['HoldReason']  = $MyRow['holdreason'];
		$_POST['PaymentTerms']  = $MyRow['paymentterms'];
		$_POST['Discount']  = locale_number_format($MyRow['discount'] * 100,2);
		$_POST['DiscountCode']  = $MyRow['discountcode'];
		$_POST['PymtDiscount']  = locale_number_format($MyRow['pymtdiscount'] * 100,2);
		$_POST['CreditLimit']	= locale_number_format($MyRow['creditlimit'],0);
		$_POST['InvAddrBranch'] = $MyRow['invaddrbranch'];
		$_POST['TaxRef'] = $MyRow['taxref'];
		$_POST['CustomerPOLine'] = $MyRow['customerpoline'];
		$_POST['typeid'] = $MyRow['typeid'];
		$_POST['LanguageID'] = $MyRow['language_id'];

		echo '<input type="hidden" name="DebtorNo" value="' . $DebtorNo . '" />';

	} else {
	// its a new customer being added
		echo '<input type="hidden" name="New" value="Yes" />';

		/* if $AutoDebtorNo in config.php has not been set or if it has been set to a number less than one,
		then provide an input box for the DebtorNo to manually assigned */
		if ($_SESSION['AutoDebtorNo']== 0 )  {
			echo '<field>
					<label for="DebtorNo">' . __('Customer Code') . ':</label>
					<input ' . (in_array('DebtorNo',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="DebtorNo" required="required" data-type="no-illegal-chars" title="" value="' . $DebtorNo . '" size="12" maxlength="10" />
				<fieldhelp>' . __('The customer code can be up to 10 alpha-numeric characters long or underscore') . '</fieldhelp>
			</field>';
		}
	}
	if (isset($_GET['Modify'])) {
		echo '<field>
				<label for="CustName">' . __('Customer Name') . ':</label>
				<fieldtext>' . $_POST['CustName'] . '</fieldtext>
			</field>
			<field>
				<label for="Address1">' . __('Address Line 1 (Street)') . ':</label>
				<fieldtext>' . $_POST['Address1'] . '</fieldtext>
			</field>
			<field>
				<label for="Address2">' . __('Address Line 2 (Street)') . ':</label>
				<fieldtext>' . $_POST['Address2'] . '</fieldtext>
			</field>
			<field>
				<label for="Address3">' . __('Address Line 3 (Suburb/City)') . ':</label>
				<fieldtext>' . $_POST['Address3'] . '</fieldtext>
			</field>
			<field>
				<label for="Address4">' . __('Address Line 4 (State/Province)') . ':</label>
				<fieldtext>' . $_POST['Address4'] . '</fieldtext>
			</field>
			<field>
				<label for="Address5">' . __('Address Line 5 (Postal Code)') . ':</label>
				<fieldtext>' . $_POST['Address5'] . '</fieldtext>
			</field>
			<field>
				<label for="Address6">' . __('Country') . ':</label>
				<fieldtext>' . $_POST['Address6'] . '</fieldtext>
			</field>';
	} else {
		echo '<field>
				<label for="CustName">' . __('Customer Name') . ':</label>
				<input ' . (in_array('CustName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="CustName" required="required" autofocus="autofocus" value="' . $_POST['CustName'] . '" size="42" maxlength="40" />
			</field>
			<field>
				<label for="Address1">' . __('Address Line 1 (Street)') . ':</label>
				<td><input ' . (in_array('Address1',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address1" required="required" size="42" maxlength="40" value="' . $_POST['Address1'] . '" /></td>
			</field>
			<field>
				<label for="Address2">' . __('Address Line 2 (Street)') . ':</label>
				<td><input ' . (in_array('Address2',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address2" size="42" maxlength="40" value="' . $_POST['Address2'] . '" /></td>
			</field>
			<field>
				<label for="Address3">' . __('Address Line 3 (Suburb/City)') . ':</label>
				<td><input ' . (in_array('Address3',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address3" size="42" maxlength="40" value="' . $_POST['Address3'] . '" /></td>
			</field>
			<field>
				<label for="Address4">' . __('Address Line 4 (State/Province)') . ':</label>
				<td><input ' . (in_array('Address4',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address4" size="42" maxlength="40" value="' . $_POST['Address4'] . '" /></td>
			</field>
			<field>
				<label for="Address5">' . __('Address Line 5 (Postal Code)') . ':</label>
				<td><input ' . (in_array('Address5',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address5" size="42" maxlength="40" value="' . $_POST['Address5'] . '" /></td>
			</field>';
		echo '<field>
				<label for="Address6">' . __('Country') . ':</label>
				<select name="Address6">';
		foreach ($CountriesArray as $CountryEntry => $CountryName){
			if (isset($_POST['Address6']) AND (strtoupper($_POST['Address6']) == strtoupper($CountryName))){
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
			}elseif (!isset($_POST['Address6']) AND $CountryName == "") {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
			} else {
				echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
			}
		}
		echo '</select>
			</field>';

	}
// Select sales types for drop down list
	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT sales_type FROM salestypes WHERE typeabbrev='".$_POST['SalesType']."'");
		$MyRow=DB_fetch_array($Result);
		echo '<field>
				<td>' . __('Sales Type') . ':</td>
				<td>' . $MyRow['sales_type'] . '</td></field>';
	} else {
		$Result = DB_query("SELECT typeabbrev, sales_type FROM salestypes");
		echo '<field>
				<label for="SalesType">' . __('Sales Type') . '/' . __('Price List') . ':</label>
				<select name="SalesType" required="required">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['SalesType']==$MyRow['typeabbrev']){
				echo '<option selected="selected" value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
			} else {
				echo '<option value="'. $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result,0);
		echo '</select>
			</field>';
	}

// Select Customer types for drop down list for SELECT/UPDATE
	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT typename FROM debtortype WHERE typeid='".$_POST['typeid']."'");
		$MyRow=DB_fetch_array($Result);
		echo '<field>
				<td>' . __('Customer Type') . ':</td>
				<td>' . $MyRow['typename'] . '</td>
			</field>';
	} else {
		$Result = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
		echo '<field>
				<label for="typeid">' . __('Customer Type') . ':</label>
				<select name="typeid" required="required">';
		while ($MyRow = DB_fetch_array($Result)) {
				if ($_POST['typeid']==$MyRow['typeid']){
					echo '<option selected="selected" value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
				} else {
					echo '<option value="'. $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
				}
		} //end while loop
		echo '</select>
			</field>';
		DB_data_seek($Result,0);
	}

	if (isset($_GET['Modify'])) {
		echo '<field>
				<label for="ClientSince">' . __('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</label>
				<fieldtext>' . $_POST['ClientSince'] . '</fieldtext>
			</field>';

		echo '</fieldset>
				<fieldset class="Column2">';

		echo '<field>
				<label>' . __('Discount Percent') . ':</label>
				<fieldtext>' . $_POST['Discount'] . '</fieldtext>
			</field>
			<field>
				<label>' . __('Discount Code') . ':</label>
				<fieldtext>' . $_POST['DiscountCode'] . '</fieldtext>
			</field>
			<field>
				<label>' . __('Payment Discount Percent') . ':</label>
				<fieldtext>' . $_POST['PymtDiscount'] . '</fieldtext>
			</field>
			<field>
				<label>' . __('Credit Limit') . ':</label>
				<fieldtext>' . $_POST['CreditLimit'] . '</fieldtext>
			</field>
			<field>
				<label>' . __('Tax Reference') . ':</label>
				<fieldtext>' . $_POST['TaxRef'] . '</fieldtext>
			</field>';
	} else {
		echo '<field>
				<label for="DefaultDateFormat">' . __('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</label>
				<input ' . (in_array('ClientSince',$Errors) ?  'class="inputerror"' : '' ) .' type="date" name="ClientSince" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['ClientSince']) . '" />
			</field>
			</fieldset>
			<fieldset class="Column2">';

		echo '<field>
				<label for="Discount">' . __('Discount Percent') . ':</label>
				<input type="text" name="Discount" class="number" size="5" maxlength="4" value="' . $_POST['Discount'] . '" />
			</field>
			<field>
				<label for="DiscountCode">' . __('Discount Code') . ':</label>
				<input ' . (in_array('DiscountCode',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="DiscountCode" size="3" maxlength="2" value="' . $_POST['DiscountCode'] . '" />
			</field>
			<field>
				<label for="PymtDiscount">' . __('Payment Discount Percent') . ':</label>
				<input ' . (in_array('PymtDiscount',$Errors) ?  'class="inputerror"' : '' ) .' type="text" class="number" name="PymtDiscount" size="5" maxlength="4" value="' . $_POST['PymtDiscount'] . '" />
			</field>
			<field>
				<label for="CreditLimit">' . __('Credit Limit') . ':</label>
				<input ' . (in_array('CreditLimit',$Errors) ?  'class="inputerror"' : '' ) .' type="text" class="integer" name="CreditLimit" required="required" size="16" maxlength="14" value="' . $_POST['CreditLimit'] . '" />
			</field>
			<field>
				<label for="TaxRef">' . __('Tax Reference') . ':</label>
				<input type="text" name="TaxRef" size="22" maxlength="20"  value="' . $_POST['TaxRef'] . '" />
			</field>';
	}

	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT terms FROM paymentterms WHERE termsindicator='".$_POST['PaymentTerms']."'");
		$MyRow=DB_fetch_array($Result);
		echo '<field>
				<td>' . __('Payment Terms') . ':</td>
				<td>' . $MyRow['terms'] . '</td>
			</field>';
	} else {
		$Result = DB_query("SELECT terms, termsindicator FROM paymentterms");
		echo '<field>
				<label for="PaymentTerms">' . __('Payment Terms') . ':</label>
				<select name="PaymentTerms" required="required">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['PaymentTerms']==$MyRow['termsindicator']){
				echo '<option selected="selected" value="'. $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
			} else {
				echo '<option value="'. $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result,0);
		echo '</select>
			</field>';
	}

	if (isset($_GET['Modify'])) {
		$Result = DB_query("SELECT reasondescription FROM holdreasons WHERE reasoncode='".$_POST['HoldReason']."'");
		$MyRow=DB_fetch_array($Result);
		echo '<field>
				<td>' . __('Credit Status') . ':</td>
				<td>' . $MyRow['reasondescription'] . '</td>
			</field>';
	} else {
		$Result = DB_query("SELECT reasoncode, reasondescription FROM holdreasons");
		echo '<field>
				<label for="HoldReason">' . __('Credit Status') . ':</label>
				<select name="HoldReason" required="required">';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['HoldReason']==$MyRow['reasoncode']){
				echo '<option selected="selected" value="'. $MyRow['reasoncode'] . '">' . $MyRow['reasondescription'] . '</option>';
			} else {
				echo '<option value="'. $MyRow['reasoncode'] . '">' . $MyRow['reasondescription'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result,0);
		echo '</select>
			</field>';
	}

	if (isset($_GET['Modify'])) {
		echo '<field>
				<td>' . __('Customer Currency') . ':</td>
				<td>' . $CurrencyName[$_POST['CurrCode']] . '</td></field>';
	} else {
		$Result = DB_query("SELECT currency, currabrev FROM currencies");
		echo '<field>
				<label for="CurrCode">' . __('Customer Currency') . ':</label>
				<select name="CurrCode" required="required">';
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<option';
			if ($_POST['CurrCode']==$MyRow['currabrev']){
				echo ' selected="selected"';
			}
			echo ' value="'. $MyRow['currabrev'] . '">' . $CurrencyName[$MyRow['currabrev']] . '</option>';
		} //end while loop
		DB_data_seek($Result,0);
		echo '</select>
			</field>';
	}

	if (!isset($_POST['LanguageID']) OR $_POST['LanguageID']==''){
		$_POST['LanguageID']=$_SESSION['Language'];
	}

	if (isset($_GET['Modify'])) {
		echo '<field>
				<td>' . __('Language') . ':</td>';
		foreach ($LanguagesArray as $LanguageCode => $LanguageName){
			if ($_POST['LanguageID'] == $LanguageCode){
				echo '<td>' . $LanguageName['LanguageName'];
			}
		}
		echo '</td>
		</field>';
	} else {
		echo '<field>
				<label for="LanguageID">' . __('Language') . ':</label>
				<select name="LanguageID" required="required">';
		foreach ($LanguagesArray as $LanguageCode => $LanguageName){
			if ($_POST['LanguageID'] == $LanguageCode){
				echo '<option selected="selected" value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
			} else {
				echo '<option value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
			}
		}
		echo '</select>
		</field>';
	}
	echo '<field>
			<label for="CustomerPOLine">' . __('Require Customer PO Line on SO') . ':</label>';
	if (isset($_GET['Modify'])) {
		if ($_POST['CustomerPOLine']==0){
			echo '<td>' . __('No') . '</td>';
		} else {
			echo '<td>' . __('Yes') . '</td>';
		}
	} else {
		echo '<select name="CustomerPOLine">';
		if ($_POST['CustomerPOLine']==0){
			echo '<option selected="selected" value="0">' . __('No') . '</option>';
			echo '<option value="1">' . __('Yes') . '</option>';
		} else {
			echo '<option value="0">' . __('No') . '</option>';
			echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
		}
		echo '</select>';
	}
	echo '</field>';

	if (isset($_GET['Modify'])) {
		if ($_POST['InvAddrBranch']==0){
			echo '<field>
					<td>' . __('Invoice Addressing') . ':</td>
					<td>' . __('Address to HO') . '</td>
				</field>';
		} else {
			echo '<field>
					<td>' . __('Invoice Addressing') . ':</td>
					<td>' . __('Address to Branch') . '</td>
				</field>';
		}
	} else {
		echo '<field>
				<label for="AddrInvBranch">' . __('Invoice Addressing') . ':</label>
				<select name="AddrInvBranch">';
		if ($_POST['InvAddrBranch']==0){
			echo '<option selected="selected" value="0">' . __('Address to HO') . '</option>';
			echo '<option value="1">' . __('Address to Branch') . '</option>';
		} else {
			echo '<option value="0">' . __('Address to HO') . '</option>';
			echo '<option selected="selected" value="1">' . __('Address to Branch') . '</option>';
		}
	}

	echo '</select>
		</field>
		</fieldset>
		</fieldset>';

	if (isset($_GET['delete'])) { //User hit delete link on customer contacts
		/*Process this first before showing remaining contacts */
		$Resultupcc = DB_query("DELETE FROM custcontacts
								WHERE debtorno='".$DebtorNo."'
								AND contid='".$ID."'");
		prnMsg(__('Contact Deleted'),'success');
	}

  	$SQL = "SELECT contid,
					debtorno,
					contactname,
					role,
					phoneno,
					notes,
					email
			FROM custcontacts
			WHERE debtorno='".$DebtorNo."'
			ORDER BY contid";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	if (isset($_GET['Modify'])) {
		echo '<tr>
				<th>' . __('Name') . '</th>
				<th>' . __('Role') . '</th>
				<th>' . __('Phone Number') . '</th>
				<th>' . __('Email') . '</th>
				<th>' . __('Notes') . '</th>
			</tr>';
	} else {
		echo '<tr>
				<th>' . __('Name') . '</th>
				<th>' . __('Role') . '</th>
				<th>' . __('Phone Number') . '</th>
				<th>' . __('Email') . '</th>
				<th>' . __('Notes') . '</th>
				<th>' . __('Edit') . '</th>
				<th colspan="2"><input type="submit" name="AddContact" value="' . __('Add Contact') . '" /></th>
			</tr>';
	}

	while ($MyRow = DB_fetch_array($Result)) {

		if (isset($_GET['Modify'])) {
			echo '<tr class="striped_row">
					<td>', $MyRow['contactname'], '</td>
					<td>', $MyRow['role'], '</td>
					<td>', $MyRow['phoneno'], '</td>
					<td><a href="mailto:', $MyRow['email'], '">', $MyRow['email'], '</a></td>
					<td>', $MyRow['notes'], '</td>
				</tr>';
		} else {
			echo '<tr class="striped_row">
					<td>', $MyRow['contactname'], '</td>
					<td>', $MyRow['role'], '</td>
					<td>', $MyRow['phoneno'], '</td>
					<td><a href="mailto:', $MyRow['email'], '">', $MyRow['email'], '</a></td>
					<td>', $MyRow['notes'], '</td>
					<td><a href="' . $RootPath . '/AddCustomerContacts.php?Id=', $MyRow['contid'], '&amp;DebtorNo=', $MyRow['debtorno'], '">' .  __('Edit'). '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ID=', $MyRow['contid'], '&amp;DebtorNo=', $MyRow['debtorno'], '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this customer contact?') . '\');">' .  __('Delete'). '</a></td>
				</tr>';
		}
	}//END WHILE LIST LOOP
	echo '</table>';

	echo'</td></tr></table>';

	if (isset($_POST['New']) AND $_POST['New']) {
		echo '<div class="centre">
				<input type="submit" name="submit" value="' . __('Add New Customer') . '" />&nbsp;
				<input type="reset" name="Reset" value="' . __('Reset') . '" />
			</div>';
	} elseif (!isset($_GET['Modify'])){
		echo '<div class="centre">
				<input type="submit" name="submit" value="' . __('Update Customer') . '" />&nbsp;
				<input type="reset" name="delete" value="' . __('Delete Customer') . '" onclick="return confirm(\'' . __('Are You Sure?') . '\');" />
			</div>';
	}

	echo '</div>
		  </form>';
} // end of main ifs

include('includes/footer.php');

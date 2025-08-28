<?php

// Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'CreatingNewSystem';
$BookMark = 'CompanyParameters';
$Title = __('Company Preferences');
include('includes/header.php');

// initialise no input errors assumed initially before we test
$InputError = 0;
$Errors = array();
$i = 1;

if (isset($_POST['submit'])) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['CoyName']) > 50 OR mb_strlen($_POST['CoyName'])==0) {
		$InputError = 1;
		prnMsg(__('The company name must be entered and be fifty characters or less long'), 'error');
		$Errors[$i] = 'CoyName';
		$i++;
	}

	if (mb_strlen($_POST['Email'])>0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(__('The email address is not correctly formed'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}

	if ($InputError !=1) {

		$CompanyFileHandler = fopen($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/Companies.php', 'w');
		$Contents = "<?php\n\n";
		$Contents.= "\$CompanyName['" . $_SESSION['DatabaseName'] . "'] = '" . $_POST['CoyName'] . "';\n";
		$Contents.= "?>";

		if (!fwrite($CompanyFileHandler, $Contents)) {
			fclose($CompanyFileHandler);
			echo '<div class="error">' . __('Cannot write to the Companies.php file') . '</div>';
		}
		//close file
		fclose($CompanyFileHandler);

		$SQL = "UPDATE companies SET coyname='" . $_POST['CoyName'] . "',
									companynumber = '" . $_POST['CompanyNumber'] . "',
									gstno='" . $_POST['GSTNo'] . "',
									regoffice1='" . $_POST['RegOffice1'] . "',
									regoffice2='" . $_POST['RegOffice2'] . "',
									regoffice3='" . $_POST['RegOffice3'] . "',
									regoffice4='" . $_POST['RegOffice4'] . "',
									regoffice5='" . $_POST['RegOffice5'] . "',
									regoffice6='" . $_POST['RegOffice6'] . "',
									telephone='" . $_POST['Telephone'] . "',
									fax='" . $_POST['Fax'] . "',
									email='" . $_POST['Email'] . "',
									currencydefault='" . $_POST['CurrencyDefault'] . "',
									debtorsact='" . $_POST['DebtorsAct'] . "',
									pytdiscountact='" . $_POST['PytDiscountAct'] . "',
									creditorsact='" . $_POST['CreditorsAct'] . "',
									payrollact='" . $_POST['PayrollAct'] . "',
									grnact='" . $_POST['GRNAct'] . "',
									commissionsact='" . $_POST['CommAct'] . "',
									exchangediffact='" . $_POST['ExchangeDiffAct'] . "',
									purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
									retainedearnings='" . $_POST['RetainedEarnings'] . "',
									gllink_debtors='" . $_POST['GLLink_Debtors'] . "',
									gllink_creditors='" . $_POST['GLLink_Creditors'] . "',
									gllink_stock='" . $_POST['GLLink_Stock'] ."',
									freightact='" . $_POST['FreightAct'] . "'
								WHERE coycode=1";

			$ErrMsg =  __('The company preferences could not be updated because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg( __('Company preferences updated'),'success');

			/* Alter the exchange rates in the currencies table */

			/* Get default currency rate */
			$SQL="SELECT rate from currencies WHERE currabrev='" . $_POST['CurrencyDefault'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			$NewCurrencyRate=$MyRow[0];

			/* Set new rates */
			$SQL="UPDATE currencies SET rate=rate/" . $NewCurrencyRate;
			$ErrMsg =  __('Could not update the currency rates');
			$Result = DB_query($SQL, $ErrMsg);

			/* End of update currencies */

			$ForceConfigReload = true; // Required to force a load even if stored in the session vars
			include('includes/GetConfig.php');
			$ForceConfigReload = false;

	} else {
		prnMsg( __('Validation failed') . ', ' . __('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') .
		'" alt="" />' . ' ' . $Title . '</p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>', __('Company Profile Settings'), '</legend>';

if ($InputError != 1) {
	$SQL = "SELECT coyname,
					gstno,
					companynumber,
					regoffice1,
					regoffice2,
					regoffice3,
					regoffice4,
					regoffice5,
					regoffice6,
					telephone,
					fax,
					email,
					currencydefault,
					debtorsact,
					pytdiscountact,
					creditorsact,
					payrollact,
					grnact,
					commissionsact,
					exchangediffact,
					purchasesexchangediffact,
					retainedearnings,
					gllink_debtors,
					gllink_creditors,
					gllink_stock,
					freightact
				FROM companies
				WHERE coycode=1";

	$ErrMsg =  __('The company preferences could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);


	$MyRow = DB_fetch_array($Result);

	$_POST['CoyName'] = $MyRow['coyname'];
	$_POST['GSTNo'] = $MyRow['gstno'];
	$_POST['CompanyNumber']  = $MyRow['companynumber'];
	$_POST['RegOffice1']  = $MyRow['regoffice1'];
	$_POST['RegOffice2']  = $MyRow['regoffice2'];
	$_POST['RegOffice3']  = $MyRow['regoffice3'];
	$_POST['RegOffice4']  = $MyRow['regoffice4'];
	$_POST['RegOffice5']  = $MyRow['regoffice5'];
	$_POST['RegOffice6']  = $MyRow['regoffice6'];
	$_POST['Telephone']  = $MyRow['telephone'];
	$_POST['Fax']  = $MyRow['fax'];
	$_POST['Email']  = $MyRow['email'];
	$_POST['CurrencyDefault']  = $MyRow['currencydefault'];
	$_POST['DebtorsAct']  = $MyRow['debtorsact'];
	$_POST['PytDiscountAct']  = $MyRow['pytdiscountact'];
	$_POST['CreditorsAct']  = $MyRow['creditorsact'];
	$_POST['PayrollAct']  = $MyRow['payrollact'];
	$_POST['GRNAct'] = $MyRow['grnact'];
	$_POST['CommAct'] = $MyRow['commissionsact'];
	$_POST['ExchangeDiffAct']  = $MyRow['exchangediffact'];
	$_POST['PurchasesExchangeDiffAct']  = $MyRow['purchasesexchangediffact'];
	$_POST['RetainedEarnings'] = $MyRow['retainedearnings'];
	$_POST['GLLink_Debtors'] = $MyRow['gllink_debtors'];
	$_POST['GLLink_Creditors'] = $MyRow['gllink_creditors'];
	$_POST['GLLink_Stock'] = $MyRow['gllink_stock'];
	$_POST['FreightAct'] = $MyRow['freightact'];
}

echo '<field>
		<label for="CoyName">' . __('Name') . ' (' . __('to appear on reports') . '):</label>
		<input '.(in_array('CoyName',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="1" type="text" autofocus="autofocus" required="required" name="CoyName" value="' . stripslashes($_POST['CoyName']) . '" title="" size="52" maxlength="50" />
		<fieldhelp>' . __('Enter the name of the business. This will appear on all reports and at the top of each screen. ') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="CoyNumber">' . __('Official Company Number') . ':</label>
		<input '.(in_array('CoyNumber',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="2" type="text" name="CompanyNumber" value="' . $_POST['CompanyNumber'] . '" size="22" maxlength="20" />
	</field>';

echo '<field>
		<label for="TaxRef">' . __('Tax Authority Reference') . ':</label>
		<input '.(in_array('TaxRef',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="3" type="text" name="GSTNo" value="' . $_POST['GSTNo'] . '" size="22" maxlength="20" />
	</field>';

echo '<field>
		<label for="RegOffice1">' . __('Address Line 1') . ':</label>
		<input '.(in_array('RegOffice1',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="4" type="text" name="RegOffice1" title="" required="required" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice1']) . '" />
		<fieldhelp>' . __('Enter the first line of the company registered office. This will appear on invoices and statements.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="RegOffice2">' . __('Address Line 2') . ':</label>
		<input '.(in_array('RegOffice2',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="5" type="text" name="RegOffice2" title="" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice2']) . '" />
		<fieldhelp>' . __('Enter the second line of the company registered office. This will appear on invoices and statements.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="RegOffice3">' . __('Address Line 3') . ':</label>
		<input '.(in_array('RegOffice3',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="6" type="text" name="RegOffice3" title="" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice3']) . '" />
		<fieldhelp>' . __('Enter the third line of the company registered office. This will appear on invoices and statements.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="RegOffice4">' . __('Address Line 4') . ':</label>
		<input '.(in_array('RegOffice4',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="7" type="text" name="RegOffice4" title="" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice4']) . '" />
		<fieldhelp>' . __('Enter the fourth line of the company registered office. This will appear on invoices and statements.') . '</fieldhelp>
</field>';

echo '<field>
		<label for="RegOffice5">' . __('Address Line 5') . ':</label>
		<input '.(in_array('RegOffice5',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="8" type="text" name="RegOffice5" size="22" maxlength="20" value="' . stripslashes($_POST['RegOffice5']) . '" />
	</field>';

echo '<field>
		<label for="RegOffice6">' . __('Address Line 6') . ':</label>
		<input '.(in_array('RegOffice6',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="9" type="text" name="RegOffice6" size="17" maxlength="15" value="' . stripslashes($_POST['RegOffice6']) . '" />
	</field>';

echo '<field>
		<label for="Telephone">' . __('Telephone Number') . ':</label>
		<input ', (in_array('Telephone',$Errors) ?  'class="inputerror"' : '' ), ' maxlength="25" name="Telephone" required="required" size="26" tabindex="10" type="tel" title="" value="', $_POST['Telephone'], '" />
		<fieldhelp>', __('Enter the main telephone number of the company registered office. This will appear on invoices and statements.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Fax">' . __('Facsimile Number') . ':</label>
		<input ', (in_array('Fax',$Errors) ?  'class="inputerror"' : '' ), ' maxlength="25" name="Fax" size="26" tabindex="11" type="tel" value="', $_POST['Fax'], '" />
	</field>';

echo '<field>
		<label for="Email">' . __('Email Address') . ':</label>
		<input '.(in_array('Email',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="12" type="email" name="Email" title="" required="required" placeholder="accounts@example.com" size="50" maxlength="55" value="' . $_POST['Email'] . '" />
		<fieldhelp>' . __('Enter the main company email address. This will appear on invoices and statements.') . '</fieldhelp>
	</field>';


$Result = DB_query("SELECT currabrev, currency FROM currencies");
include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.

echo '<field>
		<label for="CurrencyDefault">', __('Home Currency'), ':</label>
		<select id="CurrencyDefault" name="CurrencyDefault" tabindex="13" >';

while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['CurrencyDefault']==$MyRow['currabrev']){
		echo '<option selected="selected" value="'. $MyRow['currabrev'] . '">' . $CurrencyName[$MyRow['currabrev']] . '</option>';
	} else {
		echo '<option value="' . $MyRow['currabrev'] . '">' . $CurrencyName[$MyRow['currabrev']] . '</option>';
	}
} //end while loop

DB_free_result($Result);

echo '</select>
	</field>';

$Result = DB_query("SELECT accountcode,
						accountname
					FROM chartmaster INNER JOIN accountgroups
					ON chartmaster.group_=accountgroups.groupname
					WHERE accountgroups.pandl=0
					ORDER BY chartmaster.accountcode");

echo '<field>
		<label>' . __('Debtors Control GL Account') . ':</label>
		<select tabindex="14" title="" name="DebtorsAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['DebtorsAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_data_seek($Result,0);

echo '</select>
	<fieldhelp>' . __('Select the general ledger account to be used for posting the local currency value of all customer transactions to. This account will always represent the total amount owed by customers to the business. Only balance sheet accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . __('Creditors Control GL Account') . ':</label>
		<select tabindex="15" title="" name="CreditorsAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['CreditorsAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_data_seek($Result,0);

echo '</select>
	<fieldhelp>' . __('Select the general ledger account to be used for posting the local currency value of all supplier transactions to. This account will always represent the total amount owed by the business to suppliers. Only balance sheet accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . __('Payroll Net Pay Clearing GL Account') . ':</label>
		<select tabindex="16" name="PayrollAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['PayrollAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_data_seek($Result,0);

echo '</select>
	</field>';

echo '<field>
		<label>' . __('Goods Received Clearing GL Account') . ':</label>
		<select title="" tabindex="17" name="GRNAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['GRNAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_data_seek($Result,0);
echo '</select>
	<fieldhelp>' . __('Select the general ledger account to be used for posting the cost of goods received pending the entry of supplier invoices for the goods. This account will represent the value of goods received yet to be invoiced by suppliers. Only balance sheet accounts are available for this selection.') . '</fieldhelp>
	</field>';

echo '<field>
		<label>', __('Sales Commission Accruals Account'), ':</label>';
echo '<label>
		<select name="CommAct">';
while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['CommAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop
DB_data_seek($Result,0);
echo '</select>
	</field>';

echo '<field>
		<label>' . __('Retained Earning Clearing GL Account') . ':</label>
		<select title="" tabindex="18" name="RetainedEarnings">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['RetainedEarnings']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_free_result($Result);

echo '</select>
	<fieldhelp>' . __('Select the general ledger account to be used for clearing profit and loss accounts to that represents the accumulated retained profits of the business. Only balance sheet accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . __('Freight Re-charged GL Account') . ':</label>
		<select tabindex="19" name="FreightAct">';

$Result = DB_query("SELECT accountcode,
						accountname
					FROM chartmaster INNER JOIN accountgroups
					ON chartmaster.group_=accountgroups.groupname
					WHERE accountgroups.pandl=1
					ORDER BY chartmaster.accountcode");

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['FreightAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_data_seek($Result,0);

echo '</select>
	</field>';

echo '<field>
		<label>' . __('Sales Exchange Variances GL Account') . ':</label>
		<select title="" tabindex="20" name="ExchangeDiffAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['ExchangeDiffAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_data_seek($Result,0);

echo '</select>
	<fieldhelp>' . __('Select the general ledger account to be used for posting accounts receivable exchange rate differences to - where the exchange rate on sales invocies is different to the exchange rate of currency receipts from customers, the exchange rate is calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . __('Purchases Exchange Variances GL Account') . ':</label>
		<select tabindex="21" title="" name="PurchasesExchangeDiffAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['PurchasesExchangeDiffAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option  value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_data_seek($Result,0);

echo '</select>
	<fieldhelp>' . __('Select the general ledger account to be used for posting the exchange differences on the accounts payable transactions to. Supplier invoices entered at one currency and paid in the supplier currency at a different exchange rate have the differences calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . __('Payment Discount GL Account') . ':</label>
		<select title="" tabindex="22" name="PytDiscountAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['PytDiscountAct']==$MyRow[0]){
		echo '<option selected="selected" value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	} else {
		echo '<option value="'. $MyRow[0] . '">' . htmlspecialchars($MyRow[1],ENT_QUOTES,'UTF-8') . ' ('.$MyRow[0].')</option>';
	}
} //end while loop

DB_data_seek($Result,0);

echo '</select>
	<fieldhelp>' . __('Select the general ledger account to be used for posting the value of payment discounts given to customers at the time of entering a receipt. Only profit and loss general ledger accounts are available for this selection.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . __('Create GL entries for AR transactions') . ':</label>
		<select title="" tabindex="23" name="GLLink_Debtors">';

if ($_POST['GLLink_Debtors']==0){
	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	echo '<option value="1">' . __('Yes'). '</option>';
} else {
	echo '<option selected="selected" value="1">' . __('Yes'). '</option>';
	echo '<option value="0">' . __('No'). '</option>';
}

echo '</select>
	<fieldhelp>' . __('Select yes to ensure that webERP creates general ledger journals for all accounts receivable transactions. webERP will maintain the debtors control account (selected above) to ensure it should always balance to the list of customer balances in local currency.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . __('Create GL entries for AP transactions') . ':</label>
		<select title="" tabindex="24" name="GLLink_Creditors">';

if ($_POST['GLLink_Creditors']==0){
	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	echo '<option value="1">' . __('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	echo '<option value="0">' . __('No') . '</option>';
}

echo '</select>
	<fieldhelp>' . __('Select yes to ensure that webERP creates general ledger journals for all accounts payable transactions. webERP will maintain the creditors control account (selected above) to ensure it should always balance to the list of supplier balances in local currency.') . '</fieldhelp>
</field>';

echo '<field>
		<label>' . __('Create GL entries for stock transactions')  . ':</label>
		<select title="" tabindex="25" name="GLLink_Stock">';

if ($_POST['GLLink_Stock']=='0'){
	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	echo '<option value="1">' . __('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	echo '<option value="0">' . __('No') . '</option>';
}

echo '</select>
	<fieldhelp>' . __('Select yes to ensure that webERP creates general ledger journals for all inventory transactions. webERP will maintain the stock control accounts (selected under the inventory categories set up) to ensure they balance. Only balance sheet general ledger accounts can be selected.') . '</fieldhelp>
</field>';


echo '</fieldset>
	<div class="centre">
		<input tabindex="26" type="submit" name="submit" value="' . __('Update') . '" />
	</div>';
echo '</form>';

include('includes/footer.php');

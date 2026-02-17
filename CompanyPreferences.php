<?php

// Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'CreatingNewSystem';
$BookMark = 'CompanyParameters';
$Title = __('Company Preferences');
include('includes/header.php');
include('includes/UIGeneralFunctions.php');

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

	if ($InputError != 1) {

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
									salesexchangediffact='" . $_POST['SalesExchangeDiffAct'] . "',
									purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
									currencyexchangediffact='" . $_POST['CurrencyExchangeDiffAct'] . "',
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

if ($InputError !=  1) {
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
					salesexchangediffact,
					purchasesexchangediffact,
					currencyexchangediffact,
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
	$_POST['SalesExchangeDiffAct']  = $MyRow['salesexchangediffact'];
	$_POST['PurchasesExchangeDiffAct']  = $MyRow['purchasesexchangediffact'];
	$_POST['CurrencyExchangeDiffAct']  = $MyRow['currencyexchangediffact'];
	$_POST['RetainedEarnings'] = $MyRow['retainedearnings'];
	$_POST['GLLink_Debtors'] = $MyRow['gllink_debtors'];
	$_POST['GLLink_Creditors'] = $MyRow['gllink_creditors'];
	$_POST['GLLink_Stock'] = $MyRow['gllink_stock'];
	$_POST['FreightAct'] = $MyRow['freightact'];
}

echo  FieldToSelectOneText('CoyName', $_POST['CoyName'], 52, 50, __('Name') . ' (' . __('to appear on reports') . ')',
	__('Enter the name of the business. This will appear on all reports and at the top of each screen.'), '', 1, true, true);

echo  FieldToSelectOneText('CompanyNumber', $_POST['CompanyNumber'], 22, 20, __('Official Company Number'),
	__('Enter the official company number.'), '', 2);

echo  FieldToSelectOneText('GSTNo', $_POST['GSTNo'], 22, 20, __('Tax Authority Reference'),
	__('Enter the tax authority reference.'), '', 3);

echo  FieldToSelectOneText('RegOffice1', stripslashes($_POST['RegOffice1']), 42, 40, __('Address Line 1'),
	__('Enter the first line of the company registered office. This will appear on invoices and statements.'), '', 4);
	
echo  FieldToSelectOneText('RegOffice2', stripslashes($_POST['RegOffice2']), 42, 40, __('Address Line 2'),
	__('Enter the second line of the company registered office. This will appear on invoices and statements.'), '', 5, false, false);

echo  FieldToSelectOneText('RegOffice3', stripslashes($_POST['RegOffice3']), 42, 40, __('Address Line 3'),
	__('Enter the third line of the company registered office. This will appear on invoices and statements.'), '', 6, false, false);

echo  FieldToSelectOneText('RegOffice4', stripslashes($_POST['RegOffice4']), 42, 40, __('Address Line 4'),
	__('Enter the fourth line of the company registered office. This will appear on invoices and statements.'), '', 7, false, false);

echo  FieldToSelectOneText('RegOffice5', stripslashes($_POST['RegOffice5']), 22, 20, __('Address Line 5'),
	__('Enter the fifth line of the company registered office. This will appear on invoices and statements.'), '', 8, false, false);

echo  FieldToSelectOneText('RegOffice6', stripslashes($_POST['RegOffice6']), 17, 15, __('Address Line 6'),
	__('Enter the sixth line of the company registered office. This will appear on invoices and statements.'), '', 9, false, false);

echo FieldToSelectOneTelephoneNumber('Telephone', $_POST['Telephone'], 26, 25, __('Telephone Number'),
	__('Enter the main telephone number of the company registered office. This will appear on invoices and statements.'), '', 10);

echo FieldToSelectOneTelephoneNumber('Fax', $_POST['Fax'], 26, 25, __('Facsimile Number'),
	__('Enter the main facsimile number of the company registered office. This will appear on invoices and statements.'), '', 11, false, false);

echo FieldToSelectOneEmail('Email', $_POST['Email'], 50, 55, __('Email Address'),
	__('Enter the main company email address. This will appear on invoices and statements.'), '', 12);

echo FieldToSelectOneCurrency('CurrencyDefault', $_POST['CurrencyDefault'], __('Home Currency'), 
	__('Select the home currency for the company. This will be used for all financial transactions and reporting.'),
	'', 13);

echo FieldToSelectOneGLAccount('DebtorsAct', $_POST['DebtorsAct'],  __('Debtors Control GL Account'), 
	__('Select the general ledger account to be used for posting the local currency value of all customer transactions to. This account will always represent the total amount owed by customers to the business. Only balance sheet accounts are available for this selection.'),
	'BS', 14);

echo FieldToSelectOneGLAccount('CreditorsAct', $_POST['CreditorsAct'],  __('Creditors Control GL Account'), 
	__('Select the general ledger account to be used for posting the local currency value of all supplier transactions to. This account will always represent the total amount owed by the business to suppliers. Only balance sheet accounts are available for this selection.'),
	'BS', 15);

echo FieldToSelectOneGLAccount('PayrollAct', $_POST['PayrollAct'],  __('Payroll Net Pay Clearing GL Account'), 
	__('Select the general ledger account to be used for posting the payroll net pay clearing transactions to. Only balance sheet accounts are available for this selection.'),
	'BS', 16);

echo FieldToSelectOneGLAccount('GRNAct', $_POST['GRNAct'],  __('Goods Received Clearing GL Account'), 
	__('Select the general ledger account to be used for posting the cost of goods received pending the entry of supplier invoices for the goods. This account will represent the value of goods received yet to be invoiced by suppliers. Only balance sheet accounts are available for this selection.'),
	'BS', 17);

echo FieldToSelectOneGLAccount('CommAct', $_POST['CommAct'],  __('Sales Commission Accruals Account'), 
	__('Select the general ledger account to be used for posting the sales commission accruals. Only balance sheet accounts are available for this selection.'),
	'BS', 18);

echo FieldToSelectOneGLAccount('RetainedEarnings', $_POST['RetainedEarnings'],  __('Retained Earning Clearing GL Account'), 
	__('Select the general ledger account to be used for clearing profit and loss accounts to that represents the accumulated retained profits of the business. Only balance sheet accounts are available for this selection.'),
	'BS', 19);

echo FieldToSelectOneGLAccount('FreightAct', $_POST['FreightAct'],  __('Freight Re-charged GL Account'), 
	__('Select the general ledger account to be used for posting the freight re-charged transactions to. Only balance sheet accounts are available for this selection.'),
	'BS', 20);

echo FieldToSelectOneGLAccount('SalesExchangeDiffAct', $_POST['SalesExchangeDiffAct'],  __('Sales Exchange Variances GL Account'), 
	__('Select the general ledger account to be used for posting accounts receivable exchange rate differences to - where the exchange rate on sales invocies is different to the exchange rate of currency receipts from customers, the exchange rate is calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.'),
	'P&L', 21);

echo FieldToSelectOneGLAccount('PurchasesExchangeDiffAct', $_POST['PurchasesExchangeDiffAct'],  __('Purchases Exchange Variances GL Account'), 
	__('Select the general ledger account to be used for posting the exchange differences on the accounts payable transactions to. Supplier invoices entered at one currency and paid in the supplier currency at a different exchange rate have the differences calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.'),
	'P&L', 22);

echo FieldToSelectOneGLAccount('CurrencyExchangeDiffAct', $_POST['CurrencyExchangeDiffAct'],  __('Currency Exchange Variances GL Account'), 
	__('Select the general ledger account to be used for posting the exchange differences on the currency transactions to. Currency transactions with an exchange rate different to the home currency exchange rate have the differences calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.'),
	'P&L', 23);

echo FieldToSelectOneGLAccount('PytDiscountAct', $_POST['PytDiscountAct'],  __('Payment Discount GL Account'), 
	__('Select the general ledger account to be used for posting the value of payment discounts given to customers at the time of entering a receipt. Only profit and loss general ledger accounts are available for this selection.'),
	'P&L', 24);

echo FieldToSelectFromTwoOptions('0', __('No'), '1', __('Yes'),
	'GLLink_Debtors', $_POST['GLLink_Debtors'], __('Create GL entries for AR transactions'), 
	__('Select yes to ensure that webERP creates general ledger journals for all accounts receivable transactions. webERP will maintain the debtors control account (selected above) to ensure it should always balance to the list of customer balances in local currency.'),
	'', 25);

echo FieldToSelectFromTwoOptions('0', __('No'), '1', __('Yes'),
	'GLLink_Creditors', $_POST['GLLink_Creditors'], __('Create GL entries for AP transactions'), 
	__('Select yes to ensure that webERP creates general ledger journals for all accounts payable transactions. webERP will maintain the creditors control account (selected above) to ensure it should always balance to the list of supplier balances in local currency.'),
	'', 26);

echo FieldToSelectFromTwoOptions('0', __('No'), '1', __('Yes'),
	'GLLink_Stock', $_POST['GLLink_Stock'], __('Create GL entries for stock transactions'), 
	__('Select yes to ensure that webERP creates general ledger journals for all inventory transactions. webERP will maintain the stock control accounts (selected under the inventory categories set up) to ensure they balance. Only balance sheet general ledger accounts can be selected.'),
	'', 27);

echo '</fieldset>';
echo OneButtonCenteredForm('submit', __('Update'));
echo '</form>';

include('includes/footer.php');

<?php

//	Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.
/*
	The country field is unneeded because the country_code is included inside the currency_code (firsts two letters).
*/

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'Setup';
$BookMark = 'Currencies';
$Title = __('Currencies Maintenance');
include('includes/header.php');

include_once('includes/CountriesArray.php');// To get the country name from the country code.
include_once('includes/CurrenciesArray.php');// To get the currency name from the currency code.
include_once('includes/SQL_CommonFunctions.php');

if (isset($_GET['SelectedCurrency'])) {
	$SelectedCurrency = $_GET['SelectedCurrency'];
} elseif (isset($_POST['SelectedCurrency'])) {
	$SelectedCurrency = $_POST['SelectedCurrency'];
}

$ForceConfigReload = true;
include('includes/GetConfig.php');
$ForceConfigReload = false;

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

$Errors = array();

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/currency.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible
	$i=1;

	$SQL="SELECT count(currabrev)
			FROM currencies
			WHERE currabrev='".$_POST['Abbreviation']."'";

	$Result = DB_query($SQL);
	$MyRow=DB_fetch_row($Result);

	if ($MyRow[0]!=0 AND !isset($SelectedCurrency)) {
		$InputError = 1;
		prnMsg( __('The currency already exists in the database'),'error');
		$Errors[$i] = 'Abbreviation';
		$i++;
	}

	if (!is_numeric(filter_number_format($_POST['ExchangeRate']))) {
		$InputError = 1;
		prnMsg(__('The exchange rate must be numeric'),'error');
		$Errors[$i] = 'ExchangeRate';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['DecimalPlaces']))) {
		$InputError = 1;
	   prnMsg(__('The number of decimal places to display for amounts in this currency must be numeric'),'error');
		$Errors[$i] = 'DecimalPlaces';
		$i++;
	}elseif (filter_number_format($_POST['DecimalPlaces'])<0) {
		$InputError = 1;
	   prnMsg(__('The number of decimal places to display for amounts in this currency must be positive or zero'),'error');
		$Errors[$i] = 'DecimalPlaces';
		$i++;
	} elseif (filter_number_format($_POST['DecimalPlaces'])>4) {
		$InputError = 1;
	   prnMsg(__('The number of decimal places to display for amounts in this currency is expected to be 4 or less'),'error');
		$Errors[$i] = 'DecimalPlaces';
		$i++;
	}

	if (mb_strlen($_POST['Country']) > 50) {
		$InputError = 1;
		prnMsg(__('The currency country must be 50 characters or less long'),'error');
		$Errors[$i] = 'Country';
		$i++;
	}
	if (mb_strlen($_POST['HundredsName']) > 15) {
		$InputError = 1;
		prnMsg(__('The hundredths name must be 15 characters or less long'),'error');
		$Errors[$i] = 'HundredsName';
		$i++;
	}
	if (($FunctionalCurrency != '') AND (isset($SelectedCurrency) AND $SelectedCurrency==$FunctionalCurrency)) {
		$_POST['ExchangeRate'] = 1;
	}

	if (isset($SelectedCurrency) AND $InputError !=1) {
		/*Get the previous exchange rate. We will need it later to adjust bank account balances */
		$SQLOldRate = "SELECT rate
				FROM currencies
				WHERE currabrev = '" . $SelectedCurrency . "'";
		$ResultOldRate = DB_query($SQLOldRate);
		$MyRow = DB_fetch_row($ResultOldRate);
		$OldRate = $MyRow[0];

		/*SelectedCurrency could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		$SQL = "UPDATE currencies SET	country='". $_POST['Country']. "',
										hundredsname='" . $_POST['HundredsName'] . "',
										decimalplaces='" . filter_number_format($_POST['DecimalPlaces']) . "',
										rate='" .filter_number_format($_POST['ExchangeRate']) . "',
										webcart='" .$_POST['webcart'] . "'
					WHERE currabrev = '" . $SelectedCurrency . "'";
		$Msg = __('The currency definition record has been updated');
		$NewRate = $_POST['ExchangeRate'];

	} else if ($InputError !=1) {

	/*Selected currencies is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */
		$SQL = "INSERT INTO currencies (
						currency,
						currabrev,
						country,
						hundredsname,
						decimalplaces,
						rate,
						webcart
					) VALUES ('" .
						$CurrencyName[$_POST['Abbreviation']] . "', '" .
						$_POST['Abbreviation'] . "', '" .
						$_POST['Country'] . "', '" .
						$_POST['HundredsName'] .  "', '" .
						filter_number_format($_POST['DecimalPlaces']) . "', '" .
						filter_number_format($_POST['ExchangeRate']) . "', '" .
						$_POST['webcart'] . "')";
		$Msg = __('The currency definition record has been added');
	}
	//run the SQL from either of the above possibilites
	$ExDiffTransNo = GetNextTransNo(36);
	DB_Txn_Begin();

	$Result = DB_query($SQL);
	if ($InputError!=1) {
		prnMsg( $Msg,'success');
	}

	/* Now we should update the functional currency value of the bank accounts of the $SelectedCurrency
	Example: if functional currency = IDR and we have a bank account in USD.
	Before rate was 1 USD = 9.000 IDR so OldRate = 1 /9.000 = 0.000111
	if the new exchange rate is 1 USD = 10.000 IDR NewRate will be 0.0001.
	If we had 5.000 USD on the bank account, we had 45.000.000 IDR on the balance sheet.
	After we update to the new rate, we still have 5.000 USD on the bank account
	but the balance value of the bank account is 50.000.000 IDR, so let's adjust the value */

	if (isset($SelectedCurrency) AND $InputError !=1) {
		/*Get the current period */
		$PostingDate = Date($_SESSION['DefaultDateFormat']);
		$PeriodNo = GetPeriod($PostingDate);

		/* get all the bank accounts denominated on the selected currency */
		$SQLBankAccounts = "SELECT 	bankaccountname,
									accountcode
							FROM bankaccounts
							WHERE currcode = '" . $SelectedCurrency . "'";
		$ResultBankAccounts = DB_query($SQLBankAccounts);
		while ($MyRowBankAccount=DB_fetch_array($ResultBankAccounts)) {

			/*Get the balance of the bank account concerned */
			$SQL = "SELECT SUM(amount) as balance
					FROM gltotals
					WHERE period<='" . $PeriodNo . "'
					AND account='" . $MyRowBankAccount['accountcode'] . "'";

			$ErrMsg = __('The bank account balance could not be returned by the SQL because');
			$BalanceResult = DB_query($SQL, $ErrMsg);
			$MyRow = DB_fetch_row($BalanceResult);
			$OldBalanceInFunctionalCurrency = $MyRow[0];
			$BalanceInAccountCurrency = $OldBalanceInFunctionalCurrency * $OldRate;

			/* Now calculate the Balance in functional currency at the new rate */
			$NewBalanceInFucntionalCurrency = $BalanceInAccountCurrency / $NewRate;

			/* If some adjustment has to be done, do it! */
			$DifferenceToAdjust = $NewBalanceInFucntionalCurrency - $OldBalanceInFunctionalCurrency;
			if($OldRate != $NewRate) {

				$SQL = "INSERT INTO gltrans (
								type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount
							) VALUES (
								36, '" .
								$ExDiffTransNo . "', '" .
								FormatDateForSQL($PostingDate) . "', '" .
								$PeriodNo . "', '" .
								$_SESSION['CompanyRecord']['exchangediffact'] . "', '" .
								mb_substr($MyRowBankAccount['bankaccountname'] . ' ' . __('currency rate adjustment to') . ' ' . locale_number_format($NewRate, 'Variable') . ' ' . $SelectedCurrency . '/' . $_SESSION['CompanyRecord']['currencydefault'], 0, 200) . "', '" .
								(-$DifferenceToAdjust) . "')";
				$ErrMsg = __('Cannot insert a GL entry for the exchange difference because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
				$SQL = "INSERT INTO gltrans (
								type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount
							) VALUES (36, '" .
								$ExDiffTransNo . "', '" .
								FormatDateForSQL($PostingDate) . "', '" .
								$PeriodNo . "', '" .
								$MyRowBankAccount['accountcode'] . "', '" .
								mb_substr($MyRowBankAccount['bankaccountname'] . ' ' . __('currency rate adjustment to') . ' ' . locale_number_format($NewRate, 'Variable') . ' ' . $SelectedCurrency . '/' . $_SESSION['CompanyRecord']['currencydefault'], 0, 200) . "', '" .
								($DifferenceToAdjust) . "')";

				$Result = DB_query($SQL, $ErrMsg, '', true);
				prnMsg(__('Bank Account') . ' ' . $MyRowBankAccount['bankaccountname'] . ' ' . __('Currency Rate difference of') . ' ' . locale_number_format($DifferenceToAdjust, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . __('has been posted'),'success');
			}
		}
	}
	DB_Txn_Commit();

	unset($SelectedCurrency);
	unset($_POST['Country']);
	unset($_POST['HundredsName']);
	unset($_POST['DecimalPlaces']);
	unset($_POST['ExchangeRate']);
	unset($_POST['Abbreviation']);
	unset($_POST['webcart']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$SQL= "SELECT COUNT(*) FROM debtorsmaster
			WHERE currcode = '" . $SelectedCurrency . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0)
	{
		prnMsg(__('Cannot delete this currency because customer accounts have been created referring to this currency') .
		 	'<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('customer accounts that refer to this currency'),'warn');
	} else {
		$SQL= "SELECT COUNT(*) FROM suppliers
				WHERE suppliers.currcode = '".$SelectedCurrency."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			prnMsg(__('Cannot delete this currency because supplier accounts have been created referring to this currency')
			 . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('supplier accounts that refer to this currency'),'warn');
		} else {
			$SQL= "SELECT COUNT(*) FROM banktrans
					WHERE currcode = '" . $SelectedCurrency . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				prnMsg(__('Cannot delete this currency because there are bank transactions that use this currency') .
				'<br />' . ' ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('bank transactions that refer to this currency'),'warn');
			} elseif ($FunctionalCurrency==$SelectedCurrency) {
				prnMsg(__('Cannot delete this currency because it is the functional currency of the company'),'warn');
			} else {
				$SQL= "SELECT COUNT(*) FROM bankaccounts
					WHERE currcode = '" . $SelectedCurrency . "'";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] > 0) {
					prnMsg(__('Cannot delete this currency because there are bank accounts that use this currency') .
					'<br />' . ' ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('bank accounts that refer to this currency'),'warn');
				} else {
					//only delete if used in neither customer or supplier, comp prefs, bank trans accounts
					$SQL="DELETE FROM currencies WHERE currabrev='" . $SelectedCurrency . "'";
					$Result = DB_query($SQL);
					prnMsg(__('The currency definition record has been deleted'),'success');
				}
			}
		}
	}
	//end if currency used in customer or supplier accounts
}

if (!isset($SelectedCurrency)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCurrency will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of payment termss will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT	currabrev,
					country,
					hundredsname,
					rate,
					decimalplaces,
					webcart
				FROM currencies";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th>&nbsp;</th>
			<th>' . __('Country') . '</th>
			<th>' . __('ISO4217 Code') . '</th>
			<th>' . __('Currency Name') . '</th>
			<th>' . __('Hundredths Name') . '</th>
			<th>' . __('Decimal Places') . '</th>
			<th>' . __('Show in webSHOP')  . '</th>
			<th>' . __('Exchange Rate') . '</th>
			<th>' . __('1 / Ex Rate') . '</th>
			<th>' . __('Ex Rate - ECB')  . '</th>
			<th colspan="3">' . __('Maintenance')  . '</th>
		</tr>';

	/*Get published currency rates from Eurpoean Central Bank */
	if ($_SESSION['UpdateCurrencyRatesDaily'] != '0') {
		$CurrencyRatesArray = GetECBCurrencyRates();
	} else {
		$CurrencyRatesArray = array();
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['currabrev']==$FunctionalCurrency) {
			echo '<tr style="background-color:#FFbbbb">';
		} else {
			echo  '<tr class="striped_row">';
		}
		// Lets show the country flag
		$ImageFile = mb_strtoupper($MyRow['currabrev']) . '.gif';

		if(!file_exists('images/flags/' . $ImageFile)) {
			$ImageFile =  'blank.gif';
		}
		if ($MyRow['webcart'] == 1) {
			$ShowInWebText = __('Yes');
		} else {
			$ShowInWebText = __('No');
		}

		if ($MyRow['rate'] == 0) {
			$MyRow['rate'] = 1;
		}
		if (!array_key_exists($MyRow['currabrev'], $CurrencyRatesArray)) {
			$CurrencyRatesArray[$FunctionalCurrency] = 0;
			$CurrencyRatesArray[$MyRow['currabrev']] = 0;
		}

		$Rate = GetCurrencyRate($MyRow['currabrev'],$CurrencyRatesArray);
		if ($Rate == 0) {
			$Rate = 1;
		}

		if ($MyRow['currabrev']!=$FunctionalCurrency) {
			echo '	<td><img alt="" src="', $RootPath , '/images/flags/', $ImageFile, '" /></td>
					<td>', $CountriesArray[substr($MyRow['currabrev'], 0, 2)], '</td>
					<td>', $MyRow['currabrev'], '</td>
					<td>', $CurrencyName[$MyRow['currabrev']], '</td>
					<td>', $MyRow['hundredsname'], '</td>
					<td class="number">', locale_number_format($MyRow['decimalplaces'], 0), '</td>
					<td class="centre">', $ShowInWebText, '</td>
					<td class="number">', locale_number_format($MyRow['rate'], 'Variable'), '</td>
					<td class="number">', locale_number_format(1/$MyRow['rate'], 2), '</td>
					<td class="number">', locale_number_format($Rate, 8), '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedCurrency=', $MyRow['currabrev'], '">', __('Edit'), '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedCurrency=', $MyRow['currabrev'], '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this currency?') . '\');">', __('Delete'), '</a></td>
					<td><a href="', $RootPath, '/ExchangeRateTrend.php?&amp;CurrencyToShow=' . $MyRow['currabrev'], '">' . __('Graph') . '</a></td>
				</tr>';
		} else {
			echo '	<td><img alt="" src="', $RootPath , '/images/flags/', $ImageFile, '" /></td>
					<td>', $CountriesArray[substr($MyRow['currabrev'], 0, 2)], '</td>
					<td>', $MyRow['currabrev'], '</td>
					<td>', $CurrencyName[$MyRow['currabrev']], '</td>
					<td>', $MyRow['hundredsname'], '</td>
					<td class="number">', locale_number_format($MyRow['decimalplaces'], 0), '</td>
					<td class="centre">', $ShowInWebText, '</td>
					<td class="number">', locale_number_format(1, 8), '</td>
					<td class="number">', locale_number_format(1, 2), '</td>
					<td class="number">', locale_number_format(1, 8), '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?&amp;SelectedCurrency=' . urlencode($MyRow['currabrev']) . '">' . __('Edit') . '</a></td>
					<td colspan="2"><a href="' . $RootPath . '/CompanyPreferences.php#CurrencyDefault">' . __('Functional Currency') . '</a></td>
				</tr>';
		}
	} //END WHILE LIST LOOP
	echo '</table>
			<br />';
} //end of ifs and buts!


if (isset($SelectedCurrency)) {
	echo '<div class="centre"><a href="' .htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">' . __('Show all currency definitions') . '</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedCurrency) AND $SelectedCurrency!='') {
		//editing an existing currency

		$SQL = "SELECT
					currabrev,
					country,
					hundredsname,
					decimalplaces,
					rate,
					webcart
				FROM currencies
				WHERE currabrev='" . $SelectedCurrency . "'";

		$ErrMsg = __('An error occurred in retrieving the currency information');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_array($Result);

		$_POST['Abbreviation'] = $MyRow['currabrev'];
		$_POST['Country']  = $MyRow['country'];
		$_POST['HundredsName']  = $MyRow['hundredsname'];
		$_POST['ExchangeRate']  = locale_number_format($MyRow['rate'], 'Variable');
		$_POST['DecimalPlaces']  = locale_number_format($MyRow['decimalplaces'], 0);
		$_POST['webcart']  = $MyRow['webcart'];

		echo '<input type="hidden" name="SelectedCurrency" value="' . $SelectedCurrency . '" />';
		echo '<input type="hidden" name="Abbreviation" value="' . $_POST['Abbreviation'] . '" />';
		echo '<fieldset>
				<legend>', __('Edit Currency Details'), '</legend>
				<field>
					<label for="Abbreviation">' . __('ISO 4217 Currency Code').':</label>
					<fieldtext>' . $_POST['Abbreviation'] . '</fieldtext>
				</field>';

	} else { //end of if $SelectedCurrency only do the else when a new record is being entered
		if (!isset($_POST['Abbreviation'])) {$_POST['Abbreviation']='';}
		echo '<fieldset>
				<legend>', __('New currency Details'), '</legend>
				<field>
					<label for="Abbreviation">' .__('Currency') . ':</label>
					<select name="Abbreviation">';
		foreach ($CurrencyName as $CurrencyCode => $CurrencyNameTxt) {
			echo '<option value="' . $CurrencyCode . '">' . $CurrencyCode . ' - ' . $CurrencyNameTxt . '</option>';
		}

		echo '</select>
			</field>';
	}

	echo '<field>
			<label for="Country">' . __('Country') . ':</label>';
	if (!isset($_POST['Country'])) {
		$_POST['Country'] = '';
	}
	if ($_POST['Abbreviation'] != $FunctionalCurrency) {
		echo '<input type="text" name="Country" size="30" required="required" minlength="1" maxlength="50" value="' . $_POST['Country'] . '" />';
	} else {
		echo '<fieldtext>' . $_POST['Country'] . '</fieldtext>';
		echo '<input type="hidden" name="Country" value="' . $_POST['Country'] . '" />';
	}
	echo '</field>';

	echo '<field>
			<label for="HundredsName">' . __('Hundredths Name') . ':</label>';
	if (!isset($_POST['HundredsName'])) {
		$_POST['HundredsName'] = '';
	}
	echo '<input type="text" name="HundredsName" size="10" required="required" minlength="1" maxlength="15" value="' . $_POST['HundredsName'] . '" />
		</field>';

	echo '<field>
			<label for="DecimalPlaces">' . __('Decimal Places to Display') . ':</label>';
	if (!isset($_POST['DecimalPlaces'])) {
		$_POST['DecimalPlaces'] = 2;
	}
	echo '<input class="integer" type="text" name="DecimalPlaces" size="2" required="required" minlength="1" maxlength="2" value="' . $_POST['DecimalPlaces'] . '" />
		</field>';

	echo '<field>
			<label for="ExchangeRate">' . __('Exchange Rate') . ':</label>';
	if (!isset($_POST['ExchangeRate'])) {
		$_POST['ExchangeRate'] = 1;
	}
	if ($_POST['Abbreviation'] != $FunctionalCurrency) {
		echo '<input class="number" maxlength="16" minlength="1" name="ExchangeRate" required="required" size="16" type="text" value="', $_POST['ExchangeRate'], '" />';
	} else {
		echo '<fieldtext>', $_POST['ExchangeRate'], '</fieldtext>',
			 '<input class="number" name="ExchangeRate" type="hidden" value="', $_POST['ExchangeRate'], '" />';
	}
	echo '</field>';
	if (!isset($_POST['webcart'])) {
		$_POST['webcart'] = 1;
	}

	echo '<field>
			<label for="webcart">' . __('Show in webSHOP') . ':</label>
			<select name="webcart">';

	if ($_POST['webcart']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['webcart']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}

	echo '</select>
		</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');

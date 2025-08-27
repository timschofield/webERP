<?php

// Displays the bank reconciliation for a selected bank account.

require(__DIR__ . '/includes/session.php');

$Title = __('Bank Reconciliation');
$ViewTopic = 'GeneralLedger';
$BookMark = 'BankAccounts';
include('includes/header.php');

include('includes/GLFunctions.php');

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/bank.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

if (isset($_GET['Account'])) {
	$_POST['BankAccount'] = $_GET['Account'];
	$_POST['ShowRec'] = true;
}

if (isset($_POST['BankStatementBalance'])) {
	$_POST['BankStatementBalance'] = filter_number_format($_POST['BankStatementBalance']);
}

if (isset($_POST['PostExchangeDifference']) AND is_numeric(filter_number_format($_POST['DoExchangeDifference']))) {

	if (!is_numeric($_POST['BankStatementBalance'])) {
		prnMsg(__('The entry in the bank statement balance is not numeric. The balance on the bank statement should be entered. The exchange difference has not been calculated and no general ledger journal has been created'), 'warn');
		echo '<br />' . $_POST['BankStatementBalance'];
	} else {

		/* Now need to get the currency of the account and the current table ex rate */
		$SQL = "SELECT rate,
						bankaccountname,
						decimalplaces AS currdecimalplaces
				FROM bankaccounts
				INNER JOIN currencies
					ON bankaccounts.currcode = currencies.currabrev
				WHERE bankaccounts.accountcode = '" . $_POST['BankAccount'] . "'";

		$ErrMsg = __('Could not retrieve the exchange rate for the selected bank account');
		$CurrencyResult = DB_query($SQL);
		$CurrencyRow = DB_fetch_array($CurrencyResult);

		$CalculatedBalance = filter_number_format($_POST['DoExchangeDifference']);

		$ExchangeDifference = ($CalculatedBalance - filter_number_format($_POST['BankStatementBalance'])) / $CurrencyRow['rate'];

		include('includes/SQL_CommonFunctions.php');
		$ExDiffTransNo = GetNextTransNo(36);
		/*Post the exchange difference to the last day of the month prior to current date*/
		$PostingDate = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), 0, Date('Y')));
		$PeriodNo = GetPeriod($PostingDate);
		DB_Txn_Begin();

		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
								  VALUES (36,
									'" . $ExDiffTransNo . "',
									'" . FormatDateForSQL($PostingDate) . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['CompanyRecord']['exchangediffact'] . "',
									'" . mb_substr($CurrencyRow['bankaccountname'] . ' ' . __('reconciliation on') . " " .
										Date($_SESSION['DefaultDateFormat']), 0, 200) . "',
									'" . $ExchangeDifference . "')";

		$ErrMsg = __('Cannot insert a GL entry for the exchange difference because');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
								  VALUES (36,
									'" . $ExDiffTransNo . "',
									'" . FormatDateForSQL($PostingDate) . "',
									'" . $PeriodNo . "',
									'" . $_POST['BankAccount'] . "',
									'" . mb_substr($CurrencyRow['bankaccountname'] . ' ' . __('reconciliation on') . ' ' . Date($_SESSION['DefaultDateFormat']), 0, 200) . "',
									'" . (-$ExchangeDifference) . "')";

		$Result = DB_query($SQL, $ErrMsg, '', true);

		DB_Txn_Commit();
		prnMsg(__('Exchange difference of') . ' ' . locale_number_format($ExchangeDifference, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . __('has been posted'), 'success');
	} //end if the bank statement balance was numeric
}

echo '<fieldset>
		<legend>', __('Select Accoutnt'), '</legend>
		<field>
			<label for="BankAccount">', __('Bank Account'), ':</label>
			<select name="BankAccount" tabindex="1">';

$SQL = "SELECT
			bankaccounts.accountcode,
			bankaccounts.bankaccountname,
			bankaccounts.currcode
		FROM bankaccounts,
			bankaccountusers
		WHERE bankaccounts.accountcode = bankaccountusers.accountcode
			AND bankaccountusers.userid = '" . $_SESSION['UserID'] . "'
		ORDER BY bankaccounts.bankaccountname";
$ErrMsg = __('The bank accounts could not be retrieved because');
$AccountsResults = DB_query($SQL, $ErrMsg);

if (DB_num_rows($AccountsResults) == 0) {
	echo '</select>
			</field>
		</fieldset>';
	prnMsg(__('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . __('define the bank accounts') . '</a> ' . __('and general ledger accounts to be affected'), 'warn');
	include('includes/footer.php');
	exit();
} else {
	while ($MyRow = DB_fetch_array($AccountsResults)) {

		// Lists bank accounts order by name
		echo '<option',
			((isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']) ? ' selected="selected"' : ''),
			' value="', $MyRow['accountcode'], '">', $MyRow['bankaccountname'], ' - ', $MyRow['currcode'], '</option>';

	}
	echo '</select>
		</field>';
}

echo '</fieldset>
	<div class="centre">
		<input type="submit" tabindex="2" name="ShowRec" value="' . __('Show bank reconciliation statement') . '" />
	</div>';


if (isset($_POST['ShowRec']) OR isset($_POST['DoExchangeDifference'])) {

	/*Get the balance of the bank account concerned */

	$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']));

	$Balance = GetGLAccountBalance($_POST['BankAccount'], $PeriodNo);

	/* Now need to get the currency of the account and the current table ex rate */
	$SQL = "SELECT rate,
					bankaccounts.currcode,
					bankaccounts.bankaccountname,
					currencies.decimalplaces AS currdecimalplaces
			FROM bankaccounts
			INNER JOIN currencies
				ON bankaccounts.currcode = currencies.currabrev
			WHERE bankaccounts.accountcode = '" . $_POST['BankAccount'] . "'";
	$ErrMsg = __('Could not retrieve the currency and exchange rate for the selected bank account');
	$CurrencyResult = DB_query($SQL);
	$CurrencyRow = DB_fetch_array($CurrencyResult);

	echo '<table class="selection">
			<tr class="total_row">
				<td colspan="6"><b>' . $CurrencyRow['bankaccountname'] . ' ' . __('Balance as at') . ' ' . Date($_SESSION['DefaultDateFormat']);

	if ($_SESSION['CompanyRecord']['currencydefault'] != $CurrencyRow['currcode']) {
		echo ' (' . $CurrencyRow['currcode'] . ' @ ' . $CurrencyRow['rate'] . ')';
	}
	echo '</b></td>
			<td valign="bottom" class="number"><b>' . locale_number_format($Balance * $CurrencyRow['rate'], $CurrencyRow['currdecimalplaces']) . '</b></td></tr>';

	$SQL = "SELECT amount / exrate AS amt,
					amountcleared,
					(amount / exrate) - amountcleared AS outstanding,
					ref,
					transdate,
					systypes.typename,
					transno
				FROM banktrans,
					systypes
				WHERE banktrans.type = systypes.typeid
					AND banktrans.bankact = '" . $_POST['BankAccount'] . "'
					AND amount < 0
					AND ABS((amount / exrate) - amountcleared) > 0.009 ORDER BY transdate";

	$ErrMsg = __('The unpresented cheques could not be retrieved by the SQL because');
	$UPChequesResult = DB_query($SQL, $ErrMsg);

	echo '<tr>
			<th colspan="6"><b>' . __('Add back unpresented cheques') . ':</b></th>
		</tr>
		<tr>
			<th>' . __('Date') . '</th>
			<th>' . __('Type') . '</th>
			<th>' . __('Number') . '</th>
			<th>' . __('Reference') . '</th>
			<th>' . __('Orig Amount') . '</th>
			<th>' . __('Outstanding') . '</th>
		</tr>';

	$TotalUnpresentedCheques = 0;

	while ($MyRow = DB_fetch_array($UPChequesResult)) {
		echo '<tr class="striped_row">
				<td>', ConvertSQLDate($MyRow['transdate']), '</td>
				<td>', $MyRow['typename'], '</td>
				<td>', $MyRow['transno'], '</td>
				<td>', $MyRow['ref'], '</td>
				<td class="number">', locale_number_format($MyRow['amt'], $CurrencyRow['currdecimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['outstanding'], $CurrencyRow['currdecimalplaces']), '</td>
				</tr>';

		$TotalUnpresentedCheques += $MyRow['outstanding'];
	}
	//end of while loop

	echo '<tr class="total_row">
			<td colspan="6">' . __('Total of all unpresented cheques') . '</td>
			<td class="number">' . locale_number_format($TotalUnpresentedCheques, $CurrencyRow['currdecimalplaces']) . '</td>
		</tr>';

	$SQL = "SELECT amount / exrate AS amt,
				amountcleared,
				(amount / exrate) - amountcleared AS outstanding,
				ref,
				transdate,
				systypes.typename,
				transno
			FROM banktrans
			INNER JOIN systypes
				ON banktrans.type = systypes.typeid
			WHERE banktrans.bankact = '" . $_POST['BankAccount'] . "'
				AND amount > 0
				AND ABS((amount / exrate) - amountcleared) > 0.009
			ORDER BY transdate";

	$ErrMsg = __('The uncleared deposits could not be retrieved by the SQL because');

	$UPChequesResult = DB_query($SQL, $ErrMsg);

	echo '<tr>
			<th colspan="6"><b>' . __('Less deposits not cleared') . ':</b></th>
		</tr>
		<tr>
			<th>' . __('Date') . '</th>
			<th>' . __('Type') . '</th>
			<th>' . __('Number') . '</th>
			<th>' . __('Reference') . '</th>
			<th>' . __('Orig Amount') . '</th>
			<th>' . __('Outstanding') . '</th>
		</tr>';

	$TotalUnclearedDeposits = 0;

	while ($MyRow = DB_fetch_array($UPChequesResult)) {
		echo '<tr class="striped_row">
				<td>', ConvertSQLDate($MyRow['transdate']), '</td>
				<td>', $MyRow['typename'], '</td>
				<td>', $MyRow['transno'], '</td>
				<td>', $MyRow['ref'], '</td>
				<td class="number">', locale_number_format($MyRow['amt'], $CurrencyRow['currdecimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['outstanding'], $CurrencyRow['currdecimalplaces']), '</td>
				</tr>';

		$TotalUnclearedDeposits += $MyRow['outstanding'];
	}
	//end of while loop
	echo '<tr class="total_row">
			<td colspan="6">' . __('Total of all uncleared deposits') . '</td>
			<td class="number">' . locale_number_format($TotalUnclearedDeposits, $CurrencyRow['currdecimalplaces']) . '</td>
		</tr>';
	$FXStatementBalance = ($Balance * $CurrencyRow['rate'] - $TotalUnpresentedCheques - $TotalUnclearedDeposits);
	echo '<tr class="total_row">
			<td colspan="6"><b>' . __('Bank statement balance should be') . ' (' . $CurrencyRow['currcode'] . ')</b></td>
			<td class="number">' . locale_number_format($FXStatementBalance, $CurrencyRow['currdecimalplaces']) . '</td>
		</tr>';

	if (isset($_POST['DoExchangeDifference'])) {
		echo '<input type="hidden" name="DoExchangeDifference" value="' . $FXStatementBalance . '" />';
		if (!isset($_POST['BankStatementBalance'])) {
			$_POST['BankStatementBalance'] = 0;
		}
		echo '<tr>
				<td colspan="6">' . __('Enter the actual bank statement balance') . ' (' . $CurrencyRow['currcode'] . ')</b></td>
				<td class="number"><input type="text" name="BankStatementBalance" class="number" autofocus="autofocus" required="required" maxlength="15" size="15" value="' . locale_number_format($_POST['BankStatementBalance'], $CurrencyRow['currdecimalplaces']) . '" /><td>
			</tr>
			<tr>
				<td colspan="7" align="center"><input type="submit" name="PostExchangeDifference" value="' . __('Calculate and Post Exchange Difference') . '" onclick="return confirm(\'' . __('This will create a general ledger journal to write off the exchange difference in the current balance of the account. It is important that the exchange rate above reflects the current value of the bank account currency') . ' - ' . __('Are You Sure?') . '\');" /></td>
			</tr>';
	}

	if ($_SESSION['CompanyRecord']['currencydefault'] != $CurrencyRow['currcode'] AND !isset($_POST['DoExchangeDifference'])) {

		echo '<tr>
				<td colspan="7"><hr /></td>
			</tr>
			<tr>
				<td colspan="7">' . __('It is normal for foreign currency accounts to have exchange differences that need to be reflected as the exchange rate varies. This reconciliation is prepared using the exchange rate set up in the currencies table (see the set-up tab). This table must be maintained with the current exchange rate before running the reconciliation. If you wish to create a journal to reflect the exchange difference based on the current exchange rate to correct the reconciliation to the actual bank statement balance click below.') . '</td>
			</tr>
			<tr>
				<td colspan="7" align="center"><input type="submit" name="DoExchangeDifference" value="' . __('Calculate and Post Exchange Difference') . '" /></td>
			</tr>';
	}
	echo '</table>';
}


if (isset($_POST['BankAccount'])) {
	echo '<div class="centre">
			<p>
			<a tabindex="4" href="' . $RootPath . '/BankMatching.php?Type=Payments&amp;Account=' . $_POST['BankAccount'] . '">' . __('Match off cleared payments') . '</a>
			</p>
			<a tabindex="5" href="' . $RootPath . '/BankMatching.php?Type=Receipts&amp;Account=' . $_POST['BankAccount'] . '">' . __('Match off cleared deposits') . '</a>
		</div>';
} else {
	echo '<div class="centre">
			<p>
			<a tabindex="4" href="' . $RootPath . '/BankMatching.php?Type=Payments">' . __('Match off cleared payments') . '</a>
			</p>
			<a tabindex="5" href="' . $RootPath . '/BankMatching.php?Type=Receipts">' . __('Match off cleared deposits') . '</a>
		</div>';
}
echo '</form>';
include('includes/footer.php');

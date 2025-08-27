<?php

// Shows bank accounts authorised for with balances

require(__DIR__ . '/includes/session.php');

$Title = __('List of bank account balances');
$ViewTopic = 'GeneralLedger';
$BookMark = 'BankAccountBalances';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/bank.png" title="', // Icon image.
	__('Bank Account Balances'), '" /> ', // Icon title.
	__('Bank Account Balances'), '</p>',// Page title.
	'<table>
		<tr>
			<th>', __('Bank Account'), '</th>
			<th>', __('Account Name'), '</th>
			<th>', __('Balance in account currency'), '</th>
			<th>', __('Balance in functional currency'), '</th>
		</tr>';

$SQL = "SELECT DISTINCT
			bankaccounts.accountcode,
			bankaccounts.bankaccountname,
			bankaccounts.currcode
		FROM bankaccounts
		INNER JOIN bankaccountusers
			ON bankaccounts.accountcode=bankaccountusers.accountcode
			AND userid='" . $_SESSION['UserID'] . "'
		ORDER BY bankaccounts.accountcode";
$ErrMsg = __('The bank accounts could not be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result) == 0) {
	echo __('There are no bank accounts defined that you have authority to see');
} else {
	while ($MyBankRow = DB_fetch_array($Result)) {
		$CurrBalanceSQL = "SELECT SUM(amount) AS balance
							FROM banktrans
							WHERE bankact='" . $MyBankRow['accountcode'] . "'";
		$CurrBalanceResult = DB_query($CurrBalanceSQL);
		$CurrBalanceRow = DB_fetch_array($CurrBalanceResult);

		$FuncBalanceSQL = "SELECT SUM(amount) AS balance
							FROM gltotals
							WHERE account='" . $MyBankRow['accountcode'] . "'";
		$FuncBalanceResult = DB_query($FuncBalanceSQL);
		$FuncBalanceRow = DB_fetch_array($FuncBalanceResult);

		$DecimalPlacesSQL = "SELECT decimalplaces
							FROM currencies
							WHERE currabrev='" . $MyBankRow['currcode'] . "'";
		$DecimalPlacesResult = DB_query($DecimalPlacesSQL);
		$DecimalPlacesRow = DB_fetch_array($DecimalPlacesResult);

		echo '<tr class="striped_row">
				<td>', $MyBankRow['accountcode'], '</td>
				<td>', $MyBankRow['bankaccountname'], '</td>
				<td class="number">', locale_number_format($CurrBalanceRow['balance'], $DecimalPlacesRow['decimalplaces']), ' ', $MyBankRow['currcode'], '</td>
				<td class="number">', locale_number_format($FuncBalanceRow['balance'], $_SESSION['CompanyRecord']['decimalplaces']), ' ', $_SESSION['CompanyRecord']['currencydefault'], '</td>
			</tr>';
	}

	echo '</table>';
}
include('includes/footer.php');

<?php

/* $Revision: 1.15 $ */

$PageSecurity = 7;

include ('includes/session.inc');

$title = _('Bank Reconciliation');

include('includes/header.inc');

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

if (isset($_POST['PostExchangeDifference']) and is_numeric($_POST['DoExchangeDifference'])){

	if (!is_numeric($_POST['BankStatmentBalance'])){
		prnMsg(_('The entry in the bank statement balance is not numeric. The balance on the bank statement should be entered. The exchange difference has not been calculated and no general ledger journal has been created'),'warn');
		echo '<p>' . $_POST['BankStatmentBalance'];
	} else {
		/* Now need to get the currency of the account and the current table ex rate */
		$SQL = 'SELECT rate, bankaccountname
						FROM bankaccounts INNER JOIN currencies
						ON bankaccounts.currcode=currencies.currabrev
				WHERE bankaccounts.accountcode = ' . $_POST['BankAccount'];

		$ErrMsg = _('Could not retrieve the exchange rate for the selected bank account');
		$CurrencyResult = DB_query($SQL,$db);
		$CurrencyRow =  DB_fetch_row($CurrencyResult);
		$ExRate = $CurrencyRow[0];
		$BankAccountName = $CurrencyRow[1];
		$CalculatedBalance = $_POST['DoExchangeDifference'];

		$ExchangeDifference = ($CalculatedBalance - $_POST['BankStatmentBalance'])*$ExRate;

		include ('includes/SQL_CommonFunctions.inc');
		$ExDiffTransNo = GetNextTransNo(36,$db);
		/*Post the exchange difference to the last day of the month prior to current date*/
		$PostingDate = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, Date('m'), 0,Date('Y')));
		$PeriodNo = GetPeriod($PostingDate,$db);
		$result = DB_Txn_Begin($db);

//yet to code the journal

		$SQL = 'INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
								  VALUES (36,
									' . $ExDiffTransNo . ",
									'" . FormatDateForSQL($PostingDate) . "',
									" . $PeriodNo . ',
									' . $_SESSION['CompanyRecord']['exchangediffact'] . ",
									'" . $BankAccountName . ' ' . _('reconciliation on') . ' ' . Date($_SESSION['DefaultDateFormat']) . "'," . $ExchangeDifference . ')';

		$ErrMsg = _('Cannot insert a GL entry for the exchange difference because');
		$DbgMsg = _('The SQL that failed to insert the exchange difference GL entry was');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		$SQL = 'INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
								  VALUES (36,
									' . $ExDiffTransNo . ",
									'" . FormatDateForSQL($PostingDate) . "',
									" . $PeriodNo . ',
									' . $_POST['BankAccount'] . ",
									'" . $BankAccountName . ' ' . _('reconciliation on') . ' ' . Date($_SESSION['DefaultDateFormat']) . "',
									" . (-$ExchangeDifference) . ')';

		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$result = DB_Txn_Commit($db);
		prnMsg(_('Exchange difference of') . ' ' . number_format($ExchangeDifference,2) . ' ' . _('has been posted'),'success');
	} //end if the bank statement balance was numeric
}



echo '<table>';

$SQL = 'SELECT bankaccountname, accountcode FROM bankaccounts';

$ErrMsg = _('The bank accounts could not be retrieved by the SQL because');
$DbgMsg = _('The SQL used to retrieve the bank accounts was');
$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

echo '<tr><td>' . _('Bank Account') . ':</td><td><select tabindex="1" name="BankAccount">';

if (DB_num_rows($AccountsResults)==0){
	echo '</select></td></tr></table><p>' . _('Bank Accounts have not yet been defined') . '. ' . _('You must first') . "<a href='" . $rootpath . "/BankAccounts.php'>" . _('define the bank accounts') . '</a>' . ' ' . _('and general ledger accounts to be affected') . '.';
	include('includes/footer.inc');
	exit;
} else {
	while ($myrow=DB_fetch_array($AccountsResults)){
		/*list the bank account names */
		if (isset($_POST['BankAccount']) and $_POST['BankAccount']==$myrow['accountcode']){
			echo '<option selected value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';
}

/*Now do the posting while the user is thinking about the bank account to select */

include ('includes/GLPostings.inc');

echo '</table><p><div class="centre"><input type=submit tabindex="2" name="ShowRec" value="' . _('Show bank reconciliation statement') . '"></div>';


if (isset($_POST['ShowRec']) OR isset($_POST['DoExchangeDifference'])){

/*Get the balance of the bank account concerned */

	$sql = 'SELECT MAX(period) FROM chartdetails WHERE accountcode=' . $_POST['BankAccount'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$LastPeriod = $myrow[0];

	$SQL = 'SELECT bfwd+actual AS balance
				FROM chartdetails WHERE period=' . $LastPeriod . ' AND accountcode=' . $_POST['BankAccount'];

	$ErrMsg = _('The bank account balance could not be returned by the SQL because');
	$BalanceResult = DB_query($SQL,$db,$ErrMsg);

	$myrow = DB_fetch_row($BalanceResult);
	$Balance = $myrow[0];

	/* Now need to get the currency of the account and the current table ex rate */
	$SQL = 'SELECT rate,
					bankaccounts.currcode,
					bankaccounts.bankaccountname
				FROM bankaccounts INNER JOIN currencies
				ON bankaccounts.currcode=currencies.currabrev
				WHERE bankaccounts.accountcode = ' . $_POST['BankAccount'];
	$ErrMsg = _('Could not retrieve the currency and exchange rate for the selected bank account');
	$CurrencyResult = DB_query($SQL,$db);
	$CurrencyRow =  DB_fetch_row($CurrencyResult);
	$ExRate = $CurrencyRow[0];
	$BankCurrCode = $CurrencyRow[1];
	$BankAccountName = $CurrencyRow[2];

	echo '<table>
			<tr><td colspan=6><b>' . $BankAccountName . ' ' . _('Balance as at') . ' ' . Date($_SESSION['DefaultDateFormat']);
	if ($_SESSION['CompanyRecord']['currencydefault']!=$BankCurrCode){
		echo  ' (' . $BankCurrCode . ' @ ' . $ExRate .')';
	}
	echo '</b></td>
			<td valign=bottom align=right><b>' . number_format($Balance/$ExRate,2) . '</b></td></tr>';

	$SQL = 'SELECT amount/exrate AS amt,
					amountcleared,
					(amount/exrate)-amountcleared as outstanding,
					ref,
					transdate,
					systypes.typename,
					transno
				FROM banktrans,
					systypes
				WHERE banktrans.type = systypes.typeid
				AND banktrans.bankact=' . $_POST['BankAccount'] . '
				AND amount < 0
				AND ABS((amount/exrate)-amountcleared)>0.009 ORDER BY transdate';

	echo '<tr></tr>'; /*Bang in a blank line */

	$ErrMsg = _('The unpresented cheques could not be retrieved by the SQL because');
	$UPChequesResult = DB_query($SQL, $db, $ErrMsg);

	echo '<tr><td colspan=6><b>' . _('Add back unpresented cheques') . ':</b></td></tr>';

	$TableHeader = '<tr>
			<th>' . _('Date') . '</th>
			<th>' . _('Type') . '</th>
			<th>' . _('Number') . '</th>
			<th>' . _('Reference') . '</th>
			<th>' . _('Orig Amount') . '</th>
			<th>' . _('Outstanding') . '</th>
			</tr>';

	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter
	$TotalUnpresentedCheques =0;

	while ($myrow=DB_fetch_array($UPChequesResult)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td align=right>%01.2f</td>
				<td align=right>%01.2f</td>
				</tr>',
				ConvertSQLDate($myrow['transdate']),
				$myrow['typename'],
				$myrow['transno'],
				$myrow['ref'],
				$myrow['amt'],
				$myrow['outstanding']);

		$TotalUnpresentedCheques +=$myrow['outstanding'];

		$j++;
		If ($j == 18){
			$j=1;
			echo $TableHeader;
		}
	}
	//end of while loop
	echo '<tr></tr>
			<tr><td colspan=6>' . _('Total of all unpresented cheques') . '</td><td align=right>' . number_format($TotalUnpresentedCheques,2) . '</td></tr>';

	$SQL = 'SELECT amount/exrate AS amt,
				amountcleared,
				(amount/exrate)-amountcleared as outstanding,
				ref,
				transdate,
				systypes.typename,
				transno
			FROM banktrans,
				systypes
			WHERE banktrans.type = systypes.typeid
			AND banktrans.bankact=' . $_POST['BankAccount'] . '
			AND amount > 0
			AND ABS((amount/exrate)-amountcleared)>0.009 ORDER BY transdate';

	echo '<tr></tr>'; /*Bang in a blank line */

	$ErrMsg = _('The uncleared deposits could not be retrieved by the SQL because');

	$UPChequesResult = DB_query($SQL,$db,$ErrMsg);

	echo '<tr><td colspan=6><b>' . _('Less deposits not cleared') . ':</b></td></tr>';

	$TableHeader = '<tr>
					<th>' . _('Date') . '</th>
					<th>' . _('Type') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Reference') . '</th>
					<th>' . _('Orig Amount') . '</th>
					<th>' . _('Outstanding') . '</th>
					</tr>';

	echo '<tr>' . $TableHeader;

	$j = 1;
	$k=0; //row colour counter
	$TotalUnclearedDeposits =0;

	while ($myrow=DB_fetch_array($UPChequesResult)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td align=right>%01.2f</td>
				<td align=right>%01.2f</td>
				</tr>',
				ConvertSQLDate($myrow['transdate']),
				$myrow['typename'],
				$myrow['transno'],
				$myrow['ref'],
				$myrow['amt'],
				$myrow['outstanding']
			);

		$TotalUnclearedDeposits +=$myrow['outstanding'];

		$j++;
		If ($j == 18){
			$j=1;
			echo $TableHeader;
		}
	}
	//end of while loop
	echo '<tr></tr><tr><td colspan=6>' . _('Total of all uncleared deposits') . '</td><td align=right>' . number_format($TotalUnclearedDeposits,2) . '</td></tr>';
	$FXStatementBalance = ($Balance/$ExRate) - $TotalUnpresentedCheques -$TotalUnclearedDeposits;
	echo '<tr></tr><tr><td colspan=6><b>' . _('Bank statement balance should be') . ' (' . $BankCurrCode . ')</b></td><td align=right>' . number_format($FXStatementBalance,2) . '</td></tr>';

	if (isset($_POST['DoExchangeDifference'])){
		echo '<input type="hidden" name="DoExchangeDifference" value=' . $FXStatementBalance . '>';
		echo '<tr><td colspan=6>' . _('Enter the actual bank statement balance') . ' (' . $BankCurrCode . ')</b></td>
				<td align=right><input type="text" name="BankStatmentBalance" maxlength=15 size=15 value=' . $_POST['BankStatmentBalance'] . '><td></tr>';
		echo '<tr><td colspan=7 align="center"><input type="submit" name="PostExchangeDifference" value="' . _('Calculate and Post Exchange Difference') . '" onclick="return confirm(\'' . _('This will create a general ledger journal to write off the exchange difference in the current balance of the account. It is important that the exchange rate above reflects the current value of the bank account currency') . ' - ' . _('Are You Sure?') . '\');"></td></tr>';

	}



	if ($_SESSION['CompanyRecord']['currencydefault']!=$BankCurrCode AND !isset($_POST['DoExchangeDifference'])){

		echo '<tr><td colspan=7><hr></td></tr>
				<tr><td colspan=7>' . _('It is normal for foreign currency accounts to have exchange differences that need to be reflected as the exchange rate varies. This reconciliation is prepared using the exchange rate set up in the currencies table (see the set-up tab). This table must be maintained with the current exchange rate before running the reconciliation. If you wish to create a journal to reflect the exchange difference based on the current exchange rate to correct the reconciliation to the actual bank statement balance click below.') . '</td></tr>';
		echo '<tr><td colspan=7 align="center"><input type=submit name="DoExchangeDifference" value="' . _('Calculate and Post Exchange Difference') . '"></td></tr>';
	}
	echo '</table>';
}

echo '<p><div class="centre"><a tabindex="4" href="' . $rootpath . '/BankMatching.php?' . SID . '&Type=Payments">' . _('Match off cleared payments') . '</a>';
echo '<br><a tabindex="5" href="' . $rootpath . '/BankMatching.php?' . SID . '&Type=Receipts">' . _('Match off cleared deposits') . '</a></div>';
echo '</form>';
include('includes/footer.inc');
?>
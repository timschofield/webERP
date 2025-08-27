<?php

// Shows the general ledger transactions for a specified account over a specified range of periods.

require(__DIR__ . '/includes/session.php');

$Title = __('General Ledger Account Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/transactions.png" title="', // Icon image.
__('General Ledger Account Inquiry') , '" /> ', // Icon title.
__('General Ledger Account Inquiry') , '</p>'; // Page title.

if (isset($_POST['Account'])) {
	$SelectedAccount = $_POST['Account'];
}
elseif (isset($_GET['Account'])) {
	$SelectedAccount = $_GET['Account'];
}

if (isset($_POST['Period'])) {
	$SelectedPeriod = $_POST['Period'];
}
elseif (isset($_GET['Period'])) {
	$SelectedPeriod = array(
		$_GET['Period']
	);
}

if (isset($_GET['Show'])) {
	$_POST['Show'] = $_GET['Show'];
}

/* Get the start and periods, depending on how this script was called*/
if (isset($SelectedPeriod)) { //If it was called from itself (in other words an inquiry was run and we wish to leave the periods selected unchanged
	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);
}
elseif (isset($_GET['PeriodTo'])) { //If it was called from the Trial Balance/P&L or Balance sheet, select the just last period
	$FirstPeriodSelected = $_GET['PeriodTo'];
	$LastPeriodSelected = $_GET['PeriodTo'];
	$SelectedPeriod[0] = $_GET['PeriodTo'];
	$SelectedPeriod[1] = $_GET['PeriodTo'];
}
else { // Otherwise just highlight the current period
	$FirstPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']));
	$LastPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']));
}

echo '<div class="page_help_text noPrint">' . __('Use the keyboard Shift key to select multiple periods') . '</div><br />';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<div class="noPrint">'; // Begin input of criteria div.
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m') , 0, Date('Y')));

/*Show a form to allow input of criteria for TB to show */
echo '<fieldset>
		<legend>', __('Inquiry Criteria') , '</legend>
		<field>
			<label for="Account">' . __('Account') . ':</label>
			<select name="Account">';

$SQL = "SELECT chartmaster.accountcode,
			bankaccounts.accountcode AS bankact,
			bankaccounts.currcode,
			chartmaster.accountname
		FROM chartmaster LEFT JOIN bankaccounts
		ON chartmaster.accountcode=bankaccounts.accountcode
		INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1
		ORDER BY chartmaster.accountcode";
$Account = DB_query($SQL);
while ($MyRow = DB_fetch_array($Account)) {
	if ($MyRow['accountcode'] == $SelectedAccount) {
		if (!is_null($MyRow['bankact'])) {
			$BankAccount = true;
		}
		echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
	else {
		echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
}
echo '</select>
	</field>';

//Select the tag
echo '<field>
		<label for="tag">' . __('Select Tag') . ':</label>
		<select name="tag">';

$SQL = "SELECT tagref,
			tagdescription
		FROM tags
		ORDER BY tagref";

$Result = DB_query($SQL);
echo '<option value="-1">-1 - ' . __('All tags') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref']) {
		echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	}
	else {
		echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	}
}
echo '</select>
	</field>';
// End select tag
echo '<field>
		<label for="Period">' . __('For Period range') . ':</label>
		<select name="Period[]" size="12" multiple="multiple">';

$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
$Periods = DB_query($SQL);
while ($MyRow = DB_fetch_array($Periods)) {
	if (isset($FirstPeriodSelected) AND $MyRow['periodno'] >= $FirstPeriodSelected AND $MyRow['periodno'] <= $LastPeriodSelected) {
		echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
	}
	else {
		echo '<option value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
	}
}
echo '</select>
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="Show" value="' . __('Show Account Transactions') . '" />
	</div>
	</div>', // End input of criteria div.
'</form>';

/* End of the Form  rest of script is what happens if the show button is hit*/

if (isset($_POST['Show'])) {

	if (!isset($SelectedPeriod)) {
		prnMsg(__('A period or range of periods must be selected from the list box') , 'info');
		include('includes/footer.php');
		exit();
	}
	/*Is the account a balance sheet or a profit and loss account */
	$Result = DB_query("SELECT pandl
				FROM accountgroups
				INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
				WHERE chartmaster.accountcode='" . $SelectedAccount . "'");
	$PandLRow = DB_fetch_row($Result);
	if ($PandLRow[0] == 1) {
		$PandLAccount = true;
	}
	else {
		$PandLAccount = false; /*its a balance sheet account */
	}

	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);

	$SQL = "SELECT gltrans.counterindex,
				type,
				typename,
				gltrans.typeno,
				trandate,
				narrative,
				amount,
				periodno,
				gltags.tagref,
				tags.tagdescription
			FROM gltrans
			INNER JOIN systypes
				ON systypes.typeid=gltrans.type
			LEFT JOIN gltags
				ON gltags.counterindex=gltrans.counterindex
			LEFT JOIN tags
				ON tags.tagref=gltags.tagref
			WHERE gltrans.account = '" . $SelectedAccount . "'
				AND periodno>='" . $FirstPeriodSelected . "'
				AND periodno<='" . $LastPeriodSelected . "'";

	if (isset($_POST['tag']) and $_POST['tag'] != -1) {
		$SQL = $SQL . " AND gltags.tagref='" . $_POST['tag'] . "'";
	}

	$SQL = $SQL . " ORDER BY periodno,
						gltrans.trandate,
						counterindex";

	$NameSQL = "SELECT accountname FROM chartmaster WHERE accountcode='" . $SelectedAccount . "'";
	$NameResult = DB_query($NameSQL);
	$NameRow = DB_fetch_array($NameResult);
	$SelectedAccountName = $NameRow['accountname'];
	$ErrMsg = __('The transactions for account') . ' ' . $SelectedAccount . ' ' . __('could not be retrieved because');
	$TransResult = DB_query($SQL, $ErrMsg);
	$BankAccountInfo = isset($BankAccount) ? '<th>' . __('Org Currency') . '</th>
							<th>' . __('Amount in Org Currency') . '</th>
							<th>' . __('Bank Ref') . '</th>' : '';
	echo '<br />
		<table class="selection">
		<thead>
			<tr>
				<th colspan="11"><b>', __('Transactions for account') , ' ', $SelectedAccount, ' - ', $SelectedAccountName, '</b></th>
			</tr>
			<tr>
				<th>', __('Type') , '</th>
				<th>', __('Number') , '</th>
				<th>', ('Date') , '</th>
				<th>', __('Narrative') , '</th>
				<th>', __('Debit') , '</th>
				<th>', __('Credit') , '</th>
				<th>', __('Balance') , '</th>
				<th>', __('Tag') , '</th>', $BankAccountInfo, '
			</tr>
		</thead><tbody>';

	if ($PandLAccount == true) {
		$RunningTotal = 0;
	}
	else {
		$SQL = "SELECT SUM(amount) AS bfwdamount
				FROM gltotals
				WHERE gltotals.account = '" . $SelectedAccount . "'
				AND gltotals.period < '" . $FirstPeriodSelected . "'";
		$ErrMsg = __('Could not retrieve the brought forward balance for account') . ' ' . $SelectedAccount;
		$BfwdResult = DB_query($SQL, $ErrMsg);
		$BfwdRow = DB_fetch_array($BfwdResult);
		$RunningTotal = $BfwdRow['bfwdamount'];
		if (is_null($RunningTotal)) {
			$RunningTotal = 0;
		}
		echo '<tr>
					<td colspan="4"><b>', __('Brought Forward Balance') , '</b></td>';
		if ($RunningTotal < 0) { // It is a credit balance b/fwd
			echo '<td>&nbsp;</td>
					<td class="number"><b>', locale_number_format(-$RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) , '</b></td>';
		}
		else { // It is a debit balance b/fwd
			echo '<td class="number"><b>', locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) , '</b></td>
					<td>&nbsp;</td>';
		}
		echo '<td colspan="5">&nbsp;</td>
				</tr>';
	}
	$PeriodTotal = 0;
	$PeriodNo = - 9999;
	$ShowIntegrityReport = false;
	$j = 1;
	$IntegrityReport = '';
	while ($MyRow = DB_fetch_array($TransResult)) {
		if ($MyRow['periodno'] != $PeriodNo) {
			if ($PeriodNo != - 9999) { //ie its not the first time around
				$SQL = "SELECT amount
						FROM gltotals
						WHERE gltotals.account = '" . $SelectedAccount . "'
						AND gltotals.period = '" . $PeriodNo . "'";

				$ErrMsg = __('Could not retrieve the GL total for account') . ' ' . $SelectedAccount . ' ' . __('and period') . ' ' . $PeriodNo;
				$GLTotalResult = DB_query($SQL, $ErrMsg);
				if (DB_num_rows($GLTotalResult) == 0) {
					$PeriodActual = 0; // No GL total record, assume zero movement
				} else {
					$GLTotalRow = DB_fetch_array($GLTotalResult);
					$PeriodActual = $GLTotalRow['amount'];
					if (is_null($PeriodActual)) {
						$PeriodActual = 0;
					}
				}

				echo '<tr>
					<td colspan="4"><b>' . __('Total for period') . ' ' . $PeriodNo . '</b></td>';
				if ($PandLAccount == true) {
					$RunningTotal = 0;
				}
				if ($PeriodTotal < 0) { // It is a credit balance b/fwd
					echo '<td>&nbsp;</td>
							<td class="number"><b>', locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) , '</b></td>';
				}
				else { // It is a debit balance b/fwd
					echo '<td class="number"><b>', locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) , '</b></td>
							<td>&nbsp;</td>';
				}
				echo '<td colspan="5">&nbsp;</td>
						</tr>';
				$IntegrityReport .= '<br />' . __('Period') . ': ' . $PeriodNo . __('Account movement per transaction') . ': ' . locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . __('Movement per GL Totals record') . ': ' . locale_number_format($PeriodActual, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . __('Period difference') . ': ' . locale_number_format($PeriodTotal - $PeriodActual, 3);

				if (ABS($PeriodTotal - $PeriodActual) > 0.01) {
					$ShowIntegrityReport = true;
				}
			}
			$PeriodNo = $MyRow['periodno'];
			$PeriodTotal = 0;
		}

		$BankRef = '';
		$OrgAmt = '';
		$Currency = '';
		if ($MyRow['type'] == 12 OR $MyRow['type'] == 22 OR $MyRow['type'] == 2 OR $MyRow['type'] == 1) {
			$BankSQL = "SELECT ref,currcode,amount FROM banktrans
				WHERE type='" . $MyRow['type'] . "' AND transno='" . $MyRow['typeno'] . "' AND bankact='" . $SelectedAccount . "'";
			$ErrMsg = __('Failed to retrieve bank data');
			$BankResult = DB_query($BankSQL, $ErrMsg);
			if (DB_num_rows($BankResult) > 0) {
				$BankRow = DB_fetch_array($BankResult);
				$BankRef = $BankRow['ref'];
				$OrgAmt = $BankRow['amount'];
				$Currency = $BankRow['currcode'];
			}
			elseif ($MyRow['type'] == 1) {
				//We should find out when transaction happens between bank accounts;
				$BankReceiveSQL = "SELECT ref,type,transno,currcode,amount FROM banktrans
							WHERE ref LIKE '@%' AND transdate='" . $MyRow['trandate'] . "' AND bankact='" . $SelectedAccount . "'";
				$ErrMsg = __('Failed to retrieve bank receive data');
				$BankResult = DB_query($BankReceiveSQL, $ErrMsg);
				if (DB_num_rows($BankResult) > 0) {
					while ($BankRow = DB_fetch_array($BankResult)) {
						if (substr($BankRow['ref'], 1, strpos($BankRow['ref'], ' ') - 1) == $MyRow['typeno']) {
							$BankRef = $BankRow['ref'];
							$OrgAmt = $BankRow['amount'];
							$Currency = $BankRow['currcode'];
							$BankReceipt = true;
							break;
						}
					}
				}
				if (!isset($BankReceipt)) {
					$BankRef = '';
					$OrgAmt = $MyRow['amount'];
					$Currency = $_SESSION['CompanyRecord']['currencydefault'];
				}

			}
			elseif (isset($BankAccount)) {
				$BankRef = '';
				$OrgAmt = $MyRow['amount'];
				$Currency = $_SESSION['CompanyRecord']['currencydefault'];
			}
		}

		$URL_to_TransDetail = $RootPath . '/GLTransInquiry.php?TypeID=' . urlencode($MyRow['type']) . '&amp;TransNo=' . urlencode($MyRow['typeno']);
		$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);
		if ($MyRow['amount'] >= 0) {
			$DebitAmount = locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
			$CreditAmount = '';
		}
		else {
			$CreditAmount = locale_number_format(-$MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
			$DebitAmount = '';
		}
		$RunningTotal += $MyRow['amount'];
		$PeriodTotal += $MyRow['amount'];
		echo '<tr class="striped_row">
				<td class="text">', __($MyRow['typename']) , '</td>
				<td class="number"><a href="', $URL_to_TransDetail, '">', $MyRow['typeno'], '</a></td>
				<td class="centre">', $FormatedTranDate, '</td>
				<td class="text">', $MyRow['narrative'], '</td>
				<td class="number">', $DebitAmount, '</td>
				<td class="number">', $CreditAmount, '</td>
				<td class="number">', locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) , '</td>
				<td class="text">', $MyRow['tagdescription'], '</td>';
		if (isset($BankAccount)) {
			echo '<td class="text">', $Currency, '</td>
				<td class="number"><b>', locale_number_format($OrgAmt, $_SESSION['CompanyRecord']['decimalplaces']) , '</b></td>
				<td class="text">', $BankRef, '</td>';
		}
		echo '</tr>';
	}

	echo '<tr>
			<td colspan="4"><b>';
	if ($PandLAccount == true) {
		echo __('Total Period Movement'); /* RChacon: "Total for period XX"? */
	}
	else { /*its a balance sheet account*/
		echo __('Balance C/Fwd');
	}
	echo '</b></td>';
	if ($RunningTotal < 0) { // It is a debit Total Period Movement or Balance C/Fwd
		echo '<td>&nbsp;</td>
				<td class="number"><b>', locale_number_format((-$RunningTotal) , $_SESSION['CompanyRecord']['decimalplaces']) , '</b></td>';
	}
	else { // It is a credit Total Period Movement or Balance C/Fwd
		echo '<td class="number"><b>', locale_number_format(($RunningTotal) , $_SESSION['CompanyRecord']['decimalplaces']) , '</b></td>
				<td>&nbsp;</td>';
	}
	echo '<td colspan="5">&nbsp;</td>
		</tr>
		</tbody></table>';
} /* end of if Show button hit */

if (isset($ShowIntegrityReport) AND $ShowIntegrityReport == true AND $_POST['tag'] == '0') {
	if (!isset($IntegrityReport)) {
		$IntegrityReport = '';
	}
	prnMsg(__('There are differences between the sum of the transactions and the recorded movements in the GL Totals table') . '. ' . __('A log of the account differences for the periods report shows below') , 'warn');
	echo '<p>' . $IntegrityReport;
}
include('includes/footer.php');

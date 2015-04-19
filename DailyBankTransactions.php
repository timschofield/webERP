<?php
/* $Id: DailyBankTransactions.php 4556 2011-04-26 11:03:36Z daintree $*/
/* Allows you to view all bank transactions for a selected date range, and the inquiry can be filtered by matched or unmatched transactions, or all transactions can be chosen. */

include('includes/session.inc');
$Title = _('Daily Bank Transactions');// Screen identification.
$ViewTopic = 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'DailyBankTransactions';// Anchor's id in the manual's html document.
include('includes/header.inc');

if (!isset($_POST['Show'])) {
	$SQL = "SELECT 	bankaccountname,
					bankaccounts.accountcode,
					bankaccounts.currcode
			FROM bankaccounts,
				chartmaster,
				bankaccountusers
			WHERE bankaccounts.accountcode=chartmaster.accountcode
				AND bankaccounts.accountcode=bankaccountusers.accountcode
			AND bankaccountusers.userid = '" . $_SESSION['UserID'] ."'";

	$ErrMsg = _('The bank accounts could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank accounts was');
	$AccountsResults = DB_query($SQL,$ErrMsg,$DbgMsg);

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/bank.png" title="' .// Icon image.
		_('Bank Transactions Inquiry') . '" /> ' .// Icon title.
		_('Bank Transactions Inquiry') . '</p>';// Page title.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';
	echo '<tr>
			<td>' . _('Bank Account') . ':</td>
			<td><select name="BankAccount">';

	if (DB_num_rows($AccountsResults)==0){
		echo '</select></td>
				</tr></table>';
		prnMsg( _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		while ($myrow=DB_fetch_array($AccountsResults)){
		/*list the bank account names */
			if (!isset($_POST['BankAccount']) AND $myrow['currcode']==$_SESSION['CompanyRecord']['currencydefault']){
				$_POST['BankAccount']=$myrow['accountcode'];
			}
			if ($_POST['BankAccount']==$myrow['accountcode']){
				echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'] . '</option>';
			} else {
				echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	}
	echo '<tr>
			<td>' . _('Transactions Dated From') . ':</td>
			<td><input type="text" name="FromTransDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" required="required" maxlength="10" size="11" onchange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' .
				date($_SESSION['DefaultDateFormat']) . '" /></td>
		</tr>
		<tr>
			<td>' . _('Transactions Dated To') . ':</td>
			<td><input type="text" name="ToTransDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'"  required="required" maxlength="10" size="11" onchange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' . date($_SESSION['DefaultDateFormat']) . '" /></td>
		</tr>
		<tr>
			<td>' . _('Show Transactions') . '</td>
			<td><select name="ShowType">
				<option value="All">' . _('All') . '</option>
				<option value="Unmatched">' . _('Unmatched') . '</option>
				<option value="Matched">' . _('Matched') . '</option>
			</select></td>
			</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="Show" value="' . _('Show transactions'). '" />
		</div>
        </div>
		</form>';
} else {
	$SQL = "SELECT 	bankaccountname,
					bankaccounts.currcode,
					currencies.decimalplaces
			FROM bankaccounts
			INNER JOIN currencies
				ON bankaccounts.currcode = currencies.currabrev
			WHERE bankaccounts.accountcode='" . $_POST['BankAccount'] . "'";
	$BankResult = DB_query($SQL,_('Could not retrieve the bank account details'));

	$sql="SELECT 	banktrans.currcode,
					banktrans.amount,
					banktrans.amountcleared,
					banktrans.functionalexrate,
					banktrans.exrate,
					banktrans.banktranstype,
					banktrans.transdate,
					banktrans.transno,
					banktrans.ref,
					bankaccounts.bankaccountname,
					systypes.typename,
					systypes.typeid
				FROM banktrans
				INNER JOIN bankaccounts
				ON banktrans.bankact=bankaccounts.accountcode
				INNER JOIN systypes
				ON banktrans.type=systypes.typeid
				WHERE bankact='".$_POST['BankAccount']."'
					AND transdate>='" . FormatDateForSQL($_POST['FromTransDate']) . "'
					AND transdate<='" . FormatDateForSQL($_POST['ToTransDate']) . "'
				ORDER BY banktrans.transdate ASC, banktrans.banktransid ASC";
	$result = DB_query($sql);

	if (DB_num_rows($result)==0) {
		echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
			'/images/bank.png" title="' .// Icon image.
			_('Bank Transactions Inquiry') . '" /> ' .// Icon title.
			_('Bank Transactions Inquiry') . '</p>';// Page title.
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	} else {
		$BankDetailRow = DB_fetch_array($BankResult);
		echo '<div id="Report">';// Division to identify the report block.
		echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
			'/images/bank.png" title="' .// Icon image.
			_('Bank Transactions Inquiry') . '" /> ' .// Icon title.
			_('Account Transactions For').'<br />'.$BankDetailRow['bankaccountname'].'<br />'.
			_('Between').' '.$_POST['FromTransDate'] . ' ' . _('and') . ' ' . $_POST['ToTransDate'] . '</p>';// Page title.*/
		echo '<table class="selection">
			<thead>
				<tr>
					<th>' . ('Date') . '</th>
					<th class="text">' . _('Transaction type') . '</th>
					<th class="number">' . _('Number') . '</th>
					<th class="text">' . _('Type') . '</th>
					<th class="text">' . _('Reference') . '</th>
					<th class="number">' . _('Amount in').' '.$BankDetailRow['currcode'] . '</th>
					<th class="number">' . _('Running Total').' '.$BankDetailRow['currcode'] . '</th>
					<th class="number">' . _('Amount in').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
					<th class="number">' . _('Running Total').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
					<th class="number">' . _('Cleared') . '</th>
				</tr>
			</thead><tbody>';

		$AccountCurrTotal=0;
		$LocalCurrTotal =0;

		while ($myrow = DB_fetch_array($result)){

			$AccountCurrTotal += $myrow['amount'];
			$LocalCurrTotal += $myrow['amount']/$myrow['functionalexrate']/$myrow['exrate'];

			if ($myrow['amount']==$myrow['amountcleared']) {
				$Matched=_('Yes');
			} else {
				$Matched=_('No');
			}

			echo '<tr>
					<td class="centre">' .  ConvertSQLDate($myrow['transdate']) . '</td>
					<td>' . _($myrow['typename']) . '</td>
					<td class="number"><a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $myrow['typeid'] . '&amp;TransNo=' . $myrow['transno'] . '">' . $myrow['transno'] . '</a></td>
					<td>' . $myrow['banktranstype'] . '</td>
					<td>' . $myrow['ref'] . '</td>
					<td class="number">' . locale_number_format($myrow['amount'],$BankDetailRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($AccountCurrTotal,$BankDetailRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($myrow['amount']/$myrow['functionalexrate']/$myrow['exrate'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($LocalCurrTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . $Matched . '</td>
				</tr>';
		}
		echo '</tbody></table>';
		echo '</div>';// div id="Report".
	} //end if no bank trans in the range to show

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<br />
			<div class="centre noprint">'.
				'<button onclick="javascript:window.print()" type="button"><img alt="" src="'.$RootPath.'/css/'.$Theme.
					'/images/printer.png" /> ' .
					_('Print This') . '</button>'.// "Print This" button.
				'<button name="SelectADifferentPeriod" type="submit" value="'. _('Select A Different Period') .'"><img alt="" src="'.$RootPath.'/css/'.$Theme.
					'/images/gl.png" /> ' .
					_('Select Another Date') . '</button>'.// "Select A Different Period" button.
				'<button formaction="index.php?Application=GL" type="submit"><img alt="" src="'.$RootPath.'/css/'.$Theme.
					'/images/previous.png" /> ' .
					_('Return') . '</button>'.// "Return" button.
			'</div>
		</form>';
}
include('includes/footer.inc');
?>
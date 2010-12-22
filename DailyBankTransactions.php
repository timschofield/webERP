<?php

//$PageSecurity = 8;
include ('includes/session.inc');
$title = _('Daily Banking Inquiry');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' .
	 _('Search') . '" alt="">' . ' ' . $title.'</p>';

if (!isset($_POST['Show'])) {
	echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class=selection>';

	$SQL = 'SELECT bankaccountname,
				bankaccounts.accountcode,
				bankaccounts.currcode
			FROM bankaccounts,
				chartmaster
			WHERE bankaccounts.accountcode=chartmaster.accountcode';

	$ErrMsg = _('The bank accounts could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank accounts was');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	echo '<tr><td>' . _('Bank Account') . ':</td><td><select name="BankAccount">';

	if (DB_num_rows($AccountsResults)==0){
		echo '</select></td></tr></table><p>';
		prnMsg( _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		while ($myrow=DB_fetch_array($AccountsResults)){
		/*list the bank account names */
			if (!isset($_POST['BankAccount']) AND $myrow['currcode']==$_SESSION['CompanyRecord']['currencydefault']){
				$_POST['BankAccount']=$myrow['accountcode'];
			}
			if ($_POST['BankAccount']==$myrow['accountcode']){
				echo '<option selected value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
			} else {
				echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
			}
		}
		echo '</select></td></tr>';
	}
	echo '<tr><td>' . _('Transactions Dated') . ':</td>
		<td><input type="text" name="TransDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" maxlength=10 size=11
			onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' .
				date($_SESSION['DefaultDateFormat']) . '"></td>
		</tr>';

	echo '</table>';
	echo '<br><div class="centre"><input type="submit" name="Show" value="' . _('Show transactions'). '"></div>';
	echo '</form>';
} else {
	$sql="SELECT banktrans.*,
				bankaccounts.bankaccountname,
				systypes.typename,
				systypes.typeid
			FROM banktrans
			LEFT JOIN bankaccounts
			ON banktrans.bankact=bankaccounts.accountcode
			LEFT JOIN systypes
			ON banktrans.type=systypes.typeid
			WHERE bankact='".$_POST['BankAccount']."'
				AND transdate='".FormatDateForSQL($_POST['TransDate'])."'";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)>0) {
		$myrow = DB_fetch_array($result);
		echo '<table class=selection>';
		echo '<tr><th colspan=7><font size=3 color=blue>';
		echo _('Account Transactions For').' '.$myrow['bankaccountname'].' '._('On').' '.$_POST['TransDate'];
		echo '</font></th></tr>';
		echo '<tr>';
		echo '<th>'._('Transaction type').'</th>';
		echo '<th>'._('Type').'</th>';
		echo '<th>'._('Reference').'</th>';
		echo '<th>'._('Amount in').' '.$myrow['currcode'].'</th>';
		echo '<th>'._('Running Total').' '.$myrow['currcode'].'</th>';
		echo '<th>'._('Amount in').' '.$_SESSION['CompanyRecord']['currencydefault'].'</th>';
		echo '<th>'._('Running Total').' '.$_SESSION['CompanyRecord']['currencydefault'].'</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>'.$myrow['typename'].'</td>';
		echo '<td>'.$myrow['banktranstype'].'</td>';
		echo '<td>'.$myrow['ref'].'</td>';
		echo '<td class=number>'.number_format($myrow['amount'],2).'</td>';
		echo '<td class=number>'.number_format($myrow['amount'],2).'</td>';
		echo '<td class=number>'.number_format($myrow['amount']/$myrow['functionalexrate']/$myrow['exrate'],2).'</td>';
		echo '<td class=number>'.number_format($myrow['amount']/$myrow['functionalexrate']/$myrow['exrate'],2).'</td>';
		echo '</tr>';
		$AccountCurrTotal = $myrow['amount'];
		$LocalCurrTotal = $myrow['amount']/$myrow['functionalexrate'];
		while ($myrow=DB_fetch_array($result)) {
			$AccountCurrTotal += $myrow['amount'];
			$LocalCurrTotal += $myrow['amount']/$myrow['functionalexrate'];
			echo '<tr>';
			echo '<td>'.$myrow['typename'].'</td>';
			echo '<td>'.$myrow['banktranstype'].'</td>';
			echo '<td>'.$myrow['ref'].'</td>';
			echo '<td class=number>'.number_format($myrow['amount'],2).'</td>';
			echo '<td class=number>'.number_format($AccountCurrTotal,2).'</td>';
			echo '<td class=number>'.number_format($myrow['amount']/$myrow['functionalexrate'],2).'</td>';
			echo '<td class=number>'.number_format($LocalCurrTotal,2).'</td>';
			echo '</tr>';
		}
		echo '</table>';
	} else {
		prnMsg( _('There are no transactions for this account on that day'), 'info');
	}
	echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br><div class="centre"><input type="submit" name="Return" value="' . _('Select Another Date'). '"></div>';
	echo '</form>';
}
include('includes/footer.inc');

?>
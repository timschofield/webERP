<?php
/* $Revision: 1.23 $ */

$PageSecurity = 7;

include("includes/session.inc");
$title = _('Bank Account Matching');
include('includes/header.inc');

if ((isset($_GET["Type"]) and $_GET["Type"]=='Receipts') OR (isset($_POST["Type"]) and $_POST["Type"]=='Receipts')){
	$Type = 'Receipts';
	$TypeName =_('Receipts');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Bank Matching') . '" alt="">' . ' ' . _('Bank Account Matching - Receipts') . '</p>';
} elseif ((isset($_GET["Type"]) and $_GET["Type"]=='Payments') OR (isset($_POST["Type"]) and $_POST["Type"]=='Payments')) {
	$Type = 'Payments';
	$TypeName =_('Payments');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_delete.png" title="' . _('Bank Matching') . '" alt="">' . ' ' . _('Bank Account Matching - Payments') . '</p>';
} else {
	prnMsg(_('This page must be called with a bank transaction type') . '. ' . _('It should not be called directly'),'error');
	include ('includes/footer.inc');
	exit;
}

if (isset($_POST['Update']) AND $_POST['RowCounter']>1){
	for ($Counter=1;$Counter <= $_POST['RowCounter']; $Counter++){
		if (isset($_POST["Clear_" . $Counter]) and $_POST["Clear_" . $Counter]==True){
			/*Get amount to be cleared */
			$sql = "SELECT amount, 
						exrate 
					FROM banktrans
					WHERE banktransid=" . $_POST["BankTrans_" . $Counter];
			$ErrMsg =  _('Could not retrieve transaction information');
			$result = DB_query($sql,$db,$ErrMsg);
			$myrow=DB_fetch_array($result);
			$AmountCleared = round($myrow[0] / $myrow[1],2);
			/*Update the banktrans recoord to match it off */
			$sql = "UPDATE banktrans SET amountcleared= ". $AmountCleared .
					" WHERE banktransid=" . $_POST["BankTrans_" . $Counter];
			$ErrMsg =  _('Could not match off this payment because');
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["AmtClear_" . $Counter]) and is_numeric((float) $_POST["AmtClear_" . $Counter]) AND 
			((isset($_POST["AmtClear_" . $Counter]) and $_POST["AmtClear_" . $Counter]<0 AND $Type=='Payments') OR 
			($Type=='Receipts' AND (isset($_POST["AmtClear_" . $Counter]) and $_POST["AmtClear_" . $Counter]>0)))){
			/*if the amount entered was numeric and negative for a payment or positive for a receipt */
			$sql = "UPDATE banktrans SET amountcleared=" .  $_POST["AmtClear_" . $Counter] . "
					 WHERE banktransid=" . $_POST["BankTrans_" . $Counter];

			$ErrMsg = _('Could not update the amount matched off this bank transaction because');
			$result = DB_query($sql,$db,$ErrMsg);

		} elseif (isset($_POST["Unclear_" . $Counter]) and $_POST["Unclear_" . $Counter]==True){
			$sql = "UPDATE banktrans SET amountcleared = 0
					 WHERE banktransid=" . $_POST["BankTrans_" . $Counter];
			$ErrMsg =  _('Could not unclear this bank transaction because');
			$result = DB_query($sql,$db,$ErrMsg);
		}
	}
	/*Show the updated position with the same criteria as previously entered*/
	$_POST["ShowTransactions"] = True;
}

echo '<div class="page_help_text">' . _('Use this screen to match webERP Receipts and Payments to your Bank Statement.  Check your bank statement and click the check-box when you find the matching transaction.') . '</div><br>';

echo "<form action='". $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";

echo "<input type=hidden Name=Type Value=$Type>";

echo '<table><tr>';
echo '<td align=left>' . _('Bank Account') . ':</td><td colspan=3><select tabindex="1" name="BankAccount">';

$sql = "SELECT accountcode, bankaccountname FROM bankaccounts";
$resultBankActs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultBankActs)){
	if (isset($_POST['BankAccount']) and $myrow["accountcode"]==$_POST['BankAccount']){
		echo "<option selected Value='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
	} else {
		echo "<option Value='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
	}
}

echo '</select></td></tr>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
	$_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
	$_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date("m")-3,Date("d"),Date("y")));
}

// Change to allow input of FROM DATE and then TO DATE, instead of previous back-to-front method, add datepicker
echo '<tr><td>' . _('Show') . ' ' . $TypeName . ' ' . _('from') . ':</td>
		<td><input tabindex="3" type="text" name="AfterDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" size="12" maxlength="10" onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" Value="' . $_POST['AfterDate'] . '"></td></tr>';

echo '<td>' . _('to') . ':</td>
	<td><input tabindex="2" type="text" name="BeforeDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" size="12" maxlength="10" onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" Value="' . $_POST['BeforeDate'] . '"></td>';
echo '<tr><td colspan=3>' . _('Choose outstanding') . ' ' . $TypeName . ' ' . _('only or all') . ' ' . $TypeName . ' ' . _('in the date range') . ':</td>
	<td><select tabindex="4" name="Ostg_or_All">';

if ($_POST["Ostg_or_All"]=='All'){
	echo '<option selected Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
	echo '<option Value="Ostdg">' . _('Show unmatched') . ' ' . $TypeName . ' ' . _('only');
} else {
	echo '<option Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
	echo '<option selected Value="Ostdg">' . _('Show unmatched') . ' ' . $TypeName . ' ' . _('only');
}
echo '</select></td></tr>';

echo '<tr><td colspan=3>' . _('Choose to display only the first 20 matching') . ' ' . $TypeName . ' ' .
	_('or all') . ' ' . $TypeName . ' ' . _('meeting the criteria') . ':</td><td><select tabindex="5" name="First20_or_All">';
if ($_POST["First20_or_All"]=='All'){
	echo '<option selected Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
	echo '<option Value="First20">' . _('Show only the first 20') . ' ' . $TypeName;
} else {
	echo '<option Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
	echo '<option selected Value="First20">' . _('Show only the first 20') . ' ' . $TypeName;
}
echo '</select></td></tr>';


echo '</table><div class="centre"><input tabindex="6" type=submit name="ShowTransactions" VALUE="' . _('Show selected') . ' ' . $TypeName . '">';
echo "<p><a href='$rootpath/BankReconciliation.php?" . SID . "'>" . _('Show reconciliation') . '</a></div>';
echo '<hr>';

$InputError=0;
if (!Is_Date($_POST['BeforeDate'])){
	$InputError =1;
	prnMsg(_('The date entered for the field to show') . ' ' . $TypeName . ' ' . _('before') . ', ' .
		_('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' .
		$_SESSION['DefaultDateFormat'],'error');
}
if (!Is_Date($_POST['AfterDate'])){
	$InputError =1;
	prnMsg( _('The date entered for the field to show') . ' ' . $Type . ' ' . _('after') . ', ' . 
		_('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' .
		$_SESSION['DefaultDateFormat'],'error');
}

if ($InputError !=1 AND isset($_POST["BankAccount"]) AND $_POST["BankAccount"]!="" AND isset($_POST["ShowTransactions"])){

	$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
	$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

	if ($_POST["Ostg_or_All"]=='All'){
		if ($Type=='Payments'){
			$sql = "SELECT banktransid,
					ref,
					amountcleared,
					transdate,
					amount/exrate as amt,
					banktranstype
				FROM banktrans
				WHERE amount <0
				AND transdate >= '". $SQLAfterDate . "'
				AND transdate <= '" . $SQLBeforeDate . "'
				AND bankact=" .$_POST["BankAccount"] . "
				ORDER BY transdate";

		} else { /* Type must == Receipts */
			$sql = "SELECT banktransid,
					ref,
					amountcleared,
					transdate,
					amount/exrate as amt,
					banktranstype
				FROM banktrans
				WHERE amount >0
				AND transdate >= '". $SQLAfterDate . "'
				AND transdate <= '" . $SQLBeforeDate . "'
				AND bankact=" .$_POST['BankAccount'] . "
				ORDER BY transdate";
		}
	} else { /*it must be only the outstanding bank trans required */
		if ($Type=='Payments'){
			$sql = "SELECT banktransid,
					ref,
					amountcleared,
					transdate,
					amount/exrate as amt,
					banktranstype
				FROM banktrans
				WHERE amount <0
				AND transdate >= '". $SQLAfterDate . "'
				AND transdate <= '" . $SQLBeforeDate . "'
				AND bankact=" .$_POST["BankAccount"] . "
				AND  ABS(amountcleared - (amount / exrate)) > 0.009
				ORDER BY transdate";
		} else { /* Type must == Receipts */
			$sql = "SELECT banktransid,
					ref,
					amountcleared,
					transdate,
					amount/exrate as amt,
					banktranstype
				FROM banktrans
				WHERE amount >0
				AND transdate >= '". $SQLAfterDate . "'
				AND transdate <= '" . $SQLBeforeDate . "'
				AND bankact=" .$_POST["BankAccount"] . "
				AND  ABS(amountcleared - (amount / exrate)) > 0.009
				ORDER BY transdate";
		}
	}
	if ($_POST["First20_or_All"]!='All'){
		$sql = $sql . " LIMIT 20";
	}

	$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
	$PaymentsResult = DB_query($sql, $db, $ErrMsg);

	$TableHeader = '<tr><th>'. _('Ref'). '</th>
			<th>' . $TypeName . '</th>
			<th>' . _('Date') . '</th>
			<th>' . _('Amount') . '</th>
			<th>' . _('Outstanding') . '</th>
			<th colspan=3>' . _('Clear') . ' / ' . _('Unclear') . '</th>
		</tr>';
	echo '<table cellpadding=2 BORDER=2>' . $TableHeader;


	$j = 1;  //page length counter
	$k=0; //row colour counter
	$i = 1; //no of rows counter

	while ($myrow=DB_fetch_array($PaymentsResult)) {

		$DisplayTranDate = ConvertSQLDate($myrow['transdate']);
		$Outstanding = $myrow['amt']- $myrow['amountcleared'];
		if (ABS($Outstanding)<0.009){ /*the payment is cleared dont show the check box*/

			printf("<tr bgcolor='#CCCEEE'>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class=number>%s</td>
				<td class=number>%s</td>
				<td colspan=2>%s</td>
				<td><input type='checkbox' name='Unclear_%s'><input type=hidden name='BankTrans_%s' VALUE=%s></td>
				</tr>",
				$myrow['ref'],
				$myrow['banktranstype'],
				$DisplayTranDate,
				number_format($myrow['amt'],2),
				number_format($Outstanding,2),
				_('Unclear'),
				$i,
				$i,
				$myrow['banktransid']);

		} else{
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			printf("<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class=number>%s</td>
				<td class=number>%s</td>
				<td><input type='checkbox' name='Clear_%s'><input type=hidden name='BankTrans_%s' VALUE=%s></td>
				<td colspan=2><input type='text' maxlength=15 size=15 class=number name='AmtClear_%s'></td>
				</tr>",
				$myrow['ref'],
				$myrow['banktranstype'],
				$DisplayTranDate,
				number_format($myrow['amt'],2),
				number_format($Outstanding,2),
				$i,
				$i,
				$myrow['banktransid'],
				$i
			);
		}

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeader;
		}
	//end of page full new headings if
		$i++;
	}
	//end of while loop

	echo '</table><div class="centre"><input type=hidden name="RowCounter" value=' . $i . '><input type=submit name="Update" VALUE="' . _('Update Matching') . '"></div>';

}

echo '</form>';
include('includes/footer.inc');
?>
<?php

/* $Revision: 1.7 $ */

$PageSecurity = 7;

include ('includes/session.inc');

$title = _('Bank Reconciliation');

include('includes/header.inc');

echo '<FORM METHOD="POST" ACTION="' . $_SERVER["PHP_SELF"] . '?' . SID . '">';

echo '<CENTER><TABLE>';

$SQL = 'SELECT bankaccountname, accountcode FROM bankaccounts';



$ErrMsg = _('The bank accounts could not be retrieved by the SQL because');
$DbgMsg = _('The SQL used to retrieve the bank acconts was');
$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

echo '<TR><TD>' . _('Bank Account') . ':</TD><TD><SELECT name="BankAccount">';

if (DB_num_rows($AccountsResults)==0){
	 echo '</SELECT></TD></TR></TABLE><P>' . _('Bank Accounts have not yet been defined') . '. ' . _('You must first') . "<A HREF='" . $rootpath . "/BankAccounts.php'>" . _('define the bank accounts') . '</A>' . ' ' . _('and general ledger accounts to be affected') . '.';
	include('includes/footer.inc');
	exit;
} else {
	while ($myrow=DB_fetch_array($AccountsResults)){
		/*list the bank account names */
		if ($_POST["BankAccount"]==$myrow["accountcode"]){
			echo '<OPTION SELECTED VALUE="' . $myrow["accountcode"] . '">' . $myrow["bankaccountname"];
		} else {
			echo '<OPTION VALUE="' . $myrow["accountcode"] . '">' . $myrow["bankaccountname"];
		}
	}
	echo '</SELECT></TD></TR>';
}

/*Now do the posting while the user is thinking about the bank account to select */

include ('includes/GLPostings.inc');

echo '</TABLE><P><INPUT TYPE=SUBMIT Name="ShowRec" Value="' . _('Show bank reconciliation statement') . '"></CENTER>';


if (isset($_POST['ShowRec']) AND $_POST['ShowRec']!=''){

/*Get the balance of the bank account concerned */

	$sql = "SELECT MAX(period) FROM chartdetails WHERE accountcode=" . $_POST['BankAccount'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$LastPeriod = $myrow[0];


	$SQL = "SELECT bfwd+actual AS balance FROM chartdetails WHERE period=$LastPeriod AND accountcode=" . $_POST["BankAccount"];

	$ErrMsg = _('The bank account balance could not be returned by the SQL because');
	$BalanceResult = DB_query($SQL,$db,$ErrMsg);

	$myrow = DB_fetch_row($BalanceResult);
	$Balance = $myrow[0];

	echo '<CENTER><TABLE><TR><TD COLSPAN=6><B>' . _('Current bank account balance as at') . ' ' . Date($_SESSION['DefaultDateFormat']) . '</B></TD><TD VALIGN=BOTTOM ALIGN=RIGHT><B>' . number_format($Balance,2) . '</B></TD></TR>';

	$SQL = "SELECT amount/exrate AS amt,
			amountcleared,
			(amount/exrate)-amountcleared as outstanding,
			ref,
			transdate,
			systypes.typename,
			transno
		FROM banktrans,
			systypes
		WHERE banktrans.type = systypes.typeid
		AND banktrans.bankact=" . $_POST["BankAccount"] . "
		AND amount < 0
		AND ABS((amount/exrate)-amountcleared)>0.009";

	echo '<TR></TR>'; /*Bang in a blank line */

	$ErrMsg = _('The unpresented cheques could not be retrieved by the SQL because');
	$UPChequesResult = DB_query($SQL, $db, $ErrMsg);

	echo '<TR><TD COLSPAN=6><B>' . _('Add back unpresented cheques') . ':</B></TD></TR>';

	$TableHeader = '<TR>
			<TD class="tableheader">' . _('Date') . '</TD>
			<TD class="tableheader">' . _('Type') . '</TD>
			<TD class="tableheader">' . _('Number') . '</TD>
			<TD class="tableheader">' . _('Reference') . '</TD>
			<TD class="tableheader">' . _('Orig Amount') . '</TD>
			<TD class="tableheader">' . _('Outstanding') . '</TD>
			</TR>';

	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter
	$TotalUnpresentedCheques =0;

	while ($myrow=DB_fetch_array($UPChequesResult)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

  		printf("<td>%s</td>
		        <td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%01.2f</td>
			<td ALIGN=RIGHT>%01.2f</td>
			</tr>",
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
	echo '<TR></TR><TR><TD COLSPAN=6>' . _('Total of all unpresented cheques') . '</TD><TD ALIGN=RIGHT>' . number_format($TotalUnpresentedCheques,2) . '</TD></TR>';

	$SQL = "SELECT amount/exrate AS amt,
			amountcleared,
			(amount/exrate)-amountcleared as outstanding,
			ref,
			transdate,
			systypes.typename,
			transno
		FROM banktrans,
			systypes
		WHERE banktrans.type = systypes.typeid
		AND banktrans.bankact=" . $_POST["BankAccount"] . "
		AND amount > 0
		AND ABS((amount/exrate)-amountcleared)>0.009";

	echo '<TR></TR>'; /*Bang in a blank line */

	$ErrMsg = _('The uncleared deposits could not be retrieved by the SQL because');

	$UPChequesResult = DB_query($SQL,$db,$ErrMsg);

	echo '<TR><TD COLSPAN=6><B>' . _('Less deposits not cleared') . ':</B></TD></TR>';

	$TableHeader = '<TR>
			<TD class="tableheader">' . _('Date') . '</TD>
			<TD class="tableheader">' . _('Type') . '</TD>
			<TD class="tableheader">' . _('Number') . '</TD>
			<TD class="tableheader">' . _('Reference') . '</TD>
			<TD class="tableheader">' . _('Orig Amount') . '</TD>
			<TD class="tableheader">' . _('Outstanding') . '</TD>
			</TR>';

	echo '<TR>' . $TableHeader;

	$j = 1;
	$k=0; //row colour counter
	$TotalUnclearedDeposits =0;

	while ($myrow=DB_fetch_array($UPChequesResult)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

  		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%01.2f</td>
			<td ALIGN=RIGHT>%01.2f</td>
			</tr>",
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
	echo '<TR></TR><TR><TD COLSPAN=6>' . _('Total of all uncleared deposits') . '</TD><TD ALIGN=RIGHT>' . number_format($TotalUnclearedDeposits,2) . '</TD></TR>';

	echo '<TR></TR><TR><TD COLSPAN=6><B>' . _('Bank statement balance should be') . '</B></TD><TD ALIGN=RIGHT>' . number_format(($Balance - $TotalUnpresentedCheques -$TotalUnclearedDeposits),2) . '</TD></TR>';

	echo '</TABLE>';
}
echo '<P><A HREF="' . $rootpath . '/BankMatching.php?' . SID . '&Type=Payments">' . _('Match off cleared payments') . '</A>';
echo '<BR><A HREF="' . $rootpath . '/BankMatching.php?' . SID . '&Type=Receipts">' . _('Match off cleared deposits') . '</A>';
echo '</form>';
include('includes/footer.inc');
?>

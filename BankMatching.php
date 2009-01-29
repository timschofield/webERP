<?php
/* $Revision: 1.19 $ */

$PageSecurity = 7;

include("includes/session.inc");

if ((isset($_GET["Type"]) and $_GET["Type"]=='Receipts') OR (isset($_POST["Type"]) and $_POST["Type"]=='Receipts')){
	$Type = 'Receipts';
	$TypeName =_('Receipts');
	$title = _('Bank Account Deposits Matching');
} elseif ((isset($_GET["Type"]) and $_GET["Type"]=='Payments') OR (isset($_POST["Type"]) and $_POST["Type"]=='Payments')) {
	$Type = 'Payments';
	$TypeName =_('Payments');
	$title = _('Bank Account Payments Matching');
} else {
	prnMsg(_('This page must be called with a bank transaction type') . '. ' . _('It should not be called directly'),'error');
	include ('includes/footer.inc');
	exit;
}

include('includes/header.inc');

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
			$ErrMsg =  _('Could not match off this payment beacause');
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

echo '<P CLASS="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" TITLE="' . _('Bank Matching') . '" ALT="">' . ' ' . _('Bank Account Matching') . '</P>';
echo '<DIV CLASS="page_help_text">' . _('Use this screen to match webERP deposits and receipts to your Bank Statement.') . '</DIV><BR><CENTER>';

echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN Name=Type Value=$Type>";

echo '<CENTER><TABLE><TR>';
echo '<TD ALIGN=LEFT>' . _('Bank Account') . ':</TD><TD COLSPAN=3><SELECT tabindex="1" name="BankAccount">';

$sql = "SELECT accountcode, bankaccountname FROM bankaccounts";
$resultBankActs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultBankActs)){
	if (isset($_POST['BankAccount']) and $myrow["accountcode"]==$_POST['BankAccount']){
	     echo "<OPTION SELECTED Value='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
	} else {
	     echo "<OPTION Value='" . $myrow['accountcode'] . "'>" . $myrow['bankaccountname'];
	}
}

echo '</SELECT></TD></TR>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date("m")-3,Date("d"),Date("y")));
}

echo '<TR><TD>' . _('Show') . ' ' . $TypeName . ' ' . _('before') . ':</TD>
	<TD><INPUT tabindex="2" TYPE=TEXT NAME="BeforeDate" SIZE=12 MAXLENGTH=10 Value="' . $_POST['BeforeDate'] . '"></TD>';
echo '<TD>' . _('but after') . ':</TD>
	<TD><INPUT tabindex="3" TYPE=TEXT NAME="AfterDate" SIZE=12 MAXLENGTH=10 Value="' . $_POST['AfterDate'] . '"></TD></TR>';
echo '<TR><TD COLSPAN=3>' . _('Choose outstanding') . ' ' . $TypeName . ' ' . _('only or all') . ' ' . $TypeName . ' ' . _('in the date range') . ':</TD>
	<TD><SELECT tabindex="4" NAME="Ostg_or_All">';

if ($_POST["Ostg_or_All"]=='All'){
	echo '<OPTION SELECTED Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
	echo '<OPTION Value="Ostdg">' . _('Show unmatched') . ' ' . $TypeName . ' ' . _('only');
} else {
	echo '<OPTION Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
	echo '<OPTION SELECTED Value="Ostdg">' . _('Show unmatched') . ' ' . $TypeName . ' ' . _('only');
}
echo '</SELECT></TD></TR>';

echo '<TR><TD COLSPAN=3>' . _('Choose to display only the first 20 matching') . ' ' . $TypeName . ' ' .
  _('or all') . ' ' . $TypeName . ' ' . _('meeting the criteria') . ':</TD><TD><SELECT tabindex="5" NAME="First20_or_All">';
if ($_POST["First20_or_All"]=='All'){
	echo '<OPTION SELECTED Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
	echo '<OPTION Value="First20">' . _('Show only the first 20') . ' ' . $TypeName;
} else {
	echo '<OPTION Value="All">' . _('Show all') . ' ' . $TypeName . ' ' . _('in the date range');
	echo '<OPTION SELECTED Value="First20">' . _('Show only the first 20') . ' ' . $TypeName;
}
echo '</SELECT></TD></TR>';


echo '</TABLE><CENTER><INPUT tabindex="6" TYPE=SUBMIT NAME="ShowTransactions" VALUE="' . _('Show selected') . ' ' . $TypeName . '">';
echo "<P><A HREF='$rootpath/BankReconciliation.php?" . SID . "'>" . _('Show reconciliation') . '</A>';
echo '<HR>';

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

	$TableHeader = '<TR><TH>'. _('Ref'). '</TH>
			 <TH>' . $TypeName . '</TH>
			 <TH>' . _('Date') . '</TH>
			 <TH>' . _('Amount') . '</TH>
			 <TH>' . _('Outstanding') . '</TH>
			 <TH COLSPAN=3 ALIGN=CENTER>' . _('Clear') . ' / ' . _('Unclear') . '</TH>
		</TR>';
	echo '<TABLE CELLPADDING=2 BORDER=2>' . $TableHeader;


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
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td COLSPAN=2 ALIGN=CENTER>%s</td>
				<td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Unclear_%s'><INPUT TYPE=HIDDEN NAME='BankTrans_%s' VALUE=%s></TD>
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
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Clear_%s'><INPUT TYPE=HIDDEN NAME='BankTrans_%s' VALUE=%s></td>
				<td COLSPAN=2><INPUT TYPE='text' MAXLENGTH=15 SIZE=15 NAME='AmtClear_%s'></td>
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

	echo '</TABLE><INPUT TYPE=HIDDEN NAME="RowCounter" VALUE=' . $i . '><INPUT TYPE=SUBMIT NAME="Update" VALUE="' . _('Update Matching') . '"></CENTER>';

}

echo '</FORM>';
include('includes/footer.inc');
?>

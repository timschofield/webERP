<?php
/* $Revision: 1.12 $ */

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Bank Accounts Maintenance');

include('includes/header.inc');


if (isset($_GET['SelectedBankAccount'])) {
	$SelectedBankAccount=$_GET['SelectedBankAccount'];
} elseif (isset($_POST['SelectedBankAccount'])) {
	$SelectedBankAccount=$_POST['SelectedBankAccount'];
}

if (isset($Errors)) {
	unset($Errors);
}
	
$Errors = array();	

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	
	$sql="SELECT count(accountcode) 
			FROM bankaccounts WHERE accountcode='".$_POST['AccountCode']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]!=0 and !isset($SelectedBankAccount)) {
		$InputError = 1;
		prnMsg( _('The bank account code already exists in the database'),'error');
		$Errors[$i] = 'AccountCode';
		$i++;		
	}
	if (strlen($_POST['BankAccountName']) >50) {
		$InputError = 1;
		prnMsg(_('The bank account name must be fifty characters or less long'),'error');
		$Errors[$i] = 'AccountName';
		$i++;		
	}
	if ( trim($_POST['BankAccountName']) == '' ) {
		$InputError = 1;
		prnMsg(_('The bank account name may not be empty.'),'error');
		$Errors[$i] = 'AccountName';
		$i++;		
	}
	if ( trim($_POST['BankAccountNumber']) == '' ) {
		$InputError = 1;
		prnMsg(_('The bank account number may not be empty.'),'error');
		$Errors[$i] = 'AccountNumber';
		$i++;		
	}
	if (strlen($_POST['BankAccountNumber']) >50) {
		$InputError = 1;
		prnMsg(_('The bank account number must be fifty characters or less long'),'error');
		$Errors[$i] = 'AccountNumber';
		$i++;		
	}
	if (strlen($_POST['BankAddress']) >50) {
		$InputError = 1;
		prnMsg(_('The bank address must be fifty characters or less long'),'error');
		$Errors[$i] = 'BankAddress';
		$i++;		
	}

	if (isset($SelectedBankAccount) AND $InputError !=1) {
		
		/*Check if there are already transactions against this account - cant allow change currency if there are*/
		
		$sql = 'SELECT * FROM banktrans WHERE bankact=' . $SelectedBankAccount;
		$BankTransResult = DB_query($sql,$db);
		if (DB_num_rows($BankTransResult)>0) {
			$sql = "UPDATE bankaccounts
				SET bankaccountname='" . $_POST['BankAccountName'] . "',
				bankaccountnumber='" . $_POST['BankAccountNumber'] . "',
				bankaddress='" . $_POST['BankAddress'] . "'
			WHERE accountcode = '" . $SelectedBankAccount . "'";
			prnMsg(_('Note that it is not possible to change the currency of the account once there are transactions against it'),'warn');
		} else {
			$sql = "UPDATE bankaccounts
				SET bankaccountname='" . $_POST['BankAccountName'] . "',
				bankaccountnumber='" . $_POST['BankAccountNumber'] . "',
				bankaddress='" . $_POST['BankAddress'] . "',
				currcode ='" . $_POST['CurrCode'] . "'
				WHERE accountcode = '" . $SelectedBankAccount . "'";
		}

		$msg = _('The bank account details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO bankaccounts (
						accountcode,
						bankaccountname,
						bankaccountnumber,
						bankaddress,
						currcode)
				VALUES ('" . $_POST['AccountCode'] . "',
					'" . $_POST['BankAccountName'] . "',
					'" . $_POST['BankAccountNumber'] . "',
					'" . $_POST['BankAddress'] . "', 
					'" . $_POST['CurrCode'] . "'
					)";
		$msg = _('The new bank account has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The bank account could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the bank account details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
		prnMsg($msg,'success');
		unset($_POST['AccountCode']);
		unset($_POST['BankAccountName']);
		unset($_POST['BankAccountNumber']);
		unset($_POST['BankAddress']);
		unset($_POST['CurrCode']);
		unset($SelectedBankAccount);
	}
	

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(*) FROM banktrans WHERE banktrans.bankact='$SelectedBankAccount'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this bank account because transactions have been created using this account'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions with this bank account code');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM bankaccounts WHERE accountcode='$SelectedBankAccount'";
		$result = DB_query($sql,$db);
		prnMsg(_('Bank account deleted'),'success');
	} //end if Delete bank account
	
	unset($_GET['delete']);
	unset($SelectedBankAccount);
}

/* Always show the list of accounts */
If (!isset($SelectedBankAccount)) {
	$sql = "SELECT bankaccounts.accountcode,
			chartmaster.accountname,
			bankaccountname,
			bankaccountnumber,
			bankaddress,
			currcode
		FROM bankaccounts,
			chartmaster
		WHERE bankaccounts.accountcode = chartmaster.accountcode";
	
	$ErrMsg = _('The bank accounts set up could not be retreived because');
	$DbgMsg = _('The SQL used to retrieve the bank account details was') . '<BR>' . $sql;
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
	echo '<CENTER><table>';
	
	echo "<tr><th>" . _('GL Account') . "</th>
		<th>" . _('Account Name') . "</th>
		<th>" . _('Account Number') . "</th>
		<th>" . _('Bank Address') . "</th>
		<th>" . _('Currency') . "</th>
	</tr>";
	
	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo '<tr CLASS="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr CLASS="OddTableRows">';
		$k++;
	}
	
	printf("<td>%s<BR><FONT SIZE=2>%s</FONT></td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%s?SelectedBankAccount=%s\">" . _('Edit') . "</td>
		<td><a href=\"%s?SelectedBankAccount=%s&delete=1\">" . _('Delete') . "</td>
		</tr>",
		$myrow[0],
		$myrow[1],
		$myrow[2],
		$myrow[3],
		$myrow[4],
		$myrow[5],
		$_SERVER['PHP_SELF'],
		$myrow[0],
		$_SERVER['PHP_SELF'],
		$myrow[0]);
	
	}
	//END WHILE LIST LOOP
	
	
	echo '</CENTER></table><p>';
}

if (isset($SelectedBankAccount)) {
	echo '<P>';
	echo '<CENTER><P><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Show All Bank Accounts Defined') . '</A></CENTER>';
	echo '<P>';
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

if (isset($SelectedBankAccount) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT accountcode,
			bankaccountname,
			bankaccountnumber,
			bankaddress,
			currcode
		FROM bankaccounts
		WHERE bankaccounts.accountcode='$SelectedBankAccount'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['AccountCode'] = $myrow['accountcode'];
	$_POST['BankAccountName']  = $myrow['bankaccountname'];
	$_POST['BankAccountNumber'] = $myrow['bankaccountnumber'];
	$_POST['BankAddress'] = $myrow['bankaddress'];
	$_POST['CurrCode'] = $myrow['currcode'];

	echo '<INPUT TYPE=HIDDEN NAME=SelectedBankAccount VALUE=' . $SelectedBankAccount . '>';
	echo '<INPUT TYPE=HIDDEN NAME=AccountCode VALUE=' . $_POST['AccountCode'] . '>';
	echo '<CENTER><TABLE> <TR><TD>' . _('Bank Account GL Code') . ':</TD><TD>';
	echo $_POST['AccountCode'] . '</TD></TR>';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<CENTER><TABLE><TR><TD>' . _('Bank Account GL Code') . 
		":</TD><TD><Select tabindex='1' " . (in_array('AccountCode',$Errors) ?  'class="selecterror"' : '' ) ." name='AccountCode'>";

	$sql = "SELECT accountcode,
			accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_ = accountgroups.groupname
		AND accountgroups.pandl = 0
		ORDER BY accountcode";

	$result = DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['AccountCode']) and $myrow['accountcode']==$_POST['AccountCode']) {
			echo '<OPTION SELECTED VALUE=';
		} else {
			echo '<OPTION VALUE=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'];

	} //end while loop

	echo '</SELECT></TD></TR>';
}

// Check if details exist, if not set some defaults
if (!isset($_POST['BankAccountName'])) {
	$_POST['BankAccountName']='';
}
if (!isset($_POST['BankAccountNumber'])) {
	$_POST['BankAccountNumber']='';
}
if (!isset($_POST['BankAddress'])) {
	$_POST['BankAddress']='';
}

echo '<TR><TD>' . _('Bank Account Name') . ': </TD>
			<TD><input tabindex="2" ' . (in_array('AccountName',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="BankAccountName" value="' . $_POST['BankAccountName'] . '" SIZE=40 MAXLENGTH=50></TD></TR>
		<TR><TD>' . _('Bank Account Number') . ': </TD>
			<TD><input tabindex="3" ' . (in_array('AccountNumber',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="BankAccountNumber" value="' . $_POST['BankAccountNumber'] . '" SIZE=40 MAXLENGTH=50></TD></TR>
		<TR><TD>' . _('Bank Address') . ': </TD>
			<TD><input tabindex="4" ' . (in_array('BankAddress',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="BankAddress" value="' . $_POST['BankAddress'] . '" SIZE=40 MAXLENGTH=50></TD></TR>
		<TR><TD>' . _('Currency Of Account') . ': </TD><TD><select tabindex="5" name="CurrCode">';

if (!isset($_POST['CurrCode']) OR $_POST['CurrCode']==''){
	$_POST['CurrCode'] = $_SESSION['CompanyRecord']['currencydefault'];
}
$result = DB_query('SELECT currabrev, currency FROM currencies',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['currabrev']==$_POST['CurrCode']) {
		echo '<OPTION SELECTED VALUE=';
	} else {
		echo '<OPTION VALUE=';
	}
	echo $myrow['currabrev'] . '>' . $myrow['currabrev'];
} //end while loop

echo '</SELECT></TD></TR>';

echo '</TABLE>
		<CENTER><input tabindex="6" type="Submit" name="submit" value="'. _('Enter Information') .'"></CENTER>';

echo '</FORM>';
include('includes/footer.inc');
?>
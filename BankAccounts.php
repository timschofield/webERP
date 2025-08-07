<?php
// BankAccounts.php
// This script defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation.

include('includes/session.php');
$Title = _('Bank Accounts');// Screen identificator.
$ViewTopic = 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'BankAccounts';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .
	_('Bank') . '" /> ' .// Icon title.
	_('Bank Accounts Maintenance') . '</p>';// Page title.

echo '<div class="page_help_text">' . _('Update Bank Account details.  Account Code is for SWIFT or BSB type Bank Codes.  Set Default for Invoices to Currency Default  or Fallback Default to print Account details on Invoices (only one account should be set to Fall Back Default).') . '.</div><br />';

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

	$SQL="SELECT count(accountcode)
			FROM bankaccounts WHERE accountcode='".$_POST['AccountCode']."'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_row($Result);

	if ($MyRow[0]!=0 and !isset($SelectedBankAccount)) {
		$InputError = 1;
		prnMsg( _('The bank account code already exists in the database'),'error');
		$Errors[$i] = 'AccountCode';
		$i++;
	}
	if (mb_strlen($_POST['BankAccountName']) >50) {
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
	if (mb_strlen($_POST['BankAccountNumber']) >50) {
		$InputError = 1;
		prnMsg(_('The bank account number must be fifty characters or less long'),'error');
		$Errors[$i] = 'AccountNumber';
		$i++;
	}
	if (mb_strlen($_POST['BankAddress']) >50) {
		$InputError = 1;
		prnMsg(_('The bank address must be fifty characters or less long'),'error');
		$Errors[$i] = 'BankAddress';
		$i++;
	}

	if (isset($SelectedBankAccount) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/

		$SQL = "SELECT banktransid FROM banktrans WHERE bankact='" . $SelectedBankAccount . "'";
		$BankTransResult = DB_query($SQL);
		if (DB_num_rows($BankTransResult)>0) {
			$SQL = "UPDATE bankaccounts SET bankaccountname='" . $_POST['BankAccountName'] . "',
											bankaccountcode='" . $_POST['BankAccountCode'] . "',
											bankaccountnumber='" . $_POST['BankAccountNumber'] . "',
											bankaddress='" . $_POST['BankAddress'] . "',
											invoice ='" . $_POST['DefAccount'] . "',
											importformat='" . $_POST['ImportFormat'] . "'
										WHERE accountcode = '" . $SelectedBankAccount . "'";
			prnMsg(_('Note that it is not possible to change the currency of the account once there are transactions against it'),'warn');

		} else {
			$SQL = "UPDATE bankaccounts SET bankaccountname='" . $_POST['BankAccountName'] . "',
											bankaccountcode='" . $_POST['BankAccountCode'] . "',
											bankaccountnumber='" . $_POST['BankAccountNumber'] . "',
											bankaddress='" . $_POST['BankAddress'] . "',
											currcode ='" . $_POST['CurrCode'] . "',
											invoice ='" . $_POST['DefAccount'] . "',
											importformat='" . $_POST['ImportFormat'] . "'
										WHERE accountcode = '" . $SelectedBankAccount . "'";
		}

		$Msg = _('The bank account details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$SQL = "INSERT INTO bankaccounts (accountcode,
										bankaccountname,
										bankaccountcode,
										bankaccountnumber,
										bankaddress,
										currcode,
										invoice,
										importformat
									) VALUES ('" . $_POST['AccountCode'] . "',
										'" . $_POST['BankAccountName'] . "',
										'" . $_POST['BankAccountCode'] . "',
										'" . $_POST['BankAccountNumber'] . "',
										'" . $_POST['BankAddress'] . "',
										'" . $_POST['CurrCode'] . "',
										'" . $_POST['DefAccount'] . "',
										'" . $_POST['ImportFormat'] . "' )";
		$Msg = _('The new bank account has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The bank account could not be inserted or modified because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg($Msg,'success');
		echo '<br />';
		unset($_POST['AccountCode']);
		unset($_POST['BankAccountName']);
		unset($_POST['BankAccountCode']);
		unset($_POST['BankAccountNumber']);
		unset($_POST['BankAddress']);
		unset($_POST['CurrCode']);
		unset($_POST['DefAccount']);
		unset($SelectedBankAccount);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$SQL= "SELECT COUNT(bankact) AS accounts FROM banktrans WHERE banktrans.bankact='" . $SelectedBankAccount . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow['accounts']>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this bank account because transactions have been created using this account'),'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow['accounts'] . ' ' . _('transactions with this bank account code');

	}
	if (!$CancelDelete) {
		$SQL="DELETE FROM bankaccounts WHERE accountcode='" . $SelectedBankAccount . "'";
		$Result = DB_query($SQL);
		prnMsg(_('Bank account deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedBankAccount);
}

/* Always show the list of accounts */
if (!isset($SelectedBankAccount)) {
	$SQL = "SELECT
				bankaccounts.accountcode,
				bankaccounts.bankaccountcode,
				chartmaster.accountname,
				bankaccountname,
				bankaccountnumber,
				bankaddress,
				currcode,
				invoice,
				importformat
			FROM bankaccounts
			INNER JOIN chartmaster
				ON bankaccounts.accountcode = chartmaster.accountcode
			ORDER BY bankaccounts.accountcode";
	$ErrMsg = _('The bank accounts could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table class="selection">
			<tr>
				<th>' . _('GL Account Code') . '</th>
				<th>' . _('Bank Account Name') . '</th>
				<th>' . _('Bank Account Code') . '</th>
				<th>' . _('Bank Account Number') . '</th>
				<th>' . _('Bank Address') . '</th>
				<th>' . _('Import Format') . '</th>
				<th>' . _('Currency') . '</th>
				<th>' . _('Default for Invoices') . '</th>
				<th colspan="2"></th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		// Lists bank accounts order by account code
		if ($MyRow['invoice']==0) {
			$DefaultBankAccount=_('No');
		} elseif ($MyRow['invoice']==1) {
			$DefaultBankAccount=_('Fall Back Default');
		} elseif ($MyRow['invoice']==2) {
			$DefaultBankAccount=_('Currency Default');
		}

		switch ($MyRow['importformat']) {
			case 'MT940-ING':
				$ImportFormat = 'ING MT940';
				break;
			case 'MT940-SCB':
				$ImportFormat = 'SCB MT940';
				break;
			default:
				$ImportFormat ='';
		}

		echo '<tr class="striped_row">
				<td>', $MyRow['accountcode'], '<br />', $MyRow['accountname'], '</td>
				<td>', $MyRow['bankaccountname'], '</td>
				<td>', $MyRow['bankaccountcode'], '</td>
				<td>', $MyRow['bankaccountnumber'], '</td>
				<td>', $MyRow['bankaddress'], '</td>
				<td>', $ImportFormat, '</td>
				<td>', $MyRow['currcode'], '</td>
				<td>', $DefaultBankAccount, '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedBankAccount=', $MyRow['accountcode'], '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedBankAccount=', $MyRow['accountcode'], '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this bank account?') . '\');">' . _('Delete') . '</a></td>
			</tr>';

	}
	//END WHILE LIST LOOP


	echo '</table>';
}

if (isset($SelectedBankAccount)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Bank Accounts Defined') . '</a>
		</div>';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedBankAccount) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$SQL = "SELECT accountcode,
					bankaccountname,
					bankaccountcode,
					bankaccountnumber,
					bankaddress,
					currcode,
					invoice
			FROM bankaccounts
			WHERE bankaccounts.accountcode='" . $SelectedBankAccount . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['AccountCode'] = $MyRow['accountcode'];
	$_POST['BankAccountName']  = $MyRow['bankaccountname'];
	$_POST['BankAccountCode']  = $MyRow['bankaccountcode'];
	$_POST['BankAccountNumber'] = $MyRow['bankaccountnumber'];
	$_POST['BankAddress'] = $MyRow['bankaddress'];
	$_POST['CurrCode'] = $MyRow['currcode'];
	$_POST['DefAccount'] = $MyRow['invoice'];

	echo '<input type="hidden" name="SelectedBankAccount" value="' . $SelectedBankAccount . '" />';
	echo '<input type="hidden" name="AccountCode" value="' . $_POST['AccountCode'] . '" />';
	echo '<fieldset>
			<legend>', _('Edit Bank Account Details'), '</legend>
			<field>
				<label for="AccountCode">' . _('Bank Account GL Code') . ':</label>
				<fieldtext>' . $_POST['AccountCode'] . '</fieldtext>
			</field>';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<fieldset>
			<legend>', _('Create New Bank Details'), '</legend>
			<field>
				<label for="AccountCode">' . _('Bank Account GL Code') . ':</label>
				<select tabindex="1" ' . (in_array('AccountCode',$Errors) ?  'class="selecterror"' : '' ) .' name="AccountCode" autofocus="autofocus" >';

	$SQL = "SELECT accountcode,
					accountname
			FROM chartmaster LEFT JOIN accountgroups
			ON chartmaster.group_ = accountgroups.groupname
			WHERE accountgroups.pandl = 0
			ORDER BY accountcode";

	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['AccountCode']) and $MyRow['accountcode']==$_POST['AccountCode']) {
			echo '<option selected="selected" value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
		} else {
			echo '<option value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
		}

	} //end while loop

	echo '</select>
		</field>';
}

// Check if details exist, if not set some defaults
if (!isset($_POST['BankAccountName'])) {
	$_POST['BankAccountName']='';
}
if (!isset($_POST['BankAccountNumber'])) {
	$_POST['BankAccountNumber']='';
}
if (!isset($_POST['BankAccountCode'])) {
        $_POST['BankAccountCode']='';
}
if (!isset($_POST['BankAddress'])) {
	$_POST['BankAddress']='';
}
if (!isset($_POST['ImportFormat'])) {
	$_POST['ImportFormat']='';
}
echo '<field>
		<label for="BankAccountName">' . _('Bank Account Name') . ': </label>
		<input tabindex="2" ' . (in_array('AccountName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" name="BankAccountName" value="' . $_POST['BankAccountName'] . '" size="40" maxlength="50" />
	</field>
	<field>
		<label for="AccountCode">' . _('Bank Account Code') . ': </label>
		<input tabindex="3" ' . (in_array('AccountCode',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="BankAccountCode" value="' . $_POST['BankAccountCode'] . '" size="40" maxlength="50" />
	</field>
	<field>
		<label for="AccountNumber">' . _('Bank Account Number') . ': </label>
		<input tabindex="3" ' . (in_array('AccountNumber',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="BankAccountNumber" value="' . $_POST['BankAccountNumber'] . '" size="40" maxlength="50" />
	</field>
	<field>
		<label for="BankAddress">' . _('Bank Address') . ': </label>
		<input tabindex="4" ' . (in_array('BankAddress',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="BankAddress" value="' . $_POST['BankAddress'] . '" size="40" maxlength="50" />
	</field>
	<field>
		<label for="ImportFormat">' . _('Transaction Import File Format') . ': </label>
		<select tabindex="5" name="ImportFormat">
			<option ' . ($_POST['ImportFormat']=='' ? 'selected="selected"' : '') . ' value="">' . _('N/A') . '</option>
			<option ' . ($_POST['ImportFormat']=='MT940-SCB' ? 'selected="selected"' : '') . ' value="MT940-SCB">' . _('MT940 - Siam Comercial Bank Thailand') . '</option>
			<option ' . ($_POST['ImportFormat']=='MT940-ING' ? 'selected="selected"' : '') . ' value="MT940-ING">' . _('MT940 - ING Bank Netherlands') . '</option>
			<option ' . ($_POST['ImportFormat']=='GIFTS' ? 'selected="selected"' : '') . ' value="GIFTS">' . _('GIFTS - Bank of New Zealand') . '</option>
			</select>
	</field>
	<field>
		<label for="CurrCode">' . _('Currency Of Account') . ': </label>
		<select tabindex="6" name="CurrCode">';

if (!isset($_POST['CurrCode']) or $_POST['CurrCode']=='') {
	$_POST['CurrCode'] = $_SESSION['CompanyRecord']['currencydefault'];
}
$Result = DB_query("SELECT currabrev,
							currency
					FROM currencies");

while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['currabrev']==$_POST['CurrCode']) {
		echo '<option selected="selected" value="'.$MyRow['currabrev'] . '">' . $MyRow['currabrev'] . '</option>';
	} else {
		echo '<option value="'.$MyRow['currabrev'] . '">' . $MyRow['currabrev'] . '</option>';
	}
} //end while loop

echo '</select>';
echo '</field>';

echo '<field>
		<label for="DefAccount">' . _('Default for Invoices') . ': </label>
		<select tabindex="8" name="DefAccount">';

if (!isset($_POST['DefAccount']) OR $_POST['DefAccount']=='') {
	$_POST['DefAccount'] = $_SESSION['CompanyRecord']['currencydefault'];
}

if (isset($SelectedBankAccount)) {
	$Result = DB_query("SELECT invoice FROM bankaccounts where accountcode = '" . $SelectedBankAccount . "'" );
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['invoice']== 1) {
			echo '<option selected="selected" value="1">' . _('Fall Back Default') . '</option>
					<option value="2">' . _('Currency Default') . '</option>
					<option value="0">' . _('No') . '</option>';
		} elseif ($MyRow['invoice']== 2) {
			echo '<option value="0">' . _('No') . '</option>
					<option selected="selected" value="2">' . _('Currency Default') . '</option>
					<option value="1">' . _('Fall Back Default') . '</option>';
		} else {
			echo '<option selected="selected" value="0">' . _('No') . '</option>
					<option  value="2">' . _('Currency Default') . '</option>
					<option value="1">' . _('Fall Back Default') . '</option>';
		}
	}//end while loop
} else {
	echo '<option value="1">' . _('Fall Back Default') . '</option>
			<option  value="2">' . _('Currency Default') . '</option>
			<option value="0">' . _('No') . '</option>';
}

echo '</select>
		</field>
		</fieldset>
		<div class="centre"><input tabindex="9" type="submit" name="submit" value="'. _('Enter Information') .'" /></div>
		</form>';
include('includes/footer.php');

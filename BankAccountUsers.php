<?php
/* $Id: BankAccountUsers.php 6946 2014-10-27 07:30:11Z daintree $*/
/* This script maintains table bankaccountusers (Authorized users to work with a bank account in webERP) */

include('includes/session.php');
$Title = _('Bank Account Users');
$ViewTopic = 'GeneralLedger';
$BookMark = 'BankAccountUsers';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .
	_('Bank Account Authorised Users') . '" /> ' .// Icon title.
	_('Maintenance Of Bank Account Authorised Users') . '</p>';// Page title.

if (isset($_POST['SelectedUser'])){
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif (isset($_GET['SelectedUser'])){
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
} else {
	$SelectedUser='';
}

if (isset($_POST['SelectedBankAccount'])){
	$SelectedBankAccount = mb_strtoupper($_POST['SelectedBankAccount']);
} elseif (isset($_GET['SelectedBankAccount'])){
	$SelectedBankAccount = mb_strtoupper($_GET['SelectedBankAccount']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedBankAccount);
	unset($SelectedUser);
}

if (isset($_POST['Process'])) {
	if ($_POST['SelectedBankAccount'] == '') {
		echo prnMsg(_('You have not selected any bank account'),'error');
		echo '<br />';
		unset($SelectedBankAccount);
		unset($_POST['SelectedBankAccount']);
	}
}

if (isset($_POST['submit'])) {

	$InputError=0;

	if ($_POST['SelectedUser']=='') {
		$InputError=1;
		echo prnMsg(_('You have not selected an user to be authorised to use this bank account'),'error');
		echo '<br />';
		unset($SelectedBankAccount);
	}

	if ( $InputError !=1 ) {

		// First check the user is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM bankaccountusers
			     WHERE accountcode= '" .  $_POST['SelectedBankAccount'] . "'
				 AND userid = '" .  $_POST['SelectedUser'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] >0) {
			$InputError = 1;
			prnMsg( _('The user') . ' ' . $_POST['SelectedUser'] . ' ' ._('already authorised to use this bank account'),'error');
		} else {
			// Add new record on submit
			$sql = "INSERT INTO bankaccountusers (accountcode,
												userid)
										VALUES ('" . $_POST['SelectedBankAccount'] . "',
												'" . $_POST['SelectedUser'] . "')";

			$msg = _('User') . ': ' . $_POST['SelectedUser'].' '._('has been authorised to use') .' '. $_POST['SelectedBankAccount'] .  ' ' . _('bank account');
			$result = DB_query($sql);
			prnMsg($msg,'success');
			unset($_POST['SelectedUser']);
		}
	}
} elseif ( isset($_GET['delete']) ) {
	$sql="DELETE FROM bankaccountusers
		WHERE accountcode='".$SelectedBankAccount."'
		AND userid='".$SelectedUser."'";

	$ErrMsg = _('The bank account user record could not be deleted because');
	$result = DB_query($sql,$ErrMsg);
	prnMsg(_('User').' '. $SelectedUser .' '. _('has been un-authorised to use').' '. $SelectedBankAccount .' '. _('bank account') ,'success');
	unset($_GET['delete']);
}

if (!isset($SelectedBankAccount)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedUser will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true. These will call the same page again and allow update/input or deletion of the records*/
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">
			<tr>
				<td>' . _('Select Bank Account') . ':</td>
				<td><select name="SelectedBankAccount">';

	$SQL = "SELECT accountcode,
					bankaccountname
			FROM bankaccounts";

	$result = DB_query($SQL);
	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($SelectedBankAccount) and $myrow['accountcode']==$SelectedBankAccount) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['bankaccountname'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';

   	echo '</table>'; // close main table
    DB_free_result($result);

	echo '<br />
		<div class="centre">
			<input type="submit" name="Process" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>';

	echo '</div>
          </form>';

}

//end of ifs and buts!
if (isset($_POST['process'])OR isset($SelectedBankAccount)) {
	$SQLName = "SELECT bankaccountname
			FROM bankaccounts
			WHERE accountcode='" .$SelectedBankAccount."'";
	$result = DB_query($SQLName);
	$myrow = DB_fetch_array($result);
	$SelectedBankName = $myrow['bankaccountname'];

	echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Authorised users for') . ' ' .$SelectedBankName . ' ' . _('bank account') .'</a></div>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="SelectedBankAccount" value="' . $SelectedBankAccount . '" />';

	$sql = "SELECT bankaccountusers.userid,
					www_users.realname
			FROM bankaccountusers INNER JOIN www_users
			ON bankaccountusers.userid=www_users.userid
			WHERE bankaccountusers.accountcode='" . $SelectedBankAccount . "'
			ORDER BY bankaccountusers.userid ASC";

	$result = DB_query($sql);

	echo '<br />
			<table class="selection">';
	echo '<tr><th colspan="3"><h3>' . _('Authorised users for bank account') . ' ' .$SelectedBankName. '</h3></th></tr>';
	echo '<tr>
			<th>' . _('User Code') . '</th>
			<th>' . _('User Name') . '</th>
		</tr>';

$k=0; //row colour counter

while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	printf('<td>%s</td>
			<td>%s</td>
			<td><a href="%s?SelectedUser=%s&amp;delete=yes&amp;SelectedBankAccount=' . $SelectedBankAccount . '" onclick="return confirm(\'' . _('Are you sure you wish to un-authorise this user?') . '\');">' . _('Un-authorise') . '</a></td>
			</tr>',
			$myrow['userid'],
			$myrow['realname'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$myrow['userid'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$myrow['userid']);
	}
	//END WHILE LIST LOOP
	echo '</table>';

	if (! isset($_GET['delete'])) {


		echo '<br /><table  class="selection">'; //Main table

		echo '<tr>
				<td>' . _('Select User') . ':</td>
				<td><select name="SelectedUser">';

		$SQL = "SELECT userid,
						realname
				FROM www_users";

		$result = DB_query($SQL);
		if (!isset($_POST['SelectedUser'])){
			echo '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
		}
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_POST['SelectedUser']) AND $myrow['userid']==$_POST['SelectedUser']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $myrow['userid'] . '">' . $myrow['userid'] . ' - ' . $myrow['realname'] . '</option>';

		} //end while loop

		echo '</select></td></tr>';

	   	echo '</table>'; // close main table
        DB_free_result($result);

		echo '<br /><div class="centre"><input type="submit" name="submit" value="' . _('Accept') . '" />
									<input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>';

		echo '</div>
              </form>';

	} // end if user wish to delete
}

include('includes/footer.php');
?>

<?php

// Maintains table bankaccountusers (Authorized users to work with a bank account in webERP).

require(__DIR__ . '/includes/session.php');

$Title = __('Bank Account Users');
$ViewTopic = 'GeneralLedger';
$BookMark = 'BankAccountUsers';
include('includes/header.php');

echo '<a class="toplink" href="' . $RootPath . '/BankAccountUsers.php">', __('Select another bank account'), '</a>';

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .
	__('Bank Account Authorised Users') . '" /> ' .// Icon title.
	__('Maintenance Of Bank Account Authorised Users') . '</p>';// Page title.

if (isset($_POST['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif (isset($_GET['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
} else {
	$SelectedUser='';
}

if (isset($_POST['SelectedBankAccount'])) {
	$SelectedBankAccount = mb_strtoupper($_POST['SelectedBankAccount']);
} elseif (isset($_GET['SelectedBankAccount'])) {
	$SelectedBankAccount = mb_strtoupper($_GET['SelectedBankAccount']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedBankAccount);
	unset($SelectedUser);
}

if (isset($_POST['Process'])) {
	if ($_POST['SelectedBankAccount'] == '') {
		prnMsg(__('You have not selected any bank account'),'error');
		echo '<br />';
		unset($SelectedBankAccount);
		unset($_POST['SelectedBankAccount']);
	}
}

if (isset($_POST['submit'])) {

	$InputError=0;

	if ($_POST['SelectedUser']=='') {
		$InputError=1;
		prnMsg(__('You have not selected an user to be authorised to use this bank account'),'error');
		echo '<br />';
		unset($SelectedBankAccount);
	}

	if ( $InputError !=1 ) {

		// First check the user is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM bankaccountusers
			     WHERE accountcode= '" .  $_POST['SelectedBankAccount'] . "'
				 AND userid = '" .  $_POST['SelectedUser'] . "'";

		$Checkresult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($Checkresult);

		if ( $CheckRow[0] >0) {
			$InputError = 1;
			prnMsg( __('The user') . ' ' . $_POST['SelectedUser'] . ' ' .__('already authorised to use this bank account'),'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO bankaccountusers (accountcode,
												userid)
										VALUES ('" . $_POST['SelectedBankAccount'] . "',
												'" . $_POST['SelectedUser'] . "')";

			$Msg = __('User') . ': ' . $_POST['SelectedUser'].' '.__('has been authorised to use') .' '. $_POST['SelectedBankAccount'] .  ' ' . __('bank account');
			$Result = DB_query($SQL);
			prnMsg($Msg,'success');
			unset($_POST['SelectedUser']);
		}
	}
} elseif ( isset($_GET['delete']) ) {
	$SQL="DELETE FROM bankaccountusers
		WHERE accountcode='".$SelectedBankAccount."'
		AND userid='".$SelectedUser."'";

	$ErrMsg = __('The bank account user record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(__('User').' '. $SelectedUser .' '. __('has been un-authorised to use').' '. $SelectedBankAccount .' '. __('bank account') ,'success');
	unset($_GET['delete']);
}

if (!isset($SelectedBankAccount)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedUser will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true. These will call the same page again and allow update/input or deletion of the records*/
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Select Bank Account'), '</legend>
			<field>
				<label for="SelectedBankAccount">' . __('Select Bank Account') . ':</label>
				<select name="SelectedBankAccount">';

	$SQL = "SELECT
				accountcode,
				bankaccountname,
				currcode
			FROM bankaccounts
			ORDER BY accountcode";
	$ErrMsg = __('The bank accounts could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<option value="">' . __('Not Yet Selected') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		// Lists bank accounts order by accountcode
		echo '<option',
			((isset($SelectedBankAccount) and $MyRow['accountcode']==$SelectedBankAccount) ? 'selected="selected"' : ''),
			' value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', $MyRow['bankaccountname'], ' - ', $MyRow['currcode'], '</option>';
	}// End while loop
	echo '</select>
		</field>
	</fieldset>'; // Close main table
    DB_free_result($Result);

	echo '<div class="centre">
			<input type="submit" name="Process" value="' . __('Accept') . '" />
			<input type="reset" name="Cancel" value="' . __('Cancel') . '" />
		</div>';

	echo '</form>';
}

//end of ifs and buts!
if (isset($_POST['process'])OR isset($SelectedBankAccount)) {
	$SQLName = "SELECT bankaccountname
			FROM bankaccounts
			WHERE accountcode='" .$SelectedBankAccount."'";
	$Result = DB_query($SQLName);
	$MyRow = DB_fetch_array($Result);
	$SelectedBankName = $MyRow['bankaccountname'];

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="SelectedBankAccount" value="' . $SelectedBankAccount . '" />';

	$SQL = "SELECT bankaccountusers.userid,
					www_users.realname
			FROM bankaccountusers INNER JOIN www_users
			ON bankaccountusers.userid=www_users.userid
			WHERE bankaccountusers.accountcode='" . $SelectedBankAccount . "'
			ORDER BY bankaccountusers.userid ASC";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr><th colspan="3">' . __('Authorised users for bank account') . ' ' .$SelectedBankName. '</th></tr>';
	echo '<tr>
			<th>' . __('User Code') . '</th>
			<th>' . __('User Name') . '</th>
			<th></th>
		</tr>';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">
			<td>', $MyRow['userid'], '</td>
			<td>', $MyRow['realname'], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '?SelectedUser=', $MyRow['userid'], '&amp;delete=yes&amp;SelectedBankAccount=' . $SelectedBankAccount . '" onclick="return confirm(\'' . __('Are you sure you wish to un-authorise this user?') . '\');">' . __('Un-authorise') . '</a></td>
		</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';

	if (! isset($_GET['delete'])) {


		echo '<fieldset>
				<legend>', __('Select User'), '</legend>';

		echo '<field>
				<label for="SelectedUser">' . __('Select User') . ':</label>
				<select name="SelectedUser">';

		$SQL = "SELECT userid,
						realname
				FROM www_users";

		$Result = DB_query($SQL);
		if (!isset($_POST['SelectedUser'])) {
			echo '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedUser']) AND $MyRow['userid']==$_POST['SelectedUser']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';

		} //end while loop

		echo '</select>
			</field>';

	   	echo '</fieldset>'; // close main table
        DB_free_result($Result);

		echo '<div class="centre">
				<input type="submit" name="submit" value="' . __('Accept') . '" />
									<input type="reset" name="Cancel" value="' . __('Cancel') . '" />
			</div>';

		echo '</form>';

	} // end if user wish to delete
}

include('includes/footer.php');

<?php
/* $Id: glaccountusers.php 6806 2013-09-28 05:10:46Z daintree $*/

include('includes/session.inc');
$Title = _('GL Account Authorised Users Maintenance');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" title="' . _('GL Account Authorised Users') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif (isset($_GET['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
} else {
	$SelectedUser = '';
}

if (isset($_POST['SelectedGLAccount'])) {
	$SelectedGLAccount = mb_strtoupper($_POST['SelectedGLAccount']);
} elseif (isset($_GET['SelectedGLAccount'])) {
	$SelectedGLAccount = mb_strtoupper($_GET['SelectedGLAccount']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedGLAccount);
	unset($SelectedUser);
}

if (isset($_POST['Process'])) {
	if ($_POST['SelectedGLAccount'] == '') {
		prnMsg(_('You have not selected any GL Account'), 'error');
		echo '<br />';
		unset($SelectedGLAccount);
		unset($_POST['SelectedGLAccount']);
	}
}

if (isset($_POST['submit'])) {

	$InputError = 0;

	if ($_POST['SelectedUser'] == '') {
		$InputError = 1;
		prnMsg(_('You have not selected an user to be authorised to use this GL Account'), 'error');
		echo '<br />';
		unset($SelectedGLAccount);
	}

	if ($InputError != 1) {

		// First check the user is not being duplicated

		$CheckSql = "SELECT count(*)
			     FROM glaccountusers
			     WHERE accountcode= '" . $_POST['SelectedGLAccount'] . "'
				 AND userid = '" . $_POST['SelectedUser'] . "'";

		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);

		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The user') . ' ' . $_POST['SelectedUser'] . ' ' . _('is already authorised to use this GL Account'), 'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO glaccountusers (accountcode,
												userid,
												canview,
												canupd)
										VALUES ('" . $_POST['SelectedGLAccount'] . "',
												'" . $_POST['SelectedUser'] . "',
												'1',
												'1')";

			$msg = _('User') . ': ' . $_POST['SelectedUser'] . ' ' . _('authority to use the') . ' ' . $_POST['SelectedGLAccount'] . ' ' . _('GL Account has been changed');
			$Result = DB_query($SQL);
			prnMsg($msg, 'success');
			unset($_POST['SelectedUser']);
		}
	}
} elseif (isset($_GET['delete'])) {
	$SQL = "DELETE FROM glaccountusers
		WHERE accountcode='" . $SelectedGLAccount . "'
		AND userid='" . $SelectedUser . "'";

	$ErrMsg = _('The GL Account user record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('User') . ' ' . $SelectedUser . ' ' . _('has had their authority to use the') . ' ' . $SelectedGLAccount . ' ' . _('GL Account removed'), 'success');
	unset($_GET['delete']);
} elseif (isset($_GET['ToggleUpdate'])) {
	$SQL = "UPDATE glaccountusers
			SET canupd='" . $_GET['ToggleUpdate'] . "'
			WHERE accountcode='" . $SelectedGLAccount . "'
			AND userid='" . $SelectedUser . "'";

	$ErrMsg = _('The GL Account user record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('User') . ' ' . $SelectedUser . ' ' . _('has had their authority to update') . ' ' . $SelectedGLAccount . ' ' . _('GL Account removed'), 'success');
	unset($_GET['ToggleUpdate']);
}

if (!isset($SelectedGLAccount)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedUser will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true. These will call the same page again and allow update/input or deletion of the records*/
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">
			<tr>
				<td>' . _('Select GL Account') . ':</td>
				<td><select name="SelectedGLAccount">';

	$Result = DB_query("SELECT accountcode,
								accountname
						FROM chartmaster
						ORDER BY accountcode");

	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedGLAccount) and $MyRow['accountcode'] == $SelectedGLAccount) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . $MyRow['accountname'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';

	echo '</table>'; // close main table
	DB_free_result($Result);

	echo '<div class="centre">
			<input type="submit" name="Process" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>';

	echo '</form>';

}

//end of ifs and buts!
if (isset($_POST['process']) or isset($SelectedGLAccount)) {
	$SQLName = "SELECT accountname
			FROM chartmaster
			WHERE accountcode='" . $SelectedGLAccount . "'";
	$Result = DB_query($SQLName);
	$MyRow = DB_fetch_array($Result);
	$SelectedGLAccountName = $MyRow['accountname'];

	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Authorised users for') . ' ' . $SelectedGLAccountName . ' ' . _('GL Account') . '</a></div>
		<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="SelectedGLAccount" value="' . $SelectedGLAccount . '" />';

	$SQL = "SELECT glaccountusers.userid,
					canview,
					canupd,
					www_users.realname
			FROM glaccountusers INNER JOIN www_users
			ON glaccountusers.userid=www_users.userid
			WHERE glaccountusers.accountcode='" . $SelectedGLAccount . "'
			ORDER BY glaccountusers.userid ASC";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="6"><h3>' . _('Authorised users for GL Acccount') . ': ' . $SelectedGLAccountName . '</h3></th>
		</tr>';
	echo '<tr>
			<th>' . _('User Code') . '</th>
			<th>' . _('User Name') . '</th>
			<th>' . _('View') . '</th>
			<th>' . _('Update') . '</th>
		</tr>';

	$k = 0; //row colour counter

	while ($MyRow = DB_fetch_array($Result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		if ($MyRow['canupd'] == 1) {
			$ToggleText = '<td><a href="%s?SelectedUser=%s&amp;ToggleUpdate=0&amp;SelectedGLAccount=' . $SelectedGLAccount . '" onclick="return confirm(\'' . _('Are you sure you wish to remove Update for this user?') . '\');">' . _('Remove Update') . '</a></td>';
		} else {
			$ToggleText = '<td><a href="%s?SelectedUser=%s&amp;ToggleUpdate=1&amp;SelectedGLAccount=' . $SelectedGLAccount . '" onclick="return confirm(\'' . _('Are you sure you wish to add Update for this user?') . '\');">' . _('Add Update') . '</a></td>';
		}

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>' .
				$ToggleText . '
				<td><a href="%s?SelectedUser=%s&amp;delete=yes&amp;SelectedGLAccount=' . $SelectedGLAccount . '" onclick="return confirm(\'' . _('Are you sure you wish to un-authorise this user?') . '\');">' . _('Un-authorise') . '</a></td>
				</tr>',
				$MyRow['userid'],
				$MyRow['realname'],
				$MyRow['canview'],
				$MyRow['canupd'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'),
				$MyRow['userid'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'),
				$MyRow['userid']);
	}
	//END WHILE LIST LOOP
	echo '</table>';

	if (!isset($_GET['delete'])) {


		echo '<table  class="selection">'; //Main table

		echo '<tr>
				<td>' . _('Select User') . ':</td>
				<td><select name="SelectedUser">';

		$Result = DB_query("SELECT userid,
									realname
							FROM www_users
							WHERE NOT EXISTS (SELECT glaccountusers.userid
											FROM glaccountusers
											WHERE glaccountusers.accountcode='" . $SelectedGLAccount . "'
												AND glaccountusers.userid=www_users.userid)");

		if (!isset($_POST['SelectedUser'])) {
			echo '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedUser']) and $MyRow['userid'] == $_POST['SelectedUser']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';

		} //end while loop

		echo '</select>
					</td>
				</tr>
			</table>'; // close main table
		DB_free_result($Result);

		echo '<div class="centre">
				<input type="submit" name="submit" value="' . _('Accept') . '" />
				<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
			</div>
			</form>';

	} // end if user wish to delete
}

include('includes/footer.inc');
?>

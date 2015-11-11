<?php
/* $Id: glaccountusers.php 6806 2013-09-28 05:10:46Z daintree $*/

include('includes/session.inc');
$Title = _('User Authorised GL Accounts Maintenance');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" title="' . _('User Authorised GL Accounts') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['SelectedGLAccount'])) {
	$SelectedGLAccount = mb_strtoupper($_POST['SelectedGLAccount']);
} elseif (isset($_GET['SelectedGLAccount'])) {
	$SelectedGLAccount = mb_strtoupper($_GET['SelectedGLAccount']);
} else {
	$SelectedGLAccount = '';
}

if (isset($_POST['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif (isset($_GET['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedUser);
	unset($SelectedGLAccount);
}

if (isset($_POST['Process'])) {
	if ($_POST['SelectedUser'] == '') {
		prnMsg(_('You have not selected any User'), 'error');
		echo '<br />';
		unset($SelectedUser);
		unset($_POST['SelectedUser']);
	}
}

if (isset($_POST['submit'])) {

	$InputError = 0;

	if ($_POST['SelectedGLAccount'] == '') {
		$InputError = 1;
		prnMsg(_('You have not selected an GL Account to be authorised for this user'), 'error');
		echo '<br />';
		unset($SelectedUser);
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
			prnMsg(_('The GL Account') . ' ' . $_POST['SelectedGLAccount'] . ' ' . _('is already authorised for this user'), 'error');
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
			unset($_POST['SelectedGLAccount']);
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

if (!isset($SelectedUser)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedGLAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true. These will call the same page again and allow update/input or deletion of the records*/
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">
			<tr>
				<td>' . _('Select User') . ':</td>
				<td><select name="SelectedUser">';

	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						ORDER BY userid");

	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedUser) and $MyRow['userid'] == $SelectedUser) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';

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
if (isset($_POST['process']) or isset($SelectedUser)) {
	$SQLName = "SELECT realname
			FROM www_users
			WHERE userid='" . $SelectedUser . "'";
	$Result = DB_query($SQLName);
	$MyRow = DB_fetch_array($Result);
	$SelectedUserName = $MyRow['realname'];

	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Authorised GL Accounts for') . ' ' . $SelectedUserName . '</a></div>
		<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';

	$SQL = "SELECT glaccountusers.accountcode,
					canview,
					canupd,
					chartmaster.accountname
			FROM glaccountusers INNER JOIN chartmaster
			ON glaccountusers.accountcode=chartmaster.accountcode
			WHERE glaccountusers.userid='" . $SelectedUser . "'
			ORDER BY chartmaster.accountcode ASC";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="6"><h3>' . _('Authorised GL Accounts for User') . ': ' . $SelectedUserName . '</h3></th>
		</tr>';
	echo '<tr>
			<th>' . _('Code') . '</th>
			<th>' . _('Name') . '</th>
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
			$ToggleText = '<td><a href="%s?SelectedGLAccount=%s&amp;ToggleUpdate=0&amp;SelectedUser=' . $SelectedUser . '" onclick="return confirm(\'' . _('Are you sure you wish to remove Update for this GL Account?') . '\');">' . _('Remove Update') . '</a></td>';
		} else {
			$ToggleText = '<td><a href="%s?SelectedGLAccount=%s&amp;ToggleUpdate=1&amp;SelectedUser=' . $SelectedUser . '" onclick="return confirm(\'' . _('Are you sure you wish to add Update for this GL Account?') . '\');">' . _('Add Update') . '</a></td>';
		}

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>' .
				$ToggleText . '
				<td><a href="%s?SelectedGLAccount=%s&amp;delete=yes&amp;SelectedUser=' . $SelectedUser . '" onclick="return confirm(\'' . _('Are you sure you wish to un-authorise this GL Account?') . '\');">' . _('Un-authorise') . '</a></td>
				</tr>',
				$MyRow['accountcode'],
				$MyRow['accountname'],
				$MyRow['canview'],
				$MyRow['canupd'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'),
				$MyRow['accountcode'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'),
				$MyRow['accountcode']);
	}
	//END WHILE LIST LOOP
	echo '</table>';

	if (!isset($_GET['delete'])) {


		echo '<table  class="selection">'; //Main table

		echo '<tr>
				<td>' . _('Select GL Account') . ':</td>
				<td><select name="SelectedGLAccount">';

		$Result = DB_query("SELECT accountcode,
									accountname
							FROM chartmaster
							WHERE NOT EXISTS (SELECT glaccountusers.accountcode
											FROM glaccountusers
											WHERE glaccountusers.userid='" . $SelectedUser . "'
												AND glaccountusers.accountcode=chartmaster.accountcode)
							ORDER BY accountcode");

		if (!isset($_POST['SelectedGLAccount'])) {
			echo '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedGLAccount']) and $MyRow['accountcode'] == $_POST['SelectedGLAccount']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['accountcode'] . '">' . $MyRow['accountcode'] .' - '. $MyRow['accountname'] . '</option>';

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

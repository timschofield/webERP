<?php

/* Maintenance of GL Accounts allowed for a user. */

require(__DIR__ . '/includes/session.php');

$Title = __('GL Account Authorised Users');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountUsers';
include('includes/header.php');

if(isset($_POST['SelectedGLAccount']) and $_POST['SelectedGLAccount']<>'') {//If POST not empty:
	$SelectedGLAccount = mb_strtoupper($_POST['SelectedGLAccount']);
} elseif(isset($_GET['SelectedGLAccount']) and $_GET['SelectedGLAccount']<>'') {//If GET not empty:
	$SelectedGLAccount = mb_strtoupper($_GET['SelectedGLAccount']);
} else {// Unset empty SelectedGLAccount:
	unset($_GET['SelectedGLAccount']);
	unset($_POST['SelectedGLAccount']);
	unset($SelectedGLAccount);
}

if(isset($_POST['SelectedUser']) and $_POST['SelectedUser']<>'') {//If POST not empty:
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif(isset($_GET['SelectedUser']) and $_GET['SelectedGLAccount']<>'') {//If GET not empty:
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
} else {// Unset empty SelectedUser:
	unset($_GET['SelectedUser']);
	unset($_POST['SelectedUser']);
	unset($SelectedUser);
}

if(isset($_POST['Cancel']) or isset($_GET['Cancel'] )) {
	unset($SelectedGLAccount);
	unset($SelectedUser);
}


if(!isset($SelectedGLAccount)) {// If is NOT set a GL account for users.

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedUser will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true. These will call the same page again and allow update/input or deletion of the records*/

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="',// Icon image.
		__('GL Account Authorised Users'), '" /> ',// Icon title.
		__('GL Account Authorised Users'), '</p>';// Page title.
	if(isset($_POST['Process'])) {
		prnMsg(__('You have not selected any GL Account'), 'error');
	}
	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<fieldset>
			<legend>', __('GL Account'), '</legend>
			<field>
				<label for="SelectedGLAccount">', __('Select GL Account'), ':</label>
				<select name="SelectedGLAccount" onchange="this.form.submit()">',// Submit when the value of the select is changed.
					'<option value="">', __('Not Yet Selected'), '</option>';
	$Result = DB_query("
		SELECT
			accountcode,
			accountname
		FROM chartmaster
		ORDER BY accountcode");
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option ';
		if(isset($SelectedGLAccount) and $MyRow['accountcode'] == $SelectedGLAccount) {
			echo 'selected="selected" ';
		}
		echo 'value="', $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . $MyRow['accountname'] . '</option>';
	}// End while loop.
	echo '</select>
		</field>
	</fieldset>';//Close Select_GL_Account table.
	DB_free_result($Result);
	echo	'<div class="centre noPrint">',// Form buttons:
				'<button name="Process" type="submit" value="Submit"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" /> ', __('Accept'), '</button> '; // "Accept" button.

} else {// If is set a GL account for users ($SelectedGLAccount).
	$Result = DB_query("
		SELECT accountname
		FROM chartmaster
		WHERE accountcode='" . $SelectedGLAccount . "'");
	$MyRow = DB_fetch_array($Result);
	$SelectedGLAccountName = $MyRow['accountname'];
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="',// Icon image.
		__('GL Account Authorised Users'), '" /> ',// Icon title.
		__('Authorised Users for'), ' ', $SelectedGLAccountName, '</p>';// Page title.

	// BEGIN: Needs $SelectedGLAccount, $SelectedUser.
	if(isset($_POST['submit'])) {
		if(!isset($SelectedUser)) {
			prnMsg(__('You have not selected an user to be authorised to use this GL Account'), 'error');
		} else {
			// First check the user is not being duplicated
			$CheckResult = DB_query("
				SELECT count(*)
				FROM glaccountusers
				WHERE accountcode= '" . $SelectedGLAccount . "'
				AND userid = '" . $SelectedUser . "'");
			$CheckRow = DB_fetch_row($CheckResult);

			if($CheckRow[0] > 0) {
				prnMsg(__('The user') . ' ' . $SelectedUser . ' ' . __('is already authorised to use this GL Account'), 'error');
			} else {
				// Add new record on submit
				$SQL = "INSERT INTO glaccountusers (
						accountcode,
						userid,
						canview,
						canupd
					) VALUES ('" .
						$SelectedGLAccount . "','" .
						$SelectedUser . "',
						'1',
						'1')";
				$ErrMsg = __('An access permission for a user could not be added');
				if(DB_query($SQL, $ErrMsg)) {
					prnMsg(__('An access permission for a user was added') . '. ' . __('GL Account') . ': ' . $SelectedGLAccount . '. ' . __('User') . ': ' . $SelectedUser . '.', 'success');
					unset($_GET['SelectedUser']);
					unset($_POST['SelectedUser']);
				}
			}
		}
	} elseif(isset($_GET['delete'])) {
		$SQL = "DELETE FROM glaccountusers
			WHERE accountcode='" . $SelectedGLAccount . "'
			AND userid='" . $SelectedUser . "'";
		$ErrMsg = __('An access permission for a user could not be removed');
		if(DB_query($SQL, $ErrMsg)) {
			prnMsg(__('An access permission for a user was removed') . '. ' . __('GL Account') . ': ' . $SelectedGLAccount . '. ' . __('User') . ': ' . $SelectedUser . '.', 'success');
			unset($_GET['delete']);
			unset($_POST['delete']);
		}
	} elseif(isset($_GET['ToggleUpdate'])) {
		$SQL = "UPDATE glaccountusers
				SET canupd='" . $_GET['ToggleUpdate'] . "'
				WHERE accountcode='" . $SelectedGLAccount . "'
				AND userid='" . $SelectedUser . "'";
		$ErrMsg = __('An access permission to update a GL account could not be modified');
		if(DB_query($SQL, $ErrMsg)) {
			prnMsg(__('An access permission to update a GL account was modified') . '. ' . __('GL Account') . ': ' . $SelectedGLAccount . '. ' . __('User') . ': ' . $SelectedUser . '.', 'success');
			unset($_GET['ToggleUpdate']);
			unset($_POST['ToggleUpdate']);
		}
	}
	// END: Needs $SelectedGLAccount, $SelectedUser.

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="text">', __('User Code'), '</th>
			<th class="text">', __('User Name'), '</th>
			<th class="centre">', __('View'), '</th>
			<th class="centre">', __('Update'), '</th>
			<th class="noPrint" colspan="2">&nbsp;</th>
		</tr>
		</thead><tbody>';
	$Result = DB_query("
		SELECT
			glaccountusers.userid,
			canview,
			canupd,
			www_users.realname
		FROM glaccountusers INNER JOIN www_users
		ON glaccountusers.userid=www_users.userid
		WHERE glaccountusers.accountcode='" . $SelectedGLAccount . "'
		ORDER BY glaccountusers.userid ASC");
	if(DB_num_rows($Result)>0) {// If the GL account has access permissions for one or more users:
		while($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
				<td class="text">', $MyRow['userid'], '</td>
				<td class="text">', $MyRow['realname'], '</td>
				<td class="centre">';
			if($MyRow['canview'] == 1) {
				echo __('Yes');
			} else {
				echo __('No');
			}
			echo '</td>
				<td class="centre">';

			$ScriptNameEscaped = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
			if($MyRow['canupd'] == 1) {
				echo __('Yes'), '</td>',
					'<td class="noPrint"><a href="', $ScriptNameEscaped, '?SelectedGLAccount=', $SelectedGLAccount, '&amp;SelectedUser=', $MyRow['userid'], '&amp;ToggleUpdate=0" onclick="return confirm(\'', __('Are you sure you wish to remove Update for this user?'), '\');">', __('Remove Update');
			} else {
				echo __('No'), '</td>',
					'<td class="noPrint"><a href="', $ScriptNameEscaped, '?SelectedGLAccount=', $SelectedGLAccount, '&amp;SelectedUser=', $MyRow['userid'], '&amp;ToggleUpdate=1" onclick="return confirm(\'', __('Are you sure you wish to add Update for this user?'), '\');">', __('Add Update');
			}
			echo	'</a></td>',
					'<td class="noPrint"><a href="', $ScriptNameEscaped, '?SelectedGLAccount=', $SelectedGLAccount, '&amp;SelectedUser=', $MyRow['userid'], '&amp;delete=yes" onclick="return confirm(\'', __('Are you sure you wish to un-authorise this user?'), '\');">', __('Un-authorise'), '</a></td>',
				'</tr>';
		}// End while list loop.
	} else {// If the GL account does not have access permissions for users:
		echo '<tr><td class="centre" colspan="6">', __('GL account does not have access permissions for users'), '</td></tr>';
	}
	echo '</tbody></table>
		<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<input name="SelectedGLAccount" type="hidden" value="', $SelectedGLAccount, '" />';

	echo '<fieldset>
			<legend>', __('Select User'), '</legend>
			<field>';
	$Result = DB_query("
		SELECT
			userid,
			realname
		FROM www_users
		WHERE NOT EXISTS (SELECT glaccountusers.userid
		FROM glaccountusers
		WHERE glaccountusers.accountcode='" . $SelectedGLAccount . "'
			AND glaccountusers.userid=www_users.userid)
		ORDER BY userid");
	if(DB_num_rows($Result)>0) {// If the GL account does not have access permissions for one or more users:
		echo '<label for="SelectedUser">', __('Add access permissions to a user'), ':</label>
				<select name="SelectedUser">';
		if(!isset($_POST['SelectedUser'])) {
			echo '<option selected="selected" value="">', __('Not Yet Selected'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if(isset($_POST['SelectedUser']) and $MyRow['userid'] == $_POST['SelectedUser']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		}
		echo '</select>';
		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="submit" value="Accept" />
			</div>';
	} else {// If the GL account has access permissions for all users:
		echo __('GL account has access permissions for all users');
	}
	DB_free_result($Result);
	echo '<div class="centre noPrint">', // Form buttons:
			'<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" /> ', __('Print'), '</button>', // "Print" button.
			'<button formaction="GLAccountUsers.php?Cancel" type="submit"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" /> ', __('Select A Different GL account'), '</button>'; // "Select A Different GL account" button.
}
echo		'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>', // "Return" button.
		'</div>
	</form>';

include('includes/footer.php');

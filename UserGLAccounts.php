<?php

/* Maintenance of GL Accounts allowed for a user. */

require(__DIR__ . '/includes/session.php');

$Title = __('User Authorised GL Accounts');
$ViewTopic = 'GeneralLedger';
$BookMark = 'UserGLAccounts';
include('includes/header.php');

if(isset($_POST['SelectedUser']) and $_POST['SelectedUser']<>'') {//If POST not empty:
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif(isset($_GET['SelectedUser']) and $_GET['SelectedUser']<>'') {//If GET not empty:
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
} else {// Unset empty SelectedUser:
	unset($_GET['SelectedUser']);
	unset($_POST['SelectedUser']);
	unset($SelectedUser);
}

if(isset($_POST['SelectedGLAccount']) and $_POST['SelectedGLAccount']<>'') {//If POST not empty:
	$SelectedGLAccount = mb_strtoupper($_POST['SelectedGLAccount']);
} elseif(isset($_GET['SelectedGLAccount']) and $_GET['SelectedGLAccount']<>'') {//If GET not empty:
	$SelectedGLAccount = mb_strtoupper($_GET['SelectedGLAccount']);
} else {// Unset empty SelectedGLAccount:
	unset($_GET['SelectedGLAccount']);
	unset($_POST['SelectedGLAccount']);
	unset($SelectedGLAccount);
}

if(isset($_GET['Cancel']) or isset($_POST['Cancel'])) {
	unset($SelectedUser);
	unset($SelectedGLAccount);
}


if(!isset($SelectedUser)) {// If is NOT set a user for GL accounts.
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/gl.png" title="',// Icon image.
		__('User Authorised GL Accounts'), '" /> ',// Icon title.
		__('User Authorised GL Accounts'), '</p>';// Page title.

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedGLAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true. These will call the same page again and allow update/input or deletion of the records.*/

	if(isset($_POST['Process'])) {
		prnMsg(__('You have not selected any user'), 'error');
	}
	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<fieldset>
			<legend>', __('User Selection'), '</legend>
			<field>
				<label for="SelectedUser">', __('Select User'), ':</label>
				<select name="SelectedUser" onchange="this.form.submit()">',// Submit when the value of the select is changed.
					'<option value="">', __('Not Yet Selected'), '</option>';
	$Result = DB_query("
		SELECT
			userid,
			realname
		FROM www_users
		ORDER BY userid");
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option ';
		if(isset($SelectedUser) and $MyRow['userid'] == $SelectedUser) {
			echo 'selected="selected" ';
		}
		echo 'value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
	}// End while loop.
	echo '</select>
		</field>
	</fieldset>';//Close Select_User table.

	DB_free_result($Result);

	echo	'<div class="centre noPrint">',// Form buttons:
				'<button name="Process" type="submit" value="Submit"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/user.png" /> ', __('Accept'), '</button> '; // "Accept" button.

} else {// If is set a user for GL accounts ($SelectedUser).
	$Result = DB_query("
		SELECT realname
		FROM www_users
		WHERE userid='" . $SelectedUser . "'");
	$MyRow = DB_fetch_array($Result);
	$SelectedUserName = $MyRow['realname'];
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/gl.png" title="',// Icon image.
		__('User Authorised GL Accounts'), '" /> ',// Icon title.
		__('Authorised GL Accounts for'), ' ', $SelectedUserName, '</p>';// Page title.

	// BEGIN: Needs $SelectedUser, $SelectedGLAccount:
	if(isset($_POST['submit'])) {
		if(!isset($SelectedGLAccount)) {
			prnMsg(__('You have not selected an GL Account to be authorised for this user'), 'error');
		} else {
			// First check the user is not being duplicated
			$CheckResult = DB_query("
				SELECT count(*)
				FROM glaccountusers
				WHERE accountcode= '" . $SelectedGLAccount . "'
				AND userid = '" . $SelectedUser . "'");
			$CheckRow = DB_fetch_row($CheckResult);
			if($CheckRow[0] > 0) {
				prnMsg(__('The GL Account') . ' ' . $SelectedGLAccount . ' ' . __('is already authorised for this user'), 'error');
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
				$ErrMsg = __('An access permission to a GL account could not be added');
				if(DB_query($SQL, $ErrMsg)) {
					prnMsg(__('An access permission to a GL account was added') . '. ' . __('User') . ': ' . $SelectedUser . '. ' . __('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
					unset($_GET['SelectedGLAccount']);
					unset($_POST['SelectedGLAccount']);
				}
			}
		}
	} elseif(isset($_GET['delete']) or isset($_POST['delete'])) {
		$SQL = "DELETE FROM glaccountusers
			WHERE accountcode='" . $SelectedGLAccount . "'
			AND userid='" . $SelectedUser . "'";
		$ErrMsg = __('An access permission to a GL account could not be removed');
		if(DB_query($SQL, $ErrMsg)) {
			prnMsg(__('An access permission to a GL account was removed') . '. ' . __('User') . ': ' . $SelectedUser . '. ' . __('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
			unset($_GET['delete']);
			unset($_POST['delete']);
		}
	} elseif(isset($_GET['ToggleUpdate']) or isset($_POST['ToggleUpdate'])) {// Can update (write) GL accounts flag.
		if(isset($_GET['ToggleUpdate']) and $_GET['ToggleUpdate']<>'') {//If GET not empty.
			$ToggleUpdate = $_GET['ToggleUpdate'];
		} elseif(isset($_POST['ToggleUpdate']) and $_POST['ToggleUpdate']<>'') {//If POST not empty.
			$ToggleUpdate = $_POST['ToggleUpdate'];
		}
		$SQL = "UPDATE glaccountusers
				SET canupd='" . $ToggleUpdate . "'
				WHERE accountcode='" . $SelectedGLAccount . "'
				AND userid='" . $SelectedUser . "'";
		$ErrMsg = __('An access permission to update a GL account could not be modified');
		if(DB_query($SQL, $ErrMsg)) {
			prnMsg(__('An access permission to update a GL account was modified') . '. ' . __('User') . ': ' . $SelectedUser . '. ' . __('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
			unset($_GET['ToggleUpdate']);
			unset($_POST['ToggleUpdate']);
		}
	}
// END: Needs $SelectedUser, $SelectedGLAccount.

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="text">', __('Code'), '</th>
			<th class="text">', __('Name'), '</th>
			<th class="centre">', __('View'), '</th>
			<th class="centre">', __('Update'), '</th>
			<th class="noPrint" colspan="2">&nbsp;</th>
		</tr>
		</thead><tbody>';
	$Result = DB_query("
		SELECT
			glaccountusers.accountcode,
			canview,
			canupd,
			chartmaster.accountname
		FROM glaccountusers INNER JOIN chartmaster
		ON glaccountusers.accountcode=chartmaster.accountcode
		WHERE glaccountusers.userid='" . $SelectedUser . "'
		ORDER BY chartmaster.accountcode ASC");
	if(DB_num_rows($Result)>0) {// If the user has access permissions to one or more GL accounts:
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
				<td class="text">', $MyRow['accountcode'], '</td>
				<td class="text">', $MyRow['accountname'], '</td>
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
					'<td class="noPrint"><a href="', $ScriptNameEscaped, '?SelectedUser=', $SelectedUser, '&amp;SelectedGLAccount=', $MyRow['accountcode'], '&amp;ToggleUpdate=0" onclick="return confirm(\'', __('Are you sure you wish to remove Update for this GL Account?'), '\');">', __('Remove Update');
			} else {
				echo __('No'), '</td>',
					'<td class="noPrint"><a href="', $ScriptNameEscaped, '?SelectedUser=', $SelectedUser, '&amp;SelectedGLAccount=', $MyRow['accountcode'], '&amp;ToggleUpdate=1" onclick="return confirm(\'', __('Are you sure you wish to add Update for this GL Account?'), '\');">', __('Add Update');
			}
			echo	'</a></td>',
					'<td class="noPrint"><a href="', $ScriptNameEscaped, '?SelectedUser=', $SelectedUser, '&amp;SelectedGLAccount=', $MyRow['accountcode'], '&amp;delete=yes" onclick="return confirm(\'', __('Are you sure you wish to un-authorise this GL Account?'), '\');">', __('Un-authorise'), '</a></td>',
				'</tr>';
		}// End while list loop.
	} else {// If the user does not have access permissions to GL accounts:
		echo '<tr><td class="centre" colspan="6">', __('User does not have access permissions to GL accounts'), '</td></tr>';
	}
	echo '</tbody></table>',
		'<br />',
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<input name="SelectedUser" type="hidden" value="', $SelectedUser, '" />',
		'<br />
		<table class="selection noPrint">
			<tr>
				<td>';
	$Result = DB_query("
		SELECT
			accountcode,
			accountname
		FROM chartmaster
		WHERE NOT EXISTS (SELECT glaccountusers.accountcode
		FROM glaccountusers
		WHERE glaccountusers.userid='" . $SelectedUser . "'
			AND glaccountusers.accountcode=chartmaster.accountcode)
		ORDER BY accountcode");
	if(DB_num_rows($Result)>0) {// If the user does not have access permissions to one or more GL accounts:
		echo	__('Add access permissions to a GL account'), ':</td>
				<td><select name="SelectedGLAccount">';
		if(!isset($_POST['SelectedGLAccount'])) {
			echo '<option selected="selected" value="">', __('Not Yet Selected'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if(isset($_POST['SelectedGLAccount']) and $MyRow['accountcode'] == $_POST['SelectedGLAccount']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', $MyRow['accountname'], '</option>';
		}
		echo	'</select></td>
				<td><input type="submit" name="submit" value="Accept" />';
	} else {// If the user has access permissions to all GL accounts:
		echo __('User has access permissions to all GL accounts');
	}
	echo		'</td>
			</tr>
		</table>';
	DB_free_result($Result);
	echo '<br>',
		'<div class="centre noPrint">', // Form buttons:
			'<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', __('Print'), '</button>', // "Print" button.
			'<button formaction="UserGLAccounts.php?Cancel" type="submit"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/user.png" /> ', __('Select A Different User'), '</button>'; // "Select A Different User" button.
}
echo		'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/return.svg" /> ', __('Return'), '</button>', // "Return" button.
		'</div>
	</form>';

include('includes/footer.php');

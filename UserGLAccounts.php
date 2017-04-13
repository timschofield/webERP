<?php
/* $Id: UserGLAccounts.php 7427 2015-12-27 19:59:10Z rchacon $*/
/* Maintenance of GL Accounts allowed for a user. */

include('includes/session.php');
$Title = _('User Authorised GL Accounts');
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
		_('User Authorised GL Accounts'), '" /> ',// Icon title.
		_('User Authorised GL Accounts'), '</p>';// Page title.

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedGLAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true. These will call the same page again and allow update/input or deletion of the records.*/

	if(isset($_POST['Process'])) {
		prnMsg(_('You have not selected any user'), 'error');
	}
	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<table class="selection">
			<tr>
				<td>', _('Select User'), ':</td>
				<td><select name="SelectedUser" onchange="this.form.submit()">',// Submit when the value of the select is changed.
					'<option value="">', _('Not Yet Selected'), '</option>';
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
	echo '</select></td>
			</tr>
		</table>';//Close Select_User table.

	DB_free_result($Result);

	echo	'<div class="centre noprint">',// Form buttons:
				'<button name="Process" type="submit" value="Submit"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/user.png" /> ', _('Accept'), '</button> '; // "Accept" button.

} else {// If is set a user for GL accounts ($SelectedUser).
	$Result = DB_query("
		SELECT realname
		FROM www_users
		WHERE userid='" . $SelectedUser . "'");
	$MyRow = DB_fetch_array($Result);
	$SelectedUserName = $MyRow['realname'];
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/gl.png" title="',// Icon image.
		_('User Authorised GL Accounts'), '" /> ',// Icon title.
		_('Authorised GL Accounts for'), ' ', $SelectedUserName, '</p>';// Page title.

	// BEGIN: Needs $SelectedUser, $SelectedGLAccount:
	if(isset($_POST['submit'])) {
		if(!isset($SelectedGLAccount)) {
			prnMsg(_('You have not selected an GL Account to be authorised for this user'), 'error');
		} else {
			// First check the user is not being duplicated
			$CheckResult = DB_query("
				SELECT count(*)
				FROM glaccountusers
				WHERE accountcode= '" . $SelectedGLAccount . "'
				AND userid = '" . $SelectedUser . "'");
			$CheckRow = DB_fetch_row($CheckResult);
			if($CheckRow[0] > 0) {
				prnMsg(_('The GL Account') . ' ' . $SelectedGLAccount . ' ' . _('is already authorised for this user'), 'error');
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
				$ErrMsg = _('An access permission to a GL account could not be added');
				if(DB_query($SQL, $ErrMsg)) {
					prnMsg(_('An access permission to a GL account was added') . '. ' . _('User') . ': ' . $SelectedUser . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
					unset($_GET['SelectedGLAccount']);
					unset($_POST['SelectedGLAccount']);
				}
			}
		}
	} elseif(isset($_GET['delete']) or isset($_POST['delete'])) {
		$SQL = "DELETE FROM glaccountusers
			WHERE accountcode='" . $SelectedGLAccount . "'
			AND userid='" . $SelectedUser . "'";
		$ErrMsg = _('An access permission to a GL account could not be removed');
		if(DB_query($SQL, $ErrMsg)) {
			prnMsg(_('An access permission to a GL account was removed') . '. ' . _('User') . ': ' . $SelectedUser . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
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
		$ErrMsg = _('An access permission to update a GL account could not be modified');
		if(DB_query($SQL, $ErrMsg)) {
			prnMsg(_('An access permission to update a GL account was modified') . '. ' . _('User') . ': ' . $SelectedUser . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
			unset($_GET['ToggleUpdate']);
			unset($_POST['ToggleUpdate']);
		}
	}
// END: Needs $SelectedUser, $SelectedGLAccount.

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="text">', _('Code'), '</th>
			<th class="text">', _('Name'), '</th>
			<th class="centre">', _('View'), '</th>
			<th class="centre">', _('Update'), '</th>
			<th class="noprint" colspan="2">&nbsp;</th>
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
		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '<td class="text">', $MyRow['accountcode'], '</td>
				<td class="text">', $MyRow['accountname'], '</td>
				<td class="centre">';
			if($MyRow['canview'] == 1) {
				echo _('Yes');
			} else {
				echo _('No');
			}
			echo '</td>
				<td class="centre">';

			$ScriptName = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
			if($MyRow['canupd'] == 1) {
				echo _('Yes'), '</td>',
					'<td class="noprint"><a href="', $ScriptName, '?SelectedUser=', $SelectedUser, '&amp;SelectedGLAccount=', $MyRow['accountcode'], '&amp;ToggleUpdate=0" onclick="return confirm(\'', _('Are you sure you wish to remove Update for this GL Account?'), '\');">', _('Remove Update');
			} else {
				echo _('No'), '</td>',
					'<td class="noprint"><a href="', $ScriptName, '?SelectedUser=', $SelectedUser, '&amp;SelectedGLAccount=', $MyRow['accountcode'], '&amp;ToggleUpdate=1" onclick="return confirm(\'', _('Are you sure you wish to add Update for this GL Account?'), '\');">', _('Add Update');
			}
			echo	'</a></td>',
					'<td class="noprint"><a href="', $ScriptName, '?SelectedUser=', $SelectedUser, '&amp;SelectedGLAccount=', $MyRow['accountcode'], '&amp;delete=yes" onclick="return confirm(\'', _('Are you sure you wish to un-authorise this GL Account?'), '\');">', _('Un-authorise'), '</a></td>',
				'</tr>';
		}// End while list loop.
	} else {// If the user does not have access permissions to GL accounts:
		echo '<tr><td class="centre" colspan="6">', _('User does not have access permissions to GL accounts'), '</td></tr>';
	}
	echo '</tbody></table>',
		'<br />',
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<input name="SelectedUser" type="hidden" value="', $SelectedUser, '" />',
		'<br />
		<table class="selection noprint">
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
		echo	_('Add access permissions to a GL account'), ':</td>
				<td><select name="SelectedGLAccount">';
		if(!isset($_POST['SelectedGLAccount'])) {
			echo '<option selected="selected" value="">', _('Not Yet Selected'), '</option>';
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
		echo _('User has access permissions to all GL accounts');
	}
	echo		'</td>
			</tr>
		</table>';
	DB_free_result($Result);
	echo '<br>',
		'<div class="centre noprint">', // Form buttons:
			'<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', _('Print'), '</button>', // "Print" button.
			'<button formaction="UserGLAccounts.php?Cancel" type="submit"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/user.png" /> ', _('Select A Different User'), '</button>'; // "Select A Different User" button.
}
echo		'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/return.svg" /> ', _('Return'), '</button>', // "Return" button.
		'</div>
	</form>';

include('includes/footer.php');
?>

<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Copy Authority of GL Accounts from one user to another');
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="',// Icon image.
	$Title, '" /> ',// Icon title.
	$Title, '</p>';// Page title.

if(isset($_POST['ProcessCopyAuthority'])) {

	$InputError = 0;

	if($_POST['FromUserID'] == $_POST['ToUserID']) {
		prnMsg(__('User FROM must be different from user TO'), 'error');
		$InputError = 1;
	}

	if($InputError == 0) {// no input errors
		DB_Txn_Begin();

		$SQL = "DELETE FROM glaccountusers WHERE UPPER(userid) = UPPER('" . $_POST['ToUserID'] . "')";
		$ErrMsg = __('The SQL to delete the auhority in glaccountusers record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		prnMsg(__('Deleting the previous authority to view / update the GL Accounts of user') . ' ' . $_POST['ToUserID'], 'success');

		$SQL = "INSERT INTO glaccountusers (userid, accountcode, canview, canupd)
				SELECT '" . $_POST['ToUserID'] . "', accountcode, canview, canupd
				FROM glaccountusers
				WHERE UPPER(userid) = UPPER('" . $_POST['FromUserID'] . "')";

		$ErrMsg = __('The SQL to insert the auhority in glaccountusers record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		prnMsg(__('Copied the authority to view / update the GL Accounts from user') . ' ' . $_POST['FromUserID'] . ' ' . __('to user') . ' ' . $_POST['ToUserID'], 'success');

		DB_Txn_Commit();

	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
        <legend>' . __('Copy GL Account Authority') . '</legend>';

echo '<field>';
echo '<label for="FromUserID">' . __('Select User to copy the Authority FROM') . ':</label>';
echo '<select id="FromUserID" name="FromUserID">';

if($_SESSION['AccessLevel'] == 8) {
	// if system admin can access to anyone.
	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						ORDER BY userid");
} else {
	// if not system admin, can not access to system admin role. To prevent rogue employees playing with sys admin rights ;-)
	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						WHERE fullaccess != '8'
						ORDER BY userid");
}

echo '<option selected value="">' . __('Not Yet Selected') . '</option>';
while($MyRow = DB_fetch_array($Result)) {
	echo '<option value="';
	echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';
} //end while loop
echo '</select>';
echo '</field>';

echo '<field>';
echo '<label for="ToUserID">' . __('Select User to copy the Authority TO') . ':</label>';
echo '<select id="ToUserID" name="ToUserID">';

if($_SESSION['AccessLevel'] == 8) {
	// if system admin can access to anyone.
	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						ORDER BY userid");
} else {
	// if not system admin, can not access to system admin role. To prevent rogue employees playing with sys admin rights ;-)
	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						WHERE fullaccess != '8'
						ORDER BY userid");
}

echo '<option selected value="">' . __('Not Yet Selected') . '</option>';
while($MyRow = DB_fetch_array($Result)) {
	echo '<option value="';
	echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';
} //end while loop
echo '</select>';
echo '</field>';

echo '</fieldset>';
echo '<div class="centre"><input type="submit" name="ProcessCopyAuthority" value="' . __('Process Copy of Authority') . '" />
	</div>
	</form>';

include('includes/footer.php');

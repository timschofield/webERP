<?php
// Z_GLAccountUsersCopyAuthority.php
// Utility to copy authority of GL accounts from one user to another.

include('includes/session.php');
$Title = _('GLAccount - Users Authority Copy Authority');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_GLAccountUsersCopyAuthority';

include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/maintenance.png" title="',// Icon image.
	_('Copy Authority of GL Accounts from one user to another'), '" /> ',// Icon title.
	_('Copy Authority of GL Accounts from one user to another'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.php');

if(isset($_POST['ProcessCopyAuthority'])) {

	$InputError =0;

	if($_POST['FromUserID']==$_POST['ToUserID']) {
		prnMsg(_('User FROM must be different from user TO'),'error');
		$InputError =1;
	}

	if($InputError ==0) {// no input errors
		DB_Txn_Begin();

		echo '<br />' . _('Deleting the current authority to view / update the GL Accounts of user') . ' ' .  $_POST['ToUserID'];
		$SQL = "DELETE FROM glaccountusers WHERE userid = '" . $_POST['ToUserID'] . "'";
		$ErrMsg =_('The SQL to delete the auhority in glaccountusers record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Copying the authority to view / update the GL Accounts from user') . ' ' . $_POST['FromUserID'] . ' ' . _('to') . ' ' . $_POST['ToUserID'];
		$SQL = "INSERT INTO glaccountusers (userid, accountcode, canview, canupd)
						SELECT '" . $_POST['ToUserID'] . "', accountcode, canview, canupd
						FROM glaccountusers
						WHERE userid = '" . $_POST['FromUserID'] . "'";

		$ErrMsg =_('The SQL to insert the auhority in glaccountusers record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . _('completed');
		echo '<br />';
		DB_Txn_Commit();

	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', _('Copy Authorities'), '</legend>';
echo ' <field>
		<label>' . _('Select User to copy the Authority FROM') . ':</label>
		<select name="FromUserID">';
$Result = DB_query("SELECT userid,
							realname
					FROM www_users
					ORDER BY userid");

echo '<option selected value="">' . _('Not Yet Selected') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="';
	echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';
} //end while loop
echo '</select>
	</field>';

echo ' <field>
		<label>' . _('Select User to copy the Authority TO') . ':</label>
		<select name="ToUserID">';
$Result = DB_query("SELECT userid,
							realname
					FROM www_users
					ORDER BY userid");

echo '<option selected value="">' . _('Not Yet Selected') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="';
	echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';
} //end while loop
echo '</select>
	</field>';
echo '</fieldset>
		<div class="centre">
			<button name="ProcessCopyAuthority" type="submit" value="', _('Process Copy of Authority'), '"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/tick.svg" /> ', _('Process'), '</button>', // "Process Copy of Authority" button.
			'<button formaction="' . $RootPath . '/index.php?Application=Utilities" type="submit"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/return.svg" /> ', _('Return'), '</button>', // "Return" button.
		'</div>
	</form>';

include('includes/footer.php');

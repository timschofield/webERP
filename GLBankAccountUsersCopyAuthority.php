<?php

include ('includes/session.php');
$Title = _('Bank Accounts - Users Authority Copy Authority');// Screen identificator.
include('includes/header.php');
include('includes/KLEmails.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="',// Icon image.
	_('Copy Authority of Bank Accounts from one user to another'), '" /> ',// Icon title.
	_('Copy Authority of Bank Accounts from one user to another'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['ProcessCopyAuthority'])) {

	$InputError =0;
	
	if($_POST['FromUserID']==$_POST['ToUserID']) {
		prnMsg(_('User FROM must be different from user TO'),'error');
		$InputError =1;
	}
	
	if($InputError ==0) {// no input errors
		$Result = DB_Txn_Begin();

		echo '<br />' . _('Deleting the current authority to view / update the Bank Accounts of user') . ' ' .  $_POST['ToUserID'];
		$SQL = "DELETE FROM bankaccountusers WHERE UPPER(userid) = UPPER('" . $_POST['ToUserID'] . "')";
		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to delete the auhority in bankaccountusers record failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Copying the authority to view / update the Bank Accounts from user') . ' ' . $_POST['FromUserID'] . ' ' . _('to') . ' ' . $_POST['ToUserID'];
		$SQL = "INSERT INTO bankaccountusers (userid, accountcode)
						SELECT '" . $_POST['ToUserID'] . "', accountcode
						FROM bankaccountusers
						WHERE UPPER(userid) = UPPER('" . $_POST['FromUserID'] . "')";

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to insert the auhority in bankaccountusers record failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');
		echo '<br />';
		KLSendEmail("BankAccountUserRightsCopied", "Silent",$_SESSION['UserID'], $_POST['FromUserID'],$_POST['ToUserID']);
		$Result = DB_Txn_Commit();

	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table>';
echo ' <tr>
		<td>' . _('Select User to copy the Authority FROM') . ':</td>
		<td><select name="FromUserID">';

if ($KL_SystemAdmin){
	// if system admin can access to anyone.
	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						ORDER BY userid");
}else{
	// if not system admin, can not access to system admin role. To prevent rogue employees quicking out the sys admin ;-)
	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						WHERE fullaccess != '8'
						ORDER BY userid");
} 

echo '<option selected value="">' . _('Not Yet Selected') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="';
	echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';
} //end while loop
echo '</select></td></tr>';

echo ' <tr>
		<td>' . _('Select User to copy the Authority TO') . ':</td>
		<td><select name="ToUserID">';

if ($KL_SystemAdmin){
	// if system admin can access to anyone.
	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						ORDER BY userid");
}else{
	// if not system admin, can not access to system admin role. To prevent rogue employees quicking out the sys admin ;-)
	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						WHERE fullaccess != '8'
						ORDER BY userid");
} 

echo '<option selected value="">' . _('Not Yet Selected') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="';
	echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';
} //end while loop
echo '</select></td></tr>';
echo '</table>';
echo '<input type="submit" name="ProcessCopyAuthority" value="' . _('Process Copy of Authority') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>
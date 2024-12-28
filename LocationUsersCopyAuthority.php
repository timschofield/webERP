<?php

include ('includes/session.php');
$Title = _('Location - Users Authority Copy');// Screen identificator.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="',// Icon image.
	_('Copy Authority of Locations from one user to another'), '" /> ',// Icon title.
	_('Copy Authority of Locations from one user to another'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');
include('includes/KLEmails.php');

if(isset($_POST['ProcessCopyAuthority'])) {

	$InputError =0;
	
	if($_POST['FromUserID']==$_POST['ToUserID']) {
		prnMsg(_('User FROM must be different from user TO'),'error');
		$InputError =1;
	}
	
	if($InputError ==0) {// no input errors
		$Result = DB_Txn_Begin();

		echo '<br />' . _('Deleting the current authority to view / update the Locations of user') . ' ' .  $_POST['ToUserID'];
		$SQL = "DELETE FROM locationusers WHERE UPPER(userid) = UPPER('" . $_POST['ToUserID'] . "')";
		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to delete the auhority in locationusers record failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Copying the authority to view / update the Locations from user') . ' ' . $_POST['FromUserID'] . ' ' . _('to') . ' ' . $_POST['ToUserID'];
		$SQL = "INSERT INTO locationusers (userid, loccode, canview, canupd)
						SELECT '" . $_POST['ToUserID'] . "', loccode, canview, canupd
						FROM locationusers
						WHERE UPPER(userid) = UPPER('" . $_POST['FromUserID'] . "')";

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to insert the auhority in locationusers record failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');
		echo '<br />';
		KLSendEmail("LocationUserRightsCopied", "Silent",$_SESSION['UserID'], $_POST['FromUserID'],$_POST['ToUserID']);
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
$Result = DB_query("SELECT userid,
							realname
					FROM www_users
					ORDER BY userid");

echo '<option selected value="">' . _('Not Yet Selected') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="';
	echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';
} //end while loop
echo '</select></td></tr>';

echo ' <tr>
		<td>' . _('Select User to copy the Authority TO') . ':</td>
		<td><select name="ToUserID">';
$Result = DB_query("SELECT userid,
							realname
					FROM www_users
					ORDER BY userid");

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
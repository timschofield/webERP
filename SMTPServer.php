<?php
/* This script is <create a description for script table>. */

include('includes/session.php');
$Title = _('SMTP Server details');// Screen identification.
$ViewTopic = 'CreatingNewSystem';// Filename's id in ManualContents.php's TOC.
$BookMark = 'SMTPServer';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/email.png" title="' .// Icon image.
	_('SMTP Server') . '" /> ' .// Icon title.
	_('SMTP Server Settings') . '</p>';// Page title.
// First check if there are smtp server data or not


if (isset($_POST['submit']) AND $_POST['MailServerSetting']==1) {//If there are already data setup, Update the table
	$sql="UPDATE emailsettings SET
				host='".$_POST['Host']."',
				port='".$_POST['Port']."',
				heloaddress='".$_POST['HeloAddress']."',
				username='".$_POST['UserName']."',
				password='".$_POST['Password']."',
				auth='".$_POST['Auth']."'";

	$ErrMsg = _('The email setting information failed to update');
	$DbgMsg = _('The SQL failed to update is ');
	$result1=DB_query($sql, $ErrMsg, $DbgMsg);
	unset($_POST['MailServerSetting']);
	prnMsg(_('The settings for the SMTP server have been successfully updated'), 'success');
	echo '<br />';

}elseif(isset($_POST['submit']) and $_POST['MailServerSetting']==0){//There is no data setup yet
	$sql = "INSERT INTO emailsettings(host,
		 				port,
						heloaddress,
						username,
						password,
						auth)
				VALUES ('".$_POST['Host']."',
						'".$_POST['Port']."',
						'".$_POST['HeloAddress']."',
						'".$_POST['UserName']."',
						'".$_POST['Password']."',
						'".$_POST['Auth']."')";
	$ErrMsg = _('The email settings failed to be inserted');
	$DbgMsg = _('The SQL failed to insert the email information is');
	$result2 = DB_query($sql);
	unset($_POST['MailServerSetting']);
	prnMsg(_('The settings for the SMTP server have been sucessfully inserted'),'success');
	echo '<br/>';
}

  // Check the mail server setting status

		$sql="SELECT id,
				host,
				port,
				heloaddress,
				username,
				password,
				timeout,
				auth
			FROM emailsettings";
		$ErrMsg = _('The email settings information cannot be retrieved');
		$DbgMsg = _('The SQL that failed was');

		$result=DB_query($sql,$ErrMsg,$DbgMsg);
		if(DB_num_rows($result)!=0){
			$MailServerSetting = 1;
			$myrow=DB_fetch_array($result);
		}else{
			DB_free_result($result);
			$MailServerSetting = 0;
			$myrow['host']='';
			$myrow['port']='';
			$myrow['heloaddress']='';
			$myrow['username']='';
			$myrow['password']='';
			$myrow['timeout']=5;
		}


echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="MailServerSetting" value="' . $MailServerSetting . '" />
	<fieldset>
	<legend>', _('SMTP Server Details'), '</legend>
	<field>
		<label for="Host">' . _('Server Host Name') . '</label>
		<input type="text" name="Host" required="required" value="' . $myrow['host'] . '" />
	</field>
	<field>
		<label for="Port">' . _('SMTP port') . '</label>
		<input type="text" name="Port" required="required" size="4" class="number" value="' . $myrow['port'].'" />
	</field>
	<field>
		<label for="HeloAddress">' . _('Helo Command') . '</label>
		<input type="text" name="HeloAddress" value="' . $myrow['heloaddress'] . '" />
	</field>
	<field>
		<label for="Auth">' . _('Authorisation Required') . '</label>
		<select name="Auth">';
if ($myrow['auth']==1) {
	echo '<option selected="selected" value="1">' . _('True') . '</option>';
	echo '<option value="0">' . _('False') . '</option>';
} else {
	echo '<option value="1">' . _('True') . '</option>';
	echo '<option selected="selected" value="0">' . _('False') . '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="UserName">' . _('User Name') . '</label>
		<input type="text" required="required" name="UserName" size="50" maxlength="50" value="' . $myrow['username']  .'" />
	</field>
	<field>
		<label for="Password">' . _('Password') . '</label>
		<input type="password" required="required" name="Password" value="' . $myrow['password'] . '" />
	</field>
	<field>
		<label for="Timeout">' . _('Timeout (seconds)') . '</label>
		<input type="text" size="5" name="Timeout" class="number" value="' . $myrow['timeout'] . '" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Update') . '" />
	</div>
	</form>';

include('includes/footer.php');

?>

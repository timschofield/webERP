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
	$SQL="UPDATE emailsettings SET
				host='".$_POST['Host']."',
				port='".$_POST['Port']."',
				heloaddress='".$_POST['HeloAddress']."',
				username='".$_POST['UserName']."',
				password='".$_POST['Password']."',
				auth='".$_POST['Auth']."'";

	$ErrMsg = _('The email setting information failed to update');
	$DbgMsg = _('The SQL failed to update is ');
	$Result1=DB_query($SQL, $ErrMsg, $DbgMsg);
	unset($_POST['MailServerSetting']);
	prnMsg(_('The settings for the SMTP server have been successfully updated'), 'success');
	echo '<br />';

}elseif(isset($_POST['submit']) and $_POST['MailServerSetting']==0){//There is no data setup yet
	$SQL = "INSERT INTO emailsettings(host,
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
	$Result2 = DB_query($SQL);
	unset($_POST['MailServerSetting']);
	prnMsg(_('The settings for the SMTP server have been sucessfully inserted'),'success');
	echo '<br/>';
}

  // Check the mail server setting status

		$SQL="SELECT id,
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

		$Result=DB_query($SQL,$ErrMsg,$DbgMsg);
		if(DB_num_rows($Result)!=0){
			$MailServerSetting = 1;
			$MyRow=DB_fetch_array($Result);
		}else{
			DB_free_result($Result);
			$MailServerSetting = 0;
			$MyRow['host']='';
			$MyRow['port']='';
			$MyRow['heloaddress']='';
			$MyRow['username']='';
			$MyRow['password']='';
			$MyRow['timeout']=5;
		}


echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="MailServerSetting" value="' . $MailServerSetting . '" />
	<table class="selection">
	<tr>
		<td>' . _('Server Host Name') . '</td>
		<td><input type="text" name="Host" required="required" value="' . $MyRow['host'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('SMTP port') . '</td>
		<td><input type="text" name="Port" required="required" size="4" class="number" value="' . $MyRow['port'].'" /></td>
	</tr>
	<tr>
		<td>' . _('Helo Command') . '</td>
		<td><input type="text" name="HeloAddress" value="' . $MyRow['heloaddress'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Authorisation Required') . '</td>
		<td><select name="Auth">';
if ($MyRow['auth']==1) {
	echo '<option selected="selected" value="1">' . _('True') . '</option>';
	echo '<option value="0">' . _('False') . '</option>';
} else {
	echo '<option value="1">' . _('True') . '</option>';
	echo '<option selected="selected" value="0">' . _('False') . '</option>';
}
echo '</select></td>
	</tr>
	<tr>
		<td>' . _('User Name') . '</td>
		<td><input type="text" required="required" name="UserName" size="50" maxlength="50" value="' . $MyRow['username']  .'" /></td>
	</tr>
	<tr>
		<td>' . _('Password') . '</td>
		<td><input type="password" required="required" name="Password" value="' . $MyRow['password'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Timeout (seconds)') . '</td>
		<td><input type="text" size="5" name="Timeout" class="number" value="' . $MyRow['timeout'] . '" /></td>
	</tr>
	<tr>
		<td colspan="2"><div class="centre"><input type="submit" name="submit" value="' . _('Update') . '" /></div></td>
	</tr>
	</table>
	</div>
	</form>';

include('includes/footer.php');

?>

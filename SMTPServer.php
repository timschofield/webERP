<?php

require(__DIR__ . '/includes/session.php');

use PHPMailer\PHPMailer\PHPMailer;

$Title = __('SMTP Server details');
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'SMTPServer';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/email.png" title="' .// Icon image.
	__('SMTP Server') . '" /> ' .// Icon title.
	__('SMTP Server Settings') . '</p>';// Page title.

// First check if there are smtp server data or not

if (isset($_POST['submit']) AND $_POST['MailServerSetting']==1) {//If there are already data setup, Update the table

	$mail = new PHPMailer(true);
	$mail->SMTPAuth = true;
	$mail->Username = $_POST['UserName'];
	$mail->Password = $_POST['Password'];
	$mail->Host = $_POST['Host'];
	$mail->Port = $_POST['Port'];

	// This function returns true if authentication
	// was successful, or throws an exception otherwise
	try {
		$Connection = $mail->SmtpConnect();
	}
	catch(Exception $error) {
		prnMsg(__('The connection to the SMPT server cannot be made'), 'error');
	}

	if ($Connection == 1) {
		prnMsg(__('The connection to the SMPT server has been made'), 'success');
	}

	$SQL="UPDATE emailsettings SET
				host='".$_POST['Host']."',
				port='".$_POST['Port']."',
				heloaddress='".$_POST['HeloAddress']."',
				username='".$_POST['UserName']."',
				password='".$_POST['Password']."',
				auth='".$_POST['Auth']."'";

	$ErrMsg = __('The email setting information failed to update');
	$Result1 = DB_query($SQL, $ErrMsg);
	unset($_POST['MailServerSetting']);
	prnMsg(__('The settings for the SMTP server have been successfully updated'), 'success');
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
	$ErrMsg = __('The email settings failed to be inserted');
	$Result2 = DB_query($SQL);
	unset($_POST['MailServerSetting']);
	prnMsg(__('The settings for the SMTP server have been successfully inserted'),'success');
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
		$ErrMsg = __('The email settings information cannot be retrieved');

		$Result = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($Result)!=0){
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
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="MailServerSetting" value="' . $MailServerSetting . '" />
	<fieldset>
	<legend>', __('SMTP Server Details'), '</legend>
	<field>
		<label for="Host">' . __('Server Host Name') . '</label>
		<input type="text" name="Host" required="required" value="' . $MyRow['host'] . '" />
	</field>
	<field>
		<label for="Port">' . __('SMTP port') . '</label>
		<input type="text" name="Port" required="required" size="4" class="number" value="' . $MyRow['port'].'" />
	</field>
	<field>
		<label for="HeloAddress">' . __('Helo Command') . '</label>
		<input type="text" name="HeloAddress" value="' . $MyRow['heloaddress'] . '" />
	</field>
	<field>
		<label for="Auth">' . __('Authorisation Required') . '</label>
		<select name="Auth">';
if ($MyRow['auth']==1) {
	echo '<option selected="selected" value="1">' . __('True') . '</option>';
	echo '<option value="0">' . __('False') . '</option>';
} else {
	echo '<option value="1">' . __('True') . '</option>';
	echo '<option selected="selected" value="0">' . __('False') . '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="UserName">' . __('User Name') . '</label>
		<input type="text" required="required" name="UserName" size="50" maxlength="50" value="' . $MyRow['username']  .'" />
	</field>
	<field>
		<label for="Password">' . __('Password') . '</label>
		<input type="password" required="required" name="Password" size="50" maxlength="101" value="' . $MyRow['password'] . '" />
	</field>
	<field>
		<label for="Timeout">' . __('Timeout (seconds)') . '</label>
		<input type="text" size="5" name="Timeout" class="number" value="' . $MyRow['timeout'] . '" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . __('Update') . '" />
	</div>
	</form>';

include('includes/footer.php');

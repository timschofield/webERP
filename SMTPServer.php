<?php
/* $Id: SMTPServer.php 4469 2011-01-15 02:28:37Z daintree $*/
include('includes/session.inc');

$title = _('SMTP Server details');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/email.gif" title="' . _('SMTP Server') . '" alt="" />' . ' ' . _('SMTP Server Settings') . '</p>';

if (isset($_POST['submit'])) {
	$sql="UPDATE emailsettings SET
				host='".$_POST['Host']."',
				port='".$_POST['Port']."',
				heloaddress='".$_POST['HeloAddress']."',
				username='".$_POST['UserName']."',
				password='".$_POST['Password']."',
				auth='".$_POST['Auth']."'";
	$result=DB_query($sql, $db);
	prnMsg(_('The settings for the SMTP server have been successfully updated'), 'success');
	echo '<br />';
}

$sql="SELECT id,
			host,
			port,
			heloaddress,
			username,
			password,
			timeout,
			auth
		FROM emailsettings";

$result=DB_query($sql, $db);

$myrow=DB_fetch_array($result);

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';
echo '<tr><td>'._('Server Host Name').'</td>
		<td><input type="text" name="Host" value="'.$myrow['host'].'" /></td></tr>';
echo '<tr><td>'._('SMTP port').'</td>
		<td><input type="text" name="Port" size="4" class="number" value="'.$myrow['port'].'" /></td></tr>';
echo '<tr><td>'._('Helo Command').'</td>
		<td><input type="text" name="HeloAddress" value="'.$myrow['heloaddress'].'" /></td></tr>';
echo '<tr><td>'._('Authorisation Required').'</td><td>';
echo '<select name="Auth">';
if ($myrow['auth']==1) {
	echo '<option selected value=1>'._('True').'</option>';
	echo '<option value=0>'._('False').'</option>';
} else {
	echo '<option value=1>'._('True').'</option>';
	echo '<option selected value=0>'._('False').'</option>';
}
echo '</select></td></tr>';
echo '<tr><td>'._('User Name').'</td>
	<td><input type="text" name="UserName" value="'.$myrow['username'].'" /></td></tr>';
echo '<tr><td>'._('Password').'</td>
	<td><input type="password" name="Password" value="'.$myrow['password'].'" /></td></tr>';
echo '<tr><td>'._('Timeout (seconds)').'</td>
	<td><input type="text" size="5" name="Timeout" class="number" value="'.$myrow['timeout'].'" /></td></tr>';
echo '<tr><td colspan="2"><div class="centre"><input type="submit" name="submit" value="' . _('Update') . '" /></div></td></tr>';
echo '</table></form>';

include('includes/footer.inc');

?>
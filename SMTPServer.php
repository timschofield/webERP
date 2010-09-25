<?php

$PageSecurity =15;

include('includes/session.inc');

$title = _('SMTP Server details');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/email.gif" title="' .
		_('SMTP Server') . '" alt="">' . ' ' . _('SMTP Server Settings') . '</p>';

if (isset($_POST['submit'])) {
	$sql="UPDATE emailsettings SET
				host='".$_POST['host']."',
				port='".$_POST['port']."',
				heloaddress='".$_POST['heloaddress']."',
				username='".$_POST['username']."',
				password='".$_POST['password']."',
				auth='".$_POST['auth']."'";
	$result=DB_query($sql, $db);
	prnMsg(_('The settings for the SMTP server have been successfully updated'), 'success');
	echo '<br>';
}

$sql='SELECT id,
				host,
				port,
				heloaddress,
				username,
				password,
				timeout,
				auth
			FROM emailsettings';
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<table class=selection>';
echo '<tr><td>'._('Server Host Name').'</td>
		<td><input type=text name=host value='.$myrow['host'].'></td></tr>';
echo '<tr><td>'._('SMTP port').'</td>
		<td><input type=text name=port size=4 class=number value='.$myrow['port'].'></td></tr>';
echo '<tr><td>'._('Helo Command').'</td>
		<td><input type=text name=heloaddress value='.$myrow['heloaddress'].'></td></tr>';
echo '<tr><td>'._('Authorisation Required').'</td><td>';
echo '<select name=auth>';
if ($myrow['auth']==1) {
	echo '<option selected value=1>'._('True').'</option>';
	echo '<option value=0>'._('False').'</option>';
} else {
	echo '<option value=1>'._('True').'</option>';
	echo '<option selected value=0>'._('False').'</option>';
}
echo '</select></td></tr>';
echo '<tr><td>'._('User Name').'</td>
	<td><input type=text name=username value='.$myrow['username'].'></td></tr>';
echo '<tr><td>'._('Password').'</td>
	<td><input type=password name=password value='.$myrow['password'].'></td></tr>';
echo '<tr><td>'._('Timeout (seconds)').'</td>
	<td><input type=text size=5 name=timeout class=number value='.$myrow['timeout'].'></td></tr>';
echo '<tr><td colspan=2><div class=centre><input type="submit" name="submit" value="' . _('Update') . '"></div></td></tr>';
echo '</table></form>';

include('includes/footer.inc');

?>
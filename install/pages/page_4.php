<?php

$DataSaved = '';

if (isset($_POST['test'])) {
	$_SESSION['Installer']['AdminUser'] = $_POST['adminaccount'];
	$_SESSION['Installer']['AdminEmail'] = $_POST['Email'];
	$_SESSION['Installer']['AdminPassword'] = $_POST['webERPPassword'];

	$DataSaved = 'yes';
	$Message = _('Information saved successfully');
}

echo '<form method="post" action="index.php?Page=4">
		<fieldset>
			<legend>' . _('Administrator account settings') . '</legend>
			<div class="page_help_text">
				<p>' . _('Please enter your administrator account details below.') . '<br />
				</p>
				<p>' . _('The default user name is') . ' ' . '<b><i>admin</i></b>' . ' ' . _('which you can change below.') . '<br />
				   ' . _('The default password is') . ' ' . '<b><i>weberp</i></b>' . ' ' . _('which you can change below.') . '</p>
			</div>
			<ul>
				<field>
					<label for="adminaccount">' . _('User Name') . ': </label>
					<input type="text" name="adminaccount" value="', $_SESSION['Installer']['AdminUser'], '" />
				</field>
				<field>
					<label for="Email">' . _('Email Address') . ': </label>
					<input type="email" name="Email" value="' . $_SESSION['Installer']['AdminEmail'] . '" placeholder="admin@yoursite.com" />
					<fieldhelp>' . _('For example: admin@yoursite.com') . '</fieldhelp>
				</field>
				<field>
					<label for="webERPPassword">' . _('Password') . ': </label>
					<input type="password" name="webERPPassword" required="required" value="' . $_SESSION['Installer']['AdminPassword'] . '" />
				</field>
				<field>
					<label for="PasswordConfirm">' . _('Re-enter Password') . ': </label>
					<input type="password" name="PasswordConfirm" required="required" value="' . $_SESSION['Installer']['AdminPassword'] . '" />
				</field>
			</ul>';

if ($DataSaved != '') {
	echo '<input type="submit" id="save" name="test" value="Save admin account details" /><img class="result_icon" src="valid.png" />', $Message;
} else {
	echo '<input type="submit" id="save" name="test" value="Save admin account details" />';
}
echo '</fieldset>
	</form>';

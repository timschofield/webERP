<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

$DataSaved = '';

if (isset($_POST['test'])) {
	$_SESSION['Installer']['AdminUser'] = $_POST['adminaccount'];
	$_SESSION['Installer']['AdminEmail'] = $_POST['Email'];
	$_SESSION['Installer']['AdminPassword'] = $_POST['webERPPassword'];

	$DataSaved = 'yes';
	$Message = __('Information saved successfully');
}

echo '<form method="post" action="index.php?Page=4">
		<fieldset>
			<legend>' . __('Administrator account settings') . '</legend>
			<div class="page_help_text">
				<p>' . __('Please enter your administrator account details below.') . '<br />
				</p>
				<p>' . __('The default user name is') . ' ' . '<b><i>admin</i></b>' . ' ' . __('which you can change below.') . '<br />
				   ' . __('The default password is') . ' ' . '<b><i>weberp</i></b>' . ' ' . __('which you can change below.') . '</p>
			</div>
			<ul>
				<field>
					<label for="adminaccount">' . __('User Name') . ': </label>
					<input type="text" name="adminaccount" value="', $_SESSION['Installer']['AdminUser'], '" />
				</field>
				<field>
					<label for="Email">' . __('Email Address') . ': </label>
					<input type="email" name="Email" value="' . $_SESSION['Installer']['AdminEmail'] . '" placeholder="admin@yoursite.com" />
					<fieldhelp>' . __('For example: admin@yoursite.com') . '</fieldhelp>
				</field>
				<field>
					<label for="webERPPassword">' . __('Password') . ': </label>
					<input type="password" name="webERPPassword" required="required" value="' . $_SESSION['Installer']['AdminPassword'] . '" />
				</field>
				<field>
					<label for="PasswordConfirm">' . __('Re-enter Password') . ': </label>
					<input type="password" name="PasswordConfirm" required="required" value="' . $_SESSION['Installer']['AdminPassword'] . '" />
				</field>
			</ul>';

if ($DataSaved != '') {
	echo '<input type="submit" id="save" name="test" value="Save admin account details" /><img class="result_icon" src="images/valid.png" />', $Message;
} else {
	echo '<input type="submit" id="save" name="test" value="Save admin account details" />';
}
echo '</fieldset>
	</form>';

<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

if (isset($_POST['agreed'])) {
	$_SESSION['Installer']['License_Agreed'] = ($_POST['agreed'] == 'Yes');
}

$GPLV2 = file_get_contents(__DIR__ . '/../../doc/LICENSE.txt');

if ($_SESSION['Installer']['License_Agreed'] == true) {
	$Checked = 'checked="checked"';
} else {
	$Checked = '';
}

/// @todo move away from js usage for making the user accept the license, as that is hard to test using phpunit+browserkit.
///       We could f.e. use the same pattern used for saving the db-connection data.

echo '<form method="get" id="license_form" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<label>', __('webERP is released under the GNU GPL v2 license'), ' :</label>
		<textarea id="license" rows="20" readonly="true" value="" >', $GPLV2, '</textarea><br  />
		<span id="license_agree">
			<input onclick="document.getElementById(\'license_form\').submit();" id="agreed" name="Agreed" value="Yes" type="checkbox" ', $Checked, ' />', __('I have read the license and agree to the terms and conditions within it'), '
		</span>
		<input type="hidden" name="Page" value="1" />
	</form>';

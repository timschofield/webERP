<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

include($PathPrefix . 'includes/CountriesArray.php');

echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Page=6" method="post" enctype="multipart/form-data">';
echo '<fieldset>
			<legend>' . __('Company Settings') . '</legend>
			<div class="page_help_text">
			</div>
			<ul>
				<field>
					<label for="CompanyName">' . __('Company Name') . ': </label>
					<input type="text" name="CompanyName" required="required" maxlength="50" size="30" />
					<fieldhelp>' . __('The full name of the company that you want to be used throughout webERP') . '</fieldhelp>
				</field>
				<field>
				<label for="COA">' . __('Chart of Accounts') . ': </label>
				<select name="COA">';

$COAs = glob('sql/coa/*.sql');

foreach ($COAs as $Value) {
	if ($Value == 'sql/coa/' . $_SESSION['Installer']['CoA'] . '.sql') {
		echo '<option value="' . $Value . '" selected="selected">' . $CountriesArray[substr(basename($Value, '.sql'), 3, 2) ] . '</option>';
	} else {
		echo '<option value="' . $Value . '">' . $CountriesArray[substr(basename($Value, '.sql'), 3, 2) ] . '</option>';
	}
}
echo '</select>
			<fieldhelp>' . __('Will be installed as starter Chart of Accounts. If installing the Demo data then this wont work and you will just get a standard set of accounts') . '</fieldhelp>
		</field>';

echo '<field>
			<label for="TimeZone">' . __('Time Zone') . ': </label>
			<select name="TimeZone">';
include($PathPrefix . 'install/timezone.php');
echo '</select>
	</field>';

echo '<field>
			<label for="Logo">' . __('Company logo file') . ': </label>
			<input type="file" accept="image/jpg" name="LogoFile" title="' . __('A jpg file up to 10kb, and not greater than 170px x 80px') . '" />
			<fieldhelp>' . __('jpg/jpeg/png/gif files up to 10kb, and not greater than 170px x 80px') . '<br />' . __('If you do not select a file, the default webERP logo will be used') . '</fieldhelp>
		</field>
	</ul>
</fieldset>';

echo '<fieldset>
			<legend>' . __('Installation option') . '</legend>
				<ul>
					<field>
						<label for="InstallDemo">' . __('Install the demo data?') . '</label><input type="checkbox" name="Demo" value="Yes" />
						<fieldhelp>' . __('webERP Demo site and data will be installed') . '</fieldhelp>
					</field>
				</ul>
		</fieldset>';

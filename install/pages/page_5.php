<?php

include ('../includes/CountriesArray.php');
echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Page=6" method="post" enctype="multipart/form-data">';
echo '<fieldset>
			<legend>' . _('Company Settings') . '</legend>
			<div class="page_help_text">
			</div>
			<ul>
				<field>
					<label for="CompanyName">' . _("Company Name") . ': </label>
					<input type="text" name="CompanyName" required="required" maxlength="50" size="30" />
					<fieldhelp>' . _('The full name of the company that you want to be used throughout webERP') . '</fieldhelp>
				</field>
				<field>
				<label for="COA">' . _("Chart of Accounts") . ': </label>
				<select name="COA">';

$COAs = glob('sql/coa/*.sql');

foreach ($COAs as $Value) {
	if ($Value == 'sql/coa/' . $_SESSION['Installer']['CoA'] . '.sql') {
		echo '<option value="' . $Value . '" selected="true">' . $CountriesArray[substr(basename($Value, '.sql'), 3, 2) ] . '</option>';
	} else {
		echo '<option value="' . $Value . '">' . $CountriesArray[substr(basename($Value, '.sql'), 3, 2) ] . '</option>';
	}
}
echo '</select>
			<fieldhelp>' . _('Will be installed as starter Chart of Accounts. If installing the Demo data then this wont work and you will just get a standard set of accounts') . '</fieldhelp>
		</field>';

echo '<field>
			<label for="TimeZone">' . _('Time Zone') . ': </label>
			<select name="TimeZone">';
include ('timezone.php');
echo '</select>
	</field>';

echo '<field>
			<label for="Logo">' . _('Company logo file') . ': </label>
			<input type="file" accept="image/jpg" name="LogoFile" title="' . _('A jpg file up to 10kb, and not greater than 170px x 80px') . '" />
			<fieldhelp>' . _('jpg/jpeg/png/gif files up to 10kb, and not greater than 170px x 80px') . '<br />' . _('If you do not select a file, the default webERP logo will be used') . '</fieldhelp>
		</field>
	</ul>
</fieldset>';

echo '<fieldset>
			<legend>' . _('Installation option') . '</legend>
				<ul>
					<field>
						<label for="InstallDemo">' . _('Install the demo data?') . '</label><input type="checkbox" name="Demo" value="Yes" />
						<fieldhelp>' . _('webERP Demo site and data will be installed') . '</fieldhelp>
					</field>
				</ul>
		</fieldset>';

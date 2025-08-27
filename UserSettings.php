<?php

// Allows the user to change system-wide defaults for the theme - appearance, the number of records to show in searches and the language to display messages in.

require(__DIR__ . '/includes/session.php');

$Title = __('User Settings');
$ViewTopic = 'GettingStarted';
$BookMark = 'UserSettings';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/user.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>'; // Page title.

$PDFLanguages = array(
	__('Latin Western Languages - Times'),
	__('Eastern European Russian Japanese Korean Hebrew Arabic Thai'),
	__('Chinese'),
	__('Free Serif')
);

if(isset($_POST['Modify'])) {
	// no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if($_POST['DisplayRecordsMax'] <= 0) {
		$InputError = 1;
		prnMsg(__('The Maximum Number of Records on Display entered must not be negative') . '. ' . __('0 will default to system setting'),'error');
	}

	//!!!for the demo only - enable this check so password is not changed
	if($AllowDemoMode AND $_POST['Password'] != '') {
		$InputError = 1;
		prnMsg(__('Cannot change password in the demo or others would be locked out!'),'warn');
	}

 	$UpdatePassword = 'N';

	if($_POST['PasswordCheck'] != '') {
		if(mb_strlen($_POST['Password']) < 5) {
			$InputError = 1;
			prnMsg(__('The password entered must be at least 5 characters long'),'error');
		} elseif(mb_strstr($_POST['Password'],$_SESSION['UserID'])!= false) {
			$InputError = 1;
			prnMsg(__('The password cannot contain the user id'), 'error');
		}
		if($_POST['Password'] != $_POST['PasswordCheck']) {
			$InputError = 1;
			prnMsg(__('The password and password confirmation fields entered do not match'), 'error');
		} else {
			$UpdatePassword = 'Y';
		}
	}

	if($InputError != 1) {
		// no errors
		if (isset($_POST['Language']) && !checkLanguageChoice($_POST['Language'])) {
			$_POST['Language'] = $DefaultLanguage;
		}

		if($UpdatePassword != 'Y') {
			$SQL = "UPDATE www_users
					SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
						theme='" . $_POST['Theme'] . "',
						language='" . $_POST['Language'] . "',
						email='" . $_POST['email'] . "',
						showpagehelp='" . $_POST['ShowPageHelp'] . "',
						showfieldhelp='" . $_POST['ShowFieldHelp'] . "',
						pdflanguage='" . $_POST['PDFLanguage'] . "'
					WHERE userid = '" . $_SESSION['UserID'] . "'";
			$ErrMsg = __('The user alterations could not be processed because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg( __('The user settings have been updated') . '. ' . __('Be sure to remember your password for the next time you login'),'success');
		} else {
			$SQL = "UPDATE www_users
					SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
						theme='" . $_POST['Theme'] . "',
						language='" . $_POST['Language'] . "',
						email='" . $_POST['email'] ."',
						showpagehelp='" . $_POST['ShowPageHelp'] . "',
						showfieldhelp='" . $_POST['ShowFieldHelp'] . "',
						pdflanguage='" . $_POST['PDFLanguage'] . "',
						password='" . CryptPass($_POST['Password']) . "'
					WHERE userid = '" . $_SESSION['UserID'] . "'";
			$ErrMsg = __('The user alterations could not be processed because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(__('The user settings have been updated'),'success');
		}
		// Update the session variables to reflect user changes on-the-fly:
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']); /*already set by session.php but for completeness */
		$Theme = $_SESSION['Theme'];
		$_SESSION['Language'] = trim($_POST['Language']);
		$_SESSION['ShowPageHelp'] = $_POST['ShowPageHelp'];
		$_SESSION['ShowFieldHelp'] = $_POST['ShowFieldHelp'];
		$_SESSION['PDFLanguage'] = $_POST['PDFLanguage'];
		include($PathPrefix . 'includes/LanguageSetup.php'); // After last changes in LanguageSetup.php, is it required to update?
	}
}

$SQL = "SELECT
			email,
			showpagehelp,
			showfieldhelp,
			language
		from www_users WHERE userid = '" . $_SESSION['UserID'] . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

if(!isset($_POST['email'])) {
	$_POST['email'] = $MyRow['email'];
}
$_POST['ShowPageHelp'] = $MyRow['showpagehelp'];
$_POST['ShowFieldHelp'] = $MyRow['showfieldhelp'];
$_POST['Language'] = $MyRow['language'];

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
	'<input name="FormID" value="', $_SESSION['FormID'], '" type="hidden" />';

echo '<fieldset>
		<legend>', __('Edit User Settings'), '</legend>
		<field>
			<label for="UserID">', __('User ID'), ':</label>
			<fieldtext>', $_SESSION['UserID'], '</fieldtext>
		</field>';

echo '<field>
		<label for="UsersRealName">', __('User Name'), ':</label>
		<fieldtext>', $_SESSION['UsersRealName'], '<input name="RealName" type="hidden" value="', $_SESSION['UsersRealName'], '" /></fieldtext>
	</field>';

echo '<field>
		<label for="DisplayRecordsMax">', __('Maximum Number of Records to Display'), ':</label>
		<input class="integer" maxlength="3" name="DisplayRecordsMax" required="required" size="3" title="', __('The input must be positive integer'), '" type="text" value="', $_SESSION['DisplayRecordsMax'], '" />
	</field>';

// Select language:
echo '<field>
		<label for="Language">', __('Language'), ':</label>
		<select name="Language">';
if(!isset($_POST['Language'])) {
	$_POST['Language'] = $_SESSION['Language'];
}
foreach($LanguagesArray as $LanguageEntry => $LanguageName) {
	echo '<option ';
	if(isset($_POST['Language']) AND $_POST['Language'] == $LanguageEntry) {
		echo 'selected="selected" ';
	}
	echo 'value="', $LanguageEntry, '">', $LanguageName['LanguageName'], '</option>';
}
echo '</select>
	</field>';

// Select theme:
echo '<field>
		<label for="Theme">' . __('Theme') . ':</label>
		<select name="Theme">';

$ThemeDirectories = scandir('css/');

foreach ($ThemeDirectories as $ThemeName) {
	if(is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn') {

		if($_SESSION['Theme'] == $ThemeName) {
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName . '</option>';
		}
	}
}

if(!isset($_POST['PasswordCheck'])) {
	$_POST['PasswordCheck']='';
}
if(!isset($_POST['Password'])) {
	$_POST['Password']='';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Password">', __('New Password'), ':</label>
		<input name="Password" pattern="(?!^', $_SESSION['UserID'], '$).{5,}" placeholder="', __('More than 5 characters'), '" size="20" title="', __('Must be more than 5 characters and cannot be as same as userid'), '" type="password" value="', $_POST['Password'], '" />
		<fieldhelp>', __('If you leave the password boxes empty your password will not change'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="PasswordCheck">', __('Confirm Password'), ':</label>
		<input name="PasswordCheck" pattern="(?!^', $_SESSION['UserID'], '$).{5,}" placeholder="', __('More than 5 characters'), '" size="20" title="', __('Must be more than 5 characters and cannot be as same as userid'), '" type="password" value="', $_POST['PasswordCheck'], '" />
		<fieldhelp>', __('Confirm the password you entered above'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="email">', __('Email'), ':</label>
		<input name="email" size="40" type="email" value="', $_POST['email'], '" />
	</field>';

// Turn off/on page help:
echo '<field>
		<label for="ShowPageHelp">', __('Display page help'), ':</label>
		<select id="ShowPageHelp" name="ShowPageHelp">';
if ($_POST['ShowPageHelp']==0) {
	echo '<option selected="selected" value="0">', __('No'), '</option>',
		 '<option value="1">', __('Yes'), '</option>';
} else {
	echo '<option value="0">', __('No'), '</option>',
 		 '<option selected="selected" value="1">', __('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>', __('Show page help when available'), '</fieldhelp>
</field>';

// Turn off/on field help:
echo '<field>
		<label for="ShowFieldHelp">', __('Display field help'), ':</label>
		<select id="ShowFieldHelp" name="ShowFieldHelp">';
if($_POST['ShowFieldHelp']==0) {
	echo '<option selected="selected" value="0">', __('No'), '</option>',
		 '<option value="1">', __('Yes'), '</option>';
} else {
	echo '<option value="0">', __('No'), '</option>',
 		 '<option selected="selected" value="1">', __('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>', __('Show field help when available'), '</fieldhelp>
</field>';
// PDF Language Support:
if(!isset($_POST['PDFLanguage'])) {
	$_POST['PDFLanguage']=$_SESSION['PDFLanguage'];
}
echo '<field>
		<label for="PDFLanguage">', __('PDF Language Support'), ': </label>
		<select name="PDFLanguage">';
for($i=0; $i<count($PDFLanguages); $i++) {
	if($_POST['PDFLanguage'] == $i) {
		echo '<option selected="selected" value="', $i, '">', $PDFLanguages[$i], '</option>';
	} else {
		echo '<option value="', $i, '">', $PDFLanguages[$i], '</option>';
	}
}
echo '</select>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input name="Modify" type="submit" value="', __('Modify'), '" /></div>
	</form>';

include('includes/footer.php');

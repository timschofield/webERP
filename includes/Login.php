<?php

/// @todo should we include these two here? This file is included from session.php, which already includes them...
include($PathPrefix . 'includes/LanguageSetup.php');
include('LanguagesArray.php');

// Display demo user name and password within login form if $AllowDemoMode is true
if ((isset($AllowDemoMode)) and ($AllowDemoMode == true) and (!isset($DemoText))) {
	$DemoText = __('Login as user') . ': <i>' . __('admin') . '</i><br />' . __('with password') . ': <i>' . __('weberp') . '</i>';
} elseif (!isset($DemoText)) {
	$DemoText = __('Please login here');
}

echo "<!DOCTYPE html>\n";
/// @todo handle better the case where $Language is not in xx-YY format (full spec is at https://www.rfc-editor.org/rfc/rfc5646.html)
echo '<html lang="' , str_replace('_', '-', substr($Language, 0, 5)) , '">
	<head>
		<title>WebERP ', __('Login screen'), '</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" href="favicon.ico" type="image/x-icon" />
		<script async src="', $RootPath, '/javascripts/Login.js"></script>';

if ($LanguagesArray[$DefaultLanguage]['Direction'] == 'rtl') {
	echo '<link rel="stylesheet" href="css/login_rtl.css" type="text/css" />';
} else {
	echo '<link rel="stylesheet" href="css/login.css" type="text/css" />';
}
echo '</head>';

echo '<body>
	<div id="container">
		<div id="login_logo">
			<div class="logo logo-left">web</div><div class="logo logo-right">ERP</div>
		</div>
		<div id="login_box">
			<form action="' . $RootPath . '/index.php" name="LogIn" method="post" class="noPrint">
			<input type="hidden" name="FormID" value="', sha1(uniqid(mt_rand(), true)), '" />';

if (isset($_COOKIE['Login'])) {
	$DefaultCompany = $_COOKIE['Login'];
}else{
	$DefaultCompany = $DefaultDatabase;
}

if ($AllowCompanySelectionBox === 'Hide') {
	// do not show input or selection box
	echo '<input type="hidden" name="CompanyNameField"  value="' . $DefaultCompany . '" />';
} else if ($AllowCompanySelectionBox === 'ShowInputBox') {
	// show input box
	echo '<input type="text" required="required" autofocus="autofocus" name="CompanyNameField"  value="' . $DefaultCompany . '" />';
} else {
	// Show selection box ($AllowCompanySelectionBox == 'ShowSelectionBox')
	echo '<select name="CompanyNameField" id="CompanyNameField">';

	$DirHandle = dir('companies/');

	if (!isset($_COOKIE["Company"])) {
		$Company = $DefaultCompany;
	} else {
		$Company = $_COOKIE["Company"];
	}

	while (false !== ($CompanyEntry = $DirHandle->read())) {
		if (is_dir('companies/' . $CompanyEntry) and $CompanyEntry != '..' and $CompanyEntry != '' and $CompanyEntry != '.' and $CompanyEntry != 'default') {
			if (file_exists('companies/' . $CompanyEntry . '/Companies.php')) {
				include('companies/' . $CompanyEntry . '/Companies.php');
			} else {
				$CompanyName[$CompanyEntry] = $CompanyEntry;
			}
			if ($Company == $CompanyEntry) {
				echo '<option selected="selected" value="' . $CompanyEntry . '">' . $CompanyName[$CompanyEntry] . '</option>';
			} else {
				echo '<option value="' . $CompanyEntry . '">' . $CompanyName[$CompanyEntry] . '</option>';
			}
		}
	}

	$DirHandle->close();

	echo '</select>';
}

if ($AllowCompanySelectionBox != 'Hide') {
	echo '<label for="CompanySelect">', __('Company'), ':</label>';
	echo '<input type="text" id="CompanySelect" readonly value="' . $CompanyName[$DefaultCompany] . '" />';
	if (!isset($ShowLogoAtLogin) OR ($ShowLogoAtLogin == true)) {
		echo '<ol id="dropdownlist" class="dropdownlist" style="padding-bottom:10px;">';
	} else {
		echo '<ol id="dropdownlist" class="dropdownlist" style="padding-bottom:15px;">';
	}
}

$DirHandle = dir('companies/');

while (false !== ($CompanyEntry = $DirHandle->read())) {
	if (is_dir('companies/' . $CompanyEntry) and $CompanyEntry != '..' and $CompanyEntry != '' and $CompanyEntry != '.' and $CompanyEntry != 'default') {
		if (file_exists('companies/' . $CompanyEntry . '/Companies.php')) {
			include('companies/' . $CompanyEntry . '/Companies.php');
		} else {
			$CompanyName[$CompanyEntry] = $CompanyEntry;
		}
		if ($AllowCompanySelectionBox != 'Hide'){
			if (!isset($ShowLogoAtLogin) OR ($ShowLogoAtLogin == true)) {
				if (file_exists('companies/' . $CompanyEntry . '/logo.png')) {
					echo '<li class="option" id="' . $CompanyEntry . '" ><img id="optionlogo" src="companies/' . $CompanyEntry . '/logo.png" /><span id="optionlabel">', $CompanyName[$CompanyEntry], '</span></li>';
				} else if (file_exists('companies/' . $CompanyEntry . '/logo.jpg')) {
					echo '<li class="option" id="' . $CompanyEntry . '" ><img id="optionlogo" src="companies/' . $CompanyEntry . '/logo.jpg" /><span id="optionlabel">', $CompanyName[$CompanyEntry], '</span></li>';
				}
			} else {
				echo '<li class="option" id="' . $CompanyEntry . '" ><span style="top:0px" id="optionlabel">', $CompanyName[$CompanyEntry], '</span></li>';
			}
		}
	}
}
$DirHandle->close();

if ($AllowCompanySelectionBox != 'Hide') {
	echo '</ol>';
}

echo '<label for="username">', __('User name'), ':</label>
	<input type="text" id="username" autocomplete="username" autofocus="autofocus" required="required" name="UserNameEntryField" placeholder="', __('User name'), '" maxlength="20" /><br />
	<label for="password">', __('Password'), ':</label>
	<input type="password" autocomplete="current-password" id="password" required="required" name="Password" placeholder="', __('Password'), '" />
	<input type="text" id="eye" readonly title="', __('Show Password'), '" />
	<div id="demo_text">';

if (isset($DemoText)) {
	echo $DemoText;
}

echo '</div>';

echo '<div style="text-align: left;">
        <button class="button" type="submit" value="', __('Login'), '" name="SubmitUser" onclick="ShowSpinner()">
            <img id="waiting_show" class="waiting_show" src="css/waiting.gif" />', __('Login'), ' ', '<img src="css/tick.png" title="', __('Login'), '" alt="" class="ButtonIcon" />
        </button>
      </div>';

echo '</form>
	</div>
	</div>';

echo '</body>
	</html>';

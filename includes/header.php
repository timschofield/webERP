<?php

// echo the html header and page title

// Variables which should be defined in the page this file is included with, before the inclusion of this header.php:
// $Language
// $Title
// various $_SESSION items: Theme, DefaultDateFormat, Timeout, ShowPageHelp, ShowFieldHelp, FontSize, UsersRealName, etc...

/// @todo there are any more global variables use in this script than those 3... are we sure it would work if
///       called within a function?
global $Language;
global $Title;
global $LanguagesArray;
global $RootPath;

//if (!isset($RootPath)) {
//	$RootPath = dirname(htmlspecialchars(basename(__FILE__)));
//	if ($RootPath == '/' or $RootPath == "\\") {
//		$RootPath = '';
//	}
//}

$ScriptName = basename($_SERVER['SCRIPT_NAME']);

if (!isset($ViewTopic)) {$ViewTopic = 'Contents';}
if (!isset($BookMark)) {$BookMark = '';}

/// @todo should we move this to session.php?
if (isset($_GET['Theme'])) {
	$_SESSION['Theme'] = $_GET['Theme'];
	$SQL = "UPDATE www_users SET theme='" . $_GET['Theme'] . "' WHERE userid='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
}

if ($LanguagesArray[$_SESSION['Language']]['Direction'] == 'rtl' and mb_substr($_SESSION['Theme'], -4) != '-rtl') {
	$_SESSION['Theme'] = $_SESSION['Theme'] . '-rtl';
}

if (!headers_sent()) {
	header('cache-control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
} else {
	trigger_error('Page output started before header file was included, this should not happen');
}

echo "<!DOCTYPE html>\n";

/// @todo handle better the case where $Language is not in xx-YY format (full spec is at https://www.rfc-editor.org/rfc/rfc5646.html)
echo '<html lang="' , str_replace('_', '-', substr($Language, 0, 5)) , '">
<head>
	<meta http-equiv="Content-Type" content="application/html; charset=utf-8; cache-control: no-cache, no-store, must-revalidate; Pragma: no-cache" />
	<title>', __('webERP'), ' - ', $Title, '</title>
	<link rel="icon" href="', $RootPath, '/favicon.ico" type="image/x-icon" />
	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/styles.css?version=1.0" rel="stylesheet" type="text/css" media="screen" />
	<link href="', $RootPath, '/css/print.css" rel="stylesheet" type="text/css" media="print" />
	<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '	<script async src="', $RootPath, '/javascripts/MiscFunctions.js?version=1.0"></script>' , "\n";
echo '	<script async src="', $RootPath, '/javascripts/manual.js"></script>' , "\n";
echo '	<script>
		localStorage.setItem("DateFormat", "', $_SESSION['DefaultDateFormat'], '");
		localStorage.setItem("Theme", "', $_SESSION['Theme'], '");
	</script>' , "\n";

if (isset($_SESSION['Timeout'])) {
	echo '	<meta http-equiv="refresh" content="' . (60 * $_SESSION['Timeout']) . ';url=' . $RootPath . '/Logout.php" />', "\n";
}

if ($_SESSION['ShowPageHelp'] == 0) {
	echo '	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/page_help_off.css" rel="stylesheet" type="text/css" media="screen" />' , "\n";
} else {
	echo '	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/page_help_on.css" rel="stylesheet" type="text/css" media="screen" />' , "\n";
}

if ($_SESSION['ShowFieldHelp'] == 0) {
	echo '	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/field_help_off.css" rel="stylesheet" type="text/css" media="screen" />' , "\n";
} else {
	echo '	<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/field_help_on.css" rel="stylesheet" type="text/css" media="screen" />' , "\n";
}

echo '	<style>
		body {
			font-size: ', $_SESSION['FontSize'], ';
		}
	</style>';

if (isset($ExtraHeadContent)) {
	echo "\n" . $ExtraHeadContent;
}

echo "\n</head>\n";

echo '<body onload="initial();' . (isset($BodyOnLoad) ? $BodyOnLoad : '') . '">' . "\n";

echo '<div class="help-bubble" id="help-bubble">
		<link rel="stylesheet" type="text/css" href="'. $RootPath . '/doc/Manual/css/manual.css" />
		<div class="help-header" id="help-header">
			<div id="help_exit" class="close_button" onclick="CloseHelp()" title="', __('Close this window'), '">X</div>
		</div>
		<div class="help-content" id="help-content"></div>
	</div>';

/// @todo should we move this to index.php?
if (isset($_GET['FontSize'])) {
	$SQL = "UPDATE www_users
				SET fontsize='" . $_GET['FontSize'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	switch ($_GET['FontSize']) {
		case 0:
			$_SESSION['ScreenFontSize'] = '0';
			$_SESSION['FontSize'] = '0.667rem';
		break;
		case 1:
			$_SESSION['ScreenFontSize'] = '1';
			$_SESSION['FontSize'] = '0.833rem';
		break;
		case 2:
			$_SESSION['ScreenFontSize'] = '2';
			$_SESSION['FontSize'] = '1rem';
		break;
		default:
			$_SESSION['ScreenFontSize'] = '1';
			$_SESSION['FontSize'] = '0.833rem';
	}
}

echo '<header class="noPrint">';

$CompanyLogo = '';
/// @todo move the scanning for a logo file to a dedicated function
if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.png')) {
	$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.png';
} else if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.jpeg')) {
	$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.jpeg';
} else if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.jpg')) {
	$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.jpg';
} else if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.gif')) {
	$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.gif';
}

echo '<div id="Info" data-title="', stripslashes($_SESSION['CompanyRecord']['coyname']), '">';
if ($CompanyLogo != '')
	echo '	<img src="', $CompanyLogo, '" alt="', stripslashes($_SESSION['CompanyRecord']['coyname']), '"/>';
echo '</div>';

echo '<div id="Info">
		<a class="FontSize" data-title="', __('Change the settings for'), ' ', $_SESSION['UsersRealName'], '" href="', $RootPath, '/UserSettings.php">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" alt="', stripslashes($_SESSION['UsersRealName']), '" />', $_SESSION['UsersRealName'], '
		</a>
	</div>';

echo '<div id="ExitIcon">
		<a data-title="', __('Logout'), '" href="', $RootPath, '/Logout.php" onclick="return confirm(\'', __('Are you sure you wish to logout?'), '\');">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/quit.png" alt="', __('Logout'), '" />
		</a>
	</div>';

// Fix: Ensure AllowedPageSecurityTokens is an array before counting
if (isset($_SESSION['AllowedPageSecurityTokens']) && is_array($_SESSION['AllowedPageSecurityTokens']) && count($_SESSION['AllowedPageSecurityTokens']) > 1) {

	$DefaultManualLink = '<div id="ActionIcon"><a data-title="' . __('Read the manual') . '" onclick="ShowHelp(\'' . $ViewTopic .'\',\'' . $BookMark . '\'); return false;" href="#"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/manual.png" alt="' . __('Help') . '" /></a></div>';

	if ($ScriptName != 'index.php') {
		if (strstr($_SESSION['Language'], 'en')) {
			echo $DefaultManualLink;
		} else {
			if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')) {
				echo '<div id="ActionIcon">
						<a data-title="', __('Read the manual'), '" href="', $RootPath, '/locale/', $_SESSION['Language'], '/Manual/ManualContents.php', $ViewTopic, $BookMark, '">
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/manual.png" onclick="ShowHelp(', $ViewTopic,',', $BookMark, ')" title="', __('Help'), '" alt="', __('Help'), '" />
						</a>
					</div>';
			} else {
				echo $DefaultManualLink;
			}
		}
	} else {
		echo '<div id="ActionIcon">
				<a data-title="', __('Read the manual'), '" href="', $RootPath, '/ManualContents.php" target="_blank">
					<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/manual.png" onclick="ShowHelp(', $ViewTopic,',', $BookMark, ')" title="', __('Help'), '" alt="', __('Help'), '" />
				</a>
			</div>';
	}

	$SQL = "SELECT caption, href FROM favourites WHERE userid='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$_SESSION['Favourites'][$MyRow['href']] = $MyRow['caption'];
	}
	if (DB_num_rows($Result) == 0) {
		$_SESSION['Favourites'] = Array();
	}

	echo '<div id="ActionIcon">
			<select name="Favourites" id="favourites" onchange="window.open (this.value,\'_self\',false)">';
	echo '<option value=""><i>', __('Commonly used scripts'), '</i></option>';
	foreach ($_SESSION['Favourites'] as $Url => $Caption) {
		echo '<option value="', $Url, '">', __($Caption), '</option>';
	}
	echo '</select>
		</div>';
	if ($ScriptName != 'index.php') {
		if (!isset($_SESSION['Favourites'][$ScriptName]) or $_SESSION['Favourites'][$ScriptName] == '') {
			echo '<div id="ActionIcon">
					<a data-title="', __('Add this script to your list of commonly used'), '">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/add.png" id="PlusMinus" onclick="AddScript(\'', $ScriptName, '\',\'', $Title, '\')"', ' alt="', __('Add to commonly used'), '" />
					</a>
				</div>';
		} else {
			echo '<div id="ActionIcon">
					<a data-title="', __('Remove this script from your list of commonly used'), '">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/subtract.png" id="PlusMinus" onclick="RemoveScript(\'', $ScriptName, '\')"', ' alt="', __('Remove from commonly used'), '" />
					</a>
				</div>';
		}
	}
}

if ($ScriptName != 'Dashboard.php') {
	echo '<div id="ActionIcon">
			<a data-title="', __('Show Dashboard'), '" href="', $RootPath, '/Dashboard.php">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/dashboard-icon.png" alt="', __('Show Dashboard'), '" />
			</a>
		</div>'; //take off inline formatting, use CSS instead ===HJ===

}

if ($ScriptName != 'index.php') {
	echo '<div id="ActionIcon">
			<a data-title="', __('Return to the main menu'), '" href="', $RootPath, '/index.php">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/home.png" alt="', __('Main Menu'), '" />
			</a>
		</div>'; //take off inline formatting, use CSS instead ===HJ===

}

echo '<br /><div class="ScriptTitle">', $Title, '</div>';
if ($ScriptName == 'index.php') {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	if ($_SESSION['ScreenFontSize'] == 0) {
		echo '<a style="font-size:0.667rem;" class="FontSize" href="', $RootPath, '/index.php?FontSize=0" data-title="', __('Small text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:0.667rem;" class="FontSize" href="', $RootPath, '/index.php?FontSize=0" data-title="', __('Small text size'), '">A</a>';
	}
	if ($_SESSION['ScreenFontSize'] == 1) {
		echo '<a style="font-size:0.833rem;" class="FontSize" href="', $RootPath, '/index.php?FontSize=1" data-title="', __('Medium text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:0.833rem;" class="FontSize" href="', $RootPath, '/index.php?FontSize=1" data-title="', __('Medium text size'), '">A</a>';
	}
	if ($_SESSION['ScreenFontSize'] == 2) {
		echo '<a style="font-size:1rem;" class="FontSize" href="', $RootPath, '/index.php?FontSize=2" data-title="', __('Large text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:1rem;" class="FontSize" href="', $RootPath, '/index.php?FontSize=2" data-title="', __('Large text size'), '">A</a>';
	}
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<div class="ScriptTitle">', __('Theme'), ':</div>';

	echo '<select name="Theme" id="favourites" onchange="window.open (\'index.php?Theme=\' + this.value,\'_self\',false)">';

	$Themes = glob('css/*', GLOB_ONLYDIR);
	foreach ($Themes as $ThemeName) {
		$ThemeName = basename($ThemeName);
		if ($ThemeName != 'mobile' and mb_substr($ThemeName, -4) != '-rtl') {
			if ($_SESSION['Theme'] == $ThemeName) {
				echo '<option selected="selected" value="', $ThemeName, '">', ucfirst($ThemeName), '</option>';
			} else {
				echo '<option value="', $ThemeName, '">', ucfirst($ThemeName), '</option>';
			}
		}
	}
	echo '</select>';
}

echo '</header>';

if ($ScriptName != 'index.php') {
	echo '<section class="MainBody">';
}

echo '<div id="MessageContainerHead"></div>';

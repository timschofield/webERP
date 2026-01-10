<?php

/***********************************************************************************************
*
* KL RICARD: Custom main menu, not show theme selection, Favourites, Dashboard. Always show main menu
*
***********************************************************************************************/

include('includes/KLRoles.php');

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
global $PathPrefix;

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

// Mobile menu styles and functionality
echo '<style>
	/* Header layout fix */
	.header-container {
		display: flex !important;
		align-items: center !important;
		justify-content: space-between !important;
		width: 100% !important;
	}
	
	/* Enhanced mobile menu styling */
	.mobile-menu-select {
		display: none;
		width: auto;
		min-width: 120px;
		font-size: 16px;
		font-weight: bold;
		padding: 6px 6px;
		border: 2px solid #007bff;
		border-radius: 6px;
		background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
		color: #333;
		cursor: pointer;
		margin: 0;
		box-shadow: 0 2px 4px rgba(0,123,255,0.15);
		transition: all 0.3s ease;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
	}
	
	.mobile-menu-select:hover {
		border-color: #0056b3;
		box-shadow: 0 4px 8px rgba(0,123,255,0.25);
		background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	}
	
	.mobile-menu-select:focus {
		outline: none;
		border-color: #007bff;
		box-shadow: 0 0 0 3px rgba(0,123,255,0.25), 0 3px 6px rgba(0,123,255,0.15);
	}
	
	@media (max-width: 768px) {
		.mobile-menu-select {
			display: block !important;
		}
		
		.action-icons-desktop {
			display: none !important;
		}
		
		/* Hide desktop logout button on mobile */
		#ExitIcon {
			display: none !important;
		}
		
		/* Ensure mobile menu is at same level as username */
		.mobile-menu-container {
			display: flex;
			align-items: center;
			margin: 0;
			padding: 0;
		}
	}
	
	@media (min-width: 769px) {
		.mobile-menu-select {
			display: none !important;
		}
		
		.action-icons-desktop {
			display: flex !important;
			align-items: center !important;
			gap: 0px !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		/* Ensure action icons are vertically centered with their text */
		#ActionIcon {
			display: flex !important;
			align-items: center !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		#ActionIcon a {
			display: flex !important;
			align-items: center !important;
			gap: 0px !important;
			text-decoration: none !important;
		}
		
		#ActionIcon img {
			vertical-align: middle !important;
			margin: 0 !important;
		}
		
		/* Ensure proper header layout on desktop - remove auto margin */
		.header-container {
			align-items: center !important;
		}
		
		#ExitIcon {
			display: flex !important;
			align-items: center !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		/* Reduce spacing between header and script title */
		.ScriptTitle {
			margin-top: 0px !important;
			margin-bottom: 0 !important;
			padding-top: 0 !important;
		}
		
		header.noPrint {
			margin-bottom: 0px !important;
			padding-bottom: 0 !important;
		}
	}
</style>

<script>
	function navigateToSelected(select) {
		var url = select.value;
		if (url) {
			// Special handling for logout with confirmation
			if (url.indexOf("Logout.php") !== -1) {
				if (confirm("Are you sure you wish to logout?")) {
					window.location.href = url;
				}
				select.value = ""; // Reset dropdown
			} else {
				window.location.href = url;
			}
		}
	}
</script>';

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

 
/* KL RICARD Comment these lines as only show an X on the left top corner
echo '<div class="help-bubble" id="help-bubble">
	<div class="help-header" id="help-header">
		<div id="help_exit" class="close_button" onclick="CloseHelp()" title="', __('Close this window'), '">X</div>
	</div>
	<div class="help-content" id="help-content"></div>
</div>';
KL RICARD Comment these lines as only show an X on the left top corner */
/* KL RICARD
KL RICARD END */
/* KL RICARD
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

echo '<style>
			body {
					font-size: ', $_SESSION['FontSize'], ';
				}
			</style>';

if (isset($ExtraHeadContent)) {
	echo "\n" . $ExtraHeadContent;
}

echo "\n</head>\n";

echo '<body onload="initial();' . ($BodyOnLoad ?? '') . '">' . "\n";

echo '<div class="help-bubble" id="help-bubble">
		<link rel="stylesheet" type="text/css" href="'. $RootPath . '/doc/Manual/css/manual.css" />
		<div class="help-header" id="help-header">
			<div id="help_exit" class="close_button" onclick="CloseHelp()" title="', __('Close this window'), '">X</div>
		</div>
		<div class="help-content" id="help-content"></div>
	</div>';

KL RICARD END */

echo '<header class="noPrint">';
/* KL RICARD: Header container with flexbox for better alignment to the right and spacing */
echo '<div class="header-container" style="display: flex; align-items: center; justify-content: space-between; padding: 0px; width: 100%;">';
/* KL RICARD END */

$CompanyLogo = '';
/// @todo move the scanning for a logo file to a dedicated function
if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.png')) {
	$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.png';
} elseif (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.jpeg')) {
	$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.jpeg';
} elseif (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.jpg')) {
	$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.jpg';
} elseif (file_exists('companies/' . $_SESSION['DatabaseName'] . '/logo.gif')) {
	$CompanyLogo = $RootPath . '/companies/' . $_SESSION['DatabaseName'] . '/logo.gif';
}

/* KL RICARD: Do NOT Show the company logo
echo '<div id="Info" data-title="', stripslashes($_SESSION['CompanyRecord']['coyname']), '">';
if ($CompanyLogo != ''){
	echo '	<img src="', $CompanyLogo, '" alt="', stripslashes($_SESSION['CompanyRecord']['coyname']), '"/>';
}
KL RICARD END: Do NOT Show the company logo */

// User info section - left side of header
echo '<div id="Info" style="display: flex; align-items: center;">
		<a class="FontSize" data-title="', __('Change the settings for'), ' ', $_SESSION['UsersRealName'], '" href="', $RootPath, '/UserSettings.php">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" alt="', stripslashes($_SESSION['UsersRealName']), '" />', $_SESSION['UsersRealName'], '
		</a>
	</div>';

// Action icons and controls section - right side of header  
echo '<div style="display: flex; align-items: center; gap: 10px;">';

// Fix: Ensure AllowedPageSecurityTokens is an array before counting
if (isset($_SESSION['AllowedPageSecurityTokens']) && is_array($_SESSION['AllowedPageSecurityTokens']) && count($_SESSION['AllowedPageSecurityTokens']) > 1) {

	$DefaultManualLink = '<div id="ActionIcon"><a data-title="' . __('Read the manual') . '" onclick="ShowHelp(\'' . $ViewTopic .'\',\'' . $BookMark . '\'); return false;" href="#"><img src="' . $PathPrefix . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/manual.png" alt="' . __('Help') . '" /></a></div>';

	// Mobile menu - enhanced select dropdown
	echo '<div class="mobile-menu-container">
		<select class="mobile-menu-select" onchange="navigateToSelected(this)">
			<option value="" disabled selected>📱 ', __('Top Menu'), '</option>
			<option value="', $RootPath, '/index.php">� ', __('Main Menu'), '</option>';
			
	if (!$KL_SPGSeniorOrSupport AND !$KL_SPGJunior){
		echo '<option value="', $RootPath, '/SelectCustomer.php">👥 ', __('Customers'), '</option>';
		echo '<option value="', $RootPath, '/SelectProduct.php">📦 ', __('Items'), '</option>';
		echo '<option value="', $RootPath, '/SelectSupplier.php">🏢 ', __('Suppliers'), '</option>';
	}
	
	echo '<option value="https://ptadu.com/wiki/index.php">🌐 ', __('Intranet'), '</option>';
//	echo '<option value="https://kapal-laut.com">🛒 ', __('Online Shop'), '</option>';
	echo '<option value="', $RootPath, '/Logout.php" onclick="return confirm(\'', __('Are you sure you wish to logout?'), '\');">🚪 ', __('Logout'), '</option>
		</select>
	</div>';

	// Desktop action icons container
	echo '<div class="action-icons-desktop">';

	/* KL RICARD Customized Action Icons on every page */
	// 1st - Main menu
	echo '<div id="ActionIcon">
		<a class="FontSize" data-title="', __('Return to the main menu'), '" href="', $PathPrefix, $RootPath, '/index.php">
			<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/home.png" alt="', __('Main Menu'), '" />', __('Main Menu'), '
		</a>
	</div>';

	// 2nd - Items (only for non-SPG users)
	if (!$KL_SPGSeniorOrSupport 
		AND !$KL_SPGJunior){
		echo '<div id="ActionIcon">
			<a class="FontSize" data-title="', __('Customers'), '" href="', $PathPrefix, $RootPath, '/SelectCustomer.php">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" alt="', __('Customers'), '" />', __('Customers'), '
			</a>
		</div>';

		echo '<div id="ActionIcon">
			<a class="FontSize" data-title="', __('Items'), '" href="', $PathPrefix, $RootPath, '/SelectProduct.php">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" alt="', __('Items'), '" />', __('Items'), '
			</a>
		</div>';

		echo '<div id="ActionIcon">
			<a class="FontSize" data-title="', __('Suppliers'), '" href="', $PathPrefix, $RootPath, '/SelectSupplier.php">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" alt="', __('Suppliers'), '" />', __('Suppliers'), '
			</a>
		</div>';

	}

	// 3rd - Intranet
	echo '<div id="ActionIcon">
		<a class="FontSize" data-title="', __('Intranet'), '" href="https://ptadu.com/wiki/index.php">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/plugin.png" alt="', __('Intranet'), '" />', __('Intranet'), '
		</a>
	</div>';

	// 4th - Online Shop
	/* KL RICARD No show the Online Shop link as this has been moved to KL Intranet
	echo '<div id="ActionIcon">
		<a class="FontSize" data-title="', __('Online Shop'), '" href="https://kapal-laut.com">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" alt="', __('Online Shop'), '" />', __('Online Shop'), '
		</a>
	</div>';
	*/
	echo '</div>'; // Close desktop container

	/* KL RICARD No show the Manual Link
	$DefaultManualLink = '<div id="ActionIcon"><a data-title="' . __('Read the manual') . '" onclick="ShowHelp(\'' . $ViewTopic .'\',\'' . $BookMark . '\'); return false;" href="#"><img src="' . $PathPrefix . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/manual.png" alt="' . __('Help') . '" /></a></div>';

	if ($ScriptName != 'index.php') {
		if (strstr($_SESSION['Language'], 'en')) {
			echo $DefaultManualLink;
		} else {
			if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')) {
				echo '<div id="ActionIcon">
						<a data-title="', __('Read the manual'), '" href="', $PathPrefix, $RootPath, '/locale/', $_SESSION['Language'], '/Manual/ManualContents.php', $ViewTopic, $BookMark, '">
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/manual.png" onclick="ShowHelp(', $ViewTopic,',', $BookMark, ')" title="', __('Help'), '" alt="', __('Help'), '" />
						</a>
					</div>';
			} else {
				echo $DefaultManualLink;
			}
		}
	} else {
		echo '<div id="ActionIcon">
				<a data-title="', __('Read the manual'), '" href="', $PathPrefix, $RootPath, '/ManualContents.php" target="_blank">
					<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/manual.png" onclick="ShowHelp(', $ViewTopic,',', $BookMark, ')" title="', __('Help'), '" alt="', __('Help'), '" />
				</a>
			</div>';
	}
	KL RICARD END No show the Manual Link */
	
	/* KL RICARD No show the Favourites
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
	KL RICARD END  No show the Favourites */
}

// Logout button - always visible on the right
echo '<div id="ExitIcon">
		<a data-title="', __('Logout'), '" href="', $RootPath, '/Logout.php" onclick="return confirm(\'', __('Are you sure you wish to logout?'), '\');">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/quit.png" alt="', __('Logout'), '" />
		</a>
	</div>';

echo '</div>'; // Close action icons and controls section
echo '</div>'; // Close header-container

/* KL RICARD No show the Dashboard
if ($ScriptName != 'Dashboard.php') {
	echo '<div id="ActionIcon">
			<a data-title="', __('Show Dashboard'), '" href="', $PathPrefix, $RootPath, '/Dashboard.php">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/dashboard-icon.png" alt="', __('Show Dashboard'), '" />
			</a>
		</div>'; //take off inline formatting, use CSS instead ===HJ===

}
KL RICARD END No show the Dashboard */

// KL RICARD Show the location name for SPG users on every page
if ($KL_SPGSeniorOrSupport 
	OR $KL_SPGJunior){
	echo '<div class="ScriptTitle">', $_SESSION['locationname'], '</div>';
} else {
	echo '<div class="ScriptTitle">', $Title, '</div>';
}
// KL RICARD END Show the location name for SPG users on every page

if ($ScriptName == 'index.php') {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
/*	KL RICARD No show the Font Size
	if ($_SESSION['ScreenFontSize'] == 0) {
		echo '<a style="font-size:0.667rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=0" data-title="', __('Small text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:0.667rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=0" data-title="', __('Small text size'), '">A</a>';
	}
	if ($_SESSION['ScreenFontSize'] == 1) {
		echo '<a style="font-size:0.833rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=1" data-title="', __('Medium text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:0.833rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=1" data-title="', __('Medium text size'), '">A</a>';
	}
	if ($_SESSION['ScreenFontSize'] == 2) {
		echo '<a style="font-size:1rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=2" data-title="', __('Large text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:1rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=2" data-title="', __('Large text size'), '">A</a>';
	}
KL RICARD No show the Font Size */

/*	KL RICARD No show the theme
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
KL RICARD No show the theme */
}

echo '</header>';

if ($ScriptName != 'index.php') {
	echo '<section class="MainBody">';
}

echo '<div id="MessageContainerHead"></div>';

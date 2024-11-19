<?php
// Titles and screen header
// Needs the file config.php loaded where the variables are defined for
//  $RootPath
//  $Title - should be defined in the page this file is included with
if (!isset($RootPath)) {
	$RootPath = dirname(htmlspecialchars(basename(__FILE__)));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
}

$ViewTopic = isset($ViewTopic) ? '?ViewTopic=' . $ViewTopic : '';
$BookMark = isset($BookMark) ? '#' . $BookMark : '';

if (isset($_GET['Theme'])) {
	$_SESSION['Theme'] = $_GET['Theme'];
	$SQL = "UPDATE www_users SET theme='" . $_GET['Theme'] . "' WHERE userid='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
}

if ($LanguagesArray[$_SESSION['Language']]['Direction'] == 'rtl' and mb_substr($_SESSION['Theme'], -4) != '-rtl') {
	$_SESSION['Theme'] = $_SESSION['Theme'] . '-rtl';
}

if (isset($Title) and $Title == _('Copy a BOM to New Item Code')) { //solve the cannot modify heaer information in CopyBOM.php scritps
	ob_start();
}

/*	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
		'<html xmlns="http://www.w3.org/1999/xhtml">',

		'<head>',
			'<link rel="icon" href="', $RootPath, '/favicon.ico" />',
			'<link rel="shortcut icon" href="', $RootPath, '/favicon.ico" />';
	if ($StrictXHTML) {
		echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
	} else {
		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
	}
    echo	'<link href="', $RootPath, '/css/print.css" rel="stylesheet" type="text/css" media="print" />',
			'<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/default.css" rel="stylesheet" type="text/css" media="screen"/>',
/			'<script type="text/javascript" src="', $RootPath, '/javascripts/MiscFunctions.js"></script>',
			'<title>', $Title, '</title>',
		'</head>',

		'<body>',
			'<div id="CanvasDiv">',
			'<input type="hidden" name="Lang" id="Lang" value="', $Lang, '" />',
			'<div id="HeaderDiv">',
				'<div id="HeaderWrapDiv">';

	if (isset($Title)) {
		echo '<div id="AppInfoDiv">'; //===HJ===
			echo '<div id="AppInfoCompanyDiv">';
				echo '<img alt="'._('Company').'" src="'.$RootPath.'/css/'.$Theme.'/images/company.png" title="'._('Company').'" />' . stripslashes($_SESSION['CompanyRecord']['coyname']);
			echo '</div>';
			echo '<div id="AppInfoUserDiv">';
				echo '<a href="'.$RootPath.'/UserSettings.php"><img alt="'._('User').'" src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="'._('User').'" />' . stripslashes($_SESSION['UsersRealName']) . '</a>';
			echo '</div>';
			echo '<div id="AppInfoModuleDiv">';
				// Make the title text a class, can be set to display:none is some themes
				echo $Title;
			echo '</div>';
		echo '</div>'; // AppInfoDiv


		echo '<div id="QuickMenuDiv"><ul>';

		echo '<li><a href="'.$RootPath.'/index.php">' . _('Main Menu') . '</a></li>'; //take off inline formatting, use CSS instead ===HJ===

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){
			echo '<li><a href="'.$RootPath.'/SelectCustomer.php">' . _('Customers') . '</a></li>';
			echo '<li><a href="'.$RootPath.'/SelectProduct.php">' . _('Items')     . '</a></li>';
			echo '<li><a href="'.$RootPath.'/SelectSupplier.php">' . _('Suppliers') . '</a></li>';

			$DefaultManualLink = '<li><a rel="external" accesskey="8" href="' .  $RootPath . '../../wiki/index.php'. '">' . _('Intranet KL') . '</a></li>';
			echo $DefaultManualLink;
			$DefaultWebLink = '<li><a rel="external" accesskey="9" href="' .   'https://kapal-laut.com'. '">' . _('Online Shop') . '</a></li>';
			echo $DefaultWebLink;
		}

		echo '<li><a href="'.$RootPath.'/Logout.php" onclick="return confirm(\''._('Are you sure you wish to logout?').'\');">' . _('Logout') . '</a></li>';

		echo '</ul></div>'; // QuickMenuDiv
	}
	echo		'</div>',// Close HeaderWrapDiv
			'</div>',// Close Headerdiv
			'<div id="BodyDiv">',
				'<div id="BodyWrapDiv">';
*/	

echo '<!DOCTYPE html>';

echo '<html>
		<head>
			<meta http-equiv="Content-Type" content="application/html; charset=utf-8; cache-control: no-cache, no-store, must-revalidate; Pragma: no-cache" />
			<title>', _('webERP'), ' - ', $Title, '</title>
			<link rel="icon" href="', $PathPrefix, $RootPath, '/favicon.ico?v=2" />
			<link href="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/styles.css?v=30" rel="stylesheet" type="text/css" media="screen" />
			<link href="', $PathPrefix, $RootPath, '/css/print.css" rel="stylesheet" type="text/css" media="print" />
			<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<script async type="text/javascript" src = "', $PathPrefix, $RootPath, '/javascripts/MiscFunctions.js"></script>';
echo '<script>
		localStorage.setItem("DateFormat", "', $_SESSION['DefaultDateFormat'], '");
		localStorage.setItem("Theme", "', $_SESSION['Theme'], '");
	</script>';

if ($_SESSION['ShowPageHelp'] == 0) {
	echo '<link href="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/page_help_off.css" rel="stylesheet" type="text/css" media="screen" />';
} else {
	echo '<link href="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/page_help_on.css" rel="stylesheet" type="text/css" media="screen" />';
}

if ($_SESSION['ShowFieldHelp'] == 0) {
	echo '<link href="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/field_help_off.css" rel="stylesheet" type="text/css" media="screen" />';
} else {
	echo '<link href="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/field_help_on.css" rel="stylesheet" type="text/css" media="screen" />';
}

echo '</head>';
if (isset($AutoPrintPage)) {
	echo '<body onload="window.print()">';
} else {
	echo '<body onload="initial(); load()" onunload="GUnload()">';
}

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

$ScriptName = basename($_SERVER['SCRIPT_NAME']);

echo '<header class="noPrint">';

echo '<div id="Info" data-title="', _('Company Details'), '">
		<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/company.png" alt="', _('Company'), '"/>', stripslashes($_SESSION['CompanyRecord']['coyname']), '
	</div>';

echo '<div id="Info">
		<a class="FontSize" data-title="', _('Change the settings for'), ' ', $_SESSION['UsersRealName'], '" href="', $PathPrefix, $RootPath, '/UserSettings.php">
			<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" alt="', stripslashes($_SESSION['UsersRealName']), '" />', $_SESSION['UsersRealName'], '
		</a>
	</div>';

echo '<div id="ExitIcon">
		<a data-title="', _('Logout'), '" href="', $PathPrefix, $RootPath, '/Logout.php" onclick="return confirm(\'', _('Are you sure you wish to logout?'), '\');">
			<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/quit.png" alt="', _('Logout'), '" />
		</a>
	</div>';

if (count($_SESSION['AllowedPageSecurityTokens']) > 1) {

	echo '<div id="ActionIcon">
		<a class="FontSize" data-title="', _('Online Shop'), '" href="https://kapal-laut.com">
			<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" alt="', _('Online Shop'), '" />', _('Online Shop'), '
		</a>
	</div>';

	echo '<div id="ActionIcon">
		<a class="FontSize" data-title="', _('Intranet'), '" href="', $PathPrefix, $RootPath, '../../wiki/index.php">
			<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/plugin.png" alt="', _('Intranet'), '" />', _('Intranet'), '
		</a>
	</div>';

	echo '<div id="ActionIcon">
		<a class="FontSize" data-title="', _('Suppliers'), '" href="', $PathPrefix, $RootPath, '/SelectSupplier.php">
			<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" alt="', _('Suppliers'), '" />', _('Suppliers'), '
		</a>
	</div>';

	echo '<div id="ActionIcon">
		<a class="FontSize" data-title="', _('Items'), '" href="', $PathPrefix, $RootPath, '/SelectProduct.php">
			<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" alt="', _('Items'), '" />', _('Items'), '
		</a>
	</div>';

	echo '<div id="ActionIcon">
		<a class="FontSize" data-title="', _('Customers'), '" href="', $PathPrefix, $RootPath, '/SelectCustomer.php">
			<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" alt="', _('Customers'), '" />', _('Customers'), '
		</a>
	</div>';


/*	$DefaultManualLink = '<div id="ActionIcon"><a data-title="' . _('Read the manual') . '" target="_blank" href="' . $PathPrefix . $RootPath . '/doc/Manual/ManualContents.php' . $ViewTopic . $BookMark . '"><img src="' . $PathPrefix . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/manual.png" alt="' . _('Help') . '" /></a></div>';

	if (strstr($_SESSION['Language'], 'en')) {
		echo $DefaultManualLink;
	} else {
		if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')) {
			echo '<div id="ActionIcon">
					<a data-title="', _('Read the manual'), '" href="', $PathPrefix, $RootPath, '/locale/', $_SESSION['Language'], '/Manual/ManualContents.php', $ViewTopic, $BookMark, '">
						<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/manual.png" title="', _('Help'), '" alt="', _('Help'), '" />
					</a>
				</div>';
		} else {
			echo $DefaultManualLink;
		}
	}
*/
/*	if (!isset($_SESSION['Favourites'])) {
		$SQL = "SELECT caption, href FROM favourites WHERE userid='" . $_SESSION['UserID'] . "'";
		$Result = DB_query($SQL);
		while ($MyRow = DB_fetch_array($Result)) {
			$_SESSION['Favourites'][$MyRow['href']] = $MyRow['caption'];
		}
		if (DB_num_rows($Result) == 0) {
			$_SESSION['Favourites'] = Array();
		}
	}
	echo '<div id="ActionIcon">
			<select name="Favourites" id="favourites" onchange="window.open (this.value,\'_self\',false)">';
	echo '<option value=""><i>', _('Commonly used scripts'), '</i></option>';
	foreach ($_SESSION['Favourites'] as $Url => $Caption) {
		echo '<option value="', $Url, '">', _($Caption), '</option>';
	}
	echo '</select>
		</div>';
	if ($ScriptName != 'index.php') {
		if (!isset($_SESSION['Favourites'][$ScriptName]) or $_SESSION['Favourites'][$ScriptName] == '') {
			echo '<div id="ActionIcon">
					<a data-title="', _('Add this script to your list of commonly used'), '">
						<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/add.png" id="PlusMinus" onclick="AddScript(\'', $ScriptName, '\',\'', $Title, '\')"', ' alt="', _('Add to commonly used'), '" />
					</a>
				</div>';
		} else {
			echo '<div id="ActionIcon">
					<a data-title="', _('Remove this script from your list of commonly used'), '">
						<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/subtract.png" id="PlusMinus" onclick="RemoveScript(\'', $ScriptName, '\')"', ' alt="', _('Remove from commonly used'), '" />
					</a>
				</div>';
		}
	}
*/

}
/*
if ($ScriptName != 'Dashboard.php') {
	echo '<div id="ActionIcon">
			<a data-title="', _('Show Dashboard'), '" href="', $PathPrefix, $RootPath, '/Dashboard.php">
				<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/dashboard-icon.png" alt="', _('Show Dashboard'), '" />
			</a>
		</div>'; //take off inline formatting, use CSS instead ===HJ===

}
*/
if ($ScriptName != 'index.php') {
	echo '<div id="ActionIcon">
			<a data-title="', _('Return to the main menu'), '" href="', $PathPrefix, $RootPath, '/index.php">
				<img src="', $PathPrefix, $RootPath, '/css/', $_SESSION['Theme'], '/images/home.png" alt="', _('Main Menu'), '" />
			</a>
		</div>'; //take off inline formatting, use CSS instead ===HJ===

}

echo '<br /><div class="ScriptTitle">', $Title, '</div>';
if ($ScriptName == 'index.php') {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	if ($_SESSION['ScreenFontSize'] == 0) {
		echo '<a style="font-size:0.667rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=0" data-title="', _('Small text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:0.667rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=0" data-title="', _('Small text size'), '">A</a>';
	}
	if ($_SESSION['ScreenFontSize'] == 1) {
		echo '<a style="font-size:0.833rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=1" data-title="', _('Medium text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:0.833rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=1" data-title="', _('Medium text size'), '">A</a>';
	}
	if ($_SESSION['ScreenFontSize'] == 2) {
		echo '<a style="font-size:1rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=2" data-title="', _('Large text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:1rem;" class="FontSize" href="', $PathPrefix, $RootPath, '/index.php?FontSize=2" data-title="', _('Large text size'), '">A</a>';
	}
/*	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<div class="ScriptTitle">', _('Theme'), ':</div>';

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
*/
}

echo '</header>';

if ($ScriptName != 'index.php') {
	echo '<section class="MainBody">';
}

echo '<div id="MessageContainerHead"></div>';
			
/* KL RICARD Log the script we run so we can optimize CPU time*/	
$_SESSION['ScriptStartTime'] = microtime();

?>

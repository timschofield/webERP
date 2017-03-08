<?php
/* $Id: header.inc 7324 2015-06-20 14:49:56Z rchacon $ */

	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	// $RootPath
	// $Title - should be defined in the page this file is included with
	if (!isset($RootPath)){
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		if ($RootPath == '/' OR $RootPath == "\\") {
			$RootPath = '';
		}
	}

	$ViewTopic = isset($ViewTopic)?'?ViewTopic=' . $ViewTopic : '';
	$BookMark = isset($BookMark)? '#' . $BookMark : '';
	$StrictXHTML=False;

	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}
	}
	if($Title == _('Copy a BOM to New Item Code')){//solve the cannot modify heaer information in CopyBOM.php scritps
		ob_start();
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
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
/*			'<meta name="viewport" content="width=device-width, initial-scale=1">',//To tell the small device that the website is a responsive site (keep relationship between CSS pixels and device pixels).*/
			'<script type="text/javascript" src="', $RootPath, '/javascripts/MiscFunctions.js"></script>',
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

			$DefaultManualLink = '<li><a rel="external" accesskey="8" href="' .  $RootPath . '/doc/Manual/ManualContents.php'. $ViewTopic . $BookMark. '">' . _('Manual') . '</a></li>';

			if (mb_substr($_SESSION['Language'],0,2) != 'en'){
				if (file_exists('locale/'.$_SESSION['Language'].'/Manual/ManualContents.php')){
					echo '<li><a target="_blank" href="'.$RootPath.'/locale/'.$_SESSION['Language'].'/Manual/ManualContents.php'. $ViewTopic . $BookMark. '">' . _('Manual') . '</a></li>';
				} else {
					echo $DefaultManualLink;
				}
			} else {
					echo $DefaultManualLink;
			}
		}

		echo '<li><a href="'.$RootPath.'/Logout.php" onclick="return confirm(\''._('Are you sure you wish to logout?').'\');">' . _('Logout') . '</a></li>';

		echo '</ul></div>'; // QuickMenuDiv
	}
	echo		'</div>',// Close HeaderWrapDiv
			'</div>',// Close Headerdiv
			'<div id="BodyDiv">',
				'<div id="BodyWrapDiv">';

?>

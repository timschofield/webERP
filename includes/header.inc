<?php
/* $Id$ */

	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with
	if (!isset($rootpath)){
		$rootpath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		if ($rootpath == '/' OR $rootpath == "\\") {
			$rootpath = '';
		}
	}
	$StrictXHTML=False;
    $ViewTopic = isset($ViewTopic)?"?ViewTopic=$ViewTopic":'';
    $BookMark = isset($BookMark)? "#$BookMark":'';
	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}
	}
	if($title == _('Copy a BOM to New Item Code')){//solve the cannot modify heaer information in CopyBOM.php scritps
		ob_start();
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';


	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath.'/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath.'/favicon.ico" />';
	if ($StrictXHTML) {
		echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
	} else {
		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
	}
	echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/MiscFunctions.js"></script>';
	echo '</head>';

	echo '<body>';

	echo '<div id="CanvasDiv">'; //start of CANVAS div
	echo '<div id="HeaderDiv">'; //start of HEADER div

	echo '<table class="HeaderTbl">';
	echo '<tr>';
	echo '<td colspan="2" rowspan="2">';

	if (isset($title)) {
		echo '<table cellpadding="0" cellspacing="0" border="0" id="quick_menu" class="quick_menu">';
		echo '<tr>';
		echo '<td align="left" style="width:100%;" class="quick_menu_left">';
// Use icons for company and user data, saves screen realestate, use alt tag in case theme icon not avail.
		echo '<img src="'.$rootpath.'/css/'.$theme.'/images/company.png" title="' . _('Company') . '" alt="' . _('Company') . '" />';
		echo ' ' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '  <a href="' .  $rootpath . '/UserSettings.php"><img src="'.$rootpath.'/css/'.$theme.'/images/user.png" title="User" alt="' . _('User') . '" />' . stripslashes($_SESSION['UsersRealName']) . '</a>';
// Make the title text a class, can be set to display:none is some themes
		echo '<br />' . $title . '</td>';
		echo '<td class="quick_menu_tabs">';
		echo '<table cellpadding="0" cellspacing="0" class="quick_menu_tabs"><tr>';
		echo '<td class="quick_menu_tab" align="center"><a href="' .  $rootpath . '/index.php"><span style="text-decoration:underline;">1</span> ' . _('Main Menu') . '</a></td>';

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){

			echo '<td class="quick_menu_tab" align="center"><a href="' .  $rootpath . '/SelectCustomer.php"><span style="text-decoration:underline;">2</span> ' . _('Customers') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a href="' .  $rootpath . '/SelectProduct.php"><span style="text-decoration:underline;">3</span> ' . _('Items') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a href="' .  $rootpath . '/SelectSupplier.php"><span style="text-decoration:underline;">4</span> ' . _('Suppliers') . '</a></td>';

			$DefaultManualLink = '<td class="quick_menu_tab" align="center"><a rel="external" accesskey="8" href="' .  $rootpath . '/doc/Manual/ManualContents.php'. $ViewTopic . $BookMark. '"><span style="text-decoration:underline;">8</span> ' . _('Manual') . '</a></td>';

			if (mb_substr($_SESSION['Language'],0,2) !='en'){
				if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')){
					echo '<td class="quick_menu_tab" align="center"><a target="_blank" href="' .  $rootpath . '/locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php'.$ViewTopic . $BookMark.'"><span style="text-decoration:underline;">8</span> ' . _('Manual') . '</a></td>';
				} else {
					echo $DefaultManualLink;
				}
			} else {
					echo $DefaultManualLink;
			}
		}

		echo '<td class="quick_menu_tab" align="center"><a href="' . $rootpath . '/Logout.php" onclick="return confirm(\'' . _('Are you sure you wish to logout?') . '\');"><span style="text-decoration:underline;">0</span> '  . _('Logout') . '</a></td>';

		echo '</tr></table>';
		echo '</td></tr></table>';

	}

echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>'; //end of HEADER div
echo '<div id="BodyDiv">'; //start of BODY div - contains left menu, three columns menu, forms, data, etc
?>

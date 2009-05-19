<?php

/* $Revision: 1.43 $ */
	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with

	if (!headers_sent()){
		header('Content-type: text/html; charset=' . _('iso-8859-1'));
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';


	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath.'/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath.'/favicon.ico" />';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=' . _('ISO-8859-1') . '" />';
	echo '<link href="'.$rootpath. '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/MiscFunctions.js"></script>';
	echo '</head>';

	echo '<body>';
	echo '<table class="callout_main" cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<td colspan="2" rowspan="2">';

	echo '<table class="main_page" cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<td>';
	echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" >';
	echo '<tr>';
	echo '<td>';

	if ($title AND substr($title,0,4) != 'Help') {
		echo '<table cellpadding="0" cellspacing="0" border="0" id="quick_menu" class="quick_menu">';
		echo '<tr>';
		echo '<td align="left" style="width:100%;" class="quick_menu_left">';
// Use icons for company and user data, saves screen realestate, use ALT tag in case theme icon not avail.
		echo '<img src="'.$rootpath.'/css/'.$theme.'/images/company.png" TITLE="' . _('Company') . '" ALT="' . _('Company') . '">';
		echo ' ' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '  <a href="' .  $rootpath . '/UserSettings.php?' . SID . '"><img src="'.$rootpath.'/css/'.$theme.'/images/user.png" TITLE="User" ALT="' . _('User') . '"> ' . stripslashes($_SESSION['UsersRealName']) . '</a>';
// Make the title text a class, can be set to display:none is some themes
		echo '<BR><font class="header_title"> ' . $title . '</font></td>';
		echo ' </td>';

		echo '<td class="quick_menu_tabs">';
		echo '<table cellpadding="0" cellspacing="0" class="quick_menu_tabs"><tr>';
		echo '<td class="quick_menu_tab" align="center"><a accesskey="1" href="' .  $rootpath . '/index.php?' . SID . '"><span style="text-decoration:underline;">1</span> ' . _('Main Menu') . '</a></td>';

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){

			echo '<td class="quick_menu_tab" align="center"><a accesskey="2" href="' .  $rootpath . '/SelectCustomer.php?' . SID . '"><span style="text-decoration:underline;">2</span> ' . _('Customers') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a accesskey="3" href="' .  $rootpath . '/SelectProduct.php?' . SID . '"><span style="text-decoration:underline;">3</span> ' . _('Items') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a accesskey="4" href="' .  $rootpath . '/SelectSupplier.php?' . SID . '"><span style="text-decoration:underline;">4</span> ' . _('Suppliers') . '</a></td>';

			$DefaultManualLink = '<td class="quick_menu_tab" align="center"><a rel="external" accesskey="8" href="' .  $rootpath . '/doc/Manual/ManualContents.php?' . SID . '"><span style="text-decoration:underline;">8</span> ' . _('Manual') . '</a></td>';

			if (substr($_SESSION['Language'],0,2) !='en'){
				if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')){
					echo '<td class="quick_menu_tab" align="center"><a target="_blank" accesskey="8" href="' .  $rootpath . '/locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php' . SID . '"><span style="text-decoration:underline;">8</span> ' . _('Manual') . '</a></td>';
				} else {
					echo $DefaultManualLink;
				}
			} else {
					echo $DefaultManualLink;
			}
		}

		echo '<td class="quick_menu_tab" align="center"><a accesskey="0" href="' . $rootpath . '/Logout.php?' . SID . '" onclick="return confirm(\'' . _('Are you sure you wish to logout?') . '\');"><span style="text-decoration:underline;">0</span> '  . _('Logout') . '</a></td>';

		echo '</tr></table>';
		echo '</td></tr></table>';

	}

echo '</td>';
echo '</tr>';
echo '</table>';

?>

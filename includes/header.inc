<?php

/* $Revision: 1.33 $ */
	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with

	if (!headers_sent()){
		header('Content-type: text/html; charset=' . _('ISO-8859-1'));
	}
	echo '<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">';
	
	
	echo '<html><head><title>' . $title . '</title>';
	echo '<link REL="shortcut icon" HREF="'. $rootpath.'/favicon.ico">';
	echo '<link REL="icon" HREF="' . $rootpath.'/favicon.ico">';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=' . _('ISO-8859-1') . '">';
	echo '<link href="'.$rootpath. '/css/'. $_SESSION['Theme'] .'/default.css" REL="stylesheet" TYPE="text/css">';
		
	echo '</head>';

	echo '<body>';
	echo '<table class="callout_main" cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<td colspan="2" rowspan="2">';

	echo '<table class="main_page" align="right" cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<td>';
	echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" >';
	echo '<tr>';
	echo '<td>';

	if ($title AND substr($title,0,4) != 'Help') {
		echo '<table cellpadding="0" cellspacing="0" border="0" id="quick_menu" class="quick_menu">';
		echo '<tr>';
		echo '<td align="left" width="100%" class="quick_menu_left">';
		echo  stripslashes($_SESSION['CompanyRecord']['coyname']) . ': <A HREF="' .  $rootpath . '/UserSettings.php?' . SID . '">' . stripslashes($_SESSION['UsersRealName']) . '</A>';
		echo '<BR>' . $title . '</TD>';
		
		echo '<td class="quick_menu_tabs">';
		echo '<table cellpadding="0" cellspacing="0" class="quick_menu_tabs"><tr>';
		echo '<td class="quick_menu_tab" align="center"><a accesskey="1" href="' .  $rootpath . '/index.php?' . SID . '"><u>1</u> ' . _('Main Menu') . '</a></td>';

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){

			echo '<td class="quick_menu_tab" align="center"><a accesskey="2" href="' .  $rootpath . '/SelectCustomer.php?' . SID . '"><u>2</u> ' . _('Customers') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a accesskey="3" href="' .  $rootpath . '/SelectProduct.php?' . SID . '"><u>3</u> ' . _('Items') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><A ACCESSKEY="4" HREF="' .  $rootpath . '/SelectSupplier.php?' . SID . '"><u>4</u> ' . _('Suppliers') . '</a></td>';
			
			$DefaultManualLink = '<td class="quick_menu_tab" align="center"><a target="_blank" accesskey="8" href="' .  $rootpath . '/doc/Manual/ManualContents.php?' . SID . '"><u>8</u> ' . _('Manual') . '</A></TD>';
						
			if (substr($_SESSION['Language'],0,2) !='en'){
				if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')){
					echo '<td class="quick_menu_tab" align="center"><a target="_blank" accesskey="8" href="' .  $rootpath . '/locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php' . SID . '"><u>8</u> ' . _('Manual') . '</a></td>';		
				} else {
					echo $DefaultManualLink;
				}
			} else {
					echo $DefaultManualLink;
			}
		}

		echo "<td class=\"quick_menu_tab\" align=\"center\"><a accesskey=\"0\" href=\"" . $rootpath . '/Logout.php?' . SID . "\" onclick=\"return confirm('" . _('Are you sure you wish to logout?') . "');\"><u>0</u> "  . _('Logout') . '</a></td>'; 
		
		

		echo '</tr></table>';
		echo '</td></tr></table>';

	}

echo '</td>';
echo '</tr>';
echo '</table>';

?>
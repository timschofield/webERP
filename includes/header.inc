<?php

/* $Revision: 1.14 $ */
	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
	echo '<HTML><HEAD><TITLE>' . $title . '</TITLE>';
	echo '<link REL="shortcut icon" HREF="favicon.ico">';
	echo '<link REL="icon" HREF="favicon.ico">';
	echo '<LINK HREF="css/'. $_SESSION['Theme'] .'/default.css" REL="stylesheet" TYPE="text/css">';
	echo '</HEAD>';

	echo '<BODY>';
	echo '<TABLE CLASS="callout_main" CELLPADDING="0" CELLSPACING="0">';
	echo '<TR>';
	echo '<TD COLSPAN="2" ROWSPAN="2">';

	echo '<TABLE CLASS="main_page" ALIGN="right" CELLPADDING="0" CELLSPACING="0">';
	echo '<TR>';
	echo '<TD>';
	echo '<TABLE WIDTH="100%" BORDER="1" CELLPADDING="0" CELLSPACING="0">';
	echo '<TR>';
	echo '<TD CLASS="quick_menu">';

	if ($title AND substr($title,0,4) != 'Help') {
		echo '<TABLE CELLPADDING="0" CELLSPACING="0">';
		echo '<TR>';
		echo '<TD ALIGN="left" WIDTH="100%" CLASS="quick_menu_left">';
		echo  $CompanyName . ': <A HREF="' . $rootpath . '/UserSettings.php?' . SID . '">' . $_SESSION['UsersRealName'] . '</A>';
		echo '<BR>' . $title . '</TD>';
		echo '<TD ALIGN="right">';
		echo '<IMG SRC="' . $rootpath . '/css/' . $theme .'/images/menucurve.gif" WIDTH="30" HEIGHT="30" alt=""></TD>';
		echo '<TD CLASS="quick_menu_tabs">';
		echo '<TABLE><TR>';
		echo '<TD CLASS="quick_menu_tabs" ALIGN="center"><A ACCESSKEY="1" ALIGN="center" HREF="' . $rootpath . '/index.php?' . SID . '"><U>1</U>. ' . _('Main  Menu') . '</A>&nbsp;&nbsp;</TD>';

		if (count($SecurityGroups[$_SESSION['AccessLevel']])>1){

			echo '<TD CLASS="quick_menu_tabs" ALIGN="center"><A ALIGN="center" ACCESSKEY="2" HREF="' . $rootpath . '/SelectCustomer.php?' . SID . '"><U>2</U>. ' . _('Select Customer') . '</A>&nbsp;&nbsp;</TD>';

			echo '<TD CLASS="quick_menu_tabs" ALIGN="center"><A ALIGN="center" ACCESSKEY="3" HREF="' . $rootpath . '/SelectProduct.php?' . SID . '"><U>3</U>. ' . _('Select Item') . '</A>&nbsp;&nbsp;</TD>';

			echo '<TD CLASS="quick_menu_tabs" ALIGN="center"><A ALIGN="center" ACCESSKEY="4" HREF="' . $rootpath . '/SelectSupplier.php?' . SID . '"><U>4</U>. ' . _('Select Supplier') . '</A>&nbsp;&nbsp;</TD>';

			echo '<TD CLASS="quick_menu_tabs" ALIGN="center"><A TARGET="_blank" ALIGN="center" ACCESSKEY="5" HREF="' . $rootpath . '/doc/Manual/ManualContents.php?' . SID . '"><U>5</U>. ' . _('Manual') . '</A>&nbsp;&nbsp;</TD>';

		}

		echo '<TD CLASS="quick_menu_tabs" ALIGN="center"><A TARGET="_blank" ALIGN="center" ACCESSKEY="9" HREF="' . $rootpath . '/Help.php?' . SID . 'Page=' . substr(strrchr($_SERVER['PHP_SELF'],'/'),1) . '&Title=' . $title . '"><U>9</U>. ' . _('Help') . '</A>&nbsp;&nbsp;</TD>';

		echo '<TD CLASS="quick_menu_tabs" ALIGN="center"><A ALIGN="center" ACCESSKEY="0" HREF="' . $rootpath . '/Logout.php?' . SID . '"><U>0</U>. ' . _('Logout') . '</A></TD>';

		echo '</TD></TR></TABLE>';
		echo '</TD></TR></TABLE>';

	}

echo '</TD>';
echo '</TR>';
echo '</TABLE>';

?>

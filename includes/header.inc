<?php
/* $Revision: 1.12 $ */
	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
	echo "<html> \n<head> \n<title>$title</title> \n";
	echo "<link REL='SHORTCUT ICON' HREF='favicon.ico'>";
	echo "<link href='css/". $_SESSION['Theme'] ."/default.css' rel='stylesheet' type='text/css' /> \n";
	echo "</head> \n";

?>
<body>
<table class="callout_main" cellpadding="0" cellspacing="0">
	<tr>
	<td colspan="2" rowspan="2">

		<table class="main_page" align="right" cellpadding="0" cellspacing="0">
			<tr>
			<td>
				<table width="100%" border="1" cellpadding="0" cellspacing="0">
					<tr>
					<td class="quick_menu">
<?php

	if ($title AND substr($title,0,4)!="Help") {
		echo "<table cellpadding='0' cellspacing='0'><tr>";
			echo "<td align='left' width='100%' class='quick_menu_left'>";
				echo  $CompanyName . ":<A HREF='$rootpath/UserSettings.php?" . SID . "'>" . $_SESSION['UsersRealName'] . "</A>";
				echo "<BR>$title</td>";
				echo "<td align='right'>";
					echo "<img src='$rootpath/css/". $theme ."/images/menucurve.gif' width='30' height='30' alt='' />";
				echo "</td><td class='quick_menu_tabs'>";
				echo "<table><tr> \n";
					echo "<td class='quick_menu_tabs' align='center'><a accesskey='m' align='center' href='$rootpath/index.php?". SID ."'>" . _("<u>M</u>ain  Menu") . "</a>&nbsp;&nbsp;</td> \n";

					if (count($SecurityGroups[$_SESSION['AccessLevel']])>1){

						echo "<td class='quick_menu_tabs' align='center'><a align='center' accesskey='c'  href='$rootpath/SelectCustomer.php?". SID ."'>" . _("Select <u>C</u>ustomer") . "</a>&nbsp;&nbsp;</td>";

						echo "<td class='quick_menu_tabs' align='center'><a accesskey='i' align='center' href='$rootpath/SelectProduct.php?". SID ."'>" . _("Select <u>I</u>tem") . "</a>&nbsp;&nbsp;</td>";

						echo "<td class='quick_menu_tabs' align='center'><a accesskey='s' align='center' href='$rootpath/SelectSupplier.php?". SID ."'>" . _("Select <u>S</u>upplier") . "</a>&nbsp;&nbsp;</td>";
					}

					echo "<td class='quick_menu_tabs' align='center'><a target='_blank' accesskey='h' align='center' href='$rootpath/Help.php?" . SID . "Page=" . substr(strrchr($_SERVER['PHP_SELF'],"/"),1) . "&Title=" . $title . "'>" . _("<u>H</u>elp") . "</a>&nbsp;&nbsp;</td>";

					echo "<td class='quick_menu_tabs' align='center'><a align='center' href='$rootpath/Logout.php?" . SID . "'>" . _("Logout") . "</a></td> \n";
			echo "</td></tr></table> \n";
		echo "</td></tr></table> \n";
	}

?>
	</td>
	</tr>
</table>

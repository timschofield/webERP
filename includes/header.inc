<?php
/* $Revision: 1.4 $ */
	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with

	echo "<html> \n<head> \n<title>$title</title> \n";
	echo "<link href='css/". $theme ."/default.css' rel='stylesheet' type='text/css' /> \n";
	echo "</head> \n";

?>
<table class="callout_main" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="2" rowspan="2">

		<table class="main_page" align="right" cellpadding="0" cellspacing="0">
		    <tr>
				<td>
				<table width="100%" border="1" cellpadding="0" cellspacing="0">
				    <tr>
						<td class="quick_menu">
<?

	if ($title){
		echo "<table cellpadding='0' cellspacing='0'><tr>";
		echo "<td align='left' width='100%' class='quick_menu_selected'>";
		echo $DatabaseName . " : " .$_SESSION['UsersRealName'];
		echo "<BR>$title</td>";
		echo "<td align='right'>";
		echo "<img src='$rootpath/css/". $theme ."/images/menucurve.gif' width='30' height='30' alt='' />";
		echo "</td><td class='quick_menu_tabs'>";
		echo "<table><tr> \n";
		echo "<td class='quick_menu_tabs' align='center'> | <a href='$rootpath/index.php?". SID ."'>Main  Menu</a>&nbsp;</td> \n";

		if (count($SecurityGroups[$_SESSION['AccessLevel']])>1){
			echo "<td class='quick_menu_tabs' align='center'><a href='$rootpath/SelectCustomer.php?". SID ."'>Select Customer</a>&nbsp;</td>";
			echo "<td class='quick_menu_tabs' align='center'><a href='$rootpath/SelectProduct.php?". SID ."'>Select Item</a>&nbsp;</td>";
			echo "<td class='quick_menu_tabs' align='center'><a href='$rootpath/SelectSupplier.php?". SID ."'>Select Supplier</a>&nbsp;</td>";
		}

		if (substr($title,0,4)!="Help"){
			echo "<td class='quick_menu_tabs' align='center'><a target='_blank' href='$rootpath/Help.php?" . SID . "Page=" . substr(strrchr($_SERVER['PHP_SELF'],"/"),1) . "&Title=" . $title . "'>Help</a>&nbsp;</td>";
		}
		echo "<td class='quick_menu_tabs' align='center'><a href='$rootpath/Logout.php?" . SID . "'>Logout</a></td> \n";
		echo "</td></tr></table> \n";
		echo "</td></tr></table> \n";
	}

?>
						</td>
					</tr>
				</table>

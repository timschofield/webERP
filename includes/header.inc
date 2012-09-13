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


    echo '<div id="CanvasDiv">'; 
	echo '<div id="HeaderDiv">'; 
	echo '<div id="HeaderWrapDiv">'; 


	if (isset($title)) {

	
		echo '<div id="AppInfoDiv">'; //===HJ===
			echo '<div id="AppInfoCompanyDiv">';
				echo '<img src="'.$rootpath.'/css/'.$theme.'/images/company.png" title="'._('Company').'" alt="'._('Company').'"/>'.stripslashes($_SESSION['CompanyRecord']['coyname']);
			echo '</div>';
			echo '<div id="AppInfoUserDiv">';
				echo '<a href="'. $rootpath.'/UserSettings.php"><img src="'.$rootpath.'/css/'.$theme.'/images/user.png" title="User" alt="'._('User').'"/>'.stripslashes($_SESSION['UsersRealName']).'</a>';
			echo '</div>';
			echo '<div id="AppInfoModuleDiv">';
				// Make the title text a class, can be set to display:none is some themes
				echo $title;
			echo '</div>';
		echo '</div>'; // AppInfoDiv


		echo '<div id="QuickMenuDiv"><ul>'; 
		
		echo '<li><a href="'.$rootpath.'/index.php">'._('Main Menu').'</a></li>'; //take off inline formatting, use CSS instead ===HJ===

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){
			echo '<li><a href="'.$rootpath.'/SelectCustomer.php">'._('Customers').'</a></li>';
			echo '<li><a href="'.$rootpath.'/SelectProduct.php">' ._('Items')    .'</a></li>';
			echo '<li><a href="'.$rootpath.'/SelectSupplier.php">'._('Suppliers').'</a></li>';
			
			$DefaultManualLink = '<li><a rel="external" accesskey="8" href="' .  $rootpath . '/doc/Manual/ManualContents.php'. $ViewTopic . $BookMark. '">'._('Manual').'</a></li>';

			if (mb_substr($_SESSION['Language'],0,2) != 'en'){
				if (file_exists('locale/'.$_SESSION['Language'].'/Manual/ManualContents.php')){
					echo '<li><a target="_blank" href="'.$rootpath.'/locale/'.$_SESSION['Language'].'/Manual/ManualContents.php'. $ViewTopic . $BookMark. '">'._('Manual').'</a></li>';
				} else {
					echo $DefaultManualLink;
				}
			} else {
					echo $DefaultManualLink;
			}
		}

		echo '<li><a href="'.$rootpath.'/Logout.php" onclick="return confirm(\''._('Are you sure you wish to logout?').'\');">'._('Logout').'</a></li>';

		echo '</ul></div>'; // QuickMenuDiv
	}
	echo '</div>'; // HeaderWrapDiv 
	echo '</div>'; // Headerdiv
	echo '<div id="BodyDiv">'; 
	echo '<div id="BodyWrapDiv">'; 

?>

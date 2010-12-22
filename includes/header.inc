<?php
/* $Id$ */

	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with

	$StrictXHTML=False;
	$ScriptInfo=pathinfo($_SERVER['SCRIPT_NAME']);
	$Script=$ScriptInfo['basename'];
	if (isset($_POST['go'])) {
		header('Location: '.$_POST['favourites']);
	} else if (isset($_POST['gohome_x'])) {
		header('Location: index.php');
	} else if (isset($_POST['AddShortcut_x'])) {
		$sql='INSERT INTO favourites
				VALUES (
					"'.$_SESSION['UserID'].'",
					"'.$title.'",
					"'.$Script.'")';
		$result=DB_query($sql, $db);
	} else if (isset($_POST['RemoveShortcut_x'])) {
		$sql='DELETE FROM favourites
				WHERE userid="'.$_SESSION['UserID'].'"
				AND caption="'.$title.'"
				AND href="'.$Script.'"';
		$result=DB_query($sql, $db);
	} else if (isset($_POST['Logout_x'])) {
		header('Location: Logout.php');
	}
	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}
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
	echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] .'/header.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/MiscFunctions.js"></script>';
	echo '</head>';

	$sql="SELECT caption,
				href
			FROM favourites
			WHERE userid='".$_SESSION['UserID']."'";
	$result=DB_query($sql, $db);
	echo '<body>';
	echo '<form method="post" id="headerForm" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<div id="box">';
	echo '<div id="lb"><div id="rb">';
	echo '<div id="bb"><div id="blc"><div id="brc"><div id="tb"><div id="tlc"><div id="trc">';
	echo '<div id="content">';
	echo '<table style="width:100%; margin-right:5px; font-size: 10px; background-color: transparent;"><tr><td>';
	echo '<img class="image" style="width: 20px; margin-top:2px" src="'.$rootpath.'/css/'.$theme.'/images/company.png" TITLE="' . _('Company') . '" ALT="' . _('Company') . '"></img>';
	echo ' ' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '  <a href="' .  $rootpath . '/UserSettings.php?' . SID .
		'"><img class="image" style="width: 20px; margin-top:2px" src="'.$rootpath.'/css/'.$theme.'/images/user.png" TITLE="User" ALT="' . _('User') . '"> </img>' .
			stripslashes($_SESSION['UsersRealName']) . '</a>';
	echo '</td><td>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table style="float: right; margin-right:5px; font-size: 10px; background-color: transparent;">';
	echo '<tr>';
	echo '<td><input type="image" class="image" name="gohome" src="'.$rootpath.'/css/'.$theme.'/images/home.png" TITLE="' .
		_('Back to Main Menu') . '" ALT="' . _('Back to Main Menu') . '" style="width: 24px;" /></td>';
	echo '<td><select name="favourites" onChange="ReloadForm(go)" style="text-align: center">';
	echo '<option style="text-align: center" value="">------&nbsp;'._('My Shortcuts').'&nbsp;------</option>';
	$IsShortcut=False;
	while ($myrow=DB_fetch_array($result)) {
		echo '<option value="'.$myrow['href'].'" style="text-align: left">'.$myrow['caption'].'</option>';
		if ($myrow['href']==$Script) {
			$IsShortcut=True;
		}
	}
	echo '</select></td>';
	echo '<td><input type="submit" name="go" value="Go" /></td>';
	if ($Script!='index.php') {
		if ($IsShortcut) {
			echo '<td><input type="image" class="image" name="RemoveShortcut" src="'.$rootpath.'/css/'.$theme.'/images/minus.png" TITLE="' . _('Remove from my shortcuts') . '"
				ALT="' . _('Remove from my shortcuts') . '" style="width: 24px;" /></td>';
		} else {
			echo '<td><input type="image" class="image" name="AddShortcut" src="'.$rootpath.'/css/'.$theme.'/images/plus.png" TITLE="' . _('Add to my shortcuts') . '"
				ALT="' . _('Add to my shortcuts') . '" style="width: 24px;" /></td>';
		}
	}

	echo '<td></td>';
	echo '<td><input type="image" class="image" name="Help" src="'.$rootpath.'/css/'.$theme.'/images/help.png" TITLE="' . _('Find help for this function') . '"
				ALT="' . _('Find help for this function') . '" style="width: 24px;" /></td>';
	echo '<td><input type="image" class="image" name="Logout" src="'.$rootpath.'/css/'.$theme.'/images/exit.png" TITLE="' . _('Logout of webERP') . '"
				ALT="' . _('Logout of webERP') . '" style="width: 24px;" /></td>';
	echo '<td><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /></td></tr></table></td></tr></table>';
	echo '</div></div></div></div></div></div></div></div></div></div></form>';

?>
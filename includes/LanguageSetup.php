<?php


/* This file is included in session.inc or PDFStarter.inc or a report script that does not use PDFStarter.inc
to check for the existance of gettext function and setup the necessary enviroment to allow for automatic translation

Set language - defined in config.php or user variable when logging in (session.inc)
NB this language must also exist in the locale on the web-server
normally the lower case two character country code underscore uppercase
2 character country code does the trick  except for en !!*/


// Specify location of translation tables

if (function_exists('bindtextdomain')){
	bindtextdomain ("messages", "./locale");
	// Choose domain
	textdomain ("messages");
}


if (!function_exists('gettext')){
	function _($text){
		return ($text);
	}
} else {
	if ($_SESSION['Language']=='en'){
		setlocale (LC_ALL,'en_GB');
	} else {
		setlocale (LC_ALL, $_SESSION['Language'] . "_" . strtoupper($_SESSION['Language']));
	}
}

?>
<?php
/* This file is included in session.inc or PDFStarter.inc or a report script that does not use PDFStarter.inc
to check for the existance of gettext function and setup the necessary enviroment to allow for automatic translation

Set language - defined in config.php or user variable when logging in (session.inc)
NB this language must also exist in the locale on the web-server
normally the lower case two character country code underscore uppercase
2 character country code does the trick  except for en !!*/

// Specify location of translation tables

if (function_exists('gettext')){
	if ($_SESSION['Language']=='en'){
		$Language_Country = 'en_US';
	} else {
		/*This wont work for fr_BE or where the country code is different to the language code */
		$Language_Country = $_SESSION['Language'] . '_' . strtoupper($_SESSION['Language']);
	}
	setlocale (LC_MESSAGES, $Language_Country);
	setlocale (LC_CTYPE,$Language_Country);
	putenv("LANGUAGE=$Language_Country");
	bindtextdomain ("messages", "./locale");
	textdomain ("messages");
}
?>
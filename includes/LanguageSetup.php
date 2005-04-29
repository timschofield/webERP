<?php
/* This file is included in session.inc or PDFStarter.inc or a report script that does not use PDFStarter.inc
to check for the existance of gettext function and setup the necessary enviroment to allow for automatic translation

Set language - defined in config.php or user variable when logging in (session.inc)
NB this language must also exist in the locale on the web-server
normally the lower case two character country code underscore uppercase
2 character country code does the trick  except for en !!*/

// Specify location of translation tables
If (isset($_POST['Language'])) {
	$_SESSION['Language'] = $_POST['Language'];
	$Language = $_POST['Language'];
} elseif (!isset($_SESSION['Language'])) {
	$_SESSION['Language'] = $DefaultLanguage;
	$Language = $DefaultLanguage;
} else {
	$Language = $_SESSION['Language'];
}
 
if (function_exists('gettext')){
  
  //This maybe reqiured in some stubborn installations
  //	$Locale = setlocale (LC_ALL, $_SESSION['Language']);
	
	$Locale = setlocale (LC_CTYPE, $_SESSION['Language']);
	$Locale = setlocale (LC_MESSAGES, $_SESSION['Language']);
		
	// possibly even if locale fails the language will still switch by using Language instead of locale variable
	putenv('LANG=' . $_SESSION['Language']);
	putenv('LANGUAGE=' . $_SESSION['Language']);
  //putenv('LANG=$Language_Country');
	bindtextdomain ('messages', './locale/');
	textdomain ('messages');
}

?>
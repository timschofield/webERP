<?php
define("VERSIONFILE", "1.10"); 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include ('includes/session.php');
$Title = _('OpenCart to webERP Syncrhonizer '. VERSIONFILE);
include ('includes/header.php');
include('includes/SQL_CommonFunctions.php');

include ('includes/KLGeneralFunctions.php');
include ('includes/KLMarketplaceFunctions.php');
include ('includes/KLDefines.php');
include ('includes/KLEmails.php');
include ('includes/OCOpenCartGeneralFunctions.php');
include ('includes/OCOpenCartToWeberpSync.php');
include ('includes/OCOpenCartConnectDB.php');

OpenCartToWeberpSync(TRUE , '');

include ('includes/footer.php');

?>
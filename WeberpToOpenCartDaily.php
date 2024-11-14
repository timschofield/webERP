<?php
define("VERSIONFILE", "1.05"); 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include ('includes/session.php');
$Title = _('webERP to OpenCart Daily Synchronizer '. VERSIONFILE);
include ('includes/header.php');
include('includes/GetPrice.inc');

include ('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include ('includes/OpenCartGeneralFunctions.php');
include ('includes/KLDefines.php');
include ('includes/OpenCartConnectDB.php');
include ('includes/WeberpToOpenCartSync.php');

WeberpToOpenCartDailySync(TRUE );

include ('includes/footer.php');



?>
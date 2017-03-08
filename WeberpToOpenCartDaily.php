<?php
define("VERSIONFILE", "1.05"); 

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include ('includes/session.inc');
$Title = _('webERP to OpenCart Daily Synchronizer '. VERSIONFILE);
include ('includes/header.inc');
include('includes/GetPrice.inc');

include ('includes/KLGeneralFunctions.php');
include ('includes/WeberpOpenCartDefines.php');
include ('includes/OpenCartGeneralFunctions.php');
include ('includes/WeberpToOpenCartSync.php');
include ('includes/OpenCartConnectDB.php');

WeberpToOpenCartDailySync(TRUE, $db, $db_oc, $oc_tableprefix);

include ('includes/footer.inc');



?>
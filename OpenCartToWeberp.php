<?php
define("VERSIONFILE", "1.10"); 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include ('includes/session.php');
$Title = _('OpenCart to webERP Syncrhonizer '. VERSIONFILE);
include ('includes/header.php');

include ('includes/KLGeneralFunctions.php');
include ('includes/WeberpOpenCartDefines.php');
include ('includes/OpenCartGeneralFunctions.php');
include ('includes/OpenCartToWeberpSync.php');
include ('includes/OpenCartConnectDB.php');

OpenCartToWeberpSync(TRUE, $db, $db_oc, $oc_tableprefix, '');

include ('includes/footer.php');

?>
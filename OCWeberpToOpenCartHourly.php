<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include ('includes/session.php');
$Title = _('webERP to OpenCart Hourly Synchronizer');
include ('includes/header.php');
include('includes/GetPrice.php');

include ('includes/KLGeneralFunctions.php');
include ('includes/KLDefines.php');
include ('includes/OCOpenCartGeneralFunctions.php');
include ('includes/KLMarketplaceFunctions.php');
include ('includes/OCWeberpToOpenCartSync.php');
include ('includes/OCOpenCartConnectDB.php');

WeberpToOpenCartHourlySync(TRUE , TRUE, '');

include ('includes/footer.php');

?>
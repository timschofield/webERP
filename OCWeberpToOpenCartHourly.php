<?php

require(__DIR__ . '/includes/session.php');
$Title = __('webERP to OpenCart Hourly Synchronizer');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/GetPrice.php');

include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/OCOpenCartGeneralFunctions.php');
include(__DIR__ . '/includes/KLMarketplaceFunctions.php');
include(__DIR__ . '/includes/OCWeberpToOpenCartSync.php');
include(__DIR__ . '/includes/OCOpenCartConnectDB.php');

// as the script uses _SESSION variables, reload just in case another user has been changing values in the meantime 
// because the script needs the latest values for the calculations
ReloadSessionVariablesFromConfig();

WeberpToOpenCartHourlySync(true , true, '');

include(__DIR__ . '/includes/footer.php');

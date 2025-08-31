<?php

require(__DIR__ . '/includes/session.php');
$Title = __('webERP to OpenCart Hourly Synchronizer');
include('includes/header.php');
include('includes/GetPrice.php');

include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OCWeberpToOpenCartSync.php');
include('includes/OCOpenCartConnectDB.php');

WeberpToOpenCartHourlySync(true , true, '');

include('includes/footer.php');

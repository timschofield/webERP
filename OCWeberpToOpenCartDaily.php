<?php

include('includes/session.php');
$Title = __('webERP to OpenCart Daily Synchronizer');
include('includes/header.php');
include('includes/GetPrice.php');

include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/OCOpenCartConnectDB.php');
include('includes/OCWeberpToOpenCartSync.php');

WeberpToOpenCartDailySync(TRUE );

include('includes/footer.php');

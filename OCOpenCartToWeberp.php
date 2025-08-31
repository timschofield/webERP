<?php

require(__DIR__ . '/includes/session.php');
$Title = __('OpenCart to webERP Syncrhonizer');
include('includes/header.php');
include('includes/SQL_CommonFunctions.php');

include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/KLDefines.php');
include('includes/KLEmails.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/OCOpenCartToWeberpSync.php');
include('includes/OCOpenCartConnectDB.php');

OpenCartToWeberpSync(true , '');

include('includes/footer.php');

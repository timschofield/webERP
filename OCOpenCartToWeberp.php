<?php

require(__DIR__ . '/includes/session.php');
$Title = __('OpenCart to webERP Syncrhonizer');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/SQL_CommonFunctions.php');

include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLMarketplaceFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLEmails.php');
include(__DIR__ . '/includes/OCOpenCartGeneralFunctions.php');
include(__DIR__ . '/includes/OCOpenCartToWeberpSync.php');
include(__DIR__ . '/includes/OCOpenCartConnectDB.php');

OpenCartToWeberpSync(true , '');

include(__DIR__ . '/includes/footer.php');

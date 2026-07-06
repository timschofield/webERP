<?php

require(__DIR__ . '/includes/session.php');

$Title = __('List of packaging needed to fill up the shops');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLEmails.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

// as the script uses _SESSION variables, reload just in case another user has been changing values in the meantime 
// because the script needs the latest values for the calculations
ReloadSessionVariablesFromConfig();

CheckPackagingToBeRefilled(true, true, $RootPath);

include(__DIR__ . '/includes/footer.php');

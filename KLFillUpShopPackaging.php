<?php

require(__DIR__ . '/includes/session.php');

$Title = __('List of packaging needed to fill up the shops');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLEmails.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

CheckPackagingToBeRefilled(true, true, $RootPath);

include(__DIR__ . '/includes/footer.php');

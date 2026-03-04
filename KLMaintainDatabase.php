<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL DataBase Maintenance');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLPrices.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLEmails.php');

KL_DailyCleanDB(true, '');


include(__DIR__ . '/includes/footer.php');

<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL Update Standard Cost for all suppliers');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.02, "UPDATEALL", $RootPath);
WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_FOREIGN, 0.02, "UPDATEALL", $RootPath);
WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.02, "UPDATEALL", $RootPath);
WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.02, "UPDATEALL", $RootPath);

include(__DIR__ . '/includes/footer.php');


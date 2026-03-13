<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL Update Standard Cost for all suppliers');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

// as the script uses _SESSION variables, reload just in case another user has been changing values in the meantime 
// because the script needs the latest values for the calculations
ReloadSessionVariablesFromConfig();

WrongStandardCost("Indonesia"  , "", $_SESSION['Standard_Cost_Factor_Indonesia'], 0.02, "UPDATEALL", $RootPath);
WrongStandardCost("Thailand"   , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.02, "UPDATEALL", $RootPath);
WrongStandardCost("China"      , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.02, "UPDATEALL", $RootPath);
WrongStandardCost("Hong Kong"  , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.02, "UPDATEALL", $RootPath);

include(__DIR__ . '/includes/footer.php');


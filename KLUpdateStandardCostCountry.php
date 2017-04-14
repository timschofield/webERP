<?php

include ('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
$Title = _('Kapal-Laut. Update Standard Cost for all suppliers of a country');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');

	WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.04, "UPDATEALL", $RootPath, $db);
	WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath, $db);
	WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath, $db);
	WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath, $db);
	WrongStandardCost("Catalonia"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.10, "UPDATEALL", $RootPath, $db);
	WrongStandardCost("Philippines", "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath, $db);

include('includes/footer.php');

?>
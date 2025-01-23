<?php

include ('includes/session.php');
$Title = _('Kapal-Laut. Update Standard Cost for all suppliers of a country');
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include ('includes/KLUIGeneralFunctions.php');

	WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.04, "UPDATEALL", $RootPath);
	WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath);
	WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath);
	WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath);
//	WrongStandardCost("Catalonia"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.10, "UPDATEALL", $RootPath);
//	WrongStandardCost("Philippines", "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath);
//	WrongStandardCost("India"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "UPDATEALL", $RootPath);

include('includes/footer.php');

?>
<?php

include ('includes/session.php');
$Title = _('KL Update Standard Cost for all suppliers');
include('includes/header.php');
include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include ('includes/KLUIGeneralFunctions.php');

	WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.02, "UPDATEALL", $RootPath);
	WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_FOREIGN, 0.02, "UPDATEALL", $RootPath);
	WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.02, "UPDATEALL", $RootPath);
	WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.02, "UPDATEALL", $RootPath);

include('includes/footer.php');


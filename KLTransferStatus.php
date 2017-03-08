<?php

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$Title = _('Kapal-Laut General Transfer Status');
include ('includes/header.inc');
include('includes/KLBoards.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

TransfersDelayed(2, $RootPath, $db);

ActiveTransfersByLocation($RootPath, $db);

ActiveTransferStatus($RootPath, $db);
RecentlyClosedTransferStatus(1, $RootPath, $db);

FinishedStockDistribution("FORSALE", "LOCATION", $db);

prnMsg("Performed 5 transfer status checks",'success');

include ('includes/footer.inc');

/******************************************************************************************************/
/*      FUNCTIONS ASSOCIATED
/******************************************************************************************************/


?>
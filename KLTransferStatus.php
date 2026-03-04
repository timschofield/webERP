<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL General Transfer Status');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

TransfersDelayed(2, $RootPath);

ActiveTransfersByLocation();

ActiveTransferStatus($RootPath);
RecentlyClosedTransferStatus(1, $RootPath);

FinishedStockDistribution("FORSALE", "LOCATION");

prnMsg("Performed 5 transfer status checks",'success');

include(__DIR__ . '/includes/footer.php');

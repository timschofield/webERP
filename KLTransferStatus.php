<?php

include('includes/session.php');

$Title = __('KL General Transfer Status');
include('includes/header.php');

include('includes/KLBoards.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

TransfersDelayed(2, $RootPath);

ActiveTransfersByLocation();

ActiveTransferStatus($RootPath);
RecentlyClosedTransferStatus(1, $RootPath);

FinishedStockDistribution("FORSALE", "LOCATION");

prnMsg("Performed 5 transfer status checks",'success');

include('includes/footer.php');

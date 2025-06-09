<?php

define("NUMBER_OF_TESTS", 7); 

include ('includes/session.php');
$Title = _('KL Reorder Level Adjustments');
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLReorderLevel.php');

$begintime = time_start();

// if testing only and do not want update the DB, set the flag to FALSE. For regular operations set to TRUE.
//$UpdateDB = false;
$UpdateDB = true;
$ShowMessages = true;
$EmailText = '';

KL_DailyRLAdjustmentsForOnline($ShowMessages, $UpdateDB, $RootPath, $EmailText);
KL_DailyRLAdjustmentsForKL($ShowMessages, $UpdateDB, $RootPath, $EmailText);
KL_DailyRLAdjustmentsForBlink($ShowMessages, $UpdateDB, $RootPath, $EmailText);
KL_DailyRLAdjustmentsForOutlet($ShowMessages, $UpdateDB, $RootPath, $EmailText);
KL_DailyRLRebalancing($ShowMessages, $UpdateDB, $RootPath, $EmailText);
KL_DailyRLZeroNotAvailable($ShowMessages, $UpdateDB, $RootPath, $EmailText);
KL_DailyRLAdjustmentsForPackaging($ShowMessages, $UpdateDB, $RootPath, $EmailText);

prnMsg("Performed ". NUMBER_OF_TESTS . " RL adjustement strategies",'success');
time_finish($begintime);
include ('includes/footer.php');

?>
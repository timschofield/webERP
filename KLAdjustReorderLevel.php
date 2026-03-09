<?php

define("NUMBER_OF_TESTS", 7); 

require(__DIR__ . '/includes/session.php');

$Title = __('KL Reorder Level Adjustments');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');
include(__DIR__ . '/includes/KLReorderLevel.php');

$begintime = time_start();

// if testing only and do not want update the DB, set the flag to false. For regular operations set to true.
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
KL_DailyRLAdjustmentsForPackaging($ShowMessages, $UpdateDB, $EmailText);

prnMsg("Performed ". NUMBER_OF_TESTS . " RL adjustement strategies",'success');
time_finish($begintime);
include(__DIR__ . '/includes/footer.php');

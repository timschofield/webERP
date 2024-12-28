<?php

define("VERSIONFILE", "3.02"); 
define("NUMBER_OF_TESTS", 20); 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Kapal-Laut Reorder Level Adjustments '. VERSIONFILE);
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include('includes/KLReorderLevel.php');

$begintime = time_start();
LocationInformationReview($RootPath);

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
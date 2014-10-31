<?php

define("VERSIONFILE", "3.02"); 
define("NUMBER_OF_TESTS", 20); 

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$Title = _('Kapal-Laut Reorder Level Adjustments '. VERSIONFILE);
include ('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include('includes/KLReorderLevel.php');

$begintime = time_start();
ListPriorityLocations($db);

// if testing only and do not want update the DB, set the flag to FALSE. For regular operations set to TRUE.
//$updateDB = false;
$updateDB = true;
$ShowMessages = true;
$EmailText = '';

DailyReorderLevelAdjustments($ShowMessages, $updateDB, $RootPath, $db, $EmailText);

prnMsg("Performed ". NUMBER_OF_TESTS . " RL adjustement strategies",'success');
time_finish($begintime);
include ('includes/footer.inc');

?>
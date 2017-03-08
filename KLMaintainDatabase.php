<?php

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$Title = _('Kapal-Laut DataBase Maintenance');
include ('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLPrices.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');
include('KLDailyChecks.php');

KL_DailyMaintenanceDatabase(TRUE, $db);

include ('includes/footer.inc');
?>
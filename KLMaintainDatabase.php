<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Kapal-Laut DataBase Maintenance');
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLPrices.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');
include('KLDailyChecks.php');

KL_DailyCleanDB(TRUE);


include ('includes/footer.php');
?>
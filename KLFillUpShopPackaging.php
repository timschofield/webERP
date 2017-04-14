<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('List of packaging needed to fill up the KL shops');
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');

KapalLautPackagingToBeRefilled(true, $RootPath, $db);
BlinkPackagingToBeRefilled(true, $RootPath, $db);
OutletPackagingToBeRefilled(true, $RootPath, $db);

include ('includes/footer.php');
?>
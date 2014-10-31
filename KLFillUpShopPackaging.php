<?php

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$Title = _('List of packaging needed to fill up the KL shops');
include ('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');

PackagingToBeRefilled(true, $RootPath, $db);

include ('includes/footer.inc');
?>
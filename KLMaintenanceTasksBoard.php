<?php
define("VERSIONFILE", "1.00"); // 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('KL Maintenance Tasks Board '. VERSIONFILE);
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLBoards.php');

MaintenanceTasksList("CLOSED", 60);

include ('includes/footer.php');

?>
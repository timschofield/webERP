<?php
define("VERSIONFILE", "1.00"); // 

include ('includes/session.php');
$Title = _('KL Maintenance Tasks Board '. VERSIONFILE);
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLBoards.php');
include('includes/KLUIGeneralFunctions.php');

MaintenanceTasksDistribution("OPEN", 0);
MaintenanceTasksDistribution("CLOSED", 30);
MaintenanceTasksDistribution("TOTAL", 30);
MaintenanceTasksList("CLOSED", 60);

include ('includes/footer.php');

?>
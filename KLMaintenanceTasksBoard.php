<?php

include('includes/session.php');
$Title = __('KL Maintenance Tasks Board');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLBoards.php');
include('includes/KLUIGeneralFunctions.php');

MaintenanceTasksDistribution("OPEN", 0, false);
MaintenanceTasksDistribution("CLOSED", 30, false);
MaintenanceTasksDistribution("TOTAL", 30, false);
MaintenanceTasksList("CLOSED", 60);

include('includes/footer.php');

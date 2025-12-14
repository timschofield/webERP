<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL Maintenance Tasks Board');
include('includes/header.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLBoards.php');
include('includes/KLUIGeneralFunctions.php');

MaintenanceTasksDistribution("OPEN", 0, false, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
MaintenanceTasksDistribution("CLOSED", 30, false, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
MaintenanceTasksDistribution("TOTAL", 30, false, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
MaintenanceTasksList("CLOSED", 60, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));

include('includes/footer.php');

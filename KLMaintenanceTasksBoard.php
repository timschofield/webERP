<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL Maintenance Tasks Board');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

MaintenanceTasksDistribution("OPEN", 0, false, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
MaintenanceTasksDistribution("CLOSED", 30, false, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
MaintenanceTasksDistribution("TOTAL", 30, false, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
MaintenanceTasksList("CLOSED", 60, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));

include(__DIR__ . '/includes/footer.php');

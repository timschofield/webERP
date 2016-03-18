<?php

/* Assign $KL_Role variables depending on AccessLevel, for ease of coding */

$KL_SystemAdmin = FALSE;
$KL_OperationalManager = FALSE;
$KL_AdministrationTeam = FALSE;
$KL_BusinessDevelopmentManager = FALSE;
$KL_PurchasingTeam = FALSE;
$KL_ShopSupportTeam = FALSE;
$KL_ShopSupportLeader = FALSE;
$KL_SalesDirector = FALSE;
$KL_ShopManager = FALSE;
$KL_SPG = FALSE;
$KL_SPGSupport = FALSE;
$KL_PettyCash = FALSE;

if ($_SESSION['AccessLevel'] == 8){ // webERP System Administration
	$KL_SystemAdmin = TRUE;
}else if ($_SESSION['AccessLevel'] == 9){ // Shop Support Team
	$KL_ShopSupportTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 26){ // Shop Support Team + Online. 
	$KL_ShopSupportTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 23){ // Shop Support Leader
	$KL_ShopSupportTeam = TRUE;
	$KL_ShopSupportLeader = TRUE;
}else if ($_SESSION['AccessLevel'] == 14){ // Operational Manager
	$KL_OperationalManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 15){ // PettyCash Only User (courier, IT, etc...)
	$KL_PettyCash = TRUE;
}else if ($_SESSION['AccessLevel'] == 16){ // Sales Director
	$KL_SalesDirector = TRUE;
}else if ($_SESSION['AccessLevel'] == 31){ // Shop Manager
	$KL_ShopManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 17){ // Sales Team SPG
	$KL_SPG = TRUE;
}else if ($_SESSION['AccessLevel'] == 22){ // Sales Team SPG Support
	$KL_SPGSupport = TRUE;
}else if ($_SESSION['AccessLevel'] == 19){ // Administration Leader
	$KL_AdministrationTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 27){ // Administration Team 
	$KL_AdministrationTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 21){ // Business Development manager (L)
	$KL_BusinessDevelopmentManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 24){ // Purchasing Leader
	$KL_PurchasingTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 28){ // Purchasing Team
	$KL_PurchasingTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 25){ // Marketing Manager PakRicard
	$KL_OperationalManager = TRUE;
	$KL_SalesDirector = TRUE;
	$KL_AdministrationTeam = TRUE;
	$KL_BusinessDevelopmentManager = TRUE;
	$KL_ShopSupportLeader = TRUE;
}else if ($_SESSION['AccessLevel'] == 29){ // IT Hostgator Support (as much rights as possible so they run long scripts
	$KL_SystemAdmin = TRUE;
	$KL_OperationalManager = TRUE;
	$KL_PurchasingTeam = TRUE;
	$KL_SalesDirector = TRUE;
	$KL_AdministrationTeam = TRUE;
	$KL_BusinessDevelopmentManager = TRUE;
	$KL_ShopSupportLeader = TRUE;
}


?>
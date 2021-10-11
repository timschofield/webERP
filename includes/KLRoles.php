<?php

/* Assign $KL_Role variables depending on AccessLevel, for ease of coding */

$KL_SystemAdmin = FALSE;
$KL_OperationalManager = FALSE;
$KL_AdministrationTeam = FALSE;
$KL_BusinessDevelopmentManager = FALSE;
$KL_PurchasingTeam = FALSE;
$KL_ShopSupportTeam = FALSE;
$KL_ShopSupportLeader = FALSE;
$KL_OnlineSales = FALSE;
$KL_ShopManager = FALSE;
$KL_SPGSeniorOrSupport = FALSE;
$KL_SPGJunior = FALSE;
$KL_PettyCash = FALSE;
$KL_ITSupport = FALSE;


if ($_SESSION['AccessLevel'] == 8){ // webERP System Administration (R)
	$KL_SystemAdmin = TRUE;
}else if ($_SESSION['AccessLevel'] == 14){ // Operational Manager (Ike)
	$KL_OperationalManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 26){ // Shop Support Team
	$KL_ShopSupportTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 23){ // Shop Support Leader (ria)
	$KL_ShopSupportTeam = TRUE;
	$KL_ShopSupportLeader = TRUE;
}else if ($_SESSION['AccessLevel'] == 15){ // PettyCash Only User (courier, etc...)
	$KL_PettyCash = TRUE;
}else if ($_SESSION['AccessLevel'] == 34){ // Sales DIrector (a.k.a Fathus)
	$KL_ShopManager = TRUE;
	$KL_SalesTeamOnline = TRUE;
}else if ($_SESSION['AccessLevel'] == 17){ // Sales Team SPG
	$KL_SPGSeniorOrSupport = TRUE;
}else if ($_SESSION['AccessLevel'] == 22){ // Sales Team SPG Support
	$KL_SPGJunior = TRUE;
}else if ($_SESSION['AccessLevel'] == 19){ // Administration Leader (Revi)
	$KL_AdministrationTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 27){ // Administration Team 
	$KL_AdministrationTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 21){ // Business Development manager (L)
	$KL_BusinessDevelopmentManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 24){ // Purchasing Leader (Cicik). Added as Shop Support Team too, as she has 2 roles, from COVID
	$KL_PurchasingTeam = TRUE;
	$KL_ShopSupportTeam = TRUE;             
}else if ($_SESSION['AccessLevel'] == 28){ // Purchasing Team
	$KL_PurchasingTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 29){ // IT Hostgator Support (as much rights as possible so they run long scripts
	$KL_SystemAdmin = TRUE;
}else if ($_SESSION['AccessLevel'] == 31){ // Shop Manager
	$KL_ShopManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 32){ // It Support
	$KL_ITSupport = TRUE;
}else if ($_SESSION['AccessLevel'] == 36){ // Sales Team Online (Ceria)
	$KL_SalesTeamOnline = TRUE;
}

?>
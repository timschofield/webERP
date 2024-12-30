<?php

/* Assign $KL_Role variables depending on AccessLevel, for ease of coding */

$KL_SystemAdmin = FALSE;
$KL_OperationalManager = FALSE;
$KL_OperationalLeader = FALSE;
$KL_OperationalTeam = FALSE;
$KL_AdministrationLeader = FALSE;
$KL_AdministrationTeam = FALSE;
$KL_BusinessDevelopmentManager = FALSE;
$KL_PurchasingTeam = FALSE;
$KL_ShopSupportTeam = FALSE;
$KL_ShopSupportLeader = FALSE;
$KL_OnlineSales = FALSE;
$KL_ShopManager = FALSE;
$KL_SalesDirector = FALSE;
$KL_SPGSeniorOrSupport = FALSE;
$KL_SPGJunior = FALSE;
$KL_PettyCash = FALSE;
$KL_ITSupport = FALSE;

if (isset($_SESSION['AccessLevel'])){
	if ($_SESSION['AccessLevel'] == 8){ // webERP System Administration (R)
		$KL_SystemAdmin = TRUE;
	}else if ($_SESSION['AccessLevel'] == 14){ // Operational Manager (Ike)
		$KL_OperationalManager = TRUE;
	}else if ($_SESSION['AccessLevel'] == 26){ // Shop Support Team
		$KL_ShopSupportTeam = TRUE;
	}else if ($_SESSION['AccessLevel'] == 23){ // Shop Support Leader (Ria)
		$KL_ShopSupportTeam = TRUE;
		$KL_ShopSupportLeader = TRUE;
	}else if ($_SESSION['AccessLevel'] == 15){ // PettyCash Only User (courier, etc...)
		$KL_PettyCash = TRUE;
	}else if ($_SESSION['AccessLevel'] == 34){ // Sales DIrector (a.k.a Fathus)
		$KL_SalesDirector = TRUE;
	}else if ($_SESSION['AccessLevel'] == 17){ // Sales Team SPG
		$KL_SPGSeniorOrSupport = TRUE;
	}else if ($_SESSION['AccessLevel'] == 22){ // Sales Team SPG Support
		$KL_SPGJunior = TRUE;
	}else if ($_SESSION['AccessLevel'] == 19){ // Administration Leader (Revi)
		$KL_AdministrationLeader = TRUE;
		$KL_AdministrationTeam = TRUE;
	}else if ($_SESSION['AccessLevel'] == 27){ // Administration Team 
		$KL_AdministrationTeam = TRUE;
	}else if ($_SESSION['AccessLevel'] == 21){ // Business Development manager (Laia)
		$KL_BusinessDevelopmentManager = TRUE;
	}else if ($_SESSION['AccessLevel'] == 24){ // Purchasing Leader (Cicik)
		$KL_PurchasingTeam = TRUE;
	}else if ($_SESSION['AccessLevel'] == 28){ // Purchasing Team
		$KL_PurchasingTeam = TRUE;
	}else if ($_SESSION['AccessLevel'] == 29){ // IT Hostgator Support (as much rights as possible so they run long scripts
		$KL_SystemAdmin = TRUE;
	}else if ($_SESSION['AccessLevel'] == 31){ // Shop Manager (Komang, Reza)
		$KL_ShopManager = TRUE;
	}else if ($_SESSION['AccessLevel'] == 32){ // It Support
		$KL_ITSupport = TRUE;
	}else if ($_SESSION['AccessLevel'] == 36){ // Sales Team Online
		$KL_SalesTeamOnline = TRUE;
	}else if ($_SESSION['AccessLevel'] == 37){ // Operational Leader (Novik)
		$KL_OperationalLeader = TRUE;
		$KL_OperationalTeam = TRUE;
	}
}

?>
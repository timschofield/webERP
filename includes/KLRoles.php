<?php

/* Assign $KL_Role variables depending on AccessLevel, for ease of coding */

$KL_SystemAdmin = false;
$KL_OperationalManager = false;
$KL_OperationalLeader = false;
$KL_OperationalTeam = false;
$KL_AdministrationLeader = false;
$KL_AdministrationTeam = false;
$KL_BusinessDevelopmentManager = false;
$KL_PurchasingTeam = false;
$KL_ShopSupportTeam = false;
$KL_ShopSupportLeader = false;
$KL_CustomerService = false;
$KL_ShopManager = false;
$KL_SalesDirector = false;
$KL_SPGSeniorOrSupport = false;
$KL_SPGJunior = false;
$KL_PettyCash = false;
$KL_ITSupport = false;
$KL_MarketingManager = false;

if (isset($_SESSION['AccessLevel'])){
	if ($_SESSION['AccessLevel'] == 8){ // webERP System Administration (R)
		$KL_SystemAdmin = true;
	}else if ($_SESSION['AccessLevel'] == 14){ // Operational Manager (Ike)
		$KL_OperationalManager = true;
	}else if ($_SESSION['AccessLevel'] == 15){ // PettyCash Only User (courier, etc...)
		$KL_PettyCash = true;
	}else if ($_SESSION['AccessLevel'] == 17){ // Sales Team SPG
		$KL_SPGSeniorOrSupport = true;
	}else if ($_SESSION['AccessLevel'] == 19){ // Administration Leader (Revi)
		$KL_AdministrationLeader = true;
		$KL_AdministrationTeam = true;
	}else if ($_SESSION['AccessLevel'] == 21){ // Business Development manager (Laia)
		$KL_BusinessDevelopmentManager = true;
	}else if ($_SESSION['AccessLevel'] == 22){ // Sales Team SPG Support
		$KL_SPGJunior = true;
	}else if ($_SESSION['AccessLevel'] == 23){ // Shop Support Leader (Ria)
		$KL_ShopSupportTeam = true;
		$KL_ShopSupportLeader = true;
	}else if ($_SESSION['AccessLevel'] == 24){ // Purchasing Leader (Cicik)
		$KL_PurchasingTeam = true;
	}else if ($_SESSION['AccessLevel'] == 26){ // Shop Support Team
		$KL_ShopSupportTeam = true;
	}else if ($_SESSION['AccessLevel'] == 27){ // Administration Team 
		$KL_AdministrationTeam = true;
	}else if ($_SESSION['AccessLevel'] == 28){ // Purchasing Team
		$KL_PurchasingTeam = true;
	}else if ($_SESSION['AccessLevel'] == 29){ // IT Hosting Support
		$KL_SystemAdmin = true;
	}else if ($_SESSION['AccessLevel'] == 31){ // Shop Manager (a.k.a. Komang)
		$KL_ShopManager = true;
	}else if ($_SESSION['AccessLevel'] == 32){ // It Support
		$KL_ITSupport = true;
	}else if ($_SESSION['AccessLevel'] == 34){ // Sales DIrector (a.k.a Fathus)
		$KL_SalesDirector = true;
	}else if ($_SESSION['AccessLevel'] == 36){ // Customer Service (a.k.a. Nia)
		$KL_CustomerService = true;
	}else if ($_SESSION['AccessLevel'] == 37){ // Operational Leader (Novik)
		$KL_OperationalLeader = true;
		$KL_OperationalTeam = true;
	}else if ($_SESSION['AccessLevel'] == 38){ // Marketing Manager (ex Aisyah)
		$KL_MarketingManager = true;
	}
}

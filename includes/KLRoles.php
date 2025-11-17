<?php

/* Assign $KL_Role variables depending on AccessLevel, for ease of coding */

$KL_SystemAdmin = false;
$KL_Partner = false;
$KL_GeneralAffairsManager = false;
$KL_OperationalLeader = false;
$KL_OperationalTeam = false;
$KL_AdministrationLeader = false;
$KL_AdministrationTeam = false;
$KL_PurchasingManager = false;
$KL_PurchasingTeam = false;
$KL_ShopSupportTeam = false;
$KL_ShopSupportLeader = false;
$KL_CustomerService = false;
$KL_ShopManager = false;
$KL_SalesTeamManager = false;
$KL_SPGSeniorOrSupport = false;
$KL_SPGJunior = false;
$KL_PettyCash = false;
$KL_ITSupport = false;
$KL_HRDManager = false;
$KL_MarketingManager = false;

if (isset($_SESSION['AccessLevel'])){
	if ($_SESSION['AccessLevel'] == 8){ // PTADU partner and webERP System Administration (Ricard)
		$KL_SystemAdmin = true;
		$KL_Partner = true;
	} elseif ($_SESSION['AccessLevel'] == 14){ // General Affairs Manager (Ike)
		$KL_GeneralAffairsManager = true;
	} elseif ($_SESSION['AccessLevel'] == 15){ // PettyCash Only User (courier, etc...)
		$KL_PettyCash = true;
	} elseif ($_SESSION['AccessLevel'] == 17){ // Sales Team SPG
		$KL_SPGSeniorOrSupport = true;
	} elseif ($_SESSION['AccessLevel'] == 19){ // Administration Leader (Revi)
		$KL_AdministrationLeader = true;
		$KL_AdministrationTeam = true;
	} elseif ($_SESSION['AccessLevel'] == 21){ // PTADU partner and Purchasing Manager (Laia)
		$KL_PurchasingManager = true;
		$KL_SalesTeamManager = true;
		$KL_Partner = true;
	} elseif ($_SESSION['AccessLevel'] == 22){ // Sales Team SPG Support
		$KL_SPGJunior = true;
	} elseif ($_SESSION['AccessLevel'] == 23){ // Shop Support Leader (Ria)
		$KL_ShopSupportTeam = true;
		$KL_ShopSupportLeader = true;
	} elseif ($_SESSION['AccessLevel'] == 24){ // Purchasing Leader (Cicik)
		$KL_PurchasingTeam = true;
	} elseif ($_SESSION['AccessLevel'] == 26){ // Shop Support Team
		$KL_ShopSupportTeam = true;
	} elseif ($_SESSION['AccessLevel'] == 27){ // Administration Team 
		$KL_AdministrationTeam = true;
	} elseif ($_SESSION['AccessLevel'] == 28){ // Purchasing Team
		$KL_PurchasingTeam = true;
	} elseif ($_SESSION['AccessLevel'] == 29){ // IT Hosting Support
		$KL_SystemAdmin = true;
	} elseif ($_SESSION['AccessLevel'] == 31){ // Shop Manager (a.k.a. Komang)
		$KL_ShopManager = true;
	} elseif ($_SESSION['AccessLevel'] == 32){ // It Support
		$KL_ITSupport = true;
	} elseif ($_SESSION['AccessLevel'] == 34){ // Sales Team Manager (a.k.a Fathus)
		$KL_PurchasingManager = true;
		$KL_SalesTeamManager = true;
	} elseif ($_SESSION['AccessLevel'] == 36){ // Customer Service (a.k.a. Nia)
		$KL_CustomerService = true;
	} elseif ($_SESSION['AccessLevel'] == 37){ // Operational Leader (Novik)
		$KL_OperationalLeader = true;
		$KL_OperationalTeam = true;
	} elseif ($_SESSION['AccessLevel'] == 38){ // HRD Manager (Taufik)
		$KL_HRDManager = true;
	} elseif ($_SESSION['AccessLevel'] == 39){ // Marketing Manager (---)
		$KL_MarketingManager = true;
	}
}

<?php

/* Assign $KL_Role variables depending on AccessLevel, for ease of coding */

$KL_SystemAdmin = FALSE;
$KL_KantorManager = FALSE;
$KL_KantorAdministration = FALSE;
$KL_PurchasingManager = FALSE;
$KL_PurchasingTeam = FALSE;
$KL_ShopSupportTeam = FALSE;
$KL_ShopSupportManager = FALSE;
$KL_SalesManager = FALSE;
$KL_PettyCash = FALSE;
$KL_SPG = FALSE;
$KL_SPGSupport = FALSE;

if ($_SESSION['AccessLevel'] == 8){
	$KL_SystemAdmin = TRUE;
}else if ($_SESSION['AccessLevel'] == 9){
	$KL_ShopSupportTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 14){
	$KL_KantorManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 15){
	$KL_PettyCash = TRUE;
}else if ($_SESSION['AccessLevel'] == 16){
	$KL_SalesManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 17){
	$KL_SPG = TRUE;
}else if ($_SESSION['AccessLevel'] == 19){
	$KL_KantorAdministration = TRUE;
}else if ($_SESSION['AccessLevel'] == 21){
	$KL_PurchasingManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 22){
	$KL_SPGSupport = TRUE;
}else if ($_SESSION['AccessLevel'] == 23){
	$KL_ShopSupportTeam = TRUE;
	$KL_ShopSupportManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 24){
	$KL_PurchasingTeam = TRUE;
}else if ($_SESSION['AccessLevel'] == 25){ // PakRicard
	$KL_KantorManager = TRUE;
	$KL_SalesManager = TRUE;
	$KL_KantorAdministration = TRUE;
	$KL_PurchasingManager = TRUE;
	$KL_ShopSupportManager = TRUE;
}else if ($_SESSION['AccessLevel'] == 26){ // Shop Support + Online
	$KL_ShopSupportTeam = TRUE;
}

?>
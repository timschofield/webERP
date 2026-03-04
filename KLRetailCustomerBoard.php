<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL Retail Customer Analysis');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLCountriesForRetail.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLRetailCustomer.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin){
	RetailCustomerDataQualitySPG("ALL", 7);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_GeneralAffairsManager 
	OR $KL_SalesTeamManager
	OR $KL_ShopManager){
	RetailCustomerDataQualitySPG("ALL", 30);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_GeneralAffairsManager 
	OR $KL_SalesTeamManager
	OR $KL_ShopManager){
	RetailCustomerAnalysisBySex(365, "ALL");
	$NumberOfTestExecuted++;
}
	
if ($KL_SystemAdmin){
	RetailCustomerAnalysisByCountry(365, "ALL", "ALL", 500, $CountriesForRetail);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_GeneralAffairsManager 
	OR $KL_SalesTeamManager
	OR $KL_ShopManager){

	RetailCustomerAnalysisByCountry(90, "ALL", "ALL", 30, $CountriesForRetail);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "KAPAL-LAUT", "ALL", 20, $CountriesForRetail);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "BLINK", "ALL", 20, $CountriesForRetail);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "OUTLET", "ALL", 10, $CountriesForRetail);
	$NumberOfTestExecuted++;
	
	RetailCustomerAnalysisByCountry(90, "ALL", "CANGGU", 10, $CountriesForRetail);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "KUTA", 10, $CountriesForRetail);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "OBEROI", 10, $CountriesForRetail);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "SEMINYAK", 10, $CountriesForRetail);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "SANUR", 10, $CountriesForRetail);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "UBUD", 10, $CountriesForRetail);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_GeneralAffairsManager 
	OR $KL_SalesTeamManager
	OR $KL_ShopManager){

	RetailCustomerAnalysisByAge(90, "ALL");
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(365, "ALL");
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(90, "KAPAL-LAUT");
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(365, "KAPAL-LAUT");
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(90, "BLINK");
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(365, "BLINK");
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(90, "OUTLET");
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(365, "OUTLET");
	$NumberOfTestExecuted++;

	EmailHarvested(30, "ALL");
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin){
	EmailHarvested(365, "ALL");
	$NumberOfTestExecuted++;
}

prnMsg("Performed ". $NumberOfTestExecuted . " Retail Customers Analysis",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include(__DIR__ . '/includes/footer.php');

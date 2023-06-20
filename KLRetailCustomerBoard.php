<?php
define("VERSIONFILE", "1.12");
define("NUMBER_OF_TESTS", 28); 

include ('includes/session.php');
$Title = _('Kapal-Laut Retail Customer Analysis '. VERSIONFILE);
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLCountriesForRetail.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLRetailCustomer.php');

/* ASSIGN users to groups */
include ('includes/KLRoles.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin){
	RetailCustomerDataQualitySPG("ALL", 7, $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager){
	RetailCustomerDataQualitySPG("ALL", 30, $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager){
	RetailCustomerAnalysisBySex(365, "ALL", $db);
	$NumberOfTestExecuted++;
}
	
if ($KL_SystemAdmin){
	RetailCustomerAnalysisByCountry(365, "ALL", "ALL", 500, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager){

	RetailCustomerAnalysisByCountry(90, "ALL", "ALL", 30, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "KAPAL-LAUT", "ALL", 20, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "BLINK", "ALL", 20, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "OUTLET", "ALL", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	
	RetailCustomerAnalysisByCountry(90, "ALL", "CANGGU", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "KUTA", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "OBEROI", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "SEMINYAK", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "SANUR", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(90, "ALL", "UBUD", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager){

	RetailCustomerAnalysisByAge(90, "ALL", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(365, "ALL", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(90, "KAPAL-LAUT", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(365, "KAPAL-LAUT", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(90, "BLINK", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(365, "BLINK", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(90, "OUTLET", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(365, "OUTLET", $db);
	$NumberOfTestExecuted++;

	EmailHarvested(30, "ALL", $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin){
	EmailHarvested(365, "ALL", $db);
	$NumberOfTestExecuted++;
}

prnMsg("Performed ". $NumberOfTestExecuted . " Retail Customers Analysis",'success');
time_finish($begintime);

include ('includes/footer.php');

?>
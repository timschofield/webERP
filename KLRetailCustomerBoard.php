<?php
define("VERSIONFILE", "1.12");
define("NUMBER_OF_TESTS", 28); 

include ('includes/session.inc');
$Title = _('Kapal-Laut Retail Customer Analysis '. VERSIONFILE);
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLCountriesForRetail.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLRetailCustomer.php');

/* ASSIGN users to groups */
include ('includes/KLRoles.inc');

$begintime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin){
	RetailCustomerDataQualitySPG("ALL", 7, $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector){
	RetailCustomerDataQualitySPG("ALL", 30, $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector){
	RetailCustomerAnalysisBySex(90, "ALL", $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin){
	RetailCustomerAnalysisByCountry(365, "ALL", 50, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector){

	RetailCustomerAnalysisByCountry(90, "ALL", 20, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(60, "ALL", 20, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(30, "ALL", 20, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(30, "KAPAL-LAUT", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(30, "BLINK", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(30, "OUTLET", 10, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector){

	RetailCustomerAnalysisByAge(60, "ALL", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(90, "ALL", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(60, "ALL", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(30, "ALL", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(30, "KAPAL-LAUT", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(30, "BLINK", $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByAge(30, "OUTLET", $db);
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

include ('includes/footer.inc');

?>
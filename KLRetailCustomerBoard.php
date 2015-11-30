<?php
define("VERSIONFILE", "1.11"); 
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

if ($KL_SystemAdmin){
	RetailCustomerDataQualitySPG("ALL", 7, $db);
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesManager){
	RetailCustomerDataQualitySPG("ALL", 30, $db);
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesManager){

	RetailCustomerAnalysisBySex(90, "ALL", $db);
}

if ($KL_SystemAdmin){
	RetailCustomerAnalysisByCountry(365, "ALL", 50, $CountriesForRetail, $db);

//	RetailCustomerAnalysisByCountry(7, "ALL", 3, $CountriesForRetail, $db);
//	RetailCustomerAnalysisByCountry(7, "'TOK66','TOKSE','TOKOB'", 3, $CountriesForRetail, $db);
//	RetailCustomerAnalysisByCountry(7, "'TOKKS','TOKBW'", 3, $CountriesForRetail, $db);
//	RetailCustomerAnalysisByCountry(7, "'TOKJC'", 3, $CountriesForRetail, $db);
//	RetailCustomerAnalysisByCountry(7, "'TOKSA','TOKSS','TOKSU'", 3, $CountriesForRetail, $db);
//	RetailCustomerAnalysisByCountry(7, "'TOKUB','TOKPU','TOKMF'", 3, $CountriesForRetail, $db);
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesManager){

	RetailCustomerAnalysisByCountry(90, "ALL", 20, $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(60, "ALL", 20, $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "ALL", 20, $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(90, "'TOK66','TOKSE','TOKOB','TOKKA'", 10, $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(90, "'TOKKS','TOKBW','TOKPA'", 10, $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(90, "'TOKJC'", 10, $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(90, "'TOKSA','TOKSS','TOKSU'", 10, $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(90, "'TOKUB','TOKPU','TOKMF'", 10, $CountriesForRetail, $db);
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesManager){

	RetailCustomerAnalysisByAge(60, "ALL", $db);
	RetailCustomerAnalysisByAge(60, "'TOK66','TOKSE','TOKOB','TOKKA'", $db);
	RetailCustomerAnalysisByAge(60, "'TOKKS','TOKBW','TOKPA'", $db);
	RetailCustomerAnalysisByAge(60, "'TOKJC'", $db);
	RetailCustomerAnalysisByAge(60, "'TOKSA','TOKSS','TOKSU'", $db);
	RetailCustomerAnalysisByAge(60, "'TOKUB','TOKPU','TOKMF'", $db);

	EmailHarvested(30, "ALL", $db);
}

if ($KL_SystemAdmin){
	EmailHarvested(365, "ALL", $db);

//	EmailHarvested(1, "ALL", $db);
//	EmailHarvested(1, "'TOK66','TOKSE','TOKOB'", $db);
//	EmailHarvested(1, "'TOKKS','TOKBW'", $db);
//	EmailHarvested(1, "'TOKJC'", $db);
//	EmailHarvested(1, "'TOKSA','TOKSS','TOKSU'", $db);
//	EmailHarvested(1, "'TOKUB','TOKPU','TOKMF'", $db);
}

prnMsg("Performed ". NUMBER_OF_TESTS . " Retail Customers Analysis",'success');
time_finish($begintime);

include ('includes/footer.inc');

?>
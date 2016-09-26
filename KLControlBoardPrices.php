<?php
define("VERSIONFILE", "4.00");
define("NUMBER_OF_TESTS", 73); 

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include ('includes/session.inc');
$Title = _('Kapal-Laut General Control Board for Pricing '. VERSIONFILE);
include ('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');

/* Do the pending GL Postings to get the latest finantial control reports*/
include ('includes/GLPostings.inc');

/* ASSIGN users to groups */
include ('includes/KLRoles.inc');
$begintime = time_start();

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

/***************************************************************************************
* RETAIL PRICE         
***************************************************************************************/

if ($KL_SystemAdmin 
	OR $KL_BusinessDevelopmentManager){
	
//	PricesNotUpdatedinXDays(365*2, 15, $RootPath, $db);
//	PricesNotUpdatedinXDays(365  , 10, $RootPath, $db);

	ItemsWithoutRetailPrice("SETKL", 4.75, $RootPath, $db);
	ItemsWithoutRetailPrice("SETBL", 4.75, $RootPath, $db);
	ItemsWithoutRetailPrice("SETGE", 4.75, $RootPath, $db);

	ItemsWithoutRetailPrice("TESTKL", 4.75, $RootPath, $db);
	ItemsWithoutRetailPrice("STABKL", 4.75, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOKL", 4.75, $RootPath, $db);

	ItemsWithoutRetailPrice("TESTBL", 5.50, $RootPath, $db);
	ItemsWithoutRetailPrice("STABBL", 5.50, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOBL", 5.50, $RootPath, $db);

	ItemsWithoutRetailPrice("TESTGE", 5.50, $RootPath, $db);
	ItemsWithoutRetailPrice("STABGE", 5.50, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOGE", 5.50, $RootPath, $db);

//	ItemsWithoutRetailPrice("CONSIG", 1.60, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_BusinessDevelopmentManager){
	PriceBelowStandard("SETKL", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("TESTKL", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("STABKL", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("NOPOKL", 4.75, 0, 10, $RootPath, $db);

	PriceBelowStandard("SETBL", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("TESTBL", 5.50, 0, 10, $RootPath, $db);
	PriceBelowStandard("STABBL", 5.50, 0, 10, $RootPath, $db);
	PriceBelowStandard("NOPOBL", 5.50, 0, 10, $RootPath, $db);

	PriceBelowStandard("SETGE", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("TESTGE", 5.50, 0, 10, $RootPath, $db);
	PriceBelowStandard("STABGE", 5.50, 0, 10, $RootPath, $db);
	PriceBelowStandard("NOPOGE", 5.50, 0, 10, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_BusinessDevelopmentManager){
	ItemsTooCheap("TESTKL", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("STABKL", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("NOPOKL", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("TESTBL", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("STABBL", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("NOPOBL", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("TESTGE", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("STABGE", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("NOPOGE", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooExpensive("TESTKL", 4.75, 5.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("STABKL", 4.75, 5.50, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("NOPOKL", 4.75, 6.00, 0.05, 5, 800, 60, $RootPath, $db);

	ItemsTooExpensive("TESTBL", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("STABBL", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("NOPOBL", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);

	ItemsTooExpensive("TESTGE", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("STABGE", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("NOPOGE", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);

}


if ($KL_SystemAdmin 
	OR $KL_BusinessDevelopmentManager){
	PriceWrongRounding($RootPath, $db);
}

prnMsg("Performed ". NUMBER_OF_TESTS . " control tests",'success');

time_finish($begintime);

include ('includes/footer.inc');

?>
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
	OR $KL_PurchasingManager){
	ItemsWithoutRetailPrice("TESTSI", 4.75, $RootPath, $db);
	ItemsWithoutRetailPrice("SILVER", 4.75, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOSI", 4.75, $RootPath, $db);

	ItemsWithoutRetailPrice("TESTSS", 4.75, $RootPath, $db);
	ItemsWithoutRetailPrice("STAINL", 4.75, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOSS", 4.75, $RootPath, $db);

	ItemsWithoutRetailPrice("TESTFJ", 5.50, $RootPath, $db);
	ItemsWithoutRetailPrice("FASHIO", 5.50, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOFJ", 5.50, $RootPath, $db);

	ItemsWithoutRetailPrice("TESTAC", 5.50, $RootPath, $db);
	ItemsWithoutRetailPrice("ACCESO", 5.50, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOAC", 5.50, $RootPath, $db);

//	ItemsWithoutRetailPrice("CONSIG", 1.60, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_PurchasingManager){
	PriceBelowStandard("TESTSI", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("SILVER", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("NOPOSI", 4.75, 0, 10, $RootPath, $db);

	PriceBelowStandard("TESTSS", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("STAINL", 4.75, 0, 10, $RootPath, $db);
	PriceBelowStandard("NOPOSS", 4.75, 0, 10, $RootPath, $db);

	PriceBelowStandard("TESTFJ", 5.50, 0, 10, $RootPath, $db);
	PriceBelowStandard("FASHIO", 5.50, 0, 10, $RootPath, $db);
	PriceBelowStandard("NOPOFJ", 5.50, 0, 10, $RootPath, $db);

	PriceBelowStandard("TESTAC", 5.50, 0, 10, $RootPath, $db);
	PriceBelowStandard("ACCESO", 5.50, 0, 10, $RootPath, $db);
	PriceBelowStandard("NOPOAC", 5.50, 0, 10, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_PurchasingManager){
	ItemsTooCheap("TESTSI", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("SILVER", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("NOPOSI", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("TESTSS", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("STAINL", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("TESTSS", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);
	
	ItemsTooCheap("TESTFJ", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("FASHIO", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("NOPOFJ", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("TESTAC", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("ACCESO", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
	ItemsTooCheap("NOPOAC", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooExpensive("TESTSI", 4.75, 5.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("SILVER", 4.75, 5.50, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("NOPOSI", 4.75, 6.00, 0.05, 5, 800, 60, $RootPath, $db);

	ItemsTooExpensive("TESTSS", 4.75, 5.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("STAINL", 4.75, 5.50, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("NOPOSS", 4.75, 6.00, 0.05, 5, 800, 60, $RootPath, $db);
	
	ItemsTooExpensive("TESTFJ", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("FASHIO", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("NOPOFJ", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);

	ItemsTooExpensive("TESTAC", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("ACCESO", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("NOPOAC", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);

}


if ($KL_SystemAdmin 
	OR $KL_PurchasingManager){
	PriceWrongRounding($RootPath, $db);
}

prnMsg("Performed ". NUMBER_OF_TESTS . " control tests",'success');

time_finish($begintime);

include ('includes/footer.inc');

?>
<?php
define("VERSIONFILE", "3.11"); 
define("NUMBER_OF_TESTS", 93); 

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

$begintime = time_start();

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

/***************************************************************************************
* RETAIL PRICE         
***************************************************************************************/
if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")){
	ItemsWithoutRetailPrice("TESTSI", 4.25, $RootPath, $db);
	ItemsWithoutRetailPrice("TESTSS", 4.25, $RootPath, $db);
	ItemsWithoutRetailPrice("TESTFJ", 4.25, $RootPath, $db);
	ItemsWithoutRetailPrice("TESTAC", 4.25, $RootPath, $db);
	ItemsWithoutRetailPrice("SILVER", 4.25, $RootPath, $db);
	ItemsWithoutRetailPrice("STAINL", 4.25, $RootPath, $db);
	ItemsWithoutRetailPrice("FASHIO", 5.00, $RootPath, $db);
	ItemsWithoutRetailPrice("ACCESO", 5.00, $RootPath, $db);
	ItemsWithoutRetailPrice("CONSIG", 1.60, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOAC", 5.00, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOFJ", 5.00, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOSI", 4.25, $RootPath, $db);
	ItemsWithoutRetailPrice("NOPOSS", 4.25, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")){
	PriceBelowStandard("TESTSI", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("TESTSI", 4.25, 0.20,  5, $RootPath, $db);

	PriceBelowStandard("TESTSS", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("TESTSS", 4.25, 0.20,  5, $RootPath, $db);

	PriceBelowStandard("TESTFJ", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("TESTFJ", 4.25, 0.20,  5, $RootPath, $db);

	PriceBelowStandard("TESTAC", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("TESTAC", 4.25, 0.20,  5, $RootPath, $db);

	PriceBelowStandard("SILVER", 4.25, 0.03, 15, $RootPath, $db);
	PriceBelowStandard("SILVER", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("SILVER", 4.25, 0.20,  5, $RootPath, $db);

	PriceBelowStandard("STAINL", 4.25, 0.03, 15, $RootPath, $db);
	PriceBelowStandard("STAINL", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("STAINL", 4.25, 0.20,  5, $RootPath, $db);

	PriceBelowStandard("FASHIO", 4.25, 0.03, 15, $RootPath, $db);
	PriceBelowStandard("FASHIO", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("FASHIO", 4.25, 0.20,  5, $RootPath, $db);

	PriceBelowStandard("ACCESO", 4.50, 0.03, 15, $RootPath, $db);
	PriceBelowStandard("ACCESO", 4.50, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("ACCESO", 4.50, 0.20,  5, $RootPath, $db);

	PriceBelowStandard("CONSIG", 1.60, 0.03,  1, $RootPath, $db);
	PriceBelowStandard("CONSIG", 1.60, 0.20,  1, $RootPath, $db);

	PriceBelowStandard("NOPOAC", 5.00, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("NOPOAC", 5.00, 0.20,  5, $RootPath, $db);
	PriceBelowStandard("NOPOFJ", 5.00, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("NOPOFJ", 5.00, 0.20,  5, $RootPath, $db);
	PriceBelowStandard("NOPOSI", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("NOPOSI", 4.25, 0.20,  5, $RootPath, $db);
	PriceBelowStandard("NOPOSS", 4.25, 0.05, 10, $RootPath, $db);
	PriceBelowStandard("NOPOSS", 4.25, 0.20,  5, $RootPath, $db);

	ItemsWith20501231($RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")){
	ItemsTooCheap("TESTSI", 4.25, 4.75, 0.05, 10,  50, 60, $RootPath, $db);
	ItemsTooCheap("TESTSI", 4.25, 4.50, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("TESTSS", 4.25, 4.75, 0.05, 10,  50, 60, $RootPath, $db);
	ItemsTooCheap("TESTSS", 4.25, 4.50, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("TESTFJ", 4.25, 4.75, 0.05, 10,  50, 60, $RootPath, $db);
	ItemsTooCheap("TESTFJ", 4.25, 4.50, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("TESTAC", 4.25, 4.75, 0.05, 10,  50, 60, $RootPath, $db);
	ItemsTooCheap("TESTAC", 4.25, 4.50, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("SILVER", 4.25, 4.75, 0.05, 10,  50, 60, $RootPath, $db);
	ItemsTooCheap("SILVER", 4.25, 4.50, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("STAINL", 4.25, 4.75, 0.05, 10,  50, 60, $RootPath, $db);
	ItemsTooCheap("STAINL", 4.25, 4.50, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("FASHIO", 4.25, 4.75, 0.05, 10,  50, 60, $RootPath, $db);
	ItemsTooCheap("FASHIO", 4.25, 4.50, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooCheap("ACCESO", 4.25, 4.75, 0.05, 10,  50, 60, $RootPath, $db);
	ItemsTooCheap("ACCESO", 4.25, 4.50, 0.05, 10, 100, 60, $RootPath, $db);

	ItemsTooExpensive("TESTSI", 4.25, 5.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("TESTSI", 4.25, 6.00, 0.05, 5, 600, 90, $RootPath, $db);
	ItemsTooExpensive("TESTSS", 4.25, 5.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("TESTSS", 4.25, 6.00, 0.05, 5, 600, 90, $RootPath, $db);
	ItemsTooExpensive("TESTFJ", 4.25, 5.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("TESTFJ", 4.25, 6.00, 0.05, 5, 600, 90, $RootPath, $db);
	ItemsTooExpensive("TESTAC", 4.25, 5.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("TESTAC", 4.25, 6.00, 0.05, 5, 600, 90, $RootPath, $db);

	ItemsTooExpensive("SILVER", 4.25, 5.50, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("SILVER", 4.25, 6.00, 0.05, 5, 600, 90, $RootPath, $db);

	ItemsTooExpensive("STAINL", 4.25, 5.50, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("STAINL", 4.25, 6.00, 0.05, 5, 600, 90, $RootPath, $db);

	ItemsTooExpensive("FASHIO", 4.25, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("FASHIO", 4.25, 6.50, 0.05, 5, 600, 90, $RootPath, $db);

	ItemsTooExpensive("ACCESO", 4.25, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
	ItemsTooExpensive("ACCESO", 4.25, 6.50, 0.05, 5, 600, 90, $RootPath, $db);

	ItemsTooExpensive("NOPOSI", 4.25, 6.00, 0.05, 5, 800, 60, $RootPath, $db);
	ItemsTooExpensive("NOPOSI", 4.25, 6.50, 0.05, 5, 600, 60, $RootPath, $db);
	ItemsTooExpensive("NOPOSS", 4.25, 6.00, 0.05, 5, 800, 60, $RootPath, $db);
	ItemsTooExpensive("NOPOSS", 4.25, 6.50, 0.05, 5, 600, 60, $RootPath, $db);
	ItemsTooExpensive("NOPOFJ", 4.25, 6.00, 0.05, 5, 800, 60, $RootPath, $db);
	ItemsTooExpensive("NOPOFJ", 4.25, 6.50, 0.05, 5, 600, 60, $RootPath, $db);
	ItemsTooExpensive("NOPOAC", 4.25, 6.00, 0.05, 5, 800, 60, $RootPath, $db);
	ItemsTooExpensive("NOPOAC", 4.25, 6.50, 0.05, 5, 600, 60, $RootPath, $db);

}


if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")){
	PriceWrongRounding($RootPath, $db);
}

prnMsg("Performed ". NUMBER_OF_TESTS . " control tests",'success');

time_finish($begintime);

include ('includes/footer.inc');

?>
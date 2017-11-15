<?php
define("VERSIONFILE", "5.00");

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include ('includes/session.php');
$Title = _('Kapal-Laut Pricing Control Board '. VERSIONFILE);
include ('includes/header.php');
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
$NumberOfTestExecuted = 0;

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

/* Assign the sections to be executed, to avoid error 504*/
$ShowSectionInfo = FALSE;
$ProcessSection01 = FALSE;
$ProcessSection02 = FALSE;

if (!isset($_GET['Section'])){
	$ProcessSection01 = TRUE;
	$ProcessSection02 = TRUE;
}else{
	$ShowSectionInfo = TRUE;
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = TRUE;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = TRUE;
	}
}

/***************************************************************************************
* SECTION 1         
***************************************************************************************/
if ($ProcessSection01){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 01",'info');
	}
	/***************************************************************************************
	* RETAIL PRICE         
	***************************************************************************************/

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		
	//	PricesNotUpdatedinXDays(365*2, 15, $RootPath, $db);
	//	PricesNotUpdatedinXDays(365  , 10, $RootPath, $db);

		ItemsWithoutRetailPrice("SETKL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETBL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETGE", 3.50, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsWithoutRetailPrice("TESTKL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("TESTBL", 5.00, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("TESTGE", 3.50, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsWithoutRetailPrice("STABKL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("STABBL", 5.00, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("STABGE", 3.50, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsWithoutRetailPrice("NOPOKL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("NOPOBL", 5.00, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("NOPOGE", 3.50, $RootPath, $db);
		$NumberOfTestExecuted++;

	//	ItemsWithoutRetailPrice("CONSIG", 1.60, $RootPath, $db);
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		PriceBelowStandard("SETKL", 4.75, 0, 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("TESTKL", 4.75, 0, 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("STABKL", 4.75, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("NOPOKL", 4.75, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;

		PriceBelowStandard("SETBL", 4.75, 0, 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("TESTBL", 5.50, 0, 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("STABBL", 5.50, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("NOPOBL", 5.50, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;

		PriceBelowStandard("SETGE", 3.50, 0, 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("TESTGE", 3.50, 0, 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("STABGE", 3.50, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("NOPOGE", 3.50, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
}

/***************************************************************************************
* SECTION 2
***************************************************************************************/

if ($ProcessSection02){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 02",'info');
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){

		ItemsTooCheap("TESTKL", 4.75, 5.00, 0.05, 10, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("STABKL", 4.75, 5.00, 0.05, 20, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("NOPOKL", 4.75, 5.00, 0.05, 20, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsTooCheap("TESTBL", 5.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("STABBL", 5.50, 5.75, 0.05, 20, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("NOPOBL", 5.50, 5.75, 0.05, 20, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;

/*		ItemsTooCheap("TESTGE", 3.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("STABGE", 3.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("NOPOGE", 3.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
*/
		ItemsTooExpensive("TESTKL", 4.75, 5.00, 0.05, 10, 400, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("STABKL", 4.75, 5.50, 0.05, 20, 400, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("NOPOKL", 4.75, 6.00, 0.05, 20, 400, 60, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsTooExpensive("TESTBL", 5.50, 6.00, 0.05, 10, 200, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("STABBL", 5.50, 6.00, 0.05, 20, 200, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("NOPOBL", 5.50, 6.00, 0.05, 20, 200, 90, $RootPath, $db);
		$NumberOfTestExecuted++;

/*		ItemsTooExpensive("TESTGE", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("STABGE", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("NOPOGE", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
*/
	}


	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		PriceWrongRounding($RootPath, $db);
		$NumberOfTestExecuted++;
	}
}

prnMsg("Performed ". $NumberOfTestExecuted . " pricing control tests",'success');

time_finish($begintime);

include ('includes/footer.php');

?>
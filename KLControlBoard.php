<?php
define("VERSIONFILE", "4.01");

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include('includes/session.php');
$Title = _('Kapal-Laut General Control Board '. VERSIONFILE);
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/KLReorderLevel.php');

include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');
include('includes/WeberpOpenCartDefines.php');

/* Do the pending GL Postings to get the latest financial control reports*/
include('includes/GLPostings.inc');

/* ASSIGN users to groups */
include('includes/KLRoles.inc');

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
* TEST AND PLAY AREA      
***************************************************************************************/

if ($KL_SystemAdmin){
//	ItemsNotNeededInOnlineOrderButRequested($RootPath, $db);
//	phpinfo();
//	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_AdministrationTeam 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_PurchasingTeam 
	OR $KL_ShopSupportTeam 
	OR $KL_ShopSupportLeader 
	OR $KL_SalesDirector 
	OR $KL_ShopManager
	OR $KL_ShopManagerOnline
	OR $KL_PettyCash 
	OR $KL_SPGSeniorOrSupport 
	OR $KL_SPGJunior){

//	$NumberOfTestExecuted++;
	
}

/***************************************************************************************
* SECTION 1         
***************************************************************************************/

if ($ProcessSection01){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 01",'info');
	}
	/***************************************************************************************
	* SPG PERFORMANCE         
	***************************************************************************************/

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_ShopManager){
		SPGNotReportingSalesInDays(2, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SalesDirector
		OR $KL_ShopManager
		OR $KL_ShopSupportLeader
		OR $KL_OperationalManager){
// POS does not allow splitted payments so no reason for this control check.
//		SplittedPaymentsBySPG(15, 2, $db);
//		$NumberOfTestExecuted++;
	}


	/***************************************************************************************
	* STANDARD COST         
	***************************************************************************************/
	if ($KL_PurchasingTeam){
		SuppliersWithoutBasicData($RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutStandardCost($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Philippines", "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("India"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingTeam) {
		WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_FOREIGN, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Philippines", "", STANDARD_COST_FACTOR_FOREIGN, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("India"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	/***************************************************************************************
	* RETAIL PRICE         
	***************************************************************************************/

	if ($KL_BusinessDevelopmentManager
		OR $KL_SystemAdmin){
		over_or_below_limit("DISC80 Items in AR", "BELOW", 50, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_ShopSupportTeam){
		
		over_or_below_limit("Items changing price or moving category", "OVER", 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items changing price", "OVER", 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 20% discount", "OVER", 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 50% discount", "OVER", 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 80% discount", "OVER", 20, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam){

		ItemsChangingPriceDelayed(4, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(20, 4, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(50, 4, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(80, 4, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager
		OR $KL_BusinessDevelopmentManager){

		ItemsChangingPriceDelayed(5, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(20, 5, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(50, 5, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(80, 5, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportLeader){
		
		ItemsInWrongShops("KAPAL-LAUT", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInWrongShops("BLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInWrongShops("OUTLET", $RootPath, $db);
		$NumberOfTestExecuted++;

	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportLeader){	

		DiscountedItemsWithWrongDiscount("DISC20", "20", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC2A", "20", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC50", "50", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC5A", "50", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC58", "80", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC8A", "80", $RootPath, $db);
		$NumberOfTestExecuted++;
		NotDiscountedItemsWithDiscount($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager 
		OR $KL_ShopSupportLeader){
		ErrorsInTransfers( 7, $RootPath, $db);
		$NumberOfTestExecuted++;
//		ErrorsInTransfers(30, $RootPath, $db);
//		$NumberOfTestExecuted++;
	}
	
	/***************************************************************************************
	* BALANCE ACCOUNTS         
	***************************************************************************************/
	if ($KL_SystemAdmin){
		GoodsReceivedNotInvoicedControl(1000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		CustomersDebtControl(1000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_AdministrationTeam){
		
		BalanceAccountControl("111111101",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111102",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111103",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111105",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111106",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111107",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111108",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111109",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111110",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111111",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111112",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111113",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111114",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111115",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111116",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111117",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111118",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111119",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111120",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111121",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111122",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111123",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111124",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111125",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111126",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111127",         0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_PurchasingTeam
		OR $KL_AdministrationTeam){
		BalanceAccountControl("111111100",          -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111202030",          -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_AdministrationTeam){
		// Bank Mandiri or  BCA has enough funds to be transferred to Danamon
		BalanceAccountControl(ACCOUNT_PTBB_MANDIRI,  1000000,   50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121101PT",  1000000,  100000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110PT",   100000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin){
		BalanceListAccountControl("('111111101', 
									'111111102', 
									'111111103', 
									'111111109', 
									'111111110', 
									'111111111', 
									'111111112', 
									'111111113', 
									'111111114', 
									'111111115', 
									'111111116', 
									'111111117', 
									'111111118', 
									'111111119', 
									'111111120', 
									'111111121', 
									'111111122', 
									'111111123', 
									'111111124', 
									'111111125', 
									'111111126', 
									'111111127')", "Total Cash @ shops",         0, 22 * 7000000, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111121105AD', 
									'111203010AD')", "Total Banks PT.ADU", 1000000000, 4000000000, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111121100PT', 
									'111121101PT', 
									'111121105PT', 
									'111121110PT', 
									'111121111PT', 
									'111121120PT', 
									'111121130PT', 
									'111203010PT',
									'111259010PT', 
									'111259020PT', 
									'111259050PT')", "Total Banks PT.BB", 2000000000, 3500000000, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111259010PT', 
									'111259020PT', 
									'111259050PT')", "Total PayPal PT.BB", 0, 20000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_AdministrationTeam){
		BalanceAccountControl("111121100IK",  1000000,  50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
// Bank Danamon IK not opened yet
//		BalanceAccountControl("111121105IK",  1000000,  50000000, $periodnow, $db);
//		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110IK",  1000000,  50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_AdministrationTeam){
		BalanceAccountControl("111121100PI",  1000000,  50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
// Bank Danamon IK not opened yet
//		BalanceAccountControl("111121105PI",  1000000,  50000000, $periodnow, $db);
//		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110PI",  1000000,  50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		BalanceListAccountControl("('111131100', 
									'111208010', 
									'111208020', 
									'111208030', 
									'111208040')", "Total Brankas RL",      0, 2000000000, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111513000', 
									'111513000AD')", "Total WIP",   -5000000,    5000000, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceAccountControl("111111200",   50000000,  100000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111202010",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111209",          0,   25000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111131100",         -1, 1000000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111520000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111512000",   50000000,  200000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111800000",  200000000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111900000",   15000000,   25000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111311100",  -20000000,          0, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111499000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021400", -100000000,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021500",  600000000,  900000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612012015",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612012016",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* STOCK CONTROL         
	***************************************************************************************/

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){
		ItemsInSetup("ReadyToTest", "SETKLA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInSetup("ReadyToTest", "SETBLA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInSetup("ReadyToTest", "SETGEA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInSetup("NeedDescription", "SETKLA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInSetup("NeedDescription", "SETBLA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInSetup("NeedDescription", "SETGEA", $RootPath, $db);
		$NumberOfTestExecuted++;
		//ItemsInSetup("NeedPrice", $RootPath, $db);
		//$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETKLA", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETBLA", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETGEA", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInSetup("WithReorderLevel", "SETKLA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInSetup("WithReorderLevel", "SETBLA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInSetup("WithReorderLevel", "SETGEA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ObsoleteComponentsInActiveBOM($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){

		ItemsInmediateShortage("COMPON", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInmediateShortage("COMPOA", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager){

		GoodsJustArrived("PO", "KANTO", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustArrived("WO", "KANTO", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustArrived("WO", "SUPBA", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SAMPR", "KANTO", 2, 30, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SASPG", "KANTO", 2, 30, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERSU", "KANTO", 2, 30, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERSW", "KANTO", 2, 30, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERDE", "KANTO", 2, 30, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERVI", "KANTO", 2, 30, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager){

//		ItemsWithStockLocationButNoStockAvailable("CSLAZ", "Lazada", 15, 9999, $RootPath, $db);
//		$NumberOfTestExecuted++;
//		ItemsWithStockLocationButNoStockAvailable("CSZAL", "Zalora", 15, 9999, $RootPath, $db);
//		$NumberOfTestExecuted++;
	
		ItemsWithStockKantorButReorderLevelTokoZero($RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("TESTKA", "TOKPU", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKA", "TOKKA", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKA", "TOKSU", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKA", "TOKSS", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKA", "TOKPA", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKA", "TOKSA", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKA", "TOK66", 8, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("STABKA", "TOKPU", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKKA", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKSU", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKSS", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKPA", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKSA", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOK66", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKSE", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKKS", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKOB", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKM2", 14, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKA", "TOKU2", 14, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("NOPOKA", "TOKPU", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKA", "TOKKA", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKA", "TOKSU", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKA", "TOKSS", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKA", "TOKPA", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKA", "TOKSA", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKA", "TOK66", 8, $RootPath, $db);
		$NumberOfTestExecuted++;
		
		CategoryItemsNotInShop("TESTBA", "TOKBU", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTBA", "TOKPS", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTBA", "TOKSB", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTBA", "TOKBB", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTBA", "TOKBK", 6, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("STABBA", "TOKBU", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKPS", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKSB", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKPB", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKMU", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKU3", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKBB", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKTB", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKO2", 12, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBA", "TOKBK", 12, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("NOPOBA", "TOKBU", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOBA", "TOKPS", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOBA", "TOKSB", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOBA", "TOKBB", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOBA", "TOKBK", 6, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("DISC2A", "TOKAR", 1, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("DISC5A", "TOKAR", 1, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("DISC8A", "TOKAR", 1, $RootPath, $db);
		$NumberOfTestExecuted++;

	}


	if ($KL_OperationalManager 
		OR $KL_ShopSupportLeader
		OR $KL_PurchasingTeam){

		ConsumablesGoodsNotEnoughStock(30, 15, 45, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){

		ValueStockLocation("SERVI",    0,  150, 0, 0, $db);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERDE",    0,  150, 0, 0, $db);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERSU",    0,  150, 0, 0, $db);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERSW",    0,  150, 0, 0, $db);
		$NumberOfTestExecuted++;
		OvestockAtSamples(1, $RootPath, $db);
		$NumberOfTestExecuted++;
		SamplesNotLongerNeeded($RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "ONLYDISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ONLYDISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "ALL", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ALL", $RootPath, $db);
		$NumberOfTestExecuted++;
	}


	if ($KL_SystemAdmin
		OR $KL_PurchasingTeam){
		ItemsWithoutPurchasingData($RootPath, $db);
		$NumberOfTestExecuted++;
	}
	if ($KL_BusinessDevelopmentManager){
		ComponentsToObsolete(false, 0, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){
		FlaggedAsObsoleteButStockAvailable($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader){
		ItemsInKLProcessAndRLNotZero($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader){
		ItemsOnSpecialRequest($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam
		OR $KL_PurchasingTeam){
		PackagingItemsOnWrongLocation($RootPath, $db); // Works for both regular and outlet shop packaging
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin OR
		$KL_ShopSupportLeader OR 
		$KL_OperationalManager){
		PackagingToBeRefilledKapalLaut(false, $RootPath, $db);
		$NumberOfTestExecuted++;
		PackagingToBeRefilledBlink(false, $RootPath, $db);
		$NumberOfTestExecuted++;
		PackagingToBeRefilledOutlet(false, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingTeam){
		InsuficientStockForShopPackaging('SHPACK', 15, 80, 30, true, $RootPath, $db); // Works for both regular and outlet shop packaging
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportTeam){
		
		CheckNegativeStock($RootPath, $db);
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
	/***************************************************************************************
	* SALES CONTROL         
	***************************************************************************************/
	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){

		ItemsInCategoryForMoreThanDays( 120, "SETKLA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "SETBLA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "SETGEA", $RootPath, $db);
		$NumberOfTestExecuted++;


		ActiveItemsNoSales( 30, "TESTKA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTKA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "TESTBA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTBA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "TESTGA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTGA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "STABKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABKA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABBA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABGE", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABGA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "NOPOKA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOKA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "NOPOBA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOBA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOGE", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "NOPOGA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOGE", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOGA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "DISC20", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "DISC2A", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "DISC20", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "DISC2A", $RootPath, $db);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 50, "DISC50", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 50, "DISC5A", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "DISC50", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "DISC5A", $RootPath, $db);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 60, "DISC80", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 60, "DISC8A", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* PO, Sales Orders         
	***************************************************************************************/

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){
		OldPurchasingOrdersStillActive(90, $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongItemsOnPurchaseOrders($RootPath, $db);
		$NumberOfTestExecuted++;
		WrongItemsOnWorkOrders($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager){
		PurchaseOrdersProcessTime(90, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	
	if ($KL_BusinessDevelopmentManager OR 
		$KL_PurchasingTeam){
		PurchaseOrdersWrongPlannedDates($RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("IN NEGOTIAION WITH SUPPLIER", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("ON PRODUCTION", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("FINISHED BUT NOT PAID", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_BusinessDevelopmentManager){
		POStatusControl("STILL NOT FULLY PAID", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_BusinessDevelopmentManager OR 
		$KL_PurchasingTeam){
		POStatusControl("BALI PAID BUT NOT RECEIVED IN KANTOR", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("BALI RECEIVED IN KANTOR BUT NOT PAID", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PAID NOT SHIPPED BY SUPPLIER", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PAID NOT RECEIVED IN AYE CARGO", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PAID NOT RECEIVED IN WANGFOONG CARGO", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("IN AYE CARGO BUT NOT SHIPPED", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("IN WANGFOONG CARGO BUT NOT SHIPPED", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("SHIPPED IN TRANSIT", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("CUSTOMS CLEARANCE", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("RECEIVED IN KANTOR", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager){
		POStatusControl("ARRIVING IN NEXT DAYS", 75, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_OperationalManager){
		OutstandingOrders("Retail", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
		OutstandingOrders("Retail", "Quotation", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_OperationalManager
		OR $KL_ShopSupportLeader
		OR $KL_ShopSupportTeam){
		OutstandingOrders("Wholesale", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_OperationalManager){
		OutstandingOrders("Wholesale", "Quotation", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	
	/*
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopSupportLeader){ 
		OutstandingOrders("Consignment", "Order", $RootPath, $db);
		OutstandingOrders("Consignment", "Quotation", $RootPath, $db);
	}
	*/

	if ($KL_SystemAdmin 
		OR $KL_ShopManagerOnline){
		OnlineCustomersNoOrderPlaced($RootPath, $db);
		$NumberOfTestExecuted++;
		OnlineQuotationsFollowUp($RootPath, $db);
		$NumberOfTestExecuted++;
		OldOnlineQuotations(10, $RootPath, $db);
		$NumberOfTestExecuted++;
	//	OutstandingOrders("Online", "Quotation", $RootPath, $db);
	//	$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_ShopManagerOnline
		OR $KL_OperationalManager
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("Online", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
		OnlineItemsOnProcess($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_ShopSupportLeader){ 
		ItemsNotNeededInOnlineOrderButRequested($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_ShopManagerOnline){
		OnlineOrdersFollowUp("KL-WEBSITE", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
//		OnlineOrdersFollowUp("LAZADA", 10, $RootPath, $db);
//		$NumberOfTestExecuted++;
	}
	/***************************************************************************************
	* Other tests     
	***************************************************************************************/
	if ($KL_ITSupport
		OR $KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){
		ActiveItemsWithoutPicture($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_ITSupport){
		ImagesWithoutProduct($RootPath, $db);
		$NumberOfTestExecuted++;
		OpenCartItemsWithoutPicture($RootPath, $db, $db_oc, $oc_tableprefix);
		$NumberOfTestExecuted++;
	//	ImagesShouldNotBeInOpencartCatalog($RootPath, $db, $db_oc, $oc_tableprefix);
	//	$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		ItemsWithoutWeightOrVolume($RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsShouldBeInWebsite($db);
		$NumberOfTestExecuted++;
		UsersNotLoggingIn(60, "ALL_EXCEPT_SPGSUPPORT", $db);
		$NumberOfTestExecuted++;
		UsersNotLoggingIn(90, "SPGSUPPORT", $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager 
		OR $KL_BusinessDevelopmentManager 
		OR $KL_ShopSupportLeader 
		OR $KL_ShopManager
		OR $KL_SalesDirector){
		RegularTransfersToShopNotReceived('08:00:00','15:00:00', $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_ShopSupportLeader){
		TransferWithWrongInformation(15, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam){ 
		TransfersDelayed(3, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsCancelledInTransfers(3, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager){
		TransfersDelayed(4, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager){
		ItemsCancelledInTransfers(3, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if (!$KL_SystemAdmin){
		PettyCashBalance('User', $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		PettyCashBalance('Authorizer', $db);
		$NumberOfTestExecuted++;
		PettyCashToBeAuthorized($db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_OperationalManager 
		OR $KL_AdministrationTeam 
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportTeam 
		OR $KL_ShopSupportLeader 
		OR $KL_SalesDirector 
		OR $KL_PettyCash 
		OR $KL_SPGSeniorOrSupport 
		OR $KL_SPGJunior){
		PettyCashBalance('User', $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin){
		StockToPTADU("PO", 1, 1, $RootPath, $db);
		$NumberOfTestExecuted++;
		StockToPTADU("WO", 1, 1, $RootPath, $db);
		$NumberOfTestExecuted++;
		StockToPTADU("PO", 99999999, 2, $RootPath, $db);
		$NumberOfTestExecuted++;
		StockToPTADU("WO", 99999999, 2, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
}
prnMsg("Performed ". $NumberOfTestExecuted . " control tests",'success');

time_finish($begintime);

include ('includes/footer.php');


/********************************************************************************************
FUNCTIONS ONLY USED IN CONTROL BOARD
*********************************************************************************************/

function ActiveItemsNoSales($maxdays, $group, $RootPath, $db){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));

// This line goes in WHERE quantity if (Service Excluded) 
//							AND locstock.loccode NOT IN ('SERSU','SERVI')) AS quantity
	
	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.lastcategoryupdate,
					stockmaster.units, 
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid) AS quantity
			FROM 	stockmaster, stockcategory
			WHERE 	stockmaster.categoryid = stockcategory.categoryid
					AND stockmaster.discontinued = 0 
					AND stockmaster.klchangingprice = 0
					AND stockmaster.klmovingdiscount20 = 0
					AND stockmaster.klmovingdiscount50 = 0
					AND stockmaster.klmovingdiscount80 = 0
					AND stockmaster.lastcategoryupdate <= '" . $FromDate . "'
					AND stockmaster.categoryid ='" . $group . "'
					AND stockcategory.stocktype = 'F'
					AND NOT EXISTS (SELECT * 
									FROM 	salesorderdetails, salesorders
									WHERE 	stockmaster.stockid = salesorderdetails.stkcode
											AND (salesorderdetails.orderno = salesorders.orderno)
											AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
					AND (IFNULL((SELECT SUM(woitems.qtyreqd -woitems.qtyrecd) 
							FROM woitems, workorders
							WHERE woitems.stockid = stockmaster.stockid
								AND woitems.wo = workorders.wo
								AND workorders.closed = 0) ,0) = 0 )
					AND NOT EXISTS (SELECT * 
									FROM 	stockmoves
									WHERE 	stockmoves.stockid = stockmaster.stockid
											AND stockmoves.trandate >= '" . $FromDate . "')
					AND EXISTS (SELECT * 
								FROM 	stockmoves
								WHERE 	stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate < '" . $FromDate . "'
										AND stockmoves.qty > 0) 
					AND NOT EXISTS (SELECT * 
									FROM 	purchorderdetails
									WHERE 	purchorderdetails.itemcode = stockmaster.stockid
											AND purchorderdetails.completed = 0)
			GROUP BY stockmaster.stockid
			ORDER BY stockmaster.stockid";
	
	$result = DB_query($SQL);		
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $group . _(' Items with NO sales on last ') . $maxdays . ' days and NO current PO or WO. Move to next category step</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('DOB Category') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['categoryid'], 
					ConvertSQLDate($myrow['lastcategoryupdate']),
					locale_number_format($myrow['quantity'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}	

function ActiveItemsWithoutPicture($RootPath, $db){
/* EXPLAIN SQL 2014-05-21	Can't use key. Probably explained at http://stackoverflow.com/questions/11784322/why-would-mysql-not-use-keys-when-there-are-possible-keys 
2014-05-30 Fixed adding a new index disontinued+Stockid
2015-05-19 TAke out some exceptions 
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "

*/
	$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockcategory.categorydescription
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockmaster.discontinued = 0
			AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SETUP . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_CONSIGNMENT . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_COMPONENTS . "
				)
		ORDER BY stockcategory.categorydescription, stockmaster.stockid";
	$result = DB_query($SQL);
	$showHeader = TRUE;

	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			if(!file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg') ) {
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . _('Current Items without picture in webERP') . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$k = 0; //row colour counter
					$i = 1;
					$TableHeader = '<tr>
									<th class="ascending">' . '#' . '</th>
									<th class="ascending">' . _('Category') . '</th>
									<th class="ascending">' . _('Item Code') . '</th>
									<th class="ascending">' . _('Description') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$myrow['categorydescription'],
						$CodeLink, 
						$myrow['description']
						);
				$i++;
			}
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function BalanceAccountControl($account, $min, $max, $period, $db){
	$SQL = "SELECT (bfwd + actual) as saldo, accountname
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode = '" . $account . "'
				AND chartdetails.period = ". $period . "";
				
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	if ($myrow['saldo'] < $min){
		$text = "Account " . $account . " - " . $myrow['accountname'] . " is BELOW the minimum. Balance = " . locale_number_format($myrow['saldo'],0) . " Minimum = " . locale_number_format($min,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
	if ($myrow['saldo'] > $max){
		$text = "Account " . $account . " - " . $myrow['accountname'] . " is OVER the maximum. Balance = " . locale_number_format($myrow['saldo'],0) . " Maximum = " . locale_number_format($max,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function BalanceListAccountControl($accountlist, $description, $min, $max, $period, $db){
	$SQL = "SELECT SUM(bfwd + actual) as saldo
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode IN " . $accountlist . "
				AND chartdetails.period = ". $period . "";
				
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	if ($myrow['saldo'] < $min){
		$text = $description . " is BELOW the minimum. Balance = " . locale_number_format($myrow['saldo'],0) . " Minimum = " . locale_number_format($min,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
	if ($myrow['saldo'] > $max){
		$text = $description . " is OVER the maximum. Balance = " . locale_number_format($myrow['saldo'],0) . " Maximum = " . locale_number_format($max,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function CategoryItemsNotInShop($Category, $Shop, $MinQOH, $RootPath, $db){
	
	$Message = $Category . _(' items NOT in ') . $Shop . ' with QOH >= ' . $MinQOH .' (excluding Change of Price, Move to Discount, Service, Shop online and Return to Supplier)';

	$ShopsKL = NumberOfShops("SHOPKL", $db);
	$ShopsBL = NumberOfShops("SHOPBL", $db);
	$ShopsOU = NumberOfShops("SHOPOU", $db);

	// count to how many shops do we need to set the RL
	if ($Category == 'TESTKA'){
		$WhereCat = " AND stockmaster.categoryid = 'TESTKA' ";
		$TypeOfShop = 'SHOPKL';
		$TitleCat = "TEST";
		$ShopsToSetRL = $ShopsKL;
	} else if ($Category == 'STABKA') {
		$WhereCat = " AND (stockmaster.categoryid = 'STABKL' OR stockmaster.categoryid = 'STABKA')";
		$TypeOfShop = 'SHOPKL';
		$TitleCat = "STABLE";
		$ShopsToSetRL = $ShopsKL;
	} else if ($Category == 'NOPOKA') {
		$WhereCat = " AND (stockmaster.categoryid = 'NOPOKL' OR stockmaster.categoryid = 'NOPOKA')";
		$TypeOfShop = 'SHOPKL';
		$TitleCat = "NO MORE PO";
		$ShopsToSetRL = $ShopsKL;
	} else if ($Category == 'TESTBA') {
		$WhereCat = " AND stockmaster.categoryid = 'TESTBA' ";
		$TypeOfShop = 'SHOPBL';
		$TitleCat = "TEST";
		$ShopsToSetRL = $ShopsKL;
	} else if ($Category == 'STABBA') {
		$WhereCat = " AND (stockmaster.categoryid = 'STABBL' OR stockmaster.categoryid = 'STABBA')";
		$TypeOfShop = 'SHOPBL';
		$TitleCat = "STABLE";
		$ShopsToSetRL = $ShopsBL;
	} else if ($Category == 'NOPOBA') {
		$WhereCat = " AND (stockmaster.categoryid = 'NOPOBL' OR stockmaster.categoryid = 'NOPOBA')";
		$TypeOfShop = 'SHOPBL';
		$TitleCat = "NO MORE PO";
		$ShopsToSetRL = $ShopsBL;
	} else if ($Category == 'DISC2A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC20' OR stockmaster.categoryid = 'DISC2A')";
		$TypeOfShop = 'SHOPOU';
		$TitleCat = "DISC20";
		$ShopsToSetRL = $ShopsOU;
	} else if ($Category == 'DISC5A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC50' OR stockmaster.categoryid = 'DISC5A')";
		$TypeOfShop = 'SHOPOU';
		$TitleCat = "DISC50";
		$ShopsToSetRL = $ShopsOU;
	} else if ($Category == 'DISC8A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC80' OR stockmaster.categoryid = 'DISC8A')";
		$TypeOfShop = 'SHOPOU';
		$TitleCat = "DISC80";
		$ShopsToSetRL = $ShopsOU;
	}else{
		$ShopsToSetRL = 0;
	}

	$Message = $TitleCat . _(' items NOT in ') . $Shop . ' with QOH >= ' . $MinQOH .' (excluding Change of Price, Move to Discount, Service, Shop online and Return to Supplier)';
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SAMPLE_LOCATIONS . ") AS qoh,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid" . 
				$WhereCat . "
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND locstock.loccode = '" . $Shop . "'
				AND locstock.quantity = 0 
				AND locstock.reorderlevel = 0
				AND ((SELECT l.reorderlevel
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode = 'KASPE') = 0)
				AND ((SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SAMPLE_LOCATIONS . ") >= ". $MinQOH .")
				AND ((SELECT SUM(l.reorderlevel)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode IN " . LIST_ONLINE_SHOPS . ") = 0)
			ORDER BY stockmaster.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Message . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('RL=?') . '</th>
							<th class="ascending">' . _('RL=1') . '</th>
							<th class="ascending">' . _('RL=2') . '</th>
							<th class="ascending">' . _('RL=3') . '</th>
							<th class="ascending">' . _('RL=4') . '</th>
							<th class="ascending">' . _('RL=5') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			if ((ItemInList($Category , LIST_STOCK_CATEGORIES_TEST)) 
				OR (ItemInList($Category , LIST_STOCK_CATEGORIES_STABLE))
				OR (ItemInList($Category , LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING))
				OR (ItemInList($Category , LIST_STOCK_CATEGORIES_OUTLET))) {
				$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . 'Manual' . '</a>';
			}else{
				$ManualLink = '';
			}

			// set the links to nil, and just set some if we have enough QOH
			$LinkRL1 = '';
			$LinkRL2 = '';
			$LinkRL3 = '';
			$LinkRL4 = '';
			$LinkRL5 = '';
			if($ShopsToSetRL != 0){
				if ($myrow['qoh'] >= $ShopsToSetRL){
					$LinkRL1 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=1' . '">' . 'RL=1' . '</a>';
				}
				if ($myrow['qoh'] >= $ShopsToSetRL * 2){
					$LinkRL2 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=2' . '">' . 'RL=2' . '</a>';
				}
				if ($myrow['qoh'] >= $ShopsToSetRL * 3){
					$LinkRL3 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=3' . '">' . 'RL=3' . '</a>';
				}
				if ($myrow['qoh'] >= $ShopsToSetRL * 4){
					$LinkRL4 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=4' . '">' . 'RL=4' . '</a>';
				}
				if ($myrow['qoh'] >= $ShopsToSetRL * 5){
					$LinkRL5 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=5' . '">' . 'RL=5' . '</a>';
				}
			}

			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';

			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['qoh'], 
					$ManualLink,
					$LinkRL1,
					$LinkRL2,
					$LinkRL3,
					$LinkRL4,
					$LinkRL5
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function CheckNegativeStock($RootPath, $db){
	/* Check if there is any negative stock */

	$SQL = "SELECT stockmaster.stockid,			
				   stockmaster.description,			
				   stockmaster.decimalplaces,			
				   locations.locationname,			
				   locstock.quantity			
			FROM stockmaster INNER JOIN locstock 			
			ON stockmaster.stockid=locstock.stockid			
			INNER JOIN locations 			
			ON locstock.loccode = locations.loccode			
			WHERE locstock.quantity < 0			
			ORDER BY stockmaster.stockid";
				
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items with Negative Stock') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Location') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['locationname'], 
					locale_number_format($myrow['quantity'],$myrow['decimalplaces'])
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ConsumablesGoodsNotEnoughStock($DaysUsage, $DaysMinStock, $DaysStockPurchase, $RootPath, $db){
/* EXPLAIN SQL 2014-05-40 added index discontinued+categoryid*/
	/*  Check if there are consumable goods with not enough stock for the following $DaysMinStock
		based on last $DaysUsage usage*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysUsage));
	$FactorStock = $DaysMinStock / $DaysUsage;

	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				(SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode =  " . CODE_KANTOR . ") AS qtyKANTOR,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
						AND stockrequestitems.stockid = stockmaster.stockid
						AND stockrequest.despatchdate >= '" . $StartDate . "') AS usageKL
		FROM stockmaster
		WHERE stockmaster.categoryid IN('SHCONS')
			AND stockmaster.discontinued = 0 
			AND ((SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = " . CODE_KANTOR . ") < 
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
						AND stockrequestitems.stockid = stockmaster.stockid
						AND stockrequest.despatchdate >= '" . $StartDate . "') * ". $FactorStock .")
			AND NOT EXISTS (SELECT * 
					FROM 	purchorderdetails
					WHERE 	purchorderdetails.itemcode = stockmaster.stockid
							AND purchorderdetails.completed = 0)
		ORDER BY stockmaster.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Consumables with stock ready for less than ') . $DaysMinStock . ' days and NO active PO.' . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
							<th class="ascending">' . _('Used ') . $DaysUsage . ' days'. '</th>
							<th class="ascending">' . _('Urgent Needed') . '</th>
							<th class="ascending">' . _('Recommended Purchase') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$Needed = (($myrow['usageKL'] / $DaysUsage) * $DaysMinStock ) - $myrow['qtyKANTOR'];
			$Recommended = (($myrow['usageKL'] / $DaysUsage) * $DaysStockPurchase);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qtyKANTOR'],0),
					locale_number_format($myrow['usageKL'],0),
					locale_number_format($Needed,0),					
					locale_number_format($Recommended,0)					
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function CustomersDebtControl($AcceptedDifference, $period, $db){
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode = '111311100'
				AND chartdetails.period = ". $period . "";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	$ValueAtBalance = $myrow['saldo'];
	
	$SQL = "SELECT SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				)/currencies.rate AS balance
			FROM debtorsmaster,
				currencies,
				debtortrans
			WHERE debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = 'IDR' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$DebtValueIDR = $myrow[0];

	$SQL = "SELECT SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				)/currencies.rate AS balance
			FROM debtorsmaster,
				currencies,
				debtortrans
			WHERE debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = 'USD' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$DebtValueUSD = $myrow[0];

	$SQL = "SELECT SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				)/currencies.rate AS balance
			FROM debtorsmaster,
				currencies,
				debtortrans
			WHERE debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = 'AUD' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$DebtValueAUD = $myrow[0];

	$SQL = "SELECT SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				)/currencies.rate AS balance
			FROM debtorsmaster,
				currencies,
				debtortrans
			WHERE debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = 'EUR' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$DebtValueEUR = $myrow[0];	
	
	$DebtValue = $DebtValueIDR + $DebtValueUSD + $DebtValueAUD + $DebtValueEUR;
	
	if (abs($ValueAtBalance - $DebtValue) > $AcceptedDifference){
		$text = "Customer's Debt Balance value = " . locale_number_format($ValueAtBalance,0) . " <-> Customer's Debt = " . locale_number_format($DebtValue,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function DiscountedItemsWithWrongDiscount($Category, $DiscountCode, $RootPath, $db){
	$SQL = "SELECT * 
			FROM  stockmaster 
			WHERE categoryid = '" . $Category . "'
				AND discountcategory !=  '". $DiscountCode ."'
				AND discontinued = 0";
				
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Category . _(' items with wrong discount (Not ') . $DiscountCode. '%)</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function FlaggedAsObsoleteButStockAvailable($RootPath, $db){
	/* Check if there is any item flagged as obsolete BUT with some stock available */
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description
			FROM stockmaster
			WHERE discontinued = 1 
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) > 0";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Obsolete Items with available Stock') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function GoodsJustArrived($kind, $location, $numdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays));
	$ShopsKL = NumberOfShops("SHOPKL", $db);
	$ShopsBL = NumberOfShops("SHOPBL", $db);
	$ShopsOU = NumberOfShops("SHOPOU", $db);
	if ($kind == "PO"){
		$type = 25;
	}elseif ($kind == "WO"){
		$type = 26;
	}
	$SQL = "SELECT stockmoves.stockid, 
					stockmaster.description,
					stockmaster.categoryid,
					stockmoves.trandate, 
					stockmoves.qty AS qtyarrived,
					(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SAMPLE_LOCATIONS . ") AS qtytotal
			FROM stockmoves, stockmaster, stockcategory
			WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockcategory.stocktype = 'F'
				AND stockmoves.loccode ='" . $location . "'
				AND stockmoves.type ='" . $type . "'
				AND stockmoves.trandate >'" . $StartDate . "'
				ORDER BY stockmoves.trandate DESC, 
						stockmoves.stockid";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($kind == "PO"){
			echo '<p class="page_title_text" align="center"><strong>' . $kind . _(' Finished Goods just arrived at ') . $location . ' during the last '. $numdays . ' days'. '</strong></p>';
		}elseif ($kind == "WO"){
			echo '<p class="page_title_text" align="center"><strong>' . $kind . _(' Goods just produced at ') . $location . ' during the last '. $numdays . ' days'. '</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Received') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('RL=?') . '</th>
							<th class="ascending">' . _('RL=1') . '</th>
							<th class="ascending">' . _('RL=2') . '</th>
							<th class="ascending">' . _('RL=3') . '</th>
							<th class="ascending">' . _('RL=4') . '</th>
							<th class="ascending">' . _('RL=5') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			
			// count to how many shops do we need to set the RL
			if (($myrow['categoryid']== 'STABKL') 
				OR ($myrow['categoryid']== 'STABKA')){
				$TypeOfShop = 'SHOPKL';
				$ShopsToSetRL = $ShopsKL;
			}elseif (($myrow['categoryid']== 'STABBL') 
				OR ($myrow['categoryid']== 'STABBA')){
				$TypeOfShop = 'SHOPBL';
				$ShopsToSetRL = $ShopsBL;
			}elseif(($myrow['categoryid']== 'DISC20') 
					OR ($myrow['categoryid']== 'DISC2A') 
					OR ($myrow['categoryid']== 'DISC50') 
					OR ($myrow['categoryid']== 'DISC5A') 
					OR ($myrow['categoryid']== 'DISC80') 
					OR ($myrow['categoryid']== 'DISC8A')){
				$TypeOfShop = 'SHOPOU';
				$ShopsToSetRL = $ShopsOU;
			}else{
				$ShopsToSetRL = 0;
			}

			if ((ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_TEST)) 
				OR (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_STABLE))
				OR (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING))
				OR (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET))) {
				$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . 'Manual' . '</a>';
			}else{
				$ManualLink = '';
			}

			// set the links to nil, and just set some if we have enough QOH
			$LinkRL1 = '';
			$LinkRL2 = '';
			$LinkRL3 = '';
			$LinkRL4 = '';
			$LinkRL5 = '';
			if($ShopsToSetRL != 0){
				if ($myrow['qtytotal'] >= $ShopsToSetRL){
					$LinkRL1 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=1' . '">' . 'RL=1' . '</a>';
				}
				if ($myrow['qtytotal'] >= $ShopsToSetRL * 2){
					$LinkRL2 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=2' . '">' . 'RL=2' . '</a>';
				}
				if ($myrow['qtytotal'] >= $ShopsToSetRL * 3){
					$LinkRL3 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=3' . '">' . 'RL=3' . '</a>';
				}
				if ($myrow['qtytotal'] >= $ShopsToSetRL * 4){
					$LinkRL4 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=4' . '">' . 'RL=4' . '</a>';
				}
				if ($myrow['qtytotal'] >= $ShopsToSetRL * 5){
					$LinkRL5 = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&RL=5' . '">' . 'RL=5' . '</a>';
				}
			}

			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					ConvertSQLDate($myrow['trandate']),
					$myrow['stockid'], 
					$myrow['categoryid'], 
					$myrow['description'], 
					locale_number_format($myrow['qtyarrived'],0),
					locale_number_format($myrow['qtytotal'],0),
					$ManualLink,
					$LinkRL1,
					$LinkRL2,
					$LinkRL3,
					$LinkRL4,
					$LinkRL5
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function GoodsJustTransferred($locationfrom, $locationto, $numdays, $qohmax, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays+1));

	$SQL = "SELECT loctransfers.stockid,
					stockmaster.description,
					loctransfers.recdate, 
					loctransfers.recqty AS qtytransferred,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = loctransfers.stockid) AS qtytotal
			FROM loctransfers, stockmaster, stockcategory
			WHERE loctransfers.stockid = stockmaster.stockid
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype = 'F'
				AND loctransfers.shiploc ='" . $locationfrom . "'
				AND loctransfers.recloc ='" . $locationto . "'
				AND loctransfers.recdate >'" . $StartDate . "'
				AND (SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = loctransfers.stockid) <= " . $qohmax . "
				ORDER BY loctransfers.recdate DESC, 
						loctransfers.stockid";
						
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _(' Finished Goods just transferred from ') . $locationfrom  . ' to '. $locationto . ' during the last '. $numdays . ' days and QOH <= '. $qohmax . '.</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Transferred') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					ConvertSQLDate($myrow['recdate']),
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qtytransferred'],0),
					locale_number_format($myrow['qtytotal'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function GoodsReceivedNotInvoicedControl($AcceptedDifference, $period, $db){
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode = '211021400'
				AND chartdetails.period = ". $period . "";
// EXPLAIN SQL 2014-05-31 OK!
//prnMsg($SQL);
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	$ValueAtBalance = -$myrow['saldo'];
	
	$SQL = "SELECT SUM((grns.qtyrecd - grns.quantityinv) * (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))
			FROM grns, stockmaster
			WHERE stockmaster.stockid = grns.itemcode
				AND (grns.qtyrecd - grns.quantityinv) > 0";
// EXPLAIN SQL 2014-05-31
// NOT OK. All 10.000 rows each time
// prnMsg($SQL);	
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);

	$GoodsValue = $myrow[0];

	if (abs($ValueAtBalance - $GoodsValue) > $AcceptedDifference){
		$text = "Goods Received Balance value = " . locale_number_format($ValueAtBalance,0) . " <-> Real Goods Received Value at Std Cost = " . locale_number_format($GoodsValue,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function ImagesWithoutProduct($RootPath, $db){
	$ShowHeader = TRUE;
	$k = 0; //row colour counter
	$i= 0;
	// get all images in part_pics folder
	$suffix = ".jpg";
	$imagefiles = getDirectoryTree($_SESSION['part_pics_dir'], 'jpg');
	foreach ($imagefiles as $file) {
		if ($file != '.ftpquota' AND
			$file != 'Obsolete'){
			$StockId = substr($file, 0, strpos($file, $suffix));
			if (strpos($StockId, '.1') > 0){
				$StockId = substr($file, 0, strpos($StockId, '.1'));
			}
			if (strpos($StockId, '.2') > 0){
				$StockId = substr($file, 0, strpos($StockId, '.2'));
			}
			if (strpos($StockId, '.3') > 0){
				$StockId = substr($file, 0, strpos($StockId, '.3'));
			}
			if (strpos($StockId, '.4') > 0){
				$StockId = substr($file, 0, strpos($StockId, '.4'));
			}
			if (strpos($StockId, '.5') > 0){
				$StockId = substr($file, 0, strpos($StockId, '.5'));
			}
			$SQL = "SELECT stockid
				FROM stockmaster
				WHERE stockmaster.stockid = '" . $StockId . "'";
			$result = DB_query($SQL);
			if (DB_num_rows($result) == 0){
				if ($ShowHeader){
					echo '<p class="page_title_text" align="center"><strong>' . _('Images without product in webERP') .'</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('File') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						</tr>', 
						$_SESSION['part_pics_dir'].'/'.$file
						);
			}
		}
	}
	if (!$ShowHeader){
		echo '</table>
				</div>';
	}
}

function ItemsCancelledInTransfers($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT loctransfers.reference,
					loctransfers.shipdate,
					loctransfers.shiploc,
					loctransfers.recloc,
					loctransfers.stockid,
					loctransfercancellations.cancelqty,
					loctransfercancellations.canceldate,
					loctransfercancellations.canceluserid
			FROM loctransfers 
			INNER JOIN loctransfercancellations
				ON loctransfers.reference = loctransfercancellations.reference 
					AND loctransfers.stockid = loctransfercancellations.stockid
			WHERE loctransfercancellations.canceldate >= '". $StartDate ."'
			ORDER BY loctransfers.stockid";
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items cancelled in Transfers during the last ') . $maxdays . _(' days ') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Cancel Qty') . '</th>
							<th class="ascending">' . _('Cancel Date') . '</th>
							<th class="ascending">' . _('Cancelled By') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$TransferLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink,
					$TransferLink, 
					ConvertSQLDateTime($myrow['shipdate']), 
					$myrow['shiploc'], 
					$myrow['recloc'],
					locale_number_format($myrow['cancelqty'],0),
					ConvertSQLDateTime($myrow['canceldate']), 
					$myrow['canceluserid']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsChangingPriceDelayed($NumDays, $RootPath, $db){
/* EXPLAIN SQL 2014-05-21	*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT SUM(locstock.quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = locations.loccode
						AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . ") AS qohpos,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohconsignment,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT SUM(locstock.quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = locations.loccode
						AND locstock.loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
						AND locations.typeloc NOT IN " . BALI_SHOPS_LIST_BY_TYPE . "
						AND locstock.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
					FROM loctransfers,locations
					WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc = locations.loccode
						AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
					FROM loctransfers
					WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
					FROM loctransfers
					WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_KANTOR_LOCATIONS . ") AS intransitfromkantor,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klchangeprice.counterpricechange,
				klchangeprice.startprocessdate,
				klchangeprice.newretailprice
			FROM stockmaster, klchangeprice					
			WHERE stockmaster.stockid = klchangeprice.stockid
				AND klchangeprice.endprocessdate = '0000-00-00'
				AND klchangeprice.startprocessdate <= '". $StartDate ."'";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items delayed in Change Price Procedure for more than '). $NumDays . ' days. </strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Start Date') . '</th>
							<th class="ascending">' . _('QOH KL Shops') . '</th>
							<th class="ascending">' . _('QOH Consignment') . '</th>
							<th class="ascending">' . _('Transit From Kantor') . '</th>
							<th class="ascending">' . _('Transit To Kantor') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
							<th class="ascending">' . _('QOH Others') . '</th>
							<th class="ascending">' . _('QOH Total') . '</th>
							<th class="ascending">' . _('New Retail Price') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$NewPriceLink = locale_number_format($myrow['newretailprice'],0);
			
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($myrow['counterpricechange'],0),
					$CodeLink, 
					$myrow['description'],
					ConvertSQLDate($myrow['startprocessdate']),
					locale_number_format_zero_blank($myrow['qohpos']-$myrow['intransitfromshops'],0),
					locale_number_format_zero_blank($myrow['qohconsignment']-$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['intransitfromshops']+$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['qohkantor']-$myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['qohotherlocs'],0),
					locale_number_format_zero_blank($myrow['qohtotal'],0),
					$NewPriceLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsInCategoryForMoreThanDays($maxdays, $group, $RootPath, $db){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));


	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.lastcategoryupdate,
					stockmaster.units, 
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid) AS quantity
			FROM 	stockmaster
			WHERE 	stockmaster.discontinued = 0 
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.lastcategoryupdate <= '" . $FromDate . "'
				AND stockmaster.categoryid ='" . $group . "'
			ORDER BY stockmaster.stockid";
	
	$result = DB_query($SQL);		
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $group . ' Items for more than ' . $maxdays . ' days. Move to next step of cycle of life</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('DOB Category') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['categoryid'], 
					ConvertSQLDate($myrow['lastcategoryupdate']),
					locale_number_format($myrow['quantity'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}	

function ItemsInmediateShortage($Cat, $RootPath, $db){

	$SQL = "SELECT stm.stockid,
				COALESCE ((SELECT sum(quantity)
					FROM locstock
					WHERE stockid = stm.stockid),0) AS qoh,
				COALESCE ((SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd)
					FROM purchorders
						INNER JOIN purchorderdetails
							ON purchorders.orderno=purchorderdetails.orderno
					WHERE purchorderdetails.itemcode=stm.stockid
						AND purchorderdetails.completed = 0
						AND purchorders.status<>'Cancelled'
						AND purchorders.status<>'Pending'
						AND purchorders.status<>'Rejected'
						AND purchorders.status<>'Completed'),0) AS qtypo,
				COALESCE ((SELECT SUM(woitems.qtyreqd-woitems.qtyrecd)
					FROM woitems
						INNER JOIN workorders
							ON woitems.wo=workorders.wo
					WHERE workorders.closed=0
						AND woitems.stockid=stm.stockid),0) AS qtywo,
				COALESCE ((SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced)
					FROM salesorderdetails 
					INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
					WHERE salesorderdetails.completed=0
					AND salesorders.quotation=0
					AND salesorderdetails.stkcode=stm.stockid),0) AS directdemand,
				COALESCE ((SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd))
					FROM woitems INNER JOIN worequirements
						ON woitems.stockid=worequirements.parentstockid
					INNER JOIN workorders
						ON woitems.wo=workorders.wo
					AND woitems.wo=worequirements.wo
					WHERE  worequirements.stockid=stm.stockid
						AND workorders.closed=0),0) AS wodemand
			FROM stockmaster stm
			WHERE stm.discontinued = 0
				AND stm.categoryid = '" . $Cat . "'
				AND 
				(COALESCE ((SELECT sum(quantity)
					FROM locstock
					WHERE stockid = stm.stockid),0)
				+ 
				COALESCE ((SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd)
					FROM purchorders
						INNER JOIN purchorderdetails
							ON purchorders.orderno=purchorderdetails.orderno
					WHERE purchorderdetails.itemcode=stm.stockid
						AND purchorderdetails.completed = 0
						AND purchorders.status<>'Cancelled'
						AND purchorders.status<>'Pending'
						AND purchorders.status<>'Rejected'
						AND purchorders.status<>'Completed'),0)
				+ 
				COALESCE ((SELECT SUM(woitems.qtyreqd-woitems.qtyrecd)
					FROM woitems
						INNER JOIN workorders
							ON woitems.wo=workorders.wo
					WHERE workorders.closed=0
						AND woitems.stockid=stm.stockid),0)
				) <	(
				COALESCE ((SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced)
					FROM salesorderdetails 
					INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
					WHERE salesorderdetails.completed=0
					AND salesorders.quotation=0
					AND salesorderdetails.stkcode=stm.stockid),0)
				+ 
				COALESCE ((SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd))
					FROM woitems INNER JOIN worequirements
						ON woitems.stockid=worequirements.parentstockid
					INNER JOIN workorders
						ON woitems.wo=workorders.wo
					AND woitems.wo=worequirements.wo
					WHERE  worequirements.stockid=stm.stockid
						AND workorders.closed=0),0)
				)
			ORDER BY stm.stockid";
	
	$result = DB_query($SQL);		
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Cat . ' Items in inmediate shortage stock</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('Qty @ PO') . '</th>
							<th class="ascending">' . _('Qty @ WO') . '</th>
							<th class="ascending">' . _('Demand') . '</th>
							<th class="ascending">' . _('Shortage') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					locale_number_format($myrow['qoh'],0), 
					locale_number_format($myrow['qtypo'],0), 
					locale_number_format($myrow['qtywo'],0), 
					locale_number_format($myrow['directdemand']+$myrow['wodemand'],0),
					locale_number_format($myrow['qoh']+$myrow['qtypo']+$myrow['qtywo']-$myrow['directdemand']-$myrow['wodemand'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}	


function ItemsInKLProcessAndRLNotZero($RootPath, $db){
	/* Check if there is any item in any KL process and RL is not zero... */

	$SQL = "SELECT stockmaster.stockid,			
				   stockmaster.description,			
				   locstock.loccode,			
				   locations.locationname,			
				   locstock.reorderlevel,
					stockmaster.klmovingdiscount20,		
					stockmaster.klmovingdiscount50,		
					stockmaster.klmovingdiscount80,		
					stockmaster.klchangingprice   
			FROM stockmaster INNER JOIN locstock 			
			ON stockmaster.stockid=locstock.stockid			
			INNER JOIN locations 			
			ON locstock.loccode = locations.loccode			
			WHERE locstock.reorderlevel != 0
				AND (stockmaster.klmovingdiscount20 != 0
					OR  stockmaster.klmovingdiscount50 != 0
					OR  stockmaster.klmovingdiscount80 != 0
					OR stockmaster.klchangingprice != 0 ) 			
			ORDER BY stockmaster.stockid,
					locstock.loccode";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items with in KL process and RL not zero') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Location') . '</th>
							<th class="ascending">' . _('RL') . '</th>
							<th class="ascending">' . _('Changing Price') . '</th>
							<th class="ascending">' . _('MoveTo 20% Disc') . '</th>
							<th class="ascending">' . _('MoveTo 50% Disc') . '</th>
							<th class="ascending">' . _('MoveTo 80% Disc') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			if ($myrow['klchangingprice'] == 1){
				$ItemChangingPrice = "Yes";
			}else{
				$ItemChangingPrice = "";
			}
			if ($myrow['klmovingdiscount20'] == 1){
				$ItemMovingToDiscount20 = "Yes";
			}else{
				$ItemMovingToDiscount20 = "";
			}
			if ($myrow['klmovingdiscount50'] == 1){
				$ItemMovingToDiscount50 = "Yes";
			}else{
				$ItemMovingToDiscount50 = "";
			}
			if ($myrow['klmovingdiscount80'] == 1){
				$ItemMovingToDiscount80 = "Yes";
			}else{
				$ItemMovingToDiscount80 = "";
			}
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['locationname'], 
					locale_number_format($myrow['reorderlevel'],0),
					$ItemChangingPrice,
					$ItemMovingToDiscount20,
					$ItemMovingToDiscount50,
					$ItemMovingToDiscount80
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsNotNeededInOnlineOrderButRequested($RootPath, $db){
	
	$SQL = "SELECT locstock.stockid,
				locstock.quantity
			FROM locstock
			WHERE locstock.loccode = ". CODE_ONLINE_SHOP ."
				AND locstock.quantity > 0
				AND NOT EXISTS (SELECT 	salesorderdetails.stkcode
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.stkcode = locstock.stockid
									AND salesorders.quotation = 0
									AND salesorders.fromstkloc = ". CODE_ONLINE_SHOP ."
									AND salesorderdetails.completed= 0)";
	$result = DB_query($SQL);
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "Items Not needed for any Online Shop but with QOH > 0 in Shop Online" . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Item Code') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$ItemLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$ItemLink, 
					locale_number_format($myrow['quantity'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsInSetup($Check, $Category, $RootPath, $db){
	$today = date('Y-m-d');
	
	if ($Check == "ReadyToTest"){
		$Title = $Category . " Items ready to change to TEST";
		$SQLWhere = "AND LENGTH(stockmaster.description) > 2
					AND (SELECT SUM(locstock.quantity)
							FROM locstock
							WHERE locstock.stockid = stockmaster.stockid
								AND locstock.loccode = " . CODE_KANTOR . ") > 0
					AND (SELECT price
							FROM prices
							WHERE stockmaster.stockid = prices.stockid
								AND prices.startdate <= '". $today. "' 
								AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
								AND prices.typeabbrev = 'RT'
								AND currabrev = 'IDR') IS NOT NULL
					AND NOT EXISTS (SELECT *
							FROM loctransfers 
							WHERE  recqty < shipqty
								AND loctransfers.stockid =  stockmaster.stockid)";
	}elseif($Check == "NeedDescription"){
		$Title = $Category . " Items needing descriptions";
		$SQLWhere ="AND LENGTH(stockmaster.description) <= 2";
	}elseif($Check == "NeedPrice"){
		$Title = $Category . " Items needing price";
		$SQLWhere ="AND (SELECT price
				FROM prices
				WHERE stockmaster.stockid = prices.stockid
					AND prices.typeabbrev = 'RT'
					AND currabrev = 'IDR') IS NULL";
	}elseif($Check == "WithReorderLevel"){
		$Title = $Category . " Items with RL (items in SETUP should not have RL set)";
		$SQLWhere ="AND (SELECT SUM(reorderlevel)
				FROM locstock
				WHERE stockmaster.stockid = locstock.stockid) > 0 ";
	}else{
		$Title = $Category . " Items in SETUP";
		$SQLWhere ="";
	}

	$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			(SELECT price
				FROM prices
				WHERE stockmaster.stockid = prices.stockid
					AND prices.typeabbrev = 'RT'
					AND prices.startdate <= '". $today. "' 
					AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					AND currabrev = 'IDR') AS price,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) AS QOH
			FROM stockmaster
			WHERE stockmaster.categoryid = '" . $Category . "'
				AND discontinued = 0 ".
			 $SQLWhere ." 
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			if (    ($Check != "ReadyToTest") 
				OR (($Check == "ReadyToTest") 
					AND (file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg')))) {
				if ($ShowHeader){
					echo '<p class="page_title_text" align="center"><strong>' . $Title . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Price') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$RLLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . locale_number_format($myrow['QOH'],0) . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($myrow['price'],0),
						$RLLink
						);
				$i++;
			}
		}
		if(!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
}

function ItemsInWrongShops($TypeItem, $RootPath, $db){

	if ($TypeItem == "KAPAL-LAUT"){
		$Message = 'KL items on wrong shops';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . "
							OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . ")
						AND locations.typeloc = 'SHOPKL' ";
	}elseif ($TypeItem == "BLINK"){
		$Message = 'BLINK items on wrong shops';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . "
							OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . ")
						AND locations.typeloc = 'SHOPBL' ";
	}elseif ($TypeItem == "OUTLET"){
		$Message = 'DISCOUNT items on wrong shops';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . "
							OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . ")
						AND locations.typeloc = 'SHOPOU' ";
	}else{
		//error_
		return;
	}
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock, locations
			WHERE stockmaster.stockid = locstock.stockid 
				AND locstock.loccode = locations.loccode
				" .	$Condition . "
				AND ( locstock.quantity > 0 OR locstock.reorderlevel > 0 )
			ORDER BY stockmaster.stockid";
// EXPLAIN SQL 2014-05-31
//	prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Message . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('Reorder Level') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$CodeLinkRL = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['reorderlevel'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['loccode'], 
					$myrow['quantity'], 
					$CodeLinkRL 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsMovingToDiscountDelayed($TypeDiscount, $NumDays, $RootPath, $db){
/* EXPLAIN SQL 2014-05-21	*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT SUM(locstock.quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . ") AS qohpos,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohconsignment,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT SUM(locstock.quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locstock.loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
					AND locations.typeloc NOT IN " . BALI_SHOPS_LIST_BY_TYPE . "
					AND locstock.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers,locations
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc = locations.loccode
						AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_KANTOR_LOCATIONS . ") AS intransitfromkantor,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klmovetodiscount".$TypeDiscount.".countermovediscount,
				klmovetodiscount".$TypeDiscount.".startprocessdate,
				klmovetodiscount".$TypeDiscount.".discountcategory
			FROM stockmaster, klmovetodiscount".$TypeDiscount."					
			WHERE stockmaster.stockid = klmovetodiscount".$TypeDiscount.".stockid
				AND klmovetodiscount".$TypeDiscount.".endprocessdate = '0000-00-00'
				AND klmovetodiscount".$TypeDiscount.".startprocessdate <= '". $StartDate ."'";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . 'Items delayed Moving To ' . $TypeDiscount . '% Discount Procedure for more than '. $NumDays . ' days. </strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Start Date') . '</th>
							<th class="ascending">' . _('QOH KL Shops') . '</th>
							<th class="ascending">' . _('QOH Consignment') . '</th>
							<th class="ascending">' . _('Transit From Kantor') . '</th>
							<th class="ascending">' . _('Transit To Kantor') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
							<th class="ascending">' . _('QOH Others') . '</th>
							<th class="ascending">' . _('QOH Total') . '</th>
							<th class="ascending">' . _('Discount Code') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($myrow['countermovediscount'],0),
					$CodeLink, 
					$myrow['description'],
					ConvertSQLDate($myrow['startprocessdate']),
					locale_number_format_zero_blank($myrow['qohpos']-$myrow['intransitfromshops'],0),
					locale_number_format_zero_blank($myrow['qohconsignment']-$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['intransitfromshops']+$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['qohkantor']-$myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['qohotherlocs'],0),
					locale_number_format_zero_blank($myrow['qohtotal'],0),
					$myrow['discountcategory']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsOnSpecialRequest($RootPath, $db){
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid
				AND locstock.loccode = 'KASPE'
				AND (locstock.quantity > 0 
					OR locstock.reorderlevel > 0)
			ORDER BY stockmaster.stockid";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items on Special Kantor Request') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('Reorder Level') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['quantity'], 
					$myrow['reorderlevel'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsShouldBeInWebsite($db){
	$SQL = "SELECT stockid, description
			FROM stockmaster
			WHERE categoryid IN " . CATEGORIES_AVAILABLE_WEBSITE .
				SQLForWebsiteStockidExceptions(). "
				AND NOT EXISTS (SELECT *
								FROM salescatprod
								WHERE salescatprod.stockid = stockmaster.stockid)";
	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			if(file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg') ) {
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . _('Items with picture but not available in Online Shop') . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
									</tr>';
					echo $TableHeader;
					$k = 0; //row colour counter
					$i = 1;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$myrow['stockid'], 
						$myrow['description'] 
						);
				$i++;
			}			
		}
		if (!$showHeader){
			echo '</table>
					</div>';
		}
	}
}

function ItemsWithStockLocationButNoStockAvailable($Location, $NameLocation, $MinAvailable, $MaxTopSalesItems, $RootPath, $db){
	/*  EXPLAIN SQL 2014-05-30
		Examples of usage in control boards
		ItemsWithStockLocationButNoStockAvailable("WABOM", "WaterBom", 15, 600, $RootPath, $db);
		ItemsWithStockLocationButNoStockAvailable("WHAYA", "Ayana", 15, 600, $RootPath, $db);
		ItemsWithStockLocationButNoStockAvailable("WHINT", "InterContinental", 15, 600, $RootPath, $db);
		InsuficientStockForItems("STABKL", "TM-", "Tali Mie", 20, 40, $RootPath, $db);
		
		2018-03-18 taken out the condition:		AND locstock.reorderlevel > 0

	*/
	
	$SQL = "SELECT locstock.stockid,
				locstock.quantity,
				stockmaster.categoryid,
				(SELECT SUM(l2.quantity)
					FROM locations, locstock l2
					WHERE l2.loccode = locations.loccode
						AND locstock.stockid = l2.stockid
						AND (locations.typeloc IN " . ALL_SHOPS_LIST_BY_TYPE . "
							OR l2.loccode = " . CODE_KANTOR . ")
				) AS available
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ."
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET ."
				AND locstock.loccode = '" . $Location . "'
				AND locstock.quantity > 0
				AND (SELECT SUM(l2.quantity)
						FROM locations, locstock l2
						WHERE l2.loccode = locations.loccode
							AND locstock.stockid = l2.stockid
							AND (locations.typeloc IN " . ALL_SHOPS_LIST_BY_TYPE . "
								OR l2.loccode = " . CODE_KANTOR . ")
					) <= " . $MinAvailable;
	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = PositionTopSalesItem($myrow['stockid'], 60, $db);
			if($PositionTopSales <= $MaxTopSalesItems){
				if ($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . $MaxTopSalesItems ._(' Top Sales Items (Exclude No More Purchasing, Discount) with stock at ') . $NameLocation . ' but KL Stock Available (Toko + Kantor) <= ' . $MinAvailable . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('TopSale#') . '</th>
										<th class="ascending">' . _('Qty ') . $Location . '</th>
										<th class="ascending">' . _('QOH Available') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						locale_number_format($PositionTopSales,0),
						locale_number_format($myrow['quantity'],0),
						locale_number_format($myrow['available'],0)
						);
				$i++;
			}
		}
		if (!$showHeader){
			echo '</table>
					</div>';
		}
	}
}

function ItemsWithoutPurchasingData($RootPath, $db){
/* EXPLAIN SQL	2014-05-20	

id	select_type	table		type	possible_keys		key			key_len	ref									rows	Extra
1	SIMPLE		purchdata	ref		StockID,Preferred	Preferred	1		const								4387	Using where; Using temporary; Using filesort
1	SIMPLE		stockmaster	eq_ref	PRIMARY,StockID		PRIMARY		62		kurakura_kl_erp.purchdata.stockid	1	Using where

*/
	
	$SQL = "SELECT purchdata.stockid,
				purchdata.supplierno,
				price,
				conversionfactor,
				supplierdescription,
				suppliersuom,
				suppliers_partno,
				leadtime,
				MAX(purchdata.effectivefrom) AS latesteffectivefrom
			FROM purchdata, stockmaster
			WHERE purchdata.stockid = stockmaster.stockid 
				AND purchdata.preferred = 1
				AND stockmaster.discontinued = 0
				AND ((supplierdescription = '' AND suppliers_partno = '')
					OR suppliersuom = '')
			GROUP BY purchdata.price,
					purchdata.conversionfactor,
					purchdata.supplierdescription,
					purchdata.suppliersuom,
					purchdata.suppliers_partno,
					purchdata.leadtime
			ORDER BY purchdata.stockid, latesteffectivefrom DESC";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>Items without full purchasing data</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Supplier') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Supplier Part #') . '</th>
							<th class="ascending">' . _('Supplier Description') . '</th>
							<th class="ascending">' . _('UOM') . '</th>
							<th class="ascending">' . _('Leadtime') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$CodeLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $myrow['stockid'] . '">'. $myrow['stockid'] .'</a>';
			$SupplierLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $myrow['stockid'] . 
															'&SupplierID=' . $myrow['supplierno'] . 
															'&Edit=1' .
															'&EffectiveFrom=' . $myrow['latesteffectivefrom'] . '">'. $myrow['supplierno'] .'</a>';
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$SupplierLink, 
					$myrow['latesteffectivefrom'],
					$myrow['suppliers_partno'],
					$myrow['supplierdescription'],
					$myrow['suppliersuom'],
					locale_number_format($myrow['leadtime'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsWithoutStandardCost($RootPath, $db){
	/* Check if there is any item without standard cost */
	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description, 
				(SELECT SUM(locstock.quantity) 
					FROM locstock 
					WHERE locstock.stockid = stockmaster.stockid) AS availablestock
			FROM stockmaster,stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype != 'D'
				AND (materialcost + labourcost + overheadcost) = 0
				AND discontinued = 0";
// EXPLAIN SQL 2014-05-31
//	prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items without standard cost') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['availablestock'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsWithoutWeightOrVolume($RootPath, $db){
	$SQL = "SELECT stockmaster.stockid,
				   stockmaster.description,
				   stockmaster.grossweight,
				   stockmaster.netweight,
				   stockmaster.volume,
				   stockmaster.longdescription,	
				   stockmaster.categoryid	
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid IN " . CATEGORIES_AVAILABLE_WEBSITE .
				SQLForWebsiteStockidExceptions() . "
				AND (stockmaster.grossweight < 0.00001 
					OR stockmaster.volume < 0.00001
					OR stockmaster.grossweight <= stockmaster.netweight)
			ORDER BY stockmaster.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Online Shop items with no gross weight, no volume or Net > Gross Weight') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Net Weight Kg') . '</th>
							<th class="ascending">' . _('Gross Weight Kg') . '</th>
							<th class="ascending">' . _('Volume m3') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/Stocks.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['netweight'],5), 
					locale_number_format($myrow['grossweight'],5), 
					locale_number_format($myrow['volume'],5)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsWithStockKantorButReorderLevelTokoZero($RootPath, $db){
/**********************************************************************
items with stock kantor > 0 
RL is zero at all shops
No pending transfer regarding this item

2013-04-16 excluding items in change price process
2013-04-25 excluding items in move to discount / outlet process 
2014-12-02 excluding items in OLD categories

***********************************************************************/

	$SQL = "SELECT stockid,
			stockmaster.categoryid,
			stockmaster.description,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid
				AND (locstock.loccode = " . CODE_KANTOR . " ))AS QtyKantor
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND (SELECT SUM(locstock.reorderlevel)
					FROM locstock, locations
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = locations.loccode
						AND locations.loccode IN " . BALI_SHOPS_LIST_BY_TYPE . ") = 0
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = " . CODE_KANTOR . ") > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  recqty < shipqty
							AND loctransfers.stockid =  stockmaster.stockid)
				AND discontinued = 0
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SETUP . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OLD . "
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items with stock available (but NO changing price or category) at Kantor but RL zero for ALL SHOPS') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['categoryid'], 
					$myrow['description'], 
					locale_number_format($myrow['QtyKantor'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function NotDiscountedItemsWithDiscount($RootPath, $db){
	$SQL = "SELECT stockid,
					description
			FROM  stockmaster 
			WHERE   categoryid NOT IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS ."
				AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET ."
				AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_OLD ."
				AND discountcategory !=  ''
				AND discontinued = 0";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Not Discounted items with discount') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ObsoleteComponentsInActiveBOM($RootPath, $db){

	$SQL = "SELECT bom.parent,
				bom.component
			FROM bom, stockmaster AS stP, stockmaster AS stC
			WHERE bom.parent = stP.stockid 
				AND bom.component = stC.stockid
				AND stP.discontinued = 0
				AND stC.discontinued = 1";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Active BOM with obsolete components') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('BOM of') . '</th>
							<th class="ascending">' . _('Component') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLinkParent = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['parent'] . '">' . $myrow['parent'] . '</a>';
			$CodeLinkComponent = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['component'] . '">' . $myrow['component'] . '</a>';
			printf('<td>%s</td>
					<td>%s</td>
					</tr>', 
					$CodeLinkParent, 
					$CodeLinkComponent
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OldOnlineQuotations($NumDays, $RootPath, $db){

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$Titletext = "Online Quotations with more than " . $NumDays . " Days. (To de deleted)";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				salesorders.klemailremindbanktransfer,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
				debtorsmaster.currcode,
				currencies.decimalplaces
			FROM salesorders 
				INNER JOIN salesorderdetails 	
					ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
					ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 0	
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")
				AND salesorders.quotation = 1
				AND salesorders.orddate < '" . $StartDate . "'
			GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";			

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . _('Reminder Bank Transfer Sent On') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$EmailType = "RemindBankTransfer";
			if ($myrow['klemailremindbanktransfer']== '0000-00-00'){
				$EmailLinkText = 'Send now';
				$EmailLink = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
			}else{
				$EmailLink = ConvertSQLDate($myrow['klemailremindbanktransfer']);
			}
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue']+$myrow['freightcost'],$myrow['decimalplaces']),
					$myrow['currcode'], 
					$EmailLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OldPurchasingOrdersStillActive($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT orderno,
				   orddate,
				   supplierno
			FROM purchorders 
			WHERE status NOT IN ('Completed', 'Cancelled', 'Rejected')
			AND orddate <= '". $StartDate ."'
			AND EXISTS (SELECT *
						FROM purchorderdetails
						WHERE purchorderdetails.orderno = purchorders.orderno
						AND completed = 0)
			ORDER BY orderno";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('POs older than ') . $maxdays . _(' days and still not closed') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('PO') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Supplier') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/PO_OrderDetails.php?OrderNo=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					ConvertSQLDate($myrow['orddate']), 
					$myrow['supplierno']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineCustomersNoOrderPlaced($RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

	$SQL = "SELECT 	debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					debtorsmaster.clientsince
			FROM debtorsmaster
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")
				AND debtorsmaster.klemailnowebshoporder = '0000-00-00'
				AND NOT EXISTS (SELECT * 
								FROM salesorders
								WHERE salesorders.debtorno = debtorsmaster.debtorno)
				AND debtorsmaster.debtorno != 'Online Shop'
			ORDER BY debtorsmaster.debtorno";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Online Customers registered but no order placed.') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Country') . '</th>
							<th class="ascending">' . _('Currency ') . '</th>
							<th class="ascending">' . _('Registered on') . '</th>
							<th class="ascending">' . _('Send Email') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . $myrow['debtorno'] . '">' . $myrow['debtorno'] . '</a>';
			$EmailLinkText = 'Send Now';
			$EmailType = 'NoOrderPlaced';
			$EmailLink = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['debtorno'] . '&EmailType=' . $EmailType. '&CustomerOrder=' . $myrow['debtorno'] . '">'. $EmailLinkText .'</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['name'], 
					$myrow['address6'], 
					$myrow['currcode'], 
					ConvertSQLDateTime($myrow['clientsince']), 
					$EmailLink				
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineItemsOnProcess($RootPath, $db){
	
	$SQL = "SELECT salesorders.orderno,	
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				salesorderdetails.stkcode,
				salesorderdetails.quantity AS qtyorder,
				l1.reorderlevel,
				l1.quantity AS qtyready,
				(SELECT SUM(l2.quantity)
					FROM locstock AS l2
					WHERE l1.stockid = l2.stockid
						AND l2.loccode = " . CODE_KANTOR . ") AS qohkantor
			FROM salesorderdetails, salesorders, locstock AS l1, debtorsmaster	
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = l1.stockid
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND salesorders.quotation = 0
				AND salesorders.fromstkloc = ". CODE_ONLINE_SHOP ."
				AND l1.loccode = ". CODE_ONLINE_SHOP ."
				AND salesorderdetails.completed= 0
			ORDER BY salesorders.orderno, salesorderdetails.stkcode";
	$result = DB_query($SQL);
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "Items on process for Online Orders" . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Item Code') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('RL at Toko Online') . '</th>
							<th class="ascending">' . _('QOH Toko Online') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$ItemLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stkcode'] . '">' . $myrow['stkcode'] . '</a>';
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					$ItemLink, 
					locale_number_format($myrow['qtyorder'],0),
					locale_number_format($myrow['reorderlevel'],0),
					locale_number_format($myrow['qtyready'],0),
					locale_number_format($myrow['qohkantor'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineOrdersFollowUp($Source, $numDays, $RootPath, $db){

	$Titletext = "Follow up Outstanding " . $Source. " Online Orders";
	$ThankYouDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));
// 2015-01-14 Prices already NET for online orders
//				(SELECT SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent))
	if ($Source == "LAZADA"){	
		$SQL = "SELECT salesorders.orderno,	
					salesorders.customerref,
					salesorders.klemailpaymentconfirm,
					salesorders.klemailtrackingconfirm,
					salesorders.klemailthankyouorder,
					debtorsmaster.debtorno,
					salesorders.deliverto AS name,
					salesorders.orddate,
					(SELECT SUM(salesorderdetails.unitprice*salesorderdetails.quantity)
							FROM salesorderdetails
							WHERE salesorderdetails.orderno = salesorders.orderno) AS ordervalue,
					salesorders.freightcost,
					debtorsmaster.currcode,
					shippers.shippername,
					debtortrans.consignment,
					currencies.decimalplaces
				FROM salesorders 
					INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortrans 
						ON debtortrans.order_ = salesorders.orderno
					INNER JOIN shippers 
						ON salesorders.shipvia = shippers.shipper_id
					INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
				WHERE debtorsmaster.debtorno = 'LAZADA'
					AND salesorders.quotation = 0
					AND ((salesorders.klemailthankyouorder = '0000-00-00' 
								AND salesorders.klemailtrackingconfirm <= '" . $ThankYouDate . "' 
								AND salesorders.klemailtrackingconfirm != '0000-00-00')
						)
				GROUP BY salesorders.orderno,	
					debtorsmaster.name,
					salesorders.orddate
				ORDER BY salesorders.orderno";			
	}else{
		$SQL = "SELECT salesorders.orderno,	
					salesorders.customerref,
					salesorders.klemailpaymentconfirm,
					salesorders.klemailtrackingconfirm,
					salesorders.klemailthankyouorder,
					debtorsmaster.debtorno,
					salesorders.deliverto AS name,
					salesorders.orddate,
					(SELECT SUM(salesorderdetails.unitprice*salesorderdetails.quantity)
							FROM salesorderdetails
							WHERE salesorderdetails.orderno = salesorders.orderno) AS ordervalue,
					salesorders.freightcost,
					debtorsmaster.currcode,
					shippers.shippername,
					debtortrans.consignment,
					currencies.decimalplaces
				FROM salesorders 
					INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortrans 
						ON debtortrans.order_ = salesorders.orderno
					INNER JOIN shippers 
						ON salesorders.shipvia = shippers.shipper_id
					INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
				WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")
					AND debtorsmaster.debtorno != 'LAZADA'
					AND salesorders.quotation = 0
					AND (	(debtortrans.type = 12 
								AND salesorders.klemailpaymentconfirm = '0000-00-00')
						 OR (debtortrans.type = 10 
								AND salesorders.klemailtrackingconfirm = '0000-00-00')
						 OR (salesorders.klemailthankyouorder = '0000-00-00' 
								AND salesorders.klemailtrackingconfirm <= '" . $ThankYouDate . "' 
								AND salesorders.klemailtrackingconfirm != '0000-00-00')
						)
				GROUP BY salesorders.orderno,	
					debtorsmaster.name,
					salesorders.orddate
				ORDER BY salesorders.orderno";			
	}
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . '#' . '</th>
							<th class="ascending">' . _('webERP Order') . '</th>
							<th class="ascending">' . '#' . $Source . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . _('Payment Confirmation') . '</th>
							<th class="ascending">' . _('Tracking Number') . '</th>
							<th class="ascending">' . _('Tracking Confirmation') . '</th>
							<th colspan="3" class="ascending">' . _('Thank You') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			
			$EmailType3 = "ThankYouOrder";
			$EmailType4 = "NoSendThankYou";
			if ($myrow['klemailthankyouorder']== '0000-00-00'){
				$EmailLinkText3 = 'Send now';
				$EmailLinkText4 = 'Do NOT send';
				$EmailLink3 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType3. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText3 .'</a>';
				$EmailLink4 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType4. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText4 .'</a>';
			}else{
				$EmailLink3 = ConvertSQLDate($myrow['klemailthankyouorder']);
				$EmailLink4 = ConvertSQLDate($myrow['klemailthankyouorder']);
			}

			$EmailType2 = "TrackingConfirmation";
			if ($myrow['klemailtrackingconfirm']== '0000-00-00'){
				$EmailLinkText = 'Send now';
				$EmailLink2 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType2. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
				$EmailLink3 = 'Tracking Confirmation first';
				$EmailLink4 = 'Tracking Confirmation first';
			}else{
				$EmailLink2 = ConvertSQLDate($myrow['klemailtrackingconfirm']);
			}
			
			$EmailType1 = "PaymentConfirmation";
			if ($myrow['klemailpaymentconfirm']== '0000-00-00'){
				$EmailLinkText = 'Send now';
				$EmailLink1 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType1. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
				$EmailLink2 = 'Payment Confirmation first';
				$EmailLink3 = 'Payment Confirmation first';
				$EmailLink4 = 'Payment Confirmation first';
			}else{
				$EmailLink1 = ConvertSQLDate($myrow['klemailpaymentconfirm']);
			}

			if ($Source == "LAZADA"){
				$EmailLink1 = '';
				$EmailLink2 = '';
			}
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					locale_number_format($myrow['customerref']),
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue']+$myrow['freightcost'],$myrow['decimalplaces']),
					$myrow['currcode'], 
					$EmailLink1,
					$myrow['shippername'] . ' ' . $myrow['consignment'],
					$EmailLink2,
					$EmailLink3,
					$EmailLink4
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineQuotationsFollowUp($RootPath, $db){

	$Titletext = "Follow up Outstanding Online Quotations";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				salesorders.klemailremindbanktransfer,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
				debtorsmaster.currcode,
				currencies.decimalplaces
			FROM salesorders 
				INNER JOIN salesorderdetails 	
					ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
					ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 0	
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")
				AND salesorders.quotation = 1
			GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";			

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . _('Reminder Bank Transfer') . '</th>
							<th class="ascending">' . _('Paid by Mandiri') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$EmailType = "RemindBankTransfer";
			if ($myrow['klemailremindbanktransfer']== '0000-00-00'){
				$EmailLinkText = 'Send now';
				$EmailLink = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
			}else{
				$EmailLink = ConvertSQLDate($myrow['klemailremindbanktransfer']);
			}
			$PaymentLinkText = 'Apply Payment';
			$PaymentValue = $myrow['ordervalue']+$myrow['freightcost'];
			$PaymentMandiri = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $myrow['orderno'] . '&Bank=' . ACCOUNT_PTBB_MANDIRI . '&CustomerCode=' . $myrow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
			
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue']+$myrow['freightcost'],$myrow['decimalplaces']),
					$myrow['currcode'], 
					$EmailLink,
					$PaymentMandiri
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OpenCartItemsWithoutPicture($RootPath, $db, $db_oc, $oc_tableprefix){

	$SQL = "SELECT 	" . $oc_tableprefix . "product.model AS stockid
			FROM " . $oc_tableprefix . "product
			WHERE " . $oc_tableprefix . "product.status = 1
			ORDER BY " . $oc_tableprefix . "product.model";
	$result = DB_query_oc($SQL);
	$showHeader = TRUE;

	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			if(!file_exists(ABSOLUTE_PATH_OPENCART_IMAGES .$myrow['stockid'].'.jpg') ) {
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . _('Online Shop Items without picture') . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$k = 0; //row colour counter
					$i = 1;
					$TableHeader = '<tr>
									<th class="ascending">' . '#' . '</th>
									<th class="ascending">' . _('Item Code') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$CodeLink
						);
				$i++;
			}
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function OutstandingOrders($customertype, $ordertype, $RootPath, $db){
	/* Check if there are outstanding orders for retail customers */

	if ($customertype == "Retail"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")";
		$namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Retail";
	}elseif ($customertype == "Consignment"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_CONSIGNMENT . ")";
		$namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Consignment";
	}elseif ($customertype == "Wholesale"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WHOLESALE . ")";
		$namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Wholesale";
	}elseif ($customertype == "Online"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")";
		$namefield = " salesorders.deliverto AS name ";
		$Titletext = "Outstanding Online";
	}else{
		$namefield = " debtorsmaster.name ";
		$whereclause = " ";
		$Titletext = _('Outstanding');
	}
	
	if ($ordertype == "Quotation"){
		$whereclause = $whereclause . " AND salesorders.quotation = 1 ";
		$Titletext = $Titletext . " Quotations";
	}elseif  ($ordertype == "Order"){
		$whereclause = $whereclause . " AND salesorders.quotation = 0 ";
		$Titletext = $Titletext . " Orders";
	}else{
		$Titletext = _(' Orders and Quotations');
	}
	
	$SQL = "SELECT salesorders.orderno,	
				debtorsmaster.debtorno, "
			   . $namefield . ",
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
			FROM salesorders INNER JOIN salesorderdetails 	
				ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
				ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 0	"
			. $whereclause .
			" GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Total Value') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function over_or_below_limit($Request, $Sign, $Limit, $RootPath, $db){
/* EXPLAIN SQL 2014-05-21	*/
	if ($Request == "Items changing price"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klchangingprice != 0";
	}elseif ($Request =="Items moving to 20% discount"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klmovingdiscount20 != 0";
	}elseif ($Request =="Items moving to 50% discount"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klmovingdiscount50 != 0";
	}elseif ($Request =="Items moving to 80% discount"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klmovingdiscount80 != 0";
	}elseif ($Request =="Items changing price or moving category"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klchangingprice != 0
					OR stockmaster.klmovingdiscount20 != 0
					OR stockmaster.klmovingdiscount50 != 0
					OR stockmaster.klmovingdiscount80 != 0";
	}elseif ($Request =="DISC20 Items in AR"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid LIKE 'DISC2%'
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='TOKAR'";
	}elseif ($Request =="DISC80 Items in AR"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid LIKE 'DISC8%'
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='TOKAR'";
	}
	
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	if ($Sign == "OVER"){
		if ($myrow[0] > $Limit){
			$text = $Request . " is OVER the maximum. Current value = " . locale_number_format($myrow[0],0) . " Maximum = " . locale_number_format($Limit,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
	}
	if ($Sign == "BELOW"){
		if ($myrow[0] < $Limit){
			$text = $Request . " is BELOW the minimum. Current value = " . locale_number_format($myrow[0],0) . " Minimum = " . locale_number_format($Limit,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
	}
}

function OvestockAtSamples($maxallowedsamples, $RootPath, $db){

	$SQL = "SELECT locstock.stockid, 
					stockmaster.description, 
					quantity AS qty
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND loccode = 'SAMPR'
				AND quantity > '". $maxallowedsamples."'
			ORDER BY locstock.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Overstock of samples') . '</strong></p>';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Qty of samples') . '</th>
						</tr>';
		echo '<div>';
		echo '<table class="selection">';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
		printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qty'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PackagingItemsOnWrongLocation($RootPath, $db){
/* EXPLAIN SQL	2014-05-20

id	select_type	table	type	possible_keys	key	key_len	ref	rows	Extra
1	SIMPLE	stockmaster	ref	PRIMARY,CategoryID,StockID	CategoryID	20	const	10	Using where
1	SIMPLE	locstock	ref	PRIMARY,StockID	StockID	62	kurakura_kl_erp.stockmaster.stockid	14	Using where

*/	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock, locations
			WHERE stockmaster.stockid = locstock.stockid
				AND locstock.loccode = locations.loccode
				AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				AND locations.typeloc NOT IN " . BALI_SHOPS_LIST_BY_TYPE . "
				AND locstock.loccode NOT IN " . LIST_GUDANG_FOR_PACKAGING . "
				AND ( locstock.quantity > 0 OR locstock.reorderlevel > 0 )
			ORDER BY stockmaster.stockid";

			$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>Packaging items in wrong locations (must be transferred to another location)</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('RL') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['loccode'], 
					$myrow['quantity'],
					$myrow['reorderlevel']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PettyCashBalance($TypeUser, $db){

	if ($TypeUser == 'Authorizer'){
		$WhereUser = "AND pctabs.authorizer LIKE '%" . $_SESSION['UserID'] . "%'";
	}elseif($TypeUser == 'User'){
		$WhereUser = "AND pctabs.usercode = '". $_SESSION['UserID'] ."'";
	}else{
		$WhereUser = "";
	}

	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount,
				pctabs.currency
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	".
			$WhereUser . "
			GROUP BY pcashdetails.tabcode, pctabs.tablimit
			HAVING ( SUM(pcashdetails.amount) < -0.01
					OR SUM(pcashdetails.amount) > pctabs.tablimit)";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		
		if ($TypeUser == "Authorizer"){
			echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Accounts you AUTHORIZE with balance too Low or Too High') . '</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Balance you USE with balance too Low or Too High') . '</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('PC Tab Code') . '</th>
							<th class="ascending">' . _('Amount') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$myrow['tabcode'], 
					locale_number_format($myrow['amount'],0),
					$myrow['currency']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PettyCashToBeAuthorized($db){

	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount,
				pctabs.currency
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pcashdetails.authorized = '0000-00-00'
				AND pctabs.authorizer LIKE '%" . $_SESSION['UserID'] . "%'
			GROUP BY pcashdetails.tabcode";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Expenses to be Authorized') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('PC Tab Code') . '</th>
							<th class="ascending">' . _('Amount') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$myrow['tabcode'], 
					locale_number_format($myrow['amount'],0),
					$myrow['currency']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function RegularTransfersToShopNotReceived($PreparationTime, $LimitTime, $RootPath, $db){

	$StartDate = Date('Y-m-d');
	$StartTime = Date('H:i:s');

	if ($StartTime >= $LimitTime){
		$SQL = "SELECT DISTINCT loctransfers.reference,
						loctransfers.shipdate,
						loctransfers.shiploc,
						loctransfers.recloc
				FROM loctransfers,locations
				WHERE  loctransfers.recloc = locations.loccode
					AND loctransfers.recqty < loctransfers.shipqty
					AND loctransfers.shipdate <= '". $StartDate ." " . $PreparationTime . "'
					AND   (locations.typeloc = 'SHOPKL'
						OR locations.typeloc = 'SHOPBL'
						OR locations.typeloc = 'SHOPOU'
						OR locations.typeloc = 'ONLINE')
				ORDER BY loctransfers.reference";
		$result = DB_query($SQL);

		if (DB_num_rows($result) != 0){
			echo '<p class="page_title_text" align="center"><strong>' . 'Transfers to Shops prepared before ' . Date($_SESSION['DefaultDateFormat']) . 
																		' at ' . $PreparationTime . ' but not received by SPG before ' . $LimitTime . '</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Transfer') . '</th>
								<th class="ascending">' . _('Date') . '</th>
								<th class="ascending">' . _('From') . '</th>
								<th class="ascending">' . _('To') . '</th>
							</tr>';
			echo $TableHeader;
			$k = 0; //row colour counter
			$i = 1;
			while ($myrow = DB_fetch_array($result)) {
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						ConvertSQLDateTime($myrow['shipdate']), 
						$myrow['shiploc'], 
						$myrow['recloc'] 
						);
				$i++;
			}
			echo '</table>
					</div>';
		}
	}
}

function SamplesNotLongerNeeded($RootPath, $db){

	$SQL = "SELECT locstock.stockid, 
					stockmaster.description, 
					quantity AS qty
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND loccode = 'SAMPR'
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ." 
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET .")
				AND quantity > 0
			ORDER BY locstock.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Samples Not Longer Needed (No More Buy, Discount, Outlet)') . '</strong></p>';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Qty of samples') . '</th>
						</tr>';
		echo '<div>';
		echo '<table class="selection">';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
		printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qty'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function SPGNotReportingSalesInDays($maxdays, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));

	$SQL = "SELECT salesman.salesmancode,
				salesman.salesmanname,
				www_users.defaultlocation,
				(SELECT orddate
					FROM salesorders
					WHERE salesorders.salesperson = salesman.salesmancode
					ORDER BY orddate DESC
					LIMIT 1) AS lastsale
		FROM salesman, www_users
		WHERE www_users.salesman = salesman.salesmancode
			AND salesman.current = 1	
			AND salesman.salesmancode != '999'
			AND www_users.fullaccess = '17'
			AND www_users.blocked = 0
			AND NOT EXISTS (SELECT *
							FROM salesorders
							WHERE orddate >= '". $StartDate. "'
								AND salesorders.salesperson = salesman.salesmancode)
		ORDER BY salesman.salesmancode";
//	prnMsg($SQL);			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Senior or Support SPG with more than ') . $maxdays . _(' days not reporting ANY sales.') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' .  _('SPG') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Last Sale') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if (isset($myrow['lastsale'])){
				$Day = ConvertSQLDate($myrow['lastsale']);
			}else{
				$Day = "No sale yet";
			}
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['salesmancode'],
					$myrow['salesmanname'],
					$myrow['defaultlocation'],
					$Day
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

function SuppliersWithoutBasicData($RootPath, $db){

	$SQL = "SELECT supplierid,
					suppname
			FROM suppliers
			WHERE address6 = ''";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Suppliers without basic data') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Name') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['supplierid'], 
					$myrow['suppname'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function TransferWithWrongInformation($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays+1));
	$SQL = "SELECT loctransferid, 
					reference,
					stockid,
					recdate,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = shiploc) AS locfrom,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = recloc) AS locto,
					shipqty AS shippedqty,
					recqty AS receivedqty
			FROM loctransfers
			WHERE  shipdate >= '" . $StartDate . "'
				AND recdate > '2000'
				AND shipqty != recqty
			ORDER BY recdate ASC, reference ASC, stockid ASC";
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Transfers With Wrong Information during the last ') . $maxdays  . ' days</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Reception Date') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Item') . '</th>
							<th class="ascending">' . _('Shipped Qty') . '</th>
							<th class="ascending">' . _('Received Qty') . '</th>
							<th class="ascending">' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;
		$resultTx = DB_Txn_Begin();
		$k = 0; //row colour counter
		$LastStockid = "";
		$LastTransfer = "";
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			if (($myrow['stockid'] != $LastStockid) OR ($myrow['reference'] != $LastTransfer)){
				$sql = "UPDATE loctransfers SET shipqty = recqty 
						WHERE loctransferid = '".$myrow['loctransferid'] . "'";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('Unable to fix the wrong information');
				$ResultFix = DB_query($sql, $ErrMsg, $DbgMsg, true);
				$Action = "Fixed"; 
			}else{
				$sql = "DELETE FROM loctransfers 
						WHERE loctransferid = '".$myrow['loctransferid'] . "'";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('Unable to delete the wrong information');
				$ResultDelete = DB_query($sql, $ErrMsg, $DbgMsg, true);
				$Action = "Deleted";
			}
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					locale_number_format($myrow['loctransferid'],0), 
					ConvertSQLDateTime($myrow['recdate']), 
					$CodeLink, 
					$myrow['locfrom'], 
					$myrow['locto'], 
					$myrow['stockid'], 
					locale_number_format($myrow['shippedqty'],0),
					locale_number_format($myrow['receivedqty'],0),
					$Action
					);
			$LastStockid = $myrow['stockid'];
			$LastTransfer = $myrow['reference'];
		}
		$resultTx = DB_Txn_Commit();
		echo '</table>
				</div>
				</form>';
	}
}

function UsersNotLoggingIn($maxdays, $type, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays-1)) ;
	
	if ($type=='SPGSUPPORT'){
		$WhereType = " AND fullaccess = 22";
	}else{
		$WhereType = " AND fullaccess != 22";
	}
	
	$SQL = "SELECT userid,
				realname,
				lastvisitdate
			FROM www_users
			WHERE lastvisitdate IS NOT NULL
				AND DATE(lastvisitdate) < '" . $StartDate . "'
				AND userid NOT LIKE '999%'
				AND userid <> 'TestUser'" . $WhereType;
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($type=='SPGSUPPORT'){
			echo '<p class="page_title_text" align="center"><strong>' . _('SPG Support webERP users not logging in for more than ') . $maxdays . _(' days.') .'</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Regular webERP users not logging in for more than ') . $maxdays . _(' days.') .'</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' .  _('User ID') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Last Login') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['userid'],
					$myrow['realname'],
					ConvertSQLDate($myrow['lastvisitdate'])
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

function ValueStockLocation($location, $minpcs, $maxpcs, $minvalue, $maxvalue, $db){
/*	$minpcs = $optimalpcs * (1 - $varpcs);
	$maxpcs = $optimalpcs * (1 + $varpcs);
	$minvalue = $optimalvalue * (1 - $varvalue);
	$maxvalue = $optimalvalue * (1 + $varvalue);
*/	
	$SQL = "SELECT 
				locations.locationname,
				SUM(locstock.quantity) AS qtyonhand,
				SUM(locstock.quantity *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost)) AS valuetotal
			FROM stockmaster,
				stockcategory,
				locations,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
				AND locations.loccode = '" . $location . "'
				AND stockmaster.categoryid=stockcategory.categoryid
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES . "
				AND locstock.quantity!=0
				AND locstock.loccode = '" . $location . "'";
				
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	if ($myrow['qtyonhand'] < $minpcs){
		$text = "Number of items at " . $myrow['locationname'] . " is BELOW the minimum. QOH = " . locale_number_format($myrow['qtyonhand'],0) . " pcs. Minimum = " . locale_number_format($minpcs,0) . " pcs";
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
	if ($myrow['qtyonhand'] > $maxpcs){
		$text = "Number of items at " . $myrow['locationname'] . " is OVER the maximum. QOH = " . locale_number_format($myrow['qtyonhand'],0) . " pcs. Maximum = " . locale_number_format($maxpcs,0) . " pcs";
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function WrongItemsOnPurchaseOrders($RootPath, $db){
/* EXPLAIN SQL 2014-05-30 */
	$SQL = "SELECT purchorderdetails.orderno,
				purchorderdetails.itemcode,
				stockmaster.description,
				purchorderdetails.quantityord
			FROM purchorderdetails, purchorders, stockmaster
			WHERE stockmaster.stockid = purchorderdetails.itemcode
				AND purchorderdetails.orderno = purchorders.orderno
				AND purchorderdetails.completed = 0
				AND purchorders.status NOT IN ('Cancelled', 'Rejected')
				AND (  stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ."
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."
					OR stockmaster.discontinued = 1)
			ORDER BY purchorderdetails.orderno,
					purchorderdetails.itemcode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if (TRUE){
				if ($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' .'Wrong items (No More Purchasing, Discount or Obsolete) in Active POs' . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('PO') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('QOO') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $myrow['itemcode'] . '">' . $myrow['itemcode'] . '</a>';
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						locale_number_format($myrow['orderno'],0),
						$CodeLink, 
						$myrow['description'],
						locale_number_format($myrow['quantityord'],0)
						);
				$i++;
			}
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function WrongItemsOnWorkOrders($RootPath, $db){
/* EXPLAIN SQL 2014-05-30 */
	$SQL = "SELECT workorders.wo,
				woitems.stockid,
				stockmaster.description,
				woitems.qtyreqd
			FROM woitems, workorders, stockmaster
			WHERE stockmaster.stockid = woitems.stockid
				AND woitems.wo = workorders.wo
				AND workorders.closed = 0
				AND (  stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ."
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."
					OR stockmaster.discontinued = 1)
			ORDER BY woitems.wo,
					woitems.stockid";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if (TRUE){
				if ($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' .'Wrong items (No More Purchasing, Discount or Obsolete) in Active Work Orders' . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('WO') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Qty') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						locale_number_format($myrow['wo'],0),
						$myrow['stockid'],
						$myrow['description'],
						locale_number_format($myrow['qtyreqd'],0)
						);
				$i++;
			}
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function StockToPTADU($Kind, $FactorNearStock, $LimitToMove, $RootPath, $db){
	
	if($Kind == "PO"){
		$SQL = "SELECT purchorderdetails.itemcode,
					stockmaster.categoryid,
					(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS stdcost,
					SUM(purchorderdetails.quantityrecd) AS qtyreceivedptadu,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = purchorderdetails.itemcode) AS qoh,
					(SELECT SUM(locstock.quantity)
						FROM locstock,locations
						WHERE locstock.stockid = stockmaster.stockid
							AND locstock.loccode = locations.loccode
							AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . ") AS qohshops
				FROM purchorderdetails, stockmaster
				WHERE purchorderdetails.itemcode = stockmaster.stockid
					AND stockmaster.categoryid IN ('STABKL','STABBL','STABGE','NOPOKL','NOPOBL','NOPOGE','DISC20','DISC50','DISC80','COMPON')
					AND purchorderdetails.orderno >= 2808
					AND purchorderdetails.orderno != 2811
					AND purchorderdetails.orderno != 2816
					AND purchorderdetails.orderno != 2819
				GROUP BY purchorderdetails.itemcode
				HAVING qtyreceivedptadu * " . $FactorNearStock. " >= qoh 
					AND qtyreceivedptadu > 0
				ORDER BY purchorderdetails.itemcode";
	}elseif($Kind == "WO"){
		$SQL = "SELECT woitems.stockid AS itemcode,
					stockmaster.categoryid,
					(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS stdcost,
					SUM(woitems.qtyrecd) AS qtyreceivedptadu,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = woitems.stockid) AS qoh,
					(SELECT SUM(locstock.quantity)
						FROM locstock,locations
						WHERE locstock.stockid = woitems.stockid
							AND locstock.loccode = locations.loccode
							AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . ") AS qohshops
				FROM workorders, woitems, stockmaster
				WHERE woitems.stockid = stockmaster.stockid
					AND stockmaster.categoryid IN ('STABKL','STABBL','STABGE','NOPOKL','NOPOBL','NOPOGE','DISC20','DISC50','DISC80','COMPON')
					AND workorders.wo = woitems.wo
					AND workorders.closed = 1
					AND (workorders.wo > 3614 
						OR workorders.wo = 3576
						OR workorders.wo = 3577
						OR workorders.wo = 3578
						OR workorders.wo = 3579
						OR workorders.wo = 3580
						OR workorders.wo = 3581
						OR workorders.wo = 3582
						OR workorders.wo = 3583
						OR workorders.wo = 3584
						OR workorders.wo = 3591
						OR workorders.wo = 3592
						OR workorders.wo = 3594
						OR workorders.wo = 3599
						OR workorders.wo = 3600
						OR workorders.wo = 3601
						OR workorders.wo = 3606
						OR workorders.wo = 3613
						)
					AND workorders.wo != 3617
					AND workorders.wo != 3623
					AND workorders.wo != 3651
					AND workorders.wo != 3652
					AND workorders.wo != 3653
					AND workorders.wo != 3654
					AND workorders.wo != 3655
					AND workorders.wo != 3661
					AND workorders.wo != 3662
					AND workorders.wo != 3664
					AND workorders.wo != 3673
					AND workorders.wo != 3674
					AND workorders.wo != 3687
					AND workorders.wo != 3691
					AND workorders.wo != 3693
					AND workorders.wo != 3694
					AND workorders.wo != 3695
				GROUP BY woitems.stockid
				HAVING qtyreceivedptadu * " . $FactorNearStock. " >= qoh
					AND qtyreceivedptadu > 0
				ORDER BY woitems.stockid";
	}
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Kind . ' items to move to PTADU stock categories with QOH ADU >= ' . $FactorNearStock . ' * QOH Total </strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . '#' . '</th>
							<th class="ascending">' . 'Stock ID' . '</th>
							<th class="ascending">' . 'Std Cost' . '</th>
							<th class="ascending">' . 'Category BB' . '</th>
							<th class="ascending">' . 'Category ADU' . '</th>
							<th class="ascending">' . 'QOH Total' . '</th>
							<th class="ascending">' . 'QOH ADU' . '</th>
							<th class="ascending">' . 'QOH BB' . '</th>
							<th class="ascending">' . 'Value ADU' . '</th>
							<th class="ascending">' . 'Value BB' . '</th>
							<th class="ascending">' . 'Action' . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$TotalPcsADU = 0;
		$TotalPcsBB = 0;
		$TotalValueADU = 0;
		$TotalValueBB = 0;
		
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['itemcode'] . '">' . $myrow['itemcode'] . '</a>';

			if ($myrow['categoryid'] == "STABKL"){
				$NewCategory = "STABKA";
			}elseif ($myrow['categoryid'] == "STABBL"){
				$NewCategory = "STABBA";
			}elseif ($myrow['categoryid'] == "STABGE"){
				$NewCategory = "STABGA";
			}elseif ($myrow['categoryid'] == "NOPOKL"){
				$NewCategory = "NOPOKA";
			}elseif ($myrow['categoryid'] == "NOPOBL"){
				$NewCategory = "NOPOBA";
			}elseif ($myrow['categoryid'] == "NOPOGE"){
				$NewCategory = "NOPOGA";
			}elseif ($myrow['categoryid'] == "DISC20"){
				$NewCategory = "DISC2A";
			}elseif ($myrow['categoryid'] == "DISC50"){
				$NewCategory = "DISC5A";
			}elseif ($myrow['categoryid'] == "DISC80"){
				$NewCategory = "DISC8A";
			}elseif ($myrow['categoryid'] == "COMPON"){
				$NewCategory = "COMPOA";
			}
			
			// if there is some ADU qty at shops (so, stock at shops more than BB stock) OR no BB qty left
			if (($myrow['qoh']-$myrow['qtyreceivedptadu']) <= $LimitToMove){
				$Action = '<a href="' . $RootPath . '/KLUpdateStockCategory.php?StockId=' . $myrow['itemcode'] . '&OldCat=' . $myrow['categoryid'] . '&NewCat=' . $NewCategory .'">' . 'Change Category' . '</a>';
			}else{
				$Action = '';
			}
			$TotalPcsADU += $myrow['qtyreceivedptadu'];
			$TotalPcsBB += ($myrow['qoh']-$myrow['qtyreceivedptadu']);
			$TotalValueADU += $myrow['qtyreceivedptadu']*$myrow['stdcost'];
			$TotalValueBB += ($myrow['qoh']-$myrow['qtyreceivedptadu'])*$myrow['stdcost'];
		
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					locale_number_format_zero_blank($i,0),
					$CodeLink,
					locale_number_format_zero_blank($myrow['stdcost'],0),
					$myrow['categoryid'],
					$NewCategory,
					locale_number_format_zero_blank($myrow['qoh'],0),
					locale_number_format_zero_blank($myrow['qtyreceivedptadu'],0),
					locale_number_format_zero_blank($myrow['qoh']-$myrow['qtyreceivedptadu'],0),
					locale_number_format_zero_blank($myrow['qtyreceivedptadu']*$myrow['stdcost'],0),
					locale_number_format_zero_blank(($myrow['qoh']-$myrow['qtyreceivedptadu'])*$myrow['stdcost'],0),
					$Action
					);
			$i++;
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>', 
				'',
				'TOTAL',
				'',
				'',
				'',
				'',
				locale_number_format_zero_blank($TotalPcsADU,0),
				locale_number_format_zero_blank($TotalPcsBB,0),
				locale_number_format_zero_blank($TotalValueADU,0),
				locale_number_format_zero_blank($TotalValueBB,0),
				''
				);
		echo '</table>
			</div>
			</form>';
	}
}

?>
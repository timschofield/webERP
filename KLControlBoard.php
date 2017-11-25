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
	// WARNINGS STILL NOT DOCUMENTED ON WIKI
//	prnMsg("START OF PENDING FOR KL INTRANET ",'success');
//	prnMsg("END OF PENDING FOR KL INTRANET ",'success');
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
		WrongStandardCost("India", "", STANDARD_COST_FACTOR_FOREIGN, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	/***************************************************************************************
	* RETAIL PRICE         
	***************************************************************************************/

	if ($KL_BusinessDevelopmentManager
		OR $KL_SystemAdmin){
		
		over_or_below_limit("DISC20 Items in AR", "BELOW", 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("DISC80 Items in AR", "BELOW", 20, $RootPath, $db);
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
		DiscountedItemsWithWrongDiscount("DISC50", "50", $RootPath, $db);
		$NumberOfTestExecuted++;
		NotDiscountedItemsWithDiscount($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager 
		OR $KL_ShopSupportLeader){
		ErrorsInTransfers( 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		ErrorsInTransfers(30, $RootPath, $db);
		$NumberOfTestExecuted++;
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
	}
	
	if ($KL_AdministrationTeam){
		// Bank Mandiri or  BCA has enough funds to be transferred to Danamon
		BalanceAccountControl("111121100PT",  1000000,   50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121101PT",  1000000,  100000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110PT",   100000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121111PT", 11000000,  110000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin){
		BalanceAccountControl("111121111PT", 11000000,  110000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121105PT",500000000, 1500000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111200",   50000000,  100000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111202010",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111209",          0,   25000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111131100",         -1,  500000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111520000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111512000",   50000000,  200000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111513000",   -5000000,    5000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111800000",  200000000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111900000",   25000000,   50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111311100",  -20000000,          0, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111499000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021400", -100000000,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021500",  500000000, 1000000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612011215",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612012015",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612012016",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* STOCK CONTROL         
	***************************************************************************************/

	if ($KL_SystemAdmin){

//		ItemsNeedingAutomaticTranslation($RootPath, $db);
//		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingTeam
		OR $KL_BusinessDevelopmentManager){
		
//		ItemsNeedingTranslationRevision($RootPath, $db);
//		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){
		ItemsinSetUp("ReadyToTest", "SETKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("ReadyToTest", "SETBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("ReadyToTest", "SETGE", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("NeedDescription", "SETKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("NeedDescription", "SETBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("NeedDescription", "SETGE", $RootPath, $db);
		$NumberOfTestExecuted++;
		//ItemsinSetUp("NeedPrice", $RootPath, $db);
		//$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETKL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETBL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETGE", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("WithReorderLevel", "SETKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("WithReorderLevel", "SETBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("WithReorderLevel", "SETGE", $RootPath, $db);
		$NumberOfTestExecuted++;
		ObsoleteComponentsInActiveBOM($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager){

		GoodsJustArrived("PO", "KANTO", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustArrived("WO", "KANTO", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustArrived("WO", "SUPBA", 3, $RootPath, $db);
		$NumberOfTestExecuted++;

		GoodsJustTransferred("SAMPR", "KANTO", 2, 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SASPG", "KANTO", 2, 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERSU", "KANTO", 2, 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERSW", "KANTO", 2, 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERDE", "KANTO", 2, 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERVI", "KANTO", 2, 50, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager){
		ItemsWithStockKantorButReorderLevelTokoZero($RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("TESTKL", "TOKPU", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKKA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKSU", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKSS", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKPA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKSA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOK66", 10, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("STABKL", "TOKPU", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKKA", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKSU", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKSS", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKPA", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKSA", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOK66", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKSE", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKKS", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKOB", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKM2", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKU2", 15, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("NOPOKL", "TOKPU", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKKA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKSU", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKSS", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKPA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKSA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOK66", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKSE", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
// Do not report OB Requested by laia 2017-08-28
//		CategoryItemsNotInShop("NOPOKL", "TOKOB", 10, $RootPath, $db);
//		$NumberOfTestExecuted++;

		
		CategoryItemsNotInShop("TESTBL", "TOKBU", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTBL", "TOKPS", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTBL", "TOKSB", 6, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("STABBL", "TOKBU", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKPS", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKSB", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKPB", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKMU", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKU3", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKBB", 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKTB", 15, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("NOPOBL", "TOKBU", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOBL", "TOKPS", 6, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOBL", "TOKSB", 6, $RootPath, $db);
		$NumberOfTestExecuted++;


		CategoryItemsNotInShop("DISC20", "TOKAR", 1, $RootPath, $db);
		$NumberOfTestExecuted++;
		
		CategoryItemsNotInShop("DISC50", "TOKAR", 1, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("DISC80", "TOKAR", 1, $RootPath, $db);
		$NumberOfTestExecuted++;

	}


	if ($KL_OperationalManager 
		OR $KL_PurchasingTeam){

		ConsumablesGoodsNotEnoughStock(50, 25, 75, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){

		ValueStockLocation("SERVI",    0,  150, 0, 0, $db);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERDE",    0,  150, 0, 0, $db);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERSU",    0,  300, 0, 0, $db);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERSW",    0,  300, 0, 0, $db);
		$NumberOfTestExecuted++;
		OvestockAtSamples(1, $RootPath, $db);
		$NumberOfTestExecuted++;
		SamplesNotLongerNeeded($RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "ONLYDISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "ALL", $RootPath, $db);
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

	if ($KL_SystemAdmin){
		KapalLautPackagingToBeRefilled(false, $RootPath, $db);
		$NumberOfTestExecuted++;
		BlinkPackagingToBeRefilled(false, $RootPath, $db);
		$NumberOfTestExecuted++;
		OutletPackagingToBeRefilled(false, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

/*	if ($KL_SystemAdmin){
		InsuficientStockForShopPackaging('SHPACK', 21, 95, 30, false, $RootPath, $db); // Works for both regular and outlet shop packaging
		$NumberOfTestExecuted++;
	}
*/
	if ($KL_PurchasingTeam){
		InsuficientStockForShopPackaging('SHPACK', 21, 95, 30, true, $RootPath, $db); // Works for both regular and outlet shop packaging
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

	/*	GoodSellingItemsInCategory("TESTKL", 15, 6, $RootPath, $db);
		GoodSellingItemsInCategory("TESTBL", 15, 6, $RootPath, $db);

		GoodSellingItemsInCategory("NOPOKL", 15, 6, $RootPath, $db);
		GoodSellingItemsInCategory("NOPOBL", 15, 6, $RootPath, $db);
	*/
		ItemsInCategoryForMoreThanDays( 120, "SETKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "SETBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "SETGE", $RootPath, $db);
		$NumberOfTestExecuted++;


		ActiveItemsNoSales( 30, "TESTKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTKL", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "TESTBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTBL", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "TESTGE", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTGE", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "STABKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABGE", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOKL", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOBL", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOGE", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOGE", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "DISC20", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "DISC20", $RootPath, $db);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 60, "DISC50", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 365, "DISC50", $RootPath, $db);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 90, "DISC80", $RootPath, $db);
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

	if ($KL_OperationalManager
		OR $KL_ShopSupportLeader){
	//	WrongGiftItem("ONLINE-VIP-PACK", "Retail", "OVER",  500000, 1, $RootPath, $db);
	//	WrongGiftItem("ONLINE-VIP-PACK", "Retail", "BELOW", 500000, 1, $RootPath, $db);
	//	WrongGiftItem("GIFT-ALAR01", "Retail", "OVER",  1000000, 3, $RootPath, $db);
	//	WrongGiftItem("GIFT-ALAR01", "Retail", "BELOW", 1000000, 3, $RootPath, $db);
	}

	if ($KL_SystemAdmin 
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
	//	NewCustomers(2, $RootPath, $db);
	//	$NumberOfTestExecuted++;
		OnlineCustomersNoOrderPlaced($RootPath, $db);
		$NumberOfTestExecuted++;
		OnlineQuotationsFollowUp($RootPath, $db);
		$NumberOfTestExecuted++;
		OldOnlineQuotations(30, $RootPath, $db);
		$NumberOfTestExecuted++;
	//	OutstandingOrders("Online", "Quotation", $RootPath, $db);
	//	$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_ShopManagerOnline
		OR $KL_OperationalManager
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("Online", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
		OnlineItemsOnProcess($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
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
}
prnMsg("Performed ". $NumberOfTestExecuted . " control tests",'success');

time_finish($begintime);

include ('includes/footer.php');


/********************************************************************************************
FUNCTIONS ONLY USED IN CONTROL BOARD
*********************************************************************************************/

function CategoryItemsNotInShop($Category, $Shop, $MinQOH, $RootPath, $db){
	
	$Message = $Category . _(' items NOT in ') . $Shop . ' with QOH >= ' . $MinQOH .' (excluding Change of Price, Move to Discount, Service, Shop online and Return to Supplier)';
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SAMPLE_LOCATIONS . ") AS qoh,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.categoryid = '" . $Category . "'
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['qoh'], 
					$CodeLinkRL 
					);
			$i++;
		}
		echo '</table>
				</div>';
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
			WHERE categoryid IN " . CATEGORIES_AVAILABLE_WEBSITE ."
				AND discontinued = 0
				AND stockid NOT LIKE '%-D'
				AND stockid != 'WKPC01'
				AND stockid NOT LIKE 'KLBE%'
				AND stockid NOT LIKE 'GOTA%'
				AND stockid NOT LIKE 'TM-%'
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

?>
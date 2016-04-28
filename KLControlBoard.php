<?php
define("VERSIONFILE", "4.01");

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include('includes/session.inc');
$Title = _('Kapal-Laut General Control Board '. VERSIONFILE);
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');

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
prnMsg("START OF PENDING FOR KL INTRANET ",'success');
		SuppliersWithoutBasicData($RootPath, $db);
		ItemsWithoutStandardCost($RootPath, $db);
		over_or_below_limit("Items changing price or moving category", "OVER", 50, $RootPath, $db);
		over_or_below_limit("Items changing price", "OVER", 20, $RootPath, $db);
		over_or_below_limit("Items moving to 20% discount", "OVER", 20, $RootPath, $db);
		over_or_below_limit("Items moving to 50% discount", "OVER", 20, $RootPath, $db);
		over_or_below_limit("Items moving to 80% discount", "OVER", 20, $RootPath, $db);
		DiscountedItemsOnNotOutletShops("DISC20", $RootPath, $db);
		DiscountedItemsOnNotOutletShops("DISC50", $RootPath, $db);
		DiscountedItemsOnNotOutletShops("DISC80", $RootPath, $db);
		NotDiscountedItemsOnOutLetShops($RootPath, $db);
		DiscountedItemsWithWrongDiscount("DISC20", "20", $RootPath, $db);
		DiscountedItemsWithWrongDiscount("DISC50", "50", $RootPath, $db);
		NotDiscountedItemsWithDiscount($RootPath, $db);
		GoodsReceivedNotInvoicedControl(1000000, $periodnow, $db);
		CustomersDebtControl(100000, $periodnow, $db);
		ObsoleteComponentsInActiveBOM($RootPath, $db);
		OvestockAtSamples(1, $RootPath, $db);
		SamplesNotLongerNeeded($RootPath, $db);
		FlaggedAsObsoleteButStockAvailable($RootPath, $db);
		ItemsInKLProcessAndRLNotZero($RootPath, $db);
		OldPurchasingOrdersStillActive(90, $RootPath, $db);

prnMsg("END OF PENDING FOR KL INTRANET ",'success');

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
	OR $KL_PettyCash 
	OR $KL_SPG 
	OR $KL_SPGSupport){

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
		SplittedPaymentsBySPG(15, 2, $db);
		$NumberOfTestExecuted++;
	}

	/*
	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		SPGBelowMinimumSales("TOK66", 2, 1300000,$db);
		SPGBelowMinimumSales("TOKSA", 2, 1650000,$db);
		SPGBelowMinimumSales("TOKKS", 2, 2000000,$db);
		SPGBelowMinimumSales("TOKLE", 2, 1500000,$db);
		SPGBelowMinimumSales("TOKJC", 2, 1300000,$db);
		SPGBelowMinimumSales("TOKUB", 2, 1300000,$db);
		SPGBelowMinimumSales("TOKMF", 2, 1500000,$db);
		SPGBelowMinimumSales("TOKBW", 2, 1500000,$db);
		SPGBelowMinimumSales("TOKSE", 2, 1300000,$db);
	}
	*/

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
		WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_THAILAND, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_CHINA, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_HONG_KONG, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Catalonia"  , "", STANDARD_COST_FACTOR_CATALONIA, 0.10, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Philippines", "", STANDARD_COST_FACTOR_PHILIPPINES, 0.04, "SHOWLINK", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingTeam) {
		WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_THAILAND, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_CHINA, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_HONG_KONG, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Catalonia"  , "", STANDARD_COST_FACTOR_CATALONIA, 0.10, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongStandardCost("Philippines", "", STANDARD_COST_FACTOR_PHILIPPINES, 0.05, "SHOWONLY", $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	/***************************************************************************************
	* RETAIL PRICE         
	***************************************************************************************/

	/*
	if (($KL_SystemAdmin)){
		over_or_below_limit("Items changing price", "OVER", 0, $RootPath, $db);
		over_or_below_limit("Items moving to 20% discount", "OVER", 0, $RootPath, $db);
		over_or_below_limit("Items moving to 50% discount", "OVER", 0, $RootPath, $db);
		over_or_below_limit("Items moving to 80% discount", "OVER", 0, $RootPath, $db);
	}
	*/

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
		
		DiscountedItemsOnNotOutletShops("DISC20", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsOnNotOutletShops("DISC50", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsOnNotOutletShops("DISC80", $RootPath, $db);
		$NumberOfTestExecuted++;
		NotDiscountedItemsOnOutLetShops($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportLeader){	

		DiscountedItemsWithWrongDiscount("DISC20", "20", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC50", "50", $RootPath, $db);
		$NumberOfTestExecuted++;
	//	DiscountedItemsWithWrongDiscount("DISC80", "80", $RootPath, $db);
	//	$NumberOfTestExecuted++;
		NotDiscountedItemsWithDiscount($RootPath, $db);
		$NumberOfTestExecuted++;
	}


	/***************************************************************************************
	* BALANCE ACCOUNTS         
	***************************************************************************************/
	if ($KL_SystemAdmin){
		GoodsReceivedNotInvoicedControl(1000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		CustomersDebtControl(100000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_AdministrationTeam){
		
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
	}

	if ($KL_SystemAdmin 
		OR $KL_PurchasingTeam
		OR $KL_AdministrationTeam){

		BalanceAccountControl("111111100",          -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
	}


	if ($KL_SystemAdmin 
		OR $KL_AdministrationTeam){
		// Bank Mandiri or  BCA has enough funds to be transferred to Danamon
		BalanceAccountControl("111121100PT",  1000000,   50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121101PT",  1000000,  100000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110PT",   100000,  100000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121111PT", 11000000,  110000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){

		BalanceAccountControl("111121105PT",250000000,  500000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111200",   30000000,   50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111209",          0,   15000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111131100",         -1,  400000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111520000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111512000",   50000000,  150000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111513000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111800000",  150000000,  250000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111900000",   15000000,   25000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111311100",   -1000000,    1000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111499000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021400", -300000000,          0, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021500",  100000000,  800000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612011215",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612012015",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* STOCK CONTROL         
	***************************************************************************************/

	if ($KL_SystemAdmin){
		
		ItemsNeedingAutomaticTranslation($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingTeam
		OR $KL_BusinessDevelopmentManager){
		
		ItemsNeedingTranslationRevision($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	/*
	if ($KL_BusinessDevelopmentManager
		OR $KL_OperationalManager
		OR $KL_ShopSupportTeam){
		ItemsWithStockLocationButNoStockAvailable("WABOM", "WaterBom", 15, 600, $RootPath, $db);
		ItemsWithStockLocationButNoStockAvailable("WHAYA", "Ayana", 15, 600, $RootPath, $db);
		ItemsWithStockLocationButNoStockAvailable("WHINT", "InterContinental", 15, 600, $RootPath, $db);
		InsuficientStockForItems("STABKL", "TM-", "Tali Mie", 20, 40, $RootPath, $db);
	}
	*/

	if ($KL_BusinessDevelopmentManager
		OR $KL_PurchasingTeam){
		ItemsinSetUp("ReadyToTest", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("NeedDescription", $RootPath, $db);
		$NumberOfTestExecuted++;
		//ItemsinSetUp("NeedPrice", $RootPath, $db);
		//$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETKL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETBL", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETGE", 4.75, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsinSetUp("WithReorderLevel", $RootPath, $db);
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
		GoodsJustTransferred("SERDE", "KANTO", 2, 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERVI", "KANTO", 2, 50, $RootPath, $db);
		$NumberOfTestExecuted++;
		
	/*	InsuficientStockForTopSalesItems("STABKL", "10-Silver",90, 100, 150, $RootPath, $db);
		InsuficientStockForTopSalesItems("STAINL", "20-Stainless Steel", 90, 100, 150, $RootPath, $db);
		InsuficientStockForTopSalesItems("STABBL", "30-Fashion Jewellery", 90, 100, 150, $RootPath, $db);
		InsuficientStockForTopSalesItems("ACCESO", "40-Accessories", 90, 100, 150, $RootPath, $db);
		InsuficientStockForTopSalesItems("CONSIG", "50-Consignment", 60, 100, 30, $RootPath, $db);
		ValueStockLocation("TOK66", 1000, 1200, 0, 0, $db);
		ValueStockLocation("TOKSA", 1000, 1400, 0, 0, $db);
		ValueStockLocation("TOKKS",  650,  750, 0, 0, $db);
		ValueStockLocation("TOKJC",  900, 1100, 0, 0, $db);
		ValueStockLocation("TOKBW",  650,  800, 0, 0, $db);
		ValueStockLocation("TOKUB", 1000, 1200, 0, 0, $db);
		ValueStockLocation("TOKMF", 1300, 1500, 0, 0, $db);
		ValueStockLocation("TOKSE", 1000, 1200, 0, 0, $db);
		ValueStockLocation("SASPG",   10,   30, 0, 0, $db);
	*/
	}

	if ($KL_BusinessDevelopmentManager){
		ItemsWithStockKantorButReorderLevelTokoZero($RootPath, $db);
		$NumberOfTestExecuted++;
/*		ItemsWithStockKantorButRLZeroAt("TOKSU", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButRLZeroAt("TOKUB", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButRLZeroAt("TOKSA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButRLZeroAt("TOKSS", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButRLZeroAt("TOKMF", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButRLZeroAt("TOKPU", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButRLZeroAt("TOKPA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButRLZeroAt("TOKKA", $RootPath, $db);
		$NumberOfTestExecuted++;
*/

		CategoryItemsNotInShop("TESTKL", "TOK66", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKPA", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKKA", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKSA", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKSS", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKPU", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTKL", "TOKMF", 7, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("STABKL", "TOK66", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKSE", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKKS", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKOB", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKPA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKKA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKSA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKSS", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKPU", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABKL", "TOKMF", 10, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("NOPOKL", "TOK66", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKPA", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKKA", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKSA", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKSS", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKPU", 7, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOKL", "TOKMF", 7, $RootPath, $db);
		$NumberOfTestExecuted++;

		
		CategoryItemsNotInShop("TESTBL", "TOKPS", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTBL", "TOKMU", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("TESTBL", "TOKSB", 3, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("STABBL", "TOKPS", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKMU", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("STABBL", "TOKSB", 3, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("NOPOBL", "TOKPS", 2, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("NOPOBL", "TOKSB", 2, $RootPath, $db);
		$NumberOfTestExecuted++;


		CategoryItemsNotInShop("DISC20", "TOKAR", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("DISC20", "TOKSU", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("DISC20", "TOKUB", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		
		CategoryItemsNotInShop("DISC50", "TOKAR", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("DISC50", "TOKSU", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("DISC50", "TOKUB", 3, $RootPath, $db);
		$NumberOfTestExecuted++;

		CategoryItemsNotInShop("DISC80", "TOKAR", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("DISC80", "TOKSU", 3, $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop("DISC80", "TOKUB", 3, $RootPath, $db);
		$NumberOfTestExecuted++;

	//	ItemsInCategoryWithStockKantorButReorderLevelTokoZero("DISC20", $RootPath, $db);
	//	$NumberOfTestExecuted++;
	//	ItemsInCategoryWithStockKantorButReorderLevelTokoZero("DISC50", $RootPath, $db);
	//	$NumberOfTestExecuted++;
	//	ItemsInCategoryWithStockKantorButReorderLevelTokoZero("DISC80", $RootPath, $db);
	//	$NumberOfTestExecuted++;
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
		OvestockAtSamples(1, $RootPath, $db);
		$NumberOfTestExecuted++;
		SamplesNotLongerNeeded($RootPath, $db);
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

	if ($KL_SystemAdmin){
		InsuficientStockForShopPackaging('SHPACK', 21, 90, 30, false, $RootPath, $db); // Works for both regular and outlet shop packaging
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingTeam){
		InsuficientStockForShopPackaging('SHPACK', 21, 90, 30, true, $RootPath, $db); // Works for both regular and outlet shop packaging
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager
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

	//	TopSalesNotInEnoughShops(  1, 500, 60, 9,  "STABLE", $RootPath, $db);
	//	$NumberOfTestExecuted++;
	//	TopSalesNotInEnoughShops(500, 800, 60, 7, "STABLE", $RootPath, $db);
	//	$NumberOfTestExecuted++;
	//	TopSalesNotInEnoughShops(  1,  50, 60, 3, "DISC50", $RootPath, $db);
	//	$NumberOfTestExecuted++;

	/*	ItemsNotTopSalesInShop(1, 700, 60, "TOK66", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 600, 60, "TOKSE", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 600, 60, "TOKOB", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 800, 60, "TOKKA", $RootPath, $db);

		ItemsNotTopSalesInShop(1, 800, 60, "TOKPA", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 500, 60, "TOKKS", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 500, 60, "TOKBW", $RootPath, $db);

		ItemsNotTopSalesInShop(1, 900, 60, "TOKJC", $RootPath, $db);

		ItemsNotTopSalesInShop(1, 600, 60, "TOKUB", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 700, 60, "TOKMF", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 700, 60, "TOKMU", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 800, 60, "TOKPU", $RootPath, $db);

		ItemsNotTopSalesInShop(1, 800, 60, "TOKSA", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 800, 60, "TOKSU", $RootPath, $db);
		ItemsNotTopSalesInShop(1, 700, 60, "TOKSS", $RootPath, $db);

		PerformanceItemsInCategory("GOOD", "TESTKL", 15,  30, "VERY GOOD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("GOOD", "TESTKL", 30,  45, "GOOD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "TESTKL", 50,  30, "BAD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "TESTKL", 60, 100, "LONG TIME TESTING", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("GOOD", "TESTKL", 60, 100, "TEST FINISHED", $RootPath, $db);
		$NumberOfTestExecuted++;

		PerformanceItemsInCategory("GOOD", "TESTBL", 15,  30, "VERY GOOD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("GOOD", "TESTBL", 30,  45, "GOOD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "TESTBL", 50,  30, "BAD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "TESTBL", 60, 100, "LONG TIME TESTING", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("GOOD", "TESTBL", 60, 100, "TEST FINISHED", $RootPath, $db);
		$NumberOfTestExecuted++;

		PerformanceItemsInCategory("GOOD", "TESTGE", 15,  30, "VERY GOOD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("GOOD", "TESTGE", 30,  45, "GOOD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "TESTGE", 50,  30, "BAD", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "TESTGE", 60, 100, "LONG TIME TESTING", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("GOOD", "TESTGE", 60, 100, "TEST FINISHED", $RootPath, $db);
		$NumberOfTestExecuted++;

		PerformanceItemsInCategory("BAD",  "NOPOKL", 60,  50, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "NOPOKL",120,  75, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "NOPOKL",180, 100, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;

		PerformanceItemsInCategory("BAD",  "NOPOBL", 60,  50, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "NOPOBL",120,  75, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "NOPOBL",180, 100, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;

		PerformanceItemsInCategory("BAD",  "NOPOGE", 60,  50, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "NOPOGE",120,  75, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "NOPOGE",180, 100, "MOVE TO DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;

		PerformanceItemsInCategory("BAD",  "DISC20", 60, 100, "MOVE TO 50% DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		PerformanceItemsInCategory("BAD",  "DISC50",180, 100, "MOVE TO 80% DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
*/
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_OperationalManager
		OR $KL_SalesDirector){
		
	//	ItemsNoSalesInLocation("WABOM", 30, 10, $RootPath, $db);
	//	ItemsNoSalesInLocation("WHAYA", 30, 10, $RootPath, $db);
	//	ItemsNoSalesInLocation("WHINT", 30, 10, $RootPath, $db);
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
		
		PurchasingOrdersDeliveryControl("Delayed", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		PurchasingOrdersDeliveryControl("Coming Soon", 15, $RootPath, $db);
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
		OR $KL_OperationalManager){
		OutstandingOrders("Wholesale", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
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

	if ($KL_SystemAdmin){
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
		OR $KL_OperationalManager
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("Online", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
		OnlineItemsOnProcess($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		OnlineOrdersFollowUp("KL-WEBSITE", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		OnlineOrdersFollowUp("LAZADA", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	/***************************************************************************************
	* Other tests     
	***************************************************************************************/
	if ($KL_SystemAdmin 
		OR $KL_ITSupport
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
}
prnMsg("Performed ". $NumberOfTestExecuted . " control tests",'success');

time_finish($begintime);

include ('includes/footer.inc');

?>
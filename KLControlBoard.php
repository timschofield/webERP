<?php
define("VERSIONFILE", "4.04");

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include('includes/session.php');
$Title = _('KL General Control Board '. VERSIONFILE);

/* Assign the sections to be executed, to avoid error 504*/
$ShowSectionInfo = FALSE;
$ProcessSection01 = FALSE;
$ProcessSection02 = FALSE;

if (!isset($_GET['Section'])){
	$ProcessSection01 = TRUE;
	$ProcessSection02 = TRUE;
}else{
	$ShowSectionInfo = TRUE;
		$Title = 'KL General Control Board Section ' . $_GET['Section'] . ' ' . VERSIONFILE;
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = TRUE;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = TRUE;
	}
}

include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/KLReorderLevel.php');

include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

/* Do the pending GL Postings to get the latest financial control reports*/
include('includes/GLPostings.inc');

/* ASSIGN users to groups */
include('includes/KLRoles.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
$NumberOfOpenShopsKL = NumberOfShops("SHOPKL", "ALL", $db);
$NumberOfOpenShopsBL = NumberOfShops("SHOPBL", "ALL", $db);
$NumberOfOpenShopsOU = NumberOfShops("SHOPOU", "ALL", $db);
$NumberOfOpenShopsTotal = $NumberOfOpenShopsKL + $NumberOfOpenShopsBL + $NumberOfOpenShopsOU;

/***************************************************************************************
* TEST AND PLAY AREA      
***************************************************************************************/

if ($_SESSION['UserID'] == "Ricard"){

//	$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTKA", "SHOPKL", $NumberOfTestExecuted, $RootPath, $db);

//	$KL_SystemAdmin = TRUE;
//	$KL_OperationalManager = TRUE;
//	$KL_OperationalLeader = TRUE;
//	$KL_AdministrationTeam = TRUE;
//	$KL_BusinessDevelopmentManager = TRUE;
// 	$KL_SalesDirector = TRUE;
//	$KL_PurchasingTeam = TRUE;
//	$KL_ShopSupportTeam = TRUE;
//	$KL_ShopSupportLeader = TRUE;
//	$KL_OnlineSales = TRUE;
//	$KL_ShopManager = TRUE;
//	$KL_SPGSeniorOrSupport = TRUE;
//	$KL_SPGJunior = TRUE;
//	$KL_PettyCash = TRUE;
//	$KL_ITSupport = TRUE;
//	phpinfo();

/* TEST AND PLAY WITH call_user_func to move this script mainly to a table in DB
//		over_or_below_limit("DISC80 Items in AR", "BELOW", 20, $RootPath, $db);
	$FunctionName = "over_or_below_limit";
	$Parameters = '"DISC80 Items in AR", "BELOW", 30, $RootPath, $db';
	call_user_func($FunctionName, "DISC80 Items in AR", "BELOW", 30, $RootPath, $db);
	$FunctionName("DISC80 Items in AR", "BELOW", 30, $RootPath, $db);
	
	$Par1 = "DISC80 Items in AR";
	$Par2 = "BELOW";
	$Par3 = 30;
	$Par4 = $RootPath;
	$Par5 = $db;
	$Par6 = "";
	call_user_func($FunctionName, $Par1, $Par2, $Par3, $Par4, $Par5, $Par6);
	$FunctionName($Par1, $Par2, $Par3, $Par4, $Par5, $Par6);
	prnMsg("END OF TESTS");
*/
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_AdministrationTeam 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector
	OR $KL_PurchasingTeam 
	OR $KL_ShopSupportTeam 
	OR $KL_ShopSupportLeader 
	OR $KL_ShopManager
	OR $KL_SalesTeamOnline
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

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$NumberOfTestExecuted = MinimumOutletStockAvailable(20, 80, 20, $NumberOfTestExecuted);
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_ShopSupportTeam){
		
		over_or_below_limit("Items changing price or moving category", "OVER", MAX_ITEMS_CHANGING_PRICE_OR_MOVING_DISC, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items changing price", "OVER", MAX_ITEMS_CHANGING_PRICE, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 20% discount", "OVER", MAX_ITEMS_MOVING_DISC20, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 50% discount", "OVER", MAX_ITEMS_MOVING_DISC50, $RootPath, $db);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 80% discount", "OVER", MAX_ITEMS_MOVING_DISC80, $RootPath, $db);
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
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){

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
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportLeader){
		
		ItemsInWrongShops("SHOPKL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInWrongShops("SHOPBL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInWrongShops("SHOPOU", $RootPath, $db);
		$NumberOfTestExecuted++;

	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportLeader){	

		DiscountedItemsWithWrongDiscount("DISC2A", "20", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC5A", "50", $RootPath, $db);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC8A", "80", $RootPath, $db);
		$NumberOfTestExecuted++;
		NotDiscountedItemsWithDiscount($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportLeader){
		ErrorsInTransfers( 15, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* BALANCE ACCOUNTS         
	***************************************************************************************/
	if ($KL_SystemAdmin){
		GLTransDateControl($db);
		$NumberOfTestExecuted++;
		GoodsReceivedNotInvoicedControl(1000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		CustomersDebtControl(10000, $periodnow, $db);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("IDR", "('111111209',
												'111111309')", 1, $periodnow, $db);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("USD", "('111205010')", 1, $periodnow, $db);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("EUR", "('111205020')", 1, $periodnow, $db);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("THB", "('111205030',
												'111204030')", 1, $periodnow, $db);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("HKD", "('111205040')", 1, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_AdministrationTeam){
		// cash at retail shops
		$NumberOfTestExecuted = CashAtShops(0, 10000000, 0, $NumberOfOpenShopsTotal * 4000000, $NumberOfTestExecuted, $periodnow, $db);
	}

	if ($KL_SystemAdmin 
		OR $KL_PurchasingTeam
		OR $KL_AdministrationTeam){
		BalanceAccountControl("111111100",          -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111202030",          -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111204030",           0,  500000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_AdministrationTeam){
		// Other banks accounts have enough funds to be transferred to the default accounts for each company 
		BalanceAccountControl("111121121AD",        0,   20000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121122AD",        0,   20000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121125AD",        0,   40000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121101AD", 10000000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110AD", 10000000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121121BB",        0,   20000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121122BB",        0,   20000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121125BB",        0,   40000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121101BB", 10000000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110BB", 10000000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin){
		BalanceListAccountControl("('111121101AD',
									'111121105AD',
									'111121107AD',
									'111121110AD',
									'111203010AD',
									'111259010AD', 
									'111259020AD', 
									'111259050AD')", "Total Banks PT.ADU", 2000000000, 4000000000, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111259010AD', 
									'111259020AD', 
									'111259050AD')", "Total PayPal PT.ADU", -1, 50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceListAccountControl("('111121100BB', 
									'111121101BB', 
									'111121105BB', 
									'111121110BB', 
									'111121111BB', 
									'111121112BB', 
									'111121120BB',
									'111121121BB',
									'111121122BB',
									'111121125BB',
									'111121130BB', 
									'111203010BB',
									'111203015BB',
									'111203020BB',
									'111259010BB', 
									'111259020BB', 
									'111259050BB')", "Total Banks PT.BB", 2000000000, 3500000000, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111259010BB', 
									'111259020BB', 
									'111259050BB')", "Total PayPal PT.BB", -1, 30000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_AdministrationTeam){
		BalanceAccountControl("111121100IK",  5000000, 150000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110IK",  5000000,  50000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_AdministrationTeam){
		BalanceAccountControl("111121100PI",  5000000, 120000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111121110PI",  5000000,  30000000, $periodnow, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		BalanceListAccountControl("('111131100', 
									'111208010', 
									'111208020', 
									'111208030', 
									'111208040')", "Total Brankas Shareholders",      0,3000000000, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111513000', 
									'111513000AD')", "Total WIP",  -1,   1, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceAccountControl("111111200",   50000000,  300000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111202010",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111209",          0,   25000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111131100",         -1, 2000000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111208010",         -1, 1000000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111208020",         -1, 1000000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111208030",         -1, 1000000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111208040",         -1, 1000000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111520000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111512000', 
									'111512000AD')", "Persediaan Bahan Produksi (Components)",   50000000,    150000000, $periodnow, $db);

		BalanceAccountControl("111800000",  350000000,  450000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111900000",   500000 * $NumberOfOpenShopsTotal, 1000000 * $NumberOfOpenShopsTotal, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111311100",  -50000000,   10000000, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111499000",         -1,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021400", -200000000,          1, $periodnow, $db);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021500",  400000000, 1000000000, $periodnow, $db);
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
		OR $KL_SalesDirector
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
		ItemsInSetup("NeedPrice", $RootPath, $db);
		$NumberOfTestExecuted++;
		}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
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
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){

		ItemsInmediateShortage("COMPOA", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){

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

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){

		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKL", $RootPath, $db);
		$NumberOfTestExecuted++;

		$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTKA", "SHOPKL", $NumberOfTestExecuted, $RootPath, $db);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("STABKA", "SHOPKL", $NumberOfTestExecuted, $RootPath, $db);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("NOPOKA", "SHOPKL", $NumberOfTestExecuted, $RootPath, $db);

		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBL", $RootPath, $db);
		$NumberOfTestExecuted++;

		$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTBA", "SHOPBL", $NumberOfTestExecuted, $RootPath, $db);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("STABBA", "SHOPBL", $NumberOfTestExecuted, $RootPath, $db);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("NOPOBA", "SHOPBL", $NumberOfTestExecuted, $RootPath, $db);

		ItemsWithStockKantorButReorderLevelTokoZero("SHOPOU", $RootPath, $db);
		$NumberOfTestExecuted++;

		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2A", "SHOPOU", $NumberOfTestExecuted, $RootPath, $db);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5A", "SHOPOU", $NumberOfTestExecuted, $RootPath, $db);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8A", "SHOPOU", $NumberOfTestExecuted, $RootPath, $db);
	}
//////////////////////////
// END OF SECTION
}

/***************************************************************************************
* SECTION 2
***************************************************************************************/

if ($ProcessSection02){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 02",'info');
	}

// SECTION 2 STARTS HERE
////////////////////////////	
	if ($KL_ShopSupportLeader
		OR $KL_PurchasingTeam){

		ConsumablesGoodsNotEnoughStock(30, 15, 45, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
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
		GoodsToBeProduced("COMPOA", "ONLYDISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ALL", $RootPath, $db);
		$NumberOfTestExecuted++;
	}


	if ($KL_SystemAdmin
		OR $KL_PurchasingTeam){
		ItemsWithoutPurchasingData($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		ComponentsToObsolete(false, 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		FlaggedAsObsoleteButStockAvailable($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader){
		ItemsInKLProcessAndRLNotZero($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader){
		ItemsOnSpecialRequest($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam
		OR $KL_PurchasingTeam){
		PackagingItemsOnWrongLocation($RootPath, $db); 
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager){
		CheckPackagingToBeRefilled(FALSE, FALSE, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportLeader){
		CheckPackagingToBeRefilled(FALSE, TRUE, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportTeam){
		
		CheckNegativeStock($RootPath, $db);
		$NumberOfTestExecuted++;
	}


	/***************************************************************************************
	* PACKAGING CONTROL         
	***************************************************************************************/
	if ($KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		prnMsg("Packaging Information",'info');
		InsuficientStockForShopPackaging('SHPACK', 15, 90, 30, true, $RootPath, $db); // Works for both regular and outlet shop packaging
		$NumberOfTestExecuted++;
		POStatusControl("PACKAGING","ON PRODUCTION", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PACKAGING","STILL NOT FULLY PAID", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PACKAGING","ARRIVING IN NEXT DAYS", 75, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
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

		ActiveItemsNoSales( 30, "STABKA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABBA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABGA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOKA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOKA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOBA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOBA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOGA", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOGA", $RootPath, $db);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "DISC2A", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "DISC2A", $RootPath, $db);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 50, "DISC5A", $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "DISC5A", $RootPath, $db);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 60, "DISC8A", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* PO, Sales Orders         
	***************************************************************************************/

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		OldPurchasingOrdersStillActive(90, $RootPath, $db);
		$NumberOfTestExecuted++;
		WrongItemsOnPurchaseOrders($RootPath, $db);
		$NumberOfTestExecuted++;
		WrongItemsOnWorkOrders($RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		PurchaseOrdersWrongPlannedDates($RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","IN NEGOTIATION WITH SUPPLIER", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","ON PRODUCTION", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","ON PRODUCTION", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","FINISHED BUT NOT PAID", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		POStatusControl("FORSALE","STILL NOT FULLY PAID", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","STILL NOT FULLY PAID", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		POStatusControl("","BALI PAID BUT NOT RECEIVED IN KANTOR", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","BALI RECEIVED IN KANTOR BUT NOT PAID", 0,$periodnow,  $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT SHIPPED BY SUPPLIER", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT RECEIVED IN AYE CARGO", 0,$periodnow,  $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT RECEIVED IN WANGFOONG CARGO", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","IN AYE CARGO BUT NOT SHIPPED", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","IN WANGFOONG CARGO BUT NOT SHIPPED", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","SHIPPED IN TRANSIT", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("","CUSTOMS CLEARANCE", 0, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","ARRIVING IN NEXT DAYS", 75, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","ARRIVING IN NEXT DAYS", 75, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		POStatusControl("","ARRIVING IN NEXT DAYS", 75, $periodnow, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_AdministrationTeam){
		OutstandingOrders("Retail", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin ){
		OutstandingOrders("Retail", "Quotation", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_SalesTeamOnline
		OR $KL_OperationalManager
		OR $KL_ShopSupportLeader
		OR $KL_ShopSupportTeam){
		OutstandingOrders("Wholesale", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline
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
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_SalesTeamOnline
		OR $KL_OperationalManager){ 
		OnlineMarketPlacePaymentPending($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_SalesTeamOnline
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("MarketPlace", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline){
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_PENDING, $RootPath, $db, $db_oc);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline){
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_SHIPPED, $RootPath, $db, $db_oc);
		$NumberOfTestExecuted++;
	}
 
	if ($KL_SystemAdmin 
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline){
		OnlineQuotationsFollowUp($RootPath, $db, $db_oc);
		$NumberOfTestExecuted++;
		OldOnlineQuotations(1, $RootPath, $db);
		$NumberOfTestExecuted++;
//		OutstandingOrders("Online", "Quotation", $RootPath, $db);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline){ 
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_PROCESSING, $RootPath, $db, $db_oc);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline){
		OnlineOrdersFollowUp("KL-WEBSITE", 10, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_SalesTeamOnline
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("Online", "Order", $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline
		OR $KL_ShopSupportTeam){ 
		OnlineItemsOnProcess($RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_ShopSupportLeader){ 
		ItemsNotNeededInOnlineOrderButRequested($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* Other tests     
	***************************************************************************************/
	if ($KL_ITSupport
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		ActiveItemsWithoutPicture($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline
		OR $KL_ITSupport){
		ImagesWithoutProduct($RootPath, $db);
		$NumberOfTestExecuted++;
		OpenCartItemsWithoutPicture($RootPath, $db, $db_oc);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam
		OR $KL_SalesTeamOnline){
		ItemsWithoutWeightOrVolume($RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsShouldBeInWebsite($db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		UsersNotLoggingIn(($_SESSION['MonthsAuditTrail'] *30)+1, "ALL_EXCEPT_SPGSUPPORT", $RootPath, $db);
		$NumberOfTestExecuted++;
		UsersNotLoggingIn(($_SESSION['MonthsAuditTrail'] *30)+1, "SPGSUPPORT", $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager 
		OR $KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_ShopSupportLeader 
		OR $KL_ShopManager){
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
		OR $KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
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
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		PettyCashBalance('Authorizer', $db);
		$NumberOfTestExecuted++;
		PettyCashToBeAuthorized($db);
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
						WHERE locstock.stockid = stockmaster.stockid) AS quantity,
					topsales30,
					topsales60,
					topsales90
			FROM 	stockmaster, stockcategory, klsalesperformance
			WHERE 	stockmaster.stockid = klsalesperformance.stockid
					AND stockmaster.categoryid = stockcategory.categoryid
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
		echo '<p class="page_title_text" align="center"><strong>' . GetCategoryNameFromCode($group) . _(' Items with NO sales on last ') . $maxdays . ' days and NO current PO or WO. Move to next category step</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('DOB Category') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('#Top Sales 30') . '</th>
							<th class="ascending">' . _('#Top Sales 60') . '</th>
							<th class="ascending">' . _('#Top Sales 90') . '</th>
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['categoryid'], 
					ConvertSQLDate($myrow['lastcategoryupdate']),
					locale_number_format($myrow['quantity'],0),
					locale_number_format($myrow['topsales30'],0),
					locale_number_format($myrow['topsales60'],0),
					locale_number_format($myrow['topsales90'],0)
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

function CashAtShops($MinCashPerShop, $MaxCashPerShop, $MinCashAllShops, $MaxCashAllShops, $NumberOfTestExecuted, $periodnow, $db){
	// while builing the list of KL POS accounts for all shops, we check one by one
	$ListAccounts = "('";
	$SQL="SELECT klposcashaccount
		FROM locations
		WHERE  locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . " 
		ORDER BY locations.locationname"; 
	$result = DB_query($SQL);
	while ($myrow = DB_fetch_array($result)){
		$ListAccounts = $ListAccounts . $myrow['klposcashaccount'] . "','";
		BalanceAccountControl($myrow['klposcashaccount'], $MinCashPerShop,$MaxCashPerShop, $periodnow, $db);
		$NumberOfTestExecuted++;
	}
	$ListAccounts = substr($ListAccounts, 0, -2) . ")";
	// Once we have the list of all KL POS accounts for all shops, we check the total in the system
	BalanceListAccountControl($ListAccounts, "Total Cash @ shops", $MinCashAllShops, $MaxCashAllShops, $periodnow, $db);
	$NumberOfTestExecuted++;
	return $NumberOfTestExecuted;
}


function CategoryItemsMissingInShops($Category, $ShopType, $NumberOfTestExecuted, $RootPath, $db){

	$MinQOH = NumberOfShops($ShopType, "ALL", $db);
	
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_TEST)){
		$Condition = " AND locations.alltestitems = '1' ";
	}elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_STABLE)){
		$Condition = " AND locations.allstableitems = '1' ";
	}elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)){
		$Condition = " AND locations.allnopoitems = '1' ";
	}elseif ($Category == "DISC2A"){
		$Condition = " AND locations.alldisc20items = '1' ";
	}elseif ($Category == "DISC5A"){
		$Condition = " AND locations.alldisc50items = '1' ";
	}elseif ($Category == "DISC8A"){
		$Condition = " AND locations.alldisc80items = '1' ";
	}
	
	$SQL="SELECT loccode
		FROM locations
		WHERE typeloc = '" . $ShopType . "'" 
		. $Condition;
	$result = DB_query($SQL);
	while ($myrow = DB_fetch_array($result)){
		CategoryItemsNotInShop($Category, $myrow['loccode'], $MinQOH, "ALL", $RootPath, $db);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop($Category, $myrow['loccode'], 1, "KANTOR", $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	return $NumberOfTestExecuted;
	
}



function CategoryItemsNotInShop($Category, $Shop, $MinQOH, $WhereisQOH, $RootPath, $db){
	
	$Exclusions = " (excluding items in Active Tranfers, Pending of Transfer, Change of Price, Move to Discount, Special Kantor Request, Service, Shop Online and Return to Supplier)";
	if ($WhereisQOH == "KANTOR"){
		$Message = GetCategoryNameFromCode($Category) . _(' items NOT in ') . $Shop . ' but with QOH >= ' . $MinQOH .' in KANTOR' . $Exclusions;
		$SQLQty = "(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode IN " . LIST_KANTOR . ")";
		$TitleQOH = "QOH Kantor";
	}else{
		$Message = GetCategoryNameFromCode($Category) . _(' items NOT in ') . $Shop . ' with QOH >= ' . $MinQOH .' in TOTAL' . $Exclusions;
		$SQLQty = "(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SPECIAL_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SAMPLE_LOCATIONS . ")";
		$TitleQOH = "QOH Available";
	}

	// count to how many shops do we need to set the RL
	if ($Category == 'TESTKA'){
		$WhereCat = " AND stockmaster.categoryid = 'TESTKA' ";
		$TypeOfShop = 'SHOPKL';
	} else if ($Category == 'STABKA') {
		$WhereCat = " AND stockmaster.categoryid = 'STABKA' ";
		$TypeOfShop = 'SHOPKL';
	} else if ($Category == 'NOPOKA') {
		$WhereCat = " AND stockmaster.categoryid = 'NOPOKA' ";
		$TypeOfShop = 'SHOPKL';
	} else if ($Category == 'TESTBA') {
		$WhereCat = " AND stockmaster.categoryid = 'TESTBA' ";
		$TypeOfShop = 'SHOPBL';
	} else if ($Category == 'STABBA') {
		$WhereCat = " AND stockmaster.categoryid = 'STABBA' ";
		$TypeOfShop = 'SHOPBL';
	} else if ($Category == 'NOPOBA') {
		$WhereCat = " AND stockmaster.categoryid = 'NOPOBA' ";
		$TypeOfShop = 'SHOPBL';
	} else if ($Category == 'DISC2A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC2A')";
		$TypeOfShop = 'SHOPOU';
	} else if ($Category == 'DISC5A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC5A')";
		$TypeOfShop = 'SHOPOU';
	} else if ($Category == 'DISC8A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC8A')";
		$TypeOfShop = 'SHOPOU';
	}

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode, " . 
					$SQLQty . " AS qoh,
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
				AND ( " . $SQLQty . " >= ". $MinQOH .")
				AND ((SELECT SUM(l.reorderlevel)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode IN " . LIST_ONLINE_SHOPS . ") = 0)
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  pendingqty > 0
							AND loctransfers.stockid =  stockmaster.stockid)
				AND NOT EXISTS (SELECT *
						FROM locstock l
						WHERE  l.stockid = stockmaster.stockid
							AND l.reorderlevel > 0
							AND l.quantity =  0)
			ORDER BY stockmaster.stockid";

//prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Message . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . $TitleQOH . '</th>
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

			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . 'Manual' . '</a>';
			$LinkRL1 = '';
			$LinkRL2 = '';
			$LinkRL3 = '';
			$LinkRL4 = '';
			$LinkRL5 = '';
			if ($myrow['qoh'] >= 1){
				$LinkRL1  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&LocCode=' . $Shop . '&RL=1' . '">' . '1' . '</a>';
			}
			if ($myrow['qoh'] >= 2){
				$LinkRL2  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&LocCode=' . $Shop . '&RL=2' . '">' . '2' . '</a>';
			}
			if ($myrow['qoh'] >= 3){
				$LinkRL3  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&LocCode=' . $Shop . '&RL=3' . '">' . '3' . '</a>';
			}
			if ($myrow['qoh'] >= 4){
				$LinkRL4  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&LocCode=' . $Shop . '&RL=4' . '">' . '4' . '</a>';
			}
			if ($myrow['qoh'] >= 5){
				$LinkRL5  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&LocCode=' . $Shop . '&RL=5' . '">' . '5' . '</a>';
			}

			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
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

	$total = 0;
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
			$total += $myrow['quantity'];
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
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"", 
				"TOTAL", 
				"", 
				"", 
				locale_number_format($total,0)
				);
		echo '</table>
				</div>';
	}
	InsertKPI("Stock", "Negative Stock items (PCS)", abs($total));
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

function CustomerDebtByCurrency($Currency){
	$SQL = "SELECT SUM(
					debtortrans.balance
				)/currencies.rate AS balance
			FROM debtorsmaster,
				currencies,
				debtortrans
			WHERE debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = '".$Currency."' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	return $myrow[0];
}

function CustomersDebtControl($AcceptedDifference, $period, $db){
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode = '111311100'
				AND chartdetails.period = ". $period . "";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	$ValueAtBalance = $myrow['saldo'];
	
	$DebtValueIDR = CustomerDebtByCurrency("IDR");
	$DebtValueUSD = CustomerDebtByCurrency("USD");
	$DebtValueAUD = CustomerDebtByCurrency("AUD");
	$DebtValueEUR = CustomerDebtByCurrency("EUR");
	
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

function GLTransDateControl($db){
	$SQL = "SELECT counterindex,
					type,
					typeno,
					account,
					narrative,
					amount
			FROM gltrans
			WHERE trandate = '0000-00-00'";
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Wrong dated GLTrans transactions in DB') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Counterindex') . '</th>
							<th class="ascending">' . _('Type') . '</th>
							<th class="ascending">' . _('Typeno') . '</th>
							<th class="ascending">' . _('Account') . '</th>
							<th class="ascending">' . _('Narrative') . '</th>
							<th class="ascending">' . _('Amount') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$myrow['counterindex'],
					$myrow['type'],
					$myrow['typeno'],
					$myrow['account'],
					$myrow['narrative'],
					$myrow['amount']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function GoodsJustArrived($kind, $location, $numdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays));
	$ShopsKL = NumberOfShops("SHOPKL", "ALL", $db);
	$ShopsBL = NumberOfShops("SHOPBL", "ALL", $db);
	$ShopsOU = NumberOfShops("SHOPOU", "ALL", $db);
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
							<th>' . _('#') . '</th>
							<th>' . _('Date') . '</th>
							<th>' . _('Code') . '</th>
							<th>' . _('Category') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Received') . '</th>
							<th>' . _('QOH') . '</th>
							<th>' . _('RL=?') . '</th>
							<th colspan="2">' . _('RL=1') . '</th>
							<th colspan="2">' . _('RL=2') . '</th>
							<th colspan="2">' . _('RL=3') . '</th>
							<th colspan="2">' . _('RL=4') . '</th>
							<th colspan="2">' . _('RL=5') . '</th>
						</tr>
						<tr>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th></th>
							<th></th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			
			// count how many shops do we need to set the RL
			if (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_KAPAL_LAUT)){
				$TypeOfShop = 'SHOPKL';
				$ShopsToSetRL = $ShopsKL;
			}elseif (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_BLINK)){
				$TypeOfShop = 'SHOPBL';
				$ShopsToSetRL = $ShopsBL;
			}elseif (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
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
			$LinkRL1All = '';
			$LinkRL1Some = '';
			$LinkRL2All = '';
			$LinkRL2Some = '';
			$LinkRL3All = '';
			$LinkRL3Some = '';
			$LinkRL4All = '';
			$LinkRL4Some = '';
			$LinkRL5All = '';
			$LinkRL5Some = '';

			if($ShopsToSetRL != 0){
				if ($myrow['qtytotal'] >= 0){
					$LinkRL1All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=1' . '">' . '1' . '</a>';
					$LinkRL1Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=1' . '">' . '1' . '</a>';
				}
				if ($myrow['qtytotal'] >= $ShopsToSetRL * 2){
					$LinkRL2All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=2' . '">' . '2' . '</a>';
					$LinkRL2Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=2' . '">' . '2' . '</a>';
				}
				if ($myrow['qtytotal'] >= $ShopsToSetRL * 3){
					$LinkRL3All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=3' . '">' . '3' . '</a>';
					$LinkRL3Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=3' . '">' . '3' . '</a>';
				}
				if ($myrow['qtytotal'] >= $ShopsToSetRL * 4){
					$LinkRL4All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=4' . '">' . '4' . '</a>';
					$LinkRL4Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=4' . '">' . '4' . '</a>';
				}
				if ($myrow['qtytotal'] >= $ShopsToSetRL * 5){
					$LinkRL5All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=5' . '">' . '5' . '</a>';
					$LinkRL5Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=5' . '">' . '5' . '</a>';
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
					$LinkRL1All,
					$LinkRL1Some,
					$LinkRL2All,
					$LinkRL2Some,
					$LinkRL3All,
					$LinkRL3Some,
					$LinkRL4All,
					$LinkRL4Some,
					$LinkRL5All,
					$LinkRL5Some
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
	
	$SQL = "SELECT SUM((grns.qtyrecd - grns.quantityinv) * (stockmaster.actualcost))
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

function PettyCashBalanceControlControl($Currency, $PCGLAccounts, $AcceptedDifference, $period, $db){
	$SQL = "SELECT SUM(pcashdetails.amount)/currencies.rate as amount_idr
			FROM pcashdetails,pctabs,currencies	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pctabs.currency = currencies.currabrev
				AND pctabs.currency = '". $Currency ."'
				AND pcashdetails.authorized != '0000-00-00'";

	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$PettyCashValue = $myrow['amount_idr'];

	$SQL = "SELECT SUM((bfwd + actual)) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode IN ".$PCGLAccounts."
				AND chartdetails.period = ". $period . "";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$ValueAtBalance = $myrow['saldo'];

	if (abs($ValueAtBalance - $PettyCashValue) > $AcceptedDifference){
		$text = "Petty Cash (" . $Currency . ") Balance value = " . locale_number_format($ValueAtBalance,0) . " <-> Real Petty Cash (" . $Currency . ") = " . locale_number_format($PettyCashValue,0);
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
			$file != 'Obsolete' AND
			$file != 'part_pics'){
			$StockId = substr($file, 0, strpos($file, $suffix));
			if (strpos($StockId, '.1') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.1'));
			}
			if (strpos($StockId, '.2') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.2'));
			}
			if (strpos($StockId, '.3') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.3'));
			}
			if (strpos($StockId, '.4') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.4'));
			}
			if (strpos($StockId, '.5') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.5'));
			}
			if (strpos($StockId, '.6') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.6'));
			}
			if (strpos($StockId, '.7') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.7'));
			}
			if (strpos($StockId, '.8') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.8'));
			}
			if (strpos($StockId, '.9') !== false){
				$StockId = substr($file, 0, strpos($StockId, '.9'));
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
						AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS qohpos,
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
						AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
						AND locstock.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.pendingqty) 
					FROM loctransfers,locations
					WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc = locations.loccode
						AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.pendingqty) 
					FROM loctransfers
					WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.pendingqty) 
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
						WHERE locstock.stockid = stockmaster.stockid) AS quantity,
					topsales30,
					topsales60,
					topsales90
			FROM 	stockmaster, klsalesperformance
			WHERE stockmaster.stockid = klsalesperformance.stockid
				AND stockmaster.discontinued = 0 
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.lastcategoryupdate <= '" . $FromDate . "'
				AND stockmaster.categoryid ='" . $group . "'
			ORDER BY stockmaster.stockid";
	
	$result = DB_query($SQL);		
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . GetCategoryNameFromCode($group) . ' Items for more than ' . $maxdays . ' days. Move to next step of cycle of life</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('DOB Category') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('#Top Sales 30') . '</th>
							<th class="ascending">' . _('#Top Sales 60') . '</th>
							<th class="ascending">' . _('#Top Sales 90') . '</th>
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['categoryid'], 
					ConvertSQLDate($myrow['lastcategoryupdate']),
					locale_number_format($myrow['quantity'],0),
					locale_number_format($myrow['topsales30'],0),
					locale_number_format($myrow['topsales60'],0),
					locale_number_format($myrow['topsales90'],0)
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
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND locstock.loccode = ". CODE_ONLINE_SHOP ."
				AND locstock.quantity > 0
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				AND NOT EXISTS (SELECT 	salesorderdetails.stkcode
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.stkcode = locstock.stockid
									AND salesorders.quotation = 0
									AND salesorders.fromstkloc = ". CODE_ONLINE_SHOP ."
									AND salesorderdetails.completed= 0)";
	$result = DB_query($SQL);
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "Items Not needed for any Online Order but with QOH > 0 in Shop Online" . '</strong></p>';
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
		$Title = GetCategoryNameFromCode($Category) . " Items ready to change to TEST";
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
							WHERE  pendingqty > 0
								AND loctransfers.stockid =  stockmaster.stockid)";
	}elseif($Check == "NeedDescription"){
		$Title = GetCategoryNameFromCode($Category) . " Items needing descriptions";
		$SQLWhere ="AND LENGTH(stockmaster.description) <= 2";
	}elseif($Check == "NeedPrice"){
		$Title = GetCategoryNameFromCode($Category) . " Items needing price";
		$SQLWhere ="AND (SELECT price
				FROM prices
				WHERE stockmaster.stockid = prices.stockid
					AND prices.typeabbrev = 'RT'
					AND currabrev = 'IDR') IS NULL";
	}elseif($Check == "WithReorderLevel"){
		$Title = GetCategoryNameFromCode($Category) . " Items with RL (items in SETUP should not have RL set)";
		$SQLWhere ="AND (SELECT SUM(reorderlevel)
				FROM locstock
				WHERE stockmaster.stockid = locstock.stockid) > 0 ";
	}else{
		$Title = GetCategoryNameFromCode($Category) . " Items in SETUP";
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

// prnMsg($SQL);
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

function ItemsInWrongShops($ShopType, $RootPath, $db){

	if ($ShopType == "SHOPKL"){
		$Message = 'Blink or Discount Items on KL shops';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . "
							OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . ")
						AND locations.typeloc = 'SHOPKL' ";
	}elseif ($ShopType == "SHOPBL"){
		$Message = 'KL or Discount items on BLINK shops';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . "
							OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . ")
						AND locations.typeloc = 'SHOPBL' ";
	}elseif ($ShopType == "SHOPOU"){
		$Message = 'KL or Blink items on OUTLET shops';
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
					AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS qohpos,
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
					AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
					AND locstock.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers,locations
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc = locations.loccode
						AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.pendingqty) 
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
			WHERE " . SQLFilterStockmasterForOnlineShop("ALL"). "
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
		InsuficientStockForItems("STABKA", "TM-", "Tali Mie", 20, 40, $RootPath, $db);
		
		2018-03-18 taken out the condition:		AND locstock.reorderlevel > 0

	*/
	
	$SQL = "SELECT locstock.stockid,
				locstock.quantity,
				stockmaster.categoryid,
				(SELECT SUM(l2.quantity)
					FROM locations, locstock l2
					WHERE l2.loccode = locations.loccode
						AND locstock.stockid = l2.stockid
						AND (locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . "
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
							AND (locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . "
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
				AND (actualcost) = 0
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
			FROM stockmaster
			WHERE ". SQLFilterStockmasterForOnlineShop("ALL") . "
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

function ItemsWithStockKantorButReorderLevelTokoZero($TypeOfShop, $RootPath, $db){
/**********************************************************************
items with stock kantor > 0 
RL is zero at one type of shop
No pending transfer regarding this item

2013-04-16 excluding items in change price process
2013-04-25 excluding items in move to discount / outlet process 
2014-12-02 excluding items in OLD categories

***********************************************************************/

	if ($TypeOfShop == "SHOPKL"){
		$ShopsToSetRL = NumberOfShops("SHOPKL", "ALL", $db);
		$Message = 'KAPAL-LAUT';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . "
							OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_GENERAL . ")";
	}elseif ($TypeOfShop == "SHOPBL"){
		$ShopsToSetRL = NumberOfShops("SHOPBL", "ALL", $db);
		$Message = 'BLINK';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . ")";
	}elseif ($TypeOfShop == "SHOPOU"){
		$ShopsToSetRL = NumberOfShops("SHOPOU", "ALL", $db);
		$Message = 'OUTLET';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . "
							OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_GENERAL . ")";
	}else{
		//error_
		return;
	}

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
				AND discontinued = 0
				AND (SELECT SUM(locstock.reorderlevel)
					FROM locstock, locations
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = locations.loccode
						AND locations.typeloc = '" . $TypeOfShop . "') = 0
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = " . CODE_KANTOR . ") > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  pendingqty > 0
							AND loctransfers.stockid =  stockmaster.stockid)
				AND stockcategory.stocktype = 'F' " . 
				$Condition . "
			ORDER BY stockid";
// prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Message . ' Items with stock available (but NO changing price or category) at Kantor but RL zero for all ' . $Message . '  SHOPS' . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . _('Code') . '</th>
							<th>' . _('Category') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('QOH Kantor') . '</th>
							<th>' . _('RL=?') . '</th>
							<th colspan="2">' . _('RL=1') . '</th>
							<th colspan="2">' . _('RL=2') . '</th>
							<th colspan="2">' . _('RL=3') . '</th>
							<th colspan="2">' . _('RL=4') . '</th>
							<th colspan="2">' . _('RL=5') . '</th>
						<tr>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th></th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
							<th>' . _('All') . '</th>
							<th>' . _('Some') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . 'Manual' . '</a>';
			// set the links to nil, and just set some if we have enough QOH
			$LinkRL1All = '';
			$LinkRL1Some = '';
			$LinkRL2All = '';
			$LinkRL2Some = '';
			$LinkRL3All = '';
			$LinkRL3Some = '';
			$LinkRL4All = '';
			$LinkRL4Some = '';
			$LinkRL5All = '';
			$LinkRL5Some = '';
			if($ShopsToSetRL != 0){
				if ($myrow['QtyKantor'] >= 0){
					$LinkRL1All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=1' . '">' . '1' . '</a>';
					$LinkRL1Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=1' . '">' . '1' . '</a>';
				}
				if ($myrow['QtyKantor'] >= $ShopsToSetRL * 2){
					$LinkRL2All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=2' . '">' . '2' . '</a>';
					$LinkRL2Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=2' . '">' . '2' . '</a>';
				}
				if ($myrow['QtyKantor'] >= $ShopsToSetRL * 3){
					$LinkRL3All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=3' . '">' . '3' . '</a>';
					$LinkRL3Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=3' . '">' . '3' . '</a>';
				}
				if ($myrow['QtyKantor'] >= $ShopsToSetRL * 4){
					$LinkRL4All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=4' . '">' . '4' . '</a>';
					$LinkRL4Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=4' . '">' . '4' . '</a>';
				}
				if ($myrow['QtyKantor'] >= $ShopsToSetRL * 5){
					$LinkRL5All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=5' . '">' . '5' . '</a>';
					$LinkRL5Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $myrow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=5' . '">' . '5' . '</a>';
				}
			}
			printf('<td class="number">%s</td>
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
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['categoryid'], 
					$myrow['description'], 
					locale_number_format($myrow['QtyKantor'],0),
					$ManualLink,
					$LinkRL1All,
					$LinkRL1Some,
					$LinkRL2All,
					$LinkRL2Some,
					$LinkRL3All,
					$LinkRL3Some,
					$LinkRL4All,
					$LinkRL4Some,
					$LinkRL5All,
					$LinkRL5Some
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

function OldOnlineQuotations($NumDaysBank, $RootPath, $db){

	$StartDateBank = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysBank));
	$StartDateXendit = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysXendit));
	$Titletext = "Old Online Quotations to be deleted. No Payment received in more than " . $NumDaysBank . " days.";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
				salesorders.klocpaymentcode,
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
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")
				AND salesorders.quotation = 1
				AND salesorders.orddate < '" . $StartDateBank . "'
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
							<th class="ascending">' . _('#KL-Website') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . _('Payment Method') . '</th>
							<th class="ascending">' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$PaymentMethodText = GetPaymentMethodTextFromCode($myrow['klocpaymentcode']);
			$DeleteLink = '<a href="' . $RootPath . '/KLDeleteSalesOrder.php?OrderNo=' . $myrow['orderno'] . '">' . 'Delete as Expired' . '</a>';
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
					</tr>', 
					$i, 
					$CodeLink, 
					locale_number_format($myrow['customerref']),
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue']+$myrow['freightcost'],$myrow['decimalplaces']),
					$myrow['currcode'], 
					$PaymentMethodText, 
					$DeleteLink
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
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")
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
				stockmaster.categoryid,
				salesorders.orddate,
				salesorderdetails.stkcode,
				salesorderdetails.quantity AS qtyorder,
				l1.reorderlevel,
				l1.quantity AS qtyready,
				(SELECT SUM(l2.quantity)
					FROM locstock AS l2
					WHERE l1.stockid = l2.stockid
						AND l2.loccode = " . CODE_KANTOR . ") AS qohkantor
			FROM salesorderdetails, salesorders, locstock AS l1, debtorsmaster, stockmaster	
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = l1.stockid
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND salesorderdetails.stkcode = stockmaster.stockid
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
							<th>' . _('#') . '</th>
							<th>' . _('Order') . '</th>
							<th>' . _('Customer') . '</th>
							<th>' . _('Name') . '</th>
							<th>' . _('Order Date') . '</th>
							<th>' . _('Item Code') . '</th>
							<th>' . _('Quantity') . '</th>
							<th>' . _('QOH Toko Online') . '</th>
							<th>' . _('QOH Kantor') . '</th>
							<th>' . _('Status') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$OrderInProcess = -1;
		$OrderReadyForShipment = true;
		while ($myrow = DB_fetch_array($result)) {
			if (($OrderInProcess != $myrow['orderno']) AND ($OrderInProcess != -1)){
				// We just checked all items in the order, and it is not the first one
				if ($OrderReadyForShipment){
					$Status = "ORDER READY FOR SHIPMENT";
				}else{
					$Status = "ORDER IN PROCESS";
				}
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>', 
						"",
						"", 
						"", 
						"", 
						"", 
						"", 
						"",
						"",
						"",
						$Status
						);
			$OrderReadyForShipment = true;
			}
			$k = StartEvenOrOddRow($k);
			
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$ItemLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stkcode'] . '">' . $myrow['stkcode'] . '</a>';
			
			if (($myrow['qtyready'] >= $myrow['qtyorder']) OR (!ItemInList($myrow['categoryid'], ONLINESHOP_AVAILABLE_STOCK_CATEGORIES))){
				// item ready to ship
				$Status = "";
			}elseif($myrow['qtyorder'] > $myrow['qohkantor']){
				// QOH kantor not enough to cover the order, so we need to get some from the shops
				$Status = "Needs return from shops";
				$OrderReadyForShipment = false;
			}else{
				// QOH kantor enough to cover the requirements of the order
				$Status = "In process kantor";
				$OrderReadyForShipment = false;
			}
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					$ItemLink, 
					locale_number_format($myrow['qtyorder'],0),
					locale_number_format($myrow['qtyready'],0),
					locale_number_format($myrow['qohkantor'],0),
					$Status
					);
			$i++;
			$OrderInProcess = $myrow['orderno'];
		}
		// status of the last order online
		if ($OrderReadyForShipment){
			$Status = "ORDER READY FOR SHIPMENT";
		}else{
			$Status = "ORDER IN PROCESS";
		}
		$k = StartEvenOrOddRow($k);
		printf('<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>', 
				"",
				"", 
				"", 
				"", 
				"", 
				"", 
				"",
				"",
				"",
				$Status
				);

		echo '</table>
				</div>';
	}
}

function OnlineOrdersFollowUp($Source, $numDays, $RootPath, $db){

	$Titletext = "Follow up Outstanding " . $Source. " Online Orders";
	$ThankYouDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));
// 2015-01-14 Prices already NET for online orders
//                (SELECT SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent))
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
				WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")
					AND debtorsmaster.debtorno != 'LAZADA'
					AND salesorders.quotation = 0
					AND (	(debtortrans.type = 12 
								AND salesorders.klemailpaymentconfirm = '0000-00-00')
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
					''
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineQuotationsFollowUp($RootPath, $db, $db_oc){

	$Titletext = "Follow up Outstanding Online Quotations";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				salesorders.klemailremindbanktransfer,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
                SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
				salesorders.klocpaymentcode,
				salesorders.klocorderstatus,
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
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")
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
							<th class="ascending">' . _('#KL-Website') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . _('Payment Method') . '</th>
							<th class="ascending">' . _('OC Status') . '</th>
							<th class="ascending">' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';

			$PaymentLinkText = 'Apply Payment';
			$PaymentValue = $myrow['ordervalue']+$myrow['freightcost'];

			// prepare the links according to the payment code from OpenCart
			$PaymentLink = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $myrow['orderno'] . '&PaymentCode=' . $myrow['klocpaymentcode'] . '&CustomerCode=' . $myrow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
			$PaymentMethodText = GetPaymentMethodTextFromCode($myrow['klocpaymentcode']);

			$OCStatusText = GetOpenCartStatusTextFromCode($myrow['klocorderstatus'], $db_oc);

			if ($OCStatusText != "Processing"){
				$PaymentLink = ''; // do not allow Apply payment in case of an status that is not processing
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
					</tr>', 
					$i, 
					$CodeLink, 
					locale_number_format($myrow['customerref']),
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue']+$myrow['freightcost'],$myrow['decimalplaces']),
					$myrow['currcode'], 
					$PaymentMethodText,
					$OCStatusText,
					$PaymentLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineMarketPlacePaymentPending($RootPath, $db){

	$Titletext = "Follow up Marketplace Online Orders Payment Pending";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
                SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
				salesorders.klpaidcash,
				debtorsmaster.currcode,
				currencies.decimalplaces
			FROM salesorders 
				INNER JOIN salesorderdetails 	
					ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
					ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorders.klpaidcash= 0	
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_MARKETPLACE . ")
			GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.debtorno,
					salesorders.deliverto";			

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('#MarketPlace') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . '' . '</th>
							<th class="ascending">' . _('Paid Tokopedia') . '</th>
							<th class="ascending">' . _('Paid Shopee') . '</th>
							<th class="ascending">' . _('Paid Lazada') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$TotalPaymentValue = 0;
		
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$PaymentLinkText = 'Apply Payment';
			$PaymentValue = $myrow['ordervalue']+$myrow['freightcost'];

			$PaymentLinkManualText = 'Mark As Paid';
			
			$PaymentManual = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $myrow['orderno'] . '&PaymentCode=' . 'MANUAL_MARKETPLACE' . '&CustomerCode=' . $myrow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkManualText .'</a>';
			// prepare the links according to the Marketplace
			if ($myrow['debtorno'] == "TOKOPEDIA"){
				$PaymentTokopedia = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $myrow['orderno'] . '&PaymentCode=' . 'tokopedia' . '&CustomerCode=' . $myrow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
				$PaymentManual = '';
			}else{
				$PaymentTokopedia = '';
			}
			if ($myrow['debtorno'] == "SHOPEE"){
				$PaymentShopee = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $myrow['orderno'] . '&PaymentCode=' . 'shopee' . '&CustomerCode=' . $myrow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
				$PaymentManual = '';
			}else{
				$PaymentShopee = '';
			}
			if ($myrow['debtorno'] == "LAZADA"){
				$PaymentLazada = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $myrow['orderno'] . '&PaymentCode=' . 'lazada' . '&CustomerCode=' . $myrow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
				$PaymentManual = '';
			}else{
				$PaymentLazada = '';
			}

			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$myrow['debtorno'], 
					$myrow['name'], 
					$myrow['orderno'], 
					$myrow['customerref'],
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($PaymentValue,$myrow['decimalplaces']),
					$myrow['currcode'], 
					$PaymentManual,
					$PaymentTokopedia,
					$PaymentShopee,
					$PaymentLazada
					);
			$i++;
			$TotalPaymentValue += $PaymentValue;
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>', 
				"", 
				"", 
				"", 
				"", 
				"",
				"TOTAL:", 
				locale_number_format($TotalPaymentValue,$myrow['decimalplaces']),
				"IDR", 
				"",
				"",
				"",
				""
				);
		echo '</table>
				</div>';
	}
}


function OpenCartItemsWithoutPicture($RootPath, $db, $db_oc){

	$SQL = "SELECT 	oc_product.model AS stockid
			FROM oc_product
			WHERE oc_product.status = 1
			ORDER BY oc_product.model";
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
		$WebsiteIDName = "";
	}elseif ($customertype == "Consignment"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_CONSIGNMENT . ")";
		$namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Consignment";
		$WebsiteIDName = "";
	}elseif ($customertype == "Wholesale"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WHOLESALE . ")";
		$namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Wholesale";
		$WebsiteIDName = "";
	}elseif ($customertype == "Online"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")";
		$namefield = " salesorders.deliverto AS name ";
		$Titletext = "Outstanding Online";
		$WebsiteIDName = "#KL-Website";
	}elseif ($customertype == "MarketPlace"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_MARKETPLACE . ")";
		$namefield = " salesorders.deliverto AS name ";
		$Titletext = "Outstanding MarketPlace";
		$WebsiteIDName = "#KL-Website";
	}else{
		$namefield = " debtorsmaster.name ";
		$whereclause = " ";
		$Titletext = _('Outstanding');
		$WebsiteIDName = "";
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
				salesorders.customerref,
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
			WHERE salesorderdetails.completed= 0 "
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
							<th class="ascending">' . $WebsiteIDName . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Total Value') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$TotalValue = 0;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			if ($customertype == "Online"){
				$WebsiteID = locale_number_format($myrow['customerref']);
			}else{
				$WebsiteID = "";
			}

			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$WebsiteID,
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue'],0)
					);
			$TotalValue += $myrow['ordervalue'];
			$i++;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"", 
				"", 
				"",
				"", 
				"", 
				"Total IDR", 
				locale_number_format($TotalValue,0)
				);
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

function MinimumOutletStockAvailable($MinModels20, $MinModels50, $MinModels80, $NumberOfTestExecuted){
	$SQL="SELECT loccode,
			locationname
		FROM locations
		WHERE typeloc = 'SHOPOU'" 
		. $Condition;
	$result = DB_query($SQL);
	while ($myshop = DB_fetch_array($result)){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = 'DISC2A'
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$myshop['loccode']."'";
		$result = DB_query($SQL);
		$myrow = DB_fetch_array($result);
		if ($myrow[0] < $MinModels20){
			$text = "Discount 20% avaliable at " . $myshop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($myrow[0],0) . " Minimum = " . locale_number_format($MinModels20,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
		$NumberOfTestExecuted++;

		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = 'DISC5A'
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$myshop['loccode']."'";
		$result = DB_query($SQL);
		$myrow = DB_fetch_array($result);
		if ($myrow[0] < $MinModels50){
			$text = "Discount 50% avaliable at " . $myshop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($myrow[0],0) . " Minimum = " . locale_number_format($MinModels50,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
		$NumberOfTestExecuted++;

		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = 'DISC8A'
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$myshop['loccode']."'";
		$result = DB_query($SQL);
		$myrow = DB_fetch_array($result);
		if ($myrow[0] < $MinModels80){
			$text = "Discount 80% avaliable at " . $myshop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($myrow[0],0) . " Minimum = " . locale_number_format($MinModels80,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
		$NumberOfTestExecuted++;
	}
	return $NumberOfTestExecuted;
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
				AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
				AND locstock.loccode NOT IN " . LIST_PACAKING_LOCATIONS . "
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
					AND loctransfers.pendingqty > 0
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
				AND pendingqty != 0
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

function UsersNotLoggingIn($maxdays, $type, $RootPath, $db){
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
							<th>' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/WWW_User_Delete.php?UserID=' . $myrow['userid'] . '">' . 'Delete' . '</a>';
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['userid'],
					$myrow['realname'],
					ConvertSQLDate($myrow['lastvisitdate']),
					$CodeLink
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
				SUM(locstock.quantity *(stockmaster.actualcost)) AS valuetotal
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

function OpenCartOrdersByStatus($Status, $RootPath, $db, $db_oc){
	$SQL = "SELECT 	oc_order.order_id,
				oc_order.store_name,
				oc_order.firstname,
				oc_order.lastname,
				oc_order.date_modified
			FROM oc_order
			WHERE oc_order.order_status_id = '" . $Status . "'
			ORDER BY oc_order.date_modified";
	$result = DB_query_oc($SQL);
	if (DB_num_rows($result) != 0){
		$showHeader = TRUE;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($showHeader){
				if ($Status == OPENCART_ORDER_STATUS_PENDING){
					$StatusText = "Pending";
				}else if ($Status == OPENCART_ORDER_STATUS_PROCESSING){
					$StatusText = "Processing";
				}else if ($Status == OPENCART_ORDER_STATUS_SHIPPED){
					$StatusText = "Shipped";
				}else{
					$StatusText = "Unknown";
				}
				echo '<p class="page_title_text" align="center"><strong>' . $StatusText .' OpenCart Online Orders' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th class="ascending">' . _('#') . '</th>
									<th class="ascending">' . _('#Order') . '</th>
									<th class="ascending">' . _('Last Modification') . '</th>
									<th class="ascending">' . _('Shop') . '</th>
									<th class="ascending">' . _('Customer name') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			if ($myrow['currency_code'] == "IDR"){
				$RoundingDecimals = 0;
			}else{
				$RoundingDecimals = 2;
			}
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					locale_number_format($myrow['order_id'],0),
					ConvertSQLDateTime($myrow['date_modified']), 
					$myrow['store_name'],
					$myrow['firstname'] . " " . $myrow['lastname']
					);
			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}

}

?>
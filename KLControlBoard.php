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

$begintime = time_start();
$NumberOfTestExecuted = 0;

$PeriodNow=GetPeriod(Date($_SESSION['DefaultDateFormat']));
$NumberOfOpenShopsKL = NumberOfShops("SHOPKL");
$NumberOfOpenShopsBL = NumberOfShops("SHOPBL");
$NumberOfOpenShopsOU = NumberOfShops("SHOPOU");
$NumberOfOpenShopsTotal = $NumberOfOpenShopsKL + $NumberOfOpenShopsBL + $NumberOfOpenShopsOU;

/***************************************************************************************
* TEST AND PLAY AREA      
***************************************************************************************/

if ($_SESSION['UserID'] == "Ricard"){

//	$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);

/*	$KL_SystemAdmin = TRUE;
	$KL_OperationalManager = TRUE;
	$KL_OperationalLeader = TRUE;
	$KL_AdministrationLeader = TRUE;
	$KL_AdministrationTeam = TRUE;
	$KL_BusinessDevelopmentManager = TRUE;
 	$KL_SalesDirector = TRUE;
	$KL_PurchasingTeam = TRUE;
	$KL_ShopSupportTeam = TRUE;
	$KL_ShopSupportLeader = TRUE;
	$KL_OnlineSales = TRUE;
	$KL_ShopManager = TRUE;
	$KL_SPGSeniorOrSupport = TRUE;
	$KL_SPGJunior = TRUE;
	$KL_PettyCash = TRUE;
	$KL_ITSupport = TRUE;
*/
//	phpinfo();

/* TEST AND PLAY WITH call_user_func to move this script mainly to a table in DB
//		over_or_below_limit("DISC80 Items in AR", "BELOW", 20, $RootPath);
	$FunctionName = "over_or_below_limit";
	$Parameters = '"DISC80 Items in AR", "BELOW", 30, $RootPath';
	call_user_func($FunctionName, "DISC80 Items in AR", "BELOW", 30, $RootPath);
	$FunctionName("DISC80 Items in AR", "BELOW", 30, $RootPath);
	
	$Par1 = "DISC80 Items in AR";
	$Par2 = "BELOW";
	$Par3 = 30;
	$Par4 = $RootPath;
	$Par5 = "";
	call_user_func($FunctionName, $Par1, $Par2, $Par3, $Par4, $Par5);
	$FunctionName($Par1, $Par2, $Par3, $Par4, $Par5);
	prnMsg("END OF TESTS");
*/
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
		SuppliersWithoutBasicData($RootPath);
		$NumberOfTestExecuted++;
		ItemsWithoutStandardCost($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.04, "SHOWLINK", $RootPath);
		$NumberOfTestExecuted++;
		WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath);
		$NumberOfTestExecuted++;
		WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath);
		$NumberOfTestExecuted++;
		WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath);
		$NumberOfTestExecuted++;
//		WrongStandardCost("Philippines", "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath);
//		$NumberOfTestExecuted++;
//		WrongStandardCost("India"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWLINK", $RootPath);
//		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingTeam) {
		WrongStandardCost("Indonesia"  , "", STANDARD_COST_FACTOR_INDONESIA, 0.04, "SHOWONLY", $RootPath);
		$NumberOfTestExecuted++;
		WrongStandardCost("Thailand"   , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWONLY", $RootPath);
		$NumberOfTestExecuted++;
		WrongStandardCost("China"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWONLY", $RootPath);
		$NumberOfTestExecuted++;
		WrongStandardCost("Hong Kong"  , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWONLY", $RootPath);
		$NumberOfTestExecuted++;
//		WrongStandardCost("Philippines", "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWONLY", $RootPath);
//		$NumberOfTestExecuted++;
//		WrongStandardCost("India"      , "", STANDARD_COST_FACTOR_FOREIGN, 0.04, "SHOWONLY", $RootPath);
//		$NumberOfTestExecuted++;
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
		
		over_or_below_limit("Items changing price or moving category", "OVER", MAX_ITEMS_CHANGING_PRICE_OR_MOVING_DISC, $RootPath);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items changing price", "OVER", MAX_ITEMS_CHANGING_PRICE, $RootPath);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 20% discount", "OVER", MAX_ITEMS_MOVING_DISC20, $RootPath);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 50% discount", "OVER", MAX_ITEMS_MOVING_DISC50, $RootPath);
		$NumberOfTestExecuted++;
		over_or_below_limit("Items moving to 80% discount", "OVER", MAX_ITEMS_MOVING_DISC80, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam){

		ItemsChangingPriceDelayed(4, $RootPath);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(20, 4, $RootPath);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(50, 4, $RootPath);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(80, 4, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){

		ItemsChangingPriceDelayed(5, $RootPath);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(20, 5, $RootPath);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(50, 5, $RootPath);
		$NumberOfTestExecuted++;
		ItemsMovingToDiscountDelayed(80, 5, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportLeader){
		
		ItemsInWrongShops("SHOPKL", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInWrongShops("SHOPBL", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInWrongShops("SHOPOU", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInWrongShops("DEFECTIVE", $RootPath);
		$NumberOfTestExecuted++;

	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportLeader){	

		DiscountedItemsWithWrongDiscount("DISC2A", "20", $RootPath);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC2B", "20", $RootPath);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC2G", "20", $RootPath);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC5A", "50", $RootPath);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC5B", "50", $RootPath);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC5G", "50", $RootPath);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC8A", "80", $RootPath);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC8B", "80", $RootPath);
		$NumberOfTestExecuted++;
		DiscountedItemsWithWrongDiscount("DISC8G", "80", $RootPath);
		$NumberOfTestExecuted++;
		NotDiscountedItemsWithDiscount($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportLeader){
		ActiveTransfersByLocation($RootPath);
		$NumberOfTestExecuted++;
		ActiveTransferStatus($RootPath);
		$NumberOfTestExecuted++;
		RecentlyClosedTransferStatus(1, $RootPath);
		$NumberOfTestExecuted++;
		ErrorsInTransfers( 15, $RootPath);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* BALANCE ACCOUNTS         
	***************************************************************************************/
	if ($KL_SystemAdmin){
		GLTransDateControl();
		$NumberOfTestExecuted++;
		GoodsReceivedNotInvoicedControl(1000000, $PeriodNow);
		$NumberOfTestExecuted++;
		CustomersDebtControl(10000, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("IDR", "('111111209',
												'111111309')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("USD", "('111205010')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("EUR", "('111205020')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("THB", "('111205030',
												'111204030')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControlControl("HKD", "('111205040')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
	}

	if ($KL_AdministrationTeam){
		// cash at retail shops
		$NumberOfTestExecuted = CashAtShops(0, 10000000, 0, $NumberOfOpenShopsTotal * 4000000, $NumberOfTestExecuted, $PeriodNow);
	}
	
	if ($KL_SystemAdmin
		OR $KL_AdministrationLeader
		OR $KL_AdministrationTeam){
		InternalBankTransfers("PTADU", 
					"111121105AD", 1000000000, 2000000000,
					"111121101AD",   25000000,   75000000,
					"111121110AD",   25000000,   75000000,
					"111121115AD",   25000000,   75000000, 
					"111121121AD",     100000,    1000000,
					"111121122AD",     100000,    1000000,
					"111121125AD",     100000,    1000000,
					25000000,
					 1000000,
					$PeriodNow);	
		$NumberOfTestExecuted++;

		InternalBankTransfers("PTSMH", 
					"111121105SM", 1000000000, 1500000000,
					"111121100SM",   25000000,   75000000,
					"111121110SM",   25000000,   75000000,
					"111121115SM",   25000000,   75000000, 
					"", 0, 0, "", 0, 0,	"", 0, 0,
					25000000,
					0,
					$PeriodNow);	
		$NumberOfTestExecuted++;

		InternalBankTransfers("PTBB", 
					"111121105BB", 1000000000, 1500000000,
					"111121101BB",   25000000,   75000000,
					"111121110BB",   25000000,   75000000,
					"111121115BB",   25000000,   75000000, 
					"", 0, 0, "", 0, 0,	"", 0, 0,
					25000000,
					0,
					$PeriodNow);	
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_PurchasingTeam
		OR $KL_AdministrationLeader){
		BalanceAccountControl("111111100",          -1,          1, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111202030",          -1,          1, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111204030",           0,  500000000, $PeriodNow);
		$NumberOfTestExecuted++;
	}
	

	if ($KL_SystemAdmin){
		BalanceListAccountControl("('111121101AD',
									'111121105AD',
									'111121107AD',
									'111121110AD',
									'111121115AD',
									'111121121AD',
									'111121122AD',
									'111121125AD',
									'111203010AD',
									'111203020AD',
									'111259010AD', 
									'111259020AD', 
									'111259050AD')", "Total Banks PT ADU", 2000000000, 10000000000, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111259010AD', 
									'111259020AD', 
									'111259050AD')", "Total PayPal PT ADU", -1, 75000000, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111121100SM',
									'111121105SM',
									'111121110SM',
									'111121115SM')", "Total Banks PT SMH", 2000000000, 5000000000, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111121100BB', 
									'111121101BB', 
									'111121105BB', 
									'111121110BB', 
									'111121115BB', 
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
									'111259050BB')", "Total Banks PT BB", 1000000000, 3000000000, $PeriodNow);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin){
		BalanceListAccountControl("('111131100', 
									'111208010', 
									'111208020', 
									'111208030', 
									'111208040')", "Total Brankas Shareholders",      0,2000000000, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111513000', 
									'111513000AD')", "Total WIP",  -5000000,   5000000, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceAccountControl("111111200",   50000000,  400000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111202010",         -1,          1, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111111209",          0,   25000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111131100",         -1, 2000000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111208010",         -1, 1000000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111208020",         -1, 1000000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111208030",         -1, 1000000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111208040",         -1, 1000000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111520000",         -1,          1, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111512000', 
									'111512000AD')", "Persediaan Bahan Produksi (Components)",   50000000,    200000000, $PeriodNow);

		BalanceAccountControl("111800000",  12000000 * $NumberOfOpenShopsTotal,  17500000 * $NumberOfOpenShopsTotal, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111900000",    500000 * $NumberOfOpenShopsTotal,   1200000 * $NumberOfOpenShopsTotal, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111311100",  -50000000,   10000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111499000",         -1,          1, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021400", -200000000,          1, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021500",  500000000, 1500000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612012015",         -1,          1, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("612012016",         -1,          1, $PeriodNow);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* STOCK CONTROL         
	***************************************************************************************/

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		ItemsInSetup("ReadyToTest", "SETKLA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInSetup("ReadyToTest", "SETBLA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInSetup("ReadyToTest", "SETGEA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInSetup("NeedDescription", "SETKLA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInSetup("NeedDescription", "SETBLA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInSetup("NeedDescription", "SETGEA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInSetup("NeedPrice", "", $RootPath);
		$NumberOfTestExecuted++;
		}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		ItemsInSetup("WithReorderLevel", "SETKLA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInSetup("WithReorderLevel", "SETBLA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInSetup("WithReorderLevel", "SETGEA", $RootPath);
		$NumberOfTestExecuted++;
		ObsoleteComponentsInActiveBOM($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){

		ItemsInmediateShortage("COMPOA", $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){

		GoodsJustArrived("PO", "KANTO", 3, $RootPath);
		$NumberOfTestExecuted++;
		GoodsJustArrived("WO", "KANTO", 3, $RootPath);
		$NumberOfTestExecuted++;
		GoodsJustArrived("WO", "SUPBA", 3, $RootPath);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SAMPR", "KANTO", 2, 30, $RootPath);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SASPG", "KANTO", 2, 30, $RootPath);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERSU", "KANTO", 2, 30, $RootPath);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERSW", "KANTO", 2, 30, $RootPath);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERDE", "KANTO", 2, 30, $RootPath);
		$NumberOfTestExecuted++;
		GoodsJustTransferred("SERVI", "KANTO", 2, 30, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){

		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKL", $RootPath);
		$NumberOfTestExecuted++;

		$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("STABKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("NOPOKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2A", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5A", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8A", "SHOPKL", $NumberOfTestExecuted, $RootPath);

		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBL", $RootPath);
		$NumberOfTestExecuted++;

		$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTBA", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("STABBA", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("NOPOBA", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2B", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5B", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8B", "SHOPBL", $NumberOfTestExecuted, $RootPath);

		ItemsWithStockKantorButReorderLevelTokoZero("SHOPOU", $RootPath);
		$NumberOfTestExecuted++;

		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2A", "SHOPOU", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2B", "SHOPOU", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2G", "SHOPOU", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5A", "SHOPOU", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5B", "SHOPOU", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5G", "SHOPOU", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8A", "SHOPOU", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8B", "SHOPOU", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8G", "SHOPOU", $NumberOfTestExecuted, $RootPath);
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

		ConsumablesGoodsNotEnoughStock(30, 15, 45, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){

		ValueStockLocation("SERVI",    0,  150, 0, 0);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERDE",    0,  150, 0, 0);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERSU",    0,  150, 0, 0);
		$NumberOfTestExecuted++;
		ValueStockLocation("SERSW",    0,  150, 0, 0);
		$NumberOfTestExecuted++;
		OvestockAtSamples(1, $RootPath);
		$NumberOfTestExecuted++;
		SamplesNotLongerNeeded($RootPath);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ONLYDISCOUNT", $RootPath);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "DISCOUNT", $RootPath);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ALL", $RootPath);
		$NumberOfTestExecuted++;
	}


	if ($KL_SystemAdmin
		OR $KL_PurchasingTeam){
		ItemsWithoutPurchasingData($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		ComponentsToObsolete(false, 0, $RootPath);
		$NumberOfTestExecuted++;
		FlaggedAsObsoleteButStockAvailable($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader){
		ItemsInKLProcessAndRLNotZero($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader){
		ItemsOnSpecialRequest($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam
		OR $KL_PurchasingTeam){
		PackagingItemsOnWrongLocation($RootPath); 
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager){
		CheckPackagingToBeRefilled(FALSE, FALSE, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportLeader){
		CheckPackagingToBeRefilled(FALSE, TRUE, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportTeam){
		
		CheckNegativeStock($RootPath);
		$NumberOfTestExecuted++;
	}


	/***************************************************************************************
	* PACKAGING CONTROL         
	***************************************************************************************/
	if ($KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		prnMsg("Packaging Information",'info');
		InsuficientStockForShopPackaging('SHPACK', 30, FORECAST_DAYS_FOR_PACKAGING_STOCK, true, false, $RootPath); // Works for both regular and outlet shop packaging
		$NumberOfTestExecuted++;
		POStatusControl("PACKAGING","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* SALES CONTROL         
	***************************************************************************************/
	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){

		ItemsInCategoryForMoreThanDays( 120, "SETKLA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "SETBLA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "SETGEA", $RootPath);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "TESTKA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTKA", $RootPath);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "TESTBA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTBA", $RootPath);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "TESTGA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 60, "TESTGA", $RootPath);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "STABKA", $RootPath);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABBA", $RootPath);
		$NumberOfTestExecuted++;
		ActiveItemsNoSales( 30, "STABGA", $RootPath);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOKA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOKA", $RootPath);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOBA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOBA", $RootPath);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "NOPOGA", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "NOPOGA", $RootPath);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "DISC2A", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "DISC2A", $RootPath);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 30, "DISC2B", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "DISC2B", $RootPath);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 30, "DISC2G", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 90, "DISC2G", $RootPath);
		$NumberOfTestExecuted++;

		ActiveItemsNoSales( 50, "DISC5A", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "DISC5A", $RootPath);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 50, "DISC5B", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "DISC5B", $RootPath);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 50, "DISC5G", $RootPath);
		$NumberOfTestExecuted++;
		ItemsInCategoryForMoreThanDays( 120, "DISC5G", $RootPath);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 60, "DISC8A", $RootPath);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 60, "DISC8B", $RootPath);
		$NumberOfTestExecuted++;
		
		ActiveItemsNoSales( 60, "DISC8G", $RootPath);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* PO, Sales Orders         
	***************************************************************************************/

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		OldPOStillActive(90, $RootPath);
		$NumberOfTestExecuted++;
		OldWOStillActive(60, $RootPath);
		$NumberOfTestExecuted++;
		WrongItemsOnPurchaseOrders($RootPath);
		$NumberOfTestExecuted++;
		WrongItemsOnWorkOrders($RootPath);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		PurchaseOrdersWrongPlannedDates($RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","IN NEGOTIATION WITH SUPPLIER", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","ON PRODUCTION", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","ON PRODUCTION", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","FINISHED BUT NOT PAID", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		POStatusControl("FORSALE","STILL NOT FULLY PAID", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","STILL NOT FULLY PAID", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		POStatusControl("","BALI PAID BUT NOT RECEIVED IN KANTOR", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","BALI RECEIVED IN KANTOR BUT NOT PAID", 0,$PeriodNow,  $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT SHIPPED BY SUPPLIER", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT RECEIVED IN AYE CARGO", 0,$PeriodNow,  $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT RECEIVED IN WANGFOONG CARGO", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","IN AYE CARGO BUT NOT SHIPPED", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","IN WANGFOONG CARGO BUT NOT SHIPPED", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","SHIPPED IN TRANSIT", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","CUSTOMS CLEARANCE", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		POStatusControl("","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_AdministrationTeam){
		OutstandingOrders("Retail", "Order", $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin ){
		OutstandingOrders("Retail", "Quotation", $RootPath);
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
		OutstandingOrders("Wholesale", "Order", $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline
		OR $KL_OperationalManager){
		OutstandingOrders("Wholesale", "Quotation", $RootPath);
		$NumberOfTestExecuted++;
	}
	
	/*
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopSupportLeader){ 
		OutstandingOrders("Consignment", "Order", $RootPath);
		OutstandingOrders("Consignment", "Quotation", $RootPath);
	}
	*/

	if ($KL_AdministrationTeam
		OR $KL_SalesTeamOnline){ 
		OnlineMarketPlacePaymentPending(0, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_SalesTeamOnline
		OR $KL_ShopSupportLeader
		OR $KL_OperationalManager){ 
		OnlineMarketPlacePaymentPending(10, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_SalesTeamOnline
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("MarketPlace", "Order", $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SalesTeamOnline){
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_PENDING, $RootPath );
		$NumberOfTestExecuted++;
	}

	if ($KL_SalesTeamOnline){
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_SHIPPED, $RootPath );
		$NumberOfTestExecuted++;
	}
 
	if ($KL_SystemAdmin 
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline){
		OnlineQuotationsFollowUp($RootPath );
		$NumberOfTestExecuted++;
		OldOnlineQuotations(1, $RootPath);
		$NumberOfTestExecuted++;
//		OutstandingOrders("Online", "Quotation", $RootPath);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesTeamOnline){ 
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_PROCESSING, $RootPath );
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline){
		OnlineOrdersFollowUp("KL-WEBSITE", 10, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_SalesTeamOnline
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("Online", "Order", $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline
		OR $KL_ShopSupportTeam){ 
		OnlineItemsOnProcess($RootPath);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_ShopSupportLeader){ 
		ItemsNotNeededInOnlineOrderButRequested($RootPath);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* Other tests     
	***************************************************************************************/
	if ($KL_ITSupport
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
		ActiveItemsWithoutPicture($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline
		OR $KL_ITSupport){
		ImagesWithoutProduct($RootPath);
		$NumberOfTestExecuted++;
		OpenCartItemsWithoutPicture($RootPath );
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam
		OR $KL_SalesTeamOnline){
		ItemsWithoutWeightOrVolume($RootPath);
		$NumberOfTestExecuted++;
		ItemsShouldBeInWebsite();
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		UsersNotLoggingIn(($_SESSION['MonthsAuditTrail'] *30)+1, "ALL_EXCEPT_SPGSUPPORT", $RootPath);
		$NumberOfTestExecuted++;
		UsersNotLoggingIn(($_SESSION['MonthsAuditTrail'] *30)+1, "SPGSUPPORT", $RootPath);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager 
		OR $KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_ShopSupportLeader 
		OR $KL_ShopManager){
		RegularTransfersToShopNotReceived('08:00:00','15:00:00', $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_ShopSupportLeader){
		TransferWithWrongInformation(15, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam){ 
		TransfersDelayed(3, $RootPath);
		$NumberOfTestExecuted++;
		ItemsCancelledInTransfers(3, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager 
		OR $KL_SalesDirector
		OR $KL_OperationalManager){
		TransfersDelayed(4, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager){
		ItemsCancelledInTransfers(3, $RootPath);
		$NumberOfTestExecuted++;
	}

	if (!$KL_SystemAdmin){
		PettyCashBalance('User');
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_OperationalManager
		OR $KL_AdministrationLeader){
		PettyCashBalance('Authorizer');
		$NumberOfTestExecuted++;
		PettyCashToBeAuthorized('Cash');
		$NumberOfTestExecuted++;
		PettyCashToBeAuthorized('Expenses');
		$NumberOfTestExecuted++;
	}

}
prnMsg("Performed ". $NumberOfTestExecuted . " control tests",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include ('includes/footer.php');


/********************************************************************************************
FUNCTIONS ONLY USED IN CONTROL BOARD
*********************************************************************************************/

function ActiveItemsNoSales($maxdays, $group, $RootPath){
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
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = GetCategoryNameFromCode($group) . _(' Items with NO sales on last ') . $maxdays . ' days and NO current PO or WO. Move to next category step';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Category') . '</th>
						<th class="SortedColumn">' . _('DOB Category') . '</th>
						<th class="SortedColumn">' . _('QOH') . '</th>
						<th class="SortedColumn">' . _('#Top Sales 30') . '</th>
						<th class="SortedColumn">' . _('#Top Sales 60') . '</th>
						<th class="SortedColumn">' . _('#Top Sales 90') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
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
					$MyRow['description'], 
					$MyRow['categoryid'], 
					ConvertSQLDate($MyRow['lastcategoryupdate']),
					locale_number_format($MyRow['quantity'],0),
					locale_number_format($MyRow['topsales30'],0),
					locale_number_format($MyRow['topsales60'],0),
					locale_number_format($MyRow['topsales90'],0)
				);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}	

function ActiveItemsWithoutPicture($RootPath){
/* EXPLAIN SQL 2014-05-21	Can't use key. Probably explained at http://stackoverflow.com/questions/11784322/why-would-mysql-not-use-keys-when-there-are-possible-keys 
2014-05-30 Fixed adding a new index disontinued+Stockid
2015-05-19 TAke out some exceptions 
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "

*/
	$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockcategory.categorydescription,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) AS qoh
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
			AND (SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) > 0
		ORDER BY stockcategory.categorydescription, stockmaster.stockid";
	$Result = DB_query($SQL);
	$ShowHeader = TRUE;

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if(!file_exists($_SESSION['part_pics_dir'] . '/' .$MyRow['stockid'].'.jpg') ) {
				if($ShowHeader){
					$TableTitleText = _('Current Items without picture in webERP and QOH > 0');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . '#' . '</th>
									<th class="SortedColumn">' . _('Category') . '</th>
									<th class="SortedColumn">' . _('Item Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('QOH') . '</th>
								</tr>
							</thead>
							<tbody>';
					$i = 1;
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$MyRow['categorydescription'],
						$CodeLink, 
						$MyRow['description'],
						locale_number_format($MyRow['qoh'],0)
						);
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function BalanceAccountControl($account, $min, $max, $Period){
	$SQL = "SELECT (bfwd + actual) as saldo, accountname
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode = '" . $account . "'
				AND chartdetails.period = ". $Period . "";
				
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	if ($MyRow['saldo'] < $min){
		$WarningTitleText = "Account " . $account . " - " . $MyRow['accountname'] . " is BELOW the minimum. Balance = " . locale_number_format($MyRow['saldo'],0) . " Minimum = " . locale_number_format($min,0);
        ShowWarningTitle($WarningTitleText);
	}
	if ($MyRow['saldo'] > $max){
		$WarningTitleText = "Account " . $account . " - " . $MyRow['accountname'] . " is OVER the maximum. Balance = " . locale_number_format($MyRow['saldo'],0) . " Maximum = " . locale_number_format($max,0);
        ShowWarningTitle($WarningTitleText);
	}
}

function BalanceListAccountControl($accountlist, $Description, $min, $max, $Period){
	$SQL = "SELECT SUM(bfwd + actual) as saldo
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode IN " . $accountlist . "
				AND chartdetails.period = ". $Period . "";
				
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	if ($MyRow['saldo'] < $min){
		$WarningTitleText = $Description . " is BELOW the minimum. Balance = " . locale_number_format($MyRow['saldo'],0) . " Minimum = " . locale_number_format($min,0);
        ShowWarningTitle($WarningTitleText);
	}
	if ($MyRow['saldo'] > $max){
		$WarningTitleText = $Description . " is OVER the maximum. Balance = " . locale_number_format($MyRow['saldo'],0) . " Maximum = " . locale_number_format($max,0);
        ShowWarningTitle($WarningTitleText);
	}
}

function CashAtShops($MinCashPerShop, $MaxCashPerShop, $MinCashAllShops, $MaxCashAllShops, $NumberOfTestExecuted, $PeriodNow){
	// while builing the list of KL POS accounts for all shops, we check one by one
	$ListAccounts = "('";
	$SQL="SELECT klposcashaccount
		FROM locations
		WHERE  locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . " 
		ORDER BY locations.locationname"; 
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)){
		$ListAccounts = $ListAccounts . $MyRow['klposcashaccount'] . "','";
		BalanceAccountControl($MyRow['klposcashaccount'], $MinCashPerShop,$MaxCashPerShop, $PeriodNow);
		$NumberOfTestExecuted++;
	}
	$ListAccounts = substr($ListAccounts, 0, -2) . ")";
	// Once we have the list of all KL POS accounts for all shops, we check the total in the system
	BalanceListAccountControl($ListAccounts, "Total Cash @ shops", $MinCashAllShops, $MaxCashAllShops, $PeriodNow);
	$NumberOfTestExecuted++;
	return $NumberOfTestExecuted;
}


function CategoryItemsMissingInShops($Category, $ShopType, $NumberOfTestExecuted, $RootPath){

	$MinQOH = NumberOfShops($ShopType);
	
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_TEST)){
		$Condition = " AND locations.alltestitems = '1' ";
	}elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_STABLE)){
		$Condition = " AND locations.allstableitems = '1' ";
	}elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)){
		$Condition = " AND locations.allnopoitems = '1' ";
	}elseif (ItemInLIst($Category, LIST_STOCK_CATEGORIES_DISCOUNT_20)){
		$Condition = " AND locations.alldisc20items = '1' ";
	}elseif (ItemInLIst($Category, LIST_STOCK_CATEGORIES_DISCOUNT_50)){
		$Condition = " AND locations.alldisc50items = '1' ";
	}elseif (ItemInLIst($Category, LIST_STOCK_CATEGORIES_DISCOUNT_80)){
		$Condition = " AND locations.alldisc80items = '1' ";
	}
	
	$SQL="SELECT loccode
		FROM locations
		WHERE typeloc = '" . $ShopType . "'" 
		. $Condition;
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)){
		CategoryItemsNotInShop($Category, $MyRow['loccode'], $MinQOH, "ALL", $RootPath);
		$NumberOfTestExecuted++;
		CategoryItemsNotInShop($Category, $MyRow['loccode'], 1, "KANTOR", $RootPath);
		$NumberOfTestExecuted++;
	}
	return $NumberOfTestExecuted;
	
}



function CategoryItemsNotInShop($Category, $Shop, $MinQOH, $WhereisQOH, $RootPath){
	
	$Exclusions = " (excluding items in Active Tranfers, Pending of Transfer, Change of Price, Move to Discount, Special Kantor Request, Service, Shop Online and Return to Supplier)";
	if ($WhereisQOH == "KANTOR"){
		$TableTitleText = GetCategoryNameFromCode($Category) . _(' items NOT in ') . $Shop . ' but with QOH >= ' . $MinQOH .' in KANTOR' . $Exclusions;
		$SQLQty = "(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode IN " . LIST_KANTOR . ")";
		$TitleQOH = "QOH Kantor";
	}else{
		$TableTitleText = GetCategoryNameFromCode($Category) . _(' items NOT in ') . $Shop . ' with QOH >= ' . $MinQOH .' in TOTAL' . $Exclusions;
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
	} else if ($Category == 'STABKA') {
		$WhereCat = " AND stockmaster.categoryid = 'STABKA' ";
	} else if ($Category == 'NOPOKA') {
		$WhereCat = " AND stockmaster.categoryid = 'NOPOKA' ";
	} else if ($Category == 'TESTBA') {
		$WhereCat = " AND stockmaster.categoryid = 'TESTBA' ";
	} else if ($Category == 'STABBA') {
		$WhereCat = " AND stockmaster.categoryid = 'STABBA' ";
	} else if ($Category == 'NOPOBA') {
		$WhereCat = " AND stockmaster.categoryid = 'NOPOBA' ";
	} else if ($Category == 'DISC2A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC2A')";
	} else if ($Category == 'DISC2B') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC2B')";
	} else if ($Category == 'DISC2G') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC2G')";
	} else if ($Category == 'DISC5A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC5A')";
	} else if ($Category == 'DISC5B') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC5B')";
	} else if ($Category == 'DISC5G') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC5G')";
	} else if ($Category == 'DISC8A') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC8A')";
	} else if ($Category == 'DISC8B') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC8B')";
	} else if ($Category == 'DISC8G') {
		$WhereCat = " AND (stockmaster.categoryid = 'DISC8G')";
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . $TitleQOH . '</th>
						<th class="SortedColumn">' . _('RL=?') . '</th>
						<th class="SortedColumn">' . _('RL=1') . '</th>
						<th class="SortedColumn">' . _('RL=2') . '</th>
						<th class="SortedColumn">' . _('RL=3') . '</th>
						<th class="SortedColumn">' . _('RL=4') . '</th>
						<th class="SortedColumn">' . _('RL=5') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . 'Manual' . '</a>';
			$LinkRL1 = '';
			$LinkRL2 = '';
			$LinkRL3 = '';
			$LinkRL4 = '';
			$LinkRL5 = '';
			if ($MyRow['qoh'] >= 1){
				$LinkRL1  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=1' . '">' . '1' . '</a>';
			}
			if ($MyRow['qoh'] >= 2){
				$LinkRL2  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=2' . '">' . '2' . '</a>';
			}
			if ($MyRow['qoh'] >= 3){
				$LinkRL3  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=3' . '">' . '3' . '</a>';
			}
			if ($MyRow['qoh'] >= 4){
				$LinkRL4  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=4' . '">' . '4' . '</a>';
			}
			if ($MyRow['qoh'] >= 5){
				$LinkRL5  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=5' . '">' . '5' . '</a>';
			}

			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					$MyRow['description'], 
					$MyRow['qoh'], 
					$ManualLink,
					$LinkRL1,
					$LinkRL2,
					$LinkRL3,
					$LinkRL4,
					$LinkRL5
			);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function CheckNegativeStock($RootPath){
	/* Check if there is any negative stock */

	$Total = 0;
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
				
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items with Negative Stock');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Location') . '</th>
						<th class="SortedColumn">' . _('Quantity') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$Total += $MyRow['quantity'];
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					$MyRow['locationname'], 
					locale_number_format($MyRow['quantity'],$MyRow['decimalplaces'])
					);
			$i++;
		}
		printf('<tr class="striped_row">
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"", 
				"TOTAL", 
				"", 
				"", 
				locale_number_format($Total,0)
				);
		echo '</tbody>
			</table>
			</div>';
	}
	InsertKPI("Stock", "Negative Stock items (PCS)", abs($Total));
}

function ConsumablesGoodsNotEnoughStock($DaysUsage, $DaysMinStock, $DaysStockPurchase, $RootPath){
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Consumables with stock ready for less than ') . $DaysMinStock . ' days and NO active PO.';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('QOH Kantor') . '</th>
						<th class="SortedColumn">' . _('Used ') . $DaysUsage . ' days'. '</th>
						<th class="SortedColumn">' . _('Urgent Needed') . '</th>
						<th class="SortedColumn">' . _('Recommended Purchase') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$Needed = (($MyRow['usageKL'] / $DaysUsage) * $DaysMinStock ) - $MyRow['qtyKANTOR'];
			$Recommended = (($MyRow['usageKL'] / $DaysUsage) * $DaysStockPurchase);
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
					$MyRow['description'], 
					locale_number_format($MyRow['qtyKANTOR'],0),
					locale_number_format($MyRow['usageKL'],0),
					locale_number_format($Needed,0),					
					locale_number_format($Recommended,0)					
					);
			$i++;
		}
		echo '</tbody>
			</table>
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
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow[0];
}

function CustomersDebtControl($AcceptedDifference, $Period){
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode = '111311100'
				AND chartdetails.period = ". $Period . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	$ValueAtBalance = $MyRow['saldo'];
	
	$DebtValueIDR = CustomerDebtByCurrency("IDR");
	$DebtValueUSD = CustomerDebtByCurrency("USD");
	$DebtValueAUD = CustomerDebtByCurrency("AUD");
	$DebtValueEUR = CustomerDebtByCurrency("EUR");
	
	$DebtValue = $DebtValueIDR + $DebtValueUSD + $DebtValueAUD + $DebtValueEUR;
	
	if (abs($ValueAtBalance - $DebtValue) > $AcceptedDifference){
		$WarningTitleText = "Customer's Debt Balance value = " . locale_number_format($ValueAtBalance,0) . 
				" <-> Customer's Debt = " . locale_number_format($DebtValue,0) . 
				" Difference = ". locale_number_format($ValueAtBalance - $DebtValue,0);
        ShowWarningTitle($WarningTitleText);
	}
}

function DiscountedItemsWithWrongDiscount($Category, $DiscountCode, $RootPath){
	$SQL = "SELECT * 
			FROM  stockmaster 
			WHERE categoryid = '" . $Category . "'
				AND discountcategory !=  '". $DiscountCode ."'
				AND discontinued = 0";
				
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Category . _(' items with wrong discount (Not ') . $DiscountCode. '%)';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function FlaggedAsObsoleteButStockAvailable($RootPath){
	/* Check if there is any item flagged as obsolete BUT with some stock available */
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description
			FROM stockmaster
			WHERE discontinued = 1 
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) > 0";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Obsolete Items with available Stock');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'] 
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function GLTransDateControl(){
	$SQL = "SELECT counterindex,
					type,
					typeno,
					account,
					narrative,
					amount
			FROM gltrans
			WHERE trandate = '0000-00-00'";
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Wrong dated GLTrans transactions in DB');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Counterindex') . '</th>
						<th class="SortedColumn">' . _('Type') . '</th>
						<th class="SortedColumn">' . _('Typeno') . '</th>
						<th class="SortedColumn">' . _('Account') . '</th>
						<th class="SortedColumn">' . _('Narrative') . '</th>
						<th class="SortedColumn">' . _('Amount') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['counterindex'],
					$MyRow['type'],
					$MyRow['typeno'],
					$MyRow['account'],
					$MyRow['narrative'],
					$MyRow['amount']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function GoodsJustArrived($Kind, $Location, $numdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays));
	$ShopsKL = NumberOfShops("SHOPKL");
	$ShopsBL = NumberOfShops("SHOPBL");
	$ShopsOU = NumberOfShops("SHOPOU");
	if ($Kind == "PO"){
		$Type = 25;
	}elseif ($Kind == "WO"){
		$Type = 26;
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
				AND stockmoves.loccode ='" . $Location . "'
				AND stockmoves.type ='" . $Type . "'
				AND stockmoves.trandate >'" . $StartDate . "'
				ORDER BY stockmoves.trandate DESC, 
						stockmoves.stockid";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($Kind == "PO"){
			$TableTitleText = $Kind . _(' Finished Goods just arrived at ') . $Location . ' during the last '. $numdays . ' days';
		}elseif ($Kind == "WO"){
			$TableTitleText = $Kind . _(' Goods just produced at ') . $Location . ' during the last '. $numdays . ' days';
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
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
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
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
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			
			// count how many shops do we need to set the RL
			if (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_KAPAL_LAUT)){
				$TypeOfShop = 'SHOPKL';
				$ShopsToSetRL = $ShopsKL;
			}elseif (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_BLINK)){
				$TypeOfShop = 'SHOPBL';
				$ShopsToSetRL = $ShopsBL;
			}elseif (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
				$TypeOfShop = 'SHOPOU';
				$ShopsToSetRL = $ShopsOU;
			}else{
				$ShopsToSetRL = 0;
			}

			if ((ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_TEST)) 
				OR (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_STABLE))
				OR (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING))
				OR (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET))) {
				$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . 'Manual' . '</a>';
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
				if ($MyRow['qtytotal'] >= 0){
					$LinkRL1All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=1' . '">' . '1' . '</a>';
					$LinkRL1Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=1' . '">' . '1' . '</a>';
				}
				if ($MyRow['qtytotal'] >= $ShopsToSetRL * 2){
					$LinkRL2All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=2' . '">' . '2' . '</a>';
					$LinkRL2Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=2' . '">' . '2' . '</a>';
				}
				if ($MyRow['qtytotal'] >= $ShopsToSetRL * 3){
					$LinkRL3All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=3' . '">' . '3' . '</a>';
					$LinkRL3Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=3' . '">' . '3' . '</a>';
				}
				if ($MyRow['qtytotal'] >= $ShopsToSetRL * 4){
					$LinkRL4All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=4' . '">' . '4' . '</a>';
					$LinkRL4Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=4' . '">' . '4' . '</a>';
				}
				if ($MyRow['qtytotal'] >= $ShopsToSetRL * 5){
					$LinkRL5All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=5' . '">' . '5' . '</a>';
					$LinkRL5Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=5' . '">' . '5' . '</a>';
				}
			}

			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					ConvertSQLDate($MyRow['trandate']),
					$MyRow['stockid'], 
					$MyRow['categoryid'], 
					$MyRow['description'], 
					locale_number_format($MyRow['qtyarrived'],0),
					locale_number_format($MyRow['qtytotal'],0),
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
		echo '</tbody>
			</table>
			</div>';
	}
}

function GoodsJustTransferred($Locationfrom, $Locationto, $numdays, $QOHmax, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays+1));

	$SQL = "SELECT loctransfers.stockid,
					stockmaster.description,
					stockcategory.categorydescription,
					loctransfers.recdate, 
					loctransfers.recqty AS qtytransferred,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = loctransfers.stockid) AS qtytotal
			FROM loctransfers, stockmaster, stockcategory
			WHERE loctransfers.stockid = stockmaster.stockid
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype = 'F'
				AND loctransfers.shiploc ='" . $Locationfrom . "'
				AND loctransfers.recloc ='" . $Locationto . "'
				AND loctransfers.recdate >'" . $StartDate . "'
				AND (SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = loctransfers.stockid) <= " . $QOHmax . "
				ORDER BY loctransfers.recdate DESC, 
						loctransfers.stockid";
						
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _(' Finished Goods just transferred from ') . $Locationfrom  . ' to '. $Locationto . ' during the last '. $numdays . ' days and QOH <= '. $QOHmax . '.';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Date') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Category') . '</th>
						<th class="SortedColumn">' . _('Transferred') . '</th>
						<th class="SortedColumn">' . _('QOH') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					ConvertSQLDate($MyRow['recdate']),
					$CodeLink, 
					$MyRow['description'], 
					$MyRow['categorydescription'], 
					locale_number_format($MyRow['qtytransferred'],0),
					locale_number_format($MyRow['qtytotal'],0)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function GoodsReceivedNotInvoicedControl($AcceptedDifference, $Period){
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode = '211021400'
				AND chartdetails.period = ". $Period . "";
// EXPLAIN SQL 2014-05-31 OK!
//prnMsg($SQL);
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	$ValueAtBalance = -$MyRow['saldo'];
	
	$SQL = "SELECT SUM((grns.qtyrecd - grns.quantityinv) * (stockmaster.actualcost))
			FROM grns, stockmaster
			WHERE stockmaster.stockid = grns.itemcode
				AND (grns.qtyrecd - grns.quantityinv) > 0";
// EXPLAIN SQL 2014-05-31
// NOT OK. All 10.000 rows each time
// prnMsg($SQL);	
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$GoodsValue = $MyRow[0];

	if (abs($ValueAtBalance - $GoodsValue) > $AcceptedDifference){
		$WarningTitleText = "Goods Received Balance value = " . locale_number_format($ValueAtBalance,0) . 
				" <-> Real Goods Received Value at Std Cost = " . locale_number_format($GoodsValue,0) .
				" Difference = ". locale_number_format($ValueAtBalance - $GoodsValue,0);;
        ShowWarningTitle($WarningTitleText);
	}
}

function PettyCashBalanceControlControl($Currency, $PCGLAccounts, $AcceptedDifference, $Period){
	$SQL = "SELECT SUM(pcashdetails.amount)/currencies.rate as amount_idr
			FROM pcashdetails,pctabs,currencies	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pctabs.currency = currencies.currabrev
				AND pctabs.currency = '". $Currency ."'
				AND pcashdetails.authorized != '0000-00-00'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$PettyCashValue = $MyRow['amount_idr'];

	$SQL = "SELECT SUM((bfwd + actual)) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode IN ".$PCGLAccounts."
				AND chartdetails.period = ". $Period . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ValueAtBalance = $MyRow['saldo'];

	if (abs($ValueAtBalance - $PettyCashValue) > $AcceptedDifference){
		$WarningTitleText = "Petty Cash (" . $Currency . ") Balance value = " . locale_number_format($ValueAtBalance,0) . 
				" <-> Real Petty Cash (" . $Currency . ") = " . locale_number_format($PettyCashValue,0) . 
				" Difference = ". locale_number_format($ValueAtBalance - $PettyCashValue,0);
        ShowWarningTitle($WarningTitleText);
	}
}


function ImagesWithoutProduct($RootPath){
	$ShowHeader = TRUE;
	$i= 0;
	// get all images in part_pics folder
	$suffix = ".jpg";
	$ImageFiles = getDirectoryTree($_SESSION['part_pics_dir']);
	foreach ($ImageFiles as $File) {
		if ($File != '.ftpquota' AND
			$File != 'Obsolete' AND
			$File != 'part_pics'){
			$StockId = substr($File, 0, strpos($File, $suffix));
			if (strpos($StockId, '.1') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.1'));
			}
			if (strpos($StockId, '.2') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.2'));
			}
			if (strpos($StockId, '.3') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.3'));
			}
			if (strpos($StockId, '.4') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.4'));
			}
			if (strpos($StockId, '.5') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.5'));
			}
			if (strpos($StockId, '.6') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.6'));
			}
			if (strpos($StockId, '.7') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.7'));
			}
			if (strpos($StockId, '.8') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.8'));
			}
			if (strpos($StockId, '.9') !== false){
				$StockId = substr($File, 0, strpos($StockId, '.9'));
			}
			$SQL = "SELECT stockid
				FROM stockmaster
				WHERE stockmaster.stockid = '" . $StockId . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0){
				if ($ShowHeader){
					$TableTitleText = _('Images without product in webERP');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('File') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				printf('<tr class="striped_row">
						<td>%s</td>
						</tr>', 
						$_SESSION['part_pics_dir'].'/'.$File
						);
			}
		}
	}
	if (!$ShowHeader){
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsCancelledInTransfers($maxdays, $RootPath){
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
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items cancelled in Transfers during the last ') . $maxdays . _(' days ');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Transfer') . '</th>
						<th class="SortedColumn">' . _('Date') . '</th>
						<th class="SortedColumn">' . _('From') . '</th>
						<th class="SortedColumn">' . _('To') . '</th>
						<th class="SortedColumn">' . _('Cancel Qty') . '</th>
						<th class="SortedColumn">' . _('Cancel Date') . '</th>
						<th class="SortedColumn">' . _('Cancelled By') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$TransferLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					ConvertSQLDateTime($MyRow['shipdate']), 
					$MyRow['shiploc'], 
					$MyRow['recloc'],
					locale_number_format($MyRow['cancelqty'],0),
					ConvertSQLDateTime($MyRow['canceldate']), 
					$MyRow['canceluserid']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsChangingPriceDelayed($NumDays, $RootPath){
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items delayed in Change Price Procedure for more than '). $NumDays . ' days. ';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Start Date') . '</th>
						<th class="SortedColumn">' . _('QOH KL Shops') . '</th>
						<th class="SortedColumn">' . _('QOH Consignment') . '</th>
						<th class="SortedColumn">' . _('Transit From Kantor') . '</th>
						<th class="SortedColumn">' . _('Transit To Kantor') . '</th>
						<th class="SortedColumn">' . _('QOH Kantor') . '</th>
						<th class="SortedColumn">' . _('QOH Others') . '</th>
						<th class="SortedColumn">' . _('QOH Total') . '</th>
						<th class="SortedColumn">' . _('New Retail Price') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$NewPriceLink = locale_number_format($MyRow['newretailprice'],0);
			
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					locale_number_format($MyRow['counterpricechange'],0),
					$CodeLink, 
					$MyRow['description'],
					ConvertSQLDate($MyRow['startprocessdate']),
					locale_number_format_zero_blank($MyRow['qohpos']-$MyRow['intransitfromshops'],0),
					locale_number_format_zero_blank($MyRow['qohconsignment']-$MyRow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($MyRow['intransitfromkantor'],0),
					locale_number_format_zero_blank($MyRow['intransitfromshops']+$MyRow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($MyRow['qohkantor']-$MyRow['intransitfromkantor'],0),
					locale_number_format_zero_blank($MyRow['qohotherlocs'],0),
					locale_number_format_zero_blank($MyRow['qohtotal'],0),
					$NewPriceLink
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsInCategoryForMoreThanDays($maxdays, $group, $RootPath){
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
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = GetCategoryNameFromCode($group) . ' Items for more than ' . $maxdays . ' days. Move to next step of cycle of life';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Category') . '</th>
						<th class="SortedColumn">' . _('DOB Category') . '</th>
						<th class="SortedColumn">' . _('QOH') . '</th>
						<th class="SortedColumn">' . _('#Top Sales 30') . '</th>
						<th class="SortedColumn">' . _('#Top Sales 60') . '</th>
						<th class="SortedColumn">' . _('#Top Sales 90') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
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
					$MyRow['description'], 
					$MyRow['categoryid'], 
					ConvertSQLDate($MyRow['lastcategoryupdate']),
					locale_number_format($MyRow['quantity'],0),
					locale_number_format($MyRow['topsales30'],0),
					locale_number_format($MyRow['topsales60'],0),
					locale_number_format($MyRow['topsales90'],0)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}	

function ItemsInmediateShortage($Cat, $RootPath){

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
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Cat . ' Items in inmediate shortage stock';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('QOH') . '</th>
						<th class="SortedColumn">' . _('Qty @ PO') . '</th>
						<th class="SortedColumn">' . _('Qty @ WO') . '</th>
						<th class="SortedColumn">' . _('Demand') . '</th>
						<th class="SortedColumn">' . _('Shortage') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
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
					locale_number_format($MyRow['qoh'],0), 
					locale_number_format($MyRow['qtypo'],0), 
					locale_number_format($MyRow['qtywo'],0), 
					locale_number_format($MyRow['directdemand']+$MyRow['wodemand'],0),
					locale_number_format($MyRow['qoh']+$MyRow['qtypo']+$MyRow['qtywo']-$MyRow['directdemand']-$MyRow['wodemand'],0)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}	


function ItemsInKLProcessAndRLNotZero($RootPath){
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items with in KL process and RL not zero');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Location') . '</th>
						<th class="SortedColumn">' . _('RL') . '</th>
						<th class="SortedColumn">' . _('Changing Price') . '</th>
						<th class="SortedColumn">' . _('MoveTo 20% Disc') . '</th>
						<th class="SortedColumn">' . _('MoveTo 50% Disc') . '</th>
						<th class="SortedColumn">' . _('MoveTo 80% Disc') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			if ($MyRow['klchangingprice'] == 1){
				$ItemChangingPrice = "Yes";
			}else{
				$ItemChangingPrice = "";
			}
			if ($MyRow['klmovingdiscount20'] == 1){
				$ItemMovingToDiscount20 = "Yes";
			}else{
				$ItemMovingToDiscount20 = "";
			}
			if ($MyRow['klmovingdiscount50'] == 1){
				$ItemMovingToDiscount50 = "Yes";
			}else{
				$ItemMovingToDiscount50 = "";
			}
			if ($MyRow['klmovingdiscount80'] == 1){
				$ItemMovingToDiscount80 = "Yes";
			}else{
				$ItemMovingToDiscount80 = "";
			}
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					$MyRow['locationname'], 
					locale_number_format($MyRow['reorderlevel'],0),
					$ItemChangingPrice,
					$ItemMovingToDiscount20,
					$ItemMovingToDiscount50,
					$ItemMovingToDiscount80
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsNotNeededInOnlineOrderButRequested($RootPath){
	
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
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "Items Not needed for any Online Order but with QOH > 0 in Shop Online";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Item Code') . '</th>
						<th class="SortedColumn">' . _('Quantity') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$ItemLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$ItemLink, 
					locale_number_format($MyRow['quantity'],0)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsInSetup($Check, $Category, $RootPath){
	$Today = date('Y-m-d');
	
	if ($Check == "ReadyToTest"){
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items ready to change to TEST";
		$SQLWhere = "AND LENGTH(stockmaster.description) > 2
					AND (SELECT SUM(locstock.quantity)
							FROM locstock
							WHERE locstock.stockid = stockmaster.stockid
								AND locstock.loccode = " . CODE_KANTOR . ") > 0
					AND (SELECT price
							FROM prices
							WHERE stockmaster.stockid = prices.stockid
								AND prices.startdate <= CURRENT_DATE 
								AND prices.enddate >= CURRENT_DATE
								AND prices.typeabbrev = 'RT'
								AND currabrev = 'IDR') IS NOT NULL
					AND NOT EXISTS (SELECT *
							FROM loctransfers 
							WHERE  pendingqty > 0
								AND loctransfers.stockid =  stockmaster.stockid)";
	}elseif($Check == "NeedDescription"){
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items needing descriptions";
		$SQLWhere ="AND LENGTH(stockmaster.description) <= 2";
	}elseif($Check == "NeedPrice"){
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items needing price";
		$SQLWhere ="AND (SELECT price
				FROM prices
				WHERE stockmaster.stockid = prices.stockid
					AND prices.typeabbrev = 'RT'
					AND currabrev = 'IDR') IS NULL";
	}elseif($Check == "WithReorderLevel"){
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items with RL (items in SETUP should not have RL set)";
		$SQLWhere ="AND (SELECT SUM(reorderlevel)
				FROM locstock
				WHERE stockmaster.stockid = locstock.stockid) > 0 ";
	}else{
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items in SETUP";
		$SQLWhere ="";
	}

	$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			(SELECT price
				FROM prices
				WHERE stockmaster.stockid = prices.stockid
					AND prices.typeabbrev = 'RT'
					AND prices.startdate <= CURRENT_DATE 
					AND prices.enddate >= CURRENT_DATE
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$i = 1;
		$ShowHeader = TRUE;
		while ($MyRow = DB_fetch_array($Result)) {
			if (    ($Check != "ReadyToTest") 
				OR (($Check == "ReadyToTest") 
					AND (file_exists($_SESSION['part_pics_dir'] . '/' .$MyRow['stockid'].'.jpg')))) {
				if ($ShowHeader){
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('Price') . '</th>
									<th class="SortedColumn">' . _('QOH') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				$RLLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . locale_number_format($MyRow['QOH'],0) . '</a>';
				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$MyRow['description'], 
						locale_number_format($MyRow['price'],0),
						$RLLink
						);
				$i++;
			}
		}
		if(!$ShowHeader){
			echo '</tbody>
					</table>
					</div>';
		}
	}
}

function ItemsInWrongShops($ShopType, $RootPath){

	if ($ShopType == "SHOPKL"){
		$TableTitleText = 'Blink or KL 80% Discount Items on KL shops';
		$Condition =  " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_DISC_20_50 . "
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_GENERAL_INCLUDING_DISC_20_50 . "
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_SHOPS_NOT_FOR_SALE . "
						AND locations.typeloc = 'SHOPKL' ";
	}elseif ($ShopType == "SHOPBL"){
		$TableTitleText = 'KL or Blink 80% Discount items on BLINK shops';
		$Condition =  " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_BLINK_INCLUDING_DISC_20_50 . "
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_GENERAL_INCLUDING_DISC_20_50 . "
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_SHOPS_NOT_FOR_SALE . "
						AND locations.typeloc = 'SHOPBL' ";
	}elseif ($ShopType == "SHOPOU"){
		$TableTitleText = 'KL or Blink full priced items on OUTLET shops';
		$Condition =  " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET . "
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_GENERAL_INCLUDING_ALL_DISCOUNT . "
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_SHOPS_NOT_FOR_SALE . "
						AND locations.typeloc = 'SHOPOU' ";
	}elseif ($ShopType == "DEFECTIVE"){
		$TableTitleText = 'Discounted -D items on KL or Blink shops';
		$Condition =  " AND UPPER(RIGHT(stockmaster.stockid,2)) = '-D'
						AND (locations.typeloc = 'SHOPKL' 
							OR locations.typeloc = 'SHOPBL')";
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Shop') . '</th>
						<th class="SortedColumn">' . _('Quantity') . '</th>
						<th class="SortedColumn">' . _('Reorder Level') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$CodeLinkRL = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['reorderlevel'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					$MyRow['loccode'], 
					$MyRow['quantity'], 
					$CodeLinkRL 
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsMovingToDiscountDelayed($TypeDiscount, $NumDays, $RootPath){
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'Items delayed Moving To ' . $TypeDiscount . '% Discount Procedure for more than '. $NumDays . ' days. ';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Start Date') . '</th>
						<th class="SortedColumn">' . _('QOH KL Shops') . '</th>
						<th class="SortedColumn">' . _('QOH Consignment') . '</th>
						<th class="SortedColumn">' . _('Transit From Kantor') . '</th>
						<th class="SortedColumn">' . _('Transit To Kantor') . '</th>
						<th class="SortedColumn">' . _('QOH Kantor') . '</th>
						<th class="SortedColumn">' . _('QOH Others') . '</th>
						<th class="SortedColumn">' . _('QOH Total') . '</th>
						<th class="SortedColumn">' . _('Discount Code') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					locale_number_format($MyRow['countermovediscount'],0),
					$CodeLink, 
					$MyRow['description'],
					ConvertSQLDate($MyRow['startprocessdate']),
					locale_number_format_zero_blank($MyRow['qohpos']-$MyRow['intransitfromshops'],0),
					locale_number_format_zero_blank($MyRow['qohconsignment']-$MyRow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($MyRow['intransitfromkantor'],0),
					locale_number_format_zero_blank($MyRow['intransitfromshops']+$MyRow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($MyRow['qohkantor']-$MyRow['intransitfromkantor'],0),
					locale_number_format_zero_blank($MyRow['qohotherlocs'],0),
					locale_number_format_zero_blank($MyRow['qohtotal'],0),
					$MyRow['discountcategory']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsOnSpecialRequest($RootPath){
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items on Special Kantor Request');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Quantity') . '</th>
						<th class="SortedColumn">' . _('Reorder Level') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					$MyRow['quantity'], 
					$MyRow['reorderlevel'] 
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsShouldBeInWebsite(){
	$SQL = "SELECT stockid, description
			FROM stockmaster
			WHERE " . SQLFilterStockmasterForOnlineShop("ALL"). "
				AND NOT EXISTS (SELECT *
								FROM salescatprod
								WHERE salescatprod.stockid = stockmaster.stockid)";
	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if(file_exists($_SESSION['part_pics_dir'] . '/' .$MyRow['stockid'].'.jpg') ) {
				if($ShowHeader){
					$TableTitleText = _('Items with picture but not available in Online Shop');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
								</tr>
							</thead>
							<tbody>';
					$i = 1;
					$ShowHeader = FALSE;
				}
				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$MyRow['stockid'], 
						$MyRow['description'] 
						);
				$i++;
			}			
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function ItemsWithStockLocationButNoStockAvailable($Location, $NameLocation, $MinAvailable, $MaxTopSalesItems, $RootPath){
	/*  EXPLAIN SQL 2014-05-30
		Examples of usage in control boards
		ItemsWithStockLocationButNoStockAvailable("WABOM", "WaterBom", 15, 600, $RootPath);
		ItemsWithStockLocationButNoStockAvailable("WHAYA", "Ayana", 15, 600, $RootPath);
		ItemsWithStockLocationButNoStockAvailable("WHINT", "InterContinental", 15, 600, $RootPath);
		InsuficientStockForItems("STABKA", "TM-", "Tali Mie", 20, 40, $RootPath);
		
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
	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	if (DB_num_rows($Result) != 0){
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$PositionTopSales = PositionTopSalesItem($MyRow['stockid'], 60);
			if($PositionTopSales <= $MaxTopSalesItems){
				if ($ShowHeader){
					$TableTitleText = $MaxTopSalesItems ._(' Top Sales Items (Exclude No More Purchasing, Discount) with stock at ') . $NameLocation . ' but KL Stock Available (Toko + Kantor) <= ' . $MinAvailable;
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('TopSale#') . '</th>
									<th class="SortedColumn">' . _('Qty ') . $Location . '</th>
									<th class="SortedColumn">' . _('QOH Available') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						locale_number_format($PositionTopSales,0),
						locale_number_format($MyRow['quantity'],0),
						locale_number_format($MyRow['available'],0)
						);
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function ItemsWithoutPurchasingData($RootPath){
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items without full purchasing data');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Supplier') . '</th>
						<th class="SortedColumn">' . _('Date') . '</th>
						<th class="SortedColumn">' . _('Supplier Part #') . '</th>
						<th class="SortedColumn">' . _('Supplier Description') . '</th>
						<th class="SortedColumn">' . _('UOM') . '</th>
						<th class="SortedColumn">' . _('Leadtime') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $MyRow['stockid'] . '">'. $MyRow['stockid'] .'</a>';
			$SupplierLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $MyRow['stockid'] . 
															'&SupplierID=' . $MyRow['supplierno'] . 
															'&Edit=1' .
															'&EffectiveFrom=' . $MyRow['latesteffectivefrom'] . '">'. $MyRow['supplierno'] .'</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					$MyRow['latesteffectivefrom'],
					$MyRow['suppliers_partno'],
					$MyRow['supplierdescription'],
					$MyRow['suppliersuom'],
					locale_number_format($MyRow['leadtime'],0)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsWithoutStandardCost($RootPath){
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items without standard cost');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('QOH') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					locale_number_format($MyRow['availablestock'],0)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsWithoutWeightOrVolume($RootPath){
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Online Shop items with no gross weight, no volume or Net > Gross Weight');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Net Weight Kg') . '</th>
						<th class="SortedColumn">' . _('Gross Weight Kg') . '</th>
						<th class="SortedColumn">' . _('Volume m3') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/Stocks.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					locale_number_format($MyRow['netweight'],5), 
					locale_number_format($MyRow['grossweight'],5), 
					locale_number_format($MyRow['volume'],5)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsWithStockKantorButReorderLevelTokoZero($TypeOfShop, $RootPath){
/**********************************************************************
items with stock kantor > 0 
RL is zero at one type of shop
No pending transfer regarding this item

2013-04-16 excluding items in change price process
2013-04-25 excluding items in move to discount / outlet process 
2014-12-02 excluding items in OLD categories

***********************************************************************/

	$ShopsToSetRL = NumberOfShops($TypeOfShop);
	if ($TypeOfShop == "SHOPKL"){
		$Message = 'KAPAL-LAUT';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . ")";
	}elseif ($TypeOfShop == "SHOPBL"){
		$Message = 'BLINK';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . ")";
	}elseif ($TypeOfShop == "SHOPOU"){
		$Message = 'OUTLET';
		$Condition =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . ")";
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Message . ' Items with stock available (but NO changing price or category) at Kantor but RL zero for all ' . $Message . '  SHOPS';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
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
					</tr>
					<tr>
						<th class="SortedColumn"></th>
						<th class="SortedColumn"></th>
						<th class="SortedColumn"></th>
						<th class="SortedColumn"></th>
						<th class="SortedColumn"></th>
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
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . 'Manual' . '</a>';
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
				if ($MyRow['QtyKantor'] >= 0){
					$LinkRL1All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=1' . '">' . '1' . '</a>';
					$LinkRL1Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=1' . '">' . '1' . '</a>';
				}
				if ($MyRow['QtyKantor'] >= $ShopsToSetRL * 2){
					$LinkRL2All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=2' . '">' . '2' . '</a>';
					$LinkRL2Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=2' . '">' . '2' . '</a>';
				}
				if ($MyRow['QtyKantor'] >= $ShopsToSetRL * 3){
					$LinkRL3All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=3' . '">' . '3' . '</a>';
					$LinkRL3Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=3' . '">' . '3' . '</a>';
				}
				if ($MyRow['QtyKantor'] >= $ShopsToSetRL * 4){
					$LinkRL4All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=4' . '">' . '4' . '</a>';
					$LinkRL4Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=4' . '">' . '4' . '</a>';
				}
				if ($MyRow['QtyKantor'] >= $ShopsToSetRL * 5){
					$LinkRL5All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=5' . '">' . '5' . '</a>';
					$LinkRL5Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=5' . '">' . '5' . '</a>';
				}
			}
			printf('<tr class="striped_row">
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
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['categoryid'], 
					$MyRow['description'], 
					locale_number_format($MyRow['QtyKantor'],0),
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
		echo '</tbody>
			</table>
			</div>';
	}
}

function NotDiscountedItemsWithDiscount($RootPath){
	$SQL = "SELECT stockid,
					description
			FROM  stockmaster 
			WHERE   categoryid NOT IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS ."
				AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET ."
				AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_OLD ."
				AND discountcategory !=  ''
				AND discontinued = 0";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Not Discounted items with discount');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ObsoleteComponentsInActiveBOM($RootPath){

	$SQL = "SELECT bom.parent,
				bom.component
			FROM bom, stockmaster AS stP, stockmaster AS stC
			WHERE bom.parent = stP.stockid 
				AND bom.component = stC.stockid
				AND stP.discontinued = 0
				AND stC.discontinued = 1";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Active BOM with obsolete components');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('BOM of') . '</th>
						<th class="SortedColumn">' . _('Component') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLinkParent = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['parent'] . '">' . $MyRow['parent'] . '</a>';
			$CodeLinkComponent = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['component'] . '">' . $MyRow['component'] . '</a>';
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$CodeLinkParent, 
					$CodeLinkComponent
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OldOnlineQuotations($NumDaysBank, $RootPath){

	$StartDateBank = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysBank));
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "Old Online Quotations to be deleted. No Payment received in more than " . $NumDaysBank . " days.";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Order') . '</th>
						<th class="SortedColumn">' . _('#KL-Website') . '</th>
						<th class="SortedColumn">' . _('Customer') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('Order Date') . '</th>
						<th class="SortedColumn">' . _('Order Value') . '</th>
						<th class="SortedColumn">' . _('Currency') . '</th>
						<th class="SortedColumn">' . _('Payment Method') . '</th>
						<th class="SortedColumn">' . _('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			$PaymentMethodText = GetPaymentMethodTextFromCode($MyRow['klocpaymentcode']);
			$DeleteLink = '<a href="' . $RootPath . '/KLDeleteSalesOrder.php?OrderNo=' . $MyRow['orderno'] . '">' . 'Delete as Expired' . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					locale_number_format($MyRow['customerref']),
					$MyRow['debtorno'], 
					$MyRow['name'], 
					ConvertSQLDate($MyRow['orddate']), 
					locale_number_format($MyRow['ordervalue']+$MyRow['freightcost'],$MyRow['decimalplaces']),
					$MyRow['currcode'], 
					$PaymentMethodText, 
					$DeleteLink
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OldPOStillActive($maxdays, $RootPath){
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('POs older than ') . $maxdays . _(' days and still not closed');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('PO') . '</th>
						<th class="SortedColumn">' . _('Date') . '</th>
						<th class="SortedColumn">' . _('Supplier') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/PO_OrderDetails.php?OrderNo=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					ConvertSQLDate($MyRow['orddate']), 
					$MyRow['supplierno']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OldWOStillActive($maxdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT wo,
				   startdate
			FROM workorders 
			WHERE closed = 0
			AND startdate <= '". $StartDate ."'
			ORDER BY wo";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('WOs older than ') . $maxdays . _(' days and still not closed');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('WO') . '</th>
						<th class="SortedColumn">' . _('Date') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/WorkOrderEntry.php?WO=' . $MyRow['wo'] . '">' . $MyRow['wo'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					ConvertSQLDate($MyRow['startdate'])
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OnlineCustomersNoOrderPlaced($RootPath){
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Online Customers registered but no order placed.');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Customer') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('Country') . '</th>
						<th class="SortedColumn">' . _('Currency ') . '</th>
						<th class="SortedColumn">' . _('Registered on') . '</th>
						<th class="SortedColumn">' . _('Send Email') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . $MyRow['debtorno'] . '">' . $MyRow['debtorno'] . '</a>';
			$EmailLinkText = 'Send Now';
			$EmailType = 'NoOrderPlaced';
			$EmailLink = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['debtorno'] . '&EmailType=' . $EmailType. '&CustomerOrder=' . $MyRow['debtorno'] . '">'. $EmailLinkText .'</a>';
			printf('<tr class="striped_row">
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
					$MyRow['name'], 
					$MyRow['address6'], 
					$MyRow['currcode'], 
					ConvertSQLDateTime($MyRow['clientsince']), 
					$EmailLink				
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OnlineItemsOnProcess($RootPath){
	
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
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "Items on process for Online Orders";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
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
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		$OrderInProcess = -1;
		$OrderReadyForShipment = true;
		while ($MyRow = DB_fetch_array($Result)) {
			if (($OrderInProcess != $MyRow['orderno']) AND ($OrderInProcess != -1)){
				// We just checked all items in the order, and it is not the first one
				if ($OrderReadyForShipment){
					$Status = "ORDER READY FOR SHIPMENT";
				}else{
					$Status = "ORDER IN PROCESS";
				}
				printf('<tr class="striped_row">
						<td>%s</td>
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
			
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			$ItemLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stkcode'] . '">' . $MyRow['stkcode'] . '</a>';
			
			if (($MyRow['qtyready'] >= $MyRow['qtyorder']) OR (!ItemInList($MyRow['categoryid'], ONLINESHOP_AVAILABLE_STOCK_CATEGORIES))){
				// item ready to ship
				$Status = "";
			}elseif($MyRow['qtyorder'] > $MyRow['qohkantor']){
				// QOH kantor not enough to cover the order, so we need to get some from the shops
				$Status = "Needs return from shops";
				$OrderReadyForShipment = false;
			}else{
				// QOH kantor enough to cover the requirements of the order
				$Status = "In process kantor";
				$OrderReadyForShipment = false;
			}
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					$MyRow['debtorno'], 
					$MyRow['name'], 
					ConvertSQLDate($MyRow['orddate']), 
					$ItemLink, 
					locale_number_format($MyRow['qtyorder'],0),
					locale_number_format($MyRow['qtyready'],0),
					locale_number_format($MyRow['qohkantor'],0),
					$Status
					);
			$i++;
			$OrderInProcess = $MyRow['orderno'];
		}
		// status of the last order online
		if ($OrderReadyForShipment){
			$Status = "ORDER READY FOR SHIPMENT";
		}else{
			$Status = "ORDER IN PROCESS";
		}
		printf('<tr class="striped_row">
				<td>%s</td>
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

		echo '</tbody>
			</table>
			</div>';
	}
}

function OnlineOrdersFollowUp($Source, $numDays, $RootPath){

	$TableTitleText = "Follow up Outstanding " . $Source. " Online Orders";
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . '#' . '</th>
						<th class="SortedColumn">' . _('webERP Order') . '</th>
						<th class="SortedColumn">' . '#' . $Source . '</th>
						<th class="SortedColumn">' . _('Customer') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('Order Date') . '</th>
						<th class="SortedColumn">' . _('Order Value') . '</th>
						<th class="SortedColumn">' . _('Currency') . '</th>
						<th class="SortedColumn">' . _('Payment Confirmation') . '</th>
						<th class="SortedColumn">' . _('Tracking Number') . '</th>
						<th class="SortedColumn">' . _('Tracking Confirmation') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			
			$EmailType3 = "ThankYouOrder";
			$EmailType4 = "NoSendThankYou";
			if ($MyRow['klemailthankyouorder']== '0000-00-00'){
				$EmailLinkText3 = 'Send now';
				$EmailLinkText4 = 'Do NOT send';
				$EmailLink3 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['orderno'] . '&EmailType=' . $EmailType3. '&CustomerOrder=' . $MyRow['customerref'] . '">'. $EmailLinkText3 .'</a>';
				$EmailLink4 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['orderno'] . '&EmailType=' . $EmailType4. '&CustomerOrder=' . $MyRow['customerref'] . '">'. $EmailLinkText4 .'</a>';
			}else{
				$EmailLink3 = ConvertSQLDate($MyRow['klemailthankyouorder']);
				$EmailLink4 = ConvertSQLDate($MyRow['klemailthankyouorder']);
			}

			$EmailType2 = "TrackingConfirmation";
			if ($MyRow['klemailtrackingconfirm']== '0000-00-00'){
				$EmailLinkText = 'Send now';
				$EmailLink2 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['orderno'] . '&EmailType=' . $EmailType2. '&CustomerOrder=' . $MyRow['customerref'] . '">'. $EmailLinkText .'</a>';
				$EmailLink3 = 'Tracking Confirmation first';
				$EmailLink4 = 'Tracking Confirmation first';
			}else{
				$EmailLink2 = ConvertSQLDate($MyRow['klemailtrackingconfirm']);
			}
			
			$EmailType1 = "PaymentConfirmation";
			if ($MyRow['klemailpaymentconfirm']== '0000-00-00'){
				$EmailLinkText = 'Send now';
				$EmailLink1 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['orderno'] . '&EmailType=' . $EmailType1. '&CustomerOrder=' . $MyRow['customerref'] . '">'. $EmailLinkText .'</a>';
				$EmailLink2 = 'Payment Confirmation first';
				$EmailLink3 = 'Payment Confirmation first';
				$EmailLink4 = 'Payment Confirmation first';
			}else{
				$EmailLink1 = ConvertSQLDate($MyRow['klemailpaymentconfirm']);
			}

			if ($Source == "LAZADA"){
				$EmailLink1 = '';
				$EmailLink2 = '';
			}
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					locale_number_format($MyRow['customerref']),
					$MyRow['debtorno'], 
					$MyRow['name'], 
					ConvertSQLDate($MyRow['orddate']), 
					locale_number_format($MyRow['ordervalue']+$MyRow['freightcost'],$MyRow['decimalplaces']),
					$MyRow['currcode'], 
					$EmailLink1,
					$MyRow['shippername'] . ' ' . $MyRow['consignment'],
					''
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OnlineQuotationsFollowUp($RootPath ){

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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Titletext;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Order') . '</th>
						<th class="SortedColumn">' . _('#KL-Website') . '</th>
						<th class="SortedColumn">' . _('Customer') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('Order Date') . '</th>
						<th class="SortedColumn">' . _('Order Value') . '</th>
						<th class="SortedColumn">' . _('Currency') . '</th>
						<th class="SortedColumn">' . _('Payment Method') . '</th>
						<th class="SortedColumn">' . _('OC Status') . '</th>
						<th class="SortedColumn">' . _('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';

			$PaymentLinkText = 'Apply Payment';
			$PaymentValue = $MyRow['ordervalue']+$MyRow['freightcost'];

			// prepare the links according to the payment code from OpenCart
			$PaymentLink = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $MyRow['orderno'] . '&PaymentCode=' . $MyRow['klocpaymentcode'] . '&CustomerCode=' . $MyRow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
			$PaymentMethodText = GetPaymentMethodTextFromCode($MyRow['klocpaymentcode']);

			$OCStatusText = GetOpenCartStatusTextFromCode($MyRow['klocorderstatus']);

			if ($OCStatusText != "Processing"){
				$PaymentLink = ''; // do not allow Apply payment in case of an status that is not processing
			}
			
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					locale_number_format($MyRow['customerref']),
					$MyRow['debtorno'], 
					$MyRow['name'], 
					ConvertSQLDate($MyRow['orddate']), 
					locale_number_format($MyRow['ordervalue']+$MyRow['freightcost'],$MyRow['decimalplaces']),
					$MyRow['currcode'], 
					$PaymentMethodText,
					$OCStatusText,
					$PaymentLink
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OpenCartItemsWithoutPicture($RootPath ){

	$SQL = "SELECT 	oc_product.model AS stockid
			FROM oc_product
			WHERE oc_product.status = 1
			ORDER BY oc_product.model";
	$Result = DB_query_oc($SQL);
	$ShowHeader = TRUE;

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if(!file_exists(ABSOLUTE_PATH_OPENCART_IMAGES .$MyRow['stockid'].'.jpg') ) {
				if($ShowHeader){
					$TableTitleText = _('Online Shop Items without picture');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . '#' . '</th>
									<th class="SortedColumn">' . _('Item Code') . '</th>
								</tr>
							</thead>
							<tbody>';
					$i = 1;
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$CodeLink
						);
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function OutstandingOrders($customertype, $Ordertype, $RootPath){
	/* Check if there are outstanding orders for retail customers */

	if ($customertype == "Retail"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")";
		$Namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Retail";
		$WebsiteIDName = "";
	}elseif ($customertype == "Consignment"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_CONSIGNMENT . ")";
		$Namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Consignment";
		$WebsiteIDName = "";
	}elseif ($customertype == "Wholesale"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WHOLESALE . ")";
		$Namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Wholesale";
		$WebsiteIDName = "";
	}elseif ($customertype == "Online"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")";
		$Namefield = " salesorders.deliverto AS name ";
		$Titletext = "Outstanding Online";
		$WebsiteIDName = "#KL-Website";
	}elseif ($customertype == "MarketPlace"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_MARKETPLACE . ")";
		$Namefield = " salesorders.deliverto AS name ";
		$Titletext = "Outstanding MarketPlace";
		$WebsiteIDName = "#KL-Website";
	}else{
		$Namefield = " debtorsmaster.name ";
		$Whereclause = " ";
		$Titletext = _('Outstanding');
		$WebsiteIDName = "";
	}
	
	if ($Ordertype == "Quotation"){
		$Whereclause = $Whereclause . " AND salesorders.quotation = 1 ";
		$Titletext = $Titletext . " Quotations";
	}elseif  ($Ordertype == "Order"){
		$Whereclause = $Whereclause . " AND salesorders.quotation = 0 ";
		$Titletext = $Titletext . " Orders";
	}else{
		$Titletext = _(' Orders and Quotations');
	}
	
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				debtorsmaster.debtorno, "
			   . $Namefield . ",
				salesorders.orddate,
                SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
			FROM salesorders INNER JOIN salesorderdetails 	
				ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
				ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 0 "
			. $Whereclause .
			" GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Titletext;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Order') . '</th>
						<th class="SortedColumn">' . $WebsiteIDName . '</th>
						<th class="SortedColumn">' . _('Customer') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('Order Date') . '</th>
						<th class="SortedColumn">' . _('Total Value') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		$TotalValue = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			if ($customertype == "Online"){
				$WebsiteID = locale_number_format($MyRow['customerref']);
			}else{
				$WebsiteID = "";
			}

			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					$MyRow['debtorno'], 
					$MyRow['name'], 
					ConvertSQLDate($MyRow['orddate']), 
					locale_number_format($MyRow['ordervalue'],0)
					);
			$TotalValue += $MyRow['ordervalue'];
			$i++;
		}
		printf('<tr class="striped_row">
				<td>%s</td>
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
		echo '</tbody>
			</table>
			</div>';
	}
}

function over_or_below_limit($Request, $Sign, $Limit, $RootPath){
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
	
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	if ($Sign == "OVER"){
		if ($MyRow[0] > $Limit){
			$Text = $Request . " is OVER the maximum. Current value = " . locale_number_format($MyRow[0],0) . " Maximum = " . locale_number_format($Limit,0);
			ShowWarningTitle($Text);
		}
	}
	if ($Sign == "BELOW"){
		if ($MyRow[0] < $Limit){
			$Text = $Request . " is BELOW the minimum. Current value = " . locale_number_format($MyRow[0],0) . " Minimum = " . locale_number_format($Limit,0);
			ShowWarningTitle($Text);
		}
	}
}

function MinimumOutletStockAvailable($MinModels20, $MinModels50, $MinModels80, $NumberOfTestExecuted){
	$SQL="SELECT loccode,
			locationname
		FROM locations
		WHERE typeloc = 'SHOPOU'";
	$Result = DB_query($SQL);
	while ($MyShop = DB_fetch_array($Result)){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_20 . "
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$MyShop['loccode']."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow[0] < $MinModels20){
			$Text = "Discount 20% avaliable at " . $MyShop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($MyRow[0],0) . " Minimum = " . locale_number_format($MinModels20,0);
			ShowWarningTitle($Text);
		}
		$NumberOfTestExecuted++;

		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_50 . "
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$MyShop['loccode']."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow[0] < $MinModels50){
			$Text = "Discount 50% avaliable at " . $MyShop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($MyRow[0],0) . " Minimum = " . locale_number_format($MinModels50,0);
			ShowWarningTitle($Text);
		}
		$NumberOfTestExecuted++;

		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_80 . "
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$MyShop['loccode']."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow[0] < $MinModels80){
			$Text = "Discount 80% avaliable at " . $MyShop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($MyRow[0],0) . " Minimum = " . locale_number_format($MinModels80,0);
			ShowWarningTitle($Text);
		}
		$NumberOfTestExecuted++;
	}
	return $NumberOfTestExecuted;
}

function OvestockAtSamples($maxallowedsamples, $RootPath){

	$SQL = "SELECT locstock.stockid, 
					stockmaster.description, 
					quantity AS qty
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND loccode = 'SAMPR'
				AND quantity > '". $maxallowedsamples."'
			ORDER BY locstock.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Overstock of samples');
		ShowTableTitle($TableTitleText);
		$TableHeader = '<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Code') . '</th>
							<th class="SortedColumn">' . _('Description') . '</th>
							<th class="SortedColumn">' . _('Qty of samples') . '</th>
						</tr>';
		echo '<div>';
		echo '<table class="selection">
				<thead>' . $TableHeader . '</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					locale_number_format($MyRow['qty'],0)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function PackagingItemsOnWrongLocation($RootPath){
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

			$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'Packaging items in wrong locations (must be transferred to another location)';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Shop') . '</th>
						<th class="SortedColumn">' . _('Quantity') . '</th>
						<th class="SortedColumn">' . _('RL') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					$MyRow['loccode'], 
					$MyRow['quantity'],
					$MyRow['reorderlevel']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function PettyCashBalance($TypeUser){

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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		
		if ($TypeUser == "Authorizer"){
			$TableTitleText = _('Petty Cash Accounts you AUTHORIZE with balance too Low or Too High');
		}else{
			$TableTitleText = _('Petty Cash Balance you USE with balance too Low or Too High');
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('PC Tab Code') . '</th>
						<th class="SortedColumn">' . _('Amount') . '</th>
						<th class="SortedColumn">' . _('Currency') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$MyRow['tabcode'], 
					locale_number_format($MyRow['amount'],0),
					$MyRow['currency']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function PettyCashToBeAuthorized($AuthorizationType){

	if ($AuthorizationType == "Cash"){
		$TableTitleText = "Petty Cash Assignations to be Authorized";
		$SQLAuthority = "AND pctabs.authorizer LIKE '%" . $_SESSION['UserID'] . "%'
						AND pcashdetails.codeexpense = 'ASSIGNCASH'";
	}else{
		$TableTitleText = "Petty Cash Expenses to be Authorized";
		$SQLAuthority = "AND pctabs.authorizerexpenses LIKE '%" . $_SESSION['UserID'] . "%'
						AND pcashdetails.codeexpense != 'ASSIGNCASH'";
	}
	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount,
				pctabs.currency
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pcashdetails.authorized = '0000-00-00'" .
				$SQLAuthority . "
			GROUP BY pcashdetails.tabcode";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('PC Tab Code') . '</th>
						<th class="SortedColumn">' . _('Amount') . '</th>
						<th class="SortedColumn">' . _('Currency') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$MyRow['tabcode'], 
					locale_number_format($MyRow['amount'],0),
					$MyRow['currency']
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function RegularTransfersToShopNotReceived($PreparationTime, $LimitTime, $RootPath){

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
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) != 0){
			$TableTitleText = 'Transfers to Shops prepared before ' . Date($_SESSION['DefaultDateFormat']) . 
																		' at ' . $PreparationTime . ' but not received by SPG before ' . $LimitTime;
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">
					<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Transfer') . '</th>
							<th class="SortedColumn">' . _('Date') . '</th>
							<th class="SortedColumn">' . _('From') . '</th>
							<th class="SortedColumn">' . _('To') . '</th>
						</tr>
					</thead>
					<tbody>';
			$i = 1;
			while ($MyRow = DB_fetch_array($Result)) {
				$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						ConvertSQLDateTime($MyRow['shipdate']), 
						$MyRow['shiploc'], 
						$MyRow['recloc'] 
						);
				$i++;
			}
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function SamplesNotLongerNeeded($RootPath){

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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Samples Not Longer Needed (No More Buy, Discount, Outlet)');
		ShowTableTitle($TableTitleText);
		$TableHeader = '<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Code') . '</th>
							<th class="SortedColumn">' . _('Description') . '</th>
							<th class="SortedColumn">' . _('Qty of samples') . '</th>
						</tr>';
		echo '<div>';
		echo '<table class="selection">
				<thead>' . $TableHeader . '</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					locale_number_format($MyRow['qty'],0)
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function SPGNotReportingSalesInDays($maxdays){
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText =  _('Senior or Support SPG with more than ') . $maxdays . _(' days not reporting ANY sales.');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' .  _('SPG') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('Shop') . '</th>
						<th class="SortedColumn">' . _('Last Sale') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($MyRow['lastsale'])){
				$Day = ConvertSQLDate($MyRow['lastsale']);
			}else{
				$Day = "No sale yet";
			}
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$MyRow['salesmancode'],
					$MyRow['salesmanname'],
					$MyRow['defaultlocation'],
					$Day
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

function SuppliersWithoutBasicData($RootPath){

	$SQL = "SELECT supplierid,
					suppname
			FROM suppliers
			WHERE address6 = ''";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Suppliers without basic data');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$MyRow['supplierid'], 
					$MyRow['suppname'] 
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function TransferWithWrongInformation($maxdays, $RootPath){
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
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Transfers With Wrong Information during the last ') . $maxdays  . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Reception Date') . '</th>
						<th class="SortedColumn">' . _('Transfer') . '</th>
						<th class="SortedColumn">' . _('From') . '</th>
						<th class="SortedColumn">' . _('To') . '</th>
						<th class="SortedColumn">' . _('Item') . '</th>
						<th class="SortedColumn">' . _('Shipped Qty') . '</th>
						<th class="SortedColumn">' . _('Received Qty') . '</th>
						<th class="SortedColumn">' . _('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$ResultTx = DB_Txn_Begin();
		$LastStockid = "";
		$LastTransfer = "";
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
			if (($MyRow['stockid'] != $LastStockid) OR ($MyRow['reference'] != $LastTransfer)){
				$SQL = "UPDATE loctransfers SET shipqty = recqty 
						WHERE loctransferid = '".$MyRow['loctransferid'] . "'";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('Unable to fix the wrong information');
				$ResultFix = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				$Action = "Fixed"; 
			}else{
				$SQL = "DELETE FROM loctransfers 
						WHERE loctransferid = '".$MyRow['loctransferid'] . "'";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('Unable to delete the wrong information');
				$ResultDelete = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				$Action = "Deleted";
			}
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					locale_number_format($MyRow['loctransferid'],0), 
					ConvertSQLDateTime($MyRow['recdate']), 
					$CodeLink, 
					$MyRow['locfrom'], 
					$MyRow['locto'], 
					$MyRow['stockid'], 
					locale_number_format($MyRow['shippedqty'],0),
					locale_number_format($MyRow['receivedqty'],0),
					$Action
					);
			$LastStockid = $MyRow['stockid'];
			$LastTransfer = $MyRow['reference'];
		}
		$ResultTx = DB_Txn_Commit();
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

function UsersNotLoggingIn($maxdays, $Type, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays-1)) ;
	
	if ($Type=='SPGSUPPORT'){
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
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($Type=='SPGSUPPORT'){
			$TableTitleText = _('SPG Support webERP users not logging in for more than ') . $maxdays . _(' days.');
		}else{
			$TableTitleText = _('Regular webERP users not logging in for more than ') . $maxdays . _(' days.');
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' .  _('User ID') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('Last Login') . '</th>
						<th>' . _('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/WWW_User_Delete.php?UserID=' . $MyRow['userid'] . '">' . 'Delete' . '</a>';
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$MyRow['userid'],
					$MyRow['realname'],
					ConvertSQLDate($MyRow['lastvisitdate']),
					$CodeLink
					);
			$i++;
		}
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

function ValueStockLocation($Location, $minpcs, $maxpcs, $minvalue, $maxvalue){
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
				AND locations.loccode = '" . $Location . "'
				AND stockmaster.categoryid=stockcategory.categoryid
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES . "
				AND locstock.quantity!=0
				AND locstock.loccode = '" . $Location . "'";
				
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	if ($MyRow['qtyonhand'] < $minpcs){
		$WarningTitleText = "Number of items at " . $MyRow['locationname'] . " is BELOW the minimum. QOH = " . locale_number_format($MyRow['qtyonhand'],0) . " pcs. Minimum = " . locale_number_format($minpcs,0) . " pcs";
        ShowWarningTitle($WarningTitleText);
	}
	if ($MyRow['qtyonhand'] > $maxpcs){
		$WarningTitleText = "Number of items at " . $MyRow['locationname'] . " is OVER the maximum. QOH = " . locale_number_format($MyRow['qtyonhand'],0) . " pcs. Maximum = " . locale_number_format($maxpcs,0) . " pcs";
        ShowWarningTitle($WarningTitleText);
	}
}

function WrongItemsOnPurchaseOrders($RootPath){
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

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	if (DB_num_rows($Result) != 0){
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if (TRUE){
				if ($ShowHeader){
					$TableTitleText = _('Wrong items (No More Purchasing, Discount or Obsolete) in Active POs');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('PO') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('QOO') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $MyRow['itemcode'] . '">' . $MyRow['itemcode'] . '</a>';
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						locale_number_format($MyRow['orderno'],0),
						$CodeLink, 
						$MyRow['description'],
						locale_number_format($MyRow['quantityord'],0)
						);
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function WrongItemsOnWorkOrders($RootPath){
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

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	if (DB_num_rows($Result) != 0){
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if (TRUE){
				if ($ShowHeader){
					$TableTitleText = _('Wrong items (No More Purchasing, Discount or Obsolete) in Active Work Orders');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('WO') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('Qty') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						locale_number_format($MyRow['wo'],0),
						$MyRow['stockid'],
						$MyRow['description'],
						locale_number_format($MyRow['qtyreqd'],0)
						);
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function OpenCartOrdersByStatus($Status, $RootPath ){
	$SQL = "SELECT 	oc_order.order_id,
				oc_order.store_name,
				oc_order.firstname,
				oc_order.lastname,
				oc_order.currency_code,
				oc_order.date_modified
			FROM oc_order
			WHERE oc_order.order_status_id = '" . $Status . "'
			ORDER BY oc_order.date_modified";
	$Result = DB_query_oc($SQL);
	if (DB_num_rows($Result) != 0){
		$ShowHeader = TRUE;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowHeader){
				if ($Status == OPENCART_ORDER_STATUS_PENDING){
					$StatusText = "Pending";
				}else if ($Status == OPENCART_ORDER_STATUS_PROCESSING){
					$StatusText = "Processing";
				}else if ($Status == OPENCART_ORDER_STATUS_SHIPPED){
					$StatusText = "Shipped";
				}else{
					$StatusText = "Unknown";
				}
				$TableTitleText = $StatusText .' OpenCart Online Orders';
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('#Order') . '</th>
								<th class="SortedColumn">' . _('Last Modification') . '</th>
								<th class="SortedColumn">' . _('Shop') . '</th>
								<th class="SortedColumn">' . _('Customer name') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}
			if ($MyRow['currency_code'] == "IDR"){
				$RoundingDecimals = 0;
			}else{
				$RoundingDecimals = 2;
			}
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					locale_number_format($MyRow['order_id'],0),
					ConvertSQLDateTime($MyRow['date_modified']), 
					$MyRow['store_name'],
					$MyRow['firstname'] . " " . $MyRow['lastname']
					);
			$i++;
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function GetBalanceAccount($account, $Period){
	$SQL = "SELECT (bfwd + actual) as saldo
		FROM chartdetails, chartmaster
		WHERE chartdetails.accountcode = chartmaster.accountcode
			AND chartdetails.accountcode = '" . $account . "'
			AND chartdetails.period = ". $Period . "";
				
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow['saldo'];
}

function InternalBankTransfers($Company, 
							$DanamonAccount, $DanamonMin, $DanamonMax,
							$MandiriAccount, $MandiriMin, $MandiriMax,
							$BCAAccount, $BCAMin, $BCAMax,
							$BNIAccount, $BNIMin, $BNIMax, 
							$TokopediaAccount, $TokopediaMin, $TokopediaMax, 
							$ShopeeAccount, $ShopeeMin, $ShopeeMax, 
							$MidtransAccount, $MidtransMin, $MidtransMax, 
							$TransferBlockFromBank,
							$TransferBlockFromOnline,
							$Period){

	$SaldoDanamon = GetBalanceAccount($DanamonAccount, $Period);
	if ($SaldoDanamon <= $DanamonMin){
		// Danamon is below minimum balance... transfer from other banks until the Max Danamon
		$TransferNeededDanamon = $DanamonMax - $SaldoDanamon;

		// let's check if we can transfer from any bank account in order of preference
		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$TokopediaAccount, 
															"Tokopedia",
															$TokopediaMin, 
															$TokopediaMax,
															$TransferBlockFromOnline,
															$Period
															);
		
		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$ShopeeAccount, 
															"Shopee",
															$ShopeeMin, 
															$ShopeeMax,
															$TransferBlockFromOnline,
															$Period
															);

		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$MidtransAccount, 
															"Midtrans",
															$MidtransMin, 
															$MidtransMax,
															$TransferBlockFromOnline,
															$Period
															);
		
		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$MandiriAccount, 
															"Mandiri",
															$MandiriMin, 
															$MandiriMax,
															$TransferBlockFromBank,
															$Period
															);

		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$BCAAccount, 
															"BCA",
															$BCAMin, 
															$BCAMax,
															$TransferBlockFromBank,
															$Period
															);

		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$BNIAccount, 
															"BNI",
															$BNIMin, 
															$BNIMax,
															$TransferBlockFromBank,
															$Period
															);
	}
}

function CalculateTransferFromBankToDanamon($Company, 
											$TransferNeededDanamon,
											$Account, 
											$AccountName,
											$SaldoMin, 
											$SaldoMax,
											$TransferBlock,
											$Period){
	if($TransferNeededDanamon > 0){
		$Saldo = GetBalanceAccount($Account, $Period);
		if ($Saldo >= $SaldoMax){
			$AvailableForTransfer = $Saldo - $SaldoMin;
			$Transfer = min($AvailableForTransfer, $TransferNeededDanamon);
			$Transfer = round_down_multiple_of($Transfer, $TransferBlock);
			if ($Transfer > 0){
				$WarningTitleText = "Transfer ".locale_number_format($Transfer,0)." IDR from " . $AccountName.  " " . $Company . " to Danamon ". $Company;
   				ShowWarningTitle($WarningTitleText);
				$TransferNeededDanamon = $TransferNeededDanamon - $Transfer;
			}
		} 
	}
	return $TransferNeededDanamon;
}

?>
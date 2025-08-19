<?php

include('includes/session.php');
$Title = __('KL General Control Board');

/* Assign the sections to be executed, to avoid error 504*/
$ShowSectionInfo = false;
$ProcessSection01 = false;
$ProcessSection02 = false;

if (!isset($_GET['Section'])){
	$ProcessSection01 = true;
	$ProcessSection02 = true;
}else{
	$ShowSectionInfo = true;
		$Title = 'KL General Control Board Section ' . $_GET['Section'];
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = true;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = true;
	}
}

include('includes/header.php');
include('includes/GLFunctions.php');

include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLControlBoardFunctions.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/KLReorderLevel.php');
include('includes/KLUIGeneralFunctions.php');

include('includes/OCOpenCartGeneralFunctions.php');
include('includes/OCOpenCartConnectDB.php');

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

/*	$KL_SystemAdmin = true;
	$KL_OperationalManager = true;
	$KL_OperationalLeader = true;
	$KL_AdministrationLeader = true;
	$KL_AdministrationTeam = true;
	$KL_BusinessDevelopmentManager = true;
 	$KL_SalesDirector = true;
	$KL_PurchasingTeam = true;
	$KL_ShopSupportTeam = true;
	$KL_ShopSupportLeader = true;
	$KL_OnlineSales = true;
	$KL_ShopManager = true;
	$KL_SPGSeniorOrSupport = true;
	$KL_SPGJunior = true;
	$KL_PettyCash = true;
	$KL_ITSupport = true;
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
	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){
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
		OR $KL_PurchasingTeam){	

		ItemsInLocationForMoreThan('SERVI', 10, $RootPath);
		$NumberOfTestExecuted++;
		ItemsInLocationForMoreThan('SERSV', 10, $RootPath);
		$NumberOfTestExecuted++;
		ItemsInLocationForMoreThan('SERSU', 15, $RootPath);
		$NumberOfTestExecuted++;
		ItemsInLocationForMoreThan('SERSW', 15, $RootPath);
		$NumberOfTestExecuted++;
		ItemsInLocationForMoreThan('SERDE', 90, $RootPath);
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
		PettyCashBalanceControl("IDR", "('111111209',
												'111111309')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControl("USD", "('111205010')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControl("EUR", "('111205020')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControl("THB", "('111205030',
												'111204030AD')", 1, $PeriodNow);
		$NumberOfTestExecuted++;
		PettyCashBalanceControl("HKD", "('111205040')", 1, $PeriodNow);
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
		BalanceAccountControl("111204030AD",           0,  500000000, $PeriodNow);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		BalanceListAccountControl("('111121101AD',
									'111121105AD',
									'111121107AD',
									'111121110AD',
									'111121115AD',
									'111121117AD',
									'111121121AD',
									'111121122AD',
									'111121125AD',
									'111203010AD',
									'111203020AD',
									'111259010AD', 
									'111259020AD', 
									'111259050AD')", "Total Banks PT ADU", 2000000000, 5000000000, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111121121AD', 
									'111121122AD', 
									'111121125AD')", "Total Marketplaces PT ADU", -1, 75000000, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111259010AD', 
									'111259020AD', 
									'111259050AD')", "Total PayPal PT ADU", -1, 75000000, $PeriodNow);
		$NumberOfTestExecuted++;

		BalanceListAccountControl("('111121100SM',
									'111121105SM',
									'111121110SM',
									'111121115SM',
									'111121117SM')", "Total Banks PT SMH", 1500000000, 4000000000, $PeriodNow);
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

		BalanceAccountControl("111800000AD",  15000000 * $NumberOfOpenShopsTotal,  22500000 * $NumberOfOpenShopsTotal, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111900000AD",    500000 * $NumberOfOpenShopsTotal,   1200000 * $NumberOfOpenShopsTotal, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111311100AD",  -50000000,   20000000, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("111499000",         -1,          1, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021400AD", -200000000,          1, $PeriodNow);
		$NumberOfTestExecuted++;
		BalanceAccountControl("211021500AD",  500000000, 1500000000, $PeriodNow);
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
		GoodsJustTransferred("SERSV", "KANTO", 2, 30, $RootPath);
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
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKLDISCOUNT20", $RootPath);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKLDISCOUNT50", $RootPath);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKLDISCOUNT80", $RootPath);
		$NumberOfTestExecuted++;

		$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("STABKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("NOPOKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2A", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5A", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8A", "SHOPKL", $NumberOfTestExecuted, $RootPath);

		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBL", $RootPath);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBLDISCOUNT20", $RootPath);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBLDISCOUNT50", $RootPath);
		$NumberOfTestExecuted++;
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBLDISCOUNT80", $RootPath);
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
}

/***************************************************************************************
* SECTION 2
***************************************************************************************/

if ($ProcessSection02){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 02",'info');
	}

	if ($KL_OperationalManager
		OR $KL_ShopSupportLeader
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
		ValueStockLocation("SERSV",    0,  150, 0, 0);
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
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam){

		ItemsWithoutPurchasingData($RootPath);
		$NumberOfTestExecuted++;
		ItemsWithWrongNumberOfPreferredSuppliers($RootPath);
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

	if ($KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader
		OR $KL_ShopSupportTeam){
		PackagingItemsOnWrongLocation($RootPath); 
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager){
		CheckPackagingToBeRefilled(false, false, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportLeader){
		CheckPackagingToBeRefilled(false, true, $RootPath);
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
//		POStatusControl("","IN WANGFOONG CARGO BUT NOT SHIPPED", 0, $PeriodNow, $RootPath);
//		$NumberOfTestExecuted++;
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
		OR $KL_CustomerService
		OR $KL_OperationalManager
		OR $KL_ShopSupportLeader
		OR $KL_ShopSupportTeam){
		OutstandingOrders("Wholesale", "Order", $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_CustomerService
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
		OR $KL_CustomerService){ 
		OnlineMarketPlacePaymentPending(0, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_CustomerService
		OR $KL_ShopSupportLeader
		OR $KL_OperationalManager){ 
		OnlineMarketPlacePaymentPending(10, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_CustomerService
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("MarketPlace", "Order", $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_CustomerService){
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_PENDING, $RootPath );
		$NumberOfTestExecuted++;
	}

	if ($KL_CustomerService){
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_SHIPPED, $RootPath );
		$NumberOfTestExecuted++;
	}
 
	if ($KL_SystemAdmin 
		OR $KL_SalesDirector
		OR $KL_CustomerService){
		OnlineQuotationsFollowUp($RootPath );
		$NumberOfTestExecuted++;
		OldOnlineQuotations(1, $RootPath);
		$NumberOfTestExecuted++;
//		OutstandingOrders("Online", "Quotation", $RootPath);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_CustomerService){ 
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_PROCESSING, $RootPath );
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_CustomerService){
		OnlineOrdersFollowUp("KL-WEBSITE", 10, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_AdministrationTeam
		OR $KL_CustomerService
		OR $KL_ShopSupportTeam){ 
		OutstandingOrders("Online", "Order", $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_CustomerService
		OR $KL_ShopSupportTeam){ 
		OnlineItemsOnProcess($RootPath);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin
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
		OR $KL_CustomerService
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
		OR $KL_CustomerService){
		ItemsWithoutWeightOrVolume($RootPath);
		$NumberOfTestExecuted++;
		ItemsShouldBeInWebsite();
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_ITSupport){
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

	if ($KL_SystemAdmin){
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

include('includes/footer.php');

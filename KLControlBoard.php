<?php
define("VERSIONFILE", "3.24"); 
define("NUMBER_OF_TESTS", 155); 

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include ('includes/session.inc');
$Title = _('Kapal-Laut General Control Board '. VERSIONFILE);
include ('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');

include ('includes/OpenCartGeneralFunctions.php');
include ('includes/OpenCartConnectDB.php');
include ('includes/WeberpOpenCartDefines.php');

/* Do the pending GL Postings to get the latest financial control reports*/
include ('includes/GLPostings.inc');

/* ASSIGN users to groups */
include ('includes/KLRoles.inc');

$begintime = time_start();

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

/***************************************************************************************
* TEST AND PLAY AREA      
***************************************************************************************/

if ($KL_SystemAdmin){
//	phpinfo();
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager 
	OR $KL_KantorAdministration 
	OR $KL_PurchasingManager 
	OR $KL_PurchasingTeam 
	OR $KL_ShopSupportTeam 
	OR $KL_ShopSupportManager 
	OR $KL_SalesManager 
	OR $KL_PettyCash 
	OR $KL_SPG 
	OR $KL_SPGSupport){

}

/***************************************************************************************
* SPG PERFORMANCE         
***************************************************************************************/

if ($KL_SystemAdmin 
	OR $KL_PurchasingManager
	OR $KL_SalesManager
	OR $KL_KantorManager){
	SPGNotReportingSalesInDays(2, $db);
}

if ($KL_SystemAdmin
	OR $KL_PurchasingManager
	OR $KL_KantorManager){
	SplittedPaymentsBySPG(15, 2, $db);
}

/*
if ($KL_PurchasingManager
	OR $KL_SalesManager){
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
if ($KL_SystemAdmin
	OR $KL_PurchasingTeam){
	ItemsWithoutStandardCost($RootPath, $db);
}

if ($KL_SystemAdmin){
	WrongStandardCost("Indonesia"         , "", 1.00, 0.04, false, $RootPath, $db);
	WrongStandardCost("Thailand"          , "", 1.25, 0.04, false, $RootPath, $db);
	WrongStandardCost("China"             , "", 1.25, 0.04, false, $RootPath, $db);
	WrongStandardCost("Hong Kong, (China)", "", 1.25, 0.04, false, $RootPath, $db);
	WrongStandardCost("Catalonia"         , "", 1.25, 0.10, false, $RootPath, $db);
}

if ($KL_PurchasingTeam) {
	WrongStandardCost("Indonesia"         , "", 1.00, 0.05, true, $RootPath, $db);
	WrongStandardCost("Thailand"          , "", 1.25, 0.05, true, $RootPath, $db);
	WrongStandardCost("China"             , "", 1.25, 0.05, true, $RootPath, $db);
	WrongStandardCost("Hong Kong, (China)", "", 1.25, 0.05, true, $RootPath, $db);
	WrongStandardCost("Catalonia"         , "", 1.25, 0.10, true, $RootPath, $db);
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

if ($KL_PurchasingManager
	OR $KL_ShopSupportTeam
	OR $KL_KantorManager){
	
	over_or_below_limit("Items changing price or moving category", "OVER", 50, $RootPath, $db);
	over_or_below_limit("Items changing price", "OVER", 20, $RootPath, $db);
	over_or_below_limit("Items moving to 20% discount", "OVER", 20, $RootPath, $db);
	over_or_below_limit("Items moving to 50% discount", "OVER", 20, $RootPath, $db);
	over_or_below_limit("Items moving to 80% discount", "OVER", 20, $RootPath, $db);
}

if ($KL_ShopSupportTeam){

	ItemsChangingPriceDelayed(4, $RootPath, $db);
	ItemsMovingToDiscountDelayed(20, 4, $RootPath, $db);
	ItemsMovingToDiscountDelayed(50, 4, $RootPath, $db);
	ItemsMovingToDiscountDelayed(80, 4, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager
	OR $KL_SalesManager 
	OR $KL_PurchasingManager){

	ItemsChangingPriceDelayed(5, $RootPath, $db);
	ItemsMovingToDiscountDelayed(20, 5, $RootPath, $db);
	ItemsMovingToDiscountDelayed(50, 5, $RootPath, $db);
	ItemsMovingToDiscountDelayed(80, 5, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_PurchasingManager
	OR $KL_KantorManager
	OR $KL_SalesManager 
	OR $KL_PurchasingTeam 
	OR $KL_ShopSupportTeam){
	
	DiscountedItemsOnNotOutletShops("DISC50", $RootPath, $db);
	DiscountedItemsOnNotOutletShops("DISC80", $RootPath, $db);
	NotDiscountedItemsOnOutLetShops($RootPath, $db);
	
	DiscountedItemsWithWrongDiscount("DISC20", "20", $RootPath, $db);
	DiscountedItemsWithWrongDiscount("DISC50", "50", $RootPath, $db);
//	DiscountedItemsWithWrongDiscount("DISC80", "80", $RootPath, $db);

	NotDiscountedItemsWithDiscount($RootPath, $db);
}


/***************************************************************************************
* BALANCE ACCOUNTS         
***************************************************************************************/
if ($KL_SystemAdmin){
	GoodsReceivedNotInvoicedControl($periodnow, $db);
	CustomersDebtControl(100000, $periodnow, $db);
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager
	OR $KL_KantorAdministration){
	
	BalanceAccountControl("111111101",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111102",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111103",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111105",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111106",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111107",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111108",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111109",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111110",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111111",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111112",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111113",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111114",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111115",         0,   15000000, $periodnow, $db);
	BalanceAccountControl("111111116",         0,   15000000, $periodnow, $db);
}

if ($KL_SystemAdmin 
	OR $KL_PurchasingTeam
	OR $KL_KantorAdministration){

	BalanceAccountControl("111111100",          -1,          1, $periodnow, $db);
}


if ($KL_SystemAdmin 
	OR $KL_KantorAdministration){
	// Bank Mandiri or  BCA has enough funds to be transferred to Danamon
	BalanceAccountControl("111121100PT",  1000000,   50000000, $periodnow, $db);
	BalanceAccountControl("111121101PT",  1000000,  100000000, $periodnow, $db);
	BalanceAccountControl("111121110PT",  1000000,  100000000, $periodnow, $db);
}

if ($KL_SystemAdmin){

	BalanceAccountControl("111111200",   20000000,   50000000, $periodnow, $db);
	BalanceAccountControl("111111209",          0,   15000000, $periodnow, $db);
	BalanceAccountControl("111121105PT",200000000,  400000000, $periodnow, $db);
	BalanceAccountControl("111131100",         -1,  400000000, $periodnow, $db);
	BalanceAccountControl("111520000",  150000000,  300000000, $periodnow, $db);
	BalanceAccountControl("111512000",   50000000,  150000000, $periodnow, $db);
	BalanceAccountControl("111513000",         -1,          1, $periodnow, $db);
	BalanceAccountControl("111800000",  120000000,  150000000, $periodnow, $db);
	BalanceAccountControl("111900000",   15000000,   25000000, $periodnow, $db);
	BalanceAccountControl("111311100",   -1000000,    1000000, $periodnow, $db);
	BalanceAccountControl("111499000",         -1,          1, $periodnow, $db);
	BalanceAccountControl("211021400", -300000000,          0, $periodnow, $db);
	BalanceAccountControl("211021500",  -20000000,  300000000, $periodnow, $db);
	BalanceAccountControl("612011215",         -1,          1, $periodnow, $db);
}

/***************************************************************************************
* STOCK CONTROL         
***************************************************************************************/

if ($KL_SystemAdmin){
	
	ItemsNeedingAutomaticTranslation($RootPath, $db);
}

if ($KL_SystemAdmin
	OR $KL_PurchasingManager
	OR $KL_KantorManager){
	
	ItemsNeedingTranslationRevision($RootPath, $db);
}

/*
if ($KL_PurchasingManager
	OR $KL_KantorManager
	OR $KL_ShopSupportTeam){
	ItemsWithStockLocationButNoStockAvailable("WABOM", "WaterBom", 15, 600, $RootPath, $db);
	ItemsWithStockLocationButNoStockAvailable("WHAYA", "Ayana", 15, 600, $RootPath, $db);
	ItemsWithStockLocationButNoStockAvailable("WHINT", "InterContinental", 15, 600, $RootPath, $db);
	InsuficientStockForItems("SILVER", "TM-", "Tali Mie", 20, 40, $RootPath, $db);
}
*/

if ($KL_PurchasingManager
	OR $KL_PurchasingTeam){
	ItemsinSetUp("ReadyToTest", $RootPath, $db);
	ItemsinSetUp("NeedDescription", $RootPath, $db);
	//ItemsinSetUp("NeedPrice", $RootPath, $db);
	ItemsWithoutRetailPrice("SETUP", 4.75, $RootPath, $db);
	ItemsinSetUp("WithReorderLevel", $RootPath, $db);

	ObsoleteComponentsInActiveBOM($RootPath, $db);
}

if ($KL_PurchasingManager){

	GoodsJustArrived("PO", "KANTO", 3, $RootPath, $db);
	GoodsJustArrived("WO", "KANTO", 3, $RootPath, $db);
	GoodsJustArrived("WO", "SUPBA", 3, $RootPath, $db);

	GoodsJustTransferred("SAMPR", "KANTO", 2, 50, $RootPath, $db);
	GoodsJustTransferred("SASPG", "KANTO", 2, 50, $RootPath, $db);
	GoodsJustTransferred("SERSU", "KANTO", 2, 50, $RootPath, $db);
	GoodsJustTransferred("SERDE", "KANTO", 2, 50, $RootPath, $db);
	GoodsJustTransferred("SERVI", "KANTO", 2, 50, $RootPath, $db);
//	GoodsJustTransferred("WABOM", "KANTO", 2, 50, $RootPath, $db);
	
	InsuficientStockForTopSalesItems("SILVER", "10-Silver",90, 100, 150, $RootPath, $db);
	InsuficientStockForTopSalesItems("STAINL", "20-Stainless Steel", 90, 100, 150, $RootPath, $db);
	InsuficientStockForTopSalesItems("FASHIO", "30-Fashion Jewellery", 90, 100, 150, $RootPath, $db);
	InsuficientStockForTopSalesItems("ACCESO", "40-Accessories", 90, 100, 150, $RootPath, $db);
	InsuficientStockForTopSalesItems("CONSIG", "50-Consignment", 60, 100, 30, $RootPath, $db);

/*	ValueStockLocation("TOK66", 1000, 1200, 0, 0, $db);
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

if ($KL_PurchasingManager
	OR $KL_SalesManager){
	ItemsWithStockKantorButReorderLevelTokoZero($RootPath, $db);

	ItemsWithStockKantorButRLZeroAt("DISC50", "TOKSU", $RootPath, $db);
	ItemsWithStockKantorButRLZeroAt("DISC80", "TOKSU", $RootPath, $db);
}
	
if ($KL_PurchasingManager){
	ItemsWithStockKantorButRLZeroAt("ALL", "TOKSA", $RootPath, $db);
	ItemsWithStockKantorButRLZeroAt("ALL", "TOKSS", $RootPath, $db);
	ItemsWithStockKantorButRLZeroAt("ALL", "TOKMF", $RootPath, $db);
	ItemsWithStockKantorButRLZeroAt("ALL", "TOKPU", $RootPath, $db);
	ItemsWithStockKantorButRLZeroAt("ALL", "TOKPA", $RootPath, $db);
	ItemsWithStockKantorButRLZeroAt("ALL", "TOKKA", $RootPath, $db);

	CategoryItemsNotInShop("DISC50", "TOKSU", $RootPath, $db);
	CategoryItemsNotInShop("DISC80", "TOKSU", $RootPath, $db);

	ItemsInCategoryWithStockKantorButReorderLevelTokoZero("DISC20", $RootPath, $db);
	ItemsInCategoryWithStockKantorButReorderLevelTokoZero("DISC50", $RootPath, $db);
	ItemsInCategoryWithStockKantorButReorderLevelTokoZero("DISC80", $RootPath, $db);
}


if ($KL_KantorManager 
	OR $KL_PurchasingManager
	OR $KL_PurchasingTeam){

	ConsumablesGoodsNotEnoughStock(50, 25, 75, $RootPath, $db);
}

if ($KL_PurchasingManager
	OR $KL_PurchasingTeam){

	ValueStockLocation("SERVI",    0,  150, 0, 0, $db);
	ValueStockLocation("SERDE",    0,  150, 0, 0, $db);
	ValueStockLocation("SERSU",    0,  300, 0, 0, $db);
	OvestockAtSamples(1, $RootPath, $db);
	SamplesNotLongerNeeded($RootPath, $db);
	GoodsToBeProduced("COMPON", "DISCOUNT", $RootPath, $db);
	GoodsToBeProduced("COMPON", "ALL", $RootPath, $db);
}


if ($KL_SystemAdmin
	OR $KL_PurchasingTeam){
	ItemsWithoutPurchasingData($RootPath, $db);
}
if ($KL_PurchasingManager
	OR $KL_PurchasingTeam){
	ComponentsToObsolete(false, 0, $RootPath, $db);
}

if ($KL_SystemAdmin
	OR $KL_PurchasingManager
	OR $KL_PurchasingTeam){
	FlaggedAsObsoleteButStockAvailable($RootPath, $db);
}

if ($KL_PurchasingManager){
	ItemsInKLProcessAndRLNotZero($RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager
	OR $KL_PurchasingManager){
	ItemsOnSpecialRequest($RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_ShopSupportTeam
	OR $KL_PurchasingTeam){
	PackagingItemsOnWrongLocation($RootPath, $db); // Works for both regular and outlet shop packaging
}

if ($KL_SystemAdmin){
	PackagingToBeRefilled(false, $RootPath, $db);
	OutletPackagingToBeRefilled(false, $RootPath, $db);
}

if ($KL_SystemAdmin){
	InsuficientStockForShopPackaging('SHPACK', 21, 90, 30, false, $RootPath, $db); // Works for both regular and outlet shop packaging
}

if ($KL_PurchasingTeam){
	InsuficientStockForShopPackaging('SHPACK', 21, 90, 30, true, $RootPath, $db); // Works for both regular and outlet shop packaging
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager){
//	InsuficientStockForShopPackaging('ZAPON', 21, 60, 30, false, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager
	OR $KL_PurchasingTeam 
	OR $KL_ShopSupportTeam){
	
	CheckNegativeStock($RootPath, $db);
}
/***************************************************************************************
* SALES CONTROL         
***************************************************************************************/
if ($KL_PurchasingManager
	OR $KL_SalesManager){

	GoodSellingItemsInCategory("TESTSI", 15, 6, $RootPath, $db);
	GoodSellingItemsInCategory("TESTSS", 15, 6, $RootPath, $db);
	GoodSellingItemsInCategory("TESTFJ", 15, 6, $RootPath, $db);
	GoodSellingItemsInCategory("TESTAC", 15, 6, $RootPath, $db);

	GoodSellingItemsInCategory("NOPOSI", 15, 6, $RootPath, $db);
	GoodSellingItemsInCategory("NOPOSS", 15, 6, $RootPath, $db);
	GoodSellingItemsInCategory("NOPOFJ", 15, 6, $RootPath, $db);
	GoodSellingItemsInCategory("NOPOAC", 15, 6, $RootPath, $db);

	ActiveItemsNoSales( 30, "TESTSI", $RootPath, $db);
	ActiveItemsNoSales( 30, "TESTSS", $RootPath, $db);
	ActiveItemsNoSales( 30, "TESTFJ", $RootPath, $db);
	ActiveItemsNoSales( 30, "TESTAC", $RootPath, $db);

	ActiveItemsNoSales( 30, "SILVER", $RootPath, $db);
	ActiveItemsNoSales( 30, "STAINL", $RootPath, $db);
	ActiveItemsNoSales( 30, "FASHIO", $RootPath, $db);
	ActiveItemsNoSales( 30, "ACCESO", $RootPath, $db);

	ActiveItemsNoSales( 45, "NOPOSI", $RootPath, $db);
	ActiveItemsNoSales( 45, "NOPOSS", $RootPath, $db);
	ActiveItemsNoSales( 45, "NOPOFJ", $RootPath, $db);
	ActiveItemsNoSales( 45, "NOPOAC", $RootPath, $db);

	ActiveItemsNoSales( 45, "DISC20", $RootPath, $db);
	ActiveItemsNoSales( 45, "DISC50", $RootPath, $db);
	ActiveItemsNoSales( 90, "DISC80", $RootPath, $db);

	TopSalesNotInEnoughShops(  1, 500, 60, 11, "STABLE", $RootPath, $db);
	TopSalesNotInEnoughShops(500, 800, 60,  9, "STABLE", $RootPath, $db);
	TopSalesNotInEnoughShops(800,1200, 60,  7, "STABLE", $RootPath, $db);
//	TopSalesNotInEnoughShops(  1,  50, 60, 3, "DISC50", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 900, 60, "TOK66", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 900, 60, "TOKSE", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 900, 60, "TOKOB", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 900, 60, "TOKKA", "ACTIVE", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 900, 60, "TOKPA", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 800, 60, "TOKKS", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 800, 60, "TOKBW", "ACTIVE", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 900, 60, "TOKJC", "ACTIVE", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 900, 60, "TOKUB", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 1000, 60, "TOKMF", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 1000, 60, "TOKPU", "ACTIVE", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 1000, 60, "TOKSA", "ACTIVE", $RootPath, $db);
//	ItemsNotTopSalesInShop(1, 800, 60, "TOKSU", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 1000, 60, "TOKSS", "ACTIVE", $RootPath, $db);

	PerformanceItemsInCategory("GOOD", "TESTSI", 15,  30, "VERY GOOD", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTSI", 30,  45, "GOOD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTSI", 50,  30, "BAD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTSI", 60, 100, "LONG TIME TESTING", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTSI", 60, 100, "TEST FINISHED", $RootPath, $db);

	PerformanceItemsInCategory("GOOD", "TESTSS", 15,  30, "VERY GOOD", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTSS", 30,  45, "GOOD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTSS", 50,  30, "BAD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTSS", 60, 100, "LONG TIME TESTING", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTSS", 60, 100, "TEST FINISHED", $RootPath, $db);

	PerformanceItemsInCategory("GOOD", "TESTFJ", 15,  30, "VERY GOOD", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTFJ", 30,  45, "GOOD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTFJ", 50,  30, "BAD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTFJ", 60, 100, "LONG TIME TESTING", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTFJ", 60, 100, "TEST FINISHED", $RootPath, $db);

	PerformanceItemsInCategory("GOOD", "TESTAC", 15,  30, "VERY GOOD", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTAC", 30,  45, "GOOD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTAC", 50,  30, "BAD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTAC", 60, 100, "LONG TIME TESTING", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTAC", 60, 100, "TEST FINISHED", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "NOPOSI", 60,  50, "MOVE TO DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "NOPOSI",120, 100, "MOVE TO DISCOUNT", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "NOPOSS", 60,  50, "MOVE TO DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "NOPOSS",120, 100, "MOVE TO DISCOUNT", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "NOPOFJ", 60,  50, "MOVE TO DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "NOPOFJ",120, 100, "MOVE TO DISCOUNT", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "NOPOAC", 60,  50, "MOVE TO DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "NOPOAC",120, 100, "MOVE TO DISCOUNT", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "DISC20", 60, 100, "MOVE TO 50% DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "DISC50",120, 100, "MOVE TO 80% DISCOUNT", $RootPath, $db);
}

if ($KL_PurchasingManager
	OR $KL_KantorManager
	OR $KL_SalesManager){
	
//	ItemsNoSalesInLocation("WABOM", 30, 10, $RootPath, $db);
//	ItemsNoSalesInLocation("WHAYA", 30, 10, $RootPath, $db);
//	ItemsNoSalesInLocation("WHINT", 30, 10, $RootPath, $db);
}

/***************************************************************************************
* PO, Sales Orders         
***************************************************************************************/

if ($KL_PurchasingManager
	OR $KL_PurchasingTeam){
	
	OldPurchasingOrdersStillActive(90, $RootPath, $db);
	WrongItemsOnPurchaseOrders($RootPath, $db);
	
	PurchasingOrdersDeliveryControl("Delayed", 0, $RootPath, $db);
	PurchasingOrdersDeliveryControl("Coming Soon", 7, $RootPath, $db);
}


if ($KL_KantorManager
	OR $KL_ShopSupportManager){
	
//	WrongGiftItem("ONLINE-VIP-PACK", "Retail", "OVER",  500000, 1, $RootPath, $db);
//	WrongGiftItem("ONLINE-VIP-PACK", "Retail", "BELOW", 500000, 1, $RootPath, $db);
//	WrongGiftItem("GIFT-ALAR01", "Retail", "OVER",  1000000, 3, $RootPath, $db);
//	WrongGiftItem("GIFT-ALAR01", "Retail", "BELOW", 1000000, 3, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager){
	OutstandingOrders("Retail", "Order", $RootPath, $db);
	OutstandingOrders("Retail", "Quotation", $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_PurchasingManager
	OR $KL_KantorManager){
	OutstandingOrders("Wholesale", "Order", $RootPath, $db);
	OutstandingOrders("Wholesale", "Quotation", $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager
	OR $KL_ShopSupportManager){ 
	OutstandingOrders("Consignment", "Order", $RootPath, $db);
	OutstandingOrders("Consignment", "Quotation", $RootPath, $db);
}

if ($KL_SystemAdmin){
//	NewCustomers(2, $RootPath, $db);
	OnlineCustomersNoOrderPlaced($RootPath, $db);
	OnlineQuotationsFollowUp($RootPath, $db);
	OldOnlineQuotations(30, $RootPath, $db);
//	OutstandingOrders("Online", "Quotation", $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_KantorManager
	OR $KL_ShopSupportTeam){ 
	OutstandingOrders("Online", "Order", $RootPath, $db);
	OnlineItemsOnProcess($RootPath, $db);
}

if ($KL_SystemAdmin){
	OnlineOrdersFollowUp("KL-WEBSITE", 10, $RootPath, $db);
	OnlineOrdersFollowUp("LAZADA", 10, $RootPath, $db);
}
/***************************************************************************************
* Other tests     
***************************************************************************************/
if ($KL_SystemAdmin 
	OR $KL_PurchasingManager
	OR $KL_PurchasingTeam){
	ActiveItemsWithoutPicture($RootPath, $db);
}

if ($KL_SystemAdmin){
	ImagesWithoutProduct($RootPath, $db);
	OpenCartItemsWithoutPicture($RootPath, $db, $db_oc, $oc_tableprefix);
//	ImagesShouldNotBeInOpencartCatalog($RootPath, $db, $db_oc, $oc_tableprefix);
	ItemsWithoutWeightOrVolume($RootPath, $db);
	ItemsShouldBeInWebsite($db);
	UsersNotLoggingIn(60, "ALL_EXCEPT_SPGSUPPORT", $db);
	UsersNotLoggingIn(90, "SPGSUPPORT", $db);
}

if ($KL_ShopSupportTeam){ 
	TransfersDelayed(3, $RootPath, $db);
	ItemsCancelledInTransfers(3, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_PurchasingManager
	OR $KL_KantorManager){
	TransfersDelayed(4, $RootPath, $db);
	ItemsCancelledInTransfers(3, $RootPath, $db);
}

if ($KL_SystemAdmin 
	OR $KL_PurchasingManager
	OR $KL_KantorManager){
	PettyCashBalance($db);
	PettyCashToBeAuthorized($db);
}

prnMsg("Performed ". NUMBER_OF_TESTS . " control tests",'success');

time_finish($begintime);

include ('includes/footer.inc');

?>
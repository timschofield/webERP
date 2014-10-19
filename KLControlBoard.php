<?php
define("VERSIONFILE", "3.22"); 
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

$begintime = time_start();

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

/***************************************************************************************
* TEST AND PLAY AREA      
***************************************************************************************/

if ($_SESSION['UserID'] == "Ricard"){
//	phpinfo();
}


/***************************************************************************************
* SPG PERFORMANCE         
***************************************************************************************/

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Juliette")
	OR ($_SESSION['UserID'] == "Ike1")){
	SPGNotReportingSalesInDays(2, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")){
	SplittedPaymentsBySPG(15, 2, $db);
}

/*
if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Evelin")){
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
if (($_SESSION['UserID'] == "Ricard")
	OR ($_SESSION['UserID'] == "Cicik")){
	ItemsWithoutStandardCost($RootPath, $db);
}

if ($_SESSION['UserID'] == "Ricard"){
	WrongStandardCost("Indonesia"         , "", 1.00, 0.04, false, $RootPath, $db);
	WrongStandardCost("Thailand"          , "", 1.25, 0.04, false, $RootPath, $db);
	WrongStandardCost("China"             , "", 1.25, 0.04, false, $RootPath, $db);
	WrongStandardCost("Hong Kong, (China)", "", 1.25, 0.04, false, $RootPath, $db);
	WrongStandardCost("Catalonia"         , "", 1.25, 0.10, false, $RootPath, $db);
}

if ($_SESSION['UserID'] == "Cicik"){
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
if (($_SESSION['UserID'] == "Ricard")){
	over_or_below_limit("Items changing price", "OVER", 0, $RootPath, $db);
	over_or_below_limit("Items moving to discount", "OVER", 0, $RootPath, $db);
	over_or_below_limit("Items moving to outlet", "OVER", 0, $RootPath, $db);
}
*/

if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "RiaResti")
	OR ($_SESSION['UserID'] == "Karolin")
	OR ($_SESSION['UserID'] == "Dita")
	OR ($_SESSION['UserID'] == "Ike1")){
	
	over_or_below_limit("Items changing price or moving category", "OVER", 50, $RootPath, $db);
	over_or_below_limit("Items changing price", "OVER", 20, $RootPath, $db);
	over_or_below_limit("Items moving to discount", "OVER", 20, $RootPath, $db);
	over_or_below_limit("Items moving to outlet", "OVER", 0, $RootPath, $db);
}

if (($_SESSION['UserID'] == "RiaResti")
	OR ($_SESSION['UserID'] == "Dita")
	OR ($_SESSION['UserID'] == "Karolin")){

	ItemsChangingPriceDelayed(4, $RootPath, $db);
	ItemsMovingToDiscountDelayed(4, $RootPath, $db);
	ItemsMovingToOutletDelayed(4, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "Laia")){

	ItemsChangingPriceDelayed(5, $RootPath, $db);
	ItemsMovingToDiscountDelayed(5, $RootPath, $db);
	ItemsMovingToOutletDelayed(5, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "Juliette") 
	OR ($_SESSION['UserID'] == "Cicik") 
	OR ($_SESSION['UserID'] == "RiaResti")
	OR ($_SESSION['UserID'] == "Dita")
	OR ($_SESSION['UserID'] == "Karolin")){
	
	DiscountedItemsOnWrongShops("DISCOU", $RootPath, $db);
	DiscountedItemsOnWrongShops("OUTLET", $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "Cicik") 
	OR ($_SESSION['UserID'] == "RiaResti")
	OR ($_SESSION['UserID'] == "Dita")
	OR ($_SESSION['UserID'] == "Karolin")){
	
	DiscountedItemsWithWrongDiscount("DISCOU", "50", $RootPath, $db);
//	DiscountedItemsWithWrongDiscount("OUTLET", "80", $RootPath, $db);
	NotDiscountedItemsWithDiscount($RootPath, $db);
}

/***************************************************************************************
* BALANCE ACCOUNTS         
***************************************************************************************/
if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "Revi")){
	
	GoodsReceivedNotInvoicedControl($periodnow, $db);
	CustomersDebtControl(100000, $periodnow, $db);
	BalanceAccountControl("111111101",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111102",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111103",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111105",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111106",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111107",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111108",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111109",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111110",         0,   10000000, $periodnow, $db);
	BalanceAccountControl("111111111",         0,   10000000, $periodnow, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Cicik")
	OR ($_SESSION['UserID'] == "Revi")){

	BalanceAccountControl("111111100",          -1,          1, $periodnow, $db);
}


if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Revi")){
	// Bank Mandiri has enough funds to be transferred to Danamon
	BalanceAccountControl("111121100PT", 10000000,  100000000, $periodnow, $db);
}

if ($_SESSION['UserID'] == "Ricard"){

	BalanceAccountControl("111111200",   20000000,   50000000, $periodnow, $db);
	BalanceAccountControl("111111209",          0,   10000000, $periodnow, $db);
	BalanceAccountControl("111121105PT",100000000,  300000000, $periodnow, $db);
	BalanceAccountControl("111131100",         -1,  250000000, $periodnow, $db);
	BalanceAccountControl("111510000",          0,  200000000, $periodnow, $db);
	BalanceAccountControl("111511000",  500000000, 1000000000, $periodnow, $db);
	BalanceAccountControl("111511010",  100000000,  250000000, $periodnow, $db);
	BalanceAccountControl("111512000",   50000000,  150000000, $periodnow, $db);
	BalanceAccountControl("111513000",         -1,          1, $periodnow, $db);
	BalanceAccountControl("111518000",   25000000,  100000000, $periodnow, $db);
	BalanceAccountControl("111800000",   50000000,  100000000, $periodnow, $db);
	BalanceAccountControl("111900000",   10000000,   20000000, $periodnow, $db);
	BalanceAccountControl("111311100",          0,   25000000, $periodnow, $db);
	BalanceAccountControl("111499000",         -1,          1, $periodnow, $db);
	BalanceAccountControl("211021400", -200000000,          0, $periodnow, $db);
	BalanceAccountControl("211021500",  -20000000,  100000000, $periodnow, $db);
}

/***************************************************************************************
* STOCK CONTROL         
***************************************************************************************/
if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "RiaResti")
	OR ($_SESSION['UserID'] == "Dita")
	OR ($_SESSION['UserID'] == "Karolin")){
	
	ItemsWithStockLocationButNoStockAvailable("WABOM", "WaterBom", 12, 600, $RootPath, $db);
	ItemsWithStockLocationButNoStockAvailable("WHAYA", "Ayana", 12, 600, $RootPath, $db);
	ItemsWithStockLocationButNoStockAvailable("WHINT", "InterContinental", 12, 600, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "Cicik")){

	InsuficientStockForItems("TM-", "Tali Mie", 20, 40, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ricard")
	OR ($_SESSION['UserID'] == "Cicik")){
	ObsoleteComponentsInActiveBOM($RootPath, $db);
}

if ($_SESSION['UserID'] == "Laia"){

	GoodsJustArrived("PO", "KANTO", 3, $RootPath, $db);
	GoodsJustArrived("WO", "KANTO", 3, $RootPath, $db);
	GoodsJustArrived("WO", "SUPBA", 3, $RootPath, $db);

	GoodsJustTransferred("SAMPR", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("SASPG", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("SERSU", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("SERDE", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("SERVI", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("WABOM", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("WHAYA", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("WHINT", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("WHOLE", "KANTO", 2, 20, $RootPath, $db);
	GoodsJustTransferred("WHSHE", "KANTO", 2, 20, $RootPath, $db);
	
	InsuficientStockForTopSalesItems("SILVER", "10-Silver",30, 100, 70, $RootPath, $db);
	InsuficientStockForTopSalesItems("STAINL", "20-Stainless Steel", 30, 100, 70, $RootPath, $db);
	InsuficientStockForTopSalesItems("FASHIO", "30-Fashion Jewellery", 30, 100, 70, $RootPath, $db);
	InsuficientStockForTopSalesItems("ACCESO", "40-Accessories", 30, 100, 60, $RootPath, $db);
	InsuficientStockForTopSalesItems("CONSIG", "50-Consignment", 30, 100, 30, $RootPath, $db);

/*	ValueStockLocation("TOK66", 1000, 1200, 0, 0, $db);
	ValueStockLocation("TOKSA", 1000, 1400, 0, 0, $db);
	ValueStockLocation("TOKKS",  650,  750, 0, 0, $db);
	ValueStockLocation("TOKJC",  900, 1100, 0, 0, $db);
//	ValueStockLocation("TOKBW",  650,  800, 0, 0, $db);
	ValueStockLocation("TOKUB", 1000, 1200, 0, 0, $db);
	ValueStockLocation("TOKMF", 1300, 1500, 0, 0, $db);
	ValueStockLocation("TOKSE", 1000, 1200, 0, 0, $db);
	ValueStockLocation("SASPG",   10,   30, 0, 0, $db);
*/
	ItemsWithStockKantorButReorderLevelTokoZero($RootPath, $db);

	ItemsWithStockKantorButRLZeroAt("ALL", "TOKSA", $RootPath, $db);
	ItemsWithStockKantorButRLZeroAt("ALL", "TOKMF", $RootPath, $db);
	ItemsWithStockKantorButRLZeroAt("ALL", "TOKPU", $RootPath, $db);

//	ItemsWithStockKantorButRLZeroAt("DISCOU", "TOKLE", $RootPath, $db);

	ItemsInCategoryWithStockKantorButReorderLevelTokoZero("OUTLET", $RootPath, $db);
	ItemsInCategoryWithStockKantorButReorderLevelTokoZero("DISCOU", $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ike1") 
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Cicik")){

	ConsumablesGoodsNotEnoughStock(60, 30, 75, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Cicik")){

	ValueStockLocation("SERVI",    0,  150, 0, 0, $db);
	ValueStockLocation("SERDE",    0,  150, 0, 0, $db);
	ValueStockLocation("SERSU",    0,  300, 0, 0, $db);
	OvestockAtSamples(1, $RootPath, $db);
	SamplesNotLongerNeeded($RootPath, $db);
	GoodsToBeProduced("COMPON",$RootPath, $db);
}


if (($_SESSION['UserID'] == "Ricard")
	OR ($_SESSION['UserID'] == "Cicik")){
	ItemsWithoutPurchasingData($RootPath, $db);
}
if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Cicik")){
	ComponentsToObsolete(false, 0, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard")
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Cicik")){
	FlaggedAsObsoleteButStockAvailable($RootPath, $db);
}

if (($_SESSION['UserID'] == "Laia")){
	ItemsInKLProcessAndRLNotZero($RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "Laia")){
	ItemsOnSpecialRequest($RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Cicik")){
	PackagingItemsOnWrongLocation($RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard")){
	PackagingToBeRefilled($RootPath, $db);
}


if (($_SESSION['UserID'] == "Ricard")){
	InsuficientStockForShopPackaging('SHPACK', 21, 100, 30, false, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Cicik")){
	InsuficientStockForShopPackaging('SHPACK', 21, 100, 30, true, $RootPath, $db);
}


if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")){
	InsuficientStockForShopPackaging('ZAPON', 21, 60, 30, false, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "Cicik") 
	OR ($_SESSION['UserID'] == "RiaResti")
	OR ($_SESSION['UserID'] == "Karolin")
	OR ($_SESSION['UserID'] == "Dita")){
	
	CheckNegativeStock($RootPath, $db);
}
/***************************************************************************************
* SALES CONTROL         
***************************************************************************************/
if ($_SESSION['UserID'] == "Laia"){

	GoodSellingItemsInCategory("TESTSI", 15, 5, $RootPath, $db);
	GoodSellingItemsInCategory("TESTSS", 15, 5, $RootPath, $db);
	GoodSellingItemsInCategory("TESTFJ", 15, 5, $RootPath, $db);
	GoodSellingItemsInCategory("TESTAC", 15, 5, $RootPath, $db);

	GoodSellingItemsInCategory("NOPOSI", 15, 3, $RootPath, $db);
	GoodSellingItemsInCategory("NOPOSS", 15, 3, $RootPath, $db);
	GoodSellingItemsInCategory("NOPOFJ", 15, 3, $RootPath, $db);
	GoodSellingItemsInCategory("NOPOAC", 15, 3, $RootPath, $db);

	ActiveItemsNoSales( 45, "ACTIVE", $RootPath, $db);

	ActiveItemsNoSales( 45, "NOPOSI", $RootPath, $db);
	ActiveItemsNoSales( 45, "NOPOSS", $RootPath, $db);
	ActiveItemsNoSales( 45, "NOPOFJ", $RootPath, $db);
	ActiveItemsNoSales( 45, "NOPOAC", $RootPath, $db);

	ActiveItemsNoSales( 45, "DISCOU", $RootPath, $db);
	ActiveItemsNoSales(365, "OUTLET", $RootPath, $db);

	TopSalesNotInEnoughShops(  1, 300, 60, 9, "ACTIVE", $RootPath, $db);
	TopSalesNotInEnoughShops(300, 350, 60, 8, "ACTIVE", $RootPath, $db);
	TopSalesNotInEnoughShops(350, 500, 60, 7, "ACTIVE", $RootPath, $db);
//	TopSalesNotInEnoughShops(  1,  50, 60, 3, "DISCOU", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 400, 60, "TOK66", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 400, 60, "TOKSE", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 400, 60, "TOKOB", "ACTIVE", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 400, 60, "TOKKS", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 400, 60, "TOKBW", "ACTIVE", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 400, 60, "TOKJC", "ACTIVE", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 400, 60, "TOKUB", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 400, 60, "TOKMF", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 400, 60, "TOKPU", "ACTIVE", $RootPath, $db);

	ItemsNotTopSalesInShop(1, 400, 60, "TOKSA", "ACTIVE", $RootPath, $db);
	ItemsNotTopSalesInShop(1, 400, 60, "TOKSU", "ACTIVE", $RootPath, $db);

	PerformanceItemsInCategory("GOOD", "TESTSI", 15,  30, "VERY GOOD", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTSI", 30,  40, "GOOD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTSI", 50,  25, "BAD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTSI", 80, 100, "LONG TIME TESTING", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTSI", 80, 100, "TEST FINISHED", $RootPath, $db);

	PerformanceItemsInCategory("GOOD", "TESTSS", 15,  30, "VERY GOOD", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTSS", 30,  40, "GOOD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTSS", 50,  25, "BAD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTSS", 80, 100, "LONG TIME TESTING", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTSS", 80, 100, "TEST FINISHED", $RootPath, $db);

	PerformanceItemsInCategory("GOOD", "TESTFJ", 15,  30, "VERY GOOD", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTFJ", 30,  40, "GOOD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTFJ", 50,  25, "BAD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTFJ", 80, 100, "LONG TIME TESTING", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTFJ", 80, 100, "TEST FINISHED", $RootPath, $db);

	PerformanceItemsInCategory("GOOD", "TESTAC", 15,  30, "VERY GOOD", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTAC", 30,  40, "GOOD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTAC", 50,  25, "BAD", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "TESTAC", 80, 100, "LONG TIME TESTING", $RootPath, $db);
	PerformanceItemsInCategory("GOOD", "TESTAC", 80, 100, "TEST FINISHED", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "NOPOSI", 80,  50, "MOVE TO DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "NOPOSI",100, 100, "MOVE TO DISCOUNT", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "NOPOSS", 80,  50, "MOVE TO DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "NOPOSS",100, 100, "MOVE TO DISCOUNT", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "NOPOFJ", 80,  50, "MOVE TO DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "NOPOFJ",100, 100, "MOVE TO DISCOUNT", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "NOPOAC", 80,  50, "MOVE TO DISCOUNT", $RootPath, $db);
	PerformanceItemsInCategory("BAD",  "NOPOAC",100, 100, "MOVE TO DISCOUNT", $RootPath, $db);

	PerformanceItemsInCategory("BAD",  "DISCOU",120, 100, "MOVE TO OUTLET", $RootPath, $db);
}

if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")){
	
	ItemsNoSalesInLocation("WABOM", 30, 10, $RootPath, $db);
	ItemsNoSalesInLocation("WHAYA", 30, 10, $RootPath, $db);
	ItemsNoSalesInLocation("WHINT", 30, 10, $RootPath, $db);
}

/***************************************************************************************
* PO, Sales Orders         
***************************************************************************************/


if (($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Cicik")){
	
	OldPurchasingOrdersStillActive(90, $RootPath, $db);
	WrongItemsOnPurchaseOrders($RootPath, $db);
	
	PurchasingOrdersDeliveryControl("Delayed", 0, $RootPath, $db);
	PurchasingOrdersDeliveryControl("Coming Soon", 7, $RootPath, $db);
}


if (($_SESSION['UserID'] == "Ike1")){
	
	WrongGiftItem("ONLINE-VIP-PACK", "Retail", "OVER",  500000, 1, $RootPath, $db);
	WrongGiftItem("ONLINE-VIP-PACK", "Retail", "BELOW", 500000, 1, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")){
	OutstandingOrders("Retail", "Order", $RootPath, $db);
	OutstandingOrders("Retail", "Quotation", $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")){
	OutstandingOrders("Wholesale", "Order", $RootPath, $db);
	OutstandingOrders("Wholesale", "Quotation", $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "RiaResti")){ 
	OutstandingOrders("Consignment", "Order", $RootPath, $db);
	OutstandingOrders("Consignment", "Quotation", $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard")){
//	NewCustomers(2, $RootPath, $db);
	OnlineCustomersNoOrderPlaced($RootPath, $db);
	OnlineQuotationsFollowUp($RootPath, $db);
	OldOnlineQuotations(30, $RootPath, $db);
	OnlineOrdersFollowUp(7, $RootPath, $db);
//	OutstandingOrders("Online", "Quotation", $RootPath, $db);
	OnlineItemsOnProcess($RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "RiaResti")
	OR ($_SESSION['UserID'] == "Karolin")
	OR ($_SESSION['UserID'] == "Dita")){ 
	OutstandingOrders("Online", "Order", $RootPath, $db);
}

/***************************************************************************************
* Other tests     
***************************************************************************************/
if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Cicik")){
	ActiveItemsWithoutPicture($RootPath, $db);
}

if ($_SESSION['UserID'] == "Ricard"){
	ImagesWithoutProduct($RootPath, $db);
	OpenCartItemsWithoutPicture($RootPath, $db, $db_oc, $oc_tableprefix);
//	ImagesShouldNotBeInOpencartCatalog($RootPath, $db, $db_oc, $oc_tableprefix);
	ItemsWithoutWeightOrVolume($RootPath, $db);
	ItemsShouldBeInWebsite($db);
	UsersNotLoggingIn(120, $db);
}

if (($_SESSION['UserID'] == "RiaResti")
	OR ($_SESSION['UserID'] == "Karolin")
	OR ($_SESSION['UserID'] == "Dita")){ 
	TransfersDelayed(3, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")){
	TransfersDelayed(4, $RootPath, $db);
}

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")
	OR ($_SESSION['UserID'] == "Ike1")){
	PettyCashBalance($db);
	PettyCashToBeAuthorized($db);
}

prnMsg("Performed ". NUMBER_OF_TESTS . " control tests",'success');

time_finish($begintime);

include ('includes/footer.inc');

?>
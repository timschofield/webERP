<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL General Control Board');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/GLFunctions.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLControlBoardFunctions.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLPrices.php');
include(__DIR__ . '/includes/KLEmails.php');
include(__DIR__ . '/includes/KLReorderLevel.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');
include(__DIR__ . '/includes/KLGLFunctions.php');

include(__DIR__ . '/includes/OCOpenCartGeneralFunctions.php');
include(__DIR__ . '/includes/OCOpenCartConnectDB.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

// as the script uses _SESSION variables, reload just in case another user has been changing values in the meantime 
// because the script needs the latest values for the calculations
ReloadSessionVariablesFromConfig();

/* Assign the sections to be executed, to avoid error 504*/
$ShowSectionInfo = false;
$ProcessSection01 = false;
$ProcessSection02 = false;

if (!isset($_GET['Section'])){
	$ProcessSection01 = true;
	$ProcessSection02 = true;
} else {
	$ShowSectionInfo = true;
		$Title = 'KL General Control Board Section ' . $_GET['Section'];
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = true;
	} elseif ($_GET['Section'] == '02'){
		$ProcessSection02 = true;
	}
}

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
	$KL_Partner = true;
	$KL_GeneralAffairsManager = true;
	$KL_OperationalLeader = true;
	$KL_AdministrationLeader = true;
	$KL_AdministrationTeam = true;
	$KL_PurchasingManager = true;
 	$KL_SalesTeamManager = true;
	$KL_PurchasingTeam = true;
	$KL_ShopSupportTeam = true;
	$KL_ShopSupportLeader = true;
	$KL_OnlineSales = true;
	$KL_ShopManager = true;
	$KL_SPGSeniorOrSupport = true;
	$KL_SPGJunior = true;
	$KL_PettyCash = true;
	$KL_ITSupport = true;
	$KL_HRDManager = true;
	$KL_MarketingManager = true;
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
	if ($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 01",'info');
	}

	/***************************************************************************************
	* STANDARD COST
	***************************************************************************************/
	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam){
		$StartTime = microtime(true);
		SuppliersWithoutBasicData($RootPath);
		TimeNeededForExecution("SuppliersWithoutBasicData", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsWithoutStandardCost($RootPath);
		TimeNeededForExecution("ItemsWithoutStandardCost", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		WrongStandardCost("Indonesia"  , "", $_SESSION['Standard_Cost_Factor_Indonesia'], 0.04, "SHOWLINK", $RootPath);
		TimeNeededForExecution("WrongStandardCost_Indonesia", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		WrongStandardCost("Thailand"   , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.04, "SHOWLINK", $RootPath);
		TimeNeededForExecution("WrongStandardCost_Thailand", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		WrongStandardCost("China"      , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.04, "SHOWLINK", $RootPath);
		TimeNeededForExecution("WrongStandardCost_China", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		WrongStandardCost("Hong Kong"  , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.04, "SHOWLINK", $RootPath);
		TimeNeededForExecution("WrongStandardCost_HongKong", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingTeam) {
		$StartTime = microtime(true);
		WrongStandardCost("Indonesia"  , "", $_SESSION['Standard_Cost_Factor_Indonesia'], 0.04, "SHOWONLY", $RootPath);
		TimeNeededForExecution("WrongStandardCost_Indonesia_SHOWONLY", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		WrongStandardCost("Thailand"   , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.04, "SHOWONLY", $RootPath);
		TimeNeededForExecution("WrongStandardCost_Thailand_SHOWONLY", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		WrongStandardCost("China"      , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.04, "SHOWONLY", $RootPath);
		TimeNeededForExecution("WrongStandardCost_China_SHOWONLY", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		WrongStandardCost("Hong Kong"  , "", $_SESSION['Standard_Cost_Factor_Foreign'], 0.04, "SHOWONLY", $RootPath);
		TimeNeededForExecution("WrongStandardCost_HongKong_SHOWONLY", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* RETAIL PRICE
	***************************************************************************************/

	if ($KL_SystemAdmin
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		$NumberOfTestExecuted = MinimumOutletStockAvailable(20, 80, 20, $NumberOfTestExecuted);
		TimeNeededForExecution("MinimumOutletStockAvailable", $StartTime, $KL_SystemAdmin);
	}

	if ($KL_SystemAdmin
		OR $KL_SalesTeamManager
		OR $KL_ShopSupportTeam){
		
		$StartTime = microtime(true);
		over_or_below_limit("Items changing price or moving category", "OVER", $_SESSION['MaxItemsChangingPriceOrMovingDisc'], $RootPath);
		TimeNeededForExecution("over_or_below_limit", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		over_or_below_limit("Items changing price", "OVER", $_SESSION['MaxItemsChangingPrice'], $RootPath);
		TimeNeededForExecution("over_or_below_limit", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		over_or_below_limit("Items moving to 20% discount", "OVER", $_SESSION['MaxItemsChangingDisc20'], $RootPath);
		TimeNeededForExecution("over_or_below_limit", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		over_or_below_limit("Items moving to 50% discount", "OVER", $_SESSION['MaxItemsChangingDisc50'], $RootPath);
		TimeNeededForExecution("over_or_below_limit", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		over_or_below_limit("Items moving to 80% discount", "OVER", $_SESSION['MaxItemsChangingDisc80'], $RootPath);
		TimeNeededForExecution("over_or_below_limit", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam){

		$StartTime = microtime(true);
		ItemsChangingPriceDelayed(4, $RootPath);
		TimeNeededForExecution("ItemsChangingPriceDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsMovingToDiscountDelayed(20, 4, $RootPath);
		TimeNeededForExecution("ItemsMovingToDiscountDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsMovingToDiscountDelayed(50, 4, $RootPath);
		TimeNeededForExecution("ItemsMovingToDiscountDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsMovingToDiscountDelayed(80, 4, $RootPath);
		TimeNeededForExecution("ItemsMovingToDiscountDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_GeneralAffairsManager
		OR $KL_SalesTeamManager){

		$StartTime = microtime(true);
		ItemsChangingPriceDelayed(5, $RootPath);
		TimeNeededForExecution("ItemsChangingPriceDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsMovingToDiscountDelayed(20, 5, $RootPath);
		TimeNeededForExecution("ItemsMovingToDiscountDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsMovingToDiscountDelayed(50, 5, $RootPath);
		TimeNeededForExecution("ItemsMovingToDiscountDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsMovingToDiscountDelayed(80, 5, $RootPath);
		TimeNeededForExecution("ItemsMovingToDiscountDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesTeamManager
		OR $KL_ShopSupportLeader){
		
		$StartTime = microtime(true);
		ItemsInWrongShops("SHOPKL", $RootPath);
		TimeNeededForExecution("ItemsInWrongShops", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInWrongShops("SHOPBL", $RootPath);
		TimeNeededForExecution("ItemsInWrongShops", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInWrongShops("DEFECTIVE", $RootPath);
		TimeNeededForExecution("ItemsInWrongShops", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

	}

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam){	

		$StartTime = microtime(true);
		ItemsInLocationForMoreThan('SERVI', 10, $RootPath);
		TimeNeededForExecution("ItemsInLocationForMoreThan", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInLocationForMoreThan('SERSV', 10, $RootPath);
		TimeNeededForExecution("ItemsInLocationForMoreThan", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInLocationForMoreThan('SERSU', 15, $RootPath);
		TimeNeededForExecution("ItemsInLocationForMoreThan", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInLocationForMoreThan('SERSW', 15, $RootPath);
		TimeNeededForExecution("ItemsInLocationForMoreThan", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInLocationForMoreThan('SERDE', 90, $RootPath);
		TimeNeededForExecution("ItemsInLocationForMoreThan", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportLeader){	

		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC2A", "20", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC2B", "20", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC2G", "20", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC5A", "50", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC5B", "50", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC5G", "50", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC8A", "80", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC8B", "80", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		DiscountedItemsWithWrongDiscount("DISC8G", "80", $RootPath);
		TimeNeededForExecution("DiscountedItemsWithWrongDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		NotDiscountedItemsWithDiscount($RootPath);
		TimeNeededForExecution("NotDiscountedItemsWithDiscount", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportLeader){
		$StartTime = microtime(true);
		ActiveTransfersByLocation($RootPath);
		TimeNeededForExecution("ActiveTransfersByLocation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ActiveTransferStatus($RootPath);
		TimeNeededForExecution("ActiveTransferStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		RecentlyClosedTransferStatus(1, $RootPath);
		TimeNeededForExecution("RecentlyClosedTransferStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ErrorsInTransfers( 15, $RootPath);
		TimeNeededForExecution("ErrorsInTransfers", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* BALANCE ACCOUNTS
	***************************************************************************************/
	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		GLTransDateControl();
		TimeNeededForExecution("GLTransDateControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsReceivedNotInvoicedControl(1000000, $PeriodNow);
		TimeNeededForExecution("GoodsReceivedNotInvoicedControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		CustomersDebtControl(10000, $PeriodNow);
		TimeNeededForExecution("CustomersDebtControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashBalanceControl("IDR", "('111111209',
										'111111309')", 1, $PeriodNow);
		TimeNeededForExecution("PettyCashBalanceControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashBalanceControl("USD", "('111205010')", 1, $PeriodNow);
		TimeNeededForExecution("PettyCashBalanceControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashBalanceControl("EUR", "('111205020')", 1, $PeriodNow);
		TimeNeededForExecution("PettyCashBalanceControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashBalanceControl("THB", "('111205030',
										'111204030AD')", 1, $PeriodNow);
		TimeNeededForExecution("PettyCashBalanceControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashBalanceControl("HKD", "('111205040')", 1, $PeriodNow);
		TimeNeededForExecution("PettyCashBalanceControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_AdministrationTeam){
		// cash at retail shops
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CashAtShops(0, 10000000, 0, $NumberOfOpenShopsTotal * 4000000, $NumberOfTestExecuted, $PeriodNow);
		TimeNeededForExecution("CashAtShops", $StartTime, $KL_SystemAdmin);
	}
	
	if ($KL_SystemAdmin
		OR $KL_AdministrationLeader
		OR $KL_AdministrationTeam){
		$StartTime = microtime(true);
		InternalBankTransfers("PTADU", 
					"111121105AD",  750000000, 1500000000, 2000000000,
					"111121101AD",   25000000,   75000000,
					"111121110AD",   25000000,   75000000,
					"111121115AD",   25000000,   75000000, 
					"111121106AD",   25000000,   75000000, 
					"111121117AD",    1000000,    5000000,
					"111121121AD",     100000,    1000000,
					"111121122AD",     100000,    1000000,
					"111121125AD",     100000,    1000000,
					25000000,
					 1000000,
					$PeriodNow);	
		TimeNeededForExecution("InternalBankTransfers", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		InternalBankTransfers("PTSMH", 
					"111121105SM",  750000000, 1500000000, 2000000000,
					"111121100SM",   25000000,   75000000,
					"111121110SM",   25000000,   75000000,
					"111121115SM",   25000000,   75000000, 
					"111121106SM",   25000000,   75000000, 
					"111121117SM",    1000000,    5000000,
					"", 0, 0, "", 0, 0,	"", 0, 0,
					25000000,
					0,
					$PeriodNow);	
		TimeNeededForExecution("InternalBankTransfers", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		InternalBankTransfers("PTBB", 
					"111121105BB",  750000000, 1500000000, 2000000000,
					"111121101BB",   25000000,   50000000,
					"111121110BB",   25000000,   50000000,
					"111121115BB",   25000000,   50000000, 
					"111121106BB",   25000000,   50000000,
					"", 0, 0, 
					"", 0, 0, "", 0, 0,	"", 0, 0,
					25000000,
					0,
					$PeriodNow);	
		TimeNeededForExecution("InternalBankTransfers", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_PurchasingTeam
		OR $KL_AdministrationLeader){
		$StartTime = microtime(true);
		BalanceAccountControl("111111100",          -1,          1, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111202030",          -1,          1, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111204030AD",           0,  500000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		BalanceListAccountControl("('111121101AD',
									'111121105AD',
									'111121106AD',
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
		TimeNeededForExecution("BalanceListAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_AdministrationLeader){
		$StartTime = microtime(true);
		BalanceListAccountControl("('111121121AD', 
									'111121122AD', 
									'111121125AD')", "Total Marketplaces PT ADU", -1, 75000000, $PeriodNow);
		TimeNeededForExecution("BalanceListAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		BalanceListAccountControl("('111259010AD', 
									'111259020AD', 
									'111259050AD')", "Total PayPal PT ADU", -1, 75000000, $PeriodNow);
		TimeNeededForExecution("BalanceListAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		BalanceListAccountControl("('111121100SM',
									'111121105SM',
									'111121106SM',
									'111121110SM',
									'111121115SM',
									'111121117SM')", "Total Banks PT SMH", 1500000000, 4000000000, $PeriodNow);
		TimeNeededForExecution("BalanceListAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		BalanceListAccountControl("('111121100BB',
									'111121101BB',
									'111121105BB',
									'111121106BB',
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
		TimeNeededForExecution("BalanceListAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		BalanceListAccountControl("('111131100', 
									'111208010', 
									'111208020', 
									'111208030', 
									'111208040')", "Total Brankas Shareholders",      0,2000000000, $PeriodNow);
		TimeNeededForExecution("BalanceListAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		BalanceListAccountControl("('111513000', 
									'111513000AD')", "Total WIP",  -5000000,   5000000, $PeriodNow);
		TimeNeededForExecution("BalanceListAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		BalanceAccountControl("111111200",   50000000,  400000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111202010",         -1,          1, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111111209",          0,   25000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111131100",         -1, 2000000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111208010",         -1, 1000000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111208020",         -1, 1000000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111208030",         -1, 1000000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111208040",         -1, 1000000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111520000",         -1,          1, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		BalanceListAccountControl("('111512000', 
									'111512000AD')", "Persediaan Bahan Produksi (Components)",   50000000,    200000000, $PeriodNow);
		TimeNeededForExecution("BalanceListAccountControl", $StartTime, $KL_SystemAdmin);

		$StartTime = microtime(true);
		BalanceAccountControl("111800000AD",  15000000 * $NumberOfOpenShopsTotal,  22500000 * $NumberOfOpenShopsTotal, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111900000AD",    300000 * $NumberOfOpenShopsTotal,   900000 * $NumberOfOpenShopsTotal, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111311100AD",  -50000000,   20000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("111499000",         -1,          1, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("211021400AD", -200000000,          1, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("211021500AD",  500000000, 1500000000, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("612012015",         -1,          1, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		BalanceAccountControl("612012016",         -1,          1, $PeriodNow);
		TimeNeededForExecution("BalanceAccountControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* STOCK CONTROL
	***************************************************************************************/

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam){
		$StartTime = microtime(true);
		ItemsInSetup("ReadyToTest", "SETKLA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("ReadyToTest", "SETBLA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("ReadyToTest", "SETGEA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("NeedDescription", "SETKLA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("NeedDescription", "SETBLA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("NeedDescription", "SETGEA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("NeedPrice", "", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("WithReorderLevel", "SETKLA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("WithReorderLevel", "SETBLA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInSetup("WithReorderLevel", "SETGEA", $RootPath);
		TimeNeededForExecution("ItemsInSetup", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam){
		$StartTime = microtime(true);
		ObsoleteComponentsInActiveBOM($RootPath);
		TimeNeededForExecution("ObsoleteComponentsInActiveBOM", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam){

		$StartTime = microtime(true);
		ItemsInmediateShortage("COMPOA", $RootPath);
		TimeNeededForExecution("ItemsInmediateShortage", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager){

		$StartTime = microtime(true);
		GoodsJustArrived("PO", "KANTO", 3, $RootPath);
		TimeNeededForExecution("GoodsJustArrived", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustArrived("WO", "KANTO", 3, $RootPath);
		TimeNeededForExecution("GoodsJustArrived", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustArrived("WO", "SUPBA", 3, $RootPath);
		TimeNeededForExecution("GoodsJustArrived", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustTransferred("SAMPR", "KANTO", 2, 30, $RootPath);
		TimeNeededForExecution("GoodsJustTransferred", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustTransferred("SASPG", "KANTO", 2, 30, $RootPath);
		TimeNeededForExecution("GoodsJustTransferred", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustTransferred("SERSU", "KANTO", 2, 30, $RootPath);
		TimeNeededForExecution("GoodsJustTransferred", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustTransferred("SERSV", "KANTO", 2, 30, $RootPath);
		TimeNeededForExecution("GoodsJustTransferred", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustTransferred("SERSW", "KANTO", 2, 30, $RootPath);
		TimeNeededForExecution("GoodsJustTransferred", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustTransferred("SERDE", "KANTO", 2, 30, $RootPath);
		TimeNeededForExecution("GoodsJustTransferred", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsJustTransferred("SERVI", "KANTO", 2, 30, $RootPath);
		TimeNeededForExecution("GoodsJustTransferred", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SalesTeamManager){

		$StartTime = microtime(true);
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKL", $RootPath);
		TimeNeededForExecution("ItemsWithStockKantorButReorderLevelTokoZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKLDISCOUNT20", $RootPath);
		TimeNeededForExecution("ItemsWithStockKantorButReorderLevelTokoZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKLDISCOUNT50", $RootPath);
		TimeNeededForExecution("ItemsWithStockKantorButReorderLevelTokoZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPKLDISCOUNT80", $RootPath);
		TimeNeededForExecution("ItemsWithStockKantorButReorderLevelTokoZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("STABKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("NOPOKA", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2A", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5A", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8A", "SHOPKL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);

		$StartTime = microtime(true);
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBL", $RootPath);
		TimeNeededForExecution("ItemsWithStockKantorButReorderLevelTokoZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBLDISCOUNT20", $RootPath);
		TimeNeededForExecution("ItemsWithStockKantorButReorderLevelTokoZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBLDISCOUNT50", $RootPath);
		TimeNeededForExecution("ItemsWithStockKantorButReorderLevelTokoZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsWithStockKantorButReorderLevelTokoZero("SHOPBLDISCOUNT80", $RootPath);
		TimeNeededForExecution("ItemsWithStockKantorButReorderLevelTokoZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("TESTBA", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("STABBA", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("NOPOBA", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC2B", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC5B", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);
		$StartTime = microtime(true);
		$NumberOfTestExecuted = CategoryItemsMissingInShops("DISC8B", "SHOPBL", $NumberOfTestExecuted, $RootPath);
		TimeNeededForExecution("CategoryItemsMissingInShops", $StartTime, $KL_SystemAdmin);

	}
}

/***************************************************************************************
* SECTION 2
***************************************************************************************/

if ($ProcessSection02){
	if ($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 02",'info');
	}

	if ($KL_GeneralAffairsManager
		OR $KL_ShopSupportLeader
		OR $KL_PurchasingTeam){

		$StartTime = microtime(true);
		ConsumablesGoodsNotEnoughStock(30, 15, 45, $RootPath);
		TimeNeededForExecution("ConsumablesGoodsNotEnoughStock", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam){

		$StartTime = microtime(true);
		ValueStockLocation("SERVI",    0,  150, 0, 0);
		TimeNeededForExecution("ValueStockLocation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ValueStockLocation("SERDE",    0,  150, 0, 0);
		TimeNeededForExecution("ValueStockLocation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ValueStockLocation("SERSU",    0,  150, 0, 0);
		TimeNeededForExecution("ValueStockLocation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ValueStockLocation("SERSV",    0,  150, 0, 0);
		TimeNeededForExecution("ValueStockLocation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ValueStockLocation("SERSW",    0,  150, 0, 0);
		TimeNeededForExecution("ValueStockLocation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		OvestockAtSamples(1, $RootPath);
		TimeNeededForExecution("OvestockAtSamples", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		SamplesNotLongerNeeded($RootPath);
		TimeNeededForExecution("SamplesNotLongerNeeded", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsToBeProduced("COMPOA", "ONLYDISCOUNT", $RootPath);
		TimeNeededForExecution("GoodsToBeProduced", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsToBeProduced("COMPOA", "DISCOUNT", $RootPath);
		TimeNeededForExecution("GoodsToBeProduced", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsToBeProduced("COMPOA", "ALL", $RootPath);
		TimeNeededForExecution("GoodsToBeProduced", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_PurchasingManager
		OR $KL_PurchasingTeam){

		$StartTime = microtime(true);
		ItemsWithoutPurchasingData($RootPath);
		TimeNeededForExecution("ItemsWithoutPurchasingData", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsWithWrongNumberOfPreferredSuppliers($RootPath);
		TimeNeededForExecution("ItemsWithWrongNumberOfPreferredSuppliers", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam){
		$StartTime = microtime(true);
		ComponentsToObsolete(false, 0, $RootPath);
		TimeNeededForExecution("ComponentsToObsolete", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		FlaggedAsObsoleteButStockAvailable($RootPath);
		TimeNeededForExecution("FlaggedAsObsoleteButStockAvailable", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader){
		$StartTime = microtime(true);
		ItemsInKLProcessAndRLNotZero($RootPath);
		TimeNeededForExecution("ItemsInKLProcessAndRLNotZero", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader){
		$StartTime = microtime(true);
		ItemsOnSpecialRequest($RootPath);
		TimeNeededForExecution("ItemsOnSpecialRequest", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam
		OR $KL_ShopSupportLeader
		OR $KL_ShopSupportTeam){
		$StartTime = microtime(true);
		PackagingItemsOnWrongLocation($RootPath); 
		TimeNeededForExecution("PackagingItemsOnWrongLocation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_GeneralAffairsManager){
		$StartTime = microtime(true);
		CheckPackagingToBeRefilled(false, false, $RootPath);
		TimeNeededForExecution("CheckPackagingToBeRefilled", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportLeader){
		$StartTime = microtime(true);
		CheckPackagingToBeRefilled(false, true, $RootPath);
		TimeNeededForExecution("CheckPackagingToBeRefilled", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_GeneralAffairsManager
		OR $KL_SalesTeamManager
		OR $KL_PurchasingTeam 
		OR $KL_ShopSupportTeam){
		
		$StartTime = microtime(true);
		CheckNegativeStock($RootPath);
		TimeNeededForExecution("CheckNegativeStock", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* PACKAGING CONTROL
	***************************************************************************************/
	if ($KL_PurchasingManager 
		OR $KL_PurchasingTeam){

		$StartTime = microtime(true);
		InsuficientStockForShopPackaging('SHPACK', $_SESSION['Usage_Days_For_Packaging_Stock'], $_SESSION['Forecast_Days_For_Packaging_Stock'], true, false, $RootPath); // Works for both regular and outlet shop packaging
		TimeNeededForExecution("InsuficientStockForShopPackaging", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("PACKAGING","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* SALES CONTROL
	***************************************************************************************/
	if ($KL_SalesTeamManager){

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

		$StartTime = microtime(true);
		ActiveItemsNoSales( 30, "NOPOGA", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInCategoryForMoreThanDays( 90, "NOPOGA", $RootPath);
		TimeNeededForExecution("ItemsInCategoryForMoreThanDays", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		ActiveItemsNoSales( 30, "DISC2A", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInCategoryForMoreThanDays( 90, "DISC2A", $RootPath);
		TimeNeededForExecution("ItemsInCategoryForMoreThanDays", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		
		$StartTime = microtime(true);
		ActiveItemsNoSales( 30, "DISC2B", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInCategoryForMoreThanDays( 90, "DISC2B", $RootPath);
		TimeNeededForExecution("ItemsInCategoryForMoreThanDays", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		ActiveItemsNoSales( 30, "DISC2G", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInCategoryForMoreThanDays( 90, "DISC2G", $RootPath);
		TimeNeededForExecution("ItemsInCategoryForMoreThanDays", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

		$StartTime = microtime(true);
		ActiveItemsNoSales( 50, "DISC5A", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInCategoryForMoreThanDays( 120, "DISC5A", $RootPath);
		TimeNeededForExecution("ItemsInCategoryForMoreThanDays", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		
		$StartTime = microtime(true);
		ActiveItemsNoSales( 50, "DISC5B", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInCategoryForMoreThanDays( 120, "DISC5B", $RootPath);
		TimeNeededForExecution("ItemsInCategoryForMoreThanDays", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		
		$StartTime = microtime(true);
		ActiveItemsNoSales( 50, "DISC5G", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsInCategoryForMoreThanDays( 120, "DISC5G", $RootPath);
		TimeNeededForExecution("ItemsInCategoryForMoreThanDays", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		
		$StartTime = microtime(true);
		ActiveItemsNoSales( 60, "DISC8A", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		
		$StartTime = microtime(true);
		ActiveItemsNoSales( 60, "DISC8B", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		
		$StartTime = microtime(true);
		ActiveItemsNoSales( 60, "DISC8G", $RootPath);
		TimeNeededForExecution("ActiveItemsNoSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* PO, Sales Orders
	***************************************************************************************/

	if ($KL_PurchasingManager
		OR $KL_PurchasingTeam){

		$StartTime = microtime(true);
		OldPOStillActive(90, $RootPath);
		TimeNeededForExecution("OldPOStillActive", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		OldWOStillActive(60, $RootPath);
		TimeNeededForExecution("OldWOStillActive", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		WrongItemsOnPurchaseOrders($RootPath);
		TimeNeededForExecution("WrongItemsOnPurchaseOrders", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		WrongItemsOnWorkOrders($RootPath);
		TimeNeededForExecution("WrongItemsOnWorkOrders", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_PurchasingManager 
		OR $KL_PurchasingTeam){

		$StartTime = microtime(true);
		PurchaseOrdersWrongPlannedDates($RootPath);
		TimeNeededForExecution("PurchaseOrdersWrongPlannedDates", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","IN NEGOTIATION WITH SUPPLIER", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("FORSALE","ON PRODUCTION", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("OTHERS","ON PRODUCTION", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","FINISHED BUT NOT PAID", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_PurchasingManager){

		$StartTime = microtime(true);
		POStatusControl("FORSALE","STILL NOT FULLY PAID", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("OTHERS","STILL NOT FULLY PAID", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_PurchasingManager 
		OR $KL_PurchasingTeam){

		$StartTime = microtime(true);
		POStatusControl("","BALI PAID BUT NOT RECEIVED IN KANTOR", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","BALI RECEIVED IN KANTOR BUT NOT PAID", 0,$PeriodNow,  $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","PAID NOT SHIPPED BY SUPPLIER", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","PAID NOT RECEIVED IN AYE CARGO", 0,$PeriodNow,  $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","PAID NOT RECEIVED IN WANGFOONG CARGO", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","IN AYE CARGO BUT NOT SHIPPED", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
//		POStatusControl("","IN WANGFOONG CARGO BUT NOT SHIPPED", 0, $PeriodNow, $RootPath);
//		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","SHIPPED IN TRANSIT", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("","CUSTOMS CLEARANCE", 0, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("FORSALE","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("OTHERS","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager){

		$StartTime = microtime(true);
		POStatusControl("","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_CustomerService){

		$StartTime = microtime(true);
		OutstandingOrders("Retail", "Order", $RootPath);
		TimeNeededForExecution("OutstandingOrders", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin ){
		$StartTime = microtime(true);
		OutstandingOrders("Retail", "Quotation", $RootPath);
		TimeNeededForExecution("OutstandingOrders", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager
		OR $KL_AdministrationTeam
		OR $KL_CustomerService
		OR $KL_GeneralAffairsManager
		OR $KL_ShopSupportLeader
		OR $KL_ShopSupportTeam){
		$StartTime = microtime(true);
		OutstandingOrders("Wholesale", "Order", $RootPath);
		TimeNeededForExecution("OutstandingOrders", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager
		OR $KL_CustomerService
		OR $KL_GeneralAffairsManager){
		$StartTime = microtime(true);
		OutstandingOrders("Wholesale", "Quotation", $RootPath);
		TimeNeededForExecution("OutstandingOrders", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	/*
	if ($KL_SystemAdmin 
		OR $KL_GeneralAffairsManager
		OR $KL_ShopSupportLeader){ 
		OutstandingOrders("Consignment", "Order", $RootPath);
		OutstandingOrders("Consignment", "Quotation", $RootPath);
	}
	*/

	if ($KL_AdministrationTeam
		OR $KL_CustomerService){ 
		$StartTime = microtime(true);
		OnlineMarketPlacePaymentPending(0, $RootPath);
		TimeNeededForExecution("OnlineMarketPlacePaymentPending", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager
		OR $KL_AdministrationTeam
		OR $KL_CustomerService
		OR $KL_ShopSupportLeader
		OR $KL_GeneralAffairsManager){ 
		$StartTime = microtime(true);
		OnlineMarketPlacePaymentPending(10, $RootPath);
		TimeNeededForExecution("OnlineMarketPlacePaymentPending", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager
		OR $KL_AdministrationTeam
		OR $KL_CustomerService
		OR $KL_ShopSupportTeam){ 
		$StartTime = microtime(true);
		OutstandingOrders("MarketPlace", "Order", $RootPath);
		TimeNeededForExecution("OutstandingOrders", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_CustomerService){
		$StartTime = microtime(true);
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_PENDING, $RootPath );
		TimeNeededForExecution("OpenCartOrdersByStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_CustomerService){
		$StartTime = microtime(true);
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_SHIPPED, $RootPath );
		TimeNeededForExecution("OpenCartOrdersByStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
 
	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager
		OR $KL_CustomerService){
		$StartTime = microtime(true);
		OnlineQuotationsFollowUp($RootPath );
		TimeNeededForExecution("OnlineQuotationsFollowUp", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		OldOnlineQuotations(1, $RootPath);
		TimeNeededForExecution("OldOnlineQuotations", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
//		OutstandingOrders("Online", "Quotation", $RootPath);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_CustomerService){ 
		$StartTime = microtime(true);
		OpenCartOrdersByStatus(OPENCART_ORDER_STATUS_PROCESSING, $RootPath );
		TimeNeededForExecution("OpenCartOrdersByStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_CustomerService){
		$StartTime = microtime(true);
		OnlineOrdersFollowUp("KL-WEBSITE", 10, $RootPath);
		TimeNeededForExecution("OnlineOrdersFollowUp", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_AdministrationTeam
		OR $KL_CustomerService
		OR $KL_ShopSupportTeam){ 
		$StartTime = microtime(true);
		OutstandingOrders("Online", "Order", $RootPath);
		TimeNeededForExecution("OutstandingOrders", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_CustomerService
		OR $KL_ShopSupportTeam){ 
		$StartTime = microtime(true);
		OnlineItemsOnProcess($RootPath);
		TimeNeededForExecution("OnlineItemsOnProcess", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin
		OR $KL_ShopSupportLeader){ 
		$StartTime = microtime(true);
		ItemsNotNeededInOnlineOrderButRequested($RootPath);
		TimeNeededForExecution("ItemsNotNeededInOnlineOrderButRequested", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	/***************************************************************************************
	* Other tests
	***************************************************************************************/

	if ($KL_ITSupport
		OR $KL_PurchasingManager
		OR $KL_PurchasingTeam){
		$StartTime = microtime(true);
		ActiveItemsWithoutPicture($RootPath);
		TimeNeededForExecution("ActiveItemsWithoutPicture", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_PurchasingManager
		OR $KL_ITSupport){
		$StartTime = microtime(true);
		ImagesWithoutProduct($RootPath);
		TimeNeededForExecution("ImagesWithoutProduct", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SalesTeamManager
		OR $KL_CustomerService
		OR $KL_ITSupport){
		$StartTime = microtime(true);
		OpenCartItemsWithoutPicture($RootPath );
		TimeNeededForExecution("OpenCartItemsWithoutPicture", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SalesTeamManager
		OR $KL_ITSupport){
		$StartTime = microtime(true);
		PicturesToMoveToObsolete(false, $RootPath);
		TimeNeededForExecution("PicturesToMoveToObsolete", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesTeamManager
		OR $KL_PurchasingTeam
		OR $KL_CustomerService){
		$StartTime = microtime(true);
		ItemsWithoutWeightOrVolume($RootPath);
		TimeNeededForExecution("ItemsWithoutWeightOrVolume", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsShouldBeInWebsite();
		TimeNeededForExecution("ItemsShouldBeInWebsite", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_ITSupport){
		$StartTime = microtime(true);
		UsersNotLoggingIn(($_SESSION['MonthsAuditTrail'] *30)+1, "ALL_EXCEPT_SPGSUPPORT", $RootPath);
		TimeNeededForExecution("UsersNotLoggingIn", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		UsersNotLoggingIn(($_SESSION['MonthsAuditTrail'] *30)+1, "SPGSUPPORT", $RootPath);
		TimeNeededForExecution("UsersNotLoggingIn", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_GeneralAffairsManager 
		OR $KL_SalesTeamManager
		OR $KL_ShopSupportLeader 
		OR $KL_ShopManager){
		$StartTime = microtime(true);
		RegularTransfersToShopNotReceived('08:00:00','15:00:00', $RootPath);
		TimeNeededForExecution("RegularTransfersToShopNotReceived", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		TransferWithWrongInformation(15, $RootPath);
		TimeNeededForExecution("TransferWithWrongInformation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_ShopSupportTeam){ 
		$StartTime = microtime(true);
		TransfersDelayed(3, $RootPath);
		TimeNeededForExecution("TransfersDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ItemsCancelledInTransfers(3, $RootPath);
		TimeNeededForExecution("ItemsCancelledInTransfers", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager
		OR $KL_GeneralAffairsManager){
		$StartTime = microtime(true);
		TransfersDelayed(4, $RootPath);
		TimeNeededForExecution("TransfersDelayed", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_GeneralAffairsManager){
		$StartTime = microtime(true);
		ItemsCancelledInTransfers(3, $RootPath);
		TimeNeededForExecution("ItemsCancelledInTransfers", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if (!$KL_SystemAdmin){
		$StartTime = microtime(true);
		PettyCashBalance('User');
		TimeNeededForExecution("PettyCashBalance", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_PurchasingManager
		OR $KL_SalesTeamManager
		OR $KL_AdministrationLeader){
		$StartTime = microtime(true);
		PettyCashBalance('Authorizer');
		TimeNeededForExecution("PettyCashBalance", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashToBeAuthorized('Cash');
		TimeNeededForExecution("PettyCashToBeAuthorized", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashToBeAuthorized('Expenses');
		TimeNeededForExecution("PettyCashToBeAuthorized", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

}
prnMsg("Performed ". $NumberOfTestExecuted . " control tests",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include(__DIR__ . '/includes/footer.php');

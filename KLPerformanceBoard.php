<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL General Performance Board');
include(__DIR__ . '/includes/header.php');

include_once(__DIR__ . '/includes/GLFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLPerformanceBoardFunctions.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLPrices.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');
include_once(__DIR__ . '/includes/KLGLFunctions.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

// as the script uses _SESSION variables, reload just in case another user has been changing values in the meantime 
// because the script needs the latest values for the calculations
ReloadSessionVariablesFromConfig();

/* Assign the sections to be executed, to avoid error 504*/
$ShowSectionInfo = false;
$ProcessSection01 = false;
$ProcessSection02 = false;
$ProcessSection03 = false;
$ProcessSection04 = false;

if (!isset($_GET['Section'])){
	$ProcessSection01 = true;
	$ProcessSection02 = true;
	$ProcessSection03 = true;
	$ProcessSection04 = true;
} else {
	$ShowSectionInfo = true;
		$Title = 'KL General Performance Board Section ' . $_GET['Section'];
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = true;
	} elseif ($_GET['Section'] == '02'){
		$ProcessSection02 = true;
	} elseif ($_GET['Section'] == '03'){
		$ProcessSection03 = true;
	} elseif ($_GET['Section'] == '04'){
		$ProcessSection04 = true;
	}
}

$PeriodNow=GetPeriod(Date($_SESSION['DefaultDateFormat']));
$YesterdaysYear = date('Y', strtotime("-1 days"));

/***************************************************************************************
* TEST AND PLAY AREA	  
***************************************************************************************/

if ($_SESSION['UserID'] == "Ricard"){
//	phpinfo();
}

/***************************************************************************************
* SECTION 1		 
***************************************************************************************/

if ($ProcessSection01){
	if ($ShowSectionInfo){
		$TableTitleText = "Sales Performance Board Section 01";
		ShowTableTitle($TableTitleText);
	}

	if ($KL_SystemAdmin
		OR $KL_GeneralAffairsManager
		OR $KL_SalesTeamManager
		OR $KL_CustomerService
		OR $KL_ShopManager){
		$StartTime = microtime(true);
		AverageSales("Shop", 365, 180, 90, 30, 15, 1, 30, "CurrentYear", "All");
		TimeNeededForExecution("AverageSales", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PeriodDifferenceSales("IMMEDIATE", "Shop",  15);
		TimeNeededForExecution("PeriodDifferenceSales_15", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PeriodDifferenceSales("IMMEDIATE", "Shop",  30);
		TimeNeededForExecution("PeriodDifferenceSales_30", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		PeriodDifferenceSales("IMMEDIATE", "Shop", 365);
		TimeNeededForExecution("PeriodDifferenceSales_365", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_GeneralAffairsManager
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		PeriodDifferenceSales("YEAR", "Shop",  30);
		TimeNeededForExecution("PeriodDifferenceSales_YEAR_30", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		PeriodDifferenceSales("YEAR", "Shop",  180);
		TimeNeededForExecution("PeriodDifferenceSales_YEAR_180", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PeriodDifferenceSales($YesterdaysYear -1, "Shop",  "YTD"); // previous year
		TimeNeededForExecution("PeriodDifferenceSales_YTD", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		OnlineMarketPlacePaymentPending(0, $RootPath);
		TimeNeededForExecution("OnlineMarketPlacePaymentPending", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_GeneralAffairsManager
		OR $KL_ShopManager
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPKL", $_SESSION['AverageInvoiceValueNumberDays']);
		TimeNeededForExecution("AverageCustomerBehaviourByValueInvoice_SHOPKL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPBL", $_SESSION['AverageInvoiceValueNumberDays']);
		TimeNeededForExecution("AverageCustomerBehaviourByValueInvoice_SHOPBL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_ShopManager
		OR $KL_GeneralAffairsManager
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		GeneralCustomerBehaviour("SHOPKL", 30);
		TimeNeededForExecution("GeneralCustomerBehaviour_SHOPKL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GeneralCustomerBehaviour("SHOPBL", 30);
		TimeNeededForExecution("GeneralCustomerBehaviour_SHOPBL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		DailySalesRecords($_SESSION['TopSalesNumberOfDays'], 365 * 2, $_SESSION['TopSalesSince']);
		TimeNeededForExecution("DailySalesRecords", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
}

/***************************************************************************************
* SECTION 2
***************************************************************************************/

if ($ProcessSection02){
	if ($ShowSectionInfo){
		$TableTitleText = "Transfers, Purchasing Performance Board Section 02";
		ShowTableTitle($TableTitleText);
	}

	if ($KL_SystemAdmin 
		OR $KL_GeneralAffairsManager
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		LocationInformationReview($RootPath);
		TimeNeededForExecution("LocationInformationReview", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_GeneralAffairsManager
		OR $KL_ShopManager
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		ActiveTransfersByLocation();
		TimeNeededForExecution("ActiveTransfersByLocation", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ActiveTransferStatus($RootPath);
		TimeNeededForExecution("ActiveTransferStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager
		OR $KL_GeneralAffairsManager){
		$StartTime = microtime(true);
		RecentlyClosedTransferStatus(1, $RootPath);
		TimeNeededForExecution("RecentlyClosedTransferStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ErrorsInTransfers(15, $RootPath);
		TimeNeededForExecution("ErrorsInTransfers", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		FinishedStockDistribution("FORSALE", "LOCATION");
		TimeNeededForExecution("FinishedStockDistribution_FORSALE_LOCATION", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		FinishedStockDistributionByShopAndCategory();
		TimeNeededForExecution("FinishedStockDistributionByShopAndCategory", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		FinishedStockDistribution("FORSALE", "STOCKCATEGORY");
		TimeNeededForExecution("FinishedStockDistribution_FORSALE_STOCKCATEGORY", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPKL", $_SESSION['DaysToPredictFutureSalesPerBrand'], $_SESSION['OptimumDaysStockPOWOForKL'], false);
		TimeNeededForExecution("StockByBrand_SHOPKL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPBL", $_SESSION['DaysToPredictFutureSalesPerBrand'], $_SESSION['OptimumDaysStockPOWOForBlink'], false);
		TimeNeededForExecution("StockByBrand_SHOPBL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPOU", $_SESSION['DaysToPredictFutureSalesPerBrand'], 0, false);
		TimeNeededForExecution("StockByBrand_SHOPOU", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPOK", $_SESSION['DaysToPredictFutureSalesPerBrand'], 0, false);
		TimeNeededForExecution("StockByBrand_SHOPOK", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPOB", $_SESSION['DaysToPredictFutureSalesPerBrand'], 0, false);
		TimeNeededForExecution("StockByBrand_SHOPOB", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPOG", $_SESSION['DaysToPredictFutureSalesPerBrand'], 0, false);
		TimeNeededForExecution("StockByBrand_SHOPOG", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockAdjustmentsByReason(30);
		TimeNeededForExecution("StockAdjustmentsByReason", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		QualityIssuesByReason(30, $RootPath);
		TimeNeededForExecution("QualityIssuesByReason", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		TransferReasons(30);
		TimeNeededForExecution("TransferReasons", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;

	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		GoodsToBeProduced("COMPOA", "ONLYDISCOUNT", $RootPath);
		TimeNeededForExecution("GoodsToBeProduced_ONLYDISCOUNT", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsToBeProduced("COMPOA", "DISCOUNT", $RootPath);
		TimeNeededForExecution("GoodsToBeProduced_DISCOUNT", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GoodsToBeProduced("COMPOA", "ALL", $RootPath);
		TimeNeededForExecution("GoodsToBeProduced_ALL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		ComponentsToObsolete(false, 0, $RootPath);
		TimeNeededForExecution("ComponentsToObsolete", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		PurchaseOrdersProcessTime(75);
		TimeNeededForExecution("PurchaseOrdersProcessTime", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
//		PurchaseOrdersWrongPlannedDates($RootPath);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin OR
		$KL_GeneralAffairsManager){
		$StartTime = microtime(true);
		POStatusControl("PACKAGING","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl_PACKAGING_ARRIVING", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("FORSALE","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl_FORSALE_ARRIVING", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		POStatusControl("OTHERS","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		TimeNeededForExecution("POStatusControl_OTHERS_ARRIVING", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
}

/***************************************************************************************
* SECTION 3
***************************************************************************************/

if ($ProcessSection03){
	if ($ShowSectionInfo){
		$TableTitleText = "Packaging, Displays, Petty Cash, Financial Performance Board Section 03";
		ShowTableTitle($TableTitleText);
	}

	if ($KL_SystemAdmin ){
		$StartTime = microtime(true);
		InsuficientStockForShopPackaging('SHPACK', $_SESSION['Usage_Days_For_Packaging_Stock'], $_SESSION['Forecast_Days_For_Packaging_Stock'], true, false, $RootPath);
		TimeNeededForExecution("InsuficientStockForShopPackaging", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		FinishedStockDistribution("PACKAGING", "LOCATION");
		TimeNeededForExecution("FinishedStockDistribution_PACKAGING_LOCATION", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin  
		OR $KL_ShopManager
		OR $KL_SalesTeamManager){
		$StartTime = microtime(true);
		FinishedStockDistribution("DISPLAYS", "LOCATION");
		TimeNeededForExecution("FinishedStockDistribution_DISPLAYS_LOCATION", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_GeneralAffairsManager
		OR $KL_OperationalLeader
		OR $KL_ShopManager){
		$StartTime = microtime(true);
		MaintenanceTasksDistribution("OPEN", 0, $KL_SystemAdmin, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
		TimeNeededForExecution("MaintenanceTasksDistribution_OPEN", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		MaintenanceTasksDistribution("CLOSED", 30, $KL_SystemAdmin, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
		TimeNeededForExecution("MaintenanceTasksDistribution_CLOSED", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		MaintenanceTasksDistribution("TOTAL", 30, $KL_SystemAdmin, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
		TimeNeededForExecution("MaintenanceTasksDistribution_TOTAL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_GeneralAffairsManager
		OR $KL_OperationalLeader
		OR $KL_ShopManager){
		$StartTime = microtime(true);
		MaintenanceTasksList("OPEN", 0, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
		TimeNeededForExecution("MaintenanceTasksList_OPEN", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		MaintenanceTasksList("CLOSED", 30, ($KL_SPGSeniorOrSupport OR $KL_SPGJunior OR $KL_ShopManager));
		TimeNeededForExecution("MaintenanceTasksList_CLOSED", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		PettyCashStatus("IDR");
		TimeNeededForExecution("PettyCashStatus_IDR", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashStatus("USD");
		TimeNeededForExecution("PettyCashStatus_USD", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashStatus("THB");
		TimeNeededForExecution("PettyCashStatus_THB", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashStatus("EUR");
		TimeNeededForExecution("PettyCashStatus_EUR", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PettyCashStatus("HKD");
		TimeNeededForExecution("PettyCashStatus_HKD", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_AdministrationLeader){
		$StartTime = microtime(true);
		CashStatus($YesterdaysYear, 
					$_SESSION['CashKantorEndLastYearPTADU'], 200000000, 100000000, 
					$_SESSION['CashKantorEndLastYearPTSMH'], 200000000, 100000000, 
					$_SESSION['CashKantorEndLastYearPTBB'], 200000000, 100000000, 
					100000000, 
					75, 1.05,
					  5000, 
					 $_SESSION['USDMaxEasyPurchasePerMonth'],
					 $_SESSION['SaldoADUDanamonUSDMin'],
					 $_SESSION['SaldoADUPayoneerUSDMin'],
					 $_SESSION['SaldoADUPayoneerUSDMax'],
					$PeriodNow, $KL_SystemAdmin);
		TimeNeededForExecution("CashStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		UnbalancedGLTransTX(15, $RootPath);
		TimeNeededForExecution("UnbalancedGLTransTX", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		EmptyAccountsGLTransTX(15, $RootPath);
		TimeNeededForExecution("EmptyAccountsGLTransTX", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	} 
}

/***************************************************************************************
* SECTION 4
***************************************************************************************/

if ($ProcessSection04){
	if ($ShowSectionInfo){
		$TableTitleText = "Human Resources Performance Board Section 04";
		ShowTableTitle($TableTitleText);
	}
	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		HumanResourcesKPIScreenShot();
		TimeNeededForExecution("HumanResourcesKPIScreenShot", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	} 
}

prnMsg("Performed ". $NumberOfTestExecuted . " performance tests",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include(__DIR__ . '/includes/footer.php');

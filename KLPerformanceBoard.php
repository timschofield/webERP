<?php

require(__DIR__ . '/includes/session.php');

$Title = __('KL General Performance Board');
include('includes/header.php');

include('includes/GLFunctions.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLPerformanceBoardFunctions.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLGLFunctions.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

/* Assign the sections to be executed, to avoid error 504*/
$ShowSectionInfo = false;
$ProcessSection01 = false;
$ProcessSection02 = false;
$ProcessSection03 = false;

if (!isset($_GET['Section'])){
	$ProcessSection01 = true;
	$ProcessSection02 = true;
	$ProcessSection03 = true;
}else{
	$ShowSectionInfo = true;
		$Title = 'KL General Performance Board Section ' . $_GET['Section'];
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = true;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = true;
	}elseif($_GET['Section'] == '03'){
		$ProcessSection03 = true;
	}
}

$PeriodNow=GetPeriod(Date($_SESSION['DefaultDateFormat']));
$yesterday_year = date('Y', strtotime("-1 days"));


/***************************************************************************************
* TEST AND PLAY AREA	  
***************************************************************************************/

if ($_SESSION['UserID'] == "Ricard"){
/*	$KL_SystemAdmin = true;
	$KL_OperationalManager = true;
	$KL_OperationalLeader = true;
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
}

/***************************************************************************************
* SECTION 1		 
***************************************************************************************/

if ($ProcessSection01){
	if($ShowSectionInfo){
		$TableTitleText = "Sales Performance Board Section 01";
		ShowTableTitle($TableTitleText);
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_SalesDirector
		OR $KL_CustomerService
		OR $KL_BusinessDevelopmentManager
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
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$StartTime = microtime(true);
		PeriodDifferenceSales("IMMEDIATE", "Shop", 365);
		TimeNeededForExecution("PeriodDifferenceSales_365", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){
		$StartTime = microtime(true);
		PeriodDifferenceSales("YEAR", "Shop",  30);
		TimeNeededForExecution("PeriodDifferenceSales_YEAR_30", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		PeriodDifferenceSales($yesterday_year -1, "Shop",  "YTD"); // previous year
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
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){

//		AverageSales("Online", 365, 180, 90, 30, 15, 1, 30, "CurrentYear", "All");
//		$NumberOfTestExecuted++;
//		PeriodDifferenceSales("IMMEDIATE", "Online",   7);
//		$NumberOfTestExecuted++;
//		PeriodDifferenceSales("IMMEDIATE", "Online",  30);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$StartTime = microtime(true);
		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPKL", 30);
		TimeNeededForExecution("AverageCustomerBehaviourByValueInvoice_SHOPKL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPBL", 30);
		TimeNeededForExecution("AverageCustomerBehaviourByValueInvoice_SHOPBL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
//		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPOU", 30);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_ShopManager
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$StartTime = microtime(true);
		GeneralCustomerBehaviour("SHOPKL", 30);
		TimeNeededForExecution("GeneralCustomerBehaviour_SHOPKL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		GeneralCustomerBehaviour("SHOPBL", 30);
		TimeNeededForExecution("GeneralCustomerBehaviour_SHOPBL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
//		GeneralCustomerBehaviour("SHOPOU", 30);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		$StartTime = microtime(true);
		DailySalesRecords(10, 365 * 2, "2024-08-04");
		TimeNeededForExecution("DailySalesRecords", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
}

/***************************************************************************************
* SECTION 2
***************************************************************************************/

if ($ProcessSection02){
	if($ShowSectionInfo){
		$TableTitleText = "Transfers, Purchasing Performance Board Section 02";
		ShowTableTitle($TableTitleText);
	}

	if ($KL_SystemAdmin){
		$StartTime = microtime(true);
		LocationInformationReview($RootPath);
		TimeNeededForExecution("LocationInformationReview", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopManager
		OR $KL_SalesDirector){
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
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_OperationalManager){
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
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
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
		StockByBrand("SHOPKL", 75, 150, false);
		TimeNeededForExecution("StockByBrand_SHOPKL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPBL", 75, 150, false);
		TimeNeededForExecution("StockByBrand_SHOPBL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPOU", 75, 150, false);
		TimeNeededForExecution("StockByBrand_SHOPOU", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPOK", 75, 150, false);
		TimeNeededForExecution("StockByBrand_SHOPOK", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPOB", 75, 150, false);
		TimeNeededForExecution("StockByBrand_SHOPOB", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		StockByBrand("SHOPOG", 75, 150, false);
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
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$StartTime = microtime(true);
		PurchaseOrdersProcessTime(75);
		TimeNeededForExecution("PurchaseOrdersProcessTime", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
//		PurchaseOrdersWrongPlannedDates($RootPath);
//		$NumberOfTestExecuted++;
	}
/*
	if ($KL_SystemAdmin){
		POStatusControl("","IN NEGOTIATION WITH SUPPLIER", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("PACKAGING","ON PRODUCTION", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","ON PRODUCTION", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","ON PRODUCTION", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","FINISHED BUT NOT PAID", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("PACKAGING","STILL NOT FULLY PAID", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","STILL NOT FULLY PAID", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","STILL NOT FULLY PAID", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","BALI PAID BUT NOT RECEIVED IN KANTOR", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","BALI RECEIVED IN KANTOR BUT NOT PAID", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT SHIPPED BY SUPPLIER", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT RECEIVED IN AYE CARGO", 0, $PeriodNow, $RootPath);
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
		POStatusControl("","RECEIVED IN KANTOR", 0, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
	}
*/	
	if ($KL_SystemAdmin OR
		$KL_OperationalManager){
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
	if($ShowSectionInfo){
		$TableTitleText = "Packaging, Displays, Petty Cash, Financial Performance Board Section 03";
		ShowTableTitle($TableTitleText);
	}

	if ($KL_SystemAdmin ){
		$StartTime = microtime(true);
		InsuficientStockForShopPackaging('SHPACK', 30, FORECAST_DAYS_FOR_PACKAGING_STOCK, true, false, $RootPath);
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
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$StartTime = microtime(true);
		FinishedStockDistribution("DISPLAYS", "LOCATION");
		TimeNeededForExecution("FinishedStockDistribution_DISPLAYS_LOCATION", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_OperationalLeader
		OR $KL_ShopManager){
		$StartTime = microtime(true);
		MaintenanceTasksDistribution("OPEN", 0, $KL_SystemAdmin);
		TimeNeededForExecution("MaintenanceTasksDistribution_OPEN", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		MaintenanceTasksDistribution("CLOSED", 30, $KL_SystemAdmin);
		TimeNeededForExecution("MaintenanceTasksDistribution_CLOSED", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		MaintenanceTasksDistribution("TOTAL", 30, $KL_SystemAdmin);
		TimeNeededForExecution("MaintenanceTasksDistribution_TOTAL", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager
		OR $KL_OperationalLeader
		OR $KL_ShopManager){
		$StartTime = microtime(true);
		MaintenanceTasksList("OPEN", 0);
		TimeNeededForExecution("MaintenanceTasksList_OPEN", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		$StartTime = microtime(true);
		MaintenanceTasksList("CLOSED", 30);
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
		CashStatus($yesterday_year, 
					 20306860, 200000000, 100000000, 
					108448536, 200000000, 100000000, 
					 71654025, 200000000, 100000000, 
					100000000, 
					75, 1.05,
					  5000, 
					100000,
					100000,
					 10000,
					125000,
					 10000,
					 40000,
					$PeriodNow, $KL_SystemAdmin);
		TimeNeededForExecution("CashStatus", $StartTime, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	if ($KL_SystemAdmin){
//		ShowKPIHistory(90);
//		$NumberOfTestExecuted++;
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

prnMsg("Performed ". $NumberOfTestExecuted . " performance tests",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include('includes/footer.php');

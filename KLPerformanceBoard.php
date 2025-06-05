<?php

include ('includes/session.php');
$Title = _('KL General Performance Board');

/* Assign the sections to be executed, to avoid error 504*/
$ShowSectionInfo = FALSE;
$ProcessSection01 = FALSE;
$ProcessSection02 = FALSE;
$ProcessSection03 = FALSE;

if (!isset($_GET['Section'])){
	$ProcessSection01 = TRUE;
	$ProcessSection02 = TRUE;
	$ProcessSection03 = TRUE;
}else{
	$ShowSectionInfo = TRUE;
		$Title = 'KL General Performance Board Section ' . $_GET['Section'];
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = TRUE;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = TRUE;
	}elseif($_GET['Section'] == '03'){
		$ProcessSection03 = TRUE;
	}
}

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

$PeriodNow=GetPeriod(Date($_SESSION['DefaultDateFormat']));
$yesterday_year = date('Y', strtotime("-1 days"));


/***************************************************************************************
* TEST AND PLAY AREA	  
***************************************************************************************/

if ($_SESSION['UserID'] == "Ricard"){
/*	$KL_SystemAdmin = TRUE;
	$KL_OperationalManager = TRUE;
	$KL_OperationalLeader = TRUE;
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
		AverageSales("Shop", 365, 180, 90, 30, 15, 1, 30, "CurrentYear", "All");
		$NumberOfTestExecuted++;
		PeriodDifferenceSales("IMMEDIATE", "Shop",  15);
		$NumberOfTestExecuted++;
		PeriodDifferenceSales("IMMEDIATE", "Shop",  30);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		PeriodDifferenceSales("IMMEDIATE", "Shop", 365);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){
		PeriodDifferenceSales("YEAR", "Shop",  30);
		$NumberOfTestExecuted++;
		PeriodDifferenceSales($yesterday_year -1, "Shop",  "YTD"); // previous year
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		OnlineMarketPlacePaymentPending(0, $RootPath);
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
		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPKL", 30);
		$NumberOfTestExecuted++;
		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPBL", 30);
		$NumberOfTestExecuted++;
//		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPOU", 30);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_ShopManager
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		GeneralCustomerBehaviour("SHOPKL", 30);
		$NumberOfTestExecuted++;
		GeneralCustomerBehaviour("SHOPBL", 30);
		$NumberOfTestExecuted++;
//		GeneralCustomerBehaviour("SHOPOU", 30);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		DailySalesRecords(10, 365 * 2, "2024-08-04");
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
		LocationInformationReview($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopManager
		OR $KL_SalesDirector){
		ActiveTransfersByLocation();
		$NumberOfTestExecuted++;
		ActiveTransferStatus($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector
		OR $KL_OperationalManager){
		RecentlyClosedTransferStatus(1, $RootPath);
		$NumberOfTestExecuted++;
		ErrorsInTransfers(15, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		FinishedStockDistribution("FORSALE", "LOCATION");
		$NumberOfTestExecuted++;
		FinishedStockDistributionByShopAndCategory();
		$NumberOfTestExecuted++;
		FinishedStockDistribution("FORSALE", "STOCKCATEGORY");
		$NumberOfTestExecuted++;
		StockByBrand("SHOPKL", 75, 150, false);
		$NumberOfTestExecuted++;
		StockByBrand("SHOPBL", 75, 150, false);
		$NumberOfTestExecuted++;
		StockByBrand("SHOPOU", 75, 150, false);
		$NumberOfTestExecuted++;
		StockAdjustmentsByReason(30);
		$NumberOfTestExecuted++;
		QualityIssuesByReason(30, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		GoodsToBeProduced("COMPOA", "ONLYDISCOUNT", $RootPath);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "DISCOUNT", $RootPath);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ALL", $RootPath);
		$NumberOfTestExecuted++;
		ComponentsToObsolete(false, 0, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		PurchaseOrdersProcessTime(75);
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
		POStatusControl("PACKAGING","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","ARRIVING IN NEXT DAYS", 75, $PeriodNow, $RootPath);
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
		InsuficientStockForShopPackaging('SHPACK', 30, FORECAST_DAYS_FOR_PACKAGING_STOCK, true, false, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		FinishedStockDistribution("PACKAGING", "LOCATION");
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin  
		OR $KL_ShopManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		FinishedStockDistribution("DISPLAYS", "LOCATION");
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_OperationalLeader
		OR $KL_ShopManager){
		MaintenanceTasksDistribution("OPEN", 0, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		MaintenanceTasksDistribution("CLOSED", 30, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		MaintenanceTasksDistribution("TOTAL", 30, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager
		OR $KL_OperationalLeader
		OR $KL_ShopManager){
		MaintenanceTasksList("OPEN", 0);
		$NumberOfTestExecuted++;
		MaintenanceTasksList("CLOSED", 30);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		PettyCashStatus("IDR");
		$NumberOfTestExecuted++;
		PettyCashStatus("USD");
		$NumberOfTestExecuted++;
		PettyCashStatus("THB");
		$NumberOfTestExecuted++;
		PettyCashStatus("EUR");
		$NumberOfTestExecuted++;
		PettyCashStatus("HKD");
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_AdministrationLeader){
		CashStatus($yesterday_year, 
					 20306860, 200000000, 100000000, 
					108448536, 200000000, 100000000, 
					 71654025, 200000000, 100000000, 
					100000000, 
					75, 1.05,
					  5000, 
					100000,
					125000,
					 10000,
					125000,
					 10000,
					 40000,
					$PeriodNow, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	if ($KL_SystemAdmin){
//		ShowKPIHistory(90);
//		$NumberOfTestExecuted++;
		UnbalancedGLTransTX(15, $RootPath);
		$NumberOfTestExecuted++;
		EmptyAccountsGLTransTX(15, $RootPath);
		$NumberOfTestExecuted++;
	} 
}

prnMsg("Performed ". $NumberOfTestExecuted . " performance tests",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include ('includes/footer.php');

?>
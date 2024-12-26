<?php
define("VERSIONFILE", "4.00");

/* Session started in session.php for password checking and authorisation level check config.php is in turn included in session.php*/

include ('includes/session.php');
$Title = _('KL General Performance Board '. VERSIONFILE);

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
		$Title = 'KL General Performance Board Section ' . $_GET['Section'] . ' ' . VERSIONFILE;
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = TRUE;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = TRUE;
	}elseif($_GET['Section'] == '03'){
		$ProcessSection03 = TRUE;
	}
}

include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');

/* ASSIGN users to groups */
include ('includes/KLRoles.php');
$begintime = time_start();
$NumberOfTestExecuted = 0;

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']));
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
		prnMsg("Sales Performance Board Section 01.",'info');
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_SalesDirector
		OR $KL_SalesTeamOnline
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
		AverageCustomerBehaviourByValueInvoice("Shop", "SHOPOU", 30);
		$NumberOfTestExecuted++;
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
		GeneralCustomerBehaviour("SHOPOU", 30);
		$NumberOfTestExecuted++;
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
		prnMsg("Transfers, Purchasing Performance Board Section 02.",'info');
	}

	if ($KL_SystemAdmin){
		LocationInformationReview($RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopManager
		OR $KL_SalesDirector){
		ActiveTransfersByLocation($RootPath);
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
		StockByBrand("SHOPKL", 75, 150, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		StockByBrand("SHOPBL", 75, 150, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
		StockByBrand("SHOPOU", 75, 150, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}

/*	if ($KL_SystemAdmin){
		GoodsToBeProduced("COMPOA", "ONLYDISCOUNT", $RootPath);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "DISCOUNT", $RootPath);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ALL", $RootPath);
		$NumberOfTestExecuted++;
		ComponentsToObsolete(false, 0, $RootPath);
		$NumberOfTestExecuted++;
	}
*/
	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		PurchaseOrdersProcessTime(75, $RootPath);
		$NumberOfTestExecuted++;
//		PurchaseOrdersWrongPlannedDates($RootPath);
//		$NumberOfTestExecuted++;
	}
/*
	if ($KL_SystemAdmin){
		POStatusControl("","IN NEGOTIATION WITH SUPPLIER", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("PACKAGING","ON PRODUCTION", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","ON PRODUCTION", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","ON PRODUCTION", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","FINISHED BUT NOT PAID", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("PACKAGING","STILL NOT FULLY PAID", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","STILL NOT FULLY PAID", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","STILL NOT FULLY PAID", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","BALI PAID BUT NOT RECEIVED IN KANTOR", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","BALI RECEIVED IN KANTOR BUT NOT PAID", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT SHIPPED BY SUPPLIER", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT RECEIVED IN AYE CARGO", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","PAID NOT RECEIVED IN WANGFOONG CARGO", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","IN AYE CARGO BUT NOT SHIPPED", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","IN WANGFOONG CARGO BUT NOT SHIPPED", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","SHIPPED IN TRANSIT", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","CUSTOMS CLEARANCE", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("","RECEIVED IN KANTOR", 0, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
	}
*/	
	if ($KL_SystemAdmin OR
		$KL_OperationalManager){
		POStatusControl("PACKAGING","ARRIVING IN NEXT DAYS", 75, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("FORSALE","ARRIVING IN NEXT DAYS", 75, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
		POStatusControl("OTHERS","ARRIVING IN NEXT DAYS", 75, $periodnow, $RootPath);
		$NumberOfTestExecuted++;
	}
}

/***************************************************************************************
* SECTION 3
***************************************************************************************/

if ($ProcessSection03){
	if($ShowSectionInfo){
		prnMsg("Packaging, Displays, Petty Cash, Financial Performance Board Section 03.",'info');
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
		MaintenanceTasksDistribution("OPEN", 0);
		$NumberOfTestExecuted++;
	}

	if ($KL_OperationalManager
		OR $KL_OperationalLeader
		OR $KL_ShopManager){
		MaintenanceTasksDistribution("CLOSED", 30);
		$NumberOfTestExecuted++;
		MaintenanceTasksDistribution("TOTAL", 30);
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
					226900000, 200000000, 100000000, 
					143000000, 200000000, 100000000, 
					 40525935, 300000000, 100000000, 
					100000000, 
					75, 1.05,
					  5000, 
					100000,
					125000,
					 10000,
					125000,
					 10000,
					 50000,
					$periodnow, $KL_SystemAdmin);
		$NumberOfTestExecuted++;
	}
	if ($KL_SystemAdmin){
		ShowKPIHistory(90);
		$NumberOfTestExecuted++;
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

/******************************************************************************************************/
/*      FUNCTIONS ASSOCIATED
/******************************************************************************************************/
function AverageCustomerBehaviourByValueInvoice($typereport, $Brand, $NumDaysA){
/* EXPLAIN SQL 2014-05-21	*/
	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));

	if ($typereport == "Shop"){
		$BrandText= BrandTextFromCode($Brand);
		$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					(SELECT SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard)
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
						GROUP BY salesorders.debtorno) AS invoicesum,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
						GROUP BY salesorders.debtorno) AS invoicecount,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) <= " . AVERAGE_INVOICE_VALUE_01 . "
						GROUP BY salesorders.debtorno) AS invoice01,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) >  " . AVERAGE_INVOICE_VALUE_01 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) <= " . AVERAGE_INVOICE_VALUE_02 . "
						GROUP BY salesorders.debtorno) AS invoice02,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) >  " . AVERAGE_INVOICE_VALUE_02 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) <= " . AVERAGE_INVOICE_VALUE_03 . "
						GROUP BY salesorders.debtorno) AS invoice03,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) >  " . AVERAGE_INVOICE_VALUE_03 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) <= " . AVERAGE_INVOICE_VALUE_04 . "
						GROUP BY salesorders.debtorno) AS invoice04,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) >  " . AVERAGE_INVOICE_VALUE_04 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) <= " . AVERAGE_INVOICE_VALUE_05 . "
						GROUP BY salesorders.debtorno) AS invoice05,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) >  " . AVERAGE_INVOICE_VALUE_05 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) <= " . AVERAGE_INVOICE_VALUE_06 . "
						GROUP BY salesorders.debtorno) AS invoice06,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) >  " . AVERAGE_INVOICE_VALUE_06 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) <= " . AVERAGE_INVOICE_VALUE_07 . "
						GROUP BY salesorders.debtorno) AS invoice07,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) >  " . AVERAGE_INVOICE_VALUE_07 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) <= " . AVERAGE_INVOICE_VALUE_08 . "
						GROUP BY salesorders.debtorno) AS invoice08,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard) > " . AVERAGE_INVOICE_VALUE_08 . "
						GROUP BY salesorders.debtorno) AS invoice09
				FROM debtorsmaster, custbranch, locations
				WHERE debtorsmaster.debtorno = custbranch.debtorno
					AND custbranch.defaultlocation = locations.loccode
					AND debtorsmaster.typeid = 2
					AND locations.typeloc = '".$Brand."'
				ORDER BY (SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
						GROUP BY salesorders.debtorno) DESC";
	}else{
		return;
	}
	
	$SumInvoiceSum   = 0;
	$SumInvoiceCount = 0;
	$SumInvoice01    = 0;
	$SumInvoice02    = 0;
	$SumInvoice03    = 0;
	$SumInvoice04    = 0;
	$SumInvoice05    = 0;
	$SumInvoice06    = 0;
	$SumInvoice07    = 0;
	$SumInvoice08    = 0;
	$SumInvoice09    = 0;
						
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Average value of invoice by ') . $BrandText . " " . $typereport . " during the last " . $NumDaysA . " days.".'</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . $typereport . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . 'IDR/Invoice.'. '</th>
						<th class="SortedColumn">' . '# Invoice/Day'. '</th>
						<th class="SortedColumn">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_01,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_02,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_03,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_04,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_05,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_06,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_07,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_08,0) . '</th>
						<th class="SortedColumn">' . '>'. locale_number_format(AVERAGE_INVOICE_VALUE_08,0) . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {

			if ($typereport == "Shop"){
				$Code = $MyRow['debtorno'];
				$Name = $MyRow['name'];
			}else{
				return;
			}
			
			if ($MyRow['invoicesum'] > 0){
				printf('<tr class="striped_row">
						<td class="number">%s</td>
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
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i,
						$Code,
						$Name,
						locale_number_format($MyRow['invoicesum']/$MyRow['invoicecount'],0), 
						locale_number_format($MyRow['invoicecount']/$NumDaysA,1),
						locale_number_format($MyRow['invoice01']/$MyRow['invoicecount']*100,1).'%', 
						locale_number_format($MyRow['invoice02']/$MyRow['invoicecount']*100,1).'%', 
						locale_number_format($MyRow['invoice03']/$MyRow['invoicecount']*100,1).'%', 
						locale_number_format($MyRow['invoice04']/$MyRow['invoicecount']*100,1).'%', 
						locale_number_format($MyRow['invoice05']/$MyRow['invoicecount']*100,1).'%', 
						locale_number_format($MyRow['invoice06']/$MyRow['invoicecount']*100,1).'%', 
						locale_number_format($MyRow['invoice07']/$MyRow['invoicecount']*100,1).'%', 
						locale_number_format($MyRow['invoice08']/$MyRow['invoicecount']*100,1).'%', 
						locale_number_format($MyRow['invoice09']/$MyRow['invoicecount']*100,1).'%'
						);
			}
			$i++;
			$SumInvoiceSum   += $MyRow['invoicesum'];
			$SumInvoiceCount += $MyRow['invoicecount'] ;
			$SumInvoice01    += $MyRow['invoice01'];
			$SumInvoice02    += $MyRow['invoice02'];
			$SumInvoice03    += $MyRow['invoice03'];
			$SumInvoice04    += $MyRow['invoice04'];
			$SumInvoice05    += $MyRow['invoice05'];
			$SumInvoice06    += $MyRow['invoice06'];
			$SumInvoice07    += $MyRow['invoice07'];
			$SumInvoice08    += $MyRow['invoice08'];
			$SumInvoice09    += $MyRow['invoice09'];
		}
		printf('<tr class="striped_row">
				<td class="number">%s</td>
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
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				"",
				"",
				"TOTAL",
				locale_number_format_zero_blank($SumInvoiceSum/$SumInvoiceCount,0), 
				locale_number_format_zero_blank($SumInvoiceCount/$NumDaysA,1),
				locale_number_format_zero_blank($SumInvoice01/$SumInvoiceCount*100,1).'%', 
				locale_number_format_zero_blank($SumInvoice02/$SumInvoiceCount*100,1).'%', 
				locale_number_format_zero_blank($SumInvoice03/$SumInvoiceCount*100,1).'%', 
				locale_number_format_zero_blank($SumInvoice04/$SumInvoiceCount*100,1).'%', 
				locale_number_format_zero_blank($SumInvoice05/$SumInvoiceCount*100,1).'%', 
				locale_number_format_zero_blank($SumInvoice06/$SumInvoiceCount*100,1).'%', 
				locale_number_format_zero_blank($SumInvoice07/$SumInvoiceCount*100,1).'%', 
				locale_number_format_zero_blank($SumInvoice08/$SumInvoiceCount*100,1).'%', 
				locale_number_format_zero_blank($SumInvoice09/$SumInvoiceCount*100,1).'%'
				);
		echo '</tbody></table>
				</div>';
		InsertKPI("Sales", "Avg Invoice Value Last " . $NumDaysA . " days (IDR) " . $BrandText, $SumInvoiceSum/$SumInvoiceCount);
		InsertKPI("Sales", "Avg Invoices Last " . $NumDaysA . " days (INVOICES) " . $BrandText, $SumInvoiceCount/$NumDaysA);
	}
}

function CashStatus($Year, 	
					$CashEndOfPreviousYearADU, 
					$YearlyGoalADU, 
					$MinTransferADU, 
					$CashEndOfPreviousYearSMH, 
					$YearlyGoalSMH, 
					$MinTransferSMH, 
					$CashEndOfPreviousYearBB, 
					$YearlyGoalBB, 
					$MinTransferBB, 
					$MinMoveFree, 
					$USDPODaysSchedule,
					$USDSafetyFactor,
					$USDMinPurchase,
					$USDMaxEasyPurchasePerMonth,
					$SaldoADUGlobalUSDMax,
					$SaldoADUDanamonUSDMin,
					$SaldoADUDanamonUSDMax,
					$SaldoADUPayoneerUSDMin,
					$SaldoADUPayoneerUSDMax,
					$Period, 
					$AdminRole){

    // Consider all year, not until today as some tx are reported into the future
	$EndOfYear = $Year . "-12-31";
	$StartDateYTD = $Year . "-01-01";
	$Today = date('Y-m-d');
	$FirstDateOfMonth = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));

	$SQL = "SELECT lastdate_in_period
			FROM periods
			WHERE periodno = '".$Period."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$LastDateOfMonth = $MyRow['lastdate_in_period'];
	$DaysUntilEndOfMonth = DaysBetween($Today, $LastDateOfMonth)+1;

	////////////////////////////////////////////////////////
	// CASH STATUS ADU IDR CALCULATIONS
	////////////////////////////////////////////////////////

	// Sales Cash PT ADU during the year
	$Account = "410000000AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SalesCashADU = -$MyRow[0];

	// Cash sales still floating (still not received in kantor)
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account IN (SELECT klposcashaccount
										FROM locations
										WHERE partnercode = 'PTADU'
											AND typeloc LIKE 'SHOP%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$FloatingCashADU = $MyRow[0];
	
	// Cash Danamon IDR PTADU to Cash Kantor
	$Account = "111121105AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO SUPP%'
					OR gltrans.narrative LIKE '%BANK TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO BANK%'
					OR gltrans.narrative LIKE '%UANG KECIL%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$BankToCashADU = -$MyRow[0];

	// Expenses ADU Paid by Petty Cash (excluding salaries, Corporate CC)
	$AccountSuffix = "AD";
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $StartDateYTD . "'
				AND pcashdetails.date <= '" . $EndOfYear . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%CEK ADU'
				AND pctabs.tabcode NOT LIKE 'CC-DANAMON%'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%".$AccountSuffix."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ExpensesADUPaidCash = -$MyRow[0];
	
	// Cash in Kantor to Small Suppliers PTADU
	$Account = "510010070AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH%'
					OR gltrans.narrative LIKE '%KANTOR%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToSmallSuppliersADU = $MyRow[0];

	// Cash in Kantor to Pay rents PTADU
	$Account = "211030200AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToRentADU = $MyRow[0];

	// Cash in Kantor to Pay dividends PTADU
	$Account = "614012400AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToDividendsADU = $MyRow[0];

	$CurrentBalanceADU = $CashEndOfPreviousYearADU
						+$SalesCashADU
						+$BankToCashADU
						-$FloatingCashADU
						-$ExpensesADUPaidCash
						-$CashToSmallSuppliersADU
						-$CashToRentADU
						-$CashToDividendsADU;
	$ToBeMovedADU = $CurrentBalanceADU-$YearlyGoalADU ;
	$ToBeTransferredADU = round_multiple_of($ToBeMovedADU, $MinTransferADU);

	////////////////////////////////////////////////////////
	// CASH STATUS ADU IDR SHOW TABLE
	////////////////////////////////////////////////////////

	echo '<p class="page_title_text" align="center"><strong>' . 'Status Cash IDR PT. Angin Dingin Utara ' . $Year . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . 'Concept' . '</th>
					<th>' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash ADU in Brankas Kantor end of ' . ($Year-1), 
			locale_number_format($CashEndOfPreviousYearADU,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Sales Retail PT ADU Cash during '. $Year, 
			locale_number_format($SalesCashADU,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Floating Cash still in shops PT ADU', 
			locale_number_format(-$FloatingCashADU,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash received from shops PT ADU in Brankas Kantor during '. $Year, 
			locale_number_format($SalesCashADU-$FloatingCashADU,0)
			);
	if ($BankToCashADU >= 0){
		$Text = 'Total withdrawal from Danamon IDR PTADU to Brankas Kantor';
	}else{
		$Text = 'Total deposit from Brankas Kantor to Danamon IDR PTADU ';
	}
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			$Text, 
			locale_number_format($BankToCashADU,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PT ADU Paid by Petty Cash (excluding checks, salaries, Corporate CC)', 
			locale_number_format(-$ExpensesADUPaidCash,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PT ADU Small Suppliers Paid from Cash Kantor', 
			locale_number_format(-$CashToSmallSuppliersADU,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PT ADU Rent Paid from Cash Kantor', 
			locale_number_format(-$CashToRentADU,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Dividends PT ADU Paid from Cash Kantor', 
			locale_number_format(-$CashToDividendsADU,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current Cash PT ADU in Brankas Kantor', 
			locale_number_format($CurrentBalanceADU,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash ADU in Brankas Kantor Goal for end of '. $Year, 
			locale_number_format($YearlyGoalADU,0)
			);
	if ($ToBeMovedADU >= 0){
		$Text = 'Cash ADU OVER goal in Brankas Kantor';
	}else{
		$Text = 'Cash ADU BELOW goal in Brankas Kantor';
	}
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			$Text, 
			locale_number_format(abs($ToBeMovedADU),0)
			);
			
	if ($ToBeTransferredADU != 0){
		if ($ToBeTransferredADU > 0){
			$Text = 'ACTION NEEDED -> Deposit from Brankas Kantor to Danamon IDR ADU';
		}elseif ($ToBeTransferredADU < 0){
			$Text = 'ACTION NEEDED -> Withdrawal from Danamon IDR ADU to Brankas Kantor';
		}
		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				$Text, 
				locale_number_format(abs($ToBeTransferredADU),0)
				);
	}
	echo '</tbody></table>
		</div>';
	
	////////////////////////////////////////////////////////
	// CASH STATUS ADU USD CALCULATIONS
	////////////////////////////////////////////////////////

	$SQL = "SELECT rate
			FROM currencies
			WHERE currabrev = 'USD'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CurrentUSDRate = $MyRow['rate'];
	
	$Account = "111203010AD"; // Danamon PTADU USD in IDR
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode = '" . $Account . "'
				AND chartdetails.period = ". $Period . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SaldoADUDanamonUSD = round($MyRow['saldo']*$CurrentUSDRate, 0);

	$Account = "111203020AD"; // Payoneer PTADU USD in IDR
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode = '" . $Account . "'
				AND chartdetails.period = ". $Period . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SaldoADUPayoneerUSD = round($MyRow['saldo']*$CurrentUSDRate, 0);

	$Account = "111204030"; // Cash in Agent Aye Cargo in BKK in IDR
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode = '" . $Account . "'
				AND chartdetails.period = ". $Period . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SaldoAyeCargoUSD = round($MyRow['saldo']*$CurrentUSDRate, 0);

	$Account = "111203010AD"; // USD already exchanged current month
	$SQL = "SELECT SUM(banktrans.amount) AS saldo
			FROM banktrans
			WHERE banktrans.bankact = '" . $Account . "'
				AND banktrans.transdate >= '". $FirstDateOfMonth . "'
				AND banktrans.amount > 0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$USDAlreadyExhangedThisMonth = round($MyRow['saldo'], 0);

	$PORunningTotalUSD = round(GetLastKPIValue("Purchase Orders","PO Items for sale arriving next % days (IDR)")*$CurrentUSDRate,0);
	$POPaymentsPendingUSD = round(GetLastKPIValue("Purchase Orders","Payments pending%")*$CurrentUSDRate,0);
	$POPaymentsPendingUSDuntilEndOfMonth = $PORunningTotalUSD / $USDPODaysSchedule * $DaysUntilEndOfMonth * $USDSafetyFactor;
	$SaldoUSD = $SaldoADUDanamonUSD + $SaldoADUPayoneerUSD + $SaldoAyeCargoUSD;
	$ShortageUSDuntilEndOfMonth = $POPaymentsPendingUSDuntilEndOfMonth - $SaldoUSD;

	if ($SaldoUSD <= $USDMaxEasyPurchasePerMonth){
		if (($USDAlreadyExhangedThisMonth < $USDMaxEasyPurchasePerMonth) 
			AND ($SaldoADUDanamonUSD < $SaldoADUDanamonUSDMax)){
			$ToBeExchanged = round_multiple_of(min($USDMaxEasyPurchasePerMonth - $USDAlreadyExhangedThisMonth,
													$SaldoADUGlobalUSDMax - $SaldoUSD), 5000);	
		}elseif ($ShortageUSDuntilEndOfMonth > $SaldoADUDanamonUSD){
			$ToBeExchanged = round_multiple_of($ShortageUSDuntilEndOfMonth, 5000);	
		}else{
			$ToBeExchanged = 0;	
		}
	}else{
		$ToBeExchanged = 0;	
	}
	
	if ($SaldoADUPayoneerUSD < $SaldoADUPayoneerUSDMin){
		$ToBeTransferredToPayoneer = round_multiple_of(min($SaldoADUPayoneerUSDMax - $SaldoADUPayoneerUSD, 
															$SaldoADUDanamonUSD - $SaldoADUDanamonUSDMin), 5000);	
	}else{
		$ToBeTransferredToPayoneer = 0;
	}

	////////////////////////////////////////////////////////
	// CASH STATUS ADU USD SHOW TABLE
	////////////////////////////////////////////////////////

	echo '<p class="page_title_text" align="center"><strong>' . 'Status USD PT. Angin Dingin Utara ' . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . 'Concept' . '</th>
					<th class="SortedColumn">' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Running PO for items for sale (USD approx)', 
			locale_number_format($PORunningTotalUSD,0)
			);

	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Pending payments until end of month ('.$DaysUntilEndOfMonth.' days) (USD approx)', 
			locale_number_format($POPaymentsPendingUSDuntilEndOfMonth,0)
			);

	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current balance Danamon USD ADU (USD approx)', 
			locale_number_format($SaldoADUDanamonUSD,0)
			);

	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current balance Payoneer USD ADU (USD approx)', 
			locale_number_format($SaldoADUPayoneerUSD,0)
			);

	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current balance Aye Cargo ADU (USD approx)', 
			locale_number_format($SaldoAyeCargoUSD,0)
			);

	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current balance available USD ADU (USD approx)', 
			locale_number_format($SaldoUSD,0)
			);

	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'USD already exchanged from IDR this month for ADU (USD approx)', 
			locale_number_format($USDAlreadyExhangedThisMonth,0)
			);

	printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'USD needed until end of month ('.$DaysUntilEndOfMonth.' days) (USD approx)', 
				locale_number_format(max($ShortageUSDuntilEndOfMonth,0),0)
				);

	if ($ToBeExchanged > 0){
		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'ACTION NEEDED --> Purchase USD from ADU Danamon IDR to ADU Danamon USD', 
				locale_number_format($ToBeExchanged)
				);
	}
	
	if ($ToBeTransferredToPayoneer > 0){
		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'ACTION NEEDED --> Transfer from ADU Danamon USD to ADU Payoneer USD', 
				locale_number_format($ToBeTransferredToPayoneer)
				);
	}

	echo '</tbody></table>
		</div>';

	////////////////////////////////////////////////////////
	// CASH STATUS SMH IDR CALCULATIONS
	////////////////////////////////////////////////////////

	// Sales Cash PT SMH during the year
	$Account = "410000000SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SalesCashSMH = -$MyRow[0];

	// Cash sales still floating (still not received in kantor)
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account IN (SELECT klposcashaccount
										FROM locations
										WHERE partnercode = 'PTSMH'
											AND typeloc LIKE 'SHOP%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$FloatingCashSMH = $MyRow[0];
	
	// Cash Danamon IDR PTSMH to Cash Kantor
	$Account = "111121105SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO SUPP%'
					OR gltrans.narrative LIKE '%BANK TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO BANK%'
					OR gltrans.narrative LIKE '%UANG KECIL%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$BankToCashSMH = -$MyRow[0];
	
	// Cash Mandiri IDR PTSMH to Cash Kantor
	$Account = "111121100SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO SUPP%'
					OR gltrans.narrative LIKE '%BANK TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO BANK%'
					OR gltrans.narrative LIKE '%UANG KECIL%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$BankToCashSMH -= $MyRow[0];

	// Expenses SMH Paid by Petty Cash (excluding salaries, Corporate CC)
	$AccountSuffix = "SM";
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $StartDateYTD . "'
				AND pcashdetails.date <= '" . $EndOfYear . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%CEK SMH'
				AND pctabs.tabcode NOT LIKE 'CC-DANAMON%'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%".$AccountSuffix."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ExpensesSMHPaidCash = -$MyRow[0];
	
	// Cash in Kantor to Small Suppliers PTSMH
	$Account = "510010070SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH%'
					OR gltrans.narrative LIKE '%KANTOR%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToSmallSuppliersSMH = $MyRow[0];

	// Cash in Kantor to Pay rents PTSMH
	$Account = "211030200SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToRentSMH = $MyRow[0];

	// Cash in Kantor to Pay dividends PTSMH
	$Account = "614012400SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToDividendsSMH = $MyRow[0];

	$CurrentBalanceSMH = $CashEndOfPreviousYearSMH
						+$SalesCashSMH
						+$BankToCashSMH
						-$FloatingCashSMH
						-$ExpensesSMHPaidCash
						-$CashToSmallSuppliersSMH
						-$CashToRentSMH
						-$CashToDividendsSMH;
	$ToBeMovedSMH = $CurrentBalanceSMH-$YearlyGoalSMH ;
	$ToBeTransferredSMH = round_multiple_of($ToBeMovedSMH, $MinTransferSMH);

	////////////////////////////////////////////////////////
	// CASH STATUS SMH IDR SHOW TABLE
	////////////////////////////////////////////////////////

	echo '<p class="page_title_text" align="center"><strong>' . 'Status Cash IDR PT. Sungai Mutiara Hitam ' . $Year . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . 'Concept' . '</th>
					<th class="SortedColumn">' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash SMH in Brankas Kantor end of ' . ($Year-1), 
			locale_number_format($CashEndOfPreviousYearSMH,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Sales Retail PT SMH Cash during '. $Year, 
			locale_number_format($SalesCashSMH,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Floating Cash still in shops PT SMH', 
			locale_number_format(-$FloatingCashSMH,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash received from shops PT SMH in Brankas Kantor during '. $Year, 
			locale_number_format($SalesCashSMH-$FloatingCashSMH,0)
			);
	if ($BankToCashSMH >= 0){
		$Text = 'Total withdrawal from Danamon/Mandiri IDR PTSMH to Brankas Kantor';
	}else{
		$Text = 'Total deposit from Brankas Kantor to Danamon/Mandiri IDR PTSMH ';
	}
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			$Text, 
			locale_number_format($BankToCashSMH,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PT SMH Paid by Petty Cash (excluding checks, salaries, Corporate CC)', 
			locale_number_format(-$ExpensesSMHPaidCash,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PT SMH Small Suppliers Paid from Cash Kantor', 
			locale_number_format(-$CashToSmallSuppliersSMH,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PT SMH Rent Paid from Cash Kantor', 
			locale_number_format(-$CashToRentSMH,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Dividends PT SMH Paid from Cash Kantor', 
			locale_number_format(-$CashToDividendsSMH,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current Cash PT SMH in Brankas Kantor', 
			locale_number_format($CurrentBalanceSMH,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash SMH in Brankas Kantor Goal for end of '. $Year, 
			locale_number_format($YearlyGoalSMH,0)
			);
	if ($ToBeMovedSMH >= 0){
		$Text = 'Cash SMH OVER goal in Brankas Kantor';
	}else{
		$Text = 'Cash SMH BELOW goal in Brankas Kantor';
	}
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			$Text, 
			locale_number_format(abs($ToBeMovedSMH),0)
			);
			
	if ($ToBeTransferredSMH != 0){
		if ($ToBeTransferredSMH > 0){
			$Text = 'ACTION NEEDED -> Deposit from Brankas Kantor to Danamon IDR SMH';
		}elseif ($ToBeTransferredSMH < 0){
			$Text = 'ACTION NEEDED -> Withdrawal from Danamon IDR SMH to Brankas Kantor';
		}
		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				$Text, 
				locale_number_format(abs($ToBeTransferredSMH),0)
				);
	}
	echo '</tbody></table>
		</div>';

	////////////////////////////////////////////////////////
	// CASH STATUS BB IDR CALCULATIONS
	////////////////////////////////////////////////////////

	// Sales PTBB in Cash during the Year
	$Account = "410000000BB";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SalesCashBB = -$MyRow[0];

	// Cash sales still floating (still not received in kantor)
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account IN (SELECT klposcashaccount
										FROM locations
										WHERE partnercode = 'PTBB'
											AND typeloc LIKE 'SHOP%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$FloatingCashBB = $MyRow[0];

	// Cash Danamon IDR PTBB to Cash Kantor
	$Account = "111121105BB";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO SUPP%'
					OR gltrans.narrative LIKE '%BANK TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO BANK%'
					OR gltrans.narrative LIKE '%UANG KECIL%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$BankToCashBB = -$MyRow[0];

	// Expenses PT Paid by Petty Cash (excluding salaries, Corporate CC)
	$AccountSuffix = "BB";
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $StartDateYTD . "'
				AND pcashdetails.date <= '" . $EndOfYear . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%CEK BB'
				AND pctabs.tabcode NOT LIKE 'CC-DANAMON%'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%".$AccountSuffix."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ExpensesBBPaidCash = -$MyRow[0];
	
	// Cash in Kantor to Small Suppliers PTBB
	$Account = "510010070BB";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH%'
					OR gltrans.narrative LIKE '%KANTOR%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToSmallSuppliersBB = $MyRow[0];

	// Cash in Kantor to Pay rents PTBB
	$Account = "211030200BB";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToRentBB = $MyRow[0];
	
	// Cash in Kantor to Pay dividends PTBB
	$Account = "614012400BB";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToDividendsBB = $MyRow[0];

	$CurrentBalanceBB = $CashEndOfPreviousYearBB
						+$SalesCashBB
						+$BankToCashBB
						-$FloatingCashBB
						-$ExpensesBBPaidCash
						-$CashToSmallSuppliersBB
						-$CashToRentBB
						-$CashToDividendsBB;
	$ToBeMovedBB = $CurrentBalanceBB-$YearlyGoalBB ;
	$ToBeTransferredBB = round_multiple_of($ToBeMovedBB, $MinTransferBB);	

	////////////////////////////////////////////////////////
	// CASH STATUS BB IDR SHOW TABLE
	////////////////////////////////////////////////////////

	echo '<p class="page_title_text" align="center"><strong>' . 'Status Cash IDR PT. Bumi Biru ' . $Year . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . 'Concept' . '</th>
					<th class="SortedColumn">' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash PTBB in Brankas Kantor end of ' . ($Year-1), 
			locale_number_format($CashEndOfPreviousYearBB,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Sales Retail PTBB Cash during '. $Year, 
			locale_number_format($SalesCashBB,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Floating Cash still in shops PTBB', 
			locale_number_format(-$FloatingCashBB,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash received from shops PTBB in Brankas Kantor during '. $Year, 
			locale_number_format($SalesCashBB-$FloatingCashBB,0)
			);
	if ($BankToCashBB >= 0){
		$Text = 'Total withdrawal from Danamon IDR PTBB to Brankas Kantor';
	}else{
		$Text = 'Total deposit from Brankas Kantor to Danamon IDR PTBB ';
	}
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			$Text, 
			locale_number_format($BankToCashBB,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PTBB Paid by Petty Cash (excluding checks, salaries, Corporate CC)', 
			locale_number_format(-$ExpensesBBPaidCash,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PTBB Small Suppliers Paid from Cash Kantor', 
			locale_number_format(-$CashToSmallSuppliersBB,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PTBB Rent Paid from Cash Kantor', 
			locale_number_format(-$CashToRentBB,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Dividends PTBB Paid from Cash Kantor', 
			locale_number_format(-$CashToDividendsBB,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current Cash PTBB in Brankas Kantor', 
			locale_number_format($CurrentBalanceBB,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash PTBB in Brankas Kantor Goal for end of '. $Year, 
			locale_number_format($YearlyGoalBB,0)
			);
	if ($ToBeMovedBB >= 0){
		$Text = 'Cash PTBB OVER goal in Brankas Kantor';
	}else{
		$Text = 'Cash PTBB BELOW goal in Brankas Kantor';
	}
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			$Text, 
			locale_number_format(abs($ToBeMovedBB),0)
			);

	if ($ToBeTransferredBB != 0){
		if ($ToBeTransferredBB > 0){
			$Text = 'ACTION NEEDED -> Deposit from Brankas Kantor to Danamon IDR BB';
		}elseif ($ToBeTransferredBB < 0){
			$Text = 'ACTION NEEDED -> Withdrawal from Danamon IDR BB to Brankas Kantor';
		}
		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				$Text, 
				locale_number_format(abs($ToBeTransferredBB),0)
				);
	}
	echo '</tbody></table>
		</div>';	

	////////////////////////////////////////////////////////
	// CASH STATUS BRANKAS KANTOR & SHAREHOLDERS IDR CALCULATIONS
	////////////////////////////////////////////////////////

	$Account = "111111200";
	$SQL = "SELECT (bfwd + actual) as saldo, accountname
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode = '" . $Account . "'
				AND chartdetails.period = ". $Period . "";
				
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SaldoBrankasKantor = $MyRow['saldo'];

	$Account = "111131100";
	$SQL = "SELECT (bfwd + actual) as saldo, accountname
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode = '" . $Account . "'
				AND chartdetails.period = ". $Period . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SaldoBrankasShareholders = $MyRow['saldo'];
		
	////////////////////////////////////////////////////////
	// CASH STATUS STATUS BRANKAS KANTOR & SHAREHOLDERS IDR SHOW TABLE
	////////////////////////////////////////////////////////
		
	if ($AdminRole){
		echo '<p class="page_title_text" align="center"><strong>' . 'Status Cash IDR Brankas Kantor and Shareholders ' . $Year . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th>' . 'Concept' . '</th>
						<th>' . 'Value' . '</th>
					</tr>
				</thead>
				<tbody>';
		
		$FreeSaldoBrankasKantor = $SaldoBrankasKantor - $CurrentBalanceADU - $CurrentBalanceSMH - $CurrentBalanceBB;
		$FreeSaldoBrankasShareholders = $SaldoBrankasShareholders + $FreeSaldoBrankasKantor;
		$ToBeDistributedToShareholders = round_multiple_of($FreeSaldoBrankasShareholders, $MinMoveFree);	

		$i = 1;
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Cash belonging to PTADU', 
				locale_number_format($CurrentBalanceADU,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Cash belonging to PTSMH', 
				locale_number_format($CurrentBalanceSMH,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Cash belonging to PTBB', 
				locale_number_format($CurrentBalanceBB,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Total Cash PTADU+PTSMH+PTBB', 
				locale_number_format($CurrentBalanceADU+$CurrentBalanceSMH+$CurrentBalanceBB,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Saldo Cash in Brankas Kantor ', 
				locale_number_format($SaldoBrankasKantor,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Saldo Cash in Brankas Shareholders', 
				locale_number_format($SaldoBrankasShareholders,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Total Saldo Cash', 
				locale_number_format($SaldoBrankasKantor + $SaldoBrankasShareholders,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Free Cash', 
				locale_number_format($FreeSaldoBrankasShareholders,0)
				);
		if ($ToBeDistributedToShareholders !=0){
			if ($FreeSaldoBrankasShareholders >= 0){
				$Text = 'ACTION NEEDED -> Distribute Cash from Brankas Shareholders to Shareholders';
			}else{
				$Text = 'ACTION NEEDED -> Get Cash from Shareholders to Brankas Shareholders';
			}
			printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				$Text, 
				locale_number_format(abs($ToBeDistributedToShareholders),0)
				);
		}
		echo '</tbody></table>
			</div>';	

		InsertKPI("Cash", "Free Cash", $FreeSaldoBrankasShareholders);
	}

	InsertKPI("Cash", "Cash PT ADU", $CurrentBalanceADU);
	InsertKPI("Cash", "Cash PT SMH", $CurrentBalanceSMH);
	InsertKPI("Cash", "Cash PT BB", $CurrentBalanceBB);

}

function DailySalesRecords($Days, $NumDays, $Since){

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	
	if ($Since != ''){
		if ($Since >= $FromDate){
			$FromDate = $Since;
		}
	}
	$SQL = "SELECT salesorders.orddate,
				SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) AS sales
			FROM salesorders
			INNER JOIN salesorderdetails ON
				salesorders.orderno=salesorderdetails.orderno
			INNER JOIN debtorsmaster ON 
				salesorders.debtorno = debtorsmaster.debtorno
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
				AND salesorders.orddate >= '" . $FromDate . "'
			GROUP BY salesorders.orddate
			ORDER BY SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) DESC
			LIMIT ". $Days . "";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Top ') . $Days . _(' retail sales days since '). ConvertSQLDate($FromDate) .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' .  _('#') . '</th>
						<th class="SortedColumn">' .  _('Date') . '</th>
						<th class="SortedColumn">' . _('Sales') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while (($MyRow = DB_fetch_array($Result)) AND ($i <= $Days)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($i,0),
					ConvertSQLDate($MyRow['orddate']),
					locale_number_format($MyRow['sales'],0)
					);
			$i++;
		}
		echo '</tbody></table>
				</div>
				</form>';
	}
}

function GeneralCustomerBehaviour($Brand, $NumDaysA){
	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));
	$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-365));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1-365));

	$BrandText= BrandTextFromCode($Brand);

	$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				(SELECT SUM(salesorderdetails.qtyinvoiced)
					FROM salesorders, salesorderdetails
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorderdetails.orderno = salesorders.orderno
					GROUP BY salesorders.debtorno) AS itemcount,
				(SELECT SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard)
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) AS invoicesum,
				(SELECT COUNT(DISTINCT(salesorders.orderno))
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) AS invoicecount,
				(SELECT SUM(salesorderdetails.qtyinvoiced)
					FROM salesorders, salesorderdetails
					WHERE salesorders.orddate >=  '" . $StartDateB . "'
						AND salesorders.orddate <= '" . $YesterdayB . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorderdetails.orderno = salesorders.orderno
					GROUP BY salesorders.debtorno) AS itemcount_lastyear,
				(SELECT SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard)
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateB . "'
						AND salesorders.orddate <= '" . $YesterdayB . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) AS invoicesum_lastyear,
				(SELECT COUNT(DISTINCT(salesorders.orderno))
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateB . "'
						AND salesorders.orddate <= '" . $YesterdayB . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) AS invoicecount_lastyear
			FROM debtorsmaster, custbranch, locations
			WHERE debtorsmaster.debtorno = custbranch.debtorno
				AND custbranch.defaultlocation = locations.loccode
				AND debtorsmaster.typeid = 2
				AND locations.typeloc = '".$Brand."'
			ORDER BY (SELECT COUNT(DISTINCT(salesorders.orderno))
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) DESC";
	
						
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "General Customer Behaviour by " . $BrandText  . " shop during the last " . $NumDaysA . " days.".'</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="3"></th>
						<th colspan="5">' . 'This year'. '</th>
						<th colspan="5">' . 'Last year'. '</th>
					</tr>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Shop') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . 'IDR/Invoice'. '</th>
						<th class="SortedColumn">' . 'IDR/Piece'. '</th>
						<th class="SortedColumn">' . '# Invoice/Day'. '</th>
						<th class="SortedColumn">' . '# Pcs/Day'. '</th>
						<th class="SortedColumn">' . '# Pcs/Inv'. '</th>
						<th class="SortedColumn">' . 'IDR/Invoice'. '</th>
						<th class="SortedColumn">' . 'IDR/Piece'. '</th>
						<th class="SortedColumn">' . '# Invoice/Day'. '</th>
						<th class="SortedColumn">' . '# Pcs/Day'. '</th>
						<th class="SortedColumn">' . '# Pcs/Inv'. '</th>
					</tr>
				</thead>
				<tbody>';
		$TotalInvoiceSum = 0;
		$TotalInvoiceCount = 0;
		$TotalItemCount = 0;		
		$TotalInvoiceSumLastYear = 0;
		$TotalInvoiceCountLastYear = 0;
		$TotalItemCountLastYear = 0;
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$i++;
			$Code = $MyRow['debtorno'];
			$Name = $MyRow['name'];
			
			if ($MyRow['invoicesum'] > 0){

				$TotalInvoiceSum += $MyRow['invoicesum'];
				$TotalInvoiceCount += $MyRow['invoicecount'];
				$TotalItemCount += $MyRow['itemcount'];		
				$TotalInvoiceSumLastYear += $MyRow['invoicesum_lastyear'];
				$TotalInvoiceCountLastYear += $MyRow['invoicecount_lastyear'];
				$TotalItemCountLastYear += $MyRow['itemcount_lastyear'];		

				$AvgIDRPerInvoice = ($MyRow['invoicecount'] !=0) ? $MyRow['invoicesum']/$MyRow['invoicecount'] : 0;
				$AvgIDRPerItem = ($MyRow['itemcount'] !=0) ? $MyRow['invoicesum']/$MyRow['itemcount'] : 0;
				$AvgInvoicesPerDay = ($NumDaysA != 0) ? $MyRow['invoicecount']/$NumDaysA : 0;
				$AvgItemsPerDay = ($NumDaysA != 0) ? $MyRow['itemcount']/$NumDaysA : 0;
				$AvgItemsPerInvoice = ($MyRow['invoicecount'] != 0) ? $MyRow['itemcount']/$MyRow['invoicecount'] : 0;

				$AvgIDRPerInvoiceLastYear = ($MyRow['invoicecount_lastyear'] !=0) ? $MyRow['invoicesum_lastyear']/$MyRow['invoicecount_lastyear'] : 0;
				$AvgIDRPerItemLastYear = ($MyRow['itemcount_lastyear'] !=0) ? $MyRow['invoicesum_lastyear']/$MyRow['itemcount_lastyear'] : 0;
				$AvgInvoicesPerDayLastYear = ($NumDaysA != 0) ? $MyRow['invoicecount_lastyear']/$NumDaysA: 0;
				$AvgItemsPerDayLastYear = ($NumDaysA != 0) ? $MyRow['itemcount_lastyear']/$NumDaysA : 0;
				$AvgItemsPerInvoiceLastYear = ($MyRow['invoicecount_lastyear'] != 0) ? $MyRow['itemcount_lastyear']/$MyRow['invoicecount_lastyear'] : 0;

				printf('<tr class="striped_row">
						<td class="number">%s</td>
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
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i,
						$Code,
						$Name,
						locale_number_format_zero_blank($AvgIDRPerInvoice,0), 
						locale_number_format_zero_blank($AvgIDRPerItem,0), 
						locale_number_format_zero_blank($AvgInvoicesPerDay,1),
						locale_number_format_zero_blank($AvgItemsPerDay,1),
						locale_number_format_zero_blank($AvgItemsPerInvoice,1),
						locale_number_format_zero_blank($AvgIDRPerInvoiceLastYear,0), 
						locale_number_format_zero_blank($AvgIDRPerItemLastYear,0), 
						locale_number_format_zero_blank($AvgInvoicesPerDayLastYear,1),
						locale_number_format_zero_blank($AvgItemsPerDayLastYear,1),
						locale_number_format_zero_blank($AvgItemsPerInvoiceLastYear,1)
						);
				
			}
		}
		printf('<tr class="striped_row">
				<td class="number">%s</td>
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
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				"",
				"",
				"Brand Average",
				locale_number_format_zero_blank($TotalInvoiceSum/$TotalInvoiceCount,0), 
				locale_number_format_zero_blank($TotalInvoiceSum/$TotalItemCount,0), 
				locale_number_format_zero_blank($TotalInvoiceCount/$NumDaysA,1),
				locale_number_format_zero_blank($TotalItemCount/$NumDaysA,1),
				locale_number_format_zero_blank($TotalItemCount/$TotalInvoiceCount,1),
				locale_number_format_zero_blank($TotalInvoiceSumLastYear/$TotalInvoiceCountLastYear,0), 
				locale_number_format_zero_blank($TotalInvoiceSumLastYear/$TotalItemCountLastYear,0), 
				locale_number_format_zero_blank($TotalInvoiceCountLastYear/$NumDaysA,1),
				locale_number_format_zero_blank($TotalItemCountLastYear/$NumDaysA,1),
				locale_number_format_zero_blank($TotalItemCountLastYear/$TotalInvoiceCountLastYear,1)
				);
		echo '</tbody></table>
				</div>';
		InsertKPI("Sales", "Items x Invoice Last " . $NumDaysA . " days (ITEMS) " . $BrandText, $TotalItemCount/$TotalInvoiceCount);
	}
}

function PackagingStatusForBlink($RootPath){

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.rldaysforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-L') AS qty_box_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-L') AS rl_box_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKBX02-L') AS ot_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-M') AS qty_box_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-M') AS rl_box_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKBX02-M') AS ot_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-S') AS qty_box_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-S') AS rl_box_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKBX02-S') AS ot_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB03-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB03-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB03-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-L') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-L') AS rl_shopping_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKSB04-L') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-M') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-M') AS rl_shopping_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKSB04-M') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-S') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-S') AS rl_shopping_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKSB04-S') AS ot_shopping_s
			FROM locations
			WHERE locations.typeloc = 'SHOPBL'
				OR locations.loccode IN " . LIST_PACAKING_LOCATIONS . "
			ORDER BY locations.loccode";

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$i = 1;
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'BLINK Shop Packaging Stock Status by Shop' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th></th>
								<th></th>
								<th></th>
								<th colspan="3">' . _('BLINK Box L') . '</th>
								<th colspan="3">' . _('BLINK Box M') . '</th>
								<th colspan="3">' . _('BLINK Box S') . '</th>
								<th colspan="3">' . _('BLINK PouchBag L') . '</th>
								<th colspan="3">' . _('BLINK PouchBag M') . '</th>
								<th colspan="3">' . _('BLINK PouchBag S') . '</th>
								<th colspan="3">' . _('BLINK ShoppingBag L') . '</th>
								<th colspan="3">' . _('BLINK ShoppingBag M') . '</th>
								<th colspan="3">' . _('BLINK ShoppingBag S') . '</th>
							</tr>
							<tr>
								<th class="SortedColumn">' . _('BLINK Shop') . '</th>
								<th class="SortedColumn">' . _('Days RL') . '</th>
								<th class="SortedColumn">' . _('Factor') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}

			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['locationname'], 
					$MyRow['rldaysforpackaging'], 
					$MyRow['rlfactorforpackaging'], 
					locale_number_format_zero_blank($MyRow['qty_box_l'],0), 
					locale_number_format_zero_blank($MyRow['ot_box_l'],0),
					locale_number_format_zero_blank($MyRow['rl_box_l'],0),
					locale_number_format_zero_blank($MyRow['qty_box_m'],0), 
					locale_number_format_zero_blank($MyRow['ot_box_m'],0),
					locale_number_format_zero_blank($MyRow['rl_box_m'],0),
					locale_number_format_zero_blank($MyRow['qty_box_s'],0), 
					locale_number_format_zero_blank($MyRow['ot_box_s'],0),
					locale_number_format_zero_blank($MyRow['rl_box_s'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_l'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_l'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_l'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_m'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_m'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_m'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_s'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_s'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_s'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($MyRow['ot_shopping_l'],0),
					locale_number_format_zero_blank($MyRow['rl_shopping_l'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($MyRow['ot_shopping_m'],0),
					locale_number_format_zero_blank($MyRow['rl_shopping_m'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($MyRow['ot_shopping_s'],0),
					locale_number_format_zero_blank($MyRow['rl_shopping_s'],0)
					);

			$i++;
		}
		if (!$ShowHeader){
			echo '</tbody></table>
				</div>';
		}
	}
}

function PackagingStatusForKapalLaut($RootPath){

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.rldaysforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS qty_box_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS rl_box_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKBX01-L') AS ot_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS qty_box_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS rl_box_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKBX01-M') AS ot_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS qty_box_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS rl_box_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKBX01-S') AS ot_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB01-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB01-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB01-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS rl_shopping_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKSB02-L') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS rl_shopping_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKSB02-M') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS rl_shopping_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKSB02-S') AS ot_shopping_s
			FROM locations
			WHERE locations.typeloc = 'SHOPKL'
				OR locations.loccode IN " . LIST_PACAKING_LOCATIONS . "
			ORDER BY locations.loccode";

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$i = 1;
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shop Packaging Stock Status by Shop' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th></th>
								<th></th>
								<th></th>
								<th colspan="3">' . _('KL Box L') . '</th>
								<th colspan="3">' . _('KL Box M') . '</th>
								<th colspan="3">' . _('KL Box S') . '</th>
								<th colspan="3">' . _('KL PouchBag L') . '</th>
								<th colspan="3">' . _('KL PouchBag M') . '</th>
								<th colspan="3">' . _('KL PouchBag S') . '</th>
								<th colspan="3">' . _('KL ShoppingBag L') . '</th>
								<th colspan="3">' . _('KL ShoppingBag M') . '</th>
								<th colspan="3">' . _('KL ShoppingBag S') . '</th>
							</tr>
							<tr>
								<th class="SortedColumn">' . _('KL Shop') . '</th>
								<th class="SortedColumn">' . _('Days RL') . '</th>
								<th class="SortedColumn">' . _('Factor') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}

			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['locationname'], 
					$MyRow['rldaysforpackaging'], 
					$MyRow['rlfactorforpackaging'], 
					locale_number_format_zero_blank($MyRow['qty_box_l'],0), 
					locale_number_format_zero_blank($MyRow['ot_box_l'],0),
					locale_number_format_zero_blank($MyRow['rl_box_l'],0),
					locale_number_format_zero_blank($MyRow['qty_box_m'],0), 
					locale_number_format_zero_blank($MyRow['ot_box_m'],0),
					locale_number_format_zero_blank($MyRow['rl_box_m'],0),
					locale_number_format_zero_blank($MyRow['qty_box_s'],0), 
					locale_number_format_zero_blank($MyRow['ot_box_s'],0),
					locale_number_format_zero_blank($MyRow['rl_box_s'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_l'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_l'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_l'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_m'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_m'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_m'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_s'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_s'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_s'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($MyRow['ot_shopping_l'],0),
					locale_number_format_zero_blank($MyRow['rl_shopping_l'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($MyRow['ot_shopping_m'],0),
					locale_number_format_zero_blank($MyRow['rl_shopping_m'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($MyRow['ot_shopping_s'],0),
					locale_number_format_zero_blank($MyRow['rl_shopping_s'],0)
					);

			$i++;
		}
		if (!$ShowHeader){
			echo '</tbody></table>
				</div>';
		}
	}
}

function PackagingStatusForOutlet($RootPath){

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.rldaysforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB02-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB02-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB02-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS rl_shopping_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKSB03') AS ot_shopping_m
			FROM locations
			WHERE locations.typeloc = 'SHOPOU'
				OR locations.loccode IN " . LIST_PACAKING_LOCATIONS . "
			ORDER BY locations.loccode";

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$i = 1;
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'OUTLET Shop Packaging Stock Status by Shop' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th></th>
								<th></th>
								<th></th>
								<th colspan="3">' . _('OUTLET PouchBag L') . '</th>
								<th colspan="3">' . _('OUTLET PouchBag M') . '</th>
								<th colspan="3">' . _('OUTLET PouchBag S') . '</th>
								<th colspan="3">' . _('OUTLET ShoppingBag') . '</th>
							</tr>
							<tr>
								<th class="SortedColumn">' . _('KL Shop') . '</th>
								<th class="SortedColumn">' . _('Days RL') . '</th>
								<th class="SortedColumn">' . _('Factor') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Transit') . '</th>
								<th class="SortedColumn">' . _('RL') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}

			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['locationname'], 
					$MyRow['rldaysforpackaging'], 
					$MyRow['rlfactorforpackaging'], 
					locale_number_format_zero_blank($MyRow['qty_bag_l'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_l'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_l'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_m'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_m'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_m'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_s'],0), 
					locale_number_format_zero_blank($MyRow['ot_bag_s'],0),
					locale_number_format_zero_blank($MyRow['rl_bag_s'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($MyRow['ot_shopping_m'],0),
					locale_number_format_zero_blank($MyRow['rl_shopping_m'],0)
					);

			$i++;
		}
		if (!$ShowHeader){
			echo '</tbody></table>
				</div>';
		}
	}
}

function PackagingUsageForBlink($NumDays, $RootPath){
/* EXPLAIN 2014-05-20	 OK! */

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$NumDays));

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-L') AS qty_box_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX02-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-M') AS qty_box_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX02-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX02-S') AS qty_box_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX02-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-L') AS qty_bag_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB03-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-M') AS qty_bag_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB03-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-S') AS qty_bag_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB03-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-L') AS qty_shopping_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB04-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-M') AS qty_shopping_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB04-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-S') AS qty_shopping_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB04-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_s
			FROM locations
			WHERE locations.typeloc = 'SHOPBL'
				OR locations.loccode IN " . LIST_PACAKING_LOCATIONS . "
			ORDER BY locations.loccode";

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$i = 1;
	
	$totalqty_box_l   = 0;
	$totalsales_box_l = 0;
	$totalqty_box_m   = 0;
	$totalsales_box_m = 0;
	$totalqty_box_s   = 0;
	$totalsales_box_s = 0;

	$totalqty_bag_l   = 0;
	$totalsales_bag_l = 0;
	$totalqty_bag_m   = 0;
	$totalsales_bag_m = 0;
	$totalqty_bag_s   = 0;
	$totalsales_bag_s = 0;
	
	$totalqty_shopping_l   = 0;
	$totalsales_shopping_l = 0;
	$totalqty_shopping_m    = 0;
	$totalsales_shopping_m  = 0;
	$totalqty_shopping_s    = 0;
	$totalsales_shopping_s  = 0;

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'BLINK Shop Packaging Usage during the last ' . $NumDays . ' days'. '</strong></p>';
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th></th>
								<th colspan="3">' . _('BLINK Box L') . '</th>
								<th colspan="3">' . _('BLINK Box M') . '</th>
								<th colspan="3">' . _('BLINK Box S') . '</th>
								<th colspan="3">' . _('BLINK PouchBag L') . '</th>
								<th colspan="3">' . _('BLINK PouchBag M') . '</th>
								<th colspan="3">' . _('BLINK PouchBag S') . '</th>
								<th colspan="3">' . _('BLINK ShoppingBag L') . '</th>
								<th colspan="3">' . _('BLINK ShoppingBag M') . '</th>
								<th colspan="3">' . _('BLINK ShoppingBag S') . '</th>
							</tr>
							<tr>
								<th class="SortedColumn">' . _('Shop') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}

			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['locationname'], 
					locale_number_format_zero_blank($MyRow['qty_box_l'],0), 
					locale_number_format_zero_blank($MyRow['sales_box_l'],0),
					locale_number_format_zero_blank($MyRow['qty_box_l']/($MyRow['sales_box_l']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_box_m'],0), 
					locale_number_format_zero_blank($MyRow['sales_box_m'],0),
					locale_number_format_zero_blank($MyRow['qty_box_m']/($MyRow['sales_box_m']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_box_s'],0), 
					locale_number_format_zero_blank($MyRow['sales_box_s'],0),
					locale_number_format_zero_blank($MyRow['qty_box_s']/($MyRow['sales_box_s']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_bag_l'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_l'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_l']/($MyRow['sales_bag_l']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_bag_m'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_m'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_m']/($MyRow['sales_bag_m']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_bag_s'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_s'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_s']/($MyRow['sales_bag_s']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($MyRow['sales_shopping_l'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_l']/($MyRow['sales_shopping_l']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($MyRow['sales_shopping_m'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m']/($MyRow['sales_shopping_m']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($MyRow['sales_shopping_s'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_s']/($MyRow['sales_shopping_s']/$NumDays),0)
					);
			$totalqty_box_l   = $totalqty_box_l + $MyRow['qty_box_l'];
			$totalsales_box_l = $totalsales_box_l + $MyRow['sales_box_l'];
			$totalqty_box_m   = $totalqty_box_m + $MyRow['qty_box_m'];
			$totalsales_box_m = $totalsales_box_m + $MyRow['sales_box_m'];
			$totalqty_box_s   = $totalqty_box_s + $MyRow['qty_box_s'];
			$totalsales_box_s = $totalsales_box_s + $MyRow['sales_box_s'];

			$totalqty_bag_l   = $totalqty_bag_l + $MyRow['qty_bag_l'];
			$totalsales_bag_l = $totalsales_bag_l + $MyRow['sales_bag_l'];
			$totalqty_bag_m   = $totalqty_bag_m + $MyRow['qty_bag_m'];
			$totalsales_bag_m = $totalsales_bag_m + $MyRow['sales_bag_m'];
			$totalqty_bag_s   = $totalqty_bag_s + $MyRow['qty_bag_s'];
			$totalsales_bag_s = $totalsales_bag_s + $MyRow['sales_bag_s'];

			$totalqty_shopping_l    = $totalqty_shopping_l + $MyRow['qty_shopping_l'];
			$totalsales_shopping_l  = $totalsales_shopping_l + $MyRow['sales_shopping_l'];
			$totalqty_shopping_m    = $totalqty_shopping_m + $MyRow['qty_shopping_m'];
			$totalsales_shopping_m  = $totalsales_shopping_m + $MyRow['sales_shopping_m'];
			$totalqty_shopping_s    = $totalqty_shopping_s + $MyRow['qty_shopping_s'];
			$totalsales_shopping_s  = $totalsales_shopping_s + $MyRow['sales_shopping_s'];

			$i++;
		}
		if (!$ShowHeader){
			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'TOTAL', 
					locale_number_format_zero_blank($totalqty_box_l,0), 
					locale_number_format_zero_blank($totalsales_box_l,0),
					locale_number_format_zero_blank($totalqty_box_l/($totalsales_box_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_box_m,0), 
					locale_number_format_zero_blank($totalsales_box_m,0),
					locale_number_format_zero_blank($totalqty_box_m/($totalsales_box_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_box_s,0), 
					locale_number_format_zero_blank($totalsales_box_s,0),
					locale_number_format_zero_blank($totalqty_box_s/($totalsales_box_s/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_l,0), 
					locale_number_format_zero_blank($totalsales_bag_l,0),
					locale_number_format_zero_blank($totalqty_bag_l/($totalsales_bag_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_m,0), 
					locale_number_format_zero_blank($totalsales_bag_m,0),
					locale_number_format_zero_blank($totalqty_bag_m/($totalsales_bag_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_s,0), 
					locale_number_format_zero_blank($totalsales_bag_s,0),
					locale_number_format_zero_blank($totalqty_bag_s/($totalsales_bag_s/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_l,0), 
					locale_number_format_zero_blank($totalsales_shopping_l,0),
					locale_number_format_zero_blank($totalqty_shopping_l/($totalsales_shopping_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_m,0), 
					locale_number_format_zero_blank($totalsales_shopping_m,0),
					locale_number_format_zero_blank($totalqty_shopping_m/($totalsales_shopping_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_s,0), 
					locale_number_format_zero_blank($totalsales_shopping_s,0),
					locale_number_format_zero_blank($totalqty_shopping_s/($totalsales_shopping_s/$NumDays),0)
					);
			echo '</tbody></table>
				</div>';
		}
	}
}

function PackagingUsageForKapalLaut($NumDays, $RootPath){
/* EXPLAIN 2014-05-20	 OK! */

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$NumDays));

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS qty_box_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX01-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS qty_box_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX01-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS qty_box_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX01-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS qty_bag_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB01-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS qty_bag_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB01-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS qty_bag_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB01-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS qty_shopping_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB02-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS qty_shopping_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB02-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS qty_shopping_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB02-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_s
			FROM locations
			WHERE locations.typeloc = 'SHOPKL'
				OR locations.loccode IN " . LIST_PACAKING_LOCATIONS . "
			ORDER BY locations.loccode";

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$i = 1;
	
	$totalqty_box_l   = 0;
	$totalsales_box_l = 0;
	$totalqty_box_m   = 0;
	$totalsales_box_m = 0;
	$totalqty_box_s   = 0;
	$totalsales_box_s = 0;

	$totalqty_bag_l   = 0;
	$totalsales_bag_l = 0;
	$totalqty_bag_m   = 0;
	$totalsales_bag_m = 0;
	$totalqty_bag_s   = 0;
	$totalsales_bag_s = 0;
	
	$totalqty_shopping_l    = 0;
	$totalsales_shopping_l  = 0;
	$totalqty_shopping_m    = 0;
	$totalsales_shopping_m  = 0;
	$totalqty_shopping_s    = 0;
	$totalsales_shopping_s  = 0;

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shop Packaging Usage during the last ' . $NumDays . ' days'. '</strong></p>';
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th></th>
								<th colspan="3">' . _('Box L') . '</th>
								<th colspan="3">' . _('Box M') . '</th>
								<th colspan="3">' . _('Box S') . '</th>
								<th colspan="3">' . _('PouchBag L') . '</th>
								<th colspan="3">' . _('PouchBag M') . '</th>
								<th colspan="3">' . _('PouchBag S') . '</th>
								<th colspan="3">' . _('ShoppingBag L') . '</th>
								<th colspan="3">' . _('ShoppingBag M') . '</th>
								<th colspan="3">' . _('ShoppingBag S') . '</th>
							</tr>
							<tr>
								<th class="SortedColumn">' . _('KL Shop') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}

			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['locationname'], 
					locale_number_format_zero_blank($MyRow['qty_box_l'],0), 
					locale_number_format_zero_blank($MyRow['sales_box_l'],0),
					locale_number_format_zero_blank($MyRow['qty_box_l']/($MyRow['sales_box_l']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_box_m'],0), 
					locale_number_format_zero_blank($MyRow['sales_box_m'],0),
					locale_number_format_zero_blank($MyRow['qty_box_m']/($MyRow['sales_box_m']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_box_s'],0), 
					locale_number_format_zero_blank($MyRow['sales_box_s'],0),
					locale_number_format_zero_blank($MyRow['qty_box_s']/($MyRow['sales_box_s']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_bag_l'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_l'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_l']/($MyRow['sales_bag_l']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_bag_m'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_m'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_m']/($MyRow['sales_bag_m']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_bag_s'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_s'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_s']/($MyRow['sales_bag_s']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($MyRow['sales_shopping_l'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_l']/($MyRow['sales_shopping_l']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($MyRow['sales_shopping_m'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m']/($MyRow['sales_shopping_m']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($MyRow['sales_shopping_s'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_s']/($MyRow['sales_shopping_s']/$NumDays),0)
					);
			$totalqty_box_l   = $totalqty_box_l + $MyRow['qty_box_l'];
			$totalsales_box_l = $totalsales_box_l + $MyRow['sales_box_l'];
			$totalqty_box_m   = $totalqty_box_m + $MyRow['qty_box_m'];
			$totalsales_box_m = $totalsales_box_m + $MyRow['sales_box_m'];
			$totalqty_box_s   = $totalqty_box_s + $MyRow['qty_box_s'];
			$totalsales_box_s = $totalsales_box_s + $MyRow['sales_box_s'];

			$totalqty_bag_l   = $totalqty_bag_l + $MyRow['qty_bag_l'];
			$totalsales_bag_l = $totalsales_bag_l + $MyRow['sales_bag_l'];
			$totalqty_bag_m   = $totalqty_bag_m + $MyRow['qty_bag_m'];
			$totalsales_bag_m = $totalsales_bag_m + $MyRow['sales_bag_m'];
			$totalqty_bag_s   = $totalqty_bag_s + $MyRow['qty_bag_s'];
			$totalsales_bag_s = $totalsales_bag_s + $MyRow['sales_bag_s'];

			$totalqty_shopping_l    = $totalqty_shopping_l + $MyRow['qty_shopping_l'];
			$totalsales_shopping_l  = $totalsales_shopping_l + $MyRow['sales_shopping_l'];
			$totalqty_shopping_m    = $totalqty_shopping_m + $MyRow['qty_shopping_m'];
			$totalsales_shopping_m  = $totalsales_shopping_m + $MyRow['sales_shopping_m'];
			$totalqty_shopping_s    = $totalqty_shopping_s + $MyRow['qty_shopping_s'];
			$totalsales_shopping_s  = $totalsales_shopping_s + $MyRow['sales_shopping_s'];

			$i++;
		}
		if (!$ShowHeader){
			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'TOTAL', 
					locale_number_format_zero_blank($totalqty_box_l,0), 
					locale_number_format_zero_blank($totalsales_box_l,0),
					locale_number_format_zero_blank($totalqty_box_l/($totalsales_box_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_box_m,0), 
					locale_number_format_zero_blank($totalsales_box_m,0),
					locale_number_format_zero_blank($totalqty_box_m/($totalsales_box_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_box_s,0), 
					locale_number_format_zero_blank($totalsales_box_s,0),
					locale_number_format_zero_blank($totalqty_box_s/($totalsales_box_s/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_l,0), 
					locale_number_format_zero_blank($totalsales_bag_l,0),
					locale_number_format_zero_blank($totalqty_bag_l/($totalsales_bag_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_m,0), 
					locale_number_format_zero_blank($totalsales_bag_m,0),
					locale_number_format_zero_blank($totalqty_bag_m/($totalsales_bag_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_s,0), 
					locale_number_format_zero_blank($totalsales_bag_s,0),
					locale_number_format_zero_blank($totalqty_bag_s/($totalsales_bag_s/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_l,0), 
					locale_number_format_zero_blank($totalsales_shopping_l,0),
					locale_number_format_zero_blank($totalqty_shopping_l/($totalsales_shopping_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_m,0), 
					locale_number_format_zero_blank($totalsales_shopping_m,0),
					locale_number_format_zero_blank($totalqty_shopping_m/($totalsales_shopping_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_s,0), 
					locale_number_format_zero_blank($totalsales_shopping_s,0),
					locale_number_format_zero_blank($totalqty_shopping_s/($totalsales_shopping_s/$NumDays),0)
					);
			echo '</tbody></table>
				</div>';
		}
	}
}

function PackagingUsageByWeeks($RootPath){

	$StartWeek1 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -1));
	$EndWeek1 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -7));
	$StartWeek2 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -8));
	$EndWeek2 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -14));
	$StartWeek3 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -15));
	$EndWeek3 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -21));
	$StartWeek4 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -22));
	$EndWeek4 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -28));
	$StartWeek5 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -29));
	$EndWeek5 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -35));
	$StartWeek6 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -36));
	$EndWeek6 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -42));
	$StartWeek7 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -43));
	$EndWeek7 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -49));
	$StartWeek8 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -50));
	$EndWeek8 = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -56));

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.stockid = stockmaster.stockid
							AND packagingused.date <= '". $StartWeek1 ."'
							AND packagingused.date >= '". $EndWeek1 ."') AS useweek1,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.stockid = stockmaster.stockid
							AND packagingused.date <= '". $StartWeek2 ."'
							AND packagingused.date >= '". $EndWeek2 ."') AS useweek2,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.stockid = stockmaster.stockid
							AND packagingused.date <= '". $StartWeek3 ."'
							AND packagingused.date >= '". $EndWeek3 ."') AS useweek3,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.stockid = stockmaster.stockid
							AND packagingused.date <= '". $StartWeek4 ."'
							AND packagingused.date >= '". $EndWeek4 ."') AS useweek4,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.stockid = stockmaster.stockid
							AND packagingused.date <= '". $StartWeek5 ."'
							AND packagingused.date >= '". $EndWeek5 ."') AS useweek5,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.stockid = stockmaster.stockid
							AND packagingused.date <= '". $StartWeek6 ."'
							AND packagingused.date >= '". $EndWeek6 ."') AS useweek6,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.stockid = stockmaster.stockid
							AND packagingused.date <= '". $StartWeek7 ."'
							AND packagingused.date >= '". $EndWeek7 ."') AS useweek7,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.stockid = stockmaster.stockid
							AND packagingused.date <= '". $StartWeek8 ."'
							AND packagingused.date >= '". $EndWeek8 ."') AS useweek8
			FROM stockmaster
			WHERE stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				AND stockmaster.discontinued = 0
			ORDER BY stockmaster.stockid";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . 'Shop Packaging Usage by week'. '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . ConvertSQLDate($StartWeek1) . '</th>
						<th class="SortedColumn">' . ConvertSQLDate($StartWeek2) . '</th>
						<th class="SortedColumn">' . ConvertSQLDate($StartWeek3) . '</th>
						<th class="SortedColumn">' . ConvertSQLDate($StartWeek4) . '</th>
						<th class="SortedColumn">' . ConvertSQLDate($StartWeek5) . '</th>
						<th class="SortedColumn">' . ConvertSQLDate($StartWeek6) . '</th>
						<th class="SortedColumn">' . ConvertSQLDate($StartWeek7) . '</th>
						<th class="SortedColumn">' . ConvertSQLDate($StartWeek8) . '</th>
						<th class="SortedColumn">' . _('Average') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			$Average = ($MyRow['useweek1'] + 
					$MyRow['useweek2'] + 
					$MyRow['useweek3'] + 
					$MyRow['useweek4'] + 
					$MyRow['useweek5'] + 
					$MyRow['useweek6'] + 
					$MyRow['useweek7'] + 
					$MyRow['useweek8']) / 8;
					
			printf('<tr class="striped_row">
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
					<td class="number">%s</td>
					</tr>', 
					$MyRow['stockid'], 
					$MyRow['description'], 
					locale_number_format_zero_blank($MyRow['useweek1'],0), 
					locale_number_format_zero_blank($MyRow['useweek2'],0), 
					locale_number_format_zero_blank($MyRow['useweek3'],0), 
					locale_number_format_zero_blank($MyRow['useweek4'],0), 
					locale_number_format_zero_blank($MyRow['useweek5'],0), 
					locale_number_format_zero_blank($MyRow['useweek6'],0), 
					locale_number_format_zero_blank($MyRow['useweek7'],0), 
					locale_number_format_zero_blank($MyRow['useweek8'],0), 
					locale_number_format_zero_blank($Average,0) 
					);
		}
		echo '</tbody></table>
			</div>';
	}
}

function PackagingUsageForOutlet($NumDays, $RootPath){
/* EXPLAIN 2014-05-20	 OK! */

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$NumDays));

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS qty_bag_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB02-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS qty_bag_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB02-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS qty_bag_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB02-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS qty_shopping_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB03'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_m
			FROM locations
			WHERE locations.typeloc = 'SHOPOU'
				OR locations.loccode IN " . LIST_PACAKING_LOCATIONS . "
			ORDER BY locations.loccode";

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$i = 1;
	
	$totalqty_bag_l   = 0;
	$totalsales_bag_l = 0;
	$totalqty_bag_m   = 0;
	$totalsales_bag_m = 0;
	$totalqty_bag_s   = 0;
	$totalsales_bag_s = 0;
	
	$totalqty_shopping_m    = 0;
	$totalsales_shopping_m  = 0;

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'OUTLET Shop Packaging Usage during the last ' . $NumDays . ' days'. '</strong></p>';
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th></th>
								<th colspan="3">' . _('OUTLET PouchBag L') . '</th>
								<th colspan="3">' . _('OUTLET PouchBag M') . '</th>
								<th colspan="3">' . _('OUTLET PouchBag S') . '</th>
								<th colspan="3">' . _('OUTLET ShoppingBag M') . '</th>
							</tr>
							<tr>
								<th class="SortedColumn">' . _('KL Shop') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Use ') . $NumDays . ' d</th>
								<th class="SortedColumn">' . _('Days Stock') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}

			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['locationname'], 
					locale_number_format_zero_blank($MyRow['qty_bag_l'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_l'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_l']/($MyRow['sales_bag_l']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_bag_m'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_m'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_m']/($MyRow['sales_bag_m']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_bag_s'],0), 
					locale_number_format_zero_blank($MyRow['sales_bag_s'],0),
					locale_number_format_zero_blank($MyRow['qty_bag_s']/($MyRow['sales_bag_s']/$NumDays),0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($MyRow['sales_shopping_m'],0),
					locale_number_format_zero_blank($MyRow['qty_shopping_m']/($MyRow['sales_shopping_m']/$NumDays),0)
					);

			$totalqty_bag_l   = $totalqty_bag_l + $MyRow['qty_bag_l'];
			$totalsales_bag_l = $totalsales_bag_l + $MyRow['sales_bag_l'];
			$totalqty_bag_m   = $totalqty_bag_m + $MyRow['qty_bag_m'];
			$totalsales_bag_m = $totalsales_bag_m + $MyRow['sales_bag_m'];
			$totalqty_bag_s   = $totalqty_bag_s + $MyRow['qty_bag_s'];
			$totalsales_bag_s = $totalsales_bag_s + $MyRow['sales_bag_s'];

			$totalqty_shopping_m    = $totalqty_shopping_m + $MyRow['qty_shopping_m'];
			$totalsales_shopping_m  = $totalsales_shopping_m + $MyRow['sales_shopping_m'];

			$i++;
		}
		if (!$ShowHeader){
			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'TOTAL', 
					locale_number_format_zero_blank($totalqty_bag_l,0), 
					locale_number_format_zero_blank($totalsales_bag_l,0),
					locale_number_format_zero_blank($totalqty_bag_l/($totalsales_bag_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_m,0), 
					locale_number_format_zero_blank($totalsales_bag_m,0),
					locale_number_format_zero_blank($totalqty_bag_m/($totalsales_bag_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_s,0), 
					locale_number_format_zero_blank($totalsales_bag_s,0),
					locale_number_format_zero_blank($totalqty_bag_s/($totalsales_bag_s/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_m,0), 
					locale_number_format_zero_blank($totalsales_shopping_m,0),
					locale_number_format_zero_blank($totalqty_shopping_m/($totalsales_shopping_m/$NumDays),0)
					);
			echo '</tbody></table>
				</div>';
		}
	}
}

function PettyCashStatus($currency){

	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pctabs.currency = '". $currency ."'
				AND pcashdetails.authorized != '0000-00-00'
			GROUP BY pcashdetails.tabcode
			HAVING ( SUM(pcashdetails.amount) <= -0.01
					OR SUM(pcashdetails.amount) >= 0.01)";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Authorized Status for '). $currency . ' accounts'  . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('PC Tab Code') . '</th>
						<th class="SortedColumn">' . _('Amount') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		$total = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$MyRow['tabcode'], 
					locale_number_format($MyRow['amount'],0)
					);
			$i++;
			$total = $total + $MyRow['amount'];
		}
		printf('<tr class="striped_row">
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'Total', 
				locale_number_format($total,0)
				);
		
		echo '</tbody></table>
				</div>';
	}
}

function PeriodDifferenceSales($typeperiod, $typereport, $NumDaysA){
	
	if ($NumDaysA == "YTD"){
		// we need to translate YTD to a number of days
		// As suggested by OpenAI ChatGPT ;-)
		// Get the current timestamp
		$current_timestamp = time();
		// Extract the year of yesterday
		$current_year = date('Y', strtotime("-1 days"));
		// Create a timestamp for the first day of the year
		$first_day_timestamp = mktime(0, 0, 0, 1, 1, $current_year);
		// Calculate the number of seconds between the two timestamps
		$seconds_diff = $current_timestamp - $first_day_timestamp;
		// Calculate the number of days between the first day of the year and the current day
		$NumDaysA = floor($seconds_diff / 86400);		

		$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDateA = $current_year . '-01-01';
		$YesterdayB = $typeperiod . substr($YesterdayA, 4, 6);
		$StartDateB = $typeperiod . '-01-01';
		$Title = _('Difference sales for ') . $typereport . " YTD (Year To Date) and same period in " . $typeperiod;
		$TitleCurrent = $NumDaysA . ' Days This Year';
		$TitlePrevious = $NumDaysA . ' Days '. $typeperiod;
	}
	else{
		$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA));
		if ($typeperiod == "YEAR"){
			$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-365));
			$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-365));
			$Title = _('Difference sales for ') . $typereport . " during the last " . $NumDaysA . " days and same period last year";
			$TitleCurrent = $NumDaysA . ' Days This Year';
			$TitlePrevious = $NumDaysA . ' Days Last Year';
		}elseif ($typeperiod == "IMMEDIATE"){
			$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-$NumDaysA));
			$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-$NumDaysA));
			$Title = _('Difference sales for ') . $typereport .  " during the last " . $NumDaysA . " days and previous immediate same period";
			$TitleCurrent = $NumDaysA . ' Last Days';
			$TitlePrevious = $NumDaysA . ' Previous Days';
		}else{
			// comparing with a fixed year
			$YesterdayB  = $typeperiod . substr($YesterdayA, 4, 6);
			$StartDateB = FormatDateForSQL(DateAdd(ConvertSQLDate($YesterdayB),'d',-$NumDaysA));
			$Title = _('Difference sales for ') . $typereport . " during the last " . $NumDaysA . " days and same period in " . $typeperiod;
			$TitleCurrent = $NumDaysA . ' Days This Year';
			$TitlePrevious = $NumDaysA . ' Days '. $typeperiod;
		}
	}

	$TotalDateA = 0;
	$TotalDateB = 0;
	$TotalRent = 0;
	$TotalBothYearsDateA = 0;
	$TotalBothYearsDateB = 0;
	$TotalBothYearsRent = 0;
	$TotalNewDateA = 0;
	$TotalOldDateB = 0;
	$TotalNewRent = 0;
	$TotalOldRent = 0;

	if (($typereport == "Shop") OR ($typereport == "Online")){
		$SQL = "SELECT debtorno,
					name, ";
		if ($typereport == "Shop"){
			$SQL = $SQL . "(SELECT locations.klyearlyrent 
						FROM locations
						WHERE locations.cashsalecustomer = debtorsmaster.debtorno
						LIMIT 1) AS yearlyrent, ";
		}else{
			$SQL = $SQL . "0 AS yearlyrent, ";
		}
		$SQL = $SQL . "(SELECT SUM(linenetprice)/currencies.rate
						FROM salesorderdetails, salesorders, currencies
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND debtorsmaster.currcode = currencies.currabrev
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesA,
					(SELECT SUM(linenetprice)/currencies.rate
						FROM salesorderdetails, salesorders, currencies
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND debtorsmaster.currcode = currencies.currabrev
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateB . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesB
				FROM debtorsmaster ";
		if ($typereport == "Shop"){
			// retail shops or old retail shops
			$SQL = $SQL .  "WHERE (debtorsmaster.typeid = 2 OR debtorsmaster.typeid = 11)  
							ORDER BY (SELECT SUM(linenetprice)
										FROM salesorderdetails, salesorders
										WHERE salesorderdetails.orderno = salesorders.orderno
											AND salesorderdetails.completed = 1
											AND salesorders.orddate >= '". $StartDateA . "'
											AND salesorders.orddate <= '". $YesterdayA . "'
											AND salesorders.debtorno = debtorsmaster.debtorno) DESC";
		}else{
			// online not being wholesale
			$SQL = $SQL . "WHERE (debtorsmaster.typeid = 9 OR debtorsmaster.typeid = 10)
								AND debtorsmaster.debtorno != 'WEB-WH-IDR'
								AND debtorsmaster.debtorno != 'WEB-WH-USD'
								AND debtorsmaster.debtorno != 'WEB-WH-EUR'
								AND debtorsmaster.debtorno != 'WEB-WH-AUD' 
							ORDER BY debtorsmaster.debtorno";
		}
	}else{
		$SQL = "SELECT salesmancode,
					salesmanname,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesA,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateB . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesB
				FROM salesman
				WHERE salesman.current = 1
				ORDER BY (SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) DESC";
	}
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Title  .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . $typereport . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . $TitleCurrent . '</th>
						<th class="SortedColumn">' . $TitlePrevious . '</th>
						<th class="SortedColumn">' . _('Trend') . '</th>
						<th class="SortedColumn">' . _('%Rent/Sales') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {

			if (($typereport == "Shop") OR ($typereport == "Online")){
				$Code = $MyRow['debtorno'];
				$Name = $MyRow['name'];
				if (($MyRow['salesA'] > 0) AND ($MyRow['yearlyrent'] > 0)){
					$Rent = round(($MyRow['yearlyrent'] / 365 * $NumDaysA) / $MyRow['salesA'] * 100) . '%';
				}else{
					$Rent = "";
				}
			}else{
				$Code = $MyRow['salesmancode'];
				$Name = $MyRow['salesmanname'];
				$Rent = "";
			}
			
			if ($MyRow['salesB'] != 0){
				$Percent = (($MyRow['salesA'])-($MyRow['salesB']))/($MyRow['salesB']) * 100;
			}else{
				$Percent = 0;
			}
			$Trend = " ";
			if ($Percent > MINIMUM_AVERAGE_SALES_COMPARED_LAST_YEAR_TREND){
				$Trend = "Improving ". locale_number_format($Percent,0) . "%";
			}
			if ($Percent < -MINIMUM_AVERAGE_SALES_COMPARED_LAST_YEAR_TREND){
				$Trend = "Degrading ". locale_number_format($Percent,0) . "%";
			}
			if (($MyRow['salesA'] > 0) OR ($MyRow['salesB'] > 0)){
				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i,
						$Code,
						$Name,
						locale_number_format($MyRow['salesA'],0), 
						locale_number_format($MyRow['salesB'],0), 
						$Trend,
						$Rent
						);
				$i++;
			}

			if (($MyRow['salesA'] > 0) AND ($MyRow['salesB'] > 0)){
				$TotalBothYearsDateA = $TotalBothYearsDateA +($MyRow['salesA']);
				$TotalBothYearsDateB = $TotalBothYearsDateB +($MyRow['salesB']);
				$TotalBothYearsRent = $TotalBothYearsRent +($MyRow['yearlyrent']);
			}
			if (($MyRow['salesA'] > 0) AND ($MyRow['salesB'] == 0)){
				$TotalNewDateA = $TotalNewDateA +($MyRow['salesA']);
				$TotalNewRent = $TotalNewRent +($MyRow['yearlyrent']);
			}
			if (($MyRow['salesA'] == 0) AND ($MyRow['salesB'] > 0)){
				$TotalOldDateB = $TotalOldDateB +($MyRow['salesB']);
				$TotalOldRent = $TotalOldRent +($MyRow['yearlyrent']);
			}
			$TotalDateA = $TotalDateA +($MyRow['salesA']);
			$TotalRent = $TotalRent +($MyRow['yearlyrent']);
			$TotalDateB = $TotalDateB +($MyRow['salesB']);
		}
		if ($typereport == "Shop"){
			$Percent = (($TotalBothYearsDateA)-($TotalBothYearsDateB))/($TotalBothYearsDateB) * 100;
			$Trend = " ";
			if ($Percent > 0){
				$Trend = "Improving ". locale_number_format($Percent,1) . "%";
			}
			if ($Percent < 0){
				$Trend = "Degrading ". locale_number_format($Percent,1) . "%";
			}
			$Rent = round(($TotalBothYearsRent / 365 * $NumDaysA) / $TotalBothYearsDateA * 100) . '%';
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					"",
					"",
					"EXISTING SHOPS",
					locale_number_format($TotalBothYearsDateA,0), 
					locale_number_format($TotalBothYearsDateB,0), 
					$Trend,
					$Rent
					);
			if ($TotalNewDateA > 0){
				$Rent = round(($TotalNewRent / 365 * $NumDaysA) / $TotalNewDateA * 100) . '%';
				printf('<tr class="striped_row">
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						"",
						"",
						"NEW SHOPS",
						locale_number_format($TotalNewDateA,0), 
						"", 
						"",
						$Rent
						);
			}
			if ($TotalOldDateB > 0){
				$Rent = round(($TotalOldRent / 365 * $NumDaysA) / $TotalOldDateB * 100) . '%';
				printf('<tr class="striped_row">
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						"",
						"",
						"CLOSED SHOPS",
						"", 
						locale_number_format($TotalOldDateB,0), 
						"",
						$Rent
						);
			}
		}
		if (($typereport == "Shop") OR ($typereport == "Online")){
			$Percent = (($TotalDateA)-($TotalDateB))/($TotalDateB) * 100;
			$Trend = " ";
			if ($Percent > 0){
				$Trend = "Improving ". locale_number_format($Percent,1) . "%";
			}
			if ($Percent < 0){
				$Trend = "Degrading ". locale_number_format($Percent,1) . "%";
			}
			$Rent = round(($TotalRent / 365 * $NumDaysA) / $TotalDateA * 100) . '%';
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					"",
					"",
					"TOTAL",
					locale_number_format($TotalDateA,0), 
					locale_number_format($TotalDateB,0), 
					$Trend,
					$Rent
					);
		}
		echo '</tbody></table>
				</div>';
		if (($typereport == "Shop") AND ($typeperiod == "YEAR")){
			InsertKPI("Sales", "Trend retail ".$NumDaysA." days against last year (%)", $Percent);
		}
	}
}

function UnbalancedGLTransTX($NumDays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT gltrans.trandate, 
				systypes.typename, 
				gltrans.type, 
				gltrans.typeno, 
				SUM(gltrans.amount) AS unbalance
			FROM gltrans, systypes
			WHERE gltrans.type = systypes.typeid 
				AND gltrans.trandate >= '" . $StartDate . "'
			GROUP BY gltrans.type, gltrans.typeno 
			HAVING ABS(SUM(gltrans.amount)) >= 1
			ORDER BY gltrans.trandate";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . 'Unbalanced GLTrans Transactions during the last ' . $NumDays . ' days' . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Date') . '</th>
						<th class="SortedColumn">' . _('Type') . '</th>
						<th class="SortedColumn">' . _('TypeNo') . '</th>
						<th class="SortedColumn">' . _('Unbalance') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {

			$CodeLink = '<a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $MyRow['type'] . '&TransNo=' . $MyRow['typeno'] . '">' . $MyRow['typeno'] . '</a>';
					
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					ConvertSQLDateTime($MyRow['trandate']), 
					$MyRow['typename'], 
					$CodeLink, 
					locale_number_format($MyRow['unbalance'],0)
					);
		}
		echo '</tbody></table>
			</div>';
	}
}

function EmptyAccountsGLTransTX($NumDays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT gltrans.counterindex,
				gltrans.trandate, 
				gltrans.type, 
				gltrans.typeno, 
				gltrans.amount
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDate . "'
				AND account = ''
			ORDER BY gltrans.counterindex";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . 'Empty account code GLTrans Transactions during the last ' . $NumDays . ' days' . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Date') . '</th>
						<th class="SortedColumn">' . _('Type') . '</th>
						<th class="SortedColumn">' . _('TypeNo') . '</th>
						<th class="SortedColumn">' . _('Amount') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {

			$CodeLink = '<a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $MyRow['type'] . '&TransNo=' . $MyRow['typeno'] . '">' . $MyRow['typeno'] . '</a>';
					
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($MyRow['counterindex'],0),
					ConvertSQLDateTime($MyRow['trandate']), 
					$MyRow['type'], 
					$CodeLink, 
					locale_number_format($MyRow['amount'],0)
					);
			$TotalAmount += $MyRow['amount'];
		}
		printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				"TOTAL",
				"", 
				"", 
				"", 
				locale_number_format($TotalAmount,0)
				);
		echo '</tbody></table>
			</div>';
	}
}


function ShowKPIHistory($NumDays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT class,
				concept,
				MIN(value) AS minimumvalue,
				AVG(value) AS averagevalue,
				MAX(value) AS maximumvalue
			FROM klkpi
			WHERE date >= '" . $StartDate . "'
			GROUP BY class, concept
			ORDER BY class, concept";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . 'General KPI last ' . $NumDays . ' days' . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Class') . '</th>
						<th class="SortedColumn">' . _('Concept') . '</th>
						<th class="SortedColumn">' . _('Minimum') . '</th>
						<th class="SortedColumn">' . _('Average') . '</th>
						<th class="SortedColumn">' . _('Maximum') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['class'], 
					$MyRow['concept'], 
					locale_number_format_kpi($MyRow['minimumvalue']),
					locale_number_format_kpi($MyRow['averagevalue']),
					locale_number_format_kpi($MyRow['maximumvalue'])
					);
		}
		echo '</tbody></table>
			</div>';
	}
}


function MaintenanceTasksDistribution($Status, $NumDays){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	if ($Status == "OPEN"){
		$WhereStatus = "WHERE klmaintenancetasks.closed = 0";
		$Title = 'Open Maintenance Tasks distribution';
	}elseif ($Status == "CLOSED"){
		$WhereStatus = "WHERE klmaintenancetasks.closed = 1
							AND closedate >= '" . $FromDate . "'";
		$Title = 'Closed Maintenance Tasks distribution during the last ' . $NumDays . ' days';
	}elseif ($Status == "TOTAL"){
		$WhereStatus = "WHERE klmaintenancetasks.closed = 0
							OR (klmaintenancetasks.closed = 1
								AND closedate >= '" . $FromDate . "')";
		$Title = 'All Maintenance Tasks distribution during the last ' . $NumDays . ' days';
	}
	$TableResult = array();
	// now populate the array with info
	$SQL = "SELECT COUNT(counterindex) AS total, 
				klmaintenancetasks.loccode,
				locations.locationname,
				klmaintenancetasks.maintenancetype
			FROM klmaintenancetasks
				INNER JOIN locations 
					ON locations.loccode=klmaintenancetasks.loccode 
				INNER JOIN klmaintenancetypes 
					ON klmaintenancetypes.maintenancetype=klmaintenancetasks.maintenancetype 
				INNER JOIN locationusers 
					ON locationusers.loccode=klmaintenancetasks.loccode 
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1 " . 
			$WhereStatus . "
			GROUP BY klmaintenancetasks.loccode, klmaintenancetasks.maintenancetype
			ORDER BY locationname, klmaintenancetasks.maintenancetype";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$TableResult[$MyRow['loccode']]['locationname'] = $MyRow['locationname'];
			$TableResult[$MyRow['loccode']][$MyRow['maintenancetype']] = $MyRow['total'];
		}
		$TableHeader = '<tr>
						<th class="SortedColumn">' . _('Location') . '</th>
						<th class="SortedColumn">' . _('AC') . '</th>
						<th class="SortedColumn">' . _('Bocor') . '</th>
						<th class="SortedColumn">' . _('Furniture') . '</th>
						<th class="SortedColumn">' . _('IT') . '</th>
						<th class="SortedColumn">' . _('Kanopi') . '</th>
						<th class="SortedColumn">' . _('Lampu') . '</th>
						<th class="SortedColumn">' . _('Listrik') . '</th>
						<th class="SortedColumn">' . _('Paint') . '</th>
						<th class="SortedColumn">' . _('Pintukaca') . '</th>
						<th class="SortedColumn">' . _('Toilet') . '</th>
						<th class="SortedColumn">' . _('Wallpaper') . '</th>
						<th class="SortedColumn">' . _('DLL') . '</th>
						<th class="SortedColumn">' . _('Total') . '</th>
					</tr>';
		echo '<p class="page_title_text" align="center"><strong>' . $Title . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>';
		echo $TableHeader;
		echo '</thead>
				<tbody>';
		$TotalIssuesAC = 0;
		$TotalIssuesBOCOR = 0;
		$TotalIssuesFURNITURE = 0;
		$TotalIssuesIT = 0;
		$TotalIssuesKANOPI = 0;
		$TotalIssuesLAMPU = 0;
		$TotalIssuesLISTRIK = 0;
		$TotalIssuesPAINT = 0;
		$TotalIssuesPINTUKACA = 0;
		$TotalIssuesTOILET = 0;
		$TotalIssuesWALLPAPER = 0;
		$TotalIssuesDLL = 0;
		$TotalIssues = 0;
		foreach ($TableResult as $row) {
			$TotalIssuesLocation = 0;
			if (isset($row['AC'])){
				$IssuesAC = $row['AC'];
				$TotalIssuesAC += $IssuesAC;
				$TotalIssuesLocation += $IssuesAC;
				$TotalIssues += $IssuesAC;
			}else{
				$IssuesAC = '';
			}
			if (isset($row['BOCOR'])){
				$IssuesBOCOR = $row['BOCOR'];
				$TotalIssuesBOCOR += $IssuesBOCOR;
				$TotalIssuesLocation += $IssuesBOCOR;
				$TotalIssues += $IssuesBOCOR;
			}else{
				$IssuesBOCOR = '';
			}
			if (isset($row['FURNITURE'])){
				$IssuesFURNITURE = $row['FURNITURE'];
				$TotalIssuesFURNITURE += $IssuesFURNITURE;
				$TotalIssuesLocation += $IssuesFURNITURE;
				$TotalIssues += $IssuesFURNITURE;
			}else{
				$IssuesFURNITURE = '';
			}
			if (isset($row['IT'])){
				$IssuesIT = $row['IT'];
				$TotalIssuesIT += $IssuesIT;
				$TotalIssuesLocation += $IssuesIT;
				$TotalIssues += $IssuesIT;
			}else{
				$IssuesIT = '';
			}
			if (isset($row['KANOPI'])){
				$IssuesKANOPI = $row['KANOPI'];
				$TotalIssuesKANOPI += $IssuesKANOPI;
				$TotalIssuesLocation += $IssuesKANOPI;
				$TotalIssues += $IssuesKANOPI;
			}else{
				$IssuesKANOPI = '';
			}
			if (isset($row['LAMPU'])){
				$IssuesLAMPU = $row['LAMPU'];
				$TotalIssuesLAMPU += $IssuesLAMPU;
				$TotalIssuesLocation += $IssuesLAMPU;
				$TotalIssues += $IssuesLAMPU;
			}else{
				$IssuesLAMPU = '';
			}
			if (isset($row['LISTRIK'])){
				$IssuesLISTRIK = $row['LISTRIK'];
				$TotalIssuesLISTRIK += $IssuesLISTRIK;
				$TotalIssuesLocation += $IssuesLISTRIK;
				$TotalIssues += $IssuesLISTRIK;
			}else{
				$IssuesLISTRIK = '';
			}
			if (isset($row['PAINT'])){
				$IssuesPAINT = $row['PAINT'];
				$TotalIssuesPAINT += $IssuesPAINT;
				$TotalIssuesLocation += $IssuesPAINT;
				$TotalIssues += $IssuesPAINT;
			}else{
				$IssuesPAINT = '';
			}
			if (isset($row['PINTUKACA'])){
				$IssuesPINTUKACA = $row['PINTUKACA'];
				$TotalIssuesPINTUKACA += $IssuesPINTUKACA;
				$TotalIssuesLocation += $IssuesPINTUKACA;
				$TotalIssues += $IssuesPINTUKACA;
			}else{
				$IssuesPINTUKACA = '';
			}
			if (isset($row['TOILET'])){
				$IssuesTOILET = $row['TOILET'];
				$TotalIssuesTOILET += $IssuesTOILET;
				$TotalIssuesLocation += $IssuesTOILET;
				$TotalIssues += $IssuesTOILET;
			}else{
				$IssuesTOILET = '';
			}
			if (isset($row['WALLPAPER'])){
				$IssuesWALLPAPER = $row['WALLPAPER'];
				$TotalIssuesWALLPAPER += $IssuesWALLPAPER;
				$TotalIssuesLocation += $IssuesWALLPAPER;
				$TotalIssues += $IssuesWALLPAPER;
			}else{
				$IssuesWALLPAPER = '';
			}
			if (isset($row['_DLL'])){
				$IssuesDLL = $row['_DLL'];
				$TotalIssuesDLL += $IssuesDLL;
				$TotalIssuesLocation += $IssuesDLL;
				$TotalIssues += $IssuesDLL;
			}else{
				$IssuesDLL = '';
			}
			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$row['locationname'], 
					$IssuesAC, 
					$IssuesBOCOR, 
					$IssuesFURNITURE, 
					$IssuesIT, 
					$IssuesKANOPI, 
					$IssuesLAMPU, 
					$IssuesLISTRIK, 
					$IssuesPAINT, 
					$IssuesPINTUKACA, 
					$IssuesTOILET, 
					$IssuesWALLPAPER, 
					$IssuesDLL, 
					$TotalIssuesLocation 
					);
		}
		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				"TOTAL", 
				$TotalIssuesAC, 
				$TotalIssuesBOCOR, 
				$TotalIssuesFURNITURE, 
				$TotalIssuesIT, 
				$TotalIssuesKANOPI, 
				$TotalIssuesLAMPU, 
				$TotalIssuesLISTRIK, 
				$TotalIssuesPAINT, 
				$TotalIssuesPINTUKACA, 
				$TotalIssuesTOILET, 
				$TotalIssuesWALLPAPER, 
				$TotalIssuesDLL, 
				$TotalIssues
				);
		
		echo '</tbody></table>
			</div>';

		if ($Status == "OPEN"){
			InsertKPI("Maintenance", "Open Maintenance Tasks", $TotalIssues);
		}elseif ($Status == "CLOSED"){
			InsertKPI("Maintenance", "Closed Maintenance Tasks during " . $NumDays . " days", $TotalIssues);
		}elseif ($Status == "TOTAL"){
			InsertKPI("Maintenance", "All Maintenance Tasks during " . $NumDays . " days", $TotalIssues);
		}
	}
}

function StockByBrand($Brand, $NumDays, $OptimalDaysStock, $ShowFullDetails){
	
	$BrandText= BrandTextFromCode($Brand);

	$Shops = NumberOfShops($Brand);
	$NumDaysLastYear = $OptimalDaysStock - $NumDays;
	
	/* Past NumDays This Year*/
	$ToLastDaysThisYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$FromLastDaysThisYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays-1));

	/* Next $NumDays future days since yesterday one year ago */
	$ToNextDaysLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-365+$NumDays));
	$FromNextDaysLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-365));

	/* Past $NumDays days since yesterday one year ago */
	$ToLastDaysLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-366));
	$FromLastDaysLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays-366));

	$TotalModels  = TotalModels($Brand);
	$TotalItems   = TotalItems($Brand);
	$DisplayItems = TotalDisplayItems($Brand);
	$AvailableForSaleItems = $TotalItems - $DisplayItems;
	$DailySoldItemsThisYearPastDays = NumItemsSoldPerBrand($Brand, $FromLastDaysThisYear, $ToLastDaysThisYear) / $NumDays;
	$DailySoldItemsLastYearPastDays = NumItemsSoldPerBrand($Brand, $FromLastDaysLastYear, $ToLastDaysLastYear) / $NumDays;
	$TrendThisYear = ($DailySoldItemsThisYearPastDays - $DailySoldItemsLastYearPastDays) / $DailySoldItemsLastYearPastDays;
	if ($Brand != "SHOPOU"){
		$DailySoldItemsLastYearNextDays = NumItemsSoldPerBrand($Brand, $FromNextDaysLastYear, $ToNextDaysLastYear) / $NumDaysLastYear;
		$ItemsToBeSoldNextDaysBasedOnTrendLastYear = $DailySoldItemsLastYearNextDays * ($TrendThisYear+1);
		$EstimationDailyItemsToBeSoldNextDays = max($DailySoldItemsThisYearPastDays, $ItemsToBeSoldNextDaysBasedOnTrendLastYear);
	}else{
		$EstimationDailyItemsToBeSoldNextDays = $DailySoldItemsThisYearPastDays;
	}
	$DaysStockForSale = $AvailableForSaleItems / $EstimationDailyItemsToBeSoldNextDays;
	$ItemsPO = TotalItemsToBeReceivedByPO($Brand);
	$ItemsWO = TotalItemsToBeReceivedByWO($Brand);
	$DaysStockForSaleIncludingPOWO = ($AvailableForSaleItems + $ItemsPO + $ItemsWO) / $EstimationDailyItemsToBeSoldNextDays;
	
	if ($DaysStockForSaleIncludingPOWO < $OptimalDaysStock){
		$ItemsToGetOptimalDaysStock = ($OptimalDaysStock - $DaysStockForSaleIncludingPOWO) * $EstimationDailyItemsToBeSoldNextDays; 
	}else{
		$ItemsToGetOptimalDaysStock = 0;
	}
	
	echo '<p class="page_title_text" align="center"><strong>' . 'Stock for Brand ' . $BrandText. '</strong></p>';
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . 'Concept' . '</th>
					<th>' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';

	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'# Shops Open', 
			locale_number_format($Shops,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			"Total Models (MODELS)", 
			locale_number_format($TotalModels,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			"Total Stock (PCS)", 
			locale_number_format($TotalItems,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			"Stock needed for display (PCS)", 
			locale_number_format($DisplayItems,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			"Stock available for sale (PCS)", 
			locale_number_format($AvailableForSaleItems,0)
			);

	if ($ShowFullDetails){
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"Daily Stock sold last " . $NumDays . " days " . 
				ConvertSQLDate($FromLastDaysThisYear) . "-" .
				ConvertSQLDate($ToLastDaysThisYear). " (PCS)", 
				locale_number_format($DailySoldItemsThisYearPastDays,0)
				);
	}

	if ($ShowFullDetails AND ($Brand != "SHOPOU")){
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"Daily Stock sold same last " . $NumDays . " days last year " . 
				ConvertSQLDate($FromLastDaysLastYear) . "-" .
				ConvertSQLDate($ToLastDaysLastYear). " (PCS)", 
				locale_number_format($DailySoldItemsLastYearPastDays,0)
				);
	}
	
	if ($Brand != "SHOPOU"){
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"Retail trend from same days last year (%)", 
				locale_number_format($TrendThisYear*100,1). "%"
				);
	}
	
	if ($ShowFullDetails AND ($Brand != "SHOPOU")){
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"Daily Stock sold next " . $NumDaysLastYear . " days last year " . 
				ConvertSQLDate($FromNextDaysLastYear) . "-" .
				ConvertSQLDate($ToNextDaysLastYear). " (PCS)", 
				locale_number_format($DailySoldItemsLastYearNextDays,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"Items to be sold next " . $NumDaysLastYear . " days based on trend (PCS)", 
				locale_number_format($ItemsToBeSoldNextDaysBasedOnTrendLastYear,0)
				);
	}

	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			"Estimation daily Stock to be sold next " . $NumDays . " days  (PCS)", 
			locale_number_format($EstimationDailyItemsToBeSoldNextDays,0)
			);
	printf('<tr>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			"Days left of stock (DAYS)", 
			locale_number_format($DaysStockForSale,0)
			);

	if ($Brand != "SHOPOU"){
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"Stock to be received by PO (PCS)", 
				locale_number_format($ItemsPO,0)
				);
		printf('<<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"Stock to be received by WO (PCS)", 
				locale_number_format($ItemsWO,0)
				);
		printf('<tr>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"Days left of stock including PO & WO (DAYS)", 
				locale_number_format($DaysStockForSaleIncludingPOWO,0)
				);
		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				"ACTION: Stock needed to reach " . $OptimalDaysStock . " days of optimal stock+PO+WO (PCS)", 
				locale_number_format($ItemsToGetOptimalDaysStock,0)
				);
	}
	echo '</tbody></table>
			</div>
			</form>';

	InsertKPI("Shops", "Shops Open " . $BrandText, $Shops);
	InsertKPI("Stock", "Total Models (MODELS) " . $BrandText, $TotalModels);
	InsertKPI("Stock", "Total Stock (PCS) " . $BrandText, $TotalItems);
	InsertKPI("Stock", "Stock needed for display (PCS) " . $BrandText, $DisplayItems);
	InsertKPI("Stock", "Stock available for sale (PCS) " . $BrandText, $AvailableForSaleItems);
	InsertKPI("Stock", "Average pieces per model (PCS) " . $BrandText, round($AvailableForSaleItems/$TotalModels,2));
	InsertKPI("Stock", "Daily Stock sold average " . $NumDays . " days (PCS) " . $BrandText, $DailySoldItemsThisYearPastDays);
	InsertKPI("Stock", "Daily Stock forecast for " . $NumDays . " days (PCS) " . $BrandText, $EstimationDailyItemsToBeSoldNextDays);
	InsertKPI("Stock", "Days left of stock (DAYS) " .$BrandText, $DaysStockForSale);
	InsertKPI("Stock", "Stock to be received PO (PCS) " . $BrandText, $ItemsPO);
	InsertKPI("Stock", "Stock to be received WO (PCS) " . $BrandText, $ItemsWO);
	InsertKPI("Stock", "Days left of stock+PO+WO(DAYS) " .$BrandText, $DaysStockForSaleIncludingPOWO);
	InsertKPI("Stock", "Stock needed for optimal (PCS) " . $BrandText, $ItemsToGetOptimalDaysStock);
	if ($Brand != "SHOPOU"){
		InsertKPI("Stock", "Trend retail ". $NumDays . " days (%) " . $BrandText, $TrendThisYear*100);
	}
}

?>
<?php
define("VERSIONFILE", "3.10");

/* Session started in session.php for password checking and authorisation level check config.php is in turn included in session.php*/

include ('includes/session.php');
$Title = _('Kapal-Laut General Performance Board '. VERSIONFILE);
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

/* ASSIGN users to groups */
include ('includes/KLRoles.inc');
$begintime = time_start();
$NumberOfTestExecuted = 0;

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

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
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = TRUE;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = TRUE;
	}elseif($_GET['Section'] == '03'){
		$ProcessSection03 = TRUE;
	}
}

/***************************************************************************************
* TEST AND PLAY AREA      
***************************************************************************************/
if ($KL_SystemAdmin){
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
		OR $KL_ShopManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){
		AverageSales("Shop", 365, 90, 30, 15, 7, 1, 30, "CurrentYear", "All", $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager
		OR $KL_ShopManager
		OR $KL_SalesDirector){
//		YearDifferenceSales("Shop",   7, $db);
//		$NumberOfTestExecuted++;
		YearDifferenceSales("Shop",  15, $db);
		$NumberOfTestExecuted++;
		YearDifferenceSales("Shop",  30, $db);
		$NumberOfTestExecuted++;
		YearDifferenceSales("Shop",	 90, $db);
		$NumberOfTestExecuted++;
		YearDifferenceSales("Shop", 365, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
//		AverageCustomerBehaviourByValueInvoice("Shop", 15, $db);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){
		AverageCustomerBehaviourByValueInvoice("Shop", 30, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_SalesDirector){
//		AverageCustomerBehaviourByValueInvoice("Shop", 90, $db);
//		$NumberOfTestExecuted++;
//		AverageCustomerBehaviourByValueInvoice("Shop", 365, $db);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_ShopManager
		OR $KL_OperationalManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){
		GeneralCustomerBehaviour(30, $db);
		$NumberOfTestExecuted++;
//		GeneralCustomerBehaviour(90, $db);
//		$NumberOfTestExecuted++;
	}
	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		DailySalesRecords(10, 365, $db);
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
		ListPriorityLocations($db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_SalesDirector
		OR $KL_ShopManager){
		ActiveTransfersByLocation($RootPath, $db);
		$NumberOfTestExecuted++;
		ActiveTransferStatus($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager){
		RecentlyClosedTransferStatus(1, $RootPath, $db);
		$NumberOfTestExecuted++;
		ErrorsInTransfers(7, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){
		FinishedStockDistribution("FORSALE", "LOCATION", $db);
		$NumberOfTestExecuted++;
		FinishedStockDistributionByShopAndCategory($db);
		$NumberOfTestExecuted++;
		FinishedStockDistribution("FORSALE", "STOCKCATEGORY", $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		GoodsToBeProduced("COMPON", "ONLYDISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ONLYDISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "ALL", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPOA", "ALL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ComponentsToObsolete(false, 0, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager){
		PurchaseOrdersProcessTime(90, $RootPath, $db);
		$NumberOfTestExecuted++;
		PurchaseOrdersWrongPlannedDates($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin OR
		$KL_OperationalManager){
		POStatusControl("IN NEGOTIAION WITH SUPPLIER", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("ON PRODUCTION", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("FINISHED BUT NOT PAID", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
//		POStatusControl("STILL NOT FULLY PAID", 0, $RootPath, $db);
//		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin OR
		$KL_OperationalManager){
		POStatusControl("BALI PAID BUT NOT RECEIVED IN KANTOR", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("BALI RECEIVED IN KANTOR BUT NOT PAID", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PAID NOT SHIPPED BY SUPPLIER", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PAID NOT RECEIVED IN AYE CARGO", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PAID NOT RECEIVED IN WANGFOONG CARGO", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("IN AYE CARGO BUT NOT SHIPPED", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("IN WANGFOONG CARGO BUT NOT SHIPPED", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("SHIPPED IN TRANSIT", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("CUSTOMS CLEARANCE", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("RECEIVED IN KANTOR", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin){
		POStatusControl("ARRIVING IN NEXT DAYS", 75, $RootPath, $db);
		$NumberOfTestExecuted++;
	}
	
//	RetailTypePayments("Shop",180, $db);
//	NumberOfTestExecuted++;
}

/***************************************************************************************
* SECTION 3
***************************************************************************************/

if ($ProcessSection03){
	if($ShowSectionInfo){
		prnMsg("Packaging, Displays, Petty Cash, Financial Performance Board Section 03.",'info');
	}

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager){
		InsuficientStockForShopPackaging('SHPACK', 15, 80, 30, true, $RootPath, $db);
		$NumberOfTestExecuted++;
//		InsuficientStockForShopPackaging('ZAPON', 15, 60, 30, true, $RootPath, $db);
//		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		PackagingStatusForKapalLaut($RootPath, $db);
		$NumberOfTestExecuted++;
		PackagingUsageForKapalLaut(30, $RootPath, $db);
		$NumberOfTestExecuted++;

		PackagingStatusForBlink($RootPath, $db);
		$NumberOfTestExecuted++;
		PackagingUsageForBlink(30, $RootPath, $db);
		$NumberOfTestExecuted++;

		PackagingStatusForOutlet($RootPath, $db);
		$NumberOfTestExecuted++;
		PackagingUsageForOutlet(30, $RootPath, $db);
		$NumberOfTestExecuted++;
		
		PackagingUsageByWeeks($RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		FinishedStockDistribution("PACKAGING", "LOCATION", $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){
		FinishedStockDistribution("DISPLAYS", "LOCATION", $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		PettyCashStatus("IDR", $db);
		$NumberOfTestExecuted++;
		PettyCashStatus("USD", $db);
		$NumberOfTestExecuted++;
		PettyCashStatus("THB", $db);
		$NumberOfTestExecuted++;
		PettyCashStatus("EUR", $db);
		$NumberOfTestExecuted++;
		PettyCashStatus("HKD", $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager
		OR $KL_AdministrationTeam){
		CashStatusPTADU("2018",         0,$db);
		$NumberOfTestExecuted++;
		CashStatusPTBB("2018", 2300000000,$db);
		$NumberOfTestExecuted++;
	}
}

prnMsg("Performed ". $NumberOfTestExecuted . " performance tests",'success');
time_finish($begintime);

include ('includes/footer.php');

/******************************************************************************************************/
/*      FUNCTIONS ASSOCIATED
/******************************************************************************************************/
function AverageCustomerBehaviourByValueInvoice($typereport, $NumDaysA, $db){
/* EXPLAIN SQL 2014-05-21	*/
	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));

	if ($typereport == "Shop"){
		$SQL = "SELECT debtorno,
					name,
					(SELECT SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods)
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
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) <= " . AVERAGE_INVOICE_VALUE_01 . "
						GROUP BY salesorders.debtorno) AS invoice01,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) >  " . AVERAGE_INVOICE_VALUE_01 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) <= " . AVERAGE_INVOICE_VALUE_02 . "
						GROUP BY salesorders.debtorno) AS invoice02,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) >  " . AVERAGE_INVOICE_VALUE_02 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) <= " . AVERAGE_INVOICE_VALUE_03 . "
						GROUP BY salesorders.debtorno) AS invoice03,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) >  " . AVERAGE_INVOICE_VALUE_03 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) <= " . AVERAGE_INVOICE_VALUE_04 . "
						GROUP BY salesorders.debtorno) AS invoice04,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) >  " . AVERAGE_INVOICE_VALUE_04 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) <= " . AVERAGE_INVOICE_VALUE_05 . "
						GROUP BY salesorders.debtorno) AS invoice05,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) >  " . AVERAGE_INVOICE_VALUE_05 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) <= " . AVERAGE_INVOICE_VALUE_06 . "
						GROUP BY salesorders.debtorno) AS invoice06,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) >  " . AVERAGE_INVOICE_VALUE_06 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) <= " . AVERAGE_INVOICE_VALUE_07 . "
						GROUP BY salesorders.debtorno) AS invoice07,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) >  " . AVERAGE_INVOICE_VALUE_07 . "
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) <= " . AVERAGE_INVOICE_VALUE_08 . "
						GROUP BY salesorders.debtorno) AS invoice08,
					(SELECT COUNT(DISTINCT(salesorders.orderno))
						FROM salesorders
						WHERE salesorders.orddate >=  '" . $StartDateA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND (salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods) > " . AVERAGE_INVOICE_VALUE_08 . "
						GROUP BY salesorders.debtorno) AS invoice09
				FROM debtorsmaster
				WHERE debtorsmaster.typeid = 2
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
						
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Average value of invoice by ') . $typereport . " during the last " . $NumDaysA . " days.".'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . $typereport . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . 'IDR/Invoice.'. '</th>
							<th class="ascending">' . '# Invoice/Day'. '</th>
							<th class="ascending">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_01,0) . '</th>
							<th class="ascending">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_02,0) . '</th>
							<th class="ascending">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_03,0) . '</th>
							<th class="ascending">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_04,0) . '</th>
							<th class="ascending">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_05,0) . '</th>
							<th class="ascending">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_06,0) . '</th>
							<th class="ascending">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_07,0) . '</th>
							<th class="ascending">' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_08,0) . '</th>
							<th class="ascending">' . '>'. locale_number_format(AVERAGE_INVOICE_VALUE_08,0) . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			if ($typereport == "Shop"){
				$Code = $myrow['debtorno'];
				$Name = $myrow['name'];
			}else{
				return;
			}
			
			printf('<td class="number">%s</td>
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
					locale_number_format($myrow['invoicesum']/$myrow['invoicecount'],0), 
					locale_number_format($myrow['invoicecount']/$NumDaysA,1),
					locale_number_format($myrow['invoice01']/$myrow['invoicecount']*100,1).'%', 
					locale_number_format($myrow['invoice02']/$myrow['invoicecount']*100,1).'%', 
					locale_number_format($myrow['invoice03']/$myrow['invoicecount']*100,1).'%', 
					locale_number_format($myrow['invoice04']/$myrow['invoicecount']*100,1).'%', 
					locale_number_format($myrow['invoice05']/$myrow['invoicecount']*100,1).'%', 
					locale_number_format($myrow['invoice06']/$myrow['invoicecount']*100,1).'%', 
					locale_number_format($myrow['invoice07']/$myrow['invoicecount']*100,1).'%', 
					locale_number_format($myrow['invoice08']/$myrow['invoicecount']*100,1).'%', 
					locale_number_format($myrow['invoice09']/$myrow['invoicecount']*100,1).'%'
					);
			$i++;
			$SumInvoiceSum   += $myrow['invoicesum'];
			$SumInvoiceCount += $myrow['invoicecount'] ;
			$SumInvoice01    += $myrow['invoice01'];
			$SumInvoice02    += $myrow['invoice02'];
			$SumInvoice03    += $myrow['invoice03'];
			$SumInvoice04    += $myrow['invoice04'];
			$SumInvoice05    += $myrow['invoice05'];
			$SumInvoice06    += $myrow['invoice06'];
			$SumInvoice07    += $myrow['invoice07'];
			$SumInvoice08    += $myrow['invoice08'];
			$SumInvoice09    += $myrow['invoice09'];
		}
		printf('<td class="number">%s</td>
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
		echo '</table>
				</div>';
	}
}

function CashStatusPTADU($Year, $YearlyGoal, $db){

	$Today = date('Y-m-d');
	$StartDateYTD=FormatDateForSQL(Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,1,1,Date('Y'))));
	
	// Sales Cash PT ADU during the year
	$SalesCash = 0;

	// Cash Danamon IDR PTADU to Cash Kantor
	$Account = "111121105AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $Today . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO SUPP%'
					OR gltrans.narrative LIKE '%BANK TO CASH%'
					OR gltrans.narrative LIKE '%UANG KECIL%')";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	$BankToCash = -$myrow[0];

	// Expenses ADU Paid by Petty Cash (excluding salaries, Corporate CC)
	$AccountSuffix = "AD";
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $StartDateYTD . "'
				AND pcashdetails.date <= '" . $Today . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%DANAMON'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%".$AccountSuffix."'";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	$ExpensesPTPaidCash = -$myrow[0];
	
	// Cash in Kantor to Small Suppliers PTADU
	$Account = "510010070AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $Today . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH%'
					OR gltrans.narrative LIKE '%KANTOR%')";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	$CashToSmallSuppliers = $myrow[0];

	echo '<p class="page_title_text" align="center"><strong>' . 'Status Cash PT. Angin Dingin Utara ' . $Year . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';

	$TableHeader = '<tr>
						<th>' . 'Concept' . '</th>
						<th>' . 'Value' . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Sales PT ADU Cash', 
			locale_number_format($SalesCash,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash Danamon IDR ADU to Cash Kantor', 
			locale_number_format($BankToCash,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PT.ADU Paid by Petty Cash (excluding salaries, Corporate CC)', 
			locale_number_format(-$ExpensesPTPaidCash,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PTBB Small Suppliers Paid from Cash Kantor', 
			locale_number_format(-$CashToSmallSuppliers,0)
			);
	$CurrentBalance = $SalesCash+$BankToCash-$ExpensesPTPaidCash-$CashToSmallSuppliers;
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current Balance', 
			locale_number_format($CurrentBalance,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Goal for '. $Year, 
			locale_number_format($YearlyGoal,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Balance Cash Kantor PT ADU at end of '. $Year , 
			locale_number_format(-$YearlyGoal+$CurrentBalance,0)
			);

	echo '</table>
		</div>';
}

function CashStatusPTBB($Year, $YearlyGoal, $db){

	$Today = date('Y-m-d');
	$StartDateYTD=FormatDateForSQL(Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,1,1,Date('Y'))));
	
	// Sales PTBB in Cash during the Year
	$Account = "410000000PT";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $Today . "'
				AND gltrans.account = '" . $Account . "'";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	$SalesCash = -$myrow[0];

	// Cash Danamon IDR PTBB to Cash Kantor
	$Account = "111121105PT";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $Today . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH TO CASH%'
					OR gltrans.narrative LIKE '%BANK TO CASH%'
					OR gltrans.narrative LIKE '%CASH TO BANK%'
					OR gltrans.narrative LIKE '%UANG KECIL%')";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	$BankToCash = -$myrow[0];

	// Expenses PT Paid by Petty Cash (excluding salaries, Corporate CC)
	$AccountSuffix = "PT";
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $StartDateYTD . "'
				AND pcashdetails.date <= '" . $Today . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%DANAMON'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%".$AccountSuffix."'";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	$ExpensesPTPaidCash = -$myrow[0];
	
	// Cash in Kantor to Small Suppliers PTBB
	$Account = "510010070PT";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $Today . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH%'
					OR gltrans.narrative LIKE '%KANTOR%')";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	$CashToSmallSuppliers = $myrow[0];

	echo '<p class="page_title_text" align="center"><strong>' . 'Status Cash PT.Bumi Biru ' . $Year . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';

	$TableHeader = '<tr>
						<th>' . 'Concept' . '</th>
						<th>' . 'Value' . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Sales Retail PT.BB Cash', 
			locale_number_format($SalesCash,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Cash Danamon IDR PTBB to Cash Kantor', 
			locale_number_format($BankToCash,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PTBB Paid by Petty Cash (excluding salaries, Corporate CC)', 
			locale_number_format(-$ExpensesPTPaidCash,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Expenses PTBB Small Suppliers Paid from Cash Kantor', 
			locale_number_format(-$CashToSmallSuppliers,0)
			);
	$CurrentBalance = $SalesCash+$BankToCash-$ExpensesPTPaidCash-$CashToSmallSuppliers;
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Current Balance', 
			locale_number_format($CurrentBalance,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Goal for '. $Year, 
			locale_number_format($YearlyGoal,0)
			);
	printf('<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Balance Cash Kantor PTBB at end of '. $Year , 
			locale_number_format(-$YearlyGoal+$CurrentBalance,0)
			);

	echo '</table>
		</div>';
}

function DailySalesRecords($Days, $NumDays, $db){

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

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

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Top and bottom ') . $Days . _(' retail sales days since '). ConvertSQLDate($FromDate) .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' .  _('#') . '</th>
							<th class="ascending">' .  _('Date') . '</th>
							<th class="ascending">' . _('Sales') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while (($myrow = DB_fetch_array($result)) AND ($i <= $Days)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($i,0),
					ConvertSQLDate($myrow['orddate']),
					locale_number_format($myrow['sales'],0)
					);
			$i++;
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
			ORDER BY SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) ASC
			LIMIT ". $Days . "";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$TableHeader = '<tr>
							<th class="ascending"></th>
							<th class="ascending"></th>
							<th class="ascending"></th>
						</tr>';
		echo $TableHeader;
		$i = 1;
		while (($myrow = DB_fetch_array($result)) AND ($i <= $Days)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($i,0),
					ConvertSQLDate($myrow['orddate']),
					locale_number_format($myrow['sales'],0)
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

function GeneralCustomerBehaviour($NumDaysA, $db){
	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));
	$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-365));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1-365));

	$SQL = "SELECT debtorno,
				name,
				(SELECT SUM(salesorderdetails.qtyinvoiced)
					FROM salesorders, salesorderdetails
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorderdetails.orderno = salesorders.orderno
					GROUP BY salesorders.debtorno) AS itemcount,
				(SELECT SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods)
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
				(SELECT SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard + klreturnedgoods)
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
			FROM debtorsmaster
			WHERE debtorsmaster.typeid = 2
			ORDER BY (SELECT COUNT(DISTINCT(salesorders.orderno))
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) DESC";
	
						
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "General Customer Behaviour by shop during the last " . $NumDaysA . " days.".'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th colspan="3"></th>
							<th colspan="5">' . 'This year'. '</th>
							<th colspan="5">' . 'Last year'. '</th>
						</tr>';
		echo $TableHeader;
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . _('Shop') . '</th>
							<th>' . _('Name') . '</th>
							<th>' . 'IDR/Invoice'. '</th>
							<th>' . 'IDR/Piece'. '</th>
							<th>' . '# Invoice/Day'. '</th>
							<th>' . '# Pcs/Day'. '</th>
							<th>' . '# Pcs/Inv'. '</th>
							<th>' . 'IDR/Invoice'. '</th>
							<th>' . 'IDR/Piece'. '</th>
							<th>' . '# Invoice/Day'. '</th>
							<th>' . '# Pcs/Day'. '</th>
							<th>' . '# Pcs/Inv'. '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			$Code = $myrow['debtorno'];
			$Name = $myrow['name'];
			
			printf('<td class="number">%s</td>
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
					locale_number_format_zero_blank($myrow['invoicesum']/$myrow['invoicecount'],0), 
					locale_number_format_zero_blank($myrow['invoicesum']/$myrow['itemcount'],0), 
					locale_number_format_zero_blank($myrow['invoicecount']/$NumDaysA,1),
					locale_number_format_zero_blank($myrow['itemcount']/$NumDaysA,1),
					locale_number_format_zero_blank($myrow['itemcount']/$myrow['invoicecount'],1),
					locale_number_format_zero_blank($myrow['invoicesum_lastyear']/$myrow['invoicecount_lastyear'],0), 
					locale_number_format_zero_blank($myrow['invoicesum_lastyear']/$myrow['itemcount_lastyear'],0), 
					locale_number_format_zero_blank($myrow['invoicecount_lastyear']/$NumDaysA,1),
					locale_number_format_zero_blank($myrow['itemcount_lastyear']/$NumDaysA,1),
					locale_number_format_zero_blank($myrow['itemcount_lastyear']/$myrow['invoicecount_lastyear'],1)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PackagingStatusForBlink($RootPath, $db){

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.rldaysforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB03-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB03-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB03-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-L') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-L') AS rl_shopping_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB04-L') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-M') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-M') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB04-M') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-S') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-S') AS rl_shopping_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB04-S') AS ot_shopping_s
			FROM locations
			WHERE locations.typeloc = 'SHOPBL'
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'BLINK Shop Packaging Stock Status by Shop' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
									<th colspan="3">' . _('BLINK PouchBag L') . '</th>
									<th colspan="3">' . _('BLINK PouchBag M') . '</th>
									<th colspan="3">' . _('BLINK PouchBag S') . '</th>
									<th colspan="3">' . _('BLINK ShoppingBag L') . '</th>
									<th colspan="3">' . _('BLINK ShoppingBag M') . '</th>
									<th colspan="3">' . _('BLINK ShoppingBag S') . '</th>
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('Days RL') . '</th>
									<th class="ascending">' . _('Factor') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

			printf('<td>%s</td>
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
					$myrow['locationname'], 
					$myrow['rldaysforpackaging'], 
					$myrow['rlfactorforpackaging'], 
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_l'],0),
					locale_number_format_zero_blank($myrow['rl_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_m'],0),
					locale_number_format_zero_blank($myrow['rl_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_s'],0),
					locale_number_format_zero_blank($myrow['rl_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_l'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_l'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_m'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_m'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_s'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_s'],0)
					);

			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function PackagingStatusForKapalLaut($RootPath, $db){

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
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-L') AS ot_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS qty_box_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS rl_box_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-M') AS ot_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS qty_box_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS rl_box_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-S') AS ot_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS rl_shopping_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-L') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-M') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS rl_shopping_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-S') AS ot_shopping_s
			FROM locations
			WHERE locations.typeloc = 'SHOPKL'
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shop Packaging Stock Status by Shop' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
									<th colspan="3">' . _('Box L') . '</th>
									<th colspan="3">' . _('Box M') . '</th>
									<th colspan="3">' . _('Box S') . '</th>
									<th colspan="3">' . _('PouchBag L') . '</th>
									<th colspan="3">' . _('PouchBag M') . '</th>
									<th colspan="3">' . _('PouchBag S') . '</th>
									<th colspan="3">' . _('ShoppingBag L') . '</th>
									<th colspan="3">' . _('ShoppingBag M') . '</th>
									<th colspan="3">' . _('ShoppingBag S') . '</th>
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('Days RL') . '</th>
									<th class="ascending">' . _('Factor') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

			printf('<td>%s</td>
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
					$myrow['locationname'], 
					$myrow['rldaysforpackaging'], 
					$myrow['rlfactorforpackaging'], 
					locale_number_format_zero_blank($myrow['qty_box_l'],0), 
					locale_number_format_zero_blank($myrow['ot_box_l'],0),
					locale_number_format_zero_blank($myrow['rl_box_l'],0),
					locale_number_format_zero_blank($myrow['qty_box_m'],0), 
					locale_number_format_zero_blank($myrow['ot_box_m'],0),
					locale_number_format_zero_blank($myrow['rl_box_m'],0),
					locale_number_format_zero_blank($myrow['qty_box_s'],0), 
					locale_number_format_zero_blank($myrow['ot_box_s'],0),
					locale_number_format_zero_blank($myrow['rl_box_s'],0),
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_l'],0),
					locale_number_format_zero_blank($myrow['rl_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_m'],0),
					locale_number_format_zero_blank($myrow['rl_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_s'],0),
					locale_number_format_zero_blank($myrow['rl_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_l'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_l'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_m'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_m'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_s'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_s'],0)
					);

			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function PackagingStatusForOutlet($RootPath, $db){

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
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB03') AS ot_shopping_m
			FROM locations
			WHERE locations.typeloc = 'SHOPOU'
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'OUTLET Shop Packaging Stock Status by Shop' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag L') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag M') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag S') . '</th>
									<th colspan="3">' . _('OUTLET ShoppingBag') . '</th>
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('Days RL') . '</th>
									<th class="ascending">' . _('Factor') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

			printf('<td>%s</td>
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
					$myrow['locationname'], 
					$myrow['rldaysforpackaging'], 
					$myrow['rlfactorforpackaging'], 
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_l'],0),
					locale_number_format_zero_blank($myrow['rl_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_m'],0),
					locale_number_format_zero_blank($myrow['rl_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_s'],0),
					locale_number_format_zero_blank($myrow['rl_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_m'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_m'],0)
					);

			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function PackagingUsageForBlink($NumDays, $RootPath, $db){
/* EXPLAIN 2014-05-20	 OK! */

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$NumDays));

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
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
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	
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

	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'BLINK Shop Packaging Usage during the last ' . $NumDays . ' days'. '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th colspan="3">' . _('PouchBag L') . '</th>
									<th colspan="3">' . _('PouchBag M') . '</th>
									<th colspan="3">' . _('PouchBag S') . '</th>
									<th colspan="3">' . _('ShoppingBag L') . '</th>
									<th colspan="3">' . _('ShoppingBag M') . '</th>
									<th colspan="3">' . _('ShoppingBag S') . '</th>
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

			printf('<td>%s</td>
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
					$myrow['locationname'], 
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_l']/($myrow['sales_bag_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m']/($myrow['sales_bag_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s']/($myrow['sales_bag_s']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_l'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_l']/($myrow['sales_shopping_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_m'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m']/($myrow['sales_shopping_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_s'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_s']/($myrow['sales_shopping_s']/$NumDays),0)
					);

			$totalqty_bag_l   = $totalqty_bag_l + $myrow['qty_bag_l'];
			$totalsales_bag_l = $totalsales_bag_l + $myrow['sales_bag_l'];
			$totalqty_bag_m   = $totalqty_bag_m + $myrow['qty_bag_m'];
			$totalsales_bag_m = $totalsales_bag_m + $myrow['sales_bag_m'];
			$totalqty_bag_s   = $totalqty_bag_s + $myrow['qty_bag_s'];
			$totalsales_bag_s = $totalsales_bag_s + $myrow['sales_bag_s'];

			$totalqty_shopping_l    = $totalqty_shopping_l + $myrow['qty_shopping_l'];
			$totalsales_shopping_l  = $totalsales_shopping_l + $myrow['sales_shopping_l'];
			$totalqty_shopping_m    = $totalqty_shopping_m + $myrow['qty_shopping_m'];
			$totalsales_shopping_m  = $totalsales_shopping_m + $myrow['sales_shopping_m'];
			$totalqty_shopping_s    = $totalqty_shopping_s + $myrow['qty_shopping_s'];
			$totalsales_shopping_s  = $totalsales_shopping_s + $myrow['sales_shopping_s'];

			$i++;
		}
		if (!$showHeader){
			printf('<td>%s</td>
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
			echo '</table>
				</div>';
		}
	}
}

function PackagingUsageForKapalLaut($NumDays, $RootPath, $db){
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
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
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

	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shop Packaging Usage during the last ' . $NumDays . ' days'. '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th colspan="3">' . _('Box L') . '</th>
									<th colspan="3">' . _('Box M') . '</th>
									<th colspan="3">' . _('Box S') . '</th>
									<th colspan="3">' . _('PouchBag L') . '</th>
									<th colspan="3">' . _('PouchBag M') . '</th>
									<th colspan="3">' . _('PouchBag S') . '</th>
									<th colspan="3">' . _('ShoppingBag L') . '</th>
									<th colspan="3">' . _('ShoppingBag M') . '</th>
									<th colspan="3">' . _('ShoppingBag S') . '</th>
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

			printf('<td>%s</td>
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
					$myrow['locationname'], 
					locale_number_format_zero_blank($myrow['qty_box_l'],0), 
					locale_number_format_zero_blank($myrow['sales_box_l'],0),
					locale_number_format_zero_blank($myrow['qty_box_l']/($myrow['sales_box_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_box_m'],0), 
					locale_number_format_zero_blank($myrow['sales_box_m'],0),
					locale_number_format_zero_blank($myrow['qty_box_m']/($myrow['sales_box_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_box_s'],0), 
					locale_number_format_zero_blank($myrow['sales_box_s'],0),
					locale_number_format_zero_blank($myrow['qty_box_s']/($myrow['sales_box_s']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_l']/($myrow['sales_bag_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m']/($myrow['sales_bag_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s']/($myrow['sales_bag_s']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_l'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_l']/($myrow['sales_shopping_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_m'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m']/($myrow['sales_shopping_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_s'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_s']/($myrow['sales_shopping_s']/$NumDays),0)
					);
			$totalqty_box_l   = $totalqty_box_l + $myrow['qty_box_l'];
			$totalsales_box_l = $totalsales_box_l + $myrow['sales_box_l'];
			$totalqty_box_m   = $totalqty_box_m + $myrow['qty_box_m'];
			$totalsales_box_m = $totalsales_box_m + $myrow['sales_box_m'];
			$totalqty_box_s   = $totalqty_box_s + $myrow['qty_box_s'];
			$totalsales_box_s = $totalsales_box_s + $myrow['sales_box_s'];

			$totalqty_bag_l   = $totalqty_bag_l + $myrow['qty_bag_l'];
			$totalsales_bag_l = $totalsales_bag_l + $myrow['sales_bag_l'];
			$totalqty_bag_m   = $totalqty_bag_m + $myrow['qty_bag_m'];
			$totalsales_bag_m = $totalsales_bag_m + $myrow['sales_bag_m'];
			$totalqty_bag_s   = $totalqty_bag_s + $myrow['qty_bag_s'];
			$totalsales_bag_s = $totalsales_bag_s + $myrow['sales_bag_s'];

			$totalqty_shopping_l    = $totalqty_shopping_l + $myrow['qty_shopping_l'];
			$totalsales_shopping_l  = $totalsales_shopping_l + $myrow['sales_shopping_l'];
			$totalqty_shopping_m    = $totalqty_shopping_m + $myrow['qty_shopping_m'];
			$totalsales_shopping_m  = $totalsales_shopping_m + $myrow['sales_shopping_m'];
			$totalqty_shopping_s    = $totalqty_shopping_s + $myrow['qty_shopping_s'];
			$totalsales_shopping_s  = $totalsales_shopping_s + $myrow['sales_shopping_s'];

			$i++;
		}
		if (!$showHeader){
			printf('<td>%s</td>
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
			echo '</table>
				</div>';
		}
	}
}


function PackagingUsageByWeeks($RootPath, $db){

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
	$result = DB_query($SQL);
	
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		echo '<p class="page_title_text" align="center"><strong>' . 'Shop Packaging Usage by week'. '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . ConvertSQLDate($StartWeek1) . '</th>
							<th class="ascending">' . ConvertSQLDate($StartWeek2) . '</th>
							<th class="ascending">' . ConvertSQLDate($StartWeek3) . '</th>
							<th class="ascending">' . ConvertSQLDate($StartWeek4) . '</th>
							<th class="ascending">' . ConvertSQLDate($StartWeek5) . '</th>
							<th class="ascending">' . ConvertSQLDate($StartWeek6) . '</th>
							<th class="ascending">' . ConvertSQLDate($StartWeek7) . '</th>
							<th class="ascending">' . ConvertSQLDate($StartWeek8) . '</th>
							<th class="ascending">' . _('Average') . '</th>
						</tr>';
		echo $TableHeader;

		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$Average = ($myrow['useweek1'] + 
					$myrow['useweek2'] + 
					$myrow['useweek3'] + 
					$myrow['useweek4'] + 
					$myrow['useweek5'] + 
					$myrow['useweek6'] + 
					$myrow['useweek7'] + 
					$myrow['useweek8']) / 8;
					
			printf('<td>%s</td>
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
					$myrow['stockid'], 
					$myrow['description'], 
					locale_number_format_zero_blank($myrow['useweek1'],0), 
					locale_number_format_zero_blank($myrow['useweek2'],0), 
					locale_number_format_zero_blank($myrow['useweek3'],0), 
					locale_number_format_zero_blank($myrow['useweek4'],0), 
					locale_number_format_zero_blank($myrow['useweek5'],0), 
					locale_number_format_zero_blank($myrow['useweek6'],0), 
					locale_number_format_zero_blank($myrow['useweek7'],0), 
					locale_number_format_zero_blank($myrow['useweek8'],0), 
					locale_number_format_zero_blank($Average,0) 
					);
		}
		echo '</table>
			</div>';
	}
}

function PackagingUsageForOutlet($NumDays, $RootPath, $db){
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
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	
	$totalqty_bag_l   = 0;
	$totalsales_bag_l = 0;
	$totalqty_bag_m   = 0;
	$totalsales_bag_m = 0;
	$totalqty_bag_s   = 0;
	$totalsales_bag_s = 0;
	
	$totalqty_shopping_m    = 0;
	$totalsales_shopping_m  = 0;

	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'OUTLET Shop Packaging Usage during the last ' . $NumDays . ' days'. '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag L') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag M') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag S') . '</th>
									<th colspan="3">' . _('OUTLET ShoppingBag M') . '</th>
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

			printf('<td>%s</td>
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
					$myrow['locationname'], 
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_l']/($myrow['sales_bag_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m']/($myrow['sales_bag_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s']/($myrow['sales_bag_s']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_m'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m']/($myrow['sales_shopping_m']/$NumDays),0)
					);

			$totalqty_bag_l   = $totalqty_bag_l + $myrow['qty_bag_l'];
			$totalsales_bag_l = $totalsales_bag_l + $myrow['sales_bag_l'];
			$totalqty_bag_m   = $totalqty_bag_m + $myrow['qty_bag_m'];
			$totalsales_bag_m = $totalsales_bag_m + $myrow['sales_bag_m'];
			$totalqty_bag_s   = $totalqty_bag_s + $myrow['qty_bag_s'];
			$totalsales_bag_s = $totalsales_bag_s + $myrow['sales_bag_s'];

			$totalqty_shopping_m    = $totalqty_shopping_m + $myrow['qty_shopping_m'];
			$totalsales_shopping_m  = $totalsales_shopping_m + $myrow['sales_shopping_m'];

			$i++;
		}
		if (!$showHeader){
			printf('<td>%s</td>
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
			echo '</table>
				</div>';
		}
	}
}

function PettyCashStatus($currency, $db){

	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pctabs.currency = '". $currency ."'
				AND pcashdetails.authorized != '0000-00-00'
			GROUP BY pcashdetails.tabcode
			HAVING ( SUM(pcashdetails.amount) <= -0.01
					OR SUM(pcashdetails.amount) >= 0.01)";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Authorized Status for '). $currency . ' accounts'  . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('PC Tab Code') . '</th>
							<th class="ascending">' . _('Amount') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$total = 0;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$myrow['tabcode'], 
					locale_number_format($myrow['amount'],0)
					);
			$i++;
			$total = $total + $myrow['amount'];
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'Total', 
				locale_number_format($total,0)
				);
		
		echo '</table>
				</div>';
	}
}

function YearDifferenceSales($typereport, $NumDaysA, $db){

	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA));
	$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-365));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-365));

	$TotalDateA = 0;
	$TotalDateB = 0;
	$TotalRent = 0;
	$TotalBothYearsDateA = 0;
	$TotalBothYearsDateB = 0;
	$TotalBothYearsRent = 0;
	$TotalNewDateA = 0;
	$TotalNewRent = 0;
	$TotalOldRent = 0;

	if ($typereport == "Shop"){
		$SQL = "SELECT debtorno,
					name,
					(SELECT locations.klyearlyrent 
						FROM locations
						WHERE locations.cashsalecustomer = debtorsmaster.debtorno
						LIMIT 1) AS yearlyrent,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateB . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesB
				FROM debtorsmaster
				WHERE debtorsmaster.typeid = 2
				ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) DESC";
	}else{
		$SQL = "SELECT salesmancode,
					salesmanname,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateB . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesB
				FROM salesman
				WHERE salesman.current = 1
				ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) DESC";
	}
	
						
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Difference sales for ') . $typereport . " during the last " . $NumDaysA . " days and same period last year".'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . $typereport . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . $NumDaysA . _(' Days This Year') . '</th>
							<th class="ascending">' . $NumDaysA . _(' Days Last Year') . '</th>
							<th class="ascending">' . _('Trend') . '</th>
							<th class="ascending">' . _('%Rent/Sales') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			if ($typereport == "Shop"){
				$Code = $myrow['debtorno'];
				$Name = $myrow['name'];
				if (($myrow['salesA'] > 0) AND ($myrow['yearlyrent'] > 0)){
					$Rent = round(($myrow['yearlyrent'] / 365 * $NumDaysA) / $myrow['salesA'] * 100) . '%';
				}else{
					$Rent = "";
				}
			}else{
				$Code = $myrow['salesmancode'];
				$Name = $myrow['salesmanname'];
				$Rent = "";
			}
			
			$percent = (($myrow['salesA'])-($myrow['salesB']))/($myrow['salesB']) * 100;
			$trend = " ";
			if ($percent > IMPROVEMENT_SALES_COMPARED_LAST_YEAR){
				$trend = "Improving ". locale_number_format($percent,0) . "%";
			}
			if ($percent < -IMPROVEMENT_SALES_COMPARED_LAST_YEAR){
				$trend = "Degrading ". locale_number_format($percent,0) . "%";
			}
			
			printf('<td>%s</td>
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
					locale_number_format($myrow['salesA'],0), 
					locale_number_format($myrow['salesB'],0), 
					$trend,
					$Rent
					);

			if (($myrow['salesA'] > 0) AND ($myrow['salesB'] > 0)){
				$TotalBothYearsDateA = $TotalBothYearsDateA +($myrow['salesA']);
				$TotalBothYearsDateB = $TotalBothYearsDateB +($myrow['salesB']);
				$TotalBothYearsRent = $TotalBothYearsRent +($myrow['yearlyrent']);
			}
			if (($myrow['salesA'] > 0) AND ($myrow['salesB'] == 0)){
				$TotalNewDateA = $TotalNewDateA +($myrow['salesA']);
				$TotalNewRent = $TotalNewRent +($myrow['yearlyrent']);
			}
			if (($myrow['salesA'] == 0) AND ($myrow['salesB'] > 0)){
				$TotalOldDateB = $TotalOldDateB +($myrow['salesB']);
				$TotalOldRent = $TotalOldRent +($myrow['yearlyrent']);
			}
			$TotalDateA = $TotalDateA +($myrow['salesA']);
			$TotalRent = $TotalRent +($myrow['yearlyrent']);
			$TotalDateB = $TotalDateB +($myrow['salesB']);
			$i++;
		}
		if ($typereport == "Shop"){
			$percent = (($TotalBothYearsDateA)-($TotalBothYearsDateB))/($TotalBothYearsDateB) * 100;
			$trend = " ";
			if ($percent > 0){
				$trend = "Improving ". locale_number_format($percent,0) . "%";
			}
			if ($percent < 0){
				$trend = "Degrading ". locale_number_format($percent,0) . "%";
			}
			$k = StartEvenOrOddRow($k);
			$Rent = round(($TotalBothYearsRent / 365 * $NumDaysA) / $TotalBothYearsDateA * 100) . '%';
			printf('<td>%s</td>
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
					$trend,
					$Rent
					);
			if ($TotalNewDateA > 0){
				$k = StartEvenOrOddRow($k);
				$Rent = round(($TotalNewRent / 365 * $NumDaysA) / $TotalNewDateA * 100) . '%';
				printf('<td>%s</td>
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
				$k = StartEvenOrOddRow($k);
				$Rent = round(($TotalOldRent / 365 * $NumDaysA) / $TotalOldDateB * 100) . '%';
				printf('<td>%s</td>
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
			$percent = (($TotalDateA)-($TotalDateB))/($TotalDateB) * 100;
			$trend = " ";
			if ($percent > 0){
				$trend = "Improving ". locale_number_format($percent,0) . "%";
			}
			if ($percent < 0){
				$trend = "Degrading ". locale_number_format($percent,0) . "%";
			}
			$k = StartEvenOrOddRow($k);
			$Rent = round(($TotalRent / 365 * $NumDaysA) / $TotalDateA * 100) . '%';
			printf('<td>%s</td>
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
					$trend,
					$Rent
					);
		}
		echo '</table>
				</div>';
	}
}

?>
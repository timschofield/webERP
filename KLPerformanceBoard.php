<?php
define("VERSIONFILE", "3.00");

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

if (!isset($_GET['Section'])){
	$ProcessSection01 = TRUE;
	$ProcessSection02 = TRUE;
}else{
	$ShowSectionInfo = TRUE;
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = TRUE;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = TRUE;
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
		prnMsg("Performing Control Panel Section 01",'info');
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
	//	AverageSales("Shop", 365, 90, 30, 15, 7, 1, 30, "LastYear", "All", $db);
	//	$NumberOfTestExecuted++;
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
		AverageCustomerBehaviourByValueInvoice("Shop", 15, $db);
		$NumberOfTestExecuted++;
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
		AverageCustomerBehaviourByValueInvoice("Shop", 90, $db);
		$NumberOfTestExecuted++;
		AverageCustomerBehaviourByValueInvoice("Shop", 365, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_ShopManager
		OR $KL_OperationalManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){
		GeneralCustomerBehaviour(30, $db);
		$NumberOfTestExecuted++;
		GeneralCustomerBehaviour(90, $db);
		$NumberOfTestExecuted++;
	}
	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		DailySalesRecords(10, '2017-06-30', $db);
		$NumberOfTestExecuted++;
//		DailySalesRecordsByShops(10, '2015-06-01', $db);
//		$NumberOfTestExecuted++;
	}
}

/***************************************************************************************
* SECTION 2
***************************************************************************************/

if ($ProcessSection02){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 02",'info');
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
		FinishedStockDistribution("DISPLAYS", "LOCATION", $db);
		$NumberOfTestExecuted++;
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
	}

	if ($KL_SystemAdmin){
		FinishedStockDistribution("PACKAGING", "LOCATION", $db);
		$NumberOfTestExecuted++;
	}
	
	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_BusinessDevelopmentManager){
		InsuficientStockForShopPackaging( 'SHPACK', 21, 95, 30, true, $RootPath, $db);
		$NumberOfTestExecuted++;
		InsuficientStockForShopPackaging( 'ZAPON', 21, 60, 30, true, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin){
		GoodsToBeProduced("COMPON", "ONLYDISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "DISCOUNT", $RootPath, $db);
		$NumberOfTestExecuted++;
		GoodsToBeProduced("COMPON", "ALL", $RootPath, $db);
		$NumberOfTestExecuted++;
		ComponentsToObsolete(false, 0, $RootPath, $db);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin
		OR $KL_OperationalManager){
		POStatusControl("IN NEGOTIAION WITH SUPPLIER", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("ON PRODUCTION", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("FINISHED BUT NOT PAID", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("STILL NOT FULLY PAID", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("BALI PAID BUT NOT RECEIVED IN KANTOR", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("BALI RECEIVED IN KANTOR BUT NOT PAID", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PAID NOT SHIPPED BY SUPPLIER", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("PAID NOT RECEIVED IN CARGO AGENT", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("IN CARGO AGENT BUT NOT SHIPPED", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("SHIPPED IN TRANSIT", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("CUSTOMS CLEARANCE", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("RECEIVED IN KANTOR", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		POStatusControl("ARRIVING IN NEXT DAYS", 60, $RootPath, $db);
		$NumberOfTestExecuted++;

/*		PurchasingOrdersDeliveryControl("Delayed", "Delivery", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		PurchasingOrdersDeliveryControl("Coming Soon", "Delivery", 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		PurchasingOrdersDeliveryControl("Delayed", "Payment", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		PurchasingOrdersDeliveryControl("Coming Soon", "Payment", 30, $RootPath, $db);
		$NumberOfTestExecuted++;
		PurchasingOrdersDeliveryControl("Coming Soon", "Payment", 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		PurchasingOrdersDeliveryControl("Delayed", "Arrival", 0, $RootPath, $db);
		$NumberOfTestExecuted++;
		PurchasingOrdersDeliveryControl("Coming Soon", "Arrival", 30, $RootPath, $db);
		$NumberOfTestExecuted++;
*/	}

//	RetailTypePayments("Shop",180, $db);
//	NumberOfTestExecuted++;

	if ($KL_SystemAdmin 
		OR $KL_OperationalManager
		OR $KL_ShopManager
		OR $KL_SalesDirector
		OR $KL_BusinessDevelopmentManager){	
		RetailTypePayments("SPG",180, $db);
		$NumberOfTestExecuted++;
		RetailTypePayments("SPG",  15, $db);
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

function RetailTypePayments($typereport, $maxdays, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$totalcash = 0;
	$totalcredit = 0;
	$totalreturned = 0;
	$totalvouchers = 0;
	$total = 0;

	if ($typereport == "Shop"){
		$SQL = "SELECT salesorders.debtorno AS reportunit,
					debtorsmaster.name AS reportname,
					SUM(salesorders.klpaidcash) AS cashshop, 
					SUM(salesorders.klpaidcreditcard) AS creditshop, 
					SUM(salesorders.klreturnedgoods) AS returnedgoodsshop,
					SUM(salesorders.klvouchers) AS vouchersshop,
					SUM(salesorders.klpaidcash+salesorders.klpaidcreditcard+salesorders.klreturnedgoods+salesorders.klvouchers) AS totalshop
			FROM salesorders, debtorsmaster
			WHERE salesorders.debtorno = debtorsmaster.debtorno
				AND salesorders.orddate >= '". $StartDate. "'
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			GROUP BY salesorders.debtorno
			ORDER BY salesorders.debtorno";
	}else{
		$SQL = "SELECT salesorders.salesperson AS reportunit, 
					salesman.salesmanname AS reportname,
					SUM(klpaidcash) AS cashshop, 
					SUM(klpaidcreditcard) AS creditshop, 
					SUM(klreturnedgoods) AS returnedgoodsshop,
					SUM(klvouchers) AS vouchersshop,
					SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers) AS totalshop
			FROM salesorders, salesman, debtorsmaster
			WHERE salesorders.debtorno = debtorsmaster.debtorno
				AND salesorders.salesperson = salesman.salesmancode
				AND orddate >= '". $StartDate. "'
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			GROUP BY salesorders.salesperson
			ORDER BY salesorders.salesperson";
	}
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Distribution Cash / Credit Card during the last ') . $maxdays . _(' days by ') .$typereport .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . $typereport . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('% Cash') . '</th>
							<th class="ascending">' . _('% Credit') . '</th>
							<th class="ascending">' . _('% Returns') . '</th>
							<th class="ascending">' . _('% Vouchers') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['totalshop'] != 0){
				$k = StartEvenOrOddRow($k);
				
				$percentcash = locale_number_format(($myrow['cashshop']/$myrow['totalshop'])*100,1);
				$percentcredit = locale_number_format(($myrow['creditshop']/$myrow['totalshop'])*100,1);
				$percentreturns = locale_number_format(($myrow['returnedgoodsshop']/$myrow['totalshop'])*100,1);
				$percentvouchers = locale_number_format(($myrow['vouchersshop']/$myrow['totalshop'])*100,1);
				
				$totalcash = $totalcash + $myrow['cashshop'];
				$totalcredit = $totalcredit + $myrow['creditshop'];
				$totalreturned = $totalreturned + $myrow['returnedgoodsshop'];
				$totalvouchers = $totalvouchers + $myrow['vouchersshop'];
				$total = $total + $myrow['totalshop'];
				
				printf('<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$myrow['reportunit'],
						$myrow['reportname'],
						$percentcash, 
						$percentcredit, 
						$percentreturns, 
						$percentvouchers
						);
				$i++;
			}
		}

		$percentcash = locale_number_format(($totalcash/$total)*100,1);
		$percentcredit = locale_number_format(($totalcredit/$total)*100,1);
		$percentreturns = locale_number_format(($totalreturned/$total)*100,1);
		$percentvouchers = locale_number_format(($totalvouchers/$total)*100,1);
		
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				"",
				"Average",
				$percentcash, 
				$percentcredit, 
				$percentreturns, 
				$percentvouchers
				);
		
		echo '</table>
				</div>';
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

function DailySalesRecords($Days, $FromDate, $db){

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

function DailySalesRecordsByShops($Days, $FromDate, $db){

	$SQL = "SELECT salesorders.orddate,
				salesorders.debtorno,
				SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) AS sales
			FROM salesorders
			INNER JOIN salesorderdetails ON
				salesorders.orderno=salesorderdetails.orderno
			INNER JOIN debtorsmaster ON 
				salesorders.debtorno = debtorsmaster.debtorno
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
				AND salesorders.orddate >= '" . $FromDate . "'
			GROUP BY salesorders.orddate,salesorders.debtorno
			ORDER BY SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) DESC
			LIMIT ". $Days . "";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Top ') . $Days . _(' retail sales days by shop since '). ConvertSQLDate($FromDate) .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' .  _('#') . '</th>
							<th class="ascending">' .  _('Date') . '</th>
							<th class="ascending">' .  _('Shop') . '</th>
							<th class="ascending">' . _('Sales') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while (($myrow = DB_fetch_array($result)) AND ($i <= $Days)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($i,0),
					ConvertSQLDate($myrow['orddate']),
					$myrow['debtorno'],
					locale_number_format($myrow['sales'],0)
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

?>
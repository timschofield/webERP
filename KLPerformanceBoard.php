<?php
define("VERSIONFILE", "2.11"); 
define("NUMBER_OF_TESTS", 43); 

/* Session started in session.inc for password checking and authorisation level check config.php is in turn included in session.inc*/

include ('includes/session.inc');
$Title = _('Kapal-Laut General Performance Board '. VERSIONFILE);
include ('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

$begintime = time_start();

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

AverageSales("Shop", 365, 90, 30, 15, 7, 1, 30, "CurrentYear", "All", $db);

if ($_SESSION['UserID'] == "Ricard"){
//	AverageSales("Shop", 365, 90, 30, 15,7, 1, 30, "LastYear", "All", $db);
	YearDifferenceSales("Shop",  15, $db);
	YearDifferenceSales("Shop",  30, $db);
	YearDifferenceSales("Shop",	 90, $db);
	YearDifferenceSales("Shop", 365, $db);
}

AverageSales("SPG", 365, 90, 30, 15, 7, 1,30, "CurrentYear", "All", $db);

SPGPerformanceByShop("RETAIL66", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILSE", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILOB", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILKS", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILBW", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILSA", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILSU", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILSS", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILUB", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILMF", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILPU", 30, 60, 90, $db);
SPGPerformanceByShop("RETAILJC", 30, 60, 90, $db);



if ($_SESSION['UserID'] == "Ricard"){
	YearDifferenceSales("SPG", 30, $db);
	YearDifferenceSales("SPG", 90, $db);
}

if ($_SESSION['UserID'] == "Ricard"){
	AverageCustomerBehaviour("Shop", 15, $db);
}

AverageCustomerBehaviour("Shop", 30, $db);

if ($_SESSION['UserID'] == "Ricard"){
	AverageCustomerBehaviour("Shop", 90, $db);
	AverageCustomerBehaviour("Shop", 365, $db);
}

if ($_SESSION['UserID'] == "Ricard"){
	ListPriorityLocations($db);
}

ActiveTransfersByLocation($RootPath, $db);
ActiveTransferStatus($RootPath, $db);
RecentlyClosedTransferStatus(1, $RootPath, $db);

FinishedStockDistribution("FORSALE", "LOCATION", $db);

if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Laia")){
	FinishedStockDistribution("FORSALE", "STOCKCATEGORY", $db);
}

FinishedStockDistribution("DISPLAYS", "LOCATION", $db);

PackagingStatus($RootPath, $db);

if ($_SESSION['UserID'] == "Ricard"){
	PackagingUsage(30, $RootPath, $db);
	FinishedStockDistribution("PACKAGING", "LOCATION", $db);
}
if (($_SESSION['UserID'] == "Ricard") 
	OR ($_SESSION['UserID'] == "Ike1")
	OR ($_SESSION['UserID'] == "Laia")){
	InsuficientStockForShopPackaging( 'SHPACK', 21, 100, 30, true, $RootPath, $db);
	InsuficientStockForShopPackaging( 'ZAPON', 21, 60, 30, true, $RootPath, $db);
}

if ($_SESSION['UserID'] == "Ricard"){
	GoodsToBeProduced("COMPON",$RootPath, $db);
	ComponentsToObsolete(false, 0, $RootPath, $db);
}

// RetailTypePayments("Shop",180, $db);

RetailTypePayments("SPG",180, $db);
RetailTypePayments("SPG",  15, $db);

if ($_SESSION['UserID'] == "Ricard"){
	PettyCashStatus("IDR", $db);
	PettyCashStatus("USD", $db);
	PettyCashStatus("THB", $db);
	PettyCashStatus("EUR", $db);
	PettyCashStatus("HKD", $db);
}

prnMsg("Performed ". NUMBER_OF_TESTS . " performance tests",'success');
time_finish($begintime);

include ('includes/footer.inc');

/******************************************************************************************************/
/*      FUNCTIONS ASSOCIATED
/******************************************************************************************************/
function AverageCustomerBehaviour($typereport, $NumDaysA, $db){
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
						
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Average value of invoice by ') . $typereport . " during the last " . $NumDaysA . " days.".'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . $typereport . '</th>
							<th>' . _('Name') . '</th>
							<th>' . 'IDR/Invoice.'. '</th>
							<th>' . '# Invoice/Day'. '</th>
							<th>' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_01,0) . '</th>
							<th>' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_02,0) . '</th>
							<th>' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_03,0) . '</th>
							<th>' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_04,0) . '</th>
							<th>' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_05,0) . '</th>
							<th>' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_06,0) . '</th>
							<th>' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_07,0) . '</th>
							<th>' . '<='. locale_number_format(AVERAGE_INVOICE_VALUE_08,0) . '</th>
							<th>' . '>'. locale_number_format(AVERAGE_INVOICE_VALUE_08,0) . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

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
				locale_number_format($SumInvoiceSum/$SumInvoiceCount,0), 
				locale_number_format($SumInvoiceCount/$NumDaysA,1),
				locale_number_format($SumInvoice01/$SumInvoiceCount*100,1).'%', 
				locale_number_format($SumInvoice02/$SumInvoiceCount*100,1).'%', 
				locale_number_format($SumInvoice03/$SumInvoiceCount*100,1).'%', 
				locale_number_format($SumInvoice04/$SumInvoiceCount*100,1).'%', 
				locale_number_format($SumInvoice05/$SumInvoiceCount*100,1).'%', 
				locale_number_format($SumInvoice06/$SumInvoiceCount*100,1).'%', 
				locale_number_format($SumInvoice07/$SumInvoiceCount*100,1).'%', 
				locale_number_format($SumInvoice08/$SumInvoiceCount*100,1).'%', 
				locale_number_format($SumInvoice09/$SumInvoiceCount*100,1).'%'
				);
		echo '</table>
				</div>';
	}
}

function YearDifferenceSales($typereport, $NumDaysA, $db){

	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));
	$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-365));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1-365));

	$TotalDateA = 0;
	$TotalDateB = 0;

	if ($typereport == "Shop"){
		$SQL = "SELECT debtorno,
					name,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateB . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesB
				FROM debtorsmaster
				WHERE debtorsmaster.typeid = 2
				ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) DESC";
	}else{
		$SQL = "SELECT salesmancode,
					salesmanname,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateB . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesB
				FROM salesman
				WHERE salesman.current = 1
				ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateA . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) DESC";
	}
	
						
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Difference sales for ') . $typereport . " during the last " . $NumDaysA . " days and same period last year".'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . $typereport . '</th>
							<th>' . _('Name') . '</th>
							<th>' . $NumDaysA . _(' Days This Year') . '</th>
							<th>' . $NumDaysA . _(' Days Last Year') . '</th>
							<th>' . _('Trend') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			if ($typereport == "Shop"){
				$Code = $myrow['debtorno'];
				$Name = $myrow['name'];
			}else{
				$Code = $myrow['salesmancode'];
				$Name = $myrow['salesmanname'];
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
					</tr>', 
					$i,
					$Code,
					$Name,
					locale_number_format($myrow['salesA'],0), 
					locale_number_format($myrow['salesB'],0), 
					$trend
					);
			$TotalDateA = $TotalDateA +($myrow['salesA']);
			$TotalDateB = $TotalDateB +($myrow['salesB']);
			$i++;
		}
		$percent = (($TotalDateA)-($TotalDateB))/($TotalDateB) * 100;
		$trend = " ";
		if ($percent > 0){
			$trend = "Improving ". locale_number_format($percent,0) . "%";
		}
		if ($percent < 0){
			$trend = "Degrading ". locale_number_format($percent,0) . "%";
		}
	if ($typereport == "Shop"){
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					"",
					"",
					"TOTAL",
					locale_number_format($TotalDateA,0), 
					locale_number_format($TotalDateB,0), 
					$trend
					);
		}
		echo '</table>
				</div>';
	}
}

function AverageSales($typereport, $NumDaysA, $NumDaysB, $NumDaysC, $NumDaysD, $NumDaysE, $NumDaysF, $NumDaysSort, $Year, $Shop, $db){

	if ($Year == "LastYear"){
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-366));
		$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-366));
		$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB-366));
		$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysC-366));
		$StartDateD = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysD-366));
		$StartDateE = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysE-366));
		$StartDateF = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysF-366));
		$StartDateSort = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysSort-366));
	}else{
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));
		$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB-1));
		$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysC-1));
		$StartDateD = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysD-1));
		$StartDateE = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysE-1));
		$StartDateF = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysF-1));
		$StartDateSort = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysSort-1));
	}

	$TotalDateA = 0;
	$TotalDateB = 0;
	$TotalDateC = 0;
	$TotalDateD = 0;
	$TotalDateE = 0;
	$TotalDateF = 0;
	$TotalForecast = 0;
	
	if ($Shop == "All"){
		$SQLByShop = "";
	}else{
		$SQLByShop = " AND salesorders.debtorno =  '". $Shop . "' ";
	}

	if ($typereport == "Shop"){
		$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					locations.minmonthlysalestarget,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateA . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateB . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesB,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateC . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesC,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateD . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesD,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateE . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesE,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateF . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesF
				FROM debtorsmaster, locations
				WHERE debtorsmaster.debtorno = locations.cashsalecustomer
					AND debtorsmaster.typeid = 2
				ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateSort . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) DESC";
	}else{
		$SQL = "SELECT salesmancode,
					salesmanname,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >  '". $StartDateA . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >  '". $StartDateB . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesB,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >  '". $StartDateC . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesC,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >  '". $StartDateD . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesD,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >  '". $StartDateE . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesE,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >  '". $StartDateF . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesF
				FROM salesman
				WHERE salesman.current = 1
				ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >  '". $StartDateSort . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) DESC";
	}
	
						
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		if ($Year == "LastYear"){
			echo '<p class="page_title_text" align="center"><strong>' . _('LAST YEAR Average Daily sales by ') . $typereport . " during the last " . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . ", ". $NumDaysE . ", ". $NumDaysF . " days. Sorted by " . $NumDaysSort ." days. Trend by " . $NumDaysD . " days.".'</strong></p>';
			$TitleTarget = "";
		}else{
			if ($Shop == "All"){
				echo '<p class="page_title_text" align="center"><strong>' . _('Current Average Daily sales by ') . $typereport . " during the last " . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . ", ". $NumDaysE . ", ". $NumDaysF . " days. Sorted by " . $NumDaysSort ." days. Trend by " . $NumDaysD . " days.".'</strong></p>';
			}else{
				echo '<p class="page_title_text" align="center"><strong>' . _('Current Average Daily sales in ') . $Shop . ' by ' . $typereport . " during the last " . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . ", ". $NumDaysE . ", ". $NumDaysF . " days. Sorted by " . $NumDaysSort ." days. Trend by " . $NumDaysD . " days.".'</strong></p>';
			}
			if($typereport == "Shop"){
				$TitleTarget = "Minimum Target";
			}else{
				$TitleTarget = "";
			}
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . $typereport . '</th>
							<th>' . _('Name') . '</th>
							<th>' . $NumDaysA . _(' days') . '</th>
							<th>' . $NumDaysB . _(' days') . '</th>
							<th>' . $NumDaysC . _(' days') . '</th>
							<th>' . $NumDaysD . _(' days') . '</th>
							<th>' . $NumDaysE . _(' days') . '</th>
							<th>' . $NumDaysF . _(' days') . '</th>
							<th>' . _('Trend') . '</th>
							<th>' . 'Forecast '. $NumDaysC . _(' days') . '</th>
							<th>' . $TitleTarget . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			$target = "";
			if ($typereport == "Shop"){
				$Code = $myrow['debtorno'];
				$Name = $myrow['name'];
				if($Year != "LastYear"){
					$target = locale_number_format($myrow['minmonthlysalestarget']/30*$NumDaysC,0);
				}
			}else{
				$Code = $myrow['salesmancode'];
				$Name = $myrow['salesmanname'];
			}
			
			$dailyA = locale_number_format(($myrow['salesA']/$NumDaysA),0);
			$dailyB = locale_number_format(($myrow['salesB']/$NumDaysB),0);
			$dailyC = locale_number_format(($myrow['salesC']/$NumDaysC),0);
			$dailyD = locale_number_format(($myrow['salesD']/$NumDaysD),0);
			$dailyE = locale_number_format(($myrow['salesE']/$NumDaysE),0);
			$dailyF = locale_number_format(($myrow['salesF']/$NumDaysF),0);
			$percent = (($myrow['salesD']/$NumDaysD)-($myrow['salesC']/$NumDaysC))/($myrow['salesC']/$NumDaysC) * 100;
			$trend = " ";
			if ($percent > IMPROVEMENT_AVERAGE_SALES){
				$trend = "Improving ". locale_number_format($percent,0) . "%";
			}
			if ($percent < -IMPROVEMENT_AVERAGE_SALES){
				$trend = "Degrading ". locale_number_format($percent,0) . "%";
			}
			$forecast = locale_number_format(round($myrow['salesC'], -5),0);
			
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i,
					$Code,
					$Name,
					$dailyA, 
					$dailyB, 
					$dailyC,
					$dailyD,
					$dailyE,
					$dailyF,
					$trend,
					$forecast,
					$target
					);
			$TotalDateA = $TotalDateA +($myrow['salesA']/$NumDaysA);
			$TotalDateB = $TotalDateB +($myrow['salesB']/$NumDaysB);
			$TotalDateC = $TotalDateC +($myrow['salesC']/$NumDaysC);
			$TotalDateD = $TotalDateD +($myrow['salesD']/$NumDaysD);
			$TotalDateE = $TotalDateE +($myrow['salesE']/$NumDaysE);
			$TotalDateF = $TotalDateF +($myrow['salesF']/$NumDaysF);
			$TotalForecast = $TotalForecast + round($myrow['salesC'], -5);
			$i++;
		}
		if ($typereport == "Shop"){
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					"",
					"",
					"TOTAL",
					locale_number_format($TotalDateA,0), 
					locale_number_format($TotalDateB,0), 
					locale_number_format($TotalDateC,0),
					locale_number_format($TotalDateD,0),
					locale_number_format($TotalDateE,0),
					locale_number_format($TotalDateF,0),
					"",
					locale_number_format($TotalForecast,0),
					""
					);
		}
		echo '</table>
				</div>';
	}
}

function SPGPerformanceByShop($Shop, $NumDaysA, $NumDaysB, $NumDaysC, $db){

	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));

	$YesterdayB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1-1));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB-1));

	$YesterdayC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB-1-1));
	$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysC-1));
	
	$SQL = "SELECT salesmancode,
				salesmanname,
				securityroles.secrolename,
				(SELECT COUNT(DISTINCT(salesorders.orddate))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >  '". $StartDateA . "'
						AND salesorders.orddate <= '". $YesterdayA . "'
						AND salesorders.salesperson = salesman.salesmancode) AS daysA,
				(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >  '". $StartDateA . "'
						AND salesorders.orddate <= '". $YesterdayA . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesA,
				(SELECT COUNT(DISTINCT(salesorders.orddate))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >  '". $StartDateB . "'
						AND salesorders.orddate <= '". $YesterdayB . "'
						AND salesorders.salesperson = salesman.salesmancode) AS daysB,
				(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >  '". $StartDateB . "'
						AND salesorders.orddate <= '". $YesterdayB . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesB,
				(SELECT COUNT(DISTINCT(salesorders.orddate))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >  '". $StartDateC . "'
						AND salesorders.orddate <= '". $YesterdayC . "'
						AND salesorders.salesperson = salesman.salesmancode) AS daysC,
				(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >  '". $StartDateC . "'
						AND salesorders.orddate <= '". $YesterdayC . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesC
			FROM salesman, www_users, securityroles
			WHERE www_users.salesman = salesman.salesmancode
				AND www_users.fullaccess = securityroles.secroleid
				AND www_users.customerid = '" . $Shop . "'
				AND ((SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >  '". $StartDateA . "'
						AND salesorders.orddate <= '". $YesterdayA . "'
						AND salesorders.salesperson = salesman.salesmancode) > 0)
			ORDER BY salesman.salesmancode";
	
						
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('SPG Performance in ') . $Shop . " during the last " . $NumDaysA . " days and ". ($NumDaysB - $NumDaysA) . " previous days". '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th colspan="3">' . _('SPG') . '</th>
							<th colspan="2">' . $NumDaysA . ' last days' . '</th>
							<th colspan="2">' . $NumDaysA . '-' . $NumDaysB . ' previous days' . '</th>
							<th colspan="2">' . $NumDaysB . '-' . $NumDaysC . ' previous days' . '</th>
						</tr>';
		echo $TableHeader;
		$TableHeader = '<tr>
							<th>' . _('Code') . '</th>
							<th>' . _('Name') . '</th>
							<th>' . _('Role') . '</th>
							<th>' . _('Days') . '</th>
							<th>' . _('Avg Daily Sales') . '</th>
							<th>' . _('Days') . '</th>
							<th>' . _('Avg Daily Sales') . '</th>
							<th>' . _('Days') . '</th>
							<th>' . _('Avg Daily Sales') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$myrow['salesmancode'],
					$myrow['salesmanname'],
					$myrow['secrolename'],
					locale_number_format_zero_blank($myrow['daysA'],0),
					locale_number_format_zero_blank($myrow['salesA']/$myrow['daysA'],0),
					locale_number_format_zero_blank($myrow['daysB'],0),
					locale_number_format_zero_blank($myrow['salesB']/$myrow['daysB'],0),
					locale_number_format_zero_blank($myrow['daysC'],0),
					locale_number_format_zero_blank($myrow['salesC']/$myrow['daysC'],0)
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
					SUM(klpaidcash) AS cashshop, 
					SUM(klpaidcreditcard) AS creditshop, 
					SUM(klreturnedgoods) AS returnedgoodsshop,
					SUM(klvouchers) AS vouchersshop,
					SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers) AS totalshop
			FROM salesorders, debtorsmaster
			WHERE salesorders.debtorno = debtorsmaster.debtorno
				AND orddate >= '". $StartDate. "'
				AND salesorders.debtorno LIKE 'RETAIL%'
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
			FROM salesorders, salesman
			WHERE salesorders.salesperson = salesman.salesmancode
				AND orddate >= '". $StartDate. "'
				AND salesorders.debtorno LIKE 'RETAIL%'
			GROUP BY salesorders.salesperson
			ORDER BY salesorders.salesperson";
	}
	
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Distribution Cash / Credit Card during the last ') . $maxdays . _(' days by ') .$typereport .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . $typereport . '</th>
							<th>' . _('Name') . '</th>
							<th>' . _('% Cash') . '</th>
							<th>' . _('% Credit') . '</th>
							<th>' . _('% Returns') . '</th>
							<th>' . _('% Vouchers') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['totalshop'] != 0){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				
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

	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Authorized Status for '). $currency . ' accounts'  . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . _('PC Tab Code') . '</th>
							<th>' . _('Amount') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$total = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
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


?>
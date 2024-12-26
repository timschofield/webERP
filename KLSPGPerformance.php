<?php
define("VERSIONFILE", "1.02");

include ('includes/session.php');
$Title = _('Kapal-Laut SPG Performance Report '. VERSIONFILE);
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin){
}

if ($KL_OperationalManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager){

	SPGPerformanceMonthy();
	$NumberOfTestExecuted++;
}

if ($KL_SalesDirector
	OR $KL_ShopManager){

	SPGPerformanceWeekly();
	$NumberOfTestExecuted++;
}
	
if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager){

	AverageSales("SPG", 30, 15, 10, 7, 5, 1, 7, "CurrentYear", "All");
	$NumberOfTestExecuted++;
}
	
if ($KL_OperationalManager 
	OR $KL_ShopManager){

	SPGPerformanceAllShops(15, 30, 45);
	$NumberOfTestExecuted++;
}
	
if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector){
		
	HourlySales(15,$RootPath);
	$NumberOfTestExecuted++;
	HourlySales(30,$RootPath);
	$NumberOfTestExecuted++;
	
	DaysOfWeekSales(180,$RootPath);
	$NumberOfTestExecuted++;

}

if ($KL_SystemAdmin){	

	RetailTypePayments("SPG",90);
	$NumberOfTestExecuted++;
}

prnMsg("Performed ". $NumberOfTestExecuted . " SPG Performance Report",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include ('includes/footer.php');

/********************************************************************************************
FUNCTIONS ONLY USED IN SPG PERFORMANCE BOARD
*********************************************************************************************/

function RetailTypePayments($typereport, $maxdays){
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
	
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Distribution Cash / Credit Card during the last ') . $maxdays . _(' days by ') .$typereport .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . $typereport . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('% Cash') . '</th>
						<th class="SortedColumn">' . _('% Credit') . '</th>
						<th class="SortedColumn">' . _('% Returns') . '</th>
						<th class="SortedColumn">' . _('% Vouchers') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['totalshop'] != 0){
				$Percentcash = locale_number_format(($MyRow['cashshop']/$MyRow['totalshop'])*100,1);
				$Percentcredit = locale_number_format(($MyRow['creditshop']/$MyRow['totalshop'])*100,1);
				$Percentreturns = locale_number_format(($MyRow['returnedgoodsshop']/$MyRow['totalshop'])*100,1);
				$Percentvouchers = locale_number_format(($MyRow['vouchersshop']/$MyRow['totalshop'])*100,1);
				
				$totalcash = $totalcash + $MyRow['cashshop'];
				$totalcredit = $totalcredit + $MyRow['creditshop'];
				$totalreturned = $totalreturned + $MyRow['returnedgoodsshop'];
				$totalvouchers = $totalvouchers + $MyRow['vouchersshop'];
				$total = $total + $MyRow['totalshop'];
				
				printf('<tr class="striped_row">
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$MyRow['reportunit'],
						$MyRow['reportname'],
						$Percentcash, 
						$Percentcredit, 
						$Percentreturns, 
						$Percentvouchers
						);
				$i++;
			}
		}

		$Percentcash = locale_number_format(($totalcash/$total)*100,1);
		$Percentcredit = locale_number_format(($totalcredit/$total)*100,1);
		$Percentreturns = locale_number_format(($totalreturned/$total)*100,1);
		$Percentvouchers = locale_number_format(($totalvouchers/$total)*100,1);
		
		printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				"",
				"Average",
				$Percentcash, 
				$Percentcredit, 
				$Percentreturns, 
				$Percentvouchers
				);
		
		echo '</tbody>
				</table>
				</div>';
	}
}

function SPGPerformanceAllShops($NumDaysA, $NumDaysB, $NumDaysC){
	$SQL = "SELECT locations.cashsalecustomer
			FROM locations
			WHERE locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
			ORDER BY locations.zone,
				locations.loccode";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		SPGPerformanceByShop($MyRow['cashsalecustomer'], $NumDaysA, $NumDaysB, $NumDaysC);
	}
}

function SPGPerformanceByShop($Shop, $NumDaysA, $NumDaysB, $NumDaysC){

	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA));

	$YesterdayB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB));

	$YesterdayC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB-1));
	$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysC));

	$SQL = "SELECT salesmancode,
				salesmanname,
				securityroles.secrolename,
				(SELECT COUNT(DISTINCT(salesorders.orddate))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $YesterdayA . "'
						AND salesorders.salesperson = salesman.salesmancode) AS daysA,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $YesterdayA . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesA,
				(SELECT COUNT(DISTINCT(salesorders.orddate))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >= '". $StartDateB . "'
						AND salesorders.orddate <= '". $YesterdayB . "'
						AND salesorders.salesperson = salesman.salesmancode) AS daysB,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >= '". $StartDateB . "'
						AND salesorders.orddate <= '". $YesterdayB . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesB,
				(SELECT COUNT(DISTINCT(salesorders.orddate))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >= '". $StartDateC . "'
						AND salesorders.orddate <= '". $YesterdayC . "'
						AND salesorders.salesperson = salesman.salesmancode) AS daysC,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >= '". $StartDateC . "'
						AND salesorders.orddate <= '". $YesterdayC . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesC
			FROM salesman, www_users, securityroles
			WHERE www_users.salesman = salesman.salesmancode
				AND www_users.fullaccess = securityroles.secroleid
				AND www_users.customerid = '" . $Shop . "'
				AND ((SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.debtorno = '" . $Shop . "'
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $YesterdayA . "'
						AND salesorders.salesperson = salesman.salesmancode) > 0)
			ORDER BY salesman.salesmancode";
				
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('SPG Performance in ') . $Shop . " during the last " . $NumDaysA . " days and ". ($NumDaysB - $NumDaysA) . " previous days". '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="3">' . _('SPG') . '</th>
						<th colspan="2">' . $NumDaysA . ' last days' . '</th>
						<th colspan="2">' . $NumDaysA . '-' . $NumDaysB . ' previous days' . '</th>
						<th colspan="2">' . $NumDaysB . '-' . $NumDaysC . ' previous days' . '</th>
					</tr>
					<tr>
						<th>' . _('Code') . '</th>
						<th>' . _('Name') . '</th>
						<th>' . _('Role') . '</th>
						<th class="SortedColumn">' . _('Days') . '</th>
						<th class="SortedColumn">' . _('Avg Daily Sales') . '</th>
						<th class="SortedColumn">' . _('Days') . '</th>
						<th class="SortedColumn">' . _('Avg Daily Sales') . '</th>
						<th class="SortedColumn">' . _('Days') . '</th>
						<th class="SortedColumn">' . _('Avg Daily Sales') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			$DailyA = ($MyRow['daysA'] != 0) ? ($MyRow['salesA']/$MyRow['daysA']) : 0;
			$DailyB = ($MyRow['daysB'] != 0) ? ($MyRow['salesB']/$MyRow['daysB']) : 0;
			$DailyC = ($MyRow['daysC'] != 0) ? ($MyRow['salesC']/$MyRow['daysC']) : 0;
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$MyRow['salesmancode'],
					$MyRow['salesmanname'],
					$MyRow['secrolename'],
					locale_number_format_zero_blank($MyRow['daysA'],0),
					locale_number_format_zero_blank($DailyA,0),
					locale_number_format_zero_blank($MyRow['daysB'],0),
					locale_number_format_zero_blank($DailyB,0),
					locale_number_format_zero_blank($MyRow['daysC'],0),
					locale_number_format_zero_blank($DailyC,0)
					);
		}
		echo '</tbody>
				</table>
				</div>';
	}
}

function SPGPerformanceMonthy(){

	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$Last30A      = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-30));
	$Last60A     = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-60));
	$StartMonthA = substr($YesterdayA,0,7). '-01';	

	$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-31));
	$Last30B      = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-60));
	$Last60B     = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-90));
	$StartMonthB = substr($YesterdayB,0,7). '-01';	

	$YesterdayC  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-61));
	$Last30C      = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-90));
	$Last60C     = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-120));
	$StartMonthC = substr($YesterdayC,0,7). '-01';	
	
	$YesterdayD  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-91));
	$Last30D      = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-120));
	$Last60D     = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-150));
	$StartMonthD = substr($YesterdayD,0,7). '-01';	

	$SQL = "SELECT locations.zone,
					locations.loccode,
					salesman.salesmancode,
					salesman.salesmanname,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last30A . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days30A,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30A . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30A,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last60A . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days60A,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last60A . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last60A,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $StartMonthA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS mtdA,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last30B . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days30B,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30B . "'
							AND salesorders.orddate <= '" . $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30B,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last60B . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days60B,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last60B . "'
							AND salesorders.orddate <= '" . $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last60B,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $StartMonthB . "'
							AND salesorders.orddate <= '" . $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS mtdB,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last30C . "'
							AND salesorders.orddate <= '". $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days30C,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30C . "'
							AND salesorders.orddate <= '" . $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30C,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last60C . "'
							AND salesorders.orddate <= '". $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days60C,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last60C . "'
							AND salesorders.orddate <= '" . $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last60C,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $StartMonthC . "'
							AND salesorders.orddate <= '" . $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS mtdC,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last30D . "'
							AND salesorders.orddate <= '". $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days30D,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30D . "'
							AND salesorders.orddate <= '" . $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30D,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last60D . "'
							AND salesorders.orddate <= '". $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days60D,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last60D . "'
							AND salesorders.orddate <= '" . $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last60D,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $StartMonthD . "'
							AND salesorders.orddate <= '" . $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS mtdD
			FROM locations, www_users, salesman
			WHERE locations.loccode = www_users.defaultlocation 
				AND www_users.salesman = salesman.salesmancode
				AND ((SELECT SUM(linenetprice)
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.completed = 1
									AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last60A . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
									AND salesorders.salesperson = salesman.salesmancode) > 0)
			ORDER BY locations.zone,
				locations.loccode,
				salesman.salesmancode";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "SPG Monthly performance". '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="4">' . 'SPG' . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayD) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayC) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayB) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayA) . '</th>
					</tr>
					<tr>
						<th>' . 'Zone' . '</th>
						<th>' . 'Shop' . '</th>
						<th>' . 'SPG' . '</th>
						<th>' . 'Name' . '</th>
						<th>' . 'MTD' . '</th>
						<th>' . 'Last 30d' . '</th>
						<th>' . 'last 60d' . '</th>
						<th>' . 'MTD' . '</th>
						<th>' . 'Last 30d' . '</th>
						<th>' . 'last 60d' . '</th>
						<th>' . 'MTD' . '</th>
						<th>' . 'Last 30d' . '</th>
						<th>' . 'last 60d' . '</th>
						<th>' . 'MTD' . '</th>
						<th>' . 'Last 30d' . '</th>
						<th>' . 'last 60d' . '</th>
					</tr>
				</thead>
				<tbody>';
		$k = 0; //row colour counter
		$lastshop = "";
		while ($MyRow = DB_fetch_array($Result)) {
			if ($lastshop != $MyRow['loccode']){
				echo $TableHeader;
			}
			$Last30D = ($MyRow['days30D'] != 0) ? ($MyRow['last30D']/$MyRow['days30D']) : 0;
			$Last60D = ($MyRow['days60D'] != 0) ? ($MyRow['last60D']/$MyRow['days60D']) : 0;
			$Last30C = ($MyRow['days30C'] != 0) ? ($MyRow['last30C']/$MyRow['days30C']) : 0;
			$Last60C = ($MyRow['days60C'] != 0) ? ($MyRow['last60C']/$MyRow['days60C']) : 0;
			$Last30B = ($MyRow['days30B'] != 0) ? ($MyRow['last30B']/$MyRow['days30B']) : 0;
			$Last60B = ($MyRow['days60B'] != 0) ? ($MyRow['last60B']/$MyRow['days60B']) : 0;
			$Last30A = ($MyRow['days30A'] != 0) ? ($MyRow['last30A']/$MyRow['days30A']) : 0;
			$Last60A = ($MyRow['days60A'] != 0) ? ($MyRow['last60A']/$MyRow['days60A']) : 0;
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
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
					<td class="number">%s</td>
					</tr>', 
					$MyRow['zone'],
					$MyRow['loccode'],
					$MyRow['salesmancode'],
					$MyRow['salesmanname'],
					locale_number_format_zero_blank($MyRow['mtdD'],0),
					locale_number_format_zero_blank($Last30D,0),
					locale_number_format_zero_blank($Last60D,0),
					locale_number_format_zero_blank($MyRow['mtdC'],0),
					locale_number_format_zero_blank($Last30C,0),
					locale_number_format_zero_blank($Last60C,0),
					locale_number_format_zero_blank($MyRow['mtdB'],0),
					locale_number_format_zero_blank($Last30B,0),
					locale_number_format_zero_blank($Last60B,0),
					locale_number_format_zero_blank($MyRow['mtdA'],0),
					locale_number_format_zero_blank($Last30A,0),
					locale_number_format_zero_blank($Last60A,0)
					);
			$lastshop = $MyRow['loccode'];
		}
		echo '</tbody>
				</table>
				</div>';
	}
}

function SPGPerformanceWeekly(){

	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$Last7A      = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-7));
	$Last30A     = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-30));
	$StartMonthA = substr($YesterdayA,0,7). '-01';	

	$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-8));
	$Last7B      = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-14));
	$Last30B     = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-37));
	$StartMonthB = substr($YesterdayB,0,7). '-01';	

	$YesterdayC  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-15));
	$Last7C      = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-21));
	$Last30C     = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-44));
	$StartMonthC = substr($YesterdayC,0,7). '-01';	
	
	$YesterdayD  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-22));
	$Last7D      = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-28));
	$Last30D     = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-51));
	$StartMonthD = substr($YesterdayD,0,7). '-01';	

	$SQL = "SELECT locations.zone,
					locations.loccode,
					salesman.salesmancode,
					salesman.salesmanname,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last7A . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days7A,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last7A . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last7A,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last30A . "'
							AND salesorders.orddate <= '". $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days30A,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30A . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30A,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $StartMonthA . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS mtdA,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last7B . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days7B,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last7B . "'
							AND salesorders.orddate <= '" . $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last7B,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last30B . "'
							AND salesorders.orddate <= '". $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days30B,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30B . "'
							AND salesorders.orddate <= '" . $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30B,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $StartMonthB . "'
							AND salesorders.orddate <= '" . $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS mtdB,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last7C . "'
							AND salesorders.orddate <= '". $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days7C,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last7C . "'
							AND salesorders.orddate <= '" . $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last7C,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last30C . "'
							AND salesorders.orddate <= '". $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days30C,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30C . "'
							AND salesorders.orddate <= '" . $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30C,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $StartMonthC . "'
							AND salesorders.orddate <= '" . $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS mtdC,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last7D . "'
							AND salesorders.orddate <= '". $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days7D,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last7D . "'
							AND salesorders.orddate <= '" . $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last7D,
					(SELECT COUNT(DISTINCT(salesorders.orddate))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '". $Last30D . "'
							AND salesorders.orddate <= '". $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS days30D,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30D . "'
							AND salesorders.orddate <= '" . $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30D,
					(SELECT SUM(linenetprice)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $StartMonthD . "'
							AND salesorders.orddate <= '" . $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS mtdD
			FROM locations, www_users, salesman
			WHERE locations.loccode = www_users.defaultlocation 
				AND www_users.salesman = salesman.salesmancode
				AND ((SELECT SUM(linenetprice)
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.completed = 1
									AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30A . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
									AND salesorders.salesperson = salesman.salesmancode) > 0)
			ORDER BY locations.zone,
				locations.loccode,
				salesman.salesmancode";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "SPG Weekly performance". '</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="4">' . 'SPG' . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayD) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayC) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayB) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayA) . '</th>
					</tr>
					<tr>
						<th>' . 'Zone' . '</th>
						<th>' . 'Shop' . '</th>
						<th>' . 'SPG' . '</th>
						<th>' . 'Name' . '</th>
						<th>' . 'MTD' . '</th>
						<th>' . 'Last 7d' . '</th>
						<th>' . 'last 30d' . '</th>
						<th>' . 'MTD' . '</th>
						<th>' . 'Last 7d' . '</th>
						<th>' . 'last 30d' . '</th>
						<th>' . 'MTD' . '</th>
						<th>' . 'Last 7d' . '</th>
						<th>' . 'last 30d' . '</th>
						<th>' . 'MTD' . '</th>
						<th>' . 'Last 7d' . '</th>
						<th>' . 'last 30d' . '</th>
					</tr>
				</thead>
				<tbody>';
		$k = 0; //row colour counter
		$lastshop = "";
		while ($MyRow = DB_fetch_array($Result)) {
			if ($lastshop != $MyRow['loccode']){
				echo $TableHeader;
			}
			$Last30D = ($MyRow['days30D'] != 0) ? ($MyRow['last30D']/$MyRow['days30D']) : 0;
			$Last7D = ($MyRow['days7D'] != 0) ? ($MyRow['last7D']/$MyRow['days7D']) : 0;
			$Last30C = ($MyRow['days30C'] != 0) ? ($MyRow['last30C']/$MyRow['days30C']) : 0;
			$Last7C = ($MyRow['days7C'] != 0) ? ($MyRow['last7C']/$MyRow['days7C']) : 0;
			$Last30B = ($MyRow['days30B'] != 0) ? ($MyRow['last30B']/$MyRow['days30B']) : 0;
			$Last7B = ($MyRow['days7B'] != 0) ? ($MyRow['last7B']/$MyRow['days7B']) : 0;
			$Last30A = ($MyRow['days30A'] != 0) ? ($MyRow['last30A']/$MyRow['days30A']) : 0;
			$Last7A = ($MyRow['days7A'] != 0) ? ($MyRow['last7A']/$MyRow['days7A']) : 0;
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
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
					<td class="number">%s</td>
					</tr>', 
					$MyRow['zone'],
					$MyRow['loccode'],
					$MyRow['salesmancode'],
					$MyRow['salesmanname'],
					locale_number_format_zero_blank($MyRow['mtdD'],0),
					locale_number_format_zero_blank($Last7D,0),
					locale_number_format_zero_blank($Last30D,0),
					locale_number_format_zero_blank($MyRow['mtdC'],0),
					locale_number_format_zero_blank($Last7C,0),
					locale_number_format_zero_blank($Last30C,0),
					locale_number_format_zero_blank($MyRow['mtdB'],0),
					locale_number_format_zero_blank($Last7B,0),
					locale_number_format_zero_blank($Last30B,0),
					locale_number_format_zero_blank($MyRow['mtdA'],0),
					locale_number_format_zero_blank($Last7A,0),
					locale_number_format_zero_blank($Last30A,0)
					);
			$lastshop = $MyRow['loccode'];
		}
		echo '</tbody>
				</table>
				</div>';
	}
}

function HourlySales($numDays, $RootPath){

	$Today = date('Y-m-d');
	$Yesterday = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

	$SQL = "SELECT debtorsmaster.debtorno,
				locations.zone,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime < '08:00:00') AS sales07,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '08:00:00'
					AND salesorders.ordtime < '09:00:00') AS sales08,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '09:00:00'
					AND salesorders.ordtime < '10:00:00') AS sales09,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '10:00:00'
					AND salesorders.ordtime <  '11:00:00') AS sales10,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '11:00:00'
					AND salesorders.ordtime <  '12:00:00') AS sales11,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '12:00:00'
					AND salesorders.ordtime <  '13:00:00') AS sales12,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '13:00:00'
					AND salesorders.ordtime <  '14:00:00') AS sales13,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '14:00:00'
					AND salesorders.ordtime <  '15:00:00') AS sales14,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '15:00:00'
					AND salesorders.ordtime <  '16:00:00') AS sales15,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '16:00:00'
					AND salesorders.ordtime <  '17:00:00') AS sales16,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '17:00:00'
					AND salesorders.ordtime <  '18:00:00') AS sales17,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '18:00:00'
					AND salesorders.ordtime <  '19:00:00') AS sales18,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '19:00:00'
					AND salesorders.ordtime <  '20:00:00') AS sales19,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '20:00:00'
					AND salesorders.ordtime <  '21:00:00') AS sales20,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '21:00:00'
					AND salesorders.ordtime <  '22:00:00') AS sales21,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '22:00:00'
					AND salesorders.ordtime <  '23:00:00') AS sales22,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '23:00:00'
					AND salesorders.ordtime <  '24:00:00') AS sales23,
				(SELECT MIN(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."') AS firstsale,
				(SELECT MAX(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."') AS lastsale					
			FROM debtorsmaster, custbranch, locations
			WHERE debtorsmaster.debtorno = custbranch.debtorno
				AND custbranch.defaultlocation = locations.loccode
				AND locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . "
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			ORDER BY locations.zone, 
				debtorsmaster.debtorno";

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$GrandTotal = 0;
	
	if (DB_num_rows($Result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		$Total07 = 0;
		$Total08 = 0;
		$Total09 = 0;
		$Total10 = 0;
		$Total11 = 0;
		$Total12 = 0;
		$Total13 = 0;
		$Total14 = 0;
		$Total15 = 0;
		$Total16 = 0;
		$Total17 = 0;
		$Total18 = 0;
		$Total19 = 0;
		$Total20 = 0;
		$Total21 = 0;
		$Total22 = 0;
		$Total23 = 0;
		$ZoneName = '';
		
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' .'Hourly sales and value for the last ' . $numDays . ' days</strong></p>';
				echo '<div>';
				echo '<table class="selection">
						<thead>';
			}
			if (($ShowHeader) OR ($ZoneName != $MyRow['zone'])){
				$TableHeader = '<tr>
									<th class="SortedColumn">' . _('Zone') . '</th>
									<th class="SortedColumn">' . _('Shop') . '</th>
									<th class="SortedColumn">' . _('Type') . '</th>
									<th class="SortedColumn">' . _('First Sale') . '</th>
									<th class="SortedColumn">' . _('00-08') . '</th>
									<th class="SortedColumn">' . _('08-09') . '</th>
									<th class="SortedColumn">' . _('09-10') . '</th>
									<th class="SortedColumn">' . _('10-11') . '</th>
									<th class="SortedColumn">' . _('11-12') . '</th>
									<th class="SortedColumn">' . _('12-13') . '</th>
									<th class="SortedColumn">' . _('13-14') . '</th>
									<th class="SortedColumn">' . _('14-15') . '</th>
									<th class="SortedColumn">' . _('15-16') . '</th>
									<th class="SortedColumn">' . _('16-17') . '</th>
									<th class="SortedColumn">' . _('17-18') . '</th>
									<th class="SortedColumn">' . _('18-19') . '</th>
									<th class="SortedColumn">' . _('19-20') . '</th>
									<th class="SortedColumn">' . _('20-21') . '</th>
									<th class="SortedColumn">' . _('21-22') . '</th>
									<th class="SortedColumn">' . _('22-23') . '</th>
									<th class="SortedColumn">' . _('23-24') . '</th>
									<th class="SortedColumn">' . _('Last Sale') . '</th>
								</tr>
							</thead>
							<tbody>';
				echo $TableHeader;
				$ShowHeader = FALSE;
			}
			$TotalSales = $MyRow['sales07'] +
						$MyRow['sales08'] +
						$MyRow['sales09'] +
						$MyRow['sales10'] +
						$MyRow['sales11'] +
						$MyRow['sales12'] +
						$MyRow['sales13'] +
						$MyRow['sales14'] +
						$MyRow['sales15'] +
						$MyRow['sales16'] +
						$MyRow['sales17'] +
						$MyRow['sales18'] +
						$MyRow['sales19'] +
						$MyRow['sales20'] +
						$MyRow['sales21'] +
						$MyRow['sales22'] +
						$MyRow['sales23'] ;
			$GrandTotal += $TotalSales;
			$ZoneName = $MyRow['zone'];
			
			if ($MyRow['sales07'] != 0){
				$Sales07 = locale_number_format_zero_blank($MyRow['sales07']/$TotalSales*100,0).'%';
				$Total07 += $MyRow['sales07'];
			}else{
				$Sales07 = '';
			}		
			if ($MyRow['sales08'] != 0){
				$Sales08 = locale_number_format_zero_blank($MyRow['sales08']/$TotalSales*100,0).'%';
				$Total08 += $MyRow['sales08'];
			}else{
				$Sales08 = '';
			}		
			if ($MyRow['sales09'] != 0){
				$Sales09 = locale_number_format_zero_blank($MyRow['sales09']/$TotalSales*100,0).'%';
				$Total09 += $MyRow['sales09'];
			}else{
				$Sales09 = '';
			}		
			if ($MyRow['sales10'] != 0){
				$Sales10 = locale_number_format_zero_blank($MyRow['sales10']/$TotalSales*100,0).'%';
				$Total10 += $MyRow['sales10'];
			}else{
				$Sales10 = '';
			}		
			if ($MyRow['sales11'] != 0){
				$Sales11 = locale_number_format_zero_blank($MyRow['sales11']/$TotalSales*100,0).'%';
				$Total11 += $MyRow['sales11'];
			}else{
				$Sales11 = '';
			}		
			if ($MyRow['sales12'] != 0){
				$Sales12 = locale_number_format_zero_blank($MyRow['sales12']/$TotalSales*100,0).'%';
				$Total12 += $MyRow['sales12'];
			}else{
				$Sales12 = '';
			}		
			if ($MyRow['sales13'] != 0){
				$Sales13 = locale_number_format_zero_blank($MyRow['sales13']/$TotalSales*100,0).'%';
				$Total13 += $MyRow['sales13'];
			}else{
				$Sales13 = '';
			}		
			if ($MyRow['sales14'] != 0){
				$Sales14 = locale_number_format_zero_blank($MyRow['sales14']/$TotalSales*100,0).'%';
				$Total14 += $MyRow['sales14'];
			}else{
				$Sales14 = '';
			}		
			if ($MyRow['sales15'] != 0){
				$Sales15 = locale_number_format_zero_blank($MyRow['sales15']/$TotalSales*100,0).'%';
				$Total15 += $MyRow['sales15'];
			}else{
				$Sales15 = '';
			}		
			if ($MyRow['sales16'] != 0){
				$Sales16 = locale_number_format_zero_blank($MyRow['sales16']/$TotalSales*100,0).'%';
				$Total16 += $MyRow['sales16'];
			}else{
				$Sales16 = '';
			}		
			if ($MyRow['sales17'] != 0){
				$Sales17 = locale_number_format_zero_blank($MyRow['sales17']/$TotalSales*100,0).'%';
				$Total17 += $MyRow['sales17'];
			}else{
				$Sales17 = '';
			}		
			if ($MyRow['sales18'] != 0){
				$Sales18 = locale_number_format_zero_blank($MyRow['sales18']/$TotalSales*100,0).'%';
				$Total18 += $MyRow['sales18'];
			}else{
				$Sales18 = '';
			}		
			if ($MyRow['sales19'] != 0){
				$Sales19 = locale_number_format_zero_blank($MyRow['sales19']/$TotalSales*100,0).'%';
				$Total19 += $MyRow['sales19'];
			}else{
				$Sales19 = '';
			}		
			if ($MyRow['sales20'] != 0){
				$Sales20 = locale_number_format_zero_blank($MyRow['sales20']/$TotalSales*100,0).'%';
				$Total20 += $MyRow['sales20'];
			}else{
				$Sales20 = '';
			}		
			if ($MyRow['sales21'] != 0){
				$Sales21 = locale_number_format_zero_blank($MyRow['sales21']/$TotalSales*100,0).'%';
				$Total21 += $MyRow['sales21'];
			}else{
				$Sales21 = '';
			}		
			if ($MyRow['sales22'] != 0){
				$Sales22 = locale_number_format_zero_blank($MyRow['sales22']/$TotalSales*100,0).'%';
				$Total22 += $MyRow['sales22'];
			}else{
				$Sales22 = '';
			}		
			if ($MyRow['sales23'] != 0){
				$Sales23 = locale_number_format_zero_blank($MyRow['sales23']/$TotalSales*100,0).'%';
				$Total23 += $MyRow['sales23'];
			}else{
				$Sales23 = '';
			}		

			$k = StartEvenOrOddRow($k);
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$MyRow['zone'],
					$MyRow['debtorno'],
					'Sales',
					$MyRow['firstsale'],
					$Sales07,
					$Sales08,
					$Sales09,
					$Sales10,
					$Sales11,
					$Sales12,
					$Sales13,
					$Sales14,
					$Sales15,
					$Sales16,
					$Sales17,
					$Sales18,
					$Sales19,
					$Sales20,
					$Sales21,
					$Sales22,
					$Sales23,
					$MyRow['lastsale']
					);

			$k = StartSameColourRow($k);
			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'',
					'',
					'Value',
					'',
					locale_number_format_zero_blank($MyRow['sales07']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales08']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales09']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales10']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales11']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales12']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales13']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales14']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales15']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales16']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales17']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales18']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales19']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales20']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales21']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales22']/$numDays,0),
					locale_number_format_zero_blank($MyRow['sales23']/$numDays,0),
					locale_number_format_zero_blank($TotalSales/$numDays,0)
					);
					
					$i++;
		}
		$k = StartEvenOrOddRow($k);
		printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
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
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>', 
				'TOTALS',
				'',
				'',
				'',
				locale_number_format_zero_blank($Total07/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total08/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total09/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total10/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total11/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total12/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total13/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total14/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total15/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total16/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total17/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total18/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total19/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total20/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total21/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total22/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank($Total23/$GrandTotal*100,0).'%',
				''
				);

		$k = StartEvenOrOddRow($k);
		printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
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
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>', 
				'CUMULATIVE',
				'',
				'',
				'',
				locale_number_format_zero_blank($Total07/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21+$Total22)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21+$Total22+$Total23)/$GrandTotal*100,0).'%',
				''
				);
				
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function DaysOfWeekSales($numDays, $RootPath){
	$Today = date('Y-m-d');
	$Yesterday = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

	// get the total sales of the period from POS sales
	$SQL = "SELECT SUM(klpaidcash + klpaidcreditcard + klreturnedgoods + klvouchers) AS TotalSales
			FROM salesorders
			WHERE orddate >= '" . $InitialDate . "'
				AND orddate <='" . $Yesterday . "'";
	$Result = DB_query($SQL);
	if ($MyRow = DB_fetch_array($Result)){
		$TotalSales = $MyRow['TotalSales'];
	}else{
		return;
	}

	$SQL = "SELECT DAYOFWEEK(orddate) AS WeekDay,
				SUM(klpaidcash + klpaidcreditcard + klreturnedgoods + klvouchers) AS WeekDaySales
			FROM salesorders
			WHERE orddate >= '" . $InitialDate . "'
				AND orddate <='" . $Yesterday . "' 
			GROUP BY DAYOFWEEK(orddate)
			ORDER BY WeekDaySales DESC";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Distribution of retail sales by week days for the last ') . $numDays . _(' days') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Day') . '</th>
						<th class="SortedColumn">' . _('% Sales') . '</th>
						<th class="SortedColumn">' . _('Avg Distance') . '</th>
					</tr>
				</thead>
				<tbody>';
		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			printf('<tr class="striped_row">
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					GetDayNameFromWeekDay($MyRow['WeekDay']),
					locale_number_format(($MyRow['WeekDaySales']/$TotalSales)*100,1) . '%', 
					locale_number_format((($MyRow['WeekDaySales']/$TotalSales/(1/7))-1)*100,1) . '%' 
					);
		}
	}
	echo '</tbody>
			</table>
			</div>';
}

?>
<?php
define("VERSIONFILE", "1.02");

include ('includes/session.php');
$Title = _('Kapal-Laut SPG Performance Report '. VERSIONFILE);
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

/* ASSIGN users to groups */
include ('includes/KLRoles.php');

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
time_finish($begintime);

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

function SPGPerformanceAllShops($NumDaysA, $NumDaysB, $NumDaysC){
	$SQL = "SELECT locations.cashsalecustomer
			FROM locations
			WHERE locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
			ORDER BY locations.zone,
				locations.loccode";
	$result = DB_query($SQL);
	while ($myrow = DB_fetch_array($result)) {
		SPGPerformanceByShop($myrow['cashsalecustomer'], $NumDaysA, $NumDaysB, $NumDaysC);
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
				
	$result = DB_query($SQL);
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
			$k = StartEvenOrOddRow($k);
			
			$DailyA = ($myrow['daysA'] != 0) ? ($myrow['salesA']/$myrow['daysA']) : 0;
			$DailyB = ($myrow['daysB'] != 0) ? ($myrow['salesB']/$myrow['daysB']) : 0;
			$DailyC = ($myrow['daysC'] != 0) ? ($myrow['salesC']/$myrow['daysC']) : 0;
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
					locale_number_format_zero_blank($DailyA,0),
					locale_number_format_zero_blank($myrow['daysB'],0),
					locale_number_format_zero_blank($DailyB,0),
					locale_number_format_zero_blank($myrow['daysC'],0),
					locale_number_format_zero_blank($DailyC,0)
					);
		}
		echo '</table>
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

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "SPG Monthly performance". '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th colspan="4">' . 'SPG' . '</th>
							<th colspan="3">' . ConvertSQLDate($YesterdayD) . '</th>
							<th colspan="3">' . ConvertSQLDate($YesterdayC) . '</th>
							<th colspan="3">' . ConvertSQLDate($YesterdayB) . '</th>
							<th colspan="3">' . ConvertSQLDate($YesterdayA) . '</th>
						</tr>';
		echo $TableHeader;
		
		$TableHeader = '<tr>
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
						</tr>';
		$k = 0; //row colour counter
		$lastshop = "";
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($lastshop != $myrow['loccode']){
				echo $TableHeader;
			}
			$Last30D = ($myrow['days30D'] != 0) ? ($myrow['last30D']/$myrow['days30D']) : 0;
			$Last60D = ($myrow['days60D'] != 0) ? ($myrow['last60D']/$myrow['days60D']) : 0;
			$Last30C = ($myrow['days30C'] != 0) ? ($myrow['last30C']/$myrow['days30C']) : 0;
			$Last60C = ($myrow['days60C'] != 0) ? ($myrow['last60C']/$myrow['days60C']) : 0;
			$Last30B = ($myrow['days30B'] != 0) ? ($myrow['last30B']/$myrow['days30B']) : 0;
			$Last60B = ($myrow['days60B'] != 0) ? ($myrow['last60B']/$myrow['days60B']) : 0;
			$Last30A = ($myrow['days30A'] != 0) ? ($myrow['last30A']/$myrow['days30A']) : 0;
			$Last60A = ($myrow['days60A'] != 0) ? ($myrow['last60A']/$myrow['days60A']) : 0;
			printf('<td>%s</td>
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
					$myrow['zone'],
					$myrow['loccode'],
					$myrow['salesmancode'],
					$myrow['salesmanname'],
					locale_number_format_zero_blank($myrow['mtdD'],0),
					locale_number_format_zero_blank($Last30D,0),
					locale_number_format_zero_blank($Last60D,0),
					locale_number_format_zero_blank($myrow['mtdC'],0),
					locale_number_format_zero_blank($Last30C,0),
					locale_number_format_zero_blank($Last60C,0),
					locale_number_format_zero_blank($myrow['mtdB'],0),
					locale_number_format_zero_blank($Last30B,0),
					locale_number_format_zero_blank($Last60B,0),
					locale_number_format_zero_blank($myrow['mtdA'],0),
					locale_number_format_zero_blank($Last30A,0),
					locale_number_format_zero_blank($Last60A,0)
					);
			$lastshop = $myrow['loccode'];
		}
		echo '</table>
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
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "SPG Weekly performance". '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th colspan="4">' . 'SPG' . '</th>
							<th colspan="3">' . ConvertSQLDate($YesterdayD) . '</th>
							<th colspan="3">' . ConvertSQLDate($YesterdayC) . '</th>
							<th colspan="3">' . ConvertSQLDate($YesterdayB) . '</th>
							<th colspan="3">' . ConvertSQLDate($YesterdayA) . '</th>
						</tr>';
		echo $TableHeader;
		
		$TableHeader = '<tr>
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
						</tr>';
		$k = 0; //row colour counter
		$lastshop = "";
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($lastshop != $myrow['loccode']){
				echo $TableHeader;
			}
			$Last30D = ($myrow['days30D'] != 0) ? ($myrow['last30D']/$myrow['days30D']) : 0;
			$Last7D = ($myrow['days7D'] != 0) ? ($myrow['last7D']/$myrow['days7D']) : 0;
			$Last30C = ($myrow['days30C'] != 0) ? ($myrow['last30C']/$myrow['days30C']) : 0;
			$Last7C = ($myrow['days7C'] != 0) ? ($myrow['last7C']/$myrow['days7C']) : 0;
			$Last30B = ($myrow['days30B'] != 0) ? ($myrow['last30B']/$myrow['days30B']) : 0;
			$Last7B = ($myrow['days7B'] != 0) ? ($myrow['last7B']/$myrow['days7B']) : 0;
			$Last30A = ($myrow['days30A'] != 0) ? ($myrow['last30A']/$myrow['days30A']) : 0;
			$Last7A = ($myrow['days7A'] != 0) ? ($myrow['last7A']/$myrow['days7A']) : 0;
			printf('<td>%s</td>
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
					$myrow['zone'],
					$myrow['loccode'],
					$myrow['salesmancode'],
					$myrow['salesmanname'],
					locale_number_format_zero_blank($myrow['mtdD'],0),
					locale_number_format_zero_blank($Last7D,0),
					locale_number_format_zero_blank($Last30D,0),
					locale_number_format_zero_blank($myrow['mtdC'],0),
					locale_number_format_zero_blank($Last7C,0),
					locale_number_format_zero_blank($Last30C,0),
					locale_number_format_zero_blank($myrow['mtdB'],0),
					locale_number_format_zero_blank($Last7B,0),
					locale_number_format_zero_blank($Last30B,0),
					locale_number_format_zero_blank($myrow['mtdA'],0),
					locale_number_format_zero_blank($Last7A,0),
					locale_number_format_zero_blank($Last30A,0)
					);
			$lastshop = $myrow['loccode'];
		}
		echo '</table>
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

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$GrandTotal = 0;
	
	if (DB_num_rows($result) != 0){
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
		
		while ($myrow = DB_fetch_array($result)) {
			if ($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' .'Hourly sales and value for the last ' . $numDays . ' days</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
			}
			if (($showHeader) OR ($ZoneName != $myrow['zone'])){
				$TableHeader = '<tr>
									<th class="ascending">' . _('Zone') . '</th>
									<th class="ascending">' . _('Shop') . '</th>
									<th class="ascending">' . _('Type') . '</th>
									<th class="ascending">' . _('First Sale') . '</th>
									<th class="ascending">' . _('00-08') . '</th>
									<th class="ascending">' . _('08-09') . '</th>
									<th class="ascending">' . _('09-10') . '</th>
									<th class="ascending">' . _('10-11') . '</th>
									<th class="ascending">' . _('11-12') . '</th>
									<th class="ascending">' . _('12-13') . '</th>
									<th class="ascending">' . _('13-14') . '</th>
									<th class="ascending">' . _('14-15') . '</th>
									<th class="ascending">' . _('15-16') . '</th>
									<th class="ascending">' . _('16-17') . '</th>
									<th class="ascending">' . _('17-18') . '</th>
									<th class="ascending">' . _('18-19') . '</th>
									<th class="ascending">' . _('19-20') . '</th>
									<th class="ascending">' . _('20-21') . '</th>
									<th class="ascending">' . _('21-22') . '</th>
									<th class="ascending">' . _('22-23') . '</th>
									<th class="ascending">' . _('23-24') . '</th>
									<th class="ascending">' . _('Last Sale') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$TotalSales = $myrow['sales07'] +
						$myrow['sales08'] +
						$myrow['sales09'] +
						$myrow['sales10'] +
						$myrow['sales11'] +
						$myrow['sales12'] +
						$myrow['sales13'] +
						$myrow['sales14'] +
						$myrow['sales15'] +
						$myrow['sales16'] +
						$myrow['sales17'] +
						$myrow['sales18'] +
						$myrow['sales19'] +
						$myrow['sales20'] +
						$myrow['sales21'] +
						$myrow['sales22'] +
						$myrow['sales23'] ;
			$GrandTotal += $TotalSales;
			$ZoneName = $myrow['zone'];
			
			if ($myrow['sales07'] != 0){
				$Sales07 = locale_number_format_zero_blank($myrow['sales07']/$TotalSales*100,0).'%';
				$Total07 += $myrow['sales07'];
			}else{
				$Sales07 = '';
			}		
			if ($myrow['sales08'] != 0){
				$Sales08 = locale_number_format_zero_blank($myrow['sales08']/$TotalSales*100,0).'%';
				$Total08 += $myrow['sales08'];
			}else{
				$Sales08 = '';
			}		
			if ($myrow['sales09'] != 0){
				$Sales09 = locale_number_format_zero_blank($myrow['sales09']/$TotalSales*100,0).'%';
				$Total09 += $myrow['sales09'];
			}else{
				$Sales09 = '';
			}		
			if ($myrow['sales10'] != 0){
				$Sales10 = locale_number_format_zero_blank($myrow['sales10']/$TotalSales*100,0).'%';
				$Total10 += $myrow['sales10'];
			}else{
				$Sales10 = '';
			}		
			if ($myrow['sales11'] != 0){
				$Sales11 = locale_number_format_zero_blank($myrow['sales11']/$TotalSales*100,0).'%';
				$Total11 += $myrow['sales11'];
			}else{
				$Sales11 = '';
			}		
			if ($myrow['sales12'] != 0){
				$Sales12 = locale_number_format_zero_blank($myrow['sales12']/$TotalSales*100,0).'%';
				$Total12 += $myrow['sales12'];
			}else{
				$Sales12 = '';
			}		
			if ($myrow['sales13'] != 0){
				$Sales13 = locale_number_format_zero_blank($myrow['sales13']/$TotalSales*100,0).'%';
				$Total13 += $myrow['sales13'];
			}else{
				$Sales13 = '';
			}		
			if ($myrow['sales14'] != 0){
				$Sales14 = locale_number_format_zero_blank($myrow['sales14']/$TotalSales*100,0).'%';
				$Total14 += $myrow['sales14'];
			}else{
				$Sales14 = '';
			}		
			if ($myrow['sales15'] != 0){
				$Sales15 = locale_number_format_zero_blank($myrow['sales15']/$TotalSales*100,0).'%';
				$Total15 += $myrow['sales15'];
			}else{
				$Sales15 = '';
			}		
			if ($myrow['sales16'] != 0){
				$Sales16 = locale_number_format_zero_blank($myrow['sales16']/$TotalSales*100,0).'%';
				$Total16 += $myrow['sales16'];
			}else{
				$Sales16 = '';
			}		
			if ($myrow['sales17'] != 0){
				$Sales17 = locale_number_format_zero_blank($myrow['sales17']/$TotalSales*100,0).'%';
				$Total17 += $myrow['sales17'];
			}else{
				$Sales17 = '';
			}		
			if ($myrow['sales18'] != 0){
				$Sales18 = locale_number_format_zero_blank($myrow['sales18']/$TotalSales*100,0).'%';
				$Total18 += $myrow['sales18'];
			}else{
				$Sales18 = '';
			}		
			if ($myrow['sales19'] != 0){
				$Sales19 = locale_number_format_zero_blank($myrow['sales19']/$TotalSales*100,0).'%';
				$Total19 += $myrow['sales19'];
			}else{
				$Sales19 = '';
			}		
			if ($myrow['sales20'] != 0){
				$Sales20 = locale_number_format_zero_blank($myrow['sales20']/$TotalSales*100,0).'%';
				$Total20 += $myrow['sales20'];
			}else{
				$Sales20 = '';
			}		
			if ($myrow['sales21'] != 0){
				$Sales21 = locale_number_format_zero_blank($myrow['sales21']/$TotalSales*100,0).'%';
				$Total21 += $myrow['sales21'];
			}else{
				$Sales21 = '';
			}		
			if ($myrow['sales22'] != 0){
				$Sales22 = locale_number_format_zero_blank($myrow['sales22']/$TotalSales*100,0).'%';
				$Total22 += $myrow['sales22'];
			}else{
				$Sales22 = '';
			}		
			if ($myrow['sales23'] != 0){
				$Sales23 = locale_number_format_zero_blank($myrow['sales23']/$TotalSales*100,0).'%';
				$Total23 += $myrow['sales23'];
			}else{
				$Sales23 = '';
			}		

			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
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
					$myrow['zone'],
					$myrow['debtorno'],
					'Sales',
					$myrow['firstsale'],
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
					$myrow['lastsale']
					);

			$k = StartSameColourRow($k);
			printf('<td>%s</td>
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
					locale_number_format_zero_blank($myrow['sales07']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales08']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales09']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales10']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales11']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales12']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales13']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales14']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales15']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales16']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales17']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales18']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales19']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales20']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales21']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales22']/$numDays,0),
					locale_number_format_zero_blank($myrow['sales23']/$numDays,0),
					locale_number_format_zero_blank($TotalSales/$numDays,0)
					);
					
					$i++;
		}
		$k = StartEvenOrOddRow($k);
		printf('<td>%s</td>
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
		printf('<td>%s</td>
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
				
		if (!$showHeader){
			echo '</table>
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
	$result = DB_query($SQL);
	if ($myrow = DB_fetch_array($result)){
		$TotalSales = $myrow['TotalSales'];
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
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Distribution of retail sales by week days for the last ') . $numDays . _(' days') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Day') . '</th>
							<th class="ascending">' . _('% Sales') . '</th>
							<th class="ascending">' . _('Avg Distance') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					GetDayNameFromWeekDay($myrow['WeekDay']),
					locale_number_format(($myrow['WeekDaySales']/$TotalSales)*100,1) . '%', 
					locale_number_format((($myrow['WeekDaySales']/$TotalSales/(1/7))-1)*100,1) . '%' 
					);
		}
	}
	echo '</table>
			</div>';
}

?>
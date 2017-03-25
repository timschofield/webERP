<?php
define("VERSIONFILE", "1.00");

include ('includes/session.inc');
$Title = _('Kapal-Laut SPG Performance Report '. VERSIONFILE);
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

/* ASSIGN users to groups */
include ('includes/KLRoles.inc');

$begintime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin){
}

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_ShopManager 
	OR $KL_SalesDirector){
	SPGPerformanceWeekly($db);
	$NumberOfTestExecuted++;

	AverageSales("SPG", 365, 90, 30, 15, 7, 1,30, "CurrentYear", "All", $db);
	$NumberOfTestExecuted++;

	SPGPerformanceByShop("RETAIL66", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILSE", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILOB", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILO2", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILKA", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILPS", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILAR", 30, 60, 90, $db);
	$NumberOfTestExecuted++;

	SPGPerformanceByShop("RETAILKS", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILPA", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILPB", 30, 60, 90, $db);
	$NumberOfTestExecuted++;

	SPGPerformanceByShop("RETAILSA", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILSB", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILSU", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILSS", 30, 60, 90, $db);
	$NumberOfTestExecuted++;

	SPGPerformanceByShop("RETAILUB", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILMF", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILM2", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILMU", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILPU", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILBU", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILU2", 30, 60, 90, $db);
	$NumberOfTestExecuted++;
	SPGPerformanceByShop("RETAILU3", 30, 60, 90, $db);
	$NumberOfTestExecuted++;

	//	YearDifferenceSales("SPG", 30, $db);
	//  $NumberOfTestExecuted++;
	//	YearDifferenceSales("SPG", 90, $db);
	//	$NumberOfTestExecuted++;

}

prnMsg("Performed ". $NumberOfTestExecuted . " SPG Performance Report",'success');
time_finish($begintime);

include ('includes/footer.inc');

function SPGPerformanceWeekly($db){

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
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30A . "'
							AND salesorders.orddate <= '" . $YesterdayA . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30A,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30B . "'
							AND salesorders.orddate <= '" . $YesterdayB . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30B,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30C . "'
							AND salesorders.orddate <= '" . $YesterdayC . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30C,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last30D . "'
							AND salesorders.orddate <= '" . $YesterdayD . "'
							AND salesorders.salesperson = salesman.salesmancode) AS last30D,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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
				AND ((SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.completed = 1
									AND salesorders.fromstkloc = locations.loccode
							AND salesorders.orddate >= '" . $Last7A . "'
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
		$lastshop = $myrow['loccode'];
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($lastshop != $myrow['loccode']){
				echo $TableHeader;
			}
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
					locale_number_format_zero_blank($myrow['last7D']/$myrow['days7D'],0),
					locale_number_format_zero_blank($myrow['last30D']/$myrow['days30D'],0),
					locale_number_format_zero_blank($myrow['mtdC'],0),
					locale_number_format_zero_blank($myrow['last7C']/$myrow['days7C'],0),
					locale_number_format_zero_blank($myrow['last30C']/$myrow['days30C'],0),
					locale_number_format_zero_blank($myrow['mtdB'],0),
					locale_number_format_zero_blank($myrow['last7B']/$myrow['days7B'],0),
					locale_number_format_zero_blank($myrow['last30B']/$myrow['days30B'],0),
					locale_number_format_zero_blank($myrow['mtdA'],0),
					locale_number_format_zero_blank($myrow['last7A']/$myrow['days7A'],0),
					locale_number_format_zero_blank($myrow['last30A']/$myrow['days30A'],0)
					);
			$lastshop = $myrow['loccode'];
		}
		echo '</table>
				</div>';
	}
}


?>
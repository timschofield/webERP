<?php
define("VERSIONFILE", "1.02");

include('includes/session.php');
$Title = _('Kapal-Laut SPG Performance Report ' . VERSIONFILE);
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$BeginTime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin) {

}

if ($KL_OperationalManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager) {

	SPGPerformanceMonthy();
	$NumberOfTestExecuted++;
}

if ($KL_SalesDirector
	OR $KL_ShopManager) {

	SPGPerformanceWeekly();
	$NumberOfTestExecuted++;
}
	
if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager) {

	AverageSales("SPG", 30, 15, 10, 7, 5, 1, 7, "CurrentYear", "All");
	$NumberOfTestExecuted++;
}
	
if ($KL_OperationalManager 
	OR $KL_ShopManager) {

	SPGPerformanceAllShops(15, 30, 45);
	$NumberOfTestExecuted++;
}
	
if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector) {
		
	HourlySales(15, $RootPath);
	$NumberOfTestExecuted++;
	HourlySales(30, $RootPath);
	$NumberOfTestExecuted++;
	
	DaysOfWeekSales(180, $RootPath);
	$NumberOfTestExecuted++;
}

if ($KL_SystemAdmin) {	
	RetailTypePayments("SPG", 90);
	$NumberOfTestExecuted++;
}

prnMsg("Performed " . $NumberOfTestExecuted . " SPG Performance Report", 'success');

if ($KL_SystemAdmin) {
	time_finish($BeginTime);
}

include('includes/footer.php');

/********************************************************************************************
FUNCTIONS ONLY USED IN SPG PERFORMANCE BOARD
*********************************************************************************************/

function RetailTypePayments($TypeReport, $MaxDays) {
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$MaxDays));
	$TotalCash = 0;
	$TotalCredit = 0;
	$TotalReturned = 0;
	$TotalVouchers = 0;
	$Total = 0;

	if ($TypeReport == "Shop") {
		$SQL = "SELECT salesorders.debtorno AS reportunit,
					debtorsmaster.name AS reportname,
					SUM(salesorders.klpaidcash) AS cashshop, 
					SUM(salesorders.klpaidcreditcard) AS creditshop, 
					SUM(salesorders.klreturnedgoods) AS returnedgoodsshop,
					SUM(salesorders.klvouchers) AS vouchersshop,
					SUM(salesorders.klpaidcash+salesorders.klpaidcreditcard+salesorders.klreturnedgoods+salesorders.klvouchers) AS totalshop
			FROM salesorders, debtorsmaster
			WHERE salesorders.debtorno = debtorsmaster.debtorno
				AND salesorders.orddate >= '" . $StartDate . "' 
				AND debtorsmaster.typeid IN (" . CUSTOMER_TYPE_RETAIL . ")
			GROUP BY salesorders.debtorno
			ORDER BY salesorders.debtorno";
	} else {
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
				AND orddate >= '" . $StartDate . "' 
				AND debtorsmaster.typeid IN (" . CUSTOMER_TYPE_RETAIL . ")
			GROUP BY salesorders.salesperson
			ORDER BY salesorders.salesperson";
	}
	
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0) {
		$TableTitleText = _('Distribution Cash / Credit Card during the last ') . $MaxDays . _(' days by ') . $TypeReport;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . $TypeReport . '</th>
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
			if ($MyRow['totalshop'] != 0) {
				$PercentCash = locale_number_format(($MyRow['cashshop'] / $MyRow['totalshop']) * 100, 1);
				$PercentCredit = locale_number_format(($MyRow['creditshop'] / $MyRow['totalshop']) * 100, 1);
				$PercentReturns = locale_number_format(($MyRow['returnedgoodsshop'] / $MyRow['totalshop']) * 100, 1);
				$PercentVouchers = locale_number_format(($MyRow['vouchersshop'] / $MyRow['totalshop']) * 100, 1);
				
				$TotalCash = $TotalCash + $MyRow['cashshop'];
				$TotalCredit = $TotalCredit + $MyRow['creditshop'];
				$TotalReturned = $TotalReturned + $MyRow['returnedgoodsshop'];
				$TotalVouchers = $TotalVouchers + $MyRow['vouchersshop'];
				$Total = $Total + $MyRow['totalshop'];
				
				echo '<tr class="striped_row">
						<td>' . $MyRow['reportunit'] . '</td>
						<td>' . $MyRow['reportname'] . '</td>
						<td class="number">' . $PercentCash . '</td>
						<td class="number">' . $PercentCredit . '</td>
						<td class="number">' . $PercentReturns . '</td>
						<td class="number">' . $PercentVouchers . '</td>
						</tr>';
				$i++;
			}
		}

		$PercentCash = $Total > 0 ? locale_number_format(($TotalCash / $Total) * 100, 1) : '0.0';
		$PercentCredit = $Total > 0 ? locale_number_format(($TotalCredit / $Total) * 100, 1) : '0.0';
		$PercentReturns = $Total > 0 ? locale_number_format(($TotalReturned / $Total) * 100, 1) : '0.0';
		$PercentVouchers = $Total > 0 ? locale_number_format(($TotalVouchers / $Total) * 100, 1) : '0.0';
		
		echo '<tr class="striped_row">
				<td>' . "" . '</td>
				<td>' . "Average" . '</td>
				<td class="number">' . $PercentCash . '</td>
				<td class="number">' . $PercentCredit . '</td>
				<td class="number">' . $PercentReturns . '</td>
				<td class="number">' . $PercentVouchers . '</td>
				</tr>';
		
		echo '</tbody>
				</table>
				</div>';
	}
}

function SPGPerformanceAllShops($NumDaysA, $NumDaysB, $NumDaysC) {
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

function SPGPerformanceByShop($Shop, $NumDaysA, $NumDaysB, $NumDaysC) {
	// Calculate date ranges
	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysA));

	$YesterdayB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysA - 1));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysB));

	$YesterdayC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysB - 1));
	$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysC));

	// Get shop name for display purposes
	$ShopNameSQL = "SELECT name FROM debtorsmaster WHERE debtorno = '" . $Shop . "'";
	$ShopNameResult = DB_query($ShopNameSQL);
	$ShopNameRow = DB_fetch_array($ShopNameResult);
	$ShopName = isset($ShopNameRow['name']) ? $ShopNameRow['name'] : $Shop;

    // Use JOIN approach instead of WITH clause for compatibility with older MariaDB versions
    $SQL = "SELECT sm.salesmancode,
               sm.salesmanname,
               sr.secrolename,
               COALESCE(periodA.days, 0) AS daysA,
               COALESCE(periodA.sales, 0) AS salesA,
               COALESCE(periodB.days, 0) AS daysB,
               COALESCE(periodB.sales, 0) AS salesB,
               COALESCE(periodC.days, 0) AS daysC,
               COALESCE(periodC.sales, 0) AS salesC
        FROM salesman sm
        JOIN www_users wu ON wu.salesman = sm.salesmancode
        JOIN securityroles sr ON wu.fullaccess = sr.secroleid
        LEFT JOIN (
            SELECT salesorders.salesperson, 
                   COUNT(DISTINCT(salesorders.orddate)) AS days,
                   SUM(salesorderdetails.linenetprice) AS sales
            FROM salesorderdetails, salesorders
            WHERE salesorderdetails.orderno = salesorders.orderno
                AND salesorderdetails.completed = 1
                AND salesorders.debtorno = '" . $Shop . "'
                AND salesorders.orddate >= '". $StartDateA . "'
                AND salesorders.orddate <= '". $YesterdayA . "'
            GROUP BY salesorders.salesperson
        ) AS periodA ON sm.salesmancode = periodA.salesperson
        LEFT JOIN (
            SELECT salesorders.salesperson, 
                   COUNT(DISTINCT(salesorders.orddate)) AS days,
                   SUM(salesorderdetails.linenetprice) AS sales
            FROM salesorderdetails, salesorders
            WHERE salesorderdetails.orderno = salesorders.orderno
                AND salesorderdetails.completed = 1
                AND salesorders.debtorno = '" . $Shop . "'
                AND salesorders.orddate >= '". $StartDateB . "'
                AND salesorders.orddate <= '". $YesterdayB . "'
            GROUP BY salesorders.salesperson
        ) AS periodB ON sm.salesmancode = periodB.salesperson
        LEFT JOIN (
            SELECT salesorders.salesperson, 
                   COUNT(DISTINCT(salesorders.orddate)) AS days,
                   SUM(salesorderdetails.linenetprice) AS sales
            FROM salesorderdetails, salesorders
            WHERE salesorderdetails.orderno = salesorders.orderno
                AND salesorderdetails.completed = 1
                AND salesorders.debtorno = '" . $Shop . "'
                AND salesorders.orddate >= '". $StartDateC . "'
                AND salesorders.orddate <= '". $YesterdayC . "'
            GROUP BY salesorders.salesperson
        ) AS periodC ON sm.salesmancode = periodC.salesperson
        WHERE wu.customerid = '" . $Shop . "'
            AND (COALESCE(periodA.sales, 0) > 0 OR COALESCE(periodB.sales, 0) > 0 OR COALESCE(periodC.sales, 0) > 0)
        ORDER BY sm.salesmancode";
                
    $Result = DB_query($SQL);
    if (DB_num_rows($Result) != 0){
        $TableTitleText = _('SPG Performance in ') . $ShopName . ' (' . $Shop . ') ' . _('during the last ') . $NumDaysA . _(' days and ') . ($NumDaysB - $NumDaysA) . _(' previous days');
        ShowTableTitle($TableTitleText);
        echo '<div>';
        echo '<table class="selection">
                <thead>
                    <tr>
                        <th colspan="3">' . _('SPG') . '</th>
                        <th colspan="2">' . $NumDaysA . ' ' . _('last days') . '</th>
                        <th colspan="2">' . $NumDaysA . '-' . $NumDaysB . ' ' . _('previous days') . '</th>
                        <th colspan="2">' . $NumDaysB . '-' . $NumDaysC . ' ' . _('previous days') . '</th>
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
            
            echo '<tr class="striped_row">
                    <td>' . $MyRow['salesmancode'] . '</td>
                    <td>' . $MyRow['salesmanname'] . '</td>
                    <td>' . $MyRow['secrolename'] . '</td>
                    <td class="number">' . locale_number_format_zero_blank($MyRow['daysA'],0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($DailyA,0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($MyRow['daysB'],0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($DailyB,0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($MyRow['daysC'],0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($DailyC,0) . '</td>
                    </tr>';
        }
        
        // Add shop summary row by calculating totals - use a simpler query for compatibility
        $ShopSummarySQL = "SELECT 
            (SELECT COUNT(DISTINCT(orddate)) FROM salesorders 
             WHERE debtorno = '$Shop' AND orddate >= '$StartDateA' AND orddate <= '$YesterdayA') AS totalDaysA,
            (SELECT SUM(linenetprice) FROM salesorders 
             JOIN salesorderdetails ON salesorders.orderno = salesorderdetails.orderno
             WHERE debtorno = '$Shop' AND orddate >= '$StartDateA' 
             AND orddate <= '$YesterdayA' AND salesorderdetails.completed = 1) AS totalA,
            
            (SELECT COUNT(DISTINCT(orddate)) FROM salesorders 
             WHERE debtorno = '$Shop' AND orddate >= '$StartDateB' AND orddate <= '$YesterdayB') AS totalDaysB,
            (SELECT SUM(linenetprice) FROM salesorders 
             JOIN salesorderdetails ON salesorders.orderno = salesorderdetails.orderno
             WHERE debtorno = '$Shop' AND orddate >= '$StartDateB' 
             AND orddate <= '$YesterdayB' AND salesorderdetails.completed = 1) AS totalB,
            
            (SELECT COUNT(DISTINCT(orddate)) FROM salesorders 
             WHERE debtorno = '$Shop' AND orddate >= '$StartDateC' AND orddate <= '$YesterdayC') AS totalDaysC,
            (SELECT SUM(linenetprice) FROM salesorders 
             JOIN salesorderdetails ON salesorders.orderno = salesorderdetails.orderno
             WHERE debtorno = '$Shop' AND orddate >= '$StartDateC' 
             AND orddate <= '$YesterdayC' AND salesorderdetails.completed = 1) AS totalC";
        
        $SummaryResult = DB_query($ShopSummarySQL);
        if ($SummaryRow = DB_fetch_array($SummaryResult)) {
            $ShopDailyA = ($SummaryRow['totalDaysA'] > 0) ? $SummaryRow['totalA'] / $SummaryRow['totalDaysA'] : 0;
            $ShopDailyB = ($SummaryRow['totalDaysB'] > 0) ? $SummaryRow['totalB'] / $SummaryRow['totalDaysB'] : 0;
            $ShopDailyC = ($SummaryRow['totalDaysC'] > 0) ? $SummaryRow['totalC'] / $SummaryRow['totalDaysC'] : 0;
            
            echo '<tr class="striped_row" style="font-weight:bold;">
                    <td colspan="3">' . _('SHOP TOTAL') . '</td>
                    <td class="number">' . locale_number_format_zero_blank($SummaryRow['totalDaysA'],0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($ShopDailyA,0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($SummaryRow['totalDaysB'],0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($ShopDailyB,0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($SummaryRow['totalDaysC'],0) . '</td>
                    <td class="number">' . locale_number_format_zero_blank($ShopDailyC,0) . '</td>
                    </tr>';
        }
        
        echo '</tbody>
                </table>
                </div>';
    } else {
        prnMsg(_('No performance data available for shop ') . $ShopName . ' (' . $Shop . ')', 'info');
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

	// SQL Query optimized by Claude 3.7, around 10X faster than the previous one
	$SQL = "SELECT 
		locations.zone,
		locations.loccode,
		salesman.salesmancode,
		salesman.salesmanname,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last30D ."' AND so.orddate <= '". $YesterdayD ."' THEN so.orddate END) AS days30D,
		SUM(CASE WHEN so.orddate >= '". $Last30D ."' AND so.orddate <= '". $YesterdayD ."' THEN sod.linenetprice ELSE 0 END) AS last30D,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last60D ."' AND so.orddate <= '". $YesterdayD ."' THEN so.orddate END) AS days60D,
		SUM(CASE WHEN so.orddate >= '". $Last60D ."' AND so.orddate <= '". $YesterdayD ."' THEN sod.linenetprice ELSE 0 END) AS last60D,
		SUM(CASE WHEN so.orddate >= '". $StartMonthD ."' AND so.orddate <= '". $YesterdayD ."' THEN sod.linenetprice ELSE 0 END) AS mtdD,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last30C ."' AND so.orddate <= '". $YesterdayC ."' THEN so.orddate END) AS days30C,
		SUM(CASE WHEN so.orddate >= '". $Last30C ."' AND so.orddate <= '". $YesterdayC ."' THEN sod.linenetprice ELSE 0 END) AS last30C,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last60C ."' AND so.orddate <= '". $YesterdayC ."' THEN so.orddate END) AS days60C,
		SUM(CASE WHEN so.orddate >= '". $Last60C ."' AND so.orddate <= '". $YesterdayC ."' THEN sod.linenetprice ELSE 0 END) AS last60C,
		SUM(CASE WHEN so.orddate >= '". $StartMonthC ."' AND so.orddate <= '". $YesterdayC ."' THEN sod.linenetprice ELSE 0 END) AS mtdC,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last30B ."' AND so.orddate <= '". $YesterdayB ."' THEN so.orddate END) AS days30B,
		SUM(CASE WHEN so.orddate >= '". $Last30B ."' AND so.orddate <= '". $YesterdayB ."' THEN sod.linenetprice ELSE 0 END) AS last30B,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last60B ."' AND so.orddate <= '". $YesterdayB ."' THEN so.orddate END) AS days60B,
		SUM(CASE WHEN so.orddate >= '". $Last60B ."' AND so.orddate <= '". $YesterdayB ."' THEN sod.linenetprice ELSE 0 END) AS last60B,
		SUM(CASE WHEN so.orddate >= '". $StartMonthB ."' AND so.orddate <= '". $YesterdayB ."' THEN sod.linenetprice ELSE 0 END) AS mtdB,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last30A ."' AND so.orddate <= '". $YesterdayA ."' THEN so.orddate END) AS days30A,
		SUM(CASE WHEN so.orddate >= '". $Last30A ."' AND so.orddate <= '". $YesterdayA ."' THEN sod.linenetprice ELSE 0 END) AS last30A,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last60A ."' AND so.orddate <= '". $YesterdayA ."' THEN so.orddate END) AS days60A,
		SUM(CASE WHEN so.orddate >= '". $Last60A ."' AND so.orddate <= '". $YesterdayA ."' THEN sod.linenetprice ELSE 0 END) AS last60A,
		SUM(CASE WHEN so.orddate >= '". $StartMonthA ."' AND so.orddate <= '". $YesterdayA ."' THEN sod.linenetprice ELSE 0 END) AS mtdA
	FROM locations
	JOIN salesorders so 
		ON so.fromstkloc = locations.loccode
	JOIN salesorderdetails sod
		ON sod.orderno = so.orderno
			AND sod.completed = 1
	JOIN salesman 
		ON so.salesperson = salesman.salesmancode
	WHERE 
		so.orddate >= '". $Last60D ."'
		AND so.orddate <= '". $YesterdayA ."'
		AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
	GROUP BY 
		locations.zone,
		locations.loccode,
		salesman.salesmancode
	HAVING 
		SUM(CASE WHEN so.orddate >= '". $Last60A ."' AND so.orddate <= '". $YesterdayA ."' THEN sod.linenetprice ELSE 0 END) > 0
	ORDER BY 
		locations.zone,
		locations.loccode,
		salesman.salesmancode";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "SPG Monthly performance";
		ShowTableTitle($TableTitleText);
		echo '<div>';
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

		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="4">' . 'SPG' . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayD) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayC) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayB) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayA) . '</th>
					</tr>
				</thead>
				<tbody>';
		$LastShop = "";
		while ($MyRow = DB_fetch_array($Result)) {
			if ($LastShop != $MyRow['loccode']){
				echo $TableHeader;
				$LastShop = $MyRow['loccode'];
		}
			$Last30D = ($MyRow['days30D'] != 0) ? ($MyRow['last30D']/$MyRow['days30D']) : 0;
			$Last60D = ($MyRow['days60D'] != 0) ? ($MyRow['last60D']/$MyRow['days60D']) : 0;
			$Last30C = ($MyRow['days30C'] != 0) ? ($MyRow['last30C']/$MyRow['days30C']) : 0;
			$Last60C = ($MyRow['days60C'] != 0) ? ($MyRow['last60C']/$MyRow['days60C']) : 0;
			$Last30B = ($MyRow['days30B'] != 0) ? ($MyRow['last30B']/$MyRow['days30B']) : 0;
			$Last60B = ($MyRow['days60B'] != 0) ? ($MyRow['last60B']/$MyRow['days60B']) : 0;
			$Last30A = ($MyRow['days30A'] != 0) ? ($MyRow['last30A']/$MyRow['days30A']) : 0;
			$Last60A = ($MyRow['days60A'] != 0) ? ($MyRow['last60A']/$MyRow['days60A']) : 0;
			echo '<tr class="striped_row">
					<td>' . $MyRow['zone'] . '</td>
					<td>' . $MyRow['loccode'] . '</td>
					<td>' . $MyRow['salesmancode'] . '</td>
					<td>' . $MyRow['salesmanname'] . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['mtdD'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last30D,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last60D,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['mtdC'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last30C,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last60C,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['mtdB'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last30B,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last60B,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['mtdA'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last30A,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last60A,0) . '</td>
					</tr>';
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

	// SQL Query optimized by Claude 3.7, around 10X faster than the previous one
	$SQL = "SELECT 
		locations.zone,
		locations.loccode,
		salesman.salesmancode,
		salesman.salesmanname,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last7D ."' AND so.orddate <= '". $YesterdayD ."' THEN so.orddate END) AS days7D,
		SUM(CASE WHEN so.orddate >= '". $Last7D ."' AND so.orddate <= '". $YesterdayD ."' THEN sod.linenetprice ELSE 0 END) AS last7D,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last30D ."' AND so.orddate <= '". $YesterdayD ."' THEN so.orddate END) AS days30D,
		SUM(CASE WHEN so.orddate >= '". $Last30D ."' AND so.orddate <= '". $YesterdayD ."' THEN sod.linenetprice ELSE 0 END) AS last30D,
		SUM(CASE WHEN so.orddate >= '". $StartMonthD ."' AND so.orddate <= '". $YesterdayD ."' THEN sod.linenetprice ELSE 0 END) AS mtdD,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last7C ."' AND so.orddate <= '". $YesterdayC ."' THEN so.orddate END) AS days7C,
		SUM(CASE WHEN so.orddate >= '". $Last7C ."' AND so.orddate <= '". $YesterdayC ."' THEN sod.linenetprice ELSE 0 END) AS last7C,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last30C ."' AND so.orddate <= '". $YesterdayC ."' THEN so.orddate END) AS days30C,
		SUM(CASE WHEN so.orddate >= '". $Last30C ."' AND so.orddate <= '". $YesterdayC ."' THEN sod.linenetprice ELSE 0 END) AS last30C,
		SUM(CASE WHEN so.orddate >= '". $StartMonthC ."' AND so.orddate <= '". $YesterdayC ."' THEN sod.linenetprice ELSE 0 END) AS mtdC,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last7B ."' AND so.orddate <= '". $YesterdayB ."' THEN so.orddate END) AS days7B,
		SUM(CASE WHEN so.orddate >= '". $Last7B ."' AND so.orddate <= '". $YesterdayB ."' THEN sod.linenetprice ELSE 0 END) AS last7B,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last30B ."' AND so.orddate <= '". $YesterdayB ."' THEN so.orddate END) AS days30B,
		SUM(CASE WHEN so.orddate >= '". $Last30B ."' AND so.orddate <= '". $YesterdayB ."' THEN sod.linenetprice ELSE 0 END) AS last30B,
		SUM(CASE WHEN so.orddate >= '". $StartMonthB ."' AND so.orddate <= '". $YesterdayB ."' THEN sod.linenetprice ELSE 0 END) AS mtdB,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last7A ."' AND so.orddate <= '". $YesterdayA ."' THEN so.orddate END) AS days7A,
		SUM(CASE WHEN so.orddate >= '". $Last7A ."' AND so.orddate <= '". $YesterdayA ."' THEN sod.linenetprice ELSE 0 END) AS last7A,
		COUNT(DISTINCT CASE WHEN so.orddate >= '". $Last30A ."' AND so.orddate <= '". $YesterdayA ."' THEN so.orddate END) AS days30A,
		SUM(CASE WHEN so.orddate >= '". $Last30A ."' AND so.orddate <= '". $YesterdayA ."' THEN sod.linenetprice ELSE 0 END) AS last30A,
		SUM(CASE WHEN so.orddate >= '". $StartMonthA ."' AND so.orddate <= '". $YesterdayA ."' THEN sod.linenetprice ELSE 0 END) AS mtdA
	FROM locations
	JOIN salesorders so
		ON so.fromstkloc = locations.loccode
	JOIN salesorderdetails sod 
		ON sod.orderno = so.orderno
			AND sod.completed = 1
	JOIN salesman 
		ON so.salesperson = salesman.salesmancode
	WHERE so.orddate >= '". $Last30D ."'
		AND so.orddate <= '". $YesterdayA ."'
		AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
	GROUP BY 
		locations.zone,
		locations.loccode,
		salesman.salesmancode
	HAVING 
		SUM(CASE WHEN so.orddate >= '". $Last30A ."' AND so.orddate <= '". $YesterdayA ."' THEN sod.linenetprice ELSE 0 END) > 0
	ORDER BY 
		locations.zone,
		locations.loccode,
		salesman.salesmancode";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "SPG Weekly performance";
		ShowTableTitle($TableTitleText);
		echo '<div>';
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

		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="4">' . 'SPG' . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayD) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayC) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayB) . '</th>
						<th colspan="3">' . ConvertSQLDate($YesterdayA) . '</th>
					</tr>
				</thead>
				<tbody>';
		$LastShop = "";
		while ($MyRow = DB_fetch_array($Result)) {
			if ($LastShop != $MyRow['loccode']){
				echo $TableHeader;
				$LastShop = $MyRow['loccode'];
			}
			$Last30D = ($MyRow['days30D'] != 0) ? ($MyRow['last30D']/$MyRow['days30D']) : 0;
			$Last7D = ($MyRow['days7D'] != 0) ? ($MyRow['last7D']/$MyRow['days7D']) : 0;
			$Last30C = ($MyRow['days30C'] != 0) ? ($MyRow['last30C']/$MyRow['days30C']) : 0;
			$Last7C = ($MyRow['days7C'] != 0) ? ($MyRow['last7C']/$MyRow['days7C']) : 0;
			$Last30B = ($MyRow['days30B'] != 0) ? ($MyRow['last30B']/$MyRow['days30B']) : 0;
			$Last7B = ($MyRow['days7B'] != 0) ? ($MyRow['last7B']/$MyRow['days7B']) : 0;
			$Last30A = ($MyRow['days30A'] != 0) ? ($MyRow['last30A']/$MyRow['days30A']) : 0;
			$Last7A = ($MyRow['days7A'] != 0) ? ($MyRow['last7A']/$MyRow['days7A']) : 0;
			echo '<tr class="striped_row">
					<td>' . $MyRow['zone'] . '</td>
					<td>' . $MyRow['loccode'] . '</td>
					<td>' . $MyRow['salesmancode'] . '</td>
					<td>' . $MyRow['salesmanname'] . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['mtdD'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last7D,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last30D,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['mtdC'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last7C,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last30C,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['mtdB'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last7B,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last30B,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['mtdA'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last7A,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($Last30A,0) . '</td>
					</tr>';
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

	// Optimized query using CASE WHEN for conditional aggregation
	$SQL = "SELECT dm.debtorno,
				loc.zone,
				MIN(so.ordtime) AS firstsale,
				MAX(so.ordtime) AS lastsale,
				SUM(CASE WHEN so.ordtime < '08:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales07,
				SUM(CASE WHEN so.ordtime >= '08:00:00' AND so.ordtime < '09:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales08,
				SUM(CASE WHEN so.ordtime >= '09:00:00' AND so.ordtime < '10:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales09,
				SUM(CASE WHEN so.ordtime >= '10:00:00' AND so.ordtime < '11:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales10,
				SUM(CASE WHEN so.ordtime >= '11:00:00' AND so.ordtime < '12:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales11,
				SUM(CASE WHEN so.ordtime >= '12:00:00' AND so.ordtime < '13:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales12,
				SUM(CASE WHEN so.ordtime >= '13:00:00' AND so.ordtime < '14:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales13,
				SUM(CASE WHEN so.ordtime >= '14:00:00' AND so.ordtime < '15:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales14,
				SUM(CASE WHEN so.ordtime >= '15:00:00' AND so.ordtime < '16:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales15,
				SUM(CASE WHEN so.ordtime >= '16:00:00' AND so.ordtime < '17:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales16,
				SUM(CASE WHEN so.ordtime >= '17:00:00' AND so.ordtime < '18:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales17,
				SUM(CASE WHEN so.ordtime >= '18:00:00' AND so.ordtime < '19:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales18,
				SUM(CASE WHEN so.ordtime >= '19:00:00' AND so.ordtime < '20:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales19,
				SUM(CASE WHEN so.ordtime >= '20:00:00' AND so.ordtime < '21:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales20,
				SUM(CASE WHEN so.ordtime >= '21:00:00' AND so.ordtime < '22:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales21,
				SUM(CASE WHEN so.ordtime >= '22:00:00' AND so.ordtime < '23:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales22,
				SUM(CASE WHEN so.ordtime >= '23:00:00' THEN (so.klpaidcash + so.klpaidcreditcard) ELSE 0 END) AS sales23
			FROM debtorsmaster dm
			JOIN custbranch cb 
				ON dm.debtorno = cb.debtorno
			JOIN locations loc 
				ON cb.defaultlocation = loc.loccode
			JOIN salesorders so 
				ON so.debtorno = dm.debtorno
			WHERE so.orddate >= '". $InitialDate ."'
				AND so.orddate <= '". $Yesterday ."'
				AND loc.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . "
				AND dm.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			GROUP BY dm.debtorno,
				loc.zone
			ORDER BY loc.zone,
				dm.debtorno";


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
				$TableTitleText = 'Hourly sales and value for the last ' . $numDays . ' days';
				ShowTableTitle($TableTitleText);
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
			echo '<tr class="striped_row">
					<td>' . $MyRow['zone'] . '</td>
					<td>' . $MyRow['debtorno'] . '</td>
					<td>' . 'Sales' . '</td>
					<td>' . $MyRow['firstsale'] . '</td>
					<td class="number">' . $Sales07 . '</td>
					<td class="number">' . $Sales08 . '</td>
					<td class="number">' . $Sales09 . '</td>
					<td class="number">' . $Sales10 . '</td>
					<td class="number">' . $Sales11 . '</td>
					<td class="number">' . $Sales12 . '</td>
					<td class="number">' . $Sales13 . '</td>
					<td class="number">' . $Sales14 . '</td>
					<td class="number">' . $Sales15 . '</td>
					<td class="number">' . $Sales16 . '</td>
					<td class="number">' . $Sales17 . '</td>
					<td class="number">' . $Sales18 . '</td>
					<td class="number">' . $Sales19 . '</td>
					<td class="number">' . $Sales20 . '</td>
					<td class="number">' . $Sales21 . '</td>
					<td class="number">' . $Sales22 . '</td>
					<td class="number">' . $Sales23 . '</td>
					<td>' . $MyRow['lastsale'] . '</td>
					</tr>';

			$k = StartSameColourRow($k);
			echo '<tr class="striped_row">
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . 'Value' . '</td>
					<td>' . '' . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales07']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales08']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales09']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales10']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales11']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales12']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales13']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales14']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales15']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales16']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales17']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales18']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales19']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales20']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales21']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales22']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['sales23']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalSales/$numDays,0) . '</td>
					</tr>';
					
					$i++;
		}
		$k = StartEvenOrOddRow($k);
		echo '<tr class="striped_row">
				<td>' . 'TOTALS' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total07/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total08/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total09/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total10/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total11/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total12/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total13/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total14/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total15/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total16/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total17/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total18/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total19/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total20/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total21/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total22/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total23/$GrandTotal*100,0).'%' . '</td>
				<td>' . '' . '</td>
				</tr>';

		$k = StartEvenOrOddRow($k);
		echo '<tr class="striped_row">
				<td>' . 'CUMULATIVE' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td class="number">' . locale_number_format_zero_blank($Total07/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21+$Total22)/$GrandTotal*100,0).'%' . '</td>
				<td class="number">' . locale_number_format_zero_blank(($Total07+$Total08+$Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21+$Total22+$Total23)/$GrandTotal*100,0).'%' . '</td>
				<td>' . '' . '</td>
				</tr>';
				
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
		$TableTitleText = _('Distribution of retail sales by week days for the last ') . $numDays . _(' days');
		ShowTableTitle($TableTitleText);
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
			echo '<tr class="striped_row">
					<td>' . GetDayNameFromWeekDay($MyRow['WeekDay']) . '</td>
					<td class="number">' . locale_number_format(($MyRow['WeekDaySales']/$TotalSales)*100,1) . '%' . '</td>
					<td class="number">' . locale_number_format((($MyRow['WeekDaySales']/$TotalSales/(1/7))-1)*100,1) . '%' . '</td>
					</tr>';
		}
	}
	echo '</tbody>
			</table>
			</div>';
}

?>
<?php
include ('includes/session.php');
$Title = _('KL Sales Hourly Report');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLCountriesForRetail.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLRetailCustomer.php');
include('includes/KLUIGeneralFunctions.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin 
	OR $KL_OperationalManager 
	OR $KL_BusinessDevelopmentManager 
	OR $KL_SalesDirector
	OR $KL_ShopManager){
	HourlyPerformance(15,$RootPath);
	$NumberOfTestExecuted++;
}

prnMsg("Performed ". $NumberOfTestExecuted . " Sales Hourly Report",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include ('includes/footer.php');

function HourlyPerformance($numDays, $RootPath){

	$Today = date('Y-m-d');
	$Now = date ('H:i:s');
	$Yesterday = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

	$SQL = "SELECT name,
				(SELECT MIN(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."') AS firstsalefull,
				(SELECT MAX(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."') AS lastsalefull,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."') AS totalsalesfull,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."') AS valuesalesfull,
				(SELECT MIN(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime <= '". $Now ."') AS firstsale,
				(SELECT MAX(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime <= '". $Now ."') AS lastsale,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime <= '". $Now ."') AS totalsales,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime <= '". $Now ."') AS valuesales,
				(SELECT MIN(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = CURRENT_DATE) AS firstsaletoday,
				(SELECT MAX(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = CURRENT_DATE) AS lastsaletoday,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = CURRENT_DATE) AS totalsalestoday,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = CURRENT_DATE) AS valuesalestoday
			FROM debtorsmaster
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			ORDER BY (SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."') DESC,
				debtorsmaster.debtorno";

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$FirstSaleFull = '99:99:99';
	$LastSaleFull = '00:00:00';
	$TotalSalesFull = 0;
	$ValueSalesFull = 0;
	$FirstSale = '99:99:99';
	$LastSale = '00:00:00';
	$TotalSales = 0;
	$ValueSales = 0;
	$FirstSaleToday = '99:99:99';
	$LastSaleToday = '00:00:00';
	$TotalSalesToday = 0;
	$ValueSalesToday = 0;
	
	if (DB_num_rows($Result) != 0){
		$i = 0; // row counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowHeader){
				$TableTitleText = _('Hourly Sales Performance until '. $Now);
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<thead>
									<tr>
										<th>#</th>
										<th>' . _('Shop') . '</th>
										<th colspan=2>' . 'Last ' . $numDays . ' days</th>
										<th colspan=2>' . 'Last ' . $numDays . ' days until ' . $Now . '</th>
										<th colspan=4>' . 'Today' . '</th>
									</tr>
									<tr>
										<th class="SortedColumn">#</th>
										<th class="SortedColumn">' . _('Name') . '</th>
										<th class="SortedColumn">' . _('# Sales') . '</th>
										<th class="SortedColumn">' . _('Value Sales') . '</th>
										<th class="SortedColumn">' . _('# Sales') . '</th>
										<th class="SortedColumn">' . _('Value Sales') . '</th>
										<th class="SortedColumn">' . _('First Sale ') . '</th>
										<th class="SortedColumn">' . _('Last Sale ') . '</th>
										<th class="SortedColumn">' . _('# Sales ') . '</th>
										<th class="SortedColumn">' . _('Value Sales') . '</th>
									</tr>
								</thead>
								<tbody>';
				echo $TableHeader;
				$ShowHeader = FALSE;
			}
			$i++;
			echo '<tr class="striped_row">
					<td class="number">' . locale_number_format_zero_blank($i,0) . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['totalsalesfull']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['valuesalesfull']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['totalsales']/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['valuesales']/$numDays,0) . '</td>
					<td class="number">' . $MyRow['firstsaletoday'] . '</td>
					<td class="number">' . $MyRow['lastsaletoday'] . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['totalsalestoday'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['valuesalestoday'],0) . '</td>
					</tr>';
					
			if (isset($MyRow['firstsalefull'])){		
				if ($FirstSaleFull > $MyRow['firstsalefull']){
					$FirstSaleFull = $MyRow['firstsalefull'];
				}
			}
			if (isset($MyRow['lastsalefull'])){		
				if ($LastSaleFull < $MyRow['lastsalefull']){
					$LastSaleFull = $MyRow['lastsalefull'];
				}
			}
			$TotalSalesFull = $TotalSalesFull + $MyRow['totalsalesfull'];
			$ValueSalesFull = $ValueSalesFull + $MyRow['valuesalesfull'];

			if (isset($MyRow['firstsale'])){		
				if ($FirstSale > $MyRow['firstsale']){
					$FirstSale = $MyRow['firstsale'];
				}
			}
			if (isset($MyRow['lastsale'])){		
				if ($LastSale < $MyRow['lastsale']){
					$LastSale = $MyRow['lastsale'];
				}
			}
			$TotalSales = $TotalSales + $MyRow['totalsales'];
			$ValueSales = $ValueSales + $MyRow['valuesales'];
			
			if (isset($MyRow['firstsaletoday'])){		
				if ($FirstSaleToday > $MyRow['firstsaletoday']){
					$FirstSaleToday = $MyRow['firstsaletoday'];
				}
			}
			if (isset($MyRow['lastsaletoday'])){		
				if ($LastSaleToday < $MyRow['lastsaletoday']){
					$LastSaleToday = $MyRow['lastsaletoday'];
				}
			}
			$TotalSalesToday = $TotalSalesToday + $MyRow['totalsalestoday'];
			$ValueSalesToday = $ValueSalesToday + $MyRow['valuesalestoday'];
			
		}
		
		echo '</tbody>
			<tfooter>';

		if (!$ShowHeader){
			echo '<tr>
					<td class="number"></td>
					<td>TOTALS</td>
					<td class="number">' . locale_number_format_zero_blank($TotalSalesFull/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($ValueSalesFull/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalSales/$numDays,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($ValueSales/$numDays,0) . '</td>
					<td class="number">' . $FirstSaleToday . '</td>
					<td class="number">' . $LastSaleToday . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalSalesToday,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($ValueSalesToday,0) . '</td>
					</tr>';
			
			$TotalPercent = ($ValueSalesFull != 0)? ($ValueSales/$ValueSalesFull*100) : 0 ;
			$TodayRythm = ($ValueSales != 0) ? ($ValueSalesToday/($ValueSales/$numDays)*100) : 0 ;

			echo '<tr>
					<td class="number"></td>
					<td></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number">' . locale_number_format_zero_blank($TotalPercent,0) . '%</td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number">' . locale_number_format_zero_blank($TodayRythm,0) . '%</td>
					</tr>';

			$TodayForecast = ($ValueSalesToday != 0) ? (round($ValueSalesFull/$ValueSales*$ValueSalesToday/JUTA)*JUTA) : 0 ;
			
			echo '<tr>
					<td class="number"></td>
					<td>Today Forecast</td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number">' . locale_number_format_zero_blank($TodayForecast,0) . '</td>
					</tr>';
			echo '</tfooter></table></div>';
		}
	}
}

?>
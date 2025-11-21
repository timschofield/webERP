<?php
require(__DIR__ . '/includes/session.php');

$Title = __('KL Sales Hourly Report');
include('includes/header.php');

include('includes/KLDefines.php');
include('includes/KLCountriesForRetail.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLRetailCustomer.php');
include('includes/KLUIGeneralFunctions.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin 
	OR $KL_GeneralAffairsManager 
	OR $KL_SalesTeamManager
	OR $KL_ShopManager){
	HourlyPerformance(15,$RootPath);
	$NumberOfTestExecuted++;
}

prnMsg("Performed ". $NumberOfTestExecuted . " Sales Hourly Report",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include('includes/footer.php');

function HourlyPerformance($numDays, $RootPath){

	$Today = date('Y-m-d');
	$Now = date ('H:i:s');
	$Yesterday = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

	/* SQL optimized by Claude Sonnet 4.0 23/08/2025 from around 4,25 secs to 1,5 secs*/
	$SQL = "SELECT dm.name,
				MIN(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
					THEN so.ordtime END) AS firstsalefull,
				MAX(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
					THEN so.ordtime END) AS lastsalefull,
				COUNT(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
					THEN 1 END) AS totalsalesfull,
				SUM(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
					THEN so.klpaidcash + so.klpaidcreditcard END) AS valuesalesfull,
				MIN(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
					AND so.ordtime <= '" . $Now . "' THEN so.ordtime END) AS firstsale,
				MAX(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
					AND so.ordtime <= '" . $Now . "' THEN so.ordtime END) AS lastsale,
				COUNT(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
					AND so.ordtime <= '" . $Now . "' THEN 1 END) AS totalsales,
				SUM(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
					AND so.ordtime <= '" . $Now . "' THEN so.klpaidcash + so.klpaidcreditcard END) AS valuesales,
				MIN(CASE WHEN so.orddate = CURRENT_DATE THEN so.ordtime END) AS firstsaletoday,
				MAX(CASE WHEN so.orddate = CURRENT_DATE THEN so.ordtime END) AS lastsaletoday,
				COUNT(CASE WHEN so.orddate = CURRENT_DATE THEN 1 END) AS totalsalestoday,
				SUM(CASE WHEN so.orddate = CURRENT_DATE THEN so.klpaidcash + so.klpaidcreditcard END) AS valuesalestoday
			FROM debtorsmaster dm
			LEFT JOIN salesorders so 
				ON dm.debtorno = so.debtorno 
					AND (so.orddate >= '" . $InitialDate . "' OR so.orddate = CURRENT_DATE)
			WHERE dm.typeid IN (" . CUSTOMER_TYPE_RETAIL . ")
			GROUP BY dm.debtorno,
				dm.name
			ORDER BY SUM(CASE WHEN so.orddate >= '" . $InitialDate . "' AND so.orddate <= '" . $Yesterday . "' 
				THEN so.klpaidcash + so.klpaidcreditcard END) DESC, dm.debtorno";

	$Result = DB_query($SQL);
	$ShowHeader = true;
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
				$TableTitleText = __('Hourly Sales Performance until '. $Now);
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<thead>
									<tr>
										<th>#</th>
										<th>' . __('Shop') . '</th>
										<th colspan=2>' . 'Last ' . $numDays . ' days</th>
										<th colspan=2>' . 'Last ' . $numDays . ' days until ' . $Now . '</th>
										<th colspan=4>' . 'Today' . '</th>
									</tr>
									<tr>
										<th class="SortedColumn">#</th>
										<th class="SortedColumn">' . __('Name') . '</th>
										<th class="SortedColumn">' . __('# Sales') . '</th>
										<th class="SortedColumn">' . __('Value Sales') . '</th>
										<th class="SortedColumn">' . __('# Sales') . '</th>
										<th class="SortedColumn">' . __('Value Sales') . '</th>
										<th class="SortedColumn">' . __('First Sale ') . '</th>
										<th class="SortedColumn">' . __('Last Sale ') . '</th>
										<th class="SortedColumn">' . __('# Sales ') . '</th>
										<th class="SortedColumn">' . __('Value Sales') . '</th>
									</tr>
								</thead>
								<tbody>';
				echo $TableHeader;
				$ShowHeader = false;
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

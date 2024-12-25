<?php
define("VERSIONFILE", "1.01");

include ('includes/session.php');
$Title = _('Kapal-Laut Sales Hourly Report '. VERSIONFILE);
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLCountriesForRetail.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLRetailCustomer.php');

/* ASSIGN users to groups */
include ('includes/KLRoles.php');

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
					AND salesorders.orddate = '". $Today ."') AS firstsaletoday,
				(SELECT MAX(salesorders.ordtime)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."') AS lastsaletoday,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."') AS totalsalestoday,
				(SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."') AS valuesalestoday
			FROM debtorsmaster
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			ORDER BY (SELECT SUM(klpaidcash+klpaidcreditcard)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."') DESC,
				debtorsmaster.debtorno";

	$result = DB_query($SQL);
	$showHeader = TRUE;
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
	
	if (DB_num_rows($result) != 0){
		$i = 0; // row counter
		while ($myrow = DB_fetch_array($result)) {
			if ($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' .'Hourly Sales Performance until '. $Now .'</strong></p>';
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
				$showHeader = FALSE;
			}
			$i++;
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format_zero_blank($i,0),
					$myrow['name'],
					locale_number_format_zero_blank($myrow['totalsalesfull']/$numDays,0),
					locale_number_format_zero_blank($myrow['valuesalesfull']/$numDays,0),
					locale_number_format_zero_blank($myrow['totalsales']/$numDays,0),
					locale_number_format_zero_blank($myrow['valuesales']/$numDays,0),
					$myrow['firstsaletoday'],
					$myrow['lastsaletoday'],
					locale_number_format_zero_blank($myrow['totalsalestoday'],0),
					locale_number_format_zero_blank($myrow['valuesalestoday'],0)
					);
					
			if (isset($myrow['firstsalefull'])){		
				if ($FirstSaleFull > $myrow['firstsalefull']){
					$FirstSaleFull = $myrow['firstsalefull'];
				}
			}
			if (isset($myrow['lastsalefull'])){		
				if ($LastSaleFull < $myrow['lastsalefull']){
					$LastSaleFull = $myrow['lastsalefull'];
				}
			}
			$TotalSalesFull = $TotalSalesFull + $myrow['totalsalesfull'];
			$ValueSalesFull = $ValueSalesFull + $myrow['valuesalesfull'];

			if (isset($myrow['firstsale'])){		
				if ($FirstSale > $myrow['firstsale']){
					$FirstSale = $myrow['firstsale'];
				}
			}
			if (isset($myrow['lastsale'])){		
				if ($LastSale < $myrow['lastsale']){
					$LastSale = $myrow['lastsale'];
				}
			}
			$TotalSales = $TotalSales + $myrow['totalsales'];
			$ValueSales = $ValueSales + $myrow['valuesales'];
			
			if (isset($myrow['firstsaletoday'])){		
				if ($FirstSaleToday > $myrow['firstsaletoday']){
					$FirstSaleToday = $myrow['firstsaletoday'];
				}
			}
			if (isset($myrow['lastsaletoday'])){		
				if ($LastSaleToday < $myrow['lastsaletoday']){
					$LastSaleToday = $myrow['lastsaletoday'];
				}
			}
			$TotalSalesToday = $TotalSalesToday + $myrow['totalsalestoday'];
			$ValueSalesToday = $ValueSalesToday + $myrow['valuesalestoday'];
			
		}
		
		echo '</tbody>
			<tfooter>';

		if (!$showHeader){
			printf('<tr>
					<td class="number">%s</td>
					<td>%s</td>
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
					'TOTALS',
					locale_number_format_zero_blank($TotalSalesFull/$numDays,0),
					locale_number_format_zero_blank($ValueSalesFull/$numDays,0),
					locale_number_format_zero_blank($TotalSales/$numDays,0),
					locale_number_format_zero_blank($ValueSales/$numDays,0),
					$FirstSaleToday,
					$LastSaleToday,
					locale_number_format_zero_blank($TotalSalesToday,0),
					locale_number_format_zero_blank($ValueSalesToday,0)
					);
			
			$TotalPercent = ($ValueSalesFull != 0)? ($ValueSales/$ValueSalesFull*100) : 0 ;
			$TodayRythm = ($ValueSales != 0) ? ($ValueSalesToday/($ValueSales/$numDays)*100) : 0 ;

			printf('<tr>
					<td class="number">%s</td>
					<td>%s</td>
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
					'',
					'',
					'',
					locale_number_format_zero_blank($TotalPercent,0).'%',
					'',
					'',
					'',
					locale_number_format_zero_blank($TodayRythm,0).'%'
					);

			$TodayForecast = ($ValueSalesToday != 0) ? (round($ValueSalesFull/$ValueSales*$ValueSalesToday/JUTA)*JUTA) : 0 ;
			
			printf('<tr>
					<td class="number">%s</td>
					<td>%s</td>
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
					'Today Forecast',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					locale_number_format_zero_blank($TodayForecast,0)
					);
			echo '</tfooter></table></div>';
		}
	}
}

?>
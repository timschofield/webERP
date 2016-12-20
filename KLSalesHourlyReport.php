<?php
define("VERSIONFILE", "1.00");

include ('includes/session.inc');
$Title = _('Kapal-Laut Sales Hourly Report '. VERSIONFILE);
include('includes/header.inc');
include('includes/KLDefines.php');
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
	HourlyPerformance(15,$RootPath, $db);
	$NumberOfTestExecuted++;
	HourlySales(15,$RootPath, $db);
	$NumberOfTestExecuted++;
}


prnMsg("Performed ". $NumberOfTestExecuted . " Sales Hourly Report",'success');
time_finish($begintime);

include ('includes/footer.inc');

function HourlySales($numDays, $RootPath, $db){

	$Today = date('Y-m-d');
	$Yesterday = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

	$SQL = "SELECT debtorno,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '09:00:00'
					AND salesorders.ordtime < '10:00:00') AS sales09,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '10:00:00'
					AND salesorders.ordtime <  '11:00:00') AS sales10,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '11:00:00'
					AND salesorders.ordtime <  '12:00:00') AS sales11,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '12:00:00'
					AND salesorders.ordtime <  '13:00:00') AS sales12,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '13:00:00'
					AND salesorders.ordtime <  '14:00:00') AS sales13,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '14:00:00'
					AND salesorders.ordtime <  '15:00:00') AS sales14,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '15:00:00'
					AND salesorders.ordtime <  '16:00:00') AS sales15,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '16:00:00'
					AND salesorders.ordtime <  '17:00:00') AS sales16,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '17:00:00'
					AND salesorders.ordtime <  '18:00:00') AS sales17,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '18:00:00'
					AND salesorders.ordtime <  '19:00:00') AS sales18,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '19:00:00'
					AND salesorders.ordtime <  '20:00:00') AS sales19,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '20:00:00'
					AND salesorders.ordtime <  '21:00:00') AS sales20,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '21:00:00'
					AND salesorders.ordtime <  '22:00:00') AS sales21,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '22:00:00'
					AND salesorders.ordtime <  '23:00:00') AS sales22,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '23:00:00'
					AND salesorders.ordtime <  '24:00:00') AS sales23,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '09:00:00'
					AND salesorders.ordtime < '10:00:00') AS today09,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '10:00:00'
					AND salesorders.ordtime <  '11:00:00') AS today10,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '11:00:00'
					AND salesorders.ordtime <  '12:00:00') AS today11,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '12:00:00'
					AND salesorders.ordtime <  '13:00:00') AS today12,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '13:00:00'
					AND salesorders.ordtime <  '14:00:00') AS today13,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '14:00:00'
					AND salesorders.ordtime <  '15:00:00') AS today14,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '15:00:00'
					AND salesorders.ordtime <  '16:00:00') AS today15,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '16:00:00'
					AND salesorders.ordtime <  '17:00:00') AS today16,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '17:00:00'
					AND salesorders.ordtime <  '18:00:00') AS today17,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '18:00:00'
					AND salesorders.ordtime <  '19:00:00') AS today18,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '19:00:00'
					AND salesorders.ordtime <  '20:00:00') AS today19,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '20:00:00'
					AND salesorders.ordtime <  '21:00:00') AS today20,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '21:00:00'
					AND salesorders.ordtime <  '22:00:00') AS today21,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '22:00:00'
					AND salesorders.ordtime <  '23:00:00') AS today22,
				(SELECT COUNT(*)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."'
					AND salesorders.ordtime >= '23:00:00'
					AND salesorders.ordtime <  '24:00:00') AS today23	
			FROM debtorsmaster
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
				AND debtorsmaster.debtorno LIKE 'RETAIL%'
			ORDER BY debtorsmaster.debtorno";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' .'Daily Number of sales by hour of the day for the last ' . $numDays . ' days</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
			}
			if (($showHeader) OR ($i % 3 == 1)){
				$TableHeader = '<tr>
									<th class="ascending">' . _('Shop') . '</th>
									<th class="ascending">' . _('Type') . '</th>
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
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$myrow['debtorno'],
					'Hourly '. $numDays . ' days',
					locale_number_format_zero_blank($myrow['sales09']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales10']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales11']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales12']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales13']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales14']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales15']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales16']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales17']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales18']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales19']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales20']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales21']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales22']/$numDays,1),
					locale_number_format_zero_blank($myrow['sales23']/$numDays,1)
					);

			$Acum09 = $myrow['sales09'];
			$Acum10 = $Acum09 + $myrow['sales10'];
			$Acum11 = $Acum10 + $myrow['sales11'];
			$Acum12 = $Acum11 + $myrow['sales12'];
			$Acum13 = $Acum12 + $myrow['sales13'];
			$Acum14 = $Acum13 + $myrow['sales14'];
			$Acum15 = $Acum14 + $myrow['sales15'];
			$Acum16 = $Acum15 + $myrow['sales16'];
			$Acum17 = $Acum16 + $myrow['sales17'];
			$Acum18 = $Acum17 + $myrow['sales18'];
			$Acum19 = $Acum18 + $myrow['sales19'];
			$Acum20 = $Acum19 + $myrow['sales20'];
			$Acum21 = $Acum20 + $myrow['sales21'];
			$Acum22 = $Acum21 + $myrow['sales22'];
			$Acum23 = $Acum22 + $myrow['sales23'];
			
			$k = StartSameColourRow($k);
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'',
					'Acumulated '. $numDays . ' days',
					locale_number_format_zero_blank($Acum09/$numDays,1),
					locale_number_format_zero_blank($Acum10/$numDays,1),
					locale_number_format_zero_blank($Acum11/$numDays,1),
					locale_number_format_zero_blank($Acum12/$numDays,1),
					locale_number_format_zero_blank($Acum13/$numDays,1),
					locale_number_format_zero_blank($Acum14/$numDays,1),
					locale_number_format_zero_blank($Acum15/$numDays,1),
					locale_number_format_zero_blank($Acum16/$numDays,1),
					locale_number_format_zero_blank($Acum17/$numDays,1),
					locale_number_format_zero_blank($Acum18/$numDays,1),
					locale_number_format_zero_blank($Acum19/$numDays,1),
					locale_number_format_zero_blank($Acum20/$numDays,1),
					locale_number_format_zero_blank($Acum21/$numDays,1),
					locale_number_format_zero_blank($Acum22/$numDays,1),
					locale_number_format_zero_blank($Acum23/$numDays,1)
					);
			$k = StartSameColourRow($k);
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'',
					'Hourly today',
					locale_number_format_zero_blank($myrow['today09'],1),
					locale_number_format_zero_blank($myrow['today10'],1),
					locale_number_format_zero_blank($myrow['today11'],1),
					locale_number_format_zero_blank($myrow['today12'],1),
					locale_number_format_zero_blank($myrow['today13'],1),
					locale_number_format_zero_blank($myrow['today14'],1),
					locale_number_format_zero_blank($myrow['today15'],1),
					locale_number_format_zero_blank($myrow['today16'],1),
					locale_number_format_zero_blank($myrow['today17'],1),
					locale_number_format_zero_blank($myrow['today18'],1),
					locale_number_format_zero_blank($myrow['today19'],1),
					locale_number_format_zero_blank($myrow['today20'],1),
					locale_number_format_zero_blank($myrow['today21'],1),
					locale_number_format_zero_blank($myrow['today22'],1),
					locale_number_format_zero_blank($myrow['today23'],1)
					);
			$Acum09 = $myrow['today09'];
			$Acum10 = $Acum09 + $myrow['today10'];
			$Acum11 = $Acum10 + $myrow['today11'];
			$Acum12 = $Acum11 + $myrow['today12'];
			$Acum13 = $Acum12 + $myrow['today13'];
			$Acum14 = $Acum13 + $myrow['today14'];
			$Acum15 = $Acum14 + $myrow['today15'];
			$Acum16 = $Acum15 + $myrow['today16'];
			$Acum17 = $Acum16 + $myrow['today17'];
			$Acum18 = $Acum17 + $myrow['today18'];
			$Acum19 = $Acum18 + $myrow['today19'];
			$Acum20 = $Acum19 + $myrow['today20'];
			$Acum21 = $Acum20 + $myrow['today21'];
			$Acum22 = $Acum21 + $myrow['today22'];
			$Acum23 = $Acum22 + $myrow['today23'];

			$k = StartSameColourRow($k);
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'',
					'Acumulated today',
					locale_number_format_zero_blank($Acum09,1),
					locale_number_format_zero_blank($Acum10,1),
					locale_number_format_zero_blank($Acum11,1),
					locale_number_format_zero_blank($Acum12,1),
					locale_number_format_zero_blank($Acum13,1),
					locale_number_format_zero_blank($Acum14,1),
					locale_number_format_zero_blank($Acum15,1),
					locale_number_format_zero_blank($Acum16,1),
					locale_number_format_zero_blank($Acum17,1),
					locale_number_format_zero_blank($Acum18,1),
					locale_number_format_zero_blank($Acum19,1),
					locale_number_format_zero_blank($Acum20,1),
					locale_number_format_zero_blank($Acum21,1),
					locale_number_format_zero_blank($Acum22,1),
					locale_number_format_zero_blank($Acum23,1)
					);
					$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function HourlyPerformance($numDays, $RootPath, $db){

	$Today = date('Y-m-d');
	$Now = date ('H:i:s');
	$Yesterday = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

	$SQL = "SELECT debtorno,
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
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
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
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
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
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate = '". $Today ."') AS valuesalestoday
			FROM debtorsmaster
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
				AND debtorsmaster.debtorno LIKE 'RETAIL%'
			ORDER BY (SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
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
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if ($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' .'Hourly Sales Performance until '. $Now .'</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th class="ascending">' . _('Shop') . '</th>
									<th class="ascending" colspan=4>' . 'Last ' . $numDays . ' days</th>
									<th class="ascending" colspan=4>' . 'Last ' . $numDays . ' days until ' . $Now . '</th>
									<th class="ascending" colspan=4>' . 'Today' . '</th>
								</tr>
								<tr>
									<th class="ascending">' . _('Name') . '</th>
									<th class="ascending">' . _('First Sale') . '</th>
									<th class="ascending">' . _('Last Sale') . '</th>
									<th class="ascending">' . _('# Sales') . '</th>
									<th class="ascending">' . _('Value Sales') . '</th>
									<th class="ascending">' . _('First Sale') . '</th>
									<th class="ascending">' . _('Last Sale') . '</th>
									<th class="ascending">' . _('# Sales') . '</th>
									<th class="ascending">' . _('Value Sales') . '</th>
									<th class="ascending">' . _('First Sale ') . '</th>
									<th class="ascending">' . _('Last Sale ') . '</th>
									<th class="ascending">' . _('# Sales ') . '</th>
									<th class="ascending">' . _('Value Sales') . '</th>
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
					$myrow['debtorno'],
					$myrow['firstsalefull'],
					$myrow['lastsalefull'],
					locale_number_format_zero_blank($myrow['totalsalesfull']/$numDays,0),
					locale_number_format_zero_blank($myrow['valuesalesfull']/$numDays,0),
					$myrow['firstsale'],
					$myrow['lastsale'],
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
		if (!$showHeader){
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
					'TOTALS',
					$FirstSaleFull,
					$LastSaleFull,
					locale_number_format_zero_blank($TotalSalesFull/$numDays,0),
					locale_number_format_zero_blank($ValueSalesFull/$numDays,0),
					$FirstSale,
					$LastSale,
					locale_number_format_zero_blank($TotalSales/$numDays,0),
					locale_number_format_zero_blank($ValueSales/$numDays,0),
					$FirstSaleToday,
					$LastSaleToday,
					locale_number_format_zero_blank($TotalSalesToday,0),
					locale_number_format_zero_blank($ValueSalesToday,0)
					);
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
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					locale_number_format_zero_blank($TotalSales/$TotalSalesFull*100,0).'%',
					locale_number_format_zero_blank($ValueSales/$ValueSalesFull*100,0).'%',
					'',
					'',
					locale_number_format_zero_blank($TotalSalesToday/($TotalSales/$numDays)*100,0).'%',
					locale_number_format_zero_blank($ValueSalesToday/($ValueSales/$numDays)*100,0).'%'
					);
			echo '</table>
				</div>';
		}
	}
}


?>
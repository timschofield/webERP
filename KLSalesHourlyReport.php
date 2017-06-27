<?php
define("VERSIONFILE", "1.00");

include ('includes/session.php');
$Title = _('Kapal-Laut Sales Hourly Report '. VERSIONFILE);
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLCountriesForRetail.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLRetailCustomer.php');

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

	RetailCustomerAnalysisByCountry(-1, "ALL", "ALL", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(-1, "KAPAL-LAUT", "ALL", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(-1, "BLINK", "ALL", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(-1, "OUTLET", "ALL", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	
	RetailCustomerAnalysisByCountry(-1, "ALL", "CANGGU", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(-1, "ALL", "KUTA", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(-1, "ALL", "SEMINYAK", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(-1, "ALL", "SANUR", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;
	RetailCustomerAnalysisByCountry(-1, "ALL", "UBUD", 0, $CountriesForRetail, $db);
	$NumberOfTestExecuted++;

	RetailCustomerAnalysisBySex(-1, "ALL", $db);
	$NumberOfTestExecuted++;

	RetailCustomerAnalysisByAge(-1, "ALL", $db);
	$NumberOfTestExecuted++;

	HourlySales(30,$RootPath, $db);
	$NumberOfTestExecuted++;
	
}


prnMsg("Performed ". $NumberOfTestExecuted . " Sales Hourly Report",'success');
time_finish($begintime);

include ('includes/footer.php');

function HourlySales($numDays, $RootPath, $db){

	$Today = date('Y-m-d');
	$Yesterday = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

	$SQL = "SELECT debtorsmaster.debtorno,
				locations.zone,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '09:00:00'
					AND salesorders.ordtime < '10:00:00') AS sales09,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '10:00:00'
					AND salesorders.ordtime <  '11:00:00') AS sales10,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '11:00:00'
					AND salesorders.ordtime <  '12:00:00') AS sales11,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '12:00:00'
					AND salesorders.ordtime <  '13:00:00') AS sales12,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '13:00:00'
					AND salesorders.ordtime <  '14:00:00') AS sales13,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '14:00:00'
					AND salesorders.ordtime <  '15:00:00') AS sales14,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '15:00:00'
					AND salesorders.ordtime <  '16:00:00') AS sales15,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '16:00:00'
					AND salesorders.ordtime <  '17:00:00') AS sales16,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '17:00:00'
					AND salesorders.ordtime <  '18:00:00') AS sales17,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '18:00:00'
					AND salesorders.ordtime <  '19:00:00') AS sales18,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '19:00:00'
					AND salesorders.ordtime <  '20:00:00') AS sales19,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '20:00:00'
					AND salesorders.ordtime <  '21:00:00') AS sales20,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '21:00:00'
					AND salesorders.ordtime <  '22:00:00') AS sales21,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
				FROM salesorders
				WHERE salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.orddate >= '". $InitialDate ."'
					AND salesorders.orddate <= '". $Yesterday ."'
					AND salesorders.ordtime >= '22:00:00'
					AND salesorders.ordtime <  '23:00:00') AS sales22,
				(SELECT SUM(klpaidcash+klpaidcreditcard+klreturnedgoods+klvouchers)
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
				AND locations.zone NOT IN ". ZONES_OF_KANTOR . "
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			ORDER BY locations.zone, 
				debtorsmaster.debtorno";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$GrandTotal = 0;
	
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
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
		
		while ($myrow = DB_fetch_array($result)) {
			if ($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' .'Hourly sales and value for the last ' . $numDays . ' days</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
			}
			if (($showHeader) OR ($i % 10 == 1)){
				$TableHeader = '<tr>
									<th class="ascending">' . _('Zone') . '</th>
									<th class="ascending">' . _('Shop') . '</th>
									<th class="ascending">' . _('Type') . '</th>
									<th class="ascending">' . _('First Sale') . '</th>
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
			$TotalSales = $myrow['sales09'] +
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
					<td>%s</td>
					</tr>', 
					$myrow['zone'],
					$myrow['debtorno'],
					'Sales',
					$myrow['firstsale'],
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

/*			$k = StartSameColourRow($k);
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
					<td>%s</td>
					</tr>', 
					'',
					'',
					'Daily Value',
					'',
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
					''
					);
*/					
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
				<td>%s</td>
				</tr>', 
				'TOTALS',
				'',
				'',
				'',
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
				<td>%s</td>
				</tr>', 
				'CUMULATIVE',
				'',
				'',
				'',
				locale_number_format_zero_blank($Total09/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21+$Total22)/$GrandTotal*100,0).'%',
				locale_number_format_zero_blank(($Total09+$Total10+$Total11+$Total12+$Total13+$Total14+$Total15+$Total16+$Total17+$Total18+$Total19+$Total20+$Total21+$Total22+$Total23)/$GrandTotal*100,0).'%',
				''
				);
				
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
									<th class="ascending" colspan=2>' . 'Last ' . $numDays . ' days</th>
									<th class="ascending" colspan=2>' . 'Last ' . $numDays . ' days until ' . $Now . '</th>
									<th class="ascending" colspan=4>' . 'Today' . '</th>
								</tr>
								<tr>
									<th class="ascending">' . _('Name') . '</th>
									<th class="ascending">' . _('# Sales') . '</th>
									<th class="ascending">' . _('Value Sales') . '</th>
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
					</tr>', 
					$myrow['debtorno'],
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
					</tr>', 
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
					</tr>', 
					'',
					'',
					'',
					'',
					locale_number_format_zero_blank($ValueSales/$ValueSalesFull*100,0).'%',
					'',
					'',
					'',
					locale_number_format_zero_blank($ValueSalesToday/($ValueSales/$numDays)*100,0).'%'
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
					</tr>', 
					'Today Forecast',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					locale_number_format_zero_blank(round($ValueSalesFull/$ValueSales*$ValueSalesToday/JUTA)*JUTA,0)
					);
			echo '</table>
				</div>';
		}
	}
}


?>
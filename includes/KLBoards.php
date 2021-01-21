<?php

/**************************************************************************************************
			FUNCTIONS RELATED CONTROL, PERFORMANCE OR OTHER KL BOARDS
**************************************************************************************************/

function ActiveTransfersByLocation($RootPath, $db){
	$TotalTransferIn = 0;
	$TotalTransferOut = 0;
	$TotalPcsIn = 0;
	$TotalPcsOut = 0;
	
	$SQL = "SELECT locations.locationname,
			(SELECT SUM(shipqty-recqty)
				FROM loctransfers
				WHERE  recqty < shipqty
					AND loctransfers.shiploc = locations.loccode) as qtyout,
			(SELECT SUM(shipqty-recqty)
				FROM loctransfers
				WHERE  recqty < shipqty
					AND loctransfers.recloc = locations.loccode) as qtyin,
			(SELECT COUNT(DISTINCT(reference))
				FROM loctransfers
				WHERE  recqty < shipqty
					AND loctransfers.shiploc = locations.loccode) as transferout,
			(SELECT COUNT(DISTINCT(reference))
				FROM loctransfers
				WHERE  recqty < shipqty
					AND loctransfers.recloc = locations.loccode) as transferin
			FROM locations
			WHERE locations.typeloc IN " . ALL_SHOPS_LIST_BY_TYPE . "
			ORDER BY (SELECT SUM(shipqty-recqty)
				FROM loctransfers
				WHERE  recqty < shipqty
					AND (loctransfers.shiploc = locations.loccode OR loctransfers.recloc = locations.loccode)) DESC";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Pending Goods to be transferred by shop') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Transfer OUT') . '</th>
							<th class="ascending">' . _('Transfer IN') . '</th>
							<th class="ascending">' . _('Transfer Total') . '</th>
							<th class="ascending">' . _('Pcs OUT') . '</th>
							<th class="ascending">' . _('Pcs IN') . '</th>
							<th class="ascending">' . _('Pcs Total') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$TotalTransferIn = $TotalTransferIn + $myrow['transferin'];
			$TotalTransferOut = $TotalTransferOut + $myrow['transferout'];
			$TotalPcsIn = $TotalPcsIn + $myrow['qtyin'];
			$TotalPcsOut = $TotalPcsOut + $myrow['qtyout'];

			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$myrow['locationname'], 
					locale_number_format($myrow['transferout'],0),
					locale_number_format($myrow['transferin'],0),
					locale_number_format($myrow['transferout']+$myrow['transferin'],0),
					locale_number_format($myrow['qtyout'],0),
					locale_number_format($myrow['qtyin'],0),
					locale_number_format($myrow['qtyout']+$myrow['qtyin'],0)
					);
			$i++;
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'Total', 
				locale_number_format($TotalTransferOut,0),
				locale_number_format($TotalTransferIn,0),
				locale_number_format($TotalTransferOut+$TotalTransferIn,0),
				locale_number_format($TotalPcsOut,0),
				locale_number_format($TotalPcsIn,0),
				locale_number_format($TotalPcsOut+$TotalPcsIn,0)
				);
		
		echo '</table>
				</div>
				</form>';
	}
}

function ActiveTransferStatus($RootPath, $db){
	$SQL = "SELECT reference,
					shipdate,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = shiploc)AS locfrom,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = recloc)AS locto,
					SUM(shipqty-recqty) AS pendingqty
			FROM loctransfers
			WHERE  recqty < shipqty
			GROUP BY reference
			ORDER BY shipdate ASC, reference ASC";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('List of Active Transfers') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Qty') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$total = 0;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					ConvertSQLDateTime($myrow['shipdate']), 
					$CodeLink, 
					$myrow['locfrom'], 
					$myrow['locto'], 
					locale_number_format($myrow['pendingqty'],0)
					);
			$i++;
			$total = $total + $myrow['pendingqty'];
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'', 
				'', 
				'', 
				'Total', 
				locale_number_format($total,0)
				);
		echo '</table>
				</div>
				</form>';
	}
}

function AverageSales($typereport, $NumDaysA, $NumDaysB, $NumDaysC, $NumDaysD, $NumDaysE, $NumDaysF, $NumDaysSort, $Year, $Shop, $db){

	if ($Year == "LastYear"){
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-365-1));
		$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-365));
		$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB-365));
		$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysC-365));
		$StartDateD = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysD-365));
		$StartDateE = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysE-365));
		$StartDateF = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysF-365));
		$StartDateSort = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysSort-365));
		$StartDateMTD=FormatDateForSQL(Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y'))));
	}else{
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA));
		$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB));
		$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysC));
		$StartDateD = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysD));
		$StartDateE = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysE));
		$StartDateF = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysF));
		$StartDateSort = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysSort));
		$StartDateMTD=FormatDateForSQL(Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y'))));
	}

	$TotalDateA = 0;
	$TotalDateB = 0;
	$TotalDateC = 0;
	$TotalDateD = 0;
	$TotalDateE = 0;
	$TotalDateF = 0;
	$TotalForecast = 0;
	$TotalMTD = 0;
	
	if ($Shop == "All"){
		$SQLByShop = "";
	}else{
		$SQLByShop = " AND salesorders.debtorno =  '". $Shop . "' ";
	}

	if ($typereport == "Shop"){
		$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateB . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesB,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateC . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesC,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateD . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesD,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateE . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesE,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateF . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesF,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >=  '". $StartDateMTD . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesMTD
				FROM debtorsmaster
				WHERE debtorsmaster.typeid = 2
				ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateSort . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) DESC";
	}elseif ($typereport == "Online"){
		$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))/currencies.rate
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))/currencies.rate
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateB . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesB,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))/currencies.rate
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateC . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesC,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))/currencies.rate
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateD . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesD,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))/currencies.rate
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateE . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesE,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))/currencies.rate
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateF . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesF,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))/currencies.rate
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >=  '". $StartDateMTD . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.debtorno = debtorsmaster.debtorno) AS salesMTD
				FROM debtorsmaster
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
				WHERE debtorsmaster.typeid = 9 
					OR debtorsmaster.typeid = 10
				ORDER BY debtorsmaster.debtorno";	
	}else{
		$SQL = "SELECT salesmancode,
					salesmanname,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >= '". $StartDateA . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesA,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >= '". $StartDateB . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesB,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >= '". $StartDateC . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesC,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >= '". $StartDateD . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesD,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >= '". $StartDateE . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesE,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >= '". $StartDateF . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesF,
					(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1 ".
							$SQLByShop . "
							AND salesorders.orddate >=  '". $StartDateMTD . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) AS salesMTD
				FROM salesman
				WHERE salesman.current = 1
				ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDateSort . "'
							AND salesorders.orddate <= '". $Yesterday . "'
							AND salesorders.salesperson = salesman.salesmancode) DESC";
	}
	
	$result = DB_query($SQL);
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
			$TitleTarget = "";
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . $typereport . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . $NumDaysA . _(' days') . '</th>
							<th class="ascending">' . $NumDaysB . _(' days') . '</th>
							<th class="ascending">' . $NumDaysC . _(' days') . '</th>
							<th class="ascending">' . $NumDaysD . _(' days') . '</th>
							<th class="ascending">' . $NumDaysE . _(' days') . '</th>
							<th class="ascending">' . $NumDaysF . _(' days') . '</th>
							<th class="ascending">' . _('MTD') . '</th>
							<th class="ascending">' . _('Trend') . '</th>
							<th class="ascending">' . 'Forecast '. $NumDaysC . _(' days') . '</th>
							<th class="ascending">' . $TitleTarget . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			
			$target = "";
			if (($typereport == "Shop") OR ($typereport == "Online")){
				$Code = $myrow['debtorno'];
				$Name = $myrow['name'];
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
			$MTD = locale_number_format($myrow['salesMTD'], 0);
			
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
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
					$MTD,
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
			$TotalDateMTD = $TotalDateMTD +$myrow['salesMTD'];
			$TotalForecast = $TotalForecast + round($myrow['salesC'], -5);
			$i++;
		}
		if (($typereport == "Shop") OR ($typereport == "Online")){
			$percent = ($TotalDateD-$TotalDateC)/$TotalDateC * 100;
			$trend = " ";
			if ($percent > 0){
				$trend = "Improving ". locale_number_format($percent,0) . "%";
			}
			if ($percent < 0){
				$trend = "Degrading ". locale_number_format($percent,0) . "%";
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
					locale_number_format($TotalDateMTD,0),
					$trend,
					locale_number_format($TotalForecast,0),
					""
					);
		}
		echo '</table>
				</div>';
	}
}

function ChangeItemStandardCost($StockID, $NewCost, $OldCost, $QOH){
	$Result = DB_Txn_Begin();
	ItemCostUpdateGL($db, $StockID, $NewCost, $OldCost, $QOH);
	$SQL = "UPDATE stockmaster SET	materialcost='" . $NewCost . "',
									labourcost='" . 0 . "',
									overheadcost='" . 0 . "',
									lastcost='" . $OldCost . "',
									lastcostupdate ='" . Date('Y-m-d')."'
							WHERE stockid='" . $StockID . "'";

	$ErrMsg = _('The cost details for the stock item could not be updated because');
	$DbgMsg = _('The SQL that failed was');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	$Result = DB_Txn_Commit();
	UpdateCost($db, $StockID); //Update any affected BOMs
}

function ComponentsToObsolete($ShowOnlyTotal, $ShowLimit, $RootPath, $db){
	$SQL = "SELECT s.stockid,
					s.units,
					s.description,
					(s.materialcost + s.labourcost + s.overheadcost) AS stdcost,
					(SELECT SUM(quantity)
						FROM locstock
						WHERE s.stockid = locstock.stockid) AS qoh
			FROM stockmaster AS s
			WHERE s.categoryid IN " . LIST_STOCK_CATEGORIES_COMPONENTS . "
				AND s.discontinued = 0
				AND NOT EXISTS(
					SELECT bom.component
					FROM bom,stockmaster AS stP, stockmaster AS stC
					WHERE bom.parent = stP.stockid
						AND bom.component = stC.stockid 
						AND s.stockid = bom.component
						AND stP.discontinued = 0)";
	$result = DB_query($SQL);
	$totalcost = 0;
	if (DB_num_rows($result) != 0){
		if (!$ShowOnlyTotal){
			echo '<p class="page_title_text" align="center"><strong>' . _('Components NOT Used in any BOM. Use them in any product (IF QOH > 0) OR flag as obsolete (IF QOH = 0).') . '</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Code') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('QOH') . '</th>
								<th class="ascending">' . _('UOM') . '</th>
								<th class="ascending">' . _('Stock value') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$totalcost = $totalcost + ($myrow['qoh']*$myrow['stdcost']);
			if (!$ShowOnlyTotal){
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'],
						locale_number_format($myrow['qoh'],0),
						$myrow['units'],
						locale_number_format($myrow['qoh']*$myrow['stdcost'],0)
						);
			}
			$i++;
		}
		if (!$ShowOnlyTotal){
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					'', 
					'', 
					'Total Cost',
					'',
					'',
					locale_number_format($totalcost,0)
					);
			echo '</table>
					</div>';
		}elseif ($totalcost >= $ShowLimit){
			$text = "Components NOT Used in any BOM cost over the limit. Current cost = ". locale_number_format($totalcost,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
	}
}

function ErrorsInTransfers($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT DISTINCT(loctransfers.reference),
					loctransfers.shipdate,
					loctransfers.shiploc,
					loctransfers.recloc,
					SUM(loctransfers.shipqty) AS shipped_quantity,
					COUNT(loctransfers.stockid) AS shipped_models,
					(SELECT SUM(loctransfercancellations.cancelqty)
						FROM loctransfercancellations
						WHERE loctransfercancellations.reference = loctransfers.reference) AS cancelled_quantity,
					(SELECT COUNT(loctransfercancellations.stockid)
						FROM loctransfercancellations
						WHERE loctransfercancellations.reference = loctransfers.reference) AS cancelled_models
			FROM loctransfers 
			WHERE loctransfers.shipdate >= '". $StartDate ."'
			GROUP BY loctransfers.reference
			HAVING SUM(loctransfers.shipqty) = SUM(loctransfers.recqty)
			ORDER BY loctransfers.reference";
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Errors on Closed Transfers during the last ') . $maxdays . _(' days ') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Total Models') . '</th>
							<th class="ascending">' . _('Cancelled Models') . '</th>
							<th class="ascending">' . _('% Models') . '</th>
							<th class="ascending">' . _('Total Qty') . '</th>
							<th class="ascending">' . _('Cancelled Qty') . '</th>
							<th class="ascending">' . _('% Qty') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$NumTransfers = 1;
		$NumTransfersWithErrors = 0;
		
		$TotalShippedModels = 0;
		$TotalCancelledModels = 0;
		$TotalShippedQty = 0;
		$TotalCancelledQty = 0;
		
		while ($myrow = DB_fetch_array($result)) {

			$TotalShippedModels += $myrow['shipped_models'];
			$TotalCancelledModels += $myrow['cancelled_models'];
			$TotalShippedQty += $myrow['shipped_quantity'];
			$TotalCancelledQty += $myrow['cancelled_quantity'];
			
			if (($myrow['cancelled_models'] != 0) OR ($myrow['cancelled_quantity'] != 0)){
				$k = StartEvenOrOddRow($k);
				$TransferLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
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
						$NumTransfersWithErrors, 
						$TransferLink, 
						ConvertSQLDateTime($myrow['shipdate']), 
						$myrow['shiploc'], 
						$myrow['recloc'],
						locale_number_format($myrow['shipped_models'],0),
						locale_number_format($myrow['cancelled_models'],0),
						locale_number_format($myrow['cancelled_models'] / $myrow['shipped_models'] * 100,2) . '%',
						locale_number_format($myrow['shipped_quantity'],0),
						locale_number_format($myrow['cancelled_quantity'],0),
						locale_number_format($myrow['cancelled_quantity'] / $myrow['shipped_quantity'] * 100,2) . '%'
						);
				$NumTransfersWithErrors++;

			}
			$NumTransfers++;
		}
		$k = StartEvenOrOddRow($k);
		printf('<td class="number">%s</td>
				<td class="number">%s</td>
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
				locale_number_format($NumTransfers,0), 
				locale_number_format($NumTransfersWithErrors / $NumTransfers * 100,2) . '%', 
				'', 
				'', 
				'TOTAL',
				locale_number_format($TotalShippedModels,0),
				locale_number_format($TotalCancelledModels,0),
				locale_number_format($TotalCancelledModels / $TotalShippedModels * 100,2) . '%',
				locale_number_format($TotalShippedQty,0),
				locale_number_format($TotalCancelledQty,0),
				locale_number_format($TotalCancelledQty / $TotalShippedQty* 100,2) . '%'
				);
		echo '</table>
				</div>';
	}
}

function FinishedStockDistribution($kind, $byreport, $db){

	if ($kind == "FORSALE"){			
		$operator1 = " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_KL_SHOPS_NOT_FOR_SALE ."";
		$operator2 = " AND m2.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_KL_SHOPS_NOT_FOR_SALE ."";
	}elseif ($kind == "DISPLAYS"){			
		$operator1 =  "	AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . " ";
		$operator2 = "	AND m2.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . " ";
	}elseif ($kind == "PACKAGING"){			
		$operator1 =  "	AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . " ";
		$operator2 = "	AND m2.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . " ";
	}else{
		$operator1 =  "	";
		$operator2 =  "	";
	}
	if ($byreport == "LOCATION"){
		$SQL =	"SELECT locstock.loccode,
					locations.locationname,
					SUM(locstock.reorderlevel) AS optimalstock,
					SUM(locstock.quantity) AS realstock,
					(SELECT COUNT(l2.reorderlevel)
						FROM locstock AS l2,
							stockmaster as m2
						WHERE l2.loccode = locations.loccode
							AND m2.stockid = l2.stockid " . 
							$operator2 ."
							AND l2.reorderlevel != 0) AS optimalmodels,
					(SELECT COUNT(l2.quantity)
						FROM locstock AS l2,
							stockmaster as m2
						WHERE l2.loccode = locations.loccode
							AND m2.stockid = l2.stockid " . 
							$operator2 ."
						AND l2.quantity != 0) AS realmodels
				FROM locstock, locations, stockmaster, stockcategory
				WHERE locstock.loccode = locations.loccode
					AND stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.stocktype = 'F'" . 
				$operator1 . " 
				GROUP BY locstock.loccode
				ORDER BY locations.locationname";
	}elseif ($byreport == "STOCKCATEGORY"){
		$SQL =	"SELECT stockmaster.categoryid,
						stockcategory.categorydescription,
					SUM(locstock.reorderlevel) AS optimalstock,
					SUM(locstock.quantity) AS realstock,
					0 AS optimalmodels,
					(SELECT COUNT(DISTINCT(l2.stockid))
						FROM locstock AS l2,
							stockmaster as m2
						WHERE m2.stockid = l2.stockid
							AND m2.categoryid = stockcategory.categoryid" . 
							$operator2 ."
						AND l2.quantity != 0) AS realmodels
				FROM locstock, locations, stockmaster, stockcategory
				WHERE locstock.loccode = locations.loccode
					AND stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.stocktype = 'F'" . 
				$operator1 . "
				GROUP BY stockmaster.categoryid
				ORDER BY stockcategory.categorydescription";
	}else{
		return false;
	}
				
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($kind == "FORSALE"){			
			$Titletext = "Finished Stock FOR SALE Distribution by "; 
		}
		if ($kind == "DISPLAYS"){			
			$Titletext = "Finished Stock DISPLAYS Distribution by "; 
		}
		if ($kind == "PACKAGING"){			
			$Titletext = "Finished Stock SHOP PACKAGING Distribution by "; 
		}
		if ($byreport == "LOCATION"){			
			$Titletext = $Titletext . "Location"; 
			$Titleheader = "Location";
		}
		if ($byreport == "STOCKCATEGORY"){			
			$Titletext = $Titletext . "Stock Category"; 
			$Titleheader = "Stock Category";
		}
		
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . $Titleheader . '</th>
							<th class="ascending">' . _('QOH Pcs') . '</th>
							<th class="ascending">' . _('RL Pcs') . '</th>
							<th class="ascending">' . _('% Pcs') . '</th>
							<th class="ascending">' . _('QOH Models') . '</th>
							<th class="ascending">' . _('RL Models') . '</th>
							<th class="ascending">' . _('% Models') . '</th>
							<th class="ascending">' . _('QOH Pcs/Model') . '</th>
							<th class="ascending">' . _('RL Pcs/Model') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$totalpcs = 0;
		
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($myrow['optimalstock'] != 0){
				$percentStock = locale_number_format(($myrow['realstock']/$myrow['optimalstock']) * 100,0) . "%";
			}else{
				$percentStock = "";
			}
			if ($myrow['optimalmodels'] != 0){
				$percentModels =locale_number_format(($myrow['realmodels']/$myrow['optimalmodels']) * 100,0). "%";
			}else{
				$percentModels = "";
			}
			if ($myrow['realmodels'] != 0){
				$realPcsModel =locale_number_format(($myrow['realstock']/$myrow['realmodels']),1);
			}else{
				$realPcsModel = "";
			}
			if ($myrow['optimalmodels'] != 0){
				$optimalPcsModel =locale_number_format(($myrow['optimalstock']/$myrow['optimalmodels']),1);
			}else{
				$optimalPcsModel = "";
			}
			if ($byreport == "LOCATION"){			
				printf('<td class="number">%s</td>
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
						$i,
						$myrow['locationname'],
						locale_number_format($myrow['realstock'],0),
						locale_number_format($myrow['optimalstock'],0),
						$percentStock,
						locale_number_format($myrow['realmodels'],0),
						locale_number_format($myrow['optimalmodels'],0),
						$percentModels,
						$realPcsModel,
						$optimalPcsModel
						);
			}
			if ($byreport == "STOCKCATEGORY"){			
				printf('<td class="number">%s</td>
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
						$i,
						$myrow['categorydescription'],
						locale_number_format($myrow['realstock'],0),
						'',
						'',
						locale_number_format($myrow['realmodels'],0),
						'',
						'',
						$realPcsModel,
						$optimalPcsModel
						);
			}
			$i++;
			$totalpcs = $totalpcs + $myrow['realstock'];
		}
		if ($byreport == "STOCKCATEGORY"){			
			$SQL =	"SELECT COUNT(DISTINCT(l2.stockid)) AS realmodels
						FROM locstock AS l2,
							stockmaster as m2,
							stockcategory
						WHERE m2.stockid = l2.stockid" . 
							$operator2 ."
						AND stockcategory.categoryid = m2.categoryid
						AND stockcategory.stocktype = 'F'
						AND l2.quantity != 0";
			$result1 = DB_query($SQL);
			if (DB_num_rows($result1) != 0){
				while ($myrow1 = DB_fetch_array($result1)) {
					$totalModels = locale_number_format($myrow1['realmodels'],0);
					$percentModels =locale_number_format(($totalpcs/$myrow1['realmodels']),1);
				}
			}
		}else{
			$totalModels = "";
			$percentModels = "";
		}
		printf('<td class="number">%s</td>
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
				"",
				"Total",
				locale_number_format($totalpcs,0),
				"",
				"",
				$totalModels,
				"",
				"",
				$percentModels,
				""
				);
		
		echo '</table>
				</div>
				</form>';
	}
}

function FinishedStockDistributionByShopAndCategory($db){

	$SQL =	"SELECT locations.loccode,
				locations.locationname,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'TESTKA'
						AND l2.reorderlevel != 0) AS modelsTESTKL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'STABKA'
						AND l2.reorderlevel != 0) AS modelsSTABLEKAPALLAUT,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'NOPOKA'
						AND l2.reorderlevel != 0) AS modelsNOPOKL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'TESTBA'
						AND l2.reorderlevel != 0) AS modelsTESTBL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'STABBA'
						AND l2.reorderlevel != 0) AS modelsSTABLEBLINK,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'NOPOBA'
						AND l2.reorderlevel != 0) AS modelsNOPOBL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'TESTGA'
						AND l2.reorderlevel != 0) AS modelsTESTGE,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'STABGA'
						AND l2.reorderlevel != 0) AS modelsSTABLEGENERAL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'NOPOGA'
						AND l2.reorderlevel != 0) AS modelsNOPOGE,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'DISC2A'
						AND l2.reorderlevel != 0) AS modelsDISC20,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'DISC5A'
						AND l2.reorderlevel != 0) AS modelsDISC50,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid 
						AND m2.categoryid = 'DISC8A'
						AND l2.reorderlevel != 0) AS modelsDISC80
			FROM locations
			WHERE locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . "
			ORDER BY locations.locationname";
						
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$Titletext = "Models FOR SALE Distribution by Location and Category"; 
		
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . "Location" . '</th>
							<th class="ascending">' . _('TEST KL') . '</th>
							<th class="ascending">' . _('Stable KL') . '</th>
							<th class="ascending">' . _('NO PO KL') . '</th>
							<th class="ascending">' . _('TEST BL') . '</th>
							<th class="ascending">' . _('Stable BL') . '</th>
							<th class="ascending">' . _('NO PO BL') . '</th>
							<th class="ascending">' . _('TEST GE') . '</th>
							<th class="ascending">' . _('Stable GE') . '</th>
							<th class="ascending">' . _('NO PO GE') . '</th>
							<th class="ascending">' . _('Disc 20') . '</th>
							<th class="ascending">' . _('Disc 50') . '</th>
							<th class="ascending">' . _('Disc 80') . '</th>
							<th class="ascending">' . _('Total') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$totalpcs = 0;
		
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$TotalModelsLocation = 	$myrow['modelsTESTKL'] + 
									$myrow['modelsSTABLEKAPALLAUT'] +
									$myrow['modelsNOPOKL'] +
									$myrow['modelsTESTBL'] + 
									$myrow['modelsSTABLEBLINK'] +
									$myrow['modelsNOPOBL'] +
									$myrow['modelsTESTGE'] + 
									$myrow['modelsSTABLEGENERAL'] +
									$myrow['modelsNOPOGE'] +
									$myrow['modelsDISC20'] +
									$myrow['modelsDISC50'] +
									$myrow['modelsDISC80'];
									
//			$optimalPcsModel =locale_number_format(($myrow['optimalstock']/$myrow['optimalmodels']),1);

			printf('<td class="number">%s</td>
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
					</tr>', 
					$i,
					$myrow['locationname'],
					locale_number_format_zero_blank($myrow['modelsTESTKL'],0),
					locale_number_format_zero_blank($myrow['modelsSTABLEKAPALLAUT'],0),
					locale_number_format_zero_blank($myrow['modelsNOPOKL'],0),
					locale_number_format_zero_blank($myrow['modelsTESTBL'],0),
					locale_number_format_zero_blank($myrow['modelsSTABLEBLINK'],0),
					locale_number_format_zero_blank($myrow['modelsNOPOBL'],0),
					locale_number_format_zero_blank($myrow['modelsTESTGE'],0),
					locale_number_format_zero_blank($myrow['modelsSTABLEGENERAL'],0),
					locale_number_format_zero_blank($myrow['modelsNOPOGE'],0),
					locale_number_format_zero_blank($myrow['modelsDISC20'],0),
					locale_number_format_zero_blank($myrow['modelsDISC50'],0),
					locale_number_format_zero_blank($myrow['modelsDISC80'],0),
					locale_number_format_zero_blank($TotalModelsLocation,0)
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

function GetTopSalesField($TopItemsDays){
	// selects the field to be used in queries of Top Sales depending on the days
	
	if ($TopItemsDays == 30){
		$TopSalesField = "topsales30";
	}elseif ($TopItemsDays == 60){
		$TopSalesField = "topsales60";
	}elseif ($TopItemsDays == 90){
		$TopSalesField = "topsales90";
	}else{
		$TopSalesField = "topsales60"; // if wrong input assumes 60.
	}
	return $TopSalesField;
}

function GoodsToBeProduced($CategoryComponent, $ParentCategory, $RootPath, $db){
/* EXPLAIN SQL 2014-05-30 */
	/* Check if there is any	component at kantor ready to be transformed into sellable goods */
	if ($ParentCategory == "ONLYDISCOUNT"){
		$WhereParentCategory = " AND stP.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . " ";
		$OnlyDiscountExists = " AND NOT EXISTS(
											SELECT bom.component
											FROM bom,stockmaster AS stP, stockmaster AS stC
											WHERE bom.parent = stP.stockid
												AND bom.component = stC.stockid 
												AND s.stockid = bom.component
												AND stP.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET . "
												AND stP.discontinued = 0)";
	}elseif ($ParentCategory == "DISCOUNT"){
		$WhereParentCategory = " AND stP.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . " ";
		$OnlyDiscountExists = " ";
	}else{
		$WhereParentCategory = " ";
		$OnlyDiscountExists = " ";
	}
	
	$SQL = "SELECT s.stockid,
				s.units,
				s.description, 
				(s.materialcost + s.labourcost + s.overheadcost) AS stdcost,(SELECT SUM(quantity) 
					FROM locstock 
					WHERE locstock.stockid = s.stockid
					AND locstock.loccode NOT IN ('SERSU')) AS availablestock
			FROM stockmaster AS s
			WHERE s.discontinued = 0 
			AND s.categoryid = '".$CategoryComponent."'
			AND ((SELECT SUM(quantity) 
					FROM locstock 
					WHERE locstock.stockid = s.stockid
					AND locstock.loccode NOT IN ('SERSU')) > 0)
			AND EXISTS(
				SELECT bom.component
				FROM bom,stockmaster AS stP, stockmaster AS stC
				WHERE bom.parent = stP.stockid
					AND bom.component = stC.stockid 
					AND s.stockid = bom.component " .
					$WhereParentCategory . "
					AND stP.discontinued = 0)" .
			$OnlyDiscountExists;

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ParentCategory == "ONLYDISCOUNT"){
			echo '<p class="page_title_text" align="center"><strong>' . _('Components ready to WO in kantor used ONLY for Discount items') . '</strong></p>';
		}elseif ($ParentCategory == "DISCOUNT"){
			echo '<p class="page_title_text" align="center"><strong>' . _('Components ready to WO in kantor used for Discount items') . '</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Components ready to WO in kantor for any items') . '</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('UOM') . '</th>
							<th class="ascending">' . _('Stock value') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$totalcost = 0;
		while ($myrow = DB_fetch_array($result)) {
			$totalcost = $totalcost + ($myrow['availablestock']*$myrow['stdcost']);
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['availablestock'],0),
					$myrow['units'], 
					locale_number_format($myrow['availablestock']*$myrow['stdcost'],0)
					);
			$i++;
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'', 
				'Total Cost',
				'',
				'',
				locale_number_format($totalcost,0)
				);
		echo '</table>
				</div>';
	}
}

function InsuficientStockForShopPackaging($Category, $DaysUsage, $DaysMinimumStock, $DaysProduction, $ShowAll, $RootPath, $db){
/* EXPLAIN SQL	2014-05-20	
id	select_type			table				type	possible_keys				key					key_len	ref	rows	Extra
1	PRIMARY				stockmaster			ref		CategoryID					CategoryID			20	const	10	Using where
4	DEPENDENT SUBQUERY	purchorderdetails	ref		ItemCode,OrderNo,Completed	ItemCode			62	kurakura_kl_erp.stockmaster.stockid	2	Using where
4	DEPENDENT SUBQUERY	purchorders			eq_ref	PRIMARY						PRIMARY				4	kurakura_kl_erp.purchorderdetails.orderno	1	Using where
3	DEPENDENT SUBQUERY	packagingused		ref		StockID+Date				StockID+Date		62	kurakura_kl_erp.stockmaster.stockid	81	Using where
2	DEPENDENT SUBQUERY	locstock			ref		StockID	StockID									62	kurakura_kl_erp.stockmaster.stockid	14	

*/
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$DaysUsage-1));
	$ToDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -1));
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.eoq,
					stockmaster.pansize,
					(SELECT SUM(quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid) AS qoh,";
	if ($Category == 'SHPACK'){
			$SQL = $SQL . "	(SELECT SUM(qty)
								FROM packagingused
								WHERE packagingused.stockid = stockmaster.stockid
									AND packagingused.date >= '". $FromDate ."'
									AND packagingused.date <= '". $ToDate ."') AS qused,";
	}else{
			$SQL = $SQL . "	(SELECT SUM(qtyinvoiced) 
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.stkcode = stockmaster.stockid
									AND salesorderdetails.completed = 1
									AND salesorders.orddate >= '". $FromDate . "'
									AND salesorders.orddate <= '". $ToDate . "') AS qused,";
	}
	$SQL = $SQL . "	 (SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd) 
						FROM purchorderdetails, purchorders
						WHERE purchorderdetails.itemcode = stockmaster.stockid
							AND purchorders.orderno=purchorderdetails.orderno
							AND purchorderdetails.completed = 0
							AND purchorders.status NOT IN ('Cancelled', 'Pending', 'Rejected')) AS qoo
			FROM stockmaster
			WHERE categoryid = '". $Category ."'
				AND discontinued = 0
			ORDER BY stockid";
	
	$result = DB_query($SQL);		
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$DailyUse = $myrow['qused'] / $DaysUsage;
			$ForecastProductionOnly = ceil($DailyUse * $DaysProduction);
			$Forecast = ceil($DailyUse * ($DaysMinimumStock));
			$ForecastIncludingProduction = $Forecast + $ForecastProductionOnly;
			$QtyNeeded = max(0, $ForecastIncludingProduction - $myrow['qoh'] - $myrow['qoo']);
			$DaysQOH = floor($myrow['qoh'] / $DailyUse);
			$DaysQOO = floor(($myrow['qoh'] + $myrow['qoo']) / $DailyUse);
			if ($QtyNeeded > 0){
				if ($myrow['pansize'] > 0){
					$PanSize = $myrow['pansize'];
				}else{
					$PanSize = 1;
				}
				$QtyToOrder = max($myrow['eoq'], ceil($QtyNeeded/$PanSize)*$PanSize);
			}else{
				$QtyToOrder = 0;
			}
			if (($QtyNeeded > 0) OR ($ShowAll)){
				if ($showHeader){
					if ($Category == 'SHPACK'){
						if ($ShowAll){
							echo '<p class="page_title_text" align="center"><strong>Shop packaging order status</strong></p>';
						}else{
							echo '<p class="page_title_text" align="center"><strong>Shop packaging with insufficient stock for the next ' . ($DaysMinimumStock + $DaysProduction) . ' days.</strong></p>';
						}
					}
					if ($Category == 'ZAPON'){
						if ($ShowAll){
							echo '<p class="page_title_text" align="center"><strong>Online Promotion items order status</strong></p>';
						}else{
							echo '<p class="page_title_text" align="center"><strong>Online Promotion items with insufficient stock for the next ' . $DaysMinimumStock . ' days.</strong></p>';
						}
					}
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Usage ') . $DaysProduction . ' days</th>
										<th class="ascending">' . _('Forecast ') . $DaysMinimumStock . ' days</th>
										<th class="ascending">' . _('QOH Total') . '</th>
										<th class="ascending">' . _('Days QOH') . '</th>
										<th class="ascending">' . _('Pending QOO') . '</th>
										<th class="ascending">' . _('Days QOH+QOO') . '</th>
										<th class="ascending">' . _('Optimum Order') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}

				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$k = StartEvenOrOddRow($k);
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
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($ForecastProductionOnly,0),
						locale_number_format($Forecast,0),
						locale_number_format($myrow['qoh'],0),
						locale_number_format($DaysQOH,0),
						locale_number_format_zero_blank($myrow['qoo'],0),
						locale_number_format($DaysQOO,0),
						locale_number_format_zero_blank($QtyToOrder,0)
						);
			}
			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function ItemsWithoutRetailPrice($stockcat, $factorRetail, $RootPath, $db){
	/* Check if there is any item without retail price */
	$today = date('Y-m-d');
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS stdcost
			FROM stockmaster, stockcategory					
			WHERE stockmaster.categoryid = stockcategory.categoryid					
				AND stockmaster.discontinued = 0					
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockcategory.stocktype ='F' 		
				AND stockmaster.categoryid = '". $stockcat ."'
				AND NOT EXISTS (SELECT * 					
								FROM prices	
								WHERE stockmaster.stockid = prices.stockid	
									AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
									AND prices.currabrev = '". CURRENCY_CODE ."'
									AND prices.startdate <= '". $today. "' 
									AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00'))";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $stockcat . _(' Items without active retail price') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Std Cost') . '</th>
							<th class="ascending">' . _('Recommended Retail') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$NewPrice = correction_for_low_end_prices(round_price($myrow['stdcost'] * $factorRetail, "UP"));
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$PriceLink = '<a href="' . $RootPath . '/Prices.php?Item=' . $myrow['stockid'] . '">' . locale_number_format($myrow['stdcost'],0) . '</a>';
			$NewPriceLink = '<a href="' . $RootPath . '/KLChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $NewPrice .  '&Action=New">' . locale_number_format($NewPrice,0) . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'],
					$PriceLink,
					$NewPriceLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ListPriorityLocations($db){
	$SQL="SELECT locationname,
				zone,
				priority,
				stockavailableforonline,
				smartdispatchminmodels,
				smartdispatchmaxmodels
		FROM locations
		WHERE locations.typeloc IN " . ALL_SHOPS_LIST_BY_TYPE . "
		ORDER BY locationname ASC";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Priority Assigned to Shops. 1-Maximum 10-Minimum') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Location') . '</th>
							<th class="ascending">' . _('Zone') . '</th>
							<th class="ascending">' . _('Priority') . '</th>
							<th class="ascending">' . _('Stock Online?') . '</th>
							<th class="ascending">' . _('Min Daily Tr') . '</th>
							<th class="ascending">' . _('Max Daily Tr') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($myrow['stockavailableforonline'] ==  1){
				$StockOnline = "Yes";
			}else{
				$StockOnline = "No";
			}
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$myrow['locationname'],
					$myrow['zone'],
					$myrow['priority'],
					$StockOnline,
					$myrow['smartdispatchminmodels'],
					$myrow['smartdispatchmaxmodels']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PackagingToBeRefilledBlink($ShowAll, $RootPath, $db){
/* EXPLAIN SQL 2014-05-20
Updated 3 index in loctransfers
*/

	$TableResult = array();
	if ($ShowAll){
		$OrderBy = " ORDER BY locations.locationname";
	}else{
		$OrderBy = " ORDER BY locations.klemaillastpackacgingtransfer";
	}
	
	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.klemaillastpackacgingtransfer,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-XL') AS qty_bag_xl,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-XL') AS rl_bag_xl,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB03-XL') AS ot_bag_xl,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB03-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB03-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB03-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB03-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-L') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-L') AS rl_shopping_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB04-L') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-M') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-M') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB04-M') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-S') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB04-S') AS rl_shopping_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB04-S') AS ot_shopping_s
			FROM locations
			WHERE locations.typeloc = 'SHOPBL' " .  
			$OrderBy;

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$numshops = 0;
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			$numshops++;
			$TableResult[$numshops]['show'] = FALSE; // to start we don't need to show any result
			$TableResult[$numshops]['loccode'] = $myrow['loccode'];
			$TableResult[$numshops]['locationname'] = $myrow['locationname'];
			$TableResult[$numshops]['rlfactorforpackaging'] = $myrow['rlfactorforpackaging'];
			$TableResult[$numshops]['klemaillastpackacgingtransfer'] = $myrow['klemaillastpackacgingtransfer'];

			$TableResult[$numshops]['qty_bag_xl'] = $myrow['qty_bag_xl'];
			$TableResult[$numshops]['qty_bag_l'] = $myrow['qty_bag_l'];
			$TableResult[$numshops]['qty_bag_m'] = $myrow['qty_bag_m'];
			$TableResult[$numshops]['qty_bag_s'] = $myrow['qty_bag_s'];
			$TableResult[$numshops]['qty_shopping_l'] = $myrow['qty_shopping_l'];
			$TableResult[$numshops]['qty_shopping_m'] = $myrow['qty_shopping_m'];
			$TableResult[$numshops]['qty_shopping_s'] = $myrow['qty_shopping_s'];

			$TableResult[$numshops]['ot_bag_xl'] = $myrow['ot_bag_xl'];
			$TableResult[$numshops]['ot_bag_l'] = $myrow['ot_bag_l'];
			$TableResult[$numshops]['ot_bag_m'] = $myrow['ot_bag_m'];
			$TableResult[$numshops]['ot_bag_s'] = $myrow['ot_bag_s'];
			$TableResult[$numshops]['ot_shopping_l'] = $myrow['ot_shopping_l'];
			$TableResult[$numshops]['ot_shopping_m'] = $myrow['ot_shopping_m'];
			$TableResult[$numshops]['ot_shopping_s'] = $myrow['ot_shopping_s'];

			$TableResult[$numshops]['rl_bag_xl'] = $myrow['rl_bag_xl'];
			$TableResult[$numshops]['rl_bag_l'] = $myrow['rl_bag_l'];
			$TableResult[$numshops]['rl_bag_m'] = $myrow['rl_bag_m'];
			$TableResult[$numshops]['rl_bag_s'] = $myrow['rl_bag_s'];
			$TableResult[$numshops]['rl_shopping_l'] = $myrow['rl_shopping_l'];
			$TableResult[$numshops]['rl_shopping_m'] = $myrow['rl_shopping_m'];
			$TableResult[$numshops]['rl_shopping_s'] = $myrow['rl_shopping_s'];
		}
	}

	/* Let's see if we need to show some shops	*/
	$i = 1;
	while ($i <= $numshops) {
		if (($TableResult[$i]['qty_bag_xl'] < $TableResult[$i]['rl_bag_xl']) OR 
			($TableResult[$i]['qty_bag_l'] < $TableResult[$i]['rl_bag_l']) OR 
			($TableResult[$i]['qty_bag_m'] < $TableResult[$i]['rl_bag_m']) OR 
			($TableResult[$i]['qty_bag_s'] < $TableResult[$i]['rl_bag_s']) OR 
			($TableResult[$i]['qty_shopping_l'] < $TableResult[$i]['rl_shopping_l']) OR 
			($TableResult[$i]['qty_shopping_m'] < $TableResult[$i]['rl_shopping_m']) OR 
			($TableResult[$i]['qty_shopping_s'] < $TableResult[$i]['rl_shopping_s'])) 
		{
			$TableResult[$i]['show'] = TRUE;
		}
		$i++;
	}
	
	if ($numshops > 0){
		$i = 1;
		$k = 0; //row colour counter

		while ($i <= $numshops) {
			
			if ($ShowAll OR ($TableResult[$i]['show'])) {
				// IF we are SHORT of any packaging material in that shop...
				// Or we show All the shops 
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . 'BLINK Shops needing Packaging Transfers (Do not forget to create transfer in webERP)' . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th>' . _('') . '</th>
										<th colspan="3">' . _('PouchBag XL') . '</th>
										<th colspan="3">' . _('PouchBag L') . '</th>
										<th colspan="3">' . _('PouchBag M') . '</th>
										<th colspan="3">' . _('PouchBag S') . '</th>
										<th colspan="3">' . _('ShoppingBag L') . '</th>
										<th colspan="3">' . _('ShoppingBag M') . '</th>
										<th colspan="3">' . _('ShoppingBag S') . '</th>
										<th>' . _('') . '</th>
										<th>' . _('') . '</th>
									</tr>';
					$TableHeader = $TableHeader . '<tr>
										<th class="ascending">' . _('KL Shop') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Last Email') . '</th>
										<th class="ascending">' . _('Action') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);

				// Calculate how many we should ship to the shop...
				$NeedBagXL = max(0,round(($TableResult[$i]['rl_bag_xl'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_xl'],0));
				$NeedBagL = max(0,round(($TableResult[$i]['rl_bag_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_l'],0));
				$NeedBagM = max(0,round(($TableResult[$i]['rl_bag_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_m'],0));
				$NeedBagS = max(0,round(($TableResult[$i]['rl_bag_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_s'],0));
				$NeedShoppingL = max(0,round(($TableResult[$i]['rl_shopping_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_l'],0));
				$NeedShoppingM = max(0,round(($TableResult[$i]['rl_shopping_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_m'],0));
				$NeedShoppingS = max(0,round(($TableResult[$i]['rl_shopping_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_s'],0));

				$ToShipBagXL = max(0,$NeedBagXL - $TableResult[$i]['ot_bag_xl']);
				$ToShipBagL = max(0,$NeedBagL - $TableResult[$i]['ot_bag_l']);
				$ToShipBagM = max(0,$NeedBagM - $TableResult[$i]['ot_bag_m']);
				$ToShipBagS = max(0,$NeedBagS - $TableResult[$i]['ot_bag_s']);
				$ToShipShoppingL = max(0,$NeedShoppingL - $TableResult[$i]['ot_shopping_l']);
				$ToShipShoppingM = max(0,$NeedShoppingM - $TableResult[$i]['ot_shopping_m']);
				$ToShipShoppingS = max(0,$NeedShoppingS - $TableResult[$i]['ot_shopping_s']);

				$EmailLink = '<a href="' . $RootPath . '/KLPreparePackagingTransferBlink.php?Shop=' . $TableResult[$i]['loccode'] 
																						. '&Name=' . $TableResult[$i]['locationname'] 
																						. '&BagXL=' . $ToShipBagXL 
																						. '&BagL=' . $ToShipBagL 
																						. '&BagM=' . $ToShipBagM 
																						. '&BagS=' . $ToShipBagS 
																						. '&ShoppingL=' . $ToShipShoppingL 
																						. '&ShoppingM=' . $ToShipShoppingM 
																						. '&ShoppingS=' . $ToShipShoppingS 
																						.'">' . 'Send email to team' . '</a>';
				
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
						<td>%s</td>
						</tr>', 
						$TableResult[$i]['locationname'], 
						locale_number_format_zero_blank($NeedBagXL, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_xl'],0),
						locale_number_format_zero_blank($ToShipBagXL,0),
						locale_number_format_zero_blank($NeedBagL, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_l'],0),
						locale_number_format_zero_blank($ToShipBagL,0),
						locale_number_format_zero_blank($NeedBagM, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_m'],0),
						locale_number_format_zero_blank($ToShipBagM,0),
						locale_number_format_zero_blank($NeedBagS,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_s'],0),
						locale_number_format_zero_blank($ToShipBagS,0),
						locale_number_format_zero_blank($NeedShoppingL,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_l'],0),
						locale_number_format_zero_blank($ToShipShoppingL,0),
						locale_number_format_zero_blank($NeedShoppingM,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_m'],0),
						locale_number_format_zero_blank($ToShipShoppingM,0),
						locale_number_format_zero_blank($NeedShoppingS,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_s'],0),
						locale_number_format_zero_blank($ToShipShoppingS,0),
						ConvertSQLDateTime($TableResult[$i]['klemaillastpackacgingtransfer']), 
						$EmailLink
						);
			}
			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function PackagingToBeRefilledKapalLaut($ShowAll, $RootPath, $db){
/* EXPLAIN SQL 2014-05-20
Updated 3 index in loctransfers
*/

	$TableResult = array();
	if ($ShowAll){
		$OrderBy = " ORDER BY locations.locationname";
	}else{
		$OrderBy = " ORDER BY locations.klemaillastpackacgingtransfer";
	}
	
	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.klemaillastpackacgingtransfer,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS qty_box_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS rl_box_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-L') AS ot_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS qty_box_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS rl_box_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-M') AS ot_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS qty_box_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS rl_box_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-S') AS ot_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS rl_shopping_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-L') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-M') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS rl_shopping_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-S') AS ot_shopping_s
			FROM locations
			WHERE locations.typeloc = 'SHOPKL' " .  
			$OrderBy;

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$numshops = 0;
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			$numshops++;
			$TableResult[$numshops]['show'] = FALSE; // to start we don't need to show any result
			$TableResult[$numshops]['loccode'] = $myrow['loccode'];
			$TableResult[$numshops]['locationname'] = $myrow['locationname'];
			$TableResult[$numshops]['rlfactorforpackaging'] = $myrow['rlfactorforpackaging'];
			$TableResult[$numshops]['klemaillastpackacgingtransfer'] = $myrow['klemaillastpackacgingtransfer'];

			$TableResult[$numshops]['qty_box_l'] = $myrow['qty_box_l'];
			$TableResult[$numshops]['qty_box_m'] = $myrow['qty_box_m'];
			$TableResult[$numshops]['qty_box_s'] = $myrow['qty_box_s'];
			$TableResult[$numshops]['qty_bag_l'] = $myrow['qty_bag_l'];
			$TableResult[$numshops]['qty_bag_m'] = $myrow['qty_bag_m'];
			$TableResult[$numshops]['qty_bag_s'] = $myrow['qty_bag_s'];
			$TableResult[$numshops]['qty_shopping_l'] = $myrow['qty_shopping_l'];
			$TableResult[$numshops]['qty_shopping_m'] = $myrow['qty_shopping_m'];
			$TableResult[$numshops]['qty_shopping_s'] = $myrow['qty_shopping_s'];

			$TableResult[$numshops]['ot_box_l'] = $myrow['ot_box_l'];
			$TableResult[$numshops]['ot_box_m'] = $myrow['ot_box_m'];
			$TableResult[$numshops]['ot_box_s'] = $myrow['ot_box_s'];
			$TableResult[$numshops]['ot_bag_l'] = $myrow['ot_bag_l'];
			$TableResult[$numshops]['ot_bag_m'] = $myrow['ot_bag_m'];
			$TableResult[$numshops]['ot_bag_s'] = $myrow['ot_bag_s'];
			$TableResult[$numshops]['ot_shopping_l'] = $myrow['ot_shopping_l'];
			$TableResult[$numshops]['ot_shopping_m'] = $myrow['ot_shopping_m'];
			$TableResult[$numshops]['ot_shopping_s'] = $myrow['ot_shopping_s'];

			$TableResult[$numshops]['rl_box_l'] = $myrow['rl_box_l'];
			$TableResult[$numshops]['rl_box_m'] = $myrow['rl_box_m'];
			$TableResult[$numshops]['rl_box_s'] = $myrow['rl_box_s'];
			$TableResult[$numshops]['rl_bag_l'] = $myrow['rl_bag_l'];
			$TableResult[$numshops]['rl_bag_m'] = $myrow['rl_bag_m'];
			$TableResult[$numshops]['rl_bag_s'] = $myrow['rl_bag_s'];
			$TableResult[$numshops]['rl_shopping_l'] = $myrow['rl_shopping_l'];
			$TableResult[$numshops]['rl_shopping_m'] = $myrow['rl_shopping_m'];
			$TableResult[$numshops]['rl_shopping_s'] = $myrow['rl_shopping_s'];
		}
	}

	/* Let's see if we need to show some shops	*/
	$i = 1;
	while ($i <= $numshops) {
		if (($TableResult[$i]['qty_box_l'] < $TableResult[$i]['rl_box_l']) OR 
			($TableResult[$i]['qty_box_m'] < $TableResult[$i]['rl_box_m']) OR 
			($TableResult[$i]['qty_box_s'] < $TableResult[$i]['rl_box_s']) OR 
			($TableResult[$i]['qty_bag_l'] < $TableResult[$i]['rl_bag_l']) OR 
			($TableResult[$i]['qty_bag_m'] < $TableResult[$i]['rl_bag_m']) OR 
			($TableResult[$i]['qty_bag_s'] < $TableResult[$i]['rl_bag_s']) OR 
			($TableResult[$i]['qty_shopping_l'] < $TableResult[$i]['rl_shopping_l']) OR 
			($TableResult[$i]['qty_shopping_m'] < $TableResult[$i]['rl_shopping_m']) OR 
			($TableResult[$i]['qty_shopping_s'] < $TableResult[$i]['rl_shopping_s'])) 
		{
			$TableResult[$i]['show'] = TRUE;
		}
		$i++;
	}
	
	if ($numshops > 0){
		$i = 1;
		$k = 0; //row colour counter

		while ($i <= $numshops) {
			
			if ($ShowAll OR ($TableResult[$i]['show'])) {
				// IF we are SHORT of any packaging material in that shop...
				// Or we show All the shops 
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shops needing Packaging Transfers (Do not forget to create transfer in webERP)' . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th>' . _('') . '</th>
										<th colspan="3">' . _('Box L') . '</th>
										<th colspan="3">' . _('Box M') . '</th>
										<th colspan="3">' . _('Box S') . '</th>
										<th colspan="3">' . _('PouchBag L') . '</th>
										<th colspan="3">' . _('PouchBag M') . '</th>
										<th colspan="3">' . _('PouchBag S') . '</th>
										<th colspan="3">' . _('ShoppingBag L') . '</th>
										<th colspan="3">' . _('ShoppingBag M') . '</th>
										<th colspan="3">' . _('ShoppingBag S') . '</th>
										<th>' . _('') . '</th>
										<th>' . _('') . '</th>
									</tr>';
					$TableHeader = $TableHeader . '<tr>
										<th class="ascending">' . _('KL Shop') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Last Email') . '</th>
										<th class="ascending">' . _('Action') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);

				// Calculate how many we should ship to the shop...
				$NeedBoxL = max(0,round(($TableResult[$i]['rl_box_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_box_l'],0));
				$NeedBoxM = max(0,round(($TableResult[$i]['rl_box_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_box_m'],0));
				$NeedBoxS = max(0,round(($TableResult[$i]['rl_box_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_box_s'],0));
				$NeedBagL = max(0,round(($TableResult[$i]['rl_bag_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_l'],0));
				$NeedBagM = max(0,round(($TableResult[$i]['rl_bag_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_m'],0));
				$NeedBagS = max(0,round(($TableResult[$i]['rl_bag_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_s'],0));
				$NeedShoppingL = max(0,round(($TableResult[$i]['rl_shopping_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_l'],0));
				$NeedShoppingM = max(0,round(($TableResult[$i]['rl_shopping_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_m'],0));
				$NeedShoppingS = max(0,round(($TableResult[$i]['rl_shopping_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_s'],0));

				$ToShipBoxL = max(0,$NeedBoxL - $TableResult[$i]['ot_box_l']);
				$ToShipBoxM = max(0,$NeedBoxM - $TableResult[$i]['ot_box_m']);
				$ToShipBoxS = max(0,$NeedBoxS - $TableResult[$i]['ot_box_s']);
				$ToShipBagL = max(0,$NeedBagL - $TableResult[$i]['ot_bag_l']);
				$ToShipBagM = max(0,$NeedBagM - $TableResult[$i]['ot_bag_m']);
				$ToShipBagS = max(0,$NeedBagS - $TableResult[$i]['ot_bag_s']);
				$ToShipShoppingL = max(0,$NeedShoppingL - $TableResult[$i]['ot_shopping_l']);
				$ToShipShoppingM = max(0,$NeedShoppingM - $TableResult[$i]['ot_shopping_m']);
				$ToShipShoppingS = max(0,$NeedShoppingS - $TableResult[$i]['ot_shopping_s']);

				$EmailLink = '<a href="' . $RootPath . '/KLPreparePackagingTransfer.php?Shop=' . $TableResult[$i]['loccode'] 
																						. '&Name=' . $TableResult[$i]['locationname'] 
																						. '&BoxL=' . $ToShipBoxL  
																						. '&BoxM=' . $ToShipBoxM  
																						. '&BoxS=' . $ToShipBoxS 
																						. '&BagL=' . $ToShipBagL 
																						. '&BagM=' . $ToShipBagM 
																						. '&BagS=' . $ToShipBagS 
																						. '&ShoppingL=' . $ToShipShoppingL 
																						. '&ShoppingM=' . $ToShipShoppingM 
																						. '&ShoppingS=' . $ToShipShoppingS 
																						.'">' . 'Send email to team' . '</a>';
				
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
						<td>%s</td>
						</tr>', 
						$TableResult[$i]['locationname'], 
						locale_number_format_zero_blank($NeedBoxL,0), 
						locale_number_format_zero_blank($TableResult[$i]['ot_box_l'],0),
						locale_number_format_zero_blank($ToShipBoxL,0),
						locale_number_format_zero_blank($NeedBoxM, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_box_m'],0),
						locale_number_format_zero_blank($ToShipBoxM,0),
						locale_number_format_zero_blank($NeedBoxS, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_box_s'],0),
						locale_number_format_zero_blank($ToShipBoxS,0),
						locale_number_format_zero_blank($NeedBagL, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_l'],0),
						locale_number_format_zero_blank($ToShipBagL,0),
						locale_number_format_zero_blank($NeedBagM, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_m'],0),
						locale_number_format_zero_blank($ToShipBagM,0),
						locale_number_format_zero_blank($NeedBagS,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_s'],0),
						locale_number_format_zero_blank($ToShipBagS,0),
						locale_number_format_zero_blank($NeedShoppingL,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_l'],0),
						locale_number_format_zero_blank($ToShipShoppingL,0),
						locale_number_format_zero_blank($NeedShoppingM,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_m'],0),
						locale_number_format_zero_blank($ToShipShoppingM,0),
						locale_number_format_zero_blank($NeedShoppingS,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_s'],0),
						locale_number_format_zero_blank($ToShipShoppingS,0),
						ConvertSQLDateTime($TableResult[$i]['klemaillastpackacgingtransfer']), 
						$EmailLink
						);
			}
			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function PackagingToBeRefilledOutlet($ShowAll, $RootPath, $db){

	$TableResult = array();
	if ($ShowAll){
		$OrderBy = " ORDER BY locations.locationname";
	}else{
		$OrderBy = " ORDER BY locations.klemaillastpackacgingtransfer";
	}
	
	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.klemaillastpackacgingtransfer,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB03') AS ot_shopping_m
			FROM locations
			WHERE locations.typeloc = 'SHOPOU' " .  
			$OrderBy;

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$numshops = 0;
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			$numshops++;
			$TableResult[$numshops]['show'] = FALSE; // to start we don't need to show any result
			$TableResult[$numshops]['loccode'] = $myrow['loccode'];
			$TableResult[$numshops]['locationname'] = $myrow['locationname'];
			$TableResult[$numshops]['rlfactorforpackaging'] = $myrow['rlfactorforpackaging'];
			$TableResult[$numshops]['klemaillastpackacgingtransfer'] = $myrow['klemaillastpackacgingtransfer'];

			$TableResult[$numshops]['qty_bag_l'] = $myrow['qty_bag_l'];
			$TableResult[$numshops]['qty_bag_m'] = $myrow['qty_bag_m'];
			$TableResult[$numshops]['qty_bag_s'] = $myrow['qty_bag_s'];
			$TableResult[$numshops]['qty_shopping_m'] = $myrow['qty_shopping_m'];

			$TableResult[$numshops]['ot_bag_l'] = $myrow['ot_bag_l'];
			$TableResult[$numshops]['ot_bag_m'] = $myrow['ot_bag_m'];
			$TableResult[$numshops]['ot_bag_s'] = $myrow['ot_bag_s'];
			$TableResult[$numshops]['ot_shopping_m'] = $myrow['ot_shopping_m'];

			$TableResult[$numshops]['rl_bag_l'] = $myrow['rl_bag_l'];
			$TableResult[$numshops]['rl_bag_m'] = $myrow['rl_bag_m'];
			$TableResult[$numshops]['rl_bag_s'] = $myrow['rl_bag_s'];
			$TableResult[$numshops]['rl_shopping_m'] = $myrow['rl_shopping_m'];
		}
	}
	
	if ($numshops > 0){
		$i = 1;
		$k = 0; //row colour counter

		while ($i <= $numshops) {
			if (($TableResult[$i]['qty_bag_l'] < $TableResult[$i]['rl_bag_l']) OR 
				($TableResult[$i]['qty_bag_m'] < $TableResult[$i]['rl_bag_m']) OR 
				($TableResult[$i]['qty_bag_s'] < $TableResult[$i]['rl_bag_s']) OR 
				($TableResult[$i]['qty_shopping_m'] < $TableResult[$i]['rl_shopping_m'])) 
			{
				$TableResult[$i]['show'] = TRUE;
			}
			
			if ($ShowAll OR ($TableResult[$i]['show'])) {
				// IF we are SHORT of any packaging material in that shop...
				// Or we show All the shops 
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . 'OUTLET Shops needing OUTLET Packaging Transfers (Do not forget to create transfer in webERP)' . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th>' . _('') . '</th>
										<th colspan="3">' . _('OUTLET PouchBag L') . '</th>
										<th colspan="3">' . _('OUTLET PouchBag M') . '</th>
										<th colspan="3">' . _('OUTLET PouchBag S') . '</th>
										<th colspan="3">' . _('OUTLET ShoppingBag') . '</th>
										<th>' . _('') . '</th>
										<th>' . _('') . '</th>
									</tr>';
					$TableHeader = $TableHeader . '<tr>
										<th class="ascending">' . _('KL Shop') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Last Email') . '</th>
										<th class="ascending">' . _('Action') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);

				// Calculate how many we should ship to the shop...
				$NeedBagL = max(0,round(($TableResult[$i]['rl_bag_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_l'],0));
				$NeedBagM = max(0,round(($TableResult[$i]['rl_bag_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_m'],0));
				$NeedBagS = max(0,round(($TableResult[$i]['rl_bag_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_s'],0));
				$NeedShoppingM = max(0,round(($TableResult[$i]['rl_shopping_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_m'],0));

				$ToShipBagL = max(0,$NeedBagL - $TableResult[$i]['ot_bag_l']);
				$ToShipBagM = max(0,$NeedBagM - $TableResult[$i]['ot_bag_m']);
				$ToShipBagS = max(0,$NeedBagS - $TableResult[$i]['ot_bag_s']);
				$ToShipShoppingM = max(0,$NeedShoppingM - $TableResult[$i]['ot_shopping_m']);

				$EmailLink = '<a href="' . $RootPath . '/KLPreparePackagingTransferOutlet.php?Shop=' . $TableResult[$i]['loccode'] 
																								. '&Name=' . $TableResult[$i]['locationname'] 
																								. '&BagL=' . $ToShipBagL 
																								. '&BagM=' . $ToShipBagM 
																								. '&BagS=' . $ToShipBagS 
																								. '&ShoppingM=' . $ToShipShoppingM 
																								.'">' . 'Send email to team' . '</a>';
				
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
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$TableResult[$i]['locationname'], 
						locale_number_format_zero_blank($NeedBagL, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_l'],0),
						locale_number_format_zero_blank($ToShipBagL,0),
						locale_number_format_zero_blank($NeedBagM, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_m'],0),
						locale_number_format_zero_blank($ToShipBagM,0),
						locale_number_format_zero_blank($NeedBagS,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_s'],0),
						locale_number_format_zero_blank($ToShipBagS,0),
						locale_number_format_zero_blank($NeedShoppingM,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_m'],0),
						locale_number_format_zero_blank($ToShipShoppingM,0),
						ConvertSQLDateTime($TableResult[$i]['klemaillastpackacgingtransfer']), 
						$EmailLink
						);
			}
			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function PositionTopSalesItem($stockid, $TopItemsDays, $db){

	$TopSalesField = GetTopSalesField($TopItemsDays);
	$SQL="SELECT ". $TopSalesField." AS topsalesposition
		  FROM klsalesperformance
		  WHERE stockid = '" . $stockid . "'";
	$result = DB_query($SQL);
	$TopSalesPosition = 999999;
	if (DB_num_rows($result) != 0){
		if ($myrow = DB_fetch_array($result)) {
			$TopSalesPosition = $myrow['topsalesposition'];
		}
	}
	return $TopSalesPosition;
}

function POStatusControl($TypeOfCode, $maxdays, $RootPath, $db){

	if ($TypeOfCode == "IN NEGOTIAION WITH SUPPLIER"){
		$DateField1 = "orddate";
		$FieldName1 = "Planned Order Date";
		$DateField2 = "orddate";
		$FieldName2 = "";
		$ShipmentAWB = '';
		$TitleWarning = 'POs in Negotiations with supplier';
		$SQLFilterKLStatus = " AND purchorders.klstatus = '1000' ";
	}else if ($TypeOfCode == "ON PRODUCTION"){
		$DateField1 = "agreeddeliverydate";
		$FieldName1 = "Agreed Delivery";
		$DateField2 = "deliverydate";
		$FieldName2 = "Planned Delivery";
		$ShipmentAWB = '';
		$TitleWarning = 'POs on Production by supplier';
		$SQLFilterKLStatus = " AND purchorders.klstatus = '2000' ";
	}else if ($TypeOfCode == "FINISHED BUT NOT PAID"){
		$DateField1 = "deliverydate";
		$FieldName1 = "Delivery Date";
		$DateField2 = "paymentdate";
		$FieldName2 = "Planned Payment";
		$ShipmentAWB = '';
		$TitleWarning = 'POs finished by supplier but not fully paid';
		$SQLFilterKLStatus = " AND purchorders.klstatus = '3000' ";
	}else if ($TypeOfCode == "STILL NOT FULLY PAID"){
		$DateField1 = "deliverydate";
		$FieldName1 = "Delivery Date";
		$DateField2 = "paymentdate";
		$FieldName2 = "Planned Payment";
		$ShipmentAWB = '';
		$TitleWarning = 'POs still not fully paid';
		$SQLFilterKLStatus = " AND purchorders.klstatus > '1000'
							   AND (   (purchorders.klstatus < '4000' AND suppliers.paymentterms = 'B1') 
									OR (purchorders.klstatus < '7000' AND suppliers.paymentterms = 'B2')
									OR (purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O1')
									OR (purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O2')
									OR (purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O3')
									OR (purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O4')
									OR (purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O5')) ";
	}else if ($TypeOfCode == "BALI PAID BUT NOT RECEIVED IN KANTOR"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "arrivaldate";
		$FieldName2 = "Planned Arrival";
		$ShipmentAWB = '';
		$TitleWarning = 'Bali POs paid but not delivered in kantor';
		$SQLFilterKLStatus = " AND (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'B1') ";
	}else if ($TypeOfCode == "BALI RECEIVED IN KANTOR BUT NOT PAID"){
		$DateField1 = "arrivaldate";
		$FieldName1 = "Arrival Date";
		$DateField2 = "paymentdate";
		$FieldName2 = "Planned Payment";
		$ShipmentAWB = '';
		$TitleWarning = 'Bali POs delivered in kantor but not paid yet';
		$SQLFilterKLStatus = " AND (purchorders.klstatus = '6000' AND suppliers.paymentterms = 'B2') ";
	}else if ($TypeOfCode == "PAID NOT SHIPPED BY SUPPLIER"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TitleWarning = 'Overseas POs paid but not shipped directly by supplier';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O1')
									OR (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O3')) ";
	}else if ($TypeOfCode == "PAID NOT RECEIVED IN AYE CARGO"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TitleWarning = 'Overseas POs paid to supplier but not received by Aye Cargo';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O2')
									OR (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O5')) ";
	}else if ($TypeOfCode == "PAID NOT RECEIVED IN WANGFOONG CARGO"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TitleWarning = 'Overseas POs paid to supplier but not received by Wangfoong Cargo';
		$SQLFilterKLStatus = " AND (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O4') ";
	}else if ($TypeOfCode == "IN AYE CARGO BUT NOT SHIPPED"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TitleWarning = 'Overseas POs waiting to be shipped by Aye Cargo';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '4500' AND suppliers.paymentterms = 'O2')
									OR (purchorders.klstatus = '4500' AND suppliers.paymentterms = 'O5')) ";
	}else if ($TypeOfCode == "IN WANGFOONG CARGO BUT NOT SHIPPED"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TitleWarning = 'Overseas POs waiting to be shipped by Wangfoong Cargo';
		$SQLFilterKLStatus = " AND (purchorders.klstatus = '4500' AND suppliers.paymentterms = 'O4') ";
	}else if ($TypeOfCode == "SHIPPED IN TRANSIT"){
		$DateField1 = "shipmentdate";
		$FieldName1 = "Shipment Date";
		$DateField2 = "customsdate";
		$FieldName2 = "Planned Customs";
		$ShipmentAWB = 'AWB';
		$TitleWarning = 'Overseas POs shipped and in transit to Customs';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '5000' AND suppliers.paymentterms = 'O1')
									OR (purchorders.klstatus = '5000' AND suppliers.paymentterms = 'O2')
									OR (purchorders.klstatus = '5000' AND suppliers.paymentterms = 'O3')
									OR (purchorders.klstatus = '5000' AND suppliers.paymentterms = 'O4')
									OR (purchorders.klstatus = '5000' AND suppliers.paymentterms = 'O5')) ";
	}else if ($TypeOfCode == "CUSTOMS CLEARANCE"){
		$DateField1 = "customsdate";
		$FieldName1 = "Customs Date";
		$DateField2 = "arrivaldate";
		$FieldName2 = "Planned Arrival";
		$ShipmentAWB = 'AWB';
		$TitleWarning = 'Overseas POs in Customs Clearance';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '5500' AND suppliers.paymentterms = 'O1')
									OR (purchorders.klstatus = '5500' AND suppliers.paymentterms = 'O2')
									OR (purchorders.klstatus = '5500' AND suppliers.paymentterms = 'O3')
									OR (purchorders.klstatus = '5500' AND suppliers.paymentterms = 'O4')
									OR (purchorders.klstatus = '5500' AND suppliers.paymentterms = 'O5')) ";
	}else if ($TypeOfCode == "RECEIVED IN KANTOR"){
		$DateField1 = "arrivaldate";
		$FieldName1 = "Arrival Date";
		$DateField2 = "arrivaldate";
		$FieldName2 = "";
		$ShipmentAWB = 'AWB';
		$TitleWarning = 'Overseas POs already received in kantor';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '6000' AND suppliers.paymentterms = 'O1')
									OR (purchorders.klstatus = '6000' AND suppliers.paymentterms = 'O2')
									OR (purchorders.klstatus = '6000' AND suppliers.paymentterms = 'O3')
									OR (purchorders.klstatus = '6000' AND suppliers.paymentterms = 'O4')
									OR (purchorders.klstatus = '6000' AND suppliers.paymentterms = 'O5')) ";
	}else if ($TypeOfCode == "ARRIVING IN NEXT DAYS"){
		$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',+$maxdays));
		$DateField1 = "arrivaldate";
		$FieldName1 = "Planned Arrival";
		$DateField2 = "arrivaldate";
		$FieldName2 = "";
		$ShipmentAWB = 'AWB';
		$TitleWarning = 'POs arriving in the next ' . $maxdays . ' days';
		$SQLFilterKLStatus = " AND purchorders.klstatus >= '1000' 
			AND purchorders.klstatus <= '6000'
			AND purchorders." . $DateField1 ." <  '". $StartDate ."'";
	}else{
		return;
	}
	
	$SQL = "SELECT purchorders.orderno,
				purchorders.supplierno,
				purchorders.orddate,
				purchorders." . $DateField1 ." AS reportdate,
				purchorders." . $DateField2 ." AS reportdate2,
				purchorders.shipmentawb,
				purchorders.status,
				purchorders.initiator,
				purchorders.allowprint,
				suppliers.currcode,
				currencies.rate AS exchangerate,
				currencies.decimalplaces AS currdecimalplaces,
				SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
			FROM purchorders INNER JOIN purchorderdetails
				ON purchorders.orderno = purchorderdetails.orderno
			INNER JOIN suppliers 
				ON  purchorders.supplierno = suppliers.supplierid 
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			WHERE purchorderdetails.completed=0 "
			    . $SQLFilterKLStatus . 
				" AND purchorders.status IN ('Authorised', 'Printed', 'Pending')	
			GROUP BY purchorders.orderno ASC,
				purchorders.supplierno,
				purchorders.orddate,
				purchorders.status,
				purchorders.initiator,
				purchorders.allowprint,
				suppliers.currcode,
				currencies.decimalplaces
			ORDER BY purchorders." . $DateField1 ." ASC,
				purchorders.orderno ASC";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $TitleWarning . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th colspan="9">' . _('Order') . '</th>
							<th colspan="3">' . _('Supplier DP') . '</th>
							<th colspan="3">' . _('Payment Needed') . '</th>
							<th colspan="3">' . _('Acummulated Payment') . '</th>
						</tr>
						<tr>
							<th>' . _('#') . '</th>
							<th>' . _('PO') . '</th>
							<th>' . _('Supplier') . '</th>
							<th>' . $FieldName1 . '</th>
							<th>' . $FieldName2 . '</th>
							<th>' . $ShipmentAWB . '</th>
							<th>' . _('IDR') . '</th>
							<th>' . _('USD') . '</th>
							<th>' . _('THB') . '</th>
							<th>' . _('IDR') . '</th>
							<th>' . _('USD') . '</th>
							<th>' . _('THB') . '</th>
							<th>' . _('IDR') . '</th>
							<th>' . _('USD') . '</th>
							<th>' . _('THB') . '</th>
							<th>' . _('IDR') . '</th>
							<th>' . _('USD') . '</th>
							<th>' . _('THB') . '</th>
						</tr>';
		echo $TableHeader;
		
		$TotalValueOrderIDR = 0;
		$TotalValueOrderUSD = 0;
		$TotalValueOrderTHB = 0;
		$TotalValueAllOrders = 0;
		$TotalValueAllPayments = 0;
		$AcumIDR = 0;
		$AcumUSD = 0;
		$AcumTHB = 0;
		$Payments = array();
		
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/PO_Header.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			
			if (isset($Payments[$myrow['supplierno']])){
				// we already have info in memory about the supplier
			}else{
				// the first time we find this supplier, let's get the balance
				$SQL = "SELECT SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
						FROM supptrans
						WHERE supptrans.supplierno = '" . $myrow['supplierno'] . "'";
				$SupplierResult = DB_query($SQL);
				$mySupplier=DB_fetch_array($SupplierResult);
				$Payments[$myrow['supplierno']]['currency'] = $myrow['currcode']; 
				$Payments[$myrow['supplierno']]['balance'] = -$mySupplier['balance']; 
			}
			
			$ValueOrderIDR = 0;
			$ValueOrderUSD = 0;
			$ValueOrderTHB = 0;
			$PaymentOrderIDR = 0;
			$PaymentOrderUSD = 0;
			$PaymentOrderTHB = 0;
			
			if ($myrow['currcode'] == 'IDR'){
				$ValueOrderIDR = $myrow['ordervalue'];
				$TotalValueOrderIDR += $ValueOrderIDR;
				$TotalValueAllOrders += $ValueOrderIDR;
				$SupplierBalanceIDR =  $Payments[$myrow['supplierno']]['balance'];
				$SupplierBalanceUSD =  0;
				$SupplierBalanceTHB =  0;
				if ($SupplierBalanceIDR >= $ValueOrderIDR){
					// we have enough balance to cover the order, no payment needed
					$PaymentOrderIDR = 0;
				}else{
					$PaymentOrderIDR = $ValueOrderIDR - $SupplierBalanceIDR;
					$AcumIDR = $AcumIDR + $PaymentOrderIDR; 
					$TotalValueAllPayments = $TotalValueAllPayments + $PaymentOrderIDR; 
				}
			}elseif	($myrow['currcode'] == 'USD'){
				$ValueOrderUSD = $myrow['ordervalue'];
				$TotalValueOrderUSD += $ValueOrderUSD;
				$TotalValueAllOrders += ($ValueOrderUSD/$myrow['exchangerate']*STANDARD_COST_FACTOR_FOREIGN);
				$SupplierBalanceIDR =  0;
				$SupplierBalanceUSD =  $Payments[$myrow['supplierno']]['balance'];
				$SupplierBalanceTHB =  0;
				if ($SupplierBalanceUSD >= $ValueOrderUSD){
					// we have enough balance to cover the order, no payment needed
					$PaymentOrderUSD = 0;
				}else{
					$PaymentOrderUSD = $ValueOrderUSD - $SupplierBalanceUSD;
					$AcumUSD = $AcumUSD + $PaymentOrderUSD; 
					$TotalValueAllPayments = $TotalValueAllPayments + ($PaymentOrderUSD/$myrow['exchangerate']); 
				}
			}elseif	($myrow['currcode'] == 'THB'){
				$ValueOrderTHB = $myrow['ordervalue'];
				$TotalValueOrderTHB += $ValueOrderTHB;
				$TotalValueAllOrders += ($ValueOrderTHB/$myrow['exchangerate']*STANDARD_COST_FACTOR_FOREIGN);
				$SupplierBalanceIDR =  0;
				$SupplierBalanceUSD =  0;
				$SupplierBalanceTHB =  $Payments[$myrow['supplierno']]['balance'];
				if ($SupplierBalanceTHB >= $ValueOrderTHB){
					// we have enough balance to cover the order, no payment needed
					$PaymentOrderTHB = 0;
				}else{
					$PaymentOrderTHB = $ValueOrderTHB - $SupplierBalanceTHB;
					$AcumTHB = $AcumTHB + $PaymentOrderTHB; 
					$TotalValueAllPayments = $TotalValueAllPayments + ($PaymentOrderTHB/$myrow['exchangerate']); 
				}
			}
			if ($FieldName2 == ""){
				$Date2 = "";
			}else{
				$Date2 = ConvertSQLDate($myrow['reportdate2']);
			}
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
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
					$i, 
					$CodeLink, 
					$myrow['supplierno'],
					ConvertSQLDate($myrow['reportdate']), 
					$Date2, 
					$myrow['shipmentawb'],
					locale_number_format_zero_blank($ValueOrderIDR,0),
					locale_number_format_zero_blank($ValueOrderUSD,0),
					locale_number_format_zero_blank($ValueOrderTHB,0),
					locale_number_format_zero_blank($SupplierBalanceIDR,0),
					locale_number_format_zero_blank($SupplierBalanceUSD,0),
					locale_number_format_zero_blank($SupplierBalanceTHB,0),
					locale_number_format_zero_blank($PaymentOrderIDR,0),
					locale_number_format_zero_blank($PaymentOrderUSD,0),
					locale_number_format_zero_blank($PaymentOrderTHB,0),
					locale_number_format_zero_blank($AcumIDR,0),
					locale_number_format_zero_blank($AcumUSD,0),
					locale_number_format_zero_blank($AcumTHB,0)
					);
			// update the supplier balance after the order 
			$Payments[$myrow['supplierno']]['balance'] = $Payments[$myrow['supplierno']]['balance'] - $myrow['ordervalue']; 
			if ($Payments[$myrow['supplierno']]['balance'] < 0){
				$Payments[$myrow['supplierno']]['balance'] = 0;
			}
			$i++;
		}
		if (($TypeOfCode == "IN NEGOTIAION WITH SUPPLIER") OR
			($TypeOfCode == "ON PRODUCTION") OR 
			($TypeOfCode == "FINISHED BUT NOT PAID") OR 
			($TypeOfCode == "STILL NOT FULLY PAID") OR 
			($TypeOfCode == "ARRIVING IN NEXT DAYS")){

			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
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
					'', 
					'', 
					'TOTAL ORDERS',
					'', 
					'', 
					'', 
					locale_number_format_zero_blank($TotalValueOrderIDR,0),
					locale_number_format_zero_blank($TotalValueOrderUSD,0),
					locale_number_format_zero_blank($TotalValueOrderTHB,0),
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					locale_number_format_zero_blank($TotalValueAllPayments,0),
					'', 
					'' 
					);
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
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
					'', 
					'', 
					'TOTAL ORDERS @ SC',
					'IDR', 
					'', 
					'', 
					locale_number_format_zero_blank($TotalValueAllOrders,0),
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'' 
					);
		}
		if ($TypeOfCode == "ARRIVING IN NEXT DAYS"){
			$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
			$SQL = "SELECT SUM(amount) AS cogs
					FROM  gltrans 
					WHERE   trandate >= '". $StartDate ."'		
						AND (account IN " . GL_COGS_GOODS ."
							OR account IN " . GL_COGS_OTHERS . ")";
			$result = DB_query($SQL);
			$myrow = DB_fetch_array($result);
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
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
					'', 
					'', 
					'COGS',
					'IDR', 
					'', 
					'', 
					locale_number_format_zero_blank($myrow['cogs'],0),
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'' 
					);
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
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
					'', 
					'', 
					'DIFFERENCE STOCK',
					'IDR', 
					'', 
					'', 
					locale_number_format_zero_blank($TotalValueAllOrders-$myrow['cogs'],0),
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'' 
					);

			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
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
					'', 
					'', 
					'MONTHLY STOCK CHANGE',
					'IDR', 
					'', 
					'', 
					locale_number_format_zero_blank(($TotalValueAllOrders-$myrow['cogs'])/$maxdays*30,0),
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'', 
					'' 
					);
			
		}
		echo '</table>
				</div>';
	}
}

function PurchaseOrdersProcessTime($NumDays, $RootPath, $db){

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	
	$SQL = "SELECT suppliers.address6, 
				COUNT(purchorders.orderno) AS numorders,
				AVG(datediff(purchorders.deliverydate,purchorders.orddate)) AS productiondays,
				AVG(datediff(purchorders.paymentdate,purchorders.deliverydate)) AS paymentdays,
				AVG(datediff(purchorders.shipmentdate,purchorders.paymentdate)) AS shipmentdays,
				AVG(datediff(purchorders.customsdate,purchorders.shipmentdate)) AS transitdays,
				AVG(datediff(purchorders.arrivaldate,purchorders.customsdate)) AS customsdays,
				MIN(datediff(purchorders.arrivaldate,purchorders.orddate)) AS mintotaldays,
				MAX(datediff(purchorders.arrivaldate,purchorders.orddate)) AS maxtotaldays,
				AVG(datediff(purchorders.arrivaldate,purchorders.orddate)) AS avgtotaldays
			FROM purchorders, suppliers
			WHERE purchorders.supplierno = suppliers.supplierid
				AND purchorders.arrivaldate >= '" . $StartDate. "'
				AND purchorders.status = 'Completed'
				AND purchorders.klstatus != '1000'
				AND purchorders.arrivaldate != purchorders.orddate
				AND suppliers.address6 != 'Indonesia'
			GROUP BY address6";
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Process time (in days) for POs arrived during the last ') . $NumDays . " days" . ' </strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Country') . '</th>
							<th class="ascending">' . _('#POs') . '</th>
							<th class="ascending">' . _('Production') . '</th>
							<th class="ascending">' . _('Payment') . '</th>
							<th class="ascending">' . _('Ready To Ship') . '</th>
							<th class="ascending">' . _('Transit') . '</th>
							<th class="ascending">' . _('Customs') . '</th>
							<th class="ascending">' . _('Min Days') . '</th>
							<th class="ascending">' . _('Max Days') . '</th>
							<th class="ascending">' . _('Average Days') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($myrow['productiondays'] < 0) {$myrow['productiondays'] = 0;}
			if ($myrow['paymentdays'] < 0) {$myrow['paymentdays'] = 0;}
			if ($myrow['shipmentdays'] < 0) {$myrow['shipmentdays'] = 0;}
			if ($myrow['transitdays'] < 0) {$myrow['transitdays'] = 0;}
			if ($myrow['customsdate'] < 0) {$myrow['customsdate'] = 0;}
			if ($myrow['mintotaldays'] < 0) {$myrow['mintotaldays'] = 0;}
			if ($myrow['maxtotaldays'] < 0) {$myrow['maxtotaldays'] = 0;}
			if ($myrow['avgtotaldays'] < 0) {$myrow['avgtotaldays'] = 0;}

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
					</tr>', 
					$myrow['address6'], 
					locale_number_format($myrow['numorders'],0),
					locale_number_format($myrow['productiondays'],0),
					locale_number_format($myrow['paymentdays'],0),
					locale_number_format($myrow['shipmentdays'],0),
					locale_number_format($myrow['transitdays'],0),
					locale_number_format($myrow['customsdate'],0),
					locale_number_format($myrow['mintotaldays'],0),
					locale_number_format($myrow['maxtotaldays'],0),
					locale_number_format($myrow['avgtotaldays'],0)
					);
			$i++;
		}
	}
	// Total Overseas PO's
	$SQL = "SELECT COUNT(purchorders.orderno) AS numorders,
				AVG(datediff(purchorders.deliverydate,purchorders.orddate)) AS productiondays,
				AVG(datediff(purchorders.paymentdate,purchorders.deliverydate)) AS paymentdays,
				AVG(datediff(purchorders.shipmentdate,purchorders.paymentdate)) AS shipmentdays,
				AVG(datediff(purchorders.customsdate,purchorders.shipmentdate)) AS transitdays,
				AVG(datediff(purchorders.arrivaldate,purchorders.customsdate)) AS customsdays,
				MIN(datediff(purchorders.arrivaldate,purchorders.orddate)) AS mintotaldays,
				MAX(datediff(purchorders.arrivaldate,purchorders.orddate)) AS maxtotaldays,
				AVG(datediff(purchorders.arrivaldate,purchorders.orddate)) AS avgtotaldays
			FROM purchorders, suppliers
			WHERE purchorders.supplierno = suppliers.supplierid
				AND purchorders.arrivaldate >= '" . $StartDate. "'
				AND purchorders.status = 'Completed'
				AND purchorders.klstatus != '1000'
				AND purchorders.arrivaldate != purchorders.orddate
				AND suppliers.address6 != 'Indonesia'";
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($myrow['productiondays'] < 0) {$myrow['productiondays'] = 0;}
			if ($myrow['paymentdays'] < 0) {$myrow['paymentdays'] = 0;}
			if ($myrow['shipmentdays'] < 0) {$myrow['shipmentdays'] = 0;}
			if ($myrow['transitdays'] < 0) {$myrow['transitdays'] = 0;}
			if ($myrow['customsdate'] < 0) {$myrow['customsdate'] = 0;}
			if ($myrow['mintotaldays'] < 0) {$myrow['mintotaldays'] = 0;}
			if ($myrow['maxtotaldays'] < 0) {$myrow['maxtotaldays'] = 0;}
			if ($myrow['avgtotaldays'] < 0) {$myrow['avgtotaldays'] = 0;}

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
					</tr>', 
					'OVERSEAS', 
					locale_number_format($myrow['numorders'],0),
					locale_number_format($myrow['productiondays'],0),
					locale_number_format($myrow['paymentdays'],0),
					locale_number_format($myrow['shipmentdays'],0),
					locale_number_format($myrow['transitdays'],0),
					locale_number_format($myrow['customsdate'],0),
					locale_number_format($myrow['mintotaldays'],0),
					locale_number_format($myrow['maxtotaldays'],0),
					locale_number_format($myrow['avgtotaldays'],0)
					);
			$i++;
		}
	}
	// INDONESIAN PO's
	$SQL = "SELECT COUNT(purchorders.orderno) AS numorders,
				AVG(datediff(purchorders.deliverydate,purchorders.orddate)) AS productiondays,
				AVG(datediff(purchorders.paymentdate,purchorders.deliverydate)) AS paymentdays,
				AVG(datediff(purchorders.shipmentdate,purchorders.paymentdate)) AS shipmentdays,
				AVG(datediff(purchorders.customsdate,purchorders.shipmentdate)) AS transitdays,
				AVG(datediff(purchorders.arrivaldate,purchorders.customsdate)) AS customsdays,
				MIN(datediff(purchorders.arrivaldate,purchorders.orddate)) AS mintotaldays,
				MAX(datediff(purchorders.arrivaldate,purchorders.orddate)) AS maxtotaldays,
				AVG(datediff(purchorders.arrivaldate,purchorders.orddate)) AS avgtotaldays
			FROM purchorders, suppliers
			WHERE purchorders.supplierno = suppliers.supplierid
				AND purchorders.arrivaldate >= '" . $StartDate. "'
				AND purchorders.status = 'Completed'
				AND purchorders.klstatus != '1000'
				AND purchorders.arrivaldate != purchorders.orddate
				AND suppliers.address6 = 'Indonesia'";
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($myrow['productiondays'] < 0) {$myrow['productiondays'] = 0;}
			if ($myrow['paymentdays'] < 0) {$myrow['paymentdays'] = 0;}
			if ($myrow['shipmentdays'] < 0) {$myrow['shipmentdays'] = 0;}
			if ($myrow['transitdays'] < 0) {$myrow['transitdays'] = 0;}
			if ($myrow['customsdate'] < 0) {$myrow['customsdate'] = 0;}
			if ($myrow['mintotaldays'] < 0) {$myrow['mintotaldays'] = 0;}
			if ($myrow['maxtotaldays'] < 0) {$myrow['maxtotaldays'] = 0;}
			if ($myrow['avgtotaldays'] < 0) {$myrow['avgtotaldays'] = 0;}

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
					</tr>', 
					'Indonesia', 
					locale_number_format($myrow['numorders'],0),
					locale_number_format($myrow['productiondays'],0),
					locale_number_format($myrow['paymentdays'],0),
					locale_number_format($myrow['shipmentdays'],0),
					locale_number_format($myrow['transitdays'],0),
					locale_number_format($myrow['customsdate'],0),
					locale_number_format($myrow['mintotaldays'],0),
					locale_number_format($myrow['maxtotaldays'],0),
					locale_number_format($myrow['avgtotaldays'],0)
					);
			$i++;
		}

		echo '</table>
				</div>';
	}
}

function PurchaseOrdersWrongPlannedDates($RootPath, $db){
	$Today = date('Y-m-d');

	$SQL = "SELECT purchorders.orderno, 
				purchorders.supplierno,
				klpostatus.description,
				purchorders.orddate,
				purchorders.agreeddeliverydate,
				purchorders.deliverydate,
				purchorders.paymentdate,
				purchorders.shipmentdate,
				purchorders.customsdate,
				purchorders.arrivaldate,
				SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue,
				suppliers.currcode
			FROM purchorders, suppliers, klpostatus, purchorderdetails
			WHERE purchorders.supplierno = suppliers.supplierid
				AND purchorders.orderno = purchorderdetails.orderno
				AND klpostatus.paymentterm = suppliers.paymentterms
				AND klpostatus.code = purchorders.klstatus
				AND purchorderdetails.completed = 0
				AND purchorders.status IN ('Authorised', 'Printed', 'Pending')
				AND (   (purchorders.klstatus <= '1000' 
						AND (purchorders.orddate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus <= '2000' 
						AND (purchorders.deliverydate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'B1'
						AND (purchorders.paymentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '7000' AND suppliers.paymentterms = 'B2'
						AND (purchorders.paymentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O1'
						AND (purchorders.paymentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O2'
						AND (purchorders.paymentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O3'
						AND (purchorders.paymentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O4'
						AND (purchorders.paymentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O5'
						AND (purchorders.arrivaldate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O1'
						AND (purchorders.shipmentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O2'
						AND (purchorders.shipmentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O3'
						AND (purchorders.shipmentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O4'
						AND (purchorders.shipmentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O5'
						AND (purchorders.shipmentdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O1'
						AND (purchorders.customsdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O2'
						AND (purchorders.customsdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O3'
						AND (purchorders.customsdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O4'
						AND (purchorders.customsdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O5'
						AND (purchorders.customsdate < '" . $Today ."'))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '6000' 
						AND (purchorders.arrivaldate < '" . $Today ."'))
					)
				AND purchorders.arrivaldate != purchorders.orddate
			GROUP BY purchorders.orderno
			ORDER BY purchorders.orderno";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('POs with wrong planned dates OR wrong status') . ' </strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#PO') . '</th>
							<th class="ascending">' . _('Supplier') . '</th>
							<th class="ascending">' . _('Order value') . '</th>
							<th class="ascending">' . _('KL Status') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Agreed Delivery') . '</th>
							<th class="ascending">' . _('Delivery') . '</th>
							<th class="ascending">' . _('Payment') . '</th>
							<th class="ascending">' . _('Shipment') . '</th>
							<th class="ascending">' . _('Customs') . '</th>
							<th class="ascending">' . _('Arrival') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/PO_Header.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$OrderDate = ConvertSQLDate(substr($myrow['orddate'],0,10));
			if ($myrow['agreeddeliverydate'] == '0000-00-00'){
				$myrow['agreeddeliverydate'] = '';
			} else {
				$myrow['agreeddeliverydate'] = ConvertSQLDate($myrow['agreeddeliverydate']);
			}
			if ($myrow['deliverydate'] == '0000-00-00'){
				$myrow['deliverydate'] = '';
			} else {
				$myrow['deliverydate'] = ConvertSQLDate($myrow['deliverydate']);
			}
			if ($myrow['paymentdate'] == '0000-00-00'){
				$myrow['paymentdate'] = '';
			} else {
				$myrow['paymentdate'] = ConvertSQLDate($myrow['paymentdate']);
			}
			if ($myrow['shipmentdate'] == '0000-00-00'){
				$myrow['shipmentdate'] = '';
			} else {
				$myrow['shipmentdate'] = ConvertSQLDate($myrow['shipmentdate']);
			}
			if ($myrow['customsdate'] == '0000-00-00'){
				$myrow['customsdate'] = '';
			} else {
				$myrow['customsdate'] = ConvertSQLDate($myrow['customsdate']);
			}
			if ($myrow['arrivaldate'] == '0000-00-00'){
				$myrow['arrivaldate'] = '';
			} else {
				$myrow['arrivaldate'] = ConvertSQLDate($myrow['arrivaldate']);
			}
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$CodeLink, 
					$myrow['supplierno'], 
					locale_number_format($myrow['ordervalue'],0) . ' ' . $myrow['currcode'] ,
					$myrow['description'], 
					$OrderDate, 
					$myrow['agreeddeliverydate'], 
					$myrow['deliverydate'], 
					$myrow['paymentdate'], 
					$myrow['shipmentdate'], 
					$myrow['customsdate'], 
					$myrow['arrivaldate']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function RecentlyClosedTransferStatus($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays+1));
	$SQL = "SELECT reference,
					recdate,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = shiploc) AS locfrom,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = recloc) AS locto,
					SUM(recqty) AS receivedqty
			FROM loctransfers
			WHERE  recdate >= '" . $StartDate . "'
			GROUP BY reference
			ORDER BY recdate ASC, reference ASC";
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($maxdays == 1){
			echo '<p class="page_title_text" align="center"><strong>' . _('List of Transfers Closed today ') . ' </strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('List of Transfers Closed during last ') . $maxdays  . ' days</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Qty') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$total = 0;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					ConvertSQLDateTime($myrow['recdate']), 
					$CodeLink, 
					$myrow['locfrom'], 
					$myrow['locto'], 
					locale_number_format($myrow['receivedqty'],0)
					);
			$i++;
			$total = $total + $myrow['receivedqty'];
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'', 
				'', 
				'', 
				'Total', 
				locale_number_format($total,0)
				);
		echo '</table>
				</div>
				</form>';
	}
}

function SQLFilterStockmasterForOnlineShop($Type){
	/* Not discontinued
		Not some items in doscount and some not (items ending with -D)
		Not a set (items with "ST" in position 3 and 4 of code)
				Not the polishing cloth WKPC01
				AND stockmaster.stockid != 'WKPC01'
				THIS exclusion removed when created teh sales categories Accessories
		Not items starting with KLBE
		Not items starting with GOTA
		Not Tali Mie (items starting with TM-)
	*/
	$SQL = "";
	if ($Type == "ALL"){
		$SQL = " stockmaster.categoryid IN " . ONLINESHOP_AVAILABLE_STOCK_CATEGORIES . "
				AND stockmaster.discontinued = 0
				AND SUBSTR(stockmaster.stockid, -2, 2) != '-D' 
				AND SUBSTR(stockmaster.stockid, 3, 2) != 'ST'
				AND stockmaster.stockid NOT LIKE 'KLBE%'
				AND stockmaster.stockid NOT LIKE 'GOTA%'
				AND stockmaster.stockid NOT LIKE 'TM-%' ";
	}else if ($Type == "KL+BL"){
		$SQL = " stockmaster.categoryid IN " . ONLINESHOP_AVAILABLE_STOCK_KL_BLINK . "
				AND stockmaster.discontinued = 0
				AND SUBSTR(stockmaster.stockid, -2, 2) != '-D' 
				AND SUBSTR(stockmaster.stockid, 3, 2) != 'ST'
				AND stockmaster.stockid NOT LIKE 'KLBE%'
				AND stockmaster.stockid NOT LIKE 'GOTA%'
				AND stockmaster.stockid NOT LIKE 'TM-%' ";
	}
	return $SQL;
}

function TransfersDelayed($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT DISTINCT reference,
					shipdate,
					shiploc,
					recloc
			FROM loctransfers 
			WHERE  recqty < shipqty
				AND shipdate <= '". $StartDate ."'
			ORDER BY reference";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Transfers delayed more than ') . $maxdays . _(' days ') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					ConvertSQLDate($myrow['shipdate']), 
					$myrow['shiploc'], 
					$myrow['recloc'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function WrongStandardCost($Country, $StockCat, $StdFactor, $Tolerance, $Mode, $RootPath, $db){
/* FunctionMode means
	SHOWONLY: Shows data only
	SHOWLINK: Shows link to update the standard Cost manually
	UPDATEALL: Runs the update function for all items
*/
	$ToleranceHigh = 1 + $Tolerance;
	$ToleranceLow  = 1 - $Tolerance;
	
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				purchdata.supplierno,
				purchdata.conversionfactor,
				purchdata.price,
				suppliers.currcode,
				purchdata.suppliersuom,
				purchdata.effectivefrom,
				stockmaster.lastcostupdate,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS stdcost,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qoh,
				stockmaster.units,
				currencies.decimalplaces,
				currencies.rate
			FROM purchdata, stockmaster, suppliers, currencies
			WHERE  purchdata.stockid = stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND suppliers.address6 = '" . $Country . "'";
	if ($StockCat != ""){			
		$SQL = $SQL . " AND stockmaster.categoryid = '" . $StockCat . "'";
	}
	$SQL = $SQL . " AND suppliers.currcode =  currencies.currabrev
				AND (	(((purchdata.price / purchdata.conversionfactor) * " . $StdFactor . " * (1 / currencies.rate) * " . $ToleranceHigh . " ) 
						< (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))
					OR	(((purchdata.price / purchdata.conversionfactor) * " . $StdFactor . " * (1 / currencies.rate) * " . $ToleranceLow . " ) 
						> (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))
					)
				AND purchdata.supplierno = suppliers.supplierid
				AND purchdata.effectivefrom = (SELECT MAX(p2.effectivefrom)
												FROM purchdata p2
												WHERE p2.stockid = purchdata.stockid)
			ORDER BY stockmaster.stockid";
// EXPLAIN SQL 2014-05-31
// prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $StockCat . ' Items from ' . $Country . _(' with wrong Standard Cost') .  ' ---> Cost Factor = ' . locale_number_format($StdFactor, 2) . ' ---> Tolerance = '. locale_number_format($Tolerance * 100, 2) .'%</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		if ($Mode == "SHOWONLY"){
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Code') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('Supplier') . '</th>
								<th class="ascending">' . _('From') . '</th>
								<th class="ascending">' . _('Price') . '</th>
								<th class="ascending">' . _('Currency') . '</th>
								<th class="ascending">' . _('Rate') . '</th>
								<th class="ascending">' . _('Supplier UOM') . '</th>
								<th class="ascending">' . _('UOM Factor') . '</th>
								<th class="ascending">' . _('Date Std Cost') . '</th>
								<th class="ascending">' . _('Std Cost IDR') . '</th>
							</tr>';
		}else{
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Code') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('Supplier') . '</th>
								<th class="ascending">' . _('From') . '</th>
								<th class="ascending">' . _('Price') . '</th>
								<th class="ascending">' . _('Currency') . '</th>
								<th class="ascending">' . _('Rate') . '</th>
								<th class="ascending">' . _('Supplier UOM') . '</th>
								<th class="ascending">' . _('UOM Factor') . '</th>
								<th class="ascending">' . _('Date Std Cost') . '</th>
								<th class="ascending">' . _('Std Cost IDR') . '</th>
								<th class="ascending">' . _('QOH') . '</th>
								<th class="ascending">' . _('KL UOM') . '</th>
								<th class="ascending">' . _('Real Std Cost') . '</th>
								<th class="ascending">' . _('% Dif') . '</th>
							</tr>';
		}
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			
			$NewStdCost = $myrow['price'] / $myrow['conversionfactor'] * (1/$myrow['rate']) * $StdFactor;
			$Price = locale_number_format($myrow['price'],$myrow['decimalplaces']);
			$PurchasingLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $myrow['stockid'] . '&SupplierID='. $myrow['supplierno'] . '&Edit=1&EffectiveFrom='. $myrow['effectivefrom']  .' ">' . $Price . '</a>';
			if ($Mode == "SHOWONLY"){
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						$myrow['supplierno'], 
						ConvertSQLDate($myrow['effectivefrom']),
						$PurchasingLink,
						$myrow['currcode'], 
						locale_number_format(1/$myrow['rate'],2),
						$myrow['suppliersuom'], 
						locale_number_format($myrow['conversionfactor'],0),
						ConvertSQLDate($myrow['lastcostupdate']),
						locale_number_format($myrow['stdcost'],0)
						);
			}else{
				if($Mode == "UPDATEALL"){
					// UPDATEALL
					$StdCost = locale_number_format($NewStdCost,0);
					ChangeItemStandardCost($myrow['stockid'], $NewStdCost, $myrow['stdcost'], $myrow['qoh']);
				}else{
					// SHOWLINK
					$StdCost = '<a href="' . $RootPath . '/KLUpdateStandardCost.php?StockId=' . $myrow['stockid'] . '&NewCost=' . round($NewStdCost,0) .'">' . locale_number_format($NewStdCost,0) . '</a>';
				}
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						$myrow['supplierno'], 
						ConvertSQLDate($myrow['effectivefrom']),
						$PurchasingLink,
						$myrow['currcode'], 
						locale_number_format(1/$myrow['rate'],2),
						$myrow['suppliersuom'], 
						locale_number_format($myrow['conversionfactor'],0),
						ConvertSQLDate($myrow['lastcostupdate']),
						locale_number_format($myrow['stdcost'],0),
						locale_number_format($myrow['qoh'],0),
						$myrow['units'], 
						$StdCost,
						locale_number_format((($myrow['price'] / $myrow['conversionfactor'] * (1/$myrow['rate']) * $StdFactor)/$myrow['stdcost'] * 100)-100,1) . '%'
						);
			}
			$i++;
		}
		echo '</table>
				</div>';
	}
}

?>
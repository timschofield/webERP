<?php

/**************************************************************************************************
			FUNCTIONS RELATED CONTROL, PERFORMANCE OR OTHER KL BOARDS
**************************************************************************************************/

/**************************************************************************************************
ALPHABETICAL LIST OF FUNCTIONS:

ActiveTransfersByLocation - Shows pending transfers by location
ActiveTransferStatus - Shows status of active transfers
AverageKPIHistory - Shows average business KPI history
AverageSales - Shows average sales for different time periods
ChangeItemStandardCost - Updates the standard cost of an item
CheckPackagingToBeRefilled - Checks packaging that needs to be refilled
ComponentsToObsolete - Shows components that are not used in any BOM
ErrorsInTransfers - Shows errors in closed transfers during a specified period
FinishedStockDistribution - Shows distribution of finished stock by various criteria
FinishedStockDistributionByShopAndCategory - Shows finished stock distribution by shop and category
GetTopSalesField - Gets the field to be used in top sales queries based on days
GetTotalQtyItemsForSale - Gets total quantity of items for sale
GetTotalValueItemsForSale - Gets total value of items for sale
GoodsToBeProduced - Shows components ready to be transformed into sellable goods
InsuficientStockForShopPackaging - Shows shop packaging items with insufficient stock
ItemsWithoutRetailPrice - Shows items without retail price
LocationInformationReview - Shows shop information
MaintenanceTasksList - Shows maintenance tasks list
MaintenanceTasksDistribution - Shows distribution of maintenance tasks
OnlineMarketPlacePaymentPending - Shows online marketplace orders with pending payments
PackagingToBeRefilledFromGudang - Shows packaging that needs to be refilled from a specific location
POStatusControl - Shows purchase orders status control by type
PositionTopSalesItem - Returns the position of an item in top sales
PurchaseOrdersProcessTime - Shows process time for purchase orders
PurchaseOrdersWrongPlannedDates - Shows purchase orders with wrong planned dates
QualityIssuesByReason - Shows quality issues by reason during the last X days
RecentlyClosedTransferStatus - Shows recently closed transfers status
RoundPackagingTransfer - Rounds packaging transfer quantity
SQLFilterStockmasterForOnlineShop - Provides SQL filtering for online shop
ShowTotalItemsMoving - Shows total items moving to discount
StockAdjustmentsByReason - Shows stock adjustments by reason during the last X days
TransfersDelayed - Shows transfers delayed more than specified days
WrongStandardCost - Shows items with wrong standard cost
**************************************************************************************************/

/**************************************************************************************************************
* Function: ActiveTransfersByLocation
* Brief description: Shows pending transfers by location
* Parameters:
*   - $RootPath: Root path of the application
* Returns: None
**************************************************************************************************************/
function ActiveTransfersByLocation($RootPath){
	$TotalTransferIn = 0;
	$TotalTransferOut = 0;
	$TotalPcsIn = 0;
	$TotalPcsOut = 0;

	$SQL = "SELECT locations.locationname,
			(SELECT SUM(pendingqty)
				FROM loctransfers
				WHERE  pendingqty > 0
					AND loctransfers.shiploc = locations.loccode) as qtyout,
			(SELECT SUM(pendingqty)
				FROM loctransfers
				WHERE  pendingqty > 0
					AND loctransfers.recloc = locations.loccode) as qtyin,
			(SELECT COUNT(DISTINCT(reference))
				FROM loctransfers
				WHERE  pendingqty > 0
					AND loctransfers.shiploc = locations.loccode) as transferout,
			(SELECT COUNT(DISTINCT(reference))
				FROM loctransfers
				WHERE  pendingqty > 0
					AND loctransfers.recloc = locations.loccode) as transferin
			FROM locations
			WHERE locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . "
			ORDER BY (SELECT SUM(pendingqty)
				FROM loctransfers
				WHERE  pendingqty > 0
					AND (loctransfers.shiploc = locations.loccode OR loctransfers.recloc = locations.loccode)) DESC";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Pending Goods to be transferred by shop');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Shop') . '</th>
						<th class="SortedColumn">' . _('Transfer OUT') . '</th>
						<th class="SortedColumn">' . _('Transfer IN') . '</th>
						<th class="SortedColumn">' . _('Transfer Total') . '</th>
						<th class="SortedColumn">' . _('Pcs OUT') . '</th>
						<th class="SortedColumn">' . _('Pcs IN') . '</th>
						<th class="SortedColumn">' . _('Pcs Total') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$TotalTransferIn = $TotalTransferIn + $MyRow['transferin'];
			$TotalTransferOut = $TotalTransferOut + $MyRow['transferout'];
			$TotalPcsIn = $TotalPcsIn + $MyRow['qtyin'];
			$TotalPcsOut = $TotalPcsOut + $MyRow['qtyout'];

			echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $MyRow['locationname'] . '</td>
						<td class="number">' . locale_number_format($MyRow['transferout'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['transferin'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['transferout']+$MyRow['transferin'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qtyout'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qtyin'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qtyout']+$MyRow['qtyin'],0) . '</td>
					</tr>';
			$i++;
		}
		echo'</tbody>
			<tfooter>';
		echo '<tr class="striped_row">
					<td class="number">' . '' . '</td>
					<td>' . 'Total' . '</td>
					<td class="number">' . locale_number_format($TotalTransferOut,0) . '</td>
					<td class="number">' . locale_number_format($TotalTransferIn,0) . '</td>
					<td class="number">' . locale_number_format($TotalTransferOut+$TotalTransferIn,0) . '</td>
					<td class="number">' . locale_number_format($TotalPcsOut,0) . '</td>
					<td class="number">' . locale_number_format($TotalPcsIn,0) . '</td>
					<td class="number">' . locale_number_format($TotalPcsOut+$TotalPcsIn,0) . '</td>
				</tr>';
		InsertKPI("Transfers","Goods Pending to be transferred @ shops (pcs)", $TotalPcsOut+$TotalPcsIn);
		echo '</tfooter>
				</table>
				</div>';
	}
}

/**************************************************************************************************************
* Function: ActiveTransferStatus
* Brief description: Shows status of active transfers
* Parameters:
*   - $RootPath: Root path of the application
* Returns: None
**************************************************************************************************************/
function ActiveTransferStatus($RootPath){
	$SQL = "SELECT reference,
					shipdate,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = shiploc) AS locfrom,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = recloc) AS locto,
					SUM(pendingqty) AS pendingqty
			FROM loctransfers
			WHERE pendingqty > 0
			GROUP BY reference
			ORDER BY shipdate ASC, reference ASC";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('List of Active Transfers');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Date') . '</th>
						<th class="SortedColumn">' . _('Transfer') . '</th>
						<th class="SortedColumn">' . _('From') . '</th>
						<th class="SortedColumn">' . _('To') . '</th>
						<th class="SortedColumn">' . _('Qty') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		$Total = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . ConvertSQLDateTime($MyRow['shipdate']) . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['locfrom'] . '</td>
					<td>' . $MyRow['locto'] . '</td>
					<td class="number">' . locale_number_format($MyRow['pendingqty'], 0) . '</td>
					</tr>';
			$i++;
			$Total = $Total + $MyRow['pendingqty'];
		}
		echo'</tbody>
			<tfooter>';
		echo '<tr class="striped_row">
				<td class="number">' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . 'Total' . '</td>
				<td class="number">' . locale_number_format($Total, 0) . '</td>
				</tr>';
		InsertKPI("Transfers", "Active Transfers (pcs)", $Total);
		echo '</tfooter>
			  </table>
			  </div>';
	}
}

/**************************************************************************************************************
* Function: AverageKPIHistory
* Brief description: Shows average business KPI history for different time periods
* Parameters:
*   - $NumDaysA: Number of days for period A
*   - $NumDaysB: Number of days for period B
*   - $NumDaysC: Number of days for period C
*   - $NumDaysD: Number of days for period D
*   - $NumDaysE: Number of days for period E
*   - $NumDaysF: Number of days for period F
* Returns: None
**************************************************************************************************************/
function AverageKPIHistory($NumDaysA, $NumDaysB, $NumDaysC, $NumDaysD, $NumDaysE, $NumDaysF){

	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysA));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysB));
	$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysC));
	$StartDateD = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysD));
	$StartDateE = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysE));
	$StartDateF = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDaysF));

	$SQL = "SELECT bh1.class,
				bh1.concept,
				(SELECT AVG(value)
					FROM klkpi bh2
					WHERE bh1.class =  bh2.class
						AND bh1.concept =  bh2.concept
						AND bh2.date >= '". $StartDateA . "'
						AND bh2.date <= CURRENT_DATE) AS salesA,
				(SELECT AVG(value)
					FROM klkpi bh2
					WHERE bh1.class =  bh2.class
						AND bh1.concept =  bh2.concept
						AND bh2.date >= '". $StartDateB . "'
						AND bh2.date <= CURRENT_DATE) AS salesB,
				(SELECT AVG(value)
					FROM klkpi bh2
					WHERE bh1.class =  bh2.class
						AND bh1.concept =  bh2.concept
						AND bh2.date >= '". $StartDateC . "'
						AND bh2.date <= CURRENT_DATE) AS salesC,
				(SELECT AVG(value)
					FROM klkpi bh2
					WHERE bh1.class =  bh2.class
						AND bh1.concept =  bh2.concept
						AND bh2.date >= '". $StartDateD . "'
						AND bh2.date <= CURRENT_DATE) AS salesD,
				(SELECT AVG(value)
					FROM klkpi bh2
					WHERE bh1.class =  bh2.class
						AND bh1.concept =  bh2.concept
						AND bh2.date >= '". $StartDateE . "'
						AND bh2.date <= CURRENT_DATE) AS salesE,
				(SELECT AVG(value)
					FROM klkpi bh2
					WHERE bh1.class =  bh2.class
						AND bh1.concept =  bh2.concept
						AND bh2.date >= '". $StartDateF . "'
						AND bh2.date <= CURRENT_DATE) AS salesF
			FROM klkpi bh1
			GROUP BY bh1.class,
					bh1.concept
			ORDER BY bh1.class,
					bh1.concept";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "Average Business KPI for the last " . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . ", ". $NumDaysE . ", ". $NumDaysF . " days. Trend by " . $NumDaysD . " days.";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . 'Class' . '</th>
						<th class="SortedColumn">' . _('Concept') . '</th>
						<th class="SortedColumn">' . $NumDaysA . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysB . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysC . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysD . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysE . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysF . _(' days') . '</th>
						<th class="SortedColumn">' . _('Trend') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$Code = $MyRow['class'];
			$Name = $MyRow['concept'];

			$dailyA = locale_number_format_kpi(($MyRow['salesA']));
			$dailyB = locale_number_format_kpi(($MyRow['salesB']));
			$dailyC = locale_number_format_kpi(($MyRow['salesC']));
			$dailyD = locale_number_format_kpi(($MyRow['salesD']));
			$dailyE = locale_number_format_kpi(($MyRow['salesE']));
			$dailyF = locale_number_format_kpi(($MyRow['salesF']));
			
			// Fix division by zero error
			$Percent = 0;
			if (!empty($MyRow['salesC']) && $MyRow['salesC'] != 0) {
				$Percent = (($MyRow['salesD']) - ($MyRow['salesC'])) / ($MyRow['salesC']) * 100;
			}
			
			$Trend = " ";
			if ($Percent > MINIMUM_BUSINESS_HISTORY_TREND){
				$Trend = "Increasing " . locale_number_format($Percent, 0) . "%";
			}
			if ($Percent < -MINIMUM_BUSINESS_HISTORY_TREND){
				$Trend = "Decreasing " . locale_number_format($Percent, 0) . "%";
			}

			echo '<tr class="striped_row">
					<td>' . $i . '</td>
					<td>' . $Code . '</td>
					<td>' . $Name . '</td>
					<td class="number">' . $dailyA . '</td>
					<td class="number">' . $dailyB . '</td>
					<td class="number">' . $dailyC . '</td>
					<td class="number">' . $dailyD . '</td>
					<td class="number">' . $dailyE . '</td>
					<td class="number">' . $dailyF . '</td>
					<td>' . $Trend . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			  </table>
			  </div>';
	}
}

/**************************************************************************************************************
* Function: AverageSales
* Brief description: Shows average sales for different time periods
* Parameters:
*   - $TypeReport: Type of report (Shop, Online, etc.)
*   - $NumDaysA: Number of days for period A
*   - $NumDaysB: Number of days for period B
*   - $NumDaysC: Number of days for period C
*   - $NumDaysD: Number of days for period D
*   - $NumDaysE: Number of days for period E
*   - $NumDaysF: Number of days for period F
*   - $NumDaysSort: Number of days for sorting
*   - $Year: Year for report (CurrentYear or LastYear)
*   - $Shop: Shop code or "All" for all shops
* Returns: None
**************************************************************************************************************/
function AverageSales($TypeReport, $NumDaysA, $NumDaysB, $NumDaysC, $NumDaysD, $NumDaysE, $NumDaysF, $NumDaysSort, $Year, $Shop){

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
	$TotalDateMTD = 0;

	if ($Shop == "All"){
		$SQLByShop = "";
	}else{
		$SQLByShop = " AND salesorders.debtorno =  '". $Shop . "' ";
	}

	if ($TypeReport == "Shop"){
		// Optimized SQL using conditional aggregation
		$SQL = "SELECT
					dm.debtorno,
					dm.name,
					COALESCE(s.salesA, 0) AS salesA,
					COALESCE(s.salesB, 0) AS salesB,
					COALESCE(s.salesC, 0) AS salesC,
					COALESCE(s.salesD, 0) AS salesD,
					COALESCE(s.salesE, 0) AS salesE,
					COALESCE(s.salesF, 0) AS salesF,
					COALESCE(s.salesMTD, 0) AS salesMTD
				FROM debtorsmaster dm
				LEFT JOIN (
					SELECT
						so.debtorno,
						SUM(CASE WHEN so.orddate >= '". $StartDateA . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesA,
						SUM(CASE WHEN so.orddate >= '". $StartDateB . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesB,
						SUM(CASE WHEN so.orddate >= '". $StartDateC . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesC,
						SUM(CASE WHEN so.orddate >= '". $StartDateD . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesD,
						SUM(CASE WHEN so.orddate >= '". $StartDateE . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesE,
						SUM(CASE WHEN so.orddate >= '". $StartDateF . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesF,
						SUM(CASE WHEN so.orddate >= '". $StartDateMTD . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesMTD,
						SUM(CASE WHEN so.orddate >= '". $StartDateSort . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesSort
					FROM salesorders so
					INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
					WHERE sod.completed = 1
					  AND so.orddate >= '". $StartDateA . "' -- Use the earliest start date to limit initial scan
					  AND so.orddate <= '". $Yesterday . "'
					GROUP BY so.debtorno
				) s ON dm.debtorno = s.debtorno
				WHERE dm.typeid = 2
				ORDER BY COALESCE(s.salesSort, 0) DESC";
	}elseif ($TypeReport == "Online"){
		// Optimized SQL using conditional aggregation
		// Note: Assumes MySQL syntax. Adjust for other DBs if needed (e.g., CROSS APPLY for SQL Server).
		$SQL = "SELECT
					dm.debtorno,
					dm.name,
					COALESCE(CASE WHEN cur.rate <> 0 THEN s.salesA_unconverted / cur.rate ELSE 0 END, 0) AS salesA,
					COALESCE(CASE WHEN cur.rate <> 0 THEN s.salesB_unconverted / cur.rate ELSE 0 END, 0) AS salesB,
					COALESCE(CASE WHEN cur.rate <> 0 THEN s.salesC_unconverted / cur.rate ELSE 0 END, 0) AS salesC,
					COALESCE(CASE WHEN cur.rate <> 0 THEN s.salesD_unconverted / cur.rate ELSE 0 END, 0) AS salesD,
					COALESCE(CASE WHEN cur.rate <> 0 THEN s.salesE_unconverted / cur.rate ELSE 0 END, 0) AS salesE,
					COALESCE(CASE WHEN cur.rate <> 0 THEN s.salesF_unconverted / cur.rate ELSE 0 END, 0) AS salesF,
					COALESCE(CASE WHEN cur.rate <> 0 THEN s.salesMTD_unconverted / cur.rate ELSE 0 END, 0) AS salesMTD
				FROM debtorsmaster dm
				INNER JOIN currencies cur ON dm.currcode = cur.currabrev
				LEFT JOIN (
					SELECT
						so.debtorno,
						SUM(CASE WHEN so.orddate >= '". $StartDateA . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesA_unconverted,
						SUM(CASE WHEN so.orddate >= '". $StartDateB . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesB_unconverted,
						SUM(CASE WHEN so.orddate >= '". $StartDateC . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesC_unconverted,
						SUM(CASE WHEN so.orddate >= '". $StartDateD . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesD_unconverted,
						SUM(CASE WHEN so.orddate >= '". $StartDateE . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesE_unconverted,
						SUM(CASE WHEN so.orddate >= '". $StartDateF . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesF_unconverted,
						SUM(CASE WHEN so.orddate >= '". $StartDateMTD . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesMTD_unconverted
					FROM salesorders so
					INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
					WHERE sod.completed = 1
					  AND so.orddate >= '". $StartDateA . "' -- Use the earliest start date
					  AND so.orddate <= '". $Yesterday . "'
					GROUP BY so.debtorno
				) s ON dm.debtorno = s.debtorno
				WHERE dm.typeid IN (9, 10)
				ORDER BY dm.debtorno";
	}else{
		// Optimized SQL using conditional aggregation
		$SQL = "SELECT
					sm.salesmancode,
					sm.salesmanname,
					COALESCE(s.salesA, 0) AS salesA,
					COALESCE(s.salesB, 0) AS salesB,
					COALESCE(s.salesC, 0) AS salesC,
					COALESCE(s.salesD, 0) AS salesD,
					COALESCE(s.salesE, 0) AS salesE,
					COALESCE(s.salesF, 0) AS salesF,
					COALESCE(s.salesMTD, 0) AS salesMTD
				FROM salesman sm
				LEFT JOIN (
					SELECT
						so.salesperson,
						SUM(CASE WHEN so.orddate >= '". $StartDateA . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesA,
						SUM(CASE WHEN so.orddate >= '". $StartDateB . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesB,
						SUM(CASE WHEN so.orddate >= '". $StartDateC . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesC,
						SUM(CASE WHEN so.orddate >= '". $StartDateD . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesD,
						SUM(CASE WHEN so.orddate >= '". $StartDateE . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesE,
						SUM(CASE WHEN so.orddate >= '". $StartDateF . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesF,
						SUM(CASE WHEN so.orddate >= '". $StartDateMTD . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesMTD,
						SUM(CASE WHEN so.orddate >= '". $StartDateSort . "' AND so.orddate <= '". $Yesterday . "' THEN sod.linenetprice ELSE 0 END) AS salesSort
					FROM salesorders so
					INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
					WHERE sod.completed = 1
					  AND so.orddate >= '". $StartDateA . "' -- Use the earliest start date
					  AND so.orddate <= '". $Yesterday . "'
					  " . $SQLByShop . " -- Apply shop filter here if needed
					GROUP BY so.salesperson
				) s ON sm.salesmancode = s.salesperson
				WHERE sm.current = 1
				ORDER BY COALESCE(s.salesSort, 0) DESC";
	}

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($Year == "LastYear"){
			$TableTitleText = _('LAST YEAR Moving Average Daily sales by ') . $TypeReport . " during the last " . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . ", ". $NumDaysE . ", ". $NumDaysF . " days. Sorted by " . $NumDaysSort . " days. Trend by " . $NumDaysD . " days.";
		} else {
			if ($Shop == "All"){
				$TableTitleText = _('Current Moving Average Daily sales by ') . $TypeReport . " during the last " . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . ", ". $NumDaysE . ", ". $NumDaysF . " days. Sorted by " . $NumDaysSort . " days. Trend by " . $NumDaysD . " days.";
			} else {
				$TableTitleText = _('Current Moving Average Daily sales in ') . $Shop . ' by ' . $TypeReport . " during the last " . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . ", ". $NumDaysE . ", ". $NumDaysF . " days. Sorted by " . $NumDaysSort . " days. Trend by " . $NumDaysD . " days.";
			}
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th>' . $TypeReport . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . $NumDaysA . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysB . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysC . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysD . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysE . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysF . _(' days') . '</th>
						<th class="SortedColumn">' . _('MTD') . '</th>
						<th class="SortedColumn">' . _('Trend') . '</th>
						<th class="SortedColumn">' . 'Monthly Forecast' . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if (($TypeReport == "Shop") OR ($TypeReport == "Online")){
				$Code = $MyRow['debtorno'];
				$Name = $MyRow['name'];
			} else {
				$Code = $MyRow['salesmancode'];
				$Name = $MyRow['salesmanname'];
			}

			// Fix division by zero errors
			$dailyA = ($NumDaysA != 0) ? $MyRow['salesA'] / $NumDaysA : 0;
			$dailyB = ($NumDaysB != 0) ? $MyRow['salesB'] / $NumDaysB : 0;
			$dailyC = ($NumDaysC != 0) ? $MyRow['salesC'] / $NumDaysC : 0;
			$dailyD = ($NumDaysD != 0) ? $MyRow['salesD'] / $NumDaysD : 0;
			$dailyE = ($NumDaysE != 0) ? $MyRow['salesE'] / $NumDaysE : 0;
			$dailyF = ($NumDaysF != 0) ? $MyRow['salesF'] / $NumDaysF : 0;
			
			// Fix division by zero error
			$Percent = 0;
			if ($NumDaysD != 0 && $NumDaysC != 0 && $MyRow['salesC'] != 0) {
				$Percent = (($MyRow['salesD'] / $NumDaysD) - ($MyRow['salesC'] / $NumDaysC)) / ($MyRow['salesC'] / $NumDaysC) * 100;
			}
			
			$Trend = " ";
			if ($Percent > MINIMUM_AVERAGE_SALES_TREND){
				$Trend = "Improving " . locale_number_format($Percent, 0) . "%";
			}
			if ($Percent < -MINIMUM_AVERAGE_SALES_TREND){
				$Trend = "Degrading " . locale_number_format($Percent, 0) . "%";
			}
			// Fix division by zero error for forecast calculation
			$Forecast = 0;
			if ($NumDaysD != 0 && $NumDaysE != 0) {
				$Forecast = round((($MyRow['salesD'] / $NumDaysD) + ($MyRow['salesE'] / $NumDaysE)) / 2 * 30, -5);
			}

			$MTD = locale_number_format($MyRow['salesMTD'], 0);

			if ($dailyA + $dailyB + $dailyC + $dailyD + $dailyE + $dailyF > 0){
				// if there is any daily report not zero...
				echo '<tr class="striped_row">
						<td>' . $i . '</td>
						<td>' . $Code . '</td>
						<td>' . $Name . '</td>
						<td class="number">' . locale_number_format($dailyA, 0) . '</td>
						<td class="number">' . locale_number_format($dailyB, 0) . '</td>
						<td class="number">' . locale_number_format($dailyC, 0) . '</td>
						<td class="number">' . locale_number_format($dailyD, 0) . '</td>
						<td class="number">' . locale_number_format($dailyE, 0) . '</td>
						<td class="number">' . locale_number_format($dailyF, 0) . '</td>
						<td class="number">' . $MTD . '</td>
						<td>' . $Trend . '</td>
						<td class="number">' . locale_number_format($Forecast, 0) . '</td>
						</tr>';

			}
			$TotalDateA = $TotalDateA + ($NumDaysA != 0 ? ($MyRow['salesA'] / $NumDaysA) : 0);
			$TotalDateB = $TotalDateB + ($NumDaysB != 0 ? ($MyRow['salesB'] / $NumDaysB) : 0);
			$TotalDateC = $TotalDateC + ($NumDaysC != 0 ? ($MyRow['salesC'] / $NumDaysC) : 0);
			$TotalDateD = $TotalDateD + ($NumDaysD != 0 ? ($MyRow['salesD'] / $NumDaysD) : 0);
			$TotalDateE = $TotalDateE + ($NumDaysE != 0 ? ($MyRow['salesE'] / $NumDaysE) : 0);
			$TotalDateF = $TotalDateF + ($NumDaysF != 0 ? ($MyRow['salesF'] / $NumDaysF) : 0);
			$TotalDateMTD = $TotalDateMTD + $MyRow['salesMTD'];
			
			// Fix division by zero error
			$Percent = 0;
			if ($TotalDateC != 0) {
				$Percent = ($TotalDateD - $TotalDateC) / $TotalDateC * 100;
			}
			
			$TotalForecast = $TotalForecast + $Forecast;
			$i++;
		}
		echo'</tbody>
			<tfooter>';
		if (($TypeReport == "Shop") OR ($TypeReport == "Online")){
			$Trend = " ";
			if ($Percent > 0){
				$Trend = "Improving " . locale_number_format($Percent, 0) . "%";
			}
			if ($Percent < 0){
				$Trend = "Degrading " . locale_number_format($Percent, 0) . "%";
			}
			echo '<tr class="striped_row">
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "TOTAL" . '</td>
					<td class="number">' . locale_number_format($TotalDateA, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateB, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateC, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateD, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateE, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateF, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateMTD, 0) . '</td>
					<td>' . $Trend . '</td>
					<td class="number">' . locale_number_format($TotalForecast, 0) . '</td>
					</tr>';
			$i--;
			// Fix division by zero error
			$i = max(1, $i - 1); // Ensure $i is at least 1
			echo '<tr class="striped_row">
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "AVERAGE" . '</td>
					<td class="number">' . locale_number_format($TotalDateA / $i, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateB / $i, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateC / $i, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateD / $i, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateE / $i, 0) . '</td>
					<td class="number">' . locale_number_format($TotalDateF / $i, 0) . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td class="number">' . locale_number_format($TotalForecast / 30, 0) . '</td>
					<td>' . "" . '</td>
					</tr>';
		}
		echo '</tfooter>
			  </table>
			  </div>';
	}

	$NumDaysA = str_pad($NumDaysA, 3, '0', STR_PAD_LEFT);
	$NumDaysB = str_pad($NumDaysB, 3, '0', STR_PAD_LEFT);
	$NumDaysC = str_pad($NumDaysC, 3, '0', STR_PAD_LEFT);
	$NumDaysD = str_pad($NumDaysD, 3, '0', STR_PAD_LEFT);
	$NumDaysE = str_pad($NumDaysE, 3, '0', STR_PAD_LEFT);

	if (($TypeReport == "Shop") AND ($Year == "CurrentYear")){
		InsertKPI("Sales", "Retail Daily Sales Average Last " . $NumDaysD . " days (IDR)", $TotalDateD);
		InsertKPI("Sales", "Retail Daily Sales Average Last " . $NumDaysE . " days (IDR)", $TotalDateE);
	}
	if (($TypeReport == "Online") AND ($Year == "CurrentYear")){
		InsertKPI("Sales", "Online Daily Sales Average Last " . $NumDaysD . " days (IDR)", $TotalDateD);
		InsertKPI("Sales", "Online Daily Sales Average Last " . $NumDaysE . " days (IDR)", $TotalDateE);
	}
}

function ChangeItemStandardCost($StockID, $NewCost, $OldCost, $QOH){
	DB_Txn_Begin();
	ItemCostUpdateGL($StockID, $NewCost, $OldCost, $QOH);
	$SQL = "UPDATE stockmaster SET	materialcost='" . $NewCost . "',
									labourcost='" . 0 . "',
									overheadcost='" . 0 . "',
									lastcost='" . $OldCost . "',
									lastcostupdate = CURRENT_DATE
							WHERE stockid='" . $StockID . "'";

	$ErrMsg = _('The cost details for the stock item could not be updated because');
	$DbgMsg = _('The SQL that failed was');
	DB_query($SQL,$ErrMsg,$DbgMsg,true);
	DB_Txn_Commit();
	UpdateCost($StockID); //Update any affected BOMs
}

function MaintenanceTasksList($Status, $NumDays){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	if ($Status == "OPEN"){
		$WhereStatus = "WHERE klmaintenancetasks.closed = 0";
		$TableTitleText = 'Open Maintenance Tasks';
	} else {
		$WhereStatus = "WHERE klmaintenancetasks.closed = 1
							AND closedate >= '" . $FromDate . "'";
		$TableTitleText = 'Closed Maintenance Tasks during the last ' . $NumDays . ' days';
	}
	$SQL = "SELECT klmaintenancetasks.counterindex,
				klmaintenancetasks.loccode,
				locations.locationname,
				klmaintenancetasks.maintenancetype,
				klmaintenancetypes.description AS typedescription,
				klmaintenancetasks.description AS taskdescription,
				klmaintenancetasks.creationuser,
				klmaintenancetasks.creationdate,
				klmaintenancetasks.closeuser,
				klmaintenancetasks.closedate
			FROM klmaintenancetasks
			INNER JOIN locations
				ON locations.loccode = klmaintenancetasks.loccode
			INNER JOIN klmaintenancetypes
				ON klmaintenancetypes.maintenancetype = klmaintenancetasks.maintenancetype
			INNER JOIN locationusers
				ON locationusers.loccode = klmaintenancetasks.loccode
					AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
					AND locationusers.canview = 1 " .
			$WhereStatus . "
			ORDER BY klmaintenancetasks.counterindex";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Task') . '</th>
							<th class="SortedColumn">' . _('Location') . '</th>
							<th class="SortedColumn">' . _('Type') . '</th>
							<th class="SortedColumn">' . _('Description') . '</th>
							<th class="SortedColumn">' . _('Created By') . '</th>
							<th class="SortedColumn">' . _('Created Date') . '</th>
							<th class="SortedColumn">' . _('Closed By') . '</th>
							<th class="SortedColumn">' . _('Closed Date') . '</th>
							<th class="SortedColumn">' . _('Days') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$i++;
			if ($Status == "OPEN"){
				$CloseUser = "";
				$CloseDate = "";
				$DaysOpen = "";
			} else {
				$CloseUser = $MyRow['closeuser'];
				$CloseDate = ConvertSQLDateTime($MyRow['closedate']);
				$DaysOpen = locale_number_format(abs(strtotime($MyRow['closedate']) - strtotime($MyRow['creationdate'])) / 60 / 60 / 24, 1);
			}
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . locale_number_format($MyRow['counterindex'], 0) . '</td>
					<td>' . $MyRow['locationname'] . '</td>
					<td>' . $MyRow['maintenancetype'] . '</td>
					<td>' . $MyRow['taskdescription'] . '</td>
					<td>' . $MyRow['creationuser'] . '</td>
					<td>' . ConvertSQLDateTime($MyRow['creationdate']) . '</td>
					<td>' . $CloseUser . '</td>
					<td>' . $CloseDate . '</td>
					<td class="number">' . $DaysOpen . '</td>
					</tr>';
			// check if there are any updates to show
			$SQLUpdates = "SELECT klmaintenancetaskupdates.counterindex,
								klmaintenancetaskupdates.description AS updatedescription,
								klmaintenancetaskupdates.updateuser,
								klmaintenancetaskupdates.updatedate
							FROM klmaintenancetaskupdates
							WHERE klmaintenancetaskupdates.taskcounter = '" . $MyRow['counterindex'] . "'
							ORDER BY klmaintenancetaskupdates.counterindex";
			$ResultUpdates = DB_query($SQLUpdates);
			while ($MyUpdates = DB_fetch_array($ResultUpdates)) {
				echo '<tr class="striped_row">
						<td class="number">' . '' . '</td>
						<td class="number">' . '' . '</td>
						<td>' . '' . '</td>
						<td>' . '' . '</td>
						<td>' . $MyUpdates['updatedescription'] . '</td>
						<td>' . $MyUpdates['updateuser'] . '</td>
						<td>' . ConvertSQLDateTime($MyUpdates['updatedate']) . '</td>
						<td>' . '' . '</td>
						<td>' . '' . '</td>
						<td class="number">' . '' . '</td>
						</tr>';
			}
		}
		echo '</tbody>
			  </table>
			  </div>';
	}
}

function ComponentsToObsolete($ShowOnlyTotal, $ShowLimit, $RootPath){
	$SQL = "SELECT s.stockid,
					s.units,
					s.description,
					(s.actualcost) AS stdcost,
					(SELECT SUM(quantity)
						FROM locstock
						WHERE s.stockid = locstock.stockid) AS qoh
			FROM stockmaster AS s
			WHERE s.categoryid IN " . LIST_STOCK_CATEGORIES_COMPONENTS . "
				AND s.discontinued = 0
				AND NOT EXISTS(
					SELECT bom.component
					FROM bom, stockmaster AS stP, stockmaster AS stC
					WHERE bom.parent = stP.stockid
						AND bom.component = stC.stockid
						AND s.stockid = bom.component
						AND stP.discontinued = 0)";
	$Result = DB_query($SQL);
	$TotalCost = 0;
	if (DB_num_rows($Result) != 0){
		if (!$ShowOnlyTotal){
			$TableTitleText = _('Components NOT Used in any BOM. Use them in any product (IF QOH > 0) OR flag as obsolete (IF QOH = 0).');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('UOM') . '</th>
								<th class="SortedColumn">' . _('Stock value') . '</th>
							</tr>
							</thead>
							<tbody>';
			echo $TableHeader;
		}
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$TotalCost = $TotalCost + ($MyRow['qoh'] * $MyRow['stdcost']);
			if (!$ShowOnlyTotal){
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($MyRow['qoh'], 0) . '</td>
						<td>' . $MyRow['units'] . '</td>
						<td class="number">' . locale_number_format($MyRow['qoh'] * $MyRow['stdcost'], 0) . '</td>
						</tr>';
			}
			$i++;
		}
		if (!$ShowOnlyTotal){
			echo'</tbody>
				<tfooter>';
			echo '<tr class="striped_row">
					<td class="number">' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . 'Total Cost' . '</td>
					<td class="number">' . '' . '</td>
					<td>' . '' . '</td>
					<td class="number">' . locale_number_format($TotalCost, 0) . '</td>
					</tr>';
			echo '</tfooter>
				  </table>
				  </div>';
		} elseif ($TotalCost >= $ShowLimit){
			$WarningTitleText = "Components NOT Used in any BOM cost over the limit. Current cost = " . locale_number_format($TotalCost, 0);
			ShowWarningTitle($WarningTitleText);

		}
	}
	InsertKPI("Components", "Components not used in any BOM (IDR)", $TotalCost);
}

function ErrorsInTransfers($maxdays, $RootPath){
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
			WHERE loctransfers.shipdate >= '" . $StartDate . "'
			GROUP BY loctransfers.reference
			HAVING SUM(loctransfers.pendingqty) = 0
			ORDER BY loctransfers.reference";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Errors on Closed Transfers during the last ') . $maxdays . _(' days ');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Transfer') . '</th>
							<th class="SortedColumn">' . _('Date') . '</th>
							<th class="SortedColumn">' . _('From') . '</th>
							<th class="SortedColumn">' . _('To') . '</th>
							<th class="SortedColumn">' . _('Total Models') . '</th>
							<th class="SortedColumn">' . _('Cancelled Models') . '</th>
							<th class="SortedColumn">' . _('% Models') . '</th>
							<th class="SortedColumn">' . _('Total Qty') . '</th>
							<th class="SortedColumn">' . _('Cancelled Qty') . '</th>
							<th class="SortedColumn">' . _('% Qty') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$NumTransfers = 1;
		$NumTransfersWithErrors = 0;

		$TotalShippedModels = 0;
		$TotalCancelledModels = 0;
		$TotalShippedQty = 0;
		$TotalCancelledQty = 0;

		while ($MyRow = DB_fetch_array($Result)) {

			$TotalShippedModels += $MyRow['shipped_models'];
			$TotalCancelledModels += $MyRow['cancelled_models'];
			$TotalShippedQty += $MyRow['shipped_quantity'];
			$TotalCancelledQty += $MyRow['cancelled_quantity'];

			if (($MyRow['cancelled_models'] != 0) OR ($MyRow['cancelled_quantity'] != 0)){
				$TransferLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $NumTransfersWithErrors . '</td>
						<td class="number">' . $TransferLink . '</td>
						<td>' . ConvertSQLDateTime($MyRow['shipdate']) . '</td>
						<td>' . $MyRow['shiploc'] . '</td>
						<td>' . $MyRow['recloc'] . '</td>
						<td class="number">' . locale_number_format($MyRow['shipped_models'], 0) . '</td>
						<td class="number">' . locale_number_format($MyRow['cancelled_models'], 0) . '</td>
						<td class="number">' . locale_number_format(($MyRow['shipped_models'] != 0) ? ($MyRow['cancelled_models'] / $MyRow['shipped_models'] * 100) : 0, 2) . '%' . '</td>
						<td class="number">' . locale_number_format($MyRow['shipped_quantity'], 0) . '</td>
						<td class="number">' . locale_number_format($MyRow['cancelled_quantity'], 0) . '</td>
						<td class="number">' . locale_number_format(($MyRow['shipped_quantity'] != 0) ? ($MyRow['cancelled_quantity'] / $MyRow['shipped_quantity'] * 100) : 0, 2) . '%' . '</td>
						</tr>';
				$NumTransfersWithErrors++;
			}
			$NumTransfers++;
		}
		echo '</tbody>
			  <tfooter>';
		echo '<tr class="striped_row">
				<td class="number">' . locale_number_format($NumTransfers, 0) . '</td>
				<td class="number">' . locale_number_format(($NumTransfers != 0) ? ($NumTransfersWithErrors / $NumTransfers * 100) : 0, 2) . '%' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . 'TOTAL' . '</td>
				<td class="number">' . locale_number_format($TotalShippedModels, 0) . '</td>
				<td class="number">' . locale_number_format($TotalCancelledModels, 0) . '</td>
				<td class="number">' . locale_number_format(($TotalShippedModels != 0) ? ($TotalCancelledModels / $TotalShippedModels * 100) : 0, 2) . '%' . '</td>
				<td class="number">' . locale_number_format($TotalShippedQty, 0) . '</td>
				<td class="number">' . locale_number_format($TotalCancelledQty, 0) . '</td>
				<td class="number">' . locale_number_format(($TotalShippedQty != 0) ? ($TotalCancelledQty / $TotalShippedQty * 100) : 0, 2) . '%' . '</td>
				</tr>';
		echo '</tfooter>
			  </table>
			  </div>';
	}
}

function FinishedStockDistribution($Kind, $ByReport){

	if ($Kind == "FORSALE"){
		$Operator1 = " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_SHOPS_NOT_FOR_SALE ."";
		$Operator2 = " AND m2.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_SHOPS_NOT_FOR_SALE ."";
	}elseif ($Kind == "DISPLAYS"){
		$Operator1 =  "	AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . " ";
		$Operator2 = "	AND m2.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . " ";
	}elseif ($Kind == "PACKAGING"){
		$Operator1 =  "	AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . " ";
		$Operator2 = "	AND m2.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . " ";
	}else{
		$Operator1 =  "	";
		$Operator2 =  "	";
	}
	if ($ByReport == "LOCATION"){
		$SQL =	"SELECT locstock.loccode,
					locations.locationname,
					SUM(locstock.reorderlevel) AS optimalstock,
					SUM(locstock.quantity) AS realstock,
					(SELECT COUNT(l2.reorderlevel)
						FROM locstock AS l2,
							stockmaster as m2
						WHERE l2.loccode = locations.loccode
							AND m2.stockid = l2.stockid " .
							$Operator2 ."
							AND l2.reorderlevel != 0) AS optimalmodels,
					(SELECT COUNT(l2.quantity)
						FROM locstock AS l2,
							stockmaster as m2
						WHERE l2.loccode = locations.loccode
							AND m2.stockid = l2.stockid " .
							$Operator2 ."
						AND l2.quantity != 0) AS realmodels
				FROM locstock, locations, stockmaster, stockcategory
				WHERE locstock.loccode = locations.loccode
					AND stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.stocktype = 'F'" .
				$Operator1 . "
				GROUP BY locstock.loccode
				ORDER BY locations.locationname";
	}elseif ($ByReport == "STOCKCATEGORY"){
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
							$Operator2 ."
						AND l2.quantity != 0) AS realmodels
				FROM locstock, locations, stockmaster, stockcategory
				WHERE locstock.loccode = locations.loccode
					AND stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.stocktype = 'F'" .
				$Operator1 . "
				GROUP BY stockmaster.categoryid
				ORDER BY stockcategory.categorydescription";
	}else{
		return false;
	}

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($Kind == "FORSALE"){
			$TableTitleText = "Finished Stock FOR SALE Distribution by ";
		}
		if ($Kind == "DISPLAYS"){
			$TableTitleText = "Finished Stock DISPLAYS Distribution by ";
		}
		if ($Kind == "PACKAGING"){
			$TableTitleText = "Finished Stock SHOP PACKAGING Distribution by ";
		}
		if ($ByReport == "LOCATION"){
			$TableTitleText = $TableTitleText . "Location";
			$Titleheader = "Location";
		}
		if ($ByReport == "STOCKCATEGORY"){
			$TableTitleText = $TableTitleText . "Stock Category";
			$Titleheader = "Stock Category";
		}

		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . $Titleheader . '</th>
								<th class="SortedColumn">' . _('QOH Pcs') . '</th>
								<th class="SortedColumn">' . _('RL Pcs') . '</th>
								<th class="SortedColumn">' . _('% Pcs') . '</th>
								<th class="SortedColumn">' . _('QOH Models') . '</th>
								<th class="SortedColumn">' . _('RL Models') . '</th>
								<th class="SortedColumn">' . _('% Models') . '</th>
								<th class="SortedColumn">' . _('QOH Pcs/Model') . '</th>
								<th class="SortedColumn">' . _('RL Pcs/Model') . '</th>
							</tr>
						</thead>
						<tbody>';
		echo $TableHeader;

		$i = 1;
		$Totalpcs = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			// Fix division by zero errors
			$PercentStock = "";
			if ($MyRow['optimalstock'] != 0) {
				$PercentStock = locale_number_format(($MyRow['realstock']/$MyRow['optimalstock']) * 100, 0) . "%";
			}
			
			$PercentModels = "";
			if ($MyRow['optimalmodels'] != 0) {
				$PercentModels = locale_number_format(($MyRow['realmodels']/$MyRow['optimalmodels']) * 100, 0). "%";
			}
			
			$RealPcsModel = "";
			if ($MyRow['realmodels'] != 0) {
				$RealPcsModel = locale_number_format(($MyRow['realstock']/$MyRow['realmodels']), 1);
			}
			
			$OptimalPcsModel = "";
			if ($MyRow['optimalmodels'] != 0) {
				$OptimalPcsModel = locale_number_format(($MyRow['optimalstock']/$MyRow['optimalmodels']), 1);
			}
			
			if ($ByReport == "LOCATION"){
				echo '<tr class="striped_row">
							<td class="number">' . $i . '</td>
							<td>' . $MyRow['locationname'] . '</td>
							<td class="number">' . locale_number_format($MyRow['realstock'],0) . '</td>
							<td class="number">' . locale_number_format($MyRow['optimalstock'],0) . '</td>
							<td class="number">' . $PercentStock . '</td>
							<td class="number">' . locale_number_format($MyRow['realmodels'],0) . '</td>
							<td class="number">' . locale_number_format($MyRow['optimalmodels'],0) . '</td>
							<td class="number">' . $PercentModels . '</td>
							<td class="number">' . $RealPcsModel . '</td>
							<td class="number">' . $OptimalPcsModel . '</td>
						</tr>';
			}
			if ($ByReport == "STOCKCATEGORY"){
				echo '<tr class="striped_row">
							<td class="number">' . $i . '</td>
							<td>' . $MyRow['categorydescription'] . '</td>
							<td class="number">' . locale_number_format($MyRow['realstock'],0) . '</td>
							<td class="number">' . '' . '</td>
							<td class="number">' . '' . '</td>
							<td class="number">' . locale_number_format($MyRow['realmodels'],0) . '</td>
							<td class="number">' . '' . '</td>
							<td class="number">' . '' . '</td>
							<td class="number">' . $RealPcsModel . '</td>
							<td class="number">' . $OptimalPcsModel . '</td>
						</tr>';
			}
			$i++;
			$Totalpcs = $Totalpcs + $MyRow['realstock'];
		}
		echo'</tbody>
			<tfooter>';
		if ($ByReport == "STOCKCATEGORY"){
			$SQL =	"SELECT COUNT(DISTINCT(l2.stockid)) AS realmodels
						FROM locstock AS l2,
							stockmaster as m2,
							stockcategory
						WHERE m2.stockid = l2.stockid" .
							$Operator2 ."
						AND stockcategory.categoryid = m2.categoryid
						AND stockcategory.stocktype = 'F'
						AND l2.quantity != 0";
			$Result1 = DB_query($SQL);
			if (DB_num_rows($Result1) != 0){
				while ($MyRow1 = DB_fetch_array($Result1)) {
					$TotalModels = locale_number_format($MyRow1['realmodels'],0);
					$PercentModels =locale_number_format(($Totalpcs/$MyRow1['realmodels']),1);
				}
			}
		}else{
			$TotalModels = "";
			$PercentModels = "";
		}
		echo '<tr class="striped_row">
					<td class="number">' . "" . '</td>
					<td>' . "Total" . '</td>
					<td class="number">' . locale_number_format($Totalpcs,0) . '</td>
					<td class="number">' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td class="number">' . $TotalModels . '</td>
					<td class="number">' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td class="number">' . $PercentModels . '</td>
					<td class="number">' . "" . '</td>
				</tr>';

		echo '</tfooter>
			  </table>
			  </div>';
	}

	if ($Kind == "DISPLAYS"){
		InsertKPI("Stock", "Stock of Displays (PCS)", $Totalpcs);
	}
}

function FinishedStockDistributionByShopAndCategory(){

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
						AND l2.reorderlevel != 0) AS modelsDISC20KL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid
						AND m2.categoryid = 'DISC2B'
						AND l2.reorderlevel != 0) AS modelsDISC20BL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid
						AND m2.categoryid = 'DISC2G'
						AND l2.reorderlevel != 0) AS modelsDISC20GE,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid
						AND m2.categoryid = 'DISC5A'
						AND l2.reorderlevel != 0) AS modelsDISC50KL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid
						AND m2.categoryid = 'DISC5B'
						AND l2.reorderlevel != 0) AS modelsDISC50BL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid
						AND m2.categoryid = 'DISC5G'
						AND l2.reorderlevel != 0) AS modelsDISC50GE,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid
						AND m2.categoryid = 'DISC8A'
						AND l2.reorderlevel != 0) AS modelsDISC80KL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid
						AND m2.categoryid = 'DISC8B'
						AND l2.reorderlevel != 0) AS modelsDISC80BL,
				(SELECT COUNT(l2.reorderlevel)
					FROM locstock AS l2,
						stockmaster as m2
					WHERE l2.loccode = locations.loccode
						AND m2.stockid = l2.stockid
						AND m2.categoryid = 'DISC8G'
						AND l2.reorderlevel != 0) AS modelsDISC80GE
			FROM locations
			WHERE locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
			ORDER BY locations.locationname";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "Models FOR SALE Distribution by Location and Category";
		ShowTableTitle($TableTitleText);

		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="2"></th>
						<th colspan="6">' . 'KL Models'. '</th>
						<th colspan="6">' . 'Blink Models'. '</th>
						<th colspan="6">' . 'General Models'. '</th>
						<th></th>
					</tr>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . "Location" . '</th>
						<th>' . _('Test') . '</th>
						<th>' . _('Stable') . '</th>
						<th>' . _('NO PO') . '</th>
						<th>' . _('D 20%') . '</th>
						<th>' . _('D 50%') . '</th>
						<th>' . _('D 80%') . '</th>
						<th>' . _('Test') . '</th>
						<th>' . _('Stable') . '</th>
						<th>' . _('NO PO') . '</th>
						<th>' . _('D 20%') . '</th>
						<th>' . _('D 50%') . '</th>
						<th>' . _('D 80%') . '</th>
						<th>' . _('Test') . '</th>
						<th>' . _('Stable') . '</th>
						<th>' . _('NO PO') . '</th>
						<th>' . _('D 20%') . '</th>
						<th>' . _('D 50%') . '</th>
						<th>' . _('D 80%') . '</th>
						<th>' . _('Total') . '</th>
					</tr>
				</thead>
				<tbody>';

		$i = 1;
		$Totalpcs = 0;

		while ($MyRow = DB_fetch_array($Result)) {
			$TotalModelsLocation = 	$MyRow['modelsTESTKL'] +
									$MyRow['modelsSTABLEKAPALLAUT'] +
									$MyRow['modelsNOPOKL'] +
									$MyRow['modelsTESTBL'] +
									$MyRow['modelsSTABLEBLINK'] +
									$MyRow['modelsNOPOBL'] +
									$MyRow['modelsTESTGE'] +
									$MyRow['modelsSTABLEGENERAL'] +
									$MyRow['modelsNOPOGE'] +
									$MyRow['modelsDISC20KL'] +
									$MyRow['modelsDISC20BL'] +
									$MyRow['modelsDISC20GE'] +
									$MyRow['modelsDISC50KL'] +
									$MyRow['modelsDISC50BL'] +
									$MyRow['modelsDISC50GE'] +
									$MyRow['modelsDISC80KL'] +
									$MyRow['modelsDISC80BL'] +
									$MyRow['modelsDISC80GE'] ;

			echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $MyRow['locationname'] . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsTESTKL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsSTABLEKAPALLAUT'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsNOPOKL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC20KL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC50KL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC80KL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsTESTBL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsSTABLEBLINK'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsNOPOBL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC20BL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC50BL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC80BL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsTESTGE'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsSTABLEGENERAL'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsNOPOGE'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC20GE'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC50GE'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['modelsDISC80GE'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalModelsLocation,0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			  </table>
			  </div>';
	}
}

function GetTotalQtyItemsForSale(){
	$SQL = "SELECT SUM(locstock.quantity) AS realstock
			FROM locstock, stockmaster, stockcategory
			WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid NOT IN ('SHDISP', 'SHCONS', 'SHPACK', 'SHOTHE')";
	$ErrMsg = _('Error in function GetTotalQtyItemsForSale()');
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function GetTotalValueItemsForSale($period){
	$SQL = "SELECT SUM(amount) as saldo
			FROM gltotals
			WHERE account IN ('111515000AD',
							'111516000AD',
							'111517000AD',
							'111518000AD',
							'111518900AD',
							'111519000AD',
							'111519100AD')
				AND period = ". $period . "";
	$ErrMsg = _('Error in function GetTotalValueItemsForSale()');
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
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

function GoodsToBeProduced($CategoryComponent, $ParentCategory, $RootPath){
	/* EXPLAIN SQL 2014-05-30 */
	/* Check if there is any component at kantor ready to be transformed into sellable goods */
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
				(s.actualcost) AS stdcost,(SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = s.stockid
					AND locstock.loccode NOT IN " . LIST_SERVICE_LOCATIONS . ") AS availablestock
			FROM stockmaster AS s
			WHERE s.discontinued = 0
			AND s.categoryid = '".$CategoryComponent."'
			AND ((SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = s.stockid
					AND locstock.loccode NOT IN " . LIST_SERVICE_LOCATIONS . ") > 0)
			AND EXISTS(
				SELECT bom.component
				FROM bom,stockmaster AS stP, stockmaster AS stC
				WHERE bom.parent = stP.stockid
					AND bom.component = stC.stockid
					AND s.stockid = bom.component " .
					$WhereParentCategory . "
					AND stP.discontinued = 0)" .
			$OnlyDiscountExists;

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ParentCategory == "ONLYDISCOUNT"){
			$TableTitleText = _('Components ready to WO in kantor used ONLY for Discount items');
			$BusinessConcept = "Components ONLY for Discount items (IDR)";
		}elseif ($ParentCategory == "DISCOUNT"){
			$TableTitleText = _('Components ready to WO in kantor used for Discount items');
			$BusinessConcept = "Components for Discount items (IDR)";
		}else{
			$TableTitleText = _('Components ready to WO in kantor for any items');
			$BusinessConcept = "Components for any items (IDR)";
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('QOH') . '</th>
						<th class="SortedColumn">' . _('UOM') . '</th>
						<th class="SortedColumn">' . _('Stock value') . '</th>
					</tr>
				</thead>
				<tbody>';

		$i = 1;
		$TotalCost = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$TotalCost = $TotalCost + ($MyRow['availablestock'] * $MyRow['stdcost']);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['availablestock'], 0) . '</td>
					<td>' . $MyRow['units'] . '</td>
					<td class="number">' . locale_number_format($MyRow['availablestock'] * $MyRow['stdcost'], 0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			  <tfooter>';
		echo '<tr class="striped_row">
				<td class="number">' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . 'Total Cost' . '</td>
				<td class="number">' . '' . '</td>
				<td>' . '' . '</td>
				<td class="number">' . locale_number_format($TotalCost, 0) . '</td>
				</tr>';
		echo '</tfooter>
			  </table>
			  </div>';
		InsertKPI("Components", $BusinessConcept, $TotalCost);
	}
}

function InsuficientStockForShopPackaging($Category, $DaysUsage, $DaysMinimumStock, $ShowAll, $ExtendedVersion, $RootPath){
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

	$FromForecastDateLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -365));
	$ToForecastDateLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -365+$DaysMinimumStock));

	$Year = date('Y', strtotime("-1 days"));

	$TrendThisYearKL = round(GetLastKPIValue("Stock","Trend retail 75 days (%) Kapal-Laut") / 100,3);
	$TrendThisYearBL = round(GetLastKPIValue("Stock","Trend retail 75 days (%) Blink") / 100,3);
	$TrendThisYearOU = round(GetLastKPIValue("Sales","Trend retail%") / 100,3);

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.eoq,
					stockmaster.pansize,
					(SELECT SUM(quantity)
						FROM locstock, locations
						WHERE locstock.stockid = stockmaster.stockid
							AND locstock.loccode = locations.loccode
							AND locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . ") AS qohshops,
					(SELECT SUM(quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
							AND locstock.loccode IN " . LIST_PACAKING_LOCATIONS . ") AS qohgudang,
					(SELECT SUM(GREATEST(reorderlevel," . TRANSFER_ROUNDING_STEP01 . "))
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
							AND reorderlevel > 0) AS sumrl,";
	if ($Category == 'SHPACK'){
			$SQL = $SQL . "	(SELECT SUM(qty)
								FROM packagingused
								WHERE packagingused.stockid = stockmaster.stockid
									AND packagingused.date >= '". $FromDate ."'
									AND packagingused.date <= '". $ToDate ."') AS qused,
							(SELECT SUM(qty)
								FROM packagingused
								WHERE packagingused.stockid = stockmaster.stockid
									AND packagingused.date >= '". $FromForecastDateLastYear ."'
									AND packagingused.date <= '". $ToForecastDateLastYear ."') AS qusedlastyear,";
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
	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	if (DB_num_rows($Result) != 0){
		$i = 1;
		$UsageXDays = 0;
		$ForecastXDays = 0;
		$ForecastXDaysLastYear = 0;
		$QOHTotal = 0;
		$MissingTotal = 0;
		$QtyNeededTotal = 0;
		$PendingQOO = 0;
		$OptimumOrder = 0;
		$TotalMinimumGudang = 0;
		$TotalQOHOptimum = 0;
		$TotalQOHGudang = 0;
		$TotalQOHShops = 0;
		$NumberOfOpenShopsKL = NumberOfShops("SHOPKL");
		$NumberOfOpenShopsBL = NumberOfShops("SHOPBL");
		$NumberOfOpenShopsOU = NumberOfShops("SHOPOU");

		while ($MyRow = DB_fetch_array($Result)) {
			// Fix division by zero errors
			$DailyUse = ($DaysUsage > 0) ? ($MyRow['qused'] / $DaysUsage) : 0;
			$UsedLastXDays = ceil($DailyUse * $DaysUsage);
			$ForecastUsedThisYear = ceil($DailyUse * ($DaysMinimumStock));
			if (ItemInList($MyRow['stockid'], LIST_ITEMS_KAPAL_LAUT_PACKAGING)){
				$ForecastUsedLastYear = ceil($MyRow['qusedlastyear'] * (1 + $TrendThisYearKL));
			}elseif (ItemInList($MyRow['stockid'], LIST_ITEMS_BLINK_PACKAGING)){
				$ForecastUsedLastYear = ceil($MyRow['qusedlastyear'] * (1 + $TrendThisYearBL));
			}else{
				$ForecastUsedLastYear = ceil($MyRow['qusedlastyear'] * (1 + $TrendThisYearOU));
			}
			$ForecastUsageNextDays = max( $ForecastUsedThisYear, $ForecastUsedLastYear);
			// Fix division by zero errors
			$ForecastUsageDaily = ($DaysMinimumStock > 0) ? ($ForecastUsageNextDays / $DaysMinimumStock) : 0;

			// to prevent shortage on slow moving items in ANY gudang, and be still able to serve the item to the shops
			// we need to keep a minimum stock always in gudang
			if (isPackagingPaperInsideBox($MyRow['stockid'])){
				if (ItemInList($MyRow['stockid'], LIST_ITEMS_KAPAL_LAUT_PACKAGING)){
					$MinQOHGudang = $NumberOfOpenShopsKL * $MyRow['eoq'] * FACTOR_GUDANG_PACKAGING_PAPER_INSIDE_BOX;
				}elseif (ItemInList($MyRow['stockid'], LIST_ITEMS_BLINK_PACKAGING)){
					$MinQOHGudang = $NumberOfOpenShopsBL * $MyRow['eoq'] * FACTOR_GUDANG_PACKAGING_PAPER_INSIDE_BOX;
				}else{
					$MinQOHGudang = $NumberOfOpenShopsOU * $MyRow['eoq'] * FACTOR_GUDANG_PACKAGING_PAPER_INSIDE_BOX;
				}
			}else{
				$MinQOHGudang = $MyRow['sumrl'] * FACTOR_GUDANG_PACKAGING;
			}

			$OptimumQOH = max($ForecastUsageNextDays, $MinQOHGudang);
			$QOH = max($MyRow['qohgudang']+$MyRow['qohshops'],0);
			$QOHDays = ($ForecastUsageDaily > 0) ? ($QOH / $ForecastUsageDaily) : 0; // QOH expressed in days at daily forecast rate
			$MissingQOH = max($OptimumQOH - $QOH, 0);
			$DaysQOH = ($DailyUse > 0) ? floor($QOH / $DailyUse): 0;
			$DaysQOO = ($DailyUse > 0) ? floor(($QOH + $MyRow['qoo']) / $DailyUse) : 0;
			
			// Fix division by zero error
			$PanSize = ($MyRow['pansize'] > 0) ? $MyRow['pansize'] : 1;

			if ($MinQOHGudang < $MyRow['qohgudang']){
				// we have enough in gudang, don't need to add some to keep in gudang
				$QtyNeeded = max(0, ($ForecastUsageNextDays - $QOH - $MyRow['qoo']));
			}else{
				// we don't have enough in gudang, we need to get some to keep in gudang
				if($MyRow['qoo'] < ($MinQOHGudang - $MyRow['qohgudang'])){
					// if we don't have enough QOO to cover the deficit in gudang
					$QtyNeeded = max(0, ($ForecastUsageNextDays - $QOH - $MyRow['qoo']),($MinQOHGudang-$MyRow['qohgudang']-$MyRow['qoo']));
				}else{
					$QtyNeeded = max(0, ($ForecastUsageNextDays - $QOH - $MyRow['qoo']));
				}
			}
			
			$QtyToOrder = OptimumOrderQuantity($QtyNeeded, $MyRow['eoq'], $PanSize);

			// phasing out these codes, don't want to buy anymore
			if (($MyRow['stockid'] == "PKPB02-L")
				OR ($MyRow['stockid'] == "PKPB02-M")
				OR ($MyRow['stockid'] == "PKPB02-S")
				OR ($MyRow['stockid'] == "PKSB03")){
				$QtyToOrder = 0;
			}

			if (($QtyNeeded > 0) OR ($ShowAll)){
				if ($ShowHeader){
					if ($Category == 'SHPACK'){
						if ($ShowAll){
							$TableTitleText = 'Shop packaging order status';
							ShowTableTitle($TableTitleText);
							$TableSubTitleText = 'Forecast '.$DaysMinimumStock.' 	days ' . $Year . ' based on usage from '. ConvertSQLDate($FromDate) . ' to ' . ConvertSQLDate($ToDate) . 
												'<br>' . 
												'Forecast '.$DaysMinimumStock.' 	days ' . ($Year - 1) . ' based on usage from '. ConvertSQLDate($FromForecastDateLastYear) . ' to ' . ConvertSQLDate($ToForecastDateLastYear) . 
												'<br>' .
												'Trend retail against last year for Kapal-Laut = '. ($TrendThisYearKL*100).'%, Blink = '. ($TrendThisYearBL*100).'%, Outlet = '. ($TrendThisYearOU*100).'%';	
												
							ShowTableSubTitle($TableSubTitleText);
						}else{
							$TableTitleText = 'Shop packaging with insufficient stock for the next ' . ($DaysMinimumStock) . ' days.';
							ShowTableTitle($TableTitleText);
						}
					}
					if ($Category == 'ZAPON'){
						if ($ShowAll){
							$TableTitleText = 'Online Promotion items order status';
						}else{
							$TableTitleText = 'Online Promotion items with insufficient stock for the next ' . $DaysMinimumStock . ' days.';
						}
						ShowTableTitle($TableTitleText);
					}
					echo '<div>';
					echo '<table class="selection">';
					if ($ExtendedVersion){
						$TableHeader = '<thead>
										<tr>
										<th class="SortedColumn">' . _('#') . '</th>
										<th class="SortedColumn">' . _('Code') . '</th>
										<th class="SortedColumn">' . _('Description') . '</th>
										<th class="SortedColumn">' . _('Current Daily Usage') . '</th>
										<th class="SortedColumn">' . _('Forecast ') . $DaysMinimumStock . ' days ' . $Year . '</th>
										<th class="SortedColumn">' . _('Forecast ') . $DaysMinimumStock . ' days ' . ($Year - 1) . '</th>
										<th class="SortedColumn">' . _('Min QTY Gudang') . '</th>
										<th class="SortedColumn">' . _('QTY Optimum') . '</th>
										<th class="SortedColumn">' . _('QOH Gudang') . '</th>
										<th class="SortedColumn">' . _('QOH Shops') . '</th>
										<th class="SortedColumn">' . _('QOH Total') . '</th>
										<th class="SortedColumn">' . _('QOH Days') . '</th>
										<th class="SortedColumn">' . _('QTY Shortage') . '</th>
										<th class="SortedColumn">' . _('% Shortage') . '</th>
										<th class="SortedColumn">' . _('QOO Running') . '</th>
										<th class="SortedColumn">' . _('Next Order') . '</th>
									</tr>
									</thead>
									<tbody>';
					}else{
						$TableHeader = '<thead>
										<tr>
										<th class="SortedColumn">' . _('#') . '</th>
										<th class="SortedColumn">' . _('Code') . '</th>
										<th class="SortedColumn">' . _('Description') . '</th>
										<th class="SortedColumn">' . _('Optimum QTY') . '</th>
										<th class="SortedColumn">' . _('QOH Gudang') . '</th>
										<th class="SortedColumn">' . _('QOH Shops') . '</th>
										<th class="SortedColumn">' . _('QOH Total') . '</th>
										<th class="SortedColumn">' . _('QOH Days') . '</th>
										<th class="SortedColumn">' . _('Shortage QTY') . '</th>
										<th class="SortedColumn">' . _('% Shortage') . '</th>
										<th class="SortedColumn">' . _('Running QOO') . '</th>
										<th class="SortedColumn">' . _('Next Order') . '</th>
									</tr>
									</thead>
									<tbody>';
					}
					echo $TableHeader;
					$ShowHeader = FALSE;
				}

				$UsageXDays += $UsedLastXDays;
				$ForecastXDays += $ForecastUsedThisYear;
				$ForecastXDaysLastYear += $ForecastUsedLastYear;
				$QOHTotal += $QOH;
				$MissingTotal += $MissingQOH;
				$QtyNeededTotal += $QtyNeeded;
				$PendingQOO += $MyRow['qoo'];
				$OptimumOrder += $QtyToOrder;
				$TotalMinimumGudang += $MinQOHGudang;
				$TotalQOHOptimum += $OptimumQOH;
				$TotalQOHGudang += $MyRow['qohgudang'];
				$TotalQOHShops += $MyRow['qohshops'];
				$PercentShortage = 0;
				if ($OptimumQOH != 0) {
					$PercentShortage = $MissingQOH / $OptimumQOH * 100;
				}
	
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				if ($ExtendedVersion){
					echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($DailyUse,0) . '</td>
						<td class="number">' . locale_number_format($ForecastUsedThisYear,0) . '</td>
						<td class="number">' . locale_number_format($ForecastUsedLastYear,0) . '</td>
						<td class="number">' . locale_number_format($MinQOHGudang,0) . '</td>
						<td class="number">' . locale_number_format($OptimumQOH,0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qohgudang'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qohshops'],0) . '</td>
						<td class="number">' . locale_number_format($QOH,0) . '</td>
						<td class="number">' . locale_number_format($QOHDays,0) . '</td>
						<td class="number">' . locale_number_format($MissingQOH,0) . '</td>
						<td class="number">' . locale_number_format($PercentShortage,0) . '%' . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['qoo'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($QtyToOrder,0) . '</td>
						</tr>';
				}else{
					echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($OptimumQOH,0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qohgudang'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qohshops'],0) . '</td>
						<td class="number">' . locale_number_format($QOH,0) . '</td>
						<td class="number">' . locale_number_format($QOHDays,0) . '</td>
						<td class="number">' . locale_number_format($MissingQOH,0) . '</td>
						<td class="number">' . locale_number_format($PercentShortage,0) . '%' . '</td>
						<td class="number">' . locale_number_format_zero_blank($MyRow['qoo'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($QtyToOrder,0) . '</td>
						</tr>';
				}
			}
			$i++;
		}
		if (!$ShowHeader){
			$TotalDailyUse = ($DaysUsage > 0) ? ($UsageXDays / $DaysUsage) : 0;
			
			// Fix division by zero errors
			$TotalDaysQOH = 0;
			$TotalDaysQOO = 0;
			if ($TotalDailyUse != 0) {
				$TotalDaysQOH = floor($QOHTotal / $TotalDailyUse);
				$TotalDaysQOO = floor(($QOHTotal + $PendingQOO) / $TotalDailyUse);
			}
			
			$PercentTotalShortage = 0;
			if ($TotalQOHOptimum != 0) {
				$PercentTotalShortage = $MissingTotal / $TotalQOHOptimum * 100;
			}
			
			echo'</tbody>
				<tfooter>';
			if ($ExtendedVersion){
				echo '<tr class="striped_row">
						<td class="number">' . "" . '</td>
					<td>' . "TOTAL" . '</td>
					<td>' . "" . '</td>
					<td class="number">' . locale_number_format($TotalDailyUse,0) . '</td>
					<td class="number">' . locale_number_format($ForecastXDays,0) . '</td>
					<td class="number">' . locale_number_format($ForecastXDaysLastYear,0) . '</td>
					<td class="number">' . locale_number_format($TotalMinimumGudang,0) . '</td>
					<td class="number">' . locale_number_format($TotalQOHOptimum,0) . '</td>
					<td class="number">' . locale_number_format($TotalQOHGudang,0) . '</td>
					<td class="number">' . locale_number_format($TotalQOHShops,0) . '</td>
					<td class="number">' . locale_number_format($QOHTotal,0) . '</td>
					<td class="number">' . '' . '</td>
					<td class="number">' . locale_number_format($MissingTotal,0) . '</td>
					<td class="number">' . locale_number_format($PercentTotalShortage,0) . '%' . '</td>
					<td class="number">' . locale_number_format_zero_blank($PendingQOO,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($OptimumOrder,0) . '</td>
					</tr>';
			}else{
				echo '<tr class="striped_row">
						<td class="number">' . "" . '</td>
					<td>' . "TOTAL" . '</td>
					<td>' . "" . '</td>
					<td class="number">' . locale_number_format($TotalQOHOptimum,0) . '</td>
					<td class="number">' . locale_number_format($TotalQOHGudang,0) . '</td>
					<td class="number">' . locale_number_format($TotalQOHShops,0) . '</td>
					<td class="number">' . locale_number_format($QOHTotal,0) . '</td>
					<td class="number">' . '' . '</td>
					<td class="number">' . locale_number_format($MissingTotal,0) . '</td>
					<td class="number">' . locale_number_format($PercentTotalShortage,0) . '%' . '</td>
					<td class="number">' . locale_number_format_zero_blank($PendingQOO,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($OptimumOrder,0) . '</td>
					</tr>';
			}
			echo '</tfooter>
				</table>
				</div>';

			InsertKPI("Packaging", "Packaging current daily use (PCS)", $TotalDailyUse);
			InsertKPI("Packaging", "Packaging used last " . $DaysUsage .  " days (PCS)", $UsageXDays);
			InsertKPI("Packaging", "Packaging forecast next X days (PCS)", $ForecastXDays);
			InsertKPI("Packaging", "Packaging QOH total (PCS)", $QOHTotal);
			InsertKPI("Packaging", "Packaging QOH total (DAYS)", $TotalDaysQOH);
			InsertKPI("Packaging", "Packaging QOO not received (PCS)", $PendingQOO);
			InsertKPI("Packaging", "Packaging QOH + QOO total (DAYS)", $TotalDaysQOO);
			InsertKPI("Packaging", "Packaging Optimum Order (PCS)", $OptimumOrder);
			InsertKPI("Packaging", "Packaging Optimum QOH (PCS)", $TotalQOHOptimum);
			InsertKPI("Packaging", "Packaging Shortage (PCS)", $MissingTotal);
			InsertKPI("Packaging", "Packaging Shortage (%)", round($PercentTotalShortage,0));
		}
	}
}

function ItemsWithoutRetailPrice($StockCat, $factorRetail, $RootPath){
	/* Check if there is any item without retail price */
	$Issues = 0;
	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				(stockmaster.actualcost) AS stdcost
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockcategory.stocktype ='F'
				AND stockmaster.categoryid = '". $StockCat ."'
				AND NOT EXISTS (SELECT *
								FROM prices
								WHERE stockmaster.stockid = prices.stockid
									AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
									AND prices.currabrev = '". CURRENCY_CODE ."'
									AND prices.startdate <= CURRENT_DATE
									AND prices.enddate >= CURRENT_DATE)";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$CategoryName = GetCategoryNameFromCode($StockCat);
		$TableTitleText = $CategoryName . _(' Items without active retail price');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Code') . '</th>
							<th class="SortedColumn">' . _('Description') . '</th>
							<th class="SortedColumn">' . _('Std Cost') . '</th>
							<th class="SortedColumn">' . _('Factor') . '</th>
							<th class="SortedColumn">' . _('Recommended Retail') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		while ($MyRow = DB_fetch_array($Result)) {
			$Issues++;
			$NewPrice = round_price($MyRow['stdcost'] * $factorRetail, "UP");
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$PriceLink = '<a href="' . $RootPath . '/Prices.php?Item=' . $MyRow['stockid'] . '">' . locale_number_format($MyRow['stdcost'],0) . '</a>';
			$NewPriceLink = '<a href="' . $RootPath . '/KLChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $NewPrice .  '&Action=New">' . locale_number_format($NewPrice,0) . '</a>';
			$Factor = ($MyRow['stdcost'] != 0) ? ($NewPrice/$MyRow['stdcost']) : 0;
			echo '<tr class="striped_row">
					<td class="number">' . $Issues . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . $PriceLink . '</td>
					<td class="number">' . locale_number_format_zero_blank($Factor, 2) . '</td>
					<td class="number">' . $NewPriceLink . '</td>
					</tr>';
		}
		echo '</tbody>
				</table>
				</div>';
	}
	return $Issues;
}

function LocationInformationReview($RootPath){
	$SQL="SELECT loccode,
				locationname,
				zone,
				typeloc,
				klyearlyrent,
				partnercode,
				priority,
				stockavailableforonline,
				alltestitems,
				allstableitems,
				allnopoitems,
				alldisc20items,
				alldisc50items,
				alldisc80items,
				rlfactorforpackaging,
				rldaysforpackaging,
				smartdispatchmaxmodels
		FROM locations
		WHERE locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . "
		ORDER BY locationname ASC";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Shop Information Review');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Location') . '</th>
							<th class="SortedColumn">' . _('Zone') . '</th>
							<th class="SortedColumn">' . _('Type') . '</th>
							<th class="SortedColumn">' . _('Partner') . '</th>
							<th class="SortedColumn">' . _('Rent (jt)') . '</th>
							<th class="SortedColumn">' . _('Priority') . '</th>
							<th class="SortedColumn">' . _('Max Daily Tr') . '</th>
							<th class="SortedColumn">' . _('Stock Online?') . '</th>
							<th class="SortedColumn">' . _('All Test?') . '</th>
							<th class="SortedColumn">' . _('All Stable?') . '</th>
							<th class="SortedColumn">' . _('All NOPO?') . '</th>
							<th class="SortedColumn">' . _('Sell 20%D?') . '</th>
							<th class="SortedColumn">' . _('Sell 50%D?') . '</th>
							<th class="SortedColumn">' . _('Sell 80%D?') . '</th>
							<th class="SortedColumn">' . _('Pack Factor') . '</th>
							<th class="SortedColumn">' . _('Pack Days') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/Locations.php?SelectedLocation=' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</a>';
			if ($MyRow['stockavailableforonline'] ==  1){
				$StockOnline = "Yes";
			}else{
				$StockOnline = "No";
			}
			if ($MyRow['alltestitems'] ==  1){
				$StockTest = "Yes";
			}else{
				$StockTest = "No";
			}
			if ($MyRow['allstableitems'] ==  1){
				$StockStable = "Yes";
			}else{
				$StockStable = "No";
			}
			if ($MyRow['allnopoitems'] ==  1){
				$StockNoPo= "Yes";
			}else{
				$StockNoPo = "No";
			}
			if ($MyRow['alldisc20items'] ==  1){
				$Stock20D= "Yes";
			}else{
				$Stock20D = "No";
			}
			if ($MyRow['alldisc50items'] ==  1){
				$Stock50D= "Yes";
			}else{
				$Stock50D = "No";
			}
			if ($MyRow['alldisc80items'] ==  1){
				$Stock80D= "Yes";
			}else{
				$Stock80D = "No";
			}
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['zone'] . '</td>
					<td>' . $MyRow['typeloc'] . '</td>
					<td>' . $MyRow['partnercode'] . '</td>
					<td class="number">' . locale_number_format($MyRow['klyearlyrent']/JUTA,0) . '</td>
					<td class="number">' . $MyRow['priority'] . '</td>
					<td class="number">' . $MyRow['smartdispatchmaxmodels'] . '</td>
					<td>' . $StockOnline . '</td>
					<td>' . $StockTest . '</td>
					<td>' . $StockStable . '</td>
					<td>' . $StockNoPo . '</td>
					<td>' . $Stock20D . '</td>
					<td>' . $Stock50D . '</td>
					<td>' . $Stock80D . '</td>
					<td class="number">' . $MyRow['rlfactorforpackaging'] . '</td>
					<td class="number">' . $MyRow['rldaysforpackaging'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function CheckPackagingToBeRefilled($ShowAll, $ShowLinkEmail, $RootPath){
	$SQL = "SELECT  locations.loccode
			FROM locations
			WHERE locations.packagingfrom != ''
				AND locations.loccode NOT IN " . LIST_ONLINE_SHOPS . "
			ORDER BY locations.klemaillastpackacgingtransfer,
				locations.packagingfrom,
				locations.locationname";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			PackagingToBeRefilledFromGudang($MyRow['loccode'], $ShowAll, $ShowLinkEmail, $RootPath);
		}
	}
}

function PackagingToBeRefilledFromGudang($LocCode, $ShowAll, $ShowLinkEmail, $RootPath){

	$TableResult = array();

	// get info from locations table
	$SQL = "SELECT  locations.locationname,
					locations.rlfactorforpackaging AS rlfactor,
					locations.packagingfrom AS parentgudang,
					locations.klemaillastpackacgingtransfer,
					locations.typeloc,
					(SELECT l2.locationname
						FROM locations l2
						WHERE l2.loccode = locations.packagingfrom) AS parentgudangname
			FROM locations
			WHERE locations.loccode = '" . $LocCode . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$RLFactor = $MyRow['rlfactor'];
	$LocationName = $MyRow['locationname'];
	$LocationType = $MyRow['typeloc'];
	$ParentGudang = $MyRow['parentgudang'];
	$ParentGudangName = $MyRow['parentgudangname'];
	$LastPackagingTransferDate = ConvertSQLDate($MyRow['klemaillastpackacgingtransfer']);

	// check what packaging items are missing on that location
	$SQL = "SELECT  stockmaster.stockid,
					stockmaster.description,
					locstock.quantity AS qoh,
					(SELECT l2.quantity
						FROM locstock AS l2
						WHERE l2.stockid = stockmaster.stockid
							AND l2.loccode = '". $ParentGudang ."') AS qohparent,
					locstock.reorderlevel AS rl,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locstock.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = stockmaster.stockid) AS intransit
			FROM locstock, stockmaster
			WHERE stockmaster.stockid = locstock.stockid
				AND locstock.reorderlevel != 0
				AND locstock.loccode = '" . $LocCode . "'
				AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				AND stockmaster.discontinued = 0
			ORDER BY stockmaster.stockid";
	$Result = DB_query($SQL);

	$ShowHeader = FALSE;
	$ShowReport = FALSE;
	$NumItems = 0;
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$NumItems++;
			$TableResult[$NumItems]['stockid'] = $MyRow['stockid'];
			$TableResult[$NumItems]['description'] = $MyRow['description'];
			$TableResult[$NumItems]['qohparent'] = $MyRow['qohparent'];
			$TableResult[$NumItems]['qoh'] = $MyRow['qoh'];
			$TableResult[$NumItems]['rl'] = $MyRow['rl'];
			$TableResult[$NumItems]['intransit'] = $MyRow['intransit'];
			$TableResult[$NumItems]['optimum'] = round(($MyRow['rl'] * $RLFactor),0);
			$TableResult[$NumItems]['needed']= max(0,$TableResult[$NumItems]['optimum'] - $MyRow['qoh']);
			$QtyToShip = min(max(0,$TableResult[$NumItems]['needed'] - $MyRow['intransit']),($MyRow['qohparent'] - $MyRow['intransit']));
			if (ItemInList($LocCode, LIST_PACAKING_LOCATIONS)){
				// if it is a transfer from a gudang packaging to another and we don't have much stock,
				// we divide the available gudang QOH between all the packaging gudang
				$QOHAllGudang = $MyRow['qohparent'] + $MyRow['qoh'];
				$FairQOHGudang = $QOHAllGudang / NumberOfItemsInList(LIST_PACAKING_LOCATIONS);
				if ($QtyToShip > $FairQOHGudang){
					// if we should ship more than the "fair share", we cap it so all gudang end up with a QOH close to the fair share
					$QtyToShip = $FairQOHGudang - $MyRow['qoh'];
				}
			}
			$TableResult[$NumItems]['toship'] = min($MyRow['qohparent'],RoundPackagingTransfer($MyRow['stockid'], $QtyToShip));

			// cap the maximum number of boxes to be sent to a shop,
			// to prevent shipments too bulky for courier to safely bring in one motorbike trip
			if (isPackagingBox($TableResult[$NumItems]['stockid'])
				AND ($LocationType == "SHOPKL" OR
					$LocationType == "SHOPBL" OR
					$LocationType == "SHOPOU")
				AND ($TableResult[$NumItems]['toship'] > MAXIMUM_BOXES_PACKAGING_TRANSFER_TO_SHOP)){
				$TableResult[$NumItems]['toship'] = MAXIMUM_BOXES_PACKAGING_TRANSFER_TO_SHOP;
			}

			if ($ShowAll OR (($MyRow['qoh'] < $MyRow['rl']) AND ($TableResult[$NumItems]['toship'] > 0))){
				// at least 1 item needs to be refilled at the location and we can ship it, so we have to show the report
				$TableResult[$NumItems]['show'] = TRUE;
				$ShowHeader = TRUE;
				$ShowReport = TRUE;
			}else{
				$TableResult[$NumItems]['show'] = FALSE;
			}
		}
	}

	if ($ShowReport){
		$i = 1;
		$ItemsToShip = 0;

		while ($i <= $NumItems) {
			// IF we are SHORT of that packaging material in that location...
			// Or we show All the the packaging items in that location
			if($ShowHeader){
				$TableTitleText = 'Packaging needed at ' . $LocationName . ' from ' . $ParentGudangName . '. Last transfer: ' . $LastPackagingTransferDate;
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<thead>
								<tr>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('QOH @ ') . $ParentGudang . '</th>
									<th class="SortedColumn">' . _('QOH @ ') . $LocCode . '</th>
									<th class="SortedColumn">' . _('RL @ ') . $LocCode . '</th>
									<th class="SortedColumn">' . _('Optimum') . '</th>
									<th class="SortedColumn">' . _('Needing') . '</th>
									<th class="SortedColumn">' . _('%') . '</th>
									<th class="SortedColumn">' . _('Transit') . '</th>
									<th class="SortedColumn">' . _('To Ship') . '</th>
									<th class="SortedColumn">' . _('Reason') . '</th>
								</tr>
								</thead>
								<tbody>';
				echo $TableHeader;
				$ShowHeader = FALSE;
				$EmailLink = '<a href="' . $RootPath . '/KLPreparePackagingTransferFromGudang.php?From=' . $ParentGudang
																								. '&To=' . $LocCode;
			}

			if ($TableResult[$i]['toship'] > 0){
				if ($TableResult[$i]['qoh'] == 0){
					$Reason = "QOH = 0";
				}elseif ($TableResult[$i]['rl'] > $TableResult[$i]['qoh']){
					$Reason = "QOH below RL";
				}else{
					$Reason = "Top up";
				}
				echo '<tr class="striped_row">
						<td>' . $TableResult[$i]['stockid'] . '</td>
						<td>' . $TableResult[$i]['description'] . '</td>
						<td class="number">' . locale_number_format_zero_blank($TableResult[$i]['qohparent'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($TableResult[$i]['qoh'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($TableResult[$i]['rl'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($TableResult[$i]['optimum'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($TableResult[$i]['needed'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($TableResult[$i]['needed']/$TableResult[$i]['optimum']*100,0) . "%" . '</td>
						<td class="number">' . locale_number_format_zero_blank($TableResult[$i]['intransit'],0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($TableResult[$i]['toship'],0) . '</td>
						<td>' . $Reason . '</td>
						</tr>';
				if ($TableResult[$i]['toship'] > 0){
					$ItemsToShip++;
					$EmailLink = $EmailLink . '&Item' . $ItemsToShip . '=' . $TableResult[$i]['stockid'] .
											'&Qty' . $ItemsToShip . '=' . $TableResult[$i]['toship'];
				}
			}
			$i++;
		}
		if (!$ShowHeader){
			echo'</tbody>
				<tfooter>';
			$EmailLink = $EmailLink . '">' . 'Send email to team' . '</a>';
			if ($ShowLinkEmail){
				echo '<tr class="striped_row">
						<td>' . "" . '</td>
						<td>' . $EmailLink . '</td>
						</tr>';
			}
			echo '</tfooter>
				</table>
				</div>';
		}
	}
}

function RoundPackagingTransfer($StockID, $n){
	if(isPackagingPaperInsideBox($StockID)){
		$n = ceil($n/TRANSFER_ROUNDING_PAPER_INSIDE_BOX)*TRANSFER_ROUNDING_PAPER_INSIDE_BOX;
	}else{
		if ($n < TRANSFER_ROUNDING_LIMIT01){
			$n = ceil($n/TRANSFER_ROUNDING_STEP01)*TRANSFER_ROUNDING_STEP01;
		}elseif ($n < TRANSFER_ROUNDING_LIMIT02){
			$n = ceil($n/TRANSFER_ROUNDING_STEP02)*TRANSFER_ROUNDING_STEP02;
		}elseif ($n < TRANSFER_ROUNDING_LIMIT03){
			$n = ceil($n/TRANSFER_ROUNDING_STEP03)*TRANSFER_ROUNDING_STEP03;
		}else{
			$n = ceil($n/TRANSFER_ROUNDING_STEP04)*TRANSFER_ROUNDING_STEP04;
		}
	}
	return $n;
}

function PositionTopSalesItem($StockID, $TopItemsDays){

	$TopSalesField = GetTopSalesField($TopItemsDays);
	$SQL="SELECT ". $TopSalesField." AS topsalesposition
		  FROM klsalesperformance
		  WHERE stockid = '" . $StockID . "'";
	$Result = DB_query($SQL);
	$TopSalesPosition = 9999999;
	if (DB_num_rows($Result) != 0){
		if ($MyRow = DB_fetch_array($Result)) {
			$TopSalesPosition = $MyRow['topsalesposition'];
		}
	}
	return $TopSalesPosition;
}

function POStatusControl($TypeOfProduct, $TypeOfCode, $maxdays, $periodnow, $RootPath){

	if ($TypeOfCode == "IN NEGOTIATION WITH SUPPLIER"){
		$DateField1 = "orddate";
		$FieldName1 = "Planned Order Date";
		$DateField2 = "orddate";
		$FieldName2 = "";
		$ShipmentAWB = '';
		$TableTitleText = 'POs in Negotiations with supplier';
		$SQLFilterKLStatus = " AND purchorders.klstatus = '1000' ";
	}else if ($TypeOfCode == "ON PRODUCTION"){
		$DateField1 = "agreeddeliverydate";
		$FieldName1 = "Agreed Delivery";
		$DateField2 = "deliverydate";
		$FieldName2 = "Planned Delivery";
		$ShipmentAWB = '';
		$TableTitleText = 'POs on Production by supplier';
		$SQLFilterKLStatus = " AND purchorders.klstatus = '2000' ";
	}else if ($TypeOfCode == "FINISHED BUT NOT PAID"){
		$DateField1 = "deliverydate";
		$FieldName1 = "Delivery Date";
		$DateField2 = "paymentdate";
		$FieldName2 = "Planned Payment";
		$ShipmentAWB = '';
		$TableTitleText = 'POs finished by supplier but not fully paid';
		$SQLFilterKLStatus = " AND purchorders.klstatus = '3000' ";
	}else if ($TypeOfCode == "STILL NOT FULLY PAID"){
		$DateField1 = "deliverydate";
		$FieldName1 = "Delivery Date";
		$DateField2 = "paymentdate";
		$FieldName2 = "Planned Payment";
		$ShipmentAWB = '';
		$TableTitleText = 'POs still not fully paid';
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
		$TableTitleText = 'Bali POs paid but not delivered in kantor';
		$SQLFilterKLStatus = " AND (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'B1') ";
	}else if ($TypeOfCode == "BALI RECEIVED IN KANTOR BUT NOT PAID"){
		$DateField1 = "arrivaldate";
		$FieldName1 = "Arrival Date";
		$DateField2 = "paymentdate";
		$FieldName2 = "Planned Payment";
		$ShipmentAWB = '';
		$TableTitleText = 'Bali POs delivered in kantor but not paid yet';
		$SQLFilterKLStatus = " AND (purchorders.klstatus = '6000' AND suppliers.paymentterms = 'B2') ";
	}else if ($TypeOfCode == "PAID NOT SHIPPED BY SUPPLIER"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TableTitleText = 'Overseas POs paid but not shipped directly by supplier';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O1')
									OR (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O3')) ";
	}else if ($TypeOfCode == "PAID NOT RECEIVED IN AYE CARGO"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TableTitleText = 'Overseas POs paid to supplier but not received by Aye Cargo';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O2')
									OR (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O5')) ";
	}else if ($TypeOfCode == "PAID NOT RECEIVED IN WANGFOONG CARGO"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TableTitleText = 'Overseas POs paid to supplier but not received by Wangfoong Cargo';
		$SQLFilterKLStatus = " AND (purchorders.klstatus = '4000' AND suppliers.paymentterms = 'O4') ";
	}else if ($TypeOfCode == "IN AYE CARGO BUT NOT SHIPPED"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TableTitleText = 'Overseas POs waiting to be shipped by Aye Cargo';
		$SQLFilterKLStatus = " AND (   (purchorders.klstatus = '4500' AND suppliers.paymentterms = 'O2')
									OR (purchorders.klstatus = '4500' AND suppliers.paymentterms = 'O5')) ";
	}else if ($TypeOfCode == "IN WANGFOONG CARGO BUT NOT SHIPPED"){
		$DateField1 = "paymentdate";
		$FieldName1 = "Payment Date";
		$DateField2 = "shipmentdate";
		$FieldName2 = "Planned Shipment";
		$ShipmentAWB = '';
		$TableTitleText = 'Overseas POs waiting to be shipped by Wangfoong Cargo';
		$SQLFilterKLStatus = " AND (purchorders.klstatus = '4500' AND suppliers.paymentterms = 'O4') ";
	}else if ($TypeOfCode == "SHIPPED IN TRANSIT"){
		$DateField1 = "shipmentdate";
		$FieldName1 = "Shipment Date";
		$DateField2 = "customsdate";
		$FieldName2 = "Planned Customs";
		$ShipmentAWB = 'AWB';
		$TableTitleText = 'Overseas POs shipped and in transit to Customs';
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
		$TableTitleText = 'Overseas POs in Customs Clearance';
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
		$TableTitleText = 'Overseas POs already received in kantor';
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
		$TableTitleText = 'POs arriving in the next ' . $maxdays . ' days';
		$SQLFilterKLStatus = " AND purchorders.klstatus >= '1000'
			AND purchorders.klstatus <= '6000'
			AND purchorders." . $DateField1 ." <  '". $StartDate ."'";
	}else{
		return;
	}

	$SQLFilterProduct = "";
	if ($TypeOfProduct != ""){
		if ($TypeOfProduct == "PACKAGING"){
			$TableTitleText = "Packaging " . $TableTitleText;
			$SQLFilterProduct = " AND stockmaster.categoryid = 'SHPACK' ";
		}elseif ($TypeOfProduct == "OTHERS"){
			$TableTitleText = "Other " . $TableTitleText;
			$SQLFilterProduct = " AND (stockmaster.categoryid = 'SHDISP'
									OR stockmaster.categoryid = 'SHCONS'
									OR stockmaster.categoryid = 'SHOTHE')";
		}elseif ($TypeOfProduct == "FORSALE"){
			$TableTitleText = "Items FOR SALE " . $TableTitleText;
			$SQLFilterProduct = " AND stockmaster.categoryid != 'SHPACK'
								AND stockmaster.categoryid != 'SHDISP'
								AND stockmaster.categoryid != 'SHCONS'
								AND stockmaster.categoryid != 'SHOTHE'";
		}
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
				SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue,
				SUM(purchorderdetails.quantityord) AS orderitems
			FROM purchorders
			INNER JOIN purchorderdetails
				ON purchorders.orderno = purchorderdetails.orderno
			INNER JOIN stockmaster
				ON stockmaster.stockid = purchorderdetails.itemcode
			INNER JOIN suppliers
				ON  purchorders.supplierno = suppliers.supplierid
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			WHERE purchorderdetails.completed=0 "
				. $SQLFilterKLStatus .
				$SQLFilterProduct .
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th colspan="10">' . _('Order') . '</th>
							<th colspan="3">' . _('Supplier DP') . '</th>
							<th colspan="3">' . _('Payment Needed') . '</th>
							<th colspan="3">' . _('Acummulated Payment') . '</th>
						</tr>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('PO') . '</th>
							<th class="SortedColumn">' . _('Supplier') . '</th>
							<th class="SortedColumn">' . $FieldName1 . '</th>
							<th class="SortedColumn">' . $FieldName2 . '</th>
							<th class="SortedColumn">' . $ShipmentAWB . '</th>
							<th class="SortedColumn">' . _('# pcs') . '</th>
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
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;

		$TotalValueOrderIDR = 0;
		$TotalValueOrderUSD = 0;
		$TotalValueOrderTHB = 0;
		$TotalItemsAllOrders = 0;
		$TotalValueAllOrders = 0;
		$TotalValueAllPayments = 0;
		$AcumIDR = 0;
		$AcumUSD = 0;
		$AcumTHB = 0;
		$Payments = array();

		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/PO_Header.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';

			if (isset($Payments[$MyRow['supplierno']])){
				// we already have info in memory about the supplier
			}else{
				// the first time we find this supplier, let's get the balance
				$SQL = "SELECT SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
						FROM supptrans
						WHERE supptrans.supplierno = '" . $MyRow['supplierno'] . "'";
				$SupplierResult = DB_query($SQL);
				$MySupplier=DB_fetch_array($SupplierResult);
				$Payments[$MyRow['supplierno']]['currency'] = $MyRow['currcode'];
				$Payments[$MyRow['supplierno']]['balance'] = -$MySupplier['balance'];
			}

			$ValueOrderIDR = 0;
			$ValueOrderUSD = 0;
			$ValueOrderTHB = 0;
			$PaymentOrderIDR = 0;
			$PaymentOrderUSD = 0;
			$PaymentOrderTHB = 0;

			$TotalItemsAllOrders += $MyRow['orderitems'];

			if ($MyRow['currcode'] == 'IDR'){
				$ValueOrderIDR = $MyRow['ordervalue'];
				$TotalValueOrderIDR += $ValueOrderIDR;
				$TotalValueAllOrders += $ValueOrderIDR;
				$SupplierBalanceIDR =  $Payments[$MyRow['supplierno']]['balance'];
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
			}elseif	($MyRow['currcode'] == 'USD'){
				$ValueOrderUSD = $MyRow['ordervalue'];
				$TotalValueOrderUSD += $ValueOrderUSD;
				// Fix division by zero error
				if ($MyRow['exchangerate'] != 0) {
					$TotalValueAllOrders += ($ValueOrderUSD/$MyRow['exchangerate']*STANDARD_COST_FACTOR_FOREIGN);
				}
				$SupplierBalanceIDR =  0;
				$SupplierBalanceUSD =  $Payments[$MyRow['supplierno']]['balance'];
				$SupplierBalanceTHB =  0;
				if ($SupplierBalanceUSD >= $ValueOrderUSD){
					// we have enough balance to cover the order, no payment needed
					$PaymentOrderUSD = 0;
				}else{
					$PaymentOrderUSD = $ValueOrderUSD - $SupplierBalanceUSD;
					$AcumUSD = $AcumUSD + $PaymentOrderUSD;
					// Fix division by zero error
					if ($MyRow['exchangerate'] != 0) {
						$TotalValueAllPayments = $TotalValueAllPayments + ($PaymentOrderUSD/$MyRow['exchangerate']);
					}
				}
			}elseif	($MyRow['currcode'] == 'THB'){
				$ValueOrderTHB = $MyRow['ordervalue'];
				$TotalValueOrderTHB += $ValueOrderTHB;
				// Fix division by zero error
				if ($MyRow['exchangerate'] != 0) {
					$TotalValueAllOrders += ($ValueOrderTHB/$MyRow['exchangerate']*STANDARD_COST_FACTOR_FOREIGN);
				}
				$SupplierBalanceIDR =  0;
				$SupplierBalanceUSD =  0;
				$SupplierBalanceTHB =  $Payments[$MyRow['supplierno']]['balance'];
				if ($SupplierBalanceTHB >= $ValueOrderTHB){
					// we have enough balance to cover the order, no payment needed
					$PaymentOrderTHB = 0;
				}else{
					$PaymentOrderTHB = $ValueOrderTHB - $SupplierBalanceTHB;
					$AcumTHB = $AcumTHB + $PaymentOrderTHB;
					// Fix division by zero error
					if ($MyRow['exchangerate'] != 0) {
						$TotalValueAllPayments = $TotalValueAllPayments + ($PaymentOrderTHB/$MyRow['exchangerate']);
					}
				}
			}
			if ($FieldName2 == ""){
				$Date2 = "";
			}else{
				$Date2 = ConvertSQLDate($MyRow['reportdate2']);
			}
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td>' . $MyRow['supplierno'] . '</td>
					<td>' . ConvertSQLDate($MyRow['reportdate']) . '</td>
					<td>' . $Date2 . '</td>
					<td>' . $MyRow['shipmentawb'] . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['orderitems'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($ValueOrderIDR,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($ValueOrderUSD,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($ValueOrderTHB,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($SupplierBalanceIDR,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($SupplierBalanceUSD,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($SupplierBalanceTHB,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($PaymentOrderIDR,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($PaymentOrderUSD,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($PaymentOrderTHB,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($AcumIDR,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($AcumUSD,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($AcumTHB,0) . '</td>
					</tr>';
			// update the supplier balance after the order
			$Payments[$MyRow['supplierno']]['balance'] = $Payments[$MyRow['supplierno']]['balance'] - $MyRow['ordervalue'];
			if ($Payments[$MyRow['supplierno']]['balance'] < 0){
				$Payments[$MyRow['supplierno']]['balance'] = 0;
			}
			$i++;
		}
		echo'</tbody>
			<tfooter>';
		if (($TypeOfCode == "IN NEGOTIAION WITH SUPPLIER") OR
			($TypeOfCode == "ON PRODUCTION") OR
			($TypeOfCode == "FINISHED BUT NOT PAID") OR
			($TypeOfCode == "STILL NOT FULLY PAID") OR
			($TypeOfCode == "ARRIVING IN NEXT DAYS")){

			echo '<tr class="striped_row">
					<td class="number">' . '' . '</td>
					<td class="number">' . '' . '</td>
					<td>' . 'TOTAL ORDERS' . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalItemsAllOrders,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalValueOrderIDR,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalValueOrderUSD,0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalValueOrderTHB,0) . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalValueAllPayments,0) . '</td>
					<td>' . '' . '</td>
					<td>' . '' . '</td>
					</tr>';
			if (($TypeOfProduct == "FORSALE")
				AND ($maxdays > 0)){
				InsertKPI("Purchase Orders", "Payments pending items for sale in ". $maxdays . " days (IDR)", $TotalValueAllPayments);
			}
		}

		if (($TypeOfCode == "ARRIVING IN NEXT DAYS")
			AND ($TypeOfProduct == "FORSALE")){
			$CurrentTotalQtyItemsForSale = GetTotalQtyItemsForSale();
			$CurrentTotalValueItemsForSale = GetTotalValueItemsForSale($periodnow);
			InsertKPI("Stock", "Current Stock Items For Sale (IDR)", $CurrentTotalValueItemsForSale);
			InsertKPI("Stock", "Current Stock Items For Sale (PCS)", $CurrentTotalQtyItemsForSale);
			echo '<tr class="striped_row">
					<td class="number">' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td>' . "CURRENT STOCK" . '</td>
					<td>' . "IDR" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td class="number">' . locale_number_format_zero_blank($CurrentTotalValueItemsForSale,0) . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					</tr>';
		}

		if (($TypeOfCode == "IN NEGOTIAION WITH SUPPLIER") OR
			($TypeOfCode == "ON PRODUCTION") OR
			($TypeOfCode == "FINISHED BUT NOT PAID") OR
			($TypeOfCode == "STILL NOT FULLY PAID") OR
			($TypeOfCode == "ARRIVING IN NEXT DAYS")){
			echo '<tr class="striped_row">
					<td class="number">' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td>' . "TOTAL ORDERS" . '</td>
					<td>' . "IDR" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td class="number">' . locale_number_format_zero_blank($TotalValueAllOrders,0) . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					</tr>';
		}
		if (($TypeOfCode == "ARRIVING IN NEXT DAYS")
			AND ($TypeOfProduct == "FORSALE")){
			// Fix division by zero error
			$AverageItemCost = 0;
			if ($CurrentTotalQtyItemsForSale != 0) {
				$AverageItemCost = $CurrentTotalValueItemsForSale / $CurrentTotalQtyItemsForSale;
			}
			
			InsertKPI("Stock", "Average Standard Cost for item for sale (IDR)", $AverageItemCost);
			$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
			$SQL = "SELECT SUM(amount) AS cogs
					FROM  gltrans
					WHERE   trandate >= '". $StartDate ."'
						AND (account IN " . GL_COGS_GOODS ."
							OR account IN " . GL_COGS_OTHERS . ")";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			InsertKPI("Purchase Orders", "PO Items for sale arriving next ". $maxdays." days (IDR)", $TotalValueAllOrders);
			//Prevent division by zero error
			$ArrivingPCS = 0;
			if ($AverageItemCost != 0) {
				$ArrivingPCS = round($TotalValueAllOrders / $AverageItemCost);
			}
			InsertKPI("Purchase Orders", "PO Items for sale arriving next ". $maxdays." days (PCS @SC)", $ArrivingPCS);
			InsertKPI("Stock", "Expected COGS next ". $maxdays . " days (IDR)", round($MyRow['cogs'],-6));
			InsertKPI("Stock", "Expected COGS next ". $maxdays . " days (PCS)", round($MyRow['cogs']/$AverageItemCost, -2));
			$ExpectedDifferenceValueStock = round($TotalValueAllOrders-$MyRow['cogs'],-6);
			InsertKPI("Stock", "Expected difference stock in ". $maxdays . " days (IDR)", $ExpectedDifferenceValueStock);
			$ExpectedDifferenceQtyStock = 0;
			if ($AverageItemCost != 0) {
				$ExpectedDifferenceQtyStock = round($ExpectedDifferenceValueStock/$AverageItemCost, -2);
			}
			InsertKPI("Stock", "Expected difference stock in ". $maxdays . " days (PCS)", $ExpectedDifferenceQtyStock);
			$ExpectedFutureValueStock = round($CurrentTotalValueItemsForSale+$ExpectedDifferenceValueStock, -6);
			InsertKPI("Stock", "Expected future stock in ". $maxdays . " days (IDR)", $ExpectedFutureValueStock);
			$ExpectedFutureQtyStock = 0;
			if ($AverageItemCost != 0) {
				$ExpectedFutureQtyStock = round($ExpectedFutureValueStock / $AverageItemCost, -2);
			}
			InsertKPI("Stock", "Expected future stock in ". $maxdays . " days (PCS)", $ExpectedFutureQtyStock);
		}
		echo '</tfooter>
				</table>
				</div>';
	}
}

function PurchaseOrdersProcessTime($NumDays){

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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Process time (in days) for POs arrived during the last ') . $NumDays . " days";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('Country') . '</th>
							<th class="SortedColumn">' . _('#POs') . '</th>
							<th class="SortedColumn">' . _('Production') . '</th>
							<th class="SortedColumn">' . _('Payment') . '</th>
							<th class="SortedColumn">' . _('Ready To Ship') . '</th>
							<th class="SortedColumn">' . _('Transit') . '</th>
							<th class="SortedColumn">' . _('Customs') . '</th>
							<th class="SortedColumn">' . _('Min Days') . '</th>
							<th class="SortedColumn">' . _('Max Days') . '</th>
							<th class="SortedColumn">' . _('Average Days') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['productiondays'] < 0) {$MyRow['productiondays'] = 0;}
			if ($MyRow['paymentdays'] < 0) {$MyRow['paymentdays'] = 0;}
			if ($MyRow['shipmentdays'] < 0) {$MyRow['shipmentdays'] = 0;}
			if ($MyRow['transitdays'] < 0) {$MyRow['transitdays'] = 0;}
			if ($MyRow['customsdays'] < 0) {$MyRow['customsdays'] = 0;}
			if ($MyRow['mintotaldays'] < 0) {$MyRow['mintotaldays'] = 0;}
			if ($MyRow['maxtotaldays'] < 0) {$MyRow['maxtotaldays'] = 0;}
			if ($MyRow['avgtotaldays'] < 0) {$MyRow['avgtotaldays'] = 0;}

			echo '<tr class="striped_row">
					<td>' . $MyRow['address6'] . '</td>
					<td class="number">' . locale_number_format($MyRow['numorders'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['productiondays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['paymentdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['shipmentdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['transitdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['customsdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['mintotaldays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['maxtotaldays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['avgtotaldays'],0) . '</td>
					</tr>';
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['productiondays'] < 0) {$MyRow['productiondays'] = 0;}
			if ($MyRow['paymentdays'] < 0) {$MyRow['paymentdays'] = 0;}
			if ($MyRow['shipmentdays'] < 0) {$MyRow['shipmentdays'] = 0;}
			if ($MyRow['transitdays'] < 0) {$MyRow['transitdays'] = 0;}
			if ($MyRow['customsdays'] < 0) {$MyRow['customsdays'] = 0;}
			if ($MyRow['mintotaldays'] < 0) {$MyRow['mintotaldays'] = 0;}
			if ($MyRow['maxtotaldays'] < 0) {$MyRow['maxtotaldays'] = 0;}
			if ($MyRow['avgtotaldays'] < 0) {$MyRow['avgtotaldays'] = 0;}

			echo '<tr class="striped_row">
					<td>' . 'OVERSEAS' . '</td>
					<td class="number">' . locale_number_format($MyRow['numorders'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['productiondays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['paymentdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['shipmentdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['transitdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['customsdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['mintotaldays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['maxtotaldays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['avgtotaldays'],0) . '</td>
					</tr>';
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['productiondays'] < 0) {$MyRow['productiondays'] = 0;}
			if ($MyRow['paymentdays'] < 0) {$MyRow['paymentdays'] = 0;}
			if ($MyRow['shipmentdays'] < 0) {$MyRow['shipmentdays'] = 0;}
			if ($MyRow['transitdays'] < 0) {$MyRow['transitdays'] = 0;}
			if ($MyRow['customsdays'] < 0) {$MyRow['customsdays'] = 0;}
			if ($MyRow['mintotaldays'] < 0) {$MyRow['mintotaldays'] = 0;}
			if ($MyRow['maxtotaldays'] < 0) {$MyRow['maxtotaldays'] = 0;}
			if ($MyRow['avgtotaldays'] < 0) {$MyRow['avgtotaldays'] = 0;}

			echo '<tr class="striped_row">
					<td>' . 'Indonesia' . '</td>
					<td class="number">' . locale_number_format($MyRow['numorders'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['productiondays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['paymentdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['shipmentdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['transitdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['customsdays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['mintotaldays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['maxtotaldays'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['avgtotaldays'],0) . '</td>
					</tr>';
			$i++;
		}

		echo '</tbody>
			  </table>
			  </div>';
	}
}

function PurchaseOrdersWrongPlannedDates($RootPath){

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
				AND ( (purchorders.klstatus > '1000' AND purchorders.klstatus <= '2000'
						AND (purchorders.deliverydate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'B1'
						AND (purchorders.paymentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '7000' AND suppliers.paymentterms = 'B2'
						AND (purchorders.paymentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O1'
						AND (purchorders.paymentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O2'
						AND (purchorders.paymentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O3'
						AND (purchorders.paymentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O4'
						AND (purchorders.paymentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '4000' AND suppliers.paymentterms = 'O5'
						AND (purchorders.arrivaldate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O1'
						AND (purchorders.shipmentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O2'
						AND (purchorders.shipmentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O3'
						AND (purchorders.shipmentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O4'
						AND (purchorders.shipmentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5000' AND suppliers.paymentterms = 'O5'
						AND (purchorders.shipmentdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O1'
						AND (purchorders.customsdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O2'
						AND (purchorders.customsdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O3'
						AND (purchorders.customsdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O4'
						AND (purchorders.customsdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '5500' AND suppliers.paymentterms = 'O5'
						AND (purchorders.customsdate < CURRENT_DATE))
					 OR (purchorders.klstatus > '1000' AND purchorders.klstatus < '6000'
						AND (purchorders.arrivaldate < CURRENT_DATE))
					)
				AND purchorders.arrivaldate != purchorders.orddate
			GROUP BY purchorders.orderno
			ORDER BY purchorders.orderno";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('POs with wrong planned dates OR wrong status');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('#PO') . '</th>
							<th class="SortedColumn">' . _('Supplier') . '</th>
							<th class="SortedColumn">' . _('Order value') . '</th>
							<th class="SortedColumn">' . _('KL Status') . '</th>
							<th class="SortedColumn">' . _('Order') . '</th>
							<th class="SortedColumn">' . _('Agreed Delivery') . '</th>
							<th class="SortedColumn">' . _('Delivery') . '</th>
							<th class="SortedColumn">' . _('Payment') . '</th>
							<th class="SortedColumn">' . _('Shipment') . '</th>
							<th class="SortedColumn">' . _('Customs') . '</th>
							<th class="SortedColumn">' . _('Arrival') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/PO_Header.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			$OrderDate = ConvertSQLDate(substr($MyRow['orddate'],0,10));
			if ($MyRow['agreeddeliverydate'] == '1000-01-01'){
				$MyRow['agreeddeliverydate'] = '';
			} else {
				$MyRow['agreeddeliverydate'] = ConvertSQLDate($MyRow['agreeddeliverydate']);
			}
			if ($MyRow['deliverydate'] == '1000-01-01'){
				$MyRow['deliverydate'] = '';
			} else {
				$MyRow['deliverydate'] = ConvertSQLDate($MyRow['deliverydate']);
			}
			if ($MyRow['paymentdate'] == '1000-01-01'){
				$MyRow['paymentdate'] = '';
			} else {
				$MyRow['paymentdate'] = ConvertSQLDate($MyRow['paymentdate']);
			}
			if ($MyRow['shipmentdate'] == '1000-01-01'){
				$MyRow['shipmentdate'] = '';
			} else {
				$MyRow['shipmentdate'] = ConvertSQLDate($MyRow['shipmentdate']);
			}
			if ($MyRow['customsdate'] == '1000-01-01'){
				$MyRow['customsdate'] = '';
			} else {
				$MyRow['customsdate'] = ConvertSQLDate($MyRow['customsdate']);
			}
			if ($MyRow['arrivaldate'] == '1000-01-01'){
				$MyRow['arrivaldate'] = '';
			} else {
				$MyRow['arrivaldate'] = ConvertSQLDate($MyRow['arrivaldate']);
			}
			echo '<tr class="striped_row">
					<td class="number">' . $CodeLink . '</td>
					<td>' . $MyRow['supplierno'] . '</td>
					<td class="number">' . locale_number_format($MyRow['ordervalue'],0) . ' ' . $MyRow['currcode'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $OrderDate . '</td>
					<td>' . $MyRow['agreeddeliverydate'] . '</td>
					<td>' . $MyRow['deliverydate'] . '</td>
					<td>' . $MyRow['paymentdate'] . '</td>
					<td>' . $MyRow['shipmentdate'] . '</td>
					<td>' . $MyRow['customsdate'] . '</td>
					<td>' . $MyRow['arrivaldate'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			  </table>
			  </div>';
	}
}

function RecentlyClosedTransferStatus($maxdays, $RootPath){
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($maxdays == 1){
			$TableTitleText = _('List of Transfers Closed today ');
		}else{
			$TableTitleText = _('List of Transfers Closed during last ') . $maxdays  . ' days';
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Date') . '</th>
							<th class="SortedColumn">' . _('Transfer') . '</th>
							<th class="SortedColumn">' . _('From') . '</th>
							<th class="SortedColumn">' . _('To') . '</th>
							<th class="SortedColumn">' . _('Qty') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 1;
		$Total = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . ConvertSQLDateTime($MyRow['recdate']) . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['locfrom'] . '</td>
					<td>' . $MyRow['locto'] . '</td>
					<td class="number">' . locale_number_format($MyRow['receivedqty'],0) . '</td>
					</tr>';
			$i++;
			$Total = $Total + $MyRow['receivedqty'];
		}
		echo'</tbody>
			<tfooter>';
		echo '<tr class="striped_row">
				<td class="number">' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . '' . '</td>
				<td>' . 'Total' . '</td>
				<td class="number">' . locale_number_format($Total,0) . '</td>
				</tr>';
		echo '</tfooter>
				</table>
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

function TransfersDelayed($maxdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT DISTINCT reference,
					shipdate,
					shiploc,
					recloc
			FROM loctransfers
			WHERE  pendingqty > 0
				AND shipdate <= '". $StartDate ."'
			ORDER BY reference";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Transfers delayed more than ') . $maxdays . _(' days ');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Transfer') . '</th>
							<th class="SortedColumn">' . _('Date') . '</th>
							<th class="SortedColumn">' . _('From') . '</th>
							<th class="SortedColumn">' . _('To') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . ConvertSQLDate($MyRow['shipdate']) . '</td>
					<td>' . $MyRow['shiploc'] . '</td>
					<td>' . $MyRow['recloc'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
				</table>
				</div>';
	}
}

function WrongStandardCost($Country, $StockCat, $StdFactor, $Tolerance, $Mode, $RootPath){
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
				(stockmaster.actualcost) AS stdcost,
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
						< (stockmaster.actualcost))
					OR	(((purchdata.price / purchdata.conversionfactor) * " . $StdFactor . " * (1 / currencies.rate) * " . $ToleranceLow . " )
						> (stockmaster.actualcost))
					)
				AND purchdata.supplierno = suppliers.supplierid
				AND purchdata.effectivefrom = (SELECT MAX(p2.effectivefrom)
												FROM purchdata p2
												WHERE p2.stockid = purchdata.stockid)
			ORDER BY stockmaster.stockid";
// EXPLAIN SQL 2014-05-31
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $StockCat . ' Items from ' . $Country . _(' with wrong Standard Cost') .  ' ---> Cost Factor = ' . locale_number_format($StdFactor, 2) . ' ---> Tolerance = '. locale_number_format($Tolerance * 100, 2) .'%';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		if ($Mode == "SHOWONLY"){
			$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th class="SortedColumn">' . _('Supplier') . '</th>
								<th class="SortedColumn">' . _('From') . '</th>
								<th class="SortedColumn">' . _('Price') . '</th>
								<th class="SortedColumn">' . _('Currency') . '</th>
								<th class="SortedColumn">' . _('Rate') . '</th>
								<th class="SortedColumn">' . _('Supplier UOM') . '</th>
								<th class="SortedColumn">' . _('UOM Factor') . '</th>
								<th class="SortedColumn">' . _('Date Std Cost') . '</th>
								<th class="SortedColumn">' . _('Wrong Std Cost') . '</th>
								<th class="SortedColumn">' . _('Real Std Cost') . '</th>
							</tr>
							</thead>
							<tbody>';
		}else{
			$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th class="SortedColumn">' . _('Supplier') . '</th>
								<th class="SortedColumn">' . _('From') . '</th>
								<th class="SortedColumn">' . _('Price') . '</th>
								<th class="SortedColumn">' . _('Currency') . '</th>
								<th class="SortedColumn">' . _('Rate') . '</th>
								<th class="SortedColumn">' . _('Supplier UOM') . '</th>
								<th class="SortedColumn">' . _('UOM Factor') . '</th>
								<th class="SortedColumn">' . _('Date Std Cost') . '</th>
								<th class="SortedColumn">' . _('Wrong Std Cost') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
									<th class="SortedColumn">' . _('KL UOM') . '</th>
								<th class="SortedColumn">' . _('Real Std Cost') . '</th>
								<th class="SortedColumn">' . _('% Dif') . '</th>
							</tr>
							</thead>
							<tbody>';
		}
		echo $TableHeader;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';

			// Fix division by zero errors
			$NewStdCost = 0;
			if ($MyRow['conversionfactor'] > 0 && $MyRow['rate'] > 0) {
				$NewStdCost = $MyRow['price'] / $MyRow['conversionfactor'] * (1/$MyRow['rate']) * $StdFactor;
			}
			
			$Price = locale_number_format($MyRow['price'],$MyRow['decimalplaces']);
			$PurchasingLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $MyRow['stockid'] . '&SupplierID='. $MyRow['supplierno'] . '&Edit=1&EffectiveFrom='. $MyRow['effectivefrom']  .' ">' . $Price . '</a>';
			if ($Mode == "SHOWONLY"){
				$StdCostText = locale_number_format($NewStdCost,0);
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td>' . $MyRow['supplierno'] . '</td>
						<td>' . ConvertSQLDate($MyRow['effectivefrom']) . '</td>
						<td class="number">' . $PurchasingLink . '</td>
						<td>' . $MyRow['currcode'] . '</td>
						<td class="number">' . locale_number_format(1/$MyRow['rate'],2) . '</td>
						<td>' . $MyRow['suppliersuom'] . '</td>
						<td class="number">' . locale_number_format($MyRow['conversionfactor'],0) . '</td>
						<td>' . ConvertSQLDate($MyRow['lastcostupdate']) . '</td>
						<td class="number">' . locale_number_format($MyRow['stdcost'],0) . '</td>
						<td class="number">' . $StdCostText . '</td>
						</tr>';
			}else{
				if($Mode == "UPDATEALL"){
					// UPDATEALL
					$StdCostText = locale_number_format($NewStdCost,0);
					ChangeItemStandardCost($MyRow['stockid'], $NewStdCost, $MyRow['stdcost'], $MyRow['qoh']);
				}else{
					// SHOWLINK
					$StdCostText = '<a href="' . $RootPath . '/KLUpdateStandardCost.php?StockId=' . $MyRow['stockid'] . '&NewCost=' . round($NewStdCost,0) .'">' . locale_number_format($NewStdCost,0) . '</a>';
				}
				// Fix division by zero error
				$PercentDiff = 0;
				if ($MyRow['stdcost'] != 0) {
					$PercentDiff = ((($MyRow['price'] / $MyRow['conversionfactor'] * (1/$MyRow['rate']) * $StdFactor)/$MyRow['stdcost'] * 100)-100);
				}
				
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td>' . $MyRow['supplierno'] . '</td>
						<td>' . ConvertSQLDate($MyRow['effectivefrom']) . '</td>
						<td class="number">' . $PurchasingLink . '</td>
						<td>' . $MyRow['currcode'] . '</td>
						<td class="number">' . locale_number_format(1/$MyRow['rate'],2) . '</td>
						<td>' . $MyRow['suppliersuom'] . '</td>
						<td class="number">' . locale_number_format($MyRow['conversionfactor'],0) . '</td>
						<td>' . ConvertSQLDate($MyRow['lastcostupdate']) . '</td>
						<td class="number">' . locale_number_format($MyRow['stdcost'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qoh'],0) . '</td>
						<td>' . $MyRow['units'] . '</td>
						<td class="number">' . $StdCostText . '</td>
						<td class="number">' . locale_number_format($PercentDiff,1) . '%' . '</td>
						</tr>';
			}
			$i++;
		}
		echo '</tbody>
			  </table>
			  </div>';
	}
}

function ShowTotalItemsMoving(){
	$NumItems = GetTotalItemsChangingPrice();
	$WarningTitleText = "# Items changing price: " . $NumItems;
	ShowWarningTitle($WarningTitleText);
	InsertKPI("Prices", "Items changing price", $NumItems);

	$NumItems = GetTotalItemsMovingToDiscount("20");
	$WarningTitleText = "# Items moving to 20% discount: " . $NumItems;
	ShowWarningTitle($WarningTitleText);
	InsertKPI("Prices", "Items moving to 20% discount", $NumItems);

	$NumItems = GetTotalItemsMovingToDiscount("50");
	$WarningTitleText = "# Items moving to 50% discount: " . $NumItems;
	ShowWarningTitle($WarningTitleText);
	InsertKPI("Prices", "Items moving to 50% discount", $NumItems);

	$NumItems = GetTotalItemsMovingToDiscount("80");
	$WarningTitleText = "# Items moving to 80% discount: " . $NumItems;
	ShowWarningTitle($WarningTitleText);
	InsertKPI("Prices", "Items moving to 80% discount", $NumItems);
}

function OnlineMarketPlacePaymentPending($Days, $RootPath){
	// if $Days = 0 it means all the Online Marketplace Orders still pending of payment
	// if $Days > 0 it means the same but only show the delayed for more than $Days
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$Days));

	if ($Days == 0){
		$TableTitleText = "All Marketplace Online Orders with Payment Pending";
		$WhereStatement = "";
	}else{
		$TableTitleText = "Delayed Marketplace Online Orders Payment Pending for more than " . $Days . " days";
		$WhereStatement = " AND salesorders.orddate <= '" . $StartDate . "' ";
	}

	$SQL = "SELECT salesorders.orderno,
				salesorders.customerref,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
				salesorders.klpaidcash,
				debtorsmaster.currcode,
				currencies.decimalplaces
			FROM salesorders
				INNER JOIN salesorderdetails
					ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster
					ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorders.klpaidcash= 0
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_MARKETPLACE . ") " .
				$WhereStatement . "
			GROUP BY salesorders.orderno,
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.debtorno,
					salesorders.deliverto";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Customer') . '</th>
							<th class="SortedColumn">' . _('Name') . '</th>
							<th class="SortedColumn">' . _('Order') . '</th>
							<th class="SortedColumn">' . _('#MarketPlace') . '</th>
							<th class="SortedColumn">' . _('Order Date') . '</th>
							<th class="SortedColumn">' . _('Order Value') . '</th>
							<th class="SortedColumn">' . _('Currency') . '</th>
							<th class="SortedColumn">' . '' . '</th>
							<th class="SortedColumn">' . _('Paid Tokopedia') . '</th>
							<th class="SortedColumn">' . _('Paid Shopee') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 1;
		$TotalShopeeValue = 0;
		$TotalTokopediaValue = 0;
		$TotalPaymentValue = 0;
		$DecimalPlaces = 0;

		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			$PaymentLinkText = 'Apply Payment';
			$PaymentValue = $MyRow['ordervalue']+$MyRow['freightcost'];

			$PaymentLinkManualText = 'Mark As Paid';

			$PaymentManual = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $MyRow['orderno'] . '&PaymentCode=' . 'MANUAL_MARKETPLACE' . '&CustomerCode=' . $MyRow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkManualText .'</a>';
			// prepare the links according to the Marketplace
			if ($MyRow['debtorno'] == "TOKOPEDIA"){
				$PaymentTokopedia = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $MyRow['orderno'] . '&PaymentCode=' . 'tokopedia' . '&CustomerCode=' . $MyRow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
				$PaymentManual = '';
				$TotalTokopediaValue += $PaymentValue;
			}else{
				$PaymentTokopedia = '';
			}
			if ($MyRow['debtorno'] == "SHOPEE"){
				$PaymentShopee = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $MyRow['orderno'] . '&PaymentCode=' . 'shopee' . '&CustomerCode=' . $MyRow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
				$PaymentManual = '';
				$TotalShopeeValue += $PaymentValue;
			}else{
				$PaymentShopee = '';
			}
			$DecimalPlaces = $MyRow['decimalplaces'];

			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $MyRow['debtorno'] . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td class="number">' . $MyRow['customerref'] . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td class="number">' . locale_number_format($PaymentValue,$DecimalPlaces) . '</td>
					<td>' . $MyRow['currcode'] . '</td>
					<td>' . $PaymentManual . '</td>
					<td>' . $PaymentTokopedia . '</td>
					<td>' . $PaymentShopee . '</td>
					</tr>';
			$i++;
			$TotalPaymentValue += $PaymentValue;
		}
		echo'</tbody>
			<tfooter>';
		// for the detailed report, show totals. If only delayed more than $Days, no need to show totals
		if ($Days == 0){
			echo '<tr class="striped_row">
					<td class="number">' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td>' . "SHOPEE:" . '</td>
					<td class="number">' . locale_number_format($TotalShopeeValue,$DecimalPlaces) . '</td>
					<td>' . "IDR" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					</tr>';
				echo '<tr class="striped_row">
					<td class="number">' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td>' . "TOKOPEDIA:" . '</td>
					<td class="number">' . locale_number_format($TotalTokopediaValue,$DecimalPlaces) . '</td>
					<td>' . "IDR" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					</tr>';
				echo '<tr class="striped_row">
					<td class="number">' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td class="number">' . "" . '</td>
					<td>' . "TOTAL:" . '</td>
					<td class="number">' . locale_number_format($TotalPaymentValue,$DecimalPlaces) . '</td>
					<td>' . "IDR" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					<td>' . "" . '</td>
					</tr>';
		}
		echo '</tfooter>
				</table>
				</div>';
	}
}

function MaintenanceTasksDistribution($Status, $NumDays, $UserIsSystemAdmin){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	if ($Status == "OPEN"){
		$WhereStatus = "WHERE klmaintenancetasks.closed = 0";
		$Title = 'Open Maintenance Tasks distribution';
	}elseif ($Status == "CLOSED"){
		$WhereStatus = "WHERE klmaintenancetasks.closed = 1
							AND closedate >= '" . $FromDate . "'";
		$Title = 'Closed Maintenance Tasks distribution during the last ' . $NumDays . ' days';
	}elseif ($Status == "TOTAL"){
		$WhereStatus = "WHERE klmaintenancetasks.closed = 0
							OR (klmaintenancetasks.closed = 1
								AND closedate >= '" . $FromDate . "')";
		$Title = 'All Maintenance Tasks distribution during the last ' . $NumDays . ' days';
	}
	$TableResult = array();
	// now populate the array with info
	$SQL = "SELECT COUNT(counterindex) AS total, 
				klmaintenancetasks.loccode,
				locations.locationname,
				klmaintenancetasks.maintenancetype
			FROM klmaintenancetasks
				INNER JOIN locations 
					ON locations.loccode=klmaintenancetasks.loccode 
				INNER JOIN klmaintenancetypes 
					ON klmaintenancetypes.maintenancetype=klmaintenancetasks.maintenancetype 
				INNER JOIN locationusers 
					ON locationusers.loccode=klmaintenancetasks.loccode 
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1 " . 
			$WhereStatus . "
			GROUP BY klmaintenancetasks.loccode, klmaintenancetasks.maintenancetype
			ORDER BY locationname, klmaintenancetasks.maintenancetype";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$TableResult[$MyRow['loccode']]['locationname'] = $MyRow['locationname'];
			$TableResult[$MyRow['loccode']][$MyRow['maintenancetype']] = $MyRow['total'];
		}
		$TableHeader = '<tr>
						<th class="SortedColumn">' . _('Location') . '</th>
						<th class="SortedColumn">' . _('AC') . '</th>
						<th class="SortedColumn">' . _('Bocor') . '</th>
						<th class="SortedColumn">' . _('Furniture') . '</th>
						<th class="SortedColumn">' . _('IT') . '</th>
						<th class="SortedColumn">' . _('Kanopi') . '</th>
						<th class="SortedColumn">' . _('Lampu') . '</th>
						<th class="SortedColumn">' . _('Listrik') . '</th>
						<th class="SortedColumn">' . _('Paint') . '</th>
						<th class="SortedColumn">' . _('Pintukaca') . '</th>
						<th class="SortedColumn">' . _('Toilet') . '</th>
						<th class="SortedColumn">' . _('Wallpaper') . '</th>
						<th class="SortedColumn">' . _('DLL') . '</th>
						<th class="SortedColumn">' . _('Total') . '</th>
					</tr>';
		$TableTitleText = $Title;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>';
		echo $TableHeader;
		echo '</thead>
				<tbody>';
		$TotalIssuesAC = 0;
		$TotalIssuesBOCOR = 0;
		$TotalIssuesFURNITURE = 0;
		$TotalIssuesIT = 0;
		$TotalIssuesKANOPI = 0;
		$TotalIssuesLAMPU = 0;
		$TotalIssuesLISTRIK = 0;
		$TotalIssuesPAINT = 0;
		$TotalIssuesPINTUKACA = 0;
		$TotalIssuesTOILET = 0;
		$TotalIssuesWALLPAPER = 0;
		$TotalIssuesDLL = 0;
		$TotalIssues = 0;
		foreach ($TableResult as $Row) {
			$TotalIssuesLocation = 0;
			if (isset($Row['AC'])){
				$IssuesAC = $Row['AC'];
				$TotalIssuesAC += $IssuesAC;
				$TotalIssuesLocation += $IssuesAC;
				$TotalIssues += $IssuesAC;
			}else{
				$IssuesAC = '';
			}
			if (isset($Row['BOCOR'])){
				$IssuesBOCOR = $Row['BOCOR'];
				$TotalIssuesBOCOR += $IssuesBOCOR;
				$TotalIssuesLocation += $IssuesBOCOR;
				$TotalIssues += $IssuesBOCOR;
			}else{
				$IssuesBOCOR = '';
			}
			if (isset($Row['FURNITURE'])){
				$IssuesFURNITURE = $Row['FURNITURE'];
				$TotalIssuesFURNITURE += $IssuesFURNITURE;
				$TotalIssuesLocation += $IssuesFURNITURE;
				$TotalIssues += $IssuesFURNITURE;
			}else{
				$IssuesFURNITURE = '';
			}
			if (isset($Row['IT'])){
				$IssuesIT = $Row['IT'];
				$TotalIssuesIT += $IssuesIT;
				$TotalIssuesLocation += $IssuesIT;
				$TotalIssues += $IssuesIT;
			}else{
				$IssuesIT = '';
			}
			if (isset($Row['KANOPI'])){
				$IssuesKANOPI = $Row['KANOPI'];
				$TotalIssuesKANOPI += $IssuesKANOPI;
				$TotalIssuesLocation += $IssuesKANOPI;
				$TotalIssues += $IssuesKANOPI;
			}else{
				$IssuesKANOPI = '';
			}
			if (isset($Row['LAMPU'])){
				$IssuesLAMPU = $Row['LAMPU'];
				$TotalIssuesLAMPU += $IssuesLAMPU;
				$TotalIssuesLocation += $IssuesLAMPU;
				$TotalIssues += $IssuesLAMPU;
			}else{
				$IssuesLAMPU = '';
			}
			if (isset($Row['LISTRIK'])){
				$IssuesLISTRIK = $Row['LISTRIK'];
				$TotalIssuesLISTRIK += $IssuesLISTRIK;
				$TotalIssuesLocation += $IssuesLISTRIK;
				$TotalIssues += $IssuesLISTRIK;
			}else{
				$IssuesLISTRIK = '';
			}
			if (isset($Row['PAINT'])){
				$IssuesPAINT = $Row['PAINT'];
				$TotalIssuesPAINT += $IssuesPAINT;
				$TotalIssuesLocation += $IssuesPAINT;
				$TotalIssues += $IssuesPAINT;
			}else{
				$IssuesPAINT = '';
			}
			if (isset($Row['PINTUKACA'])){
				$IssuesPINTUKACA = $Row['PINTUKACA'];
				$TotalIssuesPINTUKACA += $IssuesPINTUKACA;
				$TotalIssuesLocation += $IssuesPINTUKACA;
				$TotalIssues += $IssuesPINTUKACA;
			}else{
				$IssuesPINTUKACA = '';
			}
			if (isset($Row['TOILET'])){
				$IssuesTOILET = $Row['TOILET'];
				$TotalIssuesTOILET += $IssuesTOILET;
				$TotalIssuesLocation += $IssuesTOILET;
				$TotalIssues += $IssuesTOILET;
			}else{
				$IssuesTOILET = '';
			}
			if (isset($Row['WALLPAPER'])){
				$IssuesWALLPAPER = $Row['WALLPAPER'];
				$TotalIssuesWALLPAPER += $IssuesWALLPAPER;
				$TotalIssuesLocation += $IssuesWALLPAPER;
				$TotalIssues += $IssuesWALLPAPER;
			}else{
				$IssuesWALLPAPER = '';
			}
			if (isset($Row['_DLL'])){
				$IssuesDLL = $Row['_DLL'];
				$TotalIssuesDLL += $IssuesDLL;
				$TotalIssuesLocation += $IssuesDLL;
				$TotalIssues += $IssuesDLL;
			}else{
				$IssuesDLL = '';
			}
			echo '<tr class="striped_row">
					<td>' . $Row['locationname'] . '</td>
					<td class="number">' . $IssuesAC . '</td>
					<td class="number">' . $IssuesBOCOR . '</td>
					<td class="number">' . $IssuesFURNITURE . '</td>
					<td class="number">' . $IssuesIT . '</td>
					<td class="number">' . $IssuesKANOPI . '</td>
					<td class="number">' . $IssuesLAMPU . '</td>
					<td class="number">' . $IssuesLISTRIK . '</td>
					<td class="number">' . $IssuesPAINT . '</td>
					<td class="number">' . $IssuesPINTUKACA . '</td>
					<td class="number">' . $IssuesTOILET . '</td>
					<td class="number">' . $IssuesWALLPAPER . '</td>
					<td class="number">' . $IssuesDLL . '</td>
					<td class="number">' . $TotalIssuesLocation . '</td>
					</tr>';
		}
		echo '<tr class="striped_row">
				<td>' . "TOTAL" . '</td>
				<td class="number">' . $TotalIssuesAC . '</td>
				<td class="number">' . $TotalIssuesBOCOR . '</td>
				<td class="number">' . $TotalIssuesFURNITURE . '</td>
				<td class="number">' . $TotalIssuesIT . '</td>
				<td class="number">' . $TotalIssuesKANOPI . '</td>
				<td class="number">' . $TotalIssuesLAMPU . '</td>
				<td class="number">' . $TotalIssuesLISTRIK . '</td>
				<td class="number">' . $TotalIssuesPAINT . '</td>
				<td class="number">' . $TotalIssuesPINTUKACA . '</td>
				<td class="number">' . $TotalIssuesTOILET . '</td>
				<td class="number">' . $TotalIssuesWALLPAPER . '</td>
				<td class="number">' . $TotalIssuesDLL . '</td>
				<td class="number">' . $TotalIssues . '</td>
				</tr>';
		
		echo '</tbody></table>
			</div>';
		if ($UserIsSystemAdmin){
			if ($Status == "OPEN"){
				InsertKPI("Maintenance", "Open Maintenance Tasks", $TotalIssues);
			}elseif ($Status == "CLOSED"){
				InsertKPI("Maintenance", "Closed Maintenance Tasks during " . $NumDays . " days", $TotalIssues);
			}elseif ($Status == "TOTAL"){
				InsertKPI("Maintenance", "All Maintenance Tasks during " . $NumDays . " days", $TotalIssues);
			}
		}
	}
}

function QualityIssuesByReason($Days, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$Days));

	$SQL = "SELECT reasonname,
				COUNT(*) AS totalreturned
			FROM returneditems
			INNER JOIN returnitemreasons
				ON returneditems.reasonid = returnitemreasons.reasonid
			WHERE returneditems.returndate >= '" . $StartDate . "'
			GROUP BY returnitemreasons.reasonid
			ORDER BY totalreturned DESC";

	$TotalReturned = 0;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('# Items returned by customer by Reason during the last ') . $Days . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Reason') . '</th>
						<th class="SortedColumn">' . _('# Items returned') . '</th>
						<th class="SortedColumn">' . _('Daily Average') . '</th>
				</tr>
				</thead>	
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>' . $MyRow['reasonname'] . '</td>
					<td class="number">' . locale_number_format($MyRow['totalreturned'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['totalreturned'] / $Days, 1) . '</td>
				</tr>';

			$TotalReturned += $MyRow['totalreturned'];

			if ($Days == 30) {
				InsertKPI('Returned Items', $MyRow['reasonname'] . ' Last 30 days (PCS)', $MyRow['totalreturned']);
			}
		}
	}

	if ($Days == 30) {
		InsertKPI('Returned Items', 'Total Returned Items Last 30 days (PCS)', $TotalReturned);
	}		

	echo '</tbody>
		<tfooter>';
	echo '<tr class="striped_row">
			<td>Total</td>
			<td class="number">' . locale_number_format($TotalReturned, 0) . '</td>
			<td class="number">' . locale_number_format($TotalReturned / $Days, 1) . '</td>
		</tr>';
	 echo '</tfooter>
		</table>
		</div>';
}

function StockAdjustmentsByReason($Days){
$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$Days));

$SQL = "SELECT reasonname,
			SUM(qty) AS totaladjusted
		FROM stockmoves
		INNER JOIN stockadjustments
			ON stockmoves.transno = stockadjustments.transno
			AND stockmoves.type = 17
		INNER JOIN stockadjustmentreasons
			ON stockadjustments.reasonid = stockadjustmentreasons.reasonid
		WHERE stockmoves.trandate >= '" . $StartDate . "'
		GROUP BY stockadjustmentreasons.reasonid
		ORDER BY ABS(SUM(qty)) DESC";

$TotalAdjusted = 0;
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0){
	$TableTitleText = _('# stock adjustments by Reason during the last ') . $Days . ' days';
	ShowTableTitle($TableTitleText);
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Reason') . '</th>
					<th class="SortedColumn">' . _('Qty adjusted') . '</th>
					<th class="SortedColumn">' . _('Daily Average') . '</th>
				</tr>
			</thead>
			<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $MyRow['reasonname'] . '</td>
				<td class="number">' . locale_number_format($MyRow['totaladjusted'],0) . '</td>
				<td class="number">' . locale_number_format($MyRow['totaladjusted'] / $Days, 1) . '</td>
			</tr>';

		$TotalAdjusted += $MyRow['totaladjusted'];
		if ($Days == 30) {
			InsertKPI('Stock Adjustments', $MyRow['reasonname'] . ' Last 30 days (PCS)', $MyRow['totaladjusted']);
		}
	}
}

if ($Days == 30) {
	InsertKPI('Stock Adjustments', 'Total Stock Adjustments Last 30 days (PCS)', $TotalAdjusted);
}

echo '</tbody>
	<tfooter>';
echo '<tr class="striped_row">
		<td>Total</td>
		<td class="number">' . locale_number_format($TotalAdjusted, 0) . '</td>
		<td class="number">' . locale_number_format($TotalAdjusted / $Days, 1) . '</td>
	</tr>';
echo '</tfooter>
	</table>
	</div>';
}	


?>
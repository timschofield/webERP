<?php
define("VERSIONFILE", "2.00");

include('includes/session.php');
$Title = _('KL Stock Adjustment and Customer Returns Board '. VERSIONFILE);
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$BeginTime = time_start();
$NumberOfTestExecuted = 0;

if ($KL_SystemAdmin 
	OR $KL_OperationalManager
	OR $KL_BusinessDevelopmentManager
	OR $KL_SalesDirector
	OR $KL_PurchasingTeam
	OR $KL_ShopSupportLeader) {

	StockAdjustmentsByReason(30, $RootPath);
	$NumberOfTestExecuted++;
	StockAdjustmentsByReason(90, $RootPath);
	$NumberOfTestExecuted++;
	StockAdjustmentsByItemAndReason(30, $RootPath);
	$NumberOfTestExecuted++;
	QualityIssuesByReason(30, $RootPath);
	$NumberOfTestExecuted++;
	QualityIssuesByReason(90, $RootPath);
	$NumberOfTestExecuted++;
	QualityIssuesByItem("QualityIssuesByItem", 90, $RootPath);
	$NumberOfTestExecuted++;
	QualityIssuesByItem("QualityIssuesByFamily", 90, $RootPath);
	$NumberOfTestExecuted++;
	QualityIssuesByItem("ChangeOfMindByFamily", 90, $RootPath);
	$NumberOfTestExecuted++;
}

prnMsg("Performed ". $NumberOfTestExecuted . " Quality and Returns tests", 'success');

if ($KL_SystemAdmin) {
	time_finish($BeginTime);
}

include('includes/footer.php');

function QualityIssuesByItem($TypeReport, $NumDays, $RootPath) {
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDays + 1));

	if ($TypeReport == "QualityIssuesByItem") {
		$SQL = "SELECT itemcode AS Item, 
					COUNT(*) AS Incidences,
					(SELECT SUM(salesorderdetails.qtyinvoiced)
						FROM salesorderdetails
						WHERE salesorderdetails.stkcode = returneditems.itemcode
							AND salesorderdetails.completed = 1
							AND salesorderdetails.itemdue >= '". $StartDate . "') AS QtySold
				FROM returneditems
				WHERE (reasonid = 4 OR reasonid = 5)
					AND oldinvoicedate >= '". $StartDate . "'
				GROUP BY itemcode";
		$TableTitleText = 'Items returned by customers due to Quality Issues in the last ' . $NumDays . ' days';
		$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Incidences') . '</th>
								<th class="SortedColumn">' . _('Qty Sold') . '</th>
								<th class="SortedColumn">' . _('%Incidences') . '</th>
							</tr>
						</thead>';
	} elseif ($TypeReport == "QualityIssuesByFamily") {
		//2025-03-31: SQL query optimized by GitHib Copilot, around 30 times faster than the original one
		$SQL = "SELECT SUBSTRING(r.itemcode, 1, 2) AS Item, 
					COUNT(*) AS Incidences, 
					COALESCE(s.QtySold, 0) AS QtySold 
				FROM returneditems r
				LEFT JOIN (
					SELECT SUBSTRING(stkcode, 1, 2) AS ItemPrefix,
						SUM(qtyinvoiced) AS QtySold
					FROM salesorderdetails
					WHERE completed = 1 
					AND itemdue >= '". $StartDate . "'
					GROUP BY SUBSTRING(stkcode, 1, 2)
				) s ON SUBSTRING(r.itemcode, 1, 2) = s.ItemPrefix
				WHERE r.reasonid IN (4, 5)
				AND r.oldinvoicedate >= '". $StartDate . "'
				GROUP BY SUBSTRING(r.itemcode, 1, 2)";

		$TableTitleText = 'Items returned by customers due to Quality Issues of items in the last ' . $NumDays . ' days (grouped by family)';
		$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Family') . '</th>
								<th class="SortedColumn">' . _('Incidences') . '</th>
								<th class="SortedColumn">' . _('Qty Sold') . '</th>
								<th class="SortedColumn">' . _('%Incidences') . '</th>
							</tr>
						</thead>';
	} elseif ($TypeReport == "ChangeOfMindByFamily") {
		//2025-03-31: SQL query optimized by GitHib Copilot, around 30 times faster than the original one
		$SQL = "SELECT SUBSTRING(r.itemcode, 1, 2) AS Item, 
					COUNT(*) AS Incidences, 
					COALESCE(s.QtySold, 0) AS QtySold 
				FROM returneditems r
				LEFT JOIN (
					SELECT SUBSTRING(stkcode, 1, 2) AS ItemPrefix,
						SUM(qtyinvoiced) AS QtySold
					FROM salesorderdetails
					WHERE completed = 1 
					AND itemdue >= '". $StartDate . "'
					GROUP BY SUBSTRING(stkcode, 1, 2)
				) s ON SUBSTRING(r.itemcode, 1, 2) = s.ItemPrefix
				WHERE r.reasonid IN (1, 2, 3)
				AND r.oldinvoicedate >= '". $StartDate . "'
				GROUP BY SUBSTRING(r.itemcode, 1, 2)";
				$TableTitleText = 'Items returned by customers due to Change of Mind of items in the last ' . $NumDays . ' days (grouped by family)';
				$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Family') . '</th>
								<th class="SortedColumn">' . _('Incidences') . '</th>
								<th class="SortedColumn">' . _('Qty Sold') . '</th>
								<th class="SortedColumn">' . _('%Incidences') . '</th>
							</tr>
						</thead>';
	}

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0) {
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		echo $TableHeader;
		echo '<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$PercentIncidences = ($MyRow['QtySold'] != 0) ? $MyRow['Incidences'] / $MyRow['QtySold'] : 0;
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $MyRow['Item'] . '</td>
					<td class="number">' . locale_number_format($MyRow['Incidences'], 0) . '</td>
					<td class="number">' . locale_number_format($MyRow['QtySold'], 0) . '</td>
					<td class="number">' . locale_number_format($PercentIncidences * 100, 1) . '%' . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody></table></div>';
	}
}


function ReturnsBySPG($SPG, $NumDays) {
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$NumDays));

	if ($SPG != "ALL") {
		$WhereSPG = " AND salesman.salesmancode = " . $SPG . " ";
	} else {
		$WhereSPG = " ";
	}
	
	$Yesterday = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -1));
	
	$SQL = "SELECT salesorders.salesperson,
				salesman.salesmanname,
				(SELECT COUNT(*)
					FROM returneditems,
					WHERE returneditems.returndate >= '". $StartDate ."'
						AND so2.salesperson = salesorders.salesperson) AS TotalOrders,
				(SELECT SUM(qtyinvoiced)
					FROM salesorderdetails, salesorders AS so2
					WHERE salesorderdetails.orderno = so2.orderno
						AND so2.orddate >= '". $StartDate ."'
						AND so2.orddate <= '". $Yesterday ."'
						AND so2.salesperson = salesorders.salesperson
						AND salesorderdetails.stkcode = 'ONLINE-VIP-PACK') AS OnlineVipCards 
			FROM salesorders, salesman
			WHERE salesman.salesmancode = salesorders.salesperson " . 
				$WhereSPG . "
				AND salesorders.orddate >= '". $StartDate ."'
				AND salesorders.orddate <= '". $Yesterday ."'
			GROUP BY salesorders.salesperson
			ORDER BY salesorders.salesperson";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0) {
		$TableTitleText = _('Quality data Retail Customer by SPG during the last ') . 
						locale_number_format($NumDays, 0) . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('SPG') . '</th>
								<th class="SortedColumn">' . _('Name') . '</th>
								<th class="SortedColumn">' . _('# Sales') . '</th>
								<th class="SortedColumn">' . _('% Data') . '</th>
								<th class="SortedColumn">' . _('% First') . '</th>
								<th class="SortedColumn">' . _('% Last') . '</th>
								<th class="SortedColumn">' . _('% Country') . '</th>
								<th class="SortedColumn">' . _('% DOB') . '</th>
								<th class="SortedColumn">' . _('% Email') . '</th>
								<th class="SortedColumn">' . _('% Sex') . '</th>
								<th class="SortedColumn">' . _('% VIP-PACK') . '</th>
							</tr>
						</thead>';
		echo $TableHeader;
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			// Fixed divide by zero errors by checking denominators before division
			$HarvestedPercent = ($MyRow['TotalOrders'] > 0) ? 
								($MyRow['Harvested'] / $MyRow['TotalOrders']) * 100 : 0;
			$FirstNamePercent = ($MyRow['Harvested'] > 0) ? 
								($MyRow['FirstNames'] / $MyRow['Harvested']) * 100 : 0;
			$LastNamePercent = ($MyRow['Harvested'] > 0) ? 
								($MyRow['LastNames'] / $MyRow['Harvested']) * 100 : 0;
			$CountriesPercent = ($MyRow['Harvested'] > 0) ? 
								($MyRow['Countries'] / $MyRow['Harvested']) * 100 : 0;
			$DOBPercent = ($MyRow['Harvested'] > 0) ? 
								($MyRow['Date_Of_Births'] / $MyRow['Harvested']) * 100 : 0;
			$EmailsPercent = ($MyRow['Harvested'] > 0) ? 
								($MyRow['Emails'] / $MyRow['Harvested']) * 100 : 0;
			$SexsPercent = ($MyRow['Harvested'] > 0) ? 
								($MyRow['Sexs'] / $MyRow['Harvested']) * 100 : 0;
			$VIPPercent = ($MyRow['TotalOrders'] > 0) ? 
								($MyRow['OnlineVipCards'] / $MyRow['TotalOrders']) * 100 : 0;
				
			echo '<tr class="striped_row">
				<td>' . $MyRow['salesperson'] . '</td>
				<td>' . $MyRow['salesmanname'] . '</td>
				<td class="number">' . locale_number_format($MyRow['TotalOrders'], 0) . '</td>
				<td class="number">' . locale_number_format($HarvestedPercent, 0) . '%' . '</td>
				<td class="number">' . locale_number_format($FirstNamePercent, 0) . '%' . '</td>
				<td class="number">' . locale_number_format($LastNamePercent, 0) . '%' . '</td>
				<td class="number">' . locale_number_format($CountriesPercent, 0) . '%' . '</td>
				<td class="number">' . locale_number_format($DOBPercent, 0) . '%' . '</td>
				<td class="number">' . locale_number_format($EmailsPercent, 0) . '%' . '</td>
				<td class="number">' . locale_number_format($SexsPercent, 0) . '%' . '</td>
				<td class="number">' . locale_number_format($VIPPercent, 0) . '%' . '</td>
				</tr>';
		}
		echo '</tbody></table></div></form>';
	}
}

function StockAdjustmentsByItemAndReason($Days, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$Days));

	$SQL = "SELECT stockmoves.stockid,
				stockadjustmentreasons.reasonname,
				SUM(qty) AS totaladjusted
			FROM stockmoves
			INNER JOIN stockadjustments
				ON stockmoves.transno = stockadjustments.transno
				AND stockmoves.type = 17
			INNER JOIN stockadjustmentreasons
				ON stockadjustments.reasonid = stockadjustmentreasons.reasonid
			WHERE stockmoves.trandate >= '" . $StartDate . "'
			GROUP BY stockmoves.stockid,
				stockadjustmentreasons.reasonid
			ORDER BY stockmoves.stockid,
				ABS(SUM(qty)) DESC";

	$TotalAdjusted = 0;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('# stock adjustments by Item and Reason during the last ') . $Days . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Item Code') . '</th>
						<th class="SortedColumn">' . _('Reason') . '</th>
						<th class="SortedColumn">' . _('Qty adjusted') . '</th>
						<th class="SortedColumn">' . _('Daily Average') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['reasonname'] . '</td>
					<td class="number">' . locale_number_format($MyRow['totaladjusted'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['totaladjusted'] / $Days, 1) . '</td>
				</tr>';

			$TotalAdjusted += $MyRow['totaladjusted'];
		}
	}
	echo '</tbody>
		<tfooter>';
	echo '<tr class="striped_row">
			<td>Total</td>
			<td></td>
			<td class="number">' . locale_number_format($TotalAdjusted, 0) . '</td>
			<td class="number">' . locale_number_format($TotalAdjusted / $Days, 1) . '</td>
		</tr>';
	
	echo '</tfooter>
		</table>
		</div>';
}	



?>
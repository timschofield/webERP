<?php
define("VERSIONFILE", "1.00");

include ('includes/session.php');
$Title = _('Kapal-Laut Quality and Returns Performance Board '. VERSIONFILE);
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

$PeriodNow=GetPeriod(Date($_SESSION['DefaultDateFormat']));

prnMsg("Performing Control Panel Section 02",'info');

if ($KL_SystemAdmin 
	OR $KL_BusinessDevelopmentManager){
	QualityIssuesByItem("QualityIssuesByItem", 90, $RootPath);
	$NumberOfTestExecuted++;
	QualityIssuesByItem("QualityIssuesByFamily", 90, $RootPath);
	$NumberOfTestExecuted++;
	QualityIssuesByItem("ChangeOfMindByFamily", 90, $RootPath);
	$NumberOfTestExecuted++;
}

prnMsg("Performed ". $NumberOfTestExecuted . " Quality and Returns tests",'success');

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include ('includes/footer.php');

function QualityIssuesByItem($Typereport, $numdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays+1));

	if ($Typereport == "QualityIssuesByItem"){
		$SQL = "SELECT itemcodes AS item, 
					COUNT(*) AS incidences,
					(SELECT SUM(salesorderdetails.qtyinvoiced)
						FROM salesorderdetails
						WHERE salesorderdetails.stkcode = returneditems.itemcodes
							AND salesorderdetails.completed = 1
							AND salesorderdetails.itemdue >= '". $StartDate . "') AS qtysold
				FROM returneditems
				WHERE (reasonid = 4 OR reasonid = 5)
					AND oldinvoicedate >= '". $StartDate . "'
				GROUP BY itemcodes";
		$TitleReport = 'Customer Quality Issues by items on the last ' . $numdays . ' days';
		$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Incidences') . '</th>
								<th class="SortedColumn">' . _('Qty Sold') . '</th>
								<th class="SortedColumn">' . _('%Incidences') . '</th>
							</tr>
						</thead>';
	}elseif ($Typereport == "QualityIssuesByFamily"){
		$SQL = "SELECT SUBSTRING(returneditems.itemcodes,1,2) AS item, 
						COUNT(*) AS incidences,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
						FROM salesorderdetails
						WHERE SUBSTRING(salesorderdetails.stkcode,1,2) = SUBSTRING(returneditems.itemcodes,1,2)
							AND salesorderdetails.completed = 1
							AND salesorderdetails.itemdue > '". $StartDate . "') AS qtysold
				FROM returneditems
				WHERE (returneditems.reasonid = 4 OR returneditems.reasonid = 5)
					AND returneditems.oldinvoicedate >= '". $StartDate . "'
				GROUP BY SUBSTRING(returneditems.itemcodes,1,2)";
		$TitleReport = 'Customer Quality Issues by Families of items on the last ' . $numdays . ' days';
		$TableHeader = '<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Family') . '</th>
								<th class="SortedColumn">' . _('Incidences') . '</th>
								<th class="SortedColumn">' . _('Qty Sold') . '</th>
								<th class="SortedColumn">' . _('%Incidences') . '</th>
							</tr>
						</thead>';
	}elseif ($Typereport == "ChangeOfMindByFamily"){
		$SQL = "SELECT SUBSTRING(returneditems.itemcodes,1,2) AS item, 
						COUNT(*) AS incidences,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
						FROM salesorderdetails
						WHERE SUBSTRING(salesorderdetails.stkcode,1,2) = SUBSTRING(returneditems.itemcodes,1,2)
							AND salesorderdetails.completed = 1
							AND salesorderdetails.itemdue > '". $StartDate . "') AS qtysold
				FROM returneditems
				WHERE (returneditems.reasonid = 1 OR returneditems.reasonid = 2 OR returneditems.reasonid = 3)
					AND returneditems.oldinvoicedate >= '". $StartDate . "'
				GROUP BY SUBSTRING(returneditems.itemcodes,1,2)";
		$TitleReport = 'Change Of Mind by Families of items on the last ' . $numdays . ' days';
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
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $TitleReport;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		echo $TableHeader;
		echo '<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$PercentIncidences = ($MyRow['qtysold'] != 0) ? $MyRow['incidences']/$MyRow['qtysold'] : 0;
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$MyRow['item'],
					locale_number_format($MyRow['incidences'],0),
					locale_number_format($MyRow['qtysold'],0),
					locale_number_format($PercentIncidences*100,1).'%'
					);
			$i++;
		}
		echo '</tbody></table></div>';
	}
}

/*
function ReturnsBySPG($SPG, $NumDays){

	$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

	if ($SPG != "ALL"){
		$WhereSPG = " AND salesman.salesmancode = " . $SPG . " ";
	}else{
		$WhereSPG = " ";
	}
	$SQL = "SELECT salesorders.salesperson,
				salesman.salesmanname,
				(SELECT COUNT(*)
					FROM returneditems,
					WHERE returneditems.returndate >= '". $StartDate ."'
						AND so2.salesperson = salesorders.salesperson) AS totalorders,
				(SELECT SUM(qtyinvoiced)
					FROM salesorderdetails, salesorders AS so2
					WHERE salesorderdetails.orderno = so2.orderno
						AND so2.orddate >= '". $StartDate ."'
						AND so2.orddate <= '". $Yesterday ."'
						AND so2.salesperson = salesorders.salesperson
						AND salesorderdetails.stkcode = 'ONLINE-VIP-PACK') AS onlinevipcards 
			FROM salesorders, salesman
			WHERE salesman.salesmancode = salesorders.salesperson " . 
				$WhereSPG . "
				AND salesorders.orddate >= '". $StartDate ."'
				AND salesorders.orddate <= '". $Yesterday ."'
			GROUP BY salesorders.salesperson
			ORDER BY salesorders.salesperson";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _ _('Quality data Retail Customer by SPG during the last ') . locale_number_format($NumDays,0) . ' days';
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
			printf('<tr class="striped_row">
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
				</tr>', 
				$MyRow['salesperson'],
				$MyRow['salesmanname'],
				locale_number_format($MyRow['totalorders'],0),
				locale_number_format(($MyRow['harvested']/$MyRow['totalorders'])*100,0).'%',
				locale_number_format(($MyRow['firstnames']/$MyRow['harvested'])*100,0).'%',
				locale_number_format(($MyRow['lastnames']/$MyRow['harvested'])*100,0).'%',
				locale_number_format(($MyRow['countries']/$MyRow['harvested'])*100,0).'%',
				locale_number_format(($MyRow['date_of_births']/$MyRow['harvested'])*100,0).'%',
				locale_number_format(($MyRow['emails']/$MyRow['harvested'])*100,0).'%',
				locale_number_format(($MyRow['sexs']/$MyRow['harvested'])*100,0).'%',
				locale_number_format(($MyRow['onlinevipcards']/$MyRow['totalorders'])*100,0).'%'
				);
		}
		echo '</tbody></table></div></form>';
	}
}
*/
?>
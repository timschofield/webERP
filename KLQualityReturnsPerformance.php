<?php
define("VERSIONFILE", "1.00");

include ('includes/session.php');
$Title = _('Kapal-Laut Quality and Returns Performance Board '. VERSIONFILE);
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

/* ASSIGN users to groups */
include ('includes/KLRoles.php');
$begintime = time_start();
$NumberOfTestExecuted = 0;

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']));

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
time_finish($begintime);

include ('includes/footer.php');

function QualityIssuesByItem($typereport, $numdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays+1));

	if ($typereport == "QualityIssuesByItem"){
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
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Incidences') . '</th>
							<th class="ascending">' . _('Qty Sold') . '</th>
							<th class="ascending">' . _('%Incidences') . '</th>
						</tr>';
	}elseif ($typereport == "QualityIssuesByFamily"){
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
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Family') . '</th>
							<th class="ascending">' . _('Incidences') . '</th>
							<th class="ascending">' . _('Qty Sold') . '</th>
							<th class="ascending">' . _('%Incidences') . '</th>
						</tr>';
	}elseif ($typereport == "ChangeOfMindByFamily"){
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
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Family') . '</th>
							<th class="ascending">' . _('Incidences') . '</th>
							<th class="ascending">' . _('Qty Sold') . '</th>
							<th class="ascending">' . _('%Incidences') . '</th>
						</tr>';
	}					
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $TitleReport . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$PercentIncidences = ($myrow['qtysold'] != 0) ? $myrow['incidences']/$myrow['qtysold'] : 0;
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$myrow['item'],
					locale_number_format($myrow['incidences'],0),
					locale_number_format($myrow['qtysold'],0),
					locale_number_format($PercentIncidences*100,1).'%'
					);
			$i++;
		}
		echo '</table>
				</div>';
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
	$result = DB_query($SQL);
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Quality data Retail Customer by SPG during the last ') . locale_number_format($NumDays,0) . ' days</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('SPG') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('# Sales') . '</th>
							<th class="ascending">' . _('% Data') . '</th>
							<th class="ascending">' . _('% First') . '</th>
							<th class="ascending">' . _('% Last') . '</th>
							<th class="ascending">' . _('% Country') . '</th>
							<th class="ascending">' . _('% DOB') . '</th>
							<th class="ascending">' . _('% Email') . '</th>
							<th class="ascending">' . _('% Sex') . '</th>
							<th class="ascending">' . _('% VIP-PACK') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter

		while ($myrow = DB_fetch_array($result)) {
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
				</tr>', 
				$myrow['salesperson'],
				$myrow['salesmanname'],
				locale_number_format($myrow['totalorders'],0),
				locale_number_format(($myrow['harvested']/$myrow['totalorders'])*100,0).'%',
				locale_number_format(($myrow['firstnames']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['lastnames']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['countries']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['date_of_births']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['emails']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['sexs']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['onlinevipcards']/$myrow['totalorders'])*100,0).'%'
				);
		}
		echo '</table>
			</div>
			</form>';
	}
}
*/

?>
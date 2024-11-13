<?php
define("VERSIONFILE", "2.10"); // 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Kapal-Laut SPG Control Board '. VERSIONFILE);
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include ('includes/KLRetailCustomer.php');

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']));

/***************************************************************************************
* SPG identification         
***************************************************************************************/

AverageSPGSales($_SESSION['SalesmanLogin'], 90, 60, 30, 15, $db);
SPGTypePayments($_SESSION['SalesmanLogin'], 15, $db);
lastSalesSPG($_SESSION['SalesmanLogin'], 3, $db);
RetailCustomerDataQualitySPG($_SESSION['SalesmanLogin'], 15, $db);

prnMsg("Performed 4 SPG control board tests",'success');

include ('includes/footer.php');

/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/
/*      FUNCTIONS ASSOCIATED
/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/
function AverageSPGSales($SPG, $NumDaysA, $NumDaysB, $NumDaysC, $NumDaysD, $db){
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysB-1));
	$StartDateC = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysC-1));
	$StartDateD = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysD-1));
	$StartDateMTD=FormatDateForSQL(Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y'))));

	$SQL = "SELECT salesmancode,
				salesmanname,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesA,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateB . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesB,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateC . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesC,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateD . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesD,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateMTD . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesMTD
			FROM salesman
			WHERE salesman.salesmancode = '" . $SPG . "'";
						
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Average Daily sales by SPG during the last ') . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . " days.".'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' .  _('Code') . '</th>
							<th>' . _('Name') . '</th>
							<th>' . $NumDaysA . _(' days') . '</th>
							<th>' . $NumDaysB . _(' days') . '</th>
							<th>' . $NumDaysC . _(' days') . '</th>
							<th>' . $NumDaysD . _(' days') . '</th>
							<th>' . _('MTD') . '</th>
							<th>' . _('Trend') . '</th>
							<th>' . 'Forecast '. $NumDaysC . _(' days') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			$Code = $myrow['salesmancode'];
			$Name = $myrow['salesmanname'];
			
			$dailyA = locale_number_format(($myrow['salesA']/$NumDaysA),0);
			$dailyB = locale_number_format(($myrow['salesB']/$NumDaysB),0);
			$dailyC = locale_number_format(($myrow['salesC']/$NumDaysC),0);
			$dailyD = locale_number_format(($myrow['salesD']/$NumDaysD),0);
			$percent = (($myrow['salesD']/$NumDaysD)-($myrow['salesC']/$NumDaysC))/($myrow['salesC']/$NumDaysC) * 100;
			$trend = " ";
			if ($percent > MINIMUM_AVERAGE_SALES_TREND){
				$trend = "Improving ". locale_number_format($percent,0) . "%";
			}
			if ($percent < -MINIMUM_AVERAGE_SALES_TREND){
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
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i,
					$Code,
					$Name,
					$dailyA, 
					$dailyB, 
					$dailyC,
					$dailyD,
					$MTD,
					$trend,
					$forecast
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

function SPGTypePayments($SPG, $maxdays, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$totalcash = 0;
	$totalcredit = 0;
	$totalreturned = 0;
	$totalvouchers = 0;
	$total = 0;

	$SQL = "SELECT salesorders.salesperson AS reportunit, 
				salesman.salesmanname AS reportname,
				SUM(salesorders.klpaidcash) AS cashshop, 
				SUM(salesorders.klpaidcreditcard) AS creditshop, 
				SUM(salesorders.klreturnedgoods) AS returnedgoodsshop,
				SUM(salesorders.klvouchers) AS vouchersshop,
				SUM(salesorders.klpaidcash+salesorders.klpaidcreditcard) AS totalshop
		FROM salesorders, salesman, debtorsmaster
		WHERE salesorders.salesperson = salesman.salesmancode
			AND salesorders.debtorno = debtorsmaster.debtorno
			AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			AND salesman.salesmancode = '" . $SPG . "'
			AND salesorders.orddate >= '". $StartDate. "'";
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Distribution Cash / Credit Card during the last ') . $maxdays . _(' days by SPG') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('Code') . '</th>
							<th>' . _('Name') . '</th>
							<th>' . _('% Cash') . '</th>
							<th>' . _('% Credit') . '</th>
							<th>' . _('% Returns') . '</th>
							<th>' . _('% Vouchers') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['totalshop'] != 0){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				
				$percentcash = locale_number_format(($myrow['cashshop']/$myrow['totalshop'])*100,1);
				$percentcredit = locale_number_format(($myrow['creditshop']/$myrow['totalshop'])*100,1);
				$percentreturns = locale_number_format(($myrow['returnedgoodsshop']/$myrow['totalshop'])*100,1);
				$percentvouchers = locale_number_format(($myrow['vouchersshop']/$myrow['totalshop'])*100,1);
				
				$totalcash = $totalcash + $myrow['cashshop'];
				$totalcredit = $totalcredit + $myrow['creditshop'];
				$totalreturned = $totalreturned + $myrow['returnedgoodsshop'];
				$totalvouchers = $totalvouchers + $myrow['vouchersshop'];
				$total = $total + $myrow['totalshop'];
				
				printf('<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$myrow['reportunit'],
						$myrow['reportname'],
						$percentcash, 
						$percentcredit, 
						$percentreturns, 
						$percentvouchers
						);
				$i++;
			}
		}
		echo '</table>
		</div>
		</form>';

	}
}

function lastSalesSPG($spg, $NumDaysA, $db){
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));
	
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				salesorders.orddate,
				salesorders.klpaidcash,
				salesorders.klpaidcreditcard,
				salesorders.klreturnedgoods,
				salesorders.klvouchers
			FROM salesorders
			WHERE salesorders.salesperson = '". $spg ."'
				AND salesorders.orddate >= '". $StartDateA . "'
			ORDER BY salesorders.orderno DESC";
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>Sales of the last ' . $NumDaysA . ' days for SPG ' . $spg . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('webERP#') . '</th>
							<th>' . _('Yellow#') . '</th>
							<th>' . _('Date') . '</th>
							<th>' . _('Cash') . '</th>
							<th>' . _('Credit Card') . '</th>
							<th>' . _('Returned Goods') . '</th>
							<th>' . _('Vouchers') . '</th>
							<th>' . _('Total') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			$total = $myrow['klpaidcash'] + 
					$myrow['klpaidcreditcard'];
					
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($myrow['orderno'],0),
					$myrow['customerref'],
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['klpaidcash'],0),
					locale_number_format($myrow['klpaidcreditcard'],0),
					locale_number_format($myrow['klreturnedgoods'],0),
					locale_number_format($myrow['klvouchers'],0),
					locale_number_format($total,0)
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}


?>
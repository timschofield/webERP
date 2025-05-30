<?php

include ('includes/session.php');
$Title = _('KL SPG Control Board');
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include ('includes/KLRetailCustomer.php');
include('includes/KLUIGeneralFunctions.php');

$PeriodNow=GetPeriod(Date($_SESSION['DefaultDateFormat']));

/***************************************************************************************
* SPG identification         
***************************************************************************************/

AverageSPGSales($_SESSION['SalesmanLogin'], 90, 60, 30, 15);
SPGTypePayments($_SESSION['SalesmanLogin'], 15);
lastSalesSPG($_SESSION['SalesmanLogin'], 3);
RetailCustomerDataQualitySPG($_SESSION['SalesmanLogin'], 15);

prnMsg("Performed 4 SPG control board tests",'success');

include ('includes/footer.php');

/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/
/*      FUNCTIONS ASSOCIATED
/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/
function AverageSPGSales($SPG, $NumDaysA, $NumDaysB, $NumDaysC, $NumDaysD){
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
						
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Average Daily sales by SPG during the last ') . $NumDaysA . ", ". $NumDaysB . ", ". $NumDaysC . ", ". $NumDaysD . " days.";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' .  _('Code') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . $NumDaysA . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysB . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysC . _(' days') . '</th>
						<th class="SortedColumn">' . $NumDaysD . _(' days') . '</th>
						<th class="SortedColumn">' . _('MTD') . '</th>
						<th class="SortedColumn">' . _('Trend') . '</th>
						<th class="SortedColumn">' . 'Forecast '. $NumDaysC . _(' days') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			$Code = $MyRow['salesmancode'];
			$Name = $MyRow['salesmanname'];
			
			$dailyA = locale_number_format(($MyRow['salesA']/$NumDaysA),0);
			$dailyB = locale_number_format(($MyRow['salesB']/$NumDaysB),0);
			$dailyC = locale_number_format(($MyRow['salesC']/$NumDaysC),0);
			$dailyD = locale_number_format(($MyRow['salesD']/$NumDaysD),0);
			$Percent = (($MyRow['salesD']/$NumDaysD)-($MyRow['salesC']/$NumDaysC))/($MyRow['salesC']/$NumDaysC) * 100;
			$Trend = " ";
			if ($Percent > MINIMUM_AVERAGE_SALES_TREND){
				$Trend = "Improving ". locale_number_format($Percent,0) . "%";
			}
			if ($Percent < -MINIMUM_AVERAGE_SALES_TREND){
				$Trend = "Degrading ". locale_number_format($Percent,0) . "%";
			}
			$Forecast = locale_number_format(round($MyRow['salesC'], -5),0);
			$MTD = locale_number_format($MyRow['salesMTD'], 0);
			
			echo '<tr class="striped_row">
					<td>' . $i . '</td>
					<td>' . $Code . '</td>
					<td>' . $Name . '</td>
					<td class="number">' . $dailyA . '</td>
					<td class="number">' . $dailyB . '</td>
					<td class="number">' . $dailyC . '</td>
					<td class="number">' . $dailyD . '</td>
					<td class="number">' . $MTD . '</td>
					<td>' . $Trend . '</td>
					<td class="number">' . $Forecast . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

function SPGTypePayments($SPG, $maxdays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$Totalcash = 0;
	$Totalcredit = 0;
	$Totalreturned = 0;
	$Totalvouchers = 0;
	$Total = 0;

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
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Distribution Cash / Credit Card during the last ') . $maxdays . _(' days by SPG');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('% Cash') . '</th>
						<th class="SortedColumn">' . _('% Credit') . '</th>
						<th class="SortedColumn">' . _('% Returns') . '</th>
						<th class="SortedColumn">' . _('% Vouchers') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['totalshop'] != 0){
				$Percentcash = locale_number_format(($MyRow['cashshop']/$MyRow['totalshop'])*100,1);
				$Percentcredit = locale_number_format(($MyRow['creditshop']/$MyRow['totalshop'])*100,1);
				$Percentreturns = locale_number_format(($MyRow['returnedgoodsshop']/$MyRow['totalshop'])*100,1);
				$Percentvouchers = locale_number_format(($MyRow['vouchersshop']/$MyRow['totalshop'])*100,1);
				
				$Totalcash = $Totalcash + $MyRow['cashshop'];
				$Totalcredit = $Totalcredit + $MyRow['creditshop'];
				$Totalreturned = $Totalreturned + $MyRow['returnedgoodsshop'];
				$Totalvouchers = $Totalvouchers + $MyRow['vouchersshop'];
				$Total = $Total + $MyRow['totalshop'];
				
				echo '<tr class="striped_row">
						<td>' . $MyRow['reportunit'] . '</td>
						<td>' . $MyRow['reportname'] . '</td>
						<td class="number">' . $Percentcash . '</td>
						<td class="number">' . $Percentcredit . '</td>
						<td class="number">' . $Percentreturns . '</td>
						<td class="number">' . $Percentvouchers . '</td>
						</tr>';
				$i++;
			}
		}
		echo '</tbody>
			</table>
			</div>
			</form>';

	}
}

function lastSalesSPG($spg, $NumDaysA){
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
	
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'Sales of the last ' . $NumDaysA . ' days for SPG ' . $spg;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('webERP#') . '</th>
						<th class="SortedColumn">' . _('Yellow#') . '</th>
						<th class="SortedColumn">' . _('Date') . '</th>
						<th class="SortedColumn">' . _('Cash') . '</th>
						<th class="SortedColumn">' . _('Credit Card') . '</th>
						<th class="SortedColumn">' . _('Returned Goods') . '</th>
						<th class="SortedColumn">' . _('Vouchers') . '</th>
						<th class="SortedColumn">' . _('Total') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$Total = $MyRow['klpaidcash'] + 
					$MyRow['klpaidcreditcard'];
					
			echo '<tr class="striped_row">
					<td class="number">' . locale_number_format($MyRow['orderno'],0) . '</td>
					<td class="number">' . $MyRow['customerref'] . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['klpaidcash'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['klpaidcreditcard'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['klreturnedgoods'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['klvouchers'],0) . '</td>
					<td class="number">' . locale_number_format($Total,0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

?>
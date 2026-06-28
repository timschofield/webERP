<?php

/******************************************************************************************************/
/*	  FUNCTIONS LIST (In alphabetical order)
/******************************************************************************************************/
/*
AverageCustomerBehaviourByValueInvoice - Analyzes customer invoice behavior by value ranges for a specific brand
CashStatus - Manages and displays cash status for different entities (ADU, SMH, BB) with related metrics
DailySalesRecords - Shows top sales days for a given time period
EmptyAccountsGLTransTX - Finds transactions with empty account codes in GL transactions
GeneralCustomerBehaviour - Analyzes general customer behavior metrics for a brand
PeriodDifferenceSales - Compares sales between different time periods
PettyCashStatus - Displays petty cash status for a specific currency
ShowKPIHistory - Shows Key Performance Indicators history for specified days
StockByBrand - Analyzes stock levels and requirements by brand
UnbalancedGLTransTX - Detects unbalanced GL transactions
*/


/**************************************************************************************************************
* AverageCustomerBehaviourByValueInvoice
*
* Analyzes and displays customer invoice behavior by value ranges for a specific brand
* 
* @param string $Typereport - Type of report (e.g., "Shop")
* @param string $Brand - Brand code to analyze
* @param int $NumDaysA - Number of days to analyze
* 
* @return void - Outputs HTML table and inserts KPI values
**************************************************************************************************************/
function AverageCustomerBehaviourByValueInvoice($Typereport, $Brand, $NumDaysA){
	/* EXPLAIN SQL 2014-05-21	*/
	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));

	$BrandCode = substr($Brand, -2); // Get the 2 rightmost characters of $Brand

	if ($Typereport == "Shop"){
		$BrandText= BrandTextFromCode($Brand);
		if ($BrandCode == "KL") {
			$AverageInvoiceValue01 = $_SESSION['AverageInvoiceValueKL01'];
			$AverageInvoiceValue02 = $_SESSION['AverageInvoiceValueKL02'];
			$AverageInvoiceValue03 = $_SESSION['AverageInvoiceValueKL03'];
			$AverageInvoiceValue04 = $_SESSION['AverageInvoiceValueKL04'];
			$AverageInvoiceValue05 = $_SESSION['AverageInvoiceValueKL05'];
			$AverageInvoiceValue06 = $_SESSION['AverageInvoiceValueKL06'];
			$AverageInvoiceValue07 = $_SESSION['AverageInvoiceValueKL07'];
			$AverageInvoiceValue08 = $_SESSION['AverageInvoiceValueKL08'];
		} elseif ($BrandCode == "BL") {
			$AverageInvoiceValue01 = $_SESSION['AverageInvoiceValueBlink01'];
			$AverageInvoiceValue02 = $_SESSION['AverageInvoiceValueBlink02'];
			$AverageInvoiceValue03 = $_SESSION['AverageInvoiceValueBlink03'];
			$AverageInvoiceValue04 = $_SESSION['AverageInvoiceValueBlink04'];
			$AverageInvoiceValue05 = $_SESSION['AverageInvoiceValueBlink05'];
			$AverageInvoiceValue06 = $_SESSION['AverageInvoiceValueBlink06'];
			$AverageInvoiceValue07 = $_SESSION['AverageInvoiceValueBlink07'];
			$AverageInvoiceValue08 = $_SESSION['AverageInvoiceValueBlink08'];
		} else {
			return;
		}
		$SQL = "SELECT dm.debtorno,
					dm.name,
					so_stats.invoicesum,
					so_stats.invoicecount,
					so_stats.invoice01,
					so_stats.invoice02,
					so_stats.invoice03,
					so_stats.invoice04,
					so_stats.invoice05,
					so_stats.invoice06,
					so_stats.invoice07,
					so_stats.invoice08,
					so_stats.invoice09
				FROM debtorsmaster dm
				INNER JOIN custbranch cb ON dm.debtorno = cb.debtorno
				INNER JOIN locations loc ON cb.defaultlocation = loc.loccode
				LEFT JOIN (
					SELECT so.debtorno,
						SUM(so.klpaidcash + so.klpaidcreditcard) AS invoicesum,
						COUNT(DISTINCT(so.orderno)) AS invoicecount,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) <= " . $AverageInvoiceValue01 . " THEN 1 ELSE 0 END) AS invoice01,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) > " . $AverageInvoiceValue01 . " AND (so.klpaidcash + so.klpaidcreditcard) <= " . $AverageInvoiceValue02 . " THEN 1 ELSE 0 END) AS invoice02,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) > " . $AverageInvoiceValue02 . " AND (so.klpaidcash + so.klpaidcreditcard) <= " . $AverageInvoiceValue03 . " THEN 1 ELSE 0 END) AS invoice03,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) > " . $AverageInvoiceValue03 . " AND (so.klpaidcash + so.klpaidcreditcard) <= " . $AverageInvoiceValue04 . " THEN 1 ELSE 0 END) AS invoice04,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) > " . $AverageInvoiceValue04 . " AND (so.klpaidcash + so.klpaidcreditcard) <= " . $AverageInvoiceValue05 . " THEN 1 ELSE 0 END) AS invoice05,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) > " . $AverageInvoiceValue05 . " AND (so.klpaidcash + so.klpaidcreditcard) <= " . $AverageInvoiceValue06 . " THEN 1 ELSE 0 END) AS invoice06,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) > " . $AverageInvoiceValue06 . " AND (so.klpaidcash + so.klpaidcreditcard) <= " . $AverageInvoiceValue07 . " THEN 1 ELSE 0 END) AS invoice07,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) > " . $AverageInvoiceValue07 . " AND (so.klpaidcash + so.klpaidcreditcard) <= " . $AverageInvoiceValue08 . " THEN 1 ELSE 0 END) AS invoice08,
						SUM(CASE WHEN (so.klpaidcash + so.klpaidcreditcard) > " . $AverageInvoiceValue08 . " THEN 1 ELSE 0 END) AS invoice09
					FROM salesorders so
					WHERE so.orddate >= '" . $StartDateA . "'
						AND so.orddate <= '" . $YesterdayA . "'
					GROUP BY so.debtorno
				) so_stats ON dm.debtorno = so_stats.debtorno
				WHERE dm.typeid = 2
					AND loc.typeloc = '" . $Brand . "'
				ORDER BY so_stats.invoicecount DESC";
	} else {
		return;
	}
	
	$SumInvoiceSum   = 0;
	$SumInvoiceCount = 0;
	$SumInvoice01	= 0;
	$SumInvoice02	= 0;
	$SumInvoice03	= 0;
	$SumInvoice04	= 0;
	$SumInvoice05	= 0;
	$SumInvoice06	= 0;
	$SumInvoice07	= 0;
	$SumInvoice08	= 0;
	$SumInvoice09	= 0;
						
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Average invoice value by ') . $BrandText . " " . $Typereport . " during the last " . $NumDaysA . " days.";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . $Typereport . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . __('IDR/Invoice') . '</th>
						<th class="SortedColumn">' . __('# Invoice/Day') . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format($AverageInvoiceValue01,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format($AverageInvoiceValue02,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format($AverageInvoiceValue03,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format($AverageInvoiceValue04,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format($AverageInvoiceValue05,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format($AverageInvoiceValue06,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format($AverageInvoiceValue07,0) . '</th>
						<th class="SortedColumn">' . '<='. locale_number_format($AverageInvoiceValue08,0) . '</th>
						<th class="SortedColumn">' . '>'. locale_number_format($AverageInvoiceValue08,0) . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {

			if ($Typereport == "Shop"){
				$Code = $MyRow['debtorno'];
				$Name = $MyRow['name'];
			} else {
				return;
			}
			
			if ($MyRow['invoicesum'] > 0){
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $Code . '</td>
						<td>' . $Name . '</td>
						<td class="number">' . locale_number_format($MyRow['invoicesum']/$MyRow['invoicecount'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['invoicecount']/$NumDaysA,1) . '</td>
						<td class="number">' . locale_number_format($MyRow['invoice01']/$MyRow['invoicecount']*100,1) . '%</td>
						<td class="number">' . locale_number_format($MyRow['invoice02']/$MyRow['invoicecount']*100,1) . '%</td>
						<td class="number">' . locale_number_format($MyRow['invoice03']/$MyRow['invoicecount']*100,1) . '%</td>
						<td class="number">' . locale_number_format($MyRow['invoice04']/$MyRow['invoicecount']*100,1) . '%</td>
						<td class="number">' . locale_number_format($MyRow['invoice05']/$MyRow['invoicecount']*100,1) . '%</td>
						<td class="number">' . locale_number_format($MyRow['invoice06']/$MyRow['invoicecount']*100,1) . '%</td>
						<td class="number">' . locale_number_format($MyRow['invoice07']/$MyRow['invoicecount']*100,1) . '%</td>
						<td class="number">' . locale_number_format($MyRow['invoice08']/$MyRow['invoicecount']*100,1) . '%</td>
						<td class="number">' . locale_number_format($MyRow['invoice09']/$MyRow['invoicecount']*100,1) . '%</td>
						</tr>';
			}
			$i++;
			$SumInvoiceSum   += $MyRow['invoicesum'];
			$SumInvoiceCount += $MyRow['invoicecount'] ;
			$SumInvoice01	+= $MyRow['invoice01'];
			$SumInvoice02	+= $MyRow['invoice02'];
			$SumInvoice03	+= $MyRow['invoice03'];
			$SumInvoice04	+= $MyRow['invoice04'];
			$SumInvoice05	+= $MyRow['invoice05'];
			$SumInvoice06	+= $MyRow['invoice06'];
			$SumInvoice07	+= $MyRow['invoice07'];
			$SumInvoice08	+= $MyRow['invoice08'];
			$SumInvoice09	+= $MyRow['invoice09'];
		}
		echo '<tr class="striped_row">
				<td class="number"></td>
				<td></td>
				<td>TOTAL</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoiceSum/$SumInvoiceCount,0) . '</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoiceCount/$NumDaysA,1) . '</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice01/$SumInvoiceCount*100,1) . '%</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice02/$SumInvoiceCount*100,1) . '%</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice03/$SumInvoiceCount*100,1) . '%</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice04/$SumInvoiceCount*100,1) . '%</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice05/$SumInvoiceCount*100,1) . '%</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice06/$SumInvoiceCount*100,1) . '%</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice07/$SumInvoiceCount*100,1) . '%</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice08/$SumInvoiceCount*100,1) . '%</td>
				<td class="number">' . locale_number_format_zero_blank($SumInvoice09/$SumInvoiceCount*100,1) . '%</td>
				</tr>';
		echo '</tbody></table>
				</div>';
		InsertKPI("INV-AV-INV-VALUE-" . $NumDaysA . "-IDR-" . $BrandCode, $SumInvoiceSum/$SumInvoiceCount);
		InsertKPI("INV-AV-INV-NUMBER-" . $NumDaysA . "-INV-" . $BrandCode, $SumInvoiceCount/$NumDaysA);
	}
}

/**************************************************************************************************************
* CashStatus
*
* Analyzes and displays cash status for different entities (ADU, SMH, BB) along with USD calculations
* 
* @param int $Year - The year to analyze
* @param float $CashEndOfPreviousYearADU - Previous year-end cash balance for ADU
* @param float $YearlyGoalADU - Cash goal for ADU by end of year
* @param float $MinTransferADU - Minimum transfer amount for ADU
* @param float $CashEndOfPreviousYearSMH - Previous year-end cash balance for SMH
* @param float $YearlyGoalSMH - Cash goal for SMH by end of year
* @param float $MinTransferSMH - Minimum transfer amount for SMH
* @param float $CashEndOfPreviousYearBB - Previous year-end cash balance for BB
* @param float $YearlyGoalBB - Cash goal for BB by end of year
* @param float $MinTransferBB - Minimum transfer amount for BB
* @param float $MinMoveFree - Minimum amount for free cash movements
* @param int $USDPODaysSchedule - Days schedule for USD purchase orders
* @param float $USDSafetyFactor - Safety factor for USD calculations
* @param float $USDMinPurchase - Minimum USD purchase amount
* @param float $USDMaxEasyPurchasePerMonth - Maximum easy purchase USD per month
* @param float $SaldoADUGlobalUSDMax - Maximum global USD balance for ADU
* @param float $SaldoADUDanamonUSDMin - Minimum Danamon USD balance for ADU
* @param float $SaldoADUDanamonUSDMax - Maximum Danamon USD balance for ADU
* @param float $SaldoADUPayoneerUSDMin - Minimum Payoneer USD balance for ADU
* @param float $SaldoADUPayoneerUSDMax - Maximum Payoneer USD balance for ADU
* @param int $Period - Period number for accounting
* @param bool $AdminRole - Whether user has admin role
*
* @return void - Outputs HTML tables and inserts KPI values
**************************************************************************************************************/
function CashStatus($Year, 	
					$CashEndOfPreviousYearADU, 
					$YearlyGoalADU, 
					$MinTransferADU, 
					$CashEndOfPreviousYearSMH, 
					$YearlyGoalSMH, 
					$MinTransferSMH, 
					$CashEndOfPreviousYearBB, 
					$YearlyGoalBB, 
					$MinTransferBB, 
					$MinMoveFree, 
					$USDPODaysSchedule,
					$USDSafetyFactor,
					$USDMinPurchase,
					$USDMaxEasyPurchasePerMonth,
					$SaldoADUDanamonUSDMin,
					$SaldoADUPayoneerUSDMin,
					$SaldoADUPayoneerUSDMax,
					$Period, 
					$AdminRole){

	// Consider all year, not until today as some tx are reported into the future
	$EndOfYear = $Year . "-12-31";
	$StartDateYTD = $Year . "-01-01";
	$Today = date('Y-m-d');
	$FirstDateOfMonth = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));

	$LastDateOfMonth = EndDateSQLFromPeriodNo($Period);
	$DaysUntilEndOfMonth = DaysBetween($Today, $LastDateOfMonth)+1;

	////////////////////////////////////////////////////////
	// CASH STATUS ADU IDR CALCULATIONS
	////////////////////////////////////////////////////////

	// Sales Cash PT ADU during the year
	$Account = "410000000AD";
	$SalesCashADU = -GetGLAccountValueBetweenTwoDates($Account, "ALL", $StartDateYTD, $EndOfYear);

	// Cash sales still floating (still not received in kantor)
	$FloatingCashADU = GetCashSalesValueStillFloating('PTADU', $StartDateYTD, $EndOfYear);
	
	// Cash Danamon IDR PTADU to Cash Kantor
	$Account = "111121105AD";
	$BankToCashADU = -GetGLAccountValueBetweenTwoDates($Account, "TO_CASH_KANTOR", $StartDateYTD, $EndOfYear);

	// Expenses ADU Paid by Petty Cash (excluding salaries, Corporate CC)
	$AccountSuffix = "AD";
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $StartDateYTD . "'
				AND pcashdetails.date <= '" . $EndOfYear . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%CEK ADU'
				AND pctabs.tabcode NOT LIKE 'CC-DANAMON%'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%".$AccountSuffix."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ExpensesADUPaidCash = -($MyRow[0] ?? 0);
	
	// Cash in Kantor to Small Suppliers PTADU
	$Account = "510010070AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH%'
					OR gltrans.narrative LIKE '%KANTOR%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToSmallSuppliersADU = $MyRow[0] ?? 0;

	// Cash in Kantor to Pay rents PTADU
	$Account = "211030200AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToRentADU = $MyRow[0] ?? 0;

	// Cash in Kantor to Pay dividends PTADU
	$Account = "614012400AD";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToDividendsADU = $MyRow[0] ?? 0;

	$CurrentBalanceADU = $CashEndOfPreviousYearADU
						+$SalesCashADU
						+$BankToCashADU
						-$FloatingCashADU
						-$ExpensesADUPaidCash
						-$CashToSmallSuppliersADU
						-$CashToRentADU
						-$CashToDividendsADU;
	$ToBeMovedADU = $CurrentBalanceADU-$YearlyGoalADU ;
	$ToBeTransferredADU = round_multiple_of($ToBeMovedADU, $MinTransferADU);

	////////////////////////////////////////////////////////
	// CASH STATUS ADU IDR SHOW TABLE
	////////////////////////////////////////////////////////

	$TableTitleText = __('Status Cash IDR PT. Angin Dingin Utara ' . $Year);
	ShowTableTitle($TableTitleText);

	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . 'Concept' . '</th>
					<th>' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	echo '<tr>
			<td>Cash ADU in Brankas Kantor end of ' . ($Year-1) . '</td>
			<td class="number">' . locale_number_format($CashEndOfPreviousYearADU,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Sales Retail PT ADU Cash during '. $Year . '</td>
			<td class="number">' . locale_number_format($SalesCashADU,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Floating Cash still in shops PT ADU</td>
			<td class="number">' . locale_number_format(-$FloatingCashADU,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Cash received from shops PT ADU in Brankas Kantor during '. $Year . '</td>
			<td class="number">' . locale_number_format($SalesCashADU-$FloatingCashADU,0) . '</td>
			</tr>';
	if ($BankToCashADU >= 0){
		$Text = 'Total withdrawal from Danamon IDR PTADU to Brankas Kantor';
	} else {
		$Text = 'Total deposit from Brankas Kantor to Danamon IDR PTADU ';
	}
	echo '<tr>
			<td>' . $Text . '</td>
			<td class="number">' . locale_number_format($BankToCashADU,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PT ADU Paid by Petty Cash (excluding checks, salaries, Corporate CC)</td>
			<td class="number">' . locale_number_format(-$ExpensesADUPaidCash,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PT ADU Small Suppliers Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToSmallSuppliersADU,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PT ADU Rent Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToRentADU,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Dividends PT ADU Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToDividendsADU,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Current Cash PT ADU in Brankas Kantor</td>
			<td class="number">' . locale_number_format($CurrentBalanceADU,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Cash ADU in Brankas Kantor Goal for end of '. $Year . '</td>
			<td class="number">' . locale_number_format($YearlyGoalADU,0) . '</td>
			</tr>';
	if ($ToBeMovedADU >= 0){
		$Text = 'Cash ADU OVER goal in Brankas Kantor';
	} else {
		$Text = 'Cash ADU BELOW goal in Brankas Kantor';
	}
	echo '<tr>
			<td>' . $Text . '</td>
			<td class="number">' . locale_number_format(abs($ToBeMovedADU),0) . '</td>
			</tr>';
			
	if ($ToBeTransferredADU != 0){
		if ($ToBeTransferredADU > 0){
			$Text = 'ACTION NEEDED -> Deposit from Brankas Kantor to Danamon IDR ADU';
		} elseif ($ToBeTransferredADU < 0){
			$Text = 'ACTION NEEDED -> Withdrawal from Danamon IDR ADU to Brankas Kantor';
		}
		echo '<tr class="striped_row">
				<td>' . $Text . '</td>
				<td class="number">' . locale_number_format(abs($ToBeTransferredADU),0) . '</td>
				</tr>';
	}
	echo '</tbody></table>
		</div>';
	
	////////////////////////////////////////////////////////
	// CASH STATUS ADU USD CALCULATIONS
	////////////////////////////////////////////////////////

	$SQL = "SELECT rate
			FROM currencies
			WHERE currabrev = 'USD'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CurrentUSDRate = $MyRow['rate'];
	
	$Account = "111203010AD"; // Danamon PTADU USD in IDR
	$SaldoADUDanamonUSD = round(GetGLAccountBalance($Account, $Period) * $CurrentUSDRate, 0);

	$Account = "111203020AD"; // Payoneer PTADU USD in IDR
	$SaldoADUPayoneerUSD = round(GetGLAccountBalance($Account, $Period) * $CurrentUSDRate, 0);

	$Account = "111204030AD"; // Cash in Agent Aye Cargo in BKK in IDR
	$SaldoAyeCargoUSD = round(GetGLAccountBalance($Account, $Period) * $CurrentUSDRate, 0);

	$Account = "111203010AD"; // USD already exchanged current month
	$SQL = "SELECT SUM(banktrans.amount) AS saldo
			FROM banktrans
			WHERE banktrans.bankact = '" . $Account . "'
				AND banktrans.transdate >= '". $FirstDateOfMonth . "'
				AND banktrans.amount > 0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$USDAlreadyExhangedThisMonth = round(($MyRow['saldo'] ?? 0), 0);

	$PORunningTotalUSD = round(GetLastKPIValue("PO-ITEMS-NEXT-%-IDR")*$CurrentUSDRate,0);
	$POPaymentsPendingUSDuntilEndOfMonth = $PORunningTotalUSD / $USDPODaysSchedule * $DaysUntilEndOfMonth * $USDSafetyFactor;
	$SaldoUSD = $SaldoADUDanamonUSD + $SaldoADUPayoneerUSD + $SaldoAyeCargoUSD;
	$ShortageUSD = max(0, $PORunningTotalUSD - $SaldoUSD);
	$ShortageUSDuntilEndOfMonth = $POPaymentsPendingUSDuntilEndOfMonth - $SaldoUSD;
	$USDEasyAvailableThisMonth = max(0, $USDMaxEasyPurchasePerMonth - $USDAlreadyExhangedThisMonth);

	if ($ShortageUSD == 0){ // No USD shortage, no need to exchange
		$ToBeExchanged = 0;
	} elseif ($ShortageUSD <= $USDEasyAvailableThisMonth){ // Shortage can be fulfilled with easy purchase available
		$ToBeExchanged = round_multiple_of($ShortageUSD, $USDMinPurchase);			
	} elseif ($ShortageUSDuntilEndOfMonth <= $USDEasyAvailableThisMonth){ // Shortage until end of month can be fulfilled with easy purchase available
		$ToBeExchanged = round_multiple_of($USDEasyAvailableThisMonth, $USDMinPurchase);			
	} else { // Shortage until end of month cannot be fulfilled with easy purchase available, need to exchange at least the shortage until end of month
		$ToBeExchanged = round_multiple_of($ShortageUSDuntilEndOfMonth, $USDMinPurchase);			
	}
	
	if ($SaldoADUPayoneerUSD < $SaldoADUPayoneerUSDMin){
		$ToBeTransferredToPayoneer = round_multiple_of(min($SaldoADUPayoneerUSDMax - $SaldoADUPayoneerUSD, 
															$SaldoADUDanamonUSD - $SaldoADUDanamonUSDMin), $USDMinPurchase);	
	} else {
		$ToBeTransferredToPayoneer = 0;
	}

	////////////////////////////////////////////////////////
	// CASH STATUS ADU USD SHOW TABLE
	////////////////////////////////////////////////////////

	$TableTitleText = __('Status USD PT. Angin Dingin Utara ');
	ShowTableTitle($TableTitleText);

	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . 'Concept' . '</th>
					<th class="SortedColumn">' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	echo '<tr>
			<td>Running PO for items for sale (USD approx)</td>
			<td class="number">' . locale_number_format($PORunningTotalUSD,0) . '</td>
			</tr>';

	echo '<tr>
			<td>Pending payments until end of month ('.$DaysUntilEndOfMonth.' days) (USD approx)</td>
			<td class="number">' . locale_number_format($POPaymentsPendingUSDuntilEndOfMonth,0) . '</td>
			</tr>';

	echo '<tr>
			<td>Current balance Danamon USD ADU (USD approx)</td>
			<td class="number">' . locale_number_format($SaldoADUDanamonUSD,0) . '</td>
			</tr>';

	echo '<tr>
			<td>Current balance Payoneer USD ADU (USD approx)</td>
			<td class="number">' . locale_number_format($SaldoADUPayoneerUSD,0) . '</td>
			</tr>';

	echo '<tr>
			<td>Current balance Aye Cargo ADU (USD approx)</td>
			<td class="number">' . locale_number_format($SaldoAyeCargoUSD,0) . '</td>
			</tr>';

	echo '<tr>
			<td>Current balance available USD ADU (USD approx)</td>
			<td class="number">' . locale_number_format($SaldoUSD,0) . '</td>
			</tr>';

	echo '<tr>
			<td>USD already exchanged from IDR this month for ADU (USD approx)</td>
			<td class="number">' . locale_number_format($USDAlreadyExhangedThisMonth,0) . '</td>
			</tr>';

	echo '<tr>
				<td>USD needed until end of month ('.$DaysUntilEndOfMonth.' days) (USD approx)</td>
				<td class="number">' . locale_number_format(max($ShortageUSDuntilEndOfMonth,0),0) . '</td>
				</tr>';

	if ($ToBeExchanged > 0){
		echo '<tr class="striped_row">
				<td>ACTION NEEDED --> Purchase USD from ADU Danamon IDR to ADU Danamon USD</td>
				<td class="number">' . locale_number_format($ToBeExchanged) . '</td>
				</tr>';
	}
	
	if ($ToBeTransferredToPayoneer > 0){
		echo '<tr class="striped_row">
				<td>ACTION NEEDED --> Transfer from ADU Danamon USD to ADU Payoneer USD</td>
				<td class="number">' . locale_number_format($ToBeTransferredToPayoneer) . '</td>
				</tr>';
	}

	echo '</tbody></table>
		</div>';

	////////////////////////////////////////////////////////
	// CASH STATUS SMH IDR CALCULATIONS
	////////////////////////////////////////////////////////

	// Sales Cash PT SMH during the year
	$Account = "410000000SM";
	$SalesCashSMH = -GetGLAccountValueBetweenTwoDates($Account, "ALL", $StartDateYTD, $EndOfYear);

	// Cash sales still floating (still not received in kantor)
	$FloatingCashSMH = GetCashSalesValueStillFloating('PTSMH', $StartDateYTD, $EndOfYear);
	
	// Cash Danamon IDR PTSMH to Cash Kantor
	$Account = "111121105SM";
	$BankToCashSMH = -GetGLAccountValueBetweenTwoDates($Account, "TO_CASH_KANTOR", $StartDateYTD, $EndOfYear);
	
	// Cash Mandiri IDR PTSMH to Cash Kantor
	$Account = "111121100SM";
	$BankToCashSMH -= GetGLAccountValueBetweenTwoDates($Account, "TO_CASH_KANTOR", $StartDateYTD, $EndOfYear);

	// Expenses SMH Paid by Petty Cash (excluding salaries, Corporate CC)
	$AccountSuffix = "SM";
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $StartDateYTD . "'
				AND pcashdetails.date <= '" . $EndOfYear . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%CEK SMH'
				AND pctabs.tabcode NOT LIKE 'CC-DANAMON%'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%".$AccountSuffix."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ExpensesSMHPaidCash = -($MyRow[0] ?? 0);
	
	// Cash in Kantor to Small Suppliers PTSMH
	$Account = "510010070SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH%'
					OR gltrans.narrative LIKE '%KANTOR%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToSmallSuppliersSMH = $MyRow[0] ?? 0;

	// Cash in Kantor to Pay rents PTSMH
	$Account = "211030200SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToRentSMH = $MyRow[0] ?? 0;

	// Cash in Kantor to Pay dividends PTSMH
	$Account = "614012400SM";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToDividendsSMH = $MyRow[0] ?? 0;

	$CurrentBalanceSMH = $CashEndOfPreviousYearSMH
						+$SalesCashSMH
						+$BankToCashSMH
						-$FloatingCashSMH
						-$ExpensesSMHPaidCash
						-$CashToSmallSuppliersSMH
						-$CashToRentSMH
						-$CashToDividendsSMH;
	$ToBeMovedSMH = $CurrentBalanceSMH-$YearlyGoalSMH ;
	$ToBeTransferredSMH = round_multiple_of($ToBeMovedSMH, $MinTransferSMH);

	////////////////////////////////////////////////////////
	// CASH STATUS SMH IDR SHOW TABLE
	////////////////////////////////////////////////////////

	$TableTitleText = __('Status Cash IDR PT. Sungai Mutiara Hitam ' . $Year);
	ShowTableTitle($TableTitleText);

	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . 'Concept' . '</th>
					<th class="SortedColumn">' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	echo '<tr>
			<td>Cash SMH in Brankas Kantor end of ' . ($Year-1) . '</td>
			<td class="number">' . locale_number_format($CashEndOfPreviousYearSMH,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Sales Retail PT SMH Cash during '. $Year . '</td>
			<td class="number">' . locale_number_format($SalesCashSMH,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Floating Cash still in shops PT SMH</td>
			<td class="number">' . locale_number_format(-$FloatingCashSMH,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Cash received from shops PT SMH in Brankas Kantor during '. $Year . '</td>
			<td class="number">' . locale_number_format($SalesCashSMH-$FloatingCashSMH,0) . '</td>
			</tr>';
	if ($BankToCashSMH >= 0){
		$Text = 'Total withdrawal from Danamon/Mandiri IDR PTSMH to Brankas Kantor';
	} else {
		$Text = 'Total deposit from Brankas Kantor to Danamon/Mandiri IDR PTSMH ';
	}
	echo '<tr>
			<td>' . $Text . '</td>
			<td class="number">' . locale_number_format($BankToCashSMH,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PT SMH Paid by Petty Cash (excluding checks, salaries, Corporate CC)</td>
			<td class="number">' . locale_number_format(-$ExpensesSMHPaidCash,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PT SMH Small Suppliers Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToSmallSuppliersSMH,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PT SMH Rent Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToRentSMH,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Dividends PT SMH Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToDividendsSMH,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Current Cash PT SMH in Brankas Kantor</td>
			<td class="number">' . locale_number_format($CurrentBalanceSMH,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Cash SMH in Brankas Kantor Goal for end of '. $Year . '</td>
			<td class="number">' . locale_number_format($YearlyGoalSMH,0) . '</td>
			</tr>';
	if ($ToBeMovedSMH >= 0){
		$Text = 'Cash SMH OVER goal in Brankas Kantor';
	} else {
		$Text = 'Cash SMH BELOW goal in Brankas Kantor';
	}
	echo '<tr>
			<td>' . $Text . '</td>
			<td class="number">' . locale_number_format(abs($ToBeMovedSMH),0) . '</td>
			</tr>';
			
	if ($ToBeTransferredSMH != 0){
		if ($ToBeTransferredSMH > 0){
			$Text = 'ACTION NEEDED -> Deposit from Brankas Kantor to Danamon IDR SMH';
		} elseif ($ToBeTransferredSMH < 0){
			$Text = 'ACTION NEEDED -> Withdrawal from Danamon IDR SMH to Brankas Kantor';
		}
		echo '<tr class="striped_row">
				<td>' . $Text . '</td>
				<td class="number">' . locale_number_format(abs($ToBeTransferredSMH),0) . '</td>
				</tr>';
	}
	echo '</tbody></table>
		</div>';

	////////////////////////////////////////////////////////
	// CASH STATUS BB IDR CALCULATIONS
	////////////////////////////////////////////////////////

	// Sales PTBB in Cash during the Year
	$Account = "410000000BB";
	$SalesCashBB = -GetGLAccountValueBetweenTwoDates($Account, "ALL", $StartDateYTD, $EndOfYear);

	// Cash sales still floating (still not received in kantor)
	$FloatingCashBB = GetCashSalesValueStillFloating('PTBB', $StartDateYTD, $EndOfYear);

	// Cash Danamon IDR PTBB to Cash Kantor
	$Account = "111121105BB";
	$BankToCashBB = -GetGLAccountValueBetweenTwoDates($Account, "TO_CASH_KANTOR", $StartDateYTD, $EndOfYear);

	// Expenses PT Paid by Petty Cash (excluding salaries, Corporate CC)
	$AccountSuffix = "BB";
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $StartDateYTD . "'
				AND pcashdetails.date <= '" . $EndOfYear . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%CEK BB'
				AND pctabs.tabcode NOT LIKE 'CC-DANAMON%'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%".$AccountSuffix."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ExpensesBBPaidCash = -($MyRow[0] ?? 0);
	
	// Cash in Kantor to Small Suppliers PTBB
	$Account = "510010070BB";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND (gltrans.narrative LIKE '%CASH%'
					OR gltrans.narrative LIKE '%KANTOR%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToSmallSuppliersBB = $MyRow[0] ?? 0;

	// Cash in Kantor to Pay rents PTBB
	$Account = "211030200BB";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToRentBB = $MyRow[0] ?? 0;
	
	// Cash in Kantor to Pay dividends PTBB
	$Account = "614012400BB";
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDateYTD . "'
				AND gltrans.trandate <= '" . $EndOfYear . "'
				AND gltrans.account = '" . $Account . "'
				AND gltrans.narrative LIKE '%CASH%'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CashToDividendsBB = $MyRow[0] ?? 0;

	$CurrentBalanceBB = $CashEndOfPreviousYearBB
						+$SalesCashBB
						+$BankToCashBB
						-$FloatingCashBB
						-$ExpensesBBPaidCash
						-$CashToSmallSuppliersBB
						-$CashToRentBB
						-$CashToDividendsBB;
	$ToBeMovedBB = $CurrentBalanceBB-$YearlyGoalBB ;
	$ToBeTransferredBB = round_multiple_of($ToBeMovedBB, $MinTransferBB);	

	////////////////////////////////////////////////////////
	// CASH STATUS BB IDR SHOW TABLE
	////////////////////////////////////////////////////////

	$TableTitleText = __('Status Cash IDR PT. Bumi Biru ' . $Year);
	ShowTableTitle($TableTitleText);

	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . 'Concept' . '</th>
					<th class="SortedColumn">' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	echo '<tr>
			<td>Cash PTBB in Brankas Kantor end of ' . ($Year-1) . '</td>
			<td class="number">' . locale_number_format($CashEndOfPreviousYearBB,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Sales Retail PTBB Cash during '. $Year . '</td>
			<td class="number">' . locale_number_format($SalesCashBB,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Floating Cash still in shops PTBB</td>
			<td class="number">' . locale_number_format(-$FloatingCashBB,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Cash received from shops PTBB in Brankas Kantor during '. $Year . '</td>
			<td class="number">' . locale_number_format($SalesCashBB-$FloatingCashBB,0) . '</td>
			</tr>';
	if ($BankToCashBB >= 0){
		$Text = 'Total withdrawal from Danamon IDR PTBB to Brankas Kantor';
	} else {
		$Text = 'Total deposit from Brankas Kantor to Danamon IDR PTBB ';
	}
	echo '<tr>
			<td>' . $Text . '</td>
			<td class="number">' . locale_number_format($BankToCashBB,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PTBB Paid by Petty Cash (excluding checks, salaries, Corporate CC)</td>
			<td class="number">' . locale_number_format(-$ExpensesBBPaidCash,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PTBB Small Suppliers Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToSmallSuppliersBB,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Expenses PTBB Rent Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToRentBB,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Dividends PTBB Paid from Cash Kantor</td>
			<td class="number">' . locale_number_format(-$CashToDividendsBB,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Current Cash PTBB in Brankas Kantor</td>
			<td class="number">' . locale_number_format($CurrentBalanceBB,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Cash PTBB in Brankas Kantor Goal for end of '. $Year . '</td>
			<td class="number">' . locale_number_format($YearlyGoalBB,0) . '</td>
			</tr>';
	if ($ToBeMovedBB >= 0){
		$Text = 'Cash PTBB OVER goal in Brankas Kantor';
	} else {
		$Text = 'Cash PTBB BELOW goal in Brankas Kantor';
	}
	echo '<tr>
			<td>' . $Text . '</td>
			<td class="number">' . locale_number_format(abs($ToBeMovedBB),0) . '</td>
			</tr>';

	if ($ToBeTransferredBB != 0){
		if ($ToBeTransferredBB > 0){
			$Text = 'ACTION NEEDED -> Deposit from Brankas Kantor to Danamon IDR BB';
		} elseif ($ToBeTransferredBB < 0){
			$Text = 'ACTION NEEDED -> Withdrawal from Danamon IDR BB to Brankas Kantor';
		}
		echo '<tr class="striped_row">
				<td>' . $Text . '</td>
				<td class="number">' . locale_number_format(abs($ToBeTransferredBB),0) . '</td>
				</tr>';
	}
	echo '</tbody></table>
		</div>';	

	////////////////////////////////////////////////////////
	// CASH STATUS BRANKAS KANTOR & SHAREHOLDERS IDR CALCULATIONS
	////////////////////////////////////////////////////////

	$Account = "111111200";
	$SaldoBrankasKantor = round(GetGLAccountBalance($Account, $Period), 0);

	$Account = "111131100";
	$SaldoBrankasShareholders = round(GetGLAccountBalance($Account, $Period), 0);
		
	////////////////////////////////////////////////////////
	// CASH STATUS STATUS BRANKAS KANTOR & SHAREHOLDERS IDR SHOW TABLE
	////////////////////////////////////////////////////////
		
	if ($AdminRole){
		$TableTitleText = __('Status Cash IDR Brankas Kantor and Shareholders ' . $Year);
		ShowTableTitle($TableTitleText);

		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th>' . 'Concept' . '</th>
						<th>' . 'Value' . '</th>
					</tr>
				</thead>
				<tbody>';
		
		$FreeSaldoBrankasKantor = $SaldoBrankasKantor - $CurrentBalanceADU - $CurrentBalanceSMH - $CurrentBalanceBB;
		$FreeSaldoBrankasShareholders = $SaldoBrankasShareholders + $FreeSaldoBrankasKantor;
		$ToBeDistributedToShareholders = round_multiple_of($FreeSaldoBrankasShareholders, $MinMoveFree);	

		$i = 1;
		echo '<tr>
				<td>Cash belonging to PTADU</td>
				<td class="number">' . locale_number_format($CurrentBalanceADU,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Cash belonging to PTSMH</td>
				<td class="number">' . locale_number_format($CurrentBalanceSMH,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Cash belonging to PTBB</td>
				<td class="number">' . locale_number_format($CurrentBalanceBB,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Total Cash PTADU+PTSMH+PTBB</td>
				<td class="number">' . locale_number_format($CurrentBalanceADU+$CurrentBalanceSMH+$CurrentBalanceBB,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Saldo Cash in Brankas Kantor </td>
				<td class="number">' . locale_number_format($SaldoBrankasKantor,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Saldo Cash in Brankas Shareholders</td>
				<td class="number">' . locale_number_format($SaldoBrankasShareholders,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Total Saldo Cash</td>
				<td class="number">' . locale_number_format($SaldoBrankasKantor + $SaldoBrankasShareholders,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Free Cash</td>
				<td class="number">' . locale_number_format($FreeSaldoBrankasShareholders,0) . '</td>
				</tr>';
		if ($ToBeDistributedToShareholders !=0){
			if ($FreeSaldoBrankasShareholders >= 0){
				$Text = 'ACTION NEEDED -> Distribute Cash from Brankas Shareholders to Shareholders';
			} else {
				$Text = 'ACTION NEEDED -> Get Cash from Shareholders to Brankas Shareholders';
			}
			echo '<tr class="striped_row">
				<td>' . $Text . '</td>
				<td class="number">' . locale_number_format(abs($ToBeDistributedToShareholders),0) . '</td>
				</tr>';
		}
		echo '</tbody></table>
			</div>';	

		InsertKPI("CASH-FREE", $FreeSaldoBrankasShareholders);
	}

	InsertKPI("CASH-PTADU", $CurrentBalanceADU);
	InsertKPI("CASH-PTSMH", $CurrentBalanceSMH);
	InsertKPI("CASH-PTBB", $CurrentBalanceBB);

}

/**************************************************************************************************************
* DailySalesRecords
*
* Displays the top sales days within a given date range
* 
* @param int $Days - Number of top days to show
* @param int $NumDays - Number of days back to analyze
* @param string $Since - Optional date to start analysis from
* 
* @return void - Outputs HTML table with top sales days
**************************************************************************************************************/
function DailySalesRecords($Days, $NumDays, $Since){

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	
	if ($Since != ''){
		if ($Since >= $FromDate){
			$FromDate = $Since;
		}
	}
	$SQL = "SELECT salesorders.orddate,
				SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) AS sales
			FROM salesorders
			INNER JOIN salesorderdetails ON
				salesorders.orderno=salesorderdetails.orderno
			INNER JOIN debtorsmaster ON 
				salesorders.debtorno = debtorsmaster.debtorno
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
				AND salesorders.orddate >= '" . $FromDate . "'
			GROUP BY salesorders.orddate
			ORDER BY SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) DESC
			LIMIT ". $Days . "";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Top ') . $Days . __(' retail sales days since '). ConvertSQLDate($FromDate);
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' .  __('#') . '</th>
						<th class="SortedColumn">' .  __('Date') . '</th>
						<th class="SortedColumn">' . __('Sales') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while (($MyRow = DB_fetch_array($Result)) AND ($i <= $Days)) {
			echo '<tr class="striped_row">
					<td class="number">' . locale_number_format($i,0) . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['sales'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody></table>
				</div>
				</form>';
	}
}

/**************************************************************************************************************
* GeneralCustomerBehaviour
*
* Analyzes and displays general customer behavior metrics for a specific brand, comparing current
* period with same period last year
* 
* @param string $Brand - Brand code to analyze
* @param int $NumDaysA - Number of days to analyze
* 
* @return void - Outputs HTML table and inserts KPI values
**************************************************************************************************************/
function GeneralCustomerBehaviour($Brand, $NumDaysA){
	$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1));
	$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-365));
	$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-1-365));

	$BrandText= BrandTextFromCode($Brand);
	$BrandCode = substr($Brand, -2); // Get the 2 rightmost characters of $Brand

	$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				(SELECT SUM(salesorderdetails.qtyinvoiced)
					FROM salesorders, salesorderdetails
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorderdetails.orderno = salesorders.orderno
					GROUP BY salesorders.debtorno) AS itemcount,
				(SELECT SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard)
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) AS invoicesum,
				(SELECT COUNT(DISTINCT(salesorders.orderno))
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) AS invoicecount,
				(SELECT SUM(salesorderdetails.qtyinvoiced)
					FROM salesorders, salesorderdetails
					WHERE salesorders.orddate >=  '" . $StartDateB . "'
						AND salesorders.orddate <= '" . $YesterdayB . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorderdetails.orderno = salesorders.orderno
					GROUP BY salesorders.debtorno) AS itemcount_lastyear,
				(SELECT SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard)
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateB . "'
						AND salesorders.orddate <= '" . $YesterdayB . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) AS invoicesum_lastyear,
				(SELECT COUNT(DISTINCT(salesorders.orderno))
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateB . "'
						AND salesorders.orddate <= '" . $YesterdayB . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) AS invoicecount_lastyear
			FROM debtorsmaster, custbranch, locations
			WHERE debtorsmaster.debtorno = custbranch.debtorno
				AND custbranch.defaultlocation = locations.loccode
				AND debtorsmaster.typeid = 2
				AND locations.typeloc = '".$Brand."'
			ORDER BY (SELECT COUNT(DISTINCT(salesorders.orderno))
					FROM salesorders
					WHERE salesorders.orddate >=  '" . $StartDateA . "'
						AND salesorders.orddate <= '" . $YesterdayA . "'
						AND salesorders.debtorno = debtorsmaster.debtorno
					GROUP BY salesorders.debtorno) DESC";
	
						
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "General Customer Behaviour by " . $BrandText  . " shop during the last " . $NumDaysA . " days.";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="3"></th>
						<th colspan="5">' . 'This year'. '</th>
						<th colspan="5">' . 'Last year'. '</th>
					</tr>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Shop') . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . 'IDR/Invoice'. '</th>
						<th class="SortedColumn">' . 'IDR/Piece'. '</th>
						<th class="SortedColumn">' . '# Invoice/Day'. '</th>
						<th class="SortedColumn">' . '# Pcs/Day'. '</th>
						<th class="SortedColumn">' . '# Pcs/Inv'. '</th>
						<th class="SortedColumn">' . 'IDR/Invoice'. '</th>
						<th class="SortedColumn">' . 'IDR/Piece'. '</th>
						<th class="SortedColumn">' . '# Invoice/Day'. '</th>
						<th class="SortedColumn">' . '# Pcs/Day'. '</th>
						<th class="SortedColumn">' . '# Pcs/Inv'. '</th>
					</tr>
				</thead>
				<tbody>';
		$TotalInvoiceSum = 0;
		$TotalInvoiceCount = 0;
		$TotalItemCount = 0;		
		$TotalInvoiceSumLastYear = 0;
		$TotalInvoiceCountLastYear = 0;
		$TotalItemCountLastYear = 0;
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$i++;
			$Code = $MyRow['debtorno'];
			$Name = $MyRow['name'];
			
			if ($MyRow['invoicesum'] > 0){

				$TotalInvoiceSum += $MyRow['invoicesum'];
				$TotalInvoiceCount += $MyRow['invoicecount'];
				$TotalItemCount += $MyRow['itemcount'];		
				$TotalInvoiceSumLastYear += $MyRow['invoicesum_lastyear'];
				$TotalInvoiceCountLastYear += $MyRow['invoicecount_lastyear'];
				$TotalItemCountLastYear += $MyRow['itemcount_lastyear'];		

				$AvgIDRPerInvoice = ($MyRow['invoicecount'] !=0) ? $MyRow['invoicesum']/$MyRow['invoicecount'] : 0;
				$AvgIDRPerItem = ($MyRow['itemcount'] !=0) ? $MyRow['invoicesum']/$MyRow['itemcount'] : 0;
				$AvgInvoicesPerDay = ($NumDaysA != 0) ? $MyRow['invoicecount']/$NumDaysA : 0;
				$AvgItemsPerDay = ($NumDaysA != 0) ? $MyRow['itemcount']/$NumDaysA : 0;
				$AvgItemsPerInvoice = ($MyRow['invoicecount'] != 0) ? $MyRow['itemcount']/$MyRow['invoicecount'] : 0;

				$AvgIDRPerInvoiceLastYear = ($MyRow['invoicecount_lastyear'] !=0) ? $MyRow['invoicesum_lastyear']/$MyRow['invoicecount_lastyear'] : 0;
				$AvgIDRPerItemLastYear = ($MyRow['itemcount_lastyear'] !=0) ? $MyRow['invoicesum_lastyear']/$MyRow['itemcount_lastyear'] : 0;
				$AvgInvoicesPerDayLastYear = ($NumDaysA != 0) ? $MyRow['invoicecount_lastyear']/$NumDaysA: 0;
				$AvgItemsPerDayLastYear = ($NumDaysA != 0) ? $MyRow['itemcount_lastyear']/$NumDaysA : 0;
				$AvgItemsPerInvoiceLastYear = ($MyRow['invoicecount_lastyear'] != 0) ? $MyRow['itemcount_lastyear']/$MyRow['invoicecount_lastyear'] : 0;

				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $Code . '</td>
						<td>' . $Name . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgIDRPerInvoice,0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgIDRPerItem,0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgInvoicesPerDay,1) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgItemsPerDay,1) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgItemsPerInvoice,1) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgIDRPerInvoiceLastYear,0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgIDRPerItemLastYear,0) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgInvoicesPerDayLastYear,1) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgItemsPerDayLastYear,1) . '</td>
						<td class="number">' . locale_number_format_zero_blank($AvgItemsPerInvoiceLastYear,1) . '</td>
						</tr>';
				
			}
		}
		echo '<tr class="striped_row">
				<td class="number"></td>
				<td></td>
				<td>Brand Average</td>
				<td class="number">' . locale_number_format_zero_blank($TotalInvoiceSum/$TotalInvoiceCount,0) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalInvoiceSum/$TotalItemCount,0) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalInvoiceCount/$NumDaysA,1) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalItemCount/$NumDaysA,1) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalItemCount/$TotalInvoiceCount,1) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalInvoiceSumLastYear/$TotalInvoiceCountLastYear,0) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalInvoiceSumLastYear/$TotalItemCountLastYear,0) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalInvoiceCountLastYear/$NumDaysA,1) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalItemCountLastYear/$NumDaysA,1) . '</td>
				<td class="number">' . locale_number_format_zero_blank($TotalItemCountLastYear/$TotalInvoiceCountLastYear,1) . '</td>
				</tr>';
		echo '</tbody></table>
				</div>';
		InsertKPI("INV-AV-ITEMS-INV-" . $NumDaysA . "-ITEM-" . $BrandCode, $TotalItemCount/$TotalInvoiceCount);
	}
}

/**************************************************************************************************************
* PettyCashStatus
*
* Displays the status of petty cash accounts for a specific currency
* 
* @param string $Currency - Currency code (e.g., "IDR", "USD")
* 
* @return void - Outputs HTML table showing petty cash status
**************************************************************************************************************/
function PettyCashStatus($Currency){

	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pctabs.currency = '". $Currency ."'
				AND pcashdetails.authorized != '1000-01-01'
			GROUP BY pcashdetails.tabcode
			HAVING ( SUM(pcashdetails.amount) <= -0.01
					OR SUM(pcashdetails.amount) >= 0.01)";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Petty Cash Authorized Status for '). $Currency . ' accounts';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('PC Tab Code') . '</th>
						<th class="SortedColumn">' . __('Amount') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		$Total = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $MyRow['tabcode'] . '</td>
					<td class="number">' . locale_number_format($MyRow['amount'],0) . '</td>
					</tr>';
			$i++;
			$Total = $Total + $MyRow['amount'];
		}
		echo '<tr class="striped_row">
				<td class="number"></td>
				<td>Total</td>
				<td class="number">' . locale_number_format($Total,0) . '</td>
				</tr>';
		
		echo '</tbody></table>
				</div>';
	}
}

/**************************************************************************************************************
* PeriodDifferenceSales
*
* Compares sales between two different time periods for shops, online channels, or sales personnel
* 
* @param string $Typeperiod - Type of period comparison ("YEAR", "IMMEDIATE", or specific year)
* @param string $Typereport - Type of report ("Shop", "Online", or other)
* @param mixed $NumDaysA - Number of days to analyze or "YTD" for year-to-date
* 
* @return void - Outputs HTML table and may insert KPI values
**************************************************************************************************************/
function PeriodDifferenceSales($Typeperiod, $Typereport, $NumDaysA){

	/* SQL optimized by Claude Sonnet 4.0 23/08/2025 */
	if ($NumDaysA == "YTD"){
		// we need to translate YTD to a number of days
		// As suggested by OpenAI ChatGPT ;-)
		// Get the current timestamp
		$Current_timestamp = time();
		// Extract the year of yesterday
		$Current_year = date('Y', strtotime("-1 days"));
		// Create a timestamp for the first day of the year
	
		$first_day_timestamp = mktime(0, 0, 0, 1, 1, $Current_year);
		// Calculate the number of seconds between the two timestamps
		$seconds_diff = $Current_timestamp - $first_day_timestamp;
		// Calculate the number of days between the first day of the year and the current day
		$NumDaysA = floor($seconds_diff / 86400);		

		$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDateA = $Current_year . '-01-01';
		$YesterdayB  = $Typeperiod . substr($YesterdayA, 4, 6);
		$StartDateB = $Typeperiod . '-01-01';
		$Title = __('Difference sales for ') . $Typereport . " YTD (Year To Date) and same period in " . $Typeperiod;
		$TitleCurrent = $NumDaysA . ' Days This Year';
		$TitlePrevious = $NumDaysA . ' Days '. $Typeperiod;
	}
	else {
		$YesterdayA  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA));
		if ($Typeperiod == "YEAR"){
			$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-365));
			$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-365));
			$Title = __('Difference sales for ') . $Typereport . " during the last " . $NumDaysA . " days and same period last year";
			$TitleCurrent = $NumDaysA . ' Days This Year';
			$TitlePrevious = $NumDaysA . ' Days Last Year';
		} elseif ($Typeperiod == "IMMEDIATE"){
			$YesterdayB  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1-$NumDaysA));
			$StartDateB = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA-$NumDaysA));
			$Title = __('Difference sales for ') . $Typereport .  " during the last " . $NumDaysA . " days and previous immediate same period";
			$TitleCurrent = $NumDaysA . ' Last Days';
			$TitlePrevious = $NumDaysA . ' Previous Days';
		} else {
			// comparing with a fixed year
			$YesterdayB  = $Typeperiod . substr($YesterdayA, 4, 6);
			$StartDateB = FormatDateForSQL(DateAdd(ConvertSQLDate($YesterdayB),'d',-$NumDaysA));
			$Title = __('Difference sales for ') . $Typereport . " during the last " . $NumDaysA . " days and same period in " . $Typeperiod;
			$TitleCurrent = $NumDaysA . ' Days This Year';
			$TitlePrevious = $NumDaysA . ' Days '. $Typeperiod;
		}
	}

	$TotalDateA = 0;
	$TotalDateB = 0;
	$TotalRent = 0;
	$TotalBothYearsDateA = 0;
	$TotalBothYearsDateB = 0;
	$TotalBothYearsRent = 0;
	$TotalNewDateA = 0;
	$TotalOldDateB = 0;
	$TotalNewRent = 0;
	$TotalOldRent = 0;

	if (($Typereport == "Shop") OR ($Typereport == "Online")) {
		if ($Typereport == "Shop") {
			// Optimized Shop query with separate subqueries for current and compare periods
			$SQL = "SELECT dm.debtorno,
						dm.name,
						COALESCE(loc.klyearlyrent, 0) AS yearlyrent,
						COALESCE(current_sales.sales, 0) AS salesA,
						COALESCE(compare_sales.sales, 0) AS salesB
					FROM debtorsmaster dm
					LEFT JOIN locations loc ON loc.cashsalecustomer = dm.debtorno
					LEFT JOIN (
						SELECT so.debtorno,
							SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100) / c.rate) AS sales
						FROM salesorders so
						INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
						INNER JOIN debtorsmaster dm2 ON so.debtorno = dm2.debtorno
						INNER JOIN currencies c ON dm2.currcode = c.currabrev
						WHERE so.orddate >= '" . $StartDateA . "' AND so.orddate <= '" . $YesterdayA . "'
							AND so.quotation = 0
							AND sod.qtyinvoiced > 0
						GROUP BY so.debtorno
					) current_sales ON dm.debtorno = current_sales.debtorno
					LEFT JOIN (
						SELECT so.debtorno,
							SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100) / c.rate) AS sales
						FROM salesorders so
						INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
						INNER JOIN debtorsmaster dm2 ON so.debtorno = dm2.debtorno
						INNER JOIN currencies c ON dm2.currcode = c.currabrev
						WHERE so.orddate >= '" . $StartDateB . "' AND so.orddate <= '" . $YesterdayB . "'
							AND so.quotation = 0
							AND sod.qtyinvoiced > 0
						GROUP BY so.debtorno
					) compare_sales ON dm.debtorno = compare_sales.debtorno
					WHERE (dm.typeid = 2 OR dm.typeid = 11)
						AND (current_sales.sales > 0 OR compare_sales.sales > 0)
					ORDER BY COALESCE(current_sales.sales, 0) DESC";
		} else {
			// Optimized Online query with separate subqueries for current and compare periods
			$SQL = "SELECT dm.debtorno,
						CONCAT(dm.name, ' (', dt.typename, ')') AS name,
						0 AS yearlyrent,
						COALESCE(current_sales.sales, 0) AS salesA,
						COALESCE(compare_sales.sales, 0) AS salesB
					FROM debtorsmaster dm
					INNER JOIN debtortype dt ON dm.typeid = dt.typeid
					LEFT JOIN (
						SELECT so.debtorno,
							SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100) / c.rate) AS sales
						FROM salesorders so
						INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
						INNER JOIN debtorsmaster dm2 ON so.debtorno = dm2.debtorno
						INNER JOIN currencies c ON dm2.currcode = c.currabrev
						WHERE so.orddate >= '" . $StartDateA . "' AND so.orddate <= '" . $YesterdayA . "'
							AND so.quotation = 0
							AND sod.qtyinvoiced > 0
						GROUP BY so.debtorno
					) current_sales ON dm.debtorno = current_sales.debtorno
					LEFT JOIN (
						SELECT so.debtorno,
							SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100) / c.rate) AS sales
						FROM salesorders so
						INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
						INNER JOIN debtorsmaster dm2 ON so.debtorno = dm2.debtorno
						INNER JOIN currencies c ON dm2.currcode = c.currabrev
						WHERE so.orddate >= '" . $StartDateB . "' AND so.orddate <= '" . $YesterdayB . "'
							AND so.quotation = 0
							AND sod.qtyinvoiced > 0
						GROUP BY so.debtorno
					) compare_sales ON dm.debtorno = compare_sales.debtorno
					WHERE dm.typeid IN (2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20)
						AND dm.debtorno NOT IN ('WEB-WH-IDR', 'WEB-WH-USD', 'WEB-WH-EUR', 'WEB-WH-AUD')
						AND (current_sales.sales > 0 OR compare_sales.sales > 0)
					ORDER BY COALESCE(current_sales.sales, 0) DESC";
		}
	} else {
		// Optimized Salesman query with separate subqueries for current and compare periods
		$SQL = "SELECT s.salesmancode,
					s.salesmanname,
					COALESCE(current_sales.sales, 0) AS salesA,
					COALESCE(compare_sales.sales, 0) AS salesB
				FROM salesman s
				LEFT JOIN (
					SELECT so.salesperson,
						SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100) / c.rate) AS sales
					FROM salesorders so
					INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
					INNER JOIN debtorsmaster dm ON so.debtorno = dm.debtorno
					INNER JOIN currencies c ON dm.currcode = c.currabrev
					WHERE so.orddate >= '" . $StartDateA . "' AND so.orddate <= '" . $YesterdayA . "'
						AND so.quotation = 0
						AND sod.qtyinvoiced > 0
					GROUP BY so.salesperson
				) current_sales ON s.salesmancode = current_sales.salesperson
				LEFT JOIN (
					SELECT so.salesperson,
						SUM(sod.qtyinvoiced * sod.unitprice * (1 - sod.discountpercent/100) / c.rate) AS sales
					FROM salesorders so
					INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
					INNER JOIN debtorsmaster dm ON so.debtorno = dm.debtorno
					INNER JOIN currencies c ON dm.currcode = c.currabrev
					WHERE so.orddate >= '" . $StartDateB . "' AND so.orddate <= '" . $YesterdayB . "'
						AND so.quotation = 0
						AND sod.qtyinvoiced > 0
					GROUP BY so.salesperson
				) compare_sales ON s.salesmancode = compare_sales.salesperson
				WHERE s.current = 1
					AND (current_sales.sales > 0 OR compare_sales.sales > 0)
				ORDER BY COALESCE(current_sales.sales, 0) DESC";
	}
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Title;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . $Typereport . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . $TitleCurrent . '</th>
						<th class="SortedColumn">' . $TitlePrevious . '</th>
						<th class="SortedColumn">' . __('Trend') . '</th>
						<th class="SortedColumn">' . __('%Rent/Sales') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {

			if (($Typereport == "Shop") OR ($Typereport == "Online")){
				$Code = $MyRow['debtorno'];
				$Name = $MyRow['name'];
				if (($MyRow['salesA'] > 0) AND ($MyRow['yearlyrent'] > 0)){
					$Rent = round(($MyRow['yearlyrent'] / 365 * $NumDaysA) / $MyRow['salesA'] * 100) . '%';
				} else {
					$Rent = "";
				}
			} else {
				$Code = $MyRow['salesmancode'];
				$Name = $MyRow['salesmanname'];
				$Rent = "";
			}
			
			if ($MyRow['salesB'] != 0){
				$Percent = (($MyRow['salesA'])-($MyRow['salesB']))/($MyRow['salesB']) * 100;
			} else {
				$Percent = 0;
			}
			$Trend = " ";
			if ($Percent > MINIMUM_AVERAGE_SALES_COMPARED_LAST_YEAR_TREND){
				$Trend = "Improving ". locale_number_format($Percent,0) . "%";
			}
			if ($Percent < -MINIMUM_AVERAGE_SALES_COMPARED_LAST_YEAR_TREND){
				$Trend = "Degrading ". locale_number_format($Percent,0) . "%";
			}
			if (($MyRow['salesA'] > 0) OR ($MyRow['salesB'] > 0)){
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $Code . '</td>
						<td>' . $Name . '</td>
						<td class="number">' . locale_number_format($MyRow['salesA'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['salesB'],0) . '</td>
						<td>' . $Trend . '</td>
						<td class="number">' . $Rent . '</td>
						</tr>';
				$i++;
			}

			if (($MyRow['salesA'] > 0) AND ($MyRow['salesB'] > 0)){
				$TotalBothYearsDateA = $TotalBothYearsDateA +($MyRow['salesA']);
				$TotalBothYearsDateB = $TotalBothYearsDateB +($MyRow['salesB']);
				$TotalBothYearsRent = $TotalBothYearsRent +($MyRow['yearlyrent']);
			}
			if (($MyRow['salesA'] > 0) AND ($MyRow['salesB'] == 0)){
				$TotalNewDateA = $TotalNewDateA +($MyRow['salesA']);
				$TotalNewRent = $TotalNewRent +($MyRow['yearlyrent']);
			}
			if (($MyRow['salesA'] == 0) AND ($MyRow['salesB'] > 0)){
				$TotalOldDateB = $TotalOldDateB +($MyRow['salesB']);
				$TotalOldRent = $TotalOldRent +($MyRow['yearlyrent']);
			}
			$TotalDateA = $TotalDateA +($MyRow['salesA']);
			$TotalRent = $TotalRent +($MyRow['yearlyrent']);
			$TotalDateB = $TotalDateB +($MyRow['salesB']);
		}
		echo '</tbody>
			<tfooter>';
		if ($Typereport == "Shop"){
			$Percent = (($TotalBothYearsDateA)-($TotalBothYearsDateB))/($TotalBothYearsDateB) * 100;
			$Trend = " ";
			if ($Percent > 0){
				$Trend = "Improving ". locale_number_format($Percent,1) . "%";
			}
			if ($Percent < 0){
				$Trend = "Degrading ". locale_number_format($Percent,1) . "%";
			}
			$Rent = round(($TotalBothYearsRent / 365 * $NumDaysA) / $TotalBothYearsDateA * 100) . '%';
			echo '<tr class="striped_row">
					<td></td>
					<td></td>
					<td>EXISTING SHOPS</td>
					<td class="number">' . locale_number_format($TotalBothYearsDateA,0) . '</td>
					<td class="number">' . locale_number_format($TotalBothYearsDateB,0) . '</td>
					<td>' . $Trend . '</td>
					<td class="number">' . $Rent . '</td>
					</tr>';
			if ($TotalNewDateA > 0){
				$Rent = round(($TotalNewRent / 365 * $NumDaysA) / $TotalNewDateA * 100) . '%';
				echo '<tr class="striped_row">
						<td></td>
						<td></td>
						<td>NEW SHOPS</td>
						<td class="number">' . locale_number_format($TotalNewDateA,0) . '</td>
						<td class="number"></td>
						<td></td>
						<td class="number">' . $Rent . '</td>
						</tr>';
			}
			if ($TotalOldDateB > 0){
				$Rent = round(($TotalOldRent / 365 * $NumDaysA) / $TotalOldDateB * 100) . '%';
				echo '<tr class="striped_row">
						<td></td>
						<td></td>
						<td>CLOSED SHOPS</td>
						<td class="number"></td>
						<td class="number">' . locale_number_format($TotalOldDateB,0) . '</td>
						<td></td>
						<td class="number">' . $Rent . '</td>
						</tr>';
			}
		}
		if (($Typereport == "Shop") OR ($Typereport == "Online")){
			$Percent = (($TotalDateA)-($TotalDateB))/($TotalDateB) * 100;
			$Trend = " ";
			if ($Percent > 0){
				$Trend = "Improving ". locale_number_format($Percent,1) . "%";
			}
			if ($Percent < 0){
				$Trend = "Degrading ". locale_number_format($Percent,1) . "%";
			}
			$Rent = round(($TotalRent / 365 * $NumDaysA) / $TotalDateA * 100) . '%';
			echo '<tr class="striped_row">
					<td></td>
					<td></td>
					<td>TOTAL</td>
					<td class="number">' . locale_number_format($TotalDateA,0) . '</td>
					<td class="number">' . locale_number_format($TotalDateB,0) . '</td>
					<td>' . $Trend . '</td>
					<td class="number">' . $Rent . '</td>
					</tr>';
		}
		echo '</tfooter>
				</table>
				</div>';
		if (($Typereport == "Shop") AND ($Typeperiod == "YEAR")){
			InsertKPI("SALES-TREND-RETAIL-" . $NumDaysA . "D-PERCENT", $Percent);
		}
	}
}

/**************************************************************************************************************
* UnbalancedGLTransTX
*
* Finds and displays General Ledger transactions that are unbalanced
* 
* @param int $NumDays - Number of days back to analyze
* @param string $RootPath - Root path of the application for links
* 
* @return void - Outputs HTML table of unbalanced transactions
**************************************************************************************************************/
function UnbalancedGLTransTX($NumDays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT gltrans.trandate, 
				systypes.typename, 
				gltrans.type, 
				gltrans.typeno, 
				SUM(gltrans.amount) AS unbalance
			FROM gltrans, systypes
			WHERE gltrans.type = systypes.typeid 
				AND gltrans.trandate >= '" . $StartDate . "'
			GROUP BY gltrans.type, gltrans.typeno 
			HAVING ABS(SUM(gltrans.amount)) >= 1
			ORDER BY gltrans.trandate";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'Unbalanced GLTrans Transactions during the last ' . $NumDays . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('Date') . '</th>
						<th class="SortedColumn">' . __('Type') . '</th>
						<th class="SortedColumn">' . __('TypeNo') . '</th>
						<th class="SortedColumn">' . __('Unbalance') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {

			$CodeLink = '<a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $MyRow['type'] . '&TransNo=' . $MyRow['typeno'] . '">' . $MyRow['typeno'] . '</a>';
					
			echo '<tr class="striped_row">
					<td>' . ConvertSQLDateTime($MyRow['trandate']) . '</td>
					<td>' . $MyRow['typename'] . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td class="number">' . locale_number_format($MyRow['unbalance'],0) . '</td>
					</tr>';
		}
		echo '</tbody></table>
			</div>';
	}
}

/**************************************************************************************************************
* EmptyAccountsGLTransTX
*
* Finds and displays General Ledger transactions with empty account codes
* 
* @param int $NumDays - Number of days back to analyze
* @param string $RootPath - Root path of the application for links
* 
* @return void - Outputs HTML table of transactions with empty accounts
**************************************************************************************************************/
function EmptyAccountsGLTransTX($NumDays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$TotalAmount = 0;
	$SQL = "SELECT gltrans.counterindex,
				gltrans.trandate, 
				gltrans.type, 
				gltrans.typeno, 
				gltrans.amount
			FROM gltrans
			WHERE gltrans.trandate >= '" . $StartDate . "'
				AND account = ''
			ORDER BY gltrans.counterindex";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'Empty account code GLTrans Transactions during the last ' . $NumDays . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Date') . '</th>
						<th class="SortedColumn">' . __('Type') . '</th>
						<th class="SortedColumn">' . __('TypeNo') . '</th>
						<th class="SortedColumn">' . __('Amount') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {

			$CodeLink = '<a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $MyRow['type'] . '&TransNo=' . $MyRow['typeno'] . '">' . $MyRow['typeno'] . '</a>';
					
			echo '<tr class="striped_row">
					<td class="number">' . locale_number_format($MyRow['counterindex'],0) . '</td>
					<td>' . ConvertSQLDateTime($MyRow['trandate']) . '</td>
					<td>' . $MyRow['type'] . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td class="number">' . locale_number_format($MyRow['amount'],0) . '</td>
					</tr>';
			$TotalAmount += $MyRow['amount'];
		}
		echo '<tr class="striped_row">
				<td>TOTAL</td>
				<td></td>
				<td></td>
				<td class="number"></td>
				<td class="number">' . locale_number_format($TotalAmount,0) . '</td>
				</tr>';
		echo '</tbody></table>
			</div>';
	}
}

/**************************************************************************************************************
* ShowKPIHistory
*
* Displays historical Key Performance Indicators for a specified number of days
* 
* @param int $NumDays - Number of days of history to show
* 
* @return void - Outputs HTML table with KPI history
**************************************************************************************************************/
function ShowKPIHistory($NumDays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT kpicode,
				kpidescripiption,
				MIN(value) AS minimumvalue,
				AVG(value) AS averagevalue,
				MAX(value) AS maximumvalue
			FROM klkpi
			INNER JOIN klkpidescriptions
				ON klkpi.klkpi = klkpidescriptions.klkpi
			WHERE date >= '" . $StartDate . "'
			GROUP BY kpicode
			ORDER BY kpicode";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'General KPI last ' . $NumDays . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('KPI') . '</th>
						<th class="SortedColumn">' . __('Minimum') . '</th>
						<th class="SortedColumn">' . __('Average') . '</th>
						<th class="SortedColumn">' . __('Maximum') . '</th>
					</tr>
				</thead>
				<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>' . $MyRow['concept'] . '</td>
					<td class="number">' . locale_number_format_kpi($MyRow['minimumvalue']) . '</td>
					<td class="number">' . locale_number_format_kpi($MyRow['averagevalue']) . '</td>
					<td class="number">' . locale_number_format_kpi($MyRow['maximumvalue']) . '</td>
					</tr>';
		}
		echo '</tbody></table>
			</div>';
	}
}

/**************************************************************************************************************
* StockByBrand
*
* Analyzes stock levels, sales trends, and inventory requirements for a specific brand
* 
* @param string $Brand - Brand code to analyze
* @param int $NumDays - Number of days for sales analysis
* @param int $OptimalDaysStock - Target number of days to maintain in stock
* @param bool $ShowFullDetails - Whether to show detailed information
* 
* @return void - Outputs HTML table and inserts KPI values
**************************************************************************************************************/
function StockByBrand($Brand, $NumDays, $OptimalDaysStock, $ShowFullDetails){
	
	$BrandText= BrandTextFromCode($Brand);
	$BrandCode = substr($Brand, -2); // Get the 2 rightmost characters of $Brand

	$Shops = NumberOfShops($Brand);
	$NextDaysLastYear = $OptimalDaysStock - $NumDays;
	
	$TotalModels  = TotalModels($Brand);
	$TotalItems   = TotalItems($Brand);

	/* Past $NumDays This Year*/
	$ToLastDaysThisYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$FromLastDaysThisYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays-1));
	$DailySoldItemsThisYearNumDays = NumItemsSoldPerBrand($Brand, $FromLastDaysThisYear, $ToLastDaysThisYear) / $NumDays;

	/* Past $NumDays days since yesterday one year ago */
	$ToLastDaysLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-366));
	$FromLastDaysLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays-366));
	$DailySoldItemsLastYearNumDays = NumItemsSoldPerBrand($Brand, $FromLastDaysLastYear, $ToLastDaysLastYear) / $NumDays;
	
	/* Trend during the past $NumDays compared to same period last year */
	$TrendThisYear = ($DailySoldItemsThisYearNumDays - $DailySoldItemsLastYearNumDays) / $DailySoldItemsLastYearNumDays;

	/* Next $NumDays future days since today one year ago */
	$ToNextDaysLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-365+$NumDays));
	$FromNextDaysLastYear = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-365));

	if ($Brand == "SHOPKL" OR $Brand == "SHOPBL") {
		$DisplayItems = TotalDisplayItems($Brand);
		$AvailableForSaleItems = $TotalItems - $DisplayItems;
		$DailySoldItemsLastYearNextDays = NumItemsSoldPerBrand($Brand, $FromNextDaysLastYear, $ToNextDaysLastYear) / $NumDays;
		$ItemsToBeSoldNextDaysBasedOnTrendLastYear = $DailySoldItemsLastYearNextDays * ($TrendThisYear+1);
		$EstimationDailyItemsToBeSoldNextDays = max($DailySoldItemsThisYearNumDays, $ItemsToBeSoldNextDaysBasedOnTrendLastYear);
	} else {
		$DisplayItems = 0; // for discounted items we don't want to keep enough for display, we want to get rid of them
		$AvailableForSaleItems = $TotalItems ;
		$EstimationDailyItemsToBeSoldNextDays = $DailySoldItemsThisYearNumDays;
	}
	$OptimalStock = $EstimationDailyItemsToBeSoldNextDays * $OptimalDaysStock;
	$DaysStockForSale = $AvailableForSaleItems / $EstimationDailyItemsToBeSoldNextDays;
	$ItemsPO = TotalItemsToBeReceivedByPO($Brand);
	$ItemsWO = TotalItemsToBeReceivedByWO($Brand);
	$DaysStockForSaleIncludingPOWO = ($AvailableForSaleItems + $ItemsPO + $ItemsWO) / $EstimationDailyItemsToBeSoldNextDays;
	
	if ($DaysStockForSaleIncludingPOWO < $OptimalDaysStock){
		$ItemsToGetOptimalDaysStock = ($OptimalDaysStock - $DaysStockForSaleIncludingPOWO) * $EstimationDailyItemsToBeSoldNextDays; 
	} else {
		$ItemsToGetOptimalDaysStock = 0;
	}
	
	$TableTitleText = __('Stock for Brand ' . $BrandText);
	ShowTableTitle($TableTitleText);

	echo '<div>';
	echo '<table>
			<thead>
				<tr>
					<th>' . 'Concept' . '</th>
					<th>' . 'Value' . '</th>
				</tr>
			</thead>
			<tbody>';

	echo '<tr>
			<td># Shops Open</td>
			<td class="number">' . locale_number_format($Shops,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Total Models (MODELS)</td>
			<td class="number">' . locale_number_format($TotalModels,0) . '</td>
			</tr>';
	echo '<tr>
			<td>Total Stock (PCS)</td>
			<td class="number">' . locale_number_format($TotalItems,0) . '</td>
			</tr>';

	if ($Brand == "SHOPKL" OR $Brand == "SHOPBL"){
		echo '<tr>
			<td>Stock needed for display (PCS)</td>
			<td class="number">' . locale_number_format($DisplayItems,0) . '</td>
			</tr>';
	}

	if ($ShowFullDetails){
		echo '<tr>
				<td>Daily Stock sold last ' . $NumDays . ' days ' . 
				ConvertSQLDate($FromLastDaysThisYear) . '-' .
				ConvertSQLDate($ToLastDaysThisYear). ' (PCS)</td>
				<td class="number">' . locale_number_format($DailySoldItemsThisYearNumDays,0) . '</td>
				</tr>';
	}

	if ($ShowFullDetails AND ($Brand == "SHOPKL" OR $Brand == "SHOPBL")){
		echo '<tr>
				<td>Daily Stock sold same last ' . $NumDays . ' days last year ' . 
				ConvertSQLDate($FromLastDaysLastYear) . '-' .
				ConvertSQLDate($ToLastDaysLastYear). ' (PCS)</td>
				<td class="number">' . locale_number_format($DailySoldItemsLastYearNumDays,0) . '</td>
				</tr>';
	}
	
	if ($Brand == "SHOPKL" OR $Brand == "SHOPBL"){
		echo '<tr>
				<td>Retail trend from same days last year (%)</td>
				<td class="number">' . locale_number_format($TrendThisYear*100,1). '%</td>
				</tr>';
	}
	
	if ($ShowFullDetails AND ($Brand == "SHOPKL" OR $Brand == "SHOPBL")){
		echo '<tr>
				<td>Daily Stock sold next ' . $NumDays . ' days last year ' . 
				ConvertSQLDate($FromNextDaysLastYear) . '-' .
				ConvertSQLDate($ToNextDaysLastYear). ' (PCS)</td>
				<td class="number">' . locale_number_format($DailySoldItemsLastYearNextDays,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Estimated Daily Items to be sold next ' . $NumDays . ' days based on trend (PCS)</td>
				<td class="number">' . locale_number_format($ItemsToBeSoldNextDaysBasedOnTrendLastYear,0) . '</td>
				</tr>';
	}

	echo '<tr>
			<td>Estimated Daily Items to be sold next ' . $NumDays . ' days  (PCS)</td>
			<td class="number">' . locale_number_format($EstimationDailyItemsToBeSoldNextDays,0) . '</td>
			</tr>';

	if ($Brand == "SHOPKL" OR $Brand == "SHOPBL"){
		echo '<tr>
				<td>Optimal Stock available for sale for next ' . $OptimalDaysStock . ' days (PCS)</td>
				<td class="number">' . locale_number_format($OptimalStock,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Current Stock available for sale (PCS)</td>
				<td class="number">' . locale_number_format($AvailableForSaleItems,0) . '</td>
				</tr>';
	}

	echo '<tr>
			<td>Days left with current stock (DAYS)</td>
			<td class="number">' . locale_number_format($DaysStockForSale,0) . '</td>
			</tr>';

	if ($Brand == "SHOPKL" OR $Brand == "SHOPBL"){
		echo '<tr>
				<td>Pending Stock to achieve Optimal Stock for next ' . $OptimalDaysStock . ' days (PCS)</td>
				<td class="number">' . locale_number_format($OptimalStock - $AvailableForSaleItems,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Stock to be received by PO (PCS)</td>
				<td class="number">' . locale_number_format($ItemsPO,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Stock to be received by WO (PCS)</td>
				<td class="number">' . locale_number_format($ItemsWO,0) . '</td>
				</tr>';
		echo '<tr>
				<td>Days left of stock including PO & WO (DAYS)</td>
				<td class="number">' . locale_number_format($DaysStockForSaleIncludingPOWO,0) . '</td>
				</tr>';
		echo '<tr class="striped_row">
				<td>ACTION: Stock needed to reach ' . $OptimalDaysStock . ' days of optimal stock+PO+WO (PCS)</td>
				<td class="number">' . locale_number_format($ItemsToGetOptimalDaysStock,0) . '</td>
				</tr>';
	}
	echo '</tbody></table>
			</div>
			</form>';

	if ($Brand == "SHOPKL" OR $Brand == "SHOPBL" OR $Brand == "SHOPOU"){
		InsertKPI("SHOPS-OPEN-" . $BrandCode, $Shops);
		InsertKPI("STOCK-MODELS-" . $BrandCode, $TotalModels);
		InsertKPI("STOCK-TOTAL-PCS-" . $BrandCode, $TotalItems);
		InsertKPI("STOCK-DISPLAY-PCS-" . $BrandCode, $DisplayItems);
		InsertKPI("STOCK-FORSALE-PCS-" . $BrandCode, $AvailableForSaleItems);
		InsertKPI("STOCK-AV-PCS-MODEL-" . $BrandCode, round($AvailableForSaleItems/$TotalModels,2));
		InsertKPI("STOCK-AV-SOLD-" . $NumDays . "D-PCS-" . $BrandCode, $DailySoldItemsThisYearNumDays);
		InsertKPI("STOCK-FORECAST-" . $NumDays . "D-PCS-" . $BrandCode, $EstimationDailyItemsToBeSoldNextDays);
		InsertKPI("STOCK-QOH-DAYS-" .$BrandCode, $DaysStockForSale);
		InsertKPI("STOCK-PENDING-PO-PCS-" . $BrandCode, $ItemsPO);
		InsertKPI("STOCK-PENDING-WO-PCS-" . $BrandCode, $ItemsWO);
		InsertKPI("STOCK-QOH-PO-WO-DAYS-" .$BrandCode, $DaysStockForSaleIncludingPOWO);
		InsertKPI("STOCK-NEED-OPTIMAL-PCS-" . $BrandCode, $ItemsToGetOptimalDaysStock);
	}

	if ($Brand == "SHOPKL" OR $Brand == "SHOPBL"){
		InsertKPI("SALES-TREND-RETAIL-". $NumDays . "D-" . $BrandCode, $TrendThisYear*100);
	}
}

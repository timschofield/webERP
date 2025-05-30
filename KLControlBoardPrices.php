<?php

include ('includes/session.php');
$Title = _('KL Pricing Control Board');
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;
$IssuesFound = 0;

$PeriodNow=GetPeriod(Date($_SESSION['DefaultDateFormat']));

/* Assign the sections to be executed, to avoid error 504*/
$ShowSectionInfo = FALSE;
$ProcessSection01 = FALSE;
$ProcessSection02 = FALSE;

if (!isset($_GET['Section'])){
	$ProcessSection01 = TRUE;
	$ProcessSection02 = TRUE;
}else{
	$ShowSectionInfo = TRUE;
	if ($_GET['Section'] == '01'){
		$ProcessSection01 = TRUE;
	}elseif($_GET['Section'] == '02'){
		$ProcessSection02 = TRUE;
	}
}

/***************************************************************************************
* TEST AND PLAY AREA      
***************************************************************************************/

if ($_SESSION['UserID'] == "Ricard"){
//	$KL_SystemAdmin = TRUE;
//	$KL_OperationalManager = TRUE;
//	$KL_OperationalLeader = TRUE;
//	$KL_AdministrationTeam = TRUE;
//	$KL_BusinessDevelopmentManager = TRUE;
//	$KL_PurchasingTeam = TRUE;
//	$KL_ShopSupportTeam = TRUE;
//	$KL_ShopSupportLeader = TRUE;
//	$KL_OnlineSales = TRUE;
//	$KL_ShopManager = TRUE;
//	$KL_SPGSeniorOrSupport = TRUE;
//	$KL_SPGJunior = TRUE;
//	$KL_PettyCash = TRUE;
//	$KL_ITSupport = TRUE;
//	phpinfo();
}

/***************************************************************************************
* SECTION 1         
***************************************************************************************/
if ($ProcessSection01){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 01",'info');
	}
	/***************************************************************************************
	* RETAIL PRICE         
	***************************************************************************************/
	ShowTotalItemsMoving();
	
	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		
		ItemsWithoutRetailPrice("SETKLA", MINIMUM_PRICE_FACTOR_KL, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("TESTKA", MINIMUM_PRICE_FACTOR_KL, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("STABKA", MINIMUM_PRICE_FACTOR_KL, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("NOPOKA", MINIMUM_PRICE_FACTOR_KL, $RootPath);
		$NumberOfTestExecuted++;

		ItemsWithoutRetailPrice("SETBLA", MINIMUM_PRICE_FACTOR_BLINK, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("TESTBA", MINIMUM_PRICE_FACTOR_BLINK, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("STABBA", MINIMUM_PRICE_FACTOR_BLINK, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("NOPOBA", MINIMUM_PRICE_FACTOR_BLINK, $RootPath);
		$NumberOfTestExecuted++;

		ItemsWithoutRetailPrice("SETGEA", MINIMUM_PRICE_FACTOR_GENERAL, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("TESTGA", MINIMUM_PRICE_FACTOR_GENERAL, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("STABGA", MINIMUM_PRICE_FACTOR_GENERAL, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsWithoutRetailPrice("NOPOGA", MINIMUM_PRICE_FACTOR_GENERAL, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$IssuesFound += PriceBelowStandard("SETKLA", MINIMUM_PRICE_FACTOR_KL, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("TESTKA", MINIMUM_PRICE_FACTOR_KL, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("STABKA", MINIMUM_PRICE_FACTOR_KL, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("NOPOKA", MINIMUM_PRICE_FACTOR_KL, 0, $RootPath);
		$NumberOfTestExecuted++;

		$IssuesFound += PriceBelowStandard("SETBLA", MINIMUM_PRICE_FACTOR_BLINK, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("TESTBA", MINIMUM_PRICE_FACTOR_BLINK, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("STABBA", MINIMUM_PRICE_FACTOR_BLINK, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("NOPOBA", MINIMUM_PRICE_FACTOR_BLINK, 0, $RootPath);
		$NumberOfTestExecuted++;

		$IssuesFound += PriceBelowStandard("SETGEA", MINIMUM_PRICE_FACTOR_GENERAL, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("TESTGA", MINIMUM_PRICE_FACTOR_GENERAL, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("STABGA", MINIMUM_PRICE_FACTOR_GENERAL, 0, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceBelowStandard("NOPOGA", MINIMUM_PRICE_FACTOR_GENERAL, 0, $RootPath);
		$NumberOfTestExecuted++;
	}
}

/***************************************************************************************
* SECTION 2
***************************************************************************************/

if ($ProcessSection02){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 02",'info');
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$IssuesFound += ItemsTooCheap("TESTKA", MINIMUM_PRICE_FACTOR_KL, MINIMUM_PRICE_FACTOR_TOPSALES_KL, 0, 50, 60, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsTooCheap("STABKA", MINIMUM_PRICE_FACTOR_KL, MINIMUM_PRICE_FACTOR_TOPSALES_KL, 0, 50, 60, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsTooCheap("NOPOKA", MINIMUM_PRICE_FACTOR_KL, MINIMUM_PRICE_FACTOR_TOPSALES_KL, 0, 50, 60, $RootPath);
		$NumberOfTestExecuted++;

		$IssuesFound += ItemsTooCheap("TESTBA", MINIMUM_PRICE_FACTOR_BLINK, MINIMUM_PRICE_FACTOR_TOPSALES_BLINK, 0, 40, 60, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsTooCheap("STABBA", MINIMUM_PRICE_FACTOR_BLINK, MINIMUM_PRICE_FACTOR_TOPSALES_BLINK, 0, 40, 60, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsTooCheap("NOPOBA", MINIMUM_PRICE_FACTOR_BLINK, MINIMUM_PRICE_FACTOR_TOPSALES_BLINK, 0, 40, 60, $RootPath);
		$NumberOfTestExecuted++;

		$IssuesFound += ItemsTooExpensive("TESTKA", MINIMUM_PRICE_FACTOR_KL, MAXIMUM_PRICE_FACTOR_BOTTOMSALES_KL, 0, 500, 60, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsTooExpensive("STABKA", MINIMUM_PRICE_FACTOR_KL, MAXIMUM_PRICE_FACTOR_BOTTOMSALES_KL, 0, 500, 60, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsTooExpensive("NOPOKA", MINIMUM_PRICE_FACTOR_KL, MAXIMUM_PRICE_FACTOR_BOTTOMSALES_KL, 0, 500, 60, $RootPath);
		$NumberOfTestExecuted++;

		$IssuesFound += ItemsTooExpensive("TESTBA", MINIMUM_PRICE_FACTOR_BLINK, MAXIMUM_PRICE_FACTOR_BOTTOMSALES_BLINK, 0, 300, 60, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsTooExpensive("STABBA", MINIMUM_PRICE_FACTOR_BLINK, MAXIMUM_PRICE_FACTOR_BOTTOMSALES_BLINK, 0, 300, 60, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += ItemsTooExpensive("NOPOBA", MINIMUM_PRICE_FACTOR_BLINK, MAXIMUM_PRICE_FACTOR_BOTTOMSALES_BLINK, 0, 300, 60, $RootPath);
		$NumberOfTestExecuted++;
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager
		OR $KL_SalesDirector){
		$IssuesFound += PricesTooOld(3, 10, 20, $RootPath);
		$NumberOfTestExecuted++;
		$IssuesFound += PriceWrongRounding($RootPath);
		$NumberOfTestExecuted++;
	}
}

prnMsg("Performed ". $NumberOfTestExecuted . " pricing control tests",'success');
prnMsg("Detected ". $IssuesFound . " pricing issues",'success');
InsertKPI("PRICE-ISSUES", $IssuesFound);

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include ('includes/footer.php');

/********************************************************************************************
FUNCTIONS ONLY USED IN PRICING CONTROL BOARD
*********************************************************************************************/

function ItemsTooCheap($Stockcat, $FactorMin, $FactorMax, $MinQoh, $TopSales, $DaysTopSales, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysTopSales));
	$Issues = 0;
	
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
				(SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) AS qoh,
				prices.startdate,
				prices.price AS retailprice,
				(stockmaster.actualcost) AS standardcost
			FROM stockmaster, prices				
			WHERE stockmaster.stockid = prices.stockid	
				AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
				AND prices.currabrev = '". CURRENCY_CODE ."'
				AND prices.startdate <= CURRENT_DATE 
				AND prices.enddate >= CURRENT_DATE
				AND stockmaster.categoryid = '". $Stockcat ."'					
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.lastcategoryupdate <= '". $StartDate."'
				AND ((SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) >= " . $MinQoh . ")
				AND (prices.price < ((stockmaster.actualcost) * ". $FactorMax . "))";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$ShowHeader = TRUE;
		while ($MyRow = DB_fetch_array($Result)) {
			$PositionTopSales = PositionTopSalesItem($MyRow['stockid'], $DaysTopSales);
			if ($PositionTopSales < $TopSales){

				$MaxPrice = $MyRow['standardcost'] * $FactorMax;
				$MinPrice = $MyRow['standardcost'] * $FactorMin;
				$RecommendedPrice = round_price($MaxPrice, "UP");
				$Increase = locale_number_format(($RecommendedPrice-$MyRow['retailprice'])/$MyRow['retailprice']*100,1).'%';
				$IncomeIncrease = $MyRow['qoh'] * ($RecommendedPrice-$MyRow['retailprice']);
				
				// due to rounding in recommended price, sometimes recommended price is equal to current price, so we filter them
				if ($MyRow['retailprice'] != $RecommendedPrice){
					if ($ShowHeader){
						$CategoryName = GetCategoryNameFromCode($Stockcat);
						$TableTitleText = $CategoryName . ' Items TOO CHEAP: ' . ' TOP '.locale_number_format($TopSales,0) . ' sales. Price BELOW ' . $FactorMax . ' x standard cost. QOH >= ' .  locale_number_format($MinQoh,0);
						ShowTableTitle($TableTitleText);
						echo '<div>';
						echo '<table class="selection">
								<thead>
									<tr>
										<th class="SortedColumn">' . _('#') . '</th>
										<th class="SortedColumn">' . _('Code') . '</th>
										<th class="SortedColumn">' . _('Description') . '</th>
										<th class="SortedColumn">' . _('TopSales') . '</th>
										<th class="SortedColumn">' . _('QOH') . '</th>
										<th class="SortedColumn">' . _('QOO') . '</th>
										<th class="SortedColumn">' . _('Std Cost') . '</th>
										<th class="SortedColumn">' . _('Minimum Price') . '</th>
										<th class="SortedColumn">' . _('Date Price') . '</th>
										<th class="SortedColumn">' . _('Current Price') . '</th>
										<th class="SortedColumn">' . _('Current Factor') . '</th>
										<th class="SortedColumn">' . _('Optimum Price') . '</th>
										<th class="SortedColumn">' . _('Recommended Retail') . '</th>
										<th class="SortedColumn">' . _('% Increase') . '</th>
										<th class="SortedColumn">' . _('Income Increase') . '</th>
									</tr>
								</thead>
								<tbody>';
						$ShowHeader = FALSE;
					}
					$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
					$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
					$QOO = GetQuantityOnOrder($MyRow['stockid'], '');
					
					$Issues++;
					echo '<tr class="striped_row">
							<td class="number">' . $Issues . '</td>
							<td>' . $CodeLink . '</td>
							<td>' . $MyRow['description'] . '</td>
							<td class="number">' . locale_number_format($PositionTopSales,0) . '</td>
							<td class="number">' . locale_number_format($MyRow['qoh'],0) . '</td>
							<td class="number">' . locale_number_format($QOO,0) . '</td>
							<td class="number">' . locale_number_format($MyRow['standardcost'],0) . '</td>
							<td class="number">' . locale_number_format($MinPrice,0) . '</td>
							<td>' . ConvertSQLDate($MyRow['startdate']) . '</td>
							<td class="number">' . locale_number_format($MyRow['retailprice'],0) . '</td>
							<td class="number">' . locale_number_format($MyRow['retailprice']/$MyRow['standardcost'],2) . '</td>
							<td class="number">' . locale_number_format($MaxPrice,0) . '</td>
							<td class="number">' . $NewPriceLink . '</td>
							<td>' . $Increase . '</td>
							<td class="number">' . locale_number_format($IncomeIncrease,0) . '</td>
							</tr>';
				}
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
	return $Issues;
}

function ItemsTooExpensive($Stockcat, $FactorMin, $FactorMax, $MinQoh, $TopSales, $DaysTopSales, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysTopSales));
	$Issues = 0;
	
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
				(SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) AS qoh,
				prices.startdate,
				prices.price AS retailprice,
				(stockmaster.actualcost) AS standardcost
			FROM stockmaster, prices				
			WHERE stockmaster.stockid = prices.stockid	
				AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
				AND prices.currabrev = '". CURRENCY_CODE ."'
				AND prices.startdate <= '". $StartDate. "' 
				AND prices.startdate <= CURRENT_DATE 
				AND prices.enddate >= CURRENT_DATE
				AND stockmaster.categoryid = '". $Stockcat ."'					
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.lastcategoryupdate <= '". $StartDate."'
				AND ((SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) >= " . $MinQoh . ")
				AND (prices.price > " . SMALL_PRICE_CALCULATED_STEP04 . ") 
				AND (prices.price > ((stockmaster.actualcost) * " . $FactorMax . "))";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
			$ShowHeader = TRUE;
		while ($MyRow = DB_fetch_array($Result)) {
			$PositionTopSales = PositionTopSalesItem($MyRow['stockid'], $DaysTopSales);
			$MaxPrice = $MyRow['standardcost'] * $FactorMax;
			$MinPrice = $MyRow['standardcost'] * $FactorMin;
			$RecommendedPrice = round_price($MaxPrice, "UP");
			$Decrease = locale_number_format(($RecommendedPrice-$MyRow['retailprice'])/$MyRow['retailprice']*100,1).'%';
			$IncomeDecrease = $MyRow['qoh'] * ($RecommendedPrice-$MyRow['retailprice']);
			if (($PositionTopSales > $TopSales) AND 
				($RecommendedPrice < $MyRow['retailprice'])){
				if ($ShowHeader){
					$CategoryName = GetCategoryNameFromCode($Stockcat);
					$TableTitleText = $CategoryName . ' Items TOO EXPENSIVE: ' . ' NO TOP '.locale_number_format($TopSales,0) . ' sales. Retail Price OVER ' . $FactorMax . _(' x standard cost. ') . 'QOH >= ' .  locale_number_format($MinQoh,0);
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('TopSales') . '</th>
									<th class="SortedColumn">' . _('QOH') . '</th>
									<th class="SortedColumn">' . _('QOO') . '</th>
									<th class="SortedColumn">' . _('Std Cost') . '</th>
									<th class="SortedColumn">' . _('Minimum Price') . '</th>
									<th class="SortedColumn">' . _('Date Price') . '</th>
									<th class="SortedColumn">' . _('Current Price') . '</th>
									<th class="SortedColumn">' . _('Current Factor') . '</th>
									<th class="SortedColumn">' . _('Optimum Price') . '</th>
									<th class="SortedColumn">' . _('Recommended Retail') . '</th>
									<th class="SortedColumn">' . _('% Decrease') . '</th>
									<th class="SortedColumn">' . _('Income Decrease') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
				$QOO = GetQuantityOnOrder($MyRow['stockid'], '');

				$Issues++;
				echo '<tr class="striped_row">
						<td class="number">' . $Issues . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($PositionTopSales,0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qoh'],0) . '</td>
						<td class="number">' . locale_number_format($QOO,0) . '</td>
						<td class="number">' . locale_number_format($MyRow['standardcost'],0) . '</td>
						<td class="number">' . locale_number_format($MinPrice,0) . '</td>
						<td>' . ConvertSQLDate($MyRow['startdate']) . '</td>
						<td class="number">' . locale_number_format($MyRow['retailprice'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['retailprice']/$MyRow['standardcost'],2) . '</td>
						<td class="number">' . locale_number_format($MaxPrice,0) . '</td>
						<td class="number">' . $NewPriceLink . '</td>
						<td>' . $Decrease . '</td>
						<td class="number">' . locale_number_format($IncomeDecrease,0) . '</td>
						</tr>';
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
	return $Issues;
}

function PriceBelowStandard($Stockcat, $Factor, $MinQoh, $RootPath){
	$Issues = 0;
	
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
				(SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) AS qoh,
				prices.startdate,
				prices.price AS retailprice,
				(stockmaster.actualcost) AS standardcost
			FROM stockmaster, prices				
			WHERE stockmaster.stockid = prices.stockid	
				AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
				AND prices.currabrev = '". CURRENCY_CODE ."'
				AND prices.startdate <= CURRENT_DATE 
				AND (prices.enddate >= CURRENT_DATE)
				AND stockmaster.categoryid = '". $Stockcat ."'					
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND ((SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) >= " . $MinQoh . ")
				AND (prices.price < ((stockmaster.actualcost) * ". $Factor ."))
				AND NOT EXISTS (SELECT * 					
					FROM prices	
					WHERE stockmaster.stockid = prices.stockid	
						AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
						AND prices.currabrev = '". CURRENCY_CODE ."'
						AND prices.startdate > CURRENT_DATE)
			ORDER BY (prices.price / (stockmaster.actualcost))";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$i = 0;
		$ShowHeader = TRUE;
		while ($MyRow = DB_fetch_array($Result)) {
			$NewPrice = $MyRow['standardcost'] * $Factor;
			$RecommendedPrice = round_price($NewPrice, "UP");
			if ($MyRow['retailprice'] != $RecommendedPrice){
				if ($ShowHeader){
					$CategoryName = GetCategoryNameFromCode($Stockcat);
					$TableTitleText = $CategoryName . _(' Items with retail price below minimum. ') . $Factor . _(' x standard cost. ') .  'QOH >= ' .  locale_number_format($MinQoh,0);
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('TopSales') . '</th>
									<th class="SortedColumn">' . _('QOH') . '</th>
									<th class="SortedColumn">' . _('QOO') . '</th>
									<th class="SortedColumn">' . _('Std Cost') . '</th>
									<th class="SortedColumn">' . _('Date Price') . '</th>
									<th class="SortedColumn">' . _('Current Price') . '</th>
									<th class="SortedColumn">' . _('Current Factor') . '</th>
									<th class="SortedColumn">' . _('Minimum Price') . '</th>
									<th class="SortedColumn">' . _('Recommended Retail') . '</th>
									<th class="SortedColumn">' . _('% Increase') . '</th>
									<th class="SortedColumn">' . _('Income Increase') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				$Issues++;
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				$Increase = locale_number_format(($RecommendedPrice-$MyRow['retailprice'])/$MyRow['retailprice']*100,1).'%';
				$PositionTopSales = PositionTopSalesItem($MyRow['stockid'], 60);
				$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
				$QOO = GetQuantityOnOrder($MyRow['stockid'], '');
				$IncomeIncrease = ($MyRow['qoh'] + $QOO) * ($RecommendedPrice-$MyRow['retailprice']);

				echo '<tr class="striped_row">
						<td class="number">' . $Issues . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($PositionTopSales,0) . '</td>
						<td class="number">' . locale_number_format($MyRow['qoh'],0) . '</td>
						<td class="number">' . locale_number_format($QOO,0) . '</td>
						<td class="number">' . locale_number_format($MyRow['standardcost'],0) . '</td>
						<td>' . ConvertSQLDate($MyRow['startdate']) . '</td>
						<td class="number">' . locale_number_format($MyRow['retailprice'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['retailprice']/$MyRow['standardcost'],2) . '</td>
						<td class="number">' . locale_number_format($NewPrice,0) . '</td>
						<td class="number">' . $NewPriceLink . '</td>
						<td>' . $Increase . '</td>
						<td class="number">' . locale_number_format($IncomeIncrease,0) . '</td>
						</tr>';
			}
		}
		if(!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
	return $Issues;
}

function PriceWrongRounding($RootPath){
	$Issues = 0;

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
				(SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) AS qoh,
				(SELECT price 					
					FROM prices	
					WHERE stockmaster.stockid = prices.stockid	
						AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
						AND prices.currabrev = '". CURRENCY_CODE ."'
						AND prices.startdate <= CURRENT_DATE 
						AND prices.enddate >= CURRENT_DATE
					LIMIT 1) AS retailprice,
				(stockmaster.actualcost) AS standardcost
			FROM stockmaster				
			WHERE stockmaster.discontinued = 0
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING . ")
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_GENERAL . "
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
			ORDER BY stockmaster.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$k = 0; //row colour counter
		$ShowHeader = TRUE;
		while ($MyRow = DB_fetch_array($Result)) {
			$RoundedDown = round_price($MyRow['retailprice'], "DOWN");
			$RoundedUp = round_price($MyRow['retailprice'], "UP");
			
			if(!IsPriceRoundedOK($MyRow['retailprice'])){
				if($ShowHeader){
					$TableTitleText = _('Items with WRONG rounding retail price.');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('Top Sales') . '</th>
									<th class="SortedColumn">' . _('QOH') . '</th>
									<th class="SortedColumn">' . _('QOO') . '</th>
									<th class="SortedColumn">' . _('Rounded Down') . '</th>
									<th class="SortedColumn">' . _('Current Price') . '</th>
									<th class="SortedColumn">' . _('Rounded Up') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				$DownPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $RoundedDown .  '">' . locale_number_format($RoundedDown,0) . '</a>';
				$UpPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $RoundedUp .  '">' . locale_number_format($RoundedUp,0) . '</a>';
				$PositionTopSales = PositionTopSalesItem($MyRow['stockid'], 60);
				$QOO = GetQuantityOnOrder($MyRow['stockid'], '');
				$Issues++;
				
				echo '<tr class="striped_row">
						<td class="number">' . $Issues . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . $PositionTopSales . '</td>
						<td class="number">' . locale_number_format($MyRow['qoh'],0) . '</td>
						<td class="number">' . locale_number_format($QOO,0) . '</td>
						<td class="number">' . $DownPriceLink . '</td>
						<td class="number">' . locale_number_format($MyRow['retailprice'],0) . '</td>
						<td class="number">' . $UpPriceLink . '</td>
						</tr>';
			}
		}
		if(!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
	return $Issues;
}

function PricesTooOld($Years, $IncreaseA, $IncreaseB, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$Years * 365));
	$Issues = 0;

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
				(SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) AS qoh,
				prices.startdate,
				prices.price AS retailprice,
				(stockmaster.actualcost) AS standardcost
			FROM stockmaster, prices				
			WHERE stockmaster.stockid = prices.stockid
				AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
				AND prices.currabrev = '". CURRENCY_CODE ."'
				AND prices.startdate <= '" . $StartDate . "' 
				AND prices.enddate >= CURRENT_DATE
				AND stockmaster.discontinued = 0
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING . ")
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
			ORDER BY prices.startdate";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
			$ShowHeader = TRUE;
		while ($MyRow = DB_fetch_array($Result)) {
			if($ShowHeader){
				$TableTitleText = _('Items with prices older than ') . $Years . ' years';
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th class="SortedColumn">' . _('Top Sales') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('QOO') . '</th>
								<th class="SortedColumn">' . _('Standard Cost') . '</th>
								<th class="SortedColumn">' . _('Price Date') . '</th>
								<th class="SortedColumn">' . _('Current Price') . '</th>
								<th class="SortedColumn">' . _('Current Factor') . '</th>
								<th class="SortedColumn">' . _('Increase ') . $IncreaseA. '%' . '</th>
								<th class="SortedColumn">' . _('Increase ') . $IncreaseB. '%' . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$PriceA = round_price(($MyRow['retailprice']*(1+($IncreaseA/100))), "UP");
			$PriceB = round_price(($MyRow['retailprice']*(1+($IncreaseB/100))), "UP");
			$PriceALink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $PriceA .  '">' . locale_number_format($PriceA,0) . '</a>';
			$PriceBLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $PriceB .  '">' . locale_number_format($PriceB,0) . '</a>';
			$PositionTopSales = PositionTopSalesItem($MyRow['stockid'], 60);
			$QOO = GetQuantityOnOrder($MyRow['stockid'], '');
			$Issues++;
			
			echo '<tr class="striped_row">
					<td class="number">' . $Issues . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . $PositionTopSales . '</td>
					<td class="number">' . locale_number_format($MyRow['qoh'],0) . '</td>
					<td class="number">' . locale_number_format($QOO,0) . '</td>
					<td class="number">' . locale_number_format($MyRow['standardcost'],0) . '</td>
					<td>' . ConvertSQLDate($MyRow['startdate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['retailprice'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['retailprice']/$MyRow['standardcost'],2) . '</td>
					<td class="number">' . $PriceALink . '</td>
					<td class="number">' . $PriceBLink . '</td>
					</tr>';
		}
		if(!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
	return $Issues;
}

?>
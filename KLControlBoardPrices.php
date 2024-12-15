<?php
define("VERSIONFILE", "5.01");

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include ('includes/session.php');
$Title = _('Kapal-Laut Pricing Control Board '. VERSIONFILE);
include ('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/SQL_CommonFunctions.inc');

/* ASSIGN users to groups */
include ('includes/KLRoles.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;
$IssuesFound = 0;

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']));

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
InsertKPI("Prices", "Pricing Issues", $IssuesFound);

if ($KL_SystemAdmin){
	time_finish($begintime);
}

include ('includes/footer.php');

/********************************************************************************************
FUNCTIONS ONLY USED IN PRICING CONTROL BOARD
*********************************************************************************************/

function ItemsTooCheap($Stockcat, $FactorMin, $FactorMax, $MinQoh, $TopSales, $DaysTopSales, $RootPath){
	$today = date('Y-m-d');
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysTopSales));
	$issues = 0;
	
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
				AND prices.startdate <= '". $today. "' 
				AND (prices.enddate >= '". $today. "' OR prices.enddate = '9999-12-31')
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

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = PositionTopSalesItem($myrow['stockid'], $DaysTopSales);
			if ($PositionTopSales < $TopSales){

				$MaxPrice = $myrow['standardcost'] * $FactorMax;
				$MinPrice = $myrow['standardcost'] * $FactorMin;
				$RecommendedPrice = round_price($MaxPrice, "UP");
				$Increase = locale_number_format(($RecommendedPrice-$myrow['retailprice'])/$myrow['retailprice']*100,1).'%';
				$IncomeIncrease = $myrow['qoh'] * ($RecommendedPrice-$myrow['retailprice']);
				
				// due to rounding in recommended price, sometimes recommended price is equal to current price, so we filter them
				if ($myrow['retailprice'] != $RecommendedPrice){
					if ($ShowHeader){
						$CategoryName = GetCategoryNameFromCode($Stockcat);
						echo '<p class="page_title_text" align="center"><strong>' .  $CategoryName . ' Items TOO CHEAP: ' . ' TOP '.locale_number_format($TopSales,0) . ' sales. Price BELOW ' . $FactorMax . ' x standard cost. QOH >= ' .  locale_number_format($MinQoh,0).  '</strong></p>';
						echo '<div>';
						echo '<table class="selection">';
						$TableHeader = '<tr>
											<th class="ascending">' . _('#') . '</th>
											<th class="ascending">' . _('Code') . '</th>
											<th class="ascending">' . _('Description') . '</th>
											<th class="ascending">' . _('TopSales') . '</th>
											<th class="ascending">' . _('QOH') . '</th>
											<th class="ascending">' . _('QOO') . '</th>
											<th class="ascending">' . _('Std Cost') . '</th>
											<th class="ascending">' . _('Minimum Price') . '</th>
											<th class="ascending">' . _('Date Price') . '</th>
											<th class="ascending">' . _('Current Price') . '</th>
											<th class="ascending">' . _('Current Factor') . '</th>
											<th class="ascending">' . _('Optimum Price') . '</th>
											<th class="ascending">' . _('Recommended Retail') . '</th>
											<th class="ascending">' . _('% Increase') . '</th>
											<th class="ascending">' . _('Income Increase') . '</th>
										</tr>';
						echo $TableHeader;
						$ShowHeader = FALSE;
					}
					$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
					$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
					$QOO = GetQuantityOnOrderDueToPurchaseOrders($myrow['stockid'], '') 
						+ GetQuantityOnOrderDueToWorkOrders($myrow['stockid'], '');
					
					$k = StartEvenOrOddRow($k);
					$issues++;
					printf('<td class="number">%s</td>
							<td>%s</td>
							<td>%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td>%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td>%s</td>
							<td class="number">%s</td>
							</tr>', 
							$issues, 
							$CodeLink, 
							$myrow['description'], 
							locale_number_format($PositionTopSales,0),
							locale_number_format($myrow['qoh'],0),
							locale_number_format($QOO,0),
							locale_number_format($myrow['standardcost'],0),
							locale_number_format($MinPrice,0),
							ConvertSQLDateTime($myrow['startdate']), 
							locale_number_format($myrow['retailprice'],0),
							locale_number_format($myrow['retailprice']/$myrow['standardcost'],2),
							locale_number_format($MaxPrice,0),
							$NewPriceLink,
							$Increase,
							locale_number_format($IncomeIncrease,0)
							);
				}
			}
		}
		if (!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
	return $issues;
}

function ItemsTooExpensive($Stockcat, $FactorMin, $FactorMax, $MinQoh, $TopSales, $DaysTopSales, $RootPath){
	$today = date('Y-m-d');
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysTopSales));
	$issues = 0;
	
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
				AND prices.startdate <= '". $today. "' 
				AND (prices.enddate >= '". $today. "' OR prices.enddate = '9999-12-31')
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

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = PositionTopSalesItem($myrow['stockid'], $DaysTopSales);
			$MaxPrice = $myrow['standardcost'] * $FactorMax;
			$MinPrice = $myrow['standardcost'] * $FactorMin;
			$RecommendedPrice = round_price($MaxPrice, "UP");
			$Decrease = locale_number_format(($RecommendedPrice-$myrow['retailprice'])/$myrow['retailprice']*100,1).'%';
			$IncomeDecrease = $myrow['qoh'] * ($RecommendedPrice-$myrow['retailprice']);
			if (($PositionTopSales > $TopSales) AND 
				($RecommendedPrice < $myrow['retailprice'])){
				if ($ShowHeader){
					$CategoryName = GetCategoryNameFromCode($Stockcat);
					echo '<p class="page_title_text" align="center"><strong>' .  $CategoryName . ' Items TOO EXPENSIVE: ' . ' NO TOP '.locale_number_format($TopSales,0) . ' sales. Retail Price OVER ' . $FactorMax . _(' x standard cost. ') . 'QOH >= ' .  locale_number_format($MinQoh,0).  '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('TopSales') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('QOO') . '</th>
										<th class="ascending">' . _('Std Cost') . '</th>
										<th class="ascending">' . _('Minimum Price') . '</th>
										<th class="ascending">' . _('Date Price') . '</th>
										<th class="ascending">' . _('Current Price') . '</th>
										<th class="ascending">' . _('Current Factor') . '</th>
										<th class="ascending">' . _('Optimum Price') . '</th>
										<th class="ascending">' . _('Recommended Retail') . '</th>
										<th class="ascending">' . _('% Decrease') . '</th>
										<th class="ascending">' . _('Income Decrease') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
				$QOO = GetQuantityOnOrderDueToPurchaseOrders($myrow['stockid'], '') 
					+ GetQuantityOnOrderDueToWorkOrders($myrow['stockid'], '');

				$k = StartEvenOrOddRow($k);
				$issues++;
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$issues, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($PositionTopSales,0),
						locale_number_format($myrow['qoh'],0),
						locale_number_format($QOO,0),
						locale_number_format($myrow['standardcost'],0),
						locale_number_format($MinPrice,0),
						ConvertSQLDateTime($myrow['startdate']), 
						locale_number_format($myrow['retailprice'],0),
						locale_number_format($myrow['retailprice']/$myrow['standardcost'],2),
						locale_number_format($MaxPrice,0),
						$NewPriceLink,
						$Decrease,
						locale_number_format($IncomeDecrease,0)
						);
			}
		}
		if (!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
	return $issues;
}

function PriceBelowStandard($Stockcat, $Factor, $MinQoh, $RootPath){
	$today = date('Y-m-d');
	$issues = 0;
	
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
				AND prices.startdate <= '". $today. "' 
				AND (prices.enddate >= '". $today. "' OR prices.enddate = '9999-12-31')
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
						AND prices.startdate > '". $today. "')
			ORDER BY (prices.price / (stockmaster.actualcost))";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 0;
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			$NewPrice = $myrow['standardcost'] * $Factor;
			$RecommendedPrice = round_price($NewPrice, "UP");
			if ($myrow['retailprice'] != $RecommendedPrice){
				if ($ShowHeader){
					$CategoryName = GetCategoryNameFromCode($Stockcat);
					echo '<p class="page_title_text" align="center"><strong>' . $CategoryName . _(' Items with retail price below minimum. ') . $Factor . _(' x standard cost. ') .  'QOH >= ' .  locale_number_format($MinQoh,0). '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('TopSales') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('QOO') . '</th>
										<th class="ascending">' . _('Std Cost') . '</th>
										<th class="ascending">' . _('Date Price') . '</th>
										<th class="ascending">' . _('Current Price') . '</th>
										<th class="ascending">' . _('Current Factor') . '</th>
										<th class="ascending">' . _('Minimum Price') . '</th>
										<th class="ascending">' . _('Recommended Retail') . '</th>
										<th class="ascending">' . _('% Increase') . '</th>
										<th class="ascending">' . _('Income Increase') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$issues++;
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$Increase = locale_number_format(($RecommendedPrice-$myrow['retailprice'])/$myrow['retailprice']*100,1).'%';
				$PositionTopSales = PositionTopSalesItem($myrow['stockid'], 60);
				$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
				$QOO = GetQuantityOnOrderDueToPurchaseOrders($myrow['stockid'], '') 
					+ GetQuantityOnOrderDueToWorkOrders($myrow['stockid'], '');
				$IncomeIncrease = ($myrow['qoh'] + $QOO) * ($RecommendedPrice-$myrow['retailprice']);

				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$issues, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($PositionTopSales,0),
						locale_number_format($myrow['qoh'],0),
						locale_number_format($QOO,0),
						locale_number_format($myrow['standardcost'],0),
						ConvertSQLDateTime($myrow['startdate']), 
						locale_number_format($myrow['retailprice'],0),
						locale_number_format($myrow['retailprice']/$myrow['standardcost'],2),
						locale_number_format($NewPrice,0),
						$NewPriceLink,
						$Increase,
						locale_number_format($IncomeIncrease,0)
						);
			}
		}
		if(!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
	return $issues;
}

function PriceWrongRounding($RootPath){
	$today = date('Y-m-d');
	$issues = 0;

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
						AND prices.startdate <= '". $today. "' 
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '9999-12-31')
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

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			$RoundedDown = round_price($myrow['retailprice'], "DOWN");
			$RoundedUp = round_price($myrow['retailprice'], "UP");
			
			if(!IsPriceRoundedOK($myrow['retailprice'])){
				if($ShowHeader){
					echo '<p class="page_title_text" align="center"><strong>' . _('Items with WRONG rounding retail price.') . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Top Sales') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('QOO') . '</th>
										<th class="ascending">' . _('Rounded Down') . '</th>
										<th class="ascending">' . _('Current Price') . '</th>
										<th class="ascending">' . _('Rounded Up') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$DownPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $RoundedDown .  '">' . locale_number_format($RoundedDown,0) . '</a>';
				$UpPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $RoundedUp .  '">' . locale_number_format($RoundedUp,0) . '</a>';
				$PositionTopSales = PositionTopSalesItem($myrow['stockid'], 60);
				$QOO = GetQuantityOnOrderDueToPurchaseOrders($myrow['stockid'], '') 
					+ GetQuantityOnOrderDueToWorkOrders($myrow['stockid'], '');
				$k = StartEvenOrOddRow($k);
				$issues++;
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$issues, 
						$CodeLink, 
						$myrow['description'], 
						$PositionTopSales,
						locale_number_format($myrow['qoh'],0),
						locale_number_format($QOO,0),
						$DownPriceLink,
						locale_number_format($myrow['retailprice'],0),
						$UpPriceLink
						);
			}
		}
		if(!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
	return $issues;
}

function PricesTooOld($Years, $IncreaseA, $IncreaseB, $RootPath){
	$today = date('Y-m-d');
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$Years * 365));
	$issues = 0;

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
				AND (prices.enddate >= '". $today. "' OR prices.enddate = '9999-12-31')
				AND stockmaster.discontinued = 0
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING . ")
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
			ORDER BY prices.startdate";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			if($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . _('Items with prices older than ') . $Years . ' years' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th class="ascending">' . _('#') . '</th>
									<th class="ascending">' . _('Code') . '</th>
									<th class="ascending">' . _('Description') . '</th>
									<th class="ascending">' . _('Top Sales') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('QOO') . '</th>
									<th class="ascending">' . _('Standard Cost') . '</th>
									<th class="ascending">' . _('Price Date') . '</th>
									<th class="ascending">' . _('Current Price') . '</th>
									<th class="ascending">' . _('Current Factor') . '</th>
									<th class="ascending">' . _('Increase ') . $IncreaseA. '%' . '</th>
									<th class="ascending">' . _('Increase ') . $IncreaseB. '%' . '</th>
								</tr>';
				echo $TableHeader;
				$ShowHeader = FALSE;
			}
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$PriceA = round_price(($myrow['retailprice']*(1+($IncreaseA/100))), "UP");
			$PriceB = round_price(($myrow['retailprice']*(1+($IncreaseB/100))), "UP");
			$PriceALink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $PriceA .  '">' . locale_number_format($PriceA,0) . '</a>';
			$PriceBLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $PriceB .  '">' . locale_number_format($PriceB,0) . '</a>';
			$PositionTopSales = PositionTopSalesItem($myrow['stockid'], 60);
			$QOO = GetQuantityOnOrderDueToPurchaseOrders($myrow['stockid'], '') 
				+ GetQuantityOnOrderDueToWorkOrders($myrow['stockid'], '');
			$k = StartEvenOrOddRow($k);
			$issues++;
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$issues, 
					$CodeLink, 
					$myrow['description'], 
					$PositionTopSales,
					locale_number_format($myrow['qoh'],0),
					locale_number_format($QOO,0),
					locale_number_format($myrow['standardcost'],0),
					ConvertSQLDateTime($myrow['startdate']), 
					locale_number_format($myrow['retailprice'],0),
					locale_number_format($myrow['retailprice']/$myrow['standardcost'],2),
					$PriceALink,
					$PriceBLink
					);
		}
		if(!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
	return $issues;
}

?>
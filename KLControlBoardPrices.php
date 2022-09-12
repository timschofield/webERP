<?php
define("VERSIONFILE", "5.00");

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

/* Do the pending GL Postings to get the latest finantial control reports*/
include ('includes/GLPostings.inc');

/* ASSIGN users to groups */
include ('includes/KLRoles.php');

$begintime = time_start();
$NumberOfTestExecuted = 0;

$periodnow=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

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
* SECTION 1         
***************************************************************************************/
if ($ProcessSection01){
	if($ShowSectionInfo){
		prnMsg("Performing Control Panel Section 01",'info');
	}
	/***************************************************************************************
	* RETAIL PRICE         
	***************************************************************************************/

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		
	//	PricesNotUpdatedinXDays(365*2, 15, $RootPath, $db);
	//	PricesNotUpdatedinXDays(365  , 10, $RootPath, $db);

		ItemsWithoutRetailPrice("SETKLA", 6.00, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETBLA", 6.50, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("SETGEA", 4.40, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsWithoutRetailPrice("TESTKA", 6.00, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("TESTBA", 6.50, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("TESTGA", 4.40, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsWithoutRetailPrice("STABKA", 6.00, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("STABBA", 6.50, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("STABGA", 4.40, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsWithoutRetailPrice("NOPOKA", 6.00, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("NOPOBA", 6.50, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsWithoutRetailPrice("NOPOGA", 4.40, $RootPath, $db);
		$NumberOfTestExecuted++;

	//	ItemsWithoutRetailPrice("CONSIG", 1.60, $RootPath, $db);
	}

	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		PriceBelowStandard("SETKLA", 6.00, 0, 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("TESTKA", 6.00, 0, 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("STABKA", 6.00, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("NOPOKA", 6.00, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;

		PriceBelowStandard("SETBLA", 6.50, 0, 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("TESTBA", 6.50, 0, 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("STABBA", 6.50, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("NOPOBA", 6.50, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;

		PriceBelowStandard("SETGEA", 4.40, 0, 10, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("TESTGA", 4.40, 0, 15, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("STABGA", 4.40, 0, 20, $RootPath, $db);
		$NumberOfTestExecuted++;
		PriceBelowStandard("NOPOGA", 4.40, 0, 20, $RootPath, $db);
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
		OR $KL_BusinessDevelopmentManager){

		ItemsTooCheap("TESTKA", 6.00, 6.25, 0.05, 10, 80, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("STABKA", 6.00, 6.25, 0.05, 20, 80, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("NOPOKA", 6.00, 6.25, 0.05, 20, 80, 60, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsTooCheap("TESTBA", 6.50, 6.75, 0.05, 10, 80, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("STABBA", 6.50, 6.75, 0.05, 20, 80, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("NOPOBA", 6.50, 6.75, 0.05, 20, 80, 60, $RootPath, $db);
		$NumberOfTestExecuted++;

/*		ItemsTooCheap("TESTGA", 3.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("STABGA", 3.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooCheap("NOPOGA", 3.50, 5.75, 0.05, 10, 100, 60, $RootPath, $db);
		$NumberOfTestExecuted++;
*/
		ItemsTooExpensive("TESTKA", 6.00, 6.50, 0.05, 10, 300, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("STABKA", 6.00, 6.50, 0.05, 20, 300, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("NOPOKA", 6.00, 6.50, 0.05, 20, 300, 60, $RootPath, $db);
		$NumberOfTestExecuted++;

		ItemsTooExpensive("TESTBA", 6.50, 7.00, 0.05, 10, 200, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("STABBA", 6.50, 7.00, 0.05, 20, 200, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("NOPOBA", 6.50, 7.00, 0.05, 20, 200, 90, $RootPath, $db);
		$NumberOfTestExecuted++;

/*		ItemsTooExpensive("TESTGA", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("STABGA", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
		ItemsTooExpensive("NOPOGA", 5.50, 6.00, 0.05, 5, 800, 90, $RootPath, $db);
		$NumberOfTestExecuted++;
*/
	}


	if ($KL_SystemAdmin 
		OR $KL_BusinessDevelopmentManager){
		PriceWrongRounding($RootPath, $db);
		$NumberOfTestExecuted++;
	}
}

prnMsg("Performed ". $NumberOfTestExecuted . " pricing control tests",'success');
time_finish($begintime);
include ('includes/footer.php');

/********************************************************************************************
FUNCTIONS ONLY USED IN PRICING CONTROL BOARD
*********************************************************************************************/

function ItemsTooCheap($Stockcat, $FactorMin, $FactorMax, $Tolerance, $MinQoh, $TopSales, $DaysTopSales, $RootPath, $db){
	$today = date('Y-m-d');
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysTopSales));
	$FactorTolerance = 1 + $Tolerance;

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
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					LIMIT 1) AS retailprice,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS standardcost
			FROM stockmaster				
			WHERE stockmaster.categoryid = '". $Stockcat ."'					
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.lastcategoryupdate <= '". $StartDate."'
				AND ((SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) >= " . $MinQoh . ")
				AND ((SELECT price 					
					FROM prices	
					WHERE stockmaster.stockid = prices.stockid	
						AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
						AND prices.currabrev = '". CURRENCY_CODE ."'
						AND prices.startdate <= '". $today. "' 
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					LIMIT 1) < ((stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) * ". $FactorMax ." / ". $FactorTolerance ."))";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = PositionTopSalesItem($myrow['stockid'], $DaysTopSales, $db);
			if ($PositionTopSales < $TopSales){
				if ($ShowHeader){
					$CategoryName = GetCategoryNameFromCode($Stockcat);
					echo '<p class="page_title_text" align="center"><strong>' .  $CategoryName . ' Items TOO CHEAP: ' . ' TOP '.locale_number_format($TopSales,0) . ' sales. Price BELOW ' . $FactorMax . _(' x standard cost. Tolerance ') . locale_number_format($Tolerance * 100,0) . '%. QOH >= ' .  locale_number_format($MinQoh,0).  '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('TopSales') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('Std Cost') . '</th>
										<th class="ascending">' . _('Minimum Price') . '</th>
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
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$MaxPrice = $myrow['standardcost'] * $FactorMax;
				$MinPrice = $myrow['standardcost'] * $FactorMin;
				$RecommendedPrice = round_price($MaxPrice, "UP");
				$Increase = locale_number_format(($RecommendedPrice-$myrow['retailprice'])/$myrow['retailprice']*100,1).'%';
				$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
				$IncomeIncrease = $myrow['qoh'] * ($RecommendedPrice-$myrow['retailprice']);
				
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
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($PositionTopSales,0),
						locale_number_format($myrow['qoh'],0),
						locale_number_format($myrow['standardcost'],0),
						locale_number_format($MinPrice,0),
						locale_number_format($myrow['retailprice'],0),
						locale_number_format($myrow['retailprice']/$myrow['standardcost'],2),
						locale_number_format($MaxPrice,0),
						$NewPriceLink,
						$Increase,
						locale_number_format($IncomeIncrease,0)
						);
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
}

function ItemsTooExpensive($Stockcat, $FactorMin, $FactorMax, $Tolerance, $MinQoh, $TopSales, $DaysTopSales, $RootPath, $db){
	$today = date('Y-m-d');
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysTopSales));
	$FactorTolerance = 1 - $Tolerance;

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
						AND prices.startdate <= '". $StartDate. "' 
						AND prices.startdate <= '". $today. "' 
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					LIMIT 1) AS retailprice,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS standardcost
			FROM stockmaster				
			WHERE stockmaster.categoryid = '". $Stockcat ."'					
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.lastcategoryupdate <= '". $StartDate."'
				AND ((SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) >= " . $MinQoh . ")
				AND ((SELECT price 					
					FROM prices	
					WHERE stockmaster.stockid = prices.stockid	
						AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
						AND prices.currabrev = '". CURRENCY_CODE ."'
						AND prices.startdate <= '". $StartDate. "' 
						AND prices.startdate <= '". $today. "' 
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					LIMIT 1) > " . SMALL_PRICE_CALCULATED_STEP04 . ") 
				AND ((SELECT price 					
					FROM prices	
					WHERE stockmaster.stockid = prices.stockid	
						AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
						AND prices.currabrev = '". CURRENCY_CODE ."'
						AND prices.startdate <= '". $StartDate. "' 
						AND prices.startdate <= '". $today. "' 
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					LIMIT 1) > ((stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) * ". $FactorMax ." / ". $FactorTolerance ."))";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = PositionTopSalesItem($myrow['stockid'], $DaysTopSales, $db);
			$MaxPrice = $myrow['standardcost'] * $FactorMax;
			$MinPrice = $myrow['standardcost'] * $FactorMin;
			$RecommendedPrice = round_price($MaxPrice, "UP");
			$Decrease = locale_number_format(($RecommendedPrice-$myrow['retailprice'])/$myrow['retailprice']*100,1).'%';
			$IncomeDecrease = $myrow['qoh'] * ($RecommendedPrice-$myrow['retailprice']);
			if (($PositionTopSales > $TopSales) AND 
				($RecommendedPrice < $myrow['retailprice'])){
				if ($ShowHeader){
					$CategoryName = GetCategoryNameFromCode($Stockcat);
					echo '<p class="page_title_text" align="center"><strong>' .  $CategoryName . ' Items TOO EXPENSIVE: ' . ' NO TOP '.locale_number_format($TopSales,0) . ' sales. Retail Price OVER ' . $FactorMax . _(' x standard cost. Tolerance ') . locale_number_format($Tolerance * 100,0) . '%. QOH >= ' .  locale_number_format($MinQoh,0).  '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('TopSales') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('Std Cost') . '</th>
										<th class="ascending">' . _('Minimum Price') . '</th>
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
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($PositionTopSales,0),
						locale_number_format($myrow['qoh'],0),
						locale_number_format($myrow['standardcost'],0),
						locale_number_format($MinPrice,0),
						locale_number_format($myrow['retailprice'],0),
						locale_number_format($myrow['retailprice']/$myrow['standardcost'],2),
						locale_number_format($MaxPrice,0),
						$NewPriceLink,
						$Decrease,
						locale_number_format($IncomeDecrease,0)
						);
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
}

function PriceBelowStandard($Stockcat, $Factor, $Tolerance, $MinQoh, $RootPath, $db){
	$today = date('Y-m-d');
	$FactorTolerance = 1 + $Tolerance;

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
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					LIMIT 1) AS retailprice,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS standardcost
			FROM stockmaster				
			WHERE stockmaster.categoryid = '". $Stockcat ."'					
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND ((SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) >= " . $MinQoh . ")
				AND ((SELECT price 					
					FROM prices	
					WHERE stockmaster.stockid = prices.stockid	
						AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
						AND prices.currabrev = '". CURRENCY_CODE ."'
						AND prices.startdate <= '". $today. "' 
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					LIMIT 1) < ((stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) * ". $Factor ." / ". $FactorTolerance ."))
				AND NOT EXISTS (SELECT * 					
					FROM prices	
					WHERE stockmaster.stockid = prices.stockid	
						AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
						AND prices.currabrev = '". CURRENCY_CODE ."'
						AND prices.startdate > '". $today. "')";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			$NewPrice = $myrow['standardcost'] * $Factor;
			$RecommendedPrice = round_price($NewPrice, "UP");
			if ($myrow['retailprice'] != $RecommendedPrice){
				if ($ShowHeader){
					$CategoryName = GetCategoryNameFromCode($Stockcat);
					echo '<p class="page_title_text" align="center"><strong>' . $CategoryName . _(' Items with retail price below minimum. ') . $Factor . _(' x standard cost. Tolerance -') . locale_number_format($Tolerance * 100,0) . '%. QOH >= ' .  locale_number_format($MinQoh,0). '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('TopSales') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('Std Cost') . '</th>
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
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$Increase = locale_number_format(($RecommendedPrice-$myrow['retailprice'])/$myrow['retailprice']*100,1).'%';
				$PositionTopSales = PositionTopSalesItem($myrow['stockid'], 60, $db);
				$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
				$IncomeIncrease = $myrow['qoh'] * ($RecommendedPrice-$myrow['retailprice']);
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
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($PositionTopSales,0),
						locale_number_format($myrow['qoh'],0),
						locale_number_format($myrow['standardcost'],0),
						locale_number_format($myrow['retailprice'],0),
						locale_number_format($myrow['retailprice']/$myrow['standardcost'],2),
						locale_number_format($NewPrice,0),
						$NewPriceLink,
						$Increase,
						locale_number_format($IncomeIncrease,0)
						);
				$i++;
			}
		}
		if(!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
}

function PriceWrongRounding($RootPath, $db){
	$today = date('Y-m-d');

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
						AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					LIMIT 1) AS retailprice,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS standardcost
			FROM stockmaster				
			WHERE stockmaster.discontinued = 0
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING . ")
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
			ORDER BY stockmaster.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			$RoundedDown = round_price($myrow['retailprice'], "DOWN");
			$RoundedUp = round_price($myrow['retailprice'], "UP");
			
			if($myrow['retailprice'] != $RoundedUp){
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
				$PositionTopSales = PositionTopSalesItem($myrow['stockid'], 60, $db);
				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						$PositionTopSales,
						locale_number_format($myrow['qoh'],0),
						$DownPriceLink,
						locale_number_format($myrow['retailprice'],0),
						$UpPriceLink
						);
				$i++;
			}
		}
		if(!$ShowHeader){
			echo '</table>
					</div>';
		}
	}
}


?>
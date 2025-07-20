<?php

/**************************************************************************************************************
* KLReorderLevel.php - Functions for managing reorder levels in a retail environment
*
* Functions included in this file (alphabetical order):
* - ActiveLocationsForItem: Counts active locations with reorder level > 0 for a given stock item
* - AdjustPackaging: Adjusts packaging requirements for shops based on sales history
* - AdjustPackagingGudang: Adjusts reorder levels for packaging in warehouse (gudang)
* - AdjustPackagingItemByShop: Calculates and sets packaging reorder levels for specific item/shop combinations
* - KL_DailyRLAdjustmentsForBlink: Manages reorder level adjustments for Blink brand shops
* - KL_DailyRLAdjustmentsForKL: Manages reorder level adjustments for Kapal-Laut brand shops
* - KL_DailyRLAdjustmentsForOnline: Manages reorder level adjustments for online shop
* - KL_DailyRLAdjustmentsForOutlet: Manages reorder level adjustments for outlet stores
* - KL_DailyRLAdjustmentsForPackaging: Manages reorder level adjustments for packaging materials
* - KL_DailyRLRebalancing: Rebalances stock between shops when kantor (office) has zero stock
* - KL_DailyRLZeroNotAvailable: Sets reorder level to zero for items with no stock available
* - MaxRLCorrectionSomeModels: Applies model-specific corrections to reorder levels
* - MaxTopSalesForTypeOfShop: Gets maximum top sales value for shop type
* - OnlineReorderLevelAdjustments: Adjusts reorder levels for online shop based on pending orders
* - QtyAvailable: Gets available quantity for a stock item in specified location(s)
* - RebalancingBetweenShops: Redistributes stock between shops when kantor has no stock
* - SetReorderLevel: Updates reorder level for a stock item at specified location
* - SetRLForLowSalesHighRL: Reduces reorder levels for slow-selling items with high reorder levels
* - SetRLForTopSalesItems: Increases reorder levels for top-selling items with sufficient stock
* - SetRLZeroForNotAvailableItems: Sets reorder level to zero for items with no stock
* - WorstLocationForItem: Finds worst location for an item based on sales history
**************************************************************************************************************/

/**************************************************************************************************************
* Manages reorder level adjustments for online shop
*
* @param bool $ShowMessages - Whether to display messages in the UI
* @param bool $UpdateDB - Whether to update the database
* @param string $RootPath - Root path of the application
* @param string $EmailText - Current email text to be appended to
* 
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyRLAdjustmentsForOnline($ShowMessages, $UpdateDB, $RootPath, $EmailText) {
	$EmailText = OnlineReorderLevelAdjustments($ShowMessages, $UpdateDB, $RootPath, $EmailText); // Updates RL for online orders
	return $EmailText;
}

/**************************************************************************************************************
* Manages reorder level adjustments for Kapal-Laut brand shops
*
* @param bool $ShowMessages - Whether to display messages in the UI
* @param bool $UpdateDB - Whether to update the database
* @param string $RootPath - Root path of the application
* @param string $EmailText - Current email text to be appended to
* 
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyRLAdjustmentsForKL($ShowMessages, $UpdateDB, $RootPath, $EmailText) {
	// For KL SHOPS
	$Shops = NumberOfShops("SHOPKL");
	if ($EmailText != '') {
		$EmailText = $EmailText . "\n" . "Number of Shops Kapal-Laut = " . $Shops . "\n";
	}
	if ($ShowMessages) {
		prnMsg('Number of Shops Kapal-laut = ' . $Shops, 'info');
	}

	$EmailText = SetRLForTopSalesItems("SHOPKL", 1, 50, ($Shops * 7),       999999, 5, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 1, 50, ($Shops * 6), ($Shops * 7), 4, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 1, 50, ($Shops * 5), ($Shops * 6), 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPKL", 51, 100, ($Shops * 6),       999999, 4, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 51, 100, ($Shops * 5), ($Shops * 6), 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 51, 100, ($Shops * 4), ($Shops * 5), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, ($Shops * 5),       999999, 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, ($Shops * 4), ($Shops * 5), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, ($Shops * 3), ($Shops * 4), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	$EmailText = SetRLForLowSalesHighRL("SHOPKL", 30, 5, 4, ($Shops * 6), $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPKL", 20, 4, 3, ($Shops * 5), $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPKL", 10, 3, 2, ($Shops * 4), $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	
	return $EmailText;
}

/**************************************************************************************************************
* Manages reorder level adjustments for Blink brand shops
*
* @param bool $ShowMessages - Whether to display messages in the UI
* @param bool $UpdateDB - Whether to update the database
* @param string $RootPath - Root path of the application
* @param string $EmailText - Current email text to be appended to
* 
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyRLAdjustmentsForBlink($ShowMessages, $UpdateDB, $RootPath, $EmailText) {
	// For BLINK SHOPS
	$Shops = NumberOfShops("SHOPBL");
	if ($EmailText != '') {
		$EmailText = $EmailText . "\n" . "Number of Shops Blink = " . $Shops . "\n";
	}
	if ($ShowMessages) {
		prnMsg('Number of Shops Blink = ' . $Shops, 'info');
	}

	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, ($Shops * 7),       999999, 5, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, ($Shops * 6), ($Shops * 7), 4, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, ($Shops * 5), ($Shops * 6), 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, ($Shops * 6),       999999, 4, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, ($Shops * 5), ($Shops * 6), 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, ($Shops * 4), ($Shops * 5), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, ($Shops * 5),       999999, 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, ($Shops * 4), ($Shops * 5), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, ($Shops * 3), ($Shops * 4), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  30, 5, 4, ($Shops * 6), $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  20, 4, 3, ($Shops * 5), $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  10, 3, 2, ($Shops * 4), $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	return $EmailText;
}

/**************************************************************************************************************
* Manages reorder level adjustments for outlet stores
*
* @param bool $ShowMessages - Whether to display messages in the UI
* @param bool $UpdateDB - Whether to update the database
* @param string $RootPath - Root path of the application
* @param string $EmailText - Current email text to be appended to
* 
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyRLAdjustmentsForOutlet($ShowMessages, $UpdateDB, $RootPath, $EmailText){

	// for OUTLET SHOPS
	$Shops = NumberOfShops("SHOPOU");
	$RegularKLShopsSellingDiscount = NumberOfRegularShopsSellingDiscount("SHOPKL");
	$RegularBlinkShopsSellingDiscount = NumberOfRegularShopsSellingDiscount("SHOPBL");
	
	if ($EmailText != '') {
		$EmailText = $EmailText . "\n" . "Number of Shops Outlet = " . $Shops . "\n";
		$EmailText = $EmailText . "\n" . "Number of regular KL Shops selling discount = " . $RegularKLShopsSellingDiscount . "\n";
		$EmailText = $EmailText . "\n" . "Number of regular Blink Shops selling discount = " . $RegularBlinkShopsSellingDiscount . "\n";
	}
	if ($ShowMessages) {
		prnMsg('Number of Shops Outlet = ' . $Shops,'info');
		prnMsg('Number of regular KL Shops selling discount = ' . $RegularKLShopsSellingDiscount,'info');
		prnMsg('Number of regular Blink Shops selling discount = ' . $RegularBlinkShopsSellingDiscount,'info');
	}

	$ShopsDiscountKL = $Shops + $RegularKLShopsSellingDiscount;
	$EmailText = SetRLForTopSalesItems("OUTKL",   1,  50, ($ShopsDiscountKL * 5),       999999, 4, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTKL",   1,  50, ($ShopsDiscountKL * 4), ($ShopsDiscountKL * 5), 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTKL",   1,  50, ($ShopsDiscountKL * 3), ($ShopsDiscountKL * 4), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("OUTKL",  51, 100, ($ShopsDiscountKL * 4),       999999, 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTKL",  51, 100, ($ShopsDiscountKL * 3), ($ShopsDiscountKL * 4), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	
	$ShopsDiscountBL = $Shops + $RegularBlinkShopsSellingDiscount;
	$EmailText = SetRLForTopSalesItems("OUTBL",   1,  50, ($ShopsDiscountBL * 5),       999999, 4, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTBL",   1,  50, ($ShopsDiscountBL * 4), ($ShopsDiscountBL * 5), 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTBL",   1,  50, ($ShopsDiscountBL * 3), ($ShopsDiscountBL * 4), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("OUTBL",  51, 100, ($ShopsDiscountBL * 4),       999999, 3, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTBL",  51, 100, ($ShopsDiscountBL * 3), ($ShopsDiscountBL * 4), 2, $ShowMessages, $UpdateDB, $RootPath, $EmailText);

	return $EmailText;
}

/**************************************************************************************************************
* Rebalances stock between shops when kantor (office) has zero stock
*
* @param bool $ShowMessages - Whether to display messages in the UI
* @param bool $UpdateDB - Whether to update the database
* @param string $RootPath - Root path of the application
* @param string $EmailText - Current email text to be appended to
* 
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyRLRebalancing($ShowMessages, $UpdateDB, $RootPath, $EmailText){
	// These functions does not need to be segregated by type of shop, as it only takes care of shops with RL > 0
	$EmailText = RebalancingBetweenShops(60, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	return $EmailText;
}

/**************************************************************************************************************
* Sets reorder level to zero for items with no stock available across all locations
*
* @param bool $ShowMessages - Whether to display messages in the UI
* @param bool $UpdateDB - Whether to update the database
* @param string $RootPath - Root path of the application
* @param string $EmailText - Current email text to be appended to
* 
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyRLZeroNotAvailable($ShowMessages, $UpdateDB, $RootPath, $EmailText){
	$EmailText = SetRLZeroForNotAvailableItems($ShowMessages, $UpdateDB, $RootPath, $EmailText);
	return $EmailText;
}

/**************************************************************************************************************
* Manages reorder level adjustments for packaging materials for all shop types and warehouses
*
* @param bool $ShowMessages - Whether to display messages in the UI
* @param bool $UpdateDB - Whether to update the database
* @param string $RootPath - Root path of the application
* @param string $EmailText - Current email text to be appended to
* 
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyRLAdjustmentsForPackaging($ShowMessages, $UpdateDB, $RootPath, $EmailText){

	$EmailText = AdjustPackaging(60, 'SHOPKL', $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = AdjustPackaging(60, 'SHOPBL', $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	$EmailText = AdjustPackagingGudang('PACKU', FACTOR_GUDANG_PACKAGING, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
	
	return $EmailText;
}

/**************************************************************************************************************
* Redistributes stock between shops when the main warehouse (kantor) has no stock of an item needed by shops.
* It identifies items needed in some shops, available in others, with zero stock at kantor, and no pending transfers.
* It then attempts to rebalance by adjusting reorder levels to facilitate transfers.
*
* @param int $maxdays - The number of past days to consider for sales data when determining worst locations.
* @param bool $ShowMessages - Whether to display messages in the UI.
* @param bool $UpdateDB - Whether to update the database with new reorder levels.
* @param string $RootPath - Root path of the application, used for generating links.
* @param string $EmailText - Current email text to be appended with operation results.
* 
* @return string - Updated email text containing results of operations.
**************************************************************************************************************/
function RebalancingBetweenShops($maxdays, $ShowMessages, $UpdateDB, $RootPath, $EmailText){

	$ItemsRebalanced = 0;
	if ($EmailText != ''){
		$EmailText = $EmailText . "\n" . "Rebalancing stock between shops." . "\n\n";
	}

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.categoryid,
					stockmaster.description,
					(SELECT locstock.loccode
						FROM locstock, locations
						WHERE stockmaster.stockid = locstock.stockid 
							AND locstock.loccode = locations.loccode
							AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
							AND locstock.quantity < locstock.reorderlevel
						ORDER BY reorderlevel DESC
						LIMIT 1) AS locationneeded
			FROM stockmaster
			WHERE stockmaster.categoryid NOT IN ('SHDISP', 'SHPACK')
				AND EXISTS (SELECT *
							FROM locstock, locations
							WHERE stockmaster.stockid = locstock.stockid 
								AND locstock.loccode = locations.loccode
								AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
								AND locstock.quantity < locstock.reorderlevel)
				AND EXISTS (SELECT *
							FROM locstock, locations
							WHERE stockmaster.stockid = locstock.stockid 
								AND locstock.loccode = locations.loccode
								AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
								AND locstock.quantity > 0)
				AND EXISTS (SELECT *
						FROM locstock
						WHERE stockmaster.stockid = locstock.stockid 
							AND locstock.loccode = " . CODE_KANTOR . "
							AND locstock.quantity = 0)
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  pendingqty > 0
							AND loctransfers.stockid =  stockmaster.stockid)
			ORDER BY stockmaster.stockid";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Rebalancing between shops (Stock available at kantor = 0)');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('#') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('Category') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Toko From') . '</th>
								<th>' . _('RL From') . '</th>
								<th>' . _('Needed At') . '</th>
								<th>' . _('Strategy') . '</th>
							</tr>';
			echo '<thead>' . $TableHeader . '</thead>';
			echo '<tbody>';
		}
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages){
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			}
			$RebalancingLocationFrom = "";
			$Strategy = "";
			$PrintLine = true;

			// Look for the WORST location for the item with overstock (QOH > RL).
			$LocationWithOverstock  = WorstLocationForItem($MyRow['stockid'], "OVERSTOCK", $maxdays);
			if ($LocationWithOverstock == ""){
				// NO location with overstock found, then we need to reduce RL at the worst selling location with some stock available (qty > 0)
				$WorstLocation  = WorstLocationForItem($MyRow['stockid'], "AVAILABLE", $maxdays);
				if ($WorstLocation == ""){
					// Does not exist any shop with available stock. This was the last one!
					// No need to do anything!!!
					$RebalancingLocationFrom = "";
					$Strategy = "No shop with available stock. No RL changed";
				}else{
					// let's distribute available stock between the shops with RL > 0.
					// if RL = 0 we suppose we do not want it there for any reason 
					$QtyToDistribute = QtyAvailable($MyRow['stockid'], "ALLSHOPS");
					if ($EmailText != ''){
						$EmailText = $EmailText . $MyRow['stockid'] . " Quantity to distribute = " . $QtyToDistribute . "\n";
					}
					$LocationsDistributed = 0;
					
					// the location's order has to take into account if the shop is supposed to have all the collection or not,
					// so we order as 1
					// 1st: shops with all collection = true
					// 2nd: shops with all collection = false (small shops or slow shops)
					// 3rd: shops with higher sales of the item
					// 4rd: shops with higer sales in general

					if (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_TEST)){
						$OrderBy = " locations.alltestitems DESC, ";
					}elseif (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_STABLE)){
						$OrderBy = " locations.allstableitems DESC, ";
					}elseif (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)){
						$OrderBy = " locations.allnopoitems DESC, ";
					}else{
						$OrderBy = "";
					}
					$DistributionSQL = "SELECT locstock.loccode, 
											locstock.reorderlevel AS oldrl
										FROM locstock, locations
										WHERE  locstock.loccode = locations.loccode
											AND locstock.stockid = '" . $MyRow['stockid'] . "'
											AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
											AND locstock.reorderlevel > 0 
										ORDER BY locations.priority ASC, ".
												$OrderBy ."
												(SELECT COUNT(qtyinvoiced)
													FROM salesorderdetails, salesorders
													WHERE salesorderdetails.orderno = salesorders.orderno
														AND salesorderdetails.completed = 1
														AND salesorders.orddate >= '". $StartDate . "'
														AND salesorders.fromstkloc = locstock.loccode
														AND salesorderdetails.stkcode = '". $MyRow['stockid'] . "') DESC, 
												(SELECT COUNT(qtyinvoiced)
													FROM salesorderdetails, salesorders
													WHERE salesorderdetails.orderno = salesorders.orderno
														AND salesorderdetails.completed = 1
														AND salesorders.orddate >= '". $StartDate . "'
														AND salesorders.fromstkloc = locstock.loccode) DESC";
														
					$DistributionResult = DB_query($DistributionSQL);
					$LocationsToDistribute = DB_num_rows($DistributionResult);
					if ($LocationsToDistribute != 0){
						// We have some locations to distribute the stock
						while ($MyDistribution = DB_fetch_array($DistributionResult)) {
							// distribute the stock between the locations with RL>0, updating the RL
							$NewRL = ceil($QtyToDistribute / ($LocationsToDistribute - $LocationsDistributed));
							// Fix corrections to some models, due to space restrictions, or other exceptions
							$NewRL = MaxRLCorrectionSomeModels($MyRow['stockid'], $MyDistribution['loccode'], $NewRL);
							SetReorderLevel("Rebalancing", $MyRow['stockid'], $MyDistribution['loccode'], $MyDistribution['oldrl'], $NewRL, $UpdateDB);
							$Strategy = "Distribute available stock between shops with RL > 0";
							$QtyToDistribute = $QtyToDistribute - $NewRL;
							$LocationsDistributed++;
							if ($ShowMessages){
								echo '<tr class="striped_row">
										<td class="number">'.$i.'</td>
										<td>'.$CodeLink.'</td>
										<td>'.$MyRow['categoryid'].'</td>
										<td>'.$MyRow['description'].'</td>
										<td>'.$MyDistribution['loccode'].'</td>
										<td class="number">'.locale_number_format($MyDistribution['oldrl'],0).'</td>
										<td>'.$MyRow['locationneeded'].'</td>
										<td>'.$Strategy.'</td>
									</tr>';
								$PrintLine = false;
							}
							if ($EmailText != ''){
								$EmailText = $EmailText . $MyRow['stockid'] .
														" OldRL @ " . 
														$MyDistribution['loccode'] . 
														" = " . 
														locale_number_format($MyDistribution['oldrl'],0) .
														" NewRL = " . 
														locale_number_format($NewRL,0) .
														"\n";
							}
						}
						$ItemsRebalanced++;
					}else{
						$Strategy = "No shop to distribute";
					}
				}
			}else{
				// We have some overstock location. When transferrng from TOKO to kantor will be rebalanced.
				// No need to do anything, as the overstock item in that location will return to kantor 
				// and from kantor will be sent to the location needing it.
				$RebalancingLocationFrom = $LocationWithOverstock;
				$Strategy = "Overstock available in some shop. No RL changed";
			}
			if ($ShowMessages){
				if ($PrintLine){
					echo '<tr class="striped_row"><td class="number">'.$i.'</td>
							<td>'.$CodeLink.'</td>
							<td>'.$MyRow['categoryid'].'</td>
							<td>'.$MyRow['description'].'</td>
							<td>'.$RebalancingLocationFrom.'</td>
							<td class="number">'."".'</td>
							<td>'.$MyRow['locationneeded'].'</td>
							<td>'.$Strategy.'</td>
						</tr>';
				}
			}
			if ($EmailText != ''){
				$EmailText = $EmailText . $MyRow['stockid'] . " needed @ " . 
										$MyRow['locationneeded'] .
										" Strategy used: " . 
										$Strategy . " " . 
										"\n\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</tbody></table></div>';
		}
	}

	InsertKPI("TRANSFERS-REBALANCE-MOD", $ItemsRebalanced);

	return $EmailText;
}

/**************************************************************************************************************
* Finds the worst performing location for a given stock item based on sales history.
* 'Worst' can mean either having overstock (quantity > reorder level) or just having available stock (quantity > 0)
* if no overstock location is found. Locations are prioritized by sales (lower sales = worse).
*
* @param string $StockID - The stock ID of the item.
* @param string $Kind - Type of search: "OVERSTOCK" for locations with quantity > RL, 
*                       "AVAILABLE" for locations with quantity > 0.
* @param int $maxdays - The number of past days to consider for sales data.
* 
* @return string - The location code of the worst performing shop, or an empty string if none found.
**************************************************************************************************************/
function WorstLocationForItem($StockID, $Kind, $maxdays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$maxdays));
	$SQL = "SELECT locstock.loccode
			FROM locstock, locations
			WHERE locstock.loccode = locations.loccode
				AND locstock.stockid = '" . $StockID . "'";

	if ($Kind == "OVERSTOCK"){
		$SQL = $SQL . " AND locstock.quantity > locstock.reorderlevel"; 
	}elseif ($Kind == "AVAILABLE"){
		$SQL = $SQL . " AND locstock.quantity > 0 "; 
	}

	$SQL = $SQL . "	AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
					ORDER BY locations.priority DESC,
					(SELECT COUNT(qtyinvoiced)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDate . "'
							AND salesorders.fromstkloc = locstock.loccode
							AND salesorderdetails.stkcode = '". $StockID . "') ASC";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Location = $MyRow['loccode'];
	}else{
		$Location = "";
	}
	return $Location;
}

/**************************************************************************************************************
* Gets the total available quantity for a stock item across specified location(s).
*
* @param string $StockID - The stock ID of the item.
* @param string $Location - Specifies the scope: "ALLSHOPS" for all Bali shops, 
*                           "ALLSHOPSANDONLINE" for all shops including online, 
*                           "ALL" for all locations, or a specific location code.
* 
* @return int - The total available quantity.
**************************************************************************************************************/
function QtyAvailable($StockID, $Location){
	$SQL = "SELECT SUM(locstock.quantity) AS total
			FROM locstock,locations
			WHERE locstock.stockid = '" . $StockID . "'
				AND locstock.loccode = locations.loccode";
	if ($Location == "ALLSHOPS"){
		$SQL = $SQL . " AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . " "; 
	}elseif ($Location == "ALLSHOPSANDONLINE"){
		$SQL = $SQL . " AND locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . " "; 
	}elseif ($Location == "ALL"){
		$SQL = $SQL . " "; 
	}else{
		$SQL = $SQL . " AND locstock.loccode = '". $Location . "'"; 
	}
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Qty = $MyRow['total'];
	}else{
		$Qty = 0;
	}
	return $Qty;
}

/**************************************************************************************************************
* Counts the number of active locations (reorder level > 0) for a given stock item.
*
* @param string $StockID - The stock ID of the item.
* 
* @return int - The count of active locations.
**************************************************************************************************************/
function ActiveLocationsForItem($StockID){
	$SQL = "SELECT COUNT(locstock.loccode) AS total
			FROM locstock
			WHERE locstock.stockid = '" . $StockID . "'
				AND locstock.reorderlevel > 0";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Qty = $MyRow['total'];
	}else{
		$Qty = 0;
	}
	return $Qty;
}

/**************************************************************************************************************
* Sets the reorder level to zero for items that are not available (total quantity is zero across all ready-to-sell locations)
* but still have a positive reorder level in some shops. Excludes shop consumables and packaging.
*
* @param bool $ShowMessages - Whether to display messages in the UI.
* @param bool $UpdateDB - Whether to update the database with new reorder levels.
* @param string $RootPath - Root path of the application, used for generating links.
* @param string $EmailText - Current email text to be appended with operation results.
* 
* @return string - Updated email text containing results of operations.
**************************************************************************************************************/
 function SetRLZeroForNotAvailableItems($ShowMessages, $UpdateDB, $RootPath, $EmailText){
	/* On 17/12/2013 we take out the SHOP consumables to avoid problems with the shop packagings */
	/* On 21/12/2013 we take out the SHOP packaging to avoid problems with the shop packagings */

	if ($EmailText != ''){
		$EmailText = $EmailText . "Set RL = 0 for not available items." . "\n\n";
	}
	
	$SQL = "SELECT locstock.stockid,
					stockmaster.description,
					locstock.reorderlevel
			FROM locstock, stockmaster, stockcategory, locations
			WHERE locstock.stockid = stockmaster.stockid
				AND locstock.loccode = locations.loccode
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.categoryid != 'SHCONS'
				AND stockmaster.categoryid != 'SHPACK'
				AND stockcategory.stocktype = 'F'
				AND locations.stockreadytosell = 1
				AND EXISTS (SELECT *
							FROM locstock, locations loc2
							WHERE locstock.stockid = stockmaster.stockid
								AND locstock.loccode = loc2.loccode
								AND locstock.reorderlevel > 0 
								AND loc2.stockreadytosell = 1)
			GROUP BY locstock.stockid
			HAVING SUM(locstock.quantity) = 0";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = 'Set RL = 0 for items with NO stock available at shops or kantor.';
			ShowTableTitle($TableTitleText);
			$TableHeader = '<tr>
								<th>' . _('#') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('Description') . '</th>
							</tr>';
			echo '<div>
				<table class="selection">
				<thead>' . $TableHeader . '</thead>
				<tbody>';
		}
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages){
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
					</tr>';
				$i++;
			}
			if ($EmailText != ''){
				$EmailText = $EmailText . $MyRow['stockid'] . "\n";
			}
			SetReorderLevel("NotAvailable", $MyRow['stockid'],"SHOPS", 999999, 0, $UpdateDB);
		}
		if ($ShowMessages){
			echo '</tbody></table></div>';
		}
	}
	return $EmailText;
}

/**************************************************************************************************************
* Increases reorder levels for top-selling items within a specified sales rank range,
* provided there is sufficient stock available globally and the item is not undergoing a price change.
*
* @param string $ShopType - The type of shop (e.g., "SHOPKL", "SHOPBL") to filter items and apply RL changes.
* @param int $StartTopItems - The starting rank in top sales items.
* @param int $EndTopItems - The ending rank in top sales items.
* @param int $MinStockAvailable - The minimum global stock quantity required for the item.
* @param int $MaxStockAvailable - The maximum global stock quantity allowed for the item.
* @param int $NewRL - The new reorder level to set for qualifying items/locations.
* @param bool $ShowMessages - Whether to display messages in the UI.
* @param bool $UpdateDB - Whether to update the database with new reorder levels.
* @param string $RootPath - Root path of the application, used for generating links.
* @param string $EmailText - Current email text to be appended with operation results.
* 
* @return string - Updated email text containing results of operations.
**************************************************************************************************************/
function SetRLForTopSalesItems($ShopType, $StartTopItems, $EndTopItems, $MinStockAvailable, $MaxStockAvailable, $NewRL, $ShowMessages, $UpdateDB, $RootPath, $EmailText){

/* function SetRLForTopSalesItems Increases RL for good selling items with enough stock.
Sets Reorder Level to $NewRL 
for the items in top sales items (from $StartTopItems to $EndTopItems during last $daystopitems) 
with stock available higher than $MinStockAvailable, lower than $MaxStockAvailable
to the shops with RL > 0.

24/12/2012 modification: For Plastic bag products, there is a MAX qty for some shops. HARDCODED.
28/12/2012 modification: Not include items with schedduled price change to avoid problems with price tag changes
				AND NOT EXISTS (SELECT * 					
					FROM prices	
					WHERE stockmaster.stockid = prices.stockid	
						AND prices.typeabbrev = 'RT'
						AND prices.currabrev = 'IDR'
						AND prices.startdate > CURRENT_DATE)
19/04/2013 modification: Change the condition of "not changing price" to the new flag
24/07/2013 modification: Do not increase RL for toko online
11/03/2017 modification: filter by ShopType (brand) and simplified code with stockreadytosell
18/12/2019 modification: change the LIKE in typeloc as we always call only one kind of typeloc
19/12/2019 modification: simplified the main query to use klsalesperformance table, to reduce CPU time.
18/12/2024 modofication: discounted items now can be sold in regular shops

*/	
	if ($EmailText != ''){
		$EmailText = $EmailText . "\n" . "Set RL For " . $ShopType . " top sales items range " . $StartTopItems . " - " . $EndTopItems . " Top Sales with RL lower than " . $NewRL . " and minimum available stock " . $MinStockAvailable . "\n";
	}

	if ($ShopType == "SHOPKL") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . " ";
	}elseif ($ShopType == "SHOPBL") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . " ";
	}elseif ($ShopType == "OUTKL") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT_ONLY_DISCOUNT . " ";
	}elseif ($ShopType == "OUTBL") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK_ONLY_DISCOUNT . " ";
	}else{
		$WhereCat = " ";
	}
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.categoryid,
					stockmaster.description,
					klsalesperformance.topsales60
			FROM stockmaster, klsalesperformance
			WHERE stockmaster.stockid = klsalesperformance.stockid
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				" . $WhereCat . "
			ORDER BY topsales60 DESC
			LIMIT " . ($StartTopItems - 1) . "," . ($EndTopItems - $StartTopItems + 1);			

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$ShowHeader = true;
		$i = $StartTopItems;
		while ($MyRow = DB_fetch_array($Result)) {

			$SQLQtyAvailable = "SELECT SUM(locstock.quantity) AS QtyAvailable
								FROM locstock, locations loc2
								WHERE locstock.stockid  = '" . $MyRow['stockid'] . "'
									AND locstock.loccode = loc2.loccode
									AND loc2.stockreadytosell = 1";
			$ResultQtyAvailable = DB_query($SQLQtyAvailable);
			$MyRowQtyAvailable = DB_fetch_array($ResultQtyAvailable);
			
			if (($MyRowQtyAvailable['QtyAvailable'] > $MinStockAvailable) 
				AND ($MyRowQtyAvailable['QtyAvailable'] <= $MaxStockAvailable)){
				$DistributionSQL = "SELECT locstock.loccode, 
										locstock.reorderlevel AS oldrl
									FROM locstock,locations
									WHERE locstock.stockid = '" . $MyRow['stockid'] . "'
										AND locstock.loccode = locations.loccode
										AND locations.stockreadytosell = 1
										AND locstock.reorderlevel > 0";
				$DistributionResult = DB_query($DistributionSQL);
				$LocationsToDistribute = DB_num_rows($DistributionResult);
				if ($LocationsToDistribute != 0){
					while ($MyDistribution = DB_fetch_array($DistributionResult)) {

						$CurrentNewRL = MaxRLCorrectionSomeModels($MyRow['stockid'], $MyDistribution['loccode'], $NewRL);

						if($MyDistribution['oldrl'] < $CurrentNewRL){
							SetReorderLevel("TopSalesLowRL", $MyRow['stockid'], $MyDistribution['loccode'], $MyDistribution['oldrl'], $CurrentNewRL, $UpdateDB);
							if ($ShowMessages){
								if($ShowHeader){
									$TableTitleText = 'Set RL minimum to ' . $NewRL . 
													' for Top Sales '. $StartTopItems . '-'. $EndTopItems . 
													' with Stock Available > '. $MinStockAvailable .
													' and <= '. $MaxStockAvailable .
													' at '. $ShopType;
									ShowTableTitle($TableTitleText);
									echo '<div>';
									echo '<table class="selection">';
									$TableHeader = '<tr>
														<th>' . _('#') . '</th>
														<th>' . _('Code') . '</th>
														<th>' . _('Category') . '</th>
														<th>' . _('Description') . '</th>
														<th>' . _('Qty') . '</th>
														<th>' . _('Toko') . '</th>
														<th>' . _('Old RL') . '</th>
														<th>' . _('New RL') . '</th>
													</tr>';
									echo '<thead>' . $TableHeader . '</thead>';
									echo '<tbody>';
									$ShowHeader = false;
								}
								$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
								echo '<tr class="striped_row">
										<td class="number">' . $i . '</td>
										<td>' . $CodeLink . '</td>
										<td>' . $MyRow['categoryid'] . '</td>
										<td>' . $MyRow['description'] . '</td>
										<td class="number">' . locale_number_format($MyRowQtyAvailable['QtyAvailable'], 0) . '</td>
										<td>' . $MyDistribution['loccode'] . '</td>
										<td class="number">' . locale_number_format($MyDistribution['oldrl'], 0) . '</td>
										<td class="number">' . locale_number_format($CurrentNewRL, 0) . '</td>
									</tr>';
							}
							if ($EmailText != ''){
								$EmailText = $EmailText . $MyRow['stockid'] . " @ " . $MyDistribution['loccode'] . " Old RL = " . $MyDistribution['oldrl'] .  " New RL = " . $CurrentNewRL . "\n";
							}
						}
					}
				}
			}
			$i++;
		}
		if ($ShowMessages){
			if(!$ShowHeader){ // This implies the table was opened and body started
				echo '</tbody></table></div>';
			}
		}
	}
	return $EmailText;
}

/**************************************************************************************************************
* Gets the maximum top sales value (based on klsalesperformance table) for a given shop type and number of days.
*
* @param string $ShopType - The type of shop (e.g., "SHOPKL", "SHOPBL", "SHOPOU") to filter items.
* @param int $NumDays - The number of days for which the top sales figure is considered (e.g., 60 for topsales60).
* 
* @return mixed - The maximum top sales value, or null if no data found.
**************************************************************************************************************/
function MaxTopSalesForTypeOfShop($ShopType, $NumDays){
	if ($ShopType == "SHOPKL") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . " ";
	}elseif ($ShopType == "SHOPBL") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . " ";
	}elseif ($ShopType == "SHOPOU") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . " ";
	}else{
		$WhereCat = " ";
	}
	
	$SQL = "SELECT MAX(topsales" .$NumDays. ") AS maxtopsales
			FROM klsalesperformance, stockmaster
			WHERE klsalesperformance.stockid = stockmaster.stockid" .
			$WhereCat;
	$Result = DB_query($SQL);		
	$MyRow = DB_fetch_array($Result);
	return $MyRow['maxtopsales'];
}

/**************************************************************************************************************
* Reduces reorder levels for items that are in the bottom percentage of top sales,
* have a high current reorder level, and low global stock availability.
*
* @param string $ShopType - The type of shop (e.g., "SHOPKL", "SHOPBL", "SHOPOU") to filter items.
* @param int $BottomPercentTopSales - The bottom percentage of top sales to consider (e.g., 30 for bottom 30%).
* @param int $OldRL - The minimum current reorder level an item must have at a shop to be considered.
* @param int $maxRL - The new maximum reorder level to set for qualifying items/locations.
* @param int $minavailablestock - The maximum global stock quantity an item must have to be considered.
* @param bool $ShowMessages - Whether to display messages in the UI.
* @param bool $UpdateDB - Whether to update the database with new reorder levels.
* @param string $RootPath - Root path of the application, used for generating links.
* @param string $EmailText - Current email text to be appended with operation results.
* 
* @return string - Updated email text containing results of operations.
**************************************************************************************************************/
function SetRLForLowSalesHighRL($ShopType, $BottomPercentTopSales, $OldRL, $maxRL, $minavailablestock, $ShowMessages, $UpdateDB, $RootPath, $EmailText){
	/*  items bottom% in percent, 
		with stock at the shop
		with RL >= oldRL at the shop
		with less than minavailablestock at shops or office
	*/
	if ($EmailText != ''){
		$EmailText = $EmailText . "\n" . "Set RL For " . $ShopType . " items in the bottom " . $BottomPercentTopSales . "% Top Sales with RL higher than " . $maxRL . " and available stock <= " . $minavailablestock . "\n";
	}

	if ($ShopType == "SHOPKL") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . " ";
	}elseif ($ShopType == "SHOPBL") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . " ";
	}elseif ($ShopType == "SHOPOU") {
		$WhereCat = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . " ";
	}else{
		$WhereCat = " ";
	}

	$MaxTopSales = MaxTopSalesForTypeOfShop($ShopType, 60);
	$MinTopSales = round($MaxTopSales * ((100 - $BottomPercentTopSales) / 100), 0);
	
	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.units, 
					locstock.quantity,
					locstock.reorderlevel,
					locstock.loccode
			FROM 	stockmaster,locstock,klsalesperformance
			WHERE 	stockmaster.stockid = locstock.stockid
					AND stockmaster.stockid = klsalesperformance.stockid
					AND klsalesperformance.topsales60 >= " . $MinTopSales . 
					$WhereCat . "
					AND (locstock.quantity > 0)
					AND (locstock.reorderlevel >= ". $OldRL .")
					AND (SELECT SUM(locstock.quantity)
						FROM locstock, locations loc2
						WHERE stockmaster.stockid = locstock.stockid
							AND locstock.loccode = loc2.loccode
							AND loc2.stockreadytosell = 1) <= ".$minavailablestock."
			ORDER BY stockmaster.stockid";
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = 'Items in ' . $ShopType . ' with Top Sales Rank in the bottom ' . $BottomPercentTopSales . '% with RL >= ' . $OldRL . ' and stock available <= ' . $minavailablestock;
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('#') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Category') . '</th>
								<th>' . _('Location') . '</th>
								<th>' . _('Old RL') . '</th>
								<th>' . _('New RL') . '</th>
							</tr>';
			echo '<thead>' . $TableHeader . '</thead>';
			echo '<tbody>';
		}
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			SetReorderLevel("BottomSalesHighRL", $MyRow['stockid'],$MyRow['loccode'], $MyRow['reorderlevel'], $maxRL, $UpdateDB);
			if ($ShowMessages){
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td>' . $MyRow['categoryid'] . '</td>
						<td>' . $MyRow['loccode'] . '</td>
						<td class="number">' . locale_number_format($MyRow['reorderlevel'],0) . '</td>
						<td class="number">' . locale_number_format($maxRL,0) . '</td>
					</tr>';
			}
			if ($EmailText != ''){
				$EmailText = $EmailText .  $MyRow['stockid'] . " @ " . $MyRow['loccode'] . " OldRL = " . locale_number_format($MyRow['reorderlevel'],0) . " NewRL = " . locale_number_format($maxRL,0) . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</tbody></table></div>';
		}
	}
	return $EmailText;
}

/**************************************************************************************************************
* Applies model-specific corrections to a proposed new reorder level.
* Currently, this function is a placeholder and returns the new reorder level unchanged.
*
* @param string $StockID - The stock ID of the item.
* @param string $loccode - The location code.
* @param int $NewRL - The proposed new reorder level.
* 
* @return int - The corrected new reorder level.
**************************************************************************************************************/
function MaxRLCorrectionSomeModels($StockID, $loccode, $NewRL){
	$CurrentNewRL = $NewRL;
	return $CurrentNewRL;
}

/**************************************************************************************************************
* Updates the reorder level for a stock item at a specified location or group of locations.
* Logs the change in the `kladjustrl` table if the database update is enabled and the RL changes.
*
* @param string $Reason - The reason for the reorder level change (for logging).
* @param string $StockID - The stock ID of the item.
* @param string $loccode - The location code. Can be "ALL" for all locations, 
*                          "SHOPS" for all Bali shop type locations, or a specific location code.
* @param int $OldRL - The old reorder level (for logging).
* @param int $NewRL - The new reorder level to set.
* @param bool $UpdateDB - Whether to update the database and log the change.
* 
* @return void
**************************************************************************************************************/
function SetReorderLevel($Reason, $StockID, $loccode, $OldRL, $NewRL, $UpdateDB){
	if ($UpdateDB){
		if ($OldRL != $NewRL){
			if ($loccode == "ALL"){
				$SQL = "UPDATE locstock
						SET reorderlevel = '" . $NewRL ."'
						WHERE stockid = '". $StockID ."'";
			}elseif ($loccode == "SHOPS"){
				$SQL = "UPDATE locstock
						SET reorderlevel = '" . $NewRL ."'
						WHERE stockid = '". $StockID ."'
							AND loccode IN (SELECT locations.loccode
											FROM locations
											WHERE locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ")";
			}else{
				$SQL = "UPDATE locstock
						SET reorderlevel = '" . $NewRL ."'
						WHERE stockid = '". $StockID ."'
							AND loccode = '". $loccode ."'";
			}
			$ErrMsg =_('Could not update reorder level because');
			DB_query($SQL,$ErrMsg);
			// insert the change in the KLAdjustRL table (acting as a log of these automatic changes)
			$SQL = "INSERT INTO kladjustrl 
						(adjustdate,
						reason,
						loccode,
						stockid,
						oldrl,
						newrl)
					VALUES 
						('". Date('Y-m-d H-i-s') ."',
						'". $Reason ."',
						'". $loccode ."',
						'". $StockID ."',
						'". $OldRL ."',
						'". $NewRL ."')";		
		$ErrMsg =_('Could not insert the KLAdjustRL Log');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		DB_query($SQL,$ErrMsg,$DbgMsg,true);
		}
	}
}

/**************************************************************************************************************
* Adjusts reorder levels for the online shop (TOKWS).
* First, it resets all reorder levels for the online shop to zero.
* Then, it sets the reorder level for items based on the total quantity in uncompleted sales orders.
*
* @param bool $ShowMessages - Whether to display messages in the UI.
* @param bool $UpdateDB - Whether to update the database with new reorder levels.
* @param string $RootPath - Root path of the application, used for generating links.
* @param string $EmailText - Current email text to be appended with operation results.
* 
* @return string - Updated email text containing results of operations.
**************************************************************************************************************/
function OnlineReorderLevelAdjustments($ShowMessages, $UpdateDB, $RootPath, $EmailText){

	if ($EmailText != ''){
		$EmailText = $EmailText . "\n" . "OnlineReorderLevelAdjustments" . "\n\n";
	}
	
	// set all RL=0 for toko online
	if($UpdateDB){
		$RLSQL = "UPDATE locstock
					SET reorderlevel = 0 
					WHERE reorderlevel > 0 AND loccode = ". CODE_ONLINE_SHOP ."";
		$ErrMsg =_('Error in function OnlineReorderLevelAdjustments');
		$DbgMsg = _('The following SQL to update reorder levels was used');
		$Result = DB_query($RLSQL,$ErrMsg,$DbgMsg,true);		
		if ($ShowMessages){
			prnMsg(_('Reset all RL=0 for location Shop Online'),'info');
		}
		if ($EmailText != ''){
			$EmailText = $EmailText . "Reset all RL=0 for location Shop Online" . "\n";
		}
	}
// adjust RL for toko online as needed
	$SQL = "SELECT salesorderdetails.stkcode,
				SUM(salesorderdetails.quantity) AS totalqty,
				locstock.reorderlevel
			FROM salesorders, salesorderdetails, locstock
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = locstock.stockid
				AND locstock.loccode = ". CODE_ONLINE_SHOP ."
				AND salesorders.fromstkloc = ". CODE_ONLINE_SHOP ."
				AND salesorders.quotation = 0
				AND salesorderdetails.completed = 0
			GROUP BY salesorderdetails.stkcode
			ORDER BY salesorderdetails.stkcode";
				
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Adjustment RL for Toko Online');
			ShowTableTitle($TableTitleText);
			$TableHeader = '<tr>
								<th>' . _('#') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('QOH = New RL') . '</th>
								<th>' . _('Old RL') . '</th>
							</tr>';
			echo '<div>
				<table class="selection">
				<thead>' . $TableHeader . '</thead>
				<tbody>';
		}
		if ($EmailText != ''){
			$EmailText = $EmailText . 'Adjustment RL for Toko Online for existing online orders' . "\n";
		}
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			/* set the RL to the total of qty requested by customers */
			SetReorderLevel("OnlineSales", $MyRow['stkcode'],'TOKWS', 0, $MyRow['totalqty'], $UpdateDB);
			if ($ShowMessages){
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stkcode'] . '">' . $MyRow['stkcode'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td class="number">' . locale_number_format($MyRow['totalqty'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['reorderlevel'],0) . '</td>
					</tr>';
				$i++;
			}
			if ($EmailText != ''){
				$EmailText = $EmailText . $MyRow['stkcode'] . " Old RL = " . $MyRow['reorderlevel'] . " New RL = " . $MyRow['totalqty'] . "\n";
			}
		}
		if ($ShowMessages){
			echo '</tbody></table></div>';
		}
	}else{
		if ($ShowMessages){
			prnMsg(_('No Online Shop orders to be processed at this time.'),'info');
		}
		if ($EmailText != ''){
			$EmailText = $EmailText . "No Online Shop orders to be processed at this time" . "\n";
		}
	}
	return $EmailText;
}

/**************************************************************************************************************
* Adjusts reorder levels for packaging items in a specified warehouse (gudang).
* It first updates the gudang's own RL factor and days based on the maximums of the shops it supplies.
* Then, it sets the RL for each packaging item in the gudang to the sum of the RLs of that item in the shops it supplies.
*
* @param string $GudangCode - The location code of the warehouse.
* @param float $FactorGudangPackaging - A factor to apply to the RL factor and days for the gudang.
* @param bool $ShowMessages - Whether to display messages in the UI.
* @param bool $UpdateDB - Whether to update the database with new reorder levels.
* @param string $RootPath - Root path of the application.
* @param string $EmailText - Current email text to be appended with operation results.
* 
* @return string - Updated email text containing results of operations.
**************************************************************************************************************/
function AdjustPackagingGudang($GudangCode, $FactorGudangPackaging, $ShowMessages, $UpdateDB, $RootPath, $EmailText){

	$Message = "Adjusting RL for Packaging Gudang " . $GudangCode ;
	if ($ShowMessages){
		prnMsg($Message,'info');
	}
	if ($EmailText != ''){
		$EmailText = $EmailText . "\n" . $Message . "\n";
	}

	// updating the RL settings for packaging, just in case any of the dependant shops has change its settings and affects the gudang
	$SQL = "SELECT  MAX(locations.rlfactorforpackaging) AS rlfactor,
					MAX(locations.rldaysforpackaging) AS rldays
			FROM locations
			WHERE locations.packagingfrom = '" . $GudangCode . "'
				AND locations.loccode != '" . $GudangCode . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$RLFactorGudang = round($MyRow['rlfactor']*$FactorGudangPackaging, 2);
		$RLDaysGudang = round($MyRow['rldays']*$FactorGudangPackaging, 0);
		$Text = $GudangCode . ' RL Factor for Packaging = ' . $RLFactorGudang;
		if ($ShowMessages){
			ShowWarningTitle($Text);
		}
		if ($EmailText != ''){
			$EmailText = $EmailText . $Text . "\n";
		}
		$Text = $GudangCode . ' RL Days for Packaging = ' . $RLDaysGudang;
		if ($ShowMessages){
			ShowWarningTitle($Text);
		}
		if ($EmailText != ''){
			$EmailText = $EmailText . $Text . "\n";
		}
		$SQL = "UPDATE locations
				SET rlfactorforpackaging = '" . $RLFactorGudang ."',
					rldaysforpackaging = '" . $RLDaysGudang ."'
				WHERE loccode = '". $GudangCode ."'";
		$ErrMsg = 'Could not update RL packaging settings for Gudang because';
		$Result = DB_query($SQL,$ErrMsg);
	}	

	// Now, update the RL for the items to be stocked at the gudang
	$SQL = "SELECT  stockmaster.stockid,
					SUM(locstock.reorderlevel) AS rl
			FROM locations, locstock, stockmaster
			WHERE locations.loccode = locstock.loccode
				AND stockmaster.stockid = locstock.stockid
				AND locations.packagingfrom = '" . $GudangCode . "'
				AND locations.loccode != '" . $GudangCode . "'
				AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				AND stockmaster.discontinued = 0
			GROUP BY stockmaster.stockid
			ORDER BY stockmaster.stockid";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$Text = $GudangCode . ' ' . $MyRow['stockid'] . ' New RL = ' . $MyRow['rl'];
			if ($ShowMessages){
				ShowWarningTitle($Text);
			}
			if ($EmailText != ''){
				$EmailText = $EmailText . $Text . "\n";
			}
			SetReorderLevel("PackagingGudangOptimization", $MyRow['stockid'], $GudangCode, 0, $MyRow['rl'], $UpdateDB);
		}
	}	

	return $EmailText;
}

/**************************************************************************************************************
* Adjusts reorder levels for packaging items for a given shop type based on sales history.
* Iterates through shops of the specified type and their associated packaging items.
*
* @param int $DaysSales - The number of past days of sales data to consider.
* @param string $ShopType - The type of shop (e.g., "SHOPKL", "SHOPBL").
* @param bool $ShowMessages - Whether to display messages in the UI.
* @param bool $UpdateDB - Whether to update the database with new reorder levels.
* @param string $RootPath - Root path of the application.
* @param string $EmailText - Current email text to be appended with operation results.
* 
* @return string|void - Updated email text, or void if ShopType is invalid.
**************************************************************************************************************/
function AdjustPackaging($DaysSales, $ShopType, $ShowMessages, $UpdateDB, $RootPath, $EmailText){
	
	if($ShopType == 'SHOPKL'){
		$ListOfItems = LIST_ITEMS_KAPAL_LAUT_PACKAGING;
	}elseif ($ShopType == 'SHOPBL'){
		$ListOfItems = LIST_ITEMS_BLINK_PACKAGING;
	}else{
		return;
	}

	if ($EmailText != ''){
		$EmailText = $EmailText . "\n" . "Adjust Packaging" . "\n" .
					"For " . $DaysSales . " days of sales" . "\n" .
					"Type of Shops Using Packaging Control = " . $ShopType . "\n" .
					"List Items Using Packaging Control = " . CleanListToPrint($ListOfItems) . "\n\n" ;
	}

	$Items = ListToArray($ListOfItems,",");
	$CountItem = count($Items);

	$SQL = "SELECT locations.loccode
			FROM locations
			WHERE locations.typeloc = '" . $ShopType . "'";
	$Resultloc = DB_query($SQL);
	if (DB_num_rows($Resultloc) != 0){
		while ($MyLoc = DB_fetch_array($Resultloc)) {
			$iItem = 0;
			while ($iItem < $CountItem){
				$EmailText = AdjustPackagingItemByShop($Items[$iItem], $MyLoc['loccode'], $DaysSales, $ShowMessages, $UpdateDB, $RootPath, $EmailText);
				$iItem++;
			}
		}
	}	
	return $EmailText;
}

/**************************************************************************************************************
* Calculates and sets the reorder level for a specific packaging item at a specific shop
* based on its usage over a defined number of days and the shop's configured RL days for packaging.
* The new RL is the daily usage multiplied by RL days, with a minimum applied.
*
* @param string $Item - The stock ID of the packaging item.
* @param string $Shop - The location code of the shop.
* @param int $DaysSales - The number of past days of usage data to consider.
* @param bool $ShowMessages - Whether to display messages in the UI.
* @param bool $UpdateDB - Whether to update the database with the new reorder level.
* @param string $RootPath - Root path of the application.
* @param string $EmailText - Current email text to be appended with operation results.
* 
* @return string - Updated email text containing results of operations.
**************************************************************************************************************/
function AdjustPackagingItemByShop($Item, $Shop, $DaysSales, $ShowMessages, $UpdateDB, $RootPath, $EmailText) {

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$DaysSales));
	$SQL = "SELECT 	locations.locationname,
					locations.rldaysforpackaging,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = '" . $Item . "'
							AND packagingused.date >= '" . $FromDate . "') AS Sales,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '" . $Item . "') AS RL
			FROM locations
			WHERE locations.loccode = '" . $Shop . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0) {
		$MyRow = DB_fetch_array($Result);
		// New RL is the daily needs x number of days to keep as RL
		$NewRL = 0;
		if ($DaysSales > 0) {
			$Sales = $MyRow['Sales'] ?? 0; // Ensure we have a numeric value even if null
			$NewRL = max(round($Sales / $DaysSales * $MyRow['rldaysforpackaging'], 0), 
					MIN_REORDER_LEVEL_PACKAGING_ITEM_PER_SHOP);
		} else {
			$NewRL = MIN_REORDER_LEVEL_PACKAGING_ITEM_PER_SHOP;
		}
		$OldRL = $MyRow['RL'];
		if ($NewRL != $OldRL) {
			$Text = $Shop . ' ' . $Item . 
				' Old RL = ' . $OldRL . 
				' Used ' . $DaysSales . ' days = ' . ($MyRow['Sales'] ?? 0) . 
				' New RL = ' . $NewRL;
			if ($ShowMessages) {
				ShowWarningTitle($Text);
			}
			if ($EmailText != '') {
				$EmailText = $EmailText . $Text . "\n";
			}
			SetReorderLevel("PackagingOptimization", $Item, $Shop, $OldRL, $NewRL, $UpdateDB);
		}
	}
	return $EmailText;
}


<?php

function KL_DailyRLAdjustmentsForOnline($ShowMessages, $updateDB, $RootPath, $EmailText){

	$EmailText = OnlineReorderLevelAdjustments($ShowMessages, $updateDB, $RootPath, $EmailText); // Updates RL for online orders
	
	return $EmailText;
}

function KL_DailyRLAdjustmentsForKL($ShowMessages, $updateDB, $RootPath, $EmailText){

	// For KL SHOPS
	$Shops = NumberOfShops("SHOPKL");
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Number of Shops Kapal-Laut = " . $Shops . "\n";
	}
	if ($ShowMessages){
		prnMsg('Number of Shops Kapal-laut = ' . $Shops,'info');
	}

	$EmailText = SetRLForTopSalesItems("SHOPKL",   1,  50, ($Shops * 7),       999999, 5, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL",   1,  50, ($Shops * 6), ($Shops * 7), 4, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL",   1,  50, ($Shops * 5), ($Shops * 6), 3, $ShowMessages, $updateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPKL",  51, 100, ($Shops * 6),       999999, 4, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL",  51, 100, ($Shops * 5), ($Shops * 6), 3, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL",  51, 100, ($Shops * 4), ($Shops * 5), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, ($Shops * 5),       999999, 3, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, ($Shops * 4), ($Shops * 5), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, ($Shops * 3), ($Shops * 4), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);

	$EmailText = SetRLForLowSalesHighRL("SHOPKL",  30, 5, 4, ($Shops * 6), $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPKL",  20, 4, 3, ($Shops * 5), $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPKL",  10, 3, 2, ($Shops * 4), $ShowMessages, $updateDB, $RootPath, $EmailText);
	
	return $EmailText;
}

function KL_DailyRLAdjustmentsForBlink($ShowMessages, $updateDB, $RootPath, $EmailText){

	// For BLINK SHOPS
	$Shops = NumberOfShops("SHOPBL");
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Number of Shops Blink = " . $Shops . "\n";
	}
	if ($ShowMessages){
		prnMsg('Number of Shops Blink = ' . $Shops,'info');
	}

	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, ($Shops * 7),       999999, 5, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, ($Shops * 6), ($Shops * 7), 4, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, ($Shops * 5), ($Shops * 6), 3, $ShowMessages, $updateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, ($Shops * 6),       999999, 4, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, ($Shops * 5), ($Shops * 6), 3, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, ($Shops * 4), ($Shops * 5), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, ($Shops * 5),       999999, 3, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, ($Shops * 4), ($Shops * 5), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, ($Shops * 3), ($Shops * 4), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);

	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  30, 5, 4, ($Shops * 6), $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  20, 4, 3, ($Shops * 5), $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  10, 3, 2, ($Shops * 4), $ShowMessages, $updateDB, $RootPath, $EmailText);

	return $EmailText;
}

function KL_DailyRLAdjustmentsForOutlet($ShowMessages, $updateDB, $RootPath, $EmailText){

	// for OUTLET SHOPS
	$Shops = NumberOfShops("SHOPOU");
	$RegularKLShopsSellingDiscount = NumberOfRegularShopsSellingDiscount("SHOPKL");
	$RegularBlinkShopsSellingDiscount = NumberOfRegularShopsSellingDiscount("SHOPBL");
	
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Number of Shops Outlet = " . $Shops . "\n";
		$EmailText = $EmailText . "\n" . "Number of regular KL Shops selling discount = " . $RegularKLShopsSellingDiscount . "\n";
		$EmailText = $EmailText . "\n" . "Number of regular Blink Shops selling discount = " . $RegularBlinkShopsSellingDiscount . "\n";
	}
	if ($ShowMessages){
		prnMsg('Number of Shops Outlet = ' . $Shops,'info');
		prnMsg('Number of regular KL Shops selling discount = ' . $RegularKLShopsSellingDiscount,'info');
		prnMsg('Number of regular Blink Shops selling discount = ' . $RegularBlinkShopsSellingDiscount,'info');
	}

	$ShopsDiscountKL = $Shops + $RegularKLShopsSellingDiscount;
	$EmailText = SetRLForTopSalesItems("OUTKL",   1,  50, ($ShopsDiscountKL * 5),       999999, 4, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTKL",   1,  50, ($ShopsDiscountKL * 4), ($ShopsDiscountKL * 5), 3, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTKL",   1,  50, ($ShopsDiscountKL * 3), ($ShopsDiscountKL * 4), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("OUTKL",  51, 100, ($ShopsDiscountKL * 4),       999999, 3, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTKL",  51, 100, ($ShopsDiscountKL * 3), ($ShopsDiscountKL * 4), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);
	
	$ShopsDiscountBL = $Shops + $RegularBlinkShopsSellingDiscount;
	$EmailText = SetRLForTopSalesItems("OUTBL",   1,  50, ($ShopsDiscountBL * 5),       999999, 4, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTBL",   1,  50, ($ShopsDiscountBL * 4), ($ShopsDiscountBL * 5), 3, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTBL",   1,  50, ($ShopsDiscountBL * 3), ($ShopsDiscountBL * 4), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);

	$EmailText = SetRLForTopSalesItems("OUTBL",  51, 100, ($ShopsDiscountBL * 4),       999999, 3, $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = SetRLForTopSalesItems("OUTBL",  51, 100, ($ShopsDiscountBL * 3), ($ShopsDiscountBL * 4), 2, $ShowMessages, $updateDB, $RootPath, $EmailText);

	return $EmailText;
}

function KL_DailyRLRebalancing($ShowMessages, $updateDB, $RootPath, $EmailText){
	
	// These functions does not need to be segregated by type of shop, as it only takes care of shops with RL > 0
	$EmailText = RebalancingBetweenShops(60, $ShowMessages, $updateDB, $RootPath, $EmailText);

	return $EmailText;
}

function KL_DailyRLZeroNotAvailable($ShowMessages, $updateDB, $RootPath, $EmailText){

	$EmailText = SetRLZeroForNotAvailableItems($ShowMessages, $updateDB, $RootPath, $EmailText);

	return $EmailText;
}

function KL_DailyRLAdjustmentsForPackaging($ShowMessages, $updateDB, $RootPath, $EmailText){

	$EmailText = AdjustPackaging(60, 'SHOPKL', $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = AdjustPackaging(60, 'SHOPBL', $ShowMessages, $updateDB, $RootPath, $EmailText);
	$EmailText = AdjustPackagingGudang('PACKU', FACTOR_GUDANG_PACKAGING, $ShowMessages, $updateDB, $RootPath, $EmailText);
	
	return $EmailText;
}

function RebalancingBetweenShops($maxdays, $ShowMessages, $updateDB, $RootPath, $EmailText){
	/* 
		items 
		that some stock is needed at some shops, 
		and there is at least one shop with more than 0 item 
		and stock at kantor is zero 
		and there is no transfer alive for this item 
		
	*/

	$ItemsRebalanced = 0;
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Rebalancing stock between shops." . "\n\n";
	}

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.categoryid,
					stockmaster.description,
					(SELECT locstock.loccode
						FROM locstock, locations
						WHERE stockmaster.stockid  = locstock.stockid 
							AND locstock.loccode = locations.loccode
							AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
							AND locstock.quantity < locstock.reorderlevel
						ORDER BY reorderlevel DESC
						LIMIT 1) AS locationneeded
			FROM stockmaster
			WHERE stockmaster.categoryid NOT IN ('SHDISP', 'SHPACK')
				AND EXISTS (SELECT *
							FROM locstock, locations
							WHERE stockmaster.stockid  = locstock.stockid 
								AND locstock.loccode = locations.loccode
								AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
								AND locstock.quantity < locstock.reorderlevel)
				AND EXISTS (SELECT *
							FROM locstock, locations
							WHERE stockmaster.stockid  = locstock.stockid 
								AND locstock.loccode = locations.loccode
								AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
								AND locstock.quantity > 0)
				AND EXISTS (SELECT *
						FROM locstock
						WHERE stockmaster.stockid  = locstock.stockid 
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
			echo $TableHeader;
		}
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			}
			$rebalancinglocationfrom = "";
			$strategy = "";
			$PrintLine = TRUE;

			//Look for the WORST location with overstock.
			$Locationoverstock  = WorstLocationForItem($MyRow['stockid'], "OVERSTOCK", $maxdays);
			if ($Locationoverstock == ""){
				// NO location with overstock
				// We need to reduce RL at the worst selling location with some stock available (qty > 0)
				$Locationworst  = WorstLocationForItem($MyRow['stockid'], "AVAILABLE", $maxdays);
				if ($Locationworst == ""){
					// Does not exist any shop with available stock. This was the last one!
					// No need to do anything!!!
					$rebalancinglocationfrom = "";
					$strategy = "No shop with available stock. No RL changed";
				}else{
					// let's distribute available stock between the shops with RL > 0.
					// if RL = 0 we suppose we do not want it there for any reason 
					$QtyToDistribute = QtyAvailable($MyRow['stockid'], "ALLSHOPS");
					if ($EmailText!=''){
						$EmailText = $EmailText . $MyRow['stockid']. " Quantity to distribute = " . $QtyToDistribute . "\n";
					}
					$QOH =$QtyToDistribute;
					$LocationsDistributed = 0;
					
					// order should also account if the shop is supposed to have all the collection or not,
					// so we order firsts shops with all collection = TRUE, as normally shops with all collection = FALSE
					// are the small shops or slow shops
					
					if (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_TEST)){
						$OrderBy = " locations.alltestitems DESC, ";
					}elseif (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_STABLE)){
						$OrderBy = " locations.allstableitems DESC, ";
					}elseif (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)){
						$OrderBy = " locations.allnopoitems DESC, ";
					}else{
						$OrderBy = "";
					}
					$SQLDistribution = "SELECT locstock.loccode, 
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
														
					$Resultdistribution = DB_query($SQLDistribution);
					$LocationsToDistribute = DB_num_rows($Resultdistribution);
					if ($LocationsToDistribute != 0){
						while ($MyDistribution = DB_fetch_array($Resultdistribution)) {
							$NewRL = ceil($QtyToDistribute / ($LocationsToDistribute - $LocationsDistributed));
							$NewRL = MaxRLCorrectionSomeModels($MyRow['stockid'], $MyDistribution['loccode'], $NewRL);
							SetReorderLevel("Rebalancing", $MyRow['stockid'], $MyDistribution['loccode'], $MyDistribution['oldrl'], $NewRL, $updateDB);
							$strategy = "Distribute all available stock between shops with RL > 0";
							$QtyToDistribute = $QtyToDistribute - $NewRL;
							$LocationsDistributed++;
							if ($ShowMessages){
								$k = StartEvenOrOddRow($k);
								echo '<td class="number">'.$i.'</td>
									<td>'.$CodeLink.'</td>
									<td>'.$MyRow['categoryid'].'</td>
									<td>'.$MyRow['description'].'</td>
									<td>'.$MyDistribution['loccode'].'</td>
									<td class="number">'.locale_number_format($MyDistribution['oldrl'],0).'</td>
									<td>'.$MyRow['locationneeded'].'</td>
									<td>'.$strategy.'</td>
									</tr>';
								$PrintLine = FALSE;
							}
							if ($EmailText!=''){
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
						$Location = "";
						$strategy = "No shop to distribute";
					}
				}
			}else{
				// We have some overstock location. When transferrng from TOKO to kantor will be rebalanced.
				// No need to do anything, as the overstock item in that location will return to kantor 
				// and from kantor will be sent to the location needing it.
				$rebalancinglocationfrom = $Locationoverstock;
				$strategy = "Overstock available in some shop. No RL changed";
			}
			if ($ShowMessages){
				if ($PrintLine){
					echo '<td class="number">'.$i.'</td>
						<td>'.$CodeLink.'</td>
						<td>'.$MyRow['categoryid'].'</td>
						<td>'.$MyRow['description'].'</td>
						<td>'.$rebalancinglocationfrom.'</td>
						<td class="number">'."".'</td>
						<td>'.$MyRow['locationneeded'].'</td>
						<td>'.$strategy.'</td>
						</tr>';
				}
			}
			if ($EmailText!=''){
				$EmailText = $EmailText . $MyRow['stockid'] . " needed @ " . 
										$MyRow['locationneeded'] .
										" Strategy used: " . 
										$strategy . " " . 
										"\n\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}

	InsertKPI("TRANSFERS-REBALANCE-MOD", $ItemsRebalanced);

	return $EmailText;
}

function WorstLocationForItem($StockID, $Kind, $maxdays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
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

function LocationOrderForItem($StockID, $Order, $maxdays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT locstock.loccode
			FROM locstock,locations
			WHERE locstock.stockid = '" . $StockID . "'
				AND locstock.loccode = locations.loccode
				AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
			ORDER BY (SELECT COUNT(qtyinvoiced)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDate . "'
							AND salesorders.fromstkloc = locstock.loccode
							AND salesorderdetails.stkcode = '". $StockID . "') DESC
			LIMIT ". $Order . ", 1";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Location = $MyRow['loccode'];
	}else{
		$Location = "";
	}
	return $Location;
}

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

function SetRLZeroForNotAvailableItems($ShowMessages, $updateDB, $RootPath, $EmailText){
	/* On 17/12/2013 we take out the SHOP consumables to avoid problems with the shop packagings */
	/* On 21/12/2013 we take out the SHOP packaging to avoid problems with the shop packagings */

	if ($EmailText!=''){
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
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('#') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('Description') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<td class="number">'.$i.'</td>
						<td>'.$CodeLink.'</td>
						<td>'.$MyRow['description'].'</td>
					</tr>';
				$i++;
			}
			if ($EmailText!=''){
				$EmailText = $EmailText . $MyRow['stockid'] . "\n";
			}
			SetReorderLevel("NotAvailable", $MyRow['stockid'],"SHOPS", 999999, 0, $updateDB);
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}
	return $EmailText;
}

function SetRLForTopSalesItems($ShopType, $starttopitems, $endtopitems, $minstockavailable, $maxstockavailable, $NewRL, $ShowMessages, $updateDB, $RootPath, $EmailText){

/* function SetRLForTopSalesItems Increases RL for good selling items with enough stock.
Sets Reorder Level to $NewRL 
for the items in top sales items (from $starttopitems to $endtopitems during last $daystopitems) 
with stock available higher than $minstockavailable, lower than $maxstockavailable
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
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Set RL For " . $ShopType . " top sales items range " . $starttopitems . " - " . $endtopitems . " Top Sales with RL lower than " . $NewRL . " and minimum available stock " . $minstockavailable . "\n";
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
			LIMIT " . ($starttopitems - 1) . "," . ($endtopitems - $starttopitems + 1);			

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$ShowHeader = true;
		$k = 0; //row colour counter
		$i = $starttopitems;
		while ($MyRow = DB_fetch_array($Result)) {

			$SQLQtyAvailable = "SELECT SUM(locstock.quantity) AS QtyAvailable
								FROM locstock, locations loc2
								WHERE locstock.stockid  = '" . $MyRow['stockid'] . "'
									AND locstock.loccode = loc2.loccode
									AND loc2.stockreadytosell = 1";
			$ResultQtyAvailable = DB_query($SQLQtyAvailable);
			$MyRowQtyAvailable = DB_fetch_array($ResultQtyAvailable);
			
			if (($MyRowQtyAvailable['QtyAvailable'] > $minstockavailable) 
				AND ($MyRowQtyAvailable['QtyAvailable'] <= $maxstockavailable)){
				$SQLDistribution = "SELECT locstock.loccode, 
										locstock.reorderlevel AS oldrl
									FROM locstock,locations
									WHERE locstock.stockid = '" . $MyRow['stockid'] . "'
										AND locstock.loccode = locations.loccode
										AND locations.stockreadytosell = 1
										AND locstock.reorderlevel > 0";
				$Resultdistribution = DB_query($SQLDistribution);
				$LocationsToDistribute = DB_num_rows($Resultdistribution);
				if ($LocationsToDistribute != 0){
					if ($k == 1) {
						$k = 0;
					} else {
						$k = 1;
					}
					while ($MyDistribution = DB_fetch_array($Resultdistribution)) {

						$CurrentNewRL = MaxRLCorrectionSomeModels($MyRow['stockid'], $MyDistribution['loccode'], $NewRL);

						if($MyDistribution['oldrl'] < $CurrentNewRL){
							SetReorderLevel("TopSalesLowRL", $MyRow['stockid'], $MyDistribution['loccode'], $MyDistribution['oldrl'], $CurrentNewRL, $updateDB);
							if ($ShowMessages){
								if($ShowHeader){
									$TableTitleText = 'Set RL minimum to ' . $NewRL . 
													' for Top Sales '. $starttopitems . '-'. $endtopitems . 
													' with Stock Available > '. $minstockavailable .
													' and <= '. $maxstockavailable .
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
									echo $TableHeader;
									$ShowHeader = false;
								}
								if ($k == 0) {
									echo '<tr class="EvenTableRows">';
								} else {
									echo '<tr class="OddTableRows">';
								}
								$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
								echo '<td class="number">'.$i.'</td>
									<td>'.$CodeLink.'</td>
									<td>'.$MyRow['categoryid'].'</td>
									<td>'.$MyRow['description'].'</td>
									<td class="number">'.locale_number_format($MyRowQtyAvailable['QtyAvailable'],0).'</td>
									<td>'.$MyDistribution['loccode'].'</td>
									<td class="number">'.locale_number_format($MyDistribution['oldrl'],0).'</td>
									<td class="number">'.locale_number_format($CurrentNewRL,0).'</td>
									</tr>';
							}
							if ($EmailText!=''){
								$EmailText = $EmailText . $MyRow['stockid'] . " @ " . $MyDistribution['loccode'] . " Old RL = " . $MyDistribution['oldrl'] .  " New RL = " . $CurrentNewRL . "\n";
							}
						}
					}
				}
			}
			$i++;
		}
		if ($ShowMessages){
			if(!$ShowHeader){
				echo '</table>
						</div>';
			}
		}
	}
	return $EmailText;
}

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

function SetRLForLowSalesHighRL($ShopType, $BottomPercentTopSales, $OldRL, $maxRL, $minavailablestock, $ShowMessages, $updateDB, $RootPath, $EmailText){
	/*  items bottom% in percent, 
		with stock at the shop
		with RL >= oldRL at the shop
		with less than minavailablestock at shops or office
	*/
	if ($EmailText!=''){
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
			echo $TableHeader;
		}
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			SetReorderLevel("BottomSalesHighRL", $MyRow['stockid'],$MyRow['loccode'], $MyRow['reorderlevel'], $maxRL, $updateDB);
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<td class="number">'.$i.'</td>
						<td>'.$CodeLink.'</td>
						<td>'.$MyRow['description'].'</td>
						<td>'.$MyRow['categoryid'].'</td>
						<td>'.$MyRow['loccode'].'</td>
						<td class="number">'.locale_number_format($MyRow['reorderlevel'],0).'</td>
						<td class="number">'.locale_number_format($maxRL,0).'</td>
						</tr>';
			}
			if ($EmailText!=''){
				$EmailText = $EmailText .  $MyRow['stockid'] . " @ " . $MyRow['loccode'] . " OldRL = " . locale_number_format($MyRow['reorderlevel'],0) . " NewRL = " . locale_number_format($maxRL,0) . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}
	return $EmailText;
}


function MaxRLCorrectionSomeModels($StockID, $loccode, $NewRL){
	$CurrentNewRL = $NewRL;
	return $CurrentNewRL;
}

function SetReorderLevel($reason, $StockID, $loccode, $OldRL, $NewRL, $updateDB){
	if ($updateDB){
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
			$Result = DB_query($SQL,$ErrMsg);
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
						'". $reason ."',
						'". $loccode ."',
						'". $StockID ."',
						'". $OldRL ."',
						'". $NewRL ."')";		
		$ErrMsg =_('Could not insert the KLAdjustRL Log');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		}
	}
}


function OnlineReorderLevelAdjustments($ShowMessages, $updateDB, $RootPath, $EmailText){

	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "OnlineReorderLevelAdjustments" . "\n\n";
	}
	
	// set all RL=0 for toko online
	if($updateDB){
		$RLSQL = "UPDATE locstock
					SET reorderlevel = 0 
					WHERE reorderlevel > 0 AND loccode = ". CODE_ONLINE_SHOP ."";
		$ErrMsg =_('Error in function OnlineReorderLevelAdjustments');
		$DbgMsg = _('The following SQL to update reorder levels was used');
		$Result = DB_query($RLSQL,$ErrMsg,$DbgMsg,true);		
		if ($ShowMessages){
			prnMsg(_('Reset all RL=0 for location Shop Online'),'info');
		}
		if ($EmailText!=''){
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
				AND salesorderdetails.completed= 0
			GROUP BY salesorderdetails.stkcode
			ORDER BY salesorderdetails.stkcode";
				
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Adjustment RL for Toko Online');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('#') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('QOH = New RL') . '</th>
								<th>' . _('Old RL') . '</th>
							</tr>';
			echo $TableHeader;
		}
		if ($EmailText!=''){
			$EmailText = $EmailText . 'Adjustment RL for Toko Online for existing online orders' . "\n";
		}
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			/* set the RL to the total of qty requested by customers */
			SetReorderLevel("OnlineSales", $MyRow['stkcode'],'TOKWS', 0, $MyRow['totalqty'], $updateDB);
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stkcode'] . '">' . $MyRow['stkcode'] . '</a>';
				echo '<td class="number">'.$i.'</td>
						<td>'.$CodeLink.'</td>
						<td class="number">'.locale_number_format($MyRow['totalqty'],0).'</td>
						<td class="number">'.locale_number_format($MyRow['reorderlevel'],0).'</td>
						</tr>';
				$i++;
			}
			if ($EmailText!=''){
				$EmailText = $EmailText . $MyRow['stkcode'] . " Old RL = " . $MyRow['reorderlevel'] . " New RL = " . $MyRow['totalqty'] . "\n";
			}
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}else{
		if ($ShowMessages){
			prnMsg(_('No Online Shop orders to be processed at this time.'),'info');
		}
		if ($EmailText!=''){
			$EmailText = $EmailText . "No Online Shop orders to be processed at this time" . "\n";
		}
	}
	return $EmailText;
}

function AdjustPackagingGudang($GudangCode, $FactorGudangPackaging, $ShowMessages, $updateDB, $RootPath, $EmailText){

	$Message = "Adjusting RL for Packaging Gudang " . $GudangCode ;
	if ($ShowMessages){
		prnMsg($Message,'info');
	}
	if ($EmailText!=''){
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
		if ($EmailText!=''){
			$EmailText = $EmailText . $Text . "\n";
		}
		$Text = $GudangCode . ' RL Days for Packaging = ' . $RLDaysGudang;
		if ($ShowMessages){
			ShowWarningTitle($Text);
		}
		if ($EmailText!=''){
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
			if ($EmailText!=''){
				$EmailText = $EmailText . $Text . "\n";
			}
			SetReorderLevel("PackagingGudangOptimization", $MyRow['stockid'], $GudangCode, 0, $MyRow['rl'], $updateDB);
		}
	}	

	return $EmailText;
}


function AdjustPackaging($DaysSales, $ShopType, $ShowMessages, $updateDB, $RootPath, $EmailText){
	
	if($ShopType == 'SHOPKL'){
		$ListOfItems = LIST_ITEMS_KAPAL_LAUT_PACKAGING;
	}elseif ($ShopType == 'SHOPBL'){
		$ListOfItems = LIST_ITEMS_BLINK_PACKAGING;
	}else{
		return;
	}

	if ($EmailText!=''){
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
				$EmailText = AdjustPackagingItemByShop($Items[$iItem], $MyLoc['loccode'], $DaysSales, $ShowMessages, $updateDB, $RootPath, $EmailText);
				$iItem++;
			}
		}
	}	
	return $EmailText;
}

function AdjustPackagingItemByShop($Item, $Shop, $DaysSales, $ShowMessages, $updateDB, $RootPath, $EmailText){

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$DaysSales));

	$SQL = "SELECT 	locations.locationname,
					locations.rldaysforpackaging,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = '" . $Item . "'
							AND packagingused.date >= '". $FromDate ."') AS sales,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '" . $Item . "') AS rl
			FROM locations
			WHERE locations.loccode = '" . $Shop . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		// New RL is the daily needs x number of days to keep as RL
		$NewRL = max(round($MyRow['sales'] / $DaysSales * $MyRow['rldaysforpackaging'],0),MIN_REORDER_LEVEL_PACKAGING_ITEM_PER_SHOP);
		$OldRL = $MyRow['rl'];
		if ($NewRL != $OldRL){
			$Text = $Shop . ' ' . $Item .  
				' Old RL = ' . $OldRL . 
				' Used ' . $DaysSales . ' days = ' . $MyRow['sales'] . 
				' New RL = ' . $NewRL;
			if ($ShowMessages){
				ShowWarningTitle($Text);
			}
			if ($EmailText!=''){
				$EmailText = $EmailText . $Text . "\n";
			}
			SetReorderLevel("PackagingOptimization", $Item, $Shop, $OldRL, $NewRL, $updateDB);
		}
	}
	return $EmailText;
}

?>

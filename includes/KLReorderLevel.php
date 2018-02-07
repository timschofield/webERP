<?php

function DailyReorderLevelAdjustments($ShowMessages, $updateDB, $RootPath, $db, $EmailText){

	$EmailText = OnlineReorderLevelAdjustments($ShowMessages, $updateDB, $RootPath, $db, $EmailText); // Updates RL for online orders

	// For KL SHOPS
	$Shops = NumberOfShops("SHOPKL", $db);
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Number of Shops Kapal-Laut = " . $Shops . "\n\n";
	}
	if ($ShowMessages){
		prnMsg('Number of Shops Kapal-laut = ' . $Shops,'info');
	}
	$EmailText = SetRLForTopSalesItems("SHOPKL",   1,  50, 60, ($Shops * 7),       999999, 6, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL",   1,  50, 60, ($Shops * 6), ($Shops * 7), 5, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL",   1,  50, 60, ($Shops * 5), ($Shops * 6), 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPKL",  51, 100, 60, ($Shops * 6),       999999, 5, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL",  51, 100, 60, ($Shops * 5), ($Shops * 6), 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL",  51, 100, 60, ($Shops * 4), ($Shops * 5), 3, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, 60, ($Shops * 5),       999999, 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, 60, ($Shops * 4), ($Shops * 5), 3, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPKL", 101, 250, 60, ($Shops * 3), ($Shops * 4), 2, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = SetRLForLowSalesHighRL("SHOPKL",  30, 6, 5, ($Shops * 6), $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPKL",  40, 5, 4, ($Shops * 5), $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPKL",  50, 4, 3, ($Shops * 4), $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	
	// For BLINK SHOPS
	$Shops = NumberOfShops("SHOPBL", $db);
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Number of Shops Blink = " . $Shops . "\n\n";
	}
	if ($ShowMessages){
		prnMsg('Number of Shops Blink = ' . $Shops,'info');
	}
	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, 60, ($Shops * 7),       999999, 6, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, 60, ($Shops * 6), ($Shops * 7), 5, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",   1,  50, 60, ($Shops * 5), ($Shops * 6), 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, 60, ($Shops * 6),       999999, 5, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, 60, ($Shops * 5), ($Shops * 6), 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL",  51, 100, 60, ($Shops * 4), ($Shops * 5), 3, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, 60, ($Shops * 5),       999999, 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, 60, ($Shops * 4), ($Shops * 5), 3, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPBL", 101, 200, 60, ($Shops * 3), ($Shops * 4), 2, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  30, 6, 5, ($Shops * 6), $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  40, 5, 4, ($Shops * 5), $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForLowSalesHighRL("SHOPBL",  50, 4, 3, ($Shops * 4), $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	// for OUTLET SHOPS
	$Shops = NumberOfShops("SHOPOU", $db);
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Number of Shops Outlet = " . $Shops . "\n\n";
	}
	if ($ShowMessages){
		prnMsg('Number of Shops Outlet = ' . $Shops,'info');
	}
	$EmailText = SetRLForTopSalesItems("SHOPOU",   1,  50, 60, ($Shops * 7),       999999, 6, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPOU",   1,  50, 60, ($Shops * 6), ($Shops * 7), 5, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPOU",   1,  50, 60, ($Shops * 5), ($Shops * 6), 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = SetRLForTopSalesItems("SHOPOU",  51, 100, 60, ($Shops * 6),       999999, 5, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPOU",  51, 100, 60, ($Shops * 5), ($Shops * 6), 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems("SHOPOU",  51, 100, 60, ($Shops * 4), ($Shops * 5), 3, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	
	// These functions does not need to be segregated by type of shop, as it only takes care of shops with RL > 0
	$EmailText = RebalancingBetweenShops(60, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = SetRLZeroForNotAvailableItems($ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	$EmailText = AdjustPackaging(60, 'SHOPKL', $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = AdjustPackaging(60, 'SHOPBL', $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = AdjustPackaging(60, 'SHOPOU', $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	
	return $EmailText;
}

function NumberOfShops($ShopType, $db){
	$SQL="SELECT COUNT(*)
		FROM locations
		WHERE typeloc LIKE '%" . $ShopType . "%'";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function isReorderLevelManuallyChanged($stockid, $loccode, $maxmanualchanges, $db){
	if ($maxmanualchanges == 0){
		return '0000-00-00';
	}
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxmanualchanges));
	$SQL="SELECT transactiondate
		FROM audittrail
		WHERE transactiondate >= '".$StartDate."'
			AND querystring LIKE '%" . $stockid . "%' 
			AND querystring LIKE '%" . $loccode . "%' 
			AND querystring LIKE '%locstock%reorderlevel%' 
		ORDER BY transactiondate DESC
		LIMIT 1";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$lastdate = $myrow['transactiondate'];
	}else{
		$lastdate = '0000-00-00';
	}
	return $lastdate;

}

function RebalancingBetweenShops($maxdays, $ShowMessages, $updateDB, $RootPath, $db, $EmailText){
	/* 
		items 
		that some stock is needed at some shops, 
		and there is at least one shop with more than 0 item 
		and stock at kantor is zero 
		and there is no transfer alive for this item 
		
	*/
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
							AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . "
							AND locstock.quantity < locstock.reorderlevel
						ORDER BY reorderlevel DESC
						LIMIT 1) AS locationneeded
			FROM stockmaster
			WHERE stockmaster.categoryid NOT IN ('SHDISP', 'SHPACK')
				AND EXISTS (SELECT *
							FROM locstock, locations
							WHERE stockmaster.stockid  = locstock.stockid 
								AND locstock.loccode = locations.loccode
								AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . "
								AND locstock.quantity < locstock.reorderlevel)
				AND EXISTS (SELECT *
							FROM locstock, locations
							WHERE stockmaster.stockid  = locstock.stockid 
								AND locstock.loccode = locations.loccode
								AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . "
								AND locstock.quantity > 0)
				AND EXISTS (SELECT *
						FROM locstock
						WHERE stockmaster.stockid  = locstock.stockid 
							AND locstock.loccode = " . CODE_KANTOR . "
							AND locstock.quantity = 0)
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  recqty < shipqty
							AND loctransfers.stockid =  stockmaster.stockid)
			ORDER BY stockmaster.stockid";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Rebalancing between shops (Stock available at kantor = 0)') . '</strong></p>';
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
		while ($myrow = DB_fetch_array($result)) {
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			}
			$rebalancinglocationfrom = "";
			$strategy = "";
			$PrintLine = TRUE;

			//Look for the WORST location with overstock.
			$locationoverstock  = WorstLocationForItem($myrow['stockid'], $myrow['categoryid'], "OVERSTOCK", $maxdays, $db);
			if ($locationoverstock == ""){
				// NO location with overstock
				// We need to reduce RL at the worst selling location with some stock available (qty > 0)
				$locationworst  = WorstLocationForItem($myrow['stockid'], $myrow['categoryid'], "AVAILABLE", $maxdays, $db);
				if ($locationworst == ""){
					// Does not exist any shop with available stock. This was the last one!
					// No need to do anything!!!
					$rebalancinglocationfrom = "";
					$strategy = "No stock available at shops. No RL changed";
				}else{
					// let's distribute available stock between the shops with RL > 0.
					// if RL = 0 we suppose we do not want it there for any reason 
					$QtyToDistribute = QtyAvailable($myrow['stockid'], "ALLSHOPS", $db);
					$QOH =$QtyToDistribute;
					$LocationsDistributed = 0;
					
					$SQLDistribution = "SELECT locstock.loccode, 
											locstock.reorderlevel AS oldrl
										FROM locstock, locations
										WHERE  locstock.loccode = locations.loccode
											AND locstock.stockid = '" . $myrow['stockid'] . "'
											AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . "
											AND locstock.reorderlevel > 0 
										ORDER BY locations.priority ASC, 
												(SELECT COUNT(qtyinvoiced)
													FROM salesorderdetails, salesorders
													WHERE salesorderdetails.orderno = salesorders.orderno
														AND salesorderdetails.completed = 1
														AND salesorders.orddate >= '". $StartDate . "'
														AND salesorders.fromstkloc = locstock.loccode
														AND salesorderdetails.stkcode = '". $myrow['stockid'] . "') DESC, 
												(SELECT COUNT(qtyinvoiced)
													FROM salesorderdetails, salesorders
													WHERE salesorderdetails.orderno = salesorders.orderno
														AND salesorderdetails.completed = 1
														AND salesorders.orddate >= '". $StartDate . "'
														AND salesorders.fromstkloc = locstock.loccode) DESC";
														
					$resultdistribution = DB_query($SQLDistribution);
					$LocationsToDistribute = DB_num_rows($resultdistribution);
					if ($LocationsToDistribute != 0){
						while ($mydistribution = DB_fetch_array($resultdistribution)) {
							$NewRL = ceil($QtyToDistribute / ($LocationsToDistribute - $LocationsDistributed));
							$NewRL = MaxRLCorrectionSomeModels($myrow['stockid'], $mydistribution['loccode'], $NewRL);
							SetReorderLevel("Rebalancing", $myrow['stockid'], $mydistribution['loccode'], $mydistribution['oldrl'], $NewRL, $updateDB, $db);
							if($mydistribution['oldrl'] != $NewRL){
								$strategy = "QOH=" . $QOH . ". Set RL at ".$mydistribution['loccode'] . "= " . locale_number_format($NewRL,0);
							}else{
								$strategy = "QOH=" . $QOH . ". RL OK.";
							}
							$QtyToDistribute = $QtyToDistribute - $NewRL;
							$LocationsDistributed++;
							if ($ShowMessages){
								$k = StartEvenOrOddRow($k);
								printf('<td class="number">%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td class="number">%s</td>
									<td>%s</td>
									<td>%s</td>
									</tr>', 
									$i, 
									$CodeLink, 
									$myrow['categoryid'], 
									$myrow['description'], 
									$mydistribution['loccode'],
									locale_number_format($mydistribution['oldrl'],0),
									$myrow['locationneeded'],
									$strategy
									);
								$PrintLine = FALSE;
							}
							if ($EmailText!=''){
								$EmailText = $EmailText . "\n" . $myrow['stockid'] . " @" . 
																$mydistribution['loccode'] ." " . 
																locale_number_format($mydistribution['oldrl'],0) ." Needed at:" . 
																$myrow['locationneeded'] ." " . 
																$strategy ." " . 
																"\n";
							}
						}
					}else{
						$location = "";
					}
				}
			}else{
				// We have some overstock location. When transferrng from TOKO to kantor will be rebalanced.
				// No need to do anything!!!
				$rebalancinglocationfrom = $locationoverstock;
				$strategy = "Overstock available. No RL changed";
			}
			if ($ShowMessages){
				if ($PrintLine){
					printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['categoryid'], 
						$myrow['description'], 
						$rebalancinglocationfrom,
						"",
						$myrow['locationneeded'],
						$strategy
						);
				}
			}
			if ($EmailText!=''){
				$EmailText = $EmailText . "\n" . $myrow['stockid'] . " " . 
												$myrow['categoryid'] . " " . 
												$rebalancinglocationfrom ." " . 
												"---" . " " . 
												$myrow['locationneeded'] ." " . 
												$strategy ." " . 
												"\n";
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

function WorstLocationForItem($stockid, $stockcat, $kind, $maxdays, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT locstock.loccode
			FROM locstock, locations
			WHERE locstock.loccode = locations.loccode
				AND locstock.stockid = '" . $stockid . "'";

	if ($kind == "OVERSTOCK"){
		$SQL = $SQL . " AND locstock.quantity > locstock.reorderlevel"; 
	}elseif ($kind == "AVAILABLE"){
		$SQL = $SQL . " AND locstock.quantity > 0 "; 
	}

	$SQL = $SQL . "	AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . "
					ORDER BY locations.priority DESC,
					(SELECT COUNT(qtyinvoiced)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDate . "'
							AND salesorders.fromstkloc = locstock.loccode
							AND salesorderdetails.stkcode = '". $stockid . "') ASC";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$location = $myrow['loccode'];
	}else{
		$location = "";
	}
	return $location;
}

function LocationOrderForItem($stockid, $order, $maxdays, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT locstock.loccode
			FROM locstock,locations
			WHERE locstock.stockid = '" . $stockid . "'
				AND locstock.loccode = locations.loccode
				AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . "
			ORDER BY (SELECT COUNT(qtyinvoiced)
						FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.completed = 1
							AND salesorders.orddate >= '". $StartDate . "'
							AND salesorders.fromstkloc = locstock.loccode
							AND salesorderdetails.stkcode = '". $stockid . "') DESC
			LIMIT ". $order . ", 1";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$location = $myrow['loccode'];
	}else{
		$location = "";
	}
	return $location;
}

function QtyAvailable($stockid, $location, $db){
	$SQL = "SELECT SUM(locstock.quantity) AS total
			FROM locstock,locations
			WHERE locstock.stockid = '" . $stockid . "'
				AND locstock.loccode = locations.loccode";
	if ($location == "ALLSHOPS"){
		$SQL = $SQL . " AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . " "; 
	}elseif ($location == "ALL"){
		$SQL = $SQL . " "; 
	}else{
		$SQL = $SQL . " AND locstock.loccode = '". $location . "'"; 
	}
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$qty = $myrow['total'];
	}else{
		$qty = 0;
	}
	return $qty;
}

function ActiveLocationsForItem($stockid, $db){
	$SQL = "SELECT COUNT(locstock.loccode) AS total
			FROM locstock
			WHERE locstock.stockid = '" . $stockid . "'
				AND locstock.reorderlevel > 0";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$qty = $myrow['total'];
	}else{
		$qty = 0;
	}
	return $qty;
}

function SetRLZeroForNotAvailableItems($ShowMessages, $updateDB, $RootPath, $db, $EmailText){
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
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . 'Set RL = 0 for items with NO stock available at shops or kantor. </strong></p>';
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
		while ($myrow = DB_fetch_array($result)) {
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
					</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description']
						);
				$i++;
			}
			if ($EmailText!=''){
				$EmailText = $EmailText . $myrow['stockid'] . "\n";
			}
			SetReorderLevel("NotAvailable", $myrow['stockid'],"SHOPS", 999999, 0, $updateDB, $db);
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}
	return $EmailText;
}

function SetRLForTopSalesItems($ShopType, $starttopitems, $endtopitems, $daystopitems, $minstockavailable, $maxstockavailable, $NewRL, $ShowMessages, $updateDB, $RootPath, $db, $EmailText){

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
						AND prices.startdate > '". $Today. "')
19/04/2013 modification: Change the condition of "not changing price" to the new flag
24/07/2013 modification: Do not increase RL for toko online
11/03/2017 modification: filter by ShopType (brand) and simplified code with stockreadytosell
*/	
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "SetRLForTopSalesItems for " . $ShopType . "\n\n";
	}

	$Today = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$daystopitems));
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.categoryid,
					stockmaster.description,
					SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced,
					(SELECT SUM(locstock.quantity)
						FROM locstock, locations loc2
						WHERE stockmaster.stockid  = locstock.stockid
							AND locstock.loccode = loc2.loccode
							AND loc2.stockreadytosell = 1) AS QtyAvailable
			FROM salesorderdetails, salesorders, stockmaster, locations
			WHERE salesorderdetails.orderno = salesorders.orderno 
				AND salesorders.fromstkloc = locations.loccode
				AND locations.typeloc LIKE '%" . $ShopType . "%'
				AND stockmaster.discontinued = 0
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
				AND stockmaster.klchangingprice = 0
			GROUP BY salesorderdetails.stkcode
			ORDER BY totalinvoiced DESC
			LIMIT " . ($starttopitems - 1) . "," . ($endtopitems - $starttopitems + 1);			

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$showHeader = true;
		$k = 0; //row colour counter
		$i = $starttopitems;
		while ($myrow = DB_fetch_array($result)) {
			if (($myrow['QtyAvailable'] > $minstockavailable) 
				AND ($myrow['QtyAvailable'] <= $maxstockavailable)){

				$SQLDistribution = "SELECT locstock.loccode, 
										locstock.reorderlevel AS oldrl
									FROM locstock,locations
									WHERE locstock.stockid = '" . $myrow['stockid'] . "'
										AND locstock.loccode = locations.loccode
										AND locations.stockreadytosell = 1
										AND locstock.reorderlevel > 0";
				$resultdistribution = DB_query($SQLDistribution);
				$LocationsToDistribute = DB_num_rows($resultdistribution);
				if ($LocationsToDistribute != 0){
					if ($k == 1) {
						$k = 0;
					} else {
						$k = 1;
					}
					while ($mydistribution = DB_fetch_array($resultdistribution)) {

						$CurrentNewRL = MaxRLCorrectionSomeModels($myrow['stockid'], $mydistribution['loccode'], $NewRL);

						if($mydistribution['oldrl'] < $CurrentNewRL){
							SetReorderLevel("TopSalesAdjust", $myrow['stockid'], $mydistribution['loccode'], $mydistribution['oldrl'], $CurrentNewRL, $updateDB, $db);
							if ($ShowMessages){
								if($showHeader){
									echo '<p class="page_title_text" align="center"><strong>' . 'Set RL minimum to ' . $NewRL . 
																								' for Top Sales '. $starttopitems . '-'. $endtopitems . 
																								' with Stock Available > '. $minstockavailable .
																								' and <= '. $maxstockavailable .
																								' at '. $ShopType .
																								'</strong></p>';
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
									$showHeader = false;
								}
								if ($k == 0) {
									echo '<tr class="EvenTableRows">';
								} else {
									echo '<tr class="OddTableRows">';
								}
								$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
								printf('<td class="number">%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td class="number">%s</td>
									<td>%s</td>
									<td class="number">%s</td>
									<td class="number">%s</td>
									</tr>', 
									$i, 
									$CodeLink, 
									$myrow['categoryid'], 
									$myrow['description'], 
									locale_number_format($myrow['QtyAvailable'],0),
									$mydistribution['loccode'],
									locale_number_format($mydistribution['oldrl'],0),
									locale_number_format($CurrentNewRL,0)
									);
							}
							if ($EmailText!=''){
								$EmailText = $EmailText . $myrow['stockid'] . " @ " . $mydistribution['loccode'] . " Old RL = " . $mydistribution['oldrl'] .  " New RL = " . $CurrentNewRL . "\n";
							}
						}
					}
				}
			}
			$i++;
		}
		if ($ShowMessages){
			if(!$showHeader){
				echo '</table>
						</div>';
			}
		}
	}
	return $EmailText;
}

function SetRLForLowSalesItems( $starttopitems, $endtopitems, $daystopitems, $NewRL, $ShowMessages, $updateDB, $RootPath, $db){

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$daystopitems));
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.categoryid,
					stockmaster.description,
					SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced
			FROM salesorderdetails, salesorders, stockmaster
			WHERE salesorderdetails.orderno = salesorders.orderno 
				AND stockmaster.discontinued = 0
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND (stockmaster.lastcategoryupdate <= '" . $StartDate . "'
					OR stockmaster.lastcategoryupdate = '0000-00-00')
				AND salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
			GROUP BY salesorderdetails.stkcode
			ORDER BY totalinvoiced DESC
			LIMIT " . ($starttopitems - 1) . "," . ($endtopitems - $starttopitems + 1);			

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$showHeader = true;
		$k = 0; //row colour counter
		$i = $starttopitems;
		while ($myrow = DB_fetch_array($result)) {
			$SQLDistribution = "SELECT locstock.loccode, 
									locstock.reorderlevel AS oldrl
								FROM locstock,locations
								WHERE locstock.stockid = '" . $myrow['stockid'] . "'
									AND locstock.loccode = locations.loccode
									AND locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . "
									AND locstock.reorderlevel > 0";
			$resultdistribution = DB_query($SQLDistribution);
			$LocationsToDistribute = DB_num_rows($resultdistribution);
			if ($LocationsToDistribute != 0){
				if ($k == 1) {
					$k = 0;
				} else {
					$k = 1;
				}
				while ($mydistribution = DB_fetch_array($resultdistribution)) {
					if($mydistribution['oldrl'] > $NewRL){
						SetReorderLevel("LowSalesAdjust", $myrow['stockid'], $mydistribution['loccode'], $mydistribution['oldrl'], $NewRL, $updateDB, $db);
						if ($ShowMessages){
							if($showHeader){
								echo '<p class="page_title_text" align="center"><strong>' . _('Set RL Max to ') . $NewRL . ' for Low Sales '. $starttopitems . '-'. $endtopitems . ' for at least ' . $daystopitems . ' days </strong></p>';
								echo '<div>';
								echo '<table class="selection">';
								$TableHeader = '<tr>
													<th>' . _('#') . '</th>
													<th>' . _('Code') . '</th>
													<th>' . _('Category') . '</th>
													<th>' . _('Description') . '</th>
													<th>' . _('Toko') . '</th>
													<th>' . _('Old RL') . '</th>
													<th>' . _('New RL') . '</th>
												</tr>';
								echo $TableHeader;
								$showHeader = false;
							}
							if ($k == 0) {
								echo '<tr class="EvenTableRows">';
							} else {
								echo '<tr class="OddTableRows">';
							}
							$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
							printf('<td class="number">%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td class="number">%s</td>
								<td class="number">%s</td>
								</tr>', 
								$i, 
								$CodeLink, 
								$myrow['categoryid'], 
								$myrow['description'], 
								$mydistribution['loccode'],
								locale_number_format($mydistribution['oldrl'],0),
								locale_number_format($NewRL,0)
								);
						}
					}
				}
			}
			$i++;
		}
		if ($ShowMessages){
			if(!$showHeader){
				echo '</table>
						</div>';
			}
		}
	}
}

function SetRLForLowSalesHighRL($ShopType, $maxdays, $oldRL, $maxRL, $minavailablestock, $ShowMessages, $updateDB, $RootPath, $db, $EmailText){
	/* No Sales during last maxdays, 
		with stock at the shop
		with RL >= oldRL at the shop
		with less than minavailablestock at shops or office
	*/
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "SetRLForLowSalesHighRL for " . $ShopType . " MaxRL = " . $maxRL . " MinAvailableStock = " . $minavailablestock . "\n\n";
	}

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));
	
	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.units, 
					locstock.quantity,
					locstock.reorderlevel,
					locstock.loccode,
					locations.locationname 
			FROM 	stockmaster,locstock,locations
			WHERE 	stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid NOT IN ('SHDISP')
					AND locstock.loccode = locations.loccode
					AND locations.typeloc LIKE '%" . $ShopType . "%'
					AND (locstock.quantity > 0)
					AND (locstock.reorderlevel >= ". $oldRL .")
					AND (SELECT SUM(locstock.quantity)
						FROM locstock, locations loc2
						WHERE stockmaster.stockid = locstock.stockid
							AND locstock.loccode = loc2.loccode
							AND loc2.stockreadytosell = 1) <= ".$minavailablestock."
					AND NOT EXISTS (SELECT * 
									FROM 	salesorderdetails, salesorders
									WHERE 	stockmaster.stockid = salesorderdetails.stkcode
											AND (salesorders.fromstkloc = locstock.loccode)
											AND (salesorderdetails.orderno = salesorders.orderno)
											AND salesorderdetails.actualdispatchdate > '". $FromDate."')
					AND NOT EXISTS (SELECT * 
									FROM 	stockmoves
									WHERE 	stockmoves.loccode = locstock.loccode 
											AND stockmoves.stockid = stockmaster.stockid
											AND stockmoves.trandate >= '". $FromDate."')
					AND EXISTS (SELECT * 
								FROM 	stockmoves
								WHERE 	stockmoves.loccode = locstock.loccode 
										AND stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate < '". $FromDate."'
										AND stockmoves.qty >0) 
			ORDER BY stockmaster.stockid";
	
	$result = DB_query($SQL);		
	
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Items (NOT Discount) with NO sales on last ') . $maxdays . ' days with RL >= ' . $oldRL . ' and stock available <= ' . $minavailablestock . ' </strong></p>';
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
								<th>' . _('Notes') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$newRL = $maxRL;
			$notes = "";
			// Check if belongs to a special category
			// comented om 2014-05-06 Change of categories structure
/*			if ($myrow['categoryid'] == "KLPRGE"){
				$newRL = $myrow['reorderlevel'];
				$notes = "KLPRGE - Tali. RL Not changed";
			}
*/
			if(($myrow['categoryid'] == 'DISC20') OR($myrow['categoryid'] == 'DISC50') OR ($myrow['categoryid'] == 'DISC80')){
				$newRL = $myrow['reorderlevel'];
				$notes = "Discounted or outlet item. RL Not changed";
			}

			// if manually reseted, not change it
			$lastManualModification = isReorderLevelManuallyChanged($myrow['stockid'], $location, $maxmanualchanges, $db);
			if ($lastManualModification != '0000-00-00'){
				$newRL = $myrow['reorderlevel'];
				$notes = "Manually changed on ". ConvertSQLDate($lastManualModification);
			}
			SetReorderLevel("LowSalesHighRL", $myrow['stockid'],$myrow['loccode'], $myrow['reorderlevel'], $newRL, $updateDB, $db);
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						$myrow['categoryid'], 
						$myrow['loccode'], 
						locale_number_format($myrow['reorderlevel'],0),
						locale_number_format($newRL,0),
						$notes
						);
			}
			if ($EmailText!=''){
				$EmailText = $EmailText .  $myrow['stockid'] . " " . $myrow['loccode'] . " OldRL = " . locale_number_format($myrow['reorderlevel'],0) . " NewRL = " . locale_number_format($newRL,0) . " " . $notes . "\n";
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

function MaxRLCorrectionSomeModels($stockid, $loccode, $NewRL){
	$CurrentNewRL = $NewRL;
	//
	// MAX correction for some locations, depending on some items
	//
/*	if ($loccode == "TOKPA"){
		if ($stockid != "GIFT-ALAR01"){
			$CurrentNewRL	= min($NewRL, 2);
		}
	}
*/
	//
	// MAX correction for some models, depending on the shops
	//
/*	if (isPlasticBag($stockid)){
		if ($loccode == "TOKKS"){
			$CurrentNewRL	= min($NewRL, 2);
		}
	}
*/	// END of MAX Corrections of New RL
	return $CurrentNewRL;
}

function SetReorderLevel($reason, $stockid, $loccode, $oldRL, $newRL, $updateDB, $db){
	if ($updateDB){
		if ($oldRL != $newRL){
			if ($loccode == "ALL"){
				$sql = "UPDATE locstock
						SET reorderlevel = '" . $newRL ."'
						WHERE stockid = '". $stockid ."'";
			}elseif ($loccode == "SHOPS"){
				$sql = "UPDATE locstock
						SET reorderlevel = '" . $newRL ."'
						WHERE stockid = '". $stockid ."'
							AND loccode IN (SELECT locations.loccode
											FROM locations
											WHERE locations.typeloc IN " . BALI_SHOPS_LIST_BY_TYPE . ")";
			}else{
				$sql = "UPDATE locstock
						SET reorderlevel = '" . $newRL ."'
						WHERE stockid = '". $stockid ."'
							AND loccode = '". $loccode ."'";
			}
			$ErrMsg =_('Could not update reorder level because');
			$result = DB_query($sql,$ErrMsg);
			// insert bthe change in the KLAdjustRL table (acting as a log of these automatic changes)
			$sql = "INSERT INTO kladjustrl 
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
						'". $stockid ."',
						'". $oldRL ."',
						'". $newRL ."')";		
		$ErrMsg =_('Could not insert the KLAdjustRL Log');
		$DbgMsg = _('The following SQL to insert the request header record was used');
		$Result = DB_query($sql,$ErrMsg,$DbgMsg,true);

		}
	}
}


function OnlineReorderLevelAdjustments($ShowMessages, $updateDB, $RootPath, $db, $EmailText){

	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "OnlineReorderLevelAdjustments" . "\n\n";
	}
	
	// set all RL=0 for toko online
	if($updateDB){
		$RLSQL = "UPDATE locstock
					SET reorderlevel = 0 
					WHERE reorderlevel > 0 AND loccode = ". CODE_ONLINE_SHOP ."";
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
				
	$result = DB_query($SQL);		
	
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Adjustment RL for Toko Online ') . ' </strong></p>';
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
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			/* set the RL to the total of qty requested by customers */
			SetReorderLevel("OnlineSales", $myrow['stkcode'],'TOKWS', 0, $myrow['totalqty'], $updateDB, $db);
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stkcode'] . '">' . $myrow['stkcode'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						locale_number_format($myrow['totalqty'],0),
						locale_number_format($myrow['reorderlevel'],0)
						);
				$i++;
			}
			if ($EmailText!=''){
				$EmailText = $EmailText . $myrow['stkcode'] . " QOH = " . $myrow['totalqty'] .  " RL = " . $myrow['reorderlevel'] . "\n";
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

function AdjustPackaging($DaysSales, $ShopType, $ShowMessages, $updateDB, $RootPath, $db, $EmailText){
	
	if($ShopType == 'SHOPKL'){
		$ListOfItems = LIST_ITEMS_KAPAL_LAUT_PACKAGING;
	}elseif ($ShopType == 'SHOPBL'){
		$ListOfItems = LIST_ITEMS_BLINK_PACKAGING;
	}elseif ($ShopType == 'SHOPOU'){
		$ListOfItems = LIST_ITEMS_OUTLET_PACKAGING;
	}

	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Adjust Packaging" . "\n\n" .
					"DaysSales = " . $DaysSales . " " .
					"RootPath = " . $RootPath . "\n" .
					"Type of Shops Using Packaging Control = " . $ShopType . "\n" .
					"List Items Using Packaging Control = " . CleanListToPrint($ListOfItems) . "\n\n" ;
	}

	$Items = ListToArray($ListOfItems,",");
	$CountItem = count($Items);

	$SQL = "SELECT locations.loccode
			FROM locations
			WHERE locations.typeloc = '" . $ShopType . "'";
	$resultloc = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		while ($myloc = DB_fetch_array($resultloc)) {
			$iItem = 0;
			while ($iItem < $CountItem){
				$EmailText = AdjustPackagingItemByShop($Items[$iItem], $myloc['loccode'], $DaysSales, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
				$iItem++;
			}
		}
	}	
	return $EmailText;
}

function AdjustPackagingItemByShop($Item, $Shop, $DaysSales, $ShowMessages, $updateDB, $RootPath, $db, $EmailText){

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
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		// New RL is the daily needs x number of days to keep as RL
		$NewRL = max(round($myrow['sales'] / $DaysSales * $myrow['rldaysforpackaging'],0),MIN_REORDER_LEVEL_PACKAGING_ITEM_PER_SHOP);
		$OldRL = $myrow['rl'];
		if ($NewRL > $OldRL){
			if ($ShowMessages){
				$text = $Shop . ' ' . $Item .  
					' Old RL = ' . $OldRL . 
					' Used ' . $DaysSales . ' days = ' . $myrow['sales'] . 
					' New RL = ' . $NewRL;
				echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
			}
			if ($EmailText!=''){
				$text = $Shop . ' ' . $Item . "\n" . 
					' Old RL = ' . $OldRL . "\n" . 
					' Used ' . $DaysSales . ' days = ' . $myrow['sales'] . "\n" . 
					' New RL = ' . $NewRL . "\n";
				$EmailText = $EmailText . $text;
			}
			SetReorderLevel("PackagingOptimization", $Item,$Shop, $OldRL, $NewRL, $updateDB, $db);
		}
	}
	return $EmailText;
}

?>

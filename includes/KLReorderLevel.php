<?php

function DailyReorderLevelAdjustments($ShowMessages, $updateDB, $RootPath, $db, $EmailText){

	$EmailText = OnlineReorderLevelAdjustments($ShowMessages, $updateDB, $RootPath, $db, $EmailText); // Updates RL for online orders

	$EmailText = SetRLForTopSalesItems(   1, 100, 60,  60, 999999, 4, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems(   1, 100, 60,  45,     60, 3, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems(   1, 100, 60,  30,     45, 2, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	$EmailText = SetRLForTopSalesItems( 101, 250, 60,  30, 999999, 2, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);

	RebalancingBetweenShops(60, $ShowMessages, $updateDB, $RootPath, $db);

/*	AdjustNoSales("TOK66", 180, 0, 400, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("TOKSA", 180, 0, 400, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("TOKKS", 100, 0, 400, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("TOKJC", 200, 0, 400, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("TOKBW", 150, 0, 400, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("TOKUB", 180, 0, 400, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("TOKMF", 180, 0, 400, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("TOKSE", 180, 0, 400, 60, $ShowMessages, $updateDB, $RootPath, $db);

	AdjustNoSales("WABOM", 180, 0, 250, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("WHAYA", 180, 0, 250, 60, $ShowMessages, $updateDB, $RootPath, $db);
	AdjustNoSales("WHINT", 180, 0, 250, 60, $ShowMessages, $updateDB, $RootPath, $db);
*/
	SetRLForLowSalesHighRL( 30, 5, 4, 60, $ShowMessages, $updateDB, $RootPath, $db);
	SetRLForLowSalesHighRL( 45, 4, 3, 45, $ShowMessages, $updateDB, $RootPath, $db);
	SetRLForLowSalesHighRL( 60, 3, 2, 30, $ShowMessages, $updateDB, $RootPath, $db);

	SetRLZeroForNotAvailableItems($ShowMessages, $updateDB, $RootPath, $db);

	$EmailText = AdjustPackaging(60, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
	
	return $EmailText;
}

function AdjustNoSales($location, $maxdays, $maxmanualchanges, $topitems, $topitemsdays, $ShowMessages, $updateDB, $RootPath, $db){
	/* No Sales during last maxdays, 
		with stock at the shop
		with RL > at the shop
	*/

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$topitemsdays));
	
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
					AND stockmaster.categoryid != 'SHDISP'
					AND (locstock.loccode = locations.loccode)
					AND locstock.loccode = '" . $location . "'
					AND (locstock.quantity > 0)
					AND (locstock.reorderlevel > 0)
					AND NOT EXISTS (SELECT * 
									FROM 	salesorderdetails, salesorders
									WHERE 	stockmaster.stockid = salesorderdetails.stkcode
											AND (salesorders.fromstkloc = locstock.loccode)
											AND (salesorderdetails.orderno = salesorders.orderno)
											AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
					AND NOT EXISTS (SELECT * 
									FROM 	stockmoves
									WHERE 	stockmoves.loccode = locstock.loccode 
											AND stockmoves.stockid = stockmaster.stockid
											AND stockmoves.trandate >= '" . $FromDate . "')
					AND EXISTS (SELECT * 
								FROM 	stockmoves
								WHERE 	stockmoves.loccode = locstock.loccode 
										AND stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate < '" . $FromDate . "'
										AND stockmoves.qty >0) 
					ORDER BY stockmaster.stockid";
	
	$result = DB_query($SQL);		
	
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Items with NO sales on last ') . $maxdays . ' days in ' . $location . ' </strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('#') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Category') . '</th>
								<th>' . _('QOH') . '</th>
								<th>' . _('Old RL') . '</th>
								<th>' . _('New RL') . '</th>
								<th>' . _('Notes') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$newRL = 0;
			$notes = "";
			// Check if belongs to a special category
			// comented on change of category structure 2014-05-06
/*			if ($myrow['categoryid'] == "KLPRGE"){
				$newRL = $myrow['reorderlevel'];
				$notes = "KLPRGE - Tali. RL Not changed";
			}
*/			// check if RING and we have sold on the same location same model, other sizes, then should be RL = 1.
			if (isRing($myrow['stockid'])){
				// get the model code and see if the location has sold of different sizes, so we need to keep all sizes at the shop
				// even if no sales.
				$RingModel = CodeModelRing($myrow['stockid']);
				$SalesModel = SalesOfItemByLocation($RingModel, $location, $maxdays, $db);
				if ($SalesModel != 0){
					// sales for some size, so we want to keep it in stock (just 1, in case there were 2 or more)...
					$newRL = 1;
					$notes = $SalesModel . " sold other sizes.";
				}
			}
			if (isTopSalesItem($myrow['stockid'], $topitems, $topitemsdays, $db)){
				$newRL = $myrow['reorderlevel'];
				$notes = "Top ". $topitems . " sales.";
			}
/* KL RICARD COMMENTED ON 2014-06-10
			// if manually reseted, not change it
			$lastManualModification = isReorderLevelManuallyChanged($myrow['stockid'], $location, $maxmanualchanges, $db);
			if ($lastManualModification != '0000-00-00'){
				$newRL = $myrow['reorderlevel'];
				$notes = "Manually changed on ". ConvertSQLDate($lastManualModification);
			}
*/			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						$myrow['categoryid'], 
						locale_number_format($myrow['quantity'],0),
						locale_number_format($myrow['reorderlevel'],0),
						locale_number_format($newRL,0),
						$notes
						);
				$i++;
			}
			SetReorderLevel("AdjustNoSales", $myrow['stockid'],$location, $myrow['reorderlevel'], $newRL, $updateDB, $db);
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
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

function RebalancingBetweenShops($maxdays, $ShowMessages, $updateDB, $RootPath, $db){
	/* 
		items 
		that some stock is needed at some shops, 
		and there is at least one shop with more than 0 item 
		and stock at kantor is zero 
		and there is no transfer alive for this item 
		
	*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.categoryid,
					stockmaster.description,
					(SELECT locstock.loccode
						FROM locstock
						WHERE stockmaster.stockid  = locstock.stockid 
							AND locstock.loccode LIKE 'TOK%'
							AND locstock.quantity < locstock.reorderlevel
						ORDER BY reorderlevel DESC
						LIMIT 1) AS locationneeded
			FROM stockmaster
			WHERE stockmaster.categoryid NOT IN ('SHDISP', 'SHPACK')
				AND EXISTS (SELECT *
						FROM locstock
						WHERE stockmaster.stockid  = locstock.stockid 
							AND (locstock.loccode LIKE 'TOK%'
								)
						AND locstock.quantity < locstock.reorderlevel)
				AND EXISTS (SELECT *
						FROM locstock
						WHERE stockmaster.stockid  = locstock.stockid 
							AND (locstock.loccode LIKE 'TOK%'
							)
							AND locstock.quantity > 0)
				AND EXISTS (SELECT *
						FROM locstock
						WHERE stockmaster.stockid  = locstock.stockid 
							AND locstock.loccode = 'KANTO'
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
					
					// if category is discount or outlet, then use priority for these categories
					if(($myrow['categoryid'] == 'DISC20') OR($myrow['categoryid'] == 'DISC50') OR ($myrow['categoryid'] == 'DISC80')){
						$OrderBy = " ORDER BY locations.prioritydiscount ASC, "; 
					}else{
						$OrderBy = " ORDER BY locations.priority ASC, "; 
					}
					
					$SQLDistribution = "SELECT locstock.loccode, 
											locstock.reorderlevel AS oldrl
										FROM locstock, locations
										WHERE  locstock.loccode = locations.loccode
											AND locstock.stockid = '" . $myrow['stockid'] . "'
											AND (locstock.loccode LIKE 'TOK%'
												)
											AND locstock.reorderlevel > 0 ".
										$OrderBy . "
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
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}
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

	$SQL = $SQL . "	AND (locstock.loccode LIKE 'TOK%')";

	// if category is discount or outlet, then use priority for these categories
	if(($stockcat == 'DISC20') 
		OR ($stockcat == 'DISC50') 
		OR ($stockcat == 'DISC80')){
		$SQL = $SQL . " ORDER BY locations.prioritydiscount DESC, "; 
	}else{
		$SQL = $SQL . " ORDER BY locations.priority DESC, "; 
	}
	$SQL = $SQL . 
			"		(SELECT COUNT(qtyinvoiced)
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
			FROM locstock
			WHERE locstock.stockid = '" . $stockid . "'
			AND (locstock.loccode LIKE 'TOK%'
				)
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
			FROM locstock
			WHERE locstock.stockid = '" . $stockid . "'";
	if ($location == "ALLSHOPS"){
		$SQL = $SQL . " AND locstock.loccode LIKE 'TOK%' "; 
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

function SetRLZeroForNotAvailableItems($ShowMessages, $updateDB, $RootPath, $db){
	/* On 17/12/2013 we take out the SHOP consumables to avoid problems with the shop packagings */
	/* On 21/12/2013 we take out the SHOP packaging to avoid problems with the shop packagings */
	
	$SQL = "SELECT locstock.stockid,
					stockmaster.description,
					locstock.reorderlevel
			FROM locstock, stockmaster, stockcategory
			WHERE locstock.stockid = stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.categoryid != 'SHCONS'
				AND stockmaster.categoryid != 'SHPACK'
				AND stockcategory.stocktype = 'F'
				AND (locstock.loccode LIKE 'TOK%'
					OR locstock.loccode = 'KANTO')
				AND EXISTS (SELECT *
							FROM locstock
							WHERE locstock.stockid = stockmaster.stockid
								AND locstock.reorderlevel > 0 
								AND (locstock.loccode LIKE 'TOK%'
									OR locstock.loccode = 'KANTO'))
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
			SetReorderLevel("NotAvailable", $myrow['stockid'],"SHOPS", 999999, 0, $updateDB, $db);
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}
}

function SetRLForTopSalesItems( $starttopitems, $endtopitems, $daystopitems, $minstockavailable, $maxstockavailable, $NewRL, $ShowMessages, $updateDB, $RootPath, $db, $EmailText){

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
*/	
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "SetRLForTopSalesItems" . "\n\n";
	}

	$Today = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$daystopitems));
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.categoryid,
					stockmaster.description,
					SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE stockmaster.stockid  = locstock.stockid
							AND (locstock.loccode = 'KANTO' OR locstock.loccode LIKE 'TOK%')
							AND locstock.loccode != 'TOKWS') AS QtyAvailable
			FROM salesorderdetails, salesorders, stockmaster
			WHERE salesorderdetails.orderno = salesorders.orderno 
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
									FROM locstock
									WHERE locstock.stockid = '" . $myrow['stockid'] . "'
									AND locstock.loccode LIKE 'TOK%'
									AND locstock.loccode != 'TOKWS'
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
									echo '<p class="page_title_text" align="center"><strong>' . _('Set RL min to ') . $NewRL . ' for Top Sales '. $starttopitems . '-'. $endtopitems . ' with Stock Available > '. $minstockavailable.'</strong></p>';
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
								FROM locstock
								WHERE locstock.stockid = '" . $myrow['stockid'] . "'
								AND locstock.loccode LIKE 'TOK%'
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

function SetRLForLowSalesHighRL($maxdays, $oldRL, $maxRL, $minavailablestock, $ShowMessages, $updateDB, $RootPath, $db){
	/* No Sales during last maxdays, 
		with stock at the shop
		with RL >= oldRL at the shop
		with less than minavailablestock at shops or office
	*/

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
					AND stockmaster.categoryid NOT IN ('SHDISP',)
					AND stockmaster.categoryid NOT IN" . LIST_STOCK_CATEGORIES_DISCOUNT . " 
					AND (locstock.loccode = locations.loccode)
					AND (locstock.loccode LIKE 'TOK%')
					AND (locstock.quantity > 0)
					AND (locstock.reorderlevel >= ". $oldRL .")
					AND (SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE stockmaster.stockid = locstock.stockid
							AND (locstock.loccode LIKE 'TOK%' 
								OR locstock.loccode = 'KANTO' )) <= ".$minavailablestock."
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
/*			if (isTopSalesItem($myrow['stockid'], $topitems, $topitemsdays, $db)){
				$newRL = $myrow['reorderlevel'];
				$notes = "Top ". $topitems . " sales.";
			}
*/			// if manually reseted, not change it
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
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}
}

function MaxRLCorrectionSomeModels($stockid, $loccode, $NewRL){
	$CurrentNewRL = $NewRL;
	//
	// MAX correction for some locations, depending on some items
	//
	if ($loccode == "TOKPA"){
		if ($stockid != "GIFT-ALAR01"){
			$CurrentNewRL	= min($NewRL, 2);
		}
	}


	//
	// MAX correction for some models, depending on the shops
	//
	if (isPlasticBag($stockid)){
		if ($loccode == "TOKKS"){
			$CurrentNewRL	= min($NewRL, 2);
		}
		if ($loccode == "TOKBW"){
			$CurrentNewRL	= min($NewRL, 2);
		}
		if ($loccode == "TOKJC"){
			$CurrentNewRL	= min($NewRL, 2);
		}
		if ($loccode == "TOKUB"){
			$CurrentNewRL	= min($NewRL, 2);
		}
	}
	// END of MAX Corrections of New RL
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
							AND loccode LIKE 'TOK%'";
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
					WHERE reorderlevel > 0 AND loccode = 'TOKWS'";
		$Result = DB_query($RLSQL,$ErrMsg,$DbgMsg,true);		
		if ($ShowMessages){
			prnMsg(_('Reset all RL=0 for shop online location TOKWS.'),'info');
		}
		if ($EmailText!=''){
			$EmailText = $EmailText . "Reset all RL=0 for shop online location TOKWS" . "\n";
		}
	}
// adjust RL for toko online as needed
	$SQL = "SELECT salesorderdetails.stkcode,
				SUM(salesorderdetails.quantity) AS totalqty,
				locstock.reorderlevel
			FROM salesorders, salesorderdetails, locstock
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = locstock.stockid
				AND locstock.loccode = 'TOKWS'
				AND salesorders.fromstkloc = 'TOKWS'
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
			prnMsg(_('No webSHOP orders to be processed at this time.'),'info');
		}
		if ($EmailText!=''){
			$EmailText = $EmailText . "No webSHOP orders to be processed at this time" . "\n";
		}
	}
	return $EmailText;
}

function AdjustPackaging($DaysSales, $ShowMessages, $updateDB, $RootPath, $db, $EmailText){
	if ($EmailText!=''){
		$EmailText = $EmailText . "\n" . "Adjust Packaging" . "\n\n" .
					"DaysSales = " . $DaysSales . " " .
					"RootPath = " . $RootPath . "\n" .
					"List Shops Using Packaging Control = " . CleanListToPrint(LIST_SHOPS_USING_PACKAGING_CONTROL) . "\n" .
					"List Items Using Packaging Control = " . CleanListToPrint(LIST_ITEMS_USING_PACKAGING_CONTROL) . "\n\n" ;
	}

	$Shops = ListToArray(LIST_SHOPS_USING_PACKAGING_CONTROL,",");
	$CountShops = count($Shops);
	$iShop = 0;

	$Items = ListToArray(LIST_ITEMS_USING_PACKAGING_CONTROL,",");
	$CountItem = count($Items);

	while ($iShop < $CountShops -1 ){ // take out the lst PACKA location
		$iItem = 0;
		while ($iItem < $CountItem){
			$EmailText = AdjustPackagingItemByShop($Items[$iItem], $Shops[$iShop], $DaysSales, $ShowMessages, $updateDB, $RootPath, $db, $EmailText);
			$iItem++;
		}
		$iShop++;
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
		$NewRL = round($myrow['sales'] / $DaysSales * $myrow['rldaysforpackaging'],0);
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

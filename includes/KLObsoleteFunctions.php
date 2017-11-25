<?php

/* This file contains obsolete functions that can be useful someday for some reason */

function AdjustNoSales($location, $maxdays, $maxmanualchanges, $topitems, $TopItemsDays, $ShowMessages, $updateDB, $RootPath, $db){
	/* No Sales during last maxdays, 
		with stock at the shop
		with RL > at the shop
	*/

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$TopItemsDays));
	
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
			if (isTopSalesItem($myrow['stockid'], $topitems, $TopItemsDays, $db)){
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

function InsuficientStockForTopSalesItems($StockCat, $StockCatDescription, $DaysTopSales, $PercentageOfTopItems, $DaysMinimumStock, $RootPath, $db){

/* Examples of use in Control Boards
		InsuficientStockForTopSalesItems("STABKL", "10-Silver",90, 100, 150, $RootPath, $db);
		InsuficientStockForTopSalesItems("STAINL", "20-Stainless Steel", 90, 100, 150, $RootPath, $db);
		InsuficientStockForTopSalesItems("STABBL", "30-Fashion Jewellery", 90, 100, 150, $RootPath, $db);
		InsuficientStockForTopSalesItems("ACCESO", "40-Accessories", 90, 100, 150, $RootPath, $db);
		InsuficientStockForTopSalesItems("CONSIG", "50-Consignment", 60, 100, 30, $RootPath, $db);
*/		

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$DaysTopSales));
	
	$SQL = "(SELECT COUNT(DISTINCT(l2.stockid))
						FROM locstock AS l2,
							stockmaster as m2
						WHERE m2.stockid = l2.stockid
							AND m2.categoryid = '" . $StockCat ."'
						AND l2.quantity != 0) ";
	$resultTI = DB_query($SQL);		
	$myrowTI = DB_fetch_array($resultTI);
	$NumberOfTopItems = ceil($myrowTI[0]/100*$PercentageOfTopItems);
	
	$SQL = "SELECT 	salesorderdetails.stkcode,
					SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced,
					SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice/currencies.rate ) AS valuesales,
					stockmaster.description,
					stockmaster.units,
					currencies.rate,
					debtorsmaster.currcode,
					stockmaster.decimalplaces,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = salesorderdetails.stkcode
						AND locstock.loccode NOT IN ('SAMPR')) AS qoh,
					(SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd) 
						FROM purchorderdetails, purchorders
						WHERE purchorderdetails.itemcode = salesorderdetails.stkcode
							AND purchorders.orderno=purchorderdetails.orderno
							AND purchorderdetails.completed = 0
							AND purchorders.status NOT IN ('Cancelled', 'Pending', 'Rejected')) as qoo,
					(SELECT SUM(woitems.qtyreqd -woitems.qtyrecd) 
						FROM woitems, workorders
						WHERE woitems.stockid = salesorderdetails.stkcode
							AND woitems.wo = workorders.wo
							AND workorders.closed = 0) as qow
				FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster, currencies
			WHERE 	salesorderdetails.orderno = salesorders.orderno
					AND stockmaster.discontinued = 0
					AND salesorderdetails.stkcode = stockmaster.stockid
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.currcode = currencies.currabrev 
					AND salesorderdetails.actualdispatchdate >= '" . $FromDate . "'
					AND stockmaster.categoryid = '" . $StockCat . "'
			GROUP BY salesorderdetails.stkcode
			ORDER BY totalinvoiced DESC
			LIMIT " . $NumberOfTopItems;
	
	$result = DB_query($SQL);		
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$Forecast = ceil($myrow['totalinvoiced'] / $DaysTopSales * $DaysMinimumStock);
			$QtyNeeded = $Forecast - $myrow['qoh'] - $myrow['qoo'] - $myrow['qow'];
			if ($QtyNeeded > 0){
				if ($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . $NumberOfTopItems . ' Top Items from ' . strtoupper($StockCatDescription) . ' with insufficient stock for the next ' . $DaysMinimumStock . ' days (Excluded Samples).</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Sales ') . $DaysTopSales . '</th>
										<th class="ascending">' . _('Forecast ') . $DaysMinimumStock . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('QOO') . '</th>
										<th class="ascending">' . _('QOW') . '</th>
										<th class="ascending">' . _('Needed') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}

				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stkcode'] . '">' . $myrow['stkcode'] . '</a>';
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
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($myrow['totalinvoiced'],0),
						locale_number_format($Forecast,0),
						locale_number_format($myrow['qoh'],0),
						locale_number_format($myrow['qoo'],0),
						locale_number_format($myrow['qow'],0),
						locale_number_format($QtyNeeded,0)
						);
			}
			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function isTopSalesItem($stockid, $topitems, $TopItemsDays, $db){

	$TopSalesField = GetTopSalesField($TopItemsDays);

	$SQL="SELECT ". $TopSalesField." AS topsalesposition
		  FROM klsalesperformance
		  WHERE stockid = '" . $stockid . "'";
	$result = DB_query($SQL);
	$istopsales = false;
	if (DB_num_rows($result) != 0){
		if ($myrow = DB_fetch_array($result)) {
			if ($myrow['topsalesposition'] <= $topitems){
				$istopsales = true;
			}
		}
	}
	return $istopsales;
}

function ItemsNotTopSalesInShop($starttopitems, $endtopitems, $maxdays, $codeshop, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	if (ItemInList($Location, LIST_SHOPS_KAPAL_LAUT)){
		$FilterCategory = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . " ";
	}else if (ItemInList($Location, LIST_SHOPS_BLINK)){
		$FilterCategory = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . " ";
	}else if (ItemInList($Location, LIST_SHOPS_OUTLET)){
		$FilterCategory = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . " ";
	}
	$SQL = "SELECT salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.categoryid,
				SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced,
				stockmaster.units,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = salesorderdetails.stkcode
						AND locstock.loccode = '". $codeshop ."') AS qoh,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = salesorderdetails.stkcode) AS qohtotal,
				(SELECT sum(reorderlevel)
					FROM locstock
					WHERE locstock.stockid = salesorderdetails.stkcode
						AND locstock.loccode = '". $codeshop ."') AS rl
			FROM salesorderdetails, salesorders, stockmaster
			WHERE salesorderdetails.orderno = salesorders.orderno " . 
			$FilterCategory . 
			" AND stockmaster.discontinued = 0
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
			GROUP BY salesorderdetails.stkcode
			ORDER BY totalinvoiced DESC
			LIMIT " . ($endtopitems - 1) . ", 99999999";			
	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = $endtopitems;
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['rl'] > 0){
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . 'Items NOT ' . $endtopitems . ' top sales available in ' . $codeshop . ' shop. ' . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Category') . '</th>
										<th class="ascending">' . _('QOH Total') . '</th>
										<th class="ascending">' . _('RL') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stkcode'] . '">' . $myrow['stkcode'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						$myrow['categoryid'], 
						$myrow['qohtotal'],
						$myrow['rl'],
						$myrow['qoh']
						);
			}
			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function ItemsWithStockLocationButNoStockAvailable($Location, $NameLocation, $MinAvailable, $MaxTopSalesItems, $RootPath, $db){
	/*  EXPLAIN SQL 2014-05-30
		Examples of usage in control boards
		ItemsWithStockLocationButNoStockAvailable("WABOM", "WaterBom", 15, 600, $RootPath, $db);
		ItemsWithStockLocationButNoStockAvailable("WHAYA", "Ayana", 15, 600, $RootPath, $db);
		ItemsWithStockLocationButNoStockAvailable("WHINT", "InterContinental", 15, 600, $RootPath, $db);
		InsuficientStockForItems("STABKL", "TM-", "Tali Mie", 20, 40, $RootPath, $db);
	*/
	$SQL = "SELECT locstock.stockid,
				locstock.quantity,
				stockmaster.categoryid,
				(SELECT SUM(l2.quantity)
					FROM locstock l2
					WHERE locstock.stockid = l2.stockid
					AND (l2.loccode IN " . LIST_ALL_SHOPS . "
						OR l2.loccode = " . CODE_KANTOR . ")
				) AS available
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ."
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET ."
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET ."
				AND locstock.loccode = '" . $Location . "'
				AND locstock.quantity > 0
				AND locstock.reorderlevel > 0
				AND (SELECT SUM(l2.quantity)
						FROM locstock l2
						WHERE locstock.stockid = l2.stockid
						AND (l2.loccode IN " . LIST_ALL_SHOPS . "
							OR l2.loccode = " . CODE_KANTOR . ")
					) <= " . $MinAvailable;

	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = positionTopSalesItem($myrow['stockid'], 60, $db);
			if($PositionTopSales <= $MaxTopSalesItems){
				if ($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . $MaxTopSalesItems ._(' Top Sales Items (Exclude No More Purchasing, Discount) with stock at ') . $NameLocation . ' but KL Stock Available (Toko + Kantor) <= ' . $MinAvailable . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('TopSale#') . '</th>
										<th class="ascending">' . _('Qty ') . $Location . '</th>
										<th class="ascending">' . _('QOH Available') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						locale_number_format($PositionTopSales,0),
						locale_number_format($myrow['quantity'],0),
						locale_number_format($myrow['available'],0)
						);
				$i++;
			}
		}
		if (!$showHeader){
			echo '</table>
					</div>';
		}
	}
}

function OvestockAtShops($kind, $RootPath, $db){

	if($kind == "OVERSTOCK"){			
		$SQL = "SELECT locstock.loccode, 
						locstock.stockid, 
						stockmaster.description, 
						quantity - reorderlevel AS qty
				FROM locstock, stockmaster
				WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_ALL_SHOPS . "
					AND reorderlevel < quantity
					AND NOT EXISTS (SELECT *
									FROM loctransfers 
									WHERE  recqty < shipqty
										AND loctransfers.stockid =  stockmaster.stockid)
				ORDER BY locstock.loccode, stockmaster.categoryid, locstock.stockid";
	}else{
		$SQL = "SELECT locstock.loccode, 
					locstock.stockid, 
					stockmaster.description, 
					stockmaster.categoryid, 
					reorderlevel - quantity AS qty
				FROM locstock, stockmaster
				WHERE locstock.stockid = stockmaster.stockid
					AND locstock.reorderlevel > 0
					AND locstock.quantity = 0
					AND loccode IN " . LIST_ALL_SHOPS . "
					AND NOT EXISTS (SELECT *
										FROM loctransfers 
										WHERE  recqty < shipqty
											AND loctransfers.stockid =  locstock.stockid)
				ORDER BY locstock.loccode, stockmaster.categoryid, locstock.stockid";
	}
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if($kind == "OVERSTOCK"){			
			echo '<p class="page_title_text" align="center"><strong>' . _('Overstock of items at shops') . '</strong></p>';
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Shop') . '</th>
								<th class="ascending">' . _('Code') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('Overstock') . '</th>
							</tr>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Items needed at shops. (No overstock - No transfer)') . '</strong></p>';
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Shop') . '</th>
								<th class="ascending">' . _('Code') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('Need') . '</th>
							</tr>';
		}
		echo '<div>';
		echo '<table class="selection">';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$myrow['loccode'], 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qty'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}
			
function TopSalesNotInEnoughShops($starttopitems, $endtopitems, $maxdays, $minshops, $categories, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.categoryid,
				SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced,
				stockmaster.units,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = salesorderdetails.stkcode
						AND (locstock.loccode IN " . LIST_ALL_SHOPS . " 
							OR locstock.loccode = " . CODE_KANTOR . ")) AS qoh,
				(SELECT count(loccode)
					FROM locstock
					WHERE locstock.stockid = salesorderdetails.stkcode
						AND locstock.reorderlevel > 0
						AND locstock.loccode IN " . LIST_ALL_SHOPS . ") AS availableshops
			FROM salesorderdetails, salesorders, stockmaster
			WHERE salesorderdetails.orderno = salesorders.orderno ";
	if ($categories == "DISC20"){
		$SQL = $SQL . " AND stockmaster.categoryid = 'DISC20'";
	}		
	if ($categories == "DISC50"){
		$SQL = $SQL . " AND stockmaster.categoryid = 'DISC50'";
	}		
	if ($categories == "DISC80"){
		$SQL = $SQL . " AND stockmaster.categoryid = 'DISC80'";
	}		
	if ($categories == "TEST"){
		$SQL = $SQL . " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "";
	}		
	if ($categories == "STABLE"){
		$SQL = $SQL . " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "";
	}		
	$SQL = $SQL . " AND stockmaster.discontinued = 0
					AND stockmaster.klchangingprice = 0
					AND stockmaster.klmovingdiscount20 = 0
					AND stockmaster.klmovingdiscount50 = 0
					AND stockmaster.klmovingdiscount80 = 0
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
			GROUP BY salesorderdetails.stkcode
			ORDER BY totalinvoiced DESC
			LIMIT " . ($starttopitems - 1) . "," . ($endtopitems - $starttopitems + 1);			
	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = $starttopitems;
		while ($myrow = DB_fetch_array($result)) {
			if (($myrow['availableshops'] < $minshops) && ($myrow['qoh'] > $myrow['availableshops'])){
				if($showHeader){
					if ($categories == "DISC20"){
						echo '<p class="page_title_text" align="center"><strong>' . $endtopitems . ' Top sales items 20% Discount available in less than ' . $minshops . ' shops. ' . '</strong></p>';
					}		
					if ($categories == "DISC50"){
						echo '<p class="page_title_text" align="center"><strong>' . $endtopitems . ' Top sales items 50% Discount available in less than ' . $minshops . ' shops. ' . '</strong></p>';
					}		
					if ($categories == "DISC80"){
						echo '<p class="page_title_text" align="center"><strong>' . $endtopitems . ' Top sales items 80% Discount available in less than ' . $minshops . ' shops. ' . '</strong></p>';
					}		
					if ($categories == "STABLE"){
						echo '<p class="page_title_text" align="center"><strong>' . $endtopitems . ' Top sales items NOT DISCOUNTED OR CHANGING PRICE available in less than ' . $minshops . ' shops. ' . '</strong></p>';
					}		
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Category') . '</th>
										<th class="ascending">' . _('Sold ') . $maxdays . ' days' . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('# Toko') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stkcode'] . '">' . $myrow['stkcode'] . '</a>';
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
						$myrow['description'], 
						$myrow['categoryid'], 
						$myrow['totalinvoiced'], 
						$myrow['qoh'], 
						$myrow['availableshops'] 
						);
			}
			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}


?>

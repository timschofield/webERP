<?php


define('JEWELLERY_ON_SPECIAL',51);
define('RINGS_ON_SPECIAL',52);
define('BRACELETS_ON_SPECIAL',53);
define('EARRINGS_ON_SPECIAL',54);
define('PENDANTS_ON_SPECIAL',55);
define('NECKLACES_ON_SPECIAL',56);
define('ANKLETS_ON_SPECIAL',60);
define('TOERINGS_ON_SPECIAL',59);
define('EARCUFFS_ON_SPECIAL',74);
define('BROOCHES_ON_SPECIAL',81);
define('KEYHOLDERS_ON_SPECIAL',85);
define('JEWELLERY_ROLLS_ON_SPECIAL',91);
define('FACEMASKS_ON_SPECIAL',92);



/* This file contains obsolete functions that can be useful someday for some reason */

function AdjustNoSales($location, $maxdays, $maxmanualchanges, $topitems, $TopItemsDays, $ShowMessages, $updateDB, $RootPath){
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
				$SalesModel = SalesOfItemByLocation($RingModel, $location, $maxdays);
				if ($SalesModel != 0){
					// sales for some size, so we want to keep it in stock (just 1, in case there were 2 or more)...
					$newRL = 1;
					$notes = $SalesModel . " sold other sizes.";
				}
			}
			if (isTopSalesItem($myrow['stockid'], $topitems, $TopItemsDays)){
				$newRL = $myrow['reorderlevel'];
				$notes = "Top ". $topitems . " sales.";
			}
/* KL RICARD COMMENTED ON 2014-06-10
			// if manually reseted, not change it
			$lastManualModification = isReorderLevelManuallyChanged($myrow['stockid'], $location, $maxmanualchanges);
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
			SetReorderLevel("AdjustNoSales", $myrow['stockid'],$location, $myrow['reorderlevel'], $newRL, $updateDB);
		}
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}
}

function DailySalesRecordsByShops($Days, $FromDate){

	$SQL = "SELECT salesorders.orddate,
				salesorders.debtorno,
				SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) AS sales
			FROM salesorders
			INNER JOIN salesorderdetails ON
				salesorders.orderno=salesorderdetails.orderno
			INNER JOIN debtorsmaster ON 
				salesorders.debtorno = debtorsmaster.debtorno
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
				AND salesorders.orddate >= '" . $FromDate . "'
			GROUP BY salesorders.orddate,salesorders.debtorno
			ORDER BY SUM(salesorderdetails.qtyinvoiced * (salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent))) DESC
			LIMIT ". $Days . "";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Top ') . $Days . _(' retail sales days by shop since '). ConvertSQLDate($FromDate) .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' .  _('#') . '</th>
							<th class="ascending">' .  _('Date') . '</th>
							<th class="ascending">' .  _('Shop') . '</th>
							<th class="ascending">' . _('Sales') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while (($myrow = DB_fetch_array($result)) AND ($i <= $Days)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($i,0),
					ConvertSQLDate($myrow['orddate']),
					$myrow['debtorno'],
					locale_number_format($myrow['sales'],0)
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

function GoodSellingItemsInCategory($CategoryId, $days, $minsales, $RootPath){
/* EXPLAIN SQL 2014-05-21 */
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$days));

	//				AND lastcategoryupdate <= '" . $StartDate. "'
	
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
				) AS qoh,
				(SELECT SUM(qtyinvoiced)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.stkcode = stockmaster.stockid
						AND salesorderdetails.orderno = salesorders.orderno
						AND salesorders.orddate >= '". $StartDate ."'
				) as sold				
			FROM stockmaster			
			WHERE categoryid = '" . $CategoryId . "'
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND (SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
				) > 0
				AND ((SELECT SUM(qtyinvoiced)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.stkcode = stockmaster.stockid
						AND salesorderdetails.orderno = salesorders.orderno
						AND salesorders.orddate >= '". $StartDate ."') >= ". $minsales .")
			ORDER BY stockmaster.stockid ASC";
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items in category ') . $CategoryId . " with more than " . $minsales . " pcs sold in the last " . $days . " days.(GOOD ITEMS)" . ' </strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('Sold '). $days . ' Days' . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qoh'],0),
					locale_number_format($myrow['sold'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ImagesShouldNotBeInOpencartCatalog($RootPath){

	$ShowHeader = TRUE;
	$k = 0; //row colour counter
	$i= 0;
	// get all images in part_pics folder
	$suffix = ".jpg";
	$imagefiles = getDirectoryTree(ABSOLUTE_PATH_OPENCART_IMAGES);
	foreach ($imagefiles as $file) {
		$StockId = substr($file, 0, strpos($file, $suffix));
		if (strpos($StockId, '.1') > 0){
			$StockId = substr($file, 0, strpos($StockId, '.1'));
		}
		if (strpos($StockId, '.2') > 0){
			$StockId = substr($file, 0, strpos($StockId, '.2'));
		}
		if (strpos($StockId, '.3') > 0){
			$StockId = substr($file, 0, strpos($StockId, '.3'));
		}
		if (strpos($StockId, '.4') > 0){
			$StockId = substr($file, 0, strpos($StockId, '.4'));
		}
		if (strpos($StockId, '.5') > 0){
			$StockId = substr($file, 0, strpos($StockId, '.5'));
		}
		$ProductId = GetOpenCartProductId($StockId);
		if ($ProductId == 0){
			if ($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . _('Opencart Images without product in OpenCart') .'</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th class="ascending">' . _('File') . '</th>
								</tr>';
				echo $TableHeader;
				$ShowHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
					</tr>', 
					ABSOLUTE_PATH_OPENCART_IMAGES.$file
					);
//			unlink(ABSOLUTE_PATH_OPENCART_IMAGES.$file);
		}
	}
	if (!$ShowHeader){
		echo '</table>
				</div>';
	}
}

function InsuficientStockForItems($Category, $ItemCode, $ItemDescription, $MinimumStock, $OptimalStock, $RootPath){

	if($Category == "ALL"){
		$SQLCategory = " ";
	}else{
		$SQLCategory = " AND stockmaster.categoryid = '" . $Category . "' ";
	}
/* EXPLAIN SQL 2014-05-21 */	
	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND (loccode IN " . LIST_ALL_SHOPS . "
							OR loccode = " . CODE_KANTOR . ")) AS qoh
			FROM stockmaster
			WHERE stockmaster.stockid LIKE '" . $ItemCode . "%'
				AND stockmaster.discontinued = 0".
				$SQLCategory . "
				AND (SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND (loccode IN " . LIST_ALL_SHOPS . "
							OR loccode = " . CODE_KANTOR . ")) < " . $MinimumStock . "
			ORDER BY stockmaster.stockid";
	
	$result = DB_query($SQL);		
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$QtyNeeded = $OptimalStock - $myrow['qoh'];
			if ($QtyNeeded > 0){
				if ($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . $ItemDescription . ' Items with QOH (kantor+toko) < ' . $MinimumStock . ' pcs.</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
										<th class="ascending">' . _('Needed') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}

				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($myrow['qoh'],0),
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

function InsuficientStockForTopSalesItems($StockCat, $StockCatDescription, $DaysTopSales, $PercentageOfTopItems, $DaysMinimumStock, $RootPath){

/* Examples of use in Control Boards
		InsuficientStockForTopSalesItems("STABKA", "10-Silver",90, 100, 150, $RootPath);
		InsuficientStockForTopSalesItems("STAINL", "20-Stainless Steel", 90, 100, 150, $RootPath);
		InsuficientStockForTopSalesItems("STABBA", "30-Fashion Jewellery", 90, 100, 150, $RootPath);
		InsuficientStockForTopSalesItems("ACCESO", "40-Accessories", 90, 100, 150, $RootPath);
		InsuficientStockForTopSalesItems("CONSIG", "50-Consignment", 60, 100, 30, $RootPath);
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

function isReorderLevelManuallyChanged($stockid, $loccode, $maxmanualchanges){
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

function isTopSalesItem($stockid, $topitems, $TopItemsDays){

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

function ItemsInCategoryWithStockKantorButReorderLevelTokoZero($CategoryId, $RootPath){
	if (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_OUTLET)){
		if (LIST_SHOPS_OUTLET == "('')"){
			// no shops with outlet, so this report has NO sense.
			return;
		}else{
			$WhereLocation = " AND locations.typeloc = 'SHOPOU' ";
		}
	}else{
		$WhereLocation = " AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . " ";
	}

	$SQL = "SELECT stockid,
			stockmaster.categoryid,
			stockmaster.description,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid
				AND (locstock.loccode = " . CODE_KANTOR . " ))AS QtyKantor
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND (SELECT SUM(locstock.reorderlevel)
					FROM locstock, locations
					WHERE locstock.stockid = stockmaster.stockid 
						AND locstock.loccode = locations.loccode ".
						$WhereLocation . " ) = 0
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = " . CODE_KANTOR . ") > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  pendingqty > 0
							AND loctransfers.stockid =  stockmaster.stockid)
				AND discontinued = 0
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid = '" . $CategoryId . "'
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_OUTLET)){
			echo '<p class="page_title_text" align="center"><strong>' . $CategoryId ._(' Items with stock available at Kantor but RL zero for ') . LIST_SHOPS_OUTLET . '</strong></p>';
		}elseif (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_OUTLET)){
			echo '<p class="page_title_text" align="center"><strong>' . $CategoryId ._(' Items with stock available at Kantor but RL zero for ') . LIST_SHOPS_OUTLET . '</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . $CategoryId ._(' Items with stock available at Kantor but RL zero for all toko KL') . '</strong></p>';
		}
		
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
						</tr>';
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
					$CodeLink, 
					$myrow['categoryid'], 
					$myrow['description'], 
					locale_number_format($myrow['QtyKantor'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsNeedingAutomaticTranslation($RootPath){
	$SQL = "SELECT COUNT(stockdescriptiontranslations.stockid)
			FROM stockmaster, stockdescriptiontranslations
			WHERE stockmaster.stockid = stockdescriptiontranslations.stockid
				AND stockmaster.discontinued = 0
				AND LENGTH(stockmaster.description) > 2
				AND (descriptiontranslation = ''
					OR longdescriptiontranslation = '')";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	if ($myrow[0] > 0){
		$text = locale_number_format($myrow[0],0) . " items need Automatic Description Translation";
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function ItemsNeedingTranslationRevision($RootPath){
	$SQL = "SELECT COUNT(stockdescriptiontranslations.stockid)
			FROM stockmaster, stockdescriptiontranslations
			WHERE stockmaster.stockid = stockdescriptiontranslations.stockid
				AND stockmaster.discontinued = 0
				AND needsrevision = '1'";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	if ($myrow[0] > 0){
		$text = locale_number_format($myrow[0],0) . " items need Translation Revision";
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function ItemsNoSalesInLocation($location, $maxdays, $QOHAvailable, $RootPath){
/* EXPLAIN SQL 2014-05-20	*/
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));
	
	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.units, 
					locstock.quantity,
					(SELECT SUM(loc2.quantity)
							FROM locstock AS loc2, locations as locations2
							WHERE loc2.stockid = stockmaster.stockid
							AND locations2.loccode = loc2.loccode
							AND (locations2.typeloc = 'SHOPKL'
								OR locations2.typeloc = 'SHOPBL'
								OR locations2.typeloc = 'SHOPOU'
								OR loc2.loccode =  " . CODE_KANTOR . ") ) AS qtyavailable,
					locstock.reorderlevel,
					locstock.loccode,
					locations.locationname 
			FROM 	stockmaster,locstock,locations
			WHERE 	stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
					AND (locstock.loccode = locations.loccode)
					AND locstock.loccode = '" . $location . "'
					AND (locstock.quantity > 0)
					AND (locstock.reorderlevel > 0)
					AND  (SELECT SUM(loc2.quantity)
							FROM locstock AS loc2, locations as locations2
							WHERE loc2.stockid = stockmaster.stockid
							AND locations2.loccode = loc2.loccode
							AND (locations2.typeloc = 'SHOPKL'
								OR locations2.typeloc = 'SHOPBL'
								OR locations2.typeloc = 'SHOPOU'
								OR loc2.loccode = " . CODE_KANTOR . ") ) <= ". $QOHAvailable ."
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
		echo '<p class="page_title_text" align="center"><strong>' . _('Items with NO sales on last ') . $maxdays . ' days in ' . $location . ' with stock <= ' . $QOHAvailable . ' at shops or kantor</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('QOH ') . $location . '</th>
							<th class="ascending">' . _('QOH Shops+Kantor') . '</th>
						</tr>';
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
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['categoryid'], 
					locale_number_format($myrow['quantity'],0),
					locale_number_format($myrow['qtyavailable'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsNotTopSalesInShop($starttopitems, $endtopitems, $maxdays, $codeshop, $RootPath){
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

function ItemsWithStockKantorButRLZeroAt($Location, $RootPath){
/*
items with stock kantor > 0 
RL is zero at $Location
No pending transfer regarding this item
*/
/* 2013-04-16 excluding items in change price process */
/* 2013-05-27 excluding items in consignment clothing */

	if (ItemInList($Location, LIST_SHOPS_KAPAL_LAUT)){
		$FilterCategory = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . " ";
		$MessageCategory = " KL Categories ";
	}else if (ItemInList($Location, LIST_SHOPS_BLINK)){
		$FilterCategory = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . " ";
		$MessageCategory = " BLINK Categories ";
	}else if (ItemInList($Location, LIST_SHOPS_OUTLET)){
		$FilterCategory = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . " ";
		$MessageCategory = " DISCOUNT Categories ";
	}
	
	$SQL = "SELECT stockid,
			stockmaster.categoryid,
			stockmaster.description,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid
				AND (locstock.loccode = " . CODE_KANTOR . " ))AS QtyKantor
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND discontinued = 0
				AND stockcategory.stocktype = 'F'
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.klchangingprice = 0 " .
				$FilterCategory . "
				AND (SELECT SUM(locstock.reorderlevel)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = '". $Location ."') = 0
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = " . CODE_KANTOR . ") > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  pendingqty > 0
							AND loctransfers.stockid =  stockmaster.stockid)
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $MessageCategory . _(' Items with stock available (but NO changing price or category) at Kantor but RL = 0 at ') . $Location . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
						</tr>';
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
					$CodeLink, 
					$myrow['categoryid'], 
					$myrow['description'], 
					locale_number_format($myrow['QtyKantor'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function MarkSisterShopInArray(&$TableResult, $numshops, $SisterShop){
	$sistershop = 1;
	while ($sistershop <= $numshops){
		if ($TableResult[$sistershop]['loccode'] ==  $SisterShop){
			$TableResult[$sistershop]['show'] = TRUE;
		}
		$sistershop++;
	}
}

function NewCustomers($NumDays, $RootPath){
/* EXPLAIN SQL 2014-05-20	*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

	$SQL = "SELECT 	debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					debtorsmaster.clientsince,
					debtortype.typename
			FROM debtorsmaster, debtortype
			WHERE debtorsmaster.typeid = debtortype.typeid
				AND debtorsmaster.clientsince > '".$StartDate."'
			ORDER BY debtorsmaster.clientsince";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('New customers registered during the last ') . $NumDays . ' days.' . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Country') . '</th>
							<th class="ascending">' . _('Currency ') . '</th>
							<th class="ascending">' . _('Registered on') . '</th>
							<th class="ascending">' . _('Type') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . $myrow['debtorno'] . '">' . $myrow['debtorno'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['name'], 
					$myrow['address6'], 
					$myrow['currcode'], 
					ConvertSQLDateTime($myrow['clientsince']), 
					$myrow['typename']				
					);
			$i++;
		}
		echo '</table>
				</div>';
	}

}

function OvestockAtShops($kind, $RootPath){

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
									WHERE  pendingqty > 0
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
										WHERE  pendingqty > 0
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

function PerformanceItemsInCategory($ReportType, $CategoryId, $maxdays, $percentsales, $TextTitle, $RootPath){

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	
	if ($ReportType == "GOOD"){
		$Sign = ">=";
	}else{
		$Sign = "<=";
	}

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.lastcategoryupdate,
					(SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
				) AS qoh,
					(SELECT SUM(qtyinvoiced)
					FROM salesorderdetails
					WHERE salesorderdetails.stkcode = stockmaster.stockid
					AND salesorderdetails.itemdue >= '" . $StartDate . "'
				) as sold				
			FROM stockmaster			
			WHERE categoryid = '" . $CategoryId . "'
				AND lastcategoryupdate ". $Sign . " '" . $StartDate. "'
				AND klchangingprice = 0
				AND klmovingdiscount20 = 0
				AND klmovingdiscount50 = 0
				AND klmovingdiscount80 = 0
				AND (SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
				) > 0
				AND (((SELECT SUM(qtyinvoiced)
					FROM salesorderdetails
					WHERE salesorderdetails.stkcode = stockmaster.stockid) /
					((SELECT SUM(qtyinvoiced)
					FROM salesorderdetails
					WHERE salesorderdetails.stkcode = stockmaster.stockid) +
					(SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid)))	". $Sign . " ('" . $percentsales ."' / 100)";

	if ($ReportType == "GOOD"){
		$SQL = $SQL . ")";
	}else{
		$SQL = $SQL . " OR ((SELECT SUM(qtyinvoiced)
								FROM salesorderdetails
								WHERE salesorderdetails.stkcode = stockmaster.stockid) IS NULL))";
	}
	$SQL = $SQL . " ORDER BY stockmaster.lastcategoryupdate ASC, stockmaster.stockid ASC";
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ReportType == "GOOD"){
			echo '<p class="page_title_text" align="center"><strong>' . _('Items in category ') . $CategoryId . " for less than " . $maxdays . " days with more than " . $percentsales . "% of sold stock (" . $TextTitle . " Items)." . ' </strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Items in category ') . $CategoryId . " for more than " . $maxdays . " days with less than " . $percentsales . "% of sold stock (" . $TextTitle . " Items).". ' </strong></p>';
		}echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('DOB Category') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Total Qty') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('Sold Qty') . '</th>
							<th class="ascending">' . _('% Sold') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$DaysInCategory = DateDiff(Date($_SESSION['DefaultDateFormat']), ConvertSQLDate($StartDate), 'd');
			if (($myrow['sold'] + $myrow['qoh']) != 0){
				$ActualSales = ($myrow['sold'] / ($myrow['sold'] + $myrow['qoh'])) * 100;
			}else{
				$ActualSales = 0 ;
			}
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					ConvertSQLDate($myrow['lastcategoryupdate']), 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qoh']  + $myrow['sold'],0),
					locale_number_format($myrow['qoh'],0),
					locale_number_format($myrow['sold'],0),
					locale_number_format($ActualSales,0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PricesNotUpdatedinXDays($numDays, $percentageIncrease, $RootPath){
	
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));
	$today = date('Y-m-d');

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(stockmaster.actualcost) AS stdcost,
				prices.price,
				prices.startdate
			FROM prices, stockmaster
			WHERE stockmaster.stockid = prices.stockid	
				AND ( stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING . ")
				AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
				AND prices.currabrev = '". CURRENCY_CODE ."'
				AND prices.startdate <= '". $InitialDate. "' 
				AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
				AND stockmaster.discontinued = 0					
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
			ORDER BY stockmaster.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . 'Prices not updated during the last ' . $numDays . ' days. Recommended increase '. $percentageIncrease . '%</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Std Cost') . '</th>
							<th class="ascending">' . _('Date Price') . '</th>
							<th class="ascending">' . _('Current Price') . '</th>
							<th class="ascending">' . _('Recommended Price') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$NewPrice = round_price($myrow['price'] * (1 + $percentageIncrease/100), "UP");
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
		//	$PriceLink = '<a href="' . $RootPath . '/Prices.php?Item=' . $myrow['stockid'] . '">' . locale_number_format($myrow['price'],0) . '</a>';
			$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $NewPrice .  '">' . locale_number_format($NewPrice,0) . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'],
					locale_number_format($myrow['stdcost'],0),
					ConvertSQLDate($myrow['startdate']), 
					locale_number_format($myrow['price'],0),
					$NewPriceLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function SalesOfItemByLocation($stockid, $location, $maxdays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT COUNT(qtyinvoiced) AS sales
			FROM salesorderdetails, salesorders
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.completed = 1
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.fromstkloc = '". $location . "'
				AND salesorderdetails.stkcode LIKE '". $stockid . "%'";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$sales = $myrow['sales'];
	}else{
		$sales = 999;
	}
	return $sales;
}

function SetRLForLowSalesItems( $starttopitems, $endtopitems, $daystopitems, $NewRL, $ShowMessages, $updateDB, $RootPath){

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
									AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
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
						SetReorderLevel("LowSalesAdjust", $myrow['stockid'], $mydistribution['loccode'], $mydistribution['oldrl'], $NewRL, $updateDB);
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

function SPGBelowMinimumSales($Shop, $NumDaysA, $MinimumSales){
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA));

	$SQL = "SELECT salesmancode,
				salesmanname,
				(SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.fromstkloc = '". $Shop . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesA
			FROM salesman
			WHERE salesman.current = 1
			AND (SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.fromstkloc = '". $Shop . "'
						AND salesorders.salesperson = salesman.salesmancode) <= ". $MinimumSales ."
			ORDER BY (SELECT SUM(linenetprice)
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.fromstkloc = '". $Shop . "'
						AND salesorders.salesperson = salesman.salesmancode) ASC";
//prnMsg($SQL);
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('SPG with daily sales below minimum of ') . locale_number_format($MinimumSales,0) . "/day during the last " . $NumDaysA . " days in ". $Shop .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Sales ') . locale_number_format($NumDaysA,0) . _(' days') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			$Code = $myrow['salesmancode'];
			$Name = $myrow['salesmanname'];
			
			$dailyA = locale_number_format(($myrow['salesA']/$NumDaysA),0);
			
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i,
					$Code,
					$Name,
					$dailyA
					);
			$i++;
		}
		
		echo '</table>
				</div>
				</form>';
	}
}

function SplittedpaymentsBySPG($maxdays, $maxsplitted){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$totalcash = 0;
	$totalcredit = 0;
	$totalreturned = 0;
	$total = 0;

	$SQL = "SELECT salesorders.salesperson, 
				COUNT(salesorders.klpaidcash + salesorders.klpaidcreditcard) AS splitted, 
				SUM(salesorders.klpaidcash + salesorders.klpaidcreditcard) AS amount
		FROM salesorders, debtorsmaster
		WHERE salesorders.debtorno = debtorsmaster.debtorno
			AND salesorders.orddate >= '". $StartDate. "'
			AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")
			AND salesorders.klpaidcash > 0
			AND salesorders.klpaidcreditcard > 0
		GROUP BY salesorders.salesperson
		HAVING COUNT(salesorders.klpaidcash + salesorders.klpaidcreditcard) >= '" . $maxsplitted . "'
		ORDER BY salesorders.salesperson";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('SPG with ') . $maxsplitted . _(' or more splitted payments during the last ') . $maxdays . _(' days.') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' .  _('SPG') . '</th>
							<th>' . _('Splitted') . '</th>
							<th>' . _('Amount') . '</th>
							<th>' . _('Date') . '</th>
							<th>' . _('Order') . '</th>
							<th>' . _('Yellow#') . '</th>
							<th>' . _('Cash') . '</th>
							<th>' . _('Credit Card') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['salesperson'],
					locale_number_format($myrow['splitted'],0),
					locale_number_format($myrow['amount'],0),
					'',
					'',
					'',
					'',
					''
					);
			$SQLDetails = "SELECT orderno,
								customerref,
								klpaidcash, 
								klpaidcreditcard,
								orddate								
						FROM salesorders
						WHERE orddate >= '". $StartDate. "'
							AND salesperson = '". $myrow['salesperson']. "'
							AND klpaidcash > 0
							AND klpaidcreditcard > 0
						ORDER BY orderno";
			$resultdetails = DB_query($SQLDetails);
			while ($myrowdetails = DB_fetch_array($resultdetails)) {
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						'',
						'',
						'',
						ConvertSQLDate($myrowdetails['orddate']),
						$myrowdetails['orderno'],
						$myrowdetails['customerref'],
						locale_number_format($myrowdetails['klpaidcash'],0),
						locale_number_format($myrowdetails['klpaidcreditcard'],0)
						);
			}
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function TopSalesNotInEnoughShops($starttopitems, $endtopitems, $maxdays, $minshops, $categories, $RootPath){
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
		$SQL = $SQL . " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_20 . "";
	}		
	if ($categories == "DISC50"){
		$SQL = $SQL . " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_50 . "";
	}		
	if ($categories == "DISC80"){
		$SQL = $SQL . " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_80 . "";
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

function WrongGiftItem($stockid, $customertype, $ErrorType, $OrderValue, $numDays, $RootPath){

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

	if ($customertype == "Retail"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ") ";
		$Titletext = "Retail";
	}elseif ($customertype == "Consignment"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_CONSIGNMENT . ") ";
		$Titletext = "Consignment";
	}elseif ($customertype == "Wholesale"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WHOLESALE . ") ";
		$Titletext = "Wholesale";
	}elseif ($customertype == "Online"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ") ";
		$Titletext = "Online";
	}else{
		$whereclause = " ";
	}
	
	if ($ErrorType == "OVER"){
		$Titletext .= _(' Orders over ') . locale_number_format($OrderValue,0). _(' without GIFT ') . $stockid . _(' during the last ') . $numDays . ' days';
		$Sign = " >= ";
		$Not = "NOT";
	}else{
		$Titletext .= _(' Orders below ') . locale_number_format($OrderValue,0). _(' with GIFT ') . $stockid . _(' during the last ') . $numDays . ' days';
		$Sign = " < ";
		$Not = "";
	}
	
	$SQL = "SELECT salesorders.orderno,	
				debtorsmaster.name,
				salesorders.customerref,
				salesorders.orddate,
				salesman.salesmanname,
				SUM((salesorderdetails.linenetprice)/currencies.rate) AS ordervalue
			FROM salesorders 
				INNER JOIN salesorderdetails 	
					ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
					ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN salesman
					ON salesorders.salesperson = salesman.salesmancode
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 1 " 
			. $whereclause .
			" GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.customerref,
				salesorders.orddate " .
			" HAVING salesorders.orddate >= '" . $StartDate . "'" . 
				" AND SUM((salesorderdetails.linenetprice)/currencies.rate)" . $Sign . $OrderValue .
				" AND " . $Not . " EXISTS (SELECT * 
								FROM salesorderdetails AS so2 
								WHERE salesorders.orderno = so2.orderno 
								AND so2.stkcode LIKE '" . $stockid . "' )". 
			" ORDER BY salesorders.orderno";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('webERP Order') . '</th>
							<th class="ascending">' . _('Yellow Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('SPG') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Total Value') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/OrderDetails.php?OrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['customerref'], 
					$myrow['name'], 
					$myrow['salesmanname'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}


function SyncDOKUPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun, $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order DOKU Payment Information" . "\n" . PrintTimeInformation();
	}
	
	$ComissionFlatDOKU = GetWeberpComissionFlatDOKU();
	$ComissionCCDOKU = GetWeberpComissionCCDOKU();

	// Now deal with the DOKU payment/s of the order...
	$SQL = "SELECT 	oc_dokuonecheckout.trx_id,
				oc_order.order_id,
				oc_order.currency_code AS ordercurrency,
				oc_order.currency_value,
				oc_order.customer_id,
				oc_order.customer_group_id,
				oc_customer.email,
				oc_order.total AS ordertotal,
				oc_dokuonecheckout.payment_channel,
				oc_dokuonecheckout.process_type,
				oc_dokuonecheckout.result_msg,
				oc_dokuonecheckout.status_code,
				oc_dokuonecheckout.approval_code,
				oc_dokuonecheckout.amount
		FROM oc_dokuonecheckout,
			 oc_order,
			 oc_customer
		WHERE oc_dokuonecheckout.transidmerchant  = oc_order.order_id
				AND oc_order.customer_id  = oc_customer.customer_id
				AND ( oc_dokuonecheckout.date_created >= '" . $LastTimeRun . "'
					OR oc_dokuonecheckout.date_updated >= '" . $LastTimeRun . "')
		ORDER BY oc_dokuonecheckout.trx_id";
	$result = DB_query_oc($SQL);

	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('DOKU Payments from OpenCart') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('CustomerID') . '</th>
								<th>' . _('email') . '</th>
								<th>' . _('webERP Code') . '</th>
								<th>' . _('OrderID') . '</th>
								<th>' . _('webERP #') . '</th>
								<th>' . _('Order Total') . '</th>
								<th>' . _('Order Curr') . '</th>
								<th>' . _('DOKU Total') . '</th>
								<th>' . _('Shipment') . '</th>
								<th>' . _('DOKU Curr') . '</th>
								<th>' . _('DOKU Trx') . '</th>
								<th>' . _('Trx Total') . '</th>
								<th>' . _('Channel') . '</th>
								<th>' . _('Commission') . '</th>
								<th>' . _('Date') . '</th>
								<th>' . _('Process') . '</th>
								<th>' . _('Result') . '</th>
								<th>' . _('Status code') . '</th>
								<th>' . _('Approval code') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update OpenCart DOKU payments in webERP failed');
		$InsertErrMsg = _('The SQL to insert OpenCart DOKU payments in webERP failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
			}
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($myrow['customer_group_id'], $myrow['ordercurrency']);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $myrow['order_id']);
			$PaymentSystem = OPENCART_DOKU_PAYMENT_SYSTEM;
			$CurrencyOrder = $myrow['ordercurrency'];
			$CurrencyPayment = $myrow['ordercurrency'];
			$TotalOrder = round($myrow['ordertotal'] * $myrow['currency_value'],0); // from OC default currency to order and payment currency
			$Rate = GetWeberpCurrencyRate($CurrencyOrder);
			$AmountPaid = $myrow['amount'];
			$TransactionID = $myrow['trx_id'];
			$GLAccount = GetWeberpGLAccountPayPalFromCustomerGroupAndCurrency($myrow['customer_group_id'], $CurrencyPayment);
			$GLCommissionAccount = GetWeberpGLCommissionAccountPayPalFromCustomerGroupAndCurrency($myrow['customer_group_id'], $CurrencyPayment);
			
			$Commission = $ComissionFlatDOKU; // For each tx there is a flat comission
			if (($myrow['payment_channel'] == "15") OR ($myrow['payment_channel'] == "16")){
				// if it is a payment via CC there is a CC commission extra from DOKU to add to the flat commission
				$Commission += round(($AmountPaid * $ComissionCCDOKU /100),0);
			}
			
			$WebERPDateOrder = date('Y-m-d H:i:s', strtotime( $myrow['created'] . -$TimeDifference . ' hours'));
			$FreightCost = RoundPriceFromCart(GetTotalFromOrder("shipping", $myrow['order_id']) * $myrow['currency_value'],$myrow['ordercurrency']);


			if (($myrow['ordercurrency'] == 'IDR') AND ($myrow['result_msg'] == 'SUCCESS')) {
				// order currency is IDR
				// AND has been paid OK
				$PaymentOK = true;
			}else{
				$PaymentOK = false;
			}

			if ($PaymentOK){
				$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
				InsertCustomerReceipt($CustomerCode, $AmountPaid, $FreightCost, $CurrencyPayment, $Rate, $GLAccount, $PaymentSystem, $TransactionID, $OrderNo, $PeriodNo);
				TransactionCommissionGL($CustomerCode, $GLAccount, $GLCommissionAccount, $Commission, $CurrencyPayment, $Rate, $PaymentSystem, $TransactionID, $PeriodNo);
				ChangeOrderQuotationFlag($OrderNo, 0); // it has been paid, so we consider it a firm order
			}

			if ($ShowMessages){
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$myrow['customer_id'],
						$myrow['email'],
						$CustomerCode,
						$myrow['order_id'],
						$OrderNo,
						$TotalOrder,
						$myrow['ordercurrency'],
						$AmountPaid,
						$FreightCost,
						$myrow['ordercurrency'],
						$TransactionID,
						$myrow['amount'],
						$myrow['payment_channel'],
						$Commission,
						$WebERPDateOrder,
						$myrow['process_type'],
						$myrow['result_msg'],
						$myrow['status_code'],
						$myrow['approval_code']
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $myrow['customer_id'] .
									      " = " . $myrow['email'] .
									      " = " . $CustomerCode .
									      " = " . $myrow['order_id'] .
									      " = " . $TotalOrder .
									      " = " . $myrow['ordercurrency'] .
									      " = " . $AmountPaid .
									      " = " . $FreightCost .
									      " = " . $myrow['payment_channel'] .
									      " = " . $myrow['process_type'] .
									      " = " . $myrow['result_msg'] .
									      " = " . $myrow['status_code'] .
									      " = " . $myrow['approval_code'] .
										  " --> " . $Action . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('DOKU Payments synchronized from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('DOKU Payments synchronized from OpenCart to webERP') . "\n\n";
	}
	return $EmailText;
}

function WebsiteCategoryDiscount($StockId, $Description, $Long, $Category){
	$WebCat = 0;

	if (ItemInList($Category, LIST_STOCK_CATEGORIES_OUTLET)){
		$WebCat = JEWELLERY_ON_SPECIAL;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isRing($StockId)){
		$WebCat = RINGS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isEarring($StockId)){
		$WebCat = EARRINGS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isPiercing($StockId)){
		$WebCat = EARRINGS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isEarcuff($StockId)){
		$WebCat = EARCUFFS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isBracelet($StockId)){
		$WebCat = BRACELETS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isNecklace($StockId)){
		$WebCat = NECKLACES_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isPendant($StockId)){
		$WebCat = PENDANTS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isToeRing($StockId)){
		$WebCat = TOERINGS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isAnklet($StockId)){
		$WebCat = ANKLETS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isBrooche($StockId)){
		$WebCat = BROOCHES_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isKeyHolder($StockId)){
		$WebCat = KEYHOLDERS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isFaceMask($StockId)){
		$WebCat = FACEMASKS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isJewelleryRoll($StockId)){
		$WebCat = JEWELLERY_ROLLS_ON_SPECIAL;	
	}	
	return $WebCat; 
}

function PackagingToBeRefilled($ShopType, $ShowAll, $RootPath){
/* EXPLAIN SQL 2014-05-20
Updated 3 index in loctransfers
*/

	if ($ShopType == "KAPAL-LAUT"){
		$TypeLoc  	   = "SHOPKL";
		$CodeBoxL 	   = "PKBX01-L";
		$CodeBoxM 	   = "PKBX01-M";
		$CodeBoxS 	   = "PKBX01-S";
		$CodeBagL 	   = "PKPB01-L";
		$CodeBagM 	   = "PKPB01-M";
		$CodeBagS 	   = "PKPB01-S";
		$CodeShoppingL = "PKSB02-L"; 
		$CodeShoppingM = "PKSB02-M"; 
		$CodeShoppingS = "PKSB02-S"; 
	}else{
		$TypeLoc  	   = "SHOPBL";
		$CodeBoxL 	   = "PKBX02-L";
		$CodeBoxM 	   = "PKBX02-M";
		$CodeBoxS 	   = "PKBX02-S";
		$CodeBagL 	   = "PKPB03-L";
		$CodeBagM 	   = "PKPB03-M";
		$CodeBagS 	   = "PKPB03-S";
		$CodeShoppingL = "PKSB04-L"; 
		$CodeShoppingM = "PKSB04-M"; 
		$CodeShoppingS = "PKSB04-S"; 
	}

	$TableResult = array();
	if ($ShowAll){
		$OrderBy = " ORDER BY locations.locationname";
	}else{
		$OrderBy = " ORDER BY locations.klemaillastpackacgingtransfer";
	}
	
	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.klemaillastpackacgingtransfer,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBoxL."') AS qty_box_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBoxL."') AS rl_box_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeBoxL."') AS ot_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBoxM."') AS qty_box_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBoxM."') AS rl_box_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeBoxM."') AS ot_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBoxS."') AS qty_box_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBoxS."') AS rl_box_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeBoxS."') AS ot_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBagL."') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBagL."') AS rl_bag_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeBagL."') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBagM."') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBagM."') AS rl_bag_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeBagM."') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBagS."') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeBagS."') AS rl_bag_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeBagS."') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeShoppingL."') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeShoppingL."') AS rl_shopping_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeShoppingL."') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeShoppingM."') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeShoppingM."') AS rl_shopping_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeShoppingM."') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeShoppingS."') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = '".$CodeShoppingS."') AS rl_shopping_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = '".$CodeShoppingS."') AS ot_shopping_s
			FROM locations
			WHERE locations.typeloc = '".$TypeLoc."' " .  
			$OrderBy;

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$numshops = 0;
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			$numshops++;
			$TableResult[$numshops]['show'] = FALSE; // to start we don't need to show any result
			$TableResult[$numshops]['loccode'] = $myrow['loccode'];
			$TableResult[$numshops]['locationname'] = $myrow['locationname'];
			$TableResult[$numshops]['rlfactorforpackaging'] = $myrow['rlfactorforpackaging'];
			$TableResult[$numshops]['klemaillastpackacgingtransfer'] = $myrow['klemaillastpackacgingtransfer'];

			$TableResult[$numshops]['qty_box_l'] = $myrow['qty_box_l'];
			$TableResult[$numshops]['qty_box_m'] = $myrow['qty_box_m'];
			$TableResult[$numshops]['qty_box_s'] = $myrow['qty_box_s'];
			$TableResult[$numshops]['qty_bag_l'] = $myrow['qty_bag_l'];
			$TableResult[$numshops]['qty_bag_m'] = $myrow['qty_bag_m'];
			$TableResult[$numshops]['qty_bag_s'] = $myrow['qty_bag_s'];
			$TableResult[$numshops]['qty_shopping_l'] = $myrow['qty_shopping_l'];
			$TableResult[$numshops]['qty_shopping_m'] = $myrow['qty_shopping_m'];
			$TableResult[$numshops]['qty_shopping_s'] = $myrow['qty_shopping_s'];

			$TableResult[$numshops]['ot_box_l'] = $myrow['ot_box_l'];
			$TableResult[$numshops]['ot_box_m'] = $myrow['ot_box_m'];
			$TableResult[$numshops]['ot_box_s'] = $myrow['ot_box_s'];
			$TableResult[$numshops]['ot_bag_l'] = $myrow['ot_bag_l'];
			$TableResult[$numshops]['ot_bag_m'] = $myrow['ot_bag_m'];
			$TableResult[$numshops]['ot_bag_s'] = $myrow['ot_bag_s'];
			$TableResult[$numshops]['ot_shopping_l'] = $myrow['ot_shopping_l'];
			$TableResult[$numshops]['ot_shopping_m'] = $myrow['ot_shopping_m'];
			$TableResult[$numshops]['ot_shopping_s'] = $myrow['ot_shopping_s'];

			$TableResult[$numshops]['rl_box_l'] = $myrow['rl_box_l'];
			$TableResult[$numshops]['rl_box_m'] = $myrow['rl_box_m'];
			$TableResult[$numshops]['rl_box_s'] = $myrow['rl_box_s'];
			$TableResult[$numshops]['rl_bag_l'] = $myrow['rl_bag_l'];
			$TableResult[$numshops]['rl_bag_m'] = $myrow['rl_bag_m'];
			$TableResult[$numshops]['rl_bag_s'] = $myrow['rl_bag_s'];
			$TableResult[$numshops]['rl_shopping_l'] = $myrow['rl_shopping_l'];
			$TableResult[$numshops]['rl_shopping_m'] = $myrow['rl_shopping_m'];
			$TableResult[$numshops]['rl_shopping_s'] = $myrow['rl_shopping_s'];
		}
	}

	/* Let's see if we need to show some shops	*/
	$i = 1;
	while ($i <= $numshops) {
		if (($TableResult[$i]['qty_box_l'] < $TableResult[$i]['rl_box_l']) OR 
			($TableResult[$i]['qty_box_m'] < $TableResult[$i]['rl_box_m']) OR 
			($TableResult[$i]['qty_box_s'] < $TableResult[$i]['rl_box_s']) OR 
			($TableResult[$i]['qty_bag_l'] < $TableResult[$i]['rl_bag_l']) OR 
			($TableResult[$i]['qty_bag_m'] < $TableResult[$i]['rl_bag_m']) OR 
			($TableResult[$i]['qty_bag_s'] < $TableResult[$i]['rl_bag_s']) OR 
			($TableResult[$i]['qty_shopping_l'] < $TableResult[$i]['rl_shopping_l']) OR 
			($TableResult[$i]['qty_shopping_m'] < $TableResult[$i]['rl_shopping_m']) OR 
			($TableResult[$i]['qty_shopping_s'] < $TableResult[$i]['rl_shopping_s'])) 
		{
			$TableResult[$i]['show'] = TRUE;
		}
		$i++;
	}
	
	if ($numshops > 0){
		$i = 1;
		$k = 0; //row colour counter

		while ($i <= $numshops) {
			
			if ($ShowAll OR ($TableResult[$i]['show'])) {
				// IF we are SHORT of any packaging material in that shop...
				// Or we show All the shops 
				if($showHeader){
					if ($ShopType == "KAPAL-LAUT"){
						echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shops needing Packaging Transfers (Do not forget to create transfer in webERP)' . '</strong></p>';
					}else{
						echo '<p class="page_title_text" align="center"><strong>' . 'BLINK Shops needing Packaging Transfers (Do not forget to create transfer in webERP)' . '</strong></p>';
					}
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th>' . _('') . '</th>
										<th colspan="3">' . _('Box L') . '</th>
										<th colspan="3">' . _('Box M') . '</th>
										<th colspan="3">' . _('Box S') . '</th>
										<th colspan="3">' . _('PouchBag L') . '</th>
										<th colspan="3">' . _('PouchBag M') . '</th>
										<th colspan="3">' . _('PouchBag S') . '</th>
										<th colspan="3">' . _('ShoppingBag L') . '</th>
										<th colspan="3">' . _('ShoppingBag M') . '</th>
										<th colspan="3">' . _('ShoppingBag S') . '</th>
										<th>' . _('') . '</th>
										<th>' . _('') . '</th>
									</tr>';
					$TableHeader = $TableHeader . '<tr>
										<th class="ascending">' . _('Shop') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Last Email') . '</th>
										<th class="ascending">' . _('Action') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);

				// Calculate how many we should ship to the shop...
				$NeedBoxL = max(0,round(($TableResult[$i]['rl_box_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_box_l'],0));
				$NeedBoxM = max(0,round(($TableResult[$i]['rl_box_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_box_m'],0));
				$NeedBoxS = max(0,round(($TableResult[$i]['rl_box_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_box_s'],0));
				$NeedBagL = max(0,round(($TableResult[$i]['rl_bag_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_l'],0));
				$NeedBagM = max(0,round(($TableResult[$i]['rl_bag_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_m'],0));
				$NeedBagS = max(0,round(($TableResult[$i]['rl_bag_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_s'],0));
				$NeedShoppingL = max(0,round(($TableResult[$i]['rl_shopping_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_l'],0));
				$NeedShoppingM = max(0,round(($TableResult[$i]['rl_shopping_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_m'],0));
				$NeedShoppingS = max(0,round(($TableResult[$i]['rl_shopping_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_s'],0));

				$ToShipBoxL = max(0,$NeedBoxL - $TableResult[$i]['ot_box_l']);
				$ToShipBoxM = max(0,$NeedBoxM - $TableResult[$i]['ot_box_m']);
				$ToShipBoxS = max(0,$NeedBoxS - $TableResult[$i]['ot_box_s']);
				$ToShipBagL = max(0,$NeedBagL - $TableResult[$i]['ot_bag_l']);
				$ToShipBagM = max(0,$NeedBagM - $TableResult[$i]['ot_bag_m']);
				$ToShipBagS = max(0,$NeedBagS - $TableResult[$i]['ot_bag_s']);
				$ToShipShoppingL = max(0,$NeedShoppingL - $TableResult[$i]['ot_shopping_l']);
				$ToShipShoppingM = max(0,$NeedShoppingM - $TableResult[$i]['ot_shopping_m']);
				$ToShipShoppingS = max(0,$NeedShoppingS - $TableResult[$i]['ot_shopping_s']);

				if ($ShopType == "KAPAL-LAUT"){
					$EmailLink = '<a href="' . $RootPath . '/KLPreparePackagingTransfer.php?Shop=' . $TableResult[$i]['loccode'] 
																						. '&Name=' . $TableResult[$i]['locationname'] 
																						. '&BoxL=' . $ToShipBoxL  
																						. '&BoxM=' . $ToShipBoxM  
																						. '&BoxS=' . $ToShipBoxS 
																						. '&BagL=' . $ToShipBagL 
																						. '&BagM=' . $ToShipBagM 
																						. '&BagS=' . $ToShipBagS 
																						. '&ShoppingL=' . $ToShipShoppingL 
																						. '&ShoppingM=' . $ToShipShoppingM 
																						. '&ShoppingS=' . $ToShipShoppingS 
																						.'">' . 'Send email to team' . '</a>';
				}else{
					$EmailLink = '<a href="' . $RootPath . '/KLPreparePackagingTransferBlink.php?Shop=' . $TableResult[$i]['loccode'] 
																						. '&Name=' . $TableResult[$i]['locationname'] 
																						. '&BoxL=' . $ToShipBoxL  
																						. '&BoxM=' . $ToShipBoxM  
																						. '&BoxS=' . $ToShipBoxS 
																						. '&BagL=' . $ToShipBagL 
																						. '&BagM=' . $ToShipBagM 
																						. '&BagS=' . $ToShipBagS 
																						. '&ShoppingL=' . $ToShipShoppingL 
																						. '&ShoppingM=' . $ToShipShoppingM 
																						. '&ShoppingS=' . $ToShipShoppingS 
																						.'">' . 'Send email to team' . '</a>';
					
				}
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$TableResult[$i]['locationname'], 
						locale_number_format_zero_blank($NeedBoxL,0), 
						locale_number_format_zero_blank($TableResult[$i]['ot_box_l'],0),
						locale_number_format_zero_blank($ToShipBoxL,0),
						locale_number_format_zero_blank($NeedBoxM, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_box_m'],0),
						locale_number_format_zero_blank($ToShipBoxM,0),
						locale_number_format_zero_blank($NeedBoxS, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_box_s'],0),
						locale_number_format_zero_blank($ToShipBoxS,0),
						locale_number_format_zero_blank($NeedBagL, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_l'],0),
						locale_number_format_zero_blank($ToShipBagL,0),
						locale_number_format_zero_blank($NeedBagM, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_m'],0),
						locale_number_format_zero_blank($ToShipBagM,0),
						locale_number_format_zero_blank($NeedBagS,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_s'],0),
						locale_number_format_zero_blank($ToShipBagS,0),
						locale_number_format_zero_blank($NeedShoppingL,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_l'],0),
						locale_number_format_zero_blank($ToShipShoppingL,0),
						locale_number_format_zero_blank($NeedShoppingM,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_m'],0),
						locale_number_format_zero_blank($ToShipShoppingM,0),
						locale_number_format_zero_blank($NeedShoppingS,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_s'],0),
						locale_number_format_zero_blank($ToShipShoppingS,0),
						ConvertSQLDateTime($TableResult[$i]['klemaillastpackacgingtransfer']), 
						$EmailLink
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

function PackagingToBeRefilledOutlet($ShowAll, $RootPath){

	$TableResult = array();
	if ($ShowAll){
		$OrderBy = " ORDER BY locations.locationname";
	}else{
		$OrderBy = " ORDER BY locations.klemaillastpackacgingtransfer";
	}
	
	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.klemaillastpackacgingtransfer,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB02-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB02-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKPB02-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS rl_shopping_m,
					(SELECT SUM(loctransfers.pendingqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.pendingqty != 0
							AND loctransfers.stockid = 'PKSB03') AS ot_shopping_m
			FROM locations
			WHERE locations.typeloc = 'SHOPOU' " .  
			$OrderBy;

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$numshops = 0;
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			$numshops++;
			$TableResult[$numshops]['show'] = FALSE; // to start we don't need to show any result
			$TableResult[$numshops]['loccode'] = $myrow['loccode'];
			$TableResult[$numshops]['locationname'] = $myrow['locationname'];
			$TableResult[$numshops]['rlfactorforpackaging'] = $myrow['rlfactorforpackaging'];
			$TableResult[$numshops]['klemaillastpackacgingtransfer'] = $myrow['klemaillastpackacgingtransfer'];

			$TableResult[$numshops]['qty_bag_l'] = $myrow['qty_bag_l'];
			$TableResult[$numshops]['qty_bag_m'] = $myrow['qty_bag_m'];
			$TableResult[$numshops]['qty_bag_s'] = $myrow['qty_bag_s'];
			$TableResult[$numshops]['qty_shopping_m'] = $myrow['qty_shopping_m'];

			$TableResult[$numshops]['ot_bag_l'] = $myrow['ot_bag_l'];
			$TableResult[$numshops]['ot_bag_m'] = $myrow['ot_bag_m'];
			$TableResult[$numshops]['ot_bag_s'] = $myrow['ot_bag_s'];
			$TableResult[$numshops]['ot_shopping_m'] = $myrow['ot_shopping_m'];

			$TableResult[$numshops]['rl_bag_l'] = $myrow['rl_bag_l'];
			$TableResult[$numshops]['rl_bag_m'] = $myrow['rl_bag_m'];
			$TableResult[$numshops]['rl_bag_s'] = $myrow['rl_bag_s'];
			$TableResult[$numshops]['rl_shopping_m'] = $myrow['rl_shopping_m'];
		}
	}
	
	if ($numshops > 0){
		$i = 1;
		$k = 0; //row colour counter

		while ($i <= $numshops) {
			if (($TableResult[$i]['qty_bag_l'] < $TableResult[$i]['rl_bag_l']) OR 
				($TableResult[$i]['qty_bag_m'] < $TableResult[$i]['rl_bag_m']) OR 
				($TableResult[$i]['qty_bag_s'] < $TableResult[$i]['rl_bag_s']) OR 
				($TableResult[$i]['qty_shopping_m'] < $TableResult[$i]['rl_shopping_m'])) 
			{
				$TableResult[$i]['show'] = TRUE;
			}
			
			if ($ShowAll OR ($TableResult[$i]['show'])) {
				// IF we are SHORT of any packaging material in that shop...
				// Or we show All the shops 
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . 'OUTLET Shops needing OUTLET Packaging Transfers (Do not forget to create transfer in webERP)' . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th>' . _('') . '</th>
										<th colspan="3">' . _('OUTLET PouchBag L') . '</th>
										<th colspan="3">' . _('OUTLET PouchBag M') . '</th>
										<th colspan="3">' . _('OUTLET PouchBag S') . '</th>
										<th colspan="3">' . _('OUTLET ShoppingBag') . '</th>
										<th>' . _('') . '</th>
										<th>' . _('') . '</th>
									</tr>';
					$TableHeader = $TableHeader . '<tr>
										<th class="ascending">' . _('KL Shop') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Needs') . '</th>
										<th class="ascending">' . _('Transit') . '</th>
										<th class="ascending">' . _('To Ship') . '</th>
										<th class="ascending">' . _('Last Email') . '</th>
										<th class="ascending">' . _('Action') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);

				// Calculate how many we should ship to the shop...
				$NeedBagL = max(0,round(($TableResult[$i]['rl_bag_l'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_l'],0));
				$NeedBagM = max(0,round(($TableResult[$i]['rl_bag_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_m'],0));
				$NeedBagS = max(0,round(($TableResult[$i]['rl_bag_s'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_bag_s'],0));
				$NeedShoppingM = max(0,round(($TableResult[$i]['rl_shopping_m'] * $TableResult[$i]['rlfactorforpackaging']) - $TableResult[$i]['qty_shopping_m'],0));

				$ToShipBagL = max(0,$NeedBagL - $TableResult[$i]['ot_bag_l']);
				$ToShipBagM = max(0,$NeedBagM - $TableResult[$i]['ot_bag_m']);
				$ToShipBagS = max(0,$NeedBagS - $TableResult[$i]['ot_bag_s']);
				$ToShipShoppingM = max(0,$NeedShoppingM - $TableResult[$i]['ot_shopping_m']);

				$EmailLink = '<a href="' . $RootPath . '/KLPreparePackagingTransferOutlet.php?Shop=' . $TableResult[$i]['loccode'] 
																								. '&Name=' . $TableResult[$i]['locationname'] 
																								. '&BagL=' . $ToShipBagL 
																								. '&BagM=' . $ToShipBagM 
																								. '&BagS=' . $ToShipBagS 
																								. '&ShoppingM=' . $ToShipShoppingM 
																								.'">' . 'Send email to team' . '</a>';
				
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$TableResult[$i]['locationname'], 
						locale_number_format_zero_blank($NeedBagL, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_l'],0),
						locale_number_format_zero_blank($ToShipBagL,0),
						locale_number_format_zero_blank($NeedBagM, 0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_m'],0),
						locale_number_format_zero_blank($ToShipBagM,0),
						locale_number_format_zero_blank($NeedBagS,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_bag_s'],0),
						locale_number_format_zero_blank($ToShipBagS,0),
						locale_number_format_zero_blank($NeedShoppingM,0),
						locale_number_format_zero_blank($TableResult[$i]['ot_shopping_m'],0),
						locale_number_format_zero_blank($ToShipShoppingM,0),
						ConvertSQLDateTime($TableResult[$i]['klemaillastpackacgingtransfer']), 
						$EmailLink
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

function GetLenghtClassId($webERPDimensions, $language_id){
	$SQL = "SELECT length_class_id
			FROM oc_length_class_description
			WHERE unit = '" . $webERPDimensions . "'
				AND language_id = '" . $language_id . "'";
	$ErrMsg =_('Could not get the LenghtClassId in OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetLenghtUnits($LenghtClassId, $language_id){
	$SQL = "SELECT unit
			FROM oc_length_class_description
			WHERE length_class_id = '" . $LenghtClassId . "'
				AND language_id = '" . $language_id . "'";
	$ErrMsg =_('Could not get the Lenght Units in OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetOpenCartSettingValue($Store, $Code, $Key){
	$SQL = "SELECT value
			FROM oc_setting
			WHERE store_id = '" . $Store . "'
				AND `code` = '" . $Code . "'
				AND `key` = '" . $Key . "'";
	$ErrMsg =_('Could not get the SettingId in OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function UpdateSettingValueOpenCartByCodeAndKey($Store, $Code, $Key, $Value){
	$DbgMsg = _('The SQL statement that failed was');
	$UpdateErrMsg = _('The SQL to update setting value in Opencart failed');
	$sqlUpdate = "UPDATE oc_setting
					SET	value = '" . $Value . "'
				WHERE `code` = '" . $Code . "'
					AND `key` = '" . $Key . "'
					AND `store_id` = '" . $Store . "'";

	$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);
}

function MaintainWeberpOutletSalesCategories($ShowMessages, $LastTimeRun, $EmailText=''){

	/* Look for all products in weberp marked as OUTLET and "something else"*/

	$SQL = "SELECT salescatprod.stockid
			FROM salescatprod
			WHERE salescatprod.salescatid IN (" . ONLINESHOP_OUTLET_SALES_CATEGORIES . ")";
		$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Maintain webERP Outlet Sales Categories') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update outlet sales category in webERP failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			$ProductId = $myrow['stockid'];

			$Action = "Delete sales categories not OUTLET";
			$sqlDelete = "DELETE FROM salescatprod
							WHERE stockid = '" . $ProductId . "'
								AND salescatid NOT IN (" . ONLINESHOP_OUTLET_SALES_CATEGORIES . ")";
			$resultDelete = DB_query($sqlDelete,$UpdateErrMsg,$DbgMsg,true);
			if ($ShowMessages){
				printf('<td>%s</td>
						<td>%s</td>
						</tr>',
						$ProductId,
						$Action
						);
			}
/*			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($ProductId, 20, " ") . " --> " . $Action . "\n";
			}
*/			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('webERP Outlet Sales Categories Maintained') . "\n\n";
	}
	return $EmailText;
}

function SyncFeaturedList($ShowMessages, $LastTimeRun, $EmailText= ''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Clean Duplicated URL Alias" . "\n" . PrintTimeInformation();
	}
	/* Let's get the ID for the list of featured products for featured module
	   we will need it later on to save the results in the appropiate setting */
	$SettingId = GetOpenCartSettingId(0,"featured", "featured_product");
	$ListFeaturedOpenCart = "";

	/* Look for the featured items in webERP
	we'll recreate the full list everytime as it will be short and
	it's a list that will change quite often */
	$SQL = "SELECT DISTINCT(salescatprod.stockid)
			FROM salescatprod
			WHERE salescatprod.featured ='1'
			ORDER BY salescatprod.stockid";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Create featured list in OpenCart') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('OpenCartID') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$Action = "Added";
		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			/* Field Matching */
			$Model = $myrow['stockid'];

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model);

			// Let's build the list
			if ($i == 0){
				$ListFeaturedOpenCart = strval($ProductId);
			}else{
				$ListFeaturedOpenCart = $ListFeaturedOpenCart . "," . strval($ProductId);
			}
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>',
						$Model,
						$ProductId,
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " = " . $ProductId. " --> " . $Action . "\n";
			}
			$i++;
		}
		UpdateSettingValueOpenCart($SettingId, $ListFeaturedOpenCart);
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Products included in the featured list in OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Products included in the featured list in OpenCart') . "\n\n";
	}
	return $EmailText;
}

function SyncSalesCategories($ShowMessages, $LastTimeRun, $EmailText= ''){
	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());
	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Sales Categories" . "\n" . PrintTimeInformation();
	}

	$SQL = "SELECT salescatid,
				parentcatid,
				salescatname,
				active
			FROM salescat
			WHERE date_created >= '" . $LastTimeRun . "'
				OR date_updated >= '" . $LastTimeRun . "'
			ORDER BY salescatid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Sales categories') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('SalesCatID') . '</th>
								<th>' . _('SalesCatName') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update sales categories in Opencart failed');
		$InsertErrMsg = _('The SQL to insert sales categories in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {

			/* FIELD MATCHING */
			if ($myrow['parentcatid'] == 0){
				$Top = 1;
			}else{
				$Top = 0;
			}

			$Column = 1;
			$Language_Id = 1; // for now NO multi language
			$SortOrder = 1;
			$Name = trim($myrow['salescatname']);
			$Description = trim($myrow['salescatname']);
			$MetaTitle = trim($myrow['salescatname']);
			$MetaDescription = CreateMetaDescriptionSalesCategory('Sales category', trim($myrow['salescatname']));
			$CategoryId = $myrow['salescatid'];
			if (DataExistsInOpenCart('oc_category', 'category_id', $myrow['salescatid'])){
				$Action = "Update";
				$sqlUpdate = "UPDATE oc_category
								SET parent_id 		= '" . $myrow['parentcatid'] . "',
									status 			= '" . $myrow['active'] . "',
									top 			= '" . $Top . "',
									date_modified 	= '" . $ServerNow . "'
								WHERE category_id 	= '" . $CategoryId . "'";
				$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);

				$sqlUpdate = "UPDATE oc_category_description
								SET language_id 		= '" . $Language_Id . "',
									name	 			= '" . $Name . "',
									description			= '" . $Description . "',
									meta_title 			= '" . $MetaTitle . "',
									meta_description	= '" . $MetaDescription . "',
									meta_keyword		= '" . $MetaKeyword . "'
								WHERE category_id 	= '" . $CategoryId . "'";
				$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);

			}else{
				$Action = "Insert";
				$sqlInsert = "INSERT INTO oc_category
								(category_id,
								image,
								parent_id,
								top,
								`column`,
								sort_order,
								status,
								date_added,
								date_modified)
							VALUES
								('" . $CategoryId . "',
								'',
								'" . $myrow['parentcatid'] . "',
								'" . $Top . "',
								'" . $Column . "',
								'" . $SortOrder . "',
								'" . $myrow['active'] . "',
								'" . $ServerNow . "',
								'" . $ServerNow . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
				$sqlInsert = "INSERT INTO oc_category_description
								(category_id,
								language_id,
								name,
								description,
								meta_title,
								meta_description,
								meta_keyword)
							VALUES
								('" . $CategoryId . "',
								'" . $Language_Id . "',
								'" . $Name . "',
								'" . $Description . "',
								'" . $MetaTitle . "',
								'" . $MetaDescription . "',
								'" . $MetaKeyword . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
				$sqlInsert = "INSERT INTO oc_category_to_store
								(category_id,
								store_id)
							VALUES
								('" . $CategoryId . "',
								'" . $StoreId . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
				$SortOrder++;

			}

			// SEO URL Keywords if needed
			$SEOQuery = 'category_id='.$CategoryId;
			$SEOKeyword = CreateSEOKeyword($Name);
			// This bit should be smarter... we don't know if a sales category is from KL or Blink, so we assign to both
			// outlet and wholesale, yes, they are.
			MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, OPENCART_STORE_KAPAL_LAUT, $LanguageId);
			MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, OPENCART_STORE_BLINK, $LanguageId);
			MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, OPENCART_STORE_OUTLET, $LanguageId);
			MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, OPENCART_STORE_WHOLESALE, $LanguageId);

			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$myrow['salescatid'],
						$Name,
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $myrow['salescatid'] . " = " . $Name. " --> " . $Action . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		if ($i > 0){
			prnMsg('Remind to run Repair Categories on OpenCart!','warn');
		}
		prnMsg(locale_number_format($i,0) . ' ' . _('Sales Categories synchronized from webERP to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Sales Categories synchronized from webERP to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function ActivateCategoryDependingOnQOH($ShowMessages, $LastTimeRun, $EmailText= ''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Activate category Depending on QOH" . "\n" . PrintTimeInformation();
	}
	$SQL = "SELECT salescatid,
				parentcatid,
				salescatname,
				active,
				(SELECT SUM(locstock.quantity)
					FROM salescatprod,locstock,locations
					WHERE salescat.salescatid = salescatprod.salescatid
						AND salescatprod.stockid = locstock.stockid
						AND locstock.loccode = locations.loccode
						AND locations.stockavailableforonline = '1'
				) as qoh
			FROM salescat
			WHERE active = 1
				AND parentcatid != 0
			ORDER BY salescatname";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Activate/Inactivate Sales Categories depending on QOH') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('Sales Category') . '</th>
								<th>' . _('QOH') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to Activate Categories depending QOH in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {

			/* Field Matching */
			$CategoryId = $myrow['salescatid'];
			$CategoryName = $myrow['salescatname'];
			$CategoryQOH = $myrow['qoh'];
			if (isset($myrow['qoh'])){
				if ($CategoryQOH > 0){
					$CategoryQOH = $myrow['qoh'];
					$Status = 1;
					$Action = "Active";
				}else{
					$CategoryQOH = 0;
					$Status = 0;
					$Action = "Inactive QOH = 0";
				}
			}else{
				$CategoryQOH = 0;
				$Status = 0;
				$Action = "Inactive QOH = 0";
			}

			$sqlUpdate = "UPDATE oc_category SET
								status = '" . $Status . "'
							WHERE category_id = '" . $CategoryId . "'";
			$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>',
						$CategoryName,
						locale_number_format($CategoryQOH,0),
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $CategoryName . " --> " . locale_number_format($CategoryQOH,0) . " --> " . $Action . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('OpenCart Categories Activated / Inactivated depending on QOH'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('OpenCart Categories Activated / Inactivated depending on QOH') . "\n\n";
	}
	return $EmailText;
}

function MaintainOpenCartOutletSalesCategories($ShowMessages, $LastTimeRun, $EmailText = ''){

	/* Look for all products in OC marked as OUTLET and "something else"*/
	$SQL = "SELECT oc_product.product_id,
				   oc_product.model
			FROM oc_product_to_category ,
				 oc_product
			WHERE oc_product.product_id = oc_product_to_category.product_id
				AND category_id IN (" . ONLINESHOP_OUTLET_SALES_CATEGORIES . ")";
		$result = DB_query_oc($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Maintain Outlet Sales Categories') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product QOH in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {

			$ProductId = $myrow['product_id'];
			$Model = $myrow['model'];

			$Action = "Delete sales categories not OUTLET";
			$sqlDelete = "DELETE FROM oc_product_to_category
							WHERE product_id = '" . $ProductId . "'
								AND category_id NOT IN (" . ONLINESHOP_OUTLET_SALES_CATEGORIES . ")";
			$resultDelete = DB_query_oc($sqlDelete,$UpdateErrMsg,$DbgMsg,true);
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						</tr>',
						$Model,
						$Action
						);
			}
/*			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " --> " . $Action . "\n";
			}
*/			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('OpenCart Outlet Sales Categories maintained') . "\n\n";
	}
	return $EmailText;
}

function SyncRelatedItems($ShowMessages, $LastTimeRun, $EmailText = ''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Related Items" . "\n" . PrintTimeInformation();
	}

	$SQL = "SELECT relateditems.stockid,
				relateditems.related
			FROM relateditems, stockmaster
			WHERE relateditems.stockid = stockmaster.stockid
				AND stockmaster.discontinued = '0'
				AND stockmaster.klsynctoopencart = '1'
				AND (relateditems.date_created >= '" . $LastTimeRun . "'
					OR relateditems.date_updated >= '" . $LastTimeRun . "')
			ORDER BY relateditems.stockid, 
				relateditems.related";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Related Items') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('Item webERP') . '</th>
								<th>' . _('Related webERP') . '</th>
								<th>' . _('Item OC') . '</th>
								<th>' . _('Related OC') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update related items in Opencart failed');
		$InsertErrMsg = _('The SQL to insert related items in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {

			/* FIELD MATCHING */
			$ProductId = GetOpenCartProductId($myrow['stockid']);
			$RelatedId = GetOpenCartProductId($myrow['related']);
			if (($ProductId != '') AND ($RelatedId != '')){
				// if both products still exist in OpenCart
				if (((isRing($ProductId)) AND (isRing($RelatedId))) == FALSE){
					// if both are rings most probably is a sizing "related", so we don't sync them
					$k = StartEvenOrOddRow($k);
					if (DataExistsInOpenCart('oc_product_related', 'product_id', $ProductId, 'related_id', $RelatedId )){
						$Action = "Update";
					}else{
						$Action = "Insert";
						$sqlInsert = "INSERT INTO oc_product_related
										(product_id,
										related_id)
									VALUES
										('" . $ProductId . "',
										'" . $RelatedId . "'
										)";
						$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
					}
					if ($ShowMessages){
						printf('<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								</tr>',
								$myrow['stockid'],
								$myrow['related'],
								$ProductId,
								$RelatedId,
								$Action
								);
					}
					$i++;
				}
			}
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Pairs of related items synchronized from webERP to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Pairs of related items synchronized from webERP to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function CleanDuplicatedUrlAlias($ShowMessages, $LastTimeRun, $EmailText = ''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Clean Duplicated URL Alias" . "\n" . PrintTimeInformation();
	}

	$SQL = "SELECT 	oc_seo_url.seo_url_id,
				oc_seo_url.query,
				oc_seo_url.keyword
		FROM oc_seo_url
		ORDER BY oc_seo_url.query,
				oc_seo_url.seo_url_id DESC";
	$result = DB_query_oc($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 0;
		$PreviousQuery = "";
		$PreviousKeyword = "";
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			if ($PreviousQuery == $myrow['query']){
				// we have a duplicated
				$DuplicatedQuery = $myrow['query'];
				$DuplicatedKeyword = $myrow['keyword'];

				if ($ShowHeader){
					if ($ShowMessages){
						echo '<p class="page_title_text" align="center"><strong>' . _('Duplicated URL Alias clean up') .'</strong></p>';
						echo '<div>';
						echo '<table class="selection">';
						$TableHeader = '<tr>
											<th>' . _('URL Alias ID') . '</th>
											<th>' . _('Query') . '</th>
											<th>' . _('Keyword') . '</th>
										</tr>';
						echo $TableHeader;
					}
					$ShowHeader = FALSE;
				}
				// we delete the duplicated
				$sqlDelete = "DELETE FROM oc_seo_url
							WHERE seo_url_id = '" .  $myrow['seo_url_id'] . "'";
				$resultDelete = DB_query_oc($sqlDelete,$UpdateErrMsg,$DbgMsg,true);

				// we set it up as a redirect just in case someome uses this old URL keyword
				if ($PreviousKeyword != $myrow['keyword']){
					$Active = 1;
					$FromURL = PATH_OPENCART_BASE . '/'. $myrow['keyword'];
					$ToURL = PATH_OPENCART_BASE . '/' . ROUTE_TO_PRODUCT . $myrow['query'];
					$ResponseCode = REDIRECT_RESPONSE_CODE;
					$FromDate = date('Y-m-d');
					$TimesUsed = 0;
					$sqlInsert = "INSERT INTO oc_redirect
								(active,
								from_url,
								to_url,
								response_code,
								date_start,
								times_used)
							VALUES
								('" . $Active . "',
								'" . $FromURL . "',
								'" . $ToURL . "',
								'" . $ResponseCode . "',
								'" . $FromDate . "',
								'" . $TimesUsed . "'
								)";
					$resultInsert = DB_query_oc($sqlInsert,$UpdateErrMsg,$DbgMsg,true);
				}

				if ($ShowMessages){
					$k = StartEvenOrOddRow($k);
					printf('<td class="number">%s</td>
							<td>%s</td>
							<td>%s</td>
							</tr>',
							locale_number_format($myrow['seo_url_id'],0),
							$myrow['query'],
							$myrow['keyword']
							);
				}
				if ($EmailText !=''){
					$EmailText = $EmailText . locale_number_format($myrow['seo_url_id'],0) . " --> " . $myrow['query'] . " --> ". $myrow['keyword'] . "\n";
				}
				$i++;
			}
			$PreviousQuery = $myrow['query'];
			$PreviousKeyword = $myrow['keyword'];
		}
		if (!$ShowHeader){
			if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
			}
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Duplicated URL Alias synchronized in OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Duplicated URL Alias synchronized in OpenCart') . "\n\n";
	}
	return $EmailText;
}


?>
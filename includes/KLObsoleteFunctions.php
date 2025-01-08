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

function AdjustNoSales($Location, $maxdays, $maxmanualchanges, $topitems, $TopItemsDays, $ShowMessages, $updateDB, $RootPath){
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
					AND locstock.loccode = '" . $Location . "'
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
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Items with NO sales on last ') . $maxdays . ' days in ' . $Location;
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">
					<thead>
						<tr>
							<th>' . _('#') . '</th>
							<th>' . _('Code') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Category') . '</th>
							<th>' . _('QOH') . '</th>
							<th>' . _('Old RL') . '</th>
							<th>' . _('New RL') . '</th>
							<th>' . _('Notes') . '</th>
						</tr>
					</thead>
					<tbody>';
		}
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$NewRL = 0;
			$notes = "";
			// Check if belongs to a special category
			// comented on change of category structure 2014-05-06
/*			if ($MyRow['categoryid'] == "KLPRGE"){
				$NewRL = $MyRow['reorderlevel'];
				$notes = "KLPRGE - Tali. RL Not changed";
			}
*/			// check if RING and we have sold on the same location same model, other sizes, then should be RL = 1.
			if (isRing($MyRow['stockid'])){
				// get the model code and see if the location has sold of different sizes, so we need to keep all sizes at the shop
				// even if no sales.
				$RingModel = CodeModelRing($MyRow['stockid']);
				$SalesModel = SalesOfItemByLocation($RingModel, $Location, $maxdays);
				if ($SalesModel != 0){
					// sales for some size, so we want to keep it in stock (just 1, in case there were 2 or more)...
					$NewRL = 1;
					$notes = $SalesModel . " sold other sizes.";
				}
			}
			if (isTopSalesItem($MyRow['stockid'], $topitems, $TopItemsDays)){
				$NewRL = $MyRow['reorderlevel'];
				$notes = "Top ". $topitems . " sales.";
			}
/* KL RICARD COMMENTED ON 2014-06-10
			// if manually reseted, not change it
			$lastManualModification = isReorderLevelManuallyChanged($MyRow['stockid'], $Location, $maxmanualchanges);
			if ($lastManualModification != '0000-00-00'){
				$NewRL = $MyRow['reorderlevel'];
				$notes = "Manually changed on ". ConvertSQLDate($lastManualModification);
			}
*/			if ($ShowMessages){
				printf('<tr class="striped_row">
						<td class="number">%s</td>
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
						$MyRow['description'], 
						$MyRow['categoryid'], 
						locale_number_format($MyRow['quantity'],0),
						locale_number_format($MyRow['reorderlevel'],0),
						locale_number_format($NewRL,0),
						$notes
						);
				$i++;
			}
			SetReorderLevel("AdjustNoSales", $MyRow['stockid'],$Location, $MyRow['reorderlevel'], $NewRL, $updateDB);
		}
		if ($ShowMessages){
			echo '</tbody>
				  </table>
				  </div>
				  </form>';
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Top ') . $Days . _(' retail sales days by shop since '). ConvertSQLDate($FromDate);
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' .  _('#') . '</th>
						<th class="SortedColumn">' .  _('Date') . '</th>
						<th class="SortedColumn">' .  _('Shop') . '</th>
						<th class="SortedColumn">' . _('Sales') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while (($MyRow = DB_fetch_array($Result)) AND ($i <= $Days)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($i,0),
					ConvertSQLDate($MyRow['orddate']),
					$MyRow['debtorno'],
					locale_number_format($MyRow['sales'],0)
					);
			$i++;
		}
		echo '</tbody>
			  </table>
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
	
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items in category ') . $CategoryId . " with more than " . $minsales . " pcs sold in the last " . $days . " days.(GOOD ITEMS)";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('QOH') . '</th>
						<th class="SortedColumn">' . _('Sold '). $days . ' Days' . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					locale_number_format($MyRow['qoh'],0),
					locale_number_format($MyRow['sold'],0)
					);
			$i++;
		}
		echo '</tbody>
			  </table>
			  </div>
			  </form>';
	}
}

function ImagesShouldNotBeInOpencartCatalog($RootPath){

	$ShowHeader = TRUE;
	$k = 0; //row colour counter
	$i= 0;
	// get all images in part_pics folder
	$suffix = ".jpg";
	$ImageFiles = getDirectoryTree(ABSOLUTE_PATH_OPENCART_IMAGES);
	foreach ($ImageFiles as $file) {
		$StockID = substr($file, 0, strpos($file, $suffix));
		if (strpos($StockID, '.1') > 0){
			$StockID = substr($file, 0, strpos($StockID, '.1'));
		}
		if (strpos($StockID, '.2') > 0){
			$StockID = substr($file, 0, strpos($StockID, '.2'));
		}
		if (strpos($StockID, '.3') > 0){
			$StockID = substr($file, 0, strpos($StockID, '.3'));
		}
		if (strpos($StockID, '.4') > 0){
			$StockID = substr($file, 0, strpos($StockID, '.4'));
		}
		if (strpos($StockID, '.5') > 0){
			$StockID = substr($file, 0, strpos($StockID, '.5'));
		}
		$ProductId = GetOpenCartProductId($StockID);
		if ($ProductId == 0){
			if ($ShowHeader){
				$TableTitleText = _('Opencart Images without product in OpenCart');
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th class="SortedColumn">' . _('File') . '</th>
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
		echo '</tbody>
			  </table>
			  </div>
			  </form>';
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
	
	$Result = DB_query($SQL);		
	$ShowHeader = TRUE;
	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		$QtyNeeded = $OptimalStock - $MyRow['qoh'];
		if ($QtyNeeded > 0){
			if ($ShowHeader){
				$TableTitleText = $ItemDescription . ' Items with QOH (kantor+toko) < ' . $MinimumStock . ' pcs.';
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Needed') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}

			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					locale_number_format($MyRow['qoh'],0),
					locale_number_format($QtyNeeded,0)
					);
		}
		$i++;
	}
	if (!$ShowHeader){
		echo '</tbody>
			  </table>
			  </div>
			  </form>';
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
	$ResultTI = DB_query($SQL);		
	$MyRowTI = DB_fetch_array($ResultTI);
	$NumberOfTopItems = ceil($MyRowTI[0]/100*$PercentageOfTopItems);
	
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
	
	$Result = DB_query($SQL);		
	$ShowHeader = TRUE;
	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		$Forecast = ceil($MyRow['totalinvoiced'] / $DaysTopSales * $DaysMinimumStock);
		$QtyNeeded = $Forecast - $MyRow['qoh'] - $MyRow['qoo'] - $MyRow['qow'];
		if ($QtyNeeded > 0){
			if ($ShowHeader){
				$TableTitleText = $NumberOfTopItems . ' Top Items from ' . strtoupper($StockCatDescription) . ' with insufficient stock for the next ' . $DaysMinimumStock . ' days (Excluded Samples).';
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th class="SortedColumn">' . _('Sales ') . $DaysTopSales . '</th>
								<th class="SortedColumn">' . _('Forecast ') . $DaysMinimumStock . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('QOO') . '</th>
								<th class="SortedColumn">' . _('QOW') . '</th>
								<th class="SortedColumn">' . _('Needed') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = FALSE;
			}

			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stkcode'] . '">' . $MyRow['stkcode'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					$MyRow['description'], 
					locale_number_format($MyRow['totalinvoiced'],0),
					locale_number_format($Forecast,0),
					locale_number_format($MyRow['qoh'],0),
					locale_number_format($MyRow['qoo'],0),
					locale_number_format($MyRow['qow'],0),
					locale_number_format($QtyNeeded,0)
					);
		}
		$i++;
	}
	if (!$ShowHeader){
		echo '</tbody>
			  </table>
			  </div>
			  </form>';
	}
}

function isReorderLevelManuallyChanged($StockID, $loccode, $maxmanualchanges){
	if ($maxmanualchanges == 0){
		return '0000-00-00';
	}
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxmanualchanges));
	$SQL="SELECT transactiondate
		FROM audittrail
		WHERE transactiondate >= '".$StartDate."'
			AND querystring LIKE '%" . $StockID . "%' 
			AND querystring LIKE '%" . $loccode . "%' 
			AND querystring LIKE '%locstock%reorderlevel%' 
		ORDER BY transactiondate DESC
		LIMIT 1";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$lastdate = $MyRow['transactiondate'];
	}else{
		$lastdate = '0000-00-00';
	}
	return $lastdate;

}

function isTopSalesItem($StockID, $topitems, $TopItemsDays){

	$TopSalesField = GetTopSalesField($TopItemsDays);

	$SQL="SELECT ". $TopSalesField." AS topsalesposition
		  FROM klsalesperformance
		  WHERE stockid = '" . $StockID . "'";
	$Result = DB_query($SQL);
	$istopsales = false;
	if (DB_num_rows($Result) != 0){
		if ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['topsalesposition'] <= $topitems){
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_OUTLET)){
			$TableTitleText = $CategoryId ._(' Items with stock available at Kantor but RL zero for ') . LIST_SHOPS_OUTLET;
		}elseif (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_OUTLET)){
			$TableTitleText = $CategoryId ._(' Items with stock available at Kantor but RL zero for ') . LIST_SHOPS_OUTLET;
		}else{
			$TableTitleText = $CategoryId ._(' Items with stock available at Kantor but RL zero for all toko KL');
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Category') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('QOH Kantor') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['categoryid'], 
					$MyRow['description'], 
					locale_number_format($MyRow['QtyKantor'],0)
					);
			$i++;
		}
		echo '</tbody>
			  </table>
			  </div>
			  </form>';
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
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow[0] > 0){
		$Text = locale_number_format($MyRow[0],0) . " items need Automatic Description Translation";
		ShowWarningTitle($Text);
	}
}

function ItemsNeedingTranslationRevision($RootPath){
	$SQL = "SELECT COUNT(stockdescriptiontranslations.stockid)
			FROM stockmaster, stockdescriptiontranslations
			WHERE stockmaster.stockid = stockdescriptiontranslations.stockid
				AND stockmaster.discontinued = 0
				AND needsrevision = '1'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow[0] > 0){
		$Text = locale_number_format($MyRow[0],0) . " items need Translation Revision";
		ShowWarningTitle($Text);
	}
}

function ItemsNoSalesInLocation($Location, $maxdays, $QOHAvailable, $RootPath){
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
					AND locstock.loccode = '" . $Location . "'
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
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items with NO sales on last ') . $maxdays . ' days in ' . $Location . ' with stock <= ' . $QOHAvailable . ' at shops or kantor';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('Category') . '</th>
						<th class="SortedColumn">' . _('QOH ') . $Location . '</th>
						<th class="SortedColumn">' . _('QOH Shops+Kantor') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['description'], 
					$MyRow['categoryid'], 
					locale_number_format($MyRow['quantity'],0),
					locale_number_format($MyRow['qtyavailable'],0)
					);
			$i++;
		}
		echo '</tbody>
			  </table>
			  </div>
			  </form>';
	}
}

function ItemsNotTopSalesInShop($starttopitems, $endtopitems, $maxdays, $Codeshop, $RootPath){
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
						AND locstock.loccode = '". $Codeshop ."') AS qoh,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = salesorderdetails.stkcode) AS qohtotal,
				(SELECT sum(reorderlevel)
					FROM locstock
					WHERE locstock.stockid = salesorderdetails.stkcode
						AND locstock.loccode = '". $Codeshop ."') AS rl
			FROM salesorderdetails, salesorders, stockmaster
			WHERE salesorderdetails.orderno = salesorders.orderno " . 
			$FilterCategory . 
			" AND stockmaster.discontinued = 0
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
			GROUP BY salesorderdetails.stkcode
			ORDER BY totalinvoiced DESC
			LIMIT " . ($endtopitems - 1) . ", 99999999";			
	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	if (DB_num_rows($Result) != 0){
		$i = $endtopitems;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['rl'] > 0){
				if($ShowHeader){
					$TableTitleText = 'Items NOT ' . $endtopitems . ' top sales available in ' . $Codeshop . ' shop. ';
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('Category') . '</th>
									<th class="SortedColumn">' . _('QOH Total') . '</th>
									<th class="SortedColumn">' . _('RL') . '</th>
									<th class="SortedColumn">' . _('QOH') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stkcode'] . '">' . $MyRow['stkcode'] . '</a>';
				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$MyRow['description'], 
						$MyRow['categoryid'], 
						$MyRow['qohtotal'],
						$MyRow['rl'],
						$MyRow['qoh']
						);
			}
			$i++;
		}
		if (!$ShowHeader){
			echo '</tbody>
				  </table>
				  </div>
				  </form>';
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $MessageCategory . _(' Items with stock available (but NO changing price or category) at Kantor but RL = 0 at ') . $Location;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Category') . '</th>
						<th class="SortedColumn">' . _('Description') . '</th>
						<th class="SortedColumn">' . _('QOH Kantor') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['categoryid'], 
					$MyRow['description'], 
					locale_number_format($MyRow['QtyKantor'],0)
					);
			$i++;
		}
		echo '</tbody>
			  </table>
			  </div>
			  </form>';
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('New customers registered during the last ') . $NumDays . ' days.';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Customer') . '</th>
							<th class="SortedColumn">' . _('Name') . '</th>
							<th class="SortedColumn">' . _('Country') . '</th>
							<th class="SortedColumn">' . _('Currency ') . '</th>
							<th class="SortedColumn">' . _('Registered on') . '</th>
							<th class="SortedColumn">' . _('Type') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . $MyRow['debtorno'] . '">' . $MyRow['debtorno'] . '</a>';
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
					$MyRow['name'], 
					$MyRow['address6'], 
					$MyRow['currcode'], 
					ConvertSQLDateTime($MyRow['clientsince']), 
					$MyRow['typename']				
					);
			$i++;
		}
		echo '</table>
				</div>';
	}

}

function OvestockAtShops($Kind, $RootPath){

	if($Kind == "OVERSTOCK"){			
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if($Kind == "OVERSTOCK"){			
			$TableTitleText = _('Overstock of items at shops');
			ShowTableTitle($TableTitleText);
			$TableHeader = '<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Shop') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th class="SortedColumn">' . _('Overstock') . '</th>
							</tr>';
		}else{
			$TableTitleText = _('Items needed at shops. (No overstock - No transfer)');
			ShowTableTitle($TableTitleText);
			$TableHeader = '<tr>
								<th class="SortedColumn">' . _('#') . '</th>
								<th class="SortedColumn">' . _('Shop') . '</th>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th class="SortedColumn">' . _('Need') . '</th>
							</tr>';
		}
		echo '<div>';
		echo '<table class="selection">';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$MyRow['loccode'], 
					$CodeLink, 
					$MyRow['description'], 
					locale_number_format($MyRow['qty'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PerformanceItemsInCategory($ReportType, $CategoryId, $maxdays, $Percentsales, $TextTitle, $RootPath){

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
					WHERE locstock.stockid = stockmaster.stockid)))	". $Sign . " ('" . $Percentsales ."' / 100)";

	if ($ReportType == "GOOD"){
		$SQL = $SQL . ")";
	}else{
		$SQL = $SQL . " OR ((SELECT SUM(qtyinvoiced)
								FROM salesorderdetails
								WHERE salesorderdetails.stkcode = stockmaster.stockid) IS NULL))";
	}
	$SQL = $SQL . " ORDER BY stockmaster.lastcategoryupdate ASC, stockmaster.stockid ASC";
	
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ReportType == "GOOD"){
			$TableTitleText = _('Items in category ') . $CategoryId . " for less than " . $maxdays . " days with more than " . $Percentsales . "% of sold stock (" . $TextTitle . " Items).";
		}else{
			$TableTitleText = _('Items in category ') . $CategoryId . " for more than " . $maxdays . " days with less than " . $Percentsales . "% of sold stock (" . $TextTitle . " Items).";
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('DOB Category') . '</th>
							<th class="SortedColumn">' . _('Code') . '</th>
							<th class="SortedColumn">' . _('Description') . '</th>
							<th class="SortedColumn">' . _('Total Qty') . '</th>
							<th class="SortedColumn">' . _('QOH') . '</th>
							<th class="SortedColumn">' . _('Sold Qty') . '</th>
							<th class="SortedColumn">' . _('% Sold') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$DaysInCategory = DateDiff(Date($_SESSION['DefaultDateFormat']), ConvertSQLDate($StartDate), 'd');
			if (($MyRow['sold'] + $MyRow['qoh']) != 0){
				$ActualSales = ($MyRow['sold'] / ($MyRow['sold'] + $MyRow['qoh'])) * 100;
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
					ConvertSQLDate($MyRow['lastcategoryupdate']), 
					$CodeLink, 
					$MyRow['description'], 
					locale_number_format($MyRow['qoh']  + $MyRow['sold'],0),
					locale_number_format($MyRow['qoh'],0),
					locale_number_format($MyRow['sold'],0),
					locale_number_format($ActualSales,0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PricesNotUpdatedinXDays($numDays, $PercentageIncrease, $RootPath){
	
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));

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
				AND prices.enddate >= CURRENT_DATE)
				AND stockmaster.discontinued = 0					
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
			ORDER BY stockmaster.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'Prices not updated during the last ' . $numDays . ' days. Recommended increase '. $PercentageIncrease . '%';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Code') . '</th>
							<th class="SortedColumn">' . _('Description') . '</th>
							<th class="SortedColumn">' . _('Std Cost') . '</th>
							<th class="SortedColumn">' . _('Date Price') . '</th>
							<th class="SortedColumn">' . _('Current Price') . '</th>
							<th class="SortedColumn">' . _('Recommended Price') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			$NewPrice = round_price($MyRow['price'] * (1 + $PercentageIncrease/100), "UP");
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		//	$PriceLink = '<a href="' . $RootPath . '/Prices.php?Item=' . $MyRow['stockid'] . '">' . locale_number_format($MyRow['price'],0) . '</a>';
			$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $NewPrice .  '">' . locale_number_format($NewPrice,0) . '</a>';
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
					$MyRow['description'],
					locale_number_format($MyRow['stdcost'],0),
					ConvertSQLDate($MyRow['startdate']), 
					locale_number_format($MyRow['price'],0),
					$NewPriceLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function SalesOfItemByLocation($StockID, $Location, $maxdays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT COUNT(qtyinvoiced) AS sales
			FROM salesorderdetails, salesorders
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.completed = 1
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.fromstkloc = '". $Location . "'
				AND salesorderdetails.stkcode LIKE '". $StockID . "%'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$sales = $MyRow['sales'];
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$ShowHeader = true;
		$k = 0; //row colour counter
		$i = $starttopitems;
		while ($MyRow = DB_fetch_array($Result)) {
			$SQLDistribution = "SELECT locstock.loccode, 
									locstock.reorderlevel AS oldrl
								FROM locstock,locations
								WHERE locstock.stockid = '" . $MyRow['stockid'] . "'
									AND locstock.loccode = locations.loccode
									AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
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
					if($MyDistribution['oldrl'] > $NewRL){
						SetReorderLevel("LowSalesAdjust", $MyRow['stockid'], $MyDistribution['loccode'], $MyDistribution['oldrl'], $NewRL, $updateDB);
						if ($ShowMessages){
							if($ShowHeader){
								$TableTitleText = _('Set RL Max to ') . $NewRL . ' for Low Sales '. $starttopitems . '-'. $endtopitems . ' for at least ' . $daystopitems . ' days ';
								ShowTableTitle($TableTitleText);
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
								$ShowHeader = false;
							}
							if ($k == 0) {
								echo '<tr class="EvenTableRows">';
							} else {
								echo '<tr class="OddTableRows">';
							}
							$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
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
								$MyRow['categoryid'], 
								$MyRow['description'], 
								$MyDistribution['loccode'],
								locale_number_format($MyDistribution['oldrl'],0),
								locale_number_format($NewRL,0)
								);
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
	
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('SPG with daily sales below minimum of ') . locale_number_format($MinimumSales,0) . "/day during the last " . $NumDaysA . " days in ". $Shop;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('Code') . '</th>
							<th class="SortedColumn">' . _('Name') . '</th>
							<th class="SortedColumn">' . _('Sales ') . locale_number_format($NumDaysA,0) . _(' days') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);

			$Code = $MyRow['salesmancode'];
			$Name = $MyRow['salesmanname'];
			
			$dailyA = locale_number_format(($MyRow['salesA']/$NumDaysA),0);
			
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
	$Totalcash = 0;
	$Totalcredit = 0;
	$Totalreturned = 0;
	$Total = 0;

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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('SPG with ') . $maxsplitted . _(' or more splitted payments during the last ') . $maxdays . _(' days.');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="SortedColumn">' .  _('SPG') . '</th>
							<th class="SortedColumn">' . _('Splitted') . '</th>
							<th class="SortedColumn">' . _('Amount') . '</th>
							<th class="SortedColumn">' . _('Date') . '</th>
							<th class="SortedColumn">' . _('Order') . '</th>
							<th class="SortedColumn">' . _('Yellow#') . '</th>
							<th class="SortedColumn">' . _('Cash') . '</th>
							<th class="SortedColumn">' . _('Credit Card') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
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
					$MyRow['salesperson'],
					locale_number_format($MyRow['splitted'],0),
					locale_number_format($MyRow['amount'],0),
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
							AND salesperson = '". $MyRow['salesperson']. "'
							AND klpaidcash > 0
							AND klpaidcreditcard > 0
						ORDER BY orderno";
			$Resultdetails = DB_query($SQLDetails);
			while ($MyRowdetails = DB_fetch_array($Resultdetails)) {
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
						ConvertSQLDate($MyRowdetails['orddate']),
						$MyRowdetails['orderno'],
						$MyRowdetails['customerref'],
						locale_number_format($MyRowdetails['klpaidcash'],0),
						locale_number_format($MyRowdetails['klpaidcreditcard'],0)
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
	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	if (DB_num_rows($Result) != 0){
		$i = $starttopitems;
		while ($MyRow = DB_fetch_array($Result)) {
			if (($MyRow['availableshops'] < $minshops) && ($MyRow['qoh'] > $MyRow['availableshops'])){
				if($ShowHeader){
					if ($categories == "DISC20"){
						$TableTitleText = $endtopitems . ' Top sales items 20% Discount available in less than ' . $minshops . ' shops. ';
					}		
					if ($categories == "DISC50"){
						$TableTitleText = $endtopitems . ' Top sales items 50% Discount available in less than ' . $minshops . ' shops. ';
					}		
					if ($categories == "DISC80"){
						$TableTitleText = $endtopitems . ' Top sales items 80% Discount available in less than ' . $minshops . ' shops. ';
					}		
					if ($categories == "STABLE"){
						$TableTitleText = $endtopitems . ' Top sales items NOT DISCOUNTED OR CHANGING PRICE available in less than ' . $minshops . ' shops. ';
					}		
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . _('#') . '</th>
									<th class="SortedColumn">' . _('Code') . '</th>
									<th class="SortedColumn">' . _('Description') . '</th>
									<th class="SortedColumn">' . _('Category') . '</th>
									<th class="SortedColumn">' . _('Sold ') . $maxdays . ' days' . '</th>
									<th class="SortedColumn">' . _('QOH') . '</th>
									<th class="SortedColumn">' . _('# Toko') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stkcode'] . '">' . $MyRow['stkcode'] . '</a>';
				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$MyRow['description'], 
						$MyRow['categoryid'], 
						$MyRow['totalinvoiced'], 
						$MyRow['qoh'], 
						$MyRow['availableshops'] 
						);
			}
			$i++;
		}
		if (!$ShowHeader){
			echo '</tbody>
				  </table>
				  </div>
				  </form>';
		}
	}
}

function WrongGiftItem($StockID, $customertype, $ErrorType, $OrderValue, $numDays, $RootPath){

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
		$Titletext .= _(' Orders over ') . locale_number_format($OrderValue,0). _(' without GIFT ') . $StockID . _(' during the last ') . $numDays . ' days';
		$Sign = " >= ";
		$Not = "NOT";
	}else{
		$Titletext .= _(' Orders below ') . locale_number_format($OrderValue,0). _(' with GIFT ') . $StockID . _(' during the last ') . $numDays . ' days';
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
								AND so2.stkcode LIKE '" . $StockID . "' )". 
			" ORDER BY salesorders.orderno";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($Titletext);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="SortedColumn">' . _('#') . '</th>
							<th class="SortedColumn">' . _('webERP Order') . '</th>
							<th class="SortedColumn">' . _('Yellow Order') . '</th>
							<th class="SortedColumn">' . _('Customer') . '</th>
							<th class="SortedColumn">' . _('SPG') . '</th>
							<th class="SortedColumn">' . _('Order Date') . '</th>
							<th class="SortedColumn">' . _('Total Value') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/OrderDetails.php?OrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$MyRow['customerref'], 
					$MyRow['name'], 
					$MyRow['salesmanname'], 
					ConvertSQLDate($MyRow['orddate']), 
					locale_number_format($MyRow['ordervalue'],0)
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
	$Result = DB_query_oc($SQL);

	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('DOKU Payments from OpenCart');
			ShowTableTitle($TableTitleText);
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
		while ($MyRow = DB_fetch_array($Result)) {
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
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($MyRow['customer_group_id'], $MyRow['ordercurrency']);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $MyRow['order_id']);
			$PaymentSystem = OPENCART_DOKU_PAYMENT_SYSTEM;
			$CurrencyOrder = $MyRow['ordercurrency'];
			$CurrencyPayment = $MyRow['ordercurrency'];
			$TotalOrder = round($MyRow['ordertotal'] * $MyRow['currency_value'],0); // from OC default currency to order and payment currency
			$Rate = GetWeberpCurrencyRate($CurrencyOrder);
			$AmountPaid = $MyRow['amount'];
			$TransactionID = $MyRow['trx_id'];
			$GLAccount = GetWeberpGLAccountPayPalFromCustomerGroupAndCurrency($MyRow['customer_group_id'], $CurrencyPayment);
			$GLCommissionAccount = GetWeberpGLCommissionAccountPayPalFromCustomerGroupAndCurrency($MyRow['customer_group_id'], $CurrencyPayment);
			
			$Commission = $ComissionFlatDOKU; // For each tx there is a flat comission
			if (($MyRow['payment_channel'] == "15") OR ($MyRow['payment_channel'] == "16")){
				// if it is a payment via CC there is a CC commission extra from DOKU to add to the flat commission
				$Commission += round(($AmountPaid * $ComissionCCDOKU /100),0);
			}
			
			$WebERPDateOrder = date('Y-m-d H:i:s', strtotime( $MyRow['created'] . -$TimeDifference . ' hours'));
			$FreightCost = RoundPriceFromCart(GetTotalFromOrder("shipping", $MyRow['order_id']) * $MyRow['currency_value'],$MyRow['ordercurrency']);


			if (($MyRow['ordercurrency'] == 'IDR') AND ($MyRow['result_msg'] == 'SUCCESS')) {
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
				printf('<tr class="striped_row">
						<td class="number">%s</td>
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
						$MyRow['customer_id'],
						$MyRow['email'],
						$CustomerCode,
						$MyRow['order_id'],
						$OrderNo,
						$TotalOrder,
						$MyRow['ordercurrency'],
						$AmountPaid,
						$FreightCost,
						$MyRow['ordercurrency'],
						$TransactionID,
						$MyRow['amount'],
						$MyRow['payment_channel'],
						$Commission,
						$WebERPDateOrder,
						$MyRow['process_type'],
						$MyRow['result_msg'],
						$MyRow['status_code'],
						$MyRow['approval_code']
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $MyRow['customer_id'] .
									      " = " . $MyRow['email'] .
									      " = " . $CustomerCode .
									      " = " . $MyRow['order_id'] .
									      " = " . $TotalOrder .
									      " = " . $MyRow['ordercurrency'] .
									      " = " . $AmountPaid .
									      " = " . $FreightCost .
									      " = " . $MyRow['payment_channel'] .
									      " = " . $MyRow['process_type'] .
									      " = " . $MyRow['result_msg'] .
									      " = " . $MyRow['status_code'] .
									      " = " . $MyRow['approval_code'] .
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

function WebsiteCategoryDiscount($StockID, $Description, $Long, $Category){
	$WebCat = 0;

	if (ItemInList($Category, LIST_STOCK_CATEGORIES_OUTLET)){
		$WebCat = JEWELLERY_ON_SPECIAL;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockID, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isRing($StockID)){
		$WebCat = RINGS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isEarring($StockID)){
		$WebCat = EARRINGS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isPiercing($StockID)){
		$WebCat = EARRINGS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isEarcuff($StockID)){
		$WebCat = EARCUFFS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isBracelet($StockID)){
		$WebCat = BRACELETS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isNecklace($StockID)){
		$WebCat = NECKLACES_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isPendant($StockID)){
		$WebCat = PENDANTS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isToeRing($StockID)){
		$WebCat = TOERINGS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isAnklet($StockID)){
		$WebCat = ANKLETS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isBrooche($StockID)){
		$WebCat = BROOCHES_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isKeyHolder($StockID)){
		$WebCat = KEYHOLDERS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isFaceMask($StockID)){
		$WebCat = FACEMASKS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isJewelleryRoll($StockID)){
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

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$numshops = 0;
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$numshops++;
			$TableResult[$numshops]['show'] = FALSE; // to start we don't need to show any result
			$TableResult[$numshops]['loccode'] = $MyRow['loccode'];
			$TableResult[$numshops]['locationname'] = $MyRow['locationname'];
			$TableResult[$numshops]['rlfactorforpackaging'] = $MyRow['rlfactorforpackaging'];
			$TableResult[$numshops]['klemaillastpackacgingtransfer'] = $MyRow['klemaillastpackacgingtransfer'];

			$TableResult[$numshops]['qty_box_l'] = $MyRow['qty_box_l'];
			$TableResult[$numshops]['qty_box_m'] = $MyRow['qty_box_m'];
			$TableResult[$numshops]['qty_box_s'] = $MyRow['qty_box_s'];
			$TableResult[$numshops]['qty_bag_l'] = $MyRow['qty_bag_l'];
			$TableResult[$numshops]['qty_bag_m'] = $MyRow['qty_bag_m'];
			$TableResult[$numshops]['qty_bag_s'] = $MyRow['qty_bag_s'];
			$TableResult[$numshops]['qty_shopping_l'] = $MyRow['qty_shopping_l'];
			$TableResult[$numshops]['qty_shopping_m'] = $MyRow['qty_shopping_m'];
			$TableResult[$numshops]['qty_shopping_s'] = $MyRow['qty_shopping_s'];

			$TableResult[$numshops]['ot_box_l'] = $MyRow['ot_box_l'];
			$TableResult[$numshops]['ot_box_m'] = $MyRow['ot_box_m'];
			$TableResult[$numshops]['ot_box_s'] = $MyRow['ot_box_s'];
			$TableResult[$numshops]['ot_bag_l'] = $MyRow['ot_bag_l'];
			$TableResult[$numshops]['ot_bag_m'] = $MyRow['ot_bag_m'];
			$TableResult[$numshops]['ot_bag_s'] = $MyRow['ot_bag_s'];
			$TableResult[$numshops]['ot_shopping_l'] = $MyRow['ot_shopping_l'];
			$TableResult[$numshops]['ot_shopping_m'] = $MyRow['ot_shopping_m'];
			$TableResult[$numshops]['ot_shopping_s'] = $MyRow['ot_shopping_s'];

			$TableResult[$numshops]['rl_box_l'] = $MyRow['rl_box_l'];
			$TableResult[$numshops]['rl_box_m'] = $MyRow['rl_box_m'];
			$TableResult[$numshops]['rl_box_s'] = $MyRow['rl_box_s'];
			$TableResult[$numshops]['rl_bag_l'] = $MyRow['rl_bag_l'];
			$TableResult[$numshops]['rl_bag_m'] = $MyRow['rl_bag_m'];
			$TableResult[$numshops]['rl_bag_s'] = $MyRow['rl_bag_s'];
			$TableResult[$numshops]['rl_shopping_l'] = $MyRow['rl_shopping_l'];
			$TableResult[$numshops]['rl_shopping_m'] = $MyRow['rl_shopping_m'];
			$TableResult[$numshops]['rl_shopping_s'] = $MyRow['rl_shopping_s'];
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
				if($ShowHeader){
					if ($ShopType == "KAPAL-LAUT"){
						$TableTitleText = 'KAPAL-LAUT Shops needing Packaging Transfers (Do not forget to create transfer in webERP)';
					}else{
						$TableTitleText = 'BLINK Shops needing Packaging Transfers (Do not forget to create transfer in webERP)';
					}
					ShowTableTitle($TableTitleText);
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
										<th class="SortedColumn">' . _('Shop') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Last Email') . '</th>
										<th class="SortedColumn">' . _('Action') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
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
				printf('<tr class="striped_row">
						<td>%s</td>
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
		if (!$ShowHeader){
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

	$Result = DB_query($SQL);
	$ShowHeader = TRUE;
	$numshops = 0;
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$numshops++;
			$TableResult[$numshops]['show'] = FALSE; // to start we don't need to show any result
			$TableResult[$numshops]['loccode'] = $MyRow['loccode'];
			$TableResult[$numshops]['locationname'] = $MyRow['locationname'];
			$TableResult[$numshops]['rlfactorforpackaging'] = $MyRow['rlfactorforpackaging'];
			$TableResult[$numshops]['klemaillastpackacgingtransfer'] = $MyRow['klemaillastpackacgingtransfer'];

			$TableResult[$numshops]['qty_bag_l'] = $MyRow['qty_bag_l'];
			$TableResult[$numshops]['qty_bag_m'] = $MyRow['qty_bag_m'];
			$TableResult[$numshops]['qty_bag_s'] = $MyRow['qty_bag_s'];
			$TableResult[$numshops]['qty_shopping_m'] = $MyRow['qty_shopping_m'];

			$TableResult[$numshops]['ot_bag_l'] = $MyRow['ot_bag_l'];
			$TableResult[$numshops]['ot_bag_m'] = $MyRow['ot_bag_m'];
			$TableResult[$numshops]['ot_bag_s'] = $MyRow['ot_bag_s'];
			$TableResult[$numshops]['ot_shopping_m'] = $MyRow['ot_shopping_m'];

			$TableResult[$numshops]['rl_bag_l'] = $MyRow['rl_bag_l'];
			$TableResult[$numshops]['rl_bag_m'] = $MyRow['rl_bag_m'];
			$TableResult[$numshops]['rl_bag_s'] = $MyRow['rl_bag_s'];
			$TableResult[$numshops]['rl_shopping_m'] = $MyRow['rl_shopping_m'];
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
				if($ShowHeader){
					$TableTitleText = 'OUTLET Shops needing OUTLET Packaging Transfers (Do not forget to create transfer in webERP)';
					ShowTableTitle($TableTitleText);
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
										<th class="SortedColumn">' . _('KL Shop') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Needs') . '</th>
										<th class="SortedColumn">' . _('Transit') . '</th>
										<th class="SortedColumn">' . _('To Ship') . '</th>
										<th class="SortedColumn">' . _('Last Email') . '</th>
										<th class="SortedColumn">' . _('Action') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
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
				
				printf('<tr class="striped_row">
						<td>%s</td>
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
		if (!$ShowHeader){
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
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
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
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
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
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return 0;
	}
}

function UpdateSettingValueOpenCartByCodeAndKey($Store, $Code, $Key, $Value){
	$DbgMsg = _('The SQL statement that failed was');
	$UpdateErrMsg = _('The SQL to update setting value in Opencart failed');
	$SQLUpdate = "UPDATE oc_setting
					SET	value = '" . $Value . "'
				WHERE `code` = '" . $Code . "'
					AND `key` = '" . $Key . "'
					AND `store_id` = '" . $Store . "'";

	$ResultUpdate = DB_query_oc($SQLUpdate,$UpdateErrMsg,$DbgMsg,true);
}

function MaintainWeberpOutletSalesCategories($ShowMessages, $LastTimeRun, $EmailText=''){

	/* Look for all products in weberp marked as OUTLET and "something else"*/

	$SQL = "SELECT salescatprod.stockid
			FROM salescatprod
			WHERE salescatprod.salescatid IN (" . ONLINESHOP_OUTLET_SALES_CATEGORIES . ")";
		$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Maintain webERP Outlet Sales Categories');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="SortedColumn">' . _('StockID') . '</th>
								<th class="SortedColumn">' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update outlet sales category in webERP failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$k = StartEvenOrOddRow($k);

			$ProductId = $MyRow['stockid'];

			$Action = "Delete sales categories not OUTLET";
			$SQLDelete = "DELETE FROM salescatprod
							WHERE stockid = '" . $ProductId . "'
								AND salescatid NOT IN (" . ONLINESHOP_OUTLET_SALES_CATEGORIES . ")";
			$ResultDelete = DB_query($SQLDelete,$UpdateErrMsg,$DbgMsg,true);
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Create featured list in OpenCart');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="SortedColumn">' . _('StockID') . '</th>
								<th class="SortedColumn">' . _('OpenCartID') . '</th>
								<th class="SortedColumn">' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$Action = "Added";
		$k = 0; //row colour counter
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			/* Field Matching */
			$Model = $MyRow['stockid'];

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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Sales categories');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="SortedColumn">' . _('SalesCatID') . '</th>
								<th class="SortedColumn">' . _('SalesCatName') . '</th>
								<th class="SortedColumn">' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update sales categories in Opencart failed');
		$InsertErrMsg = _('The SQL to insert sales categories in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {

			/* FIELD MATCHING */
			if ($MyRow['parentcatid'] == 0){
				$Top = 1;
			}else{
				$Top = 0;
			}

			$Column = 1;
			$Language_Id = 1; // for now NO multi language
			$SortOrder = 1;
			$Name = trim($MyRow['salescatname']);
			$Description = trim($MyRow['salescatname']);
			$MetaTitle = trim($MyRow['salescatname']);
			$MetaDescription = CreateMetaDescriptionSalesCategory('Sales category', trim($MyRow['salescatname']));
			$CategoryId = $MyRow['salescatid'];
			if (DataExistsInOpenCart('oc_category', 'category_id', $MyRow['salescatid'])){
				$Action = "Update";
				$SQLUpdate = "UPDATE oc_category
								SET parent_id 		= '" . $MyRow['parentcatid'] . "',
									status 			= '" . $MyRow['active'] . "',
									top 			= '" . $Top . "',
									date_modified 	= '" . $ServerNow . "'
								WHERE category_id 	= '" . $CategoryId . "'";
				$ResultUpdate = DB_query_oc($SQLUpdate,$UpdateErrMsg,$DbgMsg,true);

				$SQLUpdate = "UPDATE oc_category_description
								SET language_id 		= '" . $Language_Id . "',
									name	 			= '" . $Name . "',
									description			= '" . $Description . "',
									meta_title 			= '" . $MetaTitle . "',
									meta_description	= '" . $MetaDescription . "',
									meta_keyword		= '" . $MetaKeyword . "'
								WHERE category_id 	= '" . $CategoryId . "'";
				$ResultUpdate = DB_query_oc($SQLUpdate,$UpdateErrMsg,$DbgMsg,true);

			}else{
				$Action = "Insert";
				$SQLInsert = "INSERT INTO oc_category
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
								'" . $MyRow['parentcatid'] . "',
								'" . $Top . "',
								'" . $Column . "',
								'" . $SortOrder . "',
								'" . $MyRow['active'] . "',
								'" . $ServerNow . "',
								'" . $ServerNow . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert,$InsertErrMsg,$DbgMsg,true);
				$SQLInsert = "INSERT INTO oc_category_description
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
				$ResultInsert = DB_query_oc($SQLInsert,$InsertErrMsg,$DbgMsg,true);
				$SQLInsert = "INSERT INTO oc_category_to_store
								(category_id,
								store_id)
							VALUES
								('" . $CategoryId . "',
								'" . $StoreId . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert,$InsertErrMsg,$DbgMsg,true);
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
						$MyRow['salescatid'],
						$Name,
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $MyRow['salescatid'] . " = " . $Name. " --> " . $Action . "\n";
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
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Activate/Inactivate Sales Categories depending on QOH');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="SortedColumn">' . _('Sales Category') . '</th>
								<th class="SortedColumn">' . _('QOH') . '</th>
								<th class="SortedColumn">' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to Activate Categories depending QOH in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {

			/* Field Matching */
			$CategoryId = $MyRow['salescatid'];
			$CategoryName = $MyRow['salescatname'];
			$CategoryQOH = $MyRow['qoh'];
			if (isset($MyRow['qoh'])){
				if ($CategoryQOH > 0){
					$CategoryQOH = $MyRow['qoh'];
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

			$SQLUpdate = "UPDATE oc_category SET
								status = '" . $Status . "'
							WHERE category_id = '" . $CategoryId . "'";
			$ResultUpdate = DB_query_oc($SQLUpdate,$UpdateErrMsg,$DbgMsg,true);
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
		$Result = DB_query_oc($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Maintain Outlet Sales Categories');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="SortedColumn">' . _('StockID') . '</th>
								<th class="SortedColumn">' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product QOH in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {

			$ProductId = $MyRow['product_id'];
			$Model = $MyRow['model'];

			$Action = "Delete sales categories not OUTLET";
			$SQLDelete = "DELETE FROM oc_product_to_category
							WHERE product_id = '" . $ProductId . "'
								AND category_id NOT IN (" . ONLINESHOP_OUTLET_SALES_CATEGORIES . ")";
			$ResultDelete = DB_query_oc($SQLDelete,$UpdateErrMsg,$DbgMsg,true);
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

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			$TableTitleText = _('Related Items');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="SortedColumn">' . _('Item webERP') . '</th>
								<th class="SortedColumn">' . _('Related webERP') . '</th>
								<th class="SortedColumn">' . _('Item OC') . '</th>
								<th class="SortedColumn">' . _('Related OC') . '</th>
								<th class="SortedColumn">' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update related items in Opencart failed');
		$InsertErrMsg = _('The SQL to insert related items in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {

			/* FIELD MATCHING */
			$ProductId = GetOpenCartProductId($MyRow['stockid']);
			$RelatedId = GetOpenCartProductId($MyRow['related']);
			if (($ProductId != '') AND ($RelatedId != '')){
				// if both products still exist in OpenCart
				if (((isRing($ProductId)) AND (isRing($RelatedId))) == FALSE){
					// if both are rings most probably is a sizing "related", so we don't sync them
					$k = StartEvenOrOddRow($k);
					if (DataExistsInOpenCart('oc_product_related', 'product_id', $ProductId, 'related_id', $RelatedId )){
						$Action = "Update";
					}else{
						$Action = "Insert";
						$SQLInsert = "INSERT INTO oc_product_related
										(product_id,
										related_id)
									VALUES
										('" . $ProductId . "',
										'" . $RelatedId . "'
										)";
						$ResultInsert = DB_query_oc($SQLInsert,$InsertErrMsg,$DbgMsg,true);
					}
					if ($ShowMessages){
						printf('<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								</tr>',
								$MyRow['stockid'],
								$MyRow['related'],
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
	$Result = DB_query_oc($SQL);
	if (DB_num_rows($Result) != 0){
		$k = 0; //row colour counter
		$i = 0;
		$PreviousQuery = "";
		$PreviousKeyword = "";
		$ShowHeader = TRUE;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($PreviousQuery == $MyRow['query']){
				// we have a duplicated
				$DuplicatedQuery = $MyRow['query'];
				$DuplicatedKeyword = $MyRow['keyword'];

				if ($ShowHeader){
					if ($ShowMessages){
						$TableTitleText = _('Duplicated URL Alias clean up');
						ShowTableTitle($TableTitleText);
						echo '<div>';
						echo '<table class="selection">';
						$TableHeader = '<tr>
											<th class="SortedColumn">' . _('URL Alias ID') . '</th>
											<th class="SortedColumn">' . _('Query') . '</th>
											<th class="SortedColumn">' . _('Keyword') . '</th>
										</tr>';
						echo $TableHeader;
					}
					$ShowHeader = FALSE;
				}
				// we delete the duplicated
				$SQLDelete = "DELETE FROM oc_seo_url
							WHERE seo_url_id = '" .  $MyRow['seo_url_id'] . "'";
				$ResultDelete = DB_query_oc($SQLDelete,$UpdateErrMsg,$DbgMsg,true);

				// we set it up as a redirect just in case someome uses this old URL keyword
				if ($PreviousKeyword != $MyRow['keyword']){
					$Active = 1;
					$FromURL = PATH_OPENCART_BASE . '/'. $MyRow['keyword'];
					$ToURL = PATH_OPENCART_BASE . '/' . ROUTE_TO_PRODUCT . $MyRow['query'];
					$ResponseCode = REDIRECT_RESPONSE_CODE;
					$FromDate = date('Y-m-d');
					$TimesUsed = 0;
					$SQLInsert = "INSERT INTO oc_redirect
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
					$ResultInsert = DB_query_oc($SQLInsert,$UpdateErrMsg,$DbgMsg,true);
				}

				if ($ShowMessages){
					$k = StartEvenOrOddRow($k);
					printf('<td class="number">%s</td>
							<td>%s</td>
							<td>%s</td>
							</tr>',
							locale_number_format($MyRow['seo_url_id'],0),
							$MyRow['query'],
							$MyRow['keyword']
							);
				}
				if ($EmailText !=''){
					$EmailText = $EmailText . locale_number_format($MyRow['seo_url_id'],0) . " --> " . $MyRow['query'] . " --> ". $MyRow['keyword'] . "\n";
				}
				$i++;
			}
			$PreviousQuery = $MyRow['query'];
			$PreviousKeyword = $MyRow['keyword'];
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
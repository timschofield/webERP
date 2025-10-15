<?php

/********************************************************************************************
* FUNCTIONS INCLUDED IN THIS FILE (in alphabetical order)
*
* ActiveItemsNoSales - Lists items with no sales in last X days and no current PO or WO
* ActiveItemsWithoutPicture - Lists active items without pictures in webERP and QOH > 0
* ActiveTransferStatus - Shows active transfer status summary
* BalanceAccountControl - Checks account balance against min/max limits 
* BalanceListAccountControl - Checks total balance of a list of accounts against min/max limits
* CalculateTransferFromBankToDanamon - Calculates transfer amount from bank to Danamon account
* CashAtShops - Checks cash balances at retail shops
* CategoryItemsMissingInShops - Lists category items missing in shops
* CategoryItemsNotInShop - Lists items in a category not available in a specific shop
* CheckNegativeStock - Checks for negative stock quantities
* CheckPackagingToBeRefilled - Checks packaging that needs to be refilled
* ComponentsToObsolete - Lists components that could be obsoleted
* ConsumablesGoodsNotEnoughStock - Lists consumable goods with insufficient stock
* CustomersDebtControl - Controls customer debt balances
* DiscountedItemsWithWrongDiscount - Lists discounted items with wrong discount percentage
* ErrorsInTransfers - Lists errors in transfers
* FlaggedAsObsoleteButStockAvailable - Lists obsolete items that still have stock
* GLTransDateControl - Checks for GL transactions with wrong dates
* GoodsJustArrived - Lists goods just arrived at a location
* GoodsJustTransferred - Lists goods just transferred between locations
* GoodsReceivedNotInvoicedControl - Controls goods received not invoiced
* GoodsToBeProduced - Lists goods that need to be produced
* ImagesWithoutProduct - Lists images without corresponding products
* InsuficientStockForShopPackaging - Lists shop packaging with insufficient stock
* InternalBankTransfers - Manages internal bank transfers between accounts
* ItemsCancelledInTransfers - Lists items cancelled in transfers
* ItemsChangingPriceDelayed - Lists items with delayed price changes
* ItemsInCategoryForMoreThanDays - Lists items in a category for more than X days
* ItemsInKLProcessAndRLNotZero - Lists items in KL process with reorder level not zero
* ItemsInLocationForMoreThan - Lists items in a location for more than X days
* ItemsInmediateShortage - Lists items with immediate shortage
* ItemsInSetup - Lists items in setup phase
* ItemsInWrongShops - Lists items in wrong shop locations
* ItemsMovingToDiscountDelayed - Lists items with delayed discount moves
* ItemsNotNeededInOnlineOrderButRequested - Lists items not needed for online orders but with stock in online shop
* ItemsOnSpecialRequest - Lists items on special request
* ItemsWithoutPurchasingData - Lists items without purchasing data
* ItemsWithoutStandardCost - Lists items without standard cost
* ItemsWithoutWeightOrVolume - Lists items without weight or volume data
* ItemsWithStockKantorButReorderLevelTokoZero - Lists items with stock at Kantor but zero reorder level at shops
* ItemsWithStockLocationButNoStockAvailable - Lists items with locations but no available stock
* ItemsWithWrongNumberOfPreferredSuppliers - Lists items with wrong number of preferred suppliers
* ItemsShouldBeInWebsite - Lists items that should be in the website
* MinimumOutletStockAvailable - Checks minimum outlet stock availability
* NotDiscountedItemsWithDiscount - Lists non-discounted items with discount
* ObsoleteComponentsInActiveBOM - Lists obsolete components in active BOMs
* OldOnlineQuotations - Lists old online quotations
* OldPOStillActive - Lists old purchase orders still active
* OldWOStillActive - Lists old work orders still active
* OnlineItemsOnProcess - Lists online items in process
* OnlineMarketPlacePaymentPending - Lists online marketplace payments pending
* OnlineOrdersFollowUp - Follow up on outstanding online orders
* OnlineQuotationsFollowUp - Follow up on outstanding online quotations
* OpenCartItemsWithoutPicture - Lists OpenCart items without pictures
* OpenCartOrdersByStatus - Lists OpenCart orders by status
* OutstandingOrders - Lists outstanding orders
* over_or_below_limit - Checks if a value is over or below a limit
* OvestockAtSamples - Lists overstock at samples location
* PackagingItemsOnWrongLocation - Lists packaging items in wrong locations
* PettyCashBalance - Checks petty cash balance
* PettyCashBalanceControl - Controls petty cash balance accounts
* PettyCashToBeAuthorized - Lists petty cash to be authorized
* POStatusControl - Controls purchase order status
* PurchaseOrdersWrongPlannedDates - Lists purchase orders with wrong planned dates
* RecentlyClosedTransferStatus - Lists recently closed transfers
* RegularTransfersToShopNotReceived - Lists regular transfers to shop not received
* SamplesNotLongerNeeded - Lists samples not longer needed
* SPGNotReportingSalesInDays - Lists SPG not reporting sales in days
* SuppliersWithoutBasicData - Lists suppliers without basic data
* TransferWithWrongInformation - Lists transfers with wrong information
* TransfersDelayed - Lists delayed transfers
* UsersNotLoggingIn - Lists users not logging in 
* ValueStockLocation - Checks value of stock in location
* WrongItemsOnPurchaseOrders - Lists wrong items on purchase orders
* WrongItemsOnWorkOrders - Lists wrong items on work orders
* WrongStandardCost - Lists items with wrong standard cost
*********************************************************************************************/

/********************************************************************************************
FUNCTIONS ONLY USED IN CONTROL BOARD
*********************************************************************************************/

function ActiveItemsNoSales($maxdays, $group, $RootPath){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));

// This line goes in WHERE quantity if (Service Excluded) 
//							AND locstock.loccode NOT IN ('SERSU','SERSV','SERVI')) AS quantity
	
	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.lastcategoryupdate,
					stockmaster.units, 
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid) AS quantity,
					topsales30,
					topsales60,
					topsales90
			FROM 	stockmaster, stockcategory, klsalesperformance
			WHERE 	stockmaster.stockid = klsalesperformance.stockid
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockmaster.discontinued = 0 
					AND stockmaster.klchangingprice = 0
					AND stockmaster.klmovingdiscount20 = 0
					AND stockmaster.klmovingdiscount50 = 0
					AND stockmaster.klmovingdiscount80 = 0
					AND stockmaster.lastcategoryupdate <= '" . $FromDate . "'
					AND stockmaster.categoryid ='" . $group . "'
					AND stockcategory.stocktype = 'F'
					AND NOT EXISTS (SELECT * 
									FROM 	salesorderdetails, salesorders
									WHERE 	stockmaster.stockid = salesorderdetails.stkcode
											AND (salesorderdetails.orderno = salesorders.orderno)
											AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
					AND (IFNULL((SELECT SUM(woitems.qtyreqd -woitems.qtyrecd) 
							FROM woitems, workorders
							WHERE woitems.stockid = stockmaster.stockid
								AND woitems.wo = workorders.wo
								AND workorders.closed = 0) ,0) = 0 )
					AND NOT EXISTS (SELECT * 
									FROM 	stockmoves
									WHERE 	stockmoves.stockid = stockmaster.stockid
											AND stockmoves.trandate >= '" . $FromDate . "')
					AND EXISTS (SELECT * 
								FROM 	stockmoves
								WHERE 	stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate < '" . $FromDate . "'
										AND stockmoves.qty > 0) 
					AND NOT EXISTS (SELECT * 
									FROM 	purchorderdetails
									WHERE 	purchorderdetails.itemcode = stockmaster.stockid
											AND purchorderdetails.completed = 0)
			GROUP BY stockmaster.stockid
			ORDER BY stockmaster.stockid";
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = GetCategoryNameFromCode($group) . __(' Items with NO sales on last ') . $maxdays . ' days and NO current PO or WO. Move to next category step';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Category') . '</th>
						<th class="SortedColumn">' . __('DOB Category') . '</th>
						<th class="SortedColumn">' . __('QOH') . '</th>
						<th class="SortedColumn">' . __('#Top Sales 30') . '</th>
						<th class="SortedColumn">' . __('#Top Sales 60') . '</th>
						<th class="SortedColumn">' . __('#Top Sales 90') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['categoryid'] . '</td>
					<td>' . ConvertSQLDate($MyRow['lastcategoryupdate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['quantity'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['topsales30'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['topsales60'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['topsales90'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}	

function ActiveItemsWithoutPicture($RootPath){
/* EXPLAIN SQL 2014-05-21	Can't use key. Probably explained at http://stackoverflow.com/questions/11784322/why-would-mysql-not-use-keys-when-there-are-possible-keys 
2014-05-30 Fixed adding a new index disontinued+Stockid
2015-05-19 TAke out some exceptions 
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "

*/
	$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockcategory.categorydescription,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) AS qoh
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockmaster.discontinued = 0
			AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SETUP . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_CONSIGNMENT . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_COMPONENTS . "
				)
			AND (SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) > 0
		ORDER BY stockcategory.categorydescription, stockmaster.stockid";
	$Result = DB_query($SQL);
	$ShowHeader = true;

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if(!file_exists($_SESSION['part_pics_dir'] . '/' .$MyRow['stockid'].'.jpg') ) {
				if($ShowHeader){
					$TableTitleText = __('Current Items without picture in webERP and QOH > 0');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . '#' . '</th>
									<th class="SortedColumn">' . __('Category') . '</th>
									<th class="SortedColumn">' . __('Item Code') . '</th>
									<th class="SortedColumn">' . __('Description') . '</th>
									<th class="SortedColumn">' . __('QOH') . '</th>
								</tr>
							</thead>
							<tbody>';
					$i = 1;
					$ShowHeader = false;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $MyRow['categorydescription'] . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($MyRow['qoh'],0) . '</td>
						</tr>';
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function BalanceAccountControl($account, $min, $max, $Period){
	$SQL = "SELECT SUM(gltotals.amount) as saldo, accountname
			FROM gltotals, chartmaster
			WHERE gltotals.account = chartmaster.accountcode
				AND gltotals.account = '" . $account . "'
				AND gltotals.period <= ". $Period . "
			GROUP BY chartmaster.accountname";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	if ($MyRow['saldo'] < $min){
		$WarningTitleText = "Account " . $account . " - " . $MyRow['accountname'] . " is BELOW the minimum. Balance = " . locale_number_format($MyRow['saldo'],0) . " Minimum = " . locale_number_format($min,0);
        ShowWarningTitle($WarningTitleText);
	}
	if ($MyRow['saldo'] > $max){
		$WarningTitleText = "Account " . $account . " - " . $MyRow['accountname'] . " is OVER the maximum. Balance = " . locale_number_format($MyRow['saldo'],0) . " Maximum = " . locale_number_format($max,0);
        ShowWarningTitle($WarningTitleText);
	}
}

function BalanceListAccountControl($accountlist, $Description, $min, $max, $Period){
	$SQL = "SELECT SUM(gltotals.amount) as saldo
			FROM gltotals
			WHERE gltotals.account IN " . $accountlist . "
				AND gltotals.period <= ". $Period . "";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	if ($MyRow['saldo'] < $min){
		$WarningTitleText = $Description . " is BELOW the minimum. Balance = " . locale_number_format($MyRow['saldo'],0) . " Minimum = " . locale_number_format($min,0);
        ShowWarningTitle($WarningTitleText);
	}
	if ($MyRow['saldo'] > $max){
		$WarningTitleText = $Description . " is OVER the maximum. Balance = " . locale_number_format($MyRow['saldo'],0) . " Maximum = " . locale_number_format($max,0);
        ShowWarningTitle($WarningTitleText);
	}
}

function CashAtShops($MinCashPerShop, $MaxCashPerShop, $MinCashAllShops, $MaxCashAllShops, $NumberOfTestExecuted, $PeriodNow){
	// while builing the list of KL POS accounts for all shops, we check one by one
	$ListAccounts = "('";
	$SQL="SELECT klposcashaccount
		FROM locations
		WHERE  locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . " 
		ORDER BY locations.locationname"; 
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)){
		$ListAccounts = $ListAccounts . $MyRow['klposcashaccount'] . "','";
		BalanceAccountControl($MyRow['klposcashaccount'], $MinCashPerShop,$MaxCashPerShop, $PeriodNow);
		$NumberOfTestExecuted++;
	}
	$ListAccounts = substr($ListAccounts, 0, -2) . ")";
	// Once we have the list of all KL POS accounts for all shops, we check the total in the system
	BalanceListAccountControl($ListAccounts, "Total Cash @ shops", $MinCashAllShops, $MaxCashAllShops, $PeriodNow);
	$NumberOfTestExecuted++;
	return $NumberOfTestExecuted;
}


function CategoryItemsMissingInShops($Category, $ShopType, $NumberOfTestExecuted, $RootPath){

	if (ItemInList($Category, LIST_STOCK_CATEGORIES_TEST)){
		$Condition = " AND locations.alltestitems = '2' ";
	}elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_STABLE)){
		$Condition = " AND locations.allstableitems = '2' ";
	}elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)){
		$Condition = " AND locations.allnopoitems = '2' ";
	}elseif (ItemInLIst($Category, LIST_STOCK_CATEGORIES_DISCOUNT_20)){
		$Condition = " AND locations.alldisc20items = '2' ";
	}elseif (ItemInLIst($Category, LIST_STOCK_CATEGORIES_DISCOUNT_50)){
		$Condition = " AND locations.alldisc50items = '2' ";
	}elseif (ItemInLIst($Category, LIST_STOCK_CATEGORIES_DISCOUNT_80)){
		$Condition = " AND locations.alldisc80items = '2' ";
	}
	
	$SQL="SELECT loccode
		FROM locations
		WHERE typeloc = '" . $ShopType . "'" 
		. $Condition;
	$Result = DB_query($SQL);
	$MinQOH = DB_num_rows($Result);
	while ($MyRow = DB_fetch_array($Result)){
		CategoryItemsNotInShop($Category, $MyRow['loccode'], $MinQOH, "ALL", $RootPath);
		$NumberOfTestExecuted++;
		if (!ItemInLIst($Category, LIST_STOCK_CATEGORIES_OUTLET)){
			CategoryItemsNotInShop($Category, $MyRow['loccode'], 1, "KANTOR", $RootPath);
			$NumberOfTestExecuted++;
		}
	}
	return $NumberOfTestExecuted;
	
}



function CategoryItemsNotInShop($Category, $Shop, $MinQOH, $WhereisQOH, $RootPath){
	
	$Exclusions = " (excluding items in Active Transfers, Pending of Transfer, Change of Price, Move to Discount, Special Kantor Request, Service, Shop Online and Return to Supplier)";
	if ($WhereisQOH == "KANTOR"){
		$TableTitleText = GetCategoryNameFromCode($Category) . __(' items NOT in ') . $Shop . ' but with QOH >= ' . $MinQOH .' in KANTOR' . $Exclusions;
		$SQLQty = "(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode IN " . LIST_KANTOR . ")";
		$TitleQOH = "QOH Kantor";
	}else{
		$TableTitleText = GetCategoryNameFromCode($Category) . __(' items NOT in ') . $Shop . ' with QOH >= ' . $MinQOH .' in TOTAL' . $Exclusions;
		$SQLQty = "(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SPECIAL_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_UNIFORM_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SAMPLE_LOCATIONS . ")";
		$TitleQOH = "QOH Available";
	}

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode, " . 
					$SQLQty . " AS qoh,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.categoryid = '" . $Category . "'
				AND stockmaster.discontinued = 0
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND locstock.loccode = '" . $Shop . "'
				AND locstock.quantity = 0 
				AND locstock.reorderlevel = 0
				AND ((SELECT l.reorderlevel
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode = 'KASPE') = 0)
				AND ( " . $SQLQty . " >= ". $MinQOH .")
				AND ((SELECT SUM(l.reorderlevel)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode IN " . LIST_ONLINE_SHOPS . ") = 0)
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  pendingqty > 0
							AND loctransfers.stockid =  stockmaster.stockid)
				AND NOT EXISTS (SELECT *
						FROM locstock l
						WHERE  l.stockid = stockmaster.stockid
							AND l.reorderlevel > 0
							AND l.quantity =  0)
			ORDER BY stockmaster.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . $TitleQOH . '</th>
						<th class="SortedColumn">' . __('RL=?') . '</th>
						<th class="SortedColumn">' . __('RL=1') . '</th>
						<th class="SortedColumn">' . __('RL=2') . '</th>
						<th class="SortedColumn">' . __('RL=3') . '</th>
						<th class="SortedColumn">' . __('RL=4') . '</th>
						<th class="SortedColumn">' . __('RL=5') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . 'Manual' . '</a>';
			$LinkRL1 = '';
			$LinkRL2 = '';
			$LinkRL3 = '';
			$LinkRL4 = '';
			$LinkRL5 = '';
			if (!ItemInLIst($Category, LIST_STOCK_CATEGORIES_OUTLET)){
				if ($MyRow['qoh'] >= 1){
					$LinkRL1  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=1' . '">' . '1' . '</a>';
				}
				if ($MyRow['qoh'] >= 2){
					$LinkRL2  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=2' . '">' . '2' . '</a>';
				}
				if ($MyRow['qoh'] >= 3){
					$LinkRL3  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=3' . '">' . '3' . '</a>';
				}
				if ($MyRow['qoh'] >= 4){
					$LinkRL4  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=4' . '">' . '4' . '</a>';
				}
				if ($MyRow['qoh'] >= 5){
					$LinkRL5  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&LocCode=' . $Shop . '&RL=5' . '">' . '5' . '</a>';
				}
			}

			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . $MyRow['qoh'] . '</td>
					<td>' . $ManualLink . '</td>
					<td class="number">' . $LinkRL1 . '</td>
					<td class="number">' . $LinkRL2 . '</td>
					<td class="number">' . $LinkRL3 . '</td>
					<td class="number">' . $LinkRL4 . '</td>
					<td class="number">' . $LinkRL5 . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function CheckNegativeStock($RootPath){
	/* Check if there is any negative stock */

	$Total = 0;
	$SQL = "SELECT stockmaster.stockid,			
				   stockmaster.description,			
				   stockmaster.decimalplaces,			
				   locations.locationname,			
				   locstock.quantity			
			FROM stockmaster INNER JOIN locstock 			
			ON stockmaster.stockid=locstock.stockid			
			INNER JOIN locations 			
			ON locstock.loccode = locations.loccode			
			WHERE locstock.quantity < 0			
			ORDER BY stockmaster.stockid";
				
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items with Negative Stock');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Location') . '</th>
						<th class="SortedColumn">' . __('Quantity') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$Total += $MyRow['quantity'];
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['locationname'] . '</td>
					<td class="number">' . locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			<tfooter>';
		echo '<tr class="striped_row">
				<td class="number"></td>
				<td>TOTAL</td>
				<td></td>
				<td></td>
				<td class="number">' . locale_number_format($Total,0) . '</td>
				</tr>';
		echo '</tfooter>
			</table>
			</div>';
	}
	InsertKPI("STOCK-NEG-PCS", abs($Total));
}

function ConsumablesGoodsNotEnoughStock($DaysUsage, $DaysMinStock, $DaysStockPurchase, $RootPath){
/* EXPLAIN SQL 2014-05-40 added index discontinued+categoryid*/
	/*  Check if there are consumable goods with not enough stock for the following $DaysMinStock
		based on last $DaysUsage usage*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysUsage));
	$FactorStock = $DaysMinStock / $DaysUsage;

	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.eoq,
				stockmaster.pansize,
				(SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode =  " . CODE_KANTOR . ") AS qtyKANTOR,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
						AND stockrequestitems.stockid = stockmaster.stockid
						AND stockrequest.despatchdate >= '" . $StartDate . "') AS usageKL
		FROM stockmaster
		WHERE stockmaster.categoryid IN('SHCONS')
			AND stockmaster.discontinued = 0 
			AND ((SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = " . CODE_KANTOR . ") < 
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
						AND stockrequestitems.stockid = stockmaster.stockid
						AND stockrequest.despatchdate >= '" . $StartDate . "') * ". $FactorStock .")
			AND NOT EXISTS (SELECT * 
					FROM 	purchorderdetails
					WHERE 	purchorderdetails.itemcode = stockmaster.stockid
							AND purchorderdetails.completed = 0)
		ORDER BY stockmaster.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Consumables with stock ready for less than ') . $DaysMinStock . ' days and NO active PO.';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('QOH Kantor') . '</th>
						<th class="SortedColumn">' . __('Used ') . $DaysUsage . ' days'. '</th>
						<th class="SortedColumn">' . __('Urgent Needed') . '</th>
						<th class="SortedColumn">' . __('Recommended Purchase') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$Needed = (($MyRow['usageKL'] / $DaysUsage) * $DaysMinStock ) - $MyRow['qtyKANTOR'];
			$Recommended = OptimumOrderQuantity((($MyRow['usageKL'] / $DaysUsage) * $DaysStockPurchase), $MyRow['eoq'], $MyRow['pansize']);
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyKANTOR'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['usageKL'],0) . '</td>
					<td class="number">' . locale_number_format($Needed,0) . '</td>
					<td class="number">' . locale_number_format($Recommended,0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function CustomersDebtControl($AcceptedDifference, $Period){

	$ValueAtBalance = GetGLAccountBalance('111311100AD', $Period);

	/* Now get the Customer debt by currency, converted to functional currency IDR */
	/* 2025-08-25 SQL optimized by Gemini */
    $SQL = "SELECT
                SUM(CASE WHEN debtorsmaster.currcode = 'IDR' THEN debtortrans.balance / currencies.rate ELSE 0 END) AS DebtValueIDR,
                SUM(CASE WHEN debtorsmaster.currcode = 'USD' THEN debtortrans.balance / currencies.rate ELSE 0 END) AS DebtValueUSD,
                SUM(CASE WHEN debtorsmaster.currcode = 'AUD' THEN debtortrans.balance / currencies.rate ELSE 0 END) AS DebtValueAUD,
                SUM(CASE WHEN debtorsmaster.currcode = 'EUR' THEN debtortrans.balance / currencies.rate ELSE 0 END) AS DebtValueEUR
            FROM debtorsmaster
            INNER JOIN debtortrans ON debtorsmaster.debtorno = debtortrans.debtorno
            INNER JOIN currencies ON debtorsmaster.currcode = currencies.currabrev
            WHERE debtorsmaster.currcode IN ('IDR', 'USD', 'AUD', 'EUR')";

    $Result = DB_query($SQL);
    $MyRow = DB_fetch_array($Result);

    $DebtValue = $MyRow['DebtValueIDR'] + $MyRow['DebtValueUSD'] + $MyRow['DebtValueAUD'] + $MyRow['DebtValueEUR'];
	
	if (abs($ValueAtBalance - $DebtValue) > $AcceptedDifference){
		$WarningTitleText = "Customer's Debt Balance value = " . locale_number_format($ValueAtBalance,0) . 
				" <-> Customer's Debt = " . locale_number_format($DebtValue,0) . 
				" Difference = ". locale_number_format($ValueAtBalance - $DebtValue,0);
        ShowWarningTitle($WarningTitleText);
	}
}

function DiscountedItemsWithWrongDiscount($Category, $DiscountCode, $RootPath){
	$SQL = "SELECT * 
			FROM  stockmaster 
			WHERE categoryid = '" . $Category . "'
				AND discountcategory !=  '". $DiscountCode ."'
				AND discontinued = 0";
				
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Category . __(' items with wrong discount (Not ') . $DiscountCode. '%)';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function FlaggedAsObsoleteButStockAvailable($RootPath){
	/* Check if there is any item flagged as obsolete BUT with some stock available */
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description
			FROM stockmaster
			WHERE discontinued = 1 
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) > 0";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Obsolete Items with available Stock');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function GLTransDateControl(){
	$SQL = "SELECT counterindex,
					type,
					typeno,
					account,
					narrative,
					amount
			FROM gltrans
			WHERE trandate = '1000-01-01'";
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Wrong dated GLTrans transactions in DB');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('Counterindex') . '</th>
						<th class="SortedColumn">' . __('Type') . '</th>
						<th class="SortedColumn">' . __('Typeno') . '</th>
						<th class="SortedColumn">' . __('Account') . '</th>
						<th class="SortedColumn">' . __('Narrative') . '</th>
						<th class="SortedColumn">' . __('Amount') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td class="number">' . $MyRow['counterindex'] . '</td>
					<td class="number">' . $MyRow['type'] . '</td>
					<td class="number">' . $MyRow['typeno'] . '</td>
					<td>' . $MyRow['account'] . '</td>
					<td>' . $MyRow['narrative'] . '</td>
					<td class="number">' . $MyRow['amount'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function GoodsJustArrived($Kind, $Location, $numdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays));
	$ShopsKL = NumberOfShops("SHOPKL");
	$ShopsBL = NumberOfShops("SHOPBL");
	$ShopsOU = NumberOfShops("SHOPOU");
	if ($Kind == "PO"){
		$Type = 25;
	}elseif ($Kind == "WO"){
		$Type = 26;
	}
	$SQL = "SELECT stockmoves.stockid, 
					stockmaster.description,
					stockmaster.categoryid,
					stockmoves.trandate, 
					stockmoves.qty AS qtyarrived,
					(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . "
							AND l.loccode NOT IN " . LIST_SAMPLE_LOCATIONS . ") AS qtytotal
			FROM stockmoves, stockmaster, stockcategory
			WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockcategory.stocktype = 'F'
				AND stockmoves.loccode ='" . $Location . "'
				AND stockmoves.type ='" . $Type . "'
				AND stockmoves.trandate >'" . $StartDate . "'
				ORDER BY stockmoves.trandate DESC, 
						stockmoves.stockid";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($Kind == "PO"){
			$TableTitleText = $Kind . __(' Finished Goods just arrived at ') . $Location . ' during the last '. $numdays . ' days';
		}elseif ($Kind == "WO"){
			$TableTitleText = $Kind . __(' Goods just produced at ') . $Location . ' during the last '. $numdays . ' days';
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th>' . __('#') . '</th>
							<th>' . __('Date') . '</th>
							<th>' . __('Code') . '</th>
							<th>' . __('Category') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Received') . '</th>
							<th>' . __('QOH') . '</th>
							<th>' . __('RL=?') . '</th>
							<th colspan="2">' . __('RL=1') . '</th>
							<th colspan="2">' . __('RL=2') . '</th>
							<th colspan="2">' . __('RL=3') . '</th>
							<th colspan="2">' . __('RL=4') . '</th>
							<th colspan="2">' . __('RL=5') . '</th>
						</tr>
						<tr>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th class="SortedColumn"></th>
							<th></th>
							<th></th>
							<th>' . __('All') . '</th>
							<th>' . __('Some') . '</th>
							<th>' . __('All') . '</th>
							<th>' . __('Some') . '</th>
							<th>' . __('All') . '</th>
							<th>' . __('Some') . '</th>
							<th>' . __('All') . '</th>
							<th>' . __('Some') . '</th>
							<th>' . __('All') . '</th>
							<th>' . __('Some') . '</th>
						</tr>
						</thead>
						<tbody>';
		echo $TableHeader;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			
			// count how many shops do we need to set the RL
			if (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_KAPAL_LAUT)){
				$TypeOfShop = 'SHOPKL';
				$ShopsToSetRL = $ShopsKL;
			}elseif (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_BLINK)){
				$TypeOfShop = 'SHOPBL';
				$ShopsToSetRL = $ShopsBL;
			}elseif (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
				$TypeOfShop = 'SHOPOU';
				$ShopsToSetRL = $ShopsOU;
			}else{
				$ShopsToSetRL = 0;
			}

			if ((ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_TEST)) 
				OR (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_STABLE))
				OR (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING))
				OR (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET))) {
				$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . 'Manual' . '</a>';
			}else{
				$ManualLink = '';
			}

			// set the links to nil, and just set some if we have enough QOH
			$LinkRL1All = '';
			$LinkRL1Some = '';
			$LinkRL2All = '';
			$LinkRL2Some = '';
			$LinkRL3All = '';
			$LinkRL3Some = '';
			$LinkRL4All = '';
			$LinkRL4Some = '';
			$LinkRL5All = '';
			$LinkRL5Some = '';

			if($ShopsToSetRL != 0){
				if ($MyRow['qtytotal'] >= 0){
					$LinkRL1All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=1' . '">' . '1' . '</a>';
					$LinkRL1Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=1' . '">' . '1' . '</a>';
				}
				if ($MyRow['qtytotal'] >= $ShopsToSetRL * 2){
					$LinkRL2All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=2' . '">' . '2' . '</a>';
					$LinkRL2Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=2' . '">' . '2' . '</a>';
				}
				if ($MyRow['qtytotal'] >= $ShopsToSetRL * 3){
					$LinkRL3All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=3' . '">' . '3' . '</a>';
					$LinkRL3Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=3' . '">' . '3' . '</a>';
				}
				if ($MyRow['qtytotal'] >= $ShopsToSetRL * 4){
					$LinkRL4All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=4' . '">' . '4' . '</a>';
					$LinkRL4Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=4' . '">' . '4' . '</a>';
				}
				if ($MyRow['qtytotal'] >= $ShopsToSetRL * 5){
					$LinkRL5All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=5' . '">' . '5' . '</a>';
					$LinkRL5Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=5' . '">' . '5' . '</a>';
				}
			}

			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['categoryid'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyarrived'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtytotal'],0) . '</td>
					<td>' . $ManualLink . '</td>
					<td>' . $LinkRL1All . '</td>
					<td>' . $LinkRL1Some . '</td>
					<td>' . $LinkRL2All . '</td>
					<td>' . $LinkRL2Some . '</td>
					<td>' . $LinkRL3All . '</td>
					<td>' . $LinkRL3Some . '</td>
					<td>' . $LinkRL4All . '</td>
					<td>' . $LinkRL4Some . '</td>
					<td>' . $LinkRL5All . '</td>
					<td>' . $LinkRL5Some . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function GoodsJustTransferred($Locationfrom, $Locationto, $numdays, $QOHmax, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays+1));

	$SQL = "SELECT loctransfers.stockid,
					stockmaster.description,
					stockcategory.categorydescription,
					loctransfers.recdate, 
					loctransfers.recqty AS qtytransferred,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = loctransfers.stockid) AS qtytotal
			FROM loctransfers, stockmaster, stockcategory
			WHERE loctransfers.stockid = stockmaster.stockid
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype = 'F'
				AND loctransfers.shiploc ='" . $Locationfrom . "'
				AND loctransfers.recloc ='" . $Locationto . "'
				AND loctransfers.recdate >'" . $StartDate . "'
				AND (SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = loctransfers.stockid) <= " . $QOHmax . "
				ORDER BY loctransfers.recdate DESC, 
						loctransfers.stockid";
						
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __(' Finished Goods just transferred from ') . $Locationfrom  . ' to '. $Locationto . ' during the last '. $numdays . ' days and QOH <= '. $QOHmax . '.';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Date') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Category') . '</th>
						<th class="SortedColumn">' . __('Transferred') . '</th>
						<th class="SortedColumn">' . __('QOH') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . ConvertSQLDate($MyRow['recdate']) . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['categorydescription'] . '</td>
					<td class="number">' . locale_number_format($MyRow['qtytransferred'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtytotal'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function GoodsReceivedNotInvoicedControl($AcceptedDifference, $Period){

	$ValueAtBalance = -GetGLAccountBalance('211021400AD', $Period);

	$SQL = "SELECT SUM((grns.qtyrecd - grns.quantityinv) * (stockmaster.actualcost))
			FROM grns, stockmaster
			WHERE stockmaster.stockid = grns.itemcode
				AND (grns.qtyrecd - grns.quantityinv) > 0";
// EXPLAIN SQL 2014-05-31
// NOT OK. All 10.000 rows each time
// prnMsg($SQL);	
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$GoodsValue = $MyRow[0];

	if (abs($ValueAtBalance - $GoodsValue) > $AcceptedDifference){
		$WarningTitleText = "Goods Received Balance value = " . locale_number_format($ValueAtBalance,0) . 
				" <-> Real Goods Received Value at Std Cost = " . locale_number_format($GoodsValue,0) .
				" Difference = ". locale_number_format($ValueAtBalance - $GoodsValue,0);;
        ShowWarningTitle($WarningTitleText);
	}
}

function PettyCashBalanceControl($Currency, $PCGLAccounts, $AcceptedDifference, $Period){
	$SQL = "SELECT SUM(pcashdetails.amount)/currencies.rate as amount_idr
			FROM pcashdetails,pctabs,currencies	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pctabs.currency = currencies.currabrev
				AND pctabs.currency = '". $Currency ."'
				AND pcashdetails.authorized != '1000-01-01'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$PettyCashValue = $MyRow['amount_idr'];

	$SQL = "SELECT SUM(amount) as saldo
			FROM gltotals
			WHERE gltotals.account IN ".$PCGLAccounts."
				AND gltotals.period <= ". $Period . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ValueAtBalance = $MyRow['saldo'];

	if (abs($ValueAtBalance - $PettyCashValue) > $AcceptedDifference){
		$WarningTitleText = "Petty Cash (" . $Currency . ") Balance value = " . locale_number_format($ValueAtBalance,0) . 
				" <-> Real Petty Cash (" . $Currency . ") = " . locale_number_format($PettyCashValue,0) . 
				" Difference = ". locale_number_format($ValueAtBalance - $PettyCashValue,0);
        ShowWarningTitle($WarningTitleText);
	}
}


function ImagesWithoutProduct($RootPath){
	$ShowHeader = true;
	$i= 0;
	// get all images in part_pics folder
	$suffix = ".jpg";
	$ImageFiles = getDirectoryTree($_SESSION['part_pics_dir']);
	foreach ($ImageFiles as $File) {
		if ($File != '.ftpquota' AND
			$File != 'Obsolete' AND
			$File != 'part_pics'){
			$StockID = substr($File, 0, strpos($File, $suffix));
			if (strpos($StockID, '.1') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.1'));
			}
			if (strpos($StockID, '.2') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.2'));
			}
			if (strpos($StockID, '.3') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.3'));
			}
			if (strpos($StockID, '.4') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.4'));
			}
			if (strpos($StockID, '.5') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.5'));
			}
			if (strpos($StockID, '.6') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.6'));
			}
			if (strpos($StockID, '.7') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.7'));
			}
			if (strpos($StockID, '.8') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.8'));
			}
			if (strpos($StockID, '.9') !== false){
				$StockID = substr($File, 0, strpos($StockID, '.9'));
			}
			$SQL = "SELECT stockid
				FROM stockmaster
				WHERE stockmaster.stockid = '" . $StockID . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0){
				if ($ShowHeader){
					$TableTitleText = __('Images without product in webERP');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . __('File') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = false;
				}
				echo '<tr class="striped_row">
						<td>' . $_SESSION['part_pics_dir'].'/'.$File . '</td>
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

function ItemsCancelledInTransfers($maxdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT loctransfers.reference,
					loctransfers.shipdate,
					loctransfers.shiploc,
					loctransfers.recloc,
					loctransfers.stockid,
					loctransfercancellations.cancelqty,
					loctransfercancellations.canceldate,
					loctransfercancellations.canceluserid
			FROM loctransfers 
			INNER JOIN loctransfercancellations
				ON loctransfers.reference = loctransfercancellations.reference 
					AND loctransfers.stockid = loctransfercancellations.stockid
			WHERE loctransfercancellations.canceldate >= '". $StartDate ."'
			ORDER BY loctransfers.stockid";
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items cancelled in Transfers during the last ') . $maxdays . __(' days ');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Transfer') . '</th>
						<th class="SortedColumn">' . __('Date') . '</th>
						<th class="SortedColumn">' . __('From') . '</th>
						<th class="SortedColumn">' . __('To') . '</th>
						<th class="SortedColumn">' . __('Cancel Qty') . '</th>
						<th class="SortedColumn">' . __('Cancel Date') . '</th>
						<th class="SortedColumn">' . __('Cancelled By') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$TransferLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td class="number">' . $TransferLink . '</td>
					<td>' . ConvertSQLDateTime($MyRow['shipdate']) . '</td>
					<td>' . $MyRow['shiploc'] . '</td>
					<td>' . $MyRow['recloc'] . '</td>
					<td class="number">' . locale_number_format($MyRow['cancelqty'],0) . '</td>
					<td>' . ConvertSQLDateTime($MyRow['canceldate']) . '</td>
					<td>' . $MyRow['canceluserid'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsChangingPriceDelayed($NumDays, $RootPath){
/* EXPLAIN SQL 2014-05-21	*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT SUM(locstock.quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = locations.loccode
						AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS qohpos,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohconsignment,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT SUM(locstock.quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = locations.loccode
						AND locstock.loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
						AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
						AND locstock.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.pendingqty) 
					FROM loctransfers,locations
					WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc = locations.loccode
						AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.pendingqty) 
					FROM loctransfers
					WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.pendingqty) 
					FROM loctransfers
					WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_KANTOR_LOCATIONS . ") AS intransitfromkantor,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klchangeprice.counterpricechange,
				klchangeprice.startprocessdate,
				klchangeprice.newretailprice
			FROM stockmaster, klchangeprice					
			WHERE stockmaster.stockid = klchangeprice.stockid
				AND klchangeprice.endprocessdate = '1000-01-01'
				AND klchangeprice.startprocessdate <= '". $StartDate ."'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items delayed in Change Price Procedure for more than '). $NumDays . ' days. ';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Start Date') . '</th>
						<th class="SortedColumn">' . __('QOH KL Shops') . '</th>
						<th class="SortedColumn">' . __('QOH Consignment') . '</th>
						<th class="SortedColumn">' . __('Transit From Kantor') . '</th>
						<th class="SortedColumn">' . __('Transit To Kantor') . '</th>
						<th class="SortedColumn">' . __('QOH Kantor') . '</th>
						<th class="SortedColumn">' . __('QOH Others') . '</th>
						<th class="SortedColumn">' . __('QOH Total') . '</th>
						<th class="SortedColumn">' . __('New Retail Price') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$NewPriceLink = locale_number_format($MyRow['newretailprice'],0);
			
			echo '<tr class="striped_row">
					<td class="number">' . locale_number_format($MyRow['counterpricechange'],0) . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . ConvertSQLDate($MyRow['startprocessdate']) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohpos']-$MyRow['intransitfromshops'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohconsignment']-$MyRow['intransitfromconsignment'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['intransitfromkantor'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['intransitfromshops']+$MyRow['intransitfromconsignment'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohkantor']-$MyRow['intransitfromkantor'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohotherlocs'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohtotal'],0) . '</td>
					<td class="number">' . $NewPriceLink . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsInCategoryForMoreThanDays($maxdays, $group, $RootPath){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));


	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.lastcategoryupdate,
					stockmaster.units, 
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid) AS quantity,
					topsales30,
					topsales60,
					topsales90
			FROM 	stockmaster, klsalesperformance
			WHERE stockmaster.stockid = klsalesperformance.stockid
				AND stockmaster.discontinued = 0 
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.lastcategoryupdate <= '" . $FromDate . "'
				AND stockmaster.categoryid ='" . $group . "'
			ORDER BY stockmaster.stockid";
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = GetCategoryNameFromCode($group) . ' Items for more than ' . $maxdays . ' days. Move to next step of cycle of life';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Category') . '</th>
						<th class="SortedColumn">' . __('DOB Category') . '</th>
						<th class="SortedColumn">' . __('QOH') . '</th>
						<th class="SortedColumn">' . __('#Top Sales 30') . '</th>
						<th class="SortedColumn">' . __('#Top Sales 60') . '</th>
						<th class="SortedColumn">' . __('#Top Sales 90') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['categoryid'] . '</td>
					<td>' . ConvertSQLDate($MyRow['lastcategoryupdate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['quantity'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['topsales30'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['topsales60'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['topsales90'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}	

function ItemsInmediateShortage($Cat, $RootPath){

	$SQL = "SELECT stm.stockid,
				COALESCE ((SELECT sum(quantity)
					FROM locstock
					WHERE stockid = stm.stockid),0) AS qoh,
				COALESCE ((SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd)
					FROM purchorders
						INNER JOIN purchorderdetails
							ON purchorders.orderno=purchorderdetails.orderno
					WHERE purchorderdetails.itemcode=stm.stockid
						AND purchorderdetails.completed = 0
						AND purchorders.status<>'Cancelled'
						AND purchorders.status<>'Pending'
						AND purchorders.status<>'Rejected'
						AND purchorders.status<>'Completed'),0) AS qtypo,
				COALESCE ((SELECT SUM(woitems.qtyreqd-woitems.qtyrecd)
					FROM woitems
						INNER JOIN workorders
							ON woitems.wo=workorders.wo
					WHERE workorders.closed=0
						AND woitems.stockid=stm.stockid),0) AS qtywo,
				COALESCE ((SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced)
					FROM salesorderdetails 
					INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
					WHERE salesorderdetails.completed=0
					AND salesorders.quotation=0
					AND salesorderdetails.stkcode=stm.stockid),0) AS directdemand,
				COALESCE ((SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd))
					FROM woitems INNER JOIN worequirements
						ON woitems.stockid=worequirements.parentstockid
					INNER JOIN workorders
						ON woitems.wo=workorders.wo
					AND woitems.wo=worequirements.wo
					WHERE  worequirements.stockid=stm.stockid
						AND workorders.closed=0),0) AS wodemand
			FROM stockmaster stm
			WHERE stm.discontinued = 0
				AND stm.categoryid = '" . $Cat . "'
				AND 
				(COALESCE ((SELECT sum(quantity)
					FROM locstock
					WHERE stockid = stm.stockid),0)
				+ 
				COALESCE ((SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd)
					FROM purchorders
						INNER JOIN purchorderdetails
							ON purchorders.orderno=purchorderdetails.orderno
					WHERE purchorderdetails.itemcode=stm.stockid
						AND purchorderdetails.completed = 0
						AND purchorders.status<>'Cancelled'
						AND purchorders.status<>'Pending'
						AND purchorders.status<>'Rejected'
						AND purchorders.status<>'Completed'),0)
				+ 
				COALESCE ((SELECT SUM(woitems.qtyreqd-woitems.qtyrecd)
					FROM woitems
						INNER JOIN workorders
							ON woitems.wo=workorders.wo
					WHERE workorders.closed=0
						AND woitems.stockid=stm.stockid),0)
				) <	(
				COALESCE ((SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced)
					FROM salesorderdetails 
					INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
					WHERE salesorderdetails.completed=0
					AND salesorders.quotation=0
					AND salesorderdetails.stkcode=stm.stockid),0)
				+ 
				COALESCE ((SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd))
					FROM woitems INNER JOIN worequirements
						ON woitems.stockid=worequirements.parentstockid
					INNER JOIN workorders
						ON woitems.wo=workorders.wo
					AND woitems.wo=worequirements.wo
					WHERE  worequirements.stockid=stm.stockid
						AND workorders.closed=0),0)
				)
			ORDER BY stm.stockid";
	
	$Result = DB_query($SQL);		
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Cat . ' Items in inmediate shortage stock';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('QOH') . '</th>
						<th class="SortedColumn">' . __('Qty @ PO') . '</th>
						<th class="SortedColumn">' . __('Qty @ WO') . '</th>
						<th class="SortedColumn">' . __('Demand') . '</th>
						<th class="SortedColumn">' . __('Shortage') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td class="number">' . locale_number_format($MyRow['qoh'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtypo'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtywo'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['directdemand']+$MyRow['wodemand'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['qoh']+$MyRow['qtypo']+$MyRow['qtywo']-$MyRow['directdemand']-$MyRow['wodemand'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}	


function ItemsInKLProcessAndRLNotZero($RootPath){
	/* Check if there is any item in any KL process and RL is not zero... */

	$SQL = "SELECT stockmaster.stockid,			
				   stockmaster.description,			
				   locstock.loccode,			
				   locations.locationname,			
				   locstock.reorderlevel,
					stockmaster.klmovingdiscount20,		
					stockmaster.klmovingdiscount50,		
					stockmaster.klmovingdiscount80,		
					stockmaster.klchangingprice   
			FROM stockmaster INNER JOIN locstock 			
			ON stockmaster.stockid=locstock.stockid			
			INNER JOIN locations 			
			ON locstock.loccode = locations.loccode			
			WHERE locstock.reorderlevel != 0
				AND (stockmaster.klmovingdiscount20 != 0
					OR  stockmaster.klmovingdiscount50 != 0
					OR  stockmaster.klmovingdiscount80 != 0
					OR stockmaster.klchangingprice != 0 ) 			
			ORDER BY stockmaster.stockid,
					locstock.loccode";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items with in KL process and RL not zero');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Location') . '</th>
						<th class="SortedColumn">' . __('RL') . '</th>
						<th class="SortedColumn">' . __('Changing Price') . '</th>
						<th class="SortedColumn">' . __('MoveTo 20% Disc') . '</th>
						<th class="SortedColumn">' . __('MoveTo 50% Disc') . '</th>
						<th class="SortedColumn">' . __('MoveTo 80% Disc') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			if ($MyRow['klchangingprice'] == 1){
				$ItemChangingPrice = "Yes";
			}else{
				$ItemChangingPrice = "";
			}
			if ($MyRow['klmovingdiscount20'] == 1){
				$ItemMovingToDiscount20 = "Yes";
			}else{
				$ItemMovingToDiscount20 = "";
			}
			if ($MyRow['klmovingdiscount50'] == 1){
				$ItemMovingToDiscount50 = "Yes";
			}else{
				$ItemMovingToDiscount50 = "";
			}
			if ($MyRow['klmovingdiscount80'] == 1){
				$ItemMovingToDiscount80 = "Yes";
			}else{
				$ItemMovingToDiscount80 = "";
			}
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['locationname'] . '</td>
					<td class="number">' . locale_number_format($MyRow['reorderlevel'],0) . '</td>
					<td>' . $ItemChangingPrice . '</td>
					<td>' . $ItemMovingToDiscount20 . '</td>
					<td>' . $ItemMovingToDiscount50 . '</td>
					<td>' . $ItemMovingToDiscount80 . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsNotNeededInOnlineOrderButRequested($RootPath){
	
	$SQL = "SELECT locstock.stockid,
				locstock.quantity
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND locstock.loccode = ". CODE_ONLINE_SHOP ."
				AND locstock.quantity > 0
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				AND NOT EXISTS (SELECT 	salesorderdetails.stkcode
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.stkcode = locstock.stockid
									AND salesorders.quotation = 0
									AND salesorders.fromstkloc = ". CODE_ONLINE_SHOP ."
									AND salesorderdetails.completed= 0)";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "Items Not needed for any Online Order but with QOH > 0 in Shop Online";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Item Code') . '</th>
						<th class="SortedColumn">' . __('Quantity') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$ItemLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $ItemLink . '</td>
					<td class="number">' . locale_number_format($MyRow['quantity'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsInSetup($Check, $Category, $RootPath){
	
	if ($Check == "ReadyToTest"){
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items ready to change to TEST";
		$SQL = "SELECT sm.stockid,
					sm.description,
					p.price,
					loc.QOH
				FROM stockmaster sm
				LEFT JOIN (
					SELECT stockid, price
					FROM prices 
					WHERE typeabbrev = 'RT'
						AND startdate <= CURRENT_DATE 
						AND enddate >= CURRENT_DATE
						AND currabrev = 'IDR'
				) p ON sm.stockid = p.stockid
				LEFT JOIN (
					SELECT stockid, SUM(quantity) as QOH
					FROM locstock
					WHERE loccode = " . CODE_KANTOR . "
					GROUP BY stockid
				) loc ON sm.stockid = loc.stockid
				WHERE sm.categoryid = '" . $Category . "'
					AND sm.discontinued = 0 
					AND LENGTH(sm.description) > 2
					AND loc.QOH > 0
					AND p.price IS NOT NULL
					AND NOT EXISTS (
						SELECT 1
						FROM loctransfers 
						WHERE pendingqty > 0
							AND stockid = sm.stockid
					)
				ORDER BY sm.stockid";
	}elseif($Check == "NeedDescription"){
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items needing descriptions";
		$SQL = "SELECT sm.stockid,
					sm.description,
					p.price,
					loc.QOH
				FROM stockmaster sm
				LEFT JOIN (
					SELECT stockid, price
					FROM prices 
					WHERE typeabbrev = 'RT'
						AND startdate <= CURRENT_DATE 
						AND enddate >= CURRENT_DATE
						AND currabrev = 'IDR'
				) p ON sm.stockid = p.stockid
				LEFT JOIN (
					SELECT stockid, SUM(quantity) as QOH
					FROM locstock
					GROUP BY stockid
				) loc ON sm.stockid = loc.stockid
				WHERE sm.categoryid = '" . $Category . "'
					AND sm.discontinued = 0 
					AND LENGTH(sm.description) <= 2
				ORDER BY sm.stockid";

	}elseif($Check == "NeedPrice"){
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items needing price";
		$SQL = "SELECT sm.stockid,
					sm.description,
					NULL as price,
					loc.QOH
				FROM stockmaster sm
				LEFT JOIN (
					SELECT stockid, SUM(quantity) as QOH
					FROM locstock
					GROUP BY stockid
				) loc ON sm.stockid = loc.stockid
				WHERE sm.categoryid = '" . $Category . "'
					AND sm.discontinued = 0
					AND NOT EXISTS (
						SELECT 1
						FROM prices
						WHERE stockid = sm.stockid
							AND typeabbrev = 'RT'
							AND startdate <= CURRENT_DATE 
							AND enddate >= CURRENT_DATE
							AND currabrev = 'IDR'
					)
				ORDER BY sm.stockid";
	}elseif($Check == "WithReorderLevel"){
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items with RL (items in SETUP should not have RL set)";
		$SQL = "SELECT sm.stockid,
					sm.description,
					p.price,
					loc.QOH
				FROM stockmaster sm
				LEFT JOIN (
					SELECT stockid, price
					FROM prices 
					WHERE typeabbrev = 'RT'
						AND startdate <= CURRENT_DATE 
						AND enddate >= CURRENT_DATE
						AND currabrev = 'IDR'
				) p ON sm.stockid = p.stockid
				LEFT JOIN (
					SELECT stockid, SUM(quantity) as QOH
					FROM locstock
					GROUP BY stockid
				) loc ON sm.stockid = loc.stockid
				WHERE sm.categoryid = '" . $Category . "'
					AND sm.discontinued = 0
					AND EXISTS (
						SELECT 1
						FROM locstock
						WHERE stockid = sm.stockid
						GROUP BY stockid
						HAVING SUM(reorderlevel) > 0
					)
				ORDER BY sm.stockid";
	}else{
		$TableTitleText = GetCategoryNameFromCode($Category) . " Items in SETUP";
		$SQL = "SELECT sm.stockid,
					sm.description,
					p.price,
					loc.QOH
				FROM stockmaster sm
				LEFT JOIN (
					SELECT stockid, price
					FROM prices 
					WHERE typeabbrev = 'RT'
						AND startdate <= CURRENT_DATE 
						AND enddate >= CURRENT_DATE
						AND currabrev = 'IDR'
				) p ON sm.stockid = p.stockid
				LEFT JOIN (
					SELECT stockid, SUM(quantity) as QOH
					FROM locstock
					GROUP BY stockid
				) loc ON sm.stockid = loc.stockid
				WHERE sm.categoryid = '" . $Category . "'
					AND sm.discontinued = 0 
				ORDER BY sm.stockid";
	}

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$i = 1;
		$ShowHeader = true;
		while ($MyRow = DB_fetch_array($Result)) {
			if (    ($Check != "ReadyToTest") 
				OR (($Check == "ReadyToTest") 
					AND (file_exists($_SESSION['part_pics_dir'] . '/' .$MyRow['stockid'].'.jpg')))) {
				if ($ShowHeader){
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . __('#') . '</th>
									<th class="SortedColumn">' . __('Code') . '</th>
									<th class="SortedColumn">' . __('Description') . '</th>
									<th class="SortedColumn">' . __('Price') . '</th>
									<th class="SortedColumn">' . __('QOH') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = false;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				$RLLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . locale_number_format($MyRow['QOH'],0) . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($MyRow['price'],0) . '</td>
						<td class="number">' . $RLLink . '</td>
						</tr>';
				$i++;
			}
		}
		if(!$ShowHeader){
			echo '</tbody>
					</table>
					</div>';
		}
	}
}

function ItemsInWrongShops($ShopType, $RootPath){

    if ($ShopType == "SHOPKL"){
        $TableTitleText = 'Items not allowed on KL shops';
        // Optimization: Replaced complex "AND NOT ((... OR ...))" with simpler "AND NOT (...) AND NOT (...)" blocks.
        $Condition =  " AND locations.typeloc = 'SHOPKL'
						AND NOT (stockmaster.categoryid = 'TESTKA' AND locations.alltestitems > 0)
						AND NOT (stockmaster.categoryid = 'STABKA' AND locations.allstableitems > 0)
						AND NOT (stockmaster.categoryid = 'NOPOKA' AND locations.allnopoitems > 0)
						AND NOT (stockmaster.categoryid = 'DISC2A' AND locations.alldisc20items > 0)
						AND NOT (stockmaster.categoryid = 'DISC5A' AND locations.alldisc50items > 0)
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_GENERAL . "
						AND NOT (stockmaster.categoryid = 'DISC2G' AND locations.alldisc20items > 0)
						AND NOT (stockmaster.categoryid = 'DISC5G' AND locations.alldisc50items > 0)
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_SHOPS_NOT_FOR_SALE;

    }elseif ($ShopType == "SHOPBL"){
        $TableTitleText = 'Items not allowed on BLINK shops';
        // Optimization: Replaced complex "AND NOT ((... OR ...))" with simpler "AND NOT (...) AND NOT (...)" blocks.
        $Condition =  " AND locations.typeloc = 'SHOPBL'
						AND NOT (stockmaster.categoryid = 'TESTBA' AND locations.alltestitems > 0)
						AND NOT (stockmaster.categoryid = 'STABBA' AND locations.allstableitems > 0)
						AND NOT (stockmaster.categoryid = 'NOPOBA' AND locations.allnopoitems > 0)
						AND NOT (stockmaster.categoryid = 'DISC2B' AND locations.alldisc20items > 0)
						AND NOT (stockmaster.categoryid = 'DISC5B' AND locations.alldisc50items > 0)
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_GENERAL . "
						AND NOT (stockmaster.categoryid = 'DISC2G' AND locations.alldisc20items > 0)
						AND NOT (stockmaster.categoryid = 'DISC5G' AND locations.alldisc50items > 0)
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_SHOPS_NOT_FOR_SALE;

    }elseif ($ShopType == "SHOPOU"){
        $TableTitleText = 'KL or Blink full priced items on OUTLET shops';
        // Simplified boolean logic for consistency
        $Condition =  " AND locations.typeloc = 'SHOPOU'
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET . "
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_GENERAL_INCLUDING_ALL_DISCOUNT . "
						AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_SHOPS_NOT_FOR_SALE;

    }elseif ($ShopType == "DEFECTIVE"){
        $TableTitleText = 'Discounted -D items on KL or Blink shops';
        $Condition =  " AND UPPER(RIGHT(stockmaster.stockid,2)) = '-D'
						AND (locations.typeloc = 'SHOPKL'
							OR locations.typeloc = 'SHOPBL')";
    }else{
        //error_
        return;
    }


	// Refactored SQL using explicit INNER JOINs for improved clarity and query planning.
	// By Gemini 2.5 2025-11-14
	$SQL = "SELECT stockmaster.stockid,
				stockcategory.categorydescription,
				stockmaster.description,
				locstock.loccode,
				locstock.quantity,
				locstock.reorderlevel
			FROM stockmaster
			INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
			INNER JOIN locations
				ON locstock.loccode = locations.loccode
			INNER JOIN stockcategory
				ON stockmaster.categoryid = stockcategory.categoryid
			WHERE (locstock.quantity > 0 OR locstock.reorderlevel > 0) "
				. $Condition . "
			ORDER BY stockmaster.stockid";

	$Result = DB_query($SQL);
    if (DB_num_rows($Result) != 0){
        ShowTableTitle($TableTitleText);
        echo '<div>';
        echo '<table class="selection">
                <thead>
                    <tr>
                        <th class="SortedColumn">' . __('#') . '</th>
                        <th class="SortedColumn">' . __('Code') . '</th>
                        <th class="SortedColumn">' . __('Description') . '</th>
                        <th class="SortedColumn">' . __('Category') . '</th>
                        <th class="SortedColumn">' . __('Shop') . '</th>
                        <th class="SortedColumn">' . __('Quantity') . '</th>
                        <th class="SortedColumn">' . __('Reorder Level') . '</th>
                    </tr>
                </thead>
                <tbody>';
        $i = 1;
        while ($MyRow = DB_fetch_array($Result)) {
            $CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
            $CodeLinkRL = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['reorderlevel'] . '</a>';
            echo '<tr class="striped_row">
                        <td class="number">' . $i . '</td>
                        <td>' . $CodeLink . '</td>
                        <td>' . $MyRow['description'] . '</td>
                        <td>' . $MyRow['categorydescription'] . '</td>
                        <td>' . $MyRow['loccode'] . '</td>
                        <td class="number">' . $MyRow['quantity'] . '</td>
                        <td class="number">' . $CodeLinkRL . '</td>
                    </tr>';
            $i++;
        }
        echo '</tbody>
            </table>
            </div>';
    }
}

function ItemsMovingToDiscountDelayed($TypeDiscount, $NumDays, $RootPath){
/* EXPLAIN SQL 2014-05-21	*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT SUM(locstock.quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS qohpos,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohconsignment,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT SUM(locstock.quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locstock.loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
					AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
					AND locstock.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers,locations
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc = locations.loccode
						AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_KANTOR_LOCATIONS . ") AS intransitfromkantor,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klmovetodiscount".$TypeDiscount.".countermovediscount,
				klmovetodiscount".$TypeDiscount.".startprocessdate,
				klmovetodiscount".$TypeDiscount.".discountcategory
			FROM stockmaster, klmovetodiscount".$TypeDiscount."					
			WHERE stockmaster.stockid = klmovetodiscount".$TypeDiscount.".stockid
				AND klmovetodiscount".$TypeDiscount.".endprocessdate = '1000-01-01'
				AND klmovetodiscount".$TypeDiscount.".startprocessdate <= '". $StartDate ."'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'Items delayed Moving To ' . $TypeDiscount . '% Discount Procedure for more than '. $NumDays . ' days. ';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Start Date') . '</th>
						<th class="SortedColumn">' . __('QOH KL Shops') . '</th>
						<th class="SortedColumn">' . __('QOH Consignment') . '</th>
						<th class="SortedColumn">' . __('Transit From Kantor') . '</th>
						<th class="SortedColumn">' . __('Transit To Kantor') . '</th>
						<th class="SortedColumn">' . __('QOH Kantor') . '</th>
						<th class="SortedColumn">' . __('QOH Others') . '</th>
						<th class="SortedColumn">' . __('QOH Total') . '</th>
						<th class="SortedColumn">' . __('Discount Code') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . locale_number_format($MyRow['countermovediscount'],0) . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . ConvertSQLDate($MyRow['startprocessdate']) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohpos']-$MyRow['intransitfromshops'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohconsignment']-$MyRow['intransitfromconsignment'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['intransitfromkantor'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['intransitfromshops']+$MyRow['intransitfromconsignment'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohkantor']-$MyRow['intransitfromkantor'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohotherlocs'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohtotal'],0) . '</td>
					<td class="number">' . $MyRow['discountcategory'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsOnSpecialRequest($RootPath){
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid
				AND locstock.loccode = 'KASPE'
				AND (locstock.quantity > 0 
					OR locstock.reorderlevel > 0)
			ORDER BY stockmaster.stockid";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items on Special Kantor Request');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Quantity') . '</th>
						<th class="SortedColumn">' . __('Reorder Level') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . $MyRow['quantity'] . '</td>
					<td class="number">' . $MyRow['reorderlevel'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsInLocationForMoreThan($LocCode, $NumDays, $RootPath){

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.quantity,
					locstock.date_updated
			FROM stockmaster
			INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
			WHERE locstock.loccode = '" . $LocCode . "'
				AND locstock.quantity > 0
				AND locstock.date_updated < '" . $StartDate . "'
			ORDER BY locstock.date_updated ASC";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items in Location ') . GetLocationNameFromCode($LocCode) . __(' for more than ') . $NumDays . __(' Days');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Quantity') . '</th>
						<th class="SortedColumn">' . __('Since') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . $MyRow['quantity'] . '</td>
					<td class="number">' . ConvertSQLDateTime($MyRow['date_updated']) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}


function ItemsShouldBeInWebsite(){
	$SQL = "SELECT stockid, description
			FROM stockmaster
			WHERE " . SQLFilterStockmasterForOnlineShop("ALL"). "
				AND NOT EXISTS (SELECT *
								FROM salescatprod
								WHERE salescatprod.stockid = stockmaster.stockid)";
	$Result = DB_query($SQL);
	$ShowHeader = true;
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if(file_exists($_SESSION['part_pics_dir'] . '/' .$MyRow['stockid'].'.jpg') ) {
				if($ShowHeader){
					$TableTitleText = __('Items with picture but not available in Online Shop');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . __('#') . '</th>
									<th class="SortedColumn">' . __('Code') . '</th>
									<th class="SortedColumn">' . __('Description') . '</th>
								</tr>
							</thead>
							<tbody>';
					$i = 1;
					$ShowHeader = false;
				}
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $MyRow['stockid'] . '</td>
						<td>' . $MyRow['description'] . '</td>
						</tr>';
				$i++;
			}			
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function ItemsWithStockLocationButNoStockAvailable($Location, $NameLocation, $MinAvailable, $MaxTopSalesItems, $RootPath){
	/*  EXPLAIN SQL 2014-05-30
		Examples of usage in control boards
		ItemsWithStockLocationButNoStockAvailable("WABOM", "WaterBom", 15, 600, $RootPath);
		ItemsWithStockLocationButNoStockAvailable("WHAYA", "Ayana", 15, 600, $RootPath);
		ItemsWithStockLocationButNoStockAvailable("WHINT", "InterContinental", 15, 600, $RootPath);
		InsuficientStockForItems("STABKA", "TM-", "Tali Mie", 20, 40, $RootPath);
		
		2018-03-18 taken out the condition:		AND locstock.reorderlevel > 0

	*/
	
	$SQL = "SELECT locstock.stockid,
				locstock.quantity,
				stockmaster.categoryid,
				(SELECT SUM(l2.quantity)
					FROM locations, locstock l2
					WHERE l2.loccode = locations.loccode
						AND locstock.stockid = l2.stockid
						AND (locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . "
							OR l2.loccode = " . CODE_KANTOR . ")
				) AS available
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ."
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET ."
				AND locstock.loccode = '" . $Location . "'
				AND locstock.quantity > 0
				AND (SELECT SUM(l2.quantity)
						FROM locations, locstock l2
						WHERE l2.loccode = locations.loccode
							AND locstock.stockid = l2.stockid
							AND (locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . "
								OR l2.loccode = " . CODE_KANTOR . ")
					) <= " . $MinAvailable;
	$Result = DB_query($SQL);
	$ShowHeader = true;
	if (DB_num_rows($Result) != 0){
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$PositionTopSales = PositionTopSalesItem($MyRow['stockid'], 60);
			if($PositionTopSales <= $MaxTopSalesItems){
				if ($ShowHeader){
					$TableTitleText = $MaxTopSalesItems .__(' Top Sales Items (Exclude No More Purchasing, Discount) with stock at ') . $NameLocation . ' but KL Stock Available (Toko + Kantor) <= ' . $MinAvailable;
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . __('#') . '</th>
									<th class="SortedColumn">' . __('Code') . '</th>
									<th class="SortedColumn">' . __('TopSale#') . '</th>
									<th class="SortedColumn">' . __('Qty ') . $Location . '</th>
									<th class="SortedColumn">' . __('QOH Available') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = false;
				}
				$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td class="number">' . locale_number_format($PositionTopSales,0) . '</td>
						<td class="number">' . locale_number_format($MyRow['quantity'],0) . '</td>
						<td class="number">' . locale_number_format($MyRow['available'],0) . '</td>
						</tr>';
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function ItemsWithoutPurchasingData($RootPath){
	/* EXPLAIN SQL	2014-05-20	
	
	id	select_type	table		type	possible_keys		key			key_len	ref									rows	Extra
	1	SIMPLE		purchdata	ref		StockID,Preferred	Preferred	1		const								4387	Using where; Using temporary; Using filesort
	1	SIMPLE		stockmaster	eq_ref	PRIMARY,StockID		PRIMARY		62		kl_erp.purchdata.stockid	1	Using where
	
	*/
		
		$SQL = "SELECT purchdata.stockid,
					purchdata.supplierno,
					price,
					conversionfactor,
					supplierdescription,
					suppliersuom,
					suppliers_partno,
					leadtime,
					MAX(purchdata.effectivefrom) AS latesteffectivefrom
				FROM purchdata, stockmaster
				WHERE purchdata.stockid = stockmaster.stockid 
					AND purchdata.preferred = 1
					AND stockmaster.discontinued = 0
					AND ((supplierdescription = '' AND suppliers_partno = '')
						OR suppliersuom = '')
				GROUP BY purchdata.price,
						purchdata.conversionfactor,
						purchdata.supplierdescription,
						purchdata.suppliersuom,
						purchdata.suppliers_partno,
						purchdata.leadtime
				ORDER BY purchdata.stockid, latesteffectivefrom DESC";
	
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			$TableTitleText = __('Items without full purchasing data');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">
					<thead>
						<tr>
							<th class="SortedColumn">' . __('#') . '</th>
							<th class="SortedColumn">' . __('Code') . '</th>
							<th class="SortedColumn">' . __('Supplier') . '</th>
							<th class="SortedColumn">' . __('Date') . '</th>
							<th class="SortedColumn">' . __('Supplier Part #') . '</th>
							<th class="SortedColumn">' . __('Supplier Description') . '</th>
							<th class="SortedColumn">' . __('UOM') . '</th>
							<th class="SortedColumn">' . __('Leadtime') . '</th>
						</tr>
					</thead>
					<tbody>';
			$i = 1;
			while ($MyRow = DB_fetch_array($Result)) {
				$CodeLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $MyRow['stockid'] . '">'. $MyRow['stockid'] .'</a>';
				$SupplierLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $MyRow['stockid'] . 
																'&SupplierID=' . $MyRow['supplierno'] . 
																'&Edit=1' .
																'&EffectiveFrom=' . $MyRow['latesteffectivefrom'] . '">'. $MyRow['supplierno'] .'</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $SupplierLink . '</td>
						<td>' . $MyRow['latesteffectivefrom'] . '</td>
						<td>' . $MyRow['suppliers_partno'] . '</td>
						<td>' . $MyRow['supplierdescription'] . '</td>
						<td>' . $MyRow['suppliersuom'] . '</td>
						<td class="number">' . locale_number_format($MyRow['leadtime'],0) . '</td>
						</tr>';
				$i++;
			}
			echo '</tbody>
				</table>
				</div>';
		}
	}
	

function ItemsWithWrongNumberOfPreferredSuppliers($RootPath){
	
	$SQL = "SELECT purchdata.stockid,
				description,
				COUNT(*) AS pref
			FROM purchdata
			INNER JOIN stockmaster
				ON stockmaster.stockid = purchdata.stockid
			WHERE preferred = 1
				AND discontinued = 0
			GROUP BY stockid
			HAVING pref != 1
			ORDER BY purchdata.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items with wrong number of preferred suppliers');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('# Preferred Suppliers') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $MyRow['stockid'] . '">'. $MyRow['stockid'] .'</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['pref'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsWithoutStandardCost($RootPath){
	/* Check if there is any item without standard cost */
	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description, 
				(SELECT SUM(locstock.quantity) 
					FROM locstock 
					WHERE locstock.stockid = stockmaster.stockid) AS availablestock
			FROM stockmaster,stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype != 'D'
				AND stockmaster.categoryid != 'ASSETS'
				AND actualcost = 0
				AND discontinued = 0";
// EXPLAIN SQL 2014-05-31
//	prnMsg($SQL);
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items without standard cost');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('QOH') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['availablestock'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsWithoutWeightOrVolume($RootPath){
	$SQL = "SELECT stockmaster.stockid,
				   stockmaster.description,
				   stockmaster.grossweight,
				   stockmaster.netweight,
				   stockmaster.volume,
				   stockmaster.longdescription,	
				   stockmaster.categoryid	
			FROM stockmaster
			WHERE ". SQLFilterStockmasterForOnlineShop("ALL") . "
				AND (stockmaster.grossweight < 0.00001 
					OR stockmaster.volume < 0.00001
					OR stockmaster.grossweight <= stockmaster.netweight)
			ORDER BY stockmaster.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Online Shop items with no gross weight, no volume or Net > Gross Weight');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Net Weight Kg') . '</th>
						<th class="SortedColumn">' . __('Gross Weight Kg') . '</th>
						<th class="SortedColumn">' . __('Volume m3') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/Stocks.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['netweight'],5) . '</td>
					<td class="number">' . locale_number_format($MyRow['grossweight'],5) . '</td>
					<td class="number">' . locale_number_format($MyRow['volume'],5) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ItemsWithStockKantorButReorderLevelTokoZero($TypeOfShop, $RootPath){
/**********************************************************************
items with stock kantor > 0 
RL is zero at one type of shop
No pending transfer regarding this item

2013-04-16 excluding items in change price process
2013-04-25 excluding items in move to discount / outlet process 
2014-12-02 excluding items in OLD categories

***********************************************************************/

	$ShopsToSetRL = NumberOfShops($TypeOfShop);
	if ($TypeOfShop == "SHOPKL"){
		$Message = 'KAPAL-LAUT';
		$ConditionCategory =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . ")";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPKL' ";
	}elseif ($TypeOfShop == "SHOPKLDISCOUNT20"){
		$Message = 'KAPAL-LAUT with 20% Discount';
		$ConditionCategory =  " AND stockmaster.categoryid = 'DISC2A' ";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPKL' 
								AND locations.alldisc20items = 2 ";
	}elseif ($TypeOfShop == "SHOPKLDISCOUNT50"){
		$Message = 'KAPAL-LAUT with 50% Discount';
		$ConditionCategory =  " AND stockmaster.categoryid = 'DISC5A' ";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPKL' 
								AND locations.alldisc50items = 2 ";
	}elseif ($TypeOfShop == "SHOPKLDISCOUNT80"){
		$Message = 'KAPAL-LAUT with 80% Discount';
		$ConditionCategory =  " AND stockmaster.categoryid = 'DISC8A' ";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPKL' 
								AND locations.alldisc80items = 2 ";
	}elseif ($TypeOfShop == "SHOPBL"){
		$Message = 'BLINK';
		$ConditionCategory =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . ")";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPBL' ";
	}elseif ($TypeOfShop == "SHOPBLDISCOUNT20"){
		$Message = 'BLINK with 20% Discount';
		$ConditionCategory =  " AND stockmaster.categoryid = 'DISC2B' ";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPBL' 
								AND locations.alldisc20items = 2 ";
	}elseif ($TypeOfShop == "SHOPBLDISCOUNT50"){
		$Message = 'BLINK with 50% Discount';
		$ConditionCategory =  " AND stockmaster.categoryid = 'DISC5B' ";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPBL' 
								AND locations.alldisc50items = 2 ";
	}elseif ($TypeOfShop == "SHOPBLDISCOUNT80"){
		$Message = 'BLINK with 80% Discount';
		$ConditionCategory =  " AND stockmaster.categoryid = 'DISC8B' ";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPBL' 
								AND locations.alldisc80items = 2 ";
	}elseif ($TypeOfShop == "SHOPOU"){
		$Message = 'OUTLET';
		$ConditionCategory =  " AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . ")";
		$ConditionTypeOfShop = " AND locations.typeloc = 'SHOPOU' ";
	}else{
		//error_
		return;
	}

	$SQL = "SELECT stockid,
			stockmaster.categoryid,
			stockmaster.description,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid
				AND (locstock.loccode = " . CODE_KANTOR . " ))AS QtyKantor
			FROM stockmaster, 
				stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND discontinued = 0
				AND (SELECT SUM(locstock.reorderlevel)
					FROM locstock, locations
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = locations.loccode " . 
						$ConditionTypeOfShop . ") = 0
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = " . CODE_KANTOR . ") > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  pendingqty > 0
							AND loctransfers.stockid =  stockmaster.stockid)
				AND stockcategory.stocktype = 'F' " . 
				$ConditionCategory . "
			ORDER BY stockid";
// prnMsg($SQL);
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Message . ' Items with stock available (but NO changing price or category) at Kantor but RL zero for all ' . $Message . '  SHOPS';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th>' . __('#') . '</th>
						<th>' . __('Code') . '</th>
						<th>' . __('Category') . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('QOH Kantor') . '</th>
						<th>' . __('RL=?') . '</th>
						<th colspan="2">' . __('RL=1') . '</th>
						<th colspan="2">' . __('RL=2') . '</th>
						<th colspan="2">' . __('RL=3') . '</th>
						<th colspan="2">' . __('RL=4') . '</th>
						<th colspan="2">' . __('RL=5') . '</th>
					</tr>
					<tr>
						<th class="SortedColumn"></th>
						<th class="SortedColumn"></th>
						<th class="SortedColumn"></th>
						<th class="SortedColumn"></th>
						<th class="SortedColumn"></th>
						<th></th>
						<th>' . __('All') . '</th>
						<th>' . __('Some') . '</th>
						<th>' . __('All') . '</th>
						<th>' . __('Some') . '</th>
						<th>' . __('All') . '</th>
						<th>' . __('Some') . '</th>
						<th>' . __('All') . '</th>
						<th>' . __('Some') . '</th>
						<th>' . __('All') . '</th>
						<th>' . __('Some') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			$ManualLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . 'Manual' . '</a>';
			// set the links to nil, and just set some if we have enough QOH
			$LinkRL1All = '';
			$LinkRL1Some = '';
			$LinkRL2All = '';
			$LinkRL2Some = '';
			$LinkRL3All = '';
			$LinkRL3Some = '';
			$LinkRL4All = '';
			$LinkRL4Some = '';
			$LinkRL5All = '';
			$LinkRL5Some = '';
			if($ShopsToSetRL != 0){
				if ($MyRow['QtyKantor'] >= 0){
					$LinkRL1All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=1' . '">' . '1' . '</a>';
					$LinkRL1Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=1' . '">' . '1' . '</a>';
				}
				if ($MyRow['QtyKantor'] >= $ShopsToSetRL * 2){
					$LinkRL2All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=2' . '">' . '2' . '</a>';
					$LinkRL2Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=2' . '">' . '2' . '</a>';
				}
				if ($MyRow['QtyKantor'] >= $ShopsToSetRL * 3){
					$LinkRL3All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=3' . '">' . '3' . '</a>';
					$LinkRL3Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=3' . '">' . '3' . '</a>';
				}
				if ($MyRow['QtyKantor'] >= $ShopsToSetRL * 4){
					$LinkRL4All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=4' . '">' . '4' . '</a>';
					$LinkRL4Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=4' . '">' . '4' . '</a>';
				}
				if ($MyRow['QtyKantor'] >= $ShopsToSetRL * 5){
					$LinkRL5All  = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=Y' . '&RL=5' . '">' . '5' . '</a>';
					$LinkRL5Some = '<a href="' . $RootPath . '/KLAutoStockReorderLevel.php?StockID=' . $MyRow['stockid'] . '&TypeOfShop=' . $TypeOfShop . '&AllShops=N' . '&RL=5' . '">' . '5' . '</a>';
				}
			}
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['categoryid'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['QtyKantor'],0) . '</td>
					<td>' . $ManualLink . '</td>
					<td>' . $LinkRL1All . '</td>
					<td>' . $LinkRL1Some . '</td>
					<td>' . $LinkRL2All . '</td>
					<td>' . $LinkRL2Some . '</td>
					<td>' . $LinkRL3All . '</td>
					<td>' . $LinkRL3Some . '</td>
					<td>' . $LinkRL4All . '</td>
					<td>' . $LinkRL4Some . '</td>
					<td>' . $LinkRL5All . '</td>
					<td>' . $LinkRL5Some . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function NotDiscountedItemsWithDiscount($RootPath){
	$SQL = "SELECT stockid,
					description
			FROM  stockmaster 
			WHERE   categoryid NOT IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS ."
				AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET ."
				AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_OLD ."
				AND discountcategory !=  ''
				AND discontinued = 0";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Not Discounted items with discount');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function ObsoleteComponentsInActiveBOM($RootPath){

	$SQL = "SELECT bom.parent,
				bom.component
			FROM bom, stockmaster AS stP, stockmaster AS stC
			WHERE bom.parent = stP.stockid 
				AND bom.component = stC.stockid
				AND stP.discontinued = 0
				AND stC.discontinued = 1";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Active BOM with obsolete components');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('BOM of') . '</th>
						<th class="SortedColumn">' . __('Component') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLinkParent = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['parent'] . '">' . $MyRow['parent'] . '</a>';
			$CodeLinkComponent = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['component'] . '">' . $MyRow['component'] . '</a>';
			echo '<tr class="striped_row">
					<td>' . $CodeLinkParent . '</td>
					<td>' . $CodeLinkComponent . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OldOnlineQuotations($NumDaysBank, $RootPath){

	$StartDateBank = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysBank));
	$Titletext = "Old Online Quotations to be deleted. No Payment received in more than " . $NumDaysBank . " days.";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
				salesorders.klocpaymentcode,
				debtorsmaster.currcode,
				currencies.decimalplaces
			FROM salesorders 
				INNER JOIN salesorderdetails 	
					ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
					ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 0	
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")
				AND salesorders.quotation = 1
				AND salesorders.orddate < '" . $StartDateBank . "'
			GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "Old Online Quotations to be deleted. No Payment received in more than " . $NumDaysBank . " days.";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Order') . '</th>
						<th class="SortedColumn">' . __('#KL-Website') . '</th>
						<th class="SortedColumn">' . __('Customer') . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . __('Order Date') . '</th>
						<th class="SortedColumn">' . __('Order Value') . '</th>
						<th class="SortedColumn">' . __('Currency') . '</th>
						<th class="SortedColumn">' . __('Payment Method') . '</th>
						<th class="SortedColumn">' . __('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			$PaymentMethodText = GetPaymentMethodTextFromCode($MyRow['klocpaymentcode']);
			$DeleteLink = '<a href="' . $RootPath . '/KLDeleteSalesOrder.php?OrderNo=' . $MyRow['orderno'] . '">' . 'Delete as Expired' . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td class="number">' . locale_number_format($MyRow['customerref']) . '</td>
					<td>' . $MyRow['debtorno'] . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['ordervalue']+$MyRow['freightcost'],$MyRow['decimalplaces']) . '</td>
					<td>' . $MyRow['currcode'] . '</td>
					<td>' . $PaymentMethodText . '</td>
					<td>' . $DeleteLink . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OldPOStillActive($maxdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT orderno,
				   orddate,
				   supplierno
			FROM purchorders 
			WHERE status NOT IN ('Completed', 'Cancelled', 'Rejected')
			AND orddate <= '". $StartDate ."'
			AND EXISTS (SELECT *
						FROM purchorderdetails
						WHERE purchorderdetails.orderno = purchorders.orderno
						AND completed = 0)
			ORDER BY orderno";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('POs older than ') . $maxdays . __(' days and still not closed');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('PO') . '</th>
						<th class="SortedColumn">' . __('Date') . '</th>
						<th class="SortedColumn">' . __('Supplier') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/PO_OrderDetails.php?OrderNo=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td>' . $MyRow['supplierno'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OldWOStillActive($maxdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT wo,
				   startdate
			FROM workorders 
			WHERE closed = 0
			AND startdate <= '". $StartDate ."'
			ORDER BY wo";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('WOs older than ') . $maxdays . __(' days and still not closed');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('WO') . '</th>
						<th class="SortedColumn">' . __('Date') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/WorkOrderEntry.php?WO=' . $MyRow['wo'] . '">' . $MyRow['wo'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td>' . ConvertSQLDate($MyRow['startdate']) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OnlineItemsOnProcess($RootPath){
	
	$SQL = "SELECT salesorders.orderno,	
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				stockmaster.categoryid,
				salesorders.orddate,
				salesorderdetails.stkcode,
				salesorderdetails.quantity AS qtyorder,
				l1.reorderlevel,
				l1.quantity AS qtyready,
				(SELECT SUM(l2.quantity)
					FROM locstock AS l2
					WHERE l1.stockid = l2.stockid
						AND l2.loccode = " . CODE_KANTOR . ") AS qohkantor
			FROM salesorderdetails, salesorders, locstock AS l1, debtorsmaster, stockmaster	
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = l1.stockid
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND salesorders.quotation = 0
				AND salesorders.fromstkloc = ". CODE_ONLINE_SHOP ."
				AND l1.loccode = ". CODE_ONLINE_SHOP ."
				AND salesorderdetails.completed= 0
			ORDER BY salesorders.orderno, salesorderdetails.stkcode";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = "Items on process for Online Orders";
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th>' . __('#') . '</th>
						<th>' . __('Order') . '</th>
						<th>' . __('Customer') . '</th>
						<th>' . __('Name') . '</th>
						<th>' . __('Order Date') . '</th>
						<th>' . __('Item Code') . '</th>
						<th>' . __('Quantity') . '</th>
						<th>' . __('QOH Toko Online') . '</th>
						<th>' . __('QOH Kantor') . '</th>
						<th>' . __('Status') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		$OrderInProcess = -1;
		$OrderReadyForShipment = true;
		while ($MyRow = DB_fetch_array($Result)) {
			if (($OrderInProcess != $MyRow['orderno']) AND ($OrderInProcess != -1)){
				// We just checked all items in the order, and it is not the first one
				if ($OrderReadyForShipment){
					$Status = "ORDER READY FOR SHIPMENT";
				}else{
					$Status = "ORDER IN PROCESS";
				}
				echo '<tr class="striped_row">
						<td></td>
						<td class="number"></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td class="number"></td>
						<td class="number"></td>
						<td class="number"></td>
						<td>' . $Status . '</td>
						</tr>';
			$OrderReadyForShipment = true;
			}
			
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			$ItemLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stkcode'] . '">' . $MyRow['stkcode'] . '</a>';
			
			if (($MyRow['qtyready'] >= $MyRow['qtyorder']) OR (!ItemInList($MyRow['categoryid'], ONLINESHOP_AVAILABLE_STOCK_CATEGORIES))){
				// item ready to ship
				$Status = "";
			}elseif($MyRow['qtyorder'] > $MyRow['qohkantor']){
				// QOH kantor not enough to cover the order, so we need to get some from the shops
				$Status = "Needs return from shops";
				$OrderReadyForShipment = false;
			}else{
				// QOH kantor enough to cover the requirements of the order
				$Status = "In process kantor";
				$OrderReadyForShipment = false;
			}
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td>' . $MyRow['debtorno'] . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td>' . $ItemLink . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyorder'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyready'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['qohkantor'],0) . '</td>
					<td>' . $Status . '</td>
					</tr>';
			$i++;
			$OrderInProcess = $MyRow['orderno'];
		}
		// status of the last order online
		if ($OrderReadyForShipment){
			$Status = "ORDER READY FOR SHIPMENT";
		}else{
			$Status = "ORDER IN PROCESS";
		}
		echo '<tr class="striped_row">
				<td></td>
				<td class="number"></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td class="number"></td>
				<td class="number"></td>
				<td class="number"></td>
				<td>' . $Status . '</td>
				</tr>';

		echo '</tbody>
			</table>
			</div>';
	}
}

function OnlineOrdersFollowUp($Source, $numDays, $RootPath){

	$TableTitleText = "Follow up Outstanding " . $Source. " Online Orders";
	$ThankYouDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));
// 2015-01-14 Prices already NET for online orders
//                (SELECT SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent))
	if ($Source == "LAZADA"){	
		$SQL = "SELECT salesorders.orderno,	
					salesorders.customerref,
					salesorders.klemailpaymentconfirm,
					salesorders.klemailtrackingconfirm,
					salesorders.klemailthankyouorder,
					debtorsmaster.debtorno,
					salesorders.deliverto AS name,
					salesorders.orddate,
					(SELECT SUM(salesorderdetails.unitprice*salesorderdetails.quantity)
							FROM salesorderdetails
							WHERE salesorderdetails.orderno = salesorders.orderno) AS ordervalue,
					salesorders.freightcost,
					debtorsmaster.currcode,
					shippers.shippername,
					debtortrans.consignment,
					currencies.decimalplaces
				FROM salesorders 
					INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortrans 
						ON debtortrans.order_ = salesorders.orderno
					INNER JOIN shippers 
						ON salesorders.shipvia = shippers.shipper_id
					INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
				WHERE debtorsmaster.debtorno = 'LAZADA'
					AND salesorders.quotation = 0
					AND ((salesorders.klemailthankyouorder = '1000-01-01' 
								AND salesorders.klemailtrackingconfirm <= '" . $ThankYouDate . "' 
								AND salesorders.klemailtrackingconfirm != '1000-01-01')
						)
				GROUP BY salesorders.orderno,	
					debtorsmaster.name,
					salesorders.orddate
				ORDER BY salesorders.orderno";			
	}else{
		$SQL = "SELECT salesorders.orderno,	
					salesorders.customerref,
					salesorders.klemailpaymentconfirm,
					salesorders.klemailtrackingconfirm,
					salesorders.klemailthankyouorder,
					debtorsmaster.debtorno,
					salesorders.deliverto AS name,
					salesorders.orddate,
					(SELECT SUM(salesorderdetails.unitprice*salesorderdetails.quantity)
							FROM salesorderdetails
							WHERE salesorderdetails.orderno = salesorders.orderno) AS ordervalue,
					salesorders.freightcost,
					debtorsmaster.currcode,
					shippers.shippername,
					debtortrans.consignment,
					currencies.decimalplaces
				FROM salesorders 
					INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortrans 
						ON debtortrans.order_ = salesorders.orderno
					INNER JOIN shippers 
						ON salesorders.shipvia = shippers.shipper_id
					INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
				WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")
					AND debtorsmaster.debtorno != 'LAZADA'
					AND salesorders.quotation = 0
					AND (	(debtortrans.type = 12 
								AND salesorders.klemailpaymentconfirm = '1000-01-01')
						)
				GROUP BY salesorders.orderno,	
					debtorsmaster.name,
					salesorders.orddate
				ORDER BY salesorders.orderno";			
	}
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . '#' . '</th>
						<th class="SortedColumn">' . __('webERP Order') . '</th>
						<th class="SortedColumn">' . '#' . $Source . '</th>
						<th class="SortedColumn">' . __('Customer') . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . __('Order Date') . '</th>
						<th class="SortedColumn">' . __('Order Value') . '</th>
						<th class="SortedColumn">' . __('Currency') . '</th>
						<th class="SortedColumn">' . __('Payment Confirmation') . '</th>
						<th class="SortedColumn">' . __('Tracking Number') . '</th>
						<th class="SortedColumn">' . __('Tracking Confirmation') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			
			$EmailType3 = "ThankYouOrder";
			$EmailType4 = "NoSendThankYou";
			if ($MyRow['klemailthankyouorder']== '1000-01-01'){
				$EmailLinkText3 = 'Send now';
				$EmailLinkText4 = 'Do NOT send';
				$EmailLink3 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['orderno'] . '&EmailType=' . $EmailType3. '&CustomerOrder=' . $MyRow['customerref'] . '">'. $EmailLinkText3 .'</a>';
				$EmailLink4 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['orderno'] . '&EmailType=' . $EmailType4. '&CustomerOrder=' . $MyRow['customerref'] . '">'. $EmailLinkText4 .'</a>';
			}else{
				$EmailLink3 = ConvertSQLDate($MyRow['klemailthankyouorder']);
				$EmailLink4 = ConvertSQLDate($MyRow['klemailthankyouorder']);
			}

			$EmailType2 = "TrackingConfirmation";
			if ($MyRow['klemailtrackingconfirm']== '1000-01-01'){
				$EmailLinkText = 'Send now';
				$EmailLink2 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['orderno'] . '&EmailType=' . $EmailType2. '&CustomerOrder=' . $MyRow['customerref'] . '">'. $EmailLinkText .'</a>';
				$EmailLink3 = 'Tracking Confirmation first';
				$EmailLink4 = 'Tracking Confirmation first';
			}else{
				$EmailLink2 = ConvertSQLDate($MyRow['klemailtrackingconfirm']);
			}
			
			$EmailType1 = "PaymentConfirmation";
			if ($MyRow['klemailpaymentconfirm']== '1000-01-01'){
				$EmailLinkText = 'Send now';
				$EmailLink1 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $MyRow['orderno'] . '&EmailType=' . $EmailType1. '&CustomerOrder=' . $MyRow['customerref'] . '">'. $EmailLinkText .'</a>';
				$EmailLink2 = 'Payment Confirmation first';
				$EmailLink3 = 'Payment Confirmation first';
				$EmailLink4 = 'Payment Confirmation first';
			}else{
				$EmailLink1 = ConvertSQLDate($MyRow['klemailpaymentconfirm']);
			}

			if ($Source == "LAZADA"){
				$EmailLink1 = '';
				$EmailLink2 = '';
			}
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td class="number">' . locale_number_format($MyRow['customerref']) . '</td>
					<td>' . $MyRow['debtorno'] . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['ordervalue']+$MyRow['freightcost'],$MyRow['decimalplaces']) . '</td>
					<td>' . $MyRow['currcode'] . '</td>
					<td>' . $EmailLink1 . '</td>
					<td>' . $MyRow['shippername'] . ' ' . $MyRow['consignment'] . '</td>
					<td></td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OnlineQuotationsFollowUp($RootPath ){

	$Titletext = "Follow up Outstanding Online Quotations";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				salesorders.klemailremindbanktransfer,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
                SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
				salesorders.klocpaymentcode,
				salesorders.klocorderstatus,
				debtorsmaster.currcode,
				currencies.decimalplaces
			FROM salesorders 
				INNER JOIN salesorderdetails 	
					ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
					ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 0	
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")
				AND salesorders.quotation = 1
			GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";			

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Titletext;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Order') . '</th>
						<th class="SortedColumn">' . __('#KL-Website') . '</th>
						<th class="SortedColumn">' . __('Customer') . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . __('Order Date') . '</th>
						<th class="SortedColumn">' . __('Order Value') . '</th>
						<th class="SortedColumn">' . __('Currency') . '</th>
						<th class="SortedColumn">' . __('Payment Method') . '</th>
						<th class="SortedColumn">' . __('OC Status') . '</th>
						<th class="SortedColumn">' . __('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';

			$PaymentLinkText = 'Apply Payment';
			$PaymentValue = $MyRow['ordervalue']+$MyRow['freightcost'];

			// prepare the links according to the payment code from OpenCart
			$PaymentLink = '<a href="' . $RootPath . '/KLReceiptPaymentOnline.php?OrderNo=' . $MyRow['orderno'] . '&PaymentCode=' . $MyRow['klocpaymentcode'] . '&CustomerCode=' . $MyRow['debtorno'] . '&Amount=' . $PaymentValue . '">'. $PaymentLinkText .'</a>';
			$PaymentMethodText = GetPaymentMethodTextFromCode($MyRow['klocpaymentcode']);

			$OCStatusText = GetOpenCartStatusTextFromCode($MyRow['klocorderstatus']);

			if ($OCStatusText != "Processing"){
				$PaymentLink = ''; // do not allow Apply payment in case of an status that is not processing
			}
			
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td class="number">' . locale_number_format($MyRow['customerref']) . '</td>
					<td>' . $MyRow['debtorno'] . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['ordervalue']+$MyRow['freightcost'],$MyRow['decimalplaces']) . '</td>
					<td>' . $MyRow['currcode'] . '</td>
					<td>' . $PaymentMethodText . '</td>
					<td>' . $OCStatusText . '</td>
					<td>' . $PaymentLink . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function OpenCartItemsWithoutPicture($RootPath ){

	$SQL = "SELECT 	oc_product.model AS stockid
			FROM oc_product
			WHERE oc_product.status = 1
			ORDER BY oc_product.model";
	$Result = DB_query_oc($SQL);
	$ShowHeader = true;

	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			if(!file_exists(ABSOLUTE_PATH_OPENCART_IMAGES .$MyRow['stockid'].'.jpg') ) {
				if($ShowHeader){
					$TableTitleText = __('Online Shop Items without picture');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . '#' . '</th>
									<th class="SortedColumn">' . __('Item Code') . '</th>
								</tr>
							</thead>
							<tbody>';
					$i = 1;
					$ShowHeader = false;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						</tr>';
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function OutstandingOrders($customertype, $Ordertype, $RootPath){
	/* Check if there are outstanding orders for retail customers */

	if ($customertype == "Retail"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")";
		$Namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Retail";
		$WebsiteIDName = "";
	}elseif ($customertype == "Consignment"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_CONSIGNMENT . ")";
		$Namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Consignment";
		$WebsiteIDName = "";
	}elseif ($customertype == "Wholesale"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WHOLESALE . ")";
		$Namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Wholesale";
		$WebsiteIDName = "";
	}elseif ($customertype == "Online"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")";
		$Namefield = " salesorders.deliverto AS name ";
		$Titletext = "Outstanding Online";
		$WebsiteIDName = "#KL-Website";
	}elseif ($customertype == "MarketPlace"){
		$Whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_MARKETPLACE . ")";
		$Namefield = " salesorders.deliverto AS name ";
		$Titletext = "Outstanding MarketPlace";
		$WebsiteIDName = "#KL-Website";
	}else{
		$Namefield = " debtorsmaster.name ";
		$Whereclause = " ";
		$Titletext = __('Outstanding');
		$WebsiteIDName = "";
	}
	
	if ($Ordertype == "Quotation"){
		$Whereclause = $Whereclause . " AND salesorders.quotation = 1 ";
		$Titletext = $Titletext . " Quotations";
	}elseif  ($Ordertype == "Order"){
		$Whereclause = $Whereclause . " AND salesorders.quotation = 0 ";
		$Titletext = $Titletext . " Orders";
	}else{
		$Titletext = __(' Orders and Quotations');
	}
	
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				debtorsmaster.debtorno, "
			   . $Namefield . ",
				salesorders.orddate,
                SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
			FROM salesorders INNER JOIN salesorderdetails 	
				ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
				ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 0 "
			. $Whereclause .
			" GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = $Titletext;
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Order') . '</th>
						<th class="SortedColumn">' . $WebsiteIDName . '</th>
						<th class="SortedColumn">' . __('Customer') . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . __('Order Date') . '</th>
						<th class="SortedColumn">' . __('Total Value') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		$TotalValue = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'] . '">' . $MyRow['orderno'] . '</a>';
			if ($customertype == "Online"){
				$WebsiteID = locale_number_format($MyRow['customerref']);
			}else{
				$WebsiteID = "";
			}

			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $CodeLink . '</td>
					<td class="number">' . $WebsiteID . '</td>
					<td>' . $MyRow['debtorno'] . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['ordervalue'],0) . '</td>
					</tr>';
			$TotalValue += $MyRow['ordervalue'];
			$i++;
		}
		echo '</tbody>
			<tfooter>';
		echo '<tr class="striped_row">
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>Total IDR</td>
				<td class="number">' . locale_number_format($TotalValue,0) . '</td>
				</tr>';
		echo '</tfooter>
			</table>
			</div>';
	}
}

function over_or_below_limit($Request, $Sign, $Limit, $RootPath){
/* EXPLAIN SQL 2014-05-21	*/
	if ($Request == "Items changing price"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klchangingprice != 0";
	}elseif ($Request =="Items moving to 20% discount"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klmovingdiscount20 != 0";
	}elseif ($Request =="Items moving to 50% discount"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klmovingdiscount50 != 0";
	}elseif ($Request =="Items moving to 80% discount"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klmovingdiscount80 != 0";
	}elseif ($Request =="Items changing price or moving category"){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster
				WHERE stockmaster.klchangingprice != 0
					OR stockmaster.klmovingdiscount20 != 0
					OR stockmaster.klmovingdiscount50 != 0
					OR stockmaster.klmovingdiscount80 != 0";
	}
	
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	if ($Sign == "OVER"){
		if ($MyRow[0] > $Limit){
			$Text = $Request . " is OVER the maximum. Current value = " . locale_number_format($MyRow[0],0) . " Maximum = " . locale_number_format($Limit,0);
			ShowWarningTitle($Text);
		}
	}
	if ($Sign == "BELOW"){
		if ($MyRow[0] < $Limit){
			$Text = $Request . " is BELOW the minimum. Current value = " . locale_number_format($MyRow[0],0) . " Minimum = " . locale_number_format($Limit,0);
			ShowWarningTitle($Text);
		}
	}
}

function MinimumOutletStockAvailable($MinModels20, $MinModels50, $MinModels80, $NumberOfTestExecuted){
	$SQL="SELECT loccode,
			locationname
		FROM locations
		WHERE typeloc = 'SHOPOU'";
	$Result = DB_query($SQL);
	while ($MyShop = DB_fetch_array($Result)){
		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_20 . "
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$MyShop['loccode']."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow[0] < $MinModels20){
			$Text = "Discount 20% avaliable at " . $MyShop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($MyRow[0],0) . " Minimum = " . locale_number_format($MinModels20,0);
			ShowWarningTitle($Text);
		}
		$NumberOfTestExecuted++;

		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_50 . "
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$MyShop['loccode']."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow[0] < $MinModels50){
			$Text = "Discount 50% avaliable at " . $MyShop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($MyRow[0],0) . " Minimum = " . locale_number_format($MinModels50,0);
			ShowWarningTitle($Text);
		}
		$NumberOfTestExecuted++;

		$SQL = "SELECT COUNT(*)
				FROM stockmaster,locstock
				WHERE stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT_80 . "
					AND locstock.reorderlevel > 0
					AND locstock.loccode ='".$MyShop['loccode']."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow[0] < $MinModels80){
			$Text = "Discount 80% avaliable at " . $MyShop['locationname'] . " is BELOW the minimum. Current value = " . locale_number_format($MyRow[0],0) . " Minimum = " . locale_number_format($MinModels80,0);
			ShowWarningTitle($Text);
		}
		$NumberOfTestExecuted++;
	}
	return $NumberOfTestExecuted;
}

function OvestockAtSamples($maxallowedsamples, $RootPath){

	$SQL = "SELECT locstock.stockid, 
					stockmaster.description, 
					quantity AS qty
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND loccode = 'SAMPR'
				AND quantity > '". $maxallowedsamples."'
			ORDER BY locstock.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Overstock of samples');
		ShowTableTitle($TableTitleText);
		$TableHeader = '<tr>
							<th class="SortedColumn">' . __('#') . '</th>
							<th class="SortedColumn">' . __('Code') . '</th>
							<th class="SortedColumn">' . __('Description') . '</th>
							<th class="SortedColumn">' . __('Qty of samples') . '</th>
						</tr>';
		echo '<div>';
		echo '<table class="selection">
				<thead>' . $TableHeader . '</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['qty'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function PackagingItemsOnWrongLocation($RootPath){
/* EXPLAIN SQL	2014-05-20

id	select_type	table	type	possible_keys	key	key_len	ref	rows	Extra
1	SIMPLE	stockmaster	ref	PRIMARY,CategoryID,StockID	CategoryID	20	const	10	Using where
1	SIMPLE	locstock	ref	PRIMARY,StockID	StockID	62	kl_erp.stockmaster.stockid	14	Using where

*/	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock, locations
			WHERE stockmaster.stockid = locstock.stockid
				AND locstock.loccode = locations.loccode
				AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
				AND locstock.loccode NOT IN " . LIST_PACAKING_LOCATIONS . "
				AND locstock.loccode != 'TOKWS'
				AND locstock.loccode != 'SAMPR'
				AND ( locstock.quantity > 0 OR locstock.reorderlevel > 0 )
			ORDER BY stockmaster.stockid";

			$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = 'Packaging items in wrong locations (must be transferred to another location)';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Shop') . '</th>
						<th class="SortedColumn">' . __('Quantity') . '</th>
						<th class="SortedColumn">' . __('RL') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['loccode'] . '</td>
					<td class="number">' . $MyRow['quantity'] . '</td>
					<td class="number">' . $MyRow['reorderlevel'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function PettyCashBalance($TypeUser){

	if ($TypeUser == 'Authorizer'){
		$WhereUser = "AND pctabs.authorizer LIKE '%" . $_SESSION['UserID'] . "%'";
	}elseif($TypeUser == 'User'){
		$WhereUser = "AND pctabs.usercode = '". $_SESSION['UserID'] ."'";
	}else{
		$WhereUser = "";
	}

	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount,
				pctabs.currency
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	".
			$WhereUser . "
			GROUP BY pcashdetails.tabcode, pctabs.tablimit
			HAVING ( SUM(pcashdetails.amount) < -0.01
					OR SUM(pcashdetails.amount) > pctabs.tablimit)";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		
		if ($TypeUser == "Authorizer"){
			$TableTitleText = __('Petty Cash Accounts you AUTHORIZE with balance too Low or Too High');
		}else{
			$TableTitleText = __('Petty Cash Balance you USE with balance too Low or Too High');
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('PC Tab Code') . '</th>
						<th class="SortedColumn">' . __('Amount') . '</th>
						<th class="SortedColumn">' . __('Currency') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $MyRow['tabcode'] . '</td>
					<td class="number">' . locale_number_format($MyRow['amount'],0) . '</td>
					<td>' . $MyRow['currency'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function PettyCashToBeAuthorized($AuthorizationType){

	if ($AuthorizationType == "Cash"){
		$TableTitleText = "Petty Cash Assignations to be Authorized";
		$SQLAuthority = "AND pctabs.authorizer LIKE '%" . $_SESSION['UserID'] . "%'
						AND pcashdetails.codeexpense = 'ASSIGNCASH'";
	}else{
		$TableTitleText = "Petty Cash Expenses to be Authorized";
		$SQLAuthority = "AND pctabs.authorizerexpenses LIKE '%" . $_SESSION['UserID'] . "%'
						AND pcashdetails.codeexpense != 'ASSIGNCASH'";
	}
	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount,
				pctabs.currency
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pcashdetails.authorized = '1000-01-01'" .
				$SQLAuthority . "
			GROUP BY pcashdetails.tabcode";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('PC Tab Code') . '</th>
						<th class="SortedColumn">' . __('Amount') . '</th>
						<th class="SortedColumn">' . __('Currency') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $MyRow['tabcode'] . '</td>
					<td class="number">' . locale_number_format($MyRow['amount'],0) . '</td>
					<td>' . $MyRow['currency'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function RegularTransfersToShopNotReceived($PreparationTime, $LimitTime, $RootPath){

	$StartDate = date('Y-m-d');
	$StartTime = date('H:i:s');

	if ($StartTime >= $LimitTime){
		$SQL = "SELECT DISTINCT loctransfers.reference,
						loctransfers.shipdate,
						loctransfers.shiploc,
						loctransfers.recloc
				FROM loctransfers,locations
				WHERE  loctransfers.recloc = locations.loccode
					AND loctransfers.pendingqty > 0
					AND loctransfers.shipdate <= '". $StartDate ." " . $PreparationTime . "'
					AND   (locations.typeloc = 'SHOPKL'
						OR locations.typeloc = 'SHOPBL'
						OR locations.typeloc = 'SHOPOU'
						OR locations.typeloc = 'ONLINE')
				ORDER BY loctransfers.reference";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) != 0){
			$TableTitleText = 'Transfers to Shops prepared before ' . date($_SESSION['DefaultDateFormat']) . 
																		' at ' . $PreparationTime . ' but not received by SPG before ' . $LimitTime;
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">
					<thead>
						<tr>
							<th class="SortedColumn">' . __('#') . '</th>
							<th class="SortedColumn">' . __('Transfer') . '</th>
							<th class="SortedColumn">' . __('Date') . '</th>
							<th class="SortedColumn">' . __('From') . '</th>
							<th class="SortedColumn">' . __('To') . '</th>
						</tr>
					</thead>
					<tbody>';
			$i = 1;
			while ($MyRow = DB_fetch_array($Result)) {
				$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . ConvertSQLDateTime($MyRow['shipdate']) . '</td>
						<td>' . $MyRow['shiploc'] . '</td>
						<td>' . $MyRow['recloc'] . '</td>
						</tr>';
				$i++;
			}
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function SamplesNotLongerNeeded($RootPath){

	$SQL = "SELECT locstock.stockid, 
					stockmaster.description, 
					quantity AS qty
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND loccode = 'SAMPR'
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ." 
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET .")
				AND quantity > 0
			ORDER BY locstock.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Samples Not Longer Needed (No More Buy, Discount, Outlet)');
		ShowTableTitle($TableTitleText);
		$TableHeader = '<tr>
							<th class="SortedColumn">' . __('#') . '</th>
							<th class="SortedColumn">' . __('Code') . '</th>
							<th class="SortedColumn">' . __('Description') . '</th>
							<th class="SortedColumn">' . __('Qty of samples') . '</th>
						</tr>';
		echo '<div>';
		echo '<table class="selection">
				<thead>' . $TableHeader . '</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['qty'],0) . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function SPGNotReportingSalesInDays($maxdays){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));

	$SQL = "SELECT salesman.salesmancode,
				salesman.salesmanname,
				www_users.defaultlocation,
				(SELECT orddate
					FROM salesorders
					WHERE salesorders.salesperson = salesman.salesmancode
					ORDER BY orddate DESC
					LIMIT 1) AS lastsale
		FROM salesman, www_users
		WHERE www_users.salesman = salesman.salesmancode
			AND salesman.current = 1	
			AND salesman.salesmancode != '999'
			AND www_users.fullaccess = '17'
			AND www_users.blocked = 0
			AND NOT EXISTS (SELECT *
							FROM salesorders
							WHERE orddate >= '". $StartDate. "'
								AND salesorders.salesperson = salesman.salesmancode)
		ORDER BY salesman.salesmancode";
//	prnMsg($SQL);			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText =  __('Senior or Support SPG with more than ') . $maxdays . __(' days not reporting ANY sales.');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' .  __('SPG') . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . __('Shop') . '</th>
						<th class="SortedColumn">' . __('Last Sale') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($MyRow['lastsale'])){
				$Day = ConvertSQLDate($MyRow['lastsale']);
			}else{
				$Day = "No sale yet";
			}
			echo '<tr class="striped_row">
					<td>' . $MyRow['salesmancode'] . '</td>
					<td>' . $MyRow['salesmanname'] . '</td>
					<td>' . $MyRow['defaultlocation'] . '</td>
					<td>' . $Day . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

function SuppliersWithoutBasicData($RootPath){

	$SQL = "SELECT supplierid,
					suppname
			FROM suppliers
			WHERE address6 = ''";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Suppliers without basic data');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>' . $MyRow['supplierid'] . '</td>
					<td>' . $MyRow['suppname'] . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}
}

function TransferWithWrongInformation($maxdays, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays+1));
	$SQL = "SELECT loctransferid, 
					reference,
					stockid,
					recdate,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = shiploc) AS locfrom,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = recloc) AS locto,
					shipqty AS shippedqty,
					recqty AS receivedqty
			FROM loctransfers
			WHERE  shipdate >= '" . $StartDate . "'
				AND recdate != '1000-01-01 00:00:00'
				AND pendingqty != 0
			ORDER BY recdate ASC, reference ASC, stockid ASC";
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Transfers With Wrong Information during the last ') . $maxdays  . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Reception Date') . '</th>
						<th class="SortedColumn">' . __('Transfer') . '</th>
						<th class="SortedColumn">' . __('From') . '</th>
						<th class="SortedColumn">' . __('To') . '</th>
						<th class="SortedColumn">' . __('Item') . '</th>
						<th class="SortedColumn">' . __('Shipped Qty') . '</th>
						<th class="SortedColumn">' . __('Received Qty') . '</th>
						<th class="SortedColumn">' . __('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$ResultTx = DB_Txn_Begin();
		$LastStockid = "";
		$LastTransfer = "";
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $MyRow['reference'] . '">' . $MyRow['reference'] . '</a>';
			if (($MyRow['stockid'] != $LastStockid) OR ($MyRow['reference'] != $LastTransfer)){
				$SQL = "UPDATE loctransfers SET shipqty = recqty 
						WHERE loctransferid = '".$MyRow['loctransferid'] . "'";
				$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('Unable to fix the wrong information');
				$ResultFix = DB_query($SQL, $ErrMsg, '', true);
				$Action = "Fixed"; 
			}else{
				$SQL = "DELETE FROM loctransfers 
						WHERE loctransferid = '".$MyRow['loctransferid'] . "'";
				$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('Unable to delete the wrong information');
				$ResultDelete = DB_query($SQL, $ErrMsg, '', true);
				$Action = "Deleted";
			}

			$Action = "Fixed";
			echo '<tr class="striped_row">
					<td class="number">' . locale_number_format($MyRow['loctransferid'],0) . '</td>
					<td>' . ConvertSQLDateTime($MyRow['recdate']) . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['locfrom'] . '</td>
					<td>' . $MyRow['locto'] . '</td>
					<td>' . $MyRow['stockid'] . '</td>
					<td class="number">' . locale_number_format($MyRow['shippedqty'],0) . '</td>
					<td class="number">' . locale_number_format($MyRow['receivedqty'],0) . '</td>
					<td>' . $Action . '</td>
					</tr>';
			$LastStockid = $MyRow['stockid'];
			$LastTransfer = $MyRow['reference'];
		}
		$ResultTx = DB_Txn_Commit();
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

function UsersNotLoggingIn($maxdays, $Type, $RootPath){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays-1)) ;
	
	if ($Type=='SPGSUPPORT'){
		$WhereType = " AND fullaccess = 22";
	}else{
		$WhereType = " AND fullaccess != 22";
	}
	
	$SQL = "SELECT userid,
				realname,
				lastvisitdate
			FROM www_users
			WHERE lastvisitdate IS NOT NULL
				AND DATE(lastvisitdate) < '" . $StartDate . "'
				AND userid NOT LIKE '%999%'
				AND userid <> 'TestUser'" . $WhereType;
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($Type=='SPGSUPPORT'){
			$TableTitleText = __('SPG Support webERP users not logging in for more than ') . $maxdays . __(' days.');
		}else{
			$TableTitleText = __('Regular webERP users not logging in for more than ') . $maxdays . __(' days.');
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' .  __('User ID') . '</th>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . __('Last Login') . '</th>
						<th>' . __('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/KLUserDelete.php?UserID=' . $MyRow['userid'] . '">' . 'Delete' . '</a>';
			echo '<tr class="striped_row">
					<td>' . $MyRow['userid'] . '</td>
					<td>' . $MyRow['realname'] . '</td>
					<td>' . ConvertSQLDate($MyRow['lastvisitdate']) . '</td>
					<td>' . $CodeLink . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

function ValueStockLocation($Location, $minpcs, $maxpcs, $minvalue, $maxvalue){
/*	$minpcs = $optimalpcs * (1 - $varpcs);
	$maxpcs = $optimalpcs * (1 + $varpcs);
	$minvalue = $optimalvalue * (1 - $varvalue);
	$maxvalue = $optimalvalue * (1 + $varvalue);
*/	
	$SQL = "SELECT 
				locations.locationname,
				SUM(locstock.quantity) AS qtyonhand,
				SUM(locstock.quantity *(stockmaster.actualcost)) AS valuetotal
			FROM stockmaster,
				stockcategory,
				locations,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
				AND locations.loccode = '" . $Location . "'
				AND stockmaster.categoryid=stockcategory.categoryid
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES . "
				AND locstock.quantity!=0
				AND locstock.loccode = '" . $Location . "'";
				
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	
	if ($MyRow['qtyonhand'] < $minpcs){
		$WarningTitleText = "Number of items at " . $MyRow['locationname'] . " is BELOW the minimum. QOH = " . locale_number_format($MyRow['qtyonhand'],0) . " pcs. Minimum = " . locale_number_format($minpcs,0) . " pcs";
        ShowWarningTitle($WarningTitleText);
	}
	if ($MyRow['qtyonhand'] > $maxpcs){
		$WarningTitleText = "Number of items at " . $MyRow['locationname'] . " is OVER the maximum. QOH = " . locale_number_format($MyRow['qtyonhand'],0) . " pcs. Maximum = " . locale_number_format($maxpcs,0) . " pcs";
        ShowWarningTitle($WarningTitleText);
	}
}

function WrongItemsOnPurchaseOrders($RootPath){
/* EXPLAIN SQL 2014-05-30 */
	$SQL = "SELECT purchorderdetails.orderno,
				purchorderdetails.itemcode,
				stockmaster.description,
				purchorderdetails.quantityord
			FROM purchorderdetails, purchorders, stockmaster
			WHERE stockmaster.stockid = purchorderdetails.itemcode
				AND purchorderdetails.orderno = purchorders.orderno
				AND purchorderdetails.completed = 0
				AND purchorders.status NOT IN ('Cancelled', 'Rejected')
				AND (  stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ."
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."
					OR stockmaster.discontinued = 1)
			ORDER BY purchorderdetails.orderno,
					purchorderdetails.itemcode";

	$Result = DB_query($SQL);
	$ShowHeader = true;
	if (DB_num_rows($Result) != 0){
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if (true){
				if ($ShowHeader){
					$TableTitleText = __('Wrong items (No More Purchasing, Discount or Obsolete) in Active POs');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . __('#') . '</th>
									<th class="SortedColumn">' . __('PO') . '</th>
									<th class="SortedColumn">' . __('Code') . '</th>
									<th class="SortedColumn">' . __('Description') . '</th>
									<th class="SortedColumn">' . __('QOO') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = false;
				}
				$CodeLink = '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $MyRow['itemcode'] . '">' . $MyRow['itemcode'] . '</a>';
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td class="number">' . locale_number_format($MyRow['orderno'],0) . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($MyRow['quantityord'],0) . '</td>
						</tr>';
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function WrongItemsOnWorkOrders($RootPath){
/* EXPLAIN SQL 2014-05-30 */
	$SQL = "SELECT workorders.wo,
				woitems.stockid,
				stockmaster.description,
				woitems.qtyreqd
			FROM woitems, workorders, stockmaster
			WHERE stockmaster.stockid = woitems.stockid
				AND woitems.wo = workorders.wo
				AND workorders.closed = 0
				AND (  stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ."
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."
					OR stockmaster.discontinued = 1)
			ORDER BY woitems.wo,
					woitems.stockid";

	$Result = DB_query($SQL);
	$ShowHeader = true;
	if (DB_num_rows($Result) != 0){
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if (true){
				if ($ShowHeader){
					$TableTitleText = __('Wrong items (No More Purchasing, Discount or Obsolete) in Active Work Orders');
					ShowTableTitle($TableTitleText);
					echo '<div>';
					echo '<table class="selection">
							<thead>
								<tr>
									<th class="SortedColumn">' . __('#') . '</th>
									<th class="SortedColumn">' . __('WO') . '</th>
									<th class="SortedColumn">' . __('Code') . '</th>
									<th class="SortedColumn">' . __('Description') . '</th>
									<th class="SortedColumn">' . __('Qty') . '</th>
								</tr>
							</thead>
							<tbody>';
					$ShowHeader = false;
				}
				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td class="number">' . locale_number_format($MyRow['wo'],0) . '</td>
						<td>' . $MyRow['stockid'] . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($MyRow['qtyreqd'],0) . '</td>
						</tr>';
				$i++;
			}
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function OpenCartOrdersByStatus($Status, $RootPath ){
	$SQL = "SELECT 	oc_order.order_id,
				oc_order.store_name,
				oc_order.firstname,
				oc_order.lastname,
				oc_order.currency_code,
				oc_order.date_modified
			FROM oc_order
			WHERE oc_order.order_status_id = '" . $Status . "'
			ORDER BY oc_order.date_modified";
	$Result = DB_query_oc($SQL);
	if (DB_num_rows($Result) != 0){
		$ShowHeader = true;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowHeader){
				if ($Status == OPENCART_ORDER_STATUS_PENDING){
					$StatusText = "Pending";
				}else if ($Status == OPENCART_ORDER_STATUS_PROCESSING){
					$StatusText = "Processing";
				}else if ($Status == OPENCART_ORDER_STATUS_SHIPPED){
					$StatusText = "Shipped";
				}else{
					$StatusText = "Unknown";
				}
				$TableTitleText = $StatusText .' OpenCart Online Orders';
				ShowTableTitle($TableTitleText);
				echo '<div>';
				echo '<table class="selection">
						<thead>
							<tr>
								<th class="SortedColumn">' . __('#') . '</th>
								<th class="SortedColumn">' . __('#Order') . '</th>
								<th class="SortedColumn">' . __('Last Modification') . '</th>
								<th class="SortedColumn">' . __('Shop') . '</th>
								<th class="SortedColumn">' . __('Customer name') . '</th>
							</tr>
						</thead>
						<tbody>';
				$ShowHeader = false;
			}
			if ($MyRow['currency_code'] == "IDR"){
				$RoundingDecimals = 0;
			}else{
				$RoundingDecimals = 2;
			}
			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . locale_number_format($MyRow['order_id'],0) . '</td>
					<td>' . ConvertSQLDateTime($MyRow['date_modified']) . '</td>
					<td>' . $MyRow['store_name'] . '</td>
					<td>' . $MyRow['firstname'] . " " . $MyRow['lastname'] . '</td>
					</tr>';
			$i++;
		}
		if (!$ShowHeader){
			echo '</tbody>
				</table>
				</div>';
		}
	}
}

function InternalBankTransfers($Company, 
							$DanamonAccount, $DanamonMin, $DanamonMax,
							$MandiriAccount, $MandiriMin, $MandiriMax,
							$BCAAccount, $BCAMin, $BCAMax,
							$BNIAccount, $BNIMin, $BNIMax, 
							$TokopediaAccount, $TokopediaMin, $TokopediaMax, 
							$ShopeeAccount, $ShopeeMin, $ShopeeMax, 
							$MidtransAccount, $MidtransMin, $MidtransMax, 
							$TransferBlockFromBank,
							$TransferBlockFromOnline,
							$Period){

	$SaldoDanamon = GetGLAccountBalance($DanamonAccount, $Period);
	if ($SaldoDanamon <= $DanamonMin){
		// Danamon is below minimum balance... transfer from other banks until the Max Danamon
		$TransferNeededDanamon = $DanamonMax - $SaldoDanamon;

		// let's check if we can transfer from any bank account in order of preference
		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$TokopediaAccount, 
															"Tokopedia",
															$TokopediaMin, 
															$TokopediaMax,
															$TransferBlockFromOnline,
															$Period
															);
		
		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$ShopeeAccount, 
															"Shopee",
															$ShopeeMin, 
															$ShopeeMax,
															$TransferBlockFromOnline,
															$Period
															);

		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$MidtransAccount, 
															"Midtrans",
															$MidtransMin, 
															$MidtransMax,
															$TransferBlockFromOnline,
															$Period
															);
		
		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$MandiriAccount, 
															"Mandiri",
															$MandiriMin, 
															$MandiriMax,
															$TransferBlockFromBank,
															$Period
															);

		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$BCAAccount, 
															"BCA",
															$BCAMin, 
															$BCAMax,
															$TransferBlockFromBank,
															$Period
															);

		$TransferNeededDanamon = CalculateTransferFromBankToDanamon($Company, 
															$TransferNeededDanamon,
															$BNIAccount, 
															"BNI",
															$BNIMin, 
															$BNIMax,
															$TransferBlockFromBank,
															$Period
															);
	}
}

function CalculateTransferFromBankToDanamon($Company, 
											$TransferNeededDanamon,
											$Account, 
											$AccountName,
											$SaldoMin, 
											$SaldoMax,
											$TransferBlock,
											$Period){
	if($TransferNeededDanamon > 0){
		$Saldo = GetGLAccountBalance($Account, $Period);
		if ($Saldo >= $SaldoMax){
			$AvailableForTransfer = $Saldo - $SaldoMin;
			$Transfer = min($AvailableForTransfer, $TransferNeededDanamon);
			$Transfer = round_down_multiple_of($Transfer, $TransferBlock);
			if ($Transfer > 0){
				$WarningTitleText = "Transfer ".locale_number_format($Transfer,0)." IDR from " . $AccountName.  " " . $Company . " to Danamon ". $Company;
   				ShowWarningTitle($WarningTitleText);
				$TransferNeededDanamon = $TransferNeededDanamon - $Transfer;
			}
		} 
	}
	return $TransferNeededDanamon;
}

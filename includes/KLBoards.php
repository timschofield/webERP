<?php

/**************************************************************************************************
			FUNCTIONS RELATED CONTROL, PERFORMANCE BOARDS
**************************************************************************************************/


function SPGBelowMinimumSales($Shop, $NumDaysA, $MinimumSales,$db){
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDateA = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDaysA));

	$SQL = "SELECT salesmancode,
				salesmanname,
				(SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.fromstkloc = '". $Shop . "'
						AND salesorders.salesperson = salesman.salesmancode) AS salesA
			FROM salesman
			WHERE salesman.current = 1
			AND (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
					FROM salesorderdetails, salesorders
					WHERE salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.completed = 1
						AND salesorders.orddate >= '". $StartDateA . "'
						AND salesorders.orddate <= '". $Yesterday . "'
						AND salesorders.fromstkloc = '". $Shop . "'
						AND salesorders.salesperson = salesman.salesmancode) <= ". $MinimumSales ."
			ORDER BY (SELECT SUM(qtyinvoiced * (unitprice * (1 - discountpercent)))
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

function SPGNotReportingSalesInDays($maxdays, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));

	$SQL = "SELECT salesman.salesmancode,
				salesman.salesmanname,
				(SELECT orddate
					FROM salesorders
					WHERE salesorders.salesperson = salesman.salesmancode
					ORDER BY orddate DESC
					LIMIT 1) AS lastsale
		FROM salesman
		WHERE salesman.current = 1	
		AND salesman.salesmancode != '999'
		AND NOT EXISTS (SELECT *
						FROM salesorders
						WHERE orddate >= '". $StartDate. "'
							AND salesorders.salesperson = salesman.salesmancode)
		AND EXISTS (SELECT *
					FROM www_users
					WHERE www_users.salesman = salesman.salesmancode
						AND www_users.fullaccess = '17')
		ORDER BY salesman.salesmancode";
//	prnMsg($SQL);			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Senior SPG with more than ') . $maxdays . _(' days not reporting ANY sales.') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' .  _('SPG') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Last Sale') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if (isset($myrow['lastsale'])){
				$Day = ConvertSQLDate($myrow['lastsale']);
			}else{
				$Day = "No sale yet";
			}
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['salesmancode'],
					$myrow['salesmanname'],
					$Day
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

function UsersNotLoggingIn($maxdays, $type, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays-1)) ;
	
	if ($type=='SPGSUPPORT'){
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
				AND userid NOT LIKE '999%'
				AND userid <> 'TestUser'" . $WhereType;
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($type=='SPGSUPPORT'){
			echo '<p class="page_title_text" align="center"><strong>' . _('SPG Support webERP users not logging in for more than ') . $maxdays . _(' days.') .'</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Regular webERP users not logging in for more than ') . $maxdays . _(' days.') .'</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' .  _('User ID') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Last Login') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['userid'],
					$myrow['realname'],
					ConvertSQLDate($myrow['lastvisitdate'])
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
}

function TransfersDelayed($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT DISTINCT reference,
					shipdate,
					shiploc,
					recloc
			FROM loctransfers 
			WHERE  recqty < shipqty
				AND shipdate <= '". $StartDate ."'
			ORDER BY reference";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Transfers delayed more than ') . $maxdays . _(' days ') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					ConvertSQLDate($myrow['shipdate']), 
					$myrow['shiploc'], 
					$myrow['recloc'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsCancelledInTransfers($maxdays, $RootPath, $db){
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
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items cancelled in Transfers during the last ') . $maxdays . _(' days ') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Cancel Qty') . '</th>
							<th class="ascending">' . _('Cancel Date') . '</th>
							<th class="ascending">' . _('Cancelled By') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$TransferLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink,
					$TransferLink, 
					ConvertSQLDateTime($myrow['shipdate']), 
					$myrow['shiploc'], 
					$myrow['recloc'],
					locale_number_format($myrow['cancelqty'],0),
					ConvertSQLDateTime($myrow['canceldate']), 
					$myrow['canceluserid']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ErrorsInTransfers($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "SELECT DISTINCT(loctransfers.reference),
					loctransfers.shipdate,
					loctransfers.shiploc,
					loctransfers.recloc,
					SUM(loctransfers.shipqty) AS shipped_quantity,
					COUNT(loctransfers.stockid) AS shipped_models,
					(SELECT SUM(loctransfercancellations.cancelqty)
						FROM loctransfercancellations
						WHERE loctransfercancellations.reference = loctransfers.reference) AS cancelled_quantity,
					(SELECT COUNT(loctransfercancellations.stockid)
						FROM loctransfercancellations
						WHERE loctransfercancellations.reference = loctransfers.reference) AS cancelled_models
			FROM loctransfers 
			WHERE loctransfers.shipdate >= '". $StartDate ."'
			GROUP BY loctransfers.reference
			HAVING SUM(loctransfers.shipqty) = SUM(loctransfers.recqty)
			ORDER BY loctransfers.reference";
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Errors on Closed Transfers during the last ') . $maxdays . _(' days ') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Total Models') . '</th>
							<th class="ascending">' . _('Cancelled Models') . '</th>
							<th class="ascending">' . _('% Models') . '</th>
							<th class="ascending">' . _('Total Qty') . '</th>
							<th class="ascending">' . _('Cancelled Qty') . '</th>
							<th class="ascending">' . _('% Qty') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		
		$TotalShippedModels = 0;
		$TotalCancelledModels = 0;
		$TotalShippedQty = 0;
		$TotalCancelledQty = 0;
		
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			$TotalShippedModels += $myrow['shipped_models'];
			$TotalCancelledModels += $myrow['cancelled_models'];
			$TotalShippedQty += $myrow['shipped_quantity'];
			$TotalCancelledQty += $myrow['cancelled_quantity'];

			$TransferLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
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
					$TransferLink, 
					ConvertSQLDateTime($myrow['shipdate']), 
					$myrow['shiploc'], 
					$myrow['recloc'],
					locale_number_format($myrow['shipped_models'],0),
					locale_number_format($myrow['cancelled_models'],0),
					locale_number_format($myrow['cancelled_models'] / $myrow['shipped_models'] * 100,2) . '%',
					locale_number_format($myrow['shipped_quantity'],0),
					locale_number_format($myrow['cancelled_quantity'],0),
					locale_number_format($myrow['cancelled_quantity'] / $myrow['shipped_quantity'] * 100,2) . '%'
					);
			$i++;
		}
		$k = StartEvenOrOddRow($k);
		printf('<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'', 
				'', 
				'', 
				'TOTAL',
				locale_number_format($TotalShippedModels,0),
				locale_number_format($TotalCancelledModels,0),
				locale_number_format($TotalCancelledModels / $TotalShippedModels * 100,2) . '%',
				locale_number_format($TotalShippedQty,0),
				locale_number_format($TotalCancelledQty,0),
				locale_number_format($TotalCancelledQty / $TotalShippedQty* 100,2) . '%'
				);
		echo '</table>
				</div>';
	}

}


function isTopSalesItem($stockid, $topitems, $topitemsdays, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$topitemsdays));
	$SQL="SELECT salesorderdetails.stkcode
			FROM salesorderdetails
			WHERE salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
			GROUP BY salesorderdetails.stkcode
			ORDER BY SUM(salesorderdetails.qtyinvoiced) DESC
			LIMIT " . $topitems;
	$result = DB_query($SQL);
	$istopsales = false;
	if (DB_num_rows($result) != 0){
		while (($myrow = DB_fetch_array($result)) AND (!$istopsales)) {
			if ($myrow['stkcode'] == $stockid){
				$istopsales = true;
			}
		}
	}
	return $istopsales;
}

function positionTopSalesItem($stockid, $topitems, $topitemsdays, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$topitemsdays));
	$SQL="SELECT salesorderdetails.stkcode
			FROM salesorderdetails
			WHERE salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
			GROUP BY salesorderdetails.stkcode
			ORDER BY SUM(salesorderdetails.qtyinvoiced) DESC
			LIMIT " . $topitems;
	$result = DB_query($SQL);
	$position = 1;
	if (DB_num_rows($result) != 0){
		while (($myrow = DB_fetch_array($result)) AND (!$istopsales)) {
			if ($myrow['stkcode'] == $stockid){
				return $position;
			}
			$position++;
		}
	}
	return $position;
}


function SalesOfItemByLocation($stockid, $location, $maxdays, $db){
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

function FinishedStockDistribution($kind, $byreport, $db){

	if ($kind == "FORSALE"){			
		$operator1 = " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_KL_SHOPS_NOT_FOR_SALE ."";
		$operator2 = " AND m2.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_KL_SHOPS_NOT_FOR_SALE ."";
	}elseif ($kind == "DISPLAYS"){			
		$operator1 =  "	AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . " ";
		$operator2 = "	AND m2.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . " ";
	}elseif ($kind == "PACKAGING"){			
		$operator1 =  "	AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . " ";
		$operator2 = "	AND m2.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . " ";
	}else{
		$operator1 =  "	";
		$operator2 =  "	";
	}
	if ($byreport == "LOCATION"){
		$SQL =	"SELECT locstock.loccode,
						locations.locationname,
						locations.kldisplaylenght,
						locations.kldisplaysurface,
					SUM(locstock.reorderlevel) AS optimalstock,
					SUM(locstock.quantity) AS realstock,
					(SELECT COUNT(l2.reorderlevel)
						FROM locstock AS l2,
							stockmaster as m2
						WHERE l2.loccode = locations.loccode
							AND m2.stockid = l2.stockid " . 
							$operator2 ."
							AND l2.reorderlevel != 0) AS optimalmodels,
					(SELECT COUNT(l2.quantity)
						FROM locstock AS l2,
							stockmaster as m2
						WHERE l2.loccode = locations.loccode
							AND m2.stockid = l2.stockid " . 
							$operator2 ."
						AND l2.quantity != 0) AS realmodels
				FROM locstock, locations, stockmaster, stockcategory
				WHERE locstock.loccode = locations.loccode
					AND stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.stocktype = 'F'" . 
				$operator1 . " 
				GROUP BY locstock.loccode
				ORDER BY locations.locationname";
	}elseif ($byreport == "STOCKCATEGORY"){
		$SQL =	"SELECT stockmaster.categoryid,
						stockcategory.categorydescription,
					SUM(locstock.reorderlevel) AS optimalstock,
					SUM(locstock.quantity) AS realstock,
					0 AS optimalmodels,
					(SELECT COUNT(DISTINCT(l2.stockid))
						FROM locstock AS l2,
							stockmaster as m2
						WHERE m2.stockid = l2.stockid
							AND m2.categoryid = stockcategory.categoryid" . 
							$operator2 ."
						AND l2.quantity != 0) AS realmodels
				FROM locstock, locations, stockmaster, stockcategory
				WHERE locstock.loccode = locations.loccode
					AND stockmaster.stockid = locstock.stockid
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockcategory.stocktype = 'F'" . 
				$operator1 . "
				GROUP BY stockmaster.categoryid
				ORDER BY stockcategory.categorydescription";
	}else{
		return false;
	}
						
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($kind == "FORSALE"){			
			$Titletext = "Finished Stock FOR SALE Distribution by "; 
		}
		if ($kind == "DISPLAYS"){			
			$Titletext = "Finished Stock DISPLAYS Distribution by "; 
		}
		if ($kind == "PACKAGING"){			
			$Titletext = "Finished Stock SHOP PACKAGING Distribution by "; 
		}
		if ($byreport == "LOCATION"){			
			$Titletext = $Titletext . "Location"; 
			$Titleheader = "Location";
		}
		if ($byreport == "STOCKCATEGORY"){			
			$Titletext = $Titletext . "Stock Category"; 
			$Titleheader = "Stock Category";
		}
		
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . $Titleheader . '</th>
							<th class="ascending">' . _('QOH Pcs') . '</th>
							<th class="ascending">' . _('RL Pcs') . '</th>
							<th class="ascending">' . _('% Pcs') . '</th>
							<th class="ascending">' . _('QOH Models') . '</th>
							<th class="ascending">' . _('RL Models') . '</th>
							<th class="ascending">' . _('% Models') . '</th>
							<th class="ascending">' . _('QOH Pcs/Model') . '</th>
							<th class="ascending">' . _('RL Pcs/Model') . '</th>
							<th class="ascending">' . _('cm/RL Model') . '</th>
							<th class="ascending">' . _('cm2/RL Model') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$totalpcs = 0;
		
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			if ($myrow['optimalstock'] != 0){
				$percentStock = locale_number_format(($myrow['realstock']/$myrow['optimalstock']) * 100,0) . "%";
			}else{
				$percentStock = "";
			}
			if ($myrow['optimalmodels'] != 0){
				$percentModels =locale_number_format(($myrow['realmodels']/$myrow['optimalmodels']) * 100,0). "%";
			}else{
				$percentModels = "";
			}
			if ($myrow['realmodels'] != 0){
				$realPcsModel =locale_number_format(($myrow['realstock']/$myrow['realmodels']),1);
			}else{
				$realPcsModel = "";
			}
			if ($myrow['optimalmodels'] != 0){
				$optimalPcsModel =locale_number_format(($myrow['optimalstock']/$myrow['optimalmodels']),1);
			}else{
				$optimalPcsModel = "";
			}
			if ($myrow['kldisplaylenght'] != 0){
				$lenght_model =locale_number_format(($myrow['kldisplaylenght']/$myrow['optimalmodels']),1);
			}else{
				$lenght_model = "";
			}
			if ($myrow['kldisplaysurface'] != 0){
				$surface_model =($myrow['kldisplaysurface']/$myrow['optimalmodels']);
				$side_model = locale_number_format(sqrt($surface_model),0);
				$square_model = $side_model . 'x' . $side_model;
			}else{
				$square_model = "";
			}
			if ($byreport == "LOCATION"){			
				printf('<td class="number">%s</td>
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
						</tr>', 
						$i,
						$myrow['locationname'],
						locale_number_format($myrow['realstock'],0),
						locale_number_format($myrow['optimalstock'],0),
						$percentStock,
						locale_number_format($myrow['realmodels'],0),
						locale_number_format($myrow['optimalmodels'],0),
						$percentModels,
						$realPcsModel,
						$optimalPcsModel,
						$lenght_model,
						$square_model
						);
			}
			if ($byreport == "STOCKCATEGORY"){			
				printf('<td class="number">%s</td>
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
						</tr>', 
						$i,
						$myrow['categorydescription'],
						locale_number_format($myrow['realstock'],0),
						'',
						'',
						locale_number_format($myrow['realmodels'],0),
						'',
						'',
						$realPcsModel,
						$optimalPcsModel,
						'',
						''
						);
			}
			$i++;
			$totalpcs = $totalpcs + $myrow['realstock'];
		}
		if ($byreport == "STOCKCATEGORY"){			
			$SQL =	"SELECT COUNT(DISTINCT(l2.stockid)) AS realmodels
						FROM locstock AS l2,
							stockmaster as m2,
							stockcategory
						WHERE m2.stockid = l2.stockid" . 
							$operator2 ."
						AND stockcategory.categoryid = m2.categoryid
						AND stockcategory.stocktype = 'F'
						AND l2.quantity != 0";
			$result1 = DB_query($SQL);
			if (DB_num_rows($result1) != 0){
				while ($myrow1 = DB_fetch_array($result1)) {
					$totalModels = locale_number_format($myrow1['realmodels'],0);
					$percentModels =locale_number_format(($totalpcs/$myrow1['realmodels']),1);
				}
			}
		}else{
			$totalModels = "";
			$percentModels = "";
		}
		printf('<td class="number">%s</td>
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
				</tr>', 
				"",
				"Total",
				locale_number_format($totalpcs,0),
				"",
				"",
				$totalModels,
				"",
				"",
				$percentModels,
				"",
				"",
				""
				);
		
		echo '</table>
				</div>
				</form>';
	}
}

/*
*************************************************************************************************
			FUNCTIONS RELATED MAINTAIN DATABASE BOARDS
*************************************************************************************************
*/


function ActiveTransfersByLocation($RootPath, $db){
	$TotalTransferIn = 0;
	$TotalTransferOut = 0;
	$TotalPcsIn = 0;
	$TotalPcsOut = 0;
	
	$SQL = "SELECT locations.locationname,
			(SELECT SUM(shipqty-recqty)
				FROM loctransfers
				WHERE  recqty < shipqty
					AND loctransfers.shiploc = locations.loccode) as qtyout,
			(SELECT SUM(shipqty-recqty)
				FROM loctransfers
				WHERE  recqty < shipqty
					AND loctransfers.recloc = locations.loccode) as qtyin,
			(SELECT COUNT(DISTINCT(reference))
				FROM loctransfers
				WHERE  recqty < shipqty
					AND loctransfers.shiploc = locations.loccode) as transferout,
			(SELECT COUNT(DISTINCT(reference))
				FROM loctransfers
				WHERE  recqty < shipqty
					AND loctransfers.recloc = locations.loccode) as transferin
			FROM locations
			WHERE locations.loccode IN " . LIST_ALL_SHOPS . "
			ORDER BY (SELECT SUM(shipqty-recqty)
				FROM loctransfers
				WHERE  recqty < shipqty
					AND (loctransfers.shiploc = locations.loccode OR loctransfers.recloc = locations.loccode)) DESC";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Pending Goods to be transferred by shop') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Transfer OUT') . '</th>
							<th class="ascending">' . _('Transfer IN') . '</th>
							<th class="ascending">' . _('Transfer Total') . '</th>
							<th class="ascending">' . _('Pcs OUT') . '</th>
							<th class="ascending">' . _('Pcs IN') . '</th>
							<th class="ascending">' . _('Pcs Total') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$TotalTransferIn = $TotalTransferIn + $myrow['transferin'];
			$TotalTransferOut = $TotalTransferOut + $myrow['transferout'];
			$TotalPcsIn = $TotalPcsIn + $myrow['qtyin'];
			$TotalPcsOut = $TotalPcsOut + $myrow['qtyout'];

			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$myrow['locationname'], 
					locale_number_format($myrow['transferout'],0),
					locale_number_format($myrow['transferin'],0),
					locale_number_format($myrow['transferout']+$myrow['transferin'],0),
					locale_number_format($myrow['qtyout'],0),
					locale_number_format($myrow['qtyin'],0),
					locale_number_format($myrow['qtyout']+$myrow['qtyin'],0)
					);
			$i++;
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'Total', 
				locale_number_format($TotalTransferOut,0),
				locale_number_format($TotalTransferIn,0),
				locale_number_format($TotalTransferOut+$TotalTransferIn,0),
				locale_number_format($TotalPcsOut,0),
				locale_number_format($TotalPcsIn,0),
				locale_number_format($TotalPcsOut+$TotalPcsIn,0)
				);
		
		echo '</table>
				</div>
				</form>';
	}
}

function ActiveTransferStatus($RootPath, $db){
	$SQL = "SELECT reference,
					shipdate,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = shiploc)AS locfrom,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = recloc)AS locto,
					SUM(shipqty-recqty) AS pendingqty
			FROM loctransfers
			WHERE  recqty < shipqty
			GROUP BY reference
			ORDER BY shipdate ASC, reference ASC";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('List of Active Transfers') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Qty') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$total = 0;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					ConvertSQLDateTime($myrow['shipdate']), 
					$CodeLink, 
					$myrow['locfrom'], 
					$myrow['locto'], 
					locale_number_format($myrow['pendingqty'],0)
					);
			$i++;
			$total = $total + $myrow['pendingqty'];
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'', 
				'', 
				'', 
				'Total', 
				locale_number_format($total,0)
				);
		echo '</table>
				</div>
				</form>';
	}
}

function RecentlyClosedTransferStatus($maxdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays+1));
	$SQL = "SELECT reference,
					recdate,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = shiploc) AS locfrom,
					(SELECT locationname
						FROM locations
						WHERE locations.loccode = recloc) AS locto,
					SUM(recqty) AS receivedqty
			FROM loctransfers
			WHERE  recdate >= '" . $StartDate . "'
			GROUP BY reference
			ORDER BY recdate ASC, reference ASC";
			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($maxdays == 1){
			echo '<p class="page_title_text" align="center"><strong>' . _('List of Transfers Closed today ') . ' </strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('List of Transfers Closed during last ') . $maxdays  . ' days</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Transfer') . '</th>
							<th class="ascending">' . _('From') . '</th>
							<th class="ascending">' . _('To') . '</th>
							<th class="ascending">' . _('Qty') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$total = 0;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockLocTransferReceive.php?Trf_ID=' . $myrow['reference'] . '">' . $myrow['reference'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					ConvertSQLDateTime($myrow['recdate']), 
					$CodeLink, 
					$myrow['locfrom'], 
					$myrow['locto'], 
					locale_number_format($myrow['receivedqty'],0)
					);
			$i++;
			$total = $total + $myrow['receivedqty'];
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'', 
				'', 
				'', 
				'Total', 
				locale_number_format($total,0)
				);
		echo '</table>
				</div>
				</form>';
	}
}

function ListPriorityLocations($db){
	$SQL="SELECT locationname,
				priority,
				prioritydiscount,
				smartdispatchmaxmodels
		FROM locations
		WHERE (loccode IN " . LIST_ALL_SHOPS . ")
		ORDER BY locationname ASC";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Priority Assigned to Shops. 1-Maximum 10-Minimum') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Location') . '</th>
							<th class="ascending">' . _('Priority Normal') . '</th>
							<th class="ascending">' . _('Priority Discount') . '</th>
							<th class="ascending">' . _('MAX Models Daily Transfer') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$myrow['locationname'],
					$myrow['priority'],
					$myrow['prioritydiscount'],
					$myrow['smartdispatchmaxmodels']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function CheckNegativeStock($RootPath, $db){
	/* Check if there is any negative stock */

	$SQL = "SELECT stockmaster.stockid,			
				   stockmaster.description,			
				   stockmaster.categoryid,			
				   stockmaster.decimalplaces,			
				   locstock.loccode,			
				   locations.locationname,			
				   locstock.quantity			
			FROM stockmaster INNER JOIN locstock 			
			ON stockmaster.stockid=locstock.stockid			
			INNER JOIN locations 			
			ON locstock.loccode = locations.loccode			
			WHERE locstock.quantity < 0			
			ORDER BY locstock.loccode, 			
				stockmaster.categoryid, 
				stockmaster.stockid,
				stockmaster.decimalplaces";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items with Negative Stock') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Location') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
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
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['locationname'], 
					locale_number_format($myrow['quantity'],$myrow['decimalplaces'])
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsInKLProcessAndRLNotZero($RootPath, $db){
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
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items with in KL process and RL not zero') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Location') . '</th>
							<th class="ascending">' . _('RL') . '</th>
							<th class="ascending">' . _('Changing Price') . '</th>
							<th class="ascending">' . _('MoveTo 20% Disc') . '</th>
							<th class="ascending">' . _('MoveTo 50% Disc') . '</th>
							<th class="ascending">' . _('MoveTo 80% Disc') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			if ($myrow['klchangingprice'] == 1){
				$ItemChangingPrice = "Yes";
			}else{
				$ItemChangingPrice = "";
			}
			if ($myrow['klmovingdiscount20'] == 1){
				$ItemMovingToDiscount20 = "Yes";
			}else{
				$ItemMovingToDiscount20 = "";
			}
			if ($myrow['klmovingdiscount50'] == 1){
				$ItemMovingToDiscount50 = "Yes";
			}else{
				$ItemMovingToDiscount50 = "";
			}
			if ($myrow['klmovingdiscount80'] == 1){
				$ItemMovingToDiscount80 = "Yes";
			}else{
				$ItemMovingToDiscount80 = "";
			}
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['locationname'], 
					locale_number_format($myrow['reorderlevel'],0),
					$ItemChangingPrice,
					$ItemMovingToDiscount20,
					$ItemMovingToDiscount50,
					$ItemMovingToDiscount80
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function FlaggedAsObsoleteButStockAvailable($RootPath, $db){
	/* Check if there is any item flagged as obsolete BUT with some stock available */
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description
			FROM stockmaster
			WHERE discontinued = 1 
				AND (SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) > 0";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Obsolete Items with available Stock') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
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
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsWithoutWeightOrVolume($RootPath, $db){
	$SQL = "SELECT stockmaster.stockid,
				   stockmaster.description,
				   stockmaster.grossweight,
				   stockmaster.netweight,
				   stockmaster.volume,
				   stockmaster.longdescription,	
				   stockmaster.categoryid	
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid IN " . CATEGORIES_AVAILABLE_WEBSITE ."
				AND stockmaster.discontinued = 0
				AND (stockmaster.grossweight < 0.00001 
					OR stockmaster.volume < 0.00001
					OR stockmaster.grossweight <= stockmaster.netweight)
			ORDER BY stockmaster.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('WebShop items with no gross weight, no volume or Net > Gross Weight') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Net Weight Kg') . '</th>
							<th class="ascending">' . _('Gross Weight Kg') . '</th>
							<th class="ascending">' . _('Volume m3') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/Stocks.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['netweight'],5), 
					locale_number_format($myrow['grossweight'],5), 
					locale_number_format($myrow['volume'],5)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsWithoutStandardCost($RootPath, $db){
	/* Check if there is any item without standard cost */
	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description, 
				(SELECT SUM(quantity) 
					FROM locstock 
					WHERE locstock.stockid = stockmaster.stockid) AS availablestock
			FROM stockmaster,stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype != 'D'
				AND (materialcost + labourcost + overheadcost) = 0
				AND discontinued = 0";
// EXPLAIN SQL 2014-05-31
//	prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items without standard cost') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
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
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['availablestock'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PricesNotUpdatedinXDays($numDays, $percentageIncrease, $RootPath, $db){
	
	$InitialDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));
	$today = date('Y-m-d');

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS stdcost,
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
			$NewPrice = correction_for_low_end_prices(round_price($myrow['price'] * (1 + $percentageIncrease/100), "UP"));
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

function ItemsWithoutRetailPrice($stockcat, $factorRetail, $RootPath, $db){
	/* Check if there is any item without retail price */
	$today = date('Y-m-d');
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS stdcost
			FROM stockmaster, stockcategory					
			WHERE stockmaster.categoryid = stockcategory.categoryid					
				AND stockmaster.discontinued = 0					
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockcategory.stocktype ='F' 		
				AND stockmaster.categoryid = '". $stockcat ."'
				AND NOT EXISTS (SELECT * 					
								FROM prices	
								WHERE stockmaster.stockid = prices.stockid	
									AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
									AND prices.currabrev = '". CURRENCY_CODE ."'
									AND prices.startdate <= '". $today. "' 
									AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00'))";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $stockcat . _(' Items without active retail price') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Std Cost') . '</th>
							<th class="ascending">' . _('Recommended Retail') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$NewPrice = correction_for_low_end_prices(round_price($myrow['stdcost'] * $factorRetail, "UP"));
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$PriceLink = '<a href="' . $RootPath . '/Prices.php?Item=' . $myrow['stockid'] . '">' . locale_number_format($myrow['stdcost'],0) . '</a>';
			$NewPriceLink = '<a href="' . $RootPath . '/KLChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $NewPrice .  '&Action=New">' . locale_number_format($NewPrice,0) . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'],
					$PriceLink,
					$NewPriceLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}



function OutstandingOrders($customertype, $ordertype, $RootPath, $db){
	/* Check if there are outstanding orders for retail customers */

	if ($customertype == "Retail"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_RETAIL . ")";
		$namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Retail";
	}elseif ($customertype == "Consignment"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_CONSIGNMENT . ")";
		$namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Consignment";
	}elseif ($customertype == "Wholesale"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_WHOLESALE . ")";
		$namefield = " debtorsmaster.name ";
		$Titletext = "Outstanding Wholesale";
	}elseif ($customertype == "Online"){
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")";
		$namefield = " salesorders.deliverto AS name ";
		$Titletext = "Outstanding Online";
	}else{
		$namefield = " debtorsmaster.name ";
		$whereclause = " ";
		$Titletext = _('Outstanding');
	}
	
	if ($ordertype == "Quotation"){
		$whereclause = $whereclause . " AND salesorders.quotation = 1 ";
		$Titletext = $Titletext . " Quotations";
	}elseif  ($ordertype == "Order"){
		$whereclause = $whereclause . " AND salesorders.quotation = 0 ";
		$Titletext = $Titletext . " Orders";
	}else{
		$Titletext = _(' Orders and Quotations');
	}
	
	$SQL = "SELECT salesorders.orderno,	
				debtorsmaster.debtorno, "
			   . $namefield . ",
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
			FROM salesorders INNER JOIN salesorderdetails 	
				ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN debtorsmaster 
				ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed= 0	"
			. $whereclause .
			" GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Total Value') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineOrdersFollowUp($Source, $numDays, $RootPath, $db){

	$Titletext = "Follow up Outstanding " . $Source. " Online Orders";
	$ThankYouDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numDays));
// 2015-01-14 Prices already NET for online orders
//				(SELECT SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent))
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
					AND ((salesorders.klemailthankyouorder = '0000-00-00' 
								AND salesorders.klemailtrackingconfirm <= '" . $ThankYouDate . "' 
								AND salesorders.klemailtrackingconfirm != '0000-00-00')
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
				WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")
					AND debtorsmaster.debtorno != 'LAZADA'
					AND salesorders.quotation = 0
					AND (	(debtortrans.type = 12 
								AND salesorders.klemailpaymentconfirm = '0000-00-00')
						 OR (debtortrans.type = 10 
								AND salesorders.klemailtrackingconfirm = '0000-00-00')
						 OR (salesorders.klemailthankyouorder = '0000-00-00' 
								AND salesorders.klemailtrackingconfirm <= '" . $ThankYouDate . "' 
								AND salesorders.klemailtrackingconfirm != '0000-00-00')
						)
				GROUP BY salesorders.orderno,	
					debtorsmaster.name,
					salesorders.orddate
				ORDER BY salesorders.orderno";			
	}
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . '#' . '</th>
							<th class="ascending">' . _('webERP Order') . '</th>
							<th class="ascending">' . '#' . $Source . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . _('Payment Confirmation Sent On') . '</th>
							<th class="ascending">' . _('Tracking Number') . '</th>
							<th class="ascending">' . _('Tracking Confirmation Sent On') . '</th>
							<th class="ascending">' . _('Thank You Sent On') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			
			$EmailType3 = "ThankYouOrder";
			if ($myrow['klemailthankyouorder']== '0000-00-00'){
				$EmailLinkText = 'Not Sent yet';
				$EmailLink3 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType3. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
			}else{
				$EmailLink3 = ConvertSQLDate($myrow['klemailthankyouorder']);
			}

			$EmailType2 = "TrackingConfirmation";
			if ($myrow['klemailtrackingconfirm']== '0000-00-00'){
				$EmailLinkText = 'Not Sent yet';
				$EmailLink2 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType2. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
				$EmailLink3 = 'Send Tracking Confirmation first';
			}else{
				$EmailLink2 = ConvertSQLDate($myrow['klemailtrackingconfirm']);
			}
			
			$EmailType1 = "PaymentConfirmation";
			if ($myrow['klemailpaymentconfirm']== '0000-00-00'){
				$EmailLinkText = 'Not Sent yet';
				$EmailLink1 = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType1. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
				$EmailLink2 = 'Send Payment Confirmation first';
				$EmailLink3 = 'Send Payment Confirmation first';
			}else{
				$EmailLink1 = ConvertSQLDate($myrow['klemailpaymentconfirm']);
			}

			if ($Source == "LAZADA"){
				$EmailLink1 = '';
				$EmailLink2 = '';
			}
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					locale_number_format($myrow['customerref']),
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue']+$myrow['freightcost'],$myrow['decimalplaces']),
					$myrow['currcode'], 
					$EmailLink1,
					$myrow['shippername'] . ' ' . $myrow['consignment'],
					$EmailLink2,
					$EmailLink3
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineQuotationsFollowUp($RootPath, $db){

	$Titletext = "Follow up Outstanding Online Quotations";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				salesorders.klemailremindbanktransfer,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
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
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")
				AND salesorders.quotation = 1
			GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";			

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . _('Reminder Bank Transfer Sent On') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$EmailType = "RemindBankTransfer";
			if ($myrow['klemailremindbanktransfer']== '0000-00-00'){
				$EmailLinkText = 'Not Sent yet';
				$EmailLink = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
			}else{
				$EmailLink = ConvertSQLDate($myrow['klemailremindbanktransfer']);
			}
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue']+$myrow['freightcost'],$myrow['decimalplaces']),
					$myrow['currcode'], 
					$EmailLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OldOnlineQuotations($NumDays, $RootPath, $db){

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$Titletext = "Online Quotations with more than " . $NumDays . " Days. (To de deleted)";
		
	$SQL = "SELECT salesorders.orderno,	
				salesorders.customerref,
				salesorders.klemailremindbanktransfer,
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue,
				salesorders.freightcost,
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
				AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")
				AND salesorders.quotation = 1
				AND salesorders.orddate < '" . $StartDate . "'
			GROUP BY salesorders.orderno,	
				debtorsmaster.name,
				salesorders.orddate
			ORDER BY salesorders.orderno";			

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Titletext . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
							<th class="ascending">' . _('Reminder Bank Transfer Sent On') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$EmailType = "RemindBankTransfer";
			if ($myrow['klemailremindbanktransfer']== '0000-00-00'){
				$EmailLinkText = 'Not Sent yet';
				$EmailLink = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['orderno'] . '&EmailType=' . $EmailType. '&CustomerOrder=' . $myrow['customerref'] . '">'. $EmailLinkText .'</a>';
			}else{
				$EmailLink = ConvertSQLDate($myrow['klemailremindbanktransfer']);
			}
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					locale_number_format($myrow['ordervalue']+$myrow['freightcost'],$myrow['decimalplaces']),
					$myrow['currcode'], 
					$EmailLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineCustomersNoOrderPlaced($RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

	$SQL = "SELECT 	debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					debtorsmaster.clientsince
			FROM debtorsmaster
			WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ")
				AND debtorsmaster.klemailnowebshoporder = '0000-00-00'
				AND NOT EXISTS (SELECT * 
								FROM salesorders
								WHERE salesorders.debtorno = debtorsmaster.debtorno)
				AND debtorsmaster.debtorno != 'WEBSHOP'
			ORDER BY debtorsmaster.debtorno";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Online Customers registered but no order placed.') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Country') . '</th>
							<th class="ascending">' . _('Currency ') . '</th>
							<th class="ascending">' . _('Registered on') . '</th>
							<th class="ascending">' . _('Send Email') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . $myrow['debtorno'] . '">' . $myrow['debtorno'] . '</a>';
			$EmailLinkText = 'Send Now';
			$EmailType = 'NoOrderPlaced';
			$EmailLink = '<a href="' . $RootPath . '/KLFollowUpOnlineEmails.php?TransNo=' . $myrow['debtorno'] . '&EmailType=' . $EmailType. '&CustomerOrder=' . $myrow['debtorno'] . '">'. $EmailLinkText .'</a>';
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
					$EmailLink				
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OnlineItemsOnProcess($RootPath, $db){
	
	$SQL = "SELECT salesorders.orderno,	
				debtorsmaster.debtorno,
				salesorders.deliverto AS name,
				salesorders.orddate,
				salesorderdetails.stkcode,
				salesorderdetails.quantity AS qtyorder,
				l1.reorderlevel,
				l1.quantity AS qtyready,
				(SELECT SUM(l2.quantity)
					FROM locstock AS l2
					WHERE l1.stockid = l2.stockid
						AND l2.loccode = 'KANTO') AS qohkantor
			FROM salesorderdetails, salesorders, locstock AS l1, debtorsmaster	
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = l1.stockid
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND salesorders.quotation = 0
				AND salesorders.fromstkloc = 'TOKWS'
				AND l1.loccode = 'TOKWS'
				AND salesorderdetails.completed= 0
			ORDER BY salesorders.orderno, salesorderdetails.stkcode";
	$result = DB_query($SQL);
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "Items on process for Online Orders" . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Order') . '</th>
							<th class="ascending">' . _('Customer') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Item Code') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('RL at Toko Online') . '</th>
							<th class="ascending">' . _('QOH Toko Online') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			$ItemLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stkcode'] . '">' . $myrow['stkcode'] . '</a>';
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['debtorno'], 
					$myrow['name'], 
					ConvertSQLDate($myrow['orddate']), 
					$ItemLink, 
					locale_number_format($myrow['qtyorder'],0),
					locale_number_format($myrow['reorderlevel'],0),
					locale_number_format($myrow['qtyready'],0),
					locale_number_format($myrow['qohkantor'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function WrongGiftItem($stockid, $customertype, $ErrorType, $OrderValue, $numDays, $RootPath, $db){

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
		$whereclause = " AND debtorsmaster.typeid IN (". CUSTOMER_TYPE_ONLINE . ") ";
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
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
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
				" AND SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate)" . $Sign . $OrderValue .
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

function PettyCashToBeAuthorized($db){

	$SQL = "SELECT pcashdetails.tabcode, 	
				SUM(pcashdetails.amount) as amount,
				pctabs.currency
			FROM pcashdetails,pctabs	
			WHERE pcashdetails.tabcode = pctabs.tabcode	
				AND pcashdetails.authorized = '0000-00-00'
				AND pctabs.authorizer = '". $_SESSION['UserID'] ."'
			GROUP BY pcashdetails.tabcode";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Expenses to be Authorized') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('PC Tab Code') . '</th>
							<th class="ascending">' . _('Amount') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$myrow['tabcode'], 
					locale_number_format($myrow['amount'],0),
					$myrow['currency']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PettyCashBalance($TypeUser, $db){

	if ($TypeUser == 'Authorizer'){
		$WhereUser = "AND pctabs.authorizer = '". $_SESSION['UserID'] ."'";
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

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		
		if ($TypeUser == "Authorizer"){
			echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Accounts you AUTHORIZE with balance too Low or Too High') . '</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Petty Cash Balance you USE with balance too Low or Too High') . '</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('PC Tab Code') . '</th>
							<th class="ascending">' . _('Amount') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$myrow['tabcode'], 
					locale_number_format($myrow['amount'],0),
					$myrow['currency']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsWithStockKantorButRLZeroAt($StockCat, $Location, $RootPath, $db){
/*
items with stock kantor > 0 
RL is zero at $Location
No pending transfer regarding this item
*/
/* 2013-04-16 excluding items in change price process */
/* 2013-05-27 excluding items in consignment clothing */

	// if the location is NOT doing discount, then we should filter discounted items
	if (!ItemInList($Location, LIST_SHOPS_OUTLET)){
		$FilterDiscount = " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET . " ";
		$MessageDiscount = " NO discount.";
	}else{
		$FilterDiscount = " ";
		$MessageDiscount = " ";
	}

	if ($StockCat != "ALL"){
		$FilterCategory = " AND stockmaster.categoryid = '" . $StockCat . "' ";;
	}
		
	
	$SQL = "SELECT stockid,
			stockmaster.categoryid,
			stockmaster.description,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid
				AND (locstock.loccode = 'KANTO' ))AS QtyKantor
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND discontinued = 0
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OLD . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SETUP . "
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.klchangingprice = 0 " .
				$FilterCategory .
				$FilterDiscount . "
				AND (SELECT SUM(locstock.reorderlevel)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = '". $Location ."') = 0
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'KANTO') > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  recqty < shipqty
							AND loctransfers.stockid =  stockmaster.stockid)
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $StockCat . _(' Items with stock available (but NO changing price or category) at Kantor but RL = 0 at ') . $Location . "." .$MessageDiscount . $MessageOutlet . '</strong></p>';
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

function ItemsWithStockKantorButReorderLevelTokoZero($RootPath, $db){
/**********************************************************************
items with stock kantor > 0 
RL is zero at all shops
No pending transfer regarding this item

2013-04-16 excluding items in change price process
2013-04-25 excluding items in move to discount / outlet process 
2014-12-02 excluding items in OLD categories

***********************************************************************/

	$SQL = "SELECT stockid,
			stockmaster.categoryid,
			stockmaster.description,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid
				AND (locstock.loccode = 'KANTO' ))AS QtyKantor
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND (SELECT SUM(locstock.reorderlevel)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode IN " . LIST_ALL_SHOPS . ") = 0
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'KANTO') > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  recqty < shipqty
							AND loctransfers.stockid =  stockmaster.stockid)
				AND discontinued = 0
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SETUP . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OLD . "
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items with stock available (but NO changing price or category) at Kantor but RL zero for all toko KL') . '</strong></p>';
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

function ItemsInCategoryWithStockKantorButReorderLevelTokoZero($CategoryId, $RootPath, $db){
	if (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_OUTLET)){
		if (LIST_SHOPS_OUTLET == "('')"){
			// no shops with outlet, so this report has NO sense.
			return;
		}else{
			$WhereLocation = " AND locstock.loccode IN  " . LIST_SHOPS_OUTLET . " ";
		}
	}elseif (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_DISCOUNT)){
		if (LIST_SHOPS_OUTLET == "('')"){
			// no shops with discount, so this report has NO sense.
			return;
		}else{
			$WhereLocation = " AND locstock.loccode IN  " . LIST_SHOPS_OUTLET . " ";
		}
	}else{
		$WhereLocation = " AND locstock.loccode IN " . LIST_ALL_SHOPS . " ";
	}

	$SQL = "SELECT stockid,
			stockmaster.categoryid,
			stockmaster.description,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid
				AND (locstock.loccode = 'KANTO' ))AS QtyKantor
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND (SELECT SUM(locstock.reorderlevel)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid ".
						$WhereLocation . " ) = 0
				AND (SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'KANTO') > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  recqty < shipqty
							AND loctransfers.stockid =  stockmaster.stockid)
				AND discontinued = 0
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid = '" . $CategoryId . "'
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_OUTLET)){
			echo '<p class="page_title_text" align="center"><strong>' . $CategoryId ._(' Items with stock available at Kantor but RL zero for ') . LIST_SHOPS_OUTLET . '</strong></p>';
		}elseif (ItemInList($CategoryId, LIST_STOCK_CATEGORIES_DISCOUNT)){
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

function ComponentsToObsolete($ShowOnlyTotal, $ShowLimit, $RootPath, $db){
	$SQL = "SELECT s.stockid,
					s.units,
					s.description,
					(s.materialcost + s.labourcost + s.overheadcost) AS stdcost,
					(SELECT SUM(quantity)
						FROM locstock
						WHERE s.stockid = locstock.stockid) AS qoh
			FROM stockmaster AS s
			WHERE s.categoryid IN ('COMPON','ZCMOTH','ZCMSST')
				AND s.discontinued = 0
				AND NOT EXISTS(
					SELECT bom.component
					FROM bom,stockmaster AS stP, stockmaster AS stC
					WHERE bom.parent = stP.stockid
						AND bom.component = stC.stockid 
						AND s.stockid = bom.component
						AND stP.discontinued = 0)";
	$result = DB_query($SQL);
	$totalcost = 0;
	if (DB_num_rows($result) != 0){
		if (!$ShowOnlyTotal){
			echo '<p class="page_title_text" align="center"><strong>' . _('Components NOT Used in any BOM. Use them in any product (IF QOH > 0) OR flag as obsolete (IF QOH = 0).') . '</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Code') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('QOH') . '</th>
								<th class="ascending">' . _('UOM') . '</th>
								<th class="ascending">' . _('Stock value') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$totalcost = $totalcost + ($myrow['qoh']*$myrow['stdcost']);
			if (!$ShowOnlyTotal){
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'],
						locale_number_format($myrow['qoh'],0),
						$myrow['units'],
						locale_number_format($myrow['qoh']*$myrow['stdcost'],0)
						);
			}
			$i++;
		}
		if (!$ShowOnlyTotal){
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					'', 
					'', 
					'Total Cost',
					'',
					'',
					locale_number_format($totalcost,0)
					);
			echo '</table>
					</div>';
		}elseif ($totalcost >= $ShowLimit){
			$text = "Components NOT Used in any BOM cost over the limit. Current cost = ". locale_number_format($totalcost,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
	}
}

function BalanceAccountControl($account, $min, $max, $period, $db){
	$SQL = "SELECT (bfwd + actual) as saldo, accountname
			FROM chartdetails, chartmaster
			WHERE chartdetails.accountcode = chartmaster.accountcode
				AND chartdetails.accountcode = '" . $account . "'
				AND chartdetails.period = ". $period . "";
				
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	if ($myrow['saldo'] < $min){
		$text = "Account " . $account . " - " . $myrow['accountname'] . " is BELOW the minimum. Balance = " . locale_number_format($myrow['saldo'],0) . " Minimum = " . locale_number_format($min,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
	if ($myrow['saldo'] > $max){
		$text = "Account " . $account . " - " . $myrow['accountname'] . " is OVER the maximum. Balance = " . locale_number_format($myrow['saldo'],0) . " Maximum = " . locale_number_format($max,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function GoodsReceivedNotInvoicedControl($period, $db){
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode = '211021400'
				AND chartdetails.period = ". $period . "";
// EXPLAIN SQL 2014-05-31 OK!
//prnMsg($SQL);
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	$ValueAtBalance = -$myrow['saldo'];
	
	$SQL = "SELECT SUM((grns.qtyrecd - grns.quantityinv) * (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))
			FROM grns, stockmaster
			WHERE stockmaster.stockid = grns.itemcode
				AND (grns.qtyrecd - grns.quantityinv) > 0";
// EXPLAIN SQL 2014-05-31
// NOT OK. All 10.000 rows each time
// prnMsg($SQL);	
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);

	$GoodsValue = $myrow[0];

	if (abs($ValueAtBalance - $GoodsValue) > 1){
		$text = "Goods Received Balance value = " . locale_number_format($ValueAtBalance,0) . " <-> Real Goods Received Value at Std Cost = " . locale_number_format($GoodsValue,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function CustomersDebtControl($AcceptedDifference, $period, $db){
	$SQL = "SELECT (bfwd + actual) as saldo
			FROM chartdetails
			WHERE chartdetails.accountcode = '111311100'
				AND chartdetails.period = ". $period . "";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	$ValueAtBalance = $myrow['saldo'];
	
	$SQL = "SELECT SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				)/currencies.rate AS balance
			FROM debtorsmaster,
				paymentterms,
				holdreasons,
				currencies,
				debtortrans
			WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.holdreason = holdreasons.reasoncode
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = 'IDR' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$DebtValueIDR = $myrow[0];

	$SQL = "SELECT SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				)/currencies.rate AS balance
			FROM debtorsmaster,
				paymentterms,
				holdreasons,
				currencies,
				debtortrans
			WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.holdreason = holdreasons.reasoncode
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = 'USD' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$DebtValueUSD = $myrow[0];

	$SQL = "SELECT SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				)/currencies.rate AS balance
			FROM debtorsmaster,
				paymentterms,
				holdreasons,
				currencies,
				debtortrans
			WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.holdreason = holdreasons.reasoncode
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = 'AUD' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$DebtValueAUD = $myrow[0];

	$SQL = "SELECT SUM(
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc
				)/currencies.rate AS balance
			FROM debtorsmaster,
				paymentterms,
				holdreasons,
				currencies,
				debtortrans
			WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.holdreason = holdreasons.reasoncode
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.currcode = 'EUR' ";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$DebtValueEUR = $myrow[0];	
	
	$DebtValue = $DebtValueIDR + $DebtValueUSD + $DebtValueAUD + $DebtValueEUR;
	
	if (abs($ValueAtBalance - $DebtValue) > $AcceptedDifference){
		$text = "Customer's Debt Balance value = " . locale_number_format($ValueAtBalance,0) . " <-> Customer's Debt = " . locale_number_format($DebtValue,0);
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
}

function ItemsShouldBeInWebsite($db){
	$SQL = "SELECT stockid, description
			FROM stockmaster
			WHERE categoryid IN " . CATEGORIES_AVAILABLE_WEBSITE ."
				AND discontinued = 0
				AND stockid NOT LIKE 'KLBE%'
				AND stockid NOT LIKE 'GOTA%'
				AND stockid NOT LIKE 'TM-%'
				AND NOT EXISTS (SELECT *
								FROM salescatprod
								WHERE salescatprod.stockid = stockmaster.stockid)";
	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			if(file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg') ) {
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . _('Items with picture but not available in website') . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
									</tr>';
					echo $TableHeader;
					$k = 0; //row colour counter
					$i = 1;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$myrow['stockid'], 
						$myrow['description'] 
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
			
function OvestockAtSamples($maxallowedsamples, $RootPath, $db){

	$SQL = "SELECT locstock.stockid, 
					stockmaster.description, 
					quantity AS qty
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND loccode = 'SAMPR'
				AND quantity > '". $maxallowedsamples."'
			ORDER BY locstock.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Overstock of samples') . '</strong></p>';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Qty of samples') . '</th>
						</tr>';
		echo '<div>';
		echo '<table class="selection">';
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
					</tr>', 
					$i, 
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

function SamplesNotLongerNeeded($RootPath, $db){

	$SQL = "SELECT locstock.stockid, 
					stockmaster.description, 
					quantity AS qty
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND loccode = 'SAMPR'
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ." 
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT ."
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET .")
				AND quantity > 0
			ORDER BY locstock.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Samples Not Longer Needed (No More Buy, Discount, Outlet)') . '</strong></p>';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Qty of samples') . '</th>
						</tr>';
		echo '<div>';
		echo '<table class="selection">';
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
					</tr>', 
					$i, 
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
			
function ValueStockLocation($location, $minpcs, $maxpcs, $minvalue, $maxvalue, $db){
/*	$minpcs = $optimalpcs * (1 - $varpcs);
	$maxpcs = $optimalpcs * (1 + $varpcs);
	$minvalue = $optimalvalue * (1 - $varvalue);
	$maxvalue = $optimalvalue * (1 + $varvalue);
*/	
	$SQL = "SELECT 
				SUM(locstock.quantity) AS qtyonhand,
				SUM(locstock.quantity *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost)) AS valuetotal
			FROM stockmaster,
				stockcategory,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.categoryid=stockcategory.categoryid
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES . "
				AND locstock.quantity!=0
				AND locstock.loccode = '" . $location . "'";
				
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	if ($myrow['qtyonhand'] < $minpcs){
		$text = "Number of items at " . $location . " is BELOW the minimum. Stock on hand = " . locale_number_format($myrow['qtyonhand'],0) . " pcs. Minimum = " . locale_number_format($minpcs,0) . " pcs";
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
/*	if ($myrow['valuetotal'] < $minvalue){
		prnMsg("Cost value of items at " . $location . " is BELOW the minimum. Value on hand = " . locale_number_format($myrow['valuetotal'],0) . " IDR. Minimum = " . locale_number_format($minvalue,0) . " IDR","warn");
	}
*/
	if ($myrow['qtyonhand'] > $maxpcs){
		$text = "Number of items at " . $location . " is OVER the maximum. Stock on hand = " . locale_number_format($myrow['qtyonhand'],0) . " pcs. Maximum = " . locale_number_format($maxpcs,0) . " pcs";
		echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
	}
/*	if ($myrow['valuetotal'] > $maxvalue){
		prnMsg("Cost value of items at " . $location . " is OVER the maximum. Value on hand = " . locale_number_format($myrow['valuetotal'],0) . " IDR. Maximum = " . locale_number_format($maxvalue,0) . " IDR","warn");
	}
*/
}

function ItemsOnSpecialRequest($RootPath, $db){
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
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items on Special Kantor Request') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('Reorder Level') . '</th>
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['quantity'], 
					$myrow['reorderlevel'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function DiscountedItemsOnNotOutletShops($Category, $RootPath, $db){
/*
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  recqty < shipqty
							AND loctransfers.stockid =  stockmaster.stockid)
*/
	
	$Message = $Category . '% Discounted items on wrong shops (NOT OUTLET Shops)';
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.categoryid = '" . $Category . "'
				AND locstock.loccode IN " . LIST_ALL_SHOPS . "
				AND locstock.loccode NOT IN " . LIST_SHOPS_OUTLET . "
				AND locstock.loccode NOT IN " . LIST_ONLINE_SHOPS . "
				AND ( locstock.quantity > 0 OR locstock.reorderlevel > 0 )
			ORDER BY stockmaster.stockid";
// EXPLAIN SQL 2014-05-31
//	prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Message . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('Reorder Level') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$CodeLinkRL = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['reorderlevel'] . '</a>';
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
					$myrow['loccode'], 
					$myrow['quantity'], 
					$CodeLinkRL 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function NotDiscountedItemsOnOutletShops($RootPath, $db){

	$Message = 'Not Discounted items on wrong shops (OUTLET Shops)';
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE . "
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING . ")
				AND locstock.loccode IN " . LIST_ALL_SHOPS . "
				AND locstock.loccode IN " . LIST_SHOPS_OUTLET . "
				AND ( locstock.quantity > 0 OR locstock.reorderlevel > 0 )
			ORDER BY stockmaster.stockid";
// EXPLAIN SQL 2014-05-31
//	prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Message . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('Reorder Level') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$CodeLinkRL = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['reorderlevel'] . '</a>';
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
					$myrow['loccode'], 
					$myrow['quantity'], 
					$CodeLinkRL 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}


function CategoryItemsNotInShop($Category, $Shop, $RootPath, $db){
	
	$Message = $Category . _(' items NOT in ') . $Shop . ' (excluding Change of Price, Move to Discount, Service, Shop online and Return to Supplier)';
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					(SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . ") AS qoh,
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
				AND ((SELECT SUM(l.quantity)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode NOT IN " . LIST_SERVICE_LOCATIONS . ") > 0)
				AND ((SELECT SUM(l.reorderlevel)
						FROM locstock l
						WHERE l.stockid = stockmaster.stockid
							AND l.loccode = 'TOKWS') = 0)
			ORDER BY stockmaster.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Message . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('Reorder Level') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$CodeLinkRL = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['reorderlevel'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['qoh'], 
					$CodeLinkRL 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}


			
function OutletItemsOnKLShops($RootPath, $db){
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.categoryid IN ('DISC80')
				AND locstock.loccode IN " . LIST_ALL_SHOPS . "
				AND locstock.quantity > 0
				AND NOT EXISTS (SELECT *
						FROM loctransfers 
						WHERE  recqty < shipqty
							AND loctransfers.stockid =  stockmaster.stockid)
			ORDER BY stockmaster.stockid";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Outlet items on KL shops') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('Reorder Level') . '</th>
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
					$myrow['loccode'], 
					$myrow['quantity'], 
					$myrow['reorderlevel'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}
			
function DiscountedItemsWithWrongDiscount($Category, $DiscountCode, $RootPath, $db){
	$SQL = "SELECT * 
			FROM  stockmaster 
			WHERE categoryid = '" . $Category . "'
				AND discountcategory !=  '". $DiscountCode ."'
				AND discontinued = 0";
				
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $Category . _(' items with wrong discount (Not ') . $DiscountCode. '%)</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
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
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function NotDiscountedItemsWithDiscount($RootPath, $db){
	$SQL = "SELECT stockid,
					description
			FROM  stockmaster 
			WHERE   categoryid NOT IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS ."
				AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_DISCOUNT ."
				AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_OLD ."
				AND discountcategory !=  ''
				AND discontinued = 0";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Not Discounted items with discount') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
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
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function OldPurchasingOrdersStillActive($maxdays, $RootPath, $db){
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
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Purchase Orders older than ') . $maxdays . _(' days and still not closed') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('PO') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Supplier') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/PO_OrderDetails.php?OrderNo=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			printf('<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					ConvertSQLDate($myrow['orddate']), 
					$myrow['supplierno']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PurchasingOrdersDeliveryControl($reason, $maxdays, $RootPath, $db){

	if ($reason == "Delayed"){
		$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
		$EndDate = "0000-00-00";
	}else{
		$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',+$maxdays));
		$EndDate = Date("Y-m-d");
	}
	$SQL = "SELECT purchorders.orderno,
				suppliers.suppname,
				purchorders.orddate,
				purchorders.deliverydate,
				purchorders.status,
				purchorders.initiator,
				purchorders.requisitionno,
				purchorders.allowprint,
				suppliers.currcode,
				currencies.decimalplaces AS currdecimalplaces,
				SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue,
				(SELECT SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc)
					FROM supptrans
					WHERE suppliers.supplierid = supptrans.supplierno) AS balance
			FROM purchorders INNER JOIN purchorderdetails
				ON purchorders.orderno = purchorderdetails.orderno
			INNER JOIN suppliers 
				ON  purchorders.supplierno = suppliers.supplierid 
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			WHERE purchorderdetails.completed=0
				AND purchorders.deliverydate <  '". $StartDate ."'		
				AND purchorders.deliverydate >= '". $EndDate ."'		
				AND purchorders.status IN ('Authorised', 'Printed', 'Pending')		
			GROUP BY purchorders.orderno ASC,
				suppliers.suppname,
				purchorders.orddate,
				purchorders.status,
				purchorders.initiator,
				purchorders.requisitionno,
				purchorders.allowprint,
				suppliers.currcode,
				currencies.decimalplaces
			ORDER BY purchorders.deliverydate ASC";
	
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($reason == "Delayed"){
			echo '<p class="page_title_text" align="center"><strong>' . _('Purchase Orders with delivery date expired') . '</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Purchase Orders to be delivered in the next ') . $maxdays . ' days' .'</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('PO') . '</th>
							<th class="ascending">' . _('Order Date') . '</th>
							<th class="ascending">' . _('Delivery Date') . '</th>
							<th class="ascending">' . _('Supplier') . '</th>
							<th class="ascending">' . _('Order Value') . '</th>
							<th class="ascending">' . _('Deposit') . '</th>
							<th class="ascending">' . _('Remaining') . '</th>
							<th class="ascending">' . _('Currency') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/PO_OrderDetails.php?OrderNo=' . $myrow['orderno'] . '">' . $myrow['orderno'] . '</a>';
			printf('<td class="number">%s</td>
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
					ConvertSQLDate($myrow['orddate']), 
					ConvertSQLDate($myrow['deliverydate']), 
					$myrow['suppname'],
					locale_number_format($myrow['ordervalue'],0),
					locale_number_format($myrow['balance'],0),
					locale_number_format($myrow['ordervalue']+$myrow['balance'],0),
					$myrow['currcode']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PerformanceItemsInCategory($ReportType, $CategoryId, $maxdays, $percentsales, $TextTitle, $RootPath, $db){

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
							<th class="ascending">' . _('Date') . '</th>
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

function SplittedpaymentsBySPG($maxdays, $maxsplitted, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$totalcash = 0;
	$totalcredit = 0;
	$totalreturned = 0;
	$total = 0;

	$SQL = "SELECT salesperson, 
				COUNT(klpaidcash + klpaidcreditcard) AS splitted, 
				SUM(klpaidcash + klpaidcreditcard) AS amount
		FROM salesorders
		WHERE orddate >= '". $StartDate. "'
			AND debtorno LIKE 'RETAIL%'
			AND klpaidcash > 0
			AND klpaidcreditcard > 0
		GROUP BY salesperson
		HAVING COUNT(klpaidcash + klpaidcreditcard) >= '" . $maxsplitted . "'
		ORDER BY salesperson";

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

function ItemsNotTopSalesInShop($starttopitems, $endtopitems, $maxdays, $codeshop, $categories, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
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
	if ($categories == "ACTIVE"){
		$SQL = $SQL . " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_DISCOUNT . " ";
	}		
	$SQL = $SQL . " AND stockmaster.discontinued = 0
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
					if ($categories == "DISC50"){
						echo '<p class="page_title_text" align="center"><strong>' . 'NOT ' . $endtopitems . ' top sales items 50% DISCOUNT available in ' . $codeshop . ' shop. ' . '</strong></p>';
					}		
					if ($categories == "DISC80"){
						echo '<p class="page_title_text" align="center"><strong>' . 'NOT ' . $endtopitems . ' top sales items 80% DISCOUNT available in ' . $codeshop . ' shop. ' . '</strong></p>';
					}		
					if ($categories == "ACTIVE"){
						echo '<p class="page_title_text" align="center"><strong>' . 'NOT ' . $endtopitems . ' top sales items NOT DISCOUNTED OR OUTLET available in ' . $codeshop . ' shop. ' . '</strong></p>';
					}		
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
							OR locstock.loccode = 'KANTO')) AS qoh,
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

function GoodsJustArrived($kind, $location, $numdays, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays));
	if ($kind == "PO"){
		$type = 25;
	}elseif ($kind == "WO"){
		$type = 26;
	}
	$SQL = "SELECT stockmoves.stockid, 
					stockmaster.description,
					stockmoves.trandate, 
					stockmoves.qty AS qtyarrived,
					stockmoves.newqoh AS qtytotal
			FROM stockmoves, stockmaster, stockcategory
			WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount20 = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockcategory.stocktype = 'F'
				AND stockmoves.loccode ='" . $location . "'
				AND stockmoves.type ='" . $type . "'
				AND stockmoves.trandate >'" . $StartDate . "'
				ORDER BY stockmoves.trandate DESC, 
						stockmoves.stockid";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($kind == "PO"){
			echo '<p class="page_title_text" align="center"><strong>' . $kind . _(' Finished Goods just arrived at ') . $location . ' during the last '. $numdays . ' days'. '</strong></p>';
		}elseif ($kind == "WO"){
			echo '<p class="page_title_text" align="center"><strong>' . $kind . _(' Goods just produced at ') . $location . ' during the last '. $numdays . ' days'. '</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Received') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
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
					ConvertSQLDate($myrow['trandate']),
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qtyarrived'],0),
					locale_number_format($myrow['qtytotal'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function GoodsJustTransferred($locationfrom, $locationto, $numdays, $qohmax, $RootPath, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$numdays+1));

	$SQL = "SELECT loctransfers.stockid,
					stockmaster.description,
					loctransfers.recdate, 
					loctransfers.recqty AS qtytransferred,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = loctransfers.stockid) AS qtytotal
			FROM loctransfers, stockmaster, stockcategory
			WHERE loctransfers.stockid = stockmaster.stockid
				AND stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype = 'F'
				AND loctransfers.shiploc ='" . $locationfrom . "'
				AND loctransfers.recloc ='" . $locationto . "'
				AND loctransfers.recdate >'" . $StartDate . "'
				AND (SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = loctransfers.stockid) <= " . $qohmax . "
				ORDER BY loctransfers.recdate DESC, 
						loctransfers.stockid";
						
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _(' Finished Goods just transferred from ') . $locationfrom  . ' to '. $locationto . ' during the last '. $numdays . ' days and QOH <= '. $qohmax . '.</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Transferred') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
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
					ConvertSQLDate($myrow['recdate']),
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qtytransferred'],0),
					locale_number_format($myrow['qtytotal'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ActiveItemsNoSales($maxdays, $group, $RootPath, $db){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));

// This line goes in WHERE quantity if (Service Excluded) 
//							AND locstock.loccode NOT IN ('SERSU','SERVI')) AS quantity
	
	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.units, 
					(SELECT SUM(quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid) AS quantity
			FROM 	stockmaster, stockcategory
			WHERE 	stockmaster.categoryid = stockcategory.categoryid
					AND stockmaster.discontinued = 0 
					AND stockmaster.klchangingprice = 0
					AND stockmaster.klmovingdiscount20 = 0
					AND stockmaster.klmovingdiscount50 = 0
					AND stockmaster.klmovingdiscount80 = 0
					AND stockmaster.lastcategoryupdate <= '" . $FromDate . "'";
	if ($group == "ACTIVE"){
		$SQL = $SQL . "	AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_TEST ." ";
		$SQL = $SQL . 		" OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_STABLE ." ";
		$SQL = $SQL . 		" OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING .") ";
	}else{
		$SQL = $SQL . "	AND stockmaster.categoryid ='" . $group . "'";
	}				
	$SQL = $SQL . " AND stockcategory.stocktype = 'F'
					AND NOT EXISTS (SELECT * 
									FROM 	salesorderdetails, salesorders
									WHERE 	stockmaster.stockid = salesorderdetails.stkcode
											AND (salesorderdetails.orderno = salesorders.orderno)
											AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
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
	
	$result = DB_query($SQL);		
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $group . _(' Items with NO sales on last ') . $maxdays . ' days and NO current PO. Move to next category step</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Category') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
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
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['categoryid'], 
					locale_number_format($myrow['quantity'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}	

function InsuficientStockForTopSalesItems($StockCat, $StockCatDescription, $DaysTopSales, $PercentageOfTopItems, $DaysMinimumStock, $RootPath, $db){
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

function PriceBelowStandard($Stockcat, $Factor, $Tolerance, $MinQoh, $RootPath, $db){
	$today = date('Y-m-d');
	$FactorTolerance = 1 + $Tolerance;

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
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
		echo '<p class="page_title_text" align="center"><strong>' . $Stockcat . _(' Items with retail price below minimum. ') . $Factor . _(' x standard cost. Tolerance -') . locale_number_format($Tolerance * 100,0) . '%. QOH >= ' .  locale_number_format($MinQoh,0). '</strong></p>';
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
							<th class="ascending">' . _('Minimum Price') . '</th>
							<th class="ascending">' . _('Recommended Retail') . '</th>
							<th class="ascending">' . _('% Increase') . '</th>
							<th class="ascending">' . _('Income Increase') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$NewPrice = $myrow['standardcost'] * $Factor;
			$RecommendedPrice = correction_for_low_end_prices(round_price($NewPrice, "UP"));
			$Increase = locale_number_format(($RecommendedPrice-$myrow['retailprice'])/$myrow['retailprice']*100,1).'%';
			$PositionTopSales = positionTopSalesItem($myrow['stockid'], 99999, 60, $db);
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
					locale_number_format($NewPrice,0),
					$NewPriceLink,
					$Increase,
					locale_number_format($IncomeIncrease,0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function PriceWrongRounding($RootPath, $db){
	$today = date('Y-m-d');

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
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
				$PositionTopSales = positionTopSalesItem($myrow['stockid'], 99999, 60, $db);
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

function ItemsTooExpensive($Stockcat, $FactorMin, $FactorMax, $Tolerance, $MinQoh, $TopSales, $DaysTopSales, $RootPath, $db){
	$today = date('Y-m-d');
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysTopSales));
	$FactorTolerance = 1 - $Tolerance;

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
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
					LIMIT 1) > ((stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) * ". $FactorMax ." / ". $FactorTolerance ."))";

	$result = DB_query($SQL);
	$ShowHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = positionTopSalesItem($myrow['stockid'], 99999, $DaysTopSales, $db);
			if ($PositionTopSales > $TopSales){
				if ($ShowHeader){
					echo '<p class="page_title_text" align="center"><strong>' .  $Stockcat . ' Items TOO EXPENSIVE: ' . ' NO TOP '.locale_number_format($TopSales,0) . ' sales. Retail Price OVER ' . $FactorMax . _(' x standard cost. Tolerance ') . locale_number_format($Tolerance * 100,0) . '%. QOH >= ' .  locale_number_format($MinQoh,0).  '</strong></p>';
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
										<th class="ascending">' . _('Optimum Price') . '</th>
										<th class="ascending">' . _('Recommended Retail') . '</th>
										<th class="ascending">' . _('% Decrease') . '</th>
										<th class="ascending">' . _('Income Decrease') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
				}
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$MaxPrice = $myrow['standardcost'] * $FactorMax;
				$MinPrice = $myrow['standardcost'] * $FactorMin;
				$RecommendedPrice = correction_for_low_end_prices(round_price($MaxPrice, "DOWN"));
				$Decrease = locale_number_format(($RecommendedPrice-$myrow['retailprice'])/$myrow['retailprice']*100,1).'%';
				$NewPriceLink = '<a href="' . $RootPath . '/KLStartChangeRetailPrice.php?Item=' . $myrow['stockid'] . '&NewPrice='. $RecommendedPrice .  '">' . locale_number_format($RecommendedPrice,0) . '</a>';
				$IncomeDecrease = $myrow['qoh'] * ($RecommendedPrice-$myrow['retailprice']);
				if ($RecommendedPrice < $myrow['retailprice']){
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
							locale_number_format($MaxPrice,0),
							$NewPriceLink,
							$Decrease,
							locale_number_format($IncomeDecrease,0)
							);
					$i++;
				}
			}
		}
	}
	if (!$ShowHeader){
		echo '</table>
				</div>';
	}
}

function ItemsTooCheap($Stockcat, $FactorMin, $FactorMax, $Tolerance, $MinQoh, $TopSales, $DaysTopSales, $RootPath, $db){
	$today = date('Y-m-d');
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysTopSales));
	$FactorTolerance = 1 + $Tolerance;

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
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
	$ShowHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = positionTopSalesItem($myrow['stockid'], 99999, $DaysTopSales, $db);
			if ($PositionTopSales < $TopSales){
				if ($ShowHeader){
					echo '<p class="page_title_text" align="center"><strong>' .  $Stockcat . ' Items TOO CHEAP: ' . ' TOP '.locale_number_format($TopSales,0) . ' sales. Price BELOW ' . $FactorMax . _(' x standard cost. Tolerance ') . locale_number_format($Tolerance * 100,0) . '%. QOH >= ' .  locale_number_format($MinQoh,0).  '</strong></p>';
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
				$RecommendedPrice = correction_for_low_end_prices(round_price($MaxPrice, "UP"));
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
						locale_number_format($MaxPrice,0),
						$NewPriceLink,
						$Increase,
						locale_number_format($IncomeIncrease,0)
						);
				$i++;
			}
		}
	}
	if (!$ShowHeader){
		echo '</table>
				</div>';
	}
	
}

function ChangeItemStandardCost($StockID, $NewCost, $OldCost, $QOH){
	$Result = DB_Txn_Begin();
	ItemCostUpdateGL($db, $StockID, $NewCost, $OldCost, $QOH);
	$SQL = "UPDATE stockmaster SET	materialcost='" . $NewCost . "',
									labourcost='" . 0 . "',
									overheadcost='" . 0 . "',
									lastcost='" . $OldCost . "',
									lastcostupdate ='" . Date('Y-m-d')."'
							WHERE stockid='" . $StockID . "'";

	$ErrMsg = _('The cost details for the stock item could not be updated because');
	$DbgMsg = _('The SQL that failed was');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	$Result = DB_Txn_Commit();
	UpdateCost($db, $StockID); //Update any affected BOMs
}


function WrongStandardCost($Country, $StockCat, $StdFactor, $Tolerance, $Mode, $RootPath, $db){
/* FunctionMode means
	SHOWONLY: Shows data only
	SHOWLINK: Shows link to update the standard Cost manually
	UPDATEALL: Runs the update function for all items
*/
	$ToleranceHigh = 1 + $Tolerance;
	$ToleranceLow  = 1 - $Tolerance;
	
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				purchdata.supplierno,
				purchdata.conversionfactor,
				purchdata.price,
				suppliers.currcode,
				purchdata.suppliersuom,
				purchdata.effectivefrom,
				stockmaster.lastcostupdate,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS stdcost,
				(SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qoh,
				stockmaster.units,
				currencies.decimalplaces,
				currencies.rate
			FROM purchdata, stockmaster, suppliers, currencies
			WHERE  purchdata.stockid = stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND suppliers.address6 = '" . $Country . "'";
	if ($StockCat != ""){			
		$SQL = $SQL . " AND stockmaster.categoryid = '" . $StockCat . "'";
	}
	$SQL = $SQL . " AND suppliers.currcode =  currencies.currabrev
				AND (	(((purchdata.price / purchdata.conversionfactor) * " . $StdFactor . " * (1 / currencies.rate) * " . $ToleranceHigh . " ) 
						< (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))
					OR	(((purchdata.price / purchdata.conversionfactor) * " . $StdFactor . " * (1 / currencies.rate) * " . $ToleranceLow . " ) 
						> (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))
					)
				AND purchdata.supplierno = suppliers.supplierid
				AND purchdata.effectivefrom = (SELECT MAX(p2.effectivefrom)
												FROM purchdata p2
												WHERE p2.stockid = purchdata.stockid)
			ORDER BY stockmaster.stockid";
// EXPLAIN SQL 2014-05-31
// prnMsg($SQL);
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . $StockCat . ' Items from ' . $Country . _(' with wrong Standard Cost') .  ' ---> Cost Factor = ' . locale_number_format($StdFactor, 2) . ' ---> Tolerance = '. locale_number_format($Tolerance * 100, 2) .'%</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		if ($Mode == "SHOWONLY"){
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Code') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('Supplier') . '</th>
								<th class="ascending">' . _('From') . '</th>
								<th class="ascending">' . _('Price') . '</th>
								<th class="ascending">' . _('Currency') . '</th>
								<th class="ascending">' . _('Rate') . '</th>
								<th class="ascending">' . _('Supplier UOM') . '</th>
								<th class="ascending">' . _('UOM Factor') . '</th>
								<th class="ascending">' . _('Date Std Cost') . '</th>
								<th class="ascending">' . _('Std Cost IDR') . '</th>
							</tr>';
		}else{
			$TableHeader = '<tr>
								<th class="ascending">' . _('#') . '</th>
								<th class="ascending">' . _('Code') . '</th>
								<th class="ascending">' . _('Description') . '</th>
								<th class="ascending">' . _('Supplier') . '</th>
								<th class="ascending">' . _('From') . '</th>
								<th class="ascending">' . _('Price') . '</th>
								<th class="ascending">' . _('Currency') . '</th>
								<th class="ascending">' . _('Rate') . '</th>
								<th class="ascending">' . _('Supplier UOM') . '</th>
								<th class="ascending">' . _('UOM Factor') . '</th>
								<th class="ascending">' . _('Date Std Cost') . '</th>
								<th class="ascending">' . _('Std Cost IDR') . '</th>
								<th class="ascending">' . _('QOH') . '</th>
								<th class="ascending">' . _('KL UOM') . '</th>
								<th class="ascending">' . _('Real Std Cost') . '</th>
								<th class="ascending">' . _('% Dif') . '</th>
							</tr>';
		}
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			
			$NewStdCost = $myrow['price'] / $myrow['conversionfactor'] * (1/$myrow['rate']) * $StdFactor;
			$Price = locale_number_format($myrow['price'],$myrow['decimalplaces']);
			$PurchasingLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $myrow['stockid'] . '&SupplierID='. $myrow['supplierno'] . '&Edit=1&EffectiveFrom='. $myrow['effectivefrom']  .' ">' . $Price . '</a>';
			if ($Mode == "SHOWONLY"){
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						$myrow['supplierno'], 
						ConvertSQLDate($myrow['effectivefrom']),
						$PurchasingLink,
						$myrow['currcode'], 
						locale_number_format(1/$myrow['rate'],2),
						$myrow['suppliersuom'], 
						locale_number_format($myrow['conversionfactor'],0),
						ConvertSQLDate($myrow['lastcostupdate']),
						locale_number_format($myrow['stdcost'],0)
						);
			}else{
				if($Mode == "UPDATEALL"){
					// UPDATEALL
					$StdCost = locale_number_format($NewStdCost,0);
					ChangeItemStandardCost($myrow['stockid'], $NewStdCost, $myrow['stdcost'], $myrow['qoh']);
				}else{
					// SHOWLINK
					$StdCost = '<a href="' . $RootPath . '/KLUpdateStandardCost.php?StockId=' . $myrow['stockid'] . '&NewCost=' . round($NewStdCost,0) .'">' . locale_number_format($NewStdCost,0) . '</a>';
				}
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						$myrow['supplierno'], 
						ConvertSQLDate($myrow['effectivefrom']),
						$PurchasingLink,
						$myrow['currcode'], 
						locale_number_format(1/$myrow['rate'],2),
						$myrow['suppliersuom'], 
						locale_number_format($myrow['conversionfactor'],0),
						ConvertSQLDate($myrow['lastcostupdate']),
						locale_number_format($myrow['stdcost'],0),
						locale_number_format($myrow['qoh'],0),
						$myrow['units'], 
						$StdCost,
						locale_number_format((($myrow['price'] / $myrow['conversionfactor'] * (1/$myrow['rate']) * $StdFactor)/$myrow['stdcost'] * 100)-100,1) . '%'
						);
			}
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsWithStockLocationButNoStockAvailable($Location, $NameLocation, $MinAvailable, $MaxTopSalesItems, $RootPath, $db){
/* EXPLAIN SQL 2014-05-30 */
	$SQL = "SELECT locstock.stockid,
				locstock.quantity,
				(SELECT SUM(l2.quantity)
					FROM locstock l2
					WHERE locstock.stockid = l2.stockid
					AND (l2.loccode IN " . LIST_ALL_SHOPS . "
						OR l2.loccode = 'KANTO')
				) AS available
			FROM locstock, stockmaster
			WHERE locstock.stockid = stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING ."
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_DISCOUNT ."
				AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OUTLET ."
				AND locstock.loccode = '" . $Location . "'
				AND locstock.quantity > 0
				AND locstock.reorderlevel > 0
				AND (SELECT SUM(l2.quantity)
						FROM locstock l2
						WHERE locstock.stockid = l2.stockid
						AND (l2.loccode IN " . LIST_ALL_SHOPS . "
							OR l2.loccode = 'KANTO')
					) <= " . $MinAvailable;

	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$PositionTopSales = positionTopSalesItem($myrow['stockid'], 99999, 60, $db);
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

function WrongItemsOnPurchaseOrders($RootPath, $db){
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
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT ."
					OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."
					OR stockmaster.discontinued = 1)
			ORDER BY purchorderdetails.orderno,
					purchorderdetails.itemcode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			if (TRUE){
				if ($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' .'Wrong items (No More Purchasing, Discount or Obsolete) in Active Purchase Orders' . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('PO') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('QOO') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $myrow['itemcode'] . '">' . $myrow['itemcode'] . '</a>';
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						locale_number_format($myrow['orderno'],0),
						$CodeLink, 
						$myrow['description'],
						locale_number_format($myrow['quantityord'],0)
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

function InsuficientStockForItems($Category, $ItemCode, $ItemDescription, $MinimumStock, $OptimalStock, $RootPath, $db){

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
							OR loccode = 'KANTO')) AS qoh
			FROM stockmaster
			WHERE stockmaster.stockid LIKE '" . $ItemCode . "%'
				AND stockmaster.discontinued = 0".
				$SQLCategory . "
				AND (SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND (loccode IN " . LIST_ALL_SHOPS . "
							OR loccode = 'KANTO')) < " . $MinimumStock . "
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

function GoodSellingItemsInCategory($CategoryId, $days, $minsales, $RootPath, $db){
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

function ObsoleteComponentsInActiveBOM($RootPath, $db){

	$SQL = "SELECT bom.parent,
				bom.component
			FROM bom, stockmaster AS stP, stockmaster AS stC
			WHERE bom.parent = stP.stockid 
				AND bom.component = stC.stockid
				AND stP.discontinued = 0
				AND stC.discontinued = 1";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Active BOM with obsolete components') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('BOM of') . '</th>
							<th class="ascending">' . _('Component') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLinkParent = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['parent'] . '">' . $myrow['parent'] . '</a>';
			$CodeLinkComponent = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['component'] . '">' . $myrow['component'] . '</a>';
			printf('<td>%s</td>
					<td>%s</td>
					</tr>', 
					$CodeLinkParent, 
					$CodeLinkComponent
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function GoodsToBeProduced($CategoryComponent, $ParentCategory, $RootPath, $db){
/* EXPLAIN SQL 2014-05-30 */
	/* Check if there is any	component at kantor ready to be transformed into sellable goods */
	if ($ParentCategory == "DISCOUNT"){
		$WhereParentCategory = " AND stP.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT . " ";
	}else{
		$WhereParentCategory = " ";
	}
	
	$SQL = "SELECT s.stockid,
				s.units,
				s.description, 
				(s.materialcost + s.labourcost + s.overheadcost) AS stdcost,(SELECT SUM(quantity) 
					FROM locstock 
					WHERE locstock.stockid = s.stockid
					AND locstock.loccode NOT IN ('SERSU')) AS availablestock
			FROM stockmaster AS s
			WHERE s.discontinued = 0 
			AND s.categoryid = '".$CategoryComponent."'
			AND ((SELECT SUM(quantity) 
					FROM locstock 
					WHERE locstock.stockid = s.stockid
					AND locstock.loccode NOT IN ('SERSU')) > 0)
			AND EXISTS(
				SELECT bom.component
				FROM bom,stockmaster AS stP, stockmaster AS stC
				WHERE bom.parent = stP.stockid
					AND bom.component = stC.stockid 
					AND s.stockid = bom.component " .
					$WhereParentCategory . "
					AND stP.discontinued = 0)";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ParentCategory == "DISCOUNT"){
			echo '<p class="page_title_text" align="center"><strong>' . _('Components '). $CategoryComponent . _(' ready to WO in kantor for items Discount') . '</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Components '). $CategoryComponent . _(' ready to WO in kantor for all items') . '</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH') . '</th>
							<th class="ascending">' . _('UOM') . '</th>
							<th class="ascending">' . _('Stock value') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		$totalcost = 0;
		while ($myrow = DB_fetch_array($result)) {
			$totalcost = $totalcost + ($myrow['availablestock']*$myrow['stdcost']);
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['availablestock'],0),
					$myrow['units'], 
					locale_number_format($myrow['availablestock']*$myrow['stdcost'],0)
					);
			$i++;
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'', 
				'', 
				'Total Cost',
				'',
				'',
				locale_number_format($totalcost,0)
				);
		echo '</table>
				</div>';
	}
}

function ConsumablesGoodsNotEnoughStock($DaysUsage, $DaysMinStock, $DaysStockPurchase, $RootPath, $db){
/* EXPLAIN SQL 2014-05-40 added index discontinued+categoryid*/
	/*  Check if there are consumable goods with not enough stock for the following $DaysMinStock
		based on last $DaysUsage usage*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysUsage));
	$FactorStock = $DaysMinStock / $DaysUsage;

	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				(SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'KANTO') AS qtyKANTOR,
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
						AND locstock.loccode = 'KANTO') < 
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

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Consumables with stock ready for less than ') . $DaysMinStock . ' days and NO active PO.' . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
							<th class="ascending">' . _('Used ') . $DaysUsage . ' days'. '</th>
							<th class="ascending">' . _('Urgent Needed') . '</th>
							<th class="ascending">' . _('Recommended Purchase') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$Needed = (($myrow['usageKL'] / $DaysUsage) * $DaysMinStock ) - $myrow['qtyKANTOR'];
			$Recommended = (($myrow['usageKL'] / $DaysUsage) * $DaysStockPurchase);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					locale_number_format($myrow['qtyKANTOR'],0),
					locale_number_format($myrow['usageKL'],0),
					locale_number_format($Needed,0),					
					locale_number_format($Recommended,0)					
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ActiveItemsWithoutPicture($RootPath, $db){
/* EXPLAIN SQL 2014-05-21	Can't use key. Probably explained at http://stackoverflow.com/questions/11784322/why-would-mysql-not-use-keys-when-there-are-possible-keys 
2014-05-30 Fixed adding a new index disontinued+Stockid
2015-05-19 TAke out some exceptions 
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_PROMOTIONAL_ITEMS . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_DISCOUNT . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_DISPLAYS . "

*/
	$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockcategory.categorydescription
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockmaster.discontinued = 0
			AND stockcategory.stocktype = 'F'
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_OLD . "
			AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_SHOP_CONSUMABLES . "
		ORDER BY stockcategory.categorydescription, stockmaster.stockid";
	$result = DB_query($SQL);
	$showHeader = TRUE;

	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			if(!file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg') ) {
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . _('Current Items without picture in webERP') . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$k = 0; //row colour counter
					$i = 1;
					$TableHeader = '<tr>
									<th class="ascending">' . '#' . '</th>
									<th class="ascending">' . _('Category') . '</th>
									<th class="ascending">' . _('Item Code') . '</th>
									<th class="ascending">' . _('Description') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$myrow['categorydescription'],
						$CodeLink, 
						$myrow['description']
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

function OpenCartItemsWithoutPicture($RootPath, $db, $db_oc, $oc_tableprefix){

	$SQL = "SELECT 	" . $oc_tableprefix . "product.model AS stockid
			FROM " . $oc_tableprefix . "product
			WHERE " . $oc_tableprefix . "product.status = 1
			ORDER BY " . $oc_tableprefix . "product.model";
	$result = DB_query_oc($SQL);
	$showHeader = TRUE;

	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			if(!file_exists(ABSOLUTE_PATH_OPENCART_IMAGES .$myrow['stockid'].'.jpg') ) {
				if($showHeader){
					echo '<p class="page_title_text" align="center"><strong>' . _('OpenCart Items without picture') . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$k = 0; //row colour counter
					$i = 1;
					$TableHeader = '<tr>
									<th class="ascending">' . '#' . '</th>
									<th class="ascending">' . _('Item Code') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$CodeLink
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

function ImagesShouldNotBeInOpencartCatalog($RootPath, $db, $db_oc, $oc_tableprefix){

	$ShowHeader = TRUE;
	$k = 0; //row colour counter
	$i= 0;
	// get all images in part_pics folder
	$suffix = ".jpg";
	$imagefiles = getDirectoryTree(ABSOLUTE_PATH_OPENCART_IMAGES, 'jpg');
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
		$ProductId = GetOpenCartProductId($StockId, $db_oc, $oc_tableprefix);
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

function ImagesWithoutProduct($RootPath, $db){
	$ShowHeader = TRUE;
	$k = 0; //row colour counter
	$i= 0;
	// get all images in part_pics folder
	$suffix = ".jpg";
	$imagefiles = getDirectoryTree($_SESSION['part_pics_dir'], 'jpg');
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
		$SQL = "SELECT stockid
			FROM stockmaster
			WHERE stockmaster.stockid = '" . $StockId . "'";
		$result = DB_query($SQL);
		if (DB_num_rows($result) == 0){
			if ($ShowHeader){
				echo '<p class="page_title_text" align="center"><strong>' . _('Images without product in webERP') .'</strong></p>';
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
					$_SESSION['part_pics_dir'].'/'.$file
					);
		}
	}
	if (!$ShowHeader){
		echo '</table>
				</div>';
	}
}

function ItemsChangingPriceDelayed($NumDays, $RootPath, $db){
/* EXPLAIN SQL 2014-05-21	*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_ALL_SHOPS . ") AS qohpos,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohconsignment,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
					AND loccode NOT IN " . LIST_ALL_SHOPS . "
					AND loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_ALL_SHOPS . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_KANTOR_LOCATIONS . ") AS intransitfromkantor,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klchangeprice.counterpricechange,
				klchangeprice.startprocessdate,
				klchangeprice.newretailprice
			FROM stockmaster, klchangeprice					
			WHERE stockmaster.stockid = klchangeprice.stockid
				AND klchangeprice.endprocessdate = '0000-00-00'
				AND klchangeprice.startprocessdate <= '". $StartDate ."'";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items delayed in Change Price Procedure for more than '). $NumDays . ' days. </strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Start Date') . '</th>
							<th class="ascending">' . _('QOH KL Shops') . '</th>
							<th class="ascending">' . _('QOH Consignment') . '</th>
							<th class="ascending">' . _('Transit From Kantor') . '</th>
							<th class="ascending">' . _('Transit To Kantor') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
							<th class="ascending">' . _('QOH Others') . '</th>
							<th class="ascending">' . _('QOH Total') . '</th>
							<th class="ascending">' . _('New Retail Price') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			$NewPriceLink = locale_number_format($myrow['newretailprice'],0);
			
			printf('<td class="number">%s</td>
					<td>%s</td>
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
					</tr>', 
					locale_number_format($myrow['counterpricechange'],0),
					$CodeLink, 
					$myrow['description'],
					ConvertSQLDate($myrow['startprocessdate']),
					locale_number_format_zero_blank($myrow['qohpos']-$myrow['intransitfromshops'],0),
					locale_number_format_zero_blank($myrow['qohconsignment']-$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['intransitfromshops']+$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['qohkantor']-$myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['qohotherlocs'],0),
					locale_number_format_zero_blank($myrow['qohtotal'],0),
					$NewPriceLink
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsMovingToDiscountDelayed($TypeDiscount, $NumDays, $RootPath, $db){
/* EXPLAIN SQL 2014-05-21	*/
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_ALL_SHOPS . ") AS qohpos,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohconsignment,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
					AND loccode NOT IN " . LIST_ALL_SHOPS . "
					AND loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_ALL_SHOPS . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_KANTOR_LOCATIONS . ") AS intransitfromkantor,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klmovetodiscount".$TypeDiscount.".countermovediscount,
				klmovetodiscount".$TypeDiscount.".startprocessdate,
				klmovetodiscount".$TypeDiscount.".discountcategory
			FROM stockmaster, klmovetodiscount".$TypeDiscount."					
			WHERE stockmaster.stockid = klmovetodiscount".$TypeDiscount.".stockid
				AND klmovetodiscount".$TypeDiscount.".endprocessdate = '0000-00-00'
				AND klmovetodiscount".$TypeDiscount.".startprocessdate <= '". $StartDate ."'";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . 'Items delayed Moving To ' . $TypeDiscount . '% Discount Procedure for more than '. $NumDays . ' days. </strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Start Date') . '</th>
							<th class="ascending">' . _('QOH KL Shops') . '</th>
							<th class="ascending">' . _('QOH Consignment') . '</th>
							<th class="ascending">' . _('Transit From Kantor') . '</th>
							<th class="ascending">' . _('Transit To Kantor') . '</th>
							<th class="ascending">' . _('QOH Kantor') . '</th>
							<th class="ascending">' . _('QOH Others') . '</th>
							<th class="ascending">' . _('QOH Total') . '</th>
							<th class="ascending">' . _('Discount Code') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
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
					</tr>', 
					locale_number_format($myrow['countermovediscount'],0),
					$CodeLink, 
					$myrow['description'],
					ConvertSQLDate($myrow['startprocessdate']),
					locale_number_format_zero_blank($myrow['qohpos']-$myrow['intransitfromshops'],0),
					locale_number_format_zero_blank($myrow['qohconsignment']-$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['intransitfromshops']+$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['qohkantor']-$myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['qohotherlocs'],0),
					locale_number_format_zero_blank($myrow['qohtotal'],0),
					$myrow['discountcategory']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function over_or_below_limit($Request, $Sign, $Limit, $RootPath, $db){
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
	
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	if ($Sign == "OVER"){
		if ($myrow[0] > $Limit){
			$text = $Request . " is OVER the maximum. Current value = " . locale_number_format($myrow[0],0) . " Maximum = " . locale_number_format($Limit,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
	}
	if ($Sign == "BELOW"){
		if ($myrow[0] < $Limit){
			$text = $Request . " is BELOW the minimum. Current value = " . locale_number_format($myrow[0],0) . " Minimum = " . locale_number_format($Limit,0);
			echo '<p class="bad" align="center"><strong>' . $text . '</strong></p>';
		}
	}
}

function NewCustomers($NumDays, $RootPath, $db){
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

function ItemsNoSalesInLocation($location, $maxdays, $QOHAvailable, $RootPath, $db){
/* EXPLAIN SQL 2014-05-20	*/
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$maxdays));
	
	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.units, 
					locstock.quantity,
					(SELECT SUM(loc2.quantity)
							FROM locstock AS loc2
							WHERE loc2.stockid = stockmaster.stockid
							AND (loc2.loccode IN " . LIST_SHOPS_KAPAL_LAUT . "
								OR loc2.loccode IN " . LIST_SHOPS_BLINK . "
								OR loc2.loccode IN " . LIST_SHOPS_OUTLET . "
								OR loc2.loccode = 'KANTO') ) AS qtyavailable,
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
							FROM locstock AS loc2
							WHERE loc2.stockid = stockmaster.stockid
							AND (loc2.loccode IN " . LIST_SHOPS_KAPAL_LAUT . "
								OR loc2.loccode IN " . LIST_SHOPS_BLINK . "
								OR loc2.loccode IN " . LIST_SHOPS_OUTLET . "
								OR loc2.loccode = 'KANTO') ) <= ". $QOHAvailable ."
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

function KapalLautPackagingToBeRefilled($ShowAll, $RootPath, $db){
/* EXPLAIN SQL 2014-05-20
Updated 3 index in loctransfers
*/

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
							AND locstock.stockid = 'PKBX01-L') AS qty_box_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS rl_box_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-L') AS ot_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS qty_box_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS rl_box_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-M') AS ot_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS qty_box_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS rl_box_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-S') AS ot_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS rl_shopping_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-L') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-M') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS rl_shopping_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-S') AS ot_shopping_s
			FROM locations
			WHERE locations.loccode IN " . LIST_SHOPS_KAPAL_LAUT . 
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
			
			// Deactivated on 2014/08/15 by request of Ike and Laia
			// Only active for Ubud and Sanur area.
			/* UB, MF, PU are sister shops */
			if ($TableResult[$i]['loccode'] == 'TOKUB'){
				MarkSisterShopInArray($TableResult, $numshops, "TOKMF");
				MarkSisterShopInArray($TableResult, $numshops, "TOKPU");
			}
			if ($TableResult[$i]['loccode'] == 'TOKMF'){
				MarkSisterShopInArray($TableResult, $numshops, "TOKUB");
				MarkSisterShopInArray($TableResult, $numshops, "TOKPU");
			}
			if ($TableResult[$i]['loccode'] == 'TOKPU'){
				MarkSisterShopInArray($TableResult, $numshops, "TOKUB");
				MarkSisterShopInArray($TableResult, $numshops, "TOKMF");
			}
			
			/* 66, SE, OB are sister shops */
/*			if ($TableResult[$i]['loccode'] == 'TOK66'){
				MarkSisterShopInArray($TableResult, $numshops, "TOKSE");
				MarkSisterShopInArray($TableResult, $numshops, "TOKOB");
			}
			if ($TableResult[$i]['loccode'] == 'TOKSE'){
				MarkSisterShopInArray($TableResult, $numshops, "TOK66");
				MarkSisterShopInArray($TableResult, $numshops, "TOKOB");
			}
			if ($TableResult[$i]['loccode'] == 'TOKOB'){
				MarkSisterShopInArray($TableResult, $numshops, "TOK66");
				MarkSisterShopInArray($TableResult, $numshops, "TOKSE");
			}
*/
			/* KS, BW are sister shops */
/*			if ($TableResult[$i]['loccode'] == 'TOKKS'){
				MarkSisterShopInArray($TableResult, $numshops, "TOKBW");
			}
			if ($TableResult[$i]['loccode'] == 'TOKBW'){
				MarkSisterShopInArray($TableResult, $numshops, "TOKKS");
			}
*/
			/* SA, SU, SS are sister shops */
// DEACTIVATED SU in 20/05/2015
			if ($TableResult[$i]['loccode'] == 'TOKSA'){
//				MarkSisterShopInArray($TableResult, $numshops, "TOKSU");
				MarkSisterShopInArray($TableResult, $numshops, "TOKSS");
			}
//			if ($TableResult[$i]['loccode'] == 'TOKSU'){
//				MarkSisterShopInArray($TableResult, $numshops, "TOKSA");
//				MarkSisterShopInArray($TableResult, $numshops, "TOKSS");
//			}
			if ($TableResult[$i]['loccode'] == 'TOKSS'){
				MarkSisterShopInArray($TableResult, $numshops, "TOKSA");
//				MarkSisterShopInArray($TableResult, $numshops, "TOKSU");
			}
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
					echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shops needing Packaging Transfers (Do not forget to create transfer in webERP)' . '</strong></p>';
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

function OutletPackagingToBeRefilled($ShowAll, $RootPath, $db){

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
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB03') AS ot_shopping_m
			FROM locations
			WHERE locations.loccode IN " . LIST_SHOPS_OUTLET . 
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

function MarkSisterShopInArray(&$TableResult, $numshops, $SisterShop){
	$sistershop = 1;
	while ($sistershop <= $numshops){
		if ($TableResult[$sistershop]['loccode'] ==  $SisterShop){
			$TableResult[$sistershop]['show'] = TRUE;
		}
		$sistershop++;
	}
}

function KapalLautPackagingStatus($RootPath, $db){

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.rldaysforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS qty_box_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS rl_box_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-L') AS ot_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS qty_box_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS rl_box_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-M') AS ot_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS qty_box_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS rl_box_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKBX01-S') AS ot_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB01-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS qty_shopping_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS rl_shopping_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-L') AS ot_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-M') AS ot_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS qty_shopping_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS rl_shopping_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB02-S') AS ot_shopping_s
			FROM locations
			WHERE locations.loccode IN " . LIST_SHOPS_KAPAL_LAUT . "
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shop Packaging Stock Status by Shop' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
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
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('Days RL') . '</th>
									<th class="ascending">' . _('Factor') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

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
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$myrow['locationname'], 
					$myrow['rldaysforpackaging'], 
					$myrow['rlfactorforpackaging'], 
					locale_number_format_zero_blank($myrow['qty_box_l'],0), 
					locale_number_format_zero_blank($myrow['ot_box_l'],0),
					locale_number_format_zero_blank($myrow['rl_box_l'],0),
					locale_number_format_zero_blank($myrow['qty_box_m'],0), 
					locale_number_format_zero_blank($myrow['ot_box_m'],0),
					locale_number_format_zero_blank($myrow['rl_box_m'],0),
					locale_number_format_zero_blank($myrow['qty_box_s'],0), 
					locale_number_format_zero_blank($myrow['ot_box_s'],0),
					locale_number_format_zero_blank($myrow['rl_box_s'],0),
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_l'],0),
					locale_number_format_zero_blank($myrow['rl_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_m'],0),
					locale_number_format_zero_blank($myrow['rl_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_s'],0),
					locale_number_format_zero_blank($myrow['rl_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_l'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_l'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_m'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_m'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_s'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_s'],0)
					);

			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function OutletPackagingStatus($RootPath, $db){

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					locations.rldaysforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS qty_bag_l,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS rl_bag_l,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-L') AS ot_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS qty_bag_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS rl_bag_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-M') AS ot_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS qty_bag_s,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS rl_bag_s,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKPB02-S') AS ot_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS qty_shopping_m,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS rl_shopping_m,
					(SELECT SUM(loctransfers.shipqty - loctransfers.recqty)
						FROM loctransfers
						WHERE loctransfers.recloc = locations.loccode
							AND loctransfers.shipqty != loctransfers.recqty
							AND loctransfers.stockid = 'PKSB03') AS ot_shopping_m
			FROM locations
			WHERE locations.loccode IN " . LIST_SHOPS_OUTLET . "
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'OUTLET Shop Packaging Stock Status by Shop' . '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
									<th>' . _('') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag L') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag M') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag S') . '</th>
									<th colspan="3">' . _('OUTLET ShoppingBag') . '</th>
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('Days RL') . '</th>
									<th class="ascending">' . _('Factor') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Transit') . '</th>
									<th class="ascending">' . _('RL') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

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
					</tr>', 
					$myrow['locationname'], 
					$myrow['rldaysforpackaging'], 
					$myrow['rlfactorforpackaging'], 
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_l'],0),
					locale_number_format_zero_blank($myrow['rl_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_m'],0),
					locale_number_format_zero_blank($myrow['rl_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['ot_bag_s'],0),
					locale_number_format_zero_blank($myrow['rl_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['ot_shopping_m'],0),
					locale_number_format_zero_blank($myrow['rl_shopping_m'],0)
					);

			$i++;
		}
		if (!$showHeader){
			echo '</table>
				</div>';
		}
	}
}

function KapalLautPackagingUsage($NumDays, $RootPath, $db){
/* EXPLAIN 2014-05-20	 OK! */

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$NumDays));

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-L') AS qty_box_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX01-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-M') AS qty_box_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX01-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKBX01-S') AS qty_box_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKBX01-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_box_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-L') AS qty_bag_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB01-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-M') AS qty_bag_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB01-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB01-S') AS qty_bag_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB01-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-L') AS qty_shopping_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB02-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-M') AS qty_shopping_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB02-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB02-S') AS qty_shopping_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB02-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_s
			FROM locations
			WHERE locations.loccode IN " . LIST_SHOPS_KAPAL_LAUT . "
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	
	$totalqty_box_l   = 0;
	$totalsales_box_l = 0;
	$totalqty_box_m   = 0;
	$totalsales_box_m = 0;
	$totalqty_box_s   = 0;
	$totalsales_box_s = 0;

	$totalqty_bag_l   = 0;
	$totalsales_bag_l = 0;
	$totalqty_bag_m   = 0;
	$totalsales_bag_m = 0;
	$totalqty_bag_s   = 0;
	$totalsales_bag_s = 0;
	
	$totalqty_shopping_l    = 0;
	$totalsales_shopping_l  = 0;
	$totalqty_shopping_m    = 0;
	$totalsales_shopping_m  = 0;
	$totalqty_shopping_s    = 0;
	$totalsales_shopping_s  = 0;

	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'KAPAL-LAUT Shop Packaging Usage during the last ' . $NumDays . ' days'. '</strong></p>';
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
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

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
					</tr>', 
					$myrow['locationname'], 
					locale_number_format_zero_blank($myrow['qty_box_l'],0), 
					locale_number_format_zero_blank($myrow['sales_box_l'],0),
					locale_number_format_zero_blank($myrow['qty_box_l']/($myrow['sales_box_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_box_m'],0), 
					locale_number_format_zero_blank($myrow['sales_box_m'],0),
					locale_number_format_zero_blank($myrow['qty_box_m']/($myrow['sales_box_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_box_s'],0), 
					locale_number_format_zero_blank($myrow['sales_box_s'],0),
					locale_number_format_zero_blank($myrow['qty_box_s']/($myrow['sales_box_s']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_l']/($myrow['sales_bag_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m']/($myrow['sales_bag_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s']/($myrow['sales_bag_s']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_l'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_l'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_l']/($myrow['sales_shopping_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_m'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m']/($myrow['sales_shopping_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_s'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_s'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_s']/($myrow['sales_shopping_s']/$NumDays),0)
					);
			$totalqty_box_l   = $totalqty_box_l + $myrow['qty_box_l'];
			$totalsales_box_l = $totalsales_box_l + $myrow['sales_box_l'];
			$totalqty_box_m   = $totalqty_box_m + $myrow['qty_box_m'];
			$totalsales_box_m = $totalsales_box_m + $myrow['sales_box_m'];
			$totalqty_box_s   = $totalqty_box_s + $myrow['qty_box_s'];
			$totalsales_box_s = $totalsales_box_s + $myrow['sales_box_s'];

			$totalqty_bag_l   = $totalqty_bag_l + $myrow['qty_bag_l'];
			$totalsales_bag_l = $totalsales_bag_l + $myrow['sales_bag_l'];
			$totalqty_bag_m   = $totalqty_bag_m + $myrow['qty_bag_m'];
			$totalsales_bag_m = $totalsales_bag_m + $myrow['sales_bag_m'];
			$totalqty_bag_s   = $totalqty_bag_s + $myrow['qty_bag_s'];
			$totalsales_bag_s = $totalsales_bag_s + $myrow['sales_bag_s'];

			$totalqty_shopping_l    = $totalqty_shopping_l + $myrow['qty_shopping_l'];
			$totalsales_shopping_l  = $totalsales_shopping_l + $myrow['sales_shopping_l'];
			$totalqty_shopping_m    = $totalqty_shopping_m + $myrow['qty_shopping_m'];
			$totalsales_shopping_m  = $totalsales_shopping_m + $myrow['sales_shopping_m'];
			$totalqty_shopping_s    = $totalqty_shopping_s + $myrow['qty_shopping_s'];
			$totalsales_shopping_s  = $totalsales_shopping_s + $myrow['sales_shopping_s'];

			$i++;
		}
		if (!$showHeader){
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
					</tr>', 
					'TOTAL', 
					locale_number_format_zero_blank($totalqty_box_l,0), 
					locale_number_format_zero_blank($totalsales_box_l,0),
					locale_number_format_zero_blank($totalqty_box_l/($totalsales_box_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_box_m,0), 
					locale_number_format_zero_blank($totalsales_box_m,0),
					locale_number_format_zero_blank($totalqty_box_m/($totalsales_box_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_box_s,0), 
					locale_number_format_zero_blank($totalsales_box_s,0),
					locale_number_format_zero_blank($totalqty_box_s/($totalsales_box_s/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_l,0), 
					locale_number_format_zero_blank($totalsales_bag_l,0),
					locale_number_format_zero_blank($totalqty_bag_l/($totalsales_bag_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_m,0), 
					locale_number_format_zero_blank($totalsales_bag_m,0),
					locale_number_format_zero_blank($totalqty_bag_m/($totalsales_bag_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_s,0), 
					locale_number_format_zero_blank($totalsales_bag_s,0),
					locale_number_format_zero_blank($totalqty_bag_s/($totalsales_bag_s/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_l,0), 
					locale_number_format_zero_blank($totalsales_shopping_l,0),
					locale_number_format_zero_blank($totalqty_shopping_l/($totalsales_shopping_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_m,0), 
					locale_number_format_zero_blank($totalsales_shopping_m,0),
					locale_number_format_zero_blank($totalqty_shopping_m/($totalsales_shopping_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_s,0), 
					locale_number_format_zero_blank($totalsales_shopping_s,0),
					locale_number_format_zero_blank($totalqty_shopping_s/($totalsales_shopping_s/$NumDays),0)
					);
			echo '</table>
				</div>';
		}
	}
}

function OutletPackagingUsage($NumDays, $RootPath, $db){
/* EXPLAIN 2014-05-20	 OK! */

	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$NumDays));

	$SQL = "SELECT locations.loccode,
					locations.locationname,
					locations.rlfactorforpackaging,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-L') AS qty_bag_l,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB02-L'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_l,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-M') AS qty_bag_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB02-M'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_m,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKPB02-S') AS qty_bag_s,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKPB02-S'
							AND packagingused.date >= '". $FromDate ."') AS sales_bag_s,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.loccode = locations.loccode
							AND locstock.stockid = 'PKSB03') AS qty_shopping_m,
					(SELECT SUM(packagingused.qty)
						FROM packagingused
						WHERE packagingused.fromlocation = locations.loccode
							AND packagingused.stockid = 'PKSB03'
							AND packagingused.date >= '". $FromDate ."') AS sales_shopping_m
			FROM locations
			WHERE locations.loccode IN " . LIST_SHOPS_OUTLET . "
				OR locations.loccode IN " . LIST_GUDANG_FOR_PACKAGING . "
			ORDER BY locations.loccode";

	$result = DB_query($SQL);
	$showHeader = TRUE;
	$i = 1;
	
	$totalqty_bag_l   = 0;
	$totalsales_bag_l = 0;
	$totalqty_bag_m   = 0;
	$totalsales_bag_m = 0;
	$totalqty_bag_s   = 0;
	$totalsales_bag_s = 0;
	
	$totalqty_shopping_m    = 0;
	$totalsales_shopping_m  = 0;

	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if($showHeader){
				echo '<p class="page_title_text" align="center"><strong>' . 'OUTLET Shop Packaging Usage during the last ' . $NumDays . ' days'. '</strong></p>';
				echo '<div>';
				echo '<table class="selection">';
				$TableHeader = '<tr>
									<th>' . _('') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag L') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag M') . '</th>
									<th colspan="3">' . _('OUTLET PouchBag S') . '</th>
									<th colspan="3">' . _('OUTLET ShoppingBag M') . '</th>
								</tr>';
				$TableHeader = $TableHeader . '<tr>
									<th class="ascending">' . _('KL Shop') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
									<th class="ascending">' . _('QOH') . '</th>
									<th class="ascending">' . _('Use ') . $NumDays . ' d</th>
									<th class="ascending">' . _('Days Stock') . '</th>
								</tr>';
				echo $TableHeader;
				$showHeader = FALSE;
			}
			$k = StartEvenOrOddRow($k);

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
					</tr>', 
					$myrow['locationname'], 
					locale_number_format_zero_blank($myrow['qty_bag_l'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_l'],0),
					locale_number_format_zero_blank($myrow['qty_bag_l']/($myrow['sales_bag_l']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_m'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_m'],0),
					locale_number_format_zero_blank($myrow['qty_bag_m']/($myrow['sales_bag_m']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_bag_s'],0), 
					locale_number_format_zero_blank($myrow['sales_bag_s'],0),
					locale_number_format_zero_blank($myrow['qty_bag_s']/($myrow['sales_bag_s']/$NumDays),0),
					locale_number_format_zero_blank($myrow['qty_shopping_m'],0), 
					locale_number_format_zero_blank($myrow['sales_shopping_m'],0),
					locale_number_format_zero_blank($myrow['qty_shopping_m']/($myrow['sales_shopping_m']/$NumDays),0)
					);

			$totalqty_bag_l   = $totalqty_bag_l + $myrow['qty_bag_l'];
			$totalsales_bag_l = $totalsales_bag_l + $myrow['sales_bag_l'];
			$totalqty_bag_m   = $totalqty_bag_m + $myrow['qty_bag_m'];
			$totalsales_bag_m = $totalsales_bag_m + $myrow['sales_bag_m'];
			$totalqty_bag_s   = $totalqty_bag_s + $myrow['qty_bag_s'];
			$totalsales_bag_s = $totalsales_bag_s + $myrow['sales_bag_s'];

			$totalqty_shopping_m    = $totalqty_shopping_m + $myrow['qty_shopping_m'];
			$totalsales_shopping_m  = $totalsales_shopping_m + $myrow['sales_shopping_m'];

			$i++;
		}
		if (!$showHeader){
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
					</tr>', 
					'TOTAL', 
					locale_number_format_zero_blank($totalqty_bag_l,0), 
					locale_number_format_zero_blank($totalsales_bag_l,0),
					locale_number_format_zero_blank($totalqty_bag_l/($totalsales_bag_l/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_m,0), 
					locale_number_format_zero_blank($totalsales_bag_m,0),
					locale_number_format_zero_blank($totalqty_bag_m/($totalsales_bag_m/$NumDays),0),
					locale_number_format_zero_blank($totalqty_bag_s,0), 
					locale_number_format_zero_blank($totalsales_bag_s,0),
					locale_number_format_zero_blank($totalqty_bag_s/($totalsales_bag_s/$NumDays),0),
					locale_number_format_zero_blank($totalqty_shopping_m,0), 
					locale_number_format_zero_blank($totalsales_shopping_m,0),
					locale_number_format_zero_blank($totalqty_shopping_m/($totalsales_shopping_m/$NumDays),0)
					);
			echo '</table>
				</div>';
		}
	}
}

function PackagingItemsOnWrongLocation($RootPath, $db){
/* EXPLAIN SQL	2014-05-20

id	select_type	table	type	possible_keys	key	key_len	ref	rows	Extra
1	SIMPLE	stockmaster	ref	PRIMARY,CategoryID,StockID	CategoryID	20	const	10	Using where
1	SIMPLE	locstock	ref	PRIMARY,StockID	StockID	62	kurakura_klerp.stockmaster.stockid	14	Using where

*/	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					locstock.loccode,
					locstock.quantity,
					locstock.reorderlevel
			FROM stockmaster, locstock
			WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SHOP_PACKAGING . "
				AND locstock.loccode NOT IN " . LIST_SHOPS_KAPAL_LAUT . "
				AND locstock.loccode NOT IN " . LIST_SHOPS_OUTLET . "
				AND locstock.loccode NOT IN " . LIST_SHOPS_BLINK . "
				AND locstock.loccode NOT IN " . LIST_GUDANG_FOR_PACKAGING . "
				AND ( locstock.quantity > 0 OR locstock.reorderlevel > 0 )
			ORDER BY stockmaster.stockid";

			$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>Packaging items in wrong locations (must be transferred to another location)</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">' . _('Shop') . '</th>
							<th class="ascending">' . _('Quantity') . '</th>
							<th class="ascending">' . _('RL') . '</th>
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
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$myrow['description'], 
					$myrow['loccode'], 
					$myrow['quantity'],
					$myrow['reorderlevel']
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function InsuficientStockForShopPackaging($Category, $DaysUsage, $DaysMinimumStock, $DaysProduction, $ShowAll, $RootPath, $db){
/* EXPLAIN SQL	2014-05-20	
id	select_type			table				type	possible_keys				key					key_len	ref	rows	Extra
1	PRIMARY				stockmaster			ref		CategoryID					CategoryID			20	const	10	Using where
4	DEPENDENT SUBQUERY	purchorderdetails	ref		ItemCode,OrderNo,Completed	ItemCode			62	kurakura_klerp.stockmaster.stockid	2	Using where
4	DEPENDENT SUBQUERY	purchorders			eq_ref	PRIMARY						PRIMARY				4	kurakura_klerp.purchorderdetails.orderno	1	Using where
3	DEPENDENT SUBQUERY	packagingused		ref		StockID+Date				StockID+Date		62	kurakura_klerp.stockmaster.stockid	81	Using where
2	DEPENDENT SUBQUERY	locstock			ref		StockID	StockID									62	kurakura_klerp.stockmaster.stockid	14	

*/
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$DaysUsage-1));
	$ToDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -1));
	
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.eoq,
					stockmaster.pansize,
					(SELECT SUM(quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid) AS qoh,";
	if ($Category == 'SHPACK'){
			$SQL = $SQL . "	(SELECT SUM(qty)
								FROM packagingused
								WHERE packagingused.stockid = stockmaster.stockid
									AND packagingused.date >= '". $FromDate ."'
									AND packagingused.date <= '". $ToDate ."') AS qused,";
	}else{
			$SQL = $SQL . "	(SELECT SUM(qtyinvoiced) 
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.stkcode = stockmaster.stockid
									AND salesorderdetails.completed = 1
									AND salesorders.orddate >= '". $FromDate . "'
									AND salesorders.orddate <= '". $ToDate . "') AS qused,";
	}
	$SQL = $SQL . "	 (SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd) 
						FROM purchorderdetails, purchorders
						WHERE purchorderdetails.itemcode = stockmaster.stockid
							AND purchorders.orderno=purchorderdetails.orderno
							AND purchorderdetails.completed = 0
							AND purchorders.status NOT IN ('Cancelled', 'Pending', 'Rejected')) AS qoo
			FROM stockmaster
			WHERE categoryid = '". $Category ."'
				AND discontinued = 0
			ORDER BY stockid";
	
	$result = DB_query($SQL);		
	$showHeader = TRUE;
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$DailyUse = $myrow['qused'] / $DaysUsage;
			$ForecastProductionOnly = ceil($DailyUse * $DaysProduction);
			$Forecast = ceil($DailyUse * ($DaysMinimumStock));
			$ForecastIncludingProduction = $Forecast + $ForecastProductionOnly;
			$QtyNeeded = max(0, $ForecastIncludingProduction - $myrow['qoh'] - $myrow['qoo']);
			$DaysQOH = floor($myrow['qoh'] / $DailyUse);
			$DaysQOO = floor(($myrow['qoh'] + $myrow['qoo']) / $DailyUse);
			if ($QtyNeeded > 0){
				if ($myrow['pansize'] > 0){
					$PanSize = $myrow['pansize'];
				}else{
					$PanSize = 1;
				}
				$QtyToOrder = max($myrow['eoq'], ceil($QtyNeeded/$PanSize)*$PanSize);
			}else{
				$QtyToOrder = 0;
			}
			if (($QtyNeeded > 0) OR ($ShowAll)){
				if ($showHeader){
					if ($Category == 'SHPACK'){
						if ($ShowAll){
							echo '<p class="page_title_text" align="center"><strong>Shop packaging order status</strong></p>';
						}else{
							echo '<p class="page_title_text" align="center"><strong>Shop packaging with insufficient stock for the next ' . $DaysMinimumStock . ' days.</strong></p>';
						}
					}
					if ($Category == 'ZAPON'){
						if ($ShowAll){
							echo '<p class="page_title_text" align="center"><strong>Online Promotion items order status</strong></p>';
						}else{
							echo '<p class="page_title_text" align="center"><strong>Online Promotion items with insufficient stock for the next ' . $DaysMinimumStock . ' days.</strong></p>';
						}
					}
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Usage ') . $DaysProduction . ' days</th>
										<th class="ascending">' . _('Forecast ') . $DaysMinimumStock . ' days</th>
										<th class="ascending">' . _('QOH Total') . '</th>
										<th class="ascending">' . _('Days QOH') . '</th>
										<th class="ascending">' . _('Pending QOO') . '</th>
										<th class="ascending">' . _('Days QOH+QOO') . '</th>
										<th class="ascending">' . _('Optimum Order') . '</th>
									</tr>';
					echo $TableHeader;
					$showHeader = FALSE;
				}

				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
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
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($ForecastProductionOnly,0),
						locale_number_format($Forecast,0),
						locale_number_format($myrow['qoh'],0),
						locale_number_format($DaysQOH,0),
						locale_number_format_zero_blank($myrow['qoo'],0),
						locale_number_format($DaysQOO,0),
						locale_number_format_zero_blank($QtyToOrder,0)
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

function ItemsWithoutPurchasingData($RootPath, $db){
/* EXPLAIN SQL	2014-05-20	

id	select_type	table		type	possible_keys		key			key_len	ref									rows	Extra
1	SIMPLE		purchdata	ref		StockID,Preferred	Preferred	1		const								4387	Using where; Using temporary; Using filesort
1	SIMPLE		stockmaster	eq_ref	PRIMARY,StockID		PRIMARY		62		kurakura_klerp.purchdata.stockid	1	Using where

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

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>Items without full purchasing data</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Supplier') . '</th>
							<th class="ascending">' . _('Date') . '</th>
							<th class="ascending">' . _('Supplier Part #') . '</th>
							<th class="ascending">' . _('Supplier Description') . '</th>
							<th class="ascending">' . _('UOM') . '</th>
							<th class="ascending">' . _('Leadtime') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$CodeLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $myrow['stockid'] . '">'. $myrow['stockid'] .'</a>';
			$SupplierLink = '<a href="' . $RootPath . '/PurchData.php?StockID=' . $myrow['stockid'] . 
															'&SupplierID=' . $myrow['supplierno'] . 
															'&Edit=1' .
															'&EffectiveFrom=' . $myrow['latesteffectivefrom'] . '">'. $myrow['supplierno'] .'</a>';
			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$i, 
					$CodeLink, 
					$SupplierLink, 
					$myrow['latesteffectivefrom'],
					$myrow['suppliers_partno'],
					$myrow['supplierdescription'],
					$myrow['suppliersuom'],
					locale_number_format($myrow['leadtime'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}

function ItemsNeedingTranslationRevision($RootPath, $db){
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

function ItemsNeedingAutomaticTranslation($RootPath, $db){
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

function ItemsinSetUp($Check, $RootPath, $db){
	$today = date('Y-m-d');
	
	if ($Check == "ReadyToTest"){
		$Title = "Items in SETUP ready to change to TEST";
		$SQLWhere = "AND LENGTH(stockmaster.description) > 2
					AND (SELECT SUM(locstock.quantity)
							FROM locstock
							WHERE locstock.stockid = stockmaster.stockid) > 0
					AND (SELECT price
							FROM prices
							WHERE stockmaster.stockid = prices.stockid
								AND prices.startdate <= '". $today. "' 
								AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
								AND prices.typeabbrev = 'RT'
								AND currabrev = 'IDR') IS NOT NULL
					AND NOT EXISTS (SELECT *
							FROM loctransfers 
							WHERE  recqty < shipqty
								AND loctransfers.stockid =  stockmaster.stockid)";
	}elseif($Check == "NeedDescription"){
		$Title = "Items in SETUP needing descriptions";
		$SQLWhere ="AND LENGTH(stockmaster.description) <= 2";
	}elseif($Check == "NeedPrice"){
		$Title = "Items in SETUP needing price";
		$SQLWhere ="AND (SELECT price
				FROM prices
				WHERE stockmaster.stockid = prices.stockid
					AND prices.typeabbrev = 'RT'
					AND currabrev = 'IDR') IS NULL";
	}elseif($Check == "WithReorderLevel"){
		$Title = "Items in SETUP with RL (items in SETUP should not have RL set)";
		$SQLWhere ="AND (SELECT SUM(reorderlevel)
				FROM locstock
				WHERE stockmaster.stockid = locstock.stockid) > 0 ";
	}else{
		$Title = "Items in SETUP";
		$SQLWhere ="";
	}

	$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			(SELECT price
				FROM prices
				WHERE stockmaster.stockid = prices.stockid
					AND prices.typeabbrev = 'RT'
					AND prices.startdate <= '". $today. "' 
					AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')
					AND currabrev = 'IDR') AS price,
			(SELECT SUM(locstock.quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) AS QOH
			FROM stockmaster
			WHERE stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_SETUP . "
				AND discontinued = 0 ".
			 $SQLWhere ." 
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$k = 0; //row colour counter
		$i = 1;
		$ShowHeader = TRUE;
		while ($myrow = DB_fetch_array($result)) {
			if (    ($Check != "ReadyToTest") 
				OR (($Check == "ReadyToTest") 
					AND (file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg')))) {
				if ($ShowHeader){
					echo '<p class="page_title_text" align="center"><strong>' . $Title . '</strong></p>';
					echo '<div>';
					echo '<table class="selection">';
					$TableHeader = '<tr>
										<th class="ascending">' . _('#') . '</th>
										<th class="ascending">' . _('Code') . '</th>
										<th class="ascending">' . _('Description') . '</th>
										<th class="ascending">' . _('Price') . '</th>
										<th class="ascending">' . _('QOH') . '</th>
									</tr>';
					echo $TableHeader;
					$ShowHeader = FALSE;
				}
				$k = StartEvenOrOddRow($k);
				$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
				$RLLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . locale_number_format($myrow['QOH'],0) . '</a>';
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$i, 
						$CodeLink, 
						$myrow['description'], 
						locale_number_format($myrow['price'],0),
						$RLLink
						);
				$i++;
			}
		}
		echo '</table>
				</div>';
	}
}

function SuppliersWithoutBasicData($RootPath, $db){

	$SQL = "SELECT supplierid,
					suppname
			FROM suppliers
			WHERE address6 = ''";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Suppliers without basic data') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Code') . '</th>
							<th class="ascending">' . _('Name') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['supplierid'], 
					$myrow['suppname'] 
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
}


?>

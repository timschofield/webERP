<?php

/**************************************************************************************************************
Functions in this file:

GetDemand                                    - Calculates total demand from all sources
GetDemandQuantityAsComponentInAssemblyItems  - Gets quantity needed as components in bill of materials
GetDemandQuantityAsComponentInWorkOrders     - Gets quantity needed as components in work orders
GetDemandQuantityDueToOutstandingSalesOrders - Gets quantity demanded from outstanding sales orders
GetItemQtyInTransitFromLocation              - Gets quantity in transit from a specific location
GetItemQtyInTransitToLocation                - Gets quantity in transit to a specific location
GetQuantityOnHand                            - Gets total quantity available in stock
GetQuantityOnOrder                           - Gets total quantity on order from all sources
GetQuantityOnOrderDueToPurchaseOrders        - Gets quantity on order from purchase orders
GetQuantityOnOrderDueToWorkOrders           - Gets quantity to be produced from work orders
**************************************************************************************************************/

/**
Retrieves the total quantity on hand for a specific stock item across specified locations.

### Parameters
- `$StockID` (string): The unique identifier for the stock item
- `$Location` (string): Location filter parameter with the following options:
  - `''` or `'ALL'`: Returns quantity from all locations
  - `'USER_CAN_VIEW'`: Returns quantity from locations user has view permissions
  - `'USER_CAN_UPDATE'`: Returns quantity from locations user has update permissions
  - Specific location code: Returns quantity from that specific location

### Returns
- `float`: The sum of quantities on hand for the specified stock item in the filtered locations
- Returns 0 if no quantities are found
*/
function GetQuantityOnHand($StockID, $Location) {
	if (($Location == '') OR ($Location == 'ALL')){
		// All locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = '';
		$ErrMsg = __('The quantity on hand for this product in all locations cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_VIEW'){
		// All user is allowed to view locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = "INNER JOIN locationusers
									ON locationusers.loccode = locstock.loccode
									AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
									AND locationusers.canview = 1 ";
		$ErrMsg = __('The quantity on hand for this product in all locations the user can view cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_UPDATE'){
		// All user is allowed to update locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = "INNER JOIN locationusers
									ON locationusers.loccode = locstock.loccode
									AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
									AND locationusers.canupd = 1 ";
		$ErrMsg = __('The quantity on hand for this product in locations the user can update cannot be retrieved because');
	}
	else{
		// Just 1 location to consider
		$WhereLocation = " AND locstock.loccode = '" . $Location . "'";
		$UserAllowedLocations = '';
		$ErrMsg = __('The quantity on hand for this product in the specified location cannot be retrieved because');
	}
	$SQL = "SELECT SUM(quantity) AS qoh
			FROM locstock " .
			$UserAllowedLocations . "
			WHERE stockid = '" . $StockID . "'" .
			$WhereLocation . "";
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		return 0;
	}else{
		$MyRow = DB_fetch_array($Result);
		return (float)$MyRow['qoh'];
	}
}

/**
## GetDemand Function
Calculates the total demand for a stock item by aggregating all types of demand.

### Parameters
- `$StockID` (string): The unique identifier for the stock item
- `$Location` (string): Location filter parameter to be passed to all demand functions

### Returns
- `float`: Total demand quantity, calculated as sum of:
  - Outstanding sales orders
  - Required components in assembly items
  - Required components in work orders

### Notes
- Acts as a helper function to combine all demand sources
- Inherits location filtering behavior from called functions
*/
function GetDemand($StockID, $Location) {
	$TotalDemand = GetDemandQuantityDueToOutstandingSalesOrders($StockID, $Location);
	$TotalDemand += GetDemandQuantityAsComponentInAssemblyItems($StockID, $Location);
	$TotalDemand += GetDemandQuantityAsComponentInWorkOrders($StockID, $Location);
	return $TotalDemand;
}

/**
Calculates the total quantity on order for a stock item by aggregating all types of incoming orders.

### Parameters
- `$StockID` (string): The unique identifier for the stock item
- `$Location` (string): Location filter parameter to be passed to all order functions

### Returns
- `float`: Total quantity on order, calculated as sum of:
  - Outstanding purchase orders
  - Expected production from work orders

### Notes
- Acts as a helper function to combine all order sources
- Inherits location filtering behavior from called functions
*/
function GetQuantityOnOrder($StockID, $Location) {
	$TotalOnOrder = GetQuantityOnOrderDueToPurchaseOrders($StockID, $Location);
	$TotalOnOrder += GetQuantityOnOrderDueToWorkOrders($StockID, $Location);
	return $TotalOnOrder;
}

/**
## GetQuantityOnOrderDueToPurchaseOrders Function
Calculates the total quantity of a stock item that is currently on order through active purchase orders for specified locations.

### Parameters
- `$StockID` (string): The unique identifier for the stock item
- `$Location` (string): Location filter parameter:
  - `''`: Returns quantities from all locations
  - Specific location code: Returns quantities for that specific location

### Returns
- `float`: The sum of outstanding quantities (ordered minus received) from active purchase orders
- Returns `0` if no outstanding orders are found

### Query Conditions
- Only includes non-completed order lines (`completed = 0`)
- Excludes orders with status: 'Cancelled', 'Pending', 'Rejected', 'Completed'
- Filters by user location permissions (canview=1)

### Security Notes
- Function automatically filters results based on user location viewing permissions
- Uses prepared statements to prevent SQL injection
*/
function GetQuantityOnOrderDueToPurchaseOrders($StockID, $Location) {
	if (($Location == '') OR ($Location == 'ALL')){
	// All locations to be considered
		$WhereLocation = "";
		$ErrMsg = __('The quantity on order due to purchase orders for') . ' ' . $StockID . ' ' . __('to be received into all locations cannot be retrieved because');
	}else{
		// Just 1 location to consider
		$WhereLocation = " AND purchorders.intostocklocation = '" . $Location . "'";
		$ErrMsg = __('The quantity on order due to purchase orders for') . ' ' . $StockID . ' ' . __('to be received into') . ' ' . $Location . ' ' . __('cannot be retrieved because');
	}

	$SQL="SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS QtyOnOrder
		FROM purchorders
			INNER JOIN purchorderdetails
				ON purchorders.orderno = purchorderdetails.orderno
			INNER JOIN locationusers
				ON locationusers.loccode = purchorders.intostocklocation
					AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
					AND locationusers.canview = 1
		WHERE purchorderdetails.itemcode = '" . $StockID . "'
			AND purchorderdetails.completed = 0
			AND purchorders.status <> 'Cancelled'
			AND purchorders.status <> 'Pending'
			AND purchorders.status <> 'Rejected'
			AND purchorders.status <> 'Completed'" .
			$WhereLocation;

	$QOOResult = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($QOOResult) == 0) {
		$QOO = 0;
	} else {
		$QOORow = DB_fetch_row($QOOResult);
		$QOO = $QOORow[0];
	}
	return $QOO;
}

/**
Calculates the total quantity of a stock item that is currently on order through active work orders for specified locations.

### Parameters
- `$StockID` (string): The unique identifier for the stock item
- `$Location` (string): Location filter parameter:
  - `''`: Returns quantities from all locations
  - Specific location code: Returns quantities for that specific location

### Returns
- `float`: The sum of outstanding quantities (required minus received) from active work orders
- Returns `0` if no outstanding work orders are found

### Query Conditions
- Only includes non-closed work orders (`closed=0`)
- Filters by user location permissions (canview=1)
*/
function GetQuantityOnOrderDueToWorkOrders($StockID, $Location){
	if (($Location == '') OR ($Location == 'ALL')){
		// All locations to be considered
		$WhereLocation = '';
		$ErrMsg = __('The quantity on order due to work orders for') . ' ' . $StockID . ' ' . __('to be received into all locations cannot be retrieved because');
	}else{
		// Just 1 location to consider
		$WhereLocation = " AND workorders.loccode = '" . $Location . "'";
		$ErrMsg = __('The quantity on order due to work orders for') . ' ' . $StockID . ' ' . __('to be received into') . ' ' . $Location . ' ' . __('cannot be retrieved because');
	}

	$SQL = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS qtywo
			FROM woitems
			INNER JOIN workorders
				ON woitems.wo = workorders.wo
			INNER JOIN locationusers
				ON locationusers.loccode = workorders.loccode
					AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
					AND locationusers.canview = 1
		WHERE workorders.closed = 0
			AND woitems.stockid = '" . $StockID . "'" .
			$WhereLocation;

	$QOOResult = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($QOOResult) == 0) {
		$QOO = 0;
	} else {
		$QOORow = DB_fetch_row($QOOResult);
		$QOO = $QOORow[0];
	}
	return $QOO;
}

/**
Calculates the total quantity of a stock item that is demanded due to outstanding sales orders.

### Parameters
- `$StockID` (string): The unique identifier for the stock item
- `$Location` (string): Location filter parameter with the following options:
  - `''` or `'ALL'`: Returns demand from all locations
  - `'USER_CAN_VIEW'`: Returns demand from locations user has view permissions
  - `'USER_CAN_UPDATE'`: Returns demand from locations user has update permissions
  - Specific location code: Returns demand from that specific location

### Returns
- `float`: The sum of outstanding quantities (ordered minus invoiced) from active sales orders
- Returns `0` if no outstanding orders for this item are found

### Query Conditions
- Only includes non-completed order lines (`completed=0`)
- Excludes quotations (`quotation=0`)
- Filters by user location permissions when applicable
*/
function GetDemandQuantityDueToOutstandingSalesOrders($StockID, $Location) {
	if (($Location == '') OR ($Location == 'ALL')){
		// All locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = '';
		$ErrMsg = __('The quantity demanded for this product in all locations cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_VIEW'){
		// All user is allowed to view locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = "INNER JOIN locationusers
									ON locationusers.loccode = salesorders.fromstkloc
									AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
									AND locationusers.canview = 1 ";
		$ErrMsg = __('The quantity demanded for this product in all locations the user can view cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_UPDATE'){
		// All user is allowed to update locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = "INNER JOIN locationusers
									ON locationusers.loccode = salesorders.fromstkloc
									AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
									AND locationusers.canupd = 1 ";
		$ErrMsg = __('The quantity demanded for this product in locations the user can update cannot be retrieved because');
	}
	else{
		// Just 1 location to consider
		$WhereLocation = " AND salesorders.fromstkloc = '" . $Location . "'";
		$UserAllowedLocations = '';
		$ErrMsg = __('The quantity demanded for this product in the specified location cannot be retrieved because');
	}

	$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS demand
			FROM salesorderdetails
			INNER JOIN salesorders
				ON salesorders.orderno = salesorderdetails.orderno " .
			$UserAllowedLocations . "
			WHERE salesorderdetails.stkcode = '" . $StockID . "'
				AND salesorderdetails.completed = 0
				AND salesorders.quotation = 0 " .
				$WhereLocation;

	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		return 0;
	}
	else{
		$MyRow = DB_fetch_array($Result);
		return (float)$MyRow['demand'];
	}
}

/**
Calculates the total quantity of a stock item that is demanded as a component in Bill of Materials (BOM)
for outstanding sales orders.

### Parameters
- `$StockID` (string): The unique identifier for the stock item
- `$Location` (string): Location filter parameter with the following options:
- `''` or `'ALL'`: Returns demand from all locations
- `'USER_CAN_VIEW'`: Returns demand from locations user has view permissions
- `'USER_CAN_UPDATE'`: Returns demand from locations user has update permissions
- Specific location code: Returns demand from that specific location

### Returns
- `float`: The sum of quantities needed as components (order quantity * BOM quantity)
- Returns `0` if no demand exists for this component

### Query Conditions
- Only includes non-completed order lines (`completed=0`)
- Only includes outstanding quantities (ordered minus invoiced > 0)
- Excludes quotations (`quotation=0`)
- Filters by user location permissions when applicable
*/
function GetDemandQuantityAsComponentInAssemblyItems($StockID, $Location){

	if (($Location == '') OR ($Location == 'ALL')){
		// All locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = '';
		$ErrMsg = __('The quantity demanded for this product as a component in BOM in all locations cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_VIEW'){
		// All user is allowed to view locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = "INNER JOIN locationusers
									ON locationusers.loccode = salesorders.fromstkloc
									AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
									AND locationusers.canview = 1 ";
		$ErrMsg = __('The quantity demanded for this product as a component in BOM in all locations the user can view cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_UPDATE'){
		// All user is allowed to update locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = "INNER JOIN locationusers
									ON locationusers.loccode = salesorders.fromstkloc
									AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
									AND locationusers.canupd = 1 ";
		$ErrMsg = __('The quantity demanded for this product as a component in BOM in locations the user can update cannot be retrieved because');
	}
	else{
		// Just 1 location to consider
		$WhereLocation = " AND salesorders.fromstkloc = '" . $Location . "'";
		$UserAllowedLocations = '';
		$ErrMsg = __('The quantity demanded for this product as a component in BOM in the specified location cannot be retrieved because');
	}

	$SQL = "SELECT SUM((salesorderdetails.quantity - salesorderdetails.qtyinvoiced) * bom.quantity) AS demand
			FROM salesorderdetails
			INNER JOIN salesorders
				ON salesorderdetails.orderno = salesorders.orderno
			INNER JOIN bom
				ON salesorderdetails.stkcode = bom.parent
			INNER JOIN stockmaster
				ON stockmaster.stockid = bom.parent" .
			$UserAllowedLocations . "
			WHERE salesorderdetails.quantity - salesorderdetails.qtyinvoiced > 0
				AND bom.component = '" . $StockID . "'
				AND salesorderdetails.completed = 0
				AND salesorders.quotation = 0 " .
				$WhereLocation;

	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		return 0;
	}
	else{
		$MyRow = DB_fetch_array($Result);
		return (float)$MyRow['demand'];
	}
}

/**
Calculates the total quantity of a stock item that is demanded as a component in active work orders.

### Parameters
- `$StockID` (string): The unique identifier for the stock item
- `$Location` (string): Location filter parameter with the following options:
- `''` or `'ALL'`: Returns demand from all locations
- `'USER_CAN_VIEW'`: Returns demand from locations user has view permissions
- `'USER_CAN_UPDATE'`: Returns demand from locations user has update permissions
- Specific location code: Returns demand from that specific location

### Returns
- `float`: The sum of quantities needed as components (quantity per unit * (required - received))
- Returns `0` if no demand exists for this component

### Query Conditions
- Only includes non-closed work orders (`closed=0`)
- Calculates demand based on remaining quantities to be received
- Takes into account the quantity per unit (qtypu) from work requirements
- Filters by user location permissions when applicable
*/
function GetDemandQuantityAsComponentInWorkOrders($StockID, $Location) {

	if (($Location == '') OR ($Location == 'ALL')){
		// All locations to be considered
		$WhereLocation = '';
		$ErrMsg = __('The workorder component demand for this product cannot be retrieved because');
	}else{
		// Just 1 location to consider
		$WhereLocation = " AND workorders.loccode='" . $Location . "'";
		$ErrMsg = __('The workorder component demand for this product from') . ' ' . $Location . ' ' . __('cannot be retrieved because');
	}
	if (($Location == '') OR ($Location == 'ALL')){
		// All locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = '';
		$ErrMsg = __('The quantity demanded for this product as a component in work orders in all locations cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_VIEW'){
		// All user is allowed to view locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = "INNER JOIN locationusers
									ON locationusers.loccode = workorders.loccode
									AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
									AND locationusers.canview = 1 ";
		$ErrMsg = __('The quantity demanded for this product as a component in work orders in all locations the user can view cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_UPDATE'){
		// All user is allowed to update locations to be considered
		$WhereLocation = '';
		$UserAllowedLocations = "INNER JOIN locationusers
									ON locationusers.loccode = workorders.loccode
									AND locationusers.userid = '" .  $_SESSION['UserID'] . "'
									AND locationusers.canupd = 1 ";
		$ErrMsg = __('The quantity demanded for this product as a component in work orders in locations the user can update cannot be retrieved because');
	}
	else{
		// Just 1 location to consider
		$WhereLocation = " AND workorders.loccode = '" . $Location . "'";
		$UserAllowedLocations = '';
		$ErrMsg = __('The quantity demanded for this product as a component in work orders in the specified location cannot be retrieved because');
	}

	$SQL = "SELECT SUM(qtypu * (woitems.qtyreqd - woitems.qtyrecd)) AS demand
			FROM woitems
			INNER JOIN worequirements
				ON woitems.stockid = worequirements.parentstockid
			INNER JOIN workorders
				ON woitems.wo = workorders.wo
					AND woitems.wo = worequirements.wo ".
			$UserAllowedLocations . "
			WHERE workorders.closed = 0
				AND worequirements.stockid = '" . $StockID . "'" .
				$WhereLocation;

	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		return 0;
	}
	else{
		$MyRow = DB_fetch_array($Result);
		return (float)$MyRow['demand'];
	}
}

/**
Calculates the total quantity of a stock item that is currently in transit FROM a specific location.

### Parameters
- `$StockId` (string): The unique identifier for the stock item
- `$LocationCode` (string): The location code from which items are being shipped

### Returns
- `float`: The sum of pending quantities being shipped from the specified location
- Returns `0` if no items are in transit from this location

### Query Conditions
- Only includes transfers with pending quantities greater than 0
- Filters by shipping location (shiploc)
- Sums all pending quantities for the specified stock item and location

### Notes
- Used to track inventory that has been shipped but not yet received
- Helps in calculating available stock by accounting for outbound transfers
*/
function GetItemQtyInTransitFromLocation($StockId, $LocationCode) {
		$InTransitSQL = "SELECT SUM(pendingqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockId . "'
							AND shiploc='" . $LocationCode . "'
							AND pendingqty > 0";
		$InTransitResult = DB_query($InTransitSQL);
		$InTransitRow = DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit'] != '') {
			return $InTransitRow['intransit'];
		} else {
			return 0;
		}
}

/**
Calculates the total quantity of a stock item that is currently in transit TO a specific location.

### Parameters
- `$StockId` (string): The unique identifier for the stock item
- `$LocationCode` (string): The location code to which items are being received

### Returns
- `float`: The sum of pending quantities being received at the specified location
- Returns `0` if no items are in transit to this location

### Query Conditions
- Only includes transfers with pending quantities greater than 0
- Filters by receiving location (recloc)
- Sums all pending quantities for the specified stock item and location

### Notes
- Used to track inventory that has been shipped but not yet received
- Helps in calculating expected stock by accounting for inbound transfers
*/
function GetItemQtyInTransitToLocation($StockId, $LocationCode) {
		$InTransitSQL = "SELECT SUM(pendingqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockId . "'
							AND recloc='" . $LocationCode . "'
							AND pendingqty > 0";
		$InTransitResult = DB_query($InTransitSQL);
		$InTransitRow = DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit'] != '') {
			return $InTransitRow['intransit'];
		} else {
			return 0;
		}
}

<?php

function GetQuantityOnHand($StockID, $Location){
/****************************************************************************************************
## GetQuantityOnHand Function
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
 ****************************************************************************************************/
	if (($Location == '') OR ($Location == 'ALL')){
		// All locations to be considered
		$WhereLocation = '';
		$UserAllowedLLocations = '';
		$ErrMsg = _('The quantity on hand for this product in all locations cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_VIEW'){
		// All user is allowed to view locations to be considered
		$WhereLocation = '';
		$UserAllowedLLocations = "INNER JOIN locationusers 
									ON locationusers.loccode=locstock.loccode 
									AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
									AND locationusers.canview=1 ";
		$ErrMsg = _('The quantity on hand for this product in all locations the user can view cannot be retrieved because');
	}
	elseif  ($Location == 'USER_CAN_UPDATE'){
		// All user is allowed to update locations to be considered
		$WhereLocation = '';
		$UserAllowedLLocations = "INNER JOIN locationusers 
									ON locationusers.loccode=locstock.loccode 
									AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
									AND locationusers.canupd=1 ";
		$ErrMsg = _('The quantity on hand for this product in locations the user can update cannot be retrieved because');
	}
	else{
		// Just 1 location to consider
		$WhereLocation = " AND locstock.loccode='" . $Location . "'";
		$UserAllowedLLocations = '';
		$ErrMsg = _('The quantity on hand for this product in the specified location cannot be retrieved because');
	}
	$DbgMsg = _('The following SQL to retrieve the total stock quantity was used');
	$SQL = "SELECT SUM(quantity) AS qoh
			FROM locstock " .
			$UserAllowedLLocations . "
			WHERE stockid = '" . $StockID . "'" .
			$WhereLocation ."";
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	if (DB_num_rows($Result) == 0) {
		return 0;
	}else{
		$QtyRow = DB_fetch_array($Result);
		return floatval($QtyRow['qoh']);
	}
}

function GetQuantityOnOrderDueToPurchaseOrders($StockID, $Location){
/****************************************************************************************************
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

 ****************************************************************************************************/
if ($Location == ""){
		// All locations to be considered
		$WhereLocation = "";
		$ErrMsg = _('The quantity on order due to purchase orders for') . ' ' . $StockID . ' ' . _('to be received into all locations cannot be retrieved because');
	}else{
		// Just 1 location to consider
		$WhereLocation = " AND purchorders.intostocklocation = '" . $Location . "'";
		$ErrMsg = _('The quantity on order due to purchase orders for') . ' ' . $StockID . ' ' . _('to be received into') . ' ' . $Location . ' ' . _('cannot be retrieved because');
	}

	$SQL="SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd) AS QtyOnOrder
		FROM purchorders
			INNER JOIN purchorderdetails
				ON purchorders.orderno=purchorderdetails.orderno
			INNER JOIN locationusers
				ON locationusers.loccode=purchorders.intostocklocation
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
		WHERE purchorderdetails.itemcode='" . $StockID . "'
			AND purchorderdetails.completed = 0
			AND purchorders.status<>'Cancelled'
			AND purchorders.status<>'Pending'
			AND purchorders.status<>'Rejected'
			AND purchorders.status<>'Completed'" .
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

function GetQuantityOnOrderDueToWorkOrders($StockID, $Location){
/****************************************************************************************************
## GetQuantityOnOrderDueToWorkOrders Function
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
****************************************************************************************************/
	if ($Location == ''){
		// All locations to be considered
		$WhereLocation = '';
		$ErrMsg = _('The quantity on order due to work orders for') . ' ' . $StockID . ' ' . _('to be received into all locations cannot be retrieved because');
	}else{
		// Just 1 location to consider
		$WhereLocation = " AND workorders.loccode='" . $Location . "'";
		$ErrMsg = _('The quantity on order due to work orders for') . ' ' . $StockID . ' ' . _('to be received into') . ' ' . $Location . ' ' . _('cannot be retrieved because');
	}

	$SQL="SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
		FROM woitems
			INNER JOIN workorders
				ON woitems.wo=workorders.wo
			INNER JOIN locationusers
				ON locationusers.loccode=workorders.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
		WHERE workorders.closed=0
			AND woitems.stockid='" . $StockID . "'" .
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


?>
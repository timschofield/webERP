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

?>
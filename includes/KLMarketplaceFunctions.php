<?php

/**************************************************************************************************
			GENERAL MARKETPLACE (SHOPEE, TOKOPEDIA) FUNCTIONS
**************************************************************************************************/

/**************************************************************************************************
Function List (Alphabetical):
- CalculateCommissionLazada: Calculates commission for Lazada marketplace
- CalculateCommissionShopee: Calculates commission for Shopee marketplace
- CalculateCommissionTokopedia: Calculates commission for Tokopedia marketplace
- ClearUrl: Cleans URL by replacing forward slashes
- CreateTextSize: Creates text size description for products
- FindLazadaCategory: Finds appropriate Lazada category for a product
- FindLazadaColor: Determines color attribute for Lazada products
- FindLazadaMaterial: Determines material attribute for Lazada products
- FindLazadaStone: Determines stone attribute for Lazada products
- FindShopeeCategory: Finds appropriate Shopee category for a product
- GetShopeeProductId: Retrieves Shopee product ID for a stock item
- GetTokopediaProductId: Retrieves Tokopedia product ID for a stock item
- ItemEnableLazadaInfo: Enables/disables Lazada marketplace info for an item
- ItemEnableShopeeInfo: Enables/disables Shopee marketplace info for an item
- ItemEnableTokopediaInfo: Enables/disables Tokopedia marketplace info for an item
- ItemInsertLazadaInfo: Inserts Lazada marketplace info for an item
- ItemInsertShopeeInfo: Inserts Shopee marketplace info for an item
- ItemInsertTokopediaInfo: Inserts Tokopedia marketplace info for an item
- ItemMarketplaceName: Creates marketplace name for an item
- ItemMarketplaceQOH: Calculates quantity on hand for marketplace listings
- ItemUpdateLazadaInfo: Updates Lazada marketplace info for an item
- ItemUpdateShopeeInfo: Updates Shopee marketplace info for an item
- ItemUpdateTokopediaInfo: Updates Tokopedia marketplace info for an item
- SQLInsertNewItemKLStockmarketplaces: Creates SQL to insert new item in marketplace stock table
- WhatsInTheBox: Determines what's included in product packaging
**************************************************************************************************/

/**************************************************************************************************************
* Calculates commission for Tokopedia marketplace
*
* @param string $CustomerCode Customer code, must be "TOKOPEDIA"
* @param string $OrderNo Order number
* @param float $TotalAmount Total order amount
* @param float $CommissionTokopediaPercent Commission percentage for Tokopedia
* @param float $CommissionTokopediaFreeShippingPerItem Free shipping commission percentage per item
* @param float $CommissionTokopediaFreeShippingMaximum Maximum free shipping commission
* @return float Calculated commission amount
**************************************************************************************************************/
function CalculateCommissionTokopedia($CustomerCode, 
									$OrderNo, 
									$TotalAmount,
									$CommissionTokopediaPercent,
									$CommissionTokopediaFreeShippingPerItem,
									$CommissionTokopediaFreeShippingMaximum) {
	if ($CustomerCode != "TOKOPEDIA") {
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = tokopedia", "error");
		include('includes/footer.php');
		exit;
	}
	// X% from all order for Tokopedia
	$CommissionTPGlobal = round($TotalAmount * $CommissionTokopediaPercent / 100, 0); // this commission still includes PPN

	// we need to pay comething to Tokopedia if shipper is SI-CEPAT, as it means free shipping for the customer, so we pay something
	$SQL = "SELECT salesorders.shipvia
		FROM salesorders 
		WHERE salesorders.orderno = '" . $OrderNo . "' ";			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0) {
		$MyRow = DB_fetch_array($Result);
		$Shipper = $MyRow['shipvia'];
		$CommissionTPFreeShipping = 0;
		if ($Shipper == '12') {
			// if shipper is 12 = GRATIS ONGKIR TOKOPEDIA... then we shipped it via free shipping, we must pay 
			// 2,5% from every item with a max 0f 10.000 for Tokopedia as cost of shipment
			$SQL = "SELECT salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.discountpercent
				FROM salesorderdetails
				WHERE salesorderdetails.orderno = '" . $OrderNo . "' ";			
			$Result = DB_query($SQL);
			while ($MyRow = DB_fetch_array($Result)) {
				$ItemPrice = $MyRow['unitprice'] * (1 - $MyRow['discountpercent']);
				$CommissionItem = min(round($ItemPrice * $CommissionTokopediaFreeShippingPerItem / 100, 0), 
					$CommissionTokopediaFreeShippingMaximum); 
				$CommissionTPFreeShipping += $CommissionItem * $MyRow['qtyinvoiced']; // this commission still has PPN
			}
		}
	} else {
		prnMsg("ERROR: Could not extract shipper information for order = " . $OrderNo, "error");
		include('includes/footer.php');
		exit;
	}
	
	$Commission = $CommissionTPGlobal + $CommissionTPFreeShipping; // this commission still has PPN
	// Fix potential divide by zero error by checking denominator is not zero
	if (PPN_PERCENT != -100) {
		$Commission = round($Commission / ((100 + PPN_PERCENT) / 100), 0); // this commision already net
	} else {
		$Commission = 0; // Avoid division by zero
	}
	return $Commission;
}

/**************************************************************************************************************
* Calculates commission for Shopee marketplace
*
* @param string $CustomerCode Customer code, must be "SHOPEE"
* @param string $OrderNo Order number
* @param float $TotalAmount Total order amount
* @param float $CommissionShopeePercent Commission percentage for Shopee
* @param float $CommissionShopeeFreeShippingPerItem Free shipping commission percentage per item
* @param float $CommissionShopeeFreeShippingMaximum Maximum free shipping commission
* @return float Calculated commission amount
**************************************************************************************************************/
function CalculateCommissionShopee($CustomerCode, 
									$OrderNo, 
									$TotalAmount, 
									$CommissionShopeePercent,
									$CommissionShopeeFreeShippingPerItem,
									$CommissionShopeeFreeShippingMaximum) {
	if ($CustomerCode != "SHOPEE") {
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = shopee", "error");
		include('includes/footer.php');
		exit;
	}
	// X% from all order for Shopee
	$CommissionTPGlobal = round($TotalAmount * $CommissionShopeePercent / 100, 0); // this commission still includes PPN

	// we need to pay comething to Shopee if shipper is SI-CEPAT, as it means free shipping for the customer, so we pay something
	$SQL = "SELECT salesorders.shipvia
		FROM salesorders 
		WHERE salesorders.orderno = '" . $OrderNo . "' ";			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0) {
		$MyRow = DB_fetch_array($Result);
		$Shipper = $MyRow['shipvia'];
		$CommissionTPFreeShipping = 0;
		if ($Shipper == '12') {
			// if shipper is 12 = GRATIS ONGKIR SHOPEE... then we shipped it via free shipping, we must pay 
			// 2,5% from every item with a max 0f 10.000 for Shopee as cost of shipment
			$SQL = "SELECT salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.discountpercent
				FROM salesorderdetails
				WHERE salesorderdetails.orderno = '" . $OrderNo . "' ";			
			$Result = DB_query($SQL);
			while ($MyRow = DB_fetch_array($Result)) {
				$ItemPrice = $MyRow['unitprice'] * (1 - $MyRow['discountpercent']);
				$CommissionItem = min(round($ItemPrice * $CommissionShopeeFreeShippingPerItem / 100, 0), 
					$CommissionShopeeFreeShippingMaximum); 
				$CommissionTPFreeShipping += $CommissionItem * $MyRow['qtyinvoiced']; // this commission still has PPN
			}
		}
	} else {
		prnMsg("ERROR: Could not extract shipper information for order = " . $OrderNo, "error");
		include('includes/footer.php');
		exit;
	}
	
	$Commission = $CommissionTPGlobal + $CommissionTPFreeShipping; // this commission still has PPN
	// Fix potential divide by zero error by checking denominator is not zero
	if (PPN_PERCENT != -100) {
		$Commission = round($Commission / ((100 + PPN_PERCENT) / 100), 0); // this commision already net
	} else {
		$Commission = 0; // Avoid division by zero
	}
	return $Commission;
}

/**************************************************************************************************************
* Calculates commission for Lazada marketplace
*
* @param string $CustomerCode Customer code, must be "LAZADA"
* @param string $OrderNo Order number
* @param float $TotalAmount Total order amount
* @param float $CommissionLazadaPercent Commission percentage for Lazada
* @return float Calculated commission amount
**************************************************************************************************************/
function CalculateCommissionLazada($CustomerCode, $OrderNo, $TotalAmount, $CommissionLazadaPercent) {
	if ($CustomerCode != "LAZADA") {
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = lazada", "error");
		include('includes/footer.php');
		exit;
	}
	// 1,80 from all order for lazada
	$Commission = round($TotalAmount * $CommissionLazadaPercent / 100, 0); // this commission still includes PPN
	// Fix potential divide by zero error by checking denominator is not zero
	if (PPN_PERCENT != -100) {
		$Commission = round($Commission / ((100 + PPN_PERCENT) / 100), 0); // this commision already net
	} else {
		$Commission = 0; // Avoid division by zero
	}
	return $Commission;
}

/**************************************************************************************************************
* Cleans URL by replacing forward slashes
*
* @param string $Url URL to clean
* @return string Cleaned URL
**************************************************************************************************************/
function ClearUrl($Url) {
	if ($Url === null) {
		return '';
	}
	$Clean = str_replace("/", "\\/", $Url);
	return $Clean;
}

/**************************************************************************************************************
* Creates text size description for products
*
* @param string $StockID Stock ID
* @param string $language Language code (ID or other)
* @param bool $IncludeTextDescription Whether to include text description
* @return string Size text description
**************************************************************************************************************/
function CreateTextSize($StockID, $language, $IncludeTextDescription) {
	$Size = ClassicalSize($StockID);
	if ($Size == "NO SIZE") {
		if (isRing($StockID)) {
			$Size = RingSize($StockID);
		} else {
			$Size = NumberSize($StockID);
		}		
	} 
	if ($IncludeTextDescription) {
		if ($Size == "NO SIZE") {
			$TextSize = "";
		} else if ($Size == "FR") {
			$TextSize = "Free Size";
		} else {
			if ($language == "ID") {
				$TextSize = "Ukuran: " . $Size;
			} else {
				$TextSize = "Size: " . $Size;
			}
		}
	} else {
		if (($Size == "NO SIZE") OR ($Size == "FR")) {
			$TextSize = "";
		} else {
			$TextSize = " - Size " . $Size;
		}
	}
	return $TextSize;
}

/**************************************************************************************************************
* Creates marketplace name for an item
*
* @param string $StockID Stock ID
* @param string $Description Item description
* @param string $Translation Item translation
* @return string Formatted marketplace name
**************************************************************************************************************/
function ItemMarketplaceName($StockID, $Description, $Translation) {
	$Name = trim($Translation) . 
			" -"  . 
			trim($Description) . 
			CreateTextSize($StockID, "EN", false);
	return $Name;
}

/**************************************************************************************************************
* Calculates quantity on hand for marketplace listings
*
* @param string $StockID Stock ID
* @return int Adjusted quantity on hand for marketplace display
**************************************************************************************************************/
function ItemMarketplaceQOH($StockID) {
	// if we have more than MAXIMUM_QOH_TO_SHOW_IN_MARKETPLACES we "cap" it, 
	// so we don't spend update credits updating QOH when it is not important for us
	$QOH = min(ItemOnlineQOH($StockID), MAXIMUM_QOH_TO_SHOW_IN_MARKETPLACES);
	
	//if less than MINIMUM_QOH_TO_SHOW_ITEM_IN_MARKETPLACES then consider we do not have available for marketplaces
	// to avoid problems of orders not fulfilled and low rankings, better show QOH = 0 than cancel the order.
	// Anyway, this can be revised, depending on internal marketplace order management 
	if ($QOH < MINIMUM_QOH_TO_SHOW_ITEM_IN_MARKETPLACES) {
		$QOH = 0;
	}
	return $QOH;
}

/**************************************************************************************************************
* Creates SQL to insert new item in marketplace stock table
*
* @param string $StockID Stock ID
* @return string SQL query for insertion
**************************************************************************************************************/
function SQLInsertNewItemKLStockmarketplaces($StockID) {
	$SQL = "INSERT INTO klstockmarketplaces 
				(stockid,
				tokopediaenabled,
				shopeeenabled,
				lazadaenabled)
		VALUES (
			'" . $StockID . "',
			'0',
			'0',
			'0')";
	return $SQL;
}

/**************************************************************************************************************
* Enables/disables Lazada marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledLazada Enabled flag (0/1)
* @return void
**************************************************************************************************************/
function ItemEnableLazadaInfo($StockID, $EnabledLazada) {
	if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)) {
		// Already exists, should exist!!! so only update the enable flag
		$SQL = "UPDATE klstockmarketplaces
				SET lazadaenabled='" . $EnabledLazada ."'
			WHERE klstockmarketplaces.stockid='" . $StockID . "'
				AND lazadaurl IS NOT NULL";
	} else {
		// does not exist, so need to insert a new row for the item as DISABLED, as it means we do not have the URL's yet
		$SQL = SQLInsertNewItemKLStockmarketplaces($StockID);
	}
	$DbgMsg = _('The SQL that failed to enable/disable the Lazada marketplace info was');
	$ErrMsg = _('Cannot enable/disable the Lazada marketplace info because');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Enables/disables Shopee marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledShopee Enabled flag (0/1)
* @return void
**************************************************************************************************************/
function ItemEnableShopeeInfo($StockID, $EnabledShopee) {
	if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)) {
		// Already exists, should exist!!! so only update the enable flag
		$SQL = "UPDATE klstockmarketplaces
				SET shopeeenabled='" . $EnabledShopee ."'
			WHERE klstockmarketplaces.stockid='" . $StockID . "'
				AND shopeeurl IS NOT NULL";
	} else {
		// does not exist, so need to insert a new row for the item as DISABLED, as it means we do not have the URL's yet
		$SQL = SQLInsertNewItemKLStockmarketplaces($StockID);
	}
	$DbgMsg = _('The SQL that failed to enable/disable the Shopee marketplace info was');
	$ErrMsg = _('Cannot enable/disable the Shopee marketplace info because');
	DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Enables/disables Tokopedia marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledTokopedia Enabled flag (0/1)
* @return void
**************************************************************************************************************/
function ItemEnableTokopediaInfo($StockID, $EnabledTokopedia) {
	if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)) {
		// Already exists, so only update the enable flag
		$SQL = "UPDATE klstockmarketplaces
				SET tokopediaenabled='" . $EnabledTokopedia ."'
			WHERE klstockmarketplaces.stockid='" . $StockID . "'
				AND tokopediaurl IS NOT NULL";
	} else {
		// does not exist, so need to insert a new row for the item as DISABLED, as it means we do not have the URL's yet
		$SQL = SQLInsertNewItemKLStockmarketplaces($StockID);
	}
	$DbgMsg = _('The SQL that failed to enable/disable the Tokopedia marketplace info was');
	$ErrMsg = _('Cannot enable/disable the Tokopedia marketplace info because');
	DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Inserts Lazada marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledLazada Enabled flag (0/1)
* @param string $LazadaProductId Lazada product ID
* @param string $URLLazada Lazada product URL
* @return void
**************************************************************************************************************/
function ItemInsertLazadaInfo($StockID, $EnabledLazada, $LazadaProductId, $URLLazada) {
	$SQL = "INSERT INTO klstockmarketplaces 
				(stockid,
				lazadaurl,
				lazadaproductid,
				lazadaenabled)
		VALUES (
			'" . $StockID . "',
			'" . $URLLazada . "',
			'" . $LazadaProductId . "',
			'" . $EnabledLazada . "')";

	$DbgMsg = _('The SQL that failed to insert the Lazada marketplace info was');
	$ErrMsg = _('Cannot insert the Lazada marketplace info because');
	DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Inserts Shopee marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledShopee Enabled flag (0/1)
* @param string $ShopeeProductId Shopee product ID
* @param string $URLShopee Shopee product URL
* @return void
**************************************************************************************************************/
function ItemInsertShopeeInfo($StockID, $EnabledShopee, $ShopeeProductId, $URLShopee) {
	$SQL = "INSERT INTO klstockmarketplaces 
				(stockid,
				shopeeurl,
				shopeeproductid,
				shopeeenabled)
		VALUES (
			'" . $StockID . "',
			'" . $URLShopee . "',
			'" . $ShopeeProductId . "',
			'" . $EnabledShopee . "')";

	$DbgMsg = _('The SQL that failed to insert the Shopee marketplace info was');
	$ErrMsg = _('Cannot insert the Shopee marketplace info because');
	DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Inserts Tokopedia marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledTokopedia Enabled flag (0/1)
* @param string $TokopediaProductId Tokopedia product ID
* @param string $URLTokopedia Tokopedia product URL
* @return void
**************************************************************************************************************/
function ItemInsertTokopediaInfo($StockID, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia) {
	$SQL = "INSERT INTO klstockmarketplaces 
				(stockid,
				tokopediaurl,
				tokopediaproductid,
				tokopediaenabled)
		VALUES (
			'" . $StockID . "',
			'" . $URLTokopedia . "',
			'" . $TokopediaProductId . "',
			'" . $EnabledTokopedia . "')";

	$DbgMsg = _('The SQL that failed to insert the Tokopedia marketplace info was');
	$ErrMsg = _('Cannot insert the Tokopedia marketplace info because');
	DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Updates Lazada marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledLazada Enabled flag (0/1)
* @param string $LazadaProductId Lazada product ID
* @param string $URLLazada Lazada product URL
* @return void
**************************************************************************************************************/
function ItemUpdateLazadaInfo($StockID, $EnabledLazada, $LazadaProductId, $URLLazada) {
	$SQL = "UPDATE klstockmarketplaces
			SET lazadaurl = '" . $URLLazada . "',
				lazadaproductid = '" . $LazadaProductId ."',
				lazadaenabled='" . $EnabledLazada ."'
		WHERE klstockmarketplaces.stockid='" . $StockID . "'";

	$DbgMsg = _('The SQL that failed to update the Lazada marketplace info was');
	$ErrMsg = _('Cannot update the Lazada marketplace info because');
	DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Updates Shopee marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledShopee Enabled flag (0/1)
* @param string $ShopeeProductId Shopee product ID
* @param string $URLShopee Shopee product URL
* @return void
**************************************************************************************************************/
function ItemUpdateShopeeInfo($StockID, $EnabledShopee, $ShopeeProductId, $URLShopee) {
	$SQL = "UPDATE klstockmarketplaces
			SET shopeeurl = '" . $URLShopee . "',
				shopeeproductid = '" . $ShopeeProductId ."',
				shopeeenabled='" . $EnabledShopee ."'
		WHERE klstockmarketplaces.stockid='" . $StockID . "'";

	$DbgMsg = _('The SQL that failed to update the Shopee marketplace info was');
	$ErrMsg = _('Cannot update the Shopee marketplace info because');
	DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Updates Tokopedia marketplace info for an item
*
* @param string $StockID Stock ID
* @param string $EnabledTokopedia Enabled flag (0/1)
* @param string $TokopediaProductId Tokopedia product ID
* @param string $URLTokopedia Tokopedia product URL
* @return void
**************************************************************************************************************/
function ItemUpdateTokopediaInfo($StockID, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia) {
	$SQL = "UPDATE klstockmarketplaces
			SET tokopediaurl = '" . $URLTokopedia . "',
				tokopediaproductid = '" . $TokopediaProductId ."',
				tokopediaenabled='" . $EnabledTokopedia ."'
		WHERE klstockmarketplaces.stockid='" . $StockID . "'";

	$DbgMsg = _('The SQL that failed to update the Tokopedia marketplace info was');
	$ErrMsg = _('Cannot update the Tokopedia marketplace info because');
	DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

/**************************************************************************************************************
* Retrieves Tokopedia product ID for a stock item
*
* @param string $StockID Stock ID
* @return string Tokopedia product ID or empty string if not found
**************************************************************************************************************/
function GetTokopediaProductId($StockID) {
	$SQL = "SELECT tokopediaproductid
			FROM klstockmarketplaces
			WHERE stockid = '" . $StockID . "'";

	$DbgMsg = _('The SQL that failed to get the Tokopedia Product ID was');
	$ErrMsg = _('Cannot get the Tokopedia Product ID because');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

/**************************************************************************************************************
* Retrieves Shopee product ID for a stock item
*
* @param string $StockID Stock ID
* @return string Shopee product ID or empty string if not found
**************************************************************************************************************/
function GetShopeeProductId($StockID) {
	$SQL = "SELECT shopeeproductid
			FROM klstockmarketplaces
			WHERE stockid = '" . $StockID . "'";

	$DbgMsg = _('The SQL that failed to get the Shopee Product ID was');
	$ErrMsg = _('Cannot get the Shopee Product ID because');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

/**************************************************************************************************************
* Finds appropriate Shopee category for a product
*
* @param string $StockID Stock ID
* @param string $Name Product name
* @param string $Description Product description
* @return string Shopee category ID
**************************************************************************************************************/
function FindShopeeCategory($StockID, $Name, $Description) {
	$ShopeeCat = "";
	if (isRing($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_RING;
	} elseif (isToeRing($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_TOE_RING;
	} elseif (isBrooche($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_BROOCHE;
	} elseif (isPiercing($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_PIERCING;
	} elseif (isEarring($StockID)) {
		if (ItemInList("stud", $Description)) {
			$ShopeeCat = SHOPEE_CATEGORY_EARRING_STUD;
		} else if (ItemInList("hoop", $Description)) {
			$ShopeeCat = SHOPEE_CATEGORY_EARRING_HOOP;
		} else if (ItemInList("hook", $Description)) {
			$ShopeeCat = SHOPEE_CATEGORY_EARRING_HOOK;
		} else {
			$ShopeeCat = SHOPEE_CATEGORY_EARRING;
		}
	} elseif (isEarcuff($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_EARRING_STUD;
	} elseif (isBracelet($StockID)) {
		if (ItemInList("bangle", $Description)) {
			$ShopeeCat = SHOPEE_CATEGORY_BANGLE;
		} else if (ItemInList("pearl", $Description)) {
			$ShopeeCat = SHOPEE_CATEGORY_BRACELET_PEARL;
		} else {
			$ShopeeCat = SHOPEE_CATEGORY_BRACELET;
		}
	} elseif (isAnklet($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_ANKLET;
	} elseif (isPendant($StockID)) {
		if (ItemInList("pearl", $Description)) {
			$ShopeeCat = SHOPEE_CATEGORY_PENDANT_PEARL;
		} else {
			$ShopeeCat = SHOPEE_CATEGORY_PENDANT;
		}
	} elseif (isNecklace($StockID)) {
		if (ItemInList("choker", $Description)) {
			$ShopeeCat = SHOPEE_CATEGORY_CHOKER;
		} else if (ItemInList("pearl", $Description)) {
			$ShopeeCat = SHOPEE_CATEGORY_NECKLACE_PEARL;
		} else {
			$ShopeeCat = SHOPEE_CATEGORY_NECKLACE;
		}
	} elseif (isTali($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_NECKLACE;
	} elseif (isBag($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_BAG;
	} elseif (isKeyRing($StockID)) {
		$ShopeeCat = SHOPEE_CATEGORY_KEYHOLDER;
	}
	return $ShopeeCat;
}

/**************************************************************************************************************
* Determines material attribute for Lazada products
*
* @param int $TypeOfShop Shop type (1 for KL, other for Blink)
* @param string $Text Product text description
* @return string Material attribute value
**************************************************************************************************************/
function FindLazadaMaterial($TypeOfShop, $Text) {
	$Material = "";
	if ($TypeOfShop == 1) {
		// if it is KL, then default material is Silver
		$Material = "Silver - Perak";	
	} else {
		// if it is Blink, then default material is Metal
		$Material = "Metal - Logam";	
	}

	if (ItemInList("silver", $Text)) {
		$Material = "Silver - Perak";	
	} elseif (ItemInList("wood", $Text)) {
		$Material = "Wood - Kayu";	
	} elseif (ItemInList("leather", $Text)) {
		$Material = "Leather - Kulit";	
	} elseif (ItemInList("Resin", $Text)) {
		$Material = "Resin";	
	} elseif (ItemInList("Shell", $Text)) {
		$Material = "Shell";	
	}
	return $Material;
}

/**************************************************************************************************************
* Determines stone attribute for Lazada products
*
* @param string $Text Product text description
* @return string Stone attribute value
**************************************************************************************************************/
function FindLazadaStone($Text) {
	$Stone = "";
	if (ItemInList("cat eye", $Text)) {
		$Stone = "Cat Eye";	
	} elseif (ItemInList("freshwater", $Text)) {
		$Stone = "Freshwater Pearl - Mutiara Air Tawar";	
	} elseif (ItemInList("pearl", $Text)) {
		$Stone = "Pearl - Mutiara";	
	} elseif (ItemInList("hematite", $Text)) {
		$Stone = "Hematite - Manik-manik";	
	} elseif (ItemInList("mother of pearl", $Text)) {
		$Stone = "Mother of Pearl";	
	} elseif (ItemInList("opal", $Text)) {
		$Stone = "Opal";	
	} elseif (ItemInList("turquoise", $Text)) {
		$Stone = "Turquoise";	
	} elseif (ItemInList("Zircon", $Text)) {
		$Stone = "Zircon";	
	} elseif (ItemInList("crystal", $Text)) {
		$Stone = "Crystal -  Kristal";	
	}
	return $Stone;
}

/**************************************************************************************************************
* Determines what's included in product packaging
*
* @param string $StockID Stock ID
* @return string Description of what's included in the box
**************************************************************************************************************/
function WhatsInTheBox($StockID) {
	$Box = "";
	if (isRing($StockID)) {
		$Box = "Ring";
	} elseif (isToeRing($StockID)) {
		$Box = "Toe Ring";
	} elseif (isBrooche($StockID)) {
		$Box = "Brooche";
	} elseif (isEarring($StockID)) {
		$Box = "2 Earrings";
	} elseif (isEarcuff($StockID)) {
		$Box = "2 Earcuffs";
	} elseif (isPiercing($StockID)) {
		$Box = "Piercing";
	} elseif (isBracelet($StockID)) {
		$Box = "Bracelet";
	} elseif (isAnklet($StockID)) {
		$Box = "Anklet";
	} elseif (isPendant($StockID)) {
		$Box = "Pendant";
	} elseif (isNecklace($StockID)) {
		$Box = "Necklace";
	} elseif (isTali($StockID)) {
		$Box = "Tali Cord";
	} elseif (isBag($StockID)) {
		$Box = "Bag";
	} elseif (isKeyRing($StockID)) {
		$Box = "Key Holder";
	}
	return $Box . ", Pouchbag, Jewellery Box";
}

/**************************************************************************************************************
* Determines color attribute for Lazada products
*
* @param string $Text Product text description
* @return string Color attribute value
**************************************************************************************************************/
function FindLazadaColor($Text) {
	$Color = "";
	if (ItemInList("white", $Text)) {
		$Color = "White - Putih";	
	} elseif (ItemInList("yellow", $Text)) {
		$Color = "Yellow - Kuning";	
	} elseif (ItemInList("silver", $Text)) {
		$Color = "Silver - Perak";	
	} elseif (ItemInList("blue", $Text)) {
		$Color = "Blue - Biru";	
	} elseif (ItemInList("red", $Text)) {
		$Color = "Red - Merah";	
	} elseif (ItemInList("pink", $Text)) {
		$Color = "Pink";	
	} elseif (ItemInList("Green", $Text)) {
		$Color = "Green - Hijau";	
	} elseif (ItemInList("Grey", $Text)) {
		$Color = "Grey - Abu abu";	
	}
	return $Color;
}


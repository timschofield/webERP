<?php

/**************************************************************************************************
			GENERAL MARKETPLACE (SHOPEE, TOKOPEDIA) FUNCTIONS
**************************************************************************************************/

function CalculateCommissionTokopedia($CustomerCode, 
									$OrderNo, 
									$TotalAmount,
									$CommissionTokopediaPercent,
									$CommissionTokopediaFreeShippingPerItem,
									$CommissionTokopediaFreeShippingMaximum){
	if ($CustomerCode != "TOKOPEDIA"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = tokopedia", "error");
		include('includes/footer.php');
		exit;
	}
	// X% from all order for Tokopedia
	$CommissionTPGlobal = round($TotalAmount * $CommissionTokopediaPercent /100 ,0); // this commission still includes PPN

	// we need to pay comething to Tokopedia if shipper is SI-CEPAT, as it means free shipping for the customer, so we pay something
	$SQL = "SELECT salesorders.shipvia
		FROM salesorders 
		WHERE salesorders.orderno = '" . $OrderNo . "' ";			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Shipper = $MyRow['shipvia'];
		$CommissionTPFreeShipping = 0;
		if ($Shipper == '12'){
			// if shipper is 12 = GRATIS ONGKIR TOKOPEDIA... then we shipped it via free shipping, we must pay 
			// 2,5% from every item with a max 0f 10.000 for Tokopedia as cost of shipment
			$SQL = "SELECT salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.discountpercent
				FROM salesorderdetails
				WHERE salesorderdetails.orderno = '" . $OrderNo . "' ";			
			$Result = DB_query($SQL);
			while ($MyRow = DB_fetch_array($Result)) {
				$ItemPrice = $MyRow['unitprice']*(1-$MyRow['discountpercent']);
				$CommissionItem = min(round($ItemPrice * $CommissionTokopediaFreeShippingPerItem /100 ,0), $CommissionTokopediaFreeShippingMaximum); 
				$CommissionTPFreeShipping += $CommissionItem * $MyRow['qtyinvoiced']; // this commission still has PPN
			}
		}
	}else{
		prnMsg("ERROR: Could not extract shipper information for order = " . $OrderNo, "error");
		include('includes/footer.php');
		exit;
	}
	
	$Commission = $CommissionTPGlobal + $CommissionTPFreeShipping; // this commission still has PPN
	$Commission = round($Commission /((100 + PPN_PERCENT)/100) ,0); // this commision already net
	return $Commission;
}

function CalculateCommissionShopee($CustomerCode, 
									$OrderNo, 
									$TotalAmount, 
									$CommissionShopeePercent,
									$CommissionTokopediaFreeShippingPerItem,
									$CommissionTokopediaFreeShippingMaximum){
	if ($CustomerCode != "SHOPEE"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = shopee", "error");
		include('includes/footer.php');
		exit;
	}
	// X% from all order for Tokopedia
	$CommissionTPGlobal = round($TotalAmount * $CommissionShopeePercent /100 ,0); // this commission still includes PPN

	// we need to pay comething to Shopee if shipper is SI-CEPAT, as it means free shipping for the customer, so we pay something
	$SQL = "SELECT salesorders.shipvia
		FROM salesorders 
		WHERE salesorders.orderno = '" . $OrderNo . "' ";			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Shipper = $MyRow['shipvia'];
		$CommissionTPFreeShipping = 0;
		if ($Shipper == '12'){
			// if shipper is 12 = GRATIS ONGKIR SHOPEE... then we shipped it via free shipping, we must pay 
			// 2,5% from every item with a max 0f 10.000 for Shopee as cost of shipment
			$SQL = "SELECT salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.discountpercent
				FROM salesorderdetails
				WHERE salesorderdetails.orderno = '" . $OrderNo . "' ";			
			$Result = DB_query($SQL);
			while ($MyRow = DB_fetch_array($Result)) {
				$ItemPrice = $MyRow['unitprice']*(1-$MyRow['discountpercent']);
				$CommissionItem = min(round($ItemPrice * $CommissionShopeeFreeShippingPerItem /100 ,0), $CommissionShopeeFreeShippingMaximum); 
				$CommissionTPFreeShipping += $CommissionItem * $MyRow['qtyinvoiced']; // this commission still has PPN
			}
		}
	}else{
		prnMsg("ERROR: Could not extract shipper information for order = " . $OrderNo, "error");
		include('includes/footer.php');
		exit;
	}
	
	$Commission = $CommissionTPGlobal + $CommissionTPFreeShipping; // this commission still has PPN
	$Commission = round($Commission /((100 + PPN_PERCENT)/100) ,0); // this commision already net
	return $Commission;
}

function CalculateCommissionLazada($CustomerCode, $OrderNo, $TotalAmount, $CommissionLazadaPercent){
	if ($CustomerCode != "LAZADA"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = lazada", "error");
		include('includes/footer.php');
		exit;
	}
	// 1,80 from all order for lazada
	$Commission = round($TotalAmount * $CommissionLazadaPercent /100 ,0); // this commission still includes PPN
	$Commission = round($Commission /((100 + PPN_PERCENT)/100) ,0); // this commision already net
	return $Commission;
}

function ClearUrl($Url){
	$Clean = str_replace("/", "\\/", $Url);
	return $Clean;
}

function CreateTextSize($StockID, $language, $IncludeTextDescription){
	$Size = ClassicalSize($StockID);
	if ($Size == "NO SIZE"){
		if (isRing($StockID)){
			$Size = RingSize($StockID);
		}else{
			$Size = NumberSize($StockID);
		}		
	} 
	if ($IncludeTextDescription){
		if ($Size == "NO SIZE"){
			$TextSize = "";
		}else if ($Size == "FR"){
			$TextSize = "Free Size";
		}else{
			if ($language == "ID"){
				$TextSize = "Ukuran: ". $Size;
			}else{
				$TextSize = "Size: ". $Size;
			}
		}
	}else{
		if (($Size == "NO SIZE") OR ($Size == "FR")){
			$TextSize = "";
		}else{
			$TextSize = " - Size " . $Size;
		}
	}
	return $TextSize;
}

function ItemMarketplaceName($StockID, $Description, $Translation){
	$Name = trim($Translation) . 
			" -"  . 
			trim($Description) . 
			CreateTextSize($StockID, "EN", false);
	return $Name;
}

function ItemMarketplaceQOH($StockID){
	// if we have more than ACI_MAXIMUM_QOH_TO_SHOW_IN_MARKETPLACES we "cap" it, 
	// so we don't spend update credits updating QOH when it is not important for us
	$QOH = 	min(ItemOnlineQOH($StockID), ACI_MAXIMUM_QOH_TO_SHOW_IN_MARKETPLACES);
	
	//if less than ACI_MINIMUM_QOH_TO_SHOW_ITEM_IN_MARKETPLACES then consider we do not have available for marketplaces
	// to avoid problems of orders not fulfilled and low rankings, better show QOH = 0 than cancel the order.
	// Anyway, this can be revised, depending on internal marketplace order management 
	if ($QOH < ACI_MINIMUM_QOH_TO_SHOW_ITEM_IN_MARKETPLACES){
		$QOH = 0;
	}
	return $QOH;
}

function SQLInsertNewItemKLStockmarketplaces($StockID){
	$SQL="INSERT INTO klstockmarketplaces 
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

function ItemEnableLazadaInfo($StockID, $EnabledLazada){
	if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)){
		// Already exists, should exist!!! so only update the enable flag
		$SQL = "UPDATE klstockmarketplaces
				SET lazadaenabled='" . $EnabledLazada ."'
			WHERE klstockmarketplaces.stockid='" . $StockID . "'
				AND lazadaurl IS NOT NULL";
	}else{
		// does not exist, so need to insert a new row for the item as DISABLED, as it means we do not have the URL's yet
		$SQL=SQLInsertNewItemKLStockmarketplaces($StockID);
	}
	$DbgMsg = _('The SQL that failed to enable/disable the Lazada marketplace info was');
	$ErrMsg = _('Cannot enable/disable the Lazada marketplace info because');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemEnableShopeeInfo($StockID, $EnabledShopee){
	if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)){
		// Already exists, should exist!!! so only update the enable flag
		$SQL = "UPDATE klstockmarketplaces
				SET shopeeenabled='" . $EnabledShopee ."'
			WHERE klstockmarketplaces.stockid='" . $StockID . "'
				AND shopeeurl IS NOT NULL";
	}else{
		// does not exist, so need to insert a new row for the item as DISABLED, as it means we do not have the URL's yet
		$SQL=SQLInsertNewItemKLStockmarketplaces($StockID);
	}
	$DbgMsg = _('The SQL that failed to enable/disable the Shopee marketplace info was');
	$ErrMsg = _('Cannot enable/disable the Shopee marketplace info because');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemEnableTokopediaInfo($StockID, $EnabledTokopedia){
	if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)){
		// Already exists, so only update the enable flag
		$SQL = "UPDATE klstockmarketplaces
				SET tokopediaenabled='" . $EnabledTokopedia ."'
			WHERE klstockmarketplaces.stockid='" . $StockID . "'
				AND tokopediaurl IS NOT NULL";
	}else{
		// does not exist, so need to insert a new row for the item as DISABLED, as it means we do not have the URL's yet
		$SQL=SQLInsertNewItemKLStockmarketplaces($StockID);
	}
	$DbgMsg = _('The SQL that failed to enable/disable the Tokopedia marketplace info was');
	$ErrMsg = _('Cannot enable/disable the Tokopedia marketplace info because');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemInsertLazadaInfo($StockID, $EnabledLazada, $LazadaProductId, $URLLazada){
	$SQL="INSERT INTO klstockmarketplaces 
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
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemInsertShopeeInfo($StockID, $EnabledShopee, $ShopeeProductId, $URLShopee){
	$SQL="INSERT INTO klstockmarketplaces 
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
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemInsertTokopediaInfo($StockID, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia){
	$SQL="INSERT INTO klstockmarketplaces 
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
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemUpdateLazadaInfo($StockID, $EnabledLazada, $LazadaProductId, $URLLazada){
	$SQL = "UPDATE klstockmarketplaces
			SET lazadaurl = '" . $URLLazada . "',
				lazadaproductid = '" . $LazadaProductId ."',
				lazadaenabled='" . $EnabledLazada ."'
		WHERE klstockmarketplaces.stockid='" . $StockID . "'";

	$DbgMsg = _('The SQL that failed to update the Lazada marketplace info was');
	$ErrMsg = _('Cannot update the Lazada marketplace info because');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemUpdateShopeeInfo($StockID, $EnabledShopee, $ShopeeProductId, $URLShopee){
	$SQL = "UPDATE klstockmarketplaces
			SET shopeeurl = '" . $URLShopee . "',
				shopeeproductid = '" . $ShopeeProductId ."',
				shopeeenabled='" . $EnabledShopee ."'
		WHERE klstockmarketplaces.stockid='" . $StockID . "'";

	$DbgMsg = _('The SQL that failed to update the Shopee marketplace info was');
	$ErrMsg = _('Cannot update the Shopee marketplace info because');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemUpdateTokopediaInfo($StockID, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia){
	$SQL = "UPDATE klstockmarketplaces
			SET tokopediaurl = '" . $URLTokopedia . "',
				tokopediaproductid = '" . $TokopediaProductId ."',
				tokopediaenabled='" . $EnabledTokopedia ."'
		WHERE klstockmarketplaces.stockid='" . $StockID . "'";

	$DbgMsg = _('The SQL that failed to update the Tokopedia marketplace info was');
	$ErrMsg = _('Cannot update the Tokopedia marketplace info because');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function FindShopeeCategory($StockID, $Name, $Description){
	$ShopeeCat = "";
	if (isRing($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_RING;
	}elseif (isToeRing($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_TOE_RING;
	}elseif (isBrooche($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_BROOCHE;
	}elseif (isPiercing($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_PIERCING;
	}elseif (isEarring($StockID)){
		if (ItemInList("stud", $Description)){
			$ShopeeCat = SHOPEE_CATEGORY_EARRING_STUD;
		}else if (ItemInList("hoop", $Description)){
			$ShopeeCat = SHOPEE_CATEGORY_EARRING_HOOP;
		}else if (ItemInList("hook", $Description)){
			$ShopeeCat = SHOPEE_CATEGORY_EARRING_HOOK;
		}else{
			$ShopeeCat = SHOPEE_CATEGORY_EARRING;
		}
	}elseif (isEarcuff($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_EARRING_STUD;
	}elseif (isBracelet($StockID)){
		if (ItemInList("bangle", $Description)){
			$ShopeeCat = SHOPEE_CATEGORY_BANGLE;
		}else if (ItemInList("pearl", $Description)){
			$ShopeeCat = SHOPEE_CATEGORY_BRACELET_PEARL;
		}else{
			$ShopeeCat = SHOPEE_CATEGORY_BRACELET;
		}
	}elseif (isAnklet($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_ANKLET;
	}elseif (isPendant($StockID)){
		if (ItemInList("pearl", $Description)){
			$ShopeeCat = SHOPEE_CATEGORY_PENDANT_PEARL;
		}else{
			$ShopeeCat = SHOPEE_CATEGORY_PENDANT;
		}
	}elseif (isNecklace($StockID)){
		if (ItemInList("choker", $Description)){
			$ShopeeCat = SHOPEE_CATEGORY_CHOKER;
		}else if (ItemInList("pearl", $Description)){
			$ShopeeCat = SHOPEE_CATEGORY_NECKLACE_PEARL;
		}else{
			$ShopeeCat = SHOPEE_CATEGORY_NECKLACE;
		}
	}elseif (isTali($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_NECKLACE;
	}elseif (isBag($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_BAG;
	}elseif (isKeyHolder($StockID)){
		$ShopeeCat = SHOPEE_CATEGORY_KEYHOLDER;
	}
	return $ShopeeCat;
}

function FindLazadaMaterial($TypeOfShop, $Text){
	$Material = "";
	if ($TypeOfShop == 1){
		// if it is KL, then default material is Silver
		$Material = "Silver - Perak";	
	}else{
		// if it is Blink, then default material is Metal
		$Material = "Metal - Logam";	
	}

	if (ItemInList("silver", $Text)){
		$Material = "Silver - Perak";	
	}elseif (ItemInList("wood", $Text)){
		$Material = "Wood - Kayu";	
	}elseif (ItemInList("leather", $Text)){
		$Material = "Leather - Kulit";	
	}elseif (ItemInList("Resin", $Text)){
		$Material = "Resin";	
	}elseif (ItemInList("Shell", $Text)){
		$Material = "Shell";	
	}
	return $Material;
}

function FindLazadaStone($Text){
	$Stone = "";
	if (ItemInList("cat eye", $Text)){
		$Stone = "Cat Eye";	
	}elseif (ItemInList("freshwater", $Text)){
		$Stone = "Freshwater Pearl - Mutiara Air Tawar";	
	}elseif (ItemInList("pearl", $Text)){
		$Stone = "Pearl - Mutiara";	
	}elseif (ItemInList("hematite", $Text)){
		$Stone = "Hematite - Manik-manik";	
	}elseif (ItemInList("mother of pearl", $Text)){
		$Stone = "Mother of Pearl";	
	}elseif (ItemInList("opal", $Text)){
		$Stone = "Opal";	
	}elseif (ItemInList("turquoise", $Text)){
		$Stone = "Turquoise";	
	}elseif (ItemInList("Zircon", $Text)){
		$Stone = "Zircon";	
	}elseif (ItemInList("crystal", $Text)){
		$Stone = "Crystal -  Kristal";	
	}
	return $Stone;
}

function WhatsInTheBox($StockID){
	$Box = "";
	if (isRing($StockID)){
		$Box = "Ring";
	}elseif (isToeRing($StockID)){
		$Box = "Toe Ring";
	}elseif (isBrooche($StockID)){
		$Box = "Brooche";
	}elseif (isEarring($StockID)){
		$Box = "2 Earrings";
	}elseif (isEarcuff($StockID)){
		$Box = "2 Earcuffs";
	}elseif (isPiercing($StockID)){
		$Box = "Piercing";
	}elseif (isBracelet($StockID)){
		$Box = "Bracelet";
	}elseif (isAnklet($StockID)){
		$Box = "Anklet";
	}elseif (isPendant($StockID)){
		$Box = "Pendant";
	}elseif (isNecklace($StockID)){
		$Box = "Necklace";
	}elseif (isTali($StockID)){
		$Box = "Tali Cord";
	}elseif (isBag($StockID)){
		$Box = "Bag";
	}elseif (isKeyHolder($StockID)){
		$Box = "Key Holder";
	}
	return $Box . ", Pouchbag, Jewellery Box";
}

function FindLazadaColor($Text){
	$Color = "";
	if (ItemInList("white", $Text)){
		$Color = "White - Putih";	
	}elseif (ItemInList("yellow", $Text)){
		$Color = "Yellow - Kuning";	
	}elseif (ItemInList("silver", $Text)){
		$Color = "Silver - Perak";	
	}elseif (ItemInList("blue", $Text)){
		$Color = "Blue - Biru";	
	}elseif (ItemInList("red", $Text)){
		$Color = "Red - Merah";	
	}elseif (ItemInList("pink", $Text)){
		$Color = "Pink";	
	}elseif (ItemInList("Green", $Text)){
		$Color = "Green - Hijau";	
	}elseif (ItemInList("Grey", $Text)){
		$Color = "Grey - Abu abu";	
	}
	return $Color;
}

?>

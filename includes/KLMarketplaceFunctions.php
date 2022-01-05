<?php

/**************************************************************************************************
			GENERAL MARKETPLACE (SHOPEE, TOKOPEDIA) FUNCTIONS
**************************************************************************************************/

function CalculateCommissionTokopedia($CustomerCode, $OrderNo, $TotalAmount){
	if ($CustomerCode != "TOKOPEDIA"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = tokopedia", "error");
		include('includes/footer.php');
		exit;
	}
	// 1% from all order for Tokopedia
	$CommissionTPGlobal = round($TotalAmount * TOKOPEDIA_COMMISSION_PERCENT /100 ,0); // this commission still includes PPN

	// we need to pay comething to Tokopedia if shipper si SI-CEPAT, as it means free shipping for the customer, so we pay something
	$SQL = "SELECT salesorders.shipvia
		FROM salesorders 
		WHERE salesorders.orderno = '" . $OrderNo . "' ";			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Shipper = $myrow['shipvia'];
		$CommissionTPFreeShipping = 0;
		if ($Shipper == '12'){
			// if shipper is 12 = GRATIS ONGKIR TOKOPEDIA... then we shipped it via free shipping, we must pay 
			// 2,5% from every item with a max 0f 10.000 for Tokopedia as cost of shipment
			$SQL = "SELECT salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.discountpercent
				FROM salesorderdetails
				WHERE salesorderdetails.orderno = '" . $OrderNo . "' ";			
			$result = DB_query($SQL);
			while ($myrow = DB_fetch_array($result)) {
				$ItemPrice = $myrow['unitprice']*(1-$myrow['discountpercent']);
				$CommissionItem = min(round($ItemPrice * TOKOPEDIA_COMMISSION_FREE_SHIPPING_PER_ITEM_PERCENT /100 ,0), TOKOPEDIA_COMMISSION_FREE_SHIPPING_PER_ITEM_MAXIMUM); 
				$CommissionTPFreeShipping += $CommissionItem * $myrow['qtyinvoiced']; // this commission still has PPN
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

function CalculateCommissionShopee($CustomerCode, $OrderNo, $TotalAmount){
	if ($CustomerCode != "SHOPEE"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = shopee", "error");
		include('includes/footer.php');
		exit;
	}
	// 1,5% from all order for Shopee
	$Commission = round($TotalAmount * SHOPEE_COMMISSION_PERCENT /100 ,0); // this commission still includes PPN
	$Commission = round($Commission /((100 + PPN_PERCENT)/100) ,0); // this commision already net
	return $Commission;
}

function ClearUrl($Url){
	$Clean = str_replace("/", "\\/", $Url);
	return $Clean;
}

function CreateTextSize($stockid, $language, $IncludeTextDescription){
	$Size = ClassicalSize($stockid);
	if ($Size == "NO SIZE"){
		if (isRing($stockid)){
			$Size = RingSize($stockid);
		}else{
			$Size = NumberSize($stockid);
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

function ItemMarketplaceQOH($StockID, $db){
	// if we have more than ADMINCERDAS_MINIMUM_STOCK_TO_UPDATE we "cap" it, 
	// so we don't spend update credits updating QOH when it is not important for us
	$QOH = 	min(ItemOnlineQOH($StockID, $db), ADMINCERDAS_MINIMUM_STOCK_TO_UPDATE);
	
	//if less than ADMINCERDAS_MINIMUM_STOCK_TO_SHOW then consider we do not have available for marketplaces
	// to avoid problems of orders not fulfilled and low rankings, better show QOH = 0 than cancel the order.
	// Anyway, this can be revised, depending on internal marketplace order management 
	if ($QOH <= ADMINCERDAS_MINIMUM_STOCK_TO_SHOW){
		$QOH = 0;
	}
	return $QOH;
}

function ItemEnableShopeeInfo($StockId, $EnabledShopee, $db){
	$SQL = "UPDATE klstockmarketplaces
			SET shopeeenabled='" . $EnabledShopee ."'
		WHERE klstockmarketplaces.stockid='" . $StockId . "'
			AND shopeeurl IS NOT NULL";

	$DbgMsg = _('The SQL that failed to enable/disable the Shopee marketplace info was');
	$ErrMsg = _('Cannot enable/disable the Shopee marketplace info because');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemEnableTokopediaInfo($StockId, $EnabledTokopedia, $db){
	$SQL = "UPDATE klstockmarketplaces
			SET tokopediaenabled='" . $EnabledTokopedia ."'
		WHERE klstockmarketplaces.stockid='" . $StockId . "'
			AND tokopediaurl IS NOT NULL";

	$DbgMsg = _('The SQL that failed to enable/disable the Tokopedia marketplace info was');
	$ErrMsg = _('Cannot enable/disable the Tokopedia marketplace info because');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemInsertShopeeInfo($StockId, $EnabledShopee, $ShopeeProductId, $URLShopee, $db){
	$SQL="INSERT INTO klstockmarketplaces 
				(stockid,
				shopeeurl,
				shopeeproductid,
				shopeeenabled)
		VALUES (
			'" . $StockId . "',
			'" . $URLShopee . "',
			'" . $ShopeeProductId . "',
			'" . $EnabledShopee . "')";

	$DbgMsg = _('The SQL that failed to insert the Shopee marketplace info was');
	$ErrMsg = _('Cannot insert the Shopee marketplace info because');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemInsertTokopediaInfo($StockId, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia, $db){
	$SQL="INSERT INTO klstockmarketplaces 
				(stockid,
				tokopediaurl,
				tokopediaproductid,
				tokopediaenabled)
		VALUES (
			'" . $StockId . "',
			'" . $URLTokopedia . "',
			'" . $TokopediaProductId . "',
			'" . $EnabledTokopedia . "')";

	$DbgMsg = _('The SQL that failed to insert the Tokopedia marketplace info was');
	$ErrMsg = _('Cannot insert the Tokopedia marketplace info because');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemUpdateShopeeInfo($StockId, $EnabledShopee, $ShopeeProductId, $URLShopee, $db){
	$SQL = "UPDATE klstockmarketplaces
			SET shopeeurl = '" . $URLShopee . "',
				shopeeproductid = '" . $ShopeeProductId ."',
				shopeeenabled='" . $EnabledShopee ."'
		WHERE klstockmarketplaces.stockid='" . $StockId . "'";

	$DbgMsg = _('The SQL that failed to update the Shopee marketplace info was');
	$ErrMsg = _('Cannot update the Shopee marketplace info because');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function ItemUpdateTokopediaInfo($StockId, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia, $db){
	$SQL = "UPDATE klstockmarketplaces
			SET tokopediaurl = '" . $URLTokopedia . "',
				tokopediaproductid = '" . $TokopediaProductId ."',
				tokopediaenabled='" . $EnabledTokopedia ."'
		WHERE klstockmarketplaces.stockid='" . $StockId . "'";

	$DbgMsg = _('The SQL that failed to update the Tokopedia marketplace info was');
	$ErrMsg = _('Cannot update the Tokopedia marketplace info because');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

?>

<?php

function Get_SQL_to_PHP_time_difference($db) {
	// Based on http://stackoverflow.com/questions/3108591/calculate-number-of-hours-between-2-dates-in-php
    $NowPHP = new DateTime();

	$SQL = "SELECT NOW()";
	$result = DB_query($SQL);
	$Row = DB_fetch_row($result);
    $NowSQL = new DateTime($Row[0]);

	$diff = $NowSQL->diff($NowPHP);
	if ($NowSQL < $NowPHP){
		$Offset = -$diff->h;
	}elseif ($NowSQL > $NowPHP){
		$Offset = 24-$diff->h;
	}else{
		$Offset = 0;
	}
	return $Offset;
}

function Get_SQL_OC_to_PHP_time_difference($db) {
	// Based on http://stackoverflow.com/questions/3108591/calculate-number-of-hours-between-2-dates-in-php
    $NowPHP = new DateTime();

	$SQL = "SELECT NOW()";
	$result = DB_query_oc($SQL);
	$Row = DB_fetch_row($result);
    $NowSQL = new DateTime($Row[0]);

	$diff = $NowSQL->diff($NowPHP);
	if ($NowSQL < $NowPHP){
		$Offset = -$diff->h;
	}elseif ($NowSQL > $NowPHP){
		$Offset = 24-$diff->h;
	}else{
		$Offset = 0;
	}
	return $Offset;

//	return -1; // Bali to JKT (OC DB lives in JKT time)
}

function GetServerTimeNow($TimeDifference){
	// webERP DB and OpenCart DB triggers happens on DB time, not local time,
	// so when checking if a row has been updated or created in webERP or OC, we need to check the timestamp against ServerTime :-)
	// 4 hours of my life were invested finding it out...
	$Now = Date('Y-m-d H:i:s');
	$ServerNow = date('Y-m-d H:i:s', strtotime( $Now . $TimeDifference . ' hours'));
	return $ServerNow;
}

function PrintTimeInformation($db) {
	$TimeDifference = Get_SQL_to_PHP_time_difference($db);
	$Text = 'Server time difference: ' . $TimeDifference . "\n" .
			'Server time now: ' . GetServerTimeNow($TimeDifference) . "\n".
			'webERP time now: ' . date('d/M/Y H:i:s') . "\n\n";
	return $Text;
}

function CheckLastTimeRun($Script, $db){
	if ($Script == 'OpenCartToWeberp'){
		$ConfigName = 'OpenCartToWeberp_LastRun';
	}elseif ($Script == 'WeberpToOpenCartHourly'){
		$ConfigName = 'WeberpToOpenCartHourly_LastRun';
	}elseif ($Script == 'WeberpToOpenCartDaily'){
		$ConfigName = 'WeberpToOpenCartDaily_LastRun';
	}
	$sql = "SELECT confvalue
			FROM config
			WHERE confname = '". $ConfigName ."'";
	$result = DB_query($sql);
	if (DB_num_rows($result)==0){
		return  "2999-12-31"; // Error, so we will not change anything
	} else {
		$myrow = DB_fetch_array($result);
		return  $myrow['confvalue'];
	}
}

function SetLastTimeRun($Script, $db){
	if ($Script == 'OpenCartToWeberp'){
		// Updating from OC to webERP: Check the time zone used in OC DB 
		$ServerNow = GetServerTimeNow(Get_SQL_OC_to_PHP_time_difference($db));
		$_SESSION['OpenCartToWeberp_LastRun'] = $ServerNow;
		$sql = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'OpenCartToWeberp_LastRun'";
	}elseif ($Script == 'WeberpToOpenCartHourly'){
		// Updating from webERP to OC: Check the time zone used in webERP DB 
		$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference($db));
		$_SESSION['WeberpToOpenCartHourly_LastRun'] = $ServerNow;
		$sql = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'WeberpToOpenCartHourly_LastRun'";
	}elseif ($Script == 'WeberpToOpenCartDaily'){
		// Updating from webERP to OC: Check the time zone used in webERP DB 
		$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference($db));
		$_SESSION['WeberpToOpenCartDaily_LastRun'] = $ServerNow;
		$sql = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'WeberpToOpenCartDaily_LastRun'";
	}
	$ErrMsg =_('Could not update Last Run Time of this script because');
	$result = DB_query($sql,$ErrMsg);
}

function DataExistsInOpenCart($db_oc, $table, $f1, $v1, $f2 = '', $v2 = ''){
	if ($f2 == ''){
		/* Primary key is 1 field only */
		$SQL = "SELECT COUNT(*)
				FROM " . $table . "
				WHERE " . $f1 . " = '" . $v1 . "'";
	}else{
		/* Primary key is 2 fields */
		$SQL = "SELECT COUNT(*)
				FROM " . $table . "
				WHERE " . $f1 . " = '" . $v1 . "'
					AND " . $f2 . " = '" . $v2 . "'";
	}
	$ErrMsg =_('Could not check existence of data in OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);

	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Exists = ($myrow[0] > 0);
	}else{
		$Exists = false;
	}
	return $Exists;
}

function GetOpenCartProductId($model, $db_oc){
	$SQL = "SELECT product_id
			FROM oc_product
			WHERE model = '" . $model . "'";
	$ErrMsg =_('Could not get the ProductId in OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetManufacturerFromProductId($ProductId, $db_oc){
	$SQL = "SELECT manufacturer_id
			FROM oc_product
			WHERE product_id = '" . $ProductId . "'";
	$ErrMsg =_('Could not get the ManufacturerId in OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}


function GetOpenCartLanguageId($language, $db_oc){
	$SQL = "SELECT language_id
			FROM oc_language
			WHERE locale LIKE '%" . $language . "%'";
	$ErrMsg =_('Could not get the LanguageId in OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetWeberpCustomerIdFromEmail($email, $db){
	$SQL = "SELECT debtorno
			FROM custbranch
			WHERE email = '" . $email . "'";
	$ErrMsg =_('Could not get the CustomerId in webERP because');
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetWeberpComissionFlatDOKU($db){
	$SQL = "SELECT comissionflatdoku
			FROM locations, klonlinepartners
			WHERE locations.onlinepartnercode = klonlinepartners.onlinepartnercode
				AND locations.loccode = " . CODE_ONLINE_SHOP . "";
	$ErrMsg ='Could not get the Commission Flat DOKU in webERP because';
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Com = $myrow['comissionflatdoku'];
	}else{
		$Com = 0;
	}
	return $Com;
}

function GetWeberpComissionCCDOKU($db){
	$SQL = "SELECT comissionccdoku
			FROM locations, klonlinepartners
			WHERE locations.onlinepartnercode = klonlinepartners.onlinepartnercode
				AND locations.loccode = " . CODE_ONLINE_SHOP . "";
	$ErrMsg ='Could not get the Commission CC DOKU in webERP because';
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Com = $myrow['comissionccdoku'];
	}else{
		$Com = 0;
	}
	return $Com;
}

function GetWeberpCustomerIdFromCurrency($Currency, $db){
	return WEBERP_ONLINE_RETAIL_CUSTOMER_CODE_PREFIX . $Currency;
}

function GetWeberpCustomerIdFromCustomerGroupAndCurrency($CustomerGroup, $Currency, $db){
	if (($CustomerGroup == "4") 
		OR ($CustomerGroup == "6")
		OR ($CustomerGroup == "7")){
		// it is wholesale
		$CustomerId = WEBERP_ONLINE_WHOLESALE_CUSTOMER_CODE_PREFIX . $Currency;
	}else{
		// it is retail or guest
		$CustomerId = WEBERP_ONLINE_RETAIL_CUSTOMER_CODE_PREFIX . $Currency;
	}
	return $CustomerId;
}

function GetWeberpForeignCurrencySurchargeFactor($Location, $db){
	$SQL = "SELECT foreigncurrencysurchargefactor
			FROM locations, klonlinepartners
			WHERE locations.onlinepartnercode = klonlinepartners.onlinepartnercode
				AND locations.loccode = '" . $Location . "'";
	$ErrMsg ='Could not get the online Foreign Currency Surcharge factor in webERP because';
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Factor = $myrow['foreigncurrencysurchargefactor'];
	}else{
		$Factor = 1;
	}
	return $Factor;
}

function GetWeberpGLAccountPayPalFromCustomer($CustomerCode, $db){

	$Area = GetAreaFromCustomer($CustomerCode);
	$Currency = GetCurrencyFromCustomer($CustomerCode);
	$OnlinePartner = GetOnlinePartnerFromArea($Area);

	$SQL = "SELECT accountdokuidr,
					accountpaypalaud,
					accountpaypalusd,
					accountpaypaleur
			FROM klonlinepartners
			WHERE klonlinepartners.onlinepartnercode = '" . $OnlinePartner . "'";
	$ErrMsg ='Could not get the Online account GL Account for ' . $OnlinePartner . ' in webERP because';
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		if($Currency == "AUD"){
			$GLAccount = $myrow['accountpaypalaud'];
		}elseif($Currency == "USD"){
			$GLAccount = $myrow['accountpaypalusd'];
		}elseif($Currency == "EUR"){
			$GLAccount = $myrow['accountpaypaleur'];
		}elseif($Currency == "IDR"){
			$GLAccount = $myrow['accountdokuidr'];
		}
	}else{
		$GLAccount = '';
	}
	// in Paypal there is no IDR yet, so we pay by bank trasnfer and record payment manually in webERP
	return $GLAccount;
}

function GetWeberpGLCommissionAccountPayPalFromCustomer($CustomerCode, $db){
	$Area = GetAreaFromCustomer($CustomerCode);
	$Currency = GetCurrencyFromCustomer($CustomerCode);
	$OnlinePartner = GetOnlinePartnerFromArea($Area);

	$SQL = "SELECT accountdokucomissionidr,
					accountpaypalcomissionaud,
					accountpaypalcomissionusd,
					accountpaypalcomissioneur
			FROM klonlinepartners
			WHERE klonlinepartners.onlinepartnercode = '" . $OnlinePartner . "'";
	$ErrMsg ='Could not get the PayPal Comission GL Account for ' . $Currency . ' in webERP because';
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		if($Currency == "AUD"){
			$GLAccount = $myrow['accountpaypalcomissionaud'];
		}elseif($Currency == "USD"){
			$GLAccount = $myrow['accountpaypalcomissionusd'];
		}elseif($Currency == "EUR"){
			$GLAccount = $myrow['accountpaypalcomissioneur'];
		}elseif($Currency == "IDR"){
			$GLAccount = $myrow['accountdokucomissionidr'];
		}
	}else{
		$GLAccount = '';
	}
	// in Paypal there is no IDR yet, so we pay by bank trasnfer and record payment manually in webERP
	return $GLAccount;
}

function GetWeberpOrderNo($CustomerId, $OrderId, $db){
	$SQL = "SELECT orderno
			FROM salesorders
			WHERE debtorno = '" . $CustomerId . "'
				AND branchcode = '" . $CustomerId . "'
				AND customerref = '" . $OrderId . "'";
	$ErrMsg =_('Could not get the OrderNo in webERP because');
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetOnlineOrderNoFromWeberp($OrderId, $db){
	$SQL = "SELECT customerref
			FROM salesorders
			WHERE orderno = '" . $OrderId . "'";
	$ErrMsg =_('Could not get the Online Order No in webERP because');
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetWeberpCustomerCurrency($CustomerId, $db){
	$SQL = "SELECT currcode
			FROM debtorsmaster
			WHERE debtorno = '" . $CustomerId . "'";
	$ErrMsg =_('Could not get the CustomerCurrency in webERP because');
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetWeberpCurrencyRate($CurrencyCode, $db){
	$SQL = "SELECT rate
			FROM currencies
			WHERE currabrev = '" . $CurrencyCode . "'";
	$ErrMsg =_('Could not get the Currency Rate in webERP because');
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetTotalTitleFromOrder($Concept, $OrderId, $db_oc){
	$SQL = "SELECT title
			FROM oc_order_total
			WHERE order_id = '" . $OrderId . "'
				AND code = '" . $Concept . "'";
	$ErrMsg =_('Could not get the '. $Concept . ' title from OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function GetTotalFromOrder($Concept, $OrderId, $db_oc){
	$SQL = "SELECT SUM(value)
			FROM oc_order_total
			WHERE order_id = '" . $OrderId . "'
				AND code = '" . $Concept . "'";
	$ErrMsg =_('Could not get the '. $Concept . ' total from OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function ItemOnlineQOH($StockId, $db){
	$SQL = "SELECT SUM(locstock.quantity)
			FROM locstock, locations
			WHERE locstock.loccode = locations.loccode
				AND locstock.stockid = '" . $StockId . "'
				AND locations.stockavailableforonline = '1'";
	$ErrMsg =_('Could not get the QOH available in webERP for OpenCart because');
	$result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function GetOnlinePriceList($db){
	$SQL = "SELECT debtorsmaster.salestype
			FROM debtorsmaster
			WHERE debtorsmaster.debtorno = '" . WEBERP_ONLINE_RETAIL_CUSTOMER_CODE_PREFIX . OPENCART_DEFAULT_CURRENCY . "'";
	$result = DB_query($SQL);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return array($myrow['salestype'], OPENCART_DEFAULT_CURRENCY);
	}else{
		return array(0,0);
	}
}

function GetDiscount($DiscountCategory, $Quantity, $PriceList, $db){
	/* Select the disount rate from the discount Matrix */
	$result = DB_query("SELECT MAX(discountrate) AS discount
						FROM discountmatrix
						WHERE salestype='" .  $PriceList . "'
						AND discountcategory ='" . $DiscountCategory . "'
						AND quantitybreak <= '" .$Quantity ."'");
	$myrow = DB_fetch_row($result);
	if ($myrow[0]==NULL){
		$DiscountMatrixRate = 0;
	} else {
		$DiscountMatrixRate = $myrow[0];
	}
	return $DiscountMatrixRate;
}

function MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList, $db, $db_oc){
	$CustomerGroupId = 1; // Retail Customers
	$Priority = 1;
	$ManufacturerId = GetManufacturerFromProductId($ProductId, $db_oc);
	
	if ($DiscountCategory == ''){
		// ProductId has no discount in webERP
		// so we delete it in OpenCart
		$SQL = "DELETE FROM oc_" . WEBERP_DISCOUNTS_IN_OPENCART_TABLE . "
				WHERE product_id = '" . $ProductId . "'";
		$DeleteErrMsg = _('The SQL to delete the product discount in Opencart table ') . ' ' . WEBERP_DISCOUNTS_IN_OPENCART_TABLE . ' ' . ('failed');
		$resultDelete = DB_query_oc($SQL,$DeleteErrMsg,$DbgMsg,true);
	}else{
		// ProductId has some discount in webERP
		// so replicate all the discounts in OpenCart
		$SQL = "SELECT quantitybreak,
						discountrate
				FROM discountmatrix
				WHERE salestype = '" . $PriceList . "'
					AND discountcategory = '" . $DiscountCategory . "'
				ORDER BY quantitybreak";
		$ErrMsg =_('Could not get the discount matrix in webERP because');
		$result = DB_query($SQL,$ErrMsg);
		if(DB_num_rows($result) != 0){
			while ($myrow = DB_fetch_array($result)){
				$DiscountedPrice = round($Price * (1 - $myrow['discountrate']),0);
				UpdateDiscountInOpenCart($ProductId, $CustomerGroupId, $myrow['quantitybreak'], $Priority, $DiscountedPrice, $db_oc);
				// Now we add the item to the category discount 
				if ($ManufacturerId == 1){
					AssignSalesCategoryToProductInOpenCart($ProductId, KL_OUTLET, FALSE, $db_oc);
				}else{
					AssignSalesCategoryToProductInOpenCart($ProductId, BLINK_OUTLET, FALSE, $db_oc);
				}
			}
		}
	}
}

function UpdateDiscountInOpenCart($ProductId, $CustomerGroupId, $Quantity, $Priority, $DiscountedPrice, $db_oc){
	if (WEBERP_DISCOUNTS_IN_OPENCART_TABLE == 'product_discount'){
		/* use the table product_discount */
		$SQL = "SELECT product_discount_id
				FROM oc_product_discount
				WHERE product_id = '" . $ProductId . "'
					AND quantity = '" . $Quantity . "'
					AND customer_group_id = '" . $CustomerGroupId ."'";

		$ErrMsg =_('Could not get the product discount in OpenCart because');
		$result = DB_query_oc($SQL,$ErrMsg);
		if(DB_num_rows($result) != 0){
			// There is already a discount, so we need to update it
			$SQL = "UPDATE oc_product_discount
					SET quantity = '" . $Quantity . "',
						priority = '" . $Priority . "',
						price = '" . $DiscountedPrice . "'
					WHERE product_id = '" . $ProductId . "'
						AND quantity = '" . $Quantity . "'
						AND customer_group_id = '" . $CustomerGroupId ."'";
			$UpdateErrMsg = _('The SQL to update the product discount in Opencart failed');
			$resultUpdate = DB_query_oc($SQL,$UpdateErrMsg,$DbgMsg,true);
		}else{
			// there is no discount in OpenCart yet, so we need to create one
			$SQL = "INSERT INTO oc_product_discount
						(product_id,
						customer_group_id,
						quantity,
						priority,
						price)
					VALUES (
						'" . $ProductId . "',
						'" . $CustomerGroupId . "',
						'" . $Quantity . "',
						'" . $Priority . "',
						'" . $DiscountedPrice . "'
					)";
			$InsertErrMsg = _('The SQL to insert the product discount in Opencart failed');
			$resultUpdate = DB_query_oc($SQL,$InsertErrMsg,$DbgMsg,true);
		}
	}else{
		/* use the table product_special */
		$SQL = "SELECT product_special_id
				FROM oc_product_special
				WHERE product_id = '" . $ProductId . "'
					AND customer_group_id = '" . $CustomerGroupId ."'";

		$ErrMsg =_('Could not get the product special in OpenCart because');
		$result = DB_query_oc($SQL,$ErrMsg);
		if(DB_num_rows($result) != 0){
			// There is already a special, so we need to update it
			$SQL = "UPDATE oc_product_special
					SET priority = '" . $Priority . "',
						price = '" . $DiscountedPrice . "'
					WHERE product_id = '" . $ProductId . "'
						AND customer_group_id = '" . $CustomerGroupId ."'";
			$UpdateErrMsg = _('The SQL to update the product special in Opencart failed');
			$resultUpdate = DB_query_oc($SQL,$UpdateErrMsg,$DbgMsg,true);
		}else{
			// there is no special in OpenCart yet, so we need to create one
			$SQL = "INSERT INTO oc_product_special
						(product_id,
						customer_group_id,
						priority,
						price)
					VALUES (
						'" . $ProductId . "',
						'" . $CustomerGroupId . "',
						'" . $Priority . "',
						'" . $DiscountedPrice . "'
					)";
			$InsertErrMsg = _('The SQL to insert the product special in Opencart failed');
			$resultUpdate = DB_query_oc($SQL,$InsertErrMsg,$DbgMsg,true);
		}
	}
}

function GetOpenCartSettingId($Store, $Code, $Key, $db_oc){
	$SQL = "SELECT setting_id
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


function UpdateSettingValueOpenCart($SettingId, $Value, $db_oc){
	$DbgMsg = _('The SQL statement that failed was');
	$UpdateErrMsg = _('The SQL to update setting value in Opencart failed');
	$sqlUpdate = "UPDATE oc_setting
					SET	value = '" . $Value . "'
				WHERE setting_id = '" . $SettingId . "'";
	$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);
}

function CreateMetaDescriptionSalesCategory($Group, $Item){
	$MetaDescription = $Group . ' ' . $Item;
	return $MetaDescription;
}

function CreateMetaDescriptionItem($StockId, $Text){
	$MetaDescription = $StockId . " " . CleanText($Text);
	return $MetaDescription;
}

function CreateMetaTitleItem($StockId, $Name, $Separator){
	$MetaTitle = $StockId . $Separator . $Name;
	return $MetaTitle;
}

function CreateMetaKeywordItem($StockId, $StoreName, $Tag, $TagSeparator){
	$MetaKeyword = $StockId . $TagSeparator . $StoreName . $TagSeparator . $Tag;
	return $MetaKeyword;
}

function CreateSEOKeyword($KeyWord){
	$SEOKeyword =trim($KeyWord);
	$SEOKeyword = str_ireplace(' ', '-', $SEOKeyword);
	$SEOKeyword = str_ireplace(',', '-', $SEOKeyword);
	$SEOKeyword = str_ireplace(';', '-', $SEOKeyword);
	$SEOKeyword = str_ireplace('.', '-', $SEOKeyword);
	$SEOKeyword = str_ireplace('/', '-', $SEOKeyword);
	return $SEOKeyword;
}

function FormatDescriptionOpencart($MessedText){
	//preserve the line breaks as in https://stackoverflow.com/questions/9345514/is-there-any-way-to-convert-plain-text-into-html-with-line-breaks
	$MessedText = CleanText($MessedText);
	$MessedText = nl2br($MessedText);
	$MessedText = str_ireplace('<br />' , '</p><p>', $MessedText);
	$MessedText = '<p>'. $MessedText . '</p>';
	
	return $MessedText;
}

function CleanText($MessedText){
	$MessedText = strip_tags($MessedText);
    $MessedText = str_ireplace('/', '', $MessedText);
	$MessedText = str_ireplace('"', '', $MessedText);
    $CleanText = str_ireplace("\'", '', $CleanText);
    $CleanText = str_ireplace("'", '', $CleanText);
	return $MessedText;
}

function CleanKeywordText($Text){
	$Text =trim($Text);
	$Text = str_ireplace(' ', ',', $Text);
	$Text = str_ireplace(',', ',', $Text);
	$Text = str_ireplace(';', ',', $Text);
	$Text = str_ireplace('.', ',', $Text);
	$Text = str_ireplace('/', ',', $Text);
	return $Text;
}

function GetWeberpItemBrand($webERPCategoryId, $ManufacturerId){
	if (ItemInList($webERPCategoryId, LIST_STOCK_CATEGORIES_KAPAL_LAUT)
		OR ($ManufacturerId == 1)){
		$ItemBrand = "KL";
	}elseif (ItemInList($webERPCategoryId, LIST_STOCK_CATEGORIES_BLINK)
		OR ($ManufacturerId == 2)){
		$ItemBrand = "BL";
	}elseif (ItemInList($webERPCategoryId, LIST_STOCK_CATEGORIES_GENERAL)){
		$ItemBrand = "GE";
	}else{
		// should never happen
		$ItemBrand = "KL";
	}
	return $ItemBrand;
}

Function GetNextSequenceNo ($SequenceType){

	global $db;
	/* SQL to get the next transaction number these are maintained in the table SysTypes - Transaction Types
	Also updates the transaction number

	10 sales invoice
	11 sales credit note
	12 sales receipt
	etc	*/

	DB_query("LOCK TABLES systypes WRITE");

	$SQL = "SELECT typeno FROM systypes WHERE typeid = '" . $SequenceType . "'";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');
	$GetTransNoResult = DB_query($SQL,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_row($GetTransNoResult);

	$SQL = "UPDATE systypes SET typeno = '" . ($myrow[0] + 1) . "' WHERE typeid = '" . $SequenceType . "'";
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
	$DbgMsg =  _('The following SQL to increment the transaction number was used');
	$UpdTransNoResult = DB_query($SQL,$ErrMsg,$DbgMsg);

	DB_query("UNLOCK TABLES");

	return $myrow[0] + 1;
}

function InsertCustomerReceipt ($CustomerCode, $AmountPaid, $FreightCost, $CustomerCurrency, $Rate, $BankAccount, $PaymentSystem, $TransactionID, $OrderNo, $PeriodNo, $db) {

	$CustomerReceiptNo = GetNextSequenceNo(12);

	$HeaderSQL = "INSERT INTO debtortrans (transno,
											type,
											debtorno,
											branchcode,
											trandate,
											inputdate,
											prd,
											reference,
											order_,
											rate,
											ovamount,
											ovfreight,
											invtext )
							VALUES ('". $CustomerReceiptNo  . "',
									'12',
									'" . $CustomerCode . "',
									'" . $CustomerCode . "',
									'" . Date('Y-m-d H:i') . "',
									'" . Date('Y-m-d H:i') . "',
									'" . $PeriodNo . "',
									'" . $TransactionID ."',
									'". $OrderNo . "',
									'" . $Rate . "',
									'" . round(-($AmountPaid-$FreightCost),2) . "',
									'" . round(-($FreightCost),2) . "',
									'" . $PaymentSystem . _(' OC Payment') . "')";

	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('The customer receipt cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL,$ErrMsg,$DbgMsg);

	$SQL = "UPDATE debtorsmaster
				SET lastpaiddate = '" . Date('Y-m-d') . "',
				lastpaid='" . $AmountPaid ."'
			WHERE debtorsmaster.debtorno='" . $CustomerCode . "'";

	$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
	$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	/*now enter the BankTrans entry */
	//First get the currency and rate for the bank account
	$BankResult = DB_query("SELECT rate FROM bankaccounts INNER JOIN currencies ON bankaccounts.currcode=currencies.currabrev WHERE accountcode='" . $BankAccount . "'");
	$BankRow = DB_fetch_array($BankResult);
	$FunctionalRate = $BankRow['rate'];

	$SQL="INSERT INTO banktrans (type,
								transno,
								bankact,
								ref,
								exrate,
								functionalexrate,
								transdate,
								banktranstype,
								amount,
								currcode)
		VALUES (12,
			'" . $CustomerReceiptNo . "',
			'" . $BankAccount . "',
			'" . _('OC Receipt') . ' ' . $CustomerCode . ' ' . $TransactionID  . "',
			'" . $Rate / $FunctionalRate  . "',
			'" . $FunctionalRate . "',
			'" . Date('Y-m-d') . "',
			'" . $PaymentSystem . ' ' . _('online') . "',
			'" . ($AmountPaid * $Rate / $FunctionalRate) . "',
			'" . $CustomerCurrency . "'
		)";
	$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
	$ErrMsg = _('Cannot insert a bank transaction');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);


	/* then enter GLTrans records for discount, bank and debtors */
	/* Bank account entry first */
	$Narrative = $CustomerCode . ' ' . _('payment for order') . ' ' . $OrderNo . ' ' . _('Transaction ID') . ': ' . $TransactionID;
	$SQL="INSERT INTO gltrans (	type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
			VALUES (12,
					'" . $CustomerReceiptNo . "',
					'" . Date('Y-m-d') . "',
					'" . $PeriodNo . "',
					'" . $BankAccount . "',
					'" . $Narrative . "',
					'" . ($AmountPaid) /$Rate . "'
				)";
	$DbgMsg = _('The SQL that failed to insert the GL transaction for the bank account debit was');
	$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

/* Now Credit Debtors account with receipts + discounts */
	$SQL="INSERT INTO gltrans ( type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
				VALUES (12,
						'" . $CustomerReceiptNo . "',
						'" . Date('Y-m-d') . "',
						'" . $PeriodNo . "',
						'". $_SESSION['CompanyRecord']['debtorsact'] . "',
						'" . $Narrative . "',
						'" . -(($AmountPaid) /$Rate). "' )";
	$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
	$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	EnsureGLEntriesBalanceOpenCart(12,$CustomerReceiptNo);
}

function EnsureGLEntriesBalanceOpenCart ($TransType, $TransTypeNo) {
	/*Ensures general ledger entries balance for a given transaction */
	global $db;

	$result = DB_query("SELECT SUM(amount)
						FROM gltrans
						WHERE type = '" . $TransType . "'
						AND typeno = '" . $TransTypeNo . "'");
	$myrow = DB_fetch_row($result);
	$Difference = $myrow[0];
	if (abs($Difference)!=0){
		if (abs($Difference)>0.1){
			message_log(_('The general ledger entries created do not balance. See your system administrator'),'error');
		} else {
			$result = DB_query("SELECT counterindex,
										MAX(amount)
								FROM gltrans
								WHERE type = '" . $TransType . "'
								AND typeno = '" . $TransTypeNo . "'
								GROUP BY counterindex");
			$myrow = DB_fetch_array($result);
			$TransToAmend = $myrow['counterindex'];
			$result = DB_query("UPDATE gltrans SET amount = amount - " . $Difference . "
								WHERE counterindex = '" . $TransToAmend . "'");

		}
	}
}

function TransactionCommissionGL ($CustomerCode, $BankAccount, $CommissionAccount, $Commission, $Currency, $Rate, $PaymentSystem, $TransactionID, $PeriodNo, $db) {

	$PaymentNo = GetNextSequenceNo(1);

	/*now enter the BankTrans entry */
	//First get the currency and rate for the bank account
	$BankResult = DB_query("SELECT rate FROM bankaccounts INNER JOIN currencies ON bankaccounts.currcode=currencies.currabrev WHERE accountcode='" . $BankAccount . "'");
	$BankRow = DB_fetch_array($BankResult);
	$FunctionalRate = $BankRow['rate'];

	$SQL="INSERT INTO banktrans (type,
								transno,
								bankact,
								ref,
								exrate,
								functionalexrate,
								transdate,
								banktranstype,
								amount,
								currcode)
						VALUES (1,
							'" . $PaymentNo . "',
							'" . $BankAccount . "',
							'" . $PaymentSystem . ' ' . _('Transaction Fees') . ' ' . $CustomerCode . ' ' . $TransactionID  . "',
							'" . $Rate / $FunctionalRate  . "',
							'" . $FunctionalRate . "',
							'" . Date('Y-m-d') . "',
							'" . $PaymentSystem . ' ' . _('Transaction Fees') . "',
							'" . -($Commission * $Rate / $FunctionalRate) . "',
							'" .$Currency . "'
						)";
	$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
	$ErrMsg = _('Cannot insert a bank transaction');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	/* Bank account entry first */
	$Narrative = $CustomerCode . ' ' . $PaymentSystem . ' ' . _('Fees for Transaction ID') . ': ' . $TransactionID;
	$SQL="INSERT INTO gltrans (	type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
			VALUES (1,
					'" . $PaymentNo . "',
					'" . Date('Y-m-d') . "',
					'" . $PeriodNo . "',
					'" . $BankAccount . "',
					'" . $Narrative . "',
					'" . -($Commission /$Rate) . "'
				)";
	$DbgMsg = _('The SQL that failed to insert the Paypal transaction fee from the bank account debit was');
	$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

/* Now Credit Debtors account with receipts + discounts */
	$SQL="INSERT INTO gltrans ( type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
				VALUES (1,
						'" . $PaymentNo . "',
						'" . Date('Y-m-d') . "',
						'" . $PeriodNo . "',
						'". $CommissionAccount . "',
						'" . $Narrative . "',
						'" . ($Commission /$Rate). "' )";
	$DbgMsg = _('The SQL that failed to insert the Paypal transaction fee for the commission account credit was');
	$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	EnsureGLEntriesBalanceOpenCart(1,$PaymentNo);
}

function ChangeOrderQuotationFlag($OrderNo, $Flag, $db){
	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('The Change of quotation flag in salesorders table');
	$sqlUpdate = "UPDATE salesorders
					SET quotation = " . $Flag . "
					WHERE orderno = '" . $OrderNo . "'";
	$resultUpdate = DB_query($sqlUpdate,$ErrMsg,$DbgMsg,true);
}

function GetPaypalReturnDataInArray($RawData){
	$ResponseArray = Array();
	$MainArray = explode(',', str_replace(array('{', '}', '"'), "", $RawData));
	foreach ($MainArray as $i => $value) {
		$TmpArray = explode(':', $value);
		if(sizeof($TmpArray) > 1) {
			$ResponseArray[$TmpArray[0]] = $TmpArray[1];
		}
	}
	return $ResponseArray;
}

function MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, $StoreId, $LanguageId, $db_oc){
	// only work on SEO URL if we are on "Insert" action, as "Update" will lead to 404 errors from Google Bots and
	// links created before the update moficiation (as the new link will be different and old ones will not be found.
	if ($Action == "Insert"){
		// search if we already have it
		$SQL = "SELECT seo_url_id
				FROM oc_seo_url
				WHERE query = '" . $SEOQuery . "'
					AND store_id = '" . $StoreId . "'
					AND language_id = '" .  $LanguageId . "'";
		$ErrMsg =_('Could not get the SEO URL in Opencart because');
		$result = DB_query_oc($SQL,$ErrMsg);
		if(DB_num_rows($result) != 0){
			// if we have it, we update it
			$myrow = DB_fetch_array($result);
			$SeoUrlId = $myrow['seo_url_id'];
			
			$DbgMsg = _('The SQL that failed was');
			$ErrMsg = _('The MaintainSeoUrl function failed');
			$sqlUpdate = "UPDATE oc_seo_url SET
							keyword ='" . $SEOKeyword . "'
						WHERE seo_url_id = '" . $SeoUrlId . "'";
			$resultUpdate = DB_query_oc($sqlUpdate,$ErrMsg,$DbgMsg,true);
		}else{
			// otherwise we insert it
			$DbgMsg = _('The SQL that failed was');
			$ErrMsg = _('The MaintainSeoUrl function failed');
			$sqlInsert = "INSERT INTO oc_seo_url
							(store_id,
							language_id,
							query,
							keyword)
						VALUES
							('" . $StoreId . "',
							'" . $LanguageId . "',
							'" . $SEOQuery . "',
							'" . $SEOKeyword . "'
							)";
			$resultInsert = DB_query_oc($sqlInsert,$ErrMsg,$DbgMsg,true);
		}
	}
}

function UpdateOpenCartOrderStatus($OrderId, $StatusId, $Notify, $Carrier, $AWB, $Comment, $db, $db_oc){

	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference($db));

	$SQL = "SELECT powertrack_code
			FROM shippers
			WHERE shipper_id = '".$Carrier."'
			ORDER BY shippername";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$CarrierPowerTrack = $myrow['powertrack_code'];
	}else{
		$CarrierPowerTrack = "";
	}

	$DbgMsg = _('The SQL statement that failed was');
	$UpdateErrMsg = _('The SQL to Update OpenCart Order Status failed');
	$sqlUpdate = "UPDATE oc_order
					SET	order_status_id = '" . $StatusId . "',
						date_modified = '" . $ServerNow . "'
				WHERE order_id = '" . $OrderId . "'";
	$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);

	// Insert the status change into the history table
	$sqlInsert = "INSERT INTO oc_order_history
					(order_id,
					order_status_id,
					notify,
					comment,
					powertrack_carrier,
					powertrack_trackcode,
					date_added)
				VALUES
					('" . $OrderId . "',
					'" . $StatusId . "',
					'" . $Notify . "',
					'" . $Comment . "',
					'" . $CarrierPowerTrack . "',
					'" . $AWB . "',
					'" . $ServerNow . "'
					)";
	$resultInsert = DB_query_oc($sqlInsert,$ErrMsg,$DbgMsg,true);

	if ($StatusId == OPENCART_ORDER_STATUS_SHIPPED){
		// Insert the status change into the powertrack table
		$sqlInsert = "INSERT INTO oc_order_history_powertrack
						(order_id,
						order_status_id,
						powertrack_carrier,
						powertrack_trackcode,
						date_added)
					VALUES
						('" . $OrderId . "',
						'" . $StatusId . "',
						'" . $CarrierPowerTrack . "',
						'" . $AWB . "',
						'" . $ServerNow . "'
						)";
		$resultInsert = DB_query_oc($sqlInsert,$ErrMsg,$DbgMsg,true);
	}
}

function UpdateOpenCartOrderPayment($OrderId, $db, $db_oc){

	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference($db));

	$DbgMsg = _('The SQL statement that failed was');
	$UpdateErrMsg = _('The SQL to Update OpenCart Order Payment failed');
	$sqlUpdate = "UPDATE oc_order
					SET	kl_payment_sync_to_weberp = '" . $ServerNow . "'
				WHERE order_id = '" . $OrderId . "'";
	$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);
}

function RoundPriceFromCart($value, $currency){
	switch ($currency){
	case 'AUD':
		$round = 0.01;
		$step = 0;
		break;
	case 'IDR':
		$round = 1;
		$step = 0;
		break;
	case 'USD':
		$round = 0.01;
		$step = 0;
		break;
	case 'EUR':
		$round = 0.01;
		$step = 0;
		break;
	default:
		$round = 1;
		$step = 0;
		break;
	}

	if ($round) {
		$value = round($value / $round) * $round;
	}

	if ($step) {
		$value -= $step;
	}

	return $value;
}

function GetWeberpShippingMethod($OpenCartShippingMethod){

	$SQL = "SELECT shipper_id
			FROM shippers
			WHERE LEFT(UPPER(opencart_text),10) LIKE LEFT ('".strtoupper($OpenCartShippingMethod)."%',10)
			ORDER BY shippername";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$WeberpShipping = $myrow['shipper_id'];
	}else{
		$WeberpShipping = OPENCART_DEFAULT_SHIPVIA;
	}
	return $WeberpShipping;
}

function GetGoogleProductFeedStatus($StockId, $SalesCategory, $Quantity){
	$Status = 0;
	if ((strpos(SALES_CATEGORIES_FOR_GOOGLE_PRODUCT_FEED, $SalesCategory) !== false)
		AND ($Quantity > 0)){
		$Status = 1;
	}
	return $Status;
}

function GetGoogleProductFeedCategory($StockId, $SalesCategory){
	if (isRing($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Rings";
	}elseif (isToeRing($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Rings";
	}elseif (isEarring($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Earrings";
	}elseif (isEarcuff($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Earrings";
	}elseif (isPiercing($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Piercings";
	}elseif (isBracelet($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Bracelets";
	}elseif (isAnklet($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Anklets";
	}elseif (isPendant($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Necklaces";
	}elseif (isNecklace($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Necklaces";
	}elseif (isPlasticBag($StockId)){
		$Category = "Clothing & Accessories > Handbags, Wallets & Cases > Handbags";
	}elseif (isTali($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Necklaces";
	}else{
		$Category = "Clothing & Accessories > Jewellery & Watches";
	}
	return $Category;
}


function CreateTagsForItem($LanguageId, $Description, $LongDescription, $SalesCategoryName){
	$ListOfTags = "";
	$Separator = ", ";
	//create a long string and look for keywords
	$LongText = strtolower($Description . " " . $LongDescription . " " . $SalesCategoryName);
	if ($LanguageId == 1){
		$SQL = "SELECT tagname AS tagtext
				FROM stocktags
				ORDER BY tagname";
	}else{
		$SQL = "SELECT tagnamebahasa AS tagtext
				FROM stocktags
				ORDER BY tagnamebahasa";
	}
	
	$result = DB_query($SQL);
	while ($myrow = DB_fetch_array($result)){
		
		if (StringContainsTag($LongText, $myrow['tagtext'])){
			// we found a tag in the text, so a candidate for tag
			if ((InconsistentTag($ListOfTags, 'earring', $myrow['tagtext'], 'ring')) == FALSE){
				//  but, we must filter inconsistencies
				if ($ListOfTags == ""){
					// the very first one
					$ListOfTags = $myrow['tagtext'];
				}else{
					$ListOfTags = $ListOfTags. $Separator . $myrow['tagtext'];
				}
			}
		}
	}
	return $ListOfTags;
}

function StringContainsTag($HayStack, $Needle){
	$Pos = stripos($HayStack, $Needle);
	$Result = !($Pos === false);
	return $Result;
}

function InconsistentTag($ListOfTags, $ExistingTag, $ProposedTag, $WrongTag){
	return ((StringContainsTag($ListOfTags, $ExistingTag)) AND ($ProposedTag == $WrongTag));
}

function UpdateOpenCartOrderStatusInWeberp($OrderNo, $OpencartOrderStatus){
	$sql = "UPDATE salesorders
			SET klocorderstatus = '" . $OpencartOrderStatus ."'
			WHERE orderno = '" . $OrderNo . "'";
	$ErrMsg =_('Could not update OpenCart Status order in webERP because');
	$result = DB_query($sql,$ErrMsg);
}

function GetOpenCartStatusTextFromCode($StatusId, $db_oc){
	$SQL = "SELECT name
			FROM oc_order_status
			WHERE language_id = '1'
				AND order_status_id = '" . $StatusId . "'";
	$ErrMsg =_('Could not get the Status name in OpenCart because');
	$result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return "Abandoned";
	}	
}

function GetPaymentMethodTextFromCode($PaymentCode){
	if ($PaymentCode == "bank_mandiri"){
		$PaymentMethodText = "TT Mandiri";
	}else if ($PaymentCode == "bank_bca"){
		$PaymentMethodText = "TT BCA";
	}else if ($PaymentCode == "bank_danamon"){
		$PaymentMethodText = "TT Danamon";
	}else if ($PaymentCode == "xenditmandiriva"){
		$PaymentMethodText = "Xendit VA";
	}else if ($PaymentCode == "xenditcc"){
		$PaymentMethodText = "Xendit CC";
	}else if ($PaymentCode == "snap"){
		$PaymentMethodText = "MidTrans";
	}else{
		$PaymentMethodText = "";
	}
	return $PaymentMethodText;
}


function MaintainPackagingImage($ProductId, $KLPackaging, $db_oc){

	if (($KLPackaging != "") AND ($KLPackaging != "NO-PACKAGING")){
		// if the item has assigned a real packaging set...
		$KLPackagingImage = OPENCART_PACKAGING_SET_IMAGE_PATH . $KLPackaging . ".jpg";

		// check if already exists the row with the info. If not, insert it! 
		// search if we already have it
		$SQL = "SELECT product_image_id
				FROM oc_product_image
				WHERE product_id = '" . $ProductId . "'
					AND sort_order = '" . OPENCART_PACKAGING_SET_IMAGE_SORT_ORDER . "'";
		$ErrMsg =_('Could not get the packaging image in Opencart because');
		$result = DB_query_oc($SQL,$ErrMsg);
		if(DB_num_rows($result) != 0){
			// if we have it, we update it
			$myrow = DB_fetch_array($result);
			$ProductImageId = $myrow['product_image_id'];
			
			$DbgMsg = _('The SQL that failed was');
			$ErrMsg = _('The MaintainPackagingImage function failed');
			$sqlUpdate = "UPDATE oc_product_image SET
							image ='" . $KLPackagingImage . "'
						WHERE product_image_id = '" . $ProductImageId . "'";
			$resultUpdate = DB_query_oc($sqlUpdate,$ErrMsg,$DbgMsg,true);
		}else{
			// otherwise we insert it
			$DbgMsg = _('The SQL that failed was');
			$ErrMsg = _('The MaintainPackagingImage function failed');
			$sqlInsert = "INSERT INTO oc_product_image
							(product_id,
							image,
							sort_order)
						VALUES
							('" . $ProductId . "',
							'" . $KLPackagingImage . "',
							'" . OPENCART_PACKAGING_SET_IMAGE_SORT_ORDER . "'
							)";
			$resultInsert = DB_query_oc($sqlInsert,$ErrMsg,$DbgMsg,true);
		}
	}
}

function InsertWebsiteSalesCategory($Stockid, $WebsiteCategory, $Manufacturers_id, $MultipleCategories, $Featured, $UpdateDB, $db){
	if($UpdateDB){
		
		if (!$MultipleCategories){
			// if don't allow an item in multiple sales categories, then delete the existing ones
			$sql =	"DELETE FROM salescatprod 
						WHERE salescatid = '" . $WebsiteCategory . "' 
							AND stockid ='" .  $Stockid . "'";
			$ErrMsg =_('Could not delete the previous website category for the item because');
			$result = DB_query($sql,$ErrMsg);
		}

		$SQLCheck = "SELECT *
				FROM salescatprod
				WHERE salescatprod.stockid = '" . $Stockid . "'
					AND salescatprod.salescatid = '" . $WebsiteCategory . "'";	
		$result = DB_query($SQLCheck);

		if(DB_num_rows($result) == 0){
			$sql = "INSERT INTO salescatprod (
						salescatid ,
						stockid,
						manufacturers_id,
						featured,
						date_created,
						date_updated)
					VALUES (
						'" . $WebsiteCategory . "',
						'" . $Stockid . "',
						'" . $Manufacturers_id . "',
						'" . $Featured . "',
						NOW(),
						NOW())";
			$ErrMsg =_('Could not insert the website category for the item because');
			$result = DB_query($sql,$ErrMsg);
		}			
	}
}

function AssignSalesCategoryToProductInOpenCart($ProductId, $SalesCatId, $OnlyOneSalesCategory, $db_oc){

	if ($OnlyOneSalesCategory){
		// Delete the current product_to_category, as we only accept 1 product_to_category in website
		$Action = "Delete";
		$DeleteErrMsg = _('The SQL to delete Product - Sales Categories in Opencart failed');
		$sqlDelete = "DELETE FROM oc_product_to_category 
					WHERE product_id = '" . $ProductId . "'";
		$resultDelete = DB_query_oc($sqlDelete,$DeleteErrMsg,$DbgMsg,true);
	}

	if (!DataExistsInOpenCart($db_oc, 'oc_product_to_category', 'product_id', $ProductId, 'category_id', $SalesCatId)){
		// If it is not already there... insert it.
		$Action = "Insert";
		$InsertErrMsg = _('The SQL to insert Product - Sales Categories in Opencart failed');
		$sqlInsert = "INSERT INTO oc_product_to_category
						(product_id,
						category_id)
					VALUES
						('" . $ProductId . "',
						'" . $SalesCatId . "'
						)";
		$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
	}
}


?>

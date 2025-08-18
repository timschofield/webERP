<?php

function Get_SQL_to_PHP_time_difference() {
	// Based on http://stackoverflow.com/questions/3108591/calculate-number-of-hours-between-2-dates-in-php
    $NowPHP = new DateTime();

	$SQL = "SELECT NOW()";
	$Result = DB_query($SQL);
	$Row = DB_fetch_row($Result);
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

function Get_SQL_OC_to_PHP_time_difference() {
	// Based on http://stackoverflow.com/questions/3108591/calculate-number-of-hours-between-2-dates-in-php
    $NowPHP = new DateTime();

	$SQL = "SELECT NOW()";
	$Result = DB_query_oc($SQL);
	$Row = DB_fetch_row($Result);
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

function PrintTimeInformation() {
	$TimeDifference = Get_SQL_to_PHP_time_difference();
	$Text = 'Server time difference: ' . $TimeDifference . "\n" .
			'Server time now: ' . GetServerTimeNow($TimeDifference) . "\n".
			'webERP time now: ' . date('d/M/Y H:i:s') . "\n\n";
	return $Text;
}

function CheckLastTimeRun($Script){
	if ($Script == 'OpenCartToWeberp'){
		$ConfigName = 'OpenCartToWeberp_LastRun';
	}elseif ($Script == 'WeberpToOpenCartHourly'){
		$ConfigName = 'WeberpToOpenCartHourly_LastRun';
	}elseif ($Script == 'WeberpToOpenCartDaily'){
		$ConfigName = 'WeberpToOpenCartDaily_LastRun';
	}
	$SQL = "SELECT confvalue
			FROM config
			WHERE confname = '". $ConfigName ."'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0){
		return  "2999-12-31"; // Error, so we will not change anything
	} else {
		$MyRow = DB_fetch_array($Result);
		return  $MyRow['confvalue'];
	}
}

function SetLastTimeRun($Script){
	if ($Script == 'OpenCartToWeberp'){
		// Updating from OC to webERP: Check the time zone used in OC DB 
		$ServerNow = GetServerTimeNow(Get_SQL_OC_to_PHP_time_difference());
		$_SESSION['OpenCartToWeberp_LastRun'] = $ServerNow;
		$SQL = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'OpenCartToWeberp_LastRun'";
	}elseif ($Script == 'WeberpToOpenCartHourly'){
		// Updating from webERP to OC: Check the time zone used in webERP DB 
		$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());
		$_SESSION['WeberpToOpenCartHourly_LastRun'] = $ServerNow;
		$SQL = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'WeberpToOpenCartHourly_LastRun'";
	}elseif ($Script == 'WeberpToOpenCartDaily'){
		// Updating from webERP to OC: Check the time zone used in webERP DB 
		$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());
		$_SESSION['WeberpToOpenCartDaily_LastRun'] = $ServerNow;
		$SQL = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'WeberpToOpenCartDaily_LastRun'";
	}
	$ErrMsg =__('Could not update Last Run Time of this script because');
	DB_query($SQL,$ErrMsg);
}

function DataExistsInOpenCart($Table, $f1, $v1, $f2 = '', $v2 = ''){
	if ($f2 == ''){
		/* Primary key is 1 field only */
		$SQL = "SELECT COUNT(*)
				FROM " . $Table . "
				WHERE " . $f1 . " = '" . $v1 . "'";
	}else{
		/* Primary key is 2 fields */
		$SQL = "SELECT COUNT(*)
				FROM " . $Table . "
				WHERE " . $f1 . " = '" . $v1 . "'
					AND " . $f2 . " = '" . $v2 . "'";
	}
	$ErrMsg =__('Could not check existence of data in OpenCart because');
	$Result = DB_query_oc($SQL,$ErrMsg);

	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Exists = ($MyRow[0] > 0);
	}else{
		$Exists = false;
	}
	return $Exists;
}

function GetOpenCartProductId($model){
	$SQL = "SELECT product_id
			FROM oc_product
			WHERE model = '" . $model . "'";
	$ErrMsg =__('Could not get the ProductId in OpenCart because');
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return '';
	}
}

function GetManufacturerFromProductId($ProductId){
	$SQL = "SELECT manufacturer_id
			FROM oc_product
			WHERE product_id = '" . $ProductId . "'";
	$ErrMsg =__('Could not get the ManufacturerId in OpenCart because');
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return '';
	}
}


function GetOpenCartLanguageId($language){
	$SQL = "SELECT language_id
			FROM oc_language
			WHERE locale LIKE '%" . $language . "%'";
	$ErrMsg =__('Could not get the LanguageId in OpenCart because');
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return '';
	}
}

function GetWeberpCustomerIdFromEmail($email){
	$SQL = "SELECT debtorno
			FROM custbranch
			WHERE email = '" . $email . "'";
	$ErrMsg =__('Could not get the CustomerId in webERP because');
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return '';
	}
}

function GetWeberpComissionFlatDOKU(){
	$SQL = "SELECT comissionflatdoku
			FROM locations, klonlinepartners
			WHERE locations.onlinepartnercode = klonlinepartners.onlinepartnercode
				AND locations.loccode = " . CODE_ONLINE_SHOP . "";
	$ErrMsg ='Could not get the Commission Flat DOKU in webERP because';
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Com = $MyRow['comissionflatdoku'];
	}else{
		$Com = 0;
	}
	return $Com;
}

function GetWeberpComissionCCDOKU(){
	$SQL = "SELECT comissionccdoku
			FROM locations, klonlinepartners
			WHERE locations.onlinepartnercode = klonlinepartners.onlinepartnercode
				AND locations.loccode = " . CODE_ONLINE_SHOP . "";
	$ErrMsg ='Could not get the Commission CC DOKU in webERP because';
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Com = $MyRow['comissionccdoku'];
	}else{
		$Com = 0;
	}
	return $Com;
}

function GetWeberpCustomerIdFromCurrency($Currency){
	return WEBERP_ONLINE_RETAIL_CUSTOMER_CODE_PREFIX . $Currency;
}

function GetWeberpCustomerIdFromCustomerGroupAndCurrency($CustomerGroup, $Currency){
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

function GetWeberpForeignCurrencySurchargeFactor($Location){
	$SQL = "SELECT foreigncurrencysurchargefactor
			FROM locations, klonlinepartners
			WHERE locations.onlinepartnercode = klonlinepartners.onlinepartnercode
				AND locations.loccode = '" . $Location . "'";
	$ErrMsg ='Could not get the online Foreign Currency Surcharge factor in webERP because';
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Factor = $MyRow['foreigncurrencysurchargefactor'];
	}else{
		$Factor = 1;
	}
	return $Factor;
}

function GetWeberpGLAccountPayPalFromCustomer($CustomerCode){

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
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		if($Currency == "AUD"){
			$GLAccount = $MyRow['accountpaypalaud'];
		}elseif($Currency == "USD"){
			$GLAccount = $MyRow['accountpaypalusd'];
		}elseif($Currency == "EUR"){
			$GLAccount = $MyRow['accountpaypaleur'];
		}elseif($Currency == "IDR"){
			$GLAccount = $MyRow['accountdokuidr'];
		}
	}else{
		$GLAccount = '';
	}
	// in Paypal there is no IDR yet, so we pay by bank trasnfer and record payment manually in webERP
	return $GLAccount;
}

function GetWeberpGLCommissionAccountPayPalFromCustomer($CustomerCode){
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
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		if($Currency == "AUD"){
			$GLAccount = $MyRow['accountpaypalcomissionaud'];
		}elseif($Currency == "USD"){
			$GLAccount = $MyRow['accountpaypalcomissionusd'];
		}elseif($Currency == "EUR"){
			$GLAccount = $MyRow['accountpaypalcomissioneur'];
		}elseif($Currency == "IDR"){
			$GLAccount = $MyRow['accountdokucomissionidr'];
		}
	}else{
		$GLAccount = '';
	}
	// in Paypal there is no IDR yet, so we pay by bank trasnfer and record payment manually in webERP
	return $GLAccount;
}

function GetWeberpOrderNo($CustomerId, $OrderId){
	$SQL = "SELECT orderno
			FROM salesorders
			WHERE debtorno = '" . $CustomerId . "'
				AND branchcode = '" . $CustomerId . "'
				AND customerref = '" . $OrderId . "'";
	$ErrMsg =__('Could not get the OrderNo in webERP because');
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return '';
	}
}

function GetOnlineOrderNoFromWeberp($OrderId){
	$SQL = "SELECT customerref
			FROM salesorders
			WHERE orderno = '" . $OrderId . "'";
	$ErrMsg =__('Could not get the Online Order No in webERP because');
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return '';
	}
}

function GetWeberpCustomerCurrency($CustomerId){
	$SQL = "SELECT currcode
			FROM debtorsmaster
			WHERE debtorno = '" . $CustomerId . "'";
	$ErrMsg =__('Could not get the CustomerCurrency in webERP because');
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return '';
	}
}

function GetWeberpCurrencyRate($CurrencyCode){
	$SQL = "SELECT rate
			FROM currencies
			WHERE currabrev = '" . $CurrencyCode . "'";
	$ErrMsg =__('Could not get the Currency Rate in webERP because');
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return '';
	}
}

function GetTotalTitleFromOrder($Concept, $OrderId){
	$SQL = "SELECT title
			FROM oc_order_total
			WHERE order_id = '" . $OrderId . "'
				AND code = '" . $Concept . "'";
	$ErrMsg =__('Could not get the '. $Concept . ' title from OpenCart because');
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return 0;
	}
}

function GetTotalFromOrder($Concept, $OrderId){
	$SQL = "SELECT SUM(value)
			FROM oc_order_total
			WHERE order_id = '" . $OrderId . "'
				AND code = '" . $Concept . "'";
	$ErrMsg =__('Could not get the '. $Concept . ' total from OpenCart because');
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return 0;
	}
}

function ItemOnlineQOH($StockID){
	$SQL = "SELECT SUM(locstock.quantity)
			FROM locstock, locations
			WHERE locstock.loccode = locations.loccode
				AND locstock.stockid = '" . $StockID . "'
				AND locations.stockavailableforonline = '1'";
	$ErrMsg =__('Could not get the QOH available in webERP for OpenCart because');
	$Result = DB_query($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return 0;
	}
}

function GetOnlinePriceList(){
	$SQL = "SELECT debtorsmaster.salestype
			FROM debtorsmaster
			WHERE debtorsmaster.debtorno = '" . WEBERP_ONLINE_RETAIL_CUSTOMER_CODE_PREFIX . OPENCART_DEFAULT_CURRENCY . "'";
	$Result = DB_query($SQL);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return array($MyRow['salestype'], OPENCART_DEFAULT_CURRENCY);
	}else{
		return array(0,0);
	}
}

function GetDiscount($DiscountCategory, $Quantity, $PriceList){
	/* Select the disount rate from the discount Matrix */
	$Result = DB_query("SELECT MAX(discountrate) AS discount
						FROM discountmatrix
						WHERE salestype='" .  $PriceList . "'
						AND discountcategory ='" . $DiscountCategory . "'
						AND quantitybreak <= '" .$Quantity ."'");
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]==NULL){
		$DiscountMatrixRate = 0;
	} else {
		$DiscountMatrixRate = $MyRow[0];
	}
	return $DiscountMatrixRate;
}

function MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList ){
	$CustomerGroupId = 1; // Retail Customers
	$Priority = 1;
	$ManufacturerId = GetManufacturerFromProductId($ProductId);

	if ($DiscountCategory == ''){
		// ProductId has no discount in webERP
		// so we delete it in OpenCart
		$SQL = "DELETE FROM oc_" . WEBERP_DISCOUNTS_IN_OPENCART_TABLE . "
				WHERE product_id = '" . $ProductId . "'";
		$DeleteErrMsg = __('The SQL to delete the product discount in Opencart table ') . ' ' . WEBERP_DISCOUNTS_IN_OPENCART_TABLE . ' ' . ('failed');
		DB_query_oc($SQL,$DeleteErrMsg,'',true);
	}else{
		// ProductId has some discount in webERP
		// so replicate all the discounts in OpenCart
		$SQL = "SELECT quantitybreak,
						discountrate
				FROM discountmatrix
				WHERE salestype = '" . $PriceList . "'
					AND discountcategory = '" . $DiscountCategory . "'
				ORDER BY quantitybreak";
		$ErrMsg =__('Could not get the discount matrix in webERP because');
		$Result = DB_query($SQL,$ErrMsg);
		if(DB_num_rows($Result) != 0){
			while ($MyRow = DB_fetch_array($Result)){
				$DiscountedPrice = round($Price * (1 - $MyRow['discountrate']),0);
				UpdateDiscountInOpenCart($ProductId, $CustomerGroupId, $MyRow['quantitybreak'], $Priority, $DiscountedPrice);
				// Now we add the item to the category discount 
				if ($ManufacturerId == 1){
					AssignSalesCategoryToProductInOpenCart($ProductId, KL_OUTLET, false);
				}else{
					AssignSalesCategoryToProductInOpenCart($ProductId, BLINK_OUTLET, false);
				}
			}
		}
	}
}

function UpdateDiscountInOpenCart($ProductId, $CustomerGroupId, $Quantity, $Priority, $DiscountedPrice){
	if (WEBERP_DISCOUNTS_IN_OPENCART_TABLE == 'product_discount'){
		/* use the table product_discount */
		$SQL = "SELECT product_discount_id
				FROM oc_product_discount
				WHERE product_id = '" . $ProductId . "'
					AND quantity = '" . $Quantity . "'
					AND customer_group_id = '" . $CustomerGroupId ."'";

		$ErrMsg =__('Could not get the product discount in OpenCart because');
		$Result = DB_query_oc($SQL,$ErrMsg);
		if(DB_num_rows($Result) != 0){
			// There is already a discount, so we need to update it
			$SQL = "UPDATE oc_product_discount
					SET quantity = '" . $Quantity . "',
						priority = '" . $Priority . "',
						price = '" . $DiscountedPrice . "'
					WHERE product_id = '" . $ProductId . "'
						AND quantity = '" . $Quantity . "'
						AND customer_group_id = '" . $CustomerGroupId ."'";
			$UpdateErrMsg = __('The SQL to update the product discount in Opencart failed');
			DB_query_oc($SQL,$UpdateErrMsg,'',true);
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
			$InsertErrMsg = __('The SQL to insert the product discount in Opencart failed');
			DB_query_oc($SQL,$InsertErrMsg,'',true);
		}
	}else{
		/* use the table product_special */
		$SQL = "SELECT product_special_id
				FROM oc_product_special
				WHERE product_id = '" . $ProductId . "'
					AND customer_group_id = '" . $CustomerGroupId ."'";

		$ErrMsg =__('Could not get the product special in OpenCart because');
		$Result = DB_query_oc($SQL,$ErrMsg);
		if(DB_num_rows($Result) != 0){
			// There is already a special, so we need to update it
			$SQL = "UPDATE oc_product_special
					SET priority = '" . $Priority . "',
						price = '" . $DiscountedPrice . "'
					WHERE product_id = '" . $ProductId . "'
						AND customer_group_id = '" . $CustomerGroupId ."'";
			$UpdateErrMsg = __('The SQL to update the product special in Opencart failed');
			DB_query_oc($SQL,$UpdateErrMsg,'',true);
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
			$InsertErrMsg = __('The SQL to insert the product special in Opencart failed');
			DB_query_oc($SQL,$InsertErrMsg,'',true);
		}
	}
}

function GetOpenCartSettingId($Store, $Code, $Key){
	$SQL = "SELECT setting_id
			FROM oc_setting
			WHERE store_id = '" . $Store . "'
				AND `code` = '" . $Code . "'
				AND `key` = '" . $Key . "'";
	$ErrMsg =__('Could not get the SettingId in OpenCart because');
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return 0;
	}
}


function UpdateSettingValueOpenCart($SettingId, $Value){
	$UpdateErrMsg = __('The SQL to update setting value in Opencart failed');
	$SQLUpdate = "UPDATE oc_setting
					SET	value = '" . $Value . "'
				WHERE setting_id = '" . $SettingId . "'";
	DB_query_oc($SQLUpdate,$UpdateErrMsg,'',true);
}

function CreateMetaDescriptionSalesCategory($Group, $Item){
	$MetaDescription = $Group . ' ' . $Item;
	return $MetaDescription;
}

function CreateMetaDescriptionItem($StockID, $Text){
	$MetaDescription = $StockID . " " . CleanText($Text);
	return $MetaDescription;
}

function CreateMetaTitleItem($StockID, $Name, $Separator){
	$MetaTitle = $StockID . $Separator . $Name;
	return $MetaTitle;
}

function CreateMetaKeywordItem($StockID, $StoreName, $Tag, $TagSeparator){
	$MetaKeyword = $StockID . $TagSeparator . $StoreName . $TagSeparator . $Tag;
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
    $MessedText = str_ireplace("\'", '', $MessedText);
    $MessedText = str_ireplace("'", '', $MessedText);
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
	if (ItemInList($webERPCategoryId, LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_ALL_DISCOUNT)
		OR ($ManufacturerId == 1)){
		$ItemBrand = "KL";
	}elseif (ItemInList($webERPCategoryId, LIST_STOCK_CATEGORIES_BLINK_INCLUDING_ALL_DISCOUNT)
		OR ($ManufacturerId == 2)){
		$ItemBrand = "BL";
	}elseif (ItemInList($webERPCategoryId, LIST_STOCK_CATEGORIES_GENERAL_INCLUDING_ALL_DISCOUNT)){
		$ItemBrand = "GE";
	}else{
		// should never happen
		$ItemBrand = "KL";
	}
	return $ItemBrand;
}

Function GetNextSequenceNo ($TransType){

	/* SQL to get the next transaction number these are maintained in the table SysTypes - Transaction Types
	Also updates the transaction number

	10 sales invoice
	11 sales credit note
	12 sales receipt
	etc	*/
	
	DB_query("SELECT typeno FROM systypes WHERE typeid='" . $TransType ."' FOR UPDATE");
	$SQL = "UPDATE systypes SET typeno = typeno + 1 WHERE typeid = '" . $TransType . "'";
	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The transaction number could not be incremented');
	DB_query($SQL,$ErrMsg);
	$SQL = "SELECT typeno FROM systypes WHERE typeid= '" . $TransType . "'";
	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . __('The next transaction number could not be retrieved from the database because');
	$GetTransNoResult = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_row($GetTransNoResult);
	return $MyRow[0];	
}

function InsertCustomerReceipt ($CustomerCode, $AmountPaid, $FreightCost, $CustomerCurrency, $Rate, $BankAccount, $PaymentSystem, $TransactionID, $OrderNo, $PeriodNo) {

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
									'" . $PaymentSystem . __(' OC Payment') . "')";

	$ErrMsg = __('The customer receipt cannot be added because');
	DB_query($HeaderSQL,$ErrMsg,'');

	$SQL = "UPDATE debtorsmaster
				SET lastpaiddate = CURRENT_DATE,
				lastpaid='" . $AmountPaid ."'
			WHERE debtorsmaster.debtorno='" . $CustomerCode . "'";

	$ErrMsg = __('Cannot update the customer record for the date of the last payment received because');
	DB_query($SQL, $ErrMsg, '', true);

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
			'" . __('OC Receipt') . ' ' . $CustomerCode . ' ' . $TransactionID  . "',
			'" . $Rate / $FunctionalRate  . "',
			'" . $FunctionalRate . "',
			CURRENT_DATE,
			'" . $PaymentSystem . ' ' . __('online') . "',
			'" . ($AmountPaid * $Rate / $FunctionalRate) . "',
			'" . $CustomerCurrency . "'
		)";
	$ErrMsg = __('Cannot insert a bank transaction');
	DB_query($SQL, $ErrMsg, '', true);


	/* then enter GLTrans records for discount, bank and debtors */
	/* Bank account entry first */
	$Narrative = $CustomerCode . ' ' . __('payment for order') . ' ' . $OrderNo . ' ' . __('Transaction ID') . ': ' . $TransactionID;
	$SQL="INSERT INTO gltrans (	type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
			VALUES (12,
					'" . $CustomerReceiptNo . "',
					CURRENT_DATE,
					'" . $PeriodNo . "',
					'" . $BankAccount . "',
					'" . mb_substr($Narrative, 0, 200) . "',
					'" . ($AmountPaid) /$Rate . "'
				)";
	$ErrMsg = __('Cannot insert a GL transaction for the bank account debit');
	DB_query($SQL, $ErrMsg, '', true);

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
						CURRENT_DATE,
						'" . $PeriodNo . "',
						'". $_SESSION['CompanyRecord']['debtorsact'] . "',
						'" . mb_substr($Narrative, 0, 200) . "',
						'" . -(($AmountPaid) /$Rate). "' )";
	$ErrMsg = __('Cannot insert a GL transaction for the debtors account credit');
	DB_query($SQL, $ErrMsg, '', true);
	EnsureGLEntriesBalanceOpenCart(12,$CustomerReceiptNo);
}

function EnsureGLEntriesBalanceOpenCart($TransType, $TransTypeNo) {
	/*Ensures general ledger entries balance for a given transaction */
	$Result = DB_query("SELECT SUM(amount)
						FROM gltrans
						WHERE type = '" . $TransType . "'
						AND typeno = '" . $TransTypeNo . "'");
	$MyRow = DB_fetch_row($Result);
	$Difference = $MyRow[0];
	if (abs($Difference)!=0){
		if (abs($Difference)>0.1){
//			message_log(__('The general ledger entries created do not balance. See your system administrator'),'error');
		} else {
			$Result = DB_query("SELECT counterindex,
										MAX(amount)
								FROM gltrans
								WHERE type = '" . $TransType . "'
								AND typeno = '" . $TransTypeNo . "'
								GROUP BY counterindex");
			$MyRow = DB_fetch_array($Result);
			$TransToAmend = $MyRow['counterindex'];
			$Result = DB_query("UPDATE gltrans SET amount = amount - " . $Difference . "
								WHERE counterindex = '" . $TransToAmend . "'");

		}
	}
}

function TransactionCommissionGL ($CustomerCode, $BankAccount, $CommissionAccount, $Commission, $Currency, $Rate, $PaymentSystem, $TransactionID, $PeriodNo) {

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
							'" . $PaymentSystem . ' ' . __('Transaction Fees') . ' ' . $CustomerCode . ' ' . $TransactionID  . "',
							'" . $Rate / $FunctionalRate  . "',
							'" . $FunctionalRate . "',
							CURRENT_DATE,
							'" . $PaymentSystem . ' ' . __('Transaction Fees') . "',
							'" . -($Commission * $Rate / $FunctionalRate) . "',
							'" .$Currency . "'
						)";
	$ErrMsg = __('Cannot insert a bank transaction');
	DB_query($SQL, $ErrMsg, '', true);

	/* Bank account entry first */
	$Narrative = $CustomerCode . ' ' . $PaymentSystem . ' ' . __('Fees for Transaction ID') . ': ' . $TransactionID;
	$SQL="INSERT INTO gltrans (	type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
			VALUES (1,
					'" . $PaymentNo . "',
					CURRENT_DATE,
					'" . $PeriodNo . "',
					'" . $BankAccount . "',
					'" . mb_substr($Narrative, 0, 200) . "',
					'" . -($Commission /$Rate) . "'
				)";
	$ErrMsg = __('Cannot insert a GL transaction for the bank account debit');
	DB_query($SQL, $ErrMsg, '', true);

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
						CURRENT_DATE,
						'" . $PeriodNo . "',
						'". $CommissionAccount . "',
						'" . mb_substr($Narrative, 0, 200) . "',
						'" . ($Commission /$Rate). "' )";
	$ErrMsg = __('Cannot insert a GL transaction for the debtors account credit');
	DB_query($SQL, $ErrMsg, '', true);
	EnsureGLEntriesBalanceOpenCart(1,$PaymentNo);
}

function ChangeOrderQuotationFlag($OrderNo, $Flag){
	$ErrMsg = __('The Change of quotation flag in salesorders table');
	$SQLUpdate = "UPDATE salesorders
					SET quotation = " . $Flag . "
					WHERE orderno = '" . $OrderNo . "'";
	DB_query($SQLUpdate,$ErrMsg,'',true);
}

function GetPaypalReturnDataInArray($RawData){
	$ResponseArray = Array();
	$MainArray = explode(',', str_replace(array('{', '}', '"'), "", $RawData));
	foreach ($MainArray as $i => $Value) {
		$TmpArray = explode(':', $Value);
		if(sizeof($TmpArray) > 1) {
			$ResponseArray[$TmpArray[0]] = $TmpArray[1];
		}
	}
	return $ResponseArray;
}

function MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, $StoreId, $LanguageId){
	// only work on SEO URL if we are on "Insert" action, as "Update" will lead to 404 errors from Google Bots and
	// links created before the update moficiation (as the new link will be different and old ones will not be found.
	if ($Action == "Insert"){
		// search if we already have it
		$SQL = "SELECT seo_url_id
				FROM oc_seo_url
				WHERE query = '" . $SEOQuery . "'
					AND store_id = '" . $StoreId . "'
					AND language_id = '" .  $LanguageId . "'";
		$ErrMsg =__('Could not get the SEO URL in Opencart because');
		$Result = DB_query_oc($SQL,$ErrMsg);
		if(DB_num_rows($Result) != 0){
			// if we have it, we update it
			$MyRow = DB_fetch_array($Result);
			$SeoUrlId = $MyRow['seo_url_id'];
			
			$ErrMsg = __('The MaintainSeoUrl function failed');
			$SQLUpdate = "UPDATE oc_seo_url SET
							keyword ='" . $SEOKeyword . "'
						WHERE seo_url_id = '" . $SeoUrlId . "'";
			DB_query_oc($SQLUpdate,$ErrMsg,'',true);
		}else{
			// otherwise we insert it
			$ErrMsg = __('The MaintainSeoUrl function failed');
			$SQLInsert = "INSERT INTO oc_seo_url
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
			DB_query_oc($SQLInsert,$ErrMsg,'',true);
		}
	}
}

function UpdateOpenCartOrderStatus($OrderId, $StatusId, $Notify, $Carrier, $AWB, $Comment){

	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());

	$SQL = "SELECT powertrack_code
			FROM shippers
			WHERE shipper_id = '".$Carrier."'
			ORDER BY shippername";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$CarrierPowerTrack = $MyRow['powertrack_code'];
	}else{
		$CarrierPowerTrack = "";
	}

	$UpdateErrMsg = __('The SQL to Update OpenCart Order Status failed');
	$SQLUpdate = "UPDATE oc_order
					SET	order_status_id = '" . $StatusId . "',
						date_modified = '" . $ServerNow . "'
				WHERE order_id = '" . $OrderId . "'";
	DB_query_oc($SQLUpdate,$UpdateErrMsg,'',true);

	// Insert the status change into the history table
	$ErrMsg = __('The SQL to Insert OpenCart Order Status failed');
	$SQLInsert = "INSERT INTO oc_order_history
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
	DB_query_oc($SQLInsert,$ErrMsg,'',true);

	if ($StatusId == OPENCART_ORDER_STATUS_SHIPPED){
		// Insert the status change into the powertrack table
		$SQLInsert = "INSERT INTO oc_order_history_powertrack
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
		DB_query_oc($SQLInsert,$ErrMsg,'',true);
	}
}

function UpdateOpenCartOrderPayment($OrderId){

	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());

	$UpdateErrMsg = __('The SQL to Update OpenCart Order Payment failed');
	$SQLUpdate = "UPDATE oc_order
					SET	kl_payment_sync_to_weberp = '" . $ServerNow . "'
				WHERE order_id = '" . $OrderId . "'";
	DB_query_oc($SQLUpdate,$UpdateErrMsg,'',true);
}

function RoundPriceFromCart($Value, $Currency){
	switch ($Currency){
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
		$Value = round($Value / $round) * $round;
	}

	if ($step) {
		$Value -= $step;
	}

	return $Value;
}

function GetWeberpShippingMethod($OpenCartShippingMethod){

	$SQL = "SELECT shipper_id
			FROM shippers
			WHERE LEFT(UPPER(opencart_text),10) LIKE LEFT ('".strtoupper($OpenCartShippingMethod)."%',10)
			ORDER BY shippername";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$WeberpShipping = $MyRow['shipper_id'];
	}else{
		$WeberpShipping = OPENCART_DEFAULT_SHIPVIA;
	}
	return $WeberpShipping;
}

function GetGoogleProductFeedStatus($StockID, $SalesCategory, $Quantity){
	$Status = 0;
	if ((strpos(SALES_CATEGORIES_FOR_GOOGLE_PRODUCT_FEED, $SalesCategory) !== false)
		AND ($Quantity > 0)){
		$Status = 1;
	}
	return $Status;
}

function GetGoogleProductFeedCategory($StockID, $SalesCategory){
	if (isRing($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Rings";
	}elseif (isToeRing($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Rings";
	}elseif (isEarring($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Earrings";
	}elseif (isEarcuff($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Earrings";
	}elseif (isPiercing($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Piercings";
	}elseif (isBracelet($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Bracelets";
	}elseif (isAnklet($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Anklets";
	}elseif (isPendant($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Necklaces";
	}elseif (isNecklace($StockID)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Necklaces";
	}elseif (isPlasticBag($StockID)){
		$Category = "Clothing & Accessories > Handbags, Wallets & Cases > Handbags";
	}elseif (isTali($StockID)){
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
	
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)){
		
		if (StringContainsTag($LongText, $MyRow['tagtext'])){
			// we found a tag in the text, so a candidate for tag
			if ((InconsistentTag($ListOfTags, 'earring', $MyRow['tagtext'], 'ring')) == false){
				//  but, we must filter inconsistencies
				if ($ListOfTags == ""){
					// the very first one
					$ListOfTags = $MyRow['tagtext'];
				}else{
					$ListOfTags = $ListOfTags. $Separator . $MyRow['tagtext'];
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
	$SQL = "UPDATE salesorders
			SET klocorderstatus = '" . $OpencartOrderStatus ."'
			WHERE orderno = '" . $OrderNo . "'";
	$ErrMsg =__('Could not update OpenCart Status order in webERP because');
	DB_query($SQL,$ErrMsg);
}

function GetOpenCartStatusTextFromCode($StatusId){
	$SQL = "SELECT name
			FROM oc_order_status
			WHERE language_id = '1'
				AND order_status_id = '" . $StatusId . "'";
	$ErrMsg =__('Could not get the Status name in OpenCart because');
	$Result = DB_query_oc($SQL,$ErrMsg);
	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
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


function MaintainPackagingImage($ProductId, $KLPackaging){

	if (($KLPackaging != "") AND ($KLPackaging != "NO-PACKAGING")){
		// if the item has assigned a real packaging set...
		$KLPackagingImage = OPENCART_PACKAGING_SET_IMAGE_PATH . $KLPackaging . ".jpg";

		// check if already exists the row with the info. If not, insert it! 
		// search if we already have it
		$SQL = "SELECT product_image_id
				FROM oc_product_image
				WHERE product_id = '" . $ProductId . "'
					AND sort_order = '" . OPENCART_PACKAGING_SET_IMAGE_SORT_ORDER . "'";
		$ErrMsg =__('Could not get the packaging image in Opencart because');
		$Result = DB_query_oc($SQL,$ErrMsg);
		if(DB_num_rows($Result) != 0){
			// if we have it, we update it
			$MyRow = DB_fetch_array($Result);
			$ProductImageId = $MyRow['product_image_id'];
			
			$ErrMsg = __('The MaintainPackagingImage function failed');
			$SQLUpdate = "UPDATE oc_product_image SET
							image ='" . $KLPackagingImage . "'
						WHERE product_image_id = '" . $ProductImageId . "'";
			DB_query_oc($SQLUpdate,$ErrMsg,'',true);
		}else{
			// otherwise we insert it
			$ErrMsg = __('The MaintainPackagingImage function failed');
			$SQLInsert = "INSERT INTO oc_product_image
							(product_id,
							image,
							sort_order)
						VALUES
							('" . $ProductId . "',
							'" . $KLPackagingImage . "',
							'" . OPENCART_PACKAGING_SET_IMAGE_SORT_ORDER . "'
							)";
			DB_query_oc($SQLInsert,$ErrMsg,'',true);
		}
	}
}

function InsertWebsiteSalesCategory($StockID, $WebsiteCategory, $Manufacturers_id, $MultipleCategories, $Featured, $UpdateDB){
	if($UpdateDB){
		
		if (!$MultipleCategories){
			// if don't allow an item in multiple sales categories, then delete the existing ones
			$SQL =	"DELETE FROM salescatprod 
						WHERE salescatid = '" . $WebsiteCategory . "' 
							AND stockid ='" .  $StockID . "'";
			$ErrMsg =__('Could not delete the previous website category for the item because');
			$Result = DB_query($SQL,$ErrMsg);
		}

		$SQLCheck = "SELECT *
				FROM salescatprod
				WHERE salescatprod.stockid = '" . $StockID . "'
					AND salescatprod.salescatid = '" . $WebsiteCategory . "'";	
		$Result = DB_query($SQLCheck);

		if(DB_num_rows($Result) == 0){
			$SQL = "INSERT INTO salescatprod (
						salescatid ,
						stockid,
						manufacturers_id,
						featured,
						date_created,
						date_updated)
					VALUES (
						'" . $WebsiteCategory . "',
						'" . $StockID . "',
						'" . $Manufacturers_id . "',
						'" . $Featured . "',
						NOW(),
						NOW())";
			$ErrMsg =__('Could not insert the website category for the item because');
			$Result = DB_query($SQL,$ErrMsg);
		}			
	}
}

function AssignSalesCategoryToProductInOpenCart($ProductId, $SalesCatId, $OnlyOneSalesCategory){

	if ($OnlyOneSalesCategory){
		// Delete the current product_to_category, as we only accept 1 product_to_category in website
		$DeleteErrMsg = __('The SQL to delete Product - Sales Categories in Opencart failed');
		$SQLDelete = "DELETE FROM oc_product_to_category 
					WHERE product_id = '" . $ProductId . "'";
		DB_query_oc($SQLDelete,$DeleteErrMsg,'',true);
	}

	if (!DataExistsInOpenCart('oc_product_to_category', 'product_id', $ProductId, 'category_id', $SalesCatId)){
		// If it is not already there... insert it.
		$InsertErrMsg = __('The SQL to insert Product - Sales Categories in Opencart failed');
		$SQLInsert = "INSERT INTO oc_product_to_category
						(product_id,
						category_id)
					VALUES
						('" . $ProductId . "',
						'" . $SalesCatId . "'
						)";
		DB_query_oc($SQLInsert,$InsertErrMsg,'',true);
	}
}


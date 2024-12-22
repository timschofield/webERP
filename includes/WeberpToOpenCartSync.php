<?php

function WeberpToOpenCartDailySync($ShowMessages , $EmailText=''){
	$begintime = time_start();

	DB_Txn_Begin();

	// check last time we run this script, so we know which records need to update from OC to webERP
	$LastTimeRun = CheckLastTimeRun('WeberpToOpenCartDaily');
	if ($ShowMessages){
		$TimeDifference = Get_SQL_to_PHP_time_difference();
		prnMsg('This script was last run on: ' . $LastTimeRun . ' Server time difference: ' . $TimeDifference,'success');
		prnMsg('Server time now: ' . GetServerTimeNow($TimeDifference) ,'success');
	}
	if ($EmailText!=''){
		$EmailText = $EmailText . 'webERP to OpenCart Daily Sync was last run on: ' . $LastTimeRun .  "\n" .
					PrintTimeInformation();
	}

	// update currencies
	$EmailText = SyncCurrencies($ShowMessages, $LastTimeRun , $EmailText);

	// maintain outlet category in webERP
	// Not needed because now in weberp one item only belongs to 1 sales category, so no chance to have more than one to clean up
//	$EmailText = MaintainWeberpOutletSalesCategories($ShowMessages, $LastTimeRun , $EmailText);

	// do all hourly maintenance as well...
	$EmailText = WeberpToOpenCartHourlySync($ShowMessages , FALSE, $EmailText);

	// recreate the list of featured in OpenCart
// NOT READY FOR OC v3.0, OC_SETTING IS DIFFERENT
//	$EmailText = SyncFeaturedList($ShowMessages, $LastTimeRun , $EmailText);

	// update sales categories
//	$EmailText = SyncSalesCategories($ShowMessages, $LastTimeRun , $EmailText);

	// activate / inactivate categories depending on items No items = inactive. Items = Active
//	$EmailText = ActivateCategoryDependingOnQOH($ShowMessages, $LastTimeRun , $EmailText);

	// maintain the outlet category in a special way (both webERP and OC)
//	$EmailText = MaintainOpenCartOutletSalesCategories($ShowMessages, $LastTimeRun , $EmailText);

	// assign multiple images to products
	$EmailText = SyncMultipleImages($ShowMessages, $LastTimeRun , $EmailText);

	// assign related items
//	$EmailText = SyncRelatedItems($ShowMessages, $LastTimeRun , $EmailText);

	// We are done!
	SetLastTimeRun('WeberpToOpenCartDaily');
	DB_Txn_Commit();
	if ($ShowMessages){
		time_finish($begintime);
	}

	return $EmailText;
}

function WeberpToOpenCartHourlySync($ShowMessages , $ControlTx = TRUE, $EmailText=''){
	$begintime = time_start();
	if ($ControlTx){
		DB_Txn_Begin();
	}
	// check last time we run this script, so we know which records need to update from OC to webERP
	$LastTimeRun = CheckLastTimeRun('WeberpToOpenCartHourly');
	if ($ShowMessages){
		$TimeDifference = Get_SQL_to_PHP_time_difference();
		prnMsg('This script was last run on: ' . $LastTimeRun . ' Server time difference: ' . $TimeDifference,'success');
		prnMsg('Server time now: ' . GetServerTimeNow($TimeDifference) ,'success');
	}
	if (($EmailText!='') AND ControlTx){
		$EmailText = $EmailText . 'webERP to OpenCart Hourly Sync was last run on: ' . $LastTimeRun .  "\n" .
					PrintTimeInformation();
	}
	// update product basic information
	$EmailText = SyncProductBasicInformation($ShowMessages, $LastTimeRun , $EmailText);

	// update product prices
	$EmailText = SyncProductPrices($ShowMessages, $LastTimeRun , $EmailText);

	// update stock in hand
	$EmailText = SyncProductQOH($ShowMessages, $LastTimeRun , $EmailText);
	
	// update links for marketplaces
	$EmailText = SyncProductMarketplacesLinks($ShowMessages, $LastTimeRun , $EmailText);

	// update product - sales categories relationship
	$EmailText = SyncProductSalesCategories($ShowMessages, $LastTimeRun , $EmailText);

	// Purge Any Product left with Discount over 50%. This happens somethimes when products move from 50% to 80% discount.
	$EmailText = PurgeDiscountOver50($ShowMessages, $LastTimeRun , $EmailText);

	// update description translations
	$EmailText = SyncProductDescriptionTranslations($ShowMessages, $LastTimeRun , $EmailText);

	// clean duplicated URL alias
//	$EmailText = CleanDuplicatedUrlAlias($ShowMessages, $LastTimeRun , $EmailText);

	// We are done!
	SetLastTimeRun('WeberpToOpenCartHourly');
	if ($ControlTx){
		DB_Txn_Commit();
	}
	if ($ShowMessages){
		time_finish($begintime);
	}

	return $EmailText;
}

function SyncProductBasicInformation($ShowMessages, $LastTimeRun , $EmailText= ''){
	$i = 0;
	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());
	$Today = date('Y-m-d');
	$TagSeparator = ", ";

	if ($EmailText !=''){
		$EmailText = $EmailText . "Basic Product Information" . "\n" . PrintTimeInformation();
	}

	/* let's get the webERP price list and base currency for the online customer */
	list ($PriceList, $Currency) = GetOnlinePriceList();

	/* Look for all stockid that have been modified lately */
	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.longdescription,
				stockmaster.discontinued,
				stockmaster.grossweight,
				stockmaster.length,
				stockmaster.width,
				stockmaster.height,
				stockmaster.unitsdimension,
				stockmaster.klpackaging,
				stockmaster.categoryid,
				stockmaster.discountcategory,
				salescatprod.salescatid,
				salescat.salescatname,
				salescatprod.manufacturers_id
			FROM stockmaster, salescatprod, salescat
			WHERE stockmaster.stockid = salescatprod.stockid
				AND stockmaster.klsynctoopencart = '1'
				AND salescatprod.salescatid = salescat.salescatid
				AND ((stockmaster.date_created >= '" . $LastTimeRun . "'	OR stockmaster.date_updated >= '" . $LastTimeRun . "')
					OR (salescatprod.date_created >= '" . $LastTimeRun . "'	OR salescatprod.date_updated >= '" . $LastTimeRun . "'))
			ORDER BY stockmaster.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Product Basic Info') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('QOH') . '</th>
								<th>' . _('Basic Price') . '</th>
								<th>' . _('Store') . '</th>
								<th>' . _('Tag') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Basic Product Information in Opencart failed');
		$InsertErrMsg = _('The SQL to insert Basic Product Information in Opencart failed');

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
			}
			/* Field Matching */
			$Model = $myrow['stockid'];
			$SKU = $myrow['stockid'];
			$MPN = $myrow['stockid'];
			$UPC = '';
			$EAN = '';
			$JAN = '';
			$ISBN = '';
			$Location = '';
			$Quantity = ItemOnlineQOH($myrow['stockid']);
			$StockStatusId = 5; // Out of stock by default

			$Image = PATH_OPENCART_IMAGES . $myrow['stockid'].'.jpg';
			$ManufacturerId = $myrow['manufacturers_id'];
			$Shipping = 1; // will need function depending if it's a shippable or not item
			$CustomerCode = WEBERP_ONLINE_RETAIL_CUSTOMER_CODE_PREFIX . OPENCART_DEFAULT_CURRENCY;
			$Price = GetPrice($myrow['stockid'], $CustomerCode, $CustomerCode); // Get the price without any discount from webERP
			$DiscountCategory = $myrow['discountcategory'];
			$ItemCategory = $myrow['category_id'];
			$Points = 0; // No points concept in webERP
			$TaxClassId = 0; // Not sure how to link stockid and tax in webERP
			$DateAvailable = $ServerNow;
			$Weight = $myrow['grossweight'];
			$WeightClassId = 1; //In OpenCart grossweight is always in Kg.

			if ($myrow['unitsdimension'] == 'mm'){
				$FactorLenght = 10;
			}elseif ($myrow['unitsdimension'] == 'cm'){
				$FactorLenght = 1;
			}else{
				// should be meter
				$FactorLenght = 0.1;
			}
			$Length = $myrow['length']/$FactorLenght; 
			$Width = $myrow['width']/$FactorLenght; 
			$Height = $myrow['height']/$FactorLenght; 
			$LenghtClassId = 1; // Store in OC in cm
			
			$Subtract = 1;
			$Minimum = 1;
			$SortOrder = 1;
			if ($myrow['discontinued'] == 0){
				/* It's a current item */
				if ($Quantity > 0){
					/* It's current and we have stock available, should be available in website */
					$Status = 1;
				}else{
					/* It's current but we don't have stock available, should not be available in website */
					$Status = 0;
				}
			}else{
				/* It's an obsolete item, not available in website */
				$Status = 0;
			}

			if (($DiscountCategory == 80) OR ($ItemCategory == "DISC8A")){
				/* It's a Outlet 80% discount item, we have to disable it! */
					$Status = 0;
			}

			$Viewed = 0;

			$LanguageId = 1; // webERP and OpenCart should have the same default language
			$Name = $myrow['description'];
			$webERPCategoryId = $myrow['categoryid'];
			$LongDescription = FormatDescriptionOpencart($myrow['longdescription']); 
			
			$ItemBrand = GetWeberpItemBrand($webERPCategoryId, $ManufacturerId);
			
			if ($ItemBrand == "KL"){
				$StoreId = OPENCART_STORE_KAPAL_LAUT;
				$StoreText = "KL";
				$StoreName = META_STORE_NAME_KL;
				$GoogleBrand = GOOGLE_BRAND_KL;
			}elseif ($ItemBrand == "BL"){
				$StoreId = OPENCART_STORE_BLINK;
				$StoreText = "Blink";
				$StoreName = META_STORE_NAME_BL;
				$GoogleBrand = GOOGLE_BRAND_BLINK;
			}elseif ($ItemBrand == "GE"){
				// it's a general item, so we assign first to KL.
				$StoreId = OPENCART_STORE_KAPAL_LAUT;
				$StoreText = "KL";
				$StoreName = META_STORE_NAME_KL;
				$GoogleBrand = GOOGLE_BRAND_KL;
			}
			
			/* Meta data */
			$Tag = CreateTagsForItem(1, $myrow['description'], $myrow['longdescription'], $myrow['salescatname']);
			$MetaKeyword = CreateMetaKeywordItem($myrow['stockid'], $StoreName, $Tag, $TagSeparator);
			$MetaDescription = CreateMetaDescriptionItem($myrow['stockid'], $myrow['longdescription']);
			$MetaTitle = CreateMetaTitleItem($myrow['stockid'], $Name, " ");

			/* Google Product Feed Fields */
			$GPFStatus = GetGoogleProductFeedStatus($myrow['stockid'], $myrow['salescatid'], $Quantity);
			$GoogleProductCategory = GetGoogleProductFeedCategory($myrow['stockid'], $myrow['salescatid']);
			$GoogleGender = GOOGLE_GENDER;
			$GoogleAgeGroup = GOOGLE_AGEGROUP;
			$GoogleCondition = GOOGLE_CONDITION;
			$GoogleOosStatus = GOOGLE_OOS_STATUS;
			$GoogleIdentifier = GOOGLE_IDENTIFIER;
			
			/* Now, insert it or update it */
			if (DataExistsInOpenCart('oc_product', 'model', $myrow['stockid'])){
				$Action = "Update";
				// Let's get the OpenCart primary key for product
				$ProductId = GetOpenCartProductId($Model);

				$sqlUpdate = "UPDATE oc_product SET
								sku = '" . $SKU . "',
								mpn = '" . $MPN . "',
								image = '" . $Image . "',
								status = '" . $Status . "',
								quantity = '" . $Quantity . "',
								manufacturer_id = '" . $ManufacturerId . "',
								weight = '" . $Weight . "',
								length = '" . $Length . "',
								width = '" . $Width . "',
								height = '" . $Height . "',
								length_class_id = '" . $LenghtClassId . "'
							WHERE product_id = '" . $ProductId . "'";
				$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);

				$sqlUpdate = "UPDATE oc_product_description SET
								name = '" . $Name . "',
								description = '" . $LongDescription . "',
								meta_description = '" . $MetaDescription . "',
								meta_title = '" . $MetaTitle . "',
								meta_keyword = '" . $MetaKeyword . "',
								tag = '" . $Tag . "'
							WHERE product_id = '" . $ProductId . "'
								AND language_id = '" . $LanguageId . "'";
				$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);

				$sqlUpdate = "UPDATE oc_product_to_store SET
								store_id = '" . $StoreId . "'
							WHERE product_id = '" . $ProductId . "'";
				$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);

			}else{
				$Action = "Insert";

				$sqlInsert = "INSERT INTO oc_product
								(model,
								sku,
								upc,
								ean,
								jan,
								isbn,
								mpn,
								location,
								quantity,
								stock_status_id,
								image,
								manufacturer_id,
								shipping,
								price,
								points,
								tax_class_id,
								date_available,
								weight,
								weight_class_id,
								length,
								width,
								height,
								length_class_id,
								subtract,
								minimum,
								sort_order,
								status,
								viewed,
								date_added,
								date_modified)
							VALUES
								('" . $Model . "',
								'" . $SKU . "',
								'" . $UPC . "',
								'" . $EAN . "',
								'" . $JAN . "',
								'" . $ISBN . "',
								'" . $MPN . "',
								'" . $Location . "',
								'" . $Quantity . "',
								'" . $StockStatusId . "',
								'" . $Image . "',
								'" . $ManufacturerId . "',
								'" . $Shipping . "',
								'" . $Price . "',
								'" . $Points . "',
								'" . $TaxClassId . "',
								'" . $DateAvailable . "',
								'" . $Weight . "',
								'" . $WeightClassId . "',
								'" . $Length . "',
								'" . $Width . "',
								'" . $Height . "',
								'" . $LenghtClassId . "',
								'" . $Subtract . "',
								'" . $Minimum . "',
								'" . $SortOrder . "',
								'" . $Status . "',
								'" . $Viewed . "',
								'" . $ServerNow . "',
								'" . $ServerNow . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);

				// Let's get the OpenCart primary key for product
				$ProductId = GetOpenCartProductId($Model);

				$sqlInsert = "INSERT INTO oc_product_description
								(product_id,
								language_id,
								name,
								description,
								meta_description,
								meta_title,
								meta_keyword,
								tag)
							VALUES
								('" . $ProductId . "',
								'" . $LanguageId . "',
								'" . $Name . "',
								'" . $LongDescription . "',
								'" . $MetaDescription . "',
								'" . $MetaTitle . "',
								'" . $MetaKeyword . "',
								'" . $Tag . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);

				$sqlInsert = "INSERT INTO oc_product_to_store
								(product_id,
								store_id)
							VALUES
								('" . $ProductId . "',
								'" . $StoreId . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);

				$SortOrder++;
			}

			// create discounts if needed
			MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList );
			
			/* IF It is an Outlet 20% or 50% item, we have to mark it as category outlet in Opencart*/
			if (($DiscountCategory == 20) OR (ItemInLIst($ItemCategory, LIST_STOCK_CATEGORIES_DISCOUNT_20))
				OR ($DiscountCategory == 50) OR (ItemInLIst($ItemCategory, LIST_STOCK_CATEGORIES_DISCOUNT_50))){
				if ($ItemBrand == "KL"){
					$SalesCatId = 129; // Category Outlet-Discount Kapal-Laut
				}elseif ($ItemBrand == "BL"){
					$SalesCatId = 128; // Category Outlet-Discount Blink
				}elseif ($ItemBrand == "GE"){
					// it's a general item, so we assign to KL.
					$SalesCatId = 129; // Category Outlet-Discount Kapal-Laut
				}
				AssignSalesCategoryToProductInOpenCart($ProductId, $SalesCatId, FALSE);
			}
			
			/* Assign access rights to the right customer groups. */
			if (($DiscountCategory == 20) OR (ItemInLIst($ItemCategory, LIST_STOCK_CATEGORIES_DISCOUNT_20))
				OR ($DiscountCategory == 50) OR (ItemInLIst($ItemCategory, LIST_STOCK_CATEGORIES_DISCOUNT_50))){
				/* if it a 20% or 50% discounted item, can be seen by all customer groups*/
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_GUEST);
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_RETAIL);
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE_NO_MINIMUM);
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE);
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE_ONLY_DISCOUNTED);
			}elseif (($DiscountCategory == 80) OR (ItemInLIst($ItemCategory, LIST_STOCK_CATEGORIES_DISCOUNT_80))){
				/* it is a 80% discount items, should not be available to anyone. Being strict it is not needed
				as it is marked as disabled, but to keep data consistent, we revoke rights*/
				RevokeAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_GUEST);
				RevokeAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_RETAIL);
				RevokeAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE_NO_MINIMUM);
				RevokeAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE);
				RevokeAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE_ONLY_DISCOUNTED);
			}else{
				/* if it is not a discounted item, it should not be available to wholesale only discounted items*/
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_GUEST);
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_RETAIL);
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE_NO_MINIMUM);
				AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE);
				RevokeAcessRightsProductsToCustomerGroupInOpenCart($ProductId, OPENCART_CUSTOMER_GROUP_WHOLESALE_ONLY_DISCOUNTED);
			}

			// create SEO Keywords if needed
			$SEOQuery = 'product_id='.$ProductId;
			$SEOKeyword = CreateSEOKeyword($Model . "-" . $Name);
			MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, $StoreId, $LanguageId);
			
			// maintain the packaging image
			MaintainPackagingImage($ProductId, $myrow['klpackaging']);
			
			// if it's a general item, we have to add it too to Blink store.
			if  (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_GENERAL) AND
			   (!DataExistsInOpenCart('oc_product_to_store', 'product_id', $ProductId, 'store_id', OPENCART_STORE_BLINK))){
				$sqlInsert = "INSERT INTO oc_product_to_store
								(product_id,
								store_id)
							VALUES
								('" . $ProductId . "',
								'" . OPENCART_STORE_BLINK . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
				$StoreText = $StoreText . " + BL";
				MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, OPENCART_STORE_BLINK, $LanguageId);
			}

			// if it's not on the wholesale store, we add it.
			if (!DataExistsInOpenCart('oc_product_to_store', 'product_id', $ProductId, 'store_id', OPENCART_STORE_WHOLESALE)){
				$sqlInsert = "INSERT INTO oc_product_to_store
								(product_id,
								store_id)
							VALUES
								('" . $ProductId . "',
								'" . OPENCART_STORE_WHOLESALE . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
				$StoreText = $StoreText . " + WH";
				MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, OPENCART_STORE_WHOLESALE, $LanguageId);
			}
			
			if ($ShowMessages){
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$Model,
						$Name,
						locale_number_format($Quantity,0),
						locale_number_format($Price,0),
						$StoreText,
						$Tag,
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " = " . $Name. " --> " . $Action . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Products synchronized from webERP to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Product Basic Info synchronized from webERP to OpenCart') . "\n\n";
	}

	return $EmailText;
}

function SyncProductSalesCategories($ShowMessages, $LastTimeRun , $EmailText= ''){
	$i = 0;

	if ($EmailText !=''){
		$EmailText = $EmailText . "Product - Sales Categories" . "\n" . PrintTimeInformation();
	}

	/* Look for the late modifications of salescatprod table in webERP */
	$SQL = "SELECT salescatprod.salescatid,
				salescatprod.stockid,
				salescatprod.manufacturers_id,
				salescatprod.featured
			FROM salescatprod
			WHERE (salescatprod.date_created >= '" . $LastTimeRun . "'
					OR salescatprod.date_updated >= '" . $LastTimeRun . "')
			ORDER BY salescatprod.salescatid, salescatprod.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Product - Sales Categories') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('Sales Category') . '</th>
								<th>' . _('Manufacturer Id') . '</th>
								<th>' . _('Featured') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {

			/* Field Matching */
			$Model = $myrow['stockid'];
			$SalesCatId = $myrow['salescatid'];
			$ManufacturerId = $myrow['manufacturers_id'];
			$Featured = $myrow['featured'];
			if($Featured == 1){
				$PrintFeatured = "Yes";
			}else{
				$PrintFeatured = "No";
			}
			
			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model);
			
			AssignSalesCategoryToProductInOpenCart($ProductId, $SalesCatId, FALSE);

			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$Model,
						$SalesCatId,
						$ManufacturerId,
						$PrintFeatured,
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " --> " . $SalesCatId. " --> " . $Action . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Products to Sales Categories synchronized from webERP to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Product - Sales Categories synchronized from webERP to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function SyncProductPrices($ShowMessages, $LastTimeRun , $EmailText = ''){
	$i = 0;

	if ($EmailText !=''){
		$EmailText = $EmailText . "Product Price Sync" . "\n" . PrintTimeInformation();
	}

	/* let's get the webERP price list and base currency for the online customer */
	list ($PriceList, $Currency) = GetOnlinePriceList();

	/* Look for the late modifications of prices table in webERP */
	$SQL = "SELECT prices.stockid,
				stockmaster.discountcategory
			FROM prices, stockmaster
			WHERE prices.stockid = stockmaster.stockid
				AND stockmaster.klsynctoopencart = '1'
				AND prices.typeabbrev ='" . $PriceList . "'
				AND prices.currabrev ='" . $Currency . "'
				AND (prices.date_created >= '" . $LastTimeRun . "'
					OR prices.date_updated >= '" . $LastTimeRun . "')
			ORDER BY prices.stockid ASC,
					prices.startdate ASC,
					prices.enddate ASC";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Product Prices Updates') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('New Price') . '</th>
								<th>' . _('Discount Category') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product Prices in Opencart failed');

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);

			/* Field Matching */
			$Model = $myrow['stockid'];
			$CustomerCode = WEBERP_ONLINE_RETAIL_CUSTOMER_CODE_PREFIX . OPENCART_DEFAULT_CURRENCY;
			$Price = GetPrice ($myrow['stockid'], $CustomerCode, $CustomerCode); // Get the price without any discount from webERP
			$DiscountCategory = $myrow['discountcategory'];

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model);

			$Action = "Update";
			$sqlUpdate = "UPDATE oc_product SET
							price = '" . $Price . "'
						WHERE product_id = '" . $ProductId . "'";
			$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);

			// update discounts if needed
			MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList );
			if ($ShowMessages){
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$Model,
						locale_number_format($Price,0),
						$DiscountCategory,
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " = " . locale_number_format($Price,0). " = " . $DiscountCategory . " --> " . $Action . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Product Prices synchronized from webERP to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Product Prices synchronized from webERP to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function SyncProductQOH($ShowMessages, $LastTimeRun , $EmailText=''){
	$i = 0;

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Product QOH" . "\n" . PrintTimeInformation();
	}

	/* Look for the late modifications of locstock table in webERP */
	$SQL = "SELECT DISTINCT(locstock.stockid)
			FROM locstock, salescatprod, locations, stockmaster
			WHERE locstock.stockid = salescatprod.stockid
				AND locstock.stockid = stockmaster.stockid
				AND locstock.loccode = locations.loccode
				AND locations.stockavailableforonline = '1'
				AND stockmaster.klsynctoopencart = '1'
				AND (locstock.date_created >= '" . $LastTimeRun . "'
					OR locstock.date_updated >= '" . $LastTimeRun . "')
			ORDER BY locstock.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Product QOH Updates') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('Online QOH') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product QOH in Opencart failed');

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {

			/* Field Matching */
			$Model = $myrow['stockid'];
			$Quantity = ItemOnlineQOH($myrow['stockid']);
			if ($Quantity > 0){
				$Status = 1;
			}else{
				$Status = 0;
			}

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model);

			$Action = "Update";
			$sqlUpdate = "UPDATE oc_product SET
							quantity = '" . $Quantity . "',
							status = '" . $Status . "'
						WHERE product_id = '" . $ProductId . "'";
			$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);
			
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>',
						$Model,
						locale_number_format($Quantity,0),
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " QOH = " . locale_number_format($Quantity,0) . "\n";
			}
			$i++;
		}
		
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	// Set the status flag to 0 for all items in OC with QOH = 0. 
	$sqlUpdate = "UPDATE oc_product SET
					status = 0
				WHERE quantity = 0";
	$resultUpdate = DB_query_oc($sqlUpdate,"","",true);
	if ($EmailText !=''){
		$EmailText = $EmailText . " Set Status = 0 for all items with QOH = 0" . "\n";
	}
	
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Product QOH synchronized from webERP to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Product QOH synchronized from webERP to OpenCart') . "\n\n";
	}

	return $EmailText;
}

function SyncProductMarketplacesLinks($ShowMessages, $LastTimeRun , $EmailText=''){
	$i = 0;
	if ($EmailText !=''){
		$EmailText = $EmailText . "Enable/Disable Product in Marketplaces based on QOH" . "\n" . PrintTimeInformation();
	}

	/* Look for the late modifications of locstock table in webERP, to see all products that have changed QOH somehow 
		we will need to update the webERP table klstockmarketplaces, later on a second SQL we can update OpenCart properly*/
	$SQL = "SELECT DISTINCT(locstock.stockid), 
				stockmaster.categoryid
			FROM locstock, salescatprod, locations, stockmaster
			WHERE locstock.stockid = salescatprod.stockid
				AND locstock.stockid = stockmaster.stockid
				AND locstock.loccode = locations.loccode
				AND locations.stockavailableforonline = '1'
				AND stockmaster.klsynctoopencart = '1'
				AND (locstock.date_created >= '" . $LastTimeRun . "'
					OR locstock.date_updated >= '" . $LastTimeRun . "')
			ORDER BY locstock.stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Product QOH Available for Marketplaces Updates') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('Marketplace QOH') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product QOH in Opencart failed');

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
				// discounted items are not enabled in marketplaces
				$Action = "Disable Outlet";
				$EnabledMarketplaces = "0";
			}else{
				// is not discount item, so we can decide depending on QOH
				$QOH = ItemMarketplaceQOH($myrow['stockid']);
				if ($QOH > 0) {
					$Action = "Enable";
					$EnabledMarketplaces = "1";
				}else{
					$Action = "Disable QOH";
					$EnabledMarketplaces = "0";
				}
			}

			ItemEnableTokopediaInfo($myrow['stockid'], $EnabledMarketplaces);
			ItemEnableShopeeInfo($myrow['stockid'], $EnabledMarketplaces);
			ItemEnableLazadaInfo($myrow['stockid'], $EnabledMarketplaces);
			
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>',
						$myrow['stockid'],
						locale_number_format($QOH,0),
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($myrow['stockid'], 20, " ") . " Action = " . $Action . "\n";
			}
			$i++;
		}
		
		if ($ShowMessages){
			echo '</table>
					</div>';
		}
	}
	
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Products set as Enabled/Disabled for Marketplaces in webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Products set as Enabled/Disabled for Marketplaces in webERP') . "\n\n";
	}

	// second round... now update Opencart oc_product_link table with current information
	$i = 0;
	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Product Links to Marketplaces" . "\n" . PrintTimeInformation();
	}

	$SQL = "SELECT stockid,
				tokopediaenabled,
				tokopediaurl,
				shopeeenabled,
				shopeeurl,
				lazadaenabled,
				lazadaurl
			FROM klstockmarketplaces
			WHERE (klstockmarketplaces.date_created >= '" . $LastTimeRun . "'
					OR klstockmarketplaces.date_updated >= '" . $LastTimeRun . "')
			ORDER BY stockid";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Product Links to Marketplaces Updates') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('Tokopedia Enabled') . '</th>
								<th>' . _('Shopee Enabled') . '</th>
								<th>' . _('Lazada Enabled') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product Links to marketplaces in Opencart failed');
		$InsertErrMsg = _('The SQL to insert Product Links to marketplaces in Opencart failed');

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {

			/* Field Matching */
			$Model = $myrow['stockid'];
			$TokopediaEnabled = $myrow['tokopediaenabled'];
			$TokopediaLink = ClearUrl($myrow['tokopediaurl']);
			$ShopeeEnabled = $myrow['shopeeenabled'];
			$ShopeeLink = ClearUrl($myrow['shopeeurl']);
			$LazadaEnabled = $myrow['lazadaenabled'];
			$LazadaLink = ClearUrl($myrow['lazadaurl']);
			
			$Link = '{"1":{"status":"';
			if ($TokopediaEnabled){
				$Link .= '1';
				$TextTokopediaEnabled = "Enabled";
			}else{
				$Link .= '0';
				$TextTokopediaEnabled = "Disabled";
			}
			$Link .= '","link":"';
			$Link .= $TokopediaLink;
			$Link .= '"},'; // Closing the Tokopedia info

			$Link .= '"2":{"status":"';
			if ($ShopeeEnabled){
				$Link .= '1';
				$TextShopeeEnabled = "Enabled";
			}else{
				$Link .= '0';
				$TextShopeeEnabled = "Disabled";
			}
			$Link .= '","link":"';
			$Link .= $ShopeeLink;
			$Link .= '"},'; // closing the Shopee info

			$Link .= '"3":{"status":"';
			if ($LazadaEnabled){
				$Link .= '1';
				$TextLazadaEnabled = "Enabled";
			}else{
				$Link .= '0';
				$TextLazadaEnabled = "Disabled";
			}
			$Link .= '","link":"';
			$Link .= $LazadaLink;
			$Link .= '"}'; // closing the Lazada info

			$Link .= '}'; // closing the full link info

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model);

			/* Now, insert it or update it */
			if (DataExistsInOpenCart('oc_product_link', 'product_id', $ProductId)){
				$Action = "Update";

				$sqlUpdate = "UPDATE oc_product_link SET
								product_link = '" . $Link . "'
							WHERE product_id = '" . $ProductId . "'";
				$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);
			}else{
				$Action = "Insert";

				$sqlInsert = "INSERT INTO oc_product_link
								(product_id,
								product_link)
							VALUES
								('" . $ProductId . "',
								'" . $Link . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
			}
			
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$Model,
						$TextTokopediaEnabled,
						$TextShopeeEnabled,
						$TextLazadaEnabled,
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . str_pad($Model, 20, " ") . $Action . " --> " . $Link . "\n";
			}
			$i++;
		}
		
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}

	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Marketplaces links updated to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Marketplaces links updated to OpenCart') . "\n\n";
	}

	return $EmailText;
}

function PurgeDiscountOver50($ShowMessages, $LastTimeRun , $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Purge Products with Discount Over 50%" . "\n" . PrintTimeInformation();
	}

	// if original price is more than 2 times the special price, it means discount > 50%. Set as disabled.
	$sqlUpdate = "UPDATE oc_product, oc_product_special
			SET oc_product.status = 0
			WHERE oc_product.product_id = oc_product_special.product_id
			AND oc_product.price / oc_product_special.price > 2
			AND oc_product.status = 1;";

	$resultUpdate = DB_query_oc($sqlUpdate,"","",true);
	
	if ($ShowMessages){
		prnMsg('Purged Products with Discount Over 50%','success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . " Purged Products with Discount Over 50%" . "\n";
	}

	return $EmailText;
}

function SyncProductDescriptionTranslations($ShowMessages, $LastTimeRun , $EmailText=''){
// UPDATE `kurakura_kl_erp`.`stockdescriptiontranslations` SET `date_updated` = NOW();
	$TagSeparator = ", ";

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Product Description Translations" . "\n" . PrintTimeInformation();
	}

	/* Look for the late modifications of description translations table in webERP */
	$SQL = "SELECT stockmaster.categoryid,
				salescatprod.manufacturers_id,
					stockdescriptiontranslations.stockid,
					stockdescriptiontranslations.language_id,
					stockdescriptiontranslations.descriptiontranslation,
					stockdescriptiontranslations.longdescriptiontranslation,
					stockdescriptiontranslations.needsrevision
			FROM stockdescriptiontranslations, stockmaster, salescatprod
			WHERE stockmaster.stockid = salescatprod.stockid
				AND stockdescriptiontranslations.stockid = stockmaster.stockid
				AND stockmaster.klsynctoopencart = '1'
				AND (stockdescriptiontranslations.date_created >= '" . $LastTimeRun . "'
					OR stockdescriptiontranslations.date_updated >= '" . $LastTimeRun . "'
					OR stockmaster.date_created >= '" . $LastTimeRun . "' 
					OR stockmaster.date_updated >= '" . $LastTimeRun . "')
			ORDER BY stockdescriptiontranslations.stockid";

	$result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Product Description Translations Updates') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('StockID') . '</th>
								<th>' . _('Language') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Long Description') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product Description Translations in Opencart failed');

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {

			/* Field Matching */
			$Model = $myrow['stockid'];
			$webERPCategoryId = $myrow['categoryid'];
			$ManufacturerId = $myrow['manufacturers_id'];

			$ItemBrand = GetWeberpItemBrand($webERPCategoryId, $ManufacturerId);

			if ($ItemBrand == "KL"){
				$StoreId = OPENCART_STORE_KAPAL_LAUT;
				$StoreName = META_STORE_NAME_KL;
			}elseif ($ItemBrand == "BL"){
				$StoreId = OPENCART_STORE_BLINK;
				$StoreName = META_STORE_NAME_BL;
			}elseif ($ItemBrand == "GE"){
				$StoreId = OPENCART_STORE_KAPAL_LAUT;
				$StoreName = META_STORE_NAME_KL;
			}

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model);

			// If the product exists in OpenCart (as in webERP we can have translations for items NOT in Opencart)
			if ($ProductId != ""){

				// Look for the Language of the Translation
				$LanguageId = GetOpenCartLanguageId(mb_substr($myrow['language_id'],0,5));
				
				// If the language exists in OpenCart, when we insert / update the description translation
				if ($LanguageId != ""){

					$Name = $myrow['descriptiontranslation'];
					$LongDescription = FormatDescriptionOpencart($myrow['longdescriptiontranslation']); 
					$Tag = CreateTagsForItem($LanguageId, $myrow['descriptiontranslation'], $myrow['longdescriptiontranslation'], $myrow['salescatname']);
					$MetaKeyword = CreateMetaKeywordItem($myrow['stockid'], $StoreName, $Tag, $TagSeparator);
					$MetaDescription = CreateMetaDescriptionItem($myrow['stockid'], $myrow['longdescriptiontranslation']);
					$MetaTitle = CreateMetaTitleItem($myrow['stockid'], $myrow['descriptiontranslation'], " ");

					if (DataExistsInOpenCart('oc_product_description', 'product_id', $ProductId, 'language_id', $LanguageId )){
						$Action = "Update";
						$sqlUpdate = "UPDATE oc_product_description SET
										name = '" . $Name . "',
										description = '" . $LongDescription . "',
										meta_title = '" . $MetaTitle . "',
										meta_description = '" . $MetaDescription . "',
										meta_keyword = '" . $MetaKeyword . "',
										tag = '" . $Tag . "'
									WHERE product_id = '" . $ProductId . "'
										AND language_id = '" . $LanguageId . "'";
						$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);

					}else{
						$Action = "Insert";
						$sqlInsert = "INSERT INTO oc_product_description
										(product_id,
										language_id,
										name,
										description,
										meta_title,
										meta_description,
										meta_keyword,
										tag)
									VALUES
										('" . $ProductId . "',
										'" . $LanguageId . "',
										'" . $Name . "',
										'" . $LongDescription . "',
										'" . $MetaTitle . "',
										'" . $MetaDescription . "',
										'" . $MetaKeyword . "',
										'" . $Tag . "'
										)";
						$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
					}
	
					// create SEO Keywords if needed
					$SEOQuery = 'product_id='.$ProductId;
					$SEOKeyword = CreateSEOKeyword($Model . "-" . $Name);
					MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, $StoreId, $LanguageId);

					// if it's a general item, we have to add it too to Blink store.
					if ($ItemBrand == "GE"){
						MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, OPENCART_STORE_BLINK, $LanguageId);
					}

					// if it's not on the wholesale store, we add it.
					MaintainSeoUrl($Action, $SEOQuery, $SEOKeyword, OPENCART_STORE_WHOLESALE, $LanguageId);

	
					if ($ShowMessages){
						$k = StartEvenOrOddRow($k);
						printf('<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								</tr>',
								$Model,
								mb_substr($myrow['language_id'],0,5),
								$myrow['descriptiontranslation'],
								$myrow['longdescriptiontranslation'],
								$Action
								);
					}
					if ($EmailText !=''){
						$EmailText = $EmailText . str_pad($Model, 20, " ") . " Description Translations for " . mb_substr($myrow['language_id'],0,5) . "\n";
					}
					$i++;
				}
			}
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Product Description Translations synchronized from webERP to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Product Description Translations synchronized from webERP to OpenCart') . "\n\n";
	}

	return $EmailText;
}

function SyncMultipleImages($ShowMessages, $LastTimeRun , $EmailText = ''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Multiple Images" . "\n" . PrintTimeInformation();
	}

	if ($ShowMessages){
		echo '<p class="page_title_text" align="center"><strong>' . _('Synchronize multiple images per item') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('webERP Code') . '</th>
							<th>' . _('File') . '</th>
						</tr>';
		echo $TableHeader;
	}
//	$SQLTruncate = "TRUNCATE oc_product_image";
//	$resultSQLTruncate = DB_query_oc($SQLTruncate);
	$k = 0; //row colour counter
	$i= 0;
	// get all images in part_pics folder (ideally should be OpenCart images folder...)
	$imagefiles = getDirectoryTree($_SESSION['part_pics_dir']);
	foreach ($imagefiles as $file) {
		$multipleimage = 1;
		$exist_multiple = TRUE;
		while ($multipleimage <= 9){
			$suffix = ".". $multipleimage;
			if (strpos($file, $suffix) !== false){
				// GET stockid from filename
				$StockId = substr($file, 0, strpos($file, $suffix));
				// get Opencart productid
				$ProductId = GetOpenCartProductId($StockId);
				if ($ProductId > 0){
					// insert info about multiple images
					$Image = PATH_OPENCART_IMAGES . $file;
					if (DataExistsInOpenCart("oc_product_image", "product_id", $ProductId, "image", $Image)== FALSE){
						$sqlInsert = "INSERT INTO oc_product_image
										(product_id,
										image,
										sort_order)
									VALUES
										('" . $ProductId . "',
										'" . $Image . "',
										'" . $multipleimage . "')";
						$resultInsert = DB_query_oc($sqlInsert,"","",true);
						if ($ShowMessages){
							$k = StartEvenOrOddRow($k);
							printf('<td>%s</td>
									<td>%s</td>
									</tr>',
									$StockId,
									$Image
									);
						}
						$i++;
					}
				}
			}
			$multipleimage++;
		}
	}
	if ($ShowMessages){
		echo '</table>
				</div>
				</form>';
		prnMsg(locale_number_format($i,0) . ' ' . _('Multiple Images Synchronized'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Multiple Images Synchronized') . "\n\n";
	}
	return $EmailText;
}

function SyncCurrencies($ShowMessages, $LastTimeRun , $EmailText= ''){
	$i = 0;
	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());
	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Currency Exchange Rates" . "\n" . PrintTimeInformation();
	}

	$SQL = "SELECT currabrev,
				currency,
				rate,
				decimalplaces
			FROM currencies
			WHERE webcart = '1'
				AND (date_created >= '" . $LastTimeRun . "'
				OR date_updated >= '" . $LastTimeRun . "')
			ORDER BY currabrev";

	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Currency exchange rates') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('Currency') . '</th>
								<th>' . _('Rate') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Currency Exchange Rates in Opencart failed');
		$InsertErrMsg = _('The SQL to insert Currency Exchange Rates in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {

			/* FIELD MATCHING */
			$Currency = $myrow['currabrev'];
			$Name = $myrow['currency'];
			$DecimalPlaces = $myrow['decimalplaces'];
			if ($myrow['rate'] != 1){
				// foreign currencies
				$Rate = ($myrow['rate'] * GetWeberpForeignCurrencySurchargeFactor(OPENCART_DEFAULT_LOCATION));
			}else{
				// functional currency
				$Rate = 1;
			}
			if (DataExistsInOpenCart('oc_currency', 'code', $Currency)){
				$Action = "Update";
				$sqlUpdate = "UPDATE oc_currency
								SET value 		= '" . $Rate . "',
									date_modified 	= '" . $ServerNow . "'
								WHERE code 	= '" . $Currency . "'";
				$resultUpdate = DB_query_oc($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);
			}else{
				$Action = "Insert";
				$sqlInsert = "INSERT INTO oc_currency
								(title,
								code,
								decimal_place,
								`value`,
								status,
								date_modified)
							VALUES
								('" . $Name . "',
								'" . $Currency . "',
								'" . $DecimalPlaces . "',
								'" . $Rate . "',
								'1',
								'" . $ServerNow . "'
								)";
				$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
			}
			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$Currency,
						$Rate,
						$Action
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $Currency . " = " . $Rate. " --> " . $Action . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Currency exchange rates synchronized from webERP to OpenCart'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Currency exchange rates synchronized from webERP to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function KL_DailyCleanOpenCartDB($ShowMessages , $EmailText=''){
	$begintime = time_start();

	DB_Txn_Begin();

	// clean old coupons
	$EmailText = CleanOldOpenCartCoupons($ShowMessages, 15 , $EmailText);
	// clean old pending orders
	$EmailText = ChangeOldPendingOpenCartOrders($ShowMessages, 2 , $EmailText);
	// Change from shipped to complete
	$EmailText = ChangeOldShippedOpenCartOrders($ShowMessages, 1 , $EmailText);

	DB_Txn_Commit();
	if ($ShowMessages){
		time_finish($begintime);
	}

	return $EmailText;
}

function CleanOldOpenCartCoupons($ShowMessages, $MaxDays , $EmailText= ''){
	$Title = 'Clean old OpenCart Couons expired ' . $MaxDays . ' ago';
	$i = 0;
	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());
	if ($EmailText !=''){
		$EmailText = $EmailText . $Title . "\n" . PrintTimeInformation();
	}
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$MaxDays)) ;

	$SQL = "SELECT coupon_id,
				name,
				code
			FROM oc_coupon
			WHERE date_end <= '" . $StartDate . "'
			ORDER BY coupon_id";

	$result = DB_query_oc($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . $Title  .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('Coupon ID') . '</th>
								<th>' . _('Name') . '</th>
								<th>' . _('Code') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = 'The SQL to update ' . $Title . ' failed';
		$InsertErrMsg = 'The SQL to insert ' . $Title . ' failed';

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {

			/* FIELD MATCHING */
			$CouponId = $myrow['coupon_id'];
			$Name = $myrow['name'];
			$Code = $myrow['code'];
			
			$SQLDelete = "DELETE FROM oc_coupon WHERE coupon_id = '" . $CouponId . "'";

			$resultDelete = DB_query_oc($SQLDelete);

			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$CouponId,
						$Name,
						$Code
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . " Coupon ID = " . $CouponId. " - " . $Name ." - " . $Code . "\n";
			}
		$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . $Title ,'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' .$Title  . "\n\n";
	}
	return $EmailText;
}

function ChangeOldPendingOpenCartOrders($ShowMessages, $MaxDays , $EmailText= ''){
	$Title = 'Change old PENDING OC Orders to EXPIRED';
	$i = 0;
	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());
	if ($EmailText !=''){
		$EmailText = $EmailText . $Title . "\n" . PrintTimeInformation();
	}
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$MaxDays)) ;

	$SQL = "SELECT order_id,
				firstname,
				lastname
			FROM oc_order
			WHERE order_status_id = " . OPENCART_ORDER_STATUS_PENDING . " 
				AND date_modified <= '" . $StartDate . "'
			ORDER BY order_id";

	$result = DB_query_oc($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . $Title  .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('Order ID') . '</th>
								<th>' . _('Name') . '</th>
								<th>' . _('Comment') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = 'The SQL to update ' . $Title . ' failed';
		$InsertErrMsg = 'The SQL to insert ' . $Title . ' failed';

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {

			/* FIELD MATCHING */
			$OrderId = $myrow['order_id'];
			$Name = $myrow['firstname'] . " " . $myrow['lastname'];
			$Comment = "webERP -> EXPIRED: Payment not received in due time.";
			UpdateOpenCartOrderStatus($OrderId, OPENCART_ORDER_STATUS_EXPIRED, 1, "", "", $Comment);

			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$OrderId,
						$Name,
						$Comment
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . " Order ID = " . $OrderId. " --> " . $Comment . "\n";
			}
		$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . $Title ,'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' .$Title  . "\n\n";
	}
	return $EmailText;
}

function ChangeOldShippedOpenCartOrders($ShowMessages, $MaxDays , $EmailText= ''){
	$Title = 'Change old SHIPPED OC Orders to COMPLETE';
	$i = 0;
	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference());
	if ($EmailText !=''){
		$EmailText = $EmailText . $Title . "\n" . PrintTimeInformation();
	}
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$MaxDays)) ;

	$SQL = "SELECT order_id,
				firstname,
				lastname
			FROM oc_order
			WHERE order_status_id = " . OPENCART_ORDER_STATUS_SHIPPED . " 
				AND date_modified <= '" . $StartDate . "'
			ORDER BY order_id";

	$result = DB_query_oc($SQL);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . $Title  .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('Order ID') . '</th>
								<th>' . _('Name') . '</th>
								<th>' . _('Comment') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = 'The SQL to update ' . $Title . ' failed';
		$InsertErrMsg = 'The SQL to insert ' . $Title . ' failed';

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {

			/* FIELD MATCHING */
			$OrderId = $myrow['order_id'];
			$Name = $myrow['firstname'] . " " . $myrow['lastname'];
			$Comment = "webERP -> COMPLETE: Order already shipped and accounted for.";
			UpdateOpenCartOrderStatus($OrderId, OPENCART_ORDER_STATUS_COMPLETE, 1, "", "", $Comment);

			if ($ShowMessages){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$OrderId,
						$Name,
						$Comment
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . " Order ID = " . $OrderId. " --> " . $Comment . "\n";
			}
		$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . $Title ,'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' .$Title  . "\n\n";
	}
	return $EmailText;
}

function AssignAcessRightsProductsToCustomerGroupInOpenCart($ProductId, $CustomerGroupId){
	/* Now, insert it, if it is not there yet*/
	if (!DataExistsInOpenCart('oc_product_to_customer_group', 'product_id', $ProductId, 'customer_group_id', $CustomerGroupId)){
		$DbgMsg = _('The SQL statement that failed was');
		$InsertErrMsg = _('The SQL on fucntion AssignAcessRightsProductsToCustomerGroupInOpenCart failed');
		$sqlInsert = "INSERT INTO oc_product_to_customer_group
						(product_id,
						customer_group_id)
					VALUES
						('" . $ProductId . "',
						'" . $CustomerGroupId . "'
						)";
		$resultInsert = DB_query_oc($sqlInsert,$InsertErrMsg,$DbgMsg,true);
	}
}

function RevokeAcessRightsProductsToCustomerGroupInOpenCart($ProductId, $CustomerGroupId){
	$DbgMsg = _('The SQL statement that failed was');
	$DeleteErrMsg = _('The SQL on fucntion RevokeAcessRightsProductsToCustomerGroupInOpenCart failed');

	/* Now, revoke (delete) the access rights*/
	$SQL = "DELETE FROM oc_product_to_customer_group
			WHERE product_id = '" . $ProductId . "'
				AND customer_group_id = '" . $CustomerGroupId . "'";
				
	$resultDelete = DB_query_oc($SQL,$DeleteErrMsg,$DbgMsg,true);
}

?>
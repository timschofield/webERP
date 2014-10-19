<?php
define("VERSIONFILE", "1.00"); 

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include ('includes/session.inc');
include ('includes/WeberpOpenCartDefines.php');
$Title = _('webERP to OpenCart Synchronizer '. VERSIONFILE);
include ('includes/OpenCartGeneralFunctions.php');
include ('includes/header.inc');
include('includes/GetPrice.inc');

$begintime = time_start();

// connect to opencart DB
include ('includes/OpenCartConnectDB.php');
DB_Txn_Begin($db);

// check last time we run this script, so we know which records need to update from OC to webERP
$LastTimeRun = CheckLastTimeRun('WeberpToOpenCart');
prnMsg('This script was last run on: ' . $LastTimeRun . ' Server time difference: ' . SERVER_TO_LOCAL_TIME_DIFFERENCE,'success');
prnMsg('Server time now: ' . GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE) ,'success');

// update sales categories
SyncSalesCategories($LastTimeRun, $db, $db_oc, $oc_tableprefix);

// update product basic information
SyncProductBasicInformation($LastTimeRun, $db, $db_oc, $oc_tableprefix);

// update product - sales categories relationship
SyncProductSalesCategories($LastTimeRun, $db, $db_oc, $oc_tableprefix);

// recreate the list of featured in OpenCart
SyncFeaturedList($LastTimeRun, $db, $db_oc, $oc_tableprefix);

// update product prices
SyncProductPrices($LastTimeRun, $db, $db_oc, $oc_tableprefix);

// update stock in hand
SyncProductQOH($LastTimeRun, $db, $db_oc, $oc_tableprefix);

// activate / inactivate categories depending on items No items = inactive. Items = Active
ActivateCategoryDependingOnQOH($LastTimeRun, $db, $db_oc, $oc_tableprefix);

// assign multiple images to products
SyncMultipleImages($LastTimeRun, $db, $db_oc, $oc_tableprefix);

// We are done!
SetLastTimeRun('WeberpToOpenCart', $db);
DB_Txn_Commit($db);
time_finish($begintime);

include ('includes/footer.inc');


function SyncSalesCategories($LastTimeRun, $db, $db_oc, $oc_tableprefix){
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);

	$SQL = "SELECT salescatid,
				parentcatid,
				salescatname,
				active
			FROM salescat
			WHERE date_created >= '" . $LastTimeRun . "'
				OR date_updated >= '" . $LastTimeRun . "'
			ORDER BY salescatid";
			
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Sales categories') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('SalesCatID') . '</th>
							<th>' . _('SalesCatName') . '</th>
							<th>' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;

		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update sales categories in Opencart failed');
		$InsertErrMsg = _('The SQL to insert sales categories in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			/* FIELD MATCHING */
			if ($myrow['parentcatid'] == 0){
				$Top = 1;
			}else{
				$Top = 0;
			}
			$StoreId = 0;
			$Column = 1; 
			$Language_Id = 1; // for now NO multi language
			$SortOrder = 1; 
			$Name = trim($myrow['salescatname']);
			$Description = trim($myrow['salescatname']); 
			$MetaDescription = CreateMetaDescription('Sales category', trim($myrow['salescatname'])); 
			$MetaKeyword = CreateMetaKeyword('', trim($myrow['salescatname'])); 
			$CategoryId = $myrow['salescatid'];
			if (DataExistsInOpenCart($db_oc, $oc_tableprefix . 'category', 'category_id', $myrow['salescatid'])){
				$Action = "Update";
				$sqlUpdate = "UPDATE " . $oc_tableprefix . "category
								SET parent_id 		= '" . $myrow['parentcatid'] . "',
									status 			= '" . $myrow['active'] . "',
									top 			= '" . $Top . "',
									date_modified 	= '" . $ServerNow . "'
								WHERE category_id 	= '" . $CategoryId . "'";
				$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);
				
				$sqlUpdate = "UPDATE " . $oc_tableprefix . "category_description
								SET language_id 		= '" . $Language_Id . "',
									name	 			= '" . $Name . "'
								WHERE category_id 	= '" . $CategoryId . "'";
				$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);

				// update SEO Keywords if needed
				$SEOQuery = 'category_id='.$CategoryId; 
				$SEOKeyword = CreateSEOKeyword($Name); 
				MaintainUrlAlias($SEOQuery, $SEOKeyword, $db_oc, $oc_tableprefix);

			}else{
				$Action = "Insert";
				$sqlInsert = "INSERT INTO " . $oc_tableprefix . "category
								(category_id,
								image,
								parent_id,
								top, 		
								`column`,
								sort_order,
								status, 		
								date_added,
								date_modified)
							VALUES
								('" . $CategoryId . "',
								'',
								'" . $myrow['parentcatid'] . "',
								'" . $Top . "',
								'" . $Column . "',
								'" . $SortOrder . "',
								'" . $myrow['active'] . "',
								'" . $ServerNow . "',
								'" . $ServerNow . "'
								)";
				$resultInsert = DB_query($sqlInsert,$db_oc,$InsertErrMsg,$DbgMsg,true);
				$sqlInsert = "INSERT INTO " . $oc_tableprefix . "category_description
								(category_id,
								language_id,
								name, 		
								description,
								meta_description,
								meta_keyword)
							VALUES
								('" . $CategoryId . "',
								'" . $Language_Id . "',
								'" . $Name . "',
								'" . $Description . "',
								'" . $MetaDescription . "',
								'" . $MetaKeyword . "'
								)";
				$resultInsert = DB_query($sqlInsert,$db_oc,$InsertErrMsg,$DbgMsg,true);			
				$sqlInsert = "INSERT INTO " . $oc_tableprefix . "category_to_store
								(category_id,
								store_id)
							VALUES
								('" . $CategoryId . "',
								'" . $StoreId . "'
								)";
				$resultInsert = DB_query($sqlInsert,$db_oc,$InsertErrMsg,$DbgMsg,true);	
				$SortOrder++;

				// insert SEO Keywords if needed
				$SEOQuery = 'category_id='.$CategoryId; 
				$SEOKeyword = CreateSEOKeyword($Name); 
				MaintainUrlAlias($SEOQuery, $SEOKeyword, $db_oc, $oc_tableprefix);

			}
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['salescatid'],
					$Name,
					$Action
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
	if ($i > 0){
		prnMsg('Remind to run Repair Categories on OpenCart!','warn');
	}
	prnMsg(locale_number_format($i,0) . ' ' . _('Sales Categories synchronized from webERP to OpenCart'),'success');
	
}

function SyncProductBasicInformation($LastTimeRun, $db, $db_oc, $oc_tableprefix){
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');
	
	/* let's get the webERP price list and base currency for the online customer */
	list ($PriceList, $Currency) = GetOnlinePriceList($db);
	
	/* Look for all stockid that have been modified lately */
	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.longdescription,
				stockmaster.grossweight,
				stockmaster.length,
				stockmaster.width,
				stockmaster.height,
				stockmaster.unitsdimension,
				stockmaster.discountcategory,
				salescatprod.manufacturers_id
			FROM stockmaster, salescatprod
			WHERE stockmaster.stockid = salescatprod.stockid
				AND ((stockmaster.date_created >= '" . $LastTimeRun . "'	OR stockmaster.date_updated >= '" . $LastTimeRun . "')
					OR (salescatprod.date_created >= '" . $LastTimeRun . "'	OR salescatprod.date_updated >= '" . $LastTimeRun . "'))
			ORDER BY stockmaster.stockid";
			
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Product Basic Info') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('StockID') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('QOH') . '</th>
							<th>' . _('Basic Price') . '</th>
							<th>' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;

		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Basic Product Information in Opencart failed');
		$InsertErrMsg = _('The SQL to insert Basic Product Information in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			/* Field Matching */
			$Model = $myrow['stockid'];
			$SKU = $myrow['stockid'];
			$UPC = '';
			$EAN = '';
			$JAN = '';
			$ISBN = '';
			$MPN = '';
			$Location = '';
			$Quantity = GetOnlineQOH($myrow['stockid'], $db); 
			$StockStatusId = 5; // Out of stock by default
			$Image = PATH_OPENCART_IMAGES . $myrow['stockid'].'.jpg';
			$ManufacturerId = $myrow['manufacturers_id'];
			$Shipping = 1; // will need function depending if it's a shippable or not item 
			$CustomerCode = GetWeberpCustomerIdFromCurrency(OPENCART_DEFAULT_CURRENCY, $db);
			$Price = GetPrice ($myrow['stockid'], $CustomerCode, $CustomerCode, $db); // Get the price without any discount from webERP
			$DiscountCategory = $myrow['discountcategory'];
			$Points = 0; // No points concept in webERP
			$TaxClassId = 0; // Not sure how to link stockid and tax in webERP
			$DateAvailable = $ServerNow; 
			$Weight = $myrow['grossweight'];
			$WeightClassId = 1; //In webERP grossweight is always in Kg.
			$Length = $myrow['length'];
			$Width = $myrow['width'];
			$Height = $myrow['height'];
			$LenghtClassId = GetLenghtClassId($myrow['unitsdimension'], 1, $db_oc, $oc_tableprefix);
			$Subtract = 1;
			$Minimum = 1;
			$SortOrder = 1;
			if ($Quantity > 0){
				$Status = 1;
			}else{
				$Status = 0;
			}
			$Viewed = 0;

			$LanguageId = 1;
			$Name = $myrow['description'];
			$Description =  str_replace("'", "\'", $myrow['longdescription']);
			$MetaDescription = CreateMetaDescription($myrow['stockid'], trim($myrow['description'])); 
			$MetaKeyword = CreateMetaKeyword($myrow['stockid'], trim($myrow['description'])); 
			$Tag = $myrow['description'];
			$StoreId = 0;
			
			if (DataExistsInOpenCart($db_oc, $oc_tableprefix . 'product', 'model', $myrow['stockid'])){
				$Action = "Update";
				// Let's get the OpenCart primary key for product
				$ProductId = GetOpenCartProductId($Model, $db_oc, $oc_tableprefix);
				$sqlUpdate = "UPDATE " . $oc_tableprefix . "product SET 
								sku = '" . $SKU . "',
								manufacturer_id = '" . $ManufacturerId . "',
								weight = '" . $Weight . "',
								length = '" . $Length . "',
								width = '" . $Width . "',
								height = '" . $Height . "',
								length_class_id = '" . $LenghtClassId . "'
							WHERE product_id = '" . $ProductId . "'";
				$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);
				
				$sqlUpdate = "UPDATE " . $oc_tableprefix . "product_description SET 
								name = '" . $Name . "',
								description = '" . $Description . "',
								meta_description = '" . $MetaDescription . "',
								meta_keyword = '" . $MetaKeyword . "',
								tag = '" . $Tag . "'
							WHERE product_id = '" . $ProductId . "'
								AND language_id = '" . $LanguageId . "'";
				$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);

				// update discounts if needed
				MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList, $db, $db_oc, $oc_tableprefix);
				
				// update SEO Keywords if needed
				$SEOQuery = 'product_id='.$ProductId; 
				$SEOKeyword = CreateSEOKeyword($Model . "-" . $Name); 
				MaintainUrlAlias($SEOQuery, $SEOKeyword, $db_oc, $oc_tableprefix);

			}else{
				$Action = "Insert";
				$sqlInsert = "INSERT INTO " . $oc_tableprefix . "product
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
				$resultInsert = DB_query($sqlInsert,$db_oc,$InsertErrMsg,$DbgMsg,true);
				
				// Let's get the OpenCart primary key for product
				$ProductId = GetOpenCartProductId($Model, $db_oc, $oc_tableprefix);

				$sqlInsert = "INSERT INTO " . $oc_tableprefix . "product_description
								(product_id,
								language_id,
								name,
								description,
								meta_description,
								meta_keyword,
								tag)
							VALUES
								('" . $ProductId . "',
								'" . $LanguageId . "',
								'" . $Name . "',
								'" . $Description . "',
								'" . $MetaDescription . "',
								'" . $MetaKeyword . "',
								'" . $Tag . "'
								)";
				$resultInsert = DB_query($sqlInsert,$db_oc,$InsertErrMsg,$DbgMsg,true);	
				
				$sqlInsert = "INSERT INTO " . $oc_tableprefix . "product_to_store
								(product_id,
								store_id)
							VALUES
								('" . $ProductId . "',
								'" . $StoreId . "'
								)";
				$resultInsert = DB_query($sqlInsert,$db_oc,$InsertErrMsg,$DbgMsg,true);	
				
				// create discounts if needed
				MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList, $db, $db_oc, $oc_tableprefix);

				// create SEO Keywords if needed
				$SEOQuery = 'product_id='.$ProductId; 
				$SEOKeyword = CreateSEOKeyword($Model . "-" . $Name); 
				MaintainUrlAlias($SEOQuery, $SEOKeyword, $db_oc, $oc_tableprefix);

				$SortOrder++;
			}
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$Model,
					$Name,
					locale_number_format($Quantity,0),
					locale_number_format($Price,2),
					$Action
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
	prnMsg(locale_number_format($i,0) . ' ' . _('Products synchronized from webERP to OpenCart'),'success');
	
}

function SyncProductSalesCategories($LastTimeRun, $db, $db_oc, $oc_tableprefix){
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');
	
	/* Look for the late modifications of salescatprod table in webERP */
	$SQL = "SELECT salescatprod.salescatid,
				salescatprod.stockid,
				salescatprod.manufacturers_id,
				salescatprod.featured
			FROM salescatprod
			WHERE (salescatprod.date_created >= '" . $LastTimeRun . "'
					OR salescatprod.date_updated >= '" . $LastTimeRun . "')
			ORDER BY salescatprod.salescatid, salescatprod.stockid";
			
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
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

		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product - Sales Categories in Opencart failed');
		$InsertErrMsg = _('The SQL to insert Product - Sales Categories in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
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
			$ProductId = GetOpenCartProductId($Model, $db_oc, $oc_tableprefix);
			
			if (DataExistsInOpenCart($db_oc, $oc_tableprefix . 'product_to_category', 'product_id', $ProductId, 'category_id', $SalesCatId)){
				$Action = "Update";
				$sqlUpdate = "UPDATE " . $oc_tableprefix . "product SET 
								manufacturer_id = '" . $ManufacturerId . "'
							WHERE product_id = '" . $ProductId . "'";
				$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);
			}else{
				$Action = "Insert";
				$sqlInsert = "INSERT INTO " . $oc_tableprefix . "product_to_category
								(product_id,
								category_id)
							VALUES
								('" . $ProductId . "',
								'" . $SalesCatId . "'
								)";
				$resultInsert = DB_query($sqlInsert,$db_oc,$InsertErrMsg,$DbgMsg,true);
			}
			
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
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
	prnMsg(locale_number_format($i,0) . ' ' . _('Products to Sales Categories synchronized from webERP to OpenCart'),'success');
}

function SyncProductPrices($LastTimeRun, $db, $db_oc, $oc_tableprefix){
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	/* let's get the webERP price list and base currency for the online customer */
	list ($PriceList, $Currency) = GetOnlinePriceList($db);
	
	/* Look for the late modifications of prices table in webERP */
	$SQL = "SELECT prices.stockid,
				stockmaster.discountcategory
			FROM prices, stockmaster
			WHERE prices.stockid = stockmaster.stockid
				AND prices.typeabbrev ='" . $PriceList . "'
				AND prices.currabrev ='" . $Currency . "'
				AND (prices.date_created >= '" . $LastTimeRun . "'
					OR prices.date_updated >= '" . $LastTimeRun . "')
			ORDER BY prices.stockid";
			
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
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

		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product Prices in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			/* Field Matching */
			$Model = $myrow['stockid'];
			$CustomerCode = GetWeberpCustomerIdFromCurrency(OPENCART_DEFAULT_CURRENCY, $db);
			$Price = GetPrice ($myrow['stockid'], $CustomerCode, $CustomerCode, $db); // Get the price without any discount from webERP
			$ManufacturerId = $myrow['manufacturers_id'];
			$DiscountCategory = $myrow['discountcategory'];
			
			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model, $db_oc, $oc_tableprefix);
			
			$Action = "Update";
			$sqlUpdate = "UPDATE " . $oc_tableprefix . "product SET 
							price = '" . $Price . "'
						WHERE product_id = '" . $ProductId . "'";
			$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);

			// update discounts if needed
			MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList, $db, $db_oc, $oc_tableprefix);
			
			printf('<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$Model,
					locale_number_format($Price,2),
					$DiscountCategory,
					$Action
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
	prnMsg(locale_number_format($i,0) . ' ' . _('Product Prices synchronized from webERP to OpenCart'),'success');
}

function SyncProductQOH($LastTimeRun, $db, $db_oc, $oc_tableprefix){
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	/* let's get the webERP price list and base currency for the online customer */
	list ($PriceList, $Currency) = GetOnlinePriceList($db);
	
	/* Look for the late modifications of prices table in webERP */
	$SQL = "SELECT DISTINCT(locstock.stockid)
			FROM locstock, salescatprod
			WHERE locstock.stockid = salescatprod.stockid
				AND locstock.loccode IN ('" . str_replace(',', "','", LOCATIONS_WITH_STOCK_FOR_ONLINE_SHOP) . "')
				AND (locstock.date_created >= '" . $LastTimeRun . "'
					OR locstock.date_updated >= '" . $LastTimeRun . "')
			ORDER BY locstock.stockid";
			
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Product QOH Updates') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('StockID') . '</th>
							<th>' . _('Online QOH') . '</th>
							<th>' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;

		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product QOH in Opencart failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			/* Field Matching */
			$Model = $myrow['stockid'];
			$Quantity = GetOnlineQOH($myrow['stockid'], $db); 
			if ($Quantity > 0){
				$Status = 1;
			}else{
				$Status = 0;
			}
			
			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model, $db_oc, $oc_tableprefix);
			
			$Action = "Update";
			$sqlUpdate = "UPDATE " . $oc_tableprefix . "product SET 
							quantity = '" . $Quantity . "',
							status = '" . $Status . "'
						WHERE product_id = '" . $ProductId . "'";
			$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);
			
			printf('<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$Model,
					locale_number_format($Quantity,0),
					$Action
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
	prnMsg(locale_number_format($i,0) . ' ' . _('Product QOH synchronized from webERP to OpenCart'),'success');
}

function SyncFeaturedList($LastTimeRun, $db, $db_oc, $oc_tableprefix){

	/* Let's get the ID for the list of featured products for featured module	
	   we will need it later on to save the results in the appropiate setting */
	$SettingId = GetOpenCartSettingId(0,"featured", "featured_product", $db_oc, $oc_tableprefix);
	$ListFeaturedOpenCart = "";
	
	/* Look for the featured items in webERP 
	we'll recreate the full list everytime as it will be short and
	it's a list that will change quite often */
	$SQL = "SELECT DISTINCT(salescatprod.stockid)
			FROM salescatprod
			WHERE salescatprod.featured ='1'
			ORDER BY salescatprod.stockid";
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Create featured list in OpenCart') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('StockID') . '</th>
							<th>' . _('OpenCartID') . '</th>
							<th>' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;


		$Action = "Added";
		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			/* Field Matching */
			$Model = $myrow['stockid'];
			
			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model, $db_oc, $oc_tableprefix);

			// Let's build the list
			if ($i == 0){
				$ListFeaturedOpenCart = strval($ProductId);	
			}else{
				$ListFeaturedOpenCart = $ListFeaturedOpenCart . "," . strval($ProductId);	
			}
			printf('<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$Model,
					$ProductId,
					$Action
					);
			$i++;
		}
		UpdateSettingValueOpenCart($SettingId, $ListFeaturedOpenCart, $db_oc, $oc_tableprefix);
		echo '</table>
				</div>
				</form>';
	}
	prnMsg(locale_number_format($i,0) . ' ' . _('Products included in the featured list in OpenCart'),'success');
}

function ActivateCategoryDependingOnQOH($LastTimeRun, $db, $db_oc, $oc_tableprefix){
	$SQL = "SELECT salescatid,
				parentcatid,
				salescatname,
				active,
				(SELECT COUNT(locstock.quantity)
					FROM salescatprod,locstock
					WHERE salescat.salescatid = salescatprod.salescatid
						AND salescatprod.stockid = locstock.stockid
						AND locstock.loccode IN ('" . str_replace(',', "','", LOCATIONS_WITH_STOCK_FOR_ONLINE_SHOP) . "')
				) as qoh
			FROM salescat
			WHERE active = 1
				AND parentcatid != 0
			ORDER BY salescatname";
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Activate/Inactivate Categories depending on QOH') .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('Sales Category') . '</th>
							<th>' . _('QOH') . '</th>
							<th>' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;

		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to Activate Categories depending QOH in Opencart failed');
		
		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			/* Field Matching */
			$CategoryId = $myrow['salescatid'];
			$CategoryName = $myrow['salescatname'];
			$CategoryQOH = $myrow['qoh'];
			
			if ($CategoryQOH > 0){
				$Status = 1;
				$Action = "Active";
			}else{
				$Status = 0;
				$Action = "Inactive QOH = 0";
			}

			$sqlUpdate = "UPDATE " . $oc_tableprefix . "category SET 
								status = '" . $Status . "'
							WHERE category_id = '" . $CategoryId . "'";
			$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);
			
			printf('<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$CategoryName,
					locale_number_format($CategoryQOH,0),
					$Action
					);
			$i++;
		}

		echo '</table>
				</div>
				</form>';
	}
	prnMsg(locale_number_format($i,0) . ' ' . _('OpenCart Categories Activated / Inactivated depending on QOH'),'success');
}


function SyncMultipleImages($LastTimeRun, $db, $db_oc, $oc_tableprefix){
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	echo '<p class="page_title_text" align="center"><strong>' . _('Synchronize multiple images per item') .'</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('webERP Code') . '</th>
						<th>' . _('File') . '</th>
					</tr>';
	echo $TableHeader;
	
	$SQLTruncate = "TRUNCATE " . $oc_tableprefix . "product_image";
	$resultSQLTruncate = DB_query($SQLTruncate, $db_oc);

	$k = 0; //row colour counter
	$i= 0;
	// get all images in part_pics folder (ideally should be OpenCart images folder...)
	$imagefiles = getDirectoryTree($_SESSION['part_pics_dir'], 'jpg');
	foreach ($imagefiles as $file) {
		$multipleimage = 1;
		$exist_multiple = TRUE;
		while ($multipleimage <= 5){
			$suffix = ".". $multipleimage;
			if (strpos($file, $suffix) > 0){
				// GET stockid from filename
				$StockId = substr($file, 0, strpos($file, $suffix));
				// get Opencart productid
				$ProductId = GetOpenCartProductId($StockId, $db_oc, $oc_tableprefix);
				if ($ProductId > 0){
					// insert info about multiple images
					$sqlInsert = "INSERT INTO " . $oc_tableprefix . "product_image
									(product_id,
									image,
									sort_order)
								VALUES
									('" . $ProductId . "',
									'" . PATH_OPENCART_IMAGES . $file . "',
									'" . $multipleimage . "')";
					$resultInsert = DB_query($sqlInsert,$db_oc,$InsertErrMsg,$DbgMsg,true);
					if ($k == 1) {
						echo '<tr class="EvenTableRows">';
						$k = 0;
					} else {
						echo '<tr class="OddTableRows">';
						$k = 1;
					}
					printf('<td>%s</td>
							<td>%s</td>
							</tr>', 
							$StockId,
							$file
							);
					$i++;
				}
			}
			$multipleimage++;
		}
	}
	echo '</table>
			</div>
			</form>';
	prnMsg(locale_number_format($i,0) . ' ' . _('Multiple Images Synchronized'),'success');
	
}




?>
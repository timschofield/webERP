<?php

define("VERSIONFILE", "1.08"); 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Kapal-Laut Set Online Shop Categories '. VERSIONFILE);
include ('includes/header.php');
include ('includes/KLDefines.php');
include ('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

$UpdateDB = TRUE;

$begintime = time_start();

// Select items and classify them
$SQL = "SELECT stockmaster.stockid,
			   stockmaster.description,
			   stockmaster.grossweight,
			   stockmaster.volume,
			   stockmaster.klpackaging,
			   stockmaster.longdescription,	
			   stockmaster.categoryid	
		FROM stockmaster
		WHERE " . SQLFilterStockmasterForOnlineShop("KL+BL") . "
			AND ((NOT EXISTS (SELECT * 
								FROM salescatprod
								WHERE stockmaster.stockid = salescatprod.stockid))
					OR stockmaster.grossweight = 0
					OR stockmaster.klpackaging = ''
					OR stockmaster.volume = 0)
		ORDER BY stockmaster.stockid";
$result = DB_query($SQL);
if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Items To Classify for Online Shop Categories') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Stock Category') . '</th>
						<th>' . _('Weight Kg') . '</th>
						<th>' . _('Volume m3') . '</th>
						<th>' . _('Brand') . '</th>
						<th>' . _('Website Category') . '</th>
						<th>' . _('Featured') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	$ItemsAdded = 0;
	while ($myrow = DB_fetch_array($result)) {

		$WebsiteDescription = "";
		$FeaturedAsTopSales = 0;
		$FeaturedText = "";

/* 	KL RICARD 30/08/2013
	Do not feature as top sales. featured depending on promotions...
		if (ItemFeaturedAsTopSale($myrow['stockid'], $myrow['categoryid'], 30, $db)){
			$FeaturedAsTopSales = 1;
			$FeaturedText = "Yes";
		}
*/		
		// Mirar si hem d'actualitzar pes o volum
		$Weight = $myrow['grossweight'];
		if ($Weight == 0){
			$Weight = UpdateWeight($myrow['stockid'], $Weight, $UpdateDB, $db);
		}
		
		$Volume = $myrow['volume'];
		if ($Volume == 0){
			$Volume = UpdateVolume($myrow['stockid'], $UpdateDB, $db);
			UpdatePackaging($myrow['stockid'],$myrow['categoryid'], $UpdateDB, $db);
		}
		
		$Packaging = $myrow['klpackaging'];
		if ($Packaging == ""){
			UpdatePackaging($myrow['stockid'],$myrow['categoryid'], $UpdateDB, $db);
		}
		
		// si tenim descripció prou llarga
		if (strlen($myrow['description']) >= 5){
			// si tenim foto seguim endavant, sino no el publiquem a la website
			if(file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg') ) {

				// Mirar si pertany a super categoria SILVER KL
				$WebsiteCategory = WebsiteCategorySilverJewellery($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
				if ($WebsiteCategory > 0){
					$Brand = FindWebsiteBrand($myrow['stockid'], $myrow['categoryid']);
					InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $Brand, FALSE, $FeaturedAsTopSales, $UpdateDB, $db);
					$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
					$ItemsAdded++;
				}else{
					// Mirar si pertany a super categoria BLINK JEWELLERY
					$WebsiteCategory = WebsiteCategoryBlinkJewellery($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
					if ($WebsiteCategory > 0){
						$Brand = FindWebsiteBrand($myrow['stockid'], $myrow['categoryid']);
						InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $Brand, FALSE, $FeaturedAsTopSales, $UpdateDB, $db);
						$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
						$ItemsAdded++;
					}else{
						// Mirar si pertany a super categoria BAGS
						$WebsiteCategory = WebsiteCategoryBags($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
						if ($WebsiteCategory > 0){
							$Brand = FindWebsiteBrand($myrow['stockid'], $myrow['categoryid']);
							InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $Brand, FALSE, $FeaturedAsTopSales, $UpdateDB, $db);
							$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
							$ItemsAdded++;
						}else{
							$WebsiteDescription = 'NO WEBSITE CATEGORY';
							$WebsiteCategory = 0;
						}
					}
				}
			}else{
				$WebsiteDescription = 'NO PICTURE';
				$WebsiteCategory = 0;
			}
		}else{
			$WebsiteDescription = 'NO DESCRIPTION';
			$WebsiteCategory = 0;
		}
		if ($WebsiteCategory < 0){
			$WebsiteDescription = 'ITEM EXCLUDED';
		}
		$k = StartEvenOrOddRow($k);
		if ($Brand == 1){
			$BrandText = "KL";
		}elseif ($Brand == 2){
			$BrandText = "Blink";
		}elseif ($Brand == 3){
			$BrandText = "Outlet";
		}
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>', 
				$i, 
				$myrow['stockid'], 
				$myrow['description'], 
				$myrow['categoryid'], 
				$Weight, 
				$Volume, 
				$BrandText,
				$WebsiteDescription,
				$FeaturedText
				);
		$i++;
	}
	echo '</table>
			</div>';
	prnMsg("Number of items associated to website catalog: " . locale_number_format($ItemsAdded));
}

time_finish($begintime);
include ('includes/footer.php');

/********************************************************************************************************
				Associated functions 
*********************************************************************************************************/

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
		if ($Manufacturers_id != 3){
			// if it is not belonging to OUTLET, as it is phased out. Outlet items belong to teh same categories as before
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
}

function DeleteWebsiteSalesCategories($Stockid, $UpdateDB, $db){
	if($UpdateDB){
		$sql =	"DELETE FROM salescatprod 
					WHERE stockid ='" .  $Stockid . "'";
		$ErrMsg =_('Could not delete the previous website category for the item because');
		$result = DB_query($sql,$ErrMsg);
	}
}

function UpdateWeight($Stockid, $UpdateDB, $db){
	if (isRing($Stockid)){
		$Weight = STANDARD_RING_WEIGHT;
	}elseif (isToeRing($Stockid)){
		$Weight = STANDARD_RING_WEIGHT;
	}elseif (isBead($Stockid)){
		$Weight = STANDARD_BEAD_WEIGHT;
	}elseif (isBrooche($Stockid)){
		$Weight = STANDARD_BROOCHE_WEIGHT;
	}elseif (isEarring($Stockid)){
		$Weight = STANDARD_EARRING_WEIGHT;
	}elseif (isEarcuff($Stockid)){
		$Weight = STANDARD_EARRING_WEIGHT;
	}elseif (isBracelet($Stockid)){
		$Weight = STANDARD_BRACELET_WEIGHT;
	}elseif (isAnklet($Stockid)){
		$Weight = STANDARD_BRACELET_WEIGHT;
	}elseif (isPendant($Stockid)){
		$Weight = STANDARD_PENDANT_WEIGHT;
	}elseif (isNecklace($Stockid)){
		$Weight = STANDARD_NECKLACE_WEIGHT;
	}elseif (isFoulard($Stockid)){
		$Weight = STANDARD_FOULARD_WEIGHT;
	}elseif (isFaceMask($Stockid)){
		$Weight = STANDARD_FACEMASK_WEIGHT;
	}elseif (isJewelleryRoll($Stockid)){
		$Weight = STANDARD_JEWEL_ROLL_WEIGHT;
	}elseif (isBag($Stockid)){
		$Weight = STANDARD_BAG_WEIGHT;
	}elseif (isPlasticBag($Stockid)){
		$Weight = STANDARD_BAG_WEIGHT;
	}elseif (isTali($Stockid)){
		$Weight = STANDARD_TALI_WEIGHT;
	}elseif (isKeyHolder($Stockid)){
		$Weight = STANDARD_KEYHOLDER_WEIGHT;
	}
	
	if($UpdateDB){
		$sql = "UPDATE stockmaster 
				SET grossweight = " . $Weight . "
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the item weight because');
		$result = DB_query($sql,$ErrMsg);
	}
	return $Weight;
}

function UpdateVolume($Stockid, $UpdateDB, $db){
	if (isRing($Stockid)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isToeRing($Stockid)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isBead($Stockid)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isBrooche($Stockid)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isEarring($Stockid)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isEarcuff($Stockid)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isBracelet($Stockid)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isAnklet($Stockid)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isPendant($Stockid)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isNecklace($Stockid)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isFaceMask($Stockid)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isJewelleryRoll($Stockid)){
		$Length = BOX_L_LENGTH;
		$Width  = BOX_L_WIDTH;
		$Height = BOX_L_HEIGHT;
	}elseif (isBag($Stockid)){
		$Length = BOX_XL_LENGTH;
		$Width  = BOX_XL_WIDTH;
		$Height = BOX_XL_HEIGHT;
	}elseif (isPlasticBag($Stockid)){
		$Length = BOX_XL_LENGTH;
		$Width  = BOX_XL_WIDTH;
		$Height = BOX_XL_HEIGHT;
	}elseif (isTali($Stockid)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isKeyHolder($Stockid)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif ($Stockid = "WKPC01"){
		$Length = BOX_XS_LENGTH;
		$Width  = BOX_XS_WIDTH;
		$Height = BOX_XS_HEIGHT;
	}else{
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}
	
	$Volume = round(($Length/1000)*($Width/1000)*($Height/1000),4,PHP_ROUND_HALF_UP); // dimensions in mm and volume in m3
	
	if($UpdateDB){
		$sql = "UPDATE stockmaster 
				SET volume = " . $Volume . ",
					length =  " . $Length . ",
					width =  " . $Width . ",
					height =  " . $Height . "
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the item volume and dimensions because');
		$result = DB_query($sql,$ErrMsg);
	}
	return $Volume;
}

function UpdatePackaging($Stockid, $Category, $UpdateDB, $db){

	if (isRing($Stockid)){
		$Packaging = "-S";
	}elseif (isToeRing($Stockid)){
		$Packaging = "-S";
	}elseif (isBead($Stockid)){
		$Packaging = "-S";
	}elseif (isBrooche($Stockid)){
		$Packaging = "-M";
	}elseif (isEarring($Stockid)){
		$Packaging = "-S";
	}elseif (isEarcuff($Stockid)){
		$Packaging = "-S";
	}elseif (isBracelet($Stockid)){
		$Packaging = "-M";
	}elseif (isAnklet($Stockid)){
		$Packaging = "-M";
	}elseif (isPendant($Stockid)){
		$Packaging = "-M";
	}elseif (isNecklace($Stockid)){
		$Packaging = "-M";
	}elseif (isFaceMask($Stockid)){
		$Packaging = "-M";
	}elseif (isJewelleryRoll($Stockid)){
		$Packaging = "-L";
	}elseif (isBag($Stockid)){
		$Packaging = "";
	}elseif (isPlasticBag($Stockid)){
		$Packaging = "";
	}elseif (isTali($Stockid)){
		$Packaging = "-S";
	}elseif (isKeyHolder($Stockid)){
		$Packaging = "-S";
	}else{
		$Packaging = "";
	}

	if ($Packaging != ""){
		if (ItemInList($Category, LIST_STOCK_CATEGORIES_KAPAL_LAUT)){
			// if belongs to one of the KL categories 
			$Packaging = "SET-PACK-KL". $Packaging;	
		}elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_BLINK)){
			// if belongs to one of the Blink categories
			$Packaging = "SET-PACK-BL". $Packaging;	
		}else{
			$Packaging = "";
		}
	}
	if (($Packaging != "") AND ($UpdateDB)){
		$sql = "UPDATE stockmaster 
				SET klpackaging = '" . $Packaging . "'
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the packaging set because');
		$result = DB_query($sql,$ErrMsg);
	}
}


function WebsiteCategorySilverJewellery($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//('KL_JEWELLERY',5);
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_KAPAL_LAUT)){
		// if belongs to one of the silver categories 
		$WebCat = KL_JEWELLERY;	
	}

	if (ItemInList($Category, LIST_STOCK_CATEGORIES_GENERAL)){
		// if belongs to one of the general categories 
		$WebCat = GENERAL_ACCESSORIES;	
	}
	
	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}
	
	// define subcategory
	if (($WebCat == KL_JEWELLERY) AND isRing($StockId)){
		if (isSlimRing($StockId)){
			$WebCat = KL_SLIMRINGS;
		}
		else{
			$WebCat = KL_RINGS;	
		}
	}
	if (($WebCat == KL_JEWELLERY) AND isToeRing($StockId)){
		$WebCat = KL_TOERINGS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isEarring($StockId)){
		$WebCat = KL_EARRINGS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isEarcuff($StockId)){
		$WebCat = KL_EARCUFFS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isBracelet($StockId)){
		$WebCat = KL_BRACELETS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isAnklet($StockId)){
		$WebCat = KL_ANKLETS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isNecklace($StockId)){
		$WebCat = KL_NECKLACES;	
	}
	if (($WebCat == KL_JEWELLERY) AND isPendant($StockId)){
		$WebCat = KL_PENDANTS;	
	}	
	if (($WebCat == KL_JEWELLERY) AND isBrooche($StockId)){
		$WebCat = KL_BROOCHES;	
	}	
	if (($WebCat == GENERAL_ACCESSORIES) AND isFaceMask($StockId)){
		$WebCat = GE_FACEMASKS;	
	}	
	if (($WebCat == GENERAL_ACCESSORIES) AND isJewelleryRoll($StockId)){
		$WebCat = GE_JEWELLERY_ROLLS;	
	}	

	return $WebCat; 
}

function WebsiteCategoryBlinkJewellery($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//(('BLINK_JEWELLERY',14);
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_BLINK)){
		// if belongs to one of the Blink categories
		$WebCat = BLINK_JEWELLERY;	
	}

	if (ItemInList($Category, LIST_STOCK_CATEGORIES_GENERAL)){
		// if belongs to one of the general categories 
		$WebCat = GENERAL_ACCESSORIES;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == BLINK_JEWELLERY) AND isRing($StockId)){
		$WebCat = BLINK_RINGS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isEarring($StockId)){
		$WebCat = BLINK_EARRINGS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isEarcuff($StockId)){
		$WebCat = BLINK_EARCUFFS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isBracelet($StockId)){
		$WebCat = BLINK_BRACELETS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isNecklace($StockId)){
		$WebCat = BLINK_NECKLACES;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isPendant($StockId)){
		$WebCat = BLINK_PENDANTS;	
	}	
	if (($WebCat == BLINK_JEWELLERY) AND isBrooche($StockId)){
		$WebCat = BLINK_BROOCHES;	
	}	
	if (($WebCat == BLINK_JEWELLERY) AND isBag($StockId)){
		$WebCat = BAGS;	
	}	
	if (($WebCat == BLINK_JEWELLERY) AND isPlasticBag($StockId)){
		$WebCat = BAGS;	
	}	
	if (($WebCat == BLINK_JEWELLERY) AND isKeyHolder($StockId)){
		$WebCat = BLINK_KEYHOLDERS;	
	}	
	if (($WebCat == GENERAL_JEWELLERY) AND isFaceMask($StockId)){
		$WebCat = GE_FACEMASKS;	
	}	
	if (($WebCat == GENERAL_JEWELLERY) AND isJewelleryRoll($StockId)){
		$WebCat = GE_JEWELLERY_ROLLS;	
	}	

	return $WebCat; 
}

function WebsiteCategoryBags($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//('BAGS',29);
	if ((ItemInList($Category, LIST_STOCK_CATEGORIES_GENERAL))
		AND (isPlasticBag($StockId))){ 
		$WebCat = BAGS;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	return $WebCat; 
}



function FindWebsiteDescription($WebsiteCategory, $db){
	$SQLCat = "SELECT salescat.salescatname
			FROM salescat
			WHERE salescat.salescatid = '". $WebsiteCategory . "'";
	$resultCat = DB_query($SQLCat);
	while ($myrowCat = DB_fetch_array($resultCat)) {
		$WebsiteDescription = $WebsiteCategory . ' -> ' . $myrowCat['salescatname'];
	}
	return $WebsiteDescription;
}


function ItemFeaturedAsTopSale($StockID, $Category, $DaysTopSales, $db){
	$Featured = FALSE;
	// si és Top Sales, llavors FEATURED
	if (PositionTopSalesItem($StockID, $DaysTopSales, $db) <= FEATURED_IN_WEBSITE_AS_TOP_SALES){
		$Featured = TRUE;
	}
	// si està a DISCOUNT, OUTLET or NO MORE BUYING llavors, not featured
	if (   ItemInList($Category, LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)
		OR ItemInList($Category, LIST_STOCK_CATEGORIES_OUTLET)
		OR ItemInList($Category, LIST_STOCK_CATEGORIES_OUTLET)){
		$Featured = FALSE;
	}
	// si és una BAG llavors, not featured
	if (isPlasticBag($StockID))  { 
		$Featured = FALSE;
	}
	return $Featured;
}

function ItemExcludedFromWebsite($StockID, $Category){
	if ((substr($StockID, 0,3) == "TM-")
		OR (substr($StockID, -2,2) == "-D")
		OR (substr($StockID, 0,4) == "GOTA")
		OR (substr($StockID, 0,4) == "BSBE")
		OR (substr($StockID, 0,4) == "KLBE")){
		return true;
	}
	return false;
}

?>
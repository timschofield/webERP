<?php

define("VERSIONFILE", "1.08"); 

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$Title = _('Kapal-Laut Set Website Categories '. VERSIONFILE);
include ('includes/header.inc');
include ('includes/KLDefines.php');
include ('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/WeberpOpenCartDefines.php');

$UpdateDB = TRUE;

$begintime = time_start();

// Delete the current classification
 
// Not any more,,, we just add the items not in webSHOP
/*
if($UpdateDB){
	prnMsg("Updating webERP DB...");

	$sql = "TRUNCATE salescatprod";
	$ErrMsg =_('Could not truncate the salescatprod table because');
	$result = DB_query($sql,$ErrMsg);
}else{
	prnMsg("NOT updating webERP DB. Only for test purposes. Set $UpdateDB = TRUE");
}
*/

// Select items and classify them
$SQL = "SELECT stockmaster.stockid,
			   stockmaster.description,
			   stockmaster.grossweight,
			   stockmaster.volume,
			   stockmaster.longdescription,	
			   stockmaster.categoryid	
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockcategory.stocktype = 'F'
			AND stockmaster.categoryid IN " . CATEGORIES_AVAILABLE_WEBSITE ."
			AND stockmaster.discontinued = 0
			AND (((NOT EXISTS (SELECT * 
								FROM salescatprod
								WHERE stockmaster.stockid = salescatprod.stockid))
					OR stockmaster.grossweight = 0
					OR stockmaster.volume = 0)
				OR ((EXISTS (SELECT * 
							FROM salescatprod
							WHERE stockmaster.stockid = salescatprod.stockid
								AND salescatprod.salescatid NOT IN (" . WEBERP_OUTLET_CATEGORIES. ")))
					AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_DISCOUNT . "))
		ORDER BY stockmaster.stockid";
$result = DB_query($SQL);
if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Items To Classify for Website Categories') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Stock Category') . '</th>
						<th>' . _('Weight Kg') . '</th>
						<th>' . _('Volume m3') . '</th>
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
			if (isRing($myrow['stockid'])){
				$Weight = STANDARD_RING_WEIGHT;
			}elseif (isToeRing($myrow['stockid'])){
				$Weight = STANDARD_RING_WEIGHT;
			}elseif (isBead($myrow['stockid'])){
				$Weight = STANDARD_BEAD_WEIGHT;
			}elseif (isEarring($myrow['stockid'])){
				$Weight = STANDARD_EARRING_WEIGHT;
			}elseif (isEarcuff($myrow['stockid'])){
				$Weight = STANDARD_EARRING_WEIGHT;
			}elseif (isBracelet($myrow['stockid'])){
				$Weight = STANDARD_BRACELET_WEIGHT;
			}elseif (isAnklet($myrow['stockid'])){
				$Weight = STANDARD_BRACELET_WEIGHT;
			}elseif (isPendant($myrow['stockid'])){
				$Weight = STANDARD_PENDANT_WEIGHT;
			}elseif (isNecklace($myrow['stockid'])){
				$Weight = STANDARD_NECKLACE_WEIGHT;
			}elseif (isPlasticBag($myrow['stockid'])){
				$Weight = STANDARD_BAG_WEIGHT;
			}elseif (isTali($myrow['stockid'])){
				$Weight = STANDARD_TALI_WEIGHT;
			}
			UpdateWeight($myrow['stockid'], $Weight, $UpdateDB, $db);
		}
		
		$Volume = $myrow['volume'];
		if ($Volume == 0){
			if (isRing($myrow['stockid'])){
				$Volume = STANDARD_RING_VOLUME;
			}elseif (isToeRing($myrow['stockid'])){
				$Volume = STANDARD_RING_VOLUME;
			}elseif (isBead($myrow['stockid'])){
				$Volume = STANDARD_BEAD_VOLUME;
			}elseif (isEarring($myrow['stockid'])){
				$Volume = STANDARD_EARRING_VOLUME;
			}elseif (isEarcuff($myrow['stockid'])){
				$Volume = STANDARD_EARRING_VOLUME;
			}elseif (isBracelet($myrow['stockid'])){
				$Volume = STANDARD_BRACELET_VOLUME;
			}elseif (isAnklet($myrow['stockid'])){
				$Volume = STANDARD_BRACELET_VOLUME;
			}elseif (isPendant($myrow['stockid'])){
				$Volume = STANDARD_PENDANT_VOLUME;
			}elseif (isNecklace($myrow['stockid'])){
				$Volume = STANDARD_NECKLACE_VOLUME;
			}elseif (isPlasticBag($myrow['stockid'])){
				$Volume = STANDARD_BAG_VOLUME;
			}elseif (isTali($myrow['stockid'])){
				$Volume = STANDARD_TALI_VOLUME;
			}
			UpdateVolume($myrow['stockid'], $Volume, $UpdateDB, $db);
		}
		// si tenim foto seguim endavant, sino no el publiquem a la website
		if(file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg') ) {

			// Mirar si pertany a super categoria ON SPECIAL. Si està en DISCOUNT, Només pot estar a on SPECIAL, per a no mesclar...
			$WebsiteCategory = WebsiteCategoryDiscount($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
			if ($WebsiteCategory > 0){
				DeleteWebsiteSalesCategories($myrow['stockid'], $UpdateDB, $db);
				InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
				$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
				$ItemsAdded++;
			}else{
				// Mirar si pertany a super categoria SILVER
				$WebsiteCategory = WebsiteCategorySilverJewellery($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
				if ($WebsiteCategory > 0){
					InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
					$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
					$ItemsAdded++;
				}else{
					// Mirar si pertany a super categoria FASHION JEWELLERY
					$WebsiteCategory = WebsiteCategoryFashionJewellery($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
					if ($WebsiteCategory > 0){
						InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
						$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
						$ItemsAdded++;
					}else{
						// Mirar si pertany a super categoria STAINLESS STEEL
						$WebsiteCategory = WebsiteCategoryStainlessSteel($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
						if ($WebsiteCategory > 0){
							InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
							$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
							$ItemsAdded++;
						}else{
							// Mirar si pertany a super categoria LEATHER RICARD DESACTIVATED 20/10/2014
							// $WebsiteCategory = WebsiteCategoryLeatherJewellery($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
							// Mirar si pertany a super categoria CLASSIC
							$WebsiteCategory = WebsiteCategoryClassic($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
							if ($WebsiteCategory > 0){
								InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
								$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
								$ItemsAdded++;
							}else{
								// Mirar si pertany a super categoria BAGS
								$WebsiteCategory = WebsiteCategoryBags($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
								if ($WebsiteCategory > 0){
									InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
									$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
									$ItemsAdded++;
								}else{
									// Mirar si pertany a super categoria WORLD BRANDS
									$WebsiteCategory = WebsiteCategoryWorldBrandJewellery($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
									if ($WebsiteCategory > 0){
										InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
										$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
										$ItemsAdded++;
									}
								}
							}
						}
					}
				}
			}
		}else{
			$WebsiteDescription = 'NO PICTURE';
			$WebsiteCategory = 0;
		}
		if ($WebsiteCategory < 0){
			$WebsiteDescription = 'ITEM EXCLUDED';
		}
		$k = StartEvenOrOddRow($k);
		printf('<td class="number">%s</td>
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
				$WebsiteDescription,
				$FeaturedText
				);
		$i++;
	}
	echo '</table>
			</div>';
	prnMsg("Number of items associated to website catalog: " . locale_number_format($ItemsAdded));
}

function InsertWebsiteSalesCategory($Stockid, $WebsiteCategory, $Featured, $UpdateDB, $db){
	if($UpdateDB){
		
//		if we allow an item to be in several categories this code must be commented. 
//		if we only want it in one category, then uncomment!
		$sql =	"DELETE FROM salescatprod 
					WHERE salescatid = '" . $WebsiteCategory . "' 
						AND stockid ='" .  $Stockid . "'";
		$ErrMsg =_('Could not delete the previous website category for the item because');
		$result = DB_query($sql,$ErrMsg);
// Comment or uncomment end 

		$SQLCheck = "SELECT *
				FROM salescatprod
				WHERE salescatprod.stockid = '" . $Stockid . "'
					AND salescatprod.salescatid = '" . $WebsiteCategory . "'";	
		$result = DB_query($SQLCheck);
		if(DB_num_rows($result) == 0){
			$sql = "INSERT INTO salescatprod (
						salescatid ,
						stockid,
						featured)
					VALUES (
						'" . $WebsiteCategory . "',
						'" . $Stockid . "',
						'" . $Featured . "')";
			$ErrMsg =_('Could not insert the website category for the item because');
			$result = DB_query($sql,$ErrMsg);
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

function UpdateWeight($Stockid, $Weight, $UpdateDB, $db){
	if($UpdateDB){
		$sql = "UPDATE stockmaster 
				SET grossweight = " . $Weight . "
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the item weight because');
		$result = DB_query($sql,$ErrMsg);
	}
}

function UpdateVolume($Stockid, $Volume, $UpdateDB, $db){
	if($UpdateDB){
		$sql = "UPDATE stockmaster 
				SET volume = " . $Volume . "
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the item volume because');
		$result = DB_query($sql,$ErrMsg);
	}
}

function WebsiteCategoryWorldBrandJewellery($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//('WORLD_BRAND_JEWELLERY',68);
	if (($Category == "CONSIG"))	{ 
		// if belongs to one of the consignment categories 
			$WebCat = WORLD_BRAND_JEWELLERY;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}
	
	// define subcategory
	if (($WebCat == WORLD_BRAND_JEWELLERY) AND isFamily($StockId, "PL")){
		$WebCat = WORLD_BRAND_PLATADEPALO;	
	}
	if (($WebCat == WORLD_BRAND_JEWELLERY) AND isfamily($StockId, "HP")){
		$WebCat = WORLD_BRAND_HIPANEMA;	
	}
	return $WebCat; 
}

function WebsiteCategorySilverJewellery($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//('SILVER_JEWELLERY',5);
	if (($Category == "SILVER") OR ($Category == "TESTSI") OR ($Category == "NOPOSI"))	{ 
		// if belongs to one of the silver categories 
		if (WebsiteCategoryClassic($StockId, $Description, $Long, $Category) == 0){
			// AND is NOT a classic category
			$WebCat = SILVER_JEWELLERY;	
		}
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}
	
	// define subcategory
	if (($WebCat == SILVER_JEWELLERY) AND isRing($StockId)){
		if (isSlimRing($StockId)){
			$WebCat = SILVER_SLIMRINGS;
		}
		else{
			$WebCat = SILVER_RINGS;	
		}
	}
	if (($WebCat == SILVER_JEWELLERY) AND isToeRing($StockId)){
		$WebCat = SILVER_TOERINGS;	
	}
	if (($WebCat == SILVER_JEWELLERY) AND isEarring($StockId)){
		$WebCat = SILVER_EARRINGS;	
	}
	if (($WebCat == SILVER_JEWELLERY) AND isEarcuff($StockId)){
		$WebCat = SILVER_EARCUFFS;	
	}
	if (($WebCat == SILVER_JEWELLERY) AND isBracelet($StockId)){
		$WebCat = SILVER_BRACELETS;	
	}
	if (($WebCat == SILVER_JEWELLERY) AND isAnklet($StockId)){
		$WebCat = SILVER_ANKLETS;	
	}
	if (($WebCat == SILVER_JEWELLERY) AND isNecklace($StockId)){
		$WebCat = SILVER_NECKLACES;	
	}
	if (($WebCat == SILVER_JEWELLERY) AND isPendant($StockId)){
		$WebCat = SILVER_PENDANTS;	
	}	
	return $WebCat; 
}

function WebsiteCategoryFashionJewellery($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//(('FASHION_JEWELLERY',14);
	if (($Category == "FASHIO") OR ($Category == "TESTFJ") OR ($Category == "NOPOFJ"))  { 
		// if belongs to one of the FJ categories BUT it does not have leather
		$WebCat = FASHION_JEWELLERY;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == FASHION_JEWELLERY) AND isRing($StockId)){
		$WebCat = FASHION_JEWELLERY_RINGS;	
	}
	if (($WebCat == FASHION_JEWELLERY) AND isEarring($StockId)){
		$WebCat = FASHION_JEWELLERY_EARRINGS;	
	}
	if (($WebCat == FASHION_JEWELLERY) AND isEarcuff($StockId)){
		$WebCat = FASHION_JEWELLERY_EARCUFFS;	
	}
	if (($WebCat == FASHION_JEWELLERY) AND isBracelet($StockId)){
		$WebCat = FASHION_JEWELLERY_BRACELETS;	
	}
	if (($WebCat == FASHION_JEWELLERY) AND isNecklace($StockId)){
		$WebCat = FASHION_JEWELLERY_NECKLACES;	
	}
	if (($WebCat == FASHION_JEWELLERY) AND isPendant($StockId)){
		$WebCat = FASHION_JEWELLERY_PENDANTS;	
	}	
	return $WebCat; 
}

function WebsiteCategoryStainlessSteel($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//('STAINLESS_STEEL_JEWELLERY',6);
	if (($Category == "STAINL") OR ($Category == "TESTSS") OR ($Category == "NOPOSS")) { 
		// if belongs to one of the SS categories
		$WebCat = STAINLESS_STEEL_JEWELLERY;	
	}
	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == STAINLESS_STEEL_JEWELLERY) AND isRing($StockId)){
		$WebCat = STAINLESS_STEEL_RINGS;	
	}
	if (($WebCat == STAINLESS_STEEL_JEWELLERY) AND isEarring($StockId)){
		$WebCat = STAINLESS_STEEL_EARRINGS;	
	}
	if (($WebCat == STAINLESS_STEEL_JEWELLERY) AND isEarcuff($StockId)){
		$WebCat = STAINLESS_STEEL_EARCUFFS;	
	}
	if (($WebCat == STAINLESS_STEEL_JEWELLERY) AND isBracelet($StockId)){
		$WebCat = STAINLESS_STEEL_BRACELETS;	
	}
	if (($WebCat == STAINLESS_STEEL_JEWELLERY) AND isNecklace($StockId)){
		$WebCat = STAINLESS_STEEL_NECKLACE;	
	}
	if (($WebCat == STAINLESS_STEEL_JEWELLERY) AND isPendant($StockId)){
		$WebCat = STAINLESS_STEEL_PENDANTS;	
	}	
	return $WebCat; 
}

function WebsiteCategoryLeatherJewellery($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//(('LEATHER_JEWELLERY',26);
	if ((($Category == "FASHIO") OR ($Category == "TESTFJ") OR ($Category == "NOPOFJ")) 
		AND ((isFamily($StockId, "LE")) OR (mb_stristr($Description, "leather") != FALSE)))  { 
		$WebCat = LEATHER_JEWELLERY;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == LEATHER_JEWELLERY) AND isRing($StockId)){
		$WebCat = LEATHER_RINGS;	
	}
	if (($WebCat == LEATHER_JEWELLERY) AND isEarring($StockId)){
		$WebCat = LEATHER_EARRINGS;	
	}
	if (($WebCat == LEATHER_JEWELLERY) AND isEarcuff($StockId)){
		$WebCat = LEATHER_EARCUFF;	
	}
	if (($WebCat == LEATHER_JEWELLERY) AND isBracelet($StockId)){
		$WebCat = LEATHER_BRACELETS;	
	}
	if (($WebCat == LEATHER_JEWELLERY) AND isNecklace($StockId)){
		$WebCat = LEATHER_NECKLACES;	
	}
	if (($WebCat == LEATHER_JEWELLERY) AND isPendant($StockId)){
		$WebCat = LEATHER_PENDANTS;	
	}	
	return $WebCat; 
}

function WebsiteCategoryBags($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//('BAGS',29);
	if ((($Category == "ACCESO") OR ($Category == "TESTAC") OR ($Category == "NOPOAC")) 
		AND (isPlasticBag($StockId))){ 
		$WebCat = BAGS;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	return $WebCat; 
}

function WebsiteCategoryClassic($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//(('CLASSIC_JEWELLERY',61);
	if ((substr($StockId, 0,4) == "BEPU") 
		OR (substr($StockId, 0,4) == "PSPU")
		OR (substr($StockId, 0,4) == "ALCL")) { 
		$WebCat = CLASSIC_JEWELLERY;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == CLASSIC_JEWELLERY) AND isRing($StockId)){
		$WebCat = CLASSIC_RINGS;	
	}
	if (($WebCat == CLASSIC_JEWELLERY) AND isEarring($StockId)){
		$WebCat = CLASSIC_EARRINGS;	
	}
	if (($WebCat == CLASSIC_JEWELLERY) AND isEarcuff($StockId)){
		$WebCat = CLASSIC_EARCUFFS;	
	}
	if (($WebCat == CLASSIC_JEWELLERY) AND isBracelet($StockId)){
		$WebCat = CLASSIC_BRACELETS;	
	}
	if (($WebCat == CLASSIC_JEWELLERY) AND isNecklace($StockId)){
		$WebCat = CLASSIC_NECKLACES;	
	}
	if (($WebCat == CLASSIC_JEWELLERY) AND isPendant($StockId)){
		$WebCat = CLASSIC_PENDANTS;	
	}	
	return $WebCat; 
}


function WebsiteCategoryDiscount($StockId, $Description, $Long, $Category){
	$WebCat = 0;

	if($Category == "DISCOU"){
		$WebCat = JEWELLERY_ON_SPECIAL;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockId, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isRing($StockId)){
		$WebCat = RINGS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isEarring($StockId)){
		$WebCat = EARRINGS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isEarcuff($StockId)){
		$WebCat = EARCUFFS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isBracelet($StockId)){
		$WebCat = BRACELETS_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isNecklace($StockId)){
		$WebCat = NECKLACES_ON_SPECIAL;	
	}
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isPendant($StockId)){
		$WebCat = PENDANTS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isToeRing($StockId)){
		$WebCat = TOERINGS_ON_SPECIAL;	
	}	
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isAnklet($StockId)){
		$WebCat = ANKLETS_ON_SPECIAL;	
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

time_finish($begintime);
include ('includes/footer.inc');

function ItemFeaturedAsTopSale($StockID, $Category, $DaysTopSales, $db){
	$Featured = FALSE;
	// si és Top Sales, llavors FEATURED
	if (positionTopSalesItem($StockID, FEATURED_IN_WEBSITE_AS_TOP_SALES, $DaysTopSales, $db) <= FEATURED_IN_WEBSITE_AS_TOP_SALES){
		$Featured = TRUE;
	}
	// si està a DISCOUNT, OUTLET or NO MORE BUYING llavors, not featured
	if (   ItemInList($Category, LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)
		OR ItemInList($Category, LIST_STOCK_CATEGORIES_DISCOUNT)
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
		OR (substr($StockID, 0,4) == "BSBE")
		OR (substr($StockID, 0,4) == "KLBE")){
		return true;
	}
	return false;
}

?>
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
include('includes/WeberpOpenCartDefines.php');

$UpdateDB = TRUE;

$begintime = time_start();

// Delete the current classification
 
// Not any more,,, we just add the items not in Online Shop
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
			AND stockmaster.categoryid IN " . CATEGORIES_AVAILABLE_WEBSITE .
			SQLForWebsiteStockidExceptions() . "
			AND (((NOT EXISTS (SELECT * 
								FROM salescatprod
								WHERE stockmaster.stockid = salescatprod.stockid))
					OR stockmaster.grossweight = 0
					OR stockmaster.volume = 0)
				OR ((EXISTS (SELECT * 
							FROM salescatprod
							WHERE stockmaster.stockid = salescatprod.stockid
								AND salescatprod.salescatid NOT IN (" . WEBERP_OUTLET_CATEGORIES. ")))
					AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . "))
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
			}elseif (isBrooche($myrow['stockid'])){
				$Weight = STANDARD_BROOCHE_WEIGHT;
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
			}elseif (isFoulard($myrow['stockid'])){
				$Weight = STANDARD_FOULARD_WEIGHT;
			}elseif (isBag($myrow['stockid'])){
				$Weight = STANDARD_BAG_WEIGHT;
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
			}elseif (isBrooche($myrow['stockid'])){
				$Volume = STANDARD_BROOCHE_VOLUME;
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
			}elseif (isFoulard($myrow['stockid'])){
				$Volume = STANDARD_FOULARD_VOLUME;
			}elseif (isBag($myrow['stockid'])){
				$Volume = STANDARD_BAG_VOLUME;
			}elseif (isPlasticBag($myrow['stockid'])){
				$Volume = STANDARD_BAG_VOLUME;
			}elseif (isTali($myrow['stockid'])){
				$Volume = STANDARD_TALI_VOLUME;
			}
			UpdateVolume($myrow['stockid'], $Volume, $UpdateDB, $db);
		}
		// si tenim descripció prou llarga
		if (strlen($myrow['description']) >= 5){
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
					// Mirar si pertany a super categoria STABLE KL
					$WebsiteCategory = WebsiteCategorySilverJewellery($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
					if ($WebsiteCategory > 0){
						InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
						$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
						$ItemsAdded++;
					}else{
						// Mirar si pertany a super categoria BLINK JEWELLERY
						$WebsiteCategory = WebsiteCategoryBlinkJewellery($myrow['stockid'], $myrow['description'], $myrow['longdescription'], $myrow['categoryid']);
						if ($WebsiteCategory > 0){
							InsertWebsiteSalesCategory($myrow['stockid'], $WebsiteCategory, $FeaturedAsTopSales, $UpdateDB, $db);
							$WebsiteDescription = FindWebsiteDescription($WebsiteCategory, $db);
							$ItemsAdded++;
						}else{
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
	if (($Category == "CONSIG") OR isFamily($StockId, "DS"))	{ 
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
	if (($WebCat == WORLD_BRAND_JEWELLERY) AND isfamily($StockId, "DS")){
		$WebCat = WORLD_BRAND_DESIGUAL;	
	}
	return $WebCat; 
}

function WebsiteCategorySilverJewellery($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//('KL_JEWELLERY',5);
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_KAPAL_LAUT)){
		// if belongs to one of the silver categories 
		if (WebsiteCategoryClassic($StockId, $Description, $Long, $Category) == 0){
			// AND is NOT a classic category
			$WebCat = KL_JEWELLERY;	
		}
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
	return $WebCat; 
}

function WebsiteCategoryBlinkJewellery($StockId, $Description, $Long, $Category){
	$WebCat = 0;
	
	//(('BLINK_JEWELLERY',14);
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_BLINK)){
		// if belongs to one of the FJ categories BUT it does not have leather
		$WebCat = BLINK_JEWELLERY;	
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
	if (($WebCat == CLASSIC_JEWELLERY) AND isBrooche($StockId)){
		$WebCat = CLASSIC_BROOCHES;	
	}	
	return $WebCat; 
}


function WebsiteCategoryDiscount($StockId, $Description, $Long, $Category){
	$WebCat = 0;

	if(($Category == "DISC20") OR ($Category == "DISC50") OR ($Category == "DISC2A") OR ($Category == "DISC5A")){
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
	if (($WebCat == JEWELLERY_ON_SPECIAL) AND isBrooche($StockId)){
		$WebCat = BROOCHES_ON_SPECIAL;	
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
include ('includes/footer.php');

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
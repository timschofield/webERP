<?php

define("VERSIONFILE", "2.10"); 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Kapal-Laut Set Online Shop Categories '. VERSIONFILE);
include ('includes/header.php');
include ('includes/KLDefines.php');
include ('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/OpenCartGeneralFunctions.php');

$UpdateDB = TRUE;

// Select items and classify them
$SQL = "SELECT stockmaster.stockid,
			   stockmaster.description,
			   stockmaster.grossweight,
			   stockmaster.volume,
			   stockmaster.klpackaging,
			   stockmaster.longdescription,	
			   stockmaster.categoryid	
		FROM stockmaster
		WHERE " . SQLFilterStockmasterForOnlineShop("ALL") . "
			AND ((NOT EXISTS (SELECT * 
								FROM salescatprod
								WHERE stockmaster.stockid = salescatprod.stockid))
					OR stockmaster.grossweight = 0
					OR stockmaster.klpackaging = ''
					OR stockmaster.volume = 0)
		ORDER BY stockmaster.stockid";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0){
	$TableTitleText = _('Items To Classify for Online Shop Categories');
	ShowTableTitle($TableTitleText);
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . _('#') . '</th>
					<th>' . _('Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Stock Category') . '</th>
					<th>' . _('Weight Kg') . '</th>
					<th>' . _('Volume m3') . '</th>
					<th>' . _('Brand') . '</th>
					<th>' . _('Website Category') . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	$ItemsAdded = 0;
	while ($MyRow = DB_fetch_array($Result)) {

		$WebsiteDescription = "";
		$FeaturedAsTopSales = 0;
		$FeaturedText = "";
		$Brand = FindWebsiteBrand($MyRow['stockid'], $MyRow['categoryid'], $MyRow['description']);
		$Weight = $MyRow['grossweight'];
		$Packaging = $MyRow['klpackaging'];
		$Volume = $MyRow['volume'];

		if ($Weight == 0){
			$Weight = UpdateWeight($MyRow['stockid'], $Weight, $UpdateDB);
		}

		if ($Packaging == ""){
			$Packaging = UpdatePackaging($MyRow['stockid'],$MyRow['categoryid'], $Brand, $UpdateDB);
			$Volume = UpdateVolumeByPackaging($MyRow['stockid'], $Packaging, $UpdateDB);
		}
		
		if ($Volume == 0){
			$Volume = UpdateVolumeByPackaging($MyRow['stockid'], $Packaging, $UpdateDB);
		}
		// if we have some kind of description, long enough, we can move ahead. Otherwise, we miss the descriptiob
		if (strlen($MyRow['description']) >= 8){
			// if we have picture, then we can publish online, otherwise not yet!
			if(file_exists($_SESSION['part_pics_dir'] . '/' .$MyRow['stockid'].'.jpg') ) {
				// From the brand we know if it gors to KL online shop or Blink online shop

				if ($Brand == 1){
					// KL brand detected ;-) select the sub category 
					$WebsiteCategory = WebsiteCategorySilverJewellery($MyRow['stockid'], $MyRow['description'], $MyRow['longdescription'], $MyRow['categoryid']);
					if ($WebsiteCategory > 0){ 
						InsertWebsiteSalesCategory($MyRow['stockid'], $WebsiteCategory, $Brand, FALSE, $FeaturedAsTopSales, $UpdateDB);
						$WebsiteDescription = FindWebsiteDescription($WebsiteCategory);
						$ItemsAdded++;
					}else{
						$WebsiteDescription = 'NO KAPAL-LAUT CATEGORY';
					}
				}else{
					// Blink brand detected ;-)
					$WebsiteCategory = WebsiteCategoryBlinkJewellery($MyRow['stockid'], $MyRow['description'], $MyRow['longdescription'], $MyRow['categoryid']);
					if ($WebsiteCategory > 0){ 
						InsertWebsiteSalesCategory($MyRow['stockid'], $WebsiteCategory, $Brand, FALSE, $FeaturedAsTopSales, $UpdateDB);
						$WebsiteDescription = FindWebsiteDescription($WebsiteCategory);
						$ItemsAdded++;
					}else{
						$WebsiteDescription = 'NO BLINK CATEGORY';
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
		if ($Brand == 1){
			$BrandText = "KL";
		}elseif ($Brand == 2){
			$BrandText = "Blink";
		}
		printf('<tr class="striped_row">
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>', 
				$i, 
				$MyRow['stockid'], 
				$MyRow['description'], 
				$MyRow['categoryid'], 
				$Weight, 
				$Volume, 
				$BrandText,
				$WebsiteDescription
				);
		$i++;
	}
	echo '</tbody>
		</table>
		</div>';
	prnMsg("Number of items associated to website catalog: " . locale_number_format($ItemsAdded));
}

include ('includes/footer.php');

/********************************************************************************************************
				Associated functions 
*********************************************************************************************************/

function DeleteWebsiteSalesCategories($Stockid, $UpdateDB){
	if($UpdateDB){
		$SQL =	"DELETE FROM salescatprod 
					WHERE stockid ='" .  $Stockid . "'";
		$ErrMsg =_('Could not delete the previous website category for the item because');
		$Result = DB_query($SQL,$ErrMsg);
	}
}

function UpdateWeight($Stockid, $UpdateDB){
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
	}elseif (isPiercing($Stockid)){
		$Weight = STANDARD_PIERCING_WEIGHT;
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
	}elseif (isJewelleryBox($Stockid)){
		$Weight = STANDARD_JEWEL_BOX_WEIGHT;
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
	}else{
		$Weight = 0;
	}
	
	if($UpdateDB){
		$SQL = "UPDATE stockmaster 
				SET grossweight = " . $Weight . "
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the item weight because');
		$Result = DB_query($SQL,$ErrMsg);
	}
	return $Weight;
}

function UpdateVolume($Stockid, $UpdateDB){
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
	}elseif (isPiercing($Stockid)){
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
	}elseif (isJewelleryBox($Stockid)){
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
		$SQL = "UPDATE stockmaster 
				SET volume = " . $Volume . ",
					length =  " . $Length . ",
					width =  " . $Width . ",
					height =  " . $Height . "
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the item volume and dimensions because');
		$Result = DB_query($SQL,$ErrMsg);
	}
	return $Volume;
}

function UpdateVolumeByPackaging($Stockid, $Packaging, $UpdateDB){
	$TypePackaging = substr($Packaging, -1, 1);
	if ($Stockid == "WKPC01"){
		$Length = BOX_XS_LENGTH;
		$Width  = BOX_XS_WIDTH;
		$Height = BOX_XS_HEIGHT;
	}elseif ($TypePackaging == "S"){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif ($TypePackaging == "M"){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif ($TypePackaging == "L"){
		$Length = BOX_L_LENGTH;
		$Width  = BOX_L_WIDTH;
		$Height = BOX_L_HEIGHT;
	}else{
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}
	
	$Volume = round(($Length/1000)*($Width/1000)*($Height/1000),4,PHP_ROUND_HALF_UP); // dimensions in mm and volume in m3
	
	if($UpdateDB){
		$SQL = "UPDATE stockmaster 
				SET volume = " . $Volume . ",
					length =  " . $Length . ",
					width =  " . $Width . ",
					height =  " . $Height . "
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the item volume and dimensions because');
		$Result = DB_query($SQL,$ErrMsg);
	}
	return $Volume;
}

function UpdatePackaging($Stockid, $Category, $Brand, $UpdateDB){

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
	}elseif (isPiercing($Stockid)){
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
	}elseif (isTali($Stockid)){
		$Packaging = "-S";
	}elseif (isKeyHolder($Stockid)){
		$Packaging = "-S";
	}else{
		$Packaging = "";
	}

	if ($Packaging != ""){
		if ($Brand == 1){
			$Packaging = "SET-PACK-KL". $Packaging;	
		}elseif ($Brand == 2){
			$Packaging = "SET-PACK-BL". $Packaging;	
		}else{
			$Packaging = "";
		}
	}

	if (isJewelleryBox($Stockid) 
		OR isBag($Stockid)
		OR isPlasticBag($Stockid)){
		$Packaging = "NO-PACKAGING";
	}
	
	if (($Packaging != "") AND ($UpdateDB)){
		$SQL = "UPDATE stockmaster 
				SET klpackaging = '" . $Packaging . "'
				WHERE stockid =	'" . $Stockid . "'";
		$ErrMsg =_('Could not update the packaging set because');
		$Result = DB_query($SQL,$ErrMsg);
	}
	
	return $Packaging;
}


function WebsiteCategorySilverJewellery($StockId, $Description, $Long, $Category){

	// It comes from Kapal-Laut Brand, so assume it is KAPAL_LAUT, let's try to be more precise
	$WebCat = KL_JEWELLERY;	

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
	if (($WebCat == KL_JEWELLERY) AND isPiercing($StockId)){
		$WebCat = KL_PIERCINGS;	
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
	if (($WebCat == KL_JEWELLERY) AND isJewelleryBox($StockId)){
		$WebCat = KL_JEWELLERY_BOXES;	
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

	// It comes from Blink Brand, so assume it is BLINK, let's try to be more precise
	$WebCat = BLINK_JEWELLERY;	

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
	if (($WebCat == BLINK_JEWELLERY) AND isPiercing($StockId)){
		$WebCat = BLINK_PIERCINGS;	
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


function FindWebsiteDescription($WebsiteCategory){
	$SQLCat = "SELECT salescat.salescatname
			FROM salescat
			WHERE salescat.salescatid = '". $WebsiteCategory . "'";
	$ResultCat = DB_query($SQLCat);
	while ($MyRowCat = DB_fetch_array($ResultCat)) {
		$WebsiteDescription = $WebsiteCategory . ' -> ' . $MyRowCat['salescatname'];
	}
	return $WebsiteDescription;
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
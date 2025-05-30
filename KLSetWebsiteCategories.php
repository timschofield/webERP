<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('KL Set Online Shop Categories');
include ('includes/header.php');
include ('includes/KLDefines.php');
include ('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/OCOpenCartGeneralFunctions.php');

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
		echo '<tr class="striped_row">
				<td class="number">'.$i.'</td>
				<td>'.$MyRow['stockid'].'</td>
				<td>'.$MyRow['description'].'</td>
				<td>'.$MyRow['categoryid'].'</td>
				<td>'.$Weight.'</td>
				<td>'.$Volume.'</td>
				<td>'.$BrandText.'</td>
				<td>'.$WebsiteDescription.'</td>
				</tr>';
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

function DeleteWebsiteSalesCategories($StockID, $UpdateDB){
	if($UpdateDB){
		$SQL =	"DELETE FROM salescatprod 
					WHERE stockid ='" .  $StockID . "'";
		$ErrMsg =_('Could not delete the previous website category for the item because');
		$Result = DB_query($SQL,$ErrMsg);
	}
}

function UpdateWeight($StockID, $UpdateDB){
	if (isRing($StockID)){
		$Weight = STANDARD_RING_WEIGHT;
	}elseif (isToeRing($StockID)){
		$Weight = STANDARD_RING_WEIGHT;
	}elseif (isBead($StockID)){
		$Weight = STANDARD_BEAD_WEIGHT;
	}elseif (isBrooche($StockID)){
		$Weight = STANDARD_BROOCHE_WEIGHT;
	}elseif (isEarring($StockID)){
		$Weight = STANDARD_EARRING_WEIGHT;
	}elseif (isEarcuff($StockID)){
		$Weight = STANDARD_EARRING_WEIGHT;
	}elseif (isPiercing($StockID)){
		$Weight = STANDARD_PIERCING_WEIGHT;
	}elseif (isBracelet($StockID)){
		$Weight = STANDARD_BRACELET_WEIGHT;
	}elseif (isAnklet($StockID)){
		$Weight = STANDARD_BRACELET_WEIGHT;
	}elseif (isPendant($StockID)){
		$Weight = STANDARD_PENDANT_WEIGHT;
	}elseif (isNecklace($StockID)){
		$Weight = STANDARD_NECKLACE_WEIGHT;
	}elseif (isFoulard($StockID)){
		$Weight = STANDARD_FOULARD_WEIGHT;
	}elseif (isFaceMask($StockID)){
		$Weight = STANDARD_FACEMASK_WEIGHT;
	}elseif (isJewelleryBox($StockID)){
		$Weight = STANDARD_JEWEL_BOX_WEIGHT;
	}elseif (isJewelleryRoll($StockID)){
		$Weight = STANDARD_JEWEL_ROLL_WEIGHT;
	}elseif (isBag($StockID)){
		$Weight = STANDARD_BAG_WEIGHT;
	}elseif (isPlasticBag($StockID)){
		$Weight = STANDARD_BAG_WEIGHT;
	}elseif (isTali($StockID)){
		$Weight = STANDARD_TALI_WEIGHT;
	}elseif (isKeyHolder($StockID)){
		$Weight = STANDARD_KEYHOLDER_WEIGHT;
	}else{
		$Weight = 0;
	}
	
	if($UpdateDB){
		$SQL = "UPDATE stockmaster 
				SET grossweight = " . $Weight . "
				WHERE stockid =	'" . $StockID . "'";
		$ErrMsg =_('Could not update the item weight because');
		$Result = DB_query($SQL,$ErrMsg);
	}
	return $Weight;
}

function UpdateVolume($StockID, $UpdateDB){
	if (isRing($StockID)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isToeRing($StockID)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isBead($StockID)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isBrooche($StockID)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isEarring($StockID)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isEarcuff($StockID)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isPiercing($StockID)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isBracelet($StockID)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isAnklet($StockID)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isPendant($StockID)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isNecklace($StockID)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isFaceMask($StockID)){
		$Length = BOX_M_LENGTH;
		$Width  = BOX_M_WIDTH;
		$Height = BOX_M_HEIGHT;
	}elseif (isJewelleryRoll($StockID)){
		$Length = BOX_L_LENGTH;
		$Width  = BOX_L_WIDTH;
		$Height = BOX_L_HEIGHT;
	}elseif (isJewelleryBox($StockID)){
		$Length = BOX_L_LENGTH;
		$Width  = BOX_L_WIDTH;
		$Height = BOX_L_HEIGHT;
	}elseif (isBag($StockID)){
		$Length = BOX_XL_LENGTH;
		$Width  = BOX_XL_WIDTH;
		$Height = BOX_XL_HEIGHT;
	}elseif (isPlasticBag($StockID)){
		$Length = BOX_XL_LENGTH;
		$Width  = BOX_XL_WIDTH;
		$Height = BOX_XL_HEIGHT;
	}elseif (isTali($StockID)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif (isKeyHolder($StockID)){
		$Length = BOX_S_LENGTH;
		$Width  = BOX_S_WIDTH;
		$Height = BOX_S_HEIGHT;
	}elseif ($StockID = "WKPC01"){
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
				WHERE stockid =	'" . $StockID . "'";
		$ErrMsg =_('Could not update the item volume and dimensions because');
		$Result = DB_query($SQL,$ErrMsg);
	}
	return $Volume;
}

function UpdateVolumeByPackaging($StockID, $Packaging, $UpdateDB){
	$TypePackaging = substr($Packaging, -1, 1);
	if ($StockID == "WKPC01"){
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
				WHERE stockid =	'" . $StockID . "'";
		$ErrMsg =_('Could not update the item volume and dimensions because');
		$Result = DB_query($SQL,$ErrMsg);
	}
	return $Volume;
}

function UpdatePackaging($StockID, $Category, $Brand, $UpdateDB){

	if (isRing($StockID)){
		$Packaging = "-S";
	}elseif (isToeRing($StockID)){
		$Packaging = "-S";
	}elseif (isBead($StockID)){
		$Packaging = "-S";
	}elseif (isBrooche($StockID)){
		$Packaging = "-M";
	}elseif (isEarring($StockID)){
		$Packaging = "-S";
	}elseif (isEarcuff($StockID)){
		$Packaging = "-S";
	}elseif (isPiercing($StockID)){
		$Packaging = "-S";
	}elseif (isBracelet($StockID)){
		$Packaging = "-M";
	}elseif (isAnklet($StockID)){
		$Packaging = "-M";
	}elseif (isPendant($StockID)){
		$Packaging = "-M";
	}elseif (isNecklace($StockID)){
		$Packaging = "-M";
	}elseif (isFaceMask($StockID)){
		$Packaging = "-M";
	}elseif (isJewelleryRoll($StockID)){
		$Packaging = "-L";
	}elseif (isTali($StockID)){
		$Packaging = "-S";
	}elseif (isKeyHolder($StockID)){
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

	if (isJewelleryBox($StockID) 
		OR isBag($StockID)
		OR isPlasticBag($StockID)){
		$Packaging = "NO-PACKAGING";
	}
	
	if (($Packaging != "") AND ($UpdateDB)){
		$SQL = "UPDATE stockmaster 
				SET klpackaging = '" . $Packaging . "'
				WHERE stockid =	'" . $StockID . "'";
		$ErrMsg =_('Could not update the packaging set because');
		$Result = DB_query($SQL,$ErrMsg);
	}
	
	return $Packaging;
}


function WebsiteCategorySilverJewellery($StockID, $Description, $Long, $Category){

	// It comes from Kapal-Laut Brand, so assume it is KAPAL_LAUT, let's try to be more precise
	$WebCat = KL_JEWELLERY;	

	if (ItemInList($Category, LIST_STOCK_CATEGORIES_GENERAL)){
		// if belongs to one of the general categories 
		$WebCat = GENERAL_ACCESSORIES;	
	}
	
	// filter some false positives
	if (ItemExcludedFromWebsite($StockID, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}
	
	// define subcategory
	if (($WebCat == KL_JEWELLERY) AND isRing($StockID)){
		if (isSlimRing($StockID)){
			$WebCat = KL_SLIMRINGS;
		}
		else{
			$WebCat = KL_RINGS;	
		}
	}
	if (($WebCat == KL_JEWELLERY) AND isToeRing($StockID)){
		$WebCat = KL_TOERINGS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isEarring($StockID)){
		$WebCat = KL_EARRINGS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isEarcuff($StockID)){
		$WebCat = KL_EARCUFFS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isPiercing($StockID)){
		$WebCat = KL_PIERCINGS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isBracelet($StockID)){
		$WebCat = KL_BRACELETS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isAnklet($StockID)){
		$WebCat = KL_ANKLETS;	
	}
	if (($WebCat == KL_JEWELLERY) AND isNecklace($StockID)){
		$WebCat = KL_NECKLACES;	
	}
	if (($WebCat == KL_JEWELLERY) AND isPendant($StockID)){
		$WebCat = KL_PENDANTS;	
	}	
	if (($WebCat == KL_JEWELLERY) AND isBrooche($StockID)){
		$WebCat = KL_BROOCHES;	
	}	
	if (($WebCat == KL_JEWELLERY) AND isJewelleryBox($StockID)){
		$WebCat = KL_JEWELLERY_BOXES;	
	}	
	if (($WebCat == GENERAL_ACCESSORIES) AND isFaceMask($StockID)){
		$WebCat = GE_FACEMASKS;	
	}	
	if (($WebCat == GENERAL_ACCESSORIES) AND isJewelleryRoll($StockID)){
		$WebCat = GE_JEWELLERY_ROLLS;	
	}	
	return $WebCat; 
}

function WebsiteCategoryBlinkJewellery($StockID, $Description, $Long, $Category){

	// It comes from Blink Brand, so assume it is BLINK, let's try to be more precise
	$WebCat = BLINK_JEWELLERY;	

	if (ItemInList($Category, LIST_STOCK_CATEGORIES_GENERAL)){
		// if belongs to one of the general categories 
		$WebCat = GENERAL_ACCESSORIES;	
	}

	// filter some false positives
	if (ItemExcludedFromWebsite($StockID, $Category)){
		$WebCat = ITEM_EXCLUDED_FROM_WEBSITE;
	}

	// define subcategory
	if (($WebCat == BLINK_JEWELLERY) AND isRing($StockID)){
		$WebCat = BLINK_RINGS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isEarring($StockID)){
		$WebCat = BLINK_EARRINGS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isEarcuff($StockID)){
		$WebCat = BLINK_EARCUFFS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isBracelet($StockID)){
		$WebCat = BLINK_BRACELETS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isPiercing($StockID)){
		$WebCat = BLINK_PIERCINGS;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isNecklace($StockID)){
		$WebCat = BLINK_NECKLACES;	
	}
	if (($WebCat == BLINK_JEWELLERY) AND isPendant($StockID)){
		$WebCat = BLINK_PENDANTS;	
	}	
	if (($WebCat == BLINK_JEWELLERY) AND isBrooche($StockID)){
		$WebCat = BLINK_BROOCHES;	
	}	
	if (($WebCat == BLINK_JEWELLERY) AND isBag($StockID)){
		$WebCat = BAGS;	
	}	
	if (($WebCat == BLINK_JEWELLERY) AND isPlasticBag($StockID)){
		$WebCat = BAGS;	
	}	
	if (($WebCat == BLINK_JEWELLERY) AND isKeyHolder($StockID)){
		$WebCat = BLINK_KEYHOLDERS;	
	}	
	if (($WebCat == GENERAL_ACCESSORIES) AND isFaceMask($StockID)){
		$WebCat = GE_FACEMASKS;	
	}	
	if (($WebCat == GENERAL_ACCESSORIES) AND isJewelleryRoll($StockID)){
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
<?php

/**************************************************************************************************
			GENERAL KL FUNCTIONS
**************************************************************************************************/

function ListToArray($List, $Separator){
	$CleanUp = array("(", ")", "'");
	return explode($Separator, str_replace($CleanUp, "", $List));
}

function time_start(){
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$begintime = $time;
	return $begintime;
}

function time_finish($begintime){
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$endtime = $time;
	$Totaltime = ($endtime - $begintime);
	prnMsg('Script execution time: ' . locale_number_format($Totaltime,2) . ' seconds.','success');
}

function function_finish($begintime){
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$endtime = $time;
	$Totaltime = ($endtime - $begintime);
	prnMsg('Function execution time: ' . locale_number_format($Totaltime,3) . ' seconds.','info');
}

function CodeModel($stockid){
	return (substr($stockid, 0,6));
}

function isBead($stockid){
	return (substr($stockid, 2,2) == "BE");
}

function isBrooche($stockid){
	return (substr($stockid, 2,2) == "PI");
}

function isRing($stockid){
	return (substr($stockid, 2,2) == "AN");
}

function isSlimRing($stockid){
	return (substr($stockid, 0,4) == "JSAN");
}

function isToeRing($stockid){
	return (substr($stockid, 2,2) == "TR");
}

function isBracelet($stockid){
	return ((substr($stockid, 2,2) == "PU") OR (substr($stockid, 2,2) == "BR"));
}

function isAnklet($stockid){
	return (substr($stockid, 2,2) == "AK");
}

function isFaceMask($stockid){
	return (substr($stockid, 2,2) == "FM");
}

function isJewelleryBox($stockid){
	return (substr($stockid, 2,2) == "BX");
}

function isJewelleryRoll($stockid){
	return (substr($stockid, 2,2) == "JR");
}

function isPendant($stockid){
	return (substr($stockid, 2,2) == "PE");
}

function isNecklace($stockid){
	return ((substr($stockid, 2,2) == "NE") OR (substr($stockid, 0,4) == "ALCL"));
}

function isEarring($stockid){
	return (substr($stockid, 2,2) == "AR");
}

function isPiercing($stockid){
	return ((substr($stockid, 2,2) == "PC") AND (substr($stockid, 0,4) != "WKPC"));
}

function isPolishingCloth($stockid){
	return (substr($stockid, 0,4) == "WKPC");
}

function isEarcuff($stockid){
	return (substr($stockid, 2,2) == "CF");
}

function isPackagingBox($stockid){
	return (substr($stockid, 0,4) == "PKBX");
}

function isPackagingPaperInsideBox($stockid){
	return (substr($stockid, 0,4) == "PKKS");
}

function isPlasticBag($stockid){
	return ((substr($stockid, 0,4) == "BAPL") OR (substr($stockid, 0,4) == "BAGC"));
}

function isBag($stockid){
	return (substr($stockid, 2,2) == "BA");
}

function isFoulard($stockid){
	return (substr($stockid, 2,2) == "SC");
}

function isTali($stockid){
	return ((substr($stockid, 0,3) == "TM-") 
		OR (substr($stockid, 0,4) == "TA15"));
}

function isKeyHolder($stockid){
	return (substr($stockid, 2,2) == "KI");
}

function isFamily($stockid, $Family){
	return (substr($stockid, 0,2) == $Family);
}

function TypeOfItem($stockid){
	if (isRing($stockid)){
		$Type = "Ring";
	}elseif (isToeRing($stockid)){
		$Type = "ToeRing";
	}elseif (isBead($stockid)){
		$Type = "Bead";
	}elseif (isBrooche($stockid)){
		$Type = "Brooche";
	}elseif (isEarring($stockid)){
		$Type = "Earring";
	}elseif (isPiercing($stockid)){
		$Type = "Piercing";
	}elseif (isEarcuff($stockid)){
		$Type = "EarCuff";
	}elseif (isFaceMask($stockid)){
		$Type = "Face Mask";
	}elseif (isJewelleryRoll($stockid)){
		$Type = "Jewellery Roll";
	}elseif (isBracelet($stockid)){
		$Type = "Bracelet";
	}elseif (isAnklet($stockid)){
		$Type = "Anklet";
	}elseif (isPendant($stockid)){
		$Type = "Pendant";
	}elseif (isNecklace($stockid)){
		$Type = "Necklace";
	}elseif (isPlasticBag($stockid)){
		$Type = "Bag";
	}elseif (isBag($stockid)){
		$Type = "Bag";
	}elseif (isTali($stockid)){
		$Type = "Tali";
	}else{
		$Type = "Unknown";
	}
	return $Type;
}

function CodeModelRing($stockid){
	if (strlen($stockid) == 6){
		$CodeModel = $stockid;
	}else{
		if((substr($stockid, -2,1) == "0") 
			OR (substr($stockid, -2,1) == "1")
			OR (substr($stockid, -2,1) == "2")){
			// ring with sizes! We need to cut the 3 last characters -XX
			$CodeModel = (substr($stockid, 0,strlen($stockid)-3));
		}else{
			$CodeModel = $stockid;
		}
	}
	return $CodeModel;
}

function RingSize($stockid){
	if (strlen($stockid) == 6){
		$Size = "FR";
	}else{
		if((substr($stockid, -2,1) == "0") 
			OR (substr($stockid, -2,1) == "1")
			OR (substr($stockid, -2,1) == "2")){
			// ring with sizes! We need to get the 2 last characters -XX
			$Size = substr($stockid, strlen($stockid)-2,2);
		}else{
			$Size = "FR";
		}
	}
	return $Size;
}

function NumberSize($stockid){
	if (strlen($stockid) == 6){
		$Size = "NO SIZE";
	}else if((substr($stockid, -2,1) == "0") 
		OR (substr($stockid, -2,1) == "1")
		OR (substr($stockid, -2,1) == "2")
		OR (substr($stockid, -2,1) == "3")
		OR (substr($stockid, -2,1) == "4")
		OR (substr($stockid, -2,1) == "5")
		OR (substr($stockid, -2,1) == "6")
		OR (substr($stockid, -2,1) == "7")
		OR (substr($stockid, -2,1) == "8")
		OR (substr($stockid, -2,1) == "9")){
		// number sizes! We need to get the 2 last characters -XX
		$Size = substr($stockid, strlen($stockid)-2,2);
	} else{
		$Size = "NO SIZE";
	}
	return $Size;
}

function ClassicalSize($stockid){
	if (strlen($stockid) == 6){
		$Size = "NO SIZE";
	}else if (substr($stockid, -3,3) == "-XS"){
		$Size = "XS";
	}else if (substr($stockid, -2,2) == "-S"){
		$Size = "S";
	}else if (substr($stockid, -2,2) == "-M"){
		$Size = "M";
	}else if (substr($stockid, -2,2) == "-L"){
		$Size = "L";
	}else if (substr($stockid, -3,3) == "-XL"){
		$Size = "XL";
	}else{
		$Size = "NO SIZE";
	}
	return $Size;
}

function ItemCodeQOH($Stockid, $CodeDetail, $Where){
	$ErrMsg = 'Error in function ItemCodeQOH()';

	$SQL = "SELECT SUM(locstock.quantity) AS total
			FROM locstock,locations 
			WHERE locstock.loccode = locations.loccode ";

	if ($CodeDetail == 'CODE_FULL'){
		$SQL .= "AND  stockid = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($Stockid)){
			$SQL .= "AND stockid LIKE '". $Stockid ."%'";
		}else{
			$SQL .= "AND stockid = '". $Stockid ."'";
		}
	}else{
		$SQL .= "AND stockid LIKE '". $Stockid ."%'";
	}

	if ($Where == "ALL_SHOPS"){
		$SQL .= " AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . " "; 
	}elseif ($Where == "ALL_SHOPS_AND_ONLINE"){
		$SQL .= " AND locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . " "; 
	}elseif ($Where == "ALL"){
		$SQL .= " "; 
	}else{
		$SQL .= " AND locstock.loccode = '". $Where . "'"; 
	}

	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Qty = $MyRow['total'];
	}else{
		$Qty = 0;
	}
	return $Qty;
}

function ItemCodeQuantityInvoiced($Stockid,$FromDate,$ToDate,$Debtorno,$CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQuantityInvoiced()';

	if ($CodeDetail == 'CODE_FULL'){
		$WhereCondition = "AND salesorderdetails.stkcode = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($Stockid)){
			$WhereCondition = "AND salesorderdetails.stkcode LIKE '". $Stockid ."%'";
		}else{
			$WhereCondition = "AND salesorderdetails.stkcode = '". $Stockid ."'";
		}
	}else{
		$WhereCondition = "AND salesorderdetails.stkcode LIKE '". $Stockid ."%'";
	}

	$SQL = "SELECT SUM(salesorderdetails.qtyinvoiced)
			FROM salesorderdetails,
				salesorders
			WHERE salesorderdetails.orderno = salesorders.orderno ". 
				$WhereCondition ."
				AND salesorders.orddate >= '" . $FromDate . "'
				AND salesorders.orddate <= '" . $ToDate . "'";

				if ($Debtorno != ''){
		$SQL = $SQL . " AND salesorders.debtorno LIKE '". $Debtorno ."%'";
	}
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function ItemCodeAvgPriceInvoiced($Stockid,$FromDate,$ToDate,$Debtorno,$CodeDetail){
	$ErrMsg = 'Error in function ItemCodeAvgPriceInvoiced()';
	
	if ($CodeDetail == 'CODE_FULL'){
		$WhereCondition = "AND salesorderdetails.stkcode = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($Stockid)){
			$WhereCondition = "AND salesorderdetails.stkcode LIKE '". $Stockid ."%'";
		}else{
			$WhereCondition = "AND salesorderdetails.stkcode = '". $Stockid ."'";
		}
	}else{
		$WhereCondition = "AND salesorderdetails.stkcode LIKE '". $Stockid ."%'";
	}
	
	$SQL = "SELECT AVG(salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate)
			FROM salesorderdetails,
				salesorders,
				debtorsmaster,
				currencies 
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND currencies.currabrev = debtorsmaster.currcode ". 
				$WhereCondition ."
				AND salesorders.orddate >= '" . $FromDate . "'
				AND salesorders.orddate <= '" . $ToDate . "'";
	if ($Debtorno != ''){
		$SQL = $SQL . " AND salesorders.debtorno LIKE '". $Debtorno ."%'";
	}
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function ItemCodeQOO_PurchaseOrders($Stockid, $CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQOO_PurchaseOorders()';

	if ($CodeDetail == 'CODE_FULL'){
		$WhereCondition = "WHERE purchorderdetails.itemcode = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($Stockid)){
			$WhereCondition = "WHERE purchorderdetails.itemcode LIKE '". $Stockid ."%'";
		}else{
			$WhereCondition = "WHERE purchorderdetails.itemcode = '". $Stockid ."'";
		}
	}else{
		$WhereCondition = "WHERE purchorderdetails.itemcode LIKE '". $Stockid ."%'";
	}

	$SQL="SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd) AS QtyOnOrder
		FROM purchorders
			INNER JOIN purchorderdetails
				ON purchorders.orderno=purchorderdetails.orderno ". 
			$WhereCondition ."
			AND purchorderdetails.completed = 0
			AND purchorders.status<>'Cancelled'
			AND purchorders.status<>'Pending'
			AND purchorders.status<>'Rejected'
			AND purchorders.status<>'Completed'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function ItemCodeQOO_WorkOrders($Stockid,$CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQOO_WorkOorders()';

	if ($CodeDetail == 'CODE_FULL'){
		$WhereCondition = "AND woitems.stockid = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($Stockid)){
			$WhereCondition = "AND woitems.stockid LIKE '". $Stockid ."%'";
		}else{
			$WhereCondition = "AND woitems.stockid = '". $Stockid ."'";
		}
	}else{
		$WhereCondition = "AND woitems.stockid LIKE '". $Stockid ."%'";
	}

	$SQL="SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
			FROM woitems
				INNER JOIN workorders
					ON woitems.wo=workorders.wo
			WHERE workorders.closed=0 ". 
				$WhereCondition ." ";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function locale_number_format_zero_blank($num,$dec){
	if($num == 0){
		return '';
	}else{
		return locale_number_format($num,$dec);
	}
}

function locale_number_format_kpi($num){
	if(abs($num) >= 1000){
		return locale_number_format($num,0);
	}elseif(abs($num) >= 10){
		return locale_number_format($num,1);
	}else{
		return locale_number_format($num,2);
	}
}


function StartEvenOrOddRow($k){
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	return $k;
}

function StartSameColourRow($k){
	if ($k == 1) {
		echo '<tr class="OddTableRows">';
	} else {
		echo '<tr class="EvenTableRows">';
	}
	return $k;
}

function getDirectoryTree($outerDir){ 
	$dirs = FALSE; 
	if (is_dir($outerDir)){
		$Directory = scandir( $outerDir );
		if (is_array($Directory)){
			$dirs = array_diff( $Directory, Array( ".", ".." ) ); 
		}
	}
   return $dirs; 
} 

function ItemInList($Item, $List){
	// http://www.php.net/manual/en/function.strpos.php for details on ===	
	if (strpos(strtolower($List), strtolower($Item)) === FALSE){
		return false;
	}else{
		return true;
	}
}

function NumberOfItemsInList($List){
	// https://www.php.net/manual/en/function.substr-count.php 	
	return substr_count($List, ',') + 1;  
}

function CapitalizeName($string){
// copied from http://www.media-division.com/correct-name-capitalization-in-php/
	$word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
	$lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
	$uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');
 
	$string = strtolower($string);
	foreach ($word_splitters as $delimiter)
	{ 
		$words = explode($delimiter, $string); 
		$newwords = array(); 
		foreach ($words as $word)
		{ 
			if (in_array(strtoupper($word), $uppercase_exceptions))
				$word = strtoupper($word);
			else
			if (!in_array($word, $lowercase_exceptions))
				$word = ucfirst($word); 
 
			$newwords[] = $word;
		}
 
		if (in_array(strtolower($delimiter), $lowercase_exceptions))
			$delimiter = strtolower($delimiter);
 
		$string = join($delimiter, $newwords); 
	} 
	return $string; 
}

function ReviseEmailAddress($email){
	$email = strtolower(trim($email));
	$atposition = strpos($email,'@');
	$domain = substr($email,$atposition+1);
	$SQL = "SELECT fixeddomain
			FROM klrevisedemaildomains
			WHERE wrongdomain = '" . $domain . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$revisedemail = substr($email,0,$atposition+1).$MyRow['fixeddomain'] ;
	}else{
		// seems OK. At least we can't detect an error
		$revisedemail = $email;
	}
	return $revisedemail; 
}

function CleanStringForWebERP($s){
	$s = str_replace("'", " ", $s);
	return $s;
}

function GetAreaFromCustomer($CustomerCode){
	$ErrMsg = 'Error in function GetAreaFromCustomer()';
	$SQL = "SELECT area
			FROM custbranch
			WHERE custbranch.debtorno ='". $CustomerCode . "'
			AND custbranch.branchcode = '" . $CustomerCode . "'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function GetCurrencyFromCustomer($CustomerCode){
	$ErrMsg = 'Error in function GetCurrencyFromCustomer()';
	$SQL = "SELECT currcode
			FROM debtorsmaster
			WHERE debtorsmaster.debtorno ='". $CustomerCode . "'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function GetOnlinePartnerFromArea($Area){
	return "ONLINEPTAD";
}

function GetCategoryNameFromCode($CategoryId){
	$ErrMsg = 'Error in function GetCategoryNameFromCode()';
	$SQL="SELECT categorydescription FROM stockcategory WHERE categoryid='" . $CategoryId . "'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function GetDefaultLocationFromUser($UserId){
	$ErrMsg = 'Error in function GetDefaultLocationFromUser()';
	$SQL = "SELECT defaultlocation FROM www_users WHERE userid='".$UserId."'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function GetLocationNameFromCode($LocCode){
	$ErrMsg = 'Error in function GetLocationNameFromCode()';
	$SQL="SELECT locationname FROM locations WHERE loccode='" . $LocCode . "'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function GetItemDescriptionFromCode($Stockid){
	$ErrMsg = 'Error in function GetItemDescriptionFromCode()';
	$SQL="SELECT description FROM stockmaster WHERE stockid='" . $Stockid . "'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function GetTotalItemsChangingPrice(){
	$ErrMsg = 'Error in function GetTotalItemsChangingPrice()';
	$SQL="SELECT COUNT(*) FROM stockmaster WHERE klchangingprice='1'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function GetTotalItemsMovingToDiscount($DiscountLevel){
	$ErrMsg = 'Error in function GetTotalItemsMovingToDiscount()';
	$SQL="SELECT COUNT(*) FROM stockmaster WHERE klmovingdiscount".$DiscountLevel."='1'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function NegativeNumber($Value){
	// be sure the value returned is negative
	if ($Value > 0){
		$Value = -$Value;
	}
	return $Value;
}

function PositiveNumber($Value){
	// be sure the value returned is positive
	if ($Value < 0){
		$Value = -$Value;
	}
	return $Value;
}

function FindReasonOfReturn($ReasonCode){
	$ErrMsg = 'Error in function FindReasonOfReturn()';
	$SQL="SELECT reasonname FROM returnitemreasons WHERE reasonid='" . $ReasonCode . "'";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function ConvertExcelDate($cell, $format = 'Y-m-d'){
	// converts an excel cell into a valid date to work with
	if(PHPExcel_Shared_Date::isDateTime($cell)) {
		$ConvertedDate = date($format,PHPExcel_Shared_Date::ExcelToPHP($cell->getCalculatedValue()));                          
	}else{
		$ConvertedDate = '0000-00-00';                          
	}
	return $ConvertedDate;
}

function AdjustBulatan($Amount, $RoundTo){
	return (ceil($Amount/$RoundTo)*$RoundTo)-$Amount;
}

function InsertIntoGLTrans($Type, $Typeno, $Trandate, $Period, $Account, $Narrative, $Amount, $Tag, $ErrCode){
	$SQL = "INSERT INTO gltrans 
				(type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount)
			VALUES 
				('" . $Type . "',
				'" . $Typeno . "',
				'" . $Trandate . "',
				'" . $Period . "',
				'" . $Account . "',
				'" . $Narrative . "',
				'" . $Amount . "')";
	$ErrMsg = 'CRITICAL ERROR! WRITE THIS CODE AND CALL THE OFFICE IMMEDIATELY: '. $ErrCode;		
	$DbgMsg = 'SQL to insert GLTrans record: ';
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function GLAccountBelongsTo($Account){
	if (ItemInList("AD", $Account)){
		$Company = "PTADU";
	}else if (ItemInList("SM", $Account)){
		$Company = "PTSMH";
	}else if (ItemInList("BB", $Account)){
		$Company = "PTBB";
	}else if (ItemInList("IK", $Account)){
		$Company = "POIK";
	}else if (ItemInList("PI", $Account)){
		$Company = "POPI";
	}else{
		$Company = "CASH";
	}
	return $Company;
}

function CreateConsignmentInvoiceNumber($CompanyFrom, $CompanyTo, $EndDate){
	return $CompanyFrom . '-' . $CompanyTo . '-' . $EndDate;
}

function FindWebsiteBrand($StockID, $Category, $Description){
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_ALL_DISCOUNT)){
		// if belongs to one of the KL categories, so Brand is KL
		$Brand = 1;	
	}else if (ItemInList($Category, LIST_STOCK_CATEGORIES_BLINK_INCLUDING_ALL_DISCOUNT)){
		// if belongs to one of the BL categories, so Brand is BL
		$Brand = 2;	
	}else if (ItemInList($Category, LIST_STOCK_CATEGORIES_GENERAL_INCLUDING_ALL_DISCOUNT)){
		// if belongs to one of the General categories, so Brand is KL
		$Brand = 1;	
	}else{
		//should be a discounted item, we keep the previous brand if still available, otherwise we continue messing around
		$SQL = "SELECT manufacturers_id
				FROM salescatprod 
				WHERE stockid = '" . $Stockid . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			// assign the current brand
			$MyRow = DB_fetch_array($Result);
			$Brand = $MyRow['manufacturers_id'];	
		}else{
			// we check the description, if we get any info
				if (mb_stristr($Description, "silver") != FALSE){
					// description contains "silver", should be KL
					$Brand = 1;	
				}else{
					// description does not contain "silver", should be Blink
					$Brand = 2;	
				}			
		}
	}
	return $Brand;
}

function ProcessPaymentOnlineOrder($OrderNo, $PaymentCode, $CustomerCode, $TotalAmount){

	// so far... only in IDR
	if (GetCurrencyFromCustomer($CustomerCode) == "IDR"){
		$FunctionalExRate = 1;
		$ExRate = 1;
		$Currency = "IDR";
	}else{
		return "ERROR";
	}

	$Area = GetAreaFromCustomer($CustomerCode);
	$OnlinePartner = GetOnlinePartnerFromArea($Area);

	if ($PaymentCode != "MANUAL_MARKETPLACE") {
		// apply the proper payment
		// let's find the accounts, commission, etc to charge to the different payment codes
		$SQLAccounts = "SELECT onlinepartnercode,
					accounttransfermandiri,
					accounttransferbca,
					accounttransferdanamon,
					accountdokuidr,
					accountdokucomissionidr,
					comissionflatdoku,
					comissionccdoku,
					accountxenditidr,
					accountxenditcomissionidr,
					comissionxenditflattransfer,
					comissionxenditflatcc,
					comissionxenditpercentcc,
					accountmidtransidr,
					accounttokopediaidr,
					accounttokopediacomissionidr,
					accountshopeeidr,
					accountshopeecomissionidr,
					accountlazadaidr,
					accountlazadacomissionidr,
					accountcomissionppn,
					comissiontokopediapercent,
					comissiontokopediafreeshippingperitempercent,
					comissiontokopediafreeshippingperitemmaximum,
					comissionshopeepercent,
					comissionshopeefreeshippingperitempercent,
					comissionshopeefreeshippingperitemmaximum,
					comissionlazadapercent
				FROM klonlinepartners
				WHERE klonlinepartners.onlinepartnercode = '" . $OnlinePartner . "'";
		$ErrMsg ='Could not get the GL Transfers and Commissions for online shop payments because';
		$ResultAccounts = DB_query($SQLAccounts,$ErrMsg);
		if(DB_num_rows($ResultAccounts) != 0){
			$MyRowAccounts = DB_fetch_array($ResultAccounts);
			if ($PaymentCode == "bank_mandiri"){
				// bank Mandiri direct transfer has no commissions 
				$GLAccountTransfer = $MyRowAccounts['accounttransfermandiri'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			}elseif ($PaymentCode == "bank_bca"){
				// bank bca direct transfer has no commissions 
				$GLAccountTransfer = $MyRowAccounts['accounttransferbca'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			}elseif ($PaymentCode == "bank_danamon"){
				// bank Danamon direct transfer has no commissions
				$GLAccountTransfer = $MyRowAccounts['accounttransferdanamon'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			}elseif  ($PaymentCode == "snap"){
				// MidTrans has commissions but we can't integrate them. We account full order, later manually we process commissions
				$GLAccountTransfer = $MyRowAccounts['accountmidtransidr'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			}elseif  ($PaymentCode == "xenditmandiriva"){
				// Xendit transfer via mandiri has commissions
				$GLAccountTransfer = $MyRowAccounts['accountxenditidr'];
				$GLAccountCommission = $MyRowAccounts['accountxenditcomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$Commission = round($MyRowAccounts['comissionxenditflattransfer'],0);
			}elseif  ($PaymentCode == "xenditcc"){
				// Xendit transfer via CC has commissions
				$GLAccountTransfer = $MyRowAccounts['accountxenditidr'];
				$GLAccountCommission = $MyRowAccounts['accountxenditcomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$Commission = round(($MyRowAccounts['comissionxenditflatcc'] + ($TotalAmount * ($MyRowAccounts['comissionxenditpercentcc']/100))) ,0);
			}elseif  ($PaymentCode == "tokopedia"){
				// Tokopedia payments  has commissions
				$GLAccountTransfer = $MyRowAccounts['accounttokopediaidr'];
				$GLAccountCommission = $MyRowAccounts['accounttokopediacomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$CommissionTokopediaPercent = $MyRowAccounts['comissiontokopediapercent'];
				$CommissionTokopediaFreeShippingPerItem = $MyRowAccounts['comissiontokopediafreeshippingperitempercent'];
				$CommissionTokopediaFreeShippingMaximum = $MyRowAccounts['comissiontokopediafreeshippingperitemmaximum'];
				$Commission = CalculateCommissionTokopedia($CustomerCode, 
															$OrderNo, 
															$TotalAmount,
															$CommissionTokopediaPercent,
															$CommissionTokopediaFreeShippingPerItem,
															$CommissionTokopediaFreeShippingMaximum);
			}elseif  ($PaymentCode == "shopee"){
				// Shopee payments  has commissions
				$GLAccountTransfer = $MyRowAccounts['accountshopeeidr'];
				$GLAccountCommission = $MyRowAccounts['accountshopeecomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$CommissionShopeePercent = $MyRowAccounts['comissionshopeepercent'];
				$CommissionShopeeFreeShippingPerItem = $MyRowAccounts['comissionshopeefreeshippingperitempercent'];
				$CommissionShopeeFreeShippingMaximum = $MyRowAccounts['comissionshopeefreeshippingperitemmaximum'];
				$Commission = CalculateCommissionShopee($CustomerCode, 
														$OrderNo, 
														$TotalAmount,
														$CommissionShopeePercent,
														$CommissionShopeeFreeShippingPerItem,
														$CommissionShopeeFreeShippingMaximum);
			}elseif  ($PaymentCode == "lazada"){
				// Lazada payments  has commissions
				$GLAccountTransfer = $MyRowAccounts['accountlazadaidr'];
				$GLAccountCommission = $MyRowAccounts['accountlazadacomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$CommissionLazadaPercent = $MyRowAccounts['comissionlazadapercent'];
				$Commission = CalculateCommissionLazada($CustomerCode, 
														$OrderNo, 
														$TotalAmount,
														$CommissionLazadaPercent);
			}
			$CommissionPPN = round($Commission * PPN_PERCENT / 100, 0);
			$NetAmount = $TotalAmount - $Commission - $CommissionPPN;
		}

		$Result = DB_Txn_Begin();

		$BatchNo = GetNextTransNo(12);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
		$Narrative = 'Online ' . $OrderNo . ' ' . $PaymentCode;
		$BankTransType = "Transfer";

		$SQL = "INSERT INTO debtortrans (transno,
										type,
										debtorno,
										branchcode,
										order_,
										trandate,
										inputdate,
										prd,
										reference,
										tpe,
										rate,
										ovamount,
										ovdiscount,
										invtext,
										salesperson)
				VALUES (
					'" . $BatchNo . "',
					12,
					'" . $CustomerCode . "',
					'',
					'" . $OrderNo . "',
					CURRENT_DATE,
					CURRENT_DATE,
					'" . $PeriodNo . "',
					'" . $Narrative . "',
					'',
					'" . ($FunctionalExRate*$ExRate) . "',
					'" . -$TotalAmount . "',
					'" . 0 . "',
					'" . $Narrative. "',
					''
				)";
				
		$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
		$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		$SQL = "UPDATE debtorsmaster
					SET lastpaiddate = CURRENT_DATE,
					lastpaid='" . $TotalAmount ."'
				WHERE debtorsmaster.debtorno='" . $CustomerCode . "'";

		$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
		$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

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
			VALUES (
				12,
				'" . $BatchNo . "',
				'" . $GLAccountTransfer . "',
				'" . $Narrative . "',
				'" . $ExRate . "',
				'" . $FunctionalExRate . "',
				CURRENT_DATE,
				'" . $BankTransType . "',
				'" . ($NetAmount * $FunctionalExRate * $ExRate) . "',
				'" . $Currency . "'
			)";
		$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
		$ErrMsg = _('Cannot insert a bank transaction');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		$SQL="INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
			VALUES (
				12,
				'" . $BatchNo . "',
				CURRENT_DATE,
				'" . $PeriodNo . "',
				'" . $GLAccountTransfer . "',
				'" . $Narrative . "',
				'" . $NetAmount . "'
			)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction from the bank account debit was');
		$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		if ($Commission > 0){
			$SQL="INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
									VALUES (
										12,
										'" . $BatchNo . "',
										CURRENT_DATE,
										'" . $PeriodNo . "',
										'" . $GLAccountCommission . "',
										'" . $Narrative . "',
										'" . $Commission . "'
									)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction from the commission was');
			$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		}

		if ($CommissionPPN > 0){
			$SQL="INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
									VALUES (
										12,
										'" . $BatchNo . "',
										CURRENT_DATE,
										'" . $PeriodNo . "',
										'" . $GLAccountCommissionPPN . "',
										'" . $Narrative . "',
										'" . $CommissionPPN . "'
									)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction from the PPN commission was');
			$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		}

		$SQL="INSERT INTO gltrans ( type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
								VALUES (
									12,
									'" . $BatchNo . "',
									CURRENT_DATE,
									'" . $PeriodNo . "',
									'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
									'" . $Narrative . "',
									'" . -$TotalAmount . "'
									)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
		$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);			

		// update the salesorder table, from quotation to confirmed order
		if  (($PaymentCode == "tokopedia") OR 
			 ($PaymentCode == "lazada") OR 
			 ($PaymentCode == "shopee")){
			// in case paid by marketplace (so after order is closed and shipment, we need to mark it as "received somehow", so we use klpaidcash
			$SQL = "UPDATE salesorders
						SET klpaidcash = '" . $TotalAmount . "',
							quotation = '0',
							confirmeddate = CURRENT_DATE
					WHERE salesorders.orderno='" . $OrderNo . "'";
		}else{
			$SQL = "UPDATE salesorders
						SET quotation = '0',
							confirmeddate = CURRENT_DATE
					WHERE salesorders.orderno='" . $OrderNo . "'";
		}
		$DbgMsg = _('The SQL that failed to update the quotation flag of the sales order was');
		$ErrMsg = _('Cannot update the quotation flag of the sales order because');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		if (($CustomerCode == "WEB-KL-IDR") OR ($CustomerCode == "WEB-WH-IDR")) {
			// online sale from our website, we must update the status of the order in OpenCart
			$OnlineOrderNo = GetOnlineOrderNoFromWeberp($OrderNo);
			$ReasonChangeStatusId = "webERP --> Payment received by " . $PaymentCode . " Amount = " . $TotalAmount;  
			UpdateOpenCartOrderStatus($OnlineOrderNo, OPENCART_ORDER_STATUS_PROCESSING, 1, "", "", $ReasonChangeStatusId);
			UpdateOpenCartOrderPayment($OnlineOrderNo);
		}

		$Result = DB_Txn_Commit();

	}else{
		// marketplace customers MANUAL_MARKETPLACE, just mark the order as paid
		// accounting has been done manually
		$Result = DB_Txn_Begin();

		$SQL = "UPDATE salesorders
					SET klpaidcash = '" . $TotalAmount . "'
				WHERE salesorders.orderno='" . $OrderNo . "'";
		$DbgMsg = _('The SQL that failed to update the payment flag of the sales order was');
		$ErrMsg = _('Cannot update the payment flag of the sales order because');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		$Result = DB_Txn_Commit();
	}
	return $Result;
}

function ItemImagesURL($StockId, $NumberOfImage, $PackagingAlreadyFound, $TypeOfPackaging){
	$PackagingImage =  FALSE;
	if ($NumberOfImage == 1){
		// main image
		$URL = PATH_TO_CATALOG_IMAGES . $StockId.'.jpg';
	}elseif ($NumberOfImage == 999){
		// last image of the lot MUST be a packaging image if still not found a packaging image
		if (($TypeOfPackaging != "") AND ($TypeOfPackaging != "NO-PACKAGING")){
			$URL = PATH_TO_CATALOG_PACKAGING_IMAGES . $TypeOfPackaging.'.jpg';
			$PackagingImage =  TRUE;
		}else{
			$URL = "";
		}
	}else{
		// extra images
		$NumberOfImage = $NumberOfImage - 1;
		if (file_exists($_SESSION['part_pics_dir'] . '/' . $StockId.'.'.$NumberOfImage.'.jpg')){
			$URL = PATH_TO_CATALOG_IMAGES . $StockId.'.'.$NumberOfImage.'.jpg';
		}else{
			if (($TypeOfPackaging != "") AND ($TypeOfPackaging != "NO-PACKAGING")){
				$URL = PATH_TO_CATALOG_PACKAGING_IMAGES . $TypeOfPackaging.'.jpg';
				$PackagingImage =  TRUE;
			}else{
				$URL = "";
			}
		}
	}
	return array($URL,$PackagingImage);
}

function DataExistsInWebERP($Table, $f1, $v1, $f2 = '', $v2 = ''){
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
	$ErrMsg =_('Could not check existence of data in webERP because');
	$Result = DB_query($SQL,$ErrMsg);

	if(DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Exists = ($MyRow[0] > 0);
	}else{
		$Exists = false;
	}
	return $Exists;
}

function InsertKPI($Class, $Concept, $Value){
	$Date = date('Y-m-d');
	if (!DataExistsInWebERP('klkpi', 'date', $Date, 'concept', $Concept)){
		$SQL = "INSERT INTO klkpi 
				(date,
				class,
				concept,
				value)
			VALUES 
				('" . $Date . "',
				'" . $Class . "',
				'" . $Concept . "',
				'" . $Value . "')";
		$ErrMsg = 'Error in function InsertKPI()';
		$DbgMsg = 'SQL to insert klkpi record: ';
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	}
}

function NumberOfShops($ShopType){
	$SQL="SELECT COUNT(*)
		FROM locations
		WHERE typeloc = '" . $ShopType . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return 0;
	}
}

function NumberOfRegularShopsSellingDiscount($ShopType){
	if ($ShopType == "SHOPKL"){
		$Categories = "AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT_ONLY_DISCOUNT . "";
	} else if ($ShopType == "SHOPBL"){
		$Categories = "AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK_ONLY_DISCOUNT . "";
	} else {
		return 0;
	}
	
	$SQL="SELECT COUNT(DISTINCT(locations.loccode))
		FROM locations,locstock,stockmaster
		WHERE locations.loccode = locstock.loccode
			AND locstock.stockid = stockmaster.stockid
			AND locations.typeloc = '" . $ShopType . "'
			AND locstock.reorderlevel > 0 " .
			$Categories;
			
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	}else{
		return 0;
	}
}

function DeleteWeberpUser($SelectedUser, $AdminRole){
	if($AllowDemoMode AND $SelectedUser == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be deleted'),'error');
	}elseif ($SelectedUser == "Ricard"){
		prnMsg('User '. $SelectedUser . ' cannot be deleted as is a super user','error');
	}elseif ((($SelectedUser == "Laia")
				OR ($SelectedUser == "Ike1")	
				OR ($SelectedUser == "Fathus")	
				OR ($SelectedUser == "RiaResti")	
				OR ($SelectedUser == "Cicik")	
				OR ($SelectedUser == "Revi"))
			AND (!$AdminRole )){
		prnMsg('You do not have enough rights to delete user '. $SelectedUser ,'error');
	}else{
		$SQL="SELECT userid FROM audittrail where userid='" . $SelectedUser ."'";
		$Result=DB_query($SQL);
		if(DB_num_rows($Result)!=0) {
			prnMsg(_('Cannot delete user as entries still exist in the audit trail'), 'error');
		} else {
			$SQL="DELETE FROM locationusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Location - User could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM glaccountusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The GL Account - User could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM bankaccountusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Bank Accounts - User could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM purchorderauth WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Purchase Orders Authority could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM www_users WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			KLSendEmail("UserDeleted", "Silent",$_SESSION['UserID'], $SelectedUser);
			prnMsg('User ' . $SelectedUser . ' deleted', 'success');
		}
		unset($SelectedUser);
	}
}

function GetDayNameFromWeekDay($WeekDay){
	if ($WeekDay == 1){
		return "Sunday";
	}elseif ($WeekDay == 2){
		return "Monday";
	}elseif ($WeekDay == 3){
		return "Tuesday";
	}elseif ($WeekDay == 4){
		return "Wednesday";
	}elseif ($WeekDay == 5){
		return "Thursday";
	}elseif ($WeekDay == 6){
		return "Friday";
	}elseif ($WeekDay == 7){
		return "Saturday";
	}
}

function GetLastKPIValue($Class,$Concept){
	$SQL = "SELECT value
			FROM klkpi
			WHERE class = '".$Class."'
				AND concept LIKE '".$Concept."'
			ORDER BY date DESC
			LIMIT 1";
	$Result = DB_query($SQL);		
	$MyRow = DB_fetch_array($Result);
	return $MyRow['value'];
	
}

function DaysBetween($date1, $date2) {
  $start = strtotime($date1);
  $end = strtotime($date2);
  $days_between = ceil(abs($end - $start) / 86400);
  return $days_between;
}

function TotalItemsToBeReceivedByPO($Brand){
	$ErrMsg = 'Error in function TotalItemsToBeReceivedByPO()';

	if ($Brand == "SHOPKL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP ."";
	}else if ($Brand == "SHOPBL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP ."";
	}else{
		return 0;	
	} 

	$SQL="SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS pending
		FROM purchorders 
		INNER JOIN purchorderdetails
			ON purchorders.orderno = purchorderdetails.orderno
		INNER JOIN stockmaster
			ON stockmaster.stockid = purchorderdetails.itemcode
		WHERE purchorderdetails.completed=0
			AND purchorders.status IN ('Authorised', 'Printed', 'Pending')" . 
			$Operator1." ";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function TotalItemsToBeReceivedByWO($Brand){
	$ErrMsg = 'Error in function TotalItemsToBeReceivedByWO()';

	if ($Brand == "SHOPKL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP ."";
	}else if ($Brand == "SHOPBL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP ."";
	}else{
		return 0;	
	} 

	$SQL="SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS pending
		FROM woitems 
		INNER JOIN stockmaster
			ON stockmaster.stockid = woitems.stockid
		INNER JOIN workorders
			ON workorders.wo = woitems.wo
		WHERE workorders.closed = 0
			AND woitems.qtyreqd > woitems.qtyrecd ".
			$Operator1." ";
	$Result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($Result);
	return $Row['0'];
}

function TotalModels($Brand){
	$ErrMsg = 'Error in TotalModels()';

	if ($Brand == "SHOPKL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP ."";
	}else if ($Brand == "SHOPBL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP ."";
	}else{
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."";
	} 

	$SQL =	"SELECT COUNT(stockmaster.stockid) AS totalmodels
			FROM stockmaster
			WHERE discontinued = 0 " . 
				$Operator1 ."";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow['0'];
}
 
function TotalItems($Brand){
	$ErrMsg = 'Error in TotalItems()';

	if ($Brand == "SHOPKL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP ."";
	}else if ($Brand == "SHOPBL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP ."";
	}else{
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."";
	} 

	$SQL =	"SELECT SUM(locstock.quantity) AS totalitems
			FROM locstock, stockmaster
			WHERE stockmaster.stockid = locstock.stockid " . 
				$Operator1 ."";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow['0'];
}

function TotalDisplayItems($Brand){
	$ErrMsg = 'Error in TotalDisplayItems()';

	if ($Brand == "SHOPKL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT ."";
	}else if ($Brand == "SHOPBL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK ."";
	}else{
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."";
	} 

	$SQL =	"SELECT COUNT(locstock.quantity) AS displayitems
			FROM locstock, stockmaster
			WHERE stockmaster.stockid = locstock.stockid 
				AND locstock.quantity >= 1" . 
				$Operator1 ."";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow['0'];
}

function NumItemsSoldPerBrand($Brand, $FromDate, $ToDate){
	$ErrMsg = 'Error in DailySoldItems()';
	if ($Brand == "SHOPKL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT ."";
	}else if ($Brand == "SHOPBL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK ."";
	}else{
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."";
	} 

	$SQL =	"SELECT SUM(salesorderdetails.qtyinvoiced) AS solditems
			FROM salesorderdetails, stockmaster
			WHERE stockmaster.stockid = salesorderdetails.stkcode 
				AND salesorderdetails.itemdue >= '" . $FromDate . "'
				AND salesorderdetails.itemdue <= '" . $ToDate . "'" . 
				$Operator1 ."";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return ($MyRow['0']);
}

function BrandTextFromCode($Brand){
	if ($Brand == "SHOPKL"){
		$BrandText = "Kapal-Laut";
	}elseif ($Brand == "SHOPBL"){
		$BrandText = "Blink";
	}elseif ($Brand == "SHOPOU"){
		$BrandText = "Outlet";
	}else{
		$BrandText = "ERROR";
	}
	return $BrandText;
}

?>

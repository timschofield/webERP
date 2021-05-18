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
	$totaltime = ($endtime - $begintime);
	prnMsg('Script execution time: ' . locale_number_format($totaltime,3) . ' seconds.','success');
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

function isEarcuff($stockid){
	return (substr($stockid, 2,2) == "CF");
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

function CreateTextSize($stockid, $language, $IncludeTextDescription){
	$Size = ClassicalSize($stockid);
	if ($Size == "NO SIZE"){
		if (isRing($stockid)){
			$Size = RingSize($stockid);
		}else{
			$Size = NumberSize($stockid);
		}		
	} 
	if ($IncludeTextDescription){
		if ($Size == "NO SIZE"){
			$TextSize = "";
		}else if ($Size == "FR"){
			$TextSize = "Free Size";
		}else{
			if ($language == "ID"){
				$TextSize = "Ukuran: ". $Size;
			}else{
				$TextSize = "Size: ". $Size;
			}
		}
	}else{
		if (($Size == "NO SIZE") OR ($Size == "FR")){
			$TextSize = "";
		}else{
			$TextSize = " - Size " . $Size;
		}
	}
	return $TextSize;
}

function ItemCodeQOH($Stockid,$CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQOH()';

	if ($CodeDetail == 'CodeFull'){
		$WhereCondition = "WHERE stockid = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CodeFullWithRings'){
		if (isRing($Stockid)){
			$WhereCondition = "WHERE stockid LIKE '". $Stockid ."%'";
		}else{
			$WhereCondition = "WHERE stockid = '". $Stockid ."'";
		}
	}else{
		$WhereCondition = "WHERE stockid LIKE '". $Stockid ."%'";
	}
	
	$SQL = "SELECT SUM(quantity)
			FROM locstock " .
			$WhereCondition ;
	$result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($result);
	return $Row['0'];
}

function ItemCodeQuantityInvoiced($Stockid,$FromDate,$ToDate,$Debtorno,$CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQuantityInvoiced()';

	if ($CodeDetail == 'CodeFull'){
		$WhereCondition = "AND salesorderdetails.stkcode = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CodeFullWithRings'){
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
	$result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($result);
	return $Row['0'];
}

function ItemCodeAvgPriceInvoiced($Stockid,$FromDate,$ToDate,$Debtorno,$CodeDetail){
	$ErrMsg = 'Error in function ItemCodeAvgPriceInvoiced()';
	
	if ($CodeDetail == 'CodeFull'){
		$WhereCondition = "AND salesorderdetails.stkcode = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CodeFullWithRings'){
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
	$result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($result);
	return $Row['0'];
}

function ItemCodeQOO_PurchaseOrders($Stockid, $CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQOO_PurchaseOorders()';

	if ($CodeDetail == 'CodeFull'){
		$WhereCondition = "WHERE purchorderdetails.itemcode = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CodeFullWithRings'){
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
	$result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($result);
	return $Row['0'];
}

function ItemCodeQOO_WorkOrders($Stockid,$CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQOO_WorkOorders()';

	if ($CodeDetail == 'CodeFull'){
		$WhereCondition = "AND woitems.stockid = '". $Stockid ."'";
	}elseif ($CodeDetail == 'CodeFullWithRings'){
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
	$result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($result);
	return $Row['0'];
}

function locale_number_format_zero_blank($num,$dec){
	if($num == 0){
		return '';
	}else{
		return locale_number_format($num,$dec);
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
function getDirectoryTree( $outerDir , $x){ 
    $dirs = array_diff( scandir( $outerDir ), Array( ".", ".." ) ); 
    return $dirs; 
} 

function ItemInList($Item, $List){
	// http://www.php.net/manual/en/function.strpos.php for details on ===	
	if (strpos($List, $Item) === FALSE){
		return false;
	}else{
		return true;
	}
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
	$sql = "SELECT fixeddomain
			FROM klrevisedemaildomains
			WHERE wrongdomain = '" . $domain . "'";
	$result = DB_query($sql);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$revisedemail = substr($email,0,$atposition+1).$myrow['fixeddomain'] ;
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

function GetLocationNameFromCode($LocCode){
	$ErrMsg = 'Error in function GetLocationNameFromCode()';
	$SQL="SELECT locationname FROM locations WHERE loccode='" . $LocCode . "'";
	$result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($result);
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

function FindReasonOfReturn($ReasonCode, $db){
	$ErrMsg = 'Error in function FindReasonOfReturn()';
	$SQL="SELECT reasonname FROM returnitemreasons WHERE reasonid='" . $ReasonCode . "'";
	$result = DB_query($SQL,$ErrMsg);
	$Row = DB_fetch_row($result);
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
				amount,
				tag)
			VALUES 
				('" . $Type . "',
				'" . $Typeno . "',
				'" . $Trandate . "',
				'" . $Period . "',
				'" . $Account . "',
				'" . $Narrative . "',
				'" . $Amount . "',
				'" . $Tag . "')";
	$ErrMsg = 'CRITICAL ERROR! WRITE THIS CODE AND CALL THE OFFICE IMMEDIATELY: '. $ErrCode;		
	$DbgMsg = 'SQL to insert GLTrans record: ';
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

function GLAccountBelongsTo($Account){
	if (ItemInList("AD", $Account)){
		$Company = "PTADU";
	}else if (ItemInList("PT", $Account)){
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

function ItemBelongsToPT($StockID){
	$SQL="SELECT stockmaster.categoryid
			FROM stockmaster
			WHERE stockmaster.stockid='" . $StockID . "'";
	$ErrMsg = _('WARNING') . ': ' . _('Could not retrieve stock ID category');
	$Result = DB_query($SQL, $ErrMsg);
	$myStockCat = DB_fetch_array($Result);
	$StockCategory = $myStockCat['categoryid'];
	$PT = "PTBB"; // by default it is PTBB
	if (($StockCategory == "SETKLA") OR
		($StockCategory == "SETBLA") OR
		($StockCategory == "SETGEA") OR
		($StockCategory == "TESTKA") OR
		($StockCategory == "TESTBA") OR
		($StockCategory == "TESTGA") OR
		($StockCategory == "STABKA") OR
		($StockCategory == "STABBA") OR
		($StockCategory == "STABGA") OR
		($StockCategory == "NOPOKA") OR
		($StockCategory == "NOPOBA") OR
		($StockCategory == "NOPOGA") OR
		($StockCategory == "DISC2A") OR
		($StockCategory == "DISC5A") OR
		($StockCategory == "DISC8A") OR
		($StockCategory == "COMPOA")){
		$PT = "PTADU";
	}
	return $PT;
}

function CreateConsignmentInvoiceNumber($CompanyFrom, $CompanyTo, $EndDate){
	return $CompanyFrom . '-' . $CompanyTo . '-' . $EndDate;
}

function FindWebsiteBrand($StockID, $Category){
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_KAPAL_LAUT)){
		// if belongs to one of the KL categories, so Brand is KL
		$Brand = 1;	
	}else if (ItemInList($Category, LIST_STOCK_CATEGORIES_BLINK)){
		// if belongs to one of the BL categories, so Brand is BL
		$Brand = 2;	
	}else if (ItemInList($Category, LIST_STOCK_CATEGORIES_GENERAL)){
		// if belongs to one of the General categories, so Brand is KL
		$Brand = 1;	
	}else{
		//should be a discounted item, we keep the previous brand if still available, otherwise assing the outlet brand
		$SQL = "SELECT manufacturers_id
				FROM salescatprod 
				WHERE stockid = '" . $Stockid . "'";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			// assign the current brand
			$myrow = DB_fetch_array($result);
			$Brand = $myrow['manufacturers_id'];	
		}else{
			// we are lost... so assign the outlet one
			$Brand = 3;	
		}
	}
	return $Brand;
}

function ProcessPaymentOnlineOrder($OrderNo, $PaymentCode, $CustomerCode, $TotalAmount){

	// check it is a customer online of the proper currency (IDR)
	if (($CustomerCode == "WEB-KL-IDR") 
		OR ($CustomerCode == "WEB-WH-IDR") 
		OR ($CustomerCode == "TOKOPEDIA") 
		OR ($CustomerCode == "SHOPEE")){
		$FunctionalExRate = 1;
		$ExRate = 1;
		$Currency = "IDR";
	}else{
		return "ERROR";
	}

	if (($CustomerCode == "WEB-WH-IDR") ){
		// it is a wholesale online order customer, so processed by PTADU
		$OnlinePartner = "ONLINEPTAD";
	}else{
		// it is retail in iDR, so it goes to PTBB
		$OnlinePartner = "ONLINEPTBB";
	}

	if ($PaymentCode != "MANUAL_MARKETPLACE") {
		// apply the proper payment
		// let's find the accounts, commission, etc to charge to the different payment codes
		$SQLAccounts = "SELECT accounttransfermandiri,
					accounttransferbca,
					accounttransferdanamon,
					accountxenditidr,
					accountxenditcomissionidr,
					accountcomissionppn,
					comissionxenditflattransfer,
					comissionxenditflatcc,
					comissionxenditpercentcc
				FROM klonlinepartners
				WHERE klonlinepartners.onlinepartnercode = '" . $OnlinePartner . "'";
		$ErrMsg ='Could not get the GL Trasnfers and Commissions for online shop payments because';
		$resultAccounts = DB_query($SQLAccounts,$ErrMsg);
		if(DB_num_rows($resultAccounts) != 0){
			$myrowAccounts = DB_fetch_array($resultAccounts);
			if ($PaymentCode == "bank_mandiri"){
				// bank Mandiri direct transfer has no commissions 
				$GLAccountTransfer = $myrowAccounts['accounttransfermandiri'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			}elseif ($PaymentCode == "bank_bca"){
				// bank bca direct transfer has no commissions 
				$GLAccountTransfer = $myrowAccounts['accounttransferbca'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			}elseif ($PaymentCode == "bank_danamon"){
				// bank Danamon direct transfer has no commissions THIS IS FOR WHOLESALE ONLY, GOES TO PTADU
				$GLAccountTransfer = $myrowAccounts['accounttransferdanamon'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			}elseif  ($PaymentCode == "snap"){
				// MidTrans has commissions but we can't integrate them. We account full order, later manually we process commissions
				$GLAccountTransfer = MIDTRANS_BANK_GL_ACCOUNT;
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			}elseif  ($PaymentCode == "xenditmandiriva"){
				// Xendit transfer via mandiri has commissions
				$GLAccountTransfer = $myrowAccounts['accountxenditidr'];
				$GLAccountCommission = $myrowAccounts['accountxenditcomissionidr'];
				$GLAccountCommissionPPN = $myrowAccounts['accountcomissionppn'];
				$Commission = round($myrowAccounts['comissionxenditflattransfer'],0);
			}elseif  ($PaymentCode == "xenditcc"){
				// Xendit transfer via CC has commissions
				$GLAccountTransfer = $myrowAccounts['accountxenditidr'];
				$GLAccountCommission = $myrowAccounts['accountxenditcomissionidr'];
				$GLAccountCommissionPPN = $myrowAccounts['accountcomissionppn'];
				$Commission = round(($myrowAccounts['comissionxenditflatcc'] + ($TotalAmount * ($myrowAccounts['comissionxenditpercentcc']/100))) ,0);
			}elseif  ($PaymentCode == "tokopedia"){
				// Tokopedia payments  has commissions
				$GLAccountTransfer = TOKOPEDIA_BANK_GL_ACCOUNT;
				$GLAccountCommission = TOKOPEDIA_COMMISSION_GL_ACCOUNT;
				$GLAccountCommissionPPN = ACCOUNT_PPN_BB;
				$Commission = CalculateCommissionTokopedia($CustomerCode, $OrderNo, $TotalAmount);
			}elseif  ($PaymentCode == "shopee"){
				// Shopee payments  has commissions
				$GLAccountTransfer = SHOPEE_BANK_GL_ACCOUNT;
				$GLAccountCommission = SHOPEE_COMMISSION_GL_ACCOUNT;
				$GLAccountCommissionPPN = ACCOUNT_PPN_BB;
				$Commission = CalculateCommissionShopee($CustomerCode, $OrderNo, $TotalAmount);
			}
			$CommissionPPN = round($Commission * PPN_PERCENT / 100, 0);
			$NetAmount = $TotalAmount - $Commission - $CommissionPPN;
		}

		$result = DB_Txn_Begin();

		$BatchNo = GetNextTransNo(12,$db);
		$Today = date('Y-m-d');
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
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
					'" . $Today . "',
					'" . $Today . "',
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
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		$SQL = "UPDATE debtorsmaster
					SET lastpaiddate = '" . $Today . "',
					lastpaid='" . $TotalAmount ."'
				WHERE debtorsmaster.debtorno='" . $CustomerCode . "'";

		$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
		$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

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
				'" . $Today . "',
				'" . $BankTransType . "',
				'" . ($NetAmount * $FunctionalExRate * $ExRate) . "',
				'" . $Currency . "'
			)";
		$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
		$ErrMsg = _('Cannot insert a bank transaction');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

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
				'" . $Today . "',
				'" . $PeriodNo . "',
				'" . $GLAccountTransfer . "',
				'" . $Narrative . "',
				'" . $NetAmount . "'
			)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction from the bank account debit was');
		$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

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
										'" . $Today . "',
										'" . $PeriodNo . "',
										'" . $GLAccountCommission . "',
										'" . $Narrative . "',
										'" . $Commission . "'
									)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction from the commission was');
			$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
			$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
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
										'" . $Today . "',
										'" . $PeriodNo . "',
										'" . $GLAccountCommissionPPN . "',
										'" . $Narrative . "',
										'" . $CommissionPPN . "'
									)";
			$DbgMsg = _('The SQL that failed to insert the GL transaction from the PPN commission was');
			$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
			$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
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
									'" . $Today . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
									'" . $Narrative . "',
									'" . -$TotalAmount . "'
									)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
		$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);			

		$SQL = "UPDATE salesorders
					SET quotation = '0'
				WHERE salesorders.orderno='" . $OrderNo . "'";
		$DbgMsg = _('The SQL that failed to update the quotation flag of the sales order was');
		$ErrMsg = _('Cannot update the quotation flag of the sales order because');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		if (($CustomerCode == "WEB-KL-IDR") OR ($CustomerCode == "WEB-WH-IDR")) {
			// online sale from our website, we must update the status of the order in OpenCart
			$OnlineOrderNo = GetOnlineOrderNoFromWeberp($OrderNo, $db);
			$ReasonChangeStatusId = "webERP --> Payment received by " . $PaymentCode . " Amount = " . $TotalAmount;  
			UpdateOpenCartOrderStatus($OnlineOrderNo, OPENCART_ORDER_STATUS_PROCESSING, 0, "", "", $ReasonChangeStatusId, $db, $db_oc, $oc_tableprefix);
		}


		if  (($PaymentCode == "tokopedia") OR 
			 ($PaymentCode == "shopee")){
			// in case paid my marketplace (so after order is closed and shipment, we need to mark it as "received somehow", so we use klpaidcash
			$SQL = "UPDATE salesorders
						SET klpaidcash = '" . $TotalAmount . "'
					WHERE salesorders.orderno='" . $OrderNo . "'";
			$DbgMsg = _('The SQL that failed to update the payment flag of the sales order was');
			$ErrMsg = _('Cannot update the payment flag of the sales order because');
			$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		}

		$result = DB_Txn_Commit();

	}else{
		// marketplace customers MANUAL_MARKETPLACE, just mark the order as paid
		// accounting has been done manually
		$result = DB_Txn_Begin();

		$SQL = "UPDATE salesorders
					SET klpaidcash = '" . $TotalAmount . "'
				WHERE salesorders.orderno='" . $OrderNo . "'";
		$DbgMsg = _('The SQL that failed to update the payment flag of the sales order was');
		$ErrMsg = _('Cannot update the payment flag of the sales order because');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		$result = DB_Txn_Commit();

	}
	return $result;
}

function CalculateCommissionTokopedia($CustomerCode, $OrderNo, $TotalAmount){
	if ($CustomerCode != "TOKOPEDIA"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = tokopedia", "error");
		include('includes/footer.php');
		exit;
	}
	// 1% from all order for Tokopedia
	$CommissionTPGlobal = round($TotalAmount * TOKOPEDIA_COMMISSION_PERCENT /100 ,0); // this commission still includes PPN

	// we need to pay comething to Tokopedia if shipper si SI-CEPAT, as it means free shipping for the customer, so we pay something
	$SQL = "SELECT salesorders.shipvia
		FROM salesorders 
		WHERE salesorders.orderno = '" . $OrderNo . "' ";			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Shipper = $myrow['shipvia'];
		$CommissionTPFreeShipping = 0;
		if ($Shipper == '12'){
			// if shipper is 12 = GRATIS ONGKIR TOKOPEDIA... then we shipped it via free shipping, we must pay 
			// 2,5% from every item with a max 0f 10.000 for Tokopedia as cost of shipment
			$SQL = "SELECT salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.discountpercent
				FROM salesorderdetails
				WHERE salesorderdetails.orderno = '" . $OrderNo . "' ";			
			$result = DB_query($SQL);
			while ($myrow = DB_fetch_array($result)) {
				$ItemPrice = $myrow['unitprice']*(1-$myrow['discountpercent']);
				$CommissionItem = min(round($ItemPrice * TOKOPEDIA_COMMISSION_FREE_SHIPPING_PER_ITEM_PERCENT /100 ,0), TOKOPEDIA_COMMISSION_FREE_SHIPPING_PER_ITEM_MAXIMUM); 
				$CommissionTPFreeShipping += $CommissionItem * $myrow['qtyinvoiced']; // this commission still has PPN
			}
		}
	}else{
		prnMsg("ERROR: Could not extract shipper information for order = " . $OrderNo, "error");
		include('includes/footer.php');
		exit;
	}
	
	$Commission = $CommissionTPGlobal + $CommissionTPFreeShipping; // this commission still has PPN
	$Commission = round($Commission /((100 + PPN_PERCENT)/100) ,0); // this commision already net
	return $Commission;
}

function CalculateCommissionShopee($CustomerCode, $OrderNo, $TotalAmount){
	if ($CustomerCode != "SHOPEE"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = shopee", "error");
		include('includes/footer.php');
		exit;
	}
	// 1,5% from all order for Shopee
	$Commission = round($TotalAmount * SHOPEE_COMMISSION_PERCENT /100 ,0); // this commission still includes PPN
	$Commission = round($Commission /((100 + PPN_PERCENT)/100) ,0); // this commision already net
	return $Commission;
}

?>

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


?>

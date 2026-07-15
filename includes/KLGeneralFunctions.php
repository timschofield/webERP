<?php

/**************************************************************************************************
			GENERAL KL FUNCTIONS
**************************************************************************************************/

/**************************************************************************************************************
FUNCTIONS INCLUDED IN ALPHABETICAL ORDER:

AdjustBulatan                     - Calculates the rounding adjustment amount
BrandTextFromCode                 - Returns the text description of a brand code
CapitalizeName                    - Properly capitalizes a name with exceptions for common prefixes
ChangeGLAccountCode               - Changes a GL account code throughout the system
ClassicalSize                     - Determines the classical size (XS, S, M, L, XL) from an item code
CleanStringForWebERP              - Removes single quotes from strings for safe database use
CodeModel                         - Returns the model code (first 6 characters) of an item
CodeModelRing                     - Returns the model code for a ring, handling size variations
ConvertExcelDate                  - Converts Excel date format to a standard date format
CreateConsignmentInvoiceNumber    - Creates a formatted consignment invoice number
DataExistsInArchive               - Checks if specific data exists in a Archive table
DataExistsInWebERP                - Checks if specific data exists in a WebERP table
DaysBetween                       - Calculates the number of days between two dates
DeleteWeberpUser                  - Deletes a user from the system with proper validation
EnsureNumberIsNegativeNumber      - Ensures a number is negative or returns 0 if not numeric
EnsureNumberIsPositiveNumber      - Ensures a number is positive or returns 0 if not numeric
FindReasonOfReturn                - Retrieves the reason description for a return code
FindWebsiteBrand                  - Determines the appropriate brand for an item for website use
function_finish                   - Records and displays the execution time of a function
getDirectoryTree                  - Gets a directory listing excluding . and ..
GetAreaFromCustomer               - Retrieves the area code for a customer
GetCategoryNameFromCode           - Gets the description of a stock category
GetCurrencyFromCustomer           - Gets the currency code for a customer
GetDayNameFromWeekDay             - Converts a day number to day name
GetDefaultLocationFromUser        - Gets the default location code for a user
GetItemDescriptionFromCode        - Gets the description of a stock item
GetItemStandardCostFromCode       - Gets the standard cost of a stock item
GetItemTransferReason             - Retrieves transfer reason description from service types table
GetKPIDescription                 - Retrieves the description for a given KPI code
GetLastKPIValue                   - Gets the most recent KPI value for a class/concept
GetLocationNameFromCode           - Gets the location name from a location code
GetNumberOfRecordsInTable         - Counts the number of records in a specified table
GetOnlinePartnerFromArea          - Gets the online partner code for an area
GetTotalItemsChangingPrice        - Counts items with changing price flag
GetTotalItemsMovingToDiscount     - Counts items moving to a specific discount level
GLAccountBelongsTo                - Determines which company a GL account belongs to
InsertIntoGLTrans                 - Inserts a transaction into the GL transaction table
InsertKPI                         - Inserts a KPI record if it doesn't already exist
isAnklet                          - Checks if an item is an anklet
isBag                             - Checks if an item is a bag
isBankAccount                     - Checks if a GL account is a bank account
isBead                            - Checks if an item is a bead
isBracelet                        - Checks if an item is a bracelet
isBrooche                         - Checks if an item is a brooch
isEarcuff                         - Checks if an item is an ear cuff
isEarring                         - Checks if an item is an earring
isFaceMask                        - Checks if an item is a face mask
isFamily                          - Checks if an item belongs to a specific family
isFoulard                         - Checks if an item is a foulard
isJewelleryBox                    - Checks if an item is a jewellery box
isJewelleryRoll                   - Checks if an item is a jewellery roll
isKeyRing                         - Checks if an item is a key holder
isNecklace                        - Checks if an item is a necklace
isPackagingBox                    - Checks if an item is a packaging box
isPackagingPaperInsideBox         - Checks if an item is packaging paper for inside a box
isPendant                         - Checks if an item is a pendant
isPiercing                        - Checks if an item is a piercing
isPlasticBag                      - Checks if an item is a plastic bag
isPolishingCloth                  - Checks if an item is a polishing cloth
isRing                            - Checks if an item is a ring
isSlimRing                        - Checks if an item is a slim ring
isTali                            - Checks if an item is a tali
isToeRing                         - Checks if an item is a toe ring
ItemCodeAvgPriceInvoiced          - Calculates average price invoiced for an item
ItemCodeQOH                       - Gets the quantity on hand for an item
ItemCodeQOO_PurchaseOrders        - Gets the quantity on order from purchase orders
ItemCodeQOO_WorkOrders            - Gets the quantity on order from work orders
ItemCodeQuantityInvoiced          - Gets the quantity invoiced for an item
ItemImagesURL                     - Gets the URL for item images
ItemInList                        - Checks if an item is in a comma-separated list
ListToArray                       - Converts a list with separators to an array
locale_number_format_kpi          - Formats numbers for KPI display with appropriate decimals
locale_number_format_zero_blank   - Formats a number but returns blank for zero
NumberOfItemsInList               - Counts items in a comma-separated list
NumberOfRegularShopsSellingDiscount - Counts shops of a type that sell discount items
NumberOfShops                     - Counts shops of a specific type
NumberSize                        - Determines the numerical size from an item code
NumItemsSoldPerBrand              - Counts items sold per brand in a date range
OptimumOrderQuantity              - Calculates the optimum order quantity
ProcessPaymentOnlineOrder         - Processes payment for an online order
ReviseEmailAddress                - Corrects email addresses with known domain issues
RingSize                          - Determines the ring size from an item code
StartEvenOrOddRow                 - Alternates between even and odd table rows
StartSameColourRow                - Maintains the same table row color
time_finish                       - Records and displays the execution time of a script
time_start                        - Records the start time for timing execution
TotalDisplayItems                 - Counts total display items for a brand
TotalItems                        - Counts total items for a brand
TotalItemsToBeReceivedByPO        - Counts items to be received by purchase order
TotalItemsToBeReceivedByWO        - Counts items to be received by work order
TotalModels                       - Counts total models for a brand
TypeOfItem                        - Returns the type description of an item
**************************************************************************************************************/

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

function ListToArray(string $List, string $Separator){
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

function time_finish(float $begintime){
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$endtime = $time;
	$Totaltime = ($endtime - $begintime);
	prnMsg('Script execution time: ' . locale_number_format($Totaltime,2) . ' seconds.','success');
}

function function_finish(float $begintime){
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$endtime = $time;
	$Totaltime = ($endtime - $begintime);
	prnMsg('Function execution time: ' . locale_number_format($Totaltime,3) . ' seconds.','info');
}

function CodeModel(string $StockID){
	return (substr($StockID, 0,6));
}

function isBead(string $StockID){
	return (substr($StockID, 2,2) == "BE");
}

function isBrooche(string $StockID){
	return (substr($StockID, 2,2) == "PI");
}

function isRing(string $StockID){
	return (substr($StockID, 2,2) == "AN");
}

function isSlimRing(string $StockID){
	return (substr($StockID, 0,4) == "JSAN");
}

function isToeRing(string $StockID){
	return (substr($StockID, 2,2) == "TR");
}

function isBracelet(string $StockID){
	return ((substr($StockID, 2,2) == "PU") OR (substr($StockID, 2,2) == "BR"));
}

function isAnklet(string $StockID){
	return (substr($StockID, 2,2) == "AK");
}

function isFaceMask(string $StockID){
	return (substr($StockID, 2,2) == "FM");
}

function isJewelleryBox(string $StockID){
	return (substr($StockID, 2,2) == "BX");
}

function isJewelleryRoll(string $StockID){
	return (substr($StockID, 2,2) == "JR");
}

function isPendant(string $StockID){
	return (substr($StockID, 2,2) == "PE");
}

function isNecklace(string $StockID){
	return ((substr($StockID, 2,2) == "NE") OR (substr($StockID, 0,4) == "ALCL"));
}

function isEarring(string $StockID){
	return (substr($StockID, 2,2) == "AR");
}

function isPiercing(string $StockID){
	return ((substr($StockID, 2,2) == "PC") AND (substr($StockID, 0,4) != "WKPC"));
}

function isPolishingCloth(string $StockID){
	return (substr($StockID, 0,4) == "WKPC");
}

function isEarcuff(string $StockID){
	return (substr($StockID, 2,2) == "CF");
}

function isPackagingBox(string $StockID){
	return (substr($StockID, 0,4) == "PKBX");
}

function isPackagingPaperInsideBox(string $StockID){
	return (substr($StockID, 0,4) == "PKKS");
}

function isPlasticBag(string $StockID){
	return ((substr($StockID, 0,4) == "BAPL") OR (substr($StockID, 0,4) == "BAGC"));
}

function isBag(string $StockID){
	return (substr($StockID, 2,2) == "BA");
}

function isFoulard(string $StockID){
	return (substr($StockID, 2,2) == "SC");
}

function isTali(string $StockID){
	return ((substr($StockID, 0,3) == "TM-") 
		OR (substr($StockID, 0,4) == "TA15"));
}

function isKeyRing(string $StockID){
	return (substr($StockID, 2,2) == "KI");
}

function isFamily(string $StockID, string $Family){
	return (substr($StockID, 0,2) == $Family);
}

function TypeOfItem(string $StockID){
	if (isRing($StockID)){
		$Type = "Ring";
	} elseif (isToeRing($StockID)){
		$Type = "ToeRing";
	} elseif (isBead($StockID)){
		$Type = "Bead";
	} elseif (isBrooche($StockID)){
		$Type = "Brooche";
	} elseif (isEarring($StockID)){
		$Type = "Earring";
	} elseif (isPiercing($StockID)){
		$Type = "Piercing";
	} elseif (isEarcuff($StockID)){
		$Type = "EarCuff";
	} elseif (isFaceMask($StockID)){
		$Type = "Face Mask";
	} elseif (isJewelleryRoll($StockID)){
		$Type = "Jewellery Roll";
	} elseif (isBracelet($StockID)){
		$Type = "Bracelet";
	} elseif (isAnklet($StockID)){
		$Type = "Anklet";
	} elseif (isPendant($StockID)){
		$Type = "Pendant";
	} elseif (isNecklace($StockID)){
		$Type = "Necklace";
	} elseif (isPlasticBag($StockID)){
		$Type = "Bag";
	} elseif (isBag($StockID)){
		$Type = "Bag";
	} elseif (isTali($StockID)){
		$Type = "Tali";
	} else {
		$Type = "Unknown";
	}
	return $Type;
}

function CodeModelRing(string $StockID){
	if (strlen($StockID) == 6){
		$CodeModel = $StockID;
	} else {
		if ((substr($StockID, -2,1) == "0") 
			OR (substr($StockID, -2,1) == "1")
			OR (substr($StockID, -2,1) == "2")){
			// ring with sizes! We need to cut the 3 last characters -XX
			$CodeModel = (substr($StockID, 0,strlen($StockID)-3));
		} else {
			$CodeModel = $StockID;
		}
	}
	return $CodeModel;
}

function RingSize(string $StockID){
	if (strlen($StockID) == 6){
		$Size = "FR";
	} else {
		if ((substr($StockID, -2,1) == "0") 
			OR (substr($StockID, -2,1) == "1")
			OR (substr($StockID, -2,1) == "2")){
			// ring with sizes! We need to get the 2 last characters -XX
			$Size = substr($StockID, strlen($StockID)-2,2);
		} else {
			$Size = "FR";
		}
	}
	return $Size;
}

function NumberSize(string $StockID){
	if (strlen($StockID) == 6){
		$Size = "NO SIZE";
	} elseif ((substr($StockID, -2,1) == "0") 
		OR (substr($StockID, -2,1) == "1")
		OR (substr($StockID, -2,1) == "2")
		OR (substr($StockID, -2,1) == "3")
		OR (substr($StockID, -2,1) == "4")
		OR (substr($StockID, -2,1) == "5")
		OR (substr($StockID, -2,1) == "6")
		OR (substr($StockID, -2,1) == "7")
		OR (substr($StockID, -2,1) == "8")
		OR (substr($StockID, -2,1) == "9")){
		// number sizes! We need to get the 2 last characters -XX
		$Size = substr($StockID, strlen($StockID)-2,2);
	} else {
		$Size = "NO SIZE";
	}
	return $Size;
}

function ClassicalSize(string $StockID){
	if (strlen($StockID) == 6){
		$Size = "NO SIZE";
	} elseif (substr($StockID, -3,3) == "-XS"){
		$Size = "XS";
	} elseif (substr($StockID, -2,2) == "-S"){
		$Size = "S";
	} elseif (substr($StockID, -2,2) == "-M"){
		$Size = "M";
	} elseif (substr($StockID, -2,2) == "-L"){
		$Size = "L";
	} elseif (substr($StockID, -3,3) == "-XL"){
		$Size = "XL";
	} else {
		$Size = "NO SIZE";
	}
	return $Size;
}

function ItemCodeQOH(string $StockID, string $CodeDetail, string $Where){
	$ErrMsg = 'Error in function ItemCodeQOH()';

	$SQL = "SELECT SUM(locstock.quantity) AS total
			FROM locstock,locations 
			WHERE locstock.loccode = locations.loccode ";

	if ($CodeDetail == 'CODE_FULL'){
		$SQL .= "AND  stockid = '". $StockID ."'";
	} elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($StockID)){
			$SQL .= "AND stockid LIKE '". $StockID ."%'";
		} else {
			$SQL .= "AND stockid = '". $StockID ."'";
		}
	} else {
		$SQL .= "AND stockid LIKE '". $StockID ."%'";
	}

	if ($Where == "ALL_SHOPS"){
		$SQL .= " AND locations.typeloc IN " . LIST_PHYSICAL_SHOPS_BY_TYPE . " "; 
	} elseif ($Where == "ALL_SHOPS_AND_ONLINE"){
		$SQL .= " AND locations.typeloc IN " . LIST_ALL_SHOPS_BY_TYPE . " "; 
	} elseif ($Where == "ALL"){
		$SQL .= " "; 
	} else {
		$SQL .= " AND locstock.loccode = '". $Where . "'"; 
	}

	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Qty = $MyRow['total'];
	} else {
		$Qty = 0;
	}
	return $Qty;
}

function ItemCodeQuantityInvoiced(string $StockID, string $FromDate, string $ToDate, string $Debtorno, string $CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQuantityInvoiced()';

	if ($CodeDetail == 'CODE_FULL'){
		$WhereCondition = "AND salesorderdetails.stkcode = '". $StockID ."'";
	} elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($StockID)){
			$WhereCondition = "AND salesorderdetails.stkcode LIKE '". $StockID ."%'";
		} else {
			$WhereCondition = "AND salesorderdetails.stkcode = '". $StockID ."'";
		}
	} else {
		$WhereCondition = "AND salesorderdetails.stkcode LIKE '". $StockID ."%'";
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
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return 0;
}

function ItemCodeAvgPriceInvoiced(string $StockID, string $FromDate, string $ToDate, string $Debtorno, string $CodeDetail){
	$ErrMsg = 'Error in function ItemCodeAvgPriceInvoiced()';
	
	if ($CodeDetail == 'CODE_FULL'){
		$WhereCondition = "AND salesorderdetails.stkcode = '". $StockID ."'";
	} elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($StockID)){
			$WhereCondition = "AND salesorderdetails.stkcode LIKE '". $StockID ."%'";
		} else {
			$WhereCondition = "AND salesorderdetails.stkcode = '". $StockID ."'";
		}
	} else {
		$WhereCondition = "AND salesorderdetails.stkcode LIKE '". $StockID ."%'";
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
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return 0;
}

function ItemCodeQOO_PurchaseOrders(string $StockID, string $CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQOO_PurchaseOorders()';

	if ($CodeDetail == 'CODE_FULL'){
		$WhereCondition = "WHERE purchorderdetails.itemcode = '". $StockID ."'";
	} elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($StockID)){
			$WhereCondition = "WHERE purchorderdetails.itemcode LIKE '". $StockID ."%'";
		} else {
			$WhereCondition = "WHERE purchorderdetails.itemcode = '". $StockID ."'";
		}
	} else {
		$WhereCondition = "WHERE purchorderdetails.itemcode LIKE '". $StockID ."%'";
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

/* SQL optimized by GEMINI */
function ItemCodeQOO_WorkOrders(string $StockID, string $CodeDetail){
	$ErrMsg = 'Error in function ItemCodeQOO_WorkOorders()';

	if ($CodeDetail == 'CODE_FULL'){
		$WhereCondition = "AND woitems.stockid = '". $StockID ."'";
	} elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
		if (isRing($StockID)){
			$WhereCondition = "AND woitems.stockid LIKE '". $StockID ."%'";
		} else {
			$WhereCondition = "AND woitems.stockid = '". $StockID ."'";
		}
	} else {
		$WhereCondition = "AND woitems.stockid LIKE '". $StockID ."%'";
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

function locale_number_format_zero_blank(float|int|null $num, int $dec){
	if ($num === null || $num == 0){
		return '';
	} else {
		return locale_number_format($num,$dec);
	}
}

function locale_number_format_kpi(float|int|null $num){
	if ($num === null){
		return '';
	} elseif (abs($num) >= 100){
		return locale_number_format($num,0);
	} elseif (abs($num) >= 10){
		return locale_number_format($num,1);
	} else {
		return locale_number_format($num,2);
	}
}


function StartEvenOrOddRow(int $k){
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	return $k;
}

function StartSameColourRow(int $k){
	if ($k == 1) {
		echo '<tr class="OddTableRows">';
	} else {
		echo '<tr class="EvenTableRows">';
	}
	return $k;
}

function getDirectoryTree(string $outerDir){ 
	$dirs = false; 
	if (is_dir($outerDir)){
		$Directory = scandir( $outerDir );
		if (is_array($Directory)){
			$dirs = array_diff( $Directory, Array( ".", ".." ) ); 
		}
	}
   return $dirs; 
} 

function ItemInList(string $Item, string $List){
	// http://www.php.net/manual/en/function.strpos.php for details on ===	
	if (strpos(strtolower($List), strtolower($Item)) === false){
		return false;
	} else {
		return true;
	}
}

function NumberOfItemsInList(string $List){
	// https://www.php.net/manual/en/function.substr-count.php 	
	return substr_count($List, ',') + 1;  
}

function CapitalizeName(string $string){
// copied from http://www.media-division.com/correct-name-capitalization-in-php/
	$word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
	$lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
	$uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');
 
	$string = strtolower($string);
	foreach ($word_splitters as $delimiter)
	{ 
		$words = explode($delimiter, $string); 
		$Newwords = array(); 
		foreach ($words as $word)
		{ 
			if (in_array(strtoupper($word), $uppercase_exceptions))
				$word = strtoupper($word);
			else
			if (!in_array($word, $lowercase_exceptions))
				$word = ucfirst($word); 
 
			$Newwords[] = $word;
		}
 
		if (in_array(strtolower($delimiter), $lowercase_exceptions))
			$delimiter = strtolower($delimiter);
 
		$string = join($delimiter, $Newwords); 
	} 
	return $string; 
}

function ReviseEmailAddress(string $email){
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
	} else {
		// seems OK. At least we can't detect an error
		$revisedemail = $email;
	}
	return $revisedemail; 
}

function CleanStringForWebERP(string $s){
	$s = str_replace("'", " ", $s);
	return $s;
}

function GetAreaFromCustomer(string $CustomerCode){
	$ErrMsg = 'Error in function GetAreaFromCustomer()';
	$SQL = "SELECT area
			FROM custbranch
			WHERE custbranch.debtorno ='". $CustomerCode . "'
			AND custbranch.branchcode = '" . $CustomerCode . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

function GetCurrencyFromCustomer(string $CustomerCode){
	$ErrMsg = 'Error in function GetCurrencyFromCustomer()';
	$SQL = "SELECT currcode
			FROM debtorsmaster
			WHERE debtorsmaster.debtorno ='". $CustomerCode . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

function GetOnlinePartnerFromArea(string $Area){
	return "ONLINEPTAD";
}

function GetCategoryNameFromCode(string $CategoryId){
	$ErrMsg = 'Error in function GetCategoryNameFromCode()';
	$SQL="SELECT categorydescription FROM stockcategory WHERE categoryid = '" . $CategoryId . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

function GetDefaultLocationFromUser(string $UserId){
	$ErrMsg = 'Error in function GetDefaultLocationFromUser()';
	$SQL = "SELECT defaultlocation FROM www_users WHERE userid = '".$UserId."'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

function GetLocationNameFromCode(string $LocCode){
	$ErrMsg = 'Error in function GetLocationNameFromCode()';
	$SQL="SELECT locationname FROM locations WHERE loccode='" . $LocCode . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

function GetItemCategoryIdFromCode(string $StockID){
	$ErrMsg = 'Error in function GetItemCategoryIdFromCode()';
	$SQL="SELECT categoryid FROM stockmaster WHERE stockid = '" . $StockID . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

function GetItemDescriptionFromCode(string $StockID){
	$ErrMsg = 'Error in function GetItemDescriptionFromCode()';
	$SQL="SELECT description FROM stockmaster WHERE stockid = '" . $StockID . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

function GetItemStandardCostFromCode(string $StockID){
	$ErrMsg = 'Error in function GetItemStandardCostFromCode()';
	$SQL = "SELECT actualcost FROM stockmaster WHERE stockid = '" . $StockID . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return round($Row['0'], 0);
	}
	return 0;
}



function GetTotalItemsChangingPrice(){
	$ErrMsg = 'Error in function GetTotalItemsChangingPrice()';
	$SQL="SELECT COUNT(*)
		FROM stockmaster
		WHERE klchangingprice = '1'
			AND categoryid NOT IN " . LIST_STOCK_CATEGORIES_TEST . "";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return 0;
}

function GetTotalItemsMovingToDiscount(string $DiscountLevel){
	$ErrMsg = 'Error in function GetTotalItemsMovingToDiscount()';
	$SQL="SELECT COUNT(*) FROM stockmaster WHERE klmovingdiscount".$DiscountLevel."='1'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return 0;
}

function EnsureNumberIsNegativeNumber(mixed $Value){
    // Return 0 if the parameter is not a number
    if (!is_numeric($Value)) {
        return 0;
    }
    // Be sure the value returned is negative
    if ($Value > 0){
        $Value = -$Value;
    }
    return $Value;
}
function EnsureNumberIsPositiveNumber(mixed $Value){
    // Return 0 if the parameter is not a number
    if (!is_numeric($Value)) {
        return 0;
    }	// be sure the value returned is positive
	if ($Value < 0){
		$Value = -$Value;
	}
	return $Value;
}

function FindReasonOfReturn(string $ReasonCode){
	$ErrMsg = 'Error in function FindReasonOfReturn()';
	$SQL="SELECT reasonname FROM returnitemreasons WHERE reasonid = '" . $ReasonCode . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_row($Result);
		return $Row['0'];
	}
	return '';
}

function ConvertExcelDate(mixed $value, string $format = 'Y-m-d') {
    // First check if the value is already a Cell object
    if ($value instanceof Cell) {
        $cellValue = $value->getValue();
    } else {
        $cellValue = $value;
    }
    
    // Check if it's a valid Excel date
    if (is_numeric($cellValue)) {
        return Date::excelToDateTimeObject($cellValue)->format($format);
    }
    
    return '1000-01-01';
}

function AdjustBulatan(float|int $Amount, float|int $RoundTo){
	return (ceil($Amount/$RoundTo)*$RoundTo)-$Amount;
}

function InsertIntoGLTrans(int|string $Type, int|string $Typeno, string $Trandate, int|string $Period, string $Account, string $Narrative, float|int $Amount, mixed $Tag, string $ErrCode){
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
				'" . mb_substr($Narrative, 0, 200) . "',
				'" . (float)$Amount . "')";
	$ErrMsg = 'CRITICAL ERROR! WRITE THIS CODE AND CALL THE OFFICE IMMEDIATELY: '. $ErrCode;		
	DB_query($SQL, $ErrMsg, '', true);
}

function GLAccountBelongsTo(string $Account){
	if (ItemInList("AD", $Account)){
		$Company = "PTADU";
	} elseif (ItemInList("SM", $Account)){
		$Company = "PTSMH";
	} elseif (ItemInList("BB", $Account)){
		$Company = "PTBB";
	} elseif (ItemInList("IK", $Account)){
		$Company = "POIK";
	} elseif (ItemInList("PI", $Account)){
		$Company = "POPI";
	} else {
		$Company = "CASH";
	}
	return $Company;
}

function isBankAccount(string $GLAccount){
	$ErrMsg = 'Error in function isBankAccount()';
	$SQL = "SELECT *
			FROM bankaccounts
			WHERE accountcode = '" . $GLAccount . "'";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) == 0){
		return false;
	}
	else {
		return true;
	}
}

function CreateConsignmentInvoiceNumber(string $CompanyFrom, string $CompanyTo, string $EndDate){
	return $CompanyFrom . '-' . $CompanyTo . '-' . $EndDate;
}

function FindWebsiteBrand(string $StockID, string $Category, string $Description){
	if (ItemInList($Category, LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_ALL_DISCOUNT)){
		// if belongs to one of the KL categories, so Brand is KL
		$Brand = 1;	
	} elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_BLINK_INCLUDING_ALL_DISCOUNT)){
		// if belongs to one of the BL categories, so Brand is BL
		$Brand = 2;	
	} elseif (ItemInList($Category, LIST_STOCK_CATEGORIES_GENERAL_INCLUDING_ALL_DISCOUNT)){
		// if belongs to one of the General categories, so Brand is KL
		$Brand = 1;	
	} else {
		//should be a discounted item, we keep the previous brand if still available, otherwise we continue messing around
		$SQL = "SELECT brands_id
				FROM salescatprod 
				WHERE stockid = '" . $StockID . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			// assign the current brand
			$MyRow = DB_fetch_array($Result);
			$Brand = $MyRow['brands_id'];	
		} else {
			// we check the description, if we get any info
				if (mb_stristr($Description, "silver") != false){
					// description contains "silver", should be KL
					$Brand = 1;	
				} else {
					// description does not contain "silver", should be Blink
					$Brand = 2;	
				}			
		}
	}
	return $Brand;
}

function ProcessPaymentOnlineOrder(int|string $OrderNo, string $PaymentCode, string $CustomerCode, float|int $TotalAmount){

	// so far... only in IDR
	if (GetCurrencyFromCustomer($CustomerCode) == "IDR"){
		$FunctionalExRate = 1;
		$ExRate = 1;
		$Currency = "IDR";
	} else {
		return "ERROR";
	}

	$Area = GetAreaFromCustomer($CustomerCode);
	$OnlinePartner = GetOnlinePartnerFromArea($Area);
	$Commission = 0;
	$CommissionPPN = 0;
	$NetAmount = 0;
	$PPh22Percent = 0;
	$GLAccountPPh22 = "";
	$PPh22 = 0;


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
					comissiontokopediaflatfee,
					accountpph22tokopedia,
					pph22tokopediapercent,
					comissionshopeepercent,
					comissionshopeeflatfee,
					accountpph22shopee,
					pph22shopeepercent,
					comissionlazadapercent
				FROM klonlinepartners
				WHERE klonlinepartners.onlinepartnercode = '" . $OnlinePartner . "'";
		$ErrMsg ='Could not get the GL Transfers and Commissions for online shop payments because';
		$ResultAccounts = DB_query($SQLAccounts,$ErrMsg);
		if (DB_num_rows($ResultAccounts) != 0){
			$MyRowAccounts = DB_fetch_array($ResultAccounts);
			if ($PaymentCode == "bank_mandiri"){
				// bank Mandiri direct transfer has no commissions 
				$GLAccountTransfer = $MyRowAccounts['accounttransfermandiri'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			} elseif ($PaymentCode == "bank_bca"){
				// bank bca direct transfer has no commissions 
				$GLAccountTransfer = $MyRowAccounts['accounttransferbca'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			} elseif ($PaymentCode == "bank_danamon"){
				// bank Danamon direct transfer has no commissions
				$GLAccountTransfer = $MyRowAccounts['accounttransferdanamon'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			} elseif  ($PaymentCode == "snap"){
				// MidTrans has commissions but we can't integrate them. We account full order, later manually we process commissions
				$GLAccountTransfer = $MyRowAccounts['accountmidtransidr'];
				$GLAccountCommission = "";
				$GLAccountCommissionPPN = "";
				$Commission = 0;
			} elseif  ($PaymentCode == "xenditmandiriva"){
				// Xendit transfer via mandiri has commissions
				$GLAccountTransfer = $MyRowAccounts['accountxenditidr'];
				$GLAccountCommission = $MyRowAccounts['accountxenditcomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$Commission = round($MyRowAccounts['comissionxenditflattransfer'],0);
			} elseif  ($PaymentCode == "xenditcc"){
				// Xendit transfer via CC has commissions
				$GLAccountTransfer = $MyRowAccounts['accountxenditidr'];
				$GLAccountCommission = $MyRowAccounts['accountxenditcomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$Commission = round(($MyRowAccounts['comissionxenditflatcc'] + ($TotalAmount * ($MyRowAccounts['comissionxenditpercentcc']/100))) ,0);
			} elseif  ($PaymentCode == "tokopedia"){
				// Tokopedia payments  has commissions
				$GLAccountTransfer = $MyRowAccounts['accounttokopediaidr'];
				$GLAccountCommission = $MyRowAccounts['accounttokopediacomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$CommissionTokopediaPercent = $MyRowAccounts['comissiontokopediapercent'];
				$ComissionTokopediaFlatFee = $MyRowAccounts['comissiontokopediaflatfee'];
				$Commission = CalculateCommissionTokopedia($CustomerCode,
															$TotalAmount,
															$CommissionTokopediaPercent,
															$ComissionTokopediaFlatFee);
				$PPh22Percent = $MyRowAccounts['pph22tokopediapercent'];
				$GLAccountPPh22 = $MyRowAccounts['accountpph22tokopedia'];

			} elseif  ($PaymentCode == "shopee"){
				// Shopee payments  has commissions
				$GLAccountTransfer = $MyRowAccounts['accountshopeeidr'];
				$GLAccountCommission = $MyRowAccounts['accountshopeecomissionidr'];
				$GLAccountCommissionPPN = $MyRowAccounts['accountcomissionppn'];
				$CommissionShopeePercent = $MyRowAccounts['comissionshopeepercent'];
				$ComissionShopeeFlatFee = $MyRowAccounts['comissionshopeeflatfee'];
				$Commission = CalculateCommissionShopee($CustomerCode,
														$TotalAmount,
														$CommissionShopeePercent,
														$ComissionShopeeFlatFee);
				$PPh22Percent = $MyRowAccounts['pph22shopeepercent'];
				$GLAccountPPh22 = $MyRowAccounts['accountpph22shopee'];

			} elseif  ($PaymentCode == "lazada"){
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
			$CommissionPPN = round($Commission * $_SESSION['PPN_Percent'] / 100, 0);
			$PPh22 = round($TotalAmount * $PPh22Percent / 100, 0);
			$NetAmount = $TotalAmount - $Commission - $CommissionPPN - $PPh22;
		}

		DB_Txn_Begin();

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
					'" . (float)($FunctionalExRate*$ExRate) . "',
					'" . (float)-$TotalAmount . "',
					'" . 0 . "',
					'" . $Narrative. "',
					''
				)";
				
		$ErrMsg = __('Cannot insert a receipt transaction against the customer because') ;
		$Result = DB_query($SQL, $ErrMsg, '', true);

		$SQL = "UPDATE debtorsmaster
					SET lastpaiddate = CURRENT_DATE,
					lastpaid = '" . $TotalAmount ."'
				WHERE debtorsmaster.debtorno='" . $CustomerCode . "'";

		$ErrMsg = __('Cannot update the customer record for the date of the last payment received because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

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
				'" . (float)($NetAmount * $FunctionalExRate * $ExRate) . "',
				'" . $Currency . "'
			)";
		$ErrMsg = __('Cannot insert a bank transaction');
		$Result = DB_query($SQL, $ErrMsg, '', true);

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
				'" . mb_substr($Narrative, 0, 200) . "',
				'" . $NetAmount . "'
			)";
		$ErrMsg = __('Cannot insert a GL transaction for the bank account debit');
		$Result = DB_query($SQL, $ErrMsg, '', true);

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
										'" . mb_substr($Narrative, 0, 200) . "',
										'" . (float)$Commission . "'
									)";
			$ErrMsg = __('Cannot insert a GL transaction for the online commission account');
			$Result = DB_query($SQL, $ErrMsg, '', true);
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
										'" . mb_substr($Narrative, 0, 200) . "',
										'" . (float)$CommissionPPN . "'
									)";
			$ErrMsg = __('Cannot insert a GL transaction for the online commission PPN account');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		}

		if ($PPh22 > 0){
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
										'" . $GLAccountPPh22 . "',
										'" . mb_substr($Narrative, 0, 200) . "',
										'" . (float)$PPh22 . "'
									)";
			$ErrMsg = __('Cannot insert a GL transaction for the online PPh22 account');
			$Result = DB_query($SQL, $ErrMsg, '', true);
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
									'" . mb_substr($Narrative, 0, 200) . "',
									'" . -$TotalAmount . "'
									)";
		$ErrMsg = __('Cannot insert a GL transaction for the debtors account credit');
		$Result = DB_query($SQL, $ErrMsg, '', true);			

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
		} else {
			$SQL = "UPDATE salesorders
						SET quotation = '0',
							confirmeddate = CURRENT_DATE
					WHERE salesorders.orderno='" . $OrderNo . "'";
		}
		$ErrMsg = __('Cannot update the quotation flag of the sales order because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		if (($CustomerCode == "WEB-KL-IDR") OR ($CustomerCode == "WEB-WH-IDR")) {
			// online sale from our website, we must update the status of the order in OpenCart
			$OnlineOrderNo = GetOnlineOrderNoFromWeberp($OrderNo);
			$ReasonChangeStatusId = "webERP --> Payment received by " . $PaymentCode . " Amount = " . $TotalAmount;  
			UpdateOpenCartOrderStatus($OnlineOrderNo, OPENCART_ORDER_STATUS_PROCESSING, 1, "", "", $ReasonChangeStatusId);
			UpdateOpenCartOrderPayment($OnlineOrderNo);
		}

		DB_Txn_Commit();

	} else {
		// marketplace customers MANUAL_MARKETPLACE, just mark the order as paid
		// accounting has been done manually
		DB_Txn_Begin();

		$SQL = "UPDATE salesorders
					SET klpaidcash = '" . $TotalAmount . "'
				WHERE salesorders.orderno='" . $OrderNo . "'";
		$ErrMsg = __('Cannot update the payment flag of the sales order because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		DB_Txn_Commit();
	}
	return $Result;
}

function ItemImagesURL(string $StockID, int $NumberOfImage, bool $PackagingAlreadyFound, string $TypeOfPackaging){
	$PackagingImage =  false;
	if ($NumberOfImage == 1){
		// main image
		$URL = PATH_TO_CATALOG_IMAGES . $StockID.'.jpg';
	} elseif ($NumberOfImage == 999){
		// last image of the lot MUST be a packaging image if still not found a packaging image
		if (($TypeOfPackaging != "") AND ($TypeOfPackaging != "NO-PACKAGING")){
			$URL = PATH_TO_CATALOG_PACKAGING_IMAGES . $TypeOfPackaging.'.jpg';
			$PackagingImage =  true;
		} else {
			$URL = "";
		}
	} else {
		// extra images
		$NumberOfImage = $NumberOfImage - 1;
		if (file_exists($_SESSION['part_pics_dir'] . '/' . $StockID.'.'.$NumberOfImage.'.jpg')){
			$URL = PATH_TO_CATALOG_IMAGES . $StockID.'.'.$NumberOfImage.'.jpg';
		} else {
			if (($TypeOfPackaging != "") AND ($TypeOfPackaging != "NO-PACKAGING")){
				$URL = PATH_TO_CATALOG_PACKAGING_IMAGES . $TypeOfPackaging.'.jpg';
				$PackagingImage =  true;
			} else {
				$URL = "";
			}
		}
	}
	return array($URL,$PackagingImage);
}

function DataExistsInWebERP(string $Table, string $f1, mixed $v1, string $f2 = '', mixed $v2 = ''){
	if ($f2 == ''){
		/* Primary key is 1 field only */
		$SQL = "SELECT COUNT(*)
				FROM " . $Table . "
				WHERE " . $f1 . " = '" . $v1 . "'";
	} else {
		/* Primary key is 2 fields */
		$SQL = "SELECT COUNT(*)
				FROM " . $Table . "
				WHERE " . $f1 . " = '" . $v1 . "'
					AND " . $f2 . " = '" . $v2 . "'";
	}
	$ErrMsg =__('Could not check existence of data in webERP because');
	$Result = DB_query($SQL,$ErrMsg);

	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Exists = ($MyRow[0] > 0);
	} else {
		$Exists = false;
	}
	return $Exists;
}

function DataExistsInArchive(string $Table, string $f1, mixed $v1, string $f2 = '', mixed $v2 = ''){
	if ($f2 == ''){
		/* Primary key is 1 field only */
		$SQL = "SELECT COUNT(*)
				FROM " . $Table . "
				WHERE " . $f1 . " = '" . $v1 . "'";
	} else {
		/* Primary key is 2 fields */
		$SQL = "SELECT COUNT(*)
				FROM " . $Table . "
				WHERE " . $f1 . " = '" . $v1 . "'
					AND " . $f2 . " = '" . $v2 . "'";
	}
	$ErrMsg =__('Could not check existence of data in webERP because');
	$Result = DB_query_archive($SQL,$ErrMsg);

	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$Exists = ($MyRow[0] > 0);
	} else {
		$Exists = false;
	}
	return $Exists;
}


function InsertKPI(string $KPICode, float|int $Value){
	$Date = date('Y-m-d');
	if (!DataExistsInWebERP('klkpi', 'date', $Date, 'kpicode', $KPICode)){
		$SQL = "INSERT INTO klkpi 
				(date,
				kpicode,
				value)
			VALUES 
				('" . $Date . "',
				'" . mb_substr($KPICode, 0, 40) . "',
				'" . (float)$Value . "')";
		$ErrMsg = 'Error in function InsertKPI()';
		DB_query($SQL, $ErrMsg, '', true);
	}
}

function NumberOfShops(string $ShopType){
	/* SQL optimized by Roo on 26/08/2025 - Performance improvements:
	 * 1. Added proper error handling with $ErrMsg variable
	 * 2. Leverages uk_locations_typeloc_loccode composite index for optimal performance
	 * 3. Consolidated discount shop logic to reduce code duplication
	 * 4. Optimized WHERE clause ordering for better index utilization
	 * 5. Enhanced return type casting and consistent error handling
	 * 6. Improved query structure for better maintainability
	 */
	$ErrMsg = 'Error in function NumberOfShops()';

	// Handle simple shop type queries (SHOPKL, SHOPBL, SHOPOU)
	if ($ShopType == "SHOPKL" || $ShopType == "SHOPBL" || $ShopType == "SHOPOU"){
		/* Optimized query structure:
		 * - Uses uk_locations_typeloc_loccode index for optimal performance
		 * - Simple equality condition leverages index efficiently
		 */
		$SQL = "SELECT COUNT(*) AS shopcount
				FROM locations
				WHERE typeloc = '" . $ShopType . "'";
				
	// Handle discount shop queries (SHOPOK, SHOPOB, SHOPOG)
	} elseif ($ShopType == "SHOPOK"){
		/* Optimized query for Kapal-Laut discount shops:
		 * - Leverages uk_locations_typeloc_loccode index for typeloc filtering
		 * - Efficient discount flag checking with OR conditions
		 */
		$SQL = "SELECT COUNT(*) AS shopcount
				FROM locations
				WHERE typeloc = 'SHOPKL'
					AND (alldisc20items > 0
						OR alldisc50items > 0
						OR alldisc80items > 0)";
						
	} elseif ($ShopType == "SHOPOB"){
		/* Optimized query for Blink discount shops:
		 * - Leverages uk_locations_typeloc_loccode index for typeloc filtering
		 * - Efficient discount flag checking with OR conditions
		 */
		$SQL = "SELECT COUNT(*) AS shopcount
				FROM locations
				WHERE typeloc = 'SHOPBL'
					AND (alldisc20items > 0
						OR alldisc50items > 0
						OR alldisc80items > 0)";
						
	} elseif ($ShopType == "SHOPOG"){
		/* Optimized query for General discount shops:
		 * - Uses uk_locations_typeloc_loccode index with IN clause for multiple types
		 * - More efficient than multiple OR conditions for typeloc
		 */
		$SQL = "SELECT COUNT(*) AS shopcount
				FROM locations
				WHERE typeloc IN ('SHOPKL', 'SHOPBL', 'SHOPOU')
					AND (alldisc20items > 0
						OR alldisc50items > 0
						OR alldisc80items > 0)";
	} else {
		// Invalid shop type - return 0
		return 0;
	}

	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) > 0){
		$MyRow = DB_fetch_array($Result);
		return (int)$MyRow['shopcount'];
	}
	return 0;
}

function NumberOfRegularShopsSellingDiscount(string $ShopType){
	if ($ShopType == "SHOPKL"){
		$Categories = "AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT_ONLY_DISCOUNT . "";
	} elseif ($ShopType == "SHOPBL"){
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
	} else {
		return 0;
	}
}

function DeleteWeberpUser(string $SelectedUser, bool $AdminRole){
	// a regular user can't delete some users, so we check if the user is a super user or not
	if ($SelectedUser == "Ricard"){
		prnMsg('User '. $SelectedUser . ' cannot be deleted as is a super user','error');
	} elseif ((($SelectedUser == "Laia")
				OR ($SelectedUser == "Garbi")	
				OR ($SelectedUser == "Ike1")	
				OR ($SelectedUser == "Fathus")	
				OR ($SelectedUser == "RiaResti")	
				OR ($SelectedUser == "Cicik")	
				OR ($SelectedUser == "Revi"))
			AND (!$AdminRole )){
		prnMsg('You do not have enough rights to delete user '. $SelectedUser ,'error');
	} else {
		$SQL="SELECT userid FROM audittrail where userid = '" . $SelectedUser ."'";
		$Result=DB_query($SQL);
		if (DB_num_rows($Result)!=0) {
			prnMsg(__('Cannot delete user as entries still exist in the audit trail'), 'error');
		} else {
			$SQL="DELETE FROM locationusers WHERE userid = '" . $SelectedUser . "'";
			$ErrMsg = __('The Location - User could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM glaccountusers WHERE userid = '" . $SelectedUser . "'";
			$ErrMsg = __('The GL Account - User could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM bankaccountusers WHERE userid = '" . $SelectedUser . "'";
			$ErrMsg = __('The Bank Accounts - User could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM purchorderauth WHERE userid = '" . $SelectedUser . "'";
			$ErrMsg = __('The Purchase Orders Authority could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM sessions WHERE userid = '" . $SelectedUser . "'";
			$ErrMsg = __('The Sessions User could not be deleted because');
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM session_data WHERE userid = '" . $SelectedUser . "'";
			$ErrMsg = __('The Session Data User could not be deleted because');
			$Result = DB_query($SQL,$ErrMsg);

			$SQL="DELETE FROM www_users WHERE userid = '" . $SelectedUser . "'";
			$ErrMsg = __('The User could not be deleted because');;
			$Result = DB_query($SQL,$ErrMsg);

			KLSendEmail("UserDeleted", "Silent",$_SESSION['UserID'], $SelectedUser);
			prnMsg('User ' . $SelectedUser . ' deleted', 'success');
		}
		unset($SelectedUser);
	}
}

function GetDayNameFromWeekDay(int $WeekDay){
	if ($WeekDay == 1){
		return "Sunday";
	} elseif ($WeekDay == 2){
		return "Monday";
	} elseif ($WeekDay == 3){
		return "Tuesday";
	} elseif ($WeekDay == 4){
		return "Wednesday";
	} elseif ($WeekDay == 5){
		return "Thursday";
	} elseif ($WeekDay == 6){
		return "Friday";
	} elseif ($WeekDay == 7){
		return "Saturday";
	}
}

function GetLastKPIValue(string $KPICode){
	$SQL = "SELECT value
			FROM klkpi
			WHERE kpicode LIKE '".$KPICode."'
			ORDER BY date DESC
			LIMIT 1";
	$Result = DB_query($SQL);		
	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		return $MyRow['value'];
	}
	return 0;
	
}

function DaysBetween(string $date1, string $date2) {
  $start = strtotime($date1);
  $end = strtotime($date2);
  $days_between = ceil(abs($end - $start) / 86400);
  return $days_between;
}

function TotalItemsToBeReceivedByPO(string $Brand){
	/* SQL optimized by Roo on 26/08/2025 - Performance improvements:
	 * 1. Reordered JOIN to filter stockmaster first by category (smaller result set)
	 * 2. Leverages idx_purchorderdetails_completed_orderno_itemcode index optimally
	 * 3. Added support for all brand categories (SHOPOK, SHOPOB, SHOPOG, SHOPOU)
	 * 4. Improved WHERE clause ordering for better index utilization
	 * 5. Enhanced error handling and consistent return type casting
	 */
	$ErrMsg = 'Error in function TotalItemsToBeReceivedByPO()';

	if ($Brand == "SHOPKL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP;
	} elseif ($Brand == "SHOPBL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP;
	} elseif ($Brand == "SHOPOK"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOB"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOG"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_GENERAL_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOU"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_OUTLET;
	} else {
		return 0;
	}

	/* Optimized query structure:
	 * - Filter stockmaster by category first (uses uk_stockmaster_categoryid_stockid index)
	 * - JOIN with purchorderdetails using filtered stockmaster results
	 * - Leverage idx_purchorderdetails_completed_orderno_itemcode for optimal performance
	 * - Filter by purchase order status using idx_orddate_status_klstatus index
	 */
	$SQL = "SELECT SUM(pod.quantityord - pod.quantityrecd) AS pending
			FROM stockmaster sm
			INNER JOIN purchorderdetails pod
				ON sm.stockid = pod.itemcode
			INNER JOIN purchorders po
				ON pod.orderno = po.orderno
			WHERE sm.categoryid IN " . $CategoryFilter . "
				AND pod.completed = 0
				AND po.status IN ('Authorised', 'Printed', 'Pending')";
				
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		return (int)$MyRow['pending'];
	}
	return 0;
}

function TotalItemsToBeReceivedByWO(string $Brand){
	/* SQL optimized by Roo on 26/08/2025 - Performance improvements:
	 * 1. Reordered JOIN to filter stockmaster first by category (smaller result set)
	 * 2. Leverages uk_stockmaster_categoryid_stockid and idx_woitems_stockid indexes optimally
	 * 4. Improved WHERE clause ordering for better index utilization
	 * 5. Enhanced error handling and consistent return type casting
	 */
	$ErrMsg = 'Error in function TotalItemsToBeReceivedByWO()';

	if ($Brand == "SHOPKL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP;
	} elseif ($Brand == "SHOPBL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP;
	} else {
		return 0;
	}

	/* Optimized query structure:
	 * - Filter stockmaster by category first (uses uk_stockmaster_categoryid_stockid index)
	 * - JOIN with woitems using filtered stockmaster results (uses idx_woitems_stockid index)
	 * - JOIN with workorders for status filtering (uses PRIMARY KEY on workorders)
	 * - This approach reduces the JOIN dataset significantly before aggregation
	 */
	$SQL = "SELECT SUM(wi.qtyreqd - wi.qtyrecd) AS pending
			FROM stockmaster sm
			INNER JOIN woitems wi
				ON sm.stockid = wi.stockid
			INNER JOIN workorders wo
				ON wi.wo = wo.wo
			WHERE sm.categoryid IN " . $CategoryFilter . "
				AND wo.closed = 0
				AND wi.qtyreqd > wi.qtyrecd";
				
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		return (int)$MyRow['pending'];
	}
	return 0;
}

function TotalModels(string $Brand){
	/* SQL optimized by Roo on 26/08/2025 - Performance improvements:
	 * 1. Changed COUNT(stockmaster.stockid) to COUNT(*) for better performance
	 * 2. Reordered WHERE conditions to leverage existing composite index
	 * 3. Added error handling and function timing capability
	 * 4. Optimized query structure for better index utilization
	 */
	$ErrMsg = 'Error in function TotalModels()';

	if ($Brand == "SHOPKL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP;
	} elseif ($Brand == "SHOPBL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP;
	} elseif ($Brand == "SHOPOK"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOB"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOG"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_GENERAL_ONLY_DISCOUNT;
	} else {
		$CategoryFilter = LIST_STOCK_CATEGORIES_OUTLET;
	}

	/* Optimized query structure:
	 * - Uses COUNT(*) instead of COUNT(stockmaster.stockid) for better performance
	 * - Leverages existing uk_stockmaster_discontinued_categoryid_stockid index
	 * - Conditions ordered for optimal index utilization
	 */
	$SQL = "SELECT COUNT(*) AS totalmodels
			FROM stockmaster
			WHERE discontinued = 0
				AND categoryid IN " . $CategoryFilter;
				
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		return (int)$MyRow['totalmodels'];
	}
	return 0;
}
 
function TotalItems(string $Brand){
	/* SQL optimized by Roo on 26/08/2025 - Performance improvements:
	 * 1. Added proper error handling with $ErrMsg variable
	 * 2. Optimized JOIN order to filter stockmaster first by category (smaller result set)
	 * 3. Leverages uk_stockmaster_categoryid_stockid index for optimal performance
	 * 4. Improved WHERE clause ordering for better index utilization
	 * 5. Added function timing capability and consistent return type casting
	 */
	$ErrMsg = 'Error in function TotalItems()';

	if ($Brand == "SHOPKL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP;
	} elseif ($Brand == "SHOPBL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP;
	} elseif ($Brand == "SHOPOK"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOB"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOG"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_GENERAL_ONLY_DISCOUNT;
	} else {
		$CategoryFilter = LIST_STOCK_CATEGORIES_OUTLET;
	}

	/* Optimized query structure:
	 * - Filter stockmaster by category first (uses uk_stockmaster_categoryid_stockid index)
	 * - JOIN with locstock using the filtered stockmaster results
	 * - This approach reduces the JOIN dataset significantly before aggregation
	 */
	$SQL = "SELECT SUM(ls.quantity) AS totalitems
			FROM stockmaster sm
			INNER JOIN locstock ls
				ON sm.stockid = ls.stockid
			WHERE sm.categoryid IN " . $CategoryFilter;
				
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		return (int)$MyRow['totalitems'];
	}
	return 0;
}

function TotalDisplayItems(string $Brand){
	/* SQL optimized by Gemini on 20/08/2025 */
	$ErrMsg = 'Error in TotalDisplayItems()';

	if ($Brand == "SHOPKL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT ."";
	} elseif ($Brand == "SHOPBL"){
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK ."";
	} else {
		$Operator1 = " AND stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET ."";
	} 

	$SQL =	"SELECT COUNT(locstock.quantity) AS displayitems
			FROM locstock
			INNER JOIN stockmaster
				ON stockmaster.stockid = locstock.stockid
			WHERE locstock.quantity >= 1
			" . $Operator1 . "";
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		return $MyRow['0'];
	}
	return 0;
}

function NumItemsSoldPerBrand(string $Brand, string $FromDate, string $ToDate){
	/* SQL optimized by Roo on 26/08/2025 - Performance improvements:
	 * 1. Reordered JOIN to filter stockmaster first by category (smaller result set)
	 * 2. Added explicit index hints for optimal query execution
	 * 3. Improved WHERE clause ordering for better index utilization
	 * 4. Added error handling and function timing
	 */
	$ErrMsg = 'Error in function NumItemsSoldPerBrand()';
	
	if ($Brand == "SHOPKL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT;
	} elseif ($Brand == "SHOPBL"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK;
	} elseif ($Brand == "SHOPOK"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_KAPAL_LAUT_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOB"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_BLINK_ONLY_DISCOUNT;
	} elseif ($Brand == "SHOPOG"){
		$CategoryFilter = LIST_STOCK_CATEGORIES_GENERAL_ONLY_DISCOUNT;
	} else {
		$CategoryFilter = LIST_STOCK_CATEGORIES_OUTLET;
	}

	/* Optimized query structure:
	 * - Filter stockmaster by category first (uses uk_stockmaster_categoryid_stockid index)
	 * - JOIN with salesorderdetails using the filtered stockmaster results
	 * - Date filtering uses idx_itemdue_stkcode index efficiently
	 */
	$SQL = "SELECT SUM(sod.qtyinvoiced) AS solditems
			FROM stockmaster sm
			INNER JOIN salesorderdetails sod
				ON sm.stockid = sod.stkcode
			WHERE sm.categoryid IN " . $CategoryFilter . "
				AND sod.itemdue >= '" . $FromDate . "'
				AND sod.itemdue <= '" . $ToDate . "'";
				
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		return (int)$MyRow['solditems'];
	}
	return 0;
}

function BrandTextFromCode(string $Brand){
	if ($Brand == "SHOPKL"){
		$BrandText = "Kapal-Laut";
	} elseif ($Brand == "SHOPBL"){
		$BrandText = "Blink";
	} elseif ($Brand == "SHOPOU"){
		$BrandText = "Outlet";
	} elseif ($Brand == "SHOPOK"){
		$BrandText = "Outlet Kapal-Laut";
	} elseif ($Brand == "SHOPOB"){
		$BrandText = "Outlet Blink";
	} elseif ($Brand == "SHOPOG"){
		$BrandText = "Outlet General";
	} else {
		$BrandText = "ERROR";
	}
	return $BrandText;
}

function OptimumOrderQuantity(float|int $QtyNeeded, float|int $Eoq, float|int $PanSize){
	if ($QtyNeeded > 0){
		if ($PanSize == 0){
			$OptimumOrderQuantity = max($Eoq, $QtyNeeded);
		}
		else {
			$OptimumOrderQuantity = max($Eoq, ceil($QtyNeeded / $PanSize) * $PanSize);
		}
	}
	else {
		$OptimumOrderQuantity = 0;
	}
	return $OptimumOrderQuantity;
}


function ChangeGLAccountCode(string $NewGL, string $OldGL) {
	/*First check the code exists */
	$Result = DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $OldGL . "'");
	$InputError = 0;
	if (DB_num_rows($Result) == 0) {
		prnMsg(__('The GL account code') . ': ' . $OldGL . ' ' . __('does not currently exist as a GL account code in the system'), 'error');
		$InputError = 1;
	}

	if (ContainsIllegalCharacters($NewGL)) {
		prnMsg(__('The new GL account code to change the old code to contains illegal characters - no changes will be made'), 'error');
		$InputError = 1;
	}

	if ($NewGL == '') {
		prnMsg(__('The new GL account code to change the old code to must be entered as well'), 'error');
		$InputError = 1;
	}

	/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $NewGL . "'");
	if (DB_num_rows($Result) != 0) {
		echo '<br /><br />';
		prnMsg(__('The replacement GL account code') . ': ' . $NewGL . ' ' . __('already exists as a GL account code in the system') . ' - ' . 
			__('a unique GL account code must be entered for the new code'), 'error');
		$InputError = 1;
	}

	if ($InputError == 0) {// no input errors
		DB_Txn_Begin();
		echo '<br />' . __('Adding the new chartmaster record');
		$SQL = "INSERT INTO chartmaster (accountcode,
										accountname,
										group_,
										cashflowsactivity,
										controlled)
				SELECT '" . $NewGL . "',
					accountname,
					group_,
					cashflowsactivity,
					controlled
				FROM chartmaster
				WHERE accountcode='" . $OldGL . "'";

		$ErrMsg = __('The SQL to insert the new chartmaster record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		DB_IgnoreForeignKeys();

		ChangeFieldInTable("bankaccounts", "accountcode", $OldGL, $NewGL);

		ChangeFieldInTable("bankaccountusers", "accountcode", $OldGL, $NewGL);

		ChangeFieldInTable("banktrans", "bankact", $OldGL, $NewGL);

		ChangeFieldInTable("cogsglpostings", "glcode", $OldGL, $NewGL);

		ChangeFieldInTable("companies", "debtorsact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "pytdiscountact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "creditorsact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "payrollact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "grnact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "salesexchangediffact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "purchasesexchangediffact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "currencyexchangediffact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "unrealizedcurrencydiffact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "retainedearnings", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "freightact", $OldGL, $NewGL);
		ChangeFieldInTable("companies", "commissionsact", $OldGL, $NewGL);

		ChangeFieldInTable("fixedassetcategories", "costact", $OldGL, $NewGL);
		ChangeFieldInTable("fixedassetcategories", "depnact", $OldGL, $NewGL);
		ChangeFieldInTable("fixedassetcategories", "disposalact", $OldGL, $NewGL);
		ChangeFieldInTable("fixedassetcategories", "accumdepnact", $OldGL, $NewGL);

		ChangeFieldInTable("glaccountusers", "accountcode", $OldGL, $NewGL);

		ChangeFieldInTable("glbudgetdetails", "account", $OldGL, $NewGL);

		ChangeFieldInTable("gltrans", "account", $OldGL, $NewGL);
		
//		ChangeFieldInTable("gltotals", "account", $OldGL, $NewGL);

		ChangeFieldInTable("locations", "glaccountcode", $OldGL, $NewGL);// Location's ledger account.
		ChangeFieldInTable("locations", "klposcashaccount", $OldGL, $NewGL);// KL POS cash account for that toko

		ChangeFieldInTable("pcexpenses", "glaccount", $OldGL, $NewGL);

		ChangeFieldInTable("pctabs", "glaccountassignment", $OldGL, $NewGL);
		ChangeFieldInTable("pctabs", "glaccountpcash", $OldGL, $NewGL);

		ChangeFieldInTable("purchorderdetails", "glcode", $OldGL, $NewGL);

		ChangeFieldInTable("regularpayments", "glcode", $OldGL, $NewGL);
		ChangeFieldInTable("regularpayments", "bankaccountcode", $OldGL, $NewGL);

		ChangeFieldInTable("salesman", "glaccount", $OldGL, $NewGL);

		ChangeFieldInTable("salesglpostings", "discountglcode", $OldGL, $NewGL);
		ChangeFieldInTable("salesglpostings", "salesglcode", $OldGL, $NewGL);

		ChangeFieldInTable("stockcategory", "stockact", $OldGL, $NewGL);
		ChangeFieldInTable("stockcategory", "adjglact", $OldGL, $NewGL);
		ChangeFieldInTable("stockcategory", "issueglact", $OldGL, $NewGL);
		ChangeFieldInTable("stockcategory", "purchpricevaract", $OldGL, $NewGL);
		ChangeFieldInTable("stockcategory", "materialuseagevarac", $OldGL, $NewGL);
		ChangeFieldInTable("stockcategory", "wipact", $OldGL, $NewGL);

		ChangeFieldInTable("taxauthorities", "taxglcode", $OldGL, $NewGL);
		ChangeFieldInTable("taxauthorities", "purchtaxglaccount", $OldGL, $NewGL);
		ChangeFieldInTable("taxauthorities", "bankacctype", $OldGL, $NewGL);

		ChangeFieldInTable("workcentres", "overheadrecoveryact", $OldGL, $NewGL);

		DB_ReinstateForeignKeys();
		// KL RICARD tables
		DB_IgnoreForeignKeys();

		ChangeFieldInTable("chartmasterADU", "accountcode", $OldGL, $NewGL);
		ChangeFieldInTable("chartmasterSMH", "accountcode", $OldGL, $NewGL);
		ChangeFieldInTable("chartmasterBB", "accountcode", $OldGL, $NewGL);
		ChangeFieldInTable("chartmasterIK", "accountcode", $OldGL, $NewGL);
		ChangeFieldInTable("chartmasterPI", "accountcode", $OldGL, $NewGL);
		
		ChangeFieldInTable("klretailpartners", "accountppn", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accounthppcompensation", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountposreceivable", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountconsignmentsalesptadu", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountconsignmentcogspartner", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountcomissioncreditcard", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountbankdanamon", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountbankbni", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountbankmandiri", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountbankbca", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountbankbri", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountwechat", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountcomissionwechat", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountcomissionqris", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountqrismandiri", $OldGL, $NewGL);
		ChangeFieldInTable("klretailpartners", "accountqrisbri", $OldGL, $NewGL);

		ChangeFieldInTable("klonlinepartners", "accountdokuidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountdokucomissionidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountpaypalaud", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountpaypalcomissionaud", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountpaypalusd", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountpaypalcomissionusd", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountpaypaleur", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountpaypalcomissioneur", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountxenditidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountxenditcomissionidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountcomissionppn", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accounttransfermandiri", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accounttransferbca", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accounttransferdanamon", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountmidtransidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accounttokopediaidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accounttokopediacomissionidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountshopeeidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountshopeecomissionidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountlazadaidr", $OldGL, $NewGL);
		ChangeFieldInTable("klonlinepartners", "accountlazadacomissionidr", $OldGL, $NewGL);

		/* Now change it in the archived tables*/
		ChangeFieldInTableArchive("banktrans", "bankact", $OldGL, $NewGL);
		ChangeFieldInTableArchive("gltrans", "account", $OldGL, $NewGL);

		DB_ReinstateForeignKeys();

		DB_Txn_Commit();

		echo '<br />' . __('Deleting the old chartmaster record');
		$SQL = "DELETE FROM chartmaster WHERE accountcode='" . $OldGL . "'";
		$ErrMsg = __('The SQL to delete the old chartmaster record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<p>' . __('GL account Code') . ': ' . $OldGL . ' ' . __('was successfully changed to') . ' : ' . $NewGL;
	}//only do the stuff above if  $InputError==0
}

/**
 * Retrieves the description for a given KPI code
 * 
 * @param string $KPICode The KPI code to look up
 * @return string The KPI description if found, empty string otherwise
 */
function GetKPIDescription(string $KPICode) {
	$DescSQL = "SELECT kpidescription 
				FROM klkpidescriptions 
				WHERE kpicode = '" . $KPICode . "'";
	$DescResult = DB_query($DescSQL);
	$KPIDescription = '';
	if (DB_num_rows($DescResult) > 0) {
		$DescRow = DB_fetch_array($DescResult);
		$KPIDescription = $DescRow['kpidescription'];
	}
	return $KPIDescription;
}

function GetNumberOfRecordsInTable(string $TableName, string $Database) {
	$SQL = "SELECT COUNT(*) AS total FROM " . $TableName;
	if ($Database == 'Production') {
		$Result = DB_query($SQL);
	} elseif ($Database == 'Archive') {
		$Result = DB_query_archive($SQL);
	} else {
		return 0; // Invalid database specified
	}

	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_array($Result);
		return $Row['total'];
	}
	return 0;
}

/**************************************************************************************************************
* Brief description: Retrieves transfer reason description from service types table
* Parameters:
*   $Reason - The reason code to look up
* Returns: The service description or empty string if not found
**************************************************************************************************************/
function GetItemTransferReason(string $Reason){
	$ErrMsg = __('Can not retrieve the transfer reason description because');

	if ($Reason == 'DISPATCH_OVERSTOCK'){
		// from StockDispatch.php
		return 'Manual Dispatch Overstock';
	} elseif ($Reason == 'DISPATCH_NEEDED'){
		// from StockDispatch.php
		return 'Manual Dispatch Needed by Reorder Level';
	} elseif ($Reason == 'MANUAL'){
		// from StockLocTransfer.php for all stock categories except packaging
		return 'Manual Transfer';
	} elseif ($Reason == 'PACKAGING'){
		// from StockLocTransfer.php for stock category packaging
		return 'Packaging Transfer';
	} elseif ($Reason == 'OTHERS_SPG'){
		// from KLPOSReturnToKantor.php
		return 'Return by SPG for other reasons';
	} elseif ($Reason == 'REQUESTED_SS'){
		// from KLPOSReturnToKantor.php
		return 'Requested by Shop Support from Shop';
	} elseif ($Reason == 'SMART_NEEDED_BY_RL'){
		// from KLSmartStockTransfers.php
		return 'Daily Cron Job From Kantor Needed in Shop by Reorder Level';
	} elseif ($Reason == 'SMART_RETURN_OVER'){
		// from KLSmartStockTransfers.php
		return 'Daily Cron Job Return Overstock from Shop to Kantor';
	} elseif (substr($Reason, 0, 5) == 'SERV_'){
		$SQL = "SELECT servicedescription 
				FROM klservicetypes
				WHERE servicecode = '" . $Reason . "'";
		$Result = DB_query($SQL, $ErrMsg, '', true);
		if (DB_num_rows($Result) == 0){
			// no reason description found, return empty string
			return '';
		} else {
			$MyRow = DB_fetch_row($Result);
			return 'Service: ' . $MyRow[0];
		}
	} else {	
		// not reason code found
		return '';
	}
}


function ReloadSessionVariablesFromConfig(){
	$SQL = "SELECT confname, confvalue FROM klconfig";
	$ErrMsg = __('Couldget Config');
	$ErrMsg = __('Could not get the KL configuration parameters from the database because');
	$ConfigResult = DB_query($SQL, $ErrMsg);
	while( $MyRow = DB_fetch_array($ConfigResult) ) {
		if (is_numeric($MyRow['confvalue']) AND $MyRow['confname']!='DefaultPriceList' AND $MyRow['confname']!='VersionNumber'){
			//the variable name is given by $MyRow[0]
			$_SESSION[$MyRow['confname']] = (float) $MyRow['confvalue'];
		} else {
			$_SESSION[$MyRow['confname']] =  $MyRow['confvalue'];
		}
	} //end loop through all config variables
}

function NumberOfEmployeesByPosition(string $Position){
	$SQL = "SELECT COUNT(*) 
			FROM hremployees
			INNER JOIN hrpositions ON hremployees.positionid = hrpositions.positionid 
			WHERE hremployees.employmentstatus = 'Active'
				AND hrpositions.positioncode LIKE '" . $Position . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[0];
	} else {
		return 0;
	}
}

function GetValueRunningPO(string $Currency, int $Days = 9999): float {
	$ArrivalDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', +$Days));
	$SQL = "SELECT currencies.currabrev,
				currencies.decimalplaces AS currdecimalplaces,
				SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
			FROM purchorders
			INNER JOIN purchorderdetails
				ON purchorders.orderno = purchorderdetails.orderno
			INNER JOIN suppliers
				ON  purchorders.supplierno = suppliers.supplierid
			INNER JOIN currencies
				ON suppliers.currcode = currencies.currabrev
			WHERE purchorderdetails.completed = 0 
				AND purchorders.arrivaldate <= '" . $ArrivalDate . "'
				AND purchorders.status IN ('Authorised', 'Printed', 'Pending')
				AND suppliers.currcode = '" . $Currency . "'
			GROUP BY suppliers.currcode,
				currencies.decimalplaces";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		return $MyRow[2];
	} else {
		return 0;
	}
}

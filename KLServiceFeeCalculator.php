<?php

include('includes/session.php');
$Title = _('Stock Service Price');
/* webERP manual links before header.php */
$ViewTopic= "Inventory";
$BookMark = "Service Fee";
include('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLDefines.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

if (isset($_GET['ServiceCode'])){
	$ServiceCode = trim(mb_strtoupper($_GET['ServiceCode']));
} elseif (isset($_POST['ServiceCode'])){
	$ServiceCode = trim(mb_strtoupper($_POST['ServiceCode']));
} else {
	$ServiceCode = '';
}

$result = DB_query("SELECT stockid
					FROM stockmaster
					WHERE stockid='".$StockID."'",
					_('Could not retrieve the requested item'),
					_('The SQL used to retrieve the items was'));
$myrow = DB_fetch_array($result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Stock Code') . ':<input type="text" data-type="no-illegal-chars" title ="'._('Input the stock code.').'" placeholder="'._('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';

$SQL = "SELECT servicecode,
			servicedescription
	FROM klservicetypes
	ORDER BY servicedescription";

$result = DB_query($SQL);
echo '<tr>
	<td>' . _('Type of Service') . ':</td>
	<td><select name="ServiceCode" required="required" autofocus="autofocus" >';
while($myrow = DB_fetch_array($result)) {
	if(isset($_POST['ServiceCode']) and $myrow['servicecode']==$_POST['ServiceCode']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $myrow['servicecode'] . '">' . $myrow['servicedescription'] . '</option>';
}
echo '</select></td>
	</tr>';

echo ' <input type="submit" name="ShowStatus" value="' . _('Check Service Fee') . '" />';
echo '<br /><br />';

if (($StockID != '') AND ($ServiceCode != '')){
	$Today  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
	$SQL = "SELECT stockmaster.description,
				stockmaster.categoryid,
				stockmaster.mbflag,
				stockmaster.discontinued,
				stockmaster.actualcost,
				stockmaster.klservicebyreplacement,
				prices.price,
				klservicetypes.pricetier01,
				klservicetypes.pricetier02,
				klservicetypes.pricetier03
			FROM stockmaster, prices, klservicetypes
			WHERE stockmaster.stockid = prices.stockid	
				AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
				AND prices.currabrev = '". CURRENCY_CODE ."'
				AND prices.startdate <= '". $Today. "' 
				AND klservicetypes.servicecode='".$ServiceCode."'
				AND stockmaster.stockid='".$StockID."'
			ORDER BY prices.startdate DESC";

	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);

	if (DB_num_rows($result) == 0){
		$Message = "Stock code can't be found";
	}else{
		if (($myrow['discontinued'] == 1) 
			OR (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET))){
			// for discounted or obsolete //
			if (($ServiceCode == "BENTUKBENGKOK") 
				OR ($ServiceCode == "CRYSTALLEPAS") 
				OR ($ServiceCode == "KOMPONENLEPAS") 
				OR ($ServiceCode == "LOCKRUSAK") 
				OR ($ServiceCode == "KURANGSHINNY") 
				OR ($ServiceCode == "TALILUSUH") 
				OR ($ServiceCode == "WIREBERKARAT") 
				OR ($ServiceCode == "_LAINLAIN")){
				$Message = "Service fee can't be calculated now. Send to office to be evaluated.";
			}else{
				if (($ServiceCode == "BERUBAHWARNA")
					OR ($ServiceCode == "KOMPONENPECAH") 
					OR ($ServiceCode == "KOMPONENRUSAK") 
					OR ($ServiceCode == "PATRI") 
					OR ($ServiceCode == "RANTAIPUTUS")){
					$Message = "CAN'T be serviced.";
				}else{
					if ($myrow['price'] <= RETAIL_PRICE_FOR_SERVICE_TIER_01){
						$FeeService = $myrow['pricetier01'];
					}elseif ($myrow['price'] <= RETAIL_PRICE_FOR_SERVICE_TIER_02){
						$FeeService = $myrow['pricetier02'];
					}else{
						$FeeService = $myrow['pricetier03'];
					}
					if ($myrow['klservicebyreplacement'] == 1){
						$FeeReplacement = $myrow['actualcost'] * FACTOR_SC_SERVICE_BY_REPLACEMENT;
					}else{
						$FeeReplacement = 0;
					}
					$Fee = round_basic_price(max($FeeService, $FeeReplacement),10000);
					$Message = "CAN be serviced at a fee of " . locale_number_format($Fee,0) . " IDR.";

					if ($ServiceCode == "MAGNETLEPAS"){
						$Message .= " Cost of components needed NOT included in this fee.";
					}
				}
			}
		}else{
			if ($ServiceCode == "_LAINLAIN"){
				$Message = "Service fee can't be calculated now. Send to office to be evaluated.";
			}else{
				if (($ServiceCode == "BERUBAHWARNA") 
					AND (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_BLINK))){
					$Message = "CAN'T be serviced.";
				}else{
					if ($myrow['price'] <= RETAIL_PRICE_FOR_SERVICE_TIER_01){
						$FeeService = $myrow['pricetier01'];
					}elseif ($myrow['price'] <= RETAIL_PRICE_FOR_SERVICE_TIER_02){
						$FeeService = $myrow['pricetier02'];
					}else{
						$FeeService = $myrow['pricetier03'];
					}
					if ($myrow['klservicebyreplacement'] == 1){
						$FeeReplacement = $myrow['actualcost'] * FACTOR_SC_SERVICE_BY_REPLACEMENT;
					}else{
						$FeeReplacement = 0;
					}
					$Fee = round_basic_price(max($FeeService, $FeeReplacement),10000);
					$Message = "CAN be serviced at a fee of " . locale_number_format($Fee,0) . " IDR.";

					if (($ServiceCode == "CRYSTALLEPAS") 
						OR ($ServiceCode == "KOMPONENLEPAS")
						OR ($ServiceCode == "KOMPONENRUSAK")
						OR ($ServiceCode == "KOMPONENPECAH")
						OR ($ServiceCode == "MAGNETLEPAS")){
						$Message .= " Cost of components needed NOT included in this fee.";
					}
				}
			}
		}
	}
	echo '<p class="page_title_text" align="center"><strong>' . $StockID . " - " . $myrow['description'] . '</strong></p>';
	echo '<p class="page_title_text" align="center"><strong>' . $Message . '</strong></p>';
}
include('includes/footer.php');

?>

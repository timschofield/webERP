<?php

include('includes/session.php');
$Title = _('Service Fee Calculator');
$ViewTopic= "Inventory";
$BookMark = "Service Fee";
include('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLDefines.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/UIGeneralFunctions.php'); 

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

$Result = DB_query("SELECT stockid
					FROM stockmaster
					WHERE stockid='".$StockID."'",
					_('Could not retrieve the requested item'),
					_('The SQL used to retrieve the items was'));
$MyRow = DB_fetch_array($Result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset><legend>' .$Title . '</legend>';
echo FieldToSelectOneText('StockID', $StockID, 21, 20, _('Stock Code'), '', '', '', true, false);
echo FieldToSelectOneServiceFee('ServiceCode', $ServiceCode, _('Type of Service'), '', '', '', true, true);
echo '</fieldset>';
echo OneButtonCenteredForm('ShowStatus', _('Check Service Fee'));

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
				AND prices.startdate <= CURRENT_DATE 
				AND klservicetypes.servicecode='".$ServiceCode."'
				AND stockmaster.stockid='".$StockID."'
			ORDER BY prices.startdate DESC";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	if (DB_num_rows($Result) == 0){
		$Message = "Stock code can't be found";
	}else{
		if (($MyRow['discontinued'] == 1) 
			OR (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET))){
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
					if ($MyRow['price'] <= RETAIL_PRICE_FOR_SERVICE_TIER_01){
						$FeeService = $MyRow['pricetier01'];
					}elseif ($MyRow['price'] <= RETAIL_PRICE_FOR_SERVICE_TIER_02){
						$FeeService = $MyRow['pricetier02'];
					}else{
						$FeeService = $MyRow['pricetier03'];
					}
					if ($MyRow['klservicebyreplacement'] == 1){
						$FeeReplacement = $MyRow['actualcost'] * FACTOR_SC_SERVICE_BY_REPLACEMENT;
					}else{
						$FeeReplacement = 0;
					}
					$Fee = round_multiple_of(max($FeeService, $FeeReplacement),10000);
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
					AND (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_BLINK))){
					$Message = "CAN'T be serviced.";
				}else{
					if ($MyRow['price'] <= RETAIL_PRICE_FOR_SERVICE_TIER_01){
						$FeeService = $MyRow['pricetier01'];
					}elseif ($MyRow['price'] <= RETAIL_PRICE_FOR_SERVICE_TIER_02){
						$FeeService = $MyRow['pricetier02'];
					}else{
						$FeeService = $MyRow['pricetier03'];
					}
					if ($MyRow['klservicebyreplacement'] == 1){
						$FeeReplacement = $MyRow['actualcost'] * FACTOR_SC_SERVICE_BY_REPLACEMENT;
					}else{
						$FeeReplacement = 0;
					}
					$Fee = round_multiple_of(max($FeeService, $FeeReplacement),10000);
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
	$TableTitleText = $StockID . " - " . $MyRow['description'];
	ShowTableTitle($TableTitleText);
	$TableTitleText = $Message;
	ShowTableTitle($TableTitleText);
}
include('includes/footer.php');

?>

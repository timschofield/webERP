<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Service Fee Calculator');
$ViewTopic= "Inventory";
$BookMark = "Service Fee";
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/UIGeneralFunctions.php'); 

include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLPrices.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');
include(__DIR__ . '/includes/KLPOSGeneral.php');

include(__DIR__ . '/includes/WebClientPrint/WebClientPrint.php');
include(__DIR__ . '/includes/KLESCPOSCommands.php');

// as the script uses _SESSION variables, reload just in case another user has been changing values in the meantime 
// because the script needs the latest values for the calculations
ReloadSessionVariablesFromConfig();

if (isset($_GET['Warranty'])){
	$Warranty = trim(mb_strtoupper($_GET['Warranty']));
} elseif (isset($_POST['Warranty'])){
	$Warranty = trim(mb_strtoupper($_POST['Warranty']));
} else {
	$Warranty = 'NO';
}

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

$Fee = -1; // Initialize fee as -1 if it can't be fixed
$FeeService = -1;
$FeeReplacement = -1;
$Message1 = '';
$Message2 = '';

$Result = DB_query("SELECT stockid
					FROM stockmaster
					WHERE stockid = '".$StockID."'",
					__('Could not retrieve the requested item'),
					__('The SQL used to retrieve the items was'));
$MyRow = DB_fetch_array($Result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset><legend>' .$Title . '</legend>';
echo FieldToSelectFromTwoOptions('NO', __('No Warranty'), 
                                'YES', __('Under warranty'), 
                                'Warranty', $Warranty, __('Is this product in warranty?'), '', '', '1', true, false);

echo FieldToSelectOneText('StockID', $StockID, 21, 20, __('Stock Code'), '', '', '2', true, false);
echo FieldToSelectOneServiceFee('ServiceCode', $ServiceCode, __('Type of Service'), '', '', '3', true, true);
echo '</fieldset>';
echo OneButtonCenteredForm('ShowStatus', __('Check Service Fee'));

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
				AND prices.currabrev = '". $_SESSION['CompanyRecord']['currencydefault'] ."'
				AND prices.startdate <= CURRENT_DATE 
				AND klservicetypes.servicecode='".$ServiceCode."'
				AND stockmaster.stockid='".$StockID."'
			ORDER BY prices.startdate DESC";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	if (DB_num_rows($Result) == 0){
		prnMsg(__('Stock code or price list can\'t be found'), 'warn');
	} else {
		if ($Warranty == 'YES'){
			$Fee = 0;
			$Message1 = "Service under warranty. No fee charged.";
			$Message2 = "";
		} elseif (($MyRow['discontinued'] == 1) 
			OR (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET))){
			// for discounted or obsolete //
			if (($ServiceCode == "SERV_BENTUKBENGKOK") 
				OR ($ServiceCode == "SERV_CRYSTALLEPAS") 
				OR ($ServiceCode == "SERV_KOMPONENLEPAS") 
				OR ($ServiceCode == "SERV_LOCKRUSAK") 
				OR ($ServiceCode == "SERV_KURANGSHINNY") 
				OR ($ServiceCode == "SERV_TALILUSUH") 
				OR ($ServiceCode == "SERV_WIREBERKARAT") 
				OR ($ServiceCode == "SERV__LAINLAIN")){
				$Message1 = "Service fee can't be calculated now. Send to office to be evaluated.";
				$Fee = -2;
			} else {
				if (($ServiceCode == "SERV_BERUBAHWARNA")
					OR ($ServiceCode == "SERV_KOMPONENPECAH") 
					OR ($ServiceCode == "SERV_KOMPONENRUSAK") 
					OR ($ServiceCode == "SERV_PATRI") 
					OR ($ServiceCode == "SERV_RANTAIPUTUS")){
					$Message1 = "CAN'T be serviced.";
					$Fee = -1;
				} else {
					if ($MyRow['price'] <= $_SESSION['RetailPriceServiceTier01']){
						$FeeService = $MyRow['pricetier01'];
					} elseif ($MyRow['price'] <= $_SESSION['RetailPriceServiceTier02']){
						$FeeService = $MyRow['pricetier02'];
					} else {
						$FeeService = $MyRow['pricetier03'];
					}
					if (($MyRow['klservicebyreplacement'] == 1)
						AND ($ServiceCode != "SERV_KOTOR")){
						$FeeReplacement = $MyRow['actualcost'] * $_SESSION['FactorStandardCostServiceReplacement'];
					} else {
						$FeeReplacement = 0;
					}
					$Fee = round_multiple_of(max($FeeService, $FeeReplacement),5000);
					$Message1 = "CAN be serviced.";
					$Message2 = "Fee of " . locale_number_format($Fee,0) . " IDR.";

					if ($ServiceCode == "MAGNETLEPAS"){
						$Message2 .= " Cost of components needed NOT included in this fee.";
					}
				}
			}
		} else {
			if ($ServiceCode == "SERV__LAINLAIN"){
				$Message1 = "Service fee can't be calculated now. Send to office to be evaluated.";
				$Fee = -2;
			} else {
				if (($ServiceCode == "SERV_BERUBAHWARNA") 
					AND (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_BLINK))){
					$Message1 = "CAN'T be serviced.";
					$Fee = -1;
				} else {
					if ($MyRow['price'] <= $_SESSION['RetailPriceServiceTier01']){
						$FeeService = $MyRow['pricetier01'];
					} elseif ($MyRow['price'] <= $_SESSION['RetailPriceServiceTier02']){
						$FeeService = $MyRow['pricetier02'];
					} else {
						$FeeService = $MyRow['pricetier03'];
					}
					if (($MyRow['klservicebyreplacement'] == 1)
						AND ($ServiceCode != "SERV_KOTOR")){
						$FeeReplacement = $MyRow['actualcost'] * $_SESSION['FactorStandardCostServiceReplacement'];
					} else {
						$FeeReplacement = 0;
					}
					$Fee = round_multiple_of(max($FeeService, $FeeReplacement),5000);
					$Message1 = "CAN be serviced.";
					$Message2 = "Fee of " . locale_number_format($Fee,0) . " IDR.";

					if (($ServiceCode == "SERV_CRYSTALLEPAS") 
						OR ($ServiceCode == "SERV_KOMPONENLEPAS")
						OR ($ServiceCode == "SERV_KOMPONENRUSAK")
						OR ($ServiceCode == "SERV_KOMPONENPECAH")
						OR ($ServiceCode == "SERV_MAGNETLEPAS")){
						$Message2 .= " Cost of components needed NOT included in this fee.";
					}
				}
			}
		}

		ShowTableTitle($StockID . " - " . $MyRow['description']);
		ShowTableTitle($Message1);
		if ($Message2 != ''){
			ShowTableTitle($Message2);
		}

		$TextToPrint = KLPrintCustomerServiceReceiptHeader(
			$StockID,
			$MyRow['description'],
			$Fee,
			$Message1,
			$Message2,
			$Warranty
		);
		$TextToPrint .= KLPrintCustomerServiceReceiptCustomerFooter();
		$TextToPrint .= KLPrintCustomerServiceReceiptHeader(
			$StockID,
			$MyRow['description'],
			$Fee,
			$Message1,
			$Message2,
			$Warranty
		);
		$TextToPrint .= KLPrintCustomerServiceReceiptShopFooter($ServiceCode);

		//################## PRINTING STUFF #####################
		$identifier = GetPOSIdentifier();
		$FileName = GetFilenameFromPOSIdentifier($identifier);
		file_put_contents($FileName, $TextToPrint);
		$TextActionToPrint = 'Print the Service Receipts';
		include(__DIR__ . '/includes/KLSilentPrinting.php');
		//################## PRINTING STUFF #####################
	}

}

include(__DIR__ . '/includes/footer.php');


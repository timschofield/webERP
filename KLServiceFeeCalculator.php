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

	if ((DB_num_rows($Result) == 0) AND strtoupper($StockID) != 'OBSOLETE') {
		prnMsg(__('Stock code or price list can\'t be found'), 'warn');
	} else {
		if (strtoupper($StockID) != 'OBSOLETE'){
			$PriceTier01 = $MyRow['pricetier01'];
			$PriceTier02 = $MyRow['pricetier02'];
			$PriceTier03 = $MyRow['pricetier03'];
			$ItemPrice = $MyRow['price'];
			$ItemDescription = $MyRow['description'];
			$ItemCategory = $MyRow['categoryid'];
			$ItemDiscontinued = $MyRow['discontinued'];
			$ItemServiceByReplacement = $MyRow['klservicebyreplacement'];
			$ItemActualCost = $MyRow['actualcost'];
		}else{
			$PriceTier01 = 9999999999;
			$PriceTier02 = 9999999999;
			$PriceTier03 = 9999999999;
			$ItemPrice = 9999999999;
			$ItemDescription = 'OBSOLETE';
			$ItemCategory = 'OBSOLETE';
			$ItemDiscontinued = 1;
			$ItemServiceByReplacement = 1;
			$ItemActualCost = 9999999999;

		}
		if ($Warranty == 'YES'){
			$Fee = 0;
			$Message1 = "Service under warranty. No fee charged.";
			$Message2 = "";
		} elseif (($ItemDiscontinued == 1) OR (strtoupper($StockID) == 'OBSOLETE')){
			// obsolete //
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
				$Message1 = "CAN'T be serviced.";
				$Fee = -1;
			}
		} elseif (ItemInList($ItemCategory, LIST_STOCK_CATEGORIES_OUTLET)){
			// for discounted //
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
					if ($ItemPrice <= $_SESSION['RetailPriceServiceTier01']){
						$FeeService = $PriceTier01;
					} elseif ($ItemPrice <= $_SESSION['RetailPriceServiceTier02']){
						$FeeService = $PriceTier02;
					} else {
						$FeeService = $PriceTier03;
					}
					if (($ItemServiceByReplacement == 1)
						AND ($ServiceCode != "SERV_KOTOR")){
						$FeeReplacement = $ItemActualCost * $_SESSION['FactorStandardCostServiceReplacement'];
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
			/* for current codes (not discounted, not obsolete) */
			if ($ServiceCode == "SERV__LAINLAIN"){
				$Message1 = "Service fee can't be calculated now. Send to office to be evaluated.";
				$Fee = -2;
			} else {
				if (($ServiceCode == "SERV_BERUBAHWARNA") 
					AND (ItemInList($ItemCategory, LIST_STOCK_CATEGORIES_BLINK))){
					$Message1 = "CAN'T be serviced.";
					$Fee = -1;
				} else {
					if ($ItemPrice <= $_SESSION['RetailPriceServiceTier01']){
						$FeeService = $PriceTier01;
					} elseif ($ItemPrice <= $_SESSION['RetailPriceServiceTier02']){
						$FeeService = $PriceTier02;
					} else {
						$FeeService = $PriceTier03;
					}
					if (($ItemServiceByReplacement == 1)
						AND ($ServiceCode != "SERV_KOTOR")){
						$FeeReplacement = $ItemActualCost * $_SESSION['FactorStandardCostServiceReplacement'];
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

		ShowTableTitle($StockID . " - " . $ItemDescription);
		ShowTableTitle($Message1);
		if ($Message2 != ''){
			ShowTableTitle($Message2);
		}

		$TextToPrint = KLPrintCustomerServiceReceiptHeader(
			$StockID,
			$ItemDescription,
			$Fee,
			$Message1,
			$Message2,
			$Warranty
		);
		$TextToPrint .= KLPrintCustomerServiceReceiptCustomerFooter();
		$TextToPrint .= KLPrintCustomerServiceReceiptHeader(
			$StockID,
			$ItemDescription,
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


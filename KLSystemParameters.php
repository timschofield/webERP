<?php
/*******************************************************************************************************
 * 
 * KL RICARD: Added ShopMode and ShopManagerEmail to the system parameter to enable web shop mode
 * Moved from the old and deprecated WebERP eCommerce module ShopParameters.php
 *
 * *****************************************************************************************************/

/* This script is for maintenance of the system parameters. */

require(__DIR__ . '/includes/session.php');
include(__DIR__ . '/includes/UIGeneralFunctions.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');

// as the script uses _SESSION variables, reload just in case another user has been changing values in the meantime 
// because the script needs the latest values for the calculations
ReloadSessionVariablesFromConfig();

$Title = __('KL System Parameters');
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'SystemParameters';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	// validate inputs are sensible
	/*
		Note: the X_ in the POST variables, the reason for this is to overcome globals=on replacing
		the actual system/overridden variables.
	*/

	if ($InputError !=1){

		$SQL = array();

// -------------------------------------
// KL RICARD  Specific Settings for PTADU webERP
// -------------------------------------
		if ($_SESSION['PPN_Percent'] != $_POST['X_PPN_Percent'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PPN_Percent']."' WHERE confname = 'PPN_Percent'";
		}

		if ($_SESSION['UpdateCurrencyRatesFrequency'] != $_POST['X_UpdateCurrencyRatesFrequency'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_UpdateCurrencyRatesFrequency']."' WHERE confname = 'UpdateCurrencyRatesFrequency'";
		}

		if ($_SESSION['Standard_Cost_Factor_Indonesia'] != $_POST['X_Standard_Cost_Factor_Indonesia'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Standard_Cost_Factor_Indonesia']."' WHERE confname = 'Standard_Cost_Factor_Indonesia'";
		}
		if ($_SESSION['Standard_Cost_Factor_Foreign'] != $_POST['X_Standard_Cost_Factor_Foreign'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Standard_Cost_Factor_Foreign']."' WHERE confname = 'Standard_Cost_Factor_Foreign'";
		}

		if ($_SESSION['Price_Factor_Minimum_KL'] != $_POST['X_Price_Factor_Minimum_KL'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Factor_Minimum_KL']."' WHERE confname = 'Price_Factor_Minimum_KL'";
		}
		if ($_SESSION['Price_Factor_Minimum_TopSales_KL'] != $_POST['X_Price_Factor_Minimum_TopSales_KL'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Factor_Minimum_TopSales_KL']."' WHERE confname = 'Price_Factor_Minimum_TopSales_KL'";
		}
		if ($_SESSION['Price_Factor_Maximum_BottomSales_KL'] != $_POST['X_Price_Factor_Maximum_BottomSales_KL'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Factor_Maximum_BottomSales_KL']."' WHERE confname = 'Price_Factor_Maximum_BottomSales_KL'";
		}

		if ($_SESSION['Price_Factor_Minimum_Blink'] != $_POST['X_Price_Factor_Minimum_Blink'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Factor_Minimum_Blink']."' WHERE confname = 'Price_Factor_Minimum_Blink'";
		}
		if ($_SESSION['Price_Factor_Minimum_TopSales_Blink'] != $_POST['X_Price_Factor_Minimum_TopSales_Blink'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Factor_Minimum_TopSales_Blink']."' WHERE confname = 'Price_Factor_Minimum_TopSales_Blink'";
		}
		if ($_SESSION['Price_Factor_Maximum_BottomSales_Blink'] != $_POST['X_Price_Factor_Maximum_BottomSales_Blink'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Factor_Maximum_BottomSales_Blink']."' WHERE confname = 'Price_Factor_Maximum_BottomSales_Blink'";
		}

		if ($_SESSION['Price_Factor_Minimum_General'] != $_POST['X_Price_Factor_Minimum_General'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Factor_Minimum_General']."' WHERE confname = 'Price_Factor_Minimum_General'";
		}

		if ($_SESSION['Price_Rounding_Step_01'] != $_POST['X_Price_Rounding_Step_01'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Rounding_Step_01']."' WHERE confname = 'Price_Rounding_Step_01'";
		}
		if ($_SESSION['Price_Rounding_Limit_01'] != $_POST['X_Price_Rounding_Limit_01'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Rounding_Limit_01']."' WHERE confname = 'Price_Rounding_Limit_01'";
		}
		if ($_SESSION['Price_Rounding_Step_02'] != $_POST['X_Price_Rounding_Step_02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Rounding_Step_02']."' WHERE confname = 'Price_Rounding_Step_02'";
		}
		if ($_SESSION['Price_Rounding_Limit_02'] != $_POST['X_Price_Rounding_Limit_02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Rounding_Limit_02']."' WHERE confname = 'Price_Rounding_Limit_02'";
		}
		if ($_SESSION['Price_Rounding_Step_03'] != $_POST['X_Price_Rounding_Step_03'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Rounding_Step_03']."' WHERE confname = 'Price_Rounding_Step_03'";
		}

		if ($_SESSION['Price_Rounding_Commercial_Module_02'] != $_POST['X_Price_Rounding_Commercial_Module_02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Rounding_Commercial_Module_02']."' WHERE confname = 'Price_Rounding_Commercial_Module_02'";
		}
		if ($_SESSION['Price_Rounding_Commercial_Step_02'] != $_POST['X_Price_Rounding_Commercial_Step_02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Price_Rounding_Commercial_Step_02']."' WHERE confname = 'Price_Rounding_Commercial_Step_02'";
		}

		if ($_SESSION['Small_Price_Calculated_Step_01'] != $_POST['X_Small_Price_Calculated_Step_01'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Small_Price_Calculated_Step_01']."' WHERE confname = 'Small_Price_Calculated_Step_01'";
		}
		if ($_SESSION['Small_Price_Corrected_Step_01'] != $_POST['X_Small_Price_Corrected_Step_01'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Small_Price_Corrected_Step_01']."' WHERE confname = 'Small_Price_Corrected_Step_01'";
		}
		if ($_SESSION['Small_Price_Calculated_Step_02'] != $_POST['X_Small_Price_Calculated_Step_02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Small_Price_Calculated_Step_02']."' WHERE confname = 'Small_Price_Calculated_Step_02'";
		}
		if ($_SESSION['Small_Price_Corrected_Step_02'] != $_POST['X_Small_Price_Corrected_Step_02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Small_Price_Corrected_Step_02']."' WHERE confname = 'Small_Price_Corrected_Step_02'";
		}
		if ($_SESSION['Small_Price_Calculated_Step_03'] != $_POST['X_Small_Price_Calculated_Step_03'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Small_Price_Calculated_Step_03']."' WHERE confname = 'Small_Price_Calculated_Step_03'";
		}
		if ($_SESSION['Small_Price_Corrected_Step_03'] != $_POST['X_Small_Price_Corrected_Step_03'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Small_Price_Corrected_Step_03']."' WHERE confname = 'Small_Price_Corrected_Step_03'";
		}
		if ($_SESSION['Small_Price_Calculated_Step_04'] != $_POST['X_Small_Price_Calculated_Step_04'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Small_Price_Calculated_Step_04']."' WHERE confname = 'Small_Price_Calculated_Step_04'";
		}
		if ($_SESSION['Small_Price_Corrected_Step_04'] != $_POST['X_Small_Price_Corrected_Step_04'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Small_Price_Corrected_Step_04']."' WHERE confname = 'Small_Price_Corrected_Step_04'";
		}

		if ($_SESSION['RetailPriceServiceTier01'] != $_POST['X_RetailPriceServiceTier01'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_RetailPriceServiceTier01']."' WHERE confname = 'RetailPriceServiceTier01'";
		}
		if ($_SESSION['RetailPriceServiceTier02'] != $_POST['X_RetailPriceServiceTier02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_RetailPriceServiceTier02']."' WHERE confname = 'RetailPriceServiceTier02'";
		}
		if ($_SESSION['FactorStandardCostServiceReplacement'] != $_POST['X_FactorStandardCostServiceReplacement'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_FactorStandardCostServiceReplacement']."' WHERE confname = 'FactorStandardCostServiceReplacement'";
		}

		if ($_SESSION['ShopMode'] != $_POST['X_ShopMode'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_ShopMode']."' WHERE confname = 'ShopMode'";
		}
		if ($_SESSION['ShopManagerEmail'] != $_POST['X_ShopManagerEmail'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '" . DB_escape_string($_POST['X_ShopManagerEmail']) ."' WHERE confname = 'ShopManagerEmail'";
		}

		if ($_SESSION['Maximum_QOH_To_Show_In_Marketplaces'] != $_POST['X_Maximum_QOH_To_Show_In_Marketplaces'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Maximum_QOH_To_Show_In_Marketplaces']."' WHERE confname = 'Maximum_QOH_To_Show_In_Marketplaces'";
		}
		if ($_SESSION['Minimum_QOH_To_Show_Item_In_Marketplaces'] != $_POST['X_Minimum_QOH_To_Show_Item_In_Marketplaces'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Minimum_QOH_To_Show_Item_In_Marketplaces']."' WHERE confname = 'Minimum_QOH_To_Show_Item_In_Marketplaces'";
		}

		if ($_SESSION['MaxItemsChangingPrice'] != $_POST['X_MaxItemsChangingPrice'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_MaxItemsChangingPrice']."' WHERE confname = 'MaxItemsChangingPrice'";
		}
		if ($_SESSION['MaxItemsChangingDisc20'] != $_POST['X_MaxItemsChangingDisc20'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_MaxItemsChangingDisc20']."' WHERE confname = 'MaxItemsChangingDisc20'";
		}
		if ($_SESSION['MaxItemsChangingDisc50'] != $_POST['X_MaxItemsChangingDisc50'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_MaxItemsChangingDisc50']."' WHERE confname = 'MaxItemsChangingDisc50'";
		}
		if ($_SESSION['MaxItemsChangingDisc80'] != $_POST['X_MaxItemsChangingDisc80'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_MaxItemsChangingDisc80']."' WHERE confname = 'MaxItemsChangingDisc80'";
		}
		if ($_SESSION['MaxItemsChangingPriceOrMovingDisc'] != $_POST['X_MaxItemsChangingPriceOrMovingDisc'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_MaxItemsChangingPriceOrMovingDisc']."' WHERE confname = 'MaxItemsChangingPriceOrMovingDisc'";
		}

		if ($_SESSION['InternalBankTransferSizeMultiple'] != $_POST['X_InternalBankTransferSizeMultiple'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_InternalBankTransferSizeMultiple']."' WHERE confname = 'InternalBankTransferSizeMultiple'";
		}
		if ($_SESSION['InternalOnlineTransferSizeMultiple'] != $_POST['X_InternalOnlineTransferSizeMultiple'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_InternalOnlineTransferSizeMultiple']."' WHERE confname = 'InternalOnlineTransferSizeMultiple'";
		}

		if ($_SESSION['PTADUDanamonMinSaldo'] != $_POST['X_PTADUDanamonMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUDanamonMinSaldo']."' WHERE confname = 'PTADUDanamonMinSaldo'";
		}
		if ($_SESSION['PTADUDanamonMaxSaldo'] != $_POST['X_PTADUDanamonMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUDanamonMaxSaldo']."' WHERE confname = 'PTADUDanamonMaxSaldo'";
		}
		if ($_SESSION['PTADUDanamonOverExcessSaldo'] != $_POST['X_PTADUDanamonOverExcessSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUDanamonOverExcessSaldo']."' WHERE confname = 'PTADUDanamonOverExcessSaldo'";
		}
		if ($_SESSION['PTADUMandiriMinSaldo'] != $_POST['X_PTADUMandiriMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUMandiriMinSaldo']."' WHERE confname = 'PTADUMandiriMinSaldo'";
		}
		if ($_SESSION['PTADUMandiriMaxSaldo'] != $_POST['X_PTADUMandiriMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUMandiriMaxSaldo']."' WHERE confname = 'PTADUMandiriMaxSaldo'";
		}
		if ($_SESSION['PTADUBCAMinSaldo'] != $_POST['X_PTADUBCAMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUBCAMinSaldo']."' WHERE confname = 'PTADUBCAMinSaldo'";
		}
		if ($_SESSION['PTADUBCAMaxSaldo'] != $_POST['X_PTADUBCAMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUBCAMaxSaldo']."' WHERE confname = 'PTADUBCAMaxSaldo'";
		}
		if ($_SESSION['PTADUBNIMinSaldo'] != $_POST['X_PTADUBNIMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUBNIMinSaldo']."' WHERE confname = 'PTADUBNIMinSaldo'";
		}
		if ($_SESSION['PTADUBNIMaxSaldo'] != $_POST['X_PTADUBNIMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUBNIMaxSaldo']."' WHERE confname = 'PTADUBNIMaxSaldo'";
		}
		if ($_SESSION['PTADUBRIMinSaldo'] != $_POST['X_PTADUBRIMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUBRIMinSaldo']."' WHERE confname = 'PTADUBRIMinSaldo'";
		}
		if ($_SESSION['PTADUBRIMaxSaldo'] != $_POST['X_PTADUBRIMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUBRIMaxSaldo']."' WHERE confname = 'PTADUBRIMaxSaldo'";
		}
		if ($_SESSION['PTADUOCBCMinSaldo'] != $_POST['X_PTADUOCBCMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUOCBCMinSaldo']."' WHERE confname = 'PTADUOCBCMinSaldo'";
		}
		if ($_SESSION['PTADUOCBCMaxSaldo'] != $_POST['X_PTADUOCBCMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUOCBCMaxSaldo']."' WHERE confname = 'PTADUOCBCMaxSaldo'";
		}
		if ($_SESSION['PTADUTokopediaMinSaldo'] != $_POST['X_PTADUTokopediaMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUTokopediaMinSaldo']."' WHERE confname = 'PTADUTokopediaMinSaldo'";
		}
		if ($_SESSION['PTADUTokopediaMaxSaldo'] != $_POST['X_PTADUTokopediaMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUTokopediaMaxSaldo']."' WHERE confname = 'PTADUTokopediaMaxSaldo'";
		}
		if ($_SESSION['PTADUShopeeMinSaldo'] != $_POST['X_PTADUShopeeMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUShopeeMinSaldo']."' WHERE confname = 'PTADUShopeeMinSaldo'";
		}
		if ($_SESSION['PTADUShopeeMaxSaldo'] != $_POST['X_PTADUShopeeMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUShopeeMaxSaldo']."' WHERE confname = 'PTADUShopeeMaxSaldo'";
		}
		if ($_SESSION['PTADUMidtransMinSaldo'] != $_POST['X_PTADUMidtransMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUMidtransMinSaldo']."' WHERE confname = 'PTADUMidtransMinSaldo'";
		}
		if ($_SESSION['PTADUMidtransMaxSaldo'] != $_POST['X_PTADUMidtransMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTADUMidtransMaxSaldo']."' WHERE confname = 'PTADUMidtransMaxSaldo'";
		}

		if ($_SESSION['PTSMHDanamonMinSaldo'] != $_POST['X_PTSMHDanamonMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHDanamonMinSaldo']."' WHERE confname = 'PTSMHDanamonMinSaldo'";
		}
		if ($_SESSION['PTSMHDanamonMaxSaldo'] != $_POST['X_PTSMHDanamonMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHDanamonMaxSaldo']."' WHERE confname = 'PTSMHDanamonMaxSaldo'";
		}
		if ($_SESSION['PTSMHDanamonOverExcessSaldo'] != $_POST['X_PTSMHDanamonOverExcessSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHDanamonOverExcessSaldo']."' WHERE confname = 'PTSMHDanamonOverExcessSaldo'";
		}
		if ($_SESSION['PTSMHMandiriMinSaldo'] != $_POST['X_PTSMHMandiriMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHMandiriMinSaldo']."' WHERE confname = 'PTSMHMandiriMinSaldo'";
		}
		if ($_SESSION['PTSMHMandiriMaxSaldo'] != $_POST['X_PTSMHMandiriMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHMandiriMaxSaldo']."' WHERE confname = 'PTSMHMandiriMaxSaldo'";
		}
		if ($_SESSION['PTSMHBCAMinSaldo'] != $_POST['X_PTSMHBCAMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHBCAMinSaldo']."' WHERE confname = 'PTSMHBCAMinSaldo'";
		}
		if ($_SESSION['PTSMHBCAMaxSaldo'] != $_POST['X_PTSMHBCAMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHBCAMaxSaldo']."' WHERE confname = 'PTSMHBCAMaxSaldo'";
		}
		if ($_SESSION['PTSMHBNIMinSaldo'] != $_POST['X_PTSMHBNIMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHBNIMinSaldo']."' WHERE confname = 'PTSMHBNIMinSaldo'";
		}
		if ($_SESSION['PTSMHBNIMaxSaldo'] != $_POST['X_PTSMHBNIMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHBNIMaxSaldo']."' WHERE confname = 'PTSMHBNIMaxSaldo'";
		}
		if ($_SESSION['PTSMHBRIMinSaldo'] != $_POST['X_PTSMHBRIMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHBRIMinSaldo']."' WHERE confname = 'PTSMHBRIMinSaldo'";
		}
		if ($_SESSION['PTSMHBRIMaxSaldo'] != $_POST['X_PTSMHBRIMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHBRIMaxSaldo']."' WHERE confname = 'PTSMHBRIMaxSaldo'";
		}
		if ($_SESSION['PTSMHOCBCMinSaldo'] != $_POST['X_PTSMHOCBCMinSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHOCBCMinSaldo']."' WHERE confname = 'PTSMHOCBCMinSaldo'";
		}
		if ($_SESSION['PTSMHOCBCMaxSaldo'] != $_POST['X_PTSMHOCBCMaxSaldo'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_PTSMHOCBCMaxSaldo']."' WHERE confname = 'PTSMHOCBCMaxSaldo'";
		}


		if ($_SESSION['DaysToPredictFutureSalesPerBrand'] != $_POST['X_DaysToPredictFutureSalesPerBrand'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_DaysToPredictFutureSalesPerBrand']."' WHERE confname = 'DaysToPredictFutureSalesPerBrand'";
		}
		if ($_SESSION['OptimumDaysStockPOWOForKL'] != $_POST['X_OptimumDaysStockPOWOForKL'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_OptimumDaysStockPOWOForKL']."' WHERE confname = 'OptimumDaysStockPOWOForKL'";
		}
		if ($_SESSION['OptimumDaysStockPOWOForBlink'] != $_POST['X_OptimumDaysStockPOWOForBlink'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_OptimumDaysStockPOWOForBlink']."' WHERE confname = 'OptimumDaysStockPOWOForBlink'";
		}

		if ($_SESSION['Usage_Days_For_Packaging_Stock'] != $_POST['X_Usage_Days_For_Packaging_Stock'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Usage_Days_For_Packaging_Stock']."' WHERE confname = 'Usage_Days_For_Packaging_Stock'";
		}
		if ($_SESSION['Forecast_Days_For_Packaging_Stock'] != $_POST['X_Forecast_Days_For_Packaging_Stock'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Forecast_Days_For_Packaging_Stock']."' WHERE confname = 'Forecast_Days_For_Packaging_Stock'";
		}
		if ($_SESSION['Forecast_Days_For_Packaging_Stock_Boxes'] != $_POST['X_Forecast_Days_For_Packaging_Stock_Boxes']) {
			$SQL[] = "UPDATE klconfig SET confvalue = '" . $_POST['X_Forecast_Days_For_Packaging_Stock_Boxes']
				. "' WHERE confname = 'Forecast_Days_For_Packaging_Stock_Boxes'";
		}
		if ($_SESSION['Factor_Gudang_Packaging'] != $_POST['X_Factor_Gudang_Packaging'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Factor_Gudang_Packaging']."' WHERE confname = 'Factor_Gudang_Packaging'";
		}
		if ($_SESSION['Factor_Gudang_Packaging_Paper_Inside_Box'] != $_POST['X_Factor_Gudang_Packaging_Paper_Inside_Box'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Factor_Gudang_Packaging_Paper_Inside_Box']."' WHERE confname = 'Factor_Gudang_Packaging_Paper_Inside_Box'";
		}

		if ($_SESSION['MinimumRLPackagingItemPerShop'] != $_POST['X_MinimumRLPackagingItemPerShop'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_MinimumRLPackagingItemPerShop']."' WHERE confname = 'MinimumRLPackagingItemPerShop'";
		}

		if ($_SESSION['CashKantorEndLastYearPTADU'] != $_POST['X_CashKantorEndLastYearPTADU'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_CashKantorEndLastYearPTADU']."' WHERE confname = 'CashKantorEndLastYearPTADU'";
		}
		if ($_SESSION['CashKantorEndLastYearPTSMH'] != $_POST['X_CashKantorEndLastYearPTSMH'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_CashKantorEndLastYearPTSMH']."' WHERE confname = 'CashKantorEndLastYearPTSMH'";
		}
		if ($_SESSION['CashKantorEndLastYearPTBB'] != $_POST['X_CashKantorEndLastYearPTBB'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_CashKantorEndLastYearPTBB']."' WHERE confname = 'CashKantorEndLastYearPTBB'";
		}
		if ($_SESSION['USDMaxEasyPurchasePerMonth'] != $_POST['X_USDMaxEasyPurchasePerMonth'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_USDMaxEasyPurchasePerMonth']."' WHERE confname = 'USDMaxEasyPurchasePerMonth'";
		}
		if ($_SESSION['SaldoADUDanamonUSDMin'] != $_POST['X_SaldoADUDanamonUSDMin'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_SaldoADUDanamonUSDMin']."' WHERE confname = 'SaldoADUDanamonUSDMin'";
		}
		if ($_SESSION['SaldoADUPayoneerUSDMin'] != $_POST['X_SaldoADUPayoneerUSDMin'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_SaldoADUPayoneerUSDMin']."' WHERE confname = 'SaldoADUPayoneerUSDMin'";
		}
		if ($_SESSION['SaldoADUPayoneerUSDMax'] != $_POST['X_SaldoADUPayoneerUSDMax'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_SaldoADUPayoneerUSDMax']."' WHERE confname = 'SaldoADUPayoneerUSDMax'";
		}
		if ($_SESSION['SaldoADUAirwallexUSDMin'] != $_POST['X_SaldoADUAirwallexUSDMin'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_SaldoADUAirwallexUSDMin']."' WHERE confname = 'SaldoADUAirwallexUSDMin'";
		}
		if ($_SESSION['SaldoADUAirwallexUSDMax'] != $_POST['X_SaldoADUAirwallexUSDMax'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_SaldoADUAirwallexUSDMax']."' WHERE confname = 'SaldoADUAirwallexUSDMax'";
		}

		if ($_SESSION['TopSalesNumberOfDays'] != $_POST['X_TopSalesNumberOfDays'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_TopSalesNumberOfDays']."' WHERE confname = 'TopSalesNumberOfDays'";
		}
		if ($_SESSION['TopSalesSince'] != $_POST['X_TopSalesSince'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_TopSalesSince']."' WHERE confname = 'TopSalesSince'";
		}

		if ($_SESSION['AverageInvoiceValueNumberDays'] != $_POST['X_AverageInvoiceValueNumberDays'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueNumberDays']."' WHERE confname = 'AverageInvoiceValueNumberDays'";
		}

		if ($_SESSION['AverageInvoiceValueKL01'] != $_POST['X_AverageInvoiceValueKL01'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueKL01']."' WHERE confname = 'AverageInvoiceValueKL01'";
		}
		if ($_SESSION['AverageInvoiceValueKL02'] != $_POST['X_AverageInvoiceValueKL02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueKL02']."' WHERE confname = 'AverageInvoiceValueKL02'";
		}
		if ($_SESSION['AverageInvoiceValueKL03'] != $_POST['X_AverageInvoiceValueKL03'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueKL03']."' WHERE confname = 'AverageInvoiceValueKL03'";
		}
		if ($_SESSION['AverageInvoiceValueKL04'] != $_POST['X_AverageInvoiceValueKL04'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueKL04']."' WHERE confname = 'AverageInvoiceValueKL04'";
		}
		if ($_SESSION['AverageInvoiceValueKL05'] != $_POST['X_AverageInvoiceValueKL05'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueKL05']."' WHERE confname = 'AverageInvoiceValueKL05'";
		}
		if ($_SESSION['AverageInvoiceValueKL06'] != $_POST['X_AverageInvoiceValueKL06'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueKL06']."' WHERE confname = 'AverageInvoiceValueKL06'";
		}
		if ($_SESSION['AverageInvoiceValueKL07'] != $_POST['X_AverageInvoiceValueKL07'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueKL07']."' WHERE confname = 'AverageInvoiceValueKL07'";
		}
		if ($_SESSION['AverageInvoiceValueKL08'] != $_POST['X_AverageInvoiceValueKL08'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueKL08']."' WHERE confname = 'AverageInvoiceValueKL08'";
		}

		if ($_SESSION['AverageInvoiceValueBlink01'] != $_POST['X_AverageInvoiceValueBlink01'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueBlink01']."' WHERE confname = 'AverageInvoiceValueBlink01'";
		}
		if ($_SESSION['AverageInvoiceValueBlink02'] != $_POST['X_AverageInvoiceValueBlink02'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueBlink02']."' WHERE confname = 'AverageInvoiceValueBlink02'";
		}
		if ($_SESSION['AverageInvoiceValueBlink03'] != $_POST['X_AverageInvoiceValueBlink03'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueBlink03']."' WHERE confname = 'AverageInvoiceValueBlink03'";
		}
		if ($_SESSION['AverageInvoiceValueBlink04'] != $_POST['X_AverageInvoiceValueBlink04'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueBlink04']."' WHERE confname = 'AverageInvoiceValueBlink04'";
		}
		if ($_SESSION['AverageInvoiceValueBlink05'] != $_POST['X_AverageInvoiceValueBlink05'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueBlink05']."' WHERE confname = 'AverageInvoiceValueBlink05'";
		}
		if ($_SESSION['AverageInvoiceValueBlink06'] != $_POST['X_AverageInvoiceValueBlink06'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueBlink06']."' WHERE confname = 'AverageInvoiceValueBlink06'";
		}
		if ($_SESSION['AverageInvoiceValueBlink07'] != $_POST['X_AverageInvoiceValueBlink07'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueBlink07']."' WHERE confname = 'AverageInvoiceValueBlink07'";
		}
		if ($_SESSION['AverageInvoiceValueBlink08'] != $_POST['X_AverageInvoiceValueBlink08'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_AverageInvoiceValueBlink08']."' WHERE confname = 'AverageInvoiceValueBlink08'";
		}

// -------------------------------------
// KL RICARD  Specific Settings for PTADU webERP
// -------------------------------------
		$ErrMsg =  __('The system configuration could not be updated because');
		if (sizeof($SQL) >= 1 ) {
			DB_Txn_Begin();
			foreach ($SQL as $Line) {
				$Result = DB_query($Line, $ErrMsg);
			}
			DB_Txn_Commit();
		}

		prnMsg( __('KL System configuration updated'),'success');

		$ForceConfigReload = true; // Required to force a load even if stored in the session vars
		include(__DIR__ . '/includes/GetConfig.php');
		$ForceConfigReload = false;
	} else {
		prnMsg( __('Validation failed') . ', ' . __('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

// -------------------------------------
// KL RICARD  Specific Settings for PTADU webERP
// -------------------------------------

echo '<fieldset>
	<legend>' . __('ADU webERP Configuration Options') . '</legend>';

	echo '<fieldset>
		<legend>' . __('Indonesia Tax Settings') . '</legend>';
		echo FieldToSelectOneNumber('X_PPN_Percent',  $_SESSION['PPN_Percent'], 6, 5, 'PPN (in %)', '', '', '100');
	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Currency Settings') . '</legend>';
		echo FieldToSelectOneNumber('X_UpdateCurrencyRatesFrequency',  $_SESSION['UpdateCurrencyRatesFrequency'], 6, 5, 'Frequency of updating currency rates (in days)', '', '', '100');
	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Standard Cost Settings') . '</legend>';
		echo FieldToSelectOneNumber('X_Standard_Cost_Factor_Indonesia',  $_SESSION['Standard_Cost_Factor_Indonesia'], 6, 5, 'Factor to multiply the purchasing price for items purchased in Indonesia', '', '', '101');
		echo FieldToSelectOneNumber('X_Standard_Cost_Factor_Foreign',  $_SESSION['Standard_Cost_Factor_Foreign'], 6, 5, 'Factor to multiply the purchasing price for items purchased outside Indonesia', '', '', '102');
	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Retail Price Settings') . '</legend>';
		echo '<fieldset>
			<legend>' . __('Retail Price Settings for KL items') . '</legend>';
			echo FieldToSelectOneNumber('X_Price_Factor_Minimum_KL',  $_SESSION['Price_Factor_Minimum_KL'], 6, 5, 'Minimum Standard Cost to Price Factor for KL items', 'Retail >= Factor x Standard Cost', '', '110');
			echo FieldToSelectOneNumber('X_Price_Factor_Minimum_TopSales_KL',  $_SESSION['Price_Factor_Minimum_TopSales_KL'], 6, 5, 'Minimum Standard Cost to Price Factor for Top Sales KL items', 'Retail Top Sales >= Factor x Standard Cost', '', '111');
			echo FieldToSelectOneNumber('X_Price_Factor_Maximum_BottomSales_KL',  $_SESSION['Price_Factor_Maximum_BottomSales_KL'], 6, 5, 'Maximum Standard Cost to Price Factor for Bottom Sales KL items', 'Retail Bottom Sales <= Factor x Standard Cost', '', '112');
		echo '</fieldset><br />';

		echo '<fieldset>
			<legend>' . __('Retail Price Settings for Blink items') . '</legend>';
			echo FieldToSelectOneNumber('X_Price_Factor_Minimum_Blink',  $_SESSION['Price_Factor_Minimum_Blink'], 6, 5, 'Minimum Standard Cost to Price Factor for Blink items', 'Retail >= Factor x Standard Cost', '', '113');
			echo FieldToSelectOneNumber('X_Price_Factor_Minimum_TopSales_Blink',  $_SESSION['Price_Factor_Minimum_TopSales_Blink'], 6, 5, 'Minimum Standard Cost to Price Factor for Top Sales Blink items', 'Retail Top Sales >= Factor x Standard Cost', '', '114');
			echo FieldToSelectOneNumber('X_Price_Factor_Maximum_BottomSales_Blink',  $_SESSION['Price_Factor_Maximum_BottomSales_Blink'], 6, 5, 'Maximum Standard Cost to Price Factor for Bottom Sales Blink items', 'Retail Bottom Sales <= Factor x Standard Cost', '', '115');
		echo '</fieldset><br />';

		echo '<fieldset>
			<legend>' . __('Retail Price Settings for General items') . '</legend>';
			echo FieldToSelectOneNumber('X_Price_Factor_Minimum_General',  $_SESSION['Price_Factor_Minimum_General'], 6, 5, 'Minimum Standard Cost to Price Factor for General items', 'Retail >= Factor x Standard Cost', '', '116');
		echo '</fieldset><br />';
	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Retail Price Rounding') . '</legend>';

		echo '<fieldset>
			<legend>' . __('Retail Price Rounding Steps') . '</legend>';
			echo FieldToSelectOneNumber('X_Price_Rounding_Step_01',  $_SESSION['Price_Rounding_Step_01'], 12, 11, 'Rounding Step for retail prices below limit 01', '', '', '120');
			echo FieldToSelectOneNumber('X_Price_Rounding_Limit_01',  $_SESSION['Price_Rounding_Limit_01'], 12, 11, 'Retail Price Limit 01', '', '', '121');
			echo FieldToSelectOneNumber('X_Price_Rounding_Step_02',  $_SESSION['Price_Rounding_Step_02'], 12, 11, 'Rounding Step for retail prices below limit 02', '', '', '122');
			echo FieldToSelectOneNumber('X_Price_Rounding_Limit_02',  $_SESSION['Price_Rounding_Limit_02'], 12, 11, 'Retail Price Limit 02', '', '', '123');
			echo FieldToSelectOneNumber('X_Price_Rounding_Step_03',  $_SESSION['Price_Rounding_Step_03'], 12, 11, 'Rounding Step for retail prices over limit 02', '', '', '123');
		echo '</fieldset><br />';

		echo '<fieldset>
			<legend>' . __('Retail Price Commercial Rounding Down') . '</legend>';
			echo FieldToSelectOneNumber('X_Price_Rounding_Commercial_Module_02',  $_SESSION['Price_Rounding_Commercial_Module_02'], 12, 11, 'Retail Price Commercial Module 02', '', '', '130');
			echo FieldToSelectOneNumber('X_Price_Rounding_Commercial_Step_02',  $_SESSION['Price_Rounding_Commercial_Step_02'], 12, 11, 'Retail Price Commercial Step 02', '', '', '131');
		echo '</fieldset><br />';

		echo '<fieldset>
			<legend>' . __('Small Retail Price Corrections') . '</legend>';
			echo FieldToSelectOneNumber('X_Small_Price_Calculated_Step_01',  $_SESSION['Small_Price_Calculated_Step_01'], 12, 11, 'For Calculated Retail Prices Smaller than 01', '', '', '140');
			echo FieldToSelectOneNumber('X_Small_Price_Corrected_Step_01',  $_SESSION['Small_Price_Corrected_Step_01'], 12, 11, 'Set Corrected Retail Price 01', '', '', '141');
			echo FieldToSelectOneNumber('X_Small_Price_Calculated_Step_02',  $_SESSION['Small_Price_Calculated_Step_02'], 12, 11, 'For Calculated Retail Prices Smaller than 02', '', '', '142');
			echo FieldToSelectOneNumber('X_Small_Price_Corrected_Step_02',  $_SESSION['Small_Price_Corrected_Step_02'], 12, 11, 'Set Corrected Retail Price 02', '', '', '143');
			echo FieldToSelectOneNumber('X_Small_Price_Calculated_Step_03',  $_SESSION['Small_Price_Calculated_Step_03'], 12, 11, 'For Calculated Retail Prices Smaller than 03', '', '', '144');
			echo FieldToSelectOneNumber('X_Small_Price_Corrected_Step_03',  $_SESSION['Small_Price_Corrected_Step_03'], 12, 11, 'Set Corrected Retail Price 03', '', '', '145');
			echo FieldToSelectOneNumber('X_Small_Price_Calculated_Step_04',  $_SESSION['Small_Price_Calculated_Step_04'], 12, 11, 'For Calculated Retail Prices Smaller than 04', '', '', '146');
			echo FieldToSelectOneNumber('X_Small_Price_Corrected_Step_04',  $_SESSION['Small_Price_Corrected_Step_04'], 12, 11, 'Set Corrected Retail Price 04', '', '', '147');
		echo '</fieldset><br />';
	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Repair Fee Calculator') . '</legend>';
		echo FieldToSelectOneNumber('X_RetailPriceServiceTier01',  $_SESSION['RetailPriceServiceTier01'], 12, 11, 'Retail Price Tier 01 for repair fees', '', '', '130');
		echo FieldToSelectOneNumber('X_RetailPriceServiceTier02',  $_SESSION['RetailPriceServiceTier02'], 12, 11, 'Retail Price Tier 02 for repair fees', '', '', '131');
		echo FieldToSelectOneNumber('X_FactorStandardCostServiceReplacement',  $_SESSION['FactorStandardCostServiceReplacement'], 6, 5, 'Standard Cost Factor in case of Item Replacement', '', '', '132');
	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Opencart Online Shop Settings') . '</legend>';
		// Moved from ShopParameters.php It is the only setting we are using to check if the Opencart shop is in test or live mode
		echo FieldToSelectFromTwoOptions('live', __('Live'), 
										'test', __('Test'), 
										'X_ShopMode', $_SESSION['ShopMode'], __('Test or Live Mode'),
										__('Set to live mode when the shop is active. No PayPal or credit card transactions will be processed in test mode'), '', '200', true, false);	
		echo FieldToSelectOneEmail('X_ShopManagerEmail', $_SESSION['ShopManagerEmail'], 51, 50, __('Online Shop Manager Email'), __('Enter the email address of the online shop manager.'), '201');
	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Marketplaces Shop Settings') . '</legend>';

		echo '<fieldset>
			<legend>' . __('Marketplace QOH Settings') . '</legend>';
			echo FieldToSelectOneNumber('X_Maximum_QOH_To_Show_In_Marketplaces',  $_SESSION['Maximum_QOH_To_Show_In_Marketplaces'], 6, 5, 'QOH Max to show on Marketplaces', 'if we have more than X then, we will show QOH=X in marketplaces to avoid unneeded updates and to create scarcity', '', '300');
			echo FieldToSelectOneNumber('X_Minimum_QOH_To_Show_Item_In_Marketplaces',  $_SESSION['Minimum_QOH_To_Show_Item_In_Marketplaces'], 6, 5, 'Hide item in marketplaces if QOH below', 'if we have less than X then we consider QOH = 0 for the marketplaces to avoid cancelled orders and bad reviews', '', '301');
		echo '</fieldset><br />';
	echo '</fieldset><br />';
echo '</fieldset><br />';

echo '<fieldset>
	<legend>' . __('Control Board Settings') . '</legend>';

	echo '<fieldset>
		<legend>' . __('Changing Items Settings') . '</legend>';
		echo FieldToSelectOneNumber('X_MaxItemsChangingPrice',  $_SESSION['MaxItemsChangingPrice'], 6, 5, 'Maximum # items changing price at the same time', 'Maximum # items changing price at the same time', '', '150');
		echo FieldToSelectOneNumber('X_MaxItemsChangingDisc20',  $_SESSION['MaxItemsChangingDisc20'], 6, 5, 'Maximum # items changing to discount 20% at the same time', 'Maximum # items changing to discount 20% at the same time', '', '150');
		echo FieldToSelectOneNumber('X_MaxItemsChangingDisc50',  $_SESSION['MaxItemsChangingDisc50'], 6, 5, 'Maximum # items changing to discount 50% at the same time', 'Maximum # items changing to discount 50% at the same time', '', '150');
		echo FieldToSelectOneNumber('X_MaxItemsChangingDisc80',  $_SESSION['MaxItemsChangingDisc80'], 6, 5, 'Maximum # items changing to discount 80% at the same time', 'Maximum # items changing to discount 80% at the same time', '', '150');
		echo FieldToSelectOneNumber('X_MaxItemsChangingPriceOrMovingDisc',  $_SESSION['MaxItemsChangingPriceOrMovingDisc'], 6, 5, 'Maximum # items changing price or moving to discount category at the same time', 'Maximum # items changing price or moving to discount category at the same time', '', '150');
	echo '</fieldset><br />';
	echo '<fieldset>
		<legend>' . __('Internal Bank Transfers Settings') . '</legend>';
		echo FieldToSelectOneNumber('X_InternalBankTransferSizeMultiple',  $_SESSION['InternalBankTransferSizeMultiple'], 15, 14, 'Internal banks transfer multiple size (IDR)', 'Internal banks transfer multiple size (IDR)', '', '150');
		echo FieldToSelectOneNumber('X_InternalOnlineTransferSizeMultiple',  $_SESSION['InternalOnlineTransferSizeMultiple'], 15, 14, 'Internal online transfer multiple size (IDR)', 'Internal online transfer multiple size (IDR)', '', '150');
		echo '<fieldset>
			<legend>' . __('Internal PTADU Bank Transfers Settings') . '</legend>';
				echo FieldToSelectOneNumber('X_PTADUDanamonMinSaldo',  $_SESSION['PTADUDanamonMinSaldo'], 15, 14, 'Minimum Saldo to be kept in Danamon PTADU (IDR)', 'Minimum Saldo to be kept in Danamon PTADU (IDR)', '', '150');
				echo FieldToSelectOneNumber('X_PTADUDanamonMaxSaldo',  $_SESSION['PTADUDanamonMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in Danamon PTADU (IDR)', 'Maximum Saldo to be kept in Danamon PTADU (IDR)', '', '151');
				echo FieldToSelectOneNumber('X_PTADUDanamonOverExcessSaldo',  $_SESSION['PTADUDanamonOverExcessSaldo'], 15, 14, 'Over Excess Saldo to be moved from Danamon to OCBC PTADU (IDR)', 'Over Excess Saldo to be kept in Danamon PTADU (IDR)', '', '152');
				echo FieldToSelectOneNumber('X_PTADUMandiriMinSaldo',  $_SESSION['PTADUMandiriMinSaldo'], 15, 14, 'Minimum Saldo to be kept in Mandiri PTADU (IDR)', 'Minimum Saldo to be kept in Mandiri PTADU (IDR)', '', '153');
				echo FieldToSelectOneNumber('X_PTADUMandiriMaxSaldo',  $_SESSION['PTADUMandiriMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in Mandiri PTADU (IDR)', 'Maximum Saldo to be kept in Mandiri PTADU (IDR)', '', '154');
				echo FieldToSelectOneNumber('X_PTADUBCAMinSaldo',  $_SESSION['PTADUBCAMinSaldo'], 15, 14, 'Minimum Saldo to be kept in BCA PTADU (IDR)', 'Minimum Saldo to be kept in BCA PTADU (IDR)', '', '155');
				echo FieldToSelectOneNumber('X_PTADUBCAMaxSaldo',  $_SESSION['PTADUBCAMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in BCA PTADU (IDR)', 'Maximum Saldo to be kept in BCA PTADU (IDR)', '', '156');
				echo FieldToSelectOneNumber('X_PTADUBNIMinSaldo',  $_SESSION['PTADUBNIMinSaldo'], 15, 14, 'Minimum Saldo to be kept in BNI PTADU (IDR)', 'Minimum Saldo to be kept in BNI PTADU (IDR)', '', '157');
				echo FieldToSelectOneNumber('X_PTADUBNIMaxSaldo',  $_SESSION['PTADUBNIMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in BNI PTADU (IDR)', 'Maximum Saldo to be kept in BNI PTADU (IDR)', '', '158');
				echo FieldToSelectOneNumber('X_PTADUBRIMinSaldo',  $_SESSION['PTADUBRIMinSaldo'], 15, 14, 'Minimum Saldo to be kept in BRI PTADU (IDR)', 'Minimum Saldo to be kept in BRI PTADU (IDR)', '', '159');
				echo FieldToSelectOneNumber('X_PTADUBRIMaxSaldo',  $_SESSION['PTADUBRIMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in BRI PTADU (IDR)', 'Maximum Saldo to be kept in BRI PTADU (IDR)', '', '160');
				echo FieldToSelectOneNumber('X_PTADUOCBCMinSaldo',  $_SESSION['PTADUOCBCMinSaldo'], 15, 14, 'Minimum Saldo to be kept in OCBC PTADU (IDR)', 'Minimum Saldo to be kept in OCBC PTADU (IDR)', '', '161');
				echo FieldToSelectOneNumber('X_PTADUOCBCMaxSaldo',  $_SESSION['PTADUOCBCMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in OCBC PTADU (IDR)', 'Maximum Saldo to be kept in OCBC PTADU (IDR)', '', '162');
				echo FieldToSelectOneNumber('X_PTADUTokopediaMinSaldo',  $_SESSION['PTADUTokopediaMinSaldo'], 15, 14, 'Minimum Saldo to be kept in Tokopedia PTADU (IDR)', 'Minimum Saldo to be kept in Tokopedia PTADU (IDR)', '', '163');
				echo FieldToSelectOneNumber('X_PTADUTokopediaMaxSaldo',  $_SESSION['PTADUTokopediaMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in Tokopedia PTADU (IDR)', 'Maximum Saldo to be kept in Tokopedia PTADU (IDR)', '', '164');
				echo FieldToSelectOneNumber('X_PTADUShopeeMinSaldo',  $_SESSION['PTADUShopeeMinSaldo'], 15, 14, 'Minimum Saldo to be kept in Shopee PTADU (IDR)', 'Minimum Saldo to be kept in Shopee PTADU (IDR)', '', '165');
				echo FieldToSelectOneNumber('X_PTADUShopeeMaxSaldo',  $_SESSION['PTADUShopeeMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in Shopee PTADU (IDR)', 'Maximum Saldo to be kept in Shopee PTADU (IDR)', '', '166');
				echo FieldToSelectOneNumber('X_PTADUMidtransMinSaldo',  $_SESSION['PTADUMidtransMinSaldo'], 15, 14, 'Minimum Saldo to be kept in Midtrans PTADU (IDR)', 'Minimum Saldo to be kept in Midtrans PTADU (IDR)', '', '167');
				echo FieldToSelectOneNumber('X_PTADUMidtransMaxSaldo',  $_SESSION['PTADUMidtransMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in Midtrans PTADU (IDR)', 'Maximum Saldo to be kept in Midtrans PTADU (IDR)', '', '168');
		echo '</fieldset><br />';
		echo '<fieldset>
			<legend>' . __('Internal PTSMH Bank Transfers Settings') . '</legend>';
				echo FieldToSelectOneNumber('X_PTSMHDanamonMinSaldo',  $_SESSION['PTSMHDanamonMinSaldo'], 15, 14, 'Minimum Saldo to be kept in Danamon PTSMH (IDR)', 'Minimum Saldo to be kept in Danamon PTSMH (IDR)', '', '150');
				echo FieldToSelectOneNumber('X_PTSMHDanamonMaxSaldo',  $_SESSION['PTSMHDanamonMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in Danamon PTSMH (IDR)', 'Maximum Saldo to be kept in Danamon PTSMH (IDR)', '', '151');
				echo FieldToSelectOneNumber('X_PTSMHDanamonOverExcessSaldo',  $_SESSION['PTSMHDanamonOverExcessSaldo'], 15, 14, 'Over Excess Saldo to be moved from Danamon to OCBC PTSMH (IDR)', 'Over Excess Saldo to be kept in Danamon PTSMH (IDR)', '', '152');
				echo FieldToSelectOneNumber('X_PTSMHMandiriMinSaldo',  $_SESSION['PTSMHMandiriMinSaldo'], 15, 14, 'Minimum Saldo to be kept in Mandiri PTSMH (IDR)', 'Minimum Saldo to be kept in Mandiri PTSMH (IDR)', '', '153');
				echo FieldToSelectOneNumber('X_PTSMHMandiriMaxSaldo',  $_SESSION['PTSMHMandiriMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in Mandiri PTSMH (IDR)', 'Maximum Saldo to be kept in Mandiri PTSMH (IDR)', '', '154');
				echo FieldToSelectOneNumber('X_PTSMHBCAMinSaldo',  $_SESSION['PTSMHBCAMinSaldo'], 15, 14, 'Minimum Saldo to be kept in BCA PTSMH (IDR)', 'Minimum Saldo to be kept in BCA PTSMH (IDR)', '', '155');
				echo FieldToSelectOneNumber('X_PTSMHBCAMaxSaldo',  $_SESSION['PTSMHBCAMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in BCA PTSMH (IDR)', 'Maximum Saldo to be kept in BCA PTSMH (IDR)', '', '156');
				echo FieldToSelectOneNumber('X_PTSMHBNIMinSaldo',  $_SESSION['PTSMHBNIMinSaldo'], 15, 14, 'Minimum Saldo to be kept in BNI PTSMH (IDR)', 'Minimum Saldo to be kept in BNI PTSMH (IDR)', '', '157');
				echo FieldToSelectOneNumber('X_PTSMHBNIMaxSaldo',  $_SESSION['PTSMHBNIMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in BNI PTSMH (IDR)', 'Maximum Saldo to be kept in BNI PTSMH (IDR)', '', '158');
				echo FieldToSelectOneNumber('X_PTSMHBRIMinSaldo',  $_SESSION['PTSMHBRIMinSaldo'], 15, 14, 'Minimum Saldo to be kept in BRI PTSMH (IDR)', 'Minimum Saldo to be kept in BRI PTSMH (IDR)', '', '159');
				echo FieldToSelectOneNumber('X_PTSMHBRIMaxSaldo',  $_SESSION['PTSMHBRIMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in BRI PTSMH (IDR)', 'Maximum Saldo to be kept in BRI PTSMH (IDR)', '', '160');
				echo FieldToSelectOneNumber('X_PTSMHOCBCMinSaldo',  $_SESSION['PTSMHOCBCMinSaldo'], 15, 14, 'Minimum Saldo to be kept in OCBC PTSMH (IDR)', 'Minimum Saldo to be kept in OCBC PTSMH (IDR)', '', '161');
				echo FieldToSelectOneNumber('X_PTSMHOCBCMaxSaldo',  $_SESSION['PTSMHOCBCMaxSaldo'], 15, 14, 'Maximum Saldo to be kept in OCBC PTSMH (IDR)', 'Maximum Saldo to be kept in OCBC PTSMH (IDR)', '', '162');
		echo '</fieldset><br />';

	echo '</fieldset><br />';
echo '</fieldset><br />';

echo '<fieldset>
	<legend>' . __('Performance Board Settings') . '</legend>';

	echo '<fieldset>
		<legend>' . __('Performance Board Section 1 Settings') . '</legend>';

		echo '<fieldset>
			<legend>' . __('Top Sales Settings') . '</legend>';
			echo FieldToSelectOneNumber('X_TopSalesNumberOfDays',  $_SESSION['TopSalesNumberOfDays'], 6, 5, 'Top X Retail days rating', '', '', '150');
			echo FieldToSelectOneDate('X_TopSalesSince', $_SESSION['TopSalesSince'], 'Starting date for Top Retail Days Table', '', '', '151');
		echo '</fieldset><br />';

		echo '<fieldset>
			<legend>' . __('Average Value of Invoice by brand') . '</legend>';			
			echo FieldToSelectOneNumber('X_AverageInvoiceValueNumberDays',  $_SESSION['AverageInvoiceValueNumberDays'], 6, 5, 'Number of days to analyze average invoice value', 'Number of days to analyze average customer behaviour by value of invoice', '', '209');			
			echo '<fieldset>
				<legend>' . __('Average Invoice Value for Kapal-Laut') . '</legend>';
				echo FieldToSelectOneNumber('X_AverageInvoiceValueKL01',  $_SESSION['AverageInvoiceValueKL01'], 12, 11, 'Average Invoice Value KL 01', 'Threshold step 1 for average invoice value (KL)', '', '210');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueKL02',  $_SESSION['AverageInvoiceValueKL02'], 12, 11, 'Average Invoice Value KL 02', 'Threshold step 2 for average invoice value (KL)', '', '211');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueKL03',  $_SESSION['AverageInvoiceValueKL03'], 12, 11, 'Average Invoice Value KL 03', 'Threshold step 3 for average invoice value (KL)', '', '212');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueKL04',  $_SESSION['AverageInvoiceValueKL04'], 12, 11, 'Average Invoice Value KL 04', 'Threshold step 4 for average invoice value (KL)', '', '213');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueKL05',  $_SESSION['AverageInvoiceValueKL05'], 12, 11, 'Average Invoice Value KL 05', 'Threshold step 5 for average invoice value (KL)', '', '214');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueKL06',  $_SESSION['AverageInvoiceValueKL06'], 12, 11, 'Average Invoice Value KL 06', 'Threshold step 6 for average invoice value (KL)', '', '215');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueKL07',  $_SESSION['AverageInvoiceValueKL07'], 12, 11, 'Average Invoice Value KL 07', 'Threshold step 7 for average invoice value (KL)', '', '216');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueKL08',  $_SESSION['AverageInvoiceValueKL08'], 12, 11, 'Average Invoice Value KL 08', 'Threshold step 8 for average invoice value (KL)', '', '217');
			echo '</fieldset><br />';
			echo '<fieldset>
				<legend>' . __('Average Invoice Value for Blink') . '</legend>';
				echo FieldToSelectOneNumber('X_AverageInvoiceValueBlink01',  $_SESSION['AverageInvoiceValueBlink01'], 12, 11, 'Average Invoice Value Blink 01', 'Threshold step 1 for average invoice value (Blink)', '', '220');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueBlink02',  $_SESSION['AverageInvoiceValueBlink02'], 12, 11, 'Average Invoice Value Blink 02', 'Threshold step 2 for average invoice value (Blink)', '', '221');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueBlink03',  $_SESSION['AverageInvoiceValueBlink03'], 12, 11, 'Average Invoice Value Blink 03', 'Threshold step 3 for average invoice value (Blink)', '', '222');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueBlink04',  $_SESSION['AverageInvoiceValueBlink04'], 12, 11, 'Average Invoice Value Blink 04', 'Threshold step 4 for average invoice value (Blink)', '', '223');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueBlink05',  $_SESSION['AverageInvoiceValueBlink05'], 12, 11, 'Average Invoice Value Blink 05', 'Threshold step 5 for average invoice value (Blink)', '', '224');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueBlink06',  $_SESSION['AverageInvoiceValueBlink06'], 12, 11, 'Average Invoice Value Blink 06', 'Threshold step 6 for average invoice value (Blink)', '', '225');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueBlink07',  $_SESSION['AverageInvoiceValueBlink07'], 12, 11, 'Average Invoice Value Blink 07', 'Threshold step 7 for average invoice value (Blink)', '', '226');
				echo FieldToSelectOneNumber('X_AverageInvoiceValueBlink08',  $_SESSION['AverageInvoiceValueBlink08'], 12, 11, 'Average Invoice Value Blink 08', 'Threshold step 8 for average invoice value (Blink)', '', '227');
			echo '</fieldset><br />';
		echo '</fieldset><br />';

	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Performance Board Section 2 Settings') . '</legend>';

		echo '<fieldset>
			<legend>' . __('Stock for Brand Settings') . '</legend>';
			echo FieldToSelectOneNumber('X_DaysToPredictFutureSalesPerBrand',  $_SESSION['DaysToPredictFutureSalesPerBrand'], 6, 5, '# Days to Predict Future Sales Per Brand', 'Number of days to predict future sales per brand', '', '150');
			echo FieldToSelectOneNumber('X_OptimumDaysStockPOWOForKL',  $_SESSION['OptimumDaysStockPOWOForKL'], 6, 5, '# Days of Optimum Current Stock+PO+WO for KL', 'Number of days of optimum current stock plus purchase orders and work orders for KL', '', '150');
			echo FieldToSelectOneNumber('X_OptimumDaysStockPOWOForBlink',  $_SESSION['OptimumDaysStockPOWOForBlink'], 6, 5, '# Days of Optimum Current Stock+PO+WO for Blink', 'Number of days of optimum current stock plus purchase orders and work orders for Blink', '', '151');
		echo '</fieldset><br />';

	echo '</fieldset><br />';

	echo '<fieldset>
		<legend>' . __('Performance Board Section 3 Settings') . '</legend>';

		echo '<fieldset>
			<legend>' . __('Packaging Forecast Settings') . '</legend>';
			echo FieldToSelectOneNumber('X_Usage_Days_For_Packaging_Stock',  $_SESSION['Usage_Days_For_Packaging_Stock'], 6, 5, '# Days of Packaging Usage for Forecast', 'Number of days  of Packaging Usage for Forecast', '', '150');
			echo FieldToSelectOneNumber('X_Forecast_Days_For_Packaging_Stock_Boxes', $_SESSION['Forecast_Days_For_Packaging_Stock_Boxes'], 6, 5, 'Optimum # Days of Packaging Stock Forecast (Boxes only)', 'Number of days to forecast packaging stock (Boxes only)', '',	'151');
			echo FieldToSelectOneNumber('X_Forecast_Days_For_Packaging_Stock',  $_SESSION['Forecast_Days_For_Packaging_Stock'], 6, 5, 'Optimum # Days of Packaging Stock Forecast (Except Boxes)', 'Number of days to forecast packaging stock', '', '151');
			echo FieldToSelectOneNumber('X_Factor_Gudang_Packaging',  $_SESSION['Factor_Gudang_Packaging'], 6, 5, 'Factor Gudang Packaging (except paper inside box)', 'Factor for packaging stock in gudang', '', '152');
			echo FieldToSelectOneNumber('X_Factor_Gudang_Packaging_Paper_Inside_Box',  $_SESSION['Factor_Gudang_Packaging_Paper_Inside_Box'], 6, 5, 'Factor Gudang Packaging Paper Inside Box', 'Factor for packaging stock in gudang for paper inside box', '', '153');
			echo FieldToSelectOneNumber('X_MinimumRLPackagingItemPerShop',  $_SESSION['MinimumRLPackagingItemPerShop'], 6, 5, 'Minimum Reorder Level for Packaging Items in Shops', 'Minimum reorder level for packaging items per shop', '', '154');
		echo '</fieldset><br />';

		echo '<fieldset>
			<legend>' . __('Status Cash Settings') . '</legend>';
			echo FieldToSelectOneNumber('X_CashKantorEndLastYearPTADU',  $_SESSION['CashKantorEndLastYearPTADU'], 15, 14, 'Cash Kantor End Last Year PTADU (IDR)', 'Cash Kantor End Last Year PTADU (IDR)', '', '150');
			echo FieldToSelectOneNumber('X_CashKantorEndLastYearPTSMH',  $_SESSION['CashKantorEndLastYearPTSMH'], 15, 14, 'Cash Kantor End Last Year PTSMH (IDR)', 'Cash Kantor End Last Year PTSMH (IDR)', '', '150');
			echo FieldToSelectOneNumber('X_CashKantorEndLastYearPTBB',  $_SESSION['CashKantorEndLastYearPTBB'], 15, 14, 'Cash Kantor End Last Year PTBB (IDR)', 'Cash Kantor End Last Year PTBB (IDR)', '', '150');
			echo FieldToSelectOneNumber('X_USDMaxEasyPurchasePerMonth',  $_SESSION['USDMaxEasyPurchasePerMonth'], 15, 14, 'USD Maximum Easy Purchase from IDR Monthly (USD)', 'USD Max Easy Purchase Per Month', '', '150');
			echo FieldToSelectOneNumber('X_SaldoADUDanamonUSDMin',  $_SESSION['SaldoADUDanamonUSDMin'], 15, 14, 'Minimum Saldo Danamon USD PTADU (USD)', 'Minimum Saldo Danamon USD ADU (USD)', '', '150');
			echo FieldToSelectOneNumber('X_SaldoADUPayoneerUSDMin',  $_SESSION['SaldoADUPayoneerUSDMin'], 15, 14, 'Minimum Saldo Payoneer USD PTADU (USD)', 'Minimum Saldo Payoneer USD ADU (USD)', '', '150');
			echo FieldToSelectOneNumber('X_SaldoADUPayoneerUSDMax',  $_SESSION['SaldoADUPayoneerUSDMax'], 15, 14, 'Maximum Saldo Payoneer USD PTADU (USD)', 'Maximum Saldo Payoneer USD ADU (USD)', '', '150');
			echo FieldToSelectOneNumber('X_SaldoADUAirwallexUSDMin',  $_SESSION['SaldoADUAirwallexUSDMin'], 15, 14, 'Minimum Saldo Airwallex USD PTADU (USD)', 'Minimum Saldo Airwallex USD ADU (USD)', '', '150');
			echo FieldToSelectOneNumber('X_SaldoADUAirwallexUSDMax',  $_SESSION['SaldoADUAirwallexUSDMax'], 15, 14, 'Maximum Saldo Airwallex USD PTADU (USD)', 'Maximum Saldo Airwallex USD ADU (USD)', '', '150');
		echo '</fieldset><br />';

	echo '</fieldset><br />';
echo '</fieldset><br />';
// -------------------------------------
// KL RICARD END Specific Settings for PTADU webERP
// -------------------------------------

echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Update') . '" />
	</div>
</form>';

include(__DIR__ . '/includes/footer.php');

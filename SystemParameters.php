<?php
/* This script is for maintenance of the system parameters. */

include('includes/session.php');
$Title = _('System Parameters');
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'SystemParameters';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

include('includes/CountriesArray.php');

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	/*
		Note: the X_ in the POST variables, the reason for this is to overcome globals=on replacing
		the actual system/overridden variables.
	*/
	if (mb_strlen($_POST['X_PastDueDays1']) > 3 OR !is_numeric($_POST['X_PastDueDays1']) ) {
		$InputError = 1;
		prnMsg(_('First overdue deadline days must be a number'),'error');
	} elseif (mb_strlen($_POST['X_PastDueDays2'])  > 3 OR !is_numeric($_POST['X_PastDueDays2']) ) {
		$InputError = 1;
		prnMsg(_('Second overdue deadline days must be a number'),'error');
	} elseif (mb_strlen($_POST['X_DefaultCreditLimit']) > 12 OR !is_numeric($_POST['X_DefaultCreditLimit']) ) {
		$InputError = 1;
		prnMsg(_('Default Credit Limit must be a number'),'error');
	} elseif (mb_strstr($_POST['X_RomalpaClause'], "'") OR mb_strlen($_POST['X_RomalpaClause']) > 5000) {
		$InputError = 1;
		prnMsg(_('The Romalpa Clause may not contain single quotes and may not be longer than 5000 chars'),'error');
	} elseif (mb_strlen($_POST['X_QuickEntries']) > 2 OR !is_numeric($_POST['X_QuickEntries']) OR
		$_POST['X_QuickEntries'] < 1 OR $_POST['X_QuickEntries'] > 99 ) {
		$InputError = 1;
		prnMsg(_('No less than 1 and more than 99 Quick entries allowed'),'error');
	} elseif (!is_numeric($_POST['X_MaxSerialItemsIssued']) or $_POST['X_MaxSerialItemsIssued'] < 1) {
		$InputError = 1;
		prnMsg(_('The maximum number of serial numbers issued must be numeric and greater than zero'), 'error');
	} elseif (mb_strlen($_POST['X_FreightChargeAppliesIfLessThan']) > 12 OR !is_numeric($_POST['X_FreightChargeAppliesIfLessThan']) ) {
		$InputError = 1;
		prnMsg(_('Freight Charge Applies If Less Than must be a number'),'error');
	} elseif ( !is_numeric($_POST['X_StandardCostDecimalPlaces']) OR
		$_POST['X_StandardCostDecimalPlaces'] < 0 OR $_POST['X_StandardCostDecimalPlaces'] > 4 ) {
		$InputError = 1;
		prnMsg(_('Standard Cost Decimal Places must be a number between 0 and 4'),'error');
	} elseif (mb_strlen($_POST['X_NumberOfPeriodsOfStockUsage']) > 2 OR !is_numeric($_POST['X_NumberOfPeriodsOfStockUsage']) OR
		$_POST['X_NumberOfPeriodsOfStockUsage'] < 1 OR $_POST['X_NumberOfPeriodsOfStockUsage'] > 12 ) {
		$InputError = 1;
		prnMsg(_('Financial period per year must be a number between 1 and 12'),'error');
	} elseif (!in_array(intval($_POST['X_StockUsageShowZeroWithinPeriodRange']), [0, 1])) {
		$InputError = 1;
		prnMsg(_('Unexpected Show Zero Counts Within Stock Usage Graph Period Range value.'), 'error');
	} elseif (mb_strlen($_POST['X_TaxAuthorityReferenceName']) >25) {
		$InputError = 1;
		prnMsg(_('The Tax Authority Reference Name must be 25 characters or less long'),'error');
	} elseif (mb_strlen($_POST['X_OverChargeProportion']) > 3 OR !is_numeric($_POST['X_OverChargeProportion']) OR
		$_POST['X_OverChargeProportion'] < 0 OR $_POST['X_OverChargeProportion'] > 100 ) {
		$InputError = 1;
		prnMsg(_('Over Charge Proportion must be a percentage'),'error');
	} elseif (mb_strlen($_POST['X_OverReceiveProportion']) > 3 OR !is_numeric($_POST['X_OverReceiveProportion']) OR
		$_POST['X_OverReceiveProportion'] < 0 OR $_POST['X_OverReceiveProportion'] > 100 ) {
		$InputError = 1;
		prnMsg(_('Over Receive Proportion must be a percentage'),'error');
	} elseif (mb_strlen($_POST['X_PageLength']) > 3 OR !is_numeric($_POST['X_PageLength']) OR
		$_POST['X_PageLength'] < 1 ) {
		$InputError = 1;
		prnMsg(_('Lines per page must be greater than 1'),'error');
	} elseif (mb_strlen($_POST['X_MonthsAuditTrail']) > 2 OR !is_numeric($_POST['X_MonthsAuditTrail']) OR
		$_POST['X_MonthsAuditTrail'] < 0 ) {
		$InputError = 1;
		prnMsg(_('The number of months of audit trail to keep must be zero or a positive number less than 100 months'),'error');
	}elseif (mb_strlen($_POST['X_DefaultTaxCategory']) > 1 OR !is_numeric($_POST['X_DefaultTaxCategory']) OR
		$_POST['X_DefaultTaxCategory'] < 1 ) {
		$InputError = 1;
		prnMsg(_('DefaultTaxCategory must be between 1 and 9'),'error');
	} elseif (mb_strlen($_POST['X_DefaultDisplayRecordsMax']) > 3 OR !is_numeric($_POST['X_DefaultDisplayRecordsMax']) OR
		$_POST['X_DefaultDisplayRecordsMax'] < 1 ) {
		$InputError = 1;
		prnMsg(_('Default maximum number of records to display must be between 1 and 500'),'error');
	}elseif (mb_strlen($_POST['X_MaxImageSize']) > 3 OR !is_numeric($_POST['X_MaxImageSize']) OR
		$_POST['X_MaxImageSize'] < 1 ) {
		$InputError = 1;
		prnMsg(_('The maximum size of item image files must be between 50 and 500 (NB this figure refers to KB)'),'error');
	}elseif (!IsEmailAddress($_POST['X_FactoryManagerEmail'])){
		$InputError = 1;
		prnMsg(_('The Factory Manager Email address does not appear to be valid'),'error');
	}elseif (!IsEmailAddress($_POST['X_PurchasingManagerEmail'])){
		$InputError = 1;
		prnMsg(_('The Purchasing Manager Email address does not appear to be valid'),'error');
	}elseif (!IsEmailAddress($_POST['X_InventoryManagerEmail']) AND $_POST['X_InventoryManagerEmail']!=''){
		$InputError = 1;
		prnMsg(_('The Inventory Manager Email address does not appear to be valid'),'error');
	}elseif (mb_strlen($_POST['X_FrequentlyOrderedItems']) > 2 OR !is_numeric($_POST['X_FrequentlyOrderedItems'])) {
		$InputError = 1;
		prnMsg(_('The number of frequently ordered items to display must be numeric'),'error');
	}elseif (strlen($_POST['X_SmtpSetting']) != 1 OR !is_numeric($_POST['X_SmtpSetting'])){
		$InputError = 1;
		prnMsg(_('The SMTP setting should be selected as Yes or No'),'error');
	}elseif (strlen($_POST['X_QualityLogSamples']) != 1 OR !is_numeric($_POST['X_QualityLogSamples'])){
		$InputError = 1;
		prnMsg(_('The Quality Log Samples setting should be selected as Yes or No'),'error');
	} elseif (mb_strstr($_POST['X_QualityProdSpecText'], "'") OR mb_strlen($_POST['X_QualityProdSpecText']) > 5000) {
		$InputError = 1;
		prnMsg(_('The Quality ProdSpec Text may not contain single quotes and may not be longer than 5000 chars'),'error');
	} elseif (mb_strstr($_POST['X_QualityCOAText'], "'") OR mb_strlen($_POST['X_QualityCOAText']) > 5000) {
		$InputError = 1;
		prnMsg(_('The Quality COA Text may not contain single quotes and may not be longer than 5000 chars'),'error');
	}

	if ($InputError !=1){

		$sql = array();

		if ($_SESSION['DefaultDateFormat'] != $_POST['X_DefaultDateFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultDateFormat']."' WHERE confname = 'DefaultDateFormat'";
		}
		if($DefaultTheme != $_POST['X_DefaultTheme']) {// If not equal, update the default theme.
			// BEGIN: Update the config.php file:
			$fhandle = fopen('config.php', 'r');
			if($fhandle) {
				$content = fread($fhandle, filesize('config.php'));
				$content = str_replace(' ;\n', ';\n', $content);// Clean space before the end-of-php-line.
				$content = str_replace('\''.$DefaultTheme .'\';', '\''.$_POST['X_DefaultTheme'].'\';', $content);
				$fhandle = fopen('config.php','w');
				if(!fwrite($fhandle,$content)) {
					prnMsg(_('Cannot write to the configuration file.'), 'error');
				} else {
					prnMsg(_('The configuration file was updated.'), 'info');
				}
				fclose($fhandle);
			} else {
				prnMsg(_('Cannot open the configuration file.'), 'error');
			}
			// END: Update the config.php file.
		}
		if ($_SESSION['PastDueDays1'] != $_POST['X_PastDueDays1'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PastDueDays1']."' WHERE confname = 'PastDueDays1'";
		}
		if ($_SESSION['PastDueDays2'] != $_POST['X_PastDueDays2'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PastDueDays2']."' WHERE confname = 'PastDueDays2'";
		}
		if ($_SESSION['DefaultCreditLimit'] != $_POST['X_DefaultCreditLimit'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultCreditLimit']."' WHERE confname = 'DefaultCreditLimit'";
		}
		if ($_SESSION['Show_Settled_LastMonth'] != $_POST['X_Show_Settled_LastMonth'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Show_Settled_LastMonth']."' WHERE confname = 'Show_Settled_LastMonth'";
		}
		if ($_SESSION['RomalpaClause'] != $_POST['X_RomalpaClause'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_RomalpaClause'] . "' WHERE confname = 'RomalpaClause'";
		}
		if ($_SESSION['QuickEntries'] != $_POST['X_QuickEntries'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_QuickEntries']."' WHERE confname = 'QuickEntries'";
		}
		if ($_SESSION['MaxSerialItemsIssued'] != $_POST['X_MaxSerialItemsIssued']) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_MaxSerialItemsIssued'] . "' WHERE confname = 'MaxSerialItemsIssued'";
		}
		if ($_SESSION['WorkingDaysWeek'] != $_POST['X_WorkingDaysWeek'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_WorkingDaysWeek']."' WHERE confname = 'WorkingDaysWeek'";
		}

		if ($_SESSION['DispatchCutOffTime'] != $_POST['X_DispatchCutOffTime'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DispatchCutOffTime']."' WHERE confname = 'DispatchCutOffTime'";
		}
		if ($_SESSION['AllowSalesOfZeroCostItems'] != $_POST['X_AllowSalesOfZeroCostItems'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_AllowSalesOfZeroCostItems']."' WHERE confname = 'AllowSalesOfZeroCostItems'";
		}
		if ($_SESSION['CreditingControlledItems_MustExist'] != $_POST['X_CreditingControlledItems_MustExist'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_CreditingControlledItems_MustExist']."' WHERE confname = 'CreditingControlledItems_MustExist'";
		}
		if ($_SESSION['DefaultPriceList'] != $_POST['X_DefaultPriceList'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultPriceList']."' WHERE confname = 'DefaultPriceList'";
		}
		if ($_SESSION['Default_Shipper'] != $_POST['X_Default_Shipper'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Default_Shipper']."' WHERE confname = 'Default_Shipper'";
		}
		if ($_SESSION['DoFreightCalc'] != $_POST['X_DoFreightCalc'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DoFreightCalc']."' WHERE confname = 'DoFreightCalc'";
		}
		if ($_SESSION['FreightChargeAppliesIfLessThan'] != $_POST['X_FreightChargeAppliesIfLessThan'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_FreightChargeAppliesIfLessThan']."' WHERE confname = 'FreightChargeAppliesIfLessThan'";
		}
		if ($_SESSION['DefaultTaxCategory'] != $_POST['X_DefaultTaxCategory'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultTaxCategory']."' WHERE confname = 'DefaultTaxCategory'";
		}
		if ($_SESSION['TaxAuthorityReferenceName'] != $_POST['X_TaxAuthorityReferenceName'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_TaxAuthorityReferenceName'] . "' WHERE confname = 'TaxAuthorityReferenceName'";
		}
		if ($_SESSION['CountryOfOperation'] != $_POST['X_CountryOfOperation'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_CountryOfOperation'] ."' WHERE confname = 'CountryOfOperation'";
		}
		if ($_SESSION['StandardCostDecimalPlaces'] != $_POST['X_StandardCostDecimalPlaces'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_StandardCostDecimalPlaces']."' WHERE confname = 'StandardCostDecimalPlaces'";
		}
		if ($_SESSION['NumberOfPeriodsOfStockUsage'] != $_POST['X_NumberOfPeriodsOfStockUsage'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_NumberOfPeriodsOfStockUsage']."' WHERE confname = 'NumberOfPeriodsOfStockUsage'";
		}
		if ($_SESSION['StockUsageShowZeroWithinPeriodRange'] != $_POST['X_StockUsageShowZeroWithinPeriodRange']) {
			$sql[] = "UPDATE config SET confvalue = '" . intval($_POST['X_StockUsageShowZeroWithinPeriodRange']) . "' WHERE confname = 'StockUsageShowZeroWithinPeriodRange'";
		}
		if ($_SESSION['Check_Qty_Charged_vs_Del_Qty'] != $_POST['X_Check_Qty_Charged_vs_Del_Qty'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Check_Qty_Charged_vs_Del_Qty']."' WHERE confname = 'Check_Qty_Charged_vs_Del_Qty'";
		}
		if ($_SESSION['Check_Price_Charged_vs_Order_Price'] != $_POST['X_Check_Price_Charged_vs_Order_Price'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Check_Price_Charged_vs_Order_Price']."' WHERE confname = 'Check_Price_Charged_vs_Order_Price'";
		}
		if ($_SESSION['OverChargeProportion'] != $_POST['X_OverChargeProportion'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_OverChargeProportion']."' WHERE confname = 'OverChargeProportion'";
		}
		if ($_SESSION['OverReceiveProportion'] != $_POST['X_OverReceiveProportion'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_OverReceiveProportion']."' WHERE confname = 'OverReceiveProportion'";
		}
		if ($_SESSION['PO_AllowSameItemMultipleTimes'] != $_POST['X_PO_AllowSameItemMultipleTimes'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PO_AllowSameItemMultipleTimes']."' WHERE confname = 'PO_AllowSameItemMultipleTimes'";
		}
		if ($_SESSION['SO_AllowSameItemMultipleTimes'] != $_POST['X_SO_AllowSameItemMultipleTimes'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_SO_AllowSameItemMultipleTimes']."' WHERE confname = 'SO_AllowSameItemMultipleTimes'";
		}
		if ($_SESSION['YearEnd'] != $_POST['X_YearEnd'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_YearEnd']."' WHERE confname = 'YearEnd'";
		}
		if ($_SESSION['PageLength'] != $_POST['X_PageLength'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PageLength']."' WHERE confname = 'PageLength'";
		}
		if ($_SESSION['DefaultDisplayRecordsMax'] != $_POST['X_DefaultDisplayRecordsMax'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultDisplayRecordsMax']."' WHERE confname = 'DefaultDisplayRecordsMax'";
		}
		if ($_SESSION['MaxImageSize'] != $_POST['X_MaxImageSize'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_MaxImageSize']."' WHERE confname = 'MaxImageSize'";
		}
		if ($_SESSION['ShowStockidOnImages'] != $_POST['X_ShowStockidOnImages'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_ShowStockidOnImages']."' WHERE confname = 'ShowStockidOnImages'";
		}
//new number must be shown
		if ($_SESSION['NumberOfMonthMustBeShown'] != $_POST['X_NumberOfMonthMustBeShown'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_NumberOfMonthMustBeShown']."' WHERE confname = 'NumberOfMonthMustBeShown'";
		}
		if ($_SESSION['part_pics_dir'] != $_POST['X_part_pics_dir'] ) {
			$sql[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_part_pics_dir']."' WHERE confname = 'part_pics_dir'";
		}
		if ($_SESSION['reports_dir'] != $_POST['X_reports_dir'] ) {
			$sql[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_reports_dir']."' WHERE confname = 'reports_dir'";
		}
		if ($_SESSION['AutoDebtorNo'] != $_POST['X_AutoDebtorNo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_AutoDebtorNo'])."' WHERE confname = 'AutoDebtorNo'";
		}
		if ($_SESSION['AutoSupplierNo'] != $_POST['X_AutoSupplierNo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_AutoSupplierNo'])."' WHERE confname = 'AutoSupplierNo'";
		}
		if ($_SESSION['HTTPS_Only'] != $_POST['X_HTTPS_Only'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_HTTPS_Only'])."' WHERE confname = 'HTTPS_Only'";
		}
		if ($_SESSION['DB_Maintenance'] != $_POST['X_DB_Maintenance'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_DB_Maintenance'])."' WHERE confname = 'DB_Maintenance'";
		}
		if ($_SESSION['DefaultBlindPackNote'] != $_POST['X_DefaultBlindPackNote'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_DefaultBlindPackNote'])."' WHERE confname = 'DefaultBlindPackNote'";
		}
		if ($_SESSION['ShowValueOnGRN'] != $_POST['X_ShowValueOnGRN'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_ShowValueOnGRN'])."' WHERE confname = 'ShowValueOnGRN'";
		}
		if ($_SESSION['PackNoteFormat'] != $_POST['X_PackNoteFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_PackNoteFormat'])."' WHERE confname = 'PackNoteFormat'";
		}
		if ($_SESSION['CheckCreditLimits'] != $_POST['X_CheckCreditLimits'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_CheckCreditLimits'])."' WHERE confname = 'CheckCreditLimits'";
		}
		if ($_SESSION['WikiApp'] !== $_POST['X_WikiApp'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_WikiApp']."' WHERE confname = 'WikiApp'";
		}
		if ($_SESSION['WikiPath'] != $_POST['X_WikiPath'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_WikiPath']."' WHERE confname = 'WikiPath'";
		}
		if ($_SESSION['ProhibitJournalsToControlAccounts'] != $_POST['X_ProhibitJournalsToControlAccounts'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_ProhibitJournalsToControlAccounts']."' WHERE confname = 'ProhibitJournalsToControlAccounts'";
		}
		if ($_SESSION['InvoiceQuantityDefault'] != $_POST['X_InvoiceQuantityDefault'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_InvoiceQuantityDefault']."' WHERE confname = 'InvoiceQuantityDefault'";
		}
		if ($_SESSION['InvoicePortraitFormat'] != $_POST['X_InvoicePortraitFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_InvoicePortraitFormat']."' WHERE confname = 'InvoicePortraitFormat'";
		}
		if ($_SESSION['AllowOrderLineItemNarrative'] != $_POST['X_AllowOrderLineItemNarrative'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_AllowOrderLineItemNarrative']."' WHERE confname = 'AllowOrderLineItemNarrative'";
		}
		if ($_SESSION['GoogleTranslatorAPIKey'] != $_POST['X_GoogleTranslatorAPIKey'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_GoogleTranslatorAPIKey']."' WHERE confname = 'GoogleTranslatorAPIKey'";
		}
		if ($_SESSION['RequirePickingNote'] != $_POST['X_RequirePickingNote'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_RequirePickingNote']."' WHERE confname = 'RequirePickingNote'";
		}
		if ($_SESSION['geocode_integration'] != $_POST['X_geocode_integration'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_geocode_integration']."' WHERE confname = 'geocode_integration'";
		}
		if ($_SESSION['Extended_SupplierInfo'] != $_POST['X_Extended_SupplierInfo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_Extended_SupplierInfo']."' WHERE confname = 'Extended_SupplierInfo'";
		}
		if ($_SESSION['Extended_CustomerInfo'] != $_POST['X_Extended_CustomerInfo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_Extended_CustomerInfo']."' WHERE confname = 'Extended_CustomerInfo'";
		}
		if ($_SESSION['ProhibitPostingsBefore'] != $_POST['X_ProhibitPostingsBefore'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_ProhibitPostingsBefore']."' WHERE confname = 'ProhibitPostingsBefore'";
		}
		if ($_SESSION['WeightedAverageCosting'] != $_POST['X_WeightedAverageCosting'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_WeightedAverageCosting']."' WHERE confname = 'WeightedAverageCosting'";
		}
		if ($_SESSION['AutoIssue'] != $_POST['X_AutoIssue']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AutoIssue'] . "' WHERE confname='AutoIssue'";
		}
		if ($_SESSION['ProhibitNegativeStock'] != $_POST['X_ProhibitNegativeStock']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ProhibitNegativeStock'] . "' WHERE confname='ProhibitNegativeStock'";
		}
		if ($_SESSION['MonthsAuditTrail'] != $_POST['X_MonthsAuditTrail']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_MonthsAuditTrail'] . "' WHERE confname='MonthsAuditTrail'";
		}
		if ($_SESSION['LogSeverity'] != $_POST['X_LogSeverity']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_LogSeverity'] . "' WHERE confname='LogSeverity'";
		}
		if ($_SESSION['LogPath'] != $_POST['X_LogPath']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_LogPath'] . "' WHERE confname='LogPath'";
		}
		if ($_SESSION['UpdateCurrencyRatesDaily'] != $_POST['X_UpdateCurrencyRatesDaily']){
			if ($_POST['X_UpdateCurrencyRatesDaily']==1) {
				$sql[] = "UPDATE config SET confvalue='".Date('Y-m-d')."' WHERE confname='UpdateCurrencyRatesDaily'";
			} else {
				$sql[] = "UPDATE config SET confvalue='0' WHERE confname='UpdateCurrencyRatesDaily'";
			}
		}
		if ($_SESSION['ExchangeRateFeed'] != $_POST['X_ExchangeRateFeed']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ExchangeRateFeed'] . "' WHERE confname='ExchangeRateFeed'";
		}
		if ($_SESSION['FactoryManagerEmail'] != $_POST['X_FactoryManagerEmail']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_FactoryManagerEmail'] . "' WHERE confname='FactoryManagerEmail'";
		}
		if ($_SESSION['PurchasingManagerEmail'] != $_POST['X_PurchasingManagerEmail']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_PurchasingManagerEmail'] . "' WHERE confname='PurchasingManagerEmail'";
		}
		if ($_SESSION['InventoryManagerEmail'] != $_POST['X_InventoryManagerEmail']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_InventoryManagerEmail'] . "' WHERE confname='InventoryManagerEmail'";
		}
		if ($_SESSION['AutoCreateWOs'] != $_POST['X_AutoCreateWOs']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AutoCreateWOs'] . "' WHERE confname='AutoCreateWOs'";
		}
		if ($_SESSION['DefaultFactoryLocation'] != $_POST['X_DefaultFactoryLocation']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultFactoryLocation'] . "' WHERE confname='DefaultFactoryLocation'";
		}
		if ($_SESSION['DefineControlledOnWOEntry'] != $_POST['X_DefineControlledOnWOEntry']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_DefineControlledOnWOEntry'] . "' WHERE confname='DefineControlledOnWOEntry'";
		}
		if ($_SESSION['FrequentlyOrderedItems'] != $_POST['X_FrequentlyOrderedItems']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_FrequentlyOrderedItems'] . "' WHERE confname='FrequentlyOrderedItems'";
		}
		if ($_SESSION['AutoAuthorisePO'] != $_POST['X_AutoAuthorisePO']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AutoAuthorisePO'] . "' WHERE confname='AutoAuthorisePO'";
		}
		if (isset($_POST['X_ItemDescriptionLanguages'])) {
			$ItemDescriptionLanguages = '';
			foreach ($_POST['X_ItemDescriptionLanguages'] as $ItemLanguage){
				$ItemDescriptionLanguages .= $ItemLanguage .',';
			}

			if ($_SESSION['ItemDescriptionLanguages'] != $ItemDescriptionLanguages){
				$sql[] = "UPDATE config SET confvalue='" . $ItemDescriptionLanguages . "' WHERE confname='ItemDescriptionLanguages'";
			}
		}
		if ($_SESSION['SmtpSetting'] != $_POST['X_SmtpSetting']){
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_SmtpSetting'] . "' WHERE confname='SmtpSetting'";

		}
		if ($_SESSION['QualityLogSamples'] != $_POST['X_QualityLogSamples']){
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_QualityLogSamples'] . "' WHERE confname='QualityLogSamples'";

		}
		if ($_SESSION['QualityProdSpecText'] != $_POST['X_QualityProdSpecText']){
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_QualityProdSpecText'] . "' WHERE confname='QualityProdSpecText'";

		}
		if ($_SESSION['QualityCOAText'] != $_POST['X_QualityCOAText']){
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_QualityCOAText'] . "' WHERE confname='QualityCOAText'";

		}
		if ($_SESSION['ShortcutMenu'] != $_POST['X_ShortcutMenu']){
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_ShortcutMenu'] . "' WHERE confname='ShortcutMenu'";

		}
		if ($_SESSION['LastDayOfWeek'] != $_POST['X_LastDayOfWeek']){
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_LastDayOfWeek'] . "' WHERE confname='LastDayOfWeek'";

		}

		$ErrMsg =  _('The system configuration could not be updated because');
		if (sizeof($sql) > 1 ) {
			DB_Txn_Begin();
			foreach ($sql as $line) {
				$result = DB_query($line,$ErrMsg);
			}
			DB_Txn_Commit();
		} elseif(sizeof($sql)==1) {
			$result = DB_query($sql,$ErrMsg);
		}

		prnMsg( _('System configuration updated'),'success');

		$ForceConfigReload = True; // Required to force a load even if stored in the session vars
		include('includes/GetConfig.php');
		$ForceConfigReload = False;
	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', _('Configuration Options'), '</legend>';

echo '<fieldset>
		<legend>', _('General Settings'), '</legend>';
// DefaultDateFormat
echo '<field>
		<label for="X_DefaultDateFormat">' . _('Default Date Format') . ':</label>
		<select name="X_DefaultDateFormat">
			<option '.(($_SESSION['DefaultDateFormat']=='d/m/Y')?'selected="selected" ':'').'value="d/m/Y">' . _('d/m/Y') . '</option>
			<option '.(($_SESSION['DefaultDateFormat']=='d.m.Y')?'selected="selected" ':'').'value="d.m.Y">' . _('d.m.Y') . '</option>
			<option '.(($_SESSION['DefaultDateFormat']=='m/d/Y')?'selected="selected" ':'').'value="m/d/Y">' . _('m/d/Y') . '</option>
			<option '.(($_SESSION['DefaultDateFormat']=='Y/m/d')?'selected="selected" ':'').'value="Y/m/d">' . _('Y/m/d') . '</option>
			<option '.(($_SESSION['DefaultDateFormat']=='Y-m-d')?'selected="selected" ':'').'value="Y-m-d">' . _('Y-m-d') . '</option>
		</select>
		<fieldhelp>' . _('The default date format for entry of dates and display.') . '</fieldhelp>
	</field>';

// DefaultTheme:
if (is_writable('config.php')) {
	echo '<field>
			<label for="X_DefaultTheme">' . _('Default Theme') . ':</label>
			<select name="X_DefaultTheme">';
	$ThemeDirectories = scandir('css');// List directories inside ~/css. Each diretory is a theme.
	foreach($ThemeDirectories as $ThemeName) {
		if(is_dir('css/'.$ThemeName) AND $ThemeName!='.' AND $ThemeName!='..' AND $ThemeName!='.svn') {
			echo '<option';
			if ($DefaultTheme == $ThemeName) {
				echo ' selected="selected"';
			}
			echo ' value="'. $ThemeName.'">' . $ThemeName . '</option>';
		}
	}
	echo '</select>
		<fieldhelp>' . _("The default theme to use for the login screen and the setup of new users. The users' theme selection will override it.") . '</fieldhelp>
	</field>';
} else {
	echo '<input type="hidden" name="X_DefaultTheme" value="' . $DefaultTheme . '" />';
}
echo '</fieldset><br />';
// ---------- New section:
echo '<fieldset>
		<legend>' . _('Accounts Receivable/Payable Settings') . '</legend>';

// PastDueDays1
echo '<field>
		<label for="X_PastDueDays1">' . _('First Overdue Deadline in (days)') . ':</label>
		<input type="text" class="integer" required="required"  pattern="(?!^0\d+$)[\d]+" title="'._('The input must be integer').'" name="X_PastDueDays1" value="' . $_SESSION['PastDueDays1'] . '" size="3" maxlength="3" />
		<fieldhelp>' . _('Customer and supplier balances are displayed as overdue by this many days. This parameter is used on customer and supplier enquiry screens and aged listings') . '</fieldhelp>
	</field>';

// PastDueDays2
echo '<field>
		<label for="X_PastDueDays2">' . _('Second Overdue Deadline in (days)') . ':</label>
		<input type="text" class="integer" required="required"  pattern="(?!^0\d+$)[\d]+" title="'._('The input must be integer').'" name="X_PastDueDays2" value="' . $_SESSION['PastDueDays2'] . '" size="3" maxlength="3" />
		<fieldhelp>' . _('As above but the next level of overdue') . '</fieldhelp>
	</field>';

// DefaultCreditLimit
echo '<field>
		<label for="X_DefaultCreditLimit">' . _('Default Credit Limit') . ':</label>
		<input type="text" class="number" required="required" title="'._('The input must be numeric').'" name="X_DefaultCreditLimit" value="' . $_SESSION['DefaultCreditLimit'] . '" size="12" maxlength="12" />
		<fieldhelp>' . _('The default used in new customer set up') . '</fieldhelp>
	</field>';

// Check Credit Limits
echo '<field>
		<label for="X_CheckCreditLimits">' . _('Check Credit Limits') . ':</label>
		<select name="X_CheckCreditLimits">
			<option value="0"' . ($_SESSION['CheckCreditLimits'] == 0 ? ' selected="selected"' : '') . '>' . _('Do not check') . '</option>
			<option value="1"' . ($_SESSION['CheckCreditLimits'] == 1 ? ' selected="selected"' : '') . '>' . _('Warn on breach') . '</option>
			<option value="2"' . ($_SESSION['CheckCreditLimits'] == 2 ? ' selected="selected"' : '') . '>' . _('Prohibit Sales') . '</option>
		</select>
		<fieldhelp>' . _('Credit limits can be checked at order entry to warn only or to stop the order from being entered where it would take a customer account balance over their limit') . '</fieldhelp>
	</field>';

// Show_Settled_LastMonth
echo '<field>
		<label for="X_Show_Settled_LastMonth">' . _('Show Settled Last Month') . ':</label>
		<select name="X_Show_Settled_LastMonth">
			<option value="1"' . ($_SESSION['Show_Settled_LastMonth'] ? ' selected="selected"' : '') .'>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['Show_Settled_LastMonth'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('This setting refers to the format of customer statements. If the invoices and credit notes that have been paid and settled during the course of the current month should be shown then select Yes. Selecting No will only show currently outstanding invoices, credits and payments that have not been allocated') . '</fieldhelp>
	</field>';

//RomalpaClause
echo '<field>
		<label for="X_RomalpaClause">' . _('Romalpa Clause') . ':</label>
		<textarea name="X_RomalpaClause" rows="3" cols="40">' . $_SESSION['RomalpaClause'] . '</textarea>
		<fieldhelp>' . _('This text appears on invoices and credit notes in small print. Normally a reservation of title clause that gives the company rights to collect goods which have not been paid for - to give some protection for bad debts.') . '</fieldhelp>
	</field>';

// QuickEntries
echo '<field>
		<label for="X_QuickEntries">' . _('Quick Entries') . ':</label>
		<input type="text" class="integer" required="required" pattern="[1-9][\d]{0,1}" name="X_QuickEntries" value="' . $_SESSION['QuickEntries'] . '" size="3" maxlength="2" />
		<fieldhelp>' . _('This parameter defines the layout of the sales order entry screen. The number of fields available for quick entries. Any number from 1 to 99 can be entered.') . '</fieldhelp>
	</field>';

// MaxSerialItemsIssued
echo '<field style="height:30px">
		<label for="X_MaxSerialItemsIssued">' . _('Maximum number of serial numbered items that can be issued') . ':</label>
		<input type="text" class="integer" name="X_MaxSerialItemsIssued" value="' . $_SESSION['MaxSerialItemsIssued'] . '" size="3" required="required" maxlength="10" />
		<fieldhelp>' . _('This parameter defines the Maximum number of serial numbered items that can be issued. It should be an integer greater than zero.') . '</fieldhelp>
	</field>';

// Frequently Ordered Items
echo '<field>
		<label for="X_FrequentlyOrderedItems">' . _('Frequently Ordered Items') . ':</label>
		<input type="text" class="integer" pattern="(?!^0[1-9]+$)[\d]{1,2}" name="X_FrequentlyOrderedItems" value="' . $_SESSION['FrequentlyOrderedItems'] . '" size="3" maxlength="2" />
		<fieldhelp>' . _('To show the most frequently ordered items enter the number of frequently ordered items you wish to display from 1 to 99. If you do not wish to display the frequently ordered item list enter 0.') . '</fieldhelp>
	</field>';

// SO_AllowSameItemMultipleTimes
echo '<field>
		<label for="X_SO_AllowSameItemMultipleTimes">' . _('Sales Order Allows Same Item Multiple Times') . ':</label>
		<select name="X_SO_AllowSameItemMultipleTimes">
			<option value="1"' . ($_SESSION['SO_AllowSameItemMultipleTimes'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['SO_AllowSameItemMultipleTimes'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
	</field>';

//'AllowOrderLineItemNarrative'
echo '<field>
		<label for="X_AllowOrderLineItemNarrative">' . _('Order Entry allows Line Item Narrative') . ':</label>
		<select name="X_AllowOrderLineItemNarrative">
			<option value="1"' . ($_SESSION['AllowOrderLineItemNarrative'] == '1' ? ' selected="selected"' : '') . '>' . _('Allow Narrative Entry') . '</option>
			<option value="0"' . ($_SESSION['AllowOrderLineItemNarrative'] == '0' ? ' selected="selected"' : '') . '>' . _('No Narrative Line') . '</option>
		</select>
		<fieldhelp>' . _('Select whether or not to allow entry of narrative on order line items. This narrative will appear on invoices and packing slips. Useful mainly for service businesses.') . '</fieldhelp>
	</field>';
//ItemDescriptionLanguages
if (!isset($_POST['X_ItemDescriptionLanguages'])){
	$_POST['X_ItemDescriptionLanguages'] = explode(',',$_SESSION['ItemDescriptionLanguages']);
}
echo '<field>
		<label for="X_ItemDescriptionLanguages">' . _('Languages to Maintain Translations for Item Descriptions') . ':</label>
		<select name="X_ItemDescriptionLanguages[]" size="5" multiple="multiple" >';

		echo '<option value=""' . (count($LanguagesArray)==0 ? '':'selected="selected"') . '>' . _('None')  . '</option>';
foreach ($LanguagesArray as $LanguageEntry => $LanguageName){
	if (isset($_POST['X_ItemDescriptionLanguages']) AND in_array($LanguageEntry,$_POST['X_ItemDescriptionLanguages'])){
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	} elseif ($LanguageEntry != $DefaultLanguage) {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName']  . '</option>';
	}
}
echo '</select>
	<fieldhelp>' . _('Select the languages in which translations of the item description will be maintained. The default language is excluded.') . '</fieldhelp>
</field>';

// Google Translator API Key
echo '<field>
		<label for="X_GoogleTranslatorAPIKey">' . _('Google Translator API Key') . ':</label>
		<input type="text" name="X_GoogleTranslatorAPIKey" size="25" maxlength="50" value="' . $_SESSION['GoogleTranslatorAPIKey'] . '" />
		<fieldhelp>' . _('Google Translator API Key to allow automatic translations. More info at https://cloud.google.com/translate/')  . '</fieldhelp>
	</field>';

//'RequirePickingNote'
echo '<field style="height:30px">
		<label for="X_RequirePickingNote">' . _('A picking note must be produced before an order can be delivered') . ':</label>
		<select name="X_RequirePickingNote">
			<option value="1"' . ($_SESSION['RequirePickingNote'] == '1' ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . ($_SESSION['RequirePickingNote'] == '0' ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('Select whether or not a picking note must be produced before an order can be delivered to a customer.') . '</fieldhelp>
	</field>';

//UpdateCurrencyRatesDaily
echo '<field>
		<label for="X_UpdateCurrencyRatesDaily">' . _('Auto Update Exchange Rates Daily') . ':</label>
		<select name="X_UpdateCurrencyRatesDaily">
			<option value="1"' . ($_SESSION['UpdateCurrencyRatesDaily'] != '1'? ' selected="selected" ':'').'>' . _('Automatic') . '</option>
			<option value="0"' . ($_SESSION['UpdateCurrencyRatesDaily'] == '0'? ' selected="selected" ':'').'>' . _('Manually') . '</option>
		</select>
		<fieldhelp>' . _('Automatic updates to exchange rates will retrieve the latest daily rates from either the European Central Bank or Google once per day - when the first user logs in for the day. Manual will never update the rates automatically - exchange rates will need to be maintained manually') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ExchangeRateFeed">' . _('Source Exchange Rates From') . ':</label>
		<select name="X_ExchangeRateFeed">
			<option value="ECB"' . ($_SESSION['ExchangeRateFeed'] != 'ECB' ? ' selected="selected"' : '') . '>' . _('European Central Bank') . '</option>
			<option value="Google"' . ($_SESSION['ExchangeRateFeed'] == 'Google' ? ' selected="selected"' : '') . '>' . _('Google') . '</option>
		</select>
		<fieldhelp>' . _('Specify the source to use for exchange rates') . '</fieldhelp>
	</field>';

//Default Packing Note Format
echo '<field>
		<label for="X_PackNoteFormat">' . _('Format of Packing Slips') . ':</label>
		<select name="X_PackNoteFormat">
			<option value="1"' . ($_SESSION['PackNoteFormat'] == '1' ? ' selected="selected"' : '') . '>' . _('Laser Printed') . '</option>
			<option value="2"' . ($_SESSION['PackNoteFormat'] == '2' ? ' selected="selected"' : '') . '>' . _('Special Stationery') . '</option>
		</select>
		<fieldhelp>' . _('Choose the format that packing notes should be printed by default') . '</fieldhelp>
	</field>';

//Default Invoice Format
echo '<field>
		<label for="X_InvoicePortraitFormat">' . _('Invoice Orientation') . ':</label>
		<select name="X_InvoicePortraitFormat">
			<option value="0"' . ($_SESSION['InvoicePortraitFormat'] == '0' ? ' selected="selected"' : '') . '>' . _('Landscape') . '</option>
			<option value="1"' . ($_SESSION['InvoicePortraitFormat'] == '1' ? ' selected="selected"' : '') . '>' . _('Portrait') . '</option>
		</select>
		<fieldhelp>' . _('Select the invoice layout') . '</fieldhelp>
	</field>';

//Default Invoice Quantity
echo '<field>
		<label for="X_InvoiceQuantityDefault">' . _('Invoice Quantity Default') . ':</label>
		<select name="X_InvoiceQuantityDefault">
			<option value="0"' . ($_SESSION['InvoiceQuantityDefault'] == '0' ? ' selected="selected"' : '') . '>0</option>
			<option value="1"' . ($_SESSION['InvoiceQuantityDefault'] == '1' ? ' selected="selected"' : '') . '>' . _('Outstanding') . '</option>
		</select>
		<fieldhelp>' . _('This setting controls the default behaviour of invoicing. Setting to 0 defaults the invoice quantity to zero to force entry. Set to outstanding to default the invoice quantity to the balance outstanding, after previous deliveries, on the sales order') . '</fieldhelp>
	</field>';

//Blind packing note
echo '<field>
		<label for="X_DefaultBlindPackNote">' . _('Show company details on packing slips') . ':</label>
		<select name="X_DefaultBlindPackNote">
			<option value="1"' . ($_SESSION['DefaultBlindPackNote'] == '1' ? ' selected="selected"' : '') . '>' . _('Show Company Details') . '</option>
			<option value="2"' . ($_SESSION['DefaultBlindPackNote'] == '2' ? ' selected="selected"' : '') . '>' . _('Hide Company Details') . '</option>
		</select>
		<fieldhelp>' . _('Customer branches can be set by default not to print packing slips with the company logo and address. This is useful for companies that ship to customers customers and to show the source of the shipment would be inappropriate. There is an option on the setup of customer branches to ship blind, this setting is the default applied to all new customer branches') . '</fieldhelp>
	</field>';

// Working days on a week
echo '<field>
		<label for="X_WorkingDaysWeek">' . _('Working Days on a Week') . ':</label>
		<select name="X_WorkingDaysWeek">
			<option '.($_SESSION['WorkingDaysWeek']=='7'?'selected="selected" ':'').'value="7">7 '._('working days') . '</option>
			<option '.($_SESSION['WorkingDaysWeek']=='6'?'selected="selected" ':'').'value="6">6 '._('working days') . '</option>
			<option '.($_SESSION['WorkingDaysWeek']=='5'?'selected="selected" ':'').'value="5">5 '._('working days') . '</option>
		</select>
		<fieldhelp>' . _('Number of working days on a week') . '</fieldhelp>
	</field>';

// DispatchCutOffTime
echo '<field>
		<label for="X_DispatchCutOffTime">' . _('Dispatch Cut-Off Time') . ':</label>
		<select name="X_DispatchCutOffTime">';
for ($i=0; $i < 24; $i++ )
	echo '<option value="' . $i . '"' . ($_SESSION['DispatchCutOffTime'] == $i ? ' selected="selected"' : '') . '>' . $i . '</option>';
echo '</select>
	<fieldhelp>' . _('Orders entered after this time will default to be dispatched the following day, this can be over-ridden at the time of sales order entry') . '</fieldhelp>
</field>';

// AllowSalesOfZeroCostItems
echo '<field>
		<label for="X_AllowSalesOfZeroCostItems">' . _('Allow Sales Of Zero Cost Items') . ':</label>
		<select name="X_AllowSalesOfZeroCostItems">
			<option value="1"' . ($_SESSION['AllowSalesOfZeroCostItems'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['AllowSalesOfZeroCostItems'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('If an item selected at order entry does not have a cost set up then if this parameter is set to No then the order line will not be able to be entered') . '</fieldhelp>
	</field>';

// CreditingControlledItems_MustExist
echo '<field>
		<label for="X_CreditingControlledItems_MustExist">' . _('Controlled Items Must Exist For Crediting') . ':</label>
		<select name="X_CreditingControlledItems_MustExist">
			<option value="1"' . ($_SESSION['CreditingControlledItems_MustExist'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['CreditingControlledItems_MustExist'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('This parameter relates to the behaviour of the controlled items code. If a serial numbered item has not previously existed then a credit note for it will not be allowed if this is set to Yes') . '</fieldhelp>
	</field>';

// DefaultPriceList
$sql = "SELECT typeabbrev, sales_type FROM salestypes ORDER BY sales_type";
$ErrMsg = _('Could not load price lists');
$result = DB_query($sql,$ErrMsg);
echo '<field>
		<label for="X_DefaultPriceList">' . _('Default Price List') . ':</label>
		<select name="X_DefaultPriceList">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected="selected" value="">' . _('Unavailable');
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['DefaultPriceList'] == $row['typeabbrev']?'selected="selected" ':'').'value="'.$row['typeabbrev'].'">' . $row['sales_type'] . '</option>';
	}
}
echo '</select>
	<fieldhelp>' . _('This price list is used as a last resort where there is no price set up for an item in the price list that the customer is set up for') . '</fieldhelp>
</field>';

// Default_Shipper
$sql = "SELECT shipper_id, shippername FROM shippers ORDER BY shippername";
$ErrMsg = _('Could not load shippers');
$result = DB_query($sql,$ErrMsg);
echo '<field>
		<label for="X_Default_Shipper">' . _('Default Shipper') . ':</label>
		<select name="X_Default_Shipper">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected="selected" value="">' . _('Unavailable') . '</option>';
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['Default_Shipper'] == $row['shipper_id']?'selected="selected" ':'').'value="'.$row['shipper_id'].'">' . $row['shippername'] . '</option>';
	}
}
echo '</select>
	<fieldhelp>' . _('This shipper is used where the best shipper for a customer branch has not been defined previously') . '</fieldhelp>
</field>';

// DoFreightCalc
echo '<field>
		<label for="X_DoFreightCalc">' . _('Do Freight Calculation') . ':</label>
		<select name="X_DoFreightCalc">
			<option value="1"' . ($_SESSION['DoFreightCalc'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['DoFreightCalc'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('If this is set to Yes then the system will attempt to calculate the freight cost of a dispatch based on the weight and cubic and the data defined for each shipper and their rates for shipping to various locations. The results of this calculation will only be meaningful if the data is entered for the item weight and volume in the stock item setup for all items and the freight costs for each shipper properly maintained.') . '</fieldhelp>
	</field>';

//FreightChargeAppliesIfLessThan
echo '<field>
		<label for="X_FreightChargeAppliesIfLessThan">' . _('Apply freight charges if an order is less than') . ':</label>
		<input type="text" class="number" required="required" title="'._('The input must be numeric').'" name="X_FreightChargeAppliesIfLessThan" size="12" maxlength="12" value="' . $_SESSION['FreightChargeAppliesIfLessThan'] . '" />
		<fieldhelp>' . _('This parameter is only effective if Do Freight Calculation is set to Yes. If it is set to 0 then freight is always charged. The total order value is compared to this value in deciding whether or not to charge freight')  . '</fieldhelp>
	</field>';


// AutoDebtorNo
echo '<field>
		<label for="X_AutoDebtorNo">' . _('Create Debtor Codes Automatically') . ':</label>
		<select name="X_AutoDebtorNo">';

if ($_SESSION['AutoDebtorNo']==0) {
	echo '<option selected="selected" value="0">' . _('Manual Entry') . '</option>';
	echo '<option value="1">' . _('Automatic') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Automatic') . '</option>';
	echo '<option value="0">' . _('Manual Entry') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('Set to Automatic - customer codes are automatically created - as a sequential number')  . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_AutoSupplierNo">' . _('Create Supplier Codes Automatically') . ':</label>
		<select name="X_AutoSupplierNo">';

if ($_SESSION['AutoSupplierNo']==0) {
	echo '<option selected="selected" value="0">' . _('Manual Entry') . '</option>';
	echo '<option value="1">' . _('Automatic') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Automatic') . '</option>';
	echo '<option value="0">' . _('Manual Entry') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('Set to Automatic - Supplier codes are automatically created - as a sequential number')  . '</fieldhelp>
</field>';

//==HJ== drop down list for tax category
$sql = "SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname";
$ErrMsg = _('Could not load tax categories table');
$result = DB_query($sql,$ErrMsg);
echo '<field>
		<label for="X_DefaultTaxCategory">' . _('Default Tax Category') . ':</label>
		<select name="X_DefaultTaxCategory">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected="selected" value="">' . _('Unavailable') . '</option>';
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['DefaultTaxCategory'] == $row['taxcatid']?'selected="selected" ':'').'value="'.$row['taxcatid'].'">' . $row['taxcatname'] . '</option>';
	}
}
echo '</select>
	<fieldhelp>' . _('This is the tax category used for entry of supplier invoices and the category at which freight attracts tax')  . '</fieldhelp>
</field>';

//TaxAuthorityReferenceName
echo '<field>
		<label for="X_TaxAuthorityReferenceName">' . _('Tax Authority Reference Name') . ':</label>
		<input type="text" name="X_TaxAuthorityReferenceName" size="16" maxlength="25" value="' . $_SESSION['TaxAuthorityReferenceName'] . '" />
		<fieldhelp>' . _('This parameter is what is displayed on tax invoices and credits for the tax authority of the company eg. in Australian this would by A.B.N.: - in NZ it would be GST No: in the UK it would be VAT Regn. No')  . '</fieldhelp>
	</field>';

// CountryOfOperation

echo '<field>
		<label for="X_CountryOfOperation">' . _('Country Of Operation') . ':</label>
		<select name="X_CountryOfOperation">';
echo '<option selected="selected" value="">' . _('Unavailable') . '</option>';
foreach ($CountriesArray as $CountryEntry => $CountryName){
	echo '<option ' . ($_SESSION['CountryOfOperation'] == $CountryEntry?'selected="selected" ':'') . ' value="' . $CountryEntry . '">' . $CountryName  . '</option>';
}

echo '</select>
	<fieldhelp>' . _('This parameter is only effective if Do Freight Calculation is set to Yes.')  . '</fieldhelp>
</field>';

// StandardCostDecimalPlaces
echo '<field>
		<label for="X_StandardCostDecimalPlaces">' . _('Standard Cost Decimal Places') . ':</label>
		<select name="X_StandardCostDecimalPlaces">';
for ($i=0; $i <= 4; $i++ )
	echo '<option value="' . $i . '"' . ($_SESSION['StandardCostDecimalPlaces'] == $i ? ' selected="selected"' : '') . '>' . $i . '</option>';
echo '</select>
	<fieldhelp>' . _('Decimal Places to be used in Standard Cost')  . '</fieldhelp>
</field>';

// NumberOfPeriodsOfStockUsage
echo '<field>
		<label for="X_NumberOfPeriodsOfStockUsage">' . _('Number Of Periods Of StockUsage') . ':</label>
		<select name="X_NumberOfPeriodsOfStockUsage">';
for ($i=1; $i <= 12; $i++ )
	echo '<option value="' . $i . '"' . ($_SESSION['NumberOfPeriodsOfStockUsage'] == $i ? ' selected="selected"' : '') . '>' . $i . '</option>';
echo '</select>
	<fieldhelp>' . _('In stock usage inquiries this determines how many periods of stock usage to show. An average is calculated over this many periods')  . '</fieldhelp>
</field>';

// StockUsageShowZeroWithinPeriodRange
echo '<field style="height:30px">
		<label for="X_StockUsageShowZeroWithinPeriodRange">' . _('Show Zero Counts Within Stock Usage Graph Period Range') . ':</label>
		<select name="X_StockUsageShowZeroWithinPeriodRange">
			<option value="1"' . ($_SESSION['StockUsageShowZeroWithinPeriodRange'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['StockUsageShowZeroWithinPeriodRange'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('Show periods having zero counts within Stock Usage Graph. Choosing yes may show a wider period range than expected.') . '</fieldhelp>
	</field>';

//Show values on GRN
echo '<field>
		<label for="X_ShowValueOnGRN">' . _('Show order values on GRN') . ':</label>
		<select name="X_ShowValueOnGRN">
			<option value="1"' . ($_SESSION['ShowValueOnGRN'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['ShowValueOnGRN'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('Should the value of the purchased stock be shown on the GRN screen') . '</fieldhelp>
	</field>';

// Check_Qty_Charged_vs_Del_Qty
echo '<field>
		<label for="X_Check_Qty_Charged_vs_Del_Qty">' . _('Check Quantity Charged vs Deliver Qty') . ':</label>
		<select name="X_Check_Qty_Charged_vs_Del_Qty">
			<option value="1"' . ($_SESSION['Check_Qty_Charged_vs_Del_Qty'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['Check_Qty_Charged_vs_Del_Qty'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('In entry of AP invoices this determines whether or not to check the quantities received into stock tie up with the quantities invoiced')  . '</fieldhelp>
	</field>';

// Check_Price_Charged_vs_Order_Price
echo '<field>
		<label for="X_Check_Price_Charged_vs_Order_Price">' . _('Check Price Charged vs Order Price') . ':</label>
		<select name="X_Check_Price_Charged_vs_Order_Price">
			<option value="1"' . ($_SESSION['Check_Price_Charged_vs_Order_Price'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['Check_Price_Charged_vs_Order_Price'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('In entry of AP invoices this parameter determines whether or not to check invoice prices tie up to ordered prices')  . '</fieldhelp>
	</field>';

// OverChargeProportion
echo '<field>
		<label for="X_OverChargeProportion">' . _('Allowed Over Charge Proportion') . ':</label>
		<input type="text" class="integer" pattern="(?!^0\d+$)[\d]{1,2}|(100)" required="required" title="'._('The input must between 0 and 100').'" name="X_OverChargeProportion" size="4" maxlength="3" value="' . $_SESSION['OverChargeProportion'] . '" placeholder="'._('integer between 0 and 100').'" />
		<fieldhelp>' . _('If check price charges vs Order price is set to yes then this proportion determines the percentage by which invoices can be overcharged with respect to price')  . '</fieldhelp>
	</field>';

// OverReceiveProportion
echo '<field>
		<label for="X_OverReceiveProportion">' . _('Allowed Over Receive Proportion') . ':</label>
		<input type="text" class="integer" pattern="(?!^0\d+$)[\d]{1,2}|(100)" required="required" title="'._('The input must between 0 and 100').'" name="X_OverReceiveProportion" size="4" maxlength="3" value="' . $_SESSION['OverReceiveProportion'] . '" />
		<fieldhelp>' . _('If check quantity charged vs delivery quantity is set to yes then this proportion determines the percentage by which invoices can be overcharged with respect to delivery')  . '</fieldhelp>
	</field>';

// PO_AllowSameItemMultipleTimes
echo '<field>
		<label for="X_PO_AllowSameItemMultipleTimes">' . _('Purchase Order Allows Same Item Multiple Times') . ':</label>
		<select name="X_PO_AllowSameItemMultipleTimes">
			<option value="1"' . ($_SESSION['PO_AllowSameItemMultipleTimes'] ? ' selected="selected"' : '') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['PO_AllowSameItemMultipleTimes'] ? ' selected="selected"' : '') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('If a purchase order can have the same item on the order several times this parameter should be set to yes') . '</fieldhelp>
	</field>';

// AutoAuthorisePO
echo '<field>
		<label for="X_AutoAuthorisePO">' . _('Automatically authorise purchase orders if user has authority') . ':</label>
		<select name="X_AutoAuthorisePO">
			<option value="1"' . ($_SESSION['AutoAuthorisePO'] ? ' selected="selected" ':'') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['AutoAuthorisePO'] ? ' selected="selected" ':'') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('If the user changing an existing purchase order or adding a new puchase order is set up to authorise purchase orders and the order is within their limit, then the purchase order status is automatically set to authorised') . '</fieldhelp>
	</field>';

echo '</fieldset><br />';

echo '<fieldset>
		<legend>' . _('General Settings') . '</legend>';

// YearEnd
$MonthNames = array( 1=>_('January'),
			2=>_('February'),
			3=>_('March'),
			4=>_('April'),
			5=>_('May'),
			6=>_('June'),
			7=>_('July'),
			8=>_('August'),
			9=>_('September'),
			10=>_('October'),
			11=>_('November'),
			12=>_('December') );
echo '<field>
		<label for="X_YearEnd">' . _('Financial Year Ends On') . ':</label>
		<select name="X_YearEnd">';
for ($i=1; $i <= sizeof($MonthNames); $i++ )
	echo '<option value="' . $i . '"' . ($_SESSION['YearEnd'] == $i ? ' selected="selected"' : '') . '>' . $MonthNames[$i] . '</option>';
echo '</select>
	<fieldhelp>' . _('Defining the month in which the financial year ends enables the system to provide useful defaults for general ledger reports')  . '</fieldhelp>
</field>';

//PageLength
echo '<field>
		<label for="X_PageLength">' . _('Report Page Length') . ':</label>
		<input type="text" class="integer" pattern="(?!^0\d*$)[\d]{1,3}" title="'._('The input should be between 1 and 999').'" placeholder="'._('1 to 999').'" name="X_PageLength" size="4" maxlength="6" value="' . $_SESSION['PageLength'] . '" /></td>
	</field>';

//DefaultDisplayRecordsMax
echo '<field>
		<label for="X_DefaultDisplayRecordsMax">' . _('Default Maximum Number of Records to Show') . ':</label>
		<input type="text" class="integer" pattern="(?!^0\d*$)[\d]{1,3}" required="required" title="'._('The records should be between 1 and 999').'" name="X_DefaultDisplayRecordsMax" size="4" maxlength="3" value="' . $_SESSION['DefaultDisplayRecordsMax'] . '" />
		<fieldhelp>' . _('When pages have code to limit the number of returned records - such as select customer, select supplier and select item, then this will be the default number of records to show for a user who has not changed this for themselves in user settings.') . '</fieldhelp>
	</field>';

// ShowStockidOnImage
echo '<field>
		<label for="X_ShowStockidOnImages">' . _('Show Stockid on images') . ':</label>
		<select name="X_ShowStockidOnImages">
			<option value="1"' . ($_SESSION['ShowStockidOnImages'] ? ' selected="selected" ':'') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['ShowStockidOnImages'] ? ' selected="selected" ':'') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('Show the code inside the thumbnail image of the items') . '</fieldhelp>
	</field>';

//MaxImageSize
echo '<field>
		<label for="X_MaxImageSize">' . _('Maximum Size in KB of uploaded images') . ':</label>
		<input type="text" class="integer" pattern="(?!^0\d*$)[\d]{1,3}" required="required" title="'._('The input should be between 1 and 999').'" placeholder="'._('1 to 999').'" name="X_MaxImageSize" size="4" maxlength="3" value="' . $_SESSION['MaxImageSize'] . '" />
		<fieldhelp>' . _('Picture files of items can be uploaded to the server. The system will check that files uploaded are less than this size (in KB) before they will be allowed to be uploaded. Large pictures will make the system slow and will be difficult to view in the stock maintenance screen.')  . '</fieldhelp>
	</field>';

//NumberOfMonthMustBeShown
echo '<field>
		<label for="X_NumberOfMonthMustBeShown">' . _('Number Of Month Must Be Shown') . ':</label>
			<input type="text" class="integer" pattern="(?!^0\d*$)[\d]+" required="required" title="'._('input must be positive integer').'" placeholder="'._('positive integer').'" name="X_NumberOfMonthMustBeShown" size="4" maxlength="3" value="' . $_SESSION['NumberOfMonthMustBeShown'] . '" />
			<fieldhelp>' . _('Number of month must be shown on report can be changed with this parameters ex: in CustomerInquiry.php ')  . '</fieldhelp>
	</field>';

//$part_pics_dir
echo '<field>
		<label for="X_part_pics_dir">' . _('The directory where images are stored') . ':</label>
		<select name="X_part_pics_dir">';
$CompanyDirectory = 'companies/' . $_SESSION['DatabaseName'] . '/';
$DirHandle = dir($CompanyDirectory);
while ($DirEntry = $DirHandle->read() ){
	if (is_dir($CompanyDirectory . $DirEntry)
		AND $DirEntry != '..'
		AND $DirEntry!='.'
		AND $DirEntry!='.svn'
		AND $DirEntry != 'CVS'
		AND $DirEntry != 'reports'
		AND $DirEntry != 'locale'
		AND $DirEntry != 'fonts'   ){
		if ($_SESSION['part_pics_dir'] == $CompanyDirectory . $DirEntry){
			echo '<option selected="selected" value="' . $DirEntry . '">' . $DirEntry . '</option>';
		} else {
			echo '<option value="' . $DirEntry . '">' . $DirEntry  . '</option>';
		}
	}
}
echo '</select>
	<fieldhelp>' . _('The directory under which all image files should be stored. Image files take the format of ItemCode.jpg - they must all be .jpg files and the part code will be the name of the image file. This is named automatically on upload. The system will check to ensure that the image is a .jpg file') . '</fieldhelp>
</field>';

//$reports_dir
echo '<field>
		<label for="X_reports_dir">' . _('The directory where reports are stored') . ':</label>
		<select name="X_reports_dir">';
$DirHandle = dir($CompanyDirectory);
while (false != ($DirEntry = $DirHandle->read())){
	if (is_dir($CompanyDirectory . $DirEntry)
		AND $DirEntry != '..'
		AND $DirEntry != 'includes'
		AND $DirEntry!='.'
		AND $DirEntry!='.svn'
		AND $DirEntry != 'doc'
		AND $DirEntry != 'css'
		AND $DirEntry != 'CVS'
		AND $DirEntry != 'sql'
		AND $DirEntry != 'part_pics'
		AND $DirEntry != 'locale'
		AND $DirEntry != 'fonts'      ){
		if ($_SESSION['reports_dir'] == $CompanyDirectory . $DirEntry){
			echo '<option selected="selected" value="' . $DirEntry . '">' . $DirEntry . '</option>';
		} else {
			echo '<option value="' . $DirEntry . '">' . $DirEntry  . '</option>';
		}
	}
}
echo '</select>
	<fieldhelp>' . _('The directory under which all report pdf files should be created in. A separate directory is recommended') . '</fieldhelp>
</field>';

// HTTPS_Only
echo '<field>
		<label for="X_HTTPS_Only">' . _('Only allow secure socket connections') . ':</label>
		<select name="X_HTTPS_Only">
			<option value="1"' . ($_SESSION['HTTPS_Only'] ? ' selected="selected" ':'') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['HTTPS_Only'] ? ' selected="selected" ':'') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('Force connections to be only over secure sockets - ie encrypted data only') . '</fieldhelp>
	</field>';

/*Perform Database maintenance DB_Maintenance*/
echo '<field>
		<label for="X_DB_Maintenance">' . _('Perform Database Maintenance At Logon') . ':</label>
		<select name="X_DB_Maintenance">';
	if ($_SESSION['DB_Maintenance']=='1'){
		echo '<option selected="selected" value="1">' . _('Daily') . '</option>';
	} else {
		echo '<option value="1">' . _('Daily') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='7'){
		echo '<option selected="selected" value="7">' . _('Weekly') . '</option>';
	} else {
		echo '<option value="7">' . _('Weekly') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='30'){
		echo '<option selected="selected" value="30">' . _('Monthly') . '</option>';
	} else {
		echo '<option value="30">' . _('Monthly') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='0'){
		echo '<option selected="selected" value="0">' . _('Never') . '</option>';
	} else {
		echo '<option value="0">' . _('Never') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='-1'){
		echo '<option selected="selected" value="-1">' . _('Allow SysAdmin Access Only') . '</option>';
	} else {
		echo '<option value="-1">' . _('Allow SysAdmin Access Only') . '</option>';
	}

	echo '</select>
		<fieldhelp>' . _('Uses the function DB_Maintenance defined in ConnectDB_XXXX.inc to perform database maintenance tasks, to run at regular intervals - checked at each and every user login') . '</fieldhelp>
	</field>';

$WikiApplications = array( _('Disabled'),
 					_('WackoWiki'),
 					_('MediaWiki'),
					_('DokuWiki') );

echo '<field>
		<label for="X_WikiApp">' . _('Wiki application') . ':</label>
		<select name="X_WikiApp">';
for ($i=0; $i < sizeof($WikiApplications); $i++ ) {
	echo '<option '.($_SESSION['WikiApp'] == $WikiApplications[$i] ? 'selected="selected" ' : '').'value="'.$WikiApplications[$i].'">' . $WikiApplications[$i]  . '</option>';
}
echo '</select>
	<fieldhelp>' . _('This feature makes webERP show links to a free form company knowledge base using a wiki. This allows sharing of important company information - about customers, suppliers and products and the set up of work flow menus and/or company procedures documentation')  . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_WikiPath">' . _('Wiki Path') . ':</label>
		<input type="text" name="X_WikiPath" size="40" maxlength="40" value="' . $_SESSION['WikiPath'] . '" />
		<fieldhelp>' . _('The path to the wiki installation to form the basis of wiki URLs - or the full URL of the wiki.')  . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_geocode_integration">' . _('Geocode Customers and Suppliers') . ':</label>
		<select name="X_geocode_integration">';
if ($_SESSION['geocode_integration']==1){
		echo  '<option selected="selected" value="1">' . _('Geocode Integration Enabled') . '</option>';
		echo  '<option value="0">' . _('Geocode Integration Disabled') . '</option>';
} else {
		echo  '<option selected="selected" value="0">' . _('Geocode Integration Disabled') . '</option>';
		echo  '<option value="1">' . _('Geocode Integration Enabled') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('This feature will give Latitude and Longitude coordinates to customers and suppliers. Requires access to a mapping provider. You must setup this facility under Main Menu - Setup - Geocode Setup. This feature is experimental.')  . '
</field>';

echo '<field>
		<label for="X_Extended_CustomerInfo">' . _('Extended Customer Information') . ':</label>
		<select name="X_Extended_CustomerInfo">';
if ($_SESSION['Extended_CustomerInfo']==1){
		echo  '<option selected="selected" value="1">' . _('Extended Customer Info Enabled') . '</option>';
		echo  '<option value="0">' . _('Extended Customer Info Disabled') . '</option>';
} else {
		echo  '<option selected="selected" value="0">' . _('Extended Customer Info Disabled') . '</option>';
		echo  '<option value="1">' . _('Extended Customer Info Enabled') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('This feature will give extended information in the Select Customer screen.')  . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_Extended_SupplierInfo">' . _('Extended Supplier Information') . ':</label>
		<select name="X_Extended_SupplierInfo">';
if ($_SESSION['Extended_SupplierInfo']==1){
		echo  '<option selected="selected" value="1">' . _('Extended Supplier Info Enabled') . '</option>';
		echo  '<option value="0">' . _('Extended Supplier Info Disabled') . '</option>';
} else {
		echo  '<option selected="selected" value="0">' . _('Extended Supplier Info Disabled') . '</option>';
		echo  '<option value="1">' . _('Extended Supplier Info Enabled') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('This feature will give extended information in the Select Supplier screen.')  . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_ProhibitJournalsToControlAccounts">' . _('Prohibit GL Journals to Control Accounts') . ':</label>
		<select name="X_ProhibitJournalsToControlAccounts">';
if ($_SESSION['ProhibitJournalsToControlAccounts']=='1'){
		echo  '<option selected="selected" value="1">' . _('Prohibited') . '</option>';
		echo  '<option value="0">' . _('Allowed') . '</option>';
} else {
		echo  '<option value="1">' . _('Prohibited') . '</option>';
		echo  '<option selected="selected" value="0">' . _('Allowed') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('Setting this to prohibited prevents accidentally entering a journal to the automatically posted and reconciled control accounts for creditors (AP) and debtors (AR)') . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_ProhibitPostingsBefore">' . _('Prohibit GL Journals to Periods Prior To') . ':</label>
		<select name="X_ProhibitPostingsBefore">';
$sql = "SELECT lastdate_in_period FROM periods ORDER BY periodno DESC";
$ErrMsg = _('Could not load periods table');
$result = DB_query($sql,$ErrMsg);
if ($_SESSION['ProhibitPostingsBefore']=='' OR $_SESSION['ProhibitPostingsBefore']=='1900-01-01' OR !isset($_SESSION['ProhibitPostingsBefore'])){
	echo '<option selected="selected" value="1900-01-01">' . ConvertSQLDate('1900-01-01') . '</option>';
}
while ($PeriodRow = DB_fetch_row($result)){
	if ($_SESSION['ProhibitPostingsBefore']==$PeriodRow[0]){
		echo  '<option selected="selected" value="' . $PeriodRow[0] . '">' . ConvertSQLDate($PeriodRow[0]) . '</option>';
	} else {
		echo  '<option value="' . $PeriodRow[0] . '">' . ConvertSQLDate($PeriodRow[0]) . '</option>';
	}
}
echo '</select>
	<fieldhelp>' . _('This allows all periods before the selected date to be locked from postings. All postings for transactions dated prior to this date will be posted in the period following this date.') . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_WeightedAverageCosting">' . _('Inventory Costing Method') . ':</label>
		<select name="X_WeightedAverageCosting">';
if ($_SESSION['WeightedAverageCosting']==1){
	echo  '<option selected="selected" value="1">' . _('Weighted Average Costing') . '</option>';
	echo  '<option value="0">' . _('Standard Costing') . '</option>';
} else {
	echo  '<option selected="selected" value="0">' . _('Standard Costing') . '</option>';
	echo  '<option value="1">' . _('Weighted Average Costing') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('webERP allows inventory to be costed based on the weighted average of items in stock or full standard costing with price variances reported. The selection here determines the method used and the general ledger postings resulting from purchase invoices and shipment closing') . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_AutoIssue">' . _('Auto Issue Components') . ':</label>
		<select name="X_AutoIssue">';
if ($_SESSION['AutoIssue']==0) {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
	}
echo '</select>
	<fieldhelp>' . _('When items are manufactured it is possible for the components of the item to be automatically decremented from stock in accordance with the Bill of Material setting') . '</fieldhelp>
</field>' ;

echo '<field>
		<label for="X_ProhibitNegativeStock">' . _('Prohibit Negative Stock') . ':</label>
		<select name="X_ProhibitNegativeStock">';
if ($_SESSION['ProhibitNegativeStock']==0) {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('Setting this parameter to Yes prevents invoicing and the issue of stock if this would result in negative stock. The stock problem must be corrected before the invoice or issue is allowed to be processed.') . '</fieldhelp>
</field>' ;

//Months of Audit Trail to Keep
echo '<field>
		<label for="X_MonthsAuditTrail">' . _('Months of Audit Trail to Retain') . ':</label>
		<input type="text" class="integer" pattern="(?!^0\d+$)[\d]{1,2}" required="required" name="X_MonthsAuditTrail" size="3" maxlength="2" value="' . $_SESSION['MonthsAuditTrail'] . '" />
		<fieldhelp>' . _('If this parameter is set to 0 (zero) then no audit trail is retained. An audit trail is a log of which users performed which additions updates and deletes of database records. The full SQL is retained') . '</fieldhelp>
	</field>';

//Which messages to log
$severityOptions = [
	_('None'),
	_('Errors Only'),
	_('Errors and Warnings'),
	_('Errors, Warnings and Info'),
	_('All'),
];
echo '<field>
		<label for="X_LogSeverity">' . _('Log Severity Level') . ':</label>
		<select name="X_LogSeverity" >';
foreach ($severityOptions as $key => $value) {
	echo '<option value="' . $key . '"' . ($_SESSION['LogSeverity'] == $key ? ' selected' : '') . '>' . $value . '</option>';
}
echo '</select>
	<fieldhelp>' . _('Choose which Status messages to keep in your log file.') . '</fieldhelp>
</field>';

//Path to keep log files in
echo '<field>
		<label for="X_LogPath">' . _('Path to log files') . ':</label>
		<input type="text" name="X_LogPath" size="40" maxlength="79" value="' . $_SESSION['LogPath'] . '" />
		<fieldhelp>' . _('The path to the directory where the log files will be stored. Note the apache user must have write permissions on this directory.') . '</fieldhelp>
</field>';

//DefineControlledOnWOEntry
echo '<field>
		<label for="X_DefineControlledOnWOEntry">' . _('Controlled Items Defined At Work Order Entry') . ':</label>
		<select name="X_DefineControlledOnWOEntry">
			<option value="1"' . ($_SESSION['DefineControlledOnWOEntry']? ' selected="selected" ':'') . '>' . _('Yes') . '</option>
			<option value="0"' . (!$_SESSION['DefineControlledOnWOEntry']? ' selected="selected" ':'') . '>' . _('No') . '</option>
		</select>
		<fieldhelp>' . _('When set to yes, controlled items are defined at the time of the work order creation. Otherwise controlled items (serial numbers and batch/roll/lot references) are entered at the time the finished items are received against the work order') . '</fieldhelp>
	</field>';

//AutoCreateWOs
echo '<field>
		<label for="X_AutoCreateWOs">' . _('Auto Create Work Orders') . ':</label>
		<select name="X_AutoCreateWOs">';
if ($_SESSION['AutoCreateWOs']==0) {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}
echo '</select>
	<fieldhelp>' . _('Setting this parameter to Yes will ensure that when a sales order is placed if there is insufficient stock then a new work order is created at the default factory location') . '</fieldhelp>
</field>' ;

echo '<field>
		<label for="X_DefaultFactoryLocation">' . _('Default Factory Location') . ':</label>
		<select name="X_DefaultFactoryLocation">';

$sql = "SELECT loccode,locationname FROM locations";
$ErrMsg = _('Could not load locations table');
$result = DB_query($sql,$ErrMsg);
while ($LocationRow = DB_fetch_array($result)){
	if ($_SESSION['DefaultFactoryLocation']==$LocationRow['loccode']){
		echo  '<option selected="selected" value="' . $LocationRow['loccode'] . '">' . $LocationRow['locationname'] . '</option>';
	} else {
		echo  '<option value="' .  $LocationRow['loccode'] . '">' . $LocationRow['locationname'] . '</option>';
	}
}
echo '</select>
	<fieldhelp>' . _('This location is the location where work orders will be created from when the auto create work orders option is activated') . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_FactoryManagerEmail">' . _('Factory Manager Email Address') . ':</label>
		<input type="email" name="X_FactoryManagerEmail" size="50" maxlength="50" value="' . $_SESSION['FactoryManagerEmail'] . '" />
		<fieldhelp>' . _('Work orders automatically created when sales orders are entered will be emailed to this address')  . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_PurchasingManagerEmail">' . _('Purchasing Manager Email Address') . ':</label>
		<input type="email" name="X_PurchasingManagerEmail" size="50" maxlength="50" value="' . $_SESSION['PurchasingManagerEmail'] . '" />
		<fieldhelp>' . _('The email address for the purchasing manager, used to receive notifications by the tendering system')  . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_InventoryManagerEmail">' . _('Inventory Manager Email Address') . ':</label>
		<input type="email" name="X_InventoryManagerEmail" size="50" maxlength="50" value="' . $_SESSION['InventoryManagerEmail'] . '" />
		<fieldhelp>' . _('The email address for the inventory manager, where notifications of all manual stock adjustments created are sent by the system. Leave blank if no emails should be sent to the factory manager for manual stock adjustments')  . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_SmtpSetting">' . _('Using Smtp Mail'). '</label>
		<select type="text" name="X_SmtpSetting" >';
		if ($_SESSION['SmtpSetting'] == 0){
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
			echo '<option value="1">' . _('Yes') . '</option>';
		} elseif ($_SESSION['SmtpSetting'] == 1){
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
			echo '<option value="0">' . _('No') . '</option>';
		}
echo '</select>
	 <fieldhelp>' .  _('The default setting is using mail in default php.ini, if you choose Yes for this selection, you can use the SMTP set in the setup section.').'</fieldhelp>
</field>';

echo '<field>
		<label for="X_QualityProdSpecText">' . _('Text for Quality Product Specification') . ':</label>
		<textarea name="X_QualityProdSpecText" rows="3" cols="40">' . $_SESSION['QualityProdSpecText'] . '</textarea>
		<fieldhelp>' . _('This text appears on product specifications') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_QualityCOAText">' . _('Text for Quality Product Certifications') . ':</label>
		<textarea name="X_QualityCOAText" rows="3" cols="40">' . $_SESSION['QualityCOAText'] . '</textarea>
		<fieldhelp>' . _('This text appears on product certifications') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_QualityLogSamples">' . _('Auto Log Quality Samples'). '</label>
		<select type="text" name="X_QualityLogSamples" >';
		if ($_SESSION['QualityLogSamples'] == 0){
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
			echo '<option value="1">' . _('Yes') . '</option>';
		} elseif ($_SESSION['QualityLogSamples'] == 1){
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
			echo '<option value="0">' . _('No') . '</option>';
		}
echo '</select>
	<fieldhelp>' .  _('The flag determines if the system creates quality samples automatically for each lot during P/O Receipt and W/O Receipt transactions.').'</fieldhelp>
</field>';

echo '<field>
		<label for="X_ShortcutMenu">' . _('Allow use of short-cut menus'). '</label>
		<select type="text" name="X_ShortcutMenu" >';
		if ($_SESSION['ShortcutMenu'] == 0){
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
			echo '<option value="1">' . _('Yes') . '</option>';
		} elseif ($_SESSION['ShortcutMenu'] == 1){
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
			echo '<option value="0">' . _('No') . '</option>';
		}
echo '</select>
	<fieldhelp>' .  _('The flag determines if the system allows users to create the javascript short cut menu - this can cause confusion to some users with some themes.').'</fieldhelp>
</field>

	<field>
		<label for="X_LastDayOfWeek">' . _('Last day of the week'). '</label>
		<select type="text" name="X_LastDayOfWeek" >
			<option ' . ($_SESSION['LastDayOfWeek'] == 0 ?'selected="selected"':'') . ' value="0">' . _('Sunday') . '</option>
			<option ' . ($_SESSION['LastDayOfWeek'] == 1 ?'selected="selected"':'') . ' value="1">' . _('Monday') . '</option>
			<option ' . ($_SESSION['LastDayOfWeek'] == 2 ?'selected="selected"':'') . ' value="2">' . _('Tuesday') . '</option>
			<option ' . ($_SESSION['LastDayOfWeek'] == 3 ?'selected="selected"':'') . ' value="3">' . _('Wednesday') . '</option>
			<option ' . ($_SESSION['LastDayOfWeek'] == 4 ?'selected="selected"':'') . ' value="4">' . _('Thursday') . '</option>
			<option ' . ($_SESSION['LastDayOfWeek'] == 5 ?'selected="selected"':'') . ' value="5">' . _('Friday') . '</option>
			<option ' . ($_SESSION['LastDayOfWeek'] == 6 ?'selected="selected"':'') . ' value="6">' . _('Saturday') . '</option>
		</select>
		<fieldhelp>' .  _('Timesheet entry default to weeks ending on this day').'</fieldhelp>
	</field>';

	echo '</fieldset>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Update') . '" />
		</div>
	</form>';

include('includes/footer.php');

?>
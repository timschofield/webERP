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

		if ($_SESSION['Usage_Days_For_Packaging_Stock'] != $_POST['X_Usage_Days_For_Packaging_Stock'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Usage_Days_For_Packaging_Stock']."' WHERE confname = 'Usage_Days_For_Packaging_Stock'";
		}
		if ($_SESSION['Forecast_Days_For_Packaging_Stock'] != $_POST['X_Forecast_Days_For_Packaging_Stock'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Forecast_Days_For_Packaging_Stock']."' WHERE confname = 'Forecast_Days_For_Packaging_Stock'";
		}
		if ($_SESSION['Factor_Gudang_Packaging'] != $_POST['X_Factor_Gudang_Packaging'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Factor_Gudang_Packaging']."' WHERE confname = 'Factor_Gudang_Packaging'";
		}
		if ($_SESSION['Factor_Gudang_Packaging_Paper_Inside_Box'] != $_POST['X_Factor_Gudang_Packaging_Paper_Inside_Box'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Factor_Gudang_Packaging_Paper_Inside_Box']."' WHERE confname = 'Factor_Gudang_Packaging_Paper_Inside_Box'";
		}

		if ($_SESSION['ShopMode'] != $_POST['X_ShopMode'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_ShopMode']."' WHERE confname = 'ShopMode'";
		}
		if ($_SESSION['ShopManagerEmail'] != $_POST['X_ShopManagerEmail'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '" . DB_escape_string($_POST['X_ShopManagerEmail']) ."' WHERE confname = 'ShopManagerEmail'";
		}

		if ($_SESSION['X_Maximum_QOH_To_Show_In_Marketplaces'] != $_POST['X_Maximum_QOH_To_Show_In_Marketplaces'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Maximum_QOH_To_Show_In_Marketplaces']."' WHERE confname = 'Maximum_QOH_To_Show_In_Marketplaces'";
		}
		if ($_SESSION['X_Minimum_QOH_To_Show_Item_In_Marketplaces'] != $_POST['X_Minimum_QOH_To_Show_Item_In_Marketplaces'] ) {
			$SQL[] = "UPDATE klconfig SET confvalue = '".$_POST['X_Minimum_QOH_To_Show_Item_In_Marketplaces']."' WHERE confname = 'Minimum_QOH_To_Show_Item_In_Marketplaces'";
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
		<legend>' . __('Packaging Forecast Settings') . '</legend>';
echo FieldToSelectOneNumber('X_Usage_Days_For_Packaging_Stock',  $_SESSION['Usage_Days_For_Packaging_Stock'], 6, 5, '# Days of Packaging Usage for Forecast', 'Number of days  of Packaging Usage for Forecast', '', '150');
echo FieldToSelectOneNumber('X_Forecast_Days_For_Packaging_Stock',  $_SESSION['Forecast_Days_For_Packaging_Stock'], 6, 5, 'Optimum # Days of Packaging Stock Forecast', 'Number of days to forecast packaging stock', '', '151');
echo FieldToSelectOneNumber('X_Factor_Gudang_Packaging',  $_SESSION['Factor_Gudang_Packaging'], 6, 5, 'Factor Gudang Packaging (except paper inside box)', 'Factor for packaging stock in gudang', '', '152');
echo FieldToSelectOneNumber('X_Factor_Gudang_Packaging_Paper_Inside_Box',  $_SESSION['Factor_Gudang_Packaging_Paper_Inside_Box'], 6, 5, 'Factor Gudang Packaging Paper Inside Box', 'Factor for packaging stock in gudang for paper inside box', '', '153');
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
// -------------------------------------
// KL RICARD END Specific Settings for PTADU webERP
// -------------------------------------

echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Update') . '" />
	</div>
</form>';

include(__DIR__ . '/includes/footer.php');

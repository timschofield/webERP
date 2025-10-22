<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Shop Configuration');
$ViewTopic = 'Setup';
$BookMark = 'ShopParameters';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Shop Configuration')
	. '" alt="" />' . $Title. '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if ($InputError !=1){

		$SQL = array();

		if ($_SESSION['ShopName'] != $_POST['X_ShopName'] ) {
			$SQL[] = "UPDATE config SET confvalue = '" . DB_escape_string($_POST['X_ShopName']) ."' WHERE confname = 'ShopName'";
		}
		if ($_SESSION['ShopTitle'] != $_POST['X_ShopTitle'] ) {
			$SQL[] = "UPDATE config SET confvalue = '" . DB_escape_string($_POST['X_ShopTitle']) ."' WHERE confname = 'ShopTitle'";
		}
		if ($_SESSION['ShopManagerEmail'] != $_POST['X_ShopManagerEmail'] ) {
			$SQL[] = "UPDATE config SET confvalue = '" . DB_escape_string($_POST['X_ShopManagerEmail']) ."' WHERE confname = 'ShopManagerEmail'";
		}
		if ($_SESSION['ShopPrivacyStatement'] != $_POST['X_ShopPrivacyStatement'] ) {
			$SQL[] = "UPDATE config SET confvalue = '" . DB_escape_string($_POST['X_ShopPrivacyStatement']) ."' WHERE confname = 'ShopPrivacyStatement'";
		}
		if ($_SESSION['ShopFreightPolicy'] != $_POST['X_ShopFreightPolicy'] ) {
			$SQL[] = "UPDATE config SET confvalue = '" . DB_escape_string($_POST['X_ShopFreightPolicy']) ."' WHERE confname = 'ShopFreightPolicy'";
		}
		if ($_SESSION['ShopTermsConditions'] != $_POST['X_ShopTermsConditions'] ) {
			$SQL[] = "UPDATE config SET confvalue = '" . DB_escape_string($_POST['X_ShopTermsConditions']) ."' WHERE confname = 'ShopTermsConditions'";
		}
		if ($_SESSION['ShopAboutUs'] != $_POST['X_ShopAboutUs'] ) {
			$SQL[] = "UPDATE config SET confvalue = '" . DB_escape_string($_POST['X_ShopAboutUs']) ."' WHERE confname = 'ShopAboutUs'";
		}
		if ($_SESSION['ShopContactUs'] != $_POST['X_ShopContactUs'] ) {
			$SQL[] = "UPDATE config SET confvalue = '" . DB_escape_string($_POST['X_ShopContactUs']) ."' WHERE confname = 'ShopContactUs'";
		}
		if ($_SESSION['ShopDebtorNo'] != $_POST['X_ShopDebtorNo'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopDebtorNo']."' WHERE confname = 'ShopDebtorNo'";
		}
		if ($_SESSION['ShopBranchCode'] != $_POST['X_ShopBranchCode'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopBranchCode']."' WHERE confname = 'ShopBranchCode'";
		}

		if ($_SESSION['ShopShowOnlyAvailableItems'] != $_POST['X_ShopShowOnlyAvailableItems'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopShowOnlyAvailableItems']."' WHERE confname = 'ShopShowOnlyAvailableItems'";
		}

		if ($_SESSION['ShopShowQOHColumn'] != $_POST['X_ShopShowQOHColumn'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopShowQOHColumn']."' WHERE confname = 'ShopShowQOHColumn'";
		}

		if (isset($_POST['X_ShopStockLocations'])) {
			$ShopStockLocations = '';
			foreach ($_POST['X_ShopStockLocations'] as $Location){
				$ShopStockLocations .= $Location .',';
			}
			$ShopStockLocations = mb_substr($ShopStockLocations,0,mb_strlen($ShopStockLocations)-1);
			if ($_SESSION['ShopStockLocations'] != $ShopStockLocations){
				$SQL[] = "UPDATE config SET confvalue='" . $ShopStockLocations . "' WHERE confname='ShopStockLocations'";
			}
		}

		if ($_SESSION['ShopAllowSurcharges'] != $_POST['X_ShopAllowSurcharges'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopAllowSurcharges']."' WHERE confname = 'ShopAllowSurcharges'";
		}

		if ($_SESSION['ShopAllowCreditCards'] != $_POST['X_ShopAllowCreditCards'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopAllowCreditCards']."' WHERE confname = 'ShopAllowCreditCards'";
		}
		if ($_SESSION['ShopAllowPayPal'] != $_POST['X_ShopAllowPayPal'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopAllowPayPal']."' WHERE confname = 'ShopAllowPayPal'";
		}
		if ($_SESSION['ShopAllowBankTransfer'] != $_POST['X_ShopAllowBankTransfer'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopAllowBankTransfer']."' WHERE confname = 'ShopAllowBankTransfer'";
		}

		if ($_SESSION['ShopPayPalSurcharge'] != $_POST['X_ShopPayPalSurcharge'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalSurcharge']."' WHERE confname = 'ShopPayPalSurcharge'";
		}
		if ($_SESSION['ShopBankTransferSurcharge'] != $_POST['X_ShopBankTransferSurcharge'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopBankTransferSurcharge']."' WHERE confname = 'ShopBankTransferSurcharge'";
		}
		if ($_SESSION['ShopCreditCardSurcharge'] != $_POST['X_ShopCreditCardSurcharge'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopCreditCardSurcharge']."' WHERE confname = 'ShopCreditCardSurcharge'";
		}
		if ($_SESSION['ShopSurchargeStockID'] != $_POST['X_ShopSurchargeStockID'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopSurchargeStockID']."' WHERE confname = 'ShopSurchargeStockID'";
		}
		if ($_SESSION['ShopCreditCardBankAccount'] != $_POST['X_ShopCreditCardBankAccount'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopCreditCardBankAccount']."' WHERE confname = 'ShopCreditCardBankAccount'";
		}
		if ($_SESSION['ShopPayPalBankAccount'] != $_POST['X_ShopPayPalBankAccount'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalBankAccount']."' WHERE confname = 'ShopPayPalBankAccount'";
		}
		if ($_SESSION['ShopPayPalCommissionAccount'] != $_POST['X_ShopPayPalCommissionAccount'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalCommissionAccount']."' WHERE confname = 'ShopPayPalCommissionAccount'";
		}
		if ($_SESSION['ShopFreightMethod'] != $_POST['X_ShopFreightMethod'] ) {
			$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopFreightMethod']."' WHERE confname = 'ShopFreightMethod'";
		}

		if (!$AllowDemoMode) {
			if ($_SESSION['ShopCreditCardGateway'] != $_POST['X_ShopCreditCardGateway'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopCreditCardGateway']."' WHERE confname = 'ShopCreditCardGateway'";
			}
			if ($_SESSION['ShopPayPalUser'] != $_POST['X_ShopPayPalUser'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalUser']."' WHERE confname = 'ShopPayPalUser'";
			}
			if ($_SESSION['ShopPayPalPassword'] != $_POST['X_ShopPayPalPassword'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalPassword']."' WHERE confname = 'ShopPayPalPassword'";
			}
			if ($_SESSION['ShopPayPalSignature'] != $_POST['X_ShopPayPalSignature'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalSignature']."' WHERE confname = 'ShopPayPalSignature'";
			}
			if ($_SESSION['ShopPayPalProUser'] != $_POST['X_ShopPayPalProUser'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalProUser']."' WHERE confname = 'ShopPayPalProUser'";
			}
			if ($_SESSION['ShopPayPalPassword'] != $_POST['X_ShopPayPalProPassword'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalProPassword']."' WHERE confname = 'ShopPayPalProPassword'";
			}
			if ($_SESSION['ShopPayPalSignature'] != $_POST['X_ShopPayPalProSignature'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayPalProSignature']."' WHERE confname = 'ShopPayPalProSignature'";
			}
			if ($_SESSION['ShopPayFlowUser'] != $_POST['X_ShopPayFlowUser'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayFlowUser']."' WHERE confname = 'ShopPayFlowUser'";
			}
			if ($_SESSION['ShopPayFlowPassword'] != $_POST['X_ShopPayFlowPassword'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayFlowPassword']."' WHERE confname = 'ShopPayFlowPassword'";
			}
			if ($_SESSION['ShopPayFlowVendor'] != $_POST['X_ShopPayFlowVendor'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayFlowVendor']."' WHERE confname = 'ShopPayFlowVendor'";
			}
			if ($_SESSION['ShopPayFlowMerchant'] != $_POST['X_ShopPayFlowMerchant'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopPayFlowMerchant']."' WHERE confname = 'ShopPayFlowMerchant'";
			}

			if ($_SESSION['ShopMode'] != $_POST['X_ShopMode'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopMode']."' WHERE confname = 'ShopMode'";
			}

			if ($_SESSION['ShopSwipeHQMerchantID'] != $_POST['X_ShopSwipeHQMerchantID'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopSwipeHQMerchantID']."' WHERE confname = 'ShopSwipeHQMerchantID'";
			}
			if ($_SESSION['ShopSwipeHQAPIKey'] != $_POST['X_ShopSwipeHQAPIKey'] ) {
				$SQL[] = "UPDATE config SET confvalue = '".$_POST['X_ShopSwipeHQAPIKey']."' WHERE confname = 'ShopSwipeHQAPIKey'";
			}
		} //these options only available in live shop - not the demo.
			else { //always ensure test mode and PayFlow for demo site
				$SQL[] = "UPDATE config SET confvalue = 'test' WHERE confname = 'ShopMode'";
				$SQL[] = "UPDATE config SET confvalue = 'PayPalPro' WHERE confname = 'ShopCreditCardGateway'";

		}
		$ErrMsg =  __('The shop configuration could not be updated because');

		if (sizeof($SQL) > 0 ) {

			DB_Txn_Begin();
			foreach ($SQL as $SqlLine) {
				$Result = DB_query($SqlLine, $ErrMsg, '', true);
			}
			DB_Txn_Commit();
			prnMsg( __('Shop configuration updated'),'success');

			$ForceConfigReload = true; // Required to force a load even if stored in the session vars
			include($PathPrefix . 'includes/GetConfig.php');
			$ForceConfigReload = false;
		}
	} else {
		prnMsg( __('Validation failed') . ', ' . __('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<fieldset class="TwoByThreeColumn">';

echo '<fieldset class="Column1x1">
		<legend>' . __('General Settings') . '</legend>';

echo '<field>
		<label for="X_ShopMode">' . __('Test or Live Mode') . ':</label>
		<select name="X_ShopMode">';
		if ($_SESSION['ShopMode']== 'test' OR $AllowDemoMode){
			echo '<option selected="selected" value="test">' . __('Test') . '</option>
				<option value="live">' . __('Live') . '</option>';
		} else {
			echo '<option value="test">' . __('Test') . '</option>
				<option selected="selected" value="live">' . __('Live') . '</option>';
		}
		echo '</select>
		<fieldhelp>' . __('Must change this to live mode when the shop is activie. No PayPal or credit card transactions will be processed in test mode') . '</fieldhelp>
	</field>';
//Shop Name
echo '<field>
		<label for="X_ShopName">' . __('Shop Name') . ':</label>
		<input type="text" name="X_ShopName" required="required" autofocus="autofocus" size="40" maxlength="40" value="' . $_SESSION['ShopName'] . '" />
		<fieldhelp>' . __('Enter the name of the shop that will be displayed on all the store pages') . '</fieldhelp>
	</field>';

//Shop Title
echo '<field>
		<label for="X_ShopTitle">' . __('Shop Title') . ':</label>
		<input type="text" name="X_ShopTitle" required="required" size="40" maxlength="40" value="' . $_SESSION['ShopTitle'] . '" />
		<fieldhelp>' . __('Enter the title of the shop that will be displayed on the main webSHOP page. Useful for SEO purposes.') . '</fieldhelp>
	</field>';

//Shop Manager Email
echo '<field>
		<label for="X_ShopManagerEmail">' . __('Shop Manager Email') . ':</label>
		<input type="email" name="X_ShopManagerEmail" required="required" size="40" maxlength="50" value="' . $_SESSION['ShopManagerEmail'] . '" />
		<fieldhelp>' . __('Enter the email address of the webSHOP manager.') . '</fieldhelp>
	</field>';

// Shop Customer
echo '<field>
		<label for="X_ShopDebtorNo">' . __('Default Web Shop Customer Acount') . ':</label>
		<input type="text" size="12" maxlength="10" required="required" name="X_ShopDebtorNo" value="' . $_SESSION['ShopDebtorNo'] . '" />
		<fieldhelp>' . __('Select the customer account that is to be used for the web-store sales') . '</fieldhelp>
	</field>';
// Shop Customer Branch
echo '<field>
		<label for="X_ShopBranchCode">' . __('Default Web Shop Branch Code').':</label>
		<input type="text" required="required" size="12" maxlength="10" name="X_ShopBranchCode" value="' . $_SESSION['ShopBranchCode'] . '" />
		<fieldhelp>' . __('The customer branch code that is to be used - a branch of the above custoemr account - for web-store sales') . '</fieldhelp>
	</field>';

//Privacy Statement
echo '<field>
		<label for="X_ShopPrivacyStatement">' . __('Privacy Statement') . ':</label>
		<textarea name="X_ShopPrivacyStatement" rows="8" cols="45">' . stripslashes($_SESSION['ShopPrivacyStatement']) . '</textarea>
		<fieldhelp>' . __('This text will appear on the web-store page that spells out the privacy policy of the web-shop') . ' ' . __('Enter the raw html without any line breaks') .  '</fieldhelp>
	</field>';
//Terms and Conditions
echo '<field>
		<label for="X_ShopTermsConditions">' . __('Terms and Conditions') . ':</label>
		<textarea name="X_ShopTermsConditions" rows="8" cols="45">' . stripslashes($_SESSION['ShopTermsConditions']) . '</textarea>
		<fieldhelp>' . __('This text will appear on the web-store page that spells out the terms and conditions associated with sales from the web-shop') . ' ' . __('Enter the raw html without any line breaks') . '</fieldhelp>
	</field>';
//About Us
echo '<field>
		<label for="X_ShopAboutUs">' . __('About Us') . ':</label>
		<textarea name="X_ShopAboutUs" rows="8" cols="45">' . stripslashes($_SESSION['ShopAboutUs']) . '</textarea>
		<fieldhelp>' . __('This text will appear on the web-store page that provides information about us to users of the web-store.') . ' ' . __('Enter the raw html without any line breaks')  . '</fieldhelp>
	</field>';
echo '<field>
		<label for="X_ShopContactUs">' . __('Contact Us') . ':</label>
		<textarea name="X_ShopContactUs" rows="8" cols="45">' . stripslashes($_SESSION['ShopContactUs']) . '</textarea>
		<fieldhelp>' . __('This text will appear on the web-store page that provides contact information to users of the web-store.') . ' ' . __('Enter the raw html without any line breaks') . '</fieldhelp>
	</field>';
//Freight Policy
echo '<field>
		<label for="X_ShopFreightPolicy">' . __('Freight Policy') . ':</label>
		<textarea name="X_ShopFreightPolicy" rows="8" cols="45">' . stripslashes($_SESSION['ShopFreightPolicy']) . '</textarea>
		<fieldhelp>' . __('This text will appear on the web-store page that spells out the freight policy of the web-shop') . ' ' . __('Enter the raw html without any line breaks')  . '</fieldhelp>
	</field>';
echo '</fieldset>';

echo '<fieldset class="Column1x2">
		<legend>' . __('Web-Store Behaviour Settings') . '</legend>';

echo '<field>
		<label for="X_ShopShowOnlyAvailableItems">' . __('Show Only Items With Available Stock') . ':</label>
		<select name="X_ShopShowOnlyAvailableItems">';
if ($_SESSION['ShopShowOnlyAvailableItems'] == '1') {
	echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	echo '<option value="0">' . __('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	echo '<option value="1">' . __('Yes') . '</option>';
}
echo '</select>
		<fieldhelp>' . __('Shows only items with QOH > 0 thus avoiding the Arriving Soon items.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ShopShowQOHColumn">' . __('Show/Hide QOH Column') . ':</label>
		<select name="X_ShopShowQOHColumn">';
if ($_SESSION['ShopShowQOHColumn'] == '1') {
	echo '<option selected="selected" value="1">' . __('Show') . '</option>';
	echo '<option value="0">' . __('Hide') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . __('Hide') . '</option>';
	echo '<option value="1">' . __('Show') . '</option>';
}
echo '</select>
		<fieldhelp>' . __('Shows / Hides the QOH Column Select Hide if you do not want webSHOP visitors to know how many stock do you currently hold.') . '</fieldhelp>
	</field>';

if (mb_strlen($_SESSION['ShopStockLocations'])>1){
	$Locations = explode(',',$_SESSION['ShopStockLocations']);
} else {
	$Locations = array();
}
echo '<field>
		<label for="X_ShopStockLocations">' . __('Stock Locations') . ':</label>
		<select name="X_ShopStockLocations[]" size="5" multiple="multiple" >';
$LocResult = DB_query("SELECT loccode, locationname FROM locations");
while ($LocRow = DB_fetch_array($LocResult)){
	if (in_array($LocRow['loccode'],$Locations)){
		echo '<option selected="selected" value="' . $LocRow['loccode'] . '">' . $LocRow['locationname']  . '</option>';
	} else {
		echo '<option value="' . $LocRow['loccode'] . '">' . $LocRow['locationname']  . '</option>';
	}
}
echo '</select>
		<fieldhelp>' . __('Select one or more stock locations (warehouses) that webSHOP should consider stock for the purposes of displaying the on hand quantity for customer information') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ShopAllowSurcharges">' . __('Allow Payment Surcharges') . ':</label>
		<select name="X_ShopAllowSurcharges">';
if ($_SESSION['ShopAllowSurcharges'] == '1') {
	echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	echo '<option value="0">' . __('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	echo '<option value="1">' . __('Yes') . '</option>';
}
echo '</select>
		<fieldhelp>' . __('Add surcharges for different payment methods.') . '</fieldhelp>
	</field>';

$DummyItemsResult = DB_query("SELECT stockid, description FROM stockmaster WHERE mbflag='D'");
echo '<field>
		<label for="X_ShopSurchargeStockID">' . __('Surcharges Stock Item') . ':</label>
		<select name="X_ShopSurchargeStockID">';
while ($ItemsRow = DB_fetch_array($DummyItemsResult)){
	if ($_SESSION['ShopSurchargeStockID'] ==$ItemsRow['stockid']) {
		echo '<option selected="selected" value="' . $ItemsRow['stockid'] . '">' . $ItemsRow['stockid'] . '-' . $ItemsRow['description'] . '</option>';
	} else {
		echo '<option value="' . $ItemsRow['stockid'] . '">' . $ItemsRow['stockid'] . '-' . $ItemsRow['description'] . '</option>';
	}
}
echo '</select>
		<fieldhelp>' . __('Select the webERP service item to use for payment surcharges to be processed as') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ShopFreightMethod">' . __('Freight Calculations') . ':</label>
		<select name="X_ShopFreightMethod">';

$FreightMethods = array(array('MethodName'=>'No Freight','MethodCode'=>'NoFreight'),
						array('MethodName'=>'webERP calculation','MethodCode'=>'webERPCalculation'),
						array('MethodName'=>'Australia Post API','MethodCode'=>'AusPost'));

foreach($FreightMethods as $FreightMethod){
	if ($_SESSION['ShopFreightMethod'] == $FreightMethod['MethodCode']) {
		echo '<option selected="selected" value="' . $FreightMethod['MethodCode'] . '">' . $FreightMethod['MethodName'] . '</option>';
	} else {
		echo '<option value="' . $FreightMethod['MethodCode'] . '">' . $FreightMethod['MethodName'] . '</option>';
	}
}
echo '</select>
		<fieldhelp>' . __('Select the freight calculation method to use for the webSHOP') . '</fieldhelp>
	</field>';

echo '</fieldset>';

echo '<fieldset class="Column2x1">
		<legend>' . __('Bank Transfer Settings') . '</legend>';

echo '<field>
		<label for="X_ShopAllowBankTransfer">' . __('Allow Bank Transfer Payment') . ':</label>
		<select name="X_ShopAllowBankTransfer">';
if ($_SESSION['ShopAllowBankTransfer'] ==1) {
	echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	echo '<option value="0">' . __('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	echo '<option value="1">' . __('Yes') . '</option>';
}
echo '</select>
		<fieldhelp>' . __('Allow bank transfers to be used for payments.') . '</fieldhelp>
	</field>';
echo '<field>
		<label for="X_ShopBankTransferSurcharge">' . __('Bank Transfer Surcharge') . ':</label>
		<input type="text" class="number" size="3" maxlength="3" name="X_ShopBankTransferSurcharge" value="' . $_SESSION['ShopBankTransferSurcharge'] . '" />
		<fieldhelp>' . __('The bank transfer surcharge') . '</fieldhelp>
	</field>';

echo '</fieldset>';

echo '<fieldset class="Column2x2">
		<legend>' . __('Paypal Settings') . '</legend>';

echo '<field>
		<label for="X_ShopAllowPayPal">' . __('Allow PayPal Payment') . ':</label>
		<select name="X_ShopAllowPayPal">';
if ($_SESSION['ShopAllowPayPal'] ==1) {
	echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	echo '<option value="0">' . __('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	echo '<option value="1">' . __('Yes') . '</option>';
}
echo '</select>
		<fieldhelp>' . __('Allow PayPal to be used for payments. The configuration details for PayPal payments must be entered below') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ShopPayPalBankAccount">' . __('Pay Pal Bank Account') . ':</label>
		<select name="X_ShopPayPalBankAccount">';
$BankAccountsResult = DB_query("SELECT accountcode, bankaccountname FROM bankaccounts");
while ($BankAccountRow = DB_fetch_array($BankAccountsResult)){
	if ($_SESSION['ShopPayPalBankAccount'] ==$BankAccountRow['accountcode']) {
		echo '<option selected="selected" value="' . $BankAccountRow['accountcode'] . '">' . $BankAccountRow['bankaccountname'] . '</option>';
	} else {
		echo '<option value="' . $BankAccountRow['accountcode'] . '">' . $BankAccountRow['bankaccountname'] . '</option>';
	}
}
echo '</select>
		<fieldhelp>' . __('Select the webERP bank account to use for receipts processed by Pay Pal') . '</fieldhelp>
	</field>';


echo '<field>
		<label for="X_ShopPayPalCommissionAccount">' . __('Pay Pal Commission Account') . ':</label>
		<select name="X_ShopPayPalCommissionAccount">';
$AccountsResult = DB_query("SELECT accountcode,
						accountname
					FROM chartmaster INNER JOIN accountgroups
					ON chartmaster.group_=accountgroups.groupname
					WHERE accountgroups.pandl=1
					ORDER BY chartmaster.accountcode");
while ($AccountRow = DB_fetch_array($AccountsResult)){
	if ($_SESSION['ShopPayPalCommissionAccount'] == $AccountRow['accountcode']) {
		echo '<option selected="selected" value="' . $AccountRow['accountcode'] . '">' . $AccountRow['accountname'] . '</option>';
	} else {
		echo '<option value="' . $AccountRow['accountcode'] . '">' . $AccountRow['accountname'] . '</option>';
	}
}
echo '</select>
		<fieldhelp>' . __('Select the webERP P/L account to use for commissions (transaction fees) charged by Pay Pal') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ShopPayPalSurcharge">' . __('PayPal Surcharge') . ':</label>
		<input type="text" class="number" size="5" maxlength="5" name="X_ShopPayPalSurcharge" value="' . $_SESSION['ShopPayPalSurcharge'] . '" />
		<fieldhelp>' . __('The PayPal surcharge') . '</fieldhelp>
	</field>';

if ($AllowDemoMode){
	echo '<field>
			<label>' . __('Paypal user account details') . '</label>
			<fieldtext>' . __('Cannot be set in the demo') . '</fieldtext>
		</field>';
} else {
	echo '<field>
			<label for="X_ShopPayPalUser">' . __('PayPal User') . ':</label>
			<input type="text" class="noSpecialChars" size="40" maxlength="40" name="X_ShopPayPalUser" value="' . $_SESSION['ShopPayPalUser'] . '" />
			<fieldhelp>' . __('The PayPal Merchant User account for Pay Pal Express Checkout') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="X_ShopPayPalPassword">' . __('PayPal Password') . ':</label>
			<input type="text" size="20" maxlength="20" name="X_ShopPayPalPassword" value="' . $_SESSION['ShopPayPalPassword'] . '" />
			<fieldhelp>' . __('The PayPal Merchant account password for Pay Pal Express Checkout') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="X_ShopPayPalSignature">' . __('PayPal Signature') . ':</label>
			<input type="text" size="45" maxlength="100" name="X_ShopPayPalSignature" value="' . $_SESSION['ShopPayPalSignature'] . '" />
			<fieldhelp>' . __('The PayPal merchant account signature for Pay Pal Express Checkout') . '</fieldhelp>
		</field>';
}
echo '</fieldset>';

echo '<fieldset class="Column1x3">
		<legend>' . __('Credit Card Processing Settings') . '</legend>';

echo '<field>
		<label for="X_ShopAllowCreditCards">' . __('Allow Credit Card Payments') . ':</label>
		<select name="X_ShopAllowCreditCards">';
if ($_SESSION['ShopAllowCreditCards'] ==1) {
	echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	echo '<option value="0">' . __('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	echo '<option value="1">' . __('Yes') . '</option>';
}
echo '</select>
		<fieldhelp>' . __('Allow Credit Cards to be used for payments. The configuration details for PayPal Pro or one of the other credit card payment solutions must be configured.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ShopCreditCardGateway">' . __('Credit Card Gateway') . ':</label>';
if ($AllowDemoMode) {
	echo '<select name="SomeNameNotUsed">';
} else {
	echo '<select name="X_ShopCreditCardGateway">';
}
if ($_SESSION['ShopCreditCardGateway'] =='PayPalPro') {
	echo '<option selected="selected" value="PayPalPro">' . __('PayPal Pro') . '</option>';
} else {
	echo '<option value="PayPalPro">' . __('PayPal Pro') . '</option>';
}
if ($_SESSION['ShopCreditCardGateway'] =='PayFlow') {
	echo '<option selected="selected" value="PayFlow">' . __('PayFlow Pro') . '</option>';
} else {
	echo '<option value="PayFlow">' . __('PayFlow Pro') . '</option>';
}
if ($_SESSION['ShopCreditCardGateway'] =='SwipeHQ') {
	echo '<option selected="selected" value="SwipeHQ">' . __('Swipe HQ - New Zealand') . '</option>';
} else {
	echo '<option value="SwipeHQ">' . __('Swipe HQ - New Zealand') . '</option>';
}
echo '</select>
		<fieldhelp>' . __('Select the credit card gateway system to be used.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ShopCreditCardSurcharge">' . __('Credit Card Surcharge') . ':</label>
		<input type="text" class="number" size="5" maxlength="5" name="X_ShopCreditCardSurcharge" value="' . $_SESSION['ShopCreditCardSurcharge'] . '" />
		<fieldhelp>' . __('The credit card surcharge') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="X_ShopCreditCardBankAccount">' . __('Credit Card Bank Account') . ':</label>
		<select name="X_ShopCreditCardBankAccount">';
DB_data_seek($BankAccountsResult,0);
while ($BankAccountRow = DB_fetch_array($BankAccountsResult)){
	if ($_SESSION['ShopCreditCardBankAccount'] ==$BankAccountRow['accountcode']) {
		echo '<option selected="selected" value="' . $BankAccountRow['accountcode'] . '">' . $BankAccountRow['bankaccountname'] . '</option>';
	} else {
		echo '<option value="' . $BankAccountRow['accountcode'] . '">' . $BankAccountRow['bankaccountname'] . '</option>';
	}
}
echo '</select>
		<fieldhelp>' . __('Select the webERP bank account to use for receipts processed by credit card') . '</fieldhelp>
	</field>';

if ($AllowDemoMode){
	echo '<field>
			<label>' . __('Credit card user account details') . '</label>
			<fieldtext>' . __('Cannot be set in the demo') . '</fieldtext>
		</field>';
} else {
	echo '<field>
			<label for="X_ShopPayPalProUser">' . __('PayPal Pro User') . ':</label>
			<input type="text" class="noSpecialChars"  size="40" maxlength="40" name="X_ShopPayPalProUser" value="' . $_SESSION['ShopPayPalProUser'] . '" />
			<fieldhelp>' . __('The') . '<a href="https://www.paypal.com/us/webapps/mpp/paypal-payments-pro">' . __('PayPal Pro') .'</a> ' .  __('Merchant User account for credit card payment available in only USA and Canada') .  '</fieldhelp>
		</field>';

	echo '<field>
			<label for="X_ShopPayPalProPassword">' . __('PayPal Pro Password') . ':</label>
			<input type="text" size="20" maxlength="20" name="X_ShopPayPalProPassword" value="' . $_SESSION['ShopPayPalProPassword'] . '" />
			<fieldhelp>' . __('The') . '<a href="https://www.paypal.com/us/webapps/mpp/paypal-payments-pro">' . __('PayPal Pro') .'</a> ' . __('Merchant account password for credit card payment available in only USA and Canada') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="X_ShopPayPalProSignature">' . __('PayPal Pro Signature') . ':</label>
			<input type="text" size="80" maxlength="80" name="X_ShopPayPalProSignature" value="' . $_SESSION['ShopPayPalProSignature'] . '" />
			<fieldhelp>' . __('The') . '<a href="https://www.paypal.com/us/webapps/mpp/paypal-payments-pro">' . __('PayPal Pro') . '</a> ' .__('merchant account signature for credit card payment available in only USA and Canada') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="X_ShopPayFlowUser">' . __('Pay Flow Pro User') . ':</label>
			<input type="text" class="noSpecialChars"  size="40" maxlength="40" name="X_ShopPayFlowUser" value="' . $_SESSION['ShopPayFlowUser'] . '" />
			<fieldhelp>' . __('The') . ' <a href="https://www.paypal.com/us/webapps/mpp/payflow-payment-gateway">PayFlow Pro</a> ' . __('Merchant User account') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="X_ShopPayFlowPassword">' . __('Pay Flow Pro Password') . ':</label>
			<input type="text" size="20" maxlength="20" name="X_ShopPayFlowPassword" value="' . $_SESSION['ShopPayFlowPassword'] . '" />
			<fieldhelp>' . __('The') . ' <a href="https://www.paypal.com/us/webapps/mpp/payflow-payment-gateway">PayFlow Pro</a> ' . __('Merchant account password') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="X_ShopPayFlowVendor">' . __('Pay Flow Pro Vendor') . ':</label>
			<input type="text" class="noSpecialChars" size="20" maxlength="20" name="X_ShopPayFlowVendor" value="' . $_SESSION['ShopPayFlowVendor'] . '" />
			<fieldhelp>' . __('The') . ' <a href="https://www.paypal.com/us/webapps/mpp/payflow-payment-gateway">PayFlow Pro</a> ' . __('vendor') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="X_ShopPayFlowMerchant">' . __('Pay Flow Pro Merchant') . ':</label>
			<input type="text" size="20" maxlength="20" name="X_ShopPayFlowMerchant" value="' . $_SESSION['ShopPayFlowMerchant'] . '" />
			<fieldhelp>' . __('The') . ' <a href="https://www.paypal.com/us/webapps/mpp/payflow-payment-gateway">PayFlow Pro</a> ' . __('merchant') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="X_ShopSwipeHQMerchantID">' . __('SwipeHQ Merchant ID') . ':</label>
			<input type="text" class="noSpecialChars" size="15" maxlength="15" name="X_ShopSwipeHQMerchantID" value="' . $_SESSION['ShopSwipeHQMerchantID'] . '" />
			<fieldhelp>' . __('The'). ' <a href="https://www.swipehq.com/credit-card-payment-solutions/index.php">SwipeHQ</a> ' . __('Merchant ID - see SwipeHQ settings -> API credentials') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="X_ShopSwipeHQAPIKey">' . __('SwipeHQ API Key') . ':</label>
			<input type="text" size="80"  maxlenght="100" name="X_ShopSwipeHQAPIKey" value="' . $_SESSION['ShopSwipeHQAPIKey'] . '" />
			<fieldhelp>' . __('The') . ' <a href="https://www.swipehq.com/credit-card-payment-solutions/index.php">SwipeHQ</a> ' . __('API Key - see SwipeHQ admin settings -> API credentials') . '</fieldhelp>
		</field>';

	echo '</fieldset>';
	echo '</fieldset>';
} //end of blocked inputs in demo mode
echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Update') . '" />
	</div>
	</form>';

include('includes/footer.php');

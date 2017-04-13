<?php

/* $Id: ShopParameters.php 5797 2013-01-26 22:31:34Z daintree $*/

include('includes/session.php');

$Title = _('Shop Configuration');

include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Shop Configuration')
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
		$ErrMsg =  _('The shop configuration could not be updated because');
		$DbgMsg = _('The SQL that failed was:');

		if (sizeof($SQL) > 0 ) {

			$result = DB_Txn_Begin();
			foreach ($SQL as $SqlLine) {
				$result = DB_query($SqlLine,$ErrMsg,$DbgMsg,true);
			}
			$result = DB_Txn_Commit();
			prnMsg( _('Shop configuration updated'),'success');

			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include($PathPrefix . 'includes/GetConfig.php');
			$ForceConfigReload = False;
		}
	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<table cellpadding="2" class="selection" width="98%">';

$TableHeader = '<tr>
					<th>' . _('Shop Configuration Parameter') . '</th>
					<th>' . _('Value') . '</th>
					<th>' . _('Notes') . '</th>
                </tr>';

echo '<tr><th colspan="3">' . _('General Settings') . '</th></tr>';
echo $TableHeader;

echo '<tr>
		<td>' . _('Test or Live Mode') . ':</td>
		<td><select name="X_ShopMode">';
		if ($_SESSION['ShopMode']== 'test' OR $AllowDemoMode){
			echo '<option selected="selected" value="test">' . _('Test') . '</option>
				<option value="live">' . _('Live') . '</option>';
		} else {
			echo '<option value="test">' . _('Test') . '</option>
				<option selected="selected" value="live">' . _('Live') . '</option>';
		}
		echo '</select></td>
		<td>' . _('Must change this to live mode when the shop is activie. No PayPal or credit card transactions will be processed in test mode') . '</td>
	</tr>';
//Shop Name
echo '<tr>
		<td>' . _('Shop Name') . ':</td>
		<td><input type="text" name="X_ShopName" required="required" autofocus="autofocus" size="40" maxlength="40" value="' . $_SESSION['ShopName'] . '" /></td>
		<td>' . _('Enter the name of the shop that will be displayed on all the store pages') . '</td>
	</tr>';

//Shop Title
echo '<tr>
		<td>' . _('Shop Title') . ':</td>
		<td><input type="text" name="X_ShopTitle" required="required" size="40" maxlength="40" value="' . $_SESSION['ShopTitle'] . '" /></td>
		<td>' . _('Enter the title of the shop that will be displayed on the main webSHOP page. Useful for SEO purposes.') . '</td>
	</tr>';

//Shop Manager Email
echo '<tr>
		<td>' . _('Shop Manager Email') . ':</td>
		<td><input type="email" name="X_ShopManagerEmail" required="required" size="50" maxlength="50" value="' . $_SESSION['ShopManagerEmail'] . '" /></td>
		<td>' . _('Enter the email address of the webSHOP manager.') . '</td>
	</tr>';

// Shop Customer
echo '<tr>
		<td>' . _('Default Web Shop Customer Acount') . ':</td>
	   <td><input type="text"size="12" maxlength="10" required="required" name="X_ShopDebtorNo" value="' . $_SESSION['ShopDebtorNo'] . '" /></td>
		<td>' . _('Select the customer account that is to be used for the web-store sales') . '</td>
	</tr>';
// Shop Customer Branch
echo '<tr>
		<td>' . _('Default Web Shop Branch Code').':</td>
		<td><input type="text" required="required" size="12" maxlength="10" name="X_ShopBranchCode" value="' . $_SESSION['ShopBranchCode'] . '" /></td>
		<td>' . _('The customer branch code that is to be used - a branch of the above custoemr account - for web-store sales') . '</td>
	</tr>';

//Privacy Statement
echo '<tr>
		<td>' . _('Privacy Statement') . ':</td>
		<td><textarea name="X_ShopPrivacyStatement" rows="8" cols="60">' . stripslashes($_SESSION['ShopPrivacyStatement']) . '</textarea></td>
		<td>' . _('This text will appear on the web-store page that spells out the privacy policy of the web-shop') . ' ' . _('Enter the raw html without any line breaks') .  '</td>
	</tr>';
//Terms and Conditions
echo '<tr>
		<td>' . _('Terms and Conditions') . ':</td>
		<td><textarea name="X_ShopTermsConditions" rows="8" cols="60">' . stripslashes($_SESSION['ShopTermsConditions']) . '</textarea></td>
		<td>' . _('This text will appear on the web-store page that spells out the terms and conditions associated with sales from the web-shop') . ' ' . _('Enter the raw html without any line breaks') . '</td>
	</tr>';
//About Us
echo '<tr>
		<td>' . _('About Us') . ':</td>
		<td><textarea name="X_ShopAboutUs" rows="8" cols="60">' . stripslashes($_SESSION['ShopAboutUs']) . '</textarea></td>
		<td>' . _('This text will appear on the web-store page that provides information about us to users of the web-store.') . ' ' . _('Enter the raw html without any line breaks')  . '</td>
	</tr>';
echo '<tr>
		<td>' . _('Contact Us') . ':</td>
		<td><textarea name="X_ShopContactUs" rows="8" cols="60">' . stripslashes($_SESSION['ShopContactUs']) . '</textarea></td>
		<td>' . _('This text will appear on the web-store page that provides contact information to users of the web-store.') . ' ' . _('Enter the raw html without any line breaks') . '</td>
	</tr>';
//Freight Policy
echo '<tr>
		<td>' . _('Freight Policy') . ':</td>
		<td><textarea name="X_ShopFreightPolicy" rows="8" cols="60">' . stripslashes($_SESSION['ShopFreightPolicy']) . '</textarea></td>
		<td>' . _('This text will appear on the web-store page that spells out the freight policy of the web-shop') . ' ' . _('Enter the raw html without any line breaks')  . '</td>
	</tr>';


echo '<tr><th colspan="3">' . _('Web-Store Behaviour Settings') . '</th></tr>';
echo $TableHeader;

echo '<tr>
		<td>' . _('Show Only Items With Available Stock') . ':</td>
		<td><select name="X_ShopShowOnlyAvailableItems">';
if ($_SESSION['ShopShowOnlyAvailableItems'] == '1') {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
}
echo '</select></td>
		<td>' . _('Shows only items with QOH > 0 thus avoiding the Arriving Soon items.') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('Show/Hide QOH column') . ':</td>
		<td><select name="X_ShopShowQOHColumn">';
if ($_SESSION['ShopShowQOHColumn'] == '1') {
	echo '<option selected="selected" value="1">' . _('Show') . '</option>';
	echo '<option value="0">' . _('Hide') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . _('Hide') . '</option>';
	echo '<option value="1">' . _('Show') . '</option>';
}
echo '</select></td>
		<td>' . _('Shows / Hides the QOH column Select Hide if you do not want webSHOP visitors to know how many stock do you currently hold.') . '</td>
	</tr>';

if (mb_strlen($_SESSION['ShopStockLocations'])>1){
	$Locations = explode(',',$_SESSION['ShopStockLocations']);
} else {
	$Locations = array();
}
echo '<tr>
		<td>' . _('Stock Locations') . ':</td>
		<td><select name="X_ShopStockLocations[]" size="5" multiple="multiple" >';
$LocResult = DB_query("SELECT loccode, locationname FROM locations");
while ($LocRow = DB_fetch_array($LocResult)){
	if (in_array($LocRow['loccode'],$Locations)){
		echo '<option selected="selected" value="' . $LocRow['loccode'] . '">' . $LocRow['locationname']  . '</option>';
	} else {
		echo '<option value="' . $LocRow['loccode'] . '">' . $LocRow['locationname']  . '</option>';
	}
}
echo '</select></td>
		<td>' . _('Select one or more stock locations (warehouses) that webSHOP should consider stock for the purposes of displaying the on hand quantity for customer information') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('Allow Payment Surcharges') . ':</td>
		<td><select name="X_ShopAllowSurcharges">';
if ($_SESSION['ShopAllowSurcharges'] == '1') {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
}
echo '</select></td>
		<td>' . _('Add surcharges for different payment methods.') . '</td>
	</tr>';

$DummyItemsResult = DB_query("SELECT stockid, description FROM stockmaster WHERE mbflag='D'");
echo '<tr>
		<td>' . _('Surcharges Stock Item') . ':</td>
		<td><select name="X_ShopSurchargeStockID">';
while ($ItemsRow = DB_fetch_array($DummyItemsResult)){
	if ($_SESSION['ShopSurchargeStockID'] ==$ItemsRow['stockid']) {
		echo '<option selected="selected" value="' . $ItemsRow['stockid'] . '">' . $ItemsRow['stockid'] . '-' . $ItemsRow['description'] . '</option>';
	} else {
		echo '<option value="' . $ItemsRow['stockid'] . '">' . $ItemsRow['stockid'] . '-' . $ItemsRow['description'] . '</option>';
	}
}
echo '</select></td>
		<td>' . _('Select the webERP service item to use for payment surcharges to be processed as') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('Freight Calculations') . ':</td>
		<td><select name="X_ShopFreightMethod">';

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
echo '</select></td>
		<td>' . _('Select the freight calculation method to use for the webSHOP') . '</td>
	</tr>';



echo '<tr>
		<th colspan="3">' . _('Bank Transfer Settings') . '</th></tr>';
echo $TableHeader;

echo '<tr>
		<td>' . _('Allow Bank Transfer Payment') . ':</td>
		<td><select name="X_ShopAllowBankTransfer">';
if ($_SESSION['ShopAllowBankTransfer'] ==1) {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
}
echo '</select></td>
		<td>' . _('Allow bank transfers to be used for payments.') . '</td>
	</tr>';
echo '<tr>
		<td>' . _('Bank Transfer Surcharge') . ':</td>
		<td><input type="text" class="number" size="3" maxlength="3" name="X_ShopBankTransferSurcharge" value="' . $_SESSION['ShopBankTransferSurcharge'] . '" /></td>
		<td>' . _('The bank transfer surcharge') . '</td>
	</tr>';

echo '<tr><th colspan="3">' . _('Paypal Settings') . '</th></tr>';
echo $TableHeader;

echo '<tr>
		<td>' . _('Allow PayPal Payment') . ':</td>
		<td><select name="X_ShopAllowPayPal">';
if ($_SESSION['ShopAllowPayPal'] ==1) {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
}
echo '</select></td>
		<td>' . _('Allow PayPal to be used for payments. The configuration details for PayPal payments must be entered below') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('Pay Pal Bank Account') . ':</td>
		<td><select name="X_ShopPayPalBankAccount">';
$BankAccountsResult = DB_query("SELECT accountcode, bankaccountname FROM bankaccounts");
while ($BankAccountRow = DB_fetch_array($BankAccountsResult)){
	if ($_SESSION['ShopPayPalBankAccount'] ==$BankAccountRow['accountcode']) {
		echo '<option selected="selected" value="' . $BankAccountRow['accountcode'] . '">' . $BankAccountRow['bankaccountname'] . '</option>';
	} else {
		echo '<option value="' . $BankAccountRow['accountcode'] . '">' . $BankAccountRow['bankaccountname'] . '</option>';
	}
}
echo '</select></td>
		<td>' . _('Select the webERP bank account to use for receipts processed by Pay Pal') . '</td>
	</tr>';


echo '<tr>
		<td>' . _('Pay Pal Commission Account') . ':</td>
		<td><select name="X_ShopPayPalCommissionAccount">';
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
echo '</select></td>
		<td>' . _('Select the webERP P/L account to use for commissions (transaction fees) charged by Pay Pal') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('PayPal Surcharge') . ':</td>
		<td><input type="text" class="number" size="5" maxlength="5" name="X_ShopPayPalSurcharge" value="' . $_SESSION['ShopPayPalSurcharge'] . '" /></td>
		<td>' . _('The PayPal surcharge') . '</td>
	</tr>';

if ($AllowDemoMode){
	echo '<tr>
			<td>' . _('Paypal user account details') . '</td>
			<td colspan="2">' . _('Cannot be set in the demo') . '</td>
		</tr>';
} else {
	echo '<tr>
			<td>' . _('PayPal User') . ':</td>
			<td><input type="text" class="noSpecialChars" size="40" maxlength="40" name="X_ShopPayPalUser" value="' . $_SESSION['ShopPayPalUser'] . '" /></td>
			<td>' . _('The PayPal Merchant User account for Pay Pal Express Checkout') . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('PayPal Password') . ':</td>
			<td><input type="text" size="20" maxlength="20" name="X_ShopPayPalPassword" value="' . $_SESSION['ShopPayPalPassword'] . '" /></td>
			<td>' . _('The PayPal Merchant account password for Pay Pal Express Checkout') . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('PayPal Signature') . ':</td>
			<td><input type="text" size="80" maxlength="100" name="X_ShopPayPalSignature" value="' . $_SESSION['ShopPayPalSignature'] . '" /></td>
			<td>' . _('The PayPal merchant account signature for Pay Pal Express Checkout') . '</td>
		</tr>';
}

echo '<tr><th colspan="3">' . _('Credit Card Processing Settings') . '</th></tr>';
echo $TableHeader;

echo '<tr>
		<td>' . _('Allow Credit Card Payments') . ':</td>
		<td><select name="X_ShopAllowCreditCards">';
if ($_SESSION['ShopAllowCreditCards'] ==1) {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
} else {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
}
echo '</select></td>
		<td>' . _('Allow Credit Cards to be used for payments. The configuration details for PayPal Pro or one of the other credit card payment solutions must be configured.') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('Credit Card Gateway') . ':</td>
		<td>';
if ($AllowDemoMode) {
	echo '<select name="SomeNameNotUsed">';
} else {
	echo '<select name="X_ShopCreditCardGateway">';
}
if ($_SESSION['ShopCreditCardGateway'] =='PayPalPro') {
	echo '<option selected="selected" value="PayPalPro">' . _('PayPal Pro') . '</option>';
} else {
	echo '<option value="PayPalPro">' . _('PayPal Pro') . '</option>';
}
if ($_SESSION['ShopCreditCardGateway'] =='PayFlow') {
	echo '<option selected="selected" value="PayFlow">' . _('PayFlow Pro') . '</option>';
} else {
	echo '<option value="PayFlow">' . _('PayFlow Pro') . '</option>';
}
if ($_SESSION['ShopCreditCardGateway'] =='SwipeHQ') {
	echo '<option selected="selected" value="SwipeHQ">' . _('Swipe HQ - New Zealand') . '</option>';
} else {
	echo '<option value="SwipeHQ">' . _('Swipe HQ - New Zealand') . '</option>';
}
echo '</select></td>
		<td>' . _('Select the credit card gateway system to be used.') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('Credit Card Surcharge') . ':</td>
		<td><input type="text" class="number" size="5" maxlength="5" name="X_ShopCreditCardSurcharge" value="' . $_SESSION['ShopCreditCardSurcharge'] . '" /></td>
		<td>' . _('The credit card surcharge') . '</td>
	</tr>';

echo '<tr>
		<td>' . _('Credit Card Bank Account') . ':</td>
		<td><select name="X_ShopCreditCardBankAccount">';
DB_data_seek($BankAccountsResult,0);
while ($BankAccountRow = DB_fetch_array($BankAccountsResult)){
	if ($_SESSION['ShopCreditCardBankAccount'] ==$BankAccountRow['accountcode']) {
		echo '<option selected="selected" value="' . $BankAccountRow['accountcode'] . '">' . $BankAccountRow['bankaccountname'] . '</option>';
	} else {
		echo '<option value="' . $BankAccountRow['accountcode'] . '">' . $BankAccountRow['bankaccountname'] . '</option>';
	}
}
echo '</select></td>
		<td>' . _('Select the webERP bank account to use for receipts processed by credit card') . '</td>
	</tr>';



if ($AllowDemoMode){
	echo '<tr>
			<td>' . _('Credit card user account details') . '</td>
			<td colspan="2">' . _('Cannot be set in the demo') . '</td>
		</tr>';
} else {
	echo '<tr>
			<td>' . _('PayPal Pro User') . ':</td>
			<td><input type="text" class="noSpecialChars"  size="40" maxlength="40" name="X_ShopPayPalProUser" value="' . $_SESSION['ShopPayPalProUser'] . '" /></td>
			<td>' . _('The') . '<a href="https://www.paypal.com/us/webapps/mpp/paypal-payments-pro">' . _('PayPal Pro') .'</a> ' .  _('Merchant User account for credit card payment available in only USA and Canada') .  '</td>
		</tr>';

	echo '<tr>
			<td>' . _('PayPal Pro Password') . ':</td>
			<td><input type="text" size="20" maxlength="20" name="X_ShopPayPalProPassword" value="' . $_SESSION['ShopPayPalProPassword'] . '" /></td>
			<td>' . _('The') . '<a href="https://www.paypal.com/us/webapps/mpp/paypal-payments-pro">' . _('PayPal Pro') .'</a> ' . _('Merchant account password for credit card payment available in only USA and Canada') . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('PayPal Pro Signature') . ':</td>
			<td><input type="text" size="80" maxlength="80" name="X_ShopPayPalProSignature" value="' . $_SESSION['ShopPayPalProSignature'] . '" /></td>
			<td>' . _('The') . '<a href="https://www.paypal.com/us/webapps/mpp/paypal-payments-pro">' . _('PayPal Pro') . '</a> ' ._('merchant account signature for credit card payment available in only USA and Canada') . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('Pay Flow Pro User') . ':</td>
			<td><input type="text" class="noSpecialChars"  size="40" maxlength="40" name="X_ShopPayFlowUser" value="' . $_SESSION['ShopPayFlowUser'] . '" /></td>
			<td>' . _('The') . ' <a href="https://www.paypal.com/us/webapps/mpp/payflow-payment-gateway">PayFlow Pro</a> ' . _('Merchant User account') . '</td>
		</tr>';

	echo '<tr>
			<td>' . _('Pay Flow Pro Password') . ':</td>
			<td><input type="text" size="20" maxlength="20" name="X_ShopPayFlowPassword" value="' . $_SESSION['ShopPayFlowPassword'] . '" /></td>
			<td>' . _('The') . ' <a href="https://www.paypal.com/us/webapps/mpp/payflow-payment-gateway">PayFlow Pro</a> ' . _('Merchant account password') . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('Pay Flow Pro Vendor') . ':</td>
			<td><input type="text" class="noSpecialChars" size="20" maxlength="20" name="X_ShopPayFlowVendor" value="' . $_SESSION['ShopPayFlowVendor'] . '" /></td>
			<td>' . _('The') . ' <a href="https://www.paypal.com/us/webapps/mpp/payflow-payment-gateway">PayFlow Pro</a> ' . _('vendor') . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('Pay Flow Pro Merchant') . ':</td>
			<td><input type="text" size="20" maxlength="20" name="X_ShopPayFlowMerchant" value="' . $_SESSION['ShopPayFlowMerchant'] . '" /></td>
			<td>' . _('The') . ' <a href="https://www.paypal.com/us/webapps/mpp/payflow-payment-gateway">PayFlow Pro</a> ' . _('merchant') . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('SwipeHQ Merchant ID') . ':</td>
			<td><input type="text" class="noSpecialChars" size="15" maxlength="15" name="X_ShopSwipeHQMerchantID" value="' . $_SESSION['ShopSwipeHQMerchantID'] . '" /></td>
			<td>' . _('The'). ' <a href="https://www.swipehq.com/credit-card-payment-solutions/index.php">SwipeHQ</a> ' . _('Merchant ID - see SwipeHQ settings -> API credentials') . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('SwipeHQ API Key') . ':</td>
			<td><input type="text" size="80"  maxlenght="100" name="X_ShopSwipeHQAPIKey" value="' . $_SESSION['ShopSwipeHQAPIKey'] . '" /></td>
			<td>' . _('The') . ' <a href="https://www.swipehq.com/credit-card-payment-solutions/index.php">SwipeHQ</a> ' . _('API Key - see SwipeHQ admin settings -> API credentials') . '</td>
		</tr>';
} //end of blocked inputs in demo mode
echo '</table>
		<br /><div class="centre"><input type="submit" name="submit" value="' . _('Update') . '" /></div>
    </div>
	</form>';

include('includes/footer.php');
?>

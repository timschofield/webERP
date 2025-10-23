<?php

/* Utility to change a GL account code in all webERP. */

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE Change A GL Account Code');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeGLAccountCode';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/gl.png" title="',// Icon image.
	__('Change A GL Account Code'), '" /> ',// Icon title.
	__('Change A GL Account Code'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.php');

if(isset($_POST['ProcessGLAccountCode'])) {

	$InputError =0;

	$_POST['NewAccountCode'] = mb_strtoupper($_POST['NewAccountCode']);

/*First check the code exists */
	$Result = DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['OldAccountCode'] . "'");
	if(DB_num_rows($Result)==0) {
		prnMsg(__('The GL account code') . ': ' . $_POST['OldAccountCode'] . ' ' . __('does not currently exist as a GL account code in the system'),'error');
		$InputError =1;
	}

	if(ContainsIllegalCharacters($_POST['NewAccountCode'])) {
		prnMsg(__('The new GL account code to change the old code to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if($_POST['NewAccountCode']=='') {
		prnMsg(__('The new GL account code to change the old code to must be entered as well'),'error');
		$InputError =1;
	}


/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['NewAccountCode'] . "'");
	if(DB_num_rows($Result)!=0) {
		echo '<br /><br />';
		prnMsg(__('The replacement GL account code') . ': ' . $_POST['NewAccountCode'] . ' ' . __('already exists as a GL account code in the system') . ' - ' . __('a unique GL account code must be entered for the new code'),'error');
		$InputError =1;
	}


	if($InputError ==0) {// no input errors
		DB_Txn_Begin();
		echo '<br />' . __('Adding the new chartmaster record');
		$SQL = "INSERT INTO chartmaster (accountcode,
										accountname,
										group_)
				SELECT '" . $_POST['NewAccountCode'] . "',
					accountname,
					group_
				FROM chartmaster
				WHERE accountcode='" . $_POST['OldAccountCode'] . "'";

		$ErrMsg =__('The SQL to insert the new chartmaster record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		DB_IgnoreForeignKeys();

		ChangeFieldInTable("bankaccounts", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("bankaccountusers", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("banktrans", "bankact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("chartdetails", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("cogsglpostings", "glcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("companies", "debtorsact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("companies", "pytdiscountact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("companies", "creditorsact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("companies", "payrollact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("companies", "grnact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("companies", "exchangediffact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("companies", "purchasesexchangediffact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("companies", "retainedearnings", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("companies", "freightact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("fixedassetcategories", "costact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("fixedassetcategories", "depnact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("fixedassetcategories", "disposalact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("fixedassetcategories", "accumdepnact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("glaccountusers", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("gltrans", "account", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("lastcostrollup", "stockact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("lastcostrollup", "adjglact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("locations", "glaccountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);// Location's ledger account.

		ChangeFieldInTable("pcexpenses", "glaccount", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("pctabs", "glaccountassignment", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("pctabs", "glaccountpcash", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("purchorderdetails", "glcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("salesglpostings", "discountglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("salesglpostings", "salesglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("stockcategory", "stockact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("stockcategory", "adjglact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("stockcategory", "issueglact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("stockcategory", "purchpricevaract", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("stockcategory", "materialuseagevarac", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("stockcategory", "wipact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("taxauthorities", "taxglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("taxauthorities", "purchtaxglaccount", $_POST['OldAccountCode'], $_POST['NewAccountCode']);
		ChangeFieldInTable("taxauthorities", "bankacctype", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		ChangeFieldInTable("workcentres", "overheadrecoveryact", $_POST['OldAccountCode'], $_POST['NewAccountCode']);

		DB_ReinstateForeignKeys();

		DB_Txn_Commit();

		echo '<br />' . __('Deleting the old chartmaster record');
		$SQL = "DELETE FROM chartmaster WHERE accountcode='" . $_POST['OldAccountCode'] . "'";
		$ErrMsg = __('The SQL to delete the old chartmaster record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<p>' . __('GL account Code') . ': ' . $_POST['OldAccountCode'] . ' ' . __('was successfully changed to') . ' : ' . $_POST['NewAccountCode'];
	}//only do the stuff above if  $InputError==0

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
	<legend>', __('GEneral Ledger Code To Change'), '</legend>
	<field>
		<label>' . __('Existing GL Account Code') . ':</label>
		<input type="text" name="OldAccountCode" size="20" maxlength="20" />
	</field>
	<field>
		<label>' . __('New GL Account Code') . ':</label>
		<input type="text" name="NewAccountCode" size="20" maxlength="20" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="ProcessGLAccountCode" value="' . __('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');

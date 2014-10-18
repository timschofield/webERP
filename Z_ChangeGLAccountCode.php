<?php

/*Script to change the GL account code in all webERP */

include ('includes/session.inc');
$Title = _('UTILITY PAGE Change A GL Account Code');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['ProcessGLAccountCode'])){

	$InputError =0;

	$_POST['NewAccountCode'] = mb_strtoupper($_POST['NewAccountCode']);

/*First check the code exists */
	$result=DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['OldAccountCode'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('The GL account code') . ': ' . $_POST['OldAccountCode'] . ' ' . _('does not currently exist as a GL account code in the system'),'error');
		$InputError =1;
	}

	if (ContainsIllegalCharacters($_POST['NewAccountCode'])){
		prnMsg(_('The new GL account code to change the old code to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if ($_POST['NewAccountCode']==''){
		prnMsg(_('The new GL account code to change the old code to must be entered as well'),'error');
		$InputError =1;
	}


/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['NewAccountCode'] . "'",$db);
	if (DB_num_rows($result)!=0){
		echo '<br /><br />';
		prnMsg(_('The replacement GL account code') . ': ' . $_POST['NewAccountCode'] . ' ' . _('already exists as a GL account code in the system') . ' - ' . _('a unique GL account code must be entered for the new code'),'error');
		$InputError =1;
	}


	if ($InputError ==0){ // no input errors
		$result = DB_Txn_Begin($db);
		echo '<br />' . _('Adding the new chartmaster record');
		$sql = "INSERT INTO chartmaster (accountcode,
										accountname,
										group_)
				SELECT '" . $_POST['NewAccountCode'] . "',
					accountname,
					group_
				FROM chartmaster
				WHERE accountcode='" . $_POST['OldAccountCode'] . "'";

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to insert the new chartmaster record failed');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		DB_IgnoreForeignKeys($db);

		ChangeFieldInTable("bankaccounts", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("banktrans", "bankact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("chartdetails", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("cogsglpostings", "glcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("companies", "debtorsact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "pytdiscountact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "creditorsact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "payrollact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "grnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "exchangediffact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "purchasesexchangediffact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "retainedearnings", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "freightact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("fixedassetcategories", "costact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "depnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "disposalact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "accumdepnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("gltrans", "account", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("lastcostrollup", "stockact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("lastcostrollup", "adjglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("pcexpenses", "glaccount", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("pctabs", "glaccountassignment", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("pctabs", "glaccountpcash", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("purchorderdetails", "glcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("salesglpostings", "discountglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("salesglpostings", "salesglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("stockcategory", "stockact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "adjglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "issueglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "purchpricevaract", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "materialuseagevarac", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "wipact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("taxauthorities", "taxglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("taxauthorities", "purchtaxglaccount", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("taxauthorities", "bankacctype", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("workcentres", "overheadrecoveryact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		DB_ReinstateForeignKeys($db);

		$result = DB_Txn_Commit($db);

		echo '<br />' . _('Deleting the old chartmaster record');
		$sql = "DELETE FROM chartmaster WHERE accountcode='" . $_POST['OldAccountCode'] . "'";
		$ErrMsg = _('The SQL to delete the old chartmaster record failed');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');


		echo '<p>' . _('GL account Code') . ': ' . $_POST['OldAccountCode'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewAccountCode'];
	} //only do the stuff above if  $InputError==0

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
    <table>
	<tr>
		<td>' . _('Existing GL Account Code') . ':</td>
		<td><input type="text" name="OldAccountCode" size="20" maxlength="20" /></td>
	</tr>
	<tr>
		<td>' . _('New GL Account Code') . ':</td>
		<td><input type="text" name="NewAccountCode" size="20" maxlength="20" /></td>
	</tr>
	</table>

		<input type="submit" name="ProcessGLAccountCode" value="' . _('Process') . '" />
	</div>
	</form>';

include('includes/footer.inc');


?>
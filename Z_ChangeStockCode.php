<?php

/* $Id: Z_ChangeStockCode.php 6067 2013-07-10 02:04:22Z tehonu $*/

include ('includes/session.inc');
$Title = _('UTILITY PAGE Change A Stock Code');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['ProcessStockChange'])){

	$InputError =0;

	$_POST['NewStockID'] = mb_strtoupper($_POST['NewStockID']);

/*First check the stock code exists */
	$result=DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('The stock code') . ': ' . $_POST['OldStockID'] . ' ' . _('does not currently exist as a stock code in the system'),'error');
		$InputError =1;
	}

	if (ContainsIllegalCharacters($_POST['NewStockID'])){
		prnMsg(_('The new stock code to change the old code to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if ($_POST['NewStockID']==''){
		prnMsg(_('The new stock code to change the old code to must be entered as well'),'error');
		$InputError =1;
	}


/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['NewStockID'] . "'",$db);
	if (DB_num_rows($result)!=0){
		echo '<br /><br />';
		prnMsg(_('The replacement stock code') . ': ' . $_POST['NewStockID'] . ' ' . _('already exists as a stock code in the system') . ' - ' . _('a unique stock code must be entered for the new code'),'error');
		$InputError =1;
	}


	if ($InputError ==0){ // no input errors
		$result = DB_Txn_Begin($db);
		echo '<br />' . _('Adding the new stock master record');
		$sql = "INSERT INTO stockmaster (stockid,
										categoryid,
										description,
										longdescription,
										units,
										mbflag,
										actualcost,
										lastcost,
										materialcost,
										labourcost,
										overheadcost,
										lowestlevel,
										discontinued,
										controlled,
										eoq,
										volume,
										grossweight,
										barcode,
										discountcategory,
										taxcatid,
										decimalplaces,
										shrinkfactor,
										pansize,
										netweight,
										perishable,
										nextserialno)
				SELECT '" . $_POST['NewStockID'] . "',
					categoryid,
					description,
					longdescription,
					units,
					mbflag,
					actualcost,
					lastcost,
					materialcost,
					labourcost,
					overheadcost,
					lowestlevel,
					discontinued,
					controlled,
					eoq,
					volume,
					grossweight,
					barcode,
					discountcategory,
					taxcatid,
					decimalplaces,
					shrinkfactor,
					pansize,
					netweight,
					perishable,
					nextserialno
				FROM stockmaster
				WHERE stockid='" . $_POST['OldStockID'] . "'";

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to insert the new stock master record failed');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		ChangeFieldInTable("locstock", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("stockmoves", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("loctransfers", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("mrpdemands", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);

		//check if MRP tables exist before assuming

		$result = DB_query("SELECT COUNT(*) FROM mrpplannedorders",$db,'','',false,false);
		if (DB_error_no($db)==0) {
			ChangeFieldInTable("mrpplannedorders", "part", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		}

		$result = DB_query("SELECT * FROM mrprequirements" , $db,'','',false,false);
		if (DB_error_no($db)==0){
			ChangeFieldInTable("mrprequirements", "part", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		}
		
		$result = DB_query("SELECT * FROM mrpsupplies" , $db,'','',false,false);
		if (DB_error_no($db)==0){
			ChangeFieldInTable("mrpsupplies", "part", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		}

		ChangeFieldInTable("salesanalysis", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("orderdeliverydifferenceslog", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("prices", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("salesorderdetails", "stkcode", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("purchorderdetails", "itemcode", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("purchdata", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("shipmentcharges", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("stockcheckfreeze", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("stockcounts", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("grns", "itemcode", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("contractbom", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("bom", "component", $_POST['OldStockID'], $_POST['NewStockID'], $db);

		DB_IgnoreForeignKeys($db);

		ChangeFieldInTable("bom", "parent", $_POST['OldStockID'], $_POST['NewStockID'], $db);

		echo '<br />' . _('Changing any image files');
		if (file_exists($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'.jpg')){
			if (rename($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'.jpg',
				$_SESSION['part_pics_dir'] . '/' .$_POST['NewStockID'].'.jpg')) {
				echo ' ... ' . _('completed');
			} else {
				echo ' ... ' . _('failed');
			}
		} else {
			echo ' .... ' . _('no image to rename');
		}

		ChangeFieldInTable("stockitemproperties", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("worequirements", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("worequirements", "parentstockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("woitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("salescatprod", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("stockserialitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("stockserialmoves", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("offers", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);
		ChangeFieldInTable("tenderitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID'], $db);

		DB_ReinstateForeignKeys($db);

		$result = DB_Txn_Commit($db);

		echo '<br />' . _('Deleting the old stock master record');
		$sql = "DELETE FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'";
		$ErrMsg = _('The SQL to delete the old stock master record failed');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<p>' . _('Stock Code') . ': ' . $_POST['OldStockID'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewStockID'];
	} //only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
    <table>
	<tr>
		<td>' . _('Existing Inventory Code') . ':</td>
		<td><input type="text" name="OldStockID" size="20" maxlength="20" /></td>
	</tr>
	<tr>
		<td>' . _('New Inventory Code') . ':</td>
		<td><input type="text" name="NewStockID" size="20" maxlength="20" /></td>
	</tr>
	</table>

		<input type="submit" name="ProcessStockChange" value="' . _('Process') . '" />
	</div>
	</form>';

include('includes/footer.inc');

?>
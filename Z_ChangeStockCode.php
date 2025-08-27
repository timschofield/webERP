<?php

/*	This script is an utility to change an inventory item code.
	It uses function ChangeFieldInTable($TableName, $FieldName, $OldValue,
	$NewValue) from .../includes/MiscFunctions.php.*/

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE Change A Stock Code');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeStockCode';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/inventory.png" title="' .
	__('Change An Inventory Item Code') . '" /> ' .// Icon title.
	__('Change An Inventory Item Code') . '</p>';// Page title.

if (isset($_POST['ProcessStockChange'])){

	$InputError =0;

	$_POST['NewStockID'] = mb_strtoupper($_POST['NewStockID']);

/*First check the stock code exists */
	$Result = DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'");
	if (DB_num_rows($Result)==0){
		prnMsg(__('The stock code') . ': ' . $_POST['OldStockID'] . ' ' . __('does not currently exist as a stock code in the system'),'error');
		$InputError =1;
	}

	if (ContainsIllegalCharacters($_POST['NewStockID'])){
		prnMsg(__('The new stock code to change the old code to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if ($_POST['NewStockID']==''){
		prnMsg(__('The new stock code to change the old code to must be entered as well'),'error');
		$InputError =1;
	}


/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['NewStockID'] . "'");
	if (DB_num_rows($Result)!=0){
		echo '<br /><br />';
		prnMsg(__('The replacement stock code') . ': ' . $_POST['NewStockID'] . ' ' . __('already exists as a stock code in the system') . ' - ' . __('a unique stock code must be entered for the new code'),'error');
		$InputError =1;
	}


	if ($InputError ==0){ // no input errors

		DB_IgnoreForeignKeys();
        DB_Txn_Begin();
		echo '<br />' . __('Adding the new stock master record');
		$SQL = "INSERT INTO stockmaster (stockid,
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
										serialised,
										perishable,
										decimalplaces,
										pansize,
										shrinkfactor,
										nextserialno,
										netweight,
										lastcostupdate)
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
					serialised,
					perishable,
					decimalplaces,
					pansize,
					shrinkfactor,
					nextserialno,
					netweight,
					lastcostupdate
				FROM stockmaster
				WHERE stockid='" . $_POST['OldStockID'] . "'";

		$ErrMsg =__('The SQL to insert the new stock master record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		ChangeFieldInTable("locstock", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockmoves", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("loctransfers", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("mrpdemands", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);

		//check if MRP tables exist before assuming
		$SQL = "SELECT * FROM mrpparameters";
		$Result = DB_query($SQL, '', '', false, false);
		if (DB_error_no() == 0) {
			$Result = DB_query("SELECT COUNT(*) FROM mrpplannedorders",'','',false,false);
			if (DB_error_no()==0) {
				ChangeFieldInTable("mrpplannedorders", "part", $_POST['OldStockID'], $_POST['NewStockID']);
			}

			$Result = DB_query("SELECT * FROM mrprequirements" ,'','',false,false);
			if (DB_error_no()==0){
				ChangeFieldInTable("mrprequirements", "part", $_POST['OldStockID'], $_POST['NewStockID']);
			}

			$Result = DB_query("SELECT * FROM mrpsupplies" ,'','',false,false);
			if (DB_error_no()==0){
				ChangeFieldInTable("mrpsupplies", "part", $_POST['OldStockID'], $_POST['NewStockID']);
			}
		}
		ChangeFieldInTable("salesanalysis", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("orderdeliverydifferenceslog", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("prices", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("salesorderdetails", "stkcode", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("purchorderdetails", "itemcode", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("purchdata", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("shipmentcharges", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockcheckfreeze", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockcounts", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("grns", "itemcode", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("contractbom", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("bom", "component", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("bom", "parent", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockrequestitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockdescriptiontranslations", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);// Updates the translated item titles (StockTitles)
		ChangeFieldInTable("custitem", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("pricematrix", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("pickreqdetails", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);

		echo '<br />' . __('Changing any image files');
		$SupportedImgExt = array('png','jpg','jpeg');
		foreach ($SupportedImgExt as $Ext) {
			$File = $_SESSION['part_pics_dir'] . '/' . $_POST['OldStockID'] . '.' . $Ext;
			if (file_exists ($File) ) {
				if (rename($File,
					$_SESSION['part_pics_dir'] . '/' .$_POST['NewStockID'] . '.' . $Ext)) {
					echo ' ... ' . __('completed');
				} else {
					echo ' ... ' . __('failed');
				}
			} else {
				echo ' .... ' . __('no image to rename');
			}
		}

		ChangeFieldInTable("stockitemproperties", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("worequirements", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("worequirements", "parentstockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("woitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("salescatprod", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockserialitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockserialmoves", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("offers", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("tenderitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("prodspecs", "keyval", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("qasamples", "prodspeckey", $_POST['OldStockID'], $_POST['NewStockID']);

		DB_ReinstateForeignKeys();

		DB_Txn_Commit();

		echo '<br />' . __('Deleting the old stock master record');
		$SQL = "DELETE FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'";
		$ErrMsg = __('The SQL to delete the old stock master record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');
		echo '<p>' . __('Stock Code') . ': ' . $_POST['OldStockID'] . ' ' . __('was successfully changed to') . ' : ' . $_POST['NewStockID'];

		// If the current SelectedStockItem is the same as the OldStockID, it updates to the NewStockID:
		if ($_SESSION['SelectedStockItem'] == $_POST['OldStockID']) {
			$_SESSION['SelectedStockItem'] = $_POST['NewStockID'];
		}

	} //only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
	<legend>', __('Stock Item To Change'), '</legend>
	<field>
		<label>' . __('Existing Inventory Code') . ':</label>
		<input type="text" name="OldStockID" size="20" maxlength="20" />
	</field>
	<field>
		<label>' . __('New Inventory Code') . ':</label>
		<input type="text" name="NewStockID" size="20" maxlength="20" />
	</field>
	</fieldset>

	<div class="centre">
		<input type="submit" name="ProcessStockChange" value="' . __('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');

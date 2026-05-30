<?php

/*	This script is an utility to change an inventory item code.
	It uses function ChangeFieldInTable($TableName, $FieldName, $OldValue,
	$NewValue) from .../includes/MiscFunctions.php.*/

/**************************************************************************************
KL RICARD MODIFICATIONS:
- change the stock code also in KL tables using this field in webERP and OpenCart (function ChangeFieldInOpenCartTable at bottom of this script)
***************************************************************************************/

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
/* RICARD KL: Added lastcategoryupdate and kl*** fields, and dimension fields */	
		echo '<br />' . __('Adding the new stock master record');
		$SQL = "INSERT INTO stockmaster (stockid,
										categoryid,
										description,
										longdescription,
										units,
										mbflag,
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
										lastcostupdate,
										lastcategoryupdate,
										length,
										width,
										height,
										unitsdimension,
										klsynctoopencart,
										klservicebyreplacement,
										klchangingprice,
										klmovingdiscount20,
										klmovingdiscount50,
										klmovingdiscount80)
				SELECT '" . $_POST['NewStockID'] . "',
					categoryid,
					description,
					longdescription,
					units,
					mbflag,
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
					lastcostupdate,
					lastcategoryupdate,
					length,
					width,
					height,
					unitsdimension,
					klsynctoopencart,
					klservicebyreplacement,
					klchangingprice,
					klmovingdiscount20,
					klmovingdiscount50,
					klmovingdiscount80
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
		// KL RICARD
		DB_IgnoreForeignKeys();
		// KL RICARD END
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

		/* KL RICARD TABLES */
		ChangeFieldInTable("kladjustrl", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klchangeprice", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klconsignment", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klfreeexchanges", "itemfrom", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klfreeexchanges", "itemto", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klmovetodiscount20", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klmovetodiscount50", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klmovetodiscount80", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("relateditems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("relateditems", "related", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klsalesperformance", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("klstockmarketplaces", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);

		include('includes/OCOpenCartConnectDB.php');
		ChangeFieldInOpenCartTable( "oc_product", "model", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInOpenCartTable( "oc_product", "sku", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInOpenCartTable( "oc_product", "mpn", $_POST['OldStockID'], $_POST['NewStockID']);
		
		/* END OF KL TABLES */

		DB_ReinstateForeignKeys();

		echo '<br />' . __('Deleting the old stock master record');
		$SQL = "DELETE FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'";
		$ErrMsg = __('The SQL to delete the old stock master record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		if (!$Result) {
			DB_Txn_Rollback();
			echo ' ... ' . __('failed');
			echo '<p>' . __('Stock Code change to') . ': ' . $_POST['NewStockID'] . ' ' . __('failed.');
		}else {
			DB_Txn_Commit();
			echo ' ... ' . __('completed');
			echo '<p>' . __('Stock Code') . ': ' . $_POST['OldStockID'] . ' ' . __('was successfully changed to') . ' : ' . $_POST['NewStockID'];
		}


		// If the current SelectedStockItem is the same as the OldStockID, it updates to the NewStockID:
		if (isset($_SESSION['SelectedStockItem']) AND $_SESSION['SelectedStockItem'] == $_POST['OldStockID']) {
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

// KL RICARD
function ChangeFieldInOpenCartTable($TableName, $FieldName, $OldValue, $NewValue){
	/* Used in Z_ scripts to change one field across the table.
	*/
	echo '<br />' . __('Changing OPENCART') . ' ' . $TableName . ' ' . __('records');
	$SQL = "UPDATE " . $TableName . " SET " . $FieldName . " ='" . $NewValue . "' WHERE " . $FieldName . "='" . $OldValue . "'";
	$ErrMsg = __('The SQL to update' . ' ' . $TableName . ' ' . __('records failed'));
	$Result = DB_query_oc($SQL,$ErrMsg,'',true);
	echo ' ... ' . __('completed');
}
// KL RICARD END


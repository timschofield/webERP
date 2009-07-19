<?php
/* $Revision: 1.17 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('UTILITY PAGE Change A Stock Code');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['ProcessStockChange'])){

	$_POST['NewStockID'] = strtoupper($_POST['NewStockID']);

/*First check the stock code exists */
	$result=DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('The stock code') . ': ' . $_POST['OldStockID'] . ' ' . _('does not currently exist as a stock code in the system'),'error');
		include('includes/footer.inc');
		exit;
	}

	if (ContainsIllegalCharacters($_POST['NewStockID'])){
		prnMsg(_('The new stock code to change the old code to contains illegal characters - no changes will be made'),'error');
		include('includes/footer.inc');
		exit;
	}

	if ($_POST['NewStockID']==''){
		prnMsg(_('The new stock code to change the old code to must be entered as well'),'error');
		include('includes/footer.inc');
		exit;
	}


/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['NewStockID'] . "'",$db);
	if (DB_num_rows($result)!=0){
		echo '<br><br>';
		prnMsg(_('The replacement stock code') . ': ' . $_POST['NewStockID'] . ' ' . _('already exists as a stock code in the system') . ' - ' . _('a unique stock code must be entered for the new code'),'error');
		include('includes/footer.inc');
		exit;
	}



	$result = DB_Txn_Begin($db);

	echo '<br>' . _('Adding the new stock master record');
	$sql = "INSERT INTO stockmaster (stockid,
					categoryid,
					description,
					longdescription,
					units,
					mbflag,
					lastcurcostdate,
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
					kgs,
					barcode,
					discountcategory,
					taxcatid)
			SELECT '" . $_POST['NewStockID'] . "',
				categoryid,
				description,
				longdescription,
				units,
				mbflag,
				lastcurcostdate,
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
				kgs,
				barcode,
				discountcategory,
				taxcatid
			FROM stockmaster
			WHERE stockid='" . $_POST['OldStockID'] . "'";

	$DbgMsg = _('The SQL statement that failed was');
	$ErrMsg =_('The SQL to insert the new stock master record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<br>' . _('Changing stock location records');
	$sql = "UPDATE locstock SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update stock location records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<br>' . _('Changing stock movement records');
	$sql = "UPDATE stockmoves SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update stock movement transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<br>' . _('Changing sales analysis records');
	$sql = "UPDATE salesanalysis SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update Sales Analysis records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing order delivery differences records');
	$sql = "UPDATE orderdeliverydifferenceslog SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update order delivery differences records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing pricing records');
	$sql = "UPDATE prices SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg =  _('The SQL to update the pricing records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing sales orders detail records');
	$sql = "UPDATE salesorderdetails SET stkcode='" . $_POST['NewStockID'] . "' WHERE stkcode='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the sales order header records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing purchase order details records');
	$sql = "UPDATE purchorderdetails SET itemcode='" . $_POST['NewStockID'] . "' WHERE itemcode='" . $_POST['OldStockID'] . "'";
	$ErrMsg =  _('The SQL to update the purchase order detail records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing purchasing data records');
	$sql = "UPDATE purchdata SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the purchasing data records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<br>' . _('Changing the stock code in shipment charges records');
	$sql = "UPDATE shipmentcharges SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update Shipment Charges records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<br>' . _('Changing the stock check freeze file records');
	$sql = "UPDATE stockcheckfreeze SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update stock check freeze records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<br>' . _('Changing the stock counts table records');
	$sql = "UPDATE stockcounts SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg =  _('The SQL to update stock counts records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing the GRNs table records');
	$sql = "UPDATE grns SET itemcode='" . $_POST['NewStockID'] . "' WHERE itemcode='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update GRN records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing the contract BOM table records');
	$sql = "UPDATE contractbom SET component='" . $_POST['NewStockID'] . "' WHERE component='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to contract BOM records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing the BOM table records') . ' - ' . _('components');
	$sql = "UPDATE bom SET component='" . $_POST['NewStockID'] . "' WHERE component='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the BOM records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<br>' . _('Changing the BOM table records') . ' - ' . _('parents');
	$sql = "UPDATE bom SET parent='" . $_POST['NewStockID'] . "' WHERE parent='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the BOM parent records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<br>' . _('Changing any image files');
	if (file_exists($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'.jpg')) {
		if (rename($_SESSION['part_pics_dir'] . '/' .$_POST['OldStockID'].'.jpg', 
			$_SESSION['part_pics_dir'] . '/' .$_POST['NewStockID'].'.jpg')) {
				echo ' ... ' . _('completed');
			} else {
				echo ' ... ' . _('failed');
			}
	} else {
		echo ' ... ' . _('completed');		
	}

	echo '<br>' . _('Changing the item properties table records') . ' - ' . _('parents');
	$sql = "UPDATE stockitemproperties SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the item properties records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<BR>' . _('Changing work order information');

	$sql = "UPDATE woitems SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the woitem records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<br>' . _('Changing any serialised item information');

	$sql = 'SET FOREIGN_KEY_CHECKS=0';
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	$sql = "UPDATE stockserialitems SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the stockserialitem records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE stockserialmoves SET stockid='" . $_POST['NewStockID'] . "' WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the stockserialitem records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	$sql = 'SET FOREIGN_KEY_CHECKS=1';
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	$result = DB_Txn_Commit($db);

	echo '<br>' . _('Deleting the old stock master record');
	$sql = "DELETE FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to delete the old stock master record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<p>' . _('Stock Code') . ': ' . $_POST['OldStockID'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewStockID'];

}

echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>";

echo '<p><table>
	<tr><td>' . _('Existing Inventory Code') . ":</td>
	<td><input type=Text name='OldStockID' size=20 maxlength=20></td></tr>";

echo '<tr><td>' . _('New Inventory Code') . ":</td><td><input type=Text name='NewStockID' size=20 maxlength=20></td></tr>";
echo '</table>';

echo "<input type=submit name='ProcessStockChange' VALUE='" . _('Process') . "'>";

echo '</form>';

include('includes/footer.inc');
?>
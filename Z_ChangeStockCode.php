<?php
/* $Revision: 1.5 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('UTILITY PAGE To Change A Stock Code In All Tables');
include('includes/header.inc');

if (isset($_POST['ProcessStockChange'])){

/*First check the stock code exists */
	$result=DB_query("SELECT StockID FROM StockMaster WHERE StockID='" . $_POST['OldStockID'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('The stock code') . ': ' . $_POST['OldStockID'] . ' ' . _('does not currently exist as a stock code in the system'),'error');
		include('includes/footer.inc');
		exit;
	}


	if ($_POST['NewStockID']==''){
		echo '<BR><BR>';
		prnMsg(-('The new stock code to change the old code to must be entered as well'),'error');
		include('includes/footer.inc');
		exit;
	}
/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT StockID FROM StockMaster WHERE StockID='" . $_POST['NewStockID'] . "'",$db);
	if (DB_num_rows($result)!=0){
		echo '<BR><BR>';
		prnMsg(_('The replacement stock code') . ': ' . $_POST['NewStockID'] . ' ' . _('already exists as a stock code in the system - a unique stock code must be entered for the new code'),'error');
		include('includes/footer.inc');
		exit;
	}

	$result = DB_query('BEGIN',$db);

	echo '<BR>' . _('Adding the new stock master record');
	$sql = "INSERT INTO StockMaster (`StockID`,
					`CategoryID`,
					`Description`,
					`LongDescription`,
					`Units`,
					`MBflag`,
					`LastCurCostDate`,
					`ActualCost`,
					`LastCost`,
					`Materialcost`,
					`Labourcost`,
					`Overheadcost`,
					`lowestlevel`,
					`Discontinued`,
					`Controlled`,
					`EOQ`,
					`Volume`,
					`KGS`,
					`BarCode`,
					`DiscountCategory`,
					`TaxLevel`)
			SELECT '" . $_POST['NewStockID'] . "',
				`CategoryID`,
				`Description`,
				`LongDescription`,
				`Units`,
				`MBflag`,
				`LastCurCostDate`,
				`ActualCost`,
				`LastCost`,
				`Materialcost`,
				`Labourcost`,
				`Overheadcost`,
				`lowestlevel`,
				`Discontinued`,
				`Controlled`,
				`EOQ`,
				`Volume`,
				`KGS`,
				`BarCode`,
				`DiscountCategory`,
				`TaxLevel`
			FROM StockMaster
			WHERE StockID='" . $_POST['OldStockID'] . "'";

	$DbgMsg = _('The SQL statement that failed was');
	$ErrMsg =_('The SQL to insert the new stock master record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<BR>' . _('Changing stock location records');
	$sql = "UPDATE LocStock SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update stock location records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<BR>' . _('Changing stock movement records');
	$sql = "UPDATE StockMoves SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update stock movement transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<BR>' . _('Changing sales analysis records');
	$sql = "UPDATE SalesAnalysis SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update Sales Analysis records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing order delivery differences records');
	$sql = "UPDATE OrderDeliveryDifferencesLog SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update order delivery differences records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing pricing records');
	$sql = "UPDATE Prices SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$ErrMsg = '<BR>' . _('The SQL to update the pricing records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing sales orders detail records');
	$sql = "UPDATE SalesOrderDetails SET StkCode='" . $_POST['NewStockID'] . "' WHERE StkCode='" . $_POST['OldStockID'] . "'";
	$ErrMsg = '<BR>' . _('The SQL to update the sales order header records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing purchase order details records');
	$sql = "UPDATE PurchOrderDetails SET ItemCode='" . $_POST['NewStockID'] . "' WHERE ItemCode='" . $_POST['OldStockID'] . "'";
	$ErrMsg = '<BR>' . _('The SQL to update the purchase order detail records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing purchasing data records');
	$sql = "UPDATE PurchData SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	echo '<BR>' . _('The SQL to update the purchasing data records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<BR>' . _('Changing the stock code in shipment charges records');
	$sql = "UPDATE ShipmentCharges SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	echo '<BR>' . _('The SQL to update Shipment Charges records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<BR>' . _('Changing the stock check freeze file records');
	$sql = "UPDATE StockCheckFreeze SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	echo '<BR>' . _('The SQL to update stock check freeze records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	echo '<BR>' . _('Changing the stock counts table records');
	$sql = "UPDATE StockCounts SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$ErrMsg = '<BR>' . _('The SQL to update stock counts records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing the GRNs table records');
	$sql = "UPDATE GRNs SET ItemCode='" . $_POST['NewStockID'] . "' WHERE ItemCode='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update GRN records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing the contract BOM table records');
	$sql = "UPDATE ContractBOM SET Component='" . $_POST['NewStockID'] . "' WHERE Component='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to contract BOM records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing the BOM table records - components');
	$sql = "UPDATE BOM SET Component='" . $_POST['NewStockID'] . "' WHERE Component='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the BOM records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<BR>' . _('Changing the BOM table records - parents');
	$sql = "UPDATE BOM SET Parent='" . $_POST['NewStockID'] . "' WHERE Parent='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to update the BOM parent records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	$result = DB_query('COMMIT',$db);

	echo '<BR>' . _('Deleting the old stock master record');
	$sql = "DELETE FROM StockMaster WHERE StockID='" . $_POST['OldStockID'] . "'";
	$ErrMsg = _('The SQL to delete the old stock master record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');


	echo '<P>' . _('Stock Code') . ': ' . $_POST['OldStockCode'] . ' ' . _('was sucessfully changed to') . ' : ' . $_POST['NewStockID'];

}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' METHOD=POST>";

echo '<P><CENTER><TABLE>
	<TR><TD>' . _('Existing Inventory Code') . ":</TD>
	<TD><INPUT TYPE=Text NAME='OldStockID' SIZE=20 MAXLENGTH=20></TD></TR>";

echo '<TR><TD>' . _('New Inventory Code') . ":</TD><TD><INPUT TYPE=Text NAME='NewStockID' SIZE=20 MAXLENGTH=20></TD></TR>";
echo '</TABLE>';

echo "<INPUT TYPE=SUBMIT NAME='ProcessStockChange' VALUE='" . _('Process') . "'>";

echo '</FORM>';

include('includes/footer.inc');
?>

<?php

/*Script to Delete all sales transactions*/

$title = "UTILITY PAGE To Change A Stock Code In All Tables";
$PageSecurity=15;
include ("includes/session.inc");
include("includes/header.inc");

if (isset($_POST['ProcessStockChange'])){

/*First check the stock code exists */
	$result=DB_query("SELECT StockID FROM StockMaster WHERE StockID='" . $_POST['OldStockID'] . "'",$db);
	if (DB_num_rows($result)==0){
		echo "<BR><BR>The stock code: " . $_POST['OldStockID'] . " does not currently exist as a stock code in the system";
		exit;
	}


	if ($_POST['NewStockID']==""){
		echo "<BR><BR>The new stock code to change the old code to must be entered as well!!";
		exit;
	}
/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT StockID FROM StockMaster WHERE StockID='" . $_POST['NewStockID'] . "'",$db);
	if (DB_num_rows($result)!=0){
		echo "<BR><BR>The replacement stock code: " . $_POST['NewStockID'] . " already exists as a stock code in the system - a unique stock code must be entered for the new code.";
		exit;
	}


	echo "<BR>Changing the stock master record";
	$sql = "UPDATE StockMaster SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update stock master record failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}
	echo "<BR>Changing stock location records";
	$sql = "UPDATE LocStock SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update stock location records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}
	echo "<BR>Changing stock movement records";
	$sql = "UPDATE StockMoves SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update stock movement transaction records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}
	echo "<BR>Changing sales analysis records";
	$sql = "UPDATE SalesAnalysis SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update Sales Analysis records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}


	echo "<BR>Changing order delivery differences records";
	$sql = "UPDATE OrderDeliveryDifferencesLog SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update order delivery differences records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<BR>Changing pricing records";
	$sql = "UPDATE Prices SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the pricing records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<BR>Changing sales orders detail records";
	$sql = "UPDATE SalesOrderDetails SET StkCode='" . $_POST['NewStockID'] . "' WHERE StkCode='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the sales order header records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<BR>Changing purchase order details records";
	$sql = "UPDATE PurchOrderDetails SET ItemCode='" . $_POST['NewStockID'] . "' WHERE ItemCode='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the purchase order detail records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<BR>Changing purchasing data records";
	$sql = "UPDATE PurchData SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the purchasing data records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}
	echo "<BR>Changing the stock code in shipment charges records";
	$sql = "UPDATE ShipmentCharges SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update Shipment Charges records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}
	echo "<BR>Changing the stock check freeze file records";
	$sql = "UPDATE StockCheckFreeze SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update stock check freeze records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}
	echo "<BR>Changing the stock counts table records";
	$sql = "UPDATE StockCounts SET StockID='" . $_POST['NewStockID'] . "' WHERE StockID='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update stock counts records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<BR>Changing the GRNs table records";
	$sql = "UPDATE GRNs SET ItemCode='" . $_POST['NewStockID'] . "' WHERE ItemCode='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update GRN records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<BR>Changing the contract BOM table records";
	$sql = "UPDATE ContractBOM SET Component='" . $_POST['NewStockID'] . "' WHERE Component='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to contract BOM records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<BR>Changing the BOM table records - components";
	$sql = "UPDATE BOM SET Component='" . $_POST['NewStockID'] . "' WHERE Component='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the BOM records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<BR>Changing the BOM table records - parents";
	$sql = "UPDATE BOM SET Parent='" . $_POST['NewStockID'] . "' WHERE Parent='" . $_POST['OldStockID'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the BOM parent records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	} else {
		echo " ... done.";
	}

	echo "<P>Stock Code: " . $_POST['OldStockCode'] . " was sucessfully changed to : " . $_POST['NewStockID'];

}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' METHOD=POST>";

?>
<P><CENTER><TABLE>
<TR><TD>Existing Inventory Code:</TD><TD><INPUT TYPE=Text NAME='OldStockID' SIZE=20 MAXLENGTH=20></TD></TR>
<TR><TD>New Inventory Code:</TD><TD><INPUT TYPE=Text NAME='NewStockID' SIZE=20 MAXLENGTH=20></TD></TR>
</TABLE>

<INPUT TYPE=SUBMIT NAME='ProcessStockChange' VALUE='Process'>

</FORM>

<?php include("includes/footer.inc"); ?>

<?php
/* $Revision: 1.3 $ */
/*Script to Delete all sales transactions*/

$title = "UTILITY PAGE To Changes A Customer Code In All Tables";
$PageSecurity=15;
include ("includes/session.inc");
include("includes/header.inc");

if (isset($_POST['ProcessCustomerChange'])){

/*First check the customer code exists */
	$result=DB_query("SELECT DebtorNo FROM DebtorsMaster WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		echo "<BR><BR>The customer code: " . $_POST['OldDebtorNo'] . " does not currently exist as a customer code in the system";
		exit;
	}


	if ($_POST['NewDebtorNo']==""){
		echo "<BR><BR>The new customer code to change the old code to must be entered as well!!";
		exit;
	}
/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT DebtorNo FROM DebtorsMaster WHERE DebtorNo='" . $_POST['NewDebtorNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		echo "<BR><BR>The replacement customer code: " . $_POST['NewDebtorNo'] . " already exists as a customer code in the system - a unique customer code must be entered for the new code.";
		exit;
	}


	echo "<BR>Changing the debtor master record";
	$sql = "UPDATE DebtorsMaster SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update debtors master record failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}
	echo "<BR>Changing customer branch records";
	$sql = "UPDATE CustBranch SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update customer branch records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}
	echo "<BR>Changing debtor transaction records";
	$sql = "UPDATE DebtorTrans SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update debtor transaction records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}
	echo "<BR>Changing sales analysis records";
	$sql = "UPDATE SalesAnalysis SET Cust='" . $_POST['NewDebtorNo'] . "' WHERE Cust='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update Sales Analysis records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}


	echo "<BR>Changing order delivery differences records";
	$sql = "UPDATE OrderDeliveryDifferencesLog SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update order delivery differences records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	echo "<BR>Changing pricing records";
	$sql = "UPDATE Prices SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the pricing records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	echo "<BR>Changing sales orders records";
	$sql = "UPDATE SalesOrders SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the sales order header records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	echo "<BR>Changing stock movement records";
	$sql = "UPDATE StockMoves SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the sales order header records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	echo "<BR>Changing user default customer records";
	$sql = "UPDATE WWW_Users SET CustomerID='" . $_POST['NewDebtorNo'] . "' WHERE CustomerID='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the user records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}
	echo "<BR>Changing the customer code in contract header records";
	$sql = "UPDATE Contracts SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update contract header records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' METHOD=POST>";

?>
<P><CENTER><TABLE>
<TR><TD>Existing Debtor Code:</TD><TD><INPUT TYPE=Text NAME='OldDebtorNo' SIZE=20 MAXLENGTH=20></TD></TR>
<TR><TD>New Debtor Code:</TD><TD><INPUT TYPE=Text NAME='NewDebtorNo' SIZE=20 MAXLENGTH=20></TD></TR>
</TABLE>

<INPUT TYPE=SUBMIT NAME='ProcessCustomerChange' VALUE='Process'>

</FORM>

<?php include("includes/footer.inc"); ?>

<?php
/* $Revision: 1.4 $ */
/*Script to Delete all sales transactions*/

$title = "UTILITY PAGE To Changes A Customer Branch Code In All Tables";
$PageSecurity=15;
include ("includes/session.inc");
include("includes/header.inc");

if (isset($_POST['ProcessCustomerChange'])){

/*First check the customer code exists */
	$result=DB_query("SELECT DebtorNo, BranchCode FROM CustBranch WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'",$db);
	if (DB_num_rows($result)==0){
		echo "<BR><BR>The customer branch code: " . $_POST['DebtorNo'] . " - " . $_POST['OldBranchCode'] . " does not currently exist as a customer branch code in the system";
		exit;
	}

	if ($_POST['NewBranchCode']==""){
		echo "<BR><BR>The new customer branch code to change the old code to must be entered as well!!";
		exit;
	}
	if (strstr($_POST['NewBranchCode'],".")>0 OR  strstr($_POST['NewBranchCode'],"&") OR strstr($_POST['NewBranchCode'],"-") OR strstr($_POST['NewBranchCode']," ")){
	echo "<BR><BR>The new customer branch code cannot contain a hyphen - an ampersand & a point . or a space ";
		exit;
	}



/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT DebtorNo FROM CustBranch WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode ='" . $_POST['NewBranchCode'] . "'",$db);
	if (DB_num_rows($result)!=0){
		echo "<BR><BR>The replacement customer branch code: " . $_POST['NewBranchCode'] . " already exists as a branch code for the same customer - a unique branch code must be entered for the new code.";
		exit;
	}


	$result = DB_query("begin",$db);

	echo "<BR>Inserting the new customer branches master record";
	$sql = "INSERT INTO CustBranch (`BranchCode`, `DebtorNo`, `BrName`, `BrAddress1`, `BrAddress2`, `BrAddress3`, `BrAddress4`, `EstDeliveryDays`, `Area`, `Salesman`, `FwdDate`, `PhoneNo`, `FaxNo`, `ContactName`, `Email`, `DefaultLocation`, `TaxAuthority`, `DisableTrans`, `BrPostAddr1`, `BrPostAddr2`, `BrPostAddr3`, `BrPostAddr4`, `DefaultShipVia`, `CustBranchCode`) SELECT '" . $_POST['NewBranchCode'] . "', `DebtorNo`, `BrName`, `BrAddress1`, `BrAddress2`, `BrAddress3`, `BrAddress4`, `EstDeliveryDays`, `Area`, `Salesman`, `FwdDate`, `PhoneNo`, `FaxNo`, `ContactName`, `Email`, `DefaultLocation`, `TaxAuthority`, `DisableTrans`, `BrPostAddr1`, `BrPostAddr2`, `BrPostAddr3`, `BrPostAddr4`, `DefaultShipVia`, `CustBranchCode` FROM CustBranch WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to insert the new customer branch master record failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}
	echo "<BR>Changing customer transaction records";
	$sql = "UPDATE DebtorTrans SET BranchCode='" . $_POST['NewBranchCode'] . "' WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update debtor transaction records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}
	echo "<BR>Changing sales analysis records";
	$sql = "UPDATE SalesAnalysis SET CustBranch='" . $_POST['NewBranchCode'] . "' WHERE Cust='" . $_POST['DebtorNo'] . "' AND CustBranch='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update Sales Analysis records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}


	echo "<BR>Changing order delivery differences records";
	$sql = "UPDATE OrderDeliveryDifferencesLog SET Branch='" . $_POST['NewBranchCode'] . "' WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND Branch='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update order delivery differences records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	echo "<BR>Changing pricing records";
	$sql = "UPDATE Prices SET BranchCode='" . $_POST['NewBranchCode'] . "' WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the pricing records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	echo "<BR>Changing sales orders records";
	$sql = "UPDATE SalesOrders SET BranchCode='" . $_POST['NewBranchCode'] . "' WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the sales order header records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	echo "<BR>Changing stock movement records";
	$sql = "UPDATE StockMoves SET BranchCode='" . $_POST['NewBranchCode'] . "' WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the sales order header records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	echo "<BR>Changing user default customer records";
	$sql = "UPDATE WWW_Users SET BranchCode='" . $_POST['NewBranchCode'] . "' WHERE CustomerID='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'";;
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update the user records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}
	echo "<BR>Changing the customer branch code in contract header records";
	$sql = "UPDATE Contracts SET BranchCode='" . $_POST['NewBranchCode'] . "' WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to update contract header records failed, the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}


	echo "<BR>Deleting the old customer branch record";
	$sql = "DELETE FROM CustBranch WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The SQL to delete the old customer branch record failed because:<BR>" . DB_error_msg($db) . "<BR>the SQL statement was:<BR>$sql";
		$result=DB_query("rollback",$db);
		exit;
	}

	$result = DB_query("commit",$db);
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' METHOD=POST>";

?>
<P><CENTER><TABLE>
<TR><TD>Customer Code:</TD><TD><INPUT TYPE=Text NAME='DebtorNo' SIZE=20 MAXLENGTH=20></TD></TR>
<TR><TD>Existing Branch Code:</TD><TD><INPUT TYPE=Text NAME='OldBranchCode' SIZE=20 MAXLENGTH=20></TD></TR>
<TR><TD>New Branch Code:</TD><TD><INPUT TYPE=Text NAME='NewBranchCode' SIZE=20 MAXLENGTH=20></TD></TR>
</TABLE>

<INPUT TYPE=SUBMIT NAME='ProcessCustomerChange' VALUE='Process'>

</FORM>

<?php include("includes/footer.inc"); ?>

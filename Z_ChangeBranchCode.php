<?php
/* $Revision: 1.7 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('UTILITY PAGE To Changes A Customer Branch Code In All Tables');
include('includes/header.inc');

if (isset($_POST['ProcessCustomerChange'])){

/*First check the customer code exists */
	$result=DB_query("SELECT DebtorNo,
				BranchCode
			FROM CustBranch
			WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
			AND BranchCode='" . $_POST['OldBranchCode'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg (_('The customer branch code') . ': ' . $_POST['DebtorNo'] . ' - ' . $_POST['OldBranchCode'] . ' ' . _('does not currently exist as a customer branch code in the system'),'error');
		include('includes/footer.inc');
		exit;
	}

	if ($_POST['NewBranchCode']==""){
		prnMsg(_('The new customer branch code to change the old code to must be entered as well'),'error');
		include('includes/footer.inc');
		exit;
	}
	if (strstr($_POST['NewBranchCode'],".")>0 OR  strstr($_POST['NewBranchCode'],"&") OR strstr($_POST['NewBranchCode'],"-") OR strstr($_POST['NewBranchCode']," ")){
		prnMsg(_('The new customer branch code cannot contain') . ' - & . ' . _('or a space'),'error');
		include('includes/footer.inc');
		exit;
	}



/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT DebtorNo FROM CustBranch WHERE DebtorNo='" . $_POST['DebtorNo'] . "' AND BranchCode ='" . $_POST['NewBranchCode'] . "'",$db);
	if (DB_num_rows($result)!=0){
		prmMsg(_('The replacement customer branch code') . ': ' . $_POST['NewBranchCode'] . ' ' . _('already exists as a branch code for the same customer') . ' - ' . _('a unique branch code must be entered for the new code'),'error');
		include('includes/footer.inc');
		exit;
	}


	$result = DB_query("begin",$db);

	prnMsg(_('Inserting the new customer branches master record'),'info');
	$sql = "INSERT INTO CustBranch (`BranchCode`,
					`DebtorNo`,
					`BrName`,
					`BrAddress1`,
					`BrAddress2`,
					`BrAddress3`,
					`BrAddress4`,
					`EstDeliveryDays`,
					`Area`,
					`Salesman`,
					`FwdDate`,
					`PhoneNo`,
					`FaxNo`,
					`ContactName`,
					`Email`,
					`DefaultLocation`,
					`TaxAuthority`,
					`DisableTrans`,
					`BrPostAddr1`,
					`BrPostAddr2`,
					`BrPostAddr3`,
					`BrPostAddr4`,
					`DefaultShipVia`,
					`CustBranchCode`)
			SELECT '" . $_POST['NewBranchCode'] . "',
					`DebtorNo`,
					`BrName`,
					`BrAddress1`,
					`BrAddress2`,
					`BrAddress3`,
					`BrAddress4`,
					`EstDeliveryDays`,
					`Area`,
					`Salesman`,
					`FwdDate`,
					`PhoneNo`,
					`FaxNo`,
					`ContactName`,
					`Email`,
					`DefaultLocation`,
					`TaxAuthority`,
					`DisableTrans`,
					`BrPostAddr1`,
					`BrPostAddr2`,
					`BrPostAddr3`,
					`BrPostAddr4`,
					`DefaultShipVia`,
					`CustBranchCode`
			FROM CustBranch
			WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
			AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('The SQL to insert the new customer branch master record failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg (_('Changing customer transaction records'),'info');
	$sql = "UPDATE DebtorTrans SET
			BranchCode='" . $_POST['NewBranchCode'] . "'
		WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
		AND BranchCode='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = _('The SQL to update debtor transaction records failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing sales analysis records'),'info');
	$sql = "UPDATE SalesAnalysis
			SET CustBranch='" . $_POST['NewBranchCode'] . "'
		WHERE Cust='" . $_POST['DebtorNo'] . "'
		AND CustBranch='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = _('The SQL to update Sales Analysis records failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


	prnMsg(_('Changing order delivery differences records'),'info');
	$sql = "UPDATE OrderDeliveryDifferencesLog
			SET Branch='" . $_POST['NewBranchCode'] . "'
		WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
		AND Branch='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = _('The SQL to update order delivery differences records failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


	prnMsg (_('Changing pricing records'),'info');
	$sql = "UPDATE Prices
			SET BranchCode='" . $_POST['NewBranchCode'] . "'
		WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
		AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = _('The SQL to update the pricing records failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


	prnMsg(_('Changing sales orders records'),'info');
	$sql = "UPDATE SalesOrders
			SET BranchCode='" . $_POST['NewBranchCode'] . "'
		WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
		AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = _('The SQL to update the sales order header records failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


	prnMsg(_('Changing stock movement records'),'info');
	$sql = "UPDATE StockMoves
		SET BranchCode='" . $_POST['NewBranchCode'] . "'
		WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
		AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = _('The SQL to update the stock movement records failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing user default customer records'),'info');
	$sql = "UPDATE WWW_Users
		SET BranchCode='" . $_POST['NewBranchCode'] . "'
		WHERE CustomerID='" . $_POST['DebtorNo'] . "'
		AND BranchCode='" . $_POST['OldBranchCode'] . "'";;

	$ErrMsg = _('The SQL to update the user records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing the customer branch code in contract header records'),'info');
	$sql = "UPDATE Contracts
		SET BranchCode='" . $_POST['NewBranchCode'] . "'
		WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
		AND BranchCode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = _('The SQL to update contract header records failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	$result = DB_query("commit",$db);

	prnMsg(_('Deleting the old customer branch record'),'info');
	$sql = "DELETE FROM CustBranch
		WHERE DebtorNo='" . $_POST['DebtorNo'] . "'
		AND BranchCode='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = _('The SQL to delete the old customer branch record failed because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' METHOD=POST>";

echo '<P><CENTER><TABLE>
<TR><TD>' . _('Customer Code') . ":</TD><TD><INPUT TYPE=Text NAME='DebtorNo' SIZE=20 MAXLENGTH=20></TD></TR>
<TR><TD>" . _('Existing Branch Code') . ":</TD><TD><INPUT TYPE=Text NAME='OldBranchCode' SIZE=20 MAXLENGTH=20></TD></TR>
<TR><TD>" . _('New Branch Code') . ":</TD><TD><INPUT TYPE=Text NAME='NewBranchCode' SIZE=20 MAXLENGTH=20></TD></TR>
</TABLE>";

echo "<INPUT TYPE=SUBMIT NAME='ProcessCustomerChange' VALUE='" . _('Process') . "'>";

echo '</FORM>';

include("includes/footer.inc");
?>

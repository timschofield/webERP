<?php
/* $Revision: 1.6 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('UTILITY PAGE To Changes A Customer Code In All Tables');
include('includes/header.inc');

if (isset($_POST['ProcessCustomerChange'])){

/*First check the customer code exists */
	$result=DB_query("SELECT DebtorNo FROM DebtorsMaster WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg ('<BR><BR>' . _('The customer code') . ': ' . $_POST['OldDebtorNo'] . ' ' . _('does not currently exist as a customer code in the system'),'error');
		include('includes/footer.inc');
		exit;
	}


	if ($_POST['NewDebtorNo']==''){
		prnMsg(_('The new customer code to change the old code to must be entered as well'),'error');
		include('includes/footer.inc');
		exit;
	}
/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT DebtorNo FROM DebtorsMaster WHERE DebtorNo='" . $_POST['NewDebtorNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		prnMsg(_('The replacement customer code') .': ' . $_POST['NewDebtorNo'] . ' ' . _('already exists as a customer code in the system') . ' - ' . _('a unique customer code must be entered for the new code'),'error');
		include('includes/footer.inc');
		exit;
	}

	$result = DB_query('begin',$db);

	prnMsg(_('Inserting the new debtors master record'),'info');
	$sql = "INSERT INTO DebtorsMaster (`DebtorNo`, `Name`, `Address1`, `Address2`, `Address3`, `Address4`, `CurrCode`, `SalesType`, `ClientSince`, `HoldReason`, `PaymentTerms`, `Discount`, `DiscountCode`, `PymtDiscount`, `LastPaid`, `LastPaidDate`, `CreditLimit`, `InvAddrBranch`, `EDIInvoices`, `EDIOrders`, `EDIReference`, `EDITransport`, `EDIAddress`, `EDIServerUser`, `EDIServerPwd`) SELECT '" . $_POST['NewDebtorNo'] . "', `Name`, `Address1`, `Address2`, `Address3`, `Address4`, `CurrCode`, `SalesType`, `ClientSince`, `HoldReason`, `PaymentTerms`, `Discount`, `DiscountCode`, `PymtDiscount`, `LastPaid`, `LastPaidDate`, `CreditLimit`, `InvAddrBranch`, `EDIInvoices`, `EDIOrders`, `EDIReference`, `EDITransport`, `EDIAddress`, `EDIServerUser`, `EDIServerPwd` FROM DebtorsMaster WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";

	$DbgMsg =_('The SQL that failed was');
	$ErrMsg = _('The SQL to insert the new debtors master record failed') . ', ' . _('the SQL statement was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Inserting new customer branch records'),'info');
	$sql = "INSERT INTO CustBranch ( `BranchCode`, `DebtorNo`, `BrName`, `BrAddress1`, `BrAddress2`, `BrAddress3`, `BrAddress4`, `EstDeliveryDays`, `Area`, `Salesman`, `FwdDate`, `PhoneNo`, `FaxNo`, `ContactName`, `Email`, `DefaultLocation`, `TaxAuthority`, `DisableTrans`, `BrPostAddr1`, `BrPostAddr2`, `BrPostAddr3`, `BrPostAddr4`, `DefaultShipVia`, `CustBranchCode`) SELECT BranchCode, '" . $_POST['NewDebtorNo'] . "', `BrName`, `BrAddress1`, `BrAddress2`, `BrAddress3`, `BrAddress4`, `EstDeliveryDays`, `Area`, `Salesman`, `FwdDate`, `PhoneNo`, `FaxNo`, `ContactName`, `Email`, `DefaultLocation`, `TaxAuthority`, `DisableTrans`, `BrPostAddr1`, `BrPostAddr2`, `BrPostAddr3`, `BrPostAddr4`, `DefaultShipVia`, `CustBranchCode` FROM CustBranch WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to insert new customer branch records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Changing debtor transaction records'),'info');

	$sql = "UPDATE DebtorTrans SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update debtor transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing sales analysis records'),'info');

	$sql = "UPDATE SalesAnalysis SET Cust='" . $_POST['NewDebtorNo'] . "' WHERE Cust='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update Sales Analysis records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);



	prnMsg (_('Changing order delivery differences records'),'info');
	$sql = "UPDATE OrderDeliveryDifferencesLog SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update order delivery differences records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


	prnMsg(_('Changing pricing records'),'info');
	$sql = "UPDATE Prices SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update the pricing records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing sales orders records'),'info');
	$sql = "UPDATE SalesOrders SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update the sales order header records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg( _('Changing stock movement records'),'info');
	$sql = "UPDATE StockMoves SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update the sales order header records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing user default customer records'),'info');
	$sql = "UPDATE WWW_Users SET CustomerID='" . $_POST['NewDebtorNo'] . "' WHERE CustomerID='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update the user records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing the customer code in contract header records'),'info');
	$sql = "UPDATE Contracts SET DebtorNo='" . $_POST['NewDebtorNo'] . "' WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update contract header records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Deleting the old customer branch records from the CustBranch table'),'info');
	$sql = "DELETE FROM CustBranch WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to delete the old CustBranch records for the old debtor record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


	prnMsg(_('Deleting the customer code from the DebtorsMaster table'),'info');
	$sql = "DELETE FROM DebtorsMaster WHERE DebtorNo='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to delete the old debtor record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


	$result = DB_query('commit',$db);

}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' METHOD=POST>";

echo '<P><CENTER><TABLE>
	<TR><TD>' . _('Existing Debtor Code') . ":</TD>
		<TD><INPUT TYPE=Text NAME='OldDebtorNo' SIZE=20 MAXLENGTH=20></TD>
	</TR>";
echo '<TR><TD> ' . _('New Debtor Code') . ":</TD>
	<TD><INPUT TYPE=Text NAME='NewDebtorNo' SIZE=20 MAXLENGTH=20></TD>
	</TR>
	</TABLE>";

echo "<INPUT TYPE=SUBMIT NAME='ProcessCustomerChange' VALUE='" . _('Process') . "'>";

echo '</FORM>';

include('includes/footer.inc');

?>

<?php

/* This script is an utility to change a customer code. */

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE To Changes A Customer Code In All Tables');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeCustomerCode';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/customer.png" title="' .
	__('Change A Customer Code') . '" /> ' .// Icon title.
	__('Change A Customer Code') . '</p>';// Page title.

if (isset($_POST['ProcessCustomerChange'])){

/*First check the customer code exists */
	$Result = DB_query("SELECT debtorno FROM debtorsmaster WHERE debtorno='" . $_POST['OldDebtorNo'] . "'");
	if (DB_num_rows($Result)==0){
		prnMsg('<br /><br />' . __('The customer code') . ': ' . $_POST['OldDebtorNo'] . ' ' . __('does not currently exist as a customer code in the system'),'error');
		include('includes/footer.php');
		exit();
	}


	if ($_POST['NewDebtorNo']==''){
		prnMsg(__('The new customer code to change the old code to must be entered as well'),'error');
		include('includes/footer.php');
		exit();
	}
/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT debtorno FROM debtorsmaster WHERE debtorno='" . $_POST['NewDebtorNo'] . "'");
	if (DB_num_rows($Result)!=0){
		prnMsg(__('The replacement customer code') .': ' . $_POST['NewDebtorNo'] . ' ' . __('already exists as a customer code in the system') . ' - ' . __('a unique customer code must be entered for the new code'),'error');
		include('includes/footer.php');
		exit();
	}

	DB_Txn_Begin();

	prnMsg(__('Inserting the new debtors master record'),'info');
	$SQL = "INSERT INTO debtorsmaster
				(debtorno,
				name,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6,
				currcode,
				salestype,
				clientsince,
				holdreason,
				paymentterms,
				discount,
				pymtdiscount,
				lastpaid,
				lastpaiddate,
				creditlimit,
				invaddrbranch,
				discountcode,
				ediinvoices,
				ediorders,
				edireference,
				editransport,
				ediaddress,
				ediserveruser,
				ediserverpwd,
				taxref,
				customerpoline,
				typeid,
				language_id)
			SELECT
				'" . $_POST['NewDebtorNo'] . "',
				name,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6,
				currcode,
				salestype,
				clientsince,
				holdreason,
				paymentterms,
				discount,
				pymtdiscount,
				lastpaid,
				lastpaiddate,
				creditlimit,
				invaddrbranch,
				discountcode,
				ediinvoices,
				ediorders,
				edireference,
				editransport,
				ediaddress,
				ediserveruser,
				ediserverpwd,
				taxref,
				customerpoline,
				typeid,
				language_id
			FROM debtorsmaster
			WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to insert the new debtors master record failed') . ', ' . __('the SQL statement was');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Inserting new customer branch records'),'info');
	$SQL = "INSERT INTO custbranch
				(branchcode,
				debtorno,
				brname,
				braddress1,
				braddress2,
				braddress3,
				braddress4,
				braddress5,
				braddress6,
				lat,
				lng,
				estdeliverydays,
				area,
				salesman,
				fwddate,
				phoneno,
				faxno,
				contactname,
				email,
				defaultlocation,
				taxgroupid,
				defaultshipvia,
				deliverblind,
				disabletrans,
				brpostaddr1,
				brpostaddr2,
				brpostaddr3,
				brpostaddr4,
				brpostaddr5,
				brpostaddr6,
				specialinstructions,
				custbranchcode)
			SELECT
				branchcode,
				'" . $_POST['NewDebtorNo'] . "',
				brname,
				braddress1,
				braddress2,
				braddress3,
				braddress4,
				braddress5,
				braddress6,
				lat,
				lng,
				estdeliverydays,
				area,
				salesman,
				fwddate,
				phoneno,
				faxno,
				contactname,
				email,
				defaultlocation,
				taxgroupid,
				defaultshipvia,
				deliverblind,
				disabletrans,
				brpostaddr1,
				brpostaddr2,
				brpostaddr3,
				brpostaddr4,
				brpostaddr5,
				brpostaddr6,
				specialinstructions,
				custbranchcode
			FROM custbranch
			WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = __('The SQL to insert new customer branch records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing the customer code in contract header records'),'info');
	$SQL = "UPDATE contracts SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update contract header records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);
	
	prnMsg(__('Changing customer contacts records'),'info');
	$SQL = "UPDATE custcontacts SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update customer contacts records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing customer items records'),'info');
	$SQL = "UPDATE custitem SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update customer items records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing customer notes records'),'info');
	$SQL = "UPDATE custnotes SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update customer notes records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing debtor transaction records'),'info');
	$SQL = "UPDATE debtortrans SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update debtor transaction records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing location records'),'info');
	$SQL = "UPDATE locations SET cashsalecustomer='" . $_POST['NewDebtorNo'] . "' WHERE cashsalecustomer='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update location records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing order delivery differences records'),'info');
	$SQL = "UPDATE orderdeliverydifferenceslog SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update order delivery differences records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing pricing records'),'info');
	$SQL = "UPDATE prices SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update the pricing records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing recurring sales orders records'),'info');
	$SQL = "UPDATE recurringsalesorders SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update debtor transaction records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing sales analysis records'),'info');
	$SQL = "UPDATE salesanalysis SET cust='" . $_POST['NewDebtorNo'] . "' WHERE cust='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update Sales Analysis records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing sales orders records'),'info');
	$SQL = "UPDATE salesorders SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update the sales order header records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing sell through support records'),'info');
	$SQL = "UPDATE sellthroughsupport SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update thesell through support records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg( __('Changing stock movement records'),'info');
	$SQL = "UPDATE stockmoves SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update the sales order header records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing user default customer records'),'info');
	$SQL = "UPDATE www_users SET customerid='" . $_POST['NewDebtorNo'] . "' WHERE customerid='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to update the user records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	DB_IgnoreForeignKeys();

	prnMsg(__('Deleting the old customer branch records from the CustBranch table'),'info');
	$SQL = "DELETE FROM custbranch WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to delete the old CustBranch records for the old debtor record failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Deleting the customer code from the DebtorsMaster table'),'info');
	$SQL = "DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = __('The SQL to delete the old debtor record failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	DB_ReinstateForeignKeys();
	DB_Txn_Commit();

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Customer to Change'), '</legend>
	<field>
		<label>' . __('Existing Customer Code') . ':</label>
		<input type="text" name="OldDebtorNo" size="20" maxlength="20" />
	</field>
	<field>
		<label> ' . __('New Customer Code') . ':</label>
		<input type="text" name="NewDebtorNo" size="20" maxlength="20" />
	</field>
	</fieldset>

	<div class="centre">
		<input type="submit" name="ProcessCustomerChange" value="' . __('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');

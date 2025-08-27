<?php

/* This script is an utility to change a customer branch code. */

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE To Changes A Customer Branch Code In All Tables');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeBranchCode';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/customer.png" title="' .
	__('Change A Customer Branch Code') . '" /> ' .// Icon title.
	__('Change A Customer Branch Code') . '</p>';// Page title.

if (isset($_POST['ProcessCustomerChange'])){

/*First check the customer code exists */
	$Result = DB_query("SELECT debtorno,
							branchcode
						FROM custbranch
						WHERE debtorno='" . $_POST['DebtorNo'] . "'
						AND branchcode='" . $_POST['OldBranchCode'] . "'");
	if (DB_num_rows($Result)==0){
		prnMsg(__('The customer branch code') . ': ' . $_POST['DebtorNo'] . ' - ' . $_POST['OldBranchCode'] . ' ' . __('does not currently exist as a customer branch code in the system'),'error');
		include('includes/footer.php');
		exit();
	}

	if ($_POST['NewBranchCode']==''){
		prnMsg(__('The new customer branch code to change the old code to must be entered as well'),'error');
		include('includes/footer.php');
		exit();
	}
	if (ContainsIllegalCharacters($_POST['NewBranchCode']) OR mb_strstr($_POST['NewBranchCode'],' ')){
		prnMsg(__('The new customer branch code cannot contain') . ' - & . ' . __('or a space'),'error');
		include('includes/footer.php');
		exit();
	}


/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT debtorno FROM custbranch WHERE debtorno='" . $_POST['DebtorNo'] . "' AND branchcode ='" . $_POST['NewBranchCode'] . "'");
	if (DB_num_rows($Result)!=0){
		prnMsg(__('The replacement customer branch code') . ': ' . $_POST['NewBranchCode'] . ' ' . __('already exists as a branch code for the same customer') . ' - ' . __('a unique branch code must be entered for the new code'),'error');
		include('includes/footer.php');
		exit();
	}


	DB_Txn_Begin();

	prnMsg(__('Inserting the new customer branches master record'),'info');
	$SQL = "INSERT INTO custbranch (`branchcode`,
					`debtorno`,
					`brname`,
					`braddress1`,
					`braddress2`,
					`braddress3`,
					`braddress4`,
					`braddress5`,
					`braddress6`,
					`estdeliverydays`,
					`area`,
					`salesman`,
					`fwddate`,
					`phoneno`,
					`faxno`,
					`contactname`,
					`email`,
					`defaultlocation`,
					`taxgroupid`,
					`disabletrans`,
					`brpostaddr1`,
					`brpostaddr2`,
					`brpostaddr3`,
					`brpostaddr4`,
					`brpostaddr5`,
					`brpostaddr6`,
					`defaultshipvia`,
					`specialinstructions`,
					`custbranchcode`)
			SELECT '" . $_POST['NewBranchCode'] . "',
					`debtorno`,
					`brname`,
					`braddress1`,
					`braddress2`,
					`braddress3`,
					`braddress4`,
					`braddress5`,
					`braddress6`,
					`estdeliverydays`,
					`area`,
					`salesman`,
					`fwddate`,
					`phoneno`,
					`faxno`,
					`contactname`,
					`email`,
					`defaultlocation`,
					`taxgroupid`,
					`disabletrans`,
					`brpostaddr1`,
					`brpostaddr2`,
					`brpostaddr3`,
					`brpostaddr4`,
					`brpostaddr5`,
					`brpostaddr6`,
					`defaultshipvia`,
					'',
					`custbranchcode`
			FROM custbranch
			WHERE debtorno='" . $_POST['DebtorNo'] . "'
			AND branchcode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = __('The SQL to insert the new customer branch master record failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing customer transaction records'),'info');
	$SQL = "UPDATE debtortrans SET
					branchcode='" . $_POST['NewBranchCode'] . "'
					WHERE debtorno='" . $_POST['DebtorNo'] . "'
					AND branchcode='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = __('The SQL to update debtor transaction records failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing sales analysis records'),'info');
	$SQL = "UPDATE salesanalysis
					SET custbranch='" . $_POST['NewBranchCode'] . "'
					WHERE cust='" . $_POST['DebtorNo'] . "'
					AND custbranch='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = __('The SQL to update Sales Analysis records failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);


	prnMsg(__('Changing order delivery differences records'),'info');
	$SQL = "UPDATE orderdeliverydifferenceslog
					SET branch='" . $_POST['NewBranchCode'] . "'
					WHERE debtorno='" . $_POST['DebtorNo'] . "'
					AND branch='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = __('The SQL to update order delivery differences records failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);


	prnMsg(__('Changing pricing records'),'info');
	$SQL = "UPDATE prices
				SET branchcode='" . $_POST['NewBranchCode'] . "'
				WHERE debtorno='" . $_POST['DebtorNo'] . "'
				AND branchcode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = __('The SQL to update the pricing records failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);


	prnMsg(__('Changing sales orders records'),'info');
	$SQL = "UPDATE salesorders
					SET branchcode='" . $_POST['NewBranchCode'] . "'
					WHERE debtorno='" . $_POST['DebtorNo'] . "'
					AND branchcode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = __('The SQL to update the sales order header records failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);


	prnMsg(__('Changing stock movement records'),'info');
	$SQL = "UPDATE stockmoves
					SET branchcode='" . $_POST['NewBranchCode'] . "'
					WHERE debtorno='" . $_POST['DebtorNo'] . "'
					AND branchcode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = __('The SQL to update the stock movement records failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing user default customer records'),'info');
	$SQL = "UPDATE www_users
					SET branchcode='" . $_POST['NewBranchCode'] . "'
					WHERE customerid='" . $_POST['DebtorNo'] . "'
					AND branchcode='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = __('The SQL to update the user records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing the customer branch code in contract header records'),'info');
	$SQL = "UPDATE contracts
					SET branchcode='" . $_POST['NewBranchCode'] . "'
					WHERE debtorno='" . $_POST['DebtorNo'] . "'
					AND branchcode='" . $_POST['OldBranchCode'] . "'";
	$ErrMsg = __('The SQL to update contract header records failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	DB_Txn_Commit();

	DB_IgnoreForeignKeys();
	prnMsg(__('Deleting the old customer branch record'),'info');
	$SQL = "DELETE FROM custbranch
					WHERE debtorno='" . $_POST['DebtorNo'] . "'
					AND branchcode='" . $_POST['OldBranchCode'] . "'";

	$ErrMsg = __('The SQL to delete the old customer branch record failed because');
	$Result = DB_query($SQL, $ErrMsg, '', true);
	DB_ReinstateForeignKeys();

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Branch Code Changes'), '</legend>
		<field>
			<label>' . __('Customer Code') . ':</label>
			<input type="text" name="DebtorNo" size="20" maxlength="20" />
		</field>
		<field>
			<label>' . __('Existing Branch Code') . ':</label>
			<input type="text" name="OldBranchCode" size="20" maxlength="20" />
		</field>
		<field>
			<label>' . __('New Branch Code') . ':</label>
			<input type="text" name="NewBranchCode" size="20" maxlength="20" />
		</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ProcessCustomerChange" value="' . __('Process') . '" />
	</div>';

echo '</form>';

include('includes/footer.php');

<?php

/* This script is an utility to change a salesman code. */

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE To Change A Salesman Code In All Tables');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeSalesmanCode';
include('includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', __('Change A Sales Person Code'), '" /> ', __('Change A Sales Person Code'),
	'</p>';

if (isset($_POST['ProcessSalesmanChange'])){

	if ($_POST['OldSalesmanCode']==''){
		prnMsg(__('An existing salesman code entry must be provided'), 'error');
		include('includes/footer.php');
		exit();
	}

/*First check the salesman code exists */
	$Result = DB_query("SELECT salesmancode FROM salesman WHERE salesmancode='" . $_POST['OldSalesmanCode'] . "'");
	if (DB_num_rows($Result) == 0){
		prnMsg('<br /><br />' . __('The salesman code') . ': ' . $_POST['OldSalesmanCode'] . ' ' . __('does not currently exist as a sales person code in the system'), 'error');
		include('includes/footer.php');
		exit();
	}

	if ($_POST['NewSalesmanCode']==''){
		prnMsg(__('A new salesman code entry must be provided'), 'error');
		include('includes/footer.php');
		exit();
	}
	else if (ContainsIllegalCharacters($_POST['NewSalesmanCode'])) {
		prnMsg(__('The new salesman code to change the old code to contains illegal characters - no changes will be made'), 'error');
		include('includes/footer.php');
		exit();
	}

	$_POST['NewSalesmanCode'] = mb_strtoupper($_POST['NewSalesmanCode']);

/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT salesmancode FROM salesman WHERE salesmancode='" . $_POST['NewSalesmanCode'] . "'");
	if (DB_num_rows($Result)!=0){
		prnMsg(__('The replacement salesman code') .': ' . $_POST['NewSalesmanCode'] . ' ' . __('already exists as a salesman code in the system') . ' - ' . __('a unique salesman code must be entered for the new code'), 'error');
		include('includes/footer.php');
		exit();
	}

    DB_Txn_Begin();

	prnMsg(__('Inserting the new salesman master record'), 'info');
	$SQL = "INSERT INTO salesman (`salesmancode`,
								`salesmanname`,
								`commissionrate1`,
								`commissionrate2`,
								`breakpoint`,
								`smantel`,
								`smanfax`,
								`current`)
					SELECT '" . $_POST['NewSalesmanCode'] . "',
								`salesmanname`,
								`commissionrate1`,
								`commissionrate2`,
								`breakpoint`,
								`smantel`,
								`smanfax`,
								`current`
					FROM salesman
					WHERE salesmancode='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = __('The SQL to insert the new salesman master record failed') . ', ' . __('the SQL statement was');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing debtor branch records'), 'info');
	$SQL = "UPDATE custbranch SET salesman='" . $_POST['NewSalesmanCode'] . "' WHERE salesman='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = __('The SQL to update debtor branch records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing debtor transaction records'), 'info');
	$SQL = "UPDATE debtortrans SET salesperson='" . $_POST['NewSalesmanCode'] . "' WHERE salesperson='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = __('The SQL to update debtor transaction records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing sales analysis records'), 'info');
	$SQL = "UPDATE salesanalysis SET salesperson='" . $_POST['NewSalesmanCode'] . "' WHERE salesperson='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = __('The SQL to update Sales Analysis records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing sales orders records'), 'info');
	$SQL = "UPDATE salesorders SET salesperson='" . $_POST['NewSalesmanCode'] . "' WHERE salesperson='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = __('The SQL to update the sales order header records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	prnMsg(__('Changing user salesman records'), 'info');
	$SQL = "UPDATE www_users SET salesman='" . $_POST['NewSalesmanCode'] . "' WHERE salesman='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = __('The SQL to update the user records failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	DB_IgnoreForeignKeys();

	prnMsg(__('Deleting the salesman code from the Salesman table'), 'info');
	$SQL = "DELETE FROM salesman WHERE salesmancode='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = __('The SQL to delete the old salesman record failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	DB_Txn_Commit();
	DB_ReinstateForeignKeys();
}

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
	<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />
	<fieldset>
	<legend>', __('Sales Person to Change'), '</legend>
	<field>
		<label>', __('Existing Sales Person Code'), ':</label>
		<input type="text" name="OldSalesmanCode" size="4" maxlength="4" required="required" />
	</field>
	<field>
		<label> ', __('New Sales Person Code'), ':</label>
		<input type="text" name="NewSalesmanCode" size="4" maxlength="4" required="required"/>
	</field>
	</fieldset>
	<div class="centre">
	<input type="submit" name="ProcessSalesmanChange" value="', __('Process'), '" />
	</div>
	</form>';

include('includes/footer.php');

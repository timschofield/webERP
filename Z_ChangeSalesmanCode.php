<?php
/* This script is an utility to change a salesman code. */

include ('includes/session.php');

$Title = _('UTILITY PAGE To Change A Salesman Code In All Tables');// Screen identificator.
$ViewTopic = 'SpecialUtilities'; // Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_ChangeSalesmanCode'; // Anchor's id in the manual's html document.

include('includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/salesman.png" title="', _('Change A Salesman Code'), '" /> ', _('Change A Salesman Code'),
	'</p>';

if (isset($_POST['ProcessSalesmanChange'])){

	if ($_POST['OldSalesmanCode']==''){
		prnMsg(_('An existing salesman code entry must be provided'), 'error');
		include('includes/footer.php');
		exit;
	}

/*First check the salesman code exists */
	$result=DB_query("SELECT salesmancode FROM salesman WHERE salesmancode='" . $_POST['OldSalesmanCode'] . "'");
	if (DB_num_rows($result) == 0){
		prnMsg('<br /><br />' . _('The salesman code') . ': ' . $_POST['OldSalesmanCode'] . ' ' . _('does not currently exist as a salesman code in the system'), 'error');
		include('includes/footer.php');
		exit;
	}

	if ($_POST['NewSalesmanCode']==''){
		prnMsg(_('A new salesman code entry must be provided'), 'error');
		include('includes/footer.php');
		exit;
	}
	else if (ContainsIllegalCharacters($_POST['NewSalesmanCode'])) {
		prnMsg(_('The new salesman code to change the old code to contains illegal characters - no changes will be made'), 'error');
		include ('includes/footer.php');
		exit;
	}

	$_POST['NewSalesmanCode'] = mb_strtoupper($_POST['NewSalesmanCode']);

/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT salesmancode FROM salesman WHERE salesmancode='" . $_POST['NewSalesmanCode'] . "'");
	if (DB_num_rows($result)!=0){
		prnMsg(_('The replacement salesman code') .': ' . $_POST['NewSalesmanCode'] . ' ' . _('already exists as a salesman code in the system') . ' - ' . _('a unique salesman code must be entered for the new code'), 'error');
		include('includes/footer.php');
		exit;
	}

	$result = DB_Txn_Begin();

	prnMsg(_('Inserting the new salesman master record'), 'info');
	$sql = "INSERT INTO salesman (`salesmancode`,
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

	$DbgMsg =_('The SQL that failed was');
	$ErrMsg = _('The SQL to insert the new salesman master record failed') . ', ' . _('the SQL statement was');
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing debtor branch records'), 'info');
	$sql = "UPDATE custbranch SET salesman='" . $_POST['NewSalesmanCode'] . "' WHERE salesman='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = _('The SQL to update debtor branch records failed');
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing debtor transaction records'), 'info');
	$sql = "UPDATE debtortrans SET salesperson='" . $_POST['NewSalesmanCode'] . "' WHERE salesperson='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = _('The SQL to update debtor transaction records failed');
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing sales analysis records'), 'info');
	$sql = "UPDATE salesanalysis SET salesperson='" . $_POST['NewSalesmanCode'] . "' WHERE salesperson='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = _('The SQL to update Sales Analysis records failed');
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing sales orders records'), 'info');
	$sql = "UPDATE salesorders SET salesperson='" . $_POST['NewSalesmanCode'] . "' WHERE salesperson='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = _('The SQL to update the sales order header records failed');
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Changing user salesman records'), 'info');
	$sql = "UPDATE www_users SET salesman='" . $_POST['NewSalesmanCode'] . "' WHERE salesman='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = _('The SQL to update the user records failed');
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);

	$result = DB_IgnoreForeignKeys();

	prnMsg(_('Deleting the salesman code from the Salesman table'), 'info');
	$sql = "DELETE FROM salesman WHERE salesmancode='" . $_POST['OldSalesmanCode'] . "'";

	$ErrMsg = _('The SQL to delete the old salesman record failed');
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);

	$result = DB_Txn_Commit();
	$result = DB_ReinstateForeignKeys();
}

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
	<div class="centre">
	<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />
	<br />
	<table>
	<tr>
		<td>', _('Existing Salesman Code'), ':</td>
		<td><input type="text" name="OldSalesmanCode" size="4" maxlength="4" required="required" /></td>
	</tr>
	<tr>
		<td> ', _('New Salesman Code'), ':</td>
		<td><input type="text" name="NewSalesmanCode" size="4" maxlength="4" required="required"/></td>
	</tr>
	</table>

	<input type="submit" name="ProcessSalesmanChange" value="', _('Process'), '" />
	</div>
	</form>';

include('includes/footer.php');
?>
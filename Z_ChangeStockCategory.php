<?php

/* $Id: Z_ChangeStockCategory.php 6125 2013-07-21 12:43:50Z tehonu $ */

include ('includes/session.inc');
$Title = _('UTILITY PAGE Change A Stock Category');
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

if (isset($_POST['ProcessStockChange'])) {
	$_POST['NewStockCategory'] = mb_strtoupper($_POST['NewStockCategory']);

	/*First check the stock code exists */
	$result = DB_query("SELECT categoryid FROM stockcategory WHERE categoryid='" . $_POST['OldStockCategory'] . "'", $db);

	if (DB_num_rows($result) == 0) {
		prnMsg(_('The stock Category') . ': ' . $_POST['OldStockCategory'] . ' ' . _('does not currently exist as a stock category in the system'), 'error');
		include ('includes/footer.inc');
		exit;
	}

	if (ContainsIllegalCharacters($_POST['NewStockCategory'])) {
		prnMsg(_('The new stock code to change the old code to contains illegal characters - no changes will be made'), 'error');
		include ('includes/footer.inc');
		exit;
	}

	if ($_POST['NewStockCategory'] == '') {
		prnMsg(_('The new stock code to change the old code to must be entered as well'), 'error');
		include ('includes/footer.inc');
		exit;
	}

	/*Now check that the new code doesn't already exist */
	$result = DB_query("SELECT categoryid FROM stockcategory WHERE categoryid='" . $_POST['NewStockCategory'] . "'", $db);

	if (DB_num_rows($result) != 0) {
		echo '<br /><br />';
		prnMsg(_('The replacement stock category') . ': ' . $_POST['NewStockCategory'] . ' ' . _('already exists as a stock category in the system') . ' - ' . _('a unique stock category must be entered for the new stock category'), 'error');
		include ('includes/footer.inc');
		exit;
	}
	$result = DB_Txn_Begin($db);
	echo '<br />' . _('Adding the new stock Category record');
	$sql = "INSERT INTO stockcategory (categoryid,
					categorydescription,
					stocktype,
					stockact,
					adjglact,
					purchpricevaract,
					materialuseagevarac,
					defaulttaxcatid,
					wipact)
			SELECT '" . $_POST['NewStockCategory'] . "',
				categorydescription,
					stocktype,
					stockact,
					adjglact,
					purchpricevaract,
					materialuseagevarac,
					defaulttaxcatid,
					wipact
			FROM stockcategory
			WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$DbgMsg = _('The SQL statement that failed was');
	$ErrMsg = _('The SQL to insert the new stock category record failed');
	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
	echo '<br />' . _('Changing stock properties');
	$sql = "UPDATE stockcatproperties SET categoryid='" . $_POST['NewStockCategory'] . "' WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to update stock properties records failed');
	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
	echo '<br />' . _('Changing stock master records');
	$sql = "UPDATE stockmaster SET categoryid='" . $_POST['NewStockCategory'] . "' WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to update stock master transaction records failed');
	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
	echo '<br />' . _('Changing sales analysis records');
	$sql = "UPDATE salesanalysis SET stkcategory='" . $_POST['NewStockID'] . "' WHERE stkcategory='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to update Sales Analysis records failed');
	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
	$sql = 'SET FOREIGN_KEY_CHECKS=1';
	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
	$result = DB_Txn_Commit($db);
	echo '<br />' . _('Deleting the old stock category record');
	$sql = "DELETE FROM stockcategory WHERE categoryid='" . $_POST['OldStockCategory'] . "'";
	$ErrMsg = _('The SQL to delete the old stock category record failed');
	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
	echo '<p>' . _('Stock Code') . ': ' . $_POST['OldStockCategory'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewStockCategory'];
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br />
	<table>
		<tr>
			<td>' . _('Existing Inventory Category Code') . ':</td>
			<td><input type="text" name="OldStockCategory" size="20" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('New Inventory Category Code') . ':</td>
			<td><input type="text" name="NewStockCategory" size="20" maxlength="20" /></td>
		</tr>
	</table>

		<input type="submit" name="ProcessStockChange" value="' . _('Process') . '" />
	</div>
	</form>';
include ('includes/footer.inc');
?>
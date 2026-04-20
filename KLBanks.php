<?php
/* KLBanks.php
 * @Author: GitHub Copilot
 * @Date: 2026-04-20
 * @Description: Script to maintain Banks table (banks).
 * Based on KLPackaging.php
 */

include(__DIR__ . '/includes/session.php');

$Title = _('KL Banks Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'KLBanks';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' .$Title . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedBankCode'])) {
	$SelectedBankCode = $_GET['SelectedBankCode'];
} elseif (isset($_POST['SelectedBankCode'])) {
	$SelectedBankCode = $_POST['SelectedBankCode'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i = 1;

	//first off validate inputs are sensible

	if (mb_strlen($_POST['BankCode']) < 1) {
		$InputError = 1;
		prnMsg(_('The bank code must exist'), 'error');
		$Errors[$i] = 'BankCode';
		$i++;
	}
	if (mb_strlen($_POST['BankCode']) > 20) {
		$InputError = 1;
		prnMsg(_('The bank code must be 20 characters or less long'), 'error');
		$Errors[$i] = 'BankCode';
		$i++;
	}
	if (empty($_POST['BankName']) or mb_strlen($_POST['BankName']) > 64) {
		$InputError = 1;
		prnMsg(_('The bank name must be 64 characters or less long and not empty'), 'error');
		$Errors[$i] = 'BankName';
		$i++;
	}
	if (mb_strlen($_POST['BankCodeForBulkTransfers']) > 20) {
		$InputError = 1;
		prnMsg(_('The bank code for bulk transfers must be 20 characters or less long'), 'error');
		$Errors[$i] = 'BankCodeForBulkTransfers';
		$i++;
	}

	if (isset($SelectedBankCode) and $InputError != 1) {

		/*SelectedBankCode could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE banks SET
						bankname='" . $_POST['BankName'] . "',
						bankcodeforbulktransfers='" . $_POST['BankCodeForBulkTransfers'] . "'
					WHERE bankcode = '" . $SelectedBankCode . "'";

		$Msg = _('The bank record has been updated') . '.';
	} else if ($InputError != 1) {

		/*SelectedBankCode is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new bank form */

		$SQL = "INSERT INTO banks (bankcode,
								bankname,
								bankcodeforbulktransfers)
						VALUES (
							'" . $_POST['BankCode'] . "',
							'" . $_POST['BankName'] . "',
							'" . $_POST['BankCodeForBulkTransfers'] . "'
						)";

		$Msg = _('The bank record has been added') . '.';
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedBankCode);
		unset($_POST['BankCode']);
		unset($_POST['BankName']);
		unset($_POST['BankCodeForBulkTransfers']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN bankaccounts
	$SQL = "SELECT COUNT(*) FROM bankaccounts WHERE bankaccounts.bank = '" . $SelectedBankCode . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this bank code because bank accounts have been created referring to this code'), 'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('bank accounts that refer to this bank code');
	} else {
		//only delete if not used in bankaccounts
		$SQL = "DELETE FROM banks WHERE bankcode='" . $SelectedBankCode . "'";
		$Result = DB_query($SQL);
		prnMsg(_('The bank record has been deleted') . '!', 'success');
	}
	//end if bank code used in bankaccounts
}

if (!isset($SelectedBankCode)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedBankCode will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of descriptions will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT bankcode, bankname, bankcodeforbulktransfers FROM banks ORDER BY bankname";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="5"><h3>' . _('Banks') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . _('Bank Code') . '</th>
			<th class="SortedColumn">' . _('Bank Name') . '</th>
			<th class="SortedColumn">' . _('Bulk Transfer Code') . '</th>
		</tr>
	</thead>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['bankcode'], '</td>
				<td>', $MyRow['bankname'], '</td>
				<td>', $MyRow['bankcodeforbulktransfers'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedBankCode=', urlencode($MyRow['bankcode']), '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedBankCode=', urlencode($MyRow['bankcode']), '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this bank?') . '\');">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedBankCode)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show all Bank Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedBankCode)) {
		//editing an existing bank

		$SQL = "SELECT bankcode,
						bankname,
						bankcodeforbulktransfers
					FROM banks
					WHERE bankcode='" . $SelectedBankCode . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['BankCode'] = $MyRow['bankcode'];
		$_POST['BankName'] = $MyRow['bankname'];
		$_POST['BankCodeForBulkTransfers'] = $MyRow['bankcodeforbulktransfers'];

		echo '<input type="hidden" name="SelectedBankCode" value="' . $SelectedBankCode . '" />';
		echo '<input type="hidden" name="BankCode" value="' . $_POST['BankCode'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Update Bank.') . '</legend>';
		echo '<field>
				<label for="BankCode">' . _('Bank Code') . ':</label>
				<fieldtext>' . $_POST['BankCode'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedBankCode only do the else when a new record is being entered

		if (!isset($_POST['BankCode'])) {
			$_POST['BankCode'] = '';
		}
		if (!isset($_POST['BankName'])) {
			$_POST['BankName'] = '';
		}
		if (!isset($_POST['BankCodeForBulkTransfers'])) {
			$_POST['BankCodeForBulkTransfers'] = '';
		}

		echo '<fieldset>';
		echo '<legend>' . _('New Bank.') . '</legend>';
		echo '<field>
				<label for="BankCode">' . _('Bank Code') . ':</label>
				<input type="text" name="BankCode"' . (in_array('BankCode', $Errors) ? 'class="inputerror"' : '') . ' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="" value="' . $_POST['BankCode'] . '" size="22" maxlength="20" />
				<fieldhelp>' . _('A 20 character code to identify this bank. Any alpha-numeric characters can be used') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="BankName">' . _('Bank Name') . ':</label>
			<input type="text"' . (in_array('BankName', $Errors) ? 'class="inputerror"' : '') . ' name="BankName" ' . (isset($SelectedBankCode) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['BankName'] . '" title="" size="66" maxlength="64" />
			<fieldhelp>' . _('The full name of the bank is required') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="BankCodeForBulkTransfers">' . _('Bulk Transfer Code') . ':</label>
			<input type="text"' . (in_array('BankCodeForBulkTransfers', $Errors) ? 'class="inputerror"' : '') . ' name="BankCodeForBulkTransfers" value="' . $_POST['BankCodeForBulkTransfers'] . '" title="" size="22" maxlength="20" />
			<fieldhelp>' . _('Bank code used for bulk transfers (optional)') . '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');

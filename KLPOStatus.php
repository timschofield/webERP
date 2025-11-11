<?php
/* KLPOStatus.php
 * @Author: Gemini 2.5 Pro
 * @Date: 2025-11-11
 * @Description: Script to maintain PO Status table (klpostatus).
 * Based on PaymentTerms.php
 */

include('includes/session.php');

$Title = _('KL Purchase Order Status Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'KLPOStatus';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' .$Title . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedPaymentTerm'])) {
	$SelectedPaymentTerm = $_GET['SelectedPaymentTerm'];
} elseif (isset($_POST['SelectedPaymentTerm'])) {
	$SelectedPaymentTerm = $_POST['SelectedPaymentTerm'];
}

if (isset($_GET['SelectedCode'])) {
	$SelectedCode = $_GET['SelectedCode'];
} elseif (isset($_POST['SelectedCode'])) {
	$SelectedCode = $_POST['SelectedCode'];
}


if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i = 1;

	//first off validate inputs are sensible

	if (mb_strlen($_POST['paymentterm']) == 0 OR mb_strlen($_POST['paymentterm']) > 2) {
		$InputError = 1;
		prnMsg(_('The payment term code must be selected and be 2 characters or less long'), 'error');
		$Errors[$i] = 'paymentterm';
		$i++;
	}
	if (mb_strlen($_POST['code']) < 1) {
		$InputError = 1;
		prnMsg(_('The PO status code must exist'), 'error');
		$Errors[$i] = 'code';
		$i++;
	}
	if (mb_strlen($_POST['code']) > 6) {
		$InputError = 1;
		prnMsg(_('The PO status code must be 6 characters or less long'), 'error');
		$Errors[$i] = 'code';
		$i++;
	}
	if (empty($_POST['description']) or mb_strlen($_POST['description']) > 50) {
		$InputError = 1;
		prnMsg(_('The PO status description must be 50 characters or less long and not empty'), 'error');
		$Errors[$i] = 'description';
		$i++;
	}


	if (isset($SelectedPaymentTerm) AND isset($SelectedCode) AND $InputError != 1) {

		/*SelectedPaymentTerm and SelectedCode could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE klpostatus SET
						description='" . $_POST['description'] . "'
					WHERE paymentterm = '" . $SelectedPaymentTerm . "'
					AND code = '" . $SelectedCode . "'";

		$Msg = _('The PO status record has been updated') . '.';
	} else if ($InputError != 1) {

		/*SelectedPaymentTerm and SelectedCode is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new PO status form */

		$SQL = "INSERT INTO klpostatus (paymentterm,
										code,
										description)
						VALUES (
							'" . $_POST['paymentterm'] . "',
							'" . $_POST['code'] . "',
							'" . $_POST['description'] . "'
						)";

		$Msg = _('The PO status record has been added') . '.';
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedPaymentTerm);
		unset($SelectedCode);
		unset($_POST['paymentterm']);
		unset($_POST['code']);
		unset($_POST['description']);
	}

} elseif (isset($_GET['delete'])  AND isset($SelectedPaymentTerm) AND isset($SelectedCode)) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN purchorders
	$SQL = "SELECT COUNT(*)
			FROM purchorders
			WHERE purchorders.paymentterms = '" . $SelectedPaymentTerm . "'
			AND purchorders.klstatus = '" . $SelectedCode . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this PO status code because purchase orders have been created referring to this code'), 'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('purchase orders that refer to this PO status code');
	} else {
		//only delete if not used in purchorders
		$SQL = "DELETE FROM klpostatus
				WHERE paymentterm='" . $SelectedPaymentTerm . "'
				AND code='" . $SelectedCode . "'";
		$Result = DB_query($SQL);
		prnMsg(_('The PO status record has been deleted') . '!', 'success');
	}
	//end if PO status code used in purchorders
}

if (!isset($SelectedPaymentTerm) OR !isset($SelectedCode)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPaymentTerm and SelectedCode will exist because they were sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of descriptions will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT klpostatus.paymentterm,
					paymentterms.terms,
					klpostatus.code,
					klpostatus.description
			FROM klpostatus
			LEFT JOIN paymentterms ON klpostatus.paymentterm = paymentterms.termsindicator
			ORDER BY klpostatus.paymentterm, klpostatus.code";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="5"><h3>' . _('Purchase Order Status') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . _('Payment Term') . '</th>
			<th class="SortedColumn">' . _('PO Status Code') . '</th>
			<th class="SortedColumn">' . _('Description') . '</th>
		</tr>
	</thead>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['paymentterm'], ' - ', $MyRow['terms'], '</td>
				<td>', $MyRow['code'], '</td>
				<td>', $MyRow['description'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedPaymentTerm=', $MyRow['paymentterm'], '&amp;SelectedCode=', $MyRow['code'], '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedPaymentTerm=', $MyRow['paymentterm'], '&amp;SelectedCode=', $MyRow['code'], '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this PO status code?') . '\');">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedPaymentTerm) AND isset($SelectedCode)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show all PO Status Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedPaymentTerm) AND isset($SelectedCode)) {
		//editing an existing PO status

		$SQL = "SELECT klpostatus.paymentterm,
						paymentterms.terms,
						klpostatus.code,
						klpostatus.description
					FROM klpostatus
					LEFT JOIN paymentterms ON klpostatus.paymentterm = paymentterms.termsindicator
					WHERE klpostatus.paymentterm='" . $SelectedPaymentTerm . "'
					AND klpostatus.code='" . $SelectedCode . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['paymentterm_desc'] = $MyRow['paymentterm'] . ' - ' . $MyRow['terms'];
		$_POST['code'] = $MyRow['code'];
		$_POST['description'] = $MyRow['description'];


		echo '<input type="hidden" name="SelectedPaymentTerm" value="' . $SelectedPaymentTerm . '" />';
		echo '<input type="hidden" name="SelectedCode" value="' . $SelectedCode . '" />';
		echo '<input type="hidden" name="paymentterm" value="' . $SelectedPaymentTerm . '" />';
		echo '<input type="hidden" name="code" value="' . $_POST['code'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Update PO Status.') . '</legend>';

		echo '<field>
				<label for="paymentterm_desc">' . _('Payment Term') . ':</label>
				<fieldtext>' . $_POST['paymentterm_desc'] . '</fieldtext>
			</field>';

		echo '<field>
				<label for="code">' . _('PO Status Code') . ':</label>
				<fieldtext>' . $_POST['code'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedPaymentTerm AND $SelectedCode only do the else when a new record is being entered

		if (!isset($_POST['paymentterm'])) {
			$_POST['paymentterm'] = '';
		}
		if (!isset($_POST['code'])) {
			$_POST['code'] = '';
		}
		if (!isset($_POST['description'])) {
			$_POST['description'] = '';
		}

		echo '<fieldset>';
		echo '<legend>' . _('New PO Status.') . '</legend>';

		echo '<field>
				<label for="paymentterm">' . _('Payment Term') . ':</label>
				<select name="paymentterm"' . (in_array('paymentterm', $Errors) ? 'class="selecterror"' : '') . '>';

		$SQL_pt = "SELECT termsindicator, terms FROM paymentterms ORDER BY terms";
		$Result_pt = DB_query($SQL_pt);
		echo '<option value="">' . _('Select Payment Term') . '</option>';
		while ($MyRow_pt = DB_fetch_array($Result_pt)) {
			if ($_POST['paymentterm'] == $MyRow_pt['termsindicator']) {
				echo '<option selected="selected" value="' . $MyRow_pt['termsindicator'] . '">' . $MyRow_pt['termsindicator'] . ' - ' . $MyRow_pt['terms'] . '</option>';
			} else {
				echo '<option value="' . $MyRow_pt['termsindicator'] . '">' . $MyRow_pt['termsindicator'] . ' - ' . $MyRow_pt['terms'] . '</option>';
			}
		}

		echo '</select>
				<fieldhelp>' . _('The payment term this status code applies to.') . '</fieldhelp>
			</field>';

		echo '<field>
				<label for="code">' . _('PO Status Code') . ':</label>
				<input type="text" name="code"' . (in_array('code', $Errors) ? 'class="inputerror"' : '') . ' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="" value="' . $_POST['code'] . '" size="8" maxlength="6" />
				<fieldhelp>' . _('A 6 character code to identify this PO status.') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="description">' . _('PO Status Description') . ':</label>
			<input type="text"' . (in_array('description', $Errors) ? 'class="inputerror"' : '') . ' name="description" ' . (isset($SelectedCode) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['description'] . '" title="" size="50" maxlength="50" />
			<fieldhelp>' . _('A description of the PO status is required') . '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
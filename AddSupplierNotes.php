<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Notes');
$ViewTopic = 'AccountsPayable';
$BookMark = 'SupplierNotes';
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');

if (isset($_POST['NoteDate'])) {
	$_POST['NoteDate'] = ConvertSQLDate($_POST['NoteDate']);
}

if (isset($_GET['Id'])) {
	$Id = (int)$_GET['Id'];
} elseif (isset($_POST['Id'])) {
	$Id = (int)$_POST['Id'];
}
if (isset($_POST['SupplierID'])) {
	$SupplierID = $_POST['SupplierID'];
} elseif (isset($_GET['SupplierID'])) {
	$SupplierID = $_GET['SupplierID'];
}

echo '<a class="toplink" href="' . $RootPath . '/SelectSupplier.php?SupplierID=' . $SupplierID . '">' . __('Back to Select Supplier') . '</a>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (trim($_POST['Note']) == '') {
		$InputError = 1;
		prnMsg(__('The supplier note may not be empty'), 'error');
	}

	if (isset($Id) and $InputError != 1) {

		$SQL = "UPDATE suppliernotes SET note='" . $_POST['Note'] . "',
									date='" . FormatDateForSQL($_POST['NoteDate']) . "'
				WHERE supplierid ='" . $SupplierID . "'
				AND noteid='" . $Id . "'";
		$Msg = __('Supplier Notes') . ' ' . $SupplierID . ' ' . __('has been updated');
	} elseif ($InputError != 1) {

		$SQL = "INSERT INTO suppliernotes (supplierid,
										note,
										date)
				VALUES ('" . $SupplierID . "',
						'" . $_POST['Note'] . "',
						'" . FormatDateForSQL($_POST['NoteDate']) . "')";
		$Msg = __('The supplier note record has been added');
	}

	if ($InputError != 1) {
		$Result = DB_query($SQL);

		echo '<br />';
		prnMsg($Msg, 'success');
		unset($Id);
		unset($_POST['Note']);
		unset($_POST['Noteid']);
		unset($_POST['NoteDate']);
	}
} elseif (isset($_GET['delete'])) {

	$SQL = "DELETE FROM suppliernotes
			WHERE noteid='" . $Id . "'
			AND supplierid='" . $SupplierID . "'";
	$Result = DB_query($SQL);

	echo '<br />';
	prnMsg(__('The supplier note record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);
}

if (!isset($Id)) {
	$SQLname = "SELECT suppname FROM suppliers
				WHERE supplierid='" . $SupplierID . "'";
	$Result = DB_query($SQLname);
	$Row = DB_fetch_array($Result);
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . __('Notes for Supplier') . ': <b>' . $Row['suppname'] . '</b></p>';

	$SQL = "SELECT noteid,
					supplierid,
					note,
					date
				FROM suppliernotes
				WHERE supplierid='" . $SupplierID . "'
				ORDER BY date DESC";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
			<tr>
				<th>' . __('Date') . '</th>
				<th>' . __('Note') . '</th>
				<th colspan="2">' . __('Action') . '</th>
			</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>', ConvertSQLDate($MyRow['date']), '</td>
					<td>', nl2br($MyRow['note']), '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Id=', $MyRow['noteid'], '&SupplierID=', $MyRow['supplierid'], '">' . __('Edit') . ' </td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Id=', $MyRow['noteid'], '&SupplierID=', $MyRow['supplierid'], '&delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this supplier note?') . '\');">' . __('Delete') . '</td>
				</tr>';

		}
		//END WHILE LIST LOOP
		echo '</table>';
	}
}
if (isset($Id)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SupplierID=' . $SupplierID . '">' . __('Review all notes for this Supplier') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SupplierID=' . $SupplierID . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {
		//editing an existing
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . __('Notes for Supplier') . ': <b>' . $SupplierID . '</b></p>';

		$SQL = "SELECT noteid,
						supplierid,
						note,
						date
					FROM suppliernotes
					WHERE noteid='" . $Id . "'
						AND supplierid='" . $SupplierID . "'";

		$Result = DB_query($SQL);

		$MyRow = DB_fetch_array($Result);

		$_POST['Noteid'] = $MyRow['noteid'];
		$_POST['Note'] = $MyRow['note'];
		$_POST['NoteDate'] = $MyRow['date'];
		$_POST['SupplierID'] = $MyRow['supplierid'];
		echo '<input type="hidden" name="Id" value="' . $Id . '" />';
		echo '<input type="hidden" name="SupplierID" value="' . $_POST['SupplierID'] . '" />';
		echo '<fieldset>
				<legend>', __('Edit existing supplier note'), '</legend>
				<field>
					<label for="Noteid">' . __('Note ID') . ':</label>
					<fieldtext>' . $_POST['Noteid'] . '</fieldtext>
				</field>';
	} else {
		echo '<fieldset>
				<legend>', __('Create new supplier note'), '</legend>';
	}

	echo '<field>
			<label for="Note">' . __('Supplier Note') . '</label>';
	if (isset($_POST['Note'])) {
		echo '<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32">' . $_POST['Note'] . '</textarea>
			<fieldhelp>', __('Write the supplier note here'), '</fieldhelp>
		</field>';
	} else {
		echo '<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32"></textarea>
			<fieldhelp>', __('Write the supplier note here'), '</fieldhelp>
		</field>';
	}

	echo '<field>
			<label for="NoteDate">' . __('Date') . '</label>';
	if (isset($_POST['NoteDate'])) {
		echo '<input type="date" required name="NoteDate"  value="' . FormatDateForSQL($_POST['NoteDate']) . '" size="11" maxlength="10" />
			<fieldhelp>', __('The date of this note'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="date" required name="NoteDate" value="' . date('Y-m-d') . '" size="11" maxlength="10" />
			<fieldhelp>', __('The date of this note'), '</fieldhelp>
		</field>';
	}
	echo '</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
	</form>';

} //end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');

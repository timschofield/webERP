<?php
/* KLPackaging.php
 * @Author: gemini 2.5 Pro
 * @Date: 2025-11-11
 * @Description: Script to maintain Packaging table (klpackaging).
 * Based on PaymentTerms.php
 */

include(__DIR__ . '/includes/session.php');

$Title = _('KL Packaging Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'KLPackaging';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' .$Title . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedPackagingCode'])) {
	$SelectedPackagingCode = $_GET['SelectedPackagingCode'];
} elseif (isset($_POST['SelectedPackagingCode'])) {
	$SelectedPackagingCode = $_POST['SelectedPackagingCode'];
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

	if (mb_strlen($_POST['packagingcode']) < 1) {
		$InputError = 1;
		prnMsg(_('The packaging code must exist'), 'error');
		$Errors[$i] = 'packagingcode';
		$i++;
	}
	if (mb_strlen($_POST['packagingcode']) > 20) {
		$InputError = 1;
		prnMsg(_('The packaging code must be 20 characters or less long'), 'error');
		$Errors[$i] = 'packagingcode';
		$i++;
	}
	if (empty($_POST['packagingdescription']) or mb_strlen($_POST['packagingdescription']) > 50) {
		$InputError = 1;
		prnMsg(_('The packaging description must be 50 characters or less long and not empty'), 'error');
		$Errors[$i] = 'packagingdescription';
		$i++;
	}


	if (isset($SelectedPackagingCode) and $InputError != 1) {

		/*SelectedPackagingCode could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE klpackaging SET
						packagingdescription='" . $_POST['packagingdescription'] . "'
					WHERE packagingcode = '" . $SelectedPackagingCode . "'";

		$Msg = _('The packaging record has been updated') . '.';
	} else if ($InputError != 1) {

		/*SelectedPackagingCode is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new packaging form */

		$SQL = "INSERT INTO klpackaging (packagingcode,
								packagingdescription)
						VALUES (
							'" . $_POST['packagingcode'] . "',
							'" . $_POST['packagingdescription'] . "'
						)";

		$Msg = _('The packaging record has been added') . '.';
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedPackagingCode);
		unset($_POST['packagingcode']);
		unset($_POST['packagingdescription']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN stockmaster
	$SQL = "SELECT COUNT(*) FROM stockmaster WHERE stockmaster.klpackaging = '" . $SelectedPackagingCode . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this packaging code because stock items have been created referring to this code'), 'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('stock items that refer to this packaging code');
	} else {
		//only delete if not used in stockmaster
		$SQL = "DELETE FROM klpackaging WHERE packagingcode='" . $SelectedPackagingCode . "'";
		$Result = DB_query($SQL);
		prnMsg(_('The packaging record has been deleted') . '!', 'success');
	}
	//end if packaging code used in stockmaster
}

if (!isset($SelectedPackagingCode)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPackagingCode will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of descriptions will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT packagingcode, packagingdescription FROM klpackaging";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="4"><h3>' . _('Packaging Types') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . _('Packaging Code') . '</th>
			<th class="SortedColumn">' . _('Description') . '</th>
		</tr>
	</thead>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['packagingcode'], '</td>
				<td>', $MyRow['packagingdescription'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedPackagingCode=', $MyRow['packagingcode'], '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedPackagingCode=', $MyRow['packagingcode'], '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this packaging code?') . '\');">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedPackagingCode)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show all Packaging Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedPackagingCode)) {
		//editing an existing packaging type

		$SQL = "SELECT packagingcode,
						packagingdescription
					FROM klpackaging
					WHERE packagingcode='" . $SelectedPackagingCode . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['packagingcode'] = $MyRow['packagingcode'];
		$_POST['packagingdescription'] = $MyRow['packagingdescription'];


		echo '<input type="hidden" name="SelectedPackagingCode" value="' . $SelectedPackagingCode . '" />';
		echo '<input type="hidden" name="packagingcode" value="' . $_POST['packagingcode'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Update Packaging Type.') . '</legend>';
		echo '<field>
				<label for="packagingcode">' . _('Packaging Code') . ':</label>
				<fieldtext>' . $_POST['packagingcode'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedPackagingCode only do the else when a new record is being entered

		if (!isset($_POST['packagingcode'])) {
			$_POST['packagingcode'] = '';
		}
		if (!isset($_POST['packagingdescription'])) {
			$_POST['packagingdescription'] = '';
		}

		echo '<fieldset>';
		echo '<legend>' . _('New Packaging Type.') . '</legend>';
		echo '<field>
				<label for="packagingcode">' . _('Packaging Code') . ':</label>
				<input type="text" name="packagingcode"' . (in_array('packagingcode', $Errors) ? 'class="inputerror"' : '') . ' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="" value="' . $_POST['packagingcode'] . '" size="22" maxlength="20" />
				<fieldhelp>' . _('A 20 character code to identify this packaging type. Any alpha-numeric characters can be used') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="packagingdescription">' . _('Packaging Description') . ':</label>
			<input type="text"' . (in_array('packagingdescription', $Errors) ? 'class="inputerror"' : '') . ' name="packagingdescription" ' . (isset($SelectedPackagingCode) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['packagingdescription'] . '" title="" size="50" maxlength="50" />
			<fieldhelp>' . _('A description of the packaging type is required') . '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');
?>
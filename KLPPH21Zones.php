<?php
/* KLPPH21Zones.php
 * @Author: GitHub Copilot
 * @Date: 2026-04-20
 * @Description: Script to maintain HR PPH21 Zones table (hrpph21zones).
 * Based on KLUMKZones.php
 */

include(__DIR__ . '/includes/session.php');

$Title = _('KL PPH21 Zones Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'KLPPH21Zones';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' .$Title . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedPPH21ZoneID'])) {
	$SelectedPPH21ZoneID = $_GET['SelectedPPH21ZoneID'];
} elseif (isset($_POST['SelectedPPH21ZoneID'])) {
	$SelectedPPH21ZoneID = $_POST['SelectedPPH21ZoneID'];
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

	if (mb_strlen($_POST['PPH21ZoneID']) < 1) {
		$InputError = 1;
		prnMsg(_('The PPH21 zone ID must exist'), 'error');
		$Errors[$i] = 'PPH21ZoneID';
		$i++;
	}
	if (mb_strlen($_POST['PPH21ZoneID']) > 20) {
		$InputError = 1;
		prnMsg(_('The PPH21 zone ID must be 20 characters or less long'), 'error');
		$Errors[$i] = 'PPH21ZoneID';
		$i++;
	}
	if (empty($_POST['PPH21ZoneName']) or mb_strlen($_POST['PPH21ZoneName']) > 64) {
		$InputError = 1;
		prnMsg(_('The PPH21 zone name must be 64 characters or less long and not empty'), 'error');
		$Errors[$i] = 'PPH21ZoneName';
		$i++;
	}

	if (isset($SelectedPPH21ZoneID) and $InputError != 1) {

		/*SelectedPPH21ZoneID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE hrpph21zones SET
						pph21zonename='" . $_POST['PPH21ZoneName'] . "'
					WHERE pph21zoneid = '" . $SelectedPPH21ZoneID . "'";

		$Msg = _('The PPH21 zone record has been updated') . '.';
	} else if ($InputError != 1) {

		/*SelectedPPH21ZoneID is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new PPH21 zone form */

		$SQL = "INSERT INTO hrpph21zones (pph21zoneid,
								pph21zonename)
						VALUES (
							'" . $_POST['PPH21ZoneID'] . "',
							'" . $_POST['PPH21ZoneName'] . "'
						)";

		$Msg = _('The PPH21 zone record has been added') . '.';
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedPPH21ZoneID);
		unset($_POST['PPH21ZoneID']);
		unset($_POST['PPH21ZoneName']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN hremployees
	$SQL = "SELECT COUNT(*) FROM hremployees WHERE hremployees.pph21zone = '" . $SelectedPPH21ZoneID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this PPH21 zone because employees have been assigned to this zone'), 'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('employees that are assigned to this PPH21 zone');
	} else {
		//only delete if not used in hremployees
		$SQL = "DELETE FROM hrpph21zones WHERE pph21zoneid='" . $SelectedPPH21ZoneID . "'";
		$Result = DB_query($SQL);
		prnMsg(_('The PPH21 zone record has been deleted') . '!', 'success');
	}
	//end if PPH21 zone used in hremployees
}

if (!isset($SelectedPPH21ZoneID)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPPH21ZoneID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of descriptions will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT pph21zoneid, pph21zonename FROM hrpph21zones ORDER BY pph21zonename";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="4"><h3>' . _('PPH21 Zones') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . _('PPH21 Zone ID') . '</th>
			<th class="SortedColumn">' . _('PPH21 Zone Name') . '</th>
		</tr>
	</thead>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['pph21zoneid'], '</td>
				<td>', $MyRow['pph21zonename'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedPPH21ZoneID=', urlencode($MyRow['pph21zoneid']), '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedPPH21ZoneID=', urlencode($MyRow['pph21zoneid']), '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this PPH21 zone?') . '\');">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedPPH21ZoneID)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show all PPH21 Zone Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedPPH21ZoneID)) {
		//editing an existing PPH21 zone

		$SQL = "SELECT pph21zoneid,
						pph21zonename
					FROM hrpph21zones
					WHERE pph21zoneid='" . $SelectedPPH21ZoneID . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['PPH21ZoneID'] = $MyRow['pph21zoneid'];
		$_POST['PPH21ZoneName'] = $MyRow['pph21zonename'];

		echo '<input type="hidden" name="SelectedPPH21ZoneID" value="' . $SelectedPPH21ZoneID . '" />';
		echo '<input type="hidden" name="PPH21ZoneID" value="' . $_POST['PPH21ZoneID'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Update PPH21 Zone.') . '</legend>';
		echo '<field>
				<label for="PPH21ZoneID">' . _('PPH21 Zone ID') . ':</label>
				<fieldtext>' . $_POST['PPH21ZoneID'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedPPH21ZoneID only do the else when a new record is being entered

		if (!isset($_POST['PPH21ZoneID'])) {
			$_POST['PPH21ZoneID'] = '';
		}
		if (!isset($_POST['PPH21ZoneName'])) {
			$_POST['PPH21ZoneName'] = '';
		}

		echo '<fieldset>';
		echo '<legend>' . _('New PPH21 Zone.') . '</legend>';
		echo '<field>
				<label for="PPH21ZoneID">' . _('PPH21 Zone ID') . ':</label>
				<input type="text" name="PPH21ZoneID"' . (in_array('PPH21ZoneID', $Errors) ? 'class="inputerror"' : '') . ' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="" value="' . $_POST['PPH21ZoneID'] . '" size="22" maxlength="20" />
				<fieldhelp>' . _('A 20 character code to identify this PPH21 zone. Any alpha-numeric characters can be used') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="PPH21ZoneName">' . _('PPH21 Zone Name') . ':</label>
			<input type="text"' . (in_array('PPH21ZoneName', $Errors) ? 'class="inputerror"' : '') . ' name="PPH21ZoneName" ' . (isset($SelectedPPH21ZoneID) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['PPH21ZoneName'] . '" title="" size="66" maxlength="64" />
			<fieldhelp>' . _('The full name of the PPH21 zone is required') . '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');
?>
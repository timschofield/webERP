<?php
/* KLUMKZones.php
 * @Author: GitHub Copilot
 * @Date: 2026-04-20
 * @Description: Script to maintain HR UMK Zones table (hrumkzones).
 * Based on KLBanks.php
 */

include(__DIR__ . '/includes/session.php');

$Title = _('KL UMK Zones Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'KLUMKZones';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' .$Title . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedUMKZoneID'])) {
	$SelectedUMKZoneID = $_GET['SelectedUMKZoneID'];
} elseif (isset($_POST['SelectedUMKZoneID'])) {
	$SelectedUMKZoneID = $_POST['SelectedUMKZoneID'];
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

	if (mb_strlen($_POST['UMKZoneID']) < 1) {
		$InputError = 1;
		prnMsg(_('The UMK zone ID must exist'), 'error');
		$Errors[$i] = 'UMKZoneID';
		$i++;
	}
	if (mb_strlen($_POST['UMKZoneID']) > 20) {
		$InputError = 1;
		prnMsg(_('The UMK zone ID must be 20 characters or less long'), 'error');
		$Errors[$i] = 'UMKZoneID';
		$i++;
	}
	if (empty($_POST['UMKZoneName']) or mb_strlen($_POST['UMKZoneName']) > 64) {
		$InputError = 1;
		prnMsg(_('The UMK zone name must be 64 characters or less long and not empty'), 'error');
		$Errors[$i] = 'UMKZoneName';
		$i++;
	}

	if (isset($SelectedUMKZoneID) and $InputError != 1) {

		/*SelectedUMKZoneID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE hrumkzones SET
						umkzonename='" . $_POST['UMKZoneName'] . "'
					WHERE umkzoneid = '" . $SelectedUMKZoneID . "'";

		$Msg = _('The UMK zone record has been updated') . '.';
	} else if ($InputError != 1) {

		/*SelectedUMKZoneID is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new UMK zone form */

		$SQL = "INSERT INTO hrumkzones (umkzoneid,
								umkzonename)
						VALUES (
							'" . $_POST['UMKZoneID'] . "',
							'" . $_POST['UMKZoneName'] . "'
						)";

		$Msg = _('The UMK zone record has been added') . '.';
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedUMKZoneID);
		unset($_POST['UMKZoneID']);
		unset($_POST['UMKZoneName']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN hremployees
	$SQL = "SELECT COUNT(*) FROM hremployees WHERE hremployees.umkzone = '" . $SelectedUMKZoneID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this UMK zone because employees have been assigned to this zone'), 'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('employees that are assigned to this UMK zone');
	} else {
		//only delete if not used in hremployees
		$SQL = "DELETE FROM hrumkzones WHERE umkzoneid='" . $SelectedUMKZoneID . "'";
		$Result = DB_query($SQL);
		prnMsg(_('The UMK zone record has been deleted') . '!', 'success');
	}
	//end if UMK zone used in hremployees
}

if (!isset($SelectedUMKZoneID)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedUMKZoneID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of descriptions will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT umkzoneid, umkzonename FROM hrumkzones ORDER BY umkzonename";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="4"><h3>' . _('UMK Zones') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . _('UMK Zone ID') . '</th>
			<th class="SortedColumn">' . _('UMK Zone Name') . '</th>
		</tr>
	</thead>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['umkzoneid'], '</td>
				<td>', $MyRow['umkzonename'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedUMKZoneID=', urlencode($MyRow['umkzoneid']), '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedUMKZoneID=', urlencode($MyRow['umkzoneid']), '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this UMK zone?') . '\');">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedUMKZoneID)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show all UMK Zone Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedUMKZoneID)) {
		//editing an existing UMK zone

		$SQL = "SELECT umkzoneid,
						umkzonename
					FROM hrumkzones
					WHERE umkzoneid='" . $SelectedUMKZoneID . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['UMKZoneID'] = $MyRow['umkzoneid'];
		$_POST['UMKZoneName'] = $MyRow['umkzonename'];

		echo '<input type="hidden" name="SelectedUMKZoneID" value="' . $SelectedUMKZoneID . '" />';
		echo '<input type="hidden" name="UMKZoneID" value="' . $_POST['UMKZoneID'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Update UMK Zone.') . '</legend>';
		echo '<field>
				<label for="UMKZoneID">' . _('UMK Zone ID') . ':</label>
				<fieldtext>' . $_POST['UMKZoneID'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedUMKZoneID only do the else when a new record is being entered

		if (!isset($_POST['UMKZoneID'])) {
			$_POST['UMKZoneID'] = '';
		}
		if (!isset($_POST['UMKZoneName'])) {
			$_POST['UMKZoneName'] = '';
		}

		echo '<fieldset>';
		echo '<legend>' . _('New UMK Zone.') . '</legend>';
		echo '<field>
				<label for="UMKZoneID">' . _('UMK Zone ID') . ':</label>
				<input type="text" name="UMKZoneID"' . (in_array('UMKZoneID', $Errors) ? 'class="inputerror"' : '') . ' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="" value="' . $_POST['UMKZoneID'] . '" size="22" maxlength="20" />
				<fieldhelp>' . _('A 20 character code to identify this UMK zone. Any alpha-numeric characters can be used') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="UMKZoneName">' . _('UMK Zone Name') . ':</label>
			<input type="text"' . (in_array('UMKZoneName', $Errors) ? 'class="inputerror"' : '') . ' name="UMKZoneName" ' . (isset($SelectedUMKZoneID) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['UMKZoneName'] . '" title="" size="66" maxlength="64" />
			<fieldhelp>' . _('The full name of the UMK zone is required') . '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');
?>
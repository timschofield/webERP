<?php

require(__DIR__ . '/includes/session.php');

$Title = __('User Authorised Inventory Locations Maintenance');
$ViewTopic = 'Inventory';
$BookMark = 'LocationUsers';
include('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" title="' . __('User Authorised Locations') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['SelectedLocation'])) {
	$SelectedLocation = mb_strtoupper($_POST['SelectedLocation']);
} elseif (isset($_GET['SelectedLocation'])) {
	$SelectedLocation = mb_strtoupper($_GET['SelectedLocation']);
} else {
	$SelectedLocation = '';
}

if (isset($_POST['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif (isset($_GET['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedUser);
	unset($SelectedLocation);
}

if (isset($_POST['Process'])) {
	if ($_POST['SelectedUser'] == '') {
		prnMsg(__('You have not selected any User'), 'error');
		echo '<br />';
		unset($SelectedUser);
		unset($_POST['SelectedUser']);
	}
}

if (isset($_POST['submit'])) {

	$InputError = 0;

	if ($_POST['SelectedLocation'] == '') {
		$InputError = 1;
		prnMsg(__('You have not selected an inventory location to be authorised for this user'), 'error');
		echo '<br />';
		unset($SelectedUser);
	}

	if ($InputError != 1) {

		// First check the user is not being duplicated

		$CheckSql = "SELECT count(*)
			     FROM locationusers
			     WHERE loccode= '" . $_POST['SelectedLocation'] . "'
				 AND userid = '" . $_POST['SelectedUser'] . "'";

		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);

		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(__('The location') . ' ' . $_POST['SelectedLocation'] . ' ' . __('is already authorised for this user'), 'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO locationusers (loccode,
												userid,
												canview,
												canupd)
										VALUES ('" . $_POST['SelectedLocation'] . "',
												'" . $_POST['SelectedUser'] . "',
												'1',
												'1')";

			$Msg = __('User') . ': ' . $_POST['SelectedUser'] . ' ' . __('authority to use the') . ' ' . $_POST['SelectedLocation'] . ' ' . __('location has been changed');
			$Result = DB_query($SQL);
			prnMsg($Msg, 'success');
			unset($_POST['SelectedLocation']);
		}
	}
} elseif (isset($_GET['delete'])) {
	$SQL = "DELETE FROM locationusers
		WHERE loccode='" . $SelectedLocation . "'
		AND userid='" . $SelectedUser . "'";

	$ErrMsg = __('The Location user record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(__('User') . ' ' . $SelectedUser . ' ' . __('has had their authority to use the') . ' ' . $SelectedLocation . ' ' . __('location removed'), 'success');
	unset($_GET['delete']);
} elseif (isset($_GET['ToggleUpdate'])) {
	$SQL = "UPDATE locationusers
			SET canupd='" . $_GET['ToggleUpdate'] . "'
			WHERE loccode='" . $SelectedLocation . "'
			AND userid='" . $SelectedUser . "'";

	$ErrMsg = __('The Location user record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(__('User') . ' ' . $SelectedUser . ' ' . __('has had their authority to update') . ' ' . $SelectedLocation . ' ' . __('location removed'), 'success');
	unset($_GET['ToggleUpdate']);
}

if (!isset($SelectedUser)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLocation will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true. These will call the same page again and allow update/input or deletion of the records*/
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<fieldset>
				<legend>', __('User Selection'), '</legend>
				<field>
					<label for="SelectedUser">' . __('Select User') . ':</label>
					<select name="SelectedUser">';

	$Result = DB_query("SELECT userid,
								realname
						FROM www_users
						ORDER BY userid");

	echo '<option value="">' . __('Not Yet Selected') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedUser) and $MyRow['userid'] == $SelectedUser) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['userid'] . '">' . $MyRow['userid'] . ' - ' . $MyRow['realname'] . '</option>';

	} //end while loop

	echo '</select>
		</field>';

	echo '</fieldset>'; // close main table
	DB_free_result($Result);

	echo '<div class="centre">
			<input type="submit" name="Process" value="' . __('Accept') . '" />
			<input type="reset" name="Cancel" value="' . __('Cancel') . '" />
		</div>';

	echo '</form>';

}

//end of ifs and buts!
if (isset($_POST['process']) or isset($SelectedUser)) {
	$SQLName = "SELECT realname
			FROM www_users
			WHERE userid='" . $SelectedUser . "'";
	$Result = DB_query($SQLName);
	$MyRow = DB_fetch_array($Result);
	$SelectedUserName = $MyRow['realname'];

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';

	$SQL = "SELECT locationusers.loccode,
					canview,
					canupd,
					locations.locationname
			FROM locationusers INNER JOIN locations
			ON locationusers.loccode=locations.loccode
			WHERE locationusers.userid='" . $SelectedUser . "'
			ORDER BY locations.locationname ASC";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="6">' . __('Authorised Inventory Locations for User') . ': ' . $SelectedUserName . '</th>
		</tr>';
	echo '<tr>
			<th>' . __('Code') . '</th>
			<th>' . __('Name') . '</th>
			<th>' . __('View') . '</th>
			<th>' . __('Update') . '</th>
			<th colspan="2"></th>
		</tr>';

	while ($MyRow = DB_fetch_array($Result)) {

		if ($MyRow['canupd'] == 1) {
			$ToggleText = '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedLocation=' . $MyRow['loccode'] . '&amp;ToggleUpdate=0&amp;SelectedUser=' . $SelectedUser . '" onclick="return confirm(\'' . __('Are you sure you wish to remove Update for this location?') . '\');">' . __('Remove Update') . '</a></td>';
		} else {
			$ToggleText = '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedLocation=' . $MyRow['loccode'] . '&amp;ToggleUpdate=1&amp;SelectedUser=' . $SelectedUser . '" onclick="return confirm(\'' . __('Are you sure you wish to add Update for this location?') . '\');">' . __('Add Update') . '</a></td>';
		}

		echo '<tr class="striped_row">
				<td>', $MyRow['loccode'], '</td>
				<td>', $MyRow['locationname'], '</td>
				<td>', $MyRow['canview'], '</td>
				<td>', $MyRow['canupd'], '</td>' .
				$ToggleText . '
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedLocation=', $MyRow['loccode'], '&amp;delete=yes&amp;SelectedUser=' . $SelectedUser . '" onclick="return confirm(\'' . __('Are you sure you wish to un-authorise this location?') . '\');">' . __('Un-authorise') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';

	if (!isset($_GET['delete'])) {


		echo '<fieldset>
				<legend>', __('Location Selection'), '</legend>'; //Main table

		echo '<field>
				<label for="SelectedLocation">' . __('Select Location') . ':</label>
				<select name="SelectedLocation">';

		$Result = DB_query("SELECT loccode,
									locationname
							FROM locations
							WHERE NOT EXISTS (SELECT locationusers.loccode
											FROM locationusers
											WHERE locationusers.userid='" . $SelectedUser . "'
												AND locationusers.loccode=locations.loccode)
							ORDER BY locationname");

		if (!isset($_POST['SelectedLocation'])) {
			echo '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedLocation']) and $MyRow['loccode'] == $_POST['SelectedLocation']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';

		} //end while loop

		echo '</select>
			</field>
		</fieldset>'; // close main table
		DB_free_result($Result);

		echo '<div class="centre">
				<input type="submit" name="submit" value="' . __('Accept') . '" />
				<input type="reset" name="Cancel" value="' . __('Cancel') . '" />
			</div>
			</form>';

	} // end if user wish to delete
}

include('includes/footer.php');

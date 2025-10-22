<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Create GL Budgets');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLBudgets';
include('includes/header.php');

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post" id="createbudget">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', __('Budgets'), '" alt="', __('Budgets'), '" />', ' ', $Title, '
	</p>';

if (isset($_POST['Submit']) or isset($_POST['Update'])) {
	$InputError = 0;

	if ($_POST['StartPeriod'] > $_POST['EndPeriod']) {
		prnMsg(__('The end period cannot be before the start period'), 'error');
		$InputError = 1;
	}

	if ($InputError == 0) {
		if ($_POST['Primary'] == 1) {
			$SQL = "UPDATE glbudgetheaders SET `current`=0";
			$Result = DB_query($SQL);
		}
		if (isset($_POST['Submit'])) {
			$InsertSQL = "INSERT INTO glbudgetheaders (`id`,
														`owner`,
														`name`,
														`description`,
														`startperiod`,
														`endperiod`,
														`current`
													) VALUES (
														NULL,
														'" . $_POST['Owner'] . "',
														'" . $_POST['Name'] . "',
														'" . $_POST['Description'] . "',
														'" . $_POST['StartPeriod'] . "',
														'" . $_POST['EndPeriod'] . "',
														'" . $_POST['Primary'] . "'
													)";
			$InsertResult = DB_query($InsertSQL);

			//Populate glbudgetdetails with zero values

			$HeaderNo = DB_Last_Insert_ID('glbudgetheaders', 'id');

			$PeriodsSQL = "SELECT periodno FROM periods";
			$PeriodsResult = DB_query($PeriodsSQL);

			$AccountsSQL = "SELECT accountcode FROM chartmaster";
			$AccountsResult = DB_query($AccountsSQL);

			while ($AccountsRow = DB_fetch_array($AccountsResult)) {
				$AccountsArray[] = $AccountsRow['accountcode'];
			}
			while ($PeriodsRow = DB_fetch_array($PeriodsResult)) {
				$PeriodsArray[] = $PeriodsRow['periodno'];
			}
			foreach ($AccountsArray as $Account) {
				foreach ($PeriodsArray as $Period) {
					$InsertDetailsSQL = "INSERT INTO glbudgetdetails VALUES (NULL,
																			'" . $HeaderNo . "',
																			'" . $Account . "',
																			'" . $Period . "',
																			0
																		)";
					$InsertDetailsResult = DB_query($InsertDetailsSQL);
				}
			}
		} elseif (isset($_POST['Update'])) {
			$UpdateSQL = "UPDATE glbudgetheaders SET `owner`='" . $_POST['Owner'] . "',
													`name`='" . $_POST['Name'] . "',
													`description`='" . $_POST['Description'] . "',
													`startperiod`='" . $_POST['StartPeriod'] . "',
													`endperiod`='" . $_POST['EndPeriod'] . "',
													`current`='" . $_POST['Primary'] . "'
												WHERE `id`='" . $_POST['ID'] . "'";
			$UpdateResult = DB_query($UpdateSQL);
		}

		if (DB_error_no() == 0) {
			prnMsg(__('The budget header information has been successfully saved'), 'success');
		} else {
			prnMsg(__('There was a problem saving the budget header information'), 'error');
		}
	}

}

if (isset($_GET['Edit'])) {
	$SQL = "SELECT `owner`,
					`name`,
					`description`,
					`startperiod`,
					`endperiod`,
					`current`
				FROM glbudgetheaders
				WHERE id='" . $_GET['Edit'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['Owner'] = $MyRow['owner'];
	$_POST['Name'] = $MyRow['name'];
	$_POST['Description'] = $MyRow['description'];
	$_POST['StartPeriod'] = $MyRow['startperiod'];
	$_POST['EndPeriod'] = $MyRow['endperiod'];
	$_POST['Primary'] = $MyRow['current'];
	echo '<fieldset>
			<legend>', __('Edit Budget Details'), '</legend>
			<field>
				<label for="ID">', __('ID'), '</label>
				<div class="fieldtext">', $_GET['Edit'], '</div>
			</field>';
	echo '<input type="hidden" name="ID" value="', $_GET['Edit'], '" />';
} else {
	$_POST['Owner'] = $_SESSION['UserID'];
	$_POST['Name'] = '';
	$_POST['Description'] = '';
	$_POST['StartPeriod'] = ReportPeriod(__('This Financial Year'), 'From');
	$_POST['EndPeriod'] = ReportPeriod(__('This Financial Year'), 'To');
	$_POST['Primary'] = 0;

	echo '<fieldset>
			<legend>', __('Create New Budget'), '</legend>';
}

$SQL = "SELECT userid, realname FROM www_users";
$Result = DB_query($SQL);

echo '<field>
		<label for="Owner">', __('Budget Owner'), '</label>
		<select name="Owner">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['userid'] == $_POST['Owner']) {
		echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['realname'], ' (', $MyRow['userid'], ')</option>';
	} else {
		echo '<option value="', $MyRow['userid'], '">', $MyRow['realname'], ' (', $MyRow['userid'], ')</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Name">', __('Name of budget'), '</label>
		<input type="text" name="Name" size="100" value="', $_POST['Name'], '" />
	</field>';

echo '<field>
		<label for="Description">', __('Budget Description'), '</label>
		<textarea cols="100" rows="10" name="Description">', $_POST['Description'], '</textarea>
	</field>';

$SQL = "SELECT periodno, lastdate_in_period FROM periods";
$Result = DB_query($SQL);

echo '<field>
		<label for="StartPeriod">', __('Start Period'), '</label>
		<select name="StartPeriod">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['periodno'] == $_POST['StartPeriod']) {
		echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	} else {
		echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
}
echo '</select>
	</field>';

$SQL = "SELECT periodno, lastdate_in_period FROM periods";
$Result = DB_query($SQL);

echo '<field>
		<label for="EndPeriod">', __('End Period'), '</label>
		<select name="EndPeriod">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['periodno'] == $_POST['EndPeriod']) {
		echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	} else {
		echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Primary">', __('Default Budget for reports'), '</label>
		<select name="Primary">';
if ($_POST['Primary'] == 1) {
	echo '<option selected="selected" value="1">', __('Yes'), '</option>';
	echo '<option value="0">', __('No'), '</option>';
} else {
	echo '<option value="1">', __('Yes'), '</option>';
	echo '<option selected="selected" value="0">', __('No'), '</option>';
}
echo '</select>
	</field>';

echo '</fieldset>';

if (isset($_GET['Edit'])) {
	echo '<div class="centre">
			<input type="submit" name="Update" value="', __('Update Details'), '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Submit" value="', __('Save Details'), '" />
		</div>';
}

echo '</form>';

$SQL = "SELECT `id`,
				`owner`,
				`name`,
				`startperiod`,
				`endperiod`,
				`current`
			FROM glbudgetheaders";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<table>
			<thead>
				<tr>
					<th>', __('ID'), '</th>
					<th>', __('Owner'), '</th>
					<th>', __('Name'), '</th>
					<th>', __('Start From Period'), '</th>
					<th>', __('End In Period'), '</th>
					<th>', __('Is Primary?'), '</th>
					<th></th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['current'] == 0) {
			$Current = __('No');
		} else {
			$Current = __('Yes');
		}
		echo '<tr class="striped_row">
				<td>', $MyRow['id'], '</td>
				<td>', $MyRow['owner'], '</td>
				<td>', $MyRow['name'], '</td>
				<td>', MonthAndYearFromPeriodNo($MyRow['startperiod']), '</td>
				<td>', MonthAndYearFromPeriodNo($MyRow['endperiod']), '</td>
				<td>', $Current, '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Edit=', $MyRow['id'], '">', __('Edit'), '</a></td>
			</tr>';
	}
	echo '</tbody>
		</table>';
}

include('includes/footer.php');

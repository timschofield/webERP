<?php

/* HR Positions Maintenance */

require(__DIR__ . '/includes/session.php');

$Title = __('HR Positions');
$ViewTopic = 'HumanResources';
$BookMark = 'HRPositions';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/package.png" title="' . __('Positions') . '" /> ' .
		__('HR Positions Maintenance') . '
	</p>';

if (isset($_GET['SelectedPosition'])) {
	$SelectedPosition = $_GET['SelectedPosition'];
} elseif (isset($_POST['SelectedPosition'])) {
	$SelectedPosition = $_POST['SelectedPosition'];
}

if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (trim($_POST['PositionTitle']) == '') {
		$InputError = 1;
		prnMsg(__('The position title must not be empty'), 'error');
	}
	if (trim($_POST['PositionCode']) == '') {
		$InputError = 1;
		prnMsg(__('The position code must not be empty'), 'error');
	}

	if (isset($SelectedPosition) AND $InputError != 1) {
		$SQL = "UPDATE hrpositions SET
					positioncode = '" . $_POST['PositionCode'] . "',
					positiontitle = '" . $_POST['PositionTitle'] . "',
					departmentid = " . (int)$_POST['DepartmentID'] . ",
					reportstopositionid = " . (int)$_POST['ReportsToPositionID'] . ",
					paygradeid = " . (int)$_POST['PayGradeID'] . ",
					positionstatus = '" . $_POST['PositionStatus'] . "',
					fte = '" . (float)$_POST['FTE'] . "',
					jobdescription = '" . $_POST['JobDescription'] . "',
					requirements = '" . $_POST['Requirements'] . "',
					active = '" . (int)$_POST['Active'] . "'
				WHERE positionid = '" . $SelectedPosition . "'";

		$ErrMsg = __('Could not update the position because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('Position') . ' ' . $_POST['PositionTitle'] . ' ' . __('has been updated'), 'success');

	} elseif ($InputError != 1) {
		$SQL = "SELECT COUNT(*) FROM hrpositions WHERE positioncode = '" . $_POST['PositionCode'] . "'";
		$Result = DB_query($SQL);
		$Row = DB_fetch_row($Result);

		if ($Row[0] > 0) {
			$InputError = 1;
			prnMsg(__('A position with this code already exists'), 'error');
		} else {
			$SQL = "INSERT INTO hrpositions (
						positioncode,
						positiontitle,
						departmentid,
						reportstopositionid,
						paygradeid,
						positionstatus,
						fte,
						jobdescription,
						requirements,
						active
					) VALUES (
						'" . $_POST['PositionCode'] . "',
						'" . $_POST['PositionTitle'] . "',
						" . (int)$_POST['DepartmentID'] . ",
						" . (int)$_POST['ReportsToPositionID'] . ",
						" . (int)$_POST['PayGradeID'] . ",
						'" . $_POST['PositionStatus'] . "',
						'" . (float)$_POST['FTE'] . "',
						'" . $_POST['JobDescription'] . "',
						'" . $_POST['Requirements'] . "',
						1
					)";

			$ErrMsg = __('Could not add the position because');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(__('Position') . ' ' . $_POST['PositionTitle'] . ' ' . __('has been added'), 'success');
		}
	}

	if ($InputError != 1) {
		unset($SelectedPosition);
		unset($_POST);
	}

} elseif (isset($_GET['delete'])) {
	$CancelDelete = 0;

	$SQL = "SELECT COUNT(*) FROM hremployees WHERE positionid = '" . $SelectedPosition . "'";
	$Result = DB_query($SQL);
	$Row = DB_fetch_row($Result);

	if ($Row[0] > 0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this position because there are') . ' ' . $Row[0] . ' ' . __('employees assigned to it'), 'error');
	}

	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM hrpositions WHERE positionid = '" . $SelectedPosition . "'";
		$ErrMsg = __('Could not delete the position because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('Position has been deleted'), 'success');
		unset($SelectedPosition);
	}
}

echo '<br />';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedPosition)) {
	$SQL = "SELECT * FROM hrpositions WHERE positionid = '" . $SelectedPosition . "'";
	$Result = DB_query($SQL);
	$Row = DB_fetch_array($Result);

	foreach ($Row as $Key => $Value) {
		if (!is_numeric($Key)) {
			$_POST[$Key] = $Value;
		}
	}

	echo '<input type="hidden" name="SelectedPosition" value="' . $SelectedPosition . '" />';
	echo '<fieldset>';
	echo '<legend>' . __('Edit Position') . '</legend>';
} else {
	echo '<fieldset>';
	echo '<legend>' . __('Add New Position') . '</legend>';
}

echo '<field>
		<label for="PositionCode">' . __('Position Code') . ':</label>
		<input type="text" name="PositionCode" required="required" size="20" maxlength="20" value="' . (isset($_POST['positioncode']) ? $_POST['positioncode'] : '') . '" />
	</field>';

echo '<field>
		<label for="PositionTitle">' . __('Position Title') . ':</label>
		<input type="text" name="PositionTitle" required="required" size="50" maxlength="100" value="' . (isset($_POST['positiontitle']) ? $_POST['positiontitle'] : '') . '" />
	</field>';

echo '<field>
		<label for="DepartmentID">' . __('Department') . ':</label>
		<select name="DepartmentID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT departmentid, description FROM departments WHERE active = 1 ORDER BY description";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	if (isset($_POST['departmentid']) AND $_POST['departmentid'] == $Row['departmentid']) {
		echo '<option selected="selected" value="' . $Row['departmentid'] . '">' . $Row['description'] . '</option>';
	} else {
		echo '<option value="' . $Row['departmentid'] . '">' . $Row['description'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="ReportsToPositionID">' . __('Reports To Position') . ':</label>
		<select name="ReportsToPositionID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT positionid, positiontitle FROM hrpositions WHERE active = 1";
if (isset($SelectedPosition)) {
	$SQL .= " AND positionid != '" . $SelectedPosition . "'";
}
$SQL .= " ORDER BY positiontitle";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	if (isset($_POST['reportstopositionid']) AND $_POST['reportstopositionid'] == $Row['positionid']) {
		echo '<option selected="selected" value="' . $Row['positionid'] . '">' . $Row['positiontitle'] . '</option>';
	} else {
		echo '<option value="' . $Row['positionid'] . '">' . $Row['positiontitle'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="PayGradeID">' . __('Pay Grade') . ':</label>
		<select name="PayGradeID">';
echo '<option value="0">' . __('None') . '</option>';
$SQL = "SELECT paygradeid, paygradename FROM hrpaygrades WHERE active = 1 ORDER BY paygradename";
$Result = DB_query($SQL);
while ($Row = DB_fetch_array($Result)) {
	if (isset($_POST['paygradeid']) AND $_POST['paygradeid'] == $Row['paygradeid']) {
		echo '<option selected="selected" value="' . $Row['paygradeid'] . '">' . $Row['paygradename'] . '</option>';
	} else {
		echo '<option value="' . $Row['paygradeid'] . '">' . $Row['paygradename'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="PositionStatus">' . __('Position Status') . ':</label>
		<select name="PositionStatus">
			<option value="Open"' . (isset($_POST['positionstatus']) && $_POST['positionstatus'] == 'Open' ? ' selected="selected"' : '') . '>' . __('Open') . '</option>
			<option value="Filled"' . (isset($_POST['positionstatus']) && $_POST['positionstatus'] == 'Filled' ? ' selected="selected"' : '') . '>' . __('Filled') . '</option>
			<option value="Frozen"' . (isset($_POST['positionstatus']) && $_POST['positionstatus'] == 'Frozen' ? ' selected="selected"' : '') . '>' . __('Frozen') . '</option>
			<option value="Eliminated"' . (isset($_POST['positionstatus']) && $_POST['positionstatus'] == 'Eliminated' ? ' selected="selected"' : '') . '>' . __('Eliminated') . '</option>
		</select>
	</field>';

echo '<field>
		<label for="FTE">' . __('FTE') . ':</label>
		<input type="number" name="FTE" min="0" max="9.99" step="0.01" value="' . (isset($_POST['fte']) ? $_POST['fte'] : '1.00') . '" />
	</field>';

echo '<field>
		<label for="JobDescription">' . __('Job Description') . ':</label>
		<textarea name="JobDescription" rows="5" cols="60">' . (isset($_POST['jobdescription']) ? $_POST['jobdescription'] : '') . '</textarea>
	</field>';

echo '<field>
		<label for="Requirements">' . __('Requirements') . ':</label>
		<textarea name="Requirements" rows="5" cols="60">' . (isset($_POST['requirements']) ? $_POST['requirements'] : '') . '</textarea>
	</field>';

if (isset($SelectedPosition)) {
	echo '<field>
			<label for="Active">' . __('Active') . ':</label>
			<input type="checkbox" name="Active" value="1"' . (isset($_POST['active']) && $_POST['active'] == 1 ? ' checked="checked"' : '') . ' />
		</field>';
}

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Submit') . '" />
	</div>
	</form>';

echo '<br />';

if (isset($SelectedPosition)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Show All Positions') . '</a></div>';
}

if (!isset($SelectedPosition)) {
	$SQL = "SELECT p.positionid,
				p.positioncode,
				p.positiontitle,
				d.description,
				pg.paygradename,
				p.positionstatus,
				rpt.positiontitle AS reportstopositiontitle,
				p.fte,
				p.active
			FROM hrpositions p
			LEFT JOIN departments d ON p.departmentid = d.departmentid
			LEFT JOIN hrpaygrades pg ON p.paygradeid = pg.paygradeid
			LEFT JOIN hrpositions rpt ON p.reportstopositionid = rpt.positionid
			ORDER BY p.positioncode";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">';
		echo '<tr>
				<th>' . __('Code') . '</th>
				<th>' . __('Position Title') . '</th>
				<th>' . __('Department') . '</th>
				<th>' . __('Reports To') . '</th>
				<th>' . __('Pay Grade') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('FTE') . '</th>
				<th>' . __('Active') . '</th>
				<th colspan="2">&nbsp;</th>
			</tr>';

		while ($Row = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>' . $Row['positioncode'] . '</td>
					<td>' . $Row['positiontitle'] . '</td>
					<td>' . $Row['description'] . '</td>
					<td>' . $Row['reportstopositiontitle'] . '</td>
					<td>' . $Row['paygradename'] . '</td>
					<td>' . $Row['positionstatus'] . '</td>
					<td>' . $Row['fte'] . '</td>
					<td>' . ($Row['active'] ? __('Yes') : __('No')) . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedPosition=' . $Row['positionid'] . '">' . __('Edit') . '</a></td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedPosition=' . $Row['positionid'] . '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this position?') . '\');">' . __('Delete') . '</a></td>
				</tr>';
		}

		echo '</table>';
	}
}

include(__DIR__ . '/includes/footer.php');

?>

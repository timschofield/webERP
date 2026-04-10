<?php

/* HR Pay Grades & Steps Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Pay Grades & Steps');
$ViewTopic = 'HumanResources';
$BookMark = 'PayGrades';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" title="' . __('Pay Grades') . '" /> ' .
		__('Pay Grades & Steps Management') . '
	</p>';

// Handle form submissions
if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (trim($_POST['GradeCode']) == '') {
		$InputError = 1;
		prnMsg(__('The grade code must not be empty'), 'error');
	}
	if (trim($_POST['GradeTitle']) == '') {
		$InputError = 1;
		prnMsg(__('The grade title must not be empty'), 'error');
	}

	if ($InputError != 1) {
		if (isset($_POST['GradeID']) && $_POST['GradeID'] > 0) {
			// Update existing grade
			$SQL = "UPDATE hrpaygrades SET
						paygradecode = '" . $_POST['GradeCode'] . "',
						paygradename = '" . $_POST['GradeTitle'] . "',
						minsalary = " . filter_var($_POST['MinAnnualSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						midsalary = " . filter_var($_POST['MidAnnualSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						maxsalary = " . filter_var($_POST['MaxAnnualSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						active = " . (isset($_POST['Active']) ? 1 : 0) . "
					WHERE paygradeid = " . (int)$_POST['GradeID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Pay grade has been updated successfully'), 'success');
			}
		} else {
			// Insert new grade
			$SQL = "INSERT INTO hrpaygrades (
						paygradecode, paygradename,
						minsalary, midsalary, maxsalary,
						active
					) VALUES (
						'" . $_POST['GradeCode'] . "',
						'" . $_POST['GradeTitle'] . "',
						" . filter_var($_POST['MinAnnualSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						" . filter_var($_POST['MidAnnualSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						" . filter_var($_POST['MaxAnnualSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . ",
						" . (isset($_POST['Active']) ? 1 : 0) . "
					)";
			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Pay grade has been created successfully'), 'success');
			}
		}
		unset($_POST['GradeID']);
	}
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['GradeID'])) {
	$SQL = "DELETE FROM hrpaygrades WHERE paygradeid = " . (int)$_GET['GradeID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Pay grade has been deleted successfully'), 'success');
	}
}

// Handle step submission
if (isset($_POST['SubmitStep'])) {
	$InputError = 0;

	if ((int)$_POST['GradeID'] == 0) {
		$InputError = 1;
		prnMsg(__('Please select a pay grade'), 'error');
	}
	if ((int)$_POST['StepNumber'] == 0) {
		$InputError = 1;
		prnMsg(__('The step number must be specified'), 'error');
	}

	if ($InputError != 1) {
		if (isset($_POST['StepID']) && $_POST['StepID'] > 0) {
			// Update existing step
			$SQL = "UPDATE hrpaysteps SET
						paygradeid = " . (int)$_POST['GradeID'] . ",
						stepnumber = " . (int)$_POST['StepNumber'] . ",
						stepamount = " . filter_var($_POST['AnnualSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . "
					WHERE paystepid = " . (int)$_POST['StepID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Pay step has been updated successfully'), 'success');
			}
		} else {
			// Insert new step
			$SQL = "INSERT INTO hrpaysteps (
						paygradeid, stepnumber, stepamount
					) VALUES (
						" . (int)$_POST['GradeID'] . ",
						" . (int)$_POST['StepNumber'] . ",
						" . filter_var($_POST['AnnualSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) . "
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Pay step has been created successfully'), 'success');
			}
		}
		unset($_POST['StepID']);
	}
}

// Handle step delete
if (isset($_GET['deletestep']) && isset($_GET['StepID'])) {
	$SQL = "DELETE FROM hrpaysteps WHERE paystepid = " . (int)$_GET['StepID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Pay step has been deleted successfully'), 'success');
	}
}

// Add/Edit form for pay grades - show by default or when edit/new is set
if (!isset($_GET['steps'])) {
	$GradeID = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
	$GradeCode = '';
	$GradeTitle = '';
	$MinAnnualSalary = 0;
	$MidAnnualSalary = 0;
	$MaxAnnualSalary = 0;
	$Active = 1;

	if ($GradeID > 0) {
		$SQL = "SELECT * FROM hrpaygrades WHERE paygradeid = " . $GradeID;
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) > 0) {
			$Row = DB_fetch_array($Result);
			$GradeCode = $Row['paygradecode'];
			$GradeTitle = $Row['paygradename'];
			$MinAnnualSalary = $Row['minsalary'];
			$MidAnnualSalary = $Row['midsalary'];
			$MaxAnnualSalary = $Row['maxsalary'];
			$Active = $Row['active'];
		}
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>' . ($GradeID > 0 ? __('Edit Pay Grade') : __('Add Pay Grade')) . '</legend>';

	if ($GradeID > 0) {
		echo '<input type="hidden" name="GradeID" value="' . $GradeID . '" />';
	}

	echo '<field>
				<label for="GradeCode">' . __('Grade Code') . ':</label>
				<input type="text" name="GradeCode" value="' . $GradeCode . '" size="10" maxlength="10" required="required" />
			</field>

			<field>
				<label for="GradeTitle">' . __('Grade Title') . ':</label>
				<input type="text" name="GradeTitle" value="' . $GradeTitle . '" size="50" maxlength="100" required="required" />
			</field>

			<field>
				<label for="MinAnnualSalary">' . __('Minimum Annual Salary') . ':</label>
				<input type="number" name="MinAnnualSalary" value="' . $MinAnnualSalary . '" step="0.01" />
			</field>

			<field>
				<label for="MidAnnualSalary">' . __('Mid Annual Salary') . ':</label>
				<input type="number" name="MidAnnualSalary" value="' . $MidAnnualSalary . '" step="0.01" />
			</field>

			<field>
				<label for="MaxAnnualSalary">' . __('Maximum Annual Salary') . ':</label>
				<input type="number" name="MaxAnnualSalary" value="' . $MaxAnnualSalary . '" step="0.01" />
			</field>

			<field>
				<label for="Active">' . __('Active') . ':</label>
				<input type="checkbox" name="Active" value="1"' . ($Active ? ' checked="checked"' : '') . ' />
			</field>
		</fieldset>';
echo '<div class="centre">
			<input type="submit" name="Submit" value="' . __('Save') . '" />
		</div>';
echo '</form>';
}

$SQL = "SELECT * FROM hrpaygrades ORDER BY paygradecode";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	// Display pay grades list
	echo '<table class="selection">
			<tr>
				<th>' . __('Grade Code') . '</th>
				<th>' . __('Grade Title') . '</th>
				<th>' . __('Min Salary') . '</th>
				<th>' . __('Mid Salary') . '</th>
				<th>' . __('Max Salary') . '</th>
				<th>' . __('Active') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>';

	while ($Row = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $Row['paygradecode'] . '</td>
				<td>' . $Row['paygradename'] . '</td>
				<td class="number">' . locale_number_format($Row['minsalary'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($Row['midsalary'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($Row['maxsalary'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . ($Row['active'] ? __('Yes') : __('No')) . '</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . $Row['paygradeid'] . '">' . __('Edit') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?steps=' . $Row['paygradeid'] . '">' . __('Steps') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=1&GradeID=' . $Row['paygradeid'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete this pay grade?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}

	echo '</table>';
}

// Display pay steps for a grade
if (isset($_GET['steps'])) {
	$GradeID = (int)$_GET['steps'];

	$SQL = "SELECT * FROM hrpaygrades WHERE paygradeid = " . $GradeID;
	$Result = DB_query($SQL);
	$GradeRow = DB_fetch_array($Result);

	echo '<br /><h3>' . __('Pay Steps for') . ': ' . $GradeRow['paygradename'] . '</h3>';

	echo '<table class="selection">
			<tr>
				<th>' . __('Step') . '</th>
				<th>' . __('Step Amount') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>';

	$SQL = "SELECT * FROM hrpaysteps WHERE paygradeid = " . $GradeID . " ORDER BY stepnumber";
	$Result = DB_query($SQL);

	while ($Row = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $Row['stepnumber'] . '</td>
				<td class="number">' . locale_number_format($Row['stepamount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?editstep=' . $Row['paystepid'] . '&steps=' . $GradeID . '">' . __('Edit') . '</a> |
					<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?deletestep=1&StepID=' . $Row['paystepid'] . '&steps=' . $GradeID . '" onclick="return confirm(\'' . __('Are you sure you want to delete this step?') . '\');">' . __('Delete') . '</a>
				</td>
			</tr>';
	}

	echo '</table>';

	// Add/Edit step form - show by default
	$StepID = isset($_GET['editstep']) ? (int)$_GET['editstep'] : 0;
	$StepNumber = '';
	$AnnualSalary = 0;

	if ($StepID > 0) {
		$SQL = "SELECT * FROM hrpaysteps WHERE paystepid = " . $StepID;
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) > 0) {
			$Row = DB_fetch_array($Result);
			$StepNumber = $Row['stepnumber'];
			$AnnualSalary = $Row['stepamount'];
		}
	}

	echo '<br /><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?steps=' . $GradeID . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="GradeID" value="' . $GradeID . '" />';

	if ($StepID > 0) {
		echo '<input type="hidden" name="StepID" value="' . $StepID . '" />';
	}

	echo '<fieldset>
			<legend>' . ($StepID > 0 ? __('Edit Pay Step') : __('Add Pay Step')) . '</legend>
			<field>
				<label for="StepNumber">' . __('Step Number') . ':</label>
				<input type="number" name="StepNumber" value="' . $StepNumber . '" min="1" required="required" />
			</field>
			<field>
				<label for="AnnualSalary">' . __('Step Amount') . ':</label>
				<input type="number" name="AnnualSalary" value="' . $AnnualSalary . '" step="0.01" required="required" />
			</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="SubmitStep" value="' . __('Save') . '" />
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Back to Pay Grades') . '</a>
		</div>
		</form>';
}

include(__DIR__ . '/includes/footer.php');

?>

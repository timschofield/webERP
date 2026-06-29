<?php
/********************************************************************************
 * 
 * KL RICARD: 	Changed the title and labels from Annual Salary to Monthly Salary, as the amounts entered are monthly salaries, not annual.
 * 				DB fields and SQL query continue with the webERP standard of Annual, but for us should be considered monthly.
 * 
 *******************************************************************************/

/* HR Pay Grades & Steps Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Pay Grades & Steps');
$ViewTopic = 'HumanResources';
$BookMark = 'PayGrades';

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/UIGeneralFunctions.php');

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
	if (empty($_POST['CurrencyCode'])) {
		$InputError = 1;
		prnMsg(__('Please select a currency code'), 'error');
	}

	if ($InputError != 1) {
		$CurrencyCode = DB_escape_string($_POST['CurrencyCode']);

		$TunjanganOperational = filter_var(
			$_POST['TunjanganOperational'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$TunjanganJabatan = filter_var(
			$_POST['TunjanganJabatan'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$VariableProfitPreviousMonth = filter_var(
			$_POST['VariableProfitPreviousMonth'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$VariableProfitPreviousMonthPartners = filter_var(
			$_POST['VariableProfitPreviousMonthPartners'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$CommissionSpgSenior = filter_var(
			$_POST['CommissionSpgSenior'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$CommissionSpgJunior = filter_var(
			$_POST['CommissionSpgJunior'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$CommissionSpgMiddle = filter_var(
			$_POST['CommissionSpgMiddle'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$MinAnnualSalary = filter_var(
			$_POST['MinAnnualSalary'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$MidAnnualSalary = filter_var(
			$_POST['MidAnnualSalary'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
		$MaxAnnualSalary = filter_var(
			$_POST['MaxAnnualSalary'],
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);

		if (isset($_POST['GradeID']) && $_POST['GradeID'] > 0) {
			// Update existing grade
			$SQL = "UPDATE hrpaygrades SET
						paygradecode = '" . $_POST['GradeCode'] . "',
						paygradename = '" . $_POST['GradeTitle'] . "',
						currencycode = '" . $CurrencyCode . "',
						umkbased = " . (int)$_POST['UmkBased'] . ",
						tunjanganoperational = " . $TunjanganOperational . ",
						tunjanganjabatan = " . $TunjanganJabatan . ",
						variableprofitpreviousmonth = " . $VariableProfitPreviousMonth . ",
						variableprofitpreviousmonthpartners = " . $VariableProfitPreviousMonthPartners . ",
						commissionspgsenior = " . $CommissionSpgSenior . ",
						commissionspgjunior = " . $CommissionSpgJunior . ",
						commissionspgmiddle = " . $CommissionSpgMiddle . ",
						minsalary = " . $MinAnnualSalary . ",
						midsalary = " . $MidAnnualSalary . ",
						maxsalary = " . $MaxAnnualSalary . ",
						active = " . (isset($_POST['Active']) ? 1 : 0) . "
					WHERE paygradeid = " . (int)$_POST['GradeID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Pay grade has been updated successfully'), 'success');
			}
		} else {
			// Insert new grade
			$SQL = "INSERT INTO hrpaygrades (
						paygradecode, paygradename, currencycode,
						umkbased, tunjanganoperational, tunjanganjabatan,
						variableprofitpreviousmonth, variableprofitpreviousmonthpartners,
						commissionspgsenior, commissionspgjunior, commissionspgmiddle,
						minsalary, midsalary, maxsalary, active
					) VALUES (
						'" . $_POST['GradeCode'] . "',
						'" . $_POST['GradeTitle'] . "',
						'" . $CurrencyCode . "',
						" . (int)$_POST['UmkBased'] . ",
						" . $TunjanganOperational . ",
						" . $TunjanganJabatan . ",
						" . $VariableProfitPreviousMonth . ",
						" . $VariableProfitPreviousMonthPartners . ",
						" . $CommissionSpgSenior . ",
						" . $CommissionSpgJunior . ",
						" . $CommissionSpgMiddle . ",
						" . $MinAnnualSalary . ",
						" . $MidAnnualSalary . ",
						" . $MaxAnnualSalary . ",
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
	$CurrencyCode = $_SESSION['CompanyRecord']['currencydefault'];
	$UmkBased = 0;
	$TunjanganOperational = 0;
	$TunjanganJabatan = 0;
	$VariableProfitPreviousMonth = 0;
	$VariableProfitPreviousMonthPartners = 0;
	$CommissionSpgSenior = 0;
	$CommissionSpgJunior = 0;
	$CommissionSpgMiddle = 0;
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
			$CurrencyCode = $Row['currencycode'];
			$UmkBased = $Row['umkbased'];
			$TunjanganOperational = $Row['tunjanganoperational'];
			$TunjanganJabatan = $Row['tunjanganjabatan'];
			$VariableProfitPreviousMonth = $Row['variableprofitpreviousmonth'];
			$VariableProfitPreviousMonthPartners = $Row['variableprofitpreviousmonthpartners'];
			$CommissionSpgSenior = $Row['commissionspgsenior'];
			$CommissionSpgJunior = $Row['commissionspgjunior'];
			$CommissionSpgMiddle = $Row['commissionspgmiddle'];
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
				<label for="CurrencyCode">' . __('Currency') . ':</label>
				<select name="CurrencyCode" required="required">';

	$SQL = "SELECT currabrev, currency FROM currencies ORDER BY currency";
	$Result = DB_query($SQL);
	while ($CurrencyRow = DB_fetch_array($Result)) {
		echo '<option value="' . htmlspecialchars($CurrencyRow['currabrev'], ENT_QUOTES, 'UTF-8') . '"' .
			($CurrencyCode == $CurrencyRow['currabrev'] ? ' selected="selected"' : '') .
			'>' . htmlspecialchars($CurrencyRow['currency'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($CurrencyRow['currabrev'], ENT_QUOTES, 'UTF-8') . ')</option>';
	}

	echo '</select>
			</field>';

	echo FieldToSelectFromTwoOptions('0', __('No'), '1', __('Yes'),
		'UmkBased', $UmkBased, __('Pay based on UMK?'));

	echo '<fieldset>
			<legend>' . __('For Pay Grade NOT UMK Based:') . '</legend>
			<field>
				<label for="MinAnnualSalary">' . __('Minimum Monthly Salary') . ':</label>
				<input type="number" name="MinAnnualSalary" value="' . $MinAnnualSalary . '" step="0.01" />
			</field>

			<field>
				<label for="MidAnnualSalary">' . __('Mid Monthly Salary') . ':</label>
				<input type="number" name="MidAnnualSalary" value="' . $MidAnnualSalary . '" step="0.01" />
			</field>

			<field>
				<label for="MaxAnnualSalary">' . __('Maximum Monthly Salary') . ':</label>
				<input type="number" name="MaxAnnualSalary" value="' . $MaxAnnualSalary . '" step="0.01" />
			</field>';

		echo FieldToSelectOneNumber('TunjanganOperational', $TunjanganOperational, 15, 15, __('Tunjangan Operasional'), '', '', '', false);
		echo FieldToSelectOneNumber('TunjanganJabatan', $TunjanganJabatan, 15, 15, __('Tunjangan Jabatan'), '', '', '', false);
		echo FieldToSelectOneNumber('VariableProfitPreviousMonth', $VariableProfitPreviousMonth, 5, 5, __('% Net Company Profit Previous Month -1'), '', '', '', false);
		echo FieldToSelectOneNumber('VariableProfitPreviousMonthPartners', $VariableProfitPreviousMonthPartners, 5, 5, __('% Net Company Profit Previous Month -1 as Partners'), '', '', '', false);

	echo '</fieldset>';

	echo '<fieldset>
			<legend>' . __('For SPG Pay Grades:') . '</legend>';

		echo FieldToSelectOneNumber('CommissionSpgSenior', $CommissionSpgSenior, 5, 5, __('% Commission Sales SPG Senior'), '', '', '', false);
		echo FieldToSelectOneNumber('CommissionSpgJunior', $CommissionSpgJunior, 5, 5, __('% Commission Sales SPG Junior'), '', '', '', false);
		echo FieldToSelectOneNumber('CommissionSpgMiddle', $CommissionSpgMiddle, 5, 5, __('% Commission Sales SPG Middle'), '', '', '', false);

	echo '</fieldset>';

	echo '<field>
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
				<th>' . __('Currency') . '</th>
				<th>' . __('UMK Based?') . '</th>
				<th>' . __('Min Salary') . '</th>
				<th>' . __('Mid Salary') . '</th>
				<th>' . __('Max Salary') . '</th>
				<th>' . __('Tunjangan Operasional') . '</th>
				<th>' . __('Tunjangan Jabatan') . '</th>
				<th>' . __('% Net Company Profit Previous Month -1') . '</th>
				<th>' . __('% Net Company Profit Previous Month -1 as Partners') . '</th>
				<th>' . __('% Commission Sales SPG Senior') . '</th>
				<th>' . __('% Commission Sales SPG Junior') . '</th>
				<th>' . __('% Commission Sales SPG Middle') . '</th>
				<th>' . __('Active') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>';

	while ($Row = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $Row['paygradecode'] . '</td>
				<td>' . $Row['paygradename'] . '</td>
				<td>' . htmlspecialchars((string)$Row['currencycode'], ENT_QUOTES, 'UTF-8') . '</td>
				<td>' . ($Row['umkbased'] ? __('Yes') : __('No')) . '</td>
				<td class="number">' .
					locale_number_format($Row['minsalary'], 0) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['midsalary'], 0) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['maxsalary'], 0) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['tunjanganoperational'], 0) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['tunjanganjabatan'], 0) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['variableprofitpreviousmonth'], 2) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['variableprofitpreviousmonthpartners'], 2) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['commissionspgsenior'], 2) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['commissionspgjunior'], 2) .
				'</td>
				<td class="number">' .
					locale_number_format($Row['commissionspgmiddle'], 2) .
				'</td>
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

<?php

/*
 * CRM Industry Maintenance
 *
 * Maintains the list of industries used by the customer CRM details section.
 * Active industries appear in the Industry dropdown on Customers.php.
 */

require(__DIR__ . '/includes/session.php');

$Title = __('CRM Industry Maintenance');
$ViewTopic = 'CRM';
$BookMark = 'CRMIndustries';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('CRM Industries') . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_GET['SelectedIndustry'])) {
	$SelectedIndustry = (int)$_GET['SelectedIndustry'];
} elseif (isset($_POST['SelectedIndustry'])) {
	$SelectedIndustry = (int)$_POST['SelectedIndustry'];
}

if (isset($_POST['submit'])) {

	$InputError = 0;

	if (trim($_POST['IndustryName']) == '') {
		$InputError = 1;
		prnMsg(__('The industry name may not be empty'), 'error');
	} elseif (mb_strlen($_POST['IndustryName']) > 60) {
		$InputError = 1;
		prnMsg(__('The industry name must be sixty characters or less long'), 'error');
	}

	if (!isset($SelectedIndustry) and $InputError != 1) {
		$SQL = "SELECT industryid
				FROM crmindustries
				WHERE industryname='" . $_POST['IndustryName'] . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) > 0) {
			$InputError = 1;
			prnMsg(__('An industry with this name already exists'), 'error');
		}
	}

	if (isset($SelectedIndustry) and $InputError != 1) {
		$SQL = "UPDATE crmindustries SET
					industryname='" . $_POST['IndustryName'] . "',
					active='" . (isset($_POST['Active']) ? '1' : '0') . "'
				WHERE industryid='" . $SelectedIndustry . "'";
		$Msg = __('Industry') . ' ' . $_POST['IndustryName'] . ' ' . __('has been updated');
	} elseif ($InputError != 1) {
		$SQL = "INSERT INTO crmindustries (industryname,
					active)
				VALUES ('" . $_POST['IndustryName'] . "',
					'1')";
		$Msg = __('Industry') . ' ' . $_POST['IndustryName'] . ' ' . __('has been added');
	}

	if ($InputError != 1) {
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedIndustry);
		unset($_POST['IndustryName']);
	}

} elseif (isset($_GET['delete'])) {
	$DeleteIndustryID = (int)$_GET['delete'];

	$SQL = "SELECT industryname
			FROM crmindustries
			WHERE industryid='" . $DeleteIndustryID . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(__('The selected industry does not exist'), 'error');
	} else {
		$MyRow = DB_fetch_array($Result);
		$IndustryName = $MyRow['industryname'];

		$SQL = "SELECT COUNT(*)
				FROM debtorsmaster
				WHERE industry='" . $IndustryName . "'";
		$Result = DB_query($SQL);
		$CountRow = DB_fetch_row($Result);

		if ($CountRow[0] > 0) {
			prnMsg(__('This industry cannot be deleted because customers are assigned to it'), 'error');
		} else {
			$SQL = "DELETE FROM crmindustries
					WHERE industryid='" . $DeleteIndustryID . "'";
			$Result = DB_query($SQL);
			prnMsg(__('The industry has been deleted'), 'success');
		}
	}
}

$SQL = "SELECT industryid,
			industryname,
			active
		FROM crmindustries
		ORDER BY industryname";
$Result = DB_query($SQL);

echo '<table class="selection">
		<tr>
			<th class="SortedColumn">' . __('Industry Name') . '</th>
			<th>' . __('Active') . '</th>
			<th class="noPrint" colspan="2">&nbsp;</th>
		</tr>';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">
			<td>' . htmlspecialchars($MyRow['industryname'], ENT_QUOTES, 'UTF-8') . '</td>
			<td>' . ($MyRow['active'] ? __('Yes') : __('No')) . '</td>
			<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedIndustry=' . $MyRow['industryid'] . '">' . __('Edit') . '</a></td>
			<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=' . $MyRow['industryid'] . '" onclick="return confirm(\'' . __('Are you sure you wish to delete this industry?') . '\');">' . __('Delete') . '</a></td>
		</tr>';
}

echo '</table>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedIndustry)) {
	$SQL = "SELECT industryid,
				industryname,
				active
			FROM crmindustries
			WHERE industryid='" . $SelectedIndustry . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['IndustryName'] = $MyRow['industryname'];
	$_POST['Active'] = $MyRow['active'];

	echo '<input type="hidden" name="SelectedIndustry" value="' . $SelectedIndustry . '" />
		<fieldset>
			<legend>' . __('Edit Industry') . '</legend>';
} else {
	echo '<fieldset>
			<legend>' . __('New Industry') . '</legend>';
}

echo '<field>
		<label for="IndustryName">' . __('Industry Name') . ':</label>
		<input type="text" name="IndustryName" maxlength="60" size="50"';
if (isset($_POST['IndustryName'])) {
	echo ' value="' . htmlspecialchars($_POST['IndustryName'], ENT_QUOTES, 'UTF-8') . '"';
}
echo ' />
		<fieldhelp>' . __('A descriptive name for this industry') . '</fieldhelp>
	</field>';

if (isset($SelectedIndustry)) {
	echo '<field>
		<label for="Active">' . __('Active') . ':</label>
		<input type="checkbox" name="Active" value="1"';
	if (isset($_POST['Active']) and $_POST['Active']) {
		echo ' checked="checked"';
	}
	echo ' />
		<fieldhelp>' . __('Uncheck to hide this industry from customer selection lists') . '</fieldhelp>
	</field>';
}

echo '</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Information') . '" />
	</div>
</form>';

include(__DIR__ . '/includes/footer.php');

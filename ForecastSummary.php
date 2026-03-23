<?php
/**
 * Forecast Summary Management
 * Create and manage summary forecasts by category, customer type, area, etc.
 */

require (__DIR__ . '/includes/session.php');

$Title = __('Forecast Summary');
$ViewTopic = 'ForecastManagement';
$BookMark = 'SummaryForecasts';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . '
	  </p>';

// Handle summary forecast creation/update
if (isset($_POST['Submit'])) {

	$Errors = array();

	if (!isset($_POST['SummaryCode']) || $_POST['SummaryCode'] == '') {
		$Errors[] = __('Summary Code is required');
	}

	if (!isset($_POST['SummaryDesc']) || $_POST['SummaryDesc'] == '') {
		$Errors[] = __('Summary Description is required');
	}

	if (count($Errors) == 0) {

		$SummaryCode = mb_strtoupper(trim($_POST['SummaryCode']));
		$SummaryDesc = trim($_POST['SummaryDesc']);
		$CategoryCode = isset($_POST['CategoryCode']) ? $_POST['CategoryCode'] : null;
		$CustomerType = isset($_POST['CustomerType']) ? $_POST['CustomerType'] : null;
		$Area = isset($_POST['Area']) ? $_POST['Area'] : null;
		$SalesPerson = isset($_POST['SalesPerson']) ? $_POST['SalesPerson'] : null;

		if (isset($_POST['SummaryID']) && $_POST['SummaryID'] != '') {
			// Update existing
			$SummaryID = (int)$_POST['SummaryID'];

			$SQL = "UPDATE forecastsummary SET
					summarydesc = '" . DB_escape_string($SummaryDesc) . "',
					categorycode = " . ($CategoryCode ? "'" . DB_escape_string($CategoryCode) . "'" : "NULL") . ",
					customertype = " . ($CustomerType ? "'" . DB_escape_string($CustomerType) . "'" : "NULL") . ",
					area = " . ($Area ? "'" . DB_escape_string($Area) . "'" : "NULL") . ",
					salesperson = " . ($SalesPerson ? "'" . DB_escape_string($SalesPerson) . "'" : "NULL") . "
					WHERE summaryid = " . $SummaryID;

			DB_query($SQL);

			prnMsg(__('Summary forecast updated successfully') , 'success');

		}
		else {
			// Create new
			$SQL = "INSERT INTO forecastsummary (summarycode, summarydesc, categorycode, customertype,
					area, salesperson, active, createdby)
					VALUES (
					'" . DB_escape_string($SummaryCode) . "',
					'" . DB_escape_string($SummaryDesc) . "',
					" . ($CategoryCode ? "'" . DB_escape_string($CategoryCode) . "'" : "NULL") . ",
					" . ($CustomerType ? "'" . DB_escape_string($CustomerType) . "'" : "NULL") . ",
					" . ($Area ? "'" . DB_escape_string($Area) . "'" : "NULL") . ",
					" . ($SalesPerson ? "'" . DB_escape_string($SalesPerson) . "'" : "NULL") . ",
					1,
					'" . $_SESSION['UserID'] . "'
					)";

			$Result = DB_query($SQL);

			prnMsg(__('Summary forecast created successfully') , 'success');
		}

		unset($_POST['SummaryCode']);
		unset($_POST['SummaryDesc']);
		unset($_GET['Edit']);

	}
	else {
		foreach ($Errors as $Error) {
			prnMsg($Error, 'error');
		}
	}
}

// Handle delete
if (isset($_GET['Delete'])) {

	$SummaryID = (int)$_GET['SummaryID'];

	$SQL = "DELETE FROM forecastsummary WHERE summaryid = " . $SummaryID;
	DB_query($SQL);

	prnMsg(__('Summary forecast deleted') , 'success');
}

// Handle generate summary from details
if (isset($_POST['GenerateSummary'])) {

	$SummaryID = (int)$_POST['SummaryID'];
	$StartDate = ConvertSQLDate($_POST['StartDate']);
	$EndDate = ConvertSQLDate($_POST['EndDate']);

	// Get summary criteria
	$SQL = "SELECT * FROM forecastsummary WHERE summaryid = " . $SummaryID;
	$Result = DB_query($SQL);
	$Summary = DB_fetch_array($Result);

	// Build query to aggregate detail forecasts
	$SQL = "SELECT fd.perioddate,
				   SUM(fd.forecastqty) as totalqty,
				   SUM(fd.revisedqty) as totalrevised
			FROM forecastdetails fd
			INNER JOIN forecastheader fh ON fd.forecastid = fh.forecastid
			INNER JOIN stockmaster sm ON fh.stockid = sm.stockid
			WHERE fh.active = 1
			  AND fd.perioddate >= '" . $StartDate . "'
			  AND fd.perioddate <= '" . $EndDate . "'";

	if ($Summary['categorycode']) {
		$SQL .= " AND sm.categoryid = '" . DB_escape_string($Summary['categorycode']) . "'";
	}

	$SQL .= " GROUP BY fd.perioddate
			  ORDER BY fd.perioddate";

	$Result = DB_query($SQL);

	// Delete existing summary details
	DB_query("DELETE FROM forecastsummarydetails WHERE summaryid = " . $SummaryID);

	$PeriodNum = 1;
	while ($MyRow = DB_fetch_array($Result)) {

		$SQL = "INSERT INTO forecastsummarydetails
				(summaryid, perioddate, periodnum, forecastqty, revisedqty)
				VALUES (
				" . $SummaryID . ",
				'" . $MyRow['perioddate'] . "',
				" . $PeriodNum . ",
				" . (float)$MyRow['totalqty'] . ",
				" . ($MyRow['totalrevised'] ? (float)$MyRow['totalrevised'] : (float)$MyRow['totalqty']) . "
				)";

		DB_query($SQL);
		$PeriodNum++;
	}

	prnMsg(__('Summary forecast generated from detail forecasts') , 'success');
}

// Get summary ID for editing
$EditSummaryID = isset($_GET['Edit']) ? (int)$_GET['Edit'] : null;
$EditData = null;

if ($EditSummaryID) {
	$SQL = "SELECT * FROM forecastsummary WHERE summaryid = " . $EditSummaryID;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$EditData = DB_fetch_array($Result);
	}
}

// Display form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if ($EditData) {
	echo '<input type="hidden" name="SummaryID" value="' . $EditData['summaryid'] . '" />';
}

echo '<fieldset>
		<legend>' . ($EditData ? __('Edit Summary Forecast') : __('Create Summary Forecast')) . '</legend>';

// Summary Code
echo '<field>
		<label>' . __('Summary Code') . ':</label>
		<input type="text" name="SummaryCode" size="20" maxlength="20" required="required" ' . ($EditData ? 'readonly="readonly"' : '') . '
			value="' . ($EditData ? $EditData['summarycode'] : '') . '" />
	  </field>';

// Summary Description
echo '<field>
		<label for="SummaryDesc">' . __('Description') . ':</label>
		<input type="text" name="SummaryDesc" size="50" maxlength="100" required="required"
				   value="' . ($EditData ? $EditData['summarydesc'] : '') . '" />
	  </field>';

// Category Code
$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$Result = DB_query($SQL);

echo '<field>
		<label for="CategoryCode">' . __('Stock Category') . ':</label>
		<select name="CategoryCode">
			<option value="">' . __('All Categories') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = ($EditData && $EditData['categorycode'] == $MyRow['categoryid']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['categoryid'] . '" ' . $Selected . '>' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
}

echo '</select>
	</field>';

// Customer Type
$SQL = "SELECT typeid, typename FROM debtortype ORDER BY typename";
$Result = DB_query($SQL);

echo '<field>
		<label for="CustomerType">' . __('Customer Type') . ':</label>
		<select name="CustomerType">
			<option value="">' . __('All Types') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = ($EditData && $EditData['customertype'] == $MyRow['typeid']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['typeid'] . '" ' . $Selected . '>' . $MyRow['typename'] . '</option>';
}

echo '</select>
	</field>';

// Sales Area
$SQL = "SELECT areacode, areadescription FROM areas ORDER BY areadescription";
$Result = DB_query($SQL);

echo '<field>
		<label for="Area">' . __('Sales Area') . ':</label>
		<select name="Area">
			<option value="">' . __('All Areas') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = ($EditData && $EditData['area'] == $MyRow['areacode']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['areacode'] . '" ' . $Selected . '>' . $MyRow['areadescription'] . '</option>';
}

echo '</select>
	</field>';

// Sales Person
$SQL = "SELECT salesmancode, salesmanname FROM salesman ORDER BY salesmanname";
$Result = DB_query($SQL);

echo '<field>
		<label for="SalesPerson">' . __('Sales Person') . ':</label>
		<select name="SalesPerson">
			<option value="">' . __('All Sales People') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = ($EditData && $EditData['salesperson'] == $MyRow['salesmancode']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['salesmancode'] . '" ' . $Selected . '>' . $MyRow['salesmanname'] . '</option>';
}

echo '</select>
	  </field>
	  </fieldset>';

echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Save Summary Forecast') . '" />
	  </div>';

echo '</form>';

// List existing summary forecasts
$SQL = "SELECT fs.*,
			   sc.categorydescription,
			   dt.typename,
			   a.areadescription,
			   s.salesmanname
		FROM forecastsummary fs
		LEFT JOIN stockcategory sc ON fs.categorycode = sc.categoryid
		LEFT JOIN debtortype dt ON fs.customertype = dt.typeid
		LEFT JOIN areas a ON fs.area = a.areacode
		LEFT JOIN salesman s ON fs.salesperson = s.salesmancode
		ORDER BY fs.summarycode";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

	echo '<br /><table class="selection">';
	echo '<tr>
			<th>' . __('Summary Code') . '</th>
			<th>' . __('Description') . '</th>
			<th>' . __('Category') . '</th>
			<th>' . __('Customer Type') . '</th>
			<th>' . __('Area') . '</th>
			<th>' . __('Sales Person') . '</th>
			<th>' . __('Status') . '</th>
			<th colspan="3">' . __('Actions') . '</th>
		  </tr>';

	while ($MyRow = DB_fetch_array($Result)) {

		$StatusText = $MyRow['active'] == 1 ? __('Active') : __('Inactive');
		$StatusClass = $MyRow['active'] == 1 ? 'success' : 'error';

		echo '<tr class="striped_row">
				<td>' . $MyRow['summarycode'] . '</td>
				<td>' . $MyRow['summarydesc'] . '</td>
				<td>' . ($MyRow['categorydescription'] ? $MyRow['categorydescription'] : __('All')) . '</td>
				<td>' . ($MyRow['typename'] ? $MyRow['typename'] : __('All')) . '</td>
				<td>' . ($MyRow['areadescription'] ? $MyRow['areadescription'] : __('All')) . '</td>
				<td>' . ($MyRow['salesmanname'] ? $MyRow['salesmanname'] : __('All')) . '</td>
				<td class="' . $StatusClass . '">' . $StatusText . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit=' . $MyRow['summaryid'] . '">' . __('Edit') . '</a></td>
				<td><a href="ForecastSummaryReview.php?SummaryID=' . $MyRow['summaryid'] . '">' . __('View Details') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=1&amp;SummaryID=' . $MyRow['summaryid'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete this summary forecast?') . '\');">' . __('Delete') . '</a></td>
			  </tr>';
	}

	echo '</table>';
}

include (__DIR__ . '/includes/footer.php');

?>

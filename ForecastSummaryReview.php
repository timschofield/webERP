<?php
/**
 * Forecast Summary Review
 * Review and edit summary forecast details
 */

require (__DIR__ . '/includes/session.php');

$Title = __('Forecast Summary Review');
$ViewTopic = 'ForecastManagement';
$BookMark = 'ForecastSummaryReview';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . '
	  </p>';

if (!isset($_GET['SummaryID']) && !isset($_POST['SummaryID'])) {
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
	exit;
}

$SummaryID = isset($_GET['SummaryID']) ? (int)$_GET['SummaryID'] : (int)$_POST['SummaryID'];

// Get summary header
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
		WHERE fs.summaryid = " . $SummaryID;

$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '<p class="bad">' . __('Summary forecast not found') . '</p>';
	include (__DIR__ . '/includes/footer.php');
	exit;
}

$Summary = DB_fetch_array($Result);

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . ' - ' . $Summary['summarycode'] . '
	  </p>';

// Handle save revisions
if (isset($_POST['SaveRevisions'])) {

	foreach ($_POST['RevisedQty'] as $detailID => $RevisedQty) {
		if (is_numeric($RevisedQty)) {

			$RevisedValue = isset($_POST['RevisedValue'][$detailID]) && is_numeric($_POST['RevisedValue'][$detailID]) ? filter_number_format($_POST['RevisedValue'][$detailID]) : null;

			$SQL = "UPDATE forecastsummarydetails SET
					revisedqty = " . filter_number_format($RevisedQty);

			if ($RevisedValue !== null) {
				$SQL .= ", revisedvalue = " . $RevisedValue;
			}

			$SQL .= " WHERE id = " . (int)$detailID . "
					  AND summaryid = " . $SummaryID;

			DB_query($SQL);
		}
	}

	prnMsg(__('Summary forecast revisions saved successfully') , 'success');
}

// Display summary information
echo '<div class="centre">';
echo '<table class="selection">';
echo '<tr><td><b>' . __('Summary Code') . ':</b></td><td>' . $Summary['summarycode'] . '</td></tr>';
echo '<tr><td><b>' . __('Description') . ':</b></td><td>' . $Summary['summarydesc'] . '</td></tr>';

if ($Summary['categorydescription']) {
	echo '<tr><td><b>' . __('Category') . ':</b></td><td>' . $Summary['categorydescription'] . '</td></tr>';
}

if ($Summary['typename']) {
	echo '<tr><td><b>' . __('Customer Type') . ':</b></td><td>' . $Summary['typename'] . '</td></tr>';
}

if ($Summary['areadescription']) {
	echo '<tr><td><b>' . __('Area') . ':</b></td><td>' . $Summary['areadescription'] . '</td></tr>';
}

if ($Summary['salesmanname']) {
	echo '<tr><td><b>' . __('Sales Person') . ':</b></td><td>' . $Summary['salesmanname'] . '</td></tr>';
}

echo '</table>';
echo '</div><br />';

// Get summary details
$SQL = "SELECT * FROM forecastsummarydetails
		WHERE summaryid = " . $SummaryID . "
		ORDER BY perioddate";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="SummaryID" value="' . $SummaryID . '" />';

	echo '<table class="selection">';
	echo '<tr>
			<th>' . __('Period') . '</th>
			<th>' . __('Date') . '</th>
			<th>' . __('Forecast Qty') . '</th>
			<th>' . __('Forecast Value') . '</th>
			<th>' . __('Actual Qty') . '</th>
			<th>' . __('Actual Value') . '</th>
			<th>' . __('Revised Qty') . '</th>
			<th>' . __('Revised Value') . '</th>
			<th>' . __('Variance') . ' %</th>
		  </tr>';

	$TotalForecastQty = 0;
	$TotalForecastValue = 0;
	$TotalActualQty = 0;
	$TotalActualValue = 0;
	$TotalRevisedQty = 0;
	$TotalRevisedValue = 0;

	while ($MyRow = DB_fetch_array($Result)) {

		$ForecastQty = (float)$MyRow['forecastqty'];
		$ForecastValue = (float)$MyRow['forecastvalue'];
		$ActualQty = (float)$MyRow['actualqty'];
		$ActualValue = (float)$MyRow['actualvalue'];
		$RevisedQty = $MyRow['revisedqty'] !== null ? (float)$MyRow['revisedqty'] : $ForecastQty;
		$RevisedValue = $MyRow['revisedvalue'] !== null ? (float)$MyRow['revisedvalue'] : $ForecastValue;

		$Variance = ($RevisedQty > 0) ? (($ActualQty - $RevisedQty) / $RevisedQty) * 100 : 0;

		$VarianceClass = '';
		if (abs($Variance) <= 10) {
			$VarianceClass = 'success';
		}
		elseif (abs($Variance) <= 25) {
			$VarianceClass = 'warn';
		}
		else {
			$VarianceClass = 'error';
		}

		$TotalForecastQty += $ForecastQty;
		$TotalForecastValue += $ForecastValue;
		$TotalActualQty += $ActualQty;
		$TotalActualValue += $ActualValue;
		$TotalRevisedQty += $RevisedQty;
		$TotalRevisedValue += $RevisedValue;

		echo '<tr class="striped_row">
				<td>' . $MyRow['periodnum'] . '</td>
				<td>' . ConvertSQLDate($MyRow['perioddate']) . '</td>
				<td class="number">' . locale_number_format($ForecastQty, $_SESSION['DecimalPlaces']) . '</td>
				<td class="number">' . locale_number_format($ForecastValue, $_SESSION['DecimalPlaces']) . '</td>
				<td class="number">' . ($ActualQty > 0 ? locale_number_format($ActualQty, $_SESSION['DecimalPlaces']) : '-') . '</td>
				<td class="number">' . ($ActualValue > 0 ? locale_number_format($ActualValue, $_SESSION['DecimalPlaces']) : '-') . '</td>
				<td><input type="number" name="RevisedQty[' . $MyRow['id'] . ']" step="0.01"
						   value="' . $RevisedQty . '" style="width:100px;" /></td>
				<td><input type="number" name="RevisedValue[' . $MyRow['id'] . ']" step="0.01"
						   value="' . $RevisedValue . '" style="width:100px;" /></td>
				<td class="number ' . $VarianceClass . '">' . locale_number_format($Variance, 2) . '%</td>
			  </tr>';
	}

	// Totals row
	$TotalVariance = ($TotalRevisedQty > 0) ? (($TotalActualQty - $TotalRevisedQty) / $TotalRevisedQty) * 100 : 0;

	echo '<tr class="total_row">
			<td colspan="2"><b>' . __('Total') . '</b></td>
			<td class="number"><b>' . locale_number_format($TotalForecastQty, $_SESSION['DecimalPlaces']) . '</b></td>
			<td class="number"><b>' . locale_number_format($TotalForecastValue, $_SESSION['DecimalPlaces']) . '</b></td>
			<td class="number"><b>' . locale_number_format($TotalActualQty, $_SESSION['DecimalPlaces']) . '</b></td>
			<td class="number"><b>' . locale_number_format($TotalActualValue, $_SESSION['DecimalPlaces']) . '</b></td>
			<td class="number"><b>' . locale_number_format($TotalRevisedQty, $_SESSION['DecimalPlaces']) . '</b></td>
			<td class="number"><b>' . locale_number_format($TotalRevisedValue, $_SESSION['DecimalPlaces']) . '</b></td>
			<td class="number"><b>' . locale_number_format($TotalVariance, 2) . '%</b></td>
		  </tr>';

	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="SaveRevisions" value="' . __('Save Revisions') . '" />
			<a href="ForecastSummary.php">' . __('Back to Summary List') . '</a>
		  </div>';

	echo '</div>
		  </form>';

}
else {
	prnMsg(__('No forecast periods found for this summary') , 'info');
}

include (__DIR__ . '/includes/footer.php');

?>

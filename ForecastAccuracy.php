<?php
/**
 * Forecast Accuracy Report
 * Compare forecast vs actual sales and calculate accuracy metrics
 */

require (__DIR__ . '/includes/session.php');
require (__DIR__ . '/includes/DefineForecastClass.php');

$Title = __('Forecast Accuracy Report');
$ViewTopic = 'ForecastManagement';
$BookMark = 'AccuracyTracking';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . '
	  </p>';

if (isset($_POST['RunReport'])) {

	$FromDate = $_POST['FromDate'];
	$ToDate = $_POST['ToDate'];
	$LocationCode = isset($_POST['LocationCode']) ? $_POST['LocationCode'] : '';
	$StockCategory = isset($_POST['StockCategory']) ? $_POST['StockCategory'] : '';

	// Build query
	$SQL = "SELECT
			fh.forecastid,
			fh.stockid,
			sm.description as stockdesc,
			sm.decimalplaces,
			fh.locationcode,
			loc.locationname,
			fh.forecastmethod,
			fm.methodname,
			COUNT(fd.id) as periods,
			SUM(fd.forecastqty) as totalforecast,
			SUM(COALESCE(fd.revisedqty, fd.forecastqty)) as totalrevised,
			SUM(COALESCE(fsh.quantity, 0)) as totalactual,
			AVG(ABS(COALESCE(fd.revisedqty, fd.forecastqty) - COALESCE(fsh.quantity, 0))) as mad,
			(1 - (AVG(ABS(COALESCE(fd.revisedqty, fd.forecastqty) - COALESCE(fsh.quantity, 0))) /
				  NULLIF(AVG(COALESCE(fsh.quantity, 0)), 0))) * 100 as poa
		FROM forecastheader fh
		INNER JOIN forecastdetails fd ON fh.forecastid = fd.forecastid
		LEFT JOIN stockmaster sm ON fh.stockid = sm.stockid
		LEFT JOIN locations loc ON fh.locationcode = loc.loccode
		LEFT JOIN forecastmethods fm ON fh.forecastmethod = fm.methodid
		LEFT JOIN (
			SELECT stockid, locationcode, perioddate, SUM(quantity) as quantity
			FROM forecastsaleshistory
			GROUP BY stockid, locationcode, perioddate
		) fsh ON fh.stockid = fsh.stockid
			 AND fh.locationcode = fsh.locationcode
			 AND fd.perioddate = fsh.perioddate
		WHERE fh.active = 1
		  AND fd.perioddate >= '" . $FromDate . "'
		  AND fd.perioddate <= '" . $ToDate . "'";

	if ($LocationCode != '') {
		$SQL .= " AND fh.locationcode = '" . DB_escape_string($LocationCode) . "'";
	}

	if ($StockCategory != '') {
		$SQL .= " AND sm.categoryid = '" . DB_escape_string($StockCategory) . "'";
	}

	$SQL .= " GROUP BY fh.forecastid, fh.stockid, sm.description, fh.locationcode,
			  loc.locationname, fh.forecastmethod, fm.methodname
			  ORDER BY poa DESC";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {

		echo '<br /><table class="selection">';
		echo '<thead>
				<tr>
					<th class="SortedColumn">' . __('Stock Code') . '</th>
					<th class="SortedColumn">' . __('Description') . '</th>
					<th class="SortedColumn">' . __('Location') . '</th>
					<th class="SortedColumn">' . __('Method') . '</th>
					<th class="SortedColumn">' . __('Periods') . '</th>
					<th class="SortedColumn">' . __('Total Forecast') . '</th>
					<th class="SortedColumn">' . __('Total Actual') . '</th>
					<th class="SortedColumn">' . __('Variance') . '</th>
					<th>' . __('MAD') . '</th>
					<th>' . __('POA') . ' %</th>
					<th>' . __('Actions') . '</th>
				  </tr>
			  </thead>';

		$GrandTotalForecast = 0;
		$GrandTotalActual = 0;
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {

			$Variance = $MyRow['totalactual'] - $MyRow['totalrevised'];
			$VariancePct = ($MyRow['totalrevised'] > 0) ? ($Variance / $MyRow['totalrevised']) * 100 : 0;

			$AccuracyClass = '';
			if ($MyRow['poa'] >= 80) {
				$AccuracyClass = 'success';
			}
			elseif ($MyRow['poa'] >= 60) {
				$AccuracyClass = 'warn';
			}
			else {
				$AccuracyClass = 'error';
			}

			$GrandTotalForecast += $MyRow['totalrevised'];
			$GrandTotalActual += $MyRow['totalactual'];

			echo '<tr class="striped_row">
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['stockdesc'] . '</td>
					<td>' . ($MyRow['locationname'] ? $MyRow['locationname'] : __('All')) . '</td>
					<td>' . $MyRow['forecastmethod'] . ' - ' . $MyRow['methodname'] . '</td>
					<td class="number">' . $MyRow['periods'] . '</td>
					<td class="number">' . locale_number_format($MyRow['totalrevised'], $MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['totalactual'], $MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($Variance, $MyRow['decimalplaces']) . '<br />(' . locale_number_format($VariancePct, 1) . '%)</td>
					<td class="number">' . locale_number_format($MyRow['mad'], 4) . '</td>
					<td class="number ' . $AccuracyClass . '"><b>' . locale_number_format($MyRow['poa'], 2) . '%</b></td>
					<td><a href="ForecastReview.php?ForecastID=' . $MyRow['forecastid'] . '&amp;View=1">' . __('Review') . '</a></td>
				  </tr>';
			$DecimalPLaces = $MyRow['decimalplaces'];
		}

		// Grand totals
		$GrandVariance = $GrandTotalActual - $GrandTotalForecast;
		$GrandVariancePct = ($GrandTotalForecast > 0) ? ($GrandVariance / $GrandTotalForecast) * 100 : 0;
		echo '</tbody>';

		echo '<tr class="total_row">
				<td colspan="5"><b>' . __('Grand Total') . '</b></td>
				<td class="number"><b>' . locale_number_format($GrandTotalForecast, $DecimalPLaces) . '</b></td>
				<td class="number"><b>' . locale_number_format($GrandTotalActual, $DecimalPLaces) . '</b></td>
				<td class="number"><b>' . locale_number_format($GrandVariance, $DecimalPLaces) . '<br />(' . locale_number_format($GrandVariancePct, 1) . '%)</b></td>
				<td colspan="3"></td>
			  </tr>';

		echo '</table>';

	}
	else {
		prnMsg(__('No forecast data found for the selected period') , 'info');
	}
}

// Display form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Report Parameters') . '</legend>';

// From Date
echo '<field>
		<label for="FromDate">' . __('From Date') . ':</label>
		<input type="date" name="FromDate" value="' . date('Y-m-d', strtotime('-12 months')) . '" required="required" />
	  </field>';

// To Date
echo '<field>
		<label for="ToDate">' . __('To Date') . ':</label>
		<input type="date" name="ToDate" value="' . date('Y-m-d') . '" required="required" />
	  </field>';

// Location
$SQL = "SELECT loccode, locationname FROM locations ORDER BY locationname";
$Result = DB_query($SQL);

echo '<field>
		<label for="LocationCode">' . __('Location') . ':</label>
		<select name="LocationCode">
			<option value="">' . __('All Locations') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
}

echo '</select><
	  </field>';

// Stock Category
$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$Result = DB_query($SQL);

echo '<field>
		<label for="StockCategory">' . __('Stock Category') . ':</label>
		<select name="StockCategory">
			<option value="">' . __('All Categories') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
}

echo '</select>
	  </field>';

// Replace existing option (for extraction)
echo '<field>
		<label for="ReplaceExisting">' . __('Replace Existing Data') . ':</label>
		<input type="checkbox" name="ReplaceExisting" value="1" checked="checked" />
	  </field>';

echo '</fieldset>

	  <div class="centre">
		<input type="submit" name="Extract" value="' . __('Extract Sales Data') . '" />
		<input type="submit" name="RunReport" value="' . __('Run Accuracy Report') . '" />
	  </div>';

echo '</form>';

include (__DIR__ . '/includes/footer.php');

?>

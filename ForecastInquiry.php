<?php
/**
 * Forecast Inquiry
 * View and compare forecasts across items and periods
 */

require (__DIR__ . '/includes/session.php');

$Title = __('Forecast Inquiry');
$ViewTopic = 'ForecastManagement';
$BookMark = 'ForecastInquiry';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . $Title . '
	  </p>';

// Display form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Inquiry Parameters') . '</legend>';

// Stock ID
echo '<field>
		<label for="StockID">' . __('Stock Item') . ':</label>
		<input type="text" name="StockID" size="20" value="' . (isset($_POST['StockID']) ? $_POST['StockID'] : '') . '" />
	  </field>';

// Category
$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$Result = DB_query($SQL);

echo '<field>
		<label for="Category">' . __('Stock Category') . ':</label>
		<select name="Category">
			<option value="">' . __('All Categories') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = (isset($_POST['Category']) && $_POST['Category'] == $MyRow['categoryid']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['categoryid'] . '" ' . $Selected . '>' . $MyRow['categorydescription'] . '</option>';
}

echo '</select>
	  </field>';

// Location
$SQL = "SELECT loccode, locationname FROM locations ORDER BY locationname";
$Result = DB_query($SQL);

echo '<field>
		<label for="Location">' . __('Location') . ':</label>
		<select name="Location">
			<option value="">' . __('All Locations') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = (isset($_POST['Location']) && $_POST['Location'] == $MyRow['loccode']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['loccode'] . '" ' . $Selected . '>' . $MyRow['locationname'] . '</option>';
}

echo '</select>
	</field>';

// Date Range
echo '<field>
		<label for="FromDate">' . __('From Date') . ':</label>
		<input type="date" name="FromDate" value="' . (isset($_POST['FromDate']) ? $_POST['FromDate'] : date('Y-m-d')) . '" required="required" />
	  </field>';

echo '<field>
		<label for="ToDate">' . __('To Date') . ':</label>
		<input type="date" name="ToDate" value="' . (isset($_POST['ToDate']) ? $_POST['ToDate'] : date('Y-m-d', strtotime('+12 months'))) . '" required="required" />
	  </field>';

// Display options
echo '<field>
		<label for="ShowActuals">' . __('Show Actuals') . ':</label>
		<input type="checkbox" name="ShowActuals" value="1" checked="checked" />
	  </field>';

echo '<field>
		<label for="ShowVariance">' . __('Show Variance') . ':</label>
		<input type="checkbox" name="ShowVariance" value="1" checked="checked" />
	  </field>
	  </fieldset>';

echo '<div class="centre">
		<input type="submit" name="Search" value="' . __('Run Inquiry') . '" />
	  </div>';

echo '</form>';

// Display results
if (isset($_POST['Search'])) {

	$FromDate = $_POST['FromDate'];
	$ToDate = $_POST['ToDate'];
	$Location = isset($_POST['Location']) ? $_POST['Location'] : '';
	$Category = isset($_POST['Category']) ? $_POST['Category'] : '';
	$StockID = isset($_POST['StockID']) && $_POST['StockID'] != '' ? mb_strtoupper(trim($_POST['StockID'])) : '';
	$ShowActuals = isset($_POST['ShowActuals']);
	$ShowVariance = isset($_POST['ShowVariance']);

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
			fd.perioddate,
			fd.periodnum,
			fd.forecastqty,
			COALESCE(fd.revisedqty, fd.forecastqty) as effectiveforecast,
			fsh.quantity as actualqty
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

	if ($Location != '') {
		$SQL .= " AND fh.locationcode = '" . DB_escape_string($Location) . "'";
	}

	if ($Category != '') {
		$SQL .= " AND sm.categoryid = '" . DB_escape_string($Category) . "'";
	}

	if ($StockID != '') {
		$SQL .= " AND fh.stockid = '" . DB_escape_string($StockID) . "'";
	}

	$SQL .= " ORDER BY fh.stockid, fh.locationcode, fd.perioddate";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {

		echo '<div style="overflow-x: auto;">';
		echo '<table class="selection">';
		echo '<tr>
				<th>' . __('Stock Code') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Location') . '</th>
				<th>' . __('Method') . '</th>
				<th>' . __('Period') . '</th>
				<th>' . __('Forecast') . '</th>';

		if ($ShowActuals) {
			echo '<th>' . __('Actual') . '</th>';
		}

		if ($ShowVariance) {
			echo '<th>' . __('Variance') . '</th>
				  <th>' . __('Variance') . ' %</th>';
		}

		echo '</tr>';

		$CurrentStock = '';
		$ItemTotal = 0;
		$ItemActual = 0;

		while ($MyRow = DB_fetch_array($Result)) {

			// Display subtotal for each item
			if ($CurrentStock != '' && $CurrentStock != $MyRow['stockid']) {

				echo '<tr class="subtotal_row">
						<td colspan="5"><b>' . __('Subtotal') . '</b></td>
						<td class="number"><b>' . locale_number_format($ItemTotal, $MyRow['decimalplaces']) . '</b></td>';

				if ($ShowActuals) {
					echo '<td class="number"><b>' . locale_number_format($ItemActual, $MyRow['decimalplaces']) . '</b></td>';
				}

				if ($ShowVariance) {
					$ItemVariance = $ItemActual - $ItemTotal;
					$ItemVariancePct = ($ItemTotal > 0) ? ($ItemVariance / $ItemTotal) * 100 : 0;
					echo '<td class="number"><b>' . locale_number_format($ItemVariance, $MyRow['decimalplaces']) . '</b></td>
						  <td class="number"><b>' . locale_number_format($ItemVariancePct, 2) . '%</b></td>';
				}

				echo '</tr>';

				$ItemTotal = 0;
				$ItemActual = 0;
			}

			$CurrentStock = $MyRow['stockid'];
			$ItemTotal += $MyRow['effectiveforecast'];
			$ItemActual += $MyRow['actualqty'];

			$Variance = $MyRow['actualqty'] - $MyRow['effectiveforecast'];
			$VariancePct = ($MyRow['effectiveforecast'] > 0) ? ($Variance / $MyRow['effectiveforecast']) * 100 : 0;

			echo '<tr class="striped_row">
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['stockdesc'] . '</td>
					<td>' . ($MyRow['locationname'] ? $MyRow['locationname'] : __('All')) . '</td>
					<td>' . $MyRow['forecastmethod'] . '</td>
					<td>' . ConvertSQLDate($MyRow['perioddate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['effectiveforecast'], $MyRow['decimalplaces']) . '</td>';

			if ($ShowActuals) {
				echo '<td class="number">' . locale_number_format($MyRow['actualqty'], $MyRow['decimalplaces']) . '</td>';
			}

			if ($ShowVariance) {
				echo '<td class="number">' . locale_number_format($Variance, $MyRow['decimalplaces']) . '</td>
					  <td class="number">' . locale_number_format($VariancePct, 2) . '%</td>';
			}

			echo '</tr>';
			$DecimalPLaces = $MyRow['decimalplaces'];
		}

		// Last subtotal
		if ($ItemTotal > 0) {
			echo '<tr class="subtotal_row">
					<td colspan="5"><b>' . __('Subtotal') . '</b></td>
					<td class="number"><b>' . locale_number_format($ItemTotal, $DecimalPLaces) . '</b></td>';

			if ($ShowActuals) {
				echo '<td class="number"><b>' . locale_number_format($ItemActual, $DecimalPLaces) . '</b></td>';
			}

			if ($ShowVariance) {
				$ItemVariance = $ItemActual - $ItemTotal;
				$ItemVariancePct = ($ItemTotal > 0) ? ($ItemVariance / $ItemTotal) * 100 : 0;
				echo '<td class="number"><b>' . locale_number_format($ItemVariance, $DecimalPLaces) . '</b></td>
					  <td class="number"><b>' . locale_number_format($ItemVariancePct, 2) . '%</b></td>';
			}

			echo '</tr>';
		}

		echo '</table>';
		echo '</div>';

	}
	else {
		prnMsg(__('No forecast data found for the selected criteria') , 'info');
	}
}

include (__DIR__ . '/includes/footer.php');

?>

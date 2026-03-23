<?php
/**
 * Forecast Review and Revisions
 * Review and manually adjust generated forecasts
 */

require (__DIR__ . '/includes/session.php');

$Title = __('Forecast Review');
$ViewTopic = 'ForecastManagement';
$BookMark = 'ReviewingForecasts';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . '
	  </p>';

// Handle forecast revision submission
if (isset($_POST['SaveRevisions'])) {

	$ForecastID = (int)$_POST['ForecastID'];

	// Update revised quantities
	foreach ($_POST['RevisedQty'] as $DetailID => $RevisedQty) {
		if (is_numeric($RevisedQty)) {

			$SQL = "UPDATE forecastdetails SET
					revisedqty = " . (float)$RevisedQty . "
					WHERE id = " . (int)$DetailID . "
					  AND forecastid = " . $ForecastID;

			DB_query($SQL);
		}
	}

	// Update header
	$SQL = "UPDATE forecastheader SET
			modifiedby = '" . $_SESSION['UserID'] . "',
			modifiedon = NOW()
			WHERE forecastid = " . $ForecastID;

	DB_query($SQL);

	prnMsg(__('Forecast revisions saved successfully') , 'success');
}

// Handle forecast activation/deactivation
if (isset($_GET['ToggleActive']) && isset($_GET['ForecastID'])) {

	$ForecastID = (int)$_GET['ForecastID'];

	$SQL = "UPDATE forecastheader SET
			active = 1 - active,
			modifiedby = '" . $_SESSION['UserID'] . "',
			modifiedon = NOW()
			WHERE forecastid = " . $ForecastID;

	DB_query($SQL);

	prnMsg(__('Forecast status updated') , 'success');
}

// Display forecast selection form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Select Forecast') . '</legend>';

// Stock ID filter
echo '<field>
		<label for="FilterStockID">' . __('Stock Item') . ':</label>
		<input type="text" name="FilterStockID" size="20"
				   value="' . (isset($_POST['FilterStockID']) ? $_POST['FilterStockID'] : '') . '" />
	  </field>';

// Location filter
$SQL = "SELECT loccode, locationname FROM locations ORDER BY locationname";
$Result = DB_query($SQL);

echo '<field>
		<label for="FilterLocation">' . __('Location') . ':</label>
		<select name="FilterLocation">
			<option value="">' . __('All Locations') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$selected = (isset($_POST['FilterLocation']) && $_POST['FilterLocation'] == $MyRow['loccode']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['loccode'] . '" ' . $selected . '>' . $MyRow['locationname'] . '</option>';
}

echo '</select>
	  </field>';

echo '<field>
		<label for="ActiveOnly">' . __('Active Only') . ':</label>
		<input type="checkbox" name="ActiveOnly" value="1" ' . (isset($_POST['ActiveOnly']) ? 'checked="checked"' : 'checked="checked"') . ' />
	  </field>';

echo '</fieldset>

	  <div class="centre">
		<input type="submit" name="Search" value="' . __('Search Forecasts') . '" />
	  </div>';

echo '</form>';

// Display search results
if (isset($_POST['Search']) || isset($_GET['ForecastID'])) {

	$SQL = "SELECT fh.forecastid,
				   fh.stockid,
				   sm.description as stockdesc,
				   fh.locationcode,
				   loc.locationname,
				   fh.forecasttype,
				   fh.forecastmethod,
				   fm.methodname,
				   fh.startdate,
				   fh.active,
				   fh.lastgenerated,
				   fh.description
			FROM forecastheader fh
			LEFT JOIN stockmaster sm ON fh.stockid = sm.stockid
			LEFT JOIN locations loc ON fh.locationcode = loc.loccode
			LEFT JOIN forecastmethods fm ON fh.forecastmethod = fm.methodid
			WHERE 1=1";

	if (isset($_POST['FilterStockID']) && $_POST['FilterStockID'] != '') {
		$SQL .= " AND fh.stockid = '" . DB_escape_string(mb_strtoupper($_POST['FilterStockID'])) . "'";
	}

	if (isset($_POST['FilterLocation']) && $_POST['FilterLocation'] != '') {
		$SQL .= " AND fh.locationcode = '" . DB_escape_string($_POST['FilterLocation']) . "'";
	}

	if (isset($_POST['ActiveOnly'])) {
		$SQL .= " AND fh.active = 1";
	}

	if (isset($_GET['ForecastID'])) {
		$SQL .= " AND fh.forecastid = " . (int)$_GET['ForecastID'];
	}

	$SQL .= " ORDER BY fh.stockid, fh.locationcode";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {

		echo '<br /><table class="selection">';
		echo '<tr>
				<th>' . __('Forecast ID') . '</th>
				<th>' . __('Stock Code') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Location') . '</th>
				<th>' . __('Type') . '</th>
				<th>' . __('Method') . '</th>
				<th>' . __('Last Generated') . '</th>
				<th>' . __('Status') . '</th>
				<th colspan="2">' . __('Actions') . '</th>
			  </tr>';

		while ($MyRow = DB_fetch_array($Result)) {

			$StatusText = $MyRow['active'] == 1 ? __('Active') : __('Inactive');
			$StatusClass = $MyRow['active'] == 1 ? 'success' : 'error';

			echo '<tr class="striped_row">
					<td>' . $MyRow['forecastid'] . '</td>
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['stockdesc'] . '</td>
					<td>' . ($MyRow['locationcode'] != '' ? $MyRow['locationname'] : __('All')) . '</td>
					<td>' . $MyRow['forecasttype'] . '</td>
					<td>' . $MyRow['forecastmethod'] . ' - ' . $MyRow['methodname'] . '</td>
					<td>' . ConvertSQLDate($MyRow['lastgenerated']) . '</td>
					<td class="' . $StatusClass . '">' . $StatusText . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?ForecastID=' . $MyRow['forecastid'] . '&amp;View=1">' . __('View/Edit') . '</a></td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?ForecastID=' . $MyRow['forecastid'] . '&amp;ToggleActive=1" onclick="return confirm(\'' . __('Are you sure?') . '\');">' . __('Toggle Status') . '</a></td>
				  </tr>';
		}

		echo '</table><br />';

	}
	else {
		prnMsg(__('No forecasts found matching the criteria') , 'info');
	}
}

// Display forecast detail view for editing
if (isset($_GET['View']) && isset($_GET['ForecastID'])) {

	$ForecastID = (int)$_GET['ForecastID'];

	// Get forecast header
	$SQL = "SELECT fh.*,
				   sm.description as stockdesc,
				   loc.locationname,
				   fm.methodname
			FROM forecastheader fh
			LEFT JOIN stockmaster sm ON fh.stockid = sm.stockid
			LEFT JOIN locations loc ON fh.locationcode = loc.loccode
			LEFT JOIN forecastmethods fm ON fh.forecastmethod = fm.methodid
			WHERE fh.forecastid = " . $ForecastID;

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {

		$Header = DB_fetch_array($Result);

		echo '<div class="centre">';
		echo '<h3>' . __('Forecast Details') . '</h3>';
		echo '<table class="selection">';
		echo '<tr><td><b>' . __('Stock Code') . ':</b></td><td>' . $Header['stockid'] . ' - ' . $Header['stockdesc'] . '</td></tr>';
		echo '<tr><td><b>' . __('Location') . ':</b></td><td>' . ($Header['locationcode'] != '' ? $Header['locationname'] : __('All')) . '</td></tr>';
		echo '<tr><td><b>' . __('Method') . ':</b></td><td>' . $Header['forecastmethod'] . ' - ' . $Header['methodname'] . '</td></tr>';
		echo '<tr><td><b>' . __('Description') . ':</b></td><td>' . $Header['description'] . '</td></tr>';
		echo '</table>';
		echo '</div><br />';

		// Get forecast details
		$SQL = "SELECT fd.*,
					   fa.quantity as actualqty
				FROM forecastdetails fd
				LEFT JOIN (
					SELECT DATE_FORMAT(soh.orddate, '%Y-%m-01') as perioddate,
						   SUM(sod.quantity) as quantity
					FROM salesorders soh
					INNER JOIN salesorderdetails sod ON soh.orderno = sod.orderno
					WHERE sod.stkcode = '" . DB_escape_string($Header['stockid']) . "'";

		if ($Header['locationcode'] != '') {
			$SQL .= " AND soh.fromstkloc = '" . DB_escape_string($Header['locationcode']) . "'";
		}

		$SQL .= " GROUP BY DATE_FORMAT(soh.orddate, '%Y-%m-01')
				) fa ON fd.perioddate = fa.perioddate
				WHERE fd.forecastid = " . $ForecastID . "
				ORDER BY fd.perioddate";

		$Result = DB_query($SQL);

		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="ForecastID" value="' . $ForecastID . '" />';

		echo '<table class="selection">';
		echo '<tr>
				<th>' . __('Period') . '</th>
				<th>' . __('Date') . '</th>
				<th>' . __('Forecast Qty') . '</th>
				<th>' . __('Actual Qty') . '</th>
				<th>' . __('Revised Qty') . '</th>
				<th>' . __('Variance') . '</th>
				<th>' . __('Variance') . ' %</th>
			  </tr>';

		$TotalForecast = 0;
		$TotalActual = 0;
		$TotalRevised = 0;

		while ($MyRow = DB_fetch_array($Result)) {

			$ForecastQty = (float)$MyRow['forecastqty'];
			$ActualQty = isset($MyRow['actualqty']) ? (float)$MyRow['actualqty'] : 0;
			$RevisedQty = $MyRow['revisedqty'] !== null ? (float)$MyRow['revisedqty'] : $ForecastQty;

			$Variance = $ActualQty - $ForecastQty;
			$VariancePct = ($ForecastQty > 0) ? ($Variance / $ForecastQty) * 100 : 0;

			$VarianceClass = '';
			if ($ActualQty > 0) {
				$VarianceClass = ($Variance > 0) ? 'success' : 'error';
			}

			$TotalForecast += $ForecastQty;
			$TotalActual += $ActualQty;
			$TotalRevised += $RevisedQty;

			echo '<tr class="striped_row">
					<td>' . $MyRow['periodnum'] . '</td>
					<td>' . ConvertSQLDate($MyRow['perioddate']) . '</td>
					<td class="number">' . locale_number_format($ForecastQty, $_SESSION['DecimalPlaces']) . '</td>
					<td class="number">' . ($ActualQty > 0 ? locale_number_format($ActualQty, $_SESSION['DecimalPlaces']) : '-') . '</td>
					<td><input type="number" name="RevisedQty[' . $MyRow['id'] . ']" step="0.01"
							   value="' . $RevisedQty . '" style="width:100px;" /></td>
					<td class="number ' . $VarianceClass . '">' . locale_number_format($Variance, $_SESSION['DecimalPlaces']) . '</td>
					<td class="number ' . $VarianceClass . '">' . locale_number_format($VariancePct, 2) . '%</td>
				  </tr>';
		}

		// Totals row
		echo '<tr class="total_row">
				<td colspan="2"><b>' . __('Total') . '</b></td>
				<td class="number"><b>' . locale_number_format($TotalForecast, $_SESSION['DecimalPlaces']) . '</b></td>
				<td class="number"><b>' . locale_number_format($TotalActual, $_SESSION['DecimalPlaces']) . '</b></td>
				<td class="number"><b>' . locale_number_format($TotalRevised, $_SESSION['DecimalPlaces']) . '</b></td>
				<td class="number"><b>' . locale_number_format($TotalActual - $TotalForecast, $_SESSION['DecimalPlaces']) . '</b></td>
				<td class="number"><b>' . ($TotalForecast > 0 ? locale_number_format((($TotalActual - $TotalForecast) / $TotalForecast) * 100, 2) : '0.00') . '%</b></td>
			  </tr>';

		echo '</table>';

		echo '<div class="centre">
				<input type="submit" name="SaveRevisions" value="' . __('Save Revisions') . '" />
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Cancel') . '</a>
			  </div>';

		echo '</form>';

	}
	else {
		prnMsg(__('Forecast not found') , 'error');
	}
}

include (__DIR__ . '/includes/footer.php');

?>

<?php
/**
 * Forecast Constants Setup
 * Configure smoothing constants and parameters for items
 */

require (__DIR__ . '/includes/session.php');

$Title = __('Forecast Constants');
$ViewTopic = 'ForecastManagement';
$BookMark = 'ForecastConstants';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . '
	  </p>';

// Handle form submission
if (isset($_POST['Submit'])) {

	$StockID = mb_strtoupper(trim($_POST['StockID']));
	$LocationCode = isset($_POST['LocationCode']) ? $_POST['LocationCode'] : '';

	// Check if record exists
	$SQL = "SELECT id FROM forecastconstants
			WHERE stockid = '" . DB_escape_string($StockID) . "'
			  AND locationcode = '" . DB_escape_string($LocationCode) . "'";

	$Result = DB_query($SQL);

	$SmoothingAlpha = filter_number_format($_POST['SmoothingAlpha']);
	$SmoothingBeta = filter_number_format($_POST['SmoothingBeta']);
	$SmoothingGamma = filter_number_format($_POST['SmoothingGamma']);
	$PeriodsAverage = (int)$_POST['PeriodsAverage'];
	$PeriodsHistory = (int)$_POST['PeriodsHistory'];
	$SafetyStock = filter_number_format($_POST['SafetyStock']);
	$SafetyStockPct = filter_number_format($_POST['SafetyStockPct']);
	$OutlierFilter = isset($_POST['OutlierFilter']) ? 1 : 0;
	$OutlierDeviation = (int)$_POST['OutlierDeviation'];

	if (DB_num_rows($Result) > 0) {
		// Update existing
		$MyRow = DB_fetch_array($Result);
		$ID = $MyRow['id'];

		$SQL = "UPDATE forecastconstants SET
				smoothingalpha = " . $SmoothingAlpha . ",
				smoothingbeta = " . $SmoothingBeta . ",
				smoothinggamma = " . $SmoothingGamma . ",
				periodsaverage = " . $PeriodsAverage . ",
				periodshistory = " . $PeriodsHistory . ",
				safetystock = " . $SafetyStock . ",
				safetystockpct = " . $SafetyStockPct . ",
				outlierfilter = " . $OutlierFilter . ",
				outlierdeviation = " . $OutlierDeviation . "
				WHERE id = " . $ID;

		DB_query($SQL);

		prnMsg(__('Forecast constants updated successfully') , 'success');

	}
	else {
		// Insert new
		$SQL = "INSERT INTO forecastconstants
				(stockid, locationcode, smoothingalpha, smoothingbeta, smoothinggamma,
				 periodsaverage, periodshistory, safetystock, safetystockpct,
				 outlierfilter, outlierdeviation)
				VALUES (
				'" . DB_escape_string($StockID) . "',
				'" . DB_escape_string($LocationCode) . "',
				" . $SmoothingAlpha . ",
				" . $SmoothingBeta . ",
				" . $SmoothingGamma . ",
				" . $PeriodsAverage . ",
				" . $PeriodsHistory . ",
				" . $SafetyStock . ",
				" . $SafetyStockPct . ",
				" . $OutlierFilter . ",
				" . $OutlierDeviation . "
				)";

		DB_query($SQL);

		prnMsg(__('Forecast constants created successfully') , 'success');
	}

	unset($_POST['StockID']);
	unset($_GET['StockID']);
}

// Handle delete
if (isset($_GET['Delete'])) {

	$ID = (int)$_GET['ID'];

	$SQL = "DELETE FROM forecastconstants WHERE id = " . $ID;
	DB_query($SQL);

	prnMsg(__('Forecast constants deleted') , 'success');
}

// Get constants for editing
$EditData = null;

if (isset($_GET['StockID'])) {
	$StockID = mb_strtoupper(trim($_GET['StockID']));
	$LocationCode = isset($_GET['LocationCode']) ? $_GET['LocationCode'] : '';

	$SQL = "SELECT * FROM forecastconstants
			WHERE stockid = '" . DB_escape_string($StockID) . "'
			  AND locationcode = '" . DB_escape_string($LocationCode) . "'";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$EditData = DB_fetch_array($Result);
	}
}

// Display form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Forecast Constants') . '</legend>
		<table class="selection">';

// Stock ID
echo '<field>
		<label for="StockID">' . __('Stock Item') . ':</label>
		<input type="text" name="StockID" size="20" maxlength="20" required="required" ' . ($EditData ? 'readonly="readonly"' : '') . '
			value="' . ($EditData ? $EditData['stockid'] : (isset($_GET['StockID']) ? $_GET['StockID'] : '')) . '" />
	  </field>';

// Location
$SQL = "SELECT loccode, locationname FROM locations ORDER BY locationname";
$Result = DB_query($SQL);

echo '<field>
		<label for="LocationCode">' . __('Location') . ':</label>
		<select name="LocationCode" ' . ($EditData ? 'disabled="disabled"' : '') . '>
			<option value="">' . __('All Locations') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = ($EditData && $EditData['locationcode'] == $MyRow['loccode']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['loccode'] . '" ' . $Selected . '>' . $MyRow['locationname'] . '</option>';
}

echo '</select>
	  </field>';

// Smoothing constants
echo '<field>
		<label for="SmoothingAlpha">' . __('Smoothing Alpha (α)') . ':</label>
		<input type="number" name="SmoothingAlpha" min="0.01" max="1" step="0.01"
				   value="' . ($EditData ? $EditData['smoothingalpha'] : '0.30') . '" required="required" />
		<fieldhelp>' . __('Level smoothing constant (0.01-1.00)') . '</fieldhelp>
	  </field>';

echo '<field>
		<label for="SmoothingBeta">' . __('Smoothing Beta (β)') . ':</label>
		<input type="number" name="SmoothingBeta" min="0.01" max="1" step="0.01"
				   value="' . ($EditData ? $EditData['smoothingbeta'] : '0.30') . '" required="required" />
		<fieldhelp>' . __('Trend smoothing constant (0.01-1.00)') . '</fieldhelp>
	  </field>';

echo '<field>
		<label for="SmoothingGamma">' . __('Smoothing Gamma (γ)') . ':</label>
		<input type="number" name="SmoothingGamma" min="0.01" max="1" step="0.01"
				   value="' . ($EditData ? $EditData['smoothinggamma'] : '0.30') . '" required="required" />
		<fieldhelp>' . __('Seasonal smoothing constant (0.01-1.00)') . '</fieldhelp>
	  </field>';

// Periods
echo '<field>
		<label for="PeriodsAverage">' . __('Periods for Average') . ':</label>
		<input type="number" name="PeriodsAverage" min="2" max="24"
				   value="' . ($EditData ? $EditData['periodsaverage'] : '4') . '" required="required" />
		<fieldhelp>' . __('Number of periods for moving average calculations') . '</fieldhelp>
	  </field>';

echo '<field>
		<label for="PeriodsHistory">' . __('Periods of History') . ':</label>
		<input type="number" name="PeriodsHistory" min="6" max="120"
				   value="' . ($EditData ? $EditData['periodshistory'] : '24') . '" required="required" />
		<fieldhelp>' . __('Number of historical periods to analyze') . '</fieldhelp>
	  </field>';

// Safety stock
echo '<field>
		<label for="SafetyStock">' . __('Safety Stock Quantity') . ':</label>
		<input type="text" name="SafetyStock" size="10" class="number"
				   value="' . ($EditData ? locale_number_format($EditData['safetystock'], $_SESSION['DecimalPlaces']) : '0') . '" />
		<fieldhelp>' . __('Fixed safety stock quantity') . '</fieldhelp>
	  </field>';

echo '<field>
		<label for="SafetyStockPct">' . __('Safety Stock Percent') . ':</label>
		<input type="number" name="SafetyStockPct" min="0" max="100" step="0.01"
				   value="' . ($EditData ? $EditData['safetystockpct'] : '0') . '" /> %
		<fieldhelp>' . __('Safety stock as percentage of forecast') . '</fieldhelp>
	  </field>';

// Outlier detection
echo '<field>
		<label for="OutlierFilter">' . __('Enable Outlier Filter') . ':</label>
		<input type="checkbox" name="OutlierFilter" value="1" ' . (($EditData && $EditData['outlierfilter']) || !$EditData ? 'checked="checked"' : '') . ' />
		<fieldhelp>' . __('Filter out unusual data points') . '</fieldhelp>
	  </field>';

echo '<field>
		<label for="OutlierDeviation">' . __('Outlier Deviation') . ':</label>
		<input type="number" name="OutlierDeviation" min="1" max="5"
				   value="' . ($EditData ? $EditData['outlierdeviation'] : '2') . '" />
		<fieldhelp>' . __('Number of standard deviations for outlier detection') . '</fieldhelp>
	  </field>';

echo '</table>
	  </fieldset>

	  <div class="centre">
		<input type="submit" name="Submit" value="' . __('Save Constants') . '" />
	  </div>';

echo '</form>';

// List existing constants
$SQL = "SELECT fc.*,
			   sm.description as stockdesc,
			   loc.locationname
		FROM forecastconstants fc
		LEFT JOIN stockmaster sm ON fc.stockid = sm.stockid
		LEFT JOIN locations loc ON fc.locationcode = loc.loccode
		ORDER BY fc.stockid, fc.locationcode";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {

	echo '<br /><table class="selection">
                <thead>
                    <tr>
		            	<th class="SortedColumn">' . __('Stock Code') . '</th>
        		    	<th class="SortedColumn">' . __('Description') . '</th>
        		    	<th class="SortedColumn">' . __('Location') . '</th>
        		    	<th class="SortedColumn">' . __('Alpha') . '</th>
        		    	<th class="SortedColumn">' . __('Beta') . '</th>
        		    	<th class="SortedColumn">' . __('Gamma') . '</th>
        		    	<th class="SortedColumn">' . __('Periods Avg') . '</th>
        		    	<th class="SortedColumn">' . __('Periods History') . '</th>
        		    	<th colspan="2">' . __('Actions') . '</th>
        	      </tr>
               </thead>
               <tbody>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['stockdesc'] . '</td>
				<td>' . ($MyRow['locationname'] ? $MyRow['locationname'] : __('All')) . '</td>
				<td class="number">' . $MyRow['smoothingalpha'] . '</td>
				<td class="number">' . $MyRow['smoothingbeta'] . '</td>
				<td class="number">' . $MyRow['smoothinggamma'] . '</td>
				<td class="number">' . $MyRow['periodsaverage'] . '</td>
				<td class="number">' . $MyRow['periodshistory'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?StockID=' . urlencode($MyRow['stockid']) . '&amp;LocationCode=' . urlencode($MyRow['locationcode']) . '">' . __('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=1&amp;ID=' . $MyRow['id'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete these constants?') . '\');">' . __('Delete') . '</a></td>
			  </field>';
	}

	echo '</tbody></table>';
}

include (__DIR__ . '/includes/footer.php');

?>

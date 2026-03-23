<?php
/**
 * Batch Forecast Generation
 * Generate forecasts for multiple items at once
 */

require (__DIR__ . '/includes/session.php');
require (__DIR__ . '/includes/DefineForecastClass.php');

$Title = __('Batch Forecast Generation');
$ViewTopic = 'ForecastManagement';
$BookMark = 'BatchForecast';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . '
	  </p>';

echo '<div class="page_help_text">' . __('Generate forecasts for multiple items based on category, location, or other criteria. This process may take several minutes for large selections.') . '</div>';

if (isset($_POST['Generate'])) {

	set_time_limit(600); // Allow up to 10 minutes
	$Category = isset($_POST['Category']) && $_POST['Category'] != '' ? $_POST['Category'] : null;
	$Location = isset($_POST['Location']) && $_POST['Location'] != '' ? $_POST['Location'] : '';
	$Method = (int)$_POST['Method'];
	$Periods = (int)$_POST['Periods'];
	$ForecastType = $_POST['ForecastType'];
	$OnlyItemsWithSales = isset($_POST['OnlyItemsWithSales']) ? true : false;

	// Build parameters
	$Parameters = array();
	$Parameters['periodshistory'] = isset($_POST['PeriodsHistory']) ? (int)$_POST['PeriodsHistory'] : 24;

	// Build item selection query
	$SQL = "SELECT DISTINCT sm.stockid, sm.description
			FROM stockmaster sm";

	if ($OnlyItemsWithSales) {
		$SQL .= " INNER JOIN forecastsaleshistory fsh ON sm.stockid = fsh.stockid";

		if ($Location != '') {
			$SQL .= " AND fsh.locationcode = '" . DB_escape_string($Location) . "'";
		}
	}

	$SQL .= " WHERE sm.discontinued = 0
			  AND (sm.mbflag = 'B' OR sm.mbflag = 'M')";

	if ($Category) {
		$SQL .= " AND sm.categoryid = '" . DB_escape_string($Category) . "'";
	}

	$SQL .= " ORDER BY sm.stockid";

	$Result = DB_query($SQL);
	$ItemCount = DB_num_rows($Result);

	if ($ItemCount == 0) {
		prnMsg(__('No items found matching the criteria') , 'warn');
	}
	else {

		echo '<div class="centre">' . __('Starting batch forecast generation') . ': ' . date('H:i:s') . '<br />';
		echo __('Processing') . ' ' . $ItemCount . ' ' . __('items') . '...<br />';
		flush();

		$ForecastCalc = new ForecastCalculator($db);

		$SuccessCount = 0;
		$ErrorCount = 0;
		$Counter = 0;

		while ($Item = DB_fetch_array($Result)) {

			$Counter++;

			// Check for item-specific constants
			$ConstSQL = "SELECT * FROM forecastconstants
						 WHERE stockid = '" . DB_escape_string($Item['stockid']) . "'
						   AND locationcode = '" . DB_escape_string($Location) . "'";

			$ConstResult = DB_query($ConstSQL);

			if (DB_num_rows($ConstResult) > 0) {
				$Constants = DB_fetch_array($ConstResult);
				$ItemParams = array(
					'smoothingalpha' => $Constants['smoothingalpha'],
					'smoothingbeta' => $Constants['smoothingbeta'],
					'smoothinggamma' => $Constants['smoothinggamma'],
					'periodsaverage' => $Constants['periodsaverage'],
					'periodshistory' => $Constants['periodshistory']
				);
			}
			else {
				$ItemParams = $Parameters;
			}

			// Generate forecast
			$Forecast = $ForecastCalc->calculateForecast($Item['stockid'], $Location, $Method, $Periods, $ItemParams);

			if (isset($Forecast['error'])) {
				$ErrorCount++;
				if ($ErrorCount <= 10) { // Only show first 10 errors
					echo '<span class="error">' . $Item['stockid'] . ': ' . $Forecast['error'] . '</span><br />';
				}
			}
			else {
				// Save forecast
				$Description = 'Batch generated - ' . date('Y-m-d H:i:s');
				$ForecastCalc->saveForecast($Item['stockid'], $Location, $ForecastType, $Method, $Forecast, $Description);
				$SuccessCount++;
			}

			// Progress indicator every 10 items
			if ($Counter % 10 == 0) {
				echo __('Processed') . ' ' . $Counter . ' ' . __('of') . ' ' . $ItemCount . '...<br />';
				flush();
			}
		}

		echo '<br />' . __('Batch generation complete') . ': ' . date('H:i:s') . '</div>';
		prnMsg(__('Successfully generated') . ': ' . $SuccessCount . '<br />' . __('Errors') . ': ' . $ErrorCount, 'success');
	}
}

// Display form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Batch Generation Parameters') . '</legend>';

// Stock Category
$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$Result = DB_query($SQL);

echo '<field>
		<label for="Category">' . __('Stock Category') . ':</label>
		<select name="Category">
			<option value="">' . __('All Categories') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
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
	echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
}

echo '</select>
	</field>';

// Forecast Type
echo '<field>
		<label for="ForecastType">' . __('Forecast Type') . ':</label>
		<select name="ForecastType">
			<option value="DT">' . __('Detail') . '</option>
			<option value="SM">' . __('Summary') . '</option>
		</select>
	  </field>';

// Forecast Method
$SQL = "SELECT methodid, methodname, methoddesc FROM forecastmethods WHERE active = 1 ORDER BY methodid";
$Result = DB_query($SQL);

echo '<field>
		<label for="Method">' . __('Forecast Method') . ':</label>
		<select name="Method" required="required">
			<option value="">' . __('Select Method') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="' . $MyRow['methodid'] . '" title="' . htmlspecialchars($MyRow['methoddesc']) . '">' . $MyRow['methodid'] . ' - ' . $MyRow['methodname'] . '</option>';
}

echo '</select>
	</field>';

// Number of periods
echo '<field>
		<label for="Periods">' . __('Periods to Forecast') . ':</label>
		<input type="number" name="Periods" min="1" max="60" value="12" required="required" />
	  </field>';

// Periods of History
echo '<field>
		<label for="PeriodsHistory">' . __('Periods of History') . ':</label>
		<input type="number" name="PeriodsHistory" min="6" max="120" value="24" />
	  </field>';

// Only items with sales
echo '<field>
		<label for="OnlyItemsWithSales">' . __('Only Items with Sales History') . ':</label>
		<input type="checkbox" name="OnlyItemsWithSales" value="1" checked="checked" />
	  </field>';

echo '</fieldset>

	  <div class="centre">
		<input type="submit" name="Generate" value="' . __('Generate Batch Forecasts') . '"
			   onclick="return confirm(\'' . __('This may take several minutes. Continue?') . '\');" />
	  </div>';

echo '</form>';

include (__DIR__ . '/includes/footer.php');

?>

<?php
/**
 * Forecast Generation - Detail Forecasts
 * Generate forecasts for individual items using various statistical methods
 */

require (__DIR__ . '/includes/session.php');
require (__DIR__ . '/includes/DefineForecastClass.php');

$Title = __('Forecast Generation');
$ViewTopic = 'ForecastManagement';
$BookMark = 'ForecastGeneration';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . $Title . '
	</p>';

// Initialize Forecast Calculator
$ForecastCalc = new ForecastCalculator($db);

if (isset($_POST['Generate'])) {

	// Validate inputs
	$Errors = array();

	$ForecastScope = isset($_POST['ForecastScope']) ? $_POST['ForecastScope'] : 'item';

	if ($ForecastScope == 'item') {
		if (!isset($_POST['StockID']) || $_POST['StockID'] == '') {
			$Errors[] = __('Stock ID is required');
		}
	}
	elseif ($ForecastScope == 'category') {
		if (!isset($_POST['CategoryID']) || $_POST['CategoryID'] == '') {
			$Errors[] = __('Stock Category is required');
		}
	}

	if (!isset($_POST['ForecastMethod']) || !is_numeric($_POST['ForecastMethod'])) {
		$Errors[] = __('A valid forecast method must be selected');
	}

	if (!isset($_POST['Periods']) || !is_numeric($_POST['Periods']) || $_POST['Periods'] < 1) {
		$Errors[] = __('Number of periods must be at least 1');
	}

	if (count($Errors) == 0) {

		$LocationCode = isset($_POST['LocationCode']) ? $_POST['LocationCode'] : '';
		$ForecastMethod = (int)$_POST['ForecastMethod'];
		$Periods = (int)$_POST['Periods'];
		$ForecastType = isset($_POST['ForecastType']) ? $_POST['ForecastType'] : 'DT';
		$Description = isset($_POST['Description']) ? $_POST['Description'] : '';

		// Build parameters array
		$Params = array();

		if (isset($_POST['SmoothingAlpha']) && is_numeric($_POST['SmoothingAlpha'])) {
			$Params['smoothingalpha'] = (float)$_POST['SmoothingAlpha'];
		}

		if (isset($_POST['SmoothingBeta']) && is_numeric($_POST['SmoothingBeta'])) {
			$Params['smoothingbeta'] = (float)$_POST['SmoothingBeta'];
		}

		if (isset($_POST['SmoothingGamma']) && is_numeric($_POST['SmoothingGamma'])) {
			$Params['smoothinggamma'] = (float)$_POST['SmoothingGamma'];
		}

		if (isset($_POST['PercentIncrease']) && is_numeric($_POST['PercentIncrease'])) {
			$Params['percent'] = (float)$_POST['PercentIncrease'];
		}

		if (isset($_POST['PeriodsAverage']) && is_numeric($_POST['PeriodsAverage'])) {
			$Params['periodsaverage'] = (int)$_POST['PeriodsAverage'];
		}

		if (isset($_POST['PeriodsHistory']) && is_numeric($_POST['PeriodsHistory'])) {
			$Params['periodshistory'] = (int)$_POST['PeriodsHistory'];
		}

		// Get items to forecast
		$ItemsToForecast = array();

		if ($ForecastScope == 'item') {
			$StockID = mb_strtoupper(trim($_POST['StockID']));
			$ItemsToForecast[] = $StockID;
		}
		else {
			// Category - get all items in category
			$CategoryID = $_POST['CategoryID'];
			$SQL = "SELECT stockid FROM stockmaster
					WHERE categoryid = '" . DB_escape_string($CategoryID) . "'
					  AND discontinued = 0
					  AND (mbflag = 'B' OR mbflag = 'M')
					ORDER BY stockid";
			$Result = DB_query($SQL);

			while ($MyRow = DB_fetch_array($Result)) {
				$ItemsToForecast[] = $MyRow['stockid'];
			}

			if (count($ItemsToForecast) == 0) {
				prnMsg(__('No active items found in the selected category') , 'warn');
			}
		}

		// Process each item
		$SuccessCount = 0;
		$FailCount = 0;
		$Results = array();

		foreach ($ItemsToForecast as $StockID) {

			// Generate forecast
			$Forecast = $ForecastCalc->calculateForecast($StockID, $LocationCode, $ForecastMethod, $Periods, $Params);

			if (isset($Forecast['error'])) {
				$FailCount++;
				if ($ForecastScope == 'item') {
					prnMsg($Forecast['error'], 'error');
				}
			}
			else {

				// Save forecast to database
				$Forecastid = $ForecastCalc->saveForecast($StockID, $LocationCode, $ForecastType, $ForecastMethod, $Forecast, $Description);

				$SuccessCount++;

				// Store results for single item display
				if ($ForecastScope == 'item') {
					$Results[$StockID] = array(
						'forecastid' => $Forecastid,
						'data' => $Forecast
					);
				}
			}
		}

		// Display results
		if ($ForecastScope == 'category') {
			prnMsg(__('Category forecast complete') . ': ' . $SuccessCount . ' ' . __('successful') . ', ' . $FailCount . ' ' . __('failed') , ($SuccessCount > 0 ? 'success' : 'error'));

			echo '<div class="centre">';
			echo '<p>' . __('Forecasts generated for') . ' ' . $SuccessCount . ' ' . __('items in category') . '</p>';
			echo '<p><a href="ForecastInquiry.php">' . __('View Forecasts') . '</a></p>';
			echo '</div>';

		}
		else {
			// Single item - display detailed results
			if ($SuccessCount > 0) {
				foreach ($Results as $Itemid => $Result) {
					prnMsg(__('Forecast generated successfully') . ' - ' . __('Stock ID') . ': ' . $Itemid . ' - ' . __('Forecast ID') . ': ' . $Result['forecastid'], 'success');

					// Display forecast results
					echo '<div class="centre">';
					echo '<h3>' . __('Forecast for') . ' ' . $Itemid . '</h3>';
					echo '<table class="selection">';
					echo '<tr>
							<th>' . __('Period Date') . '</th>
							<th>' . __('Period') . '</th>
							<th>' . __('Forecast Quantity') . '</th>
						  </tr>';

					foreach ($Result['data'] as $Period) {
						echo '<tr class="striped_row">
								<td>' . ConvertSQLDate($Period['date']) . '</td>
								<td class="number">' . $Period['method'] . '</td>
								<td class="number">' . locale_number_format($Period['quantity'], $_SESSION['DecimalPlaces']) . '</td>
							  </tr>';
					}

					echo '</table>';
					echo '</div><br />';
				}
			}
		}
	}
	else {
		foreach ($Errors as $Error) {
			prnMsg($Error, 'error');
		}
	}
}

if (isset($_POST['FindBestFit'])) {

	$ForecastScope = isset($_POST['ForecastScope']) ? $_POST['ForecastScope'] : 'item';

	if ($ForecastScope == 'item') {
		$StockID = mb_strtoupper(trim($_POST['StockID']));
		$LocationCode = isset($_POST['LocationCode']) ? $_POST['LocationCode'] : '';

		$Params = array();
		if (isset($_POST['PeriodsHistory']) && is_numeric($_POST['PeriodsHistory'])) {
			$Params['periodshistory'] = (int)$_POST['PeriodsHistory'];
		}

		prnMsg(__('Analyzing all forecast methods to find best fit...') , 'info');

		$BestFit = $ForecastCalc->findBestFitMethod($StockID, $LocationCode, $Params);

		if (isset($BestFit['error'])) {
			prnMsg($BestFit['error'], 'error');
		}
		else {

			echo '<div class="centre">';
			echo '<h3>' . __('Best Fit Analysis Results for') . ' ' . $StockID . '</h3>';

			if ($BestFit['bestmethod']) {
				prnMsg(__('Recommended Method') . ': ' . $BestFit['bestmethod']['method'] . ' - ' . __('Accuracy') . ': ' . locale_number_format($BestFit['bestmethod']['poa'], 2) . '%', 'success');
			}

			echo '<table class="selection">';
			echo '<tr>
					<th>' . __('Method') . '</th>
					<th>' . __('MAD') . '</th>
					<th>' . __('POA') . ' %</th>
					<th>' . __('RMSE') . '</th>
				  </tr>';

			foreach ($BestFit['allresults'] as $Result) {
				$MyRowClass = ($Result['method'] == $BestFit['bestmethod']['method']) ? 'hilite' : 'striped_row';

				echo '<tr class="' . $MyRowClass . '">
						<td>' . $Result['method'] . '</td>
						<td class="number">' . locale_number_format($Result['mad'], 4) . '</td>
						<td class="number">' . locale_number_format($Result['poa'], 2) . '</td>
						<td class="number">' . locale_number_format($Result['rmse'], 4) . '</td>
					  </tr>';
			}

			echo '</table>';
			echo '</div><br />';
		}
	}
	else {
		// Category - analyze each item and show summary
		$CategoryID = $_POST['CategoryID'];
		$LocationCode = isset($_POST['LocationCode']) ? $_POST['LocationCode'] : '';

		$Params = array();
		if (isset($_POST['PeriodsHistory']) && is_numeric($_POST['PeriodsHistory'])) {
			$Params['periodshistory'] = (int)$_POST['PeriodsHistory'];
		}

		prnMsg(__('Analyzing best fit methods for all items in category...') , 'info');

		// Get items in category
		$SQL = "SELECT stockid, description FROM stockmaster
				WHERE categoryid = '" . DB_escape_string($CategoryID) . "'
				  AND discontinued = 0
				  AND (mbflag = 'B' OR mbflag = 'M')
				ORDER BY stockid
				LIMIT 50";
		$Result = DB_query($SQL);

		$categoryResults = array();
		$MethodCounts = array();

		while ($MyRow = DB_fetch_array($Result)) {
			$BestFit = $ForecastCalc->findBestFitMethod($MyRow['stockid'], $LocationCode, $Params);

			if (!isset($BestFit['error']) && $BestFit['bestmethod']) {
				$categoryResults[] = array(
					'stockid' => $MyRow['stockid'],
					'description' => $MyRow['description'],
					'method' => $BestFit['bestmethod']['method'],
					'poa' => $BestFit['bestmethod']['poa']
				);

				$Method = $BestFit['bestmethod']['method'];
				if (!isset($MethodCounts[$Method])) {
					$MethodCounts[$Method] = 0;
				}
				$MethodCounts[$Method]++;
			}
		}

		if (count($categoryResults) > 0) {
			echo '<div class="centre">';
			echo '<h3>' . __('Best Fit Analysis for Category') . '</h3>';

			// Summary statistics
			echo '<p>' . __('Items analyzed') . ': ' . count($categoryResults) . '</p>';

			if (count($MethodCounts) > 0) {
				arsort($MethodCounts);
				$MostCommonMethod = array_key_first($MethodCounts);
				prnMsg(__('Most common best-fit method') . ': ' . __('Method') . ' ' . $MostCommonMethod . ' (' . $MethodCounts[$MostCommonMethod] . ' ' . __('items') . ')', 'success');
			}

			// Detailed results
			echo '<table class="selection">';
			echo '<tr>
					<th>' . __('Stock ID') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Best Method') . '</th>
					<th>' . __('POA') . ' %</th>
				  </tr>';

			foreach ($categoryResults as $Item) {
				echo '<tr class="striped_row">
						<td>' . $Item['stockid'] . '</td>
						<td>' . $Item['description'] . '</td>
						<td class="number">' . $Item['method'] . '</td>
						<td class="number">' . locale_number_format($Item['poa'], 2) . '</td>
					  </tr>';
			}

			echo '</table>';
			echo '</div><br />';
		}
		else {
			prnMsg(__('No items found or unable to analyze category') , 'warn');
		}
	}
}

// Display form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>' . __('Forecast Parameters') . '</legend>';

// Forecast Scope
echo '<field>
		<fieldset>
		<legend>' . __('Forecast Scope') . ':</legend>
			<input type="radio" name="ForecastScope" id="scope_item" value="item"
				   onchange="toggleScopeFields()" ' . ((!isset($_POST['ForecastScope']) || $_POST['ForecastScope'] == 'item') ? 'checked="checked"' : '') . ' />
			<label for="scope_item">' . __('Single Item') . '</label><br />
			<input type="radio" name="ForecastScope" id="scope_category" value="category"
				   onchange="toggleScopeFields()" ' . ((isset($_POST['ForecastScope']) && $_POST['ForecastScope'] == 'category') ? 'checked="checked"' : '') . ' />
			<label for="scope_category">' . __('Entire Category') . '</label>
			</fieldset>
	  </field>';

// Stock ID (for single item)
echo '<field id="field_stockid">
		<label for="StockID">' . __('Stock Item') . ':</label>
		<input type="text" name="StockID" size="20" maxlength="20"
				   value="' . (isset($_POST['StockID']) ? $_POST['StockID'] : '') . '" />
			<a href="' . $RootPath . '/SelectProduct.php?ReloadForm=1">' . __('Select') . '</a>
	  </field>';

// Category ID (for category forecasting)
$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$CatResult = DB_query($SQL);

echo '<field id="field_category" style="display:none;">
		<label for="CategoryID">' . __('Stock Category') . ':</label>
		<select name="CategoryID">
			<option value="">-----------' . __('Select Category') . '-----------</option>';

while ($MyRow = DB_fetch_array($CatResult)) {
	$Selected = (isset($_POST['CategoryID']) && $_POST['CategoryID'] == $MyRow['categoryid']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['categoryid'] . '" ' . $Selected . '>' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
}

echo '</select>
	</field>';

// Location
$SQL = "SELECT loccode, locationname FROM locations ORDER BY locationname";
$Result = DB_query($SQL);

echo '<field>
		<label for="LocationCode">' . __('Location') . ':</label>
		<select name="LocationCode">
			<option value="">' . __('All Locations') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = (isset($_POST['LocationCode']) && $_POST['LocationCode'] == $MyRow['loccode']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['loccode'] . '" ' . $Selected . '>' . $MyRow['locationname'] . '</option>';
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
		<label for="ForecastMethod">' . __('Forecast Method') . ':</label>
		<select name="ForecastMethod" onchange="updateMethodFields(this.value)">
			<option value="">' . __('Select Method') . '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	$Selected = (isset($_POST['ForecastMethod']) && $_POST['ForecastMethod'] == $MyRow['methodid']) ? 'selected="selected"' : '';
	echo '<option value="' . $MyRow['methodid'] . '" ' . $Selected . ' title="' . htmlspecialchars($MyRow['methoddesc']) . '">' . $MyRow['methodid'] . ' - ' . $MyRow['methodname'] . '</option>';
}

echo '</select>
	</field>';

// Number of periods
echo '<field>
		<label for="Periods">' . __('Periods to Forecast') . ':</label>
		<input type="number" name="Periods" min="1" max="60" value="' . (isset($_POST['Periods']) ? $_POST['Periods'] : '12') . '" required="required" />
	  </field>';

// Periods of History
echo '<field>
		<label for="PeriodsHistory">' . __('Periods of History') . ':</label>
		<input type="number" name="PeriodsHistory" min="6" max="120" value="' . (isset($_POST['PeriodsHistory']) ? $_POST['PeriodsHistory'] : '24') . '" />
	  </field>';

// Description
echo '<field>
		<label for="Description">' . __('Description') . ':</label>
		<input type="text" name="Description" size="50" maxlength="100"
				   value="' . (isset($_POST['Description']) ? $_POST['Description'] : '') . '" />
	  </field>';

echo '</fieldset>';

echo '<fieldset id="methodparams">
		<legend>' . __('Method Parameters') . '</legend>';

// Method-specific parameters
// For methods 1, 2
echo '<field id="param_percent" style="display:none;">
		<label for="PercentIncrease">' . __('Percent Increase') . ':</label>
		<input type="number" name="PercentIncrease" step="0.01" value="5.0" /> %
	  </field>';

// For methods 4, 9
echo '<field id="param_periodsavg" style="display:none;">
		<label for="PeriodsAverage">' . __('Periods for Average') . ':</label>
		<input type="number" name="PeriodsAverage" min="2" max="12" value="4" />
	  </field>';

// For methods 10, 11, 12
echo '<field id="param_alpha" style="display:none;">
		<label for="SmoothingAlpha">' . __('Smoothing Alpha (α)') . ':</label>
		<input type="number" name="SmoothingAlpha" min="0.01" max="1" step="0.01" value="0.30" />
	</field>';

echo '<field id="param_beta" style="display:none;">
		<label for="SmoothingBeta">' . __('Smoothing Beta (β)') . ':</label>
		<input type="number" name="SmoothingBeta" min="0.01" max="1" step="0.01" value="0.30" />
	  </field>';

echo '<field id="param_gamma" style="display:none;">
		<label for="SmoothingGamma">' . __('Smoothing Gamma (γ)') . ':</label>
		<input type="number" name="SmoothingGamma" min="0.01" max="1" step="0.01" value="0.30" />
	  </field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Generate" value="' . __('Generate Forecast') . '" />
		<input type="submit" name="FindBestFit" value="' . __('Find Best Fit Method') . '" />
	  </div>';

echo '</form>';

echo '<script>
function updateMethodFields(method) {
	// Hide all parameter fields
	document.getElementById("param_percent").style.display = "none";
	document.getElementById("param_periodsavg").style.display = "none";
	document.getElementById("param_alpha").style.display = "none";
	document.getElementById("param_beta").style.display = "none";
	document.getElementById("param_gamma").style.display = "none";

	// Show relevant fields based on method
	method = parseInt(method);

	if (method == 1 || method == 2) {
		document.getElementById("param_percent").style.display = "";
	}

	if (method == 4 || method == 9) {
		document.getElementById("param_periodsavg").style.display = "";
	}

	if (method == 10 || method == 11 || method == 12) {
		document.getElementById("param_alpha").style.display = "";
	}

	if (method == 11 || method == 12) {
		document.getElementById("param_beta").style.display = "";
	}

	if (method == 12) {
		document.getElementById("param_gamma").style.display = "";
	}
}

function toggleScopeFields() {
	var scopeItem = document.getElementById("scope_item").checked;
	var scopeCategory = document.getElementById("scope_category").checked;

	// Show/hide appropriate fields
	if (scopeItem) {
		document.getElementById("field_stockid").style.display = "";
		document.getElementById("field_category").style.display = "none";
		document.querySelector("input[name=StockID]").required = true;
		document.querySelector("select[name=CategoryID]").required = false;
	} else if (scopeCategory) {
		document.getElementById("field_stockid").style.display = "none";
		document.getElementById("field_category").style.display = "";
		document.querySelector("input[name=StockID]").required = false;
		document.querySelector("select[name=CategoryID]").required = true;
	}
}

// Initialize on page load
window.addEventListener("DOMContentLoaded", function() {
	toggleScopeFields();
});
</script>';

include (__DIR__ . '/includes/footer.php');

?>

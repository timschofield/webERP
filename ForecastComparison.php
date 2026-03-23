<?php
/**
 * Forecast Method Comparison
 * Compare multiple forecasting methods side-by-side for a single item
 */

require (__DIR__ . '/includes/session.php');
require (__DIR__ . '/includes/DefineForecastClass.php');

$Title = __('Forecast Method Comparison');
$ViewTopic = 'ForecastManagement';
$BookMark = 'MethodComparison';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . '
	  </p>';

echo '<div class="page_help_text">' . __('Compare different forecasting methods side-by-side to determine which method works best for a specific item.') . '</div>';

if (isset($_POST['Compare'])) {

	$StockID = mb_strtoupper(trim($_POST['StockID']));
	$LocationCode = isset($_POST['LocationCode']) ? $_POST['LocationCode'] : '';
	$Periods = (int)$_POST['Periods'];
	$SelectedMethods = isset($_POST['Methods']) ? $_POST['Methods'] : array();

	if (count($SelectedMethods) == 0) {
		prnMsg(__('Please select at least one method to compare') , 'error');
	}
	else {

		// Get stock description
		$SQL = "SELECT description, decimalplaces FROM stockmaster WHERE stockid = '" . DB_escape_string($StockID) . "'";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) == 0) {
			prnMsg(__('Stock item not found') , 'error');
		}
		else {

			$MyRow = DB_fetch_array($Result);
			$DecimalPlaces = $MyRow['decimalplaces'];
			$StockDesc = $MyRow['description'];

			echo '<h3>' . __('Comparison Results for') . ': ' . $StockID . ' - ' . $StockDesc . '</h3>';

			$ForecastCalc = new ForecastCalculator($db);

			$Parameters = array();
			$Parameters['periodshistory'] = isset($_POST['PeriodsHistory']) ? (int)$_POST['PeriodsHistory'] : 24;

			// Generate forecasts for each selected method
			$Forecasts = array();
			$PeriodDates = array();

			foreach ($SelectedMethods as $Method) {
				$Method = (int)$Method;

				$Forecast = $ForecastCalc->calculateForecast($StockID, $LocationCode, $Method, $Periods, $Parameters);

				if (!isset($Forecast['error'])) {
					$Forecasts[$Method] = $Forecast;

					// Collect unique period dates
					foreach ($Forecast as $Period) {
						if (!in_array($Period['date'], $PeriodDates)) {
							$PeriodDates[] = $Period['date'];
						}
					}
				}
			}

			sort($PeriodDates);

			if (count($Forecasts) > 0) {

				// Get method names
				$SQL = "SELECT methodid, methodname FROM forecastmethods ORDER BY methodid";
				$Result = DB_query($SQL);
				$MethodNames = array();
				while ($MyRow = DB_fetch_array($Result)) {
					$MethodNames[$MyRow['methodid']] = $MyRow['methodname'];
				}

				// Display comparison table
				echo '<div style="overflow-x: auto;">';
				echo '<table class="selection">';
				echo '<tr>
						<th rowspan="1">' . __('Period') . '</th>
						<th rowspan="1">' . __('Date') . '</th>';

				foreach ($Forecasts as $MethodId => $Forecast) {
					echo '<th colspan="1">' . __('Method') . ' ' . $MethodId . '<br />' . '<small>' . $MethodNames[$MethodId] . '</small></th>';
				}

				echo '</tr>';

				// Display data rows
				foreach ($PeriodDates as $Idx => $PeriodDate) {

					echo '<tr class="striped_row">
							<td>' . ($Idx + 1) . '</td>
							<td>' . ConvertSQLDate($PeriodDate) . '</td>';

					$MinQty = PHP_FLOAT_MAX;
					$MaxQty = 0;
					$Qtys = array();

					foreach ($Forecasts as $MethodId => $Forecast) {
						foreach ($Forecast as $Period) {
							if ($Period['date'] == $PeriodDate) {
								$Qty = $Period['quantity'];
								$Qtys[$MethodId] = $Qty;
								$MinQty = min($MinQty, $Qty);
								$MaxQty = max($MaxQty, $Qty);
								break;
							}
						}
					}

					// Display quantities with highlighting
					foreach ($Forecasts as $MethodId => $Forecast) {
						if (isset($Qtys[$MethodId])) {
							$Qty = $Qtys[$MethodId];

							// Highlight min and max
							$Class = '';
							if ($Qty == $MaxQty && $MaxQty != $MinQty) {
								$Class = 'success'; // Highest

							}
							elseif ($Qty == $MinQty && $MaxQty != $MinQty) {
								$Class = 'warn'; // Lowest

							}

							echo '<td class="number ' . $Class . '">' . locale_number_format($Qty, $DecimalPlaces) . '</td>';
						}
						else {
							echo '<td>-</td>';
						}
					}

					echo '</tr>';
				}

				// Calculate totals
				echo '<tr class="total_row">
						<td colspan="2"><b>' . __('Total') . '</b></td>';

				foreach ($Forecasts as $MethodId => $Forecast) {
					$Total = 0;
					foreach ($Forecast as $Period) {
						$Total += $Period['quantity'];
					}
					echo '<td class="number"><b>' . locale_number_format($Total, $DecimalPlaces) . '</b></td>';
				}

				echo '</tr>';

				// Calculate averages
				echo '<tr class="total_row">
						<td colspan="2"><b>' . __('Average per Period') . '</b></td>';

				foreach ($Forecasts as $MethodId => $Forecast) {
					$Total = 0;
					foreach ($Forecast as $Period) {
						$Total += $Period['quantity'];
					}
					$Avg = count($Forecast) > 0 ? $Total / count($Forecast) : 0;
					echo '<td class="number"><b>' . locale_number_format($Avg, $DecimalPlaces) . '</b></td>';
				}

				echo '</tr>';

				// Show variance between methods
				echo '<tr class="total_row">
						<td colspan="2"><b>' . __('Std Deviation') . '</b></td>';

				// Calculate standard deviation for each period
				foreach ($PeriodDates as $PeriodDate) {
					$Values = array();
					foreach ($Forecasts as $MethodId => $Forecast) {
						foreach ($Forecast as $Period) {
							if ($Period['date'] == $PeriodDate) {
								$Values[] = $Period['quantity'];
								break;
							}
						}
					}

					if (count($Values) > 1) {
						$Mean = array_sum($Values) / count($Values);
						$Variance = 0;
						foreach ($Values as $Value) {
							$Variance += pow($Value - $Mean, 2);
						}
						$StdDev = sqrt($Variance / count($Values));
					}
					else {
						$StdDev = 0;
					}
				}

				// Show overall variation
				$AllValues = array();
				foreach ($Forecasts as $Forecast) {
					foreach ($Forecast as $Period) {
						$AllValues[] = $Period['quantity'];
					}
				}

				if (count($AllValues) > 1) {
					$Mean = array_sum($AllValues) / count($AllValues);
					$Variance = 0;
					foreach ($AllValues as $Value) {
						$Variance += pow($Value - $Mean, 2);
					}
					$OverallStdDev = sqrt($Variance / count($AllValues));

					echo '<td colspan="' . count($Forecasts) . '" class="number"><b>' . locale_number_format($OverallStdDev, $DecimalPlaces) . '</b></td>';
				}

				echo '</tr>';

				echo '</table>';
				echo '</div>';

				// Show recommendation
				echo '<form>';
				echo '<fieldset>
						<legend>' . __('Recommendation') . '</legend>';

				echo '<p>' . __('For more accurate method selection based on historical accuracy, use the') . ' <b>' . __('Find Best Fit') . '</b> ' . __('feature in') . ' ' . '<a href="ForecastGeneration.php">ForecastGeneration.php</a></p>';

				echo '<p>' . __('Methods with higher variation may indicate:') . '</p>';
				echo '<ul>';
				echo '<li>' . __('Unstable demand patterns') . '</li>';
				echo '<li>' . __('Need for seasonal adjustment (try Method 12)') . '</li>';
				echo '<li>' . __('Insufficient historical data') . '</li>';
				echo '</ul>';

				echo '</fieldset>';
				echo '</form>';

			}
			else {
				prnMsg(__('No forecast data generated. Check that the item has sales history.') , 'warn');
			}
		}
	}
}
else {

	// Display form
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
		<legend>' . __('Comparison Parameters') . '</legend>';

	// Stock ID
	echo '<field>
		<label for="StockID">' . __('Stock Item') . ':</label>
		<input type="text" name="StockID" size="20" maxlength="20" required="required"
				   value="' . (isset($_POST['StockID']) ? $_POST['StockID'] : '') . '" />
			<a href="' . $RootPath . '/SelectProduct.php?ReloadForm=1">' . __('Select') . '</a>
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

	// Periods
	echo '<field>
			<label for="Periods">' . __('Periods to Forecast') . ':</label>
			<input type="number" name="Periods" min="1" max="24" value="' . (isset($_POST['Periods']) ? $_POST['Periods'] : '12') . '" required="required" />
		</field>';

	// Periods of History
	echo '<field>
			<label for="PeriodsHistory">' . __('Periods of History') . ':</label>
			<input type="number" name="PeriodsHistory" min="6" max="120" value="' . (isset($_POST['PeriodsHistory']) ? $_POST['PeriodsHistory'] : '24') . '" />
		</field>';

	echo '</table>
	  </fieldset>';

	// Method selection
	echo '<fieldset style="width:40%">
		<legend>' . __('Select Methods to Compare') . '</legend>';

	echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2px;">';

	$SQL = "SELECT methodid, methodname, methoddesc FROM forecastmethods WHERE active = 1 ORDER BY methodid";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		$Checked = (isset($_POST['Methods']) && in_array($MyRow['methodid'], $_POST['Methods'])) ? 'checked="checked"' : '';

		echo '<div style="padding: 5px;">
			<label>
				<input type="checkbox" name="Methods[]" value="' . $MyRow['methodid'] . '" ' . $Checked . ' />
				<b>' . $MyRow['methodid'] . '. ' . $MyRow['methodname'] . '</b><br />
				<small>' . $MyRow['methoddesc'] . '</small>
			</label>
		  </div>';
	}

	echo '</div>';

	echo '<div class="centre">
		<input type="button" value="' . __('Select All') . '" onclick="selectAllMethods(true)" />
		<input type="button" value="' . __('Select None') . '" onclick="selectAllMethods(false)" />
	  </div>';

	echo '</fieldset>';

	echo '<div class="centre">
		<input type="submit" name="Compare" value="' . __('Compare Methods') . '" />
	  </div>';

	echo '</div>
	  </form>';

	echo '<script>
function selectAllMethods(select) {
	var checkboxes = document.getElementsByName("Methods[]");
	for (var i = 0; i < checkboxes.length; i++) {
		checkboxes[i].checked = select;
	}
}
</script>';

}
include (__DIR__ . '/includes/footer.php');
?>

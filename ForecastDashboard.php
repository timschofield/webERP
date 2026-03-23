<?php
/**
 * Forecast Management Dashboard
 * Central hub for all forecast management functions
 */

require (__DIR__ . '/includes/session.php');

$Title = __('Forecast Management');
$ViewTopic = 'ForecastManagement';
$BookMark = 'ForecastDashboard';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />
		' . $Title . '
	  </p>';

echo '<div class="page_help_text">' . __('Forecast Management provides statistical demand forecasting for inventory planning and MRP. Choose a function below to get started.') . '</div>';

// Get forecast statistics
$SQL = "SELECT COUNT(*) as totalforecasts,
			   SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as activeforecasts
		FROM forecastheader";
$Result = DB_query($SQL);
$Statistics = DB_fetch_array($Result);
if ($Statistics['activeforecasts'] == NULL) {
	$Statistics['activeforecasts'] = 0;
	$Statistics['totalforecasts'] = 0;
}

$SQL = "SELECT COUNT(*) as summarycount FROM forecastsummary WHERE active = 1";
$Result = DB_query($SQL);
$SummaryStatistics = DB_fetch_array($Result);

$SQL = "SELECT MIN(perioddate) as mindate, MAX(perioddate) as maxdate, COUNT(*) as records
		FROM forecastsaleshistory";
$Result = DB_query($SQL);
$HistoryStatistics = DB_fetch_array($Result);

// Display statistics
echo '<form>';
echo '<fieldset style="width:auto">
		<legend>' . __('System Statistics') . '</legend>
		<table class="selection">';

echo '<tr>
		<td><b>' . __('Active Detail Forecasts') . ':</b></td>
		<td>' . number_format($Statistics['activeforecasts']) . ' ' . __('of') . ' ' . number_format($Statistics['totalforecasts']) . '</td>
	  </tr>';

echo '<tr>
		<td><b>' . __('Active Summary Forecasts') . ':</b></td>
		<td>' . number_format($SummaryStatistics['summarycount']) . '</td>
	  </tr>';

if ($HistoryStatistics['records'] > 0) {
	echo '<tr>
			<td><b>' . __('Sales History Range') . ':</b></td>
			<td>' . ConvertSQLDate($HistoryStatistics['mindate']) . ' - ' . ConvertSQLDate($HistoryStatistics['maxdate']) . ' (' . number_format($HistoryStatistics['records']) . ' ' . __('records') . ')</td>
		  </tr>';
}

echo '</table>
	  </fieldset></form>';

// Display menu sections
echo '<div class="row">';

// Setup section
echo '<div class="column">';
echo '<fieldset>
		<legend><b>' . __('Setup & Configuration') . '</b></legend>
		<ul class="menu">';

echo '<li><a href="' . $RootPath . '/ForecastExtractActuals.php">' . __('Extract Sales Actuals') . '</a><br />
		  <span class="help">' . __('Import sales history for forecasting') . '</span></li>';

echo '<li><a href="' . $RootPath . '/ForecastConstants.php">' . __('Forecast Constants') . '</a><br />
		  <span class="help">' . __('Configure smoothing parameters per item') . '</span></li>';

echo '</ul>
	  </fieldset>';
echo '</div>';

// Forecast Generation section
echo '<div class="column">';
echo '<fieldset>
		<legend><b>' . __('Forecast Generation') . '</b></legend>
		<ul class="menu">';

echo '<li><a href="' . $RootPath . '/ForecastGeneration.php">' . __('Generate Forecast') . '</a><br />
		  <span class="help">' . __('Create forecast for single item') . '</span></li>';

echo '<li><a href="' . $RootPath . '/ForecastBatchGeneration.php">' . __('Batch Generation') . '</a><br />
		  <span class="help">' . __('Generate forecasts for multiple items') . '</span></li>';

echo '<li><a href="' . $RootPath . '/ForecastComparison.php">' . __('Method Comparison') . '</a><br />
		  <span class="help">' . __('Compare methods side-by-side') . '</span></li>';

echo '<li><a href="' . $RootPath . '/ForecastSummary.php">' . __('Summary Forecasts') . '</a><br />
		  <span class="help">' . __('Create/manage summary forecasts') . '</span></li>';

echo '</ul>
	  </fieldset>';
echo '</div>';

// Review & Analysis section
echo '<div class="column">';
echo '<fieldset>
		<legend><b>' . __('Review & Analysis') . '</b></legend>
		<ul class="menu">';

echo '<li><a href="' . $RootPath . '/ForecastReview.php">' . __('Forecast Review') . '</a><br />
		  <span class="help">' . __('Review and revise detail forecasts') . '</span></li>';

echo '<li><a href="' . $RootPath . '/ForecastInquiry.php">' . __('Forecast Inquiry') . '</a><br />
		  <span class="help">' . __('Query and compare forecasts') . '</span></li>';

echo '<li><a href="' . $RootPath . '/ForecastAccuracy.php">' . __('Accuracy Report') . '</a><br />
		  <span class="help">' . __('Analyze forecast accuracy metrics') . '</span></li>';

echo '</ul>
	  </fieldset>';
echo '</div>';

echo '</div>'; // End row
// Display CSS for layout
echo '<style>
.row {
	display: flex;
	flex-wrap: wrap;
	margin: 0 -10px;
}

.column {
	flex: 1;
	min-width: 300px;
	padding: 0 10px;
	margin-bottom: 20px;
}

ul.menu {
	list-style-type: none;
	padding-left: 0;
}

ul.menu li {
	margin-bottom: 15px;
	padding: 10px;
	background-color: #f9f9f9;
	border-left: 3px solid #4CAF50;
}

ul.menu li a {
	font-weight: bold;
	font-size: 14px;
}

ul.menu li .help {
	display: block;
	font-size: 12px;
	color: #666;
	margin-top: 5px;
}
</style>';

// Documentation link
echo '<form><fieldset>
		<legend>' . __('Documentation & Help') . '</legend>
		<p>' . __('For complete documentation, see') . ': <a href="' . $RootPath . '/doc/ForecastManagement.md">' . __('Forecast Management Documentation') . '</a></p>
		<p>' . __('Quick start guide') . ': <a href="' . $RootPath . '/FORECAST_QUICKSTART.md">' . __('Quick Start') . '</a></p>
	  </fieldset></form>';

include (__DIR__ . '/includes/footer.php');

?>

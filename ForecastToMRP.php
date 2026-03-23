<?php
/**
 * Convert Forecasts to MRP Demands
 * Creates MRP demand records from active forecasts
 */

require (__DIR__ . '/includes/session.php');

$Title = __('Forecast to MRP Demands');
$ViewTopic = 'ForecastManagement';
$BookMark = 'ForecastToMRP';
include (__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">';
echo '<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . $Title . '</p>';

echo '<div class="page_help_text">';
echo __('This function creates MRP demands from active forecasts. It converts forecast quantities into production/purchase requirements for the MRP system.');
echo '</div>';

if (isset($_POST['Generate'])) {

	//	$FromDate = ConvertSQLDate($_POST['FromDate']);
	//	$ToDate = ConvertSQLDate($_POST['ToDate']);
	$ReplaceExisting = isset($_POST['ReplaceExisting']) ? true : false;

	// Validate dates
	if (strtotime($_POST['FromDate']) > strtotime($_POST['ToDate'])) {
		prnMsg(__('From Date must be before To Date') , 'error');
	}
	else {

		echo '<div class="centre">';
		echo '<br />' . __('Starting MRP demand generation from forecasts') . ': ' . date('H:i:s') . '<br />';
		flush();

		// Clear existing MRP demands if requested
		if ($ReplaceExisting) {
			echo __('Clearing existing MRP demands') . '...<br />';
			flush();

			$SQL = "DELETE FROM mrpdemands
					WHERE duedate >= '" . FormatDateForSQL($_POST['FromDate']) . "'
					  AND duedate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
					  AND mrpdemandtype = '" . $_POST['MRPDemandType'] . "'";

			DB_query($SQL);
		}

		echo __('Converting forecasts to MRP demands') . '...<br />';
		flush();

		// Get active forecasts and create MRP demands
		// Uses forecastqty if revisedqty is null, otherwise uses revisedqty
		$SQL = "INSERT INTO mrpdemands
				(stockid, mrpdemandtype, quantity, duedate)
				SELECT
					fh.stockid,
					'" . $_POST['MRPDemandType'] . "' as mrpdemandtype,
					COALESCE(fd.revisedqty, fd.forecastqty) as quantity,
					fd.perioddate as duedate
				FROM forecastdetails fd
				INNER JOIN forecastheader fh
					ON fd.forecastid = fh.forecastid
				WHERE fh.active = 1
				  AND fd.perioddate >= '" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND fd.perioddate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
				  AND COALESCE(fd.revisedqty, fd.forecastqty) > 0";

		// Apply location filter if specified
		if (isset($_POST['Location']) && $_POST['Location'] != 'All') {
			$SQL .= " AND fh.locationcode = '" . $_POST['Location'] . "'";
		}

		// Apply stock category filter if specified
		if (isset($_POST['Categories']) && count($_POST['Categories']) > 0) {
			$SQL .= " AND EXISTS (
						SELECT 1 FROM stockmaster sm
						WHERE sm.stockid = fh.stockid
						  AND sm.categoryid IN ('" . implode("','", $_POST['Categories']) . "')
					  )";
		}

		// Exclude if already exists (if not replacing)
		if (!$ReplaceExisting) {
			$SQL .= " AND NOT EXISTS (
						SELECT 1 FROM mrpdemands md
						WHERE md.stockid = fh.stockid
						  AND md.duedate = fd.perioddate
						  AND md.mrpdemandtype = '" . $_POST['MRPDemandType'] . "'
					  )";
		}

		$SQL .= " GROUP BY fh.stockid, fd.perioddate, COALESCE(fd.revisedqty, fd.forecastqty)";

		$Result = DB_query($SQL);

		$RecordsInserted = DB_Affected_Rows($Result);

		echo __('Generation complete') . ': ' . date('H:i:s') . '<br />';
		echo '</div>';
		prnMsg(__('MRP Demands created') . ': ' . number_format($RecordsInserted) , 'success');
	}
}

// Display form
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>';
echo '<legend>' . __('Forecast to MRP Parameters') . '</legend>';

// Date range
echo '<field>
		<label for="FromDate">' . __('From Date') . ':</label>
		<input type="text" name="FromDate" size="11" maxlength="10" class="date" value="' . Date($_SESSION['DefaultDateFormat']) . '" />
	</field>';

echo '<field>
		<label for="ToDate">' . __('To Date') . ':</label>
		<input type="text" name="ToDate" size="11" maxlength="10" class="date" value="' . Date($_SESSION['DefaultDateFormat'], strtotime('+3 months')) . '" />
	</field>';

// MRP Demand Type
echo '<field>
		<label for="MRPDemandType">' . __('MRP Demand Type') . ':</label>
		<select name="MRPDemandType">';

$SQL = "SELECT mrpdemandtype, description FROM mrpdemandtypes ORDER BY mrpdemandtype";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="' . $MyRow['mrpdemandtype'] . '">' . $MyRow['description'] . '</option>';
}
echo '</select>
	</field>';

// Location
echo '<field>
		<label for="Location">' . __('Location') . ':</label>
		<select name="Location">
		<option value="All">' . __('All Locations') . '</option>';

$SQL = "SELECT loccode, locationname FROM locations ORDER BY locationname";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
}
echo '</select>
	</field>';

// Categories
echo '<field>
		<label for="Categories">' . __('Stock Categories') . ':</label>
		<select name="Categories[]" multiple="multiple" size="5">';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="' . $MyRow['categoryid'] . '" selected>' . $MyRow['categorydescription'] . '</option>';
}
echo '</select>
	</field>';

// Replace existing option
echo '<field>
		<label for="ReplaceExisting">' . __('Replace Existing') . ':</label>
		<input type="checkbox" name="ReplaceExisting" value="1" checked />
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Generate" value="' . __('Generate MRP Demands') . '" />
	</div>';

echo '</form>';

include (__DIR__ . '/includes/footer.php');
?>

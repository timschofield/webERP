<?php
/**
 * Extract Sales Actuals for Forecast Management
 * Copy sales order history into forecast sales history cache
 */

require __DIR__ . '/includes/session.php';

$Title = __('Extract Sales Actuals');
$ViewTopic = 'ForecastManagement';
$BookMark = 'ExtractSalesActuals';
include __DIR__ . '/includes/header.php';

echo '<p class="page_title_text">
		<img src="' . $RootPath . "/css/" . $Theme . '/images/inventory.png" title="' . __("Inventory") . '" alt="" />' . $Title . '</p>';

echo '<div class="page_help_text">' . __('This function extracts sales order history and stores it in an optimized format for forecast generation. Run this periodically to keep forecast data current') . '</div>';

if (isset($_POST["Extract"])) {
	$FromDate = ConvertSQLDate($_POST["FromDate"]);
	$ToDate = ConvertSQLDate($_POST["ToDate"]);
	$ReplaceExisting = isset($_POST["ReplaceExisting"]) ? true : false;

	// Validate dates
	if (strtotime($FromDate) > strtotime($ToDate)) {
		prnMsg(__('From Date must be before To Date') , 'error');
	}
	else {
		echo '<div class="centre">';
		echo __("Starting extraction") . ': ' . date("H:i:s") . '<br />';
		flush();

		// Clear existing data if requested
		if ($ReplaceExisting) {
			echo __("Clearing existing forecast sales history") . "...<br />";
			flush();

			$SQL = "TRUNCATE forecastsaleshistory";

			DB_query($SQL);
		}

		echo __("Extracting sales data") . "...<br />";
		flush();

		// Extract from salesorders and salesorderdetails tables (direct approach)
		$SQL = "INSERT INTO forecastsaleshistory
				(stockid, locationcode, customerid, customertype, area, salesperson, perioddate, quantity, amount, cost)
				SELECT
					sod.stkcode as stockid,
					so.fromstkloc as locationcode,
					so.debtorno as customerid,
					dm.salestype as customertype,
					cb.area,
					so.salesperson,
					periods.lastdate_in_period as perioddate,
					SUM(sod.quantity) as quantity,
					SUM(sod.quantity * sod.unitprice * (1 - sod.discountpercent)) as amount,
					SUM(sod.quantity * sm.lastcost) as cost
				FROM salesorderdetails sod
				INNER JOIN salesorders so
					ON sod.orderno = so.orderno
				INNER JOIN debtorsmaster dm
					ON so.debtorno = dm.debtorno
				INNER JOIN custbranch cb
					ON so.debtorno = cb.debtorno
					AND so.branchcode = cb.branchcode
				INNER JOIN stockmaster sm
					ON sod.stkcode = sm.stockid
				INNER JOIN periods
					ON MONTH(periods.lastdate_in_period) = MONTH(so.orddate)
					AND YEAR(periods.lastdate_in_period) = YEAR(so.orddate)
				WHERE so.orddate >= '" . FormatDateForSQL($FromDate) . "'
				  AND so.orddate <= '" . FormatDateForSQL($ToDate) . "'
				  AND so.quotation = 0";

		if (!$ReplaceExisting) {
			$SQL .= " AND NOT EXISTS (
						SELECT 1 FROM forecastsaleshistory fsh
						WHERE fsh.stockid = sod.stkcode
						  AND fsh.locationcode = so.fromstkloc
						  AND fsh.perioddate = periods.lastdate_in_period
						  AND fsh.customerid = so.debtorno
					  )";
		}

		$SQL .= " GROUP BY sod.stkcode, so.fromstkloc, so.debtorno, dm.salestype, cb.area, so.salesperson,
				  periods.lastdate_in_period";
		$Result = DB_query($SQL);

		echo __("Extraction complete") . ": " . date("H:i:s") . "<br />";
		prnMsg(__("Extraction completed") , 'success');
		echo "</div>";
	}
	// Display statistics
	$SQL = "SELECT
		MIN(perioddate) as mindate,
		MAX(perioddate) as maxdate,
		COUNT(DISTINCT stockid) as items,
		COUNT(*) as records,
		SUM(quantity) as totalqty
		FROM forecastsaleshistory";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$Stats = DB_fetch_array($Result);

		if ($Stats["records"] > 0) {
			echo '<form method="post">
					<input type="hidden" name="FormID" value="' . $_SESSION["FormID"] . '" />';

			echo '<fieldset>
					<legend>' . __("Current Sales History Statistics") . '</legend>';

			echo '<field>
					<label>' . __("Date Range") . ':</label>
					<fieldtext>' . ConvertSQLDate($Stats["mindate"]) . ' to ' . ConvertSQLDate($Stats["maxdate"]) . '</fieldtext>
			  </field>';

			echo '<field>
					<label>' . __("Number of Items") . ':</label>
					<fieldtext>' . number_format($Stats["items"]) . '</fieldtext>
			  </field>';

			echo '<field>
					<label>' . __("Total Records") . ':</label>
					<fieldtext>' . number_format($Stats["records"]) . '</fieldtext>
			  </field>';
			echo '<field>
					<label>' . __("Total Quantity") . ':</label>
					<fieldtext>' . locale_number_format($Stats["totalqty"], 0) . '</fieldtext>
			  </field>';

			echo '</fieldset>
			  <div class="centre">
				<input type="submit" name="close" value="Close" />
			</div>
		</form>';
		}
	}
}
else {
	// Display form
	echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "UTF-8") . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION["FormID"] . '" />';

	echo '<fieldset>
			<legend>' . __("Extraction Parameters") . '</legend>';

	// From Date
	echo '<field>
			<label for="FromDate">' . __("From Date") . ':</label>
			<input type="date" name="FromDate" value="' . date("Y-m-d", strtotime("-24 months")) . '" required="required" /></td>
		</field>';

	// To Date
	echo '<field>
			<label for="ToDate">' . __("To Date") . ':</label>
			<input type="date" name="ToDate" value="' . date("Y-m-d") . '" required="required" />
		</field>';

	// Replace existing
	echo '<field>
			<label for="ReplaceExisting">' . __("Replace Existing Data") . ':</label>
			<input type="checkbox" checked="checked" name="ReplaceExisting" value="1" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Extract" value="' . __("Extract Sales Actuals") . '" />
		</div>';

	echo "</form>";
}

include __DIR__ . "/includes/footer.php";

?>

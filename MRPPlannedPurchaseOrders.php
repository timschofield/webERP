<?php
// Report of purchase parts that MRP has determined should have
// purchase orders created for them
require (__DIR__ . '/includes/session.php');
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

if (isset($_POST['cutoffdate'])) {
	$_POST['cutoffdate'] = ConvertSQLDate($_POST['cutoffdate']);
}

if (!DB_table_exists('mrprequirements')) {
	$Title = __('MRP error');
	include ('includes/header.php');
	echo '<br />';
	prnMsg(__('The MRP calculation must be run before you can run this report') . '<br />' . __('To run the MRP calculation click') . ' ' . '<a href="' . $RootPath . '/MRP.php">' . __('here') . '</a>', 'error');
	include ('includes/footer.php');
	exit();
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$WhereDate = ' ';
	$ReportDate = ' ';
	if (Is_Date($_POST['cutoffdate'])) {
		$FormatDate = FormatDateForSQL($_POST['cutoffdate']);
		$WhereDate = " AND duedate <= '" . $FormatDate . "' ";
		$ReportDate = ' ' . __('Through') . ' ' . $_POST['cutoffdate'];
	}

	if ($_POST['Consolidation'] == 'None') {
		$SQL = "SELECT mrpplannedorders.*,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					stockmaster.actualcost as computedcost
				FROM mrpplannedorders
				INNER JOIN stockmaster
					ON mrpplannedorders.part = stockmaster.stockid
				WHERE stockmaster.mbflag IN ('B','P') " . $WhereDate . "
				ORDER BY mrpplannedorders.part,mrpplannedorders.duedate";
	}
	elseif ($_POST['Consolidation'] == 'Weekly') {
		$SQL = "SELECT mrpplannedorders.part,
					SUM(mrpplannedorders.supplyquantity) as supplyquantity,
					TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
					MIN(mrpplannedorders.duedate) as duedate,
					MIN(mrpplannedorders.mrpdate) as mrpdate,
					COUNT(*) AS consolidatedcount,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					stockmaster.actualcost as computedcost
				FROM mrpplannedorders
				INNER JOIN stockmaster
					ON mrpplannedorders.part = stockmaster.stockid
				WHERE stockmaster.mbflag IN ('B','P') " . $WhereDate . "
				GROUP BY mrpplannedorders.part,
					weekindex,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					stockmaster.actualcost,
					computedcost
				ORDER BY mrpplannedorders.part,weekindex";
	}
	else { // This else consolidates by month
		$SQL = "SELECT mrpplannedorders.part,
					SUM(mrpplannedorders.supplyquantity) as supplyquantity,
					EXTRACT(YEAR_MONTH from duedate) AS yearmonth,
					MIN(mrpplannedorders.duedate) as duedate,
					MIN(mrpplannedorders.mrpdate) as mrpdate,
					COUNT(*) AS consolidatedcount,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					stockmaster.actualcost as computedcost
				FROM mrpplannedorders
				INNER JOIN stockmaster
					ON mrpplannedorders.part = stockmaster.stockid
				WHERE stockmaster.mbflag IN ('B','P') " . $WhereDate . "
				GROUP BY mrpplannedorders.part,
					yearmonth,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					stockmaster.actualcost,
					computedcost
				ORDER BY mrpplannedorders.part,yearmonth";
	}
	$ErrMsg = __('The MRP planned purchase orders could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) { //then there is nothing to print
		$Title = __('Print MRP Planned Purchase Orders');
		include ('includes/header.php');
		prnMsg(__('There were no items with planned purchase orders'), 'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include ('includes/footer.php');
		exit();
	}

	// Build the report HTML
	$HTML = '
		<html>
		<head>
			<style>
				body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
				h2 { text-align: center; }
				table { border-collapse: collapse; width: 100%; }
				th, td { border: 1px solid #444; padding: 4px 8px; font-size: 11px; }
				th { background-color: #eee; }
				.alt { background-color: #e0ebff; }
				.number { text-align: right; }
			</style>
		</head>
		<body>
			<h2>' . $_SESSION['CompanyRecord']['coyname'] . '<br>' . __('MRP Planned Purchase Orders Report') . '</h2>
			<div style="text-align:center; margin-bottom: 10px;">' . __('Consolidation') . ': ' . htmlspecialchars($_POST['Consolidation']) . ' &nbsp;&nbsp; ' . __('Through') . ' ' . htmlspecialchars($_POST['cutoffdate']) . '</div>
			<table>
				<tr>
					<th>' . __('Part Number') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Due Date') . '</th>
					<th>' . __('MRP Date') . '</th>
					<th>' . __('Quantity') . '</th>
					<th>' . __('Unit Cost') . '</th>
					<th>' . __('Ext. Cost') . '</th>';

	if ($_POST['Consolidation'] == 'None') {
		$HTML .= '<th>' . __('Source Type') . '</th>
					  <th>' . __('Source Order') . '</th>';
	}
	else {
		$HTML .= '<th>' . __('Consolidations') . '</th>';
	}

	$HTML .= '</tr>';

	$TotalPartQty = 0;
	$TotalPartCost = 0;
	$Total_ExtCost = 0;
	$Partctr = 0;
	$Fill = false;

	while ($MyRow = DB_fetch_array($Result)) {
		$Fill = !$Fill;
		$rowClass = $Fill ? 'alt' : '';
		$ExtCost = $MyRow['supplyquantity'] * $MyRow['computedcost'];

		$HTML .= '<tr class="' . $rowClass . '">';
		$HTML .= '<td>' . htmlspecialchars($MyRow['part']) . '</td>';
		$HTML .= '<td>' . htmlspecialchars($MyRow['description']) . '</td>';
		$HTML .= '<td>' . ConvertSQLDate($MyRow['duedate']) . '</td>';
		$HTML .= '<td>' . ConvertSQLDate($MyRow['mrpdate']) . '</td>';
		$HTML .= '<td class="number">' . locale_number_format($MyRow['supplyquantity'], $MyRow['decimalplaces']) . '</td>';
		$HTML .= '<td class="number">' . locale_number_format($MyRow['computedcost'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
		$HTML .= '<td class="number">' . locale_number_format($ExtCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';

		if ($_POST['Consolidation'] == 'None') {
			$HTML .= '<td>' . htmlspecialchars($MyRow['ordertype']) . '</td>';
			$HTML .= '<td>' . htmlspecialchars($MyRow['orderno']) . '</td>';
		}
		else {
			$HTML .= '<td class="number">' . htmlspecialchars($MyRow['consolidatedcount']) . '</td>';
		}

		$HTML .= '</tr>';

		$TotalPartQty += $MyRow['supplyquantity'];
		$TotalPartCost += $ExtCost;
		$Total_ExtCost += $ExtCost;
		$Partctr++;
	}

	$HTML .= '<tr>
			<th colspan="4" style="text-align:right;">' . __('Totals') . ':</th>
			<th class="number">' . locale_number_format($TotalPartQty, 2) . '</th>
			<th></th>
			<th class="number">' . locale_number_format($Total_ExtCost, 2) . '</th>';

	if ($_POST['Consolidation'] == 'None') {
		$HTML .= '<th colspan="2"></th>';
	}
	else {
		$HTML .= '<th></th>';
	}
	$HTML .= '</tr>';

	$HTML .= '</table>';
	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	}
	else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_MRPPlannedPurchases_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	}
	else {
		$Title = __('MRP Planned Purchase Orders');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('MRP Planned Purchase Orders') . '" alt="" />' . ' ' . __('MRP Planned Purchase Orders') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}
}
else { /*The option to print PDF was not hit so display form */

	$Title = __('MRP Planned Purchase Orders Reporting');
	$ViewTopic = 'MRP';
	$BookMark = '';
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<fieldset>
				<legend>', __('Report Criteria'), '</legend>
				<field>
					<label for="Consolidation">' . __('Consolidation') . ':</label>
					<select required="required" name="Consolidation">
						<option selected="selected" value="None">' . __('None') . '</option>
						<option value="Weekly">' . __('Weekly') . '</option>
						<option value="Monthly">' . __('Monthly') . '</option>
					</select>
			</field>
			<field>
				<label for="Fill">' . __('Print Option') . ':</label>
				<select name="Fill">
					<option selected="selected" value="yes">' . __('Print With Alternating Highlighted Lines') . '</option>
					<option value="no">' . __('Plain Print') . '</option>
				</select>
			</field>
			<field>
				<label for="cutoffdate">' . __('Cut Off Date') . ':</label>
				<input required="required" type="date" name="cutoffdate" autofocus="autofocus" maxlength="10" size="11" value="' . date('Y-m-d') . '" />
			</field>
			</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
			</div>
		</form>';

	include ('includes/footer.php');

} /*end of else not PrintPDF */

// Unchanged helper functions
function GetPartInfo($Part) {
	// Get last purchase order date and supplier for part, and also preferred supplier
	// Printed when there is a part break
	$SQL = "SELECT orddate as maxdate,
				   purchorders.orderno
			FROM purchorders INNER JOIN purchorderdetails
			ON purchorders.orderno = purchorderdetails.orderno
			WHERE purchorderdetails.itemcode = '" . $Part . "'
			ORDER BY orddate DESC LIMIT 1";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		$PartInfo[] = ConvertSQLDate($MyRow['maxdate']);
		$OrderNo = $MyRow['orderno'];

		$SQL = "SELECT supplierno
				FROM purchorders
				WHERE purchorders.orderno = '" . $OrderNo . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$PartInfo[] = $MyRow['supplierno'];

		$SQL = "SELECT supplierno, conversionfactor
				FROM purchdata
				WHERE stockid = '" . $Part . "'
				AND preferred='1'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$PartInfo[] = $MyRow['supplierno'];
		$PartInfo[] = $MyRow['conversionfactor'];

		return $PartInfo;
	}
	else {
		return array('', '', '', 1);
	}
}


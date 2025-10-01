<?php
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
				WHERE stockmaster.mbflag = 'M' " . $WhereDate . "
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
				WHERE stockmaster.mbflag = 'M' " . $WhereDate . "
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
	else { // Consolidate by month
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
				WHERE stockmaster.mbflag = 'M' " . $WhereDate . "
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
	$ErrMsg = __('The MRP planned work orders could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('MRP Planned Work Orders');
		include ('includes/header.php');
		prnMsg(__('There were no items with demand greater than supply'), 'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include ('includes/footer.php');
		exit();
	}

	// Build the report
	$HTML = '<html><head><style>
			body { font-size: 10pt; font-family: Arial, sans-serif; }
			.report-title { font-size: 16pt; font-weight: bold; margin-bottom: 10px; }
			.company { font-size: 12pt; font-weight: bold; }
			table { border-collapse: collapse; width: 100%; }
			th, td { border: 1px solid #555; padding: 3px; }
			th { background: #eee; }
			.alt { background: #e0ebff; }
			.right { text-align: right;}
			.center { text-align: center;}
		</style></head><body>';

	$HTML .= '<div class="company">' . $_SESSION['CompanyRecord']['coyname'] . '</div>';
	$HTML .= '<div class="report-title">' . __('MRP Planned Work Orders Report') . $ReportDate . '</div>';
	$HTML .= '<div>' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '</div>';

	$HTML .= '<table>';
	$HTML .= '<tr>
			<th>' . __('Part Number') . '</th>
			<th>' . __('Due Date') . '</th>
			<th>' . __('MRP Date') . '</th>
			<th>' . __('Quantity') . '</th>
			<th>' . __('Ext. Cost') . '</th>';

	if ($_POST['Consolidation'] == 'None') {
		$HTML .= '<th>' . __('Source Type') . '</th>
					  <th>' . __('Source Order') . '</th>';
	}
	else {
		$HTML .= '<th>' . __('Consolidation Count') . '</th>';
	}
	$HTML .= '</tr>';

	$HoldPart = '';
	$HoldDescription = '';
	$HoldMBFlag = '';
	$HoldCost = 0;
	$HoldDecimalPlaces = 0;
	$TotalPartQty = 0;
	$TotalPartCost = 0;
	$Total_ExtCost = 0;
	$Partctr = 0;
	$rowClass = false;

	while ($MyRow = DB_fetch_array($Result)) {
		$rowClass = !$rowClass;
		$class = $rowClass && $_POST['Fill'] == 'yes' ? 'alt' : '';
		$FormatedSupDueDate = ConvertSQLDate($MyRow['duedate']);
		$FormatedSupMRPDate = ConvertSQLDate($MyRow['mrpdate']);
		$ExtCost = $MyRow['supplyquantity'] * $MyRow['computedcost'];

		$HTML .= '<tr class="' . $class . '">
				<td>' . htmlspecialchars($MyRow['part']) . '</td>
				<td class="right">' . $FormatedSupDueDate . '</td>
				<td class="right">' . $FormatedSupMRPDate . '</td>
				<td class="right">' . locale_number_format($MyRow['supplyquantity'], $MyRow['decimalplaces']) . '</td>
				<td class="right">' . locale_number_format($ExtCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';

		if ($_POST['Consolidation'] == 'None') {
			$HTML .= '<td class="center">' . htmlspecialchars($MyRow['ordertype']) . '</td>
						  <td class="center">' . htmlspecialchars($MyRow['orderno']) . '</td>';
		}
		else {
			$HTML .= '<td class="center">' . htmlspecialchars($MyRow['consolidatedcount']) . '</td>';
		}
		$HTML .= '</tr>';

		// Totals for summary
		$HoldDescription = $MyRow['description'];
		$HoldPart = $MyRow['part'];
		$HoldMBFlag = $MyRow['mbflag'];
		$HoldCost = $MyRow['computedcost'];
		$HoldDecimalPlaces = $MyRow['decimalplaces'];
		$TotalPartCost += $ExtCost;
		$TotalPartQty += $MyRow['supplyquantity'];
		$Total_ExtCost += $ExtCost;
		$Partctr++;
	}

	// Print summary information for last part
	$HTML .= '<tr><td colspan="2"><b>' . $HoldDescription . '</b></td>
			<td class="center">' . __('Unit Cost:') . ' ' . locale_number_format($HoldCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td class="right">' . locale_number_format($TotalPartQty, $HoldDecimalPlaces) . '</td>
			<td class="right">' . locale_number_format($TotalPartCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td class="right">' . __('M/B:') . ' ' . $HoldMBFlag . '</td>
			<td></td></tr>';

	// Grand totals
	$HTML .= '<tr><td colspan="3" class="right"><b>' . __('Number of Work Orders:') . ' ' . $Partctr . '</b></td>
			<td colspan="4" class="right"><b>' . __('Total Extended Cost:') . ' ' . locale_number_format($Total_ExtCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td></tr>';

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

	// Create PDF with DomPDF
	$pdf_file = $_SESSION['DatabaseName'] . '_MRP_Planned_Work_Orders_' . Date('Y-m-d') . '.pdf';
	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_MRPPlannedWorkOrders_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	}
	else {
		$Title = __('MRP Planned Work Orders');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

}
else { /*The option to print PDF was not hit so display form */

	$Title = __('MRP Planned Work Orders Reporting');
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
}


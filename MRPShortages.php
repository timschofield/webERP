<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (!DB_table_exists('mrprequirements')) {
	$Title = __('MRP error');
	include('includes/header.php');
	echo '<br />';
	prnMsg(__('The MRP calculation must be run before you can run this report') . '<br />' . __('To run the MRP calculation click') . ' ' . '<a href="' . $RootPath . '/MRP.php">' . __('here') . '</a>', 'error');
	include('includes/footer.php');
	exit();
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	// Prepare data as in the original script
	$SQL = "CREATE TEMPORARY TABLE demandtotal (
				part char(20),
				demand double,
				KEY `PART` (`part`)) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL, __('Create of demandtotal failed because'));

	$SQL = "INSERT INTO demandtotal
						(part,
						 demand)
			   SELECT part,
					  SUM(quantity) as demand
				FROM mrprequirements
				GROUP BY part";
	$Result = DB_query($SQL);

	$SQL = "CREATE TEMPORARY TABLE supplytotal (
				part char(20),
				supply double,
				KEY `PART` (`part`)) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL, __('Create of supplytotal failed because'));

	$SQL = "INSERT INTO supplytotal
						(part,
						 supply)
			SELECT stockid,
				  0
			FROM stockmaster";
	$Result = DB_query($SQL);

	$SQL = "UPDATE supplytotal
			SET supply = (SELECT SUM(mrpsupplies.supplyquantity)
							FROM mrpsupplies
							WHERE supplytotal.part = mrpsupplies.part
								AND mrpsupplies.supplyquantity > 0)";
	$Result = DB_query($SQL);

	$SQL = "UPDATE supplytotal SET supply = 0 WHERE supply IS NULL";
	$Result = DB_query($SQL);

	if ($_POST['CategoryID'] == 'All') {
		$SQLCategory = ' ';
	} else {
		$SQLCategory = "WHERE stockmaster.categoryid = '" . $_POST['CategoryID'] . "'";
	}

	if ($_POST['ReportType'] == 'Shortage') {
		$SQLHaving = " HAVING demandtotal.demand > supplytotal.supply ";
		$reportTitle = __('MRP Shortages Report');
		$reportSubject = __('MRP Shortages');
	} else {
		$SQLHaving = " HAVING demandtotal.demand <= supplytotal.supply ";
		$reportTitle = __('MRP Excess Report');
		$reportSubject = __('MRP Excess');
	}

	$SortField = $_POST['Sort'] === 'stockid' ? 'stockmaster.stockid' : 'extcost';

	$SQL = "SELECT stockmaster.stockid,
		stockmaster.description,
		stockmaster.mbflag,
		stockmaster.actualcost,
		stockmaster.decimalplaces,
		(stockmaster.actualcost) as computedcost,
		demandtotal.demand,
		supplytotal.supply,
		(demandtotal.demand - supplytotal.supply) *
		(stockmaster.actualcost) as extcost
		   FROM stockmaster
			 LEFT JOIN demandtotal ON stockmaster.stockid = demandtotal.part
			 LEFT JOIN supplytotal ON stockmaster.stockid = supplytotal.part
			 LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid " . $SQLCategory . "WHERE stockcategory.stocktype<>'L'
			 GROUP BY stockmaster.stockid,
			   stockmaster.description,
			   stockmaster.mbflag,
			   stockmaster.actualcost,
			   stockmaster.decimalplaces,
			   stockmaster.actualcost,
			   supplytotal.supply,
			   demandtotal.demand " . $SQLHaving . " ORDER BY $SortField";

	$ErrMsg = __('The MRP shortages and excesses could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('MRP Shortages and Excesses') . ' - ' . __('Problem Report');
		include('includes/header.php');
		prnMsg(__('No MRP shortages - Excess retrieved'), 'warn');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	// Build report as HTML
	$HTML = '<html><head><meta charset="UTF-8"><style>
		body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; }
		h1 { text-align: center; font-size: 18px; }
		table { border-collapse: collapse; width: 100%; margin-top: 10px; }
		th, td { border: 1px solid #aaa; padding: 4px 6px; font-size: 10px; }
		th { background: #f0f0f0; }
		.fill { background-color: #e0ebff; }
		.right { text-align: right; }
		.centre { text-align: center; }
	</style></head><body>';

	$HTML .= '<h1>' . htmlspecialchars($_SESSION['CompanyRecord']['coyname']) . '</h1>';
	$HTML .= '<h2>' . $reportTitle . '</h2>';
	$HTML .= '<div>' . __('Printed') . ': ' . date($_SESSION['DefaultDateFormat']) . '</div>';

	$HTML .= '<table><thead><tr>
		<th>' . __('Part Number') . '</th>
		<th>' . __('Description') . '</th>
		<th>' . __('M/B') . '</th>
		<th>' . __('Unit Cost') . '</th>
		<th>' . __('Supply') . '</th>
		<th>' . __('Demand') . '</th>';

	if ($_POST['ReportType'] == 'Shortage') {
		$HTML .= '<th>' . __('Shortage') . '</th><th>' . __('Ext. Shortage') . '</th>';
	} else {
		$HTML .= '<th>' . __('Excess') . '</th><th>' . __('Ext. Excess') . '</th>';
	}

	$HTML .= '</tr></thead><tbody>';

	$Total_Shortage = 0;
	$Partctr = 0;
	$fillRow = ($_POST['Fill'] == 'yes');
	$rowAlt = false;

	while ($MyRow = DB_fetch_array($Result)) {
		if ($_POST['ReportType'] == 'Shortage') {
			$LineToPrint = ($MyRow['demand'] > $MyRow['supply']);
			$Shortage = ($MyRow['demand'] - $MyRow['supply']) * -1;
			$Extcost = $Shortage * $MyRow['computedcost'];
			$shortageVal = locale_number_format($Shortage, $MyRow['decimalplaces']);
			$extcostVal = locale_number_format($MyRow['extcost'], 2);
		} else {
			$LineToPrint = ($MyRow['demand'] <= $MyRow['supply']);
			$Shortage = ($MyRow['supply'] - $MyRow['demand']);
			$Extcost = $Shortage * $MyRow['computedcost'];
			$shortageVal = locale_number_format($Shortage, $MyRow['decimalplaces']);
			$extcostVal = locale_number_format($MyRow['extcost'], 2);
		}

		if ($LineToPrint) {
			$class = ($fillRow && $rowAlt) ? 'fill' : '';
			$HTML .= '<tr class="' . $class . '">';
			$HTML .= '<td>' . htmlspecialchars($MyRow['stockid']) . '</td>';
			$HTML .= '<td>' . htmlspecialchars($MyRow['description']) . '</td>';
			$HTML .= '<td class="centre">' . htmlspecialchars($MyRow['mbflag']) . '</td>';
			$HTML .= '<td class="right">' . locale_number_format($MyRow['computedcost'], 2) . '</td>';
			$HTML .= '<td class="right">' . locale_number_format($MyRow['supply'], $MyRow['decimalplaces']) . '</td>';
			$HTML .= '<td class="right">' . locale_number_format($MyRow['demand'], $MyRow['decimalplaces']) . '</td>';
			$HTML .= '<td class="right">' . $shortageVal . '</td>';
			$HTML .= '<td class="right">' . $extcostVal . '</td>';
			$HTML .= '</tr>';

			$Total_Shortage += $MyRow['extcost'];
			$Partctr++;
			$rowAlt = !$rowAlt;
		}
	}

	$HTML .= '</tbody></table>';

	$DisplayTotalVal = locale_number_format($Total_Shortage, 2);

	$HTML .= '<br><table style="width: 40%;"><tr>
		<td>' . __('Number of Parts:') . '</td>
		<td class="right">' . $Partctr . '</td>
	</tr><tr>
		<td>' . ($_POST['ReportType'] == 'Shortage' ? __('Total Extended Shortage:') : __('Total Extended Excess:')) . '</td>
		<td class="right">' . $DisplayTotalVal . '</td>
	</tr></table>';

	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_MRPShortages_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('MRP Shortages');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('MRP Shortages') . '" alt="" />' . ' ' . __('MRP Shortages Report') . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else {
	// Display form as before (unchanged)
	$Title = __('MRP Shortages - Excess Reporting');
	$ViewTopic = 'MRP';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Stock') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>';

	echo '<field>
			<label for="CategoryID">' . __('Inventory Category') . ':</label>
			<select name="CategoryID">';
	echo '<option selected="selected" value="All">' . __('All Stock Categories') . '</option>';
	$SQL = "SELECT categoryid,
			categorydescription
			FROM stockcategory";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Sort">' . __('Sort') . ':</label>
			<select name="Sort">
				<option selected="selected" value="extcost">' . __('Extended Shortage Dollars') . '</option>
				<option value="stockid">' . __('Part Number') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="ReportType">' . __('Shortage-Excess Option') . ':</label>
			<select name="ReportType">
				<option selected="selected" value="Shortage">' . __('Report MRP Shortages') . '</option>
				<option value="Excess">' . __('Report MRP Excesses') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="Fill">' . __('Print Option') . ':</label>
			<select name="Fill">
				<option selected="selected" value="yes">' . __('Print With Alternating Highlighted Lines') . '</option>
				<option value="no">' . __('Plain Print') . '</option>
			</select>
		</field>';
	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		</div>
	</form>';

	include('includes/footer.php');
}
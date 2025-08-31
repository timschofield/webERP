<?php

function standard_deviation($Data) {
	$Total = 0;
	$Counter = 0;
	foreach ($Data as $Element) {
		$Total += $Element;
		$Counter++;
	}
	$Average = $Total / $Counter;

	$TotalDifferenceSquared = 0;
	foreach ($Data as $Element) {
		$TotalDifferenceSquared += (($Element - $Average) * ($Element - $Average));
	}
	return sqrt($TotalDifferenceSquared / $Counter);
}

include ('includes/session.php');

use Dompdf\Dompdf;

$ViewTopic = "Inventory";
$BookMark = "PlanningReport";

include ('includes/SQL_CommonFunctions.php');
include ('includes/StockFunctions.php');

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$ReportTitle = __('Preferred Supplier Inventory Plan');
	$CompanyName = $_SESSION['CompanyRecord']['coyname'];
	$PrintDate = Date($_SESSION['DefaultDateFormat']);
	$Location = $_POST['Location'];
	$LocationText = ($Location == 'All') ? __('for all stock locations') : __('for stock at') . ' ' . $Location;
	$NumberMonthsHolding = $_POST['NumberMonthsHolding'];

	$HTML = '';
	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<div class="centre" id="ReportHeader">
				' . $CompanyName . '<br>' . $ReportTitle . ' ' . $LocationText . '<br>' . __('Printed') . ': ' . $PrintDate . '
			</div>';

	$HTML .= '<table>
		<tr>
			<th>' . __('Item') . '</th>
			<th>' . __('Description') . '</th>
			<th>' . __('Avg Qty') . '<br>' . __('4 mths') . '</th>
			<th>' . __('Max Mnth') . '<br>' . __('Quantity') . '</th>
			<th>' . __('Standard') . '<br>' . __('Deviation') . '</th>
			<th>' . __('Lead Time') . '<br>' . __('in months') . '</th>
			<th>' . __('Qty Required') . '<br>' . __('in Supply Chain') . '</th>
			<th>' . __('QOH') . '</th>
			<th>' . __('Cust Ords') . '</th>
			<th>' . __('Splr Ords') . '</th>
			<th>' . __('Sugg Ord') . '</th>
		</tr>';

	$SQL = "SELECT stockmaster.description,
				stockmaster.eoq,
				locstock.stockid,
				purchdata.supplierno,
				suppliers.suppname,
				purchdata.leadtime/30 AS monthsleadtime,
				SUM(locstock.quantity) AS qoh
			FROM locstock
				INNER JOIN locationusers
					ON locationusers.loccode=locstock.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1,
				stockmaster,
				purchdata,
				suppliers
			WHERE locstock.stockid=stockmaster.stockid
			AND purchdata.supplierno=suppliers.supplierid
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
			AND purchdata.stockid=stockmaster.stockid
			AND purchdata.preferred=1";

	if ($Location == 'All') {
		$SQL .= " GROUP BY
					purchdata.supplierno,
					stockmaster.description,
					stockmaster.eoq,
					locstock.stockid
				ORDER BY purchdata.supplierno,
					stockmaster.stockid";
	}
	else {
		$SQL .= " AND locstock.loccode = '" . $Location . "'
				ORDER BY purchdata.supplierno,
				stockmaster.stockid";
	}
	$ErrMsg = __('The inventory quantities could not be retrieved');
	$InventoryResult = DB_query($SQL, $ErrMsg);
	$ListCount = DB_num_rows($InventoryResult);

	$SupplierID = '';
	$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$Period_1 = $CurrentPeriod - 1;
	$Period_2 = $CurrentPeriod - 2;
	$Period_3 = $CurrentPeriod - 3;
	$Period_4 = $CurrentPeriod - 4;

	while ($InventoryPlan = DB_fetch_array($InventoryResult)) {

		if ($SupplierID != $InventoryPlan['supplierno']) {
			if ($SupplierID != '') {
				$HTML .= '<tr><td colspan="11"></td></tr>';
			}
			$HTML .= '<tr><th colspan="11" class="supplier-header">' . $InventoryPlan['supplierno'] . ' - ' . $InventoryPlan['suppname'] . '</th></tr>';
			$SupplierID = $InventoryPlan['supplierno'];
		}

		$SQL = "SELECT SUM(CASE WHEN (prd>='" . $Period_1 . "' OR prd<='" . $Period_4 . "') THEN -qty ELSE 0 END) AS 4mthtotal,
					SUM(CASE WHEN prd='" . $Period_1 . "' THEN -qty ELSE 0 END) AS prd1,
					SUM(CASE WHEN prd='" . $Period_2 . "' THEN -qty ELSE 0 END) AS prd2,
					SUM(CASE WHEN prd='" . $Period_3 . "' THEN -qty ELSE 0 END) AS prd3,
					SUM(CASE WHEN prd='" . $Period_4 . "' THEN -qty ELSE 0 END) AS prd4
					FROM stockmoves
					INNER JOIN locationusers
						ON locationusers.loccode=stockmoves.loccode
							AND locationusers.userid='" . $_SESSION['UserID'] . "'
							AND locationusers.canview=1
					WHERE stockid='" . $InventoryPlan['stockid'] . "'
					AND (stockmoves.type=10 OR stockmoves.type=11)
					AND stockmoves.hidemovt=0";
		if ($Location != 'All') {
			$SQL .= "	AND stockmoves.loccode ='" . $Location . "'";
		}

		$ErrMsg = __('The sales quantities could not be retrieved');
		$SalesResult = DB_query($SQL, $ErrMsg);
		$SalesRow = DB_fetch_array($SalesResult);

		$LocationCode = ($Location == 'All') ? 'ALL' : $Location;

		$TotalDemand = GetDemand($InventoryPlan['stockid'], $LocationCode);
		$QOO = GetQuantityOnOrder($InventoryPlan['stockid'], $LocationCode);

		$AverageOfLast4Months = $SalesRow['4mthtotal'] / 4;
		$MaxMthSales = max($SalesRow['prd1'], $SalesRow['prd2'], $SalesRow['prd3'], $SalesRow['prd4']);
		$Quantities = array(
			$SalesRow['prd1'],
			$SalesRow['prd2'],
			$SalesRow['prd3'],
			$SalesRow['prd4']
		);
		$StandardDeviation = standard_deviation($Quantities);

		$RequiredStockInSupplyChain = $AverageOfLast4Months * ($NumberMonthsHolding + $InventoryPlan['monthsleadtime']);
		$SuggestedTopUpOrder = $RequiredStockInSupplyChain - $InventoryPlan['qoh'] + $TotalDemand - $QOO;

		$HTML .= '<tr class="striped_row">
					<td class="left">' . $InventoryPlan['stockid'] . '</td>
					<td class="left">' . $InventoryPlan['description'] . '</td>
					<td class="number">' . locale_number_format($AverageOfLast4Months, 1) . '</td>
					<td class="number">' . locale_number_format($MaxMthSales, 0) . '</td>
					<td class="number">' . locale_number_format($StandardDeviation, 2) . '</td>
					<td class="number">' . locale_number_format($InventoryPlan['monthsleadtime'], 1) . '</td>
					<td class="number">' . locale_number_format($RequiredStockInSupplyChain, 0) . '</td>
					<td class="number">' . locale_number_format($InventoryPlan['qoh'], 0) . '</td>
					<td class="number">' . locale_number_format($TotalDemand, 0) . '</td>
					<td class="number">' . locale_number_format($QOO, 0) . '</td>
					<td class="number">' . (($SuggestedTopUpOrder <= 0) ? __('Nil') : locale_number_format($SuggestedTopUpOrder, 0)) . '</td>
				</tr>';
	}

	if (isset($_POST['PrintPDF'])) {
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

	if ($ListCount == 0) {
		$HTML .= '<div style="color:red;font-weight:bold;">' . __('There were no items in the range and location specified') . '</div>';
	}

	$HTML .= '</body></html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_Inventory_Planning_PrefSupplier_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	}
	else {
		$Title = __('Inventory Planning Report');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . ' ' . __('Inventory Planning Report') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

}
else { /*The option to print PDF was not hit */

	$Title = __('Preferred Supplier Inventory Planning');
	$ViewTopic = 'Inventory';
	$BookMark = 'PlanningReport';
	include ('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria') , '</legend>';

	echo '<field>
			<label for="Location">' . __('For Inventory in Location') . ':</label>
			<select name="Location">';
	$SQL = "SELECT locations.loccode, locationname FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$LocnResult = DB_query($SQL);

	echo '<option value="All">' . __('All Locations') . '</option>';

	while ($MyRow = DB_fetch_array($LocnResult)) {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="NumberMonthsHolding">' . __('Months Buffer Stock to Hold') . ':</label>
			<select name="NumberMonthsHolding">';

	if (!isset($_POST['NumberMonthsHolding'])) {
		$_POST['NumberMonthsHolding'] = 1;
	}
	if ($_POST['NumberMonthsHolding'] == 0.5) {
		echo '<option selected="selected" value="0.5">' . __('Two Weeks') . '</option>';
	}
	else {
		echo '<option value="0.5">' . __('Two Weeks') . '</option>';
	}
	echo '<option ' . ($_POST['NumberMonthsHolding'] == 1 ? 'selected="selected" ' : '') . 'value="1">' . __('One Month') . '</option>';
	if ($_POST['NumberMonthsHolding'] == 1.5) {
		echo '<option selected="selected" value="1.5">' . __('Six Weeks') . '</option>';
	}
	else {
		echo '<option value="1.5">' . __('Six Weeks') . '</option>';
	}
	if ($_POST['NumberMonthsHolding'] == 2) {
		echo '<option selected="selected" value="2">' . __('Two Months') . '</option>';
	}
	else {
		echo '<option value="2">' . __('Two Months') . '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
			</div>';
	echo '</form>';

	include ('includes/footer.php');
}

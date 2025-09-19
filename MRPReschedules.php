<?php
// Report of purchase orders and work orders that MRP determines should be rescheduled.
require (__DIR__ . '/includes/session.php');
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

if (!DB_table_exists('mrprequirements')) {
	$Title = 'MRP error';
	include ('includes/header.php');
	echo '<br />';
	prnMsg(__('The MRP calculation must be run before you can run this report') . '<br />' . __('To run the MRP calculation click') . ' ' . '<a href="' . $RootPath . '/MRP.php">' . __('here') . '</a>', 'error');
	include ('includes/footer.php');
	exit();
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	// Find mrpsupplies records where the duedate is not the same as the mrpdate
	$SelectType = " ";
	if ($_POST['Selection'] != 'All') {
		$SelectType = " AND ordertype = '" . $_POST['Selection'] . "'";
	}
	$SQL = "SELECT mrpsupplies.*,
				   stockmaster.description,
				   stockmaster.decimalplaces
			FROM mrpsupplies,stockmaster
			WHERE mrpsupplies.part = stockmaster.stockid AND duedate <> mrpdate
				$SelectType
			ORDER BY mrpsupplies.part";

	$ErrMsg = __('The MRP reschedules could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('MRP Reschedules') . ' - ' . __('Problem Report');
		include ('includes/header.php');
		prnMsg(__('No MRP reschedule retrieved'), 'warn');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include ('includes/footer.php');
		exit();
	}

	// Prepare HTML
	$HTML = '
	<html>
	<head>
		<style>
			body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
			table { border-collapse: collapse; width: 100%; }
			th, td { padding: 6px 8px; border: 1px solid #888; }
			th { background: #e0ebff; }
			.alt { background: #f9f9f9; }
			.center { text-align: center; }
			.right { text-align: right; }
			.page-title { font-size: 16pt; margin-bottom: 10px; }
		</style>
	</head>
	<body>
		<div class="page-title">' . $_SESSION['CompanyRecord']['coyname'] . '</div>
		<div>' . __('MRP Reschedule Report') . '</div>
		<div>' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '</div>
		<div>' . __('Selection:') . ' ' . $_POST['Selection'] . '</div>
		<br>
		<table>
			<thead>
				<tr>
					<th>' . __('Part Number') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Order No.') . '</th>
					<th>' . __('Type') . '</th>
					<th>' . __('Quantity') . '</th>
					<th>' . __('Order Date') . '</th>
					<th>' . __('MRP Date') . '</th>
				</tr>
			</thead>
			<tbody>
	';

	$rowClass = '';
	$Fill = ($_POST['Fill'] === 'yes');
	$i = 0;

	while ($MyRow = DB_fetch_array($Result)) {
		$FormatedDueDate = ConvertSQLDate($MyRow['duedate']);
		$FormatedMRPDate = ConvertSQLDate($MyRow['mrpdate']);
		if ($MyRow['mrpdate'] == '2050-12-31') {
			$FormatedMRPDate = 'Cancel';
		}

		if ($Fill) {
			$rowClass = ($i % 2 == 0) ? "" : "alt";
		}
		else {
			$rowClass = "";
		}
		$i++;

		$HTML .= '
			<tr class="' . $rowClass . '">
				<td>' . htmlspecialchars($MyRow['part']) . '</td>
				<td>' . htmlspecialchars($MyRow['description']) . '</td>
				<td class="right">' . htmlspecialchars($MyRow['orderno']) . '</td>
				<td class="right">' . htmlspecialchars($MyRow['ordertype']) . '</td>
				<td class="right">' . locale_number_format($MyRow['supplyquantity'], $MyRow['decimalplaces']) . '</td>
				<td class="right">' . $FormatedDueDate . '</td>
				<td class="right">' . $FormatedMRPDate . '</td>
			</tr>
		';
	}


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
		$dompdf->stream($_SESSION['DatabaseName'] . '_MRPReschedules_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	}
	else {
		$Title = __('MRP Reschedules');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('MRP Reschedules') . '" alt="" />' . ' ' . __('MRP Reschedules') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}
}
else { // The option to print PDF was not hit so display form
	$Title = __('MRP Reschedule Reporting');
	$ViewTopic = 'MRP';
	$BookMark = '';
	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Stock') . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
		<legend>' . __('Report Criteria') . '</legend>
		<field>
			<label for="Fill">' . __('Print Option') . ':</label>
			<select name="Fill">
				<option selected="selected" value="yes">' . __('Print With Alternating Highlighted Lines') . '</option>
				<option value="no">' . __('Plain Print') . '</option>
			</select>
		</field>
		<field>
			<label for="Selection">' . __('Selection') . ':</label>
			<select name="Selection">
				<option selected="selected" value="All">' . __('All') . '</option>
				<option value="WO">' . __('Work Orders Only') . '</option>
				<option value="PO">' . __('Purchase Orders Only') . '</option>
			</select>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		</div>
		</form>';

	include ('includes/footer.php');
}


<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

if (isset($_GET['WO'])) {
	$WO = filter_number_format($_GET['WO']);
} elseif (isset($_POST['WO'])) {
	$WO = filter_number_format($_POST['WO']);
} else {
	$WO = '';
}

if (isset($_GET['StockId'])) {
	$StockId = $_GET['StockId'];
} elseif (isset($_POST['StockId'])) {
	$StockId = $_POST['StockId'];
}

if (isset($_GET['Location'])) {
	$Location = $_GET['Location'];
} elseif (isset($_POST['Location'])) {
	$Location = $_POST['Location'];
}

if (isset($WO) && isset($StockId) && $WO != '') {

	$SQL = "SELECT woitems.qtyreqd,
					woitems.qtyrecd,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
			FROM woitems, stockmaster
			WHERE stockmaster.stockid = woitems.stockid
				AND woitems.wo = '" . $WO . "'
				AND woitems.stockid = '" . $StockId . "' ";

	$ErrMsg = __('The SQL to find the details of the item to produce failed');
	$ResultItems = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($ResultItems) != 0) {
	$HTML = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"><head><style>
		body { font-family: Arial, sans-serif; font-size: 12px; }
		.header { margin-bottom: 20px; }
		.company-info { font-size: 10px; margin-bottom: 10px; }
		.supplier-info { font-size: 12px; margin-bottom: 10px; }
		table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
		th, td { border: 1px solid #000; padding: 4px; }
		th { background: #eee; }
		.totals { text-align: right; font-weight: bold; }
		</style><link href="css/reports.css" rel="stylesheet" type="text/css" /></head><body>';
		$ReportDate = date($_SESSION['DefaultDateFormat']);
		$PageTitle = __('WO Production Slip');
		$Subject = __('WO Production Slip');

		while ($MyItem = DB_fetch_array($ResultItems)) {
			$QtyPending = $MyItem['qtyreqd'] - $MyItem['qtyrecd'];
			$HTML .= PrintHeaderHTML($_SESSION['CompanyRecord']['coyname'], $ReportDate, $WO, $StockId, $MyItem['description'], $QtyPending, $MyItem['units'], $MyItem['decimalplaces']);

			$SQLBOM = "SELECT bom.parent,
						bom.component,
						bom.quantity AS bomqty,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.description,
						stockmaster.shrinkfactor,
						locstock.quantity AS qoh
					FROM bom, stockmaster, locstock
					WHERE bom.component = stockmaster.stockid
						AND bom.component = locstock.stockid
						AND locstock.loccode = '" . $Location . "'
						AND bom.parent = '" . $StockId . "'
						AND bom.effectiveafter <= CURRENT_DATE
						AND bom.effectiveto > CURRENT_DATE";

			$ErrMsg = __('The bill of material could not be retrieved because');
			$BOMResult = DB_query($SQLBOM, $ErrMsg);

			$HTML .= '<table width="100%" border="1" cellspacing="0" cellpadding="4">
				<thead>
					<tr>
						<th>' . __('Component Code') . '</th>
						<th>' . __('Qty BOM') . '</th>
						<th>' . __('Units') . '</th>
						<th>' . __('Qty Needed') . '</th>
						<th>' . __('Units') . '</th>
						<th>' . __('Shrinkage') . '</th>
						<th>' . __('Units') . '</th>
					</tr>
				</thead>
				<tbody>';

			while ($MyComponent = DB_fetch_array($BOMResult)) {
				$ComponentNeeded = $MyComponent['bomqty'] * $QtyPending;
				$PrevisionShrinkage = $ComponentNeeded * ($MyComponent['shrinkfactor'] / 100);
				$HTML .= '<tr>
					<td>' . htmlspecialchars($MyComponent['component']) . '</td>
					<td align="right">' . locale_number_format($MyComponent['bomqty'], 'Variable') . '</td>
					<td>' . htmlspecialchars($MyComponent['units']) . '</td>
					<td align="right">' . locale_number_format($ComponentNeeded, $MyComponent['decimalplaces']) . '</td>
					<td>' . htmlspecialchars($MyComponent['units']) . '</td>
					<td align="right">' . locale_number_format($PrevisionShrinkage, $MyComponent['decimalplaces']) . '</td>
					<td>' . htmlspecialchars($MyComponent['units']) . '</td>
				</tr>';
			}

			$HTML .= '</tbody></table>';

			// Add production notes and signature section
			$HTML .= PrintFooterSlipHTML(__('Incidences / Production Notes'), __('Components Ready By'), __('Item Produced By'), __('Quality Control By'));
		}
		$HTML .= '</body>
				</html>';

		// Output to PDF using Dompdf
		$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
		$DomPDF->loadHtml($HTML);
		$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');
		$DomPDF->render();
		$filename = 'WO-' . $WO . '-' . $StockId . '-' . date('Y-m-d') . '.pdf';
		$DomPDF->stream($filename, ['Attachment' => false]);
		exit();
	} else {
		$Title = __('WO Item production Slip');
		include('includes/header.php');
		prnMsg(__('There were no items with ready to produce'), 'info');
		prnMsg($SQL);
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}
}

function PrintHeaderHTML($CompanyName, $ReportDate, $WO, $StockId, $Description, $Qty, $UOM, $DecimalPlaces) {
	$imgTag = '';
	$imgPath = $_SESSION['part_pics_dir'] . '/' . $StockId . '.jpg';
	if (file_exists($imgPath)) {
		// DomPDF requires file:// prefix for local images
		$imgTag = '<img src=' . $imgPath . ' style="width:200px;height:auto;margin-bottom:16px;" />';
	}

	return '
		<h2 style="margin-bottom:0;">' . htmlspecialchars($CompanyName) . '</h2>
		<p style="margin-top:0;">
			' . __('Printed') . ': ' . htmlspecialchars($ReportDate) . '
			<br />
			' . __('Work Order Item Production Slip') . '
			<br />
			' . __('WO') . ': ' . htmlspecialchars($WO) . '
			<br />
			' . __('Item Code') . ': ' . htmlspecialchars($StockId) . ' - ' . htmlspecialchars($Description) . '
			<br />
			' . __('Quantity') . ': ' . locale_number_format($Qty, $DecimalPlaces) . ' ' . htmlspecialchars($UOM) . '
			<br />
			' . $imgTag . '
		</p>
	';
}

function PrintFooterSlipHTML($ProductionNotes, $Column1, $Column2, $Column3) {
	return '
		<h3>' . htmlspecialchars($ProductionNotes) . ':</h3>
		<div style="height:80px;border:1px solid #000;margin-bottom:20px;"></div>
		<table width="100%" border="0" cellspacing="0" cellpadding="8" style="margin-top:30px;">
			<tr>
				<td valign="top" width="33%">
					<strong>' . htmlspecialchars($Column1) . ':</strong><br />
					<br />' . __('Name') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Date') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Hour') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Signature') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span>
				</td>
				<td valign="top" width="33%">
					<strong>' . htmlspecialchars($Column2) . ':</strong><br />
					<br />' . __('Name') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Date') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Hour') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Signature') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span>
				</td>
				<td valign="top" width="33%">
					<strong>' . htmlspecialchars($Column3) . ':</strong><br />
					<br />' . __('Name') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Date') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Hour') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span><br />
					<br />' . __('Signature') . ': <span style="border-bottom:1px solid #000;">' . str_repeat('&nbsp;', 45) . '</span>
				</td>
			</tr>
		</table>
	';
}
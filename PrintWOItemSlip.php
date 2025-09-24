<?php
require 'vendor/autoload.php'; // Make sure DomPDF is installed via Composer
use Dompdf\Dompdf;
use Dompdf\Options;

require(__DIR__ . '/includes/session.php');

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

if (isset($WO) and isset($StockId) and $WO != '') {

	$SQL = "SELECT woitems.qtyreqd,
					woitems.qtyrecd,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
			FROM woitems, stockmaster
			WHERE stockmaster.stockid = woitems.stockid
				AND woitems.wo = '" . $WO . "'
				AND woitems.stockid = '" . $StockId . "' ";

	$ErrMsg = _('The SQL to find the details of the item to produce failed');
	$ResultItems = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($ResultItems) != 0) {
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isRemoteEnabled', true);

		$dompdf = new Dompdf($options);

		$HTML = '';
		while ($myItem = DB_fetch_array($ResultItems)) {
			$FontSize = '12px';
			$QtyPending = $myItem['qtyreqd'] - $myItem['qtyrecd'];

			$HTML .= PrintHeaderHTML($_SESSION['CompanyRecord']['coyname'], $WO, $StockId, $myItem['description'], $QtyPending, $myItem['units'], $myItem['decimalplaces'], date('Y-m-d'));

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
                        AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                        AND bom.effectiveto > '" . date('Y-m-d') . "'";

			$ErrMsg = _('The bill of material could not be retrieved because');
			$BOMResult = DB_query($SQLBOM, $ErrMsg);

			$HTML .= "<table style='width:100%; border-collapse:collapse; margin-bottom:20px;'><thead>
			<tr>
				<th style='border:1px solid #000;padding:4px;'>Component Code</th>
				<th style='border:1px solid #000;padding:4px;'>Qty BOM</th>
				<th style='border:1px solid #000;padding:4px;'>Unit</th>
				<th style='border:1px solid #000;padding:4px;'>Qty Needed</th>
				<th style='border:1px solid #000;padding:4px;'>Unit</th>
				<th style='border:1px solid #000;padding:4px;'>Shrinkage</th>
				<th style='border:1px solid #000;padding:4px;'>Unit</th>
			</tr>
			</thead><tbody>";

			while ($myComponent = DB_fetch_array($BOMResult)) {
				$ComponentNeeded = $myComponent['bomqty'] * $QtyPending;
				$PrevisionShrinkage = $ComponentNeeded * ($myComponent['shrinkfactor'] / 100);

				$HTML .= "<tr>
					<td style='border:1px solid #000;padding:4px;'>{$myComponent['component']}</td>
					<td style='border:1px solid #000;padding:4px;text-align:right;'>" . locale_number_format($myComponent['bomqty'], 'Variable') . "</td>
					<td style='border:1px solid #000;padding:4px;'>{$myComponent['units']}</td>
					<td style='border:1px solid #000;padding:4px;text-align:right;'>" . locale_number_format($ComponentNeeded, $myComponent['decimalplaces']) . "</td>
					<td style='border:1px solid #000;padding:4px;'>{$myComponent['units']}</td>
					<td style='border:1px solid #000;padding:4px;text-align:right;'>" . locale_number_format($PrevisionShrinkage, $myComponent['decimalplaces']) . "</td>
					<td style='border:1px solid #000;padding:4px;'>{$myComponent['units']}</td>
				</tr>";
			}
			$HTML .= "</tbody></table>";
		}

		$HTML .= "<div style='margin-bottom:30px;'><strong>Incidences / Production Notes:</strong></div>";

		$HTML .= PrintFooterSlipHTML(_('Components Ready By'), _('Item Produced By'), _('Quality Control By'));

		$dompdf->loadHtml($HTML);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();

		$filename = 'WO-' . $WO . '-' . $StockId . '-' . date('Y-m-d') . '.pdf';
		$dompdf->stream($filename, ['Attachment' => 1]);
		exit;
	} else {
		$Title = _('WO Item production Slip');
		include ('includes/header.php');
		prnMsg(_('There were no items with ready to produce'), 'info');
		prnMsg($SQL);
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include ('includes/footer.php');
		exit;
	}
}

function PrintHeaderHTML($CompanyName, $WO, $StockId, $Description, $Qty, $UOM, $DecimalPlaces, $ReportDate) {
	$imgTag = '';
	$imgPath = $_SESSION['part_pics_dir'] . '/' . $StockId . '.jpg';
	if (file_exists($imgPath)) {
		// DomPDF requires file:// prefix for local images
		$imgTag = "<img src='file://$imgPath' style='width:200px;height:auto;margin-bottom:16px;' />";
	}
	return "
	<div style='margin-bottom:24px;'>
		<div style='font-size:18px;font-weight:bold;'>$CompanyName</div>
		<div style='float:right;'>$ReportDate</div>
		<div style='clear:both;'></div>
		<div style='font-size:16px;font-weight:bold;margin-top:10px;'>Work Order Item Production Slip</div>
		<div style='font-size:14px;margin-top:10px;'><strong>WO:</strong> $WO</div>
		<div style='font-size:14px;margin-top:8px;'><strong>Item Code:</strong> $StockId &rarr; $Description</div>
		<div style='font-size:14px;margin-top:8px;'><strong>Quantity:</strong> " . locale_number_format($Qty, $DecimalPlaces) . " $UOM</div>
		$imgTag
	</div>
	";
}

function PrintFooterSlipHTML($Column1, $Column2, $Column3) {
	$footerTable = "
	<table style='width:100%;margin-top:30px;'>
		<tr>
			<td style='vertical-align:top;'>
				<strong>$Column1:</strong><br>
				Name: __________________<br>
				Date: __________________<br>
				Hour: __________________<br>
				Signature: __________________
			</td>
			<td style='vertical-align:top;'>
				<strong>$Column2:</strong><br>
				Name: __________________<br>
				Date: __________________<br>
				Hour: __________________<br>
				Signature: __________________
			</td>
			<td style='vertical-align:top;'>
				<strong>$Column3:</strong><br>
				Name: __________________<br>
				Date: __________________<br>
				Hour: __________________<br>
				Signature: __________________
			</td>
		</tr>
	</table>
	";
	return $footerTable;
}

?>
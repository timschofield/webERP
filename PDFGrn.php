<?php

require(__DIR__ . '/includes/session.php');
require_once __DIR__ . '/vendor/autoload.php'; // DomPDF autoload

use Dompdf\Dompdf;

// Get GRNNo
$GRNNo = isset($_GET['GRNNo']) ? $_GET['GRNNo'] : '';

// Load form design XML
$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/GoodsReceived.xml');

// Prepare data
if ($GRNNo == 'Preview') {
	$MyRow = [
		'itemcode' => str_pad('', 15,'x'),
		'deliverydate' => '1000-01-01',
		'itemdescription' => str_pad('', 30,'x'),
		'qtyrecd' => 99999999.99,
		'decimalplaces' => 2,
		'conversionfactor' => 1,
		'supplierid' => str_pad('', 10,'x'),
		'suppliersunit' => str_pad('', 10,'x'),
		'units' => str_pad('', 10,'x')
	];
	$SuppRow = [
		'suppname' => str_pad('', 30,'x'),
		'address1' => str_pad('', 30,'x'),
		'address2' => str_pad('', 30,'x'),
		'address3' => str_pad('', 30,'x'),
		'address4' => str_pad('', 20,'x'),
		'address5' => str_pad('', 10,'x'),
		'address6' => str_pad('', 10,'x')
	];
	$NoOfGRNs = 1;
	$GRNRows = [$MyRow];
} else {
	$SQL = "SELECT grns.itemcode,
				grns.grnno,
				grns.deliverydate,
				grns.itemdescription,
				grns.qtyrecd,
				grns.supplierid,
				grns.supplierref,
				purchorderdetails.suppliersunit,
				purchorderdetails.conversionfactor,
				stockmaster.units,
				stockmaster.decimalplaces
			FROM grns INNER JOIN purchorderdetails
			ON grns.podetailitem=purchorderdetails.podetailitem
			INNER JOIN purchorders on purchorders.orderno = purchorderdetails.orderno
			INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			LEFT JOIN stockmaster
			ON grns.itemcode=stockmaster.stockid
			WHERE grnbatch='". $GRNNo ."'";
	$GRNResult = DB_query($SQL);
	$NoOfGRNs = DB_num_rows($GRNResult);

	$GRNRows = [];
	while ($row = DB_fetch_array($GRNResult)) {
		$GRNRows[] = $row;
	}

	if ($NoOfGRNs > 0) {
		$SQL = "SELECT suppliers.suppname,
						suppliers.address1,
						suppliers.address2 ,
						suppliers.address3,
						suppliers.address4,
						suppliers.address5,
						suppliers.address6
				FROM grns INNER JOIN suppliers
				ON grns.supplierid=suppliers.supplierid
				WHERE grnbatch='". $GRNNo ."'";
		$SuppResult = DB_query($SQL, __('Could not get the supplier of the selected GRN'));
		$SuppRow = DB_fetch_array($SuppResult);
	}
}

if ($NoOfGRNs > 0) {
	// Start HTML
	$HTML = '
	<html>
	<head>
	<style>
		body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
		table { border-collapse: collapse; width: 100%; }
		th, td { border: 1px solid #000; padding: 3px; text-align: left; }
		.right { text-align: right; }
	</style>
	</head>
	<body>
	<h2>' . $_SESSION['CompanyRecord']['coyname'] . '</h2>
	<h2>' . __('Goods Received Note') . '</h2>
	<table>
		<tr>
			<td><strong>' . __('Supplier') . ':</strong> ' . $SuppRow['suppname'] . '</td>
		</tr>
		<tr>
			<td><strong>' . __('Address') . ':</strong> ' .
				$SuppRow['address1'] . ', ' .
				$SuppRow['address2'] . ', ' .
				$SuppRow['address3'] . ', ' .
				$SuppRow['address4'] . ', ' .
				$SuppRow['address5'] . ', ' .
				$SuppRow['address6']
			. '</td>
		</tr>
	</table>
	<br>
	<table>
		<thead>
			<tr>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Delivery Date') . '</th>
				<th>' . __('Supplier Qty') . '</th>
				<th>' . __('Supplier Unit') . '</th>
				<th>' . __('Our Qty') . '</th>
				<th>' . __('Our Unit') . '</th>
			</tr>
		</thead>
		<tbody>
	';

	foreach ($GRNRows as $MyRow) {
		$DecimalPlaces = is_numeric($MyRow['decimalplaces']) ? $MyRow['decimalplaces'] : 2;
		$SuppliersQuantity = (is_numeric($MyRow['conversionfactor']) && $MyRow['conversionfactor'] != 0)
			? locale_number_format($MyRow['qtyrecd'] / $MyRow['conversionfactor'], $DecimalPlaces)
			: locale_number_format($MyRow['qtyrecd'], $DecimalPlaces);
		$OurUnitsQuantity = locale_number_format($MyRow['qtyrecd'], $DecimalPlaces);
		$DeliveryDate = ConvertSQLDate($MyRow['deliverydate']);

		$HTML .= '
			<tr>
				<td>' . htmlspecialchars($MyRow['itemcode']) . '</td>
				<td>' . htmlspecialchars($MyRow['itemdescription']) . '</td>
				<td>' . htmlspecialchars($DeliveryDate) . '</td>
				<td class="right">' . $SuppliersQuantity . '</td>
				<td>' . htmlspecialchars($MyRow['suppliersunit']) . '</td>
				<td class="right">' . $OurUnitsQuantity . '</td>
				<td>' . htmlspecialchars($MyRow['units']) . '</td>
			</tr>
		';

		// Controlled items
		$SQL = "SELECT stockmaster.controlled
				FROM stockmaster WHERE stockid ='" . $MyRow['itemcode'] . "'";
		$CheckControlledResult = DB_query($SQL, '<br />' . __('Could not determine if the item was controlled or not because') . ' ');
		$ControlledRow = DB_fetch_row($CheckControlledResult);

		if ($ControlledRow[0] == 1) {
			$SQL = "SELECT stockserialmoves.serialno,
							stockserialmoves.moveqty
					FROM stockmoves INNER JOIN stockserialmoves
					ON stockmoves.stkmoveno= stockserialmoves.stockmoveno
					WHERE stockmoves.stockid='" . $MyRow['itemcode'] . "'
					AND stockmoves.type =25
					AND stockmoves.transno='" . $GRNNo . "'";
			$GetStockMoveResult = DB_query($SQL, __('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
			while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)) {
				$HTML .= '
					<tr>
						<td colspan="2">' . __('Lot/Serial:') . ' ' . htmlspecialchars($SerialStockMoves['serialno']) . '</td>
						<td class="right">' . htmlspecialchars($SerialStockMoves['moveqty']) . '</td>
						<td colspan="4"></td>
					</tr>
				';
			}
		}
	}

	$HTML .= '
		</tbody>
	</table>
	<br>
	<table>
		<tr>
			<td><strong>' . __('Date of Receipt: ') . '</strong>' . htmlspecialchars($DeliveryDate) . '</td>
		</tr>
		<tr>
			<td><strong>' . __('Signed for') . ':</strong> ______________________</td>
		</tr>
	</table>
	</body>
	</html>
	';

	// Generate PDF
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$FileName = $_SESSION['DatabaseName'] . '_GRN_' . $GRNNo . '_' . date('Y-m-d') . '.pdf';
	$dompdf->stream($FileName, array("Attachment" => false));

	exit;
} else {
	$Title = __('GRN Error');
	include('includes/header.php');
	prnMsg(__('There were no GRNs to print'),'warn');
	echo '<br /><a href="'.$RootPath.'/index.php">' .  __('Back to the menu') . '</a>';
	include('includes/footer.php');
}
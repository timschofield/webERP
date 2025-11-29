<?php
require (__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

$GRNNo = $_GET['GRNNo'] ?? '';

$FormDesign = simplexml_load_file($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/QALabel.xml');

$PaperSize = $FormDesign->PaperSize;
$LineHeight = $FormDesign->LineHeight;

$HTML = '<html>
			<head>
				<meta charset="UTF-8">
				<title>' . __('QA Label') . '</title>
				<style>
					body { margin: 0; padding: 0; }
					div { position: relative; }
				</style>
			</head>
			<body>';

$SQL = "SELECT grns.itemcode,
				grns.grnno,
				grns.deliverydate,
				grns.itemdescription,
				grns.supplierid,
				purchorderdetails.orderno
			FROM grns INNER JOIN purchorderdetails
			ON grns.podetailitem = purchorderdetails.podetailitem
			LEFT JOIN stockmaster
			ON grns.itemcode = stockmaster.stockid
			WHERE grnbatch='" . $GRNNo . "'";

$GRNResult = DB_query($SQL);
$NoOfGRNs = DB_num_rows($GRNResult);
if ($NoOfGRNs > 0) {
	$SQL = "SELECT suppliers.suppname
				FROM grns INNER JOIN suppliers
				ON grns.supplierid = suppliers.supplierid
				WHERE grnbatch='" . $GRNNo . "'";
	$SuppResult = DB_query($SQL, __('Could not get the supplier of the selected GRN'));
	$SuppRow = DB_fetch_array($SuppResult);
}

if ($NoOfGRNs > 0) {
	for ($i = 1;$i <= $NoOfGRNs;$i++) {
		$MyRow = DB_fetch_array($GRNResult);
		$DeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
		$SQL = "SELECT stockmaster.controlled
				FROM stockmaster WHERE stockid ='" . $MyRow['itemcode'] . "'";
		$CheckControlledResult = DB_query($SQL, '<br />' . __('Could not determine if the item was controlled or not because') . ' ');
		$ControlledRow = DB_fetch_row($CheckControlledResult);

		if ($ControlledRow[0] == 1) { /* controlled item labels */
			$SQL = "SELECT stockserialmoves.serialno
					FROM stockmoves INNER JOIN stockserialmoves
					ON stockmoves.stkmoveno = stockserialmoves.stockmoveno
					WHERE stockmoves.stockid='" . $MyRow['itemcode'] . "'
					AND stockmoves.type = 25
					AND stockmoves.transno='" . $GRNNo . "'";
			$GetStockMoveResult = DB_query($SQL, __('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
			while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)) {
				$HTML .= '
					<table style="width:100%; font-family:Arial, sans-serif; page-break-after:always; border-collapse:collapse;">
						<tr><img src="' . $_SESSION['LogoFile'] . '" style="width:200px; height:' . $FormDesign->logo->height . 'px;" /></td></tr>
						<tr><td><strong>Item:</strong></td><td>' . htmlspecialchars($MyRow['itemcode']) . '</td></tr>
						<tr><td><strong>Description:</strong></td><td>' . htmlspecialchars($MyRow['itemdescription']) . '</td></tr>
						<tr><td><strong>Supplier:</strong></td><td>' . htmlspecialchars($SuppRow['suppname']) . '</td></tr>
						<tr><td><strong>Supplier Lot:</strong></td><td>' . htmlspecialchars($SerialStockMoves['serialno']) . '</td></tr>
						<tr><td><strong>Lot:</strong></td><td>' . htmlspecialchars($SerialStockMoves['serialno']) . '</td></tr>
						<tr><td><strong>Receipt Date:</strong></td><td>' . htmlspecialchars($MyRow['deliverydate']) . '</td></tr>
						<tr><td><strong>P/O:</strong></td><td>' . htmlspecialchars($MyRow['orderno']) . '</td></tr>
					</table>
				';
			}
		}
		else { /* non-controlled item labels */
			$HTML .= '
				<table style="width:100%; font-family:Arial, sans-serif; page-break-after:always; border-collapse:collapse;">
					<tr><img src="' . $_SESSION['LogoFile'] . '" style="width:200px; height:' . $FormDesign->logo->height . 'px;" /></td></tr>
					<tr><td><strong>Item:</strong></td><td>' . htmlspecialchars($MyRow['itemcode']) . '</td></tr>
					<tr><td><strong>Description:</strong></td><td>' . htmlspecialchars($MyRow['itemdescription']) . '</td></tr>
					<tr><td><strong>Supplier:</strong></td><td>' . htmlspecialchars($SuppRow['suppname']) . '</td></tr>
					<tr><td><strong>Receipt Date:</strong></td><td>' . htmlspecialchars(ConvertSQLDate($MyRow['deliverydate'])) . '</td></tr>
					<tr><td><strong>P/O:</strong></td><td>' . htmlspecialchars($MyRow['orderno']) . '</td></tr>
				</table>';
		}
	}

	$HTML .= '</body>
		</html>';

	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->setPaper($_SESSION['PageSize'], 'portrait'); // You may use $PaperSize if dynamically set
	$DomPDF->loadHtml($HTML);
	$DomPDF->render();

	$FileName = $_SESSION['DatabaseName'] . '_QALabel_' . $GRNNo . '_' . date('Y-m-d') . '.pdf';

	// Output the generated PDF to Browser
	$DomPDF->stream($FileName, array("Attachment" => false));

} else {
	$Title = __('GRN Error');
	include ('includes/header.php');
	prnMsg(__('There were no GRNs to print'), 'warn');
	echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include ('includes/footer.php');
}


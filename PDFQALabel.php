<?php
require (__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

if (isset($_GET['GRNNo'])) {
	$GRNNo = $_GET['GRNNo'];
} else {
	$GRNNo = '';
}

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

		if ($ControlledRow[0] == 1) {
			$SQL = "SELECT stockserialmoves.serialno
					FROM stockmoves INNER JOIN stockserialmoves
					ON stockmoves.stkmoveno = stockserialmoves.stockmoveno
					WHERE stockmoves.stockid='" . $MyRow['itemcode'] . "'
					AND stockmoves.type = 25
					AND stockmoves.transno='" . $GRNNo . "'";
			$GetStockMoveResult = DB_query($SQL, __('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
			while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)) {
				$HTML .= '
					<div style="page-break-after:always; font-family:Arial, sans-serif;">
						<img src="' . $_SESSION['LogoFile'] . '" style="position:absolute; left:' . $FormDesign->logo->x . 'px; top:' . $FormDesign->logo->y . 'px; width:' . $FormDesign->logo->width . 'px; height:' . $FormDesign->logo->height . 'px;" />
						<div style="position:absolute; left:' . $FormDesign->ItemNbr->x . 'px; top:' . $FormDesign->ItemNbr->y . 'px; font-size:' . $FormDesign->ItemNbr->FontSize . 'pt;">
							Item: ' . htmlspecialchars($MyRow['itemcode']) . '
						</div>
						<div style="position:absolute; left:' . $FormDesign->ItemDesc->x . 'px; top:' . $FormDesign->ItemDesc->y . 'px; font-size:' . $FormDesign->ItemDesc->FontSize . 'pt;">
							Description: ' . htmlspecialchars($MyRow['itemdescription']) . '
						</div>
						<div style="position:absolute; left:' . $FormDesign->SupplierName->x . 'px; top:' . $FormDesign->SupplierName->y . 'px; font-size:' . $FormDesign->SupplierName->FontSize . 'pt;">
							Supplier: ' . htmlspecialchars($SuppRow['suppname']) . '
						</div>
						<div style="position:absolute; left:' . $FormDesign->SupplierLot->x . 'px; top:' . $FormDesign->SupplierLot->y . 'px; font-size:' . $FormDesign->SupplierLot->FontSize . 'pt;">
							Supplier Lot: ' . htmlspecialchars($SerialStockMoves['serialno']) . '
						</div>
						<div style="position:absolute; left:' . $FormDesign->Lot->x . 'px; top:' . $FormDesign->Lot->y . 'px; font-size:' . $FormDesign->Lot->FontSize . 'pt;">
							Lot: ' . htmlspecialchars($SerialStockMoves['serialno']) . '
						</div>
						<div style="position:absolute; left:' . $FormDesign->ReceiptDate->x . 'px; top:' . $FormDesign->ReceiptDate->y . 'px; font-size:' . $FormDesign->ReceiptDate->FontSize . 'pt;">
							Receipt Date: ' . htmlspecialchars($MyRow['deliverydate']) . '
						</div>
						<div style="position:absolute; left:' . $FormDesign->OrderNumber->x . 'px; top:' . $FormDesign->OrderNumber->y . 'px; font-size:' . $FormDesign->OrderNumber->FontSize . 'pt;">
							P/O: ' . htmlspecialchars($MyRow['orderno']) . '
						</div>
					</div>
				';
			}
		}
		else {
			$HTML .= '
				<div style="page-break-after:always; font-family:Arial, sans-serif;">
					<img src="' . $_SESSION['LogoFile'] . '" style="position:absolute; left:' . $FormDesign->logo->x . 'px; top:' . $FormDesign->logo->y . 'px; width:' . $FormDesign->logo->width . 'px; height:' . $FormDesign->logo->height . 'px;" />
					<div style="position:absolute; left:' . $FormDesign->ItemNbr->x . 'px; top:' . $FormDesign->ItemNbr->y . 'px; font-size:' . $FormDesign->ItemNbr->FontSize . 'pt;">
						Item: ' . htmlspecialchars($MyRow['itemcode']) . '
					</div>
					<div style="position:absolute; left:' . $FormDesign->ItemDesc->x . 'px; top:' . $FormDesign->ItemDesc->y . 'px; font-size:' . $FormDesign->ItemDesc->FontSize . 'pt;">
						Description: ' . htmlspecialchars($MyRow['itemdescription']) . '
					</div>
					<div style="position:absolute; left:' . $FormDesign->SupplierName->x . 'px; top:' . $FormDesign->SupplierName->y . 'px; font-size:' . $FormDesign->SupplierName->FontSize . 'pt;">
						Supplier: ' . htmlspecialchars($SuppRow['suppname']) . '
					</div>
					<div style="position:absolute; left:' . $FormDesign->ReceiptDate->x . 'px; top:' . $FormDesign->ReceiptDate->y . 'px; font-size:' . $FormDesign->ReceiptDate->FontSize . 'pt;">
						Receipt Date: ' . htmlspecialchars(ConvertSQLDate($MyRow['deliverydate'])) . '
					</div>
					<div style="position:absolute; left:' . $FormDesign->OrderNumber->x . 'px; top:' . $FormDesign->OrderNumber->y . 'px; font-size:' . $FormDesign->OrderNumber->FontSize . 'pt;">
						P/O: ' . htmlspecialchars($MyRow['orderno']) . '
					</div>
				</div>';
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


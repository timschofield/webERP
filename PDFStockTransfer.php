<?php
/* This script is superseded by the PDFStockLocTransfer.php which produces a multiple item stock transfer listing - this was for the old individual stock transfers where there is just single items being transferred */

require (__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['Process'])) {
	// Prepare HTML for DomPDF
	$HTML = '<html><head><meta charset="UTF-8"><style>
	body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
	table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
	th, td { border: 1px solid #000; padding: 5px; text-align: left; }
	.header { font-weight: bold; margin-bottom: 15px; font-size: 18px; }
	.sign { margin-top: 50px; }
</style></head><body>';
	$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';

	$HTML .= '<img class="logo" src="' . $_SESSION['LogoFile'] . '" /><br />';
	$HTML .= '<div class="header">' . __('Stock Transfer Form') . '</div>';
	$HTML .= '<table><thead>
	<tr>
		<th>' . __('Stock ID') . '</th>
		<th>' . __('Description') . '</th>
		<th>' . __('From Location') . '</th>
		<th>' . __('To Location/Reference') . '</th>
		<th>' . __('Quantity') . '</th>
	</tr>
</thead><tbody>';

	/*Print out the category totals */
	$SQL = "SELECT stockmoves.stockid,
			description,
			transno,
			stockmoves.loccode,
			locationname,
			trandate,
			qty,
			reference
		FROM stockmoves
		INNER JOIN stockmaster
		ON stockmoves.stockid=stockmaster.stockid
		INNER JOIN locations
		ON stockmoves.loccode=locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
		WHERE transno='" . $_POST['TransferNo'] . "'
		AND qty < 0
		AND type=16";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		$Title = __('Print Stock Transfer - Error');
		include ('includes/header.php');
		prnMsg(__('There was no transfer found with number') . ': ' . $_POST['TransferNo'], 'error');
		echo '<a href="' . $RootPath . '/PDFStockTransfer.php">' . __('Try Again') . '</a>';
		include ('includes/footer.php');
		exit();
	}

	$Date = '';
	$From = '';
	$To = '';
	//get the first stock movement which will be the quantity taken from the initiating location
	while ($MyRow = DB_fetch_array($Result)) {
		$StockID = $MyRow['stockid'];
		$From = $MyRow['locationname'];
		$Date = $MyRow['trandate'];
		$To = $MyRow['reference'];
		$Quantity = - $MyRow['qty'];
		$Description = $MyRow['description'];

		$HTML .= '<tr>';
		$HTML .= '<td>' . htmlspecialchars($StockID) . '</td>';
		$HTML .= '<td>' . htmlspecialchars($Description) . '</td>';
		$HTML .= '<td>' . htmlspecialchars($From) . '</td>';
		$HTML .= '<td>' . htmlspecialchars($To) . '</td>';
		$HTML .= '<td class="number">' . htmlspecialchars($Quantity) . '</td>';
		$HTML .= '</tr>';

		$SQL = "SELECT stockmaster.controlled
			FROM stockmaster WHERE stockid ='" . $StockID . "'";
		$CheckControlledResult = DB_query($SQL, '<br />' . __('Could not determine if the item was controlled or not because') . ' ');
		$ControlledRow = DB_fetch_row($CheckControlledResult);

		if ($ControlledRow[0] == 1) { /*Then its a controlled item */
			$SQL = "SELECT stockserialmoves.serialno,
				stockserialmoves.moveqty
				FROM stockmoves INNER JOIN stockserialmoves
				ON stockmoves.stkmoveno= stockserialmoves.stockmoveno
				WHERE stockmoves.stockid='" . $StockID . "'
				AND stockmoves.type =16
				AND qty > 0
				AND stockmoves.transno='" . $_GET['TransferNo'] . "'";
			$GetStockMoveResult = DB_query($SQL, __('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
			while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)) {
				$HTML .= '<tr style="background:#f9f9f9;"><td colspan="2">' . __('Lot/Serial:') . ' ' . htmlspecialchars($SerialStockMoves['serialno']) . '</td>';
				$HTML .= '<td colspan="2"></td>';
				$HTML .= '<td>' . htmlspecialchars($SerialStockMoves['moveqty']) . '</td></tr>';
			}
		}
	}
	$HTML .= '</tbody></table>';

	$HTML .= '<div class="sign">' . __('Date of transfer: ') . htmlspecialchars(ConvertSQLDate($Date)) . '</div>';
	$HTML .= '<div class="sign">' . __('Signed for') . ' ' . htmlspecialchars($From) . '______________________</div>';
	$HTML .= '<div class="sign">' . __('Signed for') . ' ' . htmlspecialchars($To) . '______________________</div>';

	$HTML .= '</body></html>';

	// Setup DomPDF
	$FileName = $_SESSION['DatabaseName'] . '_StockTransfer_' . date('Y-m-d H-m-s') . '.pdf';
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream($FileName, array("Attachment" => false));

}
else {
	if (isset($_POST['TransferNo'])) {
		if (is_numeric($_POST['TransferNo'])) {
			$_GET['TransferNo'] = $_POST['TransferNo'];
		}
		else {
			prnMsg(__('The entered transfer reference is expected to be numeric'), 'error');
			unset($_POST['TransferNo']);
		}
	}
	if (!isset($_GET['TransferNo'])) { //still not set from a post then
		//open a form for entering a transfer number
		$Title = __('Print Stock Transfer');
		$ViewTopic = 'Inventory';
		$BookMark = '';
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . __('Print Transfer Note') . '" alt="" />' . ' ' . $Title . '</p>';
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" id="form" target="_blank">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<fieldset>';
		echo '<fieldset>
			<field>
				<label for="TransferNo">' . __('Print Stock Transfer Note') . ' : ' . '</label>
				<input type="text" class="number"  name="TransferNo" maxlength="10" size="11" />
			</field>
			</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="Process" value="' . __('Print Transfer Note') . '" />
			</div>
			</form>';

		echo '<form method="post" action="' . $RootPath . '/PDFShipLabel.php?Type=Sales" target="_blank">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="Type" value="Transfer" />';
		echo '<fieldset>
				<field>
					<label for="ORD">' . __('Transfer docket to reprint Shipping Labels') . '</label>
					<input type="text" class="number" size="10" name="ORD" />
				</field>
			</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="Print" value="' . __('Print Shipping Labels') . '" />
			</div>';
		echo '</fieldset>
			</form>';

		include ('includes/footer.php');
		exit();
	}
}

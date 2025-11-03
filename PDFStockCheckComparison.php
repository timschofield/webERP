<?php
/* Creates a PDF comparing the quantities entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated. */

require (__DIR__ . '/includes/session.php');

// Include DomPDF
use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

if (isset($_POST['PrintPDF']) and isset($_POST['ReportOrClose'])) {

	include ('includes/SQL_CommonFunctions.php');

	$HTML = '';
	$HTML .= '<html>
				<head>
					<meta charset="UTF-8"><title>' . __('Check Comparison Report') . '</title>
					<style>
						body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
						table { border-collapse: collapse; width: 100%; }
						th, td { border: 1px solid #333; padding: 4px; }
						th { background-color: #f2f2f2; }
						.section-header { background: #eee; font-size: 16px; font-weight: bold; }
						.category-header { background: #f8f8f8; font-size: 13px; font-weight: bold; }
						.location-header { background: #e0e0ff; font-size: 14px; font-weight: bold; }
					</style>
				</head>
				<body>';
	$HTML .= '<h2>' . __('Inventory Check Comparison') . ' ' . date($_SESSION['DefaultDateFormat']) . '</h2>';

	// Inventory Comparison file stuff and adjustments
	if ($_POST['ReportOrClose'] == 'ReportAndClose') {

		$SQL = "SELECT stockcheckfreeze.stockid,
						stockcheckfreeze.loccode,
						qoh,
						actualcost AS standardcost
				FROM stockmaster INNER JOIN stockcheckfreeze
				ON stockcheckfreeze.stockid=stockmaster.stockid
				ORDER BY stockcheckfreeze.loccode,
						stockcheckfreeze.stockid";

		$ErrMsg = __('The inventory check file could not be retrieved');
		$StockChecks = DB_query($SQL, $ErrMsg);

		$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']));
		$SQLAdjustmentDate = FormatDateForSQL(date($_SESSION['DefaultDateFormat']));
		$AdjustmentNumber = GetNextTransNo(17);

		while ($MyRow = DB_fetch_array($StockChecks)) {

			$SQL = "SELECT SUM(stockcounts.qtycounted) AS totcounted,
					COUNT(stockcounts.stockid) AS noofcounts
					FROM stockcounts
					WHERE stockcounts.stockid='" . $MyRow['stockid'] . "'
					AND stockcounts.loccode='" . $MyRow['loccode'] . "'";

			$ErrMsg = __('The inventory counts file could not be retrieved');
			$StockCounts = DB_query($SQL, $ErrMsg);

			$StkCountResult = DB_query($SQL);
			$StkCountRow = DB_fetch_array($StkCountResult);

			$StockQtyDifference = $StkCountRow['totcounted'] - $MyRow['qoh'];

			if ($_POST['ZeroCounts'] == 'Leave' and $StkCountRow['noofcounts'] == 0) {
				$StockQtyDifference = 0;
			}

			if ($StockQtyDifference != 0) { // only adjust stock if there is an adjustment to make!!
				DB_Txn_Begin();

				// Need to get the current location quantity will need it later for the stock movement
				$SQL = "SELECT locstock.quantity
						FROM locstock
					WHERE locstock.stockid='" . $MyRow['stockid'] . "'
					AND loccode= '" . $MyRow['loccode'] . "'";

				$Result = DB_query($SQL);
				if (DB_num_rows($Result) == 1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				}
				else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}

				$SQL = "INSERT INTO stockmoves (stockid,
								type,
								transno,
								loccode,
								trandate,
								userid,
								prd,
								reference,
								qty,
								newqoh,
								standardcost)
						VALUES ('" . $MyRow['stockid'] . "',
							17,
							'" . $AdjustmentNumber . "',
							'" . $MyRow['loccode'] . "',
							'" . $SQLAdjustmentDate . "',
							'" . $_SESSION['UserID'] . "',
							'" . $PeriodNo . "',
							'" . __('Inventory Check') . "',
							'" . $StockQtyDifference . "',
							'" . ($QtyOnHandPrior + $StockQtyDifference) . "',
							'" . $MyRow['standardcost'] . "'
						)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock movement record cannot be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				$SQL = "UPDATE locstock
						SET quantity = quantity + '" . $StockQtyDifference . "'
						WHERE stockid='" . $MyRow['stockid'] . "'
						AND loccode='" . $MyRow['loccode'] . "'";
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The location stock record could not be updated because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 and $MyRow['standardcost'] > 0) {

					$StockGLCodes = GetStockGLCode($MyRow['stockid']);
					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction entries could not be added because');

					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									amount,
									narrative)
							VALUES (17,
								'" . $AdjustmentNumber . "',
								'" . $SQLAdjustmentDate . "',
								'" . $PeriodNo . "',
								'" . $StockGLCodes['adjglact'] . "',
								'" . ($MyRow['standardcost'] * -($StockQtyDifference)) . "',
								'" . mb_substr($MyRow['stockid'] . " x " . $StockQtyDifference . " @ " . $MyRow['standardcost'] . " - " . __('Inventory Check'), 0, 200) . "')";
					$Result = DB_query($SQL, $ErrMsg, '', true);

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction entries could not be added because');

					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									amount,
									narrative)
							VALUES (17,
								'" . $AdjustmentNumber . "',
								'" . $SQLAdjustmentDate . "',
								'" . $PeriodNo . "',
								'" . $StockGLCodes['stockact'] . "',
								'" . $MyRow['standardcost'] * $StockQtyDifference . "',
								'" . mb_substr($MyRow['stockid'] . " x " . $StockQtyDifference . " @ " . $MyRow['standardcost'] . " - " . __('Inventory Check'), 0, 200) . "')";
					$Result = DB_query($SQL, $ErrMsg, '', true);

				} //END INSERT GL TRANS
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Unable to COMMIT transaction while adjusting stock in StockCheckAdjustmet report');
				DB_Txn_Commit();

			} // end if $StockQtyDifference !=0

		} // end loop round all the checked parts

	} // end user wanted to close the inventory check file and do the adjustments
	// now do the report
	$ErrMsg = __('The Inventory Comparison data could not be retrieved because');
	$SQL = "SELECT stockcheckfreeze.stockid,
					description,
					stockmaster.categoryid,
					stockcategory.categorydescription,
					stockcheckfreeze.loccode,
					locations.locationname,
					stockcheckfreeze.qoh,
					stockmaster.decimalplaces,
					bin
			FROM stockcheckfreeze INNER JOIN stockmaster
				ON stockcheckfreeze.stockid=stockmaster.stockid
			INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN locations
				ON stockcheckfreeze.loccode=locations.loccode
			INNER JOIN locstock
				ON stockcheckfreeze.loccode=locstock.loccode
				AND stockcheckfreeze.stockid=locstock.stockid
			ORDER BY stockcheckfreeze.loccode,
				stockmaster.categoryid,
				stockcheckfreeze.stockid";

	$CheckedItems = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($CheckedItems) == 0) {
		$HTML .= '<p><b>' . __('There is no inventory check data to report on') . '</b></p>';
		$HTML .= '<p>' . __('To start an inventory check first run the') . ' <a href="' . $RootPath . '/StockCheck.php">' . __('inventory check sheets') . '</a> - ' . __('and select the option to create new Inventory Check') . '</p>';
		$HTML .= '</body></html>';
		
		$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
		$DomPDF->loadHtml($HTML);
		$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');
		$DomPDF->render();
		$DomPDF->stream($_SESSION['DatabaseName'] . '_StockComparison_' . date('Y-m-d') . '.pdf', ["Attachment" => false]);
		exit();
	}

	DB_data_seek($CheckedItems, 0);

	$Location = '';
	$Category = '';

	$HTML .= '<table>';
	$HTML .= '<tr>
				<th>' . __('Location') . '</th>
				<th>' . __('Category') . '</th>
				<th>' . __('Stock ID') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Bin') . '</th>
				<th>' . __('System QOH') . '</th>
				<th>' . __('Counted Qty') . '</th>
				<th>' . __('Reference') . '</th>
				<th>' . __('Difference') . '</th>
			</tr>';

	while ($CheckItemRow = DB_fetch_array($CheckedItems)) {

		$SQL = "SELECT qtycounted,
						reference
				FROM stockcounts
				WHERE loccode ='" . $CheckItemRow['loccode'] . "'
				AND stockid = '" . $CheckItemRow['stockid'] . "'";

		$ErrMsg = __('The inventory counts could not be retrieved');
		$Counts = DB_query($SQL, $ErrMsg);

		$RowSpan = max(1, DB_num_rows($Counts));
		$FirstRow = true;

		if ($CheckItemRow['qoh'] != 0 or DB_num_rows($Counts) > 0) {
			if (DB_num_rows($Counts) == 0 and $CheckItemRow['qoh'] != 0) {
				$HTML .= '<tr>
							<td>' . htmlspecialchars($CheckItemRow['loccode']) . ' - ' . htmlspecialchars($CheckItemRow['locationname']) . '</td>
							<td>' . htmlspecialchars($CheckItemRow['categoryid']) . ' - ' . htmlspecialchars($CheckItemRow['categorydescription']) . '</td>
							<td>' . htmlspecialchars($CheckItemRow['stockid']) . '</td>
							<td>' . htmlspecialchars($CheckItemRow['description']) . '</td>
							<td>' . htmlspecialchars($CheckItemRow['bin']) . '</td>
							<td class="number">' . locale_number_format($CheckItemRow['qoh'], $CheckItemRow['decimalplaces']) . '</td>
							<td colspan="2">' . __('No counts entered') . '</td>
							<td class="number">' . ($_POST['ZeroCounts'] == 'Adjust' ? locale_number_format(-($CheckItemRow['qoh']), $CheckItemRow['decimalplaces']) : '') . '</td>
						</tr>';
			}
			elseif (DB_num_rows($Counts) > 0) {
				$TotalCount = 0;
				while ($CountRow = DB_fetch_array($Counts)) {
					$HTML .= "\t\t<tr>";
					if ($FirstRow) {
						$HTML .= '<td rowspan=' . $RowSpan . '>' . htmlspecialchars($CheckItemRow['loccode']) . ' - ' . htmlspecialchars($CheckItemRow['locationname']) . '</td>
								<td rowspan=' . $RowSpan . '>' . htmlspecialchars($CheckItemRow['categoryid']) . ' - ' . htmlspecialchars($CheckItemRow['categorydescription']) . '</td>
								<td rowspan=' . $RowSpan . '>' . htmlspecialchars($CheckItemRow['stockid']) . '</td>
								<td rowspan=' . $RowSpan . '>' . htmlspecialchars($CheckItemRow['description']) . '</td>
								<td rowspan=' . $RowSpan . '>' . htmlspecialchars($CheckItemRow['bin']) . '</td>
								<td rowspan=' . $RowSpan . ' style=\'text-align:right\'>' . locale_number_format($CheckItemRow['qoh'], $CheckItemRow['decimalplaces']) . '</td>';
						$FirstRow = false;
					}
					$HTML .= '<td style="text-align:right">' . locale_number_format($CountRow['qtycounted'], $CheckItemRow['decimalplaces']) . '</td>
							<td>' . htmlspecialchars($CountRow['reference']) . '</td>';
					$TotalCount += $CountRow['qtycounted'];
					$HTML .= "<td></td></tr>";
				}
				$HTML .= '<tr>
							<td colspan="6" class="number"><b>' . __('Total for') . ': ' . htmlspecialchars($CheckItemRow['stockid']) . '</b></td>
							<td class="number"><b>' . locale_number_format($TotalCount, $CheckItemRow['decimalplaces']) . '</b></td>
							<td></td>
							<td class="number"><b>' . locale_number_format($TotalCount - $CheckItemRow['qoh'], $CheckItemRow['decimalplaces']) . '</b></td>
						</tr>';
			}
		}
	} /*end STOCK comparison while loop */
	$HTML .= '</table>';
	$HTML .= '</body></html>';

	// Output PDF
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);
	$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');
	$DomPDF->render();
	$DomPDF->stream($_SESSION['DatabaseName'] . '_StockComparison_' . date('Y-m-d') . '.pdf', ["Attachment" => false]);

	if ($_POST['ReportOrClose'] == 'ReportAndClose') {
		// need to print the report first before this but don't risk re-adjusting all the stock!!
		$SQL = "TRUNCATE TABLE stockcheckfreeze";
		$Result = DB_query($SQL);

		$SQL = "TRUNCATE TABLE stockcounts";
		$Result = DB_query($SQL);
	}

} else { /*The option to print PDF was not hit */

	$Title = __('Inventory Comparison Report');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include ('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Stock Check Options'), '</legend>';
	echo '<field>
			<label for="ReportOrClose">' . __('Choose Option') . ':</label>
			<select name="ReportOrClose">';

	if ($_POST['ReportOrClose'] == 'ReportAndClose') {
		echo '<option selected="selected" value="ReportAndClose">' . __('Report and Close the Inventory Comparison Processing Adjustments As Necessary') . '</option>';
		echo '<option value="ReportOnly">' . __('Report The Inventory Comparison Differences Only - No Adjustments') . '</option>';
	}
	else {
		echo '<option selected="selected" value="ReportOnly">' . __('Report The Inventory Comparison Differences Only - No Adjustments') . '</option>';
		echo '<option value="ReportAndClose">' . __('Report and Close the Inventory Comparison Processing Adjustments As Necessary') . '</option>';
	}

	echo '</select>
		</field>';

	echo '<field>
			<label for="ZeroCounts">' . __('Action for Zero Counts') . ':</label>
			<select name="ZeroCounts">';

	if ($_POST['ZeroCounts'] == 'Adjust') {
		echo '<option selected="selected" value="Adjust">' . __('Adjust System stock to Nil') . '</option>';
		echo '<option value="Leave">' . __('Do not Adjust System stock to Nil') . '</option>';
	}
	else {
		echo '<option value="Adjust">' . __('Adjust System stock to Nil') . '</option>';
		echo '<option selected="selected" value="Leave">' . __('Do not Adjust System stock to Nil') . '</option>';
	}

	echo '</select>
		</field>';
	echo '</fieldset>
		<div class="centre"><input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" /></div>';
	echo '</form>';

	include ('includes/footer.php');

} /*end of else not PrintPDF */


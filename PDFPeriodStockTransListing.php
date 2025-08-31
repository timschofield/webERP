<?php
include ('includes/session.php');

use Dompdf\Dompdf;

include ('includes/SQL_CommonFunctions.php');

if (isset($_POST['FromDate'])) {
	$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);
}
if (isset($_POST['ToDate'])) {
	$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);
}

$InputError = 0;
if (isset($_POST['FromDate']) and !Is_Date($_POST['FromDate'])) {
	$Msg = __('The date must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
	unset($_POST['FromDate']);
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	if ($_POST['StockLocation'] == 'All') {
		$SQL = "SELECT stockmoves.type,
				stockmoves.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmoves.transno,
				stockmoves.trandate,
				stockmoves.qty,
				stockmoves.reference,
				stockmoves.narrative,
				locations.locationname
			FROM stockmoves
			LEFT JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
			LEFT JOIN locations
			ON stockmoves.loccode=locations.loccode
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE type='" . $_POST['TransType'] . "'
			AND date_format(trandate, '%Y-%m-%d')>='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND date_format(trandate, '%Y-%m-%d')<='" . FormatDateForSQL($_POST['ToDate']) . "'";
	}
	else {
		$SQL = "SELECT stockmoves.type,
				stockmoves.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmoves.transno,
				stockmoves.trandate,
				stockmoves.qty,
				stockmoves.reference,
				stockmoves.narrative,
				locations.locationname
			FROM stockmoves
			LEFT JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
			LEFT JOIN locations
			ON stockmoves.loccode=locations.loccode
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE type='" . $_POST['TransType'] . "'
			AND date_format(trandate, '%Y-%m-%d')>='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND date_format(trandate, '%Y-%m-%d')<='" . FormatDateForSQL($_POST['ToDate']) . "'
			AND stockmoves.loccode='" . $_POST['StockLocation'] . "'";
	}
	$Result = DB_query($SQL, '', '', false, false);

	if (DB_error_no() != 0) {
		$Title = __('Transaction Listing');
		include ('includes/header.php');
		prnMsg(__('An error occurred getting the transactions'), 'error');
		include ('includes/footer.php');
		exit();
	}
	elseif (DB_num_rows($Result) == 0) {
		$Title = __('Transaction Listing');
		include ('includes/header.php');
		echo '<br />';
		prnMsg(__('There were no transactions found in the database between the dates') . ' ' . $_POST['FromDate'] . ' ' . __('and') . ' ' . $_POST['ToDate'] . '<br />' . __('Please try again selecting a different date range'), 'info');
		include ('includes/footer.php');
		exit();
	}

	// Build HTML for DomPDF
	$ReportTitle = __('Stock Transaction Listing');
	$ReportSubTitle = __('Stock transaction listing from') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' . $_POST['ToDate'];

	switch ($_POST['TransType']) {
		case 10:
			$TransType = __('Customer Invoices');
		break;
		case 11:
			$TransType = __('Customer Credit Notes');
		break;
		case 16:
			$TransType = __('Location Transfers');
		break;
		case 17:
			$TransType = __('Stock Adjustments');
		break;
		case 25:
			$TransType = __('Purchase Order Deliveries');
		break;
		case 26:
			$TransType = __('Work Order Receipts');
		break;
		case 28:
			$TransType = __('Work Order Issues');
		break;
		default:
			$TransType = __('Other');
		break;
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}
	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . $ReportTitle . '<br />
					' . $ReportSubTitle . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>';

	$HTML .= '<table>
				<tr class="total_row">
					<td colspan="6"><p><strong>' . __('Transaction Type') . ':</strong> ' . $TransType . '</p></td>
				</tr>
				<tr>
					<th>' . __('Description') . '</th>
					<th>' . __('Transaction No') . '</th>
					<th>' . __('Date') . '</th>
					<th class="right">' . __('Quantity') . '</th>
					<th>' . __('Location') . '</th>
					<th>' . __('Reference') . '</th>
				</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		$HTML .= '<tr class="striped_row">
					<td>' . htmlspecialchars($MyRow['description']) . '</td>
					<td>' . htmlspecialchars($MyRow['transno']) . '</td>
					<td>' . htmlspecialchars(ConvertSQLDate($MyRow['trandate'])) . '</td>
					<td class="number">' . locale_number_format($MyRow['qty'], $MyRow['decimalplaces']) . '</td>
					<td>' . htmlspecialchars($MyRow['locationname']) . '</td>
					<td>' . htmlspecialchars($MyRow['reference']) . '</td>
				</tr>';
	}

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
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
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_PeriodStockTransListing_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	}
	else {
		$Title = __('Inventory Planning Report');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . ' ' . __('Inventory Planning Report') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}
}
else {
	$Title = __('Stock Transaction Listing');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include ('includes/header.php');

	echo '<div class="centre">
			<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . __('Stock Transaction Listing') . '</p>
		</div>';

	if ($InputError == 1) {
		prnMsg($Msg, 'error');
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" target="_blank">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
			<legend>', __('Report Criteria'), '</legend>
		<field>
			<label for="FromDate">' . __('Enter the date from which the transactions are to be listed') . ':</label>
			<input required="required" autofocus="autofocus" name="FromDate" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
		</field>
		<field>
			<label for="ToDate">' . __('Enter the date to which the transactions are to be listed') . ':</label>
			<input required="required" name="ToDate" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
		</field>
		<field>
			<label for="TransType">' . __('Transaction type') . '</label>
			<select name="TransType">
				<option value="10">' . __('Sales Invoice') . '</option>
				<option value="11">' . __('Sales Credit Note') . '</option>
				<option value="16">' . __('Location Transfer') . '</option>
				<option value="17">' . __('Stock Adjustment') . '</option>
				<option value="25">' . __('Purchase Order Delivery') . '</option>
				<option value="26">' . __('Work Order Receipt') . '</option>
				<option value="28">' . __('Work Order Issue') . '</option>
			</select>
		</field>';

	$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$ResultStkLocs = DB_query($SQL);

	echo '<field>
			<label for="StockLocation">' . __('For Stock Location') . ':</label>
			<select required="required" name="StockLocation">
				<option value="All">' . __('All') . '</option>';

	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_POST['StockLocation']) and $_POST['StockLocation'] != 'All') {
			if ($MyRow['loccode'] == $_POST['StockLocation']) {
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
			else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		}
		elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			$_POST['StockLocation'] = $MyRow['loccode'];
		}
		else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
			</div>';
	echo '</form>';

	include ('includes/footer.php');
}


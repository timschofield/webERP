<?php

include ('includes/session.php');

use Dompdf\Dompdf;

include ('includes/SQL_CommonFunctions.php');

if (isset($_POST['Date'])) {
	$_POST['Date'] = ConvertSQLDate($_POST['Date']);
}

$InputError = 0;
if (isset($_POST['Date']) && !Is_Date($_POST['Date'])) {
	$Msg = __('The date must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
	unset($_POST['Date']);
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	$SQL = "SELECT type,
			supplierno,
			suppreference,
			trandate,
			ovamount,
			ovgst,
			transtext,
			currcode,
			decimalplaces AS currdecimalplaces,
			suppname
		FROM supptrans INNER JOIN suppliers
		ON supptrans.supplierno = suppliers.supplierid
		INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE type='" . $_POST['TransType'] . "'
		AND trandate='" . FormatDateForSQL($_POST['Date']) . "'";

	$ErrMsg = __('An error occurred getting the payments');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('Payment Listing');
		include ('includes/header.php');
		echo '<br />';
		prnMsg(__('There were no transactions found in the database for the date') . ' ' . $_POST['Date'] . '. ' . __('Please try again selecting a different date'), 'info');
		include ('includes/footer.php');
		exit();
	}

	switch ($_POST['TransType']) {
		case 20:
			$TransactionType = __('Invoices');
			break;
		case 21:
			$TransactionType = __('Credits');
			break;
		case 22:
			$TransactionType = __('Payments');
			break;
		default:
			$TransactionType = __('None');
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
				<body>';


	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<img class="logo" src=' . $_SESSION['LogoFile'] . ' /><br />';
	}

	$HTML .= '<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Transaction type') . ': ' . $TransactionType . '<br />
					' . __('Date of Transactions') .': ' . $_POST['Date'] . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Supplier Name') . '</th>
							<th>' . __('Reference') . '</th>
							<th>' . __('Date') . '</th>
							<th>' . __('Amount') . '</th>
							<th>' . __('GST') . '</th>
							<th>' . __('Total') . '</th>
						</tr>
					</thead>
					<tbody>';

	$TotalCheques = 0;
	$CurrDecimalPlaces = 2; // fallback
	while ($MyRow = DB_fetch_array($Result)) {
		$CurrDecimalPlaces = $MyRow['currdecimalplaces'];
		$suppname = htmlspecialchars($MyRow['suppname']);
		$suppreference = htmlspecialchars($MyRow['suppreference']);
		$trandate = htmlspecialchars(ConvertSQLDate($MyRow['trandate']));
		$ovamount = locale_number_format($MyRow['ovamount'], $CurrDecimalPlaces);
		$ovgst = locale_number_format($MyRow['ovgst'], $CurrDecimalPlaces);
		$total = locale_number_format($MyRow['ovamount'] + $MyRow['ovgst'], $CurrDecimalPlaces);

		$HTML .= '<tr class="striped_row">
		<td>' . $suppname . '</td>
		<td>' . $suppreference . '</td>
		<td>' . $trandate . '</td>
		<td class="number">' . $ovamount . '</td>
		<td class="number">' . $ovgst . '</td>
		<td class="number">' . $total . '</td>
	</tr>';

		$TotalCheques -= $MyRow['ovamount'];
	}

	$HTML .= '<tr class="total_row">
				<td colspan="5" style="text-align: right;">' . __('Total Transactions') . '</td>
				<td class="number">' . locale_number_format(-$TotalCheques, $CurrDecimalPlaces) . '</td>
			</tr>';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_SuppTransListing_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	}
	else {
		$Title = __('Inventory Planning Report');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Supplier Transaction Listing') . '" alt="" />' . ' ' . __('Supplier Transaction Listing') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

}
else { /*The option to print PDF was not hit */
	$Title = __('Supplier Transaction Listing');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include ('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . __('Supplier Transaction Listing') . '</p>';

	if ($InputError == 1) {
		prnMsg($Msg, 'error');
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="Date">' . __('Enter the date for which the transactions are to be listed') . ':</label>
				<input name="Date" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
			</field>';

	echo '<field>
			<label for="TransType">' . __('Transaction type') . '</label>
			<select name="TransType">
				<option value="20">' . __('Invoices') . '</option>
				<option value="21">' . __('Credit Notes') . '</option>
				<option value="22">' . __('Payments') . '</option>
			</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		</div>';

	echo '</form>';

	include ('includes/footer.php');

}

<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['PrintPDF'])
	or isset($_POST['View'])
	and isset($_POST['FromCriteria'])
	and mb_strlen($_POST['FromCriteria']) >= 1
	and isset($_POST['ToCriteria'])
	and mb_strlen($_POST['ToCriteria']) >= 1) {

	$Title = __('Supplier Balance Listing');
	$Subject = __('Supplier Balances');

	// Start building HTML
	$HTML = '';
	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					currencies.currency,
					currencies.decimalplaces AS currdecimalplaces,
					SUM((supptrans.ovamount + supptrans.ovgst - supptrans.alloc)/supptrans.rate) AS balance,
					SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS fxbalance,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
						(supptrans.ovamount + supptrans.ovgst)/supptrans.rate ELSE 0 END) AS afterdatetrans,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "'
						AND (supptrans.type=22 OR supptrans.type=21) THEN
						supptrans.diffonexch ELSE 0 END) AS afterdatediffonexch,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
						supptrans.ovamount + supptrans.ovgst ELSE 0 END) AS fxafterdatetrans
			FROM suppliers INNER JOIN currencies
			ON suppliers.currcode = currencies.currabrev
			INNER JOIN supptrans
			ON suppliers.supplierid = supptrans.supplierno
			WHERE suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
			AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
			GROUP BY suppliers.supplierid,
				suppliers.suppname,
				currencies.currency,
				currencies.decimalplaces";

	$ErrMsg = __('The Supplier details could not be retrieved');
	$SupplierResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SupplierResult) == 0) {
		$Title = __('Supplier Balances - Problem Report');
		include('includes/header.php');
		prnMsg(__('There are no supplier balances to list'), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	// Table header
	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Supplier Balance Listing') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div><table>
		<thead>
			<tr>
				<th>' . __('Supplier Code & Name') . '</th>
				<th>' . __('Balance') . '</th>
				<th>' . __('FX Balance') . '</th>
				<th>' . __('Currency') . '</th>
			</tr>
		</thead>
		<tbody>';

	$TotBal = 0;

	while ($SupplierBalances = DB_fetch_array($SupplierResult)) {

		$Balance = $SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'] + $SupplierBalances['afterdatediffonexch'];
		$FXBalance = $SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'];

		if (ABS($Balance) > 0.009 || ABS($FXBalance) > 0.009) {
			$DisplayBalance = locale_number_format($Balance, $_SESSION['CompanyRecord']['decimalplaces']);
			$DisplayFXBalance = locale_number_format($FXBalance, $SupplierBalances['currdecimalplaces']);

			$TotBal += $Balance;

			$HTML .= '<tr class="striped_row">
				<td class="left">' . $SupplierBalances['supplierid'] . ' - ' . $SupplierBalances['suppname'] . '</td>
				<td class="number">' . $DisplayBalance . '</td>
				<td class="number">' . $DisplayFXBalance . '</td>
				<td class="left">' . $SupplierBalances['currency'] . '</td>
			</tr>';
		}
	} // end while

	$DisplayTotBalance = locale_number_format($TotBal, $_SESSION['CompanyRecord']['decimalplaces']);

	// Total row
	$HTML .= '<tr class="total_row">
		<td class="left"><strong>' . __('Total') . '</strong></td>
		<td class="number"><strong>' . $DisplayTotBalance . '</strong></td>
		<td></td>
		<td></td>
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_Supplier_Balances_At_Prior_Month_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	}
	else {
		$Title = __('Supplier Balances At A Period End');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . __('Suppliers') . '" alt="" />' . ' ' . __('Supplier Balances At A Period End') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

} else { // Not printing PDF, show input form

	$Title = __('Supplier Balances At A Period End');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' .
		__('Supplier Allocations') . '" alt="" />' . ' ' . $Title . '</p>';
	if (!isset($_POST['FromCriteria'])) {
		$_POST['FromCriteria'] = '1';
	}
	if (!isset($_POST['ToCriteria'])) {
		$_POST['ToCriteria'] = 'zzzzzz';
	}

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>';
	echo '<field>
			<label for="FromCriteria">' . __('From Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="FromCriteria" value="' . $_POST['FromCriteria'] . '" />
		</field>
		<field>
			<label for="ToCriteria">' . __('To Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="ToCriteria" value="' . $_POST['ToCriteria'] . '" />
		</field>
		<field>
			<label for="PeriodEnd">' . __('Balances As At') . ':</label>
			<select name="PeriodEnd">';

	$SQL = "SELECT periodno,
					lastdate_in_period
			FROM periods
			ORDER BY periodno DESC";

	$ErrMsg = __('Could not retrieve period data because');
	$Periods = DB_query($SQL, $ErrMsg);

	while ($MyRow = DB_fetch_array($Periods)) {
		echo '<option value="' . $MyRow['lastdate_in_period'] . '" selected="selected" >' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period'], 'M', -1) . '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
			</div>';
	echo '</form>';
	include('includes/footer.php');
}

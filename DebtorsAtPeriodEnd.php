<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	/*Get the date of the last day in the period selected */
	$PeriodEndDate = ConvertSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodEnd']));

	  /*Now figure out the aged analysis for the customer range under review */

	$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
		  			currencies.currency,
		  			currencies.decimalplaces,
					SUM((debtortrans.balance)/debtortrans.rate) AS balance,
					SUM(debtortrans.balance) AS fxbalance,
					SUM(CASE WHEN debtortrans.prd > '" . $_POST['PeriodEnd'] . "' THEN
					(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)/debtortrans.rate ELSE 0 END) AS afterdatetrans,
					SUM(CASE WHEN debtortrans.prd > '" . $_POST['PeriodEnd'] . "'
						AND (debtortrans.type=11 OR debtortrans.type=12) THEN
						debtortrans.diffonexch ELSE 0 END) AS afterdatediffonexch,
					SUM(CASE WHEN debtortrans.prd > '" . $_POST['PeriodEnd'] . "' THEN
					debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount ELSE 0 END
					) AS fxafterdatetrans
			FROM debtorsmaster INNER JOIN currencies
			ON debtorsmaster.currcode = currencies.currabrev
			INNER JOIN debtortrans
			ON debtorsmaster.debtorno = debtortrans.debtorno
			WHERE debtorsmaster.debtorno >= '" . $_POST['FromCriteria'] . "'
			AND debtorsmaster.debtorno <= '" . $_POST['ToCriteria'] . "'
			GROUP BY debtorsmaster.debtorno,
				debtorsmaster.name,
				currencies.currency,
				currencies.decimalplaces";

	$ErrMsg = ('The customer details could not be retrieved');
	$CustomerResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($CustomerResult) == 0) {
		$Title = __('Customer Balances') . ' - ' . __('Problem Report');
		include('includes/header.php');
		prnMsg(__('The customer details listing has no clients to report on'),'warn');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Customer Balances For Customers between') . ' ' . $_POST['FromCriteria'] .  ' ' . __('and') . ' ' . $_POST['ToCriteria'] . ' ' . __('as at') . ' ' . $PeriodEndDate . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Customer') . '</th>
							<th>' . __('Balance') . '</th>
							<th>' . __('FX') . '</th>
							<th>' . __('Currency') . '</th>
						</tr>
					</thead>
					<tbody>';

	$TotBal=0;

	while ($DebtorBalances = DB_fetch_array($CustomerResult)){

		$Balance = $DebtorBalances['balance'] - $DebtorBalances['afterdatetrans'] + $DebtorBalances['afterdatediffonexch'] ;
		$FXBalance = $DebtorBalances['fxbalance'] - $DebtorBalances['fxafterdatetrans'];

		if (abs($Balance)>0.009 OR ABS($FXBalance)>0.009) {

			$DisplayBalance = locale_number_format($DebtorBalances['balance'] - $DebtorBalances['afterdatetrans'],$DebtorBalances['decimalplaces']);
			$DisplayFXBalance = locale_number_format($DebtorBalances['fxbalance'] - $DebtorBalances['fxafterdatetrans'],$DebtorBalances['decimalplaces']);

			$TotBal += $Balance;
			$HTML .= '<tr class="striped_row">
						<td>' . $DebtorBalances['debtorno'] . ' - ' . html_entity_decode($DebtorBalances['name'],ENT_QUOTES,'UTF-8') . '</td>
						<td class="number">' . $DisplayBalance . '</td>
						<td class="number">' . $DisplayFXBalance . '</td>
						<td class="number">' . $DebtorBalances['currency'] . '</td>
					</tr>';
		}
	} /*end customer aged analysis while loop */

	$DisplayTotBalance = locale_number_format($TotBal,$_SESSION['CompanyRecord']['decimalplaces']);

	$HTML .= '<tr class="total_row">
				<td>' . __('Total balances') . '</td>
				<td class="number">' . $DisplayTotBalance . '</td>
				<td colspan="2"></td>
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_DebtorBals_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Debtor Balances');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else { /*The option to print PDF was not hit */

	$Title=__('Debtor Balances');

	$ViewTopic = 'ARReports';
	$BookMark = 'PriorMonthDebtors';

	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . __('Search') .
	 '" alt="" />' . ' ' . $Title . '</p>';

	if (!isset($_POST['FromCriteria']) OR !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<legend>', __('Report Criteria'), '</legend>';
		echo '<field>
				<label for="FromCriteria">' . __('From Customer Code') .':</label>
				<input tabindex="1" type="text" maxlength="10" size="8" name="FromCriteria" required="required" data-type="no-illegal-chars" title="" value="1" />
				<fieldhelp>' . __('Enter a portion of the code of first customer to report') . '</fieldhelp>
			</field>
			<field>
				<label for="ToCriteria">' . __('To Customer Code') . ':</label>
				<input tabindex="2" type="text" maxlength="10" size="8" name="ToCriteria" required="required" data-type="no-illegal-chars" title="" value="zzzzzz" />
				<fieldhelp>' . __('Enter a portion of the code of last customer to report') . '</fieldhelp>
			</field>
			<field>
				<label for="PeriodEnd">' . __('Balances As At') . ':</label>
				<select tabindex="3" name="PeriodEnd">';

		$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
		$Periods = DB_query($SQL,__('Could not retrieve period data because'),__('The SQL that failed to get the period data was'));

		while ($MyRow = DB_fetch_array($Periods)){

			echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';

		}
	}

	echo '</select>
		</field>
		</fieldset>
		<div class="centre">
					<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
					<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		</div>
	</form>';

	include('includes/footer.php');
} /*end of else not PrintPDF */

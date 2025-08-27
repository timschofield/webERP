<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

$ViewTopic = 'AccountsPayable';
$BookMark = 'AgedCreditors';

if (isset($_POST['PrintPDF']) or isset($_POST['View'])
	and isset($_POST['FromCriteria'])
	and mb_strlen($_POST['FromCriteria'])>=1
	and isset($_POST['ToCriteria'])
	and mb_strlen($_POST['ToCriteria'])>=1){

	  /*Now figure out the aged analysis for the Supplier range under review */

	if ($_POST['All_Or_Overdues']=='All'){
		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						currencies.currency,
						currencies.decimalplaces AS currdecimalplaces,
						paymentterms.terms,
						SUM(supptrans.ovamount + supptrans.ovgst  - supptrans.alloc) as balance,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS due,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS overdue1,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS overdue2
				FROM suppliers INNER JOIN paymentterms
				ON suppliers.paymentterms = paymentterms.termsindicator
				INNER JOIN currencies
				ON suppliers.currcode = currencies.currabrev
				INNER JOIN supptrans
				ON suppliers.supplierid = supptrans.supplierno
				WHERE suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
				AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
				AND  suppliers.currcode ='" . $_POST['Currency'] . "'
				GROUP BY suppliers.supplierid,
						suppliers.suppname,
						currencies.currency,
						paymentterms.terms,
						paymentterms.daysbeforedue,
						paymentterms.dayinfollowingmonth
				HAVING ROUND(ABS(SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc)), currencies.decimalplaces) > 0";

	} else {

		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						currencies.currency,
						currencies.decimalplaces AS currdecimalplaces,
						paymentterms.terms,
						SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
							CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS due,
						Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS overdue1,
						SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						ELSE
							CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
						END) AS overdue2
				FROM suppliers INNER JOIN paymentterms
				ON suppliers.paymentterms = paymentterms.termsindicator
				INNER JOIN currencies
				ON suppliers.currcode = currencies.currabrev
				INNER JOIN supptrans
				ON suppliers.supplierid = supptrans.supplierno
				WHERE suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
				AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
				AND suppliers.currcode ='" . $_POST['Currency'] . "'
				GROUP BY suppliers.supplierid,
					suppliers.suppname,
					currencies.currency,
					paymentterms.terms,
					paymentterms.daysbeforedue,
					paymentterms.dayinfollowingmonth
				HAVING SUM(IF (paymentterms.daysbeforedue > 0,
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END,
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END)) > 0";

	}

	$SupplierResult = DB_query($SQL, '', '', false, false); /*dont trap errors */


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
					' . __('Aged Supplier Balances For Suppliers from') . ' ' . $_POST['FromCriteria'] . ' ' . __('to') . ' ' . $_POST['ToCriteria'] . '<br />
					' . __('And Trading in') . ' ' . $_POST['Currency'] . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>';

	$HTML .= '<table>
					<thead>
						<tr>
							<th>' . __('Supplier') . '</th>
							<th>' . __('Balance') . '</th>
							<th>' . __('Current') . '</th>
							<th>' . __('Due Now') . '</th>
							<th>' . $_SESSION['PastDueDays1'] . ' ' . __('Days Over') . '</th>
							<th>' . $_SESSION['PastDueDays2'] . ' ' . __('Days Over') . '</th>
						</tr>
					</thead>
					<tbody>';

	$TotBal = 0;
	$TotDue = 0;
	$TotCurr = 0;
	$TotOD1 = 0;
	$TotOD2 = 0;
	$CurrDecimalPlaces =0;

	$ListCount = DB_num_rows($SupplierResult); // UldisN

	while ($AgedAnalysis = DB_fetch_array($SupplierResult)){

		$CurrDecimalPlaces = $AgedAnalysis['currdecimalplaces'];

		$DisplayDue = locale_number_format($AgedAnalysis['due']-$AgedAnalysis['overdue1'],$CurrDecimalPlaces);
		$DisplayCurrent = locale_number_format($AgedAnalysis['balance']-$AgedAnalysis['due'],$CurrDecimalPlaces);
		$DisplayBalance = locale_number_format($AgedAnalysis['balance'],$CurrDecimalPlaces);
		$DisplayOverdue1 = locale_number_format($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2'],$CurrDecimalPlaces);
		$DisplayOverdue2 = locale_number_format($AgedAnalysis['overdue2'],$CurrDecimalPlaces);

		$TotBal += $AgedAnalysis['balance'];
		$TotDue += ($AgedAnalysis['due']-$AgedAnalysis['overdue1']);
		$TotCurr += ($AgedAnalysis['balance']-$AgedAnalysis['due']);
		$TotOD1 += ($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2']);
		$TotOD2 += $AgedAnalysis['overdue2'];

		$HTML .= '<tr class="striped_row">
					<td>' . $AgedAnalysis['supplierid'] . ' - ' . $AgedAnalysis['suppname'] . '</td>
					<td class="number">' . $DisplayBalance . '</td>
					<td class="number">' . $DisplayCurrent . '</td>
					<td class="number">' . $DisplayDue . '</td>
					<td class="number">' . $DisplayOverdue1 . '</td>
					<td class="number">' . $DisplayOverdue2 . '</td>
				</tr>';

		if ($_POST['DetailedReport']=='Yes'){

		   $SQL = "SELECT systypes.typename,
							supptrans.suppreference,
							supptrans.trandate,
							(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) as balance,
							CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue  THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							END AS due,
							CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue	   AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate), paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							END AS overdue1,
							CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
							END AS overdue2
						FROM suppliers
						LEFT JOIN paymentterms
							ON suppliers.paymentterms = paymentterms.termsindicator
						LEFT JOIN supptrans
							ON suppliers.supplierid = supptrans.supplierno
						LEFT JOIN systypes
							ON systypes.typeid = supptrans.type
						WHERE ABS(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) >0.009
							AND supptrans.settled = 0
							AND supptrans.supplierno = '" . $AgedAnalysis["supplierid"] . "'";

			$DetailResult = DB_query($SQL, '', '', false, false); /*dont trap errors - trapped below*/

			$HTML .= '<tr>
						<td colspan="6">
							<table>';

			while ($DetailTrans = DB_fetch_array($DetailResult)){

				$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
				$HTML .= '<tr>
							<th>' . $DetailTrans['typename'] . '</th>
							<th>' . $DetailTrans['suppreference'] . '</th>
							<th>' . $DisplayTranDate . '</th>
							<th></th>
							<th></th>
							<th></th>
						</tr>';

				$DisplayDue = locale_number_format($DetailTrans['due']-$DetailTrans['overdue1'],$CurrDecimalPlaces);
				$DisplayCurrent = locale_number_format($DetailTrans['balance']-$DetailTrans['due'],$CurrDecimalPlaces);
				$DisplayBalance = locale_number_format($DetailTrans['balance'],$CurrDecimalPlaces);
				$DisplayOverdue1 = locale_number_format($DetailTrans['overdue1']-$DetailTrans['overdue2'],$CurrDecimalPlaces);
				$DisplayOverdue2 = locale_number_format($DetailTrans['overdue2'],$CurrDecimalPlaces);

				$HTML .= '<tr class="striped_row">
							<td class="number">' . $DisplayBalance . '</td>
							<td class="number">' . $DisplayCurrent . '</td>
							<td class="number">' . $DisplayDue . '</td>
							<td class="number">' . $DisplayOverdue1 . '</td>
							<td class="number">' . $DisplayOverdue2 . '</td>
						</tr>';

			} /*end while there are detail transactions to show */
			$HTML .= '</table>
					</td>
				</tr>';
		} /*Its a detailed report */
	} /*end Supplier aged analysis while loop */

	$DisplayTotBalance = locale_number_format($TotBal,$CurrDecimalPlaces);
	$DisplayTotDue = locale_number_format($TotDue,$CurrDecimalPlaces);
	$DisplayTotCurrent = locale_number_format($TotCurr,$CurrDecimalPlaces);
	$DisplayTotOverdue1 = locale_number_format($TotOD1,$CurrDecimalPlaces);
	$DisplayTotOverdue2 = locale_number_format($TotOD2,$CurrDecimalPlaces);

	$HTML .= '<tr class="total_row">
				<td></td>
				<td class="number">' . $DisplayTotBalance . '</td>
				<td class="number">' . $DisplayTotCurrent . '</td>
				<td class="number">' . $DisplayTotDue . '</td>
				<td class="number">' . $DisplayTotOverdue1 . '</td>
				<td class="number">' . $DisplayTotOverdue2 . '</td>
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_AgedCreditors_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Aged Creditor Analysis');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . __('Aged Creditor Analysis') . '" alt="" />' . ' ' . __('Aged Creditor Analysis') . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}
} else { /*The option to print PDF was not hit */

	$Title = __('Aged Supplier Analysis');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	if (!isset($_POST['FromCriteria']) or !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<legend>', __('Select Report Criteria'), '</legend>';

		echo '<field>
				<label for="FromCriteria">' . __('From Supplier Code') . ':</label>
				<input tabindex="1" type="text" required="required"  autofocus="autofocus" maxlength="6" size="7" name="FromCriteria" value="1" title+"" />
				<fieldhelp>' . __('Enter the first supplier code alphabetially to include in the report') . '</fieldhelp>
			</field>';

		echo '<field>
				<label for="ToCriteria">' . __('To Supplier Code') . ':</label>
				<input tabindex="2" type="text" maxlength="6" size="7" name="ToCriteria" required="required" value="zzzzzz" title="" />
				<fieldhelp>' . __('Enter the last supplier code alphabetically to include in the report') . '</fieldhelp>
			</field>';

		echo '<field>
				<label for="All_Or_Overdues">' . __('All balances or overdues only') . ':' . '</label>
				<select tabindex="3" name="All_Or_Overdues">
					<option selected="selected" value="All">' . __('All suppliers with balances') . '</option>
					<option value="OverduesOnly">' . __('Overdue accounts only') . '</option>
				</select>
				<fieldhelp>', __('Show all account balances, or just show accounts with overdue balances'), '</fieldhelp>
			</field>';

		echo '<field>
				<label for="Currency">' . __('For suppliers trading in') . ':' . '</label>
				<select tabindex="4" name="Currency">';

		$SQL = "SELECT currency, currabrev FROM currencies";
		$Result = DB_query($SQL);

		while ($MyRow=DB_fetch_array($Result)){
			if ($MyRow['currabrev'] == $_SESSION['CompanyRecord']['currencydefault']){
				echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			}
		}
		echo '</select>
			<fieldhelp>', __('Select the supplier currency, and just show suppliers trading in that currency'), '</fieldhelp>
		</field>';

		echo '<field>
				<label for="DetailedReport">' . __('Summary or Detailed Report') . ':' . '</label>
				<select tabindex="5" name="DetailedReport">
					<option selected="selected" value="No">' . __('Summary Report')  . '</option>
					<option value="Yes">' . __('Detailed Report')  . '</option>
				</select>
				<fieldhelp>', __('The report can be shown as a summary report, or a detailed report'), '</fieldhelp>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . __('View') . '" />
			</div>
		</form>';
	}
	include('includes/footer.php');
} /*end of else not PrintPDF */

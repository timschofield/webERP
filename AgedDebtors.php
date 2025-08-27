<?php

/* Lists customer account balances in detail or summary in selected currency */

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if(isset($_POST['PrintPDF']) or isset($_POST['View'])
	and isset($_POST['FromCriteria'])
	and mb_strlen($_POST['FromCriteria'])>=1
	and isset($_POST['ToCriteria'])
	and mb_strlen($_POST['ToCriteria'])>=1) {

	  /*Now figure out the aged analysis for the customer range under review */
	if($_SESSION['SalesmanLogin'] != '') {
		$_POST['Salesman'] = $_SESSION['SalesmanLogin'];
	}
	if(trim($_POST['Salesman'])!='') {
		$SalesLimit = " AND debtorsmaster.debtorno IN (SELECT DISTINCT debtorno FROM custbranch WHERE salesman = '".$_POST['Salesman']."') ";
	} else {
		$SalesLimit = "";
	}
	if($_POST['All_Or_Overdues']=='All') {
		$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				currencies.currency,
				currencies.decimalplaces,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription,
				SUM(debtortrans.balance) AS balance,
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
					THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
						THEN debtortrans.balance
						ELSE 0 END
					ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= 0
						THEN debtortrans.balance
						ELSE 0 END
					END
				) AS due,
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
					THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
						THEN debtortrans.balance ELSE 0 END
					ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . "
						THEN debtortrans.balance
						ELSE 0 END
					END
				) AS overdue1,
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
					THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
						THEN debtortrans.balance ELSE 0 END
					ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . "
						THEN debtortrans.balance
						ELSE 0 END
					END
				) AS overdue2
				FROM debtorsmaster,
					paymentterms,
					holdreasons,
					currencies,
					debtortrans
				WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
					AND debtorsmaster.currcode = currencies.currabrev
					AND debtorsmaster.holdreason = holdreasons.reasoncode
					AND debtorsmaster.debtorno = debtortrans.debtorno
					AND debtorsmaster.debtorno >= '" . $_POST['FromCriteria'] . "'
					AND debtorsmaster.debtorno <= '" . $_POST['ToCriteria'] . "'
					AND debtorsmaster.currcode ='" . $_POST['Currency'] . "'
					" . $SalesLimit . "
				GROUP BY debtorsmaster.debtorno,
					debtorsmaster.name,
					currencies.currency,
					paymentterms.terms,
					paymentterms.daysbeforedue,
					paymentterms.dayinfollowingmonth,
					debtorsmaster.creditlimit,
					holdreasons.dissallowinvoices,
					holdreasons.reasondescription
				HAVING
					ROUND(ABS(SUM(debtortrans.balance)),currencies.decimalplaces) > 0";

	} elseif($_POST['All_Or_Overdues']=='OverduesOnly') {

		$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				currencies.currency,
				currencies.decimalplaces,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription,
				SUM(debtortrans.balance) AS balance,
			SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
						THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= paymentterms.daysbeforedue
							THEN debtortrans.balance
							ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= 0
						THEN debtortrans.balance ELSE 0 END
					END
				) AS due,
			SUM(
			  		CASE WHEN (paymentterms.daysbeforedue > 0)
						THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
							THEN debtortrans.balance
							ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . "
							THEN debtortrans.balance
							ELSE 0 END
					END
				) AS overdue1,
			SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0)
						THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
							THEN debtortrans.balance
							ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . "
							THEN debtortrans.balance
							ELSE 0 END
					END
				) AS overdue2
			FROM debtorsmaster,
					paymentterms,
					holdreasons,
					currencies,
					debtortrans
				WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.holdreason = holdreasons.reasoncode
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND debtorsmaster.debtorno >= '" . $_POST['FromCriteria'] . "'
				AND debtorsmaster.debtorno <= '" . $_POST['ToCriteria'] . "'
				AND debtorsmaster.currcode ='" . $_POST['Currency'] . "'
				" . $SalesLimit . "
			GROUP BY debtorsmaster.debtorno,
					debtorsmaster.name,
					currencies.currency,
					paymentterms.terms,
					paymentterms.daysbeforedue,
					paymentterms.dayinfollowingmonth,
					debtorsmaster.creditlimit,
					holdreasons.dissallowinvoices,
					holdreasons.reasondescription
			HAVING SUM(
				CASE WHEN (paymentterms.daysbeforedue > 0)
						THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
							THEN debtortrans.balance
							ELSE 0 END
						ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . "
							THEN debtortrans.balance
							ELSE 0 END
					END
					) > 0.01";

	} elseif($_POST['All_Or_Overdues']=='HeldOnly') {

		$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					currencies.currency,
					currencies.decimalplaces,
					paymentterms.terms,
					debtorsmaster.creditlimit,
					holdreasons.dissallowinvoices,
					holdreasons.reasondescription,
					SUM(debtortrans.balance) AS balance,
					SUM(
						CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= paymentterms.daysbeforedue
								THEN debtortrans.balance
								ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= 0
								THEN debtortrans.balance
								ELSE 0 END
						END
					) AS due,
					SUM(
						CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
								AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
								THEN debtortrans.balance ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . "
								THEN debtortrans.balance
							ELSE 0 END
						END
					) AS overdue1,
					SUM(
						CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
								AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
								THEN debtortrans.balance
								ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= ".$_SESSION['PastDueDays2'] . "
								THEN debtortrans.balance
							ELSE 0 END
						END
					) AS overdue2
				FROM debtorsmaster,
				paymentterms,
				holdreasons,
				currencies,
				debtortrans
				WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.holdreason = holdreasons.reasoncode
				AND debtorsmaster.debtorno = debtortrans.debtorno
				AND holdreasons.dissallowinvoices=1
				AND debtorsmaster.debtorno >= '" . $_POST['FromCriteria'] . "'
				AND debtorsmaster.debtorno <= '" . $_POST['ToCriteria'] . "'
				AND debtorsmaster.currcode ='" . $_POST['Currency'] . "'
				" . $SalesLimit . "
				GROUP BY debtorsmaster.debtorno,
				debtorsmaster.name,
				currencies.currency,
				paymentterms.terms,
				paymentterms.daysbeforedue,
				paymentterms.dayinfollowingmonth,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription
				HAVING ABS(SUM(debtortrans.balance)) >0.005";
	}
	$ErrMsg = __('The customer details could not be retrieved');
	$CustomerResult = DB_query($SQL, $ErrMsg);

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
					' . __('Aged Customer Balances For Customers from') . ' ' . $_POST['FromCriteria'] . ' ' .  __('to') . ' ' . $_POST['ToCriteria'] . '<br />
					' . __('And Trading in') . ' ' . $_POST['Currency'] . '<br />';
	if (trim($_POST['Salesman'])!=''){
		$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='".$_POST['Salesman']."'";
		$rs = DB_query($SQL, '', '', false, false);
		$Row = DB_fetch_array($rs);
		$HTML .= __('And Has at Least 1 Branch Serviced By Sales Person #'). ' '. $_POST['Salesman'] . ' - ' . $Row['salesmanname'] . '<br />';
	}
	$HTML .=  __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Customer') . '</th>
							<th>' . __('Balance') . '</th>
							<th>' . __('Current') . '</th>
							<th>' . __('Due Now') . '</th>
							<th>' . $_SESSION['PastDueDays1'] . ' ' . __('Days Over') . '</th>
							<th>' . $_SESSION['PastDueDays2'] . ' ' . __('Days Over') . '</th>
						</tr>
					</thead>
					<tbody>';

	$TotBal=0;
	$TotCurr=0;
	$TotDue=0;
	$TotOD1=0;
	$TotOD2=0;

 	$ListCount = DB_num_rows($CustomerResult);
	$CurrDecimalPlaces =2; //by default

	while ($AgedAnalysis = DB_fetch_array($CustomerResult)) {
		$CurrDecimalPlaces = $AgedAnalysis['decimalplaces'];
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
					<td>' . $AgedAnalysis['debtorno'] . ' - ' . $AgedAnalysis['name'] . '</td>
					<td class="number">' . $DisplayBalance . '</td>
					<td class="number">' . $DisplayCurrent . '</td>
					<td class="number">' . $DisplayDue . '</td>
					<td class="number">' . $DisplayOverdue1 . '</td>
					<td class="number">' . $DisplayOverdue2 . '</td>
				</tr>';

		if($_POST['DetailedReport']=='Yes') {

			$SQL = "SELECT systypes.typename,
						debtortrans.transno,
						debtortrans.trandate,
						(debtortrans.balance) as balance,
						(CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
								THEN debtortrans.balance
								ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= 0
								THEN debtortrans.balance
								ELSE 0 END
						END) AS due,
						(CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ") THEN debtortrans.balance ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . "
								THEN debtortrans.balance
								ELSE 0 END
						END) AS overdue1,
						(CASE WHEN (paymentterms.daysbeforedue > 0)
							THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
								THEN debtortrans.balance
								ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . "
								THEN debtortrans.balance
								ELSE 0 END
						END) AS overdue2
				   FROM debtorsmaster,
						paymentterms,
						debtortrans,
						systypes
				   WHERE systypes.typeid = debtortrans.type
						AND debtorsmaster.paymentterms = paymentterms.termsindicator
						AND debtorsmaster.debtorno = debtortrans.debtorno
						AND debtortrans.debtorno = '" . $AgedAnalysis['debtorno'] . "'
						AND ABS(debtortrans.balance)>0.004";

			if($_SESSION['SalesmanLogin'] != '') {
				$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
			}

			$ErrMsg = __('The details of outstanding transactions for customer') . ' - ' . $AgedAnalysis['debtorno'] . ' ' . __('could not be retrieved');
			$DetailResult = DB_query($SQL, $ErrMsg);

			$HTML .= '<tr>
						<td colspan="6">
							<table>';

			while ($DetailTrans = DB_fetch_array($DetailResult)) {

				$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
				$HTML .= '<tr>
							<th>' . $DetailTrans['typename'] . '</th>
							<th>' . $DetailTrans['transno'] . '</th>
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

			$FontSize=8;
		} /*Its a detailed report */
	} /*end customer aged analysis while loop */

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
		$dompdf->stream($_SESSION['DatabaseName'] . '_AgedDebtors_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Aged Debtor Analysis');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . __('Aged Debtor Analysis') . '" alt="" />' . ' ' . __('Aged Debtor Analysis') . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else { /*The option to print PDF was not hit */

	$Title=__('Aged Debtor Analysis');

	$ViewTopic = 'ARReports';
	$BookMark = 'AgedDebtors';

	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	if((!isset($_POST['FromCriteria']) or !isset($_POST['ToCriteria']))) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<legend>', __('Select Report Criteria'), '</legend>';

		echo '<field>
				<label for="FromCriteria">' . __('From Customer Code') . ':' . '</label>
				<input tabindex="1" autofocus="autofocus" required="required" type="text" maxlength="6" size="7" name="FromCriteria" value="0" title="" />
				<fieldhelp>' . __('Enter the first customer code alphabetically to include in the report') . '</fieldhelp>
			</field>
			<field>
				<label for="ToCriteria">' . __('To Customer Code') . ':' . '</label>
				<input tabindex="2" type="text" required="required"  maxlength="6" size="7" name="ToCriteria" value="zzzzzz" title="" />
				<fieldhelp>' . __('Enter the last customer code alphabetically to include in the report') . '</fieldhelp>
			</field>
			<field>
				<label for="All_Or_Overdues">' . __('All balances or overdues only') . ':' . '</label>
				<select tabindex="3" name="All_Or_Overdues">
					<option selected="selected" value="All">' . __('All customers with balances') . '</option>
					<option value="OverduesOnly">' . __('Overdue accounts only') . '</option>
					<option value="HeldOnly">' . __('Held accounts only') . '</option>
				</select>
				<fieldhelp>', __('Show all account balances, or just show accounts with overdue balances'), '</fieldhelp>
			</field>
			<field>
				<label for="Salesman">' . __('Only Show Customers Of') . ':' . '</label>';
		if($_SESSION['SalesmanLogin'] != '') {
			echo '<fieldtext>', $_SESSION['UsersRealName'], '</fieldtext>';
		}else{
			echo '<select tabindex="4" name="Salesman">';

			$SQL = "SELECT salesmancode, salesmanname FROM salesman";

			$Result = DB_query($SQL);
			echo '<option value="">' . __('All Salespeople') . '</option>';
			while ($MyRow=DB_fetch_array($Result)) {
					echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
			}
			echo '</select>
				<fieldhelp>', __('Only show customers for a particular salesperson, or for all sales people'), '</fieldhelp>';
		}
		echo '</field>';

		echo '<field>
				<label for="Currency">' . __('Only show customers trading in') . ':' . '</label>
				<select tabindex="5" name="Currency">';

		$SQL = "SELECT currency, currabrev FROM currencies";

		$Result = DB_query($SQL);
		while ($MyRow=DB_fetch_array($Result)) {
			  if($MyRow['currabrev'] == $_SESSION['CompanyRecord']['currencydefault']) {
				echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			  } else {
				  echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			  }
		}
		echo '</select>
			<fieldhelp>', __('Select the customer currency, and just show customers trading in that currency'), '</fieldhelp>
		</field>';

		echo '<field>
				<label for="DetailedReport">' . __('Summary or detailed report') . ':' . '</label>
				<select tabindex="6" name="DetailedReport">
					<option selected="selected" value="No">' . __('Summary Report') . '</option>
					<option value="Yes">' . __('Detailed Report') . '</option>
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
